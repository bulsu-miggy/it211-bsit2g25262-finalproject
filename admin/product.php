<?php
// Database connection
require_once '../db/action/dbconfig.php';

// Start session to get admin info
session_start();

// Get current admin info
$adminUsername = isset($_SESSION['username']) ? $_SESSION['username'] : 'Admin';
$adminInitial = strtoupper(substr($adminUsername, 0, 1));

// Handle form submissions
$message = '';
$messageType = '';

function sendJson($success, $message, $extra = []) {
    header('Content-Type: application/json; charset=utf-8');
    echo json_encode(array_merge(['success' => $success, 'message' => $message], $extra));
    exit;
}

function syncPrimaryProductImage(PDO $conn, int $productId)
{
    if ($productId <= 0) {
        return;
    }

    $stmt = $conn->prepare("SELECT image_path FROM product_images WHERE product_id = ? ORDER BY sort_order ASC, id ASC LIMIT 1");
    $stmt->execute([$productId]);
    $primaryImage = trim((string) ($stmt->fetchColumn() ?: ''));

    if ($primaryImage === '') {
        $primaryImage = 'assets2/adidasblablabla.png';
    }

    $update = $conn->prepare("UPDATE products SET image = ? WHERE id = ?");
    $update->execute([$primaryImage, $productId]);
}

function syncDefaultProductImagesFromGallery(PDO $conn)
{
    $stmt = $conn->prepare("UPDATE products p
        SET p.image = (
            SELECT pi.image_path
            FROM product_images pi
            WHERE pi.product_id = p.id
            ORDER BY pi.sort_order ASC, pi.id ASC
            LIMIT 1
        )
        WHERE (p.image IS NULL OR TRIM(p.image) = '' OR p.image = 'assets2/adidasblablabla.png')
          AND EXISTS (SELECT 1 FROM product_images pe WHERE pe.product_id = p.id)");
    $stmt->execute();
}

function normalizeAdminProductImagePath($rawPath)
{
    $path = trim((string) $rawPath);
    if ($path === '') {
        return '';
    }

    $path = str_replace('\\', '/', $path);
    if (preg_match('#^(https?:)?//#i', $path) || strpos($path, 'data:') === 0) {
        return $path;
    }

    // Keep modern product image paths unchanged.
    if (strpos($path, 'images/products/') === 0) {
        return '../' . ltrim($path, '/');
    }

    // Legacy rows may store images/<file>; prefer the moved file under images/products/<file> when it exists.
    if (strpos($path, 'images/') === 0) {
        $fallbackProductPath = 'images/products/' . basename($path);
        if (file_exists(__DIR__ . '/../' . $fallbackProductPath)) {
            return '../' . $fallbackProductPath;
        }
    }

    if (strpos($path, 'assets2/') === 0 || strpos($path, 'images/') === 0) {
        return '../' . ltrim($path, '/');
    }

    return '../images/products/' . basename($path);
}

syncDefaultProductImagesFromGallery($conn);

if ($_SERVER['REQUEST_METHOD'] === 'GET' && isset($_GET['action']) && $_GET['action'] === 'get_product') {
    $productId = intval($_GET['id']);
    $stmt = $conn->prepare("SELECT * FROM products WHERE id = ?");
    $stmt->execute([$productId]);
    $product = $stmt->fetch(PDO::FETCH_ASSOC);

    if ($product) {
        $stmt = $conn->prepare("SELECT * FROM product_images WHERE product_id = ? ORDER BY sort_order ASC");
        $stmt->execute([$productId]);
        $product['images'] = $stmt->fetchAll(PDO::FETCH_ASSOC);

        foreach ($product['images'] as &$imageRow) {
            $imageRow['image_preview'] = normalizeAdminProductImagePath($imageRow['image_path'] ?? '');
        }
        unset($imageRow);

        echo json_encode(['success' => true, 'product' => $product]);
    } else {
        echo json_encode(['success' => false, 'message' => 'Product not found']);
    }
    exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['action'])) {
        $action = $_POST['action'];

        if ($action === 'add_product') {
            // Add new product
            $name = trim($_POST['name']);
            $description = trim($_POST['description']);
            $price = floatval($_POST['price']);
            $category_id = intval($_POST['category_id']);
            $stock = intval($_POST['stock']);
            $color = trim($_POST['color']);
            $size = trim($_POST['size']);

            if (empty($name) || $price <= 0) {
                sendJson(false, 'Product name and valid price are required.');
            } else {
                try {
                    $stmt = $conn->prepare("INSERT INTO products (name, description, price, category_id, stock, color, size) VALUES (?, ?, ?, ?, ?, ?, ?)");
                    $stmt->execute([$name, $description, $price, $category_id, $stock, $color, $size]);
                    $product_id = $conn->lastInsertId();

                    // Handle image uploads
                    if (isset($_FILES['images']) && !empty($_FILES['images']['name'][0])) {
                        $uploadDir = '../images/products/';
                        if (!is_dir($uploadDir)) {
                            mkdir($uploadDir, 0755, true);
                        }

                        $imageOrder = 0;
                        foreach ($_FILES['images']['tmp_name'] as $key => $tmp_name) {
                            if ($_FILES['images']['error'][$key] === UPLOAD_ERR_OK) {
                                $fileName = $_FILES['images']['name'][$key];
                                $fileExt = strtolower(pathinfo($fileName, PATHINFO_EXTENSION));
                                $allowedExts = ['jpg', 'jpeg', 'png', 'gif', 'webp'];

                                if (in_array($fileExt, $allowedExts)) {
                                    $newFileName = $product_id . '_' . $imageOrder . '.' . $fileExt;
                                    $filePath = $uploadDir . $newFileName;

                                    if (move_uploaded_file($tmp_name, $filePath)) {
                                        // Save image to database
                                        $stmt = $conn->prepare("INSERT INTO product_images (product_id, image_path, sort_order) VALUES (?, ?, ?)");
                                        $stmt->execute([$product_id, 'images/products/' . $newFileName, $imageOrder]);
                                        $imageOrder++;
                                    }
                                }
                            }
                        }
                    }

                    syncPrimaryProductImage($conn, (int) $product_id);

                    sendJson(true, 'Product added successfully!');
                } catch (Exception $e) {
                    sendJson(false, 'Error adding product: ' . $e->getMessage());
                }
            }
        } elseif ($action === 'edit_product') {
            // Edit existing product
            $id = intval($_POST['id'] ?? $_POST['product_id'] ?? 0);
            $name = trim($_POST['name']);
            $description = trim($_POST['description']);
            $price = floatval($_POST['price']);
            $category_id = intval($_POST['category_id']);
            $stock = intval($_POST['stock']);
            $color = trim($_POST['color']);
            $size = trim($_POST['size']);

            if (empty($name) || $price <= 0) {
                sendJson(false, 'Product name and valid price are required.');
            } else {
                try {
                    $stmt = $conn->prepare("UPDATE products SET name = ?, description = ?, price = ?, category_id = ?, stock = ?, color = ?, size = ? WHERE id = ?");
                    $stmt->execute([$name, $description, $price, $category_id, $stock, $color, $size, $id]);

                    // Handle new image uploads
                    if (isset($_FILES['images']) && !empty($_FILES['images']['name'][0])) {
                        $uploadDir = '../images/products/';
                        if (!is_dir($uploadDir)) {
                            mkdir($uploadDir, 0755, true);
                        }

                        // Get current max sort order
                        $stmt = $conn->prepare("SELECT MAX(sort_order) as max_order FROM product_images WHERE product_id = ?");
                        $stmt->execute([$id]);
                        $maxOrder = $stmt->fetch(PDO::FETCH_ASSOC)['max_order'] ?? -1;

                        $imageOrder = $maxOrder + 1;
                        foreach ($_FILES['images']['tmp_name'] as $key => $tmp_name) {
                            if ($_FILES['images']['error'][$key] === UPLOAD_ERR_OK) {
                                $fileName = $_FILES['images']['name'][$key];
                                $fileExt = strtolower(pathinfo($fileName, PATHINFO_EXTENSION));
                                $allowedExts = ['jpg', 'jpeg', 'png', 'gif', 'webp'];

                                if (in_array($fileExt, $allowedExts)) {
                                    $newFileName = $id . '_' . $imageOrder . '.' . $fileExt;
                                    $filePath = $uploadDir . $newFileName;

                                    if (move_uploaded_file($tmp_name, $filePath)) {
                                        // Save image to database
                                        $stmt = $conn->prepare("INSERT INTO product_images (product_id, image_path, sort_order) VALUES (?, ?, ?)");
                                        $stmt->execute([$id, 'images/products/' . $newFileName, $imageOrder]);
                                        $imageOrder++;
                                    }
                                }
                            }
                        }
                    }

                    syncPrimaryProductImage($conn, $id);

                    sendJson(true, 'Product updated successfully!');
                } catch (Exception $e) {
                    sendJson(false, 'Error updating product: ' . $e->getMessage());
                }
            }
        } elseif ($action === 'delete_product') {
            // Delete product
            $id = intval($_POST['id'] ?? $_POST['product_id'] ?? 0);
            try {
                // Delete associated images first
                $stmt = $conn->prepare("SELECT image_path FROM product_images WHERE product_id = ?");
                $stmt->execute([$id]);
                $images = $stmt->fetchAll(PDO::FETCH_ASSOC);

                foreach ($images as $image) {
                    $filePath = '../' . $image['image_path'];
                    if (file_exists($filePath)) {
                        unlink($filePath);
                    }
                }

                $stmt = $conn->prepare("DELETE FROM product_images WHERE product_id = ?");
                $stmt->execute([$id]);

                $stmt = $conn->prepare("DELETE FROM products WHERE id = ?");
                $stmt->execute([$id]);

                sendJson(true, 'Product deleted successfully!');
            } catch (Exception $e) {
                sendJson(false, 'Error deleting product: ' . $e->getMessage());
            }
        } elseif ($action === 'reorder_images') {
            // Reorder images
            $imageOrders = json_decode($_POST['image_orders'], true);
            try {
                $productIdForReorder = 0;
                foreach ($imageOrders as $imageId => $sortOrder) {
                    if ($productIdForReorder === 0) {
                        $productStmt = $conn->prepare("SELECT product_id FROM product_images WHERE id = ? LIMIT 1");
                        $productStmt->execute([(int) $imageId]);
                        $productIdForReorder = (int) ($productStmt->fetchColumn() ?: 0);
                    }
                    $stmt = $conn->prepare("UPDATE product_images SET sort_order = ? WHERE id = ?");
                    $stmt->execute([$sortOrder, $imageId]);
                }

                if ($productIdForReorder > 0) {
                    syncPrimaryProductImage($conn, $productIdForReorder);
                }

                sendJson(true, 'Images reordered successfully!');
            } catch (Exception $e) {
                sendJson(false, 'Error reordering images: ' . $e->getMessage());
            }
        } elseif ($action === 'delete_image') {
            $imageId = intval($_POST['image_id'] ?? 0);
            try {
                $stmt = $conn->prepare("SELECT product_id, image_path FROM product_images WHERE id = ?");
                $stmt->execute([$imageId]);
                $image = $stmt->fetch(PDO::FETCH_ASSOC);
                if ($image) {
                    $productIdForDeletedImage = (int) ($image['product_id'] ?? 0);
                    $filePath = '../' . $image['image_path'];
                    if (file_exists($filePath)) {
                        unlink($filePath);
                    }
                    $stmt = $conn->prepare("DELETE FROM product_images WHERE id = ?");
                    $stmt->execute([$imageId]);

                    if ($productIdForDeletedImage > 0) {
                        syncPrimaryProductImage($conn, $productIdForDeletedImage);
                    }

                    sendJson(true, 'Image deleted successfully!');
                } else {
                    sendJson(false, 'Image not found.');
                }
            } catch (Exception $e) {
                sendJson(false, 'Error deleting image: ' . $e->getMessage());
            }
        }
    }
}

// Fetch categories
$stmt = $conn->prepare("SELECT id, name FROM categories ORDER BY name");
$stmt->execute();
$categories = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Fetch products with category info and images
$query = "SELECT p.*, c.name as category_name FROM products p LEFT JOIN categories c ON p.category_id = c.id";
$conditions = [];
$params = [];

if (isset($_GET['category']) && !empty($_GET['category'])) {
    $conditions[] = "c.name = ?";
    $params[] = $_GET['category'];
}

if (isset($_GET['search']) && !empty($_GET['search'])) {
    $conditions[] = "p.name LIKE ?";
    $params[] = '%' . $_GET['search'] . '%';
}

if (!empty($conditions)) {
    $query .= " WHERE " . implode(" AND ", $conditions);
}

$query .= " ORDER BY p.created_at DESC";

$stmt = $conn->prepare($query);
$stmt->execute($params);
$productsData = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Get images for each product
$products = [];
foreach ($productsData as $product) {
    $stmt = $conn->prepare("SELECT * FROM product_images WHERE product_id = ? ORDER BY sort_order ASC");
    $stmt->execute([$product['id']]);
    $product['images'] = $stmt->fetchAll(PDO::FETCH_ASSOC);
    $products[] = $product;
}


// Debug: Show number of products found
// echo "<!-- Debug: Found " . count($products) . " products -->";

// Get low stock products for dashboard notification
$stmt = $conn->prepare("SELECT COUNT(*) as low_stock_count FROM products WHERE stock <= 10");
$stmt->execute();
$lowStockCount = $stmt->fetch(PDO::FETCH_ASSOC)['low_stock_count'] ?? 0;
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Product Management</title>
    <link rel="icon" type="image/png" href="../assets2/logo.png">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.1/font/bootstrap-icons.css">
    <link rel="stylesheet" href="admin.css">
    <style>
        .sortable-images { cursor: move; }
        .image-preview { position: relative; display: inline-block; margin: 5px; }
        .image-preview img { width: 80px; height: 80px; object-fit: cover; border-radius: 5px; }
        .delete-image { position: absolute; top: -5px; right: -5px; background: red; color: white; border-radius: 50%; width: 20px; height: 20px; display: flex; align-items: center; justify-content: center; font-size: 12px; cursor: pointer; }

        #listView .products-img-placeholder {
            width: 56px;
            height: 56px;
            overflow: hidden;
        }

        #listView .products-img-placeholder img {
            width: 100%;
            height: 100%;
            object-fit: cover;
            display: block;
        }

        #gridView .products-card-img {
            height: 180px;
            overflow: hidden;
        }

        #gridView .products-card-img img {
            width: 100%;
            height: 100%;
            object-fit: cover;
            display: block;
        }
    </style>
</head>
<body class="bg-light min-vh-100 text-dark">
    <!-- navigation sidebar -->
    <nav id="sidebar" class="bg-white border-end d-flex flex-column position-fixed top-0 start-0 min-vh-100" style="z-index:1000;">
        <!-- logo -->
        <div class="border-bottom px-3 py-3 d-flex align-items-center gap-2 fw-bold fs-5">
            <span style="font-size:1.4rem;"></span>Laces
        </div>

        <!-- Navigation part -->
        <ul class="nav flex-column mt-3">
            <li class="nav-item">
                <a href="dashboard.php" class="nav-link d-flex align-items-center gap-2 fw-semibold rounded text-secondary mx-2 my-1 px-3 py-2">
                    <i class="bi bi-grid-1x2"></i>
                    Dashboard
                </a>
            </li>
            <li class="nav-item">
                <a href="orders.php" class="nav-link d-flex align-items-center gap-2 fw-semibold text-secondary rounded mx-2 my-1 px-3 py-2">
                    <i class="bi bi-cart-fill"></i>
                    Orders
                </a>
            </li>
            <li class="nav-item">
                <a href="product.php" class="nav-link active d-flex align-items-center gap-2 fw-semibold rounded mx-2 my-1 px-3 py-2">
                    <i class="bi bi-box-seam-fill"></i>
                    Products
                </a>
            </li>
            <li class="nav-item">
                <a href="categories.php" class="nav-link d-flex align-items-center gap-2 fw-semibold rounded text-secondary mx-2 my-1 px-3 py-2">
                    <i class="bi bi-folder"></i>
                    Categories
                </a>
            </li>
            <li class="nav-item">
                <a href="customer.php" class="nav-link d-flex align-items-center gap-2 fw-semibold rounded text-secondary mx-2 my-1 px-3 py-2">
                    <i class="bi bi-people"></i>
                    Customers
                </a>
            </li>
            <li class="nav-item">
                <a href="analytics.php" class="nav-link d-flex align-items-center gap-2 fw-semibold rounded text-secondary mx-2 my-1 px-3 py-2">
                    <i class="bi bi-bar-chart"></i>
                    Analytics
                </a>
            </li>
        </ul>
        <!-- footer -->
        <div class="mt-auto border-top px-3 py-3">
            <div class="d-flex align-items-center gap-2">
                <div class="rounded-circle bg-primary d-flex align-items-center justify-content-center text-white fw-bold flex-shrink-0" style="width: 36px; height: 36px; font-size:.8rem;"><?php echo $adminInitial; ?></div>
                <div>
                    <div class="fw-bold" style="font-size:.82rem;line-height:1.2;"><?php echo htmlspecialchars($adminUsername); ?></div>
                    <div class="text-secondary" style="font-size:.72rem;">Admin</div>
                </div>
            </div>
        </div>
    </nav>

    <!-- top part -->
    <div id="topbar" class="bg-white border-bottom d-flex align-items-center px-4 sticky-top" style="height:60px;z-index:999;">
        <h5 class="mb-0 fw-bold fs-5">Dashboard</h5>

        <!-- search part -->
        <div class="position-relative ms-3" style="max-width:260px;flex:1;">
            <i class="bi bi-search text-secondary search-icon"></i>
            <input type="text" class="form-control bg-light border search-input" placeholder="Search…"/>
        </div>

        <!-- right part -->
        <div class="ms-auto d-flex align-items-center gap-3">
            <div class="position-relative">
                <i class="bi bi-bell fs-5 text-secondary"></i>
                <span class="position-absolute top-0 start-100 translate-middle badge rounded-pill bg-danger" style="font-size:.6rem;">3</span>
            </div>
            <div class="dropdown">
                <button class="btn btn-link text-dark p-0 d-flex align-items-center gap-2 dropdown-toggle" type="button" data-bs-toggle="dropdown" aria-expanded="false" style="text-decoration: none;">
                    <div class="rounded-circle bg-primary d-flex align-items-center justify-content-center text-white fw-bold flex-shrink-0" style="width:36px;height:36px;font-size:.8rem;"><?php echo $adminInitial; ?></div>
                    <div>
                        <div class="fw-bold" style="font-size:.82rem;line-height:1.1;"><?php echo htmlspecialchars($adminUsername); ?></div>
                        <div class="text-secondary" style="font-size:.72rem;">Admin</div>
                    </div>
                </button>
                <ul class="dropdown-menu dropdown-menu-end">
                    <?php if (isset($_SESSION['role']) && $_SESSION['role'] === 'superadmin'): ?>
                    <li><a class="dropdown-item" href="settings.php"><i class="bi bi-gear me-2"></i>Settings</a></li>
                    <li><hr class="dropdown-divider"></li>
                    <?php endif; ?>
                    <li><a class="dropdown-item text-danger" href="javascript:void(0)" onclick="confirmLogout()"><i class="bi bi-box-arrow-right me-2"></i>Logout</a></li>
                </ul>
            </div>
        </div>
    </div>

    <!-- main part -->
    <div id="main" class="p-4">
        <!-- Message display -->
        <?php if (!empty($message)): ?>
        <div class="alert alert-<?php echo $messageType === 'success' ? 'success' : 'danger'; ?> alert-dismissible fade show" role="alert">
            <?php echo htmlspecialchars($message); ?>
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
        <?php endif; ?>

        <!-- header -->
        <div class="d-flex align-items-start justify-content-between mb-4">
            <div>
                <h4 class="fw-bold mb-1">Products</h4>
                <p class="text-secondary mb-0 products-subtitle">Manage your product catalog</p>
            </div>
            <button class="btn btn-dark d-flex align-items-center gap-2 fw-semibold products-add-btn" data-bs-toggle="modal" data-bs-target="#productModal" onclick="openAddProductModal()">
                <i class="bi bi-plus-lg"></i> Add Product
            </button>
        </div>

        <!-- search and category toggle -->
        <div class="bg-white border rounded-3 p-3 mb-3">
            <div class="d-flex align-items-center gap-2">
                <!-- Search -->
                <div class="position-relative flex-fill">
                    <i class="bi bi-search text-secondary search-icon"></i>
                    <input type="text" id="productSearchInput" class="form-control bg-light border search-input w-100"
                           placeholder="Search products…" value="<?php echo htmlspecialchars($_GET['search'] ?? ''); ?>"/>
                </div>
                <select id="categoryFilter" class="form-select products-category-select fw-semibold">
                    <option value="">All Categories</option>
                    <?php foreach ($categories as $category): ?>
                    <option value="<?php echo htmlspecialchars($category['name']); ?>" <?php echo (isset($_GET['category']) && $_GET['category'] === $category['name']) ? 'selected' : ''; ?>>
                        <?php echo htmlspecialchars($category['name']); ?>
                    </option>
                    <?php endforeach; ?>
                </select>
                <div class="btn-group products-view-toggle" role="group">
                    <button id="btnGrid" type="button" class="btn btn-dark products-toggle-btn" onclick="setView('grid')">
                        <i class="bi bi-grid"></i>
                    </button>
                    <button id="btnList" type="button" class="btn btn-outline-secondary products-toggle-btn" onclick="setView('list')">
                        <i class="bi bi-list-ul"></i>
                    </button>
                </div>
            </div>
        </div>
 
        <!-- List view -->
        <div id="listView" class="bg-white border rounded-3 p-3">
            <div class="table-responsive">
                <table class="table products-table table-hover align-middle mb-0">
                    <thead class="fw-bold border-bottom">
                        <tr>
                            <th class="px-3 py-3">Product</th>
                            <th class="px-3 py-3">Category</th>
                            <th class="px-3 py-3">Price</th>
                            <th class="px-3 py-3">Stock</th>
                            <th class="px-3 py-3">Status</th>
                            <th class="px-3 py-3">Actions</th>
                        </tr>
                    </thead>
                    <tbody id="productsTableBody">
                        <?php if (empty($products)): ?>
                        <tr>
                            <td colspan="6" class="text-center py-4">
                                <i class="bi bi-box-seam display-1 text-secondary mb-3 d-block"></i>
                                <h5 class="text-secondary">No products found</h5>
                                <p class="text-muted">Start by adding your first product to the catalog.</p>
                                <button class="btn btn-dark" data-bs-toggle="modal" data-bs-target="#productModal" onclick="openAddProductModal()">
                                    <i class="bi bi-plus-lg me-2"></i>Add Product
                                </button>
                            </td>
                        </tr>
                        <?php else: ?>
                        <?php foreach ($products as $product): ?>
                        <tr class="border-bottom border-light-subtle" data-category="<?php echo htmlspecialchars($product['category_name']); ?>" data-name="<?php echo htmlspecialchars($product['name']); ?>">
                            <td class="px-3 py-3">
                                <div class="d-flex align-items-center gap-3">
                                    <div class="products-img-placeholder rounded-2 bg-light flex-shrink-0">
                                        <?php
                                        $productImage = '';
                                        if (!empty($product['images'])) {
                                            // Use primary image from product_images table
                                            $primaryImage = $product['images'][0];
                                            $productImage = normalizeAdminProductImagePath($primaryImage['image_path'] ?? '');
                                        } elseif (!empty($product['image'])) {
                                            // Fallback to old image column
                                            $productImage = normalizeAdminProductImagePath($product['image']);
                                        }

                                        if ($productImage) {
                                            echo '<img src="' . htmlspecialchars($productImage) . '" class="w-100 h-100 object-fit-cover rounded-2" alt="' . htmlspecialchars($product['name']) . '" onerror="this.style.display=\'none\'">';
                                        }
                                        ?>
                                    </div>
                                    <span class="fw-semibold"><?php echo htmlspecialchars($product['name']); ?></span>
                                </div>
                            </td>
                            <td class="px-3 py-3 text-secondary"><?php echo htmlspecialchars($product['category_name']); ?></td>
                            <td class="px-3 py-3 fw-semibold">₱<?php echo number_format($product['price'], 2); ?></td>
                            <td class="px-3 py-3"><?php echo htmlspecialchars($product['stock']); ?></td>
                            <td class="px-3 py-3">
                                <?php if ($product['stock'] <= 5): ?>
                                <span class="products-status-badge status-out-of-stock">Low Stock</span>
                                <?php elseif ($product['stock'] == 0): ?>
                                <span class="products-status-badge status-out-of-stock">Out of Stock</span>
                                <?php else: ?>
                                <span class="products-status-badge status-active">Active</span>
                                <?php endif; ?>
                            </td>
                            <td class="px-3 py-3">
                                <a href="javascript:void(0)" class="products-edit-link me-2" onclick="editProduct(<?php echo $product['id']; ?>)">Edit</a>
                                <a href="javascript:void(0)" class="products-delete-link" onclick="deleteProduct(<?php echo $product['id']; ?>, '<?php echo htmlspecialchars($product['name']); ?>')">Delete</a>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>

            <div id="productsEmptyState" class="text-center text-secondary py-5 d-none">
                <i class="bi bi-box-seam orders-empty-icon d-block mb-2"></i>
                No products match your search.
            </div>
        </div>
 
        <!-- Grid view -->
        <div id="gridView" class="d-none">
            <div class="row g-3" id="productsGridBody">
                <?php if (empty($products)): ?>
                <div class="col-12">
                    <div class="text-center py-5">
                        <i class="bi bi-box-seam display-1 text-secondary mb-3"></i>
                        <h5 class="text-secondary">No products found</h5>
                        <p class="text-muted">Start by adding your first product to the catalog.</p>
                        <button class="btn btn-dark" data-bs-toggle="modal" data-bs-target="#productModal" onclick="openAddProductModal()">
                            <i class="bi bi-plus-lg me-2"></i>Add Product
                        </button>
                    </div>
                </div>
                <?php else: ?>
                <?php foreach ($products as $product): ?>
                <div class="col-sm-6 col-md-4 col-lg-3" data-category="<?php echo htmlspecialchars($product['category_name']); ?>" data-name="<?php echo htmlspecialchars($product['name']); ?>">
                    <div class="bg-white border rounded-3 p-3 h-100 products-card">
                        <div class="products-card-img rounded-2 bg-light mb-3 position-relative">
                            <?php
                            $primaryImage = !empty($product['images']) ? $product['images'][0] : null;
                            $imageSrc = null;

                            if ($primaryImage) {
                                $imageSrc = normalizeAdminProductImagePath($primaryImage['image_path'] ?? '');
                            } elseif (!empty($product['image'])) {
                                $imageSrc = normalizeAdminProductImagePath($product['image']);
                            }

                            if ($imageSrc) {
                                echo '<img src="' . htmlspecialchars($imageSrc) . '" class="w-100 h-100 object-fit-cover rounded-2" alt="' . htmlspecialchars($product['name']) . '" onerror="this.style.display=\'none\'">';
                            }
                            ?>
                            <?php if ($product['stock'] <= 5): ?>
                            <div class="position-absolute top-0 end-0 m-2">
                                <span class="badge bg-danger">Low Stock</span>
                            </div>
                            <?php endif; ?>
                        </div>
                        <div class="fw-bold products-card-name mb-1"><?php echo htmlspecialchars($product['name']); ?></div>
                        <div class="text-secondary products-card-category mb-2"><?php echo htmlspecialchars($product['category_name']); ?></div>
                        <div class="d-flex align-items-center justify-content-between">
                            <span class="fw-bold products-card-price">₱<?php echo number_format($product['price'], 2); ?></span>
                            <?php if ($product['stock'] <= 5): ?>
                            <span class="products-status-badge status-out-of-stock">Low Stock</span>
                            <?php elseif ($product['stock'] == 0): ?>
                            <span class="products-status-badge status-out-of-stock">Out of Stock</span>
                            <?php else: ?>
                            <span class="products-status-badge status-active">Active</span>
                            <?php endif; ?>
                        </div>
                        <div class="text-secondary products-card-stock mt-1">Stock: <?php echo htmlspecialchars($product['stock']); ?></div>
                        <div class="d-flex gap-2 mt-3">
                            <a href="javascript:void(0)" class="products-edit-link" onclick="editProduct(<?php echo $product['id']; ?>)">Edit</a>
                            <a href="javascript:void(0)" class="products-delete-link" onclick="deleteProduct(<?php echo $product['id']; ?>, '<?php echo htmlspecialchars($product['name']); ?>')">Delete</a>
                        </div>
                    </div>
                </div>
                <?php endforeach; ?>
                <?php endif; ?>
            </div>

            <div id="gridEmptyState" class="text-center text-secondary py-5 d-none">
                <i class="bi bi-box-seam orders-empty-icon d-block mb-2"></i>
                No products match your search.
            </div>
        </div>
 
    </div>

    <!-- Product Modal -->
    <div class="modal fade" id="productModal" tabindex="-1" aria-labelledby="productModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="productModalLabel">Add Product</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <form id="productForm" enctype="multipart/form-data">
                    <div class="modal-body">
                        <input type="hidden" id="productId" name="id">
                        <input type="hidden" name="action" id="formAction" value="add_product">

                        <div class="row">
                            <div class="col-md-8">
                                <div class="mb-3">
                                    <label for="productName" class="form-label">Product Name *</label>
                                    <input type="text" class="form-control" id="productName" name="name" required>
                                </div>

                                <div class="mb-3">
                                    <label for="productDescription" class="form-label">Description *</label>
                                    <textarea class="form-control" id="productDescription" name="description" rows="3" required></textarea>
                                </div>

                                <div class="row">
                                    <div class="col-md-6">
                                        <div class="mb-3">
                                            <label for="productPrice" class="form-label">Price *</label>
                                            <div class="input-group">
                                                <span class="input-group-text">₱</span>
                                                <input type="number" class="form-control" id="productPrice" name="price" step="0.01" min="0" required>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="col-md-6">
                                        <div class="mb-3">
                                            <label for="productStock" class="form-label">Stock *</label>
                                            <input type="number" class="form-control" id="productStock" name="stock" min="0" required>
                                        </div>
                                    </div>
                                </div>

                                <div class="row">
                                    <div class="col-md-6">
                                        <div class="mb-3">
                                            <label for="productCategory" class="form-label">Category *</label>
                                            <select class="form-select" id="productCategory" name="category_id" required>
                                                <option value="">Select Category</option>
                                                <?php foreach ($categories as $category): ?>
                                                <option value="<?php echo htmlspecialchars($category['id']); ?>"><?php echo htmlspecialchars($category['name']); ?></option>
                                                <?php endforeach; ?>
                                            </select>
                                        </div>
                                    </div>
                                    <div class="col-md-3">
                                        <div class="mb-3">
                                            <label for="productColor" class="form-label">Color</label>
                                            <input type="text" class="form-control" id="productColor" name="color">
                                        </div>
                                    </div>
                                    <div class="col-md-3">
                                        <div class="mb-3">
                                            <label for="productSize" class="form-label">Size</label>
                                            <input type="text" class="form-control" id="productSize" name="size">
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <div class="col-md-4">
                                <div class="mb-3">
                                    <label class="form-label">Product Images</label>
                                    <input type="file" class="form-control" id="productImages" name="images[]" multiple accept="image/*">
                                    <div class="form-text">Select multiple images (max 5)</div>
                                </div>

                                <div id="imagePreview" class="mb-3">
                                    <!-- Image previews will be added here -->
                                </div>

                                <div id="existingImages" class="mb-3 d-none">
                                    <label class="form-label">Current Images</label>
                                    <div id="existingImagesContainer" class="sortable-images">
                                        <!-- Existing images will be loaded here -->
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                        <button type="submit" class="btn btn-primary" id="saveProductBtn">Save Product</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- Delete Confirmation Modal -->
    <div class="modal fade" id="deleteModal" tabindex="-1" aria-labelledby="deleteModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content">
                <div class="modal-header border-bottom bg-danger bg-opacity-10">
                    <h5 class="modal-title fw-bold text-danger" id="deleteModalLabel">
                        <i class="bi bi-exclamation-triangle me-2"></i>Delete Product
                    </h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <p class="mb-2">Are you sure you want to delete "<span id="deleteProductName"></span>"?</p>
                    <p class="text-secondary small mb-0">This action cannot be undone.</p>
                </div>
                <div class="modal-footer border-top">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="button" class="btn btn-danger" id="confirmDeleteBtn">
                        <i class="bi bi-trash me-2"></i>Delete Product
                    </button>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/js/bootstrap.bundle.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/sortablejs@1.15.0/Sortable.min.js"></script>
    <script>
        let currentProductId = null;

        // Initialize when document is ready
        document.addEventListener('DOMContentLoaded', function() {
            initializeEventListeners();
            updateViewFromURL();
        });

        function initializeEventListeners() {
            // Search functionality
            const searchInput = document.getElementById('productSearchInput');
            if (searchInput) {
                searchInput.addEventListener('input', filterProducts);
                filterProducts();
            }

            // Category filter
            const categoryFilter = document.getElementById('categoryFilter');
            if (categoryFilter) {
                categoryFilter.addEventListener('change', function() {
                    const category = this.value;
                    const url = new URL(window.location);
                    if (category) {
                        url.searchParams.set('category', category);
                    } else {
                        url.searchParams.delete('category');
                    }
                    url.searchParams.delete('search'); // Clear search when changing category
                    window.location.href = url.toString();
                });
            }

            // Product form submission
            document.getElementById('productForm').addEventListener('submit', handleProductSubmit);

            // Image preview
            document.getElementById('productImages').addEventListener('change', previewImages);

            // Delete confirmation
            document.getElementById('confirmDeleteBtn').addEventListener('click', confirmDelete);
        }

        function updateViewFromURL() {
            const urlParams = new URLSearchParams(window.location.search);
            const view = urlParams.get('view') || 'grid';
            setView(view);
        }

        function setView(view) {
            const listView = document.getElementById('listView');
            const gridView = document.getElementById('gridView');
            const btnGrid = document.getElementById('btnGrid');
            const btnList = document.getElementById('btnList');

            if (view === 'grid') {
                listView.classList.add('d-none');
                gridView.classList.remove('d-none');
                btnGrid.classList.add('btn-dark');
                btnGrid.classList.remove('btn-outline-secondary');
                btnList.classList.remove('btn-dark');
                btnList.classList.add('btn-outline-secondary');
            } else {
                gridView.classList.add('d-none');
                listView.classList.remove('d-none');
                btnList.classList.add('btn-dark');
                btnList.classList.remove('btn-outline-secondary');
                btnGrid.classList.remove('btn-dark');
                btnGrid.classList.add('btn-outline-secondary');
            }

            // Update URL
            const url = new URL(window.location);
            url.searchParams.set('view', view);
            window.history.replaceState({}, '', url.toString());
        }

        function filterProducts() {
            const searchInput = document.getElementById('productSearchInput');
            if (!searchInput) return;

            const searchTerm = searchInput.value.toLowerCase().trim();
            const url = new URL(window.location);
            if (searchTerm) {
                url.searchParams.set('search', searchTerm);
            } else {
                url.searchParams.delete('search');
            }
            window.history.replaceState({}, '', url.toString());

            const listRows = document.querySelectorAll('#productsTableBody tr[data-name]');
            const gridItems = document.querySelectorAll('#productsGridBody [data-name]');
            let listVisible = 0;
            let gridVisible = 0;

            listRows.forEach(row => {
                const name = (row.dataset.name || '').toLowerCase();
                const category = (row.dataset.category || '').toLowerCase();
                const match = searchTerm === '' || name.includes(searchTerm) || category.includes(searchTerm);
                row.classList.toggle('d-none', !match);
                if (match) listVisible++;
            });

            gridItems.forEach(item => {
                const name = (item.dataset.name || '').toLowerCase();
                const category = (item.dataset.category || '').toLowerCase();
                const match = searchTerm === '' || name.includes(searchTerm) || category.includes(searchTerm);
                item.classList.toggle('d-none', !match);
                if (match) gridVisible++;
            });

            const listViewActive = !document.getElementById('listView').classList.contains('d-none');
            const gridViewActive = !document.getElementById('gridView').classList.contains('d-none');
            const listEmpty = document.getElementById('productsEmptyState');
            const gridEmpty = document.getElementById('gridEmptyState');

            if (listEmpty) {
                listEmpty.classList.toggle('d-none', listVisible !== 0 || !listViewActive);
            }
            if (gridEmpty) {
                gridEmpty.classList.toggle('d-none', gridVisible !== 0 || !gridViewActive);
            }
        }

        function openAddProductModal() {
            document.getElementById('productModalLabel').textContent = 'Add Product';
            document.getElementById('formAction').value = 'add_product';
            document.getElementById('productId').value = '';
            document.getElementById('productForm').reset();
            document.getElementById('imagePreview').innerHTML = '';
            document.getElementById('existingImages').classList.add('d-none');
            currentProductId = null;
        }

        function editProduct(productId) {
            currentProductId = productId;
            document.getElementById('productModalLabel').textContent = 'Edit Product';
            document.getElementById('formAction').value = 'edit_product';
            document.getElementById('productId').value = productId;

            // Load product data (this would typically be done via AJAX)
            fetch(`product.php?action=get_product&id=${productId}`)
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        const product = data.product;
                        document.getElementById('productName').value = product.name;
                        document.getElementById('productDescription').value = product.description;
                        document.getElementById('productPrice').value = product.price;
                        document.getElementById('productStock').value = product.stock;
                        document.getElementById('productCategory').value = product.category_id;
                        document.getElementById('productColor').value = product.color || '';
                        document.getElementById('productSize').value = product.size || '';

                        // Load existing images
                        if (product.images && product.images.length > 0) {
                            loadExistingImages(product.images);
                        }

                        const modal = new bootstrap.Modal(document.getElementById('productModal'));
                        modal.show();
                    }
                })
                .catch(error => console.error('Error loading product:', error));
        }

        function loadExistingImages(images) {
            const container = document.getElementById('existingImagesContainer');
            container.innerHTML = '';

            images.forEach(image => {
                const previewSrc = image.image_preview || `../${image.image_path}`;
                const imageDiv = document.createElement('div');
                imageDiv.className = 'image-preview';
                imageDiv.innerHTML = `
                    <img src="${previewSrc}" alt="Product image">
                    <button type="button" class="delete-image" onclick="deleteImage(${image.id})">&times;</button>
                `;
                container.appendChild(imageDiv);
            });

            document.getElementById('existingImages').classList.remove('d-none');

            // Initialize sortable
            new Sortable(container, {
                animation: 150,
                onEnd: function(evt) {
                    updateImageOrder();
                }
            });
        }

        function deleteImage(imageId) {
            if (confirm('Are you sure you want to delete this image?')) {
                const formData = new FormData();
                formData.append('action', 'delete_image');
                formData.append('image_id', imageId);

                fetch('product.php', {
                    method: 'POST',
                    body: formData
                })
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        location.reload();
                    } else {
                        alert('Error deleting image: ' + data.message);
                    }
                })
                .catch(error => console.error('Error:', error));
            }
        }

        function updateImageOrder() {
            const images = document.querySelectorAll('#existingImagesContainer .image-preview');
            const imageOrders = {};

            images.forEach((img, index) => {
                const imageId = img.querySelector('button').getAttribute('onclick').match(/deleteImage\((\d+)\)/)[1];
                imageOrders[imageId] = index;
            });

            const formData = new FormData();
            formData.append('action', 'reorder_images');
            formData.append('image_orders', JSON.stringify(imageOrders));

            fetch('product.php', {
                method: 'POST',
                body: formData
            })
            .then(response => response.json())
            .then(data => {
                if (!data.success) {
                    console.error('Error reordering images:', data.message);
                }
            })
            .catch(error => console.error('Error:', error));
        }

        function previewImages() {
            const files = document.getElementById('productImages').files;
            const previewContainer = document.getElementById('imagePreview');
            previewContainer.innerHTML = '';

            Array.from(files).forEach(file => {
                if (file.type.startsWith('image/')) {
                    const reader = new FileReader();
                    reader.onload = function(e) {
                        const imageDiv = document.createElement('div');
                        imageDiv.className = 'image-preview';
                        imageDiv.innerHTML = `<img src="${e.target.result}" alt="Preview">`;
                        previewContainer.appendChild(imageDiv);
                    };
                    reader.readAsDataURL(file);
                }
            });
        }

        function handleProductSubmit(e) {
            e.preventDefault();

            const formData = new FormData(this);
            const submitBtn = document.getElementById('saveProductBtn');
            const originalText = submitBtn.textContent;

            submitBtn.disabled = true;
            submitBtn.textContent = 'Saving...';

            fetch('product.php', {
                method: 'POST',
                body: formData
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    location.reload();
                } else {
                    alert('Error: ' + data.message);
                    submitBtn.disabled = false;
                    submitBtn.textContent = originalText;
                }
            })
            .catch(error => {
                console.error('Error:', error);
                alert('An error occurred while saving the product.');
                submitBtn.disabled = false;
                submitBtn.textContent = originalText;
            });
        }

        function deleteProduct(productId, productName) {
            currentProductId = productId;
            document.getElementById('deleteProductName').textContent = productName;
            const modal = new bootstrap.Modal(document.getElementById('deleteModal'));
            modal.show();
        }

        function confirmDelete() {
            const formData = new FormData();
            formData.append('action', 'delete_product');
            formData.append('id', currentProductId);

            fetch('product.php', {
                method: 'POST',
                body: formData
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    location.reload();
                } else {
                    alert('Error deleting product: ' + data.message);
                }
            })
            .catch(error => console.error('Error:', error));
        }

        function confirmLogout() {
            const modal = new bootstrap.Modal(document.getElementById('logoutModal'));
            modal.show();
        }
    </script>

    <!-- Logout Confirmation Modal -->
    <div class="modal fade" id="logoutModal" tabindex="-1" aria-labelledby="logoutModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content">
                <div class="modal-header border-bottom bg-danger bg-opacity-10">
                    <h5 class="modal-title fw-bold text-danger" id="logoutModalLabel">
                        <i class="bi bi-exclamation-triangle me-2"></i>Confirm Logout
                    </h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <p class="mb-2">Are you sure you want to log out?</p>
                    <p class="text-secondary small mb-0">You will be redirected to the login page. Any unsaved changes will be lost.</p>
                </div>
                <div class="modal-footer border-top">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <a href="../db/action/logout.php" class="btn btn-danger">
                        <i class="bi bi-box-arrow-right me-2"></i>Logout
                    </a>
                </div>
            </div>
        </div>
    </div>
</body>
</html>