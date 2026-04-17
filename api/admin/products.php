<?php
/**
 * UniMerch Admin API — Products CRUD
 */
require_once __DIR__ . '/../../includes/db.php';
require_once __DIR__ . '/../../includes/helpers.php';
require_once __DIR__ . '/../../includes/auth.php';

header('Content-Type: application/json; charset=utf-8');
requireMerchantAuthAPI();

$pdo = db();
$method = getRequestMethod();

switch ($method) {
    case 'GET':    listProducts($pdo); break;
    case 'POST':   
        if (isset($_POST['id']) || isset($_GET['id'])) {
            updateProduct($pdo);
        } else {
            createProduct($pdo);
        }
        break;
    case 'PUT':    updateProduct($pdo); break;
    case 'DELETE': deleteProduct($pdo); break;
    default:       jsonResponse(['success' => false, 'message' => 'Method not allowed'], 405);
}

function listProducts(PDO $pdo): void {
    $search = $_GET['search'] ?? '';
    $status = $_GET['status'] ?? '';
    $category = $_GET['category'] ?? '';

    $where = ['1=1'];
    $params = [];

    if ($status) {
        $where[] = "p.status = ?";
        $params[] = $status;
    }
    if ($category) {
        $where[] = "p.category_id = ?";
        $params[] = (int) $category;
    }
    if ($search) {
        $where[] = "(p.name LIKE ? OR c.code LIKE ?)";
        $params[] = '%' . $search . '%';
        $params[] = '%' . $search . '%';
    }

    $whereSQL = implode(' AND ', $where);
    $stmt = $pdo->prepare("
        SELECT p.*, c.code AS category_code, c.name AS category_name
        FROM products p
        JOIN categories c ON p.category_id = c.id
        WHERE {$whereSQL}
        ORDER BY p.created_at DESC
    ");
    $stmt->execute($params);
    $products = $stmt->fetchAll();

    foreach ($products as &$p) {
        $p['sizes'] = $p['sizes'] ? json_decode($p['sizes'], true) : null;
        $p['image_url'] = BASE_URL . '/uploads/' . $p['image'];
    }

    jsonResponse(['success' => true, 'data' => $products]);
}

function createProduct(PDO $pdo): void {
    // Handle multipart form data for image upload
    $name        = sanitize($_POST['name'] ?? '');
    $categoryId  = (int) ($_POST['category_id'] ?? 0);
    $description = sanitize($_POST['description'] ?? '');
    $price       = (float) ($_POST['price'] ?? 0);
    $stock       = (int) ($_POST['stock'] ?? 0);
    $sizes       = $_POST['sizes'] ?? null;
    $featured    = (int) ($_POST['featured'] ?? 0);

    if (!$name || !$categoryId || $price <= 0) {
        jsonResponse(['success' => false, 'message' => 'Name, category, and price are required'], 400);
    }

    // Handle image upload
    $image = 'default-product.jpg';
    if (isset($_FILES['image']) && $_FILES['image']['error'] === UPLOAD_ERR_OK) {
        $uploaded = uploadImage($_FILES['image']);
        if ($uploaded) $image = $uploaded;
    }

    // Encode sizes as JSON
    $sizesJson = null;
    if ($sizes) {
        $sizesArray = array_map('trim', explode(',', $sizes));
        $sizesJson = json_encode($sizesArray);
    }

    $stmt = $pdo->prepare("
        INSERT INTO products (category_id, name, description, price, stock, image, sizes, featured, status)
        VALUES (?, ?, ?, ?, ?, ?, ?, ?, 'active')
    ");
    $stmt->execute([$categoryId, $name, $description, $price, $stock, $image, $sizesJson, $featured]);

    jsonResponse(['success' => true, 'message' => 'Product created successfully', 'id' => (int) $pdo->lastInsertId()]);
}

function updateProduct(PDO $pdo): void {
    // For PUT with files, check $_POST first, fallback to JSON
    $contentType = $_SERVER['CONTENT_TYPE'] ?? '';
    
    if (str_contains($contentType, 'multipart/form-data')) {
        $data = $_POST;
    } else {
        $data = getJsonBody();
    }

    $id = (int) ($data['id'] ?? $_GET['id'] ?? 0);
    if (!$id) {
        jsonResponse(['success' => false, 'message' => 'Product ID required'], 400);
    }

    $fields = [];
    $params = [];

    if (isset($data['name'])) { $fields[] = 'name = ?'; $params[] = sanitize($data['name']); }
    if (isset($data['category_id'])) { $fields[] = 'category_id = ?'; $params[] = (int) $data['category_id']; }
    if (isset($data['description'])) { $fields[] = 'description = ?'; $params[] = sanitize($data['description']); }
    if (isset($data['price'])) { $fields[] = 'price = ?'; $params[] = (float) $data['price']; }
    if (isset($data['stock'])) { $fields[] = 'stock = ?'; $params[] = (int) $data['stock']; }
    if (isset($data['status'])) { $fields[] = 'status = ?'; $params[] = $data['status']; }
    if (isset($data['featured'])) { $fields[] = 'featured = ?'; $params[] = (int) $data['featured']; }
    if (isset($data['sizes'])) {
        $sizesArray = array_map('trim', explode(',', $data['sizes']));
        $fields[] = 'sizes = ?';
        $params[] = json_encode($sizesArray);
    }

    // Handle new image
    if (isset($_FILES['image']) && $_FILES['image']['error'] === UPLOAD_ERR_OK) {
        $uploaded = uploadImage($_FILES['image']);
        if ($uploaded) {
            $fields[] = 'image = ?';
            $params[] = $uploaded;
        }
    }

    if (empty($fields)) {
        jsonResponse(['success' => false, 'message' => 'No fields to update'], 400);
    }

    $params[] = $id;
    $sql = "UPDATE products SET " . implode(', ', $fields) . " WHERE id = ?";
    $pdo->prepare($sql)->execute($params);

    jsonResponse(['success' => true, 'message' => 'Product updated']);
}

function deleteProduct(PDO $pdo): void {
    $id = (int) ($_GET['id'] ?? 0);
    if (!$id) {
        jsonResponse(['success' => false, 'message' => 'Product ID required'], 400);
    }

    $pdo->prepare("DELETE FROM products WHERE id = ?")->execute([$id]);
    jsonResponse(['success' => true, 'message' => 'Product deleted']);
}
