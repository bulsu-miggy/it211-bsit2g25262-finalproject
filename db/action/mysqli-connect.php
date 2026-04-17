<?php
$servername = "localhost";
$username = "root";
$password = "";
$con = new mysqli($servername, $username, $password, "lynx_db");

if ($con->connect_error) {
    die("Connection failed: " . $con->connect_error);
}
?>

