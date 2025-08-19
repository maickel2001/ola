<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo isset($page_title) ? htmlspecialchars($page_title) : 'Ola Store Electronics'; ?></title>
    <meta name="description" content="<?php echo isset($page_description) ? htmlspecialchars($page_description) : 'Premium electronics store with Apple-inspired design'; ?>">
    
    <!-- CSS -->
    <link rel="stylesheet" href="<?php echo getBaseUrl(); ?>assets/css/style.css">
    <?php if (isset($additional_css)): ?>
        <?php foreach ($additional_css as $css): ?>
        <link rel="stylesheet" href="<?php echo getBaseUrl(); ?>assets/css/<?php echo $css; ?>">
        <?php endforeach; ?>
    <?php endif; ?>
    
    <!-- Font Awesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    
    <!-- Favicon -->
    <link rel="icon" type="image/x-icon" href="<?php echo getBaseUrl(); ?>assets/images/favicon.ico">
</head>
<body>
    <!-- Header -->
    <header class="header">
        <div class="header-top">
            <div class="container">
                <div class="header-top-content">
                    <div class="contact-info">
                        <span><i class="fas fa-phone"></i> +1 (555) 123-4567</span>
                        <span><i class="fas fa-envelope"></i> info@olastore.com</span>
                    </div>
                    <div class="header-top-actions">
                        <?php if (is_logged_in()): ?>
                            <a href="<?php echo getBaseUrl(); ?>pages/profile.php" class="header-link">
                                <i class="fas fa-user"></i> My Account
                            </a>
                            <a href="<?php echo getBaseUrl(); ?>logout.php" class="header-link">
                                <i class="fas fa-sign-out-alt"></i> Logout
                            </a>
                        <?php else: ?>
                            <a href="<?php echo getBaseUrl(); ?>pages/login.php" class="header-link">
                                <i class="fas fa-sign-in-alt"></i> Login
                            </a>
                            <a href="<?php echo getBaseUrl(); ?>pages/register.php" class="header-link">
                                <i class="fas fa-user-plus"></i> Register
                            </a>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        </div>
        
        <div class="header-main">
            <div class="container">
                <div class="header-main-content">
                    <!-- Logo -->
                    <div class="logo">
                        <a href="<?php echo getBaseUrl(); ?>index.php">
                            <h1>Ola Store</h1>
                            <span>Electronics</span>
                        </a>
                    </div>
                    
                    <!-- Search Bar -->
                    <div class="search-bar">
                        <form action="<?php echo getBaseUrl(); ?>pages/store.php" method="GET" class="search-form">
                            <div class="search-input-group">
                                <input type="text" name="search" placeholder="Search products..." 
                                       value="<?php echo isset($_GET['search']) ? htmlspecialchars($_GET['search']) : ''; ?>"
                                       class="search-input">
                                <button type="submit" class="search-btn">
                                    <i class="fas fa-search"></i>
                                </button>
                            </div>
                        </form>
                    </div>
                    
                    <!-- Header Actions -->
                    <div class="header-actions">
                        <!-- Wishlist -->
                        <a href="<?php echo getBaseUrl(); ?>pages/wishlist.php" class="header-action-btn" title="Wishlist">
                            <i class="fas fa-heart"></i>
                            <?php if (is_logged_in()): ?>
                                <span class="wishlist-count"><?php echo getWishlistCount(); ?></span>
                            <?php endif; ?>
                        </a>
                        
                        <!-- Cart -->
                        <a href="<?php echo getBaseUrl(); ?>pages/cart.php" class="header-action-btn" title="Shopping Cart">
                            <i class="fas fa-shopping-cart"></i>
                            <span class="cart-count"><?php echo getCartCount(); ?></span>
                        </a>
                        
                        <!-- Mobile Menu Toggle -->
                        <button class="mobile-menu-toggle" id="mobileMenuToggle">
                            <span></span>
                            <span></span>
                            <span></span>
                        </button>
                    </div>
                </div>
            </div>
        </div>
        
        <!-- Navigation -->
        <nav class="main-navigation">
            <div class="container">
                <ul class="nav-menu">
                    <li class="nav-item">
                        <a href="<?php echo getBaseUrl(); ?>index.php" class="nav-link">Home</a>
                    </li>
                    <li class="nav-item dropdown">
                        <a href="<?php echo getBaseUrl(); ?>pages/store.php" class="nav-link">
                            Store <i class="fas fa-chevron-down"></i>
                        </a>
                        <ul class="dropdown-menu">
                            <?php 
                            $categories = getAllCategories();
                            foreach ($categories as $category):
                            ?>
                            <li>
                                <a href="<?php echo getBaseUrl(); ?>pages/store.php?category=<?php echo $category['id']; ?>">
                                    <?php echo htmlspecialchars($category['name']); ?>
                                </a>
                            </li>
                            <?php endforeach; ?>
                        </ul>
                    </li>
                    <li class="nav-item">
                        <a href="<?php echo getBaseUrl(); ?>pages/about.php" class="nav-link">About</a>
                    </li>
                    <li class="nav-item">
                        <a href="<?php echo getBaseUrl(); ?>pages/contact.php" class="nav-link">Contact</a>
                    </li>
                    <li class="nav-item">
                        <a href="<?php echo getBaseUrl(); ?>pages/support.php" class="nav-link">Support</a>
                    </li>
                </ul>
            </div>
        </nav>
    </header>

    <!-- Mobile Menu -->
    <div class="mobile-menu" id="mobileMenu">
        <div class="mobile-menu-header">
            <h3>Menu</h3>
            <button class="mobile-menu-close" id="mobileMenuClose">
                <i class="fas fa-times"></i>
            </button>
        </div>
        
        <nav class="mobile-nav">
            <ul class="mobile-nav-menu">
                <li><a href="<?php echo getBaseUrl(); ?>index.php">Home</a></li>
                <li><a href="<?php echo getBaseUrl(); ?>pages/store.php">Store</a></li>
                <li><a href="<?php echo getBaseUrl(); ?>pages/about.php">About</a></li>
                <li><a href="<?php echo getBaseUrl(); ?>pages/contact.php">Contact</a></li>
                <li><a href="<?php echo getBaseUrl(); ?>pages/support.php">Support</a></li>
            </ul>
            
            <div class="mobile-nav-categories">
                <h4>Categories</h4>
                <ul>
                    <?php foreach ($categories as $category): ?>
                    <li>
                        <a href="<?php echo getBaseUrl(); ?>pages/store.php?category=<?php echo $category['id']; ?>">
                            <?php echo htmlspecialchars($category['name']); ?>
                        </a>
                    </li>
                    <?php endforeach; ?>
                </ul>
            </div>
            
            <div class="mobile-nav-actions">
                <?php if (is_logged_in()): ?>
                    <a href="<?php echo getBaseUrl(); ?>pages/profile.php" class="btn btn-primary">My Account</a>
                    <a href="<?php echo getBaseUrl(); ?>logout.php" class="btn btn-outline">Logout</a>
                <?php else: ?>
                    <a href="<?php echo getBaseUrl(); ?>pages/login.php" class="btn btn-primary">Login</a>
                    <a href="<?php echo getBaseUrl(); ?>pages/register.php" class="btn btn-outline">Register</a>
                <?php endif; ?>
            </div>
        </nav>
    </div>

    <!-- Main Content Wrapper -->
    <div class="main-wrapper">