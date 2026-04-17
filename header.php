<?php 
//(to esnure the correct nav link is highlighted)
$current = basename($_SERVER['PHP_SELF']); 
?>

<nav class="navbar py-3 shadow-sm" 
     style="background: linear-gradient(90deg, #000000 0%, #13195b 40%, #3a36c6 100%);">
     
    <div class="container-fluid px-5">
        
        <!-- LOGO + TITLE -->
        <a class="navbar-brand d-flex align-items-center fw-normal fs-3" 
           href="../index/index.php" 
           style="letter-spacing: 2px; text-decoration: none; color: white;">
           
            <img src="../images/header_icon/logo.png" style="width: 100px; margin-right: 12px; filter: drop-shadow(0 0 8px #4a45ff);">
            <h1 class="mb-0" style="font-size: 3.75rem; font-weight: 400;">ANNYEONG HAVEN</h1> 
        </a>

        <!-- ICONS -->
        <div class="d-flex align-items-center gap-4">
             <a href="../Cart/cart.php"><img src="../images/header_icon/cart.png" style="width: 65px;"></a>
             <a href="../profile/profile.php"><img src="../images/header_icon/profile.png" style="width: 65px;"></a>

             <?php if(isset($_SESSION["login_data"])): ?>
                <a class="btn btn-outline-light btn-sm ms-2" href="../logout.php">Logout</a>
             <?php endif; ?>
        </div>

    </div>
</nav>

<div class="container mt-4 mb-4">
    <div class="d-flex justify-content-center align-items-center gap-3 fw-light fs-5 text-secondary">

        <a href="../index/index.php" 
           class="text-decoration-none <?= ($current == 'index.php') ? 'fw-bold text-dark' : 'text-secondary' ?>">HOME</a>
            <span class="border-end border-secondary border-2" style="height: 40px;"></span>

        <a href="../products/products_overview.php" 
           class="text-decoration-none <?= ($current == 'products_overview.php') ? 'fw-bold text-dark' : 'text-secondary' ?>">PRODUCT</a>
            <span class="border-end border-secondary border-2" style="height: 40px;"></span>

        <a href="../About/about.php" 
           class="text-decoration-none <?= ($current == 'about.php') ? 'fw-bold text-dark' : 'text-secondary' ?>">ABOUT</a>
            <span class="border-end border-secondary border-2" style="height: 40px;"></span>

        <a href="../Contact/Contact.php" 
           class="text-decoration-none <?= ($current == 'Contact.php') ? 'fw-bold text-dark' : 'text-secondary' ?>">CONTACT</a>
            <span class="border-end border-secondary border-2" style="height: 40px;"></span>
    </div>

    
</div>