<?php
include '../db/connection.php';

// Get the current status filter from the URL, default to 'all'
$status_filter = isset($_GET['status']) ? strtolower($_GET['status']) : 'all';

$where = $status_filter === 'all' ? '' : ' WHERE status = ' . $conn->quote($status_filter);
$stmt = $conn->query("SELECT 
    CONCAT('#ORD-', LPAD(id, 3, '0')) as id,
    customer_name as customer, 
    DATE_FORMAT(order_date, '%Y-%m-%d') as date, 
    total_amount as amount_raw,
    status 
FROM orders" . $where . " 
ORDER BY order_date DESC 
LIMIT 50");

$orders = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Format amounts
foreach ($orders as &$order) {
    $order['amount'] = '$' . number_format($order['amount_raw'], 2);
    unset($order['amount_raw']);
}

$filtered_orders = $orders;
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Orders - LYNX Admin</title>
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
        <li class="active" onclick="location.href='orders.php'">
          <span class="material-icons-outlined">shopping_cart</span>
          Orders
        </li>
        <li onclick="location.href='products.php'">
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
        <small>Store Owner</small>
      </div>
      <a href="../db/action/logout.php" title="Logout" style="color: black; margin-left: auto; display: flex; align-items: center; text-decoration: none;">
        <span class="material-icons-outlined">logout</span>
      </a>
    </div>
  </aside>

  <main class="main">
    <header class="topbar">
      <h1 class="page-title">Dashboard / Orders</h1>
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
        <h2>Orders</h2>
        <p class="subtext">Manage and track all customer orders</p>
      </div>
      <button class="btn-export">
        <span class="material-icons-outlined">file_download</span>
        Export
      </button>
    </div>

    <div class="filter-bar">
      <div class="filter-search">
        <span class="material-icons-outlined">search</span>
        <input type="text" placeholder="Search orders...">
      </div>
      <select class="filter-select" onchange="location.href='orders.php?status=' + this.value">
        <option value="all" <?php echo $status_filter === 'all' ? 'selected' : ''; ?>>All Status</option>
        <option value="completed" <?php echo $status_filter === 'completed' ? 'selected' : ''; ?>>Completed</option>
        <option value="processing" <?php echo $status_filter === 'processing' ? 'selected' : ''; ?>>Processing</option>
        <option value="shipped" <?php echo $status_filter === 'shipped' ? 'selected' : ''; ?>>Shipped</option>
      </select>
    </div>

    <section class="panel full-width">
      <div class="table-container">
        <table class="orders-table">
          <thead>
            <tr>
              <th>Order ID</th>
              <th>Customer</th>
              <th>Date</th>
              <th>Amount</th>
              <th>Status</th>
              <th>Actions</th>
            </tr>
          </thead>
          <tbody>
            <?php foreach ($filtered_orders as $order): ?>
            <tr>
              <td><?php echo $order['id']; ?></td>
              <td><?php echo $order['customer']; ?></td>
              <td><?php echo $order['date']; ?></td>
              <td><?php echo $order['amount']; ?></td>
              <td><span class="badge <?php echo $order['status']; ?>"><?php echo ucfirst($order['status']); ?></span></td>
              <td>
                <select class="status-select" data-id="<?php echo $order['id'] ?? ''; ?>">
                  <option value="pending" <?php echo ($order['status'] ?? '') === 'pending' ? 'selected' : ''; ?>>Pending</option>
                  <option value="processing" <?php echo ($order['status'] ?? '') === 'processing' ? 'selected' : ''; ?>>Processing</option>
                  <option value="shipped" <?php echo ($order['status'] ?? '') === 'shipped' ? 'selected' : ''; ?>>Shipped</option>
                  <option value="completed" <?php echo ($order['status'] ?? '') === 'completed' ? 'selected' : ''; ?>>Completed</option>
                </select>
              </td>
            </tr>
            <?php endforeach; ?>
          </tbody>
        </table>
      </div>
    </section>
  </main>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
  const selects = document.querySelectorAll('.status-select');
  selects.forEach(select => {
    select.addEventListener('change', function() {
      const orderId = this.dataset.id.split('-')[1]; // extract numeric id
      const newStatus = this.value;
      
      fetch('../db/action/update_order_status.php', {
        method: 'POST',
        headers: {'Content-Type': 'application/x-www-form-urlencoded'},
        body: `order_id=${orderId}&status=${newStatus}`
      })
      .then(response => response.text().then(text => {
        if (text.trim().startsWith('<!DOCTYPE')) throw new Error('HTML response');
        return JSON.parse(text);
      }))
      .then(data => {
        if (data.success) {
          this.closest('td').previousElementSibling.querySelector('.badge').textContent = data.status.charAt(0).toUpperCase() + data.status.slice(1);
          this.closest('td').previousElementSibling.className = 'badge ' + data.status;
        } else {
          alert('Update failed: ' + data.error);
          this.value = this.dataset.currentStatus; // revert
        }
      })
      .catch(err => alert('Error: ' + err));
    });
  });
});
</script>
</body>
</html>
