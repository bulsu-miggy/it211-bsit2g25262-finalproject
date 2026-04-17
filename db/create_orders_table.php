<?php
require_once 'connection.php';

$sql = "CREATE TABLE IF NOT EXISTS orders (
    id INT(11) UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    customer_name VARCHAR(255) NOT NULL,
    total_amount DECIMAL(10,2) NOT NULL,
    status VARCHAR(50) DEFAULT 'pending',
    order_date TIMESTAMP DEFAULT CURRENT_TIMESTAMP
)";

try {
    $conn->exec($sql);
    echo "<h2 style='color: green;'>Orders table created successfully!</h2>";
    echo "<p>Columns: id (auto), customer_name, total_amount, status, order_date</p>";
    echo "<a href='../dashboard/dashboard.php'>Go to Dashboard</a>";
} catch (PDOException $e) {
    echo "<h2 style='color: red;'>Error: " . $e->getMessage() . "</h2>";
}
?>

