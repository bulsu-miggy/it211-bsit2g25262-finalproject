<?php
/**
 * UniMerch API — Categories
 */
require_once __DIR__ . '/../includes/db.php';
require_once __DIR__ . '/../includes/helpers.php';

header('Content-Type: application/json; charset=utf-8');

if (getRequestMethod() !== 'GET') {
    jsonResponse(['success' => false, 'message' => 'Method not allowed'], 405);
}

$stmt = db()->query("
    SELECT c.*, 
           (SELECT COUNT(*) FROM products WHERE category_id = c.id AND status = 'active') AS product_count
    FROM categories c 
    ORDER BY c.name
");

jsonResponse([
    'success' => true,
    'data'    => $stmt->fetchAll()
]);
