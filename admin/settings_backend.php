<?php

require '../includes/db.php'; // Ensure the database connection is included
// Fetch the terms data
$sql_terms = "SELECT id, title, effective_date, content FROM terms LIMIT 1"; // Add `id` for easy updates
$result = $conn->query($sql_terms);
$terms = $result->fetch_assoc();

// Format the date to "Month Day, Year" if it exists
$formatted_date = $terms ? date("F j, Y", strtotime($terms['effective_date'])) : '';

// Fetch the policies data
$sql_policies = "SELECT id, title, effective_date, content FROM policies LIMIT 1"; // Add `id` for easy updates
$result_policies = $conn->query($sql_policies);
$policies = $result_policies->fetch_assoc();

// Format the date to "Month Day, Year" if it exists
$formatted_policies_date = $policies ? date("F j, Y", strtotime($policies['effective_date'])) : '';

// Check connection
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Handle backup creation
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['create_backup'])) {
    $stmt = $conn->prepare("SELECT logo_path FROM site_settings WHERE id = 1");
    $stmt->execute();
    $stmt->bind_result($logoPath);

    if ($stmt->fetch()) {
        $stmt->close();

        $backupDir = "../backups/";
        $imageBackupDir = $backupDir . "images/";
        if (!is_dir($backupDir)) {
            mkdir($backupDir, 0777, true);
        }
        if (!is_dir($imageBackupDir)) {
            mkdir($imageBackupDir, 0777, true);
        }

        $backupFile = $backupDir . "backup_" . date("Ymd_His") . ".json";

        // Fetch data with image URLs for backup
        $tables = [
            "activity_log",
            "announcement_clicks",
            "carousel_images",
            "categories",
            "clicks",
            "module_usage",
            "performance_metrics",
            "announcement",
            "articles",
            "news",
            "multimedia"
        ];

        $backupData = ["logo" => $logoPath];
        foreach ($tables as $table) {
            $backupData[$table] = fetchTableData($conn, $table, $imageBackupDir);
        }

        if (file_put_contents($backupFile, json_encode($backupData))) {
            $stmt = $conn->prepare("INSERT INTO backups (file_path, backup_date) VALUES (?, NOW())");
            $stmt->bind_param("s", $backupFile);
            $stmt->execute();
        } else {
        }
    } else {
        $stmt->close();
    }
}

// Fetch table data and copy images if present
function fetchTableData($conn, $table, $imageBackupDir) {
    $result = $conn->query("SELECT * FROM $table");
    $data = $result->fetch_all(MYSQLI_ASSOC);

    if (in_array($table, ["news", "announcement", "articles", "multimedia", "carousel_images"])) {
        foreach ($data as &$record) {
            if (isset($record['image_url']) && file_exists($record['image_url'])) {
                $newImagePath = $imageBackupDir . basename($record['image_url']);
                copy($record['image_url'], $newImagePath);
                $record['image_url'] = $newImagePath;
            }
        }
    }
    return $data;
}

// Restore data with images
function restoreDataWithImages($conn, $table, $records) {
    $conn->query("TRUNCATE TABLE $table");
    foreach ($records as $row) {
        $columns = implode(", ", array_keys($row));
        $placeholders = implode(", ", array_fill(0, count($row), "?"));
        $values = array_values($row);

        $stmt = $conn->prepare("INSERT INTO $table ($columns) VALUES ($placeholders)");
        $stmt->bind_param(str_repeat("s", count($values)), ...$values);
        $stmt->execute();
    }
}

// Handle backup restoration
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['restore_backup'])) {
    $backupId = intval($_POST['backup_id']);
    $stmt = $conn->prepare("SELECT file_path FROM backups WHERE id = ?");
    $stmt->bind_param("i", $backupId);
    $stmt->execute();
    $stmt->bind_result($backupPath);

    if ($stmt->fetch()) {
        $stmt->close();

        $data = json_decode(file_get_contents($backupPath), true);

        if ($data) {
            // Restore the logo
            $stmt = $conn->prepare("UPDATE site_settings SET logo_path = ? WHERE id = 1");
            $stmt->bind_param("s", $data['logo']);
            $stmt->execute();

            // Restore all tables
            foreach ($data as $table => $records) {
                if ($table !== "logo") {
                    restoreDataWithImages($conn, $table, $records);
                }
            }

        } else {
        }
    } else {
        $stmt->close();
    }
}

// Handle deletion of backups
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['delete_backup'])) {
    $backupId = intval($_POST['backup_id']);
    $stmt = $conn->prepare("DELETE FROM backups WHERE id = ?");
    $stmt->bind_param("i", $backupId);
    if ($stmt->execute()) {
    } else {
    }
}

//------------------------------------------------dito nag tatapos ang backup nakakabaliwwww



// Check if the user is logged in and if their role is 'staff' or 'admin'
if (!isset($_SESSION['role']) || ($_SESSION['role'] != 'staff' && $_SESSION['role'] != 'admin')) {
    // If not, redirect to the login page or a "no access" page
    header("Location: ../login/login.php");
    exit;
}

// If the user is a staff member or admin, allow access
if ($_SESSION['role'] == 'staff') {
    // Staff has limited access
    $hide_admin_elements = true;
} else {
    // Admin has full access
    $hide_admin_elements = false;
}

// Function to update the content of a file and return a custom success message
function updateFileContent($filePath, $newContent, $type)
{
    if (file_put_contents($filePath, $newContent)) {
        if ($type == 'terms') {
            return "Terms and Services updated successfully.";
        } elseif ($type == 'privacy') {
            return "Data Privacy Act updated successfully.";
        }
    } else {
        return "Failed to update content.";
    }
}

// Function to log activity to the database
function logActivity($username, $activity)
{
    global $conn;
    $stmt = $conn->prepare("INSERT INTO activity_log (username, activity, timestamp) VALUES (?, ?, NOW())");
    $stmt->bind_param("ss", $username, $activity);
    $stmt->execute();
    $stmt->close();
}

// Check if form submissions exist
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['update_terms'])) {
        $newTermsContent = $_POST['terms_content'];
        $message = updateFileContent('../data/terms.txt', $newTermsContent, 'terms');
        $messageType = 'terms';

        // Log the activity
        logActivity($_SESSION['username'], 'Updated Terms of Service');
    }
    if (isset($_POST['update_privacy'])) {
        $newPrivacyContent = $_POST['privacy_content'];
        $message = updateFileContent('../data/privacy.txt', $newPrivacyContent, 'privacy');
        $messageType = 'privacy';

        // Log the activity
        logActivity($_SESSION['username'], 'Updated Data Privacy Act');
    }
}

// Handle form submission for adding a new category
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['add_category'])) {
    $categoryName = trim($_POST['category_name']);
    $categorySlug = trim($_POST['category_slug']);

    // Validate category name and slug
    if (!empty($categoryName) && !empty($categorySlug)) {
        // Prepare and execute the SQL query to insert a new category
        $stmt = $conn->prepare("INSERT INTO categories (name, slug) VALUES (?, ?)");
        $stmt->bind_param("ss", $categoryName, $categorySlug);

        if ($stmt->execute()) {
            $successMessage = "Category added successfully.";
            // Log the activity
            logActivity($_SESSION['username'], "Added category: $categoryName");
        } else {
            $errorMessage = "Failed to add category. Please try again.";
        }
        $stmt->close();
    } else {
        $errorMessage = "Category name and slug cannot be empty.";
    }
}

// Handle deletion of a category
if (isset($_GET['delete'])) {
    $categoryId = intval($_GET['delete']);

    $stmt = $conn->prepare("DELETE FROM categories WHERE id = ?");
    $stmt->bind_param("i", $categoryId);

    if ($stmt->execute()) {
        $successMessage = "Category deleted successfully.";
        // Log the activity
        logActivity($_SESSION['username'], "Deleted category with ID: $categoryId");
    } else {
        $errorMessage = "Failed to delete category.";
    }
    $stmt->close();
}

// Check if there is an 'edit' parameter in the URL and fetch the data for that category
$editCategory = null;
if (isset($_GET['edit'])) {
    $categoryId = intval($_GET['edit']);
    $stmt = $conn->prepare("SELECT * FROM categories WHERE id = ?");
    $stmt->bind_param("i", $categoryId);
    $stmt->execute();
    $result = $stmt->get_result();
    if ($result->num_rows > 0) {
        $editCategory = $result->fetch_assoc(); // Get the category data
    }
    $stmt->close();
}

// Handle updating a category (additional form and logic needed for this)
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['update_category'])) {
    $categoryId = intval($_POST['category_id']);
    $categoryName = trim($_POST['category_name']);
    $categorySlug = trim($_POST['category_slug']);

    if (!empty($categoryName) && !empty($categorySlug)) {
        $stmt = $conn->prepare("UPDATE categories SET name = ?, slug = ? WHERE id = ?");
        $stmt->bind_param("ssi", $categoryName, $categorySlug, $categoryId);

        if ($stmt->execute()) {
            $successMessage = "Category updated successfully.";
            // Log the activity
            logActivity($_SESSION['username'], "Updated category with ID: $categoryId");
        } else {
            $errorMessage = "Failed to update category.";
        }
        $stmt->close();
    } else {
        $errorMessage = "Category name and slug cannot be empty.";
    }
}

// Handle logo upload
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_FILES['logo'])) {
    $targetDir = "../uploads/";
    $targetFile = $targetDir . basename($_FILES["logo"]["name"]);
    $uploadOk = 1;
    $imageFileType = strtolower(pathinfo($targetFile, PATHINFO_EXTENSION));

    // Validate the file
    $check = getimagesize($_FILES["logo"]["tmp_name"]);
    if ($check === false) {
        $uploadOk = 0;
    }
    if ($_FILES["logo"]["size"] > 2000000) {
        $uploadOk = 0;
    }
    if (!in_array($imageFileType, ['jpg', 'jpeg', 'png', 'gif'])) {
        $uploadOk = 0;
    }

    // Upload file and update database
    if ($uploadOk && move_uploaded_file($_FILES["logo"]["tmp_name"], $targetFile)) {
        $stmt = $conn->prepare("UPDATE site_settings SET logo_path = ? WHERE id = 1");
        $stmt->bind_param("s", $targetFile);
        if ($stmt->execute()) {
        } else {
        }
    } else {
    }
}

// Query to fetch all FAQs from the database, ordered by creation date in ascending order
$query = "SELECT faq_id, question, answer, created_at FROM faqs ORDER BY created_at ASC";
$result = mysqli_query($conn, $query);

// Check if the query was successful
if ($result) {
    // Check if there are any rows returned
    if (mysqli_num_rows($result) > 0) {
        // Store the results in an array to be used in the HTML
        $faqs = [];
        while ($row = mysqli_fetch_assoc($result)) {
            $faqs[] = $row;
        }
    } else {
        $faqs = null;
    }
} else {
    $faqs = null;
    $error_message = 'Error fetching data: ' . mysqli_error($conn);
}

// Query to fetch the media links
$query = "SELECT facebook, twitter, instagram FROM media_links";
$result = mysqli_query($conn, $query);

// Check if there are any results
if (mysqli_num_rows($result) > 0) {
    // Fetch the row as an associative array
    $row = mysqli_fetch_assoc($result);
} else {
    $row = null;
}

// Fetch all categories to display in the table
$result = $conn->query("SELECT * FROM categories");


?>