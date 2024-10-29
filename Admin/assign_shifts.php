<?php
include '../db_conn.php';
include 'shifts.php';
include 'employee.php';
include 'ShiftScheduler.php';

session_start();

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

$admin_email = $_SESSION['admin_email'];
$shift = new Shift($con);
$employee = new Employee($con);
$shiftScheduler = new ShiftScheduler($shift);

// Get all employees
$employees = $employee->getAllEmployees();

// Get all shifts
$all_shifts = $shift->getAllShifts();

// Handle form submission for assigning shifts
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    
error_log("Form submitted. POST data: " . print_r($_POST, true));
    $assignment_type = filter_input(INPUT_POST, 'assignment_type', FILTER_SANITIZE_FULL_SPECIAL_CHARS);
    $date = filter_input(INPUT_POST, 'date', FILTER_SANITIZE_FULL_SPECIAL_CHARS);
    error_log("Assignment Type: " . $assignment_type);
    if ($assignment_type === 'manual') {
        $employee_id = filter_input(INPUT_POST, 'employee_id', FILTER_SANITIZE_NUMBER_INT);
        $shift_id = filter_input(INPUT_POST, 'shift_id', FILTER_SANITIZE_NUMBER_INT);

        // Check if all required fields are filled
        if ($employee_id && $shift_id && $date) {
            // Check if the employee exists
            $employee_data = $employee->getEmployeeById($employee_id);
            if (!$employee_data) {
                addMessage("Error: Employee does not exist.");
            } else {
                // Check if the shift exists
                $shift_details = $shift->getShiftById($shift_id);
                if (!$shift_details) {
                    addMessage("Error: Shift does not exist.");
                } else {
                    $shift_start_time = $shift_details['start_time'];
                    $shift_end_time = $shift_details['end_time'];

                    if ($shift_end_time === '00:00:00') {
                        $shift_end_time = '23:59:59'; // Adjust to end of the day
                    }

                    // Get employee availability
                    $availability = $employee->getEmployeeAvailability($employee_id, $date);

                    if (empty($availability)) {
                        addMessage("Error: Employee is not available for this shift on the selected date.");
                    } elseif ($shift->isEmployeeOnLeave($employee_id, $date)) {
                        addMessage("Error: Employee is on approved leave on the selected date.");
                    } else {
                        // Fetch existing shifts for the employee on the selected date
                        $existing_shifts = $shift->getShiftsByEmployeeAndDate($employee_id, $date);

                        $conflict = false; // Initialize conflict variable

                        // Iterate through existing shifts to check for conflicts
                        foreach ($existing_shifts as $existing_shift) {
                            $existing_start = $existing_shift['start_time'];
                            $existing_end = $existing_shift['end_time'];

                            // Debug output for existing shifts
                            error_log("Debug: Existing shift start time: " . $existing_start);
                            error_log("Debug: Existing shift end time: " . $existing_end);

                            // Check for overlap
                            if (($shift_start_time < $existing_end) && ($shift_end_time > $existing_start)) {
                                $conflict = true; // Set conflict if there's an overlap
                                break; // No need to check further
                            }
                        }

                        if ($conflict) {
                            addMessage("Error: Shift overlaps with an existing shift on the same day.");
                        } elseif (!$shift->checkMaxHours($employee_id, $shift_start_time, $shift_end_time, $date)) {
                            addMessage("Error: Assigning this shift exceeds maximum allowed daily working hours.");
                        } elseif (!$shift->checkMaxWeeklyHours($employee_id, $shift_start_time, $shift_end_time, $date)) {
                            addMessage("Error: Assigning this shift exceeds maximum allowed weekly working hours.");
                        } else {
                            // Assign the shift if all checks pass
                            $result = $shift->assignShift($employee_id, $shift_id, $date);
                            $resultArray = json_decode($result, true);
                            if (isset($resultArray['success'])) {
                                addMessage("Shift assigned successfully.", 'success');
                            } else {
                                addMessage("Error assigning shift: " . $result);
                            }
                        }
                    }
                }
            }
        } else {
            addMessage("Please fill all fields before submitting.");
        }
    } elseif ($assignment_type === 'csp') {
        // Automatic assignment using CSP 
        if (empty($date)) {
            addMessage("Please select a date for automatic assignment.", 'error');
        } else {
        error_log("Starting automatic assignment");
        $result = $shiftScheduler->assignShifts($employees, $date);
        error_log("Automatic assignment result: " . print_r($result, true));
        
        if (isset($result['success'])) {
            addMessage($result['success'], 'success');
            if (!empty($result['errors'])) {
                foreach ($result['errors'] as $error) {
                    addMessage($error, 'error');
                }
            }
        } else if (isset($result['error'])) {
            addMessage($result['error'], 'error');
            if (isset($result['details'])) {
                foreach ($result['details'] as $detail) {
                    addMessage($detail, 'error');
                }
            }
        }
    }
}
}
// Get the week start and end dates
$week_start = isset($_GET['week_start']) ? $_GET['week_start'] : date('Y-m-d', strtotime('last week sunday'));
$week_end = date('Y-m-d', strtotime($week_start . ' +6 days'));

// Get all employees
// $employees = $shift->getAllEmployees();

// Get all shifts for the week
$weekly_shifts = $shift->getShiftsForWeek($week_start);


// Get assigned shifts for the week
$assigned_shifts = [];

for ($i = 0; $i < 7; $i++) {
    $current_date = date('Y-m-d', strtotime($week_start . " +$i days"));
    
    foreach ($employees as $employee) {
        $employee_id = $employee['id'];
        
        // Get the shifts assigned to this employee on this date
        $shifts = $shift->getShiftsByEmployeeAndDate($employee_id, $current_date);
        
        // Store the shifts in a multidimensional array with employee and date as keys
        $assigned_shifts[$employee_id][$current_date] = $shifts;
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
            font-family: 'Roboto', sans-serif;
        }
        .navbar .brand-logo {
            margin-left: 20px;
        }
        .navbar .right li a {
            color: white;
            font-weight: 500;
        }

        #nav-desktop {
        list-style: none;
    }
    #nav-desktop li {
        display: inline-block;
        margin-left: 20px;
    }
    #nav-desktop li a {
        font-size: 1.1rem;
        color: white;
    }
    #nav-desktop li a:hover {
        background-color: rgba(255, 255, 255, 0.2);
        border-radius: 5px;
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
        .day-column { border: 1px solid #ddd; padding: 10px; }
        .shift-card { margin-bottom: 10px; padding: 10px; border-radius: 4px; background-color: #e3f2fd; }
    </style>
</head>
<body>
    <!-- Navbar -->
    <nav class="navbar  teal darken-1">
        <div class="nav-wrapper">
            <ul id="nav-desktop" class="right hide-on-med-and-down">
                <li><a href="addash.php" class="waves-effect waves-light">Home</a></li>
                <li><a href="addshift.php" class="waves-effect waves-light">Add Shift</a></li>
                <li><a href="viewemployees.php" class="waves-effect waves-light">Employees</a></li>
                <li><a href="assign_shifts.php?action=logout" class="waves-effect waves-light">Logout</a></li>
            </ul>
        </div>
    </nav>
    
    <!-- Content -->
    <div class="content">
    <div class="card-panel teal lighten-5">
        <h4 class="center-align">Assign Shift</h4>

        <?php if (!empty($_SESSION['messages'])): ?>
            <div class="messages">
                <?php foreach ($_SESSION['messages'] as $message): ?>
                    <div class="message card-panel <?= $message['type'] ?> lighten-4">
                        <i class="material-icons left"><?= $message['type'] == 'error' ? 'error_outline' : 'check_circle' ?></i>
                        <?= htmlspecialchars($message['text']) ?>
                    </div>
                <?php endforeach; ?>
            </div>
            <?php $_SESSION['messages'] = []; ?>
        <?php endif; ?>

        <form method="POST" action="assign_shifts.php" class="row">
            <div class="input-field col s12">
                <i class="material-icons prefix">assignment</i>
                <select id="assignment_type" name="assignment_type" required>
                    <option value="manual" selected>Manual Assignment</option>
                    <option value="csp">Automatic Assignment</option>
                </select>
                <label for="assignment_type">Assignment Type</label>
            </div>

            <div id="manual_fields" class="row">
                <div class="input-field col s12">
                    <i class="material-icons prefix">person</i>
                    <select id="employee_id" name="employee_id" required>
                        <option value="" disabled selected>Select Employee</option>
                        <?php foreach ($employees as $emp): ?>
                            <option value="<?= htmlspecialchars($emp['id']) ?>"><?= htmlspecialchars($emp['first_name'] . ' ' . $emp['last_name']) ?></option>
                        <?php endforeach; ?>
                    </select>
                    <label for="employee_id">Select Employee</label>
                </div>

                <div class="input-field col s12">
                    <i class="material-icons prefix">schedule</i>
                    <select id="shift_id" name="shift_id" required>
                        <option value="" disabled selected>Select Shift</option>
                        <?php foreach ($all_shifts as $sh): ?>
                            <option value="<?= htmlspecialchars($sh['id']) ?>"><?= htmlspecialchars($sh['start_time'] . ' - ' . $sh['end_time'] . ' (' . $sh['note'] . ')') ?></option>
                        <?php endforeach; ?>
                    </select>
                    <label for="shift_id">Select Shift</label>
                </div>
            </div>
            <div class="input-field col s12">
                <i class="material-icons prefix">event</i>
                <input type="text" id="date" name="date" class="datepicker" required>
                <label for="date">Select Date</label>
            </div>
            <div class="col s12 center-align">
                <button type="submit" class="btn waves-effect waves-light teal darken-3">
                    <i class="material-icons left">send</i>Assign Shift
                </button>
            </div>
        </form>
    </div>
  </div>

        <h2 class="center-align teal-text text-darken-2">Weekly Shifts</h2>

<div class="row center-align">
    <div class="col s12">
        <div style="display: flex; justify-content: center; align-items: center;">
            <a href="?week_start=<?= date('Y-m-d', strtotime($week_start . ' -1 week')) ?>" class="btn teal lighten-1" style="margin-right: 5px;">Previous Week</a>
            <span class="btn disabled teal lighten-1" style="margin-right: 5px;"><?= date('M d', strtotime($week_start)) ?> - <?= date('M d', strtotime($week_end)) ?></span>
            <a href="?week_start=<?= date('Y-m-d', strtotime($week_start . ' +1 week')) ?>" class="btn teal lighten-1">Next Week</a>
        </div>
    </div>
</div>

<div class="row" style="display: flex; justify-content: space-between; flex-wrap: nowrap;">
    <?php
    $colors = ['#e1f5fe', '#ffe0b2', '#f0f4c3', '#ffccbc', '#c5cae9', '#f8bbd0', '#bbdefb']; // Different colors for each day
    for ($i = 0; $i < 7; $i++): 
        $current_date = date('Y-m-d', strtotime($week_start . " +$i days"));
        $day_name = date('D n/j', strtotime($current_date));
        $bg_color = $colors[$i % count($colors)]; // Cycle through colors
    ?>
        <div class="col s12 m6 l2 day-column" style="padding: 10px; background-color: <?= $bg_color ?>; border-radius: 5px; margin: 5px;">
            <h5 class="center-align teal-text"><?= $day_name ?></h5>

            <?php foreach ($employees as $employee): ?>
                <?php if (!empty($assigned_shifts[$employee['id']][$current_date])): ?>
                    <div class="employee-card" style="padding: 10px; margin-top: 5px;">
                        <strong><?= htmlspecialchars($employee['first_name'] . ' ' . $employee['last_name']) ?></strong>
                        <?php foreach ($assigned_shifts[$employee['id']][$current_date] as $assigned_shift): ?>
                            <div class="shift-card" style="padding: 5px; border: 1px solid #ccc; border-radius: 3px; margin-top: 5px;">
                                <strong><?= formatTime($assigned_shift['start_time']) ?> - <?= formatTime($assigned_shift['end_time']) ?></strong><br>
                                <span><?= htmlspecialchars($assigned_shift['note']) ?></span>
                            </div>
                        <?php endforeach; ?>
                    </div>
                <?php endif; ?>
            <?php endforeach; ?>
        </div>
    <?php endfor; ?>
</div>

    </div>

    <!-- Scripts -->
    <script src="https://cdnjs.cloudflare.com/ajax/libs/materialize/1.0.0/js/materialize.min.js"></script>
    <script>
      document.addEventListener('DOMContentLoaded', function() {
    // Initialize select elements and datepicker
    var elems = document.querySelectorAll('select');
    M.FormSelect.init(elems, {});

    var dateElems = document.querySelectorAll('.datepicker');
    M.Datepicker.init(dateElems, {
        format: 'yyyy-mm-dd',
        defaultDate: new Date(),
        setDefaultDate: true,
        autoClose: true,
        showClearBtn: true,
        minDate: new Date() // Set the minimum selectable date
    });

    var assignmentType = document.getElementById('assignment_type');
    var manualFields = document.getElementById('manual_fields');

    assignmentType.addEventListener('change', function() {
        if (this.value === 'manual') {
            manualFields.style.display = 'block';
            document.getElementById('employee_id').setAttribute('required', 'required');
            document.getElementById('shift_id').setAttribute('required', 'required');
        } else {
            manualFields.style.display = 'none';
            document.getElementById('employee_id').removeAttribute('required');
            document.getElementById('shift_id').removeAttribute('required');
        }
    });

    // Form submission validation
    var form = document.querySelector('form');
    form.addEventListener('submit', function(event) {
        // Check if the assignment type is automatic
        if (assignmentType.value === 'csp') {
            // Only validate the date field
            var dateField = document.getElementById('date');
            if (!dateField.value) {
                event.preventDefault(); // Prevent form submission
                alert("Please select a date for automatic assignment.");
                dateField.focus();
            }
        }
    });
});

</script>
</body>
</html>
<?php
// Helper function to format time
function formatTime($time) {
    return date('g:ia', strtotime($time));
}
?>
