<?php
session_start();
if (!isset($_SESSION['logged_in']) || !$_SESSION['logged_in']) {
    header('Location: loginpage.php');
    exit();
}

require_once '../db/conn.php';
$db = new Database();
$conn = $db->getConnection();

$user_id = $_SESSION['user_id'];

// Get user's orders
$stmt = $conn->prepare("SELECT * FROM orders WHERE user_id = ? ORDER BY created_at DESC");
$stmt->execute([$user_id]);
$orders = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>My Orders - Lasa Filipina</title>
    <link rel="stylesheet" href="../css/styles.css">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
    <style>
        body {
            background-image: url('../Imges/bg.jpg');
            background-size: cover;
            color: #2c2418;
            line-height: 1.5;
            position: relative;
        }
        body::before {
            content: '';
            position: fixed;
            top: 0;
            left: 0;
            right: 0;
            bottom: 0;
            background: linear-gradient(107deg, rgba(245, 229, 214, 0.85) 80%, rgba(255, 245, 235, 0.9) 100%);
            z-index: -1;
        }
        .orders-container {
            width: 100%;
            margin: 2rem 0;
            padding: 0 1rem;
        }
        .orders-header {
            text-align: center;
            margin-bottom: 2rem;
        }
        .orders-header h1 {
            font-size: 2.5rem;
            font-weight: 700;
            color: #2f241b;
            margin-bottom: 0.5rem;
        }
        .orders-header p {
            color: #4f3724;
            font-size: 1.1rem;
        }
        .card {
            border: none;
            border-radius: 15px;
            box-shadow: 0 8px 20px rgba(0, 0, 0, 0.08);
            transition: transform 0.3s ease, box-shadow 0.3s ease;
            background: rgba(255, 248, 240, 0.96);
            backdrop-filter: blur(2px);
        }
        .card:hover {
            transform: translateY(-5px);
            box-shadow: 0 12px 28px rgba(0, 0, 0, 0.12);
        }
        .card-header {
            background: linear-gradient(145deg, #bc6f3b, #a55828);
            color: white;
            border-radius: 15px 15px 0 0 !important;
            border: none;
            padding: 1rem;
        }
        .card-header h5 {
            margin: 0;
            font-weight: 600;
        }
        .card-body {
            padding: 1.5rem;
        }
        .card-body p {
            margin-bottom: 0.5rem;
            color: #3b2c21;
        }
        .btn {
            border-radius: 25px;
            padding: 0.5rem 1.5rem;
            font-weight: 600;
            transition: all 0.3s ease;
            border: none;
        }
        .btn-primary {
            background: linear-gradient(145deg, #bc6f3b, #a55828);
        }
        .btn-primary:hover {
            background: linear-gradient(145deg, #a55828, #8d451f);
            transform: translateY(-2px);
            box-shadow: 0 6px 15px rgba(188, 111, 59, 0.3);
        }
        .btn-secondary {
            background: #6c757d;
        }
        .btn-secondary:hover {
            background: #5a6268;
            transform: translateY(-2px);
            box-shadow: 0 6px 15px rgba(108, 117, 125, 0.3);
        }
        .modal-content {
            border-radius: 15px;
            border: none;
            box-shadow: 0 12px 28px rgba(0, 0, 0, 0.15);
        }
        .modal-header {
            background: linear-gradient(145deg, #bc6f3b, #a55828);
            color: white;
            border-radius: 15px 15px 0 0;
            border: none;
        }
        .modal-body {
            padding: 1.5rem;
        }
        .no-orders {
            text-align: center;
            padding: 3rem;
            background: rgba(255, 248, 240, 0.96);
            border-radius: 15px;
            box-shadow: 0 8px 20px rgba(0, 0, 0, 0.08);
        }
        .no-orders i {
            font-size: 4rem;
            color: #bc6f3b;
            margin-bottom: 1rem;
        }
    </style>
</head>
<body>
    <?php include 'navbar.php'; ?>

    <div class="orders-container">
        <div class="orders-header">
            <h1>My Orders</h1>
            <p>Track and manage your order history</p>
        </div>
        <?php if (empty($orders)): ?>
            <div class="no-orders">
                <i class="bi bi-receipt"></i>
                <h3>No Orders Yet</h3>
                <p>You haven't placed any orders yet. Start exploring our delicious menu!</p>
                <a href="dishes.php" class="btn btn-primary">Browse Menu</a>
            </div>
        <?php else: ?>
            <div class="row">
                <?php foreach ($orders as $order): ?>
                    <div class="col-lg-6 mb-4">
                        <div class="card h-100">
                            <div class="card-header">
                                <h5><i class="bi bi-receipt me-2"></i>Order #<?php echo htmlspecialchars($order['order_number']); ?></h5>
                                <small><?php echo date('M j, Y g:i A', strtotime($order['created_at'])); ?></small>
                            </div>
                            <div class="card-body d-flex flex-column">
                                <div class="mb-3">
                                    <p class="mb-1"><strong><i class="bi bi-info-circle me-1"></i>Status:</strong> 
                                        <span class="badge bg-<?php echo $order['status'] == 'pending' ? 'warning' : ($order['status'] == 'completed' ? 'success' : 'secondary'); ?>">
                                            <?php echo htmlspecialchars(ucfirst($order['status'])); ?>
                                        </span>
                                    </p>
                                    <p class="mb-1"><strong><i class="bi bi-cash me-1"></i>Total:</strong> $<?php echo number_format($order['total'], 2); ?></p>
                                    <p class="mb-1"><strong><i class="bi bi-credit-card me-1"></i>Payment:</strong> <?php echo htmlspecialchars($order['payment_mode']); ?></p>
                                    <p class="mb-0"><strong><i class="bi bi-geo-alt me-1"></i>Delivery:</strong> <?php echo htmlspecialchars($order['location']); ?></p>
                                </div>
                                <div class="mt-auto">
                                    <button class="btn btn-primary me-2" onclick="viewOrderDetails(<?php echo $order['id']; ?>)">
                                        <i class="bi bi-eye me-1"></i>View Details
                                    </button>
                                    <button class="btn btn-secondary" onclick="trackOrder('<?php echo htmlspecialchars($order['order_number']); ?>')">
                                        <i class="bi bi-truck me-1"></i>Track Order
                                    </button>
                                </div>
                            </div>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>
    </div>

    <!-- Modal for order details -->
    <div class="modal fade" id="orderDetailsModal" tabindex="-1">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title"><i class="bi bi-receipt me-2"></i>Order Details</h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body" id="orderDetailsContent">
                    <!-- Content will be loaded here -->
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        function viewOrderDetails(orderId) {
            fetch('get_order_details.php?order_id=' + orderId)
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        let content = '<div class="list-group list-group-flush">';
                        data.items.forEach(item => {
                            content += `
                                <div class="list-group-item d-flex justify-content-between align-items-center">
                                    <div>
                                        <h6 class="mb-1">${item.product_name}</h6>
                                        <small class="text-muted">Quantity: ${item.quantity} × $${item.product_price}</small>
                                    </div>
                                    <strong>$${item.subtotal}</strong>
                                </div>
                            `;
                        });
                        content += '</div>';
                        document.getElementById('orderDetailsContent').innerHTML = content;
                        new bootstrap.Modal(document.getElementById('orderDetailsModal')).show();
                    } else {
                        alert('Failed to load order details');
                    }
                });
        }

        function trackOrder(orderNumber) {
            // Placeholder tracking - in a real app, this would fetch actual tracking data
            const statuses = ['Order Placed', 'Preparing', 'Out for Delivery', 'Delivered'];
            const randomStatus = statuses[Math.floor(Math.random() * statuses.length)];
            alert(`Tracking for order ${orderNumber}: Currently ${randomStatus}`);
        }
    </script>
</body>
</html>