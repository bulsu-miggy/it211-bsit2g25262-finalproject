<?php
include 'connection.php';

try {
  $conn_products = new PDO("mysql:host=localhost;dbname=lynx_shop", "root", "");
  $conn_products->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
  
  // Clear and repopulate if needed
  $conn_products->exec("TRUNCATE women_products; TRUNCATE men_products;");
  
$source_conn = new PDO("mysql:host=localhost;dbname=lynx_db", "root", "");
  $stmt = $source_conn->query("SELECT id, title as name, excerpt as description, price, imgurl as image FROM books");
  
  $women_data = $stmt->fetchAll(PDO::FETCH_ASSOC);
  
  $women_stmt = $conn_products->prepare("INSERT INTO women_products (name, description, price, image) VALUES (?, ?, ?, ?)");
  $men_stmt = $conn_products->prepare("INSERT INTO men_products (name, description, price, image) VALUES (?, ?, ?, ?)");
  
  foreach ($women_data as $row) {
    if ($row['id'] % 2 == 0) {
      $women_stmt->execute([$row['name'], $row['description'], $row['price'], $row['image']]);
    } else {
      $men_stmt->execute([$row['name'], $row['description'], $row['price'], $row['image']]);
    }
  }
  
  echo "Tables created and populated successfully!";
} catch (PDOException $e) {
  echo "Error: " . $e->getMessage();
}
?>

