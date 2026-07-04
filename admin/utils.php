<?php
require '../includes/db.php'; // Adjust the path to your database connection file

function recordClick($item, $category, $conn) {
    // Sanitize the inputs to avoid SQL injection
    $item = mysqli_real_escape_string($conn, $item);
    $category = mysqli_real_escape_string($conn, $category);
    
    // Check if the combination of item and category already exists in the table
    $query = "SELECT clicks FROM clicks WHERE item = '$item' AND category = '$category'";
    $result = mysqli_query($conn, $query);

    if (mysqli_num_rows($result) > 0) {
        // If the item and category combination exists, get the current click count
        $row = mysqli_fetch_assoc($result);
        $currentClicks = $row['clicks'];
        
        // Increment the click count by 1
        $newClicks = $currentClicks + 1;
        
        // Update the click count
        $updateQuery = "UPDATE clicks SET clicks = $newClicks WHERE item = '$item' AND category = '$category'";
        mysqli_query($conn, $updateQuery);
    } else {
        // If the combination of item and category does not exist, insert a new entry with an initial count of 1
        $insertQuery = "INSERT INTO clicks (item, category, clicks) VALUES ('$item', '$category', 1)";
        mysqli_query($conn, $insertQuery);
    }
}
?>
