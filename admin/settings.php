<?php
session_start();
require '../includes/db.php'; // Ensure the database connection is included
require '../includes/logo.php';
require 'settings_backend.php';
require_once '../includes/db_helpers.php'; // Assuming this includes the database connection
require '../includes/db.php'; // Include database connection

// Query to fetch all company email records from the database
$query = "SELECT id, name, slug FROM categories";
$result = $conn->query($query);


$query = "SELECT id, email, company_name, password FROM company_email";
$result = $conn->query($query);

$faq_id = isset($_GET['faq_id']) ? intval($_GET['faq_id']) : 0;

if ($faq_id > 0) {
    // Fetch the FAQ details before deletion
    $fetchQuery = "SELECT * FROM faqs WHERE faq_id = ?";
    $stmt = $conn->prepare($fetchQuery);
    $stmt->bind_param("i", $faq_id);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows > 0) {
        $faq = $result->fetch_assoc();

        // Archive the record before deleting
        archiveDeletedRecord(
            $conn,                // Database connection
            'faqs',               // Source table name
            $faq_id,              // Record ID
            $faq['question'],     // Question (Title equivalent)
            $faq['answer'],       // Answer (Content equivalent)
            null                  // No image URL for FAQ
        );

        // Delete the FAQ from the original table
        $deleteQuery = "DELETE FROM faqs WHERE faq_id = ?";
        $deleteStmt = $stmt = $conn->prepare($deleteQuery);
        $deleteStmt->bind_param("i", $faq_id);
        $deleteStmt->execute();
    } else {
    }
} else {
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
    <link rel="stylesheet" href="css/settings.css">
    <link rel="stylesheet" href="css/sidebar.css">
    <link rel="stylesheet" href="css/settings_modal.css">
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
                <ul class="dropdown-content" id="dropdown-container">

                </ul>
            </li>

            <li><a href="about.php"><i class="fas fa-file"></i> About RAC</a></li>
            <li><a href="email.php"><i class="fas fa-envelope"></i> Email Inquiry</a></li>
            <li style="margin-bottom: 5px;"><a href="archive.php"><i class="fas fa-archive"></i> Archive</a></li> <!-- Archive added here -->

            <?php if (!$hide_admin_elements): ?>
                <p style="color:gray; margin: 0;">Admin</p>
                <li><a href="dashboard.php"><i class="fas fa-tachometer-alt"></i> Dashboard</a></li>
                <li><a href="reports.php"><i class="fas fa-chart-line"></i> Reports</a></li>
                <li><a href="user.php"><i class="fas fa-users"></i> User Management</a></li>
                <li style="margin-bottom: 0;"><a href="settings.php" class="active"><i class="fas fa-cogs"></i> Settings</a></li>
            <?php endif; ?>
        </ul>
        <button class="logout-btn" style="text-align:center;" onclick="logout()">
            <i class="fas fa-sign-out-alt logout-icon" style="margin-left:-20px;"></i> Logout
        </button>
    </div>
    <main>
        <div class="header">
            <h1 style="color:green; font-size:28px;">Settings</h1>
        </div>

        <div class="media-container">
            <div class="media-header">
                <?php if ($row): ?>
                    <h1>Social Media Links</h1>
                    <button id="editMediaButton" style="float: right;">Edit</button>
            </div>
            <div class="media-content">
                <p>Facebook: <a href="<?php echo htmlspecialchars($row['facebook']); ?>" target="_blank"><?php echo htmlspecialchars($row['facebook']); ?></a></p>
                <p>Twitter: <a href="<?php echo htmlspecialchars($row['twitter']); ?>" target="_blank"><?php echo htmlspecialchars($row['twitter']); ?></a></p>
                <p>Instagram: <a href="<?php echo htmlspecialchars($row['instagram']); ?>" target="_blank"><?php echo htmlspecialchars($row['instagram']); ?></a></p>
            <?php else: ?>
                <p>No social media links found.</p>
            <?php endif; ?>
            </div>
        </div>

        <!-- Modal -->
        <div class="media-modal-background" id="mediaModalBackground"></div>
        <div class="media-modal" id="editMediaModal">
            <div class="media-modal-header">
                <h2>Edit Social Media Links</h2>
                <button id="closeButton" class="media-close-btn">×</button>
            </div>
            <form id="editForm" method="post" action="update_links.php">
                <div class="modal-body">
                    <label for="facebook">Facebook:</label>
                    <input type="url" id="facebook" name="facebook" value="<?php echo htmlspecialchars($row['facebook']); ?>" required>

                    <label for="twitter">Twitter:</label>
                    <input type="url" id="twitter" name="twitter" value="<?php echo htmlspecialchars($row['twitter']); ?>" required>

                    <label for="instagram">Instagram:</label>
                    <input type="url" id="instagram" name="instagram" value="<?php echo htmlspecialchars($row['instagram']); ?>" required>
                </div>
                <div class="modal-footer">
                    <button type="submit" class="save-btn">Save Changes</button>
                </div>
            </form>
        </div>



        <div class="logo-container">
            <h3>Current Logo:</h3>
            <img src="<?= getLogoPath($conn) ?>" alt="Current Logo" style="max-height: 100px;"><br>
            <form action="settings.php" method="POST" enctype="multipart/form-data" class="logo-form">
                <p style="font-weight: bold;">Update Site Logo</p>
                <label for="logo" class="custom-file-button">Choose File</label>
                <input type="file" name="logo" id="logo" required><br>
                <button type="submit" class="upload-button-logo">Upload Logo</button>
            </form>
        </div>


        <div class="category-container">
            <div class="header-container">
                <h1>Category Management</h1>
                <button id="addCategoryBtn" class="btn">Add Category</button>
            </div>

            <?php if (isset($successMessage)) {
                echo "<p style='color: green;'>$successMessage</p>";
            } ?>
            <?php if (isset($errorMessage)) {
                echo "<p style='color: red;'>$errorMessage</p>";
            } ?>

            <!-- adding Category Modal Structure -->
            <div id="addCategoryModal" class="modal">
                <div class="modal-content-category">
                    <button class="close-btn" id="closeModal">←</button>
                    <h2>Add New Category</h2>
                    <form method="POST">
                        <div class="form-group">
                            <label for="category_name">Category Name</label>
                            <input type="text" id="category_name" name="category_name" required>
                        </div>
                        <div class="form-group">
                            <label for="category_slug">Category Slug</label>
                            <input type="text" id="category_slug" name="category_slug" required>
                        </div>
                        <button type="submit" name="add_category" class="btn-submit">Add Category</button>
                    </form>
                </div>
            </div>

            <!-- editing Category Modal Structure -->
            <div id="editCategoryModal" class="modal" style="display: none;">
                <div class="modal-content-category">
                    <button class="close-btn" id="closeEditModal">←</button>
                    <h2>Edit Category</h2>
                    <form method="POST">
                        <input type="hidden" name="category_id" id="edit_category_id" value="">
                        <div class="form-group">
                            <label for="edit_category_name">Category Name</label>
                            <input type="text" id="edit_category_name" name="category_name" value="" required>
                        </div>
                        <div class="form-group">
                            <label for="edit_category_slug">Category Slug</label>
                            <input type="text" id="edit_category_slug" name="category_slug" value="" required>
                        </div>
                        <button type="submit" name="update_category" class="btn-submit">Update Category</button>
                    </form>
                </div>
            </div>

            <!-- Display Existing Categories -->
            <table class="table mt-4">
                <thead>
                    <tr>
                        <th>Name</th>
                        <th>Slug</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php
                    // Ensure the query is fetching the correct columns from the 'categories' table
                    $sql = "SELECT id, name, slug FROM categories";
                    $result = $conn->query($sql);

                    if ($result && $result->num_rows > 0):
                        while ($row = $result->fetch_assoc()):
                    ?>
                            <tr>
                                <td><?php echo htmlspecialchars($row['name'], ENT_QUOTES, 'UTF-8'); ?></td>
                                <td><?php echo htmlspecialchars($row['slug'], ENT_QUOTES, 'UTF-8'); ?></td>
                                <td>
                                    <div class="edit-delete-btn">
                                        <div class="edit-btn">
                                            <img src="images/Pencil.png" alt="Edit Icon">
                                            <a href="settings.php?edit=<?php echo urlencode($row['id']); ?>" class="btn btn-warning btn-sm">Edit</a>
                                        </div>
                                        <div class="delete-btn">
                                            <img src="images/trash.png" alt="">
                                            <a href="delete_categories.php?delete=<?php echo $row['id']; ?>" class="btn btn-danger btn-sm" onclick="return confirm('Are you sure you want to delete this category?')">Delete</a>
                                        </div>
                                    </div>
                                </td>
                            </tr>
                        <?php
                        endwhile;
                    else:
                        ?>
                        <tr>
                            <td colspan="3" class="text-center">No categories found.</td>
                        </tr>
                    <?php endif; ?>
                </tbody>
            </table>

        </div>

        <div class="carousel-container">
            <h1>Carousel Management</h1>
            <form action="../includes/update_carousel.php" method="POST" enctype="multipart/form-data">
                <label for="image1">Replace Image 1:</label>
                <input type="file" name="image1" id="image1">
                <label for="alt1">Alt Text 1:</label>
                <input type="text" name="alt1" id="alt1">

                <label for="image2">Replace Image 2:</label>
                <input type="file" name="image2" id="image2">
                <label for="alt2">Alt Text 2:</label>
                <input type="text" name="alt2" id="alt2">

                <label for="image3">Replace Image 3:</label>
                <input type="file" name="image3" id="image3">
                <label for="alt3">Alt Text 3:</label>
                <input type="text" name="alt3" id="alt3">

                <button type="submit">Save Carousel Images</button>
            </form>
        </div>

        <div class="terms-container">
            <h1 style="margin-bottom: 2%;">Terms and Conditions</h1>
            <div class="terms-content">
                <?php if ($terms): ?>
                    <h2><?= htmlspecialchars($terms['title']) ?></h2>
                    <p><strong>Effective Date:</strong> <?= htmlspecialchars($formatted_date) ?></p>
                    <p><?= nl2br(htmlspecialchars($terms['content'])) ?></p>
                    <button class="btn" onclick="openTermsModal()">Edit</button>
                <?php else: ?>
                    <p>No terms found in the database.</p>
                <?php endif; ?>
            </div>
        </div>

        <div class="modal-overlay" id="modalTermsOverlay" onclick="closeTermsModal()"></div>

        <div class="modal-termsPolicies" id="editModalTerms">
            <form action="update_terms.php" method="POST">
                <div class="modal-header">
                    <h5>Edit Terms and Conditions</h5>
                    <button type="button" class="btn-close" onclick="closeTermsModal()">&times;</button>
                </div>
                <div class="modal-body">
                    <input type="hidden" name="id" value="<?= $terms['id'] ?>">
                    <label for="title">Title</label>
                    <input type="text" id="title" name="title" value="<?= htmlspecialchars($terms['title']) ?>" required>
                    <label for="effective_date">Effective Date</label>
                    <input type="date" id="effective_date" name="effective_date" value="<?= htmlspecialchars($terms['effective_date']) ?>" required>
                    <label for="content">Content</label>
                    <textarea id="content" name="content" required><?= htmlspecialchars($terms['content']) ?></textarea>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" onclick="closeTermsModal()">Cancel</button>
                    <button type="submit" class="btn btn-primary">Save Changes</button>
                </div>
            </form>
        </div>

        <div class="policies-container">
            <h1 style="margin-bottom: 2%;">Data Privacy Policy</h1>
            <div class="policies-content">
                <?php if ($policies): ?>
                    <h2><?= htmlspecialchars($policies['title']) ?></h2>
                    <p><strong>Effective Date:</strong> <?= htmlspecialchars($formatted_policies_date) ?></p>
                    <p><?= nl2br(htmlspecialchars($policies['content'])) ?></p>
                    <button class="btn" onclick="openPoliciesModal()">Edit</button>
                <?php else: ?>
                    <p>No policies found in the database.</p>
                <?php endif; ?>
            </div>
        </div>

        <div class="modal-overlay" id="modalPoliciesOverlay" onclick="closePoliciesModal()"></div>

        <div class="modal-termsPolicies" id="editModalPolicies">
            <form action="update_policies.php" method="POST">
                <div class="modal-header">
                    <h5>Edit Policies</h5>
                    <button type="button" class="btn-close" onclick="closePoliciesModal()">&times;</button>
                </div>
                <div class="modal-body">
                    <input type="hidden" name="id" value="<?= $policies['id'] ?>">
                    <label for="policy_title">Title</label>
                    <input type="text" id="policy_title" name="title" value="<?= htmlspecialchars($policies['title']) ?>" required>
                    <label for="policy_effective_date">Effective Date</label>
                    <input type="date" id="policy_effective_date" name="effective_date" value="<?= htmlspecialchars($policies['effective_date']) ?>" required>
                    <label for="policy_content">Content</label>
                    <textarea id="policy_content" name="content" rows="5" required><?= htmlspecialchars($policies['content']) ?></textarea>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" onclick="closePoliciesModal()">Cancel</button>
                    <button type="submit" class="btn btn-primary">Save Changes</button>
                </div>
            </form>
        </div>

        <!-- Terms Confirmation Modal -->
        <div id="termsConfirmationModal" class="modal">
            <div class="modal-content-terms-data">
                <h2>Are you sure you want to update the Terms of Service?</h2>
                <div class="modal-buttons">
                    <button class="cancel-btn" id="cancelButtonTerms" onclick="closeModal('termsConfirmationModal')">Cancel</button>
                    <button class="update-btn" onclick="submitForm('termsForm')">Yes, update</button>
                </div>
            </div>
        </div>

        <!-- Privacy Confirmation Modal -->
        <div id="privacyConfirmationModal" class="modal">
            <div class="modal-content-terms-data">
                <h2>Are you sure you want to update the Privacy Policy?</h2>
                <div class="modal-buttons">
                    <button class="cancel-btn" id="cancelButtonData" onclick="closeModal('privacyConfirmationModal')">Cancel</button>
                    <button class="update-btn" onclick="submitForm('privacyForm')">Yes, update</button>
                </div>
            </div>
        </div>

        <!-- FAQS -->
        <div class="faqs-container-head">
            <h1>Frequently Asked Questions</h1>
            <button onclick="openFaqModal()">Add FAQ</button>
        </div>

        <div class="faq-modal-overlay" id="modalFaqOverlay"></div>
        <div class="faq-modal" id="faqModal">
            <div class="faq-modal-header">
                <h2>Add New FAQ</h2>
                <span class="faq-modal-close" onclick="closeFaqModal()">×</span>
            </div>
            <form action="add_faq.php" method="POST">
                <div class="faq-form-group">
                    <label for="question">Question:</label>
                    <input type="text" id="question" name="question" required>
                </div>
                <div class="faq-form-group">
                    <label for="answer">Answer:</label>
                    <textarea id="answer" name="answer" rows="4" required></textarea>
                </div>
                <div class="faq-modal-footer">
                    <button type="submit">Add FAQ</button>
                    <button type="button" onclick="closeFaqModal()">Cancel</button>
                </div>
            </form>
        </div>

        <div class="faqs-container">
            <div class="faqs-content">
                <ol>
                    <?php
                    if ($faqs) {
                        foreach ($faqs as $faq) {
                            $question = htmlspecialchars($faq['question'], ENT_QUOTES, 'UTF-8');
                            $answer = htmlspecialchars($faq['answer'], ENT_QUOTES, 'UTF-8');

                            // Safely encode for JavaScript
                            $js_question = json_encode($faq['question']);
                            $js_answer = json_encode($faq['answer']);

                            echo "<li><strong>{$question}</strong><p>{$answer}</p>
                            <button class='editBtn' onclick='openEditFaqModal({$faq['faq_id']}, {$js_question}, {$js_answer})'>Edit</button>
                            <button class='deleteBtn' onclick='confirmDeleteFaq({$faq['faq_id']})'>Delete</button></li>";
                        }
                    } else {
                        echo "<p>No FAQs found.</p>";
                    }
                    ?>
                </ol>
            </div>
        </div>

        <div class="faq-modal" id="editFaqModal">
            <div class="faq-modal-header">
                <h2>Edit FAQ</h2>
                <span class="faq-modal-close" onclick="closeEditFaqModal()">×</span>
            </div>
            <form action="update_faq.php" method="POST">
                <input type="hidden" id="editFaqId" name="faq_id"> <!-- Hidden field for FAQ ID -->
                <div class="faq-form-group">
                    <label for="editQuestion">Question:</label>
                    <input type="text" id="editQuestion" name="question" required>
                </div>
                <div class="faq-form-group">
                    <label for="editAnswer">Answer:</label>
                    <textarea id="editAnswer" name="answer" rows="4" required></textarea>
                </div>
                <div class="faq-modal-footer">
                    <button type="submit">Save Changes</button>
                    <button type="button" onclick="closeEditFaqModal()">Cancel</button>
                </div>
            </form>
        </div>

        <?php
        if ($result->num_rows > 0) {
            // Display the records in a table wrapped in a div container
            echo '<div class="company-container">';
            echo '<h1 style="margin-bottom: 1%;">Company Email</h1>';
            echo '<div class="company-content">';
            echo '<table border="1">';
            echo '<tr><th>Email</th><th>Company Name</th><th>Password</th><th>Actions</th></tr>';

            while ($row = $result->fetch_assoc()) {
                echo '<tr>';
                echo '<td>' . htmlspecialchars($row['email']) . '</td>';
                echo '<td>' . htmlspecialchars($row['company_name']) . '</td>';
                echo '<td>' . htmlspecialchars($row['password']) . '</td>';
                echo '<td><button onclick="openModalCompany(' . $row['id'] . ', \'' . htmlspecialchars($row['email'], ENT_QUOTES) . '\', \'' . htmlspecialchars($row['company_name'], ENT_QUOTES) . '\', \'' . htmlspecialchars($row['password'], ENT_QUOTES) . '\')">Edit</button></td>';
                echo '</tr>';
            }
            echo '</table>';
            echo '</div>'; // Closing the div container
            echo '</div>'; // Closing the div container
        } else {
            echo '<p>No company email records found.</p>';
        }
        ?>


        <!-- Modal Form -->
        <div class="modal-overlay-company" id="modalOverlayCompany"></div>
        <div class="modalCompany" id="modalFormCompany">
            <div class="modal-header">
                <h2>Edit Company Details</h2>
                <span class="close-modal btn-close" onclick="closeModalCompany()">X</span>
            </div>
            <form action="update_company_email.php" method="post" class="companyForm">
                <input type="hidden" name="id" id="modalIdCompany">
                <label for="email">Email:</label><br>
                <input type="email" name="email" id="modalEmailCompany" required><br><br>
                <label for="companyName">Company Name:</label><br>
                <input type="text" name="company_name" id="modalCompanyName" required><br><br>
                <label for="password">Password:</label><br>
                <input type="text" name="password" id="modalPasswordCompany" required><br><br>
                <div class="modal-footer">
                    <button type="submit">Save Changes</button>
                </div>
            </form>
        </div>

        <div class="backup-container">
            <h1 class="backup">Backup Management</h1>
            <form method="POST">
                <button type="submit" name="create_backup" class="upload-button" style="margin: 20px 0;">Create Backup</button>
            </form>
            <div class="backup-table-container">
                <table>
                    <thead>
                        <tr>
                            <th>Backup ID</th>
                            <th>File Path</th>
                            <th>Backup Date</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php
                        // Fetch backups from the database
                        $result = $conn->query("SELECT id, file_path, backup_date FROM backups ORDER BY backup_date DESC");
                        while ($row = $result->fetch_assoc()) {
                            echo "<tr>";
                            echo "<td>{$row['id']}</td>";
                            echo "<td>{$row['file_path']}</td>";
                            echo "<td>{$row['backup_date']}</td>";
                            echo "<td>
                                <form method='POST' style='display:inline;'>
                                    <input type='hidden' name='backup_id' value='{$row['id']}'>
                                    <button type='submit' name='restore_backup' class='upload-button'>Restore</button>
                                </form>
                                <form method='POST' style='display:inline;'>
                                    <input type='hidden' name='backup_id' value='{$row['id']}'>
                                    <button type='submit' name='delete_backup' class='upload-button red'>Delete</button>
                                </form>
                            </td>";
                            echo "</tr>";
                        }
                        ?>
                    </tbody>
                </table>
            </div>
        </div>
    </main>
    <script src="settings.js"></script>
</body>

</html>