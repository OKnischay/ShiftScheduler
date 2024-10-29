<?php
include '../db_conn.php';
include 'shift.php';

session_start();

// Check if the user is logged in as an admin
if (!isset($_SESSION['admin_email'])) {
    header("Location: alogin.php");
    exit();
}

$shift = new Shift($con);

// Handle form submission for assigning shifts
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['run_csp'])) {
        // Run the automatic shift assignment (CSP) process
        $week_start = $_POST['week_start'] ?? date('Y-m-d', strtotime('this week monday'));
        $result = $shift->runCSPAssignment($week_start);
        $message = $result;
    } else {
        // Manual shift assignment
        $employee_id = $_POST['employee_id'] ?? '';
        $shift_id = $_POST['shift_id'] ?? '';
        $date = $_POST['date'] ?? '';
        
        if (!empty($employee_id) && !empty($shift_id) && !empty($date)) {
            // Check if the selected date is in the past
            if (strtotime($date) < strtotime('today')) {
                $message = "You cannot assign shifts for past dates.";
            } else {
                // Use the addShift method to assign the shift
                $result = $shift->addShift($shift_id, $employee_id, $date);
                $message = $result === true ? "Shift assigned successfully." : $result;
            }
        } else {
            $message = "Please fill all fields.";
        }
    }
}


// Get the week start and end dates
$week_start = isset($_GET['week_start']) ? $_GET['week_start'] : date('Y-m-d', strtotime('this week monday'));
$week_end = date('Y-m-d', strtotime($week_start . ' +6 days'));

// Get all employees
$employees = $shift->getAllEmployees();

// Get all shifts for the week
$weekly_shifts = $shift->getShiftsForWeek($week_start);


// Get assigned shifts for the week
$assigned_shifts = [];
for ($i = 0; $i < 7; $i++) {
    $current_date = date('Y-m-d', strtotime($week_start . " +$i days"));
    // Use the correct method to fetch shifts along with employee details
    $assigned_shifts[$current_date] = $shift->getShiftsByEmployeeAndDate($current_date);
}


?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Assign Shifts</title>
    <link href="https://cdnjs.cloudflare.com/ajax/libs/materialize/1.0.0/css/materialize.min.css" rel="stylesheet">
    <style>
        .day-column { border: 1px solid #ddd; padding: 10px; }
        .shift-card { margin-bottom: 10px; padding: 10px; border-radius: 4px; background-color: #e3f2fd; }
    </style>
</head>
<body>
    <div class="container">
        <h2>Job Schedule</h2>
        
        <?php if (isset($message)): ?>
            <div class="card-panel <?= strpos($message, 'successfully') !== false ? 'green' : 'red' ?> lighten-4">
                <?= htmlspecialchars($message) ?>
            </div>
        <?php endif; ?>

        <div class="row">
            <div class="col s12">
                <a href="?week_start=<?= date('Y-m-d', strtotime($week_start . ' -1 week')) ?>" class="btn">Previous Week</a>
                <span class="btn disabled"><?= date('M d', strtotime($week_start)) ?> - <?= date('M d', strtotime($week_end)) ?></span>
                <a href="?week_start=<?= date('Y-m-d', strtotime($week_start . ' +1 week')) ?>" class="btn">Next Week</a>
            </div>
        </div>

        <div class="row">
            <?php
            for ($i = 0; $i < 7; $i++) {
                $current_date = date('Y-m-d', strtotime($week_start . " +$i days"));
                $day_name = date('D n/j', strtotime($current_date));
            ?>
                <div class="col s12 m6 l1 day-column">
                    <h5><?= $day_name ?></h5>
                        <?php foreach ($assigned_shifts[$current_date] as $assigned_shift): ?>
                        <div class="shift-card">
                        <strong><?= formatTime($assigned_shift['start_time']) ?> - <?= formatTime($assigned_shift['end_time']) ?></strong><br>
                        <?= htmlspecialchars($assigned_shift['employee_name']) ?> <!-- Displaying employee name -->
                        </div>
                        <?php endforeach; ?>
                        
                </div>
            <?php } ?>
        </div>

        <h3>Assign Shift</h3>
        <form method="POST" action="">
            <div class="input-field">
                <select name="employee_id" required>
                    <option value="" disabled selected>Choose employee</option>
                    <?php foreach ($employees as $employee): ?>
                        <option value="<?= $employee['id'] ?>"><?= htmlspecialchars($employee['first_name'] . ' ' . $employee['last_name']) ?></option>
                    <?php endforeach; ?>
                </select>
                <label>Employee</label>
            </div>
            <div class="input-field">
                <select name="shift_id" required>
                    <option value="" disabled selected>Choose shift</option>
                    <?php foreach ($weekly_shifts as $shift): ?>
                        <option value="<?= $shift['id'] ?>"><?= formatTime($shift['start_time']) ?> - <?= formatTime($shift['end_time']) ?> (<?= htmlspecialchars($shift['note']) ?>)</option>
                    <?php endforeach; ?>
                </select>
                <label>Shift</label>
            </div>
            <div class="input-field">
                <input type="date" name="date" min="<?= $week_start ?>" max="<?= $week_end ?>" required>
                <label>Date</label>
            </div>
            <button type="submit" class="btn waves-effect waves-light">Assign Shift</button>
        </form>

        <h3>Run Automatic Shift Assignment (CSP)</h3>
        <form method="POST" action="">
            <input type="hidden" name="run_csp" value="1">
            <button type="submit" class="btn waves-effect waves-light">Run CSP Assignment</button>
        </form>
    </div>

    <script src="https://cdnjs.cloudflare.com/ajax/libs/materialize/1.0.0/js/materialize.min.js"></script>
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            var elems = document.querySelectorAll('select');
            M.FormSelect.init(elems);
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
