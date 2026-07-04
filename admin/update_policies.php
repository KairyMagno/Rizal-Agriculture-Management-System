<?php
require '../includes/db.php';

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
    $sql_update = "UPDATE policies SET title = ?, effective_date = ?, content = ? WHERE id = ?";
    $stmt = $conn->prepare($sql_update);
    $stmt->bind_param("sssi", $title, $effective_date, $content, $id);

    if ($stmt->execute()) {
        // Log the activity after the update is successful
        if (isset($_SESSION['username'])) {
            logActivity($conn, $_SESSION['username'], "Updated policy");
        }

        header("Location: settings.php?success=1");
        exit;
    } else {
        echo "Error updating record: " . $conn->error;
    }

    $stmt->close();
    $conn->close();
}
?>
