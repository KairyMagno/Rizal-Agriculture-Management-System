<?php
session_start();
include('../includes/db.php');
require '../includes/logo.php'; // Include the logo fetching function

// Fetch distinct years for year selection
$sql_years = "SELECT DISTINCT year FROM visitor_growth ORDER BY year DESC";
$result_years = $conn->query($sql_years);
$years = [];
if ($result_years) {
    while ($row = $result_years->fetch_assoc()) {
        $years[] = $row['year'];
    }
} else {
    die("Error fetching years: " . $conn->error);
}

// Initial year to display
$selected_year = isset($_GET['year']) ? intval($_GET['year']) : date("Y");

// Fetch visitor growth data by month for the selected year
$sql_growth = "SELECT month, year, SUM(visit_count) AS total_visits
               FROM visitor_growth
               WHERE year = $selected_year
               GROUP BY year, month
               ORDER BY month";
$result_growth = $conn->query($sql_growth);
if (!$result_growth) {
    die("Error fetching visitor growth: " . $conn->error);
}

// Prepare the data to ensure total_visits are whole numbers
$growth_data = [];
while ($row = $result_growth->fetch_assoc()) {
    $row['total_visits'] = (int) $row['total_visits']; // Convert to whole number
    $growth_data[] = $row;
}

// Fetch usage data by modules for the selected year
$sql_usage = "SELECT module_name, SUM(clicks) AS clicks 
              FROM module_usage 
              WHERE YEAR(last_updated) = $selected_year 
              GROUP BY module_name";
$result_usage = $conn->query($sql_usage);

$usage_data = [];
if ($result_usage) {
    while ($row = $result_usage->fetch_assoc()) {
        // Cast clicks to an integer to ensure whole numbers
        $usage_data[$row['module_name']] = (int) $row['clicks'];
    }
} else {
    die("Error fetching usage data: " . $conn->error);
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
    <title>Dashboard</title>
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
        .container {
            padding: 2%;
        }

        .user-growth{
            background-color: white;
            padding: 10px;
        }

        .usage-modules{
            background-color: white;
        }

        .charts-container {
            margin-top: 3%;
            display: flex;
            justify-content: center;
            gap: 20px;
        }

        canvas {
            width: 100% !important;
            height: auto !important;
        }

        .usage-modules, .user-growth {
            height: 320px;
            padding: 20px;
            border-radius: 8px;
            box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
            width: 500px;
        }

        .year-selector {
            margin: 15px 0;
            display: flex;
            gap: 10px;
            padding-left: 6%;
        }

        #year {
            padding: 5px 10px;
            border: 2px solid #555; /* Border for the select */
            border-radius: 3px; /* Rounded corners for the select */
            background-color: rgba(255, 255, 255, 0.1); /* Slightly transparent background for the select */
        }

        #year:hover {
            border-color: #4caf50; /* Changes border color on hover */
        }

        #year option {
            background-color: white; /* Ensures the options have a solid background */
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
            <li><a href="about.php"><i class="fas fa-file"></i> About RAC</a></li>
            <li><a href="email.php"><i class="fas fa-envelope"></i> Email Inquiry</a></li>
            <li style="margin-bottom: 5px;"><a href="archive.php" ><i class="fas fa-archive"></i> Archive</a></li> <!-- Archive added here -->

            <?php if (!$hide_admin_elements): ?>
                <p style="color:gray; margin: 0;">Admin</p>
                <li><a href="dashboard.php" class="active"><i class="fas fa-tachometer-alt"></i> Dashboard</a></li>
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
            <h1 style="color:green; font-size:28px;">Dashboard</h1>
        </div>

        <section class="container">
            <!-- Year Selector -->
            <div class="year-selector">
                <label for="year">Select Year:</label>
                <select id="year" onchange="changeYear()">
                    <?php foreach ($years as $year) : ?>
                        <option value="<?= $year ?>" <?= $year == $selected_year ? 'selected' : '' ?>><?= $year ?></option>
                    <?php endforeach; ?>
                </select>
            </div>

            <div class="charts-container">
                <!-- User Growth Chart Section -->
                <section class="user-growth">
                    <h2 style="color:green; margin-top:10px;">Visitor Growth by Month</h2>
                    <canvas id="growthChart"></canvas>
                </section>

                <!-- Usage by Modules Chart Section -->
                <section class="usage-modules">
                    <h2 style="color:green; margin-top:10px;">Usage by Modules</h2>
                    <canvas id="usageChart"></canvas>
                </section>
            </div>
        </section>
    </main>

    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <script>
        function changeYear() {
            const selectedYear = document.getElementById('year').value;
            window.location.href = `dashboard.php?year=${selectedYear}`;
        }

        // Prepare visitor growth data
        var visitorGrowthData = <?php echo json_encode($growth_data); ?>;
        var labels = [];
        var visitCounts = [];

        visitorGrowthData.forEach(function (data) {
            var monthLabel = new Date(data.year, data.month - 1).toLocaleString('default', { month: 'short' }) + ' ' + data.year;
            labels.push(monthLabel);
            visitCounts.push(Math.floor(data.total_visits)); // Convert to whole number
        });

        // Create the growth chart
        var ctx = document.getElementById('growthChart').getContext('2d');
        var growthChart = new Chart(ctx, {
            type: 'line',
            data: {
                labels: labels,
                datasets: [{
                    data: visitCounts,
                    borderColor: 'green',
                    fill: false,
                    tension: 0.1
                }]
            },
            options: {
                plugins: {
                    legend: {
                        display: false // Hides the legend
                    }
                },
                scales: {
                    y: {
                        min: 0, // Set the minimum value to 0
                        ticks: {
                            stepSize: 1, // Ensure whole number increments
                            callback: function (value) {
                                return value; // Display ticks as whole numbers
                            }
                        }
                    }
                }
            }
        });

        var moduleUsageData = <?php echo json_encode($usage_data); ?>;
        var moduleNames = Object.keys(moduleUsageData);
        var moduleClicks = moduleNames.map(function(name) {
            return Math.floor(moduleUsageData[name]); // Ensure whole numbers
        });

        // Create the usage chart
        var ctx2 = document.getElementById('usageChart').getContext('2d');
        var usageChart = new Chart(ctx2, {
            type: 'bar',
            data: {
                labels: moduleNames,
                datasets: [{
                    label: 'Clicks',
                    data: moduleClicks,
                    backgroundColor: ['#4caf50', '#ff9800', '#2196f3'],
                    borderColor: 'green',
                    borderWidth: 1
                }]
            },
            options: {
                plugins: {
                    legend: {
                        display: false // Hides the legend
                    }
                },
                scales: {
                    y: {
                        ticks: {
                            stepSize: 1, // Ensures step size is whole numbers
                            callback: function (value) {
                                return Number.isInteger(value) ? value : ''; // Only show whole numbers
                            }
                        }
                    }
                }
            }
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
        }

        init();
        
        function logout() {
            // Redirect to the logout PHP script
            window.location.href = '../includes/logout.php'; // Ensure this path matches the location of logout.php
        }
    </script>
</body>

</html>
