<?php
error_reporting(0);
ini_set('display_errors', 0);
require_once __DIR__ . '/includes/auth.php';
require_once __DIR__ . '/includes/db.php';

// Prevent any accidental output
while (ob_get_level()) ob_end_clean();


// Check if bypass is requested or if logout is forced
if ((isset($_GET['force']) && $_GET['force'] === 'true') || (isset($_GET['action']) && $_GET['action'] === 'logout')) {
    // Clear all session data
    $_SESSION = [];
    if (ini_get("session.use_cookies")) {
        $params = session_get_cookie_params();
        setcookie(session_name(), '', time() - 42000,
            $params["path"], $params["domain"],
            $params["secure"], $params["httponly"]
        );
    }
    session_destroy();
    session_write_close();

    if (isset($_GET['ajax']) || (isset($_SERVER['HTTP_X_REQUESTED_WITH']) && $_SERVER['HTTP_X_REQUESTED_WITH'] === 'XMLHttpRequest')) {
        header('Content-Type: application/json');
        echo json_encode(['success' => true, 'redirect' => BASE_URL . '/index.php']);
        exit;
    }
    header('Location: ' . BASE_URL . '/index.php');
    exit;
}

// Logic Gate: Check for items in the cart
$db = db();
$sessionId = session_id();
$customerId = $_SESSION['customer_id'] ?? null;

// Query both session_id (for guests/logged-in) and customer_id (for logged-in)
$query = "SELECT COUNT(*) as count FROM cart WHERE session_id = :sid";
$params = [':sid' => $sessionId];

if ($customerId) {
    $query .= " OR customer_id = :cid";
    $params[':cid'] = $customerId;
}

$stmt = $db->prepare($query);
$stmt->execute($params);
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
    // Items exist, redirect to confirmation page (Fallback for non-JS)
    header('Location: ' . BASE_URL . '/logout_confirm.php');
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
header('Location: ' . BASE_URL . '/index.php');
exit;
