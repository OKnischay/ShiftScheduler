<?php
require_once('../db_conn.php');
$today = date('Y-m-d');

// Fetch leave requests with employee details
$sql = "SELECT employees.id AS employee_id, employees.first_name, employees.last_name, employees.picture, leave_request.start, leave_request.end, leave_request.reason, leave_request.status 
        FROM leave_request
        JOIN employees ON leave_request.employee_id = employees.id
        WHERE leave_request.end >= '$today'
        ORDER BY leave_request.start";

$result = mysqli_query($con, $sql);
?>
<?php
if (isset($_GET['action']) && $_GET['action'] === 'logout') {
    session_unset();
    session_destroy();
    header("Location: alogin.php");
    exit();
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Employee Leave Requests | Admin Panel</title>
    <link href="https://fonts.googleapis.com/icon?family=Material+Icons" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/materialize/1.0.0/css/materialize.min.css" rel="stylesheet">
    <style>
       body {
    font-family: 'Roboto', sans-serif;
    background-color: #f1f1f1;
    color: #333;
    line-height: 1.6;
    margin: 0;
    padding: 0;
    }

/* Navbar Styling */
.nav-wrapper {
    background-color: #26a69a;
    box-shadow: 0 2px 5px rgba(0, 0, 0, 0.2);
}

.brand-logo {
    font-size: 2rem;
    font-weight: bold;
    color: #fff !important;
    text-transform: uppercase;
    letter-spacing: 2px;
    margin-left: 25px;
}

/* Container & Card */
.container {
    margin-top: 3rem;
}

.card {
    padding: 3rem;
    background-color: #fff;
    border-radius: 12px;
    box-shadow: 0 4px 12px rgba(0, 0, 0, 0.1);
    transition: box-shadow 0.3s ease-in-out;
}

.card:hover {
    box-shadow: 0 6px 18px rgba(0, 0, 0, 0.15);
}

h5 {
    font-weight: 600;
    color: #00796b;
    font-size: 1.8rem;
    margin-bottom: 2rem;
}

/* Table Styling */
table {
    width: 100%;
    border-collapse: collapse;
    margin-bottom: 1.5rem;
}

table th {
    background-color: #26a69a;
    color: #fff;
    padding: 10px;
    font-size: 1rem;
    text-transform: uppercase;
}

table td {
    padding: 12px;
    border-bottom: 1px solid #ddd;
}

table tr:hover {
    background-color: #f9f9f9;
}

.employee-picture {
    width: 50px;
    height: 50px;
    border-radius: 50%;
    object-fit: cover;
    box-shadow: 0 2px 5px rgba(0, 0, 0, 0.2);
}

/* Options styling */
.options a {
    margin: 0 8px;
    text-decoration: none;
    font-weight: 500;
    color: #00796b;
    padding: 6px 12px;
    border: 1px solid #00796b;
    border-radius: 4px;
    transition: all 0.3s ease;
}

.options a:hover {
    background-color: #00796b;
    color: #fff;
    border-color: #00796b;
}

/* Modal Styling */
.modal {
    width: 35%;
    padding: 20px;
    background-color: #fff;
    border-radius: 8px;
    box-shadow: 0 6px 15px rgba(0, 0, 0, 0.3);
    transition: transform 0.3s ease-in-out;
}

.modal h6 {
    font-weight: 600;
    color: #00796b;
    margin-bottom: 20px;
}

.modal .modal-content {
    padding: 20px;
}

.modal-footer {
    border-top: 1px solid #ddd;
    padding: 10px;
}

.modal-footer a {
    background-color: #00796b;
    color: #fff;
    border-radius: 5px;
    text-decoration: none;
    transition: background-color 0.3s ease;
}

.modal-footer a:hover {
    background-color: #004d40;
}

/* Add hover shadow for buttons */
.btn {
    line-height: 15px;
    background-color: #00796b;
    color: #fff;
    border-radius: 4px;
    padding: 10px 20px;
    transition: background-color 0.3s ease;
}

.btn:hover {
    background-color: #004d40;
}

/* Media Queries */
@media (max-width: 768px) {
    .container {
        margin-top: 1.5rem;
        padding: 0 1rem;
    }
    
    .modal {
        width: 80%;
    }
    
    .employee-picture {
        width: 40px;
        height: 40px;
    }
}

@media only screen and (min-width: 601px) {
    .container {
        width: 100%;
    }
}

    </style>
</head>
<body>
    <!-- Navbar -->
    <nav>
        <div class="nav-wrapper">
            <a href="#" class="brand-logo left">Leave Requests</a>
            <ul id="nav-mobile" class="right hide-on-med-and-down">
                <li><a href="addash.php">Home</a></li>
                <li><a href="addash.php?action=logout">Logout</a></li>
            </ul>
        </div>
    </nav>

    <div class="container">
        <div class="card">
            <h5>Employee Leave Requests</h5>
            <table class="striped">
                <thead>
                    <tr>
                        <th>Emp. ID</th>
                        <th>Picture</th>
                        <th>Name</th>
                        <th>Start Date</th>
                        <th>End Date</th>
                        <th>Total Days</th>
                        <th>Reason</th>
                        <th>Status</th>
                        <th>Options</th>
                    </tr>
                </thead>
                <tbody>
                    <?php
                    while ($employee = mysqli_fetch_assoc($result)) {
                        $date1 = new DateTime($employee['start']);
                        $date2 = new DateTime($employee['end']);
                        $interval = $date1->diff($date2);

                        echo "<tr>";
                        echo "<td>" . htmlspecialchars($employee['employee_id']) . "</td>";
                        echo "<td><img src='../" . htmlspecialchars($employee['picture']) . "' class='employee-picture' alt='Employee Picture' onerror=\"this.src='../path/to/default/image.jpg';\"></td>";
                        echo "<td>" . htmlspecialchars($employee['first_name']) . " " . htmlspecialchars($employee['last_name']) . "</td>";
                        echo "<td>" . htmlspecialchars($employee['start']) . "</td>";
                        echo "<td>" . htmlspecialchars($employee['end']) . "</td>";
                        echo "<td>" . ($interval->days + 1) . "</td>";
                        echo "<td>" . htmlspecialchars($employee['reason']) . "</td>";
                        echo "<td>" . htmlspecialchars($employee['status']) . "</td>";
                        echo "<td class='options'>
                                <a class='modal-trigger' href='#approveModal' data-id='" . htmlspecialchars($employee['employee_id']) . "'>Approve</a> | 
                                <a class='modal-trigger' href='#declineModal' data-id='" . htmlspecialchars($employee['employee_id']) . "'>Decline</a>
                              </td>";
                        echo "</tr>";
                    }
                    ?>
                </tbody>
            </table>
        </div>
    </div>

    <!-- Approve Modal -->
    <div id="approveModal" class="modal">
        <div class="modal-content">
            <h4>Approve Leave</h4>
            <p>Are you sure you want to approve this leave request?</p>
        </div>
        <div class="modal-footer">
            <a href="#!" class="modal-close waves-effect waves-green btn-flat">Cancel</a>
            <a href="#" id="approve-confirm" class="waves-effect waves-light btn">Approve</a>
        </div>
    </div>

    <!-- Decline Modal -->
    <div id="declineModal" class="modal">
        <div class="modal-content">
            <h4>Decline Leave</h4>
            <p>Are you sure you want to decline this leave request?</p>
        </div>
        <div class="modal-footer">
            <a href="#!" class="modal-close waves-effect waves-green btn-flat">Cancel</a>
            <a href="#" id="decline-confirm" class="waves-effect waves-light btn red">Decline</a>
        </div>
    </div>

    <script src="https://cdnjs.cloudflare.com/ajax/libs/materialize/1.0.0/js/materialize.min.js"></script>
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            var modals = document.querySelectorAll('.modal');
            M.Modal.init(modals);

            var approveButtons = document.querySelectorAll('.modal-trigger[href="#approveModal"]');
            var declineButtons = document.querySelectorAll('.modal-trigger[href="#declineModal"]');

            approveButtons.forEach(function(button) {
                button.addEventListener('click', function() {
                    var employeeId = button.getAttribute('data-id');
                    document.getElementById('approve-confirm').setAttribute('href', 'approve.php?id=' + employeeId);
                });
            });

            declineButtons.forEach(function(button) {
                button.addEventListener('click', function() {
                    var employeeId = button.getAttribute('data-id');
                    document.getElementById('decline-confirm').setAttribute('href', 'decline.php?id=' + employeeId);
                });
            });
        });
    </script>
</body>
</html>
