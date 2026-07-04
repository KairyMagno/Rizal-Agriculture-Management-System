<?php

/**
 * Generic function to archive other types of deleted records.
 * Can be reused for non-user records.
 *
 * @param mysqli $db           Database connection
 * @param string $sourceTable  Source table name
 * @param int $recordId        ID of the record being deleted
 * @param string $title        Title or name of the record
 * @param string $content      Additional content or details
 * @param string|null $imageUrl Image URL if applicable
 */
function archiveDeletedRecord($db, $sourceTable, $recordId, $title, $content, $imageUrl = null) {
    // Prepare SQL statement to insert into the deleted_posts table
    $stmt = $db->prepare("INSERT INTO deleted_post (original_id, title, content, image_url, source_table) VALUES (?, ?, ?, ?, ?)");
    $stmt->bind_param("issss", $recordId, $title, $content, $imageUrl, $sourceTable);

    // Execute the query
    $stmt->execute();

    // Close the statement after execution
    $stmt->close();
}
?>
