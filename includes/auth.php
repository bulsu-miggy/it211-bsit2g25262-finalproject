<?php
/**
 * UniMerch — Authentication Middleware
 * Session-based auth guards for merchant and customer areas
 */

require_once __DIR__ . '/config.php';

if (session_status() === PHP_SESSION_NONE) {
    session_set_cookie_params([
        'lifetime' => SESSION_LIFETIME,
        'path'     => BASE_URL,
        'httponly'  => true,
        'samesite'  => 'Lax'
    ]);
    session_start();
}

/**
 * Require merchant authentication — redirects to login if not authenticated
 */
function requireMerchantAuth(): void {
    if (!isMerchantLoggedIn()) {
        header('Location: ' . BASE_URL . '/admin/login.php');
        exit;
    }
}

/**
 * Check if a merchant is currently logged in
 */
function isMerchantLoggedIn(): bool {
    return isset($_SESSION['merchant_id']) && !empty($_SESSION['merchant_id']);
}

/**
 * Get current merchant data
 */
function getMerchant(): ?array {
    if (!isMerchantLoggedIn()) return null;
    return [
        'id'        => $_SESSION['merchant_id'],
        'username'  => $_SESSION['merchant_username'] ?? '',
        'full_name' => $_SESSION['merchant_name'] ?? '',
    ];
}

/**
 * Require customer authentication — redirects to login if not authenticated
 */
function requireCustomerAuth(): void {
    if (!isCustomerLoggedIn()) {
        header('Location: ' . BASE_URL . '/login.php');
        exit;
    }
}

/**
 * Check if a customer is currently logged in
 */
function isCustomerLoggedIn(): bool {
    return isset($_SESSION['customer_id']) && !empty($_SESSION['customer_id']);
}

/**
 * Get current customer data from session
 */
function getCustomer(): ?array {
    if (!isCustomerLoggedIn()) return null;
    return [
        'id'         => $_SESSION['customer_id'],
        'first_name' => $_SESSION['customer_first_name'] ?? '',
        'last_name'  => $_SESSION['customer_last_name'] ?? '',
        'email'      => $_SESSION['customer_email'] ?? '',
        'phone'      => $_SESSION['customer_phone'] ?? '',
    ];
}

/**
 * Require merchant auth for API endpoints — returns JSON error instead of redirect
 */
function requireMerchantAuthAPI(): void {
    if (!isMerchantLoggedIn()) {
        http_response_code(401);
        header('Content-Type: application/json');
        echo json_encode(['success' => false, 'message' => 'Unauthorized. Please log in.']);
        exit;
    }
}

/**
 * Require customer auth for API endpoints
 */
function requireCustomerAuthAPI(): void {
    if (!isCustomerLoggedIn()) {
        http_response_code(401);
        header('Content-Type: application/json');
        echo json_encode(['success' => false, 'message' => 'Please log in to continue.']);
        exit;
    }
}
