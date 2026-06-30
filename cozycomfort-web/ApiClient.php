<?php
class ApiClient {
    private $baseUrl;
    private $timeout = 30;
    private $lastError;
    private $lastStatusCode;
    private $token;

    public function __construct($apiUrl) {
        $this->baseUrl = rtrim($apiUrl, '/');
    }

    /**
     * GET request - handles ApiResponse<T> wrapper
     */
    public function get($endpoint, $params = []) {
        return $this->request('GET', $endpoint, [], $params);
    }

    /**
     * POST request - handles ApiResponse<T> wrapper
     */
    public function post($endpoint, $data = []) {
        return $this->request('POST', $endpoint, $data);
    }

    /**
     * PUT request - handles ApiResponse<T> wrapper
     */
    public function put($endpoint, $data = []) {
        return $this->request('PUT', $endpoint, $data);
    }

    /**
     * DELETE request
     */
    public function delete($endpoint) {
        return $this->request('DELETE', $endpoint, []);
    }

    /**
     * Set authentication token
     */
    public function setToken($token) {
        $this->token = $token;
    }

    /**
     * Main request method - handles .NET API DTOs
     */
    public function request($method, $endpoint, $data = [], $params = []) {
        $url = $this->buildUrl($endpoint, $params);
        
        $ch = curl_init($url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_TIMEOUT, $this->timeout);
        curl_setopt($ch, CURLOPT_CUSTOMREQUEST, $method);
        curl_setopt($ch, CURLOPT_HTTPHEADER, $this->buildHeaders());

        if ($method !== 'GET' && !empty($data)) {
            curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($data));
        }

        $response = curl_exec($ch);
        $statusCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        $this->lastStatusCode = $statusCode;
        
        if (curl_errno($ch)) {
            $this->lastError = curl_error($ch);
            curl_close($ch);
            return null;
        }
        
        curl_close($ch);

        if ($response === false) {
            $this->lastError = 'cURL error occurred';
            return null;
        }

        $decoded = json_decode($response, true);
        if (json_last_error() !== JSON_ERROR_NONE) {
            $this->lastError = 'JSON decode error: ' . json_last_error_msg();
            return null;
        }

        // Handle .NET ApiResponse<T> wrapper format
        if (isset($decoded['success'])) {
            if ($decoded['success']) {
                return $decoded['data']; // Return unwrapped data
            } else {
                $this->lastError = $decoded['message'] ?? 'Unknown API error';
                if (isset($decoded['errors']) && is_array($decoded['errors'])) {
                    $this->lastError .= ': ' . implode(', ', $decoded['errors']);
                }
                return null;
            }
        }

        // Handle direct responses (non-wrapped)
        if ($statusCode >= 200 && $statusCode < 300) {
            return $decoded;
        } else {
            $this->lastError = $decoded['message'] ?? 'HTTP Error ' . $statusCode;
            return null;
        }
    }

    /**
     * Build full URL with query parameters
     */
    private function buildUrl($endpoint, $params = []) {
        $endpoint = ltrim($endpoint, '/');
        $url = $this->baseUrl . '/' . $endpoint;

        if (!empty($params)) {
            $url .= '?' . http_build_query($params);
        }

        return $url;
    }

    /**
     * Build request headers
     */
    private function buildHeaders() {
        $headers = [
            'Content-Type: application/json',
            'Accept: application/json'
        ];

        if ($this->token) {
            $headers[] = 'Authorization: Bearer ' . $this->token;
        }

        return $headers;
    }

    /**
     * Get last error message
     */
    public function getLastError() {
        return $this->lastError;
    }

    /**
     * Get last HTTP status code
     */
    public function getLastStatusCode() {
        return $this->lastStatusCode;
    }

    /**
     * Check if last request was successful
     */
    public function isSuccess() {
        return $this->lastStatusCode >= 200 && $this->lastStatusCode < 300;
    }
}
?>
