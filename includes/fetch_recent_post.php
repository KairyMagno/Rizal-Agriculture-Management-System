<?php
require 'db.php'; // Database connection
// fetch_recent_post.php
function fetchRecentPost($conn, $postType) {
    // Prepare the SQL query to fetch the most recent post of the given type
    $query = "SELECT * FROM posts WHERE post_type = ? ORDER BY created_at DESC LIMIT 1";
    
    if ($stmt = $conn->prepare($query)) {
        // Bind the parameter for the post type
        $stmt->bind_param('s', $postType);
        
        // Execute the query
        $stmt->execute();
        
        // Initialize the variables before binding
        $id = $title = $content = $post_type = $created_at = '';
        
        // Bind the result variables
        $stmt->bind_result($id, $title, $content, $post_type, $created_at);
        
        // Fetch the result
        if ($stmt->fetch()) {
            // Backup the fetched post into the backup table
            backupPost($conn, $id, $title, $content, $post_type, $created_at);
            
            // Return the post data
            return [
                'id' => $id,
                'title' => $title,
                'content' => $content,
                'post_type' => $post_type,
                'created_at' => $created_at
            ];
        } else {
            return null; // No post found
        }
        
        // Close the statement
        $stmt->close();
    }
    return null; // Return null if there was an issue with the query
}

function backupPost($conn, $id, $title, $content, $post_type, $created_at) {
    // Prepare the SQL query to insert the post into the backup table
    $backup_query = "INSERT INTO posts_backup (id, title, content, post_type, created_at) 
                     VALUES (?, ?, ?, ?, ?)";
    
    if ($backup_stmt = $conn->prepare($backup_query)) {
        // Bind the parameters
        $backup_stmt->bind_param('issss', $id, $title, $content, $post_type, $created_at);
        
        // Execute the insert query
        $backup_stmt->execute();
        
        // Close the backup statement
        $backup_stmt->close();
    }
}
?>
