<?php
/**
 * UniMerch API — Customer Authentication
 * Register, Login, OTP Verify, Logout, Profile Update
 */
require_once __DIR__ . '/../includes/db.php';
require_once __DIR__ . '/../includes/helpers.php';
require_once __DIR__ . '/../includes/auth.php';

header('Content-Type: application/json; charset=utf-8');

$pdo = db();
$method = getRequestMethod();

// Handle logout via GET
if ($method === 'GET' && ($_GET['action'] ?? '') === 'logout') {
    session_destroy();
    header('Location: ' . BASE_URL . '/');
    exit;
}

if ($method === 'POST') {
    $data = getJsonBody();
    $action = $data['action'] ?? '';

    switch ($action) {
        case 'register': handleRegister($pdo, $data); break;
        case 'verify_otp': handleVerifyOtp($pdo, $data); break;
        case 'login': handleLogin($pdo, $data); break;
        case 'resend_otp': handleResendOtp($pdo, $data); break;
        case 'forgot_password': handleForgotPassword($pdo, $data); break;
        case 'verify_reset_otp': handleVerifyResetOtp($pdo, $data); break;
        case 'reset_password': handleResetPassword($pdo, $data); break;
        default: jsonResponse(['success' => false, 'message' => 'Invalid action'], 400);
    }
} elseif ($method === 'PUT') {
    $data = getJsonBody();
    $action = $data['action'] ?? '';

    if ($action === 'update_profile') {
        handleUpdateProfile($pdo, $data);
    }
} else {
    jsonResponse(['success' => false, 'message' => 'Method not allowed'], 405);
}

function handleRegister(PDO $pdo, array $data): void {
    $error = validateRequired($data, ['first_name', 'last_name', 'email', 'password']);
    if ($error) jsonResponse(['success' => false, 'message' => $error], 400);

    $firstName = sanitize($data['first_name']);
    $lastName  = sanitize($data['last_name']);
    $email     = strtolower(trim($data['email']));
    $phone     = sanitize($data['phone'] ?? '');
    $password  = $data['password'];

    if (strlen($password) < 6) {
        jsonResponse(['success' => false, 'message' => 'Password must be at least 6 characters'], 400);
    }

    // Check if email already exists
    $existing = $pdo->prepare("SELECT id FROM customers WHERE email = ?");
    $existing->execute([$email]);
    if ($existing->fetch()) {
        jsonResponse(['success' => false, 'message' => 'An account with this email already exists'], 409);
    }

    // Generate OTP
    $otp = generateOTP();
    $otpExpires = date('Y-m-d H:i:s', strtotime('+' . OTP_EXPIRY_MINUTES . ' minutes'));
    $hashedPassword = password_hash($password, PASSWORD_BCRYPT);

    $stmt = $pdo->prepare("
        INSERT INTO customers (first_name, last_name, email, phone, password, otp_code, otp_expires)
        VALUES (?, ?, ?, ?, ?, ?, ?)
    ");
    $stmt->execute([$firstName, $lastName, $email, $phone, $hashedPassword, $otp, $otpExpires]);

    jsonResponse([
        'success' => true,
        'message' => 'Registration successful! Please verify your email.',
        'otp'     => $otp, // Exposed for demo — remove in production
        'email'   => $email
    ]);
}

function handleVerifyOtp(PDO $pdo, array $data): void {
    $email = strtolower(trim($data['email'] ?? ''));
    $otp   = trim($data['otp'] ?? '');

    if (!$email || !$otp) {
        jsonResponse(['success' => false, 'message' => 'Email and OTP are required'], 400);
    }

    $stmt = $pdo->prepare("SELECT * FROM customers WHERE email = ? AND otp_code = ? AND otp_expires > NOW()");
    $stmt->execute([$email, $otp]);
    $customer = $stmt->fetch();

    if (!$customer) {
        jsonResponse(['success' => false, 'message' => 'Invalid or expired OTP code'], 400);
    }

    // Mark as verified and clear OTP
    $update = $pdo->prepare("UPDATE customers SET is_verified = 1, otp_code = NULL, otp_expires = NULL WHERE id = ?");
    $update->execute([$customer['id']]);

    // Auto-login after verification
    $_SESSION['customer_id']         = $customer['id'];
    $_SESSION['customer_first_name'] = $customer['first_name'];
    $_SESSION['customer_last_name']  = $customer['last_name'];
    $_SESSION['customer_email']      = $customer['email'];
    $_SESSION['customer_phone']      = $customer['phone'];

    // Migrate session cart to customer cart
    migrateCart($pdo, session_id(), $customer['id']);

    jsonResponse([
        'success'  => true,
        'message'  => 'Email verified! Welcome to UniMerch.',
        'redirect' => BASE_URL . '/'
    ]);
}

function handleLogin(PDO $pdo, array $data): void {
    $email    = strtolower(trim($data['email'] ?? ''));
    $password = $data['password'] ?? '';

    if (!$email || !$password) {
        jsonResponse(['success' => false, 'message' => 'Email and password are required'], 400);
    }

    $stmt = $pdo->prepare("SELECT * FROM customers WHERE email = ?");
    $stmt->execute([$email]);
    $customer = $stmt->fetch();

    if (!$customer || !password_verify($password, $customer['password'])) {
        jsonResponse(['success' => false, 'message' => 'Invalid email or password'], 401);
    }

    if (!$customer['is_verified']) {
        // Resend OTP
        $otp = generateOTP();
        $otpExpires = date('Y-m-d H:i:s', strtotime('+' . OTP_EXPIRY_MINUTES . ' minutes'));
        $update = $pdo->prepare("UPDATE customers SET otp_code = ?, otp_expires = ? WHERE id = ?");
        $update->execute([$otp, $otpExpires, $customer['id']]);

        jsonResponse([
            'success'    => false,
            'message'    => 'Account not verified. Please check your OTP.',
            'needs_otp'  => true,
            'otp'        => $otp,
            'email'      => $email
        ]);
    }

    // Set session
    $_SESSION['customer_id']         = $customer['id'];
    $_SESSION['customer_first_name'] = $customer['first_name'];
    $_SESSION['customer_last_name']  = $customer['last_name'];
    $_SESSION['customer_email']      = $customer['email'];
    $_SESSION['customer_phone']      = $customer['phone'];

    // Migrate session cart
    migrateCart($pdo, session_id(), $customer['id']);

    jsonResponse([
        'success'  => true,
        'message'  => 'Welcome back, ' . $customer['first_name'] . '!',
        'redirect' => BASE_URL . '/'
    ]);
}

function handleResendOtp(PDO $pdo, array $data): void {
    $email = strtolower(trim($data['email'] ?? ''));

    $stmt = $pdo->prepare("SELECT id FROM customers WHERE email = ? AND is_verified = 0");
    $stmt->execute([$email]);
    $customer = $stmt->fetch();

    if (!$customer) {
        jsonResponse(['success' => false, 'message' => 'Account not found or already verified'], 404);
    }

    $otp = generateOTP();
    $otpExpires = date('Y-m-d H:i:s', strtotime('+' . OTP_EXPIRY_MINUTES . ' minutes'));
    $update = $pdo->prepare("UPDATE customers SET otp_code = ?, otp_expires = ? WHERE id = ?");
    $update->execute([$otp, $otpExpires, $customer['id']]);

    jsonResponse([
        'success' => true,
        'message' => 'New OTP sent!',
        'otp'     => $otp
    ]);
}

function handleUpdateProfile(PDO $pdo, array $data): void {
    if (!isCustomerLoggedIn()) {
        jsonResponse(['success' => false, 'message' => 'Please log in'], 401);
    }

    $customerId = $_SESSION['customer_id'];
    $firstName  = sanitize($data['first_name'] ?? '');
    $lastName   = sanitize($data['last_name'] ?? '');
    $phone      = sanitize($data['phone'] ?? '');

    if (!$firstName || !$lastName) {
        jsonResponse(['success' => false, 'message' => 'Name is required'], 400);
    }

    $stmt = $pdo->prepare("UPDATE customers SET first_name = ?, last_name = ?, phone = ? WHERE id = ?");
    $stmt->execute([$firstName, $lastName, $phone, $customerId]);

    // Update session
    $_SESSION['customer_first_name'] = $firstName;
    $_SESSION['customer_last_name']  = $lastName;
    $_SESSION['customer_phone']      = $phone;

    jsonResponse(['success' => true, 'message' => 'Profile updated successfully']);
}

/**
 * Migrate anonymous session cart to customer cart on login/register
 */
function migrateCart(PDO $pdo, string $sessionId, int $customerId): void {
    $pdo->prepare("UPDATE cart SET customer_id = ? WHERE session_id = ? AND customer_id IS NULL")
        ->execute([$customerId, $sessionId]);
}

function handleForgotPassword(PDO $pdo, array $data): void {
    $email = strtolower(trim($data['email'] ?? ''));
    if (!$email) jsonResponse(['success' => false, 'message' => 'Email is required'], 400);

    $stmt = $pdo->prepare("SELECT id FROM customers WHERE email = ?");
    $stmt->execute([$email]);
    $customer = $stmt->fetch();

    if (!$customer) {
        // Forensically we return success even if not found to prevent email enumeration
        jsonResponse(['success' => true, 'message' => 'If this email exists, an OTP has been sent.']);
    }

    $otp = generateOTP(4);
    $otpExpires = date('Y-m-d H:i:s', strtotime('+' . OTP_EXPIRY_MINUTES . ' minutes'));
    
    $update = $pdo->prepare("UPDATE customers SET otp_code = ?, otp_expires = ? WHERE id = ?");
    $update->execute([$otp, $otpExpires, $customer['id']]);

    jsonResponse([
        'success' => true, 
        'message' => 'OTP sent to your email.',
        'otp' => $otp // Exposed for demo
    ]);
}

function handleVerifyResetOtp(PDO $pdo, array $data): void {
    $email = strtolower(trim($data['email'] ?? ''));
    $otp   = trim($data['otp'] ?? '');

    if (!$email || !$otp) jsonResponse(['success' => false, 'message' => 'Email and OTP required'], 400);

    $stmt = $pdo->prepare("SELECT id FROM customers WHERE email = ? AND otp_code = ? AND otp_expires > NOW()");
    $stmt->execute([$email, $otp]);
    $customer = $stmt->fetch();

    if (!$customer) jsonResponse(['success' => false, 'message' => 'Invalid or expired OTP'], 400);

    // Store in session that this user is cleared for reset
    $_SESSION['reset_email'] = $email;
    $_SESSION['reset_authorized'] = true;

    jsonResponse(['success' => true, 'message' => 'OTP verified.']);
}

function handleResetPassword(PDO $pdo, array $data): void {
    if (!($_SESSION['reset_authorized'] ?? false)) {
        jsonResponse(['success' => false, 'message' => 'Unauthorized reset attempt'], 403);
    }

    $email = $_SESSION['reset_email'];
    $password = $data['password'] ?? '';

    // Specialized complexity check (Forensic Requirement)
    $hasUpper = preg_match('/[A-Z]/', $password);
    $hasNumber = preg_match('/[0-9]/', $password);
    $hasSpecial = preg_match('/[!@#$%^&*(),.?":{}|<>]/', $password);
    
    if (strlen($password) < 8 || !$hasUpper || !$hasNumber || !$hasSpecial) {
        jsonResponse(['success' => false, 'message' => 'Password does not meet complexity requirements'], 400);
    }

    $hashed = password_hash($password, PASSWORD_BCRYPT);
    $stmt = $pdo->prepare("UPDATE customers SET password = ?, otp_code = NULL, otp_expires = NULL WHERE email = ?");
    $stmt->execute([$hashed, $email]);

    // Clear reset session
    unset($_SESSION['reset_email']);
    unset($_SESSION['reset_authorized']);

    jsonResponse(['success' => true, 'message' => 'Password reset successful!']);
}
