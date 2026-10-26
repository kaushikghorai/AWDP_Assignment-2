<?php
/**
 * Database Configuration
 * LUXE E-Commerce - Fashion & Clothing Store
 */

define('DB_HOST', 'localhost');
define('DB_USER', 'root');
define('DB_PASS', '');
define('DB_NAME', 'luxe_ecommerce');

// Create connection
$conn = new mysqli(DB_HOST, DB_USER, DB_PASS, DB_NAME);

// Check connection
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Set charset
$conn->set_charset("utf8mb4");

// Site configuration
define('SITE_NAME', 'LUXE');
define('SITE_URL', 'http://localhost/Project');
define('ADMIN_URL', SITE_URL . '/admin');
define('UPLOADS_DIR', __DIR__ . '/../assets/images/uploads/');
define('UPLOADS_URL', SITE_URL . '/assets/images/uploads/');
