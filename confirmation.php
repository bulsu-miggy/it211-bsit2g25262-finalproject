<?php
session_start();
if (!isset($_SESSION["username"]) || !isset($_SESSION["password"])) {
    header('Location: login.php');
    exit();
}
include 'db/action/connect.php';

$order_id = $_GET['order_id'] ?? $_GET['success'] ? $_GET['order_id'] : 'N/A';
$username = $_SESSION["username"] ?? 'Guest';

// Fetch logged-in user details
$user_query = $conn->prepare("SELECT username, email FROM login WHERE username = ?");
$user_query->execute([$username]);
$user = $user_query->fetch(PDO::FETCH_ASSOC);
$user_name = $user ? $user['username'] : 'Guest';
$user_email = $user ? $user['email'] : 'No email';
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Order Confirmation | LYNX</title>
    <link rel="stylesheet" href="css/login.css">
    <link href="https://fonts.googleapis.com/css2?family=Rubik:wght@400;500;700;900&family=Rubik+Mono+One&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://fonts.googleapis.com/css2?family=Material+Symbols+Outlined:opsz,wght,FILL,GRAD@24,400,0,0" />
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>
    <style>
        body { font-family: 'Rubik', sans-serif; background: white; color: black; margin: 0; }
        header { padding: 20px 80px; display: flex; justify-content: space-between; align-items: center; }
        .logo { font-family: 'Rubik Mono One', sans-serif; font-size: 2.5rem; color: black; text-decoration: none; }
        .icons { display: flex; gap: 20px; }
        .material-symbols-outlined { font-size: 24px; cursor: pointer; color: black; }
        .container { max-width: 1000px; margin: 60px auto; padding: 0 40px; }
        .title { font-family: 'Rubik Mono One', sans-serif; font-size: 3rem; text-align: center; margin-bottom: 40px; letter-spacing: 2px; }
        .thank-you-box { background: #f8f8f8; padding: 60px; border-radius: 20px; text-align: center; margin-bottom: 60px; border: 2px solid black; }
        .print-btn { background: black; color: white; padding: 15px 30px; border-radius: 30px; cursor: pointer; margin-bottom: 30px; display: inline-flex; align-items: center; gap: 10px; font-weight: bold; transition: all 0.3s; }
        .print-btn:hover { transform: translateY(-2px); box-shadow: 0 10px 20px rgba(0,0,0,0.2); }
        .thank-you-box h2 { font-size: 2.5rem; margin-bottom: 20px; font-family: 'Rubik Mono One', sans-serif; }
        .order-grid { display: grid; grid-template-columns: 1fr 1fr; gap: 40px; margin-bottom: 60px; }
        .card { background: white; padding: 40px; border-radius: 20px; box-shadow: 0 10px 40px rgba(0,0,0,0.1); }
        .card h3 { font-family: 'Rubik Mono One', sans-serif; font-size: 1.8rem; margin-bottom: 30px; }
        .info-row { display: flex; justify-content: space-between; margin-bottom: 20px; font-size: 1.1rem; }
        .label { font-weight: bold; opacity: 0.8; }
        .product-info { margin-top: 20px; }
        .product-img { width: 100px; height: 120px; object-fit: cover; border-radius: 10px; margin-right: 20px; float: left; }
        .history-btn { width: 100%; padding: 20px; background: black; color: white; border: none; border-radius: 30px; font-size: 1.2rem; font-weight: bold; cursor: pointer; font-family: 'Rubik', sans-serif; transition: transform 0.3s; }
        .history-btn:hover { transform: translateY(-2px); }
        .footer-banner { background: black; padding: 60px 80px; color: white; margin-top: 100px; }
        .footer-grid { display: grid; grid-template-columns: 2fr 1fr 1fr 1fr; gap: 60px; max-width: 1200px; margin: 0 auto; align-items: start; }
        .footer-logo { font-family: 'Rubik Mono One', sans-serif; font-size: 3rem; font-weight: bold; letter-spacing: 2px; }
        .footer-col h3 { font-weight: bold; font-size: 1.2rem; margin-bottom: 20px; }
        .footer-col ul { list-style: none; padding: 0; }
        .footer-col li { margin-bottom: 10px; }
        .footer-col a { color: white; text-decoration: none; }
        @media (max-width: 768px) { .order-grid { grid-template-columns: 1fr; } header { padding: 20px; } .container { padding: 0 20px; } }
    </style>
</head>
<body>
    <!-- HEADER -->
    <header class="header">
        <a href="index.php" class="logo">LYNX</a>
        <nav style="display: flex; gap: 30px;">
            <a href="women.php" style="color: black; text-decoration: none; font-weight: 500;">WOMEN</a>
            <a href="men.php" style="color: black; text-decoration: none; font-weight: 500;">MEN</a>
            <?php if (isset($_SESSION['role']) && $_SESSION['role'] === 'admin'): ?>
                <a href="addproduct.php" style="color: black; text-decoration: none; font-weight: 500;">ADD PRODUCT</a>
            <?php endif; ?>
        </nav>
        <div class="icons">
            <span class="material-symbols-outlined" onclick="openSearchModal()" style="cursor: pointer;" title="Search">search</span>
            <a href="shopping-cart.php" style="color: black; text-decoration: none;">
                <span class="material-symbols-outlined">shopping_cart</span>
            </a>
            <a href="profiles.php" style="color: black; text-decoration: none;">
                <span class="material-symbols-outlined">account_circle</span>
            </a>
            <a href="#" class="logout-btn" style="color: black; text-decoration: none;">
                <span class="material-symbols-outlined">logout</span>
            </a>
        </div>
    </header>

    <!-- SEARCH MODAL -->
    <div id="searchModal" style="display: none; position: fixed; top: 0; left: 0; width: 100%; height: 100%; background: rgba(0,0,0,0.7); z-index: 1000;">
        <div style="position: absolute; top: 50%; left: 50%; transform: translate(-50%, -50%); background: white; padding: 40px; border-radius: 15px; width: 90%; max-width: 600px;">
            <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 20px;">
                <h2 style="font-family: 'Rubik Mono One', sans-serif; font-size: 1.8rem; margin: 0;">SEARCH</h2>
                <span class="material-symbols-outlined" onclick="closeSearchModal()" style="cursor: pointer; font-size: 28px; color: #666;">close</span>
            </div>
            <form id="searchForm" onsubmit="performSearch(event)" style="display: flex; gap: 10px;">
                <input type="text" id="searchInput" placeholder="Search products..." style="flex: 1; padding: 15px 20px; border: 2px solid #ddd; border-radius: 8px;" autofocus>
                <button type="submit" style="padding: 15px 30px; background: black; color: white; border: none; border-radius: 8px; cursor: pointer; font-weight: bold;">SEARCH</button>
            </form>
        </div>
    </div>

    <div class="container">
        <h1 class="title">ORDER CONFIRMATION</h1>

        <div class="thank-you-box">
            <button class="print-btn" onclick="window.print()">
                <span class="material-symbols-outlined">print</span> PRINT
            </button>
            <h2>Thank you for your order, <?php echo htmlspecialchars($user_name); ?>!</h2>
            <p>A confirmation email will be sent to you at <strong><?php echo htmlspecialchars($user_email); ?></strong> with your complete details.</p>
            <p>For in-store order pickups, please be sure to print this order confirmation and valid ID.</p>
        </div>

        <div class="order-grid">
            <div class="card">
                <h3>Order Information</h3>
                <div class="info-row">
                    <span class="label">ORDER NO.</span>
                    <span><?php echo htmlspecialchars($order_id); ?></span>
                </div>
                <div class="info-row">
                    <span class="label">CONTACT</span>
                    <span style="text-decoration: underline;"><?php echo htmlspecialchars($user_email); ?></span>
                </div>
                <div class="info-row">
                    <span class="label">ADDRESS</span>
                    <div style="text-align: right;">
                        <?php echo htmlspecialchars($user_name); ?> Alissa Dela Cruz<br>
                        7513 N Brad Road<br>
                        Mount Morris, MI 48458
                    </div>
                </div>
            </div>

            <div class="card">
                <h3>Order Details</h3>
                <div class="info-row"><span class="label">HOME SHOPPING</span></div>
                <div class="info-row"><span class="label">ARRIVES IN:</span><span>3-4 Business Days</span></div>
                <div class="info-row"><span class="label">PRODUCT ID:</span><span>140734</span></div>
                
                <div class="product-info">
                    <img src="https://placehold.co/120x150/EBEBEB/000?text=Product" alt="Product" class="product-img">
                    <div>
                        <strong>LYNX FLEX SHORTS</strong><br>
                        Color: 04 Black<br>
                        Size: Medium<br>
                        Qty: 1<br><br>
                        <strong>PHP 299.00</strong>
                    </div>
                </div>
            </div>
        </div>

        <a href="profiles.php" class="history-btn">GO TO MY ORDER HISTORY</a>
    </div>

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
        function openSearchModal() {
            document.getElementById('searchModal').style.display = 'block';
        }
        function closeSearchModal() {
            document.getElementById('searchModal').style.display = 'none';
        }
        function performSearch(event) {
            event.preventDefault();
            const query = document.getElementById('searchInput').value.trim();
            if (query.length >= 2) {
                window.location.href = 'search.php?q=' + encodeURIComponent(query);
            }
        }
        document.addEventListener('click', (e) => {
            if (e.target.id === 'searchModal') closeSearchModal();
        });
        document.addEventListener('keydown', (e) => {
            if (e.key === 'Escape') closeSearchModal();
        });
    </script>

  <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
  <script>
$(document).ready(function() {
    $('.logout-btn').on('click', function(e) {
        e.preventDefault(); 
        
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
</body>
</html>
