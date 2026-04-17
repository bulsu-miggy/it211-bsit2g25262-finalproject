<?php
$stats = getDashboardStats();
?>
<div class="space-y-8">
    <div>
        <h1 class="text-3xl text-[#4a3728] mb-2">Dashboard</h1>
        <p class="text-[#7d634f]">Welcome back! Here's what's happening today.</p>
    </div>

    <!-- Stats Grid - Naka summary cards -->
    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6">
        <div class="border-2 border-[#e6ddd3] bg-[#ffffff] p-6 rounded">
            <div class="flex items-center justify-between mb-4">
                <svg class="w-6 h-6 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                </svg>
                <span class="text-sm px-2 py-1 rounded border bg-green-100 text-green-800 border-green-300">+20.1%</span>
            </div>
            <p class="text-sm text-[#7d634f] mb-1">Total Revenue</p>
            <p class="text-2xl text-[#4a3728]">₱<?php echo number_format($stats['revenue'] ?? 0, 2); ?></p>
        </div>
        
        <div class="border-2 border-[#e6ddd3] bg-[#ffffff] p-6 rounded">
            <div class="flex items-center justify-between mb-4">
                <svg class="w-6 h-6 text-red-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 11V7a4 4 0 00-8 0v4M5 9h14l1 12H4L5 9z"></path>
                </svg>
                <span class="text-sm px-2 py-1 rounded border bg-red-100 text-red-800 border-red-300">-12.5%</span>
            </div>
            <p class="text-sm text-[#7d634f] mb-1">Total Orders</p>
            <p class="text-2xl text-[#4a3728]"><?php echo number_format($stats['orders'] ?? 0); ?></p>
        </div>
        
        <div class="border-2 border-[#e6ddd3] bg-[#ffffff] p-6 rounded">
            <div class="flex items-center justify-between mb-4">
                <svg class="w-6 h-6 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z"></path>
                </svg>
                <span class="text-sm px-2 py-1 rounded border bg-green-100 text-green-800 border-green-300">+8.3%</span>
            </div>
            <p class="text-sm text-[#7d634f] mb-1">Total Customers</p>
            <p class="text-2xl text-[#4a3728]"><?php echo number_format($stats['customers'] ?? 0); ?></p>
        </div>
        
        <div class="border-2 border-[#e6ddd3] bg-[#ffffff] p-6 rounded">
            <div class="flex items-center justify-between mb-4">
                <svg class="w-6 h-6 text-red-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4"></path>
                </svg>
                <span class="text-sm px-2 py-1 rounded border bg-red-100 text-red-800 border-red-300">-2.4%</span>
            </div>
            <p class="text-sm text-[#7d634f] mb-1">Total Products</p>
            <p class="text-2xl text-[#4a3728]"><?php echo number_format($stats['products'] ?? 0); ?></p>
        </div>
    </div>

    <!-- CHARTS SECTION - PURO CHARTS LANG -->
    <div class="grid grid-cols-1 lg:grid-cols-2 gap-6" id="dashboardCharts"
         data-revenue-labels='<?php echo json_encode($stats['revenue_labels'] ?? []); ?>'
         data-revenue-data='<?php echo json_encode($stats['revenue_data'] ?? []); ?>'
         data-orders-labels='<?php echo json_encode($stats['orders_labels'] ?? []); ?>'
         data-orders-data='<?php echo json_encode($stats['orders_data'] ?? []); ?>'
         data-category-labels='<?php echo json_encode($stats['category_labels'] ?? []); ?>'
         data-category-data='<?php echo json_encode($stats['category_data'] ?? []); ?>'
         data-status-labels='<?php echo json_encode($stats['status_labels'] ?? []); ?>'
         data-status-data='<?php echo json_encode($stats['status_data'] ?? []); ?>'>
        
        <!-- Bar Chart - Weekly Orders -->
        <div class="border-2 border-[#e6ddd3] bg-[#ffffff] p-6 rounded">
            <div class="mb-4">
                <h2 class="text-xl font-semibold text-[#4a3728]">Weekly Orders</h2>
                <p class="text-sm text-[#7d634f]">Last 7 days</p>
            </div>
            <div class="relative h-64">
                <canvas id="weeklyOrdersChart"></canvas>
            </div>
        </div>

        <!-- Line Chart - Revenue Trend -->
        <div class="border-2 border-[#e6ddd3] bg-[#ffffff] p-6 rounded">
            <div class="mb-4">
                <h2 class="text-xl font-semibold text-[#4a3728]">Revenue Trend</h2>
                <p class="text-sm text-[#7d634f]">Last 6 months</p>
            </div>
            <div class="relative h-64">
                <canvas id="revenueTrendChart"></canvas>
            </div>
        </div>

        <!-- Doughnut Chart - Products by Category -->
        <div class="border-2 border-[#e6ddd3] bg-[#ffffff] p-6 rounded">
            <div class="mb-4">
                <h2 class="text-xl font-semibold text-[#4a3728]">Products by Category</h2>
                <p class="text-sm text-[#7d634f]">Distribution</p>
            </div>
            <div class="relative h-64">
                <canvas id="categoryDistributionChart"></canvas>
            </div>
        </div>

        <!-- Pie Chart - Order Status -->
        <div class="border-2 border-[#e6ddd3] bg-[#ffffff] p-6 rounded">
            <div class="mb-4">
                <h2 class="text-xl font-semibold text-[#4a3728]">Order Status</h2>
                <p class="text-sm text-[#7d634f]">Current breakdown</p>
            </div>
            <div class="relative h-64">
                <canvas id="orderStatusChart"></canvas>
            </div>
        </div>
    </div>
</div>