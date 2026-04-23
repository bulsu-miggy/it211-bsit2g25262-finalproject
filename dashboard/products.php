<?php
  include '../db/connection.php';
  
  // Handle delete
if (isset($_POST['delete_id']) && is_numeric($_POST['delete_id'])) {
    $delete_id = (int)$_POST['delete_id'];
    $stmt = $conn->prepare("DELETE FROM products WHERE id = ?");
    $result = $stmt->execute([$delete_id]);
    if ($result) {
      echo '<script>alert("Product deleted successfully!");</script>';
    }
    header('Location: products.php'); 
    exit;
  }
  
  // Fetch products from the unified products table with new columns
  $query = "SELECT id, title, sub_category, price, stock FROM products ORDER BY id DESC";
  $stmt = $conn->query($query);
  $products = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>

<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Products - LYNX Admin</title>
  <link href="https://fonts.googleapis.com/icon?family=Material+Icons+Outlined" rel="stylesheet">
  <link href="https://fonts.googleapis.com/css2?family=Rubik+Mono+One&display=swap" rel="stylesheet">
  <link rel="stylesheet" href="style.css">
  <script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>
<style>
    .logo {
      font-family: 'Rubik Mono One', sans-serif;
      font-size: 24px;
      color: black;
      text-decoration: none;
      margin: 0;
    }
    .category-cell {
      display: flex;
      flex-direction: column;
      gap: 4px;
      font-size: 14px;
    }
    .gender-badge {
      background: #e3f2fd;
      color: #1976d2;
      padding: 2px 8px;
      border-radius: 12px;
      font-size: 12px;
      font-weight: 500;
    }
    .sub-cat-label {
      font-weight: 500;
      color: #333;
    }
    .delete-link:hover {
      text-decoration: underline;
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
      <a href="../db/action/logout.php" title="Logout" id="logout" style="color: black; margin-left: auto; display: flex; align-items: center; text-decoration: none;" onclick="return confirm('Are you sure you want to logout?');">
        <span class="material-icons-outlined">logout</span>
      </a>
    </div>
  </aside>

  <main class="main">
    <header class="topbar">
      <h1 class="page-title">Dashboard / Products</h1>
      <div class="top-actions">
        <div class="search-container">
          <span class="material-icons-outlined">search</span>
          <input type="text" placeholder="Search...">
        </div>
        <div class="icon-badge">
          <span class="material-icons-outlined">notifications</span>
          <span class="dot">3</span>
        </div>
        <div class="avatar small"></div>
      </div>
    </header>

    <div class="orders-header">
      <div class="header-text">
        <h2>Products</h2>
        <p class="subtext">Manage your product catalog</p>
      </div>
      <button class="btn-export" onclick="location.href='add_product.php'">
        <span class="material-icons-outlined">add</span>
        Add Product
      </button>
    </div>

    <div class="filter-bar">
    <div class="filter-search">
      <span class="material-icons-outlined">search</span>
      <input type="text" id="searchInput" class="search-input" placeholder="Search products...">
    </div>
      <select class="filter-select" id="categoryFilter">
        <option value="">All Categories</option>
        <option value="basic-tops">Basic Tops</option>
        <option value="skirts">Skirts</option>
        <option value="shorts">Shorts</option>
        <option value="pants">Pants</option>
        <option value="outerwear">Outerwears</option>
      </select>
      <div class="view-toggle-group">
        <button class="view-btn"><span class="material-icons-outlined">grid_view</span></button>
        <button class="view-btn active-view"><span class="material-icons-outlined">view_list</span></button>
      </div>
    </div>

    <section class="panel full-width">
      <div class="table-container">
        <table class="orders-table">
          <thead>
            <tr>
              <th>Product</th>
              <th>Gender</th>
              <th>Sub Category</th>
              <th>Price</th>
              <th>Stock</th>
              <th>Status</th>
              <th>Actions</th>
            </tr>
          </thead>
          <tbody>
            <?php foreach ($products as $row): ?>
            <tr>
              <td>
                <div class="product-cell">
                  <div class="img-placeholder"></div>
                  <span><?php echo htmlspecialchars($row['title']); ?></span>
                </div>
              </td>
              <td>
                <span class="gender-badge"><?php echo isset($row['gender'])? htmlspecialchars($row['gender']) : '--'; ?></span>
              </td>
              <td>
                <span class="sub-cat-label"><?php echo htmlspecialchars($row['sub_category']); ?></span>
              </td>
              <td>$<?php echo number_format($row['price'], 2); ?></td>
              <td><?php echo (int)$row['stock']; ?></td>
              <td><span class="badge completed">Active</span></td>
              <td>
                <div class="action-links">
                  <a href="edit_product.php?id=<?php echo $row['id']; ?>" class="view-link">Edit</a>
                  <form method="POST" style="display: inline;" onsubmit="return confirm('Are you sure you want to delete this product?');">
                    <input type="hidden" name="delete_id" value="<?php echo $row['id']; ?>">
                    <button type="submit" class="delete-link" onclick="return confirm('Are you sure you want to delete this product?');" style="color: #dc3545; margin-left: 10px; text-decoration: none; background: none; border: none; cursor: pointer; font: inherit;">Delete</button>
                  </form>
                </div>
              </td>
            </tr>
            <?php endforeach; ?>
            <?php if (empty($products)): ?>
              <tr><td colspan="7" style="text-align:center; padding: 20px;">No products found in the catalog.</td></tr>
            <?php endif; ?>
          </tbody>
        </table>
      </div>
    </section>
  </main>
</div>

<script>
$(document).ready(function() {
  const $searchInput = $('#searchInput');
  const $categoryFilter = $('#categoryFilter');
  const $tableRows = $('table.orders-table tbody tr');

  function filterTable() {
    const searchTerm = $searchInput.val().toLowerCase();
    const category = $categoryFilter.val();

    $tableRows.each(function() {
      const $row = $(this);
      // Skip header or no products row
      if ($row.find('td').length < 7 || $row.find('td').eq(0).text().trim() === 'No products found in the catalog.') {
        return;
      }
      const titleCell = $row.find('td').eq(0).text().toLowerCase();
      const subCatCell = $row.find('td').eq(2).text().toLowerCase();

      const matchesSearch = searchTerm === '' || titleCell.includes(searchTerm);
      const normSubCat = subCatCell.replace(/_/g, ' ').replace(/\s+/g, ' ').trim().toLowerCase();
      const normCategory = category.replace(/_/g, ' ').replace(/\s+/g, ' ').trim().toLowerCase();
      const matchesCategory = category === '' || normSubCat === normCategory;

      if (matchesSearch && matchesCategory) {
        $row.show();
      } else {
        $row.hide();
      }
    });
  }

  $searchInput.on('input', filterTable);
  $categoryFilter.on('change', filterTable);
});
</script>

</body>
</html>
