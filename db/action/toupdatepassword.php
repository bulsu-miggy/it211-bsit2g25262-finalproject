<?php
  require "../../config.php";

  session_start();

    // echo "<pre>"; print_r($_POST); echo "</pre>";
    // die();

    if (!isset($_POST['submit'])) {
      header("Location: ../../loginpage.php");
      exit();
    }

    $email = $_POST['email'] ?? '';
    $newPassword = $_POST['new_password'] ?? '';

    if ($email === '' || $newPassword === '') {
      echo '<script>alert("Email and new password are required.");</script>';
      header("refresh: 1; url=$url/updatePassword.php");
      exit();
    }

    try {
      require 'dbconfig.php';
    //force binary comparison for email to prevent case-insensitive matches
      $stmt = $conn->prepare("SELECT id FROM login WHERE BINARY email = :email");
      $stmt->execute([':email' => $email]);
      $user = $stmt->fetch(PDO::FETCH_ASSOC);

      if (!$user) {
        echo '<script>alert("User cannot be found");</script>';
        header("refresh: 1; url=$url/loginpage.php");
        exit();
      }

      $hashed = md5($newPassword);

      $update = $conn->prepare("UPDATE login SET password = ? WHERE id = ?");
      $update->execute([$hashed, $user['id']]);

      echo '<script>alert("Password updated. You can now login with your new password.");</script>';
      session_unset();
      session_destroy();
      header("refresh: 1; url=$url/loginpage.php");
      exit();
    } catch (Throwable $e) {
      echo '<script>alert("An error occurred. Please try again.");</script>';
      header("refresh: 2; url=$url/updatePassword.php");
      exit();
    }

?>