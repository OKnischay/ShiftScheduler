<?php
// Including the database connection file
include("../db_conn.php");

// Getting the employee_id from the URL
$employee_id = $_GET['id'];

// Updating the leave request status to 'Rejected' for the given employee
$result = mysqli_query($con, "UPDATE `leave_request` SET `status`='Rejected' WHERE `employee_id` = $employee_id");

// Redirecting to empleave.php
header("Location: empleave.php");
?>
