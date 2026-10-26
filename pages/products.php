<?php
$pageTitle = 'Products';
require_once '../includes/header.php';

// Handle category filter
$categoryId = isset($_GET['category']) ? intval($_GET['category']) : 0;
$search = isset($_GET['search']) ? sanitize($_GET['search']) : '';
$sort = isset($_GET['sort']) ? sanitize($_GET['sort']) : 'newest';

// Build query
$where = "WHERE p.status = 'active'";
$params = [];
$types = '';

if ($categoryId > 0) {
    $where .= " AND p.category_id = ?";
    $params[] = $categoryId;
    $types .= 'i';
}
if (!empty($search)) {
    $where .= " AND (p.name LIKE ? OR p.short_description LIKE ?)";
    $searchTerm = "%$search%";
    $params[] = $searchTerm;
    $params[] = $searchTerm;
    $types .= 'ss';
}

// Sort
$orderBy = "ORDER BY p.created_at DESC";
switch ($sort) {
    case 'price_low': $orderBy = "ORDER BY COALESCE(p.discount_price, p.price) ASC"; break;
    case 'price_high': $orderBy = "ORDER BY COALESCE(p.discount_price, p.price) DESC"; break;
    case 'name': $orderBy = "ORDER BY p.name ASC"; break;
    case 'rating': $orderBy = "ORDER BY p.rating DESC"; break;
}

// Pagination
$perPage = 12;
$page = isset($_GET['page']) ? max(1, intval($_GET['page'])) : 1;
$offset = ($page - 1) * $perPage;

// Count total
$countQuery = "SELECT COUNT(*) as total FROM products p $where";
$stmt = $conn->prepare($countQuery);
if (!empty($params)) $stmt->bind_param($types, ...$params);
$stmt->execute();
$totalProducts = $stmt->get_result()->fetch_assoc()['total'];
$totalPages = ceil($totalProducts / $perPage);

// Fetch products
$query = "SELECT p.*, c.name as category_name FROM products p JOIN categories c ON p.category_id = c.id $where $orderBy LIMIT $perPage OFFSET $offset";
$stmt = $conn->prepare($query);
if (!empty($params)) $stmt->bind_param($types, ...$params);
$stmt->execute();
$products = $stmt->get_result();

// Get category name for breadcrumb
$categoryName = 'All Products';
if ($categoryId > 0) {
    $catStmt = $conn->prepare("SELECT name FROM categories WHERE id = ?");
    $catStmt->bind_param("i", $categoryId);
    $catStmt->execute();
    $catData = $catStmt->get_result()->fetch_assoc();
    if ($catData) $categoryName = $catData['name'];
}

// Fetch all categories for sidebar
$allCats = $conn->query("SELECT c.*, COUNT(p.id) as product_count FROM categories c LEFT JOIN products p ON c.id = p.category_id AND p.status = 'active' WHERE c.status = 'active' GROUP BY c.id ORDER BY c.name");
?>

<script>window.siteUrl = '<?php echo SITE_URL; ?>';</script>

<div class="page-hero">
    <div class="container">
        <h1><?php echo htmlspecialchars($categoryName); ?></h1>
        <p><?php echo $totalProducts; ?> product<?php echo $totalProducts !== 1 ? 's' : ''; ?> found</p>
        <div class="breadcrumb">
            <a href="<?php echo SITE_URL; ?>">Home</a>
            <span>/</span>
            <?php if ($categoryId > 0): ?>
                <a href="<?php echo SITE_URL; ?>/pages/products.php">Products</a>
                <span>/</span>
                <span class="current"><?php echo htmlspecialchars($categoryName); ?></span>
            <?php else: ?>
                <span class="current">Products</span>
            <?php endif; ?>
        </div>
    </div>
</div>

<section class="section-padding">
    <div class="container">
        <!-- Filters Bar -->
        <div style="display: flex; align-items: center; justify-content: space-between; flex-wrap: wrap; gap: 15px; margin-bottom: 30px; padding: 20px; background: var(--dark-card); border: 1px solid var(--border); border-radius: var(--radius-sm);">
            <div style="display: flex; align-items: center; gap: 10px; flex-wrap: wrap;">
                <a href="<?php echo SITE_URL; ?>/pages/products.php" class="btn btn-sm <?php echo $categoryId === 0 ? 'btn-primary' : 'btn-dark'; ?>">All</a>
                <?php while ($c = $allCats->fetch_assoc()): ?>
                    <a href="<?php echo SITE_URL; ?>/pages/products.php?category=<?php echo $c['id']; ?><?php echo $sort !== 'newest' ? '&sort=' . $sort : ''; ?>" class="btn btn-sm <?php echo $categoryId === intval($c['id']) ? 'btn-primary' : 'btn-dark'; ?>">
                        <?php echo htmlspecialchars($c['name']); ?> (<?php echo $c['product_count']; ?>)
                    </a>
                <?php endwhile; ?>
            </div>
            <div style="display: flex; align-items: center; gap: 10px;">
                <label style="color: var(--text-muted); font-size: 0.9rem;">Sort:</label>
                <select class="form-control" style="width: auto; padding: 8px 35px 8px 12px; font-size: 0.85rem;" onchange="window.location.href = this.value;">
                    <option value="<?php echo SITE_URL; ?>/pages/products.php?<?php echo $categoryId ? 'category=' . $categoryId . '&' : ''; ?>sort=newest" <?php echo $sort === 'newest' ? 'selected' : ''; ?>>Newest First</option>
                    <option value="<?php echo SITE_URL; ?>/pages/products.php?<?php echo $categoryId ? 'category=' . $categoryId . '&' : ''; ?>sort=price_low" <?php echo $sort === 'price_low' ? 'selected' : ''; ?>>Price: Low to High</option>
                    <option value="<?php echo SITE_URL; ?>/pages/products.php?<?php echo $categoryId ? 'category=' . $categoryId . '&' : ''; ?>sort=price_high" <?php echo $sort === 'price_high' ? 'selected' : ''; ?>>Price: High to Low</option>
                    <option value="<?php echo SITE_URL; ?>/pages/products.php?<?php echo $categoryId ? 'category=' . $categoryId . '&' : ''; ?>sort=name" <?php echo $sort === 'name' ? 'selected' : ''; ?>>Name A-Z</option>
                    <option value="<?php echo SITE_URL; ?>/pages/products.php?<?php echo $categoryId ? 'category=' . $categoryId . '&' : ''; ?>sort=rating" <?php echo $sort === 'rating' ? 'selected' : ''; ?>>Top Rated</option>
                </select>
            </div>
        </div>

        <?php if ($products->num_rows > 0): ?>
        <div class="products-grid">
            <?php while ($product = $products->fetch_assoc()): ?>
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
                    <?php elseif ($product['is_featured']): ?>
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

        <!-- Pagination -->
        <?php if ($totalPages > 1): ?>
        <div class="pagination">
            <?php if ($page > 1): ?>
                <a href="?<?php echo $categoryId ? 'category=' . $categoryId . '&' : ''; ?>sort=<?php echo $sort; ?>&page=<?php echo $page - 1; ?>"><i class="fas fa-chevron-left"></i></a>
            <?php endif; ?>
            <?php for ($p = 1; $p <= $totalPages; $p++): ?>
                <?php if ($p === $page): ?>
                    <span class="active"><?php echo $p; ?></span>
                <?php else: ?>
                    <a href="?<?php echo $categoryId ? 'category=' . $categoryId . '&' : ''; ?>sort=<?php echo $sort; ?>&page=<?php echo $p; ?>"><?php echo $p; ?></a>
                <?php endif; ?>
            <?php endfor; ?>
            <?php if ($page < $totalPages): ?>
                <a href="?<?php echo $categoryId ? 'category=' . $categoryId . '&' : ''; ?>sort=<?php echo $sort; ?>&page=<?php echo $page + 1; ?>"><i class="fas fa-chevron-right"></i></a>
            <?php endif; ?>
        </div>
        <?php endif; ?>

        <?php else: ?>
        <div class="empty-state">
            <i class="fas fa-box-open"></i>
            <h3>No Products Found</h3>
            <p>We couldn't find any products matching your criteria.</p>
            <a href="<?php echo SITE_URL; ?>/pages/products.php" class="btn btn-primary">View All Products</a>
        </div>
        <?php endif; ?>
    </div>
</section>

<?php require_once '../includes/footer.php'; ?>
