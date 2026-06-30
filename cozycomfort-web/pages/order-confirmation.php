<?php
// Check if user is logged in
if (!isLoggedIn()) {
    setNotification('Please login to view order confirmation.', 'error');
    redirect('?page=login');
}

$orderId = getParam('order_id') ?? getParam('orderId');

if (!$orderId) {
    setNotification('Invalid order ID.', 'error');
    redirect('?page=orders');
}

$apiClient = getApiClient();

// Get order details
try {
    $orderResponse = $apiClient->get("/orders/{$orderId}");
    $order = null;
    
    if (isset($orderResponse['data'])) {
        $order = $orderResponse['data'];
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

<div class="order-confirmation-page">
    <div class="container">
        <div class="confirmation-header">
            <div class="success-icon">
                ✅
            </div>
            <h1>Order Confirmed!</h1>
            <p>Thank you for your order. Your order has been successfully placed and is being processed.</p>
        </div>

        <div class="order-confirmation-content">
            <!-- Order Summary Card -->
            <div class="confirmation-card">
                <h2>Order Summary</h2>
                
                <div class="order-info-grid">
                    <div class="info-item">
                        <label>Order Number:</label>
                        <span><?php echo htmlspecialchars($order['orderReference'] ?? $order['orderNumber'] ?? $orderId); ?></span>
                    </div>
                    <div class="info-item">
                        <label>Order Date:</label>
                        <span><?php echo date('F j, Y g:i A', strtotime($order['orderDate'] ?? 'now')); ?></span>
                    </div>
                    <div class="info-item">
                        <label>Status:</label>
                        <span class="status-badge"><?php echo htmlspecialchars($order['status'] ?? 'Processing'); ?></span>
                    </div>
                    <div class="info-item">
                        <label>Estimated Delivery:</label>
                        <span><?php echo isset($order['estimatedDeliveryDate']) ? date('F j, Y', strtotime($order['estimatedDeliveryDate'])) : '3-5 business days'; ?></span>
                    </div>
                </div>
            </div>

            <!-- Items Summary -->
            <div class="confirmation-card">
                <h2>Items Ordered</h2>
                <div class="order-items-summary">
                    <?php foreach ($items as $item): ?>
                        <div class="item-summary">
                            <img src="<?php echo htmlspecialchars($item['imageUrl'] ?? 'images/placeholder.jpg'); ?>" 
                                 alt="<?php echo htmlspecialchars($item['productName'] ?? 'Product'); ?>">
                            <div class="item-details">
                                <h4><?php echo htmlspecialchars($item['productName'] ?? 'Unknown Product'); ?></h4>
                                <p>Quantity: <?php echo intval($item['quantity'] ?? 1); ?></p>
                                <p class="item-price"><?php echo formatCurrency(($item['unitPrice'] ?? $item['price'] ?? 0) * ($item['quantity'] ?? 1)); ?></p>
                            </div>
                        </div>
                    <?php endforeach; ?>
                    
                    <div class="order-total">
                        <strong>Total: <?php echo formatCurrency($order['totalAmount'] ?? 0); ?></strong>
                    </div>
                </div>
            </div>

            <!-- Quotation Status -->
            <div class="confirmation-card">
                <h2>Distributor Quotation Status</h2>
                <div id="quotationStatus">
                    <div class="quotation-loading">
                        <div class="loading-spinner-small"></div>
                        <p>Requesting quotations from distributors...</p>
                    </div>
                </div>
                
                <div id="quotationComparison" style="display: none;">
                    <!-- Quotation comparison will be populated by JavaScript -->
                </div>
            </div>

            <!-- Next Steps -->
            <div class="confirmation-card">
                <h2>What Happens Next?</h2>
                <div class="next-steps">
                    <div class="step">
                        <div class="step-number">1</div>
                        <div class="step-content">
                            <h4>Quotation Collection</h4>
                            <p>We're collecting quotations from our network of distributors to get you the best price and fastest delivery.</p>
                        </div>
                    </div>
                    <div class="step">
                        <div class="step-number">2</div>
                        <div class="step-content">
                            <h4>Best Option Selection</h4>
                            <p>Once quotations are available, you can compare and select the best distributor based on price and delivery time.</p>
                        </div>
                    </div>
                    <div class="step">
                        <div class="step-number">3</div>
                        <div class="step-content">
                            <h4>Order Processing</h4>
                            <p>Your selected distributor will process and ship your order. You'll receive tracking information via email.</p>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Action Buttons -->
            <div class="confirmation-actions">
                <a href="?page=orders" class="btn btn-outline">View All Orders</a>
                <a href="?page=products" class="btn btn-primary">Continue Shopping</a>
            </div>
        </div>
    </div>
</div>
            </div>
            
            <?php if ($quotations && !empty($quotations['quotations'])): ?>
            <div class="quotations-card">
                <h2>Quotation Status</h2>
                
                <?php if (!empty($quotations['recommendedQuotation'])): ?>
                <div class="recommended-quotation">
                    <h3>✨ Recommended Supplier</h3>
                    <div class="quotation-info">
                        <div class="supplier-name">
                            <?php echo htmlspecialchars($quotations['recommendedQuotation']['distributorName']); ?>
                        </div>
                        <div class="quotation-details">
                            <span>Total Cost: <strong><?php echo formatCurrency($quotations['recommendedQuotation']['totalCost']); ?></strong></span>
                            <span>Delivery: <?php echo formatDate($quotations['recommendedQuotation']['estimatedDeliveryTime']); ?></span>
                            <span>Available: <?php echo $quotations['recommendedQuotation']['availableUnits']; ?>/<?php echo $quotations['recommendedQuotation']['requestedUnits']; ?> units</span>
                        </div>
                    </div>
                </div>
                <?php endif; ?>
                
                <div class="all-quotations">
                    <h4>All Quotations</h4>
                    <div class="quotations-grid">
                        <?php foreach ($quotations['quotations'] as $quote): ?>
                        <div class="quotation-card <?php echo $quote === $quotations['recommendedQuotation'] ? 'recommended' : ''; ?>">
                            <h5><?php echo htmlspecialchars($quote['distributorName']); ?></h5>
                            <div class="quote-details">
                                <p><strong>Cost:</strong> <?php echo formatCurrency($quote['totalCost']); ?></p>
                                <p><strong>Delivery:</strong> <?php echo formatDate($quote['estimatedDeliveryTime']); ?></p>
                                <p><strong>Status:</strong> 
                                    <span class="status-<?php echo strtolower($quote['status']); ?>">
                                        <?php echo htmlspecialchars($quote['status']); ?>
                                    </span>
                                </p>
                                <p><strong>Available:</strong> <?php echo $quote['availableUnits']; ?>/<?php echo $quote['requestedUnits']; ?> units</p>
                            </div>
                        </div>
                        <?php endforeach; ?>
                    </div>
                    
                    <?php if (!empty($quotations['comparisonSummary'])): ?>
                    <div class="comparison-summary">
                        <h5>Summary</h5>
                        <p><?php echo htmlspecialchars($quotations['comparisonSummary']); ?></p>
                    </div>
                    <?php endif; ?>
                </div>
            </div>
            <?php endif; ?>
            
            <div class="delivery-info-card">
                <h2>Delivery Information</h2>
                <div class="delivery-address">
                    <h4>Delivery Address:</h4>
                    <p><?php echo nl2br(htmlspecialchars($order['deliveryAddress'] ?? 'Not specified')); ?></p>
                </div>
                
                <?php if (!empty($order['phoneNumber'])): ?>
                <div class="contact-info">
                    <h4>Contact Number:</h4>
                    <p><?php echo htmlspecialchars($order['phoneNumber']); ?></p>
                </div>
                <?php endif; ?>
                
                <?php if (!empty($order['notes'])): ?>
                <div class="special-instructions">
                    <h4>Special Instructions:</h4>
                    <p><?php echo nl2br(htmlspecialchars($order['notes'])); ?></p>
                </div>
                <?php endif; ?>
            </div>
        </div>
        
        <div class="order-actions">
            <a href="?page=order-tracking&orderId=<?php echo $order['orderId']; ?>" class="btn btn-primary">
                Track Your Order
            </a>
            <a href="?page=my-orders" class="btn btn-outline">
                View All Orders
            </a>
            <a href="?page=products" class="btn btn-outline">
                Continue Shopping
            </a>
        </div>
        
        <div class="next-steps">
            <h3>What happens next?</h3>
            <div class="steps">
                <div class="step">
                    <div class="step-number">1</div>
                    <div class="step-content">
                        <h4>Order Processing</h4>
                        <p>We'll review your order and start gathering quotations from our suppliers.</p>
                    </div>
                </div>
                <div class="step">
                    <div class="step-number">2</div>
                    <div class="step-content">
                        <h4>Quotation Selection</h4>
                        <p>We'll select the best quotation based on price, availability, and delivery time.</p>
                    </div>
                </div>
                <div class="step">
                    <div class="step-number">3</div>
                    <div class="step-content">
                        <h4>Shipping</h4>
                        <p>Your order will be packed and shipped to your address.</p>
                    </div>
                </div>
                <div class="step">
                    <div class="step-number">4</div>
                    <div class="step-content">
                        <h4>Delivery</h4>
                        <p>You'll receive your products within the estimated delivery time.</p>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<style>
.order-confirmation-page {
    padding: 2rem 0;
}

.confirmation-header {
    text-align: center;
    margin-bottom: 3rem;
}

.success-icon {
    font-size: 4rem;
    margin-bottom: 1rem;
}

.confirmation-header h1 {
    color: #28a745;
    margin-bottom: 1rem;
}

.order-details {
    display: grid;
    gap: 2rem;
    margin-bottom: 3rem;
}

.order-info-card,
.order-items-card,
.quotations-card,
.delivery-info-card {
    background: white;
    padding: 2rem;
    border-radius: 12px;
    border: 1px solid #e9ecef;
}

.order-meta {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
    gap: 1rem;
}

.meta-item {
    display: flex;
    justify-content: space-between;
    align-items: center;
    padding: 0.75rem;
    background: #f8f9fa;
    border-radius: 6px;
}

.status-badge {
    padding: 0.25rem 0.75rem;
    border-radius: 20px;
    font-size: 0.875rem;
    font-weight: 500;
}

.status-pending { background: #fff3cd; color: #856404; }
.status-confirmed { background: #d4edda; color: #155724; }
.status-processing { background: #cce5ff; color: #004085; }
.status-shipped { background: #e2e3e5; color: #383d41; }

.total-amount {
    font-size: 1.25rem;
    font-weight: bold;
    color: #28a745;
}

.order-item {
    display: flex;
    align-items: center;
    padding: 1rem;
    border-bottom: 1px solid #f8f9fa;
    gap: 1rem;
}

.order-item:last-child {
    border-bottom: none;
}

.item-image img {
    width: 80px;
    height: 80px;
    object-fit: cover;
    border-radius: 8px;
}

.item-details {
    flex: 1;
}

.item-details h4 {
    margin-bottom: 0.5rem;
}

.item-total {
    font-weight: bold;
    font-size: 1.125rem;
    color: #28a745;
}

.recommended-quotation {
    background: linear-gradient(135deg, #28a745, #20c997);
    color: white;
    padding: 1.5rem;
    border-radius: 12px;
    margin-bottom: 2rem;
}

.quotation-info {
    display: flex;
    justify-content: space-between;
    align-items: center;
    margin-top: 1rem;
}

.supplier-name {
    font-size: 1.25rem;
    font-weight: bold;
}

.quotation-details {
    text-align: right;
}

.quotation-details span {
    display: block;
    margin-bottom: 0.25rem;
}

.quotations-grid {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
    gap: 1rem;
    margin: 1rem 0;
}

.quotation-card {
    background: #f8f9fa;
    padding: 1.5rem;
    border-radius: 8px;
    border: 1px solid #e9ecef;
}

.quotation-card.recommended {
    border-color: #28a745;
    background: #f8fff9;
}

.comparison-summary {
    background: #e9ecef;
    padding: 1rem;
    border-radius: 8px;
    margin-top: 1rem;
}

.order-actions {
    display: flex;
    justify-content: center;
    gap: 1rem;
    margin-bottom: 3rem;
    flex-wrap: wrap;
}

.next-steps {
    background: white;
    padding: 2rem;
    border-radius: 12px;
    border: 1px solid #e9ecef;
}

.steps {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
    gap: 1.5rem;
    margin-top: 1.5rem;
}

.step {
    display: flex;
    align-items: flex-start;
    gap: 1rem;
}

.step-number {
    background: #007bff;
    color: white;
    width: 30px;
    height: 30px;
    border-radius: 50%;
    display: flex;
    align-items: center;
    justify-content: center;
    font-weight: bold;
    flex-shrink: 0;
}

.step-content h4 {
    margin-bottom: 0.5rem;
    color: #2c3e50;
}

@media (max-width: 768px) {
    .order-meta {
        grid-template-columns: 1fr;
    }
    
    .meta-item {
        flex-direction: column;
        text-align: center;
        gap: 0.5rem;
    }
    
    .quotation-info {
        flex-direction: column;
        text-align: center;
        gap: 1rem;
    }
    
    .order-actions {
        flex-direction: column;
        align-items: center;
    }
    
    .order-actions .btn {
        width: 100%;
        max-width: 300px;
    }
    
    .steps {
        grid-template-columns: 1fr;
    }
}
</style>
