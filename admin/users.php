<?php
$adminTitle = 'Users Management';
require_once __DIR__ . '/includes/header.php';

$editUser = null;
$error = '';

// Handle Delete User
if (isset($_GET['delete'])) {
    $deleteId = intval($_GET['delete']);
    // Prevent admin deleting themselves
    if ($deleteId === intval($_SESSION['user_id'])) {
        setFlashMessage('error', 'You cannot delete your own active administrator account.');
    } else {
        $stmt = $conn->prepare("DELETE FROM users WHERE id = ?");
        $stmt->bind_param("i", $deleteId);
        if ($stmt->execute()) {
            setFlashMessage('success', 'User account deleted successfully.');
        } else {
            setFlashMessage('error', 'Failed to delete user: ' . $conn->error);
        }
    }
    redirect(ADMIN_URL . '/users.php');
}

// Handle Status Toggle
if (isset($_GET['toggle_status'])) {
    $toggleId = intval($_GET['toggle_status']);
    if ($toggleId === intval($_SESSION['user_id'])) {
        setFlashMessage('error', 'You cannot deactivate your own active administrator account.');
    } else {
        $newStatus = $_GET['current'] === 'active' ? 'inactive' : 'active';
        $stmt = $conn->prepare("UPDATE users SET status = ? WHERE id = ?");
        $stmt->bind_param("si", $newStatus, $toggleId);
        $stmt->execute();
        setFlashMessage('success', "User status updated to $newStatus.");
    }
    redirect(ADMIN_URL . '/users.php');
}

// Handle Add / Edit User POST
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $userId = isset($_POST['user_id']) ? intval($_POST['user_id']) : 0;
    $name = sanitize($_POST['name'] ?? '');
    $email = sanitize($_POST['email'] ?? '');
    $mobile = sanitize($_POST['mobile'] ?? '');
    $role = in_array($_POST['role'] ?? '', ['customer', 'admin']) ? $_POST['role'] : 'customer';
    $status = in_array($_POST['status'] ?? '', ['active', 'inactive']) ? $_POST['status'] : 'active';
    $address = sanitize($_POST['address'] ?? '');
    $city = sanitize($_POST['city'] ?? '');
    $state = sanitize($_POST['state'] ?? '');
    $pincode = sanitize($_POST['pincode'] ?? '');
    $password = $_POST['password'] ?? '';

    if (empty($name) || empty($email)) {
        $error = 'Name and Email are required.';
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $error = 'Please enter a valid email address.';
    } else {
        // Check email uniqueness
        $emailCheck = $conn->prepare("SELECT id FROM users WHERE email = ? AND id != ?");
        $emailCheck->bind_param("si", $email, $userId);
        $emailCheck->execute();
        if ($emailCheck->get_result()->num_rows > 0) {
            $error = 'Email is already registered by another account.';
        } else {
            if ($userId > 0) {
                // Update
                if (!empty($password)) {
                    $hashed = password_hash($password, PASSWORD_DEFAULT);
                    $stmt = $conn->prepare("
                        UPDATE users 
                        SET name = ?, email = ?, mobile = ?, role = ?, status = ?, address = ?, city = ?, state = ?, pincode = ?, password = ? 
                        WHERE id = ?
                    ");
                    $stmt->bind_param("ssssssssssi", $name, $email, $mobile, $role, $status, $address, $city, $state, $pincode, $hashed, $userId);
                } else {
                    $stmt = $conn->prepare("
                        UPDATE users 
                        SET name = ?, email = ?, mobile = ?, role = ?, status = ?, address = ?, city = ?, state = ?, pincode = ? 
                        WHERE id = ?
                    ");
                    $stmt->bind_param("sssssssssi", $name, $email, $mobile, $role, $status, $address, $city, $state, $pincode, $userId);
                }

                if ($stmt->execute()) {
                    setFlashMessage('success', 'User details updated successfully.');
                    redirect(ADMIN_URL . '/users.php');
                } else {
                    $error = 'Failed to update user: ' . $conn->error;
                }
            } else {
                // Add new user
                if (empty($password)) {
                    $error = 'Password is required when creating a new user account.';
                } else {
                    $hashed = password_hash($password, PASSWORD_DEFAULT);
                    $stmt = $conn->prepare("
                        INSERT INTO users (name, email, mobile, password, role, status, address, city, state, pincode) 
                        VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?)
                    ");
                    $stmt->bind_param("ssssssssss", $name, $email, $mobile, $hashed, $role, $status, $address, $city, $state, $pincode);
                    if ($stmt->execute()) {
                        setFlashMessage('success', 'User registered successfully.');
                        redirect(ADMIN_URL . '/users.php');
                    } else {
                        $error = 'Failed to register user: ' . $conn->error;
                    }
                }
            }
        }
    }
}

// Check if Edit requested
if (isset($_GET['edit'])) {
    $editId = intval($_GET['edit']);
    $stmt = $conn->prepare("SELECT * FROM users WHERE id = ?");
    $stmt->bind_param("i", $editId);
    $stmt->execute();
    $editUser = $stmt->get_result()->fetch_assoc();
}

// Search & Filter
$search = sanitize($_GET['search'] ?? '');
$roleFilter = sanitize($_GET['role'] ?? '');

$sql = "
    SELECT u.*, COUNT(o.id) as orders_count 
    FROM users u 
    LEFT JOIN orders o ON u.id = o.user_id 
    WHERE 1=1 
";

if (!empty($roleFilter)) {
    $sql .= " AND u.role = '$roleFilter'";
}
if (!empty($search)) {
    $sql .= " AND (u.name LIKE '%$search%' OR u.email LIKE '%$search%' OR u.mobile LIKE '%$search%' OR u.city LIKE '%$search%')";
}
$sql .= " GROUP BY u.id ORDER BY u.id DESC";

$usersResult = $conn->query($sql);
?>

<div class="page-title-box">
    <div>
        <h1>Users & Customer Accounts</h1>
        <p>Manage customer profiles, grant administrator roles, and monitor user orders.</p>
    </div>
    <div>
        <button class="btn btn-primary" onclick="openAddUserModal()">
            <i class="fas fa-user-plus"></i> Add New User
        </button>
    </div>
</div>

<?php if ($error): ?>
    <div style="padding: 12px 18px; border-radius: var(--radius-sm); margin-bottom: 20px; background: var(--danger-bg); border: 1px solid var(--danger); color: #f87171;">
        <i class="fas fa-exclamation-circle"></i> <?php echo $error; ?>
    </div>
<?php endif; ?>

<!-- Search Bar -->
<div class="admin-card" style="padding: 18px 24px;">
    <form action="<?php echo ADMIN_URL; ?>/users.php" method="GET" style="display: flex; gap: 15px; flex-wrap: wrap; align-items: center; justify-content: space-between;">
        <div style="display: flex; gap: 12px; flex-wrap: wrap; align-items: center; flex: 1;">
            <div class="header-search" style="width: 300px;">
                <i class="fas fa-search" style="color: var(--text-dim);"></i>
                <input type="text" name="search" value="<?php echo htmlspecialchars($search); ?>" placeholder="Search name, email, or city...">
            </div>

            <select name="role" class="form-control" style="width: 160px;" onchange="this.form.submit()">
                <option value="">All Roles</option>
                <option value="customer" <?php echo $roleFilter === 'customer' ? 'selected' : ''; ?>>Customers Only</option>
                <option value="admin" <?php echo $roleFilter === 'admin' ? 'selected' : ''; ?>>Administrators</option>
            </select>
        </div>

        <div>
            <button type="submit" class="btn btn-outline btn-sm">Filter</button>
            <?php if ($roleFilter || $search): ?>
                <a href="<?php echo ADMIN_URL; ?>/users.php" class="btn btn-outline btn-sm" style="color: var(--danger);">Reset</a>
            <?php endif; ?>
        </div>
    </form>
</div>

<!-- Users Table -->
<div class="admin-card">
    <div class="table-responsive">
        <table class="admin-table">
            <thead>
                <tr>
                    <th>User ID</th>
                    <th>User Profile</th>
                    <th>Mobile</th>
                    <th>City / State</th>
                    <th>Role</th>
                    <th>Orders</th>
                    <th>Status</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php if ($usersResult && $usersResult->num_rows > 0): ?>
                    <?php while ($user = $usersResult->fetch_assoc()): ?>
                        <tr>
                            <td>#<?php echo $user['id']; ?></td>
                            <td>
                                <div style="display: flex; align-items: center; gap: 12px;">
                                    <div class="admin-avatar" style="width: 36px; height: 36px; font-size: 0.85rem;">
                                        <?php echo strtoupper(substr($user['name'], 0, 1)); ?>
                                    </div>
                                    <div>
                                        <div style="font-weight: 600;"><?php echo htmlspecialchars($user['name']); ?></div>
                                        <div style="font-size: 0.78rem; color: var(--text-muted);"><?php echo htmlspecialchars($user['email']); ?></div>
                                    </div>
                                </div>
                            </td>
                            <td><?php echo htmlspecialchars($user['mobile'] ? $user['mobile'] : '—'); ?></td>
                            <td><?php echo htmlspecialchars($user['city'] ? ($user['city'] . ', ' . $user['state']) : '—'); ?></td>
                            <td>
                                <span class="badge" style="background: <?php echo $user['role'] === 'admin' ? 'var(--primary-light)' : 'rgba(255,255,255,0.06)'; ?>; color: <?php echo $user['role'] === 'admin' ? 'var(--primary)' : 'var(--text)'; ?>;">
                                    <i class="fas fa-<?php echo $user['role'] === 'admin' ? 'shield-alt' : 'user'; ?>" style="font-size: 0.7rem; margin-right: 4px;"></i>
                                    <?php echo ucfirst($user['role']); ?>
                                </span>
                            </td>
                            <td>
                                <span class="badge" style="background: rgba(255,255,255,0.06); color: var(--text);">
                                    <?php echo $user['orders_count']; ?> Orders
                                </span>
                            </td>
                            <td>
                                <a href="<?php echo ADMIN_URL; ?>/users.php?toggle_status=<?php echo $user['id']; ?>&current=<?php echo $user['status']; ?>" 
                                   class="badge badge-<?php echo $user['status']; ?>" style="text-decoration: none;" title="Toggle status">
                                    <?php echo ucfirst($user['status']); ?>
                                </a>
                            </td>
                            <td>
                                <div style="display: flex; gap: 8px;">
                                    <button class="btn btn-sm btn-outline" onclick="editUser(<?php echo htmlspecialchars(json_encode($user)); ?>)" title="Edit User">
                                        <i class="fas fa-edit"></i>
                                    </button>
                                    <?php if ($user['id'] != $_SESSION['user_id']): ?>
                                        <button class="btn btn-sm btn-danger" onclick="confirmDelete('Are you sure you want to delete user \'<?php echo addslashes($user['name']); ?>\'?', '<?php echo ADMIN_URL; ?>/users.php?delete=<?php echo $user['id']; ?>')" title="Delete">
                                            <i class="fas fa-trash"></i>
                                        </button>
                                    <?php endif; ?>
                                </div>
                            </td>
                        </tr>
                    <?php endwhile; ?>
                <?php else: ?>
                    <tr>
                        <td colspan="8" style="text-align: center; padding: 35px; color: var(--text-muted);">
                            No registered users found.
                        </td>
                    </tr>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>

<!-- Add / Edit User Modal -->
<div class="modal-overlay <?php echo $editUser ? 'active' : ''; ?>" id="userModal">
    <div class="modal-content">
        <form action="<?php echo ADMIN_URL; ?>/users.php" method="POST">
            <div class="modal-header">
                <h3 id="userModalTitle"><?php echo $editUser ? 'Edit User: ' . htmlspecialchars($editUser['name']) : 'Add New User'; ?></h3>
                <button type="button" class="modal-close" onclick="closeModal('userModal')">&times;</button>
            </div>

            <div class="modal-body">
                <input type="hidden" name="user_id" id="modal_user_id" value="<?php echo $editUser['id'] ?? 0; ?>">

                <div class="form-grid">
                    <div class="form-group">
                        <label class="form-label" for="usr_name">Full Name <span style="color: var(--danger);">*</span></label>
                        <input type="text" id="usr_name" name="name" class="form-control" required
                               value="<?php echo htmlspecialchars($editUser['name'] ?? ''); ?>" placeholder="John Doe">
                    </div>

                    <div class="form-group">
                        <label class="form-label" for="usr_email">Email Address <span style="color: var(--danger);">*</span></label>
                        <input type="email" id="usr_email" name="email" class="form-control" required
                               value="<?php echo htmlspecialchars($editUser['email'] ?? ''); ?>" placeholder="john@example.com">
                    </div>
                </div>

                <div class="form-grid">
                    <div class="form-group">
                        <label class="form-label" for="usr_mobile">Mobile Number</label>
                        <input type="text" id="usr_mobile" name="mobile" class="form-control"
                               value="<?php echo htmlspecialchars($editUser['mobile'] ?? ''); ?>" placeholder="9876543210">
                    </div>

                    <div class="form-group">
                        <label class="form-label" for="usr_password">Password</label>
                        <input type="password" id="usr_password" name="password" class="form-control"
                               placeholder="<?php echo $editUser ? 'Leave blank to retain password' : 'Enter account password'; ?>">
                    </div>
                </div>

                <div class="form-grid">
                    <div class="form-group">
                        <label class="form-label" for="usr_role">Account Role</label>
                        <select name="role" id="usr_role" class="form-control">
                            <option value="customer" <?php echo ($editUser['role'] ?? 'customer') === 'customer' ? 'selected' : ''; ?>>Customer</option>
                            <option value="admin" <?php echo ($editUser['role'] ?? '') === 'admin' ? 'selected' : ''; ?>>Administrator</option>
                        </select>
                    </div>

                    <div class="form-group">
                        <label class="form-label" for="usr_status">Account Status</label>
                        <select name="status" id="usr_status" class="form-control">
                            <option value="active" <?php echo ($editUser['status'] ?? 'active') === 'active' ? 'selected' : ''; ?>>Active</option>
                            <option value="inactive" <?php echo ($editUser['status'] ?? '') === 'inactive' ? 'selected' : ''; ?>>Inactive / Suspended</option>
                        </select>
                    </div>
                </div>

                <div class="form-group">
                    <label class="form-label" for="usr_address">Street Address</label>
                    <textarea id="usr_address" name="address" class="form-control" rows="2" placeholder="Street address or apartment"><?php echo htmlspecialchars($editUser['address'] ?? ''); ?></textarea>
                </div>

                <div class="form-grid" style="grid-template-columns: repeat(auto-fit, minmax(140px, 1fr));">
                    <div class="form-group">
                        <label class="form-label" for="usr_city">City</label>
                        <input type="text" id="usr_city" name="city" class="form-control"
                               value="<?php echo htmlspecialchars($editUser['city'] ?? ''); ?>">
                    </div>

                    <div class="form-group">
                        <label class="form-label" for="usr_state">State</label>
                        <input type="text" id="usr_state" name="state" class="form-control"
                               value="<?php echo htmlspecialchars($editUser['state'] ?? ''); ?>">
                    </div>

                    <div class="form-group">
                        <label class="form-label" for="usr_pincode">Pincode</label>
                        <input type="text" id="usr_pincode" name="pincode" class="form-control"
                               value="<?php echo htmlspecialchars($editUser['pincode'] ?? ''); ?>">
                    </div>
                </div>
            </div>

            <div class="modal-footer">
                <button type="button" class="btn btn-outline" onclick="closeModal('userModal')">Cancel</button>
                <button type="submit" class="btn btn-primary">
                    <i class="fas fa-save"></i> Save User Details
                </button>
            </div>
        </form>
    </div>
</div>

<script>
function openAddUserModal() {
    document.getElementById('userModalTitle').textContent = 'Add New User';
    document.getElementById('modal_user_id').value = 0;
    document.getElementById('usr_name').value = '';
    document.getElementById('usr_email').value = '';
    document.getElementById('usr_mobile').value = '';
    document.getElementById('usr_password').value = '';
    document.getElementById('usr_role').value = 'customer';
    document.getElementById('usr_status').value = 'active';
    document.getElementById('usr_address').value = '';
    document.getElementById('usr_city').value = '';
    document.getElementById('usr_state').value = '';
    document.getElementById('usr_pincode').value = '';
    openModal('userModal');
}

function editUser(user) {
    document.getElementById('userModalTitle').textContent = 'Edit User: ' + user.name;
    document.getElementById('modal_user_id').value = user.id;
    document.getElementById('usr_name').value = user.name;
    document.getElementById('usr_email').value = user.email;
    document.getElementById('usr_mobile').value = user.mobile || '';
    document.getElementById('usr_password').value = '';
    document.getElementById('usr_role').value = user.role;
    document.getElementById('usr_status').value = user.status;
    document.getElementById('usr_address').value = user.address || '';
    document.getElementById('usr_city').value = user.city || '';
    document.getElementById('usr_state').value = user.state || '';
    document.getElementById('usr_pincode').value = user.pincode || '';
    openModal('userModal');
}
</script>

<?php require_once __DIR__ . '/includes/footer.php'; ?>
