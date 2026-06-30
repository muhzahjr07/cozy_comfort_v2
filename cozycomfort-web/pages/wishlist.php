<?php
// Check if user is logged in
if (!isLoggedIn()) {
    setNotification('Please login to view your wishlist.', 'error');
    redirect('?page=login');
}

$apiClient = getApiClient();
?>

<div class="wishlist-page">
    <div class="container">
        <div class="page-header">
            <h1>My Wishlist</h1>
            <p>Items you've saved for later</p>
        </div>

        <!-- Wishlist will be populated by JavaScript -->
        <div id="wishlistContent">
            <div class="loading">Loading your wishlist...</div>
        </div>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    renderWishlist();
});

async function renderWishlist() {
    const wishlist = JSON.parse(localStorage.getItem('wishlist') || '[]');
    const contentDiv = document.getElementById('wishlistContent');
    
    if (wishlist.length === 0) {
        contentDiv.innerHTML = `
            <div class="empty-wishlist">
                <div class="empty-icon">♡</div>
                <h3>Your wishlist is empty</h3>
                <p>Save your favorite products to view them here later.</p>
                <a href="?page=products" class="btn btn-primary">Browse Products</a>
            </div>
        `;
        return;
    }
    
    // Get all products from API and filter wishlist items
    try {
        const response = await fetch('/api/products');
        if (!response.ok) {
            throw new Error('Failed to fetch products');
        }
        const productsData = await response.json();
        const allProducts = productsData.data || [];
        const cart = JSON.parse(localStorage.getItem('cart') || '[]');
        
        let html = '<div class="wishlist-grid">';
        
        wishlist.forEach(productId => {
            const product = allProducts.find(p => p.id == productId);
            if (product) {
                html += renderWishlistItem(product);
            }
        });
        
        html += '</div>';
        contentDiv.innerHTML = html;
        
    } catch (error) {
        contentDiv.innerHTML = `
            <div class="error-message">
                <p>Failed to load wishlist items.</p>
                <button onclick="renderWishlist()" class="btn btn-outline">Try Again</button>
            </div>
        `;
    }
}

function renderWishlistItem(product) {
    return `
        <div class="wishlist-item">
            <div class="item-image">
                <img src="${product.imageUrl || 'images/placeholder.jpg'}" 
                     alt="${product.name}">
            </div>
            <div class="item-details">
                <h3>${product.name}</h3>
                <div class="item-rating">
                    <div class="stars">
                        ${'★'.repeat(Math.floor(product.rating || 4))}${'☆'.repeat(5 - Math.floor(product.rating || 4))}
                    </div>
                    <span>(${product.reviewCount || 0} reviews)</span>
                </div>
                <div class="item-price">
                    ${product.originalPrice ? `<span class="original-price">Rs. ${product.originalPrice.toFixed(2)}</span>` : ''}
                    <span class="current-price">Rs. ${product.price.toFixed(2)}</span>
                </div>
                <div class="item-actions">
                    <button onclick="addToCartFromWishlist(${product.id}, '${product.name.replace(/'/g, "\\'")}', ${product.price}, '${product.imageUrl || 'images/placeholder.jpg'}')" 
                            class="btn btn-primary">Add to Cart</button>
                    <button onclick="removeFromWishlist(${product.id})" 
                            class="btn btn-outline">Remove</button>
                    <a href="?page=product-detail&id=${product.id}" class="btn btn-outline">View Details</a>
                </div>
            </div>
        </div>
    `;
}

function addToCartFromWishlist(productId, name, price, imageUrl) {
    window.addToCartWithData(productId, { name, price, imageUrl }, 1);
}

function removeFromWishlist(productId) {
    let wishlist = JSON.parse(localStorage.getItem('wishlist') || '[]');
    wishlist = wishlist.filter(id => id !== productId);
    localStorage.setItem('wishlist', JSON.stringify(wishlist));
    showNotification('Removed from wishlist', 'info');
    renderWishlist();
}

// Products will be fetched from API
</script>

<style>
.wishlist-page {
    padding: 2rem 0;
    min-height: 60vh;
}

.page-header {
    margin-bottom: 2rem;
    text-align: center;
}

.page-header h1 {
    margin-bottom: 0.5rem;
    color: #333;
}

.page-header p {
    color: #666;
    margin: 0;
}

.empty-wishlist {
    text-align: center;
    padding: 4rem 2rem;
    background: white;
    border-radius: 12px;
    box-shadow: 0 2px 8px rgba(0,0,0,0.1);
}

.empty-icon {
    font-size: 4rem;
    color: #ddd;
    margin-bottom: 1rem;
}

.empty-wishlist h3 {
    margin-bottom: 1rem;
    color: #333;
}

.empty-wishlist p {
    margin-bottom: 2rem;
    color: #666;
}

.wishlist-grid {
    display: grid;
    grid-template-columns: repeat(auto-fill, minmax(300px, 1fr));
    gap: 2rem;
}

.wishlist-item {
    background: white;
    border-radius: 12px;
    overflow: hidden;
    box-shadow: 0 4px 12px rgba(0,0,0,0.1);
    transition: transform 0.3s ease;
}

.wishlist-item:hover {
    transform: translateY(-4px);
}

.item-image {
    height: 200px;
    overflow: hidden;
}

.item-image img {
    width: 100%;
    height: 100%;
    object-fit: cover;
}

.item-details {
    padding: 1.5rem;
}

.item-details h3 {
    margin-bottom: 0.75rem;
    color: #333;
    font-size: 1.25rem;
}

.item-rating {
    display: flex;
    align-items: center;
    gap: 0.5rem;
    margin-bottom: 1rem;
}

.stars {
    color: #ffc107;
    font-size: 0.9rem;
}

.item-rating span {
    font-size: 0.8rem;
    color: #666;
}

.item-price {
    margin-bottom: 1.5rem;
}

.original-price {
    text-decoration: line-through;
    color: #666;
    margin-right: 0.5rem;
}

.current-price {
    font-weight: bold;
    font-size: 1.25rem;
    color: #28a745;
}

.item-actions {
    display: flex;
    flex-direction: column;
    gap: 0.5rem;
}

.error-message {
    text-align: center;
    padding: 2rem;
    background: white;
    border-radius: 12px;
    box-shadow: 0 2px 8px rgba(0,0,0,0.1);
}

.loading {
    text-align: center;
    padding: 2rem;
    font-size: 1.1rem;
    color: #666;
}

@media (max-width: 768px) {
    .wishlist-grid {
        grid-template-columns: 1fr;
    }
    
    .item-actions {
        flex-direction: column;
    }
}
</style>
