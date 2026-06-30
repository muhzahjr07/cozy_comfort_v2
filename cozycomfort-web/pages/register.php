<?php
// Get API client
$apiClient = getApiClient();

require_once 'includes/validators.php';

// Handle registration form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $firstName = postParam('firstName');
    $lastName = postParam('lastName');
    $email = postParam('email');
    $password = postParam('password');
    $confirmPassword = postParam('confirmPassword');
    $phone = postParam('phone');
    $csrfToken = postParam('csrf_token');
    
    $validator = new Validator();
    
    if (!verifyCsrfToken($csrfToken)) {
        setNotification('Security token mismatch. Please try again.', 'error');
    } elseif (empty($firstName) || empty($lastName) || empty($email) || empty($password)) {
        setNotification('Please fill in all required fields.', 'error');
    } elseif ($password !== $confirmPassword) {
        setNotification('Passwords do not match.', 'error');
    } elseif (!$validator->validateEmail($email)) {
        setNotification('Please enter a valid email address.', 'error');
    } elseif (!$validator->validatePassword($password)) {
        setNotification(implode(' ', $validator->getErrors()), 'error');
    } elseif ($phone && !$validator->validatePhone($phone)) {
        setNotification('Please enter a valid phone number.', 'error');
    } else {
        // Attempt registration via API
        $registrationData = [
            'firstName' => $firstName,
            'lastName' => $lastName,
            'email' => $email,
            'password' => $password,
            'confirmPassword' => $confirmPassword,
            'phoneNumber' => $phone,
            'role' => postParam('role', 'Customer') // Get role from form
        ];
        
        try {
            $response = $apiClient->register($registrationData);
            
            if ($response && isset($response['success']) && $response['success']) {
                setNotification('Registration successful! Please log in.', 'success');
                redirect('?page=login');
            } else {
                $errorMsg = isset($response['message']) ? $response['message'] : 'Registration failed. Please try again.';
                setNotification($errorMsg, 'error');
            }
        } catch (Exception $e) {
            setNotification('Registration failed: ' . $e->getMessage(), 'error');
        }
    }
}
?>

<div class="register-form">
    <h2>Create Your Account</h2>
    
    <form method="POST" class="form">
        <input type="hidden" name="csrf_token" value="<?php echo generateCsrfToken(); ?>">
        
        <div class="form-row">
            <div class="form-group">
                <label for="firstName">First Name *</label>
                <input type="text" id="firstName" name="firstName" required 
                       value="<?php echo htmlspecialchars(postParam('firstName', '')); ?>"
                       placeholder="Enter your first name">
            </div>
            
            <div class="form-group">
                <label for="lastName">Last Name *</label>
                <input type="text" id="lastName" name="lastName" required 
                       value="<?php echo htmlspecialchars(postParam('lastName', '')); ?>"
                       placeholder="Enter your last name">
            </div>
        </div>
        
        <div class="form-group">
            <label for="email">Email Address *</label>
            <input type="email" id="email" name="email" required 
                   value="<?php echo htmlspecialchars(postParam('email', '')); ?>"
                   placeholder="Enter your email">
        </div>
        
        <div class="form-group">
            <label for="phone">Phone Number</label>
            <input type="tel" id="phone" name="phone" 
                   value="<?php echo htmlspecialchars(postParam('phone', '')); ?>"
                   placeholder="Enter your phone number">
        </div>
        
        <div class="form-group">
            <label for="password">Password *</label>
            <input type="password" id="password" name="password" required 
                   placeholder="Enter your password">
            <small class="form-help">Must be at least 8 characters with uppercase letter and number</small>
        </div>
        
        <div class="form-group">
            <label for="confirmPassword">Confirm Password *</label>
            <input type="password" id="confirmPassword" name="confirmPassword" required 
                   placeholder="Confirm your password">
        </div>
        
        <div class="form-group">
            <label for="role">Account Type</label>
            <select id="role" name="role">
                <option value="Customer">Customer</option>
                <option value="Admin">Administrator</option>
            </select>
        </div>
        
        <div class="form-group">
            <label class="checkbox-label">
                <input type="checkbox" name="terms" required> 
                I agree to the <a href="?page=terms" target="_blank">Terms of Service</a> and 
                <a href="?page=privacy" target="_blank">Privacy Policy</a>
            </label>
        </div>
        
        <button type="submit" class="btn btn-primary btn-block">Create Account</button>
    </form>
    
    <div class="form-footer">
        <p>Already have an account? <a href="?page=login">Sign in here</a></p>
    </div>
</div>
