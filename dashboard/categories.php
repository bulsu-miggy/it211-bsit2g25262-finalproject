<?php
// Drill-down logic
$type = isset($_GET['type']) ? strtolower($_GET['type']) : '';
$is_sub = in_array($type, ['men', 'women']);

// Mock data for sub-categories
$sub_categories = [
    'men' => [
        ['name' => 'Basic Tops', 'desc' => 'Essential tees and tanks', 'products' => 45],
        ['name' => 'Shorts', 'desc' => 'Casual and athletic shorts', 'products' => 20],
        ['name' => 'Pants', 'desc' => 'Jeans, chinos, and trousers', 'products' => 30],
        ['name' => 'Outerwear', 'desc' => 'Jackets, hoodies, and coats', 'products' => 15],
    ],
    'women' => [
        ['name' => 'Basic Tops', 'desc' => 'Essential tees and camis', 'products' => 50],
        ['name' => 'Skirts', 'desc' => 'Mini, midi, and maxi skirts', 'products' => 25],
        ['name' => 'Pants', 'desc' => 'Jeans, leggings, and trousers', 'products' => 35],
        ['name' => 'Outerwear', 'desc' => 'Blazers, coats, and jackets', 'products' => 20],
    ]
];

// Set display variables
$display_name = $is_sub ? ucfirst($type) : 'Categories';
$sub_data = $is_sub ? $sub_categories[$type] : [];
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Categories - LYNX Admin</title>
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
    /* New Category Card Styles */
    .category-grid {
      display: grid;
      grid-template-columns: 1fr 1fr;
      gap: 32px;
      margin-top: 24px;
    }
    .category-card {
      background: #fff;
      border: 1px solid var(--border-color);
      border-radius: 12px;
      padding: 60px 40px;
      text-align: center;
      text-decoration: none;
      color: var(--text-main);
      transition: all 0.2s ease;
      display: flex;
      flex-direction: column;
      align-items: center;
      gap: 16px;
    }
    .category-card:hover {
      border-color: var(--accent-black);
      transform: translateY(-4px);
      box-shadow: 0 10px 20px rgba(0,0,0,0.05);
    }
    .category-card .material-icons-outlined {
      font-size: 48px;
    }
    .category-card h3 {
      font-size: 24px;
      text-transform: uppercase;
      letter-spacing: 1px;
    }
    .back-link {
      display: inline-flex;
      align-items: center;
      gap: 4px;
      color: var(--text-muted);
      text-decoration: none;
      margin-bottom: 16px;
      font-size: 14px;
    }
    .back-link:hover {
      color: var(--text-main);
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
        <li onclick="location.href='products.php'">
          <span class="material-icons-outlined">inventory_2</span>
          Products
        </li>
        <li class="active" onclick="location.href='categories.php'">
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
      <h1 class="page-title">Dashboard / <?php echo $is_sub ? "Categories / " . $display_name : "Categories"; ?></h1>
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
        <?php if($is_sub): ?>
          <a href="categories.php" class="back-link">
            <span class="material-icons-outlined">arrow_back</span> Back to Categories
          </a>
        <?php endif; ?>
        <h2><?php echo $display_name; ?></h2>
        <p class="subtext"><?php echo $is_sub ? "Manage sub-categories for $display_name" : "Organize your products into categories"; ?></p>
      </div>
      <button class="btn-export">
        <span class="material-icons-outlined">add</span>
        Add <?php echo $is_sub ? "Sub-category" : "Category"; ?>
      </button>
    </div>

    <?php if(!$is_sub): ?>
    <!-- Step 1: Main Category Selection -->
    <div class="category-grid">
      <a href="?type=men" class="category-card">
        <span class="material-icons-outlined">man</span>
        <h3>MEN</h3>
        <p class="subtext">View all men's collections</p>
      </a>
      <a href="?type=women" class="category-card">
        <span class="material-icons-outlined">woman</span>
        <h3>WOMEN</h3>
        <p class="subtext">View all women's collections</p>
      </a>
    </div>
    <?php else: ?>
    <!-- Step 2: Sub-category View -->
    <div class="filter-bar">
      <div class="filter-search">
        <span class="material-icons-outlined">search</span>
        <input type="text" placeholder="Search <?php echo strtolower($display_name); ?> sub-categories...">
      </div>
    </div>

    <section class="panel full-width">
      <div class="table-container">
        <table class="orders-table">
          <thead>
            <tr>
              <th>Sub-category Name</th>
              <th>Description</th>
              <th>Products</th>
              <th>Status</th>
              <th>Actions</th>
            </tr>
          </thead>
          <tbody>
            <?php foreach($sub_data as $sub): ?>
              <tr>
              <td>
                <div class="product-cell">
                  <div class="img-placeholder"></div>
                  <span><?php echo $sub['name']; ?></span>
                </div>
              </td>
              <td class="text-muted"><?php echo $sub['desc']; ?></td>
              <td><?php echo $sub['products']; ?></td>
              <td><span class="badge completed">Active</span></td>
              <td>
                <div class="action-links">
                  <a href="#" class="view-link">Edit</a>
                  <a href="#" class="delete-link">Delete</a>
                </div>
              </td>
            </tr>
            <?php endforeach; ?>
          </tbody>
        </table>
      </div>
    </section>
    <?php endif; ?>
  </main>
</div>

</body>
</html>