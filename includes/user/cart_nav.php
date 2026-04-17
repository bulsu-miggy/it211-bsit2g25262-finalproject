<nav class="navbar bg-white border-bottom py-3">
    <div class="container d-flex align-items-center">
        <a href="../index.php" class="d-flex align-items-center text-decoration-none text-dark fw-bold fs-4 me-3">
            <img src="../assets2/logo.png" alt="Laces" style="width:35px;" class="me-2"> Laces
        </a>
        <form class="search-form flex-grow-1 mx-3 d-flex justify-content-center" role="search">
            <div class="position-relative w-100" style="max-width: 900px;">
                <input class="form-control search-pill rounded-pill border-dark ps-3 pe-5" type="search" placeholder="Search...">
                <i class="bi bi-search position-absolute top-50 end-0 translate-middle-y me-3 text-muted"></i>
            </div>
        </form>
        <div class="d-flex align-items-center gap-3">
            <a href="cart.php"><button class="btn p-0 border-0 bg-transparent" type="button"><img src="../assets2/cart.png" width="20" alt="Cart"></button></a>
            <button class="btn p-0 border-0 bg-transparent" type="button"><img src="../assets2/world.png" width="20" alt="Language"></button>
            <button class="btn p-0 border-0 bg-transparent" type="button"><img src="../assets2/si--notifications-alt-2-fill.png" width="20" alt="Notifications"></button>
            <button class="btn p-0 border-0 bg-transparent" type="button" data-bs-toggle="offcanvas" data-bs-target="#profileMenu">
                <img src="../assets2/gg--profile.png" width="20" alt="Profile">
            </button>
        </div>
    </div>
</nav>

<div class="offcanvas offcanvas-end" tabindex="-1" id="profileMenu" aria-labelledby="profileMenuLabel">
    <div class="offcanvas-header border-bottom">
        <h5 class="offcanvas-title fw-bold" id="profileMenuLabel">My Account</h5>
        <button type="button" class="btn-close" data-bs-dismiss="offcanvas" aria-label="Close"></button>
    </div>
    <div class="offcanvas-body">
        <div class="text-center mb-4">
            <img src="../assets2/gg--profile.png" width="70" class="mb-2 opacity-75" alt="Profile">
            <h6 class="fw-bold">Welcome back!</h6>
            <p class="small text-muted">Manage your orders and preferences</p>
        </div>
        <div class="list-group list-group-flush">
            <div class="list-group-item border-0 py-3 theme-toggle-item" data-theme-toggle-item>
                <label class="theme-toggle-label mb-0 form-check form-switch">
                    <span class="theme-toggle-copy">
                        <i class="bi bi-moon-stars me-3"></i> Dark mode
                    </span>
                    <input class="form-check-input theme-toggle-input" type="checkbox" role="switch" aria-label="Toggle dark mode">
                </label>
            </div>
            <a href="../profilepage.php" class="list-group-item list-group-item-action border-0 py-3">
                <i class="bi bi-person-circle me-3"></i> View Profile
            </a>
            <a href="orderHistory.php" class="list-group-item list-group-item-action border-0 py-3">
                <i class="bi bi-box-seam me-3"></i> My Orders
            </a>
            <a href="../db/action/logout.php" class="list-group-item list-group-item-action border-0 py-3 text-danger">
                <i class="bi bi-box-arrow-right me-3"></i> Sign Out
            </a>
        </div>
    </div>
</div>

<div class="text-center mt-3">
    <a href="../index.php" class="mx-3 text-dark text-decoration-none custom-hover">Home</a>
    <a href="../product-list.php?sort=sales&order=DESC" class="mx-3 text-dark text-decoration-none custom-hover">Trending</a>
    <a href="../product-list.php" class="mx-3 text-dark text-decoration-none custom-hover">Categories</a>
    <a href="../product-list.php" class="mx-3 text-dark text-decoration-none custom-hover">Product List</a>
</div>
