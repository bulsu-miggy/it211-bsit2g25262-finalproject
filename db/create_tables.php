<?php
$con = mysqli_connect("localhost","root","");

if(!$con){
    die("Connection failed: " . mysqli_connect_error());
}

// Create database if not exists
$sql_create_db = "CREATE DATABASE IF NOT EXISTS w7";
if (mysqli_query($con, $sql_create_db)) {
    echo "Database 'w7' ensured.\n";
} else {
    echo "Error creating database: " . mysqli_error($con) . "\n";
}

if(!mysqli_select_db($con, "w7"))
{
  die("connection error");
}

$sql_login = "CREATE TABLE IF NOT EXISTS login (
    id INT AUTO_INCREMENT PRIMARY KEY,
    username VARCHAR(255) NOT NULL UNIQUE,
    email VARCHAR(255) NOT NULL UNIQUE,
    password VARCHAR(255) NOT NULL,
    first_name VARCHAR(200),
    last_name VARCHAR(200)
)";

if (mysqli_query($con, $sql_login)) {
    echo "Table 'login' created successfully.\n";
} else {
    echo "Error creating login table: " . mysqli_error($con) . "\n";
}

$sql_products = "CREATE TABLE IF NOT EXISTS products (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(255) NOT NULL,
    description TEXT,
    price DECIMAL(10,2) NOT NULL,
    image VARCHAR(255)
)";

if (mysqli_query($con, $sql_products)) {
    echo "Table 'products' created successfully.\n";
} else {
    echo "Error creating products table: " . mysqli_error($con) . "\n";
}

mysqli_close($con);
?>