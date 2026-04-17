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

$fullName = trim((string)($user['first_name'] ?? '') . ' ' . (string)($user['last_name'] ?? ''));
$profileImage = trim((string)($user['profile_image'] ?? ''));
if ($profileImage === '') {
    $profileImage = 'assets2/his48_c01.png';
}

$saveError = ($_GET['error'] ?? '') === 'save_failed';
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Edit Profile - Laces</title>
    <link rel="icon" type="image/png" href="assets2/logo.png">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Inter:opsz,wght@14..32,100..900&family=Montserrat:wght@400;600;700;800;900&display=swap" rel="stylesheet">
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
                <a href="cart/cart.php"><button class="btn p-0 border-0 bg-transparent" type="button"><img src="assets2/cart.png" width="20" alt="Cart"></button></a>
                <button class="btn p-0 border-0 bg-transparent" type="button"><img src="assets2/world.png" width="20" alt="World"></button>
                <button class="btn p-0 border-0 bg-transparent" type="button"><img src="assets2/si--notifications-alt-2-fill.png" width="20" alt="Notifications"></button>
                <button class="btn p-0 border-0 bg-transparent" type="button" data-bs-toggle="offcanvas" data-bs-target="#profileMenu"><img src="assets2/gg--profile.png" width="20" alt="Profile"></button>
            </div>
        </div>
    </nav>

    <div class="text-center mt-3">
        <a href="index.php" class="mx-3 text-dark text-decoration-none custom-hover">Home</a>
        <a href="product-list.php?sort=sales&order=DESC" class="mx-3 text-dark text-decoration-none custom-hover">Trending</a>
        <a href="product-list.php" class="mx-3 text-dark text-decoration-none custom-hover">Categories</a>
        <a href="product-list.php" class="mx-3 text-dark text-decoration-none custom-hover">Product List</a>
    </div>

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

    <div class="container py-5 mt-4" style="max-width: 1100px;">
        <div class="white-container shadow-custom rounded-4 p-4 p-md-5">
            <div class="d-flex align-items-center justify-content-between border-bottom pb-3 mb-4">
                <h2 class="fw-900 mb-0">Edit Profile</h2>
                <span class="small-label">Profile settings</span>
            </div>

            <form action="db/action/update_profile.php" method="POST" enctype="multipart/form-data" class="profile-edit-form">
                <div class="row g-5 align-items-center mb-5">
                    <div class="col-md-auto">
                        <div class="position-relative d-inline-block">
                            <img src="<?php echo escape($profileImage); ?>" alt="Profile" class="rounded-circle border" width="170" height="170" style="object-fit: cover;" onerror="this.src='assets2/his48_c01.png'">
                        </div>
                    </div>
                    <div class="col-md">
                        <div class="mb-3" style="max-width: 340px;">
                            <label class="small-label mb-1">Profile Picture</label>
                            <input type="file" name="profile_image" class="form-control" accept="image/png,image/jpeg,image/jpg,image/webp,image/gif">
                            <small class="text-muted">Accepted: JPG, PNG, WEBP, GIF (max 2MB)</small>
                        </div>
                        <div class="d-flex align-items-center gap-2 mb-1">
                            <input type="text" name="username" class="form-control form-control-lg fw-800 rounded-pill bg-light" value="<?php echo escape($user['username']); ?>" style="max-width: 300px;">
                            <i class="bi bi-pencil-fill text-muted edit-icon"></i>
                        </div>
                        <p class="text-muted small mt-2"><?php echo escape($user['email']); ?></p>
                    </div>
                </div>

                <div class="row g-4">
                    <div class="col-md-6">
                        <div class="mb-4">
                            <label class="small-label mb-1">Full Name</label>
                            <input type="text" name="fullname" class="form-control rounded-pill bg-light border-0" value="<?php echo escape($fullName); ?>">
                        </div>
                        <div class="mb-4">
                            <label class="small-label mb-1">Address</label>
                            <textarea name="address" class="form-control rounded-3 bg-light border-0" rows="2"><?php echo escape($user['address'] ?? ''); ?></textarea>
                        </div>
                        <div class="mb-4">
                            <label class="small-label mb-1">Postal Code</label>
                            <input type="text" name="postal_code" class="form-control rounded-pill bg-light border-0" value="<?php echo escape($user['postal_code'] ?? ''); ?>">
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="mb-4">
                            <label class="small-label mb-1">Phone</label>
                            <input type="text" name="phone" class="form-control rounded-pill bg-light border-0" value="<?php echo escape($user['contact_number'] ?? ''); ?>">
                        </div>
                    </div>
                </div>

                <div class="d-flex gap-3 mt-5 pt-3">
                    <button type="submit" class="btn btn-warning rounded-pill px-5 py-2 fw-semibold">Save Edits</button>
                    <a href="profilepage.php" class="btn btn-outline-danger rounded-pill px-5 py-2 fw-semibold">Cancel</a>
                </div>
            </form>
        </div>
    </div>

    <?php require_once __DIR__ . '/includes/user/root_footer.php'; ?>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <script>
        document.addEventListener('DOMContentLoaded', function () {
            const saveError = <?php echo $saveError ? 'true' : 'false'; ?>;
            if (!saveError) {
                return;
            }
            Swal.fire({ icon: 'error', title: 'Save failed', text: 'Could not save profile. Please try again.' });
        });
    </script>
    <script src="assets2/js/master.js"></script>
</body>
</html>
