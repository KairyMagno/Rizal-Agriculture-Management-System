<?php
session_start();
include '../includes/db.php';

// Check if the user is logged in and has appropriate permissions
if (!isset($_SESSION['role']) || ($_SESSION['role'] != 'staff' && $_SESSION['role'] != 'admin')) {
    header("Location: ../login/login.php");
    exit;
}

// Get the username from the GET request
$username = isset($_GET['username']) ? trim($_GET['username']) : '';

// Check if the username is provided
if ($username != '') {
    // Fetch the user details before deletion
    $fetchQuery = "SELECT * FROM users WHERE username = ?";
    $stmt = $conn->prepare($fetchQuery);
    if ($stmt === false) {
        die('Error preparing statement: ' . $conn->error);
    }

    $stmt->bind_param("s", $username);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows > 0) {
        $user = $result->fetch_assoc();

        // Get the current date and time to archive the record
        $current_date = date('Y-m-d H:i:s');  // Current date and time in 'YYYY-MM-DD HH:MM:SS' format

        // Archive the record before deletion into the deleted_user table
        $archiveQuery = "INSERT INTO deleted_user (username, email, role, archived_date, deleted_at)
                         VALUES (?, ?, ?, ?, ?)";
        $archiveStmt = $conn->prepare($archiveQuery);
        if ($archiveStmt === false) {
            die('Error preparing archive statement: ' . $conn->error);
        }

        $archiveStmt->bind_param("sssss", $user['username'], $user['email'], $user['role'], $current_date, $current_date);

        if ($archiveStmt->execute()) {
            // Proceed with deleting the user from the users table if archiving is successful
            $deleteQuery = "DELETE FROM users WHERE username = ?";
            $deleteStmt = $conn->prepare($deleteQuery);
            if ($deleteStmt === false) {
                die('Error preparing delete statement: ' . $conn->error);
            }

            $deleteStmt->bind_param("s", $username);
            if ($deleteStmt->execute()) {
                // Redirect back to the user management page after deletion
                header('Location: user.php');
                exit;
            } else {
                echo "Error deleting the user: " . $deleteStmt->error;
            }
        } else {
            echo "Error archiving the user: " . $archiveStmt->error;
        }
    } else {
        // If no record found, redirect back
        header('Location: user.php');
        exit;
    }
} else {
    // If no username is provided, redirect back
    header('Location: user.php');
    exit;
}
?>
