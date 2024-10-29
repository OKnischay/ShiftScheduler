<?php 
// Include the database connection
include '../db_conn.php'; 
include_once 'shifts.php';

class ShiftScheduler {

    private $shift;
    private $lastValidationError = '';

    public function __construct($shift) {
        $this->shift = $shift; // Instance of Shift class for DB access
    }

    // Backtracking function to assign shifts
    // public function backtracking($employees, $shifts, &$assignedShifts, $date, &$assignedEmployees) {
    //     error_log("Backtracking called with " . count($employees) . " employees and " . count($shifts) . " shifts");
    //     error_log("Assigned shifts so far: " . count($assignedShifts));

    //     // Base case: if all shifts have been assigned, return true
    //     if (count($assignedShifts) === count($shifts)) {
    //         error_log("All shifts assigned. Returning true.");
    //         return true;
    //     }
    
    //     // Loop through each shift
    //     foreach ($shifts as $shift) {
    //         // Check if the current shift has already been assigned
    //         $shiftAssigned = false;
    //         foreach ($assignedShifts as $assignedShift) {
    //             if ($assignedShift['shift_id'] === $shift['id']) {
    //                 $shiftAssigned = true;
    //                 break;
    //             }
    //         }
    
    //         // Skip the shift if it's already assigned
    //         if ($shiftAssigned) {
    //             continue;
    //         }
    
    //         // Loop through each employee and try to assign the current shift
    //         foreach ($employees as $employee) {
    //             // Check if the employee has already been assigned a shift
    //             if (in_array($employee['id'], $assignedEmployees)) {
    //                 continue; // Skip this employee
    //             }
    
    //             error_log("Trying to assign shift ID: " . $shift['id'] . " to Employee ID: " . $employee['id']);
    
    //             // Check if the employee can be assigned this shift
    //             if ($this->isValidAssignment($employee['id'], $shift, $assignedShifts, $date)) {
    //                 error_log("Valid assignment for Employee " . $employee['id'] . " with Shift ID: " . $shift['id']);
    
    //                 // If valid, assign the shift to the employee
    //                 $assignedShifts[] = [
    //                     'employee_id' => $employee['id'],
    //                     'shift_id' => $shift['id']
    //                 ];
    
    //                 // Mark this employee as assigned to prevent further shift assignments
    //                 $assignedEmployees[] = $employee['id'];
    
    //                 // Recursively try to assign remaining shifts
    //                 if ($this->backtracking($employees, $shifts, $assignedShifts, $date, $assignedEmployees)) {
    //                     return true; // Found a valid solution
    //                 }
    
    //                 // If not valid, remove the assignment (backtrack)
    //                 array_pop($assignedShifts);
    //                 error_log("Backtracking for Employee " . $employee['id'] . " and Shift ID: " . $shift['id']);
                    
    //                 // Remove employee from the assigned list as part of backtracking
    //                 array_pop($assignedEmployees);
    //             } else {
    //                 error_log("Invalid assignment for Employee " . $employee['id'] . " with Shift ID: " . $shift['id'] . ". Reason: " . $this->lastValidationError);
    //             }
    //         }
    //     }
    
    //     // If no valid assignment is found, return false
    //     error_log("No valid assignment found in this branch. Backtracking...");
    //     return false;
    // }
    public function backtracking($employees, $shifts, &$assignedShifts, $date, &$assignedEmployees) {
        error_log("Backtracking called with " . count($employees) . " employees and " . count($shifts) . " shifts");
        error_log("Assigned shifts so far: " . count($assignedShifts));
    
        // Base case: if all shifts have been assigned, return true
        if (count($assignedShifts) === count($shifts)) {
            error_log("All shifts assigned. Returning true.");
            return true;
        }
    
        // Find the next unassigned shift
        $currentShift = null;
        foreach ($shifts as $shift) {
            if (!in_array($shift['id'], array_column($assignedShifts, 'shift_id'))) {
                $currentShift = $shift;
                break;
            }
        }
    
        // If no unassigned shift found, return true (all shifts are assigned)
        if ($currentShift === null) {
            error_log("No more shifts to assign. Returning true.");
            return true;
        }
    
        // Try to assign the current shift to an available employee
        foreach ($employees as $employee) {
            // Skip if employee is already assigned
            if (in_array($employee['id'], $assignedEmployees)) {
                continue;
            }
    
            error_log("Trying to assign shift ID: " . $currentShift['id'] . " to Employee ID: " . $employee['id']);
    
            if ($this->isValidAssignment($employee['id'], $currentShift, $assignedShifts, $date)) {
                error_log("Valid assignment for Employee " . $employee['id'] . " with Shift ID: " . $currentShift['id']);
    
                // Assign the shift
                $assignedShifts[] = [
                    'employee_id' => $employee['id'],
                    'shift_id' => $currentShift['id']
                ];
                $assignedEmployees[] = $employee['id'];
    
                // Recursively try to assign remaining shifts
                if ($this->backtracking($employees, $shifts, $assignedShifts, $date, $assignedEmployees)) {
                    return true; // Found a valid solution
                }
    
                // If not valid, remove the assignment (backtrack)
                array_pop($assignedShifts);
                array_pop($assignedEmployees);
                error_log("Backtracking for Employee " . $employee['id'] . " and Shift ID: " . $currentShift['id']);
            } else {
                error_log("Invalid assignment for Employee " . $employee['id'] . " with Shift ID: " . $currentShift['id'] . ". Reason: " . $this->lastValidationError);
            }
        }
    
        // If no valid assignment is found for this shift, return false
        error_log("No valid assignment found for Shift ID: " . $currentShift['id'] . ". Backtracking...");
        return false;
    }

    // Function to validate if the assignment is valid
    private function isValidAssignment($employee_id, $shift, $assignments, $date) {
        error_log("Detailed validation for Employee {$employee_id} and Shift {$shift['id']} on {$date}:");
        
        // Get shift details
        $shift_id = $shift['id'];
        $start_time = $shift['start_time'];
        $end_time = $shift['end_time'];

        // Check employee availability
        $availability = $this->shift->getEmployeeAvailability($employee_id, $date);
        error_log("  Availability for Employee {$employee_id} on {$date}: " . print_r($availability, true));
        if (empty($availability)) {
            error_log("  Failed: Employee {$employee_id} is NOT available on {$date}");
            $this->lastValidationError = "Employee {$employee_id} is NOT available on {$date}";
            return false;
        }
        error_log("  Passed: Employee is available");

        // Check for overlapping shifts
        if ($this->shift->checkShiftOverlap($employee_id, $start_time, $end_time, $date)) {
            error_log("  Failed: Shift overlap detected for Employee {$employee_id} from {$start_time} to {$end_time}");
            $this->lastValidationError = "Shift overlap detected for Employee {$employee_id} from {$start_time} to {$end_time}";
            return false;
        }
        error_log("  Passed: No shift overlap");

        // Check max daily hours
        if (!$this->shift->checkMaxHours($employee_id, $start_time, $end_time, $date)) {
            error_log("  Failed: Exceeded daily hours for Employee {$employee_id} on {$date}");
            $this->lastValidationError = "Exceeded daily hours for Employee {$employee_id} on {$date}";
            return false;
        }
        error_log("  Passed: Daily hours check");

        // Check if shift is already assigned
        if (in_array($shift['id'], array_column($assignments, 'shift_id'))) {
            error_log("  Failed: Shift {$shift['id']} is already assigned");
            $this->lastValidationError = "Shift {$shift['id']} is already assigned";
            return false;
        }
        error_log("  Passed: Shift is not already assigned");

        // Check max weekly hours
        if (!$this->shift->checkMaxWeeklyHours($employee_id, $start_time, $end_time, $date)) {
            error_log("  Failed: Exceeded weekly hours for Employee {$employee_id} on {$date}");
            $this->lastValidationError = "Exceeded weekly hours for Employee {$employee_id} on {$date}";
            return false;
        }
        error_log("  Passed: Weekly hours check");

        // Check if employee is on leave during the date
        if ($this->shift->isEmployeeOnLeave($employee_id, $date)) {
            error_log("  Failed: Employee {$employee_id} is on leave on {$date}");
            $this->lastValidationError = "Employee {$employee_id} is on leave on {$date}";
            return false;
        }
        error_log("  Passed: Employee is not on leave");

        error_log("  All checks passed. Assignment is valid for Employee {$employee_id} with Shift {$shift['id']} on {$date}");
        return true; // Valid assignment
    }

    // Method to initiate the scheduling process
    public function assignShifts($employees, $date) {
        error_log("Starting assignShifts for date: " . $date);
        error_log("Total employees: " . count($employees));
        
        $assignments = [];
        $assignedEmployees = [];
        $assignedShiftIds = [];
        
        // Fetch existing assignments
        $existingAssignments = $this->shift->getExistingAssignments($date);
        if (!empty($existingAssignments)) {
            $assignments = $existingAssignments;
            foreach ($existingAssignments as $assignment) {
                $assignedEmployees[] = $assignment['employee_id'];
                $assignedShiftIds[] = $assignment['shift_id'];
            }
        }

        $shifts = $this->shift->getAllShifts();
        error_log("Total shifts: " . count($shifts));
        error_log("Existing assignments: " . count($existingAssignments));

        if (empty($shifts)) {
            return ["error" => "No available shifts for the selected date."];
        }

        // Filter out shifts that are already assigned
        $availableShifts = array_filter($shifts, function($shift) use ($assignedShiftIds) {
            return !in_array($shift['id'], $assignedShiftIds);
        });

        // Filter out employees who are already assigned
        $availableEmployees = array_filter($employees, function($employee) use ($assignedEmployees) {
            return !in_array($employee['id'], $assignedEmployees);
        });

        error_log("Available shifts after filtering: " . count($availableShifts));
        error_log("Available employees after filtering: " . count($availableEmployees));

        $result = $this->backtracking($availableEmployees, $availableShifts, $assignments, $date, $assignedEmployees);

        if ($result) {
            error_log("Backtracking succeeded. Assigning shifts.");
            $successCount = 0;
            $errorMessages = [];
            foreach ($assignments as $assignment) {
                if (!isset($assignment['id'])) {  // Only assign new shifts
                    $employee_id = $assignment['employee_id'];
                    $shift_id = $assignment['shift_id'];
                    
                    // Double-check that this shift hasn't been assigned
                    if (!in_array($shift_id, $assignedShiftIds)) {
                        $assignResult = $this->shift->assignShift($employee_id, $shift_id, $date);
                        $assignResult = json_decode($assignResult, true);
                        if (isset($assignResult['success'])) {
                            $successCount++;
                            $assignedShiftIds[] = $shift_id;  // Mark this shift as assigned
                        } else if (isset($assignResult['error'])) {
                            $errorMessages[] = $assignResult['error'];
                        }
                    }
                }
            }
            
            if ($successCount > 0) {
                return ["success" => "Successfully assigned {$successCount} new shifts.", "errors" => $errorMessages];
            } else {
                return ["error" => "Failed to assign any new shifts.", "details" => $errorMessages];
            }
        } else {
            error_log("Backtracking failed. No valid assignments found.");
            return ["error" => "No valid shift assignments found."];
        }
    }
}
?>