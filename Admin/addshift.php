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

// Handle adding a new shift
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $start_time = $_POST['start_time'];
    $end_time = $_POST['end_time'];
    $note = $_POST['note'];

    // Add the shift
    $result = $shift->addShift($start_time, $end_time, $note);
    
    if ($result === true) {
        echo '<script>alert("Shift added successfully"); window.location.href = window.location.href;</script>';
    } else {
        echo '<script>alert("'. $result .'");</script>';
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Add Shift</title>
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
        table {
            width: 100%;
            margin-top: 20px;
            background-color: #ffffff;
            border-radius: 8px;
            box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
            overflow: hidden;
        }
        th, td {
            padding: 12px;
            text-align: left;
            border-bottom: 1px solid #ddd;
        }
        th {
            background-color: #2c3e50;
            color: #ffffff;
        }
        tr:last-child td {
            border-bottom: none;
        }
        tr:hover {
            background-color: #f1f1f1;
        }
        a.edit, a.delete {
            color: #2c3e50;
            text-decoration: none;
        }
        a.delete {
            color: #e74c3c;
        }
    </style>
</head>
<body>
    <nav>
        <div class="nav-wrapper">
            <ul id="nav-desktop" class="right hide-on-med-and-down">
                <li><a href="addash.php">Home</a></li>
                <li><a href="assign_shifts.php">Assign Shift</a></li>
                <li><a href="viewemployees.php">Employees</a></li>
                <li><a href="?action=logout">Logout</a></li>
            </ul>
        </div>
    </nav>

    <div class="container">
        <h2>Add New Shift</h2>

        <div class="add-shift-form">
            <form method="POST" action="">
                <div class="row">
                    <div class="input-field col s12 m6">
                        <input type="text" id="start_time" name="start_time" class="timepicker" required>
                        <label for="start_time">Start Time</label>
                    </div>
                    <div class="input-field col s12 m6">
                        <input type="text" id="end_time" name="end_time" class="timepicker" required>
                        <label for="end_time">End Time</label>
                    </div>
                </div>
                <div class="row">
                    <div class="input-field col s12">
                        <input type="text" id="note" name="note" placeholder="Optional note">
                        <label for="note">Note</label>
                    </div>
                </div>
                <div class="row">
                    <div class="col s12">
                        <button class="btn waves-effect waves-light" type="submit">Add Shift</button>
                    </div>
                </div>
            </form>
        </div>

        <table>
            <thead>
                <tr>
                    <th>#</th>
                    <th>Start Time</th>
                    <th>End Time</th>
                    <th>Note</th>
                    <th>Edit</th>
                    <th>Delete</th>
                </tr>
            </thead>
            <tbody>
                <?php
                $count = 0;
                $res = mysqli_query($con, 'SELECT * FROM shifts ORDER BY start_time DESC');

                while ($row = mysqli_fetch_array($res)) {
                    $count += 1;
                    ?>
                    <tr>
                        <td><?php echo $count; ?></td>
                        <td><?php echo htmlspecialchars($row['start_time']); ?></td>
                        <td><?php echo htmlspecialchars($row['end_time']); ?></td>
                        <td><?php echo htmlspecialchars($row['note']); ?></td>
                        <td><a href="editshift.php?id=<?php echo $row["id"];?>" class="edit">Edit</a></td> 
                        <td><a href="deleteshift.php?id=<?php echo $row["id"];?>" class="delete" onclick="return confirm('Are you sure you want to delete this shift?');">Delete</a></td>
                    </tr>
                    <?php
                }
                ?>
            </tbody>
        </table>
    </div>

    <script src="https://cdnjs.cloudflare.com/ajax/libs/materialize/1.0.0/js/materialize.min.js"></script>
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            // Initialize timepicker
            const elems = document.querySelectorAll('.timepicker');
            const instances = M.Timepicker.init(elems, {
                twelveHour: false, // Set to true for 12-hour format
                defaultTime: 'now', // Set to 'now' to start at current time
            });

        document.querySelector('form').addEventListener('submit', function(e) {
        const startTime = document.getElementById('start_time').value;
        const endTime = document.getElementById('end_time').value;

        if (startTime >= endTime) {
        e.preventDefault(); // Prevent form submission
        alert('Start time must be before end time.');
        }
        });

        });
    </script>
</body>
</html>
