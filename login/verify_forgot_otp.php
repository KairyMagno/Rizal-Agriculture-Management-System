<?php
session_start();
require '../includes/db.php';
require '../includes/logo.php'; // Include the logo fetching function

if (!isset($_SESSION['email_verification'])) {
    $_SESSION['error'] = "Session expired. Please request a new OTP.";
    header("Location: forgot_password.php");
    exit;
}

$email = $_SESSION['email_verification'];
$query = $conn->prepare("SELECT otp, otp_expiry FROM users WHERE email = ?");
$query->bind_param("s", $email);
$query->execute();
$result = $query->get_result();
$user = $result->fetch_assoc();

if (isset($_POST['verify_otp'])) {
    $otp = trim($_POST['otp']);

    if ($user && $user['otp'] === $otp && strtotime($user['otp_expiry']) >= time()) {
        // OTP is valid
        $update = $conn->prepare("UPDATE users SET otp = NULL, otp_expiry = NULL WHERE email = ?");
        $update->bind_param("s", $email);
        $update->execute();

        header("Location: reset_password.php");
        exit;
    } else {
        $_SESSION['error'] = "Invalid or expired OTP.";
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Verify OTP - Rizal Agri Cultiva</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Pirata+One&family=Poppins:ital,wght@0,100;0,200;0,300;0,400;0,500;0,600;0,700;0,800;0,900;1,100;1,200;1,300;1,400;1,500;1,600;1,700;1,800;1,900&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="../css/verify.css">
</head>
<body>
    <div class="verify-page">
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
            <h2>Verify OTP</h2>
            <form action="" method="POST">
                <div class="form-group">
                    <label for="otp">Enter OTP:</label>
                    <input type="text" name="otp" id="otp" required>
                    <?php if (isset($_SESSION['error'])): ?>
                        <p class="error"><?php echo $_SESSION['error']; unset($_SESSION['error']); ?></p>
                    <?php endif; ?>
                </div>
                
                <!-- Countdown Timer & Resend Button -->
                <p id="otp-timer-text">
                    Didn't receive the verification OTP? 
                    <span id="timer">60</span>
                    <button type="button" id="resend-btn">Resend</button>
                </p>
                
                <div class="button-container">
                    <button type="submit" name="verify_otp" class="btn-verify">Verify</button>
                </div>
            </form>
        </div>
        <div class="image-container">
            <img src="../assets/LoginHeroImage.jfif" alt="Farming">
        </div>
    </div>

    <script>
        let timer = 60;
        let countdownInterval;
        const resendButton = document.getElementById('resend-btn');
        const timerDisplay = document.getElementById('timer');

        // Function to start the countdown
        function startCountdown() {
            resendButton.style.display = 'none'; // Hide button during countdown
            timer = 60; // Reset the timer value
            timerDisplay.textContent = timer; // Update the timer display

            countdownInterval = setInterval(function () {
                timer--;
                if (timer >= 0) {
                    timerDisplay.textContent = timer;
                } else {
                    clearInterval(countdownInterval); // Stop the countdown
                    timerDisplay.textContent = ''; // Clear the timer
                    resendButton.style.display = 'inline'; // Show button when timer ends
                }
            }, 1000);
        }

        // Add event listener to the resend button
        resendButton.addEventListener('click', function () {
            // Mock API call to resend OTP
            fetch('../includes/resend_otp.php', {
                method: 'POST',
                body: JSON.stringify({ action: 'resend', email: '<?php echo $email; ?>' }),
                headers: { 'Content-Type': 'application/json' },
            })
                .then((response) => response.json())
                .then((data) => {
                    if (data.success) {
                        alert('A new OTP has been sent to your email.');
                        startCountdown(); // Restart the countdown
                    } else {
                        alert('Failed to resend OTP. Please try again.');
                    }
                })
                .catch((error) => {
                    console.error('Error:', error);
                    alert('An error occurred. Please try again.');
                });
        });

        // Start the initial countdown when the page loads
        startCountdown();
    </script>
</body>
</html>
