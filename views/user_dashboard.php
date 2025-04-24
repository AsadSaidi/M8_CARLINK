<?php
require_once 'models/Reservation.php';
require_once 'models/Payment.php';

// Ensure user is logged in
if (!isLoggedIn() || !isRenter()) {
    $_SESSION['alert'] = 'Access denied';
    $_SESSION['alert_type'] = 'danger';
    redirect('');
}

$pageTitle = "My Reservations";

// Get user's reservations
$reservationModel = new Reservation($db);
$reservations = $reservationModel->getReservationsByRenterId($_SESSION['user_id']);

// Get user's upcoming reservations
$upcomingReservations = $reservationModel->getUpcomingReservations($_SESSION['user_id'], 'renter', 3);

// Get payment history
$paymentModel = new Payment($db);
$payments = $paymentModel->getPaymentsByUserId($_SESSION['user_id'], 'renter');
?>

<?php include 'views/header.php'; ?>

<div class="container">
    <div class="row">
        <!-- Sidebar -->
        <div class="col-lg-3 mb-4">
            <div class="card shadow-sm mb-4">
                <div class="card-header bg-primary text-white">
                    <h5 class="mb-0">
                        <i class="fas fa-user-circle me-2"></i>My Account
                    </h5>
                </div>
                <div class="card-body p-0">
                    <div class="list-group list-group-flush">
                        <a href="<?= APP_URL ?>/?route=user-dashboard" class="list-group-item list-group-item-action active">
                            <i class="fas fa-calendar-alt me-2"></i>My Reservations
                        </a>
                        <a href="<?= APP_URL ?>/?route=explore" class="list-group-item list-group-item-action">
                            <i class="fas fa-search me-2"></i>Find Cars
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
                            <small class="text-muted">Member since <?= date('M Y') ?></small>
                        </div>
                    </div>
                    <div class="mb-2">
                        <i class="fas fa-envelope me-2 text-muted"></i><?= htmlspecialchars($_SESSION['user_email']) ?>
                    </div>
                    <div>
                        <i class="fas fa-user-tag me-2 text-muted"></i>Renter
                    </div>
                </div>
            </div>
        </div>
        
        <!-- Main Content -->
        <div class="col-lg-9">
            <!-- Upcoming Reservations -->
            <div class="card shadow-sm mb-4">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <h5 class="mb-0">Upcoming Trips</h5>
                </div>
                <div class="card-body">
                    <?php if (empty($upcomingReservations)): ?>
                        <div class="alert alert-info mb-0">
                            <i class="fas fa-info-circle me-2"></i>You have no upcoming trips. 
                            <a href="<?= APP_URL ?>/?route=explore" class="alert-link">Find a car to rent</a>.
                        </div>
                    <?php else: ?>
                        <div class="row">
                            <?php foreach ($upcomingReservations as $reservation): ?>
                                <div class="col-md-4 mb-3 mb-md-0">
                                    <div class="card h-100">
                                        <div class="position-relative">
                                            <?php if (!empty($reservation['car_image'])): ?>
                                                <img src="<?= htmlspecialchars($reservation['car_image']) ?>" class="card-img-top" alt="<?= htmlspecialchars($reservation['make'] . ' ' . $reservation['model']) ?>" style="height: 140px; object-fit: cover;">
                                            <?php else: ?>
                                                <div class="card-img-top bg-light d-flex align-items-center justify-content-center" style="height: 140px;">
                                                    <i class="fas fa-car fa-3x text-muted"></i>
                                                </div>
                                            <?php endif; ?>
                                        </div>
                                        <div class="card-body">
                                            <h6 class="card-title"><?= htmlspecialchars($reservation['make'] . ' ' . $reservation['model']) ?></h6>
                                            <div class="text-muted small mb-2">
                                                <i class="fas fa-user me-1"></i><?= htmlspecialchars($reservation['owner_name']) ?>
                                            </div>
                                            <div class="d-flex justify-content-between small text-muted mb-2">
                                                <span><i class="fas fa-calendar me-1"></i><?= formatDate($reservation['start_date']) ?></span>
                                                <span><i class="fas fa-arrow-right"></i></span>
                                                <span><i class="fas fa-calendar me-1"></i><?= formatDate($reservation['end_date']) ?></span>
                                            </div>
                                            <div class="d-grid">
                                                <a href="<?= APP_URL ?>/?route=car&id=<?= $reservation['car_id'] ?>" class="btn btn-sm btn-outline-primary">View Car</a>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            <?php endforeach; ?>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
            
            <!-- All Reservations -->
            <div class="card shadow-sm mb-4">
                <div class="card-header">
                    <h5 class="mb-0">All Reservations</h5>
                </div>
                <div class="card-body">
                    <?php if (empty($reservations)): ?>
                        <div class="alert alert-info mb-0">
                            <i class="fas fa-info-circle me-2"></i>You haven't made any reservations yet. 
                            <a href="<?= APP_URL ?>/?route=explore" class="alert-link">Find a car to rent</a>.
                        </div>
                    <?php else: ?>
                        <div class="table-responsive">
                            <table class="table table-hover">
                                <thead>
                                    <tr>
                                        <th>Car</th>
                                        <th>Dates</th>
                                        <th>Total</th>
                                        <th>Status</th>
                                        <th>Action</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($reservations as $reservation): ?>
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
                                                        <div class="small text-muted"><?= htmlspecialchars($reservation['owner_name']) ?></div>
                                                    </div>
                                                </div>
                                            </td>
                                            <td>
                                                <?= formatDate($reservation['start_date']) ?> to <br>
                                                <?= formatDate($reservation['end_date']) ?>
                                            </td>
                                            <td><?= formatPrice($reservation['total_price']) ?></td>
                                            <td>
                                                <?php
                                                $statusClass = '';
                                                $statusText = ucfirst($reservation['status']);
                                                
                                                switch ($reservation['status']) {
                                                    case 'pending':
                                                        $statusClass = 'bg-warning text-dark';
                                                        break;
                                                    case 'approved':
                                                        $statusClass = 'bg-success';
                                                        break;
                                                    case 'cancelled':
                                                        $statusClass = 'bg-danger';
                                                        break;
                                                    case 'rejected':
                                                        $statusClass = 'bg-danger';
                                                        break;
                                                    case 'completed':
                                                        $statusClass = 'bg-info';
                                                        break;
                                                }
                                                ?>
                                                <span class="badge <?= $statusClass ?>"><?= $statusText ?></span>
                                                
                                                <?php if (isset($reservation['payment_status'])): ?>
                                                    <br>
                                                    <small class="text-muted">
                                                        Payment: <?= ucfirst($reservation['payment_status'] ?? 'n/a') ?>
                                                    </small>
                                                <?php endif; ?>
                                            </td>
                                            <td>
                                                <div class="btn-group" role="group">
                                                    <a href="<?= APP_URL ?>/?route=car&id=<?= $reservation['car_id'] ?>" class="btn btn-sm btn-outline-primary">
                                                        <i class="fas fa-eye"></i>
                                                    </a>
                                                    
                                                    <?php if ($reservation['status'] === 'pending'): ?>
                                                        <form action="<?= APP_URL ?>/?route=update-reservation" method="POST" class="d-inline" onsubmit="return confirm('Are you sure you want to cancel this reservation?')">
                                                            <input type="hidden" name="csrf_token" value="<?= generateCSRFToken() ?>">
                                                            <input type="hidden" name="reservation_id" value="<?= $reservation['id'] ?>">
                                                            <input type="hidden" name="action" value="cancel">
                                                            <button type="submit" class="btn btn-sm btn-outline-danger">
                                                                <i class="fas fa-times"></i>
                                                            </button>
                                                        </form>
                                                    <?php endif; ?>
                                                </div>
                                            </td>
                                        </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
            
            <!-- Payment History -->
            <div class="card shadow-sm">
                <div class="card-header">
                    <h5 class="mb-0">Payment History</h5>
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
                                        <th>Amount</th>
                                        <th>Method</th>
                                        <th>Status</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($payments as $payment): ?>
                                        <tr>
                                            <td><?= formatDate(substr($payment['created_at'], 0, 10)) ?></td>
                                            <td>
                                                <strong><?= htmlspecialchars($payment['make'] . ' ' . $payment['model']) ?></strong>
                                                <div class="small text-muted">
                                                    <?= formatDate($payment['start_date']) ?> to <?= formatDate($payment['end_date']) ?>
                                                </div>
                                            </td>
                                            <td><?= formatPrice($payment['amount']) ?></td>
                                            <td><?= ucfirst($payment['payment_method'] ?? 'Credit Card') ?></td>
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
