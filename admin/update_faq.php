<?php
require '../includes/db.php'; // Ensure database connection

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $faq_id = intval($_POST['faq_id']);
    $question = mysqli_real_escape_string($conn, $_POST['question']);
    $answer = mysqli_real_escape_string($conn, $_POST['answer']);

    // Update FAQ query
    $query = "UPDATE faqs SET question = '$question', answer = '$answer' WHERE faq_id = $faq_id";
    if (mysqli_query($conn, $query)) {
        // Log the activity
        $username = 'admin'; // Replace this with the actual username, e.g., from session
        $activity = "Updated FAQ ID $faq_id";
        $timestamp = date('Y-m-d H:i:s');

        $logQuery = "INSERT INTO activity_log (username, activity, timestamp) VALUES ('$username', '$activity', '$timestamp')";
        if (!mysqli_query($conn, $logQuery)) {
            echo 'Error logging activity: ' . mysqli_error($conn);
        }

        // Redirect on success
        header('Location: settings.php?status=updated');
        exit;
    } else {
        echo 'Error updating FAQ: ' . mysqli_error($conn);
    }
}
?>
