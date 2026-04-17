<?php
    session_start();
    if (!isset($_SESSION["username"]) || !isset($_SESSION["password"]))
    {
      header('Location: ../../login.php');
      exit();
    }

    if(isset($_POST['submit']))
    {
      $title = $_POST['name'];
      $excerpt = $_POST['description'];
      $price = $_POST['price'];
      $category = $_POST['category'] ?? 'men';  // Get category from POST, default to 'men'

      // Determine table name based on category
      $table_name = ($category === 'women') ? 'women_products' : 'men_products';
      $category_label = ($category === 'women') ? 'Women' : 'Men';

      $image_name = '';
      if(isset($_FILES['image']) && !empty($_FILES['image']['name'])){
        $img_name = $_FILES['image']['name'];
        $img_size = $_FILES['image']['size'];
        $img_tmp = $_FILES['image']['tmp_name'];
        $img_type = $_FILES['image']['type'];

        if($img_size > 2097152){
          echo '<script type="text/javascript"> alert("Image file size larger than 2 MB.. Try another image file") </script>';
          header("refresh: 1; url = ../../addproduct.php");
          exit();
        }

        $allowed_types = ['image/jpeg', 'image/jpg', 'image/png'];
        if(!in_array($img_type, $allowed_types)){
          echo '<script type="text/javascript"> alert("Only JPG, JPEG, PNG files are allowed.") </script>';
          header("refresh: 1; url = ../../addproduct.php");
          exit();
        }

        $directory = "../../images/products/";
        if(!is_dir($directory)){
          mkdir($directory, 0777, true);
        }
        $originalFileName = basename($img_name);
        $fileType = pathinfo($originalFileName, PATHINFO_EXTENSION);
        $filename = "product_" . time() . ".{$fileType}";
        $target_file = $directory . $filename;

        if(move_uploaded_file($img_tmp, $target_file)){
          $image_name = $filename;
        } else {
          echo '<script type="text/javascript"> alert("Error uploading image.") </script>';
          header("refresh: 1; url = ../../addproduct.php");
          exit();
        }
      }



      include 'mysqli-connect.php';

$sql = "INSERT INTO $table_name (title, excerpt, price, imgurl, category) VALUES (?, ?, ?, ?, ?)";

      if($stmt = mysqli_prepare($con, $sql)){

        mysqli_stmt_bind_param($stmt, "ssdss", $title, $excerpt, $price, $image_name, $category);

        if(mysqli_stmt_execute($stmt)){
          echo '<script type="text/javascript"> alert("' . $category_label . ' ' . ucfirst($category) . ' product added successfully!") </script>';
          $redirect_url = isset($_SERVER['HTTP_REFERER']) ? $_SERVER['HTTP_REFERER'] : '../../index.php';
          header("refresh: 1; url = " . $redirect_url);
        } else {
          echo '<script type="text/javascript"> alert("Error adding product.") </script>';
          header("refresh: 1; url = ../../addproduct.php");
        }
        mysqli_stmt_close($stmt);
      } else {
        echo '<script type="text/javascript"> alert("Database error.") </script>';
        header("refresh: 1; url = ../../addproduct.php");
      }

      mysqli_close($con);
    } else {
      header('Location: ../../addproduct.php');
    }
?>
