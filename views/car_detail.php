<?php
require_once 'models/Car.php';
require_once 'models/Reservation.php';

// Validate car ID
if (!isset($_GET['id']) || !is_numeric($_GET['id'])) {
    $_SESSION['alert'] = 'Invalid car ID';
    $_SESSION['alert_type'] = 'danger';
    redirect('explore');
}

$carId = (int) $_GET['id'];
$carModel = new Car($db);
$car = $carModel->getCarById($carId);

if (!$car) {
    $_SESSION['alert'] = 'Car not found';
    $_SESSION['alert_type'] = 'danger';
    redirect('explore');
}

$pageTitle = $car['make'] . ' ' . $car['model'];

// Get date params if coming from search
$startDate = $_GET['start_date'] ?? '';
$endDate = $_GET['end_date'] ?? '';

// Check if car is available for the selected dates
$isAvailable = true;
$priceCalculation = null;

if (!empty($startDate) && !empty($endDate)) {
    $reservationModel = new Reservation($db);
    $isAvailable = $carModel->checkAvailability($carId, $startDate, $endDate);
    
    if ($isAvailable) {
        $priceCalculation = $reservationModel->calculatePrice($carId, $startDate, $endDate);
    }
}

$extraScripts = '<script src="' . APP_URL . '/assets/js/reservation.js"></script>';
?>

<?php include 'views/header.php'; ?>

<div class="mb-4">
    <nav aria-label="breadcrumb">
        <ol class="breadcrumb">
            <li class="breadcrumb-item"><a href="<?= APP_URL ?>">Home</a></li>
            <li class="breadcrumb-item"><a href="<?= APP_URL ?>/?route=explore">Explore Cars</a></li>
            <li class="breadcrumb-item active" aria-current="page"><?= htmlspecialchars($car['make'] . ' ' . $car['model']) ?></li>
        </ol>
    </nav>
</div>

<div class="row">
    <!-- Car Images and Details -->
    <div class="col-lg-8 mb-4 mb-lg-0">
        <div class="card shadow-sm mb-4">
            <!-- Car Image Gallery -->
            <div class="car-gallery">
                <?php if (empty($car['images'])): ?>
                    <div class="bg-light text-center py-5">
                        <i class="fas fa-car fa-5x text-muted"></i>
                        <p class="mt-3 mb-0 text-muted">No images available</p>
                    </div>
                <?php else: ?>
                    <div id="carImageCarousel" class="carousel slide" data-bs-ride="carousel">
                        <div class="carousel-indicators">
                            <?php foreach ($car['images'] as $index => $image): ?>
                                <button type="button" data-bs-target="#carImageCarousel" data-bs-slide-to="<?= $index ?>" 
                                        class="<?= $index === 0 ? 'active' : '' ?>" aria-current="<?= $index === 0 ? 'true' : 'false' ?>" 
                                        aria-label="Slide <?= $index + 1 ?>"></button>
                            <?php endforeach; ?>
                        </div>
                        <div class="carousel-inner">
                            <?php foreach ($car['images'] as $index => $image): ?>
                                <div class="carousel-item <?= $index === 0 ? 'active' : '' ?>">
                                    <img src="<?= htmlspecialchars($image['image_path']) ?>" class="d-block w-100" alt="<?= htmlspecialchars($car['make'] . ' ' . $car['model']) ?>" style="height: 400px; object-fit: cover;">
                                </div>
                            <?php endforeach; ?>
                        </div>
                        <button class="carousel-control-prev" type="button" data-bs-target="#carImageCarousel" data-bs-slide="prev">
                            <span class="carousel-control-prev-icon" aria-hidden="true"></span>
                            <span class="visually-hidden">Previous</span>
                        </button>
                        <button class="carousel-control-next" type="button" data-bs-target="#carImageCarousel" data-bs-slide="next">
                            <span class="carousel-control-next-icon" aria-hidden="true"></span>
                            <span class="visually-hidden">Next</span>
                        </button>
                    </div>
                <?php endif; ?>
            </div>
            
            <!-- Car Details -->
            <div class="card-body">
                <h2 class="card-title mb-3"><?= htmlspecialchars($car['make'] . ' ' . $car['model'] . ' (' . $car['year'] . ')') ?></h2>
                
                <div class="mb-4">
                    <span class="badge bg-success"><?= formatPrice($car['price_per_day']) ?>/day</span>
                    <span class="badge bg-light text-dark me-2">
                        <i class="fas fa-gas-pump me-1"></i><?= htmlspecialchars(ucfirst($car['fuel_type'])) ?>
                    </span>
                    <span class="badge bg-light text-dark me-2">
                        <i class="fas fa-cog me-1"></i><?= htmlspecialchars(ucfirst($car['transmission'])) ?>
                    </span>
                    <span class="badge bg-light text-dark">
                        <i class="fas fa-map-marker-alt me-1"></i><?= htmlspecialchars($car['location']) ?>
                    </span>
                </div>
                
                <h5 class="mb-3">Description</h5>
                <p><?= !empty($car['description']) ? nl2br(htmlspecialchars($car['description'])) : 'No description provided.' ?></p>
                
                <h5 class="mb-3 mt-4">Car Specifications</h5>
                <div class="row">
                    <div class="col-md-6">
                        <ul class="list-group list-group-flush">
                            <li class="list-group-item d-flex justify-content-between">
                                <span>Make:</span>
                                <strong><?= htmlspecialchars($car['make']) ?></strong>
                            </li>
                            <li class="list-group-item d-flex justify-content-between">
                                <span>Model:</span>
                                <strong><?= htmlspecialchars($car['model']) ?></strong>
                            </li>
                            <li class="list-group-item d-flex justify-content-between">
                                <span>Year:</span>
                                <strong><?= htmlspecialchars($car['year']) ?></strong>
                            </li>
                            <li class="list-group-item d-flex justify-content-between">
                                <span>Transmission:</span>
                                <strong><?= htmlspecialchars(ucfirst($car['transmission'])) ?></strong>
                            </li>
                        </ul>
                    </div>
                    <div class="col-md-6">
                        <ul class="list-group list-group-flush">
                            <li class="list-group-item d-flex justify-content-between">
                                <span>Fuel Type:</span>
                                <strong><?= htmlspecialchars(ucfirst($car['fuel_type'])) ?></strong>
                            </li>
                            <?php if (!empty($car['cylinders'])): ?>
                                <li class="list-group-item d-flex justify-content-between">
                                    <span>Cylinders:</span>
                                    <strong><?= htmlspecialchars($car['cylinders']) ?></strong>
                                </li>
                            <?php endif; ?>
                            <?php if (!empty($car['power'])): ?>
                                <li class="list-group-item d-flex justify-content-between">
                                    <span>Power:</span>
                                    <strong><?= htmlspecialchars($car['power']) ?> hp</strong>
                                </li>
                            <?php endif; ?>
                            <?php if (!empty($car['displacement'])): ?>
                                <li class="list-group-item d-flex justify-content-between">
                                    <span>Displacement:</span>
                                    <strong><?= htmlspecialchars($car['displacement']) ?> L</strong>
                                </li>
                            <?php endif; ?>
                        </ul>
                    </div>
                </div>
                
                <?php if (!empty($car['city_mpg']) || !empty($car['highway_mpg'])): ?>
                    <h5 class="mb-3 mt-4">Fuel Economy</h5>
                    <div class="row">
                        <div class="col-md-6">
                            <ul class="list-group list-group-flush">
                                <?php if (!empty($car['city_mpg'])): ?>
                                    <li class="list-group-item d-flex justify-content-between">
                                        <span>City MPG:</span>
                                        <strong><?= htmlspecialchars($car['city_mpg']) ?> mpg</strong>
                                    </li>
                                <?php endif; ?>
                            </ul>
                        </div>
                        <div class="col-md-6">
                            <ul class="list-group list-group-flush">
                                <?php if (!empty($car['highway_mpg'])): ?>
                                    <li class="list-group-item d-flex justify-content-between">
                                        <span>Highway MPG:</span>
                                        <strong><?= htmlspecialchars($car['highway_mpg']) ?> mpg</strong>
                                    </li>
                                <?php endif; ?>
                            </ul>
                        </div>
                    </div>
                <?php endif; ?>
            </div>
        </div>
        
        <!-- Owner Info -->
        <div class="card shadow-sm">
            <div class="card-header">
                <h5 class="mb-0">About the Owner</h5>
            </div>
            <div class="card-body">
                <div class="d-flex align-items-center">
                    <div class="rounded-circle bg-primary text-white d-flex align-items-center justify-content-center me-3" style="width: 50px; height: 50px;">
                        <i class="fas fa-user"></i>
                    </div>
                    <div>
                        <h5 class="mb-1"><?= htmlspecialchars($car['owner_name']) ?></h5>
                        <p class="text-muted mb-0">
                            <i class="fas fa-envelope me-2"></i><?= htmlspecialchars($car['owner_email']) ?>
                            <?php if (!empty($car['owner_phone'])): ?>
                                <br><i class="fas fa-phone me-2"></i><?= htmlspecialchars($car['owner_phone']) ?>
                            <?php endif; ?>
                        </p>
                    </div>
                </div>
            </div>
        </div>
    </div>
    
    <!-- Booking Section -->
    <div class="col-lg-4">
        <div class="card shadow-sm mb-4">
            <div class="card-header bg-primary text-white">
                <h5 class="mb-0">Book This Car</h5>
            </div>
            <div class="card-body">
                <!-- Booking Form -->
                <form id="bookingDateForm" action="<?= APP_URL ?>/?route=book" method="GET">
                    <input type="hidden" name="route" value="book">
                    <input type="hidden" name="car_id" value="<?= $carId ?>">
                    
                    <div class="mb-3">
                        <label for="booking_start_date" class="form-label">Start Date</label>
                        <input type="date" class="form-control" id="booking_start_date" name="start_date" 
                               value="<?= htmlspecialchars($startDate) ?>" min="<?= date('Y-m-d') ?>" required>
                    </div>
                    
                    <div class="mb-3">
                        <label for="booking_end_date" class="form-label">End Date</label>
                        <input type="date" class="form-control" id="booking_end_date" name="end_date" 
                               value="<?= htmlspecialchars($endDate) ?>" min="<?= date('Y-m-d') ?>" required>
                    </div>
                    
                    <?php if (!empty($priceCalculation) && $priceCalculation['success']): ?>
                        <!-- Price Preview -->
                        <div class="alert alert-info" id="price-preview">
                            <h6 class="alert-heading">Price Estimate</h6>
                            <div class="d-flex justify-content-between">
                                <span><?= $priceCalculation['days'] ?> days × <?= formatPrice($priceCalculation['price_per_day']) ?></span>
                                <span><?= formatPrice($priceCalculation['subtotal']) ?></span>
                            </div>
                            
                            <?php if ($priceCalculation['discount_rate'] > 0): ?>
                                <div class="d-flex justify-content-between text-success">
                                    <span>Discount (<?= $priceCalculation['discount_rate'] * 100 ?>%)</span>
                                    <span>-<?= formatPrice($priceCalculation['discount']) ?></span>
                                </div>
                            <?php endif; ?>
                            
                            <hr>
                            <div class="d-flex justify-content-between fw-bold">
                                <span>Total</span>
                                <span><?= formatPrice($priceCalculation['total_price']) ?></span>
                            </div>
                        </div>
                    <?php endif; ?>
                    
                    <?php if (isLoggedIn() && isOwner() && $car['owner_id'] == $_SESSION['user_id']): ?>
                        <div class="alert alert-warning">
                            <i class="fas fa-info-circle me-2"></i>You cannot book your own car.
                        </div>
                    <?php elseif (!$isAvailable && !empty($startDate) && !empty($endDate)): ?>
                        <div class="alert alert-danger">
                            <i class="fas fa-exclamation-circle me-2"></i>This car is not available for the selected dates.
                        </div>
                    <?php else: ?>
                        <div class="d-grid gap-2">
                            <?php if (!isLoggedIn()): ?>
                                <a href="<?= APP_URL ?>/?route=login" class="btn btn-primary">Login to Book This Car</a>
                            <?php elseif (empty($startDate) || empty($endDate)): ?>
                                <button type="submit" class="btn btn-primary">Check Availability</button>
                            <?php else: ?>
                                <button type="submit" class="btn btn-success">Proceed to Booking</button>
                            <?php endif; ?>
                        </div>
                    <?php endif; ?>
                </form>
            </div>
        </div>
        
        <!-- Price Info Card -->
        <div class="card shadow-sm mb-4">
            <div class="card-header">
                <h5 class="mb-0">Price Information</h5>
            </div>
            <div class="card-body">
                <div class="d-flex justify-content-between mb-2">
                    <span>Base Price:</span>
                    <strong><?= formatPrice($car['price_per_day']) ?>/day</strong>
                </div>
                
                <hr>
                
                <h6 class="mb-2">Discount Policy</h6>
                <ul class="list-unstyled text-muted small">
                    <li><i class="fas fa-check-circle text-success me-2"></i>7-13 days: 5% discount</li>
                    <li><i class="fas fa-check-circle text-success me-2"></i>14-29 days: 10% discount</li>
                    <li><i class="fas fa-check-circle text-success me-2"></i>30+ days: 15% discount</li>
                </ul>
            </div>
        </div>
        
        <!-- Location Info -->
        <div class="card shadow-sm">
            <div class="card-header">
                <h5 class="mb-0">Location</h5>
            </div>
            <div class="card-body">
                <p class="mb-2"><i class="fas fa-map-marker-alt me-2 text-primary"></i><?= htmlspecialchars($car['location']) ?></p>
                <div class="location-map bg-light text-center py-3">
                    <i class="fas fa-map fa-3x text-muted"></i>
                    <p class="mt-2 mb-0 small text-muted">Location map is shown to renters after booking confirmation</p>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    // Date validation
    const startDate = document.getElementById('booking_start_date');
    const endDate = document.getElementById('booking_end_date');
    
    startDate.addEventListener('change', function() {
        endDate.min = this.value;
        
        if (endDate.value && endDate.value < this.value) {
            endDate.value = this.value;
        }
    });
    
    endDate.addEventListener('change', function() {
        if (startDate.value && this.value < startDate.value) {
            this.value = startDate.value;
        }
    });
});
</script>

<?php include 'views/footer.php'; ?>
