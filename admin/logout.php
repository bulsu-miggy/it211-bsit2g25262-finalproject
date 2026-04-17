<?php
error_reporting(0);
ini_set('display_errors', 0);
require_once __DIR__ . '/../includes/auth.php';
require_once __DIR__ . '/../includes/db.php';

// Prevent any accidental output
while (ob_get_level()) ob_end_clean();


// Check if bypass is requested or if logout is forced
if ((isset($_GET['force']) && $_GET['force'] === 'true') || (isset($_GET['action']) && $_GET['action'] === 'logout')) {
    // Clear all session data
    $_SESSION = [];
    if (ini_get("session.use_cookies")) {
        $params = session_get_cookie_params();
        setcookie(session_name(), '', time() - 42000, $params["path"], $params["domain"], $params["secure"], $params["httponly"]);
    }
    session_destroy();
    session_write_close();
    
    if (isset($_GET['ajax']) || (isset($_SERVER['HTTP_X_REQUESTED_WITH']) && $_SERVER['HTTP_X_REQUESTED_WITH'] === 'XMLHttpRequest')) {
        header('Content-Type: application/json');
        echo json_encode(['success' => true, 'redirect' => BASE_URL . '/admin/login.php']);
        exit;
    }
    header('Location: ' . BASE_URL . '/admin/login.php');
    exit;
}

// Logic Gate: Check for pending orders
$db = db();
$stmt = $db->prepare("SELECT COUNT(*) as count FROM orders WHERE status = 'pending'");
$stmt->execute();
$result = $stmt->fetch(PDO::FETCH_ASSOC);
$count = (int)($result['count'] ?? 0);

// If AJAX check requested
if (isset($_GET['action']) && $_GET['action'] === 'check') {
    header('Content-Type: application/json');
    echo json_encode([
        'success' => true,
        'count' => $count,
        'has_items' => $count > 0
    ]);
    exit;
}

if ($count > 0) {
    // Fallback for non-JS
    header('Location: ' . BASE_URL . '/admin/logout_confirm.php');
    exit;
}

// No items, proceed with logout
$_SESSION = [];
if (ini_get("session.use_cookies")) {
    $params = session_get_cookie_params();
    setcookie(session_name(), '', time() - 42000, $params["path"], $params["domain"], $params["secure"], $params["httponly"]);
}
session_destroy();
session_write_close();
header('Location: ' . BASE_URL . '/admin/login.php');
exit;

