<?php
session_start();
require '../includes/db.php'; // Make sure you have the db connection here
require '../includes/logo.php'; // Include the logo fetching function
require '../includes/db_helpers.php';


$id = isset($_GET['id']) ? intval($_GET['id']) : 0;

if (isset($_POST['delete_id'])) {
    $recordId = $_POST['delete_id'];

    // Fetch the record from article, including the author and category
    $query = $db->prepare("SELECT id, title, content, image_url, author FROM articles WHERE id = ?");
    $query->bind_param("i", $recordId);
    $query->execute();
    $result = $query->get_result();

    if ($result->num_rows > 0) {
        // Fetch the record details
        $record = $result->fetch_assoc();
        $title = $record['title'];
        $content = $record['content'];
        $imageUrl = $record['image_url'];
        $author = $record['author']; // Get author

        // Archive the record to deleted_posts, including the author and category
        $archiveResult = archiveDeletedRecord($db, 'articles', $recordId, $title, $content, $imageUrl, $author);

        if ($archiveResult) {
            // Delete the record from article
            $deleteQuery = $db->prepare("DELETE FROM articles WHERE id = ?");
            $deleteQuery->bind_param("i", $recordId);
            $deleteQuery->execute();

            if ($deleteQuery->affected_rows > 0) {
                // Successfully deleted
            } else {
                // Error during deletion
            }

            $deleteQuery->close();
        } else {
            // Archive failure
        }
    } else {
        // Record not found
    }

    // Close the initial query connection
    $query->close();
} else {
    // No delete_id set
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
    <link rel="stylesheet" href="css/articles.css"> <!-- Changed from news.css to articles.css -->
    <link rel="stylesheet" href="css/sidebar.css">
    <link rel="stylesheet" href="css/articles_modal.css">
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
            <li><a href="articles.php" class="active"><i class="fas fa-file-alt"></i> News & Articles</a></li> 
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
    <!-- Header with Add Article button -->
    <div class="header">
        <h1 style="color:green; font-size:28px;">Articles</h1> 
        <button class="add-btn">+ Add Article</button>
    </div>
    
    <!-- Modal for Adding Article -->
    <div id="addArticleModal" class="modal"> 
        <div class="modal-content">
            <div class="back-btn-container" id="closeModal">
                <img src="images/arrowback.png" alt="Back Button">
                <span>Cancel</span>
            </div>
            <form action="admin_article_action.php" method="POST" enctype="multipart/form-data">
                <input type="hidden" name="action" value="create">
                <input type="text" id="title" name="title" placeholder="Article Title" required> 
                <input type="text" id="author" name="author" placeholder="Author Name" required>
                <textarea id="content" name="content" rows="10" placeholder="Article Content" required></textarea>
                <input type="file" id="image_file" name="image_file" accept="image/*" required>
                <button type="submit" class="modal-submit-btn">Publish Article</button>
            </form>
        </div>
    </div>

    <!-- Modal for Editing Article -->
    <div id="editArticleModal" class="modal">
        <div class="modal-content">
            <div class="back-btn-container" id="closeEditModal">
                <img src="images/arrowback.png" alt="Back Button">
                <span>Cancel</span>
            </div>
            <form action="admin_article_action.php" method="POST" enctype="multipart/form-data" id="editForm">
                <input type="hidden" name="action" value="edit">
                <input type="hidden" name="article_id" id="edit_article_id">
                <input type="text" id="edit_title" name="title" placeholder="Article Title" required>
                <input type="text" id="edit_author" name="author" placeholder="Author Name" required>
                <textarea id="edit_content" name="content" rows="10" placeholder="Article Content" required></textarea>
                <input type="file" id="edit_image_file" name="image_file" accept="image/*" placeholder="Replace Image">
                <button type="submit" class="modal-submit-btn">Update Article</button> 
            </form>
        </div>
    </div>

    <!-- Delete Confirmation Modal -->
    <div id="deleteConfirmationModal" class="delete-confirmation-modal">
        <div class="modal-content">
            <img src="images/question-square-fill.png" alt="Delete Icon" class="modal-icon"> 
            <h2>Delete Article?</h2>
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
            <h2>Article Deleted!</h2> 
            <div class="modal-buttons">
                <button class="back-to-articles-btn" id="backToArticlesBtn">Back to Articles</button> 
            </div>
        </div>
    </div>

    <!-- Articles Container -->
    <section class="articles-container">
        <div class="article-item">
            <div class="article-image">
                <img src="images/crop1.jpg" alt="Agriculture Article 1">
            </div>
            <div class="article-summary">
                <h3>Modern Irrigation Techniques for Rice Farmers</h3>
                <p style="color: #2f6c2f;">November 15, 2024</p>
                <p class="short-summary">Explore the new technologies revolutionizing irrigation methods in the rice farming industry...</p>
                <p class="full-summary" style="display:none;">Modern irrigation techniques have greatly improved the efficiency of rice farming, reducing water consumption while increasing crop yields. From drip irrigation to sprinkler systems, the adoption of technology in farming practices has allowed rice farmers to grow crops in areas previously unsuitable for rice cultivation. Learn how these innovative systems work and how they contribute to sustainability in agriculture...</p>
                <span class="read-more-text">Read More</span>
            </div>
        </div>
        <!-- Dynamically loaded articles content will appear here -->
    </section>
    
    </main>
    <script>
        const modal = document.getElementById('addArticleModal');
        const addArticleBtn = document.querySelector('.add-btn');
        const closeModal = document.getElementById('closeModal');

        // Open modal when clicking "Add Article"
        addArticleBtn.addEventListener('click', () => {
            modal.style.display = 'flex'; // Show the modal
        });

        // Add Edit Button click listener
        document.querySelectorAll('.edit-btn').forEach(button => {
            button.addEventListener('click', (event) => {
                const articleItem = event.target.closest('.article-item');
                if (articleItem) {  // Ensure articleItem exists
                    const articleId = articleItem.getAttribute('data-id'); // Get the article ID
                    openEditModal(articleId); // Open the modal and load the article data
                }
            });
        });


        // Close modal when clicking the "Cancel" button (back-btn-container)
        closeModal.addEventListener('click', () => {
            modal.style.display = 'none'; // Hide the modal
        });

        // Fetch and display articles on page load
        function fetchArticles() {
            fetch('../includes/fetch_article.php')
                .then(response => response.json())
                .then(data => {
                    console.log(data); // Check if the order is correct in the response
                    const articlesContainer = document.querySelector('.articles-container');
                    articlesContainer.innerHTML = ''; // Clear any existing article items

                    // Render each article item dynamically
                    data.forEach(article => {
                        const articleContentWithLineBreaks = article.content.replace(/\n/g, '<br>'); // Preserve line breaks
                        const articleItem = `
                            <div class="article-item" data-id="${article.id}">
                                <div class="article-image">
                                    <img src="${article.image_url}" alt="${article.title}">
                                </div>
                                <div class="article-summary">
                                    <h3>${article.title}</h3>
                                    <p style="color: #2f6c2f;">${article.formatted_date} by ${article.author}</p>
                                    <p class="short-summary">${article.content.substring(0, 100)}...</p>
                                    <p class="full-summary" style="display:none;">${articleContentWithLineBreaks}</p>
                                    <div class="bottom-row">
                                        <span class="read-more-text">Read More</span>
                                        <div class="article-buttons">
                                            <button class="edit-btn"><img src="images/Pencil.png" alt="Edit">Edit</button>
                                            <button class="delete-btn"><img src="images/trash.png" alt="Delete">Delete</button>
                                        </div>
                                    </div>
                                </div>
                            </div>`;
                        articlesContainer.innerHTML += articleItem;
                    });

                    // Add "Read More" toggle functionality
                    document.querySelectorAll('.read-more-text').forEach(link => {
                        link.addEventListener('click', (event) => {
                            const articleItem = event.target.closest('.article-item');
                            if (articleItem) {  // Ensure articleItem exists
                                const shortSummary = articleItem.querySelector('.short-summary');
                                const fullSummary = articleItem.querySelector('.full-summary');

                                shortSummary.style.display = shortSummary.style.display === 'none' ? 'block' : 'none';
                                fullSummary.style.display = fullSummary.style.display === 'none' ? 'block' : 'none';
                                event.target.innerText = fullSummary.style.display === 'block' ? 'Read Less' : 'Read More';
                            }
                        });
                    });

                    // Add Edit Button click listener
                    document.querySelectorAll('.edit-btn').forEach(button => {
                        button.addEventListener('click', (event) => {
                            const articleItem = event.target.closest('.article-item');
                            if (articleItem) {  // Ensure articleItem exists
                                const articleId = articleItem.getAttribute('data-id'); // Get the article ID
                                openEditModal(articleId); // Open the modal and load the article data
                            }
                        });
                    });

                    // Add Delete Button click listener
                    document.querySelectorAll('.delete-btn').forEach(button => {
                        button.addEventListener('click', (event) => {
                            const articleItem = event.target.closest('.article-item');
                            if (articleItem) {  // Ensure articleItem exists
                                const articleId = articleItem.getAttribute('data-id'); // Get the article ID
                                showDeleteConfirmationModal(articleId); // Show delete confirmation modal
                            }
                        });
                    });
                })
                .catch(error => console.error('Error fetching articles:', error));
        }

        // Open the Edit Article Modal and populate the form with the current article data
        function openEditModal(articleId) {
            fetch(`../includes/fetch_article.php?id=${articleId}`)
                .then(response => response.json())
                .then(data => {
                    if (data.error) {
                        alert(data.error);
                    } else {
                        // Populate the modal with the fetched article data
                        document.getElementById('editArticleModal').style.display = 'flex'; // Show modal
                        document.getElementById('edit_title').value = data.title; // Set the title
                        document.getElementById('edit_content').value = data.content; // Set the content
                        document.getElementById('edit_author').value = data.author; // Set the author
                        document.getElementById('edit_article_id').value = articleId; // Set the hidden article ID field
                    }
                })
                .catch(error => console.error('Error fetching article details:', error));
        }


        // Close modal when clicking the "Cancel" button in the Edit Modal
        document.getElementById('closeEditModal').addEventListener('click', () => {
            document.getElementById('editArticleModal').style.display = 'none'; // Hide the modal
        });

        // Show Delete Confirmation Modal
        function showDeleteConfirmationModal(articleId) {
            const deleteModal = document.getElementById('deleteConfirmationModal');
            deleteModal.style.display = 'flex'; // Show the delete confirmation modal

            // Set up the "Delete" button
            const confirmDeleteBtn = document.getElementById('confirmDeleteBtn');
            confirmDeleteBtn.onclick = function() {
                deleteArticle(articleId); // Call delete function
                deleteModal.style.display = 'none'; // Close the delete confirmation modal
            };

            // Set up the "Cancel" button
            const cancelDeleteBtn = document.getElementById('cancelDeleteBtn');
            cancelDeleteBtn.onclick = function() {
                deleteModal.style.display = 'none'; // Close the delete confirmation modal
            };
        }

        // Function to delete article
        function deleteArticle(articleId) {
            fetch(`delete_article.php?id=${articleId}`, {
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
            .catch(error => console.error('Error deleting article:', error));
        }

        // Show Success Modal after deletion
        function showSuccessModal() {
            const successModal = document.getElementById('successModal');
            successModal.style.display = 'flex'; // Show the success modal

            const backToArticlesBtn = document.getElementById('backToArticlesBtn');
            backToArticlesBtn.onclick = function() {
                successModal.style.display = 'none'; // Close the success modal
                fetchArticles(); // Reload the articles after deletion
            };
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
            fetchArticles();        // Then fetch multimedia
        }

        init();

        function logout() {
            // Redirect to the logout PHP script
            window.location.href = '../includes/logout.php'; // Ensure this path matches the location of logout.php
        }

    </script>
</body>
</html>
