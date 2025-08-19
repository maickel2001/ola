<?php
/**
 * Ola Store Electronics - Store Page
 * Product catalog with filtering and search
 */

require_once '../includes/config.php';
require_once '../includes/auth.php';
require_once '../includes/functions.php';

// Get current page and filters
$page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
$category = isset($_GET['category']) ? (int)$_GET['category'] : null;
$search = isset($_GET['search']) ? sanitize_input($_GET['search']) : '';
$brand = isset($_GET['brand']) ? sanitize_input($_GET['brand']) : '';
$minPrice = isset($_GET['min_price']) ? (float)$_GET['min_price'] : null;
$maxPrice = isset($_GET['max_price']) ? (float)$_GET['max_price'] : null;
$sortBy = isset($_GET['sort']) ? sanitize_input($_GET['sort']) : 'newest';

// Build filters array
$filters = [];
if ($category) $filters['category'] = $category;
if ($search) $filters['search'] = $search;
if ($brand) $filters['brand'] = $brand;
if ($minPrice !== null) $filters['min_price'] = $minPrice;
if ($maxPrice !== null) $filters['max_price'] = $maxPrice;

// Get products and categories
$productsData = getAllProducts($page, ITEMS_PER_PAGE, $filters);
$categories = getAllCategories();
$brands = getBrands();
$cartCount = getCartCount();

// Get category info if filtering by category
$currentCategory = null;
if ($category) {
    $currentCategory = getCategoryBySlug($category);
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Store - Ola Store Electronics</title>
    <meta name="description" content="Browse our complete collection of premium electronics including smartphones, laptops, smartwatches, and accessories.">
    
    <!-- CSS -->
    <link rel="stylesheet" href="../assets/css/style.css">
    
    <!-- Font Awesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
</head>
<body>
    <!-- Header -->
    <header class="header">
        <nav class="navbar">
            <div class="nav-container">
                <div class="nav-brand">
                    <a href="../index.php" class="logo">
                        <i class="fas fa-bolt"></i>
                        <span>Ola Store</span>
                    </a>
                </div>
                
                <div class="nav-menu">
                    <ul class="nav-list">
                        <li class="nav-item">
                            <a href="../index.php" class="nav-link">Home</a>
                        </li>
                        <li class="nav-item">
                            <a href="store.php" class="nav-link active">Store</a>
                        </li>
                        <li class="nav-item">
                            <a href="../index.php#categories" class="nav-link">Categories</a>
                        </li>
                        <li class="nav-item">
                            <a href="../index.php#about" class="nav-link">About</a>
                        </li>
                        <li class="nav-item">
                            <a href="../index.php#contact" class="nav-link">Contact</a>
                        </li>
                    </ul>
                </div>
                
                <div class="nav-actions">
                    <div class="search-container">
                        <form action="store.php" method="GET" class="search-form">
                            <input type="text" name="search" value="<?php echo htmlspecialchars($search); ?>" 
                                   placeholder="Search products..." class="search-input">
                            <button type="submit" class="search-btn">
                                <i class="fas fa-search"></i>
                            </button>
                        </form>
                    </div>
                    
                    <div class="user-actions">
                        <?php if (is_logged_in()): ?>
                            <div class="user-menu">
                                <button class="user-btn">
                                    <i class="fas fa-user"></i>
                                    <span><?php echo $_SESSION['user_name']; ?></span>
                                </button>
                                <div class="user-dropdown">
                                    <a href="profile.php" class="dropdown-item">
                                        <i class="fas fa-user-circle"></i> Profile
                                    </a>
                                    <a href="orders.php" class="dropdown-item">
                                        <i class="fas fa-shopping-bag"></i> Orders
                                    </a>
                                    <?php if (is_admin()): ?>
                                        <a href="../admin/dashboard.php" class="dropdown-item">
                                            <i class="fas fa-cog"></i> Admin
                                        </a>
                                    <?php endif; ?>
                                    <a href="../logout.php" class="dropdown-item">
                                        <i class="fas fa-sign-out-alt"></i> Logout
                                    </a>
                                </div>
                            </div>
                        <?php else: ?>
                            <a href="login.php" class="auth-btn">Login</a>
                            <a href="register.php" class="auth-btn primary">Sign Up</a>
                        <?php endif; ?>
                        
                        <a href="cart.php" class="cart-btn">
                            <i class="fas fa-shopping-cart"></i>
                            <?php if ($cartCount > 0): ?>
                                <span class="cart-count"><?php echo $cartCount; ?></span>
                            <?php endif; ?>
                        </a>
                    </div>
                    
                    <button class="mobile-menu-btn">
                        <span></span>
                        <span></span>
                        <span></span>
                    </button>
                </div>
            </div>
        </nav>
    </header>

    <!-- Main Content -->
    <main class="main">
        <!-- Page Header -->
        <section class="page-header">
            <div class="container">
                <div class="page-header-content">
                    <h1 class="page-title">
                        <?php if ($currentCategory): ?>
                            <?php echo htmlspecialchars($currentCategory['name']); ?>
                        <?php elseif ($search): ?>
                            Search Results for "<?php echo htmlspecialchars($search); ?>"
                        <?php else: ?>
                            All Products
                        <?php endif; ?>
                    </h1>
                    
                    <?php if ($currentCategory): ?>
                        <p class="page-subtitle"><?php echo htmlspecialchars($currentCategory['description']); ?></p>
                    <?php endif; ?>
                    
                    <div class="breadcrumb">
                        <a href="../index.php">Home</a>
                        <i class="fas fa-chevron-right"></i>
                        <a href="store.php">Store</a>
                        <?php if ($currentCategory): ?>
                            <i class="fas fa-chevron-right"></i>
                            <span><?php echo htmlspecialchars($currentCategory['name']); ?></span>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        </section>

        <!-- Store Content -->
        <section class="store-content">
            <div class="container">
                <div class="store-layout">
                    <!-- Sidebar Filters -->
                    <aside class="store-sidebar">
                        <div class="filter-section">
                            <h3 class="filter-title">Filters</h3>
                            
                            <!-- Category Filter -->
                            <div class="filter-group">
                                <h4 class="filter-group-title">Categories</h4>
                                <div class="filter-options">
                                    <label class="filter-option">
                                        <input type="radio" name="category" value="" 
                                               <?php echo !$category ? 'checked' : ''; ?>>
                                        <span class="checkmark"></span>
                                        All Categories
                                    </label>
                                    <?php foreach ($categories as $cat): ?>
                                        <label class="filter-option">
                                            <input type="radio" name="category" value="<?php echo $cat['id']; ?>"
                                                   <?php echo $category == $cat['id'] ? 'checked' : ''; ?>>
                                            <span class="checkmark"></span>
                                            <?php echo htmlspecialchars($cat['name']); ?>
                                        </label>
                                    <?php endforeach; ?>
                                </div>
                            </div>

                            <!-- Brand Filter -->
                            <div class="filter-group">
                                <h4 class="filter-group-title">Brands</h4>
                                <div class="filter-options">
                                    <label class="filter-option">
                                        <input type="radio" name="brand" value="" 
                                               <?php echo !$brand ? 'checked' : ''; ?>>
                                        <span class="checkmark"></span>
                                        All Brands
                                    </label>
                                    <?php foreach ($brands as $brandItem): ?>
                                        <label class="filter-option">
                                            <input type="radio" name="brand" value="<?php echo htmlspecialchars($brandItem['brand']); ?>"
                                                   <?php echo $brand === $brandItem['brand'] ? 'checked' : ''; ?>>
                                            <span class="checkmark"></span>
                                            <?php echo htmlspecialchars($brandItem['brand']); ?>
                                        </label>
                                    <?php endforeach; ?>
                                </div>
                            </div>

                            <!-- Price Filter -->
                            <div class="filter-group">
                                <h4 class="filter-group-title">Price Range</h4>
                                <div class="price-filter">
                                    <div class="price-inputs">
                                        <input type="number" name="min_price" placeholder="Min" 
                                               value="<?php echo $minPrice; ?>" class="price-input">
                                        <span class="price-separator">-</span>
                                        <input type="number" name="max_price" placeholder="Max" 
                                               value="<?php echo $maxPrice; ?>" class="price-input">
                                    </div>
                                    <button type="button" class="price-apply-btn">Apply</button>
                                </div>
                            </div>

                            <!-- Sort Filter -->
                            <div class="filter-group">
                                <h4 class="filter-group-title">Sort By</h4>
                                <div class="filter-options">
                                    <label class="filter-option">
                                        <input type="radio" name="sort" value="newest" 
                                               <?php echo $sortBy === 'newest' ? 'checked' : ''; ?>>
                                        <span class="checkmark"></span>
                                        Newest First
                                    </label>
                                    <label class="filter-option">
                                        <input type="radio" name="sort" value="price_low" 
                                               <?php echo $sortBy === 'price_low' ? 'checked' : ''; ?>>
                                        <span class="checkmark"></span>
                                        Price: Low to High
                                    </label>
                                    <label class="filter-option">
                                        <input type="radio" name="sort" value="price_high" 
                                               <?php echo $sortBy === 'price_high' ? 'checked' : ''; ?>>
                                        <span class="checkmark"></span>
                                        Price: High to Low
                                    </label>
                                    <label class="filter-option">
                                        <input type="radio" name="sort" value="name" 
                                               <?php echo $sortBy === 'name' ? 'checked' : ''; ?>>
                                        <span class="checkmark"></span>
                                        Name: A to Z
                                    </label>
                                </div>
                            </div>

                            <!-- Clear Filters -->
                            <div class="filter-actions">
                                <button type="button" class="clear-filters-btn">Clear All Filters</button>
                            </div>
                        </div>
                    </aside>

                    <!-- Main Store Area -->
                    <div class="store-main">
                        <!-- Store Header -->
                        <div class="store-header">
                            <div class="store-stats">
                                <p class="results-count">
                                    Showing <?php echo (($page - 1) * ITEMS_PER_PAGE) + 1; ?> - 
                                    <?php echo min($page * ITEMS_PER_PAGE, $productsData['total']); ?> 
                                    of <?php echo $productsData['total']; ?> products
                                </p>
                            </div>
                            
                            <div class="store-controls">
                                <div class="view-toggle">
                                    <button class="view-btn grid-view active" data-view="grid">
                                        <i class="fas fa-th"></i>
                                    </button>
                                    <button class="view-btn list-view" data-view="list">
                                        <i class="fas fa-list"></i>
                                    </button>
                                </div>
                            </div>
                        </div>

                        <!-- Products Grid -->
                        <div class="products-container">
                            <?php if (!empty($productsData['products'])): ?>
                                <div class="products-grid" id="productsGrid">
                                    <?php foreach ($productsData['products'] as $product): ?>
                                        <div class="product-card" data-animate>
                                            <div class="product-image">
                                                <img src="<?php echo $product['image_path'] ?: '../assets/images/products/placeholder.jpg'; ?>" 
                                                     alt="<?php echo htmlspecialchars($product['name']); ?>" 
                                                     class="product-img">
                                                <div class="product-overlay">
                                                    <button class="quick-view-btn" data-product-id="<?php echo $product['id']; ?>">
                                                        <i class="fas fa-eye"></i>
                                                    </button>
                                                    <button class="wishlist-btn" data-product-id="<?php echo $product['id']; ?>">
                                                        <i class="fas fa-heart"></i>
                                                    </button>
                                                </div>
                                                
                                                <?php if ($product['sale_price']): ?>
                                                    <div class="product-badge sale">Sale</div>
                                                <?php endif; ?>
                                                
                                                <?php if ($product['stock_quantity'] <= 0): ?>
                                                    <div class="product-badge out-of-stock">Out of Stock</div>
                                                <?php endif; ?>
                                            </div>
                                            
                                            <div class="product-info">
                                                <h3 class="product-name">
                                                    <a href="product.php?slug=<?php echo $product['slug']; ?>">
                                                        <?php echo htmlspecialchars($product['name']); ?>
                                                    </a>
                                                </h3>
                                                
                                                <p class="product-category"><?php echo htmlspecialchars($product['category_name']); ?></p>
                                                
                                                <div class="product-price">
                                                    <?php if ($product['sale_price']): ?>
                                                        <span class="price-old"><?php echo format_price($product['price']); ?></span>
                                                        <span class="price-new"><?php echo format_price($product['sale_price']); ?></span>
                                                    <?php else: ?>
                                                        <span class="price"><?php echo format_price($product['price']); ?></span>
                                                    <?php endif; ?>
                                                </div>
                                                
                                                <div class="product-actions">
                                                    <?php if ($product['stock_quantity'] > 0): ?>
                                                        <button class="add-to-cart-btn" data-product-id="<?php echo $product['id']; ?>">
                                                            <i class="fas fa-shopping-cart"></i>
                                                            Add to Cart
                                                        </button>
                                                    <?php else: ?>
                                                        <button class="out-of-stock-btn" disabled>
                                                            <i class="fas fa-times"></i>
                                                            Out of Stock
                                                        </button>
                                                    <?php endif; ?>
                                                </div>
                                            </div>
                                        </div>
                                    <?php endforeach; ?>
                                </div>

                                <!-- Pagination -->
                                <?php if ($productsData['pages'] > 1): ?>
                                    <div class="pagination-container">
                                        <?php echo getPagination($page, $productsData['pages'], 'store.php?' . http_build_query(array_merge($_GET, ['page' => '']))); ?>
                                    </div>
                                <?php endif; ?>
                            <?php else: ?>
                                <div class="no-products">
                                    <div class="no-products-icon">
                                        <i class="fas fa-search"></i>
                                    </div>
                                    <h3>No Products Found</h3>
                                    <p>Try adjusting your search criteria or browse our categories.</p>
                                    <a href="store.php" class="btn btn-primary">Browse All Products</a>
                                </div>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
            </div>
        </section>
    </main>

    <!-- Footer -->
    <footer class="footer">
        <div class="container">
            <div class="footer-content">
                <div class="footer-section">
                    <div class="footer-brand">
                        <i class="fas fa-bolt"></i>
                        <span>Ola Store</span>
                    </div>
                    <p class="footer-description">
                        Premium electronics store offering the latest smartphones, laptops, smartwatches, and accessories with exceptional quality and service.
                    </p>
                    <div class="social-links">
                        <a href="#" class="social-link"><i class="fab fa-facebook"></i></a>
                        <a href="#" class="social-link"><i class="fab fa-twitter"></i></a>
                        <a href="#" class="social-link"><i class="fab fa-instagram"></i></a>
                        <a href="#" class="social-link"><i class="fab fa-youtube"></i></a>
                    </div>
                </div>
                
                <div class="footer-section">
                    <h3 class="footer-title">Quick Links</h3>
                    <ul class="footer-links">
                        <li><a href="../index.php">Home</a></li>
                        <li><a href="store.php">Store</a></li>
                        <li><a href="../index.php#categories">Categories</a></li>
                        <li><a href="../index.php#about">About Us</a></li>
                        <li><a href="../index.php#contact">Contact</a></li>
                    </ul>
                </div>
                
                <div class="footer-section">
                    <h3 class="footer-title">Customer Service</h3>
                    <ul class="footer-links">
                        <li><a href="help.php">Help Center</a></li>
                        <li><a href="shipping.php">Shipping Info</a></li>
                        <li><a href="returns.php">Returns</a></li>
                        <li><a href="warranty.php">Warranty</a></li>
                        <li><a href="support.php">Support</a></li>
                    </ul>
                </div>
                
                <div class="footer-section">
                    <h3 class="footer-title">Contact Info</h3>
                    <div class="contact-info">
                        <p><i class="fas fa-map-marker-alt"></i> 123 Tech Street, Digital City, DC 12345</p>
                        <p><i class="fas fa-phone"></i> +1 (555) 123-4567</p>
                        <p><i class="fas fa-envelope"></i> info@olastore.com</p>
                    </div>
                </div>
            </div>
            
            <div class="footer-bottom">
                <div class="footer-bottom-content">
                    <p>&copy; <?php echo date('Y'); ?> Ola Store Electronics. All rights reserved.</p>
                    <div class="footer-bottom-links">
                        <a href="privacy.php">Privacy Policy</a>
                        <a href="terms.php">Terms of Service</a>
                        <a href="sitemap.php">Sitemap</a>
                    </div>
                </div>
            </div>
        </div>
    </footer>

    <!-- Quick View Modal -->
    <div id="quickViewModal" class="modal">
        <div class="modal-content">
            <span class="close">&times;</span>
            <div id="quickViewContent"></div>
        </div>
    </div>

    <!-- JavaScript -->
    <script src="../assets/js/main.js"></script>
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            // Filter functionality
            setupFilters();
            
            // View toggle functionality
            setupViewToggle();
            
            // Mobile menu toggle
            setupMobileMenu();
        });

        function setupFilters() {
            // Category filter
            const categoryInputs = document.querySelectorAll('input[name="category"]');
            categoryInputs.forEach(input => {
                input.addEventListener('change', applyFilters);
            });

            // Brand filter
            const brandInputs = document.querySelectorAll('input[name="brand"]');
            brandInputs.forEach(input => {
                input.addEventListener('change', applyFilters);
            });

            // Sort filter
            const sortInputs = document.querySelectorAll('input[name="sort"]');
            sortInputs.forEach(input => {
                input.addEventListener('change', applyFilters);
            });

            // Price filter
            const priceApplyBtn = document.querySelector('.price-apply-btn');
            if (priceApplyBtn) {
                priceApplyBtn.addEventListener('click', applyFilters);
            }

            // Clear filters
            const clearFiltersBtn = document.querySelector('.clear-filters-btn');
            if (clearFiltersBtn) {
                clearFiltersBtn.addEventListener('click', clearFilters);
            }
        }

        function applyFilters() {
            const formData = new FormData();
            
            // Get selected category
            const selectedCategory = document.querySelector('input[name="category"]:checked');
            if (selectedCategory) {
                formData.append('category', selectedCategory.value);
            }

            // Get selected brand
            const selectedBrand = document.querySelector('input[name="brand"]:checked');
            if (selectedBrand) {
                formData.append('brand', selectedBrand.value);
            }

            // Get selected sort
            const selectedSort = document.querySelector('input[name="sort"]:checked');
            if (selectedSort) {
                formData.append('sort', selectedSort.value);
            }

            // Get price range
            const minPrice = document.querySelector('input[name="min_price"]').value;
            const maxPrice = document.querySelector('input[name="max_price"]').value;
            
            if (minPrice) formData.append('min_price', minPrice);
            if (maxPrice) formData.append('max_price', maxPrice);

            // Get search term
            const searchInput = document.querySelector('input[name="search"]');
            if (searchInput && searchInput.value) {
                formData.append('search', searchInput.value);
            }

            // Build query string
            const params = new URLSearchParams(formData);
            const queryString = params.toString();
            
            // Redirect with filters
            window.location.href = `store.php?${queryString}`;
        }

        function clearFilters() {
            // Reset all radio buttons
            document.querySelectorAll('input[type="radio"]').forEach(input => {
                input.checked = false;
            });

            // Reset price inputs
            document.querySelectorAll('input[name="min_price"], input[name="max_price"]').forEach(input => {
                input.value = '';
            });

            // Reset search
            const searchInput = document.querySelector('input[name="search"]');
            if (searchInput) {
                searchInput.value = '';
            }

            // Redirect to store without filters
            window.location.href = 'store.php';
        }

        function setupViewToggle() {
            const viewBtns = document.querySelectorAll('.view-btn');
            const productsGrid = document.getElementById('productsGrid');

            viewBtns.forEach(btn => {
                btn.addEventListener('click', function() {
                    const view = this.dataset.view;
                    
                    // Update active button
                    viewBtns.forEach(b => b.classList.remove('active'));
                    this.classList.add('active');

                    // Update grid class
                    if (productsGrid) {
                        productsGrid.className = `products-grid products-${view}`;
                    }
                });
            });
        }

        function setupMobileMenu() {
            const mobileMenuBtn = document.querySelector('.mobile-menu-btn');
            const navMenu = document.querySelector('.nav-menu');
            
            if (mobileMenuBtn && navMenu) {
                mobileMenuBtn.addEventListener('click', function() {
                    navMenu.classList.toggle('active');
                    mobileMenuBtn.classList.toggle('active');
                });
            }
        }
    </script>
</body>
</html>