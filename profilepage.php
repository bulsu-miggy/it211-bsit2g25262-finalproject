<?php
require_once __DIR__ . '/db/action/dbconfig.php';
session_start();

if (!isset($_SESSION['username'])) {
    header('Location: loginpage.php');
    exit();
}

$stmt = $conn->prepare('SELECT l.id, l.first_name, l.last_name, l.username, l.email, l.img_url, p.contact_number, p.address, p.postal_code, COALESCE(NULLIF(p.profile_image, ""), l.img_url) AS profile_image FROM login l LEFT JOIN user_profiles p ON p.user_id = l.id WHERE l.username = :username LIMIT 1');
$stmt->execute([':username' => $_SESSION['username']]);
$user = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$user) {
    header('Location: loginpage.php');
    exit();
}

function escape($value)
{
    return htmlspecialchars((string)$value, ENT_QUOTES, 'UTF-8');
}

$displayName = trim((string)($user['first_name'] ?? '') . ' ' . (string)($user['last_name'] ?? ''));
if ($displayName === '') {
    $displayName = (string)$user['username'];
}

$profileImage = trim((string)($user['profile_image'] ?? ''));
if ($profileImage === '') {
    $profileImage = 'assets2/his48_c01.png';
}

$updated = ($_GET['updated'] ?? '') === '1';
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Profile - Laces</title>
    <link rel="icon" type="image/png" href="assets2/logo.png">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Montserrat:wght@400;600;700;800;900&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
    <link rel="stylesheet" href="css/master.css">
    <link rel="stylesheet" href="css/profile-style.css">
</head>
<body class="bg-white">
    <nav class="navbar bg-white border-bottom py-3">
    <div class="container d-flex align-items-center">
        <a href="index.php" class="d-flex align-items-center text-decoration-none text-dark fw-bold fs-4 me-3">
        <img src="assets2/logo.png" alt="Laces" style="width:35px;" class="me-2">
        Laces
        </a>
        <form class="flex-grow-1 mx-3 d-flex justify-content-center" role="search">
        <div class="position-relative w-100" style="max-width: 900px;">
            <input class="form-control rounded-pill border-dark ps-3 pe-5" type="search" placeholder="Search...">
            <i class="bi bi-search position-absolute top-50 end-0 translate-middle-y me-3 text-muted"></i>
        </div>
        </form>
        <div class="d-flex align-items-center gap-3">
                <a href="cart/cart.php"> <button class="btn p-0 border-0 bg-transparent" type="button">
            <img src="assets2/cart.png" width="20" alt="Cart">
        </button></a>
        <button class="btn p-0 border-0 bg-transparent" type="button"><img src="assets2/world.png" width="20" alt="World"></button>
        <button class="btn p-0 border-0 bg-transparent" type="button"><img src="assets2/si--notifications-alt-2-fill.png" width="20" alt="Notifications"></button>
        <button class="btn p-0 border-0 bg-transparent" type="button" data-bs-toggle="offcanvas" data-bs-target="#profileMenu"><img src="assets2/gg--profile.png" width="20" alt="Profile"></button>
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
                <img src="assets2/gg--profile.png" width="70" class="mb-2 opacity-75" alt="Profile">
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
                <a href="profilepage.php" class="list-group-item list-group-item-action border-0 py-3">
                    <i class="bi bi-person-circle me-3"></i> View Profile
                </a>
                <a href="cart/orderHistory.php" class="list-group-item list-group-item-action border-0 py-3">
                    <i class="bi bi-box-seam me-3"></i> My Orders
                </a>
                <a href="db/action/logout.php" class="list-group-item list-group-item-action border-0 py-3 text-danger">
                    <i class="bi bi-box-arrow-right me-3"></i> Sign Out
                </a>
            </div>
        </div>
    </div>
    <div class="text-center mt-3">
        <a href="index.php" class="mx-3 text-dark text-decoration-none custom-hover">Home</a>
        <a href="product-list.php?sort=sales&order=DESC" class="mx-3 text-dark text-decoration-none custom-hover">Trending</a>
        <a href="product-list.php" class="mx-3 text-dark text-decoration-none custom-hover">Categories</a>
        <a href="product-list.php" class="mx-3 text-dark text-decoration-none custom-hover">Product List</a>
    </div>

    <div class="container py-5 mt-4" style="max-width: 1100px;">
        <div class="mb-5">
            <h2 class="fw-800 display-6">Profile</h2>
        </div>
        <div class="row g-5 border-bottom pb-5 mb-5">
            <div class="col-md-6 d-flex align-items-center gap-4">
                <div class="position-relative">
                    <img src="<?php echo escape($profileImage); ?>" alt="Profile" class="rounded-circle border" width="170" height="170" style="object-fit: cover;" onerror="this.src='assets2/his48_c01.png'">
                </div>
                <div>
                    <h3 class="fw-800 mb-0"><?php echo escape($user['username']); ?></h3>
                    <p class="text-muted small mb-3"><?php echo escape($user['email']); ?></p>
                    <a href="edit-profile.php" class="btn btn-dark btn-sm px-4 rounded-pill fw-600">Edit Profile</a>
                </div>
            </div>
        </div>
        <div class="row g-5">
            <div class="col-md-6">
                <div class="mb-5">
                    <h6 class="small-label text-uppercase text-muted">Name</h6>
                    <p class="fw-700 fs-5"><?php echo escape($displayName); ?></p>
                </div>
                <div class="mb-5">
                    <h6 class="small-label text-uppercase text-muted">Address</h6>
                    <p class="fw-700 fs-5"><?php echo escape($user['address'] ?: 'Not set'); ?></p>
                </div>
                <div class="mb-5">
                    <h6 class="small-label text-uppercase text-muted">Postal Code</h6>
                    <p class="fw-700 fs-5"><?php echo escape($user['postal_code'] ?: 'Not set'); ?></p>
                </div>
            </div>
            <div class="col-md-6">
                <div class="mb-5">
                    <h6 class="small-label text-uppercase text-muted">Phone</h6>
                    <p class="fw-700 fs-5"><?php echo escape($user['contact_number'] ?: 'Not set'); ?></p>
                </div>
            </div>
        </div>
        <div class="text-end mt-4">
            <a href="db/action/logout.php" class="btn btn-danger px-4 fw-bold rounded-pill text-decoration-none">Log Out</a>
        </div>
    </div>

    <?php require_once __DIR__ . '/includes/user/root_footer.php'; ?>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <script>
        document.addEventListener('DOMContentLoaded', function () {
            const updated = <?php echo $updated ? 'true' : 'false'; ?>;
            if (!updated) {
                return;
            }
            Swal.fire({ icon: 'success', title: 'Profile updated', text: 'Your profile information has been saved.' });
        });
    </script>
    <script src="assets2/js/master.js"></script>
</body>
</html>
