<?php
/**
 * ==========================================
 * DATABASE CONNECTION & INITIALIZATION
 * ==========================================
 * Establishes PDO connection to MySQL database (sipflaskdb)
 * Creates all necessary tables and seeds sample data
 * All database operations throughout system use this connection
 */

// Database connection credentials
$servername = "localhost";  // MySQL server address
$username = "root";         // MySQL username
$password = "";             // MySQL password (empty for local development)
$dbname = "it211_g1g4";     // Database name (created if doesn't exist)

try {
    // ==========================================
    // CREATE DATABASE CONNECTION
    // ==========================================
    // Establish PDO connection to MySQL server
    $conn = new PDO("mysql:host=$servername;port=3306", $username, $password);
    
    // Set error mode to throw exceptions (easier error handling)
    $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    
    // Create database if it doesn't already exist
    $sql = "CREATE DATABASE IF NOT EXISTS $dbname";
    $conn->exec($sql);

    // Select the created/existing database for subsequent operations
    $conn->exec("USE $dbname");

    // ==========================================
    // CREATE USERS TABLE
    // ==========================================
    // Stores customer and admin account information
    // is_admin field distinguishes between regular users (0) and administrators (1)
    $sql = "CREATE TABLE IF NOT EXISTS users (
        id INT(11) UNSIGNED AUTO_INCREMENT PRIMARY KEY,
        first_name VARCHAR(255) NOT NULL,
        last_name VARCHAR(255) NOT NULL,
        email VARCHAR(255) NOT NULL UNIQUE,
        phone VARCHAR(20),
        password VARCHAR(255) NOT NULL,
        is_admin TINYINT(1) DEFAULT 0,
        reset_token VARCHAR(255) DEFAULT NULL,
        reset_expiry DATETIME DEFAULT NULL,
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
    )";
    $conn->exec($sql);

    // Add admin and reset columns if they don't exist (for existing tables)
    // This allows safe updates when adding new features to existing databases
    try {
        $conn->exec("ALTER TABLE users ADD COLUMN IF NOT EXISTS is_admin TINYINT(1) DEFAULT 0");
        $conn->exec("ALTER TABLE users ADD COLUMN IF NOT EXISTS reset_token VARCHAR(255) DEFAULT NULL");
        $conn->exec("ALTER TABLE users ADD COLUMN IF NOT EXISTS reset_expiry DATETIME DEFAULT NULL");
    } catch (PDOException $e) {
        // Silently skip - columns might already exist in database
    }

    // ==========================================
    // CREATE ORDERS TABLE
    // ==========================================
    // Stores customer purchase orders and payment information
    // Linked to users table via user_id foreign key
    $sql = "CREATE TABLE IF NOT EXISTS orders (
        id INT(11) UNSIGNED AUTO_INCREMENT PRIMARY KEY,
        user_id INT(11) UNSIGNED NOT NULL,
        total_amount DECIMAL(10,2) NOT NULL,
        shipping_fee DECIMAL(10,2) NOT NULL,
        payment_method VARCHAR(50) NOT NULL,
        status VARCHAR(50) DEFAULT 'Pending',
        seller_message TEXT,
        order_date TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
    )";
    $conn->exec($sql);

    // ==========================================
    // CREATE ORDER_ITEMS TABLE
    // ==========================================
    // Stores individual products within each order
    // Each order can have multiple items
    $sql = "CREATE TABLE IF NOT EXISTS order_items (
        id INT(11) UNSIGNED AUTO_INCREMENT PRIMARY KEY,
        order_id INT(11) UNSIGNED NOT NULL,
        product_name VARCHAR(255) NOT NULL,
        size VARCHAR(10) NOT NULL,
        quantity INT(11) NOT NULL,
        price DECIMAL(10,2) NOT NULL,
        FOREIGN KEY (order_id) REFERENCES orders(id) ON DELETE CASCADE
    )";
    $conn->exec($sql);

    // ==========================================
    // CREATE CATEGORIES TABLE
    // ==========================================
    // Product category classification
    $sql = "CREATE TABLE IF NOT EXISTS categories (
        id INT(11) UNSIGNED AUTO_INCREMENT PRIMARY KEY,
        name VARCHAR(255) NOT NULL UNIQUE,
        description TEXT,
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
    )";
    $conn->exec($sql);

    // ==========================================
    // CREATE PRODUCTS TABLE
    // ==========================================
    // Main product catalog for the store
    $sql = "CREATE TABLE IF NOT EXISTS products (
        id INT(11) UNSIGNED AUTO_INCREMENT PRIMARY KEY,
        name VARCHAR(255) NOT NULL,
        category_id INT(11) UNSIGNED,
        description TEXT,
        price DECIMAL(10,2) NOT NULL,
        stock_quantity INT(11) DEFAULT 0,
        image_path VARCHAR(255),
        status ENUM('active', 'inactive') DEFAULT 'active',
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        FOREIGN KEY (category_id) REFERENCES categories(id) ON DELETE SET NULL
    )";
    $conn->exec($sql);

    // ==========================================
    // SEED SAMPLE DATA
    // ==========================================
    // Check if products table is empty before seeding
    try {
        $checkStmt = $conn->query("SELECT COUNT(*) as count FROM products");
        $rowCount = $checkStmt->fetch(PDO::FETCH_ASSOC)['count'];
        
        // Only seed if table is empty (prevents overwriting existing products)
        if ($rowCount == 0) {
            // First ensure categories exist
            $categoryCount = $conn->query("SELECT COUNT(*) as count FROM categories")->fetch(PDO::FETCH_ASSOC)['count'];
            if ($categoryCount == 0) {
                $conn->exec("INSERT INTO categories (id, name, description) VALUES
                    (1, '16oz', 'SipFlask bottles in 16oz size'),
                    (2, '25oz', 'SipFlask bottles in 25oz size'),
                    (3, '32oz', 'SipFlask bottles in 32oz size')");
            }
            
            // Seed predefined products
            $conn->exec("INSERT INTO products (id, name, category_id, description, price, stock_quantity, image_path, status) VALUES
                (1, 'Grape Juice Flask 16oz', 1, 'Your perfect daily companion. Vacuum-insulated and leak-proof.', 850.00, 50, 'images/16oz/grapejuice.png', 'active'),
                (2, 'Pistachio Flask 16oz', 1, 'Compact size for quick hydration on the go.', 850.00, 50, 'images/16oz/Pistachio.png', 'active'),
                (3, 'Royal Blue Flask 25oz', 2, 'The middle ground of hydration and portability.', 890.00, 45, 'images/25oz/RoyalBlue.png', 'active'),
                (4, 'Lavender Flask 25oz', 2, 'A refreshing look for your everyday hydration.', 890.00, 45, 'images/25oz/Lavender.png', 'active'),
                (5, 'Nori Flask 32oz', 3, 'Built for the rugged outdoors. Sweat-proof design.', 950.00, 40, 'images/32oz/noriflask.png', 'active'),
                (6, 'Slate Gray Flask 32oz', 3, 'Stays ice-cold even under the hot sun.', 950.00, 40, 'images/32oz/SlateGray.png', 'active'),
                (7, 'Magenta Flask 32oz', 3, 'Tough, reliable, and keeps coffee hot for hours.', 950.00, 40, 'images/32oz/Magenta.png', 'active')");
        }
    } catch (PDOException $e) {
        // Silently skip - seeding might fail if data already exists
        echo $e;
    }

    echo "Connected successfully and database/table ready";
} catch(PDOException $e) {
    echo "Connection failed: " . $e->getMessage();
    die();
}
?>