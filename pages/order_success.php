<?php
$pageTitle = 'Order Confirmed';
require_once '../includes/header.php';

if (!isLoggedIn()) {
    redirect(SITE_URL . '/pages/login.php');
}

$orderId = isset($_GET['order_id']) ? intval($_GET['order_id']) : 0;

if ($orderId <= 0) {
    redirect(SITE_URL . '/index.php');
}

// Fetch order
$stmt = $conn->prepare("SELECT * FROM orders WHERE id = ? AND user_id = ?");
$stmt->bind_param("ii", $orderId, $_SESSION['user_id']);
$stmt->execute();
$order = $stmt->get_result()->fetch_assoc();

if (!$order) {
    setFlashMessage('error', 'Order not found.');
    redirect(SITE_URL . '/index.php');
}

// Fetch order items
$stmtItems = $conn->prepare("SELECT * FROM order_items WHERE order_id = ?");
$stmtItems->bind_param("i", $orderId);
$stmtItems->execute();
$items = $stmtItems->get_result();
?>

<script>window.siteUrl = '<?php echo SITE_URL; ?>';</script>

<section class="order-success-section section-padding">
    <div class="container">
        <div class="order-success-card">
            <div class="success-icon">
                <i class="fas fa-check"></i>
            </div>
            <h1>Order Placed Successfully!</h1>
            <p class="success-msg">Thank you for shopping with LUXE. Your order has been received and is being processed.</p>

            <div class="order-detail-box">
                <div class="order-detail-row">
                    <span class="label">Order ID</span>
                    <span class="value text-accent">#ORD-<?php echo str_pad($order['id'], 5, '0', STR_PAD_LEFT); ?></span>
                </div>
                <div class="order-detail-row">
                    <span class="label">Order Date</span>
                    <span class="value"><?php echo date('d M Y, h:i A', strtotime($order['created_at'])); ?></span>
                </div>
                <div class="order-detail-row">
                    <span class="label">Customer</span>
                    <span class="value"><?php echo htmlspecialchars($order['customer_name']); ?></span>
                </div>
                <div class="order-detail-row">
                    <span class="label">Email</span>
                    <span class="value"><?php echo htmlspecialchars($order['email']); ?></span>
                </div>
                <div class="order-detail-row">
                    <span class="label">Shipping To</span>
                    <span class="value"><?php echo htmlspecialchars($order['shipping_address'] . ', ' . $order['city'] . ', ' . $order['state'] . ' - ' . $order['pincode']); ?></span>
                </div>
                <div class="order-detail-row">
                    <span class="label">Payment Method</span>
                    <span class="value"><?php echo htmlspecialchars($order['payment_method']); ?></span>
                </div>
                <div class="order-detail-row">
                    <span class="label">Order Status</span>
                    <span class="value"><span class="status-badge status-<?php echo $order['order_status']; ?>"><?php echo ucfirst($order['order_status']); ?></span></span>
                </div>
            </div>

            <!-- Ordered Products -->
            <div class="order-detail-box">
                <h4 style="margin-bottom: 15px; font-family: var(--font-body);">Ordered Products</h4>
                <?php while ($item = $items->fetch_assoc()): ?>
                <div style="display: flex; justify-content: space-between; align-items: center; padding: 10px 0; border-bottom: 1px solid var(--border);">
                    <div>
                        <p style="font-weight: 500;"><?php echo htmlspecialchars($item['product_name']); ?></p>
                        <p style="font-size: 0.85rem; color: var(--text-muted);">Qty: <?php echo $item['quantity']; ?> × <?php echo formatPrice($item['price']); ?></p>
                    </div>
                    <span style="font-weight: 600; color: var(--accent);"><?php echo formatPrice($item['subtotal']); ?></span>
                </div>
                <?php endwhile; ?>
                <div style="display: flex; justify-content: space-between; padding: 15px 0 0; font-size: 1.1rem; font-weight: 700;">
                    <span>Total Amount</span>
                    <span style="color: var(--accent);"><?php echo formatPrice($order['total_amount']); ?></span>
                </div>
            </div>

            <div style="display: flex; gap: 15px; justify-content: center; flex-wrap: wrap;">
                <a href="<?php echo SITE_URL; ?>/pages/products.php" class="btn btn-primary btn-lg">
                    <i class="fas fa-shopping-bag"></i> Continue Shopping
                </a>
                <a href="<?php echo SITE_URL; ?>/pages/orders.php" class="btn btn-outline btn-lg">
                    <i class="fas fa-box"></i> Order History
                </a>
            </div>
        </div>
    </div>
</section>

<?php require_once '../includes/footer.php'; ?>
