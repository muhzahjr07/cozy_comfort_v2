<?php
// Load config first to define all constants
require_once 'includes/config.php';
require_once 'includes/session.php';
require_once 'includes/helpers.php';
require_once 'includes/ApiClient.php';

// Create global API client instance
if (!isset($GLOBALS['apiClient'])) {
    $GLOBALS['apiClient'] = new ApiClient(API_BASE_URL);
}

// Get page from URL - handle both query string and URL rewriting
$page = 'home';

if (isset($_GET['page'])) {
    $page = sanitize($_GET['page']);
} elseif (isset($_SERVER['REQUEST_URI'])) {
    $requestUri = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);
    $basePath = '/CozyComfort-web/';
    
    if (strpos($requestUri, $basePath) === 0) {
        $page = substr($requestUri, strlen($basePath));
        $page = trim($page, '/');
        
        if (empty($page)) {
            $page = 'home';
        }
    }
}

// Clean the page parameter
$page = preg_replace('/[^a-zA-Z0-9\-_]/', '', $page);

// Determine layout
$isAdminPage = strpos($page, 'admin') === 0;
$loginPages = ['login', 'register', 'forgot-password'];
$isLoginPage = in_array($page, $loginPages);

// Start output buffering
ob_start();

// Route request to appropriate page
switch ($page) {
    // Authentication pages
    case 'login':
        require 'pages/login.php';
        break;
    
    case 'register':
        require 'pages/register.php';
        break;
    
    case 'forgot-password':
        require 'pages/forgot-password.php';
        break;
    
    case 'logout':
        require 'pages/logout.php';
        break;
    
    // Public pages
    case 'home':
        require 'pages/home.php';
        break;
    
    case 'products':
        require 'pages/products.php';
        break;
    
    case 'product-detail':
        require 'pages/product-detail.php';
        break;
    
    case 'cart':
        require 'pages/cart.php';
        break;
    
    case 'checkout':
        requireLogin();
        require 'pages/checkout.php';
        break;
    
    case 'order-confirmation':
        requireLogin();
        require 'pages/order-confirmation.php';
        break;
    
    case 'order-tracking':
        requireLogin();
        require 'pages/order-tracking.php';
        break;
    
    case 'my-orders':
        requireLogin();
        require 'pages/my-orders.php';
        break;
    
    case 'orders':
        requireLogin();
        require 'pages/orders.php';
        break;
    
    case 'order-detail':
        requireLogin();
        require 'pages/order-detail.php';
        break;
    
    case 'profile':
        requireLogin();
        require 'pages/profile.php';
        break;
    
    // Admin pages
    case 'admin-dashboard':
        requireAdmin();
        require 'pages/admin/dashboard.php';
        break;
    
    case 'admin-orders':
        requireAdmin();
        require 'pages/admin/orders.php';
        break;
    
    case 'admin-distributors':
        requireAdmin();
        require 'pages/admin/distributors.php';
        break;
    
    case 'admin-products':
        requireAdmin();
        require 'pages/admin/products.php';
        break;
    
    case 'admin-categories':
        requireAdmin();
        require 'pages/admin/categories.php';
        break;
    
    case 'admin-reports':
        requireAdmin();
        require 'pages/admin/reports.php';
        break;
    
    default:
        // If page doesn't exist, show 404 but also provide debug info in development
        http_response_code(404);
        if (getenv('APP_ENV') !== 'production') {
            echo "<div style='padding: 2rem; background: #f8f9fa; margin: 2rem; border-radius: 8px;'>";
            echo "<h2>Debug Info</h2>";
            echo "<p><strong>Requested page:</strong> " . htmlspecialchars($page) . "</p>";
            echo "<p><strong>Request URI:</strong> " . htmlspecialchars($_SERVER['REQUEST_URI'] ?? 'N/A') . "</p>";
            echo "<p><strong>Available pages:</strong> home, products, product-detail, login, register, cart, checkout, etc.</p>";
            echo "<p><a href='?page=home'>Go to Home Page</a></p>";
            echo "</div>";
        }
        require 'pages/error.php';
        break;
}

$content = ob_get_clean();

// Include appropriate layout
if ($isLoginPage) {
    require 'layouts/login-layout.php';
} else {
    require 'layouts/main-layout.php';
}
?>
