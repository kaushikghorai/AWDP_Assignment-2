# LUXE — Dynamic E-Commerce Web Application (PHP & MySQL)

A luxury fashion & clothing e-commerce platform built with PHP and MySQL, featuring a responsive customer storefront and a complete administrative control center.

---

## 💎 Project Features

### 1. Customer Storefront
- **Home Page (`index.php`)**: Hero banner, dynamic category showcase, featured products, new arrivals, promotional offers, and footer navigation.
- **Categories Page (`pages/categories.php`)**: Dynamically loaded product categories with image cards, descriptions, and direct product catalog links.
- **Product Catalog (`pages/products.php`)**: Category filters, real-time keyword search, sorting options (price low-high, high-low, newest), and quick "Add to Cart".
- **Product Detail (`pages/product_detail.php`)**: High-resolution image view, pricing with discount calculations, stock availability, specifications, quantity selector, and "Buy Now".
- **Shopping Cart (`pages/cart.php` & `pages/cart_action.php`)**: Real-time AJAX item count updates, quantity adjustment, subtotal & total calculations, and item removal.
- **Checkout (`pages/checkout.php`)**: Secure multi-field checkout with billing/shipping addresses, order review, and Cash on Delivery integration.
- **Order Confirmation (`pages/order_success.php`)**: Receipt page showing Order ID, items breakdown, payment details, and tracking links.
- **Customer Profile (`pages/profile.php`)**: View and update profile information, shipping addresses, and secure password updates.
- **Order History (`pages/orders.php`)**: Customer dashboard showing all previous orders, order statuses, and itemized invoice details.
- **About Us (`pages/about.php`)**: Brand heritage, mission, vision, values, and customer trust badges.
- **Contact Us (`pages/contact.php`)**: Store locations, customer concierge details, and an interactive contact form that saves inquiries to the database.
- **Authentication (`pages/login.php` & `pages/register.php`)**: Secure customer registration and login with `password_hash()` and session state management.

---

### 2. Admin Control Panel (`/admin`)
- **Admin Authentication (`admin/login.php`)**: Dedicated, secure administrator login gate.
- **Dashboard (`admin/index.php`)**: Real-time business metrics — Total Revenue, Orders Count, Product Inventory, Registered Customers, Low Stock Alerts, and Recent Activity.
- **Product Management (`admin/products.php`)**: Full CRUD operations:
  - Add / Edit product with image uploads or asset filenames
  - Price and discount pricing
  - Stock quantity tracking with low-stock alerts
  - Featured & New Arrival flags
  - One-click active/inactive status toggling
  - Multi-criteria filtering and keyword search
- **Category Management (`admin/categories.php`)**: CRUD operations with image support, product count indicators, and status controls.
- **Order Fulfillment (`admin/orders.php`)**:
  - Filter and search customer orders
  - Drill-down into order line items, quantities, and delivery addresses
  - Milestone status transitions: `Pending` ➔ `Confirmed` ➔ `Processing` ➔ `Shipped` ➔ `Delivered` ➔ `Cancelled`
  - Payment status controls: `Pending`, `Paid`, `Failed`
- **Customer & User Management (`admin/users.php`)**:
  - View all registered customers and administrators
  - Add new accounts with role assignment (`customer` / `admin`)
  - Edit user details, address, and credentials
  - Account suspension / deactivation and deletion safeguards
- **Inquiry Management (`admin/messages.php`)**:
  - Review contact form submissions
  - Mark messages as read / unread
  - View full message modal with 1-click email reply

---

## 🛠️ Technology Stack
- **Backend**: PHP (PHP 7.4 / 8.0 / 8.1 / 8.2+)
- **Database**: MySQL / MariaDB (Prepared Statements & PDO/MySQLi with `InnoDB`)
- **Frontend**: Semantic HTML5, Vanilla CSS3 (Custom Luxury Dark/Gold Design System), JavaScript (ES6+ AJAX)
- **Icons**: FontAwesome 6

---

## 🚀 Setup & Installation Instructions

### 1. Web Server Setup
Place the project in your local web server root directory:
- **XAMPP**: `htdocs/Project`
- **WAMP**: `www/Project`
- **MAMP**: `htdocs/Project`
- **PHP Built-in Server**: Run `php -S localhost:8000` from the project root.

### 2. Database Import
1. Open **phpMyAdmin** or your MySQL command line client.
2. Create or import the database:
   ```sql
   mysql -u root -p < database/luxe_ecommerce.sql
   ```
   *This automatically creates the `luxe_ecommerce` database, all necessary tables, sample categories, sample products, and default administrative credentials.*

### 3. Database Configuration
If your MySQL credentials differ from `root` with no password, adjust `config/database.php`:
```php
define('DB_HOST', 'localhost');
define('DB_USER', 'root');
define('DB_PASS', '');
define('DB_NAME', 'luxe_ecommerce');
define('SITE_URL', 'http://localhost/Project');
```

---

## 🔑 Default Credentials

### Administrator Account
- **URL**: `http://localhost/Project/admin/login.php`
- **Email**: `admin@luxe.com`
- **Password**: `admin123`

### Customer Account
You can register a new customer account via `http://localhost/Project/pages/register.php` or sign in with any created account.

---

## 📁 File Structure
```
Project/
├── index.php                  # Main storefront homepage
├── config/
│   ├── database.php           # Database connection & URL constants
│   └── helpers.php            # Security sanitizers, session helpers, price formatting
├── database/
│   └── luxe_ecommerce.sql     # Database schema, table structures, and seeds
├── includes/
│   ├── header.php             # Customer site navigation, cart badge, session header
│   └── footer.php             # Customer site footer and scripts
├── pages/
│   ├── products.php           # Shop / product listing with search & filters
│   ├── product_detail.php     # Dynamic single product page
│   ├── categories.php         # Categories collection page
│   ├── cart.php               # Shopping cart page
│   ├── cart_action.php        # AJAX cart operations (add, update, remove)
│   ├── checkout.php           # Checkout with addresses & order placement
│   ├── order_success.php      # Order confirmation & receipt
│   ├── orders.php             # Customer order history & details
│   ├── profile.php            # Customer profile & password management
│   ├── about.php              # About LUXE brand story
│   ├── contact.php            # Contact details & inquiry submission form
│   ├── login.php              # Customer login
│   ├── register.php           # Customer registration
│   └── logout.php             # Customer logout
├── admin/
│   ├── index.php              # Admin dashboard with business statistics
│   ├── login.php              # Dedicated admin login
│   ├── logout.php             # Admin session logout
│   ├── products.php           # Admin products CRUD & stock management
│   ├── categories.php         # Admin categories CRUD
│   ├── orders.php             # Admin orders summary & status updates
│   ├── users.php              # Admin user accounts management
│   ├── messages.php           # Admin contact inquiry inbox
│   ├── includes/
│   │   ├── header.php         # Admin sidebar, navigation & auth verification
│   │   └── footer.php         # Admin scripts & modal handlers
│   └── assets/
│       └── css/admin.css      # Admin dark theme stylesheet
└── assets/
    ├── css/style.css          # Main customer storefront stylesheet
    ├── js/main.js             # Customer JS & AJAX cart interaction
    └── images/                # Product images and uploads directory
```
