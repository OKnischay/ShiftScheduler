<?php
// Include the database connection
include '../db_conn.php'; // Adjust the path as needed

class Shift {
    private $con;

    public function __construct($conn) {
        $this->con = $conn;
    }
    // Method to get all shifts
    public function getAllShifts() {
        $stmt = $this->con->query("SELECT * FROM shifts ORDER BY start_time DESC");
        return $stmt->fetch_all(MYSQLI_ASSOC);
    }
    public function getExistingAssignments($date) {
        $sql = "SELECT employee_id, shift_id FROM schedule WHERE date = ?";
        $stmt = $this->con->prepare($sql);
        $stmt->bind_param('s', $date); // Bind the parameter
        $stmt->execute();
    
        // Fetch the results as an associative array
        $result = $stmt->get_result();
        return $result->fetch_all(MYSQLI_ASSOC);
    }
    


    // Method to get a shift by ID
    public function getShiftById($shift_id) {
        $stmt = $this->con->prepare("SELECT * FROM shifts WHERE id = ?");
        $stmt->bind_param("i", $shift_id);
        $stmt->execute();
        return $stmt->get_result()->fetch_assoc();
    }
    public function getShiftsForWeek($start_date) {
        $end_date = date('Y-m-d', strtotime($start_date . ' +6 days'));
        $stmt = $this->con->prepare("SELECT * FROM shifts");
        $stmt->execute();
        return $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
    }
    
    // Method to add a new shift
    private function getDurationInHours($start_time, $end_time) {
        $start = new DateTime($start_time);
        $end = new DateTime($end_time);
    
        // Check if the end time is before the start time, meaning it crosses midnight
        if ($end < $start) {
            $end = (clone $end)->modify('+1 day'); // Create a new DateTime object
        }
    
        $interval = $end->diff($start);
        return ($interval->h + ($interval->days * 24)) + ($interval->i / 60); // Duration in hours
    }
    

    public function addShift($start_time, $end_time, $note) {
        // Convert to DateTime for easier manipulation
        $startDateTime = new DateTime($start_time);
        $endDateTime = new DateTime($end_time);
    
        // If end time is less than or equal to start time, assume it's the next day
        if ($endDateTime <= $startDateTime) {
            $endDateTime->modify('+1 day');
        }
    
        $duration = $this->getDurationInHours($startDateTime->format('H:i'), $endDateTime->format('H:i'));
    
        if ($duration > 8) {
            return "Shift duration must not exceed 8 hours.";
        }
    
        // Insert shift into the database
        $query = "INSERT INTO shifts (start_time, end_time, note) VALUES (?, ?, ?)";
        $stmt = $this->con->prepare($query);
        $stmt->bind_param("sss", $start_time, $end_time, $note);
    
        if ($stmt->execute()) {
            return true;
        } else {
            return "Unable to add shift.";
        }
    }
    
    public function updateShift($id, $start_time, $end_time, $note) {
        $startDateTime = new DateTime($start_time);
        $endDateTime = new DateTime($end_time);
    
        // If end time is less than or equal to start time, assume it's the next day
        if ($endDateTime <= $startDateTime) {
            $endDateTime->modify('+1 day');
        }
    
        $duration = $this->getDurationInHours($startDateTime->format('H:i'), $endDateTime->format('H:i'));
    
        if ($duration > 8) {
            return "Shift duration must not exceed 8 hours.";
        }
    
        // Update shift in the database
        $query = "UPDATE shifts SET start_time = ?, end_time = ?, note = ? WHERE id = ?";
        $stmt = $this->con->prepare($query);
        $stmt->bind_param("sssi", $start_time, $end_time, $note, $id);
    
        if ($stmt->execute()) {
            return true;
        } else {
            return "Unable to update shift.";
        }
    }

    // Method to get shifts by a specific date
    public function getShiftsByDate($date) {
        $query = "SELECT * FROM shifts WHERE id IN (SELECT shift_id FROM schedule WHERE date = ?)";
        $stmt = $this->con->prepare($query);
        $stmt->bind_param('s', $date);
        $stmt->execute();
        $result = $stmt->get_result();
        return $result->fetch_all(MYSQLI_ASSOC);
    }

    // Method to get all shifts assigned to a specific employee
    public function getShiftsByEmployee($employee_id) {
        $query = "SELECT shifts.* FROM shifts 
                  JOIN schedule ON shifts.id = schedule.shift_id 
                  WHERE schedule.employee_id = ?";
        $stmt = $this->con->prepare($query);
        $stmt->bind_param('i', $employee_id);
        $stmt->execute();
        $result = $stmt->get_result();
        
        $shifts = [];
        while ($row = $result->fetch_assoc()) {
            $shifts[] = $row;
        }
        
        return $shifts;
    }

    // Method to delete a shift
    public function deleteShift($id) {
        $stmt = $this->con->prepare("DELETE FROM schedule WHERE shift_id = ?");
        $stmt->bind_param("i", $id);
        $stmt->execute();

        $stmt = $this->con->prepare("DELETE FROM shifts WHERE id = ?");
        $stmt->bind_param("i", $id);
        $stmt->execute();
        $stmt->close();
    }
    public function getTotalHoursForEmployee($employee_id, $date) {
        // Ensure date is properly formatted (Y-m-d) before running the query
        $formatted_date = date('Y-m-d', strtotime($date));
    
        $query = "SELECT SUM(TIMESTAMPDIFF(HOUR, s.start_time, s.end_time)) AS total_hours
                  FROM schedule sc
                  JOIN shifts s ON sc.shift_id = s.id
                  WHERE sc.employee_id = ? AND sc.date = ?";
        $stmt = $this->con->prepare($query);
        $stmt->bind_param('is', $employee_id, $formatted_date);
        $stmt->execute();
        $result = $stmt->get_result();
    
        if ($row = $result->fetch_assoc()) {
            return (float)$row['total_hours'] ?: 0; // Return total hours or 0 if no shifts found
        }
        return 0; // Default to 0 if no rows found
    }
    public function getAssignmentsForWeek($start_date) {
        $end_date = date('Y-m-d', strtotime($start_date . ' +6 days'));
        $query = "SELECT s.start_time, s.end_time, e.first_name, e.last_name, sc.date
                  FROM schedule sc
                  JOIN shifts s ON sc.shift_id = s.id
                  JOIN employees e ON sc.employee_id = e.id
                  WHERE sc.date BETWEEN ? AND ?
                  ORDER BY sc.date, s.start_time";
        
        $stmt = $this->con->prepare($query);
        $stmt->bind_param("ss", $start_date, $end_date);
        $stmt->execute();
        $result = $stmt->get_result();
        
        $assignments = [];
        while ($row = $result->fetch_assoc()) {
            $assignments[] = [
                'start_time' => $row['start_time'],
                'end_time' => $row['end_time'],
                'employee_name' => $row['first_name'] . ' ' . $row['last_name'],
                'date' => $row['date']
            ];
        }
        
        return $assignments;
    }
    public function getWeeklyHours($employee_id, $date) {
        $start_of_week = date('Y-m-d', strtotime('monday this week', strtotime($date)));
        $end_of_week = date('Y-m-d', strtotime('sunday this week', strtotime($date)));

        $query = "
            SELECT s.start_time, s.end_time FROM schedule sc
            JOIN shifts s ON sc.shift_id = s.id
            WHERE sc.employee_id = ? AND sc.date BETWEEN ? AND ?
        ";
        
        $stmt = $this->con->prepare($query);
        $stmt->bind_param('iss', $employee_id, $start_of_week, $end_of_week);
        $stmt->execute();
        $result = $stmt->get_result();
        
        $total_hours = 0;
        while ($shift = $result->fetch_assoc()) {
            $total_hours += $this->getDurationInHours($shift['start_time'], $shift['end_time']);
        }
        
        return $total_hours;
    }

    // Method to get all shifts for a specific employee
    public function getEmployeeShifts($employee_id, $date) {
        $stmt = $this->con->prepare("SELECT sc.date, s.start_time, s.end_time 
                                     FROM schedule sc
                                     JOIN shifts s ON sc.shift_id = s.id
                                     WHERE sc.employee_id = ? 
                                     AND sc.date = ?");
        $stmt->bind_param("is", $employee_id, $date);
        $stmt->execute();
        $result = $stmt->get_result();
        return $result->fetch_all(MYSQLI_ASSOC);
    }
    

    public function isEmployeeOnLeave($employee_id, $date) {
        // SQL query to check if the employee has an approved leave on the specified date
        $leave_query = "SELECT status FROM leave_request WHERE employee_id = ? AND start <= ? AND end >= ? AND status = 'Approved'";
        $stmt = $this->con->prepare($leave_query);
        
        // Check for potential errors in preparation
        if (!$stmt) {
            error_log("Prepare failed: (" . $this->con->errno . ") " . $this->con->error);
            return false; // Return false or handle error
        }
    
        // Bind parameters
        $stmt->bind_param('iss', $employee_id, $date, $date);
        
        // Execute the statement
        if (!$stmt->execute()) {
            error_log("Execute failed: (" . $stmt->errno . ") " . $stmt->error);
            return false; // Return false or handle error
        }
    
        // Get the result
        $leave_result = $stmt->get_result();
        
        // Fetch associative array
        $leave = $leave_result->fetch_assoc();
    
        // Return true if an approved leave is found, otherwise false
        return $leave ? true : false;
    }
    


    // Method to check if a shift overlaps with existing shifts
    public function checkShiftOverlap($employee_id, $start_time, $end_time, $date, $shift_id = null) {
        // Convert times to DateTime objects
        $new_shift_start = new DateTime($date . ' ' . $start_time);
        $new_shift_end = new DateTime($date . ' ' . $end_time);
    
        // Adjust if the shift ends after midnight
        if ($new_shift_end <= $new_shift_start) {
            $new_shift_end->modify('+1 day');
        }
    
        // Fetch existing shifts for the employee on the given date
        $query = "SELECT s.id, s.start_time, s.end_time, sc.date 
                  FROM schedule sc
                  JOIN shifts s ON sc.shift_id = s.id
                  WHERE sc.employee_id = ? AND sc.date = ?";
        if ($shift_id !== null) {
            $query .= " AND s.id != ?";
        }
        
        $stmt = $this->con->prepare($query);
        if ($shift_id !== null) {
            $stmt->bind_param('isi', $employee_id, $date, $shift_id);
        } else {
            $stmt->bind_param('is', $employee_id, $date);
        }
        $stmt->execute();
        $result = $stmt->get_result();
        $shifts = $result->fetch_all(MYSQLI_ASSOC);
    
        // Check for overlaps
        foreach ($shifts as $shift) {
            $shift_start = new DateTime($shift['date'] . ' ' . $shift['start_time']);
            $shift_end = new DateTime($shift['date'] . ' ' . $shift['end_time']);
    
            // Adjust if the shift ends after midnight
            if ($shift_end <= $shift_start) {
                $shift_end->modify('+1 day');
            }
    
            // Detect overlap
            if ($new_shift_start < $shift_end && $new_shift_end > $shift_start) {
                return true; // Overlap found
            }
        }
        return false; // No overlap found
    }
    
    
    // Method to check if an employee is working too many hours
    public function checkMaxHours($employee_id, $new_start_time, $new_end_time, $date) {
        $max_hours_per_day = 8; // Maximum hours allowed per day
    
        // Calculate new shift duration
        $new_shift_start = new DateTime($date . ' ' . $new_start_time);
        $new_shift_end = new DateTime($date . ' ' . $new_end_time);
    
        if ($new_shift_end <= $new_shift_start) {
            $new_shift_end->modify('+1 day');
        }
    
        $new_shift_duration = $new_shift_end->getTimestamp() - $new_shift_start->getTimestamp();
        
        // Get all shifts for the given date for this employee
        $query = "SELECT s.start_time, s.end_time, sc.date 
                  FROM schedule sc
                  JOIN shifts s ON sc.shift_id = s.id
                  WHERE sc.employee_id = ? AND sc.date = ?";
        $stmt = $this->con->prepare($query);
        $stmt->bind_param('is', $employee_id, $date);
        $stmt->execute();
        $result = $stmt->get_result();
        $shifts = $result->fetch_all(MYSQLI_ASSOC);
    
        // Calculate total hours for the day including the new shift
        $total_seconds = $new_shift_duration;
    
        foreach ($shifts as $shift) {
            $shift_start = new DateTime($shift['date'] . ' ' . $shift['start_time']);
            $shift_end = new DateTime($shift['date'] . ' ' . $shift['end_time']);
    
            if ($shift_end <= $shift_start) {
                $shift_end->modify('+1 day');
            }
    
            $shift_duration = $shift_end->getTimestamp() - $shift_start->getTimestamp();
            $total_seconds += $shift_duration;
        }
    
        $total_hours = $total_seconds / 3600;
    
        // Return true if the total hours are within the limit
        return $total_hours <= $max_hours_per_day;
    }
    
    // Get the total hours worked by an employee in a given week
     
            // Check if assigning a new shift would exceed the weekly hour limit
            public function checkMaxWeeklyHours($employee_id, $new_start_time, $new_end_time, $date) {
                $max_hours_per_week = 40; // Maximum hours allowed per week

                $weekly_hours = $this->getWeeklyHours($employee_id, $date);
                $new_shift_hours = $this->getDurationInHours($new_start_time, $new_end_time);
                
                return ($weekly_hours + $new_shift_hours) <= $max_hours_per_week;
            }
            

            public function getAllShiftsByDate($date) {
                $query = "
                    SELECT 
                        shifts.id AS shift_id, 
                        shifts.start_time, 
                        shifts.end_time, 
                        shifts.note, 
                        employees.first_name, 
                        employees.last_name, 
                        employees.picture AS employee_picture
                    FROM 
                        schedule
                    JOIN 
                        shifts ON schedule.shift_id = shifts.id
                    JOIN 
                        employees ON schedule.employee_id = employees.id
                    WHERE 
                        schedule.date = ?
                ";
            
                $stmt = $this->con->prepare($query);
                $stmt->bind_param('s', $date);
                $stmt->execute();
                $result = $stmt->get_result();
            
                $shifts = [];
                while ($row = $result->fetch_assoc()) {
                    $shifts[] = [
                        'id' => $row['shift_id'],
                        'start_time' => $row['start_time'],
                        'end_time' => $row['end_time'],
                        'note' => $row['note'],
                        'employee_name' => $row['first_name'] . ' ' . $row['last_name'],
                        'employee_picture' => $row['employee_picture']
                    ];
                }
                return $shifts;
            }
            

    // Method to get shifts by employee and date
    public function getShiftsByEmployeeAndDate($employee_id, $date) {
        $query = "
            SELECT 
                shifts.id AS shift_id, 
                shifts.start_time, 
                shifts.end_time, 
                shifts.note, 
                employees.first_name, 
                employees.last_name, 
                employees.picture AS employee_picture
            FROM 
                schedule
            JOIN 
                shifts ON schedule.shift_id = shifts.id
            JOIN 
                employees ON schedule.employee_id = employees.id
            WHERE 
                schedule.employee_id = ? AND 
                schedule.date = ?
        ";
    
        $stmt = $this->con->prepare($query);
        $stmt->bind_param('is', $employee_id, $date);
        $stmt->execute();
        $result = $stmt->get_result();
    
        $shifts = [];
        while ($row = $result->fetch_assoc()) {
            $shifts[] = [
                'id' => $row['shift_id'],
                'start_time' => $row['start_time'],
                'end_time' => $row['end_time'],
                'note' => $row['note'],
                'employee_name' => $row['first_name'] . ' ' . $row['last_name'],
                'employee_picture' => $row['employee_picture']
            ];
        }
        return $shifts;
    }
    
    // Method to get employee availability
    public function getEmployeeAvailability($employee_id, $date) {
    // Convert the date to a day name (e.g., 'Sunday', 'Monday', etc.)
    $day_name = date('l', strtotime($date)); // 'l' returns the full textual representation of the day

    // Query the employee availability based on the employee_id and day name
    $availability_query = "SELECT * FROM employee_availability WHERE employee_id = ? AND day_of_week = ?";
    $stmt = $this->con->prepare($availability_query);
    $stmt->bind_param('is', $employee_id, $day_name); // Bind employee_id and day_name
    $stmt->execute();
    $result = $stmt->get_result();
    
    // Initialize an array to hold availability details
    $availability = [];
    
    while ($row = $result->fetch_assoc()) {
        $availability[] = $row; // Add each row to the availability array
    }
    
    return $availability; // Return the availability data
}

    // Method to assign a shift with constraints
    public function assignShift($employee_id, $shift_id, $date) {
        $response = [];      
        // Get shift details
        $shift_query = "SELECT start_time, end_time FROM shifts WHERE id = ?";
        $stmt = $this->con->prepare($shift_query);
        $stmt->bind_param('i', $shift_id);
        $stmt->execute();
        $result = $stmt->get_result();
        $shift = $result->fetch_assoc();
        
        if (!$shift) {
            $response['error'] = "Shift not found.";
            return json_encode($response);
        }
        
        $shift_start_time = $shift['start_time'];
        $shift_end_time = $shift['end_time'];
        
        // Check if the shift is already assigned to the employee on this date
        $shift_assigned_query = "SELECT * FROM schedule WHERE shift_id = ? AND date = ?";
        $stmt = $this->con->prepare($shift_assigned_query);
        $stmt->bind_param('is', $shift_id, $date);
        $stmt->execute();
        $result = $stmt->get_result();

        if ($result->num_rows > 0) {
        $response['error'] = "This shift has already been assigned to another employee on the selected date.";
        return json_encode($response);
        }
        
        // Check employee availability
        $availability = $this->getEmployeeAvailability($employee_id, $date);
        $available = false;
        
        $shift_day = date('l', strtotime($date)); // e.g., 'Sunday'

        // Check if the employee is available on the shift day
        foreach ($availability as $slot) {
            if ($slot['day_of_week'] === $shift_day) {
                $available = true;
                break;
            }
        }

        if (!$available) {
            $response['error'] = " Employee is not available on this day.";
            return json_encode($response);
        }
        
        // Check if the employee has leave for the shift date
        $leave_query = "SELECT status FROM leave_request WHERE employee_id = ? AND start <= ? AND end >= ? AND status = 'Approved'";
        $stmt = $this->con->prepare($leave_query);
        $stmt->bind_param('iss', $employee_id, $date, $date);
        $stmt->execute();
        $result = $stmt->get_result();
        
        if ($result->num_rows > 0) {
            $response['error'] = "Employee has approved leave on this date.";
            return json_encode($response);
        }
        
        // Check for shift overlap for this specific employee
        if ($this->checkShiftOverlap($employee_id, $shift_start_time, $shift_end_time, $date)) {
            $response['error'] = "Shift overlaps with another assigned shift for this employee.";
            return json_encode($response);
        }

        // Check if the employee exceeds max daily hours
        if (!$this->checkMaxHours($employee_id, $shift_start_time, $shift_end_time, $date)) {
            $response['error'] = "Assigning this shift exceeds daily maximum hours.";
            return json_encode($response);
        }

        // Check if the employee exceeds max weekly hours
        if (!$this->checkMaxWeeklyHours($employee_id, $shift_start_time, $shift_end_time, $date)) {
            $response['error'] = " Assigning this shift exceeds weekly maximum hours.";
            return json_encode($response);
        }

        // Assign the shift to the employee
        $assign_query = "INSERT INTO schedule (employee_id, shift_id, date) VALUES (?, ?, ?)";
        $stmt = $this->con->prepare($assign_query);
        $stmt->bind_param('iis', $employee_id, $shift_id, $date);

        if ($stmt->execute()) {
            $response['success'] = "Shift assigned successfully.";
        } else {
            $response['error'] = "Error: Failed to assign shift.";
        }
        return json_encode($response);
    }   
     
 }
?>