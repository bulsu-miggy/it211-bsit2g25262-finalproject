<?php
session_start();
if (!isset($_SESSION["username"])) {
    header('Location: ../../login.php');
    exit();
}

include '../connection.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Capture and sanitize POST data
    $id = (int)($_POST['id'] ?? 0);
    $title = trim($_POST['title'] ?? '');
    $description = trim($_POST['description'] ?? '');
    $price = floatval($_POST['price'] ?? 0.00);
    $stock = intval($_POST['stock'] ?? 0);
    $gender = trim($_POST['gender'] ?? '');
    $sub_category = trim($_POST['sub_category'] ?? '');

    if ($id <= 0 || empty($title) || empty($description) || $price <= 0 || empty($gender) || empty($sub_category)) {
        echo "<script>alert('Invalid data'); window.history.back();</script>";
        exit();
    }

    // Fetch current image for fallback
    $stmt = $conn->prepare("SELECT image_url FROM products WHERE id = ?");
    $stmt->execute([$id]);
    $current = $stmt->fetch(PDO::FETCH_ASSOC);
    $image_url = $current['image_url'] ?? '';

    // Handle optional image upload
    if (isset($_FILES['image']) && $_FILES['image']['error'] === UPLOAD_ERR_OK) {
        $file_tmp = $_FILES['image']['tmp_name'];
        $file_name = $_FILES['image']['name'];
        $file_size = $_FILES['image']['size'];
        $file_ext = strtolower(pathinfo($file_name, PATHINFO_EXTENSION));

        if ($file_size > 2097152) {
            echo "<script>alert('Image size must be less than 2MB'); window.history.back();</script>";
            exit();
        }

        $upload_dir = '../../images/products/';
        if (!is_dir($upload_dir)) {
            mkdir($upload_dir, 0777, true);
        }

        // Delete old image if exists
        if ($image_url && file_exists($upload_dir . $image_url)) {
            unlink($upload_dir . $image_url);
        }

        $new_file_name = time() . '_' . bin2hex(random_bytes(4)) . '.' . $file_ext;
        $target_path = $upload_dir . $new_file_name;

        if (move_uploaded_file($file_tmp, $target_path)) {
            $image_url = $new_file_name;
        } else {
            echo "<script>alert('Image upload failed'); window.history.back();</script>";
            exit();
        }
    }

    try {
        $sql = "UPDATE products SET title = :title, description = :description, price = :price, stock = :stock, gender = :gender, sub_category = :sub_category";
        $params = [
            ':title' => $title,
            ':description' => $description,
            ':price' => $price,
            ':stock' => $stock,
            ':gender' => $gender,
            ':sub_category' => $sub_category
        ];

        if ($image_url) {
            $sql .= ", image_url = :image_url";
            $params[':image_url'] = $image_url;
        }

        $sql .= " WHERE id = :id";
        $params[':id'] = $id;

        $stmt = $conn->prepare($sql);
        $stmt->execute($params);

        echo "<script>
                alert('Product updated successfully!');
                window.location.href = '../../dashboard/products.php';
              </script>";
        exit();

    } catch (PDOException $e) {
        echo "Database Error: " . $e->getMessage();
    }
} else {
    header('Location: ../../dashboard/products.php');
    exit();
}
?>
