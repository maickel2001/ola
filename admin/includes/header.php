<?php
require_once '../includes/config.php';
require_once '../includes/auth.php';

// Ensure user is logged in and is admin
require_admin();

$current_user = getCurrentUser();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo isset($page_title) ? $page_title . ' - ' : ''; ?>Admin Panel - <?php echo SITE_NAME; ?></title>
    
    <!-- Font Awesome for icons -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    
    <!-- Chart.js for analytics -->
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    
    <!-- Admin Styles -->
    <link rel="stylesheet" href="admin.css">
    
    <!-- Main Styles (for common elements) -->
    <link rel="stylesheet" href="../assets/css/style.css">
</head>
<body>
    <div class="admin-page">
        <header class="admin-header">
            <div class="admin-header-content">
                <div class="admin-brand">
                    <a href="dashboard.php" class="logo">
                        <i class="fas fa-store"></i>
                        Ola Store
                        <span class="admin-badge">Admin</span>
                    </a>
                </div>
                
                <nav class="admin-nav">
                    <a href="dashboard.php" class="admin-nav-link <?php echo basename($_SERVER['PHP_SELF']) == 'dashboard.php' ? 'active' : ''; ?>">
                        <i class="fas fa-chart-line"></i>
                        Dashboard
                    </a>
                    <a href="products.php" class="admin-nav-link <?php echo basename($_SERVER['PHP_SELF']) == 'products.php' ? 'active' : ''; ?>">
                        <i class="fas fa-box"></i>
                        Products
                    </a>
                    <a href="orders.php" class="admin-nav-link <?php echo basename($_SERVER['PHP_SELF']) == 'orders.php' ? 'active' : ''; ?>">
                        <i class="fas fa-shopping-cart"></i>
                        Orders
                    </a>
                    <a href="users.php" class="admin-nav-link <?php echo basename($_SERVER['PHP_SELF']) == 'users.php' ? 'active' : ''; ?>">
                        <i class="fas fa-users"></i>
                        Users
                    </a>
                    <a href="analytics.php" class="admin-nav-link <?php echo basename($_SERVER['PHP_SELF']) == 'analytics.php' ? 'active' : ''; ?>">
                        <i class="fas fa-chart-bar"></i>
                        Analytics
                    </a>
                </nav>
                
                <div class="admin-user">
                    <div class="user-info">
                        <span class="user-name"><?php echo htmlspecialchars($current_user['name']); ?></span>
                        <span class="user-role">Administrator</span>
                    </div>
                    
                    <div class="user-actions">
                        <a href="../index.php" class="admin-btn secondary" title="View Store">
                            <i class="fas fa-external-link-alt"></i>
                        </a>
                        <a href="../logout.php" class="admin-btn danger" title="Logout">
                            <i class="fas fa-sign-out-alt"></i>
                        </a>
                    </div>
                </div>
            </div>
        </header>
        
        <main class="admin-main">
            <div class="admin-container">