<?php
    require 'includes/db.php'; // Include the logo fetching function
  // You can define the background image URL here
  $backgroundImage = "assets/landingBG.png"; // Change this to the path of your background image
  $logoImage = "assets/LogoLP.png";  // Change this to the path of your logo image

  $currentMonth = date('m');
  $currentYear = date('Y');

  // Check if there's an entry for the current month and year
  $sql_check = "SELECT id, visit_count FROM visitor_growth WHERE month = ? AND year = ?";
  $stmt = $conn->prepare($sql_check);
  $stmt->bind_param('ii', $currentMonth, $currentYear);
  $stmt->execute();
  $result = $stmt->get_result();

  if ($result->num_rows > 0) {
      // Update existing record
      $row = $result->fetch_assoc();
      $newCount = $row['visit_count'] + 1;
      $sql_update = "UPDATE visitor_growth SET visit_count = ? WHERE id = ?";
      $stmt_update = $conn->prepare($sql_update);
      $stmt_update->bind_param('ii', $newCount, $row['id']);
      $stmt_update->execute();
  } else {
      // Insert new record
      $sql_insert = "INSERT INTO visitor_growth (visit_date, month, year, visit_count) VALUES (NOW(), ?, ?, 1)";
      $stmt_insert = $conn->prepare($sql_insert);
      $stmt_insert->bind_param('ii', $currentMonth, $currentYear);
      $stmt_insert->execute();
  }
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Pirata+One&family=Poppins:ital,wght@0,100;0,200;0,300;0,400;0,500;0,600;0,700;0,800;0,900;1,100;1,200;1,300;1,400;1,500;1,600;1,700;1,800;1,900&display=swap" rel="stylesheet">
    <title>Rizal Agriculture</title>
    <style>
        body, html {
            margin: 0;
            padding: 0;
            height: 100%;
            font-family: 'Poppins','Arial', sans-serif;
        }

        .hero-section {
            position: relative;
            height: 100vh;
            background-image: url('<?php echo $backgroundImage; ?>');
            background-size: cover;
            background-position: center;
            color: white;
        }

        .hero-overlay {
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            bottom: 0;
            background-color: rgba(51, 128, 25, 0.65); /* Green overlay */
        }

        .hero-content {
            position: absolute;
            top: 50%;
            left: 50%;
            transform: translate(-50%, -50%);
            text-align: center;
            color: white;
        }

        .hero-content img {
            max-width: 100%; /* Adjust the size of the logo image */
            height: auto;
            margin-bottom: 5%;
        }

        /* Styling the button */
        .btn {
            padding: 15px 40px;
            font-size: 1.2rem;
            color: white;
            text-decoration: none;
            border-radius: 25px; /* Rounded corners */
            font-weight: bold;
            letter-spacing: 1px;
            transition: background-color 0.3s, transform 0.3s;
            border: 4px solid white;
        }

        .btn:hover {
            background-color: #338019; /* Lighter green */
            transform: translateY(-3px); /* Button lift effect */
        }

    </style>
</head>
<body>

<div class="hero-section">
    <div class="hero-overlay"></div>
    <div class="hero-content">
        <!-- Replace h1 with image -->
        <img src="<?php echo $logoImage; ?>" alt="Rizal Agriculture Logo">
        <a href="Main/announcement.php" class="btn">GET STARTED</a>
    </div>
</div>

</body>
</html>
