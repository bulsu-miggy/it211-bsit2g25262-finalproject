    <div class="page" id="page-categories">
      <div class="page-header">
        <h1>Category Management</h1>
        <p>Organize your candle collections.</p>
      </div>
      <div class="filters">
        <button class="btn btn-primary btn-sm" onclick="openCatModal()" style="margin-left: auto;"><i class="fas fa-plus"></i> Add Category</button>
      </div>
      <div class="card">
        <table class="dashboard-table">
          <thead><tr><th>#</th><th>Category Name</th><th>Slug</th><th>Products</th><th>Created</th><th>Actions</th></tr></thead>
          <tbody id="catTbody"></tbody>
        </table>
      </div>
    </div>