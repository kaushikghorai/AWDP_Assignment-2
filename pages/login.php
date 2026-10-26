<?php
$pageTitle = 'Login';
require_once '../includes/header.php';

// Redirect if already logged in
if (isLoggedIn()) {
    redirect(SITE_URL . '/index.php');
}

$errors = [];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = sanitize($_POST['email'] ?? '');
    $password = $_POST['password'] ?? '';

    if (empty($email)) $errors[] = 'Email is required.';
    if (empty($password)) $errors[] = 'Password is required.';

    if (empty($errors)) {
        $stmt = $conn->prepare("SELECT * FROM users WHERE email = ? AND status = 'active'");
        $stmt->bind_param("s", $email);
        $stmt->execute();
        $user = $stmt->get_result()->fetch_assoc();

        if ($user && password_verify($password, $user['password'])) {
            $_SESSION['user_id'] = $user['id'];
            $_SESSION['user_name'] = $user['name'];
            $_SESSION['user_email'] = $user['email'];
            $_SESSION['user_role'] = $user['role'];

            setFlashMessage('success', 'Welcome back, ' . $user['name'] . '!');
            
            // Redirect to intended page or home
            $redirect = isset($_SESSION['redirect_after_login']) ? $_SESSION['redirect_after_login'] : SITE_URL . '/index.php';
            unset($_SESSION['redirect_after_login']);
            redirect($redirect);
        } else {
            $errors[] = 'Invalid email or password.';
        }
    }
}
?>

<section class="auth-section">
    <div class="auth-container">
        <div style="text-align: center; margin-bottom: 10px;">
            <a href="<?php echo SITE_URL; ?>" style="font-family: var(--font-heading); font-size: 2rem; font-weight: 700; background: var(--gold-gradient); -webkit-background-clip: text; -webkit-text-fill-color: transparent; background-clip: text; letter-spacing: 3px;">LUXE</a>
        </div>
        <h2>Welcome Back</h2>
        <p class="auth-subtitle">Sign in to your account to continue</p>

        <?php if (!empty($errors)): ?>
            <?php foreach ($errors as $error): ?>
                <div class="flash-message flash-error"><i class="fas fa-exclamation-circle"></i> <?php echo $error; ?></div>
            <?php endforeach; ?>
        <?php endif; ?>

        <form method="POST" action="">
            <div class="form-group">
                <label for="email"><i class="fas fa-envelope" style="color: var(--accent);"></i> Email Address</label>
                <input type="email" id="email" name="email" class="form-control" placeholder="Enter your email" value="<?php echo htmlspecialchars($email ?? ''); ?>" required>
            </div>

            <div class="form-group">
                <label for="password"><i class="fas fa-lock" style="color: var(--accent);"></i> Password</label>
                <input type="password" id="password" name="password" class="form-control" placeholder="Enter your password" required>
            </div>

            <button type="submit" class="btn btn-primary btn-block btn-lg">
                <i class="fas fa-sign-in-alt"></i> Sign In
            </button>
        </form>

        <div class="auth-links">
            Don't have an account? <a href="<?php echo SITE_URL; ?>/pages/register.php">Create one</a>
        </div>
    </div>
</section>

<?php require_once '../includes/footer.php'; ?>
