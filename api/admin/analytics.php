<?php
/**
 * UniMerch Admin API — Analytics Data
 */
require_once __DIR__ . '/../../includes/db.php';
require_once __DIR__ . '/../../includes/helpers.php';
require_once __DIR__ . '/../../includes/auth.php';

header('Content-Type: application/json; charset=utf-8');
requireMerchantAuthAPI();

$pdo = db();
$type = $_GET['type'] ?? 'dashboard';

switch ($type) {
    case 'dashboard': getDashboardData($pdo); break;
    case 'revenue':   getRevenueData($pdo); break;
    case 'top_products': getTopProducts($pdo); break;
    case 'orders_by_status': getOrdersByStatus($pdo); break;
    case 'sales_by_category': getSalesByCategory($pdo); break;
    default: jsonResponse(['success' => false, 'message' => 'Invalid type'], 400);
}

function getDashboardData(PDO $pdo): void {
    // Total Revenue
    $totalRevenue = (float) $pdo->query("SELECT COALESCE(SUM(total_amount), 0) FROM orders WHERE status != 'cancelled'")->fetchColumn();

    // Today's Orders
    $todayOrders = (int) $pdo->query("SELECT COUNT(*) FROM orders WHERE DATE(created_at) = CURDATE()")->fetchColumn();

    // Active Products
    $activeProducts = (int) $pdo->query("SELECT COUNT(*) FROM products WHERE status = 'active'")->fetchColumn();

    // Low Stock Alerts (stock < 20)
    $lowStock = (int) $pdo->query("SELECT COUNT(*) FROM products WHERE stock < 20 AND status = 'active'")->fetchColumn();

    // Recent Orders (last 10)
    $recentOrders = $pdo->query("
        SELECT o.*, 
               (SELECT COUNT(*) FROM order_items WHERE order_id = o.id) AS item_count
        FROM orders o 
        ORDER BY o.created_at DESC 
        LIMIT 10
    ")->fetchAll();

    // Pending Orders count
    $pendingCount = (int) $pdo->query("SELECT COUNT(*) FROM orders WHERE status = 'pending'")->fetchColumn();

    jsonResponse([
        'success' => true,
        'data' => [
            'total_revenue'   => $totalRevenue,
            'today_orders'    => $todayOrders,
            'active_products' => $activeProducts,
            'low_stock'       => $lowStock,
            'pending_orders'  => $pendingCount,
            'recent_orders'   => $recentOrders
        ]
    ]);
}

function getRevenueData(PDO $pdo): void {
    $days = (int) ($_GET['days'] ?? 30);
    $days = min(365, max(7, $days));

    $stmt = $pdo->prepare("
        SELECT DATE(created_at) AS date, 
               SUM(total_amount) AS revenue,
               COUNT(*) AS orders
        FROM orders 
        WHERE created_at >= DATE_SUB(CURDATE(), INTERVAL ? DAY)
          AND status != 'cancelled'
        GROUP BY DATE(created_at)
        ORDER BY date ASC
    ");
    $stmt->execute([$days]);

    jsonResponse(['success' => true, 'data' => $stmt->fetchAll()]);
}

function getTopProducts(PDO $pdo): void {
    $limit = min(20, max(5, (int) ($_GET['limit'] ?? 10)));

    $stmt = $pdo->prepare("
        SELECT p.name, p.image, c.code AS category_code,
               SUM(oi.quantity) AS total_sold,
               SUM(oi.quantity * oi.price) AS total_revenue
        FROM order_items oi
        JOIN products p ON oi.product_id = p.id
        JOIN categories c ON p.category_id = c.id
        JOIN orders o ON oi.order_id = o.id
        WHERE o.status != 'cancelled'
        GROUP BY p.id
        ORDER BY total_sold DESC
        LIMIT ?
    ");
    $stmt->execute([$limit]);

    jsonResponse(['success' => true, 'data' => $stmt->fetchAll()]);
}

function getOrdersByStatus(PDO $pdo): void {
    $data = $pdo->query("
        SELECT status, COUNT(*) AS count
        FROM orders
        GROUP BY status
    ")->fetchAll();

    jsonResponse(['success' => true, 'data' => $data]);
}

function getSalesByCategory(PDO $pdo): void {
    $data = $pdo->query("
        SELECT c.code AS category, c.color,
               COALESCE(SUM(oi.quantity * oi.price), 0) AS revenue,
               COALESCE(SUM(oi.quantity), 0) AS items_sold
        FROM categories c
        LEFT JOIN products p ON p.category_id = c.id
        LEFT JOIN order_items oi ON oi.product_id = p.id
        LEFT JOIN orders o ON oi.order_id = o.id AND o.status != 'cancelled'
        GROUP BY c.id
        ORDER BY revenue DESC
    ")->fetchAll();

    jsonResponse(['success' => true, 'data' => $data]);
}
