<?php
    session_start();
    require '../includes/logo.php'; // Include the logo fetching function
    require '../includes/db.php'; 
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

    $module_name = 'news & articles'; // Change as per module (news, articles, multimedia)
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
    <title>Rizal Agri Cultiva - Articles</title>
    <link rel="stylesheet" href="css/styles.css">
    <link rel="stylesheet" href="css/footer.css">
    <link rel="stylesheet" href="css/article.css">
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
                  <li><a href="article.php" class="active">News & Articles</a></li>
                  <!-- Multimedia dropdown -->
                  <li class="dropdown">
                      <a href="multimedia.php" class="dropbtn">Multimedia</a>
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
      <h1 style="color: #338019;">News & Articles</h1>
      <hr />
      <section class="articles-container">
    <!-- This is where the articles will go -->
    </section>
    <button id="load-more" style="display: none;">Load More</button>

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
             <p class="follow">FOLLOW US ON:</p>              
             <div class="social-icons">
                <a href="<?php echo htmlspecialchars($row['facebook']); ?>" target="_blank"><i class="fa-brands fa-facebook fa-xl" style="color:white; margin-right: 2px;"></i></a>
                <a href="<?php echo htmlspecialchars($row['twitter']); ?>" target="_blank"><i class="fa-brands fa-square-x-twitter fa-xl" style="color:white; border-radius: 50%; margin-right: 2px;"></i></a>
                <a href="<?php echo htmlspecialchars($row['instagram']); ?>" target="_blank"><i class="fa-brands fa-square-instagram fa-xl" style="color: white; border-radius: 50%; margin-right: 2px;"></i></a>
              </div>
              <p style="font-weight: 400; font-size: 16px; color: #F1F7F9; margin-top: 15%;">© RAC 2024</p>
          </div>
      </div>
  </footer>

  <script>
    // Function to load articles
    let articlesLoaded = 0;  // To track how many articles have been loaded
    let totalArticles = 0;  // Will store total count from database
    const articlesPerLoad = 10;  // Number of articles to load per click

    // Function to load articles
    function loadArticles() {
        fetch(`../includes/fetch_article.php?start=${articlesLoaded}&limit=${articlesPerLoad}`)
            .then(response => response.json())
            .then(data => {
                const articlesContainer = document.querySelector('.articles-container');
                const loadMoreBtn = document.getElementById('load-more');
                
                // Check if the response includes total count
                if (data.total !== undefined) {
                    totalArticles = data.total;
                }
                
                // Handle both old and new response formats
                const articles = data.articles || data;
                
                if (articles.length > 0) {
                    articles.forEach(article => {
                        const contentWithLineBreaks = article.content.replace(/\n/g, '<br>');
                        
                        // Create article HTML structure
                        const articleItem = `
                        <div class="article-item">
                            <div class="article-image">
                                <img src="${article.image_url}" alt="${article.title}">
                            </div>
                            <div class="article-summary">
                                <h3>${article.title}</h3>
                                <p style="color: #2f6c2f;">${article.formatted_date} by ${article.author}</p>
                                <p class="short-summary">${article.content.substring(0, 300)}...</p>
                                <p class="full-summary" style="display:none;">${contentWithLineBreaks}</p>
                                <span class="read-more-text">Read More</span>
                            </div>
                        </div>`;
                        articlesContainer.innerHTML += articleItem;
                    });

                    // Update the number of articles loaded
                    articlesLoaded += articles.length;

                    // Attach the Read More functionality
                    attachReadMoreListeners();
                    
                    // Show/hide Load More button based on whether there are more articles
                    if (totalArticles > 0 && articlesLoaded >= totalArticles) {
                        loadMoreBtn.style.display = 'none';
                    } else if (articles.length < articlesPerLoad) {
                        // If we received fewer articles than requested, we've reached the end
                        loadMoreBtn.style.display = 'none';
                    } else {
                        loadMoreBtn.style.display = 'block';
                    }
                } else {
                    loadMoreBtn.style.display = 'none';
                }
            })
            .catch(error => {
                console.error('Error fetching articles:', error);
                document.getElementById('load-more').style.display = 'none';
            });
    }

    // Add event listener for the "Load More" button
    document.getElementById('load-more').addEventListener('click', loadArticles);

    // Function to toggle Read More / Read Less functionality
    function attachReadMoreListeners() {
        document.querySelectorAll('.read-more-text').forEach(link => {
            link.addEventListener('click', async event => {
                const articleItem = event.target.closest('.article-item');
                const shortParagraph = articleItem.querySelector('.short-summary');
                const fullParagraph = articleItem.querySelector('.full-summary');
                const articleTitle = articleItem.querySelector('h3').innerText;

                // Toggle visibility of the paragraphs
                shortParagraph.style.display = shortParagraph.style.display === 'none' ? 'block' : 'none';
                fullParagraph.style.display = fullParagraph.style.display === 'none' ? 'block' : 'none';

                // Change text of the link
                event.target.innerText = fullParagraph.style.display === 'block' ? 'Read Less' : 'Read More';

                // Send an AJAX request to log the click in the database
                try {
                    const response = await fetch('../includes/articles_track_click.php', {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/x-www-form-urlencoded'
                        },
                        body: `title=${encodeURIComponent(articleTitle)}`
                    });

                    const result = await response.json();

                    if (!response.ok || result.status !== 'success') {
                        console.error('Failed to update click count:', result.message);
                    }
                } catch (error) {
                    console.error('Error logging click:', error);
                }
            });
        });
    }

    // Initialize the articles when the page is loaded
    async function fetchCategoriesNav() {
        try {
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
        loadArticles();        // Then fetch multimedia
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