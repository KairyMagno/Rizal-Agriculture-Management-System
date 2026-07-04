<?php
// Include necessary files
require '../includes/db.php'; // For database connection

// Check if form data is submitted
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    // Sanitize and validate input
    $role = htmlspecialchars($_POST['role']);
    $firstName = htmlspecialchars($_POST['first_name']);
    $lastName = htmlspecialchars($_POST['last_name']);
    $address = htmlspecialchars($_POST['address']);
    $email = htmlspecialchars($_POST['email']);
    $message = htmlspecialchars($_POST['message']);

    // Prepare a SQL statement to insert the data into a 'contact_queries' table (assumed)
    $stmt = $conn->prepare("INSERT INTO contact_queries (role, first_name, last_name, address, email, message, submission_date) VALUES (?, ?, ?, ?, ?, ?, NOW())");
    $stmt->bind_param("ssssss", $role, $firstName, $lastName, $address, $email, $message);

    if ($stmt->execute()) {
        // Redirect to email.php with a success flag
        header("Location: contact.php?status=success");
        exit();
    } else {
        // Handle error (optional)
        header("Location: contact.php?status=error");
        exit();
    }
}
?>
