<?php
session_start();
$key = $_GET['key'] ?? '';
$change = (int)($_GET['change'] ?? 0);

if ($key && isset($_SESSION['cart'][$key])) {
    $current_qty = (int)$_SESSION['cart'][$key]['quantity'];
    $desired_qty = $current_qty + $change;

    if ($desired_qty < 1) {
        $desired_qty = 1;
    }

    if ($change > 0) {
        require_once __DIR__ . '/../connection.php';
        $product_id = (int)explode('_', $key, 2)[0];
        $pk = "product_id";
        $check = $conn->query("SHOW COLUMNS FROM candles LIKE 'product_id'");
        if ($check->rowCount() == 0) {
            $pk = "id";
        }

        $stock_stmt = $conn->prepare("SELECT stock_qty FROM candles WHERE $pk = ?");
        $stock_stmt->execute([$product_id]);
        $stock_qty = (int)$stock_stmt->fetchColumn();

        $reserved_qty = 0;
        foreach ($_SESSION['cart'] as $cartItem) {
            if ((int)$cartItem['product_id'] === $product_id) {
                $reserved_qty += (int)$cartItem['quantity'];
            }
        }

        $reserved_qty -= $current_qty;
        $allowed_qty = max(0, $stock_qty - $reserved_qty);
        if ($desired_qty > $allowed_qty) {
            $desired_qty = $allowed_qty > 0 ? $allowed_qty : $current_qty;
        }
    }

    $_SESSION['cart'][$key]['quantity'] = $desired_qty;

    if (isset($_SESSION['user_id'])) {
        require_once __DIR__ . '/../connection.php';
        $user_id = (int) $_SESSION['user_id'];
        list($product_id, $size) = array_pad(explode('_', $key, 2), 2, 'Small');
        $product_id = (int) $product_id;
        $size = trim($size) ?: 'Small';

        if ($product_id > 0) {
            if ($desired_qty <= 0) {
                $stmt = $conn->prepare("DELETE FROM basket WHERE user_id = ? AND product_id = ? AND size = ?");
                $stmt->execute([$user_id, $product_id, $size]);
                if ($stmt->rowCount() === 0) {
                    $stmt = $conn->prepare("DELETE FROM basket WHERE user_id = ? AND product_id = ?");
                    $stmt->execute([$user_id, $product_id]);
                }
            } else {
                $stmt = $conn->prepare("UPDATE basket SET quantity = ? WHERE user_id = ? AND product_id = ? AND size = ?");
                $stmt->execute([$desired_qty, $user_id, $product_id, $size]);
                if ($stmt->rowCount() === 0) {
                    $stmt = $conn->prepare("UPDATE basket SET quantity = ? WHERE user_id = ? AND product_id = ?");
                    $stmt->execute([$desired_qty, $user_id, $product_id]);
                }
            }
        }
    }
}

header('Location: ../../basket.php');
exit();