<?php
include "../db_conn.php"; // Include your database connection file
session_start(); // Start the session

$error = ""; // Variable to hold error messages
$email = ""; // Variable to hold the email input
$password = ""; // Variable to hold the password input

if ($_SERVER["REQUEST_METHOD"] == "POST") { // Check if the form was submitted
    $email = trim($_POST['email']); // Get the email input
    $password = trim($_POST['password']); // Get the password input

    if (empty($email) || empty($password)) { // Check if the email or password is empty
        $error = "Please fill in all fields."; // Set an error message
    } else {
        // Prepare and execute a query to check for the admin in the database
        $stmt = $con->prepare("SELECT * FROM admins WHERE email = ? AND password = ?");
        $stmt->bind_param("ss", $email, $password);
        $stmt->execute();
        $result = $stmt->get_result();

        if ($result->num_rows > 0) { // If an admin is found
            // Set session variables for the logged-in admin
            $_SESSION['admin_email'] = $email;

            // Redirect to the admin dashboard
            header("Location: addash.php"); // Make sure to create this dashboard page
            exit();
        } else {
            $error = "Invalid email or password."; // Set an error message for invalid credentials
        }

        $stmt->close(); // Close the statement
    }

    $con->close(); // Close the database connection
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Login</title>
    <link href="https://fonts.googleapis.com/icon?family=Material+Icons" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/materialize/1.0.0/css/materialize.min.css" rel="stylesheet">
    <style>
        #loginForm { margin-top: 50px; }
        .loginButtons { width: 100%; }
        .error { color: red; }
    </style>
</head>
<body>
<div class="container">
    <div class="row" id="loginForm">
        <div class="col m6 offset-m3 s12">
            <div class="card-panel">
                <div class="row grey lighten-5">
                    <div class="col s12 center">
                        <h4 class="blue-text text-darken-1">
                            Admin Login
                        </h4>
                    </div>
                </div>
                <?php if (!empty($error)): ?>
                    <div class="row">
                        <div class="col s12 center">
                            <span class="error"><?php echo htmlspecialchars($error); ?></span>
                        </div>
                    </div>
                <?php endif; ?>
                <form action="alogin.php" method="POST">
                    <div class="row">
                        <div class="col s12">
                            <input
                                placeholder="Email"
                                type="email"
                                class="validate"
                                value="<?php echo htmlspecialchars($email); ?>"
                                name="email"
                                required />
                        </div>
                    </div>
                    <div class="row">
                        <div class="col s12">
                            <input
                                placeholder="Password"
                                type="password"
                                class="validate"
                                value="<?php echo htmlspecialchars($password); ?>"
                                name="password"
                                required />
                        </div>
                    </div>
                    <div class="row">
                        <div class="col s12">
                            <button class="btn waves-effect waves-light btn-large blue accent-3 loginButtons" type="submit" name="action">Login<i class="material-icons right">send</i></button>
                        </div>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

<script src="https://cdnjs.cloudflare.com/ajax/libs/materialize/1.0.0/js/materialize.min.js"></script>
</body>
</html>
