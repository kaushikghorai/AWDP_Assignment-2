<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../config/helpers.php';

// If already admin, redirect to dashboard
if (isAdmin()) {
    redirect(ADMIN_URL . '/index.php');
}

$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = sanitize($_POST['email'] ?? '');
    $password = $_POST['password'] ?? '';

    if (empty($email) || empty($password)) {
        $error = 'Please enter both email and password.';
    } else {
        $stmt = $conn->prepare("SELECT id, name, email, password, role, status FROM users WHERE email = ? AND role = 'admin'");
        $stmt->bind_param("s", $email);
        $stmt->execute();
        $result = $stmt->get_result();

        if ($result->num_rows === 1) {
            $user = $result->fetch_assoc();

            if ($user['status'] !== 'active') {
                $error = 'Your admin account has been deactivated. Please contact support.';
            } elseif (password_verify($password, $user['password'])) {
                // Success! Set admin session
                $_SESSION['user_id'] = $user['id'];
                $_SESSION['user_name'] = $user['name'];
                $_SESSION['user_email'] = $user['email'];
                $_SESSION['user_role'] = $user['role'];

                $redirect = $_SESSION['admin_redirect'] ?? (ADMIN_URL . '/index.php');
                unset($_SESSION['admin_redirect']);
                redirect($redirect);
            } else {
                $error = 'Invalid email or password.';
            }
        } else {
            $error = 'Invalid credentials or you do not have administrative privileges.';
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>LUXE Admin - Secure Portal Login</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="<?php echo ADMIN_URL; ?>/assets/css/admin.css">
    <style>
        body {
            display: flex;
            align-items: center;
            justify-content: center;
            background: radial-gradient(circle at center, #171821 0%, #0a0b0e 100%);
            padding: 20px;
        }
        .admin-login-card {
            background: #14151c;
            border: 1px solid var(--border);
            border-radius: var(--radius-lg);
            width: 100%;
            max-width: 440px;
            padding: 40px;
            box-shadow: 0 15px 50px rgba(0, 0, 0, 0.7);
        }
        .login-brand {
            text-align: center;
            margin-bottom: 30px;
        }
        .login-brand .logo {
            font-family: 'Playfair Display', serif, Georgia;
            font-size: 2.2rem;
            font-weight: 700;
            color: var(--primary);
            letter-spacing: 4px;
        }
        .login-brand p {
            color: var(--text-muted);
            font-size: 0.85rem;
            margin-top: 6px;
            letter-spacing: 1px;
            text-transform: uppercase;
        }
        .demo-credentials {
            margin-top: 25px;
            padding: 14px;
            border-radius: var(--radius-sm);
            background: rgba(255, 255, 255, 0.02);
            border: 1px dashed var(--border);
            font-size: 0.8rem;
            color: var(--text-muted);
        }
        .demo-credentials strong {
            color: var(--primary);
        }
    </style>
</head>
<body>

<div class="admin-login-card">
    <div class="login-brand">
        <div class="logo">LUXE</div>
        <p>Administrative Control Center</p>
    </div>

    <?php if ($error): ?>
        <div style="padding: 12px 16px; border-radius: var(--radius-sm); margin-bottom: 20px; background: var(--danger-bg); border: 1px solid var(--danger); color: #f87171; font-size: 0.88rem; display: flex; align-items: center; gap: 10px;">
            <i class="fas fa-exclamation-circle"></i>
            <span><?php echo $error; ?></span>
        </div>
    <?php endif; ?>

    <form action="<?php echo htmlspecialchars($_SERVER['PHP_SELF']); ?>" method="POST">
        <div class="form-group">
            <label class="form-label" for="email">Admin Email</label>
            <div style="position: relative;">
                <input type="email" id="email" name="email" class="form-control" required
                       value="<?php echo htmlspecialchars($_POST['email'] ?? 'admin@luxe.com'); ?>"
                       placeholder="admin@luxe.com">
            </div>
        </div>

        <div class="form-group">
            <label class="form-label" for="password">Password</label>
            <input type="password" id="password" name="password" class="form-control" required
                   value="admin123" placeholder="••••••••">
        </div>

        <button type="submit" class="btn btn-primary" style="width: 100%; padding: 12px; margin-top: 10px;">
            <i class="fas fa-lock" style="margin-right: 6px;"></i> Secure Login
        </button>
    </form>

    <div class="demo-credentials">
        <div style="margin-bottom: 5px;"><strong>Default Admin Credentials:</strong></div>
        <div>Email: <code>admin@luxe.com</code></div>
        <div>Password: <code>admin123</code></div>
    </div>

    <div style="text-align: center; margin-top: 25px;">
        <a href="<?php echo SITE_URL; ?>" style="color: var(--text-muted); font-size: 0.85rem; text-decoration: none;">
            <i class="fas fa-arrow-left"></i> Return to Storefront
        </a>
    </div>
</div>

</body>
</html>
