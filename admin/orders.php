<?php
require_once '../db/action/dbconfig.php';

// Start session to get admin info
session_start();

// Get current admin info
$adminUsername = isset($_SESSION['username']) ? $_SESSION['username'] : 'Admin';
$adminInitial = strtoupper(substr($adminUsername, 0, 1));

// Handle AJAX requests FIRST - before any HTML output
if (isset($_GET['action'])) {
    // Handle export
    if ($_GET['action'] === 'export' && $_GET['format'] === 'csv') {
        header('Content-Type: text/csv');
        header('Content-Disposition: attachment; filename="orders_' . date('Y-m-d') . '.csv"');
        
        $output = fopen('php://output', 'w');
        fputcsv($output, ['Order ID', 'Customer', 'Date', 'Amount', 'Status']);
        
        $stmt = $conn->prepare("SELECT o.order_code, CONCAT(l.first_name, ' ', l.last_name) as customer, o.created_at, o.total_amount, o.status FROM orders o JOIN login l ON o.user_id = l.id ORDER BY o.created_at DESC");
        $stmt->execute();
        $exportOrders = $stmt->fetchAll(PDO::FETCH_ASSOC);
        
        foreach ($exportOrders as $order) {
            fputcsv($output, [
                $order['order_code'],
                $order['customer'],
                date('Y-m-d', strtotime($order['created_at'])),
                '₱' . number_format($order['total_amount'], 2),
                $order['status']
            ]);
        }
        fclose($output);
        exit;
    }
    if ($_GET['action'] === 'export' && $_GET['format'] === 'excel') {
    header('Content-Type: application/vnd.ms-excel');
    header('Content-Disposition: attachment; filename="orders_' . date('Y-m-d') . '.xls"');
    
    $stmt = $conn->prepare("SELECT o.order_code, CONCAT(l.first_name, ' ', l.last_name) as customer, o.created_at, o.total_amount, o.status FROM orders o JOIN login l ON o.user_id = l.id ORDER BY o.created_at DESC");
    $stmt->execute();
    $exportOrders = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    echo '<table border="1">';
    echo '<tr><th>Order ID</th><th>Customer</th><th>Date</th><th>Amount</th><th>Status</th></tr>';
    foreach ($exportOrders as $order) {
        echo '<tr>';
        echo '<td>' . htmlspecialchars($order['order_code']) . '</td>';
        echo '<td>' . htmlspecialchars($order['customer']) . '</td>';
        echo '<td>' . date('Y-m-d', strtotime($order['created_at'])) . '</td>';
        echo '<td>&#8369;' . number_format($order['total_amount'], 2) . '</td>';
        echo '<td>' . htmlspecialchars($order['status']) . '</td>';
        echo '</tr>';
    }
    echo '</table>';
    exit;
}
    
    // Handle order details AJAX request
    if ($_GET['action'] === 'get_order_details') {
        header('Content-Type: application/json; charset=utf-8');
        
        $order_id = intval($_GET['id'] ?? 0);
        
        if ($order_id === 0) {
            echo json_encode(['success' => false, 'message' => 'Invalid order ID']);
            exit;
        }
        
        try {
            // Get order info
            $stmt = $conn->prepare("SELECT o.*, CONCAT(l.first_name, ' ', l.last_name) as customer_name FROM orders o JOIN login l ON o.user_id = l.id WHERE o.id = ?");
            $stmt->execute([$order_id]);
            $order = $stmt->fetch(PDO::FETCH_ASSOC);

            if (!$order) {
                echo json_encode(['success' => false, 'message' => 'Order not found']);
                exit;
            }

            // Get order items
            $stmt = $conn->prepare("
                SELECT oi.id, oi.quantity, oi.unit_price, oi.subtotal, p.name as product_name
                FROM order_items oi
                JOIN products p ON oi.product_id = p.id
                WHERE oi.order_id = ?
            ");
            $stmt->execute([$order_id]);
            $items = $stmt->fetchAll(PDO::FETCH_ASSOC);

            echo json_encode([
                'success' => true,
                'order' => $order,
                'items' => $items
            ]);
        } catch (Exception $e) {
            echo json_encode(['success' => false, 'message' => 'Error: ' . $e->getMessage()]);
        }
        exit;
    }

    // Handle order status update AJAX request
    if ($_GET['action'] === 'update_status') {
        header('Content-Type: application/json; charset=utf-8');

        $order_id = intval($_POST['order_id'] ?? 0);
        $new_status = trim($_POST['status'] ?? '');

        $valid_statuses = ['Pending', 'Processing', 'Shipped', 'Delivered', 'Cancelled'];

        if ($order_id === 0 || !in_array($new_status, $valid_statuses)) {
            echo json_encode(['success' => false, 'message' => 'Invalid order ID or status']);
            exit;
        }

        try {
            $stmt = $conn->prepare("UPDATE orders SET status = ? WHERE id = ?");
            $stmt->execute([$new_status, $order_id]);

            echo json_encode(['success' => true, 'message' => 'Order status updated successfully']);
        } catch (Exception $e) {
            echo json_encode(['success' => false, 'message' => 'Error updating status: ' . $e->getMessage()]);
        }
        exit;
    }
}

// Fetch orders for display (only for page load, not AJAX)
$stmt = $conn->prepare("SELECT o.id, o.order_code, o.total_amount, o.status, o.created_at, l.first_name, l.last_name FROM orders o JOIN login l ON o.user_id = l.id ORDER BY o.created_at DESC");
$stmt->execute();
$orders = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Orders</title>
    <link rel="icon" type="image/png" href="../assets2/logo.png">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.1/font/bootstrap-icons.css">
    <link rel="stylesheet" href="admin.css">
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/js/bootstrap.bundle.min.js"></script>
</head>
<body class="bg-light min-vh-100 text-dark">
    <!-- navigation sidebar -->
    <nav id="sidebar" class="bg-white border-end d-flex flex-column position-fixed top-0 start-0 min-vh-100" style="z-index:1000;">
        <!-- logo -->
        <div class="border-bottom px-3 py-3 d-flex align-items-center gap-2 fw-bold fs-5">
            <span style="font-size:1.4rem;"></span>Laces
        </div>

        <!-- Navigation part -->
        <ul class="nav flex-column mt-3">
            <li class="nav-item">
                <a href="dashboard.php" class="nav-link d-flex align-items-center gap-2 fw-semibold rounded text-secondary mx-2 my-1 px-3 py-2">
                    <i class="bi bi-grid-1x2"></i>
                    Dashboard
                </a>
            </li>
            <li class="nav-item">
                <a href="orders.php" class="nav-link active d-flex align-items-center gap-2 fw-semibold rounded mx-2 my-1 px-3 py-2">
                    <i class="bi bi-cart-fill"></i>
                    Orders
                </a>
            </li>
            <li class="nav-item">
                <a href="product.php" class="nav-link d-flex align-items-center gap-2 fw-semibold rounded text-secondary mx-2 my-1 px-3 py-2">
                    <i class="bi bi-box-seam"></i>
                    Products
                </a>
            </li>
            <li class="nav-item">
                <a href ="categories.php" class="nav-link d-flex align-items-center gap-2 fw-semibold rounded text-secondary mx-2 my-1 px-3 py-2">
                    <i class="bi bi-folder"></i>
                    Categories
                </a>
            </li>
            <li class="nav-item">
                <a href="customer.php" class="nav-link d-flex align-items-center gap-2 fw-semibold rounded text-secondary mx-2 my-1 px-3 py-2">
                    <i class="bi bi-people"></i>
                    Customers
                </a>
            </li>
            <li class="nav-item">
                <a href="analytics.php" class="nav-link d-flex align-items-center gap-2 fw-semibold rounded text-secondary mx-2 my-1 px-3 py-2">
                    <i class="bi bi-bar-chart"></i>
                    Analytics
                </a>
            </li>
        </ul>
        <!-- footer -->
        <div class="mt-auto border-top px-3 py-3">
            <div class="d-flex align-items-center gap-2">
                <div class="rounded-circle bg-primary d-flex align-items-center justify-content-center text-white fw-bold flex-shrink-0" style="width: 36px; height: 36px; font-size:.8rem;"><?php echo $adminInitial; ?></div>
                <div>
                    <div class="fw-bold" style="font-size:.82rem;line-height:1.2;"><?php echo htmlspecialchars($adminUsername); ?></div>
                    <div class="text-secondary" style="font-size:.72rem;">Admin</div>
                </div>
            </div>
        </div>
    </nav>

    <!-- top part -->
    <div id="topbar" class="bg-white border-bottom d-flex align-items-center px-4 sticky-top" style="height:60px;z-index:999;">
        <h5 class="mb-0 fw-bold fs-5">Dashboard</h5>

        <!-- search part -->
        <div class="position-relative ms-3" style="max-width:260px;flex:1;">
            <i class="bi bi-search text-secondary search-icon"></i>
            <input type="text" class="form-control bg-light border search-input" placeholder="Search…"/>
        </div>

        <!-- right part -->
        <div class="ms-auto d-flex align-items-center gap-3">
            <div class="position-relative">
                <i class="bi bi-bell fs-5 text-secondary"></i>
                <span class="position-absolute top-0 start-100 translate-middle badge rounded-pill bg-danger" style="font-size:.6rem;">3</span>
            </div>
            <div class="dropdown">
                <button class="btn btn-link text-dark p-0 d-flex align-items-center gap-2 dropdown-toggle" type="button" data-bs-toggle="dropdown" aria-expanded="false" style="text-decoration: none;">
                    <div class="rounded-circle bg-primary d-flex align-items-center justify-content-center text-white fw-bold flex-shrink-0" style="width:36px;height:36px;font-size:.8rem;"><?php echo $adminInitial; ?></div>
                    <div>
                        <div class="fw-bold" style="font-size:.82rem;line-height:1.1;"><?php echo htmlspecialchars($adminUsername); ?></div>
                        <div class="text-secondary" style="font-size:.72rem;">Admin</div>
                    </div>
                </button>
                <ul class="dropdown-menu dropdown-menu-end">
                    <?php if (isset($_SESSION['role']) && $_SESSION['role'] === 'superadmin'): ?>
                    <li><a class="dropdown-item" href="settings.php"><i class="bi bi-gear me-2"></i>Settings</a></li>
                    <li><hr class="dropdown-divider"></li>
                    <?php endif; ?>
                    <li><a class="dropdown-item text-danger" href="javascript:void(0)" onclick="confirmLogout()"><i class="bi bi-box-arrow-right me-2"></i>Logout</a></li>
                </ul>
            </div>
        </div>
    </div>

    <!-- main part -->
    <div id="main" class="p-4">
    
    <!-- header -->
    <div class="d-flex align-items-start justify-content-between mb-4">
        <div>
        <h4 class="fw-bold mb-1">Orders</h4>
        <p class="text-secondary mb-0 orders-subtitle">Manage and track all customer orders</p>
        </div>
            <div class="dropdown">
                <button class="btn btn-dark d-flex align-items-center gap-2 fw-semibold orders-export-btn dropdown-toggle"
                        type="button" data-bs-toggle="dropdown" aria-expanded="false">
                        <i class="bi bi-download"></i> Export
                </button>
                <ul class="dropdown-menu dropdown-menu-end shadow-sm">
                    <li>
                        <a class="dropdown-item d-flex align-items-center gap-2" href="orders.php?action=export&format=csv">
                            <i class="bi bi-filetype-csv text-success"></i> Export as CSV
                        </a>
                    </li>
                    <li>
                        <a class="dropdown-item d-flex align-items-center gap-2" href="orders.php?action=export&format=excel">
                        <i class="bi bi-file-earmark-excel text-success"></i> Export as Excel
                        </a>
                    </li>
                    <li>
                        <a class="dropdown-item d-flex align-items-center gap-2" href="javascript:void(0)" onclick="exportPDF()">
                        <i class="bi bi-file-earmark-pdf text-danger"></i> Export as PDF
                        </a>
                    </li>
                </ul>
            </div>
        </div>
    
    <!-- search and filter -->
    <div class="bg-white border rounded-3 p-3 mb-3">
        <div class="d-flex align-items-center gap-2">
        <!-- Search -->
        <div class="position-relative flex-fill">
            <i class="bi bi-search text-secondary search-icon"></i>
            <input type="text" id="orderSearch" class="form-control bg-light border search-input w-100"
                placeholder="Search orders…" oninput="filterOrders()"/>
        </div>
        <!-- status -->
        <select id="statusFilter" class="form-select orders-status-select fw-semibold" onchange="filterOrders()">
            <option value="">All Status</option>
            <option value="Pending">Pending</option>
            <option value="Processing">Processing</option>
            <option value="Shipped">Shipped</option>
            <option value="Delivered">Delivered</option>
            <option value="Cancelled">Cancelled</option>
        </select>
        <!-- filter button -->
        <button class="btn btn-outline-secondary d-flex align-items-center gap-2 fw-semibold orders-filter-btn">
            <i class="bi bi-funnel"></i> Filters
        </button>
        </div>
    </div>
    
    <!-- orders table -->
    <div class="bg-white border rounded-3 p-3">
        <div class="table-responsive">
        <table class="table orders-table table-hover align-middle mb-0">
            <thead class="fw-bold border-bottom">
            <tr>
                <th class="px-3 py-3">Order ID</th>
                <th class="px-3 py-3">Customer</th>
                <th class="px-3 py-3">Date</th>
                <th class="px-3 py-3">Amount</th>
                <th class="px-3 py-3">Status</th>
                <th class="px-3 py-3">Actions</th>
            </tr>
            </thead>
            <tbody id="ordersTableBody">
            <?php if (!empty($orders)): ?>
                <?php foreach ($orders as $order): ?>
                <tr class="border-bottom border-light-subtle" data-status="<?php echo $order['status']; ?>" data-id="<?php echo $order['order_code']; ?>">
                    <td class="px-3 py-3 fw-semibold"><?php echo htmlspecialchars($order['order_code']); ?></td>
                    <td class="px-3 py-3"><?php echo htmlspecialchars($order['first_name'] . ' ' . $order['last_name']); ?></td>
                    <td class="px-3 py-3 text-secondary"><?php echo date('Y-m-d', strtotime($order['created_at'])); ?></td>
                    <td class="px-3 py-3 fw-semibold">₱<?php echo number_format($order['total_amount'], 2); ?></td>
                    <td class="px-3 py-3"><span class="orders-status-badge status-<?php echo strtolower($order['status']); ?>"><?php echo htmlspecialchars($order['status']); ?></span></td>
                    <td class="px-3 py-3">
                        <a href="#" class="products-edit-link view-order-link me-2" data-order-id="<?php echo $order['id']; ?>">View</a>
                        <a href="#" class="products-edit-link edit-order-link" data-order-id="<?php echo $order['id']; ?>" data-order-status="<?php echo $order['status']; ?>">Edit</a>
                    </td>
                </tr>
                <?php endforeach; ?>
            <?php else: ?>
                <tr>
                    <td colspan="6" class="text-center py-4">
                        <i class="bi bi-cart-x display-1 text-secondary mb-3 d-block"></i>
                        <h5 class="text-secondary">No orders found</h5>
                        <p class="text-muted">Orders will appear here once customers place them.</p>
                    </td>
                </tr>
            <?php endif; ?>
            </tbody>
        </table>
        </div>
    
    </div>

    <!-- Order Details Modal -->
    <div class="modal fade" id="orderDetailsModal" tabindex="-1" aria-labelledby="orderDetailsModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header border-bottom">
                    <h5 class="modal-title fw-bold" id="orderDetailsModalLabel">Order Details</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <div id="orderDetailsLoadingSpinner" class="text-center py-5">
                        <div class="spinner-border text-primary" role="status">
                            <span class="visually-hidden">Loading...</span>
                        </div>
                    </div>
                    <div id="orderDetailsContent" class="d-none">
                        <!-- Order Information Section -->
                        <div class="mb-4">
                            <h6 class="fw-bold text-primary mb-3">Order Information</h6>
                            <div class="row g-2">
                                <div class="col-md-6">
                                    <label class="text-secondary small fw-semibold">Order ID</label>
                                    <p id="orderId" class="mb-0">-</p>
                                </div>
                                <div class="col-md-6">
                                    <label class="text-secondary small fw-semibold">Customer</label>
                                    <p id="orderCustomer" class="mb-0">-</p>
                                </div>
                                <div class="col-md-6">
                                    <label class="text-secondary small fw-semibold">Date</label>
                                    <p id="orderDate" class="mb-0">-</p>
                                </div>
                                <div class="col-md-6">
                                    <label class="text-secondary small fw-semibold">Status</label>
                                    <p id="orderStatus" class="mb-0">-</p>
                                </div>
                            </div>
                        </div>

                        <hr class="my-4">

                        <!-- Order Items Section -->
                        <div class="mb-4">
                            <h6 class="fw-bold text-primary mb-3">Order Items</h6>
                            <div id="orderItemsList">
                                <p class="text-secondary">No items</p>
                            </div>
                        </div>

                        <hr class="my-4">

                        <!-- Order Total -->
                        <div class="text-end">
                            <h5 class="fw-bold">Total: <span id="orderTotal">₱0.00</span></h5>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Edit Order Status Modal -->
    <div class="modal fade" id="editOrderModal" tabindex="-1" aria-labelledby="editOrderModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content">
                <div class="modal-header border-bottom">
                    <h5 class="modal-title fw-bold" id="editOrderModalLabel">Edit Order Status</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <form id="editOrderForm">
                        <input type="hidden" id="editOrderId" name="order_id">
                        <div class="mb-3">
                            <label for="editOrderStatus" class="form-label fw-semibold">Order Status</label>
                            <select class="form-select" id="editOrderStatus" name="status" required>
                                <option value="Pending">Pending</option>
                                <option value="Processing">Processing</option>
                                <option value="Shipped">Shipped</option>
                                <option value="Delivered">Delivered</option>
                                <option value="Cancelled">Cancelled</option>
                            </select>
                        </div>
                        <div class="text-end">
                            <button type="button" class="btn btn-secondary me-2" data-bs-dismiss="modal">Cancel</button>
                            <button type="submit" class="btn btn-primary">Update Status</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <!-- Logout Confirmation Modal -->
    <div class="modal fade" id="logoutModal" tabindex="-1" aria-labelledby="logoutModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content">
                <div class="modal-header border-bottom bg-danger bg-opacity-10">
                    <h5 class="modal-title fw-bold text-danger" id="logoutModalLabel">
                        <i class="bi bi-exclamation-triangle me-2"></i>Confirm Logout
                    </h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <p class="mb-2">Are you sure you want to log out?</p>
                    <p class="text-secondary small mb-0">You will be redirected to the login page. Any unsaved changes will be lost.</p>
                </div>
                <div class="modal-footer border-top">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <a href="../db/action/logout.php" class="btn btn-danger">
                        <i class="bi bi-box-arrow-right me-2"></i>Logout
                    </a>
                </div>
            </div>
        </div>
    </div>

    <script src="admin.js"></script>
    <script>
        function confirmLogout() {
            const modal = new bootstrap.Modal(document.getElementById('logoutModal'));
            modal.show();
        }

        // Handle edit order link clicks
        document.addEventListener('click', function(e) {
            if (e.target.classList.contains('edit-order-link')) {
                e.preventDefault();
                const orderId = e.target.getAttribute('data-order-id');
                const currentStatus = e.target.getAttribute('data-order-status');

                // Set form values
                document.getElementById('editOrderId').value = orderId;
                document.getElementById('editOrderStatus').value = currentStatus;

                // Show modal
                const modal = new bootstrap.Modal(document.getElementById('editOrderModal'));
                modal.show();
            }
        });

        // Handle edit order form submission
        document.getElementById('editOrderForm').addEventListener('submit', function(e) {
            e.preventDefault();

            const formData = new FormData(this);

            fetch('orders.php?action=update_status', {
                method: 'POST',
                body: formData
            })
            .then(response => {
                if (!response.ok) {
                    throw new Error('Network response was not ok: ' + response.status);
                }
                return response.json();
            })
            .then(data => {
                if (data.success) {
                    // Close modal
                    const modal = bootstrap.Modal.getInstance(document.getElementById('editOrderModal'));
                    modal.hide();

                    // Show success message
                    alert('Order status updated successfully!');

                    // Reload page to refresh the table
                    location.reload();
                } else {
                    alert('Error: ' + data.message);
                }
            })
            .catch(error => {
                console.error('Error:', error);
                alert('An error occurred while updating the order status: ' + error.message);
            });
        });
    </script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/jspdf/2.5.1/jspdf.umd.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/jspdf-autotable/3.8.2/jspdf.plugin.autotable.min.js"></script>
    <script>
        function exportPDF() {
    const { jsPDF } = window.jspdf;
    const doc = new jsPDF();

    doc.setFontSize(16);
    doc.text('Orders Report', 14, 15);
    doc.setFontSize(10);
    doc.text('Generated: ' + new Date().toLocaleDateString(), 14, 22);

    const rows = [];
    document.querySelectorAll('#ordersTableBody tr[data-id]').forEach(row => {
        const cells = row.querySelectorAll('td');
        if (cells.length >= 5) {
            rows.push([
                cells[0].innerText,
                cells[1].innerText,
                cells[2].innerText,
                cells[3].innerText,
                cells[4].innerText.trim()
            ]);
        }
    });

    doc.autoTable({
        head: [['Order ID', 'Customer', 'Date', 'Amount', 'Status']],
        body: rows,
        startY: 28,
        styles: { fontSize: 9 },
        headStyles: { fillColor: [30, 30, 30] }
    });

    doc.save('orders_' + new Date().toISOString().slice(0, 10) + '.pdf');
}
    </script>
</body>
</html>