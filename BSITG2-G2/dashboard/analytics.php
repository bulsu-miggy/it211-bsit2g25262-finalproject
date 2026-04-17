<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Annyeong Haven | Analytics</title>
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
                        <a href="customers.php" class="nav-link text-white py-3 opacity-75">
                            <i class="bi bi-people-fill me-3"></i> Customers
                        </a>
                    </li>
                    <li class="nav-item">
                        <a href="analytics.php" class="nav-link active bg-white text-dark py-3 shadow-sm rounded-3">
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
                
                <div class="bg-white border-bottom px-4 py-3 d-flex justify-content-between align-items-center sticky-top shadow-sm" style="z-index: 1000;">
                    <h5 class="mb-0 fw-bold text-dark border-start border-4 border-primary ps-3" style="border-color: #1a237e !important;">Analytics</h5>
                    <div class="d-flex align-items-center gap-4">
                        <div class="input-group input-group-sm d-none d-md-flex" style="width: 250px;">
                            <span class="input-group-text bg-light border-0"><i class="bi bi-search"></i></span>
                            <input type="text" class="form-control bg-light border-0" placeholder="Search analytics...">
                        </div>
                        <div class="position-relative">
                            <i class="bi bi-bell fs-5 text-muted"></i>
                            <span class="position-absolute top-0 start-100 translate-middle badge rounded-circle bg-danger border border-white" style="padding: 4px; font-size: 10px;">3</span>
                        </div>
                        <div class="rounded-circle overflow-hidden border shadow-sm d-flex align-items-center justify-content-center bg-light" style="width: 40px; height: 40px;">
                            <i class="bi bi-person-circle fs-4 text-secondary"></i>
                        </div>
                    </div>
                </div>

                <div class="p-4">
                    <div class="mb-4">
                        <h2 class="fw-bold mb-1 text-dark">Analytics</h2>
                        <p class="text-muted small mb-0">Track your business performance and insights</p>
                    </div>

                    <div class="row g-3 mb-4">
                        <?php
                        $stats = [
                            ['label' => 'Revenue Growth', 'value' => '23.5%', 'trend' => '+12%', 'icon' => 'bi-graph-up-arrow', 'color' => 'success'],
                            ['label' => 'Conversion Rate', 'value' => '3.2%', 'trend' => '+12%', 'icon' => 'bi-currency-dollar', 'color' => 'success'],
                            ['label' => 'Avg. Order Value', 'value' => '₱125', 'trend' => '-5%', 'icon' => 'bi-bag-check', 'color' => 'danger'],
                            ['label' => 'Customer Retention', 'value' => '84%', 'trend' => '+12%', 'icon' => 'bi-people', 'color' => 'success']
                        ];
                        foreach ($stats as $s): ?>
                        <div class="col-md-3">
                            <div class="card border-0 shadow-sm rounded-4 p-3">
                                <div class="d-flex justify-content-between align-items-start mb-3">
                                    <div class="text-<?php echo $s['color']; ?> bg-<?php echo $s['color']; ?>-subtle rounded-3 p-2">
                                        <i class="bi <?php echo $s['icon']; ?> fs-5"></i>
                                    </div>
                                    <span class="badge rounded-pill bg-<?php echo $s['color']; ?>-subtle text-<?php echo $s['color']; ?> px-2 py-1" style="font-size: 0.7rem;">
                                        <?php echo $s['trend']; ?>
                                    </span>
                                </div>
                                <p class="text-muted small mb-1"><?php echo $s['label']; ?></p>
                                <h3 class="fw-bold mb-0"><?php echo $s['value']; ?></h3>
                            </div>
                        </div>
                        <?php endforeach; ?>
                    </div>

                    <div class="card border-0 shadow-sm rounded-4 mb-4">
                        <div class="card-body p-4">
                            <div class="d-flex justify-content-between align-items-center mb-4">
                                <h5 class="fw-bold mb-0">Revenue Overview</h5>
                                <div class="btn-group btn-group-sm shadow-sm rounded-3">
                                    <button class="btn btn-dark px-3">Month</button>
                                    <button class="btn btn-outline-secondary px-3">Year</button>
                                </div>
                            </div>
                            <p class="text-muted small mb-4">Monthly revenue for the past 12 months</p>
                            
                            <div class="d-flex align-items-end justify-content-between pt-4" style="height: 250px;">
                                <?php 
                                $heights = [45, 65, 35, 80, 55, 40, 75, 85, 45, 65, 78, 82];
                                $months = ['Jan','Feb','Mar','Apr','May','Jun','Jul','Aug','Sep','Oct','Nov','Dec'];
                                foreach($heights as $i => $h): ?>
                                <div class="d-flex flex-column align-items-center w-100">
                                    <div class="bg-secondary opacity-50 rounded-top" style="width: 70%; height: <?php echo $h * 2.5; ?>px;"></div>
                                    <span class="text-muted mt-2" style="font-size: 10px;"><?php echo $months[$i]; ?></span>
                                </div>
                                <?php endforeach; ?>
                            </div>
                        </div>
                    </div>

                    <div class="row g-4">
                        <div class="col-md-7">
                            <div class="card border-0 shadow-sm rounded-4 h-100">
                                <div class="card-body p-4">
                                    <h5 class="fw-bold mb-4">Top Products</h5>
                                    <?php
                                    $top_products = [
                                        ['name' => 'Txt Moa Bong', 'sales' => '2 sales', 'width' => '90%'],
                                        ['name' => 'Twice Candy Bong', 'sales' => '1 sales', 'width' => '75%'],
                                        ['name' => 'BTS Army Bomb', 'sales' => '1 sales', 'width' => '60%'],
                                      
                                    ];
                                    foreach ($top_products as $tp): ?>
                                    <div class="mb-4">
                                        <div class="d-flex justify-content-between mb-1">
                                            <span class="small fw-bold text-dark"><?php echo $tp['name']; ?></span>
                                            <span class="small text-muted"><?php echo $tp['sales']; ?></span>
                                        </div>
                                        <div class="progress rounded-pill" style="height: 8px; background-color: #e9ecef;">
                                            <div class="progress-bar rounded-pill" style="width: <?php echo $tp['width']; ?>; background-color: #1a237e;"></div>
                                        </div>
                                    </div>
                                    <?php endforeach; ?>
                                </div>
                            </div>
                        </div>

                        <div class="col-md-5">
                            <div class="card border-0 shadow-sm rounded-4 h-100">
                                <div class="card-body p-4">
                                    <h5 class="fw-bold mb-4">Traffic Sources</h5>
                                    <div class="d-flex justify-content-center mb-4">
                                        <div class="rounded-circle d-flex align-items-center justify-content-center" 
                                             style="width: 180px; height: 180px; border: 30px solid #1a237e; border-left-color: #adb5bd; border-bottom-color: #dee2e6;">
                                        </div>
                                    </div>
                                    <div class="mt-4">
                                        <div class="d-flex justify-content-between align-items-center mb-2">
                                            <div class="small"><i class="bi bi-circle-fill me-2" style="color: #1a237e;"></i> Direct</div>
                                            <span class="small fw-bold">45%</span>
                                        </div>
                                        <div class="d-flex justify-content-between align-items-center mb-2">
                                            <div class="small"><i class="bi bi-circle-fill me-2 text-secondary"></i> Social</div>
                                            <span class="small fw-bold">30%</span>
                                        </div>
                                        <div class="d-flex justify-content-between align-items-center">
                                            <div class="small"><i class="bi bi-circle-fill me-2 opacity-25 text-secondary"></i> Referral</div>
                                            <span class="small fw-bold">25%</span>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                </div> </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>