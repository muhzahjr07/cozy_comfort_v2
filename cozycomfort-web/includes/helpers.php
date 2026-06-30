<?php

/**
 * Get global API client instance
 */
function getApiClient() {
    if (!isset($GLOBALS['apiClient'])) {
        $GLOBALS['apiClient'] = new ApiClient(API_BASE_URL ?? 'https://localhost:7102');
    }
    return $GLOBALS['apiClient'];
}

/**
 * Format currency for display
 */
function formatCurrency($amount) {
    return CURRENCY_SYMBOL . number_format($amount, 2);
}

/**
 * Check if user is logged in
 */
function isLoggedIn() {
    return (isset($_SESSION['user_id']) && !empty($_SESSION['user_id'])) 
        || (isset($_SESSION['email']) && !empty($_SESSION['email']));
}

/**
 * Format date for display
 */
function formatDate($date, $format = 'M d, Y') {
    return date($format, strtotime($date));
}

/**
 * Sanitize input string
 */
function sanitize($input) {
    return htmlspecialchars(trim($input), ENT_QUOTES, 'UTF-8');
}

/**
 * Validate email format
 */
function isValidEmail($email) {
    return filter_var($email, FILTER_VALIDATE_EMAIL) !== false;
}

/**
 * Validate URL
 */
function isValidUrl($url) {
    return filter_var($url, FILTER_VALIDATE_URL) !== false;
}

/**
 * Redirect to URL
 */
function redirect($url) {
    header('Location: ' . $url);
    exit();
}

/**
 * Get query parameter safely
 */
function getParam($key, $default = null) {
    return isset($_GET[$key]) ? sanitize($_GET[$key]) : $default;
}

/**
 * Get POST parameter safely
 */
function postParam($key, $default = null) {
    return isset($_POST[$key]) ? sanitize($_POST[$key]) : $default;
}

/**
 * Generate CSRF token
 */
function generateCsrfToken() {
    if (empty($_SESSION['csrf_token'])) {
        $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
    }
    return $_SESSION['csrf_token'];
}

/**
 * Verify CSRF token
 */
function verifyCsrfToken($token) {
    return isset($_SESSION['csrf_token']) && hash_equals($_SESSION['csrf_token'], $token);
}

/**
 * Show notification message
 */
function setNotification($message, $type = 'info') {
    $_SESSION['notification'] = [
        'message' => $message,
        'type' => $type // success, error, warning, info
    ];
}

/**
 * Get and clear notification
 */
function getNotification() {
    if (isset($_SESSION['notification'])) {
        $notification = $_SESSION['notification'];
        unset($_SESSION['notification']);
        return $notification;
    }
    return null;
}

/**
 * Check if user is admin
 */
function isAdmin() {
    return isset($_SESSION['role']) && $_SESSION['role'] === 'Admin';
}

/**
 * Require login
 */
function requireLogin() {
    if (!isset($_SESSION['email'])) {
        setNotification('Please log in first', 'warning');
        redirect('?page=login');
    }
}

/**
 * Require admin
 */
function requireAdmin() {
    requireLogin();
    if (!isAdmin()) {
        http_response_code(403);
        die('Access denied');
    }
}

/**
 * Get user ID from session
 */
function getUserId() {
    return $_SESSION['user_id'] ?? null;
}

/**
 * Pretty print for debugging
 */
function dd($data) {
    echo '<pre>';
    var_dump($data);
    echo '</pre>';
    exit;
}

?>
