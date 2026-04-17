<?php
$stats = getDashboardStats();
$monthlyRevenue = getMonthlyRevenue(12);
$orderStatusCounts = getOrderStatusCounts();
$dailyRevenue = getDailyRevenue(30);
$dailyOrders = getDailyOrderCount(30);
$topProducts = getTopProductsByRevenue(5);
$categoryRevenue = getCategoryRevenueBreakdown();
$topCustomers = getTopCustomersBySpending(5);
$customerGrowth = getCustomerGrowth(12);
?>
<div class="space-y-8">
    <div>
        <h1 class="text-3xl text-gray-800 mb-2">Analytics</h1>
        <p class="text-gray-600">Detailed insights and performance metrics</p>
    </div>

    <!-- Key Metrics -->
    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6">
        <div class="border-2 border-gray-300 bg-white p-6 rounded">
            <div class="flex items-center justify-between mb-4">
                <svg class="w-6 h-6 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                </svg>
                <span class="text-sm px-2 py-1 rounded border bg-green-100 text-green-800 border-green-300">+12.5%</span>
            </div>
            <p class="text-sm text-gray-600 mb-1">Total Revenue</p>
            <p class="text-2xl text-gray-800"><?php echo formatPrice($stats['revenue']); ?></p>
        </div>

        <div class="border-2 border-gray-300 bg-white p-6 rounded">
            <div class="flex items-center justify-between mb-4">
                <svg class="w-6 h-6 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 11V7a4 4 0 00-8 0v4M5 9h14l1 12H4L5 9z"></path>
                </svg>
                <span class="text-sm px-2 py-1 rounded border bg-green-100 text-green-800 border-green-300">+8.3%</span>
            </div>
            <p class="text-sm text-gray-600 mb-1">Total Orders</p>
            <p class="text-2xl text-gray-800"><?php echo number_format($stats['orders']); ?></p>
        </div>

        <div class="border-2 border-gray-300 bg-white p-6 rounded">
            <div class="flex items-center justify-between mb-4">
                <svg class="w-6 h-6 text-purple-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z"></path>
                </svg>
                <span class="text-sm px-2 py-1 rounded border bg-green-100 text-green-800 border-green-300">+15.7%</span>
            </div>
            <p class="text-sm text-gray-600 mb-1">Total Customers</p>
            <p class="text-2xl text-gray-800"><?php echo number_format($stats['customers']); ?></p>
        </div>

        <div class="border-2 border-gray-300 bg-white p-6 rounded">
            <div class="flex items-center justify-between mb-4">
                <svg class="w-6 h-6 text-orange-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4"></path>
                </svg>
                <span class="text-sm px-2 py-1 rounded border bg-red-100 text-red-800 border-red-300">-2.4%</span>
            </div>
            <p class="text-sm text-gray-600 mb-1">Total Products</p>
            <p class="text-2xl text-gray-800"><?php echo number_format($stats['products']); ?></p>
        </div>
    </div>

    <!-- Charts Section -->
    <div id="analyticsCharts"
         data-revenue-labels='<?php echo json_encode($monthlyRevenue['labels']); ?>'
         data-revenue-data='<?php echo json_encode($monthlyRevenue['data']); ?>'
         data-daily-revenue-labels='<?php echo json_encode($dailyRevenue['labels']); ?>'
         data-daily-revenue-data='<?php echo json_encode($dailyRevenue['data']); ?>'
         data-daily-orders-labels='<?php echo json_encode($dailyOrders['labels']); ?>'
         data-daily-orders-data='<?php echo json_encode($dailyOrders['data']); ?>'
         data-customer-labels='<?php echo json_encode($customerGrowth['labels']); ?>'
         data-customer-data='<?php echo json_encode($customerGrowth['data']); ?>'
         data-status-labels='<?php echo json_encode($orderStatusCounts['labels']); ?>'
         data-status-data='<?php echo json_encode($orderStatusCounts['data']); ?>'
         data-top-products-labels='<?php echo json_encode($topProducts['labels']); ?>'
         data-top-products-data='<?php echo json_encode($topProducts['data']); ?>'
         data-category-labels='<?php echo json_encode($categoryRevenue['labels']); ?>'
         data-category-data='<?php echo json_encode($categoryRevenue['data']); ?>'
         data-top-customers-labels='<?php echo json_encode($topCustomers['labels']); ?>'
         data-top-customers-data='<?php echo json_encode($topCustomers['data']); ?>'>
        
        <!-- Row 1: Monthly Revenue & Daily Revenue -->
        <div class="grid grid-cols-1 lg:grid-cols-2 gap-6 mb-6">
            <div class="border-2 border-gray-300 bg-white p-6 rounded">
                <div class="mb-4">
                    <h2 class="text-xl font-semibold text-gray-800">Monthly Revenue</h2>
                    <p class="text-sm text-gray-600">Last 12 months</p>
                </div>
                <div class="relative h-64">
                    <canvas id="revenueChart"></canvas>
                </div>
            </div>

            <div class="border-2 border-gray-300 bg-white p-6 rounded">
                <div class="mb-4">
                    <h2 class="text-xl font-semibold text-gray-800">Daily Revenue</h2>
                    <p class="text-sm text-gray-600">Last 30 days</p>
                </div>
                <div class="relative h-64">
                    <canvas id="dailyRevenueChart"></canvas>
                </div>
            </div>
        </div>

        <!-- Row 2: Daily Orders & Customer Growth -->
        <div class="grid grid-cols-1 lg:grid-cols-2 gap-6 mb-6">
            <div class="border-2 border-gray-300 bg-white p-6 rounded">
                <div class="mb-4">
                    <h2 class="text-xl font-semibold text-gray-800">Daily Orders</h2>
                    <p class="text-sm text-gray-600">Last 30 days</p>
                </div>
                <div class="relative h-64">
                    <canvas id="dailyOrdersChart"></canvas>
                </div>
            </div>

            <div class="border-2 border-gray-300 bg-white p-6 rounded">
                <div class="mb-4">
                    <h2 class="text-xl font-semibold text-gray-800">Customer Growth</h2>
                    <p class="text-sm text-gray-600">New customers per month</p>
                </div>
                <div class="relative h-64">
                    <canvas id="customerGrowthChart"></canvas>
                </div>
            </div>
        </div>

        <!-- Row 3: Order Status & Top Products -->
        <div class="grid grid-cols-1 lg:grid-cols-2 gap-6 mb-6">
            <div class="border-2 border-gray-300 bg-white p-6 rounded">
                <div class="mb-4">
                    <h2 class="text-xl font-semibold text-gray-800">Order Status</h2>
                    <p class="text-sm text-gray-600">Distribution by status</p>
                </div>
                <div class="relative h-64">
                    <canvas id="orderStatusChart"></canvas>
                </div>
            </div>

            <div class="border-2 border-gray-300 bg-white p-6 rounded">
                <div class="mb-4">
                    <h2 class="text-xl font-semibold text-gray-800">Top Products</h2>
                    <p class="text-sm text-gray-600">By revenue</p>
                </div>
                <div class="relative h-64">
                    <canvas id="topProductsChart"></canvas>
                </div>
            </div>
        </div>

        <!-- Row 4: Category Revenue & Top Customers -->
        <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
            <div class="border-2 border-gray-300 bg-white p-6 rounded">
                <div class="mb-4">
                    <h2 class="text-xl font-semibold text-gray-800">Revenue by Category</h2>
                    <p class="text-sm text-gray-600">Category breakdown</p>
                </div>
                <div class="relative h-64">
                    <canvas id="categoryChart"></canvas>
                </div>
            </div>

            <div class="border-2 border-gray-300 bg-white p-6 rounded">
                <div class="mb-4">
                    <h2 class="text-xl font-semibold text-gray-800">Top Customers</h2>
                    <p class="text-sm text-gray-600">By total spending</p>
                </div>
                <div class="relative h-64">
                    <canvas id="topCustomersChart"></canvas>
                </div>
            </div>
        </div>
    </div>
</div>