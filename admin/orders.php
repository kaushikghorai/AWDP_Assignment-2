<?php
$adminTitle = 'Orders Management';
require_once __DIR__ . '/includes/header.php';

// Handle Order Status & Payment Status Update
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['update_order_id'])) {
    $orderId = intval($_POST['update_order_id']);
    $orderStatus = sanitize($_POST['order_status'] ?? '');
    $paymentStatus = sanitize($_POST['payment_status'] ?? '');

    $allowedOrderStatus = ['pending', 'confirmed', 'processing', 'shipped', 'delivered', 'cancelled'];
    $allowedPaymentStatus = ['pending', 'paid', 'failed'];

    if (in_array($orderStatus, $allowedOrderStatus) && in_array($paymentStatus, $allowedPaymentStatus)) {
        $stmt = $conn->prepare("UPDATE orders SET order_status = ?, payment_status = ? WHERE id = ?");
        $stmt->bind_param("ssi", $orderStatus, $paymentStatus, $orderId);
        if ($stmt->execute()) {
            setFlashMessage('success', "Order #$orderId status updated successfully.");
        } else {
            setFlashMessage('error', 'Failed to update order: ' . $conn->error);
        }
    }
    redirect(ADMIN_URL . "/orders.php" . (isset($_GET['view']) ? "?view=$orderId" : ""));
}

// Check if viewing single order details
$viewOrder = null;
$orderItems = [];
if (isset($_GET['view'])) {
    $viewId = intval($_GET['view']);
    $stmt = $conn->prepare("
        SELECT o.*, u.email as account_email, u.name as account_name 
        FROM orders o 
        LEFT JOIN users u ON o.user_id = u.id 
        WHERE o.id = ?
    ");
    $stmt->bind_param("i", $viewId);
    $stmt->execute();
    $viewOrder = $stmt->get_result()->fetch_assoc();

    if ($viewOrder) {
        $stmtItems = $conn->prepare("SELECT * FROM order_items WHERE order_id = ?");
        $stmtItems->bind_param("i", $viewId);
        $stmtItems->execute();
        $itemsRes = $stmtItems->get_result();
        while ($item = $itemsRes->fetch_assoc()) {
            $orderItems[] = $item;
        }
    }
}

// Filters for listing
$filterStatus = sanitize($_GET['status'] ?? '');
$filterPayment = sanitize($_GET['payment'] ?? '');
$search = sanitize($_GET['search'] ?? '');

$sql = "
    SELECT o.*, u.email as user_email 
    FROM orders o 
    LEFT JOIN users u ON o.user_id = u.id 
    WHERE 1=1 
";

if (!empty($filterStatus)) {
    $sql .= " AND o.order_status = '$filterStatus'";
}
if (!empty($filterPayment)) {
    $sql .= " AND o.payment_status = '$filterPayment'";
}
if (!empty($search)) {
    $sql .= " AND (o.id = '$search' OR o.customer_name LIKE '%$search%' OR o.email LIKE '%$search%' OR o.mobile LIKE '%$search%')";
}
$sql .= " ORDER BY o.id DESC";

$ordersResult = $conn->query($sql);
?>

<div class="page-title-box">
    <div>
        <h1>Orders & Fulfillment</h1>
        <p>Review customer purchases, update fulfillment milestones, and manage payment statuses.</p>
    </div>
    <?php if ($viewOrder): ?>
        <a href="<?php echo ADMIN_URL; ?>/orders.php" class="btn btn-outline">
            <i class="fas fa-arrow-left"></i> Back to Orders List
        </a>
    <?php endif; ?>
</div>

<?php if ($viewOrder): ?>
    <!-- Single Order Detail View -->
    <div style="display: grid; grid-template-columns: 2fr 1fr; gap: 24px; margin-bottom: 30px;">
        <!-- Left: Order Items & Delivery Info -->
        <div>
            <div class="admin-card">
                <div class="card-header-flex">
                    <h3 class="card-title">
                        <i class="fas fa-box-open" style="color: var(--primary);"></i>
                        Order #<?php echo str_pad($viewOrder['id'], 5, '0', STR_PAD_LEFT); ?> Items
                    </h3>
                    <span style="font-size: 0.85rem; color: var(--text-muted);">
                        Placed on <?php echo date('F d, Y h:i A', strtotime($viewOrder['created_at'])); ?>
                    </span>
                </div>

                <div class="table-responsive">
                    <table class="admin-table">
                        <thead>
                            <tr>
                                <th>Item</th>
                                <th>Unit Price</th>
                                <th>Quantity</th>
                                <th style="text-align: right;">Subtotal</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($orderItems as $item): ?>
                                <tr>
                                    <td>
                                        <div style="font-weight: 600;"><?php echo htmlspecialchars($item['product_name']); ?></div>
                                        <div style="font-size: 0.78rem; color: var(--text-muted);">SKU ID: #<?php echo $item['product_id']; ?></div>
                                    </td>
                                    <td><?php echo formatPrice($item['price']); ?></td>
                                    <td>x<?php echo $item['quantity']; ?></td>
                                    <td style="text-align: right;"><strong><?php echo formatPrice($item['subtotal']); ?></strong></td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                        <tfoot>
                            <tr>
                                <td colspan="3" style="text-align: right; padding-top: 20px; font-weight: 600;">Total Amount:</td>
                                <td style="text-align: right; padding-top: 20px; font-size: 1.2rem; font-weight: 700; color: var(--primary);">
                                    <?php echo formatPrice($viewOrder['total_amount']); ?>
                                </td>
                            </tr>
                        </tfoot>
                    </table>
                </div>
            </div>

            <!-- Customer & Shipping Addresses -->
            <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 20px;">
                <div class="admin-card">
                    <h4 style="font-size: 0.95rem; margin-bottom: 12px; color: var(--primary);">
                        <i class="fas fa-truck"></i> Shipping Address
                    </h4>
                    <p style="font-size: 0.88rem; line-height: 1.6; color: var(--text);">
                        <strong><?php echo htmlspecialchars($viewOrder['customer_name']); ?></strong><br>
                        <?php echo nl2br(htmlspecialchars($viewOrder['shipping_address'])); ?><br>
                        <?php echo htmlspecialchars($viewOrder['city'] . ', ' . $viewOrder['state'] . ' - ' . $viewOrder['pincode']); ?><br>
                        <i class="fas fa-phone" style="font-size: 0.8rem; color: var(--text-dim); margin-top: 8px;"></i> <?php echo htmlspecialchars($viewOrder['mobile']); ?>
                    </p>
                </div>

                <div class="admin-card">
                    <h4 style="font-size: 0.95rem; margin-bottom: 12px; color: var(--primary);">
                        <i class="fas fa-file-invoice"></i> Billing Address
                    </h4>
                    <p style="font-size: 0.88rem; line-height: 1.6; color: var(--text);">
                        <strong><?php echo htmlspecialchars($viewOrder['customer_name']); ?></strong><br>
                        <?php echo nl2br(htmlspecialchars($viewOrder['billing_address'] ? $viewOrder['billing_address'] : $viewOrder['shipping_address'])); ?><br>
                        <?php echo htmlspecialchars($viewOrder['city'] . ', ' . $viewOrder['state'] . ' - ' . $viewOrder['pincode']); ?><br>
                        <i class="fas fa-envelope" style="font-size: 0.8rem; color: var(--text-dim); margin-top: 8px;"></i> <?php echo htmlspecialchars($viewOrder['email']); ?>
                    </p>
                </div>
            </div>
        </div>

        <!-- Right: Status Control Panel -->
        <div>
            <div class="admin-card">
                <h3 class="card-title" style="margin-bottom: 20px;">
                    <i class="fas fa-edit" style="color: var(--primary);"></i> Order Status Controls
                </h3>

                <form action="<?php echo ADMIN_URL; ?>/orders.php?view=<?php echo $viewOrder['id']; ?>" method="POST">
                    <input type="hidden" name="update_order_id" value="<?php echo $viewOrder['id']; ?>">

                    <div class="form-group">
                        <label class="form-label" for="order_status">Fulfillment Status</label>
                        <select name="order_status" id="order_status" class="form-control">
                            <option value="pending" <?php echo $viewOrder['order_status'] === 'pending' ? 'selected' : ''; ?>>Pending</option>
                            <option value="confirmed" <?php echo $viewOrder['order_status'] === 'confirmed' ? 'selected' : ''; ?>>Confirmed</option>
                            <option value="processing" <?php echo $viewOrder['order_status'] === 'processing' ? 'selected' : ''; ?>>Processing</option>
                            <option value="shipped" <?php echo $viewOrder['order_status'] === 'shipped' ? 'selected' : ''; ?>>Shipped</option>
                            <option value="delivered" <?php echo $viewOrder['order_status'] === 'delivered' ? 'selected' : ''; ?>>Delivered</option>
                            <option value="cancelled" <?php echo $viewOrder['order_status'] === 'cancelled' ? 'selected' : ''; ?>>Cancelled</option>
                        </select>
                    </div>

                    <div class="form-group">
                        <label class="form-label" for="payment_status">Payment Status</label>
                        <select name="payment_status" id="payment_status" class="form-control">
                            <option value="pending" <?php echo $viewOrder['payment_status'] === 'pending' ? 'selected' : ''; ?>>Pending</option>
                            <option value="paid" <?php echo $viewOrder['payment_status'] === 'paid' ? 'selected' : ''; ?>>Paid</option>
                            <option value="failed" <?php echo $viewOrder['payment_status'] === 'failed' ? 'selected' : ''; ?>>Failed</option>
                        </select>
                    </div>

                    <div style="padding: 12px; background: rgba(255,255,255,0.02); border-radius: var(--radius-sm); border: 1px solid var(--border); margin-bottom: 20px;">
                        <div style="font-size: 0.8rem; color: var(--text-muted); margin-bottom: 4px;">Payment Method:</div>
                        <div style="font-weight: 600;"><i class="fas fa-credit-card" style="color: var(--primary); margin-right: 6px;"></i> <?php echo htmlspecialchars($viewOrder['payment_method']); ?></div>
                    </div>

                    <button type="submit" class="btn btn-primary" style="width: 100%;">
                        <i class="fas fa-check"></i> Update Order Status
                    </button>
                </form>
            </div>
        </div>
    </div>

<?php else: ?>

    <!-- Orders Filter Bar -->
    <div class="admin-card" style="padding: 18px 24px;">
        <form action="<?php echo ADMIN_URL; ?>/orders.php" method="GET" style="display: flex; gap: 15px; flex-wrap: wrap; align-items: center; justify-content: space-between;">
            <div style="display: flex; gap: 12px; flex-wrap: wrap; align-items: center; flex: 1;">
                <div class="header-search" style="width: 280px;">
                    <i class="fas fa-search" style="color: var(--text-dim);"></i>
                    <input type="text" name="search" value="<?php echo htmlspecialchars($search); ?>" placeholder="Search Order ID or Customer...">
                </div>

                <select name="status" class="form-control" style="width: 160px;" onchange="this.form.submit()">
                    <option value="">All Order Statuses</option>
                    <option value="pending" <?php echo $filterStatus === 'pending' ? 'selected' : ''; ?>>Pending</option>
                    <option value="confirmed" <?php echo $filterStatus === 'confirmed' ? 'selected' : ''; ?>>Confirmed</option>
                    <option value="processing" <?php echo $filterStatus === 'processing' ? 'selected' : ''; ?>>Processing</option>
                    <option value="shipped" <?php echo $filterStatus === 'shipped' ? 'selected' : ''; ?>>Shipped</option>
                    <option value="delivered" <?php echo $filterStatus === 'delivered' ? 'selected' : ''; ?>>Delivered</option>
                    <option value="cancelled" <?php echo $filterStatus === 'cancelled' ? 'selected' : ''; ?>>Cancelled</option>
                </select>

                <select name="payment" class="form-control" style="width: 160px;" onchange="this.form.submit()">
                    <option value="">All Payments</option>
                    <option value="pending" <?php echo $filterPayment === 'pending' ? 'selected' : ''; ?>>Pending</option>
                    <option value="paid" <?php echo $filterPayment === 'paid' ? 'selected' : ''; ?>>Paid</option>
                    <option value="failed" <?php echo $filterPayment === 'failed' ? 'selected' : ''; ?>>Failed</option>
                </select>
            </div>

            <div>
                <button type="submit" class="btn btn-outline btn-sm">Filter</button>
                <?php if ($filterStatus || $filterPayment || $search): ?>
                    <a href="<?php echo ADMIN_URL; ?>/orders.php" class="btn btn-outline btn-sm" style="color: var(--danger);">Reset</a>
                <?php endif; ?>
            </div>
        </form>
    </div>

    <!-- Orders Table -->
    <div class="admin-card">
        <div class="table-responsive">
            <table class="admin-table">
                <thead>
                    <tr>
                        <th>Order ID</th>
                        <th>Customer</th>
                        <th>Order Date</th>
                        <th>Total Amount</th>
                        <th>Payment Method</th>
                        <th>Payment Status</th>
                        <th>Order Status</th>
                        <th>Action</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if ($ordersResult && $ordersResult->num_rows > 0): ?>
                        <?php while ($ord = $ordersResult->fetch_assoc()): ?>
                            <tr>
                                <td><strong>#<?php echo str_pad($ord['id'], 5, '0', STR_PAD_LEFT); ?></strong></td>
                                <td>
                                    <div><strong><?php echo htmlspecialchars($ord['customer_name']); ?></strong></div>
                                    <div style="font-size: 0.78rem; color: var(--text-muted);"><?php echo htmlspecialchars($ord['email']); ?> • <?php echo htmlspecialchars($ord['mobile']); ?></div>
                                </td>
                                <td><?php echo date('M d, Y h:i A', strtotime($ord['created_at'])); ?></td>
                                <td><strong><?php echo formatPrice($ord['total_amount']); ?></strong></td>
                                <td><?php echo htmlspecialchars($ord['payment_method']); ?></td>
                                <td>
                                    <span class="badge badge-<?php echo $ord['payment_status']; ?>">
                                        <?php echo ucfirst($ord['payment_status']); ?>
                                    </span>
                                </td>
                                <td>
                                    <span class="badge badge-<?php echo $ord['order_status']; ?>">
                                        <?php echo ucfirst($ord['order_status']); ?>
                                    </span>
                                </td>
                                <td>
                                    <a href="<?php echo ADMIN_URL; ?>/orders.php?view=<?php echo $ord['id']; ?>" class="btn btn-sm btn-outline">
                                        <i class="fas fa-eye"></i> View & Manage
                                    </a>
                                </td>
                            </tr>
                        <?php endwhile; ?>
                    <?php else: ?>
                        <tr>
                            <td colspan="8" style="text-align: center; padding: 35px; color: var(--text-muted);">
                                No orders found matching filter criteria.
                            </td>
                        </tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>

<?php endif; ?>

<?php require_once __DIR__ . '/includes/footer.php'; ?>
