<?php
// Check if user is logged in
if (!isLoggedIn()) {
    setNotification('Please login to view order details.', 'error');
    redirect('?page=login');
}

$apiClient = getApiClient();
$orderId = getParam('id');

if (!$orderId) {
    setNotification('Invalid order ID.', 'error');
    redirect('?page=orders');
}

// Get order details
try {
    $ordersResponse = $apiClient->getOrders($_SESSION['user_id']);
    $orders = [];
    
    if (isset($ordersResponse['data']) && is_array($ordersResponse['data'])) {
        $orders = $ordersResponse['data'];
    }
    
    // Find the specific order
    $order = null;
    foreach ($orders as $o) {
        if (($o['orderId'] ?? $o['id'] ?? 0) == $orderId) {
            $order = $o;
            break;
        }
    }
    
    if (!$order) {
        setNotification('Order not found.', 'error');
        redirect('?page=orders');
    }
} catch (Exception $e) {
    setNotification('Failed to load order details.', 'error');
    redirect('?page=orders');
}

$items = $order['items'] ?? [];
?>

<div class="order-detail-page">
    <div class="container">
        <div class="page-header">
            <div>
                <a href="?page=orders" class="back-link">&larr; Back to Orders</a>
                <h1>Order Details</h1>
            </div>
            <span class="status-badge status-<?php echo strtolower($order['status'] ?? 'pending'); ?>">
                <?php echo htmlspecialchars($order['status'] ?? 'Pending'); ?>
            </span>
        </div>

        <div class="order-detail-content">
            <!-- Order Information Card -->
            <div class="info-card">
                <h2>Order Information</h2>
                <div class="info-grid">
                    <div class="info-item">
                        <label>Order Number:</label>
                        <span><?php echo htmlspecialchars($order['orderReference'] ?? $order['orderNumber'] ?? 'N/A'); ?></span>
                    </div>
                    <div class="info-item">
                        <label>Order Date:</label>
                        <span><?php echo date('F j, Y', strtotime($order['orderDate'] ?? 'now')); ?></span>
                    </div>
                    <div class="info-item">
                        <label>Status:</label>
                        <span><?php echo htmlspecialchars($order['status'] ?? 'Pending'); ?></span>
                    </div>
                    <div class="info-item">
                        <label>Estimated Delivery:</label>
                        <span><?php echo isset($order['estimatedDeliveryDate']) ? date('F j, Y', strtotime($order['estimatedDeliveryDate'])) : 'TBD'; ?></span>
                    </div>
                </div>
            </div>

            <!-- Delivery Information Card -->
            <div class="info-card">
                <h2>Delivery Information</h2>
                <div class="info-grid">
                    <div class="info-item">
                        <label>Delivery Address:</label>
                        <span><?php echo nl2br(htmlspecialchars($order['deliveryAddress'] ?? 'N/A')); ?></span>
                    </div>
                    <div class="info-item">
                        <label>Phone Number:</label>
                        <span><?php echo htmlspecialchars($order['phoneNumber'] ?? 'N/A'); ?></span>
                    </div>
                </div>
            </div>

            <!-- Order Items -->
            <div class="info-card">
                <h2>Order Items</h2>
                <table class="items-table">
                    <thead>
                        <tr>
                            <th>Product</th>
                            <th>Unit Price</th>
                            <th>Quantity</th>
                            <th>Total</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php 
                        $subtotal = 0;
                        foreach ($items as $item): 
                            $unitPrice = $item['unitPrice'] ?? $item['price'] ?? 0;
                            $quantity = $item['quantity'] ?? 1;
                            $itemTotal = $unitPrice * $quantity;
                            $subtotal += $itemTotal;
                        ?>
                            <tr>
                                <td class="product-cell">
                                    <img src="<?php echo htmlspecialchars($item['imageUrl'] ?? 'images/placeholder.jpg'); ?>" 
                                         alt="<?php echo htmlspecialchars($item['productName'] ?? 'Product'); ?>">
                                    <div>
                                        <h4><?php echo htmlspecialchars($item['productName'] ?? 'Unknown Product'); ?></h4>
                                        <?php if (isset($item['productId'])): ?>
                                            <a href="?page=product-detail&id=<?php echo intval($item['productId']); ?>" class="view-product">
                                                View Product
                                            </a>
                                        <?php endif; ?>
                                    </div>
                                </td>
                                <td><?php echo formatCurrency($unitPrice); ?></td>
                                <td><?php echo intval($quantity); ?></td>
                                <td><strong><?php echo formatCurrency($itemTotal); ?></strong></td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>

                <!-- Order Summary -->
                <div class="order-summary">
                    <div class="summary-row">
                        <span>Subtotal:</span>
                        <span><?php echo formatCurrency($subtotal); ?></span>
                    </div>
                    <div class="summary-row">
                        <span>Shipping:</span>
                        <span><?php echo formatCurrency(($order['shippingCost'] ?? 0)); ?></span>
                    </div>
                    <div class="summary-row">
                        <span>Tax (8%):</span>
                        <span><?php echo formatCurrency($subtotal * 0.08); ?></span>
                    </div>
                    <div class="summary-row total">
                        <span>Total:</span>
                        <span><?php echo formatCurrency($order['totalAmount'] ?? $subtotal); ?></span>
                    </div>
                </div>
            </div>

            <!-- Order Actions -->
            <div class="order-actions">
                <?php if (($order['status'] ?? '') === 'Delivered'): ?>
                    <button class="btn btn-primary" onclick="window.location='?page=orders'">
                        Order Again
                    </button>
                <?php endif; ?>
                <a href="?page=orders" class="btn btn-outline">Back to Orders</a>
            </div>
        </div>
    </div>
</div>

<style>
.order-detail-page {
    padding: 2rem 0;
    min-height: 60vh;
}

.page-header {
    display: flex;
    justify-content: space-between;
    align-items: flex-start;
    margin-bottom: 2rem;
}

.back-link {
    display: inline-block;
    color: #007bff;
    text-decoration: none;
    margin-bottom: 0.5rem;
}

.back-link:hover {
    text-decoration: underline;
}

.page-header h1 {
    margin: 0;
    color: #333;
}

.order-detail-content {
    display: flex;
    flex-direction: column;
    gap: 1.5rem;
}

.info-card {
    background: white;
    border-radius: 8px;
    padding: 2rem;
    box-shadow: 0 2px 4px rgba(0,0,0,0.1);
}

.info-card h2 {
    margin: 0 0 1.5rem;
    color: #333;
    font-size: 1.25rem;
    padding-bottom: 1rem;
    border-bottom: 1px solid #eee;
}

.info-grid {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
    gap: 1.5rem;
}

.info-item {
    display: flex;
    flex-direction: column;
    gap: 0.5rem;
}

.info-item label {
    font-weight: 600;
    color: #666;
    font-size: 0.9rem;
}

.info-item span {
    color: #333;
    font-size: 1rem;
}

.items-table {
    width: 100%;
    border-collapse: collapse;
}

.items-table thead th {
    text-align: left;
    padding: 1rem;
    background: #f8f9fa;
    color: #666;
    font-weight: 600;
    border-bottom: 2px solid #dee2e6;
}

.items-table tbody td {
    padding: 1rem;
    border-bottom: 1px solid #eee;
}

.product-cell {
    display: flex;
    align-items: center;
    gap: 1rem;
}

.product-cell img {
    width: 60px;
    height: 60px;
    object-fit: cover;
    border-radius: 4px;
}

.product-cell h4 {
    margin: 0 0 0.25rem;
    color: #333;
    font-size: 1rem;
}

.view-product {
    color: #007bff;
    font-size: 0.85rem;
    text-decoration: none;
}

.view-product:hover {
    text-decoration: underline;
}

.order-summary {
    margin-top: 2rem;
    padding-top: 1rem;
    border-top: 2px solid #eee;
    max-width: 400px;
    margin-left: auto;
}

.summary-row {
    display: flex;
    justify-content: space-between;
    padding: 0.5rem 0;
    color: #666;
}

.summary-row.total {
    font-size: 1.25rem;
    font-weight: 600;
    color: #333;
    padding-top: 1rem;
    border-top: 1px solid #eee;
}

.summary-row.total span:last-child {
    color: #007bff;
}

.order-actions {
    display: flex;
    gap: 1rem;
    justify-content: flex-end;
}

@media (max-width: 768px) {
    .page-header {
        flex-direction: column;
        gap: 1rem;
    }
    
    .items-table {
        font-size: 0.9rem;
    }
    
    .items-table thead {
        display: none;
    }
    
    .items-table tbody td {
        display: block;
        padding: 0.5rem 0;
    }
    
    .items-table tbody tr {
        display: block;
        padding: 1rem;
        border-bottom: 2px solid #eee;
    }
    
    .order-actions {
        flex-direction: column;
    }
}
</style>

