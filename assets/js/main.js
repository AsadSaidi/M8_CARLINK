/**
 * CARLINK - Main JavaScript File
 * Contains general functionality used across the site
 */

document.addEventListener('DOMContentLoaded', function() {
    
    // Initialize tooltips
    initTooltips();
    
    // Initialize auto-dismissing alerts
    initAlerts();
    
    // Setup date input defaults and validation
    setupDateInputs();
    
    // Mobile menu behavior enhancements
    setupMobileMenu();
    
    // Add event listeners for search forms
    setupSearchForms();
});

/**
 * Initialize Bootstrap tooltips
 */
function initTooltips() {
    var tooltipTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="tooltip"]'));
    tooltipTriggerList.map(function (tooltipTriggerEl) {
        return new bootstrap.Tooltip(tooltipTriggerEl);
    });
}

/**
 * Set up auto-dismissing alerts after 5 seconds
 */
function initAlerts() {
    const alerts = document.querySelectorAll('.alert:not(.alert-permanent)');
    
    alerts.forEach(alert => {
        // Create a close timeout for non-error alerts
        if (!alert.classList.contains('alert-danger')) {
            setTimeout(() => {
                // Get the Bootstrap alert instance and close it
                const bsAlert = new bootstrap.Alert(alert);
                bsAlert.close();
            }, 5000);
        }
        
        // Add fade-out class before the alert is closed
        alert.addEventListener('close.bs.alert', function () {
            this.classList.add('fade-out');
        });
    });
}

/**
 * Set up date inputs with defaults and validation
 */
function setupDateInputs() {
    const dateInputs = document.querySelectorAll('input[type="date"]');
    
    dateInputs.forEach(input => {
        // Set min attribute to today if not already set
        if (!input.min) {
            const today = new Date().toISOString().split('T')[0];
            input.min = today;
        }
        
        // Add event listeners for start/end date pairs
        if (input.id.includes('start_date')) {
            const endDateId = input.id.replace('start_date', 'end_date');
            const endDateInput = document.getElementById(endDateId);
            
            if (endDateInput) {
                // When start date changes, update end date min value
                input.addEventListener('change', function() {
                    endDateInput.min = this.value;
                    
                    // If end date is now invalid, update it
                    if (endDateInput.value && new Date(endDateInput.value) < new Date(this.value)) {
                        endDateInput.value = this.value;
                    }
                });
            }
        }
    });
}

/**
 * Setup mobile menu behavior
 */
function setupMobileMenu() {
    const navbarToggler = document.querySelector('.navbar-toggler');
    const navbarMenu = document.querySelector('#navbarNav');
    
    if (navbarToggler && navbarMenu) {
        // Close the menu when a nav item is clicked
        const navLinks = navbarMenu.querySelectorAll('.nav-link');
        
        navLinks.forEach(link => {
            link.addEventListener('click', function() {
                // Check if the menu is expanded and viewport is mobile sized
                if (window.innerWidth < 992 && navbarToggler.getAttribute('aria-expanded') === 'true') {
                    // Create a Bootstrap collapse instance and hide it
                    const bsCollapse = new bootstrap.Collapse(navbarMenu);
                    bsCollapse.hide();
                }
            });
        });
    }
}

/**
 * Setup search forms and filters
 */
function setupSearchForms() {
    const searchForms = document.querySelectorAll('form[action*="explore"]');
    
    searchForms.forEach(form => {
        // Add an event listener for form submission
        form.addEventListener('submit', function(event) {
            // Remove empty fields from the form before submission
            const inputs = form.querySelectorAll('input, select');
            
            inputs.forEach(input => {
                if (input.value === '' && !input.required && input.name !== 'route') {
                    input.disabled = true;
                }
            });
        });
        
        // Add clear button functionality if present
        const clearButton = form.querySelector('#clearFilters');
        
        if (clearButton) {
            clearButton.addEventListener('click', function() {
                window.location.href = window.location.pathname + '?route=explore';
            });
        }
    });
}

/**
 * Format date as "Month Day, Year" (e.g., "June 15, 2023")
 * @param {string} dateStr - Date string in format YYYY-MM-DD
 * @returns {string} Formatted date string
 */
function formatDate(dateStr) {
    if (!dateStr) return '';
    
    const date = new Date(dateStr);
    const options = { year: 'numeric', month: 'long', day: 'numeric' };
    return date.toLocaleDateString('en-US', options);
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

/**
 * Calculate the difference in days between two dates
 * @param {string} startDate - Start date in YYYY-MM-DD format
 * @param {string} endDate - End date in YYYY-MM-DD format
 * @returns {number} Number of days
 */
function calculateDays(startDate, endDate) {
    const start = new Date(startDate);
    const end = new Date(endDate);
    const timeDiff = end.getTime() - start.getTime();
    return Math.ceil(timeDiff / (1000 * 3600 * 24)) + 1; // Include both start and end days
}

/**
 * Calculate rental price based on days and daily rate
 * @param {number} pricePerDay - Daily rental price
 * @param {number} days - Number of rental days
 * @returns {object} Price calculation details
 */
function calculateRentalPrice(pricePerDay, days) {
    const subtotal = pricePerDay * days;
    let discountRate = 0;
    
    // Apply discount for longer rentals
    if (days >= 7 && days < 14) {
        discountRate = 0.05; // 5% discount for 7-13 days
    } else if (days >= 14 && days < 30) {
        discountRate = 0.10; // 10% discount for 14-29 days
    } else if (days >= 30) {
        discountRate = 0.15; // 15% discount for 30+ days
    }
    
    const discount = subtotal * discountRate;
    const totalPrice = subtotal - discount;
    
    return {
        pricePerDay: pricePerDay,
        days: days,
        subtotal: subtotal,
        discountRate: discountRate,
        discount: discount,
        totalPrice: totalPrice
    };
}
