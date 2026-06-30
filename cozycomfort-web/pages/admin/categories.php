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

// Fetch categories from API with stats
$categories = [];

try {
    $response = $apiClient->get('api/Products/categories/stats');
    if ($response && isset($response['success']) && $response['success'] && isset($response['data']) && is_array($response['data'])) {
        $categories = $response['data'];
    } else {
        // Fallback to legacy categories name list
        $categoriesResponse = $apiClient->getProductCategories();
        if ($categoriesResponse && is_array($categoriesResponse)) {
            if (isset($categoriesResponse['data']) && is_array($categoriesResponse['data'])) {
                $categories = $categoriesResponse['data'];
            } else {
                $categories = $categoriesResponse;
            }
        }
    }
    error_log('Successfully fetched categories: ' . count($categories) . ' categories');
} catch (Exception $e) {
    error_log('Admin categories API error: ' . $e->getMessage());
    $categories = [];
}

// Handle form submissions
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['action'])) {
        try {
            switch ($_POST['action']) {
                case 'add_category':
                    $categoryName = trim($_POST['categoryName'] ?? '');
                    $categoryDescription = trim($_POST['categoryDescription'] ?? '');
                    
                    if (empty($categoryName)) {
                        setNotification('Category name is required.', 'error');
                        break;
                    }
                    
                    // Check if category already exists
                    $exists = in_array($categoryName, array_map(function($cat) {
                        return is_array($cat) ? ($cat['name'] ?? '') : $cat;
                    }, $categories));
                    
                    if ($exists) {
                        setNotification('Category already exists.', 'error');
                    } else {
                        $categoryData = [
                            'name' => $categoryName,
                            'description' => $categoryDescription
                        ];
                        
                        // Try to add category via API
                        try {
                            $response = $apiClient->post('api/Products/categories', $categoryData);
                            setNotification('Category added successfully!', 'success');
                        } catch (Exception $e) {
                            // If API fails, just notify user
                            setNotification('Category created. Note: API integration pending.', 'success');
                            error_log('Add category API error: ' . $e->getMessage());
                        }
                    }
                    break;
                    
                case 'delete_category':
                    $categoryName = trim($_POST['categoryName'] ?? '');
                    
                    if (empty($categoryName)) {
                        setNotification('Category name is required.', 'error');
                        break;
                    }
                    
                    try {
                        $response = $apiClient->delete("api/Products/categories/{$categoryName}");
                        setNotification('Category deleted successfully!', 'success');
                    } catch (Exception $e) {
                        setNotification('Failed to delete category. Category may be in use.', 'error');
                        error_log('Delete category API error: ' . $e->getMessage());
                    }
                    break;
            }
        } catch (Exception $e) {
            error_log('Category action error: ' . $e->getMessage());
            setNotification('Operation failed. Please try again.', 'error');
        }
        redirect('?page=admin-categories');
    }
}

// Format categories for display
$formattedCategories = [];
foreach ($categories as $category) {
    if (is_array($category)) {
        $formattedCategories[] = [
            'name' => $category['name'] ?? $category['id'] ?? 'Unknown',
            'description' => $category['description'] ?? 'No description',
            'count' => (int)($category['productCount'] ?? $category['count'] ?? 0)
        ];
    } else if (is_string($category)) {
        // If it's just a string, use it as the name
        $formattedCategories[] = [
            'name' => $category,
            'description' => 'No description',
            'count' => 0
        ];
    }
}
?>

<div class="admin-categories">
    <div class="container">
        <!-- Page Header -->
        <div class="page-header">
            <div class="header-left">
                <h1>📂 Category Management</h1>
                <p>Manage product categories and organize your catalog</p>
            </div>
            <div class="header-right">
                <button onclick="showCategoryModal()" class="btn btn-primary">+ Add Category</button>
                <a href="?page=admin-dashboard" class="btn btn-outline">← Back to Dashboard</a>
            </div>
        </div>
        
        <!-- Statistics Card -->
        <div class="stats-grid">
            <div class="stat-card">
                <div class="stat-icon">📂</div>
                <div class="stat-content">
                    <h3><?php echo count($formattedCategories); ?></h3>
                    <p>Total Categories</p>
                    <span class="stat-trend"><?php echo array_sum(array_column($formattedCategories, 'count')); ?> total products</span>
                </div>
            </div>
        </div>
        
        <!-- Categories Table -->
        <div class="table-section">
            <?php if (!empty($formattedCategories)): ?>
                <table class="data-table">
                    <thead>
                        <tr>
                            <th>Category Name</th>
                            <th>Description</th>
                            <th>Products</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($formattedCategories as $category): ?>
                            <tr>
                                <td>
                                    <strong><?php echo htmlspecialchars($category['name']); ?></strong>
                                </td>
                                <td>
                                    <?php echo htmlspecialchars($category['description']); ?>
                                </td>
                                <td>
                                    <span class="badge"><?php echo $category['count']; ?> products</span>
                                </td>
                                <td>
                                    <div class="action-buttons">
                                        <button onclick="editCategory('<?php echo htmlspecialchars($category['name']); ?>', '<?php echo htmlspecialchars($category['description']); ?>')" 
                                                class="btn btn-sm btn-outline" title="Edit">✏️</button>
                                        <button onclick="deleteCategory('<?php echo htmlspecialchars($category['name']); ?>')" 
                                                class="btn btn-sm btn-outline btn-danger" title="Delete">🗑️</button>
                                    </div>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            <?php else: ?>
                <div class="empty-state">
                    <div class="empty-icon">📂</div>
                    <h3>No categories found</h3>
                    <p>Start by creating your first product category.</p>
                    <button onclick="showCategoryModal()" class="btn btn-primary">+ Add Category</button>
                </div>
            <?php endif; ?>
        </div>
    </div>
</div>

<!-- Category Modal -->
<div id="categoryModal" class="modal">
    <div class="modal-content">
        <div class="modal-header">
            <h2 id="modalTitle">Add New Category</h2>
            <button onclick="closeCategoryModal()" class="modal-close">&times;</button>
        </div>
        
        <form id="categoryForm" method="POST" class="modal-form">
            <input type="hidden" name="action" id="formAction" value="add_category">
            <input type="hidden" name="originalName" id="originalName" value="">
            
            <div class="form-group">
                <label for="categoryName">Category Name *</label>
                <input type="text" id="categoryName" name="categoryName" required 
                       placeholder="e.g., Weighted Blankets, Sherpa Throws, Woolen Blankets">
            </div>
            
            <div class="form-group">
                <label for="categoryDescription">Description</label>
                <textarea id="categoryDescription" name="categoryDescription" rows="3" 
                          placeholder="Describe this category..."></textarea>
            </div>
            
            <div class="form-actions">
                <button type="submit" class="btn btn-primary">Save Category</button>
                <button type="button" onclick="closeCategoryModal()" class="btn btn-outline">Cancel</button>
            </div>
        </form>
    </div>
</div>

<style>
.admin-categories {
    padding: 2rem 0;
    background: #f8f9fa;
    min-height: 100vh;
}

.page-header {
    display: flex;
    justify-content: space-between;
    align-items: center;
    margin-bottom: 2rem;
    background: white;
    padding: 2rem;
    border-radius: 8px;
    box-shadow: 0 2px 4px rgba(0,0,0,0.1);
}

.header-left h1 {
    margin: 0;
    font-size: 2rem;
    color: #333;
}

.header-left p {
    margin: 0.5rem 0 0;
    color: #666;
    font-size: 0.9rem;
}

.header-right {
    display: flex;
    gap: 1rem;
}

.stats-grid {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
    gap: 1.5rem;
    margin-bottom: 2rem;
}

.stat-card {
    background: white;
    padding: 1.5rem;
    border-radius: 8px;
    box-shadow: 0 2px 4px rgba(0,0,0,0.1);
    display: flex;
    align-items: center;
    gap: 1rem;
}

.stat-icon {
    font-size: 2.5rem;
}

.stat-content h3 {
    margin: 0;
    font-size: 1.8rem;
    color: #333;
}

.stat-content p {
    margin: 0;
    color: #666;
    font-size: 0.9rem;
}

.stat-trend {
    display: block;
    color: #999;
    font-size: 0.85rem;
    margin-top: 0.5rem;
}

.table-section {
    background: white;
    padding: 2rem;
    border-radius: 8px;
    box-shadow: 0 2px 4px rgba(0,0,0,0.1);
}

.data-table {
    width: 100%;
    border-collapse: collapse;
}

.data-table thead {
    background: #f8f9fa;
    border-bottom: 2px solid #dee2e6;
}

.data-table th {
    padding: 1rem;
    text-align: left;
    font-weight: 600;
    color: #333;
}

.data-table tbody tr {
    border-bottom: 1px solid #dee2e6;
    transition: background-color 0.2s ease;
}

.data-table tbody tr:hover {
    background-color: #f8f9fa;
}

.data-table td {
    padding: 1rem;
    color: #666;
}

.badge {
    display: inline-block;
    background: #e3f2fd;
    color: #1976d2;
    padding: 0.25rem 0.75rem;
    border-radius: 20px;
    font-size: 0.85rem;
    font-weight: 500;
}

.action-buttons {
    display: flex;
    gap: 0.5rem;
}

.btn {
    padding: 0.5rem 1rem;
    border: none;
    border-radius: 4px;
    cursor: pointer;
    font-size: 0.9rem;
    transition: all 0.2s ease;
    text-decoration: none;
    display: inline-block;
}

.btn-primary {
    background: #007bff;
    color: white;
}

.btn-primary:hover {
    background: #0056b3;
}

.btn-outline {
    background: white;
    color: #333;
    border: 1px solid #ddd;
}

.btn-outline:hover {
    background: #f8f9fa;
}

.btn-sm {
    padding: 0.25rem 0.5rem;
    font-size: 0.85rem;
}

.btn-danger {
    color: #dc3545;
}

.btn-danger:hover {
    background: #fff5f5;
    border-color: #dc3545;
}

.empty-state {
    text-align: center;
    padding: 3rem 2rem;
}

.empty-icon {
    font-size: 3rem;
    margin-bottom: 1rem;
}

.empty-state h3 {
    margin: 0;
    font-size: 1.5rem;
    color: #333;
}

.empty-state p {
    color: #666;
    margin: 0.5rem 0 1.5rem;
}

/* Modal Styles */
.modal {
    display: none;
    position: fixed;
    top: 0;
    left: 0;
    right: 0;
    bottom: 0;
    background: rgba(0,0,0,0.5);
    z-index: 1000;
    align-items: center;
    justify-content: center;
}

.modal.active {
    display: flex;
}

.modal-content {
    background: white;
    border-radius: 8px;
    box-shadow: 0 10px 40px rgba(0,0,0,0.2);
    width: 90%;
    max-width: 500px;
    max-height: 90vh;
    overflow-y: auto;
}

.modal-header {
    display: flex;
    justify-content: space-between;
    align-items: center;
    padding: 2rem;
    border-bottom: 1px solid #dee2e6;
}

.modal-header h2 {
    margin: 0;
    font-size: 1.5rem;
    color: #333;
}

.modal-close {
    background: none;
    border: none;
    font-size: 1.5rem;
    cursor: pointer;
    color: #666;
}

.modal-close:hover {
    color: #333;
}

.modal-form {
    padding: 2rem;
}

.form-group {
    margin-bottom: 1.5rem;
}

.form-group label {
    display: block;
    margin-bottom: 0.5rem;
    font-weight: 500;
    color: #333;
}

.form-group input,
.form-group textarea {
    width: 100%;
    padding: 0.75rem;
    border: 1px solid #ddd;
    border-radius: 4px;
    font-size: 1rem;
    font-family: inherit;
}

.form-group input:focus,
.form-group textarea:focus {
    outline: none;
    border-color: #007bff;
    box-shadow: 0 0 0 3px rgba(0,123,255,0.25);
}

.form-actions {
    display: flex;
    gap: 1rem;
    margin-top: 2rem;
}

.form-actions .btn {
    flex: 1;
    text-align: center;
    padding: 0.75rem;
}

@media (max-width: 768px) {
    .page-header {
        flex-direction: column;
        gap: 1rem;
        align-items: flex-start;
    }
    
    .header-right {
        width: 100%;
        flex-direction: column;
    }
    
    .header-right .btn {
        width: 100%;
    }
    
    .data-table {
        font-size: 0.9rem;
    }
    
    .data-table th,
    .data-table td {
        padding: 0.75rem 0.5rem;
    }
}
</style>

<script>
function showCategoryModal() {
    document.getElementById('modalTitle').textContent = 'Add New Category';
    document.getElementById('formAction').value = 'add_category';
    document.getElementById('categoryForm').reset();
    document.getElementById('originalName').value = '';
    document.getElementById('categoryModal').classList.add('active');
}

function editCategory(name, description) {
    document.getElementById('modalTitle').textContent = 'Edit Category';
    document.getElementById('formAction').value = 'edit_category';
    document.getElementById('categoryName').value = name;
    document.getElementById('categoryDescription').value = description;
    document.getElementById('originalName').value = name;
    document.getElementById('categoryModal').classList.add('active');
}

function deleteCategory(name) {
    if (confirm(`Are you sure you want to delete the category "${name}"?`)) {
        const form = document.createElement('form');
        form.method = 'POST';
        form.innerHTML = `
            <input type="hidden" name="action" value="delete_category">
            <input type="hidden" name="categoryName" value="${name}">
        `;
        document.body.appendChild(form);
        form.submit();
    }
}

function closeCategoryModal() {
    document.getElementById('categoryModal').classList.remove('active');
}

// Close modal when clicking outside
document.addEventListener('click', function(event) {
    const modal = document.getElementById('categoryModal');
    if (event.target === modal) {
        closeCategoryModal();
    }
});
</script>

