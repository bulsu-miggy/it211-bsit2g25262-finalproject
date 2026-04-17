<?php
session_start();
error_reporting(0);
ini_set('display_errors', '0');
header('Content-Type: application/json; charset=utf-8');
ob_start();

include __DIR__ . '/../connection.php';

function sendJson($payload) {
    if (ob_get_length()) {
        ob_clean();
    }
    header('Content-Type: application/json; charset=utf-8');
    echo json_encode($payload);
    exit();
}

if (!isset($_SESSION['admin_logged_in']) || $_SESSION['admin_logged_in'] !== true) {
    sendJson(['status' => 'error', 'message' => 'Unauthorized access.']);
}

$action = $_SERVER['REQUEST_METHOD'] === 'GET'
    ? ($_GET['action'] ?? 'list')
    : ($_POST['action'] ?? 'list');

if ($_SERVER['REQUEST_METHOD'] === 'GET') {
    $details = isset($_GET['details']) && $_GET['details'] === 'true';
    
    if ($details) {
        // Full details with items_details
        try {
            $stmt = $conn->prepare("
                SELECT 
                    o.order_id,
                    o.order_number,
                    o.total_amount,
                    o.status,
                    o.created_at,
                    l.full_name as customer_name,
                    l.email as customer_email,
                    oi.quantity as qty,
                    oi.price as item_price,
                    c.name as product_name,
                    c.category
                FROM orders o
                LEFT JOIN login l ON o.user_id = l.user_id
                LEFT JOIN order_items oi ON o.order_id = oi.order_id
                LEFT JOIN candles c ON oi.product_id = c.product_id
                ORDER BY o.created_at DESC, oi.order_item_id
            ");
            $stmt->execute();
            $rows = $stmt->fetchAll(PDO::FETCH_ASSOC);

            // Group into orders with items_details
            $orders = [];
            foreach ($rows as $row) {
                $orderId = $row['order_id'];
                if (!isset($orders[$orderId])) {
                    $orders[$orderId] = [
                        'id' => $row['order_id'],
                        'order_number' => $row['order_number'],
                        'customer' => $row['customer_name'] ?: 'Unknown Customer',
                        'items' => 0, // Will count
                        'total' => (float)$row['total_amount'],
                        'status' => $row['status'],
                        'date' => date('M j, Y', strtotime($row['created_at'])),
                        'items_details' => []
                    ];
                }
                if ($row['product_name']) {
                    $orders[$orderId]['items_details'][] = [
                        'name' => $row['product_name'],
                        'price' => (float)$row['item_price'],
                        'qty' => (int)$row['qty']
                    ];
                    $orders[$orderId]['items']++;
                }
            }
            $formattedOrders = array_values($orders);
            sendJson(['status' => 'success', 'orders' => $formattedOrders]);
        } catch (PDOException $e) {
            sendJson(['status' => 'error', 'message' => 'Database error: ' . $e->getMessage()]);
        }
    } else {
        // Original summary
        try {
            $stmt = $conn->prepare("
                SELECT 
                    o.order_id,
                    o.order_number,
                    o.total_amount,
                    o.status,
                    o.created_at,
                    l.full_name as customer_name,
                    l.email as customer_email,
                    COUNT(oi.order_item_id) as item_count
                FROM orders o
                LEFT JOIN login l ON o.user_id = l.user_id
                LEFT JOIN order_items oi ON o.order_id = oi.order_id
                GROUP BY o.order_id, o.order_number, o.total_amount, o.status, o.created_at, l.full_name, l.email
                ORDER BY o.created_at DESC
            ");
            $stmt->execute();
            $orders = $stmt->fetchAll(PDO::FETCH_ASSOC);
            
            $formattedOrders = array_map(function($order) {
                return [
                    'id' => $order['order_id'],
                    'order_number' => $order['order_number'],
                    'customer' => $order['customer_name'] ?: 'Unknown Customer',
                    'customer_email' => $order['customer_email'] ?: '',
                    'items' => (int)$order['item_count'],
                    'total' => (float)$order['total_amount'],
                    'status' => $order['status'],
                    'date' => date('M j, Y', strtotime($order['created_at']))
                ];
            }, $orders);
            
            sendJson(['status' => 'success', 'orders' => $formattedOrders]);
        } catch (PDOException $e) {
            sendJson(['status' => 'error', 'message' => 'Database error: ' . $e->getMessage()]);
        }
    }
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    sendJson(['status' => 'error', 'message' => 'Invalid request method.']);
}

switch ($action) {
    case 'update_status':
        $order_id = $_POST['order_id'] ?? null;
        $status = $_POST['status'] ?? null;
        
        if (!$order_id || !$status) {
            sendJson(['status' => 'error', 'message' => 'Order ID and status are required.']);
        }
        

$validStatuses = ['Pending', 'Processing', 'Completed', 'Cancelled'];

        if (!in_array($status, $validStatuses)) {
            sendJson(['status' => 'error', 'message' => 'Invalid status.']);
        }
        
        try {
            $stmt = $conn->prepare("UPDATE orders SET status = ? WHERE order_id = ?");
            $stmt->execute([$status, $order_id]);
            
            if ($stmt->rowCount() === 0) {
                sendJson(['status' => 'error', 'message' => 'Order not found or status unchanged.']);
            }
            
            sendJson(['status' => 'success', 'message' => 'Order status updated successfully.']);
        } catch (PDOException $e) {
            sendJson(['status' => 'error', 'message' => 'Database error: ' . $e->getMessage()]);
        }
        break;

    default:
        sendJson(['status' => 'error', 'message' => 'Unknown action.']);
}
?>