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

function slugify($text) {
    $text = trim(strtolower($text));
    $text = preg_replace('/[^a-z0-9]+/', '-', $text);
    $text = preg_replace('/-+/', '-', $text);
    return trim($text, '-');
}

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
        $stmt = $conn->prepare(
            "SELECT c.category_id, c.name, c.slug, c.description, c.created_at, " .
            "COUNT(p.product_id) AS products " .
            "FROM categories c " .
            "LEFT JOIN candles p ON p.category = c.name " .
            "GROUP BY c.category_id " .
            "ORDER BY c.created_at DESC"
        );
        $stmt->execute();
        $categories = $stmt->fetchAll(PDO::FETCH_ASSOC);
        sendJson(['status' => 'success', 'categories' => $categories]);
    } catch (PDOException $e) {
        sendJson(['status' => 'error', 'message' => 'Database error: ' . $e->getMessage()]);
    }
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    sendJson(['status' => 'error', 'message' => 'Invalid request method.']);
}

$name = trim($_POST['name'] ?? '');
$slug = trim($_POST['slug'] ?? '');
$description = trim($_POST['description'] ?? '');
$category_id = isset($_POST['category_id']) ? (int)$_POST['category_id'] : 0;

if (!$slug && $name) {
    $slug = slugify($name);
}

switch ($action) {
    case 'create':
        if (!$name) {
            sendJson(['status' => 'error', 'message' => 'Category name is required.']);
        }
        try {
            $stmt = $conn->prepare("INSERT INTO categories (name, slug, description) VALUES (?, ?, ?)");
            $stmt->execute([$name, $slug, $description]);
            sendJson(['status' => 'success', 'message' => 'Category created successfully.', 'category_id' => $conn->lastInsertId()]);
        } catch (PDOException $e) {
            if ($e->getCode() === '23000') {
                sendJson(['status' => 'error', 'message' => 'Category name or slug already exists.']);
            }
            sendJson(['status' => 'error', 'message' => 'Database error: ' . $e->getMessage()]);
        }
        break;

    case 'update':
        if (!$category_id || !$name) {
            sendJson(['status' => 'error', 'message' => 'Category ID and name are required.']);
        }
        try {
            $oldStmt = $conn->prepare("SELECT name FROM categories WHERE category_id = ?");
            $oldStmt->execute([$category_id]);
            $oldCategory = $oldStmt->fetch(PDO::FETCH_ASSOC);
            if (!$oldCategory) {
                sendJson(['status' => 'error', 'message' => 'Category not found.']);
            }
            $oldName = $oldCategory['name'];
            $stmt = $conn->prepare("UPDATE categories SET name = ?, slug = ?, description = ? WHERE category_id = ?");
            $stmt->execute([$name, $slug, $description, $category_id]);
            $updateProducts = $conn->prepare("UPDATE candles SET category = ? WHERE category = ?");
            $updateProducts->execute([$name, $oldName]);
            sendJson(['status' => 'success', 'message' => 'Category updated successfully.']);
        } catch (PDOException $e) {
            if ($e->getCode() === '23000') {
                sendJson(['status' => 'error', 'message' => 'Category name or slug already exists.']);
            }
            sendJson(['status' => 'error', 'message' => 'Database error: ' . $e->getMessage()]);
        }
        break;

    case 'delete':
        if (!$category_id) {
            sendJson(['status' => 'error', 'message' => 'Category ID is required.']);
        }
        try {
            $oldStmt = $conn->prepare("SELECT name FROM categories WHERE category_id = ?");
            $oldStmt->execute([$category_id]);
            $oldCategory = $oldStmt->fetch(PDO::FETCH_ASSOC);
            if (!$oldCategory) {
                sendJson(['status' => 'error', 'message' => 'Category not found.']);
            }
            $oldName = $oldCategory['name'];
            $updateProducts = $conn->prepare("UPDATE candles SET category = '' WHERE category = ?");
            $updateProducts->execute([$oldName]);
            $stmt = $conn->prepare("DELETE FROM categories WHERE category_id = ?");
            $stmt->execute([$category_id]);
            sendJson(['status' => 'success', 'message' => 'Category deleted successfully.']);
        } catch (PDOException $e) {
            sendJson(['status' => 'error', 'message' => 'Database error: ' . $e->getMessage()]);
        }
        break;

    default:
        sendJson(['status' => 'error', 'message' => 'Unknown action.']);
}
