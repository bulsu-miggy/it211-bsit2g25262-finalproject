<?php
/**
 * admins_api.php
 * Handles CRUD operations for administrative accounts.
 */
header('Content-Type: application/json');
require_once __DIR__ . '/../connection.php';

// Parse JSON input (sent by fetch calls in modules)
$json = file_get_contents('php://input');
$data = json_decode($json, true);

// Determine action from GET (for list) or JSON body (for CRUD)
$action = $_GET['action'] ?? $data['action'] ?? $_POST['action'] ?? '';

if (!$action) {
    echo json_encode(['status' => 'error', 'message' => 'Invalid action.']);
    exit;
}

try {
    switch ($action) {
        case 'list':
            // Fetch all admins and format last_login for the JS frontend
            $stmt = $conn->query("SELECT id, full_name as name, email, role, IFNULL(last_login, 'Never') as lastLogin FROM admins ORDER BY id ASC");
            $admins = $stmt->fetchAll(PDO::FETCH_ASSOC);
            echo json_encode(['status' => 'success', 'admins' => $admins]);
            break;

        case 'create':
            $fullName = $data['full_name'] ?? '';
            $email = $data['email'] ?? '';
            $password = $data['password'] ?? '';
            $role = $data['role'] ?? 'Admin';

            if (!$fullName || !$email || !$password) {
                echo json_encode(['status' => 'error', 'message' => 'Name, email, and password are required.']);
                exit;
            }

            $hash = password_hash($password, PASSWORD_DEFAULT);
            $stmt = $conn->prepare("INSERT INTO admins (full_name, email, password_hash, role) VALUES (?, ?, ?, ?)");
            $stmt->execute([$fullName, $email, $hash, $role]);
            echo json_encode(['status' => 'success']);
            break;

        case 'update':
            $id = $data['id'] ?? null;
            $fullName = $data['full_name'] ?? '';
            $email = $data['email'] ?? '';
            $role = $data['role'] ?? 'Admin';
            $password = $data['password'] ?? null;

            if (!$id || !$fullName || !$email) {
                echo json_encode(['status' => 'error', 'message' => 'ID, name, and email are required.']);
                exit;
            }

            if ($password) {
                $hash = password_hash($password, PASSWORD_DEFAULT);
                $stmt = $conn->prepare("UPDATE admins SET full_name = ?, email = ?, role = ?, password_hash = ? WHERE id = ?");
                $stmt->execute([$fullName, $email, $role, $hash, $id]);
            } else {
                $stmt = $conn->prepare("UPDATE admins SET full_name = ?, email = ?, role = ? WHERE id = ?");
                $stmt->execute([$fullName, $email, $role, $id]);
            }
            echo json_encode(['status' => 'success']);
            break;

        case 'delete':
            $id = $data['id'] ?? null;
            if (!$id) {
                echo json_encode(['status' => 'error', 'message' => 'ID is required for deletion.']);
                exit;
            }
            // Prevent deleting the master admin (ID 1)
            if ($id == 1) {
                echo json_encode(['status' => 'error', 'message' => 'The master admin account is protected.']);
                exit;
            }
            $stmt = $conn->prepare("DELETE FROM admins WHERE id = ?");
            $stmt->execute([$id]);
            echo json_encode(['status' => 'success']);
            break;

        default:
            echo json_encode(['status' => 'error', 'message' => 'Invalid action: ' . $action]);
            break;
    }
} catch (PDOException $e) {
    echo json_encode(['status' => 'error', 'message' => 'Database error: ' . $e->getMessage()]);
}
?>