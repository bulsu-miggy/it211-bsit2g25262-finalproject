<?php
$currentPage = $_GET['page'] ?? 'dashboard';
?>
<aside class="w-64 bg-[#f4ede4] text-[#4a3728]">
    <!-- Logo -->
    <div class="px-6 py-8 border-b border-[#e6ddd3]">
        <h1 class="text-2xl font-bold text-[#4a3728]">LASA FILIPINA</h1>
        <p class="text-[#7d634f] text-sm">Since 1920</p>
    </div>

    <!-- Navigation Menu -->
    <nav class="px-4 py-6">
        <div class="space-y-2">
            <!-- Dashboard -->
            <a href="?page=dashboard" class="flex items-center gap-3 px-4 py-3 rounded transition text-[#4a3728] <?php echo ($currentPage === 'dashboard') ? 'bg-[#d4a373] text-white' : 'hover:bg-[#f0e6d8]'; ?>">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 12l2-3m0 0l7-4 7 4M5 9v10a1 1 0 001 1h12a1 1 0 001-1V9"></path>
                </svg>
                <span>Dashboard</span>
            </a>

            <!-- Products Section -->
            <div>
                <p class="px-4 py-2 text-xs font-semibold text-[#7d634f] uppercase tracking-wider">Products</p>
                <a href="?page=products" class="flex items-center gap-3 px-4 py-3 rounded transition text-[#4a3728] <?php echo ($currentPage === 'products' || $currentPage === 'edit-product') ? 'bg-[#d4a373] text-white' : 'hover:bg-[#f0e6d8]'; ?>">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4"></path>
                    </svg>
                    <span>All Products</span>
                </a>
                <a href="?page=add-product" class="flex items-center gap-3 px-4 py-3 rounded transition text-[#4a3728] <?php echo ($currentPage === 'add-product') ? 'bg-[#d4a373] text-white' : 'hover:bg-[#f0e6d8]'; ?>">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"></path>
                    </svg>
                    <span>Add Product</span>
                </a>
            </div>

            <!-- Categories Section -->
            <div>
                <p class="px-4 py-2 text-xs font-semibold text-[#7d634f] uppercase tracking-wider mt-4">Categories</p>
                <a href="?page=categories" class="flex items-center gap-3 px-4 py-3 rounded transition text-[#4a3728] <?php echo ($currentPage === 'categories' || $currentPage === 'edit-category') ? 'bg-[#d4a373] text-white' : 'hover:bg-[#f0e6d8]'; ?>">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 7h.01M7 3h5c.512 0 1.024.195 1.414.586l7 7a2 2 0 010 2.828l-7 7a2 2 0 01-2.828 0l-7-7A1.994 1.994 0 013 12V7a4 4 0 014-4z"></path>
                    </svg>
                    <span>All Categories</span>
                </a>
                <a href="?page=add-category" class="flex items-center gap-3 px-4 py-3 rounded transition text-[#4a3728] <?php echo ($currentPage === 'add-category') ? 'bg-[#d4a373] text-white' : 'hover:bg-[#f0e6d8]'; ?>">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"></path>
                    </svg>
                    <span>Add Category</span>
                </a>
            </div>

            <!-- Customers Section -->
            <div>
                <p class="px-4 py-2 text-xs font-semibold text-[#7d634f] uppercase tracking-wider mt-4">Customers</p>
                <a href="?page=customers" class="flex items-center gap-3 px-4 py-3 rounded transition text-[#4a3728] <?php echo ($currentPage === 'customers' || $currentPage === 'edit-customer') ? 'bg-[#d4a373] text-white' : 'hover:bg-[#f0e6d8]'; ?>">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z"></path>
                    </svg>
                    <span>All Customers</span>
                </a>
                <a href="?page=add-customer" class="flex items-center gap-3 px-4 py-3 rounded transition text-[#4a3728] <?php echo ($currentPage === 'add-customer') ? 'bg-[#d4a373] text-white' : 'hover:bg-[#f0e6d8]'; ?>">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"></path>
                    </svg>
                    <span>Add Customer</span>
                </a>
            </div>

            <!-- Orders Section - HIWALAY NA ITO -->
            <div>
                <p class="px-4 py-2 text-xs font-semibold text-[#7d634f] uppercase tracking-wider mt-4">Orders</p>
                <a href="?page=order" class="flex items-center gap-3 px-4 py-3 rounded transition text-[#4a3728] <?php echo ($currentPage === 'order') ? 'bg-[#d4a373] text-white' : 'hover:bg-[#f0e6d8]'; ?>">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 11V7a4 4 0 00-8 0v4M5 9h14l1 12H4L5 9z"></path>
                    </svg>
                    <span>Orders</span>
                </a>
            </div>

            <!-- Analytics Section -->
            <div>
                <p class="px-4 py-2 text-xs font-semibold text-[#7d634f] uppercase tracking-wider mt-4">Analytics</p>
                <a href="?page=analytics" class="flex items-center gap-3 px-4 py-3 rounded transition text-[#4a3728] <?php echo ($currentPage === 'analytics') ? 'bg-[#d4a373] text-white' : 'hover:bg-[#f0e6d8]'; ?>">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"></path>
                    </svg>
                    <span>Analytics</span>
                </a>
            </div>

            <!-- Settings Section -->
            <div>
                <p class="px-4 py-2 text-xs font-semibold text-[#7d634f] uppercase tracking-wider mt-4">System</p>
                <a href="?page=settings" class="flex items-center gap-3 px-4 py-3 rounded transition text-[#4a3728] <?php echo ($currentPage === 'settings') ? 'bg-[#d4a373] text-white' : 'hover:bg-[#f0e6d8]'; ?>">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z"></path>
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"></path>
                    </svg>
                    <span>Settings</span>
                </a>
            </div>
        </div>
    </nav>

    <!-- Footer -->
    <div class="absolute bottom-0 w-64 px-4 py-4 border-t border-[#e6ddd3]">
        <a href="logout.php" class="flex items-center gap-3 px-4 py-3 rounded hover:bg-[#f0e6d8] transition text-[#bc8a5f] hover:text-[#4a3728]">
            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1"></path>
            </svg>
            <span>Logout</span>
        </a>
    </div>
</aside>