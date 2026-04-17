<?php
/**
 * UniMerch API — Order Polling Sync (Subscriber)
 */
require_once __DIR__ . '/../includes/db.php';
require_once __DIR__ . '/../includes/helpers.php';
require_once __DIR__ . '/../includes/auth.php';

header('Content-Type: application/json; charset=utf-8');

// Ensure user is authenticated to their own data
requireCustomerAuthAPI();
$customer = getCustomer();

$pdo = db();
$orderIds = $_POST['order_ids'] ?? [];

if (empty($orderIds) || !is_array($orderIds)) {
    jsonResponse(['success' => false, 'message' => 'No valid order_ids provided']);
}

// Sanitize IDs
$cleanIds = array_map('intval', $orderIds);
$placeholders = implode(',', array_fill(0, count($cleanIds), '?'));

// Retrieve current status. Security: ensure the orders belong to this specific customer.
$stmt = $pdo->prepare("SELECT id, status FROM orders WHERE customer_id = ? AND id IN ($placeholders)");
$params = array_merge([$customer['id']], $cleanIds);
$stmt->execute($params);

$orders = $stmt->fetchAll(PDO::FETCH_ASSOC);

jsonResponse([
    'success' => true,
    'data' => $orders
]);
