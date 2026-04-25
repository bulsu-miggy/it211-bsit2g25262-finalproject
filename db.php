<?php
$host = "localhost";
$user = "root";
$pass = "";
$db = "it211_g1g5";

$conn = new mysqli($host, $user, $pass, $db);

if ($conn->connect_error) {
  die("DB Connection Failed");
}
?>