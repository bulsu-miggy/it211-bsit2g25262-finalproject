<?php
$pdo = getDBConnection();
$id = $_GET['id'] ?? 0;

$stmt = $pdo->prepare("SELECT * FROM categories WHERE id = ?");
$stmt->execute([$id]);
$category = $stmt->fetch();

if (!$category) {
    header('Location: index.php?page=categories');
    exit;
}
?>
<div class="max-w-4xl mx-auto">
    <div class="mb-8">
        <a href="?page=categories" class="text-blue-600 hover:underline">← Back to Categories</a>
        <h2 class="text-2xl font-bold text-gray-800 mb-2 mt-4">Edit Category</h2>
    </div>

    <div class="bg-white rounded-lg border-2 border-gray-300 p-8">
        <form method="POST" action="">
            <input type="hidden" name="edit_category" value="1">
            <input type="hidden" name="category_id" value="<?php echo $category['id']; ?>">
            
            <div class="mb-4">
                <label class="block mb-2">Category Name *</label>
                <input type="text" name="name" value="<?php echo htmlspecialchars($category['name']); ?>" class="w-full px-4 py-2 border rounded" required>
            </div>


            <div class="mb-4">
                <label class="block mb-2">Description</label>
                <textarea name="description" rows="4" class="w-full px-4 py-2 border rounded"><?php echo htmlspecialchars($category['description'] ?? ''); ?></textarea>
            </div>

            <div class="mb-4">
                <label>
                    <input type="checkbox" name="is_active" value="1" <?php echo $category['is_active'] ? 'checked' : ''; ?>>
                    Active
                </label>
            </div>

            <div class="flex gap-4">
                <button type="submit" class="px-6 py-2 bg-blue-600 text-white rounded hover:bg-blue-700">Update Category</button>
                <a href="?page=categories" class="px-6 py-2 bg-gray-300 rounded hover:bg-gray-400">Cancel</a>
            </div>
        </form>
    </div>
</div>