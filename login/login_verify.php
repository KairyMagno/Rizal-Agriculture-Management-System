<?php
session_start();
require '../includes/db.php'; // Make sure you have the db connection here
require '../includes/logo.php'; // Include the logo fetching function

// Check if the email is set for OTP verification
if (!isset($_SESSION['email_verification'])) {
    $_SESSION['error'] = "Session expired. Please log in again to receive a new OTP.";
    header("Location: login.php");
    exit;
}

$email = $_SESSION['email_verification'];

// Fetch the OTP, expiry data, role, id, and username from the database
$query = $conn->prepare("SELECT otp, otp_expiry, role, id, username FROM users WHERE email = ?");
$query->bind_param("s", $email);
$query->execute();
$result = $query->get_result();
$user = $result->fetch_assoc();

// Check if user exists and if the OTP has expired
if (!$user) {
    $_SESSION['error'] = "No user found with that email address.";
    header("Location: login.php");
    exit;
}

if (strtotime($user['otp_expiry']) < time()) {
    $_SESSION['error'] = "OTP expired. Please log in again to receive a new OTP.";
    header("Location: login.php");
    exit;
}

if (isset($_POST['verify'])) {
    $otp = trim($_POST['otp']);

    // Validate OTP entered by the user
    if ($user['otp'] == $otp) {
        // OTP is correct, clear the OTP and expiry time from the database
        $update = $conn->prepare("UPDATE users SET otp = NULL, otp_expiry = NULL, status = 'active' WHERE email = ?");
        $update->bind_param("s", $email);
        if ($update->execute()) {
            // Store session variables after OTP is successfully validated
            $_SESSION['user_id'] = $user['id'];
            $_SESSION['username'] = $user['username'];
            $_SESSION['role'] = $user['role'];

            // Log the user activity
            $activity = "User logged in successfully";
            $log_query = $conn->prepare("INSERT INTO activity_log (username, activity) VALUES (?, ?)");
            $log_query->bind_param("ss", $user['username'], $activity);
            if (!$log_query->execute()) {
                $_SESSION['error'] = "Failed to log activity. Please try again.";
                header("Location: login.php");
                exit;
            }

            // Redirect based on user role
            if ($user['role'] == 'admin') {
                header("Location: ../admin/dashboard.php"); // Redirect to the admin dashboard
            } elseif ($user['role'] == 'staff') {
                header("Location: ../admin/announcement.php"); // Redirect to the staff dashboard
            } else {
                $_SESSION['error'] = "Invalid role detected.";
                header("Location: login.php");
            }
            exit;
        } else {
            $_SESSION['error'] = "Failed to update user status. Please try again.";
        }
    } else {
        $_SESSION['error'] = "Invalid OTP. Please try again.";
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
                    <label for="otp">Enter One Time Password</label>
                    <input type="text" id="otp" name="otp" placeholder="Enter OTP" required>
                    <?php if (isset($_SESSION['error'])): ?>
                        <p class="error"><?php echo $_SESSION['error']; unset($_SESSION['error']); ?></p>
                    <?php endif; ?>
                </div>
                <p id="otp-timer-text">
                    Didn't receive the verification OTP? 
                    <span id="timer">60</span>
                    <button type="button" id="resend-btn">Resend</button>
                </p>
                <div class="button-container">
                    <button type="submit" name="verify" class="btn-verify">Verify</button>
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

        if (localStorage.getItem('otp_timer')) {
            const savedTime = parseInt(localStorage.getItem('otp_timer'), 10);
            const savedTimestamp = parseInt(localStorage.getItem('otp_timestamp'), 10);
            const elapsed = Math.floor((Date.now() - savedTimestamp) / 1000);
            timer = Math.max(savedTime - elapsed, 0);
        }

        function startCountdown() {
            resendButton.style.display = 'none';
            timer = 60;
            timerDisplay.textContent = timer;

            countdownInterval = setInterval(function () {
                timer--;
                if (timer > 0) {
                    timerDisplay.textContent = timer;
                    localStorage.setItem('otp_timer', timer);
                    localStorage.setItem('otp_timestamp', Date.now());
                } else {
                    clearInterval(countdownInterval);
                    timerDisplay.textContent = '';
                    resendButton.style.display = 'inline';
                    localStorage.removeItem('otp_timer');
                    localStorage.removeItem('otp_timestamp');
                }
            }, 1000);
        }

        resendButton.addEventListener('click', function () {
            fetch('../includes/resend_otp.php', {
                method: 'POST',
                body: JSON.stringify({ action: 'resend', email: '<?php echo $email; ?>' }),
                headers: { 'Content-Type': 'application/json' },
            })
                .then((response) => response.json())
                .then((data) => {
                    if (data.success) {
                        alert('A new OTP has been sent to your email.');
                        timer = 60;
                        startCountdown();
                    } else {
                        alert('Failed to resend OTP. Please try again.');
                    }
                })
                .catch((error) => {
                    console.error('Error:', error);
                    alert('An error occurred. Please try again.');
                });
        });

        startCountdown();
    </script>
</body>
</html>
