    </div><!-- .main-wrapper -->

    <!-- Footer -->
    <footer class="footer">
        <div class="container">
            <div class="footer-content">
                <div class="footer-section">
                    <h3>Ola Store Electronics</h3>
                    <p>Premium electronics store with Apple-inspired design. Quality products, exceptional service.</p>
                    <div class="social-links">
                        <a href="#" class="social-link"><i class="fab fa-facebook"></i></a>
                        <a href="#" class="social-link"><i class="fab fa-twitter"></i></a>
                        <a href="#" class="social-link"><i class="fab fa-instagram"></i></a>
                        <a href="#" class="social-link"><i class="fab fa-youtube"></i></a>
                    </div>
                </div>
                
                <div class="footer-section">
                    <h4>Quick Links</h4>
                    <ul class="footer-links">
                        <li><a href="<?php echo getBaseUrl(); ?>index.php">Home</a></li>
                        <li><a href="<?php echo getBaseUrl(); ?>pages/store.php">Store</a></li>
                        <li><a href="<?php echo getBaseUrl(); ?>pages/about.php">About Us</a></li>
                        <li><a href="<?php echo getBaseUrl(); ?>pages/contact.php">Contact</a></li>
                    </ul>
                </div>
                
                <div class="footer-section">
                    <h4>Customer Service</h4>
                    <ul class="footer-links">
                        <li><a href="<?php echo getBaseUrl(); ?>pages/support.php">Support</a></li>
                        <li><a href="<?php echo getBaseUrl(); ?>pages/shipping.php">Shipping Info</a></li>
                        <li><a href="<?php echo getBaseUrl(); ?>pages/returns.php">Returns</a></li>
                        <li><a href="<?php echo getBaseUrl(); ?>pages/warranty.php">Warranty</a></li>
                    </ul>
                </div>
                
                <div class="footer-section">
                    <h4>Legal</h4>
                    <ul class="footer-links">
                        <li><a href="<?php echo getBaseUrl(); ?>pages/privacy.php">Privacy Policy</a></li>
                        <li><a href="<?php echo getBaseUrl(); ?>pages/terms.php">Terms of Service</a></li>
                        <li><a href="<?php echo getBaseUrl(); ?>pages/sitemap.php">Sitemap</a></li>
                    </ul>
                </div>
                
                <div class="footer-section">
                    <h4>Newsletter</h4>
                    <p>Subscribe to get updates on new products and special offers.</p>
                    <form class="newsletter-form" action="<?php echo getBaseUrl(); ?>newsletter-subscribe.php" method="POST">
                        <div class="newsletter-input-group">
                            <input type="email" name="email" placeholder="Enter your email" required class="newsletter-input">
                            <button type="submit" class="newsletter-btn">
                                <i class="fas fa-paper-plane"></i>
                            </button>
                        </div>
                    </form>
                </div>
            </div>
            
            <div class="footer-bottom">
                <div class="footer-bottom-content">
                    <p>&copy; <?php echo date('Y'); ?> Ola Store Electronics. All rights reserved.</p>
                    <div class="payment-methods">
                        <span class="payment-method"><i class="fab fa-cc-visa"></i></span>
                        <span class="payment-method"><i class="fab fa-cc-mastercard"></i></span>
                        <span class="payment-method"><i class="fab fa-cc-amex"></i></span>
                        <span class="payment-method"><i class="fab fa-cc-paypal"></i></span>
                    </div>
                </div>
            </div>
        </div>
    </footer>

    <!-- JavaScript -->
    <script src="<?php echo getBaseUrl(); ?>assets/js/main.js"></script>
    <?php if (isset($additional_js)): ?>
        <?php foreach ($additional_js as $js): ?>
        <script src="<?php echo getBaseUrl(); ?>assets/js/<?php echo $js; ?>"></script>
        <?php endforeach; ?>
    <?php endif; ?>
    
    <script>
        // Mobile menu functionality
        document.addEventListener('DOMContentLoaded', function() {
            const mobileMenuToggle = document.getElementById('mobileMenuToggle');
            const mobileMenu = document.getElementById('mobileMenu');
            const mobileMenuClose = document.getElementById('mobileMenuClose');
            
            if (mobileMenuToggle && mobileMenu) {
                mobileMenuToggle.addEventListener('click', function() {
                    mobileMenu.classList.add('active');
                    document.body.style.overflow = 'hidden';
                });
            }
            
            if (mobileMenuClose && mobileMenu) {
                mobileMenuClose.addEventListener('click', function() {
                    mobileMenu.classList.remove('active');
                    document.body.style.overflow = '';
                });
            }
            
            // Close mobile menu on outside click
            if (mobileMenu) {
                mobileMenu.addEventListener('click', function(e) {
                    if (e.target === mobileMenu) {
                        mobileMenu.classList.remove('active');
                        document.body.style.overflow = '';
                    }
                });
            }
            
            // Close mobile menu on escape key
            document.addEventListener('keydown', function(e) {
                if (e.key === 'Escape' && mobileMenu && mobileMenu.classList.contains('active')) {
                    mobileMenu.classList.remove('active');
                    document.body.style.overflow = '';
                }
            });
        });
        
        // Global notification system
        window.showNotification = function(message, type = 'info') {
            const notification = document.createElement('div');
            notification.className = `notification notification-${type}`;
            notification.style.cssText = `
                position: fixed;
                top: 20px;
                right: 20px;
                padding: 1rem 1.5rem;
                border-radius: 8px;
                color: white;
                font-weight: 500;
                z-index: 10000;
                max-width: 300px;
                box-shadow: 0 4px 20px rgba(0, 0, 0, 0.15);
                transform: translateX(100%);
                transition: transform 0.3s ease;
            `;
            
            // Set background color based on type
            switch (type) {
                case 'success':
                    notification.style.background = '#10b981';
                    break;
                case 'error':
                    notification.style.background = '#ef4444';
                    break;
                case 'warning':
                    notification.style.background = '#f59e0b';
                    break;
                default:
                    notification.style.background = '#3b82f6';
            }
            
            notification.textContent = message;
            document.body.appendChild(notification);
            
            // Animate in
            setTimeout(() => {
                notification.style.transform = 'translateX(0)';
            }, 100);
            
            // Auto remove after 5 seconds
            setTimeout(() => {
                notification.style.transform = 'translateX(100%)';
                setTimeout(() => {
                    if (notification.parentNode) {
                        notification.parentNode.removeChild(notification);
                    }
                }, 300);
            }, 5000);
        };
    </script>
</body>
</html>