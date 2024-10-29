<?php
include '../db_conn.php'; // Include database connection

if (isset($_POST['shift_id']) && isset($_POST['date'])) {
    $shift_id = $_POST['shift_id'];
    $date = $_POST['date'];
    
    $employees = getAvailableEmployees($shift_id, $date);
    
    // Return the result as JSON
    header('Content-Type: application/json');
    echo json_encode($employees);
} else {
    header('Content-Type: application/json');
    echo json_encode([]);
}

function getAvailableEmployees($shift_id, $date) {
    global $con;

    $shiftQuery = "SELECT * FROM shifts WHERE id = ?";
    $stmt = $con->prepare($shiftQuery);
    $stmt->bind_param("i", $shift_id);
    $stmt->execute();
    $result = $stmt->get_result();
    $shift = $result->fetch_assoc();

    if (!$shift) {
        return [];
    }

    $start_time = $shift['start_time'];
    $end_time = $shift['end_time'];

    $employeeQuery = "
        SELECT e.id, e.first_name, e.last_name
        FROM employees e
        LEFT JOIN leave_requests lr ON e.id = lr.employee_id AND lr.status = 'Approved' AND ? BETWEEN lr.start_date AND lr.end_date
        LEFT JOIN scheduled_shifts ss ON e.id = ss.employee_id AND ss.scheduled_date = ? AND ss.shift_id = ?
        WHERE lr.leave_id IS NULL AND ss.id IS NULL
        AND JSON_CONTAINS(e.availability, JSON_QUOTE(?), '$')";

    $stmt = $con->prepare($employeeQuery);
    $availability = "$start_time-$end_time";
    $stmt->bind_param("ssis", $date, $date, $shift_id, $availability);
    $stmt->execute();
    $result = $stmt->get_result();

    return $result ? $result->fetch_all(MYSQLI_ASSOC) : [];
}
?>