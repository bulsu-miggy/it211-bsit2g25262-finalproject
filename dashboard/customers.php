<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Annyeong Haven | Customers</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.0/font/bootstrap-icons.css">
</head>
<body style="background-color: #f0f2f9;"> 
    <div class="container-fluid p-0">
        <div class="d-flex">
            
            <nav class="d-flex flex-column flex-shrink-0 p-0 text-white shadow" style="width: 280px; height: 100vh; background-color: #1a237e; position: sticky; top: 0;">
                <div class="p-4 text-center">
                    <div class="d-flex align-items-center justify-content-center gap-2 mb-1">
                        <h5 class="fw-bold mb-0" style="letter-spacing: 1px;">ANNYEONG HAVEN</h5>
                    </div>
                    <small class="opacity-50">Admin Panel</small>
                </div>

                <hr class="mx-3 my-0 opacity-25">
                
                <ul class="nav nav-pills flex-column mb-auto p-3 pt-4">
                    <li class="nav-item mb-2">
                        <a href="dashboard.php" class="nav-link text-white py-3 opacity-75">
                            <i class="bi bi-grid-fill me-3"></i> Dashboard
                        </a>
                    </li>
                    <li class="nav-item mb-2">
                        <a href="orders.php" class="nav-link text-white py-3 opacity-75">
                            <i class="bi bi-cart-fill me-3"></i> Orders
                        </a>
                    </li>
                    <li class="nav-item mb-2">
                        <a href="products.php" class="nav-link text-white py-3 opacity-75">
                            <i class="bi bi-box-seam-fill me-3"></i> Products
                        </a>
                    </li>
                    <li class="nav-item mb-2">
                        <a href="categories.php" class="nav-link text-white py-3 opacity-75">
                            <i class="bi bi-folder-fill me-3"></i> Categories
                        </a>
                    </li>
                    <li class="nav-item mb-2">
                        <a href="customers.php" class="nav-link active bg-white text-dark py-3 shadow-sm rounded-3">
                            <i class="bi bi-people-fill me-3"></i> Customers
                        </a>
                    </li>
                    <li class="nav-item">
                        <a href="analytics.php" class="nav-link text-white py-3 opacity-75">
                            <i class="bi bi-bar-chart-line-fill me-3"></i> Analytics
                        </a>
                    </li>
                </ul>

                <div class="p-3 border-top border-white border-opacity-10 bg-black bg-opacity-10">
                    <div class="d-flex align-items-center p-2 rounded-3">
                        <div class="rounded-circle bg-white d-flex align-items-center justify-content-center shadow-sm" style="width: 45px; height: 45px;">
                            <i class="bi bi-person-fill text-primary fs-4"></i>
                        </div>
                        <div class="ms-3 overflow-hidden">
                            <p class="mb-0 fw-bold small text-truncate text-white">Admin User</p>
                            <span class="text-white-50" style="font-size: 11px;">manager@haven.com</span>
                        </div>
                    </div>
                </div>
            </nav>

            <div class="flex-grow-1">
                
                <div class="bg-white border-bottom px-4 py-3 d-flex justify-content-between align-items-center sticky-top shadow-sm">
                    <h5 class="mb-0 fw-bold text-dark border-start border-4 border-primary ps-3" style="border-color: #1a237e !important;">Customers</h5>
                    <div class="d-flex align-items-center gap-4">
                        <div class="input-group input-group-sm d-none d-md-flex" style="width: 250px;">
                            <span class="input-group-text bg-light border-0"><i class="bi bi-search"></i></span>
                            <input type="text" class="form-control bg-light border-0" placeholder="Search customer...">
                        </div>
                        <div class="position-relative">
                            <i class="bi bi-bell fs-5 text-muted"></i>
                            <span class="position-absolute top-0 start-100 translate-middle badge rounded-circle bg-danger border border-white" style="padding: 4px; font-size: 10px;">3</span>
                        </div>
                        <div class="rounded-circle overflow-hidden border shadow-sm d-flex align-items-center justify-content-center bg-light" 
                             style="width: 40px; height: 40px;">
                            <i class="bi bi-person-circle fs-4 text-secondary"></i>
                        </div>
                    </div>
                </div>

                <div class="p-4">
                    <div class="d-flex justify-content-between align-items-end mb-4">
                        <div>
                            <h2 class="fw-bold mb-1 text-dark">Customers</h2>
                            <p class="text-muted small mb-0">Manage your customer relationships</p>
                        </div>
                        <button class="btn text-white px-4 py-2 rounded-3 shadow-sm d-flex align-items-center gap-2" style="background-color: #1a237e;">
                            <i class="bi bi-download"></i> Export
                        </button>
                    </div>

                    <div class="card border-0 shadow-sm mb-4 rounded-4">
                        <div class="card-body p-3">
                            <div class="input-group border rounded-3 overflow-hidden">
                                <span class="input-group-text bg-white border-0"><i class="bi bi-search text-muted"></i></span>
                                <input type="text" class="form-control border-0 ps-0" placeholder="Search customers...">
                            </div>
                        </div>
                    </div>

                    <div class="card border-0 shadow-sm rounded-4 overflow-hidden">
                        <div class="table-responsive">
                            <table class="table table-hover align-middle mb-0">
                                <thead style="background-color: #f8faff;">
                                    <tr class="text-muted small fw-bold">
                                        <th class="ps-4 py-4">Customer</th>
                                        <th>Email</th>
                                        <th>Orders</th>
                                        <th>Total Spent</th>
                                        <th>Joined</th>
                                        <th class="text-end pe-4">Actions</th>
                                    </tr>
                                </thead>
                                <tbody class="bg-white">
                                    <?php
                                    $customers = [
                                        ['name' => 'Ace', 'email' => 'ace@gmail.com', 'orders' => 12, 'spent' => '₱1,245.00', 'joined' => '2026-01-15'],
                                        ['name' => 'JAndrew', 'email' => 'andrew@gmail.com', 'orders' => 8, 'spent' => '₱890.50', 'joined' => '2026-02-03'],
                                        ['name' => 'Drews', 'email' => 'drews@gmail.com', 'orders' => 23, 'spent' => '₱2,310.00', 'joined' => '2025-11-20'],
                                    
                                    ];

                                    foreach ($customers as $c): ?>
                                    <tr class="border-bottom">
                                        <td class="ps-4">
                                            <div class="d-flex align-items-center gap-3 py-1">
                                                <div class="rounded-circle bg-light border" style="width: 40px; height: 40px;"></div>
                                                <span class="fw-bold text-dark"><?php echo $c['name']; ?></span>
                                            </div>
                                        </td>
                                        <td class="text-muted small"><?php echo $c['email']; ?></td>
                                        <td class="text-muted"><?php echo $c['orders']; ?></td>
                                        <td class="fw-bold text-dark"><?php echo $c['spent']; ?></td>
                                        <td class="text-muted small"><?php echo $c['joined']; ?></td>
                                        <td class="text-end pe-4">
                                            <a href="#" class="text-primary text-decoration-none small fw-bold">View Details</a>
                                        </td>
                                    </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                        </div>
                    </div>

                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>