<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);
ini_set('html_errors', 0);

try {
    ob_start();
    require_once __DIR__ . '/ProductManager.php';

    if (session_status() === PHP_SESSION_NONE) {
        session_start();
    }

    $productManager = new ProductManager();
    ob_end_clean();

    header('Content-Type: application/json');

    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        if (!isset($_POST['action'])) {
            echo json_encode(['success' => false, 'error' => 'No action specified']);
            exit;
        }

        $action = $_POST['action'];
        switch ($action) {
            case 'add':
                $product_id = (int) ($_POST['product_id'] ?? 0);
                $quantity = isset($_POST['quantity']) ? (int) $_POST['quantity'] : 1;

                if ($product_id <= 0) {
                    echo json_encode(['success' => false, 'message' => 'Invalid product ID.']);
                    exit;
                }

                $product = $productManager->getProductById($product_id);
                if (!$product) {
                    echo json_encode(['success' => false, 'message' => 'Product not found']);
                    exit;
                }

                $result = $productManager->addToCart($product_id, $quantity);
                echo json_encode([
                    'success' => $result,
                    'message' => $result ? 'Product added to cart.' : 'Failed to add product to cart.'
                ]);
                exit;

            case 'update_quantity':
                $product_id = filter_input(INPUT_POST, 'product_id', FILTER_VALIDATE_INT);
                $quantity = isset($_POST['quantity']) ? (int) $_POST['quantity'] : 0;

                if ($product_id === false || $product_id === null) {
                    echo json_encode(['success' => false, 'message' => 'Invalid product ID.']);
                    exit;
                }

                if ($quantity <= 0) {
                    $result = $productManager->removeCartItem($product_id);
                } else {
                    $result = $productManager->updateQuantity($product_id, $quantity);
                }

                echo json_encode(['success' => $result]);
                exit;

            case 'place_order':
                $payment_mode = $_POST['payment_mode'] ?? null;
                $location = $_POST['location'] ?? null;
                $payment_details = $_POST['payment_details'] ?? '{}';

                $cart_items = $productManager->getCartItems();
                $subtotal = $productManager->getCartTotal();
                $delivery_fee = 2.99;
                $total = $subtotal + $delivery_fee;

                if (empty($cart_items)) {
                    $product_id = filter_input(INPUT_POST, 'product_id', FILTER_VALIDATE_INT);
                    $quantity = isset($_POST['quantity']) ? (int) $_POST['quantity'] : 1;

                    if ($product_id === false || $product_id === null) {
                        echo json_encode(['success' => false, 'error' => 'Invalid product ID']);
                        exit;
                    }

                    $product = $productManager->getProductById($product_id);
                    if (!$product) {
                        echo json_encode(['success' => false, 'error' => 'Product not found']);
                        exit;
                    }

                    $cart_items = [[
                        'product_id' => $product['id'],
                        'title' => $product['name'],
                        'price' => $product['price'],
                        'quantity' => $quantity
                    ]];
                    $subtotal = $product['price'] * $quantity;
                    $total = $subtotal + $delivery_fee;
                }

                if (empty($cart_items)) {
                    echo json_encode(['success' => false, 'error' => 'No items in cart']);
                    exit;
                }

                $order_data = [
                    'items' => $cart_items,
                    'subtotal' => $subtotal,
                    'delivery_fee' => $delivery_fee,
                    'total' => $total,
                    'payment_mode' => $payment_mode,
                    'payment_details' => $payment_details,
                    'location' => $location
                ];

                $result = $productManager->createOrder($order_data);
                echo json_encode($result);
                exit;

            default:
                echo json_encode(['success' => false, 'error' => 'Unknown action']);
                exit;
        }
    }

    if ($_SERVER['REQUEST_METHOD'] === 'GET') {
        if (!isset($_GET['action'])) {
            echo json_encode(['error' => 'No action specified']);
            exit;
        }

        switch ($_GET['action']) {
            case 'get_count':
            case 'getCount':
                $cart_items = $productManager->getCartItems();
                $count = 0;
                foreach ($cart_items as $item) {
                    $count += $item['quantity'];
                }
                echo json_encode(['count' => $count]);
                exit;

            case 'get_totals':
                $subtotal = $productManager->getCartTotal();
                $delivery_fee = 2.99;
                $total = $subtotal + $delivery_fee;
                echo json_encode(['subtotal' => $subtotal, 'total' => $total]);
                exit;

            default:
                echo json_encode(['error' => 'Unknown action']);
                exit;
        }
    }

    echo json_encode(['success' => false, 'error' => 'Unsupported request method']);
    exit;
} catch (Throwable $e) {
    if (ob_get_length()) {
        ob_end_clean();
    }
    header('Content-Type: application/json');
    echo json_encode(['success' => false, 'error' => $e->getMessage()]);
    exit;
}
