<?php
// Check if user is logged in
if (!isLoggedIn()) {
    setNotification('Please login to view your profile.', 'error');
    redirect('?page=login');
}

$apiClient = getApiClient();
$userId = $_SESSION['user_id'];

// Handle profile update
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = postParam('action');
    
    if ($action === 'update_profile') {
        $firstName = postParam('firstName');
        $lastName = postParam('lastName');
        $phone = postParam('phone');
        $address = postParam('address');
        $city = postParam('city');
        $postalCode = postParam('postalCode');
        
        // Update session data
        $_SESSION['firstName'] = $firstName;
        $_SESSION['lastName'] = $lastName;
        
        setNotification('Profile updated successfully!', 'success');
        redirect('?page=profile');
    } elseif ($action === 'change_password') {
        $currentPassword = postParam('currentPassword');
        $newPassword = postParam('newPassword');
        $confirmPassword = postParam('confirmPassword');
        
        if ($newPassword !== $confirmPassword) {
            setNotification('New passwords do not match.', 'error');
        } elseif (strlen($newPassword) < 6) {
            setNotification('Password must be at least 6 characters.', 'error');
        } else {
            setNotification('Password changed successfully!', 'success');
            redirect('?page=profile');
        }
    }
}

// Get user data from session
$user = [
    'id' => $_SESSION['user_id'] ?? null,
    'email' => $_SESSION['email'] ?? '',
    'firstName' => $_SESSION['firstName'] ?? '',
    'lastName' => $_SESSION['lastName'] ?? '',
    'phone' => $_SESSION['phone'] ?? '',
    'address' => $_SESSION['address'] ?? '',
    'city' => $_SESSION['city'] ?? '',
    'postalCode' => $_SESSION['postalCode'] ?? '',
    'role' => $_SESSION['role'] ?? 'Customer'
];
?>

<div class="profile-page">
    <div class="container">
        <h1>My Profile</h1>
        
        <div class="profile-content">
            <!-- Profile Sidebar -->
            <aside class="profile-sidebar">
                <div class="profile-card">
                    <div class="profile-avatar">
                        <?php echo strtoupper(substr($user['firstName'], 0, 1) . substr($user['lastName'], 0, 1)); ?>
                    </div>
                    <h3><?php echo htmlspecialchars($user['firstName'] . ' ' . $user['lastName']); ?></h3>
                    <p><?php echo htmlspecialchars($user['email']); ?></p>
                    <span class="badge"><?php echo htmlspecialchars($user['role']); ?></span>
                </div>
                
                <nav class="profile-nav">
                    <a href="#personal-info" class="profile-nav-item active">
                        Personal Information
                    </a>
                    <a href="#security" class="profile-nav-item">
                        Security
                    </a>
                    <a href="?page=orders" class="profile-nav-item">
                        My Orders
                    </a>
                    <a href="?page=wishlist" class="profile-nav-item">
                        Wishlist
                    </a>
                    <a href="?page=logout" class="profile-nav-item">
                        Logout
                    </a>
                </nav>
            </aside>
            
            <!-- Profile Main Content -->
            <main class="profile-main">
                <!-- Personal Information Section -->
                <section id="personal-info" class="profile-section">
                    <h2>Personal Information</h2>
                    
                    <form method="POST" class="profile-form">
                        <input type="hidden" name="action" value="update_profile">
                        
                        <div class="form-row">
                            <div class="form-group">
                                <label for="firstName">First Name</label>
                                <input type="text" id="firstName" name="firstName" 
                                       value="<?php echo htmlspecialchars($user['firstName']); ?>" required>
                            </div>
                            
                            <div class="form-group">
                                <label for="lastName">Last Name</label>
                                <input type="text" id="lastName" name="lastName" 
                                       value="<?php echo htmlspecialchars($user['lastName']); ?>" required>
                            </div>
                        </div>
                        
                        <div class="form-group">
                            <label for="email">Email Address</label>
                            <input type="email" id="email" name="email" 
                                   value="<?php echo htmlspecialchars($user['email']); ?>" disabled>
                            <small>Email cannot be changed</small>
                        </div>
                        
                        <div class="form-group">
                            <label for="phone">Phone Number</label>
                            <input type="tel" id="phone" name="phone" 
                                   value="<?php echo htmlspecialchars($user['phone']); ?>">
                        </div>
                        
                        <div class="form-group">
                            <label for="address">Address</label>
                            <input type="text" id="address" name="address" 
                                   value="<?php echo htmlspecialchars($user['address']); ?>">
                        </div>
                        
                        <div class="form-row">
                            <div class="form-group">
                                <label for="city">City</label>
                                <input type="text" id="city" name="city" 
                                       value="<?php echo htmlspecialchars($user['city']); ?>">
                            </div>
                            
                            <div class="form-group">
                                <label for="postalCode">Postal Code</label>
                                <input type="text" id="postalCode" name="postalCode" 
                                       value="<?php echo htmlspecialchars($user['postalCode']); ?>">
                            </div>
                        </div>
                        
                        <button type="submit" class="btn btn-primary">Update Profile</button>
                    </form>
                </section>
                
                <!-- Security Section -->
                <section id="security" class="profile-section">
                    <h2>Change Password</h2>
                    
                    <form method="POST" class="profile-form">
                        <input type="hidden" name="action" value="change_password">
                        
                        <div class="form-group">
                            <label for="currentPassword">Current Password</label>
                            <input type="password" id="currentPassword" name="currentPassword" required>
                        </div>
                        
                        <div class="form-group">
                            <label for="newPassword">New Password</label>
                            <input type="password" id="newPassword" name="newPassword" 
                                   minlength="6" required>
                        </div>
                        
                        <div class="form-group">
                            <label for="confirmPassword">Confirm New Password</label>
                            <input type="password" id="confirmPassword" name="confirmPassword" 
                                   minlength="6" required>
                        </div>
                        
                        <button type="submit" class="btn btn-primary">Change Password</button>
                    </form>
                </section>
            </main>
        </div>
    </div>
</div>

<style>
.profile-page {
    padding: 2rem 0;
    min-height: 60vh;
}

.profile-content {
    display: grid;
    grid-template-columns: 300px 1fr;
    gap: 2rem;
    margin-top: 2rem;
}

.profile-sidebar {
    display: flex;
    flex-direction: column;
    gap: 1.5rem;
}

.profile-card {
    background: white;
    border-radius: 8px;
    padding: 2rem;
    text-align: center;
    box-shadow: 0 2px 4px rgba(0,0,0,0.1);
}

.profile-avatar {
    width: 100px;
    height: 100px;
    border-radius: 50%;
    background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
    color: white;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 2rem;
    font-weight: bold;
    margin: 0 auto 1rem;
}

.profile-card h3 {
    margin: 0.5rem 0 0.25rem;
    color: #333;
}

.profile-card p {
    color: #666;
    margin: 0;
    font-size: 0.9rem;
}

.profile-card .badge {
    display: inline-block;
    margin-top: 0.75rem;
    padding: 0.25rem 0.75rem;
    background: #e3f2fd;
    color: #1976d2;
    border-radius: 12px;
    font-size: 0.85rem;
}

.profile-nav {
    background: white;
    border-radius: 8px;
    padding: 1rem 0;
    box-shadow: 0 2px 4px rgba(0,0,0,0.1);
}

.profile-nav-item {
    display: block;
    padding: 0.75rem 1.5rem;
    color: #666;
    text-decoration: none;
    transition: all 0.3s;
}

.profile-nav-item:hover,
.profile-nav-item.active {
    background: #f5f5f5;
    color: #007bff;
}

.profile-main {
    display: flex;
    flex-direction: column;
    gap: 2rem;
}

.profile-section {
    background: white;
    border-radius: 8px;
    padding: 2rem;
    box-shadow: 0 2px 4px rgba(0,0,0,0.1);
}

.profile-section h2 {
    margin-top: 0;
    margin-bottom: 1.5rem;
    color: #333;
    font-size: 1.5rem;
}

.profile-form {
    max-width: 600px;
}

.form-row {
    display: grid;
    grid-template-columns: 1fr 1fr;
    gap: 1rem;
}

.form-group {
    margin-bottom: 1.5rem;
}

.form-group label {
    display: block;
    margin-bottom: 0.5rem;
    color: #333;
    font-weight: 500;
}

.form-group input {
    width: 100%;
    padding: 0.75rem;
    border: 1px solid #ddd;
    border-radius: 4px;
    font-size: 1rem;
}

.form-group input:disabled {
    background: #f5f5f5;
    cursor: not-allowed;
}

.form-group small {
    display: block;
    margin-top: 0.25rem;
    color: #666;
    font-size: 0.85rem;
}

@media (max-width: 768px) {
    .profile-content {
        grid-template-columns: 1fr;
    }
    
    .form-row {
        grid-template-columns: 1fr;
    }
}
</style>

