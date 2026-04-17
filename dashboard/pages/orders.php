
        <div class="page" id="page-orders">
      <div class="page-header-row">
        <div class="page-header">
          <h1>Order Management</h1>
          <p>Track and manage all candle orders.</p>
        </div>
        <div style="display:flex;gap:10px;">
          <button class="btn btn-outline" onclick="exportReport('csv')"><i class="fas fa-file-excel" style="color:#4a7a44;"></i> Export Spreadsheet</button>
          <button class="btn btn-outline" onclick="exportReport('pdf')"><i class="fas fa-file-pdf" style="color:var(--danger);"></i> Export PDF</button>
        </div>
      </div>
      <div class="filters">
        <input class="search-input" placeholder="Search orders…"/>

        <select class="filter-select" id="orderStatusFilter"><option>All Status</option><option>Pending</option><option>Processing</option><option>Completed</option><option>Cancelled</option></select>

        <select class="filter-select"><option>All Dates</option><option>Today</option><option>This Week</option><option>This Month</option></select>
      </div>
      <div class="card">
        <table class="dashboard-table">
          <thead><tr><th>Order ID</th><th>Customer</th><th>Items</th><th>Total</th><th>Date</th><th>Status</th><th>Actions</th></tr></thead>
          <tbody id="ordersTbody"></tbody>
        </table>
        
      </div>
    </div>
