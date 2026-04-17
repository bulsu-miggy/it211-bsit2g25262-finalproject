<?php
session_start();
require_once 'db/connection.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: login.php');
    exit();
}

$email = trim($_POST['email'] ?? '');
$password = $_POST['password'] ?? '';

if (empty($email) || empty($password)) {
    $_SESSION['login_error'] = 'Both email and password are required.';
    header('Location: login.php');
    exit();
}

if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
    $_SESSION['login_error'] = 'Please enter a valid email address.';
    header('Location: login.php');
    exit();
}

try {
    $stmt = $conn->prepare('SELECT id, first_name, last_name, email, password, is_admin FROM users WHERE email = ? LIMIT 1');
    $stmt->execute([$email]);
    $user = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$user) {
        $_SESSION['login_error'] = 'Email or password is incorrect.';
        header('Location: login.php');
        exit();
    }

    if (!password_verify($password, $user['password'])) {
        $_SESSION['login_error'] = 'Email or password is incorrect.';
        header('Location: login.php');
        exit();
    }

    // Login successful
    $_SESSION['user_id'] = $user['id'];
    $_SESSION['user_name'] = $user['first_name'] . ' ' . $user['last_name'];
    $_SESSION['user_email'] = $user['email'];
    $_SESSION['is_admin'] = !empty($user['is_admin']) ? 1 : 0;
    $_SESSION['login_success'] = 'Login successful. Welcome back!';

    if (!empty($_SESSION['is_admin'])) {
        header('Location: Admin/index.php');
    } else {
        header('Location: index.php');
    }
    exit();
} catch (PDOException $e) {
    $_SESSION['login_error'] = 'Database error: ' . $e->getMessage();
    header('Location: login.php');
    exit();
}
?>