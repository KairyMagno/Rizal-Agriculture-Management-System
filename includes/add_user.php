<?php
  // Include database configuration or connection code
  include('db.php'); // Ensure this file contains your connection logic

  if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    // Retrieve form data and sanitize it
    $username = htmlspecialchars(trim($_POST['username']));
    $email = htmlspecialchars(trim($_POST['email']));
    $password = htmlspecialchars(trim($_POST['password']));
    $role = htmlspecialchars(trim($_POST['role'])); // 'admin' or 'staff'

    // Validate input (e.g., check if fields are not empty)
    if (empty($username) || empty($email) || empty($password) || empty($role)) {
        die("All fields are required.");
    }

    // Hash the password before storing it in the database
    $hashedPassword = password_hash($password, PASSWORD_DEFAULT);

    // Prepare an SQL statement to insert the user into the database
    $stmt = $conn->prepare("INSERT INTO users (username, email, password, role) VALUES (?, ?, ?, ?)");
    $stmt->bind_param("ssss", $username, $email, $hashedPassword, $role);

    // Execute the statement and check for success
    if ($stmt->execute()) {
        header("Location: ../admin/user.php?success=1"); // Redirect back to the user management page with a success message
    } else {
        echo "Error: " . $stmt->error;
    }

    // Close the statement and the connection
    $stmt->close();
    $conn->close();
  }
?>
