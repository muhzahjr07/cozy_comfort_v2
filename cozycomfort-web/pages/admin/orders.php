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

// Pagination and filtering
$page = (int)($_GET['p'] ?? 1);
$pageSize = 15;
$statusFilter = $_GET['status'] ?? '';
$searchTerm = $_GET['search'] ?? '';

// Build query parameters
$queryParams = [
    'page' => $page,
    'pageSize' => $pageSize
];

if (!empty($statusFilter)) {
    $queryParams['status'] = $statusFilter;
}

if (!empty($searchTerm)) {
    $queryParams['search'] = $searchTerm;
}

$orders = [];
$pagination = [
    'currentPage' => 1,
    'totalPages' => 1,
    'totalCount' => 0,
    'hasNext' => false,
    'hasPrevious' => false
];

try {
    // Fetch orders from API using the new method
    $response = $apiClient->getOrders(null, $page, $pageSize);
    if ($response && is_array($response)) {
        if (isset($response['data'])) {
            // Paginated response
            $orders = $response['data'];
            $pagination = [
                'currentPage' => $response['pageNumber'] ?? 1,
                'totalPages' => $response['totalPages'] ?? 1,
                'totalCount' => $response['totalCount'] ?? 0,
                'hasNext' => $response['hasNextPage'] ?? false,
                'hasPrevious' => $response['hasPreviousPage'] ?? false
            ];
        } else {
            // Direct array of orders
            $orders = $response;
        }
    }
} catch (Exception $e) {
    error_log('Admin orders API error: ' . $e->getMessage());
}

// Handle form submissions for order status updates
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action'])) {
    try {
        switch ($_POST['action']) {
            case 'update_status':
                $orderId = (int)$_POST['orderId'];
                $newStatus = $_POST['status'];
                $notes = $_POST['notes'] ?? null;
                
                $statusData = [
                    'status' => $newStatus,
                    'notes' => $notes
                ];
                
                $response = $apiClient->updateOrderStatus($orderId, $statusData);
                if ($response) {
                    setNotification("Order status updated to {$newStatus} successfully!", 'success');
                } else {
                    setNotification('Failed to update order status.', 'error');
                }
                break;
                
            case 'delete_order':
                $orderId = (int)$_POST['orderId'];
                $response = $apiClient->deleteOrder($orderId);
                if ($response) {
                    setNotification('Order deleted successfully!', 'success');
                } else {
                    setNotification('Failed to delete order.', 'error');
                }
                break;
        }
    } catch (Exception $e) {
        error_log('Order action error: ' . $e->getMessage());
        setNotification('Operation failed. Please try again.', 'error');
    }
    redirect('?page=admin-orders');
}

$statusOptions = [
    '' => 'All Statuses',
    'Pending' => 'Pending',
    'Confirmed' => 'Confirmed',
    'Processing' => 'Processing',
    'Shipped' => 'Shipped',
    'Delivered' => 'Delivered',
    'Cancelled' => 'Cancelled'
];
?>

<div class="admin-orders">
    <div class="container">
        <!-- Page Header -->
        <div class="page-header">
            <div class="header-left">
                <h1>📦 Order Management</h1>
                <p>Manage and track all customer orders</p>
            </div>
            <div class="header-right">
                <a href="?page=admin-dashboard" class="btn btn-outline">← Back to Dashboard</a>
            </div>
        </div>
        
        <!-- Filters and Search -->
        <div class="filters-section">
            <form method="GET" class="filters-form">
                <input type="hidden" name="page" value="admin-orders">
                
                <div class="search-input">
                    <input type="text" name="search" placeholder="Search orders..." 
                           value="<?php echo htmlspecialchars($searchTerm); ?>">
                    <button type="submit" class="search-btn">🔍</button>
                </div>
                
                <div class="filter-select">
                    <select name="status" onchange="this.form.submit()">
                        <?php foreach ($statusOptions as $value => $label): ?>
                            <option value="<?php echo $value; ?>" 
                                    <?php echo $statusFilter === $value ? 'selected' : ''; ?>>
                                <?php echo $label; ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>
                
                <?php if (!empty($statusFilter) || !empty($searchTerm)): ?>
                    <a href="?page=admin-orders" class="btn btn-outline btn-sm">Clear Filters</a>
                <?php endif; ?>
            </form>
            
            <div class="results-info">
                <span><?php echo number_format($pagination['totalCount']); ?> orders found</span>
            </div>
        </div>
        
        <!-- Orders Table -->
        <div class="orders-table-container">
            <?php if (!empty($orders)): ?>
                <table class="orders-table">
                    <thead>
                        <tr>
                            <th>Order ID</th>
                            <th>Customer</th>
                            <th>Date</th>
                            <th>Items</th>
                            <th>Total</th>
                            <th>Status</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($orders as $order): ?>
                            <tr>
                                <td>
                                    <strong>#<?php echo $order['orderId']; ?></strong>
                                    <br>
                                    <small><?php echo $order['orderReference'] ?? 'GH-' . date('Y') . '-' . str_pad($order['orderId'], 6, '0', STR_PAD_LEFT); ?></small>
                                </td>
                                <td>
                                    <div class="customer-info">
                                        <strong><?php echo htmlspecialchars($order['customerName'] ?? 'Customer ' . $order['customerId']); ?></strong>
                                        <br>
                                        <small><?php echo htmlspecialchars($order['customerEmail'] ?? ''); ?></small>
                                    </div>
                                </td>
                                <td>
                                    <div class="date-info">
                                        <strong><?php echo date('M j, Y', strtotime($order['orderDate'])); ?></strong>
                                        <br>
                                        <small><?php echo date('g:i A', strtotime($order['orderDate'])); ?></small>
                                    </div>
                                </td>
                                <td>
                                    <span class="items-count"><?php echo count($order['items'] ?? []); ?> item(s)</span>
                                </td>
                                <td>
                                    <strong><?php echo CURRENCY_SYMBOL . ' ' . number_format($order['totalAmount'], 2); ?></strong>
                                </td>
                                <td>
                                    <form method="POST" class="status-form" style="margin: 0;">
                                        <input type="hidden" name="action" value="update_status">
                                        <input type="hidden" name="orderId" value="<?php echo $order['orderId']; ?>">
                                        <select name="status" onchange="this.form.submit()" 
                                                class="status-select status-<?php echo strtolower($order['status']); ?>">
                                            <?php foreach (['Pending', 'Confirmed', 'Processing', 'Shipped', 'Delivered', 'Cancelled'] as $status): ?>
                                                <option value="<?php echo $status; ?>" 
                                                        <?php echo $order['status'] === $status ? 'selected' : ''; ?>>
                                                    <?php echo $status; ?>
                                                </option>
                                            <?php endforeach; ?>
                                        </select>
                                    </form>
                                </td>
                                <td>
                                    <div class="action-buttons">
                                        <a href="?page=order-detail&id=<?php echo $order['orderId']; ?>" 
                                           class="btn btn-sm btn-outline" title="View Details">👁️</a>
                                        <button onclick="printOrder(<?php echo $order['orderId']; ?>)" 
                                                class="btn btn-sm btn-outline" title="Print Order">🖨️</button>
                                        <button onclick="emailCustomer(<?php echo $order['orderId']; ?>)" 
                                                class="btn btn-sm btn-outline" title="Email Customer">📧</button>
                                    </div>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            <?php else: ?>
                <div class="empty-state">
                    <div class="empty-icon">📦</div>
                    <h3>No orders found</h3>
                    <p>There are no orders matching your current filters.</p>
                    <?php if (!empty($statusFilter) || !empty($searchTerm)): ?>
                        <a href="?page=admin-orders" class="btn btn-primary">Show All Orders</a>
                    <?php endif; ?>
                </div>
            <?php endif; ?>
        </div>
        
        <!-- Pagination -->
        <?php if ($pagination['totalPages'] > 1): ?>
            <div class="pagination">
                <?php if ($pagination['hasPrevious']): ?>
                    <a href="?page=admin-orders&p=<?php echo $pagination['currentPage'] - 1; ?><?php echo !empty($statusFilter) ? "&status={$statusFilter}" : ''; ?><?php echo !empty($searchTerm) ? "&search=" . urlencode($searchTerm) : ''; ?>" 
                       class="btn btn-outline">← Previous</a>
                <?php endif; ?>
                
                <span class="page-info">
                    Page <?php echo $pagination['currentPage']; ?> of <?php echo $pagination['totalPages']; ?>
                </span>
                
                <?php if ($pagination['hasNext']): ?>
                    <a href="?page=admin-orders&p=<?php echo $pagination['currentPage'] + 1; ?><?php echo !empty($statusFilter) ? "&status={$statusFilter}" : ''; ?><?php echo !empty($searchTerm) ? "&search=" . urlencode($searchTerm) : ''; ?>" 
                       class="btn btn-outline">Next →</a>
                <?php endif; ?>
            </div>
        <?php endif; ?>
    </div>
</div>

<style>
.admin-orders {
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

.results-info {
    color: #6c757d;
    font-weight: 500;
}

.orders-table-container {
    background: white;
    border-radius: 12px;
    box-shadow: 0 4px 12px rgba(0, 0, 0, 0.1);
    overflow: hidden;
    margin-bottom: 2rem;
}

.orders-table {
    width: 100%;
    border-collapse: collapse;
}

.orders-table th {
    background: #f8f9fa;
    padding: 1.25rem 1rem;
    text-align: left;
    font-weight: 600;
    color: #333;
    border-bottom: 2px solid #e9ecef;
}

.orders-table td {
    padding: 1.25rem 1rem;
    border-bottom: 1px solid #e9ecef;
    vertical-align: middle;
}

.customer-info, .date-info {
    line-height: 1.4;
}

.items-count {
    background: #e9ecef;
    padding: 0.25rem 0.75rem;
    border-radius: 12px;
    font-size: 0.9rem;
    font-weight: 500;
}

.status-form {
    margin: 0;
}

.status-select {
    padding: 0.5rem 0.75rem;
    border: none;
    border-radius: 6px;
    font-size: 0.9rem;
    font-weight: 600;
    cursor: pointer;
}

.status-pending { background: #fff3cd; color: #856404; }
.status-confirmed { background: #d4edda; color: #155724; }
.status-processing { background: #cce5ff; color: #004085; }
.status-shipped { background: #d1ecf1; color: #0c5460; }
.status-delivered { background: #d1f2eb; color: #0a3622; }
.status-cancelled { background: #f8d7da; color: #721c24; }

.action-buttons {
    display: flex;
    gap: 0.5rem;
    flex-wrap: wrap;
}

.action-buttons .btn {
    padding: 0.5rem;
    min-width: auto;
    border-radius: 6px;
}

.empty-state {
    text-align: center;
    padding: 4rem 2rem;
    color: #6c757d;
}

.empty-icon {
    font-size: 4rem;
    margin-bottom: 1rem;
    opacity: 0.5;
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
    
    .orders-table-container {
        overflow-x: auto;
    }
    
    .orders-table {
        min-width: 800px;
    }
    
    .orders-table th,
    .orders-table td {
        padding: 0.75rem 0.5rem;
        font-size: 0.9rem;
    }
    
    .action-buttons {
        justify-content: center;
    }
}
</style>

<script>
function printOrder(orderId) {
    if (confirm(`Print order #${orderId}?`)) {
        // In a real application, this would generate a PDF or print view
        window.open(`?page=order-detail&id=${orderId}&print=1`, '_blank');
    }
}

function emailCustomer(orderId) {
    if (confirm(`Send order update email for order #${orderId}?`)) {
        fetch('<?php echo API_BASE_URL; ?>/admin/orders/' + orderId + '/email', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
            }
        })
        .then(response => {
            if (response.ok) {
                alert('Email sent successfully!');
            } else {
                alert('Failed to send email.');
            }
        })
        .catch(() => {
            alert('Failed to send email.');
        });
    }
}

// Auto-refresh orders every 30 seconds
setInterval(function() {
    if (!document.hidden) {
        // Refresh current page to show updated order statuses
        window.location.reload();
    }
}, 30000);

// Add loading state to status changes
document.querySelectorAll('.status-select').forEach(select => {
    select.addEventListener('change', function() {
        this.style.opacity = '0.6';
        this.disabled = true;
        
        // Re-enable after form submission
        setTimeout(() => {
            this.style.opacity = '1';
            this.disabled = false;
        }, 2000);
    });
});
</script>
