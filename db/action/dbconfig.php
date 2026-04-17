<?php

$dbservername = "localhost";
$dbusername = "root";
$dbpassword = "";
$dbname = "laces";

try {
    $conn = new PDO("mysql:host=$dbservername;port=3306", $dbusername, $dbpassword);
    $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    $conn->setAttribute(PDO::MYSQL_ATTR_USE_BUFFERED_QUERY, true);

    $sql = "USE " . $dbname;
    $conn->exec($sql);

    $conn->exec("CREATE TABLE IF NOT EXISTS login (
        id INT(11) UNSIGNED AUTO_INCREMENT PRIMARY KEY,
        first_name VARCHAR(200) NOT NULL,
        last_name VARCHAR(200) NOT NULL,
        username VARCHAR(100) NOT NULL,
        email VARCHAR(100) NOT NULL,
        img_url TEXT,
        password TEXT NOT NULL,
        role VARCHAR(50) NOT NULL DEFAULT 'user',
        login_date TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
        logout_date TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
    )");

    // New categories table
    $conn->exec("CREATE TABLE IF NOT EXISTS categories (
        id INT(11) UNSIGNED AUTO_INCREMENT PRIMARY KEY,
        name VARCHAR(100) NOT NULL UNIQUE,
        description TEXT,
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
    )");

    // Updated products table with category_id instead of category
    $conn->exec("CREATE TABLE IF NOT EXISTS products (
        id INT(11) UNSIGNED AUTO_INCREMENT PRIMARY KEY,
        name VARCHAR(255) NOT NULL,
        category_id INT(11) UNSIGNED,
        color VARCHAR(255) DEFAULT NULL,
        size VARCHAR(50) DEFAULT NULL,
        price DECIMAL(10,2) NOT NULL DEFAULT 0.00,
        image TEXT DEFAULT 'assets2/adidasblablabla.png',
        sales INT(11) NOT NULL DEFAULT 0,
        stock INT(11) NOT NULL DEFAULT 0,
        description TEXT DEFAULT NULL,
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        INDEX idx_category (category_id),
        INDEX idx_stock (stock),
        CONSTRAINT fk_products_categories FOREIGN KEY (category_id) REFERENCES categories(id) ON DELETE SET NULL
    )");

    // Insert default categories
    $defaultCategories = ['Running Shoes', 'Court Shoes', 'Field Shoes', 'Gym Shoes', 'Sneakers', 'Hiking Shoes'];
    foreach ($defaultCategories as $cat) {
        $stmt = $conn->prepare("INSERT IGNORE INTO categories (name) VALUES (?)");
        $stmt->execute([$cat]);
    }

    // Insert sample products if table is empty
    $productCount = $conn->query("SELECT COUNT(*) FROM products")->fetchColumn();
    if ($productCount == 0) {
        $sampleProducts = [
            ['name' => 'Nike Air Max', 'category_id' => 1, 'color' => 'Black/White', 'size' => '10', 'price' => 120.00, 'image' => 'assets2/adidasblablabla.png', 'sales' => 1240, 'stock' => 50],
            ['name' => 'Adidas Ultraboost', 'category_id' => 1, 'color' => 'White/Gray', 'size' => '9', 'price' => 150.00, 'image' => 'assets2/adidasblablabla.png', 'sales' => 1180, 'stock' => 45],
            ['name' => 'Puma Suede', 'category_id' => 5, 'color' => 'Blue/Red', 'size' => '11', 'price' => 85.00, 'image' => 'assets2/adidasblablabla.png', 'sales' => 980, 'stock' => 60],
            ['name' => 'New Balance 990', 'category_id' => 1, 'color' => 'Grey/Navy', 'size' => '10', 'price' => 175.00, 'image' => 'assets2/adidasblablabla.png', 'sales' => 930, 'stock' => 30],
            ['name' => 'Reebok Classic', 'category_id' => 5, 'color' => 'White/Green', 'size' => '9', 'price' => 95.00, 'image' => 'assets2/adidasblablabla.png', 'sales' => 890, 'stock' => 55],
            ['name' => 'Vans Old Skool', 'category_id' => 5, 'color' => 'Black/White', 'size' => '8', 'price' => 65.00, 'image' => 'assets2/adidasblablabla.png', 'sales' => 860, 'stock' => 70],
            ['name' => 'Converse Chuck Taylor', 'category_id' => 5, 'color' => 'Black', 'size' => '10', 'price' => 55.00, 'image' => 'assets2/adidasblablabla.png', 'sales' => 820, 'stock' => 80],
            ['name' => 'ASICS Gel-Kayano', 'category_id' => 1, 'color' => 'Blue/Orange', 'size' => '11', 'price' => 160.00, 'image' => 'assets2/adidasblablabla.png', 'sales' => 780, 'stock' => 40],
            ['name' => 'Saucony Endorphin', 'category_id' => 1, 'color' => 'Red/White', 'size' => '9', 'price' => 140.00, 'image' => 'assets2/adidasblablabla.png', 'sales' => 740, 'stock' => 35],
            ['name' => 'Hoka Bondi 8', 'category_id' => 6, 'color' => 'Blue/White', 'size' => '10', 'price' => 165.00, 'image' => 'assets2/adidasblablabla.png', 'sales' => 700, 'stock' => 25],
            ['name' => 'Brooks Ghost 15', 'category_id' => 1, 'color' => 'Black/Gray', 'size' => '8', 'price' => 130.00, 'image' => 'assets2/adidasblablabla.png', 'sales' => 660, 'stock' => 50],
            ['name' => 'Under Armour HOVR', 'category_id' => 4, 'color' => 'Blue/Red', 'size' => '11', 'price' => 110.00, 'image' => 'assets2/adidasblablabla.png', 'sales' => 620, 'stock' => 45],
        ];
        $stmt = $conn->prepare("INSERT INTO products (name, category_id, color, size, price, image, sales, stock) VALUES (?, ?, ?, ?, ?, ?, ?, ?)");
        foreach ($sampleProducts as $prod) {
            $stmt->execute(array_values($prod));
        }
    }

    $conn->exec("CREATE TABLE IF NOT EXISTS cart (
        id INT(11) UNSIGNED AUTO_INCREMENT PRIMARY KEY,
        user_id INT(11) UNSIGNED NOT NULL,
        product_id INT(11) UNSIGNED NOT NULL,
        quantity INT(11) NOT NULL DEFAULT 1,
        added_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
        UNIQUE KEY user_product (user_id, product_id)
    )");

    $conn->exec("CREATE TABLE IF NOT EXISTS orders (
        id INT(11) UNSIGNED AUTO_INCREMENT PRIMARY KEY,
        order_code VARCHAR(50) NOT NULL,
        user_id INT(11) UNSIGNED NOT NULL,
        total_amount DECIMAL(10,2) NOT NULL DEFAULT 0.00,
        status VARCHAR(30) NOT NULL DEFAULT 'Pending',
        created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
        UNIQUE KEY order_code (order_code)
    )");

    $ordersUserFkExists = $conn->query("SELECT CONSTRAINT_NAME
        FROM information_schema.KEY_COLUMN_USAGE
        WHERE TABLE_SCHEMA = DATABASE()
          AND TABLE_NAME = 'orders'
          AND COLUMN_NAME = 'user_id'
          AND REFERENCED_TABLE_NAME = 'login'
        LIMIT 1")->fetchColumn();

    if (!$ordersUserFkExists) {
        $conn->exec("ALTER TABLE orders
            ADD CONSTRAINT fk_orders_user
            FOREIGN KEY (user_id) REFERENCES login(id)
            ON DELETE CASCADE ON UPDATE CASCADE");
    }

    $conn->exec("CREATE TABLE IF NOT EXISTS order_items (
        id INT(11) UNSIGNED AUTO_INCREMENT PRIMARY KEY,
        order_id INT(11) UNSIGNED NOT NULL,
        product_id INT(11) UNSIGNED NOT NULL,
        quantity INT(11) NOT NULL DEFAULT 1,
        unit_price DECIMAL(10,2) NOT NULL DEFAULT 0.00,
        subtotal DECIMAL(10,2) NOT NULL DEFAULT 0.00,
        KEY order_id (order_id)
    )");

    $conn->exec("CREATE TABLE IF NOT EXISTS product_images (
        id INT(11) UNSIGNED AUTO_INCREMENT PRIMARY KEY,
        product_id INT(11) UNSIGNED NOT NULL,
        image_path VARCHAR(255) NOT NULL,
        sort_order INT(11) NOT NULL DEFAULT 0,
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        INDEX idx_product (product_id),
        CONSTRAINT fk_product_images_product FOREIGN KEY (product_id) REFERENCES products(id) ON DELETE CASCADE
    )");

    $conn->exec("CREATE TABLE IF NOT EXISTS customer_addresses (
        id INT(11) UNSIGNED AUTO_INCREMENT PRIMARY KEY,
        user_id INT(11) UNSIGNED NOT NULL,
        address_type VARCHAR(50) NOT NULL DEFAULT 'home',
        street_address VARCHAR(255) NOT NULL,
        city VARCHAR(100) NOT NULL,
        state_province VARCHAR(100),
        postal_code VARCHAR(20),
        country VARCHAR(100),
        phone_number VARCHAR(20),
        is_default BOOLEAN DEFAULT FALSE,
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        INDEX idx_user (user_id),
        CONSTRAINT fk_addresses_user FOREIGN KEY (user_id) REFERENCES login(id) ON DELETE CASCADE
    )");

    $conn->exec("CREATE TABLE IF NOT EXISTS user_profiles (
        id INT(11) UNSIGNED AUTO_INCREMENT PRIMARY KEY,
        user_id INT(11) UNSIGNED NOT NULL,
        contact_number VARCHAR(30) DEFAULT NULL,
        profile_image TEXT DEFAULT NULL,
        address TEXT DEFAULT NULL,
        postal_code VARCHAR(20) DEFAULT NULL,
        created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
        updated_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
        UNIQUE KEY user_profile_unique (user_id)
    )");

    $profileImageColumnExists = $conn->query("SELECT COUNT(*)
        FROM information_schema.COLUMNS
        WHERE TABLE_SCHEMA = DATABASE()
          AND TABLE_NAME = 'user_profiles'
          AND COLUMN_NAME = 'profile_image'")->fetchColumn();

    if (!$profileImageColumnExists) {
        $conn->exec("ALTER TABLE user_profiles ADD COLUMN profile_image TEXT NULL AFTER contact_number");
    }

    $profilesUserFkExists = $conn->query("SELECT CONSTRAINT_NAME
        FROM information_schema.KEY_COLUMN_USAGE
        WHERE TABLE_SCHEMA = DATABASE()
          AND TABLE_NAME = 'user_profiles'
          AND COLUMN_NAME = 'user_id'
          AND REFERENCED_TABLE_NAME = 'login'
        LIMIT 1")->fetchColumn();

    if (!$profilesUserFkExists) {
        $conn->exec("ALTER TABLE user_profiles
            ADD CONSTRAINT fk_user_profiles_user
            FOREIGN KEY (user_id) REFERENCES login(id)
            ON DELETE CASCADE ON UPDATE CASCADE");
    }

} catch (PDOException $e) {
    die("Connection setup failed: " . $e->getMessage());
}

?>