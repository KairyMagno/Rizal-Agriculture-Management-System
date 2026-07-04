<?php
    session_start();
    require '../includes/db.php'; // Include the logo fetching function
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
    
    $module_name = 'announcement'; // Change as per module (news, articles, multimedia)
    $sql_increment_click = "UPDATE module_usage SET clicks = clicks + 1 WHERE module_name = ?";
    $stmt = $conn->prepare($sql_increment_click);
    $stmt->bind_param("s", $module_name);
    $stmt->execute();
    $stmt->close();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Rizal Agri Cultiva</title>
    <link rel="stylesheet" href="css/styles.css">
    <link rel="stylesheet" href="css/footer.css">
    <link rel="stylesheet" href="css/announcement.css">
    <link rel="stylesheet" href="css/carousel.css">
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
                  <li><a href="announcement.php" class="active">Announcements</a></li>
                  <li><a href="article.php">News & Articles</a></li>
                  <!-- Multimedia dropdown -->
                  <li class="dropdown">
                      <a href="multimedia.php" class="dropbtn"  class="active">Multimedia</a>
                      <ul class="dropdown-content" id="dropdown-container">

                      </ul>
                  </li>
                  <li><a href="about.php">About RAC</a></li>
                  <li><a href="contact.php">Contact Us</a></li>
                  <li><a href="faq.php">FAQ</a></li>
              </ul>
          </nav>
        </div>
    </header>
    <main>
        <!-- Carousel Section -->
        <section class="carousel-container">
            <div class="carousel">
                <?php
                $sql = "SELECT image_path, alt_text FROM carousel_images";
                $result = $conn->query($sql);

                if ($result->num_rows > 0) {
                    while ($row = $result->fetch_assoc()) {
                        echo '<div class="carousel-slide">';
                        echo '<img src="' . htmlspecialchars($row['image_path']) . '" alt="' . htmlspecialchars($row['alt_text']) . '" class="carousel-image">';
                        echo '</div>';
                    }
                } else {
                    echo '<p>No images available for the carousel.</p>';
                }
                ?>
            </div>
            <div class="carousel-dots">
                <?php
                if ($result->num_rows > 0) {
                    for ($i = 0; $i < $result->num_rows; $i++) {
                        echo '<span class="dot" onclick="setSlide(' . $i . ')"></span>';
                    }
                }
                ?>
            </div>
        </section>


      <h1 style="color: #338019; ">Announcement</h1>
      <hr />
       <!-- Announcement Section -->
        <section class="announcement-container">

        </section>
        <button id="load-more-btn">Load More</button>
        <hr style="margin: 5% 0;"/>

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
                <a href="https://www.facebook.com/wjames13" target="_blank"><i class="fa-brands fa-facebook fa-xl" style="color:white; margin-right: 2px;"></i></a>
                  <a href="https://x.com/kk_141529" target="_blank"><i class="fa-brands fa-square-x-twitter fa-xl" style="color:white; border-radius: 50%; margin-right: 2px;"></i></a>
                  <a href="https://www.instagram.com/getaway.james/" target="_blank"><i class="fa-brands fa-square-instagram fa-xl" style="color: white; border-radius: 50%; margin-right: 2px;"></i></a>
              </div>
              <p style="font-weight: 400; font-size: 16px; color: #F1F7F9; margin-top: 15%;">© RAC 2024</p>
          </div>
      </div>
  </footer>
  <script>
    let currentSlide = 0;
    const slides = document.querySelectorAll('.carousel-slide');
    const dots = document.querySelectorAll('.dot');
    const totalSlides = slides.length;

    function updateCarousel() {
        // Update slide position
        document.querySelector('.carousel').style.transform = `translateX(-${currentSlide * 100}%)`;

        // Update dot colors based on position
        dots.forEach((dot, index) => {
            if (index === currentSlide) {
                dot.className = 'dot active'; // Darkest green for the active dot
            } else if (index > currentSlide) {
                dot.className = `dot lighter-${index - currentSlide}`; // Lighter dots after the active one
            } else {
                dot.className = `dot lighter-${currentSlide - index}`; // Lighter dots before the active one
            }
        });
    }

    function autoSlide() {
        currentSlide = (currentSlide + 1) % totalSlides;
        updateCarousel();
    }

    function setSlide(slideIndex) {
        currentSlide = slideIndex;
        updateCarousel();
    }

    // Start the automatic slideshow
    let autoSlideInterval = setInterval(autoSlide, 5000);

    // Pause on hover
    document.querySelector('.carousel-container').addEventListener('mouseover', () => {
        clearInterval(autoSlideInterval);
    });
    document.querySelector('.carousel-container').addEventListener('mouseout', () => {
        autoSlideInterval = setInterval(autoSlide, 5000);
    });

    // Initialize
    updateCarousel();

    let announcementLimit = 10; // Initial limit
    let offset = 0; // Offset for the database query

    function loadAnnouncements() {
        fetch(`../includes/fetch_announcement.php?limit=${announcementLimit}&offset=${offset}`)
            .then(response => response.json())
            .then(data => {
                const announcementContainer = document.querySelector('.announcement-container');
                data.forEach(announcement => {
                    const announcementContentWithLineBreaks = announcement.content.replace(/\n/g, '<br>'); // Preserve line breaks
                    const announcementItem = `
                        <div class="announcement-item" data-id="${announcement.id}">
                            <table>
                                <tr>
                                    <td><a href="../includes/announcement_track_click.php?id=${announcement.id}&title=${encodeURIComponent(announcement.title)}&redirect=${encodeURIComponent(announcement.link)}" target="_blank" class="announcement-link"><img src="${announcement.image_url}" alt="${announcement.title}" class="image"></a></td>
                                    <td style="width: 100%">
                                        <div class="announcement-title-container">
                                            <a href="../includes/announcement_track_click.php?id=${announcement.id}&title=${encodeURIComponent(announcement.title)}&redirect=${encodeURIComponent(announcement.link)}" target="_blank" class="announcement-link"><h4 style="color:black; margin: 0;">${announcement.title}</h4></a>
                                        </div>
                                        <a href="../includes/announcement_track_click.php?id=${announcement.id}&title=${encodeURIComponent(announcement.title)}&redirect=${encodeURIComponent(announcement.link)}" target="_blank" class="announcement-link">
                                        <p>${announcementContentWithLineBreaks}</p>
                                        </a>
                                    </td>
                                </tr>
                            </table>
                        </div>
                    `;
                    announcementContainer.innerHTML += announcementItem;
                });

                if (data.length < announcementLimit) {
                    document.getElementById('load-more-btn').style.display = 'none'; // Hide the button if no more data
                }
            });
    }

    document.getElementById('load-more-btn').addEventListener('click', () => {
        offset += announcementLimit; // Increase offset by the limit to fetch the next batch
        loadAnnouncements();
    });
    
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
        loadAnnouncements();        // Then fetch multimedia
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
