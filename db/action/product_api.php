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

function processUploadedImage($file) {
    if (!is_array($file) || $file['error'] !== UPLOAD_ERR_OK) {
        return null;
    }

    $allowedTypes = [
        'image/jpeg' => 'jpg',
        'image/png' => 'png',
        'image/webp' => 'webp',
        'image/gif' => 'gif',
    ];

    if (!isset($allowedTypes[$file['type']])) {
        sendJson(['status' => 'error', 'message' => 'Invalid image type. Only PNG, JPG, GIF, and WEBP are allowed.']);
    }

    if ($file['size'] > 5 * 1024 * 1024) {
        sendJson(['status' => 'error', 'message' => 'Image exceeds the maximum allowed size of 5MB.']);
    }

    $extension = $allowedTypes[$file['type']];
    $uploadDir = __DIR__ . '/../../images/products';
    if (!is_dir($uploadDir) && !mkdir($uploadDir, 0755, true)) {
        sendJson(['status' => 'error', 'message' => 'Unable to create image upload directory.']);
    }

    $fileName = uniqid('product_', true) . '.' . $extension;
    $destination = $uploadDir . '/' . $fileName;

    if (!move_uploaded_file($file['tmp_name'], $destination)) {
        sendJson(['status' => 'error', 'message' => 'Failed to save uploaded image.']);
    }

    return 'images/products/' . $fileName;
}

function getPrimaryKey($conn) {
    $pk = 'product_id';
    $check = $conn->query("SHOW COLUMNS FROM candles LIKE 'product_id'");
    if ($check->rowCount() == 0) {
        $pk = 'id';
    }
    return $pk;
}

function columnExists($conn, $column) {
    $check = $conn->query("SHOW COLUMNS FROM candles LIKE " . $conn->quote($column));
    return $check->rowCount() > 0;
}

$pk = getPrimaryKey($conn);
$hasStock = columnExists($conn, 'stock_qty');
$hasStatus = columnExists($conn, 'status');
$defaultImage = 'images/solis_signature.png';

$raw = file_get_contents('php://input');
$payload = json_decode($raw, true);
if (is_array($payload)) {
    $_POST = array_merge($_POST, $payload);
}

$action = $_SERVER['REQUEST_METHOD'] === 'GET'
    ? ($_GET['action'] ?? 'list')
    : ($_POST['action'] ?? 'list');

if (!isset($_SESSION['admin_logged_in']) || $_SESSION['admin_logged_in'] !== true) {
    sendJson(['status' => 'error', 'message' => 'Unauthorized access.']);
}

if ($_SERVER['REQUEST_METHOD'] === 'GET') {
    if ($action !== 'list') {
        sendJson(['status' => 'error', 'message' => 'Invalid request.']);
    }
    try {
        $stmt = $conn->prepare("SELECT * FROM candles ORDER BY $pk DESC");
        $stmt->execute();
        $products = $stmt->fetchAll(PDO::FETCH_ASSOC);
        sendJson(['status' => 'success', 'products' => $products]);
    } catch (PDOException $e) {
        sendJson(['status' => 'error', 'message' => 'Database error: ' . $e->getMessage()]);
    }
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    sendJson(['status' => 'error', 'message' => 'Invalid request method.']);
}

$name = trim($_POST['name'] ?? '');
$price = isset($_POST['price']) ? (float)$_POST['price'] : 0;
$description = trim($_POST['description'] ?? '');
$image_url = trim($_POST['image_url'] ?? '') ?: $defaultImage;
$scent_notes = trim($_POST['scent_notes'] ?? '');
$category = trim($_POST['category'] ?? '');
$stock_qty = isset($_POST['stock_qty']) ? (int)$_POST['stock_qty'] : 0;
$status = trim($_POST['status'] ?? '');

if (isset($_FILES['image_file']) && $_FILES['image_file']['error'] !== UPLOAD_ERR_NO_FILE) {
    $uploadedPath = processUploadedImage($_FILES['image_file']);
    if ($uploadedPath) {
        $image_url = $uploadedPath;
    }
}

switch ($action) {
    case 'create':
        if (!$name || $price <= 0) {
            sendJson(['status' => 'error', 'message' => 'Product name and price are required.']);
        }
        $fields = ['name', 'price', 'description', 'image_url', 'scent_notes', 'category'];
        $values = [$name, $price, $description, $image_url, $scent_notes, $category];

        if ($hasStock) {
            $fields[] = 'stock_qty';
            $values[] = $stock_qty;
        }
        if ($hasStatus) {
            $fields[] = 'status';
            $values[] = $status ?: ($stock_qty === 0 ? 'Out of Stock' : ($stock_qty <= 5 ? 'Low Stock' : 'In Stock'));
        }

        $placeholders = implode(', ', array_fill(0, count($fields), '?'));
        $sql = "INSERT INTO candles (" . implode(', ', $fields) . ") VALUES ($placeholders)";

        try {
            $stmt = $conn->prepare($sql);
            $stmt->execute($values);
            sendJson(['status' => 'success', 'message' => 'Product created successfully.', 'product_id' => $conn->lastInsertId()]);
        } catch (PDOException $e) {
            sendJson(['status' => 'error', 'message' => 'Database error: ' . $e->getMessage()]);
        }
        break;

    case 'update':
        $product_id = $_POST['product_id'] ?? null;
        if (!$product_id || !$name || $price <= 0) {
            sendJson(['status' => 'error', 'message' => 'Product ID, name, and price are required.']);
        }
        $sets = ['name = ?', 'price = ?', 'description = ?', 'image_url = ?', 'scent_notes = ?', 'category = ?'];
        $values = [$name, $price, $description, $image_url, $scent_notes, $category];

        if ($hasStock) {
            $sets[] = 'stock_qty = ?';
            $values[] = $stock_qty;
        }
        if ($hasStatus) {
            $sets[] = 'status = ?';
            $values[] = $status ?: ($stock_qty === 0 ? 'Out of Stock' : ($stock_qty <= 5 ? 'Low Stock' : 'In Stock'));
        }

        $values[] = $product_id;
        $sql = "UPDATE candles SET " . implode(', ', $sets) . " WHERE $pk = ?";

        try {
            $stmt = $conn->prepare($sql);
            $stmt->execute($values);
            sendJson(['status' => 'success', 'message' => 'Product updated successfully.']);
        } catch (PDOException $e) {
            sendJson(['status' => 'error', 'message' => 'Database error: ' . $e->getMessage()]);
        }
        break;

    case 'delete':
        $product_id = $_POST['product_id'] ?? null;
        if (!$product_id) {
            sendJson(['status' => 'error', 'message' => 'Product ID is required.']);
        }
        try {
            $stmt = $conn->prepare("DELETE FROM candles WHERE $pk = ?");
            $stmt->execute([$product_id]);
            if ($stmt->rowCount() === 0) {
                sendJson(['status' => 'error', 'message' => 'Product not found or already deleted.']);
            }
            sendJson(['status' => 'success', 'message' => 'Product deleted successfully.']);
        } catch (PDOException $e) {
            sendJson(['status' => 'error', 'message' => 'Database error: ' . $e->getMessage()]);
        }
        break;

    default:
        sendJson(['status' => 'error', 'message' => 'Unknown action.']);
}
