<?php
/**
 * SOLIS Header Template
 */

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

$current_page = basename($_SERVER['PHP_SELF']);
$cart_count = 0;

if (isset($_SESSION['cart']) && !empty($_SESSION['cart'])) {
    foreach ($_SESSION['cart'] as $item) {
        $cart_count += $item['quantity'];
    }
}
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
            <a href="#" class="icon-link">
                <svg width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5"><circle cx="11" cy="11" r="8"></circle><line x1="21" y1="21" x2="16.65" y2="16.65"></line></svg>
            </a>

            <?php if (isset($_SESSION['user_id'])): ?>
                <div class="user-menu-wrapper" id="userMenuWrapper">
                    <a href="javascript:void(0)" class="icon-link" id="userIcon">
                        <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5"><path d="M20 21v-2a4 4 0 0 0-4-4H8a4 4 0 0 0-4 4v2"></path><circle cx="12" cy="7" r="4"></circle></svg>
                    </a>

                    <div class="profile-dropdown" id="userDropdown">
                        <div class="dropdown-user-info">
                            <?= $_SESSION['user_email']; ?>
                        </div>
                        <ul>
                            <li><a href="profile.php">My Profile</a></li>
                            <li><a href="db/action/logout.php" id="logoutLink" data-cart-count="<?= $cart_count ?>" class="danger-link">Logout</a></li>
                        </ul>
                    </div>
                </div>
            <?php else: ?>
                <a href="login.php" class="guest-login-link">Login</a>
            <?php endif; ?>

            <a href="basket.php" class="icon-link">
                <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5"><path d="M6 2L3 6v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2V6l-3-4Z"></path><line x1="3" y1="6" x2="21" y2="6"></line><path d="M16 10a4 4 0 0 1-8 0"></path></svg>
                <span class="cart-count <?= ($cart_count > 0) ? '' : 'hidden' ?>">
                    <?= $cart_count; ?>
                </span>
            </a>
        </div>
    </nav>
</header>

<script src="js/header.js"></script>