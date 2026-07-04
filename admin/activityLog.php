<?php
session_start();
require '../includes/db.php';


// Function to log activity
function logActivity($username, $activity) {
    global $conn;
    $timestamp = date('Y-m-d H:i:s'); // Get the current time
    $query = "INSERT INTO activity_log (username, activity, timestamp) VALUES ('$username', '$activity', '$timestamp')";
    mysqli_query($conn, $query);
}

// Check if the user is logged in and if their role is 'staff' or 'admin'
if (!isset($_SESSION['role']) || ($_SESSION['role'] != 'staff' && $_SESSION['role'] != 'admin')) {
    header("Location: ../login/login.php");
    exit;
}

// Check if the user is admin or staff
$hide_admin_elements = $_SESSION['role'] == 'staff';

// Fetch existing admins and staff from the database
$admins = mysqli_query($conn, "SELECT * FROM users WHERE role = 'admin'");
$staff = mysqli_query($conn, "SELECT * FROM users WHERE role = 'staff'");

// Handle form submissions
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    if (isset($_POST['add_admin'])) {
        // Sanitize inputs
        $email = mysqli_real_escape_string($conn, $_POST['admin_email']);
        $username = mysqli_real_escape_string($conn, $_POST['admin_username']);
        $password = mysqli_real_escape_string($conn, $_POST['admin_password']);
        
        // Hash the password
        $hashed_password = password_hash($password, PASSWORD_BCRYPT);
        
        // Insert into the database with the hashed password
        mysqli_query($conn, "INSERT INTO users (email, username, password, role) VALUES ('$email', '$username', '$hashed_password', 'admin')");
        
        // Log the activity
        logActivity($_SESSION['username'], "Added new admin: $username");

        header("Location: user.php");
        exit;
    }

    if (isset($_POST['add_staff'])) {
        // Sanitize inputs
        $email = mysqli_real_escape_string($conn, $_POST['staff_email']);
        $username = mysqli_real_escape_string($conn, $_POST['staff_username']);
        $password = mysqli_real_escape_string($conn, $_POST['staff_password']);
        
        // Hash the password
        $hashed_password = password_hash($password, PASSWORD_BCRYPT);
        
        // Insert into the database with the hashed password
        mysqli_query($conn, "INSERT INTO users (email, username, password, role) VALUES ('$email', '$username', '$hashed_password', 'staff')");

        // Log the activity
        logActivity($_SESSION['username'], "Added new staff: $username");

        header("Location: user.php");
        exit;
    }

    if (isset($_POST['edit_user'])) {
        // Sanitize inputs
        $user_id = mysqli_real_escape_string($conn, $_POST['edit_user_id']);
        $email = mysqli_real_escape_string($conn, $_POST['edit_email']);
        $username = mysqli_real_escape_string($conn, $_POST['edit_username']);
        $password = mysqli_real_escape_string($conn, $_POST['edit_password']);
        
        // Hash the password before updating
        $hashed_password = password_hash($password, PASSWORD_BCRYPT);

        mysqli_query($conn, "UPDATE users SET email='$email', username='$username', password='$hashed_password' WHERE id='$user_id'");

        // Log the activity
        logActivity($_SESSION['username'], "Edited user: $username");

        header("Location: user.php");
        exit;
    }
}

// Handle user deletion
if (isset($_GET['delete_user'])) {
    $user_id = $_GET['delete_user'];

    // Fetch the username of the user to be deleted
    $result = mysqli_query($conn, "SELECT username FROM users WHERE id = '$user_id'");
    $user_to_delete = mysqli_fetch_assoc($result);
    $username = $user_to_delete['username'];

    // Perform delete operation
    mysqli_query($conn, "DELETE FROM users WHERE id='$user_id'");

    // Log the activity
    logActivity($_SESSION['username'], "Deleted user: $username");

    header("Location: user.php");
    exit;


    
}
?>
