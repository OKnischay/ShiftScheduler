<?php
include 'db_conn.php'; 
session_start();

// Check if the user is logged in
if (!isset($_SESSION['employee_id'])) {
    header("Location: elogin.php");
    exit();
}

// Fetch current user information
$employee_id = $_SESSION['employee_id'];
$picture = ""; // Default picture URL or path

function getCurrentUser($employee_id) {
    global $con;
    $stmt = $con->prepare("SELECT first_name, last_name, picture FROM employees WHERE id = ?");
    $stmt->bind_param("i", $employee_id);
    $stmt->execute();
    $result = $stmt->get_result();
    return $result->fetch_assoc();
}

$user = getCurrentUser($employee_id);
if ($user) {
    $picture = $user['picture'];
    $username = $user['first_name'] . " " . $user['last_name'];
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Employee Dashboard</title>
    <link href="https://fonts.googleapis.com/icon?family=Material+Icons" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/materialize/1.0.0/css/materialize.min.css" rel="stylesheet">
    <style>
        body {
            font-family: 'Roboto', sans-serif;
            background-color: #f5f5f5;
            color: #333;
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
        .dropdown-content li {
            padding: 0.5rem 0.2rem;
        }
        .dropdown-content li a {
            color: #00796b;
            font-size :15px;
        }
        
        .container {
            margin-top: 2rem;
            padding: 2rem;
        }
        .card {
            background: white;
            box-shadow: 0 4px 8px rgba(0,0,0,0.1);
            border-radius: 12px;
            padding: 2.5rem;
        }
        .card-title {
            font-size: 1.8rem;
            font-weight: bold;
            color: #00796b;
        }
        .picture {
            width: 50px;
            height: 50px;
            object-fit: cover;
            border-radius: 50%;
            border: 2px solid #00796b;
            margin-left: 5px;
        }
        nav ul a {
            font-weight: 500;
            color: white;
            display: flex;
            align-items: center;
            padding: 0 1rem;
        }
        nav ul a .material-icons {
            margin-right: 8px;
        }
        nav ul li:last-child a .material-icons {
            margin-right: 0;
        }
        .sidenav-trigger {
            color: white;
        }
    </style>
</head>
<body>
    <!-- Dropdown menu -->
    <ul id="dropdown1" class="dropdown-content">
        <li><a href="editprofile.php"><i class="material-icons left">edit</i>Edit Profile</a></li>
        <li><a href="elogout.php"><i class="material-icons left">exit_to_app</i>Logout</a></li>
    </ul>
    
    <nav>
        <div class="nav-wrapper">
            <a href="#" class="brand-logo left">
               Employee Dashboard
            </a>
            <a href="#" data-target="slide-out" class="sidenav-trigger"><i class="material-icons">menu</i></a>
            <ul class="right hide-on-med-and-down">
                <li><a href="viewshift.php"><i class="material-icons left">calendar_today</i>View Shift</a></li>
                <li><a href="applyleave.php"><i class="material-icons left">today</i>Apply Leave</a></li>
                <li>
                    <a class="dropdown-trigger" href="#" data-target="dropdown1">
                        <span><?php echo htmlspecialchars($username); ?></span>
                        <img class="picture" src="<?php echo htmlspecialchars($picture); ?>" alt="Profile Picture"/>
                    </a>
                </li>
            </ul>
        </div>
    </nav>
    
    <div class="container">
        <div class="card">
            <span class="card-title">Welcome, <?php echo htmlspecialchars($username); ?>!</span>
            <p>This is your employee dashboard.</p>
            <?php
            // Include dynamic content or child pages
            if (isset($_GET['page'])) {
                $page = $_GET['page'];
                $page = htmlspecialchars($page); // Prevent XSS
                $pagePath = $page . '.php';
                if (file_exists($pagePath)) {
                    include $pagePath; // Include page content based on the request
                } else {
                    echo "<p>Page not found.</p>";
                }
            } else {
                echo "KEEP WORKING!!.</p>";
            }
            ?>
        </div>
    </div>

    <script src="https://cdnjs.cloudflare.com/ajax/libs/materialize/1.0.0/js/materialize.min.js"></script>
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            M.Sidenav.init(document.querySelectorAll('.sidenav'), {});
            M.Dropdown.init(document.querySelectorAll('.dropdown-trigger'), {});
        });
    </script>
</body>
</html>
