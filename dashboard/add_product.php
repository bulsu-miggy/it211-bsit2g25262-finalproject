<?php
session_start();
if (!isset($_SESSION["username"])) {
    header('Location: ../login.php');
    exit();
}

include '../db/connection.php';

$error = '';
$success = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $title = trim($_POST['title'] ?? '');
    $description = trim($_POST['description'] ?? '');
    $price = floatval($_POST['price'] ?? 0);
    $stock = intval($_POST['stock'] ?? 0);
    $gender = trim($_POST['gender'] ?? '');
    $sub_category = trim($_POST['sub_category'] ?? '');

    if (empty($title) || empty($description) || $price <= 0 || empty($gender) || empty($sub_category)) {
        $error = 'Please fill all required fields correctly.';
    } elseif (isset($_FILES['image']) && $_FILES['image']['error'] === 0) {
        $file_tmp = $_FILES['image']['tmp_name'];
        $file_size = $_FILES['image']['size'];
        $file_ext = strtolower(pathinfo($_FILES['image']['name'], PATHINFO_EXTENSION));

        if ($file_size > 2097152) { // 2MB
            $error = 'Image size must be less than 2MB.';
        } else {
            $upload_dir = '../../uploads/';
            if (!is_dir($upload_dir)) {
                mkdir($upload_dir, 0777, true);
            }
            $image_filename = time() . '_' . uniqid() . '.' . $file_ext;
            $image_path = $upload_dir . $image_filename;

            if (move_uploaded_file($file_tmp, $image_path)) {
                $image_url = $image_filename;

                try {
                    $sql = "INSERT INTO products (title, description, price, stock, gender, sub_category, image_url) VALUES (?, ?, ?, ?, ?, ?, ?)";
                    $stmt = $conn->prepare($sql);
                    $stmt->execute([$title, $description, $price, $stock, $gender, $sub_category, $image_url]);

                    $success = 'Product added successfully!';
                    header('Location: products.php');
                    exit();
                } catch (PDOException $e) {
                    $error = 'Database error: ' . $e->getMessage();
                }
            } else {
                $error = 'Image upload failed.';
            }
        }
    } else {
        $error = 'Please upload an image.';
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Add Product - LYNX Admin</title>
  <link href="https://fonts.googleapis.com/icon?family=Material+Icons+Outlined" rel="stylesheet">
  <link href="https://fonts.googleapis.com/css2?family=Rubik+Mono+One&display=swap" rel="stylesheet">
  <link rel="stylesheet" href="style.css">
  <style>
    .logo { font-family: 'Rubik Mono One', sans-serif; font-size: 24px; color: black; text-decoration: none; margin: 0; }
    .form-container { max-width: 800px; margin-top: 20px; }
    .form-group { margin-bottom: 24px; display: flex; flex-direction: column; gap: 8px; }
    .form-group label { font-weight: 600; font-size: 14px; color: var(--text-main); }
    .form-group input, .form-group textarea, .form-group select { padding: 12px; border: 1px solid var(--border-color); border-radius: 8px; font-size: 14px; outline: none; transition: border-color 0.2s; }
    .form-group input:focus, .form-group textarea:focus, .form-group select:focus { border-color: var(--accent-black); }
    .form-actions { display: flex; gap: 16px; margin-top: 32px; }
    .btn-cancel { text-decoration: none; color: var(--text-muted); padding: 10px 20px; display: flex; align-items: center; font-weight: 500; }
    .gender-sub-row { display: grid; grid-template-columns: 1fr 1fr; gap: 20px; }
    .error { background: #fee; color: #c33; padding: 12px; border-radius: 8px; margin-bottom: 20px; text-align: center; font-weight: 500; }
    .success { background: #d4edda; color: #155724; padding: 12px; border-radius: 8px; margin-bottom: 20px; text-align: center; font-weight: 500; }
  </style>
</head>
<body>

<div class="dashboard-container">
  <aside class="sidebar">
    <div class="logo-section">
      <a href="dashboard.php" class="logo">LYNX</a>
    </div>
    <nav class="nav-container">
      <ul class="nav">
        <li onclick="location.href='dashboard.php'"><span class="material-icons-outlined">dashboard</span> Dashboard</li>
        <li onclick="location.href='orders.php'"><span class="material-icons-outlined">shopping_cart</span> Orders</li>
        <li class="active" onclick="location.href='products.php'"><span class="material-icons-outlined">inventory_2</span> Products</li>
        <li onclick="location.href='categories.php'"><span class="material-icons-outlined">category</span> Categories</li>
        <li onclick="location.href='customers.php'"><span class="material-icons-outlined">group</span> Customers</li>
        <li onclick="location.href='analytics.php'"><span class="material-icons-outlined">analytics</span> Analytics</li>
      </ul>
    </nav>
    <div class="sidebar-footer">
      <div class="avatar"></div>
      <div class="user-info">
        <strong>Admin</strong>
      </div>
      <a href="../db/action/logout.php" title="Logout" style="color: black; margin-left: auto; display: flex; align-items: center; text-decoration: none;">
        <span class="material-icons-outlined">logout</span>
      </a>
    </div>
  </aside>
  <main class="main">
    <header class="topbar">
      <h1 class="page-title">Dashboard / Products / Add New</h1>
    </header>
    <div class="orders-header">
      <div class="header-text">
        <h2>Add New Product</h2>
        <p class="subtext">Fill in the details below to add a new item to your catalog</p>
      </div>
    </div>
    <section class="panel form-container">
      <?php if ($error): ?>
        <div class="error"><?php echo htmlspecialchars($error); ?></div>
      <?php endif; ?>
      <?php if ($success): ?>
        <div class="success"><?php echo htmlspecialchars($success); ?></div>
      <?php endif; ?>
      <form action="add_product.php" method="POST" enctype="multipart/form-data">
        <div class="form-group">
          <label>Product Title *</label>
          <input type="text" name="title" placeholder="e.g. Oversized Cotton Tee" value="<?php echo htmlspecialchars($_POST['title'] ?? ''); ?>" required>
        </div>
        <div class="form-group">
          <label>Description *</label>
          <textarea name="description" rows="4" placeholder="Describe the product details, fit, and material..." required><?php echo htmlspecialchars($_POST['description'] ?? ''); ?></textarea>
        </div>
        <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 20px;">
          <div class="form-group">
            <label>Price ($) *</label>
            <input type="number" name="price" step="0.01" placeholder="0.00" value="<?php echo htmlspecialchars($_POST['price'] ?? ''); ?>" required min="0">
          </div>
          <div class="form-group">
            <label>Stock Quantity *</label>
            <input type="number" name="stock" placeholder="0" value="<?php echo htmlspecialchars($_POST['stock'] ?? ''); ?>" required min="0">
          </div>
        </div>
        <div class="gender-sub-row">
          <div class="form-group">
            <label>Gender *</label>
            <select name="gender" id="genderSelect" required onchange="updateSubCategories()">
              <option value="">Select gender</option>
              <option value="Women" <?php echo ($_POST['gender'] ?? '') == 'Women' ? 'selected' : ''; ?>>Women</option>
              <option value="Men" <?php echo ($_POST['gender'] ?? '') == 'Men' ? 'selected' : ''; ?>>Men</option>
            </select>
          </div>
          <div class="form-group">
            <label>Sub Category *</label>
            <select name="sub_category" id="subCategorySelect" required>
              <option value="">Select sub category</option>
            </select>
          </div>
        </div>
        <div class="form-group">
          <label>Product Image *</label>
          <input type="file" name="image" accept="image/*" required>
        </div>
        <div class="form-actions">
          <button type="submit" class="btn-export">
            <span class="material-icons-outlined">save</span>
            Save Product
          </button>
          <a href="products.php" class="btn-cancel">Cancel</a>
        </div>
      </form>
    </section>
  </main>
</div>

<script>
const subCategories = {
  'Women': ['skirts', 'shorts', 'pants', 'outerwear'],
  'Men': ['basic_tops', 'shorts', 'pants', 'outerwear']
};

function updateSubCategories() {
  const gender = document.getElementById('genderSelect').value;
  const subSelect = document.getElementById('subCategorySelect');
  
  subSelect.innerHTML = '<option value="">Select sub category</option>';
  
  if (gender && subCategories[gender]) {
    subCategories[gender].forEach(cat => {
      const option = document.createElement('option');
      option.value = cat;
      option.textContent = cat.replace('_', ' ').replace(/\b\w/g, l => l.toUpperCase());
      subSelect.appendChild(option);
    });
  }
}
</script>

</body>
</html> 
