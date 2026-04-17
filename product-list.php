<?php
require 'db/action/dbconfig.php';

function escape_html_product_list($value)
{
    return htmlspecialchars((string) $value, ENT_QUOTES, 'UTF-8');
}

function resolve_product_image_path_product_list($rawImage)
{
    $value = trim((string) $rawImage);
    if ($value === '') {
        return 'assets2/adidasblablabla.png';
    }

    $value = str_replace('\\', '/', $value);
    if (preg_match('#^(https?:)?//#i', $value) || strpos($value, 'data:') === 0) {
        return $value;
    }

    if (strpos($value, 'assets2/') === 0 || strpos($value, 'images/') === 0) {
        return $value;
    }

    return 'images/products/' . basename($value);
}

function get_display_products_product_list(PDO $conn, array $fallbackProducts, string $sortBy = 'price', string $sortOrder = 'ASC', string $filterSize = '', string $filterCategory = '', string $searchQuery = '', bool $hasFilter = false)
{
    try {
        // Determine ORDER BY clause based on sort parameter and order
        $orderBy = "ORDER BY price $sortOrder, id DESC"; // default
        
        if ($sortBy === 'sales') {
            $orderBy = "ORDER BY sales $sortOrder, id DESC";
        } elseif ($sortBy === 'name') {
            $orderBy = "ORDER BY name $sortOrder, id DESC";
        }
        
        $query = "SELECT p.id, p.name, c.name as category, p.color, p.size, p.price, p.image, p.sales
            FROM products p
            LEFT JOIN categories c ON p.category_id = c.id
            WHERE 1=1";
        
        if (!empty($filterCategory)) {
            $query .= " AND c.name = :category";
        }
        
        if (!empty($filterSize)) {
            $query .= " AND size = :size";
        }

        if ($searchQuery !== '') {
            $query .= " AND (p.name LIKE :search OR c.name LIKE :search OR p.color LIKE :search)";
        }
        
        $query .= " $orderBy";
        
        $stmt = $conn->prepare($query);
        
        if (!empty($filterCategory)) {
            $stmt->bindValue(':category', $filterCategory, PDO::PARAM_STR);
        }
        
        if (!empty($filterSize)) {
            $stmt->bindValue(':size', $filterSize, PDO::PARAM_STR);
        }

        if ($searchQuery !== '') {
            $stmt->bindValue(':search', '%' . $searchQuery . '%', PDO::PARAM_STR);
        }
        
        $stmt->execute();
        $products = $stmt->fetchAll(PDO::FETCH_ASSOC);
    } catch (PDOException $e) {
        $products = [];
    }

    if (empty($products)) {
        // If a filter is active, don't show placeholders - return empty array
        if ($hasFilter) {
            return [];
        }
        // Otherwise use placeholders as fallback
        return $fallbackProducts;
    }

    return $products;
}

function product_detail_link_product_list(array $product)
{
    return !empty($product['id']) ? 'product detail.php?id=' . urlencode((string) $product['id']) : 'product detail.php';
}

$placeholderProducts = [
    ['name' => 'Nike Air Max', 'category' => 'Footwear', 'color' => 'Black/White', 'size' => '10', 'price' => 120.00, 'image' => 'assets2/adidasblablabla.png', 'sales' => 1240],
    ['name' => 'Adidas Ultraboost', 'category' => 'Footwear', 'color' => 'White/Gray', 'size' => '9', 'price' => 150.00, 'image' => 'assets2/adidasblablabla.png', 'sales' => 1180],
    ['name' => 'Puma Suede', 'category' => 'Footwear', 'color' => 'Blue/Red', 'size' => '11', 'price' => 85.00, 'image' => 'assets2/adidasblablabla.png', 'sales' => 980],
    ['name' => 'New Balance 990', 'category' => 'Footwear', 'color' => 'Grey/Navy', 'size' => '10', 'price' => 175.00, 'image' => 'assets2/adidasblablabla.png', 'sales' => 930],
    ['name' => 'Reebok Classic', 'category' => 'Footwear', 'color' => 'White/Green', 'size' => '9', 'price' => 95.00, 'image' => 'assets2/adidasblablabla.png', 'sales' => 890],
    ['name' => 'Vans Old Skool', 'category' => 'Footwear', 'color' => 'Black/White', 'size' => '8', 'price' => 65.00, 'image' => 'assets2/adidasblablabla.png', 'sales' => 860],
    ['name' => 'Converse Chuck Taylor', 'category' => 'Footwear', 'color' => 'Black', 'size' => '10', 'price' => 55.00, 'image' => 'assets2/adidasblablabla.png', 'sales' => 820],
    ['name' => 'ASICS Gel-Kayano', 'category' => 'Footwear', 'color' => 'Blue/Orange', 'size' => '11', 'price' => 160.00, 'image' => 'assets2/adidasblablabla.png', 'sales' => 780],
    ['name' => 'Saucony Endorphin', 'category' => 'Footwear', 'color' => 'Red/White', 'size' => '9', 'price' => 140.00, 'image' => 'assets2/adidasblablabla.png', 'sales' => 740],
    ['name' => 'Hoka Bondi 8', 'category' => 'Footwear', 'color' => 'Blue/White', 'size' => '10', 'price' => 165.00, 'image' => 'assets2/adidasblablabla.png', 'sales' => 700],
    ['name' => 'Brooks Ghost 15', 'category' => 'Footwear', 'color' => 'Black/Gray', 'size' => '8', 'price' => 130.00, 'image' => 'assets2/adidasblablabla.png', 'sales' => 660],
    ['name' => 'Under Armour HOVR', 'category' => 'Footwear', 'color' => 'Blue/Red', 'size' => '11', 'price' => 110.00, 'image' => 'assets2/adidasblablabla.png', 'sales' => 620],
];

// Get sort parameter from URL (default: price)
$currentSort = isset($_GET['sort']) && in_array($_GET['sort'], ['price', 'sales', 'name']) ? $_GET['sort'] : 'price';

// Get sort order from URL (default: ASC)
$currentOrder = isset($_GET['order']) && in_array($_GET['order'], ['ASC', 'DESC']) ? $_GET['order'] : 'ASC';

// Get size filter from URL
$filterSize = isset($_GET['size']) ? $_GET['size'] : '';

// Get category filter from URL
$filterCategory = isset($_GET['category']) ? $_GET['category'] : '';

// Get search query from URL
$searchQuery = trim((string)($_GET['q'] ?? ''));

// Check if any filter is active
$hasFilter = !empty($filterSize) || !empty($filterCategory) || ($searchQuery !== '');

$catalogProducts = get_display_products_product_list($conn, $placeholderProducts, $currentSort, $currentOrder, $filterSize, $filterCategory, $searchQuery, $hasFilter);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Product List - Laces</title>
    <link rel="icon" type="image/png" href="assets2/logo.png">
    <!-- Bootstrap 5 + Icons -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
    <!-- Custom Styles -->
    <link rel="stylesheet" href="css/master.css">
    <link rel="stylesheet" href="css/list-styles.css">
    <!-- Inline fix for layout padding -->
    <style>
        /* Override the huge top padding from list-styles.css */
        .two-column-layout {
            padding-top: 2rem !important;
        }
        /* Ensure product grid adapts well */
        .product-grid {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(240px, 1fr));
            gap: 1.5rem;
        }
        @media (max-width: 768px) {
            .product-grid {
                grid-template-columns: repeat(auto-fill, minmax(180px, 1fr));
            }
        }
        /* Active filter button styling */
        .filter-btn.active {
            background-color: #f4d35e;
            color: #000;
            font-weight: bold;
        }
        .filter-btn {
            text-decoration: none;
            color: inherit;
            display: inline-block;
        }
        .filter-separator {
            display: inline-block;
            margin: 0 8px;
            color: #ccc;
        }
        /* Filter dropdowns styling */
        .filter-row {
            display: flex;
            gap: 2rem;
            margin-bottom: 2rem;
            flex-wrap: wrap;
            align-items: center;
        }
        .filter-group {
            display: flex;
            align-items: center;
            gap: 0.75rem;
        }
        .filter-group label {
            font-weight: 600;
            color: #333;
            margin: 0;
            font-size: 0.95rem;
        }
        .filter-dropdown {
            padding: 0.6rem 1rem;
            border: 1.5px solid #e0e0e0;
            border-radius: 8px;
            background: white;
            color: #333;
            font-size: 0.9rem;
            cursor: pointer;
            transition: all 0.2s ease;
            min-width: 200px;
        }
        .filter-dropdown:hover {
            border-color: #f4d35e;
            box-shadow: 0 2px 8px rgba(244, 211, 94, 0.1);
        }
        .filter-dropdown:focus {
            outline: none;
            border-color: #f4d35e;
            box-shadow: 0 0 0 2px rgba(244, 211, 94, 0.2);
        }
        /* Sidebar links should look like plain category text */
        .left-container .category-list .category-link,
        .left-container .category-list .category-link:link,
        .left-container .category-list .category-link:visited,
        .left-container .category-list .category-link:hover,
        .left-container .category-list .category-link:active {
            text-decoration: none !important;
            color: #666 !important;
        }
        .left-container .category-list .category-link:hover,
        .left-container .category-list .category-link.active {
            color: #f4d35e !important;
        }
        /* No Products Message */
        .no-products-message {
            text-align: center;
            padding: 3rem 2rem;
            background: #f9f9f9;
            border-radius: 12px;
            border: 2px dashed #e0e0e0;
            margin: 2rem 0;
        }
        .no-products-message p {
            font-size: 1.1rem;
            color: #666;
            margin: 0 0 1rem 0;
        }
        .reset-link {
            display: inline-block;
            padding: 0.7rem 1.5rem;
            background: #f4d35e;
            color: #333;
            text-decoration: none;
            border-radius: 8px;
            font-weight: 600;
            transition: all 0.2s ease;
        }
        .reset-link:hover {
            background: #e0ba4d;
            transform: translateY(-2px);
            box-shadow: 0 4px 12px rgba(244, 211, 94, 0.3);
        }
    </style>
</head>
<body class="bg-white">
    <!-- ========== NAVBAR (same as other pages) ========== -->
    <nav class="navbar bg-white border-bottom py-3">
        <div class="container d-flex align-items-center">
            <a href="index.php" class="d-flex align-items-center text-decoration-none text-dark fw-bold fs-4 me-3">
                <img src="assets2/logo.png" alt="Laces" style="width:35px;" class="me-2">
                Laces
            </a>
            <form class="flex-grow-1 mx-3 d-flex justify-content-center" role="search" action="product-list.php" method="GET">
                <div class="position-relative w-100" style="max-width: 900px;">
                    <input class="form-control rounded-pill border-dark ps-3 pe-5" type="search" name="q" value="<?php echo escape_html_product_list($searchQuery); ?>" placeholder="Search products...">
                    <i class="bi bi-search position-absolute top-50 end-0 translate-middle-y me-3 text-muted"></i>
                </div>
            </form>
            <div class="d-flex align-items-center gap-3">
                <a href="cart/cart.php">
                    <button class="btn p-0 border-0 bg-transparent">
                        <img src="assets2/cart.png" width="20">
                    </button>
                </a>
                <button class="btn p-0 border-0 bg-transparent"><img src="assets2/world.png" width="20"></button>
                <button class="btn p-0 border-0 bg-transparent"><img src="assets2/si--notifications-alt-2-fill.png" width="20"></button>
                <button class="btn p-0 border-0 bg-transparent" type="button" data-bs-toggle="offcanvas" data-bs-target="#profileMenu">
                    <img src="assets2/gg--profile.png" width="20">
                </button>
            </div>
        </div>
    </nav>

    <!-- ========== OFF CANVAS PROFILE MENU ========== -->
    <div class="offcanvas offcanvas-end" tabindex="-1" id="profileMenu" aria-labelledby="profileMenuLabel">
        <div class="offcanvas-header border-bottom">
            <h5 class="offcanvas-title fw-bold" id="profileMenuLabel">My Account</h5>
            <button type="button" class="btn-close" data-bs-dismiss="offcanvas" aria-label="Close"></button>
        </div>
        <div class="offcanvas-body">
            <div class="text-center mb-4">
                <img src="assets2/gg--profile.png" width="70" class="mb-2 opacity-75">
                <h6 class="fw-bold">Welcome back!</h6>
                <p class="small text-muted">Manage your orders and preferences</p>
            </div>
            <div class="list-group list-group-flush">
                <div class="list-group-item border-0 py-3 theme-toggle-item" data-theme-toggle-item>
                    <label class="theme-toggle-label mb-0 form-check form-switch">
                        <span class="theme-toggle-copy">
                            <i class="bi bi-moon-stars me-3"></i> Dark mode
                        </span>
                        <input class="form-check-input theme-toggle-input" type="checkbox" role="switch" aria-label="Toggle dark mode">
                    </label>
                </div>
                <a href="profilepage.php" class="list-group-item list-group-item-action border-0 py-3">
                    <i class="bi bi-person-circle me-3"></i> View Profile
                </a>
                <a href="cart/orderHistory.php" class="list-group-item list-group-item-action border-0 py-3">
                    <i class="bi bi-box-seam me-3"></i> My Orders
                </a>
                <a href="db/action/logout.php" class="list-group-item list-group-item-action border-0 py-3 text-danger">
                    <i class="bi bi-box-arrow-right me-3"></i> Sign Out
                </a>
            </div>
        </div>
    </div>

    <!-- ========== MENU ROW ========== -->
    <div class="text-center mt-3">
        <a href="index.php" class="mx-3 text-dark text-decoration-none custom-hover">Home</a>
        <a href="product-list.php?sort=sales&order=DESC" class="mx-3 text-dark text-decoration-none custom-hover">Trending</a>
        <a href="product-list.php" class="mx-3 text-dark text-decoration-none custom-hover">Categories</a>
        <a href="product-list.php" class="mx-3 text-dark text-decoration-none custom-hover">Product List</a>
    </div>

    <!-- ========== TWO COLUMN LAYOUT ========== -->
    <div class="two-column-layout">
        <!-- Left Sidebar (Categories & Brands) -->
        <aside class="left-container">
            <h3>Categories</h3>
            <ul class="category-list">
                <li><a href="?sort=<?php echo $currentSort; ?>&order=<?php echo $currentOrder; ?><?php echo !empty($filterSize) ? '&size=' . urlencode($filterSize) : ''; ?><?php echo $searchQuery !== '' ? '&q=' . urlencode($searchQuery) : ''; ?>" class="category-link <?php echo empty($filterCategory) ? 'active' : ''; ?>">All Categories</a></li>
                <li><a href="?category=Running Shoes&sort=<?php echo $currentSort; ?>&order=<?php echo $currentOrder; ?><?php echo !empty($filterSize) ? '&size=' . urlencode($filterSize) : ''; ?><?php echo $searchQuery !== '' ? '&q=' . urlencode($searchQuery) : ''; ?>" class="category-link <?php echo $filterCategory === 'Running Shoes' ? 'active' : ''; ?>">Running Shoes</a></li>
                <li><a href="?category=Court Shoes&sort=<?php echo $currentSort; ?>&order=<?php echo $currentOrder; ?><?php echo !empty($filterSize) ? '&size=' . urlencode($filterSize) : ''; ?><?php echo $searchQuery !== '' ? '&q=' . urlencode($searchQuery) : ''; ?>" class="category-link <?php echo $filterCategory === 'Court Shoes' ? 'active' : ''; ?>">Court Shoes</a></li>
                <li><a href="?category=Field Shoes&sort=<?php echo $currentSort; ?>&order=<?php echo $currentOrder; ?><?php echo !empty($filterSize) ? '&size=' . urlencode($filterSize) : ''; ?><?php echo $searchQuery !== '' ? '&q=' . urlencode($searchQuery) : ''; ?>" class="category-link <?php echo $filterCategory === 'Field Shoes' ? 'active' : ''; ?>">Field Shoes</a></li>
                <li><a href="?category=Gym Shoes&sort=<?php echo $currentSort; ?>&order=<?php echo $currentOrder; ?><?php echo !empty($filterSize) ? '&size=' . urlencode($filterSize) : ''; ?><?php echo $searchQuery !== '' ? '&q=' . urlencode($searchQuery) : ''; ?>" class="category-link <?php echo $filterCategory === 'Gym Shoes' ? 'active' : ''; ?>">Gym Shoes</a></li>
                <li><a href="?category=Sneakers&sort=<?php echo $currentSort; ?>&order=<?php echo $currentOrder; ?><?php echo !empty($filterSize) ? '&size=' . urlencode($filterSize) : ''; ?><?php echo $searchQuery !== '' ? '&q=' . urlencode($searchQuery) : ''; ?>" class="category-link <?php echo $filterCategory === 'Sneakers' ? 'active' : ''; ?>">Sneakers</a></li>
                <li><a href="?category=Hiking Shoes&sort=<?php echo $currentSort; ?>&order=<?php echo $currentOrder; ?><?php echo !empty($filterSize) ? '&size=' . urlencode($filterSize) : ''; ?><?php echo $searchQuery !== '' ? '&q=' . urlencode($searchQuery) : ''; ?>" class="category-link <?php echo $filterCategory === 'Hiking Shoes' ? 'active' : ''; ?>">Hiking Shoes</a></li>
            </ul>
            
        </aside>

        <!-- Right Product Container -->
        <section class="product-container">
            <div class="product-wrapper">
                <!-- Filter Row -->
                <div class="filter-row">
                    <div class="filter-group">
                        <label for="sortDropdown">Sort by:</label>
                        <select id="sortDropdown" class="filter-dropdown">
                            <option value="price_asc" <?php echo $currentSort === 'price' && $currentOrder === 'ASC' ? 'selected' : ''; ?>>Price (Low to High)</option>
                            <option value="price_desc" <?php echo $currentSort === 'price' && $currentOrder === 'DESC' ? 'selected' : ''; ?>>Price (High to Low)</option>
                            <option value="sales_desc" <?php echo $currentSort === 'sales' && $currentOrder === 'DESC' ? 'selected' : ''; ?>>Sales (Most Popular)</option>
                            <option value="sales_asc" <?php echo $currentSort === 'sales' && $currentOrder === 'ASC' ? 'selected' : ''; ?>>Sales (Least Popular)</option>
                            <option value="name_asc" <?php echo $currentSort === 'name' && $currentOrder === 'ASC' ? 'selected' : ''; ?>>Name (A-Z)</option>
                            <option value="name_desc" <?php echo $currentSort === 'name' && $currentOrder === 'DESC' ? 'selected' : ''; ?>>Name (Z-A)</option>
                        </select>
                    </div>

                    <div class="filter-group">
                        <label for="sizeDropdown">Size:</label>
                        <select id="sizeDropdown" class="filter-dropdown">
                            <option value="" <?php echo empty($filterSize) ? 'selected' : ''; ?>>All Sizes</option>
                            <option value="8" <?php echo $filterSize === '8' ? 'selected' : ''; ?>>Size 8</option>
                            <option value="9" <?php echo $filterSize === '9' ? 'selected' : ''; ?>>Size 9</option>
                            <option value="10" <?php echo $filterSize === '10' ? 'selected' : ''; ?>>Size 10</option>
                            <option value="11" <?php echo $filterSize === '11' ? 'selected' : ''; ?>>Size 11</option>
                        </select>
                    </div>
                </div>

                <!-- No Products Message -->
                <?php if (empty($catalogProducts) && $hasFilter): ?>
                <div class="no-products-message">
                    <p>No products found matching your search or filters.</p>
                    <a href="product-list.php" class="reset-link">Clear search and filters</a>
                </div>
                <?php endif; ?>

                <!-- Product Grid -->
            <div class="product-grid">
                <?php foreach ($catalogProducts as $product): ?>
                <?php
                    $productName = escape_html_product_list($product['name'] ?? 'Product Name');
                    $productColor = escape_html_product_list($product['color'] ?? 'Black/White');
                    $productSize = escape_html_product_list($product['size'] ?? 'N/A');
                    $productPrice = number_format((float) ($product['price'] ?? 0), 2);
                    $productImage = escape_html_product_list(resolve_product_image_path_product_list($product['image'] ?? ''));
                    $productSales = number_format((int) ($product['sales'] ?? 0));
                    $productLink = escape_html_product_list(product_detail_link_product_list($product));
                ?>
                <div class="product-card">
                    <div class="card-inner">
                        <a href="<?php echo $productLink; ?>" style="text-decoration: none; color: inherit; display: contents;">
                            <div class="image-container">
                                <div class="image-placeholder">
                                    <img src="<?php echo $productImage; ?>" alt="<?php echo $productName; ?>" class="card-image">
                                    <button class="heart-icon">♡</button>
                                    <div class="sales-container">
                                        <button class="sales-icon" type="button" aria-label="Sales" style="color:#22c55e;"><i class="bi bi-graph-up-arrow"></i></button>
                                        <span class="sales-number"><?php echo $productSales; ?> sales</span>
                                    </div>
                                </div>
                            </div>
                            <div class="product-details">
                                <h3 class="product-name"><?php echo $productName; ?></h3>
                                <p class="product-specs">Color: <?php echo $productColor; ?> | Size: <?php echo $productSize; ?></p>
                                <p class="product-price">₱<?php echo $productPrice; ?></p>
                            </div>
                        </a>
                        <div class="button-group">
                            <button class="add-to-cart-btn" data-product-id="<?php echo (int)($product['id'] ?? 0); ?>">Add to Basket</button>
                            <button class="buy-now-btn"  data-product-id="<?php echo (int)$product['id']; ?>">Buy Now</button>
                        </div>
                    </div>
                </div>
                <?php endforeach; ?>
            </div>
            </div>
        </section>
    </div>

    <?php require_once __DIR__ . '/includes/user/root_footer.php'; ?>

    <!-- Scripts -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <script src="assets2/js/master.js"></script>
    <script>
        // Handle sort dropdown changes
        document.getElementById('sortDropdown').addEventListener('change', function() {
            const sortValue = this.value;
            const params = new URLSearchParams(window.location.search);

            let sort = 'price';
            let order = 'ASC';

            if (sortValue === 'price_desc') {
                sort = 'price';
                order = 'DESC';
            } else if (sortValue === 'sales_desc') {
                sort = 'sales';
                order = 'DESC';
            } else if (sortValue === 'sales_asc') {
                sort = 'sales';
                order = 'ASC';
            } else if (sortValue === 'name_asc') {
                sort = 'name';
                order = 'ASC';
            } else if (sortValue === 'name_desc') {
                sort = 'name';
                order = 'DESC';
            }

            params.set('sort', sort);
            params.set('order', order);
            window.location.href = '?' + params.toString();
        });

        // Handle size dropdown changes
        document.getElementById('sizeDropdown').addEventListener('change', function() {
            const size = this.value;
            const params = new URLSearchParams(window.location.search);

            if (size) {
                params.set('size', size);
            } else {
                params.delete('size');
            }

            window.location.href = '?' + params.toString();
        });
    </script>
</body>
</html>