<?php
require '../includes/db.php'; // Ensure the database connection is included

// Check if the form data is submitted via POST
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Get the form data
    $id = isset($_POST['id']) ? (int)$_POST['id'] : 0;
    $email = isset($_POST['email']) ? trim($_POST['email']) : '';
    $companyName = isset($_POST['company_name']) ? trim($_POST['company_name']) : '';
    $password = isset($_POST['password']) ? trim($_POST['password']) : '';

    // Basic validation
    if (empty($email) || empty($companyName) || empty($password)) {
        echo '<p>All fields are required.</p>';
        exit;
    }

    // Prepare an SQL statement to update the record
    $stmt = $conn->prepare("UPDATE company_email SET email = ?, company_name = ?, password = ? WHERE id = ?");
    if ($stmt) {
        // Bind the parameters
        $stmt->bind_param('sssi', $email, $companyName, $password, $id);
        
        // Execute the statement
        if ($stmt->execute()) {
            echo '<p>Record updated successfully.</p>';
            header('Location: settings.php');
        } else {
            echo '<p>Error updating record: ' . $stmt->error . '</p>';
        }
        
        // Close the statement
        $stmt->close();
    } else {
        echo '<p>Failed to prepare statement: ' . $conn->error . '</p>';
    }
}

// Close the database connection
$conn->close();
?>
