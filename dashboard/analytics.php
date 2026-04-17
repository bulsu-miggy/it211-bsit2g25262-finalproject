<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Analytics - LYNX Admin</title>
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
        <li onclick="location.href='orders.php'">
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
        <li class="active" onclick="location.href='analytics.php'">
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
      <h1 class="page-title">Dashboard / Analytics</h1>
      <div class="top-actions">
        <div class="search-container">
          <span class="material-icons-outlined">calendar_today</span>
          <input type="text" value="Oct 1, 2023 - Oct 7, 2023" readonly style="width: 180px; cursor: pointer; border:none; background:none; font-size:14px;">
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
        <h2>Analytics</h2>
        <p class="subtext">Monitor your store performance and traffic</p>
      </div>
      <button class="btn-export">
        <span class="material-icons-outlined">file_download</span>
        Export Report
      </button>
    </div>

    <section class="panels">
      <div class="panel large chart-view">
        <div class="panel-header">
          <div class="header-group">
            <h3 class="panel-title-text">Sales Overview</h3>
            <p class="subtext">Daily revenue for the current week</p>
          </div>
        </div>
        
        <div class="bar-chart-container">
          <div class="y-axis">
            <span>10k</span><span>7.5k</span><span>5k</span><span>2.5k</span><span>0</span>
          </div>
          <div class="bars">
            <div class="bar-group"><div class="bar h-60"></div><span>Mon</span></div>
            <div class="bar-group"><div class="bar h-85"></div><span>Tue</span></div>
            <div class="bar-group"><div class="bar h-45"></div><span>Wed</span></div>
            <div class="bar-group"><div class="bar h-100"></div><span>Thu</span></div>
            <div class="bar-group"><div class="bar h-70"></div><span>Fri</span></div>
            <div class="bar-group"><div class="bar h-55"></div><span>Sat</span></div>
            <div class="bar-group"><div class="bar h-75"></div><span>Sun</span></div>
          </div>
        </div>
      </div>

      <div class="panel">
        <div class="panel-header">
          <h3 class="panel-title-text">Traffic Sources</h3>
        </div>
        <div class="analytics-mini-chart">
            <div class="donut-placeholder"></div>
            <div class="donut-legend">
                <div class="legend-item"><span class="dot d-black"></span> Direct</div>
                <div class="legend-item"><span class="dot d-gray"></span> Social</div>
                <div class="legend-item"><span class="dot d-light"></span> Others</div>
            </div>
        </div>
      </div>
    </section>

    <section class="panel full-width" style="margin-top: 24px;">
      <div class="panel-header">
        <h3 class="panel-title-text">Top Selling Products</h3>
      </div>
      <div class="table-container">
        <table class="orders-table">
          <thead>
            <tr>
              <th>Product</th>
              <th>Category</th>
              <th>Sales</th>
              <th>Revenue</th>
              <th>Popularity</th>
            </tr>
          </thead>
          <tbody>
            <tr>
              <td>
                <div class="product-cell">
                  <div class="img-placeholder"></div>
                  <span>Basic Top</span>
                </div>
              </td>
              <td>Women</td>
              <td>1,240</td>
              <td class="font-bold">$12,400.00</td>
              <td><div class="progress-bg"><div class="progress-fill" style="width: 85%;"></div></div></td>
            </tr>
            <tr>
              <td>
                <div class="product-cell">
                  <div class="img-placeholder"></div>
                  <span>Cargo Pants</span>
                </div>
              </td>
              <td>Men</td>
              <td>980</td>
              <td class="font-bold">$24,500.00</td>
              <td><div class="progress-bg"><div class="progress-fill" style="width: 70%;"></div></div></td>
            </tr>
          </tbody>
        </table>
      </div>
    </section>
  </main>
</div>

</body>
</html>