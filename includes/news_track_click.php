<?php
// Ensure you include the database connection
require 'db.php';

header('Content-Type: application/json');

// Get the JSON input from the request
$data = json_decode(file_get_contents('php://input'), true);

// Check if title is provided
if (isset($data['title'])) {
    $title = $data['title'];

    // Update click count query
    $stmt = $conn->prepare("UPDATE news SET click_count = click_count + 1 WHERE title = ?");
    $stmt->bind_param("s", $title);

    if ($stmt->execute()) {
        echo json_encode(['status' => 'success', 'message' => 'Click count incremented']);
    } else {
        echo json_encode(['status' => 'error', 'message' => 'Failed to increment click count']);
    }

    $stmt->close();
} else {
    echo json_encode(['status' => 'error', 'message' => 'Title not provided']);
}

// Close the database connection
$conn->close();
?>
