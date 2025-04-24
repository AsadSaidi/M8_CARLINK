<?php
require_once 'models/Car.php';
$pageTitle = "Explore Cars";

// Initialize Car model
$carModel = new Car($db);

// Get makes for filter
$makes = $carModel->getUniqueMakes();

// Get filters from URL parameters
$filters = [
    'make' => $_GET['make'] ?? '',
    'model' => $_GET['model'] ?? '',
    'location' => $_GET['location'] ?? '',
    'min_price' => $_GET['min_price'] ?? '',
    'max_price' => $_GET['max_price'] ?? '',
    'fuel_type' => $_GET['fuel_type'] ?? '',
    'transmission' => $_GET['transmission'] ?? '',
    'start_date' => $_GET['start_date'] ?? '',
    'end_date' => $_GET['end_date'] ?? '',
    'order_by' => $_GET['order_by'] ?? 'price_per_day',
    'order' => $_GET['order'] ?? 'ASC'
];

// Pagination
$page = isset($_GET['page']) ? max(1, intval($_GET['page'])) : 1;
$limit = 12;
$offset = ($page - 1) * $limit;

// Get cars based on filters
$cars = $carModel->searchCars($filters, $limit, $offset);
$totalCars = $carModel->countCars($filters);
$totalPages = ceil($totalCars / $limit);

$extraScripts = '<script src="' . APP_URL . '/assets/js/main.js"></script>';
?>

<?php include 'views/header.php'; ?>

<div class="row">
    <!-- Filters Sidebar -->
    <div class="col-lg-3 mb-4">
        <div class="card shadow-sm">
            <div class="card-header bg-primary text-white">
                <h5 class="mb-0">
                    <i class="fas fa-filter me-2"></i>Filters
                </h5>
            </div>
            <div class="card-body">
                <form action="<?= APP_URL ?>/" method="GET" id="filterForm">
                    <input type="hidden" name="route" value="explore">
                    
                    <div class="mb-3">
                        <label for="location" class="form-label">Location</label>
                        <input type="text" class="form-control" id="location" name="location" 
                               value="<?= htmlspecialchars($filters['location']) ?>" placeholder="City, Region...">
                    </div>
                    
                    <div class="mb-3">
                        <label for="start_date" class="form-label">Start Date</label>
                        <input type="date" class="form-control" id="start_date" name="start_date" 
                               value="<?= htmlspecialchars($filters['start_date']) ?>" min="<?= date('Y-m-d') ?>">
                    </div>
                    
                    <div class="mb-3">
                        <label for="end_date" class="form-label">End Date</label>
                        <input type="date" class="form-control" id="end_date" name="end_date" 
                               value="<?= htmlspecialchars($filters['end_date']) ?>" min="<?= date('Y-m-d') ?>">
                    </div>
                    
                    <div class="mb-3">
                        <label for="make" class="form-label">Make</label>
                        <select class="form-select" id="make" name="make">
                            <option value="">All Makes</option>
                            <?php foreach ($makes as $make): ?>
                                <option value="<?= htmlspecialchars($make) ?>" <?= $filters['make'] === $make ? 'selected' : '' ?>>
                                    <?= htmlspecialchars($make) ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    
                    <div class="mb-3">
                        <label for="model" class="form-label">Model</label>
                        <select class="form-select" id="model" name="model" <?= empty($filters['make']) ? 'disabled' : '' ?>>
                            <option value="">All Models</option>
                            <?php if (!empty($filters['make'])): ?>
                                <?php 
                                $models = $carModel->getModelsByMake($filters['make']); 
                                foreach ($models as $model): 
                                ?>
                                    <option value="<?= htmlspecialchars($model) ?>" <?= $filters['model'] === $model ? 'selected' : '' ?>>
                                        <?= htmlspecialchars($model) ?>
                                    </option>
                                <?php endforeach; ?>
                            <?php endif; ?>
                        </select>
                    </div>
                    
                    <div class="mb-3">
                        <label for="price_range" class="form-label">Price per Day (€)</label>
                        <div class="row">
                            <div class="col-6">
                                <input type="number" class="form-control" id="min_price" name="min_price" 
                                       placeholder="Min" min="0" value="<?= htmlspecialchars($filters['min_price']) ?>">
                            </div>
                            <div class="col-6">
                                <input type="number" class="form-control" id="max_price" name="max_price" 
                                       placeholder="Max" min="0" value="<?= htmlspecialchars($filters['max_price']) ?>">
                            </div>
                        </div>
                    </div>
                    
                    <div class="mb-3">
                        <label for="fuel_type" class="form-label">Fuel Type</label>
                        <select class="form-select" id="fuel_type" name="fuel_type">
                            <option value="">All Types</option>
                            <option value="gasoline" <?= $filters['fuel_type'] === 'gasoline' ? 'selected' : '' ?>>Gasoline</option>
                            <option value="diesel" <?= $filters['fuel_type'] === 'diesel' ? 'selected' : '' ?>>Diesel</option>
                            <option value="electric" <?= $filters['fuel_type'] === 'electric' ? 'selected' : '' ?>>Electric</option>
                            <option value="hybrid" <?= $filters['fuel_type'] === 'hybrid' ? 'selected' : '' ?>>Hybrid</option>
                        </select>
                    </div>
                    
                    <div class="mb-3">
                        <label for="transmission" class="form-label">Transmission</label>
                        <select class="form-select" id="transmission" name="transmission">
                            <option value="">All Transmissions</option>
                            <option value="automatic" <?= $filters['transmission'] === 'automatic' ? 'selected' : '' ?>>Automatic</option>
                            <option value="manual" <?= $filters['transmission'] === 'manual' ? 'selected' : '' ?>>Manual</option>
                        </select>
                    </div>
                    
                    <div class="mb-3">
                        <label for="order_by" class="form-label">Sort By</label>
                        <select class="form-select" id="order_by" name="order_by">
                            <option value="price_per_day" <?= $filters['order_by'] === 'price_per_day' ? 'selected' : '' ?>>Price</option>
                            <option value="year" <?= $filters['order_by'] === 'year' ? 'selected' : '' ?>>Year</option>
                            <option value="make" <?= $filters['order_by'] === 'make' ? 'selected' : '' ?>>Make</option>
                        </select>
                    </div>
                    
                    <div class="mb-3">
                        <label for="order" class="form-label">Order</label>
                        <select class="form-select" id="order" name="order">
                            <option value="ASC" <?= $filters['order'] === 'ASC' ? 'selected' : '' ?>>Ascending</option>
                            <option value="DESC" <?= $filters['order'] === 'DESC' ? 'selected' : '' ?>>Descending</option>
                        </select>
                    </div>
                    
                    <div class="d-grid gap-2">
                        <button type="submit" class="btn btn-primary">Apply Filters</button>
                        <button type="button" id="clearFilters" class="btn btn-outline-secondary">Clear Filters</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
    
    <!-- Car Listings -->
    <div class="col-lg-9">
        <div class="d-flex justify-content-between align-items-center mb-4">
            <h2 class="mb-0">Available Cars</h2>
            <span class="text-muted"><?= $totalCars ?> cars found</span>
        </div>
        
        <?php if (empty($cars)): ?>
            <div class="alert alert-info">
                <h5><i class="fas fa-info-circle me-2"></i>No cars found</h5>
                <p class="mb-0">Try adjusting your filters or search criteria.</p>
            </div>
        <?php else: ?>
            <div class="row">
                <?php foreach ($cars as $car): ?>
                    <div class="col-md-6 col-lg-4 mb-4">
                        <div class="card h-100 shadow-sm">
                            <div class="position-relative">
                                <?php if (!empty($car['primary_image'])): ?>
                                    <img src="<?= htmlspecialchars($car['primary_image']) ?>" class="card-img-top" alt="<?= htmlspecialchars($car['make'] . ' ' . $car['model']) ?>" style="height: 180px; object-fit: cover;">
                                <?php else: ?>
                                    <div class="card-img-top bg-light d-flex align-items-center justify-content-center" style="height: 180px;">
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
                                    <a href="<?= APP_URL ?>/?route=car&id=<?= $car['id'] ?><?= !empty($filters['start_date']) && !empty($filters['end_date']) ? '&start_date=' . urlencode($filters['start_date']) . '&end_date=' . urlencode($filters['end_date']) : '' ?>" class="btn btn-outline-primary">View Details</a>
                                </div>
                            </div>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
            
            <!-- Pagination -->
            <?php if ($totalPages > 1): ?>
                <div class="d-flex justify-content-center mt-4">
                    <nav aria-label="Page navigation">
                        <ul class="pagination">
                            <?php if ($page > 1): ?>
                                <li class="page-item">
                                    <a class="page-link" href="<?= APP_URL ?>/?route=explore&page=<?= $page - 1 ?><?= http_build_query(array_filter($filters)) ? '&' . http_build_query(array_filter($filters)) : '' ?>">Previous</a>
                                </li>
                            <?php endif; ?>
                            
                            <?php for ($i = max(1, $page - 2); $i <= min($totalPages, $page + 2); $i++): ?>
                                <li class="page-item <?= $i === $page ? 'active' : '' ?>">
                                    <a class="page-link" href="<?= APP_URL ?>/?route=explore&page=<?= $i ?><?= http_build_query(array_filter($filters)) ? '&' . http_build_query(array_filter($filters)) : '' ?>"><?= $i ?></a>
                                </li>
                            <?php endfor; ?>
                            
                            <?php if ($page < $totalPages): ?>
                                <li class="page-item">
                                    <a class="page-link" href="<?= APP_URL ?>/?route=explore&page=<?= $page + 1 ?><?= http_build_query(array_filter($filters)) ? '&' . http_build_query(array_filter($filters)) : '' ?>">Next</a>
                                </li>
                            <?php endif; ?>
                        </ul>
                    </nav>
                </div>
            <?php endif; ?>
        <?php endif; ?>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    // Make dropdown
    const makeSelect = document.getElementById('make');
    const modelSelect = document.getElementById('model');
    
    makeSelect.addEventListener('change', function() {
        const make = this.value;
        
        // Clear and disable model select if no make is selected
        if (!make) {
            modelSelect.innerHTML = '<option value="">All Models</option>';
            modelSelect.disabled = true;
            return;
        }
        
        // Fetch models for selected make
        fetch(`<?= APP_URL ?>/api/get-models.php?make=${encodeURIComponent(make)}`)
            .then(response => response.json())
            .then(data => {
                modelSelect.innerHTML = '<option value="">All Models</option>';
                
                if (data.success) {
                    data.models.forEach(model => {
                        const option = document.createElement('option');
                        option.value = model;
                        option.textContent = model;
                        modelSelect.appendChild(option);
                    });
                    modelSelect.disabled = false;
                }
            })
            .catch(error => {
                console.error('Error fetching models:', error);
            });
    });
    
    // Clear filters button
    document.getElementById('clearFilters').addEventListener('click', function() {
        window.location.href = '<?= APP_URL ?>/?route=explore';
    });
    
    // Date validation
    const startDate = document.getElementById('start_date');
    const endDate = document.getElementById('end_date');
    
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
    
    // Price validation
    const minPrice = document.getElementById('min_price');
    const maxPrice = document.getElementById('max_price');
    
    minPrice.addEventListener('change', function() {
        if (maxPrice.value && parseFloat(this.value) > parseFloat(maxPrice.value)) {
            maxPrice.value = this.value;
        }
    });
    
    maxPrice.addEventListener('change', function() {
        if (minPrice.value && parseFloat(this.value) < parseFloat(minPrice.value)) {
            this.value = minPrice.value;
        }
    });
});
</script>

<?php include 'views/footer.php'; ?>
