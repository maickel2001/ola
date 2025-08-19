<?php
/**
 * Ola Store Electronics - Configuration File
 * Database and site configuration settings
 */

// Database Configuration
define('DB_HOST', '127.0.0.1');
define('DB_NAME', 'ola_store');
define('DB_USER', 'olastore');
define('DB_PASS', 'olastore123');
define('DB_CHARSET', 'utf8mb4');

// Site Configuration
define('SITE_NAME', 'Ola Store Electronics');
define('SITE_URL', 'http://localhost/ola-store');
define('SITE_EMAIL', 'info@olastore.com');
define('ADMIN_EMAIL', 'admin@olastore.com');

// Security Configuration
define('SECRET_KEY', 'your-secret-key-here-change-in-production');
define('SESSION_LIFETIME', 3600); // 1 hour
define('PASSWORD_COST', 12); // bcrypt cost

// File Upload Configuration
define('UPLOAD_DIR', 'assets/images/products/');
define('MAX_FILE_SIZE', 5 * 1024 * 1024); // 5MB
define('ALLOWED_EXTENSIONS', ['jpg', 'jpeg', 'png', 'webp']);

// Pagination
define('ITEMS_PER_PAGE', 12);

// Currency
define('CURRENCY', 'USD');

// Tax and Shipping
define('TAX_RATE', 0.08); // 8%
define('FREE_SHIPPING_THRESHOLD', 50.00);
define('SHIPPING_COST', 9.99);

// Email Configuration (for production, use SMTP)
define('SMTP_HOST', 'smtp.gmail.com');
define('SMTP_PORT', 587);
define('SMTP_USERNAME', 'your-email@gmail.com');
define('SMTP_PASSWORD', 'your-app-password');

// Error Reporting (disable in production)
error_reporting(E_ALL);
ini_set('display_errors', 1);

// Timezone
date_default_timezone_set('UTC');

// Start session if not already started
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// CSRF Protection
if (!isset($_SESSION['csrf_token'])) {
    $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
}

// Helper function to get CSRF token
function get_csrf_token() {
    return $_SESSION['csrf_token'];
}

// Helper function to verify CSRF token
function verify_csrf_token($token) {
    return isset($_SESSION['csrf_token']) && hash_equals($_SESSION['csrf_token'], $token);
}

// Helper function to generate random string
function generate_random_string($length = 10) {
    return bin2hex(random_bytes($length));
}

// Helper function to sanitize input
function sanitize_input($data) {
    $data = trim($data);
    $data = stripslashes($data);
    $data = htmlspecialchars($data, ENT_QUOTES, 'UTF-8');
    return $data;
}

// Helper function to validate email
function is_valid_email($email) {
    return filter_var($email, FILTER_VALIDATE_EMAIL);
}

// Helper function to format price
function format_price($price) {
    return CURRENCY_SYMBOL . number_format($price, 2);
}

// Helper function to get current URL
function get_current_url() {
    $protocol = isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on' ? 'https' : 'http';
    return $protocol . '://' . $_SERVER['HTTP_HOST'] . $_SERVER['REQUEST_URI'];
}

// Helper function to redirect
function redirect($url) {
    header("Location: $url");
    exit();
}


?>