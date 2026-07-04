<?php
require 'db.php'; // Ensure database connection is included

// Check if the 'id' parameter is set (for editing a specific announcement item)
if (isset($_GET['id'])) {
    // Fetch a single announcement item by its ID
    $announcement_id = $_GET['id'];

    // Prepare the query to fetch the specific announcement item
    $query = "SELECT id, title, content, image_url, link, 
                     DATE_FORMAT(date, '%M %d, %Y') as formatted_date
              FROM announcement 
              WHERE id = ?" ;

    // Prepare and execute the statement
    $stmt = $conn->prepare($query);
    if ($stmt === false) {
        die('Error preparing statement: ' . $conn->error);
    }
    $stmt->bind_param("i", $announcement_id);
    $stmt->execute();
    $result = $stmt->get_result();

    // Check if a result is found
    if ($result && $row = $result->fetch_assoc()) {
        // Return the single announcement item as JSON
        echo json_encode($row, JSON_PRETTY_PRINT);
    } else {
        // If no announcement found or it's older than 30 days, return an error message
        echo json_encode(['error' => 'Announcement not found or has expired'], JSON_PRETTY_PRINT);
    }

    $stmt->close();
} else {
    // Add limit and offset parameters for pagination
    $limit = isset($_GET['limit']) ? intval($_GET['limit']) : 10; // Default to 10 if not provided
    $offset = isset($_GET['offset']) ? intval($_GET['offset']) : 0; // Default to 0 if not provided

    // Prepare the query for all announcements from the last 30 days with limit and offset
    $query = "SELECT id, title, content, image_url, link, 
                     DATE_FORMAT(date, '%M %d, %Y') as formatted_date
              FROM announcement 
              ORDER BY date DESC, created_at DESC, updated_at DESC
              LIMIT ? OFFSET ?";

    // Prepare and execute the statement
    $stmt = $conn->prepare($query);
    if ($stmt === false) {
        die('Error preparing statement: ' . $conn->error);
    }
    $stmt->bind_param("ii", $limit, $offset);
    $stmt->execute();
    $result = $stmt->get_result();

    // Prepare the data for JSON response
    $announcement = [];
    if ($result) {
        while ($row = $result->fetch_assoc()) {
            $announcement[] = $row; // Include all the announcement items
        }
    }

    // Return the JSON response
    header('Content-Type: application/json');
    echo json_encode($announcement, JSON_PRETTY_PRINT);

    $stmt->close();
}
?>
