<?php
session_start();
include('../includes/db.php');
require '../includes/logo.php'; // Include the logo fetching function

// Fetch contact queries from the database
$query = "SELECT id, role, first_name, last_name, address, email, message, submission_date, status FROM contact_queries ORDER BY submission_date DESC";
$result = $conn->query($query);

// Display success or error messages
$status = isset($_GET['status']) ? $_GET['status'] : '';
if ($status == 'success') {
    echo "<script>alert('Form submitted successfully.');</script>";
} elseif ($status == 'error') {
    echo "<script>alert('There was an error submitting the form. Please try again.');</script>";
}

if (!isset($_SESSION['role']) || ($_SESSION['role'] != 'staff' && $_SESSION['role'] != 'admin')) {
    header("Location: ../login/login.php");
    exit;
}

if ($_SESSION['role'] == 'staff') {
    $hide_admin_elements = true;
} else {
    $hide_admin_elements = false;
}
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>About RAC</title>
    <link rel="stylesheet" href="css/styles.css">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Pirata+One&family=Poppins:ital,wght@0,100;0,200;0,300;0,400;0,500;0,600;0,700;0,800;0,900&display=swap"
        rel="stylesheet">
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css" rel="stylesheet">
    <link rel="stylesheet" href="css/header.css">
    <link rel="stylesheet" href="css/sidebar.css">
    <style>
        body{
            font-family: 'Poppins';
        }
        .accordion {
            background-color: #338019;
            color: #444;
            cursor: pointer;
            padding: 10px;
            width: 100%;
            border: none;
            text-align: left;
            outline: none;
            margin-bottom: 5px;
            border-radius: 5px;
            display: flex;
            justify-content: space-between;
            align-items: center;
            color: white;
            font-size: 16px;
        }

        .accordion-content {
            display: flex;
            justify-content: space-between;
            width: 100%;
        }

        .email {
            flex: 1; /* Adjust as needed */
            text-align: left;
        }

        .submission-date {
            margin-right: 20px; /* Space between email and date */
            text-align: right;
            color: #f1f1f1; /* Optional: Adjust color for contrast */
        }

        .arrow {
            display: inline-block;
            transition: transform 0.3s ease;
        }

        .panel {
            display: none;
            overflow: hidden;
            padding: 10px;
            background-color: #f9f9f9;
            border: 1px solid #ddd;
            border-radius: 5px;
        }

        .panel p {
            margin: 0;
            font-size: 14px;
        }

        .accordion.active .arrow {
            transform: rotate(-180);
        }

        .answer-btn {
            background-color: #338019; /* Green button color */
            color: white; /* White text */
            border: none;
            padding: 8px 12px;
            font-size: 14px;
            cursor: pointer;
            border-radius: 4px;
            float: right;
            margin-top: 10px;
        }

        .answer-btn:hover {
            background-color: #276814; /* Darker green on hover */
        }

        .close-btn {
            background-color: red; /* Green button color */
            color: white; /* White text */
            border: none;
            padding: 8px 12px;
            font-size: 14px;
            cursor: pointer;
            border-radius: 4px;
            margin-top: 10px;
        }

        .answer-btn:hover {
            opacity: 0.8;
        }

        .modal {
            display: none; /* Hidden by default */
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background-color: rgba(0, 0, 0, 0.5); /* Semi-transparent background */
            z-index: 1000;
            overflow: auto;
            display: flex;
            justify-content: center;
            align-items: center;
        }

        .modal-content {
            background-color: #ffffff;
            padding: 20px;
            border-radius: 8px;
            box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1);
            width: 500px;
            max-width: 100%; /* Responsive width */
        }

        .modal-content h2 {
            margin: 0 0 10px;
            font-size: 24px;
            color: #338019;
        }

        textarea {
            width: 100%;
            height: 150px;
            padding: 10px;
            margin-bottom: 15px;
            border: 1px solid #ccc;
            border-radius: 4px;
            resize: vertical; /* Allow vertical resizing */
            box-sizing: border-box;
        }

        .answer-submit-btn {
            display: block;
            width: 100%;
            padding: 10px;
            background-color: #338019;
            color: #ffffff;
            border: none;
            border-radius: 4px;
            cursor: pointer;
            font-size: 16px;
            transition: opacity 0.3s ease;
        }

        .answer-submit-btn:hover {
            opacity: 0.8;
        }

        .close {
            position: absolute;
            top: 10px;
            right: 10px;
            font-size: 24px;
            cursor: pointer;
            color: #555;
        }

        .close:hover {
            color: #000;
        }

    </style>
</head>

<body>
    <!-- Sidebar -->
    <div class="sidebar">
        <div class="header-title">
            <img src="<?= getLogoPath($conn) ?>" alt="AgriWeb Logo" class="header-logo">
            <div class="logo-text">
                <h1 style="color:black; margin: 0; font-size: 18px; letter-spacing: 1px; font-weight: 500; margin-top: 10px;">RIZAL</h1>
                <h1 style="color:black; margin: 0; font-size: 18px; letter-spacing: 1px; font-weight: 500; margin-top: -5px;">AGRI</h1>
                <h1 style="color:black; margin: 0; font-size: 18px; letter-spacing: 1px; font-weight: 500; margin-top: -5px;">CULTIVA</h1>
            </div>
        </div>
        <ul class="nav-links">
            <li><a href="announcement.php"><i class="fas fa-bullhorn"></i> Announcements</a></li>
            <li><a href="articles.php"><i class="fas fa-file-alt"></i> News & Articles</a></li>
            <li class="dropdown">
                <a href="multimedia.php" class="dropbtn"><i class="fas fa-camera"></i> Multimedia</a>
                <ul class="dropdown-content" id="dropdown-container"></ul>
            </li>
            <li><a href="about.php"><i class="fas fa-file"></i> About RAC</a></li>
            <li><a href="email.php" class= "active"><i class="fas fa-envelope"></i> Email Inquiry</a></li>
            <li style="margin-bottom: 5px;"><a href="archive.php" ><i class="fas fa-archive"></i> Archive</a></li> <!-- Archive added here -->

            <?php if (!$hide_admin_elements): ?>
                <p style="color:gray; margin: 0;">Admin</p>
                <li><a href="dashboard.php"><i class="fas fa-tachometer-alt"></i> Dashboard</a></li>
                <li><a href="reports.php"><i class="fas fa-chart-line"></i> Reports</a></li>
                <li><a href="user.php"><i class="fas fa-users"></i> User Management</a></li>
                <li style="margin-bottom: 0;"><a href="settings.php"><i class="fas fa-cogs"></i> Settings</a></li>
            <?php endif; ?>
        </ul>
        <button class="logout-btn" style="text-align:center;" onclick="logout()">
            <i class="fas fa-sign-out-alt logout-icon" style="margin-left:-20px;"></i> Logout
        </button>
    </div>
    <main>
        <div class="header">
            <h1 style="color:green; font-size:28px;">Email Inquiry</h1>
        </div>
        <div class="container" style="padding: 5% 10%;">
            <?php if ($result->num_rows > 0): ?>
                <?php while ($row = $result->fetch_assoc()): ?>
                    <button class="accordion" style="background-color: <?= $row['status'] == 'answered' ? '#33801999' : '#338019'; ?>;">
                        <div class="accordion-content">
                            <span class="email"><?= htmlspecialchars($row['email']) ?></span>
                            <span class="submission-date"><?= (new DateTime($row['submission_date']))->format('F j, Y, g:i a') ?></span>
                        </div>
                        <span class="arrow"><i class="fas fa-chevron-down"></i></span>
                    </button>
                    <div class="panel">
                        <p><strong>Role:</strong> <?= htmlspecialchars($row['role']) ?></p>
                        <p><strong>First Name:</strong> <?= htmlspecialchars($row['first_name']) ?></p>
                        <p><strong>Last Name:</strong> <?= htmlspecialchars($row['last_name']) ?></p>
                        <p><strong>Address:</strong> <?= htmlspecialchars($row['address']) ?></p>
                        <p><strong>Message:</strong></p>
                        <p><?= nl2br(htmlspecialchars($row['message'])) ?></p>
                        <!-- Answer Button -->
                        <button class="answer-btn" onclick="answerQuery('<?= htmlspecialchars($row['email']) ?>', '<?= htmlspecialchars($row['id']) ?>')">Answer</button>
                    </div>
                <?php endwhile; ?>
            <?php else: ?>
                <p>No contact queries found.</p>
            <?php endif; ?>
        </div>

        <div id="answerModal" class="modal" style="display:none;">
            <div class="modal-content">
                <span class="close" onclick="closeModal()">×</span>
                <h2>Provide Your Answer</h2>
                <form id="answerForm" method="post" action="send_email.php">
                    <textarea name="answer" placeholder="Type your answer here..." rows="4" required></textarea>
                    <input type="hidden" name="recipient_email" id="recipientEmail">
                    <input type="hidden" name="query_id" id="queryId">
                    <button type="submit" class="answer-submit-btn">Send Answer</button>
                </form>
                <!-- Back button -->
                <button class="close-btn" onclick="closeModal()">Back</button>
            </div>
        </div>

        <script>
            async function fetchCategoriesNav() {
                try {
                    // Replace '/get-categories' with your actual endpoint to fetch data
                    const response = await fetch('../includes/fetch_categories.php');
                    const data = await response.json();

                    // Get the container where the categories will be displayed
                    const container = document.getElementById('dropdown-container');

                    // Clear any existing content
                    container.innerHTML = '';

                    // Loop through the data and dynamically create HTML
                    data.forEach(category => {
                        const categoryHtml = `
                            <li><a href="multimedia.php#${category.slug}">${category.name}</a></li>
                        `;
                        container.innerHTML += categoryHtml;
                    });
                } catch (error) {
                    console.error('Error fetching categories:', error);
                }
            }

            async function init() {
                await fetchCategoriesNav();
            }

            init();

            function answerQuery(email, id) {
                document.getElementById('recipientEmail').value = email;
                document.getElementById('queryId').value = id; // Add this line to set the ID
                document.getElementById('answerModal').style.display = 'flex';
            }


            function closeModal() {
                document.getElementById('answerModal').style.display = 'none';
            }

            // Optional: Close modal when clicking outside the modal content
            window.onclick = function(event) {
                if (event.target == document.getElementById('answerModal')) {
                    closeModal();
                }
            };

            document.addEventListener('DOMContentLoaded', function() {
                var acc = document.getElementsByClassName('accordion');
                for (var i = 0; i < acc.length; i++) {
                    acc[i].addEventListener('click', function() {
                        this.classList.toggle('active');
                        var panel = this.nextElementSibling;
                        var arrow = this.querySelector('.arrow i');
                        if (panel.style.display === 'block') {
                            panel.style.display = 'none';
                        } else {
                            panel.style.display = 'block';
                        }
                        // Toggle the rotation of the arrow
                        arrow.classList.toggle('fa-chevron-up');
                        arrow.classList.toggle('fa-chevron-down');
                    });
                }
            });

            function logout() {
                // Redirect to the logout PHP script
                window.location.href = '../includes/logout.php'; // Ensure this path matches the location of logout.php
            }
        </script>
    </main>
</body>

</html>
