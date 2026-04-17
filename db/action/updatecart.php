<?php
session_start();
header('Content-Type: application/json');
if (!isset($_SESSION["username"])) {
  echo json_encode(['success' => false]);
  exit;
}

$key = $_POST['key'] ?? '';
$delta = (int)($_POST['delta'] ?? 0);
if (empty($key) || $delta == 0) {
  echo json_encode(['success' => false]);
  exit;
}

if (isset($_SESSION['cart'][$key])) {
  $_SESSION['cart'][$key]['qty'] += $delta;
  if ($_SESSION['cart'][$key]['qty'] <= 0) {
    unset($_SESSION['cart'][$key]);
  }
  echo json_encode(['success' => true]);
} else {
  echo json_encode(['success' => false]);
}
?>
