<?php
session_start();
require '../includes/db.php';

// Check if the announcement ID is passed as a parameter
if (isset($_GET['id']) && isset($_GET['title'])) {
    $announcementId = intval($_GET['id']);
    $title = htmlspecialchars($_GET['title']); // Sanitize the title

    // Check if an entry exists for this announcement, increment the count if it does
    $stmt = $conn->prepare("SELECT id FROM announcement_clicks WHERE announcement_id = ?");
    if ($stmt) {
        $stmt->bind_param("i", $announcementId);
        $stmt->execute();
        $result = $stmt->get_result();

        if ($result->num_rows > 0) {
            // Update the click count
            $updateStmt = $conn->prepare("UPDATE announcement_clicks SET click_count = click_count + 1 WHERE announcement_id = ?");
            if ($updateStmt) {
                $updateStmt->bind_param("i", $announcementId);
                $updateStmt->execute();
                $updateStmt->close(); // Close the update statement after execution
            } else {
                // Handle case where preparation fails
                die("Error preparing update statement: " . $conn->error);
            }
        } else {
            // Insert a new entry if no record exists
            $insertStmt = $conn->prepare("INSERT INTO announcement_clicks (announcement_id, title, click_count) VALUES (?, ?, 1)");
            if ($insertStmt) {
                $insertStmt->bind_param("is", $announcementId, $title);
                $insertStmt->execute();
                $insertStmt->close(); // Close the insert statement after execution
            } else {
                // Handle case where preparation fails
                die("Error preparing insert statement: " . $conn->error);
            }
        }

        $stmt->close(); // Close the select statement after use
    } else {
        // Handle case where preparation fails
        die("Error preparing select statement: " . $conn->error);
    }

    // Redirect the user back to the link
    if (isset($_GET['redirect'])) {
        header("Location: " . $_GET['redirect']);
        exit();
    } else {
        // Handle missing redirect parameter
        die("Redirect URL not provided.");
    }
} else {
    // Handle missing ID or title parameters
    die("Announcement ID or title not provided.");
}
?>
