<?php
$adminTitle = 'Products Management';
require_once __DIR__ . '/includes/header.php';

$action = $_GET['action'] ?? 'list';
$editProduct = null;
$error = '';

// Handle Delete Product
if (isset($_GET['delete'])) {
    $deleteId = intval($_GET['delete']);
    $stmt = $conn->prepare("DELETE FROM products WHERE id = ?");
    $stmt->bind_param("i", $deleteId);
    if ($stmt->execute()) {
        setFlashMessage('success', 'Product removed successfully.');
    } else {
        setFlashMessage('error', 'Failed to delete product: ' . $conn->error);
    }
    redirect(ADMIN_URL . '/products.php');
}

// Handle Quick Status Toggle
if (isset($_GET['toggle_status'])) {
    $toggleId = intval($_GET['toggle_status']);
    $currentStatus = $_GET['current'] === 'active' ? 'inactive' : 'active';
    $stmt = $conn->prepare("UPDATE products SET status = ? WHERE id = ?");
    $stmt->bind_param("si", $currentStatus, $toggleId);
    $stmt->execute();
    setFlashMessage('success', "Product status changed to $currentStatus.");
    redirect(ADMIN_URL . '/products.php');
}

// Handle Add / Edit POST
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $productId = isset($_POST['product_id']) ? intval($_POST['product_id']) : 0;
    $categoryId = intval($_POST['category_id'] ?? 0);
    $name = sanitize($_POST['name'] ?? '');
    $price = floatval($_POST['price'] ?? 0);
    $discountPrice = !empty($_POST['discount_price']) ? floatval($_POST['discount_price']) : null;
    $shortDescription = sanitize($_POST['short_description'] ?? '');
    $description = sanitize($_POST['description'] ?? '');
    $stockQuantity = intval($_POST['stock_quantity'] ?? 0);
    $status = in_array($_POST['status'] ?? '', ['active', 'inactive']) ? $_POST['status'] : 'active';
    $isFeatured = isset($_POST['is_featured']) ? 1 : 0;
    $isNew = isset($_POST['is_new']) ? 1 : 0;
    $imageName = sanitize($_POST['existing_image'] ?? '');

    // Handle File Upload if provided
    if (isset($_FILES['image']) && $_FILES['image']['error'] === UPLOAD_ERR_OK) {
        $allowedExts = ['jpg', 'jpeg', 'png', 'webp', 'gif'];
        $fileInfo = pathinfo($_FILES['image']['name']);
        $ext = strtolower($fileInfo['extension'] ?? '');

        if (in_array($ext, $allowedExts)) {
            $newFileName = 'prod_' . time() . '_' . rand(100, 999) . '.' . $ext;
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

    if (empty($name) || $categoryId <= 0 || $price <= 0) {
        $error = 'Product Name, valid Category, and base Price are required.';
    } else {
        if ($productId > 0) {
            // Update
            $stmt = $conn->prepare("
                UPDATE products 
                SET category_id = ?, name = ?, image = ?, price = ?, discount_price = ?, 
                    short_description = ?, description = ?, stock_quantity = ?, is_featured = ?, is_new = ?, status = ?
                WHERE id = ?
            ");
            $stmt->bind_param("issddssiiisi", $categoryId, $name, $imageName, $price, $discountPrice, $shortDescription, $description, $stockQuantity, $isFeatured, $isNew, $status, $productId);
            if ($stmt->execute()) {
                setFlashMessage('success', 'Product updated successfully.');
                redirect(ADMIN_URL . '/products.php');
            } else {
                $error = 'Failed to update product: ' . $conn->error;
            }
        } else {
            // Insert
            $stmt = $conn->prepare("
                INSERT INTO products 
                (category_id, name, image, price, discount_price, short_description, description, stock_quantity, is_featured, is_new, status) 
                VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)
            ");
            $stmt->bind_param("issddssiiis", $categoryId, $name, $imageName, $price, $discountPrice, $shortDescription, $description, $stockQuantity, $isFeatured, $isNew, $status);
            if ($stmt->execute()) {
                setFlashMessage('success', 'Product added successfully.');
                redirect(ADMIN_URL . '/products.php');
            } else {
                $error = 'Failed to add product: ' . $conn->error;
            }
        }
    }
}

// If editing
if (isset($_GET['edit'])) {
    $editId = intval($_GET['edit']);
    $stmt = $conn->prepare("SELECT * FROM products WHERE id = ?");
    $stmt->bind_param("i", $editId);
    $stmt->execute();
    $editProduct = $stmt->get_result()->fetch_assoc();
    $action = 'form';
} elseif ($action === 'add') {
    $action = 'form';
}

// Fetch categories for dropdown
$categoriesList = $conn->query("SELECT id, name FROM categories WHERE status = 'active' ORDER BY name ASC");
$categoriesArr = [];
if ($categoriesList) {
    while ($c = $categoriesList->fetch_assoc()) {
        $categoriesArr[] = $c;
    }
}

// Filters for listing
$filterCategory = intval($_GET['category'] ?? 0);
$filterStatus = sanitize($_GET['status'] ?? '');
$search = sanitize($_GET['search'] ?? '');

$sql = "
    SELECT p.*, c.name as category_name 
    FROM products p 
    LEFT JOIN categories c ON p.category_id = c.id 
    WHERE 1=1 
";

if ($filterCategory > 0) {
    $sql .= " AND p.category_id = $filterCategory";
}
if (!empty($filterStatus)) {
    $sql .= " AND p.status = '$filterStatus'";
}
if (!empty($search)) {
    $sql .= " AND (p.name LIKE '%$search%' OR p.short_description LIKE '%$search%' OR p.id = '$search')";
}
$sql .= " ORDER BY p.id DESC";

$productsRes = $conn->query($sql);
?>

<?php if ($action === 'form'): ?>
    <!-- Product Add/Edit Form -->
    <div class="page-title-box">
        <div>
            <h1><?php echo $editProduct ? 'Edit Product: ' . htmlspecialchars($editProduct['name']) : 'Create New Product'; ?></h1>
            <p>Specify inventory, pricing, catalog categories, and creative descriptions.</p>
        </div>
        <a href="<?php echo ADMIN_URL; ?>/products.php" class="btn btn-outline">
            <i class="fas fa-arrow-left"></i> Back to Products
        </a>
    </div>

    <?php if ($error): ?>
        <div style="padding: 12px 18px; border-radius: var(--radius-sm); margin-bottom: 20px; background: var(--danger-bg); border: 1px solid var(--danger); color: #f87171;">
            <i class="fas fa-exclamation-circle"></i> <?php echo $error; ?>
        </div>
    <?php endif; ?>

    <div class="admin-card">
        <form action="<?php echo ADMIN_URL; ?>/products.php" method="POST" enctype="multipart/form-data">
            <input type="hidden" name="product_id" value="<?php echo $editProduct['id'] ?? 0; ?>">
            <input type="hidden" name="existing_image" value="<?php echo $editProduct['image'] ?? ''; ?>">

            <div class="form-grid">
                <div class="form-group">
                    <label class="form-label" for="prod_name">Product Name <span style="color: var(--danger);">*</span></label>
                    <input type="text" id="prod_name" name="name" class="form-control" required
                           value="<?php echo htmlspecialchars($editProduct['name'] ?? ''); ?>" placeholder="e.g. Classic Tailored Blazer">
                </div>

                <div class="form-group">
                    <label class="form-label" for="prod_cat">Category <span style="color: var(--danger);">*</span></label>
                    <select name="category_id" id="prod_cat" class="form-control" required>
                        <option value="">Select Category</option>
                        <?php foreach ($categoriesArr as $cat): ?>
                            <option value="<?php echo $cat['id']; ?>" <?php echo (($editProduct['category_id'] ?? '') == $cat['id']) ? 'selected' : ''; ?>>
                                <?php echo htmlspecialchars($cat['name']); ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>
            </div>

            <div class="form-grid" style="grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));">
                <div class="form-group">
                    <label class="form-label" for="prod_price">Base Price (₹) <span style="color: var(--danger);">*</span></label>
                    <input type="number" step="0.01" id="prod_price" name="price" class="form-control" required
                           value="<?php echo htmlspecialchars($editProduct['price'] ?? ''); ?>" placeholder="4999.00">
                </div>

                <div class="form-group">
                    <label class="form-label" for="prod_disc">Discount Price (₹)</label>
                    <input type="number" step="0.01" id="prod_disc" name="discount_price" class="form-control"
                           value="<?php echo htmlspecialchars($editProduct['discount_price'] ?? ''); ?>" placeholder="3999.00 (optional)">
                </div>

                <div class="form-group">
                    <label class="form-label" for="prod_stock">Stock Quantity <span style="color: var(--danger);">*</span></label>
                    <input type="number" id="prod_stock" name="stock_quantity" class="form-control" required
                           value="<?php echo htmlspecialchars($editProduct['stock_quantity'] ?? '50'); ?>" min="0">
                </div>

                <div class="form-group">
                    <label class="form-label" for="prod_status">Status</label>
                    <select name="status" id="prod_status" class="form-control">
                        <option value="active" <?php echo ($editProduct['status'] ?? 'active') === 'active' ? 'selected' : ''; ?>>Active</option>
                        <option value="inactive" <?php echo ($editProduct['status'] ?? '') === 'inactive' ? 'selected' : ''; ?>>Inactive</option>
                    </select>
                </div>
            </div>

            <div class="form-group">
                <label class="form-label" for="prod_short">Short Summary</label>
                <input type="text" id="prod_short" name="short_description" class="form-control"
                       value="<?php echo htmlspecialchars($editProduct['short_description'] ?? ''); ?>" placeholder="Brief 1-line headline about the product">
            </div>

            <div class="form-group">
                <label class="form-label" for="prod_desc">Full Description & Specifications</label>
                <textarea id="prod_desc" name="description" class="form-control" rows="5" placeholder="Detailed product specifications, materials, fit, and care guidelines"><?php echo htmlspecialchars($editProduct['description'] ?? ''); ?></textarea>
            </div>

            <div class="form-grid">
                <div class="form-group">
                    <label class="form-label" for="prod_file">Upload Image</label>
                    <input type="file" id="prod_file" name="image" class="form-control" accept="image/*">
                </div>

                <div class="form-group">
                    <label class="form-label" for="prod_custom_img">Or Image Filename / Asset</label>
                    <input type="text" id="prod_custom_img" name="custom_image_name" class="form-control"
                           value="<?php echo htmlspecialchars($editProduct['image'] ?? ''); ?>" placeholder="e.g. suit.jpg, maxi_dress.jpg">
                </div>
            </div>

            <div style="display: flex; gap: 25px; margin: 20px 0; padding: 15px; background: rgba(255,255,255,0.02); border-radius: var(--radius-sm); border: 1px solid var(--border);">
                <label style="display: flex; align-items: center; gap: 8px; cursor: pointer; font-size: 0.9rem;">
                    <input type="checkbox" name="is_featured" value="1" <?php echo !empty($editProduct['is_featured']) ? 'checked' : ''; ?>>
                    <span><i class="fas fa-star" style="color: var(--primary);"></i> Featured Product (Home Page)</span>
                </label>

                <label style="display: flex; align-items: center; gap: 8px; cursor: pointer; font-size: 0.9rem;">
                    <input type="checkbox" name="is_new" value="1" <?php echo !empty($editProduct['is_new']) ? 'checked' : ''; ?>>
                    <span><i class="fas fa-sparkles" style="color: var(--info);"></i> New Arrival Badge</span>
                </label>
            </div>

            <div style="display: flex; justify-content: flex-end; gap: 12px; margin-top: 25px;">
                <a href="<?php echo ADMIN_URL; ?>/products.php" class="btn btn-outline">Cancel</a>
                <button type="submit" class="btn btn-primary">
                    <i class="fas fa-save"></i> Save Product
                </button>
            </div>
        </form>
    </div>

<?php else: ?>
    <!-- Products List Table View -->
    <div class="page-title-box">
        <div>
            <h1>Products Inventory</h1>
            <p>Manage products catalog, pricing, inventory stock, and availability status.</p>
        </div>
        <a href="<?php echo ADMIN_URL; ?>/products.php?action=add" class="btn btn-primary">
            <i class="fas fa-plus"></i> Add New Product
        </a>
    </div>

    <!-- Filters & Search Bar -->
    <div class="admin-card" style="padding: 18px 24px;">
        <form action="<?php echo ADMIN_URL; ?>/products.php" method="GET" style="display: flex; gap: 15px; flex-wrap: wrap; align-items: center; justify-content: space-between;">
            <div style="display: flex; gap: 12px; flex-wrap: wrap; align-items: center; flex: 1;">
                <div class="header-search" style="width: 280px;">
                    <i class="fas fa-search" style="color: var(--text-dim);"></i>
                    <input type="text" name="search" value="<?php echo htmlspecialchars($search); ?>" placeholder="Search product name or ID...">
                </div>

                <select name="category" class="form-control" style="width: 180px;" onchange="this.form.submit()">
                    <option value="">All Categories</option>
                    <?php foreach ($categoriesArr as $c): ?>
                        <option value="<?php echo $c['id']; ?>" <?php echo $filterCategory == $c['id'] ? 'selected' : ''; ?>>
                            <?php echo htmlspecialchars($c['name']); ?>
                        </option>
                    <?php endforeach; ?>
                </select>

                <select name="status" class="form-control" style="width: 140px;" onchange="this.form.submit()">
                    <option value="">All Statuses</option>
                    <option value="active" <?php echo $filterStatus === 'active' ? 'selected' : ''; ?>>Active</option>
                    <option value="inactive" <?php echo $filterStatus === 'inactive' ? 'selected' : ''; ?>>Inactive</option>
                </select>
            </div>

            <div>
                <button type="submit" class="btn btn-outline btn-sm">Filter</button>
                <?php if ($filterCategory || $filterStatus || $search): ?>
                    <a href="<?php echo ADMIN_URL; ?>/products.php" class="btn btn-outline btn-sm" style="color: var(--danger);">Reset</a>
                <?php endif; ?>
            </div>
        </form>
    </div>

    <div class="admin-card">
        <div class="table-responsive">
            <table class="admin-table">
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Product</th>
                        <th>Category</th>
                        <th>Price</th>
                        <th>Stock</th>
                        <th>Badges</th>
                        <th>Status</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if ($productsRes && $productsRes->num_rows > 0): ?>
                        <?php while ($prod = $productsRes->fetch_assoc()): ?>
                            <?php 
                            $imageSrc = SITE_URL . '/assets/images/' . $prod['image'];
                            if (file_exists(UPLOADS_DIR . $prod['image'])) {
                                $imageSrc = UPLOADS_URL . $prod['image'];
                            }
                            ?>
                            <tr>
                                <td>#<?php echo $prod['id']; ?></td>
                                <td>
                                    <div style="display: flex; align-items: center; gap: 12px;">
                                        <div style="width: 44px; height: 44px; border-radius: var(--radius-sm); overflow: hidden; background: #222; border: 1px solid var(--border); flex-shrink: 0;">
                                            <img src="<?php echo $imageSrc; ?>" alt="" style="width: 100%; height: 100%; object-fit: cover;" onerror="this.onerror=null; this.src='https://placehold.co/100x100/171821/c5a059?text=Item';">
                                        </div>
                                        <div>
                                            <div style="font-weight: 600;"><?php echo htmlspecialchars($prod['name']); ?></div>
                                            <div style="font-size: 0.78rem; color: var(--text-muted);"><?php echo htmlspecialchars(truncateText($prod['short_description'], 45)); ?></div>
                                        </div>
                                    </div>
                                </td>
                                <td><?php echo htmlspecialchars($prod['category_name'] ?? 'Uncategorized'); ?></td>
                                <td>
                                    <div><strong><?php echo formatPrice($prod['discount_price'] ? $prod['discount_price'] : $prod['price']); ?></strong></div>
                                    <?php if ($prod['discount_price']): ?>
                                        <del style="font-size: 0.78rem; color: var(--text-dim);"><?php echo formatPrice($prod['price']); ?></del>
                                    <?php endif; ?>
                                </td>
                                <td>
                                    <?php if ($prod['stock_quantity'] <= 5): ?>
                                        <span class="badge badge-cancelled" title="Low Stock alert!">
                                            <?php echo $prod['stock_quantity']; ?> left
                                        </span>
                                    <?php else: ?>
                                        <span class="badge" style="background: rgba(255,255,255,0.06); color: var(--text);">
                                            <?php echo $prod['stock_quantity']; ?> units
                                        </span>
                                    <?php endif; ?>
                                </td>
                                <td>
                                    <div style="display: flex; gap: 5px;">
                                        <?php if ($prod['is_featured']): ?>
                                            <span class="badge badge-pending" title="Featured"><i class="fas fa-star"></i> Featured</span>
                                        <?php endif; ?>
                                        <?php if ($prod['is_new']): ?>
                                            <span class="badge badge-processing" title="New"><i class="fas fa-sparkles"></i> New</span>
                                        <?php endif; ?>
                                    </div>
                                </td>
                                <td>
                                    <a href="<?php echo ADMIN_URL; ?>/products.php?toggle_status=<?php echo $prod['id']; ?>&current=<?php echo $prod['status']; ?>" 
                                       class="badge badge-<?php echo $prod['status']; ?>" style="text-decoration: none;" title="Click to toggle status">
                                        <?php echo ucfirst($prod['status']); ?>
                                    </a>
                                </td>
                                <td>
                                    <div style="display: flex; gap: 8px;">
                                        <a href="<?php echo SITE_URL; ?>/pages/product_detail.php?id=<?php echo $prod['id']; ?>" target="_blank" class="btn btn-sm btn-outline" title="Preview on store">
                                            <i class="fas fa-external-link-alt"></i>
                                        </a>
                                        <a href="<?php echo ADMIN_URL; ?>/products.php?edit=<?php echo $prod['id']; ?>" class="btn btn-sm btn-outline" title="Edit Product">
                                            <i class="fas fa-edit"></i>
                                        </a>
                                        <button class="btn btn-sm btn-danger" onclick="confirmDelete('Are you sure you want to delete \'<?php echo addslashes($prod['name']); ?>\'?', '<?php echo ADMIN_URL; ?>/products.php?delete=<?php echo $prod['id']; ?>')" title="Delete">
                                            <i class="fas fa-trash"></i>
                                        </button>
                                    </div>
                                </td>
                            </tr>
                        <?php endwhile; ?>
                    <?php else: ?>
                        <tr>
                            <td colspan="8" style="text-align: center; padding: 35px; color: var(--text-muted);">
                                No products found matching criteria.
                            </td>
                        </tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
<?php endif; ?>

<?php require_once __DIR__ . '/includes/footer.php'; ?>
