<?php
session_start();
require '../includes/db.php';
require '../includes/logo.php'; // Include the logo fetching function

if (!isset($_SESSION['email_verification'])) {
    // Redirect user if they have not verified their email via OTP
    header("Location: forgot_password.php");
    exit;
}

if (isset($_POST['reset_password'])) {
    $new_password = trim($_POST['new_password']);
    $confirm_password = trim($_POST['confirm_password']);

    // Validate input
    if (empty($new_password) || empty($confirm_password)) {
        $_SESSION['error'] = "Both fields are required.";
    } elseif ($new_password !== $confirm_password) {
        $_SESSION['error'] = "Passwords do not match.";
    } elseif (strlen($new_password) < 8 || !preg_match('/[A-Z]/', $new_password) || !preg_match('/[0-9]/', $new_password)) {
        $_SESSION['error'] = "Password must be at least 8 characters long, include an uppercase letter, and a number.";
    } else {
        // Hash the new password
        $hashed_password = password_hash($new_password, PASSWORD_BCRYPT);
        $email = $_SESSION['email_verification'];

        // Update password in database
        $update = $conn->prepare("UPDATE users SET password = ?, otp = NULL, otp_expiry = NULL WHERE email = ?");
        $update->bind_param("ss", $hashed_password, $email);
        if ($update->execute()) {
            $_SESSION['success'] = "Password reset successfully.";
            unset($_SESSION['email_verification']);
            header("Location: login.php");
            exit;
        } else {
            $_SESSION['error'] = "Failed to reset password. Please try again.";
        }
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Reset Password - Rizal Agri Cultiva</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Pirata+One&family=Poppins:ital,wght@0,100;0,200;0,300;0,400;0,500;0,600;0,700;0,800;0,900&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="../css/reset_passwordV1.css">
</head>
<body>
    <div class="reset-password-page">
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
            <h2>Reset Your Password</h2>
            <?php if (isset($_SESSION['error'])): ?>
                <p class="error"><?php echo $_SESSION['error']; unset($_SESSION['error']); ?></p>
            <?php endif; ?>
            <form action="" method="POST" onsubmit="return validateForm()">
                <div class="form-group">
                    <label for="new_password">New Password</label>
                    <div class="password-wrapper">
                        <input type="password" id="new_password" name="new_password" required placeholder="Password">
                        <button type="button" onclick="togglePassword('new_password', this)" class="toggle-password">
                            <img src="../assets/unshowEye.png" alt="Show Password" id="password-icon">
                        </button>
                    </div>
                    <span id="password-strength"></span>
                </div>
                <div class="form-group">
                    <label for="confirm_password">Confirm Password</label>
                    <div class="password-wrapper">
                        <input type="password" id="confirm_password" name="confirm_password" required placeholder="Confirm Password">
                        <button type="button" onclick="togglePassword('confirm_password', this)" class="toggle-password">
                            <img src="../assets/unshowEye.png" alt="Show Password" id="confirm-password-icon">
                        </button>
                    </div>
                    <p id="password-warning" class="error"></p>
                </div>
                <div class="button-container">
                    <button type="submit" name="reset_password" class="btn-reset">Reset Password</button>
                </div>
            </form>
        </div>
        <div class="image-container">
            <img src="../assets/LoginHeroImage.jfif" alt="Password Reset">
        </div>
    </div>
    <script>
        // Toggle Password Visibility
        function togglePassword(fieldId, button) {
            const field = document.getElementById(fieldId);
            const icon = button.querySelector("img");

            if (field.type === "password") {
                field.type = "text";
                icon.src = "../assets/showEye.png"; // Path to your open-eye image
                icon.alt = "Hide Password";
            } else {
                field.type = "password";
                icon.src = "../assets/unshowEye.png"; // Path to your closed-eye image
                icon.alt = "Show Password";
            }
        }

        // Password Strength Validation
        document.addEventListener('DOMContentLoaded', () => {
            const passwordField = document.getElementById('new_password');
            const passwordStrength = document.getElementById('password-strength');

            passwordField.addEventListener('input', () => {
                const value = passwordField.value;
                if (value.length < 8) {
                    passwordStrength.textContent = "Weak: Password must be at least 8 characters.";
                    passwordStrength.style.color = "red";
                } else if (!/[A-Z]/.test(value)) {
                    passwordStrength.textContent = "Weak: Include at least one uppercase letter.";
                    passwordStrength.style.color = "red";
                } else if (!/[0-9]/.test(value)) {
                    passwordStrength.textContent = "Weak: Include at least one number.";
                    passwordStrength.style.color = "red";
                } else {
                    passwordStrength.textContent = "Strong password.";
                    passwordStrength.style.color = "green";
                }
            });
        });

        // Validate Form Before Submission
        function validateForm() {
            const password = document.getElementById('new_password').value;
            const confirmPassword = document.getElementById('confirm_password').value;
            const warning = document.getElementById('password-warning');

            if (password !== confirmPassword) {
                warning.textContent = "Passwords do not match.";
                return false;
            }
            warning.textContent = "";
            return true;
        }
    </script>
</body>
</html>