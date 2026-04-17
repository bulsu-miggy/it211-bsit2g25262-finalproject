<?php
/**
 * UniMerch Admin API — Customers
 * GET /api/admin/customers.php
 */
require_once __DIR__ . '/../../includes/db.php';
require_once __DIR__ . '/../../includes/helpers.php';
require_once __DIR__ . '/../../includes/auth.php';

requireMerchantAuthAPI();
header('Content-Type: application/json');

$pdo = db();
$search = $_GET['search'] ?? '';
$id = $_GET['id'] ?? null;

if ($id) {
    // Get single customer with history and addresses
    $stmt = $pdo->prepare("SELECT id, first_name, last_name, email, phone, is_verified, created_at FROM customers WHERE id = ?");
    $stmt->execute([$id]);
    $customer = $stmt->fetch();

    if (!$customer) jsonResponse(['success' => false, 'message' => 'Customer not found'], 404);

    // Purchase history
    $orderStmt = $pdo->prepare("SELECT id, order_number, total_amount, status, created_at FROM orders WHERE customer_id = ? ORDER BY created_at DESC");
    $orderStmt->execute([$id]);
    $customer['orders'] = $orderStmt->fetchAll();

    // Addresses (handle missing table gracefully)
    try {
        $addrStmt = $pdo->prepare("SELECT * FROM addresses WHERE customer_id = ? ORDER BY is_default DESC");
        $addrStmt->execute([$id]);
        $customer['addresses'] = $addrStmt->fetchAll();
    } catch (Exception $e) {
        $customer['addresses'] = [];
    }

    jsonResponse(['success' => true, 'data' => $customer]);
} else {
    // List customers with stats
    $where = "";
    $params = [];
    if ($search) {
        $where = "WHERE first_name LIKE ? OR last_name LIKE ? OR email LIKE ?";
        $params = ["%$search%", "%$search%", "%$search%"];
    }

    $sql = "
        SELECT 
            c.id, c.first_name, c.last_name, c.email, c.phone, c.is_verified,
            (SELECT COUNT(*) FROM orders WHERE customer_id = c.id) as order_count,
            (SELECT SUM(total_amount) FROM orders WHERE customer_id = c.id) as total_spent
        FROM customers c
        $where
        ORDER BY c.created_at DESC
    ";

    $stmt = $pdo->prepare($sql);
    $stmt->execute($params);
    $customers = $stmt->fetchAll();

    jsonResponse(['success' => true, 'data' => $customers]);
}
