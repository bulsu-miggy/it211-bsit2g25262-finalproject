/**
 * UniMerch — Dashboard JavaScript
 * Shared admin utilities: sidebar toggle, KPI loading, Chart.js setup
 */

// ============================================================
// Dashboard KPIs & Charts (only if on dashboard page)
// ============================================================
let revenueChartInstance = null;
let topProductsChartInstance = null;

$(document).ready(function() {
  if ($('#kpiGrid').length && $('#revenueChart').length) {
    loadDashboard();
  }
});

function loadDashboard() {
  // Load KPIs
  $.get(`${BASE_URL}/api/admin/analytics.php?type=dashboard`, function(res) {
    if (!res.success) return;
    const d = res.data;

    $('#kpiRevenue').text('₱' + d.total_revenue.toLocaleString('en-PH', {minimumFractionDigits:2}));
    $('#kpiOrders').text(d.today_orders);
    $('#kpiProducts').text(d.active_products);
    $('#kpiLowStock').text(d.low_stock);

    // Render recent orders table
    renderRecentOrders(d.recent_orders);
  });

  // Load Revenue Chart
  loadRevenueChart(30);

  // Revenue range change
  $('#revenueRange').on('change', function() {
    loadRevenueChart($(this).val());
  });

  // Load Top Products Chart
  loadTopProductsChart();
}

function loadRevenueChart(days) {
  $.get(`${BASE_URL}/api/admin/analytics.php?type=revenue&days=${days}`, function(res) {
    if (!res.success) return;

    const labels = res.data.map(d => {
      const date = new Date(d.date);
      return date.toLocaleDateString('en-PH', { month: 'short', day: 'numeric' });
    });
    const revenues = res.data.map(d => parseFloat(d.revenue));

    if (revenueChartInstance) revenueChartInstance.destroy();

    const ctx = document.getElementById('revenueChart').getContext('2d');
    const gradient = ctx.createLinearGradient(0, 0, 0, 300);
    gradient.addColorStop(0, 'rgba(59, 130, 246, 0.2)');
    gradient.addColorStop(1, 'rgba(59, 130, 246, 0.01)');

    revenueChartInstance = new Chart(ctx, {
      type: 'line',
      data: {
        labels,
        datasets: [{
          label: 'Revenue',
          data: revenues,
          borderColor: '#3b82f6',
          backgroundColor: gradient,
          borderWidth: 3,
          fill: true,
          tension: 0.4,
          pointBackgroundColor: '#3b82f6',
          pointBorderColor: '#fff',
          pointBorderWidth: 2,
          pointRadius: 4,
          pointHoverRadius: 6
        }]
      },
      options: {
        responsive: true,
        maintainAspectRatio: false,
        plugins: {
          legend: { display: false },
          tooltip: {
            backgroundColor: '#0f172a',
            titleColor: '#f8fafc',
            bodyColor: '#cbd5e1',
            padding: 12,
            cornerRadius: 8,
            callbacks: {
              label: ctx => '₱' + ctx.parsed.y.toLocaleString('en-PH', {minimumFractionDigits:2})
            }
          }
        },
        scales: {
          x: {
            grid: { display: false },
            ticks: { color: '#94a3b8', font: { size: 11 } }
          },
          y: {
            grid: { color: 'rgba(148,163,184,0.1)' },
            ticks: {
              color: '#94a3b8',
              font: { size: 11 },
              callback: v => '₱' + (v >= 1000 ? (v/1000).toFixed(1) + 'k' : v)
            }
          }
        }
      }
    });
  });
}

function loadTopProductsChart() {
  $.get(`${BASE_URL}/api/admin/analytics.php?type=top_products&limit=5`, function(res) {
    if (!res.success) return;

    if (topProductsChartInstance) topProductsChartInstance.destroy();

    const labels = res.data.map(p => p.name.length > 20 ? p.name.substring(0, 20) + '...' : p.name);
    const data = res.data.map(p => parseInt(p.total_sold));
    const colors = ['#3b82f6', '#f59e0b', '#10b981', '#ec4899', '#8b5cf6'];

    topProductsChartInstance = new Chart(document.getElementById('topProductsChart'), {
      type: 'bar',
      data: {
        labels,
        datasets: [{
          label: 'Units Sold',
          data,
          backgroundColor: colors,
          borderRadius: 6,
          barThickness: 24
        }]
      },
      options: {
        indexAxis: 'y',
        responsive: true,
        maintainAspectRatio: false,
        plugins: {
          legend: { display: false },
          tooltip: { backgroundColor: '#0f172a', cornerRadius: 8, padding: 12 }
        },
        scales: {
          x: { 
            grid: { color: 'rgba(148,163,184,0.1)' }, 
            ticks: { color: '#94a3b8' },
            beginAtZero: true
          },
          y: { 
            grid: { display: false }, 
            ticks: { color: '#334155', font: { size: 11, weight: 500 } } 
          }
        }
      }
    });
  });
}

function renderRecentOrders(orders) {
  if (!orders || orders.length === 0) {
    $('#recentOrdersBody').html('<tr><td colspan="7" class="text-center text-muted py-4">No orders yet</td></tr>');
    return;
  }

  let html = '';
  orders.forEach(order => {
    const date = new Date(order.created_at);
    const formattedDate = date.toLocaleDateString('en-PH', { month: 'short', day: 'numeric', year: 'numeric' });
    const formattedTime = date.toLocaleTimeString('en-PH', { hour: '2-digit', minute: '2-digit' });

    html += `
      <tr class="mobile-order-row full-bleed-row">
        <!-- Optimized Standard Order Card (Matching Product Layout logic) -->
        <td colspan="7" class="p-0 d-md-none border-0 text-start">
          <div class="mobile-order-card bg-white border shadow-sm" style="margin-bottom: 1rem; border-radius: var(--radius-xl); overflow: hidden; width: 100% !important; box-sizing: border-box;">
            <div class="p-3">
              <!-- Header Section -->
              <div class="d-flex justify-content-between align-items-center mb-3">
                <div class="d-flex align-items-center">
                   <div class="merchant-avatar me-2" style="width:32px; height:32px; background: var(--gray-50); color: var(--primary-600); border: 1px solid var(--gray-100);">
                     <i class="bi bi-receipt"></i>
                   </div>
                   <div class="fw-bold text-primary">#${order.order_number}</div>
                </div>
                <span class="badge-status badge-${order.status}" style="font-size: 0.65rem;">${order.status.charAt(0).toUpperCase() + order.status.slice(1)}</span>
              </div>

              <!-- Data Rows: Matching Product Table Style (Label Left / Value Right) -->
              <div class="d-flex justify-content-between align-items-center py-2 border-top">
                <span class="text-muted small fw-medium uppercase" style="font-size: 0.65rem; letter-spacing: 0.03em;">CUSTOMER</span>
                <span class="fw-bold text-dark" style="font-size: 0.85rem;">${order.customer_name}</span>
              </div>

              <div class="d-flex justify-content-between align-items-center py-2 border-top">
                <span class="text-muted small fw-medium uppercase" style="font-size: 0.65rem; letter-spacing: 0.03em;">TOTAL AMOUNT</span>
                <span class="fw-bold text-primary" style="font-size: 0.95rem;">₱${parseFloat(order.total_amount).toLocaleString('en-PH', {minimumFractionDigits:2})}</span>
              </div>

              <div class="d-flex justify-content-between align-items-center py-2 border-top">
                <span class="text-muted small fw-medium uppercase" style="font-size: 0.65rem; letter-spacing: 0.03em;">DATE</span>
                <span class="text-muted" style="font-size: 0.8rem;">${formattedDate}</span>
              </div>
            </div>
          </div>
        </td>

        <!-- Desktop Columns (Hidden on Mobile) -->
        <td class="d-none d-md-table-cell text-start">
          <div class="d-flex align-items-center">
             <div class="merchant-avatar me-2" style="width:32px; height:32px; background: var(--gray-50); color: var(--primary-600); font-size: 0.8rem; border: 1px solid var(--gray-100);">
               <i class="bi bi-receipt"></i>
             </div>
             <div>
               <div class="fw-bold" style="color:var(--primary-700);">${order.order_number}</div>
               <div class="small text-muted" style="font-size:0.7rem;">${formattedDate}</div>
             </div>
          </div>
        </td>
        <td data-label="Customer" class="d-none d-md-table-cell text-md-center"><strong>${order.customer_name}</strong></td>
        <td data-label="Items" class="d-none d-md-table-cell text-center text-md-center">${order.item_count || '-'} items</td>
        <td data-label="Total" class="d-none d-md-table-cell fw-bold text-end text-md-center">₱${parseFloat(order.total_amount).toLocaleString('en-PH', {minimumFractionDigits:2})}</td>
        <td data-label="Payment" class="d-none d-md-table-cell text-md-center"><span class="text-capitalize">${order.payment_method.replace('_', ' ')}</span></td>
        <td data-label="Status" class="d-none d-md-table-cell text-md-center"><div><span class="badge-status badge-${order.status}">${order.status.charAt(0).toUpperCase() + order.status.slice(1)}</span></div></td>
        <td data-label="Date" class="d-none d-md-table-cell text-md-center text-muted" style="font-size: 0.8rem;">${formattedTime}</td>
      </tr>
    `;
  });

  $('#recentOrdersBody').html(html);
}

function getStatusBadge(status) {
  const map = {
    pending: 'badge-pending',
    confirmed: 'badge-confirmed',
    processing: 'badge-processing',
    ready: 'badge-ready',
    completed: 'badge-completed',
    cancelled: 'badge-cancelled'
  };
  return `<span class="badge-status ${map[status] || ''}">${status.charAt(0).toUpperCase() + status.slice(1)}</span>`;
}
