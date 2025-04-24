/**
 * CARLINK - Payment JavaScript
 * Handles functionality for the payment processing
 */

document.addEventListener('DOMContentLoaded', function() {
    // Initialize credit card form formatting
    initCreditCardFormat();
    
    // Setup payment form validation
    initPaymentFormValidation();
    
    // Initialize billing form dynamics
    initBillingFormDynamics();
});

/**
 * Initialize credit card input formatting
 */
function initCreditCardFormat() {
    // Format credit card number
    const cardNumberInput = document.getElementById('card_number');
    if (cardNumberInput) {
        cardNumberInput.addEventListener('input', function(e) {
            // Remove non-digit characters
            let value = this.value.replace(/\D/g, '');
            
            // Add spaces for better readability (groups of 4 digits)
            if (value.length > 0) {
                value = value.match(/.{1,4}/g).join(' ');
            }
            
            // Update the input value
            this.value = value;
            
            // Limit to 19 digits (16 digits + 3 spaces)
            if (value.length > 19) {
                this.value = value.substring(0, 19);
            }
        });
    }
    
    // Format card expiry date (MM/YY)
    const cardExpiryInput = document.getElementById('card_expiry');
    if (cardExpiryInput) {
        cardExpiryInput.addEventListener('input', function(e) {
            // Remove non-digit characters
            let value = this.value.replace(/\D/g, '');
            
            // Add slash after month
            if (value.length > 2) {
                value = value.substring(0, 2) + '/' + value.substring(2, 4);
            }
            
            // Update the input value
            this.value = value;
            
            // Limit to MM/YY format (5 characters)
            if (value.length > 5) {
                this.value = value.substring(0, 5);
            }
        });
    }
    
    // Format CVV (3-4 digits only)
    const cardCvvInput = document.getElementById('card_cvv');
    if (cardCvvInput) {
        cardCvvInput.addEventListener('input', function(e) {
            // Remove non-digit characters
            let value = this.value.replace(/\D/g, '');
            
            // Update the input value
            this.value = value;
            
            // Limit to 4 digits
            if (value.length > 4) {
                this.value = value.substring(0, 4);
            }
        });
    }
}

/**
 * Initialize payment form validation
 */
function initPaymentFormValidation() {
    const paymentForm = document.getElementById('payment-form');
    
    if (paymentForm) {
        paymentForm.addEventListener('submit', function(event) {
            let isValid = true;
            
            // Validate card name
            const cardNameInput = document.getElementById('card_name');
            if (cardNameInput && cardNameInput.value.trim() === '') {
                markInvalid(cardNameInput, 'Card name is required');
                isValid = false;
            } else if (cardNameInput) {
                markValid(cardNameInput);
            }
            
            // Validate card number (basic Luhn algorithm)
            const cardNumberInput = document.getElementById('card_number');
            if (cardNumberInput) {
                // Remove spaces for validation
                const cardNumber = cardNumberInput.value.replace(/\s/g, '');
                
                if (cardNumber.length < 13 || cardNumber.length > 19) {
                    markInvalid(cardNumberInput, 'Invalid card number length');
                    isValid = false;
                } else if (!validateLuhn(cardNumber)) {
                    markInvalid(cardNumberInput, 'Invalid card number');
                    isValid = false;
                } else {
                    markValid(cardNumberInput);
                }
            }
            
            // Validate card expiry
            const cardExpiryInput = document.getElementById('card_expiry');
            if (cardExpiryInput) {
                const isExpiryValid = validateCardExpiry(cardExpiryInput.value);
                
                if (!isExpiryValid.valid) {
                    markInvalid(cardExpiryInput, isExpiryValid.message);
                    isValid = false;
                } else {
                    markValid(cardExpiryInput);
                }
            }
            
            // Validate CVV
            const cardCvvInput = document.getElementById('card_cvv');
            if (cardCvvInput) {
                const cvv = cardCvvInput.value.trim();
                
                if (cvv.length < 3 || cvv.length > 4) {
                    markInvalid(cardCvvInput, 'CVV must be 3-4 digits');
                    isValid = false;
                } else {
                    markValid(cardCvvInput);
                }
            }
            
            // Validate billing address fields
            const requiredBillingFields = ['billing_address', 'billing_city', 'billing_zip', 'billing_country'];
            
            requiredBillingFields.forEach(fieldId => {
                const field = document.getElementById(fieldId);
                
                if (field && field.value.trim() === '') {
                    markInvalid(field, 'This field is required');
                    isValid = false;
                } else if (field) {
                    markValid(field);
                }
            });
            
            // Prevent form submission if validation fails
            if (!isValid) {
                event.preventDefault();
                
                // Scroll to the first invalid input
                const firstInvalid = document.querySelector('.is-invalid');
                if (firstInvalid) {
                    firstInvalid.scrollIntoView({ behavior: 'smooth', block: 'center' });
                }
            }
        });
    }
}

/**
 * Initialize billing form dynamics
 */
function initBillingFormDynamics() {
    // Add country selection helper
    const countrySelect = document.getElementById('billing_country');
    if (countrySelect) {
        // Preselect user's country based on browser locale if not already selected
        if (!countrySelect.value) {
            const userLocale = navigator.language || navigator.userLanguage;
            const userCountry = userLocale.split('-')[1] || '';
            
            if (userCountry) {
                // Find option with this country code
                const countryOption = countrySelect.querySelector(`option[value="${userCountry}"]`);
                
                if (countryOption) {
                    countrySelect.value = userCountry;
                }
            }
        }
    }
}

/**
 * Mark an input as invalid
 * @param {HTMLElement} input - The input element
 * @param {string} message - The error message
 */
function markInvalid(input, message) {
    input.classList.add('is-invalid');
    input.classList.remove('is-valid');
    
    // Find or create feedback element
    let feedback = input.parentNode.querySelector('.invalid-feedback');
    
    if (!feedback) {
        feedback = document.createElement('div');
        feedback.className = 'invalid-feedback';
        input.parentNode.appendChild(feedback);
    }
    
    feedback.textContent = message;
}

/**
 * Mark an input as valid
 * @param {HTMLElement} input - The input element
 */
function markValid(input) {
    input.classList.remove('is-invalid');
    input.classList.add('is-valid');
}

/**
 * Validate credit card number using Luhn algorithm
 * @param {string} cardNumber - Credit card number (without spaces)
 * @returns {boolean} True if valid
 */
function validateLuhn(cardNumber) {
    if (!/^\d+$/.test(cardNumber)) return false;
    
    let sum = 0;
    let shouldDouble = false;
    
    // Walk through the card number from right to left
    for (let i = cardNumber.length - 1; i >= 0; i--) {
        let digit = parseInt(cardNumber.charAt(i));
        
        if (shouldDouble) {
            digit *= 2;
            if (digit > 9) digit -= 9;
        }
        
        sum += digit;
        shouldDouble = !shouldDouble;
    }
    
    return (sum % 10) === 0;
}

/**
 * Validate credit card expiry date
 * @param {string} expiry - Expiry date in MM/YY format
 * @returns {Object} Validation result with valid flag and error message
 */
function validateCardExpiry(expiry) {
    // Check format (MM/YY)
    if (!/^\d{2}\/\d{2}$/.test(expiry)) {
        return { valid: false, message: 'Use MM/YY format' };
    }
    
    const [monthStr, yearStr] = expiry.split('/');
    const month = parseInt(monthStr, 10);
    const year = parseInt(yearStr, 10) + 2000; // Convert to full year (20XX)
    
    // Validate month
    if (month < 1 || month > 12) {
        return { valid: false, message: 'Invalid month' };
    }
    
    // Get current date for comparison
    const now = new Date();
    const currentMonth = now.getMonth() + 1; // JavaScript months are 0-indexed
    const currentYear = now.getFullYear();
    
    // Check if card is expired
    if (year < currentYear || (year === currentYear && month < currentMonth)) {
        return { valid: false, message: 'Card has expired' };
    }
    
    // Check if date is too far in the future (more than 10 years)
    if (year > currentYear + 10) {
        return { valid: false, message: 'Expiry date too far in the future' };
    }
    
    return { valid: true, message: '' };
}

/**
 * Format price with currency symbol
 * @param {number} price - Price value
 * @param {string} currency - Currency code (default: EUR)
 * @returns {string} Formatted price string
 */
function formatPrice(price, currency = 'EUR') {
    return new Intl.NumberFormat('en-US', {
        style: 'currency',
        currency: currency,
        minimumFractionDigits: 2
    }).format(price);
}
