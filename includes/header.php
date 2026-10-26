<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../config/helpers.php';

$cartCount = getCartCount();
$currentPage = basename($_SERVER['PHP_SELF'], '.php');
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="description" content="LUXE - Premium Fashion & Clothing Store. Discover the latest trends in men's, women's, and kids' fashion.">
    <title>LUXE - Premium Fashion Store<?php echo isset($pageTitle) ? ' | ' . $pageTitle : ''; ?></title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="<?php echo SITE_URL; ?>/assets/css/style.css">
</head>
<body>

<!-- Navigation -->
<nav class="navbar" id="navbar">
    <div class="nav-container">
        <a href="<?php echo SITE_URL; ?>/index.php" class="nav-logo">LUXE</a>
        
        <div class="nav-menu" id="navMenu">
            <a href="<?php echo SITE_URL; ?>/index.php" class="<?php echo $currentPage === 'index' ? 'active' : ''; ?>">Home</a>
            <a href="<?php echo SITE_URL; ?>/pages/categories.php" class="<?php echo $currentPage === 'categories' ? 'active' : ''; ?>">Categories</a>
            <a href="<?php echo SITE_URL; ?>/pages/products.php" class="<?php echo $currentPage === 'products' ? 'active' : ''; ?>">Shop</a>
            <a href="<?php echo SITE_URL; ?>/pages/about.php" class="<?php echo $currentPage === 'about' ? 'active' : ''; ?>">About</a>
            <a href="<?php echo SITE_URL; ?>/pages/contact.php" class="<?php echo $currentPage === 'contact' ? 'active' : ''; ?>">Contact</a>
        </div>

        <div class="nav-icons">
            <a href="<?php echo SITE_URL; ?>/pages/cart.php" class="nav-icon">
                <i class="fas fa-shopping-bag"></i>
                <?php if ($cartCount > 0): ?>
                    <span class="cart-count"><?php echo $cartCount; ?></span>
                <?php endif; ?>
            </a>

            <?php if (isLoggedIn()): ?>
                <div class="nav-user-dropdown">
                    <a href="#" class="nav-icon"><i class="fas fa-user"></i></a>
                    <div class="dropdown-menu">
                        <a href="<?php echo SITE_URL; ?>/pages/profile.php"><i class="fas fa-user-circle"></i> My Profile</a>
                        <a href="<?php echo SITE_URL; ?>/pages/orders.php"><i class="fas fa-box"></i> My Orders</a>
                        <div class="dropdown-divider"></div>
                        <a href="<?php echo SITE_URL; ?>/pages/logout.php"><i class="fas fa-sign-out-alt"></i> Logout</a>
                    </div>
                </div>
            <?php else: ?>
                <a href="<?php echo SITE_URL; ?>/pages/login.php" class="nav-icon"><i class="fas fa-user"></i></a>
            <?php endif; ?>

            <div class="nav-toggle" id="navToggle" onclick="toggleMobileMenu()">
                <span></span>
                <span></span>
                <span></span>
            </div>
        </div>
    </div>
</nav>

<?php
$flash = getFlashMessage();
if ($flash): ?>
<div class="container" style="margin-top: 80px;">
    <div class="flash-message flash-<?php echo $flash['type']; ?>">
        <i class="fas fa-<?php echo $flash['type'] === 'success' ? 'check-circle' : ($flash['type'] === 'error' ? 'exclamation-circle' : 'exclamation-triangle'); ?>"></i>
        <?php echo $flash['message']; ?>
    </div>
</div>
<?php endif; ?>
