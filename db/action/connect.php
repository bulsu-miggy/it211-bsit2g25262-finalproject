<?php
$host = "localhost";
$dbname = "lynx_db";
$username = "root";
$password = "";

try {
    // You need to assign the PDO object to the $conn variable
    $conn = new PDO("mysql:host=$host;dbname=$dbname", $username, $password);
    
    // This sets the error mode so you can see if something goes wrong
    $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch (PDOException $e) {
    die("Connection failed: " . $e->getMessage());
}
?>