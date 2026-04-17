<?php

$dbservername = "localhost";
$dbusername   = "root";
$dbpassword   = "";
$dbname       = "project_wst"; // lowercase — consistent across all files

try {
    $conn = new PDO(
        "mysql:host=$dbservername;port=3306;dbname=$dbname;charset=utf8mb4",
        $dbusername,
        $dbpassword
    );
    $conn->setAttribute(PDO::ATTR_ERRMODE,         PDO::ERRMODE_EXCEPTION);
    $conn->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    die("Database connection failed: " . $e->getMessage());
}
