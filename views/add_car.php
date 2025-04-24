<?php
require_once 'models/Car.php';
require_once 'api/CarApi.php';

// Ensure user is logged in and is an owner
if (!isLoggedIn() || !isOwner()) {
    $_SESSION['alert'] = 'Access denied';
    $_SESSION['alert_type'] = 'danger';
    redirect('');
}

$pageTitle = "Add a Car";
$isEdit = false;
$car = null;

// Check if we're editing an existing car
if (isset($_GET['edit']) && is_numeric($_GET['edit'])) {
    $carId = (int) $_GET['edit'];
    $carModel = new Car($db);
    $car = $carModel->getCarById($carId);
    
    // Verify ownership
    if ($car && $car['owner_id'] == $_SESSION['user_id']) {
        $pageTitle = "Edit Car";
        $isEdit = true;
    } else {
        $_SESSION['alert'] = 'Car not found or you don\'t have permission to edit it';
        $_SESSION['alert_type'] = 'danger';
        redirect('owner-dashboard');
    }
}

// Get car makes from API
$carApi = new CarApi();
$makes = $carApi->getMakes();

$extraScripts = '
<script src="' . APP_URL . '/assets/js/validation.js"></script>
<script src="' . APP_URL . '/assets/js/car-form.js"></script>
';
?>

<?php include 'views/header.php'; ?>

<div class="row">
    <div class="col-lg-3 mb-4">
        <div class="card shadow-sm mb-4">
            <div class="card-header bg-primary text-white">
                <h5 class="mb-0">
                    <i class="fas fa-car me-2"></i><?= $isEdit ? 'Edit Car' : 'Add a Car' ?>
                </h5>
            </div>
            <div class="card-body p-0">
                <div class="list-group list-group-flush">
                    <a href="<?= APP_URL ?>/?route=owner-dashboard" class="list-group-item list-group-item-action">
                        <i class="fas fa-home me-2"></i>Dashboard
                    </a>
                    <a href="<?= APP_URL ?>/?route=add-car" class="list-group-item list-group-item-action active">
                        <i class="fas fa-plus-circle me-2"></i><?= $isEdit ? 'Edit Car' : 'Add New Car' ?>
                    </a>
                    <a href="<?= APP_URL ?>/?route=manage-reservations" class="list-group-item list-group-item-action">
                        <i class="fas fa-calendar-check me-2"></i>Manage Reservations
                    </a>
                </div>
            </div>
        </div>
        
        <div class="card shadow-sm">
            <div class="card-header">
                <h5 class="mb-0">Tips for Listing</h5>
            </div>
            <div class="card-body">
                <ul class="list-unstyled mb-0">
                    <li class="mb-2">
                        <i class="fas fa-check-circle text-success me-2"></i>
                        <strong>Clear Photos</strong>: Upload high-quality images of your car from multiple angles.
                    </li>
                    <li class="mb-2">
                        <i class="fas fa-check-circle text-success me-2"></i>
                        <strong>Detailed Description</strong>: Highlight special features or benefits of your car.
                    </li>
                    <li class="mb-2">
                        <i class="fas fa-check-circle text-success me-2"></i>
                        <strong>Accurate Information</strong>: Ensure all specifications and details are correct.
                    </li>
                    <li class="mb-2">
                        <i class="fas fa-check-circle text-success me-2"></i>
                        <strong>Competitive Pricing</strong>: Research similar cars to set an attractive rate.
                    </li>
                    <li>
                        <i class="fas fa-check-circle text-success me-2"></i>
                        <strong>Quick Response</strong>: Reply promptly to booking requests.
                    </li>
                </ul>
            </div>
        </div>
    </div>
    
    <div class="col-lg-9">
        <div class="card shadow-sm">
            <div class="card-body">
                <form id="car-form" action="<?= APP_URL ?>/?route=process-add-car" method="POST" enctype="multipart/form-data" class="needs-validation" novalidate>
                    <input type="hidden" name="csrf_token" value="<?= generateCSRFToken() ?>">
                    <?php if ($isEdit): ?>
                        <input type="hidden" name="car_id" value="<?= $car['id'] ?>">
                    <?php endif; ?>
                    
                    <h5 class="mb-4">Vehicle Information</h5>
                    
                    <div class="row mb-3">
                        <div class="col-md-4 mb-3 mb-md-0">
                            <label for="make" class="form-label">Make <span class="text-danger">*</span></label>
                            <select class="form-select" id="make" name="make" required <?= $isEdit ? 'disabled' : '' ?>>
                                <option value="">Select Make</option>
                                <?php foreach ($makes as $make): ?>
                                    <option value="<?= htmlspecialchars($make) ?>" <?= $isEdit && $car['make'] === $make ? 'selected' : '' ?>>
                                        <?= htmlspecialchars($make) ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                            <?php if ($isEdit): ?>
                                <input type="hidden" name="make" value="<?= htmlspecialchars($car['make']) ?>">
                            <?php endif; ?>
                            <div class="invalid-feedback">
                                Please select a make.
                            </div>
                        </div>
                        
                        <div class="col-md-4 mb-3 mb-md-0">
                            <label for="model" class="form-label">Model <span class="text-danger">*</span></label>
                            <select class="form-select" id="model" name="model" required <?= $isEdit ? 'disabled' : '' ?>>
                                <option value="">Select Model</option>
                                <?php if ($isEdit): ?>
                                    <option value="<?= htmlspecialchars($car['model']) ?>" selected>
                                        <?= htmlspecialchars($car['model']) ?>
                                    </option>
                                <?php endif; ?>
                            </select>
                            <?php if ($isEdit): ?>
                                <input type="hidden" name="model" value="<?= htmlspecialchars($car['model']) ?>">
                            <?php endif; ?>
                            <div class="invalid-feedback">
                                Please select a model.
                            </div>
                        </div>
                        
                        <div class="col-md-4">
                            <label for="year" class="form-label">Year <span class="text-danger">*</span></label>
                            <input type="number" class="form-control" id="year" name="year" min="1950" max="<?= date('Y') + 1 ?>" 
                                  value="<?= $isEdit ? htmlspecialchars($car['year']) : '' ?>" required <?= $isEdit ? 'readonly' : '' ?>>
                            <?php if ($isEdit): ?>
                                <input type="hidden" name="year" value="<?= htmlspecialchars($car['year']) ?>">
                            <?php endif; ?>
                            <div class="invalid-feedback">
                                Please enter a valid year.
                            </div>
                        </div>
                    </div>
                    
                    <div class="row mb-4">
                        <div class="col-md-4 mb-3 mb-md-0">
                            <label for="fuel_type" class="form-label">Fuel Type <span class="text-danger">*</span></label>
                            <select class="form-select" id="fuel_type" name="fuel_type" required>
                                <option value="">Select Fuel Type</option>
                                <option value="gasoline" <?= $isEdit && $car['fuel_type'] === 'gasoline' ? 'selected' : '' ?>>Gasoline</option>
                                <option value="diesel" <?= $isEdit && $car['fuel_type'] === 'diesel' ? 'selected' : '' ?>>Diesel</option>
                                <option value="electric" <?= $isEdit && $car['fuel_type'] === 'electric' ? 'selected' : '' ?>>Electric</option>
                                <option value="hybrid" <?= $isEdit && $car['fuel_type'] === 'hybrid' ? 'selected' : '' ?>>Hybrid</option>
                            </select>
                            <div class="invalid-feedback">
                                Please select a fuel type.
                            </div>
                        </div>
                        
                        <div class="col-md-4 mb-3 mb-md-0">
                            <label for="transmission" class="form-label">Transmission <span class="text-danger">*</span></label>
                            <select class="form-select" id="transmission" name="transmission" required>
                                <option value="">Select Transmission</option>
                                <option value="automatic" <?= $isEdit && $car['transmission'] === 'automatic' ? 'selected' : '' ?>>Automatic</option>
                                <option value="manual" <?= $isEdit && $car['transmission'] === 'manual' ? 'selected' : '' ?>>Manual</option>
                            </select>
                            <div class="invalid-feedback">
                                Please select a transmission type.
                            </div>
                        </div>
                        
                        <div class="col-md-4">
                            <label for="cylinders" class="form-label">Cylinders</label>
                            <input type="number" class="form-control" id="cylinders" name="cylinders" min="0" max="16" 
                                   value="<?= $isEdit && isset($car['cylinders']) ? htmlspecialchars($car['cylinders']) : '' ?>">
                        </div>
                    </div>
                    
                    <div class="row mb-4">
                        <div class="col-md-4 mb-3 mb-md-0">
                            <label for="displacement" class="form-label">Displacement (L)</label>
                            <input type="number" class="form-control" id="displacement" name="displacement" min="0" step="0.1" 
                                   value="<?= $isEdit && isset($car['displacement']) ? htmlspecialchars($car['displacement']) : '' ?>">
                        </div>
                        
                        <div class="col-md-4 mb-3 mb-md-0">
                            <label for="power" class="form-label">Power (HP)</label>
                            <input type="number" class="form-control" id="power" name="power" min="0" 
                                   value="<?= $isEdit && isset($car['power']) ? htmlspecialchars($car['power']) : '' ?>">
                        </div>
                        
                        <div class="col-md-4">
                            <label for="city_mpg" class="form-label">City MPG</label>
                            <input type="number" class="form-control" id="city_mpg" name="city_mpg" min="0" step="0.1" 
                                   value="<?= $isEdit && isset($car['city_mpg']) ? htmlspecialchars($car['city_mpg']) : '' ?>">
                        </div>
                    </div>
                    
                    <div class="row mb-4">
                        <div class="col-md-6 mb-3 mb-md-0">
                            <label for="highway_mpg" class="form-label">Highway MPG</label>
                            <input type="number" class="form-control" id="highway_mpg" name="highway_mpg" min="0" step="0.1" 
                                   value="<?= $isEdit && isset($car['highway_mpg']) ? htmlspecialchars($car['highway_mpg']) : '' ?>">
                        </div>
                        
                        <div class="col-md-6">
                            <label for="price_per_day" class="form-label">Price per Day (€) <span class="text-danger">*</span></label>
                            <div class="input-group">
                                <span class="input-group-text">€</span>
                                <input type="number" class="form-control" id="price_per_day" name="price_per_day" min="1" step="0.01" 
                                       value="<?= $isEdit ? htmlspecialchars($car['price_per_day']) : '' ?>" required>
                            </div>
                            <div class="invalid-feedback">
                                Please enter a valid price.
                            </div>
                        </div>
                    </div>
                    
                    <h5 class="mb-4 mt-5">Location & Details</h5>
                    
                    <div class="row mb-4">
                        <div class="col-md-12">
                            <label for="location" class="form-label">Location <span class="text-danger">*</span></label>
                            <input type="text" class="form-control" id="location" name="location" required 
                                   placeholder="City, Address" value="<?= $isEdit ? htmlspecialchars($car['location']) : '' ?>">
                            <div class="invalid-feedback">
                                Please enter a location.
                            </div>
                        </div>
                    </div>
                    
                    <div class="mb-4">
                        <label for="description" class="form-label">Description</label>
                        <textarea class="form-control" id="description" name="description" rows="4" 
                                  placeholder="Describe your car, special features, etc."><?= $isEdit && !empty($car['description']) ? htmlspecialchars($car['description']) : '' ?></textarea>
                    </div>
                    
                    <div class="mb-4">
                        <label for="available" class="form-label">Availability</label>
                        <div class="form-check form-switch">
                            <input class="form-check-input" type="checkbox" role="switch" id="available" name="available" value="1" 
                                  <?= !$isEdit || ($isEdit && $car['available']) ? 'checked' : '' ?>>
                            <label class="form-check-label" for="available">
                                Car is available for rent
                            </label>
                        </div>
                    </div>
                    
                    <h5 class="mb-4 mt-5">Car Images</h5>
                    
                    <?php if ($isEdit && !empty($car['images'])): ?>
                        <div class="mb-4">
                            <label class="form-label">Current Images</label>
                            <div class="row">
                                <?php foreach ($car['images'] as $image): ?>
                                    <div class="col-md-3 mb-3">
                                        <div class="card">
                                            <img src="<?= htmlspecialchars($image['image_path']) ?>" class="card-img-top" alt="Car Image" style="height: 120px; object-fit: cover;">
                                            <div class="card-body p-2 text-center">
                                                <div class="form-check d-inline-block">
                                                    <input class="form-check-input" type="radio" name="primary_image" id="primary_<?= $image['id'] ?>" 
                                                           value="<?= $image['id'] ?>" <?= $image['is_primary'] ? 'checked' : '' ?>>
                                                    <label class="form-check-label" for="primary_<?= $image['id'] ?>">
                                                        Primary
                                                    </label>
                                                </div>
                                                <div class="form-check d-inline-block ms-2">
                                                    <input class="form-check-input" type="checkbox" name="remove_images[]" id="remove_<?= $image['id'] ?>" 
                                                           value="<?= $image['id'] ?>">
                                                    <label class="form-check-label" for="remove_<?= $image['id'] ?>">
                                                        Remove
                                                    </label>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                <?php endforeach; ?>
                            </div>
                        </div>
                    <?php endif; ?>
                    
                    <div class="mb-4">
                        <label for="car_images" class="form-label"><?= $isEdit ? 'Add New Images' : 'Upload Images' ?> (Max 5 files, 5MB each)</label>
                        <input type="file" class="form-control" id="car_images" name="car_images[]" multiple accept="image/*">
                        <div class="form-text">
                            Please upload clear photos of your car. First image will be used as the main image.
                        </div>
                    </div>
                    
                    <div class="d-flex justify-content-between mt-5">
                        <a href="<?= APP_URL ?>/?route=owner-dashboard" class="btn btn-outline-secondary">
                            <i class="fas fa-arrow-left me-2"></i>Back to Dashboard
                        </a>
                        <button type="submit" class="btn btn-primary btn-lg">
                            <i class="fas fa-save me-2"></i><?= $isEdit ? 'Update Car' : 'Add Car' ?>
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

<?php include 'views/footer.php'; ?>
