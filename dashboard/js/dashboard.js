// ═══════════════════ CONSOLIDATED DASHBOARD JS ═══════════════════
/**
 * dashboard.js
 * Main controller for the dashboard. Handles data fetching from APIs, 
 * UI rendering, chart initialization, and dashboard-specific logic.
 */

// ═══════════════════ DATA ═══════════════════
let products = [];
let categories = [];
let orders = [];
let customers = [];
let notifiedOutOfStockProductIds = new Set(); // Tracks products for which 'out of stock' notifications have been shown
let admins = [];
let selectedProductImageUrl = null;
let selectedProductImageFile = null;

const productApiUrl = '../db/action/product_api.php';
const categoryApiUrl = '../db/action/category_api.php';
const ordersApiUrl = '../db/action/orders_api.php';
const customersApiUrl = '../db/action/customers_api.php';
const adminsApiUrl = '../db/action/admins_api.php';

/**
 * Fetches all categories from the database and updates the UI state.
 */
async function fetchDashboardCategories() {
  try {
    const response = await fetch(`${categoryApiUrl}?action=list`);
    if (!response.ok) throw new Error('Network response was not ok');
    const data = await response.json();
    if (data.status !== 'success') {
      showToast(data.message || 'Failed to load categories.', 'danger');
      return;
    }

    categories = data.categories.map(c => ({
      ...c,
      id: c.category_id ?? c.id,
      name: c.name,
      slug: c.slug ?? '',
      products: parseInt(c.products ?? 0, 10) || 0,
      created: c.created_at ? new Date(c.created_at).toLocaleDateString('en-US', { month: 'short', day: 'numeric', year: 'numeric' }) : (c.created || '')
    }));

    renderCategories();
    populateCategoryFilters();
    populateProductCategorySelect();
  } catch (error) {
    console.error(error);
    showToast('Unable to load categories from the server.', 'danger');
  }
}

/**
 * Updates the category filter dropdown list based on current data.
 */
function populateCategoryFilters() {
  const filter = document.getElementById('categoryFilter');
  if (!filter) return;
  const selected = filter.value;
  filter.innerHTML = '<option value="">All Categories</option>' + categories.map(c => `<option value="${c.name}">${c.name}</option>`).join('');
  filter.value = selected || '';
}

/**
 * Updates the category selection dropdown in the Product Modal.
 */
function populateProductCategorySelect() {
  const select = document.getElementById('pCat');
  if (!select) return;
  const current = select.value;
  if (categories.length === 0) {
    select.innerHTML = '<option value="">No categories yet</option>';
    return;
  }
  select.innerHTML = categories.map(c => `<option value="${c.name}">${c.name}</option>`).join('');
  select.value = current || select.options[0]?.value || '';
}

/**
 * Fetches sales orders and calculates revenue statistics for the dashboard charts.
 */
async function fetchDashboardOrders() {
  try {
    const response = await fetch(`${ordersApiUrl}?action=list&details=true`);
    if (!response.ok) throw new Error('Network response was not ok');
    const data = await response.json();
    if (data.status !== 'success') {
      showToast(data.message || 'Failed to load orders.', 'danger');
      return;
    }

    orders = data.orders.map(o => ({
      ...o,
      id: o.order_number || o.id // Use order_number or id as display ID
    }));

    // Generate revenue data from orders
    const now = new Date();
    const oneWeek = 7 * 24 * 60 * 60 * 1000;
    const oneMonth = 30 * 24 * 60 * 60 * 1000;

    // Daily (last 7 days)
    const dailyRev = new Array(7).fill(0);
    orders.forEach(o => {
      if (o.date) {
        const orderDate = new Date(o.date);
        const daysAgo = Math.floor((now - orderDate) / (24 * 60 * 60 * 1000));
        if (daysAgo < 7 && daysAgo >= 0) {
          dailyRev[6 - daysAgo] += o.status === 'Completed' ? (Number(o.total) || 0) : 0;
        }
      }
    });
    revData.daily.data = dailyRev;

    // Weekly (last 4 weeks)
    const weeklyRev = new Array(4).fill(0);
    orders.forEach(o => {
      if (o.date) {
        const orderDate = new Date(o.date);
        const weeksAgo = Math.floor((now - orderDate) / oneWeek);
        if (weeksAgo < 4 && weeksAgo >= 0) {
          weeklyRev[3 - weeksAgo] += o.status === 'Completed' ? (Number(o.total) || 0) : 0;
        }
      }
    });
    revData.weekly.data = weeklyRev;

    // Monthly (last 12 months)
    const monthlyRev = new Array(12).fill(0);
    orders.forEach(o => {
      if (o.date) {
        const orderDate = new Date(o.date);
        const monthsAgo = (now.getFullYear() - orderDate.getFullYear()) * 12 + (now.getMonth() - orderDate.getMonth());
        if (monthsAgo < 12 && monthsAgo >= 0) {
          monthlyRev[11 - monthsAgo] += o.status === 'Completed' ? (Number(o.total) || 0) : 0;
        }
      }
    });
    revData.monthly.data = monthlyRev;

    if (revChart) {
      revChart.update();
    }

    renderOrders();
  } catch (error) {
    console.error(error);
    showToast('Unable to load orders from the server.', 'danger');
  }
}

/**
 * Fetches customer profiles and their spending history.
 */
async function fetchDashboardCustomers() {
  try {
    const response = await fetch(`${customersApiUrl}?action=list`);
    if (!response.ok) throw new Error('Network response was not ok');
    const data = await response.json();
    if (data.status !== 'success') {
      showToast(data.message || 'Failed to load customers.', 'danger');
      return;
    }

    customers = data.customers.map(c => ({
      ...c,
      address: c.address || ''
    }));

    renderCustomers();
  } catch (error) {
    console.error(error);
    showToast('Unable to load customers from the server.', 'danger');
  }
}

/**
 * Fetches the list of administrative users.
 */
async function fetchDashboardAdmins() {
  try {
    const response = await fetch(`${adminsApiUrl}?action=list`);
    if (!response.ok) throw new Error('Network response was not ok');
    const data = await response.json();
    if (data.status !== 'success') {
      showToast(data.message || 'Failed to load admins.', 'danger');
      return;
    }

    admins = (data.admins || []).map(a => {
      const rawLastLogin = a.last_login || a.lastLogin || a.lastlogin;
      const isValidDate = rawLastLogin && rawLastLogin !== 'Never' && !isNaN(new Date(rawLastLogin).getTime());
      return {
        ...a,
        name: a.full_name || a.name || 'Admin User',
        lastLogin: isValidDate ? new Date(rawLastLogin).toLocaleString('en-US', { month: 'short', day: 'numeric', year: 'numeric', hour: 'numeric', minute: '2-digit', hour12: true }) : 'Never'
      };
    });

    renderAdmins();
  } catch (error) {
    console.error(error);
    showToast('Unable to load admins from the server.', 'danger');
  }
}

/**
 * Fetches the product catalog and calculates inventory statuses.
 */
async function fetchDashboardProducts() {
  try {
    const response = await fetch(`${productApiUrl}?action=list`);
    if (!response.ok) throw new Error('Network response was not ok');
    const data = await response.json();
    if (data.status !== 'success') {
      showToast(data.message || 'Failed to load dashboard products.', 'danger');
      return;
    }

    products = data.products.map(p => {
      const id = p.product_id ?? p.id;
      const stock = parseInt(p.stock_qty ?? p.stock ?? 0, 10) || 0;
      return {
        ...p,
        id,
        title: p.name ?? p.title,
        category: p.category ?? 'Seasonal',
        scent: p.scent_notes ?? p.scent ?? '',
        description: p.description ?? '',
        image_url: p.image_url ?? p.img_path ?? 'images/solis_signature.png',
        price: parseFloat(p.price) || 0,
        stock,
        status: p.status ?? (stock === 0 ? 'Out of Stock' : (stock <= 10 ? 'Low Stock' : 'In Stock'))
      };
    });

    renderProducts();
    notifyOutOfStockProducts(); // Check for and notify about out-of-stock products
  } catch (error) {
    console.error(error);
    showToast('Unable to load products from the server.', 'danger');
  }
}

// ═══════════════════ AUTH ═══════════════════
function togglePw(inputId, iconEl) {
  const inp = document.getElementById(inputId);
  const ic = typeof iconEl === 'string' ? document.getElementById(iconEl) : iconEl;
  if (inp && ic) {
    inp.type = inp.type === 'password' ? 'text' : 'password';
    ic.className = inp.type === 'text' ? 'fas fa-eye-slash' : 'fas fa-eye';
  }
}

function switchAuthTab(tab) {
  document.querySelectorAll('.tab-btn').forEach(btn => btn.classList.remove('active'));
  document.querySelectorAll('.auth-form').forEach(form => form.classList.remove('active'));
  event.target.classList.add('active');
  document.getElementById(tab + 'Form').classList.add('active');
}

// doLogin() removed - form submits to process_admin_login.php (DB)


function doForgot() {
  const e = document.getElementById('forgotEmail')?.value.trim();
  if (!e) { showToast('Please enter your registered email.', 'danger'); return; }
  const fs = document.getElementById('forgotSuccess');
  const nb = document.getElementById('newPwBlock');
  if (fs) fs.style.display = 'block';
  if (nb) nb.style.display = 'block';
  showToast('Reset link sent! Check your inbox.', 'success');
}

/**
 * Clears session and redirects to logout script.
 */
function doLogout() {
  closeModal('logoutModal');
  const emailField = document.getElementById('loginEmail');
  const passField = document.getElementById('loginPass');
  if (emailField) emailField.value = '';
  if (passField) passField.value = '';
  window.location.href = 'logout.php';
  showToast('You have been signed out. See you soon! 🕯️', 'info');
}

/**
 * Real-time validation for admin password strength.
 */
function checkRules(v, set) {
  // Only target the first rule (Length) and remove others
  const ids = set === 'pw' ? ['r1'] : ['a1'];
  const tests = [/^.{8,}$/];
  ids.forEach((id, i) => {
    const el = document.getElementById(id);
    if (!el) return;
    const pass = tests[i].test(v);
    el.className = pass ? 'ok' : 'fail';
  });
}

/**
 * Checks for products that are 'Out of Stock' and displays a toast notification once per session.
 */
function notifyOutOfStockProducts() {
  products.forEach(p => {
    // If a product is out of stock and we haven't notified for it yet in this session
    if (p.status === 'Out of Stock' && !notifiedOutOfStockProductIds.has(p.id)) {
      showToast(`"${p.title}" is now Out of Stock!`, 'danger');
      notifiedOutOfStockProductIds.add(p.id); // Add to set to prevent future notifications for this product in this session
    }
  });
}

// ═══════════════════ NAV ═══════════════════
/**
 * Navigation function to switch between dashboard page sections.
 */
function goTo(page, el) {
  document.querySelectorAll('.page').forEach(p => p.classList.remove('active'));
  const targetPage = document.getElementById('page-' + page);
  if (targetPage) targetPage.classList.add('active');
  document.querySelectorAll('.nav-item').forEach(n => n.classList.remove('active'));
  if (el) el.classList.add('active');
  else document.querySelectorAll('.nav-item').forEach(n => {
    if ((n.getAttribute('onclick') || '').includes(`'${page}'`)) n.classList.add('active');
  });
}

// ═══════════════════ MODALS ═══════════════════
function openModal(id) {
  const m = document.getElementById(id);
  if (m) m.classList.add('active');
}

function closeModal(id) {
  const m = document.getElementById(id);
  if (m) m.classList.remove('active');
}

// ═══════════════════ TOAST ═══════════════════
/**
 * Utility to show non-intrusive notifications.
 */
function showToast(msg, type = 'success') {
  const icons = {success: 'check-circle', danger: 'times-circle', warning: 'exclamation-triangle', info: 'info-circle'};
  const colors = {success: '#6a9664', danger: '#c97070', warning: '#d9a84e', info: '#5a87a8'};
  const t = document.createElement('div');
  t.className = `toast ${type}`;
  t.innerHTML = `<i class="fas fa-${icons[type]}" style="color:${colors[type]};font-size:1.05rem;flex-shrink:0;"></i><span style="font-size:.875rem;font-weight:500;color:var(--text);">${msg}</span>`;
  const tc = document.getElementById('toastContainer');
  if (tc) tc.appendChild(t);
  setTimeout(() => t.remove(), 3600);
}

// ═══════════════════ CHARTS ═══════════════════
let revChart, pieChart;
const revData = {
  daily: {labels: ['Mon', 'Tue', 'Wed', 'Thu', 'Fri', 'Sat', 'Sun'], data: []},
  weekly: {labels: ['Wk1', 'Wk2', 'Wk3', 'Wk4'], data: []},
  monthly: {labels: ['Jan', 'Feb', 'Mar', 'Apr', 'May', 'Jun', 'Jul', 'Aug', 'Sep', 'Oct', 'Nov', 'Dec'], data: []},
};

/**
 * Initializes the Chart.js revenue visualization.
 */
function initCharts() {
  const rc = document.getElementById('revenueChart');
  if (!rc) return;
  if (revChart) revChart.destroy();
  const rCtx = rc.getContext('2d');
  const grad = rCtx.createLinearGradient(0, 0, 0, 200);
  grad.addColorStop(0, 'rgba(184,137,90,.25)');
  grad.addColorStop(1, 'rgba(184,137,90,.03)');
  revChart = new Chart(rCtx, {type:'bar',data:{labels:revData.daily.labels,datasets:[{label:'Revenue (₱)',data:revData.daily.data,backgroundColor:grad,borderColor:'#b8895a',borderWidth:2,borderRadius:8,}]},options:{responsive:true,plugins:{legend:{display:false}},scales:{y:{beginAtZero:true,grid:{color:'#f0e8de'},ticks:{color:'#9a7d68',font:{family:'Jost'}}},x:{grid:{display:false},ticks:{color:'#9a7d68',font:{family:'Jost'}}}}}});
}

/**
 * Switches the chart view (Daily/Weekly/Monthly).
 */
function switchTab(el, key) {
  document.querySelectorAll('.tab-pill').forEach(p => p.classList.remove('active'));
  el.classList.add('active');
  if (revChart) { revChart.data.labels = revData[key].labels; revChart.data.datasets[0].data = revData[key].data; revChart.update(); }
}

// ═══════════════════ DASHBOARD STATS ═══════════════════
/**
 * Updates the numerical summary cards at the top of the dashboard.
 */
function updateDashboardStats() {
  const statsGrid = document.querySelector('.stats-grid');
  if (!statsGrid) return;

  // Total Orders
  const totalOrdersCard = Array.from(statsGrid.children).find(card => 
    card.querySelector('.s-label')?.textContent?.trim() === 'Total Orders'
  );
  if (totalOrdersCard) {
    const valueEl = totalOrdersCard.querySelector('.s-value');
    const changeEl = totalOrdersCard.querySelector('.s-change');
    if (valueEl) valueEl.textContent = orders.length.toLocaleString() || '0';
    
    if (changeEl) changeEl.innerHTML = '<i class="fas fa-arrow-up"></i> +12%';
  }

  // Total Revenue
  const revenueCard = Array.from(statsGrid.children).find(card => 
    card.querySelector('.s-label')?.textContent?.trim() === 'Total Revenue'
  );
  if (revenueCard) {
    const valueEl = revenueCard.querySelector('.s-value');
    const changeEl = revenueCard.querySelector('.s-change');
    const totalRevenue = orders.reduce((sum, o) => o.status === 'Completed' ? sum + (o.total || 0) : sum, 0);
    if (valueEl) valueEl.textContent = '₱' + totalRevenue.toLocaleString();
    
    if (changeEl) changeEl.innerHTML = '<i class="fas fa-arrow-up"></i> +18%';
  }

  // Active Customers
  const customersCard = Array.from(statsGrid.children).find(card => 
    card.querySelector('.s-label')?.textContent?.trim() === 'Active Customers'
  );
  if (customersCard) {
    const valueEl = customersCard.querySelector('.s-value');
    const changeEl = customersCard.querySelector('.s-change');
    if (valueEl) valueEl.textContent = customers.length.toLocaleString() || '0';
    
    if (changeEl) changeEl.innerHTML = '<i class="fas fa-arrow-up"></i> +5%';
  }

  // Low Stock Items
  const lowStockCard = Array.from(statsGrid.children).find(card => 
    card.querySelector('.s-label')?.textContent?.trim() === 'Low Stock Items'
  );
  if (lowStockCard) {
    const valueEl = lowStockCard.querySelector('.s-value');
    const changeEl = lowStockCard.querySelector('.s-change');
    const lowStock = products.filter(p => p.stock <= 10).length;
    if (valueEl) valueEl.textContent = lowStock || '0';
    
    // Dynamic change
    let changeText = '0';
    let changeClass = lowStock > 3 ? 'up' : 'dn';
    let changeIcon = lowStock > 3 ? 'fa-arrow-up' : lowStock > 1 ? 'fa-exclamation-circle' : 'fa-minus';
    if (lowStock > 8) changeText = '+3';
    else if (lowStock > 3) changeText = '+1';
    else if (lowStock > 0) changeText = '-1';
    
    if (changeEl) changeEl.innerHTML = `<i class="fas ${changeIcon}"></i> ${changeText}`;
    if (changeEl) changeEl.className = `s-change ${changeClass}`;
  }

// Render best-selling and top categories tables
  renderBestSelling();
  renderTopCategories();
  updateSidebarNotifications();
 }

/**
 * Updates the sidebar notification badge for out-of-stock products.
 * Dynamically updates or hides the badge based on current product inventory.
 */
function updateSidebarNotifications() {
  const navItems = document.querySelectorAll('.nav-item');
  const productsLink = Array.from(navItems).find(el => el.innerText.includes('Products'));

  if (!productsLink) return;

  const count = products.filter(p => p.stock <= 10).length;
  let badge = productsLink.querySelector('.badge, .nav-badge, .dot');

  if (count > 0) {
    if (!badge) {
      badge = document.createElement('span');
      badge.className = 'badge badge-danger'; // Matches existing dashboard styling
      badge.style.marginLeft = 'auto';
      productsLink.appendChild(badge);
    }
    badge.textContent = count;
    badge.style.display = 'inline-block';
  } else if (badge) {
    badge.style.display = 'none';
  }
}

/**
 * Renders the "Best Selling Products" table based on order data.
 */
function renderBestSelling() {
  const tbody = document.getElementById('bestSellingTbody');
  if (!tbody) return;

  // Aggregate sales from orders
  const productSales = {};
  orders.forEach(o => {
    if (Array.isArray(o.items_details) && o.status === 'Completed') {
      o.items_details.forEach(item => {
        const name = item.name;
        const qty = parseInt(item.qty) || 1;
        const rev = (parseFloat(item.price) || 0) * qty;
        if (!productSales[name]) productSales[name] = {sold: 0, revenue: 0};
        productSales[name].sold += qty;
        productSales[name].revenue += rev;
      });
    }
  });

  const topProducts = Object.entries(productSales)
    .sort(([,a], [,b]) => b.revenue - a.revenue)
    .slice(0, 5);

  tbody.innerHTML = topProducts.map(([name, data]) => `
    <tr>
      <td style="text-align: left; font-weight: 500; color: var(--espresso);">${name}</td>
      <td style="text-align: left;">${data.sold}</td>
      <td style="text-align: left; font-weight: 600; color: var(--caramel);">₱${Math.round(data.revenue).toLocaleString()}</td>
    </tr>
  `).join('') || '<tr><td colspan="3" style="text-align:center;color:var(--muted);">No sales data</td></tr>'; 
}

/**
 * Renders the "Top Categories" distribution table.
 */
function renderTopCategories() {
  const tbody = document.getElementById('topCategoriesTbody');
  if (!tbody) return;

  // Aggregate category sales from completed orders using product name → category mapping
  const catSales = {};
  orders.forEach(o => {
    if (Array.isArray(o.items_details) && o.status === 'Completed') {
      o.items_details.forEach(item => {
        const productName = item.name.trim();
        const qty = parseInt(item.qty) || 1;
        
        // Find matching product by name (title or name field)
        const product = products.find(p => 
          (p.title && p.title.toLowerCase().includes(productName.toLowerCase())) ||
          (p.name && p.name.toLowerCase().includes(productName.toLowerCase()))
        );
        
        const cat = product ? product.category : 'Uncategorized';
        
        if (!catSales[cat]) {
          catSales[cat] = { items: 0 };
        }
        catSales[cat].items += qty;  // Use qty, not 1
      });
    }
  });

  const totalItems = Object.values(catSales).reduce((sum, cat) => sum + cat.items, 0);
  const topCats = Object.entries(catSales)
    .map(([name, data]) => ({
      name,
      items: data.items,
      share: totalItems ? Math.round((data.items / totalItems) * 100) : 0
    }))
    .sort((a, b) => b.items - a.items)
    .slice(0, 5);

  tbody.innerHTML = topCats.length ? 
    topCats.map(cat => `
      <tr>
        <td style="text-align: left; font-weight: 500; color: var(--espresso);">${cat.name}</td>
        <td style="text-align: left;">${cat.items}</td>
        <td style="text-align: left; font-weight: 600; color: var(--mocha);">${cat.share}%</td>
      </tr>
    `).join('') : 
    '<tr><td colspan="3" style="text-align:center;color:var(--muted);">No category sales data</td></tr>'; 
}

// ═══════════════════ RENDER ═══════════════════
function renderAll() { 
  renderProducts(); 
  renderCategories(); 
  renderOrders(); 
  renderCustomers(); 
  renderAdmins(); 
  updateDashboardStats(); 
  if (document.getElementById('revenueChart')) initCharts();
}

/**
 * Generates HTML for status badges.
 */
function sBadge(s) {
  const m = {'In Stock':'success','Low Stock':'warning','Out of Stock':'danger','Paid':'success','Pending':'warning','Cancelled':'danger','Shipped':'info'};
  return `<span class="badge badge-${m[s]||'muted'}">${s}</span>`;
}

/**
 * Populates the products management table.
 */
function renderProducts(data) {
  const rows = data || products;
  const ptb = document.getElementById('productsTbody');
  if (!ptb) return;
  ptb.innerHTML = rows.map(p => `<tr><td><div class="product-thumb"><i class="fas fa-fire"></i></div></td><td><div style="font-weight:600;color:var(--espresso);">${p.title}</div><div style="font-size:.72rem;color:var(--muted);">ID #${p.id}</div></td><td>${p.scent.split(',').map(s => `<span class="scent-tag">${s.trim()}</span>`).join('')}</td><td>${p.category}</td><td><b style="color:var(--caramel);">₱${p.price.toLocaleString()}</b></td><td>${p.stock} pcs ${p.stock<=10?'<i class="fas fa-exclamation-circle" style="color:var(--danger);font-size:.8rem;"></i>':''}</td><td>${sBadge(p.status)}</td><td><div class="actions"><button class="btn btn-outline btn-sm" onclick="openProductModal(${p.id})"><i class="fas fa-edit"></i></button><button class="btn btn-danger btn-sm" onclick="confirmDel('product',${p.id},'${p.title}')"><i class="fas fa-trash"></i></button></div></td></tr>`).join('');
}

function renderCategories() {
  const ctb = document.getElementById('catTbody');
  if (!ctb) return;
  ctb.innerHTML = categories.map(c => `<tr><td style="color:var(--muted);">${c.id}</td><td><b style="color:var(--espresso);">${c.name}</b></td><td><code style="background:var(--cream);padding:2px 8px;border-radius:6px;font-size:.78rem;color:var(--mocha);">${c.slug}</code></td><td>${c.products} items</td><td style="color:var(--muted);font-size:.82rem;">${c.created}</td><td><div class="actions"><button class="btn btn-outline btn-sm" onclick="openCatModal(${c.id})"><i class="fas fa-edit"></i></button><button class="btn btn-danger btn-sm" onclick="confirmDel('category',${c.id},'${c.name}')"><i class="fas fa-trash"></i></button></div></td></tr>`).join('');
}

function renderOrders() {
  const otb = document.getElementById('ordersTbody');
  if (!otb) return;
  otb.innerHTML = orders.map(o => `<tr><td><b style="color:var(--mocha);">${o.order_number}</b></td><td>${o.customer}</td><td>${o.items} item${o.items>1?'s':''}</td><td><b style="color:var(--caramel);">₱${(Number(o.total) || 0).toLocaleString()}</b></td><td style="color:var(--muted);font-size:.82rem;">${o.date}</td><td>${sBadge(o.status)}</td><td><button class="btn btn-outline btn-sm" onclick="viewOrder('${o.order_number}')"><i class="fas fa-eye"></i> View</button></td></tr>`).join('');
}

function renderCustomers() {
  const cstb = document.getElementById('customersTbody');
  if (!cstb) return;

  cstb.innerHTML = customers.map(c => `<tr><td><b>${c.name}</b></td><td style="color:var(--muted);font-size:.82rem;">${c.email}</td><td>${c.orders}</td><td><b style="color:var(--caramel);">₱${(Number(c.spent) || 0).toLocaleString()}</b></td><td style="color:var(--muted);font-size:.82rem;">${c.joined}</td><td><button class="btn btn-outline btn-sm" onclick="viewCustomer(${c.id})"><i class="fas fa-eye"></i></button></td></tr>`).join('');
}

function renderAdmins() {
  const atb = document.getElementById('adminsTbody');
  if (!atb) return;
  atb.innerHTML = admins.map(a => `<tr><td><b>${a.name}</b></td><td style="color:var(--muted);font-size:.82rem;">${a.email}</td><td><span class="badge badge-purple">${a.role}</span></td><td style="color:var(--muted);font-size:.78rem;">${a.lastLogin}</td><td><div class="actions"><button class="btn btn-outline btn-sm" onclick="openAdminModal(${a.id})"><i class="fas fa-edit"></i></button>${parseInt(a.id) !== 1 ? `<button class="btn btn-danger btn-sm" onclick="confirmDel('admin',${a.id},'${a.name}')"><i class="fas fa-trash"></i></button>` : '<span style="font-size:.72rem;color:var(--muted);">Protected</span>'}</div></td></tr>`).join('');
}

// Product modal, save functions, etc.
let editPid = null;
function openProductModal(id) {
  editPid = id || null;
  populateProductCategorySelect();
  const preview = document.getElementById('imgPreview');
  if (preview) preview.innerHTML = '';
  const imgInput = document.getElementById('imgInput');
  if (imgInput) imgInput.value = '';
  selectedProductImageUrl = null;
  selectedProductImageFile = null;

  if (id) {
    const p = products.find(x => x.id === id);
    if (p) {
      const pmt = document.getElementById('pModalTitle'); if (pmt) pmt.textContent = 'Edit Candle';
      const pt = document.getElementById('pTitle'); if (pt) pt.value = p.title;
      const ps = document.getElementById('pScent'); if (ps) ps.value = p.scent;
      const pc = document.getElementById('pCat');
      if (pc) {
        let opt = Array.from(pc.options).find(o => o.value === p.category);
        if (!opt && p.category) {
          opt = document.createElement('option');
          opt.value = p.category;
          opt.textContent = p.category;
          pc.appendChild(opt);
        }
        pc.value = p.category;
      }
      const pp = document.getElementById('pPrice'); if (pp) pp.value = p.price;
      const pst = document.getElementById('pStock'); if (pst) pst.value = p.stock;
      const pd = document.getElementById('pDesc'); if (pd) pd.value = p.description || '';
      selectedProductImageFile = null;
      selectedProductImageUrl = p.image_url || p.img_path || null;
      if (selectedProductImageUrl && preview) {
        const d = document.createElement('div'); d.className = 'img-thumb';
        d.innerHTML = `<img src="${selectedProductImageUrl}" style="width:100%;height:100%;object-fit:cover;border-radius:10px;"/> <div class="rm" onclick="this.parentNode.remove();selectedProductImageUrl=null;selectedProductImageFile=null;document.getElementById('imgInput').value='';">✕</div>`;
        preview.appendChild(d);
      }
    }
  } else {
    const pmt = document.getElementById('pModalTitle'); if (pmt) pmt.textContent = 'Add Candle Product';
    ['pTitle','pScent','pDesc','pPrice','pStock'].forEach(id => { const el = document.getElementById(id); if (el) el.value = ''; });
    const pc = document.getElementById('pCat'); if (pc) pc.value = pc.options[0]?.value || '';
  }
  openModal('productModal');
}

/**
 * Sends product data to the server for creation or update.
 */
async function saveProduct() {
  const title = document.getElementById('pTitle')?.value.trim();
  const scent = document.getElementById('pScent')?.value.trim() || 'Signature Blend';
  const description = document.getElementById('pDesc')?.value.trim() || '';
  const price = parseFloat(document.getElementById('pPrice')?.value);
  const stock = parseInt(document.getElementById('pStock')?.value, 10);
  const category = document.getElementById('pCat')?.value;

  if (!title || isNaN(price) || isNaN(stock) ) {
    showToast('Please fill all required fields.', 'danger');
    return;
  }

  const status = stock === 0 ? 'Out of Stock' : stock <= 10 ? 'Low Stock' : 'In Stock';
  const action = editPid ? 'update' : 'create';
  const formData = new FormData();
  formData.append('action', action);
  if (editPid) {
    formData.append('product_id', editPid);
  }
  formData.append('name', title);
  formData.append('price', price);
  formData.append('category', category);
  formData.append('scent_notes', scent);
  formData.append('description', description);
  formData.append('stock_qty', stock);
  formData.append('status', status);

  if (selectedProductImageFile) {
    formData.append('image_file', selectedProductImageFile);
  } else if (selectedProductImageUrl) {
    formData.append('image_url', selectedProductImageUrl);
  } else {
    formData.append('image_url', 'images/solis_signature.png');
  }

  try {
    const response = await fetch(productApiUrl, {
      method: 'POST',
      body: formData
    });
    const data = await response.json();

    if (data.status !== 'success') {
      showToast(data.message || 'Unable to save product.', 'danger');
      return;
    }

    showToast(editPid ? `"${title}" updated!` : `"${title}" added to catalog! 🕯️`, 'success');
    await fetchDashboardProducts();
    closeModal('productModal');
  } catch (error) {
    console.error(error);
    showToast('Server error while saving product.', 'danger');
  }
}

function previewImgs(e) {
  const c = document.getElementById('imgPreview');
  if (!c) return;
  c.innerHTML = '';
  const file = e.target.files[0] || null;
  selectedProductImageFile = file;
  selectedProductImageUrl = null;

  if (!file) {
    return;
  }

  const reader = new FileReader();
  reader.onload = ev => {
    selectedProductImageUrl = ev.target.result;
    const d = document.createElement('div'); d.className = 'img-thumb';
    d.innerHTML = `<img src="${ev.target.result}" style="width:100%;height:100%;object-fit:cover;border-radius:10px;"/> <div class="rm" onclick="this.parentNode.remove();selectedProductImageUrl=null;selectedProductImageFile=null;document.getElementById('imgInput').value='';">✕</div>`;
    c.appendChild(d);
  };
  reader.readAsDataURL(file);
}

function filterProducts(q) { renderProducts(q ? products.filter(p => p.title.toLowerCase().includes(q.toLowerCase()) || p.scent.toLowerCase().includes(q.toLowerCase())) : products); }

function filterByCategory(cat) { renderProducts(cat ? products.filter(p => p.category === cat) : products); }

// ═══════════════════ CATEGORY MODAL ═══════════════════
/**
 * Opens the category editor modal.
 */
let editCid = null;
function openCatModal(id) {
  editCid = id || null;
  if (id) {
    const c = categories.find(x => x.id === id);
    if (c) {
    const cmt = document.getElementById('catModalTitle'); if (cmt) cmt.textContent = 'Edit Category';
    const cn = document.getElementById('catName'); if (cn) cn.value = c.name;
    const cs = document.getElementById('catSlug'); if (cs) cs.value = c.slug;
    const cd = document.getElementById('catDesc'); if (cd) cd.value = c.description || '';
  }
  } else {
    const cmt = document.getElementById('catModalTitle'); if (cmt) cmt.textContent = 'Add Category';
    const cn = document.getElementById('catName'); if (cn) cn.value = '';
    const cs = document.getElementById('catSlug'); if (cs) cs.value = '';
    const cd = document.getElementById('catDesc'); if (cd) cd.value = '';
  }
  openModal('catModal');
}

/**
 * Sends category data to the server API.
 */
async function saveCat() {
  const name = document.getElementById('catName')?.value.trim();
  const slug = document.getElementById('catSlug')?.value.trim();
  const description = document.getElementById('catDesc')?.value.trim() || '';
  if (!name) { showToast('Category name required.','danger'); return; }

  const action = editCid ? 'update' : 'create';
  const body = {
    action,
    name,
    slug,
    description,
    ...(editCid ? { category_id: editCid } : {})
  };

  try {
    const response = await fetch(categoryApiUrl, {
      method: 'POST',
      headers: { 'Content-Type': 'application/json' },
      body: JSON.stringify(body)
    });
    const data = await response.json();
    if (data.status !== 'success') {
      showToast(data.message || 'Unable to save category.', 'danger');
      return;
    }
    showToast(editCid ? `"${name}" updated!` : `"${name}" added!`, 'success');
    await fetchDashboardCategories();
    await fetchDashboardProducts();
    await fetchDashboardOrders();
    closeModal('catModal');
  } catch (error) {
    console.error(error);
    showToast('Server error while saving category.', 'danger');
  }
}

/**
 * Opens the admin user modal.
 */
let editAid = null;
function openAdminModal(id) {
  // Hide unused password rule indicators (Uppercase, Number, Special)
  ['a2', 'a3', 'a4'].forEach(ruleId => {
    const el = document.getElementById(ruleId);
    if (el) el.style.display = 'none';
  });

  editAid = id || null;
  if (id) {
    const a = admins.find(x => x.id === id);
    if (a) {
      const amt = document.getElementById('adminModalTitle'); if (amt) amt.textContent = 'Edit Admin';
      const af = document.getElementById('aFname'); if (af) af.value = a.name.split(' ')[0];
      const al = document.getElementById('aLname'); if (al) al.value = a.name.split(' ').slice(1).join(' ');
      const ae = document.getElementById('aEmail'); if (ae) ae.value = a.email;
      const ap = document.getElementById('aPass'); if (ap) ap.value = '';
    }
  } else {
    const amt = document.getElementById('adminModalTitle'); if (amt) amt.textContent = 'Add Admin User';
    ['aFname','aLname','aEmail','aPass'].forEach(id => { const el = document.getElementById(id); if (el) el.value = ''; });
  }
  openModal('adminModal');
}

/**
 * Saves admin user details to the server.
 */
async function saveAdmin() {
  const fn = document.getElementById('aFname')?.value.trim();
  const ln = document.getElementById('aLname')?.value.trim();
  const email = document.getElementById('aEmail')?.value.trim();
  const pass = document.getElementById('aPass')?.value;
  if (!fn || !email) { showToast('Name and email are required.','danger'); return; }
  if (!editAid && pass && pass.length < 8) { showToast('Password must be at least 8 characters.','danger'); return; }
  const name = `${fn} ${ln}`.trim();
  const action = editAid ? 'update' : 'create';
  const body = {
    action,
    full_name: name,
    email,
    role: 'Super Admin',
    ...(editAid ? {id: editAid} : {}),
    ...(pass ? {password: pass} : {})
  };

  try {
    const response = await fetch(adminsApiUrl, {
      method: 'POST',
      headers: {'Content-Type': 'application/json'},
      body: JSON.stringify(body)
    });
    const data = await response.json();
    if (data.status !== 'success') {
      showToast(data.message || 'Failed to save admin.', 'danger');
      return;
    }
    showToast(`"${name}" ${action === 'update' ? 'updated' : 'added'}!`, 'success');
    await fetchDashboardAdmins();
    closeModal('adminModal');
  } catch (error) {
    console.error(error);
    showToast('Server error saving admin.', 'danger');
  }
}

// ═══════════════════ ORDER VIEW ═══════════════════
/**
 * Displays a detailed summary of a specific order in a modal.
 */
function viewOrder(id) {
  const o = orders.find(x => x.order_number === id);
  if (!o) return;
  const omb = document.getElementById('orderModalBody');
  if (!omb) return;
  omb.innerHTML = `
    <div style="font-family:'Cormorant Garamond',serif;font-size:1.4rem;margin-bottom:12px;">Order #${id}</div>
    <div style="display:flex;gap:16px;margin-bottom:20px;">
      <div style="flex:1;">
        <div style="font-size:.7rem;color:var(--muted);margin-bottom:4px;">Status</div>
        <span class="badge badge-${o.status === 'Pending' ? 'warning' : o.status === 'Processing' ? 'info' : o.status === 'Shipped' ? 'success' : 'danger'}">${o.status}</span>
      </div>
      <div style="flex:1;">
        <div style="font-size:.7rem;color:var(--muted);margin-bottom:4px;">Total</div>
        <b style="font-size:1.3rem;color:var(--caramel);">₱${(Number(o.total) || 0).toLocaleString()}</b>
      </div>
    </div>
    <div style="margin-bottom:16px;">
      <div style="font-size:.8rem;color:var(--muted);margin-bottom:8px;">Items</div>
      <div style="background:var(--cream);padding:12px;border-radius:8px;">
${ (o.items_details || []).map(item => `<div style="display:flex;gap:12px;align-items:center;margin-bottom:8px;"><div style="width:42px;height:42px;border-radius:8px;background:var(--latte);display:flex;align-items:center;justify-content:center;color:#fff;font-weight:600;">${item.qty}</div><div style="flex:1;"><div style="font-weight:600;">${item.name}</div><div style="font-size:.78rem;color:var(--muted);">₱${item.price.toLocaleString()}</div></div></div>`).join('') || '<div style="color:var(--muted);font-style:italic;">No detailed item breakdown available</div>' }

      </div>
    </div>
    <div style="display:grid;grid-template-columns:1fr 1fr;gap:16px;font-size:.85rem;">
      <div><div style="font-size:.7rem;color:var(--muted);">Date</div>${o.date}</div>
      <div><div style="font-size:.7rem;color:var(--muted);">Customer</div>${o.customer}</div>
    </div>
    <div style="margin-top:20px;padding-top:16px;border-top:1px solid var(--border);">
      <div style="font-size:.7rem;color:var(--muted);">Status Actions</div>
      <div style="display:flex;gap:8px;flex-wrap:wrap;margin-top:8px;">
                <button class="btn btn-warning btn-sm" onclick="updateOrder('${o.order_number}','Pending')">Pending</button>
                <button class="btn btn-success btn-sm" onclick="updateOrder('${o.order_number}','Processing')">Processing</button>
                <button class="btn btn-info btn-sm" onclick="updateOrder('${o.order_number}','Completed')">Completed</button>
                <button class="btn btn-danger btn-sm" onclick="updateOrder('${o.order_number}','Cancelled')">Cancelled</button>
      </div>
    </div>`;
  openModal('orderModal');
}

/**
 * Updates an order's fulfillment status via API.
 */
async function updateOrder(id, status) {
  try {
    const formData = new FormData();
    formData.append('action', 'update_status');
    formData.append('order_id', id.replace('SOLIS-', '')); // Extract numeric ID
    formData.append('status', status);

    const response = await fetch(ordersApiUrl, {
      method: 'POST',
      body: formData
    });
    const data = await response.json();

    if (data.status !== 'success') {
      showToast(data.message || 'Failed to update order status.', 'danger');
      return;
    }

    // Update local data
    const orderIndex = orders.findIndex(o => o.order_number === id);
    if (orderIndex !== -1) {
      orders[orderIndex].status = status;
      renderOrders();
    }

    closeModal('orderModal');
    showToast(`Order ${id} → ${status}!`, 'success');
  } catch (error) {
    console.error(error);
    showToast('Server error while updating order.', 'danger');
  }
}

async function deleteDashboardCategory(id, name) {
  try {
    const response = await fetch(categoryApiUrl, {
      method: 'POST',
      headers: {'Content-Type': 'application/json'},
      body: JSON.stringify({ action: 'delete', category_id: id })
    });
    const data = await response.json();
    if (data.status !== 'success') {
      showToast(data.message || 'Unable to delete category.', 'danger');
      return;
    }
    showToast(`"${name}" has been removed.`, 'success');
    await fetchDashboardCategories();
    await fetchDashboardProducts();
    await fetchDashboardOrders();
    closeModal('confirmModal');
  } catch (error) {
    console.error(error);
    showToast('Server error while deleting category.', 'danger');
  }
}

// ═══════════════════ CUSTOMER VIEW ═══════════════════
/**
 * Displays a customer's profile and summary in a modal.
 */
function viewCustomer(id) {
  const c = customers.find(x => x.id === id);
  if (!c) return;
  const cmb = document.getElementById('customerModalBody');
  if (!cmb) return;
  cmb.innerHTML = `<div style="display:flex;align-items:center;gap:16px;margin-bottom:22px;padding-bottom:18px;border-bottom:1px solid var(--border);"><div class="avatar" style="width:60px;height:60px;font-size:1.3rem;">${(c.name || 'User').split(' ').map(w=>w[0]).join('')}</div><div><div style="font-family:'Cormorant Garamond',serif;font-size:1.4rem;font-weight:700;color:var(--espresso);">${c.name}</div><div style="color:var(--muted);font-size:.85rem;">Customer since ${c.joined}</div></div></div><div style="display:grid;grid-template-columns:1fr 1fr;gap:12px;"><div style="background:var(--cream);border-radius:10px;padding:14px;border:1px solid var(--border);"><div style="font-size:.7rem;font-weight:700;color:var(--muted);text-transform:uppercase;margin-bottom:4px;">Email</div><div style="font-size:.875rem;">${c.email}</div></div><div style="background:var(--cream);border-radius:10px;padding:14px;border:1px solid var(--border);"><div style="font-size:.7rem;font-weight:700;color:var(--muted);text-transform:uppercase;margin-bottom:4px;">Total Orders</div><div style="font-family:'Cormorant Garamond',serif;font-size:1.8rem;font-weight:700;color:var(--caramel);">${c.orders}</div></div><div style="background:var(--cream);border-radius:10px;padding:14px;border:1px solid var(--border);"><div style="font-size:.7rem;font-weight:700;color:var(--muted);text-transform:uppercase;margin-bottom:4px;">Total Spent</div><div style="font-family:'Cormorant Garamond',serif;font-size:1.8rem;font-weight:700;color:var(--success);">₱${(Number(c.spent) || 0).toLocaleString()}</div></div><div style="background:var(--cream);border-radius:10px;padding:14px;border:1px solid var(--border);grid-column:1/-1;"><div style="font-size:.7rem;font-weight:700;color:var(--muted);text-transform:uppercase;margin-bottom:4px;">Saved Address</div><div><i class="fas fa-map-marker-alt" style="color:var(--caramel);margin-right:6px;"></i>${c.address}</div></div></div>`;
  openModal('customerModal');
}

// ═══════════════════ DELETE ═══════════════════
async function deleteDashboardAdmin(id, name) {
  try {
    const response = await fetch(adminsApiUrl, {
      method: 'POST',
      headers: {'Content-Type': 'application/json'},
      body: JSON.stringify({ action: 'delete', id })
    });
    const data = await response.json();
    if (data.status !== 'success') {
      showToast(data.message || 'Failed to delete admin.', 'danger');
      return;
    }
    showToast(`"${name}" deleted.`, 'success');
    await fetchDashboardAdmins();
    closeModal('confirmModal');
  } catch (error) {
    console.error(error);
    showToast('Server error deleting admin.', 'danger');
  }
}

async function deleteDashboardProduct(id, name) {
  try {
    const response = await fetch(productApiUrl, {
      method: 'POST',
      headers: {'Content-Type': 'application/json'},
      body: JSON.stringify({ action: 'delete', product_id: id })
    });
    const data = await response.json();
    if (data.status !== 'success') {
      showToast(data.message || 'Unable to delete product.', 'danger');
      return;
    }
    showToast(`"${name}" has been removed.`, 'success');
    await fetchDashboardProducts();
    await fetchDashboardOrders();
    closeModal('confirmModal');
  } catch (error) {
    console.error(error);
    showToast('Server error while deleting product.', 'danger');
  }
}

/**
 * Triggers the generic confirmation modal for deletion.
 */
function confirmDel(type, id, name) {
  const ct = document.getElementById('confirmTitle');
  const cm = document.getElementById('confirmMsg');
  const cb = document.getElementById('confirmBtn');
  if (ct) ct.textContent = `Delete ${type.charAt(0).toUpperCase()+type.slice(1)}?`;
  if (cm) cm.textContent = `Are you sure you want to delete "${name}"? This cannot be undone.`;
  if (cb) {
    cb.onclick = () => {
      if (type === 'product') {
        deleteDashboardProduct(id, name);
      } else if (type === 'category') {
        deleteDashboardCategory(id, name);
      } else if (type === 'admin') {
        deleteDashboardAdmin(id, name);
      }
    };
  }
  openModal('confirmModal');
}

// ═══════════════════ EXPORT ═══════════════════
/**
 * Handles reporting exports.
 */
function exportReport(fmt) {
  if (fmt === 'csv') {
    const h = ['Order ID','Customer','Items','Total (PHP)','Date','Status'];
    const rows = orders.map(o => [o.id,o.customer,o.items,o.total,o.date,o.status]);
    const csv = [h,...rows].map(r => r.join(',')).join('\n');
    const a = document.createElement('a');
    a.href = 'data:text/csv;charset=utf-8,'+encodeURIComponent(csv);
    a.download = 'solis_orders.csv';
    a.click();
    showToast('Spreadsheet exported!','success');
  } else { showToast('Opening PDF print dialog…','info'); setTimeout(() => window.print(), 500); }
}

// ═══════════════════ SETTINGS ═══════════════════
function switchSettings(key, el) {
  document.querySelectorAll('.settings-panel').forEach(p => p.classList.remove('active'));
  document.querySelectorAll('.settings-nav-item').forEach(i => i.classList.remove('active'));
  const sp = document.getElementById('sp-'+key);
  if (sp) sp.classList.add('active');
  if (el) el.classList.add('active');
}

// ═══════════════════ INITIALIZATION ═══════════════════
/**
 * Primary page entry point; fetches all data and prepares initial UI.
 */
async function initPage() {
  try {
    // Initial activation to prevent blank flash during data fetch
    goTo('dashboard', null);

    // Sequential loading prevents PHP session blocking errors
    await fetchDashboardCategories();
    await fetchDashboardProducts();
    await fetchDashboardOrders();
    await fetchDashboardCustomers();
    await fetchDashboardAdmins();
    
    renderAll();
    // Final activation call after all data is rendered to DOM
    goTo('dashboard', null);
  } catch (err) {
    console.error("Dashboard Init Error:", err);
    showToast("Partial data load failure. Check console.", "warning");
  }

  const catNameInput = document.getElementById('catName');
  if (catNameInput) {
    catNameInput.addEventListener('input', function() {
      if (!editCid) {
        const catSlugInput = document.getElementById('catSlug');
        if (catSlugInput) catSlugInput.value = this.value.toLowerCase().replace(/\s+/g,'-').replace(/[^a-z0-9-]/g,'');
      }
    });
  }
  const loginPass = document.getElementById('loginPass');
  if (loginPass) { loginPass.addEventListener('keydown', e => { if (e.key === 'Enter') doLogin(); }); }
  document.querySelectorAll('.modal-overlay').forEach(o => {
    o.addEventListener('click', function(e) { if (e.target === this) this.classList.remove('active'); });
  });
}

if (document.readyState === 'loading') {
  document.addEventListener('DOMContentLoaded', initPage);
} else {
  initPage();
}
