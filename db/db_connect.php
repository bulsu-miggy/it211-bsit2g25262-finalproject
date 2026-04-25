<?php
$servername = "localhost";
$db_user    = "root";
$db_pass    = "";
$dbname     = "it211_g2g1";

try {
    $conn = new PDO("mysql:host=$servername;port=3306;dbname=$dbname;charset=utf8", $db_user, $db_pass);
    $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    $conn->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    die("Connection failed: " . $e->getMessage());
}
?>