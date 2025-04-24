/**
 * CARLINK - Car Form JavaScript
 * Handles functionality for the car addition/editing form
 */

document.addEventListener('DOMContentLoaded', function() {
    // Initialize make and model selection behavior
    initMakeModelSelection();
    
    // Initialize image upload preview
    initImageUploadPreview();
    
    // Add automobile specs API integration
    initAutoSpecsIntegration();
    
    // Initialize form validation
    initCarFormValidation();
});

/**
 * Initialize make and model selection behavior
 * Loads models when a make is selected
 */
function initMakeModelSelection() {
    const makeSelect = document.getElementById('make');
    const modelSelect = document.getElementById('model');
    
    if (makeSelect && modelSelect) {
        makeSelect.addEventListener('change', function() {
            const selectedMake = this.value;
            
            // Clear current model options
            modelSelect.innerHTML = '<option value="">Select Model</option>';
            
            // Disable model select if no make is selected
            if (!selectedMake) {
                modelSelect.disabled = true;
                return;
            }
            
            // Show loading indicator
            modelSelect.innerHTML += '<option value="" disabled selected>Loading models...</option>';
            modelSelect.disabled = true;
            
            // Fetch models for the selected make
            fetch(`../api/get-models.php?make=${encodeURIComponent(selectedMake)}`)
                .then(response => response.json())
                .then(data => {
                    // Reset model options
                    modelSelect.innerHTML = '<option value="">Select Model</option>';
                    
                    if (data.success && data.models && data.models.length > 0) {
                        // Add models to select dropdown
                        data.models.forEach(model => {
                            const option = document.createElement('option');
                            option.value = model;
                            option.textContent = model;
                            modelSelect.appendChild(option);
                        });
                        
                        modelSelect.disabled = false;
                    } else {
                        modelSelect.innerHTML = '<option value="" disabled selected>No models found</option>';
                    }
                })
                .catch(error => {
                    console.error('Error fetching models:', error);
                    modelSelect.innerHTML = '<option value="" disabled selected>Error loading models</option>';
                });
        });
    }
}

/**
 * Initialize image upload preview
 * Shows thumbnails of selected images before upload
 */
function initImageUploadPreview() {
    const imageInput = document.getElementById('car_images');
    
    if (imageInput) {
        // Create a preview container after the input
        const previewContainer = document.createElement('div');
        previewContainer.className = 'image-preview-container row mt-3';
        imageInput.parentNode.insertBefore(previewContainer, imageInput.nextSibling);
        
        imageInput.addEventListener('change', function() {
            // Clear previous previews
            previewContainer.innerHTML = '';
            
            // Check if files are selected
            if (this.files && this.files.length > 0) {
                // Show preview for each file (max 5)
                const maxFiles = Math.min(this.files.length, 5);
                
                for (let i = 0; i < maxFiles; i++) {
                    const file = this.files[i];
                    
                    // Validate file type
                    if (!file.type.match('image.*')) {
                        continue;
                    }
                    
                    // Create column for the preview
                    const previewCol = document.createElement('div');
                    previewCol.className = 'col-md-3 col-sm-4 col-6 mb-2';
                    
                    // Create card for the preview
                    const previewCard = document.createElement('div');
                    previewCard.className = 'card h-100';
                    
                    // Create image element
                    const img = document.createElement('img');
                    img.className = 'card-img-top';
                    img.style.height = '120px';
                    img.style.objectFit = 'cover';
                    
                    // Create card body for file name
                    const cardBody = document.createElement('div');
                    cardBody.className = 'card-body p-2';
                    cardBody.innerHTML = `<small class="text-muted">${file.name.substring(0, 15)}${file.name.length > 15 ? '...' : ''}</small>`;
                    
                    // Read file as data URL and set image source
                    const reader = new FileReader();
                    reader.onload = function(e) {
                        img.src = e.target.result;
                    };
                    reader.readAsDataURL(file);
                    
                    // Assemble the preview element
                    previewCard.appendChild(img);
                    previewCard.appendChild(cardBody);
                    previewCol.appendChild(previewCard);
                    previewContainer.appendChild(previewCol);
                }
                
                // Show warning if too many files were selected
                if (this.files.length > 5) {
                    const warningDiv = document.createElement('div');
                    warningDiv.className = 'col-12 mt-2';
                    warningDiv.innerHTML = '<div class="alert alert-warning">Only the first 5 images will be uploaded</div>';
                    previewContainer.appendChild(warningDiv);
                }
            }
        });
    }
}

/**
 * Initialize car form validation
 */
function initCarFormValidation() {
    const carForm = document.getElementById('car-form');
    
    if (carForm) {
        carForm.addEventListener('submit', function(event) {
            // Check required fields
            const requiredFields = ['make', 'model', 'year', 'fuel_type', 'transmission', 'price_per_day', 'location'];
            let isValid = true;
            
            requiredFields.forEach(fieldId => {
                const field = document.getElementById(fieldId);
                
                if (field && field.value.trim() === '') {
                    field.classList.add('is-invalid');
                    isValid = false;
                } else if (field) {
                    field.classList.remove('is-invalid');
                }
            });
            
            // Validate price
            const priceField = document.getElementById('price_per_day');
            if (priceField && (isNaN(priceField.value) || parseFloat(priceField.value) <= 0)) {
                priceField.classList.add('is-invalid');
                isValid = false;
            }
            
            // Validate year
            const yearField = document.getElementById('year');
            if (yearField) {
                const year = parseInt(yearField.value);
                const currentYear = new Date().getFullYear();
                
                if (isNaN(year) || year < 1950 || year > currentYear + 1) {
                    yearField.classList.add('is-invalid');
                    isValid = false;
                }
            }
            
            if (!isValid) {
                event.preventDefault();
                
                // Show alert for validation errors
                const errorMessage = document.createElement('div');
                errorMessage.className = 'alert alert-danger';
                errorMessage.textContent = 'Please fill in all required fields correctly.';
                
                // Find the form header and insert after it
                const formHeader = carForm.querySelector('h5');
                if (formHeader) {
                    formHeader.parentNode.insertBefore(errorMessage, formHeader.nextSibling);
                } else {
                    carForm.prepend(errorMessage);
                }
                
                // Scroll to the top of the form
                window.scrollTo(0, carForm.offsetTop - 100);
            }
        });
    }
}

/**
 * Initialize auto specs integration
 * Fetch car specifications from API when make and model are selected
 */
function initAutoSpecsIntegration() {
    const makeSelect = document.getElementById('make');
    const modelSelect = document.getElementById('model');
    const yearInput = document.getElementById('year');
    
    if (makeSelect && modelSelect && yearInput) {
        // Create a debounced function to fetch car specs
        const fetchCarSpecs = debounce(function() {
            const make = makeSelect.value;
            const model = modelSelect.value;
            const year = yearInput.value;
            
            if (make && model && year) {
                // Fetch car specs from API
                fetch(`../api/get-car-data.php?make=${encodeURIComponent(make)}&model=${encodeURIComponent(model)}&year=${year}`)
                    .then(response => response.json())
                    .then(data => {
                        if (data.success && data.cars && data.cars.length > 0) {
                            // Get the first car from results
                            const car = data.cars[0];
                            
                            // Auto-fill form fields
                            autoFillCarSpecs(car);
                        }
                    })
                    .catch(error => {
                        console.error('Error fetching car specs:', error);
                    });
            }
        }, 300);
        
        // Add event listeners to trigger specs fetch
        modelSelect.addEventListener('change', fetchCarSpecs);
        yearInput.addEventListener('change', fetchCarSpecs);
    }
}

/**
 * Auto-fill car specifications form fields from API data
 * @param {Object} car - Car data from API
 */
function autoFillCarSpecs(car) {
    // Map of API fields to form fields
    const fieldMapping = {
        'cylinders': 'cylinders',
        'displacement': 'displacement',
        'horsepower': 'power',
        'city_mpg': 'city_mpg',
        'highway_mpg': 'highway_mpg',
        'fuel_type': 'fuel_type',
        'transmission': 'transmission'
    };
    
    // Auto-fill form fields
    Object.keys(fieldMapping).forEach(apiField => {
        const formField = document.getElementById(fieldMapping[apiField]);
        
        if (formField && car[apiField]) {
            // Special handling for select elements
            if (formField.tagName === 'SELECT') {
                // Find the option that matches the value (case-insensitive)
                const options = Array.from(formField.options);
                const matchingOption = options.find(option => 
                    option.value.toLowerCase() === car[apiField].toLowerCase()
                );
                
                if (matchingOption) {
                    formField.value = matchingOption.value;
                }
            } else {
                formField.value = car[apiField];
            }
        }
    });
}

/**
 * Debounce function to limit how often a function can be called
 * @param {Function} func - Function to debounce
 * @param {number} wait - Wait time in milliseconds
 * @returns {Function} Debounced function
 */
function debounce(func, wait) {
    let timeout;
    return function(...args) {
        const context = this;
        clearTimeout(timeout);
        timeout = setTimeout(() => func.apply(context, args), wait);
    };
}
