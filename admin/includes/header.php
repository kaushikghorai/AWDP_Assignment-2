<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

require_once __DIR__ . '/../../config/database.php';
require_once __DIR__ . '/../../config/helpers.php';

// Auth check: Must be logged in as admin
if (!isAdmin()) {
    $_SESSION['admin_redirect'] = $_SERVER['REQUEST_URI'];
    redirect(ADMIN_URL . '/login.php');
}

$currentUser = getCurrentUser();
$currentAdminPage = basename($_SERVER['PHP_SELF'], '.php');

// Unread messages count
$msgCountQuery = $conn->query("SELECT COUNT(*) as count FROM contact_messages WHERE is_read = 0");
$unreadMessages = $msgCountQuery ? $msgCountQuery->fetch_assoc()['count'] : 0;

// Pending orders count
$pendingOrdersQuery = $conn->query("SELECT COUNT(*) as count FROM orders WHERE order_status = 'pending'");
$pendingOrders = $pendingOrdersQuery ? $pendingOrdersQuery->fetch_assoc()['count'] : 0;
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>LUXE Admin - <?php echo isset($adminTitle) ? $adminTitle : 'Dashboard'; ?></title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="<?php echo ADMIN_URL; ?>/assets/css/admin.css">
</head>
<body>

<!-- Admin Sidebar -->
<aside class="admin-sidebar" id="adminSidebar">
    <div class="sidebar-brand">
        <a href="<?php echo ADMIN_URL; ?>/index.php">
            <span class="brand-logo">LUXE</span>
            <span class="brand-badge">ADMIN</span>
        </a>
    </div>

    <div class="sidebar-nav">
        <div class="nav-section-title">Core Management</div>
        <a href="<?php echo ADMIN_URL; ?>/index.php" class="admin-nav-item <?php echo $currentAdminPage === 'index' ? 'active' : ''; ?>">
            <i class="fas fa-th-large"></i>
            <span>Dashboard</span>
        </a>
        <a href="<?php echo ADMIN_URL; ?>/orders.php" class="admin-nav-item <?php echo $currentAdminPage === 'orders' ? 'active' : ''; ?>">
            <i class="fas fa-shopping-bag"></i>
            <span>Orders</span>
            <?php if ($pendingOrders > 0): ?>
                <span class="badge badge-pending" style="margin-left: auto;"><?php echo $pendingOrders; ?></span>
            <?php endif; ?>
        </a>
        <a href="<?php echo ADMIN_URL; ?>/products.php" class="admin-nav-item <?php echo $currentAdminPage === 'products' ? 'active' : ''; ?>">
            <i class="fas fa-tshirt"></i>
            <span>Products</span>
        </a>
        <a href="<?php echo ADMIN_URL; ?>/categories.php" class="admin-nav-item <?php echo $currentAdminPage === 'categories' ? 'active' : ''; ?>">
            <i class="fas fa-tags"></i>
            <span>Categories</span>
        </a>
        <a href="<?php echo ADMIN_URL; ?>/users.php" class="admin-nav-item <?php echo $currentAdminPage === 'users' ? 'active' : ''; ?>">
            <i class="fas fa-users"></i>
            <span>Users & Customers</span>
        </a>
        <a href="<?php echo ADMIN_URL; ?>/messages.php" class="admin-nav-item <?php echo $currentAdminPage === 'messages' ? 'active' : ''; ?>">
            <i class="fas fa-envelope"></i>
            <span>Inquiries</span>
            <?php if ($unreadMessages > 0): ?>
                <span class="badge badge-warning" style="margin-left: auto;"><?php echo $unreadMessages; ?></span>
            <?php endif; ?>
        </a>

        <div class="nav-section-title">Storefront</div>
        <a href="<?php echo SITE_URL; ?>/index.php" target="_blank" class="admin-nav-item">
            <i class="fas fa-external-link-alt"></i>
            <span>View Website</span>
        </a>
        <a href="<?php echo ADMIN_URL; ?>/logout.php" class="admin-nav-item" style="color: var(--danger);">
            <i class="fas fa-sign-out-alt"></i>
            <span>Logout</span>
        </a>
    </div>

    <div class="sidebar-footer">
        <div class="admin-profile-pill">
            <div class="admin-avatar">
                <?php echo strtoupper(substr($currentUser['name'] ?? 'A', 0, 1)); ?>
            </div>
            <div>
                <div class="admin-name"><?php echo htmlspecialchars($currentUser['name'] ?? 'Administrator'); ?></div>
                <div class="admin-role">Super Admin</div>
            </div>
        </div>
        <a href="<?php echo ADMIN_URL; ?>/logout.php" title="Logout" style="color: var(--text-muted);">
            <i class="fas fa-power-off"></i>
        </a>
    </div>
</aside>

<!-- Admin Main Container -->
<div class="admin-main">
    <header class="admin-header">
        <div style="display: flex; align-items: center; gap: 15px;">
            <button class="btn btn-icon btn-outline" id="sidebarToggle" onclick="toggleAdminSidebar()" style="display: none;">
                <i class="fas fa-bars"></i>
            </button>
            <h2 style="font-size: 1.15rem; font-weight: 600;"><?php echo isset($adminTitle) ? $adminTitle : 'Dashboard'; ?></h2>
        </div>

        <div class="header-actions">
            <a href="<?php echo SITE_URL; ?>" target="_blank" class="btn btn-outline btn-sm">
                <i class="fas fa-store"></i> Live Store
            </a>
            <a href="<?php echo ADMIN_URL; ?>/messages.php" class="btn btn-icon btn-outline" title="Messages" style="position: relative;">
                <i class="fas fa-bell"></i>
                <?php if ($unreadMessages > 0): ?>
                    <span style="position: absolute; top: -4px; right: -4px; width: 8px; height: 8px; background: var(--primary); border-radius: 50%;"></span>
                <?php endif; ?>
            </a>
            <div class="admin-profile-pill">
                <div class="admin-avatar" style="width: 32px; height: 32px; font-size: 0.85rem;">
                    <?php echo strtoupper(substr($currentUser['name'] ?? 'A', 0, 1)); ?>
                </div>
            </div>
        </div>
    </header>

    <div class="admin-body">
        <?php
        $flash = getFlashMessage();
        if ($flash): ?>
            <div style="padding: 14px 18px; border-radius: var(--radius-sm); margin-bottom: 24px; display: flex; align-items: center; gap: 12px; background: <?php echo $flash['type'] === 'success' ? 'var(--success-bg)' : 'var(--danger-bg)'; ?>; border: 1px solid <?php echo $flash['type'] === 'success' ? 'var(--success)' : 'var(--danger)'; ?>; color: <?php echo $flash['type'] === 'success' ? '#34d399' : '#f87171'; ?>;">
                <i class="fas fa-<?php echo $flash['type'] === 'success' ? 'check-circle' : 'exclamation-circle'; ?>"></i>
                <span><?php echo $flash['message']; ?></span>
            </div>
        <?php endif; ?>
