<?php
/**
 * UniMerch Admin API — Authentication
 */
require_once __DIR__ . '/../../includes/db.php';
require_once __DIR__ . '/../../includes/helpers.php';
require_once __DIR__ . '/../../includes/auth.php';

header('Content-Type: application/json; charset=utf-8');

if (getRequestMethod() !== 'POST') {
    jsonResponse(['success' => false, 'message' => 'Method not allowed'], 405);
}

$data = getJsonBody();
$username = trim($data['username'] ?? '');
$password = $data['password'] ?? '';

if (!$username || !$password) {
    jsonResponse(['success' => false, 'message' => 'Username and password are required'], 400);
}

$stmt = db()->prepare("SELECT * FROM merchants WHERE username = ?");
$stmt->execute([$username]);
$merchant = $stmt->fetch();

if (!$merchant || !password_verify($password, $merchant['password'])) {
    jsonResponse(['success' => false, 'message' => 'Invalid credentials'], 401);
}

$_SESSION['merchant_id']       = $merchant['id'];
$_SESSION['merchant_username'] = $merchant['username'];
$_SESSION['merchant_name']     = $merchant['full_name'];

jsonResponse([
    'success'  => true,
    'message'  => 'Welcome, ' . $merchant['full_name'] . '!',
    'redirect' => BASE_URL . '/admin/'
]);
