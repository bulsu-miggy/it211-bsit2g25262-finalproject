// Global functions - accessible from onclick handlers
function viewCustomer(customerId) {
    if (!customerId) {
        console.error('No customer ID found');
        alert('Error: Customer ID not found');
        return;
    }
    
    fetch(`customer.php?action=get_customer_details&id=${customerId}`)
        .then(response => {
            if (!response.ok) {
                throw new Error('Network response was not ok');
            }
            return response.text();
        })
        .then(text => {
            try {
                const data = JSON.parse(text);
                if (data.success) {
                    populateCustomerDetailsModal(data.customer, data.orders);
                    const modal = new bootstrap.Modal(document.getElementById('customerDetailsModal'));
                    modal.show();
                } else {
                    alert('Error: ' + data.message);
                }
            } catch (e) {
                console.error('JSON parse error:', e, 'Response:', text);
                alert('Error parsing response: ' + e.message);
            }
        })
        .catch(error => {
            console.error('Fetch error:', error);
            alert('Error loading customer details: ' + error.message);
        });
}

function getStatusBadgeClass(status) {
    const statusClasses = {
        'Pending': 'bg-warning bg-opacity-10 text-warning',
        'Processing': 'bg-info bg-opacity-10 text-info',
        'Shipped': 'bg-primary bg-opacity-10 text-primary',
        'Delivered': 'bg-success bg-opacity-10 text-success',
        'Cancelled': 'bg-danger bg-opacity-10 text-danger',
        'Returned': 'bg-secondary bg-opacity-10 text-secondary'
    };
    return statusClasses[status] || 'bg-secondary bg-opacity-10 text-secondary';
}

function populateCustomerDetailsModal(customer, orders) {
    const loadingSpinner = document.getElementById('detailsLoadingSpinner');
    const detailsContent = document.getElementById('detailsContent');
    const modalTitle = document.getElementById('customerDetailsModalLabel');

    // Hide spinner and show content
    loadingSpinner.classList.add('d-none');
    detailsContent.classList.remove('d-none');

    // Update modal title
    modalTitle.textContent = `${customer.first_name} ${customer.last_name}`;

    // Populate contact info
    document.getElementById('detailsFullName').textContent = `${customer.first_name} ${customer.last_name}`;
    document.getElementById('detailsEmail').textContent = customer.email;
    document.getElementById('detailsUsername').textContent = customer.username;
    
    const joinDate = new Date(customer.login_date);
    document.getElementById('detailsJoinDate').textContent = joinDate.toLocaleDateString();

    // Populate purchase history
    const ordersList = document.getElementById('ordersList');
    if (orders && orders.length > 0) {
        let ordersHTML = '';
        orders.forEach(order => {
            const orderDate = new Date(order.created_at);
            const itemsCount = order.items ? order.items.length : 0;
            ordersHTML += `
                <div class="bg-light rounded-2 p-3 mb-2">
                    <div class="d-flex justify-content-between align-items-start mb-2">
                        <div>
                            <div class="fw-semibold">Order #${order.order_code}</div>
                            <small class="text-secondary">${orderDate.toLocaleDateString()}</small>
                        </div>
                        <span class="badge ${getStatusBadgeClass(order.status)}">${order.status}</span>
                    </div>
                    <div class="small text-secondary mb-2">${itemsCount} item(s)</div>
                    ${order.items && order.items.length > 0 ? `
                        <div class="table-responsive mb-2"><table class="table table-sm table-bordered">
                            <thead class="table-light"><tr><th>Product</th><th class="text-center">Qty</th><th class="text-end">Price</th><th class="text-end">Subtotal</th></tr></thead>
                            <tbody>
                                ${order.items.map(item => `
                                    <tr>
                                        <td class="fw-semibold">${item.product_name}</td>
                                        <td class="text-center">${item.quantity}</td>
                                        <td class="text-end">₱${parseFloat(item.unit_price).toFixed(2)}</td>
                                        <td class="text-end fw-semibold">₱${parseFloat(item.subtotal).toFixed(2)}</td>
                                    </tr>
                                `).join('')}
                            </tbody>
                        </table></div>
                    ` : ''}
                    <div class="fw-semibold text-primary">₱${parseFloat(order.total_amount).toFixed(2)}</div>
                </div>
            `;
        });
        ordersList.innerHTML = ordersHTML;
    } else {
        ordersList.innerHTML = '<p class="text-secondary">No orders yet</p>';
    }
}

// DOMContentLoaded initialization
document.addEventListener("DOMContentLoaded", function () {

    const searchInput = document.getElementById('orderSearch');
    const statusSelect = document.getElementById('statusFilter');
    const tbody = document.getElementById('ordersTableBody');
    const emptyState = document.getElementById('emptyState');

    function filterOrders() {
        if (!searchInput || !statusSelect || !tbody) return;

        const query = searchInput.value.toLowerCase().trim();
        const statusVal = statusSelect.value;
        const rows = tbody.querySelectorAll('tr');
        let visibleCount = 0;

        rows.forEach(function(row){
            const rowId = row.getAttribute('data-id')?.toLowerCase() || '';
            const rowStatus = row.getAttribute('data-status');
            const customerCell = row.cells[1]?.textContent.toLowerCase() || '';

            const matchesSearch = query === '' || rowId.includes(query) || customerCell.includes(query);
            const matchesStatus = statusVal === '' || rowStatus === statusVal;
            
            if (matchesSearch && matchesStatus) {
                row.classList.remove('d-none');
                visibleCount++;
            } else {
                row.classList.add('d-none');
            }
        });

        if (emptyState) {
            emptyState.classList.toggle('d-none', visibleCount !== 0);
        }
    }

    if (searchInput) searchInput.addEventListener('input', filterOrders);
    if (statusSelect) statusSelect.addEventListener('change', filterOrders);


    window.setView = function(view) {
        const listView = document.getElementById('listView');
        const gridView = document.getElementById('gridView');
        const btnGrid  = document.getElementById('btnGrid');
        const btnList  = document.getElementById('btnList');

        if (!listView || !gridView) return;

        if (view === 'grid') {
            listView.classList.add('d-none');
            gridView.classList.remove('d-none');
            if (btnGrid) btnGrid.classList.add('active');
            if (btnList) btnList.classList.remove('active');
        } else {
            listView.classList.remove('d-none');
            gridView.classList.add('d-none');
            if (btnGrid) btnGrid.classList.remove('active');
            if (btnList) btnList.classList.add('active');
        }
    };

    // Filter products
    function filterProducts() {
        const searchInputProduct = document.getElementById('productSearch');
        const tbody = document.getElementById('productsTableBody');
        const emptyState = document.getElementById('productsEmptyState');

        if (!searchInputProduct || !tbody) return;

        const query = searchInputProduct.value.toLowerCase().trim();
        const rows = tbody.querySelectorAll('tr');
        let visibleCount = 0;

        rows.forEach(function(row) {
            const productName = row.cells[0]?.textContent.toLowerCase() || '';
            const category = row.cells[1]?.textContent.toLowerCase() || '';
            
            if (query === '' || productName.includes(query) || category.includes(query)) {
                row.classList.remove('d-none');
                visibleCount++;
            } else {
                row.classList.add('d-none');
            }
        });

        if (emptyState) {
            emptyState.classList.toggle('d-none', visibleCount !== 0);
        }
    }

    const productSearch = document.getElementById('productSearch');
    if (productSearch) productSearch.addEventListener('input', filterProducts);


    // Filter customers
    function filterCustomers() {
        const searchInputCustomer = document.getElementById('customerSearch');
        const tbody = document.getElementById('customersTableBody');
        const emptyState = document.getElementById('customersEmptyState');

        if (!searchInputCustomer || !tbody) return;

        const query = searchInputCustomer.value.toLowerCase().trim();
        const rows = tbody.querySelectorAll('tr');
        let visibleCount = 0;

        rows.forEach(function(row) {
            const customerName = row.cells[0]?.textContent.toLowerCase() || '';
            const email = row.cells[1]?.textContent.toLowerCase() || '';
            
            if (query === '' || customerName.includes(query) || email.includes(query)) {
                row.classList.remove('d-none');
                visibleCount++;
            } else {
                row.classList.add('d-none');
            }
        });

        if (emptyState) {
            emptyState.classList.toggle('d-none', visibleCount !== 0);
        }
    }

    const customerSearch = document.getElementById('customerSearch');
    if (customerSearch) customerSearch.addEventListener('input', filterCustomers);

    // View order details functionality
    function setupOrderViewListeners() {
        const viewOrderLinks = document.querySelectorAll('.view-order-link');
        viewOrderLinks.forEach(function(link) {
            link.addEventListener('click', function(e) {
                e.preventDefault();
                const orderId = this.getAttribute('data-order-id');
                
                if (!orderId) {
                    console.error('No order ID found');
                    alert('Error: Order ID not found');
                    return;
                }
                
                fetch(`orders.php?action=get_order_details&id=${orderId}`)
                    .then(response => {
                        if (!response.ok) {
                            throw new Error('Network response was not ok');
                        }
                        return response.text();
                    })
                    .then(text => {
                        try {
                            const data = JSON.parse(text);
                            if (data.success) {
                                populateOrderDetailsModal(data.order, data.items);
                                const modal = new bootstrap.Modal(document.getElementById('orderDetailsModal'));
                                modal.show();
                            } else {
                                alert('Error: ' + data.message);
                            }
                        } catch (e) {
                            console.error('JSON parse error:', e, 'Response:', text);
                            alert('Error parsing response: ' + e.message);
                        }
                    })
                    .catch(error => {
                        console.error('Fetch error:', error);
                        alert('Error loading order details: ' + error.message);
                    });
            });
        });
    }
    
    setupOrderViewListeners();

    function populateOrderDetailsModal(order, items) {
        const loadingSpinner = document.getElementById('orderDetailsLoadingSpinner');
        const detailsContent = document.getElementById('orderDetailsContent');
        const modalTitle = document.getElementById('orderDetailsModalLabel');

        // Hide spinner and show content
        loadingSpinner.classList.add('d-none');
        detailsContent.classList.remove('d-none');

        // Update modal title
        modalTitle.textContent = `Order #${order.order_code}`;

        // Populate order info
        document.getElementById('orderId').textContent = order.order_code;
        document.getElementById('orderCustomer').textContent = order.customer_name;
        document.getElementById('orderDate').textContent = new Date(order.created_at).toLocaleDateString();
        document.getElementById('orderStatus').textContent = order.status;
        document.getElementById('orderTotal').textContent = `₱${parseFloat(order.total_amount).toFixed(2)}`;

        // Populate order items
        const itemsList = document.getElementById('orderItemsList');
        if (items && items.length > 0) {
            let itemsHTML = '<div class="table-responsive"><table class="table table-bordered">';
            itemsHTML += '<thead class="table-light"><tr><th>Product</th><th class="text-center">Qty</th><th class="text-end">Price</th><th class="text-end">Subtotal</th></tr></thead><tbody>';
            items.forEach(item => {
                itemsHTML += `
                    <tr>
                        <td class="fw-semibold">${item.product_name}</td>
                        <td class="text-center">${item.quantity}</td>
                        <td class="text-end">₱${parseFloat(item.unit_price).toFixed(2)}</td>
                        <td class="text-end fw-semibold">₱${parseFloat(item.subtotal).toFixed(2)}</td>
                    </tr>
                `;
            });
            itemsHTML += '</tbody></table></div>';
            itemsList.innerHTML = itemsHTML;
        } else {
            itemsList.innerHTML = '<p class="text-secondary">No items in this order</p>';
        }
    }

});