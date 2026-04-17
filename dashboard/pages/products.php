    <div class="page" id="page-products">
      <div class="page-header">
        <h1>Product Management</h1>
        <p>Manage your scented candle catalog.</p>
      </div>
      <div class="filters">
        <input class="search-input" placeholder="Search candles…" oninput="filterProducts(this.value)"/>
        <select class="filter-select" id="categoryFilter" onchange="filterByCategory(this.value)">
          <option value="">All Categories</option>
        </select>
        <select class="filter-select">
          <option>All Stock</option><option>In Stock</option><option>Low Stock</option><option>Out of Stock</option>
        </select>
        <button class="btn btn-primary btn-sm" onclick="openProductModal()" style="margin-left: auto;"><i class="fas fa-plus"></i> Add Candle</button>
      </div>
      <div class="card">
        <table class="dashboard-table">
          <thead><tr><th></th><th>Product</th><th>Scent Notes</th><th>Category</th><th>Price</th><th>Stock</th><th>Status</th><th>Actions</th></tr></thead>
          <tbody id="productsTbody"></tbody>
        </table>
      </div>
    </div>