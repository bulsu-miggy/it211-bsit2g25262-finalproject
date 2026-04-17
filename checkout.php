<?php
  session_start();
  if (!isset($_SESSION["username"]) || !isset($_SESSION["password"])) {
    header('Location: login.php');
    exit();
  }

  $cart_items = $_SESSION['cart'] ?? [];
  $cart_products = [];
  $items_total = 0;

  if (!empty($cart_items)) {
    try {
      $conn = new PDO("mysql:host=localhost;dbname=lynx_db", "root", "");
      $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
      
      foreach ($cart_items as $key => $item) {
        $stmt = $conn->prepare("SELECT id, title, price, imgurl FROM {$item['table']} WHERE id = ?");
        $stmt->execute([$item['id']]);
        $prod = $stmt->fetch(PDO::FETCH_ASSOC);
        if ($prod) {
          $prod['key'] = $key;
          $prod['qty'] = $item['qty'];
          $prod['color'] = $item['color'];
          $prod['size'] = $item['size'];
          $cart_products[] = $prod;
          $items_total += $prod['price'] * $item['qty'];
        }
      }
    } catch (PDOException $e) {}
  }

  if (empty($cart_products)) {
    header('Location: shopping-cart.php');
    exit();
  }

  $vat = $items_total * 0.12;
  $subtotal = $items_total + $vat;
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>CHECKOUT - LYNX</title>
    <link rel="stylesheet" href="css/login.css">
    <link href="https://fonts.googleapis.com/css2?family=Rubik:wght@400;500;700;900&family=Rubik+Mono+One&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://fonts.googleapis.com/css2?family=Material+Symbols+Outlined:opsz,wght,FILL,GRAD@24,400,0,0" />
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>
    <style>
      .checkout-container {
        max-width: 1400px;
        margin: 40px auto;
        padding: 0 20px;
        display: grid;
        grid-template-columns: 1.5fr 1fr;
        gap: 40px;
        font-family: 'Rubik', sans-serif;
        align-items: start;
      }
      .checkout-form {
        background: white;
        width: 100%;
      }
      .checkout-header {
        font-family: 'Rubik Mono One', sans-serif;
        font-size: 2.5rem;
        color: black;
        margin-bottom: 40px;
        letter-spacing: 2px;
      }
      .form-section {
        margin-bottom: 40px;
      }
      .form-section h3 {
        font-size: 1.3rem;
        font-weight: bold;
        color: black;
        margin-bottom: 20px;
        border-bottom: 2px solid #eee;
        padding-bottom: 10px;
      }
      .form-row {
        display: grid;
        grid-template-columns: 1fr 1fr;
        gap: 20px;
        margin-bottom: 15px;
      }
      .form-row.full {
        grid-template-columns: 1fr;
      }
      .form-group {
        display: flex;
        flex-direction: column;
      }
      .form-group label {
        font-weight: 500;
        margin-bottom: 8px;
        color: #333;
        font-size: 0.95rem;
      }
      .form-group input,
      .form-group select {
        padding: 12px;
        border: 2px solid #ddd;
        border-radius: 8px;
        font-size: 1rem;
        font-family: 'Rubik', sans-serif;
        transition: border-color 0.3s;
      }
      .form-group input:focus,
      .form-group select:focus {
        outline: none;
        border-color: black;
      }
      .checkbox-group {
        display: flex;
        align-items: center;
        gap: 10px;
        margin-top: 15px;
      }
      .checkbox-group input[type="checkbox"] {
        width: 20px;
        height: 20px;
        cursor: pointer;
      }
      .checkbox-group label {
        margin: 0;
        cursor: pointer;
        font-weight: 500;
      }
      .delivery-option {
        border: 2px solid #ddd;
        border-radius: 8px;
        padding: 15px;
        margin-bottom: 15px;
        cursor: pointer;
        transition: all 0.3s;
        display: flex;
        flex-direction: column;
      }
      .delivery-option:hover {
        border-color: black;
      }
      .delivery-option.selected {
        border-color: black;
        background: #f9f9f9;
      }
      .delivery-option input[type="radio"] {
        margin-right: 10px;
        cursor: pointer;
        align-self: flex-start;
        margin-top: 2px;
      }
      .delivery-option h4 {
        margin: 0 0 8px 0;
        font-weight: bold;
        color: black;
        display: flex;
        align-items: center;
      }
      .delivery-option p {
        margin: 5px 0;
        color: #666;
        font-size: 0.9rem;
        margin-left: 24px;
      }
      .payment-method {
        border: 2px solid #ddd;
        border-radius: 8px;
        padding: 15px;
        margin-bottom: 15px;
        cursor: pointer;
        transition: all 0.3s;
        display: flex;
        flex-direction: column;
      }
      .payment-method:hover {
        border-color: black;
      }
      .payment-method.selected {
        border-color: black;
        background: #f9f9f9;
      }
      .payment-method input[type="radio"] {
        margin-right: 10px;
        cursor: pointer;
        align-self: flex-start;
        margin-top: 2px;
      }
      .payment-method h4 {
        margin: 0;
        font-weight: bold;
        color: black;
        display: flex;
        align-items: center;
      }
      .card-fields {
        display: none;
        grid-template-columns: 1fr 1fr;
        gap: 15px;
        margin-top: 15px;
        padding: 15px 0 0 30px;
      }
      .card-fields.show {
        display: grid;
      }
      .card-fields .form-group.full {
        grid-column: 1 / -1;
      }
      .order-summary {
        background: #f9f9f9;
        padding: 30px;
        border-radius: 15px;
        height: fit-content;
        position: sticky;
        top: 20px;
      }
      .summary-header {
        font-weight: bold;
        font-size: 1.2rem;
        margin-bottom: 20px;
        color: black;
      }
      .summary-item {
        display: grid;
        grid-template-columns: 60px 1fr auto;
        gap: 15px;
        margin-bottom: 15px;
        padding-bottom: 15px;
        border-bottom: 1px solid #eee;
      }
      .summary-item:last-of-type {
        border-bottom: none;
      }
      .summary-item img {
        width: 60px;
        height: 60px;
        object-fit: cover;
        border-radius: 8px;
      }
      .summary-item-info h4 {
        margin: 0 0 3px 0;
        font-size: 0.9rem;
        font-weight: bold;
      }
      .summary-item-info p {
        margin: 0;
        font-size: 0.8rem;
        color: #666;
      }
      .summary-item-price {
        text-align: right;
        font-weight: bold;
      }
      .summary-totals {
        border-top: 2px solid #ddd;
        padding-top: 20px;
        margin-top: 20px;
      }
      .summary-row {
        display: flex;
        justify-content: space-between;
        margin-bottom: 10px;
        font-size: 0.95rem;
      }
      .summary-total {
        display: flex;
        justify-content: space-between;
        font-size: 1.3rem;
        font-weight: bold;
        color: black;
        margin-top: 15px;
      }
      .place-order-btn {
        width: 100%;
        padding: 18px;
        background: black;
        color: white;
        border: none;
        border-radius: 30px;
        font-size: 1.1rem;
        font-weight: bold;
        cursor: pointer;
        margin-top: 30px;
        transition: transform 0.3s;
      }
      .place-order-btn:hover {
        transform: translateY(-2px);
      }
      @media (max-width: 968px) {
        .checkout-container {
          grid-template-columns: 1fr;
        }
        .order-summary {
          position: static;
        }
      }
      body {
        background: white;
      }
      .main {
        background: white;
        padding: 0;
      }
    </style>
</head>
<body>
  <header class="header">
    <a href="index.php" style="text-decoration: none; color: black;"><h1 class="logo">LYNX</h1></a>
    <nav class="nav">
      <a href="women.php">WOMEN</a>
      <a href="men.php">MEN</a>
    </nav>
    <div class="icons">
      <span class="material-symbols-outlined" onclick="openSearchModal()" style="cursor: pointer; transition: all 0.3s;" title="Search">search</span>
      <a href="shopping-cart.php" title="Cart" style="color: black; text-decoration: none;">
        <span class="material-symbols-outlined">shopping_cart</span>
      </a>
      <a href="profiles.php" title="Profile" style="color: black; text-decoration: none;">
        <span class="material-symbols-outlined">account_circle</span>
      </a>
      <a href="#" id="logout-trigger" style="color: black; text-decoration: none;">
        <span class="material-symbols-outlined">logout</span>
      </a>
    </div>
  </header>

  <!-- SEARCH MODAL -->
  <div id="searchModal" style="display: none; position: fixed; top: 0; left: 0; width: 100%; height: 100%; background: rgba(0,0,0,0.7); z-index: 1000; animation: fadeIn 0.3s ease;">
    <div style="position: absolute; top: 50%; left: 50%; transform: translate(-50%, -50%); background: white; padding: 40px; border-radius: 15px; width: 90%; max-width: 600px; box-shadow: 0 10px 50px rgba(0,0,0,0.3); animation: slideUp 0.3s ease;">
      <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 20px;">
        <h2 style="font-family: 'Rubik Mono One', sans-serif; font-size: 1.8rem; margin: 0; color: black;">SEARCH</h2>
        <span class="material-symbols-outlined" onclick="closeSearchModal()" style="cursor: pointer; font-size: 28px; color: #666;">close</span>
      </div>
      <form id="searchForm" onsubmit="performSearch(event)" style="display: flex; gap: 10px;">
        <input type="text" id="searchInput" placeholder="Search products..." style="flex: 1; padding: 15px 20px; border: 2px solid #ddd; border-radius: 8px; font-size: 1rem; font-family: 'Rubik', sans-serif;" autofocus>
        <button type="submit" style="padding: 15px 30px; background: black; color: white; border: none; border-radius: 8px; cursor: pointer; font-family: 'Rubik', sans-serif; font-weight: bold; transition: all 0.3s; white-space: nowrap;">SEARCH</button>
      </form>
    </div>
  </div>

  <style>
    @keyframes fadeIn {
      from { opacity: 0; }
      to { opacity: 1; }
    }
    @keyframes slideUp {
      from { transform: translate(-50%, -40%); opacity: 0; }
      to { transform: translate(-50%, -50%); opacity: 1; }
    }
  </style>

  <main class="main">
    <div class="checkout-container">
      <!-- Left: Checkout Form -->
        <form class="checkout-form" method="POST" action="process_checkout.php">
        <h1 class="checkout-header">CHECKOUT</h1>
        <input type="hidden" name="total_amount" value="<?php echo $subtotal; ?>"> 

        <!-- Contact Information -->
        <div class="form-section">
          <h3>Contact Information</h3>
          <div class="form-row full">
            <div class="form-group">
              <label for="email"></label>
              <input type="email" id="email" name="email" required placeholder="Email address">
            </div>
          </div>
        </div>

        <!-- Delivery Address -->
        <div class="form-section">
          <h3>Delivery Address</h3>
          <div class="form-row">
            <div class="form-group">
              <label for="firstName"></label>
              <input type="text" id="firstName" name="firstName" required placeholder="First name">
            </div>
            <div class="form-group">
              <label for="lastName"></label>
              <input type="text" id="lastName" name="lastName" required placeholder="Last name">
            </div>
          </div>
          <div class="form-row">
            <div class="form-group">
              <label for="phone"></label>
              <input type="tel" id="phone" name="phone" required placeholder="Phone number">
            </div>
            <div class="form-group">
              <label for="postalCode"></label>
              <input type="text" id="postalCode" name="postalCode" required placeholder="Postal code">
            </div>
          </div>
          <div class="form-row full">
            <div class="form-group">
              <label for="street">Address</label>
              <input type="text" id="street" name="street" required placeholder="Street address">
            </div>
          </div>
          <div class="form-row">
            <div class="form-group">
              <label for="state">State/Province</label>
              <select id="province" name="state" required>
                <option value="" selected disabled>SELECT STATE/PROVINCE</option>
                  <option value="Bulacan">Bulacan</option>
                  <option value="Pampanga">Pampanga</option>
                  <option value="Metro Manila">Metro Manila</option>
              </select>
            </div>
            <div class="form-group">
              <label for="city">City/Municipality</label>
              <select id="city" name="city" required>
            <option value="" selected disabled>SELECT STATE/PROVINCE FIRST</option>
        </select>
            </div>
          </div>
          <div class="form-row full">
            <div class="form-group">
              <label for="barangay"></label>
              <input type="text" id="barangay" name="barangay" required placeholder="Barangay">
            </div>
          </div>
          <div class="checkbox-group">
            <input type="checkbox" id="billingAddress" name="billingAddress">
            <label for="billingAddress">Use as billing address</label>
          </div>
        </div>

        <!-- Delivery Options -->
        <div class="form-section">
          <h3>Delivery Options</h3>
          
          <label class="delivery-option">
            <input type="radio" name="delivery" value="standard" checked onchange="updateDeliveryOption(this)">
            <h4>Standard Delivery (Free)</h4>
            <p><strong>Metro Manila:</strong> Next Day (if ordered before 12 PM) or 2–4 Days (after 12 PM)</p>
            <p><strong>Luzon:</strong> 3–8 Days</p>
            <p><strong>Visayas / Mindanao:</strong> 5–8 Days</p>
          </label>

          <label class="delivery-option">
            <input type="radio" name="delivery" value="pickup" onchange="updateDeliveryOption(this)">
            <h4>Lynx Hub | Store Pickup (Free)</h4>
            <p><strong>Location:</strong> Lynx Flagship - Bulacan, Unit B-204 Industrial Heights</p>
            <p><strong>Available From:</strong> Monday, 23 Mar</p>
            <p><strong>Opening Hours:</strong> Mon-Fri (10 AM - 8 PM), Sat (10 AM - 9 PM), Sun (11 AM - 7 PM)</p>
          </label>
        </div>

        <!-- Payment Methods -->
        <div class="form-section">
          <h3>Payment Method</h3>
          
          <label class="payment-method">
            <input type="radio" name="payment" value="gcash" onchange="updatePaymentMethod(this)">
            <h4>GCash - Digital Wallet</h4>
          </label>

          <label class="payment-method">
            <input type="radio" name="payment" value="card" onchange="updatePaymentMethod(this)">
            <h4>Credit / Debit Card</h4>
            <div class="card-fields" id="cardFields">
              <div class="form-group full">
                <label for="cardName">Name on Card</label>
                <input type="text" id="cardName" name="cardName" placeholder="John Doe">
              </div>
              <div class="form-group full">
                <label for="cardNumber">Card Number</label>
                <input type="text" id="cardNumber" name="cardNumber" placeholder="1234 5678 9012 3456">
              </div>
              <div class="form-group">
                <label for="expiryDate">Expiry Date</label>
                <input type="text" id="expiryDate" name="expiryDate" placeholder="MM/YY">
              </div>
              <div class="form-group">
                <label for="cvv">CVC/CVV</label>
                <input type="text" id="cvv" name="cvv" placeholder="123">
              </div>
            </div>
          </label>

          <label class="payment-method">
            <input type="radio" name="payment" value="paypal" onchange="updatePaymentMethod(this)">
            <h4>PayPal - Online Payment Service</h4>
          </label>
        </div>

        <!-- Legal Acknowledgment -->
        <div class="form-section">
          <div class="checkbox-group">
            <input type="checkbox" id="terms" name="terms" required>
            <label for="terms">I agree to the Terms & Conditions, Delivery Terms, and Privacy Notice</label>
          </div>
        </div>

        <!-- Place Order Button -->
        <button type="submit" class="place-order-btn">PLACE ORDER</button>
      </form>

      <!-- Right: Order Summary -->
      <div class="order-summary">
        <div class="summary-header">Order Summary</div>

        <?php foreach ($cart_products as $item): ?>
        <div class="summary-item">
          <img src="<?php echo htmlspecialchars('images/products/' . $item['imgurl']); ?>" alt="<?php echo htmlspecialchars($item['title']); ?>">
          <div class="summary-item-info">
            <h4><?php echo htmlspecialchars($item['title']); ?></h4>
            <p>Qty: <?php echo $item['qty']; ?> × PHP <?php echo number_format($item['price'], 2); ?></p>
          </div>
          <div class="summary-item-price">PHP <?php echo number_format($item['price'] * $item['qty'], 2); ?></div>
        </div>
        <?php endforeach; ?>

        <div class="summary-totals">
          <div class="summary-row">
            <span>Subtotal</span>
            <span>PHP <?php echo number_format($items_total, 2); ?></span>
          </div>
          <div class="summary-row">
            <span>VAT (12%)</span>
            <span>PHP <?php echo number_format($vat, 2); ?></span>
          </div>
          <div class="summary-row">
            <span>Delivery Fee</span>
            <span>FREE</span>
          </div>
          <div class="summary-total">
            <span>Total</span>
            <span>PHP <?php echo number_format($subtotal, 2); ?></span>
          </div>
        </div>
      </div>
    </div>
  </main>

  <!-- Footer - Very Bottom -->
  <footer class="footer-banner" style="background: black; padding: 60px 20px; color: white; font-family: 'Rubik', sans-serif;">
    <div style="max-width: 1200px; margin: 0 auto; display: grid; grid-template-columns: 2fr 1fr 1fr 1fr; gap: 60px; align-items: start;">
      <!-- LYNX Logo -->
      <div>
        <h1 style="font-family: 'Rubik Mono One', sans-serif; font-size: 3rem; font-weight: bold; letter-spacing: 2px; margin: 0; color: white;">LYNX</h1>
      </div>
      
      <!-- SHOP -->
      <div>
        <h3 style="font-weight: bold; font-size: 1.2rem; margin-bottom: 20px;">SHOP</h3>
        <ul style="list-style: none; padding: 0;">
          <li style="margin-bottom: 10px;"><a href="#" style="color: white; text-decoration: none;">MEN</a></li>
          <li style="margin-bottom: 10px;"><a href="#" style="color: white; text-decoration: none;">WOMEN</a></li>
        </ul>
      </div>
      
      <!-- COMPANY -->
      <div>
        <h3 style="font-weight: bold; font-size: 1.2rem; margin-bottom: 20px;">COMPANY</h3>
        <ul style="list-style: none; padding: 0;">
          <li style="margin-bottom: 10px;"><a href="about.php" style="color: white; text-decoration: none;">ABOUT US</a></li>
        </ul>
      </div>
      
      <!-- BECOME A MEMBER -->
      <div style="text-align: right;">
        <h3 style="font-weight: bold; font-size: 1.2rem; margin-bottom: 20px;">BECOME A MEMBER</h3>
        <ul style="list-style: none; padding: 0;">
          <li style="margin-bottom: 10px;"><a href="register.php" style="color: white; text-decoration: none;">JOIN US</a></li>
        </ul>
      </div>
    </div>
  </footer>

  <script>
    $(document).ready(function() {
    $('.logout-btn').on('click', function(e) {
        e.preventDefault(); 
        
        console.log("Logout clicked!"); 

        Swal.fire({
            title: 'Logout of LYNX?',
            text: "Are you sure you want to sign out?",
            icon: 'warning',
            showCancelButton: true,
            confirmButtonColor: '#000000',
            cancelButtonColor: '#d33',
            confirmButtonText: 'Yes, Logout',
            cancelButtonText: 'Cancel'
        }).then((result) => {
            if (result.isConfirmed) {
                window.location.href = 'logout.php'; 
            }
        });
    });
});
</script>

  <script>
$(document).ready(function() {
    // Dictionary of cities (In a real app, this could come from a database)
    const cityData = {
        "Bulacan": ["Malolos City", "Meycauayan City", "Hagonoy", "Calumpit", "Guiguinto", "Baliuag"],
        "Pampanga": ["Angeles City", "San Fernando City", "Mabalacat", "Lubao", "Mexico"],
        "Metro Manila": ["Quezon City", "Manila", "Makati", "Taguig", "Pasig"]
    };

    $('#province').on('change', function() {
        const selectedProvince = $(this).val();
        const $cityDropdown = $('#city');

        // Clear existing options
        $cityDropdown.empty();
        $cityDropdown.append('<option value="" selected disabled>SELECT CITY/MUNICIPALITY</option>');

        // If the selected province exists in our data
        if (cityData[selectedProvince]) {
            // Loop through the cities and add them as options
            $.each(cityData[selectedProvince], function(index, cityName) {
                $cityDropdown.append($('<option></option>').val(cityName).text(cityName));
            });
        }
    });
});
</script>

  <script>
    function updatePaymentMethod(radio) {
      // Remove selected class from all payment methods
      document.querySelectorAll('.payment-method').forEach(method => {
        method.classList.remove('selected');
      });
      
      // Add selected class to clicked payment method
      radio.closest('.payment-method').classList.add('selected');
      
      // Show/hide card fields
      const cardFields = document.getElementById('cardFields');
      if (radio.value === 'card') {
        cardFields.classList.add('show');
      } else {
        cardFields.classList.remove('show');
      }
    }

    function updateDeliveryOption(radio) {
      // Remove selected class from all delivery options
      document.querySelectorAll('.delivery-option').forEach(option => {
        option.classList.remove('selected');
      });
      
      // Add selected class to clicked delivery option
      radio.closest('.delivery-option').classList.add('selected');
    }

    // Form submits to process_checkout.php - no JS intercept needed
    // Client-side validation kept for UX
    document.getElementById('checkoutForm').addEventListener('submit', function(e) {
      const terms = document.getElementById('terms').checked;
      if (!terms) {
        e.preventDefault();
        alert('Please agree to the Terms & Conditions');
        return;
      }
    });

    // Initialize selected delivery option
    document.querySelector('input[name="delivery"][checked]').closest('.delivery-option')?.classList.add('selected');

    // Auto-fill checkout form with profile data
    document.addEventListener('DOMContentLoaded', () => {
      const userEmail = localStorage.getItem('userEmail') || '';
      const userFirstName = localStorage.getItem('userFirstName') || '';
      const userLastName = localStorage.getItem('userLastName') || '';
      const userPhone = localStorage.getItem('userPhone') || '';
      const userStreet = localStorage.getItem('userStreet') || '';
      const userPostal = localStorage.getItem('userPostal') || '';
      const userState = localStorage.getItem('userState') || '';
      const userCity = localStorage.getItem('userCity') || '';
      const userBarangay = localStorage.getItem('userBarangay') || '';

      if (userEmail) document.getElementById('email').value = userEmail;
      if (userFirstName) document.getElementById('firstName').value = userFirstName;
      if (userLastName) document.getElementById('lastName').value = userLastName;
      if (userPhone) document.getElementById('phone').value = userPhone;
      if (userStreet) document.getElementById('street').value = userStreet;
      if (userPostal) document.getElementById('postalCode').value = userPostal;
      if (userState) document.getElementById('state').value = userState;
      if (userCity) document.getElementById('city').value = userCity;
      if (userBarangay) document.getElementById('barangay').value = userBarangay;
    });

    // Search Modal Functions
    function openSearchModal() {
      document.getElementById('searchModal').style.display = 'flex';
      document.getElementById('searchInput').focus();
    }

    function closeSearchModal() {
      document.getElementById('searchModal').style.display = 'none';
      document.getElementById('searchInput').value = '';
    }

    function performSearch(event) {
      event.preventDefault();
      const query = document.getElementById('searchInput').value.trim();
      if (query.length >= 2) {
        window.location.href = 'search.php?q=' + encodeURIComponent(query);
      } else {
        alert('Please enter at least 2 characters to search');
      }
    }

    // Close modal when clicking outside
    document.addEventListener('click', function(event) {
      const modal = document.getElementById('searchModal');
      if (event.target === modal) {
        closeSearchModal();
      }
    });

    // Allow closing modal with Escape key
    document.addEventListener('keydown', function(event) {
      if (event.key === 'Escape') {
        closeSearchModal();
      }
    });
\n  </script>\n\n  <script>\n  $(document).ready(function() {\n    $('#logout-trigger').click(function(e) {\n      e.preventDefault();\n      Swal.fire({\n        title: 'Are you sure?',\n        text: \"You will be logged out!\",\n        icon: 'warning',\n        showCancelButton: true,\n        confirmButtonColor: '#000000',\n        cancelButtonColor: '#6c757d',\n        confirmButtonText: 'Yes, logout!'\n      }).then((result) => {\n        if (result.isConfirmed) {\n          window.location.href = 'logout.php';\n        }\n      });\n    });\n  });\n  </script>\n</body>\n</html>
