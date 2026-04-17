<?php
session_start();
include __DIR__ . '/../connection.php';

function returnJson($payload)
{
    header('Content-Type: application/json');
    echo json_encode($payload);
    exit();
}

$ajaxRequest = isset($_POST['ajax']) && $_POST['ajax'] === '1';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = trim($_POST['email'] ?? '');
    if (!$email || !filter_var($email, FILTER_VALIDATE_EMAIL)) {
        if ($ajaxRequest) {
            returnJson(['status' => 'error', 'error' => 'invalid_email']);
        }
        header('Location: ../../forgot_password.php?error=invalid_email');
        exit();
    }

    try {
        $stmt = $conn->prepare('SELECT user_id FROM login WHERE email = ? LIMIT 1');
        $stmt->execute([$email]);
        $user = $stmt->fetch(PDO::FETCH_ASSOC);

        if (!$user) {
            if ($ajaxRequest) {
                returnJson(['status' => 'error', 'error' => 'email_not_found']);
            }
            header('Location: ../../forgot_password.php?error=email_not_found');
            exit();
        }

        $token = bin2hex(random_bytes(16));
        $_SESSION['password_reset'] = [
            'token' => $token,
            'email' => $email,
            'expires_at' => time() + 3600
        ];

        $resetUrl = 'reset_password.php?token=' . urlencode($token);
        if ($ajaxRequest) {
            returnJson(['status' => 'redirect', 'url' => $resetUrl]);
        }

        header('Location: ../../' . $resetUrl);
        exit();
    } catch (PDOException $e) {
        if ($ajaxRequest) {
            returnJson(['status' => 'error', 'error' => 'system_error']);
        }
        header('Location: ../../forgot_password.php?error=system_error');
        exit();
    }
}

if ($ajaxRequest) {
    returnJson(['status' => 'error', 'error' => 'invalid_request']);
}

header('Location: ../../forgot_password.php');
exit();
