<?php
/**
 * UniMerch Admin API — Update Order Status (Publisher)
 */
require_once __DIR__ . '/../../includes/db.php';
require_once __DIR__ . '/../../includes/helpers.php';
require_once __DIR__ . '/../../includes/auth.php';

header('Content-Type: application/json; charset=utf-8');
requireMerchantAuthAPI();

$pdo = db();
$method = getRequestMethod();

if ($method !== 'POST') {
    jsonResponse(['success' => false, 'message' => 'Method not allowed'], 405);
}

// Support both JSON body and standard Form POST
$data = empty($_POST) ? getJsonBody() : $_POST;

$orderId = (int)($data['order_id'] ?? 0);
$newStatus = $data['new_status'] ?? '';

if (!$orderId || !$newStatus) {
    jsonResponse(['success' => false, 'message' => 'Missing order_id or new_status'], 400);
}

$validStatuses = ['pending', 'confirmed', 'processing', 'ready', 'completed', 'cancelled'];
if (!in_array($newStatus, $validStatuses)) {
    jsonResponse(['success' => false, 'message' => 'Invalid status provided'], 400);
}

try {
    // Update the order and set updated_at timestamp
    $stmt = $pdo->prepare("UPDATE orders SET status = ?, updated_at = NOW() WHERE id = ?");
    if ($stmt->execute([$newStatus, $orderId])) {
        jsonResponse(['success' => true, 'message' => "Order updated to " . ucfirst($newStatus)]);
    } else {
        jsonResponse(['success' => false, 'message' => 'Database update failed'], 500);
    }
} catch (PDOException $e) {
    jsonResponse(['success' => false, 'message' => 'Database Error: ' . $e->getMessage()], 500);
}
