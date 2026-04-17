<?php
/**
 * UniMerch Admin API — Orders Management
 */
require_once __DIR__ . '/../../includes/db.php';
require_once __DIR__ . '/../../includes/helpers.php';
require_once __DIR__ . '/../../includes/auth.php';

header('Content-Type: application/json; charset=utf-8');
requireMerchantAuthAPI();

$pdo = db();
$method = getRequestMethod();

switch ($method) {
    case 'GET': listOrders($pdo); break;
    case 'PUT': updateOrder($pdo); break;
    default: jsonResponse(['success' => false, 'message' => 'Method not allowed'], 405);
}

function listOrders(PDO $pdo): void {
    $status = $_GET['status'] ?? '';
    $search = $_GET['search'] ?? '';
    $orderId = $_GET['id'] ?? '';

    // Single order detail view
    if ($orderId) {
        $stmt = $pdo->prepare("SELECT * FROM orders WHERE id = ?");
        $stmt->execute([(int)$orderId]);
        $order = $stmt->fetch();

        if (!$order) jsonResponse(['success' => false, 'message' => 'Order not found'], 404);

        $itemStmt = $pdo->prepare("
            SELECT oi.*, p.name AS product_name, p.image, c.code AS category_code
            FROM order_items oi
            JOIN products p ON oi.product_id = p.id
            JOIN categories c ON p.category_id = c.id
            WHERE oi.order_id = ?
        ");
        $itemStmt->execute([$order['id']]);
        $order['items'] = $itemStmt->fetchAll();

        foreach ($order['items'] as &$item) {
            $item['image_url'] = BASE_URL . '/uploads/' . $item['image'];
        }

        jsonResponse(['success' => true, 'data' => $order]);
    }

    $where = ['1=1'];
    $params = [];

    if ($status && $status !== 'all') {
        $where[] = "o.status = ?";
        $params[] = $status;
    }
    if ($search) {
        $where[] = "(o.order_number LIKE ? OR o.customer_name LIKE ? OR o.customer_email LIKE ?)";
        $params[] = '%' . $search . '%';
        $params[] = '%' . $search . '%';
        $params[] = '%' . $search . '%';
    }

    $whereSQL = implode(' AND ', $where);
    $stmt = $pdo->prepare("
        SELECT o.*,
               (SELECT COUNT(*) FROM order_items WHERE order_id = o.id) AS item_count
        FROM orders o
        WHERE {$whereSQL}
        ORDER BY o.created_at DESC
    ");
    $stmt->execute($params);

    jsonResponse(['success' => true, 'data' => $stmt->fetchAll()]);
}

function updateOrder(PDO $pdo): void {
    $data = getJsonBody();
    $id = (int)($data['id'] ?? 0);
    $status = $data['status'] ?? '';
    $paymentStatus = $data['payment_status'] ?? '';

    if (!$id) jsonResponse(['success' => false, 'message' => 'Order ID required'], 400);

    $fields = [];
    $params = [];

    $validStatuses = ['pending', 'confirmed', 'processing', 'ready', 'completed', 'cancelled'];
    if ($status && in_array($status, $validStatuses)) {
        $fields[] = 'status = ?';
        $params[] = $status;
    }

    $validPaymentStatuses = ['unpaid', 'pending_verification', 'paid'];
    if ($paymentStatus && in_array($paymentStatus, $validPaymentStatuses)) {
        $fields[] = 'payment_status = ?';
        $params[] = $paymentStatus;
    }

    if (empty($fields)) {
        jsonResponse(['success' => false, 'message' => 'Nothing to update'], 400);
    }

    $params[] = $id;
    $pdo->prepare("UPDATE orders SET " . implode(', ', $fields) . " WHERE id = ?")->execute($params);

    jsonResponse(['success' => true, 'message' => 'Order updated']);
}
