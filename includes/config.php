<?php
/**
 * UniMerch — Application Configuration
 * Central configuration constants for the UniMerch platform
 */

// Error reporting (disable in production)
error_reporting(E_ALL);
ini_set('display_errors', 0);
ini_set('log_errors', 1);

// Application
define('APP_NAME', 'UniMerch');
define('APP_TAGLINE', 'Official Campus Merchandise');
define('APP_VERSION', '1.0.0');
define('BASE_URL', '/unimerch');
define('FULL_URL', 'http://localhost' . BASE_URL);

// Paths
define('ROOT_PATH', dirname(__DIR__));
define('UPLOAD_DIR', ROOT_PATH . '/uploads/');
define('UPLOAD_URL', BASE_URL . '/uploads/');

// Upload limits
define('MAX_IMAGE_SIZE', 5 * 1024 * 1024); // 5MB
define('ALLOWED_IMAGE_TYPES', ['image/jpeg', 'image/png', 'image/webp', 'image/gif']);

// Pagination
define('PRODUCTS_PER_PAGE', 12);
define('ORDERS_PER_PAGE', 15);

// Session
define('SESSION_LIFETIME', 3600 * 24); // 24 hours

// OTP
define('OTP_LENGTH', 6);
define('OTP_EXPIRY_MINUTES', 10);

// Order number prefix
define('ORDER_PREFIX', 'UM');

// Timezone
date_default_timezone_set('Asia/Manila');
