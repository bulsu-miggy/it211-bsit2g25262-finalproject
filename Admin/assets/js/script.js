// Form validation helpers
function validateForm(formId) {
  const form = document.getElementById(formId);
  if (!form) return true;

  const requiredFields = form.querySelectorAll("[required]");
  let isValid = true;

  requiredFields.forEach((field) => {
    if (!field.value.trim()) {
      field.classList.add("border-red-500");
      isValid = false;
    } else {
      field.classList.remove("border-red-500");
    }
  });

  return isValid;
}

// Auto-slug generation
function generateSlug(text) {
  return text
    .toLowerCase()
    .replace(/[^a-z0-9]+/g, "-")
    .replace(/^-|-$/g, "");
}

// Image preview
function previewImage(input, previewId) {
  if (input.files && input.files[0]) {
    const reader = new FileReader();
    reader.onload = function (e) {
      const preview = document.getElementById(previewId);
      if (preview) {
        preview.src = e.target.result;
        preview.classList.remove("hidden");
      }
    };
    reader.readAsDataURL(input.files[0]);
  }
}

// Character counter for SEO fields
function setupCharCounter(inputId, counterId, maxLength) {
  const input = document.getElementById(inputId);
  const counter = document.getElementById(counterId);

  if (input && counter) {
    input.addEventListener("input", function () {
      counter.textContent = this.value.length;
      if (this.value.length > maxLength) {
        this.value = this.value.substring(0, maxLength);
        counter.textContent = maxLength;
      }
    });
  }
}

// Confirmation dialog helper
function confirmAction(message, callback) {
  if (confirm(message)) {
    callback();
  }
}

// Delete Product Function
function deleteProduct(productId) {
  if (confirm('Are you sure you want to delete this product?')) {
    fetch(window.location.href, {
      method: 'POST',
      headers: {
        'Content-Type': 'application/x-www-form-urlencoded',
      },
      body: 'delete_product_id=' + productId
    })
    .then(response => response.json())
    .then(data => {
      if (data.success) {
        const row = document.getElementById('product-row-' + productId);
        if (row) row.remove();
        
        const card = document.getElementById('product-card-' + productId);
        if (card) card.remove();
        
        location.reload();
      } else {
        alert('Error deleting product');
      }
    })
    .catch(error => {
      console.error('Error:', error);
      alert('Error deleting product');
    });
  }
}

// Delete Category Function
function deleteCategory(categoryId) {
  if (confirm('Are you sure you want to delete this category?')) {
    fetch(window.location.href, {
      method: 'POST',
      headers: {
        'Content-Type': 'application/x-www-form-urlencoded',
      },
      body: 'delete_category_id=' + categoryId
    })
    .then(response => response.json())
    .then(data => {
      if (data.success) {
        const row = document.getElementById('category-row-' + categoryId);
        if (row) row.remove();
        alert('Category deleted successfully!');
      } else {
        alert(data.message || 'Error deleting category');
      }
    })
    .catch(error => {
      console.error('Error:', error);
      alert('Error deleting category');
    });
  }
}

// Delete Customer Function
function deleteCustomer(customerId) {
  if (confirm('Are you sure you want to delete this customer?')) {
    fetch(window.location.href, {
      method: 'POST',
      headers: {
        'Content-Type': 'application/x-www-form-urlencoded',
      },
      body: 'delete_customer_id=' + customerId
    })
    .then(response => response.json())
    .then(data => {
      if (data.success) {
        const row = document.getElementById('customer-row-' + customerId);
        if (row) row.remove();
        alert('Customer deleted successfully!');
      } else {
        alert('Error deleting customer');
      }
    })
    .catch(error => {
      console.error('Error:', error);
      alert('Error deleting customer');
    });
  }
}

// View Order Function
function viewOrder(id) {
  window.location.href = '?page=order-detail&id=' + id;
}

// ============================================
// DASHBOARD CHARTS INITIALIZATION
// ============================================

function initializeDashboardCharts() {
    const chartContainer = document.getElementById('dashboardCharts');
    if (!chartContainer || typeof Chart === 'undefined') {
        return;
    }

    let revenueLabels = [], revenueData = [];
    let ordersLabels = [], ordersData = [];
    let categoryLabels = [], categoryData = [];
    let statusLabels = [], statusData = [];
    
    try {
        revenueLabels = JSON.parse(chartContainer.dataset.revenueLabels || '[]');
        revenueData = JSON.parse(chartContainer.dataset.revenueData || '[]');
        ordersLabels = JSON.parse(chartContainer.dataset.ordersLabels || '[]');
        ordersData = JSON.parse(chartContainer.dataset.ordersData || '[]');
        categoryLabels = JSON.parse(chartContainer.dataset.categoryLabels || '[]');
        categoryData = JSON.parse(chartContainer.dataset.categoryData || '[]');
        statusLabels = JSON.parse(chartContainer.dataset.statusLabels || '[]');
        statusData = JSON.parse(chartContainer.dataset.statusData || '[]');
    } catch (e) {
        console.error('Error parsing chart data:', e);
    }

    // Weekly Orders Chart (Bar)
    const ordersCtx = document.getElementById('weeklyOrdersChart');
    if (ordersCtx) {
        new Chart(ordersCtx, {
            type: 'bar',
            data: {
                labels: ordersLabels.length ? ordersLabels : ['Mon', 'Tue', 'Wed', 'Thu', 'Fri', 'Sat', 'Sun'],
                datasets: [{
                    label: 'Orders',
                    data: ordersData.length ? ordersData : [0, 0, 0, 0, 0, 0, 0],
                    backgroundColor: 'rgba(59, 130, 246, 0.8)',
                    borderColor: 'rgb(59, 130, 246)',
                    borderWidth: 1,
                    borderRadius: 4
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    legend: { display: false }
                },
                scales: {
                    y: {
                        beginAtZero: true
                    }
                }
            }
        });
    }

    // Revenue Trend Chart (Line)
    const revenueCtx = document.getElementById('revenueTrendChart');
    if (revenueCtx) {
        new Chart(revenueCtx, {
            type: 'line',
            data: {
                labels: revenueLabels.length ? revenueLabels : ['Jan', 'Feb', 'Mar', 'Apr', 'May', 'Jun'],
                datasets: [{
                    label: 'Revenue',
                    data: revenueData.length ? revenueData : [0, 0, 0, 0, 0, 0],
                    borderColor: 'rgb(34, 197, 94)',
                    backgroundColor: 'rgba(34, 197, 94, 0.1)',
                    fill: true,
                    tension: 0.4,
                    pointRadius: 4,
                    pointBackgroundColor: 'rgb(34, 197, 94)'
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    legend: { display: false },
                    tooltip: {
                        callbacks: {
                            label: (context) => '₱' + (context.raw || 0).toLocaleString()
                        }
                    }
                },
                scales: {
                    y: {
                        beginAtZero: true,
                        ticks: {
                            callback: (value) => '₱' + value.toLocaleString()
                        }
                    }
                }
            }
        });
    }

    // Category Distribution Chart (Doughnut)
    const categoryCtx = document.getElementById('categoryDistributionChart');
    if (categoryCtx) {
        const backgroundColors = [
            'rgba(59, 130, 246, 0.8)',
            'rgba(34, 197, 94, 0.8)',
            'rgba(249, 115, 22, 0.8)',
            'rgba(168, 85, 247, 0.8)',
            'rgba(236, 72, 153, 0.8)'
        ];

        new Chart(categoryCtx, {
            type: 'doughnut',
            data: {
                labels: categoryLabels.length ? categoryLabels : ['No Categories'],
                datasets: [{
                    data: categoryData.length ? categoryData : [1],
                    backgroundColor: backgroundColors.slice(0, Math.max(categoryData.length, 1)),
                    borderWidth: 2,
                    borderColor: '#ffffff'
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    legend: {
                        position: 'bottom',
                        labels: { padding: 15, usePointStyle: true }
                    }
                }
            }
        });
    }

    // Order Status Chart (Pie)
    const statusCtx = document.getElementById('orderStatusChart');
    if (statusCtx) {
        const backgroundColors = [
            'rgba(59, 130, 246, 0.8)',
            'rgba(34, 197, 94, 0.8)',
            'rgba(249, 115, 22, 0.8)',
            'rgba(239, 68, 68, 0.8)',
            'rgba(168, 85, 247, 0.8)'
        ];

        new Chart(statusCtx, {
            type: 'pie',
            data: {
                labels: statusLabels.length ? statusLabels : ['No Orders'],
                datasets: [{
                    data: statusData.length ? statusData : [1],
                    backgroundColor: backgroundColors.slice(0, Math.max(statusData.length, 1)),
                    borderWidth: 2,
                    borderColor: '#ffffff'
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    legend: {
                        position: 'bottom',
                        labels: { padding: 15, usePointStyle: true }
                    }
                }
            }
        });
    }
}

// ============================================
// ANALYTICS CHARTS INITIALIZATION
// ============================================

function initializeAnalyticsCharts() {
    const chartContainer = document.getElementById('analyticsCharts');
    if (!chartContainer || typeof Chart === 'undefined') {
        return;
    }

    let revenueLabels = [], revenueData = [];
    let dailyRevenueLabels = [], dailyRevenueData = [];
    let dailyOrdersLabels = [], dailyOrdersData = [];
    let customerLabels = [], customerData = [];
    let statusLabels = [], statusData = [];
    let topProductsLabels = [], topProductsData = [];
    let categoryLabels = [], categoryData = [];
    let topCustomersLabels = [], topCustomersData = [];
    
    try {
        revenueLabels = JSON.parse(chartContainer.dataset.revenueLabels || '[]');
        revenueData = JSON.parse(chartContainer.dataset.revenueData || '[]');
        dailyRevenueLabels = JSON.parse(chartContainer.dataset.dailyRevenueLabels || '[]');
        dailyRevenueData = JSON.parse(chartContainer.dataset.dailyRevenueData || '[]');
        dailyOrdersLabels = JSON.parse(chartContainer.dataset.dailyOrdersLabels || '[]');
        dailyOrdersData = JSON.parse(chartContainer.dataset.dailyOrdersData || '[]');
        customerLabels = JSON.parse(chartContainer.dataset.customerLabels || '[]');
        customerData = JSON.parse(chartContainer.dataset.customerData || '[]');
        statusLabels = JSON.parse(chartContainer.dataset.statusLabels || '[]');
        statusData = JSON.parse(chartContainer.dataset.statusData || '[]');
        topProductsLabels = JSON.parse(chartContainer.dataset.topProductsLabels || '[]');
        topProductsData = JSON.parse(chartContainer.dataset.topProductsData || '[]');
        categoryLabels = JSON.parse(chartContainer.dataset.categoryLabels || '[]');
        categoryData = JSON.parse(chartContainer.dataset.categoryData || '[]');
        topCustomersLabels = JSON.parse(chartContainer.dataset.topCustomersLabels || '[]');
        topCustomersData = JSON.parse(chartContainer.dataset.topCustomersData || '[]');
    } catch (e) {
        console.error('Error parsing analytics chart data:', e);
    }

    // 1. Monthly Revenue Chart
    const revenueCtx = document.getElementById('revenueChart');
    if (revenueCtx) {
        new Chart(revenueCtx, {
            type: 'line',
            data: {
                labels: revenueLabels.length ? revenueLabels : ['No Data'],
                datasets: [{
                    label: 'Revenue',
                    data: revenueData.length ? revenueData : [0],
                    borderColor: 'rgb(59, 130, 246)',
                    backgroundColor: 'rgba(59, 130, 246, 0.1)',
                    tension: 0.4,
                    fill: true,
                    pointRadius: 4,
                    pointBackgroundColor: 'rgb(59, 130, 246)'
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    legend: { display: false },
                    tooltip: {
                        callbacks: {
                            label: (context) => '₱' + (context.raw || 0).toLocaleString()
                        }
                    }
                },
                scales: {
                    y: {
                        beginAtZero: true,
                        ticks: {
                            callback: (value) => '₱' + value.toLocaleString()
                        }
                    }
                }
            }
        });
    }

    // 2. Daily Revenue Chart
    const dailyRevenueCtx = document.getElementById('dailyRevenueChart');
    if (dailyRevenueCtx) {
        new Chart(dailyRevenueCtx, {
            type: 'bar',
            data: {
                labels: dailyRevenueLabels.length ? dailyRevenueLabels : ['No Data'],
                datasets: [{
                    label: 'Daily Revenue',
                    data: dailyRevenueData.length ? dailyRevenueData : [0],
                    backgroundColor: 'rgba(34, 197, 94, 0.8)',
                    borderColor: 'rgb(34, 197, 94)',
                    borderWidth: 1,
                    borderRadius: 4
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    legend: { display: false },
                    tooltip: {
                        callbacks: {
                            label: (context) => '₱' + (context.raw || 0).toLocaleString()
                        }
                    }
                },
                scales: {
                    y: {
                        beginAtZero: true,
                        ticks: {
                            callback: (value) => '₱' + value.toLocaleString()
                        }
                    }
                }
            }
        });
    }

    // 3. Daily Orders Chart
    const dailyOrdersCtx = document.getElementById('dailyOrdersChart');
    if (dailyOrdersCtx) {
        new Chart(dailyOrdersCtx, {
            type: 'line',
            data: {
                labels: dailyOrdersLabels.length ? dailyOrdersLabels : ['No Data'],
                datasets: [{
                    label: 'Orders',
                    data: dailyOrdersData.length ? dailyOrdersData : [0],
                    borderColor: 'rgb(249, 115, 22)',
                    backgroundColor: 'rgba(249, 115, 22, 0.1)',
                    fill: true,
                    tension: 0.4,
                    pointRadius: 4,
                    pointBackgroundColor: 'rgb(249, 115, 22)'
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    legend: { display: false }
                },
                scales: {
                    y: {
                        beginAtZero: true
                    }
                }
            }
        });
    }

    // 4. Customer Growth Chart
    const customerCtx = document.getElementById('customerGrowthChart');
    if (customerCtx) {
        new Chart(customerCtx, {
            type: 'bar',
            data: {
                labels: customerLabels.length ? customerLabels : ['No Data'],
                datasets: [{
                    label: 'New Customers',
                    data: customerData.length ? customerData : [0],
                    backgroundColor: 'rgba(168, 85, 247, 0.8)',
                    borderColor: 'rgb(168, 85, 247)',
                    borderWidth: 1,
                    borderRadius: 4
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    legend: { display: false }
                },
                scales: {
                    y: {
                        beginAtZero: true
                    }
                }
            }
        });
    }

    // 5. Order Status Chart
    const statusCtx = document.getElementById('orderStatusChart');
    if (statusCtx) {
        new Chart(statusCtx, {
            type: 'doughnut',
            data: {
                labels: statusLabels.length ? statusLabels : ['No Orders'],
                datasets: [{
                    data: statusData.length ? statusData : [1],
                    backgroundColor: [
                        'rgba(59, 130, 246, 0.8)',
                        'rgba(234, 179, 8, 0.8)',
                        'rgba(34, 197, 94, 0.8)',
                        'rgba(239, 68, 68, 0.8)',
                        'rgba(168, 85, 247, 0.8)'
                    ],
                    borderWidth: 2,
                    borderColor: '#ffffff'
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    legend: {
                        position: 'bottom',
                        labels: { padding: 15, usePointStyle: true }
                    }
                }
            }
        });
    }

    // 6. Top Products Chart
    const topProductsCtx = document.getElementById('topProductsChart');
    if (topProductsCtx) {
        new Chart(topProductsCtx, {
            type: 'bar',
            data: {
                labels: topProductsLabels.length ? topProductsLabels : ['No Products'],
                datasets: [{
                    label: 'Revenue (₱)',
                    data: topProductsData.length ? topProductsData : [0],
                    backgroundColor: 'rgba(59, 130, 246, 0.8)',
                    borderColor: 'rgb(59, 130, 246)',
                    borderWidth: 1,
                    borderRadius: 4
                }]
            },
            options: {
                indexAxis: 'y',
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    legend: { display: false },
                    tooltip: {
                        callbacks: {
                            label: (context) => '₱' + (context.raw || 0).toLocaleString()
                        }
                    }
                },
                scales: {
                    x: {
                        beginAtZero: true,
                        ticks: {
                            callback: (value) => '₱' + value.toLocaleString()
                        }
                    }
                }
            }
        });
    }

    // 7. Category Revenue Chart
    const categoryCtx = document.getElementById('categoryChart');
    if (categoryCtx) {
        new Chart(categoryCtx, {
            type: 'polarArea',
            data: {
                labels: categoryLabels.length ? categoryLabels : ['No Data'],
                datasets: [{
                    data: categoryData.length ? categoryData : [1],
                    backgroundColor: [
                        'rgba(59, 130, 246, 0.8)',
                        'rgba(34, 197, 94, 0.8)',
                        'rgba(249, 115, 22, 0.8)',
                        'rgba(168, 85, 247, 0.8)',
                        'rgba(236, 72, 153, 0.8)',
                        'rgba(14, 165, 233, 0.8)'
                    ],
                    borderWidth: 2,
                    borderColor: '#ffffff'
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    legend: {
                        position: 'bottom',
                        labels: { padding: 15 }
                    },
                    tooltip: {
                        callbacks: {
                            label: (context) => '₱' + (context.raw || 0).toLocaleString()
                        }
                    }
                }
            }
        });
    }

    // 8. Top Customers Chart
    const topCustomersCtx = document.getElementById('topCustomersChart');
    if (topCustomersCtx) {
        new Chart(topCustomersCtx, {
            type: 'bar',
            data: {
                labels: topCustomersLabels.length ? topCustomersLabels : ['No Customers'],
                datasets: [{
                    label: 'Total Spending (₱)',
                    data: topCustomersData.length ? topCustomersData : [0],
                    backgroundColor: 'rgba(236, 72, 153, 0.8)',
                    borderColor: 'rgb(236, 72, 153)',
                    borderWidth: 1,
                    borderRadius: 4
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    legend: { display: false },
                    tooltip: {
                        callbacks: {
                            label: (context) => '₱' + (context.raw || 0).toLocaleString()
                        }
                    }
                },
                scales: {
                    y: {
                        beginAtZero: true,
                        ticks: {
                            callback: (value) => '₱' + value.toLocaleString()
                        }
                    }
                }
            }
        });
    }
}

// ============================================
// INITIALIZE EVERYTHING ON DOM LOAD
// ============================================

document.addEventListener("DOMContentLoaded", function () {
  
  // Setup meta title counter
  const metaTitle = document.querySelector('[name="metaTitle"]');
  const metaTitleCount = document.getElementById("metaTitleCount");
  if (metaTitle && metaTitleCount) {
    metaTitleCount.textContent = metaTitle.value.length;
    metaTitle.addEventListener("input", function () {
      metaTitleCount.textContent = this.value.length;
    });
  }

  // Setup meta description counter
  const metaDesc = document.querySelector('[name="metaDescription"]');
  const metaDescCount = document.getElementById("metaDescCount");
  if (metaDesc && metaDescCount) {
    metaDescCount.textContent = metaDesc.value.length;
    metaDesc.addEventListener("input", function () {
      metaDescCount.textContent = this.value.length;
    });
  }

  // Setup search functionality
  const searchInput = document.getElementById("searchInput");
  if (searchInput) {
    searchInput.addEventListener("keyup", function () {
      const searchText = this.value.toLowerCase();
      const rows = document.querySelectorAll("tbody tr");

      rows.forEach((row) => {
        const text = row.textContent.toLowerCase();
        row.style.display = text.includes(searchText) ? "" : "none";
      });
    });
  }

  // Auto-generate slug from name input
  const nameInput = document.querySelector('input[name="name"]');
  const slugInput = document.querySelector('input[name="slug"]');
  if (nameInput && slugInput) {
    nameInput.addEventListener("input", function () {
      if (slugInput.value === '' || slugInput.dataset.autoGenerated === 'true') {
        slugInput.value = generateSlug(this.value);
        slugInput.dataset.autoGenerated = 'true';
      }
    });
    
    slugInput.addEventListener("input", function () {
      slugInput.dataset.autoGenerated = 'false';
    });
  }

  // Setup character counters
  setupCharCounter('metaTitle', 'metaTitleCount', 60);
  setupCharCounter('metaDescription', 'metaDescCount', 160);
  
  // Initialize Charts
  initializeDashboardCharts();
  initializeAnalyticsCharts();
});