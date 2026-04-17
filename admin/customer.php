<?php
// Database connection
require_once '../db/action/dbconfig.php';

// Start session to get admin info
session_start();

// Get current admin info
$adminUsername = isset($_SESSION['username']) ? $_SESSION['username'] : 'Admin';
$adminInitial = strtoupper(substr($adminUsername, 0, 1));

// Handle AJAX requests FIRST - before any HTML output
if (isset($_GET['action'])) {
    if ($_GET['action'] === 'get_customer_details') {
    
    $customer_id = intval($_GET['id'] ?? 0);
    
    if ($customer_id === 0) {
        echo json_encode(['success' => false, 'message' => 'Invalid customer ID']);
        exit;
    }
    
    try {
        // Get customer info
        $stmt = $conn->prepare("SELECT * FROM login WHERE id = ? AND role = 'user'");
        $stmt->execute([$customer_id]);
        $customer = $stmt->fetch(PDO::FETCH_ASSOC);

        if (!$customer) {
            echo json_encode(['success' => false, 'message' => 'Customer not found']);
            exit;
        }

        // Get purchase history
        $stmt = $conn->prepare("
            SELECT o.id, o.order_code, o.total_amount, o.status, o.created_at
            FROM orders o
            WHERE o.user_id = ?
            ORDER BY o.created_at DESC
        ");
        $stmt->execute([$customer_id]);
        $orders = $stmt->fetchAll(PDO::FETCH_ASSOC);

        // Get order items for each order
        $ordersWithItems = [];
        foreach ($orders as $order) {
            $stmt = $conn->prepare("
                SELECT oi.id, oi.quantity, oi.unit_price, oi.subtotal, p.name as product_name
                FROM order_items oi
                JOIN products p ON oi.product_id = p.id
                WHERE oi.order_id = ?
            ");
            $stmt->execute([$order['id']]);
            $order['items'] = $stmt->fetchAll(PDO::FETCH_ASSOC);
            $ordersWithItems[] = $order;
        }

        echo json_encode([
            'success' => true,
            'customer' => $customer,
            'orders' => $ordersWithItems
        ]);
    } catch (Exception $e) {
        echo json_encode(['success' => false, 'message' => 'Error: ' . $e->getMessage()]);
    }
    exit;
}
}

// Handle customer deletion
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'delete_customer') {
    header('Content-Type: application/json; charset=utf-8');

    $customer_id = intval($_POST['customer_id'] ?? 0);

    if ($customer_id === 0) {
        echo json_encode(['success' => false, 'message' => 'Invalid customer ID']);
        exit;
    }

    try {
        // Check if customer has orders
        $stmt = $conn->prepare("SELECT COUNT(*) as order_count FROM orders WHERE user_id = ?");
        $stmt->execute([$customer_id]);
        $order_count = $stmt->fetch(PDO::FETCH_ASSOC)['order_count'];

        if ($order_count > 0) {
            echo json_encode(['success' => false, 'message' => 'Cannot delete customer with existing orders. Please cancel all orders first.']);
            exit;
        }

        // Delete customer
        $stmt = $conn->prepare("DELETE FROM login WHERE id = ? AND role = 'user'");
        $stmt->execute([$customer_id]);

        if ($stmt->rowCount() > 0) {
            echo json_encode(['success' => true, 'message' => 'Customer deleted successfully']);
        } else {
            echo json_encode(['success' => false, 'message' => 'Customer not found or already deleted']);
        }
    } catch (Exception $e) {
        echo json_encode(['success' => false, 'message' => 'Error deleting customer: ' . $e->getMessage()]);
    }
    exit;
}

// Fetch all customers with their order stats (only for page load)
$stmt = $conn->prepare("
    SELECT 
        l.id,
        l.first_name,
        l.last_name,
        l.email,
        l.login_date as joined_date,
        COUNT(DISTINCT o.id) as order_count,
        COALESCE(SUM(o.total_amount), 0) as total_spent
    FROM login l
    LEFT JOIN orders o ON l.id = o.user_id
    WHERE l.role = 'user'
    GROUP BY l.id, l.first_name, l.last_name, l.email, l.login_date
    ORDER BY l.login_date DESC
");
$stmt->execute();
$customers = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Customers</title>
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
                <a href="orders.php" class="nav-link d-flex align-items-center gap-2 fw-semibold text-secondary rounded mx-2 my-1 px-3 py-2">
                    <i class="bi bi-cart-fill"></i>
                    Orders
                </a>
            </li>
            <li class="nav-item">
                <a href="product.php" class="nav-link d-flex align-items-center gap-2 fw-semibold rounded text-secondary mx-2 my-1 px-3 py-2">
                    <i class="bi bi-box-seam-fill"></i>
                    Products
                </a>
            </li>
            <li class="nav-item">
                <a href ="categories.php"class="nav-link d-flex align-items-center gap-2 fw-semibold rounded text-secondary mx-2 my-1 px-3 py-2">
                    <i class="bi bi-folder"></i>
                    Categories
                </a>
            </li>
            <li class="nav-item">
                <a href ="customer.php" class="nav-link active d-flex align-items-center gap-2 fw-semibold rounded mx-2 my-1 px-3 py-2">
                    <i class="bi bi-people"></i>
                    Customers
                </a>
            </li>
            <li class="nav-item">
                <a href ="analytics.php" class="nav-link d-flex align-items-center gap-2 fw-semibold rounded text-secondary mx-2 my-1 px-3 py-2">
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
                <h4 class="fw-bold mb-1">Customers</h4>
                <p class="text-secondary mb-0 products-subtitle">Manage your customer relationships</p>
            </div>
        </div>
 
        <!-- search only (no category filter or view toggle) -->
        <div class="bg-white border rounded-3 p-3 mb-3">
            <div class="d-flex align-items-center gap-2">
                <!-- Search -->
                <div class="position-relative flex-fill">
                    <i class="bi bi-search text-secondary search-icon"></i>
                    <input type="text" id="customerSearch" class="form-control bg-light border search-input w-100"
                           placeholder="Search customers…"/>
                </div>
            </div>
        </div>
 
        <!-- List view -->
        <div id="listView" class="bg-white border rounded-3 p-3">
            <div class="table-responsive">
                <table class="table products-table table-hover align-middle mb-0">
                    <thead class="fw-bold border-bottom">
                        <tr>
                            <th class="px-3 py-3">Customer</th>
                            <th class="px-3 py-3">Email</th>
                            <th class="px-3 py-3">Orders</th>
                            <th class="px-3 py-3">Total Spent</th>
                            <th class="px-3 py-3">Joined</th>
                            <th class="px-3 py-3">Actions</th>
                        </tr>
                    </thead>
                    <tbody id="customersTableBody">
                        <?php if (!empty($customers)): ?>
                            <?php foreach ($customers as $customer): 
                                $customerName = htmlspecialchars($customer['first_name'] . ' ' . $customer['last_name']);
                                $customerEmail = htmlspecialchars($customer['email']);
                                $joinedDate = date('Y-m-d', strtotime($customer['joined_date']));
                                $initials = strtoupper(substr($customer['first_name'], 0, 1)) . strtoupper(substr($customer['last_name'], 0, 1));
                            ?>
                            <tr class="border-bottom border-light-subtle" data-customer-id="<?php echo $customer['id']; ?>" data-customer-name="<?php echo $customerName; ?>">
                                <td class="px-3 py-3">
                                    <div class="d-flex align-items-center gap-3">
                                        <div class="rounded-circle bg-primary d-flex align-items-center justify-content-center text-white fw-bold flex-shrink-0" style="width:40px;height:40px;font-size:.9rem;">
                                            <?php echo $initials; ?>
                                        </div>
                                        <span class="fw-semibold"><?php echo $customerName; ?></span>
                                    </div>
                                </td>
                                <td class="px-3 py-3 text-secondary"><?php echo $customerEmail; ?></td>
                                <td class="px-3 py-3 fw-semibold"><?php echo number_format($customer['order_count']); ?></td>
                                <td class="px-3 py-3 fw-semibold">₱<?php echo number_format($customer['total_spent'], 2); ?></td>
                                <td class="px-3 py-3 text-secondary"><?php echo $joinedDate; ?></td>
                                <td class="px-3 py-3">
                                    <a href="javascript:void(0)" class="products-edit-link me-2" onclick="viewCustomer(<?php echo $customer['id']; ?>)">View</a>
                                    <a href="javascript:void(0)" class="products-edit-link text-danger" onclick="deleteCustomer(<?php echo $customer['id']; ?>, '<?php echo addslashes($customerName); ?>')">Delete</a>
                                </td>
                            </tr>
                            <?php endforeach; ?>
                        <?php else: ?>
                            <tr class="border-bottom border-light-subtle">
                                <td colspan="6" class="px-3 py-3 text-center text-secondary">
                                    No customers found
                                </td>
                            </tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
 
            <div id="customersEmptyState" class="text-center text-secondary py-5 d-none">
                <i class="bi bi-people orders-empty-icon d-block mb-2"></i>
                No customers match your search.
            </div>
        </div>
 
        <!-- Grid view -->
        <div id="gridView" class="d-none">
            <div class="row g-3" id="customersGridBody">
                <!-- Grid items would be here if view toggles were enabled -->
            </div>
 
            <div id="gridEmptyState" class="text-center text-secondary py-5 d-none">
                <i class="bi bi-people orders-empty-icon d-block mb-2"></i>
                No customers match your search.
            </div>
        </div>
 
    </div>

    <!-- Customer Details Modal -->
    <div class="modal fade" id="customerDetailsModal" tabindex="-1" aria-labelledby="customerDetailsModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header border-bottom">
                    <h5 class="modal-title fw-bold" id="customerDetailsModalLabel">Customer Details</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <div id="detailsLoadingSpinner" class="text-center py-5">
                        <div class="spinner-border text-primary" role="status">
                            <span class="visually-hidden">Loading...</span>
                        </div>
                    </div>
                    <div id="detailsContent" class="d-none">
                        <!-- Contact Information Section -->
                        <div class="mb-4">
                            <h6 class="fw-bold text-primary mb-3">Contact Information</h6>
                            <div class="row g-2">
                                <div class="col-md-6">
                                    <label class="text-secondary small fw-semibold">Full Name</label>
                                    <p id="detailsFullName" class="mb-0">-</p>
                                </div>
                                <div class="col-md-6">
                                    <label class="text-secondary small fw-semibold">Email</label>
                                    <p id="detailsEmail" class="mb-0">-</p>
                                </div>
                                <div class="col-md-6">
                                    <label class="text-secondary small fw-semibold">Username</label>
                                    <p id="detailsUsername" class="mb-0">-</p>
                                </div>
                                <div class="col-md-6">
                                    <label class="text-secondary small fw-semibold">Member Since</label>
                                    <p id="detailsJoinDate" class="mb-0">-</p>
                                </div>
                            </div>
                        </div>

                        <hr class="my-4">

                        <!-- Purchase History Section -->
                        <div class="mb-4">
                            <h6 class="fw-bold text-primary mb-3">Purchase History</h6>
                            <div id="ordersList">
                                <p class="text-secondary">No orders yet</p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Delete Customer Modal -->
    <div class="modal fade" id="deleteCustomerModal" tabindex="-1" aria-labelledby="deleteCustomerModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content">
                <div class="modal-header border-bottom bg-danger bg-opacity-10">
                    <h5 class="modal-title fw-bold text-danger" id="deleteCustomerModalLabel">
                        <i class="bi bi-exclamation-triangle me-2"></i>Delete Customer
                    </h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <p class="mb-2">Are you sure you want to delete customer "<span id="deleteCustomerName"></span>"?</p>
                    <p class="text-secondary small mb-0">This action cannot be undone and will permanently remove the customer from the system.</p>
                </div>
                <div class="modal-footer border-top">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="button" class="btn btn-danger" id="confirmDeleteCustomerBtn">
                        <i class="bi bi-trash me-2"></i>Delete Customer
                    </button>
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

        function deleteCustomer(customerId, customerName) {
            document.getElementById('deleteCustomerName').textContent = customerName;
            document.getElementById('confirmDeleteCustomerBtn').onclick = function() {
                const formData = new FormData();
                formData.append('action', 'delete_customer');
                formData.append('customer_id', customerId);

                fetch('customer.php', {
                    method: 'POST',
                    body: formData
                })
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        // Close modal
                        const modal = bootstrap.Modal.getInstance(document.getElementById('deleteCustomerModal'));
                        modal.hide();

                        // Show success message
                        alert('Customer deleted successfully!');

                        // Reload page to refresh the table
                        location.reload();
                    } else {
                        alert('Error: ' + data.message);
                    }
                })
                .catch(error => {
                    console.error('Error:', error);
                    alert('An error occurred while deleting the customer.');
                });
            };

            const modal = new bootstrap.Modal(document.getElementById('deleteCustomerModal'));
            modal.show();
        }

        function filterCustomers() {
            const searchInput = document.getElementById('customerSearch');
            const tableBody = document.getElementById('customersTableBody');
            const emptyState = document.getElementById('customersEmptyState');

            if (!searchInput || !tableBody) return;

            const query = searchInput.value.toLowerCase().trim();
            const rows = tableBody.querySelectorAll('tr');
            let visibleCount = 0;

            rows.forEach(row => {
                const name = row.cells[0]?.textContent.toLowerCase() || '';
                const email = row.cells[1]?.textContent.toLowerCase() || '';
                const matches = query === '' || name.includes(query) || email.includes(query);

                row.style.display = matches ? '' : 'none';
                if (matches) visibleCount++;
            });

            if (emptyState) {
                emptyState.classList.toggle('d-none', visibleCount !== 0);
            }
        }

        const customerSearchInput = document.getElementById('customerSearch');
        if (customerSearchInput) {
            customerSearchInput.addEventListener('input', filterCustomers);
        }
    </script>
</body>
</html>