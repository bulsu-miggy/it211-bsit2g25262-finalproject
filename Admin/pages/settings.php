<?php
$error = '';
$success = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    updateSetting('store_name', $_POST['store_name'] ?? '');
    updateSetting('store_email', $_POST['store_email'] ?? '');
    updateSetting('store_description', $_POST['store_description'] ?? '');
    updateSetting('currency', $_POST['currency'] ?? 'USD');
    updateSetting('timezone', $_POST['timezone'] ?? 'America/New_York');
    updateSetting('notify_new_orders', isset($_POST['notify_new_orders']) ? '1' : '0');
    updateSetting('notify_low_stock', isset($_POST['notify_low_stock']) ? '1' : '0');
    updateSetting('notify_weekly_report', isset($_POST['notify_weekly_report']) ? '1' : '0');
    updateSetting('notify_reviews', isset($_POST['notify_reviews']) ? '1' : '0');
    
    $success = 'Settings saved successfully!';
}

$storeName = getSetting('store_name');
$storeEmail = getSetting('store_email');
$storeDescription = getSetting('store_description');
$currency = getSetting('currency');
$timezone = getSetting('timezone');
$notifyNewOrders = getSetting('notify_new_orders') === '1';
$notifyLowStock = getSetting('notify_low_stock') === '1';
$notifyWeeklyReport = getSetting('notify_weekly_report') === '1';
$notifyReviews = getSetting('notify_reviews') === '1';
?>
<div class="space-y-8">
    <div>
        <h1 class="text-3xl text-gray-800 mb-2">Settings</h1>
        <p class="text-gray-600">Manage your store settings and preferences</p>
    </div>

    <?php if ($error): ?>
        <div class="border-2 border-red-300 bg-red-50 text-red-800 p-4 rounded"><?php echo $error; ?></div>
    <?php endif; ?>
    
    <?php if ($success): ?>
        <div class="border-2 border-green-300 bg-green-50 text-green-800 p-4 rounded"><?php echo $success; ?></div>
    <?php endif; ?>

    <form method="POST">
        <!-- General Settings -->
        <div class="border-2 border-gray-300 bg-white p-6 rounded mb-6">
            <h2 class="text-xl text-gray-800 mb-6">General Settings</h2>
            <div class="space-y-6">
                <div>
                    <label class="block text-sm text-gray-700 mb-2">Store Name</label>
                    <input type="text" name="store_name" value="<?php echo htmlspecialchars($storeName); ?>" class="w-full border-2 border-gray-300 px-4 py-2 rounded outline-none focus:border-gray-800">
                </div>
                <div>
                    <label class="block text-sm text-gray-700 mb-2">Store Email</label>
                    <input type="email" name="store_email" value="<?php echo htmlspecialchars($storeEmail); ?>" class="w-full border-2 border-gray-300 px-4 py-2 rounded outline-none focus:border-gray-800">
                </div>
                <div>
                    <label class="block text-sm text-gray-700 mb-2">Store Description</label>
                    <textarea name="store_description" rows="4" class="w-full border-2 border-gray-300 px-4 py-2 rounded outline-none focus:border-gray-800 resize-none"><?php echo htmlspecialchars($storeDescription); ?></textarea>
                </div>
            </div>
        </div>

        <!-- Notifications -->
        <div class="border-2 border-gray-300 bg-white p-6 rounded mb-6">
            <h2 class="text-xl text-gray-800 mb-6">Notifications</h2>
            <div class="space-y-4">
                <label class="flex items-center gap-3 cursor-pointer">
                    <input type="checkbox" name="notify_new_orders" <?php echo $notifyNewOrders ? 'checked' : ''; ?> class="w-5 h-5 border-2 border-gray-300 rounded cursor-pointer">
                    <span class="text-gray-700">Email notifications for new orders</span>
                </label>
                <label class="flex items-center gap-3 cursor-pointer">
                    <input type="checkbox" name="notify_low_stock" <?php echo $notifyLowStock ? 'checked' : ''; ?> class="w-5 h-5 border-2 border-gray-300 rounded cursor-pointer">
                    <span class="text-gray-700">Email notifications for low stock</span>
                </label>
                <label class="flex items-center gap-3 cursor-pointer">
                    <input type="checkbox" name="notify_weekly_report" <?php echo $notifyWeeklyReport ? 'checked' : ''; ?> class="w-5 h-5 border-2 border-gray-300 rounded cursor-pointer">
                    <span class="text-gray-700">Weekly sales reports</span>
                </label>
                <label class="flex items-center gap-3 cursor-pointer">
                    <input type="checkbox" name="notify_reviews" <?php echo $notifyReviews ? 'checked' : ''; ?> class="w-5 h-5 border-2 border-gray-300 rounded cursor-pointer">
                    <span class="text-gray-700">Customer review notifications</span>
                </label>
            </div>
        </div>

        <!-- Currency & Timezone -->
        <div class="border-2 border-gray-300 bg-white p-6 rounded mb-6">
            <h2 class="text-xl text-gray-800 mb-6">Regional Settings</h2>
            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                <div>
                    <label class="block text-sm text-gray-700 mb-2">Currency</label>
                    <select name="currency" class="w-full border-2 border-gray-300 px-4 py-2 rounded outline-none focus:border-gray-800">
                        <option value="USD" <?php echo $currency === 'USD' ? 'selected' : ''; ?>>USD ($)</option>
                        <option value="EUR" <?php echo $currency === 'EUR' ? 'selected' : ''; ?>>EUR (€)</option>
                        <option value="GBP" <?php echo $currency === 'GBP' ? 'selected' : ''; ?>>GBP (£)</option>
                        <option value="JPY" <?php echo $currency === 'JPY' ? 'selected' : ''; ?>>JPY (¥)</option>
                    </select>
                </div>
                <div>
                    <label class="block text-sm text-gray-700 mb-2">Timezone</label>
                    <select name="timezone" class="w-full border-2 border-gray-300 px-4 py-2 rounded outline-none focus:border-gray-800">
                        <option value="America/Los_Angeles" <?php echo $timezone === 'America/Los_Angeles' ? 'selected' : ''; ?>>UTC-08:00 (Pacific)</option>
                        <option value="America/New_York" <?php echo $timezone === 'America/New_York' ? 'selected' : ''; ?>>UTC-05:00 (Eastern)</option>
                        <option value="Europe/London" <?php echo $timezone === 'Europe/London' ? 'selected' : ''; ?>>UTC+00:00 (GMT)</option>
                        <option value="Europe/Paris" <?php echo $timezone === 'Europe/Paris' ? 'selected' : ''; ?>>UTC+01:00 (CET)</option>
                    </select>
                </div>
            </div>
        </div>

        <!-- Save Button -->
        <div class="flex justify-end">
            <button type="submit" class="border-2 border-gray-800 bg-gray-800 text-white px-8 py-3 rounded hover:bg-gray-700 transition-colors flex items-center gap-2">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>
                </svg>
                Save Changes
            </button>
        </div>
    </form>
</div>