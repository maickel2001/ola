/**
 * Product Detail Page JavaScript
 * Handles image zoom, tab switching, quantity controls, and review functionality
 */

document.addEventListener('DOMContentLoaded', function() {
    // Initialize all product page functionality
    initProductPage();
});

function initProductPage() {
    initImageGallery();
    initTabSwitching();
    initQuantityControls();
    initReviewModal();
    initImageZoom();
}

/**
 * Initialize image gallery functionality
 */
function initImageGallery() {
    const mainImage = document.getElementById('mainImage');
    const thumbnails = document.querySelectorAll('.thumbnail');
    
    if (!mainImage || thumbnails.length === 0) return;
    
    thumbnails.forEach(thumbnail => {
        thumbnail.addEventListener('click', function() {
            // Remove active class from all thumbnails
            thumbnails.forEach(t => t.classList.remove('active'));
            
            // Add active class to clicked thumbnail
            this.classList.add('active');
            
            // Update main image
            const newImageSrc = this.getAttribute('data-image');
            mainImage.src = newImageSrc;
            
            // Add smooth transition effect
            mainImage.style.opacity = '0.7';
            setTimeout(() => {
                mainImage.style.opacity = '1';
            }, 150);
        });
    });
}

/**
 * Initialize tab switching functionality
 */
function initTabSwitching() {
    const tabButtons = document.querySelectorAll('.tab-btn');
    const tabPanes = document.querySelectorAll('.tab-pane');
    
    if (tabButtons.length === 0 || tabPanes.length === 0) return;
    
    tabButtons.forEach(button => {
        button.addEventListener('click', function() {
            const targetTab = this.getAttribute('data-tab');
            
            // Remove active class from all buttons and panes
            tabButtons.forEach(btn => btn.classList.remove('active'));
            tabPanes.forEach(pane => pane.classList.remove('active'));
            
            // Add active class to clicked button and corresponding pane
            this.classList.add('active');
            const targetPane = document.getElementById(targetTab);
            if (targetPane) {
                targetPane.classList.add('active');
                
                // Smooth scroll to reviews section if reviews tab is clicked
                if (targetTab === 'reviews') {
                    setTimeout(() => {
                        targetPane.scrollIntoView({ 
                            behavior: 'smooth', 
                            block: 'start' 
                        });
                    }, 100);
                }
            }
        });
    });
}

/**
 * Initialize quantity controls
 */
function initQuantityControls() {
    const quantityInput = document.getElementById('quantity');
    const decreaseBtn = document.querySelector('[data-action="decrease"]');
    const increaseBtn = document.querySelector('[data-action="increase"]');
    
    if (!quantityInput || !decreaseBtn || !increaseBtn) return;
    
    const min = parseInt(quantityInput.getAttribute('min')) || 1;
    const max = parseInt(quantityInput.getAttribute('max')) || 999;
    
    // Decrease quantity
    decreaseBtn.addEventListener('click', function() {
        let currentValue = parseInt(quantityInput.value) || min;
        if (currentValue > min) {
            quantityInput.value = currentValue - 1;
            updateQuantityButtons();
        }
    });
    
    // Increase quantity
    increaseBtn.addEventListener('click', function() {
        let currentValue = parseInt(quantityInput.value) || min;
        if (currentValue < max) {
            quantityInput.value = currentValue + 1;
            updateQuantityButtons();
        }
    });
    
    // Handle manual input
    quantityInput.addEventListener('input', function() {
        let value = parseInt(this.value) || min;
        value = Math.max(min, Math.min(value, max));
        this.value = value;
        updateQuantityButtons();
    });
    
    // Update button states on page load
    updateQuantityButtons();
    
    function updateQuantityButtons() {
        const currentValue = parseInt(quantityInput.value) || min;
        decreaseBtn.disabled = currentValue <= min;
        increaseBtn.disabled = currentValue >= max;
        
        // Visual feedback
        decreaseBtn.style.opacity = currentValue <= min ? '0.5' : '1';
        increaseBtn.style.opacity = currentValue >= max ? '0.5' : '1';
    }
}

/**
 * Initialize review modal functionality
 */
function initReviewModal() {
    const writeReviewBtn = document.getElementById('writeReviewBtn');
    const reviewModal = document.getElementById('reviewModal');
    const closeReviewModal = document.getElementById('closeReviewModal');
    const cancelReview = document.getElementById('cancelReview');
    const reviewForm = document.getElementById('reviewForm');
    
    if (!writeReviewBtn || !reviewModal) return;
    
    // Open modal
    writeReviewBtn.addEventListener('click', function() {
        reviewModal.classList.add('active');
        document.body.style.overflow = 'hidden';
        
        // Focus on first input
        const firstInput = reviewForm.querySelector('input, textarea');
        if (firstInput) {
            setTimeout(() => firstInput.focus(), 100);
        }
    });
    
    // Close modal functions
    function closeModal() {
        reviewModal.classList.remove('active');
        document.body.style.overflow = '';
        
        // Reset form
        if (reviewForm) {
            reviewForm.reset();
        }
    }
    
    // Close modal on various events
    if (closeReviewModal) {
        closeReviewModal.addEventListener('click', closeModal);
    }
    
    if (cancelReview) {
        cancelReview.addEventListener('click', closeModal);
    }
    
    // Close modal on outside click
    reviewModal.addEventListener('click', function(e) {
        if (e.target === reviewModal) {
            closeModal();
        }
    });
    
    // Close modal on escape key
    document.addEventListener('keydown', function(e) {
        if (e.key === 'Escape' && reviewModal.classList.contains('active')) {
            closeModal();
        }
    });
    
    // Handle form submission
    if (reviewForm) {
        reviewForm.addEventListener('submit', function(e) {
            e.preventDefault();
            submitReview(this);
        });
    }
}

/**
 * Submit review form
 */
function submitReview(form) {
    const formData = new FormData(form);
    const rating = formData.get('rating');
    const title = formData.get('title');
    const comment = formData.get('comment');
    
    // Basic validation
    if (!rating || !title || !comment) {
        showNotification('Please fill in all fields', 'error');
        return;
    }
    
    if (title.length < 3) {
        showNotification('Review title must be at least 3 characters long', 'error');
        return;
    }
    
    if (comment.length < 10) {
        showNotification('Review comment must be at least 10 characters long', 'error');
        return;
    }
    
    // Show loading state
    const submitBtn = form.querySelector('button[type="submit"]');
    const originalText = submitBtn.textContent;
    submitBtn.textContent = 'Submitting...';
    submitBtn.disabled = true;
    
    // Simulate API call (replace with actual AJAX call)
    setTimeout(() => {
        // Reset form and close modal
        form.reset();
        document.getElementById('reviewModal').classList.remove('active');
        document.body.style.overflow = '';
        
        // Show success message
        showNotification('Review submitted successfully!', 'success');
        
        // Reset button
        submitBtn.textContent = originalText;
        submitBtn.disabled = false;
        
        // Refresh reviews section (you might want to reload the page or update via AJAX)
        location.reload();
    }, 1500);
}

/**
 * Initialize image zoom functionality
 */
function initImageZoom() {
    const mainImage = document.getElementById('mainImage');
    const zoomLens = document.getElementById('zoomLens');
    
    if (!mainImage || !zoomLens) return;
    
    let isZoomEnabled = false;
    
    // Toggle zoom on double click
    mainImage.addEventListener('dblclick', function() {
        isZoomEnabled = !isZoomEnabled;
        zoomLens.style.display = isZoomEnabled ? 'block' : 'none';
        
        if (isZoomEnabled) {
            showNotification('Image zoom enabled. Move mouse over image to zoom.', 'info');
        } else {
            showNotification('Image zoom disabled.', 'info');
        }
    });
    
    // Handle mouse move for zoom
    mainImage.addEventListener('mousemove', function(e) {
        if (!isZoomEnabled) return;
        
        const rect = this.getBoundingClientRect();
        const x = e.clientX - rect.left;
        const y = e.clientY - rect.top;
        
        // Calculate lens position
        const lensSize = 100;
        const lensX = x - lensSize / 2;
        const lensY = y - lensSize / 2;
        
        // Constrain lens within image bounds
        const maxX = rect.width - lensSize;
        const maxY = rect.height - lensSize;
        
        zoomLens.style.left = Math.max(0, Math.min(lensX, maxX)) + 'px';
        zoomLens.style.top = Math.max(0, Math.min(lensY, maxY)) + 'px';
    });
    
    // Hide lens when mouse leaves image
    mainImage.addEventListener('mouseleave', function() {
        if (isZoomEnabled) {
            zoomLens.style.display = 'none';
        }
    });
}

/**
 * Show notification message
 */
function showNotification(message, type = 'info') {
    // Check if notification system exists
    if (typeof window.showNotification === 'function') {
        window.showNotification(message, type);
        return;
    }
    
    // Fallback notification
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
}

/**
 * Add to cart functionality
 */
function addToCart(productId, quantity = 1) {
    // Show loading state
    const addToCartBtn = document.querySelector('button[name="add_to_cart"]');
    if (addToCartBtn) {
        const originalText = addToCartBtn.innerHTML;
        addToCartBtn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Adding...';
        addToCartBtn.disabled = true;
        
        // Simulate API call (replace with actual AJAX call)
        setTimeout(() => {
            // Reset button
            addToCartBtn.innerHTML = originalText;
            addToCartBtn.disabled = false;
            
            // Show success message
            showNotification('Product added to cart successfully!', 'success');
            
            // Update cart count if it exists
            updateCartCount();
        }, 1000);
    }
}

/**
 * Add to wishlist functionality
 */
function addToWishlist(productId) {
    // Show loading state
    const addToWishlistBtn = document.querySelector('button[name="add_to_wishlist"]');
    if (addToWishlistBtn) {
        const originalText = addToWishlistBtn.innerHTML;
        addToWishlistBtn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Adding...';
        addToWishlistBtn.disabled = true;
        
        // Simulate API call (replace with actual AJAX call)
        setTimeout(() => {
            // Reset button
            addToWishlistBtn.innerHTML = originalText;
            addToWishlistBtn.disabled = false;
            
            // Show success message
            showNotification('Product added to wishlist!', 'success');
        }, 1000);
    }
}

/**
 * Update cart count in header
 */
function updateCartCount() {
    const cartCountElement = document.querySelector('.cart-count');
    if (cartCountElement) {
        const currentCount = parseInt(cartCountElement.textContent) || 0;
        cartCountElement.textContent = currentCount + 1;
        
        // Add animation
        cartCountElement.style.transform = 'scale(1.2)';
        setTimeout(() => {
            cartCountElement.style.transform = 'scale(1)';
        }, 200);
    }
}

/**
 * Smooth scroll to element
 */
function smoothScrollTo(elementId) {
    const element = document.getElementById(elementId);
    if (element) {
        element.scrollIntoView({
            behavior: 'smooth',
            block: 'start'
        });
    }
}

/**
 * Initialize lazy loading for images
 */
function initLazyLoading() {
    const images = document.querySelectorAll('img[data-src]');
    
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
        
        images.forEach(img => imageObserver.observe(img));
    } else {
        // Fallback for older browsers
        images.forEach(img => {
            img.src = img.dataset.src;
            img.classList.remove('lazy');
        });
    }
}

/**
 * Initialize product page analytics
 */
function initProductAnalytics() {
    // Track product view
    if (typeof gtag !== 'undefined') {
        gtag('event', 'view_item', {
            currency: 'USD',
            value: productData.price,
            items: [{
                item_id: productData.id,
                item_name: productData.name,
                price: productData.price,
                currency: 'USD'
            }]
        });
    }
    
    // Track add to cart clicks
    const addToCartBtn = document.querySelector('button[name="add_to_cart"]');
    if (addToCartBtn) {
        addToCartBtn.addEventListener('click', function() {
            if (typeof gtag !== 'undefined') {
                gtag('event', 'add_to_cart', {
                    currency: 'USD',
                    value: productData.price,
                    items: [{
                        item_id: productData.id,
                        item_name: productData.name,
                        price: productData.price,
                        currency: 'USD'
                    }]
                });
            }
        });
    }
}

// Initialize analytics if available
if (typeof productData !== 'undefined') {
    initProductAnalytics();
}

// Initialize lazy loading
initLazyLoading();