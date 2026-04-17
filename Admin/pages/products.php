<?php
$pdo = getDBConnection();
$view = $_GET['view'] ?? 'list';

// Get filter values
$search = $_GET['search'] ?? '';
$category = $_GET['category'] ?? '';

// Build query
$sql = "SELECT p.*, c.name as category_name FROM products p 
        LEFT JOIN categories c ON p.category_id = c.id 
        WHERE 1=1";
$params = [];

if ($search) {
    $sql .= " AND p.name LIKE ?";
    $params[] = "%$search%";
}
if ($category) {
    $sql .= " AND p.category_id = ?";
    $params[] = $category;
}

$sql .= " ORDER BY p.created_at DESC";
$stmt = $pdo->prepare($sql);
$stmt->execute($params);
$products = $stmt->fetchAll();

// Get categories for filter
$categories = $pdo->query("SELECT id, name FROM categories WHERE is_active = 1 ORDER BY name")->fetchAll();
?>
<div class="space-y-8">
    <div class="flex items-center justify-between">
        <div>
            <h1 class="text-3xl text-gray-800 mb-2">Products</h1>
            <p class="text-gray-600">Manage your product catalog</p>
        </div>
        <!-- <a href="?page=add-product" class="border-2 border-gray-800 bg-gray-800 text-white px-6 py-2.5 rounded hover:bg-gray-700 transition-colors flex items-center gap-2">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"></path>
            </svg>
            Add Product
        </a> -->
    </div>

    <!-- Filters -->
    <div class="border-2 border-gray-300 bg-white p-6 rounded">
        <div class="flex flex-col md:flex-row gap-4">
            <form method="GET" class="flex-1 flex gap-4">
                <input type="hidden" name="page" value="products">
                <input type="hidden" name="view" value="<?php echo $view; ?>">
                <div class="flex-1 flex items-center gap-2 border-2 border-gray-300 bg-gray-50 px-4 py-2 rounded">
                    <svg class="w-4 h-4 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"></path>
                    </svg>
                    <input type="text" name="search" value="<?php echo htmlspecialchars($search); ?>" placeholder="Search products..." class="flex-1 bg-transparent outline-none text-gray-800">
                </div>
                <select name="category" class="border-2 border-gray-300 px-4 py-2 rounded bg-white text-gray-800 outline-none">
                    <option value="">All Categories</option>
                    <?php foreach ($categories as $cat): ?>
                        <option value="<?php echo $cat['id']; ?>" <?php echo $category == $cat['id'] ? 'selected' : ''; ?>>
                            <?php echo htmlspecialchars($cat['name']); ?>
                        </option>
                    <?php endforeach; ?>
                </select>
                <button type="submit" class="border-2 border-gray-800 bg-gray-800 text-white px-4 py-2 rounded hover:bg-gray-700">Filter</button>
                <a href="?page=products" class="border-2 border-gray-300 bg-white text-gray-700 px-4 py-2 rounded hover:bg-gray-50">Clear</a>
            </form>
            <div class="flex border-2 border-gray-300 rounded overflow-hidden">
                <a href="?page=products&view=grid&search=<?php echo urlencode($search); ?>&category=<?php echo urlencode($category); ?>" 
                   class="px-3 py-2 <?php echo $view === 'grid' ? 'bg-gray-800 text-white' : 'bg-white text-gray-600 hover:bg-gray-50'; ?>">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2H6a2 2 0 01-2-2V6zM14 6a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2h-2a2 2 0 01-2-2V6zM4 16a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2H6a2 2 0 01-2-2v-2zM14 16a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2h-2a2 2 0 01-2-2v-2z"></path>
                    </svg>
                </a>
                <a href="?page=products&view=list&search=<?php echo urlencode($search); ?>&category=<?php echo urlencode($category); ?>" 
                   class="px-3 py-2 border-l-2 border-gray-300 <?php echo $view === 'list' ? 'bg-gray-800 text-white' : 'bg-white text-gray-600 hover:bg-gray-50'; ?>">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16"></path>
                    </svg>
                </a>
            </div>
        </div>
    </div>

    <!-- Products Display -->
    <?php if ($view === 'list'): ?>
    <div class="border-2 border-gray-300 bg-white rounded overflow-hidden">
        <div class="overflow-x-auto">
            <table class="w-full" id="productsTable">
                <thead class="border-b-2 border-gray-300 bg-gray-50">
                    <tr>
                        <th class="text-left px-6 py-4 text-sm text-gray-700">Product</th>
                        <th class="text-left px-6 py-4 text-sm text-gray-700">Category</th>
                        <!-- <th class="text-left px-6 py-4 text-sm text-gray-700">SKU</th> -->
                        <th class="text-left px-6 py-4 text-sm text-gray-700">Price</th>
                        <th class="text-left px-6 py-4 text-sm text-gray-700">Stock</th>
                        <th class="text-left px-6 py-4 text-sm text-gray-700">Status</th>
                        <th class="text-left px-6 py-4 text-sm text-gray-700">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($products as $product): ?>
                    <tr id="product-row-<?php echo $product['id']; ?>">
                        <td class="px-6 py-4">
                            <div class="flex items-center gap-3">
                                <div class="w-10 h-10 bg-gray-200 border-2 border-gray-300 rounded"></div>
                                <span class="text-gray-800 font-medium"><?php echo htmlspecialchars($product['name']); ?></span>
                            </div>
                        </td>
                        <td class="px-6 py-4 text-gray-600"><?php echo htmlspecialchars($product['category_name'] ?? 'Uncategorized'); ?></td>
                        <!-- <td class="px-6 py-4 text-gray-500 text-sm"><?php echo htmlspecialchars($product['sku'] ?? 'N/A'); ?></td> -->
                        <td class="px-6 py-4 text-gray-800"><?php echo formatPrice($product['price']); ?></td>
                        <td class="px-6 py-4 text-gray-600"><?php echo $product['stock_quantity']; ?></td>
                        <td class="px-6 py-4">
                            <span class="px-3 py-1 rounded text-sm border <?php echo $product['is_active'] ? 'bg-green-100 text-green-800 border-green-200' : 'bg-gray-100 text-gray-800 border-gray-200'; ?>">
                                <?php echo $product['is_active'] ? 'Available' : 'Not Available'; ?>
                            </span>
                        </td>
                        <td class="px-6 py-4">
                            <a href="?page=edit-product&id=<?php echo $product['id']; ?>" class="text-blue-600 hover:text-blue-800 underline text-sm mr-3">Edit</a>
                            <form method="POST" style="display: inline;" onsubmit="return confirm('Are you sure you want to delete this product?');">
                                <input type="hidden" name="delete_product_id" value="<?php echo $product['id']; ?>">
                                <button type="submit" class="text-red-600 hover:text-red-800 underline text-sm">Delete</button>
                            </form>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                    <?php if (empty($products)): ?>
                    <tr>
                        <td colspan="7" class="px-6 py-8 text-center text-gray-500">
                            No products found. <a href="?page=add-product" class="text-blue-600 hover:underline">Add your first product</a>.
                        </td>
                    </tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
    <?php else: ?>
    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4 gap-6" id="productsGrid">
        <?php foreach ($products as $product): ?>
        <div id="product-card-<?php echo $product['id']; ?>" class="border-2 border-gray-300 bg-white rounded overflow-hidden">
            <div class="aspect-square bg-gray-200 border-b-2 border-gray-300"></div>
            <div class="p-4 space-y-3">
                <h3 class="text-gray-800 font-medium"><?php echo htmlspecialchars($product['name']); ?></h3>
                <p class="text-sm text-gray-600"><?php echo htmlspecialchars($product['category_name'] ?? 'Uncategorized'); ?></p>
                <div class="flex items-center justify-between">
                    <span class="text-lg text-gray-800"><?php echo formatPrice($product['price']); ?></span>
                    <span class="text-sm text-gray-600">Stock: <?php echo $product['stock_quantity']; ?></span>
                </div>
                <div class="flex gap-2">
                    <a href="?page=edit-product&id=<?php echo $product['id']; ?>" class="flex-1 border-2 border-gray-800 bg-gray-800 text-white px-4 py-2 rounded text-center text-sm hover:bg-gray-700">Edit</a>
                    <form method="POST" style="flex: 1;" onsubmit="return confirm('Are you sure you want to delete this product?');">
                        <input type="hidden" name="delete_product_id" value="<?php echo $product['id']; ?>">
                        <button type="submit" class="w-full border-2 border-red-300 bg-white text-red-600 px-4 py-2 rounded text-sm hover:bg-red-50">Delete</button>
                    </form>
                </div>
            </div>
        </div>
        <?php endforeach; ?>
        <?php if (empty($products)): ?>
        <div class="col-span-full text-center py-8 text-gray-500">
            No products found. <a href="?page=add-product" class="text-blue-600 hover:underline">Add your first product</a>.
        </div>
        <?php endif; ?>
    </div>
    <?php endif; ?>
</div>