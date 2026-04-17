<?php
include 'connection.php';

echo "Starting migration to single products table...\n";

//Add new columns if not exist
try {
    $conn->exec("ALTER TABLE products ADD COLUMN IF NOT EXISTS `gender` VARCHAR(10) NOT NULL DEFAULT 'Women'");
    $conn->exec("ALTER TABLE products ADD COLUMN IF NOT EXISTS `sub_category` VARCHAR(50) DEFAULT NULL");
    echo "✓ Added gender and sub_category columns to products table.\n";
} catch (PDOException $e) {
    echo "Note: Columns may already exist: " . $e->getMessage() . "\n";
}

//Migrate men_products
try {
    $count_men = $conn->exec("
        INSERT INTO products (title, description, price, stock, category, image_url, gender, sub_category)
        SELECT title, excerpt, price, 0, category, imgurl, 'Men', category
        FROM men_products
        ON DUPLICATE KEY UPDATE stock = VALUES(stock)
    ");
    echo "✓ Migrated " . $conn->query("SELECT ROW_COUNT()")->fetchColumn() . " men products.\n";
} catch (PDOException $e) {
    echo "Men migration error: " . $e->getMessage() . "\n";
}

//Migrate women_products
try {
    $count_women = $conn->exec("
        INSERT INTO products (title, description, price, stock, category, image_url, gender, sub_category)
        SELECT title, excerpt, price, 0, category, imgurl, 'Women', category
        FROM women_products
        ON DUPLICATE KEY UPDATE stock = VALUES(stock)
    ");
    echo "✓ Migrated " . $conn->query("SELECT ROW_COUNT()")->fetchColumn() . " women products.\n";
} catch (PDOException $e) {
    echo "Women migration error: " . $e->getMessage() . "\n";
}

//Verify total products
$total = $conn->query("SELECT COUNT(*) FROM products")->fetchColumn();
echo "✓ Total products in unified table: $total\n";

echo "Migration complete! Check dashboard/products.php.\n";
?>

