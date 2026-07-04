<?php
include('../includes/db.php');

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Get the question and answer from the form
    $question = mysqli_real_escape_string($conn, $_POST['question']);
    $answer = mysqli_real_escape_string($conn, $_POST['answer']);
    $created_at = date('Y-m-d H:i:s');

    // Query to insert the new FAQ into the database
    $query = "INSERT INTO faqs (question, answer, created_at) VALUES ('$question', '$answer', '$created_at')";
    
    if (mysqli_query($conn, $query)) {
        // Log the activity
        $faq_id = mysqli_insert_id($conn); // Get the ID of the newly inserted FAQ
        $username = 'admin'; // Replace this with the actual username from the session
        $activity = "Added new FAQ: ID $faq_id";
        $timestamp = date('Y-m-d H:i:s');

        $logQuery = "INSERT INTO activity_log (username, activity, timestamp) VALUES ('$username', '$activity', '$timestamp')";
        if (!mysqli_query($conn, $logQuery)) {
            echo 'Error logging activity: ' . mysqli_error($conn);
        }

        // Redirect to the FAQ page after insertion
        header('Location: settings.php');
        exit;
    } else {
        echo 'Error inserting FAQ: ' . mysqli_error($conn);
    }

    // Close the database connection
    mysqli_close($conn);
}
?>
