<?php
// Load environment variables from .env file
if (file_exists(__DIR__ . '/../.env')) {
    $envFile = __DIR__ . '/../.env';
    $lines = file($envFile, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
    
    foreach ($lines as $line) {
        if (strpos(trim($line), '#') === 0) {
            continue;
        }
        
        list($key, $value) = explode('=', $line, 2);
        $key = trim($key);
        $value = trim($value);
        
        if (!array_key_exists($key, $_SERVER) && !array_key_exists($key, $_ENV)) {
            putenv(sprintf('%s=%s', $key, $value));
            $_ENV[$key] = $value;
            $_SERVER[$key] = $value;
        }
    }
}

// Site configuration
define('SITE_NAME', $_ENV['SITE_NAME'] ?? 'Cozy Comfort');
define('SITE_URL', $_ENV['SITE_URL'] ?? 'http://localhost/CozyComfort-web');
define('API_BASE_URL', $_ENV['API_BASE_URL'] ?? 'https://localhost:7102');

// Database configuration (if needed for local features)
define('DB_HOST', $_ENV['DB_HOST'] ?? 'localhost');
define('DB_NAME', $_ENV['DB_NAME'] ?? 'CozyComfort');
define('DB_USER', $_ENV['DB_USER'] ?? 'root');
define('DB_PASS', $_ENV['DB_PASS'] ?? '');

// Security settings
define('SECRET_KEY', $_ENV['SECRET_KEY'] ?? 'your-secret-key-here');
define('ENCRYPTION_METHOD', 'AES-256-CBC');

// Application settings
define('DEBUG_MODE', filter_var($_ENV['DEBUG_MODE'] ?? false, FILTER_VALIDATE_BOOLEAN));
define('ITEMS_PER_PAGE', intval($_ENV['ITEMS_PER_PAGE'] ?? 12));
define('SESSION_LIFETIME', intval($_ENV['SESSION_LIFETIME'] ?? 3600));
define('CURRENCY_SYMBOL', $_ENV['CURRENCY_SYMBOL'] ?? 'Rs.');
define('SUPPORT_EMAIL', 'support@CozyComfort.lk');

// Include required files
require_once __DIR__ . '/helpers.php';
require_once __DIR__ . '/validators.php';
require_once __DIR__ . '/ApiClient.php';

// Error reporting based on debug mode
if (DEBUG_MODE) {
    error_reporting(E_ALL);
    ini_set('display_errors', 1);
} else {
    error_reporting(0);
    ini_set('display_errors', 0);
}

// Set timezone
date_default_timezone_set($_ENV['TIMEZONE'] ?? 'America/New_York');
?>
