<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Edit Product - LYNX Admin</title>
  <link href="https://fonts.googleapis.com/icon?family=Material+Icons+Outlined" rel="stylesheet">
  <link href="https://fonts.googleapis.com/css2?family=Rubik+Mono+One&display=swap" rel="stylesheet">
  <link rel="stylesheet" href="style.css">
  <style>
    .logo {
      font-family: 'Rubik Mono One', sans-serif;
      font-size: 24px;
      color: black;
      text-decoration: none;
      margin: 0;
    }
    .form-container {
      max-width: 800px;
      margin-top: 20px;
    }
    .form-group {
      margin-bottom: 24px;
      display: flex;
      flex-direction: column;
      gap: 8px;
    }
    .form-group label {
      font-weight: 600;
      font-size: 14px;
      color: var(--text-main);
    }
    .form-group input, 
    .form-group textarea, 
    .form-group select {
      padding: 12px;
      border: 1px solid var(--border-color);
      border-radius: 8px;
      font-size: 14px;
      outline: none;
      transition: border-color 0.2s;
    }
    .form-group input:focus, 
    .form-group textarea:focus, 
    .form-group select:focus {
      border-color: var(--accent-black);
    }
    .form-actions {
      display: flex;
      gap: 16px;
      margin-top: 32px;
    }
    .btn-cancel {
      text-decoration: none;
      color: var(--text-muted);
      padding: 10px 20px;
      display: flex;
      align-items: center;
      font-weight: 500;
    }
    .gender-sub-row {
      display: grid;
      grid-template-columns: 1fr 1fr;
      gap: 20px;
    }
    .current-image {
      max-width: 200px;
      max-height: 200px;
      border-radius: 8px;
      margin-bottom: 10px;
    }
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
        <li onclick="location.href='dashboard.php'">
          <span class="material-icons-outlined">dashboard</span>
          Dashboard
        </li>
        <li onclick="location.href='orders.php'">
          <span class="material-icons-outlined">shopping_cart</span>
          Orders
        </li>
        <li class="active" onclick="location.href='products.php'">
          <span class="material-icons-outlined">inventory_2</span>
          Products
        </li>
        <li onclick="location.href='categories.php'">
          <span class="material-icons-outlined">category</span>
          Categories
        </li>
        <li onclick="location.href='customers.php'">
          <span class="material-icons-outlined">group</span>
          Customers
        </li>
        <li onclick="location.href='analytics.php'">
          <span class="material-icons-outlined">analytics</span>
          Analytics
        </li>
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
      <h1 class="page-title">Dashboard / Products / Edit</h1>
    </header>

    <div class="orders-header">
      <div class="header-text">
        <h2>Edit Product</h2>
        <p class="subtext">Update the product details below</p>
      </div>
    </div>

<?php
session_start();
include '../db/connection.php';

if (!isset($_SESSION['username'])) {
    header('Location: ../../login.php');
    exit;
}

$id = isset($_GET['id']) ? (int)$_GET['id'] : 0;
$product = null;
$message = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST' && $id > 0) {
    $title = trim($_POST['title'] ?? '');
    $description = trim($_POST['description'] ?? '');
    $price = floatval($_POST['price'] ?? 0);
    $stock = intval($_POST['stock'] ?? 0);
    $gender = trim($_POST['gender'] ?? '');
    $sub_category = trim($_POST['sub_category'] ?? '');

    if (!empty($title) && !empty($description) && $price > 0 && !empty($gender) && !empty($sub_category)) {
        $sql = "UPDATE products SET title = ?, description = ?, price = ?, stock = ?, gender = ?, sub_category = ? WHERE id = ?";
        $stmt = $conn->prepare($sql);
        $success = $stmt->execute([$title, $description, $price, $stock, $gender, $sub_category, $id]);
        
        if ($success) {
            echo '<script>alert("Product updated successfully!"); window.location.href="products.php";</script>';
            exit;
        } else {
            $message = 'Update failed.';
        }
    } else {
        $message = 'Please fill all required fields.';
    }
} elseif ($id > 0) {
    // Fetch product for editing
    $stmt = $conn->prepare("SELECT * FROM products WHERE id = ?");
    $stmt->execute([$id]);
    $product = $stmt->fetch(PDO::FETCH_ASSOC);
}

if (!$product) {
    echo '<p style="color: red; text-align: center;">Product not found.</p>';
    exit;
}
?>

    <section class="panel form-container">
      <form method="POST" enctype="multipart/form-data" id="productForm">
        <input type="hidden" name="id" value="<?php echo $product['id']; ?>"> 

        <div class="form-group">
          <label>Product Name</label>
          <input type="text" name="title" value="<?php echo htmlspecialchars($product['title']); ?>" required>
        </div>

        <div class="form-group">
          <label>Description</label>
          <textarea name="description" rows="4" required><?php echo htmlspecialchars($product['description'] ?? ''); ?></textarea>
        </div>

        <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 20px;">
          <div class="form-group">
            <label>Price ($)</label>
            <input type="number" name="price" step="0.01" value="<?php echo $product['price']; ?>" required>
          </div>
          <div class="form-group">
            <label>Stock Quantity</label>
            <input type="number" name="stock" value="<?php echo $product['stock']; ?>" required>
          </div>
        </div>

        <div class="gender-sub-row">
          <div class="form-group">
            <label>Category (Gender)</label>
            <select name="gender" id="genderSelect" onchange="updateSubCategories()" required>
              <option value="">Select category</option>
              <option value="Women" <?php echo ($product['gender'] == 'Women') ? 'selected' : ''; ?>>Women</option>
              <option value="Men" <?php echo ($product['gender'] == 'Men') ? 'selected' : ''; ?> >Men</option>
            </select>
          </div>
          <div class="form-group">
            <label>Sub Category</label>
            <select name="sub_category" id="subCategorySelect" required>
              <option value="">Select sub category</option>
            </select>
          </div>
        </div>

        <div class="form-group">
          <label>Current Image</label>
          <?php if ($product['image_url']): ?>
            <img src="../../images/products/<?php echo htmlspecialchars($product['image_url']); ?>" alt="Current image" class="current-image">
          <?php else: ?>
            <p>No image</p>
          <?php endif; ?>
          <label>New Product Image (optional - leave empty to keep current)</label>
          <input type="file" name="image" accept="image/*">
        </div>

        <div class="form-actions">
          <button type="submit" class="btn-export">
            <span class="material-icons-outlined">save</span>
            Update Product
          </button>
          <a href="products.php" class="btn-cancel">
            <span class="material-icons-outlined">arrow_back</span>
            Cancel
          </a>
        </div>
      </form>
    </section>
  </main>
</div>

<script>
const subCategories = {
  'Women': ['basic_tops', 'skirts', 'outerwear', 'pants'],
  'Men': ['basic_tops', 'shorts', 'outerwear', 'pants']
};

// Load current sub category
document.addEventListener('DOMContentLoaded', function() {
  updateSubCategories();
});

function updateSubCategories() {
  const gender = document.getElementById('genderSelect').value;
  const subSelect = document.getElementById('subCategorySelect');
  const currentSubCat = '<?php echo $product["sub_category"] ?? ""; ?>';
  
  subSelect.innerHTML = '<option value="">Select sub category</option>';
  
  if (gender && subCategories[gender]) {
    subCategories[gender].forEach(cat => {
      const option = document.createElement('option');
      option.value = cat;
      option.textContent = cat.replace('_', ' ').replace(/\b\w/g, l => l.toUpperCase());
      if (cat === currentSubCat) {
        option.selected = true;
      }
      subSelect.appendChild(option);
    });
  }
}
</script>

</body>
</html>
