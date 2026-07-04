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
    // Step 1: Fetch the article details
    $stmt = $conn->prepare("SELECT * FROM articles WHERE id = ?");
    $stmt->bind_param("i", $id);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($row = $result->fetch_assoc()) {
        $title = $row['title'];
        $content = $row['content'];
        $image_url = $row['image_url'];
        $author = $row['author']; // Fetch the author
        $category = $row['category']; // Assuming there's a category field in article
        $link = $row['link']; // Assuming there's a link field in article
        $date = $row['date']; // Assuming there's a date field in article
        $source_table = 'articles'; // Source table is 'articles'
        $archived_at = date('Y-m-d H:i:s'); // Current timestamp
        $username = 'admin'; // Replace with the actual username if available

        // Step 2: Insert into deleted_post table for archiving
        $archiveStmt = $conn->prepare("INSERT INTO deleted_post (original_id, title, content, deleted_at, source_table, image_url, author, category, link, date) 
                                       VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");
        $archiveStmt->bind_param("isssssssss", $id, $title, $content, $archived_at, $source_table, $image_url, $author, $category, $link, $date);
        $archiveStmt->execute(); // Archive the article

        // Log archiving activity only
        logActivity($conn, $username, "Archived article ID $id titled '$title'");

        // Step 3: Commit the transaction
        $conn->commit();
        
        // Success response
        echo json_encode(['status' => 'success']);
    } else {
        throw new Exception('Article not found');
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
