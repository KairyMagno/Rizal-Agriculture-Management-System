<?php
require '../includes/db.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $articleTitle = $_POST['title'] ?? '';

    if (!empty($articleTitle)) {
        // Use prepared statement to prevent SQL injection
        $stmt = $conn->prepare("UPDATE articles SET click_count = click_count + 1 WHERE title = ?");
        $stmt->bind_param("s", $articleTitle);

        if ($stmt->execute()) {
            echo json_encode(['status' => 'success']);
        } else {
            echo json_encode(['status' => 'error', 'message' => 'Failed to update click count.']);
        }

        $stmt->close();
    } else {
        echo json_encode(['status' => 'error', 'message' => 'Article title is missing.']);
    }
} else {
    echo json_encode(['status' => 'error', 'message' => 'Invalid request method.']);
}
?>
