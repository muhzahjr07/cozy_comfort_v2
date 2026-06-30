<?php
$error = "Page Not Found";
$statusCode = http_response_code();

switch ($statusCode) {
    case 404:
        $error = "Page Not Found";
        $message = "The page you're looking for doesn't exist.";
        break;
    case 403:
        $error = "Access Forbidden";
        $message = "You don't have permission to access this page.";
        break;
    case 500:
        $error = "Server Error";
        $message = "Something went wrong on our end. Please try again later.";
        break;
    default:
        $message = "An unexpected error occurred.";
}
?>

<div class="error-page">
    <div class="container">
        <div class="error-content">
            <div class="error-icon">
                <?php echo $statusCode === 404 ? '🔍' : '⚠️'; ?>
            </div>
            <h1><?php echo $statusCode; ?></h1>
            <h2><?php echo $error; ?></h2>
            <p><?php echo $message; ?></p>
            
            <div class="error-actions">
                <a href="?page=home" class="btn btn-primary">Go Home</a>
                <a href="?page=products" class="btn btn-outline">Browse Products</a>
            </div>
        </div>
    </div>
</div>
