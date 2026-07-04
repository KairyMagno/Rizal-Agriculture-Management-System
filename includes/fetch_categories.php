<?php
require 'db.php'; // Ensure database connection is included

// Fetch categories from the database, including the slug
$sql = "SELECT id, name, slug FROM categories";
$result = $conn->query($sql);

$categories = [];
if ($result->num_rows > 0) {
    while ($row = $result->fetch_assoc()) {
        $categories[] = $row;
    }
}

// Set the content type to JSON and output the data
header('Content-Type: application/json');
echo json_encode($categories);
?>
