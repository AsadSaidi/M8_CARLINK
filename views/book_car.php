<?php
require_once 'models/Car.php';
require_once 'models/Reservation.php';

// Check if user is logged in
if (!isLoggedIn()) {
    $_SESSION['alert'] = 'Please log in to book a car';
    $_SESSION['alert_type'] = 'warning';
    redirect('login');
}

// Check if owner is trying to book their own car
if (isOwner()) {
    $userType = 'owner';
} else {
    $userType = 'renter';
}

// Validate car ID and dates
if (!isset($_GET['car_id']) || !is_numeric($_GET['car_id']) || 
    !isset($_GET['start_date']) || !isset($_GET['end_date'])) {
    $_SESSION['alert'] = 'Invalid booking parameters';
    $_SESSION['alert_type'] = 'danger';
    redirect('explore');
}

$carId = (int) $_GET['car_id'];
$startDate = $_GET['start_date'];
$endDate = $_GET['end_date'];

// Validate dates
$today = date('Y-m-d');
if ($startDate < $today || $endDate < $today || $endDate < $startDate) {
    $_SESSION['alert'] = 'Invalid booking dates';
    $_SESSION['alert_type'] = 'danger';
    redirect('car&id=' . $carId);
}

// Get car details
$carModel = new Car($db);
$car = $carModel->getCarById($carId);

if (!$car) {
    $_SESSION['alert'] = 'Car not found';
    $_SESSION['alert_type'] = 'danger';
    redirect('explore');
}

// Check if owner is trying to book their own car
if ($car['owner_id'] == $_SESSION['user_id']) {
    $_SESSION['alert'] = 'You cannot book your own car';
    $_SESSION['alert_type'] = 'warning';
    redirect('car&id=' . $carId);
}

// Check availability
$isAvailable = $carModel->checkAvailability($carId, $startDate, $endDate);
if (!$isAvailable) {
    $_SESSION['alert'] = 'This car is not available for the selected dates';
    $_SESSION['alert_type'] = 'danger';
    redirect('car&id=' . $carId);
}

// Calculate price
$reservationModel = new Reservation($db);
$priceCalculation = $reservationModel->calculatePrice($carId, $startDate, $endDate);

if (!$priceCalculation['success']) {
    $_SESSION['alert'] = $priceCalculation['message'];
    $_SESSION['alert_type'] = 'danger';
    redirect('car&id=' . $carId);
}

$pageTitle = "Book " . $car['make'] . ' ' . $car['model'];
$extraScripts = '<script src="' . APP_URL . '/assets/js/payment.js"></script>';
?>

<?php include 'views/header.php'; ?>

<div class="row justify-content-center">
    <div class="col-lg-8">
        <div class="mb-4">
            <nav aria-label="breadcrumb">
                <ol class="breadcrumb">
                    <li class="breadcrumb-item"><a href="<?= APP_URL ?>">Home</a></li>
                    <li class="breadcrumb-item"><a href="<?= APP_URL ?>/?route=explore">Explore Cars</a></li>
                    <li class="breadcrumb-item"><a href="<?= APP_URL ?>/?route=car&id=<?= $carId ?>"><?= htmlspecialchars($car['make'] . ' ' . $car['model']) ?></a></li>
                    <li class="breadcrumb-item active" aria-current="page">Book</li>
                </ol>
            </nav>
        </div>
        
        <div class="row">
            <!-- Booking Summary -->
            <div class="col-md-6 mb-4">
                <div class="card shadow-sm h-100">
                    <div class="card-header bg-primary text-white">
                        <h5 class="mb-0">Booking Summary</h5>
                    </div>
                    <div class="card-body">
                        <div class="d-flex align-items-center mb-4">
                            <?php if (!empty($car['images'][0]['image_path'])): ?>
                                <img src="<?= htmlspecialchars($car['images'][0]['image_path']) ?>" alt="<?= htmlspecialchars($car['make'] . ' ' . $car['model']) ?>" class="me-3 rounded" style="width: 80px; height: 60px; object-fit: cover;">
                            <?php else: ?>
                                <div class="me-3 rounded bg-light d-flex align-items-center justify-content-center" style="width: 80px; height: 60px;">
                                    <i class="fas fa-car fa-2x text-muted"></i>
                                </div>
                            <?php endif; ?>
                            <div>
                                <h5 class="mb-0"><?= htmlspecialchars($car['make'] . ' ' . $car['model'] . ' (' . $car['year'] . ')') ?></h5>
                                <p class="text-muted mb-0">
                                    <i class="fas fa-map-marker-alt me-1"></i><?= htmlspecialchars($car['location']) ?>
                                </p>
                            </div>
                        </div>
                        
                        <h6 class="mb-3">Trip Details</h6>
                        <div class="row mb-3">
                            <div class="col-6">
                                <div class="d-flex flex-column">
                                    <small class="text-muted">Start Date</small>
                                    <strong><?= formatDate($startDate) ?></strong>
                                </div>
                            </div>
                            <div class="col-6">
                                <div class="d-flex flex-column">
                                    <small class="text-muted">End Date</small>
                                    <strong><?= formatDate($endDate) ?></strong>
                                </div>
                            </div>
                        </div>
                        
                        <h6 class="mb-3">Price Breakdown</h6>
                        <div class="d-flex justify-content-between mb-2">
                            <span><?= $priceCalculation['days'] ?> days × <?= formatPrice($priceCalculation['price_per_day']) ?></span>
                            <span><?= formatPrice($priceCalculation['subtotal']) ?></span>
                        </div>
                        
                        <?php if ($priceCalculation['discount_rate'] > 0): ?>
                            <div class="d-flex justify-content-between text-success mb-2">
                                <span>Discount (<?= $priceCalculation['discount_rate'] * 100 ?>%)</span>
                                <span>-<?= formatPrice($priceCalculation['discount']) ?></span>
                            </div>
                        <?php endif; ?>
                        
                        <hr>
                        <div class="d-flex justify-content-between fw-bold">
                            <span>Total</span>
                            <span><?= formatPrice($priceCalculation['total_price']) ?></span>
                        </div>
                        
                        <div class="alert alert-info mt-4 mb-0">
                            <i class="fas fa-info-circle me-2"></i>You will only be charged once the owner approves your booking request.
                        </div>
                    </div>
                </div>
            </div>
            
            <!-- Payment Form -->
            <div class="col-md-6 mb-4">
                <div class="card shadow-sm">
                    <div class="card-header bg-primary text-white">
                        <h5 class="mb-0">Payment Information</h5>
                    </div>
                    <div class="card-body">
                        <form id="payment-form" action="<?= APP_URL ?>/?route=process-reservation" method="POST">
                            <input type="hidden" name="csrf_token" value="<?= generateCSRFToken() ?>">
                            <input type="hidden" name="car_id" value="<?= $carId ?>">
                            <input type="hidden" name="start_date" value="<?= htmlspecialchars($startDate) ?>">
                            <input type="hidden" name="end_date" value="<?= htmlspecialchars($endDate) ?>">
                            <input type="hidden" name="total_price" value="<?= $priceCalculation['total_price'] ?>">
                            
                            <h6 class="mb-3">Card Details</h6>
                            <div class="mb-3">
                                <label for="card_name" class="form-label">Name on Card</label>
                                <input type="text" class="form-control" id="card_name" name="card_name" required>
                            </div>
                            
                            <div class="mb-3">
                                <label for="card_number" class="form-label">Card Number</label>
                                <div class="input-group">
                                    <span class="input-group-text"><i class="fas fa-credit-card"></i></span>
                                    <input type="text" class="form-control" id="card_number" name="card_number" 
                                           placeholder="1234 5678 9012 3456" required
                                           pattern="[0-9]{13,19}" maxlength="19">
                                </div>
                                <div class="invalid-feedback">
                                    Please enter a valid card number.
                                </div>
                            </div>
                            
                            <div class="row mb-3">
                                <div class="col-md-6 mb-3 mb-md-0">
                                    <label for="card_expiry" class="form-label">Expiry Date</label>
                                    <input type="text" class="form-control" id="card_expiry" name="card_expiry" 
                                           placeholder="MM/YY" required pattern="(0[1-9]|1[0-2])\/[0-9]{2}" maxlength="5">
                                    <div class="invalid-feedback">
                                        Please enter a valid expiry date (MM/YY).
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <label for="card_cvv" class="form-label">CVV</label>
                                    <input type="text" class="form-control" id="card_cvv" name="card_cvv" 
                                           placeholder="123" required pattern="[0-9]{3,4}" maxlength="4">
                                    <div class="invalid-feedback">
                                        Please enter a valid CVV.
                                    </div>
                                </div>
                            </div>
                            
                            <h6 class="mb-3 mt-4">Billing Address</h6>
                            <div class="mb-3">
                                <label for="billing_address" class="form-label">Address</label>
                                <input type="text" class="form-control" id="billing_address" name="billing_address" required>
                            </div>
                            
                            <div class="row mb-3">
                                <div class="col-md-6 mb-3 mb-md-0">
                                    <label for="billing_city" class="form-label">City</label>
                                    <input type="text" class="form-control" id="billing_city" name="billing_city" required>
                                </div>
                                <div class="col-md-6">
                                    <label for="billing_zip" class="form-label">ZIP Code</label>
                                    <input type="text" class="form-control" id="billing_zip" name="billing_zip" required>
                                </div>
                            </div>
                            
                            <div class="mb-4">
                                <label for="billing_country" class="form-label">Country</label>
                                <select class="form-select" id="billing_country" name="billing_country" required>
                                    <option value="">Select Country</option>
                                    <option value="US">United States</option>
                                    <option value="CA">Canada</option>
                                    <option value="GB">United Kingdom</option>
                                    <option value="AU">Australia</option>
                                    <option value="DE">Germany</option>
                                    <option value="FR">France</option>
                                    <option value="ES">Spain</option>
                                    <option value="IT">Italy</option>
                                    <option value="NL">Netherlands</option>
                                    <option value="BE">Belgium</option>
                                    <option value="AT">Austria</option>
                                    <option value="CH">Switzerland</option>
                                    <option value="SE">Sweden</option>
                                    <option value="NO">Norway</option>
                                    <option value="DK">Denmark</option>
                                    <option value="FI">Finland</option>
                                    <option value="PT">Portugal</option>
                                </select>
                            </div>
                            
                            <div class="d-grid gap-2">
                                <button type="submit" class="btn btn-success btn-lg">Complete Booking (<?= formatPrice($priceCalculation['total_price']) ?>)</button>
                                <a href="<?= APP_URL ?>/?route=car&id=<?= $carId ?>" class="btn btn-outline-secondary">Cancel</a>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
        
        <!-- Booking Terms & Conditions -->
        <div class="card shadow-sm mb-4">
            <div class="card-header">
                <h5 class="mb-0">Booking Terms & Conditions</h5>
            </div>
            <div class="card-body">
                <p class="small text-muted">By completing this booking, you agree to the following terms:</p>
                <ul class="small text-muted">
                    <li>You must present a valid driver's license and credit card at pickup.</li>
                    <li>You will be responsible for any damage to the vehicle during your rental period.</li>
                    <li>The owner has the right to approve or reject your booking request.</li>
                    <li>Cancellation policy: Free cancellation up to 24 hours before pickup. After that, a 30% cancellation fee applies.</li>
                    <li>Late returns will be charged at 1.5x the daily rate, prorated hourly.</li>
                    <li>You must return the car with the same fuel level as at pickup.</li>
                </ul>
            </div>
        </div>
    </div>
</div>

<?php include 'views/footer.php'; ?>
