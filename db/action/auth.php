<?php
    session_start();
    
    $action = $_POST['action'] ?? 'login';
    
    if ($action === 'login') {
      // LOGIN LOGIC
      if (isset($_SESSION["username"]) && isset($_SESSION["password"]))
      {
        if (isset($_SESSION["role"]) && $_SESSION["role"] === 'admin') {
          header('Location: ../../dashboard/dashboard.php');
        } else {
          header('Location: ../../index.php');
        }
        exit();
      }

      if(isset($_POST["username"]) && isset($_POST["password"])) {
          $login_user = $_POST["username"];
          $login_pass = md5($_POST["password"]);
          include '../connection.php';
          // First check username/email exists
          $select_user = "SELECT * FROM login WHERE username = ? OR email = ?";
          $stmt_user = $conn->prepare($select_user);
          $stmt_user->execute([$login_user, $login_user]);
          $userData = $stmt_user->fetch(PDO::FETCH_ASSOC);

          if (!$userData) {
            $_SESSION['error'] = 'Invalid Username';
            header('Location: ../../login.php');
            exit();
          }

          // Check password
          if ($userData['password'] !== $login_pass) {
            $_SESSION['error'] = 'Incorrect Password';
            header('Location: ../../login.php');
            exit();
          }

          // Success
          $_SESSION['username'] = $userData['username'];
          $_SESSION['password'] = $userData['password'];
          $_SESSION['role'] = !empty($userData['role']) ? $userData['role'] : 'client';

          if ($_SESSION['role'] === 'admin') {
            header('Location: ../../dashboard/dashboard.php');
          } else {
            header('Location: ../../index.php');
          }
          exit();
      } else {
          echo "No values found.";
          header("refresh: 1; url = ../../login.php");
      }
      
    } else if ($action === 'register') {
      // REGISTER LOGIC
      if (isset($_SESSION["username"]) && isset($_SESSION["password"]))
      {
        if (isset($_SESSION["role"]) && $_SESSION["role"] === 'admin') {
          header('Location: ../../dashboard/dashboard.php');
        } else {
          header('Location: ../../index.php');
        }
        exit();
      }

      if(isset($_POST['submit']))
      {
        $reg_firstname = $_POST['first_name'];
        $reg_lastname = $_POST['last_name'];
        $reg_email = $_POST['email'];
        $reg_username = $_POST['username'];
        $reg_password = $_POST['password'];
        $reg_passwordhash = md5($reg_password);
        $reg_cpassword = $_POST['cpassword'];

        if(isset($_FILES['imglink']) && !empty($_FILES['imglink']['name'])){
          $img_name = $_FILES['imglink']['name'];
          $img_size = $_FILES['imglink']['size'];
          $img_tmp = $_FILES['imglink']['tmp_name'];
        }

        if(strcmp($reg_password, $reg_cpassword) == 0)
        {
          include '../connection.php';

          // Check username
          $user_check = "SELECT id FROM login WHERE username = ?";
          $stmt = $conn->prepare($user_check);
          $stmt->execute([$reg_username]);
          $user_taken = $stmt->fetchColumn();

          // Check email
          $email_check = "SELECT id FROM login WHERE email = ?";
          $stmt = $conn->prepare($email_check);
          $stmt->execute([$reg_email]);
          $email_taken = $stmt->fetchColumn();

          if ($user_taken) {
            echo '<script type="text/javascript"> alert("Username \\"' . htmlspecialchars($reg_username) . '\\" is already taken.") </script>';
            header("refresh: 1; url = ../../register.php");
            exit();
          }
          if ($email_taken) {
            echo '<script type="text/javascript"> alert("Email \\"' . htmlspecialchars($reg_email) . '\\" is already registered.") </script>';
            header("refresh: 1; url = ../../register.php");
            exit();
          }
          elseif(isset($img_size) && $img_size > 2097152)
          {
            echo '<script type="text/javascript"> alert("Image file size larger than 2 MB.. Try another image file") </script>';
            header("refresh: 1; url = ../../register.php");
          }
          else
          {
            // Direct values, no array_merge - 5 placeholders
            $sql = "INSERT INTO login (first_name, last_name, username, email, password, role) VALUES (?, ?, ?, ?, ?, 'client')";

            $stmt = $conn->prepare($sql);
            if($stmt->execute([$reg_firstname, $reg_lastname, $reg_username, $reg_email, $reg_passwordhash])){
              if(isset($img_tmp)){
                $directory = "../../images/users/";
                if(!is_dir($directory)){
                  mkdir($directory, 0777, true);
                }
                $originalFileName = basename($img_name);
                $fileType = pathinfo($originalFileName, PATHINFO_EXTENSION);
                $filename = "user_" . $reg_firstname . "_" . $reg_lastname . "." . strtolower($fileType);
                $target_file = $directory . $filename;
                if(move_uploaded_file($img_tmp, $target_file)){
                  $update_img = $conn->prepare("UPDATE login SET img_url = ? WHERE username = ?");
                  $update_img->execute([$filename, $reg_username]);
                }
              }
              echo '<script type="text/javascript"> alert("User \\"' . htmlspecialchars($reg_username) . '\\" Registered Successfully! Please login.") </script>';
              header("refresh: 1; url = ../../login.php");
            } else {
              echo '<script type="text/javascript"> alert("Database error: " + $conn->errorInfo()[2]) </script>';
              header("refresh: 1; url = ../../register.php");
            }
          }
        }
        else
        {
          echo '<script type="text/javascript"> alert("Password and confirm password do not match!")</script>';
          header("refresh: 1; url = ../../register.php");
        }
      }
      } else if ($action === 'reset_password') {
    include '../connection.php';
    
    // Get user identifier from the form instead of session
    $user_identifier = $_POST['user_id'] ?? ''; 
    $old_password = $_POST['old_password'];
    $new_password = $_POST['new_password'];
    $confirm_new_password = $_POST['confirm_new_password'];

    if ($new_password !== $confirm_new_password) {
        echo '<script>alert("New passwords do not match!"); window.location.href="../../forgot_password.php";</script>';
        exit();
    }

    // 1. Check if the old password matches the user provided
    $old_pass_md5 = md5($old_password);
    $check_sql = "SELECT * FROM login WHERE (username = ? OR email = ?) AND password = ?";
    $stmt_check = $conn->prepare($check_sql);
    $stmt_check->execute([$user_identifier, $user_identifier, $old_pass_md5]);
    
    if ($stmt_check->rowCount() > 0) {
        // 2. Update the password
        $new_pass_md5 = md5($new_password);
        $update_sql = "UPDATE login SET password = ? WHERE username = ? OR email = ?";
        $stmt_update = $conn->prepare($update_sql);
        
        if ($stmt_update->execute([$new_pass_md5, $user_identifier, $user_identifier])) {
            echo '<script>alert("Password updated successfully! Please login."); window.location.href="../../login.php";</script>';
        } else {
            echo '<script>alert("Error updating password."); window.location.href="../../forgot_password.php";</script>';
        }
    } else {
        // This is the error you were seeing because $user_identifier was empty
        echo '<script>alert("Incorrect Username/Email or Old Password."); window.location.href="../../forgot_password.php";</script>';
    }

    // Halimbawa ng ADD PRODUCT logic para sa Admin Dashboard
if ($action === 'add_product') {
    include '../connection.php';
    
    $title = $_POST['title'];
    $main_category = $_POST['main_category']; // 'Men' or 'Women' mula sa dropdown
    $sub_category = $_POST['sub_category'];   // 'basic tops', 'shorts', etc.
    $price = $_POST['price'];
    $stock = $_POST['stock'];
    $image = $_FILES['product_image']['name'];

    // SQL Query para sa Unified Table
    $sql = "INSERT INTO products (title, main_category, sub_category, price, stock, image_url) 
            VALUES (?, ?, ?, ?, ?, ?)";
    $stmt = $conn->prepare($sql);
    
    if ($stmt->execute([$title, $main_category, $sub_category, $price, $stock, $image])) {
        echo '<script>alert("Product Added Successfully!"); window.location.href="../../dashboard/products.php";</script>';
    } else {
        echo '<script>alert("Error adding product.");</script>';
    }
}

    } else {
      echo "Invalid action";
      header("refresh: 1; url = ../../index.php");
    }
?>

