<?php
require 'db.php'; // Ensure database connection is included

header('Content-Type: application/json');

try {
    // Determine the mode of operation (single item by ID or filtered/all)
    if (isset($_GET['id'])) {
        // Fetch a single multimedia item by its ID
        fetchSingleItem($conn, $_GET['id']);
    } else {
        // Fetch all multimedia items (optionally filtered by category)
        $category = isset($_GET['category']) ? $_GET['category'] : null;
        fetchAllItems($conn, $category);
    }
} catch (Exception $e) {
    // Handle any unexpected errors
    echo json_encode(['error' => $e->getMessage()], JSON_PRETTY_PRINT);
    exit;
}

/**
 * Fetch a single multimedia item by its ID.
 */
function fetchSingleItem($conn, $id)
{
    // Validate and sanitize the ID
    $id = intval($id);

    // Prepare the query to only fetch items from the last 30 days
    $query = "SELECT id, title, content, image_url, link, 
                     category, 
                     DATE_FORMAT(date, '%M %d, %Y') as formatted_date
              FROM multimedia 
              WHERE id = ?"; // Filter items older than 30 days
    $stmt = $conn->prepare($query);
    if ($stmt === false) {
        throw new Exception('Error preparing statement: ' . $conn->error);
    }

    // Execute the query
    $stmt->bind_param("i", $id);
    $stmt->execute();
    $result = $stmt->get_result();

    // Fetch and return the result
    if ($result && $row = $result->fetch_assoc()) {
        echo json_encode($row, JSON_PRETTY_PRINT);
    } else {
        echo json_encode(['error' => 'Multimedia item not found or has expired'], JSON_PRETTY_PRINT);
    }

    $stmt->close();
}

/**
 * Fetch all multimedia items, optionally filtered by category.
 */
function fetchAllItems($conn, $category)
{
    if ($category) {
        // Query to fetch multimedia items by category and from the last 30 days
        $query = "SELECT id, title, content, image_url, link, 
                         category, 
                         DATE_FORMAT(date, '%M %d, %Y') as formatted_date
                  FROM multimedia 
                  ORDER BY date DESC, created_at DESC, updated_at DESC";
        $stmt = $conn->prepare($query);
        if ($stmt === false) {
            throw new Exception('Error preparing statement: ' . $conn->error);
        }

        $stmt->bind_param("s", $category);
        $stmt->execute();
        $result = $stmt->get_result();
    } else {
        // Query to fetch all multimedia items from the last 30 days
        $query = "SELECT id, title, content, image_url, link, 
                         category, 
                         DATE_FORMAT(date, '%M %d, %Y') as formatted_date
                  FROM multimedia 
                  ORDER BY date DESC, created_at DESC, updated_at DESC";
        $result = $conn->query($query);
    }

    // Prepare the data for JSON response
    $multimedia = [];
    if ($result) {
        while ($row = $result->fetch_assoc()) {
            $multimedia[] = $row;
        }
    }

    // Return the JSON response
    echo json_encode($multimedia, JSON_PRETTY_PRINT);
}
?>
