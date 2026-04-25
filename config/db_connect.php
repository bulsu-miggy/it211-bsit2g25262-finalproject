<?php
// Root: C:/xampp/htdocs/sparkverse/db_connect.php
$servername = "localhost";
$username = "root";     // Default username sa XAMPP
$password = "";         // Default password sa XAMPP (empty)
$dbname = "it211_g2g4"; // Siguraduhin na ito ang pangalan ng DB mo sa phpMyAdmin

// Create connection
$conn = mysqli_connect($servername, $username, $password, $dbname);

// Check connection
if (!$conn) {
    die("Connection failed: " . mysqli_connect_error());
}
?>