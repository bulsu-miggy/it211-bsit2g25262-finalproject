<?php
$pdo = getDBConnection();
$status = $_GET['status'] ?? 'all';
$search = $_GET['search'] ?? '';

// Build query for orders list
$sql = "SELECT o.*, CONCAT(c.first_name, ' ', c.last_name) as customer_name 
        FROM orders o 
        LEFT JOIN customers c ON o.customer_id = c.id 
        WHERE 1=1";
$params = [];

if ($status !== 'all') {
    $sql .= " AND o.status = ?";
    $params[] = $status;
}
if ($search) {
    $sql .= " AND (o.order_number LIKE ? OR CONCAT(c.first_name, ' ', c.last_name) LIKE ?)";
    $params[] = "%$search%";
    $params[] = "%$search%";
}

$sql .= " ORDER BY o.created_at DESC";
$stmt = $pdo->prepare($sql);
$stmt->execute($params);
$orders = $stmt->fetchAll();

// Get order counts
$totalOrders = count($orders);
$pendingCount = 0;
$processingCount = 0;
$completedCount = 0;

foreach ($orders as $order) {
    if ($order['status'] == 'pending') $pendingCount++;
    if ($order['status'] == 'processing') $processingCount++;
    if ($order['status'] == 'completed') $completedCount++;
}
?>
<div class="space-y-8">
    <div class="flex items-center justify-between">
        <div>
            <h1 class="text-3xl text-gray-800 mb-2">Orders</h1>
            <p class="text-gray-600">Manage and track all customer orders</p>
        </div>
    </div>

    <!-- Order Stats Grid -->
    <div class="grid grid-cols-1 md:grid-cols-4 gap-6">
        <div class="border-2 border-gray-300 bg-white p-6 rounded">
            <p class="text-sm text-gray-600 mb-1">Total Orders</p>
            <p class="text-2xl text-gray-800"><?php echo number_format($totalOrders); ?></p>
        </div>
        <div class="border-2 border-gray-300 bg-white p-6 rounded">
            <p class="text-sm text-gray-600 mb-1">Pending</p>
            <p class="text-2xl text-yellow-600"><?php echo number_format($pendingCount); ?></p>
        </div>
        <div class="border-2 border-gray-300 bg-white p-6 rounded">
            <p class="text-sm text-gray-600 mb-1">Processing</p>
            <p class="text-2xl text-blue-600"><?php echo number_format($processingCount); ?></p>
        </div>
        <div class="border-2 border-gray-300 bg-white p-6 rounded">
            <p class="text-sm text-gray-600 mb-1">Completed</p>
            <p class="text-2xl text-green-600"><?php echo number_format($completedCount); ?></p>
        </div>
    </div>

    <!-- Filters -->
    <div class="border-2 border-gray-300 bg-white p-6 rounded">
        <form method="GET" class="flex flex-col md:flex-row gap-4">
            <input type="hidden" name="page" value="order">
            <div class="flex-1 flex items-center gap-2 border-2 border-gray-300 bg-gray-50 px-4 py-2 rounded">
                <svg class="w-4 h-4 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"></path>
                </svg>
                <input type="text" name="search" value="<?php echo htmlspecialchars($search); ?>" placeholder="Search orders..." class="flex-1 bg-transparent outline-none text-gray-800">
            </div>
            <div class="flex gap-2">
                <select name="status" class="border-2 border-gray-300 px-4 py-2 rounded bg-white text-gray-800 outline-none">
                    <option value="all" <?php echo $status === 'all' ? 'selected' : ''; ?>>All Status</option>
                    <option value="pending" <?php echo $status === 'pending' ? 'selected' : ''; ?>>Pending</option>
                    <option value="processing" <?php echo $status === 'processing' ? 'selected' : ''; ?>>Processing</option>
                    <option value="shipped" <?php echo $status === 'shipped' ? 'selected' : ''; ?>>Shipped</option>
                    <option value="completed" <?php echo $status === 'completed' ? 'selected' : ''; ?>>Completed</option>
                    <option value="cancelled" <?php echo $status === 'cancelled' ? 'selected' : ''; ?>>Cancelled</option>
                </select>
                <button type="submit" class="border-2 border-gray-800 bg-gray-800 text-white px-4 py-2 rounded hover:bg-gray-700">Filter</button>
                <a href="?page=order" class="border-2 border-gray-300 bg-white text-gray-700 px-4 py-2 rounded hover:bg-gray-50">Clear</a>
            </div>
        </form>
    </div>

    <!-- Orders Table -->
    <div class="border-2 border-gray-300 bg-white rounded overflow-hidden">
        <div class="overflow-x-auto">
            <table class="w-full">
                <thead class="border-b-2 border-gray-300 bg-gray-50">
                    <tr>
                        <th class="text-left px-6 py-4 text-sm text-gray-700">Order ID</th>
                        <th class="text-left px-6 py-4 text-sm text-gray-700">Customer</th>
                        <th class="text-left px-6 py-4 text-sm text-gray-700">Date</th>
                        <th class="text-left px-6 py-4 text-sm text-gray-700">Amount</th>
                        <th class="text-left px-6 py-4 text-sm text-gray-700">Status</th>
                        <th class="text-left px-6 py-4 text-sm text-gray-700">View</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (!empty($orders)): ?>
                        <?php foreach ($orders as $order): ?>
                        <tr class="border-b border-gray-200 hover:bg-gray-50 transition-colors">
                            <td class="px-6 py-4 text-gray-800 font-mono"><?php echo htmlspecialchars($order['order_number']); ?></td>
                            <td class="px-6 py-4 text-gray-800"><?php echo htmlspecialchars($order['customer_name'] ?? 'Guest'); ?></td>
                            <td class="px-6 py-4 text-gray-600"><?php echo date('M d, Y', strtotime($order['created_at'])); ?></td>
                            <td class="px-6 py-4 text-gray-800 font-semibold"><?php echo formatPrice($order['total_amount']); ?></td>
                            <td class="px-6 py-4">
                                <span class="px-3 py-1 rounded text-sm border <?php echo getStatusBadgeClass(ucfirst($order['status'])); ?>">
                                    <?php echo ucfirst($order['status']); ?>
                                </span>
                            </td>
                            <td class="px-6 py-4">
                                <button onclick="viewOrder(<?php echo $order['id']; ?>)" class="text-gray-600 hover:text-gray-800 underline text-sm">View</button>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <tr>
                            <td colspan="6" class="px-6 py-8 text-center text-gray-500">
                                No orders found.
                            </td>
                        </tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<script>
function viewOrder(id) {
    alert('Order ID: ' + id + '\n\nThis would show order details.');
}
</script>