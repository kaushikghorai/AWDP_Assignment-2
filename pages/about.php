<?php
$pageTitle = 'About Us';
require_once '../includes/header.php';
?>

<script>window.siteUrl = '<?php echo SITE_URL; ?>';</script>

<div class="page-hero">
    <div class="container">
        <h1>About LUXE</h1>
        <p>Discover the story behind our passion for fashion</p>
        <div class="breadcrumb">
            <a href="<?php echo SITE_URL; ?>">Home</a>
            <span>/</span>
            <span class="current">About Us</span>
        </div>
    </div>
</div>

<section class="section-padding">
    <div class="container">
        <div class="about-grid">
            <div class="about-image">
                <div style="text-align: center; color: var(--text-muted);">
                    <i class="fas fa-gem" style="font-size: 5rem; color: var(--accent); opacity: 0.3; margin-bottom: 15px;"></i>
                    <p style="font-size: 1.2rem; font-family: var(--font-heading);">LUXE Fashion</p>
                </div>
            </div>
            <div class="about-content">
                <h2>Our Story</h2>
                <p>Founded in 2020, LUXE was born from a passion for making premium fashion accessible to everyone. We believe that style isn't just about what you wear — it's about how you feel. Our carefully curated collections blend timeless elegance with contemporary trends, ensuring that every piece in our store meets our exacting standards of quality and design.</p>
                <p>From the bustling streets of Mumbai to the fashion capitals of the world, we source the finest fabrics and partner with talented designers to bring you clothing that makes a statement. Every stitch tells a story, and every garment is crafted to be your trusted companion through life's moments — big and small.</p>
                <p>At LUXE, we don't just sell clothes. We help you express your unique personality through carefully crafted fashion that stands the test of time.</p>
            </div>
        </div>
    </div>
</section>

<!-- Mission, Vision, Values -->
<section class="section-padding" style="background: var(--primary);">
    <div class="container">
        <div class="section-header">
            <h2>What Drives Us</h2>
            <p>Our core values shape everything we do</p>
            <div class="divider"></div>
        </div>

        <div class="values-grid">
            <div class="value-card">
                <i class="fas fa-bullseye"></i>
                <h4>Our Mission</h4>
                <p>To democratize premium fashion by offering world-class clothing at accessible prices, while maintaining the highest standards of quality and sustainability.</p>
            </div>
            <div class="value-card">
                <i class="fas fa-eye"></i>
                <h4>Our Vision</h4>
                <p>To become India's most trusted fashion destination, where style meets substance and every customer finds their perfect fit and expression.</p>
            </div>
            <div class="value-card">
                <i class="fas fa-leaf"></i>
                <h4>Sustainability</h4>
                <p>We're committed to responsible fashion. From eco-friendly packaging to ethical sourcing, we strive to minimize our environmental footprint.</p>
            </div>
            <div class="value-card">
                <i class="fas fa-heart"></i>
                <h4>Customer First</h4>
                <p>Your satisfaction is our priority. We offer hassle-free returns, responsive support, and a seamless shopping experience at every touchpoint.</p>
            </div>
            <div class="value-card">
                <i class="fas fa-award"></i>
                <h4>Quality Promise</h4>
                <p>Every product undergoes rigorous quality checks. We stand behind our products with confidence and ensure premium quality in every detail.</p>
            </div>
            <div class="value-card">
                <i class="fas fa-handshake"></i>
                <h4>Ethical Fashion</h4>
                <p>We partner with manufacturers who share our values of fair wages, safe working conditions, and respect for artisan craftsmanship.</p>
            </div>
        </div>
    </div>
</section>

<!-- Stats -->
<section class="section-padding">
    <div class="container">
        <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(200px, 1fr)); gap: 30px; text-align: center;">
            <div>
                <h3 style="font-size: 2.5rem; background: var(--gold-gradient); -webkit-background-clip: text; -webkit-text-fill-color: transparent; background-clip: text;">50K+</h3>
                <p style="color: var(--text-muted);">Happy Customers</p>
            </div>
            <div>
                <h3 style="font-size: 2.5rem; background: var(--gold-gradient); -webkit-background-clip: text; -webkit-text-fill-color: transparent; background-clip: text;">1000+</h3>
                <p style="color: var(--text-muted);">Products</p>
            </div>
            <div>
                <h3 style="font-size: 2.5rem; background: var(--gold-gradient); -webkit-background-clip: text; -webkit-text-fill-color: transparent; background-clip: text;">100+</h3>
                <p style="color: var(--text-muted);">Brands</p>
            </div>
            <div>
                <h3 style="font-size: 2.5rem; background: var(--gold-gradient); -webkit-background-clip: text; -webkit-text-fill-color: transparent; background-clip: text;">500+</h3>
                <p style="color: var(--text-muted);">Cities Served</p>
            </div>
        </div>
    </div>
</section>

<?php require_once '../includes/footer.php'; ?>
