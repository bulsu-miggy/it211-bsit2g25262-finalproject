<?php
require_once 'db/connection.php';

$stmt = $conn->prepare("SELECT id, name, category_id FROM products ORDER BY id");
$stmt->execute();
$products = $stmt->fetchAll(PDO::FETCH_ASSOC);

echo "All Preloaded Products:\n";
echo "========================\n\n";
foreach ($products as $p) {
    echo $p['id'] . ": {$p['name']} (Category {$p['category_id']})\n";
}
?>