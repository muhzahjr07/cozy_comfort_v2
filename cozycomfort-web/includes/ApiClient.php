<?php

class ApiClient {
    private $baseUrl;
    private $timeout;
    private $token;

    public function __construct($baseUrl = null) {
        $this->baseUrl = $baseUrl ?: $_ENV['API_BASE_URL'];
        $this->timeout = 30;
        $this->token = null;
    }

    public function setToken($token) {
        $this->token = $token;
    }

    private function makeRequest($method, $endpoint, $data = null) {
        $url = rtrim($this->baseUrl, '/') . '/' . ltrim($endpoint, '/');
        
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_TIMEOUT, $this->timeout);
        curl_setopt($ch, CURLOPT_CUSTOMREQUEST, $method);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
        curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, false);

        // Set headers
        $headers = ['Content-Type: application/json'];
        if ($this->token) {
            $headers[] = 'Authorization: Bearer ' . $this->token;
        }
        curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);

        if ($data && ($method === 'POST' || $method === 'PUT' || $method === 'PATCH')) {
            curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($data));
        }

        $response = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        $error = curl_error($ch);
        curl_close($ch);

        if ($error) {
            error_log("cURL Error for {$url}: {$error}");
            throw new Exception("cURL Error: " . $error);
        }

        // Log the raw response for debugging
        error_log("API Response from {$url} (HTTP {$httpCode}): " . substr($response, 0, 500));

        $decodedResponse = json_decode($response, true);
        
        if ($httpCode >= 400) {
            $message = isset($decodedResponse['message']) ? $decodedResponse['message'] : 'API Error';
            error_log("API Error {$httpCode}: {$message}");
            throw new Exception($message, $httpCode);
        }

        if ($httpCode === 200 && empty($decodedResponse)) {
            error_log("Warning: Empty response from {$url}");
        }

        return [
            'statusCode' => $httpCode,
            'data' => $decodedResponse
        ];
    }

    // Products API
    public function getProducts($params = []) {
        $queryString = http_build_query($params);
        $endpoint = 'api/Products' . ($queryString ? '?' . $queryString : '');
        $result = $this->makeRequest('GET', $endpoint);
        return $result['data'];
    }

    public function getProductById($id) {
        $result = $this->makeRequest('GET', "api/Products/{$id}");
        return $result['data'];
    }

    public function createProduct($productData) {
        $result = $this->makeRequest('POST', 'api/Products', $productData);
        return $result['data'];
    }

    public function updateProduct($id, $productData) {
        $result = $this->makeRequest('PUT', "api/Products/{$id}", $productData);
        return $result['data'];
    }

    public function deleteProduct($id) {
        $result = $this->makeRequest('DELETE', "api/Products/{$id}");
        return $result['data'];
    }

    public function getProductCategories() {
        $result = $this->makeRequest('GET', 'api/Products/categories');
        // API returns {success, data: [strings], errors}
        // Extract the data array which contains the category strings
        if (isset($result['data']['data']) && is_array($result['data']['data'])) {
            return $result['data']['data'];
        }
        return $result['data'] ?? [];
    }

    public function getFeaturedProducts() {
        // Get featured products using regular products endpoint with small page size
        try {
            $result = $this->makeRequest('GET', 'api/Products?PageSize=8');
            // API returns {success, data: {data: [...products...], pagination...}}
            // Return the full paginated response structure
            return $result['data'];
        } catch (Exception $e) {
            error_log('getFeaturedProducts error: ' . $e->getMessage());
            return ['data' => [], 'totalCount' => 0];
        }
    }

    // Orders API
    public function getOrders($customerId = null, $page = 1, $pageSize = 10) {
        $params = compact('page', 'pageSize');
        if ($customerId) {
            $endpoint = "api/Orders/customer/{$customerId}";
        } else {
            $endpoint = 'api/Orders';
        }
        $queryString = http_build_query($params);
        $endpoint = $endpoint . ($queryString ? '?' . $queryString : '');
        $result = $this->makeRequest('GET', $endpoint);
        return $result['data'];
    }

    public function getOrderById($id) {
        $result = $this->makeRequest('GET', "api/Orders/{$id}");
        return $result['data'];
    }

    public function getOrderByReference($reference) {
        $result = $this->makeRequest('GET', "api/Orders/reference/{$reference}");
        return $result['data'];
    }

    public function getCustomerOrders($customerId, $page = 1, $pageSize = 10) {
        $params = compact('page', 'pageSize');
        $queryString = http_build_query($params);
        $endpoint = "api/Orders/customer/{$customerId}" . ($queryString ? '?' . $queryString : '');
        $result = $this->makeRequest('GET', $endpoint);
        return $result['data'];
    }

    public function createOrder($orderData) {
        $result = $this->makeRequest('POST', 'api/Orders', $orderData);
        return $result['data'];
    }

    public function deleteOrder($id) {
        $result = $this->makeRequest('DELETE', "api/Orders/{$id}");
        return $result['data'];
    }

    public function updateOrderStatus($id, $statusData) {
        $result = $this->makeRequest('PATCH', "api/Orders/{$id}/status", $statusData);
        return $result['data'];
    }

    // Distributors API
    public function getDistributors() {
        $result = $this->makeRequest('GET', 'api/Distributors');
        return $result['data'];
    }

    public function getDistributorById($id) {
        $result = $this->makeRequest('GET', "api/Distributors/{$id}");
        return $result['data'];
    }

    public function createDistributor($distributorData) {
        $result = $this->makeRequest('POST', 'api/Distributors', $distributorData);
        return $result['data'];
    }

    public function updateDistributor($id, $distributorData) {
        $result = $this->makeRequest('PUT', "api/Distributors/{$id}", $distributorData);
        return $result['data'];
    }

    public function deleteDistributor($id) {
        $result = $this->makeRequest('DELETE', "api/Distributors/{$id}");
        return $result['data'];
    }

    public function checkDistributorAvailability($id) {
        $result = $this->makeRequest('GET', "api/Distributors/{$id}/availability");
        return $result['data'];
    }

    public function getDistributorPerformance($id, $startDate = null, $endDate = null) {
        $params = array_filter(compact('startDate', 'endDate'));
        $queryString = http_build_query($params);
        $endpoint = "api/Distributors/{$id}/performance" . ($queryString ? '?' . $queryString : '');
        $result = $this->makeRequest('GET', $endpoint);
        return $result['data'];
    }

    // Quotations API
    public function getQuotationComparison($orderId) {
        $result = $this->makeRequest('GET', "api/Quotations/{$orderId}/compare");
        return $result['data'];
    }

    public function selectDistributor($orderId) {
        $result = $this->makeRequest('POST', "api/Quotations/{$orderId}/select-distributor");
        return $result['data'];
    }

    public function getQuotationHistory($orderId) {
        $result = $this->makeRequest('GET', "api/Quotations/{$orderId}/history");
        return $result['data'];
    }

    public function createQuotation($quotationData) {
        $result = $this->makeRequest('POST', 'api/Quotations', $quotationData);
        return $result['data'];
    }

    // Categories API (fallback for legacy compatibility)
    public function getCategories() {
        return $this->getProductCategories();
    }

    // Authentication API
    public function login($email, $password) {
        $result = $this->makeRequest('POST', 'api/Auth/login', [
            'email' => $email,
            'password' => $password
        ]);
        return $result; // Return full response including success flag
    }

    public function register($userData) {
        $result = $this->makeRequest('POST', 'api/Auth/register', $userData);
        return $result['data'];
    }

    // Generic methods for other endpoints
    public function get($endpoint, $params = []) {
        $queryString = http_build_query($params);
        $endpoint = $endpoint . ($queryString ? '?' . $queryString : '');
        $result = $this->makeRequest('GET', $endpoint);
        return $result['data'];
    }

    public function post($endpoint, $data = null) {
        return $this->makeRequest('POST', $endpoint, $data);
    }

    public function put($endpoint, $data = null) {
        return $this->makeRequest('PUT', $endpoint, $data);
    }

    public function delete($endpoint) {
        return $this->makeRequest('DELETE', $endpoint);
    }
}
?>
