<?php
require_once '../db/action/dbconfig.php';
session_start();

if (!isset($_SESSION['username'])) {
    header('Location: ../loginpage.php');
    exit();
}

$stmt = $conn->prepare('SELECT l.*, p.address, p.postal_code FROM login l LEFT JOIN user_profiles p ON p.user_id = l.id WHERE l.username = :username LIMIT 1');
$stmt->execute([':username' => $_SESSION['username']]);
$user_data = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$user_data) {
    header('Location: ../loginpage.php');
    exit();
}

$user_id = (int)$user_data['id'];
$selected_raw = $_GET['items'] ?? '';
$selected_ids = array_values(array_unique(array_filter(array_map('intval', explode(',', $selected_raw)), function ($id) {
    return $id > 0;
})));
$checkout_error = $_GET['checkout_error'] ?? '';

if (empty($selected_ids)) {
    header('Location: cart.php?checkout_error=missing_selection');
    exit();
}

$placeholders = implode(',', array_fill(0, count($selected_ids), '?'));
$sql = "
    SELECT c.id AS cart_id, c.quantity, c.product_id,
           p.name, p.color, p.size, p.price, p.image, p.stock
    FROM cart c
    JOIN products p ON c.product_id = p.id
    WHERE c.user_id = ? AND c.product_id IN ($placeholders)
    ORDER BY c.added_at DESC
";
$params = array_merge([$user_id], $selected_ids);
$stmt = $conn->prepare($sql);
$stmt->execute($params);
$checkout_items = $stmt->fetchAll(PDO::FETCH_ASSOC);

if (empty($checkout_items)) {
    header('Location: cart.php?checkout_error=invalid_selection');
    exit();
}

$cart_count = count($checkout_items);
$subtotal = array_sum(array_map(function ($item) {
    return (float)$item['price'] * (int)$item['quantity'];
}, $checkout_items));

$buyerAddress = trim((string)($user_data['address'] ?? ''));
$buyerPostal = trim((string)($user_data['postal_code'] ?? ''));

function escape($value)
{
    return htmlspecialchars((string)$value, ENT_QUOTES, 'UTF-8');
}

function resolve_product_image_path_checkout($rawImage)
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
    <title>Checkout - Laces</title>
</head>
<body>
<?php require_once '../includes/user/cart_nav.php'; ?>

<div class="container mt-5">
    <div class="d-flex justify-content-between align-items-center">
        <div class="cart-title">Checkout Items (<span class="cart-count"><?php echo $cart_count; ?></span>)</div>
        <div><a href="orderHistory.php" class="text-dark text-decoration-none border-bottom border-dark border-3 pb-2">My Orders</a></div>
    </div>
    <hr class="border-dark border-2 mt-2">
</div>

<div class="container mt-4">
    <div class="row">
        <div class="col-lg-8">
            <div class="card p-3 border-dark border-3 mb-3">
                <div class="d-flex justify-content-between align-items-center px-3 mb-2 fw-semibold">
                    <div class="ms-5 text-secondary">Product</div>
                    <div class="me-3 text-secondary">Quantity</div>
                </div>
                <hr class="mt-0">

                <?php foreach ($checkout_items as $item): ?>
                <?php
                    $img = escape(resolve_product_image_path_checkout($item['image'] ?? ''));
                ?>
                <div class="card mb-3 p-3 border-dark border-3">
                    <div class="d-flex justify-content-between align-items-center">
                        <div class="d-flex align-items-center gap-3">
                            <img src="<?php echo $img; ?>" alt="<?php echo escape($item['name']); ?>" width="70" class="rounded" onerror="this.src='../assets2/adidasblablabla.png'">
                            <div>
                                <h6 class="mb-1"><?php echo escape($item['name']); ?></h6>
                                <small class="text-muted">
                                    Size: <?php echo escape($item['size']); ?> |
                                    <?php echo escape($item['color']); ?>
                                </small>
                                <p class="mb-0 fw-bold">₱<?php echo number_format((float)$item['price'], 2); ?></p>
                            </div>
                        </div>
                        <div class="quantity-pill d-flex align-items-center">
                            <span class="px-2 quantity"><?php echo (int)$item['quantity']; ?></span>
                        </div>
                    </div>
                </div>
                <?php endforeach; ?>
            </div>

            <div class="row">
                <div class="col-8">
                    <h1 class="fw-bolder fs-1rem">Name:</h1>
                    <h4><?php echo escape(trim(($user_data['first_name'] ?? '') . ' ' . ($user_data['last_name'] ?? '')) ?: ($user_data['username'] ?? '')); ?></h4>
                    <h1 class="fw-bolder fs-1rem">Address:</h1>
                    <div class="card mb-3 p-3 border-dark border-1 w-75">
                        <h6 class="fw-bolder mb-3">Home</h6>
                        <?php if ($buyerAddress === '' && $buyerPostal === ''): ?>
                        <p class="mb-0 text-muted">Address details not set yet.</p>
                        <?php else: ?>
                        <?php if ($buyerAddress !== ''): ?>
                        <p class="mb-0"><?php echo escape($buyerAddress); ?></p>
                        <?php endif; ?>
                        <?php if ($buyerPostal !== ''): ?>
                        <p class="mb-0"><?php echo escape($buyerPostal); ?></p>
                        <?php endif; ?>
                        <?php endif; ?>
                    </div>
                </div>
                <div class="col-4 text-start">
                    <h1 class="fw-bolder fs-1rem">Email:</h1>
                    <h6><?php echo escape($user_data['email'] ?? ''); ?></h6>
                </div>
            </div>
        </div>

        <div class="col-lg-4">
            <div class="card p-3 mb-3 border-dark border-3">
                <h5>Order Summary</h5>
                <hr>
                <div class="d-flex justify-content-between">
                    <span class="text-gray-light">Items</span>
                    <span class="text-warning"><?php echo $cart_count; ?></span>
                </div>
                <div class="mt-3">
                    <?php foreach ($checkout_items as $item): ?>
                    <div class="d-flex justify-content-between">
                        <span class="text-gray-light"><?php echo escape($item['name']); ?> x<?php echo (int)$item['quantity']; ?></span>
                        <span class="fw-bold">₱<?php echo number_format((float)$item['price'] * (int)$item['quantity'], 2); ?></span>
                    </div>
                    <?php endforeach; ?>
                </div>
                <hr>
                <div class="d-flex justify-content-between fw-bold">
                    <span class="fw-bolder">TOTAL</span>
                    <span class="text-warning">PHP <?php echo number_format($subtotal, 2); ?></span>
                </div>
            </div>
            <div>
                <h1 class="fw-bolder fs-1rem">Discount Codes:</h1>
                <input type="text" class="form-control mb-3" placeholder="Enter your discount code">
                <form action="../db/action/pay_now.php" method="POST" class="m-0">
                    <input type="hidden" name="selected_items" value="<?php echo escape(implode(',', $selected_ids)); ?>">
                    <button id="pay-now-btn" type="submit" class="btn btn-custom-color w-100 mt-3 text-white font-weight-bold-custom shadow-custom rounded-3 fs-4">Pay Now</button>
                </form>
            </div>
        </div>
    </div>
</div>

<?php require_once '../includes/user/cart_footer.php'; ?>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<script>
document.addEventListener('DOMContentLoaded', function () {
    const checkoutError = <?php echo json_encode($checkout_error); ?>;
    if (checkoutError === 'insufficient_stock') {
        Swal.fire({
            icon: 'warning',
            title: 'Stock updated',
            text: 'One or more items are no longer available in the requested quantity. Please adjust your cart and try again.'
        });
    }
    if (checkoutError === 'payment_failed') {
        Swal.fire({
            icon: 'error',
            title: 'Payment failed',
            text: 'We could not place your order. Please try again.'
        });
    }
});
</script>
<script src="../assets2/js/master.js"></script>
</body>
</html>
