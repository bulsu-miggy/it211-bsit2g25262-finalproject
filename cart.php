<?php
session_start();
require_once 'auth.php';
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>SipFlask | Your Cart</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.0.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.0/font/bootstrap-icons.css">
    <link rel="stylesheet" href="css/style.css">
</head>
<body style="background-color: #00808094;">

    <nav class="navbar navbar-expand navbar-light bg-none sticky-top shadow-sm" style="background-color: #39afaf;">
        <div class="container">
            <a class="navbar-brand" href="index.php">
                <img src="images/white.png" alt="SipFlask" height="49">
            </a>
            <div class="collapse navbar-collapse justify-content-end" id="sipNavbar">
                <ul class="navbar-nav align-items-center">
                    <li class="nav-item px-0"><a class="nav-link fw-bold text-white" href="index.php">Home</a></li>
                    <li class="nav-item px-0"><a class="nav-link fw-bold text-white" href="listings.php">Listings</a></li>
                    <li class="nav-item px-0"><a class="nav-link fw-bold text-white" href="contactUs.php">Contact Us</a></li>
                    <li class="nav-item ms-tight px-0">
                        <a class="nav-link p-0 text-white" href="cart.php"><i class="bi bi-cart4 fs-5"></i></a>
                    </li>
                    <?php if (isLoggedIn()): ?>
                        <li class="nav-item px-0">
                            <a class="nav-link fw-bold text-white" href="#">Hello, <?= htmlspecialchars(getUserName()) ?></a>
                        </li>
                        <li class="nav-item px-0">
                            <a class="nav-link fw-bold text-white" href="logout.php">Logout</a>
                        </li>
                    <?php else: ?>
                        <li class="nav-item px-0">
                            <a class="nav-link fw-bold text-white" href="login.php">Login</a>
                        </li>
                        <li class="nav-item px-0">
                            <a class="nav-link fw-bold text-white" href="register.php">Register</a>
                        </li>
                    <?php endif; ?>
                </ul>
            </div>
        </div>
    </nav>

    <main class="container my-5">
        <div class="bg-white rounded-4 shadow p-4 p-md-5">
            <h2 class="fw-bold mb-4 pb-2 border-bottom">
                <i class="bi bi-cart4 me-2" style="color: #1b739a;"></i>
                Your Shopping Cart
            </h2>
            
            <?php if (isset($_SESSION['cart_success'])): ?>
                <div class="alert alert-success alert-dismissible fade show" role="alert">
                    <i class="bi bi-check-circle me-2"></i> <?= $_SESSION['cart_success'] ?>
                    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                </div>
                <?php unset($_SESSION['cart_success']); ?>
            <?php endif; ?>
            
            <?php if (isset($_SESSION['cart_error'])): ?>
                <div class="alert alert-danger alert-dismissible fade show" role="alert">
                    <i class="bi bi-exclamation-triangle me-2"></i> <?= $_SESSION['cart_error'] ?>
                    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                </div>
                <?php unset($_SESSION['cart_error']); ?>
            <?php endif; ?>

            <?php if (empty($_SESSION['cart'])): ?>
                <div class="text-center py-5">
                    <i class="bi bi-cart-x display-1 text-muted"></i>
                    <h4 class="mt-3">Your cart is empty</h4>
                    <p class="text-muted">Looks like you haven't added any items yet.</p>
                    <a href="listings.php" class="btn btn-pink mt-3 px-4 py-2 rounded-pill">Start Shopping</a>
                </div>
            <?php else: ?>
                <div class="row">
                    <div class="col-lg-8">
                        <div class="table-responsive">
                            <table class="table cart-table">
                                <thead>
                                    <tr>
                                        <th>Product</th>
                                        <th>Size</th>
                                        <th>Price</th>
                                        <th>Quantity</th>
                                        <th>Total</th>
                                        <th></th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php 
                                    $subtotal = 0;
                                    foreach ($_SESSION['cart'] as $product_id => $item): 
                                        $item_total = floatval($item['price']) * intval($item['quantity']);
                                        $subtotal += $item_total;
                                    ?>
                                    <tr>
                                        <td>
                                            <div class="d-flex align-items-center">
                                                <img src="images/<?= $item['size'] ?>/<?= $item['img'] ?>" alt="Product" class="product-img me-3" style="width: 70px; height: 70px; object-fit: contain; background-color: #f8f9fa; padding: 5px; border-radius: 10px;">
                                                <div>
                                                    <h6 class="fw-bold mb-0"><?= htmlspecialchars($item['name']) ?></h6>
                                                </div>
                                            </div>
                                        </td>
                                        <td class="text-uppercase fw-bold"><?= $item['size'] ?></td>
                                        <td class="fw-bold">₱<?= number_format($item['price'], 2) ?></td>
                                        <td>
                                            <form action="update_cart.php" method="POST" class="d-flex align-items-center">
                                                <input type="hidden" name="product_id" value="<?= $product_id ?>">
                                                <button type="submit" name="action" value="decrease" class="btn btn-outline-secondary btn-sm quantity-btn">−</button>
                                                <span class="mx-2 fw-bold" style="min-width: 30px; text-align: center;"><?= $item['quantity'] ?></span>
                                                <button type="submit" name="action" value="increase" class="btn btn-outline-secondary btn-sm quantity-btn">+</button>
                                            </form>
                                        </td>
                                        <td class="fw-bold text-primary">₱<?= number_format($item_total, 2) ?></td>
                                        <td>
                                            <form action="remove_from_cart.php" method="POST" onsubmit="return confirm('Remove this item?')">
                                                <input type="hidden" name="product_id" value="<?= $product_id ?>">
                                                <button type="submit" class="btn btn-link p-0 delete-btn">
                                                    <i class="bi bi-trash3"></i>
                                                </button>
                                            </form>
                                        </td>
                                    </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                        </div>
                        
                        <div class="d-flex align-items-center gap-3 mt-4">
                            <a href="listings.php" class="btn-continue text-center text-decoration-none d-flex align-items-center justify-content-center" style="height: 45px; flex: 1;">
                                <i class="bi bi-arrow-left me-2"></i> Continue Shopping
                            </a>
                            <form action="clear_cart.php" method="POST" onsubmit="return confirm('Clear entire cart?')" style="flex: 1;">
                                <button type="submit" class="btn-clear w-100 d-flex align-items-center justify-content-center" style="height: 45px;">
                                    <i class="bi bi-trash3 me-2"></i> Clear Cart
                                </button>
                            </form>
                        </div>
                    </div>
                    
                    <div class="col-lg-4 mt-4 mt-lg-0">
                        <div class="order-summary">
                            <h4 class="fw-bold mb-4">Order Summary</h4>
                            
                            <div class="d-flex justify-content-between mb-3">
                                <span class="text-muted">Subtotal</span>
                                <span class="fw-bold">₱<?= number_format($subtotal, 2) ?></span>
                            </div>
                            
                            <div class="d-flex justify-content-between mb-3">
                                <span class="text-muted">Shipping</span>
                                <span class="text-success fw-bold">FREE</span>
                            </div>
                            
                            <hr class="my-3">
                            
                            <div class="d-flex justify-content-between mb-4">
                                <span class="h5 fw-bold">Total</span>
                                <span class="h5 fw-bold text-primary">₱<?= number_format($subtotal, 2) ?></span>
                            </div>
                            
                            <?php if (isLoggedIn()): ?>
                                <a href="checkout.php" class="btn-checkout text-center text-decoration-none d-block">
                                    PROCEED TO CHECKOUT <i class="bi bi-arrow-right ms-2"></i>
                                </a>
                            <?php else: ?>
                                <a href="login.php" class="btn-checkout text-center text-decoration-none d-block">
                                    LOGIN TO CHECKOUT <i class="bi bi-box-arrow-in-right ms-2"></i>
                                </a>
                                <small class="text-muted d-block text-center mt-2">
                                    <i class="bi bi-info-circle"></i> Please login to complete your purchase
                                </small>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
            <?php endif; ?>
        </div>
    </main>

    <footer class="custom-footer mt-5">
        <div class="container text-center">
            <div class="footer-logo text-white border-bottom pb-1 mb-5 fw-bold d-flex justify-content-center align-items-center">
                <img src="images/exactlogo.png" alt="SipFlask" height="55" class="me-2"> SipFlask
            </div>
            <ul class="footer-links list-unstyled d-flex justify-content-center gap-4 mb-4">
                <li><a href="index.php" class="text-white text-decoration-none">Home</a></li>
                <li><a href="listings.php" class="text-white text-decoration-none">Listings</a></li>
                <li><a href="contactUs.php" class="text-white text-decoration-none">Contact Us</a></li>
            </ul>
            <div class="footer-tagline italic mb-4">#KeepItSipFlask</div>
            <div class="footer-socials mb-5">
                <a href="#" class="text-white mx-2 fs-4"><i class="bi bi-facebook"></i></a>
                <a href="#" class="text-white mx-2 fs-4"><i class="bi bi-instagram"></i></a>
                <a href="#" class="text-white mx-2 fs-4"><i class="bi bi-tiktok"></i></a>
            </div>
            <div class="footer-bottom border-top pt-3 d-flex justify-content-between small opacity-75">
                <div>All Rights Reserved © 2026 SipFlask</div>
                <div>Website - SipFlask Website</div>
            </div>
        </div>
    </footer>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.0.2/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>