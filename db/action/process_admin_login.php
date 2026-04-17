<?php
// Admin Login Processor for Dashboard
session_start();
include __DIR__ . '/../connection.php';

if ($_SERVER["REQUEST_METHOD"] !== "POST") {
header("Location: ../dashboard/login.php");
    exit();
}

try {
    $email = trim($_POST['email'] ?? '');
    $pass = $_POST['password'] ?? '';

    if (empty($email) || empty($pass)) {
        header("Location: ../../dashboard/login.php?error=empty_fields");
        exit();
    }

    $stmt = $conn->prepare("SELECT id, full_name, email, password_hash FROM admins WHERE email = ? LIMIT 1");
    $stmt->execute([$email]);
    $admin = $stmt->fetch(PDO::FETCH_ASSOC);

    $loginSuccess = false;

    if ($admin) {
        if (password_verify($pass, $admin['password_hash'])) {
            $loginSuccess = true;
        } elseif (hash_equals($admin['password_hash'], $pass)) {
            // Legacy plaintext support
            $loginSuccess = true;
            $rehash = password_hash($pass, PASSWORD_DEFAULT);
            $updateStmt = $conn->prepare("UPDATE admins SET password_hash = ? WHERE id = ?");
            $updateStmt->execute([$rehash, $admin['id']]);
        }
    }

    if ($loginSuccess) {
        session_regenerate_id(true);
        $_SESSION['admin_logged_in'] = true;
        $_SESSION['admin_id'] = $admin['id'];
        $_SESSION['admin_email'] = $admin['email'];
        $_SESSION['admin_name'] = $admin['full_name'];
header("Location: ../../dashboard/dashboard.php");
        exit();
    }

    header("Location: ../../dashboard/login.php?error=invalid");
    exit();

} catch (PDOException $e) {
    header("Location: ../../dashboard/login.php?error=sql_error");
    exit();
}
?>

