<?php
require 'db.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Define the upload directory
    $uploads_dir = '../uploads/carousel';

    // Ensure the directory exists
    if (!is_dir($uploads_dir)) {
        mkdir($uploads_dir, 0777, true);
    }

    // Define the image fields and corresponding alt text fields
    $image_files = ['image1', 'image2', 'image3'];
    $alt_texts = ['alt1', 'alt2', 'alt3'];

    foreach ($image_files as $index => $image_field) {
        if (!empty($_FILES[$image_field]['name'])) {
            $tmp_name = $_FILES[$image_field]['tmp_name'];
            $original_name = basename($_FILES[$image_field]['name']);
            $file_extension = strtolower(pathinfo($original_name, PATHINFO_EXTENSION));
            $new_file_name = $image_field . '.' . $file_extension;
            $path = $uploads_dir . '/' . $new_file_name;

            // Move the uploaded file to the target directory
            if (move_uploaded_file($tmp_name, $path)) {
                // Get the alt text
                $alt_text = $_POST[$alt_texts[$index]] ?? null;

                // Update the existing image record in the database
                $sql_update = "UPDATE carousel_images SET image_path = ?, alt_text = ? WHERE image_slot = ?";
                $stmt_update = $conn->prepare($sql_update);
                $stmt_update->bind_param("sss", $path, $alt_text, $image_field);

                // Check if the record was updated successfully
                if ($stmt_update->execute()) {
                    echo "Image for $image_field updated successfully.<br>";
                } else {
                    echo "Failed to update image for $image_field.<br>";
                }

                $stmt_update->close();
            } else {
                echo "Failed to upload file for $image_field.<br>";
            }
        }
    }

    // Redirect after processing
    header('Location: ../admin/settings.php');
    exit;
}
?>
