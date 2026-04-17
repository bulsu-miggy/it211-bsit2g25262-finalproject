<?php
$servername = "localhost";
$username = "root";
$password = "";
$dbname = "lynx_db";

try {
    $conn = new PDO("mysql:host=$servername;dbname=$dbname", $username, $password);
    $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    // Fix men_products
    $conn->exec("ALTER TABLE men_products MODIFY id INT AUTO_INCREMENT PRIMARY KEY");
    $conn->exec("ALTER TABLE men_products DROP COLUMN author, DROP COLUMN publish_date, DROP COLUMN media, DROP COLUMN media_type, DROP COLUMN name, DROP COLUMN description IF EXISTS");

    // Fix women_products
    $conn->exec("ALTER TABLE women_products MODIFY id INT AUTO_INCREMENT PRIMARY KEY");
    $conn->exec("ALTER TABLE women_products DROP COLUMN author, DROP COLUMN publish_date, DROP COLUMN media, DROP COLUMN media_type, DROP COLUMN name IF EXISTS");

    echo "Tables fixed successfully!\n";
    echo "Run: php bulk_insert_products_fixed.php to populate clothing products\n";

} catch(PDOException $e) {
    echo "Error: " . $e->getMessage() . "\n";
}
?>
