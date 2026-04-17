<?php
$pdo = getDBConnection();

$stmt = $pdo->query("SELECT * FROM customers ORDER BY created_at DESC");
$customers = $stmt->fetchAll();
?>
<div class="space-y-8">
    <div class="flex items-center justify-between">
        <div>
            <h1 class="text-3xl text-gray-800 mb-2">Customers</h1>
            <p class="text-gray-600">Manage your customer database</p>
        </div>
        <a href="?page=add-customer" class="border-2 border-gray-800 bg-gray-800 text-white px-6 py-2.5 rounded hover:bg-gray-700 transition-colors flex items-center gap-2">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"></path>
            </svg>
            Add Customer
        </a>
    </div>

    <div class="border-2 border-gray-300 bg-white p-6 rounded">
        <div class="flex items-center gap-2 border-2 border-gray-300 bg-gray-50 px-4 py-2 rounded">
            <svg class="w-4 h-4 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"></path>
            </svg>
            <input type="text" id="searchInput" placeholder="Search customers..." class="flex-1 bg-transparent outline-none text-gray-800">
        </div>
    </div>

    <div class="border-2 border-gray-300 bg-white rounded overflow-hidden">
        <div class="overflow-x-auto">
            <table class="w-full" id="customersTable">
                <thead class="border-b-2 border-gray-300 bg-gray-50">
                    <tr>
                        <th class="text-left px-6 py-4 text-sm text-gray-700">Name</th>
                        <th class="text-left px-6 py-4 text-sm text-gray-700">Email</th>
                        <th class="text-left px-6 py-4 text-sm text-gray-700">Phone</th>
                        <th class="text-left px-6 py-4 text-sm text-gray-700">Type</th>
                        <th class="text-left px-6 py-4 text-sm text-gray-700">Status</th>
                        <th class="text-left px-6 py-4 text-sm text-gray-700">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($customers as $customer): ?>
                    <tr id="customer-row-<?php echo $customer['id']; ?>">
                        <td class="px-6 py-4">
                            <div class="flex items-center gap-3">
                                <div class="w-10 h-10 bg-gray-200 border-2 border-gray-300 rounded-full flex items-center justify-center">
                                    <span class="text-gray-600 font-medium">
                                        <?php echo strtoupper(substr($customer['first_name'], 0, 1) . substr($customer['last_name'], 0, 1)); ?>
                                    </span>
                                </div>
                                <span class="text-gray-800"><?php echo htmlspecialchars($customer['first_name'] . ' ' . $customer['last_name']); ?></span>
                            </div>
                        </td>
                        <td class="px-6 py-4 text-gray-600"><?php echo htmlspecialchars($customer['email']); ?></td>
                        <td class="px-6 py-4 text-gray-600"><?php echo htmlspecialchars($customer['phone'] ?? 'N/A'); ?></td>
                        <td class="px-6 py-4">
                            <span class="px-3 py-1 rounded text-sm border <?php echo getStatusBadgeClass(ucfirst($customer['customer_type'])); ?>">
                                <?php echo ucfirst($customer['customer_type']); ?>
                            </span>
                        </td>
                        <td class="px-6 py-4">
                            <span class="px-3 py-1 rounded text-sm border <?php echo getStatusBadgeClass(ucfirst($customer['status'])); ?>">
                                <?php echo ucfirst($customer['status']); ?>
                            </span>
                        </td>
                        <td class="px-6 py-4">
                            <a href="?page=edit-customer&id=<?php echo $customer['id']; ?>" class="text-blue-600 hover:text-blue-800 underline text-sm mr-3">Edit</a>
                            <form method="POST" style="display: inline;" onsubmit="return confirm('Are you sure you want to delete this customer?');">
                                <input type="hidden" name="delete_customer_id" value="<?php echo $customer['id']; ?>">
                                <button type="submit" class="text-red-600 hover:text-red-800 underline text-sm">Delete</button>
                            </form>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                    <?php if (empty($customers)): ?>
                    <tr>
                        <td colspan="6" class="px-6 py-8 text-center text-gray-500">
                            No customers found. <a href="?page=add-customer" class="text-blue-600 hover:underline">Add your first customer</a>.
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
    const rows = document.querySelectorAll('#customersTable tbody tr');
    
    rows.forEach(row => {
        const name = row.querySelector('td:first-child span:last-child').textContent.toLowerCase();
        const email = row.querySelector('td:nth-child(2)').textContent.toLowerCase();
        if (name.includes(searchText) || email.includes(searchText)) {
            row.style.display = '';
        } else {
            row.style.display = 'none';
        }
    });
});
</script>