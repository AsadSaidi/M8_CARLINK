/**
 * CARLINK - Reservation JavaScript
 * Handles functionality for the car reservation and booking process
 */

document.addEventListener('DOMContentLoaded', function() {
    // Initialize date picker functionality
    initDatePickers();
    
    // Initialize price calculation on date change
    initPriceCalculation();
    
    // Set up reservation validation
    initReservationValidation();
});

/**
 * Initialize date picker functionality
 * Handles date selection, constraints, and validation
 */
function initDatePickers() {
    const startDateInput = document.getElementById('booking_start_date');
    const endDateInput = document.getElementById('booking_end_date');
    
    if (startDateInput && endDateInput) {
        // Set minimum date to today for the start date
        const today = new Date().toISOString().split('T')[0];
        startDateInput.min = today;
        
        // When start date changes, update end date constraints
        startDateInput.addEventListener('change', function() {
            // Set minimum end date to the selected start date
            endDateInput.min = this.value;
            
            // If end date is before new start date, reset it
            if (endDateInput.value && endDateInput.value < this.value) {
                endDateInput.value = this.value;
            }
            
            // If both dates are set, trigger price calculation
            if (this.value && endDateInput.value) {
                calculateAndDisplayPrice();
            }
        });
        
        // When end date changes
        endDateInput.addEventListener('change', function() {
            // If start date is after end date, set start date to end date
            if (startDateInput.value && startDateInput.value > this.value) {
                startDateInput.value = this.value;
            }
            
            // If both dates are set, trigger price calculation
            if (this.value && startDateInput.value) {
                calculateAndDisplayPrice();
            }
        });
    }
}

/**
 * Initialize price calculation on date change
 */
function initPriceCalculation() {
    const bookingForm = document.getElementById('bookingDateForm');
    
    if (bookingForm) {
        // Get car price per day from the page
        const priceElement = document.querySelector('.badge.bg-success');
        let pricePerDay = 0;
        
        if (priceElement) {
            // Extract price from text (format: "€XX.XX/day")
            const priceText = priceElement.textContent;
            pricePerDay = parseFloat(priceText.replace(/[^0-9.]/g, ''));
        }
        
        // Save price for calculations
        bookingForm.dataset.pricePerDay = pricePerDay;
    }
}

/**
 * Calculate and display reservation price
 */
function calculateAndDisplayPrice() {
    const bookingForm = document.getElementById('bookingDateForm');
    const startDateInput = document.getElementById('booking_start_date');
    const endDateInput = document.getElementById('booking_end_date');
    const pricePreview = document.getElementById('price-preview');
    
    if (bookingForm && startDateInput && endDateInput) {
        const startDate = new Date(startDateInput.value);
        const endDate = new Date(endDateInput.value);
        const pricePerDay = parseFloat(bookingForm.dataset.pricePerDay) || 0;
        
        if (isNaN(pricePerDay) || pricePerDay <= 0) {
            console.error('Invalid price per day:', bookingForm.dataset.pricePerDay);
            return;
        }
        
        // Calculate days (including both start and end days)
        const timeDiff = endDate.getTime() - startDate.getTime();
        const days = Math.ceil(timeDiff / (1000 * 3600 * 24)) + 1;
        
        if (days < 1) {
            console.error('Invalid date range');
            return;
        }
        
        // Calculate price
        const subtotal = pricePerDay * days;
        
        // Apply discount for longer rentals
        let discountRate = 0;
        let discountDescription = '';
        
        if (days >= 7 && days < 14) {
            discountRate = 0.05; // 5% discount
            discountDescription = '5% weekly discount';
        } else if (days >= 14 && days < 30) {
            discountRate = 0.10; // 10% discount
            discountDescription = '10% biweekly discount';
        } else if (days >= 30) {
            discountRate = 0.15; // 15% discount
            discountDescription = '15% monthly discount';
        }
        
        const discount = subtotal * discountRate;
        const totalPrice = subtotal - discount;
        
        // Create or update price preview
        if (!pricePreview) {
            // Create new price preview element
            const newPricePreview = document.createElement('div');
            newPricePreview.id = 'price-preview';
            newPricePreview.className = 'alert alert-info mt-3';
            
            // Build HTML for price preview
            let priceHtml = `
                <h6 class="alert-heading">Price Estimate</h6>
                <div class="d-flex justify-content-between">
                    <span>${days} days × €${pricePerDay.toFixed(2)}</span>
                    <span>€${subtotal.toFixed(2)}</span>
                </div>
            `;
            
            if (discountRate > 0) {
                priceHtml += `
                    <div class="d-flex justify-content-between text-success">
                        <span>${discountDescription}</span>
                        <span>-€${discount.toFixed(2)}</span>
                    </div>
                `;
            }
            
            priceHtml += `
                <hr>
                <div class="d-flex justify-content-between fw-bold">
                    <span>Total</span>
                    <span>€${totalPrice.toFixed(2)}</span>
                </div>
            `;
            
            newPricePreview.innerHTML = priceHtml;
            
            // Insert after the end date input
            endDateInput.parentNode.parentNode.insertAdjacentElement('afterend', newPricePreview);
        } else {
            // Update existing price preview
            let priceHtml = `
                <h6 class="alert-heading">Price Estimate</h6>
                <div class="d-flex justify-content-between">
                    <span>${days} days × €${pricePerDay.toFixed(2)}</span>
                    <span>€${subtotal.toFixed(2)}</span>
                </div>
            `;
            
            if (discountRate > 0) {
                priceHtml += `
                    <div class="d-flex justify-content-between text-success">
                        <span>${discountDescription}</span>
                        <span>-€${discount.toFixed(2)}</span>
                    </div>
                `;
            }
            
            priceHtml += `
                <hr>
                <div class="d-flex justify-content-between fw-bold">
                    <span>Total</span>
                    <span>€${totalPrice.toFixed(2)}</span>
                </div>
            `;
            
            pricePreview.innerHTML = priceHtml;
        }
        
        // Check for availability AJAX call
        checkCarAvailability(startDateInput.value, endDateInput.value);
    }
}

/**
 * Check car availability for the selected dates
 * @param {string} startDate - Start date in YYYY-MM-DD format
 * @param {string} endDate - End date in YYYY-MM-DD format
 */
function checkCarAvailability(startDate, endDate) {
    const bookingForm = document.getElementById('bookingDateForm');
    const carId = new URLSearchParams(bookingForm.getAttribute('action')).get('car_id');
    
    if (!carId) {
        console.error('Car ID not found');
        return;
    }
    
    // Make AJAX request to check availability
    const xhr = new XMLHttpRequest();
    xhr.open('GET', `../api/check-availability.php?car_id=${carId}&start_date=${startDate}&end_date=${endDate}`, true);
    
    xhr.onload = function() {
        if (this.status === 200) {
            try {
                const response = JSON.parse(this.responseText);
                
                if (response.success) {
                    // Update availability message
                    updateAvailabilityMessage(response.available);
                    
                    // Enable/disable book button based on availability
                    const bookButton = document.querySelector('.btn-success');
                    if (bookButton) {
                        bookButton.disabled = !response.available;
                    }
                } else {
                    console.error('Error checking availability:', response.message);
                }
            } catch (error) {
                console.error('Error parsing JSON response:', error);
            }
        } else {
            console.error('Request failed. Status:', this.status);
        }
    };
    
    xhr.onerror = function() {
        console.error('Request failed');
    };
    
    xhr.send();
}

/**
 * Update availability message on the page
 * @param {boolean} isAvailable - Whether the car is available for the selected dates
 */
function updateAvailabilityMessage(isAvailable) {
    // Remove any existing availability messages
    const existingMessage = document.getElementById('availability-message');
    if (existingMessage) {
        existingMessage.remove();
    }
    
    // Create new message
    const messageDiv = document.createElement('div');
    messageDiv.id = 'availability-message';
    messageDiv.className = isAvailable ? 'alert alert-success mt-3' : 'alert alert-danger mt-3';
    
    if (isAvailable) {
        messageDiv.innerHTML = '<i class="fas fa-check-circle me-2"></i>Car is available for the selected dates!';
    } else {
        messageDiv.innerHTML = '<i class="fas fa-exclamation-circle me-2"></i>This car is not available for the selected dates.';
    }
    
    // Insert after price preview
    const pricePreview = document.getElementById('price-preview');
    if (pricePreview) {
        pricePreview.parentNode.insertBefore(messageDiv, pricePreview.nextSibling);
    } else {
        const endDateInput = document.getElementById('booking_end_date');
        if (endDateInput) {
            endDateInput.parentNode.parentNode.insertAdjacentElement('afterend', messageDiv);
        }
    }
}

/**
 * Initialize reservation form validation
 */
function initReservationValidation() {
    const bookingForm = document.getElementById('bookingDateForm');
    
    if (bookingForm) {
        bookingForm.addEventListener('submit', function(event) {
            const startDateInput = document.getElementById('booking_start_date');
            const endDateInput = document.getElementById('booking_end_date');
            
            if (!startDateInput.value || !endDateInput.value) {
                event.preventDefault();
                alert('Please select both start and end dates');
                return false;
            }
            
            // Validate date range
            const startDate = new Date(startDateInput.value);
            const endDate = new Date(endDateInput.value);
            const today = new Date();
            today.setHours(0, 0, 0, 0);
            
            if (startDate < today) {
                event.preventDefault();
                alert('Start date cannot be in the past');
                return false;
            }
            
            if (endDate < startDate) {
                event.preventDefault();
                alert('End date must be after start date');
                return false;
            }
            
            return true;
        });
    }
}
