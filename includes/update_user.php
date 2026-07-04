<?php
session_start();
require '../includes/db.php';

// Ensure only 'staff' or 'admin' can update user data
if (!isset($_SESSION['role']) || ($_SESSION['role'] != 'staff' && $_SESSION['role'] != 'admin')) {
    header("Location: ../login/login.php");
    exit;
}

// Get data from the form
$id = intval($_POST['id']);
$username = $conn->real_escape_string($_POST['username']);
$email = $conn->real_escape_string($_POST['email']);
$role = $conn->real_escape_string($_POST['role']);

// Update query
$sql = "UPDATE users SET username = ?, email = ?, role = ? WHERE id = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param('sssi', $username, $email, $role, $id);

// Execute and check for success
if ($stmt->execute()) {
    header("Location: user.php?success=1"); // Redirect back to the user management page with a success message
} else {
    echo "Error: " . $conn->error;
}

// Close the statement and connection
$stmt->close();
$conn->close();
?>
