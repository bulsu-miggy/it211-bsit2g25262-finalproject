<?php
$pdo = getDBConnection();
$id = $_GET['id'] ?? 0;

$stmt = $pdo->prepare("SELECT * FROM products WHERE id = ?");
$stmt->execute([$id]);
$product = $stmt->fetch();

if (!$product) {
    header('Location: index.php?page=products');
    exit;
}

$categories = $pdo->query("SELECT id, name FROM categories WHERE is_active = 1 ORDER BY name")->fetchAll();

$success_message = $_SESSION['success'] ?? null;
$error_message = $_SESSION['error'] ?? null;
unset($_SESSION['success'], $_SESSION['error']);
?>
<div class="max-w-4xl mx-auto">
    <div class="mb-8">
        <div class="flex items-center gap-4">
            <a href="?page=products" class="text-gray-600 hover:text-gray-800">
                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"></path>
                </svg>
            </a>
            <div>
                <h2 class="text-2xl font-bold text-gray-800 mb-2">Edit Product</h2>
                <p class="text-gray-600">Update product information</p>
            </div>
        </div>
    </div>

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

    <div class="bg-white rounded-lg border-2 border-gray-300 p-8">
        <form method="POST" action="index.php?page=edit-product&id=<?php echo $product['id']; ?>">
            <input type="hidden" name="edit_product" value="1">
            <input type="hidden" name="product_id" value="<?php echo $product['id']; ?>">
            
            <div class="grid grid-cols-2 gap-6">
                <div>
                    <label class="block text-sm font-semibold text-gray-700 mb-2">Product Name *</label>
                    <input type="text" name="name" value="<?php echo htmlspecialchars($product['name']); ?>" class="w-full px-4 py-2 border-2 border-gray-300 rounded focus:outline-none focus:border-blue-500" required>
                </div>

                <div>
                    <label class="block text-sm font-semibold text-gray-700 mb-2">Category</label>
                    <select name="category_id" class="w-full px-4 py-2 border-2 border-gray-300 rounded focus:outline-none focus:border-blue-500">
                        <option value="">Select Category</option>
                        <?php foreach ($categories as $cat): ?>
                            <option value="<?php echo $cat['id']; ?>" <?php echo ($product['category_id'] == $cat['id']) ? 'selected' : ''; ?>>
                                <?php echo htmlspecialchars($cat['name']); ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>

                <div>
                    <label class="block text-sm font-semibold text-gray-700 mb-2">Price *</label>
                    <input type="number" name="price" step="0.01" value="<?php echo $product['price']; ?>" class="w-full px-4 py-2 border-2 border-gray-300 rounded focus:outline-none focus:border-blue-500" required>
                </div>


                <div>
                    <label class="block text-sm font-semibold text-gray-700 mb-2">Status</label>
                    <select name="is_active" class="w-full px-4 py-2 border-2 border-gray-300 rounded focus:outline-none focus:border-blue-500">
                        <option value="1" <?php echo $product['is_active'] ? 'selected' : ''; ?>>Available</option>
                        <option value="0" <?php echo !$product['is_active'] ? 'selected' : ''; ?>>Not Available</option>
                    </select>
                </div>
            </div>

            <div class="mt-6">
                <label class="block text-sm font-semibold text-gray-700 mb-2">Description</label>
                <textarea name="description" rows="5" class="w-full px-4 py-2 border-2 border-gray-300 rounded focus:outline-none focus:border-blue-500"><?php echo htmlspecialchars($product['description'] ?? ''); ?></textarea>
            </div>

            <div class="mt-6 flex gap-4">
                <button type="submit" class="px-6 py-2 bg-blue-600 text-white rounded hover:bg-blue-700 transition flex items-center gap-2">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>
                    </svg>
                    Update Product
                </button>
                <a href="?page=products" class="px-6 py-2 bg-gray-300 text-gray-700 rounded hover:bg-gray-400 transition">Cancel</a>
            </div>
        </form>
    </div>
</div>