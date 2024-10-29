<?php
// Include necessary files
include '../db_conn.php'; // Database connection
include 'shifts.php'; // Include the Shift class

// Create an instance of the Shift class
$shift = new Shift($con); // Assume $db is your PDO instance

// Specify the date you want to test
$dateToTest = '2024-09-30'; // Change this date based on your test data

// Call the getExistingAssignments function
$assignments = $shift->getExistingAssignments($dateToTest);

// Output the results
if (!empty($assignments)) {
    echo "Existing assignments for $dateToTest:\n";
    print_r($assignments);
} else {
    echo "No existing assignments found for $dateToTest.\n";
}
?>
