<?php
/**
 * ==========================================
 * ADMIN DASHBOARD - MAIN INTERFACE
 * ==========================================
 * 
 * Purpose: Central administration panel for managing all business operations
 * Access: Admin users only (requireAdmin() verification)
 * 
 * Main Sections:
 * - Dashboard: Sales analytics, revenue reports, business metrics
 * - Products: Add, edit, delete products from catalog
 * - Categories: Manage product categories
 * - Customers: View and manage customer accounts
 * - Orders: Track and update customer orders
 * - Analytics: Business performance metrics
 * 
 * Database: Uses PDO prepared statements for security
 * Sessions: Sets success/error messages for user feedback
 */

session_start();
require_once '../auth.php';
requireAdmin(); // Ensure only admin users can access this page

// Determine which page/section to display
$page = isset($_GET['page']) ? $_GET['page'] : 'dashboard';

// ==========================================
// DATABASE CONNECTION
// ==========================================
// Establish database connection for all database operations
require_once '../db/connection.php';

// Helper for uploading product images
function uploadProductImage($file) {
    if (empty($file) || $file['error'] !== UPLOAD_ERR_OK) {
        return null;
    }

    $allowedExtensions = ['jpg', 'jpeg', 'png', 'gif'];
    $fileName = basename($file['name']);
    $extension = strtolower(pathinfo($fileName, PATHINFO_EXTENSION));
    if (!in_array($extension, $allowedExtensions, true)) {
        return null;
    }

    $uploadDir = dirname(__DIR__) . '/images/products';
    if (!is_dir($uploadDir)) {
        mkdir($uploadDir, 0755, true);
    }

    $newFileName = time() . '_' . bin2hex(random_bytes(5)) . '.' . $extension;
    $destination = $uploadDir . '/' . $newFileName;

    if (move_uploaded_file($file['tmp_name'], $destination)) {
        return 'images/products/' . $newFileName;
    }

    return null;
}

// ==========================================
// FORM PROCESSING - CRUD OPERATIONS
// ==========================================
// Handle POST requests from forms (create, read, update, delete operations)
// All operations use prepared statements to prevent SQL injection

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['action'])) {
        $action = $_POST['action'];
        
        try {
            // ==========================================
            // PRODUCT MANAGEMENT - Add, Edit, Delete
            // ==========================================
            
            if ($action === 'add_product') {
                // Add new product to catalog
                $imagePath = uploadProductImage($_FILES['image'] ?? null);
                $stmt = $conn->prepare("INSERT INTO products (name, category_id, description, price, stock_quantity, image_path, status) VALUES (?, ?, ?, ?, ?, ?, ?)");
                $stmt->execute([
                    $_POST['name'],
                    $_POST['category_id'] ?: null,
                    $_POST['description'],
                    $_POST['price'],
                    $_POST['stock_quantity'],
                    $imagePath,
                    $_POST['status']
                ]);
                $_SESSION['success'] = 'Product added successfully!';
                header('Location: ?page=products');
                exit();
            } 
            elseif ($action === 'edit_product') {
                // Update existing product information
                $imagePath = $_POST['existing_image'] ?? null;
                if (!empty($_FILES['image']['name'])) {
                    $uploadedImage = uploadProductImage($_FILES['image']);
                    if ($uploadedImage !== null) {
                        $imagePath = $uploadedImage;
                    }
                }
                $stmt = $conn->prepare("UPDATE products SET name=?, category_id=?, description=?, price=?, stock_quantity=?, image_path=?, status=? WHERE id=?");
                $stmt->execute([
                    $_POST['name'],
                    $_POST['category_id'] ?: null,
                    $_POST['description'],
                    $_POST['price'],
                    $_POST['stock_quantity'],
                    $imagePath,
                    $_POST['status'],
                    $_POST['id']
                ]);
                $_SESSION['success'] = 'Product updated successfully!';
                header('Location: ?page=products');
                exit();
            } 
            elseif ($action === 'delete_product') {
                // Remove product from catalog
                $stmt = $conn->prepare("DELETE FROM products WHERE id=?");
                $stmt->execute([$_POST['id']]);
                $_SESSION['success'] = 'Product deleted successfully!';
                header('Location: ?page=products');
                exit();
            }
            
            // ==========================================
            // CATEGORY MANAGEMENT - Add, Edit, Delete
            // ==========================================
            
            elseif ($action === 'add_category') {
                // Create new product category
                $stmt = $conn->prepare("INSERT INTO categories (name, description) VALUES (?, ?)");
                $stmt->execute([$_POST['name'], $_POST['description']]);
                $_SESSION['success'] = 'Category added successfully!';
                header('Location: ?page=categories');
                exit();
            } 
            elseif ($action === 'edit_category') {
                // Update category details
                $stmt = $conn->prepare("UPDATE categories SET name=?, description=? WHERE id=?");
                $stmt->execute([$_POST['name'], $_POST['description'], $_POST['id']]);
                $_SESSION['success'] = 'Category updated successfully!';
                header('Location: ?page=categories');
                exit();
            } 
            elseif ($action === 'delete_category') {
                // Remove category and unlink products from it
                // First set products category_id to NULL (preserve product data)
                $stmt = $conn->prepare("UPDATE products SET category_id = NULL WHERE category_id = ?");
                $stmt->execute([$_POST['id']]);
                // Then delete the category itself
                $stmt = $conn->prepare("DELETE FROM categories WHERE id=?");
                $stmt->execute([$_POST['id']]);
                $_SESSION['success'] = 'Category deleted successfully!';
                header('Location: ?page=categories');
                exit();
            }
            
            // ==========================================
            // CUSTOMER MANAGEMENT - Add, Edit, Delete
            // ==========================================
            
            elseif ($action === 'add_customer') {
                // Create new customer account
                $hashed_pwd = password_hash($_POST['password'], PASSWORD_DEFAULT); // Secure password hashing
                $stmt = $conn->prepare("INSERT INTO users (first_name, last_name, email, password, is_admin) VALUES (?, ?, ?, ?, 0)");
                $stmt->execute([$_POST['first_name'], $_POST['last_name'], $__POST['email'], $hashed_pwd]);
                $_SESSION['success'] = 'Customer added successfully!';
                header('Location: ?page=customers');
                exit();
            } 
            elseif ($action === 'edit_customer') {
                // Update customer account information
                $stmt = $conn->prepare("UPDATE users SET first_name=?, last_name=?, email=? WHERE id=? AND is_admin=0");
                $stmt->execute([$_POST['first_name'], $_POST['last_name'], $_POST['email'], $_POST['id']]);
                $_SESSION['success'] = 'Customer updated successfully!';
                header('Location: ?page=customers');
                exit();
            } 
            elseif ($action === 'delete_customer') {
                // Remove customer account (orders are preserved in database)
                $stmt = $conn->prepare("DELETE FROM users WHERE id=? AND is_admin=0");
                $stmt->execute([$_POST['id']]);
                $_SESSION['success'] = 'Customer deleted successfully!';
                header('Location: ?page=customers');
                exit();
            }
            
            // ==========================================
            // ORDER MANAGEMENT - Update Status
            // ==========================================
            
            elseif ($action === 'update_order_status') {
                // Update order status (Pending → Processing → Shipped → Completed)
                $stmt = $conn->prepare("UPDATE orders SET status=? WHERE id=?");
                $stmt->execute([$_POST['status'], $_POST['id']]);
                $_SESSION['success'] = 'Order status updated successfully!';
                header('Location: ?page=orders');
                exit();
            }
        } catch (PDOException $e) {
            // Catch and display database errors
            $_SESSION['error'] = 'Error: ' . $e->getMessage();
            header('Location: ' . $_SERVER['HTTP_REFERER']);
            exit();
        }
    }
}

// ==========================================
// DASHBOARD DATA QUERIES
// ==========================================
// Fetch real-time statistics and metrics for dashboard display

if ($page === 'dashboard') {
    // Total revenue from completed orders
    // Displays total sales amount from all successfully completed orders
    $stmt = $conn->prepare("SELECT SUM(total_amount) as total_revenue FROM orders WHERE status = 'Completed'");
    $stmt->execute();
    $revenue = $stmt->fetch(PDO::FETCH_ASSOC)['total_revenue'] ?? 0;

    // Total number of orders
    // Count all orders in the system regardless of status
    $stmt = $conn->prepare("SELECT COUNT(*) as total_orders FROM orders");
    $stmt->execute();
    $total_orders = $stmt->fetch(PDO::FETCH_ASSOC)['total_orders'] ?? 0;

    // Total customers
    $stmt = $conn->prepare("SELECT COUNT(*) as total_customers FROM users WHERE is_admin = 0");
    $stmt->execute();
    $total_customers = $stmt->fetch(PDO::FETCH_ASSOC)['total_customers'] ?? 0;

    // Total products
    $stmt = $conn->prepare("SELECT COUNT(*) as total_products FROM products");
    $stmt->execute();
    $total_products = $stmt->fetch(PDO::FETCH_ASSOC)['total_products'] ?? 0;

    // Monthly sales data for chart (last 6 months)
    $monthly_sales = [];
    for ($i = 5; $i >= 0; $i--) {
        $month = date('Y-m', strtotime("-$i months"));
        $stmt = $conn->prepare("SELECT SUM(total_amount) as monthly FROM orders WHERE DATE_FORMAT(order_date, '%Y-%m') = ? AND status = 'Completed'");
        $stmt->execute([$month]);
        $monthly_sales[] = $stmt->fetch(PDO::FETCH_ASSOC)['monthly'] ?? 0;
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Dashboard - SipFlask</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.0.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.0/font/bootstrap-icons.css">
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <style>
        body {
            background-color: #f8f9fa;
        }
        .sidebar {
            background-color: #008080;
            min-height: calc(100vh - 56px);
            padding: 20px 0;
            position: sticky;
            top: 56px;
        }
        .sidebar-btn {
            color: white;
            text-align: left;
            width: 100%;
            padding: 12px 20px;
            border: none;
            background: transparent;
            font-size: 14px;
            font-weight: 500;
            transition: all 0.3s ease;
            border-left: 4px solid transparent;
        }
        .sidebar-btn:hover {
            background-color: rgba(255, 255, 255, 0.1);
            border-left-color: white;
        }
        .sidebar-btn.active {
            background-color: rgba(255, 255, 255, 0.2);
            border-left-color: white;
        }
        .sidebar-btn i {
            margin-right: 10px;
            width: 20px;
        }
        .main-content {
            padding: 40px 20px;
        }
        .content-header {
            margin-bottom: 30px;
        }
        .content-header h2 {
            color: #008080;
            font-weight: bold;
            margin-bottom: 10px;
        }
        .stat-card {
            transition: transform 0.2s;
        }
        .stat-card:hover {
            transform: translateY(-5px);
        }
    </style>
</head>
<body>
    <nav class="navbar navbar-expand-lg navbar-light bg-white shadow-sm sticky-top">
        <div class="container-fluid">
            <div class="d-flex align-items-center">
                <a class="navbar-brand fw-bold me-0" href="index.php">
                    <i class="bi bi-cup-straw"></i> SipFlask Admin
                </a>
                <span class="navbar-text text-white fw-bold ms-4" style="font-size: 1.2em;">Hello, <?= htmlspecialchars($_SESSION['user_name']) ?></span>
            </div>
            <div class="collapse navbar-collapse justify-content-end">
                <ul class="navbar-nav align-items-center">
                    <li class="nav-item me-3">
                        <a class="nav-link fw-bold" href="../index.php">
                            <i class="bi bi-shop"></i> Store Front
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="btn btn-outline-danger btn-sm" href="../logout.php">
                            <i class="bi bi-box-arrow-right"></i> Logout
                        </a>
                    </li>
                </ul>
            </div>
        </div>
    </nav>

    <?php if (isset($_SESSION['success'])): ?>
        <div class="alert alert-success alert-dismissible fade show m-3" role="alert">
            <i class="bi bi-check-circle-fill me-2"></i><?= $_SESSION['success'] ?>
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
        <?php unset($_SESSION['success']); ?>
    <?php endif; ?>
    
    <?php if (isset($_SESSION['error'])): ?>
        <div class="alert alert-danger alert-dismissible fade show m-3" role="alert">
            <i class="bi bi-exclamation-triangle-fill me-2"></i><?= $_SESSION['error'] ?>
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
        <?php unset($_SESSION['error']); ?>
    <?php endif; ?>

    <div class="container-fluid">
        <div class="row g-0">
            <!-- Sidebar -->
            <div class="col-12 col-md-3 col-lg-2 sidebar">
                <button class="sidebar-btn <?= $page === 'dashboard' ? 'active' : '' ?>" onclick="location.href='index.php'">
                    <i class="bi bi-speedometer2"></i> Dashboard
                </button>
                <button class="sidebar-btn <?= $page === 'orders' ? 'active' : '' ?>" onclick="location.href='?page=orders'">
                    <i class="bi bi-cart-check"></i> Orders
                </button>
                <button class="sidebar-btn <?= $page === 'products' ? 'active' : '' ?>" onclick="location.href='?page=products'">
                    <i class="bi bi-box-seam"></i> Products
                </button>
                <button class="sidebar-btn <?= $page === 'customers' ? 'active' : '' ?>" onclick="location.href='?page=customers'">
                    <i class="bi bi-people"></i> Customers
                </button>
                <button class="sidebar-btn <?= $page === 'categories' ? 'active' : '' ?>" onclick="location.href='?page=categories'">
                    <i class="bi bi-tag"></i> Categories
                </button>
                <button class="sidebar-btn <?= $page === 'analytics' ? 'active' : '' ?>" onclick="location.href='?page=analytics'">
                    <i class="bi bi-graph-up"></i> Analytics
                </button>
            </div>

            <!-- Main Content -->
            <div class="col-12 col-md-9 col-lg-10 main-content">
                <?php if ($page === 'dashboard'): ?>
                    <div class="content-header">
                        <h2>Dashboard</h2>
                        <p class="text-muted">Welcome to your admin dashboard. Monitor your store's performance.</p>
                    </div>
                    <div class="row g-4">
                        <div class="col-12 col-md-6 col-lg-3">
                            <div class="card border-0 shadow-sm stat-card">
                                <div class="card-body">
                                    <div class="d-flex justify-content-between align-items-start">
                                        <div>
                                            <p class="text-muted small mb-1">Total Orders</p>
                                            <h3 class="mb-0" style="color: #008080;"><?= $total_orders ?></h3>
                                        </div>
                                        <i class="bi bi-cart-check fs-3" style="color: #008080;"></i>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="col-12 col-md-6 col-lg-3">
                            <div class="card border-0 shadow-sm stat-card">
                                <div class="card-body">
                                    <div class="d-flex justify-content-between align-items-start">
                                        <div>
                                            <p class="text-muted small mb-1">Total Customers</p>
                                            <h3 class="mb-0" style="color: #008080;"><?= $total_customers ?></h3>
                                        </div>
                                        <i class="bi bi-people fs-3" style="color: #008080;"></i>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="col-12 col-md-6 col-lg-3">
                            <div class="card border-0 shadow-sm stat-card">
                                <div class="card-body">
                                    <div class="d-flex justify-content-between align-items-start">
                                        <div>
                                            <p class="text-muted small mb-1">Total Products</p>
                                            <h3 class="mb-0" style="color: #008080;"><?= $total_products ?></h3>
                                        </div>
                                        <i class="bi bi-box-seam fs-3" style="color: #008080;"></i>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="col-12 col-md-6 col-lg-3">
                            <div class="card border-0 shadow-sm stat-card">
                                <div class="card-body">
                                    <div class="d-flex justify-content-between align-items-start">
                                        <div>
                                            <p class="text-muted small mb-1">Total Revenue</p>
                                            <h3 class="mb-0" style="color: #008080;">₱<?= number_format($revenue, 2) ?></h3>
                                        </div>
                                        <i class="bi bi-cash-coin fs-3" style="color: #008080;"></i>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Sales Chart -->
                    <div class="card border-0 shadow-sm mt-4">
                        <div class="card-header bg-light border-0">
                            <h6 class="mb-0"><i class="bi bi-graph-up"></i> Monthly Sales Revenue</h6>
                        </div>
                        <div class="card-body">
                            <canvas id="salesChart" width="400" height="200"></canvas>
                        </div>
                    </div>

                <?php elseif ($page === 'orders'): ?>
                    <div class="content-header">
                        <h2><i class="bi bi-cart-check"></i> Orders</h2>
                        <p class="text-muted">View and manage all customer orders.</p>
                    </div>
                    <div class="card border-0 shadow-sm">
                        <div class="card-body">
                            <div class="table-responsive">
                                <table class="table table-hover mb-0">
                                    <thead class="table-light">
                                        <tr>
                                            <th>Order ID</th>
                                            <th>Customer</th>
                                            <th>Date</th>
                                            <th>Total Amount</th>
                                            <th>Payment Method</th>
                                            <th>Status</th>
                                            <th>Action</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php
                                        $stmt = $conn->prepare("
                                            SELECT o.*, CONCAT(u.first_name, ' ', u.last_name) as customer_name 
                                            FROM orders o 
                                            JOIN users u ON o.user_id = u.id 
                                            ORDER BY o.order_date DESC
                                        ");
                                        $stmt->execute();
                                        $orders = $stmt->fetchAll(PDO::FETCH_ASSOC);
                                        
                                        if (empty($orders)) {
                                            echo '<tr><td colspan="7" class="text-center text-muted">No orders found.</td></tr>';
                                        } else {
                                            foreach ($orders as $order) {
                                                $status_badge = '';
                                                switch($order['status']) {
                                                    case 'Completed':
                                                        $status_badge = '<span class="badge bg-success">Completed</span>';
                                                        break;
                                                    case 'Processing':
                                                        $status_badge = '<span class="badge bg-warning text-dark">Processing</span>';
                                                        break;
                                                    case 'Shipped':
                                                        $status_badge = '<span class="badge bg-info">Shipped</span>';
                                                        break;
                                                    case 'Pending':
                                                        $status_badge = '<span class="badge bg-secondary">Pending</span>';
                                                        break;
                                                    default:
                                                        $status_badge = '<span class="badge bg-secondary">' . htmlspecialchars($order['status']) . '</span>';
                                                }
                                                echo "<tr>
                                                    <td><strong>#{$order['id']}</strong></td>
                                                    <td>" . htmlspecialchars($order['customer_name']) . "</td>
                                                    <td>" . date('M d, Y h:i A', strtotime($order['order_date'])) . "</td>
                                                    <td><strong>₱" . number_format($order['total_amount'], 2) . "</strong></td>
                                                    <td>" . htmlspecialchars($order['payment_method']) . "</td>
                                                    <td>{$status_badge}</td>
                                                    <td>
                                                        <button type='button' class='btn btn-sm btn-outline-primary' data-bs-toggle='modal' data-bs-target='#orderModal{$order['id']}'>
                                                            <i class='bi bi-eye'></i> View
                                                        </button>
                                                    </td>
                                                </tr>";
                                            }
                                        }
                                        ?>
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>

                    <!-- Order Details Modals -->
                    <?php
                    foreach ($orders as $order) {
                        echo "
                        <div class='modal fade' id='orderModal{$order['id']}' tabindex='-1'>
                            <div class='modal-dialog modal-lg'>
                                <div class='modal-content'>
                                    <div class='modal-header bg-light'>
                                        <h5 class='modal-title'><i class='bi bi-box'></i> Order #{$order['id']} Details</h5>
                                        <button type='button' class='btn-close' data-bs-dismiss='modal'></button>
                                    </div>
                                    <div class='modal-body'>
                                        <div class='row mb-3'>
                                            <div class='col-md-6'>
                                                <h6 class='text-muted'>Customer</h6>
                                                <p class='mb-0'>" . htmlspecialchars($order['customer_name']) . "</p>
                                            </div>
                                            <div class='col-md-6'>
                                                <h6 class='text-muted'>Order Date</h6>
                                                <p class='mb-0'>" . date('M d, Y h:i A', strtotime($order['order_date'])) . "</p>
                                            </div>
                                        </div>
                                        <hr>
                                        <h6 class='mb-3'><i class='bi bi-list'></i> Order Items</h6>
                                        <div class='table-responsive'>
                                            <table class='table table-sm table-bordered'>
                                                <thead class='table-light'>
                                                    <tr>
                                                        <th>Product</th>
                                                        <th>Size</th>
                                                        <th>Qty</th>
                                                        <th>Price</th>
                                                        <th>Subtotal</th>
                                                    </tr>
                                                </thead>
                                                <tbody>";
                                                
                                        $items_stmt = $conn->prepare("SELECT * FROM order_items WHERE order_id = ?");
                                        $items_stmt->execute([$order['id']]);
                                        $items = $items_stmt->fetchAll(PDO::FETCH_ASSOC);
                                        foreach ($items as $item) {
                                            echo "<tr>
                                                <td>" . htmlspecialchars($item['product_name']) . "</td>
                                                <td>" . htmlspecialchars($item['size']) . "</td>
                                                <td>" . $item['quantity'] . "</td>
                                                <td>₱" . number_format($item['price'], 2) . "</td>
                                                <td><strong>₱" . number_format($item['price'] * $item['quantity'], 2) . "</strong></td>
                                            </tr>";
                                        }
                                        
                                        echo "
                                                </tbody>
                                            </table>
                                        </div>
                                        <hr>
                                        <div class='row'>
                                            <div class='col-md-6'>
                                                <p><strong>Subtotal:</strong> ₱" . number_format($order['total_amount'] - $order['shipping_fee'], 2) . "</p>
                                                <p><strong>Shipping Fee:</strong> ₱" . number_format($order['shipping_fee'], 2) . "</p>
                                                <p><strong>Total:</strong> <span style='font-size: 1.25em; color: #008080;'>₱" . number_format($order['total_amount'], 2) . "</span></p>
                                            </div>
                                            <div class='col-md-6'>
                                                <p><strong>Payment Method:</strong> " . htmlspecialchars($order['payment_method']) . "</p>
                                                <p><strong>Message:</strong> " . (empty($order['seller_message']) ? '<em class=\"text-muted\">No message</em>' : htmlspecialchars($order['seller_message'])) . "</p>
                                            </div>
                                        </div>
                                    </div>
                                    <div class='modal-footer'>
                                        <form method='POST' class='w-100'>
                                            <input type='hidden' name='action' value='update_order_status'>
                                            <input type='hidden' name='id' value='{$order['id']}'>
                                            <div class='d-flex gap-2'>
                                                <select name='status' class='form-select form-select-sm'>
                                                    <option value='Pending' " . ($order['status'] == 'Pending' ? 'selected' : '') . ">Pending</option>
                                                    <option value='Processing' " . ($order['status'] == 'Processing' ? 'selected' : '') . ">Processing</option>
                                                    <option value='Shipped' " . ($order['status'] == 'Shipped' ? 'selected' : '') . ">Shipped</option>
                                                    <option value='Completed' " . ($order['status'] == 'Completed' ? 'selected' : '') . ">Completed</option>
                                                </select>
                                                <button type='submit' class='btn btn-sm btn-primary'>Update Status</button>
                                                <button type='button' class='btn btn-sm btn-secondary' data-bs-dismiss='modal'>Close</button>
                                            </div>
                                        </form>
                                    </div>
                                </div>
                            </div>
                        </div>";
                    }
                    ?>
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>

                <?php elseif ($page === 'products'): ?>
                    <div class="content-header">
                        <div class="d-flex justify-content-between align-items-center">
                            <div>
                                <h2><i class="bi bi-box-seam"></i> Products</h2>
                                <p class="text-muted">Add, update, or remove products from your catalog.</p>
                            </div>
                            <a href="?page=add_product" class="btn btn-primary"><i class="bi bi-plus"></i> Add Product</a>
                        </div>
                    </div>
                    <div class="card border-0 shadow-sm">
                        <div class="card-body">
                            <div class="table-responsive">
                                <table class="table table-hover mb-0">
                                    <thead class="table-light">
                                        <tr>
                                            <th>Image</th>
                                            <th>Product Name</th>
                                            <th>Category</th>
                                            <th>Price</th>
                                            <th>Stock</th>
                                            <th>Status</th>
                                            <th>Created</th>
                                            <th>Action</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php
                                        $stmt = $conn->prepare("
                                            SELECT p.*, c.name as category_name 
                                            FROM products p 
                                            LEFT JOIN categories c ON p.category_id = c.id 
                                            ORDER BY p.created_at DESC
                                        ");
                                        $stmt->execute();
                                        $products = $stmt->fetchAll(PDO::FETCH_ASSOC);
                                        
                                        if (empty($products)) {
                                            echo '<tr><td colspan="8" class="text-center text-muted">No products found. <a href="?page=add_product">Add your first product</a></td></tr>';
                                        } else {
                                            foreach ($products as $product) {
                                                $status_badge = $product['status'] === 'active' ? 
                                                    '<span class="badge bg-success">Active</span>' : 
                                                    '<span class="badge bg-secondary">Inactive</span>';
                                                $imageCell = '<span class="text-muted">No image</span>';
                                                if (!empty($product['image_path'])) {
                                                    $imageCell = '<img src="../' . htmlspecialchars($product['image_path']) . '" alt="Product Image" class="img-thumbnail" style="max-width: 80px; height: auto;">';
                                                }
                                                echo "<tr>
                                                    <td>{$imageCell}</td>
                                                    <td>" . htmlspecialchars($product['name']) . "</td>
                                                    <td>" . ($product['category_name'] ?? 'No Category') . "</td>
                                                    <td>₱" . number_format($product['price'], 2) . "</td>
                                                    <td>{$product['stock_quantity']}</td>
                                                    <td>{$status_badge}</td>
                                                    <td>" . date('Y-m-d', strtotime($product['created_at'])) . "</td>
                                                    <td>
                                                        <a href='?page=edit_product&id={$product['id']}' class='btn btn-sm btn-outline-primary me-1'>Edit</a>
                                                        <form method='POST' class='d-inline' onsubmit='return confirm(\"Are you sure you want to delete this product?\")'>
                                                            <input type='hidden' name='action' value='delete_product'>
                                                            <input type='hidden' name='id' value='{$product['id']}'>
                                                            <button type='submit' class='btn btn-sm btn-outline-danger'>Delete</button>
                                                        </form>
                                                    </td>
                                                </tr>";
                                            }
                                        }
                                        ?>
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>

                <?php elseif ($page === 'customers'): ?>
                    <div class="content-header">
                        <div class="d-flex justify-content-between align-items-center">
                            <div>
                                <h2><i class="bi bi-people"></i> Customers</h2>
                                <p class="text-muted">View and manage all registered customers.</p>
                            </div>
                            <a href="?page=add_customer" class="btn btn-primary"><i class="bi bi-plus"></i> Add Customer</a>
                        </div>
                    </div>
                    <div class="card border-0 shadow-sm">
                        <div class="card-body">
                            <div class="table-responsive">
                                <table class="table table-hover mb-0">
                                    <thead class="table-light">
                                        <tr>
                                            <th>Customer Name</th>
                                            <th>Email</th>
                                            <th>Role</th>
                                            <th>Orders</th>
                                            <th>Joined</th>
                                            <th>Action</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php
                                        $stmt = $conn->prepare("
                                            SELECT u.*, COUNT(o.id) as order_count 
                                            FROM users u 
                                            LEFT JOIN orders o ON u.id = o.user_id 
                                            WHERE u.is_admin = 0 
                                            GROUP BY u.id 
                                            ORDER BY u.created_at DESC
                                        ");
                                        $stmt->execute();
                                        $customers = $stmt->fetchAll(PDO::FETCH_ASSOC);
                                        
                                        if (empty($customers)) {
                                            echo '<tr><td colspan="6" class="text-center text-muted">No customers found. <a href="?page=add_customer">Add your first customer</a></td></tr>';
                                        } else {
                                            foreach ($customers as $customer) {
                                                echo "<tr>
                                                    <td>" . htmlspecialchars($customer['first_name'] . ' ' . $customer['last_name']) . "</td>
                                                    <td>" . htmlspecialchars($customer['email']) . "</td>
                                                    <td><span class='badge bg-info'>{$customer['role']}</span></td>
                                                    <td>{$customer['order_count']}</td>
                                                    <td>" . date('Y-m-d', strtotime($customer['created_at'])) . "</td>
                                                    <td>
                                                        <a href='?page=edit_customer&id={$customer['id']}' class='btn btn-sm btn-outline-primary me-1'>Edit</a>
                                                        <form method='POST' class='d-inline' onsubmit='return confirm(\"Are you sure you want to delete this customer? This will also delete all their orders.\")'>
                                                            <input type='hidden' name='action' value='delete_customer'>
                                                            <input type='hidden' name='id' value='{$customer['id']}'>
                                                            <button type='submit' class='btn btn-sm btn-outline-danger'>Delete</button>
                                                        </form>
                                                    </td>
                                                </tr>";
                                            }
                                        }
                                        ?>
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>

                <?php elseif ($page === 'categories'): ?>
                    <div class="content-header">
                        <div class="d-flex justify-content-between align-items-center">
                            <div>
                                <h2><i class="bi bi-tag"></i> Categories</h2>
                                <p class="text-muted">Manage your product categories.</p>
                            </div>
                            <a href="?page=add_category" class="btn btn-primary"><i class="bi bi-plus"></i> Add Category</a>
                        </div>
                    </div>
                    <div class="card border-0 shadow-sm">
                        <div class="card-body">
                            <div class="table-responsive">
                                <table class="table table-hover mb-0">
                                    <thead class="table-light">
                                        <tr>
                                            <th>Category Name</th>
                                            <th>Description</th>
                                            <th>Products</th>
                                            <th>Created</th>
                                            <th>Action</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php
                                        $stmt = $conn->prepare("
                                            SELECT c.*, COUNT(p.id) as product_count 
                                            FROM categories c 
                                            LEFT JOIN products p ON c.id = p.category_id 
                                            GROUP BY c.id 
                                            ORDER BY c.created_at DESC
                                        ");
                                        $stmt->execute();
                                        $categories = $stmt->fetchAll(PDO::FETCH_ASSOC);
                                        
                                        if (empty($categories)) {
                                            echo '<tr><td colspan="5" class="text-center text-muted">No categories found. <a href="?page=add_category">Add your first category</a></td></tr>';
                                        } else {
                                            foreach ($categories as $category) {
                                                echo "<tr>
                                                    <td>" . htmlspecialchars($category['name']) . "</td>
                                                    <td>" . ($category['description'] ?? 'No description') . "</td>
                                                    <td>{$category['product_count']}</td>
                                                    <td>" . date('Y-m-d', strtotime($category['created_at'])) . "</td>
                                                    <td>
                                                        <a href='?page=edit_category&id={$category['id']}' class='btn btn-sm btn-outline-primary me-1'>Edit</a>
                                                        <form method='POST' class='d-inline' onsubmit='return confirm(\"Are you sure you want to delete this category? All associated products will be unassigned.\")'>
                                                            <input type='hidden' name='action' value='delete_category'>
                                                            <input type='hidden' name='id' value='{$category['id']}'>
                                                            <button type='submit' class='btn btn-sm btn-outline-danger'>Delete</button>
                                                        </form>
                                                    </td>
                                                </tr>";
                                            }
                                        }
                                        ?>
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>

                <?php elseif ($page === 'analytics'): ?>
                    <div class="content-header">
                        <h2><i class="bi bi-graph-up"></i> Analytics</h2>
                        <p class="text-muted">Review your store's sales and performance metrics.</p>
                    </div>
                    
                    <?php
                    // Get analytics data
                    $stmt = $conn->prepare("SELECT SUM(total_amount) as revenue_this_month FROM orders WHERE MONTH(order_date) = MONTH(CURRENT_DATE()) AND YEAR(order_date) = YEAR(CURRENT_DATE()) AND status = 'Completed'");
                    $stmt->execute();
                    $revenue_this_month = $stmt->fetch(PDO::FETCH_ASSOC)['revenue_this_month'] ?? 0;
                    
                    $stmt = $conn->prepare("SELECT COUNT(*) as orders_this_month FROM orders WHERE MONTH(order_date) = MONTH(CURRENT_DATE()) AND YEAR(order_date) = YEAR(CURRENT_DATE())");
                    $stmt->execute();
                    $orders_this_month = $stmt->fetch(PDO::FETCH_ASSOC)['orders_this_month'] ?? 0;
                    
                    $stmt = $conn->prepare("SELECT AVG(total_amount) as avg_order_value FROM orders WHERE status = 'Completed'");
                    $stmt->execute();
                    $avg_order_value = $stmt->fetch(PDO::FETCH_ASSOC)['avg_order_value'] ?? 0;
                    
                    $stmt = $conn->prepare("SELECT COUNT(*) as new_customers FROM users WHERE is_admin = 0 AND MONTH(created_at) = MONTH(CURRENT_DATE()) AND YEAR(created_at) = YEAR(CURRENT_DATE())");
                    $stmt->execute();
                    $new_customers = $stmt->fetch(PDO::FETCH_ASSOC)['new_customers'] ?? 0;
                    ?>
                    
                    <div class="row g-4">
                        <div class="col-12 col-md-6">
                            <div class="card border-0 shadow-sm">
                                <div class="card-header bg-light border-0">
                                    <h6 class="mb-0"><i class="bi bi-cash-stack"></i> Revenue This Month</h6>
                                </div>
                                <div class="card-body">
                                    <h3 style="color: #008080;">₱<?= number_format($revenue_this_month, 2) ?></h3>
                                </div>
                            </div>
                        </div>
                        <div class="col-12 col-md-6">
                            <div class="card border-0 shadow-sm">
                                <div class="card-header bg-light border-0">
                                    <h6 class="mb-0"><i class="bi bi-cart-check"></i> Orders This Month</h6>
                                </div>
                                <div class="card-body">
                                    <h3 style="color: #008080;"><?= $orders_this_month ?></h3>
                                </div>
                            </div>
                        </div>
                        <div class="col-12 col-md-6">
                            <div class="card border-0 shadow-sm">
                                <div class="card-header bg-light border-0">
                                    <h6 class="mb-0"><i class="bi bi-receipt"></i> Average Order Value</h6>
                                </div>
                                <div class="card-body">
                                    <h3 style="color: #008080;">₱<?= number_format($avg_order_value, 2) ?></h3>
                                </div>
                            </div>
                        </div>
                        <div class="col-12 col-md-6">
                            <div class="card border-0 shadow-sm">
                                <div class="card-header bg-light border-0">
                                    <h6 class="mb-0"><i class="bi bi-person-plus"></i> New Customers This Month</h6>
                                </div>
                                <div class="card-body">
                                    <h3 style="color: #008080;"><?= $new_customers ?></h3>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Top Products Chart -->
                    <div class="card border-0 shadow-sm mt-4">
                        <div class="card-header bg-light border-0">
                            <h6 class="mb-0"><i class="bi bi-trophy"></i> Top Selling Products</h6>
                        </div>
                        <div class="card-body">
                            <canvas id="topProductsChart" height="300"></canvas>
                        </div>
                    </div>

                <?php elseif ($page === 'add_product'): ?>
                    <div class="content-header">
                        <h2><i class="bi bi-plus-circle"></i> Add Product</h2>
                        <p class="text-muted">Create a new product for your catalog.</p>
                    </div>
                    <div class="row">
                        <div class="col-lg-8">
                            <div class="card border-0 shadow-sm">
                                <div class="card-body">
                                    <form method="POST" enctype="multipart/form-data">
                                        <input type="hidden" name="action" value="add_product">
                                        
                                        <div class="mb-3">
                                            <label class="form-label">Product Name *</label>
                                            <input type="text" class="form-control" name="name" required>
                                        </div>
                                        
                                        <div class="mb-3">
                                            <label class="form-label">Product Image</label>
                                            <input type="file" class="form-control" name="image" accept="image/png,image/jpeg,image/gif">
                                            <div class="form-text">Upload a JPG, PNG, or GIF file.</div>
                                        </div>

                                        <div class="mb-3">
                                            <label class="form-label">Category</label>
                                            <select class="form-select" name="category_id">
                                                <option value="">Select Category</option>
                                                <?php
                                                $stmt = $conn->prepare("SELECT * FROM categories ORDER BY name");
                                                $stmt->execute();
                                                $categories = $stmt->fetchAll(PDO::FETCH_ASSOC);
                                                foreach ($categories as $category) {
                                                    echo "<option value=\"{$category['id']}\">" . htmlspecialchars($category['name']) . "</option>";
                                                }
                                                ?>
                                            </select>
                                        </div>
                                        
                                        <div class="mb-3">
                                            <label class="form-label">Description</label>
                                            <textarea class="form-control" name="description" rows="4"></textarea>
                                        </div>
                                        
                                        <div class="row">
                                            <div class="col-md-6">
                                                <div class="mb-3">
                                                    <label class="form-label">Price *</label>
                                                    <div class="input-group">
                                                        <span class="input-group-text">₱</span>
                                                        <input type="number" class="form-control" name="price" step="0.01" required>
                                                    </div>
                                                </div>
                                            </div>
                                            <div class="col-md-6">
                                                <div class="mb-3">
                                                    <label class="form-label">Stock Quantity *</label>
                                                    <input type="number" class="form-control" name="stock_quantity" value="0" required>
                                                </div>
                                            </div>
                                        </div>
                                        
                                        <div class="mb-3">
                                            <label class="form-label">Status</label>
                                            <select class="form-select" name="status">
                                                <option value="active">Active</option>
                                                <option value="inactive">Inactive</option>
                                            </select>
                                        </div>
                                        
                                        <div class="d-flex gap-2">
                                            <button type="submit" class="btn btn-primary">Add Product</button>
                                            <a href="?page=products" class="btn btn-secondary">Cancel</a>
                                        </div>
                                    </form>
                                </div>
                            </div>
                        </div>
                    </div>

                <?php elseif ($page === 'edit_product' && isset($_GET['id'])): ?>
                    <?php
                    $stmt = $conn->prepare("SELECT * FROM products WHERE id = ?");
                    $stmt->execute([$_GET['id']]);
                    $product = $stmt->fetch(PDO::FETCH_ASSOC);
                    if (!$product) {
                        header('Location: ?page=products');
                        exit();
                    }
                    ?>
                    <div class="content-header">
                        <h2><i class="bi bi-pencil-square"></i> Edit Product</h2>
                        <p class="text-muted">Update product information.</p>
                    </div>
                    <div class="row">
                        <div class="col-lg-8">
                            <div class="card border-0 shadow-sm">
                                <div class="card-body">
                                    <form method="POST" enctype="multipart/form-data">
                                        <input type="hidden" name="action" value="edit_product">
                                        <input type="hidden" name="id" value="<?= $product['id'] ?>">
                                        <input type="hidden" name="existing_image" value="<?= htmlspecialchars($product['image_path']) ?>">
                                        
                                        <div class="mb-3">
                                            <label class="form-label">Product Name *</label>
                                            <input type="text" class="form-control" name="name" value="<?= htmlspecialchars($product['name']) ?>" required>
                                        </div>

                                        <div class="mb-3">
                                            <label class="form-label">Product Image</label>
                                            <?php if (!empty($product['image_path'])): ?>
                                                <div class="mb-2">
                                                    <img src="../<?= htmlspecialchars($product['image_path']) ?>" alt="Product Image" class="img-thumbnail" style="max-width: 180px;">
                                                </div>
                                            <?php endif; ?>
                                            <input type="file" class="form-control" name="image" accept="image/png,image/jpeg,image/gif">
                                            <div class="form-text">Leave blank to keep the current image.</div>
                                        </div>
                                        
                                        <div class="mb-3">
                                            <label class="form-label">Category</label>
                                            <select class="form-select" name="category_id">
                                                <option value="">Select Category</option>
                                                <?php
                                                $stmt = $conn->prepare("SELECT * FROM categories ORDER BY name");
                                                $stmt->execute();
                                                $categories = $stmt->fetchAll(PDO::FETCH_ASSOC);
                                                foreach ($categories as $category) {
                                                    $selected = ($product['category_id'] == $category['id']) ? 'selected' : '';
                                                    echo "<option value=\"{$category['id']}\" {$selected}>" . htmlspecialchars($category['name']) . "</option>";
                                                }
                                                ?>
                                            </select>
                                        </div>
                                        
                                        <div class="mb-3">
                                            <label class="form-label">Description</label>
                                            <textarea class="form-control" name="description" rows="4"><?= htmlspecialchars($product['description'] ?? '') ?></textarea>
                                        </div>
                                        
                                        <div class="row">
                                            <div class="col-md-6">
                                                <div class="mb-3">
                                                    <label class="form-label">Price *</label>
                                                    <div class="input-group">
                                                        <span class="input-group-text">₱</span>
                                                        <input type="number" class="form-control" name="price" step="0.01" value="<?= $product['price'] ?>" required>
                                                    </div>
                                                </div>
                                            </div>
                                            <div class="col-md-6">
                                                <div class="mb-3">
                                                    <label class="form-label">Stock Quantity *</label>
                                                    <input type="number" class="form-control" name="stock_quantity" value="<?= $product['stock_quantity'] ?>" required>
                                                </div>
                                            </div>
                                        </div>
                                        
                                        <div class="mb-3">
                                            <label class="form-label">Status</label>
                                            <select class="form-select" name="status">
                                                <option value="active" <?= $product['status'] == 'active' ? 'selected' : '' ?>>Active</option>
                                                <option value="inactive" <?= $product['status'] == 'inactive' ? 'selected' : '' ?>>Inactive</option>
                                            </select>
                                        </div>
                                        
                                        <div class="d-flex gap-2">
                                            <button type="submit" class="btn btn-primary">Update Product</button>
                                            <a href="?page=products" class="btn btn-secondary">Cancel</a>
                                        </div>
                                    </form>
                                </div>
                            </div>
                        </div>
                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                <?php elseif ($page === 'add_category'): ?>
                    <div class="content-header">
                        <h2><i class="bi bi-plus-circle"></i> Add Category</h2>
                        <p class="text-muted">Create a new product category.</p>
                    </div>
                    <div class="row">
                        <div class="col-lg-8">
                            <div class="card border-0 shadow-sm">
                                <div class="card-body">
                                    <form method="POST">
                                        <input type="hidden" name="action" value="add_category">
                                        
                                        <div class="mb-3">
                                            <label class="form-label">Category Name *</label>
                                            <input type="text" class="form-control" name="name" required>
                                        </div>
                                        
                                        <div class="mb-3">
                                            <label class="form-label">Description</label>
                                            <textarea class="form-control" name="description" rows="3"></textarea>
                                        </div>
                                        
                                        <div class="d-flex gap-2">
                                            <button type="submit" class="btn btn-primary">Add Category</button>
                                            <a href="?page=categories" class="btn btn-secondary">Cancel</a>
                                        </div>
                                    </form>
                                </div>
                            </div>
                        </div>
                    </div>

                <?php elseif ($page === 'edit_category' && isset($_GET['id'])): ?>
                    <?php
                    $stmt = $conn->prepare("SELECT * FROM categories WHERE id = ?");
                    $stmt->execute([$_GET['id']]);
                    $category = $stmt->fetch(PDO::FETCH_ASSOC);
                    if (!$category) {
                        header('Location: ?page=categories');
                        exit();
                    }
                    ?>
                    <div class="content-header">
                        <h2><i class="bi bi-pencil-square"></i> Edit Category</h2>
                        <p class="text-muted">Update category information.</p>
                    </div>
                    <div class="row">
                        <div class="col-lg-8">
                            <div class="card border-0 shadow-sm">
                                <div class="card-body">
                                    <form method="POST">
                                        <input type="hidden" name="action" value="edit_category">
                                        <input type="hidden" name="id" value="<?= $category['id'] ?>">
                                        
                                        <div class="mb-3">
                                            <label class="form-label">Category Name *</label>
                                            <input type="text" class="form-control" name="name" value="<?= htmlspecialchars($category['name']) ?>" required>
                                        </div>
                                        
                                        <div class="mb-3">
                                            <label class="form-label">Description</label>
                                            <textarea class="form-control" name="description" rows="3"><?= htmlspecialchars($category['description'] ?? '') ?></textarea>
                                        </div>
                                        
                                        <div class="d-flex gap-2">
                                            <button type="submit" class="btn btn-primary">Update Category</button>
                                            <a href="?page=categories" class="btn btn-secondary">Cancel</a>
                                        </div>
                                    </form>
                                </div>
                            </div>
                        </div>
                    </div>

                <?php elseif ($page === 'add_customer'): ?>
                    <div class="content-header">
                        <h2><i class="bi bi-person-plus"></i> Add Customer</h2>
                        <p class="text-muted">Create a new customer account.</p>
                    </div>
                    <div class="row">
                        <div class="col-lg-8">
                            <div class="card border-0 shadow-sm">
                                <div class="card-body">
                                    <form method="POST">
                                        <input type="hidden" name="action" value="add_customer">
                                        
                                        <div class="row">
                                            <div class="col-md-6">
                                                <div class="mb-3">
                                                    <label class="form-label">First Name *</label>
                                                    <input type="text" class="form-control" name="first_name" required>
                                                </div>
                                            </div>
                                            <div class="col-md-6">
                                                <div class="mb-3">
                                                    <label class="form-label">Last Name *</label>
                                                    <input type="text" class="form-control" name="last_name" required>
                                                </div>
                                            </div>
                                        </div>
                                        
                                        <div class="mb-3">
                                            <label class="form-label">Email *</label>
                                            <input type="email" class="form-control" name="email" required>
                                        </div>
                                        
                                        <div class="mb-3">
                                            <label class="form-label">Password *</label>
                                            <input type="password" class="form-control" name="password" required>
                                        </div>
                                        
                                        <div class="d-flex gap-2">
                                            <button type="submit" class="btn btn-primary">Add Customer</button>
                                            <a href="?page=customers" class="btn btn-secondary">Cancel</a>
                                        </div>
                                    </form>
                                </div>
                            </div>
                        </div>
                    </div>

                <?php elseif ($page === 'edit_customer' && isset($_GET['id'])): ?>
                    <?php
                    $stmt = $conn->prepare("SELECT * FROM users WHERE id = ? AND is_admin = 0");
                    $stmt->execute([$_GET['id']]);
                    $customer = $stmt->fetch(PDO::FETCH_ASSOC);
                    if (!$customer) {
                        header('Location: ?page=customers');
                        exit();
                    }
                    ?>
                    <div class="content-header">
                        <h2><i class="bi bi-pencil-square"></i> Edit Customer</h2>
                        <p class="text-muted">Update customer information.</p>
                    </div>
                    <div class="row">
                        <div class="col-lg-8">
                            <div class="card border-0 shadow-sm">
                                <div class="card-body">
                                    <form method="POST">
                                        <input type="hidden" name="action" value="edit_customer">
                                        <input type="hidden" name="id" value="<?= $customer['id'] ?>">
                                        
                                        <div class="row">
                                            <div class="col-md-6">
                                                <div class="mb-3">
                                                    <label class="form-label">First Name *</label>
                                                    <input type="text" class="form-control" name="first_name" value="<?= htmlspecialchars($customer['first_name']) ?>" required>
                                                </div>
                                            </div>
                                            <div class="col-md-6">
                                                <div class="mb-3">
                                                    <label class="form-label">Last Name *</label>
                                                    <input type="text" class="form-control" name="last_name" value="<?= htmlspecialchars($customer['last_name']) ?>" required>
                                                </div>
                                            </div>
                                        </div>
                                        
                                        <div class="mb-3">
                                            <label class="form-label">Email *</label>
                                            <input type="email" class="form-control" name="email" value="<?= htmlspecialchars($customer['email']) ?>" required>
                                        </div>
                                        
                                        <div class="d-flex gap-2">
                                            <button type="submit" class="btn btn-primary">Update Customer</button>
                                            <a href="?page=customers" class="btn btn-secondary">Cancel</a>
                                        </div>
                                    </form>
                                </div>
                            </div>
                        </div>
                    </div>

                <?php endif; ?>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.0.2/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        <?php if ($page === 'dashboard'): ?>
        // Monthly Sales Chart
        const ctx = document.getElementById('salesChart').getContext('2d');
        new Chart(ctx, {
            type: 'line',
            data: {
                labels: [
                    '<?= date('M Y', strtotime('-5 months')) ?>',
                    '<?= date('M Y', strtotime('-4 months')) ?>',
                    '<?= date('M Y', strtotime('-3 months')) ?>',
                    '<?= date('M Y', strtotime('-2 months')) ?>',
                    '<?= date('M Y', strtotime('-1 months')) ?>',
                    '<?= date('M Y') ?>'
                ],
                datasets: [{
                    label: 'Monthly Revenue (₱)',
                    data: [<?= implode(',', $monthly_sales) ?>],
                    borderColor: '#008080',
                    backgroundColor: 'rgba(0, 128, 128, 0.1)',
                    tension: 0.4,
                    fill: true
                }]
            },
            options: {
                responsive: true,
                plugins: {
                    legend: { display: true, position: 'top' }
                },
                scales: {
                    y: {
                        beginAtZero: true,
                        ticks: {
                            callback: function(value) {
                                return '₱' + value.toLocaleString();
                            }
                        }
                    }
                }
            }
        });
        <?php endif; ?>
        
        <?php if ($page === 'analytics'): ?>
        // Top Products Chart
        <?php
        $stmt = $conn->prepare("
            SELECT oi.product_name, SUM(oi.quantity) as total_sold 
            FROM order_items oi 
            GROUP BY oi.product_name 
            ORDER BY total_sold DESC 
            LIMIT 5
        ");
        $stmt->execute();
        $top_products = $stmt->fetchAll(PDO::FETCH_ASSOC);
        $product_names = array_column($top_products, 'product_name');
        $product_sales = array_column($top_products, 'total_sold');
        ?>
        const productsCtx = document.getElementById('topProductsChart').getContext('2d');
        new Chart(productsCtx, {
            type: 'bar',
            data: {
                labels: <?= json_encode($product_names) ?>,
                datasets: [{
                    label: 'Units Sold',
                    data: <?= json_encode($product_sales) ?>,
                    backgroundColor: '#008080',
                    borderRadius: 5
                }]
            },
            options: {
                responsive: true,
                plugins: {
                    legend: { display: true, position: 'top' }
                },
                scales: {
                    y: {
                        beginAtZero: true,
                        title: { display: true, text: 'Units Sold' }
                    }
                }
            }
        });
        <?php endif; ?>
    </script>
</body>
</html>