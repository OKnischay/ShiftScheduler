<?php
require_once('db_conn.php');

// Redirect function with JavaScript alert
function redirect($url, $message = '') {
    if ($message) {
        echo "<script>alert('" . addslashes($message) . "'); window.location.href = '$url';</script>";
    } else {
        header("Location: $url");
    }
    exit();
}

// Get the form data
$employee_id = isset($_POST['employee_id']) ? $_POST['employee_id'] : (isset($_GET['employee_id']) ? $_GET['employee_id'] : null);
$start_date = $_POST['start'];
$end_date = $_POST['end'];
$reason = $_POST['reason'];
$status = 'Pending'; // Default status

if (!$employee_id) {
    redirect('applyleave.php', 'No employee ID provided');
}

// Validate dates
if (strtotime($start_date) > strtotime($end_date)) {
    redirect('applyleave.php', 'Start date cannot be greater than end date');
}

// Prepare the SQL statement
$sql = "INSERT INTO `leave_request` (employee_id, start, end, reason, status) VALUES (?, ?, ?, ?, ?)";

// Prepare the statement
$stmt = mysqli_prepare($con, $sql);

if ($stmt) {
    // Bind the parameters
    mysqli_stmt_bind_param($stmt, "issss", $employee_id, $start_date, $end_date, $reason, $status);

    // Execute the statement
    if (mysqli_stmt_execute($stmt)) {
        redirect('applyleave.php', 'Leave request submitted successfully');
    } else {
        redirect('applyleave.php', 'Database error: ' . mysqli_error($con));
    }

    // Close the statement
    mysqli_stmt_close($stmt);
} else {
    redirect('applyleave.php', 'Prepare failed: ' . mysqli_error($con));
}

// Close the connection
mysqli_close($con);
?>