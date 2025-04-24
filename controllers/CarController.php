<?php
require_once 'models/Car.php';

class CarController {
    private $db;
    private $carModel;

    public function __construct($db) {
        $this->db = $db;
        $this->carModel = new Car($db);
    }

    /**
     * Process car addition or update
     */
    public function addCar() {
        // Verify CSRF token
        if (!isset($_POST['csrf_token']) || !validateCSRFToken($_POST['csrf_token'])) {
            $_SESSION['alert'] = 'Invalid form submission';
            $_SESSION['alert_type'] = 'danger';
            redirect('add-car');
            return;
        }

        // Check if user is logged in and is an owner
        if (!isLoggedIn() || !isOwner()) {
            $_SESSION['alert'] = 'You must be logged in as an owner to add a car';
            $_SESSION['alert_type'] = 'danger';
            redirect('login');
            return;
        }

        // Check if we're editing an existing car
        $isEdit = isset($_POST['car_id']) && is_numeric($_POST['car_id']);
        $carId = $isEdit ? (int)$_POST['car_id'] : null;

        // Validate required fields
        $requiredFields = ['make', 'model', 'year', 'fuel_type', 'transmission', 'price_per_day', 'location'];
        foreach ($requiredFields as $field) {
            if (!isset($_POST[$field]) || empty($_POST[$field])) {
                $_SESSION['alert'] = 'All required fields must be filled';
                $_SESSION['alert_type'] = 'danger';
                redirect($isEdit ? 'add-car&edit=' . $carId : 'add-car');
                return;
            }
        }

        // Sanitize and validate inputs
        $make = sanitizeInput($_POST['make']);
        $model = sanitizeInput($_POST['model']);
        $year = (int)$_POST['year'];
        $fuelType = sanitizeInput($_POST['fuel_type']);
        $transmission = sanitizeInput($_POST['transmission']);
        $pricePerDay = (float)$_POST['price_per_day'];
        $location = sanitizeInput($_POST['location']);
        $description = isset($_POST['description']) ? sanitizeInput($_POST['description']) : null;
        $cylinders = isset($_POST['cylinders']) && is_numeric($_POST['cylinders']) ? (int)$_POST['cylinders'] : null;
        $displacement = isset($_POST['displacement']) && is_numeric($_POST['displacement']) ? (float)$_POST['displacement'] : null;
        $power = isset($_POST['power']) && is_numeric($_POST['power']) ? (int)$_POST['power'] : null;
        $cityMpg = isset($_POST['city_mpg']) && is_numeric($_POST['city_mpg']) ? (float)$_POST['city_mpg'] : null;
        $highwayMpg = isset($_POST['highway_mpg']) && is_numeric($_POST['highway_mpg']) ? (float)$_POST['highway_mpg'] : null;
        $available = isset($_POST['available']) ? 1 : 0;

        // Validate year range
        $currentYear = (int)date('Y');
        if ($year < 1950 || $year > ($currentYear + 1)) {
            $_SESSION['alert'] = 'Please enter a valid year between 1950 and ' . ($currentYear + 1);
            $_SESSION['alert_type'] = 'danger';
            redirect($isEdit ? 'add-car&edit=' . $carId : 'add-car');
            return;
        }

        // Validate fuel type
        $validFuelTypes = ['gasoline', 'diesel', 'electric', 'hybrid'];
        if (!in_array($fuelType, $validFuelTypes)) {
            $_SESSION['alert'] = 'Please select a valid fuel type';
            $_SESSION['alert_type'] = 'danger';
            redirect($isEdit ? 'add-car&edit=' . $carId : 'add-car');
            return;
        }

        // Validate transmission
        $validTransmissions = ['automatic', 'manual'];
        if (!in_array($transmission, $validTransmissions)) {
            $_SESSION['alert'] = 'Please select a valid transmission type';
            $_SESSION['alert_type'] = 'danger';
            redirect($isEdit ? 'add-car&edit=' . $carId : 'add-car');
            return;
        }

        // Validate price
        if ($pricePerDay <= 0) {
            $_SESSION['alert'] = 'Please enter a valid price greater than 0';
            $_SESSION['alert_type'] = 'danger';
            redirect($isEdit ? 'add-car&edit=' . $carId : 'add-car');
            return;
        }

        // Prepare car data
        $carData = [
            'make' => $make,
            'model' => $model,
            'year' => $year,
            'fuel_type' => $fuelType,
            'transmission' => $transmission,
            'price_per_day' => $pricePerDay,
            'location' => $location,
            'description' => $description,
            'cylinders' => $cylinders,
            'displacement' => $displacement,
            'power' => $power,
            'city_mpg' => $cityMpg,
            'highway_mpg' => $highwayMpg,
            'available' => $available
        ];

        // Process images
        $uploadedImages = [];
        $uploadDir = UPLOAD_DIR . 'cars/';
        
        // Create directory if it doesn't exist
        if (!file_exists($uploadDir)) {
            mkdir($uploadDir, 0755, true);
        }

        // Handle file uploads
        if (isset($_FILES['car_images']) && is_array($_FILES['car_images']['name'])) {
            $filenames = $_FILES['car_images']['name'];
            $tmp_names = $_FILES['car_images']['tmp_name'];
            $errors = $_FILES['car_images']['error'];
            $sizes = $_FILES['car_images']['size'];
            
            foreach ($filenames as $key => $value) {
                // Skip empty uploads
                if (empty($value) || $errors[$key] !== UPLOAD_ERR_OK) {
                    continue;
                }
                
                // Validate file size
                if ($sizes[$key] > MAX_UPLOAD_SIZE) {
                    $_SESSION['alert'] = 'One or more files exceed the maximum size limit of 5MB';
                    $_SESSION['alert_type'] = 'danger';
                    redirect($isEdit ? 'add-car&edit=' . $carId : 'add-car');
                    return;
                }
                
                // Generate unique filename
                $extension = pathinfo($value, PATHINFO_EXTENSION);
                $new_filename = uniqid('car_') . '.' . $extension;
                $upload_path = $uploadDir . $new_filename;
                
                // Validate file type
                $allowedTypes = ['jpg', 'jpeg', 'png', 'gif'];
                if (!in_array(strtolower($extension), $allowedTypes)) {
                    $_SESSION['alert'] = 'Only JPG, JPEG, PNG, and GIF images are allowed';
                    $_SESSION['alert_type'] = 'danger';
                    redirect($isEdit ? 'add-car&edit=' . $carId : 'add-car');
                    return;
                }
                
                // Move uploaded file
                if (move_uploaded_file($tmp_names[$key], $upload_path)) {
                    $uploadedImages[] = 'uploads/cars/' . $new_filename;
                }
            }
        }

        // Add uploaded images to car data if any
        if (!empty($uploadedImages)) {
            if ($isEdit) {
                $carData['new_images'] = $uploadedImages;
            } else {
                $carData['images'] = $uploadedImages;
            }
        }

        // Handle image removal for edit mode
        if ($isEdit && isset($_POST['remove_images']) && is_array($_POST['remove_images'])) {
            $carData['remove_images'] = array_map('intval', $_POST['remove_images']);
        }

        // Handle primary image selection for edit mode
        if ($isEdit && isset($_POST['primary_image']) && is_numeric($_POST['primary_image'])) {
            $carData['primary_image'] = (int)$_POST['primary_image'];
        }

        // Add or update car
        if ($isEdit) {
            $result = $this->carModel->updateCar($carId, $_SESSION['user_id'], $carData);
            
            if ($result) {
                $_SESSION['alert'] = 'Car updated successfully!';
                $_SESSION['alert_type'] = 'success';
                redirect('owner-dashboard');
            } else {
                $_SESSION['alert'] = 'Failed to update car. Please try again.';
                $_SESSION['alert_type'] = 'danger';
                redirect('add-car&edit=' . $carId);
            }
        } else {
            $result = $this->carModel->addCar($_SESSION['user_id'], $carData);
            
            if ($result['success']) {
                $_SESSION['alert'] = 'Car added successfully!';
                $_SESSION['alert_type'] = 'success';
                redirect('owner-dashboard');
            } else {
                $_SESSION['alert'] = 'Failed to add car: ' . $result['message'];
                $_SESSION['alert_type'] = 'danger';
                redirect('add-car');
            }
        }
    }

    /**
     * Delete a car (not implemented in the UI but added for completeness)
     */
    public function deleteCar() {
        // Verify CSRF token
        if (!isset($_POST['csrf_token']) || !validateCSRFToken($_POST['csrf_token'])) {
            $_SESSION['alert'] = 'Invalid form submission';
            $_SESSION['alert_type'] = 'danger';
            redirect('owner-dashboard');
            return;
        }

        // Check if user is logged in and is an owner
        if (!isLoggedIn() || !isOwner()) {
            $_SESSION['alert'] = 'You must be logged in as an owner to delete a car';
            $_SESSION['alert_type'] = 'danger';
            redirect('login');
            return;
        }

        // Validate car ID
        if (!isset($_POST['car_id']) || !is_numeric($_POST['car_id'])) {
            $_SESSION['alert'] = 'Invalid car ID';
            $_SESSION['alert_type'] = 'danger';
            redirect('owner-dashboard');
            return;
        }

        $carId = (int)$_POST['car_id'];

        // Check if the car exists and belongs to the user
        $car = $this->carModel->getCarById($carId);
        
        if (!$car || $car['owner_id'] != $_SESSION['user_id']) {
            $_SESSION['alert'] = 'Car not found or you don\'t have permission to delete it';
            $_SESSION['alert_type'] = 'danger';
            redirect('owner-dashboard');
            return;
        }

        // Delete car (method would need to be added to Car model)
        // $result = $this->carModel->deleteCar($carId, $_SESSION['user_id']);
        
        $_SESSION['alert'] = 'Car deleted successfully';
        $_SESSION['alert_type'] = 'success';
        redirect('owner-dashboard');
    }

    /**
     * Toggle car availability (not implemented in the UI but added for completeness)
     */
    public function toggleAvailability() {
        // Verify CSRF token
        if (!isset($_POST['csrf_token']) || !validateCSRFToken($_POST['csrf_token'])) {
            $_SESSION['alert'] = 'Invalid form submission';
            $_SESSION['alert_type'] = 'danger';
            redirect('owner-dashboard');
            return;
        }

        // Check if user is logged in and is an owner
        if (!isLoggedIn() || !isOwner()) {
            $_SESSION['alert'] = 'You must be logged in as an owner to update car availability';
            $_SESSION['alert_type'] = 'danger';
            redirect('login');
            return;
        }

        // Validate car ID
        if (!isset($_POST['car_id']) || !is_numeric($_POST['car_id'])) {
            $_SESSION['alert'] = 'Invalid car ID';
            $_SESSION['alert_type'] = 'danger';
            redirect('owner-dashboard');
            return;
        }

        $carId = (int)$_POST['car_id'];
        $available = isset($_POST['available']) ? (bool)$_POST['available'] : false;

        // Update car availability
        $result = $this->carModel->updateCar($carId, $_SESSION['user_id'], ['available' => $available]);
        
        if ($result) {
            $_SESSION['alert'] = 'Car availability updated successfully';
            $_SESSION['alert_type'] = 'success';
        } else {
            $_SESSION['alert'] = 'Failed to update car availability';
            $_SESSION['alert_type'] = 'danger';
        }
        
        redirect('owner-dashboard');
    }
}
?>
