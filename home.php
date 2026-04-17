<?php
// 1. Start the session to track user login status
session_start();

// 2. Determine if the user is logged in
$is_logged_in = isset($_SESSION['user_id']);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>SOLIS | Awaken Your Senses</title>
    <link rel="stylesheet" href="css/style.css">
</head>
<body>
    <?php 
    if ($is_logged_in) {
        include 'includes/member_header.php';
    } else {
        include 'guest_header/guest_header.php';
    }
    ?>

    <main>
        <section class="hero-banner">
            <h1>Awaken Your Senses</h1>
            <p>Discover candles that capture the warmth of golden hour, handcrafted with intention and light.</p>
            <a href="shop.php" class="btn-outline btn-hero">Shop Collection</a>
        </section>

        <section class="section-container">
            <img src="images/about.jpg" class="section-img" alt="Philosophy">
            <div class="section-text">
                <h2>The Solis Philosophy</h2>
                <p>Solis candles are born from the belief that light should do more than illuminate—it should elevate. Each candle is a radiant blend of carefully chosen scents, hand-poured with sustainable soy wax to transform your space into a sanctuary.</p>
                <a href="#" class="btn-outline">Our Process</a>
            </div>
        </section>

        <section class="section-container reverse">
            <img src="images/help.jpg" class="section-img" alt="Help">
            <div class="section-text">
                <h2>We're Here to Help</h2>
                <p>Have a question about our scents or need help with your order? Our concierge team is ready to assist you in finding the perfect glow for your home.</p>
                <a href="#" class="btn-outline">Contact Us</a>
            </div>
        </section>
    </main>

    <?php 
    if ($is_logged_in) {
        include 'includes/member_footer.php';
    } else {
        include 'guest_header/guestfooter.php';
    }
    ?>

</body>
</html>