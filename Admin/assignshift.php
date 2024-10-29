<?php
include '../db_conn.php';
include 'shift.php';
include 'employee.php';

if (session_status() == PHP_SESSION_NONE) {
    session_start();
}

// Check if the user is logged in as an admin
if (!isset($_SESSION['admin_email'])) {
    header("Location: alogin.php");
    exit();
}

// Handle logout
if (isset($_GET['action']) && $_GET['action'] === 'logout') {
    session_unset();
    session_destroy();
    header("Location: alogin.php");
    exit();
}

// Initialize messages array
if (!isset($_SESSION['messages'])) {
    $_SESSION['messages'] = [];
}

function addMessage($message, $type = 'error') {
    $_SESSION['messages'][] = ['text' => $message, 'type' => $type];
}

function handleError($message) {
    addMessage($message, 'error');
    error_log($message);
}

$admin_email = $_SESSION['admin_email'];

// Check database connection
if (!isset($con) || $con->connect_error) {
    die("Database connection failed: " . ($con->connect_error ?? "Unknown error"));
}

$shift = new Shift($con);
$employee = new Employee($con);

// Get all employees
try {
    $employees = $shift->getAllEmployees();
} catch (Exception $e) {
    handleError("Error fetching employees: " . $e->getMessage());
    $employees = [];
}

// Get all shifts
try {
    $all_shifts = $shift->getAllShifts();
} catch (Exception $e) {
    handleError("Error fetching shifts: " . $e->getMessage());
    $all_shifts = [];
}

// Handle form submission for assigning shifts
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    try {
        $employee_id = filter_input(INPUT_POST, 'employee_id', FILTER_SANITIZE_NUMBER_INT);
        $shift_id = filter_input(INPUT_POST, 'shift_id', FILTER_SANITIZE_NUMBER_INT);
        $date = filter_input(INPUT_POST, 'date', FILTER_CALLBACK, [
            'options' => function($date) {
                return (DateTime::createFromFormat('Y-m-d', $date) !== false) ? $date : false;
            }
        ]);

        if (!$employee_id || !$shift_id || $date === false) {
            throw new Exception("Please fill all fields with valid data.");
        }

        if (!$employee->getEmployeeById($employee_id)) {
            throw new Exception("Error: Employee does not exist.");
        }

        $shift_details = $shift->getShiftById($shift_id);
        if (!$shift_details) {
            throw new Exception("Error: Shift does not exist.");
        }

        $shift_start_time = $shift_details['start_time'];
        $shift_end_time = $shift_details['end_time'];

        // Check employee availability by day of the week
        $day_of_week = date('w', strtotime($date)); // 0 (for Sunday) through 6 (for Saturday)
        $availability = $employee->getEmployeeAvailability($employee_id, $date);

        // Check if employee is available on the given day
        $is_available = false;
        if (!empty($availability)) {
            $is_available = true; // The employee is available for the entire day
        }

        if (!$is_available) {
            throw new Exception("Error: Employee is not available for this shift on the selected date.");
        }

        // Check if employee is on approved leave
        $leave_requests = $shift->getEmployeeLeaveForWeek($employee_id, date('Y-m-d', strtotime($date . ' -' . (date('N', strtotime($date)) % 7) . ' days')));
        if (!empty($leave_requests)) {
            throw new Exception("Error: Employee is on approved leave during this week.");
        }

        // Check for shift conflicts
        $existing_shifts = $shift->getShiftsByEmployeeAndDate($date);
        foreach ($existing_shifts as $existing_shift) {
            $existing_start = new DateTime($existing_shift['start_time']);
            $existing_end = new DateTime($existing_shift['end_time']);
            $new_start = new DateTime($shift_start_time);
            $new_end = new DateTime($shift_end_time);
            
            if ($new_start < $existing_end && $new_end > $existing_start) {
                throw new Exception("Error: Shift overlaps with an existing shift for this employee on the same day.");
            }
        }

        // Check maximum hours constraint
        $duration = $shift->getDurationInHours($shift_start_time, $shift_end_time);
        $total_hours = $shift->getTotalHoursForEmployee($employee_id, $date);
        if ($total_hours + $duration > $shift->max_hours_per_day) {
            throw new Exception("Error: Assigning this shift exceeds maximum allowed working hours per day.");
        }

        // Assign the shift
        $con->begin_transaction();

        $result = $shift->addShift($shift_start_time, $shift_end_time, "Assigned to employee ID: $employee_id");
        if ($result === true) {
            // If addShift was successful, now we need to associate it with the employee
            $new_shift_id = $con->insert_id;
            $schedule_result = $con->query("INSERT INTO schedule (employee_id, shift_id, date) VALUES ($employee_id, $new_shift_id, '$date')");
            if ($schedule_result) {
                $con->commit();
                addMessage("Shift assigned successfully.", 'success');
            } else {
                throw new Exception("Error: Shift created but not assigned to employee.");
            }
        } else {
            throw new Exception($result); // If addShift returns an error message
        }
        
    } catch (Exception $e) {
        $con->rollback();
        handleError($e->getMessage());
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Assign Shift</title>
    <link href="https://fonts.googleapis.com/icon?family=Material+Icons" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/materialize/1.0.0/css/materialize.min.css" rel="stylesheet">
    <style>
        body {
            font-family: 'Roboto', sans-serif;
            margin: 0;
            padding: 0;
            background-color: #fafafa;
            color: #333;
        }
        .navbar {
            background-color: #00796b;
        }
        .navbar .brand-logo {
            margin-left: 20px;
        }
        .navbar .right li a {
            color: white;
            font-weight: 500;
        }
        .content {
            margin: 30px;
            padding: 20px;
            background-color: white;
            box-shadow: 0 2px 10px rgba(0, 0, 0, 0.1);
            border-radius: 8px;
        }
        h4 {
            margin-bottom: 20px;
            color: #00796b;
        }
        .input-field label {
            color: #00796b;
        }
        .input-field input[type="date"]:focus + label {
            color: #00796b;
        }
        .input-field input[type="date"]:focus {
            border-bottom: 1px solid #00796b;
            box-shadow: 0 1px 0 0 #00796b;
        }
        .input-field .select-wrapper input.select-dropdown {
            border-bottom: 1px solid #00796b;
        }
        .btn {
            background-color: #00796b;
            width: 100%;
            margin-top: 20px;
            border-radius: 4px;
        }
        .btn:hover {
            background-color: #004d40;
        }
        .messages {
            margin-bottom: 20px;
        }
        .message {
            padding: 10px;
            border-radius: 4px;
            margin-bottom: 10px;
        }
        .message.error {
            background-color: #ffcdd2;
            color: #b71c1c;
        }
        .message.success {
            background-color: #c8e6c9;
            color: #1b5e20;
        }
    </style>
</head>
<body>
    <!-- Navbar -->
    <nav class="navbar">
        <div class="nav-wrapper">
            <ul id="nav-mobile" class="right hide-on-med-and-down">
                <li><a href="addash.php">Home</a></li>
                <li><a href="addshift.php">Add Shift</a></li>
                <li><a href="viewemployees.php">Employees</a></li>
                <li><a href="assignshift.php?action=logout">Logout</a></li>
            </ul>
        </div>
    </nav>

    <!-- Content -->
    <div class="content">
        <h4>Assign Shift</h4>
        
        <?php if (!empty($_SESSION['messages'])): ?>
            <div class="messages">
                <?php foreach ($_SESSION['messages'] as $message): ?>
                    <div class="message <?= $message['type'] ?>">
                        <?= htmlspecialchars($message['text']) ?>
                    </div>
                <?php endforeach; ?>
            </div>
            <?php $_SESSION['messages'] = []; ?>
        <?php endif; ?>
        
        <form method="POST" action="assignshift.php" id="assignShiftForm">
            <div class="input-field col s12">
                <select id="employee_id" name="employee_id" required>
                    <option value="" disabled selected>Select Employee</option>
                    <?php foreach ($employees as $emp): ?>
                        <option value="<?= htmlspecialchars($emp['id']) ?>"><?= htmlspecialchars($emp['first_name'] . ' ' . $emp['last_name']) ?></option>
                    <?php endforeach; ?>
                </select>
                <label for="employee_id">Employee</label>
            </div>
            <div class="input-field col s12">
                <select id="shift_id" name="shift_id" required>
                    <option value="" disabled selected>Select Shift</option>
                    <?php foreach ($all_shifts as $sh): ?>
                        <option value="<?= htmlspecialchars($sh['id']) ?>"><?= htmlspecialchars($sh['start_time'] . ' - ' . $sh['end_time'] . ' (' . $sh['note'] . ')') ?></option>
                    <?php endforeach; ?>
                </select>
                <label for="shift_id">Shift</label>
            </div>
            <div class="input-field col s12">
                <input type="date" id="date" name="date" required>
                <label for="date">Select Date</label>
            </div>
            <button type="submit" class="btn waves-effect waves-light">Assign Shift</button>
        </form>
    </div>

    <!-- Scripts -->
    <script src="https://cdnjs.cloudflare.com/ajax/libs/materialize/1.0.0/js/materialize.min.js"></script>
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            var elems = document.querySelectorAll('select');
            M.FormSelect.init(elems, {});

            document.getElementById('assignShiftForm').addEventListener('submit', function(e) {
                if (!confirm('Are you sure you want to assign this shift?')) {
                    e.preventDefault();
                }
            });
        });
    </script>
</body>
</html>