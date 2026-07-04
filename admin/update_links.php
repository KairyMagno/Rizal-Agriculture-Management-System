<?php
require '../includes/db.php'; // Ensure the database connection is included

// Start the session at the top of the script
session_start();

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

// Check if the form is submitted
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Get the input values and sanitize them
    $facebook = mysqli_real_escape_string($conn, $_POST['facebook']);
    $twitter = mysqli_real_escape_string($conn, $_POST['twitter']);
    $instagram = mysqli_real_escape_string($conn, $_POST['instagram']);

    // Ensure session is active and username is available
    if (!isset($_SESSION['username'])) {
        die("Error: User not logged in. Please log in first.");
    }

    // Update query to modify the existing links
    $query = "UPDATE media_links SET facebook = '$facebook', twitter = '$twitter', instagram = '$instagram' WHERE id = 1"; // Replace `1` with the appropriate ID if needed

    // Execute the query and check for success
    if (mysqli_query($conn, $query)) {
        // Log the activity here after the update
        logActivity($conn, $_SESSION['username'], "Updated social media links");

        echo "Links updated successfully!";
    } else {
        echo "Error updating links: " . mysqli_error($conn);
    }
}

// Redirect back to the previous page (or a confirmation page) after processing
header('Location: settings.php'); // Replace `your_previous_page.php` with the actual page to redirect to
exit();
?>
