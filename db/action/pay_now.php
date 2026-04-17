<?php
require_once __DIR__ . '/dbconfig.php';
session_start();

if (!isset($_SESSION['username'])) {
    header('Location: ../../loginpage.php');
    exit();
}

$stmt = $conn->prepare('SELECT id FROM login WHERE username = :username LIMIT 1');
$stmt->execute([':username' => $_SESSION['username']]);
$user = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$user) {
    header('Location: ../../loginpage.php');
    exit();
}

$user_id = (int)$user['id'];
$selected_raw = $_POST['selected_items'] ?? '';
$selected_ids = array_values(array_unique(array_filter(array_map('intval', explode(',', $selected_raw)), function ($id) {
    return $id > 0;
})));

if (empty($selected_ids)) {
    header('Location: ../../cart/cart.php?checkout_error=missing_selection');
    exit();
}

$placeholders = implode(',', array_fill(0, count($selected_ids), '?'));
$sql = "
    SELECT c.product_id, c.quantity, p.price, p.stock, p.name
    FROM cart c
    JOIN products p ON p.id = c.product_id
    WHERE c.user_id = ? AND c.product_id IN ($placeholders)
";
$params = array_merge([$user_id], $selected_ids);
$stmt = $conn->prepare($sql);
$stmt->execute($params);
$items = $stmt->fetchAll(PDO::FETCH_ASSOC);

if (empty($items)) {
    header('Location: ../../cart/cart.php?checkout_error=invalid_selection');
    exit();
}

$total = 0.00;
foreach ($items as $item) {
    $total += ((float)$item['price'] * (int)$item['quantity']);
}

$order_code = 'ORD-' . strtoupper(substr(md5(uniqid((string)$user_id, true)), 0, 8));

try {
    $conn->beginTransaction();

    // Lock product rows so stock checks and decrements are consistent.
    $stockCheckStmt = $conn->prepare("SELECT id, name, stock FROM products WHERE id IN ($placeholders) FOR UPDATE");
    $stockCheckStmt->execute($selected_ids);
    $stockRows = $stockCheckStmt->fetchAll(PDO::FETCH_ASSOC);
    $stockById = [];
    foreach ($stockRows as $row) {
        $stockById[(int)$row['id']] = $row;
    }

    foreach ($items as $item) {
        $productId = (int)$item['product_id'];
        $requiredQty = (int)$item['quantity'];
        $availableStock = isset($stockById[$productId]) ? (int)$stockById[$productId]['stock'] : 0;

        if ($availableStock < $requiredQty) {
            throw new RuntimeException('Not enough stock for ' . ($stockById[$productId]['name'] ?? 'selected item'));
        }
    }

    $stmt = $conn->prepare('INSERT INTO orders (order_code, user_id, total_amount, status) VALUES (:code, :uid, :total, :status)');
    $stmt->execute([
        ':code' => $order_code,
        ':uid' => $user_id,
        ':total' => $total,
        ':status' => 'Pending',
    ]);

    $order_id = (int)$conn->lastInsertId();

    $itemStmt = $conn->prepare('INSERT INTO order_items (order_id, product_id, quantity, unit_price, subtotal) VALUES (:oid, :pid, :qty, :price, :subtotal)');
    foreach ($items as $item) {
        $qty = (int)$item['quantity'];
        $price = (float)$item['price'];
        $itemStmt->execute([
            ':oid' => $order_id,
            ':pid' => (int)$item['product_id'],
            ':qty' => $qty,
            ':price' => $price,
            ':subtotal' => $price * $qty,
        ]);

        $stockUpdateStmt = $conn->prepare('UPDATE products SET stock = stock - :qty WHERE id = :pid');
        $stockUpdateStmt->execute([
            ':qty' => $qty,
            ':pid' => (int)$item['product_id'],
        ]);
    }

    $removeStmt = $conn->prepare("DELETE FROM cart WHERE user_id = ? AND product_id IN ($placeholders)");
    $removeStmt->execute($params);

    $conn->commit();
    header('Location: ../../cart/orderHistory.php?placed=1');
    exit();
} catch (Throwable $e) {
    if ($conn->inTransaction()) {
        $conn->rollBack();
    }
    $errorFlag = stripos($e->getMessage(), 'Not enough stock') !== false ? 'insufficient_stock' : 'payment_failed';
    header('Location: ../../cart/checkout.php?items=' . urlencode(implode(',', $selected_ids)) . '&checkout_error=' . $errorFlag);
    exit();
}
