<?php
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET, POST, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type');

require_once '../includes/config.php';

// Check if user is logged in
if (!isLoggedIn()) {
    http_response_code(401);
    echo json_encode(['error' => 'Unauthorized access']);
    exit;
}

$orderId = intval($_GET['order_id'] ?? 0);

if (!$orderId) {
    http_response_code(400);
    echo json_encode(['error' => 'Invalid order ID']);
    exit;
}

try {
    $apiClient = getApiClient();
    
    // Get quotation comparison from API
    $response = $apiClient->getQuotationComparison($orderId);
    
    if ($response && isset($response['success']) && $response['success']) {
        echo json_encode([
            'success' => true,
            'data' => $response['data'],
            'timestamp' => time()
        ]);
    } else {
        echo json_encode([
            'success' => false,
            'message' => 'No quotations available yet',
            'data' => []
        ]);
    }
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'error' => 'Failed to retrieve quotations: ' . $e->getMessage()
    ]);
}
?>
