<?php
session_start(); // Start the session
include '../includes/db.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action'])) {
    $action = $_POST['action'];

    // Validate and sanitize input fields
    $title = isset($_POST['title']) ? htmlspecialchars(trim($_POST['title'])) : null;
    $content = isset($_POST['content']) ? htmlspecialchars(trim($_POST['content'])) : null;
    $link = isset($_POST['link']) ? filter_var(trim($_POST['link']), FILTER_SANITIZE_URL) : null;
    $image_path = null;

    // Handle image upload
    if (isset($_FILES['image_file']) && $_FILES['image_file']['error'] === UPLOAD_ERR_OK) {
        $upload_dir = '../uploads/';
        $image_name = basename($_FILES['image_file']['name']);
        $image_path = $upload_dir . uniqid() . '_' . $image_name;

        $valid_extensions = ['jpg', 'jpeg', 'png', 'gif'];
        $file_extension = strtolower(pathinfo($image_name, PATHINFO_EXTENSION));

        if (!in_array($file_extension, $valid_extensions)) {
            die("Error: Invalid file type. Only JPG, JPEG, PNG, and GIF files are allowed.");
        }

        if (!is_dir($upload_dir)) {
            mkdir($upload_dir, 0755, true);
        }

        if (!move_uploaded_file($_FILES['image_file']['tmp_name'], $image_path)) {
            die("Error: Failed to upload image.");
        }
    }

    // Get the current user's username from the session
    $username = isset($_SESSION['username']) ? $_SESSION['username'] : 'Unknown';

    // Handle create action
    if ($action === 'create') {
        if ($title && $content && $link) {
            $current_date = date('Y-m-d H:i:s');

            $stmt = $conn->prepare("INSERT INTO announcement (title, image_url, date, content, link) VALUES (?, ?, ?, ?, ?)");
            $stmt->bind_param("sssss", $title, $image_path, $current_date, $content, $link);

            if ($stmt->execute()) {
                // Log the activity directly into the database
                $log_stmt = $conn->prepare("INSERT INTO activity_log (username, activity, timestamp) VALUES (?, ?, ?)");
                $activity = "Created an announcement titled: '$title'";
                $log_stmt->bind_param("sss", $username, $activity, $current_date);
                $log_stmt->execute();
                $log_stmt->close();

                header("Location: announcement.php");
                exit;
            } else {
                echo "Error: " . $stmt->error;
            }

            $stmt->close();
        } else {
            echo "Error: Title, content, and link are required.";
        }
    } elseif ($action === 'edit' && isset($_POST['announcement_id'])) {
        $announcement_id = intval($_POST['announcement_id']);

        if ($title && $content) {
            if (!$image_path) {
                $stmt = $conn->prepare("SELECT image_url FROM announcement WHERE id = ?");
                $stmt->bind_param("i", $announcement_id);
                $stmt->execute();
                $result = $stmt->get_result();
                if ($row = $result->fetch_assoc()) {
                    $image_path = $row['image_url'];
                }
                $stmt->close();
            }

            $stmt = $conn->prepare("UPDATE announcement SET title = ?, image_url = ?, content = ?, link = ? WHERE id = ?");
            $stmt->bind_param("ssssi", $title, $image_path, $content, $link, $announcement_id);

            if ($stmt->execute()) {
                // Log the activity directly into the database
                $current_date = date('Y-m-d H:i:s');
                $log_stmt = $conn->prepare("INSERT INTO activity_log (username, activity, timestamp) VALUES (?, ?, ?)");
                $activity = "Edited an announcement titled: '$title'";
                $log_stmt->bind_param("sss", $username, $activity, $current_date);
                $log_stmt->execute();
                $log_stmt->close();

                header("Location: announcement.php");
                exit;
            } else {
                echo "Error: " . $stmt->error;
            }

            $stmt->close();
        } else {
            echo "Error: Title and content are required.";
        }
    } else {
        echo "Error: Invalid action.";
    }
} else {
    echo "Error: Action is not set or form was not submitted correctly.";
}
?>
