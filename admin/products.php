<?php
/**
 * Ola Store Electronics - Admin Products Management
 * Manage products, categories, and inventory
 */

require_once '../includes/config.php';
require_once '../includes/auth.php';
require_once '../includes/functions.php';

// Require admin access
require_admin();

$db = getDB();
$action = $_GET['action'] ?? 'list';
$message = '';

// Handle form submissions
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['action'])) {
        switch ($_POST['action']) {
            case 'add':
            case 'edit':
                $productData = [
                    'name' => sanitize_input($_POST['name']),
                    'slug' => generateSlug($_POST['name']),
                    'description' => sanitize_input($_POST['description']),
                    'price' => floatval($_POST['price']),
                    'sale_price' => !empty($_POST['sale_price']) ? floatval($_POST['sale_price']) : null,
                    'category_id' => intval($_POST['category_id']),
                    'brand' => sanitize_input($_POST['brand']),
                    'sku' => sanitize_input($_POST['sku']),
                    'stock_quantity' => intval($_POST['stock_quantity']),
                    'min_stock_level' => intval($_POST['min_stock_level']),
                    'is_active' => isset($_POST['is_active']) ? 1 : 0,
                    'is_featured' => isset($_POST['is_featured']) ? 1 : 0
                ];
                
                if ($_POST['action'] === 'add') {
                    $productId = $db->insert('products', $productData);
                    if ($productId) {
                        $message = 'Product added successfully!';
                        $action = 'list';
                    } else {
                        $message = 'Error adding product.';
                    }
                } else {
                    $productId = intval($_POST['product_id']);
                    if ($db->update('products', $productData, ['id' => $productId])) {
                        $message = 'Product updated successfully!';
                        $action = 'list';
                    } else {
                        $message = 'Error updating product.';
                    }
                }
                break;
                
            case 'delete':
                $productId = intval($_POST['product_id']);
                if ($db->delete('products', ['id' => $productId])) {
                    $message = 'Product deleted successfully!';
                } else {
                    $message = 'Error deleting product.';
                }
                break;
        }
    }
}

// Get data for forms
$categories = getAllCategories();
$brands = getBrands();

if ($action === 'edit' && isset($_GET['id'])) {
    $productId = intval($_GET['id']);
    $product = getProductById($productId);
    if (!$product) {
        $action = 'list';
        $message = 'Product not found.';
    }
}

// Get products for listing
$page = isset($_GET['page']) ? max(1, intval($_GET['page'])) : 1;
$search = $_GET['search'] ?? '';
$category_filter = $_GET['category'] ?? '';
$brand_filter = $_GET['brand'] ?? '';

$where_conditions = ['1=1'];
$params = [];

if ($search) {
    $where_conditions[] = "(p.name LIKE ? OR p.sku LIKE ? OR p.description LIKE ?)";
    $search_param = "%$search%";
    $params = array_merge($params, [$search_param, $search_param, $search_param]);
}

if ($category_filter) {
    $where_conditions[] = "p.category_id = ?";
    $params[] = $category_filter;
}

if ($brand_filter) {
    $where_conditions[] = "p.brand = ?";
    $params[] = $brand_filter;
}

$where_clause = implode(' AND ', $where_conditions);

$total_products = $db->fetch("
    SELECT COUNT(*) as count 
    FROM products p 
    WHERE $where_clause
", $params)['count'];

$total_pages = ceil($total_products / ITEMS_PER_PAGE);
$offset = ($page - 1) * ITEMS_PER_PAGE;

$products = $db->fetchAll("
    SELECT p.*, c.name as category_name 
    FROM products p 
    LEFT JOIN categories c ON p.category_id = c.id 
    WHERE $where_clause 
    ORDER BY p.created_at DESC 
    LIMIT ? OFFSET ?
", array_merge($params, [ITEMS_PER_PAGE, $offset]));

$page_title = 'Products Management';
require_once 'includes/header.php';
?>

<!-- Page Header -->
<div class="page-header">
    <div class="header-content">
        <h1 class="page-title">Products Management</h1>
        <p class="page-subtitle">Manage your product catalog, inventory, and categories</p>
    </div>
    
    <div class="header-actions">
        <a href="?action=add" class="btn btn-primary">
            <i class="fas fa-plus"></i>
            Add Product
        </a>
    </div>
</div>

<?php if ($message): ?>
    <div class="alert alert-success">
        <i class="fas fa-check-circle"></i>
        <?php echo htmlspecialchars($message); ?>
    </div>
<?php endif; ?>

<?php if ($action === 'list'): ?>
    <!-- Filters and Search -->
    <div class="filters-section">
        <form method="GET" class="filters-form">
            <div class="filters-row">
                <div class="search-group">
                    <input type="text" name="search" placeholder="Search products..." 
                           value="<?php echo htmlspecialchars($search); ?>" class="search-input">
                    <button type="submit" class="search-btn">
                        <i class="fas fa-search"></i>
                    </button>
                </div>
                
                <div class="filter-group">
                    <select name="category" class="filter-select">
                        <option value="">All Categories</option>
                        <?php foreach ($categories as $category): ?>
                            <option value="<?php echo $category['id']; ?>" 
                                    <?php echo $category_filter == $category['id'] ? 'selected' : ''; ?>>
                                <?php echo htmlspecialchars($category['name']); ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>
                
                <div class="filter-group">
                    <select name="brand" class="filter-select">
                        <option value="">All Brands</option>
                        <?php foreach ($brands as $brand): ?>
                            <option value="<?php echo $brand; ?>" 
                                    <?php echo $brand_filter == $brand ? 'selected' : ''; ?>>
                                <?php echo htmlspecialchars($brand); ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>
                
                <button type="submit" class="btn btn-secondary">Apply Filters</button>
                <a href="?" class="btn btn-outline">Clear</a>
            </div>
        </form>
    </div>

    <!-- Products Table -->
    <div class="table-section">
        <div class="table-header">
            <h3>Products (<?php echo number_format($total_products); ?>)</h3>
            <div class="table-actions">
                <button class="btn btn-outline" onclick="exportProducts()">
                    <i class="fas fa-download"></i>
                    Export
                </button>
            </div>
        </div>
        
        <div class="table-container">
            <table class="admin-table">
                <thead>
                    <tr>
                        <th>Image</th>
                        <th>Product</th>
                        <th>Category</th>
                        <th>Price</th>
                        <th>Stock</th>
                        <th>Status</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (!empty($products)): ?>
                        <?php foreach ($products as $product): ?>
                            <tr>
                                <td>
                                    <div class="product-image">
                                        <?php if ($product['image']): ?>
                                            <img src="../assets/images/products/<?php echo htmlspecialchars($product['image']); ?>" 
                                                 alt="<?php echo htmlspecialchars($product['name']); ?>">
                                        <?php else: ?>
                                            <div class="placeholder-image">
                                                <i class="fas fa-image"></i>
                                            </div>
                                        <?php endif; ?>
                                    </div>
                                </td>
                                <td>
                                    <div class="product-info">
                                        <h4 class="product-name"><?php echo htmlspecialchars($product['name']); ?></h4>
                                        <p class="product-sku"><?php echo htmlspecialchars($product['sku']); ?></p>
                                        <p class="product-brand"><?php echo htmlspecialchars($product['brand']); ?></p>
                                    </div>
                                </td>
                                <td><?php echo htmlspecialchars($product['category_name']); ?></td>
                                <td>
                                    <div class="price-info">
                                        <?php if ($product['sale_price']): ?>
                                            <span class="sale-price"><?php echo format_price($product['sale_price']); ?></span>
                                            <span class="original-price"><?php echo format_price($product['price']); ?></span>
                                        <?php else: ?>
                                            <span class="price"><?php echo format_price($product['price']); ?></span>
                                        <?php endif; ?>
                                    </div>
                                </td>
                                <td>
                                    <div class="stock-info">
                                        <span class="stock-quantity <?php echo $product['stock_quantity'] <= $product['min_stock_level'] ? 'low-stock' : ''; ?>">
                                            <?php echo $product['stock_quantity']; ?>
                                        </span>
                                        <?php if ($product['stock_quantity'] <= $product['min_stock_level']): ?>
                                            <span class="stock-warning">Low Stock</span>
                                        <?php endif; ?>
                                    </div>
                                </td>
                                <td>
                                    <div class="status-info">
                                        <span class="status-badge status-<?php echo $product['is_active'] ? 'active' : 'inactive'; ?>">
                                            <?php echo $product['is_active'] ? 'Active' : 'Inactive'; ?>
                                        </span>
                                        <?php if ($product['is_featured']): ?>
                                            <span class="featured-badge">Featured</span>
                                        <?php endif; ?>
                                    </div>
                                </td>
                                <td>
                                    <div class="table-actions">
                                        <a href="?action=edit&id=<?php echo $product['id']; ?>" 
                                           class="action-btn" title="Edit">
                                            <i class="fas fa-edit"></i>
                                        </a>
                                        <a href="../pages/product.php?slug=<?php echo $product['slug']; ?>" 
                                           class="action-btn" title="View" target="_blank">
                                            <i class="fas fa-eye"></i>
                                        </a>
                                        <button class="action-btn danger" title="Delete" 
                                                onclick="deleteProduct(<?php echo $product['id']; ?>)">
                                            <i class="fas fa-trash"></i>
                                        </button>
                                    </div>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <tr>
                            <td colspan="7" class="no-data">
                                <div class="empty-state">
                                    <i class="fas fa-box-open"></i>
                                    <h4>No products found</h4>
                                    <p>Get started by adding your first product to the catalog.</p>
                                    <a href="?action=add" class="btn btn-primary">Add Product</a>
                                </div>
                            </td>
                        </tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
        
        <!-- Pagination -->
        <?php if ($total_pages > 1): ?>
            <div class="pagination">
                <?php if ($page > 1): ?>
                    <a href="?page=<?php echo $page - 1; ?>&search=<?php echo urlencode($search); ?>&category=<?php echo urlencode($category_filter); ?>&brand=<?php echo urlencode($brand_filter); ?>" 
                       class="page-link">
                        <i class="fas fa-chevron-left"></i>
                        Previous
                    </a>
                <?php endif; ?>
                
                <?php for ($i = max(1, $page - 2); $i <= min($total_pages, $page + 2); $i++): ?>
                    <a href="?page=<?php echo $i; ?>&search=<?php echo urlencode($search); ?>&category=<?php echo urlencode($category_filter); ?>&brand=<?php echo urlencode($brand_filter); ?>" 
                       class="page-link <?php echo $i === $page ? 'active' : ''; ?>">
                        <?php echo $i; ?>
                    </a>
                <?php endfor; ?>
                
                <?php if ($page < $total_pages): ?>
                    <a href="?page=<?php echo $page + 1; ?>&search=<?php echo urlencode($search); ?>&category=<?php echo urlencode($category_filter); ?>&brand=<?php echo urlencode($brand_filter); ?>" 
                       class="page-link">
                        Next
                        <i class="fas fa-chevron-right"></i>
                    </a>
                <?php endif; ?>
            </div>
        <?php endif; ?>
    </div>

<?php elseif ($action === 'add' || $action === 'edit'): ?>
    <!-- Product Form -->
    <div class="form-section">
        <div class="form-header">
            <h2><?php echo $action === 'add' ? 'Add New Product' : 'Edit Product'; ?></h2>
            <a href="?" class="btn btn-outline">
                <i class="fas fa-arrow-left"></i>
                Back to Products
            </a>
        </div>
        
        <form method="POST" class="product-form" enctype="multipart/form-data">
            <input type="hidden" name="action" value="<?php echo $action; ?>">
            <?php if ($action === 'edit'): ?>
                <input type="hidden" name="product_id" value="<?php echo $product['id']; ?>">
            <?php endif; ?>
            
            <div class="form-grid">
                <!-- Basic Information -->
                <div class="form-group">
                    <label for="name" class="form-label">Product Name *</label>
                    <input type="text" id="name" name="name" required 
                           value="<?php echo $action === 'edit' ? htmlspecialchars($product['name']) : ''; ?>"
                           class="form-input">
                </div>
                
                <div class="form-group">
                    <label for="sku" class="form-label">SKU *</label>
                    <input type="text" id="sku" name="sku" required 
                           value="<?php echo $action === 'edit' ? htmlspecialchars($product['sku']) : ''; ?>"
                           class="form-input">
                </div>
                
                <div class="form-group">
                    <label for="category_id" class="form-label">Category *</label>
                    <select id="category_id" name="category_id" required class="form-select">
                        <option value="">Select Category</option>
                        <?php foreach ($categories as $category): ?>
                            <option value="<?php echo $category['id']; ?>" 
                                    <?php echo ($action === 'edit' && $product['category_id'] == $category['id']) ? 'selected' : ''; ?>>
                                <?php echo htmlspecialchars($category['name']); ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>
                
                <div class="form-group">
                    <label for="brand" class="form-label">Brand</label>
                    <input type="text" id="brand" name="brand" 
                           value="<?php echo $action === 'edit' ? htmlspecialchars($product['brand']) : ''; ?>"
                           class="form-input">
                </div>
                
                <!-- Pricing -->
                <div class="form-group">
                    <label for="price" class="form-label">Regular Price *</label>
                    <div class="input-group">
                        <span class="input-prefix">$</span>
                        <input type="number" id="price" name="price" step="0.01" min="0" required 
                               value="<?php echo $action === 'edit' ? $product['price'] : ''; ?>"
                               class="form-input">
                    </div>
                </div>
                
                <div class="form-group">
                    <label for="sale_price" class="form-label">Sale Price</label>
                    <div class="input-group">
                        <span class="input-prefix">$</span>
                        <input type="number" id="sale_price" name="sale_price" step="0.01" min="0" 
                               value="<?php echo $action === 'edit' ? $product['sale_price'] : ''; ?>"
                               class="form-input">
                    </div>
                </div>
                
                <!-- Inventory -->
                <div class="form-group">
                    <label for="stock_quantity" class="form-label">Stock Quantity *</label>
                    <input type="number" id="stock_quantity" name="stock_quantity" min="0" required 
                           value="<?php echo $action === 'edit' ? $product['stock_quantity'] : ''; ?>"
                           class="form-input">
                </div>
                
                <div class="form-group">
                    <label for="min_stock_level" class="form-label">Minimum Stock Level</label>
                    <input type="number" id="min_stock_level" name="min_stock_level" min="0" 
                           value="<?php echo $action === 'edit' ? $product['min_stock_level'] : ''; ?>"
                           class="form-input">
                </div>
                
                <!-- Description -->
                <div class="form-group full-width">
                    <label for="description" class="form-label">Description</label>
                    <textarea id="description" name="description" rows="6" class="form-textarea"
                              placeholder="Enter product description..."><?php echo $action === 'edit' ? htmlspecialchars($product['description']) : ''; ?></textarea>
                </div>
                
                <!-- Options -->
                <div class="form-group">
                    <label class="form-label">Product Options</label>
                    <div class="checkbox-group">
                        <label class="checkbox-label">
                            <input type="checkbox" name="is_active" value="1" 
                                   <?php echo ($action === 'edit' && $product['is_active']) ? 'checked' : ''; ?>>
                            <span class="checkmark"></span>
                            Active (visible to customers)
                        </label>
                        <label class="checkbox-label">
                            <input type="checkbox" name="is_featured" value="1" 
                                   <?php echo ($action === 'edit' && $product['is_featured']) ? 'checked' : ''; ?>>
                            <span class="checkmark"></span>
                            Featured Product
                        </label>
                    </div>
                </div>
            </div>
            
            <div class="form-actions">
                <button type="submit" class="btn btn-primary">
                    <i class="fas fa-save"></i>
                    <?php echo $action === 'add' ? 'Add Product' : 'Update Product'; ?>
                </button>
                <a href="?" class="btn btn-outline">Cancel</a>
            </div>
        </form>
    </div>
<?php endif; ?>

<script>
function deleteProduct(productId) {
    if (confirm('Are you sure you want to delete this product? This action cannot be undone.')) {
        const form = document.createElement('form');
        form.method = 'POST';
        form.innerHTML = `
            <input type="hidden" name="action" value="delete">
            <input type="hidden" name="product_id" value="${productId}">
        `;
        document.body.appendChild(form);
        form.submit();
    }
}

function exportProducts() {
    // Implementation for exporting products
    alert('Export functionality will be implemented here');
}
</script>

<?php require_once 'includes/footer.php'; ?>