<?php
// Get current page title
$pageTitles = [
    'dashboard' => 'Dashboard',
    'products' => 'Products',
    'add-product' => 'Add Product',
    'edit-product' => 'Edit Product',
    'categories' => 'Categories',
    'add-category' => 'Add Category',
    'edit-category' => 'Edit Category',
    'customers' => 'Customers',
    'add-customer' => 'Add Customer',
    'orders' => 'Orders',
    'analytics' => 'Analytics',
    'settings' => 'Settings',
];

$currentPage = $_GET['page'] ?? 'dashboard';
$pageTitle = $pageTitles[$currentPage] ?? 'Dashboard';
?>
<header class="bg-[#fcfaf7] border-b-2 border-[#e6ddd3] px-8 py-4">
    <div class="flex items-center justify-between">
        <div>
            <h1 class="text-2xl text-[#4a3728]"><?php echo htmlspecialchars($pageTitle); ?></h1>
        </div>
        <div class="flex items-center gap-4">
            <div class="w-10 h-10 rounded-full bg-[#f0e6d8] border-2 border-[#e6ddd3]"></div>
            <div>
                <p class="text-sm text-[#7d634f]">Admin User</p>
                <p class="text-xs text-[#a6907c]">Administrator</p>
            </div>
        </div>
    </div>
</header>