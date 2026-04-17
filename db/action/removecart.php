<?php
session_start();
header('Content-Type: application/json');
if (!isset($_SESSION["username"])) {
  echo json_encode(['success' => false, 'message' => 'Not logged in']);
  exit;
}

$key = $_POST['key'] ?? '';
if (empty($key)) {
  echo json_encode(['success' => false, 'message' => 'No key']);
  exit;
}

if (isset($_SESSION['cart'][$key])) {
  unset($_SESSION['cart'][$key]);
  echo json_encode(['success' => true, 'message' => 'Removed from cart']);
} else {
  echo json_encode(['success' => false, 'message' => 'Item not found']);
}
?>
