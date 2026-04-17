/**
 * UniMerch — Analytics JavaScript
 * Chart.js charts for revenue, orders by status, sales by category, top products
 */

let chartInstances = {};

$(document).ready(function() {
  loadAnalytics();

  // Period change
  $('#analyticsPeriod').on('change', function() {
    loadAnalytics();
  });
});

function loadAnalytics() {
  const days = $('#analyticsPeriod').val() || 30;

  loadAnalyticsRevenue(days);
  loadOrderStatusChart();
  loadCategoryChart();
  loadTopProductsBar();
}

function loadAnalyticsRevenue(days) {
  $.get(`${BASE_URL}/api/admin/analytics.php?type=revenue&days=${days}`, function(res) {
    if (!res.success) return;

    const labels = res.data.map(d => {
      const date = new Date(d.date);
      return date.toLocaleDateString('en-PH', { month: 'short', day: 'numeric' });
    });
    const revenues = res.data.map(d => parseFloat(d.revenue));
    const orders = res.data.map(d => parseInt(d.orders));

    if (chartInstances.revenue) chartInstances.revenue.destroy();

    const ctx = document.getElementById('analyticsRevenueChart').getContext('2d');
    const gradient = ctx.createLinearGradient(0, 0, 0, 350);
    gradient.addColorStop(0, 'rgba(59, 130, 246, 0.25)');
    gradient.addColorStop(1, 'rgba(59, 130, 246, 0.01)');

    chartInstances.revenue = new Chart(ctx, {
      type: 'line',
      data: {
        labels,
        datasets: [
          {
            label: 'Revenue (₱)',
            data: revenues,
            borderColor: '#3b82f6',
            backgroundColor: gradient,
            borderWidth: 2.5,
            fill: true,
            tension: 0.4,
            pointRadius: 3,
            pointHoverRadius: 6,
            yAxisID: 'y'
          },
          {
            label: 'Orders',
            data: orders,
            borderColor: '#f59e0b',
            backgroundColor: 'rgba(245, 158, 11, 0.1)',
            borderWidth: 2,
            borderDash: [5, 5],
            fill: false,
            tension: 0.4,
            pointRadius: 3,
            pointHoverRadius: 6,
            yAxisID: 'y1'
          }
        ]
      },
      options: {
        responsive: true,
        maintainAspectRatio: false,
        interaction: { mode: 'index', intersect: false },
        plugins: {
          legend: {
            position: 'top',
            labels: { usePointStyle: true, padding: 20, font: { size: 12 } }
          },
          tooltip: {
            backgroundColor: '#0f172a',
            padding: 12,
            cornerRadius: 8,
            callbacks: {
              label: ctx => {
                if (ctx.datasetIndex === 0) return '₱' + ctx.parsed.y.toLocaleString('en-PH', {minimumFractionDigits:2});
                return ctx.parsed.y + ' orders';
              }
            }
          }
        },
        scales: {
          x: {
            grid: { display: false },
            ticks: { color: '#94a3b8', font: { size: 11 } }
          },
          y: {
            position: 'left',
            grid: { color: 'rgba(148,163,184,0.1)' },
            ticks: {
              color: '#3b82f6',
              font: { size: 11 },
              callback: v => '₱' + (v >= 1000 ? (v/1000).toFixed(1) + 'k' : v)
            }
          },
          y1: {
            position: 'right',
            grid: { display: false },
            ticks: { color: '#f59e0b', font: { size: 11 }, stepSize: 1 }
          }
        }
      }
    });
  });
}

function loadOrderStatusChart() {
  $.get(`${BASE_URL}/api/admin/analytics.php?type=orders_by_status`, function(res) {
    if (!res.success) return;

    if (chartInstances.orderStatus) chartInstances.orderStatus.destroy();

    const statusColors = {
      pending: '#f59e0b',
      confirmed: '#3b82f6',
      processing: '#6366f1',
      ready: '#10b981',
      completed: '#22c55e',
      cancelled: '#ef4444'
    };

    chartInstances.orderStatus = new Chart(document.getElementById('orderStatusChart'), {
      type: 'doughnut',
      data: {
        labels: res.data.map(d => d.status.charAt(0).toUpperCase() + d.status.slice(1)),
        datasets: [{
          data: res.data.map(d => parseInt(d.count)),
          backgroundColor: res.data.map(d => statusColors[d.status] || '#94a3b8'),
          borderWidth: 0,
          spacing: 3,
          borderRadius: 4
        }]
      },
      options: {
        responsive: true,
        maintainAspectRatio: false,
        cutout: '65%',
        plugins: {
          legend: {
            position: 'bottom',
            labels: { usePointStyle: true, padding: 16, font: { size: 11 } }
          },
          tooltip: { backgroundColor: '#0f172a', padding: 12, cornerRadius: 8 }
        }
      }
    });
  });
}

function loadCategoryChart() {
  $.get(`${BASE_URL}/api/admin/analytics.php?type=sales_by_category`, function(res) {
    if (!res.success) return;

    if (chartInstances.category) chartInstances.category.destroy();

    chartInstances.category = new Chart(document.getElementById('categoryChart'), {
      type: 'pie',
      data: {
        labels: res.data.map(d => d.category),
        datasets: [{
          data: res.data.map(d => parseFloat(d.revenue)),
          backgroundColor: res.data.map(d => d.color || '#94a3b8'),
          borderWidth: 2,
          borderColor: '#fff'
        }]
      },
      options: {
        responsive: true,
        maintainAspectRatio: false,
        plugins: {
          legend: {
            position: 'bottom',
            labels: { usePointStyle: true, padding: 16, font: { size: 11, weight: 600 } }
          },
          tooltip: {
            backgroundColor: '#0f172a',
            padding: 12,
            cornerRadius: 8,
            callbacks: {
              label: ctx => ctx.label + ': ₱' + ctx.parsed.toLocaleString('en-PH', {minimumFractionDigits:2})
            }
          }
        }
      }
    });
  });
}

function loadTopProductsBar() {
  $.get(`${BASE_URL}/api/admin/analytics.php?type=top_products&limit=10`, function(res) {
    if (!res.success) return;

    if (chartInstances.topProducts) chartInstances.topProducts.destroy();

    const colors = ['#3b82f6', '#f59e0b', '#10b981', '#ec4899', '#8b5cf6', '#06b6d4', '#f97316', '#14b8a6', '#84cc16', '#6366f1'];

    chartInstances.topProducts = new Chart(document.getElementById('topProductsBarChart'), {
      type: 'bar',
      data: {
        labels: res.data.map(p => p.name.length > 25 ? p.name.substring(0, 25) + '...' : p.name),
        datasets: [{
          label: 'Revenue (₱)',
          data: res.data.map(p => parseFloat(p.total_revenue)),
          backgroundColor: colors.slice(0, res.data.length),
          borderRadius: 8,
          barThickness: 30
        }]
      },
      options: {
        indexAxis: 'y',
        responsive: true,
        maintainAspectRatio: false,
        plugins: {
          legend: { display: false },
          tooltip: {
            backgroundColor: '#0f172a',
            padding: 12,
            cornerRadius: 8,
            callbacks: {
              label: ctx => '₱' + ctx.parsed.x.toLocaleString('en-PH', {minimumFractionDigits:2})
            }
          }
        },
        scales: {
          x: {
            grid: { color: 'rgba(148,163,184,0.1)' },
            ticks: {
              color: '#94a3b8',
              callback: v => '₱' + (v >= 1000 ? (v/1000).toFixed(1) + 'k' : v)
            }
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
