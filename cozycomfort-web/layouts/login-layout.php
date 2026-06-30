<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login - <?php echo SITE_NAME; ?></title>
    <link rel="icon" type="image/x-icon" href="images/favicon.ico">
    <script>
        const API_BASE_URL = '<?php echo API_BASE_URL; ?>/api';
        <?php if (isset($_SESSION['token']) && $_SESSION['token']): ?>
            localStorage.setItem('auth_token', '<?php echo $_SESSION['token']; ?>');
            localStorage.setItem('user_id', '<?php echo $_SESSION['user_id']; ?>');
        <?php else: ?>
            localStorage.removeItem('auth_token');
            localStorage.removeItem('user_id');
        <?php endif; ?>
    </script>
    
    <!-- CSS Files -->
    <link rel="stylesheet" href="css/style.css">
    <link rel="stylesheet" href="css/theme.css">
    <link rel="stylesheet" href="css/forms.css">
    <link rel="stylesheet" href="css/responsive.css">
</head>
<body class="login-layout">
    <div class="login-container">
        <div class="login-header" style="position: relative;">
            <button class="theme-toggle" id="themeToggle" title="Switch Theme" style="position: absolute; top: 10px; right: 10px;">
                <span class="theme-icon">🌙</span>
            </button>
            <img src="images/logo.svg" alt="<?php echo SITE_NAME; ?>" class="logo" style="height: 50px;">
            <h1><?php echo SITE_NAME; ?></h1>
        </div>
        
        <!-- Display notifications -->
        <?php 
        $notification = getNotification();
        if ($notification): 
        ?>
            <?php include 'components/alert.php'; ?>
        <?php endif; ?>
        
        <!-- Page content -->
        <?php echo $content; ?>
        
        <div class="login-footer">
            <p>&copy; <?php echo date('Y'); ?> <?php echo SITE_NAME; ?>. All rights reserved.</p>
        </div>
    </div>
    
    <!-- JavaScript Files -->
    <script src="js/main.js"></script>
    <script src="js/validation.js"></script>
</body>
</html>
