<?php
require_once __DIR__ . '/ProductManager.php';  

$productManager = new ProductManager();
$cart_items = $productManager->getCartItems();


$subtotal = $productManager->getCartTotal();
$delivery_fee = 2.99; // Fixed delivery fee
$total = $subtotal + $delivery_fee;

?>  
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Your Cart - Lasa Filipina</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        body {
            background: linear-gradient(145deg, #f7f3e9 0%, #f0ece1 100%);
            padding: 2rem;
        }
        .order-card {
            max-width: 800px;
            margin: 0 auto;
            background: white;
            border-radius: 1rem;
            box-shadow: 0 10px 30px rgba(0,0,0,0.1);
            overflow: hidden;
        }
        .cart-header {
            background: #1e1e2a;
            color: white;
            padding: 1.5rem;
        }
        .cart-item {
            border-bottom: 1px solid #eee;
            padding: 1rem;
        }
        .qty-btn {
            background: #c97e2a;
            color: white;
            border: none;
            width: 30px;
            height: 30px;
            border-radius: 50%;
        }
        .qty-btn:hover {
            background: #b0681c;
        }
        .remove-btn {
            background: #dc3545;
            color: white;
            border: none;
            padding: 5px 10px;
            border-radius: 5px;
        }
        .checkout-btn {
            background: #c97e2a;
            color: white;
            width: 100%;
            padding: 1rem;
            font-size: 1.2rem;
            border: none;
        }
        .checkout-btn:hover {
            background: #b0681c;
        }
    </style>
</head>
<body>
    <div class="order-card">
        <div class="cart-header">
            <h1>🛒 Your Cart</h1>
            <p>Lasa Filipina</p>
        </div>
        
        <div class="cart-items" id="cartItems">
            <?php if (empty($cart_items)): ?>
                <div class="text-center p-5">
                    <h3>Your cart is empty</h3>
                    <a href="home.php" class="btn btn-primary mt-3">Browse Products</a>
                </div>
            <?php else: ?>
                <?php foreach ($cart_items as $item): ?>
                <div class="cart-item" data-product-id="<?= $item['product_id'] ?>">
                    <div class="row align-items-center">
                        <div class="col-md-5">
                            <h5><?= htmlspecialchars($item['title']) ?></h5>
                            <small class="text-muted"><?= htmlspecialchars($item['brand']) ?></small>
                        </div>
                        <div class="col-md-2">
                            $<?= number_format($item['price'], 2) ?>
                        </div>
                        <div class="col-md-3">
                            <div class="d-flex align-items-center">
                                <button class="qty-btn" onclick="updateQuantity(<?= $item['product_id'] ?>, -1)">-</button>
                                <span class="mx-3 qty-<?= $item['product_id'] ?>"><?= $item['quantity'] ?></span>
                                <button class="qty-btn" onclick="updateQuantity(<?= $item['product_id'] ?>, 1)">+</button>
                            </div>
                        </div>
                        <div class="col-md-2">
                            <button class="remove-btn" onclick="removeFromCart(<?= $item['product_id'] ?>)">Remove</button>
                        </div>
                    </div>
                </div>
                <?php endforeach; ?>
            <?php endif; ?>
        </div>
        
        <div class="summary p-4 bg-light">
            <div class="d-flex justify-content-between mb-2">
                <span>Subtotal:</span>
                <span id="subtotal">$<?= number_format($subtotal, 2) ?></span>
            </div>
            <div class="d-flex justify-content-between mb-2">
                <span>Delivery Fee:</span>
                <span>$<?= number_format($delivery_fee, 2) ?></span>
            </div>
            <div class="d-flex justify-content-between mb-3 fw-bold">
                <span>Total:</span>
                <span id="total">$<?= number_format($total, 2) ?></span>
            </div>
            <button class="checkout-btn" onclick="checkout()">Proceed to Checkout 🍕</button>
        </div>
    </div>

    <script>
        async function updateQuantity(productId, delta) {
            const qtySpan = document.querySelector(`.qty-${productId}`);
            let currentQty = parseInt(qtySpan.innerText);
            let newQty = currentQty + delta;
            
            if (newQty < 0) return;
            
            try {
                const formData = new FormData();
                formData.append('action', 'update_quantity');
                formData.append('product_id', productId);
                formData.append('quantity', newQty);
                
                const response = await fetch('cart-handler.php', {
                    method: 'POST',
                    body: formData
                });
                
                const result = await response.json();
                
                if (result.success) {
                    if (newQty === 0) {
                        location.reload(); // Reload to remove item
                    } else {
                        qtySpan.innerText = newQty;
                        updateTotals();
                    }
                }
            } catch (error) {
                console.error('Error:', error);
            }
        }
        
        async function removeFromCart(productId) {
            if (confirm('Remove this item from cart?')) {
                await updateQuantity(productId, -parseInt(document.querySelector(`.qty-${productId}`).innerText));
            }
        }
        
        async function updateTotals() {
            try {
                const response = await fetch('cart-handler.php?action=get_totals');
                const result = await response.json();
                document.getElementById('subtotal').innerText = `$${result.subtotal.toFixed(2)}`;
                document.getElementById('total').innerText = `$${result.total.toFixed(2)}`;
            } catch (error) {
                console.error('Error:', error);
            }
        }
        
        async function checkout() {
            window.location.href = 'checkout.php';
        }
    </script>
</body>
</html>