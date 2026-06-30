<nav class="navbar">
    <div class="nav-container">
        <div class="nav-brand">
            <a href="?page=home">
                <img src="images/logo.svg" alt="<?php echo SITE_NAME; ?>" class="logo" style="height: 40px;">
                <span class="brand-text"><?php echo SITE_NAME; ?></span>
            </a>
        </div>
        
        <div class="nav-menu" id="navMenu">
            <ul class="nav-links">
                <li><a href="?page=home" class="nav-link">Home</a></li>
                <li><a href="?page=products" class="nav-link">Products</a></li>
                
                <?php if (isset($_SESSION['email'])): ?>
                    <li><a href="?page=my-orders" class="nav-link">My Orders</a></li>
                    <li><a href="?page=profile" class="nav-link">Profile</a></li>
                    
                    <?php if (isAdmin()): ?>
                        <li class="dropdown">
                            <a href="#" class="nav-link dropdown-toggle" onclick="return false;">Admin ▼</a>
                            <ul class="dropdown-menu">
                                <li><a href="?page=admin-dashboard">Dashboard</a></li>
                                <li><a href="?page=admin-products">Products</a></li>
                                <li><a href="?page=admin-categories">Categories</a></li>
                                <li><a href="?page=admin-orders">Orders</a></li>
                                <li><a href="?page=admin-distributors">Distributors</a></li>
                                <li><a href="?page=admin-reports">Reports</a></li>
                            </ul>
                        </li>
                    <?php endif; ?>
                    
                    <li><a href="?page=logout" class="nav-link">Logout</a></li>
                <?php else: ?>
                    <li><a href="?page=login" class="nav-link">Login</a></li>
                    <li><a href="?page=register" class="nav-link">Register</a></li>
                <?php endif; ?>
            </ul>
        </div>
        
        <div class="nav-actions">
            <button class="theme-toggle" id="themeToggle" title="Switch Theme">
                <span class="theme-icon">🌙</span>
            </button>
            <a href="?page=cart" class="cart-icon">
                <img src="images/icons/cart.svg" alt="Cart">
                <span class="cart-count" id="cartCount">0</span>
            </a>
            
            <button class="menu-toggle" id="menuToggle">
                <img src="images/icons/menu.svg" alt="Menu">
            </button>
        </div>
    </div>
</nav>
