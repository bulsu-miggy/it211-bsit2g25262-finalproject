<?php
// Database connection
require_once '../db/action/dbconfig.php';

// Start session to get admin info
session_start();

// Get current admin info
$adminUsername = isset($_SESSION['username']) ? $_SESSION['username'] : 'Admin';
$adminInitial = strtoupper(substr($adminUsername, 0, 1));

// Fetch total users (active customers)
$stmt = $conn->prepare("SELECT COUNT(*) as total FROM login WHERE role = 'user'");
$stmt->execute();
$totalUsers = $stmt->fetch(PDO::FETCH_ASSOC)['total'] ?? 0;

// Fetch total orders
$stmt = $conn->prepare("SELECT COUNT(*) as total FROM orders");
$stmt->execute();
$totalOrders = $stmt->fetch(PDO::FETCH_ASSOC)['total'] ?? 0;

// Fetch low stock count (products with stock <= 10)
$stmt = $conn->prepare("SELECT COUNT(*) as low_stock_count FROM products WHERE stock <= 10");
$stmt->execute();
$lowStockCount = $stmt->fetch(PDO::FETCH_ASSOC)['low_stock_count'] ?? 0;

// Fetch total revenue (sum of total_amount from orders)
$stmt = $conn->prepare("SELECT SUM(total_amount) as revenue FROM orders WHERE status != 'Cancelled'");
$stmt->execute();
$totalRevenue = $stmt->fetch(PDO::FETCH_ASSOC)['revenue'] ?? 0;
// Build current month revenue chart data by day
$dashboardCurrentMonth = date('F');
$dashboardCurrentYear = date('Y');
$dashboardChartData = [];
$monthStart = new DateTime('first day of this month');
$monthEnd = new DateTime('last day of this month');
$daysInMonth = (int) $monthStart->format('t');

for ($day = 1; $day <= $daysInMonth; $day++) {
    $dayDate = clone $monthStart;
    $dayDate->modify('+' . ($day - 1) . ' days');

    $stmt = $conn->prepare("SELECT COALESCE(SUM(total_amount), 0) as revenue FROM orders WHERE DATE(created_at) = :date AND status != 'Cancelled'");
    $stmt->execute([':date' => $dayDate->format('Y-m-d')]);
    $revenue = (float) ($stmt->fetch(PDO::FETCH_ASSOC)['revenue'] ?? 0);

    $dashboardChartData[] = [
        'day' => sprintf('%02d', $day),
        'label' => $dayDate->format('M j'),
        'revenue' => round($revenue, 2),
    ];
}
// Fetch low stock products (stock <= 10)
$stmt = $conn->prepare("
    SELECT id, name, stock, price FROM products 
    WHERE stock <= 10 
    ORDER BY stock ASC 
    LIMIT 5
");
$stmt->execute();
$lowStockProducts = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Fetch recent orders with product details (last 4 orders)
$stmt = $conn->prepare("
    SELECT 
        o.id as order_id,
        o.order_code,
        o.total_amount,
        o.status,
        o.created_at,
        COUNT(DISTINCT oi.product_id) as item_count,
        SUM(oi.quantity) as total_quantity
    FROM orders o
    LEFT JOIN order_items oi ON o.id = oi.order_id
    GROUP BY o.id
    ORDER BY o.created_at DESC
    LIMIT 4
");
$stmt->execute();
$recentOrders = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Get product details for each recent order
$ordersWithProducts = [];
foreach ($recentOrders as $order) {
    $stmt = $conn->prepare("
        SELECT p.name, p.price, oi.quantity, oi.subtotal
        FROM order_items oi
        JOIN products p ON oi.product_id = p.id
        WHERE oi.order_id = :order_id
        LIMIT 1
    ");
    $stmt->execute([':order_id' => $order['order_id']]);
    $product = $stmt->fetch(PDO::FETCH_ASSOC);
    $order['product'] = $product;
    $ordersWithProducts[] = $order;
}

// Calculate order time distribution (morning, afternoon, evening)
$stmt = $conn->prepare("
    SELECT
        SUM(CASE WHEN HOUR(created_at) >= 0 AND HOUR(created_at) < 12 THEN 1 ELSE 0 END) as morning,
        SUM(CASE WHEN HOUR(created_at) >= 12 AND HOUR(created_at) < 18 THEN 1 ELSE 0 END) as afternoon,
        SUM(CASE WHEN HOUR(created_at) >= 18 AND HOUR(created_at) < 24 THEN 1 ELSE 0 END) as evening
    FROM orders
");
$stmt->execute();
$orderTimeData = $stmt->fetch(PDO::FETCH_ASSOC);

$totalTimeOrders = ($orderTimeData['morning'] ?? 0) + ($orderTimeData['afternoon'] ?? 0) + ($orderTimeData['evening'] ?? 0);
$morningPercent = $totalTimeOrders > 0 ? round(($orderTimeData['morning'] / $totalTimeOrders) * 100) : 0;
$afternoonPercent = $totalTimeOrders > 0 ? round(($orderTimeData['afternoon'] / $totalTimeOrders) * 100) : 0;
$eveningPercent = $totalTimeOrders > 0 ? round(($orderTimeData['evening'] / $totalTimeOrders) * 100) : 0;

// Fetch admin/team members (users with admin role or specific team members)
$stmt = $conn->prepare("
    SELECT id, first_name, last_name, email FROM login 
    WHERE role = 'admin' 
    ORDER BY login_date DESC
    LIMIT 4
");
$stmt->execute();
$teamMembers = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Lace's Dashboard</title>
    <link rel="icon" type="image/png" href="../assets2/logo.png">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.1/font/bootstrap-icons.css">
    <link rel="stylesheet" href="admin.css">
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/js/bootstrap.bundle.min.js"></script>
</head>
<body class="bg-light min-vh-100 text-dark">
    <!-- navigation sidebar -->
    <nav id="sidebar" class="bg-white border-end d-flex flex-column position-fixed top-0 start-0 min-vh-100" style="z-index:1000;">
        <!-- logo -->
        <div class="border-bottom px-3 py-3 d-flex align-items-center gap-2 fw-bold fs-5">
            <span style="font-size:1.4rem;"></span>Laces
        </div>

        <!-- Navigation part -->
        <ul class="nav flex-column mt-3">
            <li class="nav-item">
                <a href="dashboard.php" class="nav-link active d-flex align-items-center gap-2 fw-semibold rounded mx-2 my-1 px-3 py-2">
                    <i class="bi bi-grid-1x2-fill"></i>
                    Dashboard
                </a>
            </li>
            <li class="nav-item">
                <a href="orders.php" class="nav-link d-flex align-items-center gap-2 fw-semibold rounded text-secondary mx-2 my-1 px-3 py-2">
                    <i class="bi bi-cart"></i>
                    Orders
                </a>
            </li>
            <li class="nav-item">
                <a href="product.php" class="nav-link d-flex align-items-center gap-2 fw-semibold rounded text-secondary mx-2 my-1 px-3 py-2">
                    <i class="bi bi-box-seam"></i>
                    Products
                </a>
            </li>
            <li class="nav-item">
                <a href="categories.php" class="nav-link d-flex align-items-center gap-2 fw-semibold rounded text-secondary mx-2 my-1 px-3 py-2">
                    <i class="bi bi-folder"></i>
                    Categories
                </a>
            </li>
            <li class="nav-item">
                <a href="customer.php" class="nav-link d-flex align-items-center gap-2 fw-semibold rounded text-secondary mx-2 my-1 px-3 py-2">
                    <i class="bi bi-people"></i>
                    Customers
                </a>
            </li>
            <li class="nav-item">
                <a href="analytics.php" class="nav-link d-flex align-items-center gap-2 fw-semibold rounded text-secondary mx-2 my-1 px-3 py-2">
                    <i class="bi bi-bar-chart"></i>
                    Analytics
                </a>
            </li>
        </ul>
        <!-- footer -->
        <div class="mt-auto border-top px-3 py-3">
            <div class="d-flex align-items-center gap-2">
                <div class="rounded-circle bg-primary d-flex align-items-center justify-content-center text-white fw-bold flex-shrink-0" style="width: 36px; height: 36px; font-size:.8rem;">A</div>
                <div>
                    <div class="fw-bold" style="font-size:.82rem;line-height:1.2;">Abudul</div>
                    <div class="text-secondary" style="font-size:.72rem;">Admin</div>
                </div>
            </div>
        </div>
    </nav>

    <!-- top part -->
    <div id="topbar" class="bg-white border-bottom d-flex align-items-center px-4 sticky-top" style="height:60px;z-index:999;">
        <h5 class="mb-0 fw-bold fs-5">Dashboard</h5>

        <!-- search part -->
        <div class="position-relative ms-3" style="max-width:260px;flex:1;">
            <i class="bi bi-search text-secondary search-icon"></i>
            <input id="dashboardSearch" type="text" class="form-control bg-light border search-input" placeholder="Search…"/>
        </div>

        <!-- right part -->
        <div class="ms-auto d-flex align-items-center gap-3">
            <div class="position-relative">
                <i class="bi bi-bell fs-5 text-secondary"></i>
                <span class="position-absolute top-0 start-100 translate-middle badge rounded-pill bg-danger" style="font-size:.6rem;">3</span>
            </div>
            <div class="dropdown">
                <button class="btn btn-link text-dark p-0 d-flex align-items-center gap-2 dropdown-toggle" type="button" data-bs-toggle="dropdown" aria-expanded="false" style="text-decoration: none;">
                    <div class="rounded-circle bg-primary d-flex align-items-center justify-content-center text-white fw-bold flex-shrink-0" style="width:36px;height:36px;font-size:.8rem;"><?php echo $adminInitial; ?></div>
                    <div>
                        <div class="fw-bold" style="font-size:.82rem;line-height:1.1;"><?php echo htmlspecialchars($adminUsername); ?></div>
                        <div class="text-secondary" style="font-size:.72rem;">Admin</div>
                    </div>
                </button>
                <ul class="dropdown-menu dropdown-menu-end">
                    <?php if (isset($_SESSION['role']) && $_SESSION['role'] === 'superadmin'): ?>
                    <li><a class="dropdown-item" href="settings.php"><i class="bi bi-gear me-2"></i>Settings</a></li>
                    <li><hr class="dropdown-divider"></li>
                    <?php endif; ?>
                    <li><a class="dropdown-item text-danger" href="javascript:void(0)" onclick="confirmLogout()"><i class="bi bi-box-arrow-right me-2"></i>Logout</a></li>
                </ul>
            </div>
        </div>
    </div>

    <!-- main part -->
    <div id="main" class="p-4">
        <div class="d-flex align-items-start justify-content-between mb-4">
        <div>
        <h4 class="fw-bold mb-1">Dashboard</h4>
        <p class="text-secondary mb-0 orders-subtitle">Manage and track your store performance</p>
        </div>
    </div>
        <!-- stat part -->
        <div class="row g-3 mb-4">
            <!-- total users / active customers -->
            <div class="col-md-4 col-lg-3">
                <a href="customer.php" class="text-decoration-none text-dark">
                <div class="bg-white border rounded-3 p-3 h-100">
                    <div class="d-flex align-items-center gap-3 mb-3">
                        <div class="d-flex align-items-center justify-content-center rounded-3 fs-4 flex-shrink-0" style="width:48px;height:48px;background:#fce7f3;">
                            <i class="bi bi-people-fill" style="color:#db2777;"></i>
                        </div>
                        <div>
                            <div class="fw-bold" style="font-size:1.55rem;"><?php echo number_format($totalUsers); ?></div>
                            <div class="text-secondary fw-semibold small">Active Customers</div>
                        </div>
                    </div>
                    <span class="badge rounded-pill fw-bold text-success" style="background:#dcfce7;font-size:.72rem;">
                        <i class="bi bi-arrow-up-short"></i>Live
                    </span>
                </div>
                </a>
            </div>

            <!-- Total Orders -->
            <div class="col-md-4 col-lg-3">
                <a href="orders.php" class="text-decoration-none text-dark">
                <div class="bg-white border rounded-3 p-3 h-100">
                    <div class="d-flex align-items-center gap-3 mb-3">
                        <div class="d-flex align-items-center justify-content-center rounded-3 fs-4 flex-shrink-0" style="width:48px;height:48px;background:#fef9c3;">
                            <i class="bi bi-cart-fill" style="color:#ca8a04;"></i>
                        </div>
                        <div>
                            <div class="fw-bold" style="font-size:1.55rem;"><?php echo number_format($totalOrders); ?></div>
                            <div class="text-secondary fw-semibold small">Total Orders</div>
                        </div>
                    </div>
                    <span class="badge rounded-pill fw-bold text-success" style="background:#dcfce7;font-size:.72rem;">
                        <i class="bi bi-arrow-up-short"></i>Active
                    </span>
                </div>
                </a>
            </div>

            <!-- low stock alerts -->
            <div class="col-md-4 col-lg-3">
                <div class="bg-white border rounded-3 p-3 h-100">
                    <a href="product.php" class="text-decoration-none text-dark">
                    <div class="d-flex align-items-center gap-3 mb-3">
                        <div class="d-flex align-items-center justify-content-center rounded-3 fs-4 flex-shrink-0" style="width:48px;height:48px;background:#fef3c7;">
                            <i class="bi bi-exclamation-triangle-fill" style="color:#d97706;"></i>
                        </div>
                        <div>
                            <div class="fw-bold" style="font-size:1.55rem;"><?php echo number_format($lowStockCount); ?></div>
                            <div class="text-secondary fw-semibold small">Low Stock Alerts</div>
                        </div>
                    </div>
                    <span class="badge rounded-pill fw-bold" style="background:#fef3c7;font-size:.72rem;">
                        <i class="bi bi-exclamation-circle"></i> Attention
                    </span>
                    </a>
                </div>
            </div>

            <!-- order time -->
            <div class="col-lg-3 d-none d-lg-block">
                <div class="bg-white border rounded-3 p-3 h-100">
                    <div class="d-flex justify-content-between align-items-start mb-2">
                        <div>
                            <div class="fw-bold" style="font-size:.9rem;">Order Time</div>
                            <div class="text-secondary" style="font-size:.72rem;">All Time</div>
                        </div>
                        <a href="analytics.php" class="text-primary fw-bold" style="font-size:.72rem;">View Report</a>
                    </div>
                    <div class="d-flex align-items-center gap-3 mt-2">
                        <div class="donut-wrap flex-shrink-0">
                            <svg viewBox="0 0 36 36" xmlns="http://www.w3.org/2000/svg">
                                <circle cx="18" cy="18" r="15.9" fill="none" stroke="#e2e8f0" stroke-width="3.5"/>
                                <circle cx="18" cy="18" r="15.9" fill="none" stroke="#3b82f6" stroke-width="3.5" stroke-dasharray="<?php echo $afternoonPercent; ?> <?php echo 100 - $afternoonPercent; ?>" stroke-dashoffset="25" stroke-linecap="round"/>
                                <circle cx="18" cy="18" r="15.9" fill="none" stroke="#93c5fd" stroke-width="3.5" stroke-dasharray="<?php echo $eveningPercent; ?> <?php echo 100 - $eveningPercent; ?>" stroke-dashoffset="<?php echo -15 - $afternoonPercent; ?>" stroke-linecap="round"/>
                                <circle cx="18" cy="18" r="15.9" fill="none" stroke="#bfdbfe" stroke-width="3.5" stroke-dasharray="<?php echo $morningPercent; ?> <?php echo 100 - $morningPercent; ?>" stroke-dashoffset="<?php echo -47 - $afternoonPercent - $eveningPercent; ?>" stroke-linecap="round"/>
                            </svg>
                        </div>
                        <div class="fw-bold" style="font-size:.72rem;">
                            <div class="mb-1"><span style="color:#3b82f6;">●</span> Afternoon <?php echo $afternoonPercent; ?>%</div>
                            <div class="mb-1"><span style="color:#93c5fd;">●</span> Evening <?php echo $eveningPercent; ?>%</div>
                            <div><span style="color:#bfdbfe;">●</span> Morning <?php echo $morningPercent; ?>%</div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <!-- Revenue + Right column -->
        <div class="row g-3">

            <!-- Left: Revenue + Recent Orders -->
            <div class="col-lg-8">

                <!-- Revenue -->
                <div class="bg-white border rounded-3 p-3">
                    <div class="d-flex flex-column flex-md-row justify-content-between align-items-start gap-3 mb-3">
                        <div>
                            <div class="fw-bold" style="font-size:.9rem;">Total Revenue</div>
                            <div class="fw-bold" style="font-size:2rem;">₱<?php echo number_format($totalRevenue, 2); ?></div>
                            <div class="text-secondary" style="font-size:.78rem;">
                                <i class="bi bi-info-circle"></i> All successful orders
                            </div>
                            <div class="text-secondary mt-1" style="font-size:.78rem;">As of <?php echo $dashboardCurrentMonth . ' ' . $dashboardCurrentYear; ?></div>
                        </div>
                        <div class="d-flex align-items-center">
                            <a href="orders.php" class="btn btn-sm btn-outline-primary">View Report</a>
                        </div>
                    </div>

                    <div class="mt-3">
                        <div class="text-secondary small mb-2">Revenue by day for <?php echo $dashboardCurrentMonth . ' ' . $dashboardCurrentYear; ?></div>
                        <div id="dashboardChartDiv" style="width: 100%; height: 320px;"></div>
                    </div>
                </div>

                <!-- Recent Orders -->
                <div class="bg-white border rounded-3 p-3 mt-3">
                    <div class="d-flex justify-content-between align-items-center mb-3">
                        <div class="fw-bold">Recent Orders</div>
                        <a href="orders.php" class="btn btn-sm btn-outline-secondary rounded-pill" style="font-size:.75rem;">
                            <i class="bi bi-three-dots"></i>
                        </a>
                    </div>
                    <div class="table-responsive">
                        <table class="table orders-table align-middle mb-0">
                            <thead class="text-secondary fw-bold">
                                <tr>
                                    <th class="px-3 py-2">Order ID</th>
                                    <th class="px-3 py-2">Product Name</th>
                                    <th class="px-3 py-2">Price</th>
                                    <th class="px-3 py-2">Qty</th>
                                    <th class="px-3 py-2">Total Amount</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php if (count($ordersWithProducts) > 0): ?>
                                    <?php foreach ($ordersWithProducts as $order): ?>
                                    <tr>
                                        <td class="px-3 py-2 fw-semibold"><?php echo htmlspecialchars($order['order_code']); ?></td>
                                        <td class="px-3 py-2">
                                            <div class="d-flex align-items-center gap-2">
                                                <div class="bg-light rounded-2 d-flex align-items-center justify-content-center flex-shrink-0" style="width:32px;height:32px;">📦</div>
                                                <span class="fw-semibold"><?php echo htmlspecialchars($order['product']['name'] ?? 'N/A'); ?></span>
                                            </div>
                                        </td>
                                        <td class="px-3 py-2 fw-semibold">₱<?php echo number_format($order['product']['price'] ?? 0, 2); ?></td>
                                        <td class="px-3 py-2">
                                            <span class="badge rounded-pill fw-bold text-primary" style="background:#eff6ff;font-size:.8rem;"><?php echo $order['product']['quantity'] ?? 0; ?></span>
                                        </td>
                                        <td class="px-3 py-2 fw-semibold">₱<?php echo number_format($order['total_amount'], 2); ?></td>
                                    </tr>
                                    <?php endforeach; ?>
                                    <tr id="dashboardNoResultsRow" class="text-center text-secondary d-none">
                                        <td colspan="5" class="py-4">
                                            <i class="bi bi-search"></i> No results match your search.
                                        </td>
                                    </tr>
                                <?php else: ?>
                                    <tr>
                                        <td colspan="5" class="text-center text-secondary py-4">
                                            <i class="bi bi-inbox"></i> No orders yet
                                        </td>
                                    </tr>
                                <?php endif; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>

            <!-- Right column -->
            <div class="col-lg-4 d-flex flex-column gap-3">

                <!-- Order Time (mobile only) -->
                <div class="bg-white border rounded-3 p-3 d-lg-none">
                    <div class="d-flex justify-content-between align-items-start mb-2">
                        <div>
                            <div class="fw-bold" style="font-size:.9rem;">Order Time</div>
                            <div class="text-secondary" style="font-size:.72rem;">All Time</div>
                        </div>
                        <a href="analytics.php" class="text-primary fw-bold" style="font-size:.72rem;">View Report</a>
                    </div>
                    <div class="d-flex align-items-center gap-3 mt-2">
                        <div class="donut-wrap flex-shrink-0">
                            <svg viewBox="0 0 36 36" xmlns="http://www.w3.org/2000/svg">
                                <circle cx="18" cy="18" r="15.9" fill="none" stroke="#e2e8f0" stroke-width="3.5"/>
                                <circle cx="18" cy="18" r="15.9" fill="none" stroke="#3b82f6" stroke-width="3.5" stroke-dasharray="<?php echo $afternoonPercent; ?> <?php echo 100 - $afternoonPercent; ?>" stroke-dashoffset="25" stroke-linecap="round"/>
                                <circle cx="18" cy="18" r="15.9" fill="none" stroke="#93c5fd" stroke-width="3.5" stroke-dasharray="<?php echo $eveningPercent; ?> <?php echo 100 - $eveningPercent; ?>" stroke-dashoffset="<?php echo -15 - $afternoonPercent; ?>" stroke-linecap="round"/>
                                <circle cx="18" cy="18" r="15.9" fill="none" stroke="#bfdbfe" stroke-width="3.5" stroke-dasharray="<?php echo $morningPercent; ?> <?php echo 100 - $morningPercent; ?>" stroke-dashoffset="<?php echo -47 - $afternoonPercent - $eveningPercent; ?>" stroke-linecap="round"/>
                            </svg>
                        </div>
                        <div class="fw-bold" style="font-size:.72rem;">
                            <div class="mb-1"><span style="color:#3b82f6;">●</span> Afternoon <?php echo $afternoonPercent; ?>%</div>
                            <div class="mb-1"><span style="color:#93c5fd;">●</span> Evening <?php echo $eveningPercent; ?>%</div>
                            <div><span style="color:#bfdbfe;">●</span> Morning <?php echo $morningPercent; ?>%</div>
                        </div>
                    </div>
                </div>

                <!-- Low Stock Alerts -->
                <div class="bg-white border rounded-3 p-3">
                    <div class="d-flex justify-content-between align-items-center mb-3">
                        <div class="fw-bold">Low Stock Alerts</div>
                        <a href="product.php" class="btn btn-sm btn-outline-secondary rounded-pill" style="font-size:.75rem;">
                            <i class="bi bi-three-dots"></i>
                        </a>
                    </div>
                    <?php if (count($lowStockProducts) > 0): ?>
                        <?php foreach ($lowStockProducts as $product): ?>
                        <div class="d-flex align-items-center gap-3 mb-3">
                            <div class="bg-light rounded-2 d-flex align-items-center justify-content-center flex-shrink-0" style="width:40px;height:40px;">📦</div>
                            <div class="flex-fill">
                                <div class="fw-bold" style="font-size:.85rem;"><?php echo htmlspecialchars($product['name']); ?></div>
                                <div class="text-secondary" style="font-size:.72rem;"><?php echo $product['stock']; ?> left in stock</div>
                            </div>
                            <div class="text-end">
                                <div class="fw-bold text-danger" style="font-size:.85rem;"><?php echo $product['stock']; ?></div>
                                <div class="text-secondary" style="font-size:.65rem;">Stock</div>
                            </div>
                        </div>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <div class="text-center text-secondary py-4">
                            <i class="bi bi-check-circle"></i> All products are well stocked
                        </div>
                    <?php endif; ?>
                </div>

                <!-- Team Members -->
                <div class="bg-white border rounded-3 p-3 flex-fill">
                    <div class="d-flex justify-content-between align-items-center mb-3">
                        <div class="fw-bold">Team Members</div>
                        <button class="btn btn-primary btn-circle d-flex align-items-center justify-content-center" style="width:32px;height:32px;padding:0;">
                            <i class="bi bi-plus"></i>
                        </button>
                    </div>

                    <?php if (count($teamMembers) > 0): ?>
                        <?php 
                        $colors = ['#f3e8ff', '#e0f2fe', '#fef9c3', '#dcfce7', '#fee2e2', '#e0e7ff'];
                        $icons = ['👩', '👨', '🧑', '👩', '👨', '🧑'];
                        ?>
                        <?php foreach ($teamMembers as $idx => $member): ?>
                        <div class="d-flex align-items-center gap-3 mb-3">
                            <div class="rounded-circle d-flex align-items-center justify-content-center flex-shrink-0" style="width:40px;height:40px;background:<?php echo $colors[$idx % count($colors)]; ?>;font-size:1.2rem;">
                                <?php echo $icons[$idx % count($icons)]; ?>
                            </div>
                            <div class="flex-fill">
                                <div class="fw-bold" style="font-size:.85rem;"><?php echo htmlspecialchars($member['first_name'] . ' ' . $member['last_name']); ?></div>
                                <div class="text-secondary" style="font-size:.72rem;"><?php echo htmlspecialchars($member['email']); ?></div>
                            </div>
                            <button class="btn btn-sm p-0 text-secondary">
                                <i class="bi bi-three-dots-vertical"></i>
                            </button>
                        </div>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <div class="text-center text-secondary py-4">
                            <i class="bi bi-people"></i> No team members yet
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>

    <!-- Logout Confirmation Modal -->
    <div class="modal fade" id="logoutModal" tabindex="-1" aria-labelledby="logoutModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content">
                <div class="modal-header border-bottom bg-danger bg-opacity-10">
                    <h5 class="modal-title fw-bold text-danger" id="logoutModalLabel">
                        <i class="bi bi-exclamation-triangle me-2"></i>Confirm Logout
                    </h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <p class="mb-2">Are you sure you want to log out?</p>
                    <p class="text-secondary small mb-0">You will be redirected to the login page. Any unsaved changes will be lost.</p>
                </div>
                <div class="modal-footer border-top">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <a href="../db/action/logout.php" class="btn btn-danger">
                        <i class="bi bi-box-arrow-right me-2"></i>Logout
                    </a>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.amcharts.com/lib/5/index.js"></script>
    <script src="https://cdn.amcharts.com/lib/5/xy.js"></script>
    <script src="https://cdn.amcharts.com/lib/5/themes/Animated.js"></script>
    <script>
        am5.ready(function() {
            var root = am5.Root.new("dashboardChartDiv");
            root.setThemes([am5themes_Animated.new(root)]);

            var chart = root.container.children.push(am5xy.XYChart.new(root, {
                panX: "translateX",
                panY: "translateY",
                wheelX: "panX",
                wheelY: "zoomX",
                layout: root.verticalLayout
            }));

            var xAxis = chart.xAxes.push(am5xy.CategoryAxis.new(root, {
                categoryField: "day",
                renderer: am5xy.AxisRendererX.new(root, {
                    minGridDistance: 20
                })
            }));

            var yAxis = chart.yAxes.push(am5xy.ValueAxis.new(root, {
                renderer: am5xy.AxisRendererY.new(root, {})
            }));

            var series = chart.series.push(am5xy.ColumnSeries.new(root, {
                name: "Revenue",
                xAxis: xAxis,
                yAxis: yAxis,
                valueYField: "revenue",
                categoryXField: "day",
                tooltip: am5.Tooltip.new(root, {
                    labelText: "{label}: ₱{valueY.formatNumber('#,###.00')}"
                }),
                sequencedInterpolation: true
            }));

            series.columns.template.setAll({
                cornerRadiusTL: 8,
                cornerRadiusTR: 8,
                width: am5.percent(80)
            });

            var dashboardData = <?php echo json_encode($dashboardChartData, JSON_NUMERIC_CHECK); ?>;
            series.data.setAll(dashboardData);
            xAxis.data.setAll(dashboardData);

            var cursor = chart.set("cursor", am5xy.XYCursor.new(root, {
                behavior: "none"
            }));
            cursor.lineY.set("visible", false);
        });

        function confirmLogout() {
            const modal = new bootstrap.Modal(document.getElementById('logoutModal'));
            modal.show();
        }

        function filterDashboardSearch() {
            const searchInput = document.getElementById('dashboardSearch');
            const tableBody = document.querySelector('.orders-table tbody');
            const noResultsRow = document.getElementById('dashboardNoResultsRow');

            if (!searchInput || !tableBody) return;

            const query = searchInput.value.toLowerCase().trim();
            const rows = Array.from(tableBody.querySelectorAll('tr')).filter(row => row.id !== 'dashboardNoResultsRow');
            let visibleCount = 0;

            rows.forEach(row => {
                const orderId = row.cells[0]?.textContent.toLowerCase() || '';
                const productName = row.cells[1]?.textContent.toLowerCase() || '';
                const price = row.cells[2]?.textContent.toLowerCase() || '';
                const quantity = row.cells[3]?.textContent.toLowerCase() || '';
                const totalAmount = row.cells[4]?.textContent.toLowerCase() || '';

                const matches = query === '' || orderId.includes(query) || productName.includes(query) || price.includes(query) || quantity.includes(query) || totalAmount.includes(query);
                row.style.display = matches ? '' : 'none';
                if (matches) visibleCount++;
            });

            if (noResultsRow) {
                noResultsRow.classList.toggle('d-none', visibleCount !== 0 || query === '');
            }
        }

        document.addEventListener('DOMContentLoaded', function() {
            const dashboardSearch = document.getElementById('dashboardSearch');
            if (dashboardSearch) {
                dashboardSearch.addEventListener('input', filterDashboardSearch);
            }
        });
    </script>
</body>
</html>