<?php
    session_start();

    require '../includes/db.php'; // Make sure you have the db connection here
    require '../includes/logo.php'; // Include the logo fetching function

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

    $query = "SELECT slug, name FROM categories";
    $result = $conn->query($query);

    if ($result->num_rows > 0) {
        $categories = $result->fetch_all(MYSQLI_ASSOC);
    } else {
        $categories = [];
    }

    $module_name = 'multimedia'; // Change as per module (news, articles, multimedia)
    $sql_increment_click = "UPDATE module_usage SET clicks = clicks + 1 WHERE module_name = ?";
    $stmt = $conn->prepare($sql_increment_click);
    $stmt->bind_param("s", $module_name);
    $stmt->execute();
    $stmt->close();

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
    <title>Rizal Agri Cultiva</title>
    <link rel="stylesheet" href="css/styles.css">
    <link rel="stylesheet" href="css/footer.css">
    <link rel="stylesheet" href="css/multimedia.css">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Pirata+One&family=Poppins:ital,wght@0,100;0,200;0,300;0,400;0,500;0,600;0,700;0,800;0,900;1,100;1,200;1,300;1,400;1,500;1,600;1,700;1,800;1,900&display=swap" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css" rel="stylesheet">
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
                    <li class="dropdown">
                        <a href="multimedia.php" class="dropbtn active">Multimedia</a>
                        <ul class="dropdown-content" id="dropdown-container">

                        </ul>
                    </li>
                    <li><a href="about.php">About RAC</a></li>
                    <li><a href="contact.php" >Contact Us</a></li>
                    <li><a href="faq.php">FAQ</a></li>
                </ul>
            </nav>
        </div>
    </header>

    <main>

        <div id="categories-container">

        </div>
        <!-- Terms and Services Modal -->
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
            <div class="footer-left">
                <div class="footer-logo-text">
                    <img src="../assets/whiteLogo.png" alt="R Logo" class="footer-logo">
                    <div class="footer-text">
                        <p class="footer-title">RIZAL AGRI CULTIVA</p>
                        <p>Web Management System</p>
                        <div class="footer-links">
                            <span id="terms-link">Terms of Service</span> | 
                            <span id="privacy-link">Data Privacy Policy</span>
                        </div>
                    </div>
                </div>
            </div>
            <div class="footer-right">
                <p class="follow"0>FOLLOW US ON:</p>
                <div class="social-icons">
                    <a href="<?php echo htmlspecialchars($row['facebook']); ?>" target="_blank"><i class="fa-brands fa-facebook fa-lg" style="color:white; margin-right: 2px;"></i></a>
                    <a href="<?php echo htmlspecialchars($row['twitter']); ?>" target="_blank"><i class="fa-brands fa-square-x-twitter fa-lg" style="color:white; border-radius: 50%; margin-right: 2px;"></i></a>
                    <a href="<?php echo htmlspecialchars($row['instagram']); ?>" target="_blank"><i class="fa-brands fa-square-instagram fa-lg" style="color: white; border-radius: 50%; margin-right: 2px;"></i></a>
                </div>
                <p style="font-weight: 400; font-size: 16px; color: #F1F7F9; margin-top: 15%;">© RAC 2024</p>
            </div>
        </div>
    </footer>
    <script>
        async function fetchCategories() {
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
                        <h2 style="color: #338019; font-size: 28px;">${category.name}</h2>
                        <section id="${category.slug}" class="category-container"></section>
                    `;
                    container.innerHTML += categoryHtml;
                });
            } catch (error) {
                console.error('Error fetching categories:', error);
            }
        }

        function loadMultimedia() {
            fetch('../includes/fetch_multimedia.php')
                .then(response => response.json())
                .then(data => {
                    document.querySelectorAll('.category-container').forEach(section => section.innerHTML = '');

                    // Group items by category
                    const groupedItems = {};
                    data.forEach(item => {
                        const categoryId = item.category.toLowerCase().replace(/\s+/g, '-');
                        if (!groupedItems[categoryId]) {
                            groupedItems[categoryId] = [];
                        }
                        groupedItems[categoryId].push(item);
                    });

                    // Display only the first 10 items per category
                    for (const category in groupedItems) {
                        const categorySection = document.getElementById(category);
                        if (categorySection) {
                            groupedItems[category].slice(0, 10).forEach(item => {
                                const itemHTML = `
                                    <div class="category-item" data-id="${item.id}">
                                        <a href="${item.link}" data-id="${item.id}" target="_blank" onclick="trackClick(${item.id})">
                                            <img src="${item.image_url}" alt="${item.title}" class="category-img">
                                        </a>
                                        <div class="category-content">
                                            <h3><a href="${item.link}" data-id="${item.id}" target="_blank" onclick="trackClick(${item.id})">${item.title}</a></h3>
                                            <p><a href="${item.link}" data-id="${item.id}" target="_blank" style="color: #555;" onclick="trackClick(${item.id})">${item.content}</a></p>
                                        </div>
                                    </div>
                                `;
                                categorySection.innerHTML += itemHTML;
                            });

                            // Add "Load More" button if there are more than 10 items
                            if (groupedItems[category].length > 10) {
                                const loadMoreButton = document.createElement('button');
                                loadMoreButton.textContent = 'Load More';
                                loadMoreButton.classList.add('load-more-btn');
                                loadMoreButton.setAttribute('data-category', category);
                                loadMoreButton.setAttribute('data-offset', 10);
                                loadMoreButton.addEventListener('click', function() {
                                    loadMoreItems(category, groupedItems[category]);
                                    this.style.display = 'none'; // Hide the button after clicking
                                });
                                categorySection.appendChild(loadMoreButton);
                            }
                        }
                    }
                })
                .catch(error => console.error('Error fetching multimedia:', error));
        }

        // Function to load more items for a given category
        function loadMoreItems(category, allItems) {
            const categorySection = document.getElementById(category);
            if (categorySection) {
                const currentItems = categorySection.querySelectorAll('.category-item');
                const startIndex = currentItems.length;
                const endIndex = Math.min(startIndex + 10, allItems.length);

                for (let i = startIndex; i < endIndex; i++) {
                    const item = allItems[i];
                    const itemHTML = `
                        <div class="category-item" data-id="${item.id}">
                            <a href="${item.link}" data-id="${item.id}" target="_blank" onclick="trackClick(${item.id})">
                                <img src="${item.image_url}" alt="${item.title}" class="category-img">
                            </a>
                            <div class="category-content">
                                <h3><a href="${item.link}" data-id="${item.id}" target="_blank" onclick="trackClick(${item.id})">${item.title}</a></h3>
                                <p><a href="${item.link}" data-id="${item.id}" target="_blank" style="color: #555;" onclick="trackClick(${item.id})">${item.content}</a></p>
                            </div>
                        </div>
                    `;
                    categorySection.innerHTML += itemHTML;
                }

                // Check if there are more items to show; if not, remove the button
                if (endIndex >= allItems.length) {
                    const loadMoreButton = categorySection.querySelector('.load-more-btn');
                    if (loadMoreButton) {
                        loadMoreButton.style.display = 'none';
                    }
                }
            }
        }

        document.addEventListener('DOMContentLoaded', () => {
            // Check if there's a hash in the URL
            if (window.location.hash) {
                const targetElement = document.querySelector(window.location.hash);
                if (targetElement) {
                    // Scroll into view
                    targetElement.scrollIntoView({ behavior: 'smooth' });
                }
            }
        });

        // Function to track click with JavaScript before navigating
        function trackClick(itemId) {
            fetch(`../includes/multimedia_track_clicks.php?item_id=${itemId}`, {
                method: 'GET',
                headers: {
                    'Content-Type': 'application/x-www-form-urlencoded'
                }
            }).catch(err => console.error('Error tracking click:', err));
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
            await fetchCategories();  // Ensure categories are fetched first
            loadMultimedia();        // Then fetch multimedia
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
