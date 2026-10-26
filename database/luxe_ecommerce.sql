-- ============================================================
-- LUXE E-Commerce Database Schema
-- Fashion & Clothing Store
-- ============================================================

CREATE DATABASE IF NOT EXISTS luxe_ecommerce;
USE luxe_ecommerce;

-- ============================================================
-- USERS TABLE
-- ============================================================
CREATE TABLE IF NOT EXISTS users (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(100) NOT NULL,
    email VARCHAR(150) NOT NULL UNIQUE,
    mobile VARCHAR(20),
    password VARCHAR(255) NOT NULL,
    address TEXT,
    city VARCHAR(100),
    state VARCHAR(100),
    pincode VARCHAR(10),
    role ENUM('customer', 'admin') DEFAULT 'customer',
    status ENUM('active', 'inactive') DEFAULT 'active',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB;

-- ============================================================
-- CATEGORIES TABLE
-- ============================================================
CREATE TABLE IF NOT EXISTS categories (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(100) NOT NULL,
    image VARCHAR(255),
    description TEXT,
    status ENUM('active', 'inactive') DEFAULT 'active',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB;

-- ============================================================
-- PRODUCTS TABLE
-- ============================================================
CREATE TABLE IF NOT EXISTS products (
    id INT AUTO_INCREMENT PRIMARY KEY,
    category_id INT NOT NULL,
    name VARCHAR(200) NOT NULL,
    image VARCHAR(255),
    price DECIMAL(10, 2) NOT NULL,
    discount_price DECIMAL(10, 2) DEFAULT NULL,
    description TEXT,
    short_description VARCHAR(500),
    specifications TEXT,
    stock_quantity INT DEFAULT 0,
    rating DECIMAL(2, 1) DEFAULT 0.0,
    is_featured TINYINT(1) DEFAULT 0,
    is_new TINYINT(1) DEFAULT 0,
    status ENUM('active', 'inactive') DEFAULT 'active',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (category_id) REFERENCES categories(id) ON DELETE CASCADE
) ENGINE=InnoDB;

-- ============================================================
-- ORDERS TABLE
-- ============================================================
CREATE TABLE IF NOT EXISTS orders (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    customer_name VARCHAR(100) NOT NULL,
    email VARCHAR(150),
    mobile VARCHAR(20),
    billing_address TEXT,
    shipping_address TEXT,
    city VARCHAR(100),
    state VARCHAR(100),
    pincode VARCHAR(10),
    total_amount DECIMAL(10, 2) NOT NULL,
    payment_method VARCHAR(50) DEFAULT 'Cash on Delivery',
    payment_status ENUM('pending', 'paid', 'failed') DEFAULT 'pending',
    order_status ENUM('pending', 'confirmed', 'processing', 'shipped', 'delivered', 'cancelled') DEFAULT 'pending',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
) ENGINE=InnoDB;

-- ============================================================
-- ORDER ITEMS TABLE
-- ============================================================
CREATE TABLE IF NOT EXISTS order_items (
    id INT AUTO_INCREMENT PRIMARY KEY,
    order_id INT NOT NULL,
    product_id INT NOT NULL,
    product_name VARCHAR(200),
    product_image VARCHAR(255),
    price DECIMAL(10, 2) NOT NULL,
    quantity INT NOT NULL DEFAULT 1,
    subtotal DECIMAL(10, 2) NOT NULL,
    FOREIGN KEY (order_id) REFERENCES orders(id) ON DELETE CASCADE,
    FOREIGN KEY (product_id) REFERENCES products(id) ON DELETE CASCADE
) ENGINE=InnoDB;

-- ============================================================
-- CONTACT MESSAGES TABLE
-- ============================================================
CREATE TABLE IF NOT EXISTS contact_messages (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(100) NOT NULL,
    email VARCHAR(150) NOT NULL,
    subject VARCHAR(200),
    message TEXT NOT NULL,
    is_read TINYINT(1) DEFAULT 0,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB;

-- ============================================================
-- INSERT DEFAULT ADMIN USER
-- Password: admin123 (hashed with password_hash)
-- ============================================================
INSERT INTO users (name, email, mobile, password, role, status)
VALUES ('Admin', 'admin@luxe.com', '9999999999', '$2y$10$YEhGRXBFblVLLlFObUlUd.eWkHzQKxEUfmk3JGrS9D7N3bKpKGIaK', 'admin', 'active');

-- ============================================================
-- INSERT SAMPLE CATEGORIES
-- ============================================================
INSERT INTO categories (name, image, description, status) VALUES
('Men\'s Wear', 'mens_wear.jpg', 'Discover the latest trends in men\'s fashion. From casual tees to formal suits, find your perfect style.', 'active'),
('Women\'s Wear', 'womens_wear.jpg', 'Explore our elegant women\'s collection featuring dresses, tops, and more for every occasion.', 'active'),
('Kids\' Wear', 'kids_wear.jpg', 'Adorable and comfortable clothing for kids. Fun designs that let them play in style.', 'active'),
('Accessories', 'accessories.jpg', 'Complete your look with our premium accessories - watches, belts, bags, and more.', 'active'),
('Footwear', 'footwear.jpg', 'Step out in style with our trendy footwear collection for all occasions.', 'active'),
('Winter Collection', 'winter.jpg', 'Stay warm and fashionable with our exclusive winter collection - jackets, sweaters, and coats.', 'active');

-- ============================================================
-- INSERT SAMPLE PRODUCTS
-- ============================================================
INSERT INTO products (category_id, name, image, price, discount_price, description, short_description, stock_quantity, rating, is_featured, is_new, status) VALUES
-- Men's Wear
(1, 'Classic Slim-Fit Suit', 'suit.jpg', 12999.00, 9999.00, 'Expertly tailored classic slim-fit suit made from premium Italian wool blend. Features a two-button closure, notch lapels, and a modern silhouette that flatters every body type. Perfect for business meetings, formal events, and special occasions. Includes matching trousers with a flat front design.\n\nFabric: 70% Wool, 30% Polyester\nCare: Dry clean only', 'Premium slim-fit suit in Italian wool blend. Perfect for formal occasions.', 50, 4.5, 1, 0, 'active'),
(1, 'Cotton Casual Shirt', 'casual_shirt.jpg', 1999.00, 1499.00, 'Comfortable cotton casual shirt with a relaxed fit. Features a button-down collar, chest pocket, and rolled-up sleeve tabs. Available in multiple colors.\n\nFabric: 100% Premium Cotton\nCare: Machine washable', 'Relaxed-fit cotton casual shirt. Perfect for everyday wear.', 120, 4.2, 1, 1, 'active'),
(1, 'Denim Jeans Premium', 'denim.jpg', 2999.00, 2499.00, 'Premium denim jeans with a modern straight-leg cut. Made from high-quality stretch denim for comfort and durability. Features five-pocket styling and a classic button fly.\n\nFabric: 98% Cotton, 2% Elastane\nCare: Machine washable', 'Premium straight-leg denim jeans with stretch comfort.', 200, 4.3, 0, 1, 'active'),
(1, 'Polo T-Shirt', 'polo.jpg', 1299.00, 999.00, 'Classic polo t-shirt made from breathable cotton pique fabric. Features a ribbed collar, two-button placket, and embroidered logo. Ideal for casual outings.\n\nFabric: 100% Cotton Pique\nCare: Machine washable', 'Classic cotton pique polo shirt for casual style.', 150, 4.0, 0, 0, 'active'),

-- Women's Wear
(2, 'Floral Maxi Dress', 'maxi_dress.jpg', 3999.00, 2999.00, 'Stunning floral maxi dress with a flowing silhouette. Features a V-neckline, adjustable spaghetti straps, and a tiered ruffle hem. Made from lightweight chiffon that drapes beautifully.\n\nFabric: 100% Polyester Chiffon\nCare: Hand wash recommended', 'Beautiful floral maxi dress in lightweight chiffon.', 75, 4.7, 1, 1, 'active'),
(2, 'Silk Blouse', 'silk_blouse.jpg', 2499.00, 1999.00, 'Elegant silk blouse with a relaxed fit. Features a mandarin collar, concealed button placket, and French cuffs. The lustrous silk fabric adds sophistication to any outfit.\n\nFabric: 100% Mulberry Silk\nCare: Dry clean only', 'Luxurious silk blouse with mandarin collar and French cuffs.', 60, 4.4, 1, 0, 'active'),
(2, 'High-Waist Palazzo Pants', 'palazzo.jpg', 1899.00, 1499.00, 'Chic high-waist palazzo pants with a wide-leg silhouette. Features a comfortable elastic waistband and flowing drape. Perfect for both casual and semi-formal occasions.\n\nFabric: 95% Polyester, 5% Spandex\nCare: Machine washable', 'Flowing high-waist palazzo pants for effortless elegance.', 90, 4.1, 0, 1, 'active'),
(2, 'Embroidered Kurta', 'kurta.jpg', 2299.00, 1799.00, 'Beautiful embroidered kurta with intricate threadwork. Features a round neckline, three-quarter sleeves, and side slits. Perfect for festive occasions.\n\nFabric: Cotton Blend\nCare: Hand wash', 'Intricately embroidered kurta for festive occasions.', 80, 4.6, 1, 0, 'active'),

-- Kids' Wear
(3, 'Cartoon Print T-Shirt Set', 'kids_tshirt.jpg', 999.00, 749.00, 'Fun cartoon print t-shirt and shorts set for kids. Made from soft, breathable cotton that is gentle on sensitive skin. Features colorful prints that kids love.\n\nFabric: 100% Organic Cotton\nCare: Machine washable', 'Colorful cartoon print cotton set for kids.', 200, 4.3, 1, 1, 'active'),
(3, 'Kids Party Dress', 'kids_dress.jpg', 1999.00, 1499.00, 'Adorable party dress for girls with lace detailing and a tulle skirt. Features a satin bodice, bow accents, and a comfortable lining. Perfect for birthdays and celebrations.\n\nFabric: Polyester Satin & Tulle\nCare: Hand wash', 'Beautiful lace and tulle party dress for girls.', 60, 4.5, 1, 0, 'active'),

-- Accessories
(4, 'Leather Watch', 'watch.jpg', 4999.00, 3999.00, 'Classic leather strap watch with a stainless steel case. Features an analog display with date function, water resistance up to 50m, and a genuine leather strap.\n\nCase: Stainless Steel\nStrap: Genuine Leather\nMovement: Japanese Quartz', 'Classic analog watch with genuine leather strap.', 40, 4.6, 1, 1, 'active'),
(4, 'Designer Handbag', 'handbag.jpg', 5999.00, 4499.00, 'Premium designer handbag made from genuine leather. Features multiple compartments, a detachable shoulder strap, and elegant gold-tone hardware. Spacious enough for daily essentials.\n\nMaterial: Genuine Leather\nHardware: Gold-tone', 'Elegant genuine leather designer handbag.', 30, 4.8, 1, 0, 'active'),

-- Footwear
(5, 'Classic Leather Oxfords', 'oxfords.jpg', 5499.00, 4499.00, 'Handcrafted leather Oxford shoes with Goodyear welted construction. Features a cap-toe design, leather sole, and cushioned insole for all-day comfort.\n\nMaterial: Full-grain Leather\nSole: Leather with rubber heel\nConstruction: Goodyear Welted', 'Handcrafted leather Oxfords with Goodyear welted sole.', 45, 4.5, 1, 0, 'active'),
(5, 'Running Sneakers', 'sneakers.jpg', 3999.00, 2999.00, 'High-performance running sneakers with responsive cushioning and breathable mesh upper. Features a lightweight EVA midsole, rubber outsole with flex grooves, and a padded tongue.\n\nUpper: Breathable Mesh\nMidsole: EVA Foam\nOutsole: Rubber', 'Lightweight running sneakers with responsive cushioning.', 100, 4.4, 0, 1, 'active'),

-- Winter Collection
(6, 'Wool Overcoat', 'overcoat.jpg', 8999.00, 6999.00, 'Luxurious wool overcoat with a tailored fit. Features a notch collar, double-breasted closure, and satin lining. Perfect for cold weather elegance.\n\nFabric: 80% Wool, 20% Polyester\nLining: Satin\nCare: Dry clean only', 'Tailored double-breasted wool overcoat for winter.', 35, 4.7, 1, 1, 'active'),
(6, 'Cashmere Sweater', 'cashmere.jpg', 4999.00, 3999.00, 'Ultra-soft cashmere sweater with a crew neck design. Features ribbed cuffs and hem, and a relaxed fit. Lightweight yet incredibly warm.\n\nFabric: 100% Grade A Cashmere\nCare: Hand wash in cold water', 'Ultra-soft 100% cashmere crew neck sweater.', 55, 4.6, 1, 0, 'active');
