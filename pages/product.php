<?php
require_once '../includes/config.php';
require_once '../includes/auth.php';
require_once '../includes/functions.php';
require_once '../includes/database.php';

// Start session
session_start();

// Get product ID from URL
$product_id = isset($_GET['id']) ? (int)$_GET['id'] : 0;

if (!$product_id) {
    header('Location: store.php');
    exit;
}

// Get product details
$product = getProductById($product_id);

if (!$product) {
    header('Location: store.php');
    exit;
}

// Get product images
$images = getProductImages($product_id);

// Get related products
$related_products = getRelatedProducts($product_id, $product['category_id'], 4);

// Get product reviews
$reviews = getProductReviews($product_id);

// Get average rating
$avg_rating = getProductAverageRating($product_id);

// Get review count
$review_count = count($reviews);

// Handle add to cart
$cart_message = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['add_to_cart'])) {
    if (!is_logged_in()) {
        header('Location: login.php?redirect=' . urlencode($_SERVER['REQUEST_URI']));
        exit;
    }
    
    $quantity = isset($_POST['quantity']) ? (int)$_POST['quantity'] : 1;
    $quantity = max(1, min($quantity, $product['stock']));
    
    if (addToCart($_SESSION['user_id'], $product_id, $quantity)) {
        $cart_message = 'Product added to cart successfully!';
    } else {
        $cart_message = 'Error adding product to cart.';
    }
}

// Handle add to wishlist
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['add_to_wishlist'])) {
    if (!is_logged_in()) {
        header('Location: login.php?redirect=' . urlencode($_SERVER['REQUEST_URI']));
        exit;
    }
    
    if (addToWishlist($_SESSION['user_id'], $product_id)) {
        $cart_message = 'Product added to wishlist!';
    } else {
        $cart_message = 'Product is already in your wishlist.';
    }
}

// Get page title
$page_title = $product['name'] . ' - Ola Store Electronics';
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo htmlspecialchars($page_title); ?></title>
    <meta name="description" content="<?php echo htmlspecialchars($product['description']); ?>">
    <meta name="keywords" content="<?php echo htmlspecialchars($product['name']); ?>, electronics, <?php echo htmlspecialchars($product['category_name']); ?>">
    
    <!-- Open Graph Meta Tags -->
    <meta property="og:title" content="<?php echo htmlspecialchars($product['name']); ?>">
    <meta property="og:description" content="<?php echo htmlspecialchars($product['description']); ?>">
    <meta property="og:image" content="<?php echo htmlspecialchars($product['image']); ?>">
    <meta property="og:url" content="<?php echo 'https://' . $_SERVER['HTTP_HOST'] . $_SERVER['REQUEST_URI']; ?>">
    
    <!-- CSS -->
    <link rel="stylesheet" href="../assets/css/style.css">
    <link rel="stylesheet" href="../assets/css/product.css">
    
    <!-- Font Awesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
</head>
<body>
    <!-- Header -->
    <?php include '../includes/header.php'; ?>

    <!-- Main Content -->
    <main class="main-content">
        <div class="container">
            <!-- Breadcrumb -->
            <nav class="breadcrumb" aria-label="Breadcrumb">
                <ol>
                    <li><a href="../index.php">Home</a></li>
                    <li><a href="store.php">Store</a></li>
                    <li><a href="store.php?category=<?php echo $product['category_id']; ?>"><?php echo htmlspecialchars($product['category_name']); ?></a></li>
                    <li aria-current="page"><?php echo htmlspecialchars($product['name']); ?></li>
                </ol>
            </nav>

            <!-- Product Detail Section -->
            <section class="product-detail">
                <div class="product-grid">
                    <!-- Product Images -->
                    <div class="product-images">
                        <div class="main-image-container">
                            <img src="<?php echo htmlspecialchars($product['image']); ?>" 
                                 alt="<?php echo htmlspecialchars($product['name']); ?>" 
                                 class="main-image" 
                                 id="mainImage">
                            <div class="zoom-lens" id="zoomLens"></div>
                        </div>
                        
                        <?php if (!empty($images)): ?>
                        <div class="thumbnail-images">
                            <img src="<?php echo htmlspecialchars($product['image']); ?>" 
                                 alt="<?php echo htmlspecialchars($product['name']); ?>" 
                                 class="thumbnail active" 
                                 data-image="<?php echo htmlspecialchars($product['image']); ?>">
                            
                            <?php foreach ($images as $image): ?>
                            <img src="<?php echo htmlspecialchars($image['image_url']); ?>" 
                                 alt="<?php echo htmlspecialchars($product['name']); ?>" 
                                 class="thumbnail" 
                                 data-image="<?php echo htmlspecialchars($image['image_url']); ?>">
                            <?php endforeach; ?>
                        </div>
                        <?php endif; ?>
                    </div>

                    <!-- Product Info -->
                    <div class="product-info">
                        <div class="product-header">
                            <h1 class="product-title"><?php echo htmlspecialchars($product['name']); ?></h1>
                            
                            <!-- Rating -->
                            <div class="product-rating">
                                <div class="stars">
                                    <?php for ($i = 1; $i <= 5; $i++): ?>
                                        <?php if ($i <= $avg_rating): ?>
                                            <i class="fas fa-star filled"></i>
                                        <?php elseif ($i - 0.5 <= $avg_rating): ?>
                                            <i class="fas fa-star-half-alt filled"></i>
                                        <?php else: ?>
                                            <i class="fas fa-star"></i>
                                        <?php endif; ?>
                                    <?php endfor; ?>
                                </div>
                                <span class="rating-text">
                                    <?php echo number_format($avg_rating, 1); ?> 
                                    (<?php echo $review_count; ?> reviews)
                                </span>
                                <a href="#reviews" class="review-link">Write a review</a>
                            </div>

                            <!-- Brand -->
                            <?php if ($product['brand']): ?>
                            <div class="product-brand">
                                <span class="brand-label">Brand:</span>
                                <span class="brand-name"><?php echo htmlspecialchars($product['brand']); ?></span>
                            </div>
                            <?php endif; ?>
                        </div>

                        <!-- Price and Stock -->
                        <div class="product-pricing">
                            <?php if ($product['discount_price'] && $product['discount_price'] < $product['price']): ?>
                                <div class="price-container">
                                    <span class="original-price">$<?php echo number_format($product['price'], 2); ?></span>
                                    <span class="current-price">$<?php echo number_format($product['discount_price'], 2); ?></span>
                                    <span class="discount-badge">
                                        <?php 
                                        $discount_percent = round((($product['price'] - $product['discount_price']) / $product['price']) * 100);
                                        echo $discount_percent . '% OFF';
                                        ?>
                                    </span>
                                </div>
                            <?php else: ?>
                                <div class="price-container">
                                    <span class="current-price">$<?php echo number_format($product['price'], 2); ?></span>
                                </div>
                            <?php endif; ?>

                            <!-- Stock Status -->
                            <div class="stock-status">
                                <?php if ($product['stock'] > 0): ?>
                                    <span class="in-stock">
                                        <i class="fas fa-check-circle"></i>
                                        In Stock (<?php echo $product['stock']; ?> available)
                                    </span>
                                <?php else: ?>
                                    <span class="out-of-stock">
                                        <i class="fas fa-times-circle"></i>
                                        Out of Stock
                                    </span>
                                <?php endif; ?>
                            </div>
                        </div>

                        <!-- Product Actions -->
                        <div class="product-actions">
                            <?php if ($product['stock'] > 0): ?>
                                <form method="POST" class="add-to-cart-form">
                                    <div class="quantity-selector">
                                        <label for="quantity">Quantity:</label>
                                        <div class="quantity-controls">
                                            <button type="button" class="qty-btn" data-action="decrease">-</button>
                                            <input type="number" id="quantity" name="quantity" value="1" min="1" max="<?php echo $product['stock']; ?>" class="quantity-input">
                                            <button type="button" class="qty-btn" data-action="increase">+</button>
                                        </div>
                                    </div>
                                    
                                    <div class="action-buttons">
                                        <button type="submit" name="add_to_cart" class="btn btn-primary btn-large">
                                            <i class="fas fa-shopping-cart"></i>
                                            Add to Cart
                                        </button>
                                        
                                        <button type="submit" name="add_to_wishlist" class="btn btn-outline btn-large">
                                            <i class="fas fa-heart"></i>
                                            Add to Wishlist
                                        </button>
                                    </div>
                                </form>
                            <?php else: ?>
                                <div class="out-of-stock-actions">
                                    <button class="btn btn-secondary btn-large" disabled>
                                        <i class="fas fa-bell"></i>
                                        Notify When Available
                                    </button>
                                </div>
                            <?php endif; ?>
                        </div>

                        <!-- Product Features -->
                        <?php if ($product['features']): ?>
                        <div class="product-features">
                            <h3>Key Features</h3>
                            <ul class="features-list">
                                <?php 
                                $features = explode("\n", $product['features']);
                                foreach ($features as $feature):
                                    $feature = trim($feature);
                                    if ($feature):
                                ?>
                                <li><i class="fas fa-check"></i> <?php echo htmlspecialchars($feature); ?></li>
                                <?php 
                                    endif;
                                endforeach; 
                                ?>
                            </ul>
                        </div>
                        <?php endif; ?>

                        <!-- Quick Info -->
                        <div class="quick-info">
                            <div class="info-item">
                                <i class="fas fa-shipping-fast"></i>
                                <span>Free shipping on orders over $50</span>
                            </div>
                            <div class="info-item">
                                <i class="fas fa-shield-alt"></i>
                                <span>1 year warranty</span>
                            </div>
                            <div class="info-item">
                                <i class="fas fa-undo"></i>
                                <span>30-day return policy</span>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Product Tabs -->
                <div class="product-tabs">
                    <div class="tab-navigation">
                        <button class="tab-btn active" data-tab="description">Description</button>
                        <button class="tab-btn" data-tab="specifications">Specifications</button>
                        <button class="tab-btn" data-tab="reviews">Reviews (<?php echo $review_count; ?>)</button>
                        <button class="tab-btn" data-tab="shipping">Shipping & Returns</button>
                    </div>

                    <div class="tab-content">
                        <!-- Description Tab -->
                        <div class="tab-pane active" id="description">
                            <div class="description-content">
                                <?php echo nl2br(htmlspecialchars($product['description'])); ?>
                            </div>
                        </div>

                        <!-- Specifications Tab -->
                        <div class="tab-pane" id="specifications">
                            <div class="specifications-content">
                                <?php if ($product['specifications']): ?>
                                    <?php 
                                    $specs = json_decode($product['specifications'], true);
                                    if ($specs && is_array($specs)):
                                    ?>
                                    <div class="specs-grid">
                                        <?php foreach ($specs as $category => $items): ?>
                                        <div class="spec-category">
                                            <h4><?php echo htmlspecialchars($category); ?></h4>
                                            <div class="spec-items">
                                                <?php foreach ($items as $key => $value): ?>
                                                <div class="spec-item">
                                                    <span class="spec-key"><?php echo htmlspecialchars($key); ?></span>
                                                    <span class="spec-value"><?php echo htmlspecialchars($value); ?></span>
                                                </div>
                                                <?php endforeach; ?>
                                            </div>
                                        </div>
                                        <?php endforeach; ?>
                                    </div>
                                    <?php else: ?>
                                    <p>No specifications available for this product.</p>
                                    <?php endif; ?>
                                <?php else: ?>
                                    <p>No specifications available for this product.</p>
                                <?php endif; ?>
                            </div>
                        </div>

                        <!-- Reviews Tab -->
                        <div class="tab-pane" id="reviews">
                            <div class="reviews-content">
                                <!-- Review Summary -->
                                <div class="review-summary">
                                    <div class="rating-overview">
                                        <div class="average-rating">
                                            <span class="rating-number"><?php echo number_format($avg_rating, 1); ?></span>
                                            <div class="stars-large">
                                                <?php for ($i = 1; $i <= 5; $i++): ?>
                                                    <?php if ($i <= $avg_rating): ?>
                                                        <i class="fas fa-star filled"></i>
                                                    <?php elseif ($i - 0.5 <= $avg_rating): ?>
                                                        <i class="fas fa-star-half-alt filled"></i>
                                                    <?php else: ?>
                                                        <i class="fas fa-star"></i>
                                                    <?php endif; ?>
                                                <?php endfor; ?>
                                            </div>
                                            <span class="total-reviews"><?php echo $review_count; ?> reviews</span>
                                        </div>
                                        
                                        <?php if (is_logged_in()): ?>
                                        <button class="btn btn-primary" id="writeReviewBtn">
                                            <i class="fas fa-edit"></i>
                                            Write a Review
                                        </button>
                                        <?php endif; ?>
                                    </div>
                                </div>

                                <!-- Review Form Modal -->
                                <?php if (is_logged_in()): ?>
                                <div class="modal" id="reviewModal">
                                    <div class="modal-content">
                                        <div class="modal-header">
                                            <h3>Write a Review</h3>
                                            <button class="modal-close" id="closeReviewModal">&times;</button>
                                        </div>
                                        <form class="review-form" id="reviewForm">
                                            <div class="form-group">
                                                <label for="reviewRating">Rating</label>
                                                <div class="rating-input">
                                                    <?php for ($i = 5; $i >= 1; $i--): ?>
                                                    <input type="radio" name="rating" id="star<?php echo $i; ?>" value="<?php echo $i; ?>" required>
                                                    <label for="star<?php echo $i; ?>"><i class="fas fa-star"></i></label>
                                                    <?php endfor; ?>
                                                </div>
                                            </div>
                                            
                                            <div class="form-group">
                                                <label for="reviewTitle">Title</label>
                                                <input type="text" id="reviewTitle" name="title" required maxlength="100">
                                            </div>
                                            
                                            <div class="form-group">
                                                <label for="reviewComment">Comment</label>
                                                <textarea id="reviewComment" name="comment" rows="4" required maxlength="500"></textarea>
                                            </div>
                                            
                                            <div class="form-actions">
                                                <button type="button" class="btn btn-secondary" id="cancelReview">Cancel</button>
                                                <button type="submit" class="btn btn-primary">Submit Review</button>
                                            </div>
                                        </form>
                                    </div>
                                </div>
                                <?php endif; ?>

                                <!-- Reviews List -->
                                <div class="reviews-list">
                                    <?php if (!empty($reviews)): ?>
                                        <?php foreach ($reviews as $review): ?>
                                        <div class="review-item">
                                            <div class="review-header">
                                                                                        <div class="reviewer-info">
                                            <span class="reviewer-name"><?php echo htmlspecialchars($review['first_name'] . ' ' . $review['last_name']); ?></span>
                                                    <div class="review-rating">
                                                        <?php for ($i = 1; $i <= 5; $i++): ?>
                                                            <?php if ($i <= $review['rating']): ?>
                                                                <i class="fas fa-star filled"></i>
                                                            <?php else: ?>
                                                                <i class="fas fa-star"></i>
                                                            <?php endif; ?>
                                                        <?php endfor; ?>
                                                    </div>
                                                </div>
                                                <span class="review-date"><?php echo date('M d, Y', strtotime($review['created_at'])); ?></span>
                                            </div>
                                            
                                            <?php if ($review['title']): ?>
                                            <h4 class="review-title"><?php echo htmlspecialchars($review['title']); ?></h4>
                                            <?php endif; ?>
                                            
                                            <p class="review-comment"><?php echo htmlspecialchars($review['comment']); ?></p>
                                        </div>
                                        <?php endforeach; ?>
                                    <?php else: ?>
                                        <div class="no-reviews">
                                            <p>No reviews yet. Be the first to review this product!</p>
                                            <?php if (!is_logged_in()): ?>
                                            <a href="login.php" class="btn btn-primary">Login to Review</a>
                                            <?php endif; ?>
                                        </div>
                                    <?php endif; ?>
                                </div>
                            </div>
                        </div>

                        <!-- Shipping Tab -->
                        <div class="tab-pane" id="shipping">
                            <div class="shipping-content">
                                <div class="shipping-info">
                                    <h3>Shipping Information</h3>
                                    <ul>
                                        <li><strong>Free Shipping:</strong> On orders over $50</li>
                                        <li><strong>Standard Shipping:</strong> 3-5 business days ($5.99)</li>
                                        <li><strong>Express Shipping:</strong> 1-2 business days ($12.99)</li>
                                        <li><strong>Overnight:</strong> Next business day ($24.99)</li>
                                    </ul>
                                </div>
                                
                                <div class="returns-info">
                                    <h3>Return Policy</h3>
                                    <ul>
                                        <li><strong>Return Window:</strong> 30 days from delivery</li>
                                        <li><strong>Return Shipping:</strong> Free for defective items</li>
                                        <li><strong>Refund Time:</strong> 3-5 business days after return</li>
                                        <li><strong>Restocking Fee:</strong> 15% for opened items</li>
                                    </ul>
                                </div>
                                
                                <div class="warranty-info">
                                    <h3>Warranty</h3>
                                    <ul>
                                        <li><strong>Manufacturer Warranty:</strong> 1 year</li>
                                        <li><strong>Extended Warranty:</strong> Available for purchase</li>
                                        <li><strong>Coverage:</strong> Defects in materials and workmanship</li>
                                    </ul>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Related Products -->
                <?php if (!empty($related_products)): ?>
                <section class="related-products">
                    <div class="section-header">
                        <h2>Related Products</h2>
                        <p>You might also like these products</p>
                    </div>
                    
                    <div class="products-grid">
                        <?php foreach ($related_products as $related): ?>
                        <div class="product-card">
                            <div class="product-image">
                                <a href="product.php?id=<?php echo $related['id']; ?>">
                                    <img src="<?php echo htmlspecialchars($related['image']); ?>" 
                                         alt="<?php echo htmlspecialchars($related['name']); ?>">
                                </a>
                                <?php if ($related['is_featured']): ?>
                                <span class="featured-badge">Featured</span>
                                <?php endif; ?>
                            </div>
                            
                            <div class="product-info">
                                <h3 class="product-name">
                                    <a href="product.php?id=<?php echo $related['id']; ?>">
                                        <?php echo htmlspecialchars($related['name']); ?>
                                    </a>
                                </h3>
                                
                                <div class="product-rating">
                                    <?php 
                                    $related_rating = getProductAverageRating($related['id']);
                                    for ($i = 1; $i <= 5; $i++):
                                    ?>
                                        <?php if ($i <= $related_rating): ?>
                                            <i class="fas fa-star filled"></i>
                                        <?php else: ?>
                                            <i class="fas fa-star"></i>
                                        <?php endif; ?>
                                    <?php endfor; ?>
                                    <span class="rating-count">(<?php echo getProductReviewCount($related['id']); ?>)</span>
                                </div>
                                
                                <div class="product-price">
                                    <?php if ($related['discount_price'] && $related['discount_price'] < $related['price']): ?>
                                        <span class="original-price">$<?php echo number_format($related['price'], 2); ?></span>
                                        <span class="current-price">$<?php echo number_format($related['discount_price'], 2); ?></span>
                                    <?php else: ?>
                                        <span class="current-price">$<?php echo number_format($related['price'], 2); ?></span>
                                    <?php endif; ?>
                                </div>
                                
                                <div class="product-actions">
                                    <a href="product.php?id=<?php echo $related['id']; ?>" class="btn btn-outline">
                                        View Details
                                    </a>
                                </div>
                            </div>
                        </div>
                        <?php endforeach; ?>
                    </div>
                </section>
                <?php endif; ?>
            </section>
        </div>
    </main>

    <!-- Footer -->
    <?php include '../includes/footer.php'; ?>

    <!-- JavaScript -->
    <script src="../assets/js/main.js"></script>
    <script src="../assets/js/product.js"></script>
    
    <script>
        // Product data for JavaScript
        const productData = {
            id: <?php echo $product['id']; ?>,
            name: '<?php echo addslashes($product['name']); ?>',
            price: <?php echo $product['price']; ?>,
            discountPrice: <?php echo $product['discount_price'] ?: 'null'; ?>,
            stock: <?php echo $product['stock']; ?>,
            image: '<?php echo addslashes($product['image']); ?>'
        };
        
        // Cart message
        <?php if ($cart_message): ?>
        document.addEventListener('DOMContentLoaded', function() {
            showNotification('<?php echo addslashes($cart_message); ?>', 'success');
        });
        <?php endif; ?>
    </script>
</body>
</html>