<div class="product-card">
    <?php 
    // Ensure product is a valid array with required data
    if (!is_array($product)) {
        echo '<div style="padding: 20px; text-align: center; color: #999;"><p>Product data unavailable</p></div>';
        return;
    }
    
    // Handle both 'id' and 'productId' field names from API
    $productId = $product['productId'] ?? $product['id'] ?? null;
    $productName = $product['name'] ?? null;
    $productPrice = $product['basePrice'] ?? $product['price'] ?? 0;
    
    // Check if product has required fields
    if (empty($productId) || empty($productName)) {
        echo '<div style="padding: 20px; text-align: center; color: #999;"><p>Invalid product data</p></div>';
        return;
    }
    ?>
    <div class="product-image">
        <img src="<?php echo !empty($product['imageUrl']) ? htmlspecialchars($product['imageUrl']) : 'images/placeholder.jpg'; ?>" 
             alt="<?php echo htmlspecialchars($productName); ?>"
             loading="lazy">
        <div class="product-overlay">
            <button class="btn btn-primary" onclick="quickView(<?php echo intval($productId); ?>)">
                Quick View
            </button>
        </div>
    </div>
    
    <div class="product-info">
        <h3 class="product-title">
            <a href="?page=product-detail&id=<?php echo intval($productId); ?>">
                <?php echo htmlspecialchars($productName); ?>
            </a>
        </h3>
        
        <?php if (!empty($product['description'])): ?>
        <p class="product-description">
            <?php echo htmlspecialchars(substr($product['description'], 0, 100)) . '...'; ?>
        </p>
        <?php endif; ?>
        
        <div class="product-rating">
            <div class="stars">
                <?php 
                $rating = floatval($product['rating'] ?? 4.5);
                for ($i = 1; $i <= 5; $i++) {
                    echo $i <= $rating ? '★' : '☆';
                }
                ?>
            </div>
            <span class="rating-text">(<?php echo intval($product['reviewCount'] ?? 0); ?>)</span>
        </div>
        
        <div class="product-price">
            <?php if (isset($product['originalPrice']) && floatval($product['originalPrice']) > floatval($productPrice)): ?>
                <span class="original-price"><?php echo formatCurrency(floatval($product['originalPrice'])); ?></span>
            <?php endif; ?>
            <span class="current-price"><?php echo formatCurrency(floatval($productPrice)); ?></span>
        </div>
        
        <div class="product-actions">
            <button class="btn btn-primary" onclick="addToCartWithData(<?php echo intval($productId); ?>, <?php echo htmlspecialchars(json_encode([
                'name' => $productName,
                'price' => floatval($productPrice),
                'imageUrl' => $product['imageUrl'] ?? 'images/placeholder.jpg'
            ]), ENT_QUOTES); ?>)">
                Add to Cart
            </button>
            <button class="btn btn-outline" onclick="addToWishlist(<?php echo intval($productId); ?>)">
                ♡
            </button>
        </div>
    </div>
</div>
