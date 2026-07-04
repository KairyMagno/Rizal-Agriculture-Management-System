<?php
session_start();
require '../includes/db.php'; // Database connection
require 'send_otp.php'; // Include the function to send OTP email

// Ensure the request is valid
$data = json_decode(file_get_contents('php://input'), true);

if (isset($data['action']) && $data['action'] === 'resend' && isset($data['email'])) {
    $email = $data['email'];

    // Validate email format
    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        echo json_encode(['success' => false, 'message' => 'Invalid email format']);
        exit;
    }

    // Generate a new OTP and expiry time
    $new_otp = rand(100000, 999999);
    $new_otp_expiry = date("Y-m-d H:i:s", strtotime("+10 minutes"));

    // Update the OTP and expiry in the database
    $update = $conn->prepare("UPDATE users SET otp = ?, otp_expiry = ? WHERE email = ?");
    $update->bind_param("sss", $new_otp, $new_otp_expiry, $email);

    if ($update->execute()) {
        // Send the OTP via email
        if (sendOtpEmail($email, $new_otp)) {
            echo json_encode(['success' => true, 'message' => 'OTP sent successfully']);
        } else {
            echo json_encode(['success' => false, 'message' => 'Failed to send OTP. Please try again later.']);
        }
    } else {
        echo json_encode(['success' => false, 'message' => 'Failed to update OTP in the database']);
    }
} else {
    echo json_encode(['success' => false, 'message' => 'Invalid request']);
}
?>
