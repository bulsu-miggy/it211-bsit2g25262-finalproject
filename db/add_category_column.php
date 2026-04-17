<?php
include 'connection.php';

try {
    // ALTER women_products
    $conn->exec("ALTER TABLE women_products ADD COLUMN IF NOT EXISTS category VARCHAR(50) DEFAULT NULL");
    echo "Added category column to women_products.<br>";

    // ALTER men_products
    $conn->exec("ALTER TABLE men_products ADD COLUMN IF NOT EXISTS category VARCHAR(50) DEFAULT NULL");
    echo "Added category column to men_products.<br>";

    echo "Schema updated successfully!";
} catch (PDOException $e) {
    echo "Error: " . $e->getMessage();
}
?>

