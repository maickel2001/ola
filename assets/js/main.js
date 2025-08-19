/**
 * Ola Store Electronics - Main JavaScript
 * Interactive functionality and utilities
 */

class OlaStore {
    constructor() {
        this.init();
    }

    init() {
        this.setupEventListeners();
        this.initializeComponents();
        this.setupAnimations();
    }

    setupEventListeners() {
        // Mobile menu toggle
        const mobileMenuBtn = document.querySelector('.mobile-menu-btn');
        const navMenu = document.querySelector('.nav-menu');
        
        if (mobileMenuBtn && navMenu) {
            mobileMenuBtn.addEventListener('click', () => {
                navMenu.classList.toggle('active');
                mobileMenuBtn.classList.toggle('active');
            });
        }

        // User dropdown toggle
        const userBtn = document.querySelector('.user-btn');
        const userDropdown = document.querySelector('.user-dropdown');
        
        if (userBtn && userDropdown) {
            userBtn.addEventListener('click', (e) => {
                e.stopPropagation();
                userDropdown.classList.toggle('active');
            });
            
            document.addEventListener('click', () => {
                userDropdown.classList.remove('active');
            });
        }

        // Search functionality
        this.setupSearch();

        // Cart functionality
        this.setupCart();

        // Product interactions
        this.setupProductInteractions();

        // Smooth scrolling for anchor links
        this.setupSmoothScrolling();

        // Form validation
        this.setupFormValidation();

        // Lazy loading for images
        this.setupLazyLoading();

        // Intersection Observer for animations
        this.setupIntersectionObserver();
    }

    initializeComponents() {
        // Initialize tooltips
        this.initializeTooltips();

        // Initialize modals
        this.initializeModals();

        // Initialize sliders
        this.initializeSliders();

        // Initialize counters
        this.initializeCounters();
    }

    setupSearch() {
        const searchInput = document.querySelector('.search-input');
        const searchForm = document.querySelector('.search-form');

        if (searchInput) {
            // Real-time search suggestions
            searchInput.addEventListener('input', this.debounce((e) => {
                const query = e.target.value.trim();
                if (query.length >= 2) {
                    this.showSearchSuggestions(query);
                } else {
                    this.hideSearchSuggestions();
                }
            }, 300));

            // Search form submission
            if (searchForm) {
                searchForm.addEventListener('submit', (e) => {
                    const query = searchInput.value.trim();
                    if (!query) {
                        e.preventDefault();
                        this.showNotification('Please enter a search term', 'warning');
                    }
                });
            }
        }
    }

    setupCart() {
        // Add to cart buttons
        const addToCartBtns = document.querySelectorAll('.add-to-cart-btn');
        addToCartBtns.forEach(btn => {
            btn.addEventListener('click', (e) => {
                e.preventDefault();
                const productId = btn.dataset.productId;
                const quantity = btn.dataset.quantity || 1;
                this.addToCart(productId, quantity);
            });
        });

        // Cart quantity updates
        const quantityInputs = document.querySelectorAll('.quantity-input');
        quantityInputs.forEach(input => {
            input.addEventListener('change', (e) => {
                const productId = e.target.dataset.productId;
                const quantity = parseInt(e.target.value);
                this.updateCartQuantity(productId, quantity);
            });
        });

        // Remove from cart
        const removeFromCartBtns = document.querySelectorAll('.remove-from-cart-btn');
        removeFromCartBtns.forEach(btn => {
            btn.addEventListener('click', (e) => {
                e.preventDefault();
                const productId = btn.dataset.productId;
                this.removeFromCart(productId);
            });
        });
    }

    setupProductInteractions() {
        // Quick view functionality
        const quickViewBtns = document.querySelectorAll('.quick-view-btn');
        quickViewBtns.forEach(btn => {
            btn.addEventListener('click', (e) => {
                e.preventDefault();
                const productId = btn.dataset.productId;
                this.showQuickView(productId);
            });
        });

        // Wishlist functionality
        const wishlistBtns = document.querySelectorAll('.wishlist-btn');
        wishlistBtns.forEach(btn => {
            btn.addEventListener('click', (e) => {
                e.preventDefault();
                const productId = btn.dataset.productId;
                this.toggleWishlist(productId);
            });
        });

        // Product image zoom
        const productImages = document.querySelectorAll('.product-img');
        productImages.forEach(img => {
            img.addEventListener('mouseenter', this.handleImageZoom);
            img.addEventListener('mouseleave', this.handleImageZoomOut);
        });
    }

    setupSmoothScrolling() {
        const anchorLinks = document.querySelectorAll('a[href^="#"]');
        anchorLinks.forEach(link => {
            link.addEventListener('click', (e) => {
                const href = link.getAttribute('href');
                if (href !== '#') {
                    e.preventDefault();
                    const target = document.querySelector(href);
                    if (target) {
                        target.scrollIntoView({
                            behavior: 'smooth',
                            block: 'start'
                        });
                    }
                }
            });
        });
    }

    setupFormValidation() {
        const forms = document.querySelectorAll('form[data-validate]');
        forms.forEach(form => {
            form.addEventListener('submit', (e) => {
                if (!this.validateForm(form)) {
                    e.preventDefault();
                }
            });
        });
    }

    setupLazyLoading() {
        if ('IntersectionObserver' in window) {
            const imageObserver = new IntersectionObserver((entries, observer) => {
                entries.forEach(entry => {
                    if (entry.isIntersecting) {
                        const img = entry.target;
                        img.src = img.dataset.src;
                        img.classList.remove('lazy');
                        imageObserver.unobserve(img);
                    }
                });
            });

            const lazyImages = document.querySelectorAll('img[data-src]');
            lazyImages.forEach(img => imageObserver.observe(img));
        }
    }

    setupIntersectionObserver() {
        if ('IntersectionObserver' in window) {
            const animationObserver = new IntersectionObserver((entries) => {
                entries.forEach(entry => {
                    if (entry.isIntersecting) {
                        entry.target.classList.add('animate');
                    }
                });
            }, {
                threshold: 0.1,
                rootMargin: '0px 0px -50px 0px'
            });

            const animatedElements = document.querySelectorAll('[data-animate]');
            animatedElements.forEach(el => animationObserver.observe(el));
        }
    }

    setupAnimations() {
        // Parallax effect for hero section
        this.setupParallax();

        // Smooth reveal animations
        this.setupRevealAnimations();

        // Loading animations
        this.setupLoadingAnimations();
    }

    // Search functionality
    async showSearchSuggestions(query) {
        try {
            const response = await fetch(`ajax/search-suggestions.php?q=${encodeURIComponent(query)}`);
            const suggestions = await response.json();
            
            if (suggestions.length > 0) {
                this.displaySearchSuggestions(suggestions);
            }
        } catch (error) {
            console.error('Error fetching search suggestions:', error);
        }
    }

    hideSearchSuggestions() {
        const suggestionsContainer = document.querySelector('.search-suggestions');
        if (suggestionsContainer) {
            suggestionsContainer.remove();
        }
    }

    displaySearchSuggestions(suggestions) {
        this.hideSearchSuggestions();

        const searchContainer = document.querySelector('.search-container');
        const suggestionsContainer = document.createElement('div');
        suggestionsContainer.className = 'search-suggestions';

        suggestions.forEach(suggestion => {
            const suggestionItem = document.createElement('div');
            suggestionItem.className = 'search-suggestion-item';
            suggestionItem.innerHTML = `
                <a href="pages/product.php?slug=${suggestion.slug}">
                    <img src="${suggestion.image || 'assets/images/products/placeholder.jpg'}" alt="${suggestion.name}">
                    <div class="suggestion-info">
                        <h4>${suggestion.name}</h4>
                        <p>${suggestion.price}</p>
                    </div>
                </a>
            `;
            suggestionsContainer.appendChild(suggestionItem);
        });

        searchContainer.appendChild(suggestionsContainer);
    }

    // Cart functionality
    async addToCart(productId, quantity = 1) {
        try {
            const response = await fetch('ajax/add-to-cart.php', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                },
                body: JSON.stringify({
                    product_id: productId,
                    quantity: quantity,
                    csrf_token: this.getCSRFToken()
                })
            });

            const data = await response.json();
            
            if (data.success) {
                this.updateCartCount(data.cart_count);
                this.showNotification('Product added to cart!', 'success');
                this.updateCartDisplay();
            } else {
                this.showNotification(data.message || 'Failed to add product to cart', 'error');
            }
        } catch (error) {
            console.error('Error adding to cart:', error);
            this.showNotification('An error occurred. Please try again.', 'error');
        }
    }

    async updateCartQuantity(productId, quantity) {
        try {
            const response = await fetch('ajax/update-cart.php', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                },
                body: JSON.stringify({
                    product_id: productId,
                    quantity: quantity,
                    csrf_token: this.getCSRFToken()
                })
            });

            const data = await response.json();
            
            if (data.success) {
                this.updateCartDisplay();
                this.showNotification('Cart updated successfully!', 'success');
            } else {
                this.showNotification(data.message || 'Failed to update cart', 'error');
            }
        } catch (error) {
            console.error('Error updating cart:', error);
            this.showNotification('An error occurred. Please try again.', 'error');
        }
    }

    async removeFromCart(productId) {
        try {
            const response = await fetch('ajax/remove-from-cart.php', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                },
                body: JSON.stringify({
                    product_id: productId,
                    csrf_token: this.getCSRFToken()
                })
            });

            const data = await response.json();
            
            if (data.success) {
                this.updateCartCount(data.cart_count);
                this.updateCartDisplay();
                this.showNotification('Product removed from cart!', 'success');
            } else {
                this.showNotification(data.message || 'Failed to remove product', 'error');
            }
        } catch (error) {
            console.error('Error removing from cart:', error);
            this.showNotification('An error occurred. Please try again.', 'error');
        }
    }

    updateCartCount(count) {
        const cartCount = document.querySelector('.cart-count');
        if (cartCount) {
            cartCount.textContent = count;
        } else {
            const cartBtn = document.querySelector('.cart-btn');
            if (cartBtn && count > 0) {
                const newCartCount = document.createElement('span');
                newCartCount.className = 'cart-count';
                newCartCount.textContent = count;
                cartBtn.appendChild(newCartCount);
            }
        }
    }

    async updateCartDisplay() {
        const cartContainer = document.querySelector('.cart-items');
        if (cartContainer) {
            try {
                const response = await fetch('ajax/get-cart.php');
                const cartHtml = await response.text();
                cartContainer.innerHTML = cartHtml;
            } catch (error) {
                console.error('Error updating cart display:', error);
            }
        }
    }

    // Product interactions
    async showQuickView(productId) {
        try {
            const response = await fetch(`ajax/quick-view.php?product_id=${productId}`);
            const quickViewHtml = await response.text();
            
            const modal = document.getElementById('quickViewModal');
            const modalContent = document.getElementById('quickViewContent');
            
            if (modal && modalContent) {
                modalContent.innerHTML = quickViewHtml;
                modal.style.display = 'block';
                
                // Setup quick view event listeners
                this.setupQuickViewEvents();
            }
        } catch (error) {
            console.error('Error loading quick view:', error);
            this.showNotification('Failed to load product details', 'error');
        }
    }

    async toggleWishlist(productId) {
        try {
            const response = await fetch('ajax/toggle-wishlist.php', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                },
                body: JSON.stringify({
                    product_id: productId,
                    csrf_token: this.getCSRFToken()
                })
            });

            const data = await response.json();
            
            if (data.success) {
                const wishlistBtn = document.querySelector(`[data-product-id="${productId}"].wishlist-btn`);
                if (wishlistBtn) {
                    wishlistBtn.classList.toggle('active');
                    const icon = wishlistBtn.querySelector('i');
                    if (icon) {
                        icon.className = data.in_wishlist ? 'fas fa-heart' : 'far fa-heart';
                    }
                }
                
                this.showNotification(data.message, 'success');
            } else {
                this.showNotification(data.message || 'Failed to update wishlist', 'error');
            }
        } catch (error) {
            console.error('Error toggling wishlist:', error);
            this.showNotification('An error occurred. Please try again.', 'error');
        }
    }

    // Image zoom functionality
    handleImageZoom(e) {
        const img = e.target;
        const container = img.parentElement;
        
        container.style.overflow = 'hidden';
        img.style.transform = 'scale(1.5)';
        img.style.transition = 'transform 0.3s ease';
    }

    handleImageZoomOut(e) {
        const img = e.target;
        img.style.transform = 'scale(1)';
    }

    // Form validation
    validateForm(form) {
        let isValid = true;
        const requiredFields = form.querySelectorAll('[required]');
        
        requiredFields.forEach(field => {
            if (!this.validateField(field)) {
                isValid = false;
            }
        });

        return isValid;
    }

    validateField(field) {
        const value = field.value.trim();
        const type = field.type;
        let isValid = true;

        // Remove existing error styling
        field.classList.remove('error');
        this.removeFieldError(field);

        // Required field validation
        if (field.hasAttribute('required') && !value) {
            this.showFieldError(field, 'This field is required');
            isValid = false;
        }

        // Email validation
        if (type === 'email' && value && !this.isValidEmail(value)) {
            this.showFieldError(field, 'Please enter a valid email address');
            isValid = false;
        }

        // Password validation
        if (type === 'password' && value && value.length < 8) {
            this.showFieldError(field, 'Password must be at least 8 characters long');
            isValid = false;
        }

        // Phone validation
        if (field.name === 'phone' && value && !this.isValidPhone(value)) {
            this.showFieldError(field, 'Please enter a valid phone number');
            isValid = false;
        }

        return isValid;
    }

    showFieldError(field, message) {
        field.classList.add('error');
        
        const errorDiv = document.createElement('div');
        errorDiv.className = 'field-error';
        errorDiv.textContent = message;
        
        field.parentNode.appendChild(errorDiv);
    }

    removeFieldError(field) {
        const errorDiv = field.parentNode.querySelector('.field-error');
        if (errorDiv) {
            errorDiv.remove();
        }
    }

    isValidEmail(email) {
        const emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
        return emailRegex.test(email);
    }

    isValidPhone(phone) {
        const phoneRegex = /^[\+]?[1-9][\d]{0,15}$/;
        return phoneRegex.test(phone.replace(/[\s\-\(\)]/g, ''));
    }

    // Utility functions
    debounce(func, wait) {
        let timeout;
        return function executedFunction(...args) {
            const later = () => {
                clearTimeout(timeout);
                func(...args);
            };
            clearTimeout(timeout);
            timeout = setTimeout(later, wait);
        };
    }

    getCSRFToken() {
        const tokenInput = document.querySelector('input[name="csrf_token"]');
        return tokenInput ? tokenInput.value : '';
    }

    showNotification(message, type = 'info') {
        const notification = document.createElement('div');
        notification.className = `notification notification-${type}`;
        notification.textContent = message;
        
        document.body.appendChild(notification);
        
        // Trigger reflow for animation
        notification.offsetHeight;
        
        notification.classList.add('show');
        
        setTimeout(() => {
            notification.classList.remove('show');
            setTimeout(() => {
                if (document.body.contains(notification)) {
                    document.body.removeChild(notification);
                }
            }, 300);
        }, 3000);
    }

    // Animation functions
    setupParallax() {
        window.addEventListener('scroll', () => {
            const scrolled = window.pageYOffset;
            const parallaxElements = document.querySelectorAll('[data-parallax]');
            
            parallaxElements.forEach(element => {
                const speed = element.dataset.parallax || 0.5;
                const yPos = -(scrolled * speed);
                element.style.transform = `translateY(${yPos}px)`;
            });
        });
    }

    setupRevealAnimations() {
        const revealElements = document.querySelectorAll('[data-reveal]');
        
        revealElements.forEach(element => {
            element.style.opacity = '0';
            element.style.transform = 'translateY(30px)';
            element.style.transition = 'opacity 0.6s ease, transform 0.6s ease';
        });
    }

    setupLoadingAnimations() {
        const loadingElements = document.querySelectorAll('[data-loading]');
        
        loadingElements.forEach(element => {
            element.classList.add('loading');
            
            // Simulate loading completion
            setTimeout(() => {
                element.classList.remove('loading');
            }, 2000);
        });
    }

    // Component initialization
    initializeTooltips() {
        const tooltipElements = document.querySelectorAll('[data-tooltip]');
        
        tooltipElements.forEach(element => {
            element.addEventListener('mouseenter', (e) => {
                this.showTooltip(e.target, e.target.dataset.tooltip);
            });
            
            element.addEventListener('mouseleave', () => {
                this.hideTooltip();
            });
        });
    }

    showTooltip(element, text) {
        const tooltip = document.createElement('div');
        tooltip.className = 'tooltip';
        tooltip.textContent = text;
        
        document.body.appendChild(tooltip);
        
        const rect = element.getBoundingClientRect();
        tooltip.style.left = rect.left + (rect.width / 2) - (tooltip.offsetWidth / 2) + 'px';
        tooltip.style.top = rect.top - tooltip.offsetHeight - 10 + 'px';
        
        element.tooltip = tooltip;
    }

    hideTooltip() {
        const tooltips = document.querySelectorAll('.tooltip');
        tooltips.forEach(tooltip => tooltip.remove());
    }

    initializeModals() {
        const modalTriggers = document.querySelectorAll('[data-modal]');
        
        modalTriggers.forEach(trigger => {
            trigger.addEventListener('click', (e) => {
                e.preventDefault();
                const modalId = trigger.dataset.modal;
                this.openModal(modalId);
            });
        });

        // Close modal on backdrop click
        document.addEventListener('click', (e) => {
            if (e.target.classList.contains('modal')) {
                this.closeModal(e.target.id);
            }
        });
    }

    openModal(modalId) {
        const modal = document.getElementById(modalId);
        if (modal) {
            modal.style.display = 'block';
            document.body.style.overflow = 'hidden';
        }
    }

    closeModal(modalId) {
        const modal = document.getElementById(modalId);
        if (modal) {
            modal.style.display = 'none';
            document.body.style.overflow = '';
        }
    }

    initializeSliders() {
        const sliders = document.querySelectorAll('.slider');
        
        sliders.forEach(slider => {
            const slides = slider.querySelectorAll('.slide');
            const prevBtn = slider.querySelector('.slider-prev');
            const nextBtn = slider.querySelector('.slider-next');
            let currentSlide = 0;

            if (slides.length > 1) {
                this.showSlide(slider, currentSlide);
                
                if (prevBtn) {
                    prevBtn.addEventListener('click', () => {
                        currentSlide = (currentSlide - 1 + slides.length) % slides.length;
                        this.showSlide(slider, currentSlide);
                    });
                }
                
                if (nextBtn) {
                    nextBtn.addEventListener('click', () => {
                        currentSlide = (currentSlide + 1) % slides.length;
                        this.showSlide(slider, currentSlide);
                    });
                }
            }
        });
    }

    showSlide(slider, index) {
        const slides = slider.querySelectorAll('.slide');
        slides.forEach((slide, i) => {
            slide.style.display = i === index ? 'block' : 'none';
        });
    }

    initializeCounters() {
        const counters = document.querySelectorAll('[data-counter]');
        
        counters.forEach(counter => {
            const target = parseInt(counter.dataset.counter);
            const duration = 2000; // 2 seconds
            const increment = target / (duration / 16); // 60fps
            let current = 0;
            
            const updateCounter = () => {
                current += increment;
                if (current < target) {
                    counter.textContent = Math.floor(current);
                    requestAnimationFrame(updateCounter);
                } else {
                    counter.textContent = target;
                }
            };
            
            updateCounter();
        });
    }

    // Quick view event setup
    setupQuickViewEvents() {
        const addToCartBtn = document.querySelector('#quickViewModal .add-to-cart-btn');
        if (addToCartBtn) {
            addToCartBtn.addEventListener('click', (e) => {
                e.preventDefault();
                const productId = addToCartBtn.dataset.productId;
                const quantity = document.querySelector('#quickViewModal .quantity-input')?.value || 1;
                this.addToCart(productId, quantity);
                this.closeModal('quickViewModal');
            });
        }
    }
}

// Initialize the application when DOM is loaded
document.addEventListener('DOMContentLoaded', () => {
    new OlaStore();
});

// Export for use in other modules
if (typeof module !== 'undefined' && module.exports) {
    module.exports = OlaStore;
}