<?php
require_once 'models/Car.php';
require_once 'models/Reservation.php';
require_once 'models/Payment.php';

// Ensure user is logged in and is an owner
if (!isLoggedIn() || !isOwner()) {
    $_SESSION['alert'] = 'Access denied';
    $_SESSION['alert_type'] = 'danger';
    redirect('');
}

$pageTitle = "Owner Dashboard";

// Get owner's cars
$carModel = new Car($db);
$cars = $carModel->getCarsByOwnerId($_SESSION['user_id']);

// Get reservations for owner's cars
$reservationModel = new Reservation($db);
$reservations = $reservationModel->getReservationsByOwnerId($_SESSION['user_id']);

// Get upcoming reservations
$upcomingReservations = $reservationModel->getUpcomingReservations($_SESSION['user_id'], 'owner', 3);

// Get pending reservations count
$pendingReservations = array_filter($reservations, function($reservation) {
    return $reservation['status'] === 'pending';
});

// Get payment information
$paymentModel = new Payment($db);
$payments = $paymentModel->getPaymentsByUserId($_SESSION['user_id'], 'owner');

// Calculate total earned
$totalEarned = array_reduce($payments, function($carry, $payment) {
    if ($payment['status'] === 'completed') {
        $carry += $payment['amount'];
    }
    return $carry;
}, 0);
?>

<?php include 'views/header.php'; ?>

<div class="container">
    <div class="row">
        <!-- Sidebar -->
        <div class="col-lg-3 mb-4">
            <div class="card shadow-sm mb-4">
                <div class="card-header bg-primary text-white">
                    <h5 class="mb-0">
                        <i class="fas fa-tachometer-alt me-2"></i>Owner Dashboard
                    </h5>
                </div>
                <div class="card-body p-0">
                    <div class="list-group list-group-flush">
                        <a href="<?= APP_URL ?>/?route=owner-dashboard" class="list-group-item list-group-item-action active">
                            <i class="fas fa-home me-2"></i>Dashboard
                        </a>
                        <a href="<?= APP_URL ?>/?route=add-car" class="list-group-item list-group-item-action">
                            <i class="fas fa-plus-circle me-2"></i>Add New Car
                        </a>
                        <a href="<?= APP_URL ?>/?route=manage-reservations" class="list-group-item list-group-item-action d-flex justify-content-between align-items-center">
                            <div><i class="fas fa-calendar-check me-2"></i>Manage Reservations</div>
                            <?php if (count($pendingReservations) > 0): ?>
                                <span class="badge bg-danger rounded-pill"><?= count($pendingReservations) ?></span>
                            <?php endif; ?>
                        </a>
                    </div>
                </div>
            </div>
            
            <div class="card shadow-sm">
                <div class="card-header">
                    <h5 class="mb-0">Account Info</h5>
                </div>
                <div class="card-body">
                    <div class="d-flex align-items-center mb-3">
                        <div class="rounded-circle bg-primary text-white d-flex align-items-center justify-content-center me-3" style="width: 50px; height: 50px;">
                            <i class="fas fa-user"></i>
                        </div>
                        <div>
                            <h6 class="mb-0"><?= htmlspecialchars($_SESSION['user_name']) ?></h6>
                            <small class="text-muted">Car Owner</small>
                        </div>
                    </div>
                    <div class="mb-2">
                        <i class="fas fa-envelope me-2 text-muted"></i><?= htmlspecialchars($_SESSION['user_email']) ?>
                    </div>
                    <div class="mb-3">
                        <i class="fas fa-car me-2 text-muted"></i><?= count($cars) ?> cars listed
                    </div>
                    <div class="d-grid">
                        <a href="<?= APP_URL ?>/?route=add-car" class="btn btn-outline-primary btn-sm">
                            <i class="fas fa-plus-circle me-2"></i>Add Another Car
                        </a>
                    </div>
                </div>
            </div>
        </div>
        
        <!-- Main Content -->
        <div class="col-lg-9">
            <!-- Stats Cards -->
            <div class="row mb-4">
                <div class="col-md-4 mb-3 mb-md-0">
                    <div class="card shadow-sm h-100">
                        <div class="card-body">
                            <div class="d-flex justify-content-between align-items-center">
                                <div>
                                    <h6 class="text-muted mb-2">Total Earned</h6>
                                    <h3 class="mb-0"><?= formatPrice($totalEarned) ?></h3>
                                </div>
                                <div class="rounded-circle bg-success bg-opacity-10 p-3">
                                    <i class="fas fa-money-bill-wave text-success fa-2x"></i>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="col-md-4 mb-3 mb-md-0">
                    <div class="card shadow-sm h-100">
                        <div class="card-body">
                            <div class="d-flex justify-content-between align-items-center">
                                <div>
                                    <h6 class="text-muted mb-2">Active Reservations</h6>
                                    <h3 class="mb-0"><?= count($upcomingReservations) ?></h3>
                                </div>
                                <div class="rounded-circle bg-primary bg-opacity-10 p-3">
                                    <i class="fas fa-calendar-check text-primary fa-2x"></i>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="card shadow-sm h-100">
                        <div class="card-body">
                            <div class="d-flex justify-content-between align-items-center">
                                <div>
                                    <h6 class="text-muted mb-2">Pending Requests</h6>
                                    <h3 class="mb-0"><?= count($pendingReservations) ?></h3>
                                </div>
                                <div class="rounded-circle bg-warning bg-opacity-10 p-3">
                                    <i class="fas fa-clock text-warning fa-2x"></i>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            
            <!-- My Cars -->
            <div class="card shadow-sm mb-4">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <h5 class="mb-0">My Cars</h5>
                    <a href="<?= APP_URL ?>/?route=add-car" class="btn btn-sm btn-primary">
                        <i class="fas fa-plus-circle me-1"></i>Add New Car
                    </a>
                </div>
                <div class="card-body">
                    <?php if (empty($cars)): ?>
                        <div class="alert alert-info mb-0">
                            <i class="fas fa-info-circle me-2"></i>You haven't listed any cars yet. 
                            <a href="<?= APP_URL ?>/?route=add-car" class="alert-link">Add your first car</a> to start earning.
                        </div>
                    <?php else: ?>
                        <div class="row">
                            <?php foreach ($cars as $car): ?>
                                <div class="col-md-6 col-lg-4 mb-4">
                                    <div class="card h-100">
                                        <div class="position-relative">
                                            <?php if (!empty($car['primary_image'])): ?>
                                                <img src="<?= htmlspecialchars($car['primary_image']) ?>" class="card-img-top" alt="<?= htmlspecialchars($car['make'] . ' ' . $car['model']) ?>" style="height: 140px; object-fit: cover;">
                                            <?php else: ?>
                                                <div class="card-img-top bg-light d-flex align-items-center justify-content-center" style="height: 140px;">
                                                    <i class="fas fa-car fa-3x text-muted"></i>
                                                </div>
                                            <?php endif; ?>
                                            
                                            <div class="position-absolute top-0 end-0 mt-2 me-2">
                                                <span class="badge bg-<?= $car['available'] ? 'success' : 'danger' ?>">
                                                    <?= $car['available'] ? 'Available' : 'Not Available' ?>
                                                </span>
                                            </div>
                                            
                                            <?php if ($car['active_reservations'] > 0): ?>
                                                <div class="position-absolute bottom-0 end-0 mb-2 me-2">
                                                    <span class="badge bg-primary">
                                                        <?= $car['active_reservations'] ?> active <?= $car['active_reservations'] > 1 ? 'reservations' : 'reservation' ?>
                                                    </span>
                                                </div>
                                            <?php endif; ?>
                                        </div>
                                        <div class="card-body">
                                            <h6 class="card-title"><?= htmlspecialchars($car['make'] . ' ' . $car['model'] . ' (' . $car['year'] . ')') ?></h6>
                                            <p class="card-text small text-muted">
                                                <i class="fas fa-map-marker-alt me-1"></i><?= htmlspecialchars($car['location']) ?>
                                                <br>
                                                <i class="fas fa-tag me-1"></i><?= formatPrice($car['price_per_day']) ?>/day
                                            </p>
                                            <div class="d-flex justify-content-between mt-3">
                                                <a href="<?= APP_URL ?>/?route=car&id=<?= $car['id'] ?>" class="btn btn-sm btn-outline-primary">
                                                    <i class="fas fa-eye me-1"></i>View
                                                </a>
                                                <a href="<?= APP_URL ?>/?route=add-car&edit=<?= $car['id'] ?>" class="btn btn-sm btn-outline-secondary">
                                                    <i class="fas fa-edit me-1"></i>Edit
                                                </a>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            <?php endforeach; ?>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
            
            <!-- Upcoming Reservations -->
            <div class="card shadow-sm mb-4">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <h5 class="mb-0">Upcoming Reservations</h5>
                    <a href="<?= APP_URL ?>/?route=manage-reservations" class="btn btn-sm btn-outline-primary">View All</a>
                </div>
                <div class="card-body">
                    <?php if (empty($upcomingReservations)): ?>
                        <div class="alert alert-info mb-0">
                            <i class="fas fa-info-circle me-2"></i>You have no upcoming reservations.
                        </div>
                    <?php else: ?>
                        <div class="table-responsive">
                            <table class="table table-hover">
                                <thead>
                                    <tr>
                                        <th>Car</th>
                                        <th>Renter</th>
                                        <th>Dates</th>
                                        <th>Total</th>
                                        <th>Status</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($upcomingReservations as $reservation): ?>
                                        <tr>
                                            <td>
                                                <div class="d-flex align-items-center">
                                                    <?php if (!empty($reservation['car_image'])): ?>
                                                        <img src="<?= htmlspecialchars($reservation['car_image']) ?>" alt="<?= htmlspecialchars($reservation['make'] . ' ' . $reservation['model']) ?>" class="me-2 rounded" width="50" height="40" style="object-fit: cover;">
                                                    <?php else: ?>
                                                        <div class="me-2 rounded bg-light d-flex align-items-center justify-content-center" style="width: 50px; height: 40px;">
                                                            <i class="fas fa-car text-muted"></i>
                                                        </div>
                                                    <?php endif; ?>
                                                    <div>
                                                        <strong><?= htmlspecialchars($reservation['make'] . ' ' . $reservation['model']) ?></strong>
                                                    </div>
                                                </div>
                                            </td>
                                            <td><?= htmlspecialchars($reservation['renter_name']) ?></td>
                                            <td>
                                                <?= formatDate($reservation['start_date']) ?> to <br>
                                                <?= formatDate($reservation['end_date']) ?>
                                            </td>
                                            <td><?= formatPrice($reservation['total_price']) ?></td>
                                            <td>
                                                <span class="badge bg-success">Upcoming</span>
                                            </td>
                                        </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
            
            <!-- Recent Payments -->
            <div class="card shadow-sm">
                <div class="card-header">
                    <h5 class="mb-0">Recent Payments</h5>
                </div>
                <div class="card-body">
                    <?php if (empty($payments)): ?>
                        <div class="alert alert-info mb-0">
                            <i class="fas fa-info-circle me-2"></i>No payment history available.
                        </div>
                    <?php else: ?>
                        <div class="table-responsive">
                            <table class="table table-hover">
                                <thead>
                                    <tr>
                                        <th>Date</th>
                                        <th>Car</th>
                                        <th>Renter</th>
                                        <th>Amount</th>
                                        <th>Status</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php 
                                    // Sort by date descending and limit to 5
                                    usort($payments, function($a, $b) {
                                        return strtotime($b['created_at']) - strtotime($a['created_at']);
                                    });
                                    
                                    $recentPayments = array_slice($payments, 0, 5);
                                    
                                    foreach ($recentPayments as $payment): 
                                    ?>
                                        <tr>
                                            <td><?= formatDate(substr($payment['created_at'], 0, 10)) ?></td>
                                            <td>
                                                <strong><?= htmlspecialchars($payment['make'] . ' ' . $payment['model']) ?></strong>
                                            </td>
                                            <td><?= htmlspecialchars($payment['renter_name']) ?></td>
                                            <td><?= formatPrice($payment['amount']) ?></td>
                                            <td>
                                                <?php
                                                $paymentStatusClass = '';
                                                $paymentStatusText = ucfirst($payment['status']);
                                                
                                                switch ($payment['status']) {
                                                    case 'pending':
                                                        $paymentStatusClass = 'bg-warning text-dark';
                                                        break;
                                                    case 'completed':
                                                        $paymentStatusClass = 'bg-success';
                                                        break;
                                                    case 'failed':
                                                        $paymentStatusClass = 'bg-danger';
                                                        break;
                                                    case 'refunded':
                                                        $paymentStatusClass = 'bg-info';
                                                        break;
                                                }
                                                ?>
                                                <span class="badge <?= $paymentStatusClass ?>"><?= $paymentStatusText ?></span>
                                            </td>
                                        </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>
</div>

<?php include 'views/footer.php'; ?>
