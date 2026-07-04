<?php
require 'db.php'; // Database connection
// fetch_all_posts.php
function fetchAllPosts($conn, $postType) {
    // Prepare the SQL query to fetch the posts based on type
    $query = "SELECT id, title, content, post_type, created_at FROM posts WHERE post_type = ? ORDER BY created_at DESC";
    
    if ($stmt = $conn->prepare($query)) {
        // Bind the parameter for the post type
        $stmt->bind_param('s', $postType);
        
        // Execute the query
        if (!$stmt->execute()) {
            echo "Error executing query: " . $stmt->error;
            return null; // Query execution failed
        }
        
        // Define the variables before binding them
        $id = $title = $content = $post_type = $created_at = '';
        
        // Bind the result variables
        $stmt->bind_result($id, $title, $content, $post_type, $created_at);
        
        // Fetch and store all posts in an array
        $posts = [];
        while ($stmt->fetch()) {
            $posts[] = [
                'id' => $id,
                'title' => $title,
                'content' => $content,
                'post_type' => $post_type,
                'created_at' => $created_at
            ];
        }

        // Close the statement
        $stmt->close();
        
        // Return all fetched posts
        return $posts;
    } else {
        echo "Error preparing query: " . $conn->error;
        return null; // Query preparation failed
    }
}
?>
