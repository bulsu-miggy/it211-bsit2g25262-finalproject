<?php
$pdo = getDBConnection();
$id = $_GET['id'] ?? 0;

$stmt = $pdo->prepare("SELECT * FROM customers WHERE id = ?");
$stmt->execute([$id]);
$customer = $stmt->fetch();

if (!$customer) {
    header('Location: index.php?page=customers');
    exit;
}

$success_message = $_SESSION['success'] ?? null;
$error_message = $_SESSION['error'] ?? null;
unset($_SESSION['success'], $_SESSION['error']);
?>
<div class="max-w-4xl mx-auto">
    <div class="mb-8">
        <div class="flex items-center gap-4">
            <a href="?page=customers" class="text-gray-600 hover:text-gray-800">
                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"></path>
                </svg>
            </a>
            <div>
                <h2 class="text-2xl font-bold text-gray-800 mb-2">Edit Customer</h2>
                <p class="text-gray-600">Update customer information</p>
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
        <form method="POST" action="index.php?page=edit-customer&id=<?php echo $customer['id']; ?>">
            <input type="hidden" name="edit_customer" value="1">
            <input type="hidden" name="customer_id" value="<?php echo $customer['id']; ?>">
            
            <div class="grid grid-cols-2 gap-6">
                <div>
                    <label class="block text-sm font-semibold text-gray-700 mb-2">First Name *</label>
                    <input type="text" name="first_name" value="<?php echo htmlspecialchars($customer['first_name']); ?>" class="w-full px-4 py-2 border-2 border-gray-300 rounded focus:outline-none focus:border-blue-500" required>
                </div>

                <div>
                    <label class="block text-sm font-semibold text-gray-700 mb-2">Last Name *</label>
                    <input type="text" name="last_name" value="<?php echo htmlspecialchars($customer['last_name']); ?>" class="w-full px-4 py-2 border-2 border-gray-300 rounded focus:outline-none focus:border-blue-500" required>
                </div>

                <div>
                    <label class="block text-sm font-semibold text-gray-700 mb-2">Email *</label>
                    <input type="email" name="email" value="<?php echo htmlspecialchars($customer['email']); ?>" class="w-full px-4 py-2 border-2 border-gray-300 rounded focus:outline-none focus:border-blue-500" required>
                </div>

                <div>
                    <label class="block text-sm font-semibold text-gray-700 mb-2">Phone</label>
                    <input type="tel" name="phone" value="<?php echo htmlspecialchars($customer['phone'] ?? ''); ?>" class="w-full px-4 py-2 border-2 border-gray-300 rounded focus:outline-none focus:border-blue-500">
                </div>

                <div>
                    <label class="block text-sm font-semibold text-gray-700 mb-2">Customer Type</label>
                    <select name="customer_type" class="w-full px-4 py-2 border-2 border-gray-300 rounded focus:outline-none focus:border-blue-500">
                        <option value="regular" <?php echo ($customer['customer_type'] ?? 'regular') === 'regular' ? 'selected' : ''; ?>>Regular</option>
                        <option value="vip" <?php echo ($customer['customer_type'] ?? '') === 'vip' ? 'selected' : ''; ?>>VIP</option>
                        <option value="wholesale" <?php echo ($customer['customer_type'] ?? '') === 'wholesale' ? 'selected' : ''; ?>>Wholesale</option>
                    </select>
                </div>

                <div>
                    <label class="block text-sm font-semibold text-gray-700 mb-2">Status</label>
                    <select name="status" class="w-full px-4 py-2 border-2 border-gray-300 rounded focus:outline-none focus:border-blue-500">
                        <option value="active" <?php echo ($customer['status'] ?? 'active') === 'active' ? 'selected' : ''; ?>>Active</option>
                        <option value="inactive" <?php echo ($customer['status'] ?? '') === 'inactive' ? 'selected' : ''; ?>>Inactive</option>
                    </select>
                </div>
            </div>

            <div class="mt-6 flex gap-4">
                <button type="submit" class="px-6 py-2 bg-blue-600 text-white rounded hover:bg-blue-700 transition flex items-center gap-2">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>
                    </svg>
                    Update Customer
                </button>
                <a href="?page=customers" class="px-6 py-2 bg-gray-300 text-gray-700 rounded hover:bg-gray-400 transition">Cancel</a>
            </div>
        </form>
    </div>
</div>