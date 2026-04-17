<?php
// 1. Start the session
session_start();

// 2. Include the connection
include __DIR__ . '/../connection.php';

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    try {
        // Collect data from the form
        $email = trim($_POST['email'] ?? '');
        $pass = $_POST['password'] ?? '';

        if (empty($email) || empty($pass)) {
            header("Location: ../../login.php?error=empty_fields");
            exit();
        }

        $stmt = $conn->prepare("SELECT user_id, email, password, full_name FROM login WHERE email = ? LIMIT 1");
        $stmt->execute([$email]);
        $user = $stmt->fetch(PDO::FETCH_ASSOC);

        $loginSuccess = false;

        if ($user) {
            if (password_verify($pass, $user['password'])) {
                $loginSuccess = true;
            } elseif (hash_equals($user['password'], $pass)) {
                // Support legacy plaintext users while upgrading them to a secure hash
                $loginSuccess = true;
                $rehash = password_hash($pass, PASSWORD_DEFAULT);
                $updateStmt = $conn->prepare("UPDATE login SET password = ? WHERE user_id = ?");
                $updateStmt->execute([$rehash, $user['user_id']]);
            }
        }

        if ($loginSuccess) {
            session_regenerate_id(true);
            $_SESSION['user_id'] = $user['user_id'];
            $_SESSION['user_email'] = $user['email'];
            $_SESSION['full_name'] = $user['full_name'];

            header("Location: ../../home.php");
            exit();
        }

        header("Location: ../../login.php?error=invalid_login");
        exit();
    } catch (PDOException $e) {
        header("Location: ../../login.php?error=sql_error");
        exit();
    }
} else {
    header("Location: ../../login.php");
    exit();
}
?>