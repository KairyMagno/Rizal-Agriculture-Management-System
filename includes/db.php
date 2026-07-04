<?php
// db.php - Database Connection
$host = 'localhost:3306'; // Database host
$username = 'root'; // Database username
$password = ''; // Database password
$database = 'wms'; // Database name

// Create a connection
$conn = new mysqli($host, $username, $password, $database);

// Check connection
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}
?>
