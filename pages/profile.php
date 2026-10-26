<?php
$pageTitle = 'My Profile';
require_once '../includes/header.php';

if (!isLoggedIn()) {
    $_SESSION['redirect_after_login'] = SITE_URL . '/pages/profile.php';
    redirect(SITE_URL . '/pages/login.php');
}

$user = getCurrentUser();
$errors = [];
$success = false;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name = sanitize($_POST['name'] ?? '');
    $email = sanitize($_POST['email'] ?? '');
    $mobile = sanitize($_POST['mobile'] ?? '');
    $address = sanitize($_POST['address'] ?? '');
    $city = sanitize($_POST['city'] ?? '');
    $state = sanitize($_POST['state'] ?? '');
    $pincode = sanitize($_POST['pincode'] ?? '');
    $newPassword = $_POST['new_password'] ?? '';
    $confirmPassword = $_POST['confirm_password'] ?? '';

    if (empty($name)) $errors[] = 'Name is required.';
    if (empty($email)) $errors[] = 'Email is required.';

    // Check if email changed and is unique
    if ($email !== $user['email']) {
        $stmt = $conn->prepare("SELECT id FROM users WHERE email = ? AND id != ?");
        $stmt->bind_param("si", $email, $_SESSION['user_id']);
        $stmt->execute();
        if ($stmt->get_result()->num_rows > 0) {
            $errors[] = 'Email already in use.';
        }
    }

    // Password change
    if (!empty($newPassword)) {
        if (strlen($newPassword) < 6) $errors[] = 'New password must be at least 6 characters.';
        if ($newPassword !== $confirmPassword) $errors[] = 'Passwords do not match.';
    }

    if (empty($errors)) {
        if (!empty($newPassword)) {
            $hashedPassword = password_hash($newPassword, PASSWORD_DEFAULT);
            $stmt = $conn->prepare("UPDATE users SET name=?, email=?, mobile=?, address=?, city=?, state=?, pincode=?, password=? WHERE id=?");
            $stmt->bind_param("ssssssssi", $name, $email, $mobile, $address, $city, $state, $pincode, $hashedPassword, $_SESSION['user_id']);
        } else {
            $stmt = $conn->prepare("UPDATE users SET name=?, email=?, mobile=?, address=?, city=?, state=?, pincode=? WHERE id=?");
            $stmt->bind_param("sssssssi", $name, $email, $mobile, $address, $city, $state, $pincode, $_SESSION['user_id']);
        }

        if ($stmt->execute()) {
            $_SESSION['user_name'] = $name;
            $_SESSION['user_email'] = $email;
            setFlashMessage('success', 'Profile updated successfully!');
            redirect(SITE_URL . '/pages/profile.php');
        } else {
            $errors[] = 'Failed to update profile.';
        }
    }
}

// Refresh user data
$user = getCurrentUser();
?>

<script>window.siteUrl = '<?php echo SITE_URL; ?>';</script>

<section class="profile-section section-padding">
    <div class="container">
        <div class="breadcrumb" style="justify-content: flex-start; margin-bottom: 30px;">
            <a href="<?php echo SITE_URL; ?>">Home</a>
            <span>/</span>
            <span class="current">My Profile</span>
        </div>

        <div class="profile-layout">
            <!-- Sidebar -->
            <div class="profile-sidebar">
                <div class="profile-avatar">
                    <i class="fas fa-user"></i>
                </div>
                <h3><?php echo htmlspecialchars($user['name']); ?></h3>
                <p><?php echo htmlspecialchars($user['email']); ?></p>
                <nav class="profile-nav">
                    <a href="<?php echo SITE_URL; ?>/pages/profile.php" class="active"><i class="fas fa-user-circle"></i> My Profile</a>
                    <a href="<?php echo SITE_URL; ?>/pages/orders.php"><i class="fas fa-box"></i> My Orders</a>
                    <a href="<?php echo SITE_URL; ?>/pages/logout.php"><i class="fas fa-sign-out-alt"></i> Logout</a>
                </nav>
            </div>

            <!-- Content -->
            <div class="profile-content">
                <h2><i class="fas fa-user-edit" style="color: var(--accent); margin-right: 10px;"></i> Edit Profile</h2>

                <?php if (!empty($errors)): ?>
                    <?php foreach ($errors as $error): ?>
                        <div class="flash-message flash-error"><i class="fas fa-exclamation-circle"></i> <?php echo $error; ?></div>
                    <?php endforeach; ?>
                <?php endif; ?>

                <form method="POST" action="">
                    <div class="form-row">
                        <div class="form-group">
                            <label for="name">Full Name *</label>
                            <input type="text" id="name" name="name" class="form-control" value="<?php echo htmlspecialchars($user['name']); ?>" required>
                        </div>
                        <div class="form-group">
                            <label for="email">Email *</label>
                            <input type="email" id="email" name="email" class="form-control" value="<?php echo htmlspecialchars($user['email']); ?>" required>
                        </div>
                    </div>

                    <div class="form-group">
                        <label for="mobile">Mobile Number</label>
                        <input type="text" id="mobile" name="mobile" class="form-control" value="<?php echo htmlspecialchars($user['mobile'] ?? ''); ?>">
                    </div>

                    <div class="form-group">
                        <label for="address">Address</label>
                        <input type="text" id="address" name="address" class="form-control" value="<?php echo htmlspecialchars($user['address'] ?? ''); ?>">
                    </div>

                    <div class="form-row">
                        <div class="form-group">
                            <label for="city">City</label>
                            <input type="text" id="city" name="city" class="form-control" value="<?php echo htmlspecialchars($user['city'] ?? ''); ?>">
                        </div>
                        <div class="form-group">
                            <label for="state">State</label>
                            <input type="text" id="state" name="state" class="form-control" value="<?php echo htmlspecialchars($user['state'] ?? ''); ?>">
                        </div>
                    </div>

                    <div class="form-group">
                        <label for="pincode">Pincode</label>
                        <input type="text" id="pincode" name="pincode" class="form-control" value="<?php echo htmlspecialchars($user['pincode'] ?? ''); ?>" style="max-width: 200px;">
                    </div>

                    <hr style="border-color: var(--border); margin: 25px 0;">
                    <h4 style="margin-bottom: 15px; font-family: var(--font-body); color: var(--text-secondary);"><i class="fas fa-lock" style="color: var(--accent);"></i> Change Password <small style="color: var(--text-muted);">(leave blank to keep current)</small></h4>

                    <div class="form-row">
                        <div class="form-group">
                            <label for="new_password">New Password</label>
                            <input type="password" id="new_password" name="new_password" class="form-control" placeholder="Min 6 characters">
                        </div>
                        <div class="form-group">
                            <label for="confirm_password">Confirm Password</label>
                            <input type="password" id="confirm_password" name="confirm_password" class="form-control" placeholder="Repeat password">
                        </div>
                    </div>

                    <button type="submit" class="btn btn-primary btn-lg mt-2">
                        <i class="fas fa-save"></i> Save Changes
                    </button>
                </form>
            </div>
        </div>
    </div>
</section>

<?php require_once '../includes/footer.php'; ?>
