<?php
require '../includes/db.php'; // Include database connection

// Function to prepare and bind SQL statements
function prepareAndExecute($conn, $sql, $types, $params) {
    $stmt = $conn->prepare($sql);
    if ($stmt === false) {
        die('MySQL prepare error: ' . $conn->error);
    }
    $stmt->bind_param($types, ...$params);
    $execute_result = $stmt->execute();
    if (!$execute_result) {
        die('Execute error: ' . $stmt->error);
    }
    return $stmt;
}

// Check if 'id' is set in the GET request
if (isset($_GET['id'])) {
    $deleted_post_id = intval($_GET['id']);

    // Fetch ALL needed fields including slug and original_id
    $sql = "SELECT * FROM deleted_post WHERE id = ?";
    $stmt = prepareAndExecute($conn, $sql, "i", [$deleted_post_id]);
    $result = $stmt->get_result();

    if ($result->num_rows > 0) {
        $post = $result->fetch_assoc();

        // Fetch values
        $title = $post['title'];
        $content = $post['content'];
        $imageUrl = $post['image_url'];
        $category = $post['category'];
        $source_table = $post['source_table'];
        $date = $post['date'];
        $link = $post['link'];
        $author = $post['author'];
        $slug = $post['slug'] ?? null;
        $original_id = $post['original_id'] ?? null;

        // Based on the source table, restore the post
        switch ($source_table) {
            case 'categories':
                // Check if category name already exists
                $check_sql = "SELECT id FROM categories WHERE name = ?";
                $check_stmt = prepareAndExecute($conn, $check_sql, "s", [$title]);
                $check_result = $check_stmt->get_result();
                
                if ($check_result->num_rows > 0) {
                    // Category exists, update it instead
                    $restore_sql = "UPDATE categories SET slug = ? WHERE name = ?";
                    $params = [$slug, $title];
                    $types = "ss";
                } else {
                    // Insert new category without specifying ID (let auto-increment work)
                    $restore_sql = "INSERT INTO categories (name, slug) VALUES (?, ?)";
                    $params = [$title, $slug];
                    $types = "ss";
                }
                break;

            case 'multimedia':
                // Remove ID to avoid duplicate primary key
                $restore_sql = "INSERT INTO multimedia (title, content, image_url, category, date, link) 
                                VALUES (?, ?, ?, ?, ?, ?)";
                $params = [$title, $content, $imageUrl, $category, $date, $link];
                $types = "ssssss";
                break;

            case 'news':
                // Remove ID to avoid duplicate primary key
                $restore_sql = "INSERT INTO news (title, content, image_url, date) 
                                VALUES (?, ?, ?, ?)";
                $params = [$title, $content, $imageUrl, $date];
                $types = "ssss";
                break;

            case 'articles':
                // Remove ID to avoid duplicate primary key
                $restore_sql = "INSERT INTO articles (title, content, image_url, date, author) 
                                VALUES (?, ?, ?, ?, ?)";
                $params = [$title, $content, $imageUrl, $date, $author];
                $types = "sssss";
                break;

            case 'announcement':
                // Remove ID to avoid duplicate primary key
                $restore_sql = "INSERT INTO announcement (title, content, image_url, date) 
                                VALUES (?, ?, ?, ?)";
                $params = [$title, $content, $imageUrl, $date];
                $types = "ssss";
                break;

            case 'faqs':
                // Remove faq_id to avoid duplicate primary key
                $restore_sql = "INSERT INTO faqs (question, answer, created_at) 
                                VALUES (?, ?, ?)";
                $params = [$title, $content, $date];
                $types = "sss";
                break;

            case 'users':
                // Check if username already exists
                $check_sql = "SELECT id FROM users WHERE username = ?";
                $check_stmt = prepareAndExecute($conn, $check_sql, "s", [$post['username']]);
                $check_result = $check_stmt->get_result();
                
                if ($check_result->num_rows > 0) {
                    // User exists, skip restoration or update
                    echo "User '{$post['username']}' already exists. Skipping restoration.";
                    // Delete from deleted_post and redirect
                    $delete_sql = "DELETE FROM deleted_post WHERE id = ?";
                    prepareAndExecute($conn, $delete_sql, "i", [$deleted_post_id]);
                    header("Location: archive.php?status=user_exists");
                    exit();
                }
                
                $restore_sql = "INSERT INTO users (username, email, role, deleted_at, firstname, lastname, password) 
                                VALUES (?, ?, ?, ?, ?, ?, ?)";
                $params = [
                    $post['username'], $post['email'], $post['role'], $post['deleted_at'],
                    $post['firstname'], $post['lastname'], $post['password']
                ];
                $types = "sssssss";
                break;

            default:
                echo "Unknown post type: " . htmlspecialchars($source_table);
                exit();
        }

        // Execute the restoration process (only if not in special cases)
        if ($source_table !== 'users' || !isset($check_result) || $check_result->num_rows === 0) {
            if ($source_table === 'categories' && isset($check_result) && $check_result->num_rows > 0) {
                // Handle category update case
                $restore_stmt = prepareAndExecute($conn, $restore_sql, $types, $params);
            } else {
                $restore_stmt = prepareAndExecute($conn, $restore_sql, $types, $params);
            }
        }

        // After restoring, delete the record from deleted_post
        $delete_sql = "DELETE FROM deleted_post WHERE id = ?";
        prepareAndExecute($conn, $delete_sql, "i", [$deleted_post_id]);

        // Redirect with success message
        header("Location: archive.php?status=success");
        exit();

    } else {
        echo "No such record found in the archive.";
    }

    $stmt->close();
} else {
    echo "Invalid request. ID is missing.";
}
?>