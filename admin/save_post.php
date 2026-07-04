<?php
// save_post.php
function savePost($conn, $title, $content, $postType) {
    // Prepare the SQL query to insert the new post into the database
    $query = "INSERT INTO posts (title, content, post_type) VALUES (?, ?, ?)";
    
    if ($stmt = $conn->prepare($query)) {
        // Bind the parameters to prevent SQL injection
        $stmt->bind_param('sss', $title, $content, $postType);
        
        // Execute the statement to insert the post
        if ($stmt->execute()) {
            return true; // Post saved successfully
        } else {
            return false; // Failed to save post
            
        }
        // Close the statement
        $stmt->close();
        
    }
    return false; // Return false in case of an error
}
?>
