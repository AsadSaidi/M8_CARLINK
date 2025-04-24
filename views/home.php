<?php
require_once 'models/Car.php';
$pageTitle = "Home";

// Get featured cars
$carModel = new Car($db);
$featuredCars = $carModel->getFeaturedCars(6);
?>

<?php include 'views/header.php'; ?>

<!-- Hero Section -->
<section class="hero py-5">
    <div class="container">
        <div class="row align-items-center">
            <div class="col-lg-6">
                <h1 class="display-4 fw-bold mb-3">Rent the perfect car from local owners</h1>
                <p class="lead mb-4">
                    CARLINK makes it easy to find the perfect car for every occasion.
                    Book a nearby car, or share your car and earn extra income.
                </p>
                <div class="d-flex flex-wrap gap-2">
                    <a href="<?= APP_URL ?>/?route=explore" class="btn btn-primary btn-lg">Find a Car</a>
                    <?php if (!isLoggedIn() || (isLoggedIn() && isOwner())): ?>
                        <a href="<?= APP_URL ?>/?route=<?= isLoggedIn() ? 'add-car' : 'register' ?>" class="btn btn-outline-dark btn-lg">Share Your Car</a>
                    <?php endif; ?>
                </div>
            </div>
            <div class="col-lg-6 mt-4 mt-lg-0">
                <div class="card shadow-lg">
                    <div class="card-body p-4">
                        <h3 class="card-title mb-4">Quick Search</h3>
                        <form action="<?= APP_URL ?>/?route=explore" method="GET">
                            <input type="hidden" name="route" value="explore">
                            <div class="mb-3">
                                <label for="location" class="form-label">Location</label>
                                <input type="text" class="form-control" id="location" name="location" placeholder="Where do you need a car?">
                            </div>
                            <div class="row">
                                <div class="col-md-6 mb-3">
                                    <label for="start_date" class="form-label">Start Date</label>
                                    <input type="date" class="form-control" id="start_date" name="start_date" min="<?= date('Y-m-d') ?>">
                                </div>
                                <div class="col-md-6 mb-3">
                                    <label for="end_date" class="form-label">End Date</label>
                                    <input type="date" class="form-control" id="end_date" name="end_date" min="<?= date('Y-m-d') ?>">
                                </div>
                            </div>
                            <button type="submit" class="btn btn-success w-100">Search Cars</button>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>

<!-- How It Works Section -->
<section class="py-5 bg-light">
    <div class="container">
        <h2 class="text-center mb-5">How CARLINK Works</h2>
        <div class="row">
            <div class="col-md-4 mb-4 mb-md-0">
                <div class="card h-100 border-0 shadow-sm">
                    <div class="card-body text-center p-4">
                        <div class="rounded-circle bg-primary bg-opacity-10 d-flex align-items-center justify-content-center mx-auto mb-4" style="width: 80px; height: 80px;">
                            <i class="fas fa-search fa-2x text-primary"></i>
                        </div>
                        <h5 class="card-title">Find the Perfect Car</h5>
                        <p class="card-text text-muted">Search by location, date, or car type. Filter for the features you want, and book online instantly.</p>
                    </div>
                </div>
            </div>
            <div class="col-md-4 mb-4 mb-md-0">
                <div class="card h-100 border-0 shadow-sm">
                    <div class="card-body text-center p-4">
                        <div class="rounded-circle bg-primary bg-opacity-10 d-flex align-items-center justify-content-center mx-auto mb-4" style="width: 80px; height: 80px;">
                            <i class="fas fa-car fa-2x text-primary"></i>
                        </div>
                        <h5 class="card-title">Book & Drive</h5>
                        <p class="card-text text-muted">Book your car with just a few clicks, make a secure payment, and enjoy your rental period.</p>
                    </div>
                </div>
            </div>
            <div class="col-md-4">
                <div class="card h-100 border-0 shadow-sm">
                    <div class="card-body text-center p-4">
                        <div class="rounded-circle bg-primary bg-opacity-10 d-flex align-items-center justify-content-center mx-auto mb-4" style="width: 80px; height: 80px;">
                            <i class="fas fa-money-bill-wave fa-2x text-primary"></i>
                        </div>
                        <h5 class="card-title">Share & Earn</h5>
                        <p class="card-text text-muted">Car owners can list their vehicles and earn money when they're not using them.</p>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>

<!-- Featured Cars Section -->
<section class="py-5">
    <div class="container">
        <h2 class="mb-4">Featured Cars</h2>
        <?php if (empty($featuredCars)): ?>
            <div class="alert alert-info">
                <p class="mb-0">No cars available at the moment. Please check back later!</p>
            </div>
        <?php else: ?>
            <div class="row">
                <?php foreach ($featuredCars as $car): ?>
                    <div class="col-md-6 col-lg-4 mb-4">
                        <div class="card h-100 shadow-sm">
                            <div class="position-relative">
                                <?php if (!empty($car['primary_image'])): ?>
                                    <img src="<?= htmlspecialchars($car['primary_image']) ?>" class="card-img-top" alt="<?= htmlspecialchars($car['make'] . ' ' . $car['model']) ?>" style="height: 200px; object-fit: cover;">
                                <?php else: ?>
                                    <div class="card-img-top bg-light d-flex align-items-center justify-content-center" style="height: 200px;">
                                        <i class="fas fa-car fa-3x text-muted"></i>
                                    </div>
                                <?php endif; ?>
                                <div class="position-absolute bottom-0 start-0 bg-dark bg-opacity-75 text-white px-3 py-1">
                                    <strong><?= formatPrice($car['price_per_day']) ?></strong>/day
                                </div>
                            </div>
                            <div class="card-body">
                                <h5 class="card-title"><?= htmlspecialchars($car['make'] . ' ' . $car['model'] . ' (' . $car['year'] . ')') ?></h5>
                                <p class="card-text text-muted">
                                    <i class="fas fa-map-marker-alt me-2"></i><?= htmlspecialchars($car['location']) ?>
                                </p>
                                <div class="d-flex mb-3">
                                    <span class="badge bg-light text-dark me-2">
                                        <i class="fas fa-gas-pump me-1"></i><?= htmlspecialchars($car['fuel_type']) ?>
                                    </span>
                                    <span class="badge bg-light text-dark">
                                        <i class="fas fa-cog me-1"></i><?= htmlspecialchars($car['transmission']) ?>
                                    </span>
                                </div>
                                <div class="d-grid gap-2">
                                    <a href="<?= APP_URL ?>/?route=car&id=<?= $car['id'] ?>" class="btn btn-outline-primary">View Details</a>
                                </div>
                            </div>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
            <div class="text-center mt-4">
                <a href="<?= APP_URL ?>/?route=explore" class="btn btn-success btn-lg">View All Cars</a>
            </div>
        <?php endif; ?>
    </div>
</section>

<!-- Testimonials Section -->
<section class="py-5 bg-light">
    <div class="container">
        <h2 class="text-center mb-5">What Our Users Say</h2>
        <div class="row">
            <div class="col-md-4 mb-4 mb-md-0">
                <div class="card h-100 border-0 shadow-sm">
                    <div class="card-body p-4">
                        <div class="d-flex mb-3">
                            <div class="text-warning">
                                <i class="fas fa-star"></i>
                                <i class="fas fa-star"></i>
                                <i class="fas fa-star"></i>
                                <i class="fas fa-star"></i>
                                <i class="fas fa-star"></i>
                            </div>
                        </div>
                        <p class="card-text">"I rented a car through CARLINK for my weekend trip. The process was so smooth and the car was perfect. Will definitely use again!"</p>
                        <div class="d-flex align-items-center mt-3">
                            <div class="rounded-circle bg-primary text-white d-flex align-items-center justify-content-center me-3" style="width: 40px; height: 40px;">
                                <i class="fas fa-user"></i>
                            </div>
                            <div>
                                <h6 class="mb-0">Sarah Martinez</h6>
                                <small class="text-muted">Renter</small>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-md-4 mb-4 mb-md-0">
                <div class="card h-100 border-0 shadow-sm">
                    <div class="card-body p-4">
                        <div class="d-flex mb-3">
                            <div class="text-warning">
                                <i class="fas fa-star"></i>
                                <i class="fas fa-star"></i>
                                <i class="fas fa-star"></i>
                                <i class="fas fa-star"></i>
                                <i class="fas fa-star"></i>
                            </div>
                        </div>
                        <p class="card-text">"As a car owner, CARLINK has been a fantastic way to earn extra income. The platform is easy to use and the support is excellent."</p>
                        <div class="d-flex align-items-center mt-3">
                            <div class="rounded-circle bg-primary text-white d-flex align-items-center justify-content-center me-3" style="width: 40px; height: 40px;">
                                <i class="fas fa-user"></i>
                            </div>
                            <div>
                                <h6 class="mb-0">David Johnson</h6>
                                <small class="text-muted">Car Owner</small>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-md-4">
                <div class="card h-100 border-0 shadow-sm">
                    <div class="card-body p-4">
                        <div class="d-flex mb-3">
                            <div class="text-warning">
                                <i class="fas fa-star"></i>
                                <i class="fas fa-star"></i>
                                <i class="fas fa-star"></i>
                                <i class="fas fa-star"></i>
                                <i class="fas fa-star-half-alt"></i>
                            </div>
                        </div>
                        <p class="card-text">"I needed a specific type of car for a family trip and found exactly what I was looking for on CARLINK. Great selection and reasonable prices."</p>
                        <div class="d-flex align-items-center mt-3">
                            <div class="rounded-circle bg-primary text-white d-flex align-items-center justify-content-center me-3" style="width: 40px; height: 40px;">
                                <i class="fas fa-user"></i>
                            </div>
                            <div>
                                <h6 class="mb-0">Michael Chen</h6>
                                <small class="text-muted">Renter</small>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>

<!-- CTA Section -->
<section class="py-5 bg-primary text-white">
    <div class="container text-center">
        <h2 class="mb-4">Ready to get started with CARLINK?</h2>
        <p class="lead mb-4">Join thousands of users who are already renting and sharing cars on our platform.</p>
        <div class="d-flex justify-content-center gap-3 flex-wrap">
            <a href="<?= APP_URL ?>/?route=explore" class="btn btn-light btn-lg">Find a Car</a>
            <?php if (!isLoggedIn() || (isLoggedIn() && isOwner())): ?>
                <a href="<?= APP_URL ?>/?route=<?= isLoggedIn() ? 'add-car' : 'register' ?>" class="btn btn-outline-light btn-lg">Share Your Car</a>
            <?php endif; ?>
        </div>
    </div>
</section>

<?php include 'views/footer.php'; ?>
