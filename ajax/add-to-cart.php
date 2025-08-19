<?php
/**
 * Ola Store Electronics - Add to Cart AJAX
 * Handle adding products to shopping cart
 */

require_once '../includes/config.php';
require_once '../includes/functions.php';

// Set JSON response header
header('Content-Type: application/json');

// Check if it's a POST request
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['success' => false, 'message' => 'Method not allowed']);
    exit();
}

// Get JSON input
$input = json_decode(file_get_contents('php://input'), true);

if (!$input) {
    http_response_code(400);
    echo json_encode(['success' => false, 'message' => 'Invalid input']);
    exit();
}

// Validate CSRF token
if (!isset($input['csrf_token']) || !verify_csrf_token($input['csrf_token'])) {
    http_response_code(403);
    echo json_encode(['success' => false, 'message' => 'Security validation failed']);
    exit();
}

// Validate required fields
if (!isset($input['product_id']) || !isset($input['quantity'])) {
    http_response_code(400);
    echo json_encode(['success' => false, 'message' => 'Product ID and quantity are required']);
    exit();
}

$productId = (int)$input['product_id'];
$quantity = (int)$input['quantity'];

// Validate quantity
if ($quantity <= 0) {
    http_response_code(400);
    echo json_encode(['success' => false, 'message' => 'Quantity must be greater than 0']);
    exit();
}

// Check if product exists and is active
$db = getDB();
$product = $db->fetch("SELECT * FROM products WHERE id = ? AND is_active = 1", [$productId]);

if (!$product) {
    http_response_code(404);
    echo json_encode(['success' => false, 'message' => 'Product not found']);
    exit();
}

// Check stock availability
if ($product['stock_quantity'] < $quantity) {
    http_response_code(400);
    echo json_encode(['success' => false, 'message' => 'Insufficient stock available']);
    exit();
}

// Add to cart
$result = addToCart($productId, $quantity);

if ($result) {
    $cartCount = getCartCount();
    echo json_encode([
        'success' => true,
        'message' => 'Product added to cart successfully',
        'cart_count' => $cartCount,
        'product_name' => $product['name']
    ]);
} else {
    http_response_code(500);
    echo json_encode(['success' => false, 'message' => 'Failed to add product to cart']);
}
?>