<?php
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: POST, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type');

require_once '../includes/config.php';

// Check if user is logged in
if (!isLoggedIn()) {
    http_response_code(401);
    echo json_encode(['error' => 'Unauthorized access']);
    exit;
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['error' => 'Method not allowed']);
    exit;
}

$input = json_decode(file_get_contents('php://input'), true);

$orderId = intval($input['order_id'] ?? 0);
$distributorId = intval($input['distributor_id'] ?? 0);

if (!$orderId || !$distributorId) {
    http_response_code(400);
    echo json_encode(['error' => 'Invalid order ID or distributor ID']);
    exit;
}

try {
    $apiClient = getApiClient();
    
    // Select distributor via API
    $response = $apiClient->post("/orders/{$orderId}/select-distributor", [
        'distributorId' => $distributorId,
        'customerId' => $_SESSION['user_id']
    ]);
    
    if ($response && isset($response['success']) && $response['success']) {
        echo json_encode([
            'success' => true,
            'message' => 'Distributor selected successfully',
            'data' => $response['data']
        ]);
    } else {
        echo json_encode([
            'success' => false,
            'message' => $response['message'] ?? 'Failed to select distributor'
        ]);
    }
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'error' => 'Failed to select distributor: ' . $e->getMessage()
    ]);
}
?>
