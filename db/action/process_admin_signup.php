<?php
// Admin Signup Processor for Dashboard
session_start();
include __DIR__ . '/../connection.php';

if ($_SERVER["REQUEST_METHOD"] !== "POST") {
header("Location: ../../dashboard/login.php");
    exit();
}

try {
    $full_name = trim($_POST['full_name'] ?? '');
    $email = trim($_POST['email'] ?? '');
    $pass = $_POST['password'] ?? '';

    // Validation
    if (empty($full_name) || empty($email) || empty($pass)) {
        header("Location: ../../dashboard/login.php?error=empty_fields");
        exit();
    }

    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        header("Location: ../../dashboard/login.php?error=invalid_email");
        exit();
    }

    if (strlen($pass) < 8) {
        header("Location: ../../dashboard/login.php?error=password_too_short");
        exit();
    }

    // Check if email exists
    $stmt = $conn->prepare("SELECT id FROM admins WHERE email = ? LIMIT 1");
    $stmt->execute([$email]);
    if ($stmt->fetch()) {
        header("Location: ../../dashboard/login.php?error=email_exists");
        exit();
    }

    // Hash password
    $hashed_password = password_hash($pass, PASSWORD_DEFAULT);

    // Insert new admin
    $stmt = $conn->prepare("INSERT INTO admins (full_name, email, password_hash, role) VALUES (?, ?, ?, 'Super Admin')");
    $stmt->execute([$full_name, $email, $hashed_password]);

header("Location: ../../dashboard/login.php?success=signup");
    exit();

} catch (PDOException $e) {
    header("Location: ../../dashboard/login.php?error=signup_failed");
    exit();
}
?>

