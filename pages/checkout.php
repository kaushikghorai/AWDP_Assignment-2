<?php
$pageTitle = 'Checkout';
require_once '../includes/header.php';

// Require login
if (!isLoggedIn()) {
    $_SESSION['redirect_after_login'] = SITE_URL . '/pages/checkout.php';
    setFlashMessage('warning', 'Please login to proceed with checkout.');
    redirect(SITE_URL . '/pages/login.php');
}

// Check cart
if (!isset($_SESSION['cart']) || empty($_SESSION['cart'])) {
    setFlashMessage('error', 'Your cart is empty.');
    redirect(SITE_URL . '/pages/cart.php');
}

$user = getCurrentUser();

// Calculate cart items
$cartItems = [];
$subtotal = 0;
foreach ($_SESSION['cart'] as $productId => $item) {
    $stmt = $conn->prepare("SELECT p.*, c.name as category_name FROM products p JOIN categories c ON p.category_id = c.id WHERE p.id = ?");
    $stmt->bind_param("i", $productId);
    $stmt->execute();
    $product = $stmt->get_result()->fetch_assoc();
    if ($product) {
        $price = $product['discount_price'] ?: $product['price'];
        $product['cart_quantity'] = $item['quantity'];
        $product['cart_price'] = $price;
        $product['cart_subtotal'] = $price * $item['quantity'];
        $subtotal += $product['cart_subtotal'];
        $cartItems[] = $product;
    }
}
$shipping = $subtotal >= 999 ? 0 : 99;
$total = $subtotal + $shipping;

$errors = [];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $customerName = sanitize($_POST['customer_name'] ?? '');
    $email = sanitize($_POST['email'] ?? '');
    $mobile = sanitize($_POST['mobile'] ?? '');
    $billingAddress = sanitize($_POST['billing_address'] ?? '');
    $shippingAddress = sanitize($_POST['shipping_address'] ?? '');
    $city = sanitize($_POST['city'] ?? '');
    $state = sanitize($_POST['state'] ?? '');
    $pincode = sanitize($_POST['pincode'] ?? '');
    $paymentMethod = sanitize($_POST['payment_method'] ?? 'Cash on Delivery');

    // Validation
    if (empty($customerName)) $errors[] = 'Name is required.';
    if (empty($email)) $errors[] = 'Email is required.';
    if (empty($mobile)) $errors[] = 'Mobile is required.';
    if (empty($billingAddress)) $errors[] = 'Billing address is required.';
    if (empty($city)) $errors[] = 'City is required.';
    if (empty($state)) $errors[] = 'State is required.';
    if (empty($pincode)) $errors[] = 'Pincode is required.';

    if (empty($shippingAddress)) {
        $shippingAddress = $billingAddress;
    }

    if (empty($errors)) {
        // Create order
        $stmt = $conn->prepare("INSERT INTO orders (user_id, customer_name, email, mobile, billing_address, shipping_address, city, state, pincode, total_amount, payment_method, payment_status, order_status) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, 'pending', 'pending')");
        $stmt->bind_param("issssssssds", $_SESSION['user_id'], $customerName, $email, $mobile, $billingAddress, $shippingAddress, $city, $state, $pincode, $total, $paymentMethod);
        
        if ($stmt->execute()) {
            $orderId = $stmt->insert_id;

            // Insert order items and update stock
            foreach ($cartItems as $item) {
                $stmtItem = $conn->prepare("INSERT INTO order_items (order_id, product_id, product_name, product_image, price, quantity, subtotal) VALUES (?, ?, ?, ?, ?, ?, ?)");
                $stmtItem->bind_param("iissdid", $orderId, $item['id'], $item['name'], $item['image'], $item['cart_price'], $item['cart_quantity'], $item['cart_subtotal']);
                $stmtItem->execute();

                // Update stock
                $newStock = max(0, $item['stock_quantity'] - $item['cart_quantity']);
                $stmtStock = $conn->prepare("UPDATE products SET stock_quantity = ? WHERE id = ?");
                $stmtStock->bind_param("ii", $newStock, $item['id']);
                $stmtStock->execute();
            }

            // Clear cart
            $_SESSION['cart'] = [];

            // Redirect to order success
            redirect(SITE_URL . '/pages/order_success.php?order_id=' . $orderId);
        } else {
            $errors[] = 'Failed to place order. Please try again.';
        }
    }
}
?>

<script>window.siteUrl = '<?php echo SITE_URL; ?>';</script>

<section class="checkout-section section-padding">
    <div class="container">
        <div class="breadcrumb" style="justify-content: flex-start; margin-bottom: 30px;">
            <a href="<?php echo SITE_URL; ?>">Home</a>
            <span>/</span>
            <a href="<?php echo SITE_URL; ?>/pages/cart.php">Cart</a>
            <span>/</span>
            <span class="current">Checkout</span>
        </div>

        <?php if (!empty($errors)): ?>
            <?php foreach ($errors as $error): ?>
                <div class="flash-message flash-error"><i class="fas fa-exclamation-circle"></i> <?php echo $error; ?></div>
            <?php endforeach; ?>
        <?php endif; ?>

        <form method="POST" action="">
            <div class="checkout-grid">
                <!-- Checkout Form -->
                <div>
                    <div class="checkout-form-card mb-2">
                        <h3><i class="fas fa-user"></i> Contact Information</h3>
                        <div class="form-group">
                            <label for="customer_name">Full Name *</label>
                            <input type="text" id="customer_name" name="customer_name" class="form-control" value="<?php echo htmlspecialchars($user['name'] ?? ''); ?>" required>
                        </div>
                        <div class="form-row">
                            <div class="form-group">
                                <label for="email">Email *</label>
                                <input type="email" id="email" name="email" class="form-control" value="<?php echo htmlspecialchars($user['email'] ?? ''); ?>" required>
                            </div>
                            <div class="form-group">
                                <label for="mobile">Mobile *</label>
                                <input type="text" id="mobile" name="mobile" class="form-control" value="<?php echo htmlspecialchars($user['mobile'] ?? ''); ?>" required>
                            </div>
                        </div>
                    </div>

                    <div class="checkout-form-card mb-2">
                        <h3><i class="fas fa-map-marker-alt"></i> Shipping Details</h3>
                        <div class="form-group">
                            <label for="billing_address">Billing Address *</label>
                            <input type="text" id="billing_address" name="billing_address" class="form-control" value="<?php echo htmlspecialchars($user['address'] ?? ''); ?>" required>
                        </div>
                        <div class="form-group">
                            <label for="shipping_address">Shipping Address <small style="color: var(--text-muted);">(leave blank if same as billing)</small></label>
                            <input type="text" id="shipping_address" name="shipping_address" class="form-control" placeholder="Same as billing address">
                        </div>
                        <div class="form-row">
                            <div class="form-group">
                                <label for="city">City *</label>
                                <input type="text" id="city" name="city" class="form-control" value="<?php echo htmlspecialchars($user['city'] ?? ''); ?>" required>
                            </div>
                            <div class="form-group">
                                <label for="state">State *</label>
                                <input type="text" id="state" name="state" class="form-control" value="<?php echo htmlspecialchars($user['state'] ?? ''); ?>" required>
                            </div>
                        </div>
                        <div class="form-group">
                            <label for="pincode">Pincode *</label>
                            <input type="text" id="pincode" name="pincode" class="form-control" value="<?php echo htmlspecialchars($user['pincode'] ?? ''); ?>" required style="max-width: 200px;">
                        </div>
                    </div>

                    <div class="checkout-form-card">
                        <h3><i class="fas fa-credit-card"></i> Payment Method</h3>
                        <div class="payment-methods">
                            <label class="payment-method selected">
                                <input type="radio" name="payment_method" value="Cash on Delivery" checked>
                                <i class="fas fa-money-bill-wave" style="color: var(--success); font-size: 1.2rem;"></i>
                                <div>
                                    <strong>Cash on Delivery</strong>
                                    <p style="font-size: 0.85rem; color: var(--text-muted); margin: 0;">Pay when you receive your order</p>
                                </div>
                            </label>
                        </div>
                    </div>
                </div>

                <!-- Order Summary -->
                <div class="cart-summary">
                    <h3>Order Summary</h3>
                    
                    <?php foreach ($cartItems as $item): ?>
                    <div style="display: flex; align-items: center; gap: 12px; margin-bottom: 15px; padding-bottom: 15px; border-bottom: 1px solid var(--border);">
                        <div style="width: 50px; height: 50px; border-radius: 8px; background: var(--dark-lighter); display: flex; align-items: center; justify-content: center; flex-shrink: 0;">
                            <i class="fas fa-tshirt" style="color: var(--accent); opacity: 0.5;"></i>
                        </div>
                        <div style="flex: 1; min-width: 0;">
                            <p style="font-size: 0.9rem; font-weight: 500; white-space: nowrap; overflow: hidden; text-overflow: ellipsis;"><?php echo htmlspecialchars($item['name']); ?></p>
                            <p style="font-size: 0.8rem; color: var(--text-muted);">Qty: <?php echo $item['cart_quantity']; ?></p>
                        </div>
                        <span style="font-weight: 600; color: var(--accent); white-space: nowrap;"><?php echo formatPrice($item['cart_subtotal']); ?></span>
                    </div>
                    <?php endforeach; ?>

                    <div class="summary-row">
                        <span class="label">Subtotal</span>
                        <span class="value"><?php echo formatPrice($subtotal); ?></span>
                    </div>
                    <div class="summary-row">
                        <span class="label">Shipping</span>
                        <span class="value"><?php echo $shipping === 0 ? '<span class="text-success">Free</span>' : formatPrice($shipping); ?></span>
                    </div>
                    <div class="summary-row total">
                        <span class="label">Total</span>
                        <span class="value"><?php echo formatPrice($total); ?></span>
                    </div>

                    <button type="submit" class="btn btn-primary btn-block btn-lg mt-2">
                        <i class="fas fa-check-circle"></i> Place Order
                    </button>

                    <div style="text-align: center; margin-top: 15px;">
                        <p style="font-size: 0.8rem; color: var(--text-muted);">
                            <i class="fas fa-shield-alt text-accent"></i> Your information is secure and encrypted
                        </p>
                    </div>
                </div>
            </div>
        </form>
    </div>
</section>

<?php require_once '../includes/footer.php'; ?>
