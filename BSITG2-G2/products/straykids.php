<?php session_start(); ?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Annyeong Haven</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.0/font/bootstrap-icons.css">
    <style>
        input[type="number"]::-webkit-outer-spin-button,
        input[type="number"]::-webkit-inner-spin-button {
            -webkit-appearance: none !important;
            margin: 0;
        }
        input[type="number"] {
            -webkit-appearance: textfield !important;
            -moz-appearance: textfield !important;
            appearance: textfield !important;
        }
    </style>
</head>
<body class="bg-primary bg-opacity-10" style="min-height: 100vh;">

<?php include '../header.php'; ?>
    
    <div class="container py-5 mt-5">
        <div class="row align-items-center">
            
            <div class="col-md-5 text-center position-relative">
               <h2 class="display-1 fw-bold text-uppercase mb-n5 position-relative z-1 text-nowrap" style="font-weight: 950; opacity: 0.8;">Nachimbong</h2>
                <img src="images/straykids.png" alt="STRAYKIDS Lightstick" class="img-fluid position-relative z-2" style="filter: drop-shadow(0 15px 15px rgba(0,0,0,0.1));">
            </div>

            <div class="col-md-7 ps-md-5">
                <h2 class="display-3 fw-bold mb-4">STRAYKIDS Official Lightstick</h2>
                
                <p class="fs-5 mb-4 text-dark">Size: <span class="fw-bold">105 x 256 x 48 (mm)</span></p>
                
                <div class="mb-4">
                    <h5 class="fw-bold">📦 Inclusions</h5>
                    <ul class="list-unstyled ms-3 fs-5">
                        <li class="mb-1">• 1 x STRAYKIDS Official Lightstick & Box</li>
                        <li class="mb-1">• Strap</li>
                        <li class="mb-1">• User manual</li>
                        <li class="mb-1">• Exclusive photocards</li>
                    </ul>
                </div>

                <p class="fs-5 mb-5">Power: <span class="fw-bold">AAA X 3EA</span></p>

                <div class="row align-items-center">
                    <div class="col-6">
                        <p class="h1 display-2 fw-bold mb-0">₱2,999</p>
                        <small class="text-uppercase ms-2 text-secondary fw-bold" style="letter-spacing: 2px;">price</small>
                    </div>
            <form action="../Cart/cart.php" method="POST" class="col-6 text-end">
                <input type="hidden" name="product_id" value="straykids_lightstick">
                <input type="hidden" name="product_name" value="STRAY KIDS Nachimbong">
                <input type="hidden" name="product_price" value="2999">
                <input type="hidden" name="product_image" value="../images/straykids.png">
                <div class="d-inline-flex align-items-center bg-dark text-white rounded-pill px-3 py-1 mb-3">
                    <button type="button" id="decrement" class="btn btn-link text-white text-decoration-none fw-bold fs-4 p-0 px-2">−</button>
        
                        <input type="number" id="quantity" name="quantity" value="1" min="1" 
                        class="bg-transparent text-white border-0 text-center fw-bold fs-5" 
                        style="width: 40px; outline: none;">
               
                    <button type="button" id="increment" class="btn btn-link text-white text-decoration-none fw-bold fs-4 p-0 px-2">+</button>
                </div>   
                        <br>
                        <button type="submit" name="add_to_cart" class="btn btn-lg px-5 py-2 fw-bold text-white rounded-pill shadow-lg border-0" 
                            style="background: linear-gradient(180deg, #6c757d 0%, #343a40 100%);">
                            ADD TO CART
                        </button>
                    </form>
                </div>
            </div>

        </div>
    </div>

    <div class="container pb-5">
        <div class="row mb-5 justify-content-center">
            <div class="col-md-10 text-center">
                <h2 class="text-start fw-bold mb-4 display-6" style="color: #4a90e2;">Inclusions:</h2>
                 <img src="../images/straykidsinc.png" class="img-fluid w-75" alt="inclusions">
            </div>
        </div>

        <div class="bg-white p-1 rounded shadow-sm text-center align-middle">
            <h4 class="fw-bold p-3 mb-0">Information:</h4>
            
            <div class="table-responsive">
                <table class="table table-bordered mb-0">
                    <tbody>
                        <tr>
                            <td class="bg-primary text-white fw-bold text-center align-middle" style="width: 20%; background-color: #13195b !important;">Product Name</td>
                            <td class="bg-light p-3 px-4">STRAYKIDS Official Lightstick</td>
                        </tr>
                        <tr>
                            <td class="bg-primary text-white fw-bold text-center align-middle" style="background-color: #13195b !important;">Country of manufacture</td>
                            <td class="bg-light p-3 px-4">Republic of Korea</td>
                        </tr>
                        <tr>
                            <td class="bg-primary text-white fw-bold text-center align-middle" style="background-color: #13195b !important;">Power</td>
                            <td class="bg-light p-3 px-4">AAA X 3EA (not included)</td>
                        </tr>
                        <tr>
                            <td class="bg-primary text-white fw-bold text-center align-middle" style="background-color: #13195b !important;">Operation time</td>
                            <td class="bg-light p-3 px-4">*Approximately 3 hours<br>*New Batteries</td>
                        </tr>
                        <tr>
                            <td class="bg-primary text-white fw-bold text-center align-middle" style="background-color: #13195b !important;">Instructions for care and handling</td>
                            <td class="bg-light p-3 px-4">
                                <ol class="ps-3 mb-0 small">
                                    <li>Keep the product at room temperature, and away from high temperatures and humidity.</li>
                                    <li>Do not look at the light directly at a close distance.</li>
                                    <li>Be careful not to drop the product as impacts may cause the light color to change.</li>
                                    <li>Do not swallow the parts or put them in the mouth.</li>
                                    <li>Use standard alkaline AAA batteries that were not used before.</li>
                                </ol>
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <script>
    // 1. Select the elements
    const qtyInput = document.getElementById('quantity');
    const btnPlus = document.getElementById('increment');
    const btnMinus = document.getElementById('decrement');

    // 2. Add Plus functionality
    btnPlus.addEventListener('click', () => {
        // We use .value for <input> tags
        qtyInput.value = parseInt(qtyInput.value) + 1;
    });

    // 3. Add Minus functionality
    btnMinus.addEventListener('click', () => {
        let currentValue = parseInt(qtyInput.value);
        if (currentValue > 1) { 
            qtyInput.value = currentValue - 1;
        }
    });
    // Handle add to cart
    const addToCartForm = document.querySelector('form[action="../Cart/cart.php"]');
    addToCartForm.addEventListener('submit', function(e) {
        e.preventDefault();
        
        // Get form data
        const product_id = this.querySelector('input[name="product_id"]').value;
        const product_name = this.querySelector('input[name="product_name"]').value;
        const product_price = this.querySelector('input[name="product_price"]').value;
        const product_image = this.querySelector('input[name="product_image"]').value;
        const quantity = parseInt(this.querySelector('input[name="quantity"]').value);
        
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
                price: product_price,
                image: product_image,
                quantity: quantity
            });
        }
        
        // Save to localStorage
        localStorage.setItem('annyeong_cart', JSON.stringify(cart));
        alert('Product added to cart!');
    });</script>
</body>
</html>