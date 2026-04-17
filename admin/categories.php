<?php
require_once '../db/action/dbconfig.php';

// Start session to get admin info
session_start();

// Get current admin info
$adminUsername = isset($_SESSION['username']) ? $_SESSION['username'] : 'Admin';
$adminInitial = strtoupper(substr($adminUsername, 0, 1));

$message = '';
$messageType = '';

function isAjaxRequest() {
    $requestedWith = $_SERVER['HTTP_X_REQUESTED_WITH'] ?? '';
    $accept = $_SERVER['HTTP_ACCEPT'] ?? '';
    return strtolower($requestedWith) === 'xmlhttprequest' || strpos($accept, 'application/json') !== false;
}

function sendJson($success, $message) {
    header('Content-Type: application/json; charset=utf-8');
    echo json_encode([
        'success' => $success,
        'message' => $message,
    ]);
    exit;
}

function normalizeCategoryImagePath($rawPath) {
    $path = trim((string) $rawPath);
    if ($path === '') {
        return '';
    }

    $path = str_replace('\\', '/', $path);
    if (preg_match('#^(https?:)?//#i', $path) || strpos($path, 'data:') === 0) {
        return $path;
    }

    if (strpos($path, 'images/') === 0 || strpos($path, 'assets2/') === 0) {
        return '../' . $path;
    }

    return '../images/products/' . basename($path);
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action'])) {
    $action = $_POST['action'];
    $isAjax = isAjaxRequest();

    if ($action === 'add_category') {
        $name = trim($_POST['name']);
        $description = trim($_POST['description']);

        if (empty($name)) {
            $message = 'Category name is required.';
            $messageType = 'error';
            if ($isAjax) {
                sendJson(false, $message);
            }
        } else {
            try {
                $stmt = $conn->prepare('INSERT INTO categories (name, description) VALUES (?, ?)');
                $stmt->execute([$name, $description]);
                $message = 'Category added successfully.';
                $messageType = 'success';
                if ($isAjax) {
                    sendJson(true, $message);
                }
            } catch (PDOException $e) {
                if ($e->getCode() === '23000') {
                    $message = 'Category name already exists.';
                } else {
                    $message = 'Error adding category: ' . $e->getMessage();
                }
                $messageType = 'error';
                if ($isAjax) {
                    sendJson(false, $message);
                }
            }
        }
    }

    if ($action === 'edit_category') {
        $categoryId = intval($_POST['category_id']);
        $name = trim($_POST['name']);
        $description = trim($_POST['description']);

        if (empty($name)) {
            $message = 'Category name is required.';
            $messageType = 'error';
            if ($isAjax) {
                sendJson(false, $message);
            }
        } else {
            try {
                $stmt = $conn->prepare('UPDATE categories SET name = ?, description = ? WHERE id = ?');
                $stmt->execute([$name, $description, $categoryId]);
                $message = 'Category updated successfully.';
                $messageType = 'success';
                if ($isAjax) {
                    sendJson(true, $message);
                }
            } catch (PDOException $e) {
                if ($e->getCode() === '23000') {
                    $message = 'Category name already exists.';
                } else {
                    $message = 'Error updating category: ' . $e->getMessage();
                }
                $messageType = 'error';
                if ($isAjax) {
                    sendJson(false, $message);
                }
            }
        }
    }

    if ($action === 'delete_category') {
        $categoryId = intval($_POST['category_id']);

        try {
            $stmt = $conn->prepare('DELETE FROM categories WHERE id = ?');
            $stmt->execute([$categoryId]);
            $message = 'Category deleted successfully.';
            $messageType = 'success';
            if ($isAjax) {
                sendJson(true, $message);
            }
        } catch (PDOException $e) {
            $message = 'Error deleting category: ' . $e->getMessage();
            $messageType = 'error';
            if ($isAjax) {
                sendJson(false, $message);
            }
        }
    }
}

$stmt = $conn->prepare("SELECT
    c.id,
    c.name,
    c.description,
    COUNT(p.id) AS product_count,
    (
        SELECT p3.image
        FROM products p3
        WHERE p3.category_id = c.id AND p3.image IS NOT NULL AND TRIM(p3.image) <> ''
        ORDER BY p3.created_at DESC, p3.id DESC
        LIMIT 1
    ) AS latest_image
FROM categories c
LEFT JOIN products p ON p.category_id = c.id
GROUP BY c.id, c.name, c.description
ORDER BY c.name ASC");
$stmt->execute();
$categories = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Categories</title>
    <link rel="icon" type="image/png" href="../assets2/logo.png">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.1/font/bootstrap-icons.css">
    <link rel="stylesheet" href="admin.css">
    <style>
        .products-img-placeholder {
            width: 56px;
            height: 56px;
        }

        .products-img-placeholder img {
            display: block;
            width: 100%;
            height: 100%;
            object-fit: cover;
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
                <a href="product.php" class="nav-link d-flex align-items-center gap-2 fw-semibold rounded text-secondary mx-2 my-1 px-3 py-2">
                    <i class="bi bi-box-seam-fill"></i>
                    Products
                </a>
            </li>
            <li class="nav-item">
                <a href="categories.php" class="nav-link active d-flex align-items-center gap-2 fw-semibold rounded mx-2 my-1 px-3 py-2">
                    <i class="bi bi-folder"></i>
                    Categories
                </a>
            </li>
            <li class="nav-item">
                <a href ="customer.php" class="nav-link d-flex align-items-center gap-2 fw-semibold rounded text-secondary mx-2 my-1 px-3 py-2">
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
 
        <!-- header -->
        <div class="d-flex align-items-start justify-content-between mb-4">
            <div>
                <h4 class="fw-bold mb-1">Categories</h4>
                <p class="text-secondary mb-0 products-subtitle">Manage your product categories</p>
            </div>
            <button class="btn btn-dark d-flex align-items-center gap-2 fw-semibold" data-bs-toggle="modal" data-bs-target="#categoryModal" onclick="openAddCategoryModal()">
                <i class="bi bi-plus-lg"></i> Add Category
            </button>
        </div>

        <div id="categoryAlertContainer"></div>
 
        <?php if (!empty($message)): ?>
        <div class="alert alert-<?php echo $messageType === 'success' ? 'success' : 'danger'; ?> alert-dismissible fade show" role="alert">
            <?php echo htmlspecialchars($message); ?>
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
        <?php endif; ?>
 
        <!-- search -->
        <div class="bg-white border rounded-3 p-3 mb-3">
            <div class="d-flex align-items-center gap-2">
                <!-- Search -->
                <div class="position-relative flex-fill">
                    <i class="bi bi-search text-secondary search-icon"></i>
                    <input type="text" id="categorySearch" class="form-control bg-light border search-input w-100"
                           placeholder="Search categories…"/>
                </div>
            </div>
        </div>
 
        <!-- List view -->
        <div id="listView" class="bg-white border rounded-3 p-3">
            <div class="table-responsive">
                <table class="table products-table table-hover align-middle mb-0">
                    <thead class="fw-bold border-bottom">
                        <tr>
                            <th class="px-3 py-3">Category Name</th>
                            <th class="px-3 py-3">Description</th>
                            <th class="px-3 py-3">Products</th>
                            <th class="px-3 py-3">Status</th>
                            <th class="px-3 py-3">Actions</th>
                        </tr>
                    </thead>
                    <tbody id="categoriesTableBody">
                        <?php if (empty($categories)): ?>
                        <tr>
                            <td colspan="5" class="text-center py-4">
                                <i class="bi bi-folder display-1 text-secondary mb-3 d-block"></i>
                                <h5 class="text-secondary">No categories found</h5>
                                <p class="text-muted">Add a new category to get started.</p>
                            </td>
                        </tr>
                        <?php else: ?>
                        <?php foreach ($categories as $category): ?>
                        <?php $categoryImage = normalizeCategoryImagePath($category['latest_image'] ?? ''); ?>
                        <tr class="border-bottom border-light-subtle" data-category-name="<?php echo htmlspecialchars($category['name']); ?>">
                            <td class="px-3 py-3">
                                <div class="d-flex align-items-center gap-3">
                                    <div class="products-img-placeholder rounded-2 bg-light flex-shrink-0 overflow-hidden d-flex align-items-center justify-content-center">
                                        <?php if (!empty($categoryImage)): ?>
                                        <img src="<?php echo htmlspecialchars($categoryImage); ?>" class="w-100 h-100 object-fit-cover" alt="<?php echo htmlspecialchars($category['name']); ?>" onerror="this.style.display='none'">
                                        <?php endif; ?>
                                    </div>
                                    <span class="fw-semibold"><?php echo htmlspecialchars($category['name']); ?></span>
                                </div>
                            </td>
                            <td class="px-3 py-3 text-secondary"><?php echo htmlspecialchars($category['description'] ?? ''); ?></td>
                            <td class="px-3 py-3 fw-semibold"><?php echo htmlspecialchars($category['product_count']); ?></td>
                            <td class="px-3 py-3">
                                <span class="products-status-badge status-active">Active</span>
                            </td>
                            <td class="px-3 py-3">
                                <a href="javascript:void(0)" class="products-edit-link me-2" onclick="editCategory(<?php echo $category['id']; ?>, '<?php echo htmlspecialchars(addslashes($category['name'])); ?>', '<?php echo htmlspecialchars(addslashes($category['description'] ?? '')); ?>')">Edit</a>
                                <a href="javascript:void(0)" class="products-delete-link" onclick="deleteCategory(<?php echo $category['id']; ?>, '<?php echo htmlspecialchars(addslashes($category['name'])); ?>')">Delete</a>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
 
            <div id="categoriesEmptyState" class="text-center text-secondary py-5 d-none">
                <i class="bi bi-folder orders-empty-icon d-block mb-2"></i>
                No categories match your search.
            </div>
        </div>
 
        <!-- Grid view -->
        <div id="gridView" class="d-none">
            <div class="row g-3" id="categoriesGridBody">
                <!-- Grid items would be here if view toggles were enabled -->
            </div>
 
            <div id="gridEmptyState" class="text-center text-secondary py-5 d-none">
                <i class="bi bi-folder orders-empty-icon d-block mb-2"></i>
                No categories match your search.
            </div>
        </div>
 
    </div>

    <!-- Category Modal -->
    <div class="modal fade" id="categoryModal" tabindex="-1" aria-labelledby="categoryModalLabel" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="categoryModalLabel">Add Category</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <form id="categoryForm" method="POST">
                    <div class="modal-body">
                        <input type="hidden" id="categoryId" name="category_id" value="">
                        <input type="hidden" id="categoryAction" name="action" value="add_category">

                        <div class="mb-3">
                            <label for="categoryName" class="form-label">Category Name</label>
                            <input type="text" class="form-control" id="categoryName" name="name" required>
                        </div>
                        <div class="mb-3">
                            <label for="categoryDescription" class="form-label">Description</label>
                            <textarea class="form-control" id="categoryDescription" name="description" rows="3"></textarea>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                        <button type="submit" class="btn btn-primary">Save Category</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- Delete Category Modal -->
    <div class="modal fade" id="deleteCategoryModal" tabindex="-1" aria-labelledby="deleteCategoryModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content">
                <div class="modal-header border-bottom bg-danger bg-opacity-10">
                    <h5 class="modal-title fw-bold text-danger" id="deleteCategoryModalLabel">
                        <i class="bi bi-exclamation-triangle me-2"></i>Delete Category
                    </h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <form id="deleteCategoryForm" method="POST">
                    <div class="modal-body">
                        <input type="hidden" id="deleteCategoryId" name="category_id" value="">
                        <input type="hidden" name="action" value="delete_category">
                        <p class="mb-2">Are you sure you want to delete the category "<strong id="deleteCategoryName"></strong>"?</p>
                        <p class="text-secondary small mb-0">Deleting this category will not delete the products, but those products will lose the assigned category.</p>
                    </div>
                    <div class="modal-footer border-top">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                        <button type="submit" class="btn btn-danger">
                            <i class="bi bi-trash me-2"></i>Delete Category
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        function showCategoryAlert(message, isSuccess) {
            const container = document.getElementById('categoryAlertContainer');
            if (!container) {
                alert(message);
                return;
            }

            container.innerHTML = `
                <div class="alert alert-${isSuccess ? 'success' : 'danger'} alert-dismissible fade show" role="alert">
                    ${message}
                    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                </div>
            `;
        }

        function postCategoryForm(formData) {
            return fetch('categories.php', {
                method: 'POST',
                headers: {
                    'X-Requested-With': 'XMLHttpRequest',
                    'Accept': 'application/json'
                },
                body: formData
            }).then(response => {
                if (!response.ok) {
                    throw new Error('Request failed with status ' + response.status);
                }
                return response.json();
            });
        }

        function openAddCategoryModal() {
            document.getElementById('categoryModalLabel').textContent = 'Add Category';
            document.getElementById('categoryAction').value = 'add_category';
            document.getElementById('categoryId').value = '';
            document.getElementById('categoryName').value = '';
            document.getElementById('categoryDescription').value = '';
        }

        function editCategory(id, name, description) {
            document.getElementById('categoryModalLabel').textContent = 'Edit Category';
            document.getElementById('categoryAction').value = 'edit_category';
            document.getElementById('categoryId').value = id;
            document.getElementById('categoryName').value = name;
            document.getElementById('categoryDescription').value = description;
            const modal = new bootstrap.Modal(document.getElementById('categoryModal'));
            modal.show();
        }

        function deleteCategory(id, name) {
            document.getElementById('deleteCategoryId').value = id;
            document.getElementById('deleteCategoryName').textContent = name;
            const modal = new bootstrap.Modal(document.getElementById('deleteCategoryModal'));
            modal.show();
        }

        document.getElementById('categorySearch').addEventListener('input', function() {
            const filter = this.value.toLowerCase();
            document.querySelectorAll('#categoriesTableBody tr').forEach(function(row) {
                const name = row.getAttribute('data-category-name') || '';
                row.style.display = name.toLowerCase().includes(filter) ? '' : 'none';
            });
        });

        document.getElementById('categoryForm').addEventListener('submit', function(e) {
            e.preventDefault();

            const submitBtn = this.querySelector('button[type="submit"]');
            const originalText = submitBtn.textContent;
            submitBtn.disabled = true;
            submitBtn.textContent = 'Saving...';

            postCategoryForm(new FormData(this))
                .then(data => {
                    showCategoryAlert(data.message, data.success);
                    if (data.success) {
                        const modalEl = document.getElementById('categoryModal');
                        const modal = bootstrap.Modal.getInstance(modalEl);
                        if (modal) {
                            modal.hide();
                        }
                        setTimeout(() => {
                            window.location.reload();
                        }, 350);
                    }
                })
                .catch(error => {
                    showCategoryAlert('Error saving category: ' + error.message, false);
                })
                .finally(() => {
                    submitBtn.disabled = false;
                    submitBtn.textContent = originalText;
                });
        });

        document.getElementById('deleteCategoryForm').addEventListener('submit', function(e) {
            e.preventDefault();

            const submitBtn = this.querySelector('button[type="submit"]');
            const originalText = submitBtn.textContent;
            submitBtn.disabled = true;
            submitBtn.textContent = 'Deleting...';

            postCategoryForm(new FormData(this))
                .then(data => {
                    showCategoryAlert(data.message, data.success);
                    if (data.success) {
                        const modalEl = document.getElementById('deleteCategoryModal');
                        const modal = bootstrap.Modal.getInstance(modalEl);
                        if (modal) {
                            modal.hide();
                        }
                        setTimeout(() => {
                            window.location.reload();
                        }, 350);
                    }
                })
                .catch(error => {
                    showCategoryAlert('Error deleting category: ' + error.message, false);
                })
                .finally(() => {
                    submitBtn.disabled = false;
                    submitBtn.textContent = originalText;
                });
        });

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