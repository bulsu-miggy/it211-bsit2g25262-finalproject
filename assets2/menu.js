document.addEventListener("DOMContentLoaded", function() {
    const menuHTML = `
    <nav class="navbar navbar-expand-lg navbar-light bg-white border-bottom sticky-top">
        <div class="container">
            <a class="navbar-brand d-flex align-items-center fw-bold" href="index.php">
                <img src="assets2/logo.png" alt="Laces" style="width:35px;" class="me-2">
                Laces
            </a>

            <div class="collapse navbar-collapse justify-content-center" id="navbarNav">
                <div class="nav-pill-container">
                    <a class="nav-pill-link" href="index.php">Home</a>
                    <a class="nav-pill-link" href="#">Shop</a>
                    <a class="nav-pill-link" href="#">Categories</a>
                    <a class="nav-pill-link" href="#">Contact</a>
                </div>
            </div>

            <div class="d-flex align-items-center gap-3">
                <a href="#" class="text-dark"><i class="bi bi-search fs-5"></i></a>
                <button class="btn p-0 border-0" type="button" data-bs-toggle="offcanvas" data-bs-target="#profileDrawer">
                    <img src="assets2/gg--profile.png" width="24">
                </button>
                <a href="#" class="text-dark position-relative">
                    <i class="bi bi-cart fs-5"></i>
                </a>
            </div>
        </div>
    </nav>

    <div class="offcanvas offcanvas-end" tabindex="-1" id="profileDrawer" aria-labelledby="profileDrawerLabel">
        <div class="offcanvas-header border-bottom">
            <h5 class="offcanvas-title fw-bold" id="profileDrawerLabel">My Account</h5>
            <button type="button" class="btn-close" data-bs-dismiss="offcanvas" aria-label="Close"></button>
        </div>
        <div class="offcanvas-body">
            <div class="text-center mb-4">
                <img src="assets2/gg--profile.png" width="80" class="mb-2 opacity-50">
                <h6 class="fw-bold">Welcome back!</h6>
                <p class="small text-muted">Manage your orders and profile</p>
            </div>
            <ul class="list-group list-group-flush">
                <a href="profilepage.php" class="list-group-item list-group-item-action border-0 py-3"><i class="bi bi-person me-3"></i> My Profile</a>
                <a href="#" class="list-group-item list-group-item-action border-0 py-3"><i class="bi bi-box-seam me-3"></i> Order History</a>
                <a href="#" class="list-group-item list-group-item-action border-0 py-3"><i class="bi bi-heart me-3"></i> Wishlist</a>
                <a href="#" class="list-group-item list-group-item-action border-0 py-3 text-danger"><i class="bi bi-box-arrow-right me-3"></i> Logout</a>
            </ul>
        </div>
    </div>`;

    document.body.insertAdjacentHTML('afterbegin', menuHTML);
});