<?php
require_once 'db/connection.php';

echo "Checking products in database...\n\n";

$stmt = $conn->prepare("SELECT COUNT(*) as total FROM products");
$stmt->execute();
$result = $stmt->fetch(PDO::FETCH_ASSOC);
echo "Total products: " . $result['total'] . "\n\n";

if ($result['total'] > 0) {
    echo "Sample products:\n";
    $stmt = $conn->prepare("SELECT id, name, category_id, price FROM products LIMIT 5");
    $stmt->execute();
    $products = $stmt->fetchAll(PDO::FETCH_ASSOC);
    foreach ($products as $product) {
        echo "- ID {$product['id']}: {$product['name']} (Category {$product['category_id']}) - ₱{$product['price']}\n";
    }
}
?>