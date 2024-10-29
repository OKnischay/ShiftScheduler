<?php 
include 'db_conn.php';
session_start();
// Check if the user is logged in
if (!isset($_SESSION['employee_id'])) {
    header("Location: elogin.php");
    exit();
}
$employee_id = $_SESSION['employee_id'];
$employee = getCurrentUser($employee_id);
$leaveRequestDetails = getLeaveRequestDetails($employee_id);
function getCurrentUser($employee_id) {
    global $con;
    $stmt = $con->prepare("SELECT first_name, last_name FROM employees WHERE id = ?");
    $stmt->bind_param("i", $employee_id);
    $stmt->execute();
    $result = $stmt->get_result();
    return $result->fetch_assoc();
}
function getLeaveRequestDetails($employee_id) {
    global $con;
    $stmt = $con->prepare("SELECT start, end, reason, status FROM leave_request WHERE employee_id = ? ORDER BY id DESC LIMIT 1");
    $stmt->bind_param("i", $employee_id);
    $stmt->execute();
    $result = $stmt->get_result();
    return $result->fetch_assoc();
}
$empName = $employee['first_name'] . " " . $employee['last_name'];
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Apply Leave | Employee Dashboard</title>
    <link href="https://fonts.googleapis.com/icon?family=Material+Icons" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/materialize/1.0.0/css/materialize.min.css" rel="stylesheet">
    <style>
        body {
            font-family: 'Roboto', sans-serif;
            background-color: #e0f7fa;
            margin: 0;
            padding: 0;
        }
        .nav-wrapper {
            background-color: #00796b;
            padding-left: 20px;
        }
        .brand-logo {
            font-size: 1.8rem;
            font-weight: bold;
            color: white;
        }
        .container {
            margin-top: 2rem;
        }
        .card {
            padding: 2rem;
            border-radius: 10px;
            box-shadow: 0 4px 8px rgba(0,0,0,0.1);
            background: #ffffff;
        }
        .card h5 {
            margin-bottom: 1.5rem;
            font-size: 1.6rem;
            color: #00796b;
        }
        .input-field input,
        .input-field select {
            color: #333;
        }
        .input-field input::placeholder,
        .input-field select {
            color: #666;
        }
        .btn {
            background-color: #00796b;
            border-radius: 5px;
            font-weight: bold;
        }
        .btn:hover {
            background-color: #004d40;
        }
        .recent-leave-request {
            margin-top: 2rem;
            border: 1px solid #00796b;
            padding: 1rem;
            border-radius: 8px;
            background-color: #e0f7fa;
        }
    </style>
</head>
<body>
    <!-- Navbar -->
    <nav>
        <div class="nav-wrapper">
            <a href="#" class="brand-logo left">Apply Leave</a>
            <ul id="nav-mobile" class="right hide-on-med-and-down">
                <li><a href="edash.php"><i class="material-icons left">home</i>Home</a></li>
                <li><a href="viewshift.php"><i class="material-icons left">calendar_today</i>View Shift</a></li>
                <li><a href="editprofile.php"><i class="material-icons left">edit</i>Edit Profile</a></li>
                <li><a href="elogout.php"><i class="material-icons left">exit_to_app</i>Logout</a></li>
            </ul>
        </div>
    </nav>

    <div class="container">
        <div class="card">
            <h5>Apply for Leave</h5>
            <form action="leaveprocess.php" method="POST">
                <input type="hidden" name="employee_id" value="<?php echo htmlspecialchars($employee_id); ?>">

                <div class="input-field">
                    <input type="text" id="reason" name="reason" required>
                    <label for="reason">Reason for Leave</label>
                </div>
                <div class="row">
                    <div class="input-field col s12 m6">
                        <input type="text" id="start" name="start" class="datepicker" required>
                        <label for="start">Start Date</label>
                    </div>
                    <div class="input-field col s12 m6">
                        <input type="text" id="end" name="end" class="datepicker" required>
                        <label for="end">End Date</label>
                    </div>
                </div>
                <button type="submit" class="btn waves-effect waves-light">Submit</button>
            </form>

            <?php if ($leaveRequestDetails): ?>
                <div class="recent-leave-request">
                    <h6>Recent Leave Request Details</h6>
                    <p><strong>Start Date:</strong> <?php echo htmlspecialchars($leaveRequestDetails['start']); ?></p>
                    <p><strong>End Date:</strong> <?php echo htmlspecialchars($leaveRequestDetails['end']); ?></p>
                    <p><strong>Reason:</strong> <?php echo htmlspecialchars($leaveRequestDetails['reason']); ?></p>
                    <p><strong>Status:</strong> <?php echo htmlspecialchars($leaveRequestDetails['status']); ?></p>
                </div>
            <?php else: ?>
                <p>You have no leave requests found.</p>
            <?php endif; ?>
        </div>
    </div>

    <script src="https://cdnjs.cloudflare.com/ajax/libs/materialize/1.0.0/js/materialize.min.js"></script>
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            var elems = document.querySelectorAll('select');
            M.FormSelect.init(elems, {});

            // Initialize datepicker
            var dateElems = document.querySelectorAll('.datepicker');
            M.Datepicker.init(dateElems, {
                format: 'yyyy-mm-dd',
                defaultDate: new Date(),
                setDefaultDate: true,
                autoClose: true,
                showClearBtn: true,
                minDate: new Date() // Prevent past dates
            });
        });
    </script>
</body>
</html>
