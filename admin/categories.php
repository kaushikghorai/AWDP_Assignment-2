<?php
$adminTitle = 'Categories Management';
require_once __DIR__ . '/includes/header.php';

$editCategory = null;
$error = '';

// Handle Delete Category
if (isset($_GET['delete'])) {
    $deleteId = intval($_GET['delete']);
    $stmt = $conn->prepare("DELETE FROM categories WHERE id = ?");
    $stmt->bind_param("i", $deleteId);
    if ($stmt->execute()) {
        setFlashMessage('success', 'Category deleted successfully.');
    } else {
        setFlashMessage('error', 'Failed to delete category: ' . $conn->error);
    }
    redirect(ADMIN_URL . '/categories.php');
}

// Handle Add / Edit Category POST
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $categoryId = isset($_POST['category_id']) ? intval($_POST['category_id']) : 0;
    $name = sanitize($_POST['name'] ?? '');
    $description = sanitize($_POST['description'] ?? '');
    $status = in_array($_POST['status'] ?? '', ['active', 'inactive']) ? $_POST['status'] : 'active';
    $imageName = sanitize($_POST['existing_image'] ?? '');

    // Handle File Upload if provided
    if (isset($_FILES['image']) && $_FILES['image']['error'] === UPLOAD_ERR_OK) {
        $allowedExts = ['jpg', 'jpeg', 'png', 'webp', 'gif'];
        $fileInfo = pathinfo($_FILES['image']['name']);
        $ext = strtolower($fileInfo['extension'] ?? '');

        if (in_array($ext, $allowedExts)) {
            $newFileName = 'cat_' . time() . '_' . rand(100, 999) . '.' . $ext;
            $uploadTarget = UPLOADS_DIR . $newFileName;
            if (!is_dir(UPLOADS_DIR)) {
                mkdir(UPLOADS_DIR, 0777, true);
            }
            if (move_uploaded_file($_FILES['image']['tmp_name'], $uploadTarget)) {
                $imageName = $newFileName;
            }
        }
    } elseif (!empty($_POST['custom_image_name'])) {
        $imageName = sanitize($_POST['custom_image_name']);
    }

    if (empty($name)) {
        $error = 'Category name is required.';
    } else {
        if ($categoryId > 0) {
            // Update
            $stmt = $conn->prepare("UPDATE categories SET name = ?, image = ?, description = ?, status = ? WHERE id = ?");
            $stmt->bind_param("ssssi", $name, $imageName, $description, $status, $categoryId);
            if ($stmt->execute()) {
                setFlashMessage('success', 'Category updated successfully.');
                redirect(ADMIN_URL . '/categories.php');
            } else {
                $error = 'Failed to update category: ' . $conn->error;
            }
        } else {
            // Insert
            $stmt = $conn->prepare("INSERT INTO categories (name, image, description, status) VALUES (?, ?, ?, ?)");
            $stmt->bind_param("ssss", $name, $imageName, $description, $status);
            if ($stmt->execute()) {
                setFlashMessage('success', 'Category created successfully.');
                redirect(ADMIN_URL . '/categories.php');
            } else {
                $error = 'Failed to create category: ' . $conn->error;
            }
        }
    }
}

// Check if Edit requested
if (isset($_GET['edit'])) {
    $editId = intval($_GET['edit']);
    $stmt = $conn->prepare("SELECT * FROM categories WHERE id = ?");
    $stmt->bind_param("i", $editId);
    $stmt->execute();
    $editCategory = $stmt->get_result()->fetch_assoc();
}

// Fetch all categories with product count
$search = sanitize($_GET['search'] ?? '');
$sql = "
    SELECT c.*, COUNT(p.id) as product_count 
    FROM categories c 
    LEFT JOIN products p ON c.id = p.category_id 
";
if (!empty($search)) {
    $sql .= " WHERE c.name LIKE '%$search%' OR c.description LIKE '%$search%' ";
}
$sql .= " GROUP BY c.id ORDER BY c.id DESC";
$categoriesResult = $conn->query($sql);
?>

<div class="page-title-box">
    <div>
        <h1>Categories Management</h1>
        <p>Organize store offerings into categories and collections.</p>
    </div>
    <div>
        <button class="btn btn-primary" onclick="openModal('categoryModal')">
            <i class="fas fa-plus"></i> Add New Category
        </button>
    </div>
</div>

<?php if ($error): ?>
    <div style="padding: 12px 18px; border-radius: var(--radius-sm); margin-bottom: 20px; background: var(--danger-bg); border: 1px solid var(--danger); color: #f87171;">
        <i class="fas fa-exclamation-circle"></i> <?php echo $error; ?>
    </div>
<?php endif; ?>

<div class="admin-card">
    <div class="card-header-flex">
        <h3 class="card-title">
            <i class="fas fa-tags" style="color: var(--primary);"></i> Available Categories
        </h3>
        <form action="<?php echo ADMIN_URL; ?>/categories.php" method="GET" class="header-search">
            <i class="fas fa-search" style="color: var(--text-dim);"></i>
            <input type="text" name="search" value="<?php echo htmlspecialchars($search); ?>" placeholder="Search category...">
        </form>
    </div>

    <div class="table-responsive">
        <table class="admin-table">
            <thead>
                <tr>
                    <th>ID</th>
                    <th>Image</th>
                    <th>Category Name</th>
                    <th>Description</th>
                    <th>Products</th>
                    <th>Status</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php if ($categoriesResult && $categoriesResult->num_rows > 0): ?>
                    <?php while ($cat = $categoriesResult->fetch_assoc()): ?>
                        <?php 
                        $imageSrc = SITE_URL . '/assets/images/' . $cat['image'];
                        if (file_exists(UPLOADS_DIR . $cat['image'])) {
                            $imageSrc = UPLOADS_URL . $cat['image'];
                        }
                        ?>
                        <tr>
                            <td>#<?php echo $cat['id']; ?></td>
                            <td>
                                <div style="width: 48px; height: 48px; border-radius: var(--radius-sm); overflow: hidden; background: #222; border: 1px solid var(--border); display: flex; align-items: center; justify-content: center;">
                                    <?php if ($cat['image']): ?>
                                        <img src="<?php echo $imageSrc; ?>" alt="" style="width: 100%; height: 100%; object-fit: cover;" onerror="this.onerror=null; this.src='https://placehold.co/100x100/171821/c5a059?text=<?php echo urlencode($cat['name']); ?>';">
                                    <?php else: ?>
                                        <i class="fas fa-image" style="color: var(--text-dim);"></i>
                                    <?php endif; ?>
                                </div>
                            </td>
                            <td>
                                <strong><?php echo htmlspecialchars($cat['name']); ?></strong>
                            </td>
                            <td style="max-width: 280px; color: var(--text-muted);">
                                <?php echo htmlspecialchars(truncateText($cat['description'], 80)); ?>
                            </td>
                            <td>
                                <span class="badge" style="background: rgba(255,255,255,0.06); color: var(--text);">
                                    <?php echo $cat['product_count']; ?> Products
                                </span>
                            </td>
                            <td>
                                <span class="badge badge-<?php echo $cat['status']; ?>">
                                    <?php echo ucfirst($cat['status']); ?>
                                </span>
                            </td>
                            <td>
                                <div style="display: flex; gap: 8px;">
                                    <button class="btn btn-sm btn-outline" onclick="editCategory(<?php echo htmlspecialchars(json_encode($cat)); ?>)" title="Edit">
                                        <i class="fas fa-edit"></i>
                                    </button>
                                    <button class="btn btn-sm btn-danger" onclick="confirmDelete('Are you sure you want to delete category \'<?php echo addslashes($cat['name']); ?>\'? All related products might be affected.', '<?php echo ADMIN_URL; ?>/categories.php?delete=<?php echo $cat['id']; ?>')" title="Delete">
                                        <i class="fas fa-trash"></i>
                                    </button>
                                </div>
                            </td>
                        </tr>
                    <?php endwhile; ?>
                <?php else: ?>
                    <tr>
                        <td colspan="7" style="text-align: center; padding: 30px; color: var(--text-muted);">
                            No categories found matching criteria.
                        </td>
                    </tr>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>

<!-- Category Modal (Add / Edit) -->
<div class="modal-overlay <?php echo $editCategory ? 'active' : ''; ?>" id="categoryModal">
    <div class="modal-content">
        <form action="<?php echo ADMIN_URL; ?>/categories.php" method="POST" enctype="multipart/form-data">
            <div class="modal-header">
                <h3 id="modalTitle"><?php echo $editCategory ? 'Edit Category' : 'Add New Category'; ?></h3>
                <button type="button" class="modal-close" onclick="closeModal('categoryModal')">&times;</button>
            </div>

            <div class="modal-body">
                <input type="hidden" name="category_id" id="cat_id" value="<?php echo $editCategory['id'] ?? 0; ?>">
                <input type="hidden" name="existing_image" id="cat_existing_image" value="<?php echo $editCategory['image'] ?? ''; ?>">

                <div class="form-group">
                    <label class="form-label" for="cat_name">Category Name <span style="color: var(--danger);">*</span></label>
                    <input type="text" id="cat_name" name="name" class="form-control" required
                           value="<?php echo htmlspecialchars($editCategory['name'] ?? ''); ?>" placeholder="e.g. Women's Wear">
                </div>

                <div class="form-group">
                    <label class="form-label" for="cat_description">Description</label>
                    <textarea id="cat_description" name="description" class="form-control" rows="3" placeholder="Brief overview of this category"><?php echo htmlspecialchars($editCategory['description'] ?? ''); ?></textarea>
                </div>

                <div class="form-grid">
                    <div class="form-group">
                        <label class="form-label" for="cat_status">Status</label>
                        <select name="status" id="cat_status" class="form-control">
                            <option value="active" <?php echo ($editCategory['status'] ?? 'active') === 'active' ? 'selected' : ''; ?>>Active</option>
                            <option value="inactive" <?php echo ($editCategory['status'] ?? '') === 'inactive' ? 'selected' : ''; ?>>Inactive</option>
                        </select>
                    </div>

                    <div class="form-group">
                        <label class="form-label" for="cat_image_file">Upload Image</label>
                        <input type="file" id="cat_image_file" name="image" class="form-control" accept="image/*">
                    </div>
                </div>

                <div class="form-group">
                    <label class="form-label" for="cat_custom_image">Or Image Filename / Stock Asset</label>
                    <input type="text" id="cat_custom_image" name="custom_image_name" class="form-control"
                           value="<?php echo htmlspecialchars($editCategory['image'] ?? ''); ?>" placeholder="e.g. womens_wear.jpg">
                    <span style="font-size: 0.78rem; color: var(--text-dim); margin-top: 4px; display: block;">You can either upload a local file or enter a standard asset filename from <code>assets/images/</code>.</span>
                </div>
            </div>

            <div class="modal-footer">
                <button type="button" class="btn btn-outline" onclick="closeModal('categoryModal')">Cancel</button>
                <button type="submit" class="btn btn-primary">
                    <i class="fas fa-save"></i> Save Category
                </button>
            </div>
        </form>
    </div>
</div>

<script>
function editCategory(cat) {
    document.getElementById('modalTitle').textContent = 'Edit Category: ' + cat.name;
    document.getElementById('cat_id').value = cat.id;
    document.getElementById('cat_name').value = cat.name;
    document.getElementById('cat_description').value = cat.description || '';
    document.getElementById('cat_status').value = cat.status;
    document.getElementById('cat_existing_image').value = cat.image || '';
    document.getElementById('cat_custom_image').value = cat.image || '';
    openModal('categoryModal');
}
</script>

<?php require_once __DIR__ . '/includes/footer.php'; ?>
