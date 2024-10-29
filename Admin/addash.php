<?php
include '../db_conn.php'; 
include 'shifts.php';

session_start();
date_default_timezone_set('Asia/Kathmandu');
if (!isset($_SESSION['admin_email'])) {
    header("Location: alogin.php");
    exit();
}

if (isset($_GET['action']) && $_GET['action'] === 'logout') {
    session_unset();
    session_destroy();
    header("Location: alogin.php");
    exit();
}

$admin_email = $_SESSION['admin_email'];
$shift = new Shift($con);

$shifts = $shift->getAllShiftsByDate(date('Y-m-d'));
?>

<!DOCTYPE html>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Dashboard</title>
    <link href="https://fonts.googleapis.com/icon?family=Material+Icons" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/materialize/1.0.0/css/materialize.min.css" rel="stylesheet">
    <style>
        :root {
            --primary-color: #2196F3;
            --secondary-color: #FFC107;
            --background-color: #F5F7FA;
            --text-color: #333333;
        }

        body {
            font-family: 'Roboto', sans-serif;
            background-color: var(--background-color);
            color: var(--text-color);
            margin: 0;
            padding: 0;
        }

        .navbar {
            background-color: var(--primary-color);
            box-shadow: 0 2px 10px rgba(0, 0, 0, 0.1);
        }

        .navbar .brand-logo {
            font-weight: bold;
            font-size: 1.8rem;
            color: white;
            padding-left: 10px;
        }

        .navbar ul li a {
            color: white;
            font-size: larger;
            font-weight: 500;
            transition: background-color 0.3s;
            padding: 5px 35px;
            padding-left: 10px;
        
        }

        .navbar ul li a:hover {
            background-color: rgba(255, 255, 255, 0.1);
        }

        .navbar .dropdown-trigger {
            display: flex;
            align-items: center;
        }

        .navbar .dropdown-trigger i {
            margin-left: 5px;
        }

        .dropdown-content {
            background-color: white;
            border-radius: 0px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
        }

        .dropdown-content li a {
            color: var(--primary-color) !important;
            font-weight: 400;
            display: flex;
        }

        .dropdown-content li a:hover {
            background-color: var(--secondary-color);
        }
        .content {
            margin: 40px auto;
            max-width: 90%;
        }

        h4 {
            font-size: 2rem;
            color: var(--primary-color);
            margin-bottom: 20px;
            text-align: center;
            font-weight: 600;
        }

        .card {
            border-radius: 8px;
            box-shadow: 0 4px 12px rgba(0, 0, 0, 0.1);
        }

        table {
            width: 100%;
            border-collapse: separate;
            border-spacing: 0;
            background-color: white;
            border-radius: 8px;
            overflow: hidden;
        }

        table th, table td {
            padding: 15px;
            text-align: left;
            font-size: medium;
        }

        table th {
            background-color: var(--primary-color);
            color: white;
            font-weight: 600;
            text-transform: uppercase;
        }

        table td {
            background-color: white;
            border-bottom: 1px solid #f0f0f0;
        }

        .employee-picture {
            width: 55px;
            height: 55px;
            border-radius: 50%;
            object-fit: cover;
            border: 2px solid var(--secondary-color);
        }

        .no-shifts {
            text-align: center;
            color: #999;
            font-style: italic;
            padding: 20px;
        }

        .shift-status {
            display: inline-block;
            padding: 5px 10px;
            border-radius: 20px;
            font-size: 0.8rem;
            font-weight: bold;
            text-transform: uppercase;
        }

        .status-active {
            background-color: #4CAF50;
            color: white;
        }

        .status-upcoming {
            background-color: #FFC107;
            color: black;
        }

        .status-completed {
            background-color: #9E9E9E;
            color: white;
        }
    </style>
</head>
<body>
<nav class="navbar">
        <div class="nav-wrapper">
            <a href="#" class="brand-logo">
                <i class="material-icons">dashboard</i>
                Admin Dashboard
            </a>
            <ul id="nav-mobile" class="right hide-on-med-and-down">
                <li>
                    <a class="dropdown-trigger" href="#!" data-target="shift-dropdown">
                        Shifts
                        <i class="material-icons right">arrow_drop_down</i>
                    </a>
                    <ul id="shift-dropdown" class="dropdown-content">
                        <li><a href="addshift.php"><i class="material-icons left">add</i>Add Shift</a></li>
                        <li><a href="assign_shifts.php"><i class="material-icons left">assignment</i>Assign Shift</a></li>
                    </ul>
                </li>
                <li><a href="viewemployees.php"><i class="material-icons left">people</i>Employees</a></li>
                <li><a href="empleave.php"><i class="material-icons left">date_range</i>Leave</a></li>
                <li><a href="addash.php?action=logout" class="logout-btn"><i class="material-icons left">exit_to_app</i>Logout</a></li>
            </ul>
        </div>
    </nav>

    <div class="content">
        <h4>Today's Shifts</h4>
        <div class="card">
            <table>
                <thead>
                    <tr>
                        <th>Employee</th>
                        <th>Name</th>
                        <th>Shift ID</th>
                        <th>Start Time</th>
                        <th>End Time</th>
                        <th>Status</th>
                        <th>Note</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (count($shifts) > 0): ?>
                        <?php foreach ($shifts as $shift): ?>
                            <tr>
                                <?php
                                $base_url = "http://localhost/ess/";
                                $imagePath = $base_url . $shift['employee_picture'];
                                ?>
                                <td><img src="<?= $imagePath ?>" alt="<?= $shift['employee_name'] ?>" class="employee-picture"></td>
                                <td><?= $shift['employee_name'] ?></td>
                                <td><?= $shift['id'] ?></td>
                                <td><?= $shift['start_time'] ?></td>
                                <td><?= $shift['end_time'] ?></td>
                                <td>
                                    <?php
                                    $now = new DateTime();
                                    $start = new DateTime($shift['start_time']);
                                    $end = new DateTime($shift['end_time']);
                                    if ($now >= $start && $now <= $end) {
                                        echo '<span class="shift-status status-active">Active</span>';
                                    } elseif ($now < $start) {
                                        echo '<span class="shift-status status-upcoming">Upcoming</span>';
                                    } else {
                                        echo '<span class="shift-status status-completed">Completed</span>';
                                    }
                                    ?>
                                </td>
                                <td><?= $shift['note'] ?></td>
                            </tr>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <tr>
                            <td colspan="7" class="no-shifts">No shifts scheduled for today.</td>
                        </tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>

    <script src="https://cdnjs.cloudflare.com/ajax/libs/materialize/1.0.0/js/materialize.min.js"></script>
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            var elems = document.querySelectorAll('.dropdown-trigger');
            var instances = M.Dropdown.init(elems, {});
        });
    </script>
</body>
</html>


