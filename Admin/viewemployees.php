<?php
// Include database connection
include '../db_conn.php';

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

// Fetch employee data
$query = "SELECT id, first_name, last_name, picture FROM employees";
$result = mysqli_query($con, $query);

// Check if a delete request is made

if (isset($_GET['delete_id'])) {
    $delete_id = intval($_GET['delete_id']);
    
    // First, delete from related tables like employee_availability
    $deleteAvailabilityQuery = "DELETE FROM employee_availability WHERE employee_id = $delete_id";
    mysqli_query($con, $deleteAvailabilityQuery);

    // You can add more queries for other related tables if needed
    // $deleteProjectsQuery = "DELETE FROM employee_projects WHERE employee_id = $delete_id";
    // mysqli_query($con, $deleteProjectsQuery);

    // Now delete from the employees table
    $deleteQuery = "DELETE FROM employees WHERE id = $delete_id";
    
    if (mysqli_query($con, $deleteQuery)) {
        header("Location: viewemployees.php"); // Redirect to avoid re-submission on refresh
        exit();
    } else {
        echo "Error: " . mysqli_error($con);
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>View Employees</title>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;500&display=swap" rel="stylesheet">
    <style>
        body {
            font-family: 'Poppins', sans-serif;
            background-color: #f0f2f5;
            margin: 0;
            color: #333;
        }
        h1 {
            font-size: 28px;
            margin: 20px 0;
            text-align: center;
            color: #333;
        }
        .employee-list {
            display: flex;
            flex-wrap: wrap;
            gap: 20px;
            justify-content: center;
            padding: 20px;
        }
        .employee-card {
            margin: 20px;
            padding: 20px;
            border: 1px solid #ddd; 
            border-radius: 10px; 
        }
        .employee-card:hover {
            transform: translateY(-5px);
        }
        .employee-card img {
            width: 100%;
            height: 120px;
            object-fit: cover;
        }
        .employee-card h3 {
            margin: 15px 0 5px;
            font-size: 20px;
            color: #333;
        }
        .employee-card p {
            margin: 5px 0;
            color: #777;
        }
        .more-info, .delete-btn {
            display: block;
            margin: 10px 0;
            font-size: 14px;
            cursor: pointer;
            text-decoration: none;
        }
        .more-info {
            color: #3498db;
        }
        .delete-btn {
            color: #e74c3c;
            font-weight: bold;
        }

        /* Dialog Box Styles */
        .dialog {
            display: none;
            position: fixed;
            top: 50%;
            left: 50%;
            transform: translate(-50%, -50%);
            padding: 20px;
            background: #fff;
            border-radius: 8px;
            box-shadow: 0 4px 12px rgba(0,0,0,0.2);
            width: 90%;
            max-width: 400px;
            z-index: 1000;
            overflow-y: auto;
            animation: fadeIn 0.3s ease;
        }
        .dialog.active {
            display: block;
        }
        .dialog-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            border-bottom: 1px solid #ddd;
            padding-bottom: 10px;
            margin-bottom: 10px;
        }
        .dialog-header h3 {
            margin: 0;
            font-size: 20px;
            color: #333;
        }
        .dialog-close {
            cursor: pointer;
            color: #e74c3c;
            font-size: 18px;
        }
        .dialog-content p {
            margin: 8px 0;
            font-size: 14px;
            color: #555;
        }
        .dialog-footer {
            display: flex;
            justify-content: flex-end;
            margin-top: 10px;
        }
        .dialog-footer button {
            padding: 8px 12px;
            border: none;
            border-radius: 4px;
            cursor: pointer;
            font-size: 14px;
            margin-left: 10px;
        }
        .dialog-footer .confirm {
            background-color: #e74c3c;
            color: #fff;
        }
        .dialog-footer .cancel {
            background-color: #ddd;
            color: #333;
        }

        /* Navbar Styles */
        nav {
            background: linear-gradient(90deg, #16a085, #27ae60);
            padding: 15px 20px;
        }
        .nav-wrapper {
            display: flex;
            justify-content: space-between;
            align-items: center;
            max-width: 1200px;
            margin: 0 auto;
        }
        .brand-logo {
            color: #fff;
            font-size: 24px;
            text-decoration: none;
            font-weight: bold;
        }
        .nav-wrapper ul {
            list-style: none;
            padding: 0;
            margin: 0;
            display: flex;
        }
        .nav-wrapper ul li {
            margin: 0 10px;
        }
        .nav-wrapper ul li a {
            color: #fff;
            text-decoration: none;
            font-size: 16px;
            padding: 10px;
            border-radius: 4px;
            transition: background 0.3s ease;
        }
        .nav-wrapper ul li a:hover {
            background: rgba(255, 255, 255, 0.2);
        }

        /* Animation */
        @keyframes fadeIn {
            from { opacity: 0; }
            to { opacity: 1; }
        }
    </style>
</head>
<body>
    <nav>
        <div class="nav-wrapper">
            <a href="admin_dashboard.php" class="brand-logo">Employees</a>
            <ul id="nav-mobile">
                <li><a href="addash.php">Home</a></li>
                <li><a href="viewemployees.php?action=logout">Logout</a></li>
            </ul>
        </div>
    </nav>
    <div class="employee-list">
        <?php while ($row = mysqli_fetch_assoc($result)): ?>
            <div class="employee-card">
                <img src="../<?php echo htmlspecialchars($row['picture']); ?>" alt="Employee Picture">
                <h3><?php echo htmlspecialchars($row['first_name']) . ' ' . htmlspecialchars($row['last_name']); ?></h3>
                <p>ID: <?php echo htmlspecialchars($row['id']); ?></p>
                <a class="more-info" onclick="showDialog(<?php echo htmlspecialchars($row['id']); ?>)">More</a>
                <a class="delete-btn" onclick="confirmDelete(<?php echo htmlspecialchars($row['id']); ?>)">Delete</a>
                
                <!-- Dialog Box for Employee Details -->
                <div class="dialog" id="dialog-<?php echo htmlspecialchars($row['id']); ?>">
                    <div class="dialog-header">
                        <h3>Employee Details</h3>
                        <span class="dialog-close" onclick="closeDialog(<?php echo htmlspecialchars($row['id']); ?>)">×</span>
                    </div>
                    <div class="dialog-content">
                        <?php
                        // Fetch and display detailed information
                        $employeeId = $row['id'];
                        $detailQuery = "SELECT * FROM employees WHERE id = $employeeId";
                        $detailResult = mysqli_query($con, $detailQuery);
                        $details = mysqli_fetch_assoc($detailResult);
                        ?>
                        <p><strong>ID:</strong> <?php echo htmlspecialchars($details['id']); ?></p>
                        <p><strong>First Name:</strong> <?php echo htmlspecialchars($details['first_name']); ?></p>
                        <p><strong>Last Name:</strong> <?php echo htmlspecialchars($details['last_name']); ?></p>
                        <p><strong>Email:</strong> <?php echo htmlspecialchars($details['email']); ?></p>
                        <p><strong>Phone:</strong> <?php echo htmlspecialchars($details['phone']); ?></p>
                        <p><strong>Address:</strong> <?php echo htmlspecialchars($details['address']); ?></p>
                        <p><strong>Date of Birth:</strong> <?php echo htmlspecialchars($details['date_of_birth']); ?></p>
                    </div>
                </div>
            </div>
        <?php endwhile; ?>
    </div>
    
    <div class="dialog" id="confirm-delete-dialog">
        <div class="dialog-header">
            <h3>Confirm Deletion</h3>
            <span class="dialog-close" onclick="closeDeleteDialog()">×</span>
        </div>
        <div class="dialog-content">
            <p>Are you sure you want to delete this employee?</p>
        </div>
        <div class="dialog-footer">
            <button class="cancel" onclick="closeDeleteDialog()">Cancel</button>
            <button class="confirm" id="confirm-delete-btn">Delete</button>
        </div>
    </div>

    <script>
        function showDialog(employeeId) {
            document.getElementById('dialog-' + employeeId).classList.add('active');
        }

        function closeDialog(employeeId) {
            document.getElementById('dialog-' + employeeId).classList.remove('active');
        }

        function confirmDelete(employeeId) {
            document.getElementById('confirm-delete-dialog').classList.add('active');
            document.getElementById('confirm-delete-btn').onclick = function() {
                window.location.href = "viewemployees.php?delete_id=" + employeeId;
            }
        }

        function closeDeleteDialog() {
            document.getElementById('confirm-delete-dialog').classList.remove('active');
        }
    </script>
</body>
</html>
