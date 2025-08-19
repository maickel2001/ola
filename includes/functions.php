<?php
/**
 * Ola Store Electronics - Main Functions
 * Utility functions for products, cart, and common operations
 */

require_once 'config.php';
require_once 'database.php';

/**
 * Product Functions
 */
function getFeaturedProducts($limit = 6) {
    $db = getDB();
    return $db->fetchAll("
        SELECT p.*, c.name as category_name, pi.image_path 
        FROM products p 
        LEFT JOIN categories c ON p.category_id = c.id 
        LEFT JOIN product_images pi ON p.id = pi.product_id AND pi.is_primary = 1
        WHERE p.is_featured = 1 AND p.is_active = 1 
        ORDER BY p.created_at DESC 
        LIMIT ?
    ", [$limit]);
}

function getProductsByCategory($categoryId, $page = 1, $perPage = ITEMS_PER_PAGE) {
    $db = getDB();
    $offset = ($page - 1) * $perPage;
    
    $products = $db->fetchAll("
        SELECT p.*, c.name as category_name, pi.image_path 
        FROM products p 
        LEFT JOIN categories c ON p.category_id = c.id 
        LEFT JOIN product_images pi ON p.id = pi.product_id AND pi.is_primary = 1
        WHERE p.category_id = ? AND p.is_active = 1 
        ORDER BY p.created_at DESC 
        LIMIT ? OFFSET ?
    ", [$categoryId, $perPage, $offset]);
    
    $total = $db->count('products', 'category_id = ? AND is_active = 1', [$categoryId]);
    
    return [
        'products' => $products,
        'total' => $total,
        'pages' => ceil($total / $perPage),
        'current_page' => $page
    ];
}

function getAllProducts($page = 1, $perPage = ITEMS_PER_PAGE, $filters = []) {
    $db = getDB();
    $offset = ($page - 1) * $perPage;
    
    $where = 'p.is_active = 1';
    $params = [];
    
    // Apply filters
    if (!empty($filters['category'])) {
        $where .= ' AND p.category_id = ?';
        $params[] = $filters['category'];
    }
    
    if (!empty($filters['brand'])) {
        $where .= ' AND p.brand = ?';
        $params[] = $filters['brand'];
    }
    
    if (!empty($filters['min_price'])) {
        $where .= ' AND p.price >= ?';
        $params[] = $filters['min_price'];
    }
    
    if (!empty($filters['max_price'])) {
        $where .= ' AND p.price <= ?';
        $params[] = $filters['max_price'];
    }
    
    if (!empty($filters['search'])) {
        $where .= ' AND (p.name LIKE ? OR p.description LIKE ? OR p.brand LIKE ?)';
        $searchTerm = '%' . $filters['search'] . '%';
        $params[] = $searchTerm;
        $params[] = $searchTerm;
        $params[] = $searchTerm;
    }
    
    $products = $db->fetchAll("
        SELECT p.*, c.name as category_name, pi.image_path 
        FROM products p 
        LEFT JOIN categories c ON p.category_id = c.id 
        LEFT JOIN product_images pi ON p.id = pi.product_id AND pi.is_primary = 1
        WHERE $where 
        ORDER BY p.created_at DESC 
        LIMIT ? OFFSET ?
    ", array_merge($params, [$perPage, $offset]));
    
    $total = $db->count('products p', $where, $params);
    
    return [
        'products' => $products,
        'total' => $total,
        'pages' => ceil($total / $perPage),
        'current_page' => $page
    ];
}

function getProductById($productId) {
    $db = getDB();
    return $db->fetch("
        SELECT p.*, c.name as category_name 
        FROM products p 
        LEFT JOIN categories c ON p.category_id = c.id 
        WHERE p.id = ? AND p.is_active = 1
    ", [$productId]);
}

function getProductBySlug($slug) {
    $db = getDB();
    return $db->fetch("
        SELECT p.*, c.name as category_name 
        FROM products p 
        LEFT JOIN categories c ON p.category_id = c.id 
        WHERE p.slug = ? AND p.is_active = 1
    ", [$slug]);
}

function getProductImages($productId) {
    $db = getDB();
    return $db->fetchAll("
        SELECT * FROM product_images 
        WHERE product_id = ? 
        ORDER BY sort_order ASC, is_primary DESC
    ", [$productId]);
}

function getProductSpecs($productId) {
    $db = getDB();
    return $db->fetchAll("
        SELECT * FROM product_specs 
        WHERE product_id = ? 
        ORDER BY sort_order ASC
    ", [$productId]);
}

function getRelatedProducts($productId, $categoryId, $limit = 4) {
    $db = getDB();
    return $db->fetchAll("
        SELECT p.*, pi.image_path 
        FROM products p 
        LEFT JOIN product_images pi ON p.id = pi.product_id AND pi.is_primary = 1
        WHERE p.category_id = ? AND p.id != ? AND p.is_active = 1 
        ORDER BY p.is_featured DESC, p.created_at DESC 
        LIMIT ?
    ", [$categoryId, $productId, $limit]);
}

function getAllCategories() {
    $db = getDB();
    return $db->fetchAll("
        SELECT * FROM categories 
        WHERE is_active = 1 
        ORDER BY sort_order ASC, name ASC
    ");
}

function getCategoryBySlug($slug) {
    $db = getDB();
    return $db->fetch("SELECT * FROM categories WHERE slug = ? AND is_active = 1", [$slug]);
}

function getBrands() {
    $db = getDB();
    return $db->fetchAll("
        SELECT DISTINCT brand 
        FROM products 
        WHERE brand IS NOT NULL AND brand != '' AND is_active = 1 
        ORDER BY brand ASC
    ");
}

/**
 * Cart Functions
 */
function addToCart($productId, $quantity = 1) {
    if (!isset($_SESSION['cart'])) {
        $_SESSION['cart'] = [];
    }
    
    if (isset($_SESSION['cart'][$productId])) {
        $_SESSION['cart'][$productId] += $quantity;
    } else {
        $_SESSION['cart'][$productId] = $quantity;
    }
    
    return true;
}

function updateCartQuantity($productId, $quantity) {
    if (!isset($_SESSION['cart'])) {
        return false;
    }
    
    if ($quantity <= 0) {
        unset($_SESSION['cart'][$productId]);
    } else {
        $_SESSION['cart'][$productId] = $quantity;
    }
    
    return true;
}

function removeFromCart($productId) {
    if (isset($_SESSION['cart'][$productId])) {
        unset($_SESSION['cart'][$productId]);
        return true;
    }
    return false;
}

function getCartItems() {
    if (!isset($_SESSION['cart']) || empty($_SESSION['cart'])) {
        return [];
    }
    
    $db = getDB();
    $cartItems = [];
    
    foreach ($_SESSION['cart'] as $productId => $quantity) {
        $product = $db->fetch("
            SELECT p.*, pi.image_path 
            FROM products p 
            LEFT JOIN product_images pi ON p.id = pi.product_id AND pi.is_primary = 1
            WHERE p.id = ? AND p.is_active = 1
        ", [$productId]);
        
        if ($product) {
            $product['quantity'] = $quantity;
            $product['total_price'] = $product['price'] * $quantity;
            $cartItems[] = $product;
        }
    }
    
    return $cartItems;
}

function getCartTotal() {
    $cartItems = getCartItems();
    $total = 0;
    
    foreach ($cartItems as $item) {
        $total += $item['total_price'];
    }
    
    return $total;
}

function getCartCount() {
    if (!isset($_SESSION['cart'])) {
        return 0;
    }
    
    return array_sum($_SESSION['cart']);
}

function clearCart() {
    unset($_SESSION['cart']);
    return true;
}

/**
 * Order Functions
 */
function createOrder($orderData) {
    $db = getDB();
    
    try {
        $db->beginTransaction();
        
        // Generate order number
        $orderNumber = 'OLA-' . date('Ymd') . '-' . strtoupper(substr(md5(uniqid()), 0, 8));
        
        // Calculate totals
        $cartItems = getCartItems();
        $subtotal = getCartTotal();
        $taxAmount = $subtotal * TAX_RATE;
        $shippingAmount = $subtotal >= FREE_SHIPPING_THRESHOLD ? 0 : SHIPPING_COST;
        $totalAmount = $subtotal + $taxAmount + $shippingAmount;
        
        // Create order
        $orderId = $db->insert('orders', [
            'order_number' => $orderNumber,
            'user_id' => $orderData['user_id'] ?? null,
            'guest_email' => $orderData['guest_email'] ?? null,
            'guest_name' => $orderData['guest_name'] ?? null,
            'subtotal' => $subtotal,
            'tax_amount' => $taxAmount,
            'shipping_amount' => $shippingAmount,
            'total_amount' => $totalAmount,
            'payment_method' => $orderData['payment_method'] ?? 'credit_card',
            'shipping_address' => $orderData['shipping_address'],
            'billing_address' => $orderData['billing_address'] ?? $orderData['shipping_address'],
            'shipping_city' => $orderData['shipping_city'],
            'shipping_state' => $orderData['shipping_state'],
            'shipping_zip' => $orderData['shipping_zip'],
            'shipping_country' => $orderData['shipping_country']
        ]);
        
        if (!$orderId) {
            throw new Exception('Failed to create order');
        }
        
        // Create order items
        foreach ($cartItems as $item) {
            $db->insert('order_items', [
                'order_id' => $orderId,
                'product_id' => $item['id'],
                'product_name' => $item['name'],
                'product_sku' => $item['sku'],
                'quantity' => $item['quantity'],
                'unit_price' => $item['price'],
                'total_price' => $item['total_price']
            ]);
            
            // Update stock
            $db->update('products', 
                ['stock_quantity' => $item['stock_quantity'] - $item['quantity']], 
                'id = ?', 
                [$item['id']]
            );
        }
        
        $db->commit();
        
        // Clear cart
        clearCart();
        
        return ['success' => true, 'order_id' => $orderId, 'order_number' => $orderNumber];
        
    } catch (Exception $e) {
        $db->rollback();
        error_log('Order creation error: ' . $e->getMessage());
        return ['success' => false, 'message' => 'Failed to create order. Please try again.'];
    }
}

function getOrderByNumber($orderNumber) {
    $db = getDB();
    return $db->fetch("
        SELECT o.*, u.first_name, u.last_name, u.email 
        FROM orders o 
        LEFT JOIN users u ON o.user_id = u.id 
        WHERE o.order_number = ?
    ", [$orderNumber]);
}

function getOrderItems($orderId) {
    $db = getDB();
    return $db->fetchAll("
        SELECT oi.*, p.slug, pi.image_path 
        FROM order_items oi 
        LEFT JOIN products p ON oi.product_id = p.id 
        LEFT JOIN product_images pi ON p.id = pi.product_id AND pi.is_primary = 1
        WHERE oi.order_id = ?
    ", [$orderId]);
}

function getUserOrders($userId, $page = 1, $perPage = 10) {
    $db = getDB();
    $offset = ($page - 1) * $perPage;
    
    $orders = $db->fetchAll("
        SELECT * FROM orders 
        WHERE user_id = ? 
        ORDER BY created_at DESC 
        LIMIT ? OFFSET ?
    ", [$userId, $perPage, $offset]);
    
    $total = $db->count('orders', 'user_id = ?', [$userId]);
    
    return [
        'orders' => $orders,
        'total' => $total,
        'pages' => ceil($total / $perPage),
        'current_page' => $page
    ];
}

/**
 * Review Functions
 */
function getProductReviews($productId, $approved = true) {
    $db = getDB();
    $where = 'product_id = ?';
    $params = [$productId];
    
    if ($approved) {
        $where .= ' AND is_approved = 1';
    }
    
    return $db->fetchAll("
        SELECT r.*, u.first_name, u.last_name 
        FROM reviews r 
        LEFT JOIN users u ON r.user_id = u.id 
        WHERE $where 
        ORDER BY r.created_at DESC
    ", $params);
}

function addProductReview($data) {
    $db = getDB();
    
    $reviewData = [
        'product_id' => $data['product_id'],
        'user_id' => $data['user_id'] ?? null,
        'guest_name' => $data['guest_name'] ?? null,
        'rating' => $data['rating'],
        'title' => $data['title'] ?? null,
        'comment' => $data['comment'] ?? null,
        'is_approved' => 0 // Requires admin approval
    ];
    
    $reviewId = $db->insert('reviews', $reviewData);
    
    if ($reviewId) {
        return ['success' => true, 'message' => 'Review submitted successfully and awaiting approval'];
    } else {
        return ['success' => false, 'message' => 'Failed to submit review. Please try again.'];
    }
}

function getProductAverageRating($productId) {
    $db = getDB();
    $result = $db->fetch("
        SELECT AVG(rating) as avg_rating 
        FROM reviews 
        WHERE product_id = ? AND is_approved = 1
    ", [$productId]);
    
    return $result ? round($result['avg_rating'], 1) : 0;
}

function getProductReviewCount($productId) {
    $db = getDB();
    return $db->count('reviews', 'product_id = ? AND is_approved = 1', [$productId]);
}

/**
 * Utility Functions
 */
function formatDate($date, $format = 'M j, Y') {
    return date($format, strtotime($date));
}

function truncateText($text, $length = 100) {
    if (strlen($text) <= $length) {
        return $text;
    }
    
    return substr($text, 0, $length) . '...';
}

function generateSlug($text) {
    // Convert to lowercase
    $text = strtolower($text);
    
    // Replace non-alphanumeric characters with hyphens
    $text = preg_replace('/[^a-z0-9\s-]/', '', $text);
    
    // Replace spaces with hyphens
    $text = preg_replace('/[\s-]+/', '-', $text);
    
    // Remove leading/trailing hyphens
    $text = trim($text, '-');
    
    return $text;
}

function uploadImage($file, $directory = UPLOAD_DIR) {
    // Check file size
    if ($file['size'] > MAX_FILE_SIZE) {
        return ['success' => false, 'message' => 'File size exceeds limit'];
    }
    
    // Check file extension
    $extension = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
    if (!in_array($extension, ALLOWED_EXTENSIONS)) {
        return ['success' => false, 'message' => 'File type not allowed'];
    }
    
    // Generate unique filename
    $filename = uniqid() . '.' . $extension;
    $filepath = $directory . $filename;
    
    // Create directory if it doesn't exist
    if (!is_dir($directory)) {
        mkdir($directory, 0755, true);
    }
    
    // Move uploaded file
    if (move_uploaded_file($file['tmp_name'], $filepath)) {
        return ['success' => true, 'filename' => $filename, 'filepath' => $filepath];
    } else {
        return ['success' => false, 'message' => 'Failed to upload file'];
    }
}

function sendEmail($to, $subject, $message, $headers = '') {
    // Basic email sending (for production, use proper SMTP library)
    if (empty($headers)) {
        $headers = 'From: ' . SITE_EMAIL . "\r\n" .
                   'Reply-To: ' . SITE_EMAIL . "\r\n" .
                   'Content-Type: text/html; charset=UTF-8';
    }
    
    return mail($to, $subject, $message, $headers);
}

function getPagination($currentPage, $totalPages, $baseUrl) {
    if ($totalPages <= 1) {
        return '';
    }
    
    $html = '<nav class="pagination" aria-label="Page navigation">';
    $html .= '<ul class="pagination-list">';
    
    // Previous button
    if ($currentPage > 1) {
        $html .= '<li><a href="' . $baseUrl . '?page=' . ($currentPage - 1) . '" class="pagination-link">&laquo; Previous</a></li>';
    }
    
    // Page numbers
    $start = max(1, $currentPage - 2);
    $end = min($totalPages, $currentPage + 2);
    
    for ($i = $start; $i <= $end; $i++) {
        $activeClass = $i == $currentPage ? ' active' : '';
        $html .= '<li><a href="' . $baseUrl . '?page=' . $i . '" class="pagination-link' . $activeClass . '">' . $i . '</a></li>';
    }
    
    // Next button
    if ($currentPage < $totalPages) {
        $html .= '<li><a href="' . $baseUrl . '?page=' . ($currentPage + 1) . '" class="pagination-link">Next &raquo;</a></li>';
    }
    
    $html .= '</ul></nav>';
    
    return $html;
}

function validateCSRF() {
    if (!isset($_POST['csrf_token']) || !verify_csrf_token($_POST['csrf_token'])) {
        http_response_code(403);
        die('CSRF token validation failed');
    }
}





/**
 * Utility Functions
 */
function getBaseUrl() {
    $protocol = isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on' ? 'https://' : 'http://';
    $host = $_SERVER['HTTP_HOST'];
    $scriptName = $_SERVER['SCRIPT_NAME'];
    $pathInfo = pathinfo($scriptName);
    $basePath = $pathInfo['dirname'];
    
    // If we're in a subdirectory, include it in the base URL
    if ($basePath !== '/') {
        return $protocol . $host . $basePath . '/';
    }
    
    return $protocol . $host . '/';
}



function getWishlistCount() {
    if (!is_logged_in()) {
        return 0;
    }
    
    $db = getDB();
    return $db->count('wishlist', 'user_id = ?', [$_SESSION['user_id']]);
}
?>