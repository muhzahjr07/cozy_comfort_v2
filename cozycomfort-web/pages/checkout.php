<?php
// Get API client
$apiClient = getApiClient();

// Note: Cart is stored in localStorage on client-side
// We'll render the checkout form and populate cart data via JavaScript
?>

<!-- Temporary script to check cart before rendering -->
<script>
// Check if cart has items before PHP renders the page
const cart = JSON.parse(localStorage.getItem('cart') || '[]');
const validItems = cart.filter(item => item.id && item.name && typeof item.price === 'number');

if (validItems.length === 0) {
    window.location.href = '?page=products';
}
</script>

<?php
// Handle order submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $csrfToken = postParam('csrf_token');
    
    if (!verifyCsrfToken($csrfToken)) {
        setNotification('Security token mismatch. Please try again.', 'error');
    } else {
        // Prepare order data
        $orderData = [
            'customerId' => getUserId(),
            'items' => array_map(function($item) {
                return [
                    'productId' => $item['id'],
                    'quantity' => $item['quantity'],
                    'unitPrice' => $item['price']
                ];
            }, $cartItems),
            'deliveryAddress' => trim(
                postParam('address') . ', ' . 
                postParam('city') . ', ' . 
                postParam('state') . ' ' . 
                postParam('zip')
            ),
            'phoneNumber' => postParam('phone'),
            'notes' => postParam('notes', '')
        ];
        
        $order = $apiClient->post('/orders', $orderData);
        
        if ($order && isset($order['orderId'])) {
            // Clear cart
            unset($_SESSION['cart']);
            
            // Redirect to confirmation
            redirect('?page=order-confirmation&orderId=' . $order['orderId']);
        } else {
            $error = $apiClient->getLastError() ?: 'Failed to place order. Please try again.';
            setNotification($error, 'error');
        }
    }
}
?>

<div class="checkout-page">
    <div class="container">
        <h1>Checkout</h1>
        
        <form method="POST" class="checkout-form" id="checkoutForm">
            <input type="hidden" name="csrf_token" value="<?php echo generateCsrfToken(); ?>">
            
            <div class="form-section">
                <h2>Delivery Information</h2>
                
                <div class="form-row">
                    <div class="form-group">
                        <label for="firstName">First Name *</label>
                        <input type="text" id="firstName" name="firstName" required>
                    </div>
                    <div class="form-group">
                        <label for="lastName">Last Name *</label>
                        <input type="text" id="lastName" name="lastName" required>
                    </div>
                </div>
                
                <div class="form-group">
                    <label for="email">Email Address *</label>
                    <input type="email" id="email" name="email" required 
                           value="<?php echo htmlspecialchars($_SESSION['email'] ?? ''); ?>">
                </div>
                
                <div class="form-group">
                    <label for="phone">Phone Number *</label>
                    <input type="tel" id="phone" name="phone" required>
                </div>
                
                <div class="form-group">
                    <label for="address">Street Address *</label>
                    <input type="text" id="address" name="address" required 
                           placeholder="123 Main Street, Apt 4B">
                </div>
                
                <div class="form-row">
                    <div class="form-group">
                        <label for="city">City *</label>
                        <input type="text" id="city" name="city" required>
                    </div>
                    <div class="form-group">
                        <label for="state">State/Province *</label>
                        <input type="text" id="state" name="state" required>
                    </div>
                    <div class="form-group">
                        <label for="zip">ZIP/Postal Code *</label>
                        <input type="text" id="zip" name="zip" required>
                    </div>
                </div>
                
                <div class="form-group">
                    <label for="notes">Special Instructions (Optional)</label>
                    <textarea id="notes" name="notes" rows="3" 
                              placeholder="Any special delivery instructions..."></textarea>
                </div>
            </div>
            
            <div class="form-section">
                <h2>Order Summary</h2>
                
                <!-- Cart items will be populated by JavaScript -->
                <div id="checkoutCartItems">
                    <p>Loading cart items...</p>
                </div>
                
                <div class="totals" id="checkoutTotals">
                    <!-- Totals will be calculated by JavaScript -->
                </div>
            </div>
            
            <div class="text-center">
                <a href="?page=cart" class="btn btn-outline" style="margin-right: 1rem;">Back to Cart</a>
                <button type="submit" class="btn btn-primary btn-lg">
                    Place Order
                </button>
            </div>
        </form>
    </div>
</div>

<script>
// Render cart items from localStorage
function renderCheckoutCart() {
    const cart = JSON.parse(localStorage.getItem('cart') || '[]');
    const validItems = cart.filter(item => item.id && item.name && typeof item.price === 'number');
    
    if (validItems.length === 0) {
        document.getElementById('checkoutCartItems').innerHTML = '<p>Your cart is empty</p>';
        return;
    }
    
    // Calculate totals
    const subtotal = validItems.reduce((sum, item) => sum + (item.price * item.quantity), 0);
    const tax = subtotal * 0.08;
    const shipping = subtotal > 150000 ? 0 : 5000; // Free shipping over Rs. 150,000
    const total = subtotal + tax + shipping;
    
    // Render cart items table
    let html = `
        <table class="order-summary">
            <thead>
                <tr>
                    <th>Product</th>
                    <th>Quantity</th>
                    <th>Price</th>
                    <th>Total</th>
                </tr>
            </thead>
            <tbody>
    `;
    
    validItems.forEach(item => {
        html += `
            <tr>
                <td>
                    <div class="product-info" style="display: flex; align-items: center;">
                        <img src="${item.imageUrl || 'images/placeholder.jpg'}" 
                             alt="${item.name}" 
                             style="width: 50px; height: 50px; object-fit: cover; border-radius: 4px; margin-right: 1rem;">
                        <div>
                            <strong>${item.name}</strong>
                        </div>
                    </div>
                </td>
                <td>${item.quantity}</td>
                <td>Rs. ${parseFloat(item.price).toFixed(2)}</td>
                <td>Rs. ${(item.price * item.quantity).toFixed(2)}</td>
            </tr>
        `;
    });
    
    html += `
            </tbody>
        </table>
    `;
    
    document.getElementById('checkoutCartItems').innerHTML = html;
    
    // Render totals
    const totalsHTML = `
        <div class="total-row">
            <span>Subtotal:</span>
            <span>Rs. ${subtotal.toFixed(2)}</span>
        </div>
        <div class="total-row">
            <span>Shipping:</span>
            <span>${shipping === 0 ? 'Free' : 'Rs. ' + shipping.toFixed(2)}</span>
        </div>
        <div class="total-row">
            <span>Tax (8%):</span>
            <span>Rs. ${tax.toFixed(2)}</span>
        </div>
        <div class="total-row total">
            <span>Total:</span>
            <span>Rs. ${total.toFixed(2)}</span>
        </div>
        ${shipping > 0 ? `
        <div style="background: #e7f3ff; padding: 1rem; border-radius: 6px; margin-top: 1rem;">
            <p style="margin: 0; color: #004085;">
                💡 <strong>Tip:</strong> Add Rs. ${(150000 - subtotal).toFixed(2)} more to get free shipping!
            </p>
        </div>
        ` : ''}
    `;
    
    document.getElementById('checkoutTotals').innerHTML = totalsHTML;
}

// Render on page load
document.addEventListener('DOMContentLoaded', renderCheckoutCart);

document.getElementById('checkoutForm').addEventListener('submit', function(e) {
    e.preventDefault();
    
    const requiredFields = this.querySelectorAll('[required]');
    let isValid = true;
    
    requiredFields.forEach(field => {
        if (!field.value.trim()) {
            field.classList.add('error');
            isValid = false;
        } else {
            field.classList.remove('error');
        }
    });
    
    if (!isValid) {
        showNotification('Please fill in all required fields.', 'error');
        
        // Focus first error field
        const firstError = this.querySelector('.error');
        if (firstError) {
            firstError.focus();
            firstError.scrollIntoView({ behavior: 'smooth', block: 'center' });
        }
        return;
    }
    
    // Call AJAX order submission from cart.js
    submitOrder(e);
});
</script>
