<?php
/**
 * Ola Store Electronics - Logout Script
 * Handle user logout and session cleanup
 */

require_once 'includes/config.php';
require_once 'includes/auth.php';

// Perform logout
$result = $auth->logout();

// Redirect to home page with success message
$message = urlencode($result['message']);
header("Location: index.php?message=$message");
exit();
?>