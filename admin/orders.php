<?php
/**
 * Ola Store Electronics - Admin Orders Management
 * Manage orders, update statuses, and view order details
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
            case 'update_status':
                $orderId = intval($_POST['order_id']);
                $newStatus = sanitize_input($_POST['status']);
                $statusNote = sanitize_input($_POST['status_note']);
                
                $updateData = [
                    'status' => $newStatus,
                    'updated_at' => date('Y-m-d H:i:s')
                ];
                
                if ($db->update('orders', $updateData, ['id' => $orderId])) {
                    // Add status note if provided
                    if ($statusNote) {
                        $db->insert('order_notes', [
                            'order_id' => $orderId,
                            'note' => $statusNote,
                            'created_at' => date('Y-m-d H:i:s')
                        ]);
                    }
                    
                    $message = 'Order status updated successfully!';
                } else {
                    $message = 'Error updating order status.';
                }
                break;
        }
    }
}

// Get orders for listing
$page = isset($_GET['page']) ? max(1, intval($_GET['page'])) : 1;
$search = $_GET['search'] ?? '';
$status_filter = $_GET['status'] ?? '';
$date_filter = $_GET['date'] ?? '';

$where_conditions = ['1=1'];
$params = [];

if ($search) {
    $where_conditions[] = "(o.order_number LIKE ? OR o.guest_name LIKE ? OR u.first_name LIKE ? OR u.last_name LIKE ?)";
    $search_param = "%$search%";
    $params = array_merge($params, [$search_param, $search_param, $search_param, $search_param]);
}

if ($status_filter) {
    $where_conditions[] = "o.status = ?";
    $params[] = $status_filter;
}

if ($date_filter) {
    $where_conditions[] = "DATE(o.created_at) = ?";
    $params[] = $date_filter;
}

$where_clause = implode(' AND ', $where_conditions);

$total_orders = $db->fetch("
    SELECT COUNT(*) as count 
    FROM orders o 
    LEFT JOIN users u ON o.user_id = u.id 
    WHERE $where_clause
", $params)['count'];

$total_pages = ceil($total_orders / ITEMS_PER_PAGE);
$offset = ($page - 1) * ITEMS_PER_PAGE;

$orders = $db->fetchAll("
    SELECT o.*, u.first_name, u.last_name, u.email as user_email,
           COUNT(oi.id) as item_count
    FROM orders o 
    LEFT JOIN users u ON o.user_id = u.id 
    LEFT JOIN order_items oi ON o.id = oi.order_id
    WHERE $where_clause 
    GROUP BY o.id
    ORDER BY o.created_at DESC 
    LIMIT ? OFFSET ?
", array_merge($params, [ITEMS_PER_PAGE, $offset]));

// Get order details if viewing specific order
$order = null;
$orderItems = null;
$orderNotes = null;

if ($action === 'view' && isset($_GET['id'])) {
    $orderId = intval($_GET['id']);
    $order = $db->fetch("
        SELECT o.*, u.first_name, u.last_name, u.email as user_email, u.phone as user_phone
        FROM orders o 
        LEFT JOIN users u ON o.user_id = u.id 
        WHERE o.id = ?
    ", [$orderId]);
    
    if ($order) {
        $orderItems = $db->fetchAll("
            SELECT oi.*, p.name as product_name, p.image as product_image
            FROM order_items oi 
            LEFT JOIN products p ON oi.product_id = p.id 
            WHERE oi.order_id = ?
        ", [$orderId]);
        
        $orderNotes = $db->fetchAll("
            SELECT * FROM order_notes 
            WHERE order_id = ? 
            ORDER BY created_at DESC
        ", [$orderId]);
    } else {
        $action = 'list';
        $message = 'Order not found.';
    }
}

$page_title = 'Orders Management';
require_once 'includes/header.php';
?>

<!-- Page Header -->
<div class="page-header">
    <div class="header-content">
        <h1 class="page-title">Orders Management</h1>
        <p class="page-subtitle">View and manage customer orders, update statuses, and track fulfillment</p>
    </div>
    
    <div class="header-actions">
        <button class="btn btn-outline" onclick="exportOrders()">
            <i class="fas fa-download"></i>
            Export Orders
        </button>
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
                    <input type="text" name="search" placeholder="Search orders, customers..." 
                           value="<?php echo htmlspecialchars($search); ?>" class="search-input">
                    <button type="submit" class="search-btn">
                        <i class="fas fa-search"></i>
                    </button>
                </div>
                
                <div class="filter-group">
                    <select name="status" class="filter-select">
                        <option value="">All Statuses</option>
                        <option value="pending" <?php echo $status_filter === 'pending' ? 'selected' : ''; ?>>Pending</option>
                        <option value="processing" <?php echo $status_filter === 'processing' ? 'selected' : ''; ?>>Processing</option>
                        <option value="shipped" <?php echo $status_filter === 'shipped' ? 'selected' : ''; ?>>Shipped</option>
                        <option value="delivered" <?php echo $status_filter === 'delivered' ? 'selected' : ''; ?>>Delivered</option>
                        <option value="cancelled" <?php echo $status_filter === 'cancelled' ? 'selected' : ''; ?>>Cancelled</option>
                        <option value="refunded" <?php echo $status_filter === 'refunded' ? 'selected' : ''; ?>>Refunded</option>
                    </select>
                </div>
                
                <div class="filter-group">
                    <input type="date" name="date" value="<?php echo htmlspecialchars($date_filter); ?>" 
                           class="filter-input" placeholder="Filter by date">
                </div>
                
                <button type="submit" class="btn btn-secondary">Apply Filters</button>
                <a href="?" class="btn btn-outline">Clear</a>
            </div>
        </form>
    </div>

    <!-- Orders Table -->
    <div class="table-section">
        <div class="table-header">
            <h3>Orders (<?php echo number_format($total_orders); ?>)</h3>
            <div class="table-actions">
                <span class="table-stats">
                    <span class="stat-item">
                        <i class="fas fa-clock"></i>
                        <?php echo $db->count('orders', ['status' => 'pending']); ?> Pending
                    </span>
                    <span class="stat-item">
                        <i class="fas fa-shipping-fast"></i>
                        <?php echo $db->count('orders', ['status' => 'processing']); ?> Processing
                    </span>
                </span>
            </div>
        </div>
        
        <div class="table-container">
            <table class="admin-table">
                <thead>
                    <tr>
                        <th>Order #</th>
                        <th>Customer</th>
                        <th>Items</th>
                        <th>Total</th>
                        <th>Status</th>
                        <th>Payment</th>
                        <th>Date</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (!empty($orders)): ?>
                        <?php foreach ($orders as $order): ?>
                            <tr>
                                <td>
                                    <a href="?action=view&id=<?php echo $order['id']; ?>" class="order-link">
                                        <?php echo htmlspecialchars($order['order_number']); ?>
                                    </a>
                                </td>
                                <td>
                                    <div class="customer-info">
                                        <?php if ($order['user_id']): ?>
                                            <span class="customer-name">
                                                <?php echo htmlspecialchars($order['first_name'] . ' ' . $order['last_name']); ?>
                                            </span>
                                            <span class="customer-email"><?php echo htmlspecialchars($order['user_email']); ?></span>
                                        <?php else: ?>
                                            <span class="customer-name"><?php echo htmlspecialchars($order['guest_name']); ?></span>
                                            <span class="customer-email">Guest Checkout</span>
                                        <?php endif; ?>
                                    </div>
                                </td>
                                <td>
                                    <span class="item-count"><?php echo $order['item_count']; ?> items</span>
                                </td>
                                <td class="amount"><?php echo format_price($order['total_amount']); ?></td>
                                <td>
                                    <span class="status-badge status-<?php echo $order['status']; ?>">
                                        <?php echo ucfirst($order['status']); ?>
                                    </span>
                                </td>
                                <td>
                                    <div class="payment-info">
                                        <span class="payment-method"><?php echo ucfirst($order['payment_method']); ?></span>
                                        <span class="payment-status status-<?php echo $order['payment_status']; ?>">
                                            <?php echo ucfirst($order['payment_status']); ?>
                                        </span>
                                    </div>
                                </td>
                                <td><?php echo formatDate($order['created_at']); ?></td>
                                <td>
                                    <div class="table-actions">
                                        <a href="?action=view&id=<?php echo $order['id']; ?>" 
                                           class="action-btn" title="View Details">
                                            <i class="fas fa-eye"></i>
                                        </a>
                                        <button class="action-btn" title="Update Status" 
                                                onclick="updateOrderStatus(<?php echo $order['id']; ?>, '<?php echo $order['status']; ?>')">
                                            <i class="fas fa-edit"></i>
                                        </button>
                                    </div>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <tr>
                            <td colspan="8" class="no-data">
                                <div class="empty-state">
                                    <i class="fas fa-shopping-cart"></i>
                                    <h4>No orders found</h4>
                                    <p>Orders will appear here once customers start placing them.</p>
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
                    <a href="?page=<?php echo $page - 1; ?>&search=<?php echo urlencode($search); ?>&status=<?php echo urlencode($status_filter); ?>&date=<?php echo urlencode($date_filter); ?>" 
                       class="page-link">
                        <i class="fas fa-chevron-left"></i>
                        Previous
                    </a>
                <?php endif; ?>
                
                <?php for ($i = max(1, $page - 2); $i <= min($total_pages, $page + 2); $i++): ?>
                    <a href="?page=<?php echo $i; ?>&search=<?php echo urlencode($search); ?>&status=<?php echo urlencode($status_filter); ?>&date=<?php echo urlencode($date_filter); ?>" 
                       class="page-link <?php echo $i === $page ? 'active' : ''; ?>">
                        <?php echo $i; ?>
                    </a>
                <?php endfor; ?>
                
                <?php if ($page < $total_pages): ?>
                    <a href="?page=<?php echo $page + 1; ?>&search=<?php echo urlencode($search); ?>&status=<?php echo urlencode($status_filter); ?>&date=<?php echo urlencode($date_filter); ?>" 
                       class="page-link">
                        Next
                        <i class="fas fa-chevron-right"></i>
                    </a>
                <?php endif; ?>
            </div>
        <?php endif; ?>
    </div>

<?php elseif ($action === 'view' && $order): ?>
    <!-- Order Details -->
    <div class="order-details">
        <div class="order-header">
            <div class="order-info">
                <h2>Order #<?php echo htmlspecialchars($order['order_number']); ?></h2>
                <p class="order-date">Placed on <?php echo formatDate($order['created_at']); ?></p>
                <p class="order-status">
                    Status: 
                    <span class="status-badge status-<?php echo $order['status']; ?>">
                        <?php echo ucfirst($order['status']); ?>
                    </span>
                </p>
            </div>
            
            <div class="order-actions">
                <button class="btn btn-primary" onclick="updateOrderStatus(<?php echo $order['id']; ?>, '<?php echo $order['status']; ?>')">
                    <i class="fas fa-edit"></i>
                    Update Status
                </button>
                <a href="?" class="btn btn-outline">
                    <i class="fas fa-arrow-left"></i>
                    Back to Orders
                </a>
            </div>
        </div>
        
        <div class="order-grid">
            <!-- Customer Information -->
            <div class="order-section">
                <h3>Customer Information</h3>
                <div class="customer-details">
                    <?php if ($order['user_id']): ?>
                        <div class="detail-row">
                            <span class="detail-label">Name:</span>
                            <span class="detail-value">
                                <?php echo htmlspecialchars($order['first_name'] . ' ' . $order['last_name']); ?>
                            </span>
                        </div>
                        <div class="detail-row">
                            <span class="detail-label">Email:</span>
                            <span class="detail-value"><?php echo htmlspecialchars($order['user_email']); ?></span>
                        </div>
                        <div class="detail-row">
                            <span class="detail-label">Phone:</span>
                            <span class="detail-value"><?php echo htmlspecialchars($order['user_phone']); ?></span>
                        </div>
                    <?php else: ?>
                        <div class="detail-row">
                            <span class="detail-label">Guest Name:</span>
                            <span class="detail-value"><?php echo htmlspecialchars($order['guest_name']); ?></span>
                        </div>
                        <div class="detail-row">
                            <span class="detail-label">Guest Email:</span>
                            <span class="detail-value"><?php echo htmlspecialchars($order['guest_email']); ?></span>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
            
            <!-- Order Summary -->
            <div class="order-section">
                <h3>Order Summary</h3>
                <div class="order-summary">
                    <div class="summary-row">
                        <span class="summary-label">Subtotal:</span>
                        <span class="summary-value"><?php echo format_price($order['subtotal']); ?></span>
                    </div>
                    <div class="summary-row">
                        <span class="summary-label">Tax:</span>
                        <span class="summary-value"><?php echo format_price($order['tax_amount']); ?></span>
                    </div>
                    <div class="summary-row">
                        <span class="summary-label">Shipping:</span>
                        <span class="summary-value"><?php echo format_price($order['shipping_amount']); ?></span>
                    </div>
                    <div class="summary-row total">
                        <span class="summary-label">Total:</span>
                        <span class="summary-value"><?php echo format_price($order['total_amount']); ?></span>
                    </div>
                </div>
            </div>
            
            <!-- Payment Information -->
            <div class="order-section">
                <h3>Payment Information</h3>
                <div class="payment-details">
                    <div class="detail-row">
                        <span class="detail-label">Method:</span>
                        <span class="detail-value"><?php echo ucfirst($order['payment_method']); ?></span>
                    </div>
                    <div class="detail-row">
                        <span class="detail-label">Status:</span>
                        <span class="status-badge status-<?php echo $order['payment_status']; ?>">
                            <?php echo ucfirst($order['payment_status']); ?>
                        </span>
                    </div>
                    <?php if ($order['transaction_id']): ?>
                        <div class="detail-row">
                            <span class="detail-label">Transaction ID:</span>
                            <span class="detail-value"><?php echo htmlspecialchars($order['transaction_id']); ?></span>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>
        
        <!-- Order Items -->
        <div class="order-section full-width">
            <h3>Order Items</h3>
            <div class="order-items">
                <?php if (!empty($orderItems)): ?>
                    <?php foreach ($orderItems as $item): ?>
                        <div class="order-item">
                            <div class="item-image">
                                <?php if ($item['product_image']): ?>
                                    <img src="../assets/images/products/<?php echo htmlspecialchars($item['product_image']); ?>" 
                                         alt="<?php echo htmlspecialchars($item['product_name']); ?>">
                                <?php else: ?>
                                    <div class="placeholder-image">
                                        <i class="fas fa-image"></i>
                                    </div>
                                <?php endif; ?>
                            </div>
                            <div class="item-details">
                                <h4 class="item-name"><?php echo htmlspecialchars($item['product_name']); ?></h4>
                                <p class="item-sku">SKU: <?php echo htmlspecialchars($item['sku']); ?></p>
                                <div class="item-meta">
                                    <span class="item-quantity">Qty: <?php echo $item['quantity']; ?></span>
                                    <span class="item-price"><?php echo format_price($item['unit_price']); ?> each</span>
                                </div>
                            </div>
                            <div class="item-total">
                                <?php echo format_price($item['total_price']); ?>
                            </div>
                        </div>
                    <?php endforeach; ?>
                <?php else: ?>
                    <p class="no-items">No items found for this order.</p>
                <?php endif; ?>
            </div>
        </div>
        
        <!-- Order Notes -->
        <div class="order-section full-width">
            <h3>Order Notes</h3>
            <div class="order-notes">
                <?php if (!empty($orderNotes)): ?>
                    <?php foreach ($orderNotes as $note): ?>
                        <div class="note-item">
                            <div class="note-header">
                                <span class="note-date"><?php echo formatDate($note['created_at']); ?></span>
                                <span class="note-type">Status Update</span>
                            </div>
                            <p class="note-content"><?php echo htmlspecialchars($note['note']); ?></p>
                        </div>
                    <?php endforeach; ?>
                <?php else: ?>
                    <p class="no-notes">No notes for this order.</p>
                <?php endif; ?>
            </div>
        </div>
    </div>
<?php endif; ?>

<!-- Status Update Modal -->
<div id="statusModal" class="modal" style="display: none;">
    <div class="modal-content">
        <div class="modal-header">
            <h3>Update Order Status</h3>
            <button class="modal-close" onclick="closeStatusModal()">
                <i class="fas fa-times"></i>
            </button>
        </div>
        
        <form method="POST" class="status-form">
            <input type="hidden" name="action" value="update_status">
            <input type="hidden" name="order_id" id="modalOrderId">
            
            <div class="form-group">
                <label for="status" class="form-label">New Status</label>
                <select id="status" name="status" required class="form-select">
                    <option value="pending">Pending</option>
                    <option value="processing">Processing</option>
                    <option value="shipped">Shipped</option>
                    <option value="delivered">Delivered</option>
                    <option value="cancelled">Cancelled</option>
                    <option value="refunded">Refunded</option>
                </select>
            </div>
            
            <div class="form-group">
                <label for="status_note" class="form-label">Status Note (Optional)</label>
                <textarea id="status_note" name="status_note" rows="3" 
                          class="form-textarea" placeholder="Add a note about this status change..."></textarea>
            </div>
            
            <div class="form-actions">
                <button type="submit" class="btn btn-primary">Update Status</button>
                <button type="button" class="btn btn-outline" onclick="closeStatusModal()">Cancel</button>
            </div>
        </form>
    </div>
</div>

<script>
function updateOrderStatus(orderId, currentStatus) {
    document.getElementById('modalOrderId').value = orderId;
    document.getElementById('status').value = currentStatus;
    document.getElementById('statusModal').style.display = 'flex';
}

function closeStatusModal() {
    document.getElementById('statusModal').style.display = 'none';
}

function exportOrders() {
    // Implementation for exporting orders
    alert('Export functionality will be implemented here');
}

// Close modal when clicking outside
window.onclick = function(event) {
    const modal = document.getElementById('statusModal');
    if (event.target === modal) {
        closeStatusModal();
    }
}
</script>

<?php require_once 'includes/footer.php'; ?>