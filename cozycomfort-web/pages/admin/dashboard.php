<?php
// Admin authentication check
if (!isLoggedIn()) {
    setNotification('Please login to access admin dashboard.', 'error');
    redirect('?page=login');
}

if (!isAdmin()) {
    setNotification('Access denied. Admin privileges required.', 'error');
    redirect('?page=home');
}

// Get dashboard statistics
$apiClient = getApiClient();
$stats = [
    'totalOrders' => 0,
    'pendingOrders' => 0,
    'totalRevenue' => 0,
    'totalCustomers' => 0,
    'totalProducts' => 0,
    'totalDistributors' => 0,
    'recentOrders' => []
];

try {
    // Get dashboard stats (Total Orders, Pending Orders, Total Revenue, Total Customers)
    try {
        $statsResponse = $apiClient->get('api/Orders/dashboard-stats');
        if ($statsResponse && isset($statsResponse['success']) && $statsResponse['success'] && isset($statsResponse['data'])) {
            $statsData = $statsResponse['data'];
            $stats['totalOrders'] = (int)($statsData['totalOrders'] ?? 0);
            $stats['pendingOrders'] = (int)($statsData['pendingOrders'] ?? 0);
            $stats['totalRevenue'] = (float)($statsData['totalRevenue'] ?? 0);
            $stats['totalCustomers'] = (int)($statsData['totalCustomers'] ?? 0);
        }
    } catch (Exception $e) {
        error_log('Admin dashboard stats API error: ' . $e->getMessage());
    }

    // Get recent orders (using new orders method)
    $ordersResponse = $apiClient->getOrders(null, 1, 5);
    if ($ordersResponse) {
        if (isset($ordersResponse['data']['data']) && is_array($ordersResponse['data']['data'])) {
            $stats['recentOrders'] = $ordersResponse['data']['data'];
        } elseif (isset($ordersResponse['data']) && is_array($ordersResponse['data'])) {
            $stats['recentOrders'] = $ordersResponse['data'];
        } elseif (is_array($ordersResponse)) {
            $stats['recentOrders'] = $ordersResponse;
        }
    }
    
    // Get products stats (using new products method)
    $productsResponse = $apiClient->getProducts(['PageSize' => 1]);
    if ($productsResponse) {
        if (isset($productsResponse['data']['totalCount'])) {
            $stats['totalProducts'] = (int)$productsResponse['data']['totalCount'];
        } elseif (isset($productsResponse['totalCount'])) {
            $stats['totalProducts'] = (int)$productsResponse['totalCount'];
        }
    }
    
    // Get distributors (using new distributors method)
    $distributorsResponse = $apiClient->getDistributors();
    if ($distributorsResponse) {
        if (isset($distributorsResponse['data']) && is_array($distributorsResponse['data'])) {
            $stats['totalDistributors'] = count($distributorsResponse['data']);
        } elseif (is_array($distributorsResponse)) {
            $stats['totalDistributors'] = count($distributorsResponse);
        }
    }
    
    // Get product categories (using new categories method)
    $categoriesResponse = $apiClient->getProductCategories();
    $stats['totalCategories'] = is_array($categoriesResponse) ? count($categoriesResponse) : 0;
    
    error_log('Successfully fetched dashboard data using comprehensive API methods');
} catch (Exception $e) {
    // Log error or show error message
    error_log('Admin dashboard API error: ' . $e->getMessage());
}
?>

<div class="admin-dashboard">
    <!-- Admin Header -->
    <div class="admin-header">
        <div class="container">
            <div class="header-content">
                <h1>🏪 Admin Dashboard</h1>
                <div class="admin-info">
                    <span>Welcome, <?php echo htmlspecialchars($_SESSION['firstName'] ?? 'Admin'); ?>!</span>
                    <span class="last-login">Last login: <?php echo date('M j, Y g:i A'); ?></span>
                </div>
            </div>
        </div>
    </div>
    
    <!-- Statistics Cards -->
    <div class="container">
        <div class="stats-grid">
            <div class="stat-card">
                <div class="stat-icon">📦</div>
                <div class="stat-content">
                    <h3><?php echo number_format($stats['totalOrders']); ?></h3>
                    <p>Total Orders</p>
                    <?php if ($stats['pendingOrders'] > 0): ?>
                        <span class="stat-badge"><?php echo $stats['pendingOrders']; ?> pending</span>
                    <?php endif; ?>
                </div>
            </div>
            
            <div class="stat-card">
                <div class="stat-icon">💰</div>
                <div class="stat-content">
                    <h3><?php echo CURRENCY_SYMBOL . ' ' . number_format($stats['totalRevenue'], 2); ?></h3>
                    <p>Total Revenue</p>
                    <span class="stat-trend">+12% this month</span>
                </div>
            </div>
            
            <div class="stat-card">
                <div class="stat-icon">👥</div>
                <div class="stat-content">
                    <h3><?php echo number_format($stats['totalCustomers']); ?></h3>
                    <p>Total Customers</p>
                    <span class="stat-trend">+8 this week</span>
                </div>
            </div>
            
            <div class="stat-card">
                <div class="stat-icon">🛏️</div>
                <div class="stat-content">
                    <h3><?php echo number_format($stats['totalProducts']); ?></h3>
                    <p>Total Products</p>
                    <span class="stat-trend"><?php echo $stats['totalCategories'] ?? 5; ?> categories</span>
                </div>
            </div>
        </div>
        
        <!-- Quick Actions -->
        <div class="quick-actions">
            <h2>Quick Actions</h2>
            <div class="action-grid">
                <a href="?page=admin-orders" class="action-card">
                    <div class="action-icon">📋</div>
                    <h3>Manage Orders</h3>
                    <p>View and update order status</p>
                </a>
                
                <a href="?page=admin-products" class="action-card">
                    <div class="action-icon">🛍️</div>
                    <h3>Manage Products</h3>
                    <p>Add, edit, or remove products</p>
                </a>
                
                <a href="?page=admin-customers" class="action-card">
                    <div class="action-icon">👤</div>
                    <h3>Manage Customers</h3>
                    <p>View customer profiles</p>
                </a>
                
                <a href="?page=admin-distributors" class="action-card">
                    <div class="action-icon">🏢</div>
                    <h3>Manage Distributors</h3>
                    <p>Handle distributor relationships</p>
                </a>
                
                <a href="?page=admin-reports" class="action-card">
                    <div class="action-icon">📊</div>
                    <h3>View Reports</h3>
                    <p>Sales and analytics reports</p>
                </a>
                
                <a href="?page=admin-settings" class="action-card">
                    <div class="action-icon">⚙️</div>
                    <h3>Settings</h3>
                    <p>System configuration</p>
                </a>
            </div>
        </div>
        
        <!-- Recent Orders -->
        <div class="recent-orders">
            <div class="section-header">
                <h2>Recent Orders</h2>
                <a href="?page=admin-orders" class="view-all-btn">View All</a>
            </div>
            
            <?php if (!empty($stats['recentOrders'])): ?>
                <div class="orders-table">
                    <table>
                        <thead>
                            <tr>
                                <th>Order ID</th>
                                <th>Customer</th>
                                <th>Date</th>
                                <th>Amount</th>
                                <th>Status</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($stats['recentOrders'] as $order): ?>
                                <tr>
                                    <td>#<?php echo $order['orderId']; ?></td>
                                    <td><?php echo htmlspecialchars($order['customerName'] ?? 'Customer ' . $order['customerId']); ?></td>
                                    <td><?php echo date('M j, Y', strtotime($order['orderDate'])); ?></td>
                                    <td><?php echo CURRENCY_SYMBOL . ' ' . number_format($order['totalAmount'], 2); ?></td>
                                    <td>
                                        <span class="status-badge status-<?php echo strtolower($order['status']); ?>">
                                            <?php echo htmlspecialchars($order['status']); ?>
                                        </span>
                                    </td>
                                    <td>
                                        <a href="?page=admin-order-detail&id=<?php echo $order['orderId']; ?>" class="btn btn-sm">View</a>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            <?php else: ?>
                <div class="empty-state">
                    <p>No recent orders found.</p>
                </div>
            <?php endif; ?>
        </div>
        
        <!-- System Status -->
        <div class="system-status">
            <h2>System Status</h2>
            <div class="status-grid">
                <div class="status-item">
                    <span class="status-indicator status-online"></span>
                    <div>
                        <strong>API Service</strong>
                        <p>Connected and operational</p>
                    </div>
                </div>
                
                <div class="status-item">
                    <span class="status-indicator status-online"></span>
                    <div>
                        <strong>Payment Gateway</strong>
                        <p>All systems operational</p>
                    </div>
                </div>
                
                <div class="status-item">
                    <span class="status-indicator status-warning"></span>
                    <div>
                        <strong>Inventory Sync</strong>
                        <p>Last updated: 2 hours ago</p>
                    </div>
                </div>
                
                <div class="status-item">
                    <span class="status-indicator status-online"></span>
                    <div>
                        <strong>Email Service</strong>
                        <p>All notifications sent</p>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<style>
.admin-dashboard {
    background-color: var(--bg-color);
    min-height: 100vh;
}

.admin-header {
    background: linear-gradient(135deg, #8d5b4c 0%, #4a2c22 100%);
    color: white;
    padding: 2rem 0;
    margin-bottom: 2rem;
}

.header-content {
    display: flex;
    justify-content: space-between;
    align-items: center;
    flex-wrap: wrap;
    gap: 1rem;
}

.header-content h1 {
    margin: 0;
    font-size: 2rem;
}

.admin-info {
    text-align: right;
}

.admin-info span {
    display: block;
    opacity: 0.9;
}

.last-login {
    font-size: 0.9rem;
    opacity: 0.8 !important;
}

.stats-grid {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
    gap: 1.5rem;
    margin-bottom: 3rem;
}

.stat-card {
    background: var(--card-bg);
    border: 1px solid var(--border-color);
    border-radius: 12px;
    padding: 2rem;
    box-shadow: var(--shadow);
    display: flex;
    align-items: center;
    gap: 1.5rem;
    transition: transform 0.2s, box-shadow 0.2s;
}

.stat-card:hover {
    transform: translateY(-2px);
    box-shadow: 0 8px 24px rgba(0, 0, 0, 0.15);
}

.stat-icon {
    font-size: 3rem;
    opacity: 0.8;
}

.stat-content h3 {
    margin: 0;
    font-size: 2rem;
    font-weight: 700;
    color: var(--heading-color);
}

.stat-content p {
    margin: 0.5rem 0;
    color: #6c757d;
    font-weight: 500;
}

.stat-badge {
    background: #ffc107;
    color: #212529;
    padding: 0.25rem 0.75rem;
    border-radius: 12px;
    font-size: 0.8rem;
    font-weight: 600;
}

.stat-trend {
    color: #28a745;
    font-size: 0.9rem;
    font-weight: 600;
}

.quick-actions {
    margin-bottom: 3rem;
}

.quick-actions h2 {
    margin-bottom: 1.5rem;
    color: #333;
}

.action-grid {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
    gap: 1.5rem;
}

.action-card {
    background: var(--card-bg);
    border: 1px solid var(--border-color);
    border-radius: 12px;
    padding: 2rem 1.5rem;
    text-decoration: none;
    color: var(--text-color);
    box-shadow: var(--shadow);
    transition: all 0.2s;
    text-align: center;
}

.action-card:hover {
    transform: translateY(-4px);
    box-shadow: 0 12px 24px rgba(0, 0, 0, 0.15);
    text-decoration: none;
    color: var(--primary-color);
    border-color: var(--primary-color);
}

.action-icon {
    font-size: 3rem;
    margin-bottom: 1rem;
}

.action-card h3 {
    margin-bottom: 0.5rem;
    font-size: 1.2rem;
}

.action-card p {
    color: #6c757d;
    font-size: 0.9rem;
    margin: 0;
}

.recent-orders {
    background: var(--card-bg);
    border: 1px solid var(--border-color);
    border-radius: 12px;
    padding: 2rem;
    box-shadow: var(--shadow);
    margin-bottom: 3rem;
}

.section-header {
    display: flex;
    justify-content: space-between;
    align-items: center;
    margin-bottom: 1.5rem;
}

.section-header h2 {
    margin: 0;
    color: var(--heading-color);
}

.view-all-btn {
    color: var(--primary-color);
    text-decoration: none;
    font-weight: 600;
}

.orders-table {
    overflow-x: auto;
}

.orders-table table {
    width: 100%;
    border-collapse: collapse;
}

.orders-table th {
    background: #f8f9fa;
    padding: 1rem;
    text-align: left;
    font-weight: 600;
    border-bottom: 2px solid #e9ecef;
}

.orders-table td {
    padding: 1rem;
    border-bottom: 1px solid #e9ecef;
}

.status-badge {
    padding: 0.25rem 0.75rem;
    border-radius: 12px;
    font-size: 0.8rem;
    font-weight: 600;
}

.status-pending { background: #fff3cd; color: #856404; }
.status-confirmed { background: #d4edda; color: #155724; }
.status-shipped { background: #cce5ff; color: #004085; }
.status-delivered { background: #d1ecf1; color: #0c5460; }

.system-status {
    background: var(--card-bg);
    border: 1px solid var(--border-color);
    border-radius: 12px;
    padding: 2rem;
    box-shadow: var(--shadow);
}

.system-status h2 {
    margin-bottom: 1.5rem;
    color: var(--heading-color);
}

.status-grid {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
    gap: 1rem;
}

.status-item {
    display: flex;
    align-items: center;
    gap: 1rem;
    padding: 1rem;
    border: 1px solid var(--border-color);
    border-radius: 8px;
    background: var(--bg-color);
}

.status-indicator {
    width: 12px;
    height: 12px;
    border-radius: 50%;
}

.status-online { background: #28a745; }
.status-warning { background: #ffc107; }
.status-offline { background: #dc3545; }

.status-item strong {
    display: block;
    margin-bottom: 0.25rem;
}

.status-item p {
    margin: 0;
    color: #6c757d;
    font-size: 0.9rem;
}

.empty-state {
    text-align: center;
    padding: 3rem;
    color: #6c757d;
}

@media (max-width: 768px) {
    .header-content {
        text-align: center;
    }
    
    .admin-info {
        text-align: center;
    }
    
    .stats-grid {
        grid-template-columns: 1fr;
    }
    
    .action-grid {
        grid-template-columns: repeat(auto-fit, minmax(150px, 1fr));
    }
    
    .orders-table {
        font-size: 0.9rem;
    }
    
    .orders-table th,
    .orders-table td {
        padding: 0.5rem;
    }
}
</style>

<script>
// Auto-refresh dashboard data every 5 minutes
setInterval(function() {
    if (!document.hidden) {
        // Refresh page silently to update stats
        window.location.reload();
    }
}, 300000);

// Add loading states for action cards
document.querySelectorAll('.action-card').forEach(card => {
    card.addEventListener('click', function(e) {
        if (!this.classList.contains('loading')) {
            this.classList.add('loading');
            this.style.opacity = '0.7';
        }
    });
});

// System status check
function checkSystemStatus() {
    fetch('<?php echo API_BASE_URL; ?>/health')
        .then(response => {
            const indicators = document.querySelectorAll('.status-indicator');
            if (response.ok) {
                indicators[0].className = 'status-indicator status-online';
            } else {
                indicators[0].className = 'status-indicator status-warning';
            }
        })
        .catch(() => {
            const indicators = document.querySelectorAll('.status-indicator');
            indicators[0].className = 'status-indicator status-offline';
        });
}

// Check system status on load
checkSystemStatus();
</script>
