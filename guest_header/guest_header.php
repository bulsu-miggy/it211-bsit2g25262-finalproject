<?php
// Detect the current page filename
$current_page = basename($_SERVER['PHP_SELF']);
?>
<link href="https://fonts.googleapis.com/css2?family=Cormorant+Garamond:ital,wght@0,600;1,600&family=Montserrat:wght@400;500&display=swap" rel="stylesheet">
<link rel="stylesheet" href="css/style.css">
<header>
    <nav>
        <div class="nav-section nav-links">
            <a href="home.php" class="<?= ($current_page == 'home.php') ? 'active' : ''; ?>">Home</a>
            <a href="shop.php" class="<?= ($current_page == 'shop.php') ? 'active' : ''; ?>">Shop</a>
            <a href="our_story.php" class="<?= ($current_page == 'our_story.php') ? 'active' : ''; ?>">Our Story</a>
        </div>
        
        <div class="nav-section nav-center">
            <a href="home.php" class="logo">SOLIS</a>
        </div>
        
        <div class="nav-section nav-auth">
            <a href="#" class="icon-link nav-icon">
                <svg width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5"><circle cx="11" cy="11" r="8"></circle><line x1="21" y1="21" x2="16.65" y2="16.65"></line></svg>
            </a>
            
            <a href="login.php" class="<?php echo ($current_page == 'login.php' || $current_page == 'index.php') ? 'btn-black' : ''; ?>">Log In</a>
            <a href="signup.php" class="<?php echo ($current_page == 'signup.php') ? 'btn-black' : ''; ?>">Sign Up</a>
        </div>
    </nav>
</header>
<script src="js/header.js"></script>