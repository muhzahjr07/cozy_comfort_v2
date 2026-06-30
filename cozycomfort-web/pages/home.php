<?php
// Get featured products
$apiClient = getApiClient();
$featured = [];

try {
    $featuredResponse = $apiClient->getFeaturedProducts();
    
    // Debug: Log what we're getting from the API
    error_log('Featured products API response: ' . json_encode($featuredResponse));
    
    // Extract data array from response
    if (is_array($featuredResponse)) {
        // API returns: {success, data: {data: [...products], pageNumber, etc}}
        if (isset($featuredResponse['data']['data']) && is_array($featuredResponse['data']['data'])) {
            $featured = $featuredResponse['data']['data'];
            error_log('Found featured products in data.data: ' . count($featured));
        } elseif (isset($featuredResponse['data']) && is_array($featuredResponse['data']) && isset($featuredResponse['data'][0])) {
            // Fallback: data is direct array of products
            $featured = $featuredResponse['data'];
            error_log('Found featured products in data: ' . count($featured));
        } elseif (is_array($featuredResponse) && isset($featuredResponse[0])) {
            // Direct array of products
            $featured = $featuredResponse;
            error_log('Found featured products as direct array: ' . count($featured));
        }
    }
} catch (Exception $e) {
    error_log('Featured products API error: ' . $e->getMessage());
    // Continue with empty array - don't break the page
    $featured = [];
}

// Get categories for the category filter
$categories = [];
try {
    $categoriesResponse = $apiClient->getProductCategories();
    if (is_array($categoriesResponse)) {
        $categories = $categoriesResponse;
    }
} catch (Exception $e) {
    error_log('Categories API error: ' . $e->getMessage());
    // Continue with empty categories array
    $categories = [];
}

?>

<div class="hero-section" style="background-image: url('images/hero-banner.jpg')">
    <div class="hero-content">
        <h1>Welcome to Cozy Comfort Sri Lanka</h1>
        <p>Your trusted destination for premium blankets, heated throws, and cozy bedding in Sri Lanka</p>
        <a href="?page=products" class="btn btn-primary">Shop Now</a>
    </div>
</div>

<section class="featured-products">
    <div class="container">
        <h2>Featured Products</h2>
        <div class="products-grid">
            <?php if ($featured && is_array($featured)): ?>
                <?php foreach ($featured as $product): ?>
                    <?php include 'components/product-card.php'; ?>
                <?php endforeach; ?>
            <?php else: ?>
                <p>No featured products available at the moment.</p>
            <?php endif; ?>
        </div>
        
        <div class="text-center">
            <a href="?page=products" class="btn btn-outline">View All Products</a>
        </div>
    </div>
</section>

<section class="categories">
    <div class="container">
        <h2>Shop by Category</h2>
        <div class="category-tiles">
            <?php if ($categories && is_array($categories)): ?>
                <?php foreach ($categories as $cat): ?>
                    <a href="?page=products&category=<?php echo urlencode($cat); ?>" class="category-tile">
                        <h3><?php echo htmlspecialchars($cat); ?></h3>
                    </a>
                <?php endforeach; ?>
            <?php else: ?>
                <a href="?page=products&category=Weighted+Blankets" class="category-tile">
                    <h3>Weighted Blankets</h3>
                </a>
                <a href="?page=products&category=Heated+%26+Electric+Blankets" class="category-tile">
                    <h3>Heated & Electric Blankets</h3>
                </a>
                <a href="?page=products&category=Sherpa+%26+Fleece+Throws" class="category-tile">
                    <h3>Sherpa & Fleece Throws</h3>
                </a>
                <a href="?page=products&category=Wool+%26+Knit+Blankets" class="category-tile">
                    <h3>Wool & Knit Blankets</h3>
                </a>
                <a href="?page=products&category=Luxury+Cashmere+%26+Cotton" class="category-tile">
                    <h3>Luxury Cashmere & Cotton</h3>
                </a>
            <?php endif; ?>
        </div>
    </div>
</section>

<section class="features">
    <div class="container">
        <h2>Why Choose Cozy Comfort?</h2>
        <div class="features-grid">
            <div class="feature-card">
                <h3>Fast Delivery Across Sri Lanka</h3>
                <p>Free shipping on orders over Rs. 5,000</p>
            </div>
            <div class="feature-card">
                <h3>Secure Payment</h3>
                <p>Your payments are safe and secure with local payment options</p>
            </div>
            <div class="feature-card">
                <h3>24/7 Support</h3>
                <p>Sinhala & Tamil speaking support team available anytime</p>
            </div>
            <div class="feature-card">
                <h3>Easy Returns</h3>
                <p>30-day return policy with free pickup from Colombo</p>
            </div>
        </div>
    </div>
</section>
