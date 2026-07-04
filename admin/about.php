<?php
session_start();
include('../includes/db.php');
require '../includes/logo.php'; // Include the logo fetching function

// Fetch the data from the 'aboutus' table
$query = "SELECT introduction, general_information, vision, mission, image, map_link FROM aboutus LIMIT 1";
$result = mysqli_query($conn, $query);

// Check if any row is returned
if ($result && mysqli_num_rows($result) > 0) {
    $aboutData = mysqli_fetch_assoc($result);
} else {
    // If no data found, set default values
    $aboutData = [
        'introduction' => 'No introduction available.',
        'general_information' => 'No general information available.',
        'vision' => 'No vision available.',
        'mission' => 'No mission available.',
        'image' => '',
        'map_link' => ''
    ];
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
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>About RAC</title>
    <link rel="stylesheet" href="css/styles.css">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Pirata+One&family=Poppins:ital,wght@0,100;0,200;0,300;0,400;0,500;0,600;0,700;0,800;0,900&display=swap"
        rel="stylesheet">
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css" rel="stylesheet">
    <link rel="stylesheet" href="css/header.css">
    <link rel="stylesheet" href="css/sidebar.css">
    <style>
        .content{
          background-color: #ffffff;
          border-radius: 8px;
          box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1);
          margin-bottom: 20px;
          padding: 2% 5%;
          border-left: 5px solid #4CAF50; /* Green border */
        }
        /* Overlay */
        .overlay {
            display: none; /* Hidden by default */
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background-color: rgba(0, 0, 0, 0.7); /* Dark overlay with opacity */
            z-index: 2; /* Place it behind the modal */
        }

        /* Modal container styling */
        .modal {
            display: none; /* Hidden by default */
            position: fixed;
            z-index: 3; /* On top of the overlay */
            left: 50%;
            top: 50%;
            transform: translate(-50%, -50%);
            width: 70%; /* Adjust width as needed */
            max-width: 800px; /* Optional: set a max-width */
            height: 80vh;
            background-color: #fefefe;
            border-radius: 5px;
            overflow: auto; /* Allows scrolling if content overflows */
            box-shadow: 0 2px 10px rgba(0, 0, 0, 0.2); /* Optional shadow */
            padding: 20px;
        }

        /* Close button */
        .close-btn {
            color: #aaa;
            float: right;
            font-size: 28px;
            font-weight: bold;
        }

        .close-btn:hover,
        .close-btn:focus {
            color: black;
            text-decoration: none;
            cursor: pointer;
        }

        /* Input fields */
        textarea, input[type="text"] {
            width: 100%;
            padding: 10px;
            margin: 10px 0;
            border: 1px solid #ccc;
            border-radius: 4px;
            box-sizing: border-box; /* Ensures padding and border are included in the element's total width */
        }

        textarea{
          height: 100px;
        }
        
        input[type="file"]{
          display: block;
          width: 100%;
          padding: 10px;
          margin: 10px 0;
          border: 1px solid #ccc;
          border-radius: 4px;
          box-sizing: border-box; /* Ensures padding and border are included in the element's total width */
        }

        /* Optional: styling for the submit button */
        .submit-btn {
            background-color: #338019;
            color: white;
            padding: 10px 20px;
            border: none;
            border-radius: 4px;
            cursor: pointer;
        }

        .submit-btn:hover {
            background-color: #45a049;
        }

        .edit-btn{
            background-color: #338019;
            color: white;
            padding: 10px 20px;
            border: none;
            border-radius: 4px;
            cursor: pointer;
            width: 100%;
            margin: 5% auto;
            font-size: 16px;
        }
    </style>
</head>

<body>
    <!-- Sidebar -->
    <div class="sidebar">
        <div class="header-title">
            <img src="<?= getLogoPath($conn) ?>" alt="AgriWeb Logo" class="header-logo">
            <div class="logo-text">
                <h1 style="color:black; margin: 0; font-size: 18px; letter-spacing: 1px; font-weight: 500; margin-top: 10px;">RIZAL</h1>
                <h1 style="color:black; margin: 0; font-size: 18px; letter-spacing: 1px; font-weight: 500; margin-top: -5px;">AGRI</h1>
                <h1 style="color:black; margin: 0; font-size: 18px; letter-spacing: 1px; font-weight: 500; margin-top: -5px;">CULTIVA</h1>
            </div>
        </div>
        <ul class="nav-links">
            <li><a href="announcement.php"><i class="fas fa-bullhorn"></i> Announcements</a></li>
            <li><a href="articles.php"><i class="fas fa-file-alt"></i> News & Articles</a></li>
            <li class="dropdown">
                <a href="multimedia.php" class="dropbtn"><i class="fas fa-camera"></i> Multimedia</a>
                <ul class="dropdown-content" id="dropdown-container"></ul>
            </li>
            <li><a href="about.php" class="active"><i class="fas fa-file"></i> About RAC</a></li>
            <li><a href="email.php"><i class="fas fa-envelope"></i> Email Inquiry</a></li>
            <li style="margin-bottom: 5px;"><a href="archive.php"><i class="fas fa-archive"></i> Archive</a></li> <!-- Archive added here -->

            <?php if (!$hide_admin_elements): ?>
                <p style="color:gray; margin: 0;">Admin</p>
                <li><a href="dashboard.php"><i class="fas fa-tachometer-alt"></i> Dashboard</a></li>
                <li><a href="reports.php"><i class="fas fa-chart-line"></i> Reports</a></li>
                <li><a href="user.php"><i class="fas fa-users"></i> User Management</a></li>
                <li style="margin-bottom: 0;"><a href="settings.php"><i class="fas fa-cogs"></i> Settings</a></li>
            <?php endif; ?>
        </ul>
        <button class="logout-btn" style="text-align:center;" onclick="logout()">
            <i class="fas fa-sign-out-alt logout-icon" style="margin-left:-20px;"></i> Logout
        </button>
    </div>

    <main>
        <div class="header">
            <h1 style="color:green; font-size:28px;">About RAC</h1>
        </div>
        <div class="container"  style="padding: 5% 10%;">
          <div class="content">
              <!-- Display Introduction -->
              <section class="introduction">
                  <h2>Introduction</h2>
                  <p><?= nl2br(htmlspecialchars($aboutData['introduction'])) ?></p>
              </section>

              <!-- Display General Information -->
              <section class="general-information">
                  <h2>General Information</h2>
                  <p><?= nl2br(htmlspecialchars($aboutData['general_information'])) ?></p>
              </section>

              <!-- Display Vision -->
              <section class="vision">
                  <h2>Our Vision</h2>
                  <p><?= nl2br(htmlspecialchars($aboutData['vision'])) ?></p>
              </section>

              <!-- Display Mission -->
              <section class="mission">
                  <h2>Our Mission</h2>
                  <p><?= nl2br(htmlspecialchars($aboutData['mission'])) ?></p>
              </section>

              <!-- Display Map -->
              <section class="map">
                  <h2>Antipolo Agricultural Map</h2>
                  <?php if (!empty($aboutData['image'])): ?>
                      <a href="<?= htmlspecialchars($aboutData['map_link']) ?>" target="_blank"><img src="<?= htmlspecialchars($aboutData['image']) ?>" alt="Antipolo Agricultural Map" style="width: 100%; "></a>
                  <?php else: ?>
                      <p>Map image not available.</p>
                  <?php endif; ?>
              </section>

              <!-- Edit Button -->
              <button id="editButton" class="edit-btn">Edit</button>
          </div>
        </div>

        <!-- Overlay -->
        <div id="overlay" class="overlay"></div>

        <!-- Modal -->
        <div id="editModal" class="modal">
            <div class="modal-content">
                <span class="close-btn" onclick="closeModal()">&times;</span>
                <h2>Edit About RAC</h2>
                <form action="update_aboutus.php" method="POST" enctype="multipart/form-data">
                    <div>
                        <input type="checkbox" id="toggle_introduction" onclick="toggleVisibility('introduction')">
                        <label for="toggle_introduction">Show/Hide Introduction</label>
                        <label for="introduction">Introduction:</label>
                        <textarea id="introduction" name="introduction" style="display:none;"><?= htmlspecialchars($aboutData['introduction']) ?></textarea>
                    </div>
                    <div>
                        <input type="checkbox" id="toggle_general_information" onclick="toggleVisibility('general_information')">
                        <label for="toggle_general_information">Show/Hide General Information</label>
                        <label for="general_information">General Information:</label>
                        <textarea id="general_information" name="general_information" style="display:none;"><?= htmlspecialchars($aboutData['general_information']) ?></textarea>
                    </div>
                    <div>
                        <input type="checkbox" id="toggle_vision" onclick="toggleVisibility('vision')">
                        <label for="toggle_vision">Show/Hide Vision</label>
                        <label for="vision">Vision:</label>
                        <textarea id="vision" name="vision" style="display:none;"><?= htmlspecialchars($aboutData['vision']) ?></textarea>
                    </div>
                    <div>
                        <input type="checkbox" id="toggle_mission" onclick="toggleVisibility('mission')">
                        <label for="toggle_mission">Show/Hide Mission</label>
                        <label for="mission">Mission:</label>
                        <textarea id="mission" name="mission" style="display:none;"><?= htmlspecialchars($aboutData['mission']) ?></textarea>
                    </div>
                    <div>
                        <input type="checkbox" id="toggle_image" onclick="toggleVisibility('image_div')">
                        <label for="toggle_image">Show/Hide Upload Image</label>
                        <div id="image_div" style="display:none;">
                            <label for="image">Upload Image:</label>
                            <input type="file" id="image" name="image">
                        </div>
                    </div>
                    <div>
                        <input type="checkbox" id="toggle_map_link" onclick="toggleVisibility('map_link_div')">
                        <label for="toggle_map_link">Show/Hide Map Link</label>
                        <div id="map_link_div" style="display:none;">
                            <label for="map_link">Map Link:</label>
                            <input type="text" id="map_link" name="map_link" value="<?= htmlspecialchars($aboutData['map_link']) ?>">
                        </div>
                    </div>
                    <button type="submit" class="submit-btn">Submit</button>
                </form>
            </div>
        </div>

        <script>
            function toggleVisibility(fieldId) {
                const field = document.getElementById(fieldId);
                if (field.style.display === "none") {
                    field.style.display = "block";
                } else {
                    field.style.display = "none";
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

            const overlay = document.getElementById('overlay');
            const modal = document.getElementById('editModal');
            const editButton = document.getElementById('editButton');

            editButton.addEventListener('click', function() {
                modal.style.display = 'block';
                overlay.style.display = 'block';
            });

            function closeModal() {
                modal.style.display = 'none';
                overlay.style.display = 'none';
            }

            function logout() {
                // Redirect to the logout PHP script
                window.location.href = '../includes/logout.php'; // Ensure this path matches the location of logout.php
            }
        </script>
    </main>
</body>

</html>
