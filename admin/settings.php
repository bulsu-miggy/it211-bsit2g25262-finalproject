<?php
// Database connection
require_once '../db/action/dbconfig.php';

// Start session to get admin info
session_start();

// Get current admin info
$adminUsername = isset($_SESSION['username']) ? $_SESSION['username'] : 'Admin';
$adminInitial = strtoupper(substr($adminUsername, 0, 1));
$currentUserRole = isset($_SESSION['role']) ? $_SESSION['role'] : '';

// Only superadmin can access this page
if ($currentUserRole !== 'superadmin') {
    header('Location: dashboard.php');
    exit;
}

// Handle AJAX requests FIRST - before any HTML output
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action'])) {
    header('Content-Type: application/json; charset=utf-8');
    $action = $_POST['action'];

    // Add new admin user
    if ($action === 'add_admin') {
        $first_name = trim($_POST['first_name'] ?? '');
        $last_name = trim($_POST['last_name'] ?? '');
        $email = trim($_POST['email'] ?? '');
        $username = trim($_POST['username'] ?? '');
        $password = $_POST['password'] ?? '';

        if (!$first_name || !$last_name || !$email || !$username || !$password) {
            echo json_encode(['success' => false, 'message' => 'All fields are required']);
            exit;
        }

        if (strlen($password) < 6) {
            echo json_encode(['success' => false, 'message' => 'Password must be at least 6 characters']);
            exit;
        }

        try {
            // Check if username or email already exists
            $stmt = $conn->prepare("SELECT id FROM login WHERE username = ? OR email = ?");
            $stmt->execute([$username, $email]);
            if ($stmt->fetch()) {
                echo json_encode(['success' => false, 'message' => 'Username or email already exists']);
                exit;
            }

            // Hash password and insert
            $hashedPassword = password_hash($password, PASSWORD_DEFAULT);
            $stmt = $conn->prepare("INSERT INTO login (first_name, last_name, email, username, password, role) VALUES (?, ?, ?, ?, ?, 'admin')");
            $stmt->execute([$first_name, $last_name, $email, $username, $hashedPassword]);

            echo json_encode(['success' => true, 'message' => 'Admin user added successfully']);
        } catch (Exception $e) {
            echo json_encode(['success' => false, 'message' => 'Error: ' . $e->getMessage()]);
        }
        exit;
    }

    // Update admin user
    if ($action === 'update_admin') {
        $id = intval($_POST['id'] ?? 0);
        $first_name = trim($_POST['first_name'] ?? '');
        $last_name = trim($_POST['last_name'] ?? '');
        $email = trim($_POST['email'] ?? '');
        $password = $_POST['password'] ?? '';

        if ($id === 0 || !$first_name || !$last_name || !$email) {
            echo json_encode(['success' => false, 'message' => 'Invalid input']);
            exit;
        }

        try {
            // Check if email already exists for another user
            $stmt = $conn->prepare("SELECT id FROM login WHERE email = ? AND id != ?");
            $stmt->execute([$email, $id]);
            if ($stmt->fetch()) {
                echo json_encode(['success' => false, 'message' => 'Email already exists']);
                exit;
            }

            if ($password) {
                if (strlen($password) < 6) {
                    echo json_encode(['success' => false, 'message' => 'Password must be at least 6 characters']);
                    exit;
                }
                $hashedPassword = password_hash($password, PASSWORD_DEFAULT);
                $stmt = $conn->prepare("UPDATE login SET first_name = ?, last_name = ?, email = ?, password = ? WHERE id = ? AND role = 'admin'");
                $stmt->execute([$first_name, $last_name, $email, $hashedPassword, $id]);
            } else {
                $stmt = $conn->prepare("UPDATE login SET first_name = ?, last_name = ?, email = ? WHERE id = ? AND role = 'admin'");
                $stmt->execute([$first_name, $last_name, $email, $id]);
            }

            echo json_encode(['success' => true, 'message' => 'Admin user updated successfully']);
        } catch (Exception $e) {
            echo json_encode(['success' => false, 'message' => 'Error: ' . $e->getMessage()]);
        }
        exit;
    }

    // Delete admin user
    if ($action === 'delete_admin') {
        $id = intval($_POST['id'] ?? 0);

        if ($id === 0) {
            echo json_encode(['success' => false, 'message' => 'Invalid ID']);
            exit;
        }

        try {
            $stmt = $conn->prepare("DELETE FROM login WHERE id = ? AND role = 'admin'");
            $stmt->execute([$id]);

            echo json_encode(['success' => true, 'message' => 'Admin user deleted successfully']);
        } catch (Exception $e) {
            echo json_encode(['success' => false, 'message' => 'Error: ' . $e->getMessage()]);
        }
        exit;
    }

    // Get admin details for editing
    if ($action === 'get_admin') {
        $id = intval($_GET['id'] ?? 0);

        if ($id === 0) {
            echo json_encode(['success' => false, 'message' => 'Invalid ID']);
            exit;
        }

        try {
            $stmt = $conn->prepare("SELECT id, first_name, last_name, email, username FROM login WHERE id = ? AND role = 'admin'");
            $stmt->execute([$id]);
            $admin = $stmt->fetch(PDO::FETCH_ASSOC);

            if (!$admin) {
                echo json_encode(['success' => false, 'message' => 'Admin not found']);
                exit;
            }

            echo json_encode(['success' => true, 'admin' => $admin]);
        } catch (Exception $e) {
            echo json_encode(['success' => false, 'message' => 'Error: ' . $e->getMessage()]);
        }
        exit;
    }
}

// Handle GET requests for fetching admin details
if ($_SERVER['REQUEST_METHOD'] === 'GET' && isset($_GET['action']) && $_GET['action'] === 'get_admin') {
    header('Content-Type: application/json; charset=utf-8');
    $id = intval($_GET['id'] ?? 0);

    if ($id === 0) {
        echo json_encode(['success' => false, 'message' => 'Invalid ID']);
        exit;
    }

    try {
        $stmt = $conn->prepare("SELECT id, first_name, last_name, email, username FROM login WHERE id = ? AND role = 'admin'");
        $stmt->execute([$id]);
        $admin = $stmt->fetch(PDO::FETCH_ASSOC);

        if (!$admin) {
            echo json_encode(['success' => false, 'message' => 'Admin not found']);
            exit;
        }

        echo json_encode(['success' => true, 'admin' => $admin]);
    } catch (Exception $e) {
        echo json_encode(['success' => false, 'message' => 'Error: ' . $e->getMessage()]);
    }
    exit;
}

// Fetch all admin users (for page load)
$stmt = $conn->prepare("SELECT id, first_name, last_name, email, username, login_date FROM login WHERE role = 'admin' ORDER BY login_date DESC");
$stmt->execute();
$admins = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Settings - Admin Management</title>
    <link rel="icon" type="image/png" href="../assets2/logo.png">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.1/font/bootstrap-icons.css">
    <link rel="stylesheet" href="admin.css">
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/js/bootstrap.bundle.min.js"></script>
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
                    <i class="bi bi-grid-1x2-fill"></i>
                    Dashboard
                </a>
            </li>
            <li class="nav-item">
                <a href="orders.php" class="nav-link d-flex align-items-center gap-2 fw-semibold rounded text-secondary mx-2 my-1 px-3 py-2">
                    <i class="bi bi-cart"></i>
                    Orders
                </a>
            </li>
            <li class="nav-item">
                <a href="product.php" class="nav-link d-flex align-items-center gap-2 fw-semibold rounded text-secondary mx-2 my-1 px-3 py-2">
                    <i class="bi bi-box-seam"></i>
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
                    <div class="text-secondary" style="font-size:.72rem;">Settings</div>
                </div>
            </div>
        </div>
    </nav>

    <!-- top part -->
    <div id="topbar" class="bg-white border-bottom d-flex align-items-center px-4 sticky-top" style="height:60px;z-index:999;">
        <h5 class="mb-0 fw-bold fs-5">Settings</h5>

        <!-- right part -->
        <div class="ms-auto d-flex align-items-center gap-3">
            <div class="dropdown">
                <button class="btn btn-link text-dark p-0 d-flex align-items-center gap-2 dropdown-toggle" type="button" data-bs-toggle="dropdown" aria-expanded="false" style="text-decoration: none;">
                    <div class="rounded-circle bg-primary d-flex align-items-center justify-content-center text-white fw-bold flex-shrink-0" style="width:36px;height:36px;font-size:.8rem;"><?php echo $adminInitial; ?></div>
                    <div>
                        <div class="fw-bold" style="font-size:.82rem;line-height:1.1;"><?php echo htmlspecialchars($adminUsername); ?></div>
                        <div class="text-secondary" style="font-size:.72rem;">Settings</div>
                    </div>
                </button>
                <ul class="dropdown-menu dropdown-menu-end">
                    <li><a class="dropdown-item" href="settings.php"><i class="bi bi-gear me-2"></i>Settings</a></li>
                    <li><hr class="dropdown-divider"></li>
                    <li><a class="dropdown-item text-danger" href="javascript:void(0)" onclick="confirmLogout()"><i class="bi bi-box-arrow-right me-2"></i>Logout</a></li>
                </ul>
            </div>
        </div>
    </div>

    <!-- main part -->
    <div id="main" class="p-4">
        <div class="d-flex align-items-start justify-content-between mb-4">
            <div>
                <h4 class="fw-bold mb-1">Admin Users Management</h4>
                <p class="text-secondary mb-0">Manage admin accounts and user permissions</p>
            </div>
            <button class="btn btn-dark d-flex align-items-center gap-2 fw-semibold" data-bs-toggle="modal" data-bs-target="#addAdminModal">
                <i class="bi bi-plus-lg"></i>Add Admin User
            </button>
        </div>

        <!-- Admin Users Table -->
        <div class="bg-white border rounded-3 p-3">
            <div class="table-responsive">
                <table class="table products-table table-hover align-middle mb-0">
                    <thead class="fw-bold border-bottom">
                        <tr>
                            <th class="px-3 py-3">Name</th>
                            <th class="px-3 py-3">Email</th>
                            <th class="px-3 py-3">Username</th>
                            <th class="px-3 py-3">Joined</th>
                            <th class="px-3 py-3">Actions</th>
                        </tr>
                    </thead>
                    <tbody id="adminsTableBody">
                        <?php if (!empty($admins)): ?>
                            <?php foreach ($admins as $admin): 
                                $adminName = htmlspecialchars($admin['first_name'] . ' ' . $admin['last_name']);
                                $adminEmail = htmlspecialchars($admin['email']);
                                $adminUsername = htmlspecialchars($admin['username']);
                                $joinedDate = date('Y-m-d', strtotime($admin['login_date']));
                            ?>
                            <tr class="border-bottom border-light-subtle" data-admin-id="<?php echo $admin['id']; ?>">
                                <td class="px-3 py-3">
                                    <div class="d-flex align-items-center gap-3">
                                        <div class="rounded-circle bg-primary d-flex align-items-center justify-content-center text-white fw-bold flex-shrink-0" style="width:40px;height:40px;font-size:.9rem;">
                                            <?php echo strtoupper(substr($admin['first_name'], 0, 1)) . strtoupper(substr($admin['last_name'], 0, 1)); ?>
                                        </div>
                                        <span class="fw-semibold"><?php echo $adminName; ?></span>
                                    </div>
                                </td>
                                <td class="px-3 py-3 text-secondary"><?php echo $adminEmail; ?></td>
                                <td class="px-3 py-3 text-secondary"><?php echo $adminUsername; ?></td>
                                <td class="px-3 py-3 text-secondary"><?php echo $joinedDate; ?></td>
                                <td class="px-3 py-3">
                                    <a href="javascript:void(0)" class="products-edit-link" onclick="editAdmin(<?php echo $admin['id']; ?>)">Edit</a>
                                    <a href="javascript:void(0)" class="products-delete-link ms-3" onclick="deleteAdmin(<?php echo $admin['id']; ?>)">Delete</a>
                                </td>
                            </tr>
                            <?php endforeach; ?>
                        <?php else: ?>
                            <tr class="border-bottom border-light-subtle">
                                <td colspan="5" class="px-3 py-3 text-center text-secondary">
                                    No admin users found
                                </td>
                            </tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <!-- Add/Edit Admin Modal -->
    <div class="modal fade" id="addAdminModal" tabindex="-1" aria-labelledby="addAdminModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content">
                <div class="modal-header border-bottom">
                    <h5 class="modal-title fw-bold" id="addAdminModalLabel">Add Admin User</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <form id="adminForm">
                        <input type="hidden" id="adminId" value="">
                        
                        <div class="mb-3">
                            <label for="firstName" class="form-label fw-semibold">First Name</label>
                            <input type="text" class="form-control" id="firstName" placeholder="Enter first name" required>
                        </div>

                        <div class="mb-3">
                            <label for="lastName" class="form-label fw-semibold">Last Name</label>
                            <input type="text" class="form-control" id="lastName" placeholder="Enter last name" required>
                        </div>

                        <div class="mb-3">
                            <label for="email" class="form-label fw-semibold">Email</label>
                            <input type="email" class="form-control" id="email" placeholder="Enter email" required>
                        </div>

                        <div class="mb-3" id="usernameGroup">
                            <label for="username" class="form-label fw-semibold">Username</label>
                            <input type="text" class="form-control" id="username" placeholder="Enter username" required>
                        </div>

                        <div class="mb-3">
                            <label for="password" class="form-label fw-semibold">Password <span id="passwordNote" class="text-secondary small">(Leave blank to keep current)</span></label>
                            <input type="password" class="form-control" id="password" placeholder="Enter password" required>
                        </div>

                        <div class="alert alert-info d-none" id="alertMessage" role="alert"></div>

                        <div class="d-flex gap-2">
                            <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                            <button type="submit" class="btn btn-primary ms-auto">Save Admin User</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>

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

    <script src="admin.js"></script>
    <script>
        // Admin user management functions
        function editAdmin(adminId) {
            fetch(`settings.php?action=get_admin&id=${adminId}`)
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        const admin = data.admin;
                        document.getElementById('adminId').value = admin.id;
                        document.getElementById('firstName').value = admin.first_name;
                        document.getElementById('lastName').value = admin.last_name;
                        document.getElementById('email').value = admin.email;
                        document.getElementById('username').value = admin.username;
                        document.getElementById('password').value = '';
                        document.getElementById('username').disabled = true;
                        document.getElementById('usernameGroup').style.display = 'none';
                        document.getElementById('passwordNote').innerHTML = '(Leave blank to keep current)';
                        document.getElementById('password').required = false;
                        document.getElementById('addAdminModalLabel').textContent = 'Edit Admin User';
                        const modal = new bootstrap.Modal(document.getElementById('addAdminModal'));
                        modal.show();
                    }
                })
                .catch(error => console.error('Error:', error));
        }

        function deleteAdmin(adminId) {
            if (confirm('Are you sure you want to delete this admin user?')) {
                const formData = new FormData();
                formData.append('action', 'delete_admin');
                formData.append('id', adminId);

                fetch('settings.php', {
                    method: 'POST',
                    body: formData
                })
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        alert('Admin user deleted successfully');
                        location.reload();
                    } else {
                        alert('Error: ' + data.message);
                    }
                })
                .catch(error => console.error('Error:', error));
            }
        }

        function confirmLogout() {
            const modal = new bootstrap.Modal(document.getElementById('logoutModal'));
            modal.show();
        }

        // Form submission
        document.getElementById('adminForm').addEventListener('submit', function(e) {
            e.preventDefault();

            const adminId = document.getElementById('adminId').value;
            const isEdit = adminId !== '';

            const formData = new FormData();
            formData.append('action', isEdit ? 'update_admin' : 'add_admin');
            formData.append('first_name', document.getElementById('firstName').value);
            formData.append('last_name', document.getElementById('lastName').value);
            formData.append('email', document.getElementById('email').value);
            
            if (!isEdit) {
                formData.append('username', document.getElementById('username').value);
            }
            
            formData.append('password', document.getElementById('password').value);
            
            if (isEdit) {
                formData.append('id', adminId);
            }

            fetch('settings.php', {
                method: 'POST',
                body: formData
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    alert(data.message);
                    location.reload();
                } else {
                    alert('Error: ' + data.message);
                }
            })
            .catch(error => console.error('Error:', error));
        });

        // Reset modal on close
        document.getElementById('addAdminModal').addEventListener('hidden.bs.modal', function() {
            document.getElementById('adminForm').reset();
            document.getElementById('adminId').value = '';
            document.getElementById('username').disabled = false;
            document.getElementById('usernameGroup').style.display = 'block';
            document.getElementById('addAdminModalLabel').textContent = 'Add Admin User';
            document.getElementById('password').required = true;
            document.getElementById('passwordNote').innerHTML = '';
        });
    </script>
</body>
</html>
