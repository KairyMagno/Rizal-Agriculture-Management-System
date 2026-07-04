<?php
// logo.php
function getLogoPath($conn) {
    // Fetch the logo path from the database
    $result = $conn->query("SELECT logo_path FROM site_settings WHERE id = 1");
    if ($result && $row = $result->fetch_assoc()) {
        return $row['logo_path'];
    }
    return 'default_logo.png'; // Fallback logo if no logo is set in the database
}
?>
