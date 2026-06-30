<?php
require_once 'includes/config.php';

$apiClient = getApiClient();

echo '<h2>Testing API Client Structure</h2>';
echo '<pre style="background: #f5f5f5; padding: 10px; border-radius: 5px;">';

// Test getProducts
echo "=== getProducts() response structure ===\n";
$response = $apiClient->getProducts();
echo "Response type: " . gettype($response) . "\n";
echo "Response keys: " . implode(', ', array_keys($response)) . "\n";
echo "Response['data'] type: " . (isset($response['data']) ? gettype($response['data']) : 'not set') . "\n";

if (isset($response['data'])) {
    $dataContent = $response['data'];
    echo "Response['data'] is array: " . (is_array($dataContent) ? 'YES' : 'NO') . "\n";
    
    if (is_array($dataContent) && count($dataContent) > 0) {
        echo "First element of response['data']:\n";
        echo "  - Type: " . gettype($dataContent[0]) . "\n";
        
        if (is_array($dataContent[0])) {
            echo "  - Keys: " . implode(', ', array_slice(array_keys($dataContent[0]), 0, 5)) . "...\n";
            echo "  - Has 'id' key: " . (isset($dataContent[0]['id']) ? 'YES' : 'NO') . "\n";
            echo "  - Has 'name' key: " . (isset($dataContent[0]['name']) ? 'YES' : 'NO') . "\n";
        }
    }
    
    // Check for nested data structure
    if (is_array($dataContent) && isset($dataContent['data'])) {
        echo "Response['data']['data'] exists: YES\n";
        echo "Response['data']['data'] type: " . gettype($dataContent['data']) . "\n";
    }
}

echo "\n=== Extracted Products ===\n";
$products = [];
if (is_array($response)) {
    if (isset($response['data']) && is_array($response['data'])) {
        $dataValue = $response['data'];
        
        if (isset($dataValue['data']) && is_array($dataValue['data'])) {
            $products = $dataValue['data'];
            echo "Products extracted from response['data']['data']\n";
        } elseif (count($dataValue) > 0 && is_array($dataValue[0]) && isset($dataValue[0]['id'])) {
            $products = $dataValue;
            echo "Products extracted from response['data']\n";
        }
    }
}

echo "Total products extracted: " . count($products) . "\n";
if (count($products) > 0) {
    echo "First product name: " . $products[0]['name'] . "\n";
    echo "First product price: " . $products[0]['price'] . "\n";
}

echo '</pre>';
?>

