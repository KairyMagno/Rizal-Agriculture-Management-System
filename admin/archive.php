<?php
session_start();
require '../includes/db.php'; // Make sure you have the db connection here
require '../includes/logo.php'; // Include the logo fetching function
require_once('../includes/db_helpers.php');

if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

if (isset($_POST['id'])) {
    $post_id = $_POST['id'];

    // Fetch the post from the posts table
    $sql = "SELECT * FROM post WHERE id = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("i", $post_id);
    $stmt->execute();
    $result = $stmt->get_result();
    $post = $result->fetch_assoc();

    if ($post) {
        // Insert the post into the deleted_posts table
        $insert_sql = "INSERT INTO deleted_post (original_id, title, content, image_url, source_table) 
                       VALUES (?, ?, ?, ?, ?)";
        $insert_stmt = $conn->prepare($insert_sql);
        $source_table = 'posts'; // The source table is 'posts'
        $insert_stmt->bind_param("issss", $post['id'], $post['title'], $post['content'], $post['image_url'], $source_table);

        if ($insert_stmt->execute()) {
            // Delete the post from the original table
            $delete_sql = "DELETE FROM posts WHERE id = ?";
            $delete_stmt = $conn->prepare($delete_sql);
            $delete_stmt->bind_param("i", $post_id);
            $delete_stmt->execute();
        } else {
        }
    } else {
    }
} else {
}

// Restore the post
if (isset($_POST['id'])) {
    $deleted_post_id = $_POST['id']; // Get the deleted post ID dynamically

    // Fetch the post from the deleted_posts table
    $sql = "SELECT * FROM deleted_post WHERE id = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("i", $deleted_post_id);
    $stmt->execute();
    $result = $stmt->get_result();
    $post = $result->fetch_assoc();

    if ($post) {
        // Restore to the original table
        $restore_sql = "INSERT INTO posts (id, title, content, image_url) VALUES (?, ?, ?, ?)";
        $restore_stmt = $conn->prepare($restore_sql);
        $restore_stmt->bind_param("isss", $post['original_id'], $post['title'], $post['content'], $post['image_url']);

        if ($restore_stmt->execute()) {
            // Remove from deleted_posts
            $delete_sql = "DELETE FROM deleted_post WHERE id = ?";
            $delete_stmt = $conn->prepare($delete_sql);
            $delete_stmt->bind_param("i", $deleted_post_id);
            $delete_stmt->execute();
        } else {
        }
    } else {
    }
} else {
}

// Check if the user is logged in and if their role is 'staff' or 'admin'
if (!isset($_SESSION['role']) || ($_SESSION['role'] != 'staff' && $_SESSION['role'] != 'admin')) {
    // If not, redirect to the login page or a "no access" page
    header("Location: ../login/login.php");
    exit;
}

// If the user is a staff member or admin, allow access
if ($_SESSION['role'] == 'staff') {
    // Staff has limited access
    // You can disable or hide certain elements for staff
    $hide_admin_elements = true;
} else {
    // Admin has full access
    $hide_admin_elements = false;
}
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
    <link href="https://fonts.googleapis.com/css2?family=Pirata+One&family=Poppins:ital,wght@0,100;0,200;0,300;0,400;0,500;0,600;0,700;0,800;0,900;1,100;1,200;1,300;1,400;1,500;1,600;1,700;1,800;1,900&display=swap" rel="stylesheet">
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css" rel="stylesheet">
    <link rel="stylesheet" href="css/header.css">
    <link rel="stylesheet" href="css/announcement.css">
    <link rel="stylesheet" href="css/sidebar.css">
    <link rel="stylesheet" href="css/announcement_modal.css">
    <link rel="stylesheet" href="css/archive.css">

</head>

<body>
    <!-- Sidebar -->
    <div class="sidebar">
        <div class="header-title">
            <img src="<?= getLogoPath($conn) ?>" alt="AgriWeb Logo" class="header-logo">
            <div class="logo-text">
                <h1 style="color:black;  margin: 0; font-size: 18px; letter-spacing: 1px; font-weight: 500; margin-top: 10px;">RIZAL</h1>
                <h1 style="color:black;  margin: 0; font-size: 18px; letter-spacing: 1px; font-weight: 500; margin-top: -5px;">AGRI</h1>
                <h1 style="color:black;  margin: 0; font-size: 18px; letter-spacing: 1px; font-weight: 500; margin-top: -5px">CULTIVA</h1>
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
            <li style="margin-bottom: 5px;"><a href="archive.php" class="active" ><i class="fas fa-archive"></i> Archive</a></li> <!-- Archive added here -->

            <?php if (!$hide_admin_elements): ?>
                <p style="color:gray; margin: 0;">Admin</p>
                <li><a href="dashboard.php"><i class="fas fa-tachometer-alt"></i> Dashboard</a></li> <!-- Added icon -->
                <li><a href="reports.php"><i class="fas fa-chart-line"></i> Reports</a></li> <!-- Added icon -->
                <li><a href="user.php"><i class="fas fa-users"></i> User Management</a></li> <!-- Added icon -->
                <li style="margin-bottom: 0;"><a href="settings.php"><i class="fas fa-cogs"></i> Settings</a></li> <!-- Added icon -->
            <?php endif; ?>
        </ul>
        <button class="logout-btn" style="text-align:center;" onclick="logout()">
            <i class="fas fa-sign-out-alt logout-icon" style="margin-left:-20px;"></i> Logout
        </button>
    </div>
    <main>
    <div class="header">
            <h1 style="color:green; font-size:28px;">Archive</h1>
        </div>

        <?php
        $sql = "SELECT * FROM deleted_post ORDER BY deleted_at DESC";
        $result = $conn->query($sql);

        echo "<table class='table'>";
        echo "<thead><tr><th>ID</th><th>Original ID</th><th>Title</th><th>Image URL</th><th>Deleted At</th><th>Source Table</th><th>Action</th></tr></thead>";
        echo "<tbody>";

        if ($result->num_rows > 0) {
            while ($row = $result->fetch_assoc()) {
                echo "<tr>";
                echo "<td>" . $row['id'] . "</td>";
                echo "<td>" . $row['original_id'] . "</td>";
                echo "<td>" . $row['title'] . "</td>";
        
                // Check if image_url is null and handle it
                $image_url = !empty($row['image_url']) ? htmlspecialchars($row['image_url']) : 'images/RLogo.png';
                echo "<td><img src='" . $image_url . "' alt='Post Image' width='100' height='100'></td>";
        
                echo "<td>" . $row['deleted_at'] . "</td>";
                echo "<td>" . $row['source_table'] . "</td>";
        
                // Add Restore Button
                echo "<td><a href='restore_post.php?id=" . $row['id'] . "' class='btn btn-success'>Unarchive</a></td>";
                echo "</tr>";
            }
        } else {
            echo "<tr><td colspan='7'>No archived posts found.</td></tr>";
        }
        
        echo "</tbody></table>";
        
        ?>


    </main>
</body>
</html>