<?php
// Session configuration - must be set before starting session
if (session_status() == PHP_SESSION_NONE) {
    // Get session settings from environment or use secure defaults
    $httpOnly = filter_var($_ENV['SESSION_COOKIE_HTTPONLY'] ?? true, FILTER_VALIDATE_BOOLEAN);
    $secure = filter_var($_ENV['SESSION_COOKIE_SECURE'] ?? false, FILTER_VALIDATE_BOOLEAN);
    
    ini_set('session.cookie_httponly', $httpOnly ? '1' : '0');
    ini_set('session.cookie_secure', $secure ? '1' : '0');
    ini_set('session.use_only_cookies', '1');
    ini_set('session.gc_maxlifetime', $_ENV['SESSION_LIFETIME'] ?? 3600);
    
    // Start session
    session_start();
}

// Regenerate session ID periodically for security
if (!isset($_SESSION['last_regeneration'])) {
    $_SESSION['last_regeneration'] = time();
} else if (time() - $_SESSION['last_regeneration'] > 300) {
    session_regenerate_id(true);
    $_SESSION['last_regeneration'] = time();
}
?>
