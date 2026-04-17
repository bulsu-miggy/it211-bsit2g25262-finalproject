<?php
require_once 'db/connection.php';

$stmt = $conn->prepare('SELECT id, name FROM categories ORDER BY name');
$stmt->execute();
$categories = $stmt->fetchAll(PDO::FETCH_ASSOC);

echo "Current categories in database:\n";
foreach ($categories as $cat) {
    echo $cat['id'] . ': ' . $cat['name'] . "\n";
}

echo "\nRecent products:\n";
$stmt = $conn->prepare('SELECT p.id, p.name, p.category_id, c.name as category_name FROM products p LEFT JOIN categories c ON p.category_id = c.id ORDER BY p.created_at DESC LIMIT 5');
$stmt->execute();
$products = $stmt->fetchAll(PDO::FETCH_ASSOC);

foreach ($products as $prod) {
    echo $prod['id'] . ': ' . $prod['name'] . ' -> Category: ' . ($prod['category_name'] ?? 'NULL') . ' (ID: ' . ($prod['category_id'] ?? 'NULL') . ")\n";
}
?>