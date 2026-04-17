<?php
session_start();
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET, POST');
header('Access-Control-Allow-Headers: Content-Type');

require_once '../../db/connection.php';

try {
    $action = $_GET['action'] ?? '';

    if ($action === 'list') {
        // Query customers from login with aggregates
        $stmt = $conn->prepare("
            SELECT 
                l.user_id as id,
                l.full_name as name,
                l.email,
                COALESCE(COUNT(o.order_id), 0) as orders,
                0 as spent,
                DATE_FORMAT(l.created_at, '%b %Y') as joined,
                '' as address
            FROM login l
            LEFT JOIN orders o ON o.user_id = l.user_id
            GROUP BY l.user_id, l.full_name, l.email, l.created_at
            ORDER BY l.created_at DESC
        ");
        $stmt->execute();
        $customers = $stmt->fetchAll(PDO::FETCH_ASSOC);

        echo json_encode([
            'status' => 'success',
            'customers' => array_map(function($c) {
                $c['spent'] = floatval($c['spent']);
                $c['orders'] = intval($c['orders']);
                return $c;
            }, $customers)
        ]);
    } else {
        echo json_encode(['status' => 'error', 'message' => 'Invalid action']);
    }
} catch (Exception $e) {
    echo json_encode(['status' => 'error', 'message' => $e->getMessage()]);
}
?>
