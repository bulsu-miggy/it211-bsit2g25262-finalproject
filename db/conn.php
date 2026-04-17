<?php
class Database {
    private $host = "localhost";
    private $db_name = "lasafilipina";
    private $username = "root";
    private $password = "";
    private $conn;

    public function getConnection() {
        $this->conn = null;
        try {
            $this->conn = new PDO(
                "mysql:host=" . $this->host . ";dbname=" . $this->db_name,
                $this->username,
                $this->password
            );
            $this->conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
            $this->conn->exec("SET NAMES utf8");
            $this->initSchema();
        } catch(PDOException $exception) {
            die("Connection failed: " . $exception->getMessage());
        }
        return $this->conn;
    }

    private function initSchema() {
        $this->conn->exec(
            "CREATE TABLE IF NOT EXISTS users (
                id INT AUTO_INCREMENT PRIMARY KEY,
                full_name VARCHAR(255) NOT NULL,
                email VARCHAR(255) NOT NULL UNIQUE,
                password_hash VARCHAR(255) NOT NULL,
                role VARCHAR(50) NOT NULL DEFAULT 'customer',
                address TEXT DEFAULT NULL,
                phone VARCHAR(20) DEFAULT NULL,
                created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8"
        );

        // Categories table
        $this->conn->exec("CREATE TABLE IF NOT EXISTS categories (
            id INT AUTO_INCREMENT PRIMARY KEY,
            name VARCHAR(100) NOT NULL,
            slug VARCHAR(100) NOT NULL UNIQUE,
            description TEXT,
            parent_id INT DEFAULT NULL,
            meta_title VARCHAR(60),
            meta_description VARCHAR(160),
            icon VARCHAR(255),
            is_active BOOLEAN DEFAULT TRUE,
            is_featured BOOLEAN DEFAULT FALSE,
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8");

        $this->conn->exec(
            "CREATE TABLE IF NOT EXISTS products (
                id INT AUTO_INCREMENT PRIMARY KEY,
                name VARCHAR(255) NOT NULL,
                slug VARCHAR(255) NOT NULL UNIQUE,
                description TEXT,
                price DECIMAL(10,2) NOT NULL,
                sku VARCHAR(50) UNIQUE,
                category_id INT,
                stock_quantity INT DEFAULT 0,
                brand VARCHAR(255) DEFAULT NULL,
                image_emoji VARCHAR(10) DEFAULT NULL,
                image VARCHAR(255) DEFAULT NULL,
                length_cm DECIMAL(8,2),
                width_cm DECIMAL(8,2),
                height_cm DECIMAL(8,2),
                tags VARCHAR(255),
                is_active BOOLEAN DEFAULT TRUE,
                created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
                updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8"
        );

        $this->conn->exec(
            "CREATE TABLE IF NOT EXISTS cart_items (
                session_id VARCHAR(255) NOT NULL,
                product_id INT NOT NULL,
                quantity INT NOT NULL DEFAULT 1,
                PRIMARY KEY(session_id, product_id),
                INDEX(product_id),
                CONSTRAINT fk_cart_product FOREIGN KEY (product_id) REFERENCES products(id) ON DELETE CASCADE
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8"
        );

        $this->conn->exec(
            "CREATE TABLE IF NOT EXISTS orders (
                id INT AUTO_INCREMENT PRIMARY KEY,
                order_number VARCHAR(50) NOT NULL UNIQUE,
                user_id INT DEFAULT NULL,
                session_id VARCHAR(255) NOT NULL,
                subtotal DECIMAL(10,2) NOT NULL DEFAULT 0.00,
                delivery_fee DECIMAL(10,2) NOT NULL DEFAULT 0.00,
                total DECIMAL(10,2) NOT NULL DEFAULT 0.00,
                payment_mode VARCHAR(50) DEFAULT NULL,
                location TEXT DEFAULT NULL,
                status VARCHAR(50) NOT NULL DEFAULT 'pending',
                created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8"
        );

        $this->conn->exec(
            "CREATE TABLE IF NOT EXISTS order_items (
                id INT AUTO_INCREMENT PRIMARY KEY,
                order_id INT NOT NULL,
                product_id INT NOT NULL,
                product_name VARCHAR(255) NOT NULL,
                product_price DECIMAL(10,2) NOT NULL DEFAULT 0.00,
                quantity INT NOT NULL DEFAULT 1,
                subtotal DECIMAL(10,2) NOT NULL DEFAULT 0.00,
                CONSTRAINT fk_order_items_order FOREIGN KEY (order_id) REFERENCES orders(id) ON DELETE CASCADE,
                CONSTRAINT fk_order_items_product FOREIGN KEY (product_id) REFERENCES products(id) ON DELETE CASCADE
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8"
        );

        $this->ensureColumn('products', 'image', 'VARCHAR(255) DEFAULT NULL');
        $this->ensureColumn('products', 'image_emoji', 'VARCHAR(10) DEFAULT NULL');
        $this->ensureColumn('orders', 'payment_mode', 'VARCHAR(50) DEFAULT NULL');
        $this->ensureColumn('orders', 'payment_details', 'TEXT DEFAULT NULL');
        $this->ensureColumn('orders', 'location', 'TEXT DEFAULT NULL');
        $this->ensureColumn('orders', 'user_id', 'INT DEFAULT NULL');
        $this->ensureColumn('users', 'address', 'TEXT DEFAULT NULL');
        $this->ensureColumn('users', 'phone', 'VARCHAR(20) DEFAULT NULL');
    }

    private function ensureColumn($table, $column, $definition) {
        if (!$this->columnExists($table, $column)) {
            $this->conn->exec("ALTER TABLE `{$table}` ADD COLUMN `{$column}` {$definition}");
        }
    }

    private function columnExists($table, $column) {
        $stmt = $this->conn->prepare("SHOW COLUMNS FROM `{$table}` LIKE :column");
        $stmt->bindParam(':column', $column);
        $stmt->execute();
        return (bool) $stmt->fetch();
    }
}

?>