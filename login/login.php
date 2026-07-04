<?php
session_start();
require '../includes/logo.php'; // Include the logo fetching function
require '../includes/db.php'; 
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login - Rizal Agri Cultiva</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Pirata+One&family=Poppins:ital,wght@0,100;0,200;0,300;0,400;0,500;0,600;0,700;0,800;0,900;1,100;1,200;1,300;1,400;1,500;1,600;1,700;1,800;1,900&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="../css/loginV1.css">
</head>
<body>
    <div class="login-page">
        <!-- Form Section -->
        <div class="form-container">
            <div class="logo">
                <div class="logo-text">
                    <img src="<?= getLogoPath($conn) ?>" alt="AgriWeb Logo">
                    <div>
                        <h1>RIZAL AGRI CULTIVA</h1>
                        <p>Web Management System</p>
                    </div>
                </div>
            </div>
            <h2>Log in</h2>
            <?php if (isset($_SESSION['error'])): ?>
                <p class="error"><?php echo $_SESSION['error']; unset($_SESSION['error']); ?></p>
            <?php endif; ?>
            <form action="../includes/auth.php" method="POST">
                <div class="form-group">
                    <label for="email">Email or Username</label>
                    <input type="text" id="email" name="email" 
                        value="<?php echo isset($_SESSION['login_data']['email']) ? htmlspecialchars($_SESSION['login_data']['email']) : ''; ?>" 
                        placeholder="Email or Username" required>
                </div>
                <div class="form-group2">
                    <label for="password">Password</label>
                    <div class="password-wrapper">
                        <input type="password" id="password" name="password" required placeholder="Password">
                        <button type="button" onclick="togglePassword()" class="toggle-password">
                            <img id="eye-icon" src="../assets/unshowEye.png" alt="Toggle Password" />
                        </button>
                    </div>
                </div>
                <a href="forgot_password.php" class="forgot-password">Forgot Password?</a>
                <div class="button-container">
                  <button type="submit" name="login" class="btn-login">Log in</button>
                </div>
            </form>
        </div>
        <!-- Image Section -->
        <div class="image-container">
            <img src="../assets/LoginHeroImage.jfif" alt="Farming">
        </div>
    </div>
    <script>
        function togglePassword() {
            const passwordField = document.getElementById('password');
            const eyeIcon = document.getElementById('eye-icon');

            if (passwordField.type === 'password') {
                passwordField.type = 'text';
                eyeIcon.src = '../assets/showEye.png'; // Path to the open-eye image
            } else {
                passwordField.type = 'password';
                eyeIcon.src = '../assets/unshowEye.png'; // Path to the closed-eye image
            }
        }
    </script>
</body>
</html>
