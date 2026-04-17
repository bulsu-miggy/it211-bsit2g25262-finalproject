<?php
session_start();
if (!isset($_SESSION["username"])) {
  http_response_code(401);
  exit('Unauthorized');
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
  exit('Invalid method');
}

$action = $_POST['action'] ?? '';

if (empty($action)) {
  http_response_code(400);
  exit('No action specified');
}

include 'connect.php';
$username = $_SESSION["username"];

// Handle different address operations
if ($action === 'add') {
  // Add address
  $street = $_POST['street'] ?? '';
  $city = $_POST['city'] ?? '';
  $state = $_POST['state'] ?? '';
  $zip = $_POST['zip'] ?? '';
  $country = $_POST['country'] ?? '';

  // Get user_id
  $stmt_user = mysqli_prepare($con, "SELECT id FROM login WHERE username = ?");
  mysqli_stmt_bind_param($stmt_user, "s", $username);
  mysqli_stmt_execute($stmt_user);
  $result = mysqli_stmt_get_result($stmt_user);
  $user = mysqli_fetch_assoc($result);
  $user_id = $user['id'] ?? 0;
  mysqli_stmt_close($stmt_user);

  // Insert address
  $sql = "INSERT INTO addresses (user_id, street, city, state, zip, country) VALUES (?, ?, ?, ?, ?, ?)";
  $stmt = mysqli_prepare($con, $sql);
  mysqli_stmt_bind_param($stmt, "isssss", $user_id, $street, $city, $state, $zip, $country);
  $success = mysqli_stmt_execute($stmt);
  mysqli_stmt_close($stmt);

  header('Content-Type: application/json');
  echo json_encode(['success' => $success]);

} else if ($action === 'edit') {
  // Edit address (update to login table for single address storage)
  $postal = $_POST['postal'] ?? '';
  $state = $_POST['state'] ?? '';
  $city = $_POST['city'] ?? '';
  $barangay = $_POST['barangay'] ?? '';
  $street = $_POST['street'] ?? '';

  try {
    // Store complete address in the login table 
    $sql = "UPDATE login SET street = ?, postal_code = ?, state = ?, city = ?, barangay = ? WHERE username = ?";
    $stmt = $conn->prepare($sql);
    $success = $stmt->execute([$street, $postal, $state, $city, $barangay, $username]);
    
    header('Content-Type: application/json');
    echo json_encode(['success' => true, 'message' => 'Address updated successfully']);
  } catch (PDOException $e) {
    header('Content-Type: application/json');
    echo json_encode(['success' => false, 'error' => $e->getMessage()]);
  }

} else if ($action === 'delete') {
  // Delete address
  $address_id = (int)($_POST['address_id'] ?? 0);

  // Verify user owns address
  $stmt_check = mysqli_prepare($con, "SELECT a.id FROM addresses a JOIN login l ON a.user_id = l.id WHERE l.username = ? AND a.id = ?");
  mysqli_stmt_bind_param($stmt_check, "si", $username, $address_id);
  mysqli_stmt_execute($stmt_check);
  $result = mysqli_stmt_get_result($stmt_check);
  if (!mysqli_fetch_assoc($result)) {
    http_response_code(403);
    mysqli_stmt_close($stmt_check);
    mysqli_close($con);
    exit('Forbidden');
  }
  mysqli_stmt_close($stmt_check);

  // Delete
  $sql = "DELETE FROM addresses WHERE id = ?";
  $stmt = mysqli_prepare($con, $sql);
  mysqli_stmt_bind_param($stmt, "i", $address_id);
  $success = mysqli_stmt_execute($stmt);
  mysqli_stmt_close($stmt);

  header('Content-Type: application/json');
  echo json_encode(['success' => $success]);

} else {
  http_response_code(400);
  header('Content-Type: application/json');
  echo json_encode(['success' => false, 'message' => 'Invalid action']);
}

mysqli_close($con);
?>
