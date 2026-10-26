<?php
$pageTitle = 'Home';
require_once 'includes/header.php';

// Fetch featured categories
$catResult = $conn->query("SELECT * FROM categories WHERE status = 'active' ORDER BY id LIMIT 6");

// Fetch featured products
$featuredResult = $conn->query("SELECT p.*, c.name as category_name FROM products p JOIN categories c ON p.category_id = c.id WHERE p.status = 'active' AND p.is_featured = 1 ORDER BY p.id DESC LIMIT 8");

// Fetch new/latest products
$newResult = $conn->query("SELECT p.*, c.name as category_name FROM products p JOIN categories c ON p.category_id = c.id WHERE p.status = 'active' AND p.is_new = 1 ORDER BY p.created_at DESC LIMIT 8");
?>

<script>window.siteUrl = '<?php echo SITE_URL; ?>';</script>

<!-- Hero Slider -->
<section class="hero">
    <div class="hero-slider">
        <div class="hero-slide active">
            <div class="hero-content">
                <span class="hero-tag">New Season Collection</span>
                <h1 class="hero-title">Redefine Your <span>Style</span> Statement</h1>
                <p class="hero-subtitle">Discover handpicked fashion pieces crafted for the modern trendsetter. Premium quality meets contemporary design.</p>
                <div class="hero-buttons">
                    <a href="<?php echo SITE_URL; ?>/pages/products.php" class="btn btn-primary btn-lg">Shop Now <i class="fas fa-arrow-right"></i></a>
                    <a href="<?php echo SITE_URL; ?>/pages/categories.php" class="btn btn-outline btn-lg">Explore</a>
                </div>
            </div>
            <div class="hero-decoration"></div>
        </div>
        <div class="hero-slide">
            <div class="hero-content">
                <span class="hero-tag">Winter Sale - Up to 40% Off</span>
                <h1 class="hero-title">Luxury <span>Winter</span> Collection</h1>
                <p class="hero-subtitle">Embrace the cold with our premium winter wear. Coats, sweaters, and more crafted from the finest materials.</p>
                <div class="hero-buttons">
                    <a href="<?php echo SITE_URL; ?>/pages/products.php?category=6" class="btn btn-primary btn-lg">Shop Winter <i class="fas fa-snowflake"></i></a>
                    <a href="<?php echo SITE_URL; ?>/pages/categories.php" class="btn btn-outline btn-lg">View All</a>
                </div>
            </div>
            <div class="hero-decoration"></div>
        </div>
        <div class="hero-slide">
            <div class="hero-content">
                <span class="hero-tag">Exclusive Collection</span>
                <h1 class="hero-title">Elegance in Every <span>Detail</span></h1>
                <p class="hero-subtitle">From casual essentials to designer pieces, find everything you need to build your perfect wardrobe.</p>
                <div class="hero-buttons">
                    <a href="<?php echo SITE_URL; ?>/pages/products.php" class="btn btn-primary btn-lg">Discover <i class="fas fa-gem"></i></a>
                    <a href="<?php echo SITE_URL; ?>/pages/about.php" class="btn btn-outline btn-lg">Our Story</a>
                </div>
            </div>
            <div class="hero-decoration"></div>
        </div>
    </div>
    <div class="hero-dots">
        <div class="hero-dot active"></div>
        <div class="hero-dot"></div>
        <div class="hero-dot"></div>
    </div>
</section>

<!-- Featured Categories -->
<section class="section-padding">
    <div class="container">
        <div class="section-header">
            <h2>Shop by Category</h2>
            <p>Browse our curated collections across all categories</p>
            <div class="divider"></div>
        </div>

        <div class="categories-grid">
            <?php
            $icons = ['fas fa-tshirt', 'fas fa-female', 'fas fa-child', 'fas fa-gem', 'fas fa-shoe-prints', 'fas fa-snowflake'];
            $i = 0;
            while ($cat = $catResult->fetch_assoc()):
            ?>
            <a href="<?php echo SITE_URL; ?>/pages/products.php?category=<?php echo $cat['id']; ?>" class="category-card">
                <div class="cat-icon">
                    <i class="<?php echo $icons[$i % count($icons)]; ?>"></i>
                </div>
                <h3><?php echo htmlspecialchars($cat['name']); ?></h3>
                <p><?php echo truncateText(htmlspecialchars($cat['description']), 50); ?></p>
            </a>
            <?php $i++; endwhile; ?>
        </div>
    </div>
</section>

<!-- Featured Products -->
<section class="section-padding" style="background: var(--primary);">
    <div class="container">
        <div class="section-header">
            <h2>Featured Products</h2>
            <p>Handpicked favorites from our latest collection</p>
            <div class="divider"></div>
        </div>

        <div class="products-grid">
            <?php while ($product = $featuredResult->fetch_assoc()): ?>
            <div class="product-card">
                <div class="product-image">
                    <div class="product-img-placeholder">
                        <i class="fas fa-tshirt"></i>
                        <span><?php echo htmlspecialchars($product['name']); ?></span>
                    </div>
                    <?php if ($product['is_new']): ?>
                        <span class="product-badge badge-new">New</span>
                    <?php elseif ($product['discount_price']): ?>
                        <span class="product-badge badge-sale">Sale</span>
                    <?php else: ?>
                        <span class="product-badge badge-featured">Featured</span>
                    <?php endif; ?>
                    <div class="product-actions">
                        <a href="<?php echo SITE_URL; ?>/pages/product_detail.php?id=<?php echo $product['id']; ?>" title="View Details"><i class="fas fa-eye"></i></a>
                        <button onclick="addToCart(<?php echo $product['id']; ?>)" title="Add to Cart"><i class="fas fa-shopping-bag"></i></button>
                    </div>
                </div>
                <div class="product-info">
                    <div class="product-category-tag"><?php echo htmlspecialchars($product['category_name']); ?></div>
                    <h3><a href="<?php echo SITE_URL; ?>/pages/product_detail.php?id=<?php echo $product['id']; ?>"><?php echo htmlspecialchars($product['name']); ?></a></h3>
                    <p class="short-desc"><?php echo htmlspecialchars($product['short_description']); ?></p>
                    <div class="product-price">
                        <span class="current-price"><?php echo formatPrice($product['discount_price'] ?: $product['price']); ?></span>
                        <?php if ($product['discount_price']): ?>
                            <span class="original-price"><?php echo formatPrice($product['price']); ?></span>
                            <span class="discount-percent"><?php echo round((($product['price'] - $product['discount_price']) / $product['price']) * 100); ?>% OFF</span>
                        <?php endif; ?>
                    </div>
                    <div class="product-footer">
                        <?php echo renderStars($product['rating']); ?>
                        <button class="add-cart-btn" onclick="addToCart(<?php echo $product['id']; ?>)">Add to Cart</button>
                    </div>
                </div>
            </div>
            <?php endwhile; ?>
        </div>
    </div>
</section>

<!-- Promotional Banner -->
<section class="section-padding">
    <div class="container">
        <div class="promo-banner">
            <div class="promo-content">
                <h2>End of Season <span>Sale</span></h2>
                <p>Up to 40% off on selected styles. Don't miss out on our biggest sale of the year!</p>
                <div class="promo-timer countdown-timer">
                    <div class="timer-box">
                        <span class="number days">03</span>
                        <span class="label">Days</span>
                    </div>
                    <div class="timer-box">
                        <span class="number hours">12</span>
                        <span class="label">Hours</span>
                    </div>
                    <div class="timer-box">
                        <span class="number minutes">45</span>
                        <span class="label">Mins</span>
                    </div>
                    <div class="timer-box">
                        <span class="number seconds">30</span>
                        <span class="label">Secs</span>
                    </div>
                </div>
                <a href="<?php echo SITE_URL; ?>/pages/products.php" class="btn btn-primary">Shop the Sale <i class="fas fa-arrow-right"></i></a>
            </div>
        </div>
    </div>
</section>

<!-- New Arrivals -->
<section class="section-padding" style="background: var(--primary);">
    <div class="container">
        <div class="section-header">
            <h2>New Arrivals</h2>
            <p>The latest additions to our collection</p>
            <div class="divider"></div>
        </div>

        <div class="products-grid">
            <?php while ($product = $newResult->fetch_assoc()): ?>
            <div class="product-card">
                <div class="product-image">
                    <div class="product-img-placeholder">
                        <i class="fas fa-tshirt"></i>
                        <span><?php echo htmlspecialchars($product['name']); ?></span>
                    </div>
                    <span class="product-badge badge-new">New</span>
                    <div class="product-actions">
                        <a href="<?php echo SITE_URL; ?>/pages/product_detail.php?id=<?php echo $product['id']; ?>" title="View Details"><i class="fas fa-eye"></i></a>
                        <button onclick="addToCart(<?php echo $product['id']; ?>)" title="Add to Cart"><i class="fas fa-shopping-bag"></i></button>
                    </div>
                </div>
                <div class="product-info">
                    <div class="product-category-tag"><?php echo htmlspecialchars($product['category_name']); ?></div>
                    <h3><a href="<?php echo SITE_URL; ?>/pages/product_detail.php?id=<?php echo $product['id']; ?>"><?php echo htmlspecialchars($product['name']); ?></a></h3>
                    <p class="short-desc"><?php echo htmlspecialchars($product['short_description']); ?></p>
                    <div class="product-price">
                        <span class="current-price"><?php echo formatPrice($product['discount_price'] ?: $product['price']); ?></span>
                        <?php if ($product['discount_price']): ?>
                            <span class="original-price"><?php echo formatPrice($product['price']); ?></span>
                        <?php endif; ?>
                    </div>
                    <div class="product-footer">
                        <?php echo renderStars($product['rating']); ?>
                        <button class="add-cart-btn" onclick="addToCart(<?php echo $product['id']; ?>)">Add to Cart</button>
                    </div>
                </div>
            </div>
            <?php endwhile; ?>
        </div>

        <div class="text-center mt-3">
            <a href="<?php echo SITE_URL; ?>/pages/products.php" class="btn btn-outline">View All Products <i class="fas fa-arrow-right"></i></a>
        </div>
    </div>
</section>

<?php require_once 'includes/footer.php'; ?>
