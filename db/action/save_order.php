<?php
/**
 * SOLIS - ORDER PROCESSING ENGINE
 * * WHAT IS THIS FOR?
 * This script is the "final bridge" between the checkout UI and the Database.
 * It takes temporary data (Session/Basket) and turns it into a permanent Order Record.
 *
 * CONNECTIONS:
 * 1. FROM: checkout.php (Step 3 - "Place Order" button submits here)
 * 2. TO: Database Tables (orders, order_items, user_addresses, basket)
 * 3. REDIRECT: checkout.php (Step 4 - Redirects back to show success)
 */

session_start();
require_once __DIR__ . '/../connection.php'; // CONNECTS TO: your MySQL database

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    
    // --- 1. AUTHENTICATION ---
    // Verifies the user is still logged in before saving
    if (!isset($_SESSION['user_id'])) {
        header("Location: ../../login.php");
        exit();
    }

    $user_id = $_SESSION['user_id'];
    
    // Retrieve shipping data captured in Step 1/2
    try {
        $shipping = $_SESSION['checkout_shipping'] ?? null;

        if (!$shipping || empty(trim($shipping['full_name'] ?? '')) || empty(trim($shipping['address'] ?? '')) || empty(trim($shipping['city'] ?? '')) || empty(trim($shipping['postal_code'] ?? ''))) {
            throw new Exception("Shipping information is incomplete. Please go back to the shipping step.");
        }

        $apartment = $shipping['apartment'] ?? null;

        // Start Transaction: Ensures no "half-saved" orders if the power/internet cuts out
        $conn->beginTransaction();

        // --- 2. CALCULATE TOTAL ---
        // Re-calculates from $_SESSION['cart'] or the stored database basket.
        $cart_items = $_SESSION['cart'] ?? [];

        if (empty($cart_items)) {
            // Defensive check: Detect if the column is 'product_id' or 'id'
            $pk = "product_id";
            $check = $conn->query("SHOW COLUMNS FROM candles LIKE 'product_id'");
            if ($check->rowCount() == 0) $pk = "id";
            $basketHasSize = $conn->query("SHOW COLUMNS FROM basket LIKE 'size'")->rowCount() > 0;
            $sizeField = $basketHasSize ? ", b.size" : "";
            $basket_stmt = $conn->prepare(
                "SELECT b.quantity$sizeField, p.$pk AS product_id, p.price
                 FROM basket b
                 JOIN candles p ON b.product_id = p.$pk
                 WHERE b.user_id = ?"
            );
            $basket_stmt->execute([$user_id]);
            $db_items = $basket_stmt->fetchAll(PDO::FETCH_ASSOC);
            
            // Calculate final price for DB-sourced items ONLY (Session items are already final)
            foreach ($db_items as $db_item) {
                $price = (float)$db_item['price'];
                if (($db_item['size'] ?? 'Small') === 'Medium') $price += 20;
                elseif (($db_item['size'] ?? 'Small') === 'Large') $price += 40;
                
                $cart_items[] = array_merge($db_item, ['price' => $price]);
            }
        }

        if (empty($cart_items)) {
            throw new Exception("Your cart is empty.");
        }

        $total_amount = 0;
        foreach ($cart_items as $item) {
            $total_amount += ($item['price'] * $item['quantity']);
        }

        // --- 2.5 VALIDATE STOCK AVAILABILITY ---
        $pk = "product_id";
        $check = $conn->query("SHOW COLUMNS FROM candles LIKE 'product_id'");
        if ($check->rowCount() == 0) {
            $pk = "id";
        }

        $product_quantities = [];
        foreach ($cart_items as $item) {
            $product_id = (int)$item['product_id'];
            if ($product_id <= 0) {
                continue;
            }
            $product_quantities[$product_id] = ($product_quantities[$product_id] ?? 0) + (int)$item['quantity'];
        }

        foreach ($product_quantities as $product_id => $quantity) {
            $stock_stmt = $conn->prepare("SELECT stock_qty, name FROM candles WHERE $pk = ? FOR UPDATE");
            $stock_stmt->execute([$product_id]);
            $product_row = $stock_stmt->fetch(PDO::FETCH_ASSOC);

            if (!$product_row) {
                throw new Exception("One of the products in your basket is unavailable.");
            }

            $available_stock = isset($product_row['stock_qty']) ? (int)$product_row['stock_qty'] : 0;
            $product_name = $product_row['name'] ?? 'Product';

            if ($available_stock <= 0) {
                throw new Exception("{$product_name} is currently out of stock. Please remove it from your cart.");
            }

            if ($quantity > $available_stock) {
                throw new Exception("There are only {$available_stock} left of {$product_name}. Please update your cart.");
            }
        }

        // --- 3. SAVE SHIPPING ADDRESS ---
        // CONNECTS TO TABLE: `user_addresses`
        if ($shipping) {
            // Check if this exact address already exists to avoid duplicates
            $check_addr = $conn->prepare("SELECT address_id FROM user_addresses WHERE user_id = ? AND street_address = ? AND zip_code = ? LIMIT 1");
            $check_addr->execute([$user_id, $shipping['address'], $shipping['postal_code']]);
            
            if (!$check_addr->fetch()) {
                $addr_sql = "INSERT INTO user_addresses (user_id, full_name, street_address, apartment, city, zip_code, phone_number, label) 
                             VALUES (?, ?, ?, ?, ?, ?, ?, 'Shipping Address')";
                $addr_stmt = $conn->prepare($addr_sql);
                $addr_stmt->execute([
                    $user_id,
                    $shipping['full_name'],
                    $shipping['address'],
                    $apartment,
                    $shipping['city'],
                    $shipping['postal_code'],
                    $shipping['phone'] ?? ''
                ]);
            }
        }

        // --- 4. INSERT INTO ORDERS (Initial Save) ---
        // CONNECTS TO TABLE: `orders`
        // NOTE: We leave 'order_number' empty first to get the Auto-Increment ID
        $sql = "INSERT INTO orders (user_id, total_amount, status) 
                VALUES (:uid, :total, 'Pending')";
        $stmt = $conn->prepare($sql);
        $stmt->execute([
            ':uid'    => $user_id,
            ':total'  => $total_amount
        ]);
        
        // --- 5. GENERATE ID-BASED ORDER NUMBER ---
        // GETS FROM DB: The unique Primary Key (ID) just created
        $new_order_id = $conn->lastInsertId();

        // [NEW ADDITION] --- SAVE INDIVIDUAL ITEMS ---
        // CONNECTS TO TABLE: `order_items`
        // PURPOSE: Records exactly which products were in each order
        $item_sql = "INSERT INTO order_items (order_id, product_id, quantity, price, size) 
                     VALUES (?, ?, ?, ?, ?)";
        $item_stmt = $conn->prepare($item_sql);

        foreach ($cart_items as $item) {
            $item_stmt->execute([
                $new_order_id,
                $item['product_id'],
                $item['quantity'],
                $item['price'],
                $item['size'] ?? 'Small'
            ]);
        }

        // --- 5.5 DECREMENT STOCK QUANTITIES ---
        $stock_update = $conn->prepare("UPDATE candles SET stock_qty = stock_qty - ? WHERE $pk = ?");
        foreach ($product_quantities as $product_id => $quantity) {
            $stock_update->execute([$quantity, $product_id]);
        }
        
        // CHANGE: No longer random. Now it's "SOLIS-" + the actual Database ID
        $order_number = "SOLIS-" . $new_order_id; 

        // Update the row with the clean ID-based number
        $update_sql = "UPDATE orders SET order_number = ? WHERE order_id = ?";
        $conn->prepare($update_sql)->execute([$order_number, $new_order_id]);

        // --- 6. CLEANUP ---
        // A. CONNECTS TO TABLE: `basket` (Deletes the items from the persistent DB table)
        $clear_db = $conn->prepare("DELETE FROM basket WHERE user_id = ?");
        $clear_db->execute([$user_id]);

        // B. CONNECTS TO: Browser Session (Unsets variables so the cart icon shows 0)
        unset($_SESSION['cart']);
        unset($_SESSION['checkout_shipping']);
        unset($_SESSION['checkout_payment']);

        // Finalize DB changes
        $conn->commit();

        // --- 7. FINAL REDIRECT ---
        // CONNECTS TO: checkout.php Step 4
        // We pass the numeric order_id so the success page can query the DB for "SOLIS-XX"
        header("Location: ../../checkout.php?step=4&order_id=" . $new_order_id . "&order_no=" . urlencode($order_number));
        exit();

    } catch (Exception $e) {
        if ($conn->inTransaction()) { $conn->rollBack(); }
        header('Content-Type: text/html'); // Ensure HTML is rendered for the error message
        // Check for specific foreign key constraint error related to product_id in order_items
        if (strpos($e->getMessage(), 'Cannot add or update a child row: a foreign key constraint fails') !== false && strpos($e->getMessage(), '`order_items_ibfk_2`') !== false) {
            die("Order Error: Failed to add order items. This usually means your 'candles' table's primary key is still named 'id' instead of 'product_id', or your 'candles' table's image column is 'img_path' instead of 'image_url'.<br><br>Please run the following SQL commands in phpMyAdmin (SQL tab) to fix your database schema:<br><br><code>ALTER TABLE candles CHANGE COLUMN id product_id INT(11) AUTO_INCREMENT;</code><br><code>ALTER TABLE candles CHANGE COLUMN img_path image_url TEXT;</code><br><br>Original Error: " . htmlspecialchars($e->getMessage()));
        } else {
            die("Order Error: " . htmlspecialchars($e->getMessage()));
        }
    }
}