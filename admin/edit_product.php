<?php
session_start();
if(!isset($_SESSION['admin_id'])) {
    header("Location: login.php");
    exit();
}
include('../config/db_connect.php');

$id = $_GET['id'];
// Kinuha natin ang product details
$select_query = mysqli_query($conn, "SELECT * FROM products WHERE id = '$id'");
$row = mysqli_fetch_assoc($select_query);

if(isset($_POST['update_product'])) {
    $name = mysqli_real_escape_string($conn, $_POST['name']);
    $category_id = mysqli_real_escape_string($conn, $_POST['category_id']); // Ginawang category_id
    $price = $_POST['price'];
    $stock = $_POST['stock'];
    
    // In-update ang column name sa category_id para mag-match sa database schema mo
    $update_query = "UPDATE products SET name='$name', category_id='$category_id', price='$price', stock='$stock' WHERE id='$id'";
    
    if(!empty($_FILES['image']['name'])) {
        $image = $_FILES['image']['name'];
        $image_tmp_name = $_FILES['image']['tmp_name'];
        $image_folder = '../uploads/' . $image;
        
        move_uploaded_file($image_tmp_name, $image_folder);
        mysqli_query($conn, "UPDATE products SET image='$image' WHERE id='$id'");
    }

    if(mysqli_query($conn, $update_query)) {
        header("Location: products.php?msg=updated");
    } else {
        $error = "Failed to update product.";
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Sparkverse | Edit Product</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;600;700;800&display=swap" rel="stylesheet">
    <style>
        :root { --primary-yellow: #facc15; --dark-sidebar: #1e293b; --bg-light: #f8fafc; }
        body { font-family: 'Inter', sans-serif; background-color: var(--bg-light); margin: 0; padding: 40px; display: flex; justify-content: center; }
        .form-card { background: white; padding: 30px; border-radius: 15px; box-shadow: 0 4px 20px rgba(0,0,0,0.05); width: 100%; max-width: 500px; border: 1px solid #e2e8f0; }
        .form-group { margin-bottom: 20px; }
        .form-group label { display: block; font-weight: 600; margin-bottom: 8px; color: #475569; font-size: 14px; }
        .form-group input, .form-group select { width: 100%; padding: 12px; border: 1px solid #e2e8f0; border-radius: 10px; box-sizing: border-box; }
        .submit-btn { background: var(--primary-yellow); color: #0f172a; padding: 12px; border: none; border-radius: 10px; width: 100%; font-weight: 700; cursor: pointer; }
        .back-link { display: block; margin-top: 15px; text-align: center; color: #64748b; text-decoration: none; font-size: 14px; }
    </style>
</head>
<body>
    <div class="form-card">
        <h2 style="margin-top:0;">Edit Product</h2>
        <form action="" method="POST" enctype="multipart/form-data">
            <div class="form-group">
                <label>Product Name</label>
                <input type="text" name="name" value="<?php echo htmlspecialchars($row['name']); ?>" required>
            </div>
            <div class="form-group">
                <label>Category</label>
                <select name="category_id" required>
                    <option value="">Select Category</option>
                    <?php
                    // Dynamic Category Loading mula sa database
                    $cat_query = mysqli_query($conn, "SELECT * FROM categories");
                    while($cat = mysqli_fetch_assoc($cat_query)) {
                        // I-check kung ito ang current category ng product para maging 'selected'
                        $selected = ($cat['id'] == $row['category_id']) ? 'selected' : '';
                        echo "<option value='{$cat['id']}' $selected>{$cat['name']}</option>";
                    }
                    ?>
                </select>
            </div>
            <div class="form-group">
                <label>Price (₱)</label>
                <input type="number" name="price" value="<?php echo $row['price']; ?>" step="0.01" required>
            </div>
            <div class="form-group">
                <label>Stock</label>
                <input type="number" name="stock" value="<?php echo $row['stock']; ?>" required>
            </div>
            <div class="form-group">
                <label>Product Image (Leave blank to keep current)</label>
                <input type="file" name="image" accept="image/*">
            </div>
            <button type="submit" name="update_product" class="submit-btn">UPDATE PRODUCT</button>
            <a href="products.php" class="back-link">Cancel</a>
        </form>
    </div>
</body>
</html>