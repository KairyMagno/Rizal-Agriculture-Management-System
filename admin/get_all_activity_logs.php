<?php
session_start();
require '../includes/db.php';

// Check if the user is logged in and if their role is 'staff' or 'admin'
if (!isset($_SESSION['role']) || ($_SESSION['role'] != 'staff' && $_SESSION['role'] != 'admin')) {
    header("Location: ../login/login.php");
    exit;
}

// Fetch all activity logs from the database
$activity_logs = mysqli_query($conn, "SELECT * FROM activity_log ORDER BY timestamp DESC");

if (mysqli_num_rows($activity_logs) > 0) {
    while ($row = mysqli_fetch_assoc($activity_logs)) {
        echo '<tr>
                <td>' . htmlspecialchars($row['username']) . '</td>
                <td>' . htmlspecialchars($row['activity']) . '</td>
                <td>' . htmlspecialchars($row['timestamp']) . '</td>
              </tr>';
    }
} else {
    echo '<tr>
            <td colspan="3">No activity logs available.</td>
          </tr>';
}
?>
