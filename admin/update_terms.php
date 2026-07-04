<?php
require '../includes/db.php'; // Ensure the database connection is included

// Start the session to get access to session variables like username
session_start();

// Log activity function definition
function logActivity($conn, $username, $activity) {
    $timestamp = date('Y-m-d H:i:s'); // Current timestamp
    $logQuery = "INSERT INTO activity_log (username, activity, timestamp) VALUES (?, ?, ?)";

    // Prepare the statement
    $stmt = $conn->prepare($logQuery);
    if ($stmt === false) {
        die('Error preparing log activity statement: ' . $conn->error);
    }

    // Bind the parameters
    $stmt->bind_param("sss", $username, $activity, $timestamp);

    // Execute the statement
    if (!$stmt->execute()) {
        error_log("Failed to log activity: " . $stmt->error);
    }
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $id = $_POST['id'];
    $title = $_POST['title'];
    $effective_date = $_POST['effective_date'];
    $content = $_POST['content'];

    // Update query
    $sql = "UPDATE terms SET title = ?, effective_date = ?, content = ? WHERE id = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("sssi", $title, $effective_date, $content, $id);

    if ($stmt->execute()) {
        // Log the activity after the update is successful
        if (isset($_SESSION['username'])) {
            logActivity($conn, $_SESSION['username'], "Updated terms");
        }

        header("Location: settings.php?success=1"); // Redirect back to the terms page
    } else {
        echo "Error updating terms: " . $conn->error;
    }

    $stmt->close();
    $conn->close();
}
?>
