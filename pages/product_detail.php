<?php
$pageTitle = 'Product Details';
require_once '../includes/header.php';

$productId = isset($_GET['id']) ? intval($_GET['id']) : 0;

if ($productId <= 0) {
    setFlashMessage('error', 'Invalid product.');
    redirect(SITE_URL . '/pages/products.php');
}

$stmt = $conn->prepare("SELECT p.*, c.name as category_name FROM products p JOIN categories c ON p.category_id = c.id WHERE p.id = ? AND p.status = 'active'");
$stmt->bind_param("i", $productId);
$stmt->execute();
$product = $stmt->get_result()->fetch_assoc();

if (!$product) {
    setFlashMessage('error', 'Product not found.');
    redirect(SITE_URL . '/pages/products.php');
}

// Related products
$relatedStmt = $conn->prepare("SELECT p.*, c.name as category_name FROM products p JOIN categories c ON p.category_id = c.id WHERE p.category_id = ? AND p.id != ? AND p.status = 'active' LIMIT 4");
$relatedStmt->bind_param("ii", $product['category_id'], $productId);
$relatedStmt->execute();
$relatedProducts = $relatedStmt->get_result();

$pageTitle = $product['name'];
?>

<script>window.siteUrl = '<?php echo SITE_URL; ?>';</script>

<section class="product-detail-section section-padding">
    <div class="container">
        <div class="breadcrumb" style="justify-content: flex-start; margin-bottom: 30px;">
            <a href="<?php echo SITE_URL; ?>">Home</a>
            <span>/</span>
            <a href="<?php echo SITE_URL; ?>/pages/products.php">Products</a>
            <span>/</span>
            <a href="<?php echo SITE_URL; ?>/pages/products.php?category=<?php echo $product['category_id']; ?>"><?php echo htmlspecialchars($product['category_name']); ?></a>
            <span>/</span>
            <span class="current"><?php echo htmlspecialchars($product['name']); ?></span>
        </div>

        <div class="product-detail-grid">
            <!-- Product Image -->
            <div class="product-gallery">
                <div class="product-main-image">
                    <div class="product-img-placeholder" style="font-size: 1rem;">
                        <i class="fas fa-tshirt" style="font-size: 5rem;"></i>
                        <span style="margin-top: 15px; font-size: 1rem;"><?php echo htmlspecialchars($product['name']); ?></span>
                    </div>
                </div>
            </div>

            <!-- Product Info -->
            <div class="product-detail-info">
                <div class="product-detail-category"><?php echo htmlspecialchars($product['category_name']); ?></div>
                <h1><?php echo htmlspecialchars($product['name']); ?></h1>
                
                <div style="margin: 10px 0 20px; display: flex; align-items: center; gap: 10px;">
                    <?php echo renderStars($product['rating']); ?>
                    <span style="color: var(--text-muted); font-size: 0.9rem;">(<?php echo $product['rating']; ?>/5)</span>
                </div>

                <div class="product-detail-price">
                    <span class="current"><?php echo formatPrice($product['discount_price'] ?: $product['price']); ?></span>
                    <?php if ($product['discount_price']): ?>
                        <span class="original"><?php echo formatPrice($product['price']); ?></span>
                        <span class="save-badge">Save <?php echo formatPrice($product['price'] - $product['discount_price']); ?></span>
                    <?php endif; ?>
                </div>

                <p class="product-detail-desc"><?php echo nl2br(htmlspecialchars($product['description'])); ?></p>

                <div class="product-meta">
                    <div class="product-meta-item">
                        <i class="fas fa-tag"></i>
                        <span class="label">Category:</span>
                        <span><?php echo htmlspecialchars($product['category_name']); ?></span>
                    </div>
                    <div class="product-meta-item">
                        <i class="fas fa-box"></i>
                        <span class="label">Stock:</span>
                        <span class="<?php echo $product['stock_quantity'] > 0 ? 'text-success' : 'text-danger'; ?>">
                            <?php echo $product['stock_quantity'] > 0 ? $product['stock_quantity'] . ' units available' : 'Out of Stock'; ?>
                        </span>
                    </div>
                    <div class="product-meta-item">
                        <i class="fas fa-truck"></i>
                        <span class="label">Delivery:</span>
                        <span>Free shipping on orders above ₹999</span>
                    </div>
                    <div class="product-meta-item">
                        <i class="fas fa-undo"></i>
                        <span class="label">Returns:</span>
                        <span>7-day easy returns</span>
                    </div>
                </div>

                <?php if ($product['stock_quantity'] > 0): ?>
                <form method="POST" action="<?php echo SITE_URL; ?>/pages/cart_action.php">
                    <input type="hidden" name="action" value="add">
                    <input type="hidden" name="product_id" value="<?php echo $product['id']; ?>">
                    
                    <div style="margin-bottom: 20px;">
                        <label style="display: block; margin-bottom: 8px; font-size: 0.9rem; color: var(--text-secondary);">Quantity</label>
                        <div class="quantity-selector">
                            <button type="button" onclick="updateQuantity('decrease', 'qty')">−</button>
                            <input type="number" name="quantity" id="qty" value="1" min="1" max="<?php echo $product['stock_quantity']; ?>">
                            <button type="button" onclick="updateQuantity('increase', 'qty')">+</button>
                        </div>
                    </div>

                    <div class="detail-buttons">
                        <button type="submit" class="btn btn-primary btn-lg"><i class="fas fa-shopping-bag"></i> Add to Cart</button>
                        <a href="<?php echo SITE_URL; ?>/pages/cart.php" class="btn btn-outline btn-lg"><i class="fas fa-bolt"></i> Buy Now</a>
                    </div>
                </form>
                <?php else: ?>
                <div class="btn btn-dark btn-lg btn-block" style="cursor: not-allowed; opacity: 0.6;">
                    <i class="fas fa-times-circle"></i> Out of Stock
                </div>
                <?php endif; ?>
            </div>
        </div>

        <!-- Related Products -->
        <?php if ($relatedProducts->num_rows > 0): ?>
        <div style="margin-top: 80px;">
            <div class="section-header">
                <h2>You May Also Like</h2>
                <p>Similar products from this category</p>
                <div class="divider"></div>
            </div>

            <div class="products-grid">
                <?php while ($rp = $relatedProducts->fetch_assoc()): ?>
                <div class="product-card">
                    <div class="product-image">
                        <div class="product-img-placeholder">
                            <i class="fas fa-tshirt"></i>
                            <span><?php echo htmlspecialchars($rp['name']); ?></span>
                        </div>
                        <div class="product-actions">
                            <a href="<?php echo SITE_URL; ?>/pages/product_detail.php?id=<?php echo $rp['id']; ?>"><i class="fas fa-eye"></i></a>
                            <button onclick="addToCart(<?php echo $rp['id']; ?>)"><i class="fas fa-shopping-bag"></i></button>
                        </div>
                    </div>
                    <div class="product-info">
                        <div class="product-category-tag"><?php echo htmlspecialchars($rp['category_name']); ?></div>
                        <h3><a href="<?php echo SITE_URL; ?>/pages/product_detail.php?id=<?php echo $rp['id']; ?>"><?php echo htmlspecialchars($rp['name']); ?></a></h3>
                        <div class="product-price">
                            <span class="current-price"><?php echo formatPrice($rp['discount_price'] ?: $rp['price']); ?></span>
                            <?php if ($rp['discount_price']): ?>
                                <span class="original-price"><?php echo formatPrice($rp['price']); ?></span>
                            <?php endif; ?>
                        </div>
                        <div class="product-footer">
                            <?php echo renderStars($rp['rating']); ?>
                            <button class="add-cart-btn" onclick="addToCart(<?php echo $rp['id']; ?>)">Add to Cart</button>
                        </div>
                    </div>
                </div>
                <?php endwhile; ?>
            </div>
        </div>
        <?php endif; ?>
    </div>
</section>

<?php require_once '../includes/footer.php'; ?>
