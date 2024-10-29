<?php
include '../db_conn.php';
include 'shifts.php';

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

$admin_email = $_SESSION['admin_email'];
$shift = new Shift($con);

// Handle updating a shift
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $id = $_POST['id'];
    $start_time = $_POST['start_time'];
    $end_time = $_POST['end_time'];
    $note = $_POST['note'];

    // Update the shift
    $result = $shift->updateShift($id, $start_time, $end_time, $note);

    if ($result === true) {
        echo '<script>alert("Shift updated successfully"); window.location.href = "addshift.php";</script>';
    } else {
        echo '<script>alert("'. $result .'");</script>';
    }
}

// Fetch the shift data to populate the form
$id = $_GET['id'];
$query = "SELECT * FROM shifts WHERE id = ?";
$stmt = $con->prepare($query);
$stmt->bind_param("i", $id);
$stmt->execute();
$result = $stmt->get_result();
$shift_data = $result->fetch_assoc();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Edit Shift</title>
    <link href="https://fonts.googleapis.com/icon?family=Material+Icons" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/materialize/1.0.0/css/materialize.min.css" rel="stylesheet">
    <style>
        body {
            font-family: 'Roboto', sans-serif;
            background-color: #f5f5f5;
            margin: 0;
            padding: 0;
        }
        nav {
            background-color: #2c3e50;
        }
        nav a {
            color: #ecf0f1;
            text-decoration: none;
            padding: 0 15px;
            font-weight: 500;
        }
        .container {
            margin-top: 30px;
            width: 80%;
            max-width: 900px;
            margin-left: auto;
            margin-right: auto;
        }
        h2 {
            color: #34495e;
            font-size: 24px;
            margin-bottom: 20px;
            text-align: center;
        }
        .add-shift-form {
            background: #ffffff;
            padding: 25px;
            border-radius: 8px;
            box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
        }
        .add-shift-form input, .add-shift-form button {
            margin-bottom: 15px;
            font-size: 16px;
        }
        .add-shift-form button {
            background-color: #2c3e50;
            width: 100%;
        }
    </style>
</head>
<body>
    <nav>
        <div class="nav-wrapper">
            <ul id="nav-mobile" class="right hide-on-med-and-down">
                <li><a href="addash.php">Home</a></li>
                <li><a href="assign_shifts.php">Assign Shift</a></li>
                <li><a href="viewemployee.php">Employees</a></li>
                <li><a href="?action=logout">Logout</a></li>
            </ul>
        </div>
    </nav>

    <div class="container">
        <h2>Edit Shift</h2>

        <div class="add-shift-form">
            <form method="POST" action="">
                <input type="hidden" name="id" value="<?php echo htmlspecialchars($shift_data['id']); ?>">
                <div class="row">
                    <div class="input-field col s12 m6">
                        <input type="text" id="start_time" name="start_time" class="timepicker" value="<?php echo htmlspecialchars($shift_data['start_time']); ?>" required>
                        <label for="start_time">Start Time</label>
                    </div>
                    <div class="input-field col s12 m6">
                        <input type="text" id="end_time" name="end_time" class="timepicker" value="<?php echo htmlspecialchars($shift_data['end_time']); ?>" required>
                        <label for="end_time">End Time</label>
                    </div>
                </div>
                <div class="row">
                    <div class="input-field col s12">
                        <input type="text" id="note" name="note" value="<?php echo htmlspecialchars($shift_data['note']); ?>" placeholder="Optional note">
                        <label for="note">Note</label>
                    </div>
                </div>
                <div class="row">
                    <div class="col s12">
                        <button class="btn waves-effect waves-light" type="submit">Update Shift</button>
                    </div>
                </div>
            </form>
        </div>
    </div>

    <script>
    document.addEventListener('DOMContentLoaded', function() {
        var elems = document.querySelectorAll('.timepicker');
        var instances = M.Timepicker.init(elems, {
            twelveHour: false, // 24-hour format
            defaultTime: 'now',
        });

        const form = document.querySelector('form');
        form.addEventListener('submit', function(event) {
            const startTime = document.getElementById('start_time').value;
            const endTime = document.getElementById('end_time').value;

            if (startTime && endTime) {
                const start = new Date(`1970-01-01T${startTime}:00`);
                const end = new Date(`1970-01-01T${endTime}:00`);

                // Check if end time is before start time, meaning it crosses midnight
                if (end < start) {
                    end.setDate(end.getDate() + 1); // Move end time to the next day
                }

                const diff = (end - start) / 1000 / 60 / 60; // Convert milliseconds to hours

                if (diff > 8) {
                    alert("Error: Shift duration must not exceed 8 hours.");
                    event.preventDefault(); // Prevent form submission
                }
            }
        });
    });
    </script>

    <script src="https://cdnjs.cloudflare.com/ajax/libs/materialize/1.0.0/js/materialize.min.js"></script>
</body>
</html>
