<?php
include '../includes/db.php';

$id = intval($_GET['id'] ?? 0);

if (!$id) {
    echo json_encode(['status' => 'error', 'message' => 'Invalid ID']);
    exit;
}

// Start transaction
$conn->begin_transaction();

try {
    // Step 1: Fetch the multimedia details before archiving
    $stmt = $conn->prepare("SELECT * FROM multimedia WHERE id = ?");
    $stmt->bind_param("i", $id);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($row = $result->fetch_assoc()) {
        $title = $row['title'];
        $content = $row['content'];
        $image_url = $row['image_url'];
        $category = $row['category']; // Fetch the category
        $link = $row['link']; // Assuming there's a link field in multimedia
        $date = $row['date']; // Assuming there's a date field in multimedia
        $source_table = 'multimedia'; // Assuming the source table is 'multimedia'
        $deleted_at = date('Y-m-d H:i:s'); // Current timestamp
        $username = 'admin'; // Replace with the actual username if available

        // Step 2: Insert into deleted_post table for archiving
        $archiveStmt = $conn->prepare("INSERT INTO deleted_post (original_id, title, content, deleted_at, source_table, image_url, category, link, date) 
                                       VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)");
        $archiveStmt->bind_param("issssssss", $id, $title, $content, $deleted_at, $source_table, $image_url, $category, $link, $date);
        $archiveStmt->execute(); // Archive the item
        
        // Log the archiving activity only
        logActivity($conn, $username, "Archived multimedia ID $id titled '$title'");

        // Step 3: Delete from multimedia table
        $deleteStmt = $conn->prepare("DELETE FROM multimedia WHERE id = ?");
        $deleteStmt->bind_param("i", $id);
        $deleteStmt->execute(); // Delete the item

        // Step 4: Commit the transaction
        $conn->commit();
        
        // Success response
        echo json_encode(['status' => 'success']);
    } else {
        throw new Exception('Multimedia item not found');
    }
} catch (Exception $e) {
    // Rollback the transaction on error
    $conn->rollback();
    echo json_encode(['status' => 'error', 'message' => $e->getMessage()]);
}

// Activity logging function
function logActivity($conn, $username, $activity) {
    $timestamp = date('Y-m-d H:i:s'); // Current timestamp
    $logQuery = "INSERT INTO activity_log (username, activity, timestamp) VALUES (?, ?, ?)";
    $stmt = $conn->prepare($logQuery);
    $stmt->bind_param("sss", $username, $activity, $timestamp);

    if (!$stmt->execute()) {
        // Optional: handle logging errors if necessary
        error_log("Failed to log activity: " . $stmt->error);
    }
}
?>
