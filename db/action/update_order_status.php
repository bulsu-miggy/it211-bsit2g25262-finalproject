<?php
session_start();
require_once '../connection.php'; 

header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['order_id']) && isset($_POST['status'])) {
    $order_id = (int)$_POST['order_id'];
    $new_status = trim($_POST['status']);
    
    try {
        $stmt = $conn->prepare("UPDATE orders SET status = ? WHERE id = ?");
        $success = $stmt->execute([$new_status, $order_id]);
        
        if ($success) {
            echo json_encode(['success' => true, 'status' => $new_status]);
        } else {
            echo json_encode(['success' => false, 'error' => 'Failed to update database']);
        }
    } catch (PDOException $e) {
        echo json_encode(['success' => false, 'error' => $e->getMessage()]);
    }
} else {
    echo json_encode(['success' => false, 'error' => 'Invalid request']);
}
exit(); 
?>