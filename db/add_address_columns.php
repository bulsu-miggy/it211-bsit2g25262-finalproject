<?php
// adds missing address fields to the login table
$host = "localhost";
$username = "root";
$password = "";
$dbname = "lynx_db";

$conn = new PDO("mysql:host=$host;dbname=$dbname", $username, $password);
$conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

try {
    // Check if columns exist before adding them
    $columns_to_add = [
        'phone' => 'VARCHAR(20)',
        'street' => 'VARCHAR(255)',
        'postal_code' => 'VARCHAR(20)',
        'state' => 'VARCHAR(100)',
        'city' => 'VARCHAR(100)',
        'barangay' => 'VARCHAR(100)'
    ];

    foreach ($columns_to_add as $col_name => $col_type) {
        // Check if column exists
        $check_sql = "SELECT COLUMN_NAME FROM INFORMATION_SCHEMA.COLUMNS 
                      WHERE TABLE_NAME = 'login' AND COLUMN_NAME = ?";
        $check_stmt = $conn->prepare($check_sql);
        $check_stmt->execute([$col_name]);
        $result = $check_stmt->fetch();

        if (!$result) {
            // Column doesn't exist, add it
            $alter_sql = "ALTER TABLE login ADD COLUMN $col_name $col_type DEFAULT NULL";
            $conn->exec($alter_sql);
            echo "Added column: $col_name\n";
        } else {
            echo "Column $col_name already exists\n";
        }
    }

    echo "Database migration completed successfully!";
} catch (PDOException $e) {
    echo "Error: " . $e->getMessage();
}
?>
