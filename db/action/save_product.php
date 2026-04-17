<?php
session_start();
// Restrict access to logged-in users
if (!isset($_SESSION["username"])) {
    header('Location: ../../login.php');
    exit();
}

include '../connection.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Capture and sanitize POST data
    $title = trim($_POST['name'] ?? '');
    $description = trim($_POST['description'] ?? '');
    $price = floatval($_POST['price'] ?? 0.00);
    $stock = intval($_POST['stock'] ?? 0);
    $gender = trim($_POST['gender'] ?? '');
    $sub_category = trim($_POST['sub_category'] ?? '');

    if (empty($title) || empty($description) || $price <= 0 || empty($gender) || empty($sub_category)) {
        echo "<script>alert('Please fill all required fields'); window.history.back();</script>";
        exit();
    }

    $image_url = '';

    // Handle Image Upload logic
    if (isset($_FILES['image']) && $_FILES['image']['error'] === UPLOAD_ERR_OK) {
        $file_tmp = $_FILES['image']['tmp_name'];
        $file_name = $_FILES['image']['name'];
        $file_size = $_FILES['image']['size'];
        $file_ext = strtolower(pathinfo($file_name, PATHINFO_EXTENSION));

        // Validate file size (e.g., 2MB limit)
        if ($file_size > 2097152) {
            echo "<script>alert('Error: Image size must be less than 2MB'); window.history.back();</script>";
            exit();
        }

        // Define target directory and unique filename
        $upload_dir = '../../images/products/';
        if (!is_dir($upload_dir)) {
            mkdir($upload_dir, 0777, true);
        }

        $new_file_name = time() . '_' . bin2hex(random_bytes(4)) . '.' . $file_ext;
        $target_path = $upload_dir . $new_file_name;

        if (move_uploaded_file($file_tmp, $target_path)) {
            $image_url = $new_file_name;
        } else {
            echo "<script>alert('Image upload failed'); window.history.back();</script>";
            exit();
        }
    } else {
        echo "<script>alert('No image uploaded'); window.history.back();</script>";
        exit();
    }

    try {
        // Insert into products table using PDO
        $sql = "INSERT INTO products (title, description, price, stock, gender, sub_category, image_url) 
                VALUES (:title, :description, :price, :stock, :gender, :sub_category, :image_url)";
        
        $stmt = $conn->prepare($sql);
        $stmt->execute([
            ':title' => $title,
            ':description' => $description,
            ':price' => $price,
            ':stock' => $stock,
            ':gender' => $gender,
            ':sub_category' => $sub_category,
            ':image_url' => $image_url
        ]);

        // Success feedback and redirect
        echo "<script>
                alert('Product added successfully!');
                window.location.href = '../../dashboard/products.php';
              </script>";
        exit();

    } catch (PDOException $e) {
        echo "Database Error: " . $e->getMessage();
    }
} else {
    header('Location: ../../dashboard/add_product.php');
    exit();
}
?>
