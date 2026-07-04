<?php
session_start();
require '../includes/db.php'; // Make sure you have the db connection here
require '../includes/logo.php'; // Include the logo fetching function
require_once '../includes/db_helpers.php'; // Assuming this includes the database connection

$id = isset($_GET['id']) ? intval($_GET['id']) : 0;

if ($id > 0) {
    // Fetch the announcement before deletion
    $fetchQuery = "SELECT * FROM announcement WHERE id = ?";
    $stmt = $conn->prepare($fetchQuery);
    $stmt->bind_param("i", $id);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows > 0) {
        $announcement = $result->fetch_assoc();

        // Archive the record before deleting
        archiveDeletedRecord(
            $conn,                  // Database connection
            'announcement',         // Source table name
            $id,                    // Record ID
            $announcement['title'], // Title
            $announcement['content'], // Content
            $announcement['image_url'] // Image URL (added if available)
        );

        // Delete the announcement from the original table
        $deleteQuery = "DELETE FROM announcement WHERE id = ?";
        $deleteStmt = $conn->prepare($deleteQuery);
        $deleteStmt->bind_param("i", $id);
        $deleteStmt->execute();

        // Send success response
    } else {
        // If no record found
    }
} else {
    // If no ID is provided or invalid
}


// Check if the user is logged in and if their role is 'staff' or 'admin'
if (!isset($_SESSION['role']) || ($_SESSION['role'] != 'staff' && $_SESSION['role'] != 'admin')) {
    // If not, redirect to the login page or a "no access" page
    header("Location: ../login/login.php");
    exit;
}

// If the user is a staff member or admin, allow access
if ($_SESSION['role'] == 'staff') {
    // Staff has limited access
    // You can disable or hide certain elements for staff
    $hide_admin_elements = true;
} else {
    // Admin has full access
    $hide_admin_elements = false;
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
    <link rel="stylesheet" href="css/announcement.css">
    <link rel="stylesheet" href="css/sidebar.css">
    <link rel="stylesheet" href="css/announcement_modal.css">
</head>
<body>
    <!-- Sidebar -->
    <div class="sidebar">
        <div class="header-title">
            <img src="<?= getLogoPath($conn) ?>" alt="AgriWeb Logo" class="header-logo">
            <div class="logo-text">
                <h1 style="color:black;  margin: 0; font-size: 18px; letter-spacing: 1px; font-weight: 500; margin-top: 10px;">RIZAL</h1>
                <h1 style="color:black;  margin: 0; font-size: 18px; letter-spacing: 1px; font-weight: 500; margin-top: -5px;">AGRI</h1>
                <h1 style="color:black;  margin: 0; font-size: 18px; letter-spacing: 1px; font-weight: 500; margin-top: -5px">CULTIVA</h1>
            </div>
        </div>
        <ul class="nav-links">
            <li><a href="announcement.php" class="active"><i class="fas fa-bullhorn"></i> Announcements</a></li>
            <li><a href="articles.php"><i class="fas fa-file-alt"></i> News & Articles</a></li>

            <li class="dropdown">
                <a href="multimedia.php" class="dropbtn"><i class="fas fa-camera"></i> Multimedia</a>
                <ul class="dropdown-content" id="dropdown-container">

                </ul>
            </li>
            <li><a href="about.php"><i class="fas fa-file"></i> About RAC</a></li>
            <li><a href="email.php"><i class="fas fa-envelope"></i> Email Inquiry</a></li>
            <li style="margin-bottom: 5px;"><a href="archive.php" ><i class="fas fa-archive"></i> Archive</a></li> <!-- Archive added here -->


            <?php if (!$hide_admin_elements): ?>
            <p style="color:gray; margin: 0;">Admin</p>
            <li><a href="dashboard.php"><i class="fas fa-tachometer-alt"></i> Dashboard</a></li> <!-- Added icon -->
            <li><a href="reports.php"><i class="fas fa-chart-line"></i> Reports</a></li> <!-- Added icon -->
            <li><a href="user.php"><i class="fas fa-users"></i> User Management</a></li> <!-- Added icon -->
            <li style="margin-bottom: 0;"><a href="settings.php"><i class="fas fa-cogs"></i> Settings</a></li> <!-- Added icon -->
            <?php endif; ?>
        </ul>
        <button class="logout-btn" style="text-align:center;" onclick="logout()">
            <i class="fas fa-sign-out-alt logout-icon" style="margin-left:-20px;"></i> Logout
        </button>
    </div>
    <main>
        <div class="header">
            <h1 style="color:green; font-size:28px;">Announcement</h1>
            <button class="add-btn">+ Add Announcement</button>
        </div>
        <!-- Modal for Adding Announcement -->
                <!-- Modal for Adding Announcement -->
                <div id="addAnnouncementModal" class="modal">
            <div class="modal-content">
                <div class="back-btn-container" id="closeModal">
                    <img src="images/arrowback.png" alt="Back Button">
                    <span>Cancel</span>
                </div>
                <form action="admin_announcement_actions.php" method="POST" enctype="multipart/form-data">
                    <input type="hidden" name="action" value="create">
                    <input type="text" id="title" name="title" placeholder="Announcement Title" required>
                    <textarea id="content" name="content" rows="10" placeholder="Announcement Content" required></textarea>
                    <input type="url" id="link" name="link" placeholder="Link for website (e.g., https://example.com)" required>
                    <input type="file" id="image_file" name="image_file" accept="image/*" required>
                    <button type="submit" class="modal-submit-btn">Publish Announcement</button>
                </form>
            </div>
        </div>

        <!-- Modal for Editing Announcement -->
        <div id="editAnnouncementModal" class="modal">
            <div class="modal-content">
                <div class="back-btn-container" id="closeEditModal">
                    <img src="images/arrowback.png" alt="Back Button">
                    <span>Cancel</span>
                </div>
                <form action="admin_announcement_actions.php" method="POST" enctype="multipart/form-data" id="editForm">
                    <input type="hidden" name="action" value="edit">
                    <input type="hidden" name="announcement_id" id="edit_announcement_id">
                    <input type="text" id="edit_title" name="title" placeholder="Announcement Title" required>
                    <textarea id="edit_content" name="content" rows="10" placeholder="Announcement Content" required></textarea>
                    <input type="url" id="edit_link" name="link" placeholder="Link for website (e.g., https://example.com)">
                    <input type="file" id="edit_image_file" name="image_file" accept="image/*" placeholder="Replace Image">
                    <button type="submit" class="modal-submit-btn">Update Announcement</button>
                </form>
            </div>
        </div>

        <!-- Delete Confirmation Modal -->
        <div id="deleteConfirmationModal" class="delete-confirmation-modal">
            <div class="modal-content">
                <img src="images/question-square-fill.png" alt="Delete Icon" class="modal-icon"> 
                <h2>Delete Announcement?</h2>
                <div class="modal-buttons">
                    <button class="cancel-btn" id="cancelDeleteBtn">Cancel</button>
                    <button class="delete-btn" id="confirmDeleteBtn">Delete</button>
                </div>
            </div>
        </div>

        <!-- Success Modal -->
        <div id="successModal" class="success-modal">
            <div class="modal-content">
                <img src="images/check-circle-fill.png" alt="Delete Icon" class="modal-icon"> 
                <h2>Announcement Deleted!</h2>
                <div class="modal-buttons">
                    <button class="back-to-announcements-btn" id="backToAnnouncementsBtn">Back to Announcements</button>
                </div>
            </div>
        </div>

        <section class="announcement-container">
            
        </section>
    </main>
    <script>
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
        
        const modal = document.getElementById('addAnnouncementModal');
        const addAnnouncementBtn = document.querySelector('.add-btn');
        const closeModal = document.getElementById('closeModal');

        // Open modal when clicking "Add Announcement"
        addAnnouncementBtn.addEventListener('click', () => {
            modal.style.display = 'flex'; // Show the modal
        });

        // Close modal when clicking the "Cancel" button (back-btn-container)
        closeModal.addEventListener('click', () => {
            modal.style.display = 'none'; // Hide the modal
        });

        // Fetch and display announcements on page load
        function fetchAnnouncements() {
            fetch('../includes/fetch_announcement.php')
                .then(response => response.json())
                .then(data => {
                    const announcementContainer = document.querySelector('.announcement-container');
                    announcementContainer.innerHTML = ''; 

                    data.forEach(announcement => {
                        const announcementContentWithLineBreaks = announcement.content.replace(/\n/g, '<br>'); // Preserve line breaks
                        const announcementItem = `
                            <div class="announcement-item" data-id="${announcement.id}">
                                <table>
                                    <tr>
                                        <td><a href="${announcement.link}" target="_blank" class="announcement-link"><img src="${announcement.image_url}" alt="${announcement.title}" class="image"></a></td>
                                        <td style="width: 100%">
                                            <div class="announcement-title-container">
                                                <a href="${announcement.link}" target="_blank" class="announcement-link"><h4 style="color:black; margin: 0;">${announcement.title}</h4></a>
                                                <div class="announcement-buttons">
                                                    <button class="edit-btn"><img src="images/Pencil.png" alt="Edit">Edit</button>
                                                    <button class="delete-btn"><img src="images/trash.png" alt="Delete">Delete</button>
                                                </div>
                                            </div>
                                            <a href="${announcement.link}" target="_blank" class="announcement-link">
                                            <p>${announcementContentWithLineBreaks}</p>
                                            </a>
                                        </td>
                                    </tr>
                                </table>
                            </div>
                        `;
                        // Append each dynamically generated announcement as a table row inside the announcement-container
                        announcementContainer.innerHTML += announcementItem;
                    });

                    // Add Edit Button click listener
                    document.querySelectorAll('.edit-btn').forEach(button => {
                        button.addEventListener('click', (event) => {
                            const announcementItem = event.target.closest('.announcement-item');
                            const announcementId = announcementItem.getAttribute('data-id'); // Get the announcement ID
                            openEditModal(announcementId); // Open the modal and load the announcement data
                        });
                    });

                    // Add Delete Button click listener
                    document.querySelectorAll('.delete-btn').forEach(button => {
                        button.addEventListener('click', (event) => {
                            const announcementItem = event.target.closest('.announcement-item');
                            const announcementId = announcementItem.getAttribute('data-id'); // Get the announcement ID
                            showDeleteConfirmationModal(announcementId); // Show delete confirmation modal
                        });
                    });
                })
                .catch(error => console.error('Error fetching announcements:', error));
        }


        // Open the Edit Announcement Modal and populate the form with the current announcement data
        function openEditModal(announcementId) {
            fetch(`../includes/fetch_announcement.php?id=${announcementId}`)
                .then(response => response.json())
                .then(data => {
                    if (data.error) {
                        alert(data.error);
                    } else {
                        // Populate the modal with the fetched announcement data
                        document.getElementById('editAnnouncementModal').style.display = 'flex'; // Show modal
                        document.getElementById('edit_title').value = data.title; // Set the title
                        document.getElementById('edit_content').value = data.content; // Set the content\
                        document.getElementById('edit_link').value = data.link; // Populate the link field
                        document.getElementById('edit_announcement_id').value = announcementId; // Set the hidden announcement ID field
                    }
                })
                .catch(error => console.error('Error fetching announcement details:', error));
        }

        // Close modal when clicking the "Cancel" button in the Edit Modal
        document.getElementById('closeEditModal').addEventListener('click', () => {
            document.getElementById('editAnnouncementModal').style.display = 'none'; // Hide the modal
        });

        // Show Delete Confirmation Modal
        function showDeleteConfirmationModal(announcementId) {
            const deleteModal = document.getElementById('deleteConfirmationModal');
            deleteModal.style.display = 'flex'; // Show the delete confirmation modal

            // Set up the "Delete" button
            const confirmDeleteBtn = document.getElementById('confirmDeleteBtn');
            confirmDeleteBtn.onclick = function() {
                deleteAnnouncement(announcementId); // Call delete function
                deleteModal.style.display = 'none'; // Close the delete confirmation modal
            };

            // Set up the "Cancel" button
            const cancelDeleteBtn = document.getElementById('cancelDeleteBtn');
            cancelDeleteBtn.onclick = function() {
                deleteModal.style.display = 'none'; // Close the delete confirmation modal
            };
        }

        // Function to delete announcement
        function deleteAnnouncement(announcementId) {
            fetch(`delete_announcement.php?id=${announcementId}`, {
                method: 'GET',
            })
            .then(response => response.json())
            .then(data => {
                if (data.status === 'success') {
                    showSuccessModal(); // Show the success modal
                } else {
                    alert('Error: ' + data.message);
                }
            })
            .catch(error => console.error('Error deleting announcement:', error));
        }

        // Show Success Modal after deletion
        function showSuccessModal() {
            const successModal = document.getElementById('successModal');
            successModal.style.display = 'flex'; // Show the success modal

            const backToAnnouncementsBtn = document.getElementById('backToAnnouncementsBtn');
            backToAnnouncementsBtn.onclick = function() {
                successModal.style.display = 'none'; // Close the success modal
                fetchAnnouncements(); // Reload the announcements after deletion
            };
        }

        async function init() {
            await fetchCategoriesNav();
            fetchAnnouncements();        // Then fetch multimedia
        }

        init();

        function logout() {
            // Redirect to the logout PHP script
            window.location.href = '../includes/logout.php'; // Ensure this path matches the location of logout.php
        }
    </script>
</body>
</html>