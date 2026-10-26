<?php
$pageTitle = 'Order History';
require_once '../includes/header.php';

if (!isLoggedIn()) {
    $_SESSION['redirect_after_login'] = SITE_URL . '/pages/orders.php';
    redirect(SITE_URL . '/pages/login.php');
}

$user = getCurrentUser();

// Check for specific order detail view
$viewOrderId = isset($_GET['view']) ? intval($_GET['view']) : 0;

if ($viewOrderId > 0) {
    // View specific order details
    $stmt = $conn->prepare("SELECT * FROM orders WHERE id = ? AND user_id = ?");
    $stmt->bind_param("ii", $viewOrderId, $_SESSION['user_id']);
    $stmt->execute();
    $order = $stmt->get_result()->fetch_assoc();

    if (!$order) {
        setFlashMessage('error', 'Order not found.');
        redirect(SITE_URL . '/pages/orders.php');
    }

    $stmtItems = $conn->prepare("SELECT * FROM order_items WHERE order_id = ?");
    $stmtItems->bind_param("i", $viewOrderId);
    $stmtItems->execute();
    $orderItems = $stmtItems->get_result();
}

// Fetch all orders
$stmt = $conn->prepare("SELECT o.*, (SELECT COUNT(*) FROM order_items WHERE order_id = o.id) as item_count FROM orders o WHERE o.user_id = ? ORDER BY o.created_at DESC");
$stmt->bind_param("i", $_SESSION['user_id']);
$stmt->execute();
$orders = $stmt->get_result();
?>

<script>window.siteUrl = '<?php echo SITE_URL; ?>';</script>

<section class="profile-section section-padding">
    <div class="container">
        <div class="breadcrumb" style="justify-content: flex-start; margin-bottom: 30px;">
            <a href="<?php echo SITE_URL; ?>">Home</a>
            <span>/</span>
            <span class="current">My Orders</span>
        </div>

        <div class="profile-layout">
            <!-- Sidebar -->
            <div class="profile-sidebar">
                <div class="profile-avatar">
                    <i class="fas fa-user"></i>
                </div>
                <h3><?php echo htmlspecialchars($user['name']); ?></h3>
                <p><?php echo htmlspecialchars($user['email']); ?></p>
                <nav class="profile-nav">
                    <a href="<?php echo SITE_URL; ?>/pages/profile.php"><i class="fas fa-user-circle"></i> My Profile</a>
                    <a href="<?php echo SITE_URL; ?>/pages/orders.php" class="active"><i class="fas fa-box"></i> My Orders</a>
                    <a href="<?php echo SITE_URL; ?>/pages/logout.php"><i class="fas fa-sign-out-alt"></i> Logout</a>
                </nav>
            </div>

            <!-- Content -->
            <div class="profile-content">
                <?php if ($viewOrderId > 0 && isset($order)): ?>
                <!-- Order Detail View -->
                <div style="display: flex; align-items: center; justify-content: space-between; margin-bottom: 25px; padding-bottom: 15px; border-bottom: 1px solid var(--border);">
                    <h2 style="margin: 0; border: none; padding: 0;">Order #ORD-<?php echo str_pad($order['id'], 5, '0', STR_PAD_LEFT); ?></h2>
                    <a href="<?php echo SITE_URL; ?>/pages/orders.php" class="btn btn-sm btn-dark"><i class="fas fa-arrow-left"></i> Back</a>
                </div>

                <div class="order-detail-box" style="background: transparent; border: 1px solid var(--border);">
                    <div class="order-detail-row">
                        <span class="label">Order Date</span>
                        <span class="value"><?php echo date('d M Y, h:i A', strtotime($order['created_at'])); ?></span>
                    </div>
                    <div class="order-detail-row">
                        <span class="label">Status</span>
                        <span class="value"><span class="status-badge status-<?php echo $order['order_status']; ?>"><?php echo ucfirst($order['order_status']); ?></span></span>
                    </div>
                    <div class="order-detail-row">
                        <span class="label">Payment</span>
                        <span class="value"><?php echo htmlspecialchars($order['payment_method']); ?> (<span class="status-badge status-<?php echo $order['payment_status']; ?>"><?php echo ucfirst($order['payment_status']); ?></span>)</span>
                    </div>
                    <div class="order-detail-row">
                        <span class="label">Shipping To</span>
                        <span class="value"><?php echo htmlspecialchars($order['shipping_address'] . ', ' . $order['city'] . ', ' . $order['state'] . ' - ' . $order['pincode']); ?></span>
                    </div>
                </div>

                <h4 style="margin: 25px 0 15px; font-family: var(--font-body);">Items Ordered</h4>
                <table class="data-table" style="margin-bottom: 20px;">
                    <thead>
                        <tr>
                            <th>Product</th>
                            <th>Price</th>
                            <th>Qty</th>
                            <th>Subtotal</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php while ($item = $orderItems->fetch_assoc()): ?>
                        <tr>
                            <td><?php echo htmlspecialchars($item['product_name']); ?></td>
                            <td><?php echo formatPrice($item['price']); ?></td>
                            <td><?php echo $item['quantity']; ?></td>
                            <td style="color: var(--accent); font-weight: 600;"><?php echo formatPrice($item['subtotal']); ?></td>
                        </tr>
                        <?php endwhile; ?>
                    </tbody>
                    <tfoot>
                        <tr>
                            <td colspan="3" style="text-align: right; font-weight: 700;">Total Amount:</td>
                            <td style="color: var(--accent); font-weight: 700; font-size: 1.1rem;"><?php echo formatPrice($order['total_amount']); ?></td>
                        </tr>
                    </tfoot>
                </table>

                <?php else: ?>
                <!-- Orders List -->
                <h2><i class="fas fa-box" style="color: var(--accent); margin-right: 10px;"></i> My Orders</h2>

                <?php if ($orders->num_rows > 0): ?>
                <div style="overflow-x: auto;">
                    <table class="data-table">
                        <thead>
                            <tr>
                                <th>Order ID</th>
                                <th>Date</th>
                                <th>Items</th>
                                <th>Total</th>
                                <th>Payment</th>
                                <th>Status</th>
                                <th>Action</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php while ($order = $orders->fetch_assoc()): ?>
                            <tr>
                                <td style="font-weight: 600; color: var(--accent);">#ORD-<?php echo str_pad($order['id'], 5, '0', STR_PAD_LEFT); ?></td>
                                <td><?php echo date('d M Y', strtotime($order['created_at'])); ?></td>
                                <td><?php echo $order['item_count']; ?> item<?php echo $order['item_count'] > 1 ? 's' : ''; ?></td>
                                <td style="font-weight: 600;"><?php echo formatPrice($order['total_amount']); ?></td>
                                <td><?php echo htmlspecialchars($order['payment_method']); ?></td>
                                <td><span class="status-badge status-<?php echo $order['order_status']; ?>"><?php echo ucfirst($order['order_status']); ?></span></td>
                                <td>
                                    <a href="<?php echo SITE_URL; ?>/pages/orders.php?view=<?php echo $order['id']; ?>" class="btn btn-sm btn-outline" style="padding: 5px 12px; font-size: 0.8rem;">
                                        <i class="fas fa-eye"></i> View
                                    </a>
                                </td>
                            </tr>
                            <?php endwhile; ?>
                        </tbody>
                    </table>
                </div>
                <?php else: ?>
                <div class="empty-state">
                    <i class="fas fa-box-open"></i>
                    <h3>No Orders Yet</h3>
                    <p>You haven't placed any orders yet. Start shopping!</p>
                    <a href="<?php echo SITE_URL; ?>/pages/products.php" class="btn btn-primary">Start Shopping</a>
                </div>
                <?php endif; ?>
                <?php endif; ?>
            </div>
        </div>
    </div>
</section>

<?php require_once '../includes/footer.php'; ?>
