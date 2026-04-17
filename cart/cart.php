<?php
require_once '../db/action/dbconfig.php';
session_start();

if (!isset($_SESSION['username'])) {
    header('Location: ../loginpage.php');
    exit();
}

$stmt = $conn->prepare("SELECT * FROM login WHERE username = :username");
$stmt->execute([':username' => $_SESSION['username']]);
$user_data = $stmt->fetch(PDO::FETCH_ASSOC);
$user_id = $user_data['id'];

//fetch cart items joined with product details
$stmt = $conn->prepare("
    SELECT c.id AS cart_id, c.quantity, c.product_id,
           p.name, p.color, p.size, p.price, p.image, p.stock
    FROM cart c
    JOIN products p ON c.product_id = p.id
    WHERE c.user_id = :uid
    ORDER BY c.added_at DESC
");
$stmt->execute([':uid' => $user_id]);
$cart_items = $stmt->fetchAll(PDO::FETCH_ASSOC);

$cart_count = count($cart_items);
$subtotal = array_sum(array_map(fn($i) => $i['price'] * $i['quantity'], $cart_items));

function escape($v) { return htmlspecialchars((string)$v, ENT_QUOTES, 'UTF-8'); }

function resolve_product_image_path_cart($rawImage)
{
    $value = trim((string) $rawImage);
    if ($value === '') {
        return '../assets2/adidasblablabla.png';
    }

    $value = str_replace('\\', '/', $value);
    if (preg_match('#^(https?:)?//#i', $value) || strpos($value, 'data:') === 0) {
        return $value;
    }

    if (strpos($value, 'assets2/') === 0 || strpos($value, 'images/') === 0) {
        return '../' . ltrim($value, '/');
    }

    return '../images/products/' . basename($value);
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="icon" type="image/png" href="../assets2/logo.png">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.1/font/bootstrap-icons.css">
    <link rel="stylesheet" href="../css/style2.css">
    <link rel="stylesheet" href="../css/master.css">
    <title>Basket – Laces</title>
</head>
<body>
<?php require_once '../includes/user/cart_nav.php'; ?>

<!-- TITLE -->
<div class="container mt-5">
    <div class="d-flex justify-content-between align-items-center">
        <div class="cart-title">My Basket (<span id="cart-count"><?php echo $cart_count; ?></span>)</div>
        <div><a href="orderHistory.php" class="text-dark text-decoration-none border-bottom border-dark border-3 pb-2">My Orders</a></div>
    </div>
    <hr class="border-dark border-2 mt-2">
</div>

<div class="container mt-4">
    <div class="row">

        <!-- left = cart items -->
        <div class="col-lg-8">
            <div class="card p-3 border-dark border-3">
                <div class="d-flex justify-content-between align-items-center px-3 mb-2 fw-semibold">
                    <div class="ms-5 text-secondary">Product</div>
                    <div class="me-3 text-secondary">Quantity</div>
                </div>
                <hr class="mt-0">

                <?php if (empty($cart_items)): ?>
                <div class="text-center py-5 text-muted">
                    <i class="bi bi-cart-x fs-1 d-block mb-2"></i>
                    Your basket is empty. <a href="../product-list.php" style="color: #FFBF00; text-decoration: none;">Shop now</a>
                </div>
                <?php else: ?>
                <?php foreach ($cart_items as $item): ?>
                <?php
                    $img = escape(resolve_product_image_path_cart($item['image'] ?? ''));
                ?>
                <div class="card mb-3 p-3 border-dark border-3 cart-item"
                     data-product-id="<?php echo (int)$item['product_id']; ?>"
                     data-price="<?php echo (float)$item['price']; ?>"
                     data-stock="<?php echo (int)$item['stock']; ?>">
                    <div class="d-flex justify-content-between align-items-center">
                        <div class="d-flex align-items-center gap-3">
                            <input type="checkbox" class="item-checkbox mt-1"
                                   data-product-id="<?php echo (int)$item['product_id']; ?>"
                                   data-price="<?php echo (float)$item['price']; ?>">
                            <img src="<?php echo $img; ?>" alt="<?php echo escape($item['name']); ?>"
                                 width="70" class="rounded"
                                 onerror="this.src='../assets2/adidasblablabla.png'">
                            <div>
                                <h6 class="mb-1"><?php echo escape($item['name']); ?></h6>
                                <small class="text-muted">
                                    Size: <?php echo escape($item['size']); ?> |
                                    <?php echo escape($item['color']); ?>
                                </small>
                                <p class="mb-0 fw-bold item-unit-price">
                                    ₱<?php echo number_format($item['price'], 2); ?>
                                </p>
                            </div>
                        </div>
                        <div class="d-flex align-items-center gap-2">
                            <div class="quantity-pill d-flex align-items-center">
                                <button type="button" class="btn btn-sm decrease-btn"
                                        data-product-id="<?php echo (int)$item['product_id']; ?>">−</button>
                                <span class="px-2 quantity"><?php echo (int)$item['quantity']; ?></span>
                                <button type="button" class="btn btn-sm increase-btn"
                                        data-product-id="<?php echo (int)$item['product_id']; ?>">+</button>
                            </div>
                            <button type="button" class="btn btn-sm btn-outline-danger remove-btn"
                                    data-product-id="<?php echo (int)$item['product_id']; ?>"
                                    title="Remove">
                                <i class="bi bi-trash"></i>
                            </button>
                        </div>
                    </div>
                </div>
                <?php endforeach; ?>
                <?php endif; ?>
            </div>
        </div>

        <!-- order summary right side -->
        <div class="col-lg-4">
            <div class="card p-3 mb-3 border-dark border-3">
                <h5>Order Summary</h5>
                <hr>
                <div class="d-flex justify-content-between">
                    <span class="text-secondary">Items</span>
                    <span class="text-warning" id="summary-count">0</span>
                </div>
                <div class="mt-3" id="summary-items">
                    <?php foreach ($cart_items as $item): ?>
                    <div class="d-flex justify-content-between summary-row"
                         data-product-id="<?php echo (int)$item['product_id']; ?>">
                        <span class="text-secondary small"><?php echo escape($item['name']); ?> ×<span class="qty-label"><?php echo (int)$item['quantity']; ?></span></span>
                        <span class="fw-bold item-subtotal">₱<?php echo number_format($item['price'] * $item['quantity'], 2); ?></span>
                    </div>
                    <?php endforeach; ?>
                </div>
                <hr>
                <div class="d-flex justify-content-between fw-bold">
                    <span>TOTAL</span>
                    <span class="text-warning" id="summary-total">₱0.00</span>
                </div>
                <button id="checkout-btn" type="button" class="btn btn-custom-color w-100 mt-3 text-white fw-bolder shadow rounded-3">CHECKOUT</button>
            </div>

            <!-- payment method -->
            <div class="card p-3 border-dark border-3">
                <h5>Payment Method</h5>
                <hr>
                <div class="form-check mb-2">
                    <input class="form-check-input" type="radio" name="paymentMethod" value="cod" checked>
                    <label class="form-check-label">Cash on Delivery</label>
                </div>
                <hr>
                <div class="d-flex justify-content-between align-items-center mb-2">
                    <div class="form-check">
                        <input class="form-check-input" type="radio" name="paymentMethod" value="gcash">
                        <label class="form-check-label">GCash</label>
                    </div>
                    <img src="../assets2/gcash.png" alt="GCash" width="30">
                </div>
                <hr>
                <div class="d-flex justify-content-between align-items-center mb-2">
                    <div class="form-check">
                        <input class="form-check-input" type="radio" name="paymentMethod" value="card">
                        <label class="form-check-label">Credit/Debit Card</label>
                    </div>
                    <img src="../assets2/Group 39.png" alt="Card" width="30">
                </div>
                <hr>
                <div class="d-flex justify-content-between align-items-center mb-2">
                    <div class="form-check">
                        <input class="form-check-input" type="radio" name="paymentMethod" value="paypal">
                        <label class="form-check-label">PayPal</label>
                    </div>
                    <img src="../assets2/paypal.png" alt="PayPal" width="30">
                </div>
                <hr>
                <div class="d-flex justify-content-between align-items-center mb-2">
                    <div class="form-check">
                        <input class="form-check-input" type="radio" name="paymentMethod" value="paymaya">
                        <label class="form-check-label">PayMaya</label>
                    </div>
                    <img src="../assets2/paymaya.png" alt="PayMaya" width="30">
                </div>
                <hr>
                <div class="form-check mt-2">
                    <input class="form-check-input" type="checkbox">
                    <label class="form-check-label">Remember my choice</label>
                </div>
            </div>
        </div>
    </div>
</div>

<?php require_once '../includes/user/cart_footer.php'; ?>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<script src="../assets2/js/master.js"></script>
<script src="main.js"></script>
</body>
</html>