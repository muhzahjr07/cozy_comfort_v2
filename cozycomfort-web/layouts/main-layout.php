<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo SITE_NAME; ?> - <?php echo ucwords(str_replace(['-', 'admin-'], [' ', ''], $page ?? 'Home')); ?></title>
    <meta name="description" content="Cozy Comfort - Premium blankets, heated throws, and cozy bedding in Sri Lanka">
    <!-- Google Fonts: Inter -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
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
    <link rel="icon" type="image/x-icon" href="images/favicon.ico">
    
    <!-- CSS Files -->
    <link rel="stylesheet" href="css/CozyComfort.css">
    <link rel="stylesheet" href="css/style.css">
    <link rel="stylesheet" href="css/theme.css">
    <link rel="stylesheet" href="css/responsive.css">
    <link rel="stylesheet" href="css/navbar.css">
    <link rel="stylesheet" href="css/footer.css">
    <link rel="stylesheet" href="css/forms.css">
    <link rel="stylesheet" href="css/cards.css">
    <link rel="stylesheet" href="css/animations.css">
    <?php if ($isAdminPage): ?>
    <link rel="stylesheet" href="css/dashboard.css">
    <?php endif; ?>
</head>
<body class="<?php echo $isAdminPage ? 'admin-layout' : 'main-layout'; ?>">
    
    <?php include 'components/navbar.php'; ?>
    
    <?php if ($isAdminPage): ?>
        <?php include 'components/sidebar.php'; ?>
        <main class="admin-content">
    <?php else: ?>
        <main class="main-content">
    <?php endif; ?>
        
        <!-- Display notifications -->
        <?php 
        $notification = getNotification();
        if ($notification): 
        ?>
            <?php include 'components/alert.php'; ?>
        <?php endif; ?>
        
        <!-- Page content -->
        <?php echo $content; ?>
    
    </main>
    
    <?php if (!$isAdminPage): ?>
        <?php include 'components/footer.php'; ?>
    <?php endif; ?>
    
    <!-- Loading spinner -->
    <?php include 'components/loader.php'; ?>
    
    <!-- JavaScript Files -->
    <script src="js/navbar.js?v=<?php echo time(); ?>"></script>
    <script src="js/main.js?v=<?php echo time(); ?>"></script>
    <script src="js/ajax.js"></script>
    <script src="js/cart.js"></script>
    <script src="js/validation.js"></script>
    <script src="js/utils.js"></script>
    <?php if ($isAdminPage): ?>
    <script src="js/charts.js"></script>
    <?php endif; ?>
</body>
</html>
