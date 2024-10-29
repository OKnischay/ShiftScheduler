<?php
include "db_conn.php";
session_start();

$error = ""; // Variable to hold error messages

if ($_SERVER["REQUEST_METHOD"] === "POST" && isset($_POST['submit'])) {
    $first_name = trim($_POST['first_name']);
    $last_name = trim($_POST['last_name']);
    $email = trim($_POST['email']);
    $password = trim($_POST['password']);
    $passwordConfirmation = trim($_POST['passwordConfirmation']);
    $phone = trim($_POST['phone']);
    $address = trim($_POST['address']);
    $date_of_birth = trim($_POST['date_of_birth']);
    
    // Handle file upload for picture
    $picture = ""; // Default picture path
    if (isset($_FILES['picture']) && $_FILES['picture']['error'] == 0) {
        $pictureTmpName = $_FILES['picture']['tmp_name'];
        $pictureName = $_FILES['picture']['name'];
        $uploadDirectory = 'uploads'; // Directory for storing uploaded pictures
        if (!file_exists($uploadDirectory)) {
            mkdir($uploadDirectory, 0755, true); // Create the directory if it does not exist
        }
        $picturePath = $uploadDirectory . '/' . basename($pictureName);
        
        if (move_uploaded_file($pictureTmpName, $picturePath)) {
            $picture = $picturePath; // Store the full path in the database
        } else {
            $error = "Failed to upload picture.";
        }
    }

    // Server-side validation
    if (empty($first_name) || empty($last_name) || empty($email) || empty($password) || empty($passwordConfirmation)|| empty($address)) {
        $error = "All fields are required.";
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $error = "Invalid email format.";
    } elseif (strlen($password) < 8) {
        $error = "Password must be at least 8 characters long.";
    } elseif ($password !== $passwordConfirmation) {
        $error = "Passwords do not match.";
    } else {
        // Check if email already exists
        $stmt = $con->prepare("SELECT id FROM employees WHERE email = ?");
        $stmt->bind_param("s", $email);
        $stmt->execute();
        $stmt->store_result();
        
        if ($stmt->num_rows > 0) {
            $error = "This email is already registered.";
        } else {
            // Hash the password
            $hashedPassword = password_hash($password, PASSWORD_DEFAULT);

            // Insert employee into the database
            $stmt = $con->prepare("INSERT INTO employees (first_name, last_name, email, password, phone, address, picture, date_of_birth) VALUES (?, ?, ?, ?, ?, ?, ?, ?)");
            $stmt->bind_param("ssssssss", $first_name, $last_name, $email, $hashedPassword, $phone, $address, $picture, $date_of_birth);

            if ($stmt->execute()) {
                // Redirect to login page after successful registration
                header('Location: elogin.php');
                exit();
            } else {
                $error = "Error: " . $stmt->error;
            }
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
    <title>Register</title>
    <link href="https://fonts.googleapis.com/icon?family=Material+Icons" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/materialize/1.0.0/css/materialize.min.css" rel="stylesheet">
    <style>
        body {
            background: #e0e0e0; /* Grey background */
        }
        .card-panel {
            border-radius: 12px;
            box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1);
        }
        .input-field input, .input-field textarea {
            border-radius: 8px;
        }
        .btn-large {
            border-radius: 8px;
        }
        .error {
            color: #d32f2f;
        }
        .success {
            color: #388e3c;
        }
    </style>
</head>
<body>
<div class="container">
    <div class="row" id="registerForm">
        <div class="col m8 l6 offset-m2 offset-l3">
            <div class="card-panel white">
                <div class="row center-align">
                    <h4 class="blue-text text-darken-2">Register</h4>
                    <?php if (!empty($error)) : ?>
                        <p class="error"><?php echo htmlspecialchars($error); ?></p>
                    <?php endif; ?>
                </div>
                <form action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]); ?>" method="POST" enctype="multipart/form-data">
                    <div class="row">
                        <div class="input-field col s12 m6">
                            <input id="first_name" name="first_name" type="text" class="validate" required>
                            <label for="first_name">First Name</label>
                        </div>
                        <div class="input-field col s12 m6">
                            <input id="last_name" name="last_name" type="text" class="validate" required>
                            <label for="last_name">Last Name</label>
                        </div>
                    </div>
                    <div class="row">
                        <div class="input-field col s12">
                            <input id="email" name="email" type="email" class="validate" required>
                            <label for="email">Email</label>
                        </div>
                    </div>
                    <div class="row">
                        <div class="input-field col s12 m6">
                            <input id="password" name="password" type="password" class="validate" required>
                            <label for="password">Password</label>
                        </div>
                        <div class="input-field col s12 m6">
                            <input id="passwordConfirmation" name="passwordConfirmation" type="password" class="validate" required>
                            <label for="passwordConfirmation">Confirm Password</label>
                        </div>
                    </div>
                    <div class="row">
                        <div class="input-field col s12 m6">
                            <input id="phone" name="phone" type="number" class="validate" required>
                            <label for="phone">Phone Number</label>
                        </div>
                        <div class="input-field col s12 m6">
                            <input id="date_of_birth" name="date_of_birth" type="text" class="datepicker" required>
                            <label for="date_of_birth">Date of Birth</label>
                        </div>
                    </div>
                    <div class="row">
                        <div class="input-field col s12">
                            <textarea id="address" name="address" class="materialize-textarea" required></textarea>
                            <label for="address">Address</label>
                        </div>
                    </div>
                    <div class="row">
                        <div class="file-field input-field col s12">
                            <div class="btn">
                                <span>Picture</span>
                                <input type="file" name="picture" required>
                            </div>
                            <div class="file-path-wrapper">
                                <input class="file-path validate" type="text" placeholder="Upload your picture">
                            </div>
                        </div>
                    </div>
                    <div class="row">
                        <div class="col s12">
                            <button class="btn-large waves-effect waves-light blue accent-3" type="submit" name="submit">Register<i class="material-icons right">person_add</i></button>
                            <a href="elogin.php">.        Already have an account?</a>
                        </div>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

<script src="https://cdnjs.cloudflare.com/ajax/libs/materialize/1.0.0/js/materialize.min.js"></script>
<script>
    document.addEventListener('DOMContentLoaded', function() {
        var elems = document.querySelectorAll('.datepicker');
        M.Datepicker.init(elems, { format: 'yyyy-mm-dd' }); // Initialize datepicker
    });
</script>
</body>
</html>
