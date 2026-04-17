<?php
session_start();
include __DIR__ . '/../connection.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: ../forgot_password.php');
    exit();
}

$token = $_POST['token'] ?? '';
$password = $_POST['password'] ?? '';
$confirm = $_POST['confirm_password'] ?? '';
$reset_request = $_SESSION['password_reset'] ?? null;

if (!is_array($reset_request)
    || empty($reset_request['token'])
    || $reset_request['token'] !== $token
    || empty($reset_request['expires_at'])
    || time() > $reset_request['expires_at']
) {
    header('Location: ../forgot_password.php?error=invalid_token');
    exit();
}

if ($password === '' || $confirm === '') {
    header('Location: ../reset_password.php?token=' . urlencode($token) . '&error=empty_fields');
    exit();
}

if ($password !== $confirm) {
    header('Location: ../reset_password.php?token=' . urlencode($token) . '&error=password_mismatch');
    exit();
}

if (strlen($password) < 8) {
    header('Location: ../reset_password.php?token=' . urlencode($token) . '&error=password_too_short');
    exit();
}

try {
    $email = $reset_request['email'];
    $hashed = password_hash($password, PASSWORD_DEFAULT);
    $update = $conn->prepare('UPDATE login SET password = ? WHERE email = ?');
    $update->execute([$hashed, $email]);

    unset($_SESSION['password_reset']);
    header('Location: ../../login.php?reset=success');
    exit();
} catch (Throwable $e) {
    header('Location: ../reset_password.php?token=' . urlencode($token) . '&error=system_error');
    exit();
}
