<?php
session_start();
require 'db.php'; // Database connection
require 'send_otp.php'; // Email OTP sender
date_default_timezone_set('Asia/Manila'); // Philippine Time

session_regenerate_id(true); // Prevent session fixation attacks

// Helper function for redirection with error message
function redirectWithError($location, $message) {
    $_SESSION['error'] = $message;
    header("Location: $location");
    exit;
}

// Login logic
if (isset($_POST['login'])) {
    $emailOrUsername = trim($_POST['email']); // Can be email or username
    $password = $_POST['password'];

    // Save input in session for repopulating
    $_SESSION['login_data'] = ['email' => $emailOrUsername];

    // Fetch user data
    $query = $conn->prepare("SELECT id, username, email, password, role, failed_attempts, locked_until FROM users WHERE email = ? OR username = ?");
    $query->bind_param("ss", $emailOrUsername, $emailOrUsername);
    $query->execute();
    $result = $query->get_result();
    $user = $result->fetch_assoc();

    if ($user) {
        // Handle account lock
        if ($user['locked_until'] && strtotime($user['locked_until']) > time()) {
            $remaining_time = ceil((strtotime($user['locked_until']) - time()) / 60);
            redirectWithError("../login/login.php", "This account is locked. Please try again after $remaining_time minutes.");
        }

        // Verify password
        if (password_verify($password, $user['password'])) {
            // Reset failed attempts and unlock account
            $reset = $conn->prepare("UPDATE users SET failed_attempts = 0, locked_until = NULL WHERE email = ? OR username = ?");
            $reset->bind_param("ss", $emailOrUsername, $emailOrUsername);
            $reset->execute();

            // Generate OTP
            $otp = random_int(100000, 999999);
            $otp_expiry = date("Y-m-d H:i:s", strtotime("+5 minutes"));

            $update = $conn->prepare("UPDATE users SET otp = ?, otp_expiry = ? WHERE email = ? OR username = ?");
            $update->bind_param("ssss", $otp, $otp_expiry, $emailOrUsername, $emailOrUsername);
            $update->execute();

            if (sendOtpEmail($user['email'], $otp)) {
                $_SESSION['email_verification'] = $user['email'];
                $_SESSION['user_id'] = $user['id'];
                unset($_SESSION['login_data']); // Clear saved input
                header("Location: ../login/login_verify.php");
                exit;
            } else {
                redirectWithError("../login/login.php", "Failed to send OTP. Please try again.");
            }
        } else {
            // Increment failed attempts and lock account if needed
            $failed_attempts = $user['failed_attempts'] + 1;
            $locked_until = null;

            if ($failed_attempts >= 3) {
                $locked_until = date("Y-m-d H:i:s", strtotime("+1 hour"));
                $error_message = "Too many failed attempts. This account is locked for 1 hour.";
            } else {
                $error_message = "Invalid email/username or password. Attempt $failed_attempts of 3.";
            }

            $update = $conn->prepare("UPDATE users SET failed_attempts = ?, locked_until = ? WHERE email = ? OR username = ?");
            $update->bind_param("isss", $failed_attempts, $locked_until, $emailOrUsername, $emailOrUsername);
            $update->execute();

            redirectWithError("../login/login.php", $error_message);
        }
    } else {
        redirectWithError("../login/login.php", "Invalid email/username or password.");
    }
}

// OTP verification logic
if (isset($_POST['verify_otp'])) {
    $otp = trim($_POST['otp']);
    $email = $_SESSION['email_verification'];

    $query = $conn->prepare("SELECT id, otp, otp_expiry, status FROM users WHERE email = ?");
    $query->bind_param("s", $email);
    $query->execute();
    $result = $query->get_result();
    $user = $result->fetch_assoc();

    if ($user && $otp == $user['otp'] && strtotime($user['otp_expiry']) > time()) {
        // OTP valid, activate account
        $update = $conn->prepare("UPDATE users SET otp = NULL, otp_expiry = NULL, status = 'active' WHERE email = ?");
        $update->bind_param("s", $email);
        $update->execute();

        unset($_SESSION['email_verification']);
        $_SESSION['user_id'] = $user['id'];
        $_SESSION['success'] = "Login successful!";
        header("Location: ../dashboard.php");
        exit;
    }

    redirectWithError("../login/login_verify.php", "Invalid or expired OTP.");
}
?>
