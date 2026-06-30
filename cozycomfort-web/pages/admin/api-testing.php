<?php
// Ensure admin access
if (!isAdmin()) {
    redirect('?page=home');
}

$apiClient = getApiClient();
$testResults = [];

// API Testing Functions
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['test_endpoint'])) {
    $endpoint = $_POST['test_endpoint'];
    
    try {
        switch ($endpoint) {
            // Products API Tests
            case 'products_list':
                $testResults['products_list'] = $apiClient->getProducts(['PageSize' => 5]);
                break;
                
            case 'products_categories':
                $testResults['products_categories'] = $apiClient->getProductCategories();
                break;
                
            case 'product_by_id':
                $productId = $_POST['product_id'] ?? 1;
                $testResults['product_by_id'] = $apiClient->getProductById($productId);
                break;
                
            // Orders API Tests
            case 'orders_list':
                $testResults['orders_list'] = $apiClient->getOrders(null, 1, 5);
                break;
                
            case 'order_by_id':
                $orderId = $_POST['order_id'] ?? 1;
                $testResults['order_by_id'] = $apiClient->getOrderById($orderId);
                break;
                
            case 'customer_orders':
                $customerId = $_POST['customer_id'] ?? 1;
                $testResults['customer_orders'] = $apiClient->getCustomerOrders($customerId, 1, 5);
                break;
                
            // Distributors API Tests
            case 'distributors_list':
                $testResults['distributors_list'] = $apiClient->getDistributors();
                break;
                
            case 'distributor_by_id':
                $distributorId = $_POST['distributor_id'] ?? 1;
                $testResults['distributor_by_id'] = $apiClient->getDistributorById($distributorId);
                break;
                
            case 'distributor_availability':
                $distributorId = $_POST['distributor_id'] ?? 1;
                $testResults['distributor_availability'] = $apiClient->checkDistributorAvailability($distributorId);
                break;
                
            case 'distributor_performance':
                $distributorId = $_POST['distributor_id'] ?? 1;
                $testResults['distributor_performance'] = $apiClient->getDistributorPerformance($distributorId);
                break;
                
            // Quotations API Tests
            case 'quotation_comparison':
                $orderId = $_POST['order_id'] ?? 1;
                $testResults['quotation_comparison'] = $apiClient->getQuotationComparison($orderId);
                break;
                
            case 'quotation_history':
                $orderId = $_POST['order_id'] ?? 1;
                $testResults['quotation_history'] = $apiClient->getQuotationHistory($orderId);
                break;
        }
    } catch (Exception $e) {
        $testResults[$endpoint] = [
            'error' => true,
            'message' => $e->getMessage(),
            'code' => $e->getCode()
        ];
    }
}
?>

<div class="api-testing-page">
    <div class="container">
        <!-- Page Header -->
        <div class="page-header">
            <div class="header-left">
                <h1>🔧 API Testing Dashboard</h1>
                <p>Test all available CozyComfort API endpoints</p>
            </div>
            <div class="header-right">
                <a href="?page=admin-dashboard" class="btn btn-outline">← Back to Dashboard</a>
            </div>
        </div>

        <!-- API Endpoints Grid -->
        <div class="api-sections">
            <!-- Products API Section -->
            <div class="api-section">
                <h2>📦 Products API</h2>
                <div class="api-endpoints">
                    <div class="endpoint-card">
                        <h3>GET /api/Products</h3>
                        <p>Get all products with filtering and pagination</p>
                        <form method="POST" class="test-form">
                            <input type="hidden" name="test_endpoint" value="products_list">
                            <button type="submit" class="btn btn-primary">Test Endpoint</button>
                        </form>
                    </div>
                    
                    <div class="endpoint-card">
                        <h3>GET /api/Products/categories</h3>
                        <p>Get all product categories</p>
                        <form method="POST" class="test-form">
                            <input type="hidden" name="test_endpoint" value="products_categories">
                            <button type="submit" class="btn btn-primary">Test Endpoint</button>
                        </form>
                    </div>
                    
                    <div class="endpoint-card">
                        <h3>GET /api/Products/{id}</h3>
                        <p>Get product by ID</p>
                        <form method="POST" class="test-form">
                            <input type="hidden" name="test_endpoint" value="product_by_id">
                            <div class="form-group">
                                <label>Product ID:</label>
                                <input type="number" name="product_id" value="1" min="1" required>
                            </div>
                            <button type="submit" class="btn btn-primary">Test Endpoint</button>
                        </form>
                    </div>
                </div>
            </div>

            <!-- Orders API Section -->
            <div class="api-section">
                <h2>📋 Orders API</h2>
                <div class="api-endpoints">
                    <div class="endpoint-card">
                        <h3>GET /api/Orders</h3>
                        <p>Get all orders with pagination</p>
                        <form method="POST" class="test-form">
                            <input type="hidden" name="test_endpoint" value="orders_list">
                            <button type="submit" class="btn btn-primary">Test Endpoint</button>
                        </form>
                    </div>
                    
                    <div class="endpoint-card">
                        <h3>GET /api/Orders/{id}</h3>
                        <p>Get order by ID</p>
                        <form method="POST" class="test-form">
                            <input type="hidden" name="test_endpoint" value="order_by_id">
                            <div class="form-group">
                                <label>Order ID:</label>
                                <input type="number" name="order_id" value="1" min="1" required>
                            </div>
                            <button type="submit" class="btn btn-primary">Test Endpoint</button>
                        </form>
                    </div>
                    
                    <div class="endpoint-card">
                        <h3>GET /api/Orders/customer/{customerId}</h3>
                        <p>Get orders by customer ID</p>
                        <form method="POST" class="test-form">
                            <input type="hidden" name="test_endpoint" value="customer_orders">
                            <div class="form-group">
                                <label>Customer ID:</label>
                                <input type="number" name="customer_id" value="1" min="1" required>
                            </div>
                            <button type="submit" class="btn btn-primary">Test Endpoint</button>
                        </form>
                    </div>
                </div>
            </div>

            <!-- Distributors API Section -->
            <div class="api-section">
                <h2>🏢 Distributors API</h2>
                <div class="api-endpoints">
                    <div class="endpoint-card">
                        <h3>GET /api/Distributors</h3>
                        <p>Get all distributors</p>
                        <form method="POST" class="test-form">
                            <input type="hidden" name="test_endpoint" value="distributors_list">
                            <button type="submit" class="btn btn-primary">Test Endpoint</button>
                        </form>
                    </div>
                    
                    <div class="endpoint-card">
                        <h3>GET /api/Distributors/{id}</h3>
                        <p>Get distributor by ID</p>
                        <form method="POST" class="test-form">
                            <input type="hidden" name="test_endpoint" value="distributor_by_id">
                            <div class="form-group">
                                <label>Distributor ID:</label>
                                <input type="number" name="distributor_id" value="1" min="1" required>
                            </div>
                            <button type="submit" class="btn btn-primary">Test Endpoint</button>
                        </form>
                    </div>
                    
                    <div class="endpoint-card">
                        <h3>GET /api/Distributors/{id}/availability</h3>
                        <p>Check distributor availability</p>
                        <form method="POST" class="test-form">
                            <input type="hidden" name="test_endpoint" value="distributor_availability">
                            <div class="form-group">
                                <label>Distributor ID:</label>
                                <input type="number" name="distributor_id" value="1" min="1" required>
                            </div>
                            <button type="submit" class="btn btn-primary">Test Endpoint</button>
                        </form>
                    </div>
                    
                    <div class="endpoint-card">
                        <h3>GET /api/Distributors/{id}/performance</h3>
                        <p>Get distributor performance metrics</p>
                        <form method="POST" class="test-form">
                            <input type="hidden" name="test_endpoint" value="distributor_performance">
                            <div class="form-group">
                                <label>Distributor ID:</label>
                                <input type="number" name="distributor_id" value="1" min="1" required>
                            </div>
                            <button type="submit" class="btn btn-primary">Test Endpoint</button>
                        </form>
                    </div>
                </div>
            </div>

            <!-- Quotations API Section -->
            <div class="api-section">
                <h2>💰 Quotations API</h2>
                <div class="api-endpoints">
                    <div class="endpoint-card">
                        <h3>GET /api/Quotations/{orderId}/compare</h3>
                        <p>Get quotation comparison for order</p>
                        <form method="POST" class="test-form">
                            <input type="hidden" name="test_endpoint" value="quotation_comparison">
                            <div class="form-group">
                                <label>Order ID:</label>
                                <input type="number" name="order_id" value="1" min="1" required>
                            </div>
                            <button type="submit" class="btn btn-primary">Test Endpoint</button>
                        </form>
                    </div>
                    
                    <div class="endpoint-card">
                        <h3>GET /api/Quotations/{orderId}/history</h3>
                        <p>Get quotation history for order</p>
                        <form method="POST" class="test-form">
                            <input type="hidden" name="test_endpoint" value="quotation_history">
                            <div class="form-group">
                                <label>Order ID:</label>
                                <input type="number" name="order_id" value="1" min="1" required>
                            </div>
                            <button type="submit" class="btn btn-primary">Test Endpoint</button>
                        </form>
                    </div>
                </div>
            </div>
        </div>

        <!-- Test Results Section -->
        <?php if (!empty($testResults)): ?>
        <div class="test-results">
            <h2>🔍 Test Results</h2>
            <?php foreach ($testResults as $endpoint => $result): ?>
            <div class="result-card">
                <h3><?php echo htmlspecialchars($endpoint); ?></h3>
                <?php if (isset($result['error'])): ?>
                    <div class="error-result">
                        <strong>Error:</strong> <?php echo htmlspecialchars($result['message']); ?>
                        <?php if (isset($result['code'])): ?>
                            <br><strong>Code:</strong> <?php echo $result['code']; ?>
                        <?php endif; ?>
                    </div>
                <?php else: ?>
                    <div class="success-result">
                        <strong>Success!</strong> API endpoint working correctly.
                        <details>
                            <summary>View Response Data</summary>
                            <pre><?php echo htmlspecialchars(json_encode($result, JSON_PRETTY_PRINT)); ?></pre>
                        </details>
                    </div>
                <?php endif; ?>
            </div>
            <?php endforeach; ?>
        </div>
        <?php endif; ?>
    </div>
</div>

<style>
.api-testing-page {
    padding: 2rem 0;
    min-height: 60vh;
}

.page-header {
    margin-bottom: 2rem;
    display: flex;
    justify-content: space-between;
    align-items: center;
    flex-wrap: wrap;
    gap: 1rem;
}

.api-sections {
    display: grid;
    gap: 2rem;
}

.api-section {
    background: white;
    border-radius: 12px;
    box-shadow: 0 2px 8px rgba(0,0,0,0.1);
    padding: 1.5rem;
}

.api-section h2 {
    margin: 0 0 1rem 0;
    color: #333;
}

.api-endpoints {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(300px, 1fr));
    gap: 1rem;
}

.endpoint-card {
    background: #f8f9fa;
    border: 1px solid #e9ecef;
    border-radius: 8px;
    padding: 1rem;
}

.endpoint-card h3 {
    margin: 0 0 0.5rem 0;
    font-size: 0.9rem;
    font-family: monospace;
    background: #e9ecef;
    padding: 0.25rem 0.5rem;
    border-radius: 4px;
}

.endpoint-card p {
    margin: 0 0 1rem 0;
    font-size: 0.9rem;
    color: #666;
}

.test-form {
    display: flex;
    flex-direction: column;
    gap: 0.75rem;
}

.form-group {
    display: flex;
    flex-direction: column;
    gap: 0.25rem;
}

.form-group label {
    font-size: 0.8rem;
    font-weight: 600;
    color: #555;
}

.form-group input {
    padding: 0.5rem;
    border: 1px solid #ddd;
    border-radius: 4px;
    font-size: 0.9rem;
}

.test-results {
    margin-top: 2rem;
}

.result-card {
    background: white;
    border-radius: 8px;
    border-left: 4px solid #007bff;
    padding: 1rem;
    margin-bottom: 1rem;
    box-shadow: 0 2px 4px rgba(0,0,0,0.1);
}

.result-card h3 {
    margin: 0 0 0.5rem 0;
    font-family: monospace;
    font-size: 0.9rem;
}

.success-result {
    color: #155724;
    background: #d4edda;
    border: 1px solid #c3e6cb;
    padding: 0.75rem;
    border-radius: 4px;
}

.error-result {
    color: #721c24;
    background: #f8d7da;
    border: 1px solid #f5c6cb;
    padding: 0.75rem;
    border-radius: 4px;
}

details summary {
    cursor: pointer;
    font-weight: 600;
    margin: 0.5rem 0;
    color: #007bff;
}

details pre {
    background: #f8f9fa;
    padding: 1rem;
    border-radius: 4px;
    font-size: 0.8rem;
    overflow-x: auto;
    max-height: 300px;
    overflow-y: auto;
}

@media (max-width: 768px) {
    .page-header {
        flex-direction: column;
        align-items: stretch;
    }
    
    .api-endpoints {
        grid-template-columns: 1fr;
    }
}
</style>
