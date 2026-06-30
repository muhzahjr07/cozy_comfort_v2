/**
 * Shopping cart management
 */

// Helper function for currency formatting
function formatCurrency(amount) {
    return 'Rs. ' + parseFloat(amount || 0).toFixed(2);
}

function getCart() {
    return JSON.parse(localStorage.getItem('cart') || '[]');
}

function saveCart(cart) {
    localStorage.setItem('cart', JSON.stringify(cart));
    updateCartCount(cart.reduce((sum, item) => sum + item.quantity, 0));
}

function addToCartAdvanced(productId, quantity = 1, productData = {}) {
    let cart = getCart();
    
    // Find existing item
    const existingItemIndex = cart.findIndex(item => item.id === productId);
    
    if (existingItemIndex >= 0) {
        cart[existingItemIndex].quantity += quantity;
    } else {
        cart.push({
            id: productId,
            quantity: quantity,
            addedAt: Date.now(),
            ...productData
        });
    }
    
    saveCart(cart);
    showNotification(`Added ${quantity} item(s) to cart`, 'success');
    
    // Animate cart icon
    animateCartIcon();
}

function updateCartQuantity(productId, quantity) {
    let cart = getCart();
    const itemIndex = cart.findIndex(i => i.id === productId);
    
    if (itemIndex >= 0) {
        if (quantity <= 0) {
            cart.splice(itemIndex, 1);
        } else {
            cart[itemIndex].quantity = quantity;
        }
        saveCart(cart);
    }
}

function removeFromCartAdvanced(productId) {
    let cart = getCart();
    cart = cart.filter(item => item.id !== productId);
    saveCart(cart);
    showNotification('Item removed from cart', 'info');
}

function clearCart() {
    localStorage.removeItem('cart');
    updateCartCount(0);
    showNotification('Cart cleared', 'info');
    
    // Reload page if on cart page
    if (window.location.search.includes('page=cart')) {
        setTimeout(() => location.reload(), 500);
    }
}

function getCartTotal() {
    const cart = getCart();
    return cart.reduce((total, item) => {
        const price = parseFloat(item.price) || 0;
        const quantity = parseInt(item.quantity) || 0;
        return total + (price * quantity);
    }, 0);
}

function getCartItemCount() {
    const cart = getCart();
    return cart.reduce((count, item) => count + item.quantity, 0);
}

async function submitOrder(event) {
    event.preventDefault();
    
    const form = event.target;
    const formData = new FormData(form);
    
    // Validate form
    if (!validateOrderForm(formData)) {
        return;
    }
    
    const cart = getCart();
    if (cart.length === 0) {
        showNotification('Your cart is empty', 'error');
        return;
    }
    
    // Prepare order data
    const orderData = {
        customerId: getCurrentUserId(),
        items: cart.map(item => ({
            productId: item.id,
            quantity: item.quantity,
            unitPrice: item.price
        })),
        deliveryAddress: buildAddress(formData),
        phoneNumber: formData.get('phone'),
        notes: formData.get('notes') || ''
    };
    
    try {
        showLoadingSpinner();
        const response = await apiPost('/orders', orderData);
        
        if (response && response.orderId) {
            // Clear cart on successful order
            clearCart();
            
            // Redirect to confirmation page
            window.location.href = `?page=order-confirmation&orderId=${response.orderId}`;
        }
    } catch (error) {
        showNotification('Failed to place order. Please try again.', 'error');
    } finally {
        hideLoadingSpinner();
    }
}

function validateOrderForm(formData) {
    const requiredFields = ['fullName', 'email', 'phone', 'address', 'city', 'state', 'zip'];
    const errors = [];
    
    requiredFields.forEach(field => {
        if (!formData.get(field) || !formData.get(field).trim()) {
            errors.push(`${field.replace(/([A-Z])/g, ' $1').toLowerCase()} is required`);
        }
    });
    
    if (errors.length > 0) {
        showNotification(errors.join(', '), 'error');
        return false;
    }
    
    return true;
}

function buildAddress(formData) {
    return `${formData.get('address')}, ${formData.get('city')}, ${formData.get('state')} ${formData.get('zip')}`;
}

function getCurrentUserId() {
    // In a real app, this would come from session or JWT token
    return parseInt(localStorage.getItem('user_id')) || null;
}

function animateCartIcon() {
    const cartIcon = document.querySelector('.cart-icon');
    if (cartIcon) {
        cartIcon.classList.add('cart-bounce');
        setTimeout(() => {
            cartIcon.classList.remove('cart-bounce');
        }, 600);
    }
}

// Mini cart functionality
function showMiniCart() {
    const cart = getCart();
    const miniCart = document.createElement('div');
    miniCart.className = 'mini-cart';
    miniCart.id = 'miniCart';
    
    let cartHTML = `
        <div class="mini-cart-header">
            <h3>Cart (${getCartItemCount()} items)</h3>
            <button onclick="closeMiniCart()" class="close-btn">&times;</button>
        </div>
        <div class="mini-cart-items">
    `;
    
    if (cart.length === 0) {
        cartHTML += '<p class="empty-cart-message">Your cart is empty</p>';
    } else {
        // Filter valid items only
        const validItems = cart.filter(item => item.name && typeof item.price === 'number');
        
        if (validItems.length === 0) {
            cartHTML += '<p class="empty-cart-message">Your cart has invalid items. <button onclick="clearCart();closeMiniCart();" style="color:#007bff;text-decoration:underline;background:none;border:none;cursor:pointer;">Clear cart</button></p>';
        } else {
            validItems.forEach(item => {
                cartHTML += `
                    <div class="mini-cart-item">
                        <img src="${item.imageUrl || 'images/placeholder.jpg'}" alt="${item.name || 'Product'}">
                        <div class="item-details">
                            <h4>${item.name || 'Unknown Product'}</h4>
                            <p>Qty: ${item.quantity || 1} × ${formatCurrency(item.price || 0)}</p>
                        </div>
                        <button onclick="removeFromCartAdvanced(${item.id})" class="remove-btn">×</button>
                    </div>
                `;
            });
        }
    }
    
    cartHTML += `
        </div>
        <div class="mini-cart-footer">
            <div class="cart-total">Total: ${formatCurrency(getCartTotal())}</div>
            <div class="cart-actions">
                <a href="?page=cart" class="btn btn-outline">View Cart</a>
                <a href="?page=checkout" class="btn btn-primary">Checkout</a>
            </div>
        </div>
    `;
    
    miniCart.innerHTML = cartHTML;
    document.body.appendChild(miniCart);
    
    // Close on outside click
    setTimeout(() => {
        document.addEventListener('click', function closeOnOutside(e) {
            if (!miniCart.contains(e.target) && !e.target.closest('.cart-icon')) {
                closeMiniCart();
                document.removeEventListener('click', closeOnOutside);
            }
        });
    }, 100);
}

function closeMiniCart() {
    const miniCart = document.getElementById('miniCart');
    if (miniCart) {
        miniCart.remove();
    }
}

// Initialize cart functionality
document.addEventListener('DOMContentLoaded', function() {
    // Update cart count on page load
    updateCartCount(getCartItemCount());
    
    // Add click handler to cart icon
    const cartIcon = document.querySelector('.cart-icon');
    if (cartIcon) {
        cartIcon.addEventListener('click', function(e) {
            e.preventDefault();
            showMiniCart();
        });
    }
});
