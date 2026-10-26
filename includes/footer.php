<!-- Footer -->
<footer class="footer">
    <div class="container">
        <div class="footer-grid">
            <div class="footer-brand">
                <div class="footer-logo">LUXE</div>
                <p>Your destination for premium fashion and lifestyle products. We curate the finest clothing, accessories, and footwear from world-renowned designers.</p>
                <div class="footer-social">
                    <a href="#"><i class="fab fa-facebook-f"></i></a>
                    <a href="#"><i class="fab fa-instagram"></i></a>
                    <a href="#"><i class="fab fa-twitter"></i></a>
                    <a href="#"><i class="fab fa-pinterest-p"></i></a>
                </div>
            </div>

            <div class="footer-links">
                <h4>Quick Links</h4>
                <a href="<?php echo SITE_URL; ?>/index.php">Home</a>
                <a href="<?php echo SITE_URL; ?>/pages/categories.php">Categories</a>
                <a href="<?php echo SITE_URL; ?>/pages/products.php">Shop All</a>
                <a href="<?php echo SITE_URL; ?>/pages/about.php">About Us</a>
                <a href="<?php echo SITE_URL; ?>/pages/contact.php">Contact Us</a>
            </div>

            <div class="footer-links">
                <h4>Customer Care</h4>
                <a href="<?php echo SITE_URL; ?>/pages/profile.php">My Account</a>
                <a href="<?php echo SITE_URL; ?>/pages/orders.php">Order Tracking</a>
                <a href="<?php echo SITE_URL; ?>/pages/cart.php">Shopping Cart</a>
                <a href="#">Returns & Exchanges</a>
                <a href="#">FAQ</a>
            </div>

            <div class="footer-newsletter">
                <h4>Stay Connected</h4>
                <p>Subscribe to get special offers, free giveaways, and amazing deals.</p>
                <form class="newsletter-form" onsubmit="event.preventDefault(); alert('Thank you for subscribing!');">
                    <input type="email" placeholder="Your email address" required>
                    <button type="submit"><i class="fas fa-paper-plane"></i></button>
                </form>
            </div>
        </div>

        <div class="footer-bottom">
            <p>&copy; <?php echo date('Y'); ?> LUXE Fashion Store. All Rights Reserved. | Designed with <i class="fas fa-heart text-accent"></i></p>
        </div>
    </div>
</footer>

<script src="<?php echo SITE_URL; ?>/assets/js/main.js"></script>
</body>
</html>
