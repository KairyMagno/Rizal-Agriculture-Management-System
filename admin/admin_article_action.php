<?php
session_start(); // Ensure the session is started
include '../includes/db.php';

// Check if the form was submitted and if the 'action' key exists in POST
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action'])) {
    $action = $_POST['action'];

    // Function to handle image uploads
    function uploadImage($file) {
        $upload_dir = '../uploads/';
        $image_name = basename($file['name']);
        $file_extension = strtolower(pathinfo($image_name, PATHINFO_EXTENSION));
        $valid_extensions = ['jpg', 'jpeg', 'png', 'gif'];

        if (!in_array($file_extension, $valid_extensions)) {
            die("Error: Invalid file type. Only JPG, JPEG, PNG, and GIF files are allowed.");
        }

        if (!is_dir($upload_dir)) {
            mkdir($upload_dir, 0755, true);
        }

        $unique_name = uniqid() . '_' . $image_name;
        $image_path = $upload_dir . $unique_name;

        if (!move_uploaded_file($file['tmp_name'], $image_path)) {
            die("Error: Failed to upload image.");
        }

        return $image_path;
    }

    // Common validation for title, content, and author
    function sanitizeInput($data) {
        return htmlspecialchars(trim($data));
    }

    // Log activity in the database
    function logActivity($conn, $username, $activity) {
        $timestamp = date('Y-m-d H:i:s');
        $stmt = $conn->prepare("INSERT INTO activity_log (username, activity, timestamp) VALUES (?, ?, ?)");
        $stmt->bind_param("sss", $username, $activity, $timestamp);
        $stmt->execute();
        $stmt->close();
    }

    // Get the username from the session
    $username = isset($_SESSION['username']) ? $_SESSION['username'] : 'Unknown';

    if ($action === 'create') {
        $title = sanitizeInput($_POST['title']);
        $content = sanitizeInput($_POST['content']);
        $author = sanitizeInput($_POST['author']);
        $current_date = date('Y-m-d H:i:s');

        // Handle image upload
        if (isset($_FILES['image_file']) && $_FILES['image_file']['error'] === UPLOAD_ERR_OK) {
            $image_path = uploadImage($_FILES['image_file']);
        } else {
            die("Error: No image file uploaded or an error occurred during upload.");
        }

        // Insert article data into the database
        $stmt = $conn->prepare("INSERT INTO articles (title, author, image_url, date, content) VALUES (?, ?, ?, ?, ?)");
        $stmt->bind_param("sssss", $title, $author, $image_path, $current_date, $content);

        if ($stmt->execute()) {
            // Log the creation activity
            logActivity($conn, $username, "Created an article titled: '$title'");

            header("Location: articles.php");
            exit;
        } else {
            echo "Error: " . $stmt->error;
        }
        $stmt->close();

    } elseif ($action === 'edit' && isset($_POST['article_id'])) {
        $article_id = intval($_POST['article_id']);
        $title = sanitizeInput($_POST['title']);
        $content = sanitizeInput($_POST['content']);
        $author = sanitizeInput($_POST['author']);
        $image_path = null;

        // Handle new image upload
        if (isset($_FILES['image_file']) && $_FILES['image_file']['error'] === UPLOAD_ERR_OK) {
            $image_path = uploadImage($_FILES['image_file']);
        } else {
            // Retain the existing image if no new image is uploaded
            $stmt = $conn->prepare("SELECT image_url FROM articles WHERE id = ?");
            $stmt->bind_param("i", $article_id);
            $stmt->execute();
            $result = $stmt->get_result();
            if ($row = $result->fetch_assoc()) {
                $image_path = $row['image_url'];
            }
            $stmt->close();
        }

        // Update article data in the database
        $stmt = $conn->prepare("UPDATE articles SET title = ?, author = ?, image_url = ?, content = ? WHERE id = ?");
        $stmt->bind_param("ssssi", $title, $author, $image_path, $content, $article_id);

        if ($stmt->execute()) {
            // Log the edit activity
            logActivity($conn, $username, "Edited an article titled: '$title'");

            header("Location: articles.php");
            exit;
        } else {
            echo "Error: " . $stmt->error;
        }
        $stmt->close();
    }
} else {
    echo "Error: Action is not set.";
}
?>
