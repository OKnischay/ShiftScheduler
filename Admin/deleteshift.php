<?php
include '../db_conn.php'; 
include 'Shifts.php';

session_start();

// Check if the user is logged in as an admin
if (!isset($_SESSION['admin_email'])) {
    header("Location: alogin.php");
    exit();
}

$shift = new Shift($con);

if (isset($_GET['id'])) {
    $shiftID = $_GET['id'];
    $shift->deleteShift($shiftID);
}

header("Location: addshift.php");
exit();
?>
