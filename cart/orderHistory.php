<?php
require_once '../db/action/dbconfig.php';
session_start();

if (!isset($_SESSION['username'])) {
    header('Location: ../loginpage.php');
    exit();
}

$stmt = $conn->prepare('SELECT id FROM login WHERE username = :username LIMIT 1');
$stmt->execute([':username' => $_SESSION['username']]);
$user = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$user) {
    header('Location: ../loginpage.php');
    exit();
}

$stmt = $conn->prepare('SELECT id, order_code, total_amount, status, created_at FROM orders WHERE user_id = :uid ORDER BY created_at DESC');
$stmt->execute([':uid' => (int)$user['id']]);
$orders = $stmt->fetchAll(PDO::FETCH_ASSOC);
$placed = ($_GET['placed'] ?? '') === '1';

function escape($value)
{
    return htmlspecialchars((string)$value, ENT_QUOTES, 'UTF-8');
}

function statusBadgeClass($status)
{
    $status = strtolower(trim((string)$status));
    if ($status === 'delivered' || $status === 'completed') {
        return 'bg-success-subtle text-success';
    }
    if ($status === 'cancelled') {
        return 'bg-danger-subtle text-danger';
    }
    return 'bg-secondary-subtle text-secondary';
}

function statusIconClass($status)
{
    $status = strtolower(trim((string)$status));
    if ($status === 'delivered' || $status === 'completed') {
        return 'bi-check-circle-fill';
    }
    if ($status === 'cancelled') {
        return 'bi-x-circle-fill';
    }
    return 'bi-dash-circle-fill';
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="icon" type="image/png" href="../assets2/logo.png">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.1/font/bootstrap-icons.css">
    <link rel="stylesheet" href="../css/master.css">
    <link rel="stylesheet" href="../css/style2.css">
    <title>Laces - Order History</title>
</head>
<body>
    <?php require_once '../includes/user/cart_nav.php'; ?>

     <!-- atitle section -->
      <div class="container mt-3">
            <a href="javascript:history.back()" class="text-dark text-decoration-none mb-3 fs-1">&#9664;</a>        <div class ="d-flex justify-content-between align-items-center mb-4">
            <div class="cart-title">
                Orders List
            </div>
            <div>
            </div>
        </div>
        <hr class="border-dark border-2 mt-2">
      </div>
    <!-- Table section -->
      <div class="container mt-4">
        <div class="card shadow-sm border-0">
            <div class="card-body p-0">
                <div class="d-flex px-4 py-3 bg-white text-muted small fw-semibold border-bottom">
                    <div class="col">Order ID</div>
                    <div class="col">Total</div>
                    <div class="col">Status</div>
                    <div class="col">Order Date</div>
                    <div class="col text-end"></div>
                </div>

                <?php if (empty($orders)): ?>
                <div class="text-center text-muted py-5">No orders yet. Place your first order from checkout.</div>
                <?php else: ?>
                <?php foreach ($orders as $order): ?>
                <div class="d-flex align-items-center px-3 py-3 border-bottom">
                    <div class="col d-flex align-items-center gap-2">
                        <span class="bg-warning rounded-circle" style="width:10px; height:10px;"></span>
                        <?php echo escape($order['order_code']); ?>
                    </div>

                    <div class="col">PHP <?php echo number_format((float)$order['total_amount'], 2); ?></div>

                    <div class="col">
                        <span class="badge <?php echo statusBadgeClass($order['status']); ?> rounded-pill py-2 px-3">
                            <i class="bi <?php echo statusIconClass($order['status']); ?>"></i>
                            <?php echo escape($order['status']); ?>
                        </span>
                    </div>

                    <div class="col"><?php echo date('D, d F, Y', strtotime($order['created_at'])); ?></div>
                    <div class="col text-end">
                        <a href="../track.php?order_id=<?php echo (int)$order['id']; ?>"><button class="btn btn-warning btn-sm rounded-pill fw-bold text-white">ORDER STATUS</button></a>
                    </div>
                </div>
                <?php endforeach; ?>
                <?php endif; ?>
            </div>
        </div>
      </div>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <script src="main.js"></script>

    <?php require_once '../includes/user/cart_footer.php'; ?>

    <!-- Scripts -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <script>
        document.addEventListener('DOMContentLoaded', function () {
            const placed = <?php echo $placed ? 'true' : 'false'; ?>;
            if (!placed) {
                return;
            }

            Swal.fire({
                icon: 'success',
                title: 'Order placed',
                text: 'Your order is now in Pending status.'
            });
        });
    </script>
    <script src="../assets2/js/master.js"></script>
</body>
</html>