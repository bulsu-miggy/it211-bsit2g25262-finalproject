<?php
require_once __DIR__ . '/../db/conn.php';
require_once __DIR__ . '/ProductManager.php';

// Test database connection
$db = new Database();
$connection = $db->getConnection();
echo "Database connected successfully!<br>";

// Test ProductManager
$pm = new ProductManager();
$products = $pm->getAllProducts();
echo "Found " . count($products) . " products in database:<br>";
foreach($products as $product) {
    echo "- ID: " . $product['id'] . ", Name: " . $product['name'] . ", Brand: " . ($product['brand'] ?? 'N/A') . ", Price: $" . $product['price'] . "<br>";
}
?>