<?php
$pdo = getDBConnection();

$stmt = $pdo->query("SELECT c.*, COUNT(p.id) as product_count 
                      FROM categories c 
                      LEFT JOIN products p ON c.id = p.category_id 
                      GROUP BY c.id 
                      ORDER BY c.created_at DESC");
$categories = $stmt->fetchAll();

$success_message = $_SESSION['success'] ?? null;
$error_message = $_SESSION['error'] ?? null;
unset($_SESSION['success'], $_SESSION['error']);
?>
<div class="space-y-8">
    <div class="flex items-center justify-between">
        <div>
            <h1 class="text-3xl text-gray-800 mb-2">Categories</h1>
            <p class="text-gray-600">Organize your products into categories</p>
        </div>
        <a href="?page=add-category" class="border-2 border-gray-800 bg-gray-800 text-white px-6 py-2.5 rounded hover:bg-gray-700 transition-colors flex items-center gap-2">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"></path>
            </svg>
            Add Category
        </a>
    </div>

    <?php if ($success_message): ?>
        <div class="border-2 border-green-300 bg-green-50 text-green-800 p-4 rounded flex items-center gap-2">
            <svg class="w-5 h-5 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path>
            </svg>
            <span><?php echo htmlspecialchars($success_message); ?></span>
        </div>
    <?php endif; ?>

    <?php if ($error_message): ?>
        <div class="border-2 border-red-300 bg-red-50 text-red-800 p-4 rounded flex items-center gap-2">
            <svg class="w-5 h-5 text-red-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>
            </svg>
            <span><?php echo htmlspecialchars($error_message); ?></span>
        </div>
    <?php endif; ?>

    <div class="border-2 border-gray-300 bg-white p-6 rounded">
        <div class="flex items-center gap-2 border-2 border-gray-300 bg-gray-50 px-4 py-2 rounded">
            <svg class="w-4 h-4 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"></path>
            </svg>
            <input type="text" id="searchInput" placeholder="Search categories..." class="flex-1 bg-transparent outline-none text-gray-800">
        </div>
    </div>

    <div class="border-2 border-gray-300 bg-white rounded overflow-hidden">
        <div class="overflow-x-auto">
            <table class="w-full" id="categoriesTable">
                <thead class="border-b-2 border-gray-300 bg-gray-50">
                    <tr>
                        <th class="text-left px-6 py-4 text-sm text-gray-700">Category Name</th>
                        <th class="text-left px-6 py-4 text-sm text-gray-700">Description</th>
                        <th class="text-left px-6 py-4 text-sm text-gray-700">Products</th>
                        <th class="text-left px-6 py-4 text-sm text-gray-700">Status</th>
                        <th class="text-left px-6 py-4 text-sm text-gray-700">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($categories as $category): ?>
                    <tr id="category-row-<?php echo $category['id']; ?>">
                        <td class="px-6 py-4">
                            <span class="text-gray-800 font-medium"><?php echo htmlspecialchars($category['name']); ?></span>
                        </td>
                        <td class="px-6 py-4 text-gray-600">
                            <?php 
                            $desc = $category['description'] ?? '';
                            echo htmlspecialchars(strlen($desc) > 40 ? substr($desc, 0, 40) . '...' : ($desc ?: '—'));
                            ?>
                        </td>
                        <td class="px-6 py-4 text-gray-600"><?php echo $category['product_count']; ?></td>
                        <td class="px-6 py-4">
                            <span class="px-3 py-1 rounded text-sm border <?php echo $category['is_active'] ? 'bg-green-100 text-green-800 border-green-200' : 'bg-red-100 text-red-800 border-red-200'; ?>">
                                <?php echo $category['is_active'] ? 'Active' : 'Inactive'; ?>
                            </span>
                        </td>
                        <td class="px-6 py-4">
                            <a href="?page=edit-category&id=<?php echo $category['id']; ?>" class="text-blue-600 hover:text-blue-800 underline text-sm mr-3">Edit</a>
                            <form method="POST" style="display: inline;" onsubmit="return confirm('Are you sure you want to delete this category?');">
                                <input type="hidden" name="delete_category_id" value="<?php echo $category['id']; ?>">
                                <button type="submit" class="text-red-600 hover:text-red-800 underline text-sm">Delete</button>
                            </form>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                    <?php if (empty($categories)): ?>
                    <tr>
                        <td colspan="6" class="px-6 py-8 text-center text-gray-500">
                            No categories found. Click "Add Category" to create one.
                        </td>
                    </tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<script>
document.getElementById('searchInput').addEventListener('keyup', function() {
    const searchText = this.value.toLowerCase();
    const rows = document.querySelectorAll('#categoriesTable tbody tr');
    
    rows.forEach(row => {
        const name = row.querySelector('td:first-child').textContent.toLowerCase();
        row.style.display = name.includes(searchText) ? '' : 'none';
    });
});
</script>