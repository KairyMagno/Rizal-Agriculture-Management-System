<?php
session_start();
require '../includes/db.php'; // Database connection
require '../includes/logo.php'; // Include the logo fetching function
require 'utils.php';

// Fetch the categories from the database
$query = "SELECT * FROM categories";  // Adjust this query if necessary
$result = mysqli_query($conn, $query);

// Check if the query was successful
if (!$result) {
    // If there's an error with the query, display an error message
    die("Query failed: " . mysqli_error($conn));
}


// Check if the user is logged in and if their role is 'staff' or 'admin'
if (!isset($_SESSION['role']) || ($_SESSION['role'] != 'staff' && $_SESSION['role'] != 'admin')) {
    header("Location: ../login/login.php");
    exit;
}

// Role-based access
if ($_SESSION['role'] == 'staff') {
    $hide_admin_elements = true;
} else {
    $hide_admin_elements = false;
}

// Fetch data for reports
$activity_logs = mysqli_query($conn, "SELECT * FROM activity_log ORDER BY timestamp DESC LIMIT 5");
$announcement_clicks = mysqli_query($conn, "SELECT title, click_count FROM announcement_clicks ORDER BY click_count DESC");
$articles_clicks = mysqli_query($conn, "SELECT title, click_count FROM articles WHERE click_count > 0 ORDER BY click_count DESC");
$multimedia_clicks = mysqli_query($conn, "SELECT title, click_count FROM multimedia WHERE click_count > 0 ORDER BY click_count DESC");


?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard</title>
    <link rel="stylesheet" href="css/styles.css">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link
        href="https://fonts.googleapis.com/css2?family=Pirata+One&family=Poppins:ital,wght@0,100;0,200;0,300;0,400;0,500;0,600;0,700;0,800;0,900&display=swap"
        rel="stylesheet">
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css" rel="stylesheet">
    <link rel="stylesheet" href="css/header.css">
    <link rel="stylesheet" href="css/news.css">
    <link rel="stylesheet" href="css/sidebar.css">
    <link rel="stylesheet" href="css/reports.css">
</head>

<body>
    <!-- Sidebar -->
    <div class="sidebar">
        <div class="header-title">
            <img src="<?= getLogoPath($conn) ?>" alt="AgriWeb Logo" class="header-logo">
            <div class="logo-text">
                <h1
                    style="color:black; margin: 0; font-size: 18px; letter-spacing: 1px; font-weight: 500; margin-top: 10px;">
                    RIZAL</h1>
                <h1
                    style="color:black; margin: 0; font-size: 18px; letter-spacing: 1px; font-weight: 500; margin-top: -5px;">
                    AGRI</h1>
                <h1
                    style="color:black; margin: 0; font-size: 18px; letter-spacing: 1px; font-weight: 500; margin-top: -5px">
                    CULTIVA</h1>
            </div>
        </div>
        <ul class="nav-links">
            <li><a href="announcement.php"><i class="fas fa-bullhorn"></i> Announcements</a></li>
            <li><a href="articles.php"><i class="fas fa-file-alt"></i> News & Articles</a></li>
            <li class="dropdown">
                <a href="multimedia.php" class="dropbtn"><i class="fas fa-camera"></i> Multimedia</a>
                <ul class="dropdown-content" id="dropdown-container">

                </ul>
            </li>
            <li><a href="about.php"><i class="fas fa-file"></i> About RAC</a></li>
            <li><a href="email.php"><i class="fas fa-envelope"></i> Email Inquiry</a></li>
            <li style="margin-bottom: 5px;"><a href="archive.php" ><i class="fas fa-archive"></i> Archive</a></li> <!-- Archive added here -->

            <?php if (!$hide_admin_elements): ?>
                <p style="color:gray; margin: 0;">Admin</p>
                <li><a href="dashboard.php"><i class="fas fa-tachometer-alt"></i> Dashboard</a></li>
                <li><a href="reports.php" class="active"><i class="fas fa-chart-line"></i> Reports</a></li>
                <li><a href="user.php"><i class="fas fa-users"></i> User Management</a></li>
                <li style="margin-bottom: 0;"><a href="settings.php"><i class="fas fa-cogs"></i> Settings</a></li>
            <?php endif; ?>
        </ul>
        <button class="logout-btn" onclick="logout()">
            <i class="fas fa-sign-out-alt logout-icon"></i> Logout
        </button>
    </div>
    <main>
        <div class="header">
            <h1 style="color:green; font-size:28px;">Reports</h1>
        </div>

         <!-- Announcement Clicks Table -->
         <div class="clicks-container">
            <h2 style="color:#338019; margin-left:30px; font-size: 1.5em;">Announcements Clicks</h2>
            <table>
                <thead>
                    <tr>
                        <th>Title</th>
                        <th>Click Count</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (mysqli_num_rows($announcement_clicks) > 0): ?>
                        <?php while ($row = mysqli_fetch_assoc($announcement_clicks)): ?>
                            <tr>
                                <td style="width: 85%;"><?= htmlspecialchars($row['title']); ?></td>
                                <td><?= htmlspecialchars($row['click_count']); ?></td>
                            </tr>
                        <?php endwhile; ?>
                    <?php else: ?>
                        <tr>
                            <td colspan="2">No announcement click data available.</td>
                        </tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>    

        <!-- Article Clicks Table -->
        <div class="clicks-container">
            <h2 style="color:#338019; margin-left:30px; font-size: 1.5em;">News & Articles Clicks</h2>
            <table>
                <thead>
                    <tr>
                        <th>Title</th>
                        <th>Click Count</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (mysqli_num_rows($articles_clicks) > 0): ?>
                        <?php while ($row = mysqli_fetch_assoc($articles_clicks)): ?>
                            <tr>
                                <td style="width: 85%;"><?= htmlspecialchars($row['title']); ?></td>
                                <td><?= htmlspecialchars($row['click_count']); ?></td>
                            </tr>
                        <?php endwhile; ?>
                    <?php else: ?>
                        <tr>
                            <td colspan="2">No article click data available.</td>
                        </tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>

        <!-- Multimedia Clicks Table -->
        <div class="clicks-container">
            <h2 style="color:#338019; margin-left:30px; font-size: 1.5em;">Multimedia Clicks</h2>
            <table>
                <thead>
                    <tr>
                        <th>Title</th>
                        <th>Click Count</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (mysqli_num_rows($multimedia_clicks) > 0): ?>
                        <?php while ($row = mysqli_fetch_assoc($multimedia_clicks)): ?>
                            <tr>
                                <td style="width: 85%;"><?= htmlspecialchars($row['title']); ?></td>
                                <td><?= htmlspecialchars($row['click_count']); ?></td>
                            </tr>
                        <?php endwhile; ?>
                    <?php else: ?>
                        <tr>
                            <td colspan="2">No multimedia click data available.</td>
                        </tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>


        <!-- Activity Log Table -->
        <div class="activity-log">
            <h2 style="color:#338019; margin-left:30px;">Activity Logs</h2>
            <table id="activity-log-table">
                <thead>
                    <tr>
                        <th>Username</th>
                        <th>Activity</th>
                        <th>Timestamp</th>
                    </tr>
                </thead>
                <tbody>
                    <?php while ($row = mysqli_fetch_assoc($activity_logs)): ?>
                        <tr>
                            <td><?= htmlspecialchars($row['username']); ?></td>
                            <td><?= htmlspecialchars($row['activity']); ?></td>
                            <td><?= htmlspecialchars($row['timestamp']); ?></td>
                        </tr>
                    <?php endwhile; ?>
                </tbody>
            </table>
            <button id="show-all-btn" onclick="showAllLogs()" style="margin: 10px 0; margin-left: 3%; padding: 5px 15px; background-color: #338019; color: white; border: none; border-radius: 5px; cursor: pointer;">Show All</button>
            <button id="hide-btn" onclick="hideLogs()" style="display: none; margin: 10px 0; margin-left: 3%; padding: 5px 15px; background-color: #d9534f; color: white; border: none; border-radius: 5px; cursor: pointer;">Hide</button>
        </div>

        <script>
            function showAllLogs() {
                const tableBody = document.querySelector('#activity-log-table tbody');
                document.getElementById('show-all-btn').style.display = 'none'; // Hide the "Show All" button
                document.getElementById('hide-btn').style.display = 'block'; // Show the "Hide" button

                fetch('get_all_activity_logs.php') // Replace with your appropriate path for fetching all logs
                    .then(response => response.text())
                    .then(data => {
                        tableBody.innerHTML = data;
                    })
                    .catch(error => {
                        console.error('Error fetching all activity logs:', error);
                    });
            }

            function hideLogs() {
                const tableBody = document.querySelector('#activity-log-table tbody');
                document.getElementById('show-all-btn').style.display = 'block'; // Show the "Show All" button
                document.getElementById('hide-btn').style.display = 'none'; // Hide the "Hide" button

                // Re-fetch and display the initial limited data (5 rows)
                fetch('get_activity_logs_limited.php') // Replace with your appropriate path for fetching limited logs
                    .then(response => response.text())
                    .then(data => {
                        tableBody.innerHTML = data;
                    })
                    .catch(error => {
                        console.error('Error fetching limited activity logs:', error);
                    });
            }
        </script>


        <!-- Category Report -->
        <div class="category-report">
            <h2 style="color:#338019; margin-left:30px; font-size: 1.5em;">Categories</h2>
            <table>
                <thead>
                    <tr>
                        <th>Category</th>
                    </tr>
                </thead>
                <tbody>
                    <?php
                    // Ensure $result contains category data
                    if (mysqli_num_rows($result) > 0):
                        while ($row = mysqli_fetch_assoc($result)):
                            // Access the correct column 'name'
                    ?>
                                <tr>
                                    <td><?= htmlspecialchars($row['name']); ?></td>
                                </tr>
                    <?php
                        endwhile;
                    else:
                    ?>
                        <tr>
                            <td colspan="1">No categories available.</td>
                        </tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>

        <form action="generate_pdf.php" method="post" style="margin-bottom: 20px;">
            <button type="submit" name="export_pdf" style="padding: 10px 20px; background-color: #338019; color: white; border: none; border-radius: 5px; cursor: pointer;">Export to PDF</button>
        </form>
    </main>

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
    // Logout function
    function logout() {
        // Redirect to the logout PHP script
        window.location.href = '../includes/logout.php'; // Ensure this path matches the location of logout.php
    }
</script>
</body>

</html>
                    
