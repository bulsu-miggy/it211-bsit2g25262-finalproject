<?php
// Database connection
require_once '../db/action/dbconfig.php';

// Start session to get admin info
session_start();

// Get current admin info
$adminUsername = isset($_SESSION['username']) ? $_SESSION['username'] : 'Admin';
$adminInitial = strtoupper(substr($adminUsername, 0, 1));

// Get view type from URL parameter, default to monthly
$viewType = isset($_GET['view']) ? $_GET['view'] : 'monthly';
$validViews = ['daily', 'weekly', 'monthly'];
if (!in_array($viewType, $validViews)) {
    $viewType = 'monthly';
}

// Get selected month and year from URL parameters
$selectedMonth = isset($_GET['month']) ? (int) $_GET['month'] : (int) date('n');
if ($selectedMonth < 1 || $selectedMonth > 12) {
    $selectedMonth = (int) date('n');
}

$selectedYear = isset($_GET['year']) ? (int) $_GET['year'] : (int) date('Y');
if ($selectedYear < 2000 || $selectedYear > ((int) date('Y') + 5)) {
    $selectedYear = (int) date('Y');
}

// Calculate days in selected month
$monthStart = new DateTime("$selectedYear-$selectedMonth-01");
$daysInMonth = (int) $monthStart->format('t');

// === PENDING ORDERS ===
$stmt = $conn->prepare("SELECT COUNT(*) as total FROM orders WHERE status = 'Pending'");
$stmt->execute();
$pendingOrders = $stmt->fetch(PDO::FETCH_ASSOC)['total'] ?? 0;

// === DUE PAYMENT ===
$stmt = $conn->prepare("SELECT COUNT(*) as total FROM orders WHERE status IN ('Pending', 'Processing')");
$stmt->execute();
$duePayment = $stmt->fetch(PDO::FETCH_ASSOC)['total'] ?? 0;

// === REVENUE GROWTH (Current period vs Previous period) ===
if ($viewType === 'daily') {
    $stmt = $conn->prepare("
        SELECT COALESCE(SUM(total_amount), 0) as current_period
        FROM orders
        WHERE DATE(created_at) = DATE(NOW())
        AND status != 'Cancelled'
    ");
    $stmt->execute();
    $currentPeriodRevenue = $stmt->fetch(PDO::FETCH_ASSOC)['current_period'] ?? 0;

    $stmt = $conn->prepare("
        SELECT COALESCE(SUM(total_amount), 0) as prev_period
        FROM orders
        WHERE DATE(created_at) = DATE(DATE_SUB(NOW(), INTERVAL 1 DAY))
        AND status != 'Cancelled'
    ");
    $stmt->execute();
    $prevPeriodRevenue = $stmt->fetch(PDO::FETCH_ASSOC)['prev_period'] ?? 0;
} elseif ($viewType === 'weekly') {
    $stmt = $conn->prepare("
        SELECT COALESCE(SUM(total_amount), 0) as current_period
        FROM orders
        WHERE created_at >= DATE_SUB(NOW(), INTERVAL 7 DAY)
        AND status != 'Cancelled'
    ");
    $stmt->execute();
    $currentPeriodRevenue = $stmt->fetch(PDO::FETCH_ASSOC)['current_period'] ?? 0;

    $stmt = $conn->prepare("
        SELECT COALESCE(SUM(total_amount), 0) as prev_period
        FROM orders
        WHERE created_at >= DATE_SUB(NOW(), INTERVAL 14 DAY) 
        AND created_at < DATE_SUB(NOW(), INTERVAL 7 DAY)
        AND status != 'Cancelled'
    ");
    $stmt->execute();
    $prevPeriodRevenue = $stmt->fetch(PDO::FETCH_ASSOC)['prev_period'] ?? 0;
} else { // monthly
    $stmt = $conn->prepare("
        SELECT COALESCE(SUM(total_amount), 0) as current_period
        FROM orders
        WHERE MONTH(created_at) = MONTH(NOW())
        AND YEAR(created_at) = YEAR(NOW())
        AND status != 'Cancelled'
    ");
    $stmt->execute();
    $currentPeriodRevenue = $stmt->fetch(PDO::FETCH_ASSOC)['current_period'] ?? 0;

    $stmt = $conn->prepare("
        SELECT COALESCE(SUM(total_amount), 0) as prev_period
        FROM orders
        WHERE MONTH(created_at) = MONTH(DATE_SUB(NOW(), INTERVAL 1 MONTH))
        AND YEAR(created_at) = YEAR(DATE_SUB(NOW(), INTERVAL 1 MONTH))
        AND status != 'Cancelled'
    ");
    $stmt->execute();
    $prevPeriodRevenue = $stmt->fetch(PDO::FETCH_ASSOC)['prev_period'] ?? 0;
}

$revenueGrowth = $prevPeriodRevenue > 0 ? (($currentPeriodRevenue - $prevPeriodRevenue) / $prevPeriodRevenue) * 100 : 0;
$revenueGrowthDirection = $revenueGrowth >= 0 ? '+' : '';

// === CUSTOMER RETENTION ===
$stmt = $conn->prepare("
    SELECT COUNT(DISTINCT user_id) as returning_customers
    FROM orders
    WHERE user_id IN (
        SELECT user_id FROM orders 
        GROUP BY user_id HAVING COUNT(*) > 1
    )
");
$stmt->execute();
$returningCustomers = $stmt->fetch(PDO::FETCH_ASSOC)['returning_customers'] ?? 0;

// Total unique customers
$stmt = $conn->prepare("SELECT COUNT(DISTINCT user_id) as total_customers FROM orders");
$stmt->execute();
$totalCustomers = $stmt->fetch(PDO::FETCH_ASSOC)['total_customers'] ?? 1;

$customerRetention = $totalCustomers > 0 ? round(($returningCustomers / $totalCustomers) * 100) : 0;

// === REVENUE BY PERIOD ===
// Total revenue for the selected month/year
$stmt = $conn->prepare("
    SELECT COALESCE(SUM(total_amount), 0) as selected_month_total
    FROM orders
    WHERE MONTH(created_at) = :selectedMonth
    AND YEAR(created_at) = :selectedYear
    AND status != 'Cancelled'
");
$stmt->execute([
    ':selectedMonth' => $selectedMonth,
    ':selectedYear' => $selectedYear,
]);
$selectedMonthTotal = (float) ($stmt->fetch(PDO::FETCH_ASSOC)['selected_month_total'] ?? 0);

// Daily average revenue for selected month
$dailyRevenue = $daysInMonth > 0 ? round($selectedMonthTotal / $daysInMonth, 2) : 0;

// Weekly average revenue for selected month
$weeksInMonth = ceil($daysInMonth / 7);
$weeklyRevenue = $weeksInMonth > 0 ? round($selectedMonthTotal / $weeksInMonth, 2) : 0;

// Monthly total revenue for selected month
$monthlyRevenue = $selectedMonthTotal;

// === REVENUE DATA (for bar chart - varies by view type) ===
$currentMonthName = DateTime::createFromFormat('!m', $selectedMonth)->format('F');
$currentYear = $selectedYear;
$monthStart = new DateTime("$currentYear-$selectedMonth-01");
$chartData = [];
$chartTitle = '';
$revenueOverviewSubtitle = '';

if ($viewType === 'daily') {
    for ($day = 1; $day <= $daysInMonth; $day++) {
        $date = sprintf('%04d-%02d-%02d', $currentYear, $selectedMonth, $day);
        $stmt = $conn->prepare("SELECT COALESCE(SUM(total_amount), 0) as revenue
            FROM orders
            WHERE DATE(created_at) = :date
            AND status != 'Cancelled'");
        $stmt->execute([':date' => $date]);
        $result = (float) ($stmt->fetch(PDO::FETCH_ASSOC)['revenue'] ?? 0);
        $chartData[] = [
            'label' => sprintf('%02d', $day),
            'revenue' => round($result, 2),
        ];
    }
    $chartTitle = "Daily Revenue for $currentMonthName $currentYear";
    $revenueOverviewSubtitle = "Day 01–" . str_pad($daysInMonth, 2, '0', STR_PAD_LEFT) . " of $currentMonthName $currentYear";
} elseif ($viewType === 'weekly') {
    $monthEnd = new DateTime("last day of $currentYear-$selectedMonth");
    $weekStart = clone $monthStart;
    $weekNumber = 1;

    while ($weekStart <= $monthEnd) {
        $weekEnd = clone $weekStart;
        $weekEnd->modify('+6 days');
        if ($weekEnd > $monthEnd) {
            $weekEnd = clone $monthEnd;
        }

        $stmt = $conn->prepare("
            SELECT COALESCE(SUM(total_amount), 0) as revenue
            FROM orders
            WHERE DATE(created_at) >= :weekStart
            AND DATE(created_at) <= :weekEnd
            AND status != 'Cancelled'
        ");
        $stmt->execute([
            ':weekStart' => $weekStart->format('Y-m-d'),
            ':weekEnd' => $weekEnd->format('Y-m-d'),
        ]);
        $result = (float) ($stmt->fetch(PDO::FETCH_ASSOC)['revenue'] ?? 0);
        $chartData[] = [
            'label' => 'Week ' . str_pad($weekNumber, 2, '0', STR_PAD_LEFT),
            'revenue' => round($result, 2),
        ];

        $weekNumber++;
        $weekStart->modify('+7 days');
    }

    $chartTitle = "Weekly Revenue for $currentMonthName $currentYear";
    $revenueOverviewSubtitle = 'Week 01 – Week ' . str_pad($weekNumber - 1, 2, '0', STR_PAD_LEFT) . " of $currentMonthName $currentYear";
} else { // monthly
    $monthNames = ['January', 'February', 'March', 'April', 'May', 'June', 'July', 'August', 'September', 'October', 'November', 'December'];

    for ($month = 1; $month <= 12; $month++) {
        $stmt = $conn->prepare("
            SELECT COALESCE(SUM(total_amount), 0) as revenue
            FROM orders
            WHERE MONTH(created_at) = :month
            AND YEAR(created_at) = :year
            AND status != 'Cancelled'
        ");
        $stmt->execute([
            ':month' => $month,
            ':year' => $currentYear,
        ]);
        $result = (float) ($stmt->fetch(PDO::FETCH_ASSOC)['revenue'] ?? 0);
        $chartData[] = [
            'label' => $monthNames[$month - 1],
            'revenue' => round($result, 2),
        ];
    }

    $chartTitle = "Monthly Revenue for $currentYear";
    $revenueOverviewSubtitle = "January – December $currentYear";
}

// === TOP PRODUCTS (by order quantity) ===
$stmt = $conn->prepare("
    SELECT p.id, p.name, SUM(oi.quantity) as total_quantity
    FROM order_items oi
    JOIN products p ON oi.product_id = p.id
    GROUP BY p.id, p.name
    ORDER BY total_quantity DESC
    LIMIT 5
");
$stmt->execute();
$topProducts = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Find max quantity for progress bar calculation
$maxQuantity = !empty($topProducts) ? $topProducts[0]['total_quantity'] : 1;

// === TOP CATEGORIES (by products sold in category) ===
$stmt = $conn->prepare("
    SELECT c.id, c.name, SUM(oi.quantity) as total_quantity
    FROM order_items oi
    JOIN products p ON oi.product_id = p.id
    JOIN categories c ON p.category_id = c.id
    GROUP BY c.id, c.name
    ORDER BY total_quantity DESC
    LIMIT 5
");
$stmt->execute();
$topCategories = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Find max quantity for progress bar calculation
$maxCategoryQuantity = !empty($topCategories) ? $topCategories[0]['total_quantity'] : 1;

?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Analytics</title>
    <link rel="icon" type="image/png" href="../assets2/logo.png">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.1/font/bootstrap-icons.css">
    <link rel="stylesheet" href="admin.css">
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/js/bootstrap.bundle.min.js"></script>
</head>
<body class="bg-light min-vh-100 text-dark">
    <!-- navigation sidebar -->
    <nav id="sidebar" class="bg-white border-end d-flex flex-column position-fixed top-0 start-0 min-vh-100" style="z-index:1000;">
        <div class="border-bottom px-3 py-3 d-flex align-items-center gap-2 fw-bold fs-5">
            <span style="font-size:1.4rem;"></span>Laces
        </div>
        <ul class="nav flex-column mt-3">
            <li class="nav-item">
                <a href="dashboard.php" class="nav-link d-flex align-items-center gap-2 fw-semibold rounded text-secondary mx-2 my-1 px-3 py-2">
                    <i class="bi bi-grid-1x2"></i> Dashboard
                </a>
            </li>
            <li class="nav-item">
                <a href="orders.php" class="nav-link d-flex align-items-center gap-2 fw-semibold text-secondary rounded mx-2 my-1 px-3 py-2">
                    <i class="bi bi-cart-fill"></i> Orders
                </a>
            </li>
            <li class="nav-item">
                <a href="product.php" class="nav-link d-flex align-items-center gap-2 fw-semibold rounded text-secondary mx-2 my-1 px-3 py-2">
                    <i class="bi bi-box-seam-fill"></i> Products
                </a>
            </li>
            <li class="nav-item">
                <a href="categories.php" class="nav-link d-flex align-items-center gap-2 fw-semibold rounded text-secondary mx-2 my-1 px-3 py-2">
                    <i class="bi bi-folder"></i> Categories
                </a>
            </li>
            <li class="nav-item">
                <a href="customer.php" class="nav-link d-flex align-items-center gap-2 fw-semibold rounded text-secondary mx-2 my-1 px-3 py-2">
                    <i class="bi bi-people"></i> Customers
                </a>
            </li>
            <li class="nav-item">
                <a href="analytics.php" class="nav-link active d-flex align-items-center gap-2 fw-semibold rounded mx-2 my-1 px-3 py-2">
                    <i class="bi bi-bar-chart"></i> Analytics
                </a>
            </li>
        </ul>
        <div class="mt-auto border-top px-3 py-3">
            <div class="d-flex align-items-center gap-2">
                <div class="rounded-circle bg-primary d-flex align-items-center justify-content-center text-white fw-bold flex-shrink-0" style="width:36px;height:36px;font-size:.8rem;">A</div>
                <div>
                    <div class="fw-bold" style="font-size:.82rem;line-height:1.2;">Abudul</div>
                    <div class="text-secondary" style="font-size:.72rem;">Admin</div>
                </div>
            </div>
        </div>
    </nav>

    <!-- top bar -->
    <div id="topbar" class="bg-white border-bottom d-flex align-items-center px-4 sticky-top" style="height:60px;z-index:999;">
        <h5 class="mb-0 fw-bold fs-5">Dashboard</h5>
        <div class="position-relative ms-3" style="max-width:260px;flex:1;">
            <i class="bi bi-search text-secondary search-icon"></i>
            <input type="text" class="form-control bg-light border search-input" placeholder="Search…"/>
        </div>
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

    <!-- main content -->
    <div id="main" class="p-4">
        <!-- header -->
        <div class="d-flex align-items-start justify-content-between mb-4">
            <div>
                <h4 class="fw-bold mb-1">Analytics</h4>
                <p class="text-secondary mb-0 products-subtitle">Track your business performance and insights</p>
            </div>
        </div>

        <!-- Performance Cards Row -->
        <div class="row g-3 mb-4">
            <div class="col-md-3">
                <div class="bg-white border rounded-3 p-3" style="min-height: 90px;">
                    <div class="d-flex align-items-center justify-content-between mb-2">
                        <i class="bi bi-graph-up-arrow fs-3 text-primary"></i>
                        <span class="badge <?php echo $revenueGrowth >= 0 ? 'bg-success' : 'bg-danger'; ?> bg-opacity-10 <?php echo $revenueGrowth >= 0 ? 'text-success' : 'text-danger'; ?> px-2 py-1 rounded-2 fw-semibold"><?php echo $revenueGrowthDirection; ?><?php echo number_format(abs($revenueGrowth), 1); ?>%</span>
                    </div>
                    <div class="text-secondary small fw-semibold mb-1">REVENUE GROWTH</div>
                    <h3 class="fw-light mb-0" style="font-size: 1.6rem;"><?php echo number_format($revenueGrowth, 1); ?>%</h3>
                </div>
            </div>
            <div class="col-md-3">
                <div class="bg-white border rounded-3 p-3" style="min-height: 90px;">
                    <div class="d-flex align-items-center justify-content-between mb-2">
                        <i class="bi bi-hourglass-split fs-3 text-warning"></i>
                        <span class="badge bg-warning bg-opacity-10 text-warning px-2 py-1 rounded-2 fw-semibold">Pending</span>
                    </div>
                    <div class="text-secondary small fw-semibold mb-1">PENDING ORDERS</div>
                    <h3 class="fw-light mb-0" style="font-size: 1.6rem;"><?php echo number_format($pendingOrders); ?></h3>
                </div>
            </div>
            <div class="col-md-3">
                <div class="bg-white border rounded-3 p-3" style="min-height: 90px;">
                    <div class="d-flex align-items-center justify-content-between mb-2">
                        <i class="bi bi-cash-coin fs-3 text-danger"></i>
                        <span class="badge bg-danger bg-opacity-10 text-danger px-2 py-1 rounded-2 fw-semibold">Action</span>
                    </div>
                    <div class="text-secondary small fw-semibold mb-1">DUE PAYMENT</div>
                    <h3 class="fw-light mb-0" style="font-size: 1.6rem;"><?php echo number_format($duePayment); ?></h3>
                </div>
            </div>
            <div class="col-md-3">
                <div class="bg-white border rounded-3 p-3" style="min-height: 90px;">
                    <div class="d-flex align-items-center justify-content-between mb-2">
                        <i class="bi bi-people-fill fs-3 text-primary"></i>
                        <span class="badge bg-success bg-opacity-10 text-success px-2 py-1 rounded-2 fw-semibold">+<?php echo $customerRetention; ?>%</span>
                    </div>
                    <div class="text-secondary small fw-semibold mb-1">CUSTOMER RETENTION</div>
                    <h3 class="fw-light mb-0" style="font-size: 1.6rem;"><?php echo $customerRetention; ?>%</h3>
                </div>
            </div>
        </div>

        <!-- Revenue Overview Section -->
        <div class="row g-3 mb-4">
            <div class="col-12">
                <div class="bg-white border rounded-3 p-3">
                    <div class="d-flex flex-column flex-md-row justify-content-between align-items-start align-items-md-center gap-3 mb-3">
                        <div>
                            <h5 class="fw-bold mb-1">Revenue Overview</h5>
                            <p class="text-secondary small mb-1"><?php echo htmlspecialchars($chartTitle); ?></p>
                            <p class="text-secondary small mb-0"><?php echo htmlspecialchars($revenueOverviewSubtitle); ?></p>
                        </div>
                        <div class="d-flex flex-wrap gap-2 align-items-center">
                            <div class="btn-group btn-group-sm" role="group" aria-label="View type buttons">
                                <a href="?view=daily&month=<?php echo $selectedMonth; ?>&year=<?php echo $selectedYear; ?>" class="btn <?php echo $viewType === 'daily' ? 'btn-dark' : 'btn-outline-secondary'; ?>" style="font-size:.82rem;">Daily</a>
                                <a href="?view=weekly&month=<?php echo $selectedMonth; ?>&year=<?php echo $selectedYear; ?>" class="btn <?php echo $viewType === 'weekly' ? 'btn-dark' : 'btn-outline-secondary'; ?>" style="font-size:.82rem;">Weekly</a>
                                <a href="?view=monthly&month=<?php echo $selectedMonth; ?>&year=<?php echo $selectedYear; ?>" class="btn <?php echo $viewType === 'monthly' ? 'btn-dark' : 'btn-outline-secondary'; ?>" style="font-size:.82rem;">Monthly</a>
                            </div>
                            <div class="dropdown">
                                <button class="btn btn-outline-secondary btn-sm dropdown-toggle" type="button" id="monthDropdownButton" data-bs-toggle="dropdown" aria-expanded="false" style="font-size:.82rem; min-width: 140px;">
                                    Month: <?php echo htmlspecialchars($currentMonthName); ?>
                                </button>
                                <ul class="dropdown-menu dropdown-menu-end" aria-labelledby="monthDropdownButton" style="max-height: 250px; overflow-y: auto;">
                                    <?php for ($m = 1; $m <= 12; $m++): ?>
                                        <?php $monthName = DateTime::createFromFormat('!m', $m)->format('F'); ?>
                                        <li>
                                            <a class="dropdown-item <?php echo $selectedMonth === $m ? 'active' : ''; ?>" href="?view=<?php echo urlencode($viewType); ?>&month=<?php echo $m; ?>&year=<?php echo $selectedYear; ?>">
                                                <?php echo $monthName; ?>
                                            </a>
                                        </li>
                                    <?php endfor; ?>
                                </ul>
                            </div>
                        </div>
                    </div>
                    <!-- Revenue Chart -->
                    <div class="bg-light rounded-3 p-3" style="background-color: #f8f9fa;">
                        <div id="analyticsChartDiv" style="width:100%; height:360px;"></div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Two parallel boxes -->
        <div class="row g-3">
            <!-- Top Products -->
            <div class="col-md-6">
                <div class="bg-white border rounded-3 p-3" style="min-height: 420px;">
                    <h5 class="fw-bold mb-3">Top Products</h5>
                    <?php if (!empty($topProducts)): ?>
                        <?php foreach ($topProducts as $product): 
                            $percentage = ($product['total_quantity'] / $maxQuantity) * 100;
                        ?>
                        <div class="mb-3">
                            <div class="d-flex justify-content-between mb-2">
                                <span><?php echo htmlspecialchars($product['name']); ?></span>
                                <span class="fw-semibold"><?php echo number_format($product['total_quantity']); ?> sold</span>
                            </div>
                            <div class="progress" style="height: 6px;">
                                <div class="progress-bar bg-primary" style="width: <?php echo $percentage; ?>%"></div>
                            </div>
                        </div>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <div class="alert alert-info">No products sold yet</div>
                    <?php endif; ?>
                </div>
            </div>

            <!-- Top Categories -->
            <div class="col-md-6">
                <div class="bg-white border rounded-3 p-3" style="min-height: 420px;">
                    <h5 class="fw-bold mb-3">Top Categories</h5>
                    <?php if (!empty($topCategories)): ?>
                        <?php foreach ($topCategories as $category):
                            $percentage = $maxCategoryQuantity > 0 ? ($category['total_quantity'] / $maxCategoryQuantity) * 100 : 0;
                        ?>
                        <div class="mb-3">
                            <div class="d-flex justify-content-between mb-2">
                                <span><?php echo htmlspecialchars($category['name']); ?></span>
                                <span class="fw-semibold"><?php echo number_format($category['total_quantity']); ?> products</span>
                            </div>
                            <div class="progress" style="height: 6px;">
                                <div class="progress-bar bg-success" style="width: <?php echo $percentage; ?>%"></div>
                            </div>
                        </div>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <div class="alert alert-info">No categories with sales yet</div>
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
    <script src="admin.js"></script>
    <script>
        am5.ready(function() {
            var root = am5.Root.new("analyticsChartDiv");
            root.setThemes([am5themes_Animated.new(root)]);

            var chart = root.container.children.push(am5xy.XYChart.new(root, {
                panX: "translateX",
                panY: "translateY",
                wheelX: "panX",
                wheelY: "zoomX",
                layout: root.verticalLayout
            }));

            var xAxis = chart.xAxes.push(am5xy.CategoryAxis.new(root, {
                categoryField: "label",
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
                categoryXField: "label",
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

            var analyticsData = <?php echo json_encode($chartData, JSON_NUMERIC_CHECK); ?>;
            series.data.setAll(analyticsData);
            xAxis.data.setAll(analyticsData);

            var cursor = chart.set("cursor", am5xy.XYCursor.new(root, {
                behavior: "none"
            }));
            cursor.lineY.set("visible", false);
        });

        function confirmLogout() {
            const modal = new bootstrap.Modal(document.getElementById('logoutModal'));
            modal.show();
        }
    </script>
</body>
</html>