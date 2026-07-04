<?php
require_once __DIR__ . '/../vendor/autoload.php';
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;
require '../includes/db.php'; // Include the database connection

function sendOtpEmail($email, $otp) {
    // Query to fetch SMTP credentials from the database
    global $conn; // Use the existing connection from db.php
    $query = "SELECT email, password, company_name FROM company_email WHERE id = 1"; // Adjust the WHERE clause as needed
    $result = $conn->query($query);

    if ($result->num_rows > 0) {
        // Fetch the credentials from the database
        $row = $result->fetch_assoc();
        $smtpEmail = $row['email'];
        $smtpPassword = $row['password'];
        $companyName = $row['company_name'];
    } else {
        error_log('SMTP credentials not found in the database.');
        return false;
    }

    $mail = new PHPMailer(true);
    try {
        // SMTP Configuration
        $mail->isSMTP();
        $mail->Host = 'smtp.gmail.com'; // Gmail SMTP server
        $mail->SMTPAuth = true;
        $mail->Username = $smtpEmail; // Use email fetched from the database
        $mail->Password = $smtpPassword; // Use password fetched from the database
        $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
        $mail->Port = 587;

        // Email content
        $mail->setFrom($smtpEmail, $companyName); // Use company name from the database
        $mail->addAddress($email);
        $mail->isHTML(true);
        $mail->Subject = 'Your OTP for Verification';
        $mail->Body = "Your OTP is: <b>$otp</b><br>This OTP will expire in 5 minutes.";

        $mail->send();
        return true;
    } catch (Exception $e) {
        // Log error details for debugging
        error_log('Email failed: ' . $e->getMessage());
        return false;
    }
}
?>
