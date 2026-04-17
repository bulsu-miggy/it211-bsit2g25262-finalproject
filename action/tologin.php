<?php
// login.php - This file processes the login request
session_start();
header('Content-Type: application/json');

// Database connection
$servername = "localhost";
$username = "root";
$password = "";
$dbname = "lasafilipina";

// Connect without specifying database first
$conn = new mysqli($servername, $username, $password);

if ($conn->connect_error) {
    echo json_encode(['success' => false, 'message' => 'Database connection failed: ' . $conn->connect_error]);
    exit();
}

// Create database if it doesn't exist
$conn->query("CREATE DATABASE IF NOT EXISTS `$dbname`");

// Now connect to the specific database
$conn->select_db($dbname);

if ($conn->connect_error) {
    echo json_encode(['success' => false, 'message' => 'Database selection failed']);
    exit();
}

// Ensure users table exists
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

// Add missing role column if table already existed without it
$roleColumnResult = $conn->query("SHOW COLUMNS FROM users LIKE 'role'");
if ($roleColumnResult && $roleColumnResult->num_rows === 0) {
    $conn->query("ALTER TABLE users ADD COLUMN role VARCHAR(50) DEFAULT 'customer'");
}

// Get POST data
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode(['success' => false, 'message' => 'Invalid request method: ' . $_SERVER['REQUEST_METHOD']]);
    exit();
}

$data = json_decode(file_get_contents('php://input'), true);

if (!$data) {
    $raw_input = file_get_contents('php://input');
    error_log("Invalid JSON data received: " . substr($raw_input, 0, 200));
    echo json_encode(['success' => false, 'message' => 'Invalid request data. Raw input: ' . substr($raw_input, 0, 100)]);
    exit();
}

error_log("Login data received: " . json_encode($data));

$email = $conn->real_escape_string($data['email']);
$password = $data['password'];

// Debug: log the input
error_log("Login attempt: email=$email, password length=" . strlen($password));

// Query user from database
$sql = "SELECT id, full_name, email, password_hash, role FROM users WHERE email = '$email'";
$result = $conn->query($sql);

if (!$result) {
    error_log("Login query failed: " . $conn->error);
    echo json_encode(['success' => false, 'message' => 'Server error during login.']);
    $conn->close();
    exit();
}

error_log("Query result: num_rows = " . $result->num_rows);

if ($result->num_rows === 1) {
    $user = $result->fetch_assoc();
    
    error_log("User found: id=" . $user['id'] . ", email=" . $user['email']);
    
    // Verify password (assuming passwords are hashed with password_hash())
    if (password_verify($password, $user['password_hash'])) {
        error_log("Password verified successfully");
        // Login successful
        $_SESSION['user_id'] = $user['id'];
        $_SESSION['user_name'] = $user['full_name'];
        $_SESSION['user_email'] = $user['email'];
        $_SESSION['role'] = $user['role'];
        $_SESSION['logged_in'] = true;
        
        $redirect = ($user['role'] === 'admin') ? '../Admin/' : 'home.php';
        
        echo json_encode([
            'success' => true, 
            'message' => 'Login successful',
            'redirect' => $redirect
        ]);
    } else {
        error_log("Password verification failed");
        echo json_encode(['success' => false, 'message' => 'Invalid password']);
    }
} else {
    error_log("User not found for email: $email");
    echo json_encode(['success' => false, 'message' => 'User not found']);
}

$conn->close();
?>