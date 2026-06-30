<?php
// API Configuration
define('API_BASE_URL', getenv('API_BASE_URL') ?: 'http://localhost:5000/api');
define('API_TIMEOUT', 30);

// Site Configuration
define('SITE_NAME', 'Cozy Comfort');
define('SITE_URL', getenv('SITE_URL') ?: 'http://localhost:8000');
define('SITE_TIMEZONE', 'UTC');

// Database Configuration (for local cache only)
define('DB_HOST', getenv('DB_HOST') ?: 'localhost');
define('DB_USER', getenv('DB_USER') ?: 'root');
define('DB_PASS', getenv('DB_PASS') ?: '');
define('DB_NAME', getenv('DB_NAME') ?: 'CozyComfort_cache');

// Session Configuration
define('SESSION_TIMEOUT', 3600); // 1 hour
define('SESSION_COOKIE_SECURE', getenv('SESSION_COOKIE_SECURE') === 'true');
define('SESSION_COOKIE_HTTPONLY', true);

// Email Configuration
define('ADMIN_EMAIL', getenv('ADMIN_EMAIL') ?: 'admin@CozyComfort.com');
define('SUPPORT_EMAIL', getenv('SUPPORT_EMAIL') ?: 'support@CozyComfort.com');

// Pagination
define('ITEMS_PER_PAGE', 12);
define('ORDERS_PER_PAGE', 10);

// Error Reporting (disable in production)
if (getenv('APP_ENV') === 'production') {
    error_reporting(0);
    ini_set('display_errors', 0);
} else {
    error_reporting(E_ALL);
    ini_set('display_errors', 1);
}

// Timezone
date_default_timezone_set(SITE_TIMEZONE);
?>
