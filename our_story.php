<?php 
session_start();
// 1. Check login status for consistent navigation
$is_logged_in = isset($_SESSION['user_id']);

// 2. Include appropriate headers based on user session
if ($is_logged_in) {
    include 'includes/member_header.php';
} else {
    include 'guest_header/guest_header.php';
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Our Story | SOLIS</title>
    <link href="https://fonts.googleapis.com/css2?family=Cormorant+Garamond:ital,wght@0,400;0,600;1,400&family=Montserrat:wght@300;400&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="css/style.css">
</head>
<body>

<div class="story-container">
    <span class="story-header">Est. 2026</span>
    <h1 class="story-title">Crafting light with intention.</h1>

   <img src="images/group_pic2.png" alt="Solis Craftsmanship" class="story-image">

    <div class="content-block">
    <p>
        At SOLIS, we believe that a candle is more than just a source of light—it is a vessel for memory, 
        a companion to focus, and a catalyst for tranquility. Our journey began in a small studio 
        with a simple mission: to create clean-burning, thoughtfully scented candles that elevate 
        the everyday into the extraordinary.
    </p>
</div>

<div class="content-block centered">
    <h2 class="story-subsection-title">Meet the Artisans</h2>
    <div class="team-list">
        <strong>Shandy Santillan</strong> | 
        <strong>Joshua Huevos</strong> | 
        <strong>Marcella Fajardo</strong> | 
        <strong>Maisie Joy Broas</strong>
    </div>
</div>

<div class="philosophy-section">
    <h2 class="story-section-heading">The Solis Philosophy</h2>
    <p class="philosophy-text">
        "To provide a gentle glow that honors the slow moments of life, 
        using only the finest sustainable materials and fragrance profiles 
        that speak to the soul."
    </p>
</div>

<div class="content-block">
    <p>
        Each of our candles is hand-poured in small batches to ensure the highest quality. We utilize 
        premium soy wax and lead-free cotton wicks, paired with fragrance oils that are free from 
        harmful phthalates.
    </p>
</div>

    <a href="shop.php" class="btn-shop">Explore the Collection</a>
</div>

<?php 
// 3. Include appropriate footers
if ($is_logged_in) {
    include 'includes/member_footer.php';
} else {
    include 'guest_header/guestfooter.php';
}
?>

</body>
</html>