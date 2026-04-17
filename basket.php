<?php 
session_start();
$is_logged_in = isset($_SESSION['user_id']);

if ($is_logged_in) {
    require_once 'db/connection.php';
    if (empty($_SESSION['cart'])) {
        // Determine correct Join column
        $pk = "product_id";
        $check = $conn->query("SHOW COLUMNS FROM candles LIKE 'product_id'");
        if ($check->rowCount() == 0) $pk = "id";

        // Fall back if the existing basket schema does not yet include `size`
        $basketHasSize = $conn->query("SHOW COLUMNS FROM basket LIKE 'size'")->rowCount() > 0;
        $selectFields = $basketHasSize ? "b.quantity, b.size, b.product_id" : "b.quantity, b.product_id";

        $stmt = $conn->prepare(
            "SELECT $selectFields, p.name, p.price, p.image_url AS image
             FROM basket b
             JOIN candles p ON b.product_id = p.$pk
             WHERE b.user_id = ?"
        );
        $stmt->execute([$_SESSION['user_id']]);
        $dbCart = $stmt->fetchAll(PDO::FETCH_ASSOC);

        if ($dbCart) {
            $_SESSION['cart'] = [];
            foreach ($dbCart as $item) {
                $unit_price = (float)$item['price'];
                if (($item['size'] ?? 'Small') === 'Medium') {
                    $unit_price += 20;
                } elseif (($item['size'] ?? 'Small') === 'Large') {
                    $unit_price += 40;
                }

                $cartKey = $item['product_id'] . '_' . ($item['size'] ?? 'Small');
                $_SESSION['cart'][$cartKey] = [
                    'product_id' => $item['product_id'],
                    'name' => $item['name'],
                    'price' => $unit_price,
                    'size' => $item['size'] ?? 'Small',
                    'quantity' => (int)$item['quantity'],
                    'image' => $item['image'] ?? ''
                ];
            }
        }
    }
}

if ($is_logged_in) {
    include 'includes/member_header.php';
} else {
    include 'guest_header/guest_header.php';
}

$cart_count = count($_SESSION['cart'] ?? []);
$summary_total = 0;
if (!empty($_SESSION['cart'])) {
    foreach ($_SESSION['cart'] as $item) {
        $summary_total += $item['price'] * $item['quantity'];
    }
}
?>

<script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

<div class="basket-container">
    <h1 class="basket-header">Shopping Basket</h1>
    <p class="item-count"><?= $cart_count ?> Item<?= $cart_count === 1 ? '' : 's' ?></p>

    <div class="basket-layout">
        <div class="table-container">
            <table class="cart-table">
                <thead>
                    <tr>
                        <th>Product</th>
                        <th class="table-align-center">Quantity</th>
                        <th>Price</th>
                        <th class="table-align-right">Total</th>
                    </tr>
                </thead>
                <tbody>
                    <?php 
                    $subtotal = 0;
                    if(!empty($_SESSION['cart'])):
                        foreach($_SESSION['cart'] as $key => $item): 
                            $total = $item['price'] * $item['quantity'];
                            $subtotal += $total;
                            
                            $prod_id = $item['product_id'] ?? null;
                            $details_url = $prod_id ? "product_details.php?id=" . $prod_id : "shop.php";
                    ?>
                    <tr>
                        <td>
                            <div class="product-info">
                                <a href="<?= $details_url ?>">
                                    <img src="<?= $item['image'] ?>" alt="<?= $item['name'] ?>">
                                </a>
                                <div class="product-details">
                                    <a href="<?= $details_url ?>" class="product-link">
                                        <h4><?= $item['name'] ?></h4>
                                    </a>
                                    <p><?= $item['size'] ?> Edition</p>
                                </div>
                            </div>
                        </td>
                        <td class="table-align-center">
                            <div class="qty-selector">
                                <button type="button" class="qty-btn" onclick="updateQty('<?= $key ?>', -1)">−</button>
                                <input type="text" class="qty-input" value="<?= $item['quantity'] ?>" readonly>
                                <button type="button" class="qty-btn" onclick="updateQty('<?= $key ?>', 1)">+</button>
                            </div>
                        </td>
                        <td>
                            <div class="price-col">
                                <span class="price-val">₱<?= number_format($item['price'], 2) ?></span>
                                <a href="javascript:void(0)" class="remove-btn js-remove-item" data-key="<?= $key ?>">Remove</a>
                            </div>
                        </td>
                        <td class="table-align-right font-semibold">₱<?= number_format($total, 2) ?></td>
                    </tr>
                    <?php endforeach; else: ?>
                        <tr><td colspan="4" class="table-empty">Your basket is empty.</td></tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>

        <div class="summary-container">
            <div class="summary-card">
                <h2>Order Summary</h2>
                <div class="summary-line"><span>Subtotal</span><span>₱<?= number_format($summary_total, 2) ?></span></div>
                <div class="summary-line"><span>Shipping</span><span class="summary-note">FREE</span></div>
                <div class="summary-line total-line"><span>TOTAL</span><span>₱<?= number_format($summary_total, 2) ?></span></div>
                
                <button id="btn-proceed-checkout" class="checkout-btn" data-count="<?= $cart_count ?>">Proceed to checkout</button>
                <p class="text-center text-small mt-15">
                    <a href="shop.php" class="btn-link-small">Continue Shopping</a>
                </p>
            </div>
        </div>
    </div>
</div>

<script src="js/checkout_process.js"></script>

<?php 
if ($is_logged_in) {
    include 'includes/member_footer.php';
} else {
    include 'guest_header/guestfooter.php';
}
?>