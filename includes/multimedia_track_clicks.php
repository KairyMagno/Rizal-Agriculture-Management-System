<?php
// track_click.php
session_start();
require '../includes/db.php'; // Ensure the database connection is included

if (isset($_GET['item_id'])) {
    $itemId = (int) $_GET['item_id'];

    // Prepare the update query
    $query = "UPDATE multimedia SET click_count = click_count + 1 WHERE id = ?";
    $stmt = $conn->prepare($query);
    
    if ($stmt === false) {
        die('Prepare failed: ' . $conn->error);
    }

    $stmt->bind_param("i", $itemId);
    $stmt->execute();

    if ($stmt->affected_rows > 0) {
        echo "Click tracked successfully!";
    } else {
        echo "Failed to track the click. No rows were updated.";
    }

    $stmt->close();
    $conn->close();
} else {
    echo "Item ID not provided.";
}
?>
