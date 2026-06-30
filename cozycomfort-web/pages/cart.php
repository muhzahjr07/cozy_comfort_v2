<?php
// Get API client
$apiClient = getApiClient();

// Handle logout
if (isset($_GET['action']) && $_GET['action'] === 'logout') {
    // Clear session
    session_destroy();
    setNotification('You have been logged out successfully.', 'info');
    redirect('?page=home');
}

// Cart functionality - uses localStorage on frontend and session as backup
$cart = $_SESSION['cart'] ?? [];

// Handle cart operations
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = postParam('action');
    
    switch ($action) {
        case 'add':
            $productId = postParam('productId');
            $quantity = (int)postParam('quantity', 1);
            
            // Add to cart session (in real app, this would be handled via AJAX)
            if (!isset($_SESSION['cart'])) {
                $_SESSION['cart'] = [];
            }
            
            if (isset($_SESSION['cart'][$productId])) {
                $_SESSION['cart'][$productId] += $quantity;
            } else {
                $_SESSION['cart'][$productId] = $quantity;
            }
            
            setNotification('Item added to cart!', 'success');
            break;
            
        case 'remove':
            $productId = postParam('productId');
            unset($_SESSION['cart'][$productId]);
            setNotification('Item removed from cart.', 'info');
            break;
            
        case 'update':
            $productId = postParam('productId');
            $quantity = (int)postParam('quantity');
            
            if ($quantity <= 0) {
                unset($_SESSION['cart'][$productId]);
            } else {
                $_SESSION['cart'][$productId] = $quantity;
            }
            
            setNotification('Cart updated.', 'info');
            break;
    }
    
    redirect('?page=cart');
}

// Get cart items with product details
$cartItems = [];
$subtotal = 0;

if (!empty($_SESSION['cart'])) {
    foreach ($_SESSION['cart'] as $productId => $quantity) {
        $product = $apiClient->get('/products/' . $productId);
        if ($product) {
            $product['quantity'] = $quantity;
            $product['total'] = $product['price'] * $quantity;
            $cartItems[] = $product;
            $subtotal += $product['total'];
        }
    }
}

$tax = $subtotal * 0.08; // 8% tax
$total = $subtotal + $tax;
?>

<div class="cart-page">
    <div class="container">
        <h1>Shopping Cart</h1>
        
        <!-- JavaScript will populate this from localStorage -->
        <div id="jsCartContent">
            <div class="empty-cart">
                <div class="empty-cart-icon">🛒</div>
                <h3>Your cart is empty</h3>
                <p>Add some products to your cart to continue shopping.</p>
                <a href="?page=products" class="btn btn-primary">Continue Shopping</a>
            </div>
        </div>
        
        <?php if (!empty($cartItems)): ?>
            <!-- Server-side cart fallback (shown if JavaScript is disabled) -->
            <noscript>
            <div class="cart-content">
                <div class="cart-items">
                    <table class="cart-table">
                        <thead>
                            <tr>
                                <th>Product</th>
                                <th>Price</th>
                                <th>Quantity</th>
                                <th>Total</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($cartItems as $item): ?>
                            <tr class="cart-item" data-product-id="<?php echo $item['id']; ?>">
                                <td class="product-info">
                                    <img src="<?php echo htmlspecialchars($item['imageUrl'] ?? 'images/placeholder.jpg'); ?>" 
                                         alt="<?php echo htmlspecialchars($item['name']); ?>" class="cart-item-image">
                                    <div class="product-details">
                                        <h4><?php echo htmlspecialchars($item['name']); ?></h4>
                                        <p><?php echo htmlspecialchars(substr($item['description'] ?? '', 0, 100)); ?>...</p>
                                    </div>
                                </td>
                                <td class="price"><?php echo formatCurrency($item['price']); ?></td>
                                <td class="quantity">
                                    <div class="quantity-controls">
                                        <button onclick="updateQuantity(<?php echo $item['id']; ?>, -1)">-</button>
                                        <span class="quantity-value"><?php echo $item['quantity']; ?></span>
                                        <button onclick="updateQuantity(<?php echo $item['id']; ?>, 1)">+</button>
                                    </div>
                                </td>
                                <td class="total"><?php echo formatCurrency($item['total']); ?></td>
                                <td class="actions">
                                    <button onclick="removeFromCart(<?php echo $item['id']; ?>)" class="btn btn-danger btn-sm">
                                        Remove
                                    </button>
                                </td>
                            </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
                
                <div class="cart-summary">
                    <div class="summary-card">
                        <h3>Order Summary</h3>
                        
                        <div class="summary-row">
                            <span>Subtotal:</span>
                            <span><?php echo formatCurrency($subtotal); ?></span>
                        </div>
                        
                        <div class="summary-row">
                            <span>Tax (8%):</span>
                            <span><?php echo formatCurrency($tax); ?></span>
                        </div>
                        
                        <div class="summary-row total-row">
                            <span>Total:</span>
                            <span><?php echo formatCurrency($total); ?></span>
                        </div>
                        
                        <div class="cart-actions">
                            <a href="?page=products" class="btn btn-outline">Continue Shopping</a>
                            <a href="?page=checkout" class="btn btn-primary">Proceed to Checkout</a>
                        </div>
                    </div>
                </div>
            </noscript>
        <?php endif; ?>
    </div>
</div>

<script>
function updateQuantity(productId, change) {
    // In a real application, this would be handled via AJAX
    const form = document.createElement('form');
    form.method = 'POST';
    form.style.display = 'none';
    
    const actionInput = document.createElement('input');
    actionInput.name = 'action';
    actionInput.value = 'update';
    
    const productIdInput = document.createElement('input');
    productIdInput.name = 'productId';
    productIdInput.value = productId;
    
    const currentQuantity = parseInt(document.querySelector(`[data-product-id="${productId}"] .quantity-value`).textContent);
    const newQuantity = Math.max(0, currentQuantity + change);
    
    const quantityInput = document.createElement('input');
    quantityInput.name = 'quantity';
    quantityInput.value = newQuantity;
    
    form.appendChild(actionInput);
    form.appendChild(productIdInput);
    form.appendChild(quantityInput);
    
    document.body.appendChild(form);
    form.submit();
}

function removeFromCart(productId) {
    if (confirm('Are you sure you want to remove this item from your cart?')) {
        const form = document.createElement('form');
        form.method = 'POST';
        form.style.display = 'none';
        
        const actionInput = document.createElement('input');
        actionInput.name = 'action';
        actionInput.value = 'remove';
        
        const productIdInput = document.createElement('input');
        productIdInput.name = 'productId';
        productIdInput.value = productId;
        
        form.appendChild(actionInput);
        form.appendChild(productIdInput);
        
        document.body.appendChild(form);
        form.submit();
    }
}

// Render cart from localStorage
document.addEventListener('DOMContentLoaded', function() {
    renderCartFromLocalStorage();
});

function renderCartFromLocalStorage() {
    const cart = JSON.parse(localStorage.getItem('cart') || '[]');
    const cartContent = document.getElementById('jsCartContent');
    
    if (!cartContent) return;
    
    // Filter valid items with complete data
    const validItems = cart.filter(item => item.id && item.name && typeof item.price === 'number');
    
    if (validItems.length === 0) {
        cartContent.innerHTML = `
            <div class="empty-cart">
                <div class="empty-cart-icon">🛒</div>
                <h3>Your cart is empty</h3>
                <p>Add some products to your cart to continue shopping.</p>
                <a href="?page=products" class="btn btn-primary">Continue Shopping</a>
            </div>
        `;
        return;
    }
    
    // Calculate totals
    const subtotal = validItems.reduce((sum, item) => sum + (item.price * item.quantity), 0);
    const tax = subtotal * 0.08;
    const total = subtotal + tax;
    
    // Render cart items
    let html = `
        <div class="cart-content">
            <div class="cart-items">
                <table class="cart-table">
                    <thead>
                        <tr>
                            <th>Product</th>
                            <th>Price</th>
                            <th>Quantity</th>
                            <th>Total</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
    `;
    
    validItems.forEach(item => {
        html += `
            <tr class="cart-item" data-product-id="${item.id}">
                <td class="product-info">
                    <img src="${item.imageUrl || 'images/placeholder.jpg'}" alt="${item.name}">
                    <div>
                        <h4>${item.name}</h4>
                    </div>
                </td>
                <td class="price">${formatCurrency(item.price)}</td>
                <td class="quantity">
                    <div class="quantity-controls">
                        <button onclick="updateLocalStorageQuantity(${item.id}, -1)" class="quantity-btn">-</button>
                        <span class="quantity-value">${item.quantity}</span>
                        <button onclick="updateLocalStorageQuantity(${item.id}, 1)" class="quantity-btn">+</button>
                    </div>
                </td>
                <td class="total">${formatCurrency(item.price * item.quantity)}</td>
                <td class="actions">
                    <button onclick="removeFromLocalStorageCart(${item.id})" class="btn btn-danger btn-sm">Remove</button>
                </td>
            </tr>
        `;
    });
    
    html += `
                    </tbody>
                </table>
            </div>
            
            <div class="cart-summary">
                <h3>Order Summary</h3>
                <div class="summary-row">
                    <span>Subtotal:</span>
                    <span>${formatCurrency(subtotal)}</span>
                </div>
                <div class="summary-row">
                    <span>Tax (8%):</span>
                    <span>${formatCurrency(tax)}</span>
                </div>
                <div class="summary-row total">
                    <span>Total:</span>
                    <span>${formatCurrency(total)}</span>
                </div>
                <div class="cart-actions">
                    <a href="?page=products" class="btn btn-outline">Continue Shopping</a>
                    <a href="?page=checkout" class="btn btn-primary">Proceed to Checkout</a>
                </div>
            </div>
        </div>
    `;
    
    cartContent.innerHTML = html;
}

function updateLocalStorageQuantity(productId, change) {
    let cart = JSON.parse(localStorage.getItem('cart') || '[]');
    const itemIndex = cart.findIndex(item => item.id === productId);
    
    if (itemIndex >= 0) {
        cart[itemIndex].quantity += change;
        
        if (cart[itemIndex].quantity <= 0) {
            cart.splice(itemIndex, 1);
        }
        
        localStorage.setItem('cart', JSON.stringify(cart));
        updateCartCount(cart.reduce((sum, item) => sum + item.quantity, 0));
        renderCartFromLocalStorage();
    }
}

function removeFromLocalStorageCart(productId) {
    if (confirm('Are you sure you want to remove this item from your cart?')) {
        let cart = JSON.parse(localStorage.getItem('cart') || '[]');
        cart = cart.filter(item => item.id !== productId);
        localStorage.setItem('cart', JSON.stringify(cart));
        updateCartCount(cart.reduce((sum, item) => sum + item.quantity, 0));
        renderCartFromLocalStorage();
        showNotification('Item removed from cart', 'info');
    }
}

function formatCurrency(amount) {
    return 'Rs. ' + parseFloat(amount || 0).toFixed(2);
}
</script>
