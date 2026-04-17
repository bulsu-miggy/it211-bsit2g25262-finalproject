<?php
$success_message = $_SESSION['success'] ?? null;
$error_message = $_SESSION['error'] ?? null;
unset($_SESSION['success'], $_SESSION['error']);
?>
<div class="max-w-4xl mx-auto">
    <div class="mb-8">
        <h2 class="text-2xl font-bold text-gray-800 mb-2">Add New Product</h2>
        <p class="text-gray-600">Create a new product for your store</p>
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
        <form method="POST" action="index.php?page=add-product" enctype="multipart/form-data">
            <input type="hidden" name="add_product" value="1">
            
            <div class="grid grid-cols-2 gap-6">
                <div>
                    <label class="block text-sm font-semibold text-gray-700 mb-2">Product Name *</label>
                    <input type="text" name="name" class="w-full px-4 py-2 border-2 border-gray-300 rounded focus:outline-none focus:border-blue-500" required>
                </div>

                <!-- <div>
                    <label class="block text-sm font-semibold text-gray-700 mb-2">SKU</label>
                    <input type="text" name="sku" class="w-full px-4 py-2 border-2 border-gray-300 rounded focus:outline-none focus:border-blue-500">
                </div> -->

                <div>
                    <label class="block text-sm font-semibold text-gray-700 mb-2">Category *</label>
                    <select name="category_id" class="w-full px-4 py-2 border-2 border-gray-300 rounded focus:outline-none focus:border-blue-500" required>
                        <option value="">Select Category</option>
                        <?php 
                        $categories = getAllCategories();
                        if (empty($categories)) {
                            echo '<option value="">No active categories found</option>';
                        } else {
                            foreach ($categories as $cat) {
                                echo '<option value="' . $cat['id'] . '">' . htmlspecialchars($cat['name']) . '</option>';
                            }
                        }
                        ?>
                    </select>
                    <?php if (empty($categories)): ?>
                        <p class="mt-2 text-sm text-red-600">Please add a category first. <a href="?page=add-category" class="text-blue-600 underline">Add category</a>.</p>
                    <?php endif; ?>
                </div>

                <div>
                    <label class="block text-sm font-semibold text-gray-700 mb-2">Price *</label>
                    <input type="number" name="price" step="0.01" class="w-full px-4 py-2 border-2 border-gray-300 rounded focus:outline-none focus:border-blue-500" required>
                </div>

                <div>
                    <label class="block text-sm font-semibold text-gray-700 mb-2">Status</label>
                    <select name="is_active" class="w-full px-4 py-2 border-2 border-gray-300 rounded focus:outline-none focus:border-blue-500">
                        <option value="1">Available</option>
                        <option value="0">Not Available</option>
                    </select>
                </div>
                </div>
            </div>

            <div class="mt-6">
                <label class="block text-sm font-semibold text-gray-700 mb-2">Product Image</label>
                <div class="mb-5">
                  <!-- <img id="uploadPreview" src="<?php echo "../images/dishes/beef_bulalo.jpg"; ?>" class="mb-3 avatar img-fluid" alt="Image Preview" /> -->
                  <br>
                  <input type="file" id="imglink" name="imglink" accept=".jpg,.jpeg,.png" onchange="previewImage(this, 'uploadPreview');" />
                </div>
            </div>

            <div class="mt-6">
                <label class="block text-sm font-semibold text-gray-700 mb-2">Description</label>
                <textarea name="description" rows="5" class="w-full px-4 py-2 border-2 border-gray-300 rounded focus:outline-none focus:border-blue-500"></textarea>
            </div>

            <div class="mt-6 flex gap-4">
                <button type="submit" class="px-6 py-2 bg-blue-600 text-white rounded hover:bg-blue-700 transition">Add Product</button>
                <a href="?page=products" class="px-6 py-2 bg-gray-300 text-gray-700 rounded hover:bg-gray-400 transition">Cancel</a>
            </div>
        </form>
    </div>

</div>

<!-- <script>
document.getElementById('categoryName').addEventListener('input', function() {
    const slugInput = document.getElementById('categorySlug');
    if (!slugInput.value || slugInput.dataset.autoGenerated === 'true') {
        slugInput.value = this.value.toLowerCase().replace(/[^a-z0-9]+/g, '-').replace(/^-|-$/g, '');
        slugInput.dataset.autoGenerated = 'true';
    }
});

document.getElementById('categorySlug').addEventListener('input', function() {
    this.dataset.autoGenerated = 'false';
});
</script> -->