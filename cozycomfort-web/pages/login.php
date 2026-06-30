<?php
// Get API client
$apiClient = getApiClient();

// Handle login form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = postParam('email');
    $password = postParam('password');
    $csrfToken = postParam('csrf_token');
    
    if (!verifyCsrfToken($csrfToken)) {
        setNotification('Security token mismatch. Please try again.', 'error');
    } elseif (empty($email) || empty($password)) {
        setNotification('Please fill in all fields.', 'error');
    } else {
        try {
            $response = $apiClient->login($email, $password);
            
            // Debug: Log the full response
            error_log('LOGIN DEBUG - API Response: ' . json_encode($response));
            error_log('LOGIN DEBUG - Checking success: ' . var_export($response['success'] ?? 'NOT SET', true));
            error_log('LOGIN DEBUG - Response data success: ' . var_export($response['data']['success'] ?? 'NOT SET', true));
            
            // Check nested success flag
            if ($response && isset($response['data']['success']) && $response['data']['success']) {
                // Store user session - API returns nested structure: response.data.data
                $userData = $response['data']['data'] ?? $response['data'] ?? [];
                
                // Debug: Log userData before saving to session
                error_log('LOGIN DEBUG - UserData extracted: ' . json_encode($userData));
                
                $_SESSION['user_id'] = $userData['userId'] ?? $userData['id'] ?? null;
                $_SESSION['email'] = $userData['email'] ?? $email;
                $_SESSION['first_name'] = $userData['firstName'] ?? '';
                $_SESSION['last_name'] = $userData['lastName'] ?? '';
                $_SESSION['role'] = $userData['role'] ?? 'Customer';
                $_SESSION['token'] = $userData['token'] ?? null;
                $_SESSION['login_time'] = time();
                
                // Debug log
                error_log('LOGIN DEBUG - Session data saved: ' . json_encode([
                    'user_id' => $_SESSION['user_id'],
                    'email' => $_SESSION['email'],
                    'role' => $_SESSION['role']
                ]));
                
                // Force session write
                session_write_close();
                session_start();
                
                setNotification('Login successful!', 'success');
                
                // Redirect based on role
                if ($_SESSION['role'] === 'Admin') {
                    error_log('LOGIN DEBUG - Redirecting to admin dashboard');
                    header('Location: ?page=admin-dashboard');
                    exit();
                } else {
                    error_log('LOGIN DEBUG - Redirecting to home');
                    header('Location: ?page=home');
                    exit();
                }
            } else {
                $errorMsg = isset($response['message']) ? $response['message'] : 'Invalid email or password.';
                setNotification($errorMsg, 'error');
            }
        } catch (Exception $e) {
            setNotification('Login failed: ' . $e->getMessage(), 'error');
        }
    }
}
?>

<div class="login-form">
    <h2>Login to Your Account</h2>
    
    <form method="POST" class="form">
        <input type="hidden" name="csrf_token" value="<?php echo generateCsrfToken(); ?>">
        
        <div class="form-group">
            <label for="email">Email Address</label>
            <input type="email" id="email" name="email" required 
                   value="<?php echo htmlspecialchars(postParam('email', '')); ?>"
                   placeholder="Enter your email">
        </div>
        
        <div class="form-group">
            <label for="password">Password</label>
            <input type="password" id="password" name="password" required 
                   placeholder="Enter your password">
        </div>
        
        <div class="form-group">
            <label class="checkbox-label">
                <input type="checkbox" name="remember_me"> Remember me
            </label>
        </div>
        
        <button type="submit" class="btn btn-primary btn-block">Login</button>
    </form>
    
    <div class="form-footer">
        <p>Don't have an account? <a href="?page=register">Sign up here</a></p>
        <p><a href="?page=forgot-password">Forgot your password?</a></p>
    </div>
</div>
