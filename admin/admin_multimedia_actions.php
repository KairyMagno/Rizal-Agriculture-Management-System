<?php
session_start(); // Ensure the session is started
include '../includes/db.php';

// Log activity in the database
function logActivity($conn, $username, $activity) {
    $timestamp = date('Y-m-d H:i:s');
    $stmt = $conn->prepare("INSERT INTO activity_log (username, activity, timestamp) VALUES (?, ?, ?)");
    $stmt->bind_param("sss", $username, $activity, $timestamp);
    $stmt->execute();
    $stmt->close();
}

// Check if the form was submitted and if the 'action' key exists in POST
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action'])) {
    $action = $_POST['action'];

    // Ensure user is logged in and the username is set in the session
    if (isset($_SESSION['username'])) {
        $username = $_SESSION['username']; // Username from session
    } else {
        echo "Error: User is not logged in.";
        exit; // Stop further execution if the user is not logged in
    }

    if ($action === 'create') {
        // Sanitize inputs
        $category = htmlspecialchars(trim($_POST['category'])); // For multimedia category
        $title = htmlspecialchars(trim($_POST['title']));
        $content = htmlspecialchars(trim($_POST['content']));
        $link = isset($_POST['link']) ? filter_var(trim($_POST['link']), FILTER_SANITIZE_URL) : null; // New link field
        $image_path = null; // Default value for the image path

        // Handle image upload
        if (isset($_FILES['image_file']) && $_FILES['image_file']['error'] === UPLOAD_ERR_OK) {
            $upload_dir = '../uploads/'; // Directory to store uploaded images
            $image_name = basename($_FILES['image_file']['name']);
            $image_path = $upload_dir . uniqid() . '_' . $image_name; // Create a unique filename

            // Check if file type is valid
            $valid_extensions = ['jpg', 'jpeg', 'png', 'gif'];
            $file_extension = strtolower(pathinfo($image_name, PATHINFO_EXTENSION));

            if (!in_array($file_extension, $valid_extensions)) {
                die("Error: Invalid file type. Only JPG, JPEG, PNG, and GIF files are allowed.");
            }

            // Ensure the upload directory exists
            if (!is_dir($upload_dir)) {
                mkdir($upload_dir, 0755, true);
            }

            // Move the uploaded file to the desired directory
            if (!move_uploaded_file($_FILES['image_file']['tmp_name'], $image_path)) {
                echo "Error: Failed to upload image.";
                die();
            }
        } else {
            die("Error: No image file uploaded or an error occurred during upload.");
        }

        // Insert multimedia data into the database
        $current_date = date('Y-m-d'); // Get current date in MySQL-compatible format
        $stmt = $conn->prepare("INSERT INTO multimedia (category, title, image_url, date, content, link) VALUES (?, ?, ?, ?, ?, ?)");
        $stmt->bind_param("ssssss", $category, $title, $image_path, $current_date, $content, $link);

        if ($stmt->execute()) {
            // Log the activity
            logActivity($conn, $username, "Created a multimedia item titled: '$title'");

            // Redirect to the multimedia page after success
            header("Location: multimedia.php");
            exit; // Important to prevent further execution after redirection
        } else {
            echo "Error: " . $stmt->error;
        }

        $stmt->close();

    } elseif ($action === 'edit' && isset($_POST['multimedia_id'])) {
        // Handle edit action
        $multimedia_id = intval($_POST['multimedia_id']); // Sanitize multimedia ID
        $category = htmlspecialchars(trim($_POST['category'])); // For multimedia category
        $title = htmlspecialchars(trim($_POST['title'])); // Sanitize inputs
        $content = htmlspecialchars(trim($_POST['content']));
        $link = isset($_POST['link']) ? filter_var(trim($_POST['link']), FILTER_SANITIZE_URL) : null; // Sanitize link
        $image_path = null; // Default value for the image path

        // Check if a new image is being uploaded
        if (isset($_FILES['image_file']) && $_FILES['image_file']['error'] === UPLOAD_ERR_OK) {
            $upload_dir = '../uploads/'; // Directory to store uploaded images
            $image_name = basename($_FILES['image_file']['name']);
            $image_path = $upload_dir . uniqid() . '_' . $image_name; // Create a unique filename

            // Check if file type is valid
            $valid_extensions = ['jpg', 'jpeg', 'png', 'gif'];
            $file_extension = strtolower(pathinfo($image_name, PATHINFO_EXTENSION));

            if (!in_array($file_extension, $valid_extensions)) {
                die("Error: Invalid file type. Only JPG, JPEG, PNG, and GIF files are allowed.");
            }

            // Ensure the upload directory exists
            if (!is_dir($upload_dir)) {
                mkdir($upload_dir, 0755, true);
            }

            // Move the uploaded file to the desired directory
            if (!move_uploaded_file($_FILES['image_file']['tmp_name'], $image_path)) {
                echo "Error: Failed to upload image.";
                die();
            }
        } else {
            // If no new image is uploaded, retain the existing image path
            $stmt = $conn->prepare("SELECT image_url FROM multimedia WHERE id = ?");
            $stmt->bind_param("i", $multimedia_id);
            $stmt->execute();
            $result = $stmt->get_result();
            if ($row = $result->fetch_assoc()) {
                $image_path = $row['image_url']; // Retain the existing image if no new one is uploaded
            }
            $stmt->close();
        }

        // Update multimedia data in the database, including the new link field
        $stmt = $conn->prepare("UPDATE multimedia SET category = ?, title = ?, image_url = ?, content = ?, link = ? WHERE id = ?");
        $stmt->bind_param("sssssi", $category, $title, $image_path, $content, $link, $multimedia_id);

        if ($stmt->execute()) {
            // Log the activity
            logActivity($conn, $username, "Edited a multimedia item titled: '$title'");

            // Redirect to the multimedia page after success
            header("Location: multimedia.php");
            exit; // Prevent further execution after redirection
        } else {
            echo "Error: " . $stmt->error;
        }

        $stmt->close();
    }
} else {
    echo "Error: Action is not set.";
}
?>
