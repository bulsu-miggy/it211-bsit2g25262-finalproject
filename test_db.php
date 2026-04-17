<?php
require_once 'db/connection.php';

echo "Database setup test:\n\n";

// Test categories
$stmt = $conn->prepare("SELECT COUNT(*) as count FROM categories");
$stmt->execute();
$categories_count = $stmt->fetch(PDO::FETCH_ASSOC)['count'];
echo "Categories: $categories_count\n";

// Test products
$stmt = $conn->prepare("SELECT COUNT(*) as count FROM products");
$stmt->execute();
$products_count = $stmt->fetch(PDO::FETCH_ASSOC)['count'];
echo "Products: $products_count\n";

// Test users
$stmt = $conn->prepare("SELECT COUNT(*) as count FROM users");
$stmt->execute();
$users_count = $stmt->fetch(PDO::FETCH_ASSOC)['count'];
echo "Users: $users_count\n";

// Test admin user
$stmt = $conn->prepare("SELECT COUNT(*) as count FROM users WHERE is_admin = 1");
$stmt->execute();
$admin_count = $stmt->fetch(PDO::FETCH_ASSOC)['count'];
echo "Admin users: $admin_count\n";

echo "\nDatabase setup complete!";
?>