<?php
  require "../../config.php";

  session_start();
  if (isset($_SESSION["login_data"]))
  {
    header('Location: ../../index/index.php');
    exit();
  }

  if(isset($_POST['submit']))
  {
    $email = $_POST['email'];
    $username= $_POST['username'];
    $password = $_POST['password'];
    $passwordhash = md5($password);
    $cpassword = $_POST['cpassword'];

    if(isset($_FILES['imglink']) || !empty($_FILES['imglink'])){
      $img_name = $_FILES['imglink']['name'];
      $img_size = $_FILES['imglink']['size'];
      $img_tmp = $_FILES['imglink']['tmp_name'];
    }

    if(strcmp($password, $cpassword) == 0)
    {
      try {
        require '../dbconfig.php';

        $datacheck = "SELECT * FROM it211_g2g2.user_profile WHERE username='$username' OR email_address='$email'" ;
        $query = $conn->query($datacheck);
        $results = $query->fetchAll(PDO::FETCH_ASSOC);

        if(!empty($results))
        {
          echo '<script type="text/javascript"> alert("User already exists.. try another username") </script>';
          header("refresh: 1; url = $url/Register/Register.php");
        }
        else
        {
          try {
            $conn->beginTransaction();
            $data = [
              $username,
              $email,
              $passwordhash
            ];

            $q_adduser = $conn->prepare("INSERT INTO it211_g2g2.user_profile (
                username,
                email_address,
                password
            ) VALUES (?,?,?)");

            $q_adduser->execute($data);

            if(isset($img_tmp)){
              $directory = "$images_folder/users/";
              $originalFileName = basename($img_name);
              $fileType = pathinfo($originalFileName, PATHINFO_EXTENSION);
              $filename = "user_{$username}.{$fileType}";
              $target_file = $directory.$filename;
              $uploaded_file = move_uploaded_file($img_tmp,$target_file);
            }

            $conn->commit();

            echo '<script type="text/javascript"> alert("User Registered.. Go to login page to login") </script>';
            header("refresh: 1; url = $url/login/login.php");
          } catch (Exception $e) {
            $conn->rollback();
            echo '<script type="text/javascript"> alert("Error!") </script>';
            throw $e;
          } catch (Throwable $e) {
            $conn->rollback();
            echo '<script type="text/javascript"> alert("PHP Error!") </script>';
            throw $e;
          }

          $conn = null;
          exit();
        }
      } catch(PDOException $e) {
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