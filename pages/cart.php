<?php
$pageTitle = 'Shopping Cart';
require_once '../includes/header.php';

$cartItems = [];
$subtotal = 0;

if (isset($_SESSION['cart']) && !empty($_SESSION['cart'])) {
    foreach ($_SESSION['cart'] as $productId => $item) {
        $stmt = $conn->prepare("SELECT p.*, c.name as category_name FROM products p JOIN categories c ON p.category_id = c.id WHERE p.id = ? AND p.status = 'active'");
        $stmt->bind_param("i", $productId);
        $stmt->execute();
        $product = $stmt->get_result()->fetch_assoc();
        
        if ($product) {
            $price = $product['discount_price'] ? $product['discount_price'] : $product['price'];
            $product['cart_quantity'] = $item['quantity'];
            $product['cart_price'] = $price;
            $product['cart_subtotal'] = $price * $item['quantity'];
            $subtotal += $product['cart_subtotal'];
            $cartItems[] = $product;
        }
    }
}

$shipping = $subtotal >= 999 ? 0 : 99;
$total = $subtotal + $shipping;
?>

<script>window.siteUrl = '<?php echo SITE_URL; ?>';</script>

<section class="cart-section section-padding">
    <div class="container">
        <div class="breadcrumb" style="justify-content: flex-start; margin-bottom: 30px;">
            <a href="<?php echo SITE_URL; ?>">Home</a>
            <span>/</span>
            <span class="current">Shopping Cart</span>
        </div>

        <?php if (!empty($cartItems)): ?>
        <div class="cart-layout">
            <!-- Cart Items -->
            <div class="cart-items">
                <div class="cart-header">
                    <h2><i class="fas fa-shopping-bag" style="color: var(--accent); margin-right: 10px;"></i> Your Cart (<?php echo getCartCount(); ?> items)</h2>
                    <a href="<?php echo SITE_URL; ?>/pages/cart_action.php?action=clear" class="btn btn-sm btn-dark" onclick="return confirm('Clear all items from cart?');">
                        <i class="fas fa-trash"></i> Clear All
                    </a>
                </div>

                <?php foreach ($cartItems as $item): ?>
                <div class="cart-item">
                    <div class="cart-item-image">
                        <div class="product-img-placeholder" style="height: 100px;">
                            <i class="fas fa-tshirt" style="font-size: 1.5rem;"></i>
                        </div>
                    </div>
                    <div class="cart-item-info">
                        <h4><a href="<?php echo SITE_URL; ?>/pages/product_detail.php?id=<?php echo $item['id']; ?>" style="color: var(--text-primary);"><?php echo htmlspecialchars($item['name']); ?></a></h4>
                        <p><?php echo htmlspecialchars($item['category_name']); ?></p>
                    </div>
                    <div class="cart-item-price"><?php echo formatPrice($item['cart_price']); ?></div>
                    <div class="cart-item-qty">
                        <a href="<?php echo SITE_URL; ?>/pages/cart_action.php?action=update&product_id=<?php echo $item['id']; ?>&quantity=<?php echo $item['cart_quantity'] - 1; ?>">−</a>
                        <span><?php echo $item['cart_quantity']; ?></span>
                        <a href="<?php echo SITE_URL; ?>/pages/cart_action.php?action=update&product_id=<?php echo $item['id']; ?>&quantity=<?php echo $item['cart_quantity'] + 1; ?>">+</a>
                    </div>
                    <div class="cart-item-remove">
                        <a href="<?php echo SITE_URL; ?>/pages/cart_action.php?action=remove&product_id=<?php echo $item['id']; ?>" title="Remove"><i class="fas fa-times"></i></a>
                    </div>
                </div>
                <?php endforeach; ?>
            </div>

            <!-- Cart Summary -->
            <div class="cart-summary">
                <h3>Order Summary</h3>
                <div class="summary-row">
                    <span class="label">Subtotal</span>
                    <span class="value"><?php echo formatPrice($subtotal); ?></span>
                </div>
                <div class="summary-row">
                    <span class="label">Shipping</span>
                    <span class="value"><?php echo $shipping === 0 ? '<span class="text-success">Free</span>' : formatPrice($shipping); ?></span>
                </div>
                <?php if ($subtotal < 999): ?>
                <div style="padding: 10px 15px; background: rgba(201, 169, 110, 0.1); border-radius: var(--radius-sm); margin-bottom: 12px; font-size: 0.85rem; color: var(--accent);">
                    <i class="fas fa-truck"></i> Add <?php echo formatPrice(999 - $subtotal); ?> more for free shipping!
                </div>
                <?php endif; ?>
                <div class="summary-row total">
                    <span class="label">Total</span>
                    <span class="value"><?php echo formatPrice($total); ?></span>
                </div>
                <a href="<?php echo SITE_URL; ?>/pages/checkout.php" class="btn btn-primary btn-block btn-lg mt-2">
                    <i class="fas fa-lock"></i> Proceed to Checkout
                </a>
                <a href="<?php echo SITE_URL; ?>/pages/products.php" class="btn btn-outline btn-block mt-1" style="font-size: 0.85rem;">
                    <i class="fas fa-arrow-left"></i> Continue Shopping
                </a>
            </div>
        </div>

        <?php else: ?>
        <div class="empty-state">
            <i class="fas fa-shopping-bag"></i>
            <h3>Your Cart is Empty</h3>
            <p>Looks like you haven't added anything to your cart yet.</p>
            <a href="<?php echo SITE_URL; ?>/pages/products.php" class="btn btn-primary btn-lg">
                <i class="fas fa-shopping-bag"></i> Start Shopping
            </a>
        </div>
        <?php endif; ?>
    </div>
</section>

<?php require_once '../includes/footer.php'; ?>
