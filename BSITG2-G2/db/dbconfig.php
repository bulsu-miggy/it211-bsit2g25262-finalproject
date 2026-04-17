<?php

$dbservername = "localhost";
$dbusername = "root";
$dbpassword = "";
$dbname = "annyeonghaven";

$conn = new PDO("mysql:host=$dbservername;port=3306;dbname=$dbname", $dbusername, $dbpassword);
$conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
$conn->setAttribute(PDO::MYSQL_ATTR_USE_BUFFERED_QUERY, true);
?>