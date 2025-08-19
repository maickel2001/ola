<?php
/**
 * Ola Store Electronics - Authentication System
 * Secure user authentication and session management
 */

require_once 'config.php';
require_once 'database.php';

class Auth {
    private $db;

    public function __construct() {
        $this->db = getDB();
    }

    /**
     * User registration
     */
    public function register($data) {
        // Validate input
        if (empty($data['email']) || empty($data['password']) || empty($data['first_name']) || empty($data['last_name'])) {
            return ['success' => false, 'message' => 'All fields are required'];
        }

        if (!is_valid_email($data['email'])) {
            return ['success' => false, 'message' => 'Invalid email format'];
        }

        if (strlen($data['password']) < 8) {
            return ['success' => false, 'message' => 'Password must be at least 8 characters long'];
        }

        // Check if email already exists
        $existingUser = $this->db->fetch("SELECT id FROM users WHERE email = ?", [$data['email']]);
        if ($existingUser) {
            return ['success' => false, 'message' => 'Email already registered'];
        }

        // Hash password
        $hashedPassword = password_hash($data['password'], PASSWORD_BCRYPT, ['cost' => PASSWORD_COST]);

        // Prepare user data
        $userData = [
            'email' => sanitize_input($data['email']),
            'password' => $hashedPassword,
            'first_name' => sanitize_input($data['first_name']),
            'last_name' => sanitize_input($data['last_name']),
            'phone' => isset($data['phone']) ? sanitize_input($data['phone']) : null,
            'address' => isset($data['address']) ? sanitize_input($data['address']) : null,
            'city' => isset($data['city']) ? sanitize_input($data['city']) : null,
            'state' => isset($data['state']) ? sanitize_input($data['state']) : null,
            'zip_code' => isset($data['zip_code']) ? sanitize_input($data['zip_code']) : null,
            'country' => isset($data['country']) ? sanitize_input($data['country']) : 'United States'
        ];

        // Insert user
        $userId = $this->db->insert('users', $userData);
        
        if ($userId) {
            // Auto-login after registration
            $this->login($data['email'], $data['password']);
            return ['success' => true, 'message' => 'Registration successful! Welcome to Ola Store Electronics.'];
        } else {
            return ['success' => false, 'message' => 'Registration failed. Please try again.'];
        }
    }

    /**
     * User login
     */
    public function login($email, $password) {
        if (empty($email) || empty($password)) {
            return ['success' => false, 'message' => 'Email and password are required'];
        }

        // Get user by email
        $user = $this->db->fetch("SELECT * FROM users WHERE email = ?", [sanitize_input($email)]);
        
        if (!$user) {
            return ['success' => false, 'message' => 'Invalid email or password'];
        }

        // Verify password
        if (!password_verify($password, $user['password'])) {
            return ['success' => false, 'message' => 'Invalid email or password'];
        }

        // Check if account is active
        if (!$user['is_active']) {
            return ['success' => false, 'message' => 'Account is deactivated. Please contact support.'];
        }

        // Set session
        $_SESSION['user_id'] = $user['id'];
        $_SESSION['user_email'] = $user['email'];
        $_SESSION['user_name'] = $user['first_name'] . ' ' . $user['last_name'];
        $_SESSION['is_admin'] = $user['is_admin'];
        $_SESSION['login_time'] = time();

        // Update last login
        $this->db->update('users', ['updated_at' => date('Y-m-d H:i:s')], 'id = ?', [$user['id']]);

        return ['success' => true, 'message' => 'Login successful! Welcome back, ' . $user['first_name']];
    }

    /**
     * User logout
     */
    public function logout() {
        // Clear all session data
        session_unset();
        session_destroy();
        
        // Start new session for CSRF token
        session_start();
        $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
        
        return ['success' => true, 'message' => 'Logout successful'];
    }

    /**
     * Check if user is logged in
     */
    public function isLoggedIn() {
        return isset($_SESSION['user_id']);
    }

    /**
     * Check if user is admin
     */
    public function isAdmin() {
        return isset($_SESSION['user_id']) && isset($_SESSION['is_admin']) && $_SESSION['is_admin'];
    }

    /**
     * Get current user data
     */
    public function getCurrentUser() {
        if (!$this->isLoggedIn()) {
            return null;
        }

        return $this->db->fetch("SELECT id, email, first_name, last_name, phone, address, city, state, zip_code, country, is_admin, created_at FROM users WHERE id = ?", [$_SESSION['user_id']]);
    }

    /**
     * Update user profile
     */
    public function updateProfile($data) {
        if (!$this->isLoggedIn()) {
            return ['success' => false, 'message' => 'You must be logged in to update your profile'];
        }

        $userId = $_SESSION['user_id'];
        
        // Prepare update data
        $updateData = [];
        $allowedFields = ['first_name', 'last_name', 'phone', 'address', 'city', 'state', 'zip_code', 'country'];
        
        foreach ($allowedFields as $field) {
            if (isset($data[$field])) {
                $updateData[$field] = sanitize_input($data[$field]);
            }
        }

        if (empty($updateData)) {
            return ['success' => false, 'message' => 'No valid fields to update'];
        }

        $updateData['updated_at'] = date('Y-m-d H:i:s');

        // Update user
        $result = $this->db->update('users', $updateData, 'id = ?', [$userId]);
        
        if ($result) {
            // Update session data
            if (isset($updateData['first_name']) || isset($updateData['last_name'])) {
                $user = $this->getCurrentUser();
                $_SESSION['user_name'] = $user['first_name'] . ' ' . $user['last_name'];
            }
            
            return ['success' => true, 'message' => 'Profile updated successfully'];
        } else {
            return ['success' => false, 'message' => 'Failed to update profile. Please try again.'];
        }
    }

    /**
     * Change password
     */
    public function changePassword($currentPassword, $newPassword, $confirmPassword) {
        if (!$this->isLoggedIn()) {
            return ['success' => false, 'message' => 'You must be logged in to change your password'];
        }

        if (empty($currentPassword) || empty($newPassword) || empty($confirmPassword)) {
            return ['success' => false, 'message' => 'All password fields are required'];
        }

        if ($newPassword !== $confirmPassword) {
            return ['success' => false, 'message' => 'New passwords do not match'];
        }

        if (strlen($newPassword) < 8) {
            return ['success' => false, 'message' => 'New password must be at least 8 characters long'];
        }

        $userId = $_SESSION['user_id'];
        
        // Get current user
        $user = $this->db->fetch("SELECT password FROM users WHERE id = ?", [$userId]);
        
        // Verify current password
        if (!password_verify($currentPassword, $user['password'])) {
            return ['success' => false, 'message' => 'Current password is incorrect'];
        }

        // Hash new password
        $hashedPassword = password_hash($newPassword, PASSWORD_BCRYPT, ['cost' => PASSWORD_COST]);

        // Update password
        $result = $this->db->update('users', [
            'password' => $hashedPassword,
            'updated_at' => date('Y-m-d H:i:s')
        ], 'id = ?', [$userId]);

        if ($result) {
            return ['success' => true, 'message' => 'Password changed successfully'];
        } else {
            return ['success' => false, 'message' => 'Failed to change password. Please try again.'];
        }
    }

    /**
     * Reset password (forgot password)
     */
    public function resetPassword($email) {
        if (empty($email)) {
            return ['success' => false, 'message' => 'Email is required'];
        }

        if (!is_valid_email($email)) {
            return ['success' => false, 'message' => 'Invalid email format'];
        }

        // Check if user exists
        $user = $this->db->fetch("SELECT id, first_name FROM users WHERE email = ?", [sanitize_input($email)]);
        
        if (!$user) {
            return ['success' => false, 'message' => 'If an account with this email exists, you will receive a reset link'];
        }

        // Generate reset token
        $resetToken = bin2hex(random_bytes(32));
        $resetExpiry = date('Y-m-d H:i:s', strtotime('+1 hour'));

        // Store reset token (you might want to create a separate table for this)
        // For now, we'll use a simple approach
        $result = $this->db->update('users', [
            'reset_token' => $resetToken,
            'reset_expiry' => $resetExpiry,
            'updated_at' => date('Y-m-d H:i:s')
        ], 'id = ?', [$user['id']]);

        if ($result) {
            // Send reset email (implement your email functionality here)
            // For now, we'll just return success
            return ['success' => true, 'message' => 'If an account with this email exists, you will receive a reset link'];
        } else {
            return ['success' => false, 'message' => 'Failed to process reset request. Please try again.'];
        }
    }

    /**
     * Validate reset token
     */
    public function validateResetToken($token) {
        $user = $this->db->fetch("SELECT id, reset_expiry FROM users WHERE reset_token = ?", [$token]);
        
        if (!$user) {
            return false;
        }

        // Check if token is expired
        if (strtotime($user['reset_expiry']) < time()) {
            return false;
        }

        return $user['id'];
    }

    /**
     * Set new password with reset token
     */
    public function setNewPassword($token, $newPassword) {
        $userId = $this->validateResetToken($token);
        
        if (!$userId) {
            return ['success' => false, 'message' => 'Invalid or expired reset token'];
        }

        if (strlen($newPassword) < 8) {
            return ['success' => false, 'message' => 'Password must be at least 8 characters long'];
        }

        // Hash new password
        $hashedPassword = password_hash($newPassword, PASSWORD_BCRYPT, ['cost' => PASSWORD_COST]);

        // Update password and clear reset token
        $result = $this->db->update('users', [
            'password' => $hashedPassword,
            'reset_token' => null,
            'reset_expiry' => null,
            'updated_at' => date('Y-m-d H:i:s')
        ], 'id = ?', [$userId]);

        if ($result) {
            return ['success' => true, 'message' => 'Password reset successfully. You can now login with your new password.'];
        } else {
            return ['success' => false, 'message' => 'Failed to reset password. Please try again.'];
        }
    }
}

// Initialize auth instance
$auth = new Auth();

// Helper functions
function is_logged_in() {
    global $auth;
    return $auth->isLoggedIn();
}

function is_admin() {
    global $auth;
    return $auth->isAdmin();
}

function getCurrentUser() {
    global $auth;
    return $auth->getCurrentUser();
}

function require_login() {
    if (!is_logged_in()) {
        header('Location: login.php');
        exit();
    }
}

function require_admin() {
    if (!is_admin()) {
        header('Location: index.php');
        exit();
    }
}
?>