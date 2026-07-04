<?php
    require '../includes/logo.php'; // Include the logo fetching function
    require '../includes/db.php'; 
    session_start();
    $termsQuery = "SELECT title, effective_date, content FROM terms";
    $termsResult = $conn->query($termsQuery);
    if ($termsResult->num_rows > 0) {
        $terms = $termsResult->fetch_assoc();
        $termsTitle = $terms['title'];
        $termsDate = (new DateTime($terms['effective_date']))->format('F j, Y');
        $termsContent = $terms['content'];
    } else {
        $termsTitle = "Terms of Service";
        $termsDate = "N/A";
        $termsContent = "Terms not available.";
    }

    // Fetch Privacy Policy
    $privacyQuery = "SELECT title, effective_date, content FROM policies";
    $privacyResult = $conn->query($privacyQuery);
    if ($privacyResult->num_rows > 0) {
        $privacy = $privacyResult->fetch_assoc();
        $privacyTitle = $privacy['title'];
        $privacyDate = (new DateTime($privacy['effective_date']))->format('F j, Y');
        $privacyContent = $privacy['content'];
    } else {
        $privacyTitle = "Privacy Policy";
        $privacyDate = "N/A";
        $privacyContent = "Privacy policy not available.";
    }

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
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Rizal Agri Cultiva - Articles</title>
<link rel="stylesheet" href="css/styles.css">
<link rel="stylesheet" href="css/footer.css">
<link rel="stylesheet" href="css/about.css">
<link rel="preconnect" href="https://fonts.googleapis.com">
<link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
<link href="https://fonts.googleapis.com/css2?family=Pirata+One&family=Poppins:ital,wght@0,100;0,200;0,300;0,400;0,500;0,600;0,700;0,800;0,900;1,100;1,200;1,300;1,400;1,500;1,600;1,700;1,800;1,900&display=swap" rel="stylesheet">
<link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css" rel="stylesheet">
<style>
    .contact-form {
        margin: 5% auto;
        padding: 20px;
        background-color: #f9f9f9;
        border: 1px solid #ccc;
        border-radius: 8px;
        max-width: 1200px;
        box-shadow: 0 4px 6px 6px rgba(0, 0, 0, 0.1);
    }

    .contact-us-form label {
        font-weight: bold;
        display: block;
        margin-bottom: 8px;
    }

    .contact-us-form input,
    .contact-us-form textarea, 
    .contact-us-form select {
        width: 100%;
        padding: 10px;
        margin-bottom: 15px;
        border: 1px solid #ccc;
        border-radius: 4px;
        box-sizing: border-box;
    }

    .btn-submit {
        background-color: #338019;
        color: white;
        border: none;
        padding: 10px 20px;
        font-size: 16px;
        cursor: pointer;
        border-radius: 4px;
        transition: background-color 0.3s;
    }

    .btn-submit:hover {
        background-color: #2a6c15;
    }
</style>
</head>
<body>
<header>
    <div class="header-container">
        <div class="logo-section">
            <img src="<?= getLogoPath($conn) ?>" alt="AgriWeb Logo" class="logo">
            <div class="logo-text">
                <span class="line">Rizal</span>
                <span class="line">Agri</span>
                <span class="line">Cultiva</span>
            </div>
        </div>
    </div>
    <div class="navbar-container">
        <nav class="navbar">
            <ul>
                <li><a href="announcement.php">Announcements</a></li>
                <li><a href="article.php">News & Articles</a></li>
                <!-- Multimedia dropdown -->
                <li class="dropdown">
                    <a href="multimedia.php" class="dropbtn">Multimedia</a>
                    <ul class="dropdown-content" id="dropdown-container">

                    </ul>
                </li>
                <li><a href="about.php">About RAC</a></li>
                <li><a href="contact.php" class="active">Contact Us</a></li>
                <li><a href="faq.php">FAQ</a></li>
            </ul>
        </nav>
    </div>
</header>
<main>
<div class="contact-container">
    <h1 style="margin: 21.440px 0; font-size: 32px; color: #338019;">Contact Us</h1>
    <hr style="margin: 0; margin-bottom:2%;"/>

    <section class="map">
        <h2 style="margin-bottom: 10px;">Antipolo Agricultural Map</h2>
        <?php if (!empty($aboutData['image'])): ?>
            <a href="<?= htmlspecialchars($aboutData['map_link']) ?>" target="_blank"><img src="<?= htmlspecialchars($aboutData['image']) ?>" alt="Antipolo Agricultural Map" style="width: 100%; "></a>
        <?php else: ?>
            <p>Map image not available.</p>
        <?php endif; ?>
    </section>
    <?php
        if (isset($_GET['status']) && $_GET['status'] == 'success') {
            echo "<script>alert('Your message was sent successfully!');</script>";
        }
    ?>
    <section class="contact-form">
        <h2 style="margin: 5px 0; margin-bottom: 20px; text-align: center;">Send Questions to our Email</h2>
        <form action="process_contact.php" method="POST" class="contact-us-form">
            <div class="select-group">
                <label for="role">Please choose one. <span class="required">*</span></label>
                <select id="role" name="role" required>
                    <option value="" disabled selected>Select your role</option>
                    <option value="Farmer">Farmer</option>
                    <option value="Businessman">Businessman</option>
                    <option value="Student">Student</option>
                    <option value="Researcher">Researcher</option>
                    <option value="Other">Other</option>
                </select>
            </div> 
            <label for="first_name">First Name*</label>
            <input type="text" id="first_name" name="first_name" required>

            <label for="last_name">Last Name*</label>
            <input type="text" id="last_name" name="last_name" required>

            <label for="address">Address/Location*</label>
            <input type="text" id="address" name="address" required>

            <label for="email">Email Address*</label>
            <input type="email" id="email" name="email" required>

            <label for="message">Message*</label>
            <textarea id="message" name="message" rows="5" required></textarea>

            <button type="submit" class="btn-submit">Send Message</button>
        </form>
    </section>
</div>
    <div id="terms-modal" class="modal">
        <div class="modal-content">
            <button class="close-btn-box" onclick="closeModal()">←</button>
            <div class="modal-header">
                <img src="../assets/Heaader.png" alt="Rizal Agriculture Logo" class="modal-header-img" />
            </div>
            <div class="modal-text">
                <h2><?php echo $termsTitle; ?></h2>
                <p><strong>Effective Date:</strong> <?php echo $termsDate; ?></p>
                <p><?php echo nl2br($termsContent); ?></p>
            </div>
        </div>
    </div>

    <!-- Data Privacy Act Modal -->
    <div id="data-privacy-modal" class="modal">
        <div class="modal-content">
            <button class="close-btn-box" onclick="closeDataPrivacyModal()">←</button>
            <div class="modal-header">
                <img src="../assets/Heaader.png" alt="Rizal Agriculture Logo" class="modal-header-img" />
            </div>
            <div class="modal-text">
                <h2><?php echo $privacyTitle; ?></h2>
                <p><strong>Effective Date:</strong> <?php echo $privacyDate; ?></p>
                <p><?php echo nl2br($privacyContent); ?></p>
            </div>
        </div>
    </div>
</main>
<footer class="footer">
    <div class="footer-container">
        <!-- Left Section -->
        <div class="footer-left">
            <div class="footer-logo-text">
                <img src="../assets/whiteLogo.png" alt="R Logo" class="footer-logo">
                <div class="footer-text">
                    <p class="footer-title" style="font-weight: bold; font-size: 28px; color: #F1F7F9; letter-spacing: 3px;">RIZAL AGRI CULTIVA</p>
                    <p style="font-weight: 300; font-size: 16px; color: #F1F7F9;">Web Management System</p>
                    <div class="footer-links">
                        <span id="terms-link">Terms of Service</span> | 
                        <span id="privacy-link">Data Privacy Policy</span>
                    </div>
                </div>
            </div>
        </div>

        <!-- Right Section -->
        <div class="footer-right">
            <p class="follow"0>FOLLOW US ON:</p>            
            <div class="social-icons">
                <a href="<?php echo htmlspecialchars($row['facebook']); ?>" target="_blank"><i class="fa-brands fa-facebook fa-xl" style="color: white; margin-right: 2px;"></i></a>
                <a href="<?php echo htmlspecialchars($row['twitter']); ?>" target="_blank"><i class="fa-brands fa-square-x-twitter fa-xl" style="color: white; border-radius: 50%; margin-right: 2px;"></i></a>
                <a href="<?php echo htmlspecialchars($row['instagram']); ?>" target="_blank"><i class="fa-brands fa-square-instagram fa-xl" style="color: white; border-radius: 50%; margin-right: 2px;"></i></a>
            </div>
            <p style="font-weight: 400; font-size: 16px; color: #F1F7F9; margin-top: 15%;">© RAC 2024</p>
        </div>
    </div>
</footer>

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

async function init() {
    await fetchCategoriesNav();
}

init();

    // Open the modal
    document.querySelector(".footer-links span").addEventListener("click", function(event) {
        event.preventDefault();
        document.getElementById("terms-modal").style.display = "flex";
    });

    // Close the modal
    function closeModal() {
        document.getElementById("terms-modal").style.display = "none";
    }

    // Open the Data Privacy Act Modal
    document.getElementById("privacy-link").addEventListener("click", function(event) {
        event.preventDefault();
        document.getElementById("data-privacy-modal").style.display = "flex";
    });

    // Close the Data Privacy Act Modal
    function closeDataPrivacyModal() {
        document.getElementById("data-privacy-modal").style.display = "none";
    }
</script>

</body>
</html>
