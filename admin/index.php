<?php
$adminTitle = 'Dashboard';
require_once __DIR__ . '/includes/header.php';

// Fetch Statistics
// Total Users
$usersRes = $conn->query("SELECT COUNT(*) as total, SUM(CASE WHEN role = 'customer' THEN 1 ELSE 0 END) as customers FROM users");
$userStats = $usersRes ? $usersRes->fetch_assoc() : ['total' => 0, 'customers' => 0];

// Total Categories
$catRes = $conn->query("SELECT COUNT(*) as total FROM categories");
$totalCategories = $catRes ? $catRes->fetch_assoc()['total'] : 0;

// Total Products
$prodRes = $conn->query("SELECT COUNT(*) as total, SUM(CASE WHEN stock_quantity <= 5 THEN 1 ELSE 0 END) as low_stock FROM products");
$prodStats = $prodRes ? $prodRes->fetch_assoc() : ['total' => 0, 'low_stock' => 0];

// Total Orders & Revenue
$ordersRes = $conn->query("SELECT COUNT(*) as total, SUM(CASE WHEN order_status != 'cancelled' THEN total_amount ELSE 0 END) as revenue FROM orders");
$orderStats = $ordersRes ? $ordersRes->fetch_assoc() : ['total' => 0, 'revenue' => 0];

// Recent Orders (last 5)
$recentOrders = $conn->query("
    SELECT o.*, u.email as user_email 
    FROM orders o 
    LEFT JOIN users u ON o.user_id = u.id 
    ORDER BY o.created_at DESC 
    LIMIT 6
");

// Recent Contact Inquiries (last 4)
$recentMessages = $conn->query("SELECT * FROM contact_messages ORDER BY created_at DESC LIMIT 4");
?>

<div class="page-title-box">
    <div>
        <h1>Dashboard Overview</h1>
        <p>Real-time analytics and management summary for LUXE e-commerce.</p>
    </div>
    <div style="display: flex; gap: 10px;">
        <a href="<?php echo ADMIN_URL; ?>/products.php?action=add" class="btn btn-primary">
            <i class="fas fa-plus"></i> Add Product
        </a>
        <a href="<?php echo ADMIN_URL; ?>/categories.php" class="btn btn-outline">
            <i class="fas fa-folder-plus"></i> New Category
        </a>
    </div>
</div>

<!-- Stats Widgets -->
<div class="stats-grid">
    <div class="stat-card">
        <div class="stat-info">
            <h3><?php echo formatPrice($orderStats['revenue'] ?? 0); ?></h3>
            <span>Total Sales Revenue</span>
        </div>
        <div class="stat-icon gold">
            <i class="fas fa-wallet"></i>
        </div>
    </div>

    <div class="stat-card">
        <div class="stat-info">
            <h3><?php echo number_format($orderStats['total']); ?></h3>
            <span>Total Orders</span>
        </div>
        <div class="stat-icon blue">
            <i class="fas fa-shopping-bag"></i>
        </div>
    </div>

    <div class="stat-card">
        <div class="stat-info">
            <h3><?php echo number_format($prodStats['total']); ?></h3>
            <span>Total Products</span>
        </div>
        <div class="stat-icon green">
            <i class="fas fa-tshirt"></i>
        </div>
    </div>

    <div class="stat-card">
        <div class="stat-info">
            <h3><?php echo number_format($totalCategories); ?></h3>
            <span>Categories</span>
        </div>
        <div class="stat-icon purple">
            <i class="fas fa-layer-group"></i>
        </div>
    </div>

    <div class="stat-card">
        <div class="stat-info">
            <h3><?php echo number_format($userStats['customers']); ?></h3>
            <span>Registered Customers</span>
        </div>
        <div class="stat-icon amber">
            <i class="fas fa-user-friends"></i>
        </div>
    </div>
</div>

<!-- Main Row: Recent Orders & Sidebar Info -->
<div style="display: grid; grid-template-columns: 2fr 1fr; gap: 24px;">
    <!-- Recent Orders Card -->
    <div class="admin-card">
        <div class="card-header-flex">
            <h3 class="card-title">
                <i class="fas fa-clock" style="color: var(--primary);"></i> Recent Orders
            </h3>
            <a href="<?php echo ADMIN_URL; ?>/orders.php" class="btn btn-outline btn-sm">View All Orders</a>
        </div>

        <div class="table-responsive">
            <table class="admin-table">
                <thead>
                    <tr>
                        <th>Order #</th>
                        <th>Customer</th>
                        <th>Date</th>
                        <th>Amount</th>
                        <th>Payment</th>
                        <th>Status</th>
                        <th>Action</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if ($recentOrders && $recentOrders->num_rows > 0): ?>
                        <?php while ($row = $recentOrders->fetch_assoc()): ?>
                            <tr>
                                <td><strong>#<?php echo str_pad($row['id'], 5, '0', STR_PAD_LEFT); ?></strong></td>
                                <td>
                                    <div><?php echo htmlspecialchars($row['customer_name']); ?></div>
                                    <div style="font-size: 0.78rem; color: var(--text-muted);"><?php echo htmlspecialchars($row['mobile']); ?></div>
                                </td>
                                <td><?php echo date('M d, Y', strtotime($row['created_at'])); ?></td>
                                <td><strong><?php echo formatPrice($row['total_amount']); ?></strong></td>
                                <td><span class="badge badge-<?php echo $row['payment_status']; ?>"><?php echo ucfirst($row['payment_status']); ?></span></td>
                                <td><span class="badge badge-<?php echo $row['order_status']; ?>"><?php echo ucfirst($row['order_status']); ?></span></td>
                                <td>
                                    <a href="<?php echo ADMIN_URL; ?>/orders.php?view=<?php echo $row['id']; ?>" class="btn btn-sm btn-outline">
                                        <i class="fas fa-eye"></i> Details
                                    </a>
                                </td>
                            </tr>
                        <?php endwhile; ?>
                    <?php else: ?>
                        <tr>
                            <td colspan="7" style="text-align: center; padding: 30px; color: var(--text-muted);">
                                No customer orders recorded yet.
                            </td>
                        </tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>

    <!-- Quick Alerts & Contact Inquiries -->
    <div style="display: flex; flex-direction: column; gap: 24px;">
        <!-- Quick Operations -->
        <div class="admin-card">
            <h3 class="card-title" style="margin-bottom: 15px;">
                <i class="fas fa-bolt" style="color: var(--warning);"></i> Quick Actions
            </h3>
            <div style="display: flex; flex-direction: column; gap: 10px;">
                <a href="<?php echo ADMIN_URL; ?>/products.php?action=add" class="btn btn-outline" style="justify-content: flex-start;">
                    <i class="fas fa-plus-circle" style="color: var(--primary);"></i> Add New Product
                </a>
                <a href="<?php echo ADMIN_URL; ?>/categories.php" class="btn btn-outline" style="justify-content: flex-start;">
                    <i class="fas fa-folder-plus" style="color: var(--info);"></i> Manage Categories
                </a>
                <a href="<?php echo ADMIN_URL; ?>/users.php" class="btn btn-outline" style="justify-content: flex-start;">
                    <i class="fas fa-user-plus" style="color: var(--success);"></i> Manage User Accounts
                </a>
                <a href="<?php echo ADMIN_URL; ?>/orders.php?status=pending" class="btn btn-outline" style="justify-content: flex-start;">
                    <i class="fas fa-hourglass-half" style="color: var(--warning);"></i> Pending Orders (<?php echo $pendingOrders; ?>)
                </a>
            </div>
        </div>

        <!-- Latest Messages Card -->
        <div class="admin-card">
            <div class="card-header-flex">
                <h3 class="card-title" style="font-size: 1rem;">
                    <i class="fas fa-envelope" style="color: var(--primary);"></i> Customer Messages
                </h3>
                <a href="<?php echo ADMIN_URL; ?>/messages.php" style="font-size: 0.8rem; color: var(--primary); text-decoration: none;">View All</a>
            </div>

            <div style="display: flex; flex-direction: column; gap: 12px;">
                <?php if ($recentMessages && $recentMessages->num_rows > 0): ?>
                    <?php while ($msg = $recentMessages->fetch_assoc()): ?>
                        <div style="padding: 12px; background: rgba(255,255,255,0.02); border: 1px solid var(--border); border-radius: var(--radius-sm);">
                            <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 4px;">
                                <strong style="font-size: 0.88rem;"><?php echo htmlspecialchars($msg['name']); ?></strong>
                                <span style="font-size: 0.75rem; color: var(--text-dim);"><?php echo date('M d', strtotime($msg['created_at'])); ?></span>
                            </div>
                            <div style="font-size: 0.82rem; color: var(--text-muted); margin-bottom: 6px;">
                                <?php echo htmlspecialchars($msg['subject'] ? $msg['subject'] : 'Inquiry'); ?>
                            </div>
                            <div style="font-size: 0.8rem; color: var(--text-dim); line-height: 1.4;">
                                <?php echo htmlspecialchars(truncateText($msg['message'], 70)); ?>
                            </div>
                        </div>
                    <?php endwhile; ?>
                <?php else: ?>
                    <p style="color: var(--text-muted); font-size: 0.85rem; text-align: center; padding: 15px;">No messages received yet.</p>
                <?php endif; ?>
            </div>
        </div>
    </div>
</div>

<?php require_once __DIR__ . '/includes/footer.php'; ?>
