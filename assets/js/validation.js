/**
 * CARLINK - Form Validation Script
 * Handles client-side validation for all forms
 */

document.addEventListener('DOMContentLoaded', function() {
    // Enable Bootstrap form validation
    enableBootstrapValidation();
    
    // Setup specific form validation handlers
    setupLoginFormValidation();
    setupRegisterFormValidation();
    setupPaymentFormValidation();
});

/**
 * Enable Bootstrap form validation on all forms with needs-validation class
 */
function enableBootstrapValidation() {
    // Fetch all forms we want to apply custom Bootstrap validation styles to
    const forms = document.querySelectorAll('.needs-validation');
    
    // Loop over them and prevent submission
    Array.from(forms).forEach(form => {
        form.addEventListener('submit', event => {
            if (!form.checkValidity()) {
                event.preventDefault();
                event.stopPropagation();
            }
            
            form.classList.add('was-validated');
        }, false);
    });
}

/**
 * Set up login form validation
 */
function setupLoginFormValidation() {
    const loginForm = document.getElementById('loginForm');
    
    if (loginForm) {
        const emailInput = loginForm.querySelector('#email');
        const passwordInput = loginForm.querySelector('#password');
        
        // Validate email format
        emailInput.addEventListener('input', function() {
            validateEmail(this);
        });
        
        // Ensure password is not empty
        passwordInput.addEventListener('input', function() {
            if (this.value.trim() === '') {
                this.setCustomValidity('Password is required');
            } else {
                this.setCustomValidity('');
            }
        });
    }
}

/**
 * Set up registration form validation
 */
function setupRegisterFormValidation() {
    const registerForm = document.getElementById('registerForm');
    
    if (registerForm) {
        const nameInput = registerForm.querySelector('#name');
        const emailInput = registerForm.querySelector('#email');
        const passwordInput = registerForm.querySelector('#password');
        const confirmPasswordInput = registerForm.querySelector('#confirm_password');
        const termsCheckbox = registerForm.querySelector('#terms');
        
        // Validate name (letters, spaces, and hyphens only)
        if (nameInput) {
            nameInput.addEventListener('input', function() {
                if (this.value.trim() === '') {
                    this.setCustomValidity('Name is required');
                } else if (!/^[A-Za-zÀ-ÖØ-öø-ÿ\s\-']+$/.test(this.value)) {
                    this.setCustomValidity('Name can only contain letters, spaces, hyphens, and apostrophes');
                } else {
                    this.setCustomValidity('');
                }
            });
        }
        
        // Validate email format
        if (emailInput) {
            emailInput.addEventListener('input', function() {
                validateEmail(this);
            });
        }
        
        // Validate password strength
        if (passwordInput) {
            passwordInput.addEventListener('input', function() {
                validatePassword(this);
            });
        }
        
        // Validate password confirmation
        if (confirmPasswordInput && passwordInput) {
            confirmPasswordInput.addEventListener('input', function() {
                if (this.value !== passwordInput.value) {
                    this.setCustomValidity('Passwords do not match');
                } else {
                    this.setCustomValidity('');
                }
            });
            
            // Also check confirmation when password changes
            passwordInput.addEventListener('input', function() {
                if (confirmPasswordInput.value !== '' && confirmPasswordInput.value !== this.value) {
                    confirmPasswordInput.setCustomValidity('Passwords do not match');
                } else {
                    confirmPasswordInput.setCustomValidity('');
                }
            });
        }
        
        // Check terms acceptance
        if (termsCheckbox) {
            termsCheckbox.addEventListener('change', function() {
                if (!this.checked) {
                    this.setCustomValidity('You must accept the terms and conditions');
                } else {
                    this.setCustomValidity('');
                }
            });
        }
    }
}

/**
 * Set up payment form validation
 */
function setupPaymentFormValidation() {
    const paymentForm = document.getElementById('payment-form');
    
    if (paymentForm) {
        const cardNumberInput = paymentForm.querySelector('#card_number');
        const cardExpiryInput = paymentForm.querySelector('#card_expiry');
        const cardCvvInput = paymentForm.querySelector('#card_cvv');
        
        // Format and validate card number
        if (cardNumberInput) {
            cardNumberInput.addEventListener('input', function(e) {
                // Remove non-digit characters
                let value = this.value.replace(/\D/g, '');
                
                // Add spaces for better readability
                if (value.length > 0) {
                    value = value.match(/.{1,4}/g).join(' ');
                }
                
                // Update the input value
                this.value = value;
                
                // Validate card number (basic Luhn check)
                if (value.length < 13) {
                    this.setCustomValidity('Card number is too short');
                } else if (value.length > 19) {
                    this.setCustomValidity('Card number is too long');
                } else {
                    this.setCustomValidity('');
                }
            });
        }
        
        // Format and validate expiry date
        if (cardExpiryInput) {
            cardExpiryInput.addEventListener('input', function(e) {
                // Format as MM/YY
                let value = this.value.replace(/\D/g, '');
                
                if (value.length > 2) {
                    value = value.substring(0, 2) + '/' + value.substring(2, 4);
                }
                
                this.value = value;
                
                // Validate expiry date
                if (this.value.length === 5) {
                    const [month, year] = this.value.split('/');
                    const currentDate = new Date();
                    const currentYear = currentDate.getFullYear() % 100; // Get last 2 digits
                    const currentMonth = currentDate.getMonth() + 1; // Months are 0-indexed
                    
                    const expMonth = parseInt(month, 10);
                    const expYear = parseInt(year, 10);
                    
                    if (expMonth < 1 || expMonth > 12) {
                        this.setCustomValidity('Invalid month');
                    } else if (
                        (expYear < currentYear) || 
                        (expYear === currentYear && expMonth < currentMonth)
                    ) {
                        this.setCustomValidity('Card has expired');
                    } else {
                        this.setCustomValidity('');
                    }
                } else {
                    this.setCustomValidity('Please enter a valid expiry date (MM/YY)');
                }
            });
        }
        
        // Validate CVV
        if (cardCvvInput) {
            cardCvvInput.addEventListener('input', function() {
                // Only allow digits and limit length
                this.value = this.value.replace(/\D/g, '').substring(0, 4);
                
                if (this.value.length < 3) {
                    this.setCustomValidity('CVV must be at least 3 digits');
                } else {
                    this.setCustomValidity('');
                }
            });
        }
    }
}

/**
 * Validate email format
 * @param {HTMLInputElement} inputElement - Email input element
 */
function validateEmail(inputElement) {
    const emailRegex = /^[a-zA-Z0-9._%+-]+@[a-zA-Z0-9.-]+\.[a-zA-Z]{2,}$/;
    
    if (inputElement.value.trim() === '') {
        inputElement.setCustomValidity('Email is required');
    } else if (!emailRegex.test(inputElement.value)) {
        inputElement.setCustomValidity('Please enter a valid email address');
    } else {
        inputElement.setCustomValidity('');
    }
}

/**
 * Validate password strength
 * @param {HTMLInputElement} inputElement - Password input element
 */
function validatePassword(inputElement) {
    const password = inputElement.value;
    
    if (password.trim() === '') {
        inputElement.setCustomValidity('Password is required');
        return;
    }
    
    if (password.length < 8) {
        inputElement.setCustomValidity('Password must be at least 8 characters long');
        return;
    }
    
    // Check for uppercase, lowercase, and numbers
    const hasUppercase = /[A-Z]/.test(password);
    const hasLowercase = /[a-z]/.test(password);
    const hasNumber = /[0-9]/.test(password);
    
    if (!hasUppercase || !hasLowercase || !hasNumber) {
        inputElement.setCustomValidity('Password must include uppercase, lowercase, and numbers');
    } else {
        inputElement.setCustomValidity('');
    }
}
