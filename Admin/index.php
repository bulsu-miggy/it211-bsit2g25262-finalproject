<?php
session_start();
if (!isset($_SESSION['logged_in']) || $_SESSION['role'] !== 'admin') {
    header('Location: ../PHP/loginpage.php');
    exit();
}
require_once 'config/db.php';
require_once 'include/function.php';

// ============================================
// HANDLE ALL FORM SUBMISSIONS
// ============================================

// Handle ADD CATEGORY
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['add_category'])) {
    $pdo = getDBConnection();
    
    $name = trim($_POST['name']);
    $slug = !empty($_POST['slug']) ? trim($_POST['slug']) : generateSlug($name);
    $description = trim($_POST['description'] ?? '');
    $is_active = isset($_POST['is_active']) ? 1 : 0;
    
    try {
        $sql = "INSERT INTO categories (name, slug, description, is_active) VALUES (:name, :slug, :description, :is_active)";
        $stmt = $pdo->prepare($sql);
        $stmt->bindParam(':name', $name);
        $stmt->bindParam(':slug', $slug);
        $stmt->bindParam(':description', $description);
        $stmt->bindParam(':is_active', $is_active);
        
        if ($stmt->execute()) {
            $_SESSION['success'] = 'Category added successfully!';
            header('Location: index.php?page=categories');
            exit;
        }
    } catch (PDOException $e) {
        if ($e->errorInfo[1] == 1062) {
            $_SESSION['error'] = 'Category slug already exists. Please use a different slug.';
        } else {
            $_SESSION['error'] = 'Failed to add category.';
        }
        header('Location: index.php?page=add-category');
        exit;
    }
}

// Handle EDIT CATEGORY
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['edit_category'])) {
    $pdo = getDBConnection();
    $id = $_POST['category_id'];
    $name = trim($_POST['name']);
    $slug = !empty($_POST['slug']) ? trim($_POST['slug']) : generateSlug($name);
    $description = trim($_POST['description'] ?? '');
    $is_active = isset($_POST['is_active']) ? 1 : 0;
    
    try {
        $sql = "UPDATE categories SET name = :name, slug = :slug, description = :description, is_active = :is_active WHERE id = :id";
        $stmt = $pdo->prepare($sql);
        $stmt->bindParam(':name', $name);
        $stmt->bindParam(':slug', $slug);
        $stmt->bindParam(':description', $description);
        $stmt->bindParam(':is_active', $is_active);
        $stmt->bindParam(':id', $id);
        
        if ($stmt->execute()) {
            $_SESSION['success'] = 'Category updated successfully!';
        } else {
            $errorInfo = $stmt->errorInfo();
            $_SESSION['error'] = 'Failed to update: ' . $errorInfo[2];
        }
    } catch (PDOException $e) {
        if ($e->errorInfo[1] == 1062) {
            $_SESSION['error'] = 'Category slug already exists. Please use a different slug.';
        } else {
            $_SESSION['error'] = 'PDO Error: ' . $e->getMessage();
        }
    }
    
    header('Location: index.php?page=categories');
    exit;
}

// Handle DELETE CATEGORY
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['delete_category_id'])) {
    $pdo = getDBConnection();
    $id = $_POST['delete_category_id'];
    
    $stmt = $pdo->prepare("SELECT COUNT(*) as count FROM products WHERE category_id = ?");
    $stmt->execute([$id]);
    $result = $stmt->fetch();
    
    if ($result['count'] > 0) {
        $_SESSION['error'] = 'Cannot delete category with existing products.';
        header('Location: index.php?page=categories');
        exit;
    }
    
    $stmt = $pdo->prepare("DELETE FROM categories WHERE id = ?");
    if ($stmt->execute([$id])) {
        $_SESSION['success'] = 'Category deleted successfully!';
    } else {
        $_SESSION['error'] = 'Failed to delete category.';
    }
    
    header('Location: index.php?page=categories');
    exit;
}

// Handle ADD PRODUCT
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['add_product'])) {
    $pdo = getDBConnection();
    $name = trim($_POST['name'] ?? '');
    $category_id = !empty($_POST['category_id']) ? (int) $_POST['category_id'] : null;
    $price = trim($_POST['price'] ?? '');
    $stock_quantity = isset($_POST['stock_quantity']) && is_numeric($_POST['stock_quantity']) ? (int) $_POST['stock_quantity'] : 0;
    $description = trim($_POST['description'] ?? '');
    $is_active = isset($_POST['is_active']) && $_POST['is_active'] === '1' ? 1 : 0;
    
    if ($name === '' || $price === '' || !is_numeric($price) || $category_id === null) {
        $_SESSION['error'] = 'Please provide a product name, valid category, and valid price.';
        header('Location: index.php?page=add-product');
        exit;
    }

    $price = number_format((float) $price, 2, '.', '');
    $slug = generateUniqueSlug($name);
    $image = null;
    if (!empty($_FILES['imglink']['name'])) {
        $uploadedImage = uploadFile($_FILES['imglink'], __DIR__ . '/assets/uploads/products');
        if ($uploadedImage !== false) {
            $image = $uploadedImage;
        } else {
            $_SESSION['error'] = 'Failed to upload product image. Please use a JPG, PNG, GIF or WEBP file under 5MB.';
            header('Location: index.php?page=add-product');
            exit;
        }
    }
    
    try {
        $sql = "INSERT INTO products (name, slug, category_id, price, stock_quantity, description, is_active, image) 
                VALUES (:name, :slug, :category_id, :price, :stock_quantity, :description, :is_active, :image)";
        $stmt = $pdo->prepare($sql);
        $stmt->bindParam(':name', $name);
        $stmt->bindParam(':slug', $slug);
        $stmt->bindParam(':category_id', $category_id, PDO::PARAM_INT);
        $stmt->bindParam(':price', $price);
        $stmt->bindParam(':stock_quantity', $stock_quantity, PDO::PARAM_INT);
        $stmt->bindParam(':description', $description);
        $stmt->bindParam(':is_active', $is_active, PDO::PARAM_INT);
        $stmt->bindParam(':image', $image);
        
        if ($stmt->execute()) {
            $_SESSION['success'] = 'Product added successfully!';
            header('Location: index.php?page=products');
            exit;
        }
    } catch (PDOException $e) {
        if (isset($e->errorInfo[1]) && $e->errorInfo[1] == 1062) {
            $_SESSION['error'] = 'Product already exists or duplicate slug. Please use a different product name.';
        } else {
            $_SESSION['error'] = 'Failed to add product. ' . $e->getMessage();
        }
        header('Location: index.php?page=add-product');
        exit;
    }
}

// Handle EDIT PRODUCT
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['edit_product'])) {
    $pdo = getDBConnection();
    $id = $_POST['product_id'];
    $name = trim($_POST['name']);
    $category_id = !empty($_POST['category_id']) ? $_POST['category_id'] : null;
    $price = $_POST['price'];
    $stock_quantity = $_POST['stock_quantity'] ?? 0;
    $description = trim($_POST['description'] ?? '');
    $is_active = isset($_POST['is_active']) ? 1 : 0;
    
    $slug = generateUniqueSlug($name, 'products', 'slug', $id);
    
    try {
        $sql = "UPDATE products SET 
                name = :name, slug = :slug, category_id = :category_id, 
                price = :price, stock_quantity = :stock_quantity, description = :description, is_active = :is_active
                WHERE id = :id";
        $stmt = $pdo->prepare($sql);
        $stmt->bindParam(':name', $name);
        $stmt->bindParam(':slug', $slug);
        $stmt->bindParam(':category_id', $category_id);
        $stmt->bindParam(':price', $price);
        $stmt->bindParam(':stock_quantity', $stock_quantity);
        $stmt->bindParam(':description', $description);
        $stmt->bindParam(':is_active', $is_active);
        $stmt->bindParam(':id', $id);
        
        if ($stmt->execute()) {
            $_SESSION['success'] = 'Product updated successfully!';
            header('Location: index.php?page=products');
            exit;
        } else {
            $_SESSION['error'] = 'Failed to update product.';
            header('Location: index.php?page=edit-product&id=' . $id);
            exit;
        }
    } catch (PDOException $e) {
        if ($e->errorInfo[1] == 1062) {
            $_SESSION['error'] = 'Product already exists or duplicate slug. Please use a different product name.';
        } else {
            $_SESSION['error'] = 'Failed to update product.';
        }
        header('Location: index.php?page=edit-product&id=' . $id);
        exit;
    }
}

// Handle DELETE PRODUCT
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['delete_product_id'])) {
    $pdo = getDBConnection();
    $id = $_POST['delete_product_id'];
    
    $stmt = $pdo->prepare("DELETE FROM product_images WHERE product_id = ?");
    $stmt->execute([$id]);
    
    $stmt = $pdo->prepare("DELETE FROM products WHERE id = ?");
    if ($stmt->execute([$id])) {
        $_SESSION['success'] = 'Product deleted successfully!';
    } else {
        $_SESSION['error'] = 'Failed to delete product.';
    }
    
    header('Location: index.php?page=products');
    exit;
}

// Handle ADD CUSTOMER
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['add_customer'])) {
    $pdo = getDBConnection();
    $first_name = trim($_POST['first_name']);
    $last_name = trim($_POST['last_name']);
    $email = trim($_POST['email']);
    $phone = trim($_POST['phone'] ?? '');
    $customer_type = $_POST['customer_type'] ?? 'regular';
    $status = $_POST['status'] ?? 'active';
    
    try {
        $sql = "INSERT INTO customers (first_name, last_name, email, phone, customer_type, status) 
                VALUES (:first_name, :last_name, :email, :phone, :customer_type, :status)";
        $stmt = $pdo->prepare($sql);
        $stmt->bindParam(':first_name', $first_name);
        $stmt->bindParam(':last_name', $last_name);
        $stmt->bindParam(':email', $email);
        $stmt->bindParam(':phone', $phone);
        $stmt->bindParam(':customer_type', $customer_type);
        $stmt->bindParam(':status', $status);
        
        if ($stmt->execute()) {
            $_SESSION['success'] = 'Customer added successfully!';
            header('Location: index.php?page=customers');
            exit;
        }
    } catch (PDOException $e) {
        if ($e->errorInfo[1] == 1062) {
            $_SESSION['error'] = 'Email address already exists. Please use a different email.';
        } else {
            $_SESSION['error'] = 'Failed to add customer.';
        }
        header('Location: index.php?page=add-customer');
        exit;
    }
}

// Handle EDIT CUSTOMER
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['edit_customer'])) {
    $pdo = getDBConnection();
    $id = $_POST['customer_id'];
    $first_name = trim($_POST['first_name']);
    $last_name = trim($_POST['last_name']);
    $email = trim($_POST['email']);
    $phone = trim($_POST['phone'] ?? '');
    $customer_type = $_POST['customer_type'] ?? 'regular';
    $status = $_POST['status'] ?? 'active';
    
    try {
        $sql = "UPDATE customers SET 
                first_name = :first_name, last_name = :last_name, email = :email, phone = :phone, 
                customer_type = :customer_type, status = :status WHERE id = :id";
        $stmt = $pdo->prepare($sql);
        $stmt->bindParam(':first_name', $first_name);
        $stmt->bindParam(':last_name', $last_name);
        $stmt->bindParam(':email', $email);
        $stmt->bindParam(':phone', $phone);
        $stmt->bindParam(':customer_type', $customer_type);
        $stmt->bindParam(':status', $status);
        $stmt->bindParam(':id', $id);
        
        if ($stmt->execute()) {
            $_SESSION['success'] = 'Customer updated successfully!';
            header('Location: index.php?page=customers');
            exit;
        } else {
            $_SESSION['error'] = 'Failed to update customer.';
            header('Location: index.php?page=edit-customer&id=' . $id);
            exit;
        }
    } catch (PDOException $e) {
        if ($e->errorInfo[1] == 1062) {
            $_SESSION['error'] = 'Email address already exists. Please use a different email.';
        } else {
            $_SESSION['error'] = 'Failed to update customer.';
        }
        header('Location: index.php?page=edit-customer&id=' . $id);
        exit;
    }
}

// Handle DELETE CUSTOMER
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['delete_customer_id'])) {
    $pdo = getDBConnection();
    $id = $_POST['delete_customer_id'];
    
    $stmt = $pdo->prepare("DELETE FROM customers WHERE id = ?");
    if ($stmt->execute([$id])) {
        $_SESSION['success'] = 'Customer deleted successfully!';
    } else {
        $_SESSION['error'] = 'Failed to delete customer.';
    }
    
    header('Location: index.php?page=customers');
    exit;
}

// Simple routing
$page = $_GET['page'] ?? 'dashboard';

$pages = [
    'dashboard' => 'pages/dashboard.php',
    'products' => 'pages/products.php',
    'add-product' => 'pages/add-product.php',
    'edit-product' => 'pages/edit-product.php',
    'categories' => 'pages/categories.php',
    'add-category' => 'pages/add-category.php',
    'edit-category' => 'pages/edit-category.php',
    'customers' => 'pages/customers.php',
    'add-customer' => 'pages/add-customer.php',
    'edit-customer' => 'pages/edit-customer.php',
    'order' => 'pages/order.php',
    'analytics' => 'pages/analytics.php',
    'settings' => 'pages/settings.php',
];

$content = $pages[$page] ?? 'pages/dashboard.php';

// Get session messages
$success_message = $_SESSION['success'] ?? null;
$error_message = $_SESSION['error'] ?? null;
unset($_SESSION['success'], $_SESSION['error']);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Dashboard - Lasa Filipina</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="assets/css/styles.css">
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
</head>
<body class="bg-[#fcf8f3] text-[#4a3728]">
    <div class="flex h-screen">
        <?php include 'include/sidebar.php'; ?>
        
        <div class="flex-1 overflow-auto">
            <?php include 'include/header.php'; ?>
            
            <main class="p-8">
                <?php if ($success_message): ?>
                    <div class="mb-4 border-2 border-green-300 bg-green-50 text-green-800 p-4 rounded flex items-center gap-2">
                        <svg class="w-5 h-5 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                        </svg>
                        <span><?php echo htmlspecialchars($success_message); ?></span>
                    </div>
                <?php endif; ?>
                
                <?php if ($error_message): ?>
                    <div class="mb-4 border-2 border-red-300 bg-red-50 text-red-800 p-4 rounded flex items-center gap-2">
                        <svg class="w-5 h-5 text-red-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                        </svg>
                        <span><?php echo htmlspecialchars($error_message); ?></span>
                    </div>
                <?php endif; ?>
                
                <?php include $content; ?>
            </main>
        </div>
    </div>
    
    <script src="assets/js/script.js"></script>
</body>
</html>