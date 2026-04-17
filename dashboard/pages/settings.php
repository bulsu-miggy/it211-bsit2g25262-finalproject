<div class="page" id="page-settings">
<div class="page-header">
        <h1>Settings</h1>
        <p>Manage admin accounts.</p>
      </div>
      <div class="settings-grid">
            <div style="display:flex;justify-content:space-between;align-items:center;margin-bottom:16px;">
              <h3 style="font-family:'Cormorant Garamond',serif;font-size:1.3rem;color:var(--espresso);">Admin Users</h3>
              <button class="btn btn-primary btn-sm" onclick="openAdminModal()"><i class="fas fa-plus"></i> Add Admin</button>
            </div>
            <div class="card">
              <table class="dashboard-table">
<thead><tr><th>Name</th><th>Email</th><th>Last Login</th><th>Actions</th></tr></thead>
                <tbody id="adminsTbody"></tbody>
              </table>
            </div>
          </div>
      </div>
    </div>
