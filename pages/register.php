<?php
$pageTitle = 'Register';
require_once '../includes/header.php';

if (isLoggedIn()) {
    redirect(SITE_URL . '/index.php');
}

$errors = [];
$formData = [];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $formData['name'] = sanitize($_POST['name'] ?? '');
    $formData['email'] = sanitize($_POST['email'] ?? '');
    $formData['mobile'] = sanitize($_POST['mobile'] ?? '');
    $formData['address'] = sanitize($_POST['address'] ?? '');
    $formData['city'] = sanitize($_POST['city'] ?? '');
    $formData['state'] = sanitize($_POST['state'] ?? '');
    $formData['pincode'] = sanitize($_POST['pincode'] ?? '');
    $password = $_POST['password'] ?? '';
    $confirmPassword = $_POST['confirm_password'] ?? '';

    // Validation
    if (empty($formData['name'])) $errors[] = 'Name is required.';
    if (empty($formData['email'])) $errors[] = 'Email is required.';
    elseif (!filter_var($formData['email'], FILTER_VALIDATE_EMAIL)) $errors[] = 'Invalid email format.';
    if (empty($formData['mobile'])) $errors[] = 'Mobile number is required.';
    elseif (!preg_match('/^[0-9]{10}$/', $formData['mobile'])) $errors[] = 'Mobile must be 10 digits.';
    if (empty($password)) $errors[] = 'Password is required.';
    elseif (strlen($password) < 6) $errors[] = 'Password must be at least 6 characters.';
    if ($password !== $confirmPassword) $errors[] = 'Passwords do not match.';

    // Check duplicate email
    if (empty($errors)) {
        $stmt = $conn->prepare("SELECT id FROM users WHERE email = ?");
        $stmt->bind_param("s", $formData['email']);
        $stmt->execute();
        if ($stmt->get_result()->num_rows > 0) {
            $errors[] = 'Email already registered.';
        }
    }

    if (empty($errors)) {
        $hashedPassword = password_hash($password, PASSWORD_DEFAULT);
        $stmt = $conn->prepare("INSERT INTO users (name, email, mobile, password, address, city, state, pincode) VALUES (?, ?, ?, ?, ?, ?, ?, ?)");
        $stmt->bind_param("ssssssss", $formData['name'], $formData['email'], $formData['mobile'], $hashedPassword, $formData['address'], $formData['city'], $formData['state'], $formData['pincode']);
        
        if ($stmt->execute()) {
            // Auto-login
            $_SESSION['user_id'] = $stmt->insert_id;
            $_SESSION['user_name'] = $formData['name'];
            $_SESSION['user_email'] = $formData['email'];
            $_SESSION['user_role'] = 'customer';

            setFlashMessage('success', 'Account created successfully! Welcome to LUXE.');
            redirect(SITE_URL . '/index.php');
        } else {
            $errors[] = 'Registration failed. Please try again.';
        }
    }
}
?>

<section class="auth-section" style="padding-bottom: 60px;">
    <div class="auth-container" style="max-width: 560px;">
        <div style="text-align: center; margin-bottom: 10px;">
            <a href="<?php echo SITE_URL; ?>" style="font-family: var(--font-heading); font-size: 2rem; font-weight: 700; background: var(--gold-gradient); -webkit-background-clip: text; -webkit-text-fill-color: transparent; background-clip: text; letter-spacing: 3px;">LUXE</a>
        </div>
        <h2>Create Account</h2>
        <p class="auth-subtitle">Join LUXE for an exclusive shopping experience</p>

        <?php if (!empty($errors)): ?>
            <?php foreach ($errors as $error): ?>
                <div class="flash-message flash-error"><i class="fas fa-exclamation-circle"></i> <?php echo $error; ?></div>
            <?php endforeach; ?>
        <?php endif; ?>

        <form method="POST" action="">
            <div class="form-group">
                <label for="name"><i class="fas fa-user" style="color: var(--accent);"></i> Full Name *</label>
                <input type="text" id="name" name="name" class="form-control" placeholder="Enter your full name" value="<?php echo htmlspecialchars($formData['name'] ?? ''); ?>" required>
            </div>

            <div class="form-row">
                <div class="form-group">
                    <label for="email"><i class="fas fa-envelope" style="color: var(--accent);"></i> Email *</label>
                    <input type="email" id="email" name="email" class="form-control" placeholder="your@email.com" value="<?php echo htmlspecialchars($formData['email'] ?? ''); ?>" required>
                </div>
                <div class="form-group">
                    <label for="mobile"><i class="fas fa-phone" style="color: var(--accent);"></i> Mobile *</label>
                    <input type="text" id="mobile" name="mobile" class="form-control" placeholder="10-digit number" value="<?php echo htmlspecialchars($formData['mobile'] ?? ''); ?>" required>
                </div>
            </div>

            <div class="form-row">
                <div class="form-group">
                    <label for="password"><i class="fas fa-lock" style="color: var(--accent);"></i> Password *</label>
                    <input type="password" id="password" name="password" class="form-control" placeholder="Min 6 characters" required>
                </div>
                <div class="form-group">
                    <label for="confirm_password"><i class="fas fa-lock" style="color: var(--accent);"></i> Confirm Password *</label>
                    <input type="password" id="confirm_password" name="confirm_password" class="form-control" placeholder="Repeat password" required>
                </div>
            </div>

            <div class="form-group">
                <label for="address"><i class="fas fa-map-marker-alt" style="color: var(--accent);"></i> Address</label>
                <input type="text" id="address" name="address" class="form-control" placeholder="Street address" value="<?php echo htmlspecialchars($formData['address'] ?? ''); ?>">
            </div>

            <div class="form-row">
                <div class="form-group">
                    <label for="city">City</label>
                    <input type="text" id="city" name="city" class="form-control" placeholder="City" value="<?php echo htmlspecialchars($formData['city'] ?? ''); ?>">
                </div>
                <div class="form-group">
                    <label for="state">State</label>
                    <input type="text" id="state" name="state" class="form-control" placeholder="State" value="<?php echo htmlspecialchars($formData['state'] ?? ''); ?>">
                </div>
            </div>

            <div class="form-group">
                <label for="pincode">Pincode</label>
                <input type="text" id="pincode" name="pincode" class="form-control" placeholder="6-digit pincode" value="<?php echo htmlspecialchars($formData['pincode'] ?? ''); ?>">
            </div>

            <button type="submit" class="btn btn-primary btn-block btn-lg mt-2">
                <i class="fas fa-user-plus"></i> Create Account
            </button>
        </form>

        <div class="auth-links">
            Already have an account? <a href="<?php echo SITE_URL; ?>/pages/login.php">Sign In</a>
        </div>
    </div>
</section>

<?php require_once '../includes/footer.php'; ?>
