<?php
/**
 * SESSION MANAGEMENT
 * Ensures the session is active and verifies if the user is authorized to view this page.
 */
if (session_status() === PHP_SESSION_NONE) { session_start(); }
if (!isset($_SESSION['user_id'])) { header("Location: login.php"); exit(); }

/**
 * DATABASE CONNECTION & HEADER LOADING
 * Connects to the DB and selects the navigation bar based on login status.
 */
include 'db/connection.php';
$is_logged_in = isset($_SESSION['user_id']);

// Load Headers
if ($is_logged_in) {
    include 'includes/member_header.php';
} else {
    include 'guest_header/guest_header.php';
}

$user_id = $_SESSION['user_id'];

/**
 * DATA FETCHING - SECTION 1: USER DETAILS
 * Retrieves the user's name and registration date for the profile display.
 */
$user_stmt = $conn->prepare(
    "SELECT l.full_name, l.email, l.created_at, pd.username, pd.gender, pd.phone AS phone 
     FROM login l 
     LEFT JOIN profile_details pd ON pd.user_id = l.user_id 
     WHERE l.user_id = ?"
);
$user_stmt->execute([$user_id]);
$user_data = $user_stmt->fetch();

// Security fallback: if session exists but user is missing from DB, force logout.
if (!$user_data) {
    session_destroy();
    header("Location: login.php");
    exit();
}

/**
 * DATA FETCHING - SECTION 2: STATISTICS
 * Aggregates counts for orders and addresses to show on the Overview dashboard.
 */
$count_stmt = $conn->prepare("SELECT COUNT(*) as total FROM orders WHERE user_id = ?");
$count_stmt->execute([$user_id]);
$order_stats = $count_stmt->fetch();

$addr_query = $conn->prepare("SELECT COUNT(*) as total FROM user_addresses WHERE user_id = ?");
$addr_query->execute([$user_id]);
$addr_stats = $addr_query->fetch();

/**
 * DATA FETCHING - SECTION 3: RECENT ACTIVITY
 * Fetches the 2 most recent orders to provide a quick snapshot on the home tab.
 */
$recent_orders_stmt = $conn->prepare("SELECT * FROM orders WHERE user_id = ? ORDER BY created_at DESC LIMIT 2");
$recent_orders_stmt->execute([$user_id]);
$recent_orders = $recent_orders_stmt->fetchAll();
?>

<!-- EXTERNAL ASSETS
     Loads jQuery for DOM manipulation and SweetAlert2 for stylish notifications. -->
<script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<script src="js/profile.js?v=1.1"></script>

<div class="profile-wrapper">
    <!-- PAGE TITLE -->
    <div class="profile-header">
        <h1>My Profile</h1>
        <p>Manage your account and preferences</p>
    </div>

    <div class="profile-grid">
        <!-- SIDEBAR NAVIGATION -->
        <aside class="sidebar-card">
            <div class="user-welcome">
                <div class="user-avatar">
                    <!-- User Icon SVG -->
                    <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="#A0A0A0" stroke-width="2">
                        <path d="M20 21v-2a4 4 0 0 0-4-4H8a4 4 0 0 0-4 4v2"/><circle cx="12" cy="7" r="4"/>
                    </svg>
                </div>
                <div class="user-info">
                    <div class="welcome-text">Welcome</div>
                    <div class="user-name"><?= htmlspecialchars($user_data['full_name']) ?></div>
                </div>
            </div>
            <nav class="nav-menu">
                <!-- Javascript 'openTab' handles switching between these sections -->
                <button type="button" class="nav-btn active" data-tab="overview">Overview</button>
                <button type="button" class="nav-btn" data-tab="orders">Order History</button>
                <button type="button" class="nav-btn" data-tab="addresses">Addresses</button>
                <button type="button" class="nav-btn" data-tab="settings">Settings</button>
            </nav>
        </aside>

        <!-- MAIN VIEWPORT -->
        <main>
            <!-- OVERVIEW TAB: Dashboard summary -->
            <div id="overview" class="tab-content active">
                <div class="content-card">
                    <h2>Account Overview</h2>
                    <div class="stats-grid">
                        <div class="stat-tile">
                            <div class="stat-label">Member Since</div>
                            <div class="stat-value"><?= date("F Y", strtotime($user_data['created_at'])) ?></div>
                        </div>
                        <div class="stat-tile">
                            <div class="stat-label">Total Orders</div>
                            <div class="stat-value"><?= (int)$order_stats['total'] ?> orders</div>
                        </div>
                        <div class="stat-tile">
                            <div class="stat-label">Saved Addresses</div>
                            <div class="stat-value"><?= (int)$addr_stats['total'] ?> addresses</div>
                        </div>
                    </div>
                </div>
                
                <!-- RECENT ORDERS SNAPSHOT -->
                <div class="content-card">
                    <div class="flex-between">
                        <h2>Recent Orders</h2>
                        <a href="#" class="view-all-link" data-tab="orders">View All</a>
                    </div>
                    <?php if($recent_orders): foreach($recent_orders as $ro):
                        $ro_status = !empty($ro['status']) ? $ro['status'] : 'Pending';
                        $ro_status_class = strtolower(str_replace(' ', '-', $ro_status));
                    ?>
                        <div class="order-row">
                            <div>
                                <div class="order-id"><?= htmlspecialchars($ro['order_number'] ?? 'ORD-'.$ro['order_id']) ?></div>
                                <div class="order-date"><?= date("Y-m-d", strtotime($ro['created_at'])) ?></div>
                            </div>
                            <div class="status-badge status-<?= htmlspecialchars($ro_status_class) ?>"><?= htmlspecialchars($ro_status) ?></div>
                        </div>
                    <?php endforeach; else: ?>
                        <p class="note-muted">No recent orders found.</p>
                    <?php endif; ?>
                </div>
            </div>

            <!-- ORDERS TAB: Complete Order History -->
            <div id="orders" class="tab-content">
                <div class="content-card">
                    <h2>Order History</h2>
                    <div class="history-toolbar">
                        <div class="history-tabs">
                            <a href="#" class="order-status-tab active" data-status="">All</a>
                            <a href="#" class="order-status-tab" data-status="pending">Pending</a>
                            <a href="#" class="order-status-tab" data-status="processing">Processing</a>
                            <a href="#" class="order-status-tab" data-status="completed">Completed</a>
                            <a href="#" class="order-status-tab" data-status="cancelled">Cancelled</a>
                        </div>
                    </div>
                    <p class="note-muted">Review and track your past purchases.</p>
                    <?php
                    $pk = "product_id";
                    $check = $conn->query("SHOW COLUMNS FROM candles LIKE 'product_id'");
                    if ($check->rowCount() == 0) $pk = "id";

                    $full_orders_stmt = $conn->prepare(
                        "SELECT o.*, 
                            (SELECT p.name FROM order_items oi JOIN candles p ON oi.product_id = p.$pk WHERE oi.order_id = o.order_id LIMIT 1) AS first_item_name, 
                            (SELECT SUM(quantity) FROM order_items WHERE order_id = o.order_id) AS total_items 
                        FROM orders o 
                        WHERE o.user_id = ? 
                        ORDER BY o.created_at DESC"
                    );
                    $full_orders_stmt->execute([$user_id]);
                    $all_orders = $full_orders_stmt->fetchAll();
                    
                    if($all_orders): foreach($all_orders as $order):
                        $order_status_raw = !empty($order['status']) ? trim($order['status']) : 'Pending';
                        $order_status = ucfirst(strtolower($order_status_raw));
                        $order_status_class = strtolower(str_replace(' ', '-', $order_status));
                        $order_label = !empty($order['first_item_name']) ? $order['first_item_name'] : ($order['order_number'] ?? 'Order #'.$order['order_id']);
                    ?>
                        <div class="order-row" data-status="<?= htmlspecialchars($order_status_class) ?>">
                            <div class="order-row-header">
                                <div>
                                    <div class="order-id order-no-text">#<?= htmlspecialchars($order['order_number'] ?? $order['order_id']) ?></div>
                                    <div class="order-date order-date-text">Ordered on <?= date("Y-m-d", strtotime($order['created_at'])) ?></div>
                                </div>
                                <div class="status-badge status-<?= htmlspecialchars($order_status_class) ?>"><?= htmlspecialchars($order_status) ?></div>
                            </div>

                            <div class="order-item-name"><?= htmlspecialchars($order_label) ?></div>
                            <div class="order-divider"></div>
                            <div class="order-footer">
                                <div class="order-total">Total: <span class="order-total-text">₱<?= number_format($order['total_amount'] ?? 0, 2) ?></span></div>
                                <div class="order-actions">
                                    <a href="#" class="btn-primary js-view-order" data-order-id="<?= (int)$order['order_id'] ?>">View Details</a>
                                    <?php
                                        switch ($order_status_class) {
                                            case 'pending':
                                                echo '<button type="button" class="cancel-order-btn" data-order-id="'.(int) $order['order_id'].'">Cancel Order</button>';
                                                break;
                                            case 'completed':
                                                echo '<a href="shop.php" class="btn-secondary">Buy Again</a>';
                                                echo '<button type="button" class="btn-secondary-alt">Rate</button>';
                                                break;
                                            case 'cancelled':
                                                echo '<a href="shop.php" class="btn-secondary">Re-order</a>';
                                                break;
                                            default:
                                                break;
                                        }
                                    ?>
                                </div>
                            </div>
                        </div>
                    <?php endforeach; else: ?>
                        <div class="empty-orders">
                            <p class="note-muted font-italic">You haven't placed any orders yet.</p>
                            <a href="shop.php" class="btn-primary btn-start-shopping">Start Shopping</a>
                        </div>
                    <?php endif; ?>
                </div>
            </div>

            <!-- ADDRESSES TAB: Management of shipping locations -->
            <div id="addresses" class="tab-content">
                <div class="content-card">
                    <div class="address-header-row">
                        <h2>Saved Addresses</h2>
                        <button class="btn-dark">+ Add New</button>
                    </div>
                    <div class="addr-grid">
                        <?php
                        $addr_list_stmt = $conn->prepare("SELECT * FROM user_addresses WHERE user_id = ? ORDER BY address_id DESC");
                        $addr_list_stmt->execute([$user_id]);
                        $addresses = $addr_list_stmt->fetchAll();
                        
                        if($addresses): foreach($addresses as $addr): ?>
                            <div class="addr-card">
                                <!-- CRITICAL: This hidden input allows JS to find the ID when clicking Edit/Remove -->
                                <input type="hidden" name="address_id" value="<?= $addr['address_id'] ?>">
                                
                                <?php if(isset($addr['is_default']) && $addr['is_default']): ?><span class="default-tag">Default</span><?php endif; ?>
                                
                                <div class="addr-label-text"><?= htmlspecialchars($addr['label'] ?? 'Shipping Address') ?></div>
                                <div class="address-detail-text">
                                    <strong class="addr-name-text"><?= htmlspecialchars($addr['full_name']) ?></strong><br>
                                    <span class="addr-street-text"><?= htmlspecialchars($addr['street_address']) ?></span><br>
                                    <span class="addr-city-text"><?= htmlspecialchars($addr['city']) ?></span>, <span class="addr-zip-text"><?= htmlspecialchars($addr['zip_code']) ?></span><br>
                                    Tel: <span class="addr-phone-text"><?= htmlspecialchars($addr['phone_number']) ?></span>
                                </div>
                                <div class="address-actions">
                                    <a href="#" class="js-edit-address">Edit</a>
                                    <a href="#" class="danger">Remove</a>
                                </div>
                            </div>
                        <?php endforeach; else: ?>
                            <p class="note-muted grid-span-2 font-italic">No addresses saved yet.</p>
                        <?php endif; ?>
                    </div>
                </div>
            </div>

            <!-- SETTINGS TAB: Account security and deletion -->
            <div id="settings" class="tab-content">
                <div class="content-card">
                    <h2>Account Settings</h2>
                    <div class="settings-panel settings-panel-personal">
                        <div class="settings-panel-header">Personal Information</div>
                        <div class="settings-panel-note">Update your account details and keep your profile information current.</div>
                        <form id="personal-info-form">
                            <div class="form-row-settings">
                                <label for="settings-username">Username</label>
                                <input type="text" id="settings-username" name="username" value="<?= htmlspecialchars($user_data['username'] ?? '') ?>" placeholder="Choose a username">
                            </div>
                            <div class="form-row-settings">
                                <label for="settings-full-name">Full Name</label>
                                <input type="text" id="settings-full-name" name="full_name" value="<?= htmlspecialchars($user_data['full_name']) ?>" required>
                            </div>
                            <div class="form-row-settings">
                                <label for="settings-email">Email Address</label>
                                <input type="email" id="settings-email" name="email" value="<?= htmlspecialchars($user_data['email']) ?>" required>
                            </div>
                            <div class="form-row-settings">
                                <label for="settings-phone">Phone Number</label>
                                <input type="tel" id="settings-phone" name="phone" value="<?= htmlspecialchars($user_data['phone'] ?? '') ?>" placeholder="09XXXXXXXXX">
                            </div>
                            <div class="form-row-settings">
                                <label for="settings-gender">Gender</label>
                                <select id="settings-gender" name="gender">
                                    <option value="" <?= ($user_data['gender'] ?? '') === '' ? 'selected' : '' ?>>Select gender</option>
                                    <option value="Male" <?= ($user_data['gender'] ?? '') === 'Male' ? 'selected' : '' ?>>Male</option>
                                    <option value="Female" <?= ($user_data['gender'] ?? '') === 'Female' ? 'selected' : '' ?>>Female</option>
                                    <option value="Other" <?= ($user_data['gender'] ?? '') === 'Other' ? 'selected' : '' ?>>Other</option>
                                </select>
                            </div>
                            <button type="submit" class="btn-dark">Save Changes</button>
                        </form>
                    </div>

                    <!-- Password Update Section -->
                    <div class="settings-panel">
                        <div class="settings-panel-header">Password</div>
                        <div class="settings-panel-note">Change your password to keep your account secure.</div>
                        <button class="btn-dark js-change-pass">Change Password</button>
                    </div>

                    <!-- Delete Account (Danger Zone) -->
                    <div class="danger-panel">
                        <div class="danger-panel-heading">Delete Account</div>
                        <div class="danger-panel-note">Permanently delete your account and all associated data. This action cannot be undone.</div>
                        <button class="btn-outline">Delete Account</button>
                    </div>
                </div>
            </div>
        </main>
    </div>
</div>

<?php 
/**
 * FOOTER LOADING
 * Closes the document with the appropriate footer based on session status.
 */
if ($is_logged_in) {
    include 'includes/member_footer.php';
} else {
    include 'guest_header/guestfooter.php';
}
?>