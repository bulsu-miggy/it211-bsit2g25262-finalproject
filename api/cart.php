<?php
/**
 * UniMerch API — Cart CRUD
 * Session-based cart management
 */
require_once __DIR__ . '/../includes/db.php';
require_once __DIR__ . '/../includes/helpers.php';
require_once __DIR__ . '/../includes/auth.php';

header('Content-Type: application/json; charset=utf-8');

$pdo = db();
$method = getRequestMethod();
$sessionId = session_id();
$customerId = isCustomerLoggedIn() ? $_SESSION['customer_id'] : null;

switch ($method) {
    case 'GET':
        getCart($pdo, $sessionId, $customerId);
        break;
    case 'POST':
        addToCart($pdo, $sessionId, $customerId);
        break;
    case 'PUT':
        updateCartItem($pdo, $sessionId, $customerId);
        break;
    case 'DELETE':
        removeFromCart($pdo, $sessionId, $customerId);
        break;
    default:
        jsonResponse(['success' => false, 'message' => 'Method not allowed'], 405);
}

function getCart(PDO $pdo, string $sessionId, ?int $customerId): void {
    $where = $customerId ? "c.customer_id = ?" : "c.session_id = ?";
    $param = $customerId ?: $sessionId;

    $stmt = $pdo->prepare("
        SELECT c.id, c.product_id, c.quantity, c.size,
               p.name, p.price, p.image, p.stock, p.sizes AS available_sizes,
               cat.code AS category_code
        FROM cart c
        JOIN products p ON c.product_id = p.id
        JOIN categories cat ON p.category_id = cat.id
        WHERE {$where} AND p.status = 'active'
        ORDER BY c.created_at DESC
    ");
    $stmt->execute([$param]);
    $items = $stmt->fetchAll();

    $subtotal = 0;
    foreach ($items as &$item) {
        $item['image_url'] = BASE_URL . '/uploads/' . $item['image'];
        $item['line_total'] = $item['price'] * $item['quantity'];
        $item['available_sizes'] = $item['available_sizes'] ? json_decode($item['available_sizes'], true) : null;
        $subtotal += $item['line_total'];
    }

    jsonResponse([
        'success'  => true,
        'data'     => $items,
        'summary'  => [
            'item_count' => count($items),
            'subtotal'   => $subtotal,
            'total'      => $subtotal // Free shipping
        ]
    ]);
}

function addToCart(PDO $pdo, string $sessionId, ?int $customerId): void {
    $data = getJsonBody();
    $productId = (int) ($data['product_id'] ?? 0);
    $quantity  = max(1, (int) ($data['quantity'] ?? 1));
    $size      = $data['size'] ?? null;

    if (!$productId) {
        jsonResponse(['success' => false, 'message' => 'Product ID is required'], 400);
    }

    // Check product exists and has stock
    $product = $pdo->prepare("SELECT * FROM products WHERE id = ? AND status = 'active'");
    $product->execute([$productId]);
    $product = $product->fetch();

    if (!$product) {
        jsonResponse(['success' => false, 'message' => 'Product not found'], 404);
    }

    // Check if sizes are required
    $sizes = $product['sizes'] ? json_decode($product['sizes'], true) : null;
    if ($sizes && !$size) {
        jsonResponse(['success' => false, 'message' => 'Please select a size'], 400);
    }

    if ($sizes && $size && !in_array($size, $sizes)) {
        jsonResponse(['success' => false, 'message' => 'Invalid size selected'], 400);
    }

    if ($product['stock'] < $quantity) {
        jsonResponse(['success' => false, 'message' => 'Insufficient stock. Only ' . $product['stock'] . ' left.'], 400);
    }

    // Check if already in cart (same product + same size)
    $where = $customerId ? "customer_id = ?" : "session_id = ?";
    $param = $customerId ?: $sessionId;

    $existing = $pdo->prepare("SELECT * FROM cart WHERE {$where} AND product_id = ? AND (size = ? OR (size IS NULL AND ? IS NULL))");
    $existing->execute([$param, $productId, $size, $size]);
    $existingItem = $existing->fetch();

    if ($existingItem) {
        $newQty = $existingItem['quantity'] + $quantity;
        if ($newQty > $product['stock']) {
            jsonResponse(['success' => false, 'message' => 'Cannot add more. Stock limit reached.'], 400);
        }
        $update = $pdo->prepare("UPDATE cart SET quantity = ? WHERE id = ?");
        $update->execute([$newQty, $existingItem['id']]);
    } else {
        $insert = $pdo->prepare("INSERT INTO cart (session_id, customer_id, product_id, quantity, size) VALUES (?, ?, ?, ?, ?)");
        $insert->execute([$sessionId, $customerId, $productId, $quantity, $size]);
    }

    // Get updated cart count
    $countStmt = $pdo->prepare("SELECT COALESCE(SUM(quantity), 0) FROM cart WHERE {$where}");
    $countStmt->execute([$param]);
    $cartCount = (int) $countStmt->fetchColumn();

    jsonResponse([
        'success'    => true,
        'message'    => 'Added to cart!',
        'cart_count' => $cartCount
    ]);
}

function updateCartItem(PDO $pdo, string $sessionId, ?int $customerId): void {
    $data = getJsonBody();
    $cartId  = (int) ($data['cart_id'] ?? 0);
    $quantity = max(1, (int) ($data['quantity'] ?? 1));

    if (!$cartId) {
        jsonResponse(['success' => false, 'message' => 'Cart item ID is required'], 400);
    }

    $where = $customerId ? "customer_id = ?" : "session_id = ?";
    $param = $customerId ?: $sessionId;

    // Verify ownership and get item
    $stmt = $pdo->prepare("SELECT c.*, p.stock FROM cart c JOIN products p ON c.product_id = p.id WHERE c.id = ? AND {$where}");
    $stmt->execute([$cartId, $param]);
    $item = $stmt->fetch();

    if (!$item) {
        jsonResponse(['success' => false, 'message' => 'Cart item not found'], 404);
    }

    if ($quantity > $item['stock']) {
        jsonResponse(['success' => false, 'message' => 'Only ' . $item['stock'] . ' available'], 400);
    }

    $update = $pdo->prepare("UPDATE cart SET quantity = ? WHERE id = ?");
    $update->execute([$quantity, $cartId]);

    jsonResponse(['success' => true, 'message' => 'Cart updated']);
}

function removeFromCart(PDO $pdo, string $sessionId, ?int $customerId): void {
    $cartId = (int) ($_GET['id'] ?? 0);

    if (!$cartId) {
        jsonResponse(['success' => false, 'message' => 'Cart item ID is required'], 400);
    }

    $where = $customerId ? "customer_id = ?" : "session_id = ?";
    $param = $customerId ?: $sessionId;

    $delete = $pdo->prepare("DELETE FROM cart WHERE id = ? AND {$where}");
    $delete->execute([$cartId, $param]);

    jsonResponse(['success' => true, 'message' => 'Item removed from cart']);
}
