<?php
// Admin authentication check
if (!isLoggedIn()) {
    setNotification('Please login to access admin panel.', 'error');
    redirect('?page=login');
}

if (!isAdmin()) {
    setNotification('Access denied. Admin privileges required.', 'error');
    redirect('?page=home');
}

// Get API client
$apiClient = getApiClient();

// Handle form submissions
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['action'])) {
        switch ($_POST['action']) {
            case 'add_product':
                try {
                    $productData = [
                        'name' => $_POST['name'] ?? '',
                        'description' => $_POST['description'] ?? '',
                        'category' => $_POST['category'] ?? '',
                        'specifications' => !empty($_POST['specifications']) ? explode(',', $_POST['specifications']) : [],
                        'imageUrl' => $_POST['imageUrl'] ?? null,
                        'basePrice' => !empty($_POST['basePrice']) ? (float)$_POST['basePrice'] : null
                    ];
                    
                    $response = $apiClient->createProduct($productData);
                    if ($response) {
                        setNotification('Product added successfully!', 'success');
                    } else {
                        setNotification('Failed to add product.', 'error');
                    }
                } catch (Exception $e) {
                    setNotification('Failed to add product: ' . $e->getMessage(), 'error');
                }
                break;
                
            case 'update_product':
                try {
                    $productId = (int)$_POST['productId'];
                    $productData = [
                        'name' => $_POST['name'] ?? '',
                        'description' => $_POST['description'] ?? '',
                        'category' => $_POST['category'] ?? '',
                        'specifications' => !empty($_POST['specifications']) ? explode(',', $_POST['specifications']) : [],
                        'imageUrl' => $_POST['imageUrl'] ?? null,
                        'basePrice' => !empty($_POST['basePrice']) ? (float)$_POST['basePrice'] : null
                    ];
                    
                    $response = $apiClient->updateProduct($productId, $productData);
                    if ($response) {
                        setNotification('Product updated successfully!', 'success');
                    } else {
                        setNotification('Failed to update product.', 'error');
                    }
                } catch (Exception $e) {
                    setNotification('Failed to update product: ' . $e->getMessage(), 'error');
                }
                break;
                
            case 'delete_product':
                try {
                    $productId = (int)$_POST['productId'];
                    $response = $apiClient->deleteProduct($productId);
                    if ($response) {
                        setNotification('Product deleted successfully!', 'success');
                    } else {
                        setNotification('Failed to delete product.', 'error');
                    }
                } catch (Exception $e) {
                    setNotification('Failed to delete product: ' . $e->getMessage(), 'error');
                }
                break;
        }
        
        redirect('?page=admin-products');
    }
}

// Pagination and filtering
$page = (int)($_GET['p'] ?? 1);
$pageSize = 12;
$categoryFilter = $_GET['category'] ?? '';
$searchTerm = $_GET['search'] ?? '';

// Build query parameters
$queryParams = [
    'page' => $page,
    'pageSize' => $pageSize
];

if (!empty($categoryFilter)) {
    $queryParams['category'] = $categoryFilter;
}

if (!empty($searchTerm)) {
    $queryParams['search'] = $searchTerm;
}

$products = [];
$categories = [];
$pagination = [
    'currentPage' => 1,
    'totalPages' => 1,
    'totalCount' => 0,
    'hasNext' => false,
    'hasPrevious' => false
];

try {
    // Fetch products from API
    $productsResponse = $apiClient->getProducts($queryParams);
    error_log('Products API Response type: ' . gettype($productsResponse));
    
    if ($productsResponse && is_array($productsResponse)) {
        // API returns: {success, message, data: {data: [...products], pageNumber, pageSize, etc}}
        // So we need response.data.data for the products array
        if (isset($productsResponse['data']['data']) && is_array($productsResponse['data']['data'])) {
            // This is the correct path - data.data contains the products array
            $products = $productsResponse['data']['data'];
            error_log('Found products in data.data with ' . count($products) . ' products');
            
            // Pagination info is at data level
            $pagination = [
                'currentPage' => $productsResponse['data']['pageNumber'] ?? 1,
                'totalPages' => $productsResponse['data']['totalPages'] ?? 1,
                'totalCount' => $productsResponse['data']['totalCount'] ?? 0,
                'hasNext' => $productsResponse['data']['hasNextPage'] ?? false,
                'hasPrevious' => $productsResponse['data']['hasPreviousPage'] ?? false
            ];
        } else if (isset($productsResponse['data']) && is_array($productsResponse['data']) && isset($productsResponse['data'][0])) {
            // Fallback: data is direct array of products
            $products = $productsResponse['data'];
            error_log('Found products directly in data with ' . count($products) . ' products');
        } else if (isset($productsResponse[0]) && is_array($productsResponse[0])) {
            // Fallback: response is direct array of products
            $products = $productsResponse;
            error_log('Direct array with ' . count($products) . ' items');
        }
    }
    
    error_log('Successfully fetched products: ' . count($products) . ' products');
    
    // Fetch categories from API using the correct method
    $categoriesResponse = $apiClient->getProductCategories();
    if ($categoriesResponse && is_array($categoriesResponse)) {
        // Handle different response structures
        if (isset($categoriesResponse['data']) && is_array($categoriesResponse['data'])) {
            $categories = $categoriesResponse['data'];
        } else if (isset($categoriesResponse[0]) && is_array($categoriesResponse[0])) {
            $categories = $categoriesResponse;
        } else {
            // If it's a simple array of strings, convert to objects
            $categories = array_map(function($cat, $index) {
                if (is_array($cat)) {
                    return $cat;
                }
                return [
                    'categoryId' => $cat,
                    'id' => $cat,
                    'name' => $cat
                ];
            }, is_array($categoriesResponse) ? $categoriesResponse : [], array_keys(is_array($categoriesResponse) ? $categoriesResponse : []));
        }
    }
    error_log('Successfully fetched categories: ' . count($categories) . ' categories');
} catch (Exception $e) {
    // Log error or show error message
    error_log('Admin products API error: ' . $e->getMessage());
}

// Get edit product data if editing
$editProduct = null;
if (isset($_GET['edit'])) {
    $editId = (int)$_GET['edit'];
    $editProduct = array_filter($products, fn($p) => $p['productId'] == $editId);
    $editProduct = !empty($editProduct) ? array_values($editProduct)[0] : null;
}
?>

<div class="admin-products">
    <div class="container">
        <!-- Page Header -->
        <div class="page-header">
            <div class="header-left">
                <h1>🛍️ Product Management</h1>
                <p>Manage your product catalog</p>
            </div>
            <div class="header-right">
                <button onclick="showProductModal()" class="btn btn-primary">+ Add Product</button>
                <a href="?page=admin-dashboard" class="btn btn-outline">← Back to Dashboard</a>
            </div>
        </div>
        
        <!-- Filters and Search -->
        <div class="filters-section">
            <form method="GET" class="filters-form">
                <input type="hidden" name="page" value="admin-products">
                
                <div class="search-input">
                    <input type="text" name="search" placeholder="Search products..." 
                           value="<?php echo htmlspecialchars($searchTerm); ?>">
                    <button type="submit" class="search-btn">🔍</button>
                </div>
                
                <div class="filter-select">
                    <select name="category" onchange="this.form.submit()">
                        <option value="">All Categories</option>
                        <?php foreach ($categories as $category): 
                            $catName = is_array($category) ? ($category['name'] ?? 'Unknown') : $category;
                        ?>
                            <option value="<?php echo htmlspecialchars($catName); ?>" 
                                    <?php echo $categoryFilter == $catName ? 'selected' : ''; ?>>
                                <?php echo htmlspecialchars($catName); ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>
                
                <?php if (!empty($categoryFilter) || !empty($searchTerm)): ?>
                    <a href="?page=admin-products" class="btn btn-outline btn-sm">Clear Filters</a>
                <?php endif; ?>
            </form>
            
            <div class="results-info">
                <span><?php echo number_format($pagination['totalCount']); ?> products found</span>
            </div>
        </div>
        
        <!-- Products Grid -->
        <div class="products-grid">
            <?php if (!empty($products)): ?>
                <?php foreach ($products as $product): 
                    // Skip if product is not an array
                    if (!is_array($product)) {
                        error_log('Invalid product data type: ' . gettype($product));
                        continue;
                    }
                    
                    // Safe access to product fields with defaults
                    $prod_id = $product['productId'] ?? $product['id'] ?? 0;
                    $prod_name = $product['name'] ?? 'Unnamed Product';
                    $prod_brand = $product['brand'] ?? '';
                    $prod_price = (float)($product['price'] ?? $product['basePrice'] ?? 0);
                    $prod_image = $product['imageUrl'] ?? 'images/placeholder.jpg';
                    $prod_category = $product['category'] ?? $product['categoryId'] ?? 'Unknown';
                ?>
                    <div class="product-admin-card">
                        <div class="product-image">
                            <img src="<?php echo htmlspecialchars($prod_image); ?>" 
                                 alt="<?php echo htmlspecialchars($prod_name); ?>">
                            <div class="product-overlay">
                                <button onclick="editProduct(<?php echo htmlspecialchars(json_encode($product)); ?>)" 
                                        class="btn btn-sm btn-primary">Edit</button>
                                <button onclick="deleteProduct(<?php echo $prod_id; ?>, '<?php echo htmlspecialchars($prod_name); ?>')" 
                                        class="btn btn-sm btn-danger">Delete</button>
                            </div>
                        </div>
                        
                        <div class="product-info">
                            <h3><?php echo htmlspecialchars($prod_name); ?></h3>
                            <p class="product-brand"><?php echo htmlspecialchars($prod_brand); ?></p>
                            <div class="product-price"><?php echo CURRENCY_SYMBOL . ' ' . number_format($prod_price, 2); ?></div>
                            
                            <div class="product-meta">
                                <span class="category-badge">
                                    <?php echo htmlspecialchars($prod_category); ?>
                                </span>
                                <span class="product-id">#<?php echo $prod_id; ?></span>
                            </div>
                        </div>
                    </div>
                <?php endforeach; ?>
            <?php else: ?>
                <div class="empty-state">
                    <div class="empty-icon">🛍️</div>
                    <h3>No products found</h3>
                    <p>There are no products matching your current filters.</p>
                    <button onclick="showProductModal()" class="btn btn-primary">Add First Product</button>
                </div>
            <?php endif; ?>
        </div>
        
        <!-- Pagination -->
        <?php if ($pagination['totalPages'] > 1): ?>
            <div class="pagination">
                <?php if ($pagination['hasPrevious']): ?>
                    <a href="?page=admin-products&p=<?php echo $pagination['currentPage'] - 1; ?><?php echo !empty($categoryFilter) ? "&category={$categoryFilter}" : ''; ?><?php echo !empty($searchTerm) ? "&search=" . urlencode($searchTerm) : ''; ?>" 
                       class="btn btn-outline">← Previous</a>
                <?php endif; ?>
                
                <span class="page-info">
                    Page <?php echo $pagination['currentPage']; ?> of <?php echo $pagination['totalPages']; ?>
                </span>
                
                <?php if ($pagination['hasNext']): ?>
                    <a href="?page=admin-products&p=<?php echo $pagination['currentPage'] + 1; ?><?php echo !empty($categoryFilter) ? "&category={$categoryFilter}" : ''; ?><?php echo !empty($searchTerm) ? "&search=" . urlencode($searchTerm) : ''; ?>" 
                       class="btn btn-outline">Next →</a>
                <?php endif; ?>
            </div>
        <?php endif; ?>
    </div>
</div>

<!-- Product Modal -->
<div id="productModal" class="modal">
    <div class="modal-content">
        <div class="modal-header">
            <h2 id="modalTitle">Add Product</h2>
            <button onclick="closeProductModal()" class="close-btn">×</button>
        </div>
        
        <form id="productForm" method="POST" class="modal-body">
            <input type="hidden" id="productAction" name="action" value="add_product">
            <input type="hidden" id="productId" name="productId" value="">
            
            <div class="form-grid">
                <div class="form-group">
                    <label for="productName">Product Name *</label>
                    <input type="text" id="productName" name="name" required>
                </div>
                
                <div class="form-group">
                    <label for="productCategory">Category *</label>
                    <select id="productCategory" name="category" required>
                        <option value="">Select Category</option>
                        <?php 
                        error_log('Categories available in modal: ' . count($categories));
                        foreach ($categories as $idx => $category): 
                            $catName = is_array($category) ? ($category['name'] ?? 'Unknown') : $category;
                        ?>
                            <option value="<?php echo htmlspecialchars($catName); ?>">
                                <?php echo htmlspecialchars($catName); ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>
                
                <div class="form-group">
                    <label for="productPrice">Base Price (<?php echo CURRENCY_SYMBOL; ?>)</label>
                    <input type="number" id="productPrice" name="basePrice" step="0.01" min="0">
                </div>
                
                <div class="form-group">
                    <label for="productImage">Image URL</label>
                    <input type="url" id="productImage" name="imageUrl" placeholder="https://example.com/image.jpg">
                </div>
            </div>
            
            <div class="form-group full-width">
                <label for="productDescription">Description</label>
                <textarea id="productDescription" name="description" rows="4"></textarea>
            </div>
            
            <div class="form-group full-width">
                <label for="productSpecs">Specifications</label>
                <textarea id="productSpecs" name="specifications" rows="4" 
                          placeholder="Enter specifications in JSON format or plain text"></textarea>
            </div>
            
            <div class="modal-footer">
                <button type="button" onclick="closeProductModal()" class="btn btn-outline">Cancel</button>
                <button type="submit" class="btn btn-primary">Save Product</button>
            </div>
        </form>
    </div>
</div>

<!-- Delete Confirmation Modal -->
<div id="deleteModal" class="modal">
    <div class="modal-content">
        <div class="modal-header">
            <h2>Confirm Delete</h2>
            <button onclick="closeDeleteModal()" class="close-btn">×</button>
        </div>
        <div class="modal-body">
            <p>Are you sure you want to delete "<span id="deleteProductName"></span>"?</p>
            <p class="warning-text">This action cannot be undone.</p>
            
            <form id="deleteForm" method="POST">
                <input type="hidden" name="action" value="delete_product">
                <input type="hidden" id="deleteProductId" name="productId" value="">
                
                <div class="modal-footer">
                    <button type="button" onclick="closeDeleteModal()" class="btn btn-outline">Cancel</button>
                    <button type="submit" class="btn btn-danger">Delete Product</button>
                </div>
            </form>
        </div>
    </div>
</div>

<style>
.admin-products {
    background-color: #f8f9fa;
    min-height: 100vh;
    padding: 2rem 0;
}

.page-header {
    display: flex;
    justify-content: space-between;
    align-items: center;
    margin-bottom: 2rem;
    flex-wrap: wrap;
    gap: 1rem;
}

.page-header h1 {
    margin: 0;
    font-size: 2rem;
    color: #333;
}

.page-header p {
    margin: 0.5rem 0 0 0;
    color: #6c757d;
}

.header-right {
    display: flex;
    gap: 1rem;
    flex-wrap: wrap;
}

.filters-section {
    background: white;
    border-radius: 12px;
    padding: 1.5rem;
    margin-bottom: 2rem;
    box-shadow: 0 4px 12px rgba(0, 0, 0, 0.1);
    display: flex;
    justify-content: space-between;
    align-items: center;
    flex-wrap: wrap;
    gap: 1rem;
}

.filters-form {
    display: flex;
    gap: 1rem;
    align-items: center;
    flex-wrap: wrap;
}

.search-input {
    position: relative;
    display: flex;
}

.search-input input {
    padding: 0.75rem 3rem 0.75rem 1rem;
    border: 2px solid #e9ecef;
    border-radius: 8px;
    font-size: 1rem;
    width: 250px;
}

.search-btn {
    position: absolute;
    right: 0;
    top: 0;
    height: 100%;
    background: transparent;
    border: none;
    padding: 0 1rem;
    cursor: pointer;
}

.filter-select select {
    padding: 0.75rem 1rem;
    border: 2px solid #e9ecef;
    border-radius: 8px;
    font-size: 1rem;
    background: white;
    cursor: pointer;
}

.products-grid {
    display: grid;
    grid-template-columns: repeat(auto-fill, minmax(280px, 1fr));
    gap: 1.5rem;
    margin-bottom: 2rem;
}

.product-admin-card {
    background: white;
    border-radius: 12px;
    overflow: hidden;
    box-shadow: 0 4px 12px rgba(0, 0, 0, 0.1);
    transition: transform 0.2s, box-shadow 0.2s;
}

.product-admin-card:hover {
    transform: translateY(-4px);
    box-shadow: 0 8px 24px rgba(0, 0, 0, 0.15);
}

.product-image {
    position: relative;
    height: 200px;
    overflow: hidden;
}

.product-image img {
    width: 100%;
    height: 100%;
    object-fit: cover;
}

.product-overlay {
    position: absolute;
    top: 0;
    left: 0;
    right: 0;
    bottom: 0;
    background: rgba(0, 0, 0, 0.7);
    display: flex;
    gap: 0.5rem;
    align-items: center;
    justify-content: center;
    opacity: 0;
    transition: opacity 0.2s;
}

.product-admin-card:hover .product-overlay {
    opacity: 1;
}

.product-info {
    padding: 1.5rem;
}

.product-info h3 {
    margin: 0 0 0.5rem 0;
    font-size: 1.2rem;
    color: #333;
}

.product-brand {
    margin: 0 0 0.75rem 0;
    color: #6c757d;
    font-size: 0.9rem;
}

.product-price {
    font-size: 1.25rem;
    font-weight: 700;
    color: #007bff;
    margin-bottom: 1rem;
}

.product-meta {
    display: flex;
    justify-content: space-between;
    align-items: center;
}

.category-badge {
    background: #e9ecef;
    color: #495057;
    padding: 0.25rem 0.75rem;
    border-radius: 12px;
    font-size: 0.8rem;
    font-weight: 500;
}

.product-id {
    color: #6c757d;
    font-size: 0.9rem;
    font-weight: 500;
}

.empty-state {
    grid-column: 1 / -1;
    text-align: center;
    padding: 4rem 2rem;
    color: #6c757d;
}

.empty-icon {
    font-size: 4rem;
    margin-bottom: 1rem;
    opacity: 0.5;
}

.modal {
    display: none;
    position: fixed;
    z-index: 1000;
    left: 0;
    top: 0;
    width: 100%;
    height: 100%;
    background-color: rgba(0, 0, 0, 0.5);
}

.modal-content {
    background-color: white;
    margin: 2% auto;
    border-radius: 12px;
    width: 90%;
    max-width: 600px;
    max-height: 90vh;
    overflow-y: auto;
}

.modal-header {
    padding: 1.5rem;
    border-bottom: 1px solid #e9ecef;
    display: flex;
    justify-content: space-between;
    align-items: center;
}

.modal-header h2 {
    margin: 0;
    color: #333;
}

.close-btn {
    background: none;
    border: none;
    font-size: 1.5rem;
    cursor: pointer;
    color: #6c757d;
}

.modal-body {
    padding: 1.5rem;
}

.form-grid {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
    gap: 1rem;
    margin-bottom: 1rem;
}

.form-group {
    margin-bottom: 1rem;
}

.form-group.full-width {
    grid-column: 1 / -1;
}

.form-group label {
    display: block;
    margin-bottom: 0.5rem;
    font-weight: 600;
    color: #333;
}

.form-group input,
.form-group select,
.form-group textarea {
    width: 100%;
    padding: 0.75rem;
    border: 2px solid #e9ecef;
    border-radius: 8px;
    font-size: 1rem;
}

.form-group textarea {
    resize: vertical;
}

.modal-footer {
    padding: 1.5rem;
    border-top: 1px solid #e9ecef;
    display: flex;
    gap: 1rem;
    justify-content: flex-end;
}

.warning-text {
    color: #dc3545;
    font-weight: 500;
}

.pagination {
    display: flex;
    justify-content: center;
    align-items: center;
    gap: 1rem;
}

.page-info {
    padding: 0.75rem 1rem;
    background: white;
    border-radius: 8px;
    font-weight: 600;
    color: #333;
}

@media (max-width: 768px) {
    .page-header {
        text-align: center;
    }
    
    .header-right {
        justify-content: center;
    }
    
    .filters-section {
        flex-direction: column;
        align-items: stretch;
    }
    
    .filters-form {
        justify-content: center;
    }
    
    .search-input input {
        width: 100%;
        max-width: 300px;
    }
    
    .products-grid {
        grid-template-columns: 1fr;
    }
    
    .form-grid {
        grid-template-columns: 1fr;
    }
    
    .modal-footer {
        flex-direction: column;
    }
}
</style>

<script>
function showProductModal(product = null) {
    const modal = document.getElementById('productModal');
    const form = document.getElementById('productForm');
    const title = document.getElementById('modalTitle');
    
    if (product) {
        // Edit mode
        title.textContent = 'Edit Product';
        document.getElementById('productAction').value = 'update_product';
        document.getElementById('productId').value = product.productId;
        document.getElementById('productName').value = product.name;
        document.getElementById('productPrice').value = product.basePrice || product.price || '';
        document.getElementById('productCategory').value = product.category || '';
        document.getElementById('productImage').value = product.imageUrl || '';
        document.getElementById('productDescription').value = product.description || '';
        document.getElementById('productSpecs').value = product.specifications ? product.specifications.join(',') : '';
    } else {
        // Add mode
        title.textContent = 'Add Product';
        document.getElementById('productAction').value = 'add_product';
        form.reset();
    }
    
    modal.style.display = 'block';
}

function editProduct(product) {
    showProductModal(product);
}

function closeProductModal() {
    document.getElementById('productModal').style.display = 'none';
}

function deleteProduct(productId, productName) {
    document.getElementById('deleteProductId').value = productId;
    document.getElementById('deleteProductName').textContent = productName;
    document.getElementById('deleteModal').style.display = 'block';
}

function closeDeleteModal() {
    document.getElementById('deleteModal').style.display = 'none';
}

// Close modals when clicking outside
window.onclick = function(event) {
    const productModal = document.getElementById('productModal');
    const deleteModal = document.getElementById('deleteModal');
    
    if (event.target === productModal) {
        closeProductModal();
    }
    if (event.target === deleteModal) {
        closeDeleteModal();
    }
}

// Form validation
document.getElementById('productForm').addEventListener('submit', function(e) {
    const name = document.getElementById('productName').value.trim();
    const price = parseFloat(document.getElementById('productPrice').value);
    
    if (!name) {
        alert('Product name is required.');
        e.preventDefault();
        return;
    }
    
    if (isNaN(price) || price <= 0) {
        alert('Please enter a valid price.');
        e.preventDefault();
        return;
    }
});
</script>
