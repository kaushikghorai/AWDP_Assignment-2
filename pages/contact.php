<?php
$pageTitle = 'Contact Us';
require_once '../includes/header.php';

$success = '';
$error = '';

$user = isLoggedIn() ? getCurrentUser() : null;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name = sanitize($_POST['name'] ?? '');
    $email = sanitize($_POST['email'] ?? '');
    $subject = sanitize($_POST['subject'] ?? '');
    $message = sanitize($_POST['message'] ?? '');

    if (empty($name) || empty($email) || empty($message)) {
        $error = 'Please fill in all required fields (Name, Email, Message).';
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $error = 'Please enter a valid email address.';
    } else {
        $stmt = $conn->prepare("INSERT INTO contact_messages (name, email, subject, message) VALUES (?, ?, ?, ?)");
        $stmt->bind_param("ssss", $name, $email, $subject, $message);
        
        if ($stmt->execute()) {
            $success = 'Thank you for reaching out! Your message has been sent successfully. We will get back to you shortly.';
            // Clear message field
            $subject = '';
            $message = '';
        } else {
            $error = 'Sorry, an error occurred while sending your message. Please try again.';
        }
    }
}
?>

<div class="page-hero">
    <div class="container">
        <h1>Contact Us</h1>
        <p>We'd love to hear from you. Get in touch with the LUXE concierge team.</p>
        <div class="breadcrumb">
            <a href="<?php echo SITE_URL; ?>">Home</a>
            <span>/</span>
            <span class="current">Contact Us</span>
        </div>
    </div>
</div>

<section class="section-padding">
    <div class="container">
        <?php if ($success): ?>
            <div class="flash-message flash-success" style="margin-bottom: 30px;">
                <i class="fas fa-check-circle"></i> <?php echo $success; ?>
            </div>
        <?php endif; ?>

        <?php if ($error): ?>
            <div class="flash-message flash-error" style="margin-bottom: 30px;">
                <i class="fas fa-exclamation-circle"></i> <?php echo $error; ?>
            </div>
        <?php endif; ?>

        <div class="contact-grid">
            <!-- Contact Information Cards -->
            <div class="contact-info-cards">
                <div>
                    <h2 style="margin-bottom: 15px; font-family: var(--font-heading);">Get In Touch</h2>
                    <p style="color: var(--text-muted); margin-bottom: 25px; line-height: 1.6;">
                        Have questions about our collections, sizing, or an existing order? Our dedicated support team is available 7 days a week to assist you.
                    </p>
                </div>

                <div class="contact-card">
                    <i class="fas fa-map-marker-alt"></i>
                    <div>
                        <h4>Flagship Store</h4>
                        <p>124 Fashion Avenue, High Street, Mumbai, MH 400001, India</p>
                    </div>
                </div>

                <div class="contact-card">
                    <i class="fas fa-phone-alt"></i>
                    <div>
                        <h4>Call Us</h4>
                        <p>+91 (022) 8800-LUXE (5893) / +91 98765 43210</p>
                    </div>
                </div>

                <div class="contact-card">
                    <i class="fas fa-envelope"></i>
                    <div>
                        <h4>Email Us</h4>
                        <p>concierge@luxe.com / support@luxe.com</p>
                    </div>
                </div>

                <div class="contact-card">
                    <i class="fas fa-clock"></i>
                    <div>
                        <h4>Business Hours</h4>
                        <p>Monday - Saturday: 10:00 AM - 9:00 PM<br>Sunday: 11:00 AM - 7:00 PM IST</p>
                    </div>
                </div>
            </div>

            <!-- Contact Form -->
            <div class="contact-form">
                <h3>Send Us a Message</h3>
                <form action="<?php echo htmlspecialchars($_SERVER['PHP_SELF']); ?>" method="POST">
                    <div class="form-group">
                        <label class="form-label" for="name">Your Name <span style="color: #ef4444;">*</span></label>
                        <input type="text" id="name" name="name" class="form-control" required 
                               value="<?php echo htmlspecialchars($_POST['name'] ?? ($user['name'] ?? '')); ?>" 
                               placeholder="Enter your full name">
                    </div>

                    <div class="form-group">
                        <label class="form-label" for="email">Email Address <span style="color: #ef4444;">*</span></label>
                        <input type="email" id="email" name="email" class="form-control" required 
                               value="<?php echo htmlspecialchars($_POST['email'] ?? ($user['email'] ?? '')); ?>" 
                               placeholder="Enter your email address">
                    </div>

                    <div class="form-group">
                        <label class="form-label" for="subject">Subject</label>
                        <input type="text" id="subject" name="subject" class="form-control" 
                               value="<?php echo htmlspecialchars($_POST['subject'] ?? ''); ?>" 
                               placeholder="What is this inquiry regarding?">
                    </div>

                    <div class="form-group">
                        <label class="form-label" for="message">Your Message <span style="color: #ef4444;">*</span></label>
                        <textarea id="message" name="message" class="form-control" rows="5" required 
                                  placeholder="Write your message here..."><?php echo htmlspecialchars($_POST['message'] ?? ''); ?></textarea>
                    </div>

                    <button type="submit" class="btn btn-primary" style="width: 100%;">
                        <i class="fas fa-paper-plane" style="margin-right: 8px;"></i> Send Message
                    </button>
                </form>
            </div>
        </div>
    </div>
</section>

<?php require_once '../includes/footer.php'; ?>
