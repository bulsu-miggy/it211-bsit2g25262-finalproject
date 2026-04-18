<?php
include "db.php";

$name = $_POST['name'] ?? '';
$email = $_POST['email'] ?? '';
$password = $_POST['password'] ?? '';

if ($name == "" || $email == "" || $password == "") {
    echo "Please fill all fields (name, email, password)";
    exit();
}

$check = $conn->query("SELECT * FROM users WHERE email='$email'");
if ($check->num_rows > 0) {
    echo "Email already exists";
    exit();
}

$hashed = password_hash($password, PASSWORD_DEFAULT);

$sql = "INSERT INTO users (name, email, password)
        VALUES ('$name', '$email', '$hashed')";

if ($conn->query($sql)) {
    echo "success";
} else {
    echo "error";
}
?>