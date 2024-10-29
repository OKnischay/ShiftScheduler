<?php
include 'db_conn.php';
session_start();

// Check if the user is logged in
if (!isset($_SESSION['employee_id'])) {
    header("Location: elogin.php");
    exit();
}

$employee_id = $_SESSION['employee_id'];
$days_of_week = isset($_POST['day_of_week']) ? $_POST['day_of_week'] : [];

// Validate day names
$valid_days = ['Sunday', 'Monday', 'Tuesday', 'Wednesday', 'Thursday', 'Friday', 'Saturday'];
$days_of_week = array_intersect($days_of_week, $valid_days);

$con->begin_transaction();

try {
    // Clear existing availability for the employee
    $stmt = $con->prepare("DELETE FROM employee_availability WHERE employee_id = ?");
    $stmt->bind_param("i", $employee_id);
    $stmt->execute();
    $stmt->close(); // Close the statement after use

    // Insert new availability records
    if (!empty($days_of_week)) {
        $stmt = $con->prepare("INSERT INTO employee_availability (employee_id, day_of_week) VALUES (?, ?)");
        foreach ($days_of_week as $day) {
            $stmt->bind_param("is", $employee_id, $day);
            $stmt->execute();
        }
        $stmt->close(); // Close the statement after the loop
    }

    $con->commit();
    header("Location: editprofile.php?status=success");
} catch (Exception $e) {
    $con->rollback();
    header("Location: editprofile.php?status=error");
} finally {
    $con->close(); // Close the connection
}

exit();
?>
