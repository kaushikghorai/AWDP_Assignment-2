<?php
$pageTitle = 'Categories';
require_once '../includes/header.php';

$catResult = $conn->query("SELECT c.*, COUNT(p.id) as product_count FROM categories c LEFT JOIN products p ON c.id = p.category_id AND p.status = 'active' WHERE c.status = 'active' GROUP BY c.id ORDER BY c.name");
$icons = ['fas fa-tshirt', 'fas fa-female', 'fas fa-child', 'fas fa-gem', 'fas fa-shoe-prints', 'fas fa-snowflake'];
?>

<script>window.siteUrl = '<?php echo SITE_URL; ?>';</script>

<div class="page-hero">
    <div class="container">
        <h1>Shop by Category</h1>
        <p>Browse our diverse collection of fashion categories</p>
        <div class="breadcrumb">
            <a href="<?php echo SITE_URL; ?>">Home</a>
            <span>/</span>
            <span class="current">Categories</span>
        </div>
    </div>
</div>

<section class="section-padding">
    <div class="container">
        <div class="categories-grid" style="grid-template-columns: repeat(auto-fill, minmax(280px, 1fr));">
            <?php $i = 0; while ($cat = $catResult->fetch_assoc()): ?>
            <a href="<?php echo SITE_URL; ?>/pages/products.php?category=<?php echo $cat['id']; ?>" class="category-card" style="padding: 40px 25px;">
                <div class="cat-icon" style="width: 80px; height: 80px; font-size: 2rem;">
                    <i class="<?php echo $icons[$i % count($icons)]; ?>"></i>
                </div>
                <h3 style="font-size: 1.2rem;"><?php echo htmlspecialchars($cat['name']); ?></h3>
                <p><?php echo htmlspecialchars($cat['description']); ?></p>
                <p style="color: var(--accent); font-size: 0.85rem; margin-top: 10px;">
                    <?php echo $cat['product_count']; ?> Product<?php echo $cat['product_count'] !== 1 ? 's' : ''; ?> <i class="fas fa-arrow-right" style="font-size: 0.75rem;"></i>
                </p>
            </a>
            <?php $i++; endwhile; ?>
        </div>
    </div>
</section>

<?php require_once '../includes/footer.php'; ?>
