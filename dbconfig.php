<?php
// Database connection settings
$dbservername = "localhost";
$dbusername = "root";
$dbpassword = "";
$dbname = "messi";

try {
    $conn = new PDO("mysql:host=$dbservername;port=3306", $dbusername, $dbpassword);
    $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    $sql = "CREATE DATABASE IF NOT EXISTS ". $dbname;
    
    try {
        $conn->exec($sql);
    } catch (PDOException $th) {
        //echo "<br> Database Already Exists";
    }

    $sql1 = "use ". $dbname;
    $conn->exec($sql1);
    // use exec() because no results are returned
    $table_sql = "CREATE TABLE IF NOT EXISTS users (
        id INT AUTO_INCREMENT PRIMARY KEY,
        first_name VARCHAR(50) NOT NULL,
        last_name VARCHAR(50) NOT NULL,
        username VARCHAR(50) NOT NULL UNIQUE,
        email VARCHAR(100) NOT NULL UNIQUE,
        profile_image VARCHAR(255) DEFAULT NULL,
        password VARCHAR(255) NOT NULL,
        birthday VARCHAR(50),
        address VARCHAR(255),
        phone VARCHAR(20),
        gender VARCHAR(20),
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
    )";
    $conn->exec($table_sql);



} catch (PDOException $e) {
    die("Connection failed: " . $e->getMessage());
}
?>
