<?php
  require "../../config.php";

    session_start();
    if (isset($_SESSION["login_data"]))
    {

      header('Location: ../../index.php');
      exit();

    }


    if(isset($_POST['submit']))
    {
      $firstname = $_POST['first_name'];
      $lastname = $_POST['last_name'];
      $username = $_POST['username'];
      $email = $_POST['email'];
      $password = $_POST['password'];
      $passwordhash = md5($password);
      $cpassword = $_POST['confirm_password'];


      $img_name = '';
      $img_size = 0;
      $img_tmp = '';
      $img_error = UPLOAD_ERR_NO_FILE;
      $img_ext = '';
      $allowed_ext = ['jpg', 'jpeg', 'png', 'webp', 'gif'];
      $hasAvatarUpload = isset($_FILES['imglink']) && is_array($_FILES['imglink']) && (int)($_FILES['imglink']['error'] ?? UPLOAD_ERR_NO_FILE) !== UPLOAD_ERR_NO_FILE;

      if ($hasAvatarUpload) {
        $img_name = (string)($_FILES['imglink']['name'] ?? '');
        $img_size = (int)($_FILES['imglink']['size'] ?? 0);
        $img_tmp = (string)($_FILES['imglink']['tmp_name'] ?? '');
        $img_error = (int)($_FILES['imglink']['error'] ?? UPLOAD_ERR_NO_FILE);
        $img_ext = strtolower((string)pathinfo($img_name, PATHINFO_EXTENSION));
      }

    //   echo "<pre>"; print_r($_FILES); echo "</pre>";
    //   var_dump($_REQUEST);
    //   die();
      
      if(strcmp($password, $cpassword) == 0) //0 equal values 
      {
        try {
          require 'dbconfig.php';

          $datacheck = "SELECT * FROM login WHERE username='$username' OR email='$email'" ;
          
          $query = $conn->query($datacheck);
          $results = $query->fetchAll(PDO::FETCH_ASSOC);

          // echo "<pre>"; print_r($_REQUEST); echo "</pre>";
          // // var_dump($_REQUEST);
          // die();

          if(!empty($results)) //if has data 
          {
            // there is already a user with the same username
            echo '<script type="text/javascript"> 
              alert("User already exists.. try another username") 
            </script>';

            header("refresh: 1; url = $url/registerpage.php");
          }
          else if($hasAvatarUpload && $img_error !== UPLOAD_ERR_OK)
          {
            echo '<script type="text/javascript"> alert("Image upload failed.. Please try again") </script>';
            header("refresh: 1; url = $url/registerpage.php");
          }
          else if($hasAvatarUpload && ($img_size <= 0 || $img_size > 2097152))
          {
            echo '<script type="text/javascript"> alert("Image file size larger than 2 MB.. Try another image file") </script>';
            header("refresh: 1; url = $url/registerpage.php");
          }
          else if($hasAvatarUpload && !in_array($img_ext, $allowed_ext, true))
          {
            echo '<script type="text/javascript"> alert("Invalid image format.. Use JPG, PNG, WEBP, or GIF") </script>';
            header("refresh: 1; url = $url/registerpage.php");
          }
          else
          {

            // if(isset($img_tmp)){

            //   $directory = "$images_folder/users/";
            //   $originalFileName = basename($img_name);
            //   $fileType = pathinfo($originalFileName, PATHINFO_EXTENSION); 
            //   $filename = "user_{$firstname}_{$lastname}.{$fileType}";
            //   $target_file = $directory.$filename;
            //   $uploaded_file = move_uploaded_file($img_tmp,$target_file); 	
            // }
            // die();

            try {
              $conn->beginTransaction();
              
                $avatarPath = null;

            if($hasAvatarUpload && $img_tmp !== '' && is_uploaded_file($img_tmp)){
              $directory = "$images_folder/users/";
              
              // Create directory if it doesn't exist
              if (!is_dir($directory)) {
                mkdir($directory, 0755, true);
              }

              $safeUsername = strtolower((string)preg_replace('/[^a-zA-Z0-9_-]+/', '_', $username));
              $safeUsername = trim($safeUsername, '_');
              if ($safeUsername === '') {
                $safeUsername = 'user_' . time();
              }

              $filename = "user_{$safeUsername}.{$img_ext}";
              $target_file = $directory.$filename;

              foreach (glob($directory . "user_{$safeUsername}.*") ?: [] as $oldFile) {
                if (basename($oldFile) === $filename) {
                  continue;
                }
                if (is_file($oldFile)) {
                  @unlink($oldFile);
                }
              }

              if (is_file($target_file)) {
                @unlink($target_file);
              }

              if (move_uploaded_file($img_tmp, $target_file)) {
                $avatarPath = "assets2/users/{$filename}";
              } else {
                throw new RuntimeException('Avatar upload failed.');
              }
            }

            $data = [
              $firstname,
              $lastname,
              $username,
              $email,
              $passwordhash,
              $avatarPath
            ];

            $q_adduser = $conn->prepare("INSERT INTO login (
                first_name,
                last_name,
                username,
                email,
                password,
                img_url
            ) VALUES (?,?,?,?,?,?)");

            $q_adduser->execute($data);

            $newUserId = (int)$conn->lastInsertId();
            if ($newUserId > 0) {
              $q_profile = $conn->prepare("INSERT INTO user_profiles (user_id, profile_image) VALUES (?, ?) ON DUPLICATE KEY UPDATE profile_image = COALESCE(VALUES(profile_image), profile_image)");
              $q_profile->execute([$newUserId, $avatarPath]);
            }

              $conn->commit();

              echo '<script type="text/javascript"> alert("User Registered.. Go to login page to login") </script>';
              header("refresh: 1; url = $url/loginpage.php");
            }catch (Exception $e){
                $conn->rollback();
                echo '<script type="text/javascript"> alert("Error!") </script>';
                throw $e;
                header("refresh: 1; url = $url/loginpage.php");
            }catch (Throwable $e){
                $conn->rollback();
                echo '<script type="text/javascript"> alert("PHP Error!") </script>';
                throw $e;
                header("refresh: 1; url = $url/loginpage.php");
            }

            $conn = null;
            exit();
          }	
        }catch(PDOException $e) {
          throw($e);
          die("connection error");
        }
      }
      else
      {
        echo '<script type="text/javascript"> alert("Password and confirm password does not match!")</script>';	
      }
    }

    
?>