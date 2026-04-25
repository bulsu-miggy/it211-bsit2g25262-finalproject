<?php
/**
 * connection.php
 * Establishes a PDO connection to the MySQL server and ensures the 
 * required database and tables are created. This file is central 
 * to all database interactions in the application.
 */
$servername = "localhost";
$username = "root";
$password = "";
$dbname = "it211_g1g6"; 

try {
    $conn = new PDO("mysql:host=$servername", $username, $password);
    $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    // 1. DATABASE SETUP: Create the database and set character encoding
    $conn->exec("CREATE DATABASE IF NOT EXISTS $dbname CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci");
    $conn->exec("USE $dbname");

    /* 2. LOGIN TABLE 
       PURPOSE: Primary account registry. Stores core security credentials 
       (email/password) and the account display name used throughout the site.
    */
    $conn->exec("CREATE TABLE IF NOT EXISTS login (
        user_id INT(11) AUTO_INCREMENT PRIMARY KEY,
        full_name VARCHAR(255) NOT NULL,
        email VARCHAR(100) NOT NULL UNIQUE,
        password VARCHAR(255) NOT NULL,
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
    ) ENGINE=InnoDB");

    // For existing databases, add the missing full_name column if it was created without it.
    $loginColumnCheck = $conn->query("SHOW COLUMNS FROM login LIKE 'full_name'");
    if ($loginColumnCheck->rowCount() == 0) {
        $conn->exec("ALTER TABLE login ADD COLUMN full_name VARCHAR(255) NOT NULL AFTER user_id");
    }

    /* 3. PROFILE_DETAILS TABLE 
       PURPOSE: Stores the user's personal identity information like full name, 
       username, email, phone, and gender to customize their profile view.
    */
    $conn->exec("CREATE TABLE IF NOT EXISTS profile_details (
        profile_id INT(11) AUTO_INCREMENT PRIMARY KEY,
        user_id INT(11) NOT NULL,
        full_name VARCHAR(255) NOT NULL,
        username VARCHAR(50) NOT NULL,
        email VARCHAR(100) NOT NULL,
        phone VARCHAR(50) DEFAULT NULL,
        password VARCHAR(255) NOT NULL,
        gender ENUM('Male', 'Female', 'Other') DEFAULT NULL,
        profile_picture TEXT DEFAULT NULL,
        FOREIGN KEY (user_id) REFERENCES login(user_id) ON DELETE CASCADE
    ) ENGINE=InnoDB");

    // Ensure the phone field exists on profile_details for legacy installations.
    $profilePhoneCheck = $conn->query("SHOW COLUMNS FROM profile_details LIKE 'phone'");
    if ($profilePhoneCheck->rowCount() == 0) {
        $conn->exec("ALTER TABLE profile_details ADD COLUMN phone VARCHAR(50) DEFAULT NULL AFTER email");
    }

    /* 4. CANDLES TABLE 
       PURPOSE: The product catalog. Stores descriptions, seasonal categories, 
       and prices for every candle available in the shop.
    */
    $conn->exec("CREATE TABLE IF NOT EXISTS candles (
        product_id INT(11) AUTO_INCREMENT PRIMARY KEY,
        name VARCHAR(200) NOT NULL, 
        price DECIMAL(10,2) NOT NULL, 
        description TEXT, 
        image_url TEXT,
        scent_notes VARCHAR(255) DEFAULT 'Signature Scent', 
        category ENUM('Summer', 'Fall', 'Winter', 'Spring') NOT NULL,
        INDEX (category)
    ) ENGINE=InnoDB");

    $conn->exec("CREATE TABLE IF NOT EXISTS categories (
        category_id INT(11) AUTO_INCREMENT PRIMARY KEY,
        name VARCHAR(100) NOT NULL UNIQUE,
        slug VARCHAR(120) NOT NULL UNIQUE,
        description TEXT DEFAULT NULL,
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
    ) ENGINE=InnoDB");

    $conn->exec("INSERT IGNORE INTO categories (name, slug, description) VALUES
        ('Summer', 'summer', 'Summer collection of seasonal scents.'),
        ('Spring', 'spring', 'Fresh and floral spring fragrances.'),
        ('Fall', 'fall', 'Warm, cozy fall-inspired candles.'),
        ('Winter', 'winter', 'Cooling winter and holiday aromas.')");

    $categoryColumn = $conn->query("SHOW COLUMNS FROM candles WHERE Field = 'category'")->fetch(PDO::FETCH_ASSOC);
    if ($categoryColumn && stripos($categoryColumn['Type'], 'varchar') === false) {
        $conn->exec("ALTER TABLE candles MODIFY category VARCHAR(100) NOT NULL");
    }

    $stockColumn = $conn->query("SHOW COLUMNS FROM candles WHERE Field = 'stock_qty'")->fetch(PDO::FETCH_ASSOC);
    if (!$stockColumn) {
        $conn->exec("ALTER TABLE candles ADD COLUMN stock_qty INT(11) NOT NULL DEFAULT 0 AFTER category");
    }

    $statusColumn = $conn->query("SHOW COLUMNS FROM candles WHERE Field = 'status'")->fetch(PDO::FETCH_ASSOC);
    if (!$statusColumn) {
        $conn->exec("ALTER TABLE candles ADD COLUMN status VARCHAR(32) NOT NULL DEFAULT 'In Stock' AFTER stock_qty");
    }

    // 4.1 AUTO-STATUS TRIGGERS
    // Automatically updates the 'status' column based on 'stock_qty' value.
    $conn->exec("DROP TRIGGER IF EXISTS trg_candles_status_insert");
    $conn->exec("CREATE TRIGGER trg_candles_status_insert 
                BEFORE INSERT ON candles FOR EACH ROW 
                BEGIN 
                    IF NEW.stock_qty <= 0 THEN SET NEW.status = 'Out of Stock';
                    ELSEIF NEW.stock_qty <= 10 THEN SET NEW.status = 'Low Stock';
                    ELSE SET NEW.status = 'In Stock'; END IF;
                END");

    $conn->exec("DROP TRIGGER IF EXISTS trg_candles_status_update");
    $conn->exec("CREATE TRIGGER trg_candles_status_update 
                BEFORE UPDATE ON candles FOR EACH ROW 
                BEGIN 
                    IF NEW.stock_qty <= 0 THEN SET NEW.status = 'Out of Stock';
                    ELSEIF NEW.stock_qty <= 10 THEN SET NEW.status = 'Low Stock';
                    ELSE SET NEW.status = 'In Stock'; END IF;
                END");

    /* 5. BASKET TABLE 
       PURPOSE: Temporary shopping cart. Tracks items a user has added 
       (with specific sizes) but has not yet purchased.
    */
    $conn->exec("CREATE TABLE IF NOT EXISTS basket (
        basket_id INT(11) AUTO_INCREMENT PRIMARY KEY,
        user_id INT(11) NOT NULL,
        product_id INT(11) NOT NULL,
        quantity INT(11) NOT NULL DEFAULT 1,
        size ENUM('Small', 'Medium', 'Large') DEFAULT 'Small',
        added_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        FOREIGN KEY (user_id) REFERENCES login(user_id) ON DELETE CASCADE,
        FOREIGN KEY (product_id) REFERENCES candles(product_id) ON DELETE CASCADE
    ) ENGINE=InnoDB");

    /* 6. ORDERS TABLE 
       PURPOSE: The transaction master record. Stores the total amount and 
       the current fulfillment status (Processing, Completed, etc.).
    */
    $conn->exec("CREATE TABLE IF NOT EXISTS orders (
        order_id INT(11) AUTO_INCREMENT PRIMARY KEY,
        order_number VARCHAR(20) UNIQUE NOT NULL,
        user_id INT(11) NOT NULL, 
        total_amount DECIMAL(10,2) NOT NULL,


        status ENUM('Pending', 'Processing', 'Completed', 'Cancelled') DEFAULT 'Pending',


        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        FOREIGN KEY (user_id) REFERENCES login(user_id) ON DELETE CASCADE
    ) ENGINE=InnoDB");

    /* 7. USER_ADDRESSES TABLE 
       PURPOSE: Shipping directory. Stores multiple delivery locations for 
       each user, identifying which one is their primary/default address.
    */
    $conn->exec("CREATE TABLE IF NOT EXISTS user_addresses (
        address_id INT(11) AUTO_INCREMENT PRIMARY KEY,
        user_id INT(11) NOT NULL,
        label VARCHAR(50) DEFAULT 'Home', 
        full_name VARCHAR(100) NOT NULL,
        street_address TEXT NOT NULL,
        apartment VARCHAR(100) DEFAULT NULL,
        city VARCHAR(50) NOT NULL,
        zip_code VARCHAR(10) NOT NULL,
        phone_number VARCHAR(20) NOT NULL,
        is_default TINYINT(1) DEFAULT 0,
        FOREIGN KEY (user_id) REFERENCES login(user_id) ON DELETE CASCADE
    ) ENGINE=InnoDB");

    $addressApartmentColumn = $conn->query("SHOW COLUMNS FROM user_addresses LIKE 'apartment'")->fetch(PDO::FETCH_ASSOC);
    if (!$addressApartmentColumn) {
        $conn->exec("ALTER TABLE user_addresses ADD COLUMN apartment VARCHAR(100) DEFAULT NULL AFTER street_address");
    }

    /* 8. ORDER_ITEMS TABLE 
       PURPOSE: Itemized receipt. Breaks down exactly which candles (and what sizes) 
       were part of a specific order for historical record-keeping.
    */
    $conn->exec("CREATE TABLE IF NOT EXISTS order_items (
        order_item_id INT(11) AUTO_INCREMENT PRIMARY KEY,
        order_id INT(11) NOT NULL,
        product_id INT(11) DEFAULT NULL,
        quantity INT(11) NOT NULL,
        price DECIMAL(10,2) NOT NULL,
        
        size ENUM('Small', 'Medium', 'Large') NOT NULL,
        FOREIGN KEY (order_id) REFERENCES orders(order_id) ON DELETE CASCADE,
        FOREIGN KEY (product_id) REFERENCES candles(product_id) ON DELETE SET NULL
    ) ENGINE=InnoDB");

    /* 9. ADMINS TABLE 
       PURPOSE: Admin accounts for dashboard access. Separate from customer login table.
    */
    $conn->exec("CREATE TABLE IF NOT EXISTS admins (
        id INT(11) AUTO_INCREMENT PRIMARY KEY,
        full_name VARCHAR(255) NOT NULL,
        email VARCHAR(100) NOT NULL UNIQUE,
        password_hash VARCHAR(255) NOT NULL,
        role VARCHAR(50) DEFAULT 'Admin',
        last_login TIMESTAMP NULL,
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
    ) ENGINE=InnoDB");

    // Add demo super admin if not exists
    $demo_hash = password_hash('Solis@1', PASSWORD_DEFAULT);
    $stmt = $conn->query("SELECT id FROM admins WHERE email = 'admin@solis.com'");
    if (!$stmt->fetch()) {
        $conn->exec("INSERT INTO admins (full_name, email, password_hash, role) VALUES ('Super Admin', 'admin@solis.com', '$demo_hash', 'Super Admin')");
    }

    // Ensure existing installations keep the nullable product_id required by ON DELETE SET NULL.
    $orderItemColumnCheck = $conn->query("SHOW COLUMNS FROM order_items LIKE 'product_id'");
    $orderItemColumn = $orderItemColumnCheck->fetch(PDO::FETCH_ASSOC);
    if ($orderItemColumn && $orderItemColumn['Null'] === 'NO') {
        $conn->exec("ALTER TABLE order_items MODIFY product_id INT(11) DEFAULT NULL");
    }

    echo "db_created_successfully";

} catch(PDOException $e) {
    die("Database Setup Error: " . $e->getMessage());
}
?>
