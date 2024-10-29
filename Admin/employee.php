<?php
class Employee {
    private $con;

    // Constructor to initialize the database connection
    public function __construct($con) {
        $this->con = $con;
    }

    // Get all employees
    public function getAllEmployees() {
        $query = "SELECT id, first_name, last_name FROM employees";
        $result = $this->con->query($query);
        $employees = [];
        
        while ($row = $result->fetch_assoc()) {
            $employees[] = $row;
        }
        
        return $employees;
    }

    // Get employee details by ID
    public function getEmployeeById($employee_id) {
        $query = "SELECT * FROM employees WHERE id = ?";
        $stmt = $this->con->prepare($query);
        $stmt->bind_param('i', $employee_id);
        $stmt->execute();
        $result = $stmt->get_result();
        
        if ($result->num_rows > 0) {
            return $result->fetch_assoc();
        } else {
            return false; // No employee found
        }
    }

    // Get employee availability for a specific date
    public function getEmployeeAvailability($employee_id, $date) {
        // Convert the date to a day name (e.g., 'Sunday', 'Monday', etc.)
        $day_name = date('l', strtotime($date));
    
        $query = "SELECT * FROM employee_availability 
                  WHERE employee_id = ? AND day_of_week = ?";
        $stmt = $this->con->prepare($query);
        $stmt->bind_param('is', $employee_id, $day_name);
        $stmt->execute();
        $result = $stmt->get_result();
        
        return $result->fetch_all(MYSQLI_ASSOC); // Return all matching records
    }

    // Add employee availability (updated to exclude time slots)
    public function addEmployeeAvailability($employee_id, $day_of_week) {
        $query = "INSERT INTO employee_availability (employee_id, day_of_week) VALUES (?, ?)";
        $stmt = $this->con->prepare($query);
        $stmt->bind_param('ii', $employee_id, $day_of_week);
        return $stmt->execute();
    }

    // Update employee availability (removed time fields)
    public function updateEmployeeAvailability($id, $day_of_week) {
        $query = "UPDATE employee_availability SET day_of_week = ? WHERE id = ?";
        $stmt = $this->con->prepare($query);
        $stmt->bind_param('ii', $day_of_week, $id);
        return $stmt->execute();
    }

    // Delete employee availability
    public function deleteEmployeeAvailability($id) {
        $query = "DELETE FROM employee_availability WHERE id = ?";
        $stmt = $this->con->prepare($query);
        $stmt->bind_param('i', $id);
        return $stmt->execute();
    }

    // Get all shifts for a specific employee
    public function getEmployeeShifts($employee_id) {
        $query = "SELECT s.start_time, s.end_time FROM schedule sc
                  JOIN shifts s ON sc.shift_id = s.id
                  WHERE sc.employee_id = ?";
        $stmt = $this->con->prepare($query);
        $stmt->bind_param('i', $employee_id);
        $stmt->execute();
        $result = $stmt->get_result();
        return $result->fetch_all(MYSQLI_ASSOC);
    }

    // Get shifts by employee and date
    public function getShiftsByEmployeeAndDate($employee_id, $date) {
        $query = "SELECT s.start_time, s.end_time 
                  FROM schedule sc
                  JOIN shifts s ON sc.shift_id = s.id
                  WHERE sc.employee_id = ? AND sc.date = ?";
        $stmt = $this->con->prepare($query);
        $stmt->bind_param('is', $employee_id, $date);
        $stmt->execute();
        $result = $stmt->get_result();
        return $result->fetch_all(MYSQLI_ASSOC);
    }
}

?>
