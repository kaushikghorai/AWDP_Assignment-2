<?php
/**
 * Helper Functions
 * LUXE E-Commerce
 */

/**
 * Sanitize user input
 */
function sanitize($data) {
    global $conn;
    return htmlspecialchars(strip_tags(trim($conn->real_escape_string($data))));
}

/**
 * Redirect to a URL
 */
function redirect($url) {
    header("Location: $url");
    exit();
}

/**
 * Check if user is logged in
 */
function isLoggedIn() {
    return isset($_SESSION['user_id']);
}

/**
 * Check if user is admin
 */
function isAdmin() {
    return isset($_SESSION['user_role']) && $_SESSION['user_role'] === 'admin';
}

/**
 * Get current user data
 */
function getCurrentUser() {
    global $conn;
    if (!isLoggedIn()) return null;
    $id = $_SESSION['user_id'];
    $stmt = $conn->prepare("SELECT * FROM users WHERE id = ?");
    $stmt->bind_param("i", $id);
    $stmt->execute();
    $result = $stmt->get_result();
    return $result->fetch_assoc();
}

/**
 * Format price in INR
 */
function formatPrice($price) {
    return '₹' . number_format($price, 2);
}

/**
 * Get cart count
 */
function getCartCount() {
    if (!isset($_SESSION['cart'])) return 0;
    $count = 0;
    foreach ($_SESSION['cart'] as $item) {
        $count += $item['quantity'];
    }
    return $count;
}

/**
 * Get cart total
 */
function getCartTotal() {
    global $conn;
    if (!isset($_SESSION['cart']) || empty($_SESSION['cart'])) return 0;
    $total = 0;
    foreach ($_SESSION['cart'] as $product_id => $item) {
        $stmt = $conn->prepare("SELECT price, discount_price FROM products WHERE id = ?");
        $stmt->bind_param("i", $product_id);
        $stmt->execute();
        $result = $stmt->get_result();
        $product = $result->fetch_assoc();
        if ($product) {
            $price = $product['discount_price'] ? $product['discount_price'] : $product['price'];
            $total += $price * $item['quantity'];
        }
    }
    return $total;
}

/**
 * Display flash message
 */
function setFlashMessage($type, $message) {
    $_SESSION['flash'] = ['type' => $type, 'message' => $message];
}

function getFlashMessage() {
    if (isset($_SESSION['flash'])) {
        $flash = $_SESSION['flash'];
        unset($_SESSION['flash']);
        return $flash;
    }
    return null;
}

/**
 * Generate star rating HTML
 */
function renderStars($rating) {
    $html = '<div class="stars">';
    $fullStars = floor($rating);
    $halfStar = ($rating - $fullStars) >= 0.5;
    for ($i = 0; $i < $fullStars; $i++) {
        $html .= '<i class="fas fa-star"></i>';
    }
    if ($halfStar) {
        $html .= '<i class="fas fa-star-half-alt"></i>';
        $fullStars++;
    }
    for ($i = $fullStars; $i < 5; $i++) {
        $html .= '<i class="far fa-star"></i>';
    }
    $html .= '</div>';
    return $html;
}

/**
 * Truncate text
 */
function truncateText($text, $length = 100) {
    if (strlen($text) <= $length) return $text;
    return substr($text, 0, $length) . '...';
}
