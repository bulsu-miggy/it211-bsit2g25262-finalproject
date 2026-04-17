    <div class="page active" id="page-dashboard">
      <div class="page-header">
        <h1>Dashboard</h1>
        <p>Welcome back — here's a snapshot of SOLIS today.</p>
      </div>
      <div class="stats-grid">
        <div class="stat-card">
          <div class="stat-icon ic-caramel" style="float:right"><i class="fas fa-coins"></i></div>
          <div class="s-label">Total Revenue</div>
          <div class="s-value">—</div>
        </div>
        <div class="stat-card">
          <div class="stat-icon ic-green" style="float:right"><i class="fas fa-shopping-basket"></i></div>
          <div class="s-label">Total Orders</div>
          <div class="s-value">—</div>
        </div>
        <div class="stat-card">
          <div class="stat-icon ic-amber" style="float:right"><i class="fas fa-users"></i></div>
          <div class="s-label">Active Customers</div>
          <div class="s-value">—</div>
        </div>
        <div class="stat-card">
          <div class="stat-icon ic-rose" style="float:right"><i class="fas fa-box-open"></i></div>
          <div class="s-label">Low Stock Items</div>
          <div class="s-value">—</div>
        </div>
      </div>
      <div class="charts-grid">
        <div class="chart-card">
          <div class="chart-header">
            <h3>Revenue Overview</h3>
            <div class="tab-pills">
              <div class="tab-pill active" onclick="switchTab(this,'daily')">Daily</div>
              <div class="tab-pill" onclick="switchTab(this,'weekly')">Weekly</div>
              <div class="tab-pill" onclick="switchTab(this,'monthly')">Monthly</div>
            </div>
          </div>
<canvas id="revenueChart" height="140"></canvas>
        </div>
      </div>
      <div class="two-grid">
        <div class="card">
          <div class="card-header"><h3>Best-Selling Candles</h3></div>
<table>
            <thead><tr><th>Product</th><th>No. of Items Sold</th><th>Revenue</th></tr></thead>
            <tbody id="bestSellingTbody">
            </tbody>
          </table>
        </div>
        <div class="card">
          <div class="card-header"><h3>Top Categories</h3></div>
<table style="width: 100%; table-layout: fixed;">
            <thead><tr><th style="width: 40%; text-align: left;">Category</th><th style="width: 40%; text-align: left;">No. of Items Sold</th><th style="width: 20%; text-align: left;">Share</th></tr></thead>
            <tbody id="topCategoriesTbody">
            </tbody>
          </table>
        </div>
      </div>
    </div>