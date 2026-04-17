<?php 
ini_set('session.save_path', sys_get_temp_dir());
session_set_cookie_params(0, '/', 'localhost');
session_start(); 

// Handle adding to cart
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['add_to_cart'])) {
    $product_id = $_POST['product_id'] ?? 'EMPTY';
    $product_name = $_POST['product_name'] ?? 'EMPTY';
    $product_price = $_POST['product_price'] ?? 'EMPTY';
    $product_image = $_POST['product_image'] ?? 'EMPTY';
    $quantity = isset($_POST['quantity']) ? (int)$_POST['quantity'] : 1;
    
    $is_ajax = isset($_POST['ajax']) ? 'YES' : 'NO';

    // Initialize cart if not exists
    if (!isset($_SESSION['cart'])) {
        $_SESSION['cart'] = [];
    }

    // Check if product already in cart
    $found = false;
    $index = -1;
    for ($i = 0; $i < count($_SESSION['cart']); $i++) {
        if ($_SESSION['cart'][$i]['id'] == $product_id) {
            $index = $i;
            $found = true;
            break;
        }
    }

    if ($found) {
        $_SESSION['cart'][$index]['quantity'] += $quantity;
    } else {
        $_SESSION['cart'][] = [
            'id' => $product_id,
            'name' => $product_name,
            'price' => $product_price,
            'image' => $product_image,
            'quantity' => $quantity
        ];
    }

    // Save session and respond
    if ($is_ajax === 'YES') {
        session_write_close();
        echo "Product added to cart! Cart now has: " . count($_SESSION['cart']) . " items";
        exit();
    } else {
        session_write_close();
        header('Location: cart.php');
        exit();
    }
}

// Handle updating quantities
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['update_cart'])) {
    if (isset($_POST['quantities']) && is_array($_POST['quantities'])) {
        foreach ($_POST['quantities'] as $index => $qty) {
            if (isset($_SESSION['cart'][$index])) {
                $_SESSION['cart'][$index]['quantity'] = max(1, (int)$qty);
            }
        }
    }
    header('Location: cart.php');
    exit();
}

// Handle deleting items
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['delete_item'])) {
    $delete_index = (int)$_POST['delete_item'];
    if (isset($_SESSION['cart'][$delete_index])) {
        array_splice($_SESSION['cart'], $delete_index, 1);
    }
    header('Location: cart.php');
    exit();
}

?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Annyeong Haven | Shopping Cart</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;600&display=swap" rel="stylesheet">
    <style>
        body {
            font-family: 'Poppins', sans-serif;
            background: linear-gradient(180deg, #a9c9ff 0%, #ffffff 100%);
            min-height: 100vh;
            color: #333;
        }
        .header-section {
            background-color: #13195b;
            color: white;
            padding: 22px 0;
        }
        .header-brand {
            letter-spacing: 2px;
            font-size: 1.6rem;
        }
        .header-icons a {
            color: white;
            font-size: 1.4rem;
            text-decoration: none;
            margin-left: 18px;
            opacity: 0.9;
        }
        .page-nav {
            background-color: #f8f9fa;
            padding: 14px 0;
        }
        .page-nav .nav-link {
            color: #5f6368 !important;
            font-weight: 600;
            text-transform: uppercase;
            font-size: 0.95rem;
            letter-spacing: 0.5px;
        }
        .page-nav .nav-separator {
            color: #ced4da;
        }
        .cart-img {
            width: 120px;
            height: 120px;
            object-fit: contain;
            background: white;
            padding: 5px;
        }
        .summary-img {
            width: 80px;
            height: 80px;
            object-fit: contain;
            background: white;
        }
        .card-custom-left {
            background-color: #e6f0ff;
            border: 2px solid #0d6efd !important;
        }
        .qty-input {
            width: 45px !important;
            border-left: none;
            border-right: none;
        }
        input[type="number"]::-webkit-outer-spin-button,
        input[type="number"]::-webkit-inner-spin-button {
            -webkit-appearance: none;
            margin: 0;
        }
        input[type="number"] {
            -moz-appearance: textfield;
        }
    </style>
</head>
<body>

<?php include '../header.php'; ?>
    <main class="container mb-5">
        <h4 class="mb-4 fw-bold">Your Cart</h4>
        
        <div id="cartContainer"></div>
    </main>

    <script>
        // Load cart from localStorage and display
        function loadCart() {
            const cart = JSON.parse(localStorage.getItem('annyeong_cart')) || [];
            const cartContainer = document.getElementById('cartContainer');
            
            if (cart.length === 0) {
                cartContainer.innerHTML = '<div class="text-center py-5"><h5 class="text-muted">Your cart is empty</h5><a href="../products/products_overview.php" class="btn btn-primary">Continue Shopping</a></div>';
                return;
            }
            
            // Build cart HTML
            let html = '<div class="row g-4"><div class="col-lg-7"><form method="POST" action="cart.php"><div class="card card-custom-left p-4 rounded-4 shadow-sm">';
            
            let subtotal = 0;
            cart.forEach((item, index) => {
                const itemTotal = item.price * item.quantity;
                subtotal += itemTotal;
                html += `<div class="d-flex align-items-center mb-4">
                    <input type="checkbox" class="form-check-input me-3 item-checkbox" name="selected_items[]" value="${index}">
                    <img src="${item.image}" class="rounded border cart-img" alt="${item.name}">
                    <div class="ms-3 flex-grow-1">
                        <h6 class="mb-1 fw-bold">${item.name}</h6>
                        <p class="text-danger fw-bold mb-0">₱${item.price.toLocaleString()}</p>
                    </div>
                    <div class="input-group input-group-sm w-auto">
                        <button class="btn btn-outline-secondary qty-btn" type="button" data-action="minus" data-index="${index}">-</button>
                        <input type="number" class="form-control text-center qty-input" name="quantities[${index}]" value="${item.quantity}" min="1" data-index="${index}">
                        <button class="btn btn-outline-secondary qty-btn" type="button" data-action="plus" data-index="${index}">+</button>
                    </div>
                    <button type="button" class="btn btn-outline-danger btn-sm ms-2 delete-btn" data-index="${index}">Delete</button>
                </div>`;
            });
            
            html += '<hr><div class="form-check mt-3"><input class="form-check-input" type="checkbox" id="selectAll"><label class="form-check-label fw-bold" for="selectAll">Select All Items</label></div></div><div class="mt-3"><button type="button" id="updateCartBtn" class="btn btn-primary">Update Cart</button></div></form></div>';
            
            // Order Summary
            html += '<div class="col-lg-5"><div class="card border-0 p-4 rounded-4 shadow-sm h-100 bg-white"><h5 class="fw-bold mb-4">Order Summary</h5>';
            
            cart.forEach(item => {
                html += `<div class="d-flex align-items-center mb-4">
                    <img src="${item.image}" class="rounded border summary-img" alt="Summary thumb">
                    <div class="ms-3">
                        <p class="small mb-0 fw-semibold">${item.name}</p>
                        <p class="text-danger small fw-bold mb-0">₱${(item.price * item.quantity).toLocaleString()}</p>
                    </div>
                </div>`;
            });
            
            const total = subtotal + 1000;
            html += `<div class="mt-auto">
                <div class="d-flex justify-content-between mb-2">
                    <span>Subtotal:</span>
                    <span class="fw-bold">₱${subtotal.toLocaleString()}</span>
                </div>
                <div class="d-flex justify-content-between mb-2">
                    <span>Shipping Fee:</span>
                    <span class="fw-bold">₱1,000</span>
                </div>
                <div class="d-flex justify-content-between border-top pt-2 mb-4">
                    <span class="h5 fw-bold">Total:</span>
                    <span class="h5 fw-bold text-primary">₱${total.toLocaleString()}</span>
                </div>
                <a href="../cart/checkout.php" class="btn btn-dark w-100 py-3 rounded-3 fw-bold text-decoration-none">Go to Checkout</a>
            </div></div></div></div>`;
            
            cartContainer.innerHTML = html;
            
            // Attach event listeners
            attachEventListeners(cart);
        }
        
        function attachEventListeners(cart) {
            // Quantity buttons
            document.querySelectorAll('.qty-btn').forEach(btn => {
                btn.addEventListener('click', function() {
                    const index = parseInt(this.dataset.index);
                    const action = this.dataset.action;
                    const input = document.querySelector(`input[name="quantities[${index}]"]`);
                    let value = parseInt(input.value);
                    
                    if (action === 'plus') {
                        input.value = value + 1;
                    } else if (action === 'minus' && value > 1) {
                        input.value = value - 1;
                    }
                });
            });
            
            // Update Cart button
            const updateBtn = document.getElementById('updateCartBtn');
            if (updateBtn) {
                updateBtn.addEventListener('click', function() {
                    // Get all quantity inputs
                    document.querySelectorAll('.qty-input').forEach((input, index) => {
                        const newQty = parseInt(input.value) || 1;
                        if (cart[index]) {
                            cart[index].quantity = Math.max(1, newQty);
                        }
                    });
                    
                    // Save to localStorage
                    localStorage.setItem('annyeong_cart', JSON.stringify(cart));
                    alert('Cart updated successfully!');
                    loadCart();
                });
            }
            
            // Delete buttons
            document.querySelectorAll('.delete-btn').forEach(btn => {
                btn.addEventListener('click', function() {
                    const index = parseInt(this.dataset.index);
                    cart.splice(index, 1);
                    localStorage.setItem('annyeong_cart', JSON.stringify(cart));
                    loadCart();
                });
            });
            
            // Select all
            const selectAll = document.getElementById('selectAll');
            const itemCheckboxes = document.querySelectorAll('.item-checkbox');
            
            selectAll.addEventListener('change', function() {
                itemCheckboxes.forEach(cb => cb.checked = selectAll.checked);
            });
            
            itemCheckboxes.forEach(cb => {
                cb.addEventListener('change', function() {
                    const allChecked = Array.from(itemCheckboxes).every(c => c.checked);
                    const someChecked = Array.from(itemCheckboxes).some(c => c.checked);
                    selectAll.checked = allChecked;
                    selectAll.indeterminate = someChecked && !allChecked;
                });
            });
        }
        
        // Load cart on page load
        loadCart();
    </script>

</body>
</html>