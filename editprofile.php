<?php
include 'db_conn.php';
session_start();

// Check if the user is logged in
if (!isset($_SESSION['employee_id'])) {
    header("Location: elogin.php");
    exit();
}

$employee_id = $_SESSION['employee_id'];
$success = "";
$error = "";

// Fetch current user information
function getCurrentUser($employee_id) {
    global $con;
    $stmt = $con->prepare("SELECT * FROM employees WHERE id = ?");
    $stmt->bind_param("i", $employee_id);
    $stmt->execute();
    $result = $stmt->get_result();
    return $result->fetch_assoc();
}

$user = getCurrentUser($employee_id);

// Handle form submission
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $email = trim($_POST['email']);
    $phone = trim($_POST['phone']);
    $address = trim($_POST['address']);
    $new_password = trim($_POST['new_password']);
    $confirm_password = trim($_POST['confirm_password']);
    $picture = $user['picture'];

    // Handle picture upload
    if (!empty($_FILES['picture']['name'])) {
        $target_dir = "uploads/";
        $target_file = $target_dir . basename($_FILES["picture"]["name"]);
        $imageFileType = strtolower(pathinfo($target_file, PATHINFO_EXTENSION));

        // Check if image file is an actual image or fake image
        $check = getimagesize($_FILES["picture"]["tmp_name"]);
        if ($check !== false) {
            if (move_uploaded_file($_FILES["picture"]["tmp_name"], $target_file)) {
                $picture = $target_file;
            } else {
                $error = "Sorry, there was an error uploading your file.";
            }
        } else {
            $error = "File is not an image.";
        }
    }

    // Handle password change
    if (!empty($new_password) && !empty($confirm_password)) {
        if ($new_password === $confirm_password) {
            $hashed_password = password_hash($new_password, PASSWORD_DEFAULT);
        } else {
            $error = "Passwords do not match.";
        }
    } else {
        $hashed_password = $user['password']; // Keep the old password if not changing
    }

    // Update employee information in the database
    if (empty($error)) {
        $stmt = $con->prepare("UPDATE employees SET email = ?, phone = ?, address = ?, picture = ?, password = ? WHERE id = ?");
        $stmt->bind_param("sssssi", $email, $phone, $address, $picture, $hashed_password, $employee_id);
        
        if ($stmt->execute()) {
            $success = "Profile updated successfully.";
            $user = getCurrentUser($employee_id); // Refresh user data
        } else {
            $error = "Error updating profile.";
        }
        $stmt->close();
    }
    $con->close();
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Edit Profile</title>
    <link href="https://fonts.googleapis.com/icon?family=Material+Icons" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/materialize/1.0.0/css/materialize.min.css" rel="stylesheet">
    <style>
        body {
            font-family: 'Roboto', sans-serif;
            background-color: #e0f7fa;
            color: #333;
        }
        .nav-wrapper {
            background-color: #00796b;
            padding-left: 20px;
        }
        .container {
            margin-top: 2rem;
        }
        .card {
            background: white;
            box-shadow: 0 4px 8px rgba(0,0,0,0.1);
            border-radius: 12px;
            padding: 2rem;
        }
        .card h5 {
            color: #00796b;
            font-weight: bold;
        }
        .picture {
            width: 100px;
            height: 100px;
            object-fit: cover;
            border-radius: 50%;
            border: 3px solid #00796b;
            margin-bottom: 1rem;
        }
        .file-field .btn {
            background-color: #00796b;
        }
        .file-path-wrapper input.file-path {
            border-bottom: 2px solid #00796b;
        }
        .input-field input[type="text"], .input-field input[type="password"] {
            border-bottom: 1px solid #00796b;
        }
        .input-field input[type="text"]:focus, .input-field input[type="password"]:focus {
            border-bottom: 1px solid #00796b;
            box-shadow: 0 1px 0 0 #00796b;
        }
        .btn {
            background-color: #00796b;
        }
        .btn:hover {
            background-color: #004d40;
        }
    </style>
</head>
<body>
    <!-- Navbar -->
    <nav>
        <div class="nav-wrapper">
            <a href="#" class="brand-logo left">Edit Profile</a>
            <ul id="nav-mobile" class="right hide-on-med-and-down">
                <li><a href="edash.php"><i class="material-icons left">home</i>Home</a></li>
                <li><a href="viewshift.php"><i class="material-icons left">calendar_today</i>View Shifts</a></li>
                <li><a href="applyleave.php"><i class="material-icons left">today</i>Apply Leave</a></li>
                <li><a href="elogout.php"><i class="material-icons left">exit_to_app</i>Logout</a></li>
            </ul>
        </div>
    </nav>

    <div class="container">
        <div class="card">
            <h5>Edit Profile</h5>
            <?php if (!empty($success)): ?>
                <div class="card-panel green lighten-4 green-text"><?php echo $success; ?></div>
            <?php elseif (!empty($error)): ?>
                <div class="card-panel red lighten-4 red-text"><?php echo $error; ?></div>
            <?php endif; ?>
            <form action="editprofile.php" method="POST" enctype="multipart/form-data">
                <div class="row">
                    <div class="col s12 m4">
                        <img src="<?php echo htmlspecialchars($user['picture']); ?>" alt="Profile Picture" class="picture">
                        <div class="file-field input-field">
                            <div class="btn">
                                <span>Change Picture</span>
                                <input type="file" name="picture">
                            </div>
                            <div class="file-path-wrapper">
                                <input class="file-path validate" type="text">
                            </div>
                        </div>
                    </div>
                    <div class="col s12 m8">
                        <div class="input-field">
                            <input type="text" name="email" value="<?php echo htmlspecialchars($user['email']); ?>" required>
                            <label for="email">Email</label>
                        </div>
                        <div class="input-field">
                            <input type="text" name="phone" value="<?php echo htmlspecialchars($user['phone']); ?>" required>
                            <label for="phone">Phone Number</label>
                        </div>
                        <div class="input-field">
                            <input type="text" name="address" value="<?php echo htmlspecialchars($user['address']); ?>" required>
                            <label for="address">Address</label>
                        </div>
                        <div class="input-field">
                            <button type="button" class="btn waves-effect waves-light modal-trigger" data-target="availabilityModal">Change Availability</button>
                        </div>
                        <div class="input-field">
                            <input type="password" name="new_password">
                            <label for="new_password">New Password</label>
                        </div>
                        <div class="input-field">
                            <input type="password" name="confirm_password">
                            <label for="confirm_password">Confirm New Password</label>
                        </div>
                        <button type="submit" class="btn waves-effect waves-light">Update Profile</button>
                    </div>
                </div>
            </form>
        </div>
    </div>

    <!-- Modal Structure -->
   <!-- Modal Structure -->
<div id="availabilityModal" class="modal large-modal">
    <div class="modal-content">
        <h5 class="teal-text text-darken-2">Update Your Availability</h5>
        <p>Select the days you are available for shifts.</p>
        <form action="update_availability.php" method="POST">
            <input type="hidden" name="employee_id" value="<?php echo htmlspecialchars($employee_id); ?>">

            <div class="input-field">
                <select name="day_of_week[]" multiple required>
                    <option value="" disabled selected>Choose days</option>
                    <option value="Sunday">Sunday</option>
                    <option value="Monday">Monday</option>
                    <option value="Tuesday">Tuesday</option>
                    <option value="Wednesday">Wednesday</option>
                    <option value="Thursday">Thursday</option>
                    <option value="Friday">Friday</option>
                    <option value="Saturday">Saturday</option>
                </select>
            </div>

            <div class="modal-footer">
                <button type="submit" class="btn waves-effect waves-light teal darken-2">Update Availability</button>
                <button type="button" class="modal-close btn-flat">Cancel</button>
            </div>
        </form>
    </div>
</div>

<script src="https://cdnjs.cloudflare.com/ajax/libs/materialize/1.0.0/js/materialize.min.js"></script>
<script>
    document.addEventListener('DOMContentLoaded', function() {
        var elems = document.querySelectorAll('select');
        M.FormSelect.init(elems);

        var modalElems = document.querySelectorAll('.modal');
        M.Modal.init(modalElems);
    });
</script>

<style>
    .large-modal {
        width: 600px; /* Set desired width */
        max-height: 80%; /* Set max height for better visibility */
    }

    .modal-content {
        padding: 2rem;
    }
    .input-field {
    box-sizing: border-box;
    margin-bottom: 1rem;
}

    
    .modal-footer {
        display: flex;
        justify-content: space-between;
    }
    
    .btn-flat {
        color: #00796b; /* Match your theme color */
    }
</style>

</body>
</html>
