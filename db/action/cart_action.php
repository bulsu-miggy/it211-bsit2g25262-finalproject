<?php
require_once __DIR__ . '/../../db/action/dbconfig.php';
session_start();

if (!isset($_SESSION['username'])) {
    echo json_encode(['success' => false, 'message' => 'Please log in first']);
    exit();
}

// Get user 
$stmt = $conn->prepare("SELECT id FROM login WHERE username = :username");
$stmt->execute([':username' => $_SESSION['username']]);
$user = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$user) {
    echo json_encode(['success' => false, 'message' => 'User not found']);
    exit();
}

$user_id = $user['id'];
$action  = $_POST['action'] ?? '';
$response = ['success' => false, 'message' => 'Unknown action'];

try {
    if ($action === 'add') {
        $product_id = (int)($_POST['product_id'] ?? 0);
        $quantity   = max(1, (int)($_POST['quantity'] ?? 1));

        if ($product_id <= 0) {
            $response = ['success' => false, 'message' => 'Invalid product'];
        } else {
            $stockStmt = $conn->prepare("SELECT stock FROM products WHERE id = :pid LIMIT 1");
            $stockStmt->execute([':pid' => $product_id]);
            $availableStock = (int)($stockStmt->fetchColumn() ?: 0);

            if ($availableStock <= 0) {
                $response = ['success' => false, 'message' => 'This item is out of stock'];
            } else {
                $existingStmt = $conn->prepare("SELECT quantity FROM cart WHERE user_id = :uid AND product_id = :pid LIMIT 1");
                $existingStmt->execute([':uid' => $user_id, ':pid' => $product_id]);
                $existingQty = (int)($existingStmt->fetchColumn() ?: 0);
                $newQty = $existingQty + $quantity;

                if ($newQty > $availableStock) {
                    $response = ['success' => false, 'message' => 'Only ' . $availableStock . ' item(s) are available in stock'];
                } else {
                    $stmt = $conn->prepare("
                        INSERT INTO cart (user_id, product_id, quantity)
                        VALUES (:uid, :pid, :qty)
                        ON DUPLICATE KEY UPDATE quantity = quantity + :qty
                    ");
                    $stmt->execute([':uid' => $user_id, ':pid' => $product_id, ':qty' => $quantity]);
                    $response = ['success' => true, 'message' => 'Item added to basket!'];
                }
            }
        }

    } elseif ($action === 'update') {
        $product_id = (int)($_POST['product_id'] ?? 0);
        $quantity = (int)($_POST['quantity'] ?? 1);

        if ($quantity <= 0) {
            $stmt = $conn->prepare("DELETE FROM cart WHERE user_id = :uid AND product_id = :pid");
            $stmt->execute([':uid' => $user_id, ':pid' => $product_id]);
            $response = ['success' => true, 'message' => 'Item removed from cart'];
        } else {
            $stockStmt = $conn->prepare("SELECT stock FROM products WHERE id = :pid LIMIT 1");
            $stockStmt->execute([':pid' => $product_id]);
            $availableStock = (int)($stockStmt->fetchColumn() ?: 0);

            if ($availableStock <= 0) {
                $response = ['success' => false, 'message' => 'This item is out of stock'];
            } elseif ($quantity > $availableStock) {
                $response = ['success' => false, 'message' => 'Only ' . $availableStock . ' item(s) are available in stock'];
            } else {
                $stmt = $conn->prepare("UPDATE cart SET quantity = :qty WHERE user_id = :uid AND product_id = :pid");
                $stmt->execute([':qty' => $quantity, ':uid' => $user_id, ':pid' => $product_id]);
                $response = ['success' => true, 'message' => 'Cart updated'];
            }
        }

    } elseif ($action === 'remove') {
        $product_id = (int)($_POST['product_id'] ?? 0);

        $stmt = $conn->prepare("DELETE FROM cart WHERE user_id = :uid AND product_id = :pid");
        $stmt->execute([':uid' => $user_id, ':pid' => $product_id]);
        $response = ['success' => true, 'message' => 'Item removed from cart'];

    } else {
        $response = ['success' => false, 'message' => 'Unknown action'];
    }

} catch (PDOException $e) {
    $response = ['success' => false, 'message' => 'Database error: ' . $e->getMessage()];
}

echo json_encode($response);
exit();
