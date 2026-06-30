<?php
// Get API client
$apiClient = getApiClient();

$productId = getParam('id');
if (!$productId) {
    redirect('?page=products');
}

// Get product details
try {
    $productResponse = $apiClient->getProductById($productId);
    if (!$productResponse || !isset($productResponse['data']) || !$productResponse['data']) {
        http_response_code(404);
        $error = "Product not found";
        require 'pages/error.php';
        exit;
    }
    
    $product = $productResponse['data'];
} catch (Exception $e) {
    error_log('Product detail API error: ' . $e->getMessage());
    http_response_code(500);
    $error = "Unable to load product details. Please try again later.";
    require 'pages/error.php';
    exit;
}

// Get related products
$related = [];
try {
    $relatedResponse = $apiClient->getProducts(['category' => $product['category'] ?? '', 'pageSize' => 4]);
    if ($relatedResponse && isset($relatedResponse['data']['data']) && is_array($relatedResponse['data']['data'])) {
        $related = $relatedResponse['data']['data'];
    } elseif ($relatedResponse && isset($relatedResponse['data']) && is_array($relatedResponse['data'])) {
        $related = $relatedResponse['data'];
    }
} catch (Exception $e) {
    error_log('Related products API error: ' . $e->getMessage());
    // Continue without related products
}
?>

<div class="product-detail-page">
    <div class="container">
        <div class="breadcrumb">
            <a href="?page=home">Home</a> / 
            <a href="?page=products">Products</a> / 
            <span><?php echo htmlspecialchars($product['name']); ?></span>
        </div>

        <div class="product-detail">
            <div class="product-images">
                <div class="main-image">
                    <img src="<?php echo htmlspecialchars($product['imageUrl'] ?? 'images/placeholder.jpg'); ?>" 
                         alt="<?php echo htmlspecialchars($product['name']); ?>"
                         id="mainProductImage">
                </div>
                
                <?php if (!empty($product['additionalImages'])): ?>
                <div class="thumbnail-images">
                    <img src="<?php echo htmlspecialchars($product['imageUrl']); ?>" 
                         alt="Main image" class="thumbnail active"
                         onclick="changeMainImage('<?php echo htmlspecialchars($product['imageUrl']); ?>')">
                    <?php foreach ($product['additionalImages'] as $img): ?>
                        <img src="<?php echo htmlspecialchars($img); ?>" 
                             alt="Product image" class="thumbnail"
                             onclick="changeMainImage('<?php echo htmlspecialchars($img); ?>')">
                    <?php endforeach; ?>
                </div>
                <?php endif; ?>
            </div>

            <div class="product-info">
                <h1><?php echo htmlspecialchars($product['name']); ?></h1>
                
                <div class="product-rating">
                    <div class="stars">
                        <?php 
                        $rating = $product['rating'] ?? 4.5;
                        for ($i = 1; $i <= 5; $i++) {
                            echo $i <= $rating ? '★' : '☆';
                        }
                        ?>
                    </div>
                    <span class="rating-text">(<?php echo $product['reviewCount'] ?? 0; ?> reviews)</span>
                </div>

                <div class="product-price">
                    <?php if (isset($product['originalPrice']) && $product['originalPrice'] > $product['price']): ?>
                        <span class="original-price"><?php echo formatCurrency($product['originalPrice']); ?></span>
                        <span class="discount">-<?php echo round((($product['originalPrice'] - $product['price']) / $product['originalPrice']) * 100); ?>%</span>
                    <?php endif; ?>
                    <span class="current-price"><?php echo formatCurrency($product['price']); ?></span>
                </div>

                <div class="product-description">
                    <p><?php echo nl2br(htmlspecialchars($product['description'])); ?></p>
                </div>

                <?php if (!empty($product['specifications'])): ?>
                <div class="product-specs">
                    <h3>Specifications</h3>
                    <ul>
                        <?php 
                        $specs = is_string($product['specifications']) ? 
                                json_decode($product['specifications'], true) : 
                                $product['specifications'];
                        if (is_array($specs)):
                            foreach ($specs as $key => $value): ?>
                                <li><strong><?php echo htmlspecialchars($key); ?>:</strong> <?php echo htmlspecialchars($value); ?></li>
                            <?php endforeach;
                        endif; ?>
                    </ul>
                </div>
                <?php endif; ?>

                <div class="product-actions">
                    <div class="quantity-selector">
                        <label for="quantity">Quantity:</label>
                        <div class="quantity-controls">
                            <button type="button" onclick="decreaseQuantity()">-</button>
                            <input type="number" id="quantity" name="quantity" value="1" min="1" max="<?php echo $product['stock'] ?? 99; ?>">
                            <button type="button" onclick="increaseQuantity()">+</button>
                        </div>
                    </div>

                    <div class="action-buttons">
                        <button class="btn btn-primary btn-lg" onclick="addToCartWithData(<?php echo intval($product['id'] ?? $product['productId']); ?>, <?php echo htmlspecialchars(json_encode([
                            'name' => $product['name'],
                            'price' => floatval($product['price'] ?? 0),
                            'imageUrl' => $product['imageUrl'] ?? 'images/placeholder.jpg'
                        ]), ENT_QUOTES); ?>, getQuantity())">
                            Add to Cart
                        </button>
                        <button class="btn btn-outline" onclick="addToWishlist(<?php echo $product['id']; ?>)">
                            Add to Wishlist ♡
                        </button>
                    </div>
                </div>

                <div class="product-meta">
                    <p><strong>Category:</strong> <?php echo htmlspecialchars($product['category'] ?? 'General'); ?></p>
                    <p><strong>SKU:</strong> <?php echo htmlspecialchars($product['sku'] ?? $product['id']); ?></p>
                    <p><strong>Availability:</strong> 
                        <span class="stock-status <?php echo ($product['stock'] ?? 0) > 0 ? 'in-stock' : 'out-of-stock'; ?>">
                            <?php echo ($product['stock'] ?? 0) > 0 ? 'In Stock' : 'Out of Stock'; ?>
                        </span>
                    </p>
                </div>
            </div>
        </div>

        <!-- Product Tabs -->
        <div class="product-tabs">
            <div class="tab-buttons">
                <button class="tab-button active" onclick="showTab('description')">Description</button>
                <button class="tab-button" onclick="showTab('reviews')">Reviews</button>
                <button class="tab-button" onclick="showTab('shipping')">Shipping</button>
            </div>
            
            <div class="tab-content">
                <div id="description" class="tab-panel active">
                    <p><?php echo nl2br(htmlspecialchars($product['longDescription'] ?? $product['description'])); ?></p>
                </div>
                
                <div id="reviews" class="tab-panel">
                    <p>Customer reviews will be displayed here.</p>
                </div>
                
                <div id="shipping" class="tab-panel">
                    <p>Shipping information and policies will be displayed here.</p>
                </div>
            </div>
        </div>

        <!-- Related products -->
        <?php if ($related && is_array($related) && !empty($related)): ?>
        <section class="related-products">
            <h2>Related Products</h2>
            <div class="products-grid">
                <?php foreach (array_slice($related, 0, 4) as $product): ?>
                    <?php include 'components/product-card.php'; ?>
                <?php endforeach; ?>
            </div>
        </section>
        <?php endif; ?>
    </div>
</div>

<script>
function changeMainImage(imageSrc) {
    document.getElementById('mainProductImage').src = imageSrc;
    
    // Update active thumbnail
    document.querySelectorAll('.thumbnail').forEach(thumb => thumb.classList.remove('active'));
    event.target.classList.add('active');
}

function increaseQuantity() {
    const input = document.getElementById('quantity');
    const max = parseInt(input.getAttribute('max'));
    if (parseInt(input.value) < max) {
        input.value = parseInt(input.value) + 1;
    }
}

function decreaseQuantity() {
    const input = document.getElementById('quantity');
    if (parseInt(input.value) > 1) {
        input.value = parseInt(input.value) - 1;
    }
}

function getQuantity() {
    return parseInt(document.getElementById('quantity').value);
}

function showTab(tabName) {
    // Hide all tabs
    document.querySelectorAll('.tab-panel').forEach(panel => panel.classList.remove('active'));
    document.querySelectorAll('.tab-button').forEach(button => button.classList.remove('active'));
    
    // Show selected tab
    document.getElementById(tabName).classList.add('active');
    event.target.classList.add('active');
}
</script>
