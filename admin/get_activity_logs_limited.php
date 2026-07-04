<?php
// Include database connection
require '../includes/db.php';

// Fetch limited activity logs (e.g., 5 records)
$query = "SELECT * FROM activity_log ORDER BY timestamp DESC LIMIT 5";
$result = mysqli_query($conn, $query);

// Check if there are results
if (mysqli_num_rows($result) > 0) {
    while ($row = mysqli_fetch_assoc($result)) {
        echo '<tr>
                <td>' . htmlspecialchars($row['username']) . '</td>
                <td>' . htmlspecialchars($row['activity']) . '</td>
                <td>' . htmlspecialchars($row['timestamp']) . '</td>
              </tr>';
    }
} else {
    echo '<tr>
            <td colspan="3">No activity log data available.</td>
          </tr>';
}
?>
