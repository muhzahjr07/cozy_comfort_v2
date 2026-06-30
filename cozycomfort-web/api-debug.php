<?php
require_once 'includes/config.php';
require_once 'includes/session.php';
require_once 'includes/helpers.php';
require_once 'includes/ApiClient.php';

$apiClient = getApiClient();

echo "<html><head><title>API Debug</title></head><body>";
echo "<h1>CozyComfort API Debug</h1>";
echo "<pre>";

echo "\n=== Testing API Connection ===\n";
echo "API Base URL: " . API_BASE_URL . "\n\n";

// Test 1: Get Products
echo "=== Test 1: Get Products (PageSize=3) ===\n";
try {
    $response = $apiClient->getFeaturedProducts();
    echo "Response Type: " . gettype($response) . "\n";
    echo "Response Structure:\n";
    print_r($response);
    echo "\n";
} catch (Exception $e) {
    echo "ERROR: " . $e->getMessage() . "\n";
    echo "Code: " . $e->getCode() . "\n\n";
}

// Test 2: Get Categories
echo "=== Test 2: Get Product Categories ===\n";
try {
    $categories = $apiClient->getProductCategories();
    echo "Response Type: " . gettype($categories) . "\n";
    echo "Categories:\n";
    print_r($categories);
    echo "\n";
} catch (Exception $e) {
    echo "ERROR: " . $e->getMessage() . "\n";
    echo "Code: " . $e->getCode() . "\n\n";
}

// Test 3: Direct API call
echo "=== Test 3: Direct API Call to /api/Products ===\n";
try {
    $response = $apiClient->get('api/Products', ['PageSize' => 3]);
    echo "Response Type: " . gettype($response) . "\n";
    echo "Response:\n";
    print_r($response);
    echo "\n";
} catch (Exception $e) {
    echo "ERROR: " . $e->getMessage() . "\n";
    echo "Code: " . $e->getCode() . "\n\n";
}

echo "</pre></body></html>";
?>
