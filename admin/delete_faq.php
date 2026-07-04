<?php
include '../includes/db.php';

// Get the FAQ ID from the GET request
$faq_id = isset($_GET['faq_id']) ? intval($_GET['faq_id']) : 0;

if ($faq_id > 0) {
    // Fetch the FAQ details before archiving
    $fetchQuery = "SELECT * FROM faqs WHERE faq_id = ?";
    $stmt = $conn->prepare($fetchQuery);
    $stmt->bind_param("i", $faq_id);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows > 0) {
        $faq = $result->fetch_assoc();

        // Get the current date and time
        $current_date = date('Y-m-d H:i:s');  // Current date and time in 'YYYY-MM-DD HH:MM:SS' format
        $deletion_time = $current_date;

        // Archive the record before deletion into the deleted_post table
        $archived = archiveDeletedRecord(
            $conn,
            'faqs',               // Source table name
            $faq_id,              // Record ID
            $faq['question'],     // Question (Title equivalent)
            $faq['answer'],       // Answer (Content equivalent)
            null,                 // No image URL for FAQ
            $current_date,        // Date when the record is archived
            $deletion_time        // Time when the FAQ is archived
        );

        // Log only the archiving process
        if ($archived) {
            logActivity($conn, 'admin', "Archived FAQ ID $faq_id titled '{$faq['question']}'"); // Log the archiving
        }

        // Now, delete the FAQ from the original table
        $deleteQuery = "DELETE FROM faqs WHERE faq_id = ?";
        $deleteStmt = $conn->prepare($deleteQuery);
        $deleteStmt->bind_param("i", $faq_id);
        if ($deleteStmt->execute()) {
            // Redirect back to the previous page after deletion
            header('Location: ' . $_SERVER['HTTP_REFERER']);
            exit;
        } else {
            echo "Error deleting the FAQ: " . $deleteStmt->error;
        }
    } else {
        // If no record found, redirect back
        header('Location: ' . $_SERVER['HTTP_REFERER']);
        exit;
    }
} else {
    // If no ID is provided or invalid, redirect back
    header('Location: ' . $_SERVER['HTTP_REFERER']);
    exit;
}

// Archive function definition
function archiveDeletedRecord($conn, $source_table, $record_id, $question, $answer, $image_url, $archived_date, $deletion_time) {
    // Archive the FAQ into the deleted_post table
    $archiveQuery = "INSERT INTO deleted_post (source_table, original_id, title, content, image_url, date, deleted_at)
                     VALUES (?, ?, ?, ?, ?, ?, ?)";
    $stmt = $conn->prepare($archiveQuery);
    $stmt->bind_param("sisssss", $source_table, $record_id, $question, $answer, $image_url, $archived_date, $deletion_time);
    
    if ($stmt->execute()) {
        return true;
    } else {
        echo "Error archiving the record: " . $stmt->error;
        return false;
    }
}

// Log activity function definition
function logActivity($conn, $username, $activity) {
    $timestamp = date('Y-m-d H:i:s'); // Current timestamp
    $logQuery = "INSERT INTO activity_log (username, activity, timestamp) VALUES (?, ?, ?)";
    $stmt = $conn->prepare($logQuery);
    $stmt->bind_param("sss", $username, $activity, $timestamp);

    if (!$stmt->execute()) {
        echo "Error logging activity: " . $stmt->error;
    }
}
?>
