<?php
require 'db.php';

// Set JSON header early (good practice)
header('Content-Type: application/json');

// ==========================
// FETCH SINGLE ARTICLE (EDIT)
// ==========================
if (isset($_GET['id'])) {

    $article_id = (int) $_GET['id'];

    $query = "SELECT id, title, author, content, image_url, 
                     DATE_FORMAT(date, '%M %d, %Y') as formatted_date
              FROM articles 
              WHERE id = ?";

    $stmt = $conn->prepare($query);

    if (!$stmt) {
        echo json_encode(['error' => 'Prepare failed: ' . $conn->error]);
        exit;
    }

    $stmt->bind_param("i", $article_id);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result && $row = $result->fetch_assoc()) {
        echo json_encode($row, JSON_PRETTY_PRINT);
    } else {
        echo json_encode(['error' => 'Article not found']);
    }

    $stmt->close();
    exit;
}

// ==========================
// FETCH MULTIPLE ARTICLES
// ==========================
$start = isset($_GET['start']) ? (int) $_GET['start'] : 0;
$limit = isset($_GET['limit']) ? (int) $_GET['limit'] : 10;

// First, get the total count of articles
$countQuery = "SELECT COUNT(*) as total FROM articles";
$countResult = $conn->query($countQuery);
$totalCount = 0;

if ($countResult && $countResult->num_rows > 0) {
    $countRow = $countResult->fetch_assoc();
    $totalCount = (int) $countRow['total'];
}

// Fetch the articles with limit
$query = "SELECT id, title, content, image_url, author, 
                 DATE_FORMAT(date, '%M %d, %Y') AS formatted_date 
          FROM articles 
          ORDER BY date DESC, created_at DESC, updated_at DESC
          LIMIT ?, ?";

$stmt = $conn->prepare($query);

if (!$stmt) {
    echo json_encode(['error' => 'Prepare failed: ' . $conn->error]);
    exit;
}

$stmt->bind_param("ii", $start, $limit);
$stmt->execute();
$result = $stmt->get_result();

$articles = [];

while ($row = $result->fetch_assoc()) {
    $articles[] = $row;
}

// Return both articles and total count
$response = [
    'articles' => $articles,
    'total' => $totalCount
];

echo json_encode($response, JSON_PRETTY_PRINT);

$stmt->close();
$conn->close();
?>