<?php
$servername = "localhost";
$username = "root";
$password = "";
$dbname = "lynx_db";

try {
    $conn = new PDO("mysql:host=$servername;dbname=$dbname", $username, $password);
    $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    echo "DB connected.\n";
} catch(PDOException $e) {
    die("DB error: " . $e->getMessage());
}

$tables = ['men_products', 'women_products'];
$updated = 0;

foreach ($tables as $table) {
    echo "Cleaning titles in $table...\n";
    $stmt = $conn->prepare("SELECT id, title FROM `$table`");
    $stmt->execute();
    $products = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    foreach ($products as $p) {
        $old_title = $p['title'];
        $new_title = preg_replace('/\\s*\\([^)]*\\)\\.?\\s*$/', '', trim($old_title));
        if ($old_title !== $new_title) {
            $up_stmt = $conn->prepare("UPDATE `$table` SET title = ? WHERE id = ?");
            $up_stmt->execute([$new_title, $p['id']]);
            echo "Updated: '$old_title' → '$new_title'\n";
            $updated++;
        }
    }
}

echo "\nUpdated $updated product titles. Cleaned (Category) suffixes.\nDone!\n";
?>
