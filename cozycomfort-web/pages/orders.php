<?php
// Check if user is logged in
if (!isLoggedIn()) {
    setNotification('Please login to view your orders.', 'error');
    redirect('?page=login');
}

$apiClient = getApiClient();
$userId = $_SESSION['user_id'];

// Get orders from API
try {
    $ordersResponse = $apiClient->getOrders($userId);
    $orders = [];
    
    if (isset($ordersResponse['data']) && is_array($ordersResponse['data'])) {
        $orders = $ordersResponse['data'];
    }
} catch (Exception $e) {
    $orders = [];
}
?>

<div class="orders-page">
    <div class="container">
        <div class="page-header">
            <h1>My Orders</h1>
            <p>View and track your order history</p>
        </div>

        <?php if (empty($orders)): ?>
            <div class="empty-orders">
                <div class="empty-icon">📦</div>
                <h3>No Orders Yet</h3>
                <p>You haven't placed any orders yet. Start shopping to see your orders here.</p>
                <a href="?page=products" class="btn btn-primary">Browse Products</a>
            </div>
        <?php else: ?>
            <div class="orders-list">
                <?php foreach ($orders as $order): ?>
                    <div class="order-card">
                        <div class="order-header">
                            <div class="order-info">
                                <h3>Order #<?php echo htmlspecialchars($order['orderReference'] ?? $order['orderNumber'] ?? $order['orderId'] ?? 'N/A'); ?></h3>
                                <p class="order-date">
                                    Placed on <?php echo date('F j, Y', strtotime($order['orderDate'] ?? 'now')); ?>
                                </p>
                            </div>
                            <div class="order-status">
                                <span class="status-badge status-<?php echo strtolower($order['status'] ?? 'pending'); ?>">
                                    <?php echo htmlspecialchars($order['status'] ?? 'Pending'); ?>
                                </span>
                            </div>
                        </div>

                        <div class="order-items">
                            <?php 
                            $items = $order['items'] ?? [];
                            foreach ($items as $item): 
                            ?>
                                <div class="order-item">
                                    <img src="<?php echo htmlspecialchars($item['imageUrl'] ?? 'images/placeholder.jpg'); ?>" 
                                         alt="<?php echo htmlspecialchars($item['productName'] ?? 'Product'); ?>">
                                    <div class="item-details">
                                        <h4><?php echo htmlspecialchars($item['productName'] ?? 'Unknown Product'); ?></h4>
                                        <p>Qty: <?php echo intval($item['quantity'] ?? 1); ?> × <?php echo formatCurrency($item['unitPrice'] ?? $item['price'] ?? 0); ?></p>
                                    </div>
                                    <div class="item-total">
                                        <?php echo formatCurrency(($item['unitPrice'] ?? $item['price'] ?? 0) * ($item['quantity'] ?? 1)); ?>
                                    </div>
                                </div>
                            <?php endforeach; ?>
                        </div>

                        <div class="order-footer">
                            <div class="order-total">
                                <span>Total:</span>
                                <strong><?php echo formatCurrency($order['totalAmount'] ?? 0); ?></strong>
                            </div>
                            <div class="order-actions">
                                <a href="?page=order-detail&id=<?php echo intval($order['orderId'] ?? $order['id'] ?? 0); ?>" class="btn btn-outline btn-sm">
                                    View Details
                                </a>
                                <?php if (($order['status'] ?? '') === 'Delivered'): ?>
                                    <a href="?page=product-detail&id=<?php echo intval($items[0]['productId'] ?? 0); ?>#review" 
                                       class="btn btn-primary btn-sm">
                                        Write Review
                                    </a>
                                <?php endif; ?>
                            </div>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>
    </div>
</div>

<style>
.orders-page {
    padding: 2rem 0;
    min-height: 60vh;
}

.page-header {
    margin-bottom: 2rem;
}

.page-header h1 {
    margin: 0 0 0.5rem;
    color: #333;
}

.page-header p {
    margin: 0;
    color: #666;
}

.empty-orders {
    background: white;
    border-radius: 8px;
    padding: 4rem 2rem;
    text-align: center;
    box-shadow: 0 2px 4px rgba(0,0,0,0.1);
}

.empty-icon {
    font-size: 4rem;
    margin-bottom: 1rem;
}

.empty-orders h3 {
    margin: 0 0 1rem;
    color: #333;
}

.empty-orders p {
    margin: 0 0 2rem;
    color: #666;
}

.orders-list {
    display: flex;
    flex-direction: column;
    gap: 1.5rem;
}

.order-card {
    background: white;
    border-radius: 8px;
    padding: 1.5rem;
    box-shadow: 0 2px 4px rgba(0,0,0,0.1);
}

.order-header {
    display: flex;
    justify-content: space-between;
    align-items: flex-start;
    margin-bottom: 1.5rem;
    padding-bottom: 1rem;
    border-bottom: 1px solid #eee;
}

.order-info h3 {
    margin: 0 0 0.5rem;
    color: #333;
    font-size: 1.25rem;
}

.order-date {
    margin: 0;
    color: #666;
    font-size: 0.9rem;
}

.status-badge {
    display: inline-block;
    padding: 0.5rem 1rem;
    border-radius: 20px;
    font-size: 0.9rem;
    font-weight: 500;
}

.status-pending {
    background: #fff3cd;
    color: #856404;
}

.status-processing {
    background: #cfe2ff;
    color: #084298;
}

.status-shipped {
    background: #d1ecf1;
    color: #0c5460;
}

.status-delivered {
    background: #d4edda;
    color: #155724;
}

.status-cancelled {
    background: #f8d7da;
    color: #721c24;
}

.order-items {
    display: flex;
    flex-direction: column;
    gap: 1rem;
    margin-bottom: 1.5rem;
}

.order-item {
    display: flex;
    align-items: center;
    gap: 1rem;
}

.order-item img {
    width: 80px;
    height: 80px;
    object-fit: cover;
    border-radius: 4px;
}

.item-details {
    flex: 1;
}

.item-details h4 {
    margin: 0 0 0.25rem;
    color: #333;
    font-size: 1rem;
}

.item-details p {
    margin: 0;
    color: #666;
    font-size: 0.9rem;
}

.item-total {
    font-weight: 600;
    color: #333;
}

.order-footer {
    display: flex;
    justify-content: space-between;
    align-items: center;
    padding-top: 1rem;
    border-top: 1px solid #eee;
}

.order-total {
    font-size: 1.1rem;
}

.order-total strong {
    color: #007bff;
    margin-left: 0.5rem;
}

.order-actions {
    display: flex;
    gap: 0.5rem;
}

@media (max-width: 768px) {
    .order-header {
        flex-direction: column;
        gap: 1rem;
    }
    
    .order-footer {
        flex-direction: column;
        gap: 1rem;
        align-items: stretch;
    }
    
    .order-actions {
        flex-direction: column;
    }
    
    .order-item {
        flex-direction: column;
        text-align: center;
    }
}
</style>

