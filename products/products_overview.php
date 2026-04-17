<?php session_start(); ?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Annyeong Haven - Products</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.0/font/bootstrap-icons.css">
    <link rel="stylesheet" href="style.css">
</head>
<body class="bg-primary bg-opacity-10" style="overflow-x: hidden;">

<?php include '../header.php'; ?>

<div class="container py-5">
    <div class="row row-cols-1 row-cols-md-3 g-4">
        
            <?php
        $products = [
            // Nagdagdag ako ng 'link' key para sa bawat product
            ['id' => 'txt_lightstick', 'name' => "TXT' MOA Bong", 'price' => 2999, 'sale' => '75% off', 'img' => 'txt.png', 'link' => 'txt.php'],
            ['id' => 'twice_lightstick', 'name' => "TWICE' Candy Bong Z", 'price' => 2999, 'sale' => '25% off', 'img' => 'twice.png', 'link' => 'twice.php'],
            ['id' => 'bts_lightstick', 'name' => "BTS' Army Bomb", 'price' => 3599, 'sale' => '25% off', 'img' => 'bts.png', 'link' => 'bts.php'],
            ['id' => 'enhypen_lightstick', 'name' => "ENHYPEN Official Lightstick", 'price' => 2000, 'sale' => '75% off', 'img' => 'enhypen.webp', 'link' => 'enhypen.php'],
            ['id' => 'blackpink_lightstick', 'name' => "BLACKPINK' Bi-Ping-Pong", 'price' => 3599, 'sale' => '25% off', 'img' => 'blackpink.png', 'link' => 'blackpink.php'],
            ['id' => 'oneus_lightstick', 'name' => "ONEUS' Dalbit", 'price' => 2000, 'sale' => '25% off', 'img' => 'oneus.png', 'link' => 'oneus.php'],
            ['id' => 'aespa_lightstick', 'name' => "AESPA' Official Fanlight", 'price' => 2500, 'sale' => '75% off', 'img' => 'aespa.png', 'link' => 'aespa.php'],
            ['id' => 'lesserafim_lightstick', 'name' => "LESSERAFIM Official Lightstick", 'price' => 2699, 'sale' => '25% off', 'img' => 'lesserafim.png', 'link' => 'lesserafim.php'],
            ['id' => 'straykids_lightstick', 'name' => "STRAY KIDS' Nachimbong", 'price' => 2999, 'sale' => '25% off', 'img' => 'straykids.png', 'link' => 'straykids.php'],
        ];
        ?>

        <?php foreach ($products as $product): ?>
            <div class="col reveal">
                <div class="card h-100 border-0 shadow-sm p-3 product-card position-relative">
                    
                    <a href="<?php echo $product['link']; ?>" class="text-decoration-none">
                        <div class="img-wrapper">
                            <img src="images/<?php echo $product['img']; ?>" class="img-fluid" alt="product">
                        </div>
                    </a>

                    <div class="card-body px-0 pb-0">
                        <a href="<?php echo $product['link']; ?>" class="text-decoration-none">
                            <h5 class="card-title fw-bold text-center mb-3" style="color: #2b3a8c;">
                                <?php echo $product['name']; ?>
                            </h5>
                        </a>
                        
                        <div class="d-flex justify-content-between align-items-end">
                            <div>
                                <h4 class="text-danger fw-bold mb-0">₱<?php echo number_format($product['price'], 0); ?></h4>
                                <p class="small text-muted mb-1" style="font-size: 0.8rem;">2.5k sold</p>
                            </div>

                            <form>
                                <div class="form-group mb-2">
                                    <input type="hidden" class="product-id" value="<?php echo $product['id']; ?>">
                                    <input type="hidden" class="product-name" value="<?php echo $product['name']; ?>">
                                    <input type="hidden" class="product-price" value="<?php echo $product['price']; ?>">
                                    <input type="hidden" class="product-image" value="../images/<?php echo $product['img']; ?>">
                                    <input type="number" class="product-quantity" value="1" min="1" style="width: 50px; display: none;">
                                    <button type="button" class="btn btn-dark rounded-pill px-3 py-2 shadow-sm add-to-cart-btn" style="font-size: 0.75rem; font-weight: bold;">
                                        ADD TO CART
                                    </button>
                                </div>
                            </form>
                        </div>
                    </div>
                </div>
            </div>
        <?php endforeach; ?>

    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>

<script>
    const observer = new IntersectionObserver((entries) => {
        entries.forEach((entry) => {
            if (entry.isIntersecting) {
                entry.target.classList.add('show');
            } else {
                // Remove 'show' if you want it to animate again when scrolling back up
                entry.target.classList.remove('show');
            }
        });
    }, { threshold: 0.1 }); // 10% of the card must be visible before animating

    const revealElements = document.querySelectorAll('.reveal');
    revealElements.forEach((el) => observer.observe(el));

    // Handle add to cart for product overview
    document.querySelectorAll('.add-to-cart-btn').forEach(btn => {
        btn.addEventListener('click', function() {
            const form = this.closest('form');
            const product_id = form.querySelector('.product-id').value;
            const product_name = form.querySelector('.product-name').value;
            const product_price = form.querySelector('.product-price').value;
            const product_image = form.querySelector('.product-image').value;
            const quantity = parseInt(form.querySelector('.product-quantity').value) || 1;
            
            // Get cart from localStorage
            let cart = JSON.parse(localStorage.getItem('annyeong_cart')) || [];
            
            // Check if product exists in cart
            const existingItem = cart.find(item => item.id === product_id);
            if (existingItem) {
                existingItem.quantity += quantity;
            } else {
                cart.push({
                    id: product_id,
                    name: product_name,
                    price: parseInt(product_price),
                    image: product_image,
                    quantity: quantity
                });
            }
            
            // Save to localStorage
            localStorage.setItem('annyeong_cart', JSON.stringify(cart));
            alert('Product added to cart!');
        });
    });
</script>

</body>
</html>