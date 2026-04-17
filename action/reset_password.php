<?php
session_start();
header('Content-Type: application/json');

$servername = "localhost";
$username = "root";
$password = "";
$dbname = "lasafilipina";

$conn = new mysqli($servername, $username, $password);
if ($conn->connect_error) {
    echo json_encode(['success' => false, 'message' => 'Database connection failed: ' . $conn->connect_error]);
    exit();
}

$conn->query("CREATE DATABASE IF NOT EXISTS `$dbname`");
$conn->select_db($dbname);
if ($conn->connect_error) {
    echo json_encode(['success' => false, 'message' => 'Database selection failed']);
    exit();
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode(['success' => false, 'message' => 'Invalid request method']);
    exit();
}

$data = json_decode(file_get_contents('php://input'), true);
if (!$data) {
    echo json_encode(['success' => false, 'message' => 'Invalid request data']);
    exit();
}

$email = trim($conn->real_escape_string($data['email'] ?? ''));
$password = $data['password'] ?? '';
$confirmPassword = $data['confirmPassword'] ?? '';

if (!$email || !$password || !$confirmPassword) {
    echo json_encode(['success' => false, 'message' => 'Please fill in all fields.']);
    exit();
}

if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
    echo json_encode(['success' => false, 'message' => 'Please enter a valid email address.']);
    exit();
}

if ($password !== $confirmPassword) {
    echo json_encode(['success' => false, 'message' => 'Passwords do not match.']);
    exit();
}

if (strlen($password) < 6) {
    echo json_encode(['success' => false, 'message' => 'Password must be at least 6 characters long.']);
    exit();
}

$conn->query(
    "CREATE TABLE IF NOT EXISTS users (
        id INT AUTO_INCREMENT PRIMARY KEY,
        full_name VARCHAR(255) NOT NULL,
        email VARCHAR(255) NOT NULL UNIQUE,
        password_hash VARCHAR(255) NOT NULL,
        role VARCHAR(50) DEFAULT 'customer',
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8"
);

$emailCheck = "SELECT id FROM users WHERE email = '$email'";
$result = $conn->query($emailCheck);
if (!$result) {
    echo json_encode(['success' => false, 'message' => 'Server error.']);
    exit();
}

if ($result->num_rows === 0) {
    echo json_encode(['success' => false, 'message' => 'No account found with that email address.']);
    exit();
}

$passwordHash = password_hash($password, PASSWORD_DEFAULT);
$updateSql = "UPDATE users SET password_hash = '$passwordHash' WHERE email = '$email'";
if ($conn->query($updateSql) === TRUE) {
    echo json_encode(['success' => true, 'message' => 'Password reset successfully.']);
} else {
    echo json_encode(['success' => false, 'message' => 'Unable to update password.']);
}

$conn->close();
