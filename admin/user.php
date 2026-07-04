<?php
session_start();

require '../includes/db.php';
require '../includes/logo.php';
require_once '../includes/archiveUserRecord.php'; // Assuming this includes the database connection
// Log activity function
function logActivity($username, $activity) {
    global $conn;
    $timestamp = date('Y-m-d H:i:s'); // Current timestamp
    $logQuery = "INSERT INTO activity_log (username, activity, timestamp) VALUES (?, ?, ?)";

    // Prepare the statement
    $stmt = $conn->prepare($logQuery);
    if ($stmt === false) {
        die('Error preparing log activity statement: ' . $conn->error);
    }

    // Bind the parameters
    $stmt->bind_param("sss", $username, $activity, $timestamp);

    // Execute the statement
    if (!$stmt->execute()) {
        error_log("Failed to log activity: " . $stmt->error);
    }
}

if (!isset($_SESSION['role']) || ($_SESSION['role'] != 'staff' && $_SESSION['role'] != 'admin')) {
    header("Location: ../login/login.php");
    exit;
}

if ($_SESSION['role'] == 'staff') {
    $hide_admin_elements = true;
} else {
    $hide_admin_elements = false;
}

$query = "SELECT lastname, firstname, username, email, role, status FROM users";
$result = $conn->query($query);

if ($result && $result->num_rows > 0) {
    $users = $result->fetch_all(MYSQLI_ASSOC);
} else {
    $users = [];
}

// Handle the form submission to add a user
if (isset($_POST['addUser'])) {
    $lastname = trim($_POST['lastname']);
    $firstname = trim($_POST['firstname']);
    $username = trim($_POST['username']);
    $email = trim($_POST['email']);
    $password = trim($_POST['password']); // Capture raw password input
    $hashedPassword = password_hash($password, PASSWORD_DEFAULT);
    $role = trim($_POST['role']);
    $status = trim($_POST['status']);

    // Validate password strength in PHP
    $regexUpperCase = '/[A-Z]/';
    $regexNumber = '/[0-9]/';
    $regexSpecial = '/[!@#$%^&*(),.?":{}|<>]/';
    $minLength = 8;

    if (strlen($password) < $minLength || !preg_match($regexUpperCase, $password) || !preg_match($regexNumber, $password) || !preg_match($regexSpecial, $password)) {
        echo "<script>alert('Password is weak. It must be at least 8 characters long, include one uppercase letter, one number, and one special character.');</script>";
    } else {
        // Check for duplicate username and email
        $checkQuery = "SELECT username, email FROM users WHERE username = ? OR email = ?";
        $stmt = $conn->prepare($checkQuery);
        $stmt->bind_param("ss", $username, $email);
        $stmt->execute();
        $result = $stmt->get_result();

        if ($result->num_rows > 0) {
            echo "<script>alert('Username or Email already exists. Please choose another.');</script>";
        } else {
            // Insert new user into the database
            $insertQuery = "INSERT INTO users (lastname, firstname, username, email, password, role, status) VALUES (?, ?, ?, ?, ?, ?, ?)";
            $stmt = $conn->prepare($insertQuery);
            $stmt->bind_param("sssssss", $lastname, $firstname, $username, $email, $hashedPassword, $role, $status);

            if ($stmt->execute()) {
                // Log the activity after successfully adding the user
                logActivity($_SESSION['username'], "Added new user: $username");

                echo "<script>alert('User added successfully!');</script>";
                header('Location: user.php');
            } else {
                echo "<script>alert('Failed to add user. Please try again.');</script>";
            }
        }
    }
}

// Save Changes (Edit User)
if (isset($_POST['saveChanges'])) {
    $username = trim($_POST['username']);
    $lastname = trim($_POST['lastname']);
    $firstname = trim($_POST['firstname']);
    $email = trim($_POST['email']);
    $role = trim($_POST['role']);

    // Prepare the update query
    $updateQuery = "UPDATE users SET lastname = ?, firstname = ?, email = ?, role = ? WHERE username = ?";
    $stmt = $conn->prepare($updateQuery);
    $stmt->bind_param("sssss", $lastname, $firstname, $email, $role, $username);

    // Execute the query
    if ($stmt->execute()) {
        // Log the activity after the user details are updated
        logActivity($_SESSION['username'], "Edited user: $username");

        echo "<script>alert('User updated successfully!');</script>";
        header('Location: user.php');
    } else {
        echo "<script>alert('Failed to update user. Please try again.');</script>";
    }
}
// Delete User
if (isset($_POST['deleteUser'])) {
    $username = trim($_POST['username']); // Assuming username is passed as a hidden field in the delete form

    // Start a transaction
    $conn->begin_transaction();

    try {
        // Retrieve the user's data before deletion
        $selectQuery = "SELECT * FROM users WHERE username = ?";
        $stmt = $conn->prepare($selectQuery);
        $stmt->bind_param("s", $username);
        $stmt->execute();
        $result = $stmt->get_result();
        $userData = $result->fetch_assoc();

        if ($userData) {
            // Generate a title dynamically for the deleted post
            $title = "Deleted User: " . htmlspecialchars($userData['firstname']) . " " . htmlspecialchars($userData['lastname']);

            // Set the content as the literal string "users data"
            $content = "users data";

            // Insert deleted user's information into the deleted_post table
            $insertQuery = "INSERT INTO deleted_post (original_id, username, email, role, firstname, lastname, password, title, content, source_table, deleted_at) 
                            VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, NOW())";
            $stmtInsert = $conn->prepare($insertQuery);
            $sourceTable = 'users'; // Define the source table for users
            $stmtInsert->bind_param(
                "isssssssss",
                $userData['id'],        // original_id
                $userData['username'],  // username
                $userData['email'],     // email
                $userData['role'],      // role
                htmlspecialchars($userData['firstname']), // firstname
                htmlspecialchars($userData['lastname']),  // lastname
                $userData['password'],  // hashed password
                $title,                 // title
                $content,               // content
                $sourceTable            // source_table
            );

            if ($stmtInsert->execute()) {
                // Delete the user from the users table
                $deleteQuery = "DELETE FROM users WHERE username = ?";
                $stmtDelete = $conn->prepare($deleteQuery);
                $stmtDelete->bind_param("s", $username);

                if ($stmtDelete->execute()) {
                    // Log the deletion in the activity_log table
                    $logQuery = "INSERT INTO activity_log (username, activity, timestamp) 
                                 VALUES (?, ?, NOW())";
                    $stmtLog = $conn->prepare($logQuery);
                    if ($stmtLog === false) {
                        throw new Exception('Error preparing log statement: ' . $conn->error);
                    }

                    // Get the current logged-in user who performed the deletion
                    $currentUser = $_SESSION['username']; // Assuming the logged-in user is stored in the session

                    // Activity message for the log
                    $activity = "Archived user: " . $username;

                    // Insert the activity log
                    $stmtLog->bind_param("ss", $currentUser, $activity);
                    if ($stmtLog->execute()) {
                        // Commit transaction
                        $conn->commit();

                        echo "<script>alert('User deleted and backed up successfully!');</script>";
                        header('Location: user.php'); // Redirect after deletion
                        exit(); // Prevent further script execution
                    } else {
                        throw new Exception('Failed to log activity.');
                    }
                } else {
                    throw new Exception('Failed to delete user.');
                }
            } else {
                throw new Exception('Failed to archive user.');
            }
        } else {
            throw new Exception('User not found.');
        }
    } catch (Exception $e) {
        // Rollback transaction on failure
        $conn->rollback();
        echo "<script>alert('" . $e->getMessage() . "');</script>";
    }
}


?>



<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard</title>
    <link rel="stylesheet" href="css/styles.css">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Pirata+One&family=Poppins:ital,wght@0,100;0,200;0,300;0,400;0,500;0,600;0,700;0,800;0,900;1,100;1,200;1,300;1,400;1,500;1,600;1,700;1,800;1,900&display=swap" rel="stylesheet">
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css" rel="stylesheet">
    <link rel="stylesheet" href="css/header.css">
    <link rel="stylesheet" href="css/user.css">
    <link rel="stylesheet" href="css/sidebar.css">
    <link rel="stylesheet" href="css/user_modal.css">
</head>

<body>
    <!-- Sidebar -->
    <div class="sidebar">
        <div class="header-title">
            <img src="<?= getLogoPath($conn) ?>" alt="AgriWeb Logo" class="header-logo">
            <div class="logo-text">
                <h1 style="color:black; margin: 0; font-size: 18px; letter-spacing: 1px; font-weight: 500; margin-top: 10px;">RIZAL</h1>
                <h1 style="color:black; margin: 0; font-size: 18px; letter-spacing: 1px; font-weight: 500; margin-top: -5px;">AGRI</h1>
                <h1 style="color:black; margin: 0; font-size: 18px; letter-spacing: 1px; font-weight: 500; margin-top: -5px">CULTIVA</h1>
            </div>
        </div>
        <ul class="nav-links">
            <li><a href="announcement.php"><i class="fas fa-bullhorn"></i> Announcements</a></li>
            <li><a href="articles.php"><i class="fas fa-file-alt"></i> News & Articles</a></li>
            <li class="dropdown">
                <a href="multimedia.php" class="dropbtn"><i class="fas fa-camera"></i> Multimedia</a>
                <ul class="dropdown-content" id="dropdown-container"></ul>
            </li>
            <li><a href="about.php"><i class="fas fa-file"></i> About RAC</a></li>
            <li><a href="email.php"><i class="fas fa-envelope"></i> Email Inquiry</a></li>
            <li style="margin-bottom: 5px;"><a href="archive.php"><i class="fas fa-archive"></i> Archive</a></li> <!-- Archive added here -->
            <?php if (!$hide_admin_elements): ?>
                <p style="color:gray; margin: 0;">Admin</p>
                <li><a href="dashboard.php"><i class="fas fa-tachometer-alt"></i> Dashboard</a></li> <!-- Added icon -->
                <li><a href="reports.php"><i class="fas fa-chart-line"></i> Reports</a></li> <!-- Added icon -->
                <li><a href="user.php" class="active"><i class="fas fa-users"></i> User Management</a></li> <!-- Added icon -->
                <li style="margin-bottom: 0;"><a href="settings.php"><i class="fas fa-cogs"></i> Settings</a></li> <!-- Added icon -->
            <?php endif; ?>
        </ul>
        <button class="logout-btn" style="text-align:center;" onclick="logout()">
            <i class="fas fa-sign-out-alt logout-icon" style="margin-left:-20px;"></i> Logout
        </button>
    </div>
    <main>
        <!-- Header with Add User button -->
        <div class="header">
            <h1 style="color:green; font-size:28px;">User Management</h1>
            <button class="add-btn">+ Add User</button>
        </div>

        <!-- Display User Information -->
        <section class="users-section">
            <h2>Users List</h2>
            <?php if (!empty($users)) : ?>
                <table class="users-table">
                    <thead>
                        <tr>
                            <th>Last Name</th>
                            <th>First Name</th>
                            <th>Username</th>
                            <th>Email</th>
                            <th>Role</th>
                            <th>Status</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($users as $user) : ?>
                            <tr>
                                <td><?= htmlspecialchars($user['lastname']) ?></td>
                                <td><?= htmlspecialchars($user['firstname']) ?></td>
                                <td><?= htmlspecialchars($user['username']) ?></td>
                                <td><?= htmlspecialchars($user['email']) ?></td>
                                <td><?= htmlspecialchars($user['role']) ?></td>
                                <td><?= htmlspecialchars($user['status']) ?></td>
                                <td>
                                    <div class="actionBtnContainer">
                                        <div class="editBtnContainer">
                                            <button class="edit-btn" onclick="openEditModal('<?= htmlspecialchars($user['lastname']) ?>', '<?= htmlspecialchars($user['firstname']) ?>', '<?= htmlspecialchars($user['username']) ?>', '<?= htmlspecialchars($user['email']) ?>', '<?= htmlspecialchars($user['role']) ?>')"><img src="images/Pencil.png" alt="">Edit</button>
                                        </div>
                                        <div class="deleteBtnContainer">
                                            <form method="post" action="">
                                                <input type="hidden" name="username" value="<?= htmlspecialchars($user['username']) ?>">
                                                <button class="delete-btn" type="submit" name="deleteUser" onclick="return confirm('Are you sure you want to delete this user?')">
                                                    Delete
                                                </button>
                                            </form>
                                        </div>
                                    </div>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            <?php else : ?>
                <p>No users found.</p>
            <?php endif; ?>
        </section>

        <!-- Modal Overlay -->
        <div class="modal-overlay" id="modalOverlay"></div>

        <!-- Modal -->
        <div class="modal" id="addUserModal">
            <h2>Add User</h2>
            <form method="post" action="">
                <div class="form-group">
                    <label for="lastname">Last Name:</label>
                    <input type="text" id="lastname" name="lastname" required>
                </div>
                <div class="form-group">
                    <label for="firstname">First Name:</label>
                    <input type="text" id="firstname" name="firstname" required>
                </div>
                <div class="form-group">
                    <label for="username">Username:</label>
                    <input type="text" id="username" name="username" required>
                </div>
                <div class="form-group">
                    <label for="email">Email:</label>
                    <input type="email" id="email" name="email" required>
                </div>
                <div class="form-group">
                    <label for="password">Password:</label>
                    <div style="display: flex; align-items: center; position: relative;">
                        <input type="password" id="password" name="password" required oninput="checkPasswordStrength()" style="flex-grow: 1; padding-right: 30px;">
                        <button type="button" id="togglePassword" style="position: absolute; right: 5px; top: 50%; transform: translateY(-50%); background: none; border: none; cursor: pointer; color: #333;">
                            <i class="fas fa-eye" id="eyeIcon"></i>
                        </button>
                    </div>
                    <small id="passwordStrength" style="display: none; color: red;">Password must be at least 8 characters long, contain one number, one special character, and one uppercase letter.</small>
                </div>
                <div class="form-group">
                    <label for="role">Role:</label>
                    <select id="role" name="role" required>
                        <option value="admin">Admin</option>
                        <option value="staff">Staff</option>
                        <!-- Add other roles as needed -->
                    </select>
                </div>
                <!-- Hidden status field -->
                <input type="hidden" name="status" value="inactive">
                <button type="submit" name="addUser" class="add-user-btn">Add User</button>
            </form>
            <button class="close-modal" onclick="closeModal()">×</button>
        </div>

        <!-- Modal Overlay -->
        <div class="modal-overlay" id="modalOverlay"></div>

        <!-- Edit User Modal -->
        <div id="editModal" class="modal">
            <div class="modal-content">
                <span class="close-btn" onclick="closeEditModal()">&times;</span>
                <h2>Edit User</h2>
                <form id="editUserForm" method="POST" class="editForm">
                    <label for="editLastname">Last Name:</label>
                    <input type="text" id="editLastname" name="lastname" required>

                    <label for="editFirstname">First Name:</label>
                    <input type="text" id="editFirstname" name="firstname" required>

                    <label for="editUsername">Username:</label>
                    <input type="text" id="editUsername" name="username" required>

                    <label for="editEmail">Email:</label>
                    <input type="email" id="editEmail" name="email" required>

                    <label for="editRole">Role:</label>
                    <select id="editRole" name="role" required>
                        <option value="admin">Admin</option>
                        <option value="staff">Staff</option>
                    </select>

                    <button type="submit" name="saveChanges" class="save">Save Changes</button>
                </form>
            </div>
        </div>

    </main>

    <script>
        function confirmDeletion(username) {
            const userConfirmed = confirm('Are you sure you want to delete this user?');
            if (userConfirmed) {
                // If confirmed, you can either submit the form or use a separate request to delete
                window.location.href = 'delete_user.php?username=' + encodeURIComponent(username);
            }
        }

        function openEditModal(lastname, firstname, username, email, role) {
            document.getElementById('editLastname').value = lastname;
            document.getElementById('editFirstname').value = firstname;
            document.getElementById('editUsername').value = username;
            document.getElementById('editEmail').value = email;
            document.getElementById('editRole').value = role;
            document.getElementById('editModal').style.display = 'block';
            document.getElementById('modalOverlay').style.display = 'block';
        }

        function closeEditModal() {
            document.getElementById('editModal').style.display = 'none';
            document.getElementById('modalOverlay').style.display = 'none';
        }

        function checkPasswordStrength() {
            const password = document.getElementById('password').value;
            const passwordStrength = document.getElementById('passwordStrength');
            const regexUpperCase = /[A-Z]/;
            const regexNumber = /[0-9]/;
            const regexSpecial = /[!@#$%^&*(),.?":{}|<>]/;
            const minLength = 8;

            if (password.length >= minLength && regexUpperCase.test(password) && regexNumber.test(password) && regexSpecial.test(password)) {
                passwordStrength.style.color = 'green';
                passwordStrength.textContent = 'Strong password.';
                passwordStrength.style.display = 'block';
                document.querySelector('button[type="submit"]').disabled = false;
            } else {
                passwordStrength.style.color = 'red';
                passwordStrength.textContent = 'Weak password. Must be at least 8 characters long and include one number, one special character, and one uppercase letter.';
                passwordStrength.style.display = 'block';
                document.querySelector('button[type="submit"]').disabled = true;
            }
        }

        document.getElementById('togglePassword').addEventListener('click', function() {
            const passwordField = document.getElementById('password');
            const eyeIcon = document.getElementById('eyeIcon');

            if (passwordField.type === 'password') {
                passwordField.type = 'text';
                eyeIcon.classList.remove('fa-eye');
                eyeIcon.classList.add('fa-eye-slash');
            } else {
                passwordField.type = 'password';
                eyeIcon.classList.remove('fa-eye-slash');
                eyeIcon.classList.add('fa-eye');
            }
        });


        // Function to open the modal
        document.querySelector('.add-btn').addEventListener('click', () => {
            document.getElementById('modalOverlay').style.display = 'block';
            document.getElementById('addUserModal').style.display = 'block';
        });

        // Function to close the modal
        function closeModal() {
            document.getElementById('modalOverlay').style.display = 'none';
            document.getElementById('addUserModal').style.display = 'none';
        }

        async function fetchCategoriesNav() {
            try {
                // Replace '/get-categories' with your actual endpoint to fetch data
                const response = await fetch('../includes/fetch_categories.php');
                const data = await response.json();

                // Get the container where the categories will be displayed
                const container = document.getElementById('dropdown-container');

                // Clear any existing content
                container.innerHTML = '';

                // Loop through the data and dynamically create HTML
                data.forEach(category => {
                    const categoryHtml = `
                        <li><a href="multimedia.php#${category.slug}">${category.name}</a></li>
                    `;
                    container.innerHTML += categoryHtml;
                });
            } catch (error) {
                console.error('Error fetching categories:', error);
            }
        }

        async function init() {
            await fetchCategoriesNav();
        }

        init();

        function logout() {
            // Redirect to the logout PHP script
            window.location.href = '../includes/logout.php'; // Ensure this path matches the location of logout.php
        }
    </script>
</body>

</html>