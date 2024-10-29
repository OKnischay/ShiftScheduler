<?php
$host = 'localhost';
$db = 'shift_scheduler';
$user = 'root';
$pass = '';

$conn = new mysqli($host, $user, $pass, $db);

if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Check tables
$tables = ['admins', 'employees', 'employee_availability', 'leave_request', 'schedule', 'shifts'];
foreach ($tables as $table) {
    $result = $conn->query("SHOW TABLES LIKE '$table'");
    if ($result->num_rows === 0) {
        echo "Table $table does not exist.\n";
    } else {
        echo "Table $table exists.\n";
    }
}

// Check data
$result = $conn->query("SELECT * FROM employees LIMIT 1");
if ($result && $result->num_rows > 0) {
    echo "Data in employees table:\n";
    while ($row = $result->fetch_assoc()) {
        print_r($row);
    }
} else {
    echo "No data found in employees table.\n";
}

$conn->close();
?>
