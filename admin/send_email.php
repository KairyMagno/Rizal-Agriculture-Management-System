<?php
session_start();
// Include PHPMailer
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;
require '../vendor/autoload.php'; // Adjust the path as needed
include('../includes/db.php'); // Make sure the database connection is included

// Fetch the email configuration details from the database
$configQuery = "SELECT email, password, company_name FROM company_email WHERE id = 1"; // Replace 'your_table_name' and 'id' as needed
$configStmt = $conn->prepare($configQuery);
$configStmt->execute();
$configResult = $configStmt->get_result();
$configRow = $configResult->fetch_assoc();

if ($configRow) {
    $companyEmail = $configRow['email'];
    $password = $configRow['password'];
    $companyName = $configRow['company_name'];
} else {
    // Handle case where configuration is not found
    die('Error: Email configuration not found.');
}

// Check if the form is submitted
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Sanitize and validate input
    $recipientEmail = filter_var($_POST['recipient_email'], FILTER_VALIDATE_EMAIL);
    $queryId = $_POST['query_id']; // Get the ID from the form submission
    $answer = nl2br(htmlspecialchars($_POST['answer']));

    // Ensure the recipient email is valid
    if (!$recipientEmail) {
        header('Location: email.php?status=error&message=' . urlencode('Invalid email address'));
        exit();
    }

    // Fetch the original message and last name from the database
    $query = "SELECT message, last_name FROM contact_queries WHERE email = ?";
    $stmt = $conn->prepare($query);
    $stmt->bind_param("s", $recipientEmail);
    $stmt->execute();
    $result = $stmt->get_result();
    $row = $result->fetch_assoc();

    if (!$row) {
        header('Location: email.php?status=error&message=' . urlencode('No matching query found.'));
        exit();
    }

    // Prepare the original message for the email body
    $originalMessage = nl2br(htmlspecialchars($row['message']));
    $lastName = htmlspecialchars($row['last_name']);

    $mail = new PHPMailer(true);

    try {
        // Server settings
        $mail->isSMTP();
        $mail->Host = 'smtp.gmail.com'; // Correct SMTP host for Gmail
        $mail->SMTPAuth = true;
        $mail->Username = $companyEmail; // Use the email from the database
        $mail->Password = $password; // Use the password from the database (consider using an environment variable for security)
        $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
        $mail->Port = 587;

        // Recipients
        $mail->setFrom($companyEmail, $companyName); // Set the sender's email and name
        $mail->addAddress($recipientEmail);
        $mail->addReplyTo($companyEmail, $companyName);

        // Content
        $mail->isHTML(true);
        $mail->Subject = 'Response to Your Inquiry';
        $mail->Body = "<p>Dear $lastName,</p>
                       <p><strong>Here is the response to your query:</strong></p>
                       <p>$answer</p>
                       <p>Original Message:</p>
                       <p>$originalMessage</p>
                       <p>Best regards,</p>
                       <p>$companyName</p>";

        // Send email
        $mail->send();

        // Update the status to "answered" in the database after successful email send
        $statusUpdateQuery = "UPDATE contact_queries SET status = 'answered' WHERE id = ?";
        $statusStmt = $conn->prepare($statusUpdateQuery);
        $statusStmt->bind_param("i", $queryId);
        if ($statusStmt->execute()) {
            // Log the activity if successful
            if (isset($_SESSION['username'])) {
                $username = $_SESSION['username']; // Use the logged-in user's username
                $activity = "Answered email inquiry from $recipientEmail";

                // Log the activity in the database
                $activityQuery = "INSERT INTO activity_log (username, activity) VALUES (?, ?)";
                $activityStmt = $conn->prepare($activityQuery);
                $activityStmt->bind_param("ss", $username, $activity);
                $activityStmt->execute();
            }

            // Redirect on successful email send
            header('Location: email.php?status=success');
        } else {
            // Handle error if status update fails
            echo "<script>alert('Failed to update status in the database.'); window.history.back();</script>";
        }
        $statusStmt->close();
    } catch (Exception $e) {
        // Log the error and redirect with error details
        error_log('Mailer Error: ' . $mail->ErrorInfo);
        header('Location: email.php?status=error&message=' . urlencode($e->getMessage()));
    }
}
?>
