<?php
session_start();
if (!isset($_SESSION["username"])) {
    header('Location: ../login.php');
    exit();
}
include '../db/connection.php';

$query = "SELECT 
            customer_name, 
            email, 
            COUNT(*) as orders_count, 
            SUM(total_amount) as total_spent, 
            MIN(order_date) as joined_date 
          FROM orders 
          GROUP BY email";

$stmt = $conn->query($query);
$customers = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Format amounts
foreach ($customers as &$cust) {
    // I-store natin sa bagong keys para hindi mag-conflict sa loop
$cust['display_spent'] = '₱' . number_format($cust['total_spent'], 2);
    $cust['display_joined'] = date('Y-m-d', strtotime($cust['joined_date']));
}
unset($cust); // Good practice pagkatapos ng reference loop
?>

<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Customers - LYNX Admin</title>
  <link href="https://fonts.googleapis.com/icon?family=Material+Icons+Outlined" rel="stylesheet">
  <link href="https://fonts.googleapis.com/css2?family=Rubik+Mono+One&display=swap" rel="stylesheet">
  <link rel="stylesheet" href="style.css">
  <style>
    .logo { font-family: 'Rubik Mono One', sans-serif; font-size: 24px; color: black; text-decoration: none; margin: 0; }
    .modal { display: none; position: fixed; z-index: 1000; left: 0; top: 0; width: 100%; height: 100%; background: rgba(0,0,0,0.5); }
    .modal-content { background: white; margin: 5% auto; padding: 30px; border-radius: 12px; width: 90%; max-width: 800px; max-height: 80vh; overflow-y: auto; }
    .close { color: #aaa; float: right; font-size: 28px; font-weight: bold; cursor: pointer; }
    .close:hover { color: black; }
    .badge.completed { background: #e8f5e9; color: #2e7d32; }
    .badge.pending { background: #fff3e0; color: #ef6c00; }
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
        <li onclick="location.href='products.php'"><span class="material-icons-outlined">inventory_2</span> Products</li>
        <li onclick="location.href='categories.php'"><span class="material-icons-outlined">category</span> Categories</li>
        <li class="active" onclick="location.href='customers.php'"><span class="material-icons-outlined">group</span> Customers</li>
        <li onclick="location.href='analytics.php'"><span class="material-icons-outlined">analytics</span> Analytics</li>
      </ul>
    </nav>
    <div class="sidebar-footer">
      <div class="avatar"></div>
      <div class="user-info">
        <strong>Admin</strong>
      </div>
      <a href="../db/action/logout.php" title="Logout" style="color: black; margin-left: auto; display: flex; align-items: center; text-decoration: none;" onclick="return confirm('Are you sure you want to logout?');">
        <span class="material-icons-outlined">logout</span>
      </a>
    </div>
  </aside>

  <main class="main">
    <header class="topbar">
      <h1 class="page-title">Dashboard / Customers</h1>
      <div class="top-actions">
        <div class="search-container">
          <span class="material-icons-outlined">search</span>
          <input type="text" id="customerSearch" placeholder="Search customers...">
        </div>
        <div class="icon-badge">
          <span class="material-icons-outlined">notifications</span>
          <span class="dot" style="background: #ff4d4d; border: 2px solid white; width: 18px; height: 18px;">3</span>
        </div>
        <div class="avatar small"></div>
      </div>
    </header>

    <div class="orders-header">
      <div class="header-text">
        <h2>Customers</h2>
        <p class="subtext">Manage your customer relationships (<?=count($customers)?> total)</p>
      </div>
    </div>

    <section class="panel full-width">
      <div class="table-container">
        <table class="orders-table" id="customersTable">
          <thead>
            <tr>
              <th>Customer</th>
              <th>Email</th>
              <th>Orders</th>
              <th>Total Spent</th>
              <th>Joined</th>
              <th>Actions</th>
            </tr>
          </thead>
          <tbody>
            <?php foreach ($customers as $cust): ?>
            <tr data-customer="<?php echo htmlspecialchars($cust['customer_name'] . ' ' . $cust['email']); ?>">
              <td>
                <div class="product-cell">
                  <div class="avatar small"></div>
                  <span><?php echo htmlspecialchars($cust['customer_name']); ?></span>
                </div>
              </td>
              <td><?php echo htmlspecialchars($cust['email']); ?></td>
              <td><?php echo $cust['orders_count']; ?></td>
              <td><?php echo $cust['display_spent']; ?></td>
              <td><?php echo $cust['display_joined']; ?></td>
              <td>
                <button onclick="showCustomerDetails('<?php echo htmlspecialchars($cust['email']); ?>')" class="view-link" style="background:none; border:none; color: #1976d2; cursor:pointer; text-decoration:underline;">View Details</button>
              </td>
            </tr>
            <?php endforeach; ?>
          </tbody>
        </table>
      </div>
    </section>
  </main>
</div>

<div id="customerModal" class="modal">
  <div class="modal-content">
    <span class="close" onclick="closeCustomerModal()">&times;</span>
    <h2 id="modalTitle">Customer Details</h2>
    <div id="modalContent"></div>
  </div>
</div>

<script>
// (Copy-paste mo lang dito yung scripts mo kanina para sa showCustomerDetails)
</script>
</body>
</html>