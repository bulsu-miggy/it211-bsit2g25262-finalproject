<?php
/**
 * UniMerch API — Products
 * GET /api/products.php
 * Query params: category, search, sort, page, limit
 */
require_once __DIR__ . '/../includes/db.php';
require_once __DIR__ . '/../includes/helpers.php';

header('Content-Type: application/json; charset=utf-8');

if (getRequestMethod() !== 'GET') {
    jsonResponse(['success' => false, 'message' => 'Method not allowed'], 405);
}

$pdo = db();

$category = $_GET['category'] ?? 'all';
$search   = $_GET['search'] ?? '';
$sort     = $_GET['sort'] ?? 'newest';
$page     = max(1, (int)($_GET['page'] ?? 1));
$limit    = min(50, max(1, (int)($_GET['limit'] ?? PRODUCTS_PER_PAGE)));
$offset   = ($page - 1) * $limit;

// Build query
$where = ["p.status = 'active'"];
$params = [];

if ($category !== 'all' && is_numeric($category)) {
    $where[] = "p.category_id = ?";
    $params[] = (int) $category;
}

if (!empty($search)) {
    $where[] = "(p.name LIKE ? OR p.description LIKE ? OR c.code LIKE ?)";
    $searchTerm = '%' . $search . '%';
    $params[] = $searchTerm;
    $params[] = $searchTerm;
    $params[] = $searchTerm;
}

$whereSQL = implode(' AND ', $where);

// Sort
$orderBy = match ($sort) {
    'price_asc'  => 'p.price ASC',
    'price_desc' => 'p.price DESC',
    'name_asc'   => 'p.name ASC',
    'name_desc'  => 'p.name DESC',
    'popular'    => 'p.featured DESC, p.created_at DESC',
    default      => 'p.created_at DESC'
};

// Count total
$countStmt = $pdo->prepare("SELECT COUNT(*) FROM products p JOIN categories c ON p.category_id = c.id WHERE {$whereSQL}");
$countStmt->execute($params);
$total = (int) $countStmt->fetchColumn();

// Fetch products
$sql = "
    SELECT p.*, c.code AS category_code, c.name AS category_name, c.color AS category_color
    FROM products p
    JOIN categories c ON p.category_id = c.id
    WHERE {$whereSQL}
    ORDER BY {$orderBy}
    LIMIT {$limit} OFFSET {$offset}
";

$stmt = $pdo->prepare($sql);
$stmt->execute($params);
$products = $stmt->fetchAll();

// Parse JSON sizes
foreach ($products as &$product) {
    $product['sizes'] = $product['sizes'] ? json_decode($product['sizes'], true) : null;
    $product['image_url'] = BASE_URL . '/uploads/' . $product['image'];
}

jsonResponse([
    'success'  => true,
    'data'     => $products,
    'pagination' => [
        'page'       => $page,
        'limit'      => $limit,
        'total'      => $total,
        'totalPages' => ceil($total / $limit),
        'hasMore'    => ($page * $limit) < $total
    ]
]);
