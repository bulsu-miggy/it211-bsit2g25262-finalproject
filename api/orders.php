<?php
/**
 * UniMerch API — Orders
 * POST: Place a new order
 */
require_once __DIR__ . '/../includes/db.php';
require_once __DIR__ . '/../includes/helpers.php';
require_once __DIR__ . '/../includes/auth.php';

header('Content-Type: application/json; charset=utf-8');

$pdo = db();
$method = getRequestMethod();

if ($method === 'GET') {
    requireCustomerAuthAPI();
    $orderId = (int) ($_GET['id'] ?? 0);
    $customerId = $_SESSION['customer_id'];

    if (!$orderId) {
        jsonResponse(['success' => false, 'message' => 'Order ID required'], 400);
    }

    // Verify ownership
    $stmt = $pdo->prepare("SELECT * FROM orders WHERE id = ? AND customer_id = ?");
    $stmt->execute([$orderId, $customerId]);
    $order = $stmt->fetch();

    if (!$order) {
        jsonResponse(['success' => false, 'message' => 'Order not found'], 404);
    }

    // Fetch items
    $itemStmt = $pdo->prepare("
        SELECT oi.*, p.name AS product_name, p.image, c.code AS category_code
        FROM order_items oi
        JOIN products p ON oi.product_id = p.id
        JOIN categories c ON p.category_id = c.id
        WHERE oi.order_id = ?
    ");
    $itemStmt->execute([$orderId]);
    $items = $itemStmt->fetchAll();

    foreach ($items as &$item) {
        $item['image_url'] = BASE_URL . '/uploads/' . $item['image'];
    }

    $order['items'] = $items;
    jsonResponse(['success' => true, 'data' => $order]);
}

if ($method !== 'POST') {
    jsonResponse(['success' => false, 'message' => 'Method not allowed'], 405);
}

$data = getJsonBody();

// Validate required fields
$error = validateRequired($data, ['customer_name', 'customer_email', 'customer_phone', 'payment_method']);
if ($error) {
    jsonResponse(['success' => false, 'message' => $error], 400);
}

$customerName  = sanitize($data['customer_name']);
$customerEmail = sanitize($data['customer_email']);
$customerPhone = sanitize($data['customer_phone']);
$paymentMethod = $data['payment_method'];
$notes         = sanitize($data['notes'] ?? '');
$customerId    = isCustomerLoggedIn() ? $_SESSION['customer_id'] : null;

// Validate payment method
if (!in_array($paymentMethod, ['cash', 'gcash', 'bank_transfer'])) {
    jsonResponse(['success' => false, 'message' => 'Invalid payment method'], 400);
}

// Get cart items
$sessionId = session_id();
$where = $customerId ? "c.customer_id = ?" : "c.session_id = ?";
$param = $customerId ?: $sessionId;

$cartStmt = $pdo->prepare("
    SELECT c.*, p.name, p.price AS current_price, p.stock, p.status
    FROM cart c
    JOIN products p ON c.product_id = p.id
    WHERE {$where}
");
$cartStmt->execute([$param]);
$cartItems = $cartStmt->fetchAll();

if (empty($cartItems)) {
    jsonResponse(['success' => false, 'message' => 'Your cart is empty'], 400);
}

// Validate stock for all items
foreach ($cartItems as $item) {
    if ($item['status'] !== 'active') {
        jsonResponse(['success' => false, 'message' => $item['name'] . ' is no longer available'], 400);
    }
    if ($item['quantity'] > $item['stock']) {
        jsonResponse(['success' => false, 'message' => $item['name'] . ' only has ' . $item['stock'] . ' in stock'], 400);
    }
}

// Calculate total
$totalAmount = 0;
foreach ($cartItems as $item) {
    $totalAmount += $item['current_price'] * $item['quantity'];
}

// Begin transaction
$pdo->beginTransaction();

try {
    // Generate order number
    $orderNumber = generateOrderNumber($pdo);

    // Determine payment status
    $paymentStatus = $paymentMethod === 'cash' ? 'unpaid' : 'pending_verification';

    // Create order
    $orderStmt = $pdo->prepare("
        INSERT INTO orders (order_number, customer_id, customer_name, customer_email, customer_phone, 
                           total_amount, status, payment_method, payment_status, notes)
        VALUES (?, ?, ?, ?, ?, ?, 'pending', ?, ?, ?)
    ");
    $orderStmt->execute([
        $orderNumber, $customerId, $customerName, $customerEmail, $customerPhone,
        $totalAmount, $paymentMethod, $paymentStatus, $notes
    ]);

    $orderId = (int) $pdo->lastInsertId();

    // Create order items and decrement stock
    $itemStmt = $pdo->prepare("INSERT INTO order_items (order_id, product_id, quantity, size, price) VALUES (?, ?, ?, ?, ?)");
    $stockStmt = $pdo->prepare("UPDATE products SET stock = stock - ? WHERE id = ?");

    foreach ($cartItems as $item) {
        $itemStmt->execute([$orderId, $item['product_id'], $item['quantity'], $item['size'], $item['current_price']]);
        $stockStmt->execute([$item['quantity'], $item['product_id']]);
    }

    // Clear cart
    $clearStmt = $pdo->prepare("DELETE FROM cart WHERE " . ($customerId ? "customer_id = ?" : "session_id = ?"));
    $clearStmt->execute([$param]);

    $pdo->commit();

    jsonResponse([
        'success'      => true,
        'message'      => 'Order placed successfully!',
        'order_number' => $orderNumber,
        'redirect'     => BASE_URL . '/order-success.php?order=' . $orderNumber
    ]);

} catch (\Exception $e) {
    $pdo->rollBack();
    jsonResponse(['success' => false, 'message' => 'Failed to place order. Please try again.'], 500);
}
