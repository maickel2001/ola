<?php
/**
 * Ola Store Electronics - Admin Dashboard
 * Main admin panel with overview and navigation
 */

require_once '../includes/config.php';
require_once '../includes/auth.php';
require_once '../includes/functions.php';

// Require admin access
require_admin();

// Get dashboard statistics
$db = getDB();

$totalProducts = $db->count('products');
$totalOrders = $db->count('orders');
$totalUsers = $db->count('users');
$totalRevenue = $db->fetch("SELECT SUM(total_amount) as total FROM orders WHERE payment_status = 'paid'")['total'] ?? 0;

// Get recent orders
$recentOrders = $db->fetchAll("
    SELECT o.*, u.first_name, u.last_name 
    FROM orders o 
    LEFT JOIN users u ON o.user_id = u.id 
    ORDER BY o.created_at DESC 
    LIMIT 5
");

// Get low stock products
$lowStockProducts = $db->fetchAll("
    SELECT * FROM products 
    WHERE stock_quantity <= min_stock_level AND is_active = 1 
    ORDER BY stock_quantity ASC 
    LIMIT 5
");

// Get top selling products
$topProducts = $db->fetchAll("
    SELECT p.name, p.sku, COUNT(oi.id) as sales_count, SUM(oi.total_price) as revenue
    FROM products p 
    LEFT JOIN order_items oi ON p.id = oi.product_id 
    LEFT JOIN orders o ON oi.order_id = o.id 
    WHERE o.payment_status = 'paid' 
    GROUP BY p.id 
    ORDER BY sales_count DESC 
    LIMIT 5
");

$page_title = 'Dashboard';
require_once 'includes/header.php';
?>
            <!-- Dashboard Header -->
            <div class="dashboard-header">
                <h1 class="dashboard-title">Dashboard</h1>
                <p class="dashboard-subtitle">Welcome back, <?php echo $_SESSION['user_name']; ?>! Here's what's happening with your store.</p>
            </div>

            <!-- Statistics Cards -->
            <div class="stats-grid">
                <div class="stat-card">
                    <div class="stat-icon products">
                        <i class="fas fa-box"></i>
                    </div>
                    <div class="stat-content">
                        <h3 class="stat-number"><?php echo number_format($totalProducts); ?></h3>
                        <p class="stat-label">Total Products</p>
                    </div>
                    <div class="stat-change positive">
                        <i class="fas fa-arrow-up"></i>
                        <span>12%</span>
                    </div>
                </div>

                <div class="stat-card">
                    <div class="stat-icon orders">
                        <i class="fas fa-shopping-cart"></i>
                    </div>
                    <div class="stat-content">
                        <h3 class="stat-number"><?php echo number_format($totalOrders); ?></h3>
                        <p class="stat-label">Total Orders</p>
                    </div>
                    <div class="stat-change positive">
                        <i class="fas fa-arrow-up"></i>
                        <span>8%</span>
                    </div>
                </div>

                <div class="stat-card">
                    <div class="stat-icon users">
                        <i class="fas fa-users"></i>
                    </div>
                    <div class="stat-content">
                        <h3 class="stat-number"><?php echo number_format($totalUsers); ?></h3>
                        <p class="stat-label">Registered Users</p>
                    </div>
                    <div class="stat-change positive">
                        <i class="fas fa-arrow-up"></i>
                        <span>15%</span>
                    </div>
                </div>

                <div class="stat-card">
                    <div class="stat-icon revenue">
                        <i class="fas fa-dollar-sign"></i>
                    </div>
                    <div class="stat-content">
                        <h3 class="stat-number"><?php echo format_price($totalRevenue); ?></h3>
                        <p class="stat-label">Total Revenue</p>
                    </div>
                    <div class="stat-change positive">
                        <i class="fas fa-arrow-up"></i>
                        <span>23%</span>
                    </div>
                </div>
            </div>

            <!-- Dashboard Content -->
            <div class="dashboard-content">
                <!-- Charts Section -->
                <div class="dashboard-section">
                    <div class="section-header">
                        <h2 class="section-title">Sales Overview</h2>
                        <div class="section-actions">
                            <select class="chart-period">
                                <option value="7">Last 7 Days</option>
                                <option value="30" selected>Last 30 Days</option>
                                <option value="90">Last 90 Days</option>
                                <option value="365">Last Year</option>
                            </select>
                        </div>
                    </div>
                    
                    <div class="charts-grid">
                        <div class="chart-container">
                            <canvas id="salesChart" width="400" height="200"></canvas>
                        </div>
                        
                        <div class="chart-container">
                            <canvas id="revenueChart" width="400" height="200"></canvas>
                        </div>
                    </div>
                </div>

                <!-- Recent Activity -->
                <div class="dashboard-section">
                    <div class="section-header">
                        <h2 class="section-title">Recent Orders</h2>
                        <a href="orders.php" class="section-link">View All Orders</a>
                    </div>
                    
                    <div class="table-container">
                        <table class="admin-table">
                            <thead>
                                <tr>
                                    <th>Order #</th>
                                    <th>Customer</th>
                                    <th>Amount</th>
                                    <th>Status</th>
                                    <th>Date</th>
                                    <th>Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php if (!empty($recentOrders)): ?>
                                    <?php foreach ($recentOrders as $order): ?>
                                        <tr>
                                            <td>
                                                <a href="orders.php?order=<?php echo $order['order_number']; ?>" class="order-link">
                                                    <?php echo htmlspecialchars($order['order_number']); ?>
                                                </a>
                                            </td>
                                            <td>
                                                <?php if ($order['user_id']): ?>
                                                    <?php echo htmlspecialchars($order['first_name'] . ' ' . $order['last_name']); ?>
                                                <?php else: ?>
                                                    <?php echo htmlspecialchars($order['guest_name']); ?>
                                                <?php endif; ?>
                                            </td>
                                            <td class="amount"><?php echo format_price($order['total_amount']); ?></td>
                                            <td>
                                                <span class="status-badge status-<?php echo $order['status']; ?>">
                                                    <?php echo ucfirst($order['status']); ?>
                                                </span>
                                            </td>
                                            <td><?php echo formatDate($order['created_at']); ?></td>
                                            <td>
                                                <div class="table-actions">
                                                    <a href="orders.php?order=<?php echo $order['order_number']; ?>" class="action-btn" title="View Details">
                                                        <i class="fas fa-eye"></i>
                                                    </a>
                                                    <button class="action-btn" title="Update Status">
                                                        <i class="fas fa-edit"></i>
                                                    </button>
                                                </div>
                                            </td>
                                        </tr>
                                    <?php endforeach; ?>
                                <?php else: ?>
                                    <tr>
                                        <td colspan="6" class="no-data">No orders found</td>
                                    </tr>
                                <?php endif; ?>
                            </tbody>
                        </table>
                    </div>
                </div>

                <!-- Quick Actions & Alerts -->
                <div class="dashboard-grid">
                    <!-- Low Stock Alert -->
                    <div class="dashboard-section">
                        <div class="section-header">
                            <h2 class="section-title">Low Stock Alert</h2>
                            <a href="products.php" class="section-link">Manage Products</a>
                        </div>
                        
                        <div class="alert-list">
                            <?php if (!empty($lowStockProducts)): ?>
                                <?php foreach ($lowStockProducts as $product): ?>
                                    <div class="alert-item">
                                        <div class="alert-icon warning">
                                            <i class="fas fa-exclamation-triangle"></i>
                                        </div>
                                        <div class="alert-content">
                                            <h4 class="alert-title"><?php echo htmlspecialchars($product['name']); ?></h4>
                                            <p class="alert-message">Only <?php echo $product['stock_quantity']; ?> units left</p>
                                        </div>
                                        <a href="products.php?edit=<?php echo $product['id']; ?>" class="alert-action">
                                            <i class="fas fa-edit"></i>
                                        </a>
                                    </div>
                                <?php endforeach; ?>
                            <?php else: ?>
                                <div class="no-alerts">
                                    <i class="fas fa-check-circle"></i>
                                    <p>All products have sufficient stock</p>
                                </div>
                            <?php endif; ?>
                        </div>
                    </div>

                    <!-- Top Products -->
                    <div class="dashboard-section">
                        <div class="section-header">
                            <h2 class="section-title">Top Selling Products</h2>
                            <a href="analytics.php" class="section-link">View Analytics</a>
                        </div>
                        
                        <div class="top-products">
                            <?php if (!empty($topProducts)): ?>
                                <?php foreach ($topProducts as $index => $product): ?>
                                    <div class="product-item">
                                        <div class="product-rank">#<?php echo $index + 1; ?></div>
                                        <div class="product-info">
                                            <h4 class="product-name"><?php echo htmlspecialchars($product['name']); ?></h4>
                                            <p class="product-sku"><?php echo htmlspecialchars($product['sku']); ?></p>
                                        </div>
                                        <div class="product-stats">
                                            <span class="sales-count"><?php echo $product['sales_count']; ?> sales</span>
                                            <span class="revenue"><?php echo format_price($product['revenue']); ?></span>
                                        </div>
                                    </div>
                                <?php endforeach; ?>
                            <?php else: ?>
                                <div class="no-data">No sales data available</div>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </main>

<?php require_once 'includes/footer.php'; ?>