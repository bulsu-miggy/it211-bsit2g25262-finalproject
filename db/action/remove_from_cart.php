<?php
session_start();
$key = $_GET['key'] ?? '';
if ($key && isset($_SESSION['cart'][$key])) {
    unset($_SESSION['cart'][$key]);

    if (isset($_SESSION['user_id'])) {
        require_once __DIR__ . '/../connection.php';
        $user_id = (int) $_SESSION['user_id'];

        list($product_id, $size) = array_pad(explode('_', $key, 2), 2, 'Small');
        $product_id = (int) $product_id;
        $size = trim($size) ?: 'Small';

        if ($product_id > 0) {
            $stmt = $conn->prepare("DELETE FROM basket WHERE user_id = ? AND product_id = ? AND size = ?");
            $stmt->execute([$user_id, $product_id, $size]);

            // If table does not have size or no row matched, attempt delete by product only
            if ($stmt->rowCount() === 0) {
                $stmt = $conn->prepare("DELETE FROM basket WHERE user_id = ? AND product_id = ?");
                $stmt->execute([$user_id, $product_id]);
            }
        }
    }
}
header("Location: ../../basket.php");
exit();