<?php
require '../includes/db.php'; // Ensure you include your database connection

function archiveDeletedRecord($conn, $source_table, $record_id, $full_name, $content, $image_url = null) {
    $current_date = date('Y-m-d H:i:s');
    $archiveQuery = "INSERT INTO deleted_user (source_table, original_id, username, email, role, archived_date, deleted_at)
                     VALUES (?, ?, ?, ?, ?, ?, ?)";
    $stmt = $conn->prepare($archiveQuery);
    $stmt->bind_param("sisssss", $source_table, $record_id, $full_name, $content, $current_date, $current_date);

    if (!$stmt->execute()) {
        die('Error archiving the record: ' . $stmt->error);
    }
}


?>