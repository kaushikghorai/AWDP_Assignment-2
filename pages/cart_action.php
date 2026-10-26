<?php
/**
 * Cart Action Handler
 * Handles add, update, remove operations on the shopping cart
 */
session_start();
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../config/helpers.php';

if (!isset($_SESSION['cart'])) {
    $_SESSION['cart'] = [];
}

$action = isset($_POST['action']) ? $_POST['action'] : (isset($_GET['action']) ? $_GET['action'] : '');

switch ($action) {
    case 'add':
        $productId = intval($_POST['product_id']);
        $quantity = max(1, intval($_POST['quantity'] ?? 1));
        
        // Verify product exists and is active
        $stmt = $conn->prepare("SELECT id, stock_quantity FROM products WHERE id = ? AND status = 'active'");
        $stmt->bind_param("i", $productId);
        $stmt->execute();
        $product = $stmt->get_result()->fetch_assoc();
        
        if ($product) {
            if (isset($_SESSION['cart'][$productId])) {
                $_SESSION['cart'][$productId]['quantity'] += $quantity;
            } else {
                $_SESSION['cart'][$productId] = ['quantity' => $quantity];
            }
            // Cap at stock
            if ($_SESSION['cart'][$productId]['quantity'] > $product['stock_quantity']) {
                $_SESSION['cart'][$productId]['quantity'] = $product['stock_quantity'];
            }
            setFlashMessage('success', 'Product added to cart!');
        } else {
            setFlashMessage('error', 'Product not available.');
        }
        break;

    case 'update':
        $productId = intval($_GET['product_id'] ?? 0);
        $newQty = intval($_GET['quantity'] ?? 1);
        
        if ($productId > 0 && isset($_SESSION['cart'][$productId])) {
            if ($newQty <= 0) {
                unset($_SESSION['cart'][$productId]);
                setFlashMessage('success', 'Product removed from cart.');
            } else {
                // Check stock
                $stmt = $conn->prepare("SELECT stock_quantity FROM products WHERE id = ?");
                $stmt->bind_param("i", $productId);
                $stmt->execute();
                $product = $stmt->get_result()->fetch_assoc();
                
                $_SESSION['cart'][$productId]['quantity'] = min($newQty, $product['stock_quantity']);
                setFlashMessage('success', 'Cart updated.');
            }
        }
        break;

    case 'remove':
        $productId = intval($_GET['product_id'] ?? 0);
        if (isset($_SESSION['cart'][$productId])) {
            unset($_SESSION['cart'][$productId]);
            setFlashMessage('success', 'Product removed from cart.');
        }
        break;

    case 'clear':
        $_SESSION['cart'] = [];
        setFlashMessage('success', 'Cart cleared.');
        break;
}

// Redirect back
$redirect = isset($_POST['redirect']) ? $_POST['redirect'] : (isset($_SERVER['HTTP_REFERER']) ? $_SERVER['HTTP_REFERER'] : SITE_URL . '/pages/cart.php');
redirect($redirect);
