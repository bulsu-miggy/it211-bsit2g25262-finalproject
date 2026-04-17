<?php
// 1. Start session and set JSON header immediately
session_start();

// 2. Ensure clean JSON response and suppress non-fatal output
error_reporting(0);
ini_set('display_errors', '0');
header('Content-Type: application/json; charset=utf-8');
ob_start();

// 3. Include database connection
include __DIR__ . '/../connection.php';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    
// 3. Clean output helper
function sendJson($payload) {
    if (ob_get_length()) {
        ob_clean();
    }
    header('Content-Type: application/json; charset=utf-8');
    echo json_encode($payload);
    exit();
}

// 4. Sanitize and Validate Input
    $product_id = isset($_POST['product_id']) ? $_POST['product_id'] : null;
    $quantity   = isset($_POST['quantity']) ? (int)$_POST['quantity'] : 1;
    $size       = isset($_POST['size']) ? $_POST['size'] : 'Small';
    $user_id    = isset($_SESSION['user_id']) ? $_SESSION['user_id'] : null;

    // Basic Validation check
    if (!$product_id || $quantity < 1) {
        sendJson(['status' => 'error', 'message' => 'Invalid product selection.']);
    }

    // 4. Initialize session cart
    if (!isset($_SESSION['cart'])) { $_SESSION['cart'] = []; }
    $cart_key = $product_id . "_" . $size;

    // 5. Logic: Fetch product details from Database (Defensive Column Detection)
    $pk = "product_id";
    $check = $conn->query("SHOW COLUMNS FROM candles LIKE 'product_id'");
    if ($check->rowCount() == 0) $pk = "id";
    
    try {
        // Removed COALESCE and img_path because it doesn't exist in your connection.php schema
        $stmt = $conn->prepare("SELECT name, price, image_url, stock_qty FROM candles WHERE $pk = ?");
        $stmt->execute([$product_id]);
        $product = $stmt->fetch();

        if (!$product) {
            sendJson(['status' => 'error', 'message' => 'Product not found.']);
        }

        $stock_qty = isset($product['stock_qty']) ? (int)$product['stock_qty'] : null;
        $existing_quantity = 0;

        if (!empty($_SESSION['cart'])) {
            foreach ($_SESSION['cart'] as $cart_item) {
                if ((int)$cart_item['product_id'] === (int)$product_id) {
                    $existing_quantity += (int)$cart_item['quantity'];
                }
            }
        } elseif ($user_id) {
            $reserved_stmt = $conn->prepare("SELECT SUM(quantity) AS reserved FROM basket WHERE user_id = ? AND product_id = ?");
            $reserved_stmt->execute([$user_id, $product_id]);
            $existing_quantity = (int)$reserved_stmt->fetchColumn();
        }

        if ($stock_qty !== null) {
            $available = $stock_qty - $existing_quantity;
            if ($available <= 0) {
                sendJson(['status' => 'error', 'message' => 'This product is out of stock.']);
            }

            if ($quantity > $available) {
                sendJson(['status' => 'error', 'message' => 'Only ' . $available . ' item' . ($available === 1 ? '' : 's') . ' left in stock.']);
            }
        }
    } catch (PDOException $e) {
        sendJson(['status' => 'error', 'message' => 'Database Query Error: ' . $e->getMessage()]);
    }

    // 6. Save to Session (For immediate UI updates)
    $final_price = (float)$product['price'];
    if ($size == 'Medium') $final_price += 20;
    if ($size == 'Large')  $final_price += 40;

    if (isset($_SESSION['cart'][$cart_key])) {
        $_SESSION['cart'][$cart_key]['quantity'] += $quantity;
    } else {
        $_SESSION['cart'][$cart_key] = [
            'product_id' => $product_id,
            'name'       => $product['name'],
            'price'      => $final_price,
            'size'       => $size,
            'quantity'   => $quantity,
            'image'      => $product['image_url'] ?? ''
        ];
    }

    // --- STEP 6.5: FOLLOW YOUR DATABASE SCHEMA ---
    // If user is logged in, sync this to the 'basket' table
    if ($user_id) {
        try {
            // Check if item already exists in DB basket for this user and selected size
            $check = $conn->prepare("SELECT basket_id FROM basket WHERE user_id = ? AND product_id = ? AND size = ?");
            $check->execute([$user_id, $product_id, $size]);
            $exists = $check->fetch();

            if ($exists) {
                // Update quantity in DB
                $upd = $conn->prepare("UPDATE basket SET quantity = quantity + ? WHERE user_id = ? AND product_id = ? AND size = ?");
                $upd->execute([$quantity, $user_id, $product_id, $size]);
            } else {
                // Insert new row into your 'basket' table
                $ins = $conn->prepare("INSERT INTO basket (user_id, product_id, quantity, size) VALUES (?, ?, ?, ?)");
                $ins->execute([$user_id, $product_id, $quantity, $size]);
            }
        } catch (PDOException $e) {
            // Check for specific foreign key constraint error related to product_id in basket
            if (strpos($e->getMessage(), 'Cannot add or update a child row: a foreign key constraint fails') !== false && strpos($e->getMessage(), '`basket_ibfk_2`') !== false) {
                sendJson(['status' => 'error', 'message' => "Database Error: Failed to add item to basket. This usually means your 'candles' table's primary key is still named 'id' instead of 'product_id', or your 'candles' table's image column is 'img_path' instead of 'image_url'. Please run the SQL commands to update your database schema."]);
            } else {
                sendJson(['status' => 'error', 'message' => "Database Error: " . $e->getMessage()]);
            }
        }
    }

    // 7. Calculate total count for header
    $cart_count = 0;
    foreach ($_SESSION['cart'] as $item) { $cart_count += $item['quantity']; }

    $remaining_stock = null;
    if ($stock_qty !== null) {
        $remaining_stock = max(0, $stock_qty - $existing_quantity - $quantity);
    }

    // 8. Return Success JSON
    $response = [
        'status' => 'success',
        'message' => 'The ' . $product['name'] . ' scent has been added to your basket.',
        'count' => $cart_count
    ];

    if ($remaining_stock !== null) {
        $response['stock_qty'] = $remaining_stock;
    }

    sendJson($response);

} else {
    echo json_encode(['status' => 'error', 'message' => 'Invalid request.']);
    exit();
}