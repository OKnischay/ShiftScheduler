<?php
session_start();
require_once 'db_conn.php';

// Set the correct time zone (change 'Your/Timezone' to the correct one for your location)
date_default_timezone_set('Asia/Kathmandu');

if (!isset($_SESSION['employee_id'])) {
    header("Location: elogin.php");
    exit();
}

$employee_id = $_SESSION['employee_id'];
$date = date('Y-m-d');  // PHP current date


// SQL query to fetch today's shift for the employee
$query = "
    SELECT s.start_time, s.end_time, s.note 
    FROM schedule sc
    JOIN shifts s ON sc.shift_id = s.id
    WHERE sc.employee_id = ? AND sc.date = ?
";
$stmt = $con->prepare($query);
if ($stmt === false) {
    die("Database query preparation failed: " . $con->error);
}

$stmt->bind_param('is', $employee_id, $date);

$stmt->execute();

$result = $stmt->get_result();
if ($result === false) {
    die("Query fetching failed: " . $stmt->error);
}

$shift = $result->fetch_assoc();  // Fetch the shift details
$stmt->close();
$con->close();

?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Today's Shift</title>
    <link href="https://fonts.googleapis.com/css2?family=Roboto:wght@300;400;500;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
    <style>
        body {
            font-family: 'Roboto', sans-serif;
            background-color: #f0f4f8;
            color: #333;
            margin: 0;
            padding: 0;
            min-height: 100vh;
            display: flex;
            flex-direction: column;
        }
        nav {
            background-color: #00796b;
            padding: 1rem;
            display: flex;
            box-shadow: 0 2px 4px rgba(0,0,0,0.1);
        }
        .nav-wrapper {
            display: flex;
            justify-content: flex-end;
            max-width: 1200px;
            margin: auto;
            width: 100%;
        }
        .nav-wrapper a {
            color: white;
            text-decoration: none;
            padding: 0.5rem 1rem;
            border-radius: 4px;
            transition: background-color 0.3s ease;
        }
        .nav-wrapper a:hover {
            background-color: #004d40;
        }
        .container {
            flex-grow: 1;
            display: flex;
            justify-content: center;
            align-items: center;
            padding: 2rem;
        }
        .shift-card {
            background: white;
            border-radius: 8px;
            box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
            padding: 2rem;
            max-width: 400px;
            width: 100%;
        }
        h1 {
            color: #00796b;
            margin-bottom: 1.5rem;
            text-align: center;
        }
        .shift-info {
            display: flex;
            align-items: center;
            margin-bottom: 1rem;
        }
        .shift-info i {
            margin-right: 1rem;
            color: #00796b;
        }
        .note {
            background-color: #e8f5e9;
            border-left: 4px solid #00796b;
            padding: 1rem;
            margin-top: 1.5rem;
        }
        @media (max-width: 600px) {
            .nav-wrapper {
                flex-direction: column;
                align-items: center;
            }
            .nav-wrapper a {
                margin-bottom: 0.5rem;
            }
        }
    </style>
</head>
<body>
    <nav>
        <div class="nav-wrapper">
            <a href="edash.php">Home</a>
            <a href="applyleave.php">Apply Leave</a>
            <a href="elogout.php">Logout</a>
        </div>
    </nav>

    <div class="container">
        <div class="shift-card">
            <h1>Today's Shift</h1>
            <?php if ($shift): ?>
                <div class="shift-info">
                    <i class="fas fa-clock"></i>
                    <p><strong>Start Time:</strong> <?php echo date('h:i A', strtotime($shift['start_time'])); ?></p>
                </div>
                <div class="shift-info">
                    <i class="fas fa-clock"></i>
                    <p><strong>End Time:</strong> <?php echo date('h:i A', strtotime($shift['end_time'])); ?></p>
                </div>
                <div class="note">
                    <i class="fas fa-sticky-note"></i>
                    <p><strong>Note:</strong> <?php echo htmlspecialchars($shift['note']); ?></p>
                </div>
            <?php else: ?>
                <p>You have no shift assigned for today.</p>
            <?php endif; ?>
        </div>
    </div>

    <script src="https://kit.fontawesome.com/your-fontawesome-kit.js" crossorigin="anonymous"></script>
</body>
</html>
