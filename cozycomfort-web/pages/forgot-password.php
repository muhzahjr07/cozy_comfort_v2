<?php
// Get API client
$apiClient = getApiClient();

$passwordReset = false;

// Handle password reset form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = postParam('email');
    $newPassword = postParam('new_password');
    $confirmPassword = postParam('confirm_password');
    $csrfToken = postParam('csrf_token');
    
    if (!verifyCsrfToken($csrfToken)) {
        setNotification('Security token mismatch. Please try again.', 'error');
    } elseif (empty($email) || empty($newPassword) || empty($confirmPassword)) {
        setNotification('Please fill in all fields.', 'error');
    } elseif (!isValidEmail($email)) {
        setNotification('Please enter a valid email address.', 'error');
    } elseif ($newPassword !== $confirmPassword) {
        setNotification('Passwords do not match.', 'error');
    } elseif (strlen($newPassword) < 6) {
        setNotification('Password must be at least 6 characters long.', 'error');
    } else {
        try {
            // Call API to reset password
            // Note: This endpoint may need to be implemented in your API
            $response = $apiClient->post('api/Auth/reset-password', [
                'email' => $email,
                'newPassword' => $newPassword,
                'confirmPassword' => $confirmPassword
            ]);
            
            if ($response && isset($response['success']) && $response['success']) {
                $passwordReset = true;
                setNotification('Password reset successful! You can now login with your new password.', 'success');
            } else {
                $errorMsg = isset($response['message']) ? $response['message'] : 'Password reset failed. Please check your email address.';
                setNotification($errorMsg, 'error');
            }
        } catch (Exception $e) {
            // If API endpoint doesn't exist yet, show helpful message
            setNotification('Password reset functionality is not yet implemented in the API.', 'warning');
        }
    }
}
?>

<div class="forgot-password-form">
    <?php if ($passwordReset): ?>
        <div class="success-message">
            <div class="icon">✅</div>
            <h2>Password Reset Successfully!</h2>
            <p>Your password has been reset successfully.</p>
            <p>You can now login with your new password.</p>
            <div class="form-footer">
                <a href="?page=login" class="btn btn-primary btn-block">Go to Login</a>
            </div>
        </div>
    <?php else: ?>
        <h2>Reset Your Password</h2>
        <p class="form-description">Enter your email address and choose a new password.</p>
        
        <form method="POST" class="form">
            <input type="hidden" name="csrf_token" value="<?php echo generateCsrfToken(); ?>">
            
            <div class="form-group">
                <label for="email">Email Address *</label>
                <input type="email" id="email" name="email" required 
                       value="<?php echo htmlspecialchars(postParam('email', '')); ?>"
                       placeholder="Enter your email address">
            </div>
            
            <div class="form-group">
                <label for="new_password">New Password *</label>
                <input type="password" id="new_password" name="new_password" required 
                       placeholder="Enter new password" minlength="6">
                <small class="form-help">Must be at least 6 characters</small>
            </div>
            
            <div class="form-group">
                <label for="confirm_password">Confirm New Password *</label>
                <input type="password" id="confirm_password" name="confirm_password" required 
                       placeholder="Confirm new password" minlength="6">
            </div>
            
            <button type="submit" class="btn btn-primary btn-block">Reset Password</button>
        </form>
        
        <div class="form-footer">
            <p>Remember your password? <a href="?page=login">Sign in here</a></p>
            <p>Don't have an account? <a href="?page=register">Sign up here</a></p>
        </div>
    <?php endif; ?>
</div>

<style>
.forgot-password-form {
    max-width: 450px;
    margin: 50px auto;
    padding: 40px;
    background: white;
    border-radius: 12px;
    box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
}

.forgot-password-form h2 {
    margin-bottom: 15px;
    color: #333;
    font-size: 28px;
    text-align: center;
}

.forgot-password-form .form-description {
    text-align: center;
    color: #666;
    margin-bottom: 30px;
    line-height: 1.6;
}

.forgot-password-form .form {
    margin-top: 30px;
},
.forgot-password-form input[type="password"] {
    width: 100%;
    padding: 12px 15px;
    border: 1px solid #ddd;
    border-radius: 6px;
    font-size: 15px;
    transition: border-color 0.3s;
}

.forgot-password-form input[type="email"]:focus,
.forgot-password-form input[type="password"]:focus {
    outline: none;
    border-color: #007bff;
}

.forgot-password-form .form-help {
    display: block;
    margin-top: 5px;
    font-size: 13px;
    color: #666

.forgot-password-form input[type="email"] {
    width: 100%;
    padding: 12px 15px;
    border: 1px solid #ddd;
    border-radius: 6px;
    font-size: 15px;
    transition: border-color 0.3s;
}

.forgot-password-form input[type="email"]:focus {
    outline: none;
    border-color: #007bff;
}

.forgot-password-form .form-footer {
    margin-top: 25px;
    text-align: center;
    padding-top: 20px;
    border-top: 1px solid #eee;
}

.forgot-password-form .form-footer p {
    margin: 10px 0;
    color: #666;
}

.forgot-password-form .form-footer a {
    color: #007bff;
    text-decoration: none;
    font-weight: 500;
}

.forgot-password-form .form-footer a:hover {
    text-decoration: underline;
}

.success-message {
    text-align: center;
    padding: 20px;
}

.success-message .icon {
    font-size: 64px;
    margin-bottom: 20px;
}

.success-message h2 {
    color: #28a745;
    margin-bottom: 20px;
}

.success-message p {
    color: #666;
    line-height: 1.8;
    margin-bottom: 15px;
}

.success-message .form-footer {
    margin-top: 30px;
    padding-top: 20px;
    border-top: 1px solid #eee;
}
</style>

