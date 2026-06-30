// API_BASE_URL is dynamically defined in the layouts from the PHP constant

/**
 * Generic API call function
 */
async function apiCall(method, endpoint, data = null) {
    const url = `${API_BASE_URL}${endpoint}`;
    const token = localStorage.getItem('auth_token');
    
    const options = {
        method: method,
        headers: {
            'Content-Type': 'application/json',
            'Accept': 'application/json'
        }
    };

    if (token) {
        options.headers['Authorization'] = `Bearer ${token}`;
    }

    if (data && (method === 'POST' || method === 'PUT')) {
        options.body = JSON.stringify(data);
    }

    try {
        showLoadingSpinner();
        const response = await fetch(url, options);
        
        if (response.status === 401) {
            handleUnauthorized();
            return null;
        }

        const result = await response.json();
        
        if (!response.ok) {
            handleApiError(result, response.status);
            return null;
        }

        return result;
    } catch (error) {
        console.error('API Error:', error);
        showNotification('Network error occurred', 'error');
        return null;
    } finally {
        hideLoadingSpinner();
    }
}

async function apiGet(endpoint, params = {}) {
    const queryString = new URLSearchParams(params).toString();
    const url = queryString ? `${endpoint}?${queryString}` : endpoint;
    return apiCall('GET', url);
}

async function apiPost(endpoint, data) {
    return apiCall('POST', endpoint, data);
}

async function apiPut(endpoint, data) {
    return apiCall('PUT', endpoint, data);
}

async function apiDelete(endpoint) {
    return apiCall('DELETE', endpoint);
}

function handleApiError(error, statusCode) {
    let message = 'An error occurred';
    
    if (error && error.message) {
        message = error.message;
    } else {
        switch (statusCode) {
            case 400:
                message = 'Invalid request. Please check your input.';
                break;
            case 404:
                message = 'The requested resource was not found.';
                break;
            case 409:
                message = 'This operation conflicts with existing data.';
                break;
            case 422:
                message = 'Validation failed. Please check your input.';
                break;
            case 429:
                message = 'Too many requests. Please try again later.';
                break;
            case 500:
                message = 'Server error. Please try again later.';
                break;
        }
    }
    
    showNotification(message, 'error');
}

function handleUnauthorized() {
    localStorage.removeItem('auth_token');
    showNotification('Your session has expired. Please log in again.', 'warning');
    
    // Redirect to login after a short delay
    setTimeout(() => {
        window.location.href = '?page=login';
    }, 2000);
}

/**
 * Search products with autocomplete
 */
async function searchProducts(query) {
    if (query.length < 2) {
        return [];
    }
    
    const results = await apiGet('/products/search', { q: query, limit: 5 });
    return results || [];
}

/**
 * Setup search autocomplete
 */
function setupSearchAutocomplete() {
    const searchInput = document.querySelector('input[name="search"]');
    if (!searchInput) return;
    
    let searchTimeout;
    
    searchInput.addEventListener('input', function() {
        const query = this.value.trim();
        
        clearTimeout(searchTimeout);
        
        if (query.length < 2) {
            hideSearchSuggestions();
            return;
        }
        
        searchTimeout = setTimeout(async () => {
            const suggestions = await searchProducts(query);
            showSearchSuggestions(suggestions, searchInput);
        }, 300);
    });
    
    // Hide suggestions when clicking outside
    document.addEventListener('click', function(e) {
        if (!searchInput.contains(e.target)) {
            hideSearchSuggestions();
        }
    });
}

function showSearchSuggestions(suggestions, inputElement) {
    hideSearchSuggestions();
    
    if (!suggestions || suggestions.length === 0) return;
    
    const container = document.createElement('div');
    container.className = 'search-suggestions';
    container.id = 'searchSuggestions';
    
    suggestions.forEach(product => {
        const suggestion = document.createElement('div');
        suggestion.className = 'search-suggestion-item';
        suggestion.innerHTML = `
            <img src="${product.imageUrl || 'images/placeholder.jpg'}" alt="${product.name}">
            <div class="suggestion-info">
                <h4>${product.name}</h4>
                <p>${formatCurrency(product.price)}</p>
            </div>
        `;
        
        suggestion.addEventListener('click', () => {
            window.location.href = `?page=product-detail&id=${product.id}`;
        });
        
        container.appendChild(suggestion);
    });
    
    // Position the suggestions below the input
    const rect = inputElement.getBoundingClientRect();
    container.style.position = 'absolute';
    container.style.top = (rect.bottom + window.scrollY) + 'px';
    container.style.left = rect.left + 'px';
    container.style.width = rect.width + 'px';
    
    document.body.appendChild(container);
}

function hideSearchSuggestions() {
    const suggestions = document.getElementById('searchSuggestions');
    if (suggestions) {
        suggestions.remove();
    }
}

/**
 * Format currency
 */
function formatCurrency(amount) {
    return new Intl.NumberFormat('en-US', {
        style: 'currency',
        currency: 'USD'
    }).format(amount);
}

// Initialize when DOM is ready
document.addEventListener('DOMContentLoaded', function() {
    setupSearchAutocomplete();
});
