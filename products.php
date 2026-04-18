<?php
session_start();
require "connect.php";

// Protect page
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

$display_name = $_SESSION['username'] ?? "Admin User";

// ---  HANDLE ADD / EDIT ACTIONS ---
if (isset($_POST['save_product'])) {
    $name = $_POST['product_name'];
    $price = $_POST['price'];
    $stock = $_POST['stock'];
    $img = $_POST['img_path'];
    $status = ($stock <= 5) ? 'Low Stock' : 'In Stock';

    if (!empty($_POST['product_id'])) {
        // EDIT EXISTING
        $stmt = $conn->prepare("UPDATE products SET product=?, price=?, stock=?, stock_status=?, img=? WHERE id=?");
        $stmt->execute([$name, $price, $stock, $status, $img, $_POST['product_id']]);
    } else {
        // ADD NEW
        $stmt = $conn->prepare("INSERT INTO products (product, price, stock, stock_status, img) VALUES (?,?,?,?,?)");
        $stmt->execute([$name, $price, $stock, $status, $img]);
    }
    header("Location: products.php");
    exit();
}

// ---FETCH PRODUCTS FROM DATABASE ---
$stmt = $conn->prepare("SELECT * FROM products ORDER BY id DESC");
$stmt->execute();
$db_products = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Products - SPORTIFY</title>
    <link rel="stylesheet" href="dashboard.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        /* Simple Modal Styling */
        .modal { display: none; position: fixed; z-index: 1000; left: 0; top: 0; width: 100%; height: 100%; background: rgba(0,0,0,0.6); }
        .modal-content { background: #1a1a1a; color: white; margin: 10% auto; padding: 20px; width: 400px; border-radius: 8px; border: 1px solid #333; }
        .modal-content input { width: 100%; padding: 10px; margin: 10px 0; background: #222; border: 1px solid #444; color: white; }
        .save-btn { background: #fff; color: #000; border: none; padding: 10px 20px; cursor: pointer; font-weight: bold; width: 100%; }
    </style>
</head>
<body>

    <div class="app-container">
        <aside class="sidebar">
            <div class="sidebar-header">
                <div class="logo-box"><img src="image/logo.jpg" alt="Logo" style="width: 100%;"></div>
                <h1 class="logo-text">SPORTIFY</h1>
            </div>
            <nav class="nav-menu"> <a href="homepage.php" class="nav-item active"><i class="fas fa-th-large"></i> Dashboard</a> <a href="order.php" class="nav-item"><i class="fas fa-shopping-cart"></i> Orders</a> <a href="products.php" class="nav-item"><i class="fas fa-box"></i> Products</a> <a href="categories.php" class="nav-item"><i class="fas fa-folder"></i> Categories</a> <a href="customers.php" class="nav-item"><i class="fas fa-users"></i> Customers</a> <a href="analytics.php" class="nav-item analytics-dark"><i class="fas fa-chart-line"></i> Analytics</a> <div class="nav-divider"></div> <a href="profile.php" class="nav-item"><i class="fas fa-user-gear"></i> Profile</a> <a href="logout.php" class="nav-item logout-btn"><i class="fas fa-power-off"></i> Logout</a> </nav>
        </aside>

        <main class="content-area">
            <header class="top-header">
                <div class="header-title-area">
                    <h1>Products</h1>
                    <p>Manage your footwear collection</p>
                </div>
                <div class="header-actions">
                    <button class="add-product-btn" onclick="openModal()"><i class="fas fa-plus"></i> Add Product</button>
                    <div class="user-pill"><span><?php echo htmlspecialchars($display_name); ?></span></div>
                </div>
            </header>

            <section class="inventory-section">
                <div class="table-container">
                    <table class="styled-table">
                        <thead>
                            <tr>
                                <th>Product</th>
                                <th>Price</th>
                                <th>Stock</th>
                                <th>Status</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach($db_products as $p): ?>
                            <tr>
                                <td class="product-cell">
                                    <img src="<?php echo htmlspecialchars($p['img']); ?>" class="product-img-small" onerror="this.src='https://via.placeholder.com/50'">
                                    <span><?php echo htmlspecialchars($p['product']); ?></span>
                                </td>
                                <td><strong>₱<?php echo number_format($p['price'], 2); ?></strong></td>
                                <td><?php echo $p['stock']; ?></td>
                                <td>
                                    <span class="status-pill <?php echo ($p['stock_status'] == 'In Stock') ? 'completed' : 'low'; ?>">
                                        <?php echo htmlspecialchars($p['stock_status']); ?>
                                    </span>
                                </td>
                                <td>
                                    <div class="action-links">
                                        <a href="javascript:void(0)" class="edit-lnk" onclick='editProduct(<?php echo json_encode($p); ?>)'>Edit</a>
                                    </div>
                                </td>
                            </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            </section>
        </main>
    </div>

    <div id="productModal" class="modal">
        <div class="modal-content">
            <h2 id="modalTitle">Add Product</h2>
            <form method="POST">
                <input type="hidden" name="product_id" id="p_id">
                <input type="text" name="product_name" id="p_name" placeholder="Product Name" required>
                <input type="number" step="0.01" name="price" id="p_price" placeholder="Price" required>
                <input type="number" name="stock" id="p_stock" placeholder="Stock Quantity" required>
                <input type="text" name="img_path" id="p_img" placeholder="Image Path (e.g., images/shoe.jpg)" required>
                <button type="submit" name="save_product" class="save-btn">Save Product</button>
                <button type="button" onclick="closeModal()" style="background:transparent; color:gray; border:none; margin-top:10px; cursor:pointer;">Cancel</button>
            </form>
        </div>
    </div>

    <script>
        const modal = document.getElementById('productModal');

        function openModal() {
            document.getElementById('modalTitle').innerText = "Add Product";
            document.getElementById('p_id').value = "";
            document.getElementById('p_name').value = "";
            document.getElementById('p_price').value = "";
            document.getElementById('p_stock').value = "";
            document.getElementById('p_img').value = "";
            modal.style.display = "block";
        }

        function editProduct(data) {
            document.getElementById('modalTitle').innerText = "Edit Product";
            document.getElementById('p_id').value = data.id;
            document.getElementById('p_name').value = data.product;
            document.getElementById('p_price').value = data.price;
            document.getElementById('p_stock').value = data.stock;
            document.getElementById('p_img').value = data.img;
            modal.style.display = "block";
        }

        function closeModal() { modal.style.display = "none"; }
        window.onclick = function(event) { if (event.target == modal) closeModal(); }
    </script>
</body>
</html>