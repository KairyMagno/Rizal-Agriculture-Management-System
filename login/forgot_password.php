<?php
session_start();
require '../includes/db.php';
require '../includes/send_otp.php';
require '../includes/logo.php'; // Include the logo fetching function


if (isset($_POST['request_reset'])) {
    $input = trim($_POST['email_or_username']);

    // Determine if the input is an email or username
    if (filter_var($input, FILTER_VALIDATE_EMAIL)) {
        $query = $conn->prepare("SELECT id, email FROM users WHERE email = ?");
        $query->bind_param("s", $input);
    } else {
        $query = $conn->prepare("SELECT id, email FROM users WHERE username = ?");
        $query->bind_param("s", $input);
    }

    $query->execute();
    $result = $query->get_result();

    if ($result->num_rows > 0) {
        $user = $result->fetch_assoc();
        $email = $user['email'];

        // Generate OTP
        $otp = rand(100000, 999999);
        $otp_expiry = date("Y-m-d H:i:s", strtotime("+10 minutes"));

        // Update OTP in database
        $update = $conn->prepare("UPDATE users SET otp = ?, otp_expiry = ? WHERE email = ?");
        $update->bind_param("sss", $otp, $otp_expiry, $email);
        $update->execute();

        // Send OTP
        if (sendOtpEmail($email, $otp)) {
            $_SESSION['email_verification'] = $email;
            header("Location: verify_forgot_otp.php");
            exit;
        } else {
            $_SESSION['error'] = "Failed to send OTP. Please try again.";
        }
    } else {
        $_SESSION['error'] = "No account found with that email or username.";
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Forgot Password</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Pirata+One&family=Poppins:ital,wght@0,100;0,200;0,300;0,400;0,500;0,600;0,700;0,800;0,900;1,100;1,200;1,300;1,400;1,500;1,600;1,700;1,800;1,900&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="../css/forgot_pass.css">
</head>
<body>
  <div class="forgot-password-page">
    <div class="form-container">
      <div class="logo">
        <div class="logo-text">
          <img src="<?= getLogoPath($conn) ?>" alt="AgriWeb Logo" class="logo">
          <div>
            <h1>RIZAL AGRI CULTIVA</h1>
            <p>Web Management System</p>
          </div>
        </div>
      </div>
      <h2>Forgot Password</h2>
      <?php if (isset($_SESSION['error'])): ?>
          <p class="error"><?php echo $_SESSION['error']; unset($_SESSION['error']); ?></p>
      <?php endif; ?>
      <form action="" method="POST">
        <label for="email_or_username">Email or Username</label>
        <input type="text" name="email_or_username" id="email_or_username" placeholder="Enter your email or username" required>
        <button type="submit" name="request_reset">Send OTP</button>
      </form>
      <a href="login.php" class="back-to-login">Back to Login</a>
    </div>
    <div class="image-container">
      <img src="../assets/LoginHeroImage.jfif" alt="Password Reset Illustration">
    </div>
  </div>
</body>
</html>