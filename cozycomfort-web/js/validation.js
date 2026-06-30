/**
 * Form validation utilities
 */

class FormValidator {
    constructor(form) {
        this.form = form;
        this.errors = [];
    }
    
    validateRequired(fieldName, customMessage = null) {
        const field = this.form.querySelector(`[name="${fieldName}"]`);
        const value = field ? field.value.trim() : '';
        
        if (!value) {
            const message = customMessage || `${this.getFieldLabel(fieldName)} is required`;
            this.addError(fieldName, message);
            this.highlightField(field);
            return false;
        }
        
        this.clearFieldError(field);
        return true;
    }
    
    validateEmail(fieldName) {
        const field = this.form.querySelector(`[name="${fieldName}"]`);
        const value = field ? field.value.trim() : '';
        
        if (value && !this.isValidEmail(value)) {
            this.addError(fieldName, 'Please enter a valid email address');
            this.highlightField(field);
            return false;
        }
        
        this.clearFieldError(field);
        return true;
    }
    
    validatePassword(fieldName, minLength = 8) {
        const field = this.form.querySelector(`[name="${fieldName}"]`);
        const value = field ? field.value : '';
        
        if (!value) {
            this.addError(fieldName, 'Password is required');
            this.highlightField(field);
            return false;
        }
        
        if (value.length < minLength) {
            this.addError(fieldName, `Password must be at least ${minLength} characters long`);
            this.highlightField(field);
            return false;
        }
        
        if (!/[A-Z]/.test(value)) {
            this.addError(fieldName, 'Password must contain at least one uppercase letter');
            this.highlightField(field);
            return false;
        }
        
        if (!/[0-9]/.test(value)) {
            this.addError(fieldName, 'Password must contain at least one number');
            this.highlightField(field);
            return false;
        }
        
        this.clearFieldError(field);
        return true;
    }
    
    validatePasswordMatch(password1Field, password2Field) {
        const field1 = this.form.querySelector(`[name="${password1Field}"]`);
        const field2 = this.form.querySelector(`[name="${password2Field}"]`);
        
        if (field1 && field2 && field1.value !== field2.value) {
            this.addError(password2Field, 'Passwords do not match');
            this.highlightField(field2);
            return false;
        }
        
        this.clearFieldError(field2);
        return true;
    }
    
    validatePhone(fieldName) {
        const field = this.form.querySelector(`[name="${fieldName}"]`);
        const value = field ? field.value.trim() : '';
        
        if (value && !this.isValidPhone(value)) {
            this.addError(fieldName, 'Please enter a valid phone number');
            this.highlightField(field);
            return false;
        }
        
        this.clearFieldError(field);
        return true;
    }
    
    validateMinLength(fieldName, minLength) {
        const field = this.form.querySelector(`[name="${fieldName}"]`);
        const value = field ? field.value.trim() : '';
        
        if (value && value.length < minLength) {
            this.addError(fieldName, `Must be at least ${minLength} characters long`);
            this.highlightField(field);
            return false;
        }
        
        this.clearFieldError(field);
        return true;
    }
    
    validateMaxLength(fieldName, maxLength) {
        const field = this.form.querySelector(`[name="${fieldName}"]`);
        const value = field ? field.value.trim() : '';
        
        if (value && value.length > maxLength) {
            this.addError(fieldName, `Must be no more than ${maxLength} characters long`);
            this.highlightField(field);
            return false;
        }
        
        this.clearFieldError(field);
        return true;
    }
    
    isValidEmail(email) {
        const emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
        return emailRegex.test(email);
    }
    
    isValidPhone(phone) {
        const phoneRegex = /^[\+]?[1-9][\d]{0,15}$/;
        return phoneRegex.test(phone.replace(/[\s\-\(\)]/g, ''));
    }
    
    addError(fieldName, message) {
        this.errors.push({ field: fieldName, message: message });
    }
    
    highlightField(field) {
        if (field) {
            field.classList.add('error');
            field.addEventListener('input', () => {
                this.clearFieldError(field);
            }, { once: true });
        }
    }
    
    clearFieldError(field) {
        if (field) {
            field.classList.remove('error');
        }
    }
    
    getFieldLabel(fieldName) {
        const field = this.form.querySelector(`[name="${fieldName}"]`);
        const label = field ? this.form.querySelector(`label[for="${field.id}"]`) : null;
        
        if (label) {
            return label.textContent.replace('*', '').trim();
        }
        
        // Convert camelCase to readable format
        return fieldName.replace(/([A-Z])/g, ' $1').toLowerCase();
    }
    
    clearAllErrors() {
        this.errors = [];
        this.form.querySelectorAll('.error').forEach(field => {
            field.classList.remove('error');
        });
    }
    
    showErrors() {
        if (this.errors.length > 0) {
            const errorMessage = this.errors.map(error => error.message).join('<br>');
            showNotification(errorMessage, 'error');
        }
    }
    
    isValid() {
        return this.errors.length === 0;
    }
}

// Real-time validation setup
function setupRealtimeValidation() {
    const forms = document.querySelectorAll('form[data-validate]');
    
    forms.forEach(form => {
        const validator = new FormValidator(form);
        
        // Email fields
        const emailFields = form.querySelectorAll('input[type="email"]');
        emailFields.forEach(field => {
            field.addEventListener('blur', () => {
                validator.clearAllErrors();
                validator.validateRequired(field.name);
                validator.validateEmail(field.name);
            });
        });
        
        // Password fields
        const passwordFields = form.querySelectorAll('input[type="password"]');
        passwordFields.forEach(field => {
            field.addEventListener('blur', () => {
                validator.clearAllErrors();
                validator.validateRequired(field.name);
                if (field.name === 'password') {
                    validator.validatePassword(field.name);
                }
                if (field.name === 'confirmPassword') {
                    validator.validatePasswordMatch('password', 'confirmPassword');
                }
            });
        });
        
        // Phone fields
        const phoneFields = form.querySelectorAll('input[type="tel"]');
        phoneFields.forEach(field => {
            field.addEventListener('blur', () => {
                validator.clearAllErrors();
                if (field.value.trim()) {
                    validator.validatePhone(field.name);
                }
            });
        });
        
        // Required fields
        const requiredFields = form.querySelectorAll('[required]');
        requiredFields.forEach(field => {
            field.addEventListener('blur', () => {
                validator.clearAllErrors();
                validator.validateRequired(field.name);
            });
        });
    });
}

// Form submission validation
function validateLoginForm(form) {
    const validator = new FormValidator(form);
    
    validator.validateRequired('email');
    validator.validateEmail('email');
    validator.validateRequired('password');
    
    if (!validator.isValid()) {
        validator.showErrors();
    }
    
    return validator.isValid();
}

function validateRegistrationForm(form) {
    const validator = new FormValidator(form);
    
    validator.validateRequired('firstName');
    validator.validateRequired('lastName');
    validator.validateRequired('email');
    validator.validateEmail('email');
    validator.validateRequired('password');
    validator.validatePassword('password');
    validator.validatePasswordMatch('password', 'confirmPassword');
    
    const phoneField = form.querySelector('[name="phone"]');
    if (phoneField && phoneField.value.trim()) {
        validator.validatePhone('phone');
    }
    
    if (!validator.isValid()) {
        validator.showErrors();
    }
    
    return validator.isValid();
}

function validateCheckoutForm(form) {
    const validator = new FormValidator(form);
    
    validator.validateRequired('fullName');
    validator.validateRequired('email');
    validator.validateEmail('email');
    validator.validateRequired('phone');
    validator.validatePhone('phone');
    validator.validateRequired('address');
    validator.validateRequired('city');
    validator.validateRequired('state');
    validator.validateRequired('zip');
    
    if (!validator.isValid()) {
        validator.showErrors();
    }
    
    return validator.isValid();
}

// Initialize on DOM ready
document.addEventListener('DOMContentLoaded', function() {
    setupRealtimeValidation();
    
    // Add form validation to specific forms
    const loginForm = document.querySelector('#loginForm, form[action*="login"]');
    if (loginForm) {
        loginForm.addEventListener('submit', function(e) {
            if (!validateLoginForm(this)) {
                e.preventDefault();
            }
        });
    }
    
    const registerForm = document.querySelector('#registerForm, form[action*="register"]');
    if (registerForm) {
        registerForm.addEventListener('submit', function(e) {
            if (!validateRegistrationForm(this)) {
                e.preventDefault();
            }
        });
    }
    
    const checkoutForm = document.querySelector('#checkoutForm');
    if (checkoutForm) {
        checkoutForm.addEventListener('submit', function(e) {
            if (!validateCheckoutForm(this)) {
                e.preventDefault();
            }
        });
    }
});
