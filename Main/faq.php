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

    // Fetch FAQ data
    $faqQuery = "SELECT question, answer FROM faqs";
    $faqResult = $conn->query($faqQuery);
    $faqs = [];
    if ($faqResult->num_rows > 0) {
        while ($faq = $faqResult->fetch_assoc()) {
            $faqs[] = $faq;
        }
    } else {
        $faqs = []; // No FAQs available
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
                  <li><a href="contact.php">Contact Us</a></li>
                  <li><a href="faq.php" class="active">FAQ</a></li>
              </ul>
          </nav>
        </div>
    </header>
    <main>
      <section class="faq-section">
          <h1>Frequently Asked Questions</h1>
          <div class="accordion">
              <?php foreach ($faqs as $faq): ?>
                  <div class="accordion-item">
                      <button class="accordion-title">
                          <?= htmlspecialchars($faq['question']); ?>
                          <span class="arrow"></span>
                      </button>
                      <div class="accordion-content">
                          <p><?= nl2br(htmlspecialchars($faq['answer'])); ?></p>
                      </div>
                  </div>
              <?php endforeach; ?>
          </div>
      </section>
  </main>

  <style>
      .faq-section {
          max-width: 800px;
          margin: 20px auto;
          padding: 20px;
      }

      .faq-section h1{
          color: #338019;
          font-size: 32px;
      }

      .accordion {
          border: 1px solid #ccc;
          border-radius: 8px;
          overflow: hidden;
      }

      .accordion-item {
          border-bottom: 1px solid #ccc;
      }
      
      .accordion-item button{
          font-weight: bold;
          font-size: 18px;
      }

      .accordion-item:last-child {
          border-bottom: none;
      }

      .accordion-title {
          background-color: #338019;
          color: white;
          cursor: pointer;
          padding: 15px;
          text-align: left;
          font-size: 16px;
          border: none;
          outline: none;
          width: 100%;
          display: flex;
          justify-content: space-between;
          align-items: center;
          transition: background-color 0.3s;
      }

      .accordion-title:hover {
          background-color: #33801999;
      }

      .accordion-content {
          display: none;
          padding: 15px;
          background-color: #fff;
      }

      .accordion-content p {
          margin: 0;
      }

      .arrow {
          display: inline-block;
          width: 12px;
          height: 12px;
          border: solid white;
          border-width: 0 2px 2px 0;
          transform: rotate(45deg);
          transition: transform 0.3s;
      }

      .accordion-title.active .arrow {
          transform: rotate(225deg); /* Rotate the arrow up */
      }
  </style>

  <script>
      document.addEventListener("DOMContentLoaded", function () {
          const accordionTitles = document.querySelectorAll(".accordion-title");

          accordionTitles.forEach((title) => {
              title.addEventListener("click", function () {
                  const content = this.nextElementSibling;

                  // Close other accordion items
                  document.querySelectorAll(".accordion-content").forEach((otherContent) => {
                      if (otherContent !== content) {
                          otherContent.style.display = "none";
                          otherContent.previousElementSibling.classList.remove("active");
                      }
                  });

                  // Toggle current accordion item
                  if (content.style.display === "block") {
                      content.style.display = "none";
                      this.classList.remove("active");
                  } else {
                      content.style.display = "block";
                      this.classList.add("active");
                  }
              });
          });
      });
  </script>
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

    </script>
</body>
</html>
