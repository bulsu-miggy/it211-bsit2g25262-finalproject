<?php

require_once __DIR__ . '/../config.php';

$servername = "localhost";
$username = "root";
$password = "";
$dbname = "laces";

function normalizeImagePathFromProduct($rawImage)
{
    $value = trim((string) $rawImage);
    if ($value === '') {
        return '';
    }

    if (strpos($value, 'images/') === 0 || strpos($value, 'assets2/') === 0) {
        return $value;
    }

    return 'images/' . $value;
}

function normalizeMatchKey($value)
{
    $normalized = strtolower((string) $value);
    $normalized = preg_replace('/[^a-z0-9]+/', '', $normalized);
    return $normalized ?? '';
}

try {
    $conn = new PDO("mysql:host=$servername;port=3306", $username, $password);
    $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    $conn->exec("CREATE DATABASE IF NOT EXISTS `" . $dbname . "`");
    $conn->exec("USE `" . $dbname . "`");

    $conn->exec("CREATE TABLE IF NOT EXISTS login (
        id INT(11) UNSIGNED AUTO_INCREMENT PRIMARY KEY,
        first_name VARCHAR(200) NOT NULL,
        last_name VARCHAR(200) NOT NULL,
        username VARCHAR(100) NOT NULL,
        email VARCHAR(100) NOT NULL,
        img_url TEXT,
        password TEXT NOT NULL,
        role VARCHAR(50) NOT NULL DEFAULT 'user',
        login_date TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP COMMENT 'Monitor login date',
        logout_date TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP COMMENT 'Monitor logout date'
    )");

    $conn->exec("CREATE TABLE IF NOT EXISTS products (
        id INT(11) UNSIGNED AUTO_INCREMENT PRIMARY KEY,
        name VARCHAR(255) NOT NULL,
        category VARCHAR(100) NOT NULL DEFAULT 'Footwear',
        color VARCHAR(255) DEFAULT NULL,
        size VARCHAR(50) DEFAULT NULL,
        price DECIMAL(10,2) NOT NULL DEFAULT 0.00,
        image TEXT DEFAULT 'assets2/adidasblablabla.png',
        sales INT(11) NOT NULL DEFAULT 0,
        stock INT(11) NOT NULL DEFAULT 0,
        description TEXT DEFAULT NULL,
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
    )");

    $conn->exec("CREATE TABLE IF NOT EXISTS categories (
        id INT(11) UNSIGNED AUTO_INCREMENT PRIMARY KEY,
        name VARCHAR(100) NOT NULL,
        description TEXT DEFAULT NULL,
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        UNIQUE KEY name (name)
    )");

    $defaultCategories = ['Running Shoes', 'Court Shoes', 'Field Shoes', 'Gym Shoes', 'Sneakers', 'Hiking Shoes'];
    $insertCategory = $conn->prepare("INSERT IGNORE INTO categories (name) VALUES (?)");
    foreach ($defaultCategories as $defaultCategory) {
        $insertCategory->execute([$defaultCategory]);
    }

    $productsHasCategoryId = $conn->query("SELECT COUNT(*)
        FROM information_schema.COLUMNS
        WHERE TABLE_SCHEMA = DATABASE()
          AND TABLE_NAME = 'products'
          AND COLUMN_NAME = 'category_id'")->fetchColumn();

    if (!$productsHasCategoryId) {
        $conn->exec("ALTER TABLE products ADD COLUMN category_id INT(11) UNSIGNED NULL AFTER name");
    }

    $productsCategoryIndexExists = $conn->query("SELECT COUNT(*)
        FROM information_schema.STATISTICS
        WHERE TABLE_SCHEMA = DATABASE()
          AND TABLE_NAME = 'products'
          AND INDEX_NAME = 'idx_category'")->fetchColumn();

    if (!$productsCategoryIndexExists) {
        $conn->exec("ALTER TABLE products ADD INDEX idx_category (category_id)");
    }

    $productsCategoryFkExists = $conn->query("SELECT CONSTRAINT_NAME
        FROM information_schema.KEY_COLUMN_USAGE
        WHERE TABLE_SCHEMA = DATABASE()
          AND TABLE_NAME = 'products'
          AND COLUMN_NAME = 'category_id'
          AND REFERENCED_TABLE_NAME = 'categories'
        LIMIT 1")->fetchColumn();

    if (!$productsCategoryFkExists) {
        $conn->exec("ALTER TABLE products
            ADD CONSTRAINT fk_products_categories
            FOREIGN KEY (category_id) REFERENCES categories(id)
            ON DELETE SET NULL");
    }

    $productsHasCategory = $conn->query("SELECT COUNT(*)
        FROM information_schema.COLUMNS
        WHERE TABLE_SCHEMA = DATABASE()
          AND TABLE_NAME = 'products'
          AND COLUMN_NAME = 'category'")->fetchColumn();

    if ($productsHasCategory) {
        $conn->exec("UPDATE products p
            LEFT JOIN categories c ON c.name = p.category
            SET p.category_id = c.id
            WHERE p.category_id IS NULL AND p.category IS NOT NULL AND TRIM(p.category) <> ''");
    }

    $conn->exec("CREATE TABLE IF NOT EXISTS product_images (
        id INT(11) UNSIGNED AUTO_INCREMENT PRIMARY KEY,
        product_id INT(11) UNSIGNED NOT NULL,
        image_path VARCHAR(255) NOT NULL,
        sort_order INT(11) NOT NULL DEFAULT 0,
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        UNIQUE KEY product_image_unique (product_id, image_path),
        INDEX idx_product (product_id)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci");

    $productImagesFkExists = $conn->query("SELECT CONSTRAINT_NAME
        FROM information_schema.KEY_COLUMN_USAGE
        WHERE TABLE_SCHEMA = DATABASE()
          AND TABLE_NAME = 'product_images'
          AND COLUMN_NAME = 'product_id'
          AND REFERENCED_TABLE_NAME = 'products'
        LIMIT 1")->fetchColumn();

    if (!$productImagesFkExists) {
        $conn->exec("ALTER TABLE product_images
            ADD CONSTRAINT fk_product_images_product
            FOREIGN KEY (product_id) REFERENCES products(id)
            ON DELETE CASCADE");
    }

    $conn->exec("CREATE TABLE IF NOT EXISTS `cart` (
        `id` int(11) UNSIGNED NOT NULL AUTO_INCREMENT,
        `user_id` int(11) UNSIGNED NOT NULL,
        `product_id` int(11) UNSIGNED NOT NULL,
        `quantity` int(11) NOT NULL DEFAULT 1,
        `added_at` timestamp NOT NULL DEFAULT current_timestamp(),
        PRIMARY KEY (`id`),
        UNIQUE KEY `user_product` (`user_id`, `product_id`)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci");

    $conn->exec("CREATE TABLE IF NOT EXISTS `orders` (
        `id` int(11) UNSIGNED NOT NULL AUTO_INCREMENT,
        `order_code` varchar(50) NOT NULL,
        `user_id` int(11) UNSIGNED NOT NULL,
        `total_amount` decimal(10,2) NOT NULL DEFAULT 0.00,
        `status` varchar(30) NOT NULL DEFAULT 'Pending',
        `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
        PRIMARY KEY (`id`),
        UNIQUE KEY `order_code` (`order_code`)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci");

    $ordersUserFkExists = $conn->query("SELECT CONSTRAINT_NAME
        FROM information_schema.KEY_COLUMN_USAGE
        WHERE TABLE_SCHEMA = DATABASE()
          AND TABLE_NAME = 'orders'
          AND COLUMN_NAME = 'user_id'
          AND REFERENCED_TABLE_NAME = 'login'
        LIMIT 1")->fetchColumn();

    if (!$ordersUserFkExists) {
        $conn->exec("ALTER TABLE `orders`
            ADD CONSTRAINT `fk_orders_user`
            FOREIGN KEY (`user_id`) REFERENCES `login`(`id`)
            ON DELETE CASCADE ON UPDATE CASCADE");
    }

    $conn->exec("CREATE TABLE IF NOT EXISTS `order_items` (
        `id` int(11) UNSIGNED NOT NULL AUTO_INCREMENT,
        `order_id` int(11) UNSIGNED NOT NULL,
        `product_id` int(11) UNSIGNED NOT NULL,
        `quantity` int(11) NOT NULL DEFAULT 1,
        `unit_price` decimal(10,2) NOT NULL DEFAULT 0.00,
        `subtotal` decimal(10,2) NOT NULL DEFAULT 0.00,
        PRIMARY KEY (`id`),
        KEY `order_id` (`order_id`)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci");

    $conn->exec("CREATE TABLE IF NOT EXISTS `user_profiles` (
        `id` int(11) UNSIGNED NOT NULL AUTO_INCREMENT,
        `user_id` int(11) UNSIGNED NOT NULL,
        `contact_number` varchar(30) DEFAULT NULL,
        `profile_image` text DEFAULT NULL,
        `address` text DEFAULT NULL,
        `postal_code` varchar(20) DEFAULT NULL,
        `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
        `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
        PRIMARY KEY (`id`),
        UNIQUE KEY `user_profile_unique` (`user_id`)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci");

    $profileImageColumnExists = $conn->query("SELECT COUNT(*)
        FROM information_schema.COLUMNS
        WHERE TABLE_SCHEMA = DATABASE()
          AND TABLE_NAME = 'user_profiles'
          AND COLUMN_NAME = 'profile_image'")->fetchColumn();

    if (!$profileImageColumnExists) {
        $conn->exec("ALTER TABLE `user_profiles` ADD COLUMN `profile_image` TEXT NULL AFTER `contact_number`");
    }

    $profilesUserFkExists = $conn->query("SELECT CONSTRAINT_NAME
        FROM information_schema.KEY_COLUMN_USAGE
        WHERE TABLE_SCHEMA = DATABASE()
          AND TABLE_NAME = 'user_profiles'
          AND COLUMN_NAME = 'user_id'
          AND REFERENCED_TABLE_NAME = 'login'
        LIMIT 1")->fetchColumn();

    if (!$profilesUserFkExists) {
        $conn->exec("ALTER TABLE `user_profiles`
            ADD CONSTRAINT `fk_user_profiles_user`
            FOREIGN KEY (`user_id`) REFERENCES `login`(`id`)
            ON DELETE CASCADE ON UPDATE CASCADE");
    }

    $conn->exec("ALTER TABLE products MODIFY image TEXT DEFAULT 'assets2/adidasblablabla.png'");

    // Seed product_images from products.image and fallback to the most similar image name when missing.
    $productsStmt = $conn->query("SELECT id, name, image FROM products ORDER BY id ASC");
    $allProducts = $productsStmt->fetchAll(PDO::FETCH_ASSOC);

    $imageCatalog = [];
    foreach ($allProducts as $productRow) {
        $normalizedPath = normalizeImagePathFromProduct($productRow['image'] ?? '');
        if ($normalizedPath === '') {
            continue;
        }

        $imageCatalog[] = [
            'path' => $normalizedPath,
            'name_key' => normalizeMatchKey($productRow['name'] ?? ''),
            'image_key' => normalizeMatchKey(pathinfo($normalizedPath, PATHINFO_FILENAME)),
        ];
    }

    $existingProductImagesStmt = $conn->prepare("SELECT COUNT(*) FROM product_images WHERE product_id = ?");
    $insertProductImageStmt = $conn->prepare("INSERT IGNORE INTO product_images (product_id, image_path, sort_order) VALUES (?, ?, 0)");

    foreach ($allProducts as $productRow) {
        $productId = (int) ($productRow['id'] ?? 0);
        if ($productId <= 0) {
            continue;
        }

        $existingProductImagesStmt->execute([$productId]);
        if ((int) $existingProductImagesStmt->fetchColumn() > 0) {
            continue;
        }

        $selectedPath = normalizeImagePathFromProduct($productRow['image'] ?? '');

        if ($selectedPath === '' && !empty($imageCatalog)) {
            $targetNameKey = normalizeMatchKey($productRow['name'] ?? '');
            $bestScore = 0;

            foreach ($imageCatalog as $candidate) {
                $score = 0;
                similar_text($targetNameKey, $candidate['name_key'], $namePercent);
                similar_text($targetNameKey, $candidate['image_key'], $imagePercent);
                $score = (int) round(max($namePercent, $imagePercent));

                if ($score > $bestScore) {
                    $bestScore = $score;
                    $selectedPath = $candidate['path'];
                }
            }

            if ($bestScore < 35) {
                $selectedPath = '';
            }
        }

        if ($selectedPath !== '') {
            $insertProductImageStmt->execute([$productId, $selectedPath]);
        }
    }

    $data = [
        ['superadmin', 'superadmin@gmail.com', md5('SuperPassword123'), 'superadmin'],
        ['admin', 'admin@gmail.com', md5('admin123'), 'admin'],
        ['User', 'user@gmail.com', md5('12345678'), 'user'],
    ];

    $query_i = $conn->prepare("INSERT INTO login (
        username,
        email,
        password,
        role
    ) VALUES (?,?,?,?)");

    $check_query = $conn->prepare("SELECT id FROM login WHERE username = ? OR email = ? LIMIT 1");

    $conn->beginTransaction();
    foreach ($data as $row) {
        $check_query->execute([$row[0], $row[1]]);

        if ($check_query->fetchColumn()) {
            continue;
        }

        $query_i->execute($row);
    }

    // Ensure every login has a matching profile row on first run and subsequent boots.
    $conn->exec("INSERT IGNORE INTO user_profiles (user_id, profile_image)
        SELECT l.id, NULLIF(TRIM(COALESCE(l.img_url, '')), '')
        FROM login l");

    // Backfill profile_image from login for older rows that still have empty profile images.
    $conn->exec("UPDATE user_profiles up
        INNER JOIN login l ON l.id = up.user_id
        SET up.profile_image = l.img_url
        WHERE (up.profile_image IS NULL OR TRIM(up.profile_image) = '')
          AND l.img_url IS NOT NULL
          AND TRIM(l.img_url) <> ''");

    $conn->commit();
} catch (PDOException $e) {
    if (isset($conn) && $conn->inTransaction()) {
        $conn->rollBack();
    }

    die("Connection setup failed: " . $e->getMessage());
}