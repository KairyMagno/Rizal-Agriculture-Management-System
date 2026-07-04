<?php
require '../includes/db.php'; // Ensure the database connection is included
require_once '../includes/db_helpers.php'; // Assuming this includes helper functions



if (isset($_GET['delete'])) {
    $id = (int) $_GET['delete']; // Sanitize and cast the ID to an integer

    // Fetch category details
    $categoryQuery = $conn->prepare("SELECT name, slug FROM categories WHERE id = ?");
    $categoryQuery->bind_param("i", $id);
    $categoryQuery->execute();
    $categoryResult = $categoryQuery->get_result();

    if ($categoryResult->num_rows > 0) {
        $category = $categoryResult->fetch_assoc();

        // Archive to deleted_post with name as the title
        $source_table = "categories"; // Use lowercase for consistency
        $name_as_title = $category['name']; // Use the 'name' field as the title
        $archiveQuery = $conn->prepare("
            INSERT INTO deleted_post (title, slug, source_table, deleted_at) 
            VALUES (?, ?, ?, NOW())
        ");
        $archiveQuery->bind_param("sss", $name_as_title, $category['slug'], $source_table);

        if ($archiveQuery->execute()) {
            // Delete the category from the categories table
            $deleteQuery = $conn->prepare("DELETE FROM categories WHERE id = ?");
            $deleteQuery->bind_param("i", $id);

            if ($deleteQuery->execute()) {
                // Redirect with a success message
                header("Location: settings.php?message=Category archived successfully.");
                exit;
            } else {
                // Handle deletion error
                echo "Error deleting category: " . $conn->error;
            }
        } else {
            // Handle archiving error
            echo "Error archiving category: " . $conn->error;
        }
    } else {
        // Handle case where category is not found
        echo "Category not found.";
    }

    // Close statement and connection
    $categoryQuery->close();
    $conn->close();
} else {
    // Handle invalid access
    echo "Invalid request. No category ID specified.";
}
?>
