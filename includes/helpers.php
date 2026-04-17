<?php
/**
 * UniMerch — Helper Functions
 * Shared utility functions used across the platform
 */

/**
 * Send a JSON response and exit
 */
function jsonResponse(array $data, int $code = 200): void {
    http_response_code($code);
    header('Content-Type: application/json; charset=utf-8');
    echo json_encode($data, JSON_UNESCAPED_UNICODE);
    exit;
}

/**
 * Sanitize user input
 */
function sanitize(string $input): string {
    return htmlspecialchars(trim($input), ENT_QUOTES, 'UTF-8');
}

/**
 * Generate a unique order number: UM-YYYYMMDD-XXX
 */
function generateOrderNumber(PDO $pdo): string {
    $date = date('Ymd');
    $prefix = ORDER_PREFIX . '-' . $date . '-';

    $stmt = $pdo->prepare("SELECT order_number FROM orders WHERE order_number LIKE ? ORDER BY id DESC LIMIT 1");
    $stmt->execute([$prefix . '%']);
    $last = $stmt->fetchColumn();

    if ($last) {
        $lastNum = (int) substr($last, -3);
        $nextNum = str_pad($lastNum + 1, 3, '0', STR_PAD_LEFT);
    } else {
        $nextNum = '001';
    }

    return $prefix . $nextNum;
}

/**
 * Handle product image upload
 * Returns filename on success, null on failure
 */
function uploadImage(array $file): ?string {
    if ($file['error'] !== UPLOAD_ERR_OK) {
        return null;
    }

    if ($file['size'] > MAX_IMAGE_SIZE) {
        return null;
    }

    $mime = mime_content_type($file['tmp_name']);
    if (!in_array($mime, ALLOWED_IMAGE_TYPES)) {
        return null;
    }

    // Create uploads directory if it doesn't exist
    if (!is_dir(UPLOAD_DIR)) {
        mkdir(UPLOAD_DIR, 0755, true);
    }

    // Generate unique filename
    $ext = pathinfo($file['name'], PATHINFO_EXTENSION);
    $filename = 'product_' . uniqid() . '_' . time() . '.' . $ext;
    $destination = UPLOAD_DIR . $filename;

    if (move_uploaded_file($file['tmp_name'], $destination)) {
        return $filename;
    }

    return null;
}

/**
 * Generate a random OTP code
 */
function generateOTP(int $length = OTP_LENGTH): string {
    return str_pad((string) random_int(0, pow(10, $length) - 1), $length, '0', STR_PAD_LEFT);
}

/**
 * Get the request method
 */
function getRequestMethod(): string {
    return $_SERVER['REQUEST_METHOD'];
}

/**
 * Get JSON request body
 */
function getJsonBody(): array {
    $body = file_get_contents('php://input');
    $data = json_decode($body, true);
    return is_array($data) ? $data : [];
}

/**
 * Validate required fields exist in data
 */
function validateRequired(array $data, array $fields): ?string {
    foreach ($fields as $field) {
        if (!isset($data[$field]) || (is_string($data[$field]) && trim($data[$field]) === '')) {
            return "Field '{$field}' is required.";
        }
    }
    return null;
}

/**
 * Get current customer from session
 */
function getCurrentCustomer(): ?array {
    if (session_status() === PHP_SESSION_NONE) {
        session_start();
    }
    return $_SESSION['customer'] ?? null;
}

/**
 * Format price to Philippine Peso
 */
function formatPrice(float $amount): string {
    return '₱' . number_format($amount, 2);
}

/**
 * Get relative time string (e.g., "2 hours ago")
 */
function timeAgo(string $datetime): string {
    $now = new DateTime();
    $past = new DateTime($datetime);
    $diff = $now->diff($past);

    if ($diff->y > 0) return $diff->y . ' year' . ($diff->y > 1 ? 's' : '') . ' ago';
    if ($diff->m > 0) return $diff->m . ' month' . ($diff->m > 1 ? 's' : '') . ' ago';
    if ($diff->d > 0) return $diff->d . ' day' . ($diff->d > 1 ? 's' : '') . ' ago';
    if ($diff->h > 0) return $diff->h . ' hour' . ($diff->h > 1 ? 's' : '') . ' ago';
    if ($diff->i > 0) return $diff->i . ' minute' . ($diff->i > 1 ? 's' : '') . ' ago';
    return 'Just now';
}
