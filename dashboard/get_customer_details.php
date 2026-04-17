<?php
session_start();
if (!isset($_SESSION["username"])) {
    http_response_code(403);
    echo json_encode(['success' => false, 'error' => 'Unauthorized']);
    exit();
}

include '../db/connection.php';

$email = $_GET['email'] ?? '';
if (empty($email)) {
    echo json_encode(['success' => false, 'error' => 'No email']);
    exit();
}

try {
    // Customer summary
    $stmt = $conn->prepare("
        SELECT 
            customer_name, 
            email, 
            MAX(contact_number) as contact_number,
            MAX(address) as address,
            COUNT(*) as orders_count,
            SUM(total_amount) as total_spent
        FROM orders 
        WHERE email = ?
        GROUP BY email
    ");
    $stmt->execute([$email]);
    $customer = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if (!$customer) {
        echo json_encode(['success' => false, 'error' => 'Customer not found']);
        exit();
    }

    // Orders history
    $stmt = $conn->prepare("
        SELECT id, order_date, total_amount, status
        FROM orders 
        WHERE email = ? 
        ORDER BY order_date DESC
    ");
    $stmt->execute([$email]);
    $orders = $stmt->fetchAll(PDO::FETCH_ASSOC);

    echo json_encode([
        'success' => true,
        'customer' => $customer,
        'orders' => $orders
    ]);
} catch (PDOException $e) {
    echo json_encode(['success' => false, 'error' => $e->getMessage()]);
}
?>
