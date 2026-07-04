<?php
session_start();
include('../includes/db.php');

// Log activity function definition
function logActivity($conn, $username, $activity) {
    $timestamp = date('Y-m-d H:i:s'); // Current timestamp
    $logQuery = "INSERT INTO activity_log (username, activity, timestamp) VALUES (?, ?, ?)";

    // Prepare the statement
    $stmt = $conn->prepare($logQuery);
    if ($stmt === false) {
        die('Error preparing log activity statement: ' . $conn->error);
    }

    // Bind the parameters
    $stmt->bind_param("sss", $username, $activity, $timestamp);

    // Execute the statement
    if (!$stmt->execute()) {
        error_log("Failed to log activity: " . $stmt->error);
    }
}

// Check if the form is submitted and the file is uploaded
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $introduction = mysqli_real_escape_string($conn, $_POST['introduction']);
    $general_information = mysqli_real_escape_string($conn, $_POST['general_information']);
    $vision = mysqli_real_escape_string($conn, $_POST['vision']);
    $mission = mysqli_real_escape_string($conn, $_POST['mission']);
    $map_link = mysqli_real_escape_string($conn, $_POST['map_link']);

    // Initialize imagePath with the current image from the database
    $query = "SELECT image FROM aboutus WHERE id = 1";
    $result = mysqli_query($conn, $query);
    if ($result) {
        $row = mysqli_fetch_assoc($result);
        $existingImagePath = $row['image'];
    }

    $imagePath = $existingImagePath; // Set the default to the existing image

    // Handle image upload
    if (isset($_FILES['image']) && $_FILES['image']['error'] === UPLOAD_ERR_OK) {
        $uploadDir = '../uploads/';
        $uploadFile = $uploadDir . basename($_FILES['image']['name']);
        
        // Check if the directory exists, if not, create it
        if (!is_dir($uploadDir)) {
            mkdir($uploadDir, 0755, true);
        }
        
        // Move the uploaded file to the specified directory
        if (move_uploaded_file($_FILES['image']['tmp_name'], $uploadFile)) {
            $imagePath = $uploadFile; // Update with the new image path
        } else {
            echo "Error uploading the file.";
            exit;
        }
    }

    // Update the database with the new data
    $query = "UPDATE aboutus SET introduction = '$introduction', general_information = '$general_information', vision = '$vision', mission = '$mission', image = '$imagePath', map_link = '$map_link' WHERE id = 1";
    if (mysqli_query($conn, $query)) {
        // Log the activity of updating the about page
        logActivity($conn, $_SESSION['username'], "Updated About Us page.");

        echo "Record updated successfully.";
        header('Location: about.php'); // Redirect to the about page or show a success message
    } else {
        echo "Error updating record: " . mysqli_error($conn);
    }
}
?>
