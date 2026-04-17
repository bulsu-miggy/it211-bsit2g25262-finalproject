<?php
session_start();
if (!isset($_SESSION["username"])) {
  http_response_code(401);
  exit('Unauthorized');
}

header('Content-Type: application/json');
include 'connect.php';
$username = $_SESSION["username"];

if ($_SERVER['REQUEST_METHOD'] === 'GET') {
  // Fetch profile data
  try {
    $sql = "SELECT * FROM login WHERE username = ?";
    $stmt = $conn->prepare($sql);
    $stmt->execute([$username]);
    $user = $stmt->fetch(PDO::FETCH_ASSOC);
    echo json_encode($user ?: []);
  } catch (PDOException $e) {
    echo json_encode(['error' => $e->getMessage()]);
  }
} else if ($_SERVER['REQUEST_METHOD'] === 'POST') {
  // Update profile
  if ($_POST['action'] ?? '' === 'update') {
    $newUsername = $_POST['username'] ?? '';
    $firstName = $_POST['firstName'] ?? '';
    $lastName = $_POST['lastName'] ?? '';
    $email = $_POST['email'] ?? '';
    $phone = $_POST['phone'] ?? '';
    $password = $_POST['password'] ?? '';

    if (empty($newUsername) || empty($firstName) || empty($lastName) || empty($email) || empty($phone)) {
      echo json_encode(['success' => false, 'error' => 'All fields required']);
      exit;
    }

    // Check if new username already exists (if changed)
    if ($newUsername !== $username) {
      $checkStmt = $conn->prepare("SELECT id FROM login WHERE username = ? AND id != (SELECT id FROM login WHERE username = ?)");
      $checkStmt->execute([$newUsername, $username]);
      if ($checkStmt->rowCount() > 0) {
        echo json_encode(['success' => false, 'error' => 'Username already taken']);
        exit;
      }
    }

    try {
      if ($password) {
        $hashedPassword = md5($password);
        $sql = "UPDATE login SET username = ?, first_name = ?, last_name = ?, email = ?, phone = ?, password = ? WHERE username = ?";
        $stmt = $conn->prepare($sql);
        $success = $stmt->execute([$newUsername, $firstName, $lastName, $email, $phone, $hashedPassword, $username]);
      } else {
        $sql = "UPDATE login SET username = ?, first_name = ?, last_name = ?, email = ?, phone = ? WHERE username = ?";
        $stmt = $conn->prepare($sql);
        $success = $stmt->execute([$newUsername, $firstName, $lastName, $email, $phone, $username]);
      }

      if ($success && $newUsername !== $username) {
        $_SESSION['username'] = $newUsername;
        echo json_encode(['success' => true, 'newUsername' => $newUsername, 'reload' => true]);
      } else {
        echo json_encode(['success' => true, 'message' => 'Profile updated successfully']);
      }
    } catch (PDOException $e) {
      echo json_encode(['success' => false, 'error' => $e->getMessage()]);
    }
  }
}

?>
