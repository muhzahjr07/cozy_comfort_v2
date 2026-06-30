// Initialize on page load
document.addEventListener('DOMContentLoaded', function() {
    initializeNavigation();
    initializeCart();
    loadNotifications();
    setupEventListeners();
    initTheme();
});

function initializeNavigation() {
    const menuToggle = document.querySelector('#menuToggle');
    const navMenu = document.querySelector('#navMenu');
    
    if (menuToggle && navMenu) {
        menuToggle.addEventListener('click', function() {
            navMenu.classList.toggle('open');
        });
    }
}

function initializeCart() {
    let cart = JSON.parse(localStorage.getItem('cart') || '[]');
    
    // Clean up invalid cart items (items without required product data)
    const validCart = cart.filter(item => {
        return item.id && item.name && typeof item.price === 'number' && item.quantity;
    });
    
    // If cart was cleaned, save it
    if (validCart.length !== cart.length) {
        localStorage.setItem('cart', JSON.stringify(validCart));
        cart = validCart;
    }
    
    updateCartCount(cart.reduce((sum, item) => sum + item.quantity, 0));
}

function updateCartCount(count) {
    const cartBadge = document.querySelector('#cartCount');
    if (cartBadge) {
        cartBadge.textContent = count;
        cartBadge.style.display = count > 0 ? 'block' : 'none';
    }
}

function addToCart(productId, quantity = 1) {
    const cart = JSON.parse(localStorage.getItem('cart') || '[]');
    
    // Check if product already in cart
    const existingItem = cart.find(item => item.id === productId);
    if (existingItem) {
        existingItem.quantity += quantity;
    } else {
        cart.push({
            id: productId,
            quantity: quantity,
            addedAt: Date.now()
        });
    }
    
    localStorage.setItem('cart', JSON.stringify(cart));
    updateCartCount(cart.length);
    showNotification('Item added to cart', 'success');
    
    // Also send to server if user is logged in
    if (isLoggedIn()) {
        syncCartToServer(cart);
    }
}

// Add to cart with complete product data
window.addToCartWithData = function(productId, productData, quantity = 1) {
    const cart = JSON.parse(localStorage.getItem('cart') || '[]');
    
    // Check if product already in cart
    const existingItemIndex = cart.findIndex(item => item.id === productId);
    if (existingItemIndex >= 0) {
        cart[existingItemIndex].quantity += quantity;
    } else {
        cart.push({
            id: productId,
            quantity: quantity,
            addedAt: Date.now(),
            name: productData.name,
            price: productData.price,
            imageUrl: productData.imageUrl
        });
    }
    
    localStorage.setItem('cart', JSON.stringify(cart));
    updateCartCount(cart.reduce((sum, item) => sum + item.quantity, 0));
    showNotification('Item added to cart', 'success');
    
    // Also send to server if user is logged in
    if (typeof isLoggedIn === 'function' && isLoggedIn()) {
        syncCartToServer(cart);
    }
}

function removeFromCart(productId) {
    const cart = JSON.parse(localStorage.getItem('cart') || '[]');
    const updatedCart = cart.filter(item => item.id !== productId);
    localStorage.setItem('cart', JSON.stringify(updatedCart));
    updateCartCount(updatedCart.length);
    showNotification('Item removed from cart', 'info');
}

function clearCart() {
    localStorage.removeItem('cart');
    updateCartCount(0);
    showNotification('Cart cleared', 'info');
}

function showNotification(message, type = 'info') {
    // Check if there's already a notification
    const existingToast = document.querySelector('.toast');
    if (existingToast) {
        existingToast.remove();
    }
    
    // Create toast notification
    const toast = document.createElement('div');
    toast.className = `toast toast-${type}`;
    toast.innerHTML = `
        <span class="toast-icon">${getToastIcon(type)}</span>
        <span class="toast-message">${message}</span>
        <button class="toast-close" onclick="closeToast(this)">&times;</button>
    `;
    
    document.body.appendChild(toast);
    
    // Auto-remove after 4 seconds
    setTimeout(() => {
        if (toast && toast.parentNode) {
            toast.remove();
        }
    }, 4000);
}

function getToastIcon(type) {
    switch(type) {
        case 'success': return '✅';
        case 'error': return '❌';
        case 'warning': return '⚠️';
        default: return 'ℹ️';
    }
}

function closeToast(element) {
    element.parentElement.remove();
}

function closeAlert() {
    const alert = document.getElementById('alertMessage');
    if (alert) {
        alert.remove();
    }
}

function showLoadingSpinner() {
    const spinner = document.getElementById('loadingSpinner');
    if (spinner) spinner.style.display = 'flex';
}

function hideLoadingSpinner() {
    const spinner = document.getElementById('loadingSpinner');
    if (spinner) spinner.style.display = 'none';
}

function isLoggedIn() {
    // Check if user is logged in (you could check for session or token)
    return document.body.classList.contains('logged-in') || 
           localStorage.getItem('auth_token') !== null;
}

function syncCartToServer(cart) {
    // Sync cart to server for logged-in users
    fetch('?page=cart-sync', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
        },
        body: JSON.stringify({cart: cart})
    }).catch(error => {
        console.warn('Failed to sync cart to server:', error);
    });
}

function loadNotifications() {
    // Auto-close alerts after 5 seconds
    const alert = document.getElementById('alertMessage');
    if (alert) {
        setTimeout(() => {
            alert.style.opacity = '0';
            setTimeout(() => alert.remove(), 300);
        }, 5000);
    }
}

function setupEventListeners() {
    // Form validation
    const forms = document.querySelectorAll('form');
    forms.forEach(form => {
        form.addEventListener('submit', function(e) {
            if (!validateForm(form)) {
                e.preventDefault();
            }
        });
    });
    
    // Smooth scroll for anchor links
    const anchorLinks = document.querySelectorAll('a[href^="#"]');
    anchorLinks.forEach(link => {
        link.addEventListener('click', function(e) {
            e.preventDefault();
            const target = document.querySelector(this.getAttribute('href'));
            if (target) {
                target.scrollIntoView({ behavior: 'smooth' });
            }
        });
    });
}

function validateForm(form) {
    const requiredFields = form.querySelectorAll('[required]');
    let isValid = true;
    
    requiredFields.forEach(field => {
        if (!field.value.trim()) {
            field.classList.add('error');
            isValid = false;
        } else {
            field.classList.remove('error');
        }
    });
    
    return isValid;
}

// Quick view functionality
function quickView(productId) {
    showLoadingSpinner();
    
    // In a real application, this would open a modal with product details
    // For now, redirect to product detail page
    window.location.href = `?page=product-detail&id=${productId}`;
}

// Wishlist functionality
function addToWishlist(productId) {
    const wishlist = JSON.parse(localStorage.getItem('wishlist') || '[]');
    
    if (!wishlist.includes(productId)) {
        wishlist.push(productId);
        localStorage.setItem('wishlist', JSON.stringify(wishlist));
        showNotification('Added to wishlist', 'success');
    } else {
        showNotification('Already in wishlist', 'info');
    }
}

// Light & Dark Theme Toggling Functionality
function initTheme() {
    const currentTheme = localStorage.getItem('theme') || 'light';
    if (currentTheme === 'dark') {
        document.body.classList.add('dark');
        updateThemeToggleIcons('dark');
    } else {
        document.body.classList.remove('dark');
        updateThemeToggleIcons('light');
    }
    
    // Bind click handlers to theme switches
    const themeToggles = document.querySelectorAll('.theme-toggle');
    themeToggles.forEach(toggle => {
        toggle.addEventListener('click', toggleTheme);
    });
}

function toggleTheme() {
    if (document.body.classList.contains('dark')) {
        document.body.classList.remove('dark');
        localStorage.setItem('theme', 'light');
        updateThemeToggleIcons('light');
        showNotification('Switched to Light Theme', 'info');
    } else {
        document.body.classList.add('dark');
        localStorage.setItem('theme', 'dark');
        updateThemeToggleIcons('dark');
        showNotification('Switched to Dark Theme', 'info');
    }
}

function updateThemeToggleIcons(theme) {
    const themeToggles = document.querySelectorAll('.theme-toggle');
    themeToggles.forEach(toggle => {
        const icon = toggle.querySelector('.theme-icon');
        if (icon) {
            if (theme === 'dark') {
                icon.textContent = '☀️'; // Sun icon to trigger light mode
                toggle.title = 'Switch to Light Mode';
            } else {
                icon.textContent = '🌙'; // Moon icon to trigger dark mode
                toggle.title = 'Switch to Dark Mode';
            }
        }
    });
}
