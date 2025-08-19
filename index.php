<?php
/**
 * Ola Store Electronics - Landing Page
 * Modern, minimalist design inspired by Apple's aesthetic
 */

require_once 'includes/config.php';
require_once 'includes/auth.php';
require_once 'includes/functions.php';

// Get featured products and categories
$featuredProducts = getFeaturedProducts(6);
$categories = getAllCategories();

// Get cart count
$cartCount = getCartCount();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Ola Store Electronics - Premium Electronics Store</title>
    <meta name="description" content="Discover the latest smartphones, laptops, smartwatches, and accessories at Ola Store Electronics. Premium quality with Apple-inspired design.">
    <meta name="keywords" content="electronics, smartphones, laptops, smartwatches, accessories, premium, quality">
    
    <!-- Open Graph Meta Tags -->
    <meta property="og:title" content="Ola Store Electronics - Premium Electronics Store">
    <meta property="og:description" content="Discover the latest electronics with premium quality and Apple-inspired design.">
    <meta property="og:type" content="website">
    <meta property="og:url" content="<?php echo SITE_URL; ?>">
    
    <!-- Favicon -->
    <link rel="icon" type="image/x-icon" href="assets/images/favicon.ico">
    
    <!-- CSS -->
    <link rel="stylesheet" href="assets/css/style.css">
    
    <!-- Font Awesome for icons -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
</head>
<body>
    <!-- Header -->
    <header class="header">
        <nav class="navbar">
            <div class="nav-container">
                <div class="nav-brand">
                    <a href="index.php" class="logo">
                        <i class="fas fa-bolt"></i>
                        <span>Ola Store</span>
                    </a>
                </div>
                
                <div class="nav-menu">
                    <ul class="nav-list">
                        <li class="nav-item">
                            <a href="index.php" class="nav-link active">Home</a>
                        </li>
                        <li class="nav-item">
                            <a href="pages/store.php" class="nav-link">Store</a>
                        </li>
                        <li class="nav-item">
                            <a href="#categories" class="nav-link">Categories</a>
                        </li>
                        <li class="nav-item">
                            <a href="#about" class="nav-link">About</a>
                        </li>
                        <li class="nav-item">
                            <a href="#contact" class="nav-link">Contact</a>
                        </li>
                    </ul>
                </div>
                
                <div class="nav-actions">
                    <div class="search-container">
                        <form action="pages/store.php" method="GET" class="search-form">
                            <input type="text" name="search" placeholder="Search products..." class="search-input">
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
                                    <a href="pages/profile.php" class="dropdown-item">
                                        <i class="fas fa-user-circle"></i> Profile
                                    </a>
                                    <a href="pages/orders.php" class="dropdown-item">
                                        <i class="fas fa-shopping-bag"></i> Orders
                                    </a>
                                    <?php if (is_admin()): ?>
                                        <a href="admin/dashboard.php" class="dropdown-item">
                                            <i class="fas fa-cog"></i> Admin
                                        </a>
                                    <?php endif; ?>
                                    <a href="logout.php" class="dropdown-item">
                                        <i class="fas fa-sign-out-alt"></i> Logout
                                    </a>
                                </div>
                            </div>
                        <?php else: ?>
                            <a href="pages/login.php" class="auth-btn">Login</a>
                            <a href="pages/register.php" class="auth-btn primary">Sign Up</a>
                        <?php endif; ?>
                        
                        <a href="pages/cart.php" class="cart-btn">
                            <i class="fas fa-shopping-cart"></i>
                            <?php if ($cartCount > 0): ?>
                                <span class="cart-count"><?php echo $cartCount; ?></span>
                            <?php endif; ?>
                        </a>
                    </div>
                </div>
                
                <button class="mobile-menu-btn">
                    <span></span>
                    <span></span>
                    <span></span>
                </button>
            </div>
        </nav>
    </header>

    <!-- Main Content -->
    <main class="main">
        <!-- Hero Section -->
        <section class="hero">
            <div class="hero-container">
                <div class="hero-content">
                    <h1 class="hero-title">
                        Discover Premium Electronics
                        <span class="hero-subtitle">Inspired by Innovation</span>
                    </h1>
                    <p class="hero-description">
                        Experience the latest smartphones, laptops, smartwatches, and accessories with our premium collection. 
                        Quality meets design in every product.
                    </p>
                    <div class="hero-actions">
                        <a href="pages/store.php" class="btn btn-primary btn-large">
                            <span>Shop Now</span>
                            <i class="fas fa-arrow-right"></i>
                        </a>
                        <a href="#categories" class="btn btn-secondary btn-large">
                            <span>Explore Categories</span>
                            <i class="fas fa-chevron-down"></i>
                        </a>
                    </div>
                </div>
                
                <div class="hero-visual">
                    <div class="hero-image-container">
                        <div class="hero-image hero-image-1">
                            <img src="assets/images/hero/iphone-hero.jpg" alt="iPhone 15 Pro" class="hero-img">
                        </div>
                        <div class="hero-image hero-image-2">
                            <img src="assets/images/hero/macbook-hero.jpg" alt="MacBook Air M2" class="hero-img">
                        </div>
                        <div class="hero-image hero-image-3">
                            <img src="assets/images/hero/watch-hero.jpg" alt="Apple Watch Series 9" class="hero-img">
                        </div>
                    </div>
                </div>
            </div>
        </section>

        <!-- Categories Section -->
        <section id="categories" class="categories">
            <div class="container">
                <div class="section-header">
                    <h2 class="section-title">Shop by Category</h2>
                    <p class="section-subtitle">Find exactly what you're looking for</p>
                </div>
                
                <div class="categories-grid">
                    <?php foreach ($categories as $category): ?>
                        <div class="category-card">
                            <div class="category-icon">
                                <?php
                                $iconClass = 'fas fa-mobile-alt';
                                switch (strtolower($category['name'])) {
                                    case 'smartphones':
                                        $iconClass = 'fas fa-mobile-alt';
                                        break;
                                    case 'laptops':
                                        $iconClass = 'fas fa-laptop';
                                        break;
                                    case 'smartwatches':
                                        $iconClass = 'fas fa-clock';
                                        break;
                                    case 'accessories':
                                        $iconClass = 'fas fa-headphones';
                                        break;
                                }
                                ?>
                                <i class="<?php echo $iconClass; ?>"></i>
                            </div>
                            <h3 class="category-name"><?php echo htmlspecialchars($category['name']); ?></h3>
                            <p class="category-description"><?php echo htmlspecialchars($category['description']); ?></p>
                            <a href="pages/store.php?category=<?php echo $category['id']; ?>" class="category-link">
                                Explore <i class="fas fa-arrow-right"></i>
                            </a>
                        </div>
                    <?php endforeach; ?>
                </div>
            </div>
        </section>

        <!-- Featured Products Section -->
        <section class="featured-products">
            <div class="container">
                <div class="section-header">
                    <h2 class="section-title">Featured Products</h2>
                    <p class="section-subtitle">Handpicked premium electronics for you</p>
                </div>
                
                <div class="products-slider">
                    <div class="products-grid">
                        <?php foreach ($featuredProducts as $product): ?>
                            <div class="product-card">
                                <div class="product-image">
                                    <img src="<?php echo $product['image_path'] ?: 'assets/images/products/placeholder.jpg'; ?>" 
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
                                </div>
                                
                                <div class="product-info">
                                    <h3 class="product-name">
                                        <a href="pages/product.php?slug=<?php echo $product['slug']; ?>">
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
                                    
                                    <button class="add-to-cart-btn" data-product-id="<?php echo $product['id']; ?>">
                                        <i class="fas fa-shopping-cart"></i>
                                        Add to Cart
                                    </button>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    </div>
                </div>
                
                <div class="section-actions">
                    <a href="pages/store.php" class="btn btn-outline btn-large">
                        View All Products
                        <i class="fas fa-arrow-right"></i>
                    </a>
                </div>
            </div>
        </section>

        <!-- Testimonials Section -->
        <section class="testimonials">
            <div class="container">
                <div class="section-header">
                    <h2 class="section-title">What Our Customers Say</h2>
                    <p class="section-subtitle">Real experiences from real customers</p>
                </div>
                
                <div class="testimonials-grid">
                    <div class="testimonial-card">
                        <div class="testimonial-content">
                            <p>"Amazing quality products and exceptional customer service. The iPhone I bought exceeded my expectations!"</p>
                        </div>
                        <div class="testimonial-author">
                            <div class="author-avatar">
                                <i class="fas fa-user"></i>
                            </div>
                            <div class="author-info">
                                <h4 class="author-name">Sarah Johnson</h4>
                                <p class="author-title">Verified Customer</p>
                            </div>
                        </div>
                    </div>
                    
                    <div class="testimonial-card">
                        <div class="testimonial-content">
                            <p>"Fast delivery and the MacBook Air is perfect for my work. Highly recommend Ola Store Electronics!"</p>
                        </div>
                        <div class="testimonial-author">
                            <div class="author-avatar">
                                <i class="fas fa-user"></i>
                            </div>
                            <div class="author-info">
                                <h4 class="author-name">Michael Chen</h4>
                                <p class="author-title">Verified Customer</p>
                            </div>
                        </div>
                    </div>
                    
                    <div class="testimonial-card">
                        <div class="testimonial-content">
                            <p>"Best prices I've found online for premium electronics. The Apple Watch Series 9 is incredible!"</p>
                        </div>
                        <div class="testimonial-author">
                            <div class="author-avatar">
                                <i class="fas fa-user"></i>
                            </div>
                            <div class="author-info">
                                <h4 class="author-name">Emily Rodriguez</h4>
                                <p class="author-title">Verified Customer</p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </section>

        <!-- Newsletter Section -->
        <section class="newsletter">
            <div class="container">
                <div class="newsletter-content">
                    <h2 class="newsletter-title">Stay Updated</h2>
                    <p class="newsletter-subtitle">Get the latest product releases and exclusive offers</p>
                    
                    <form class="newsletter-form" method="POST" action="newsletter-subscribe.php">
                        <input type="hidden" name="csrf_token" value="<?php echo get_csrf_token(); ?>">
                        <div class="form-group">
                            <input type="email" name="email" placeholder="Enter your email address" 
                                   class="newsletter-input" required>
                            <button type="submit" class="newsletter-btn">
                                <span>Subscribe</span>
                                <i class="fas fa-paper-plane"></i>
                            </button>
                        </div>
                    </form>
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
                        <li><a href="index.php">Home</a></li>
                        <li><a href="pages/store.php">Store</a></li>
                        <li><a href="#categories">Categories</a></li>
                        <li><a href="#about">About Us</a></li>
                        <li><a href="#contact">Contact</a></li>
                    </ul>
                </div>
                
                <div class="footer-section">
                    <h3 class="footer-title">Customer Service</h3>
                    <ul class="footer-links">
                        <li><a href="pages/help.php">Help Center</a></li>
                        <li><a href="pages/shipping.php">Shipping Info</a></li>
                        <li><a href="pages/returns.php">Returns</a></li>
                        <li><a href="pages/warranty.php">Warranty</a></li>
                        <li><a href="pages/support.php">Support</a></li>
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
                        <a href="pages/privacy.php">Privacy Policy</a>
                        <a href="pages/terms.php">Terms of Service</a>
                        <a href="pages/sitemap.php">Sitemap</a>
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
    <script src="assets/js/main.js"></script>
    <script>
        // Initialize page-specific functionality
        document.addEventListener('DOMContentLoaded', function() {
            // Mobile menu toggle
            const mobileMenuBtn = document.querySelector('.mobile-menu-btn');
            const navMenu = document.querySelector('.nav-menu');
            
            mobileMenuBtn.addEventListener('click', function() {
                navMenu.classList.toggle('active');
                mobileMenuBtn.classList.toggle('active');
            });

            // User dropdown toggle
            const userBtn = document.querySelector('.user-btn');
            const userDropdown = document.querySelector('.user-dropdown');
            
            if (userBtn && userDropdown) {
                userBtn.addEventListener('click', function(e) {
                    e.stopPropagation();
                    userDropdown.classList.toggle('active');
                });
                
                document.addEventListener('click', function() {
                    userDropdown.classList.remove('active');
                });
            }

            // Add to cart functionality
            const addToCartBtns = document.querySelectorAll('.add-to-cart-btn');
            addToCartBtns.forEach(btn => {
                btn.addEventListener('click', function() {
                    const productId = this.dataset.productId;
                    addToCart(productId);
                });
            });

            // Quick view functionality
            const quickViewBtns = document.querySelectorAll('.quick-view-btn');
            const quickViewModal = document.getElementById('quickViewModal');
            const quickViewContent = document.getElementById('quickViewContent');
            
            quickViewBtns.forEach(btn => {
                btn.addEventListener('click', function() {
                    const productId = this.dataset.productId;
                    // Load product details via AJAX and show modal
                    loadQuickView(productId);
                });
            });

            // Close modal
            const closeBtn = document.querySelector('.close');
            closeBtn.addEventListener('click', function() {
                quickViewModal.style.display = 'none';
            });

            window.addEventListener('click', function(e) {
                if (e.target === quickViewModal) {
                    quickViewModal.style.display = 'none';
                }
            });
        });

        // Add to cart function
        function addToCart(productId) {
            fetch('ajax/add-to-cart.php', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                },
                body: JSON.stringify({
                    product_id: productId,
                    quantity: 1,
                    csrf_token: '<?php echo get_csrf_token(); ?>'
                })
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    // Update cart count
                    updateCartCount(data.cart_count);
                    showNotification('Product added to cart!', 'success');
                } else {
                    showNotification(data.message || 'Failed to add product to cart', 'error');
                }
            })
            .catch(error => {
                console.error('Error:', error);
                showNotification('An error occurred. Please try again.', 'error');
            });
        }

        // Load quick view content
        function loadQuickView(productId) {
            fetch(`ajax/quick-view.php?product_id=${productId}`)
                .then(response => response.text())
                .then(html => {
                    quickViewContent.innerHTML = html;
                    document.getElementById('quickViewModal').style.display = 'block';
                })
                .catch(error => {
                    console.error('Error:', error);
                });
        }

        // Update cart count
        function updateCartCount(count) {
            const cartCount = document.querySelector('.cart-count');
            if (cartCount) {
                cartCount.textContent = count;
            } else {
                const cartBtn = document.querySelector('.cart-btn');
                if (cartBtn) {
                    const newCartCount = document.createElement('span');
                    newCartCount.className = 'cart-count';
                    newCartCount.textContent = count;
                    cartBtn.appendChild(newCartCount);
                }
            }
        }

        // Show notification
        function showNotification(message, type = 'info') {
            const notification = document.createElement('div');
            notification.className = `notification notification-${type}`;
            notification.textContent = message;
            
            document.body.appendChild(notification);
            
            setTimeout(() => {
                notification.classList.add('show');
            }, 100);
            
            setTimeout(() => {
                notification.classList.remove('show');
                setTimeout(() => {
                    document.body.removeChild(notification);
                }, 300);
            }, 3000);
        }
    </script>
</body>
</html>