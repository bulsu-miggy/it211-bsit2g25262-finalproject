-- ============================================================
-- UniMerch Database Schema
-- Bulacan State University Campus Merchandise System
-- Generated: 2026-04-13
-- ============================================================

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
SET time_zone = "+08:00";

CREATE DATABASE IF NOT EXISTS `unimerch_db`
  DEFAULT CHARACTER SET utf8mb4
  COLLATE utf8mb4_unicode_ci;

USE `unimerch_db`;

-- ============================================================
-- 1. CATEGORIES (BulSU College Codes)
-- ============================================================
CREATE TABLE `categories` (
  `id` INT AUTO_INCREMENT PRIMARY KEY,
  `name` VARCHAR(100) NOT NULL,
  `code` VARCHAR(10) NOT NULL UNIQUE,
  `description` TEXT,
  `icon` VARCHAR(50) DEFAULT 'bi-mortarboard',
  `color` VARCHAR(7) DEFAULT '#1e40af',
  `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ============================================================
-- 2. PRODUCTS
-- ============================================================
CREATE TABLE `products` (
  `id` INT AUTO_INCREMENT PRIMARY KEY,
  `category_id` INT NOT NULL,
  `name` VARCHAR(255) NOT NULL,
  `description` TEXT,
  `price` DECIMAL(10,2) NOT NULL,
  `stock` INT NOT NULL DEFAULT 0,
  `image` VARCHAR(255) DEFAULT 'default-product.jpg',
  `sizes` JSON DEFAULT NULL,
  `status` ENUM('active','inactive') DEFAULT 'active',
  `featured` TINYINT(1) DEFAULT 0,
  `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  `updated_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  FOREIGN KEY (`category_id`) REFERENCES `categories`(`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE INDEX `idx_products_category` ON `products`(`category_id`);
CREATE INDEX `idx_products_status` ON `products`(`status`);
CREATE INDEX `idx_products_featured` ON `products`(`featured`);

-- ============================================================
-- 3. MERCHANTS (Admin Accounts)
-- ============================================================
CREATE TABLE `merchants` (
  `id` INT AUTO_INCREMENT PRIMARY KEY,
  `username` VARCHAR(50) NOT NULL UNIQUE,
  `password` VARCHAR(255) NOT NULL,
  `full_name` VARCHAR(100) NOT NULL,
  `email` VARCHAR(100),
  `avatar` VARCHAR(255) DEFAULT NULL,
  `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ============================================================
-- 4. CUSTOMERS (with OTP verification)
-- ============================================================
CREATE TABLE `customers` (
  `id` INT AUTO_INCREMENT PRIMARY KEY,
  `first_name` VARCHAR(50) NOT NULL,
  `last_name` VARCHAR(50) NOT NULL,
  `email` VARCHAR(100) NOT NULL UNIQUE,
  `phone` VARCHAR(20),
  `password` VARCHAR(255) NOT NULL,
  `otp_code` VARCHAR(6) DEFAULT NULL,
  `otp_expires` DATETIME DEFAULT NULL,
  `is_verified` TINYINT(1) DEFAULT 0,
  `avatar` VARCHAR(255) DEFAULT NULL,
  `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  `updated_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ============================================================
-- 5. ORDERS
-- ============================================================
CREATE TABLE `orders` (
  `id` INT AUTO_INCREMENT PRIMARY KEY,
  `order_number` VARCHAR(20) NOT NULL UNIQUE,
  `customer_id` INT DEFAULT NULL,
  `customer_name` VARCHAR(100) NOT NULL,
  `customer_email` VARCHAR(100) NOT NULL,
  `customer_phone` VARCHAR(20),
  `total_amount` DECIMAL(10,2) NOT NULL,
  `status` ENUM('pending','confirmed','processing','ready','completed','cancelled') DEFAULT 'pending',
  `payment_method` ENUM('cash','gcash','bank_transfer') DEFAULT 'cash',
  `payment_proof` VARCHAR(255) DEFAULT NULL,
  `payment_status` ENUM('unpaid','pending_verification','paid') DEFAULT 'unpaid',
  `notes` TEXT,
  `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  `updated_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  FOREIGN KEY (`customer_id`) REFERENCES `customers`(`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE INDEX `idx_orders_status` ON `orders`(`status`);
CREATE INDEX `idx_orders_customer` ON `orders`(`customer_id`);
CREATE INDEX `idx_orders_date` ON `orders`(`created_at`);

-- ============================================================
-- 6. ORDER ITEMS
-- ============================================================
CREATE TABLE `order_items` (
  `id` INT AUTO_INCREMENT PRIMARY KEY,
  `order_id` INT NOT NULL,
  `product_id` INT NOT NULL,
  `quantity` INT NOT NULL DEFAULT 1,
  `size` VARCHAR(10) DEFAULT NULL,
  `price` DECIMAL(10,2) NOT NULL,
  FOREIGN KEY (`order_id`) REFERENCES `orders`(`id`) ON DELETE CASCADE,
  FOREIGN KEY (`product_id`) REFERENCES `products`(`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ============================================================
-- 7. CART (Session-based)
-- ============================================================
CREATE TABLE `cart` (
  `id` INT AUTO_INCREMENT PRIMARY KEY,
  `session_id` VARCHAR(128) NOT NULL,
  `customer_id` INT DEFAULT NULL,
  `product_id` INT NOT NULL,
  `quantity` INT NOT NULL DEFAULT 1,
  `size` VARCHAR(10) DEFAULT NULL,
  `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  FOREIGN KEY (`product_id`) REFERENCES `products`(`id`) ON DELETE CASCADE,
  FOREIGN KEY (`customer_id`) REFERENCES `customers`(`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE INDEX `idx_cart_session` ON `cart`(`session_id`);
CREATE INDEX `idx_cart_customer` ON `cart`(`customer_id`);

-- ============================================================
-- SEED DATA
-- ============================================================

-- Categories (BulSU Colleges)
INSERT INTO `categories` (`name`, `code`, `description`, `icon`, `color`) VALUES
('College of Information and Communications Technology', 'CICT', 'Tech-forward merch for the builders of tomorrow.', 'bi-cpu', '#3b82f6'),
('College of Architecture and Fine Arts', 'CAFA', 'Wear the art. Live the design.', 'bi-palette', '#ec4899'),
('College of Engineering', 'COE', 'Engineered for excellence, built with pride.', 'bi-gear', '#f59e0b'),
('College of Business Administration', 'CBA', 'Dress for the boardroom and the campus.', 'bi-briefcase', '#10b981'),
('College of Education', 'COED', 'Inspire the future, one thread at a time.', 'bi-book', '#8b5cf6'),
('College of Science', 'COS', 'Science never looked this good.', 'bi-rocket-takeoff', '#06b6d4');

-- Default Merchant Account (password: unimerch2026)
INSERT INTO `merchants` (`username`, `password`, `full_name`, `email`) VALUES
('admin', '$2y$12$S2vlZamiWVSANxsE6L.tpuD20Q.6wPPWPGHyLoSLPt9Rr5DtnwqTS', 'UniMerch Admin', 'admin@unimerch.bulsu.edu.ph');

-- Products: CICT (category_id = 1)
INSERT INTO `products` (`category_id`, `name`, `description`, `price`, `stock`, `image`, `sizes`, `featured`) VALUES
(1, 'CICT Official Tee â€” Binary Edition', 'Premium cotton tee with the CICT crest and binary-art pattern on the back. Tagline: "while(alive) { code(); }"', 450.00, 120, 'cict-tee-binary.jpg', '["XS","S","M","L","XL","2XL"]', 1),
(1, 'CICT Hoodie â€” Dark Mode', 'Heavyweight pullover hoodie in matte black with reflective CICT emblem on the chest and a subtle circuit-board pattern on the sleeves.', 850.00, 60, 'cict-hoodie-dark.jpg', '["S","M","L","XL","2XL"]', 1),
(1, 'CICT Lanyard â€” USB-C Keychain', 'Polyester lanyard with detachable USB-C keychain and CICT branding. Perfect for your ID and flash drives.', 150.00, 200, 'cict-lanyard.jpg', NULL, 0),

-- Products: CAFA (category_id = 2)
(2, 'CAFA Artist Tee â€” Brushstroke', 'Off-white tee with hand-painted brushstroke print and CAFA wordmark. Every shirt has a slightly unique pattern.', 480.00, 80, 'cafa-tee-brush.jpg', '["XS","S","M","L","XL"]', 1),
(2, 'CAFA Tote Bag â€” Canvas Blueprint', 'Heavy-duty canvas tote with architectural blueprint print. Internal pocket for pencils and tools.', 320.00, 100, 'cafa-tote.jpg', NULL, 0),
(2, 'CAFA Beanie â€” Concrete Gray', 'Knitted beanie in industrial gray with embroidered CAFA patch. One size fits most.', 280.00, 90, 'cafa-beanie.jpg', NULL, 0),

-- Products: COE (category_id = 3)
(3, 'COE Varsity Jacket â€” Steel Edition', 'Wool-blend varsity jacket with faux leather sleeves. Features COE emblem on the chest and "ENGINEER" chenille patch on the back.', 1200.00, 40, 'coe-varsity.jpg', '["S","M","L","XL","2XL"]', 1),
(3, 'COE Dry-Fit Polo â€” Graphite', 'Moisture-wicking polo shirt in graphite gray. Embroidered COE logo on the chest. Perfect for lab days.', 520.00, 75, 'coe-polo.jpg', '["S","M","L","XL"]', 0),
(3, 'COE Hard Hat Sticker Pack', 'Set of 12 vinyl stickers featuring COE slogans, engineering symbols, and BulSU pride marks. Weatherproof.', 120.00, 300, 'coe-stickers.jpg', NULL, 0),

-- Products: CBA (category_id = 4)
(4, 'CBA Executive Polo â€” Navy Pinstripe', 'Structured polo with subtle pinstripe pattern. CBA crest embroidered in gold thread. Boardroom-ready.', 550.00, 65, 'cba-polo.jpg', '["S","M","L","XL","2XL"]', 1),
(4, 'CBA Notebook â€” Leather Bound', 'A5 faux-leather notebook with gold-foil CBA seal. 200 pages of premium ivory paper. Includes ribbon bookmark.', 380.00, 150, 'cba-notebook.jpg', NULL, 0),
(4, 'CBA Tumbler â€” Gold Standard', '20oz stainless steel tumbler in matte black with gold CBA branding. Double-walled, keeps drinks cold for 12 hours.', 420.00, 100, 'cba-tumbler.jpg', NULL, 0),

-- Products: COED (category_id = 5)
(5, 'COED Inspire Tee â€” Chalk Edition', 'Soft cotton tee with chalkboard-style typography reading "TEACH. INSPIRE. REPEAT." COED logo on the sleeve.', 430.00, 90, 'coed-tee-chalk.jpg', '["XS","S","M","L","XL"]', 0),
(5, 'COED Hoodie â€” Apple Red', 'Cozy pullover hoodie in apple red. Features a minimalist apple + book graphic on the front with COED text.', 820.00, 50, 'coed-hoodie.jpg', '["S","M","L","XL","2XL"]', 1),
(5, 'COED Enamel Pin Set â€” Scholar Series', 'Set of 4 enamel pins: book, globe, lightbulb, and COED shield. Comes in a velvet display case.', 250.00, 120, 'coed-pins.jpg', NULL, 0),

-- Products: COS (category_id = 6)
(6, 'COS Lab Coat Tee â€” Periodic Style', 'White tee with periodic table element tiles spelling "BulSU COS". Glow-in-the-dark ink on back print.', 460.00, 85, 'cos-tee-periodic.jpg', '["XS","S","M","L","XL"]', 0),
(6, 'COS Windbreaker â€” Quantum Blue', 'Lightweight windbreaker in gradient blue (light to dark, top to bottom). Reflective COS logo. Water-resistant.', 780.00, 55, 'cos-windbreaker.jpg', '["S","M","L","XL"]', 1),
(6, 'COS Flask â€” Erlenmeyer Edition', 'Borosilicate glass water bottle shaped like an Erlenmeyer flask. Comes with silicone sleeve in COS teal. 500ml.', 350.00, 80, 'cos-flask.jpg', NULL, 0);

-- Demo Orders (for analytics)
INSERT INTO `orders` (`order_number`, `customer_name`, `customer_email`, `customer_phone`, `total_amount`, `status`, `payment_method`, `payment_status`, `created_at`) VALUES
('UM-20260410-001', 'Juan Dela Cruz', 'juan@bulsu.edu.ph', '09171234567', 1300.00, 'completed', 'gcash', 'paid', '2026-04-10 09:30:00'),
('UM-20260410-002', 'Maria Santos', 'maria@bulsu.edu.ph', '09189876543', 850.00, 'completed', 'cash', 'paid', '2026-04-10 14:15:00'),
('UM-20260411-001', 'Pedro Reyes', 'pedro@bulsu.edu.ph', '09201112233', 930.00, 'processing', 'gcash', 'paid', '2026-04-11 10:45:00'),
('UM-20260411-002', 'Ana Gonzales', 'ana@bulsu.edu.ph', '09165554433', 480.00, 'confirmed', 'cash', 'paid', '2026-04-11 16:00:00'),
('UM-20260412-001', 'Carlos Mendoza', 'carlos@bulsu.edu.ph', '09223334455', 1650.00, 'pending', 'gcash', 'pending_verification', '2026-04-12 08:20:00'),
('UM-20260412-002', 'Sofia Ramos', 'sofia@bulsu.edu.ph', '09187776655', 600.00, 'pending', 'bank_transfer', 'unpaid', '2026-04-12 13:50:00'),
('UM-20260413-001', 'Miguel Torres', 'miguel@bulsu.edu.ph', '09156667788', 420.00, 'pending', 'cash', 'unpaid', '2026-04-13 07:10:00');

-- Demo Order Items
INSERT INTO `order_items` (`order_id`, `product_id`, `quantity`, `size`, `price`) VALUES
(1, 1, 1, 'L', 450.00), (1, 3, 1, NULL, 150.00), (1, 2, 1, 'M', 850.00),
(2, 2, 1, 'L', 850.00),
(3, 4, 1, 'M', 480.00), (3, 1, 1, 'XL', 450.00),
(4, 4, 1, 'S', 480.00),
(5, 7, 1, 'L', 1200.00), (5, 1, 1, 'M', 450.00),
(6, 5, 1, NULL, 320.00), (6, 6, 1, NULL, 280.00),
(7, 12, 1, NULL, 420.00);


