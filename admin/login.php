<?php
/**
 * Ola Store Electronics - Admin Login
 * Simple admin login for testing
 */

require_once '../includes/config.php';
require_once '../includes/auth.php';

$error = '';
$success = '';

// Handle login
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = $_POST['email'] ?? '';
    $password = $_POST['password'] ?? '';
    
    if (empty($email) || empty($password)) {
        $error = 'Please enter both email and password.';
    } else {
        $auth = new Auth();
        if ($auth->login($email, $password)) {
            if ($auth->isAdmin()) {
                header('Location: dashboard.php');
                exit;
            } else {
                $error = 'Access denied. Admin privileges required.';
                $auth->logout();
            }
        } else {
            $error = 'Invalid email or password.';
        }
    }
}

// Check if already logged in
if (is_logged_in() && is_admin()) {
    header('Location: dashboard.php');
    exit;
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Login - <?php echo SITE_NAME; ?></title>
    
    <!-- Font Awesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    
    <!-- Main Styles -->
    <link rel="stylesheet" href="../assets/css/style.css">
    
    <style>
        .admin-login-page {
            min-height: 100vh;
            background: linear-gradient(135deg, var(--bg-primary), var(--bg-secondary));
            display: flex;
            align-items: center;
            justify-content: center;
            padding: var(--spacing-lg);
        }
        
        .admin-login-card {
            background: var(--white);
            border-radius: var(--radius-2xl);
            padding: var(--spacing-3xl);
            box-shadow: var(--shadow-xl);
            max-width: 400px;
            width: 100%;
            text-align: center;
        }
        
        .admin-login-header {
            margin-bottom: var(--spacing-2xl);
        }
        
        .admin-login-logo {
            display: flex;
            align-items: center;
            justify-content: center;
            gap: var(--spacing-md);
            font-size: var(--font-size-2xl);
            font-weight: 700;
            color: var(--primary-color);
            margin-bottom: var(--spacing-md);
        }
        
        .admin-login-subtitle {
            color: var(--text-tertiary);
            font-size: var(--font-size-sm);
        }
        
        .admin-login-form {
            display: flex;
            flex-direction: column;
            gap: var(--spacing-lg);
        }
        
        .form-group {
            display: flex;
            flex-direction: column;
            gap: var(--spacing-sm);
            text-align: left;
        }
        
        .form-label {
            font-weight: 600;
            color: var(--text-primary);
            font-size: var(--font-size-sm);
        }
        
        .form-input {
            padding: var(--spacing-md);
            border: 1px solid var(--glass-border);
            border-radius: var(--radius-lg);
            font-size: var(--font-size-sm);
            transition: all var(--transition-fast);
        }
        
        .form-input:focus {
            outline: none;
            border-color: var(--primary-color);
            box-shadow: 0 0 0 2px rgba(0, 122, 255, 0.1);
        }
        
        .admin-login-btn {
            background: var(--primary-color);
            color: var(--white);
            border: none;
            padding: var(--spacing-md);
            border-radius: var(--radius-lg);
            font-size: var(--font-size-sm);
            font-weight: 600;
            cursor: pointer;
            transition: all var(--transition-fast);
        }
        
        .admin-login-btn:hover {
            background: var(--secondary-color);
            transform: translateY(-1px);
            box-shadow: var(--shadow-md);
        }
        
        .alert {
            padding: var(--spacing-md);
            border-radius: var(--radius-lg);
            font-size: var(--font-size-sm);
            font-weight: 500;
        }
        
        .alert-error {
            background: rgba(255, 59, 48, 0.1);
            color: var(--error-color);
            border: 1px solid rgba(255, 59, 48, 0.2);
        }
        
        .admin-login-footer {
            margin-top: var(--spacing-xl);
            padding-top: var(--spacing-lg);
            border-top: 1px solid var(--bg-secondary);
        }
        
        .admin-login-footer a {
            color: var(--primary-color);
            text-decoration: none;
            font-size: var(--font-size-sm);
        }
        
        .admin-login-footer a:hover {
            color: var(--secondary-color);
        }
    </style>
</head>
<body>
    <div class="admin-login-page">
        <div class="admin-login-card">
            <div class="admin-login-header">
                <div class="admin-login-logo">
                    <i class="fas fa-store"></i>
                    Ola Store
                </div>
                <p class="admin-login-subtitle">Administrator Access</p>
            </div>
            
            <?php if ($error): ?>
                <div class="alert alert-error">
                    <i class="fas fa-exclamation-circle"></i>
                    <?php echo htmlspecialchars($error); ?>
                </div>
            <?php endif; ?>
            
            <form method="POST" class="admin-login-form">
                <div class="form-group">
                    <label for="email" class="form-label">Email Address</label>
                    <input type="email" id="email" name="email" required 
                           value="<?php echo htmlspecialchars($_POST['email'] ?? ''); ?>"
                           class="form-input" placeholder="admin@olastore.com">
                </div>
                
                <div class="form-group">
                    <label for="password" class="form-label">Password</label>
                    <input type="password" id="password" name="password" required 
                           class="form-input" placeholder="Enter your password">
                </div>
                
                <button type="submit" class="admin-login-btn">
                    <i class="fas fa-sign-in-alt"></i>
                    Sign In
                </button>
            </form>
            
            <div class="admin-login-footer">
                <a href="../index.php">
                    <i class="fas fa-arrow-left"></i>
                    Back to Store
                </a>
            </div>
        </div>
    </div>
</body>
</html>