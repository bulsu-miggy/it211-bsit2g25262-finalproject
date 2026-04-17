<?php
require_once 'connection.php';

$sql = "CREATE TABLE IF NOT EXISTS stats (
    id INT(11) UNSIGNED NOT NULL AUTO_INCREMENT PRIMARY KEY,
    total_revenue DECIMAL(15,2) DEFAULT 0.00,
    total_orders INT DEFAULT 0,
    total_customers INT DEFAULT 0,
    last_updated TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
)";

try {
    $conn->exec($sql);
    
    // Ensure single summary row
    $conn->exec("INSERT INTO stats (id) VALUES (1) ON DUPLICATE KEY UPDATE id=1");
    
    echo "<h2 style='color: green;'>Stats table created successfully!</h2>";
    echo "<p>Initialized with totals: revenue=0, orders=0, customers=0</p>";
    echo "<a href='../dashboard/dashboard.php'>Go to Dashboard</a>";
} catch (PDOException $e) {
    echo "<h2 style='color: red;'>Error: " . $e->getMessage() . "</h2>";
}
?>

