<?php
require_once 'db/connection.php';

echo "Clearing products...\n";
$conn->exec("DELETE FROM products WHERE id < 1000");
$conn->exec("ALTER TABLE products AUTO_INCREMENT = 1");

echo "Seeding 7 hardcoded products...\n";
$conn->exec("INSERT INTO products (id, name, category_id, description, price, stock_quantity, image_path, status) VALUES
    (1, 'Grape Juice Flask 16oz', 1, 'Your perfect daily companion. Vacuum-insulated and leak-proof.', 850.00, 50, 'images/16oz/grapejuice.png', 'active'),
    (2, 'Pistachio Flask 16oz', 1, 'Compact size for quick hydration on the go.', 850.00, 50, 'images/16oz/Pistachio.png', 'active'),
    (3, 'Royal Blue Flask 25oz', 2, 'The middle ground of hydration and portability.', 890.00, 45, 'images/25oz/RoyalBlue.png', 'active'),
    (4, 'Lavender Flask 25oz', 2, 'A refreshing look for your everyday hydration.', 890.00, 45, 'images/25oz/Lavender.png', 'active'),
    (5, 'Nori Flask 32oz', 3, 'Built for the rugged outdoors. Sweat-proof design.', 950.00, 40, 'images/32oz/noriflask.png', 'active'),
    (6, 'Slate Gray Flask 32oz', 3, 'Stays ice-cold even under the hot sun.', 950.00, 40, 'images/32oz/SlateGray.png', 'active'),
    (7, 'Magenta Flask 32oz', 3, 'Tough, reliable, and keeps coffee hot for hours.', 950.00, 40, 'images/32oz/Magenta.png', 'active')");

$stmt = $conn->prepare("SELECT COUNT(*) as total FROM products");
$stmt->execute();
$result = $stmt->fetch(PDO::FETCH_ASSOC);
echo "\nTotal products: " . $result['total'] . "\n\n";

$stmt = $conn->prepare("SELECT id, name, category_id FROM products ORDER BY category_id, id");
$stmt->execute();
$products = $stmt->fetchAll(PDO::FETCH_ASSOC);

echo "Hardcoded Products:\n";
echo "===================\n";
$sizeMap = [1 => '16oz', 2 => '25oz', 3 => '32oz'];
$currentCat = 0;

foreach ($products as $p) {
    if ($currentCat != $p['category_id']) {
        $currentCat = $p['category_id'];
        echo "\n{$sizeMap[$currentCat]}:\n";
    }
    echo "  {$p['id']}. {$p['name']}\n";
}
?>
