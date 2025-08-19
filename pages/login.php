<?php
/**
 * Ola Store Electronics - Login Page
 * User authentication with Apple-inspired design
 */

require_once '../includes/config.php';
require_once '../includes/auth.php';

$error = '';
$success = '';

// Handle login form submission
if ($_POST && isset($_POST['login'])) {
    if (!verify_csrf_token($_POST['csrf_token'])) {
        $error = 'Security validation failed. Please try again.';
    } else {
        $email = sanitize_input($_POST['email']);
        $password = $_POST['password'];
        
        $result = $auth->login($email, $password);
        
        if ($result['success']) {
            $success = $result['message'];
            // Redirect to intended page or dashboard
            $redirect = isset($_GET['redirect']) ? $_GET['redirect'] : '../index.php';
            header("Location: $redirect");
            exit();
        } else {
            $error = $result['message'];
        }
    }
}

// Handle forgot password form
if ($_POST && isset($_POST['forgot_password'])) {
    if (!verify_csrf_token($_POST['csrf_token'])) {
        $error = 'Security validation failed. Please try again.';
    } else {
        $email = sanitize_input($_POST['email']);
        
        $result = $auth->resetPassword($email);
        
        if ($result['success']) {
            $success = $result['message'];
        } else {
            $error = $result['message'];
        }
    }
}

// Redirect if already logged in
if (is_logged_in()) {
    header('Location: ../index.php');
    exit();
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login - Ola Store Electronics</title>
    <meta name="description" content="Sign in to your Ola Store Electronics account to access your orders, wishlist, and personalized experience.">
    
    <!-- CSS -->
    <link rel="stylesheet" href="../assets/css/style.css">
    
    <!-- Font Awesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
</head>
<body class="auth-page">
    <!-- Header -->
    <header class="header">
        <nav class="navbar">
            <div class="nav-container">
                <div class="nav-brand">
                    <a href="../index.php" class="logo">
                        <i class="fas fa-bolt"></i>
                        <span>Ola Store</span>
                    </a>
                </div>
                
                <div class="nav-menu">
                    <ul class="nav-list">
                        <li class="nav-item">
                            <a href="../index.php" class="nav-link">Home</a>
                        </li>
                        <li class="nav-item">
                            <a href="store.php" class="nav-link">Store</a>
                        </li>
                        <li class="nav-item">
                            <a href="../index.php#categories" class="nav-link">Categories</a>
                        </li>
                        <li class="nav-item">
                            <a href="../index.php#about" class="nav-link">About</a>
                        </li>
                        <li class="nav-item">
                            <a href="../index.php#contact" class="nav-link">Contact</a>
                        </li>
                    </ul>
                </div>
                
                <div class="nav-actions">
                    <a href="login.php" class="auth-btn active">Login</a>
                    <a href="register.php" class="auth-btn primary">Sign Up</a>
                </div>
            </div>
        </nav>
    </header>

    <!-- Main Content -->
    <main class="main">
        <div class="auth-container">
            <div class="auth-card">
                <div class="auth-header">
                    <div class="auth-logo">
                        <i class="fas fa-bolt"></i>
                    </div>
                    <h1 class="auth-title">Welcome Back</h1>
                    <p class="auth-subtitle">Sign in to your account to continue</p>
                </div>

                <!-- Error/Success Messages -->
                <?php if ($error): ?>
                    <div class="alert alert-error">
                        <i class="fas fa-exclamation-circle"></i>
                        <span><?php echo htmlspecialchars($error); ?></span>
                    </div>
                <?php endif; ?>

                <?php if ($success): ?>
                    <div class="alert alert-success">
                        <i class="fas fa-check-circle"></i>
                        <span><?php echo htmlspecialchars($success); ?></span>
                    </div>
                <?php endif; ?>

                <!-- Login Form -->
                <form class="auth-form" method="POST" data-validate>
                    <input type="hidden" name="csrf_token" value="<?php echo get_csrf_token(); ?>">
                    
                    <div class="form-group">
                        <label for="email" class="form-label">Email Address</label>
                        <div class="input-group">
                            <i class="input-icon fas fa-envelope"></i>
                            <input type="email" id="email" name="email" 
                                   class="form-input" required 
                                   value="<?php echo isset($_POST['email']) ? htmlspecialchars($_POST['email']) : ''; ?>"
                                   placeholder="Enter your email address">
                        </div>
                    </div>

                    <div class="form-group">
                        <label for="password" class="form-label">Password</label>
                        <div class="input-group">
                            <i class="input-icon fas fa-lock"></i>
                            <input type="password" id="password" name="password" 
                                   class="form-input" required 
                                   placeholder="Enter your password">
                            <button type="button" class="password-toggle" tabindex="-1">
                                <i class="fas fa-eye"></i>
                            </button>
                        </div>
                    </div>

                    <div class="form-options">
                        <label class="checkbox-label">
                            <input type="checkbox" name="remember" value="1">
                            <span class="checkmark"></span>
                            Remember me
                        </label>
                        <a href="#forgot-password" class="forgot-link">Forgot Password?</a>
                    </div>

                    <button type="submit" name="login" class="auth-btn-primary">
                        <span>Sign In</span>
                        <i class="fas fa-arrow-right"></i>
                    </button>
                </form>

                <!-- Social Login -->
                <div class="social-login">
                    <div class="divider">
                        <span>or continue with</span>
                    </div>
                    
                    <div class="social-buttons">
                        <button type="button" class="social-btn google">
                            <i class="fab fa-google"></i>
                            <span>Google</span>
                        </button>
                        <button type="button" class="social-btn facebook">
                            <i class="fab fa-facebook-f"></i>
                            <span>Facebook</span>
                        </button>
                        <button type="button" class="social-btn apple">
                            <i class="fab fa-apple"></i>
                            <span>Apple</span>
                        </button>
                    </div>
                </div>

                <!-- Sign Up Link -->
                <div class="auth-footer">
                    <p>Don't have an account? <a href="register.php">Sign up here</a></p>
                </div>
            </div>

            <!-- Forgot Password Modal -->
            <div id="forgot-password" class="modal">
                <div class="modal-content auth-modal">
                    <div class="modal-header">
                        <h3>Reset Password</h3>
                        <a href="#" class="close">&times;</a>
                    </div>
                    
                    <form class="auth-form" method="POST">
                        <input type="hidden" name="csrf_token" value="<?php echo get_csrf_token(); ?>">
                        
                        <div class="form-group">
                            <label for="reset-email" class="form-label">Email Address</label>
                            <div class="input-group">
                                <i class="input-icon fas fa-envelope"></i>
                                <input type="email" id="reset-email" name="email" 
                                       class="form-input" required 
                                       placeholder="Enter your email address">
                            </div>
                        </div>

                        <button type="submit" name="forgot_password" class="auth-btn-primary">
                            <span>Send Reset Link</span>
                            <i class="fas fa-paper-plane"></i>
                        </button>
                    </form>
                    
                    <div class="modal-footer">
                        <p>Remember your password? <a href="#login">Sign in here</a></p>
                    </div>
                </div>
            </div>
        </div>
    </main>

    <!-- Footer -->
    <footer class="footer">
        <div class="container">
            <div class="footer-content">
                <div class="footer-section">
                    <div class="footer-brand">
                        <i class="fas fa-bolt"></i>
                        <span>Ola Store</span>
                    </div>
                    <p class="footer-description">
                        Premium electronics store offering the latest smartphones, laptops, smartwatches, and accessories with exceptional quality and service.
                    </p>
                    <div class="social-links">
                        <a href="#" class="social-link"><i class="fab fa-facebook"></i></a>
                        <a href="#" class="social-link"><i class="fab fa-twitter"></i></a>
                        <a href="#" class="social-link"><i class="fab fa-instagram"></i></a>
                        <a href="#" class="social-link"><i class="fab fa-youtube"></i></a>
                    </div>
                </div>
                
                <div class="footer-section">
                    <h3 class="footer-title">Quick Links</h3>
                    <ul class="footer-links">
                        <li><a href="../index.php">Home</a></li>
                        <li><a href="store.php">Store</a></li>
                        <li><a href="../index.php#categories">Categories</a></li>
                        <li><a href="../index.php#about">About Us</a></li>
                        <li><a href="../index.php#contact">Contact</a></li>
                    </ul>
                </div>
                
                <div class="footer-section">
                    <h3 class="footer-title">Customer Service</h3>
                    <ul class="footer-links">
                        <li><a href="help.php">Help Center</a></li>
                        <li><a href="shipping.php">Shipping Info</a></li>
                        <li><a href="returns.php">Returns</a></li>
                        <li><a href="warranty.php">Warranty</a></li>
                        <li><a href="support.php">Support</a></li>
                    </ul>
                </div>
                
                <div class="footer-section">
                    <h3 class="footer-title">Contact Info</h3>
                    <div class="contact-info">
                        <p><i class="fas fa-map-marker-alt"></i> 123 Tech Street, Digital City, DC 12345</p>
                        <p><i class="fas fa-phone"></i> +1 (555) 123-4567</p>
                        <p><i class="fas fa-envelope"></i> info@olastore.com</p>
                    </div>
                </div>
            </div>
            
            <div class="footer-bottom">
                <div class="footer-bottom-content">
                    <p>&copy; <?php echo date('Y'); ?> Ola Store Electronics. All rights reserved.</p>
                    <div class="footer-bottom-links">
                        <a href="privacy.php">Privacy Policy</a>
                        <a href="terms.php">Terms of Service</a>
                        <a href="sitemap.php">Sitemap</a>
                    </div>
                </div>
            </div>
        </div>
    </footer>

    <!-- JavaScript -->
    <script src="../assets/js/main.js"></script>
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            // Password toggle functionality
            setupPasswordToggle();
            
            // Modal functionality
            setupModals();
            
            // Form validation
            setupFormValidation();
        });

        function setupPasswordToggle() {
            const passwordToggles = document.querySelectorAll('.password-toggle');
            
            passwordToggles.forEach(toggle => {
                toggle.addEventListener('click', function() {
                    const input = this.previousElementSibling;
                    const icon = this.querySelector('i');
                    
                    if (input.type === 'password') {
                        input.type = 'text';
                        icon.className = 'fas fa-eye-slash';
                    } else {
                        input.type = 'password';
                        icon.className = 'fas fa-eye';
                    }
                });
            });
        }

        function setupModals() {
            // Forgot password modal
            const forgotLinks = document.querySelectorAll('a[href="#forgot-password"]');
            const forgotModal = document.getElementById('forgot-password');
            const closeBtn = forgotModal.querySelector('.close');
            
            forgotLinks.forEach(link => {
                link.addEventListener('click', function(e) {
                    e.preventDefault();
                    forgotModal.style.display = 'block';
                    document.body.style.overflow = 'hidden';
                });
            });
            
            closeBtn.addEventListener('click', function(e) {
                e.preventDefault();
                forgotModal.style.display = 'none';
                document.body.style.overflow = '';
            });
            
            // Close modal on backdrop click
            forgotModal.addEventListener('click', function(e) {
                if (e.target === this) {
                    this.style.display = 'none';
                    document.body.style.overflow = '';
                }
            });
            
            // Close modal on escape key
            document.addEventListener('keydown', function(e) {
                if (e.key === 'Escape' && forgotModal.style.display === 'block') {
                    forgotModal.style.display = 'none';
                    document.body.style.overflow = '';
                }
            });
        }

        function setupFormValidation() {
            const forms = document.querySelectorAll('.auth-form');
            
            forms.forEach(form => {
                form.addEventListener('submit', function(e) {
                    if (!validateForm(this)) {
                        e.preventDefault();
                    }
                });
                
                // Real-time validation
                const inputs = form.querySelectorAll('input[required]');
                inputs.forEach(input => {
                    input.addEventListener('blur', function() {
                        validateField(this);
                    });
                    
                    input.addEventListener('input', function() {
                        clearFieldError(this);
                    });
                });
            });
        }

        function validateForm(form) {
            let isValid = true;
            const requiredFields = form.querySelectorAll('input[required]');
            
            requiredFields.forEach(field => {
                if (!validateField(field)) {
                    isValid = false;
                }
            });
            
            return isValid;
        }

        function validateField(field) {
            const value = field.value.trim();
            const type = field.type;
            let isValid = true;

            // Remove existing error styling
            field.classList.remove('error');
            clearFieldError(field);

            // Required field validation
            if (field.hasAttribute('required') && !value) {
                showFieldError(field, 'This field is required');
                isValid = false;
            }

            // Email validation
            if (type === 'email' && value && !isValidEmail(value)) {
                showFieldError(field, 'Please enter a valid email address');
                isValid = false;
            }

            return isValid;
        }

        function showFieldError(field, message) {
            field.classList.add('error');
            
            const errorDiv = document.createElement('div');
            errorDiv.className = 'field-error';
            errorDiv.textContent = message;
            
            field.parentNode.appendChild(errorDiv);
        }

        function clearFieldError(field) {
            const errorDiv = field.parentNode.querySelector('.field-error');
            if (errorDiv) {
                errorDiv.remove();
            }
        }

        function isValidEmail(email) {
            const emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
            return emailRegex.test(email);
        }

        // Social login buttons (placeholder functionality)
        document.querySelectorAll('.social-btn').forEach(btn => {
            btn.addEventListener('click', function() {
                const platform = this.classList.contains('google') ? 'Google' : 
                               this.classList.contains('facebook') ? 'Facebook' : 'Apple';
                
                showNotification(`${platform} login is not implemented yet. Please use email/password.`, 'info');
            });
        });
    </script>
</body>
</html>