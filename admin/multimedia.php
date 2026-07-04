<?php
session_start();
require '../includes/db.php'; // Make sure you have the db connection here
require '../includes/logo.php'; // Include the logo fetching function
require '../includes/db_helpers.php';

$id = isset($_GET['id']) ? intval($_GET['id']) : 0;

if (isset($_POST['delete_id'])) {
    $recordId = $_POST['delete_id'];

    // Fetch the record from multimedia, including the category
    $query = $db->prepare("SELECT id, title, content, image_url, category FROM multimedia WHERE id = ?");
    $query->bind_param("i", $recordId);
    $query->execute();
    $result = $query->get_result();

    if ($result->num_rows > 0) {
        // Fetch the record details
        $record = $result->fetch_assoc();
        $title = $record['title'];
        $content = $record['content'];
        $imageUrl = $record['image_url'];
        $category = $record['category'];  // Get category

        // Archive the record to deleted_posts, including the category
        $archiveResult = archiveDeletedRecord($db, 'multimedia', $recordId, $title, $content, $imageUrl, $category);

        if ($archiveResult) {
            // Delete the record from multimedia
            $deleteQuery = $db->prepare("DELETE FROM multimedia WHERE id = ?");
            $deleteQuery->bind_param("i", $recordId);
            $deleteQuery->execute();

            if ($deleteQuery->affected_rows > 0) {
            } else {
            }

            $deleteQuery->close();
        } else {
        }
    } else {
    }

    // Close the initial query connection
    $query->close();
} else {
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

$query = "SELECT slug, name FROM categories";
$result = $conn->query($query);

if ($result->num_rows > 0) {
    $categories = $result->fetch_all(MYSQLI_ASSOC);
} else {
    $categories = [];
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
    <link rel="stylesheet" href="css/multimedia.css">
    <link rel="stylesheet" href="css/sidebar.css">
    <link rel="stylesheet" href="css/multimedia_modal.css">
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
            <li><a href="announcement.php"><i class="fas fa-bullhorn"></i> Announcements</a></li>
            <li><a href="articles.php"><i class="fas fa-file-alt"></i> News & Articles</a></li>
            <li class="dropdown">
                <a href="multimedia.php" class="dropbtn active"><i class="fas fa-camera"></i> Multimedia</a>
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
            <h1 style="color:green; font-size:28px;">Multimedia</h1>
            <button class="add-btn">+ Add Post</button>
        </div>
        <!-- Modal for Adding Multimedia -->
        <div id="addMultimediaModal" class="modal">
            <div class="modal-content">
                <div class="back-btn-container" id="closeModal">
                    <img src="images/arrowback.png" alt="Back Button">
                    <span>Cancel</span>
                </div>
                <form action="admin_multimedia_actions.php" method="POST" enctype="multipart/form-data">
                    <input type="hidden" name="action" value="create">
                    <select name="category" id="category" required>
                        <?php foreach ($categories as $category): ?>
                            <option value="<?= htmlspecialchars($category['slug']) ?>">
                                <?= htmlspecialchars($category['name']) ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                    <input type="text" id="title" name="title" placeholder="Multimedia Title" required>
                    <textarea id="content" name="content" rows="10" placeholder="Multimedia Content" required></textarea>
                    <input type="url" id="link" name="link" placeholder="Link for website (e.g., https://example.com)" required>
                    <input type="file" id="image_file" name="image_file" accept="image/*" required>
                    <!-- Submit -->
                    <button type="submit" class="modal-submit-btn">Publish Multimedia</button>
                </form>
            </div>
        </div>

        <!-- Modal for Editing Multimedia -->
        <div id="editMultimediaModal" class="modal">
            <div class="modal-content">
                <div class="back-btn-container" id="closeEditModal">
                    <img src="images/arrowback.png" alt="Back Button">
                    <span>Cancel</span>
                </div>
                <form action="admin_multimedia_actions.php" method="POST" enctype="multipart/form-data" id="editForm">
                    <input type="hidden" name="action" value="edit">
                    <input type="hidden" name="multimedia_id" id="edit_multimedia_id">
                    <select name="category" id="edit_category" required>
                        <?php foreach ($categories as $category): ?>
                            <option value="<?= htmlspecialchars($category['slug']) ?>">
                                <?= htmlspecialchars($category['name']) ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                    <input type="text" id="edit_title" name="title" placeholder="Multimedia Title" required>
                    <textarea id="edit_content" name="content" rows="10" placeholder="Multimedia Content" required></textarea>
                    <input type="url" id="edit_link" name="link" placeholder="Link for website (e.g., https://example.com)">
                    <input type="file" id="edit_image_file" name="image_file" accept="image/*" placeholde="Replace Image" >     
                    <button type="submit" class="modal-submit-btn">Update Multimedia</button>
                </form>
            </div>
        </div>

        <!-- Delete Confirmation Modal -->
        <div id="deleteConfirmationModal" class="delete-confirmation-modal">
            <div class="modal-content">
                <img src="images/question-square-fill.png" alt="Delete Icon" class="modal-icon"> 
                <h2>Delete Multimedia?</h2>
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
                <h2>Multimedia Deleted!</h2>
                <div class="modal-buttons">
                    <button class="back-to-multimedia-btn" id="backToMultimediaBtn">Back to Multimedia</button>
                </div>
            </div>
        </div>
        <div id="categories-container">

        </div>

    </main>
    <script>
        async function fetchCategoriesItems() {
            try {
                // Replace '/get-categories' with your actual endpoint to fetch data
                const response = await fetch('../includes/fetch_categories.php');
                const data = await response.json();
                
                // Get the container where the categories will be displayed
                const container = document.getElementById('categories-container');
                
                // Clear any existing content
                container.innerHTML = '';
                
                // Loop through the data and dynamically create HTML
                data.forEach(category => {
                    const categoryHtml = `
                        <h2 style="color: #338019; font-size: 28px; padding: 0 5%;">${category.name}</h2>
                        <section id="${category.slug}" class="category-container"></section>
                    `;
                    container.innerHTML += categoryHtml;
                });
            } catch (error) {
                console.error('Error fetching categories:', error);
            }
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
                        <li><a href="#${category.slug}">${category.name}</a></li>
                    `;
                    container.innerHTML += categoryHtml;
                });
            } catch (error) {
                console.error('Error fetching categories:', error);
            }
        }

        const modal = document.getElementById('addMultimediaModal');
        const addMultimediaBtn = document.querySelector('.add-btn');
        const closeModal = document.getElementById('closeModal');

        // Open modal when clicking "Add Multimedia"
        addMultimediaBtn.addEventListener('click', () => {
            modal.style.display = 'flex'; // Show the modal
        });

        // Close modal when clicking the "Cancel" button (back-btn-container)
        closeModal.addEventListener('click', () => {
            modal.style.display = 'none'; // Hide the modal
        });

        function fetchMultimedia() {
            console.log('Fetching multimedia data...');
            fetch('../includes/fetch_multimedia.php?timestamp=' + new Date().getTime())
            .then(response => response.json())
            .then(data => {
                // Clear all category sections first to ensure fresh content
                console.log('Fetched multimedia data:', data);  // Check the structure of data
                if (!Array.isArray(data)) {
            console.error('Expected an array but got:', typeof data);
            return;  // Exit early if data is not in the expected format
        }
                document.querySelectorAll('.category-container').forEach(section => section.innerHTML = '');
                

                // Iterate through the fetched multimedia data
                data.forEach(item => {
                    console.log('Item:', item);
                    
                    // Define the HTML content for each item
                    const itemHTML = `
                        <div class="category-item" data-id="${item.id}">
                            <a href="${item.link}" data-id="${item.id}" target="_blank"><img src="${item.image_url}" alt="${item.title}" class="category-img"></a>
                            <div class="category-content">
                                <div class="header-container">
                                    <h3><a href="${item.link}" data-id="${item.id}" target="_blank">${item.title}</a></h3>
                                    <div class="multimedia-buttons">
                                        <button class="edit-btn"><img src="images/Pencil.png" alt="Edit">Edit</button>
                                        <button class="delete-btn"><img src="images/trash.png" alt="Delete">Delete</button>
                                    </div>
                                </div>
                                <p><a href="${item.link}" data-id="${item.id}" target="_blank" style="color: #555;">${item.content}</a></p>
                            </div>
                        </div>`;
                    // Use the category id to target the correct section
                    const categoryId = item.category.toLowerCase().replace(/\s+/g, '-');  // Convert spaces to hyphens and lowercase

                    // Log the generated categoryId for debugging purposes
                    console.log('Generated category ID:', categoryId);

                    // Check if the category section exists in the DOM, and append content accordingly
                    const categorySection = document.getElementById(categoryId);
                    if (categorySection) {
                        console.log('Appending item to category section:', categoryId);
                        categorySection.innerHTML += itemHTML;  // Add the new item to the correct category
                    } else {
                        console.warn('Category section not found for ID:', categoryId);
                    }
                });

                if (window.location.hash) {
                        const targetElement = document.querySelector(window.location.hash);
                        if (targetElement) {
                            targetElement.scrollIntoView({ behavior: 'smooth' });
                        }
                    }

                document.querySelectorAll('.edit-btn').forEach(button => {
                    button.addEventListener('click', (event) => {
                        const multimediaItem = event.target.closest('.category-item');
                        const multimediaId = multimediaItem.getAttribute('data-id'); // Get the multimedia ID
                        openEditModal(multimediaId); // Open the modal and load the multimedia data
                    });
                });

                // Add Delete Button click listener
                document.querySelectorAll('.delete-btn').forEach(button => {
                    button.addEventListener('click', (event) => {
                        const multimediaItem = event.target.closest('.category-item');
                        const multimediaId = multimediaItem.getAttribute('data-id'); // Get the multimedia ID
                        showDeleteConfirmationModal(multimediaId, multimediaItem); // Show delete confirmation modal
                    });
                });
            })            
            .catch(error => console.error('Error fetching multimedia:', error));
    }

    async function init() {
        await fetchCategoriesNav();
        await fetchCategoriesItems();  // Ensure categories are fetched first
        fetchMultimedia();        // Then fetch multimedia
    }

    // Call the init function to start the data fetching process
    init();


    // Open the Edit multimedia Modal and populate the form with the current multimedia data
    function openEditModal(multimediaId) {
        fetch(`../includes/fetch_multimedia.php?id=${multimediaId}`)
            .then(response => response.json())
            .then(data => {
                if (data.error) {
                    alert(data.error);
                } else {
                    // Populate the modal with the fetched multimedia data
                    document.getElementById('editMultimediaModal').style.display = 'flex'; // Show modal
                    document.getElementById('edit_category').value = data.category; // Set the title
                    document.getElementById('edit_title').value = data.title; // Set the title
                    document.getElementById('edit_content').value = data.content; // Set the content\
                    document.getElementById('edit_link').value = data.link; // Populate the link field
                    document.getElementById('edit_multimedia_id').value = multimediaId; // Set the hidden multimedia ID field
                }
            })
            .catch(error => console.error('Error fetching multimedia details:', error));
    }

    // Close modal when clicking the "Cancel" button in the Edit Modal
    document.getElementById('closeEditModal').addEventListener('click', () => {
        document.getElementById('editMultimediaModal').style.display = 'none'; // Hide the modal
    });


    // Show Delete Confirmation Modal
    function showDeleteConfirmationModal(multimediaId) {
        const deleteModal = document.getElementById('deleteConfirmationModal');
        deleteModal.style.display = 'flex'; // Show the delete confirmation modal

        // Set up the "Delete" button
        const confirmDeleteBtn = document.getElementById('confirmDeleteBtn');
        confirmDeleteBtn.onclick = function() {
            deleteMultimedia(multimediaId); // Call delete function
            deleteModal.style.display = 'none'; // Close the delete confirmation modal
        };

        // Set up the "Cancel" button
        const cancelDeleteBtn = document.getElementById('cancelDeleteBtn');
        cancelDeleteBtn.onclick = function() {
            deleteModal.style.display = 'none'; // Close the delete confirmation modal
        };
    }

    // Function to delete multimedia
    function deleteMultimedia(multimediaId) {
        fetch(`delete_multimedia.php?id=${multimediaId}`, {
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
        .catch(error => console.error('Error deleting multimedia:', error));
    }

    // Show Success Modal after deletion
    function showSuccessModal() {
        const successModal = document.getElementById('successModal');
        successModal.style.display = 'flex'; // Show the success modal

        const backToMultimediaBtn = document.getElementById('backToMultimediaBtn');
        backToMultimediaBtn.onclick = function() {
            successModal.style.display = 'none'; // Close the success modal
            fetchMultimedia(); // Reload the multimedia after deletion
        };
    }

        function logout() {
            // Redirect to the logout PHP script
            window.location.href = '../includes/logout.php'; // Ensure this path matches the location of logout.php
        }
        document.querySelectorAll('a[href^="#"]').forEach(anchor => {
            anchor.addEventListener('click', function (e) {
                e.preventDefault();

                document.querySelector(this.getAttribute('href')).scrollIntoView({
                    behavior: 'smooth'
                });
            });
        });

    </script>
</body>
</html>