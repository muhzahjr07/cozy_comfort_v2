<aside class="admin-sidebar">
    <div class="sidebar-header">
        <div class="sidebar-logo">
            <span class="logo-icon">🛋️</span>
            <h2>Admin Panel</h2>
        </div>
    </div>
    
    <nav class="sidebar-nav">
        <ul>
            <li class="<?php echo $page === 'admin-dashboard' ? 'active' : ''; ?>">
                <a href="?page=admin-dashboard">
                    <span class="icon">📊</span>
                    <span>Dashboard</span>
                </a>
            </li>
            
            <li class="<?php echo $page === 'admin-products' ? 'active' : ''; ?>">
                <a href="?page=admin-products">
                    <span class="icon">🛍️</span>
                    <span>Products</span>
                </a>
            </li>
            
            <li class="<?php echo $page === 'admin-categories' ? 'active' : ''; ?>">
                <a href="?page=admin-categories">
                    <span class="icon">📂</span>
                    <span>Categories</span>
                </a>
            </li>
            
            <li class="<?php echo $page === 'admin-orders' ? 'active' : ''; ?>">
                <a href="?page=admin-orders">
                    <span class="icon">📦</span>
                    <span>Orders</span>
                </a>
            </li>
            
            <li class="<?php echo $page === 'admin-distributors' ? 'active' : ''; ?>">
                <a href="?page=admin-distributors">
                    <span class="icon">🚚</span>
                    <span>Distributors</span>
                </a>
            </li>
            
            <li class="<?php echo $page === 'admin-reports' ? 'active' : ''; ?>">
                <a href="?page=admin-reports">
                    <span class="icon">📈</span>
                    <span>Reports</span>
                </a>
            </li>
        </ul>
    </nav>
    
    <div class="sidebar-footer">
        <!-- Theme Toggle inside sidebar -->
        <button class="theme-toggle sidebar-theme-btn" id="sidebarThemeToggle" title="Toggle Light/Dark Mode">
            <span class="theme-icon icon">🌙</span>
            <span class="sidebar-theme-label">Dark Mode</span>
        </button>
        <a href="?page=home" class="sidebar-back-btn">
            <span class="icon">🏠</span>
            <span>Back to Site</span>
        </a>
        <a href="?page=logout" class="sidebar-logout-btn">
            <span class="icon">🚪</span>
            <span>Logout</span>
        </a>
    </div>
</aside>

<style>
/* =============================================
   Admin Sidebar - Themed with CSS Variables
   ============================================= */

.admin-sidebar {
    position: fixed;
    left: 0;
    top: 0;
    width: 260px;
    height: 100vh;
    background: #1e2535;
    color: #c8d0e0;
    display: flex;
    flex-direction: column;
    z-index: 200;
    box-shadow: 3px 0 15px rgba(0, 0, 0, 0.3);
    transition: background 0.3s ease, box-shadow 0.3s ease;
}

body.dark .admin-sidebar {
    background: #0d1117;
    box-shadow: 3px 0 15px rgba(0, 0, 0, 0.6);
}

/* Sidebar Header */
.sidebar-header {
    padding: 20px 18px;
    background: rgba(0, 0, 0, 0.2);
    border-bottom: 1px solid rgba(255,255,255,0.07);
}

.sidebar-logo {
    display: flex;
    align-items: center;
    gap: 10px;
}

.sidebar-logo .logo-icon {
    font-size: 22px;
}

.sidebar-header h2 {
    margin: 0;
    font-size: 17px;
    font-weight: 700;
    color: #ffffff;
    letter-spacing: 0.5px;
}

/* Navigation */
.sidebar-nav {
    flex: 1;
    overflow-y: auto;
    padding: 12px 10px;
    scrollbar-width: thin;
    scrollbar-color: rgba(255,255,255,0.1) transparent;
}

.sidebar-nav::-webkit-scrollbar { width: 4px; }
.sidebar-nav::-webkit-scrollbar-track { background: transparent; }
.sidebar-nav::-webkit-scrollbar-thumb { background: rgba(255,255,255,0.12); border-radius: 4px; }

.sidebar-nav ul {
    list-style: none;
    padding: 0;
    margin: 0;
    display: flex;
    flex-direction: column;
    gap: 3px;
}

.sidebar-nav li {
    border-radius: 8px;
    overflow: hidden;
}

.sidebar-nav a {
    display: flex;
    align-items: center;
    padding: 12px 14px;
    color: #9aa5b9;
    text-decoration: none;
    transition: all 0.2s ease;
    border-radius: 8px;
    font-size: 0.9rem;
    font-weight: 500;
    gap: 12px;
}

.sidebar-nav a:hover {
    background: rgba(255,255,255,0.07);
    color: #ffffff;
    text-decoration: none;
    transform: translateX(3px);
}

.sidebar-nav li.active a {
    background: var(--primary-color, #8d5b4c);
    color: white;
    font-weight: 600;
    box-shadow: 0 3px 10px rgba(141, 91, 76, 0.4);
}

.sidebar-nav .icon {
    font-size: 17px;
    width: 20px;
    text-align: center;
    flex-shrink: 0;
}

/* Sidebar Footer */
.sidebar-footer {
    padding: 12px 10px;
    border-top: 1px solid rgba(255,255,255,0.07);
    background: rgba(0,0,0,0.2);
    display: flex;
    flex-direction: column;
    gap: 5px;
}

/* Theme Toggle in sidebar */
.sidebar-theme-btn {
    display: flex;
    align-items: center;
    gap: 10px;
    width: 100%;
    padding: 10px 14px;
    background: rgba(255,255,255,0.06);
    color: #9aa5b9;
    border: 1px solid rgba(255,255,255,0.08);
    border-radius: 8px;
    cursor: pointer;
    font-size: 0.875rem;
    font-weight: 500;
    transition: all 0.2s ease;
    text-align: left;
}

.sidebar-theme-btn:hover {
    background: rgba(255,255,255,0.12);
    color: #fff;
}

.sidebar-theme-label {
    flex: 1;
}

/* Back to Site */
.sidebar-back-btn {
    display: flex;
    align-items: center;
    gap: 10px;
    padding: 10px 14px;
    color: #74b9ff;
    background: rgba(116, 185, 255, 0.1);
    text-decoration: none;
    border-radius: 8px;
    font-size: 0.875rem;
    font-weight: 500;
    transition: all 0.2s ease;
}

.sidebar-back-btn:hover {
    background: rgba(116, 185, 255, 0.2);
    color: #a8d4ff;
    text-decoration: none;
}

/* Logout */
.sidebar-logout-btn {
    display: flex;
    align-items: center;
    gap: 10px;
    padding: 10px 14px;
    color: #ff7675;
    background: rgba(255, 118, 117, 0.1);
    text-decoration: none;
    border-radius: 8px;
    font-size: 0.875rem;
    font-weight: 500;
    transition: all 0.2s ease;
}

.sidebar-logout-btn:hover {
    background: rgba(255, 118, 117, 0.2);
    color: #fab1a0;
    text-decoration: none;
}

.sidebar-footer .icon {
    font-size: 16px;
    width: 20px;
    text-align: center;
    flex-shrink: 0;
}

/* Admin Content Area */
.admin-content {
    margin-left: 260px;
    padding: 24px;
    min-height: 100vh;
    background: var(--bg-color, #f0f2f5);
    transition: background 0.3s ease, margin 0.3s ease;
}

/* Responsive Sidebar */
@media (max-width: 900px) {
    .admin-sidebar {
        width: 66px;
    }
    
    .sidebar-header h2,
    .sidebar-nav a span:not(.icon),
    .sidebar-back-btn span:not(.icon),
    .sidebar-logout-btn span:not(.icon),
    .sidebar-theme-label {
        display: none;
    }
    
    .sidebar-nav a,
    .sidebar-back-btn,
    .sidebar-logout-btn,
    .sidebar-theme-btn {
        justify-content: center;
        padding: 12px 10px;
        gap: 0;
    }
    
    .admin-content {
        margin-left: 66px;
    }
}
</style>

<script>
// Sync sidebar theme button label with current theme
(function() {
    function syncSidebarThemeBtn() {
        var btn = document.getElementById('sidebarThemeToggle');
        if (!btn) return;
        var isDark = document.body.classList.contains('dark');
        var icon = btn.querySelector('.theme-icon');
        var label = btn.querySelector('.sidebar-theme-label');
        if (icon) icon.textContent = isDark ? '☀️' : '🌙';
        if (label) label.textContent = isDark ? 'Light Mode' : 'Dark Mode';
    }

    // Run on load after a tick so initTheme() has already applied class
    setTimeout(syncSidebarThemeBtn, 50);

    document.addEventListener('DOMContentLoaded', function() {
        var btn = document.getElementById('sidebarThemeToggle');
        if (btn) {
            btn.addEventListener('click', function() {
                // Delegate to the global toggleTheme from main.js
                if (typeof toggleTheme === 'function') {
                    toggleTheme();
                } else {
                    document.body.classList.toggle('dark');
                    var theme = document.body.classList.contains('dark') ? 'dark' : 'light';
                    localStorage.setItem('theme', theme);
                }
                syncSidebarThemeBtn();
            });
        }
    });
})();
</script>
