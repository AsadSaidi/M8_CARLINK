<?php
require_once 'models/Reservation.php';

// Ensure user is logged in and is an owner
if (!isLoggedIn() || !isOwner()) {
    $_SESSION['alert'] = 'Access denied';
    $_SESSION['alert_type'] = 'danger';
    redirect('');
}

$pageTitle = "Manage Reservations";

// Get all reservations for owner's cars
$reservationModel = new Reservation($db);
$reservations = $reservationModel->getReservationsByOwnerId($_SESSION['user_id']);

// Filter reservations by status
$pendingReservations = array_filter($reservations, function($reservation) {
    return $reservation['status'] === 'pending';
});

$upcomingReservations = array_filter($reservations, function($reservation) {
    return $reservation['status'] === 'approved' && strtotime($reservation['start_date']) >= strtotime(date('Y-m-d'));
});

$activeReservations = array_filter($reservations, function($reservation) {
    return $reservation['status'] === 'approved' && 
           strtotime($reservation['start_date']) <= strtotime(date('Y-m-d')) && 
           strtotime($reservation['end_date']) >= strtotime(date('Y-m-d'));
});

$pastReservations = array_filter($reservations, function($reservation) {
    return ($reservation['status'] === 'approved' || $reservation['status'] === 'completed') && 
           strtotime($reservation['end_date']) < strtotime(date('Y-m-d'));
});

$cancelledReservations = array_filter($reservations, function($reservation) {
    return $reservation['status'] === 'cancelled' || $reservation['status'] === 'rejected';
});
?>

<?php include 'views/header.php'; ?>

<div class="row">
    <!-- Sidebar -->
    <div class="col-lg-3 mb-4">
        <div class="card shadow-sm mb-4">
            <div class="card-header bg-primary text-white">
                <h5 class="mb-0">
                    <i class="fas fa-calendar-check me-2"></i>Manage Reservations
                </h5>
            </div>
            <div class="card-body p-0">
                <div class="list-group list-group-flush">
                    <a href="<?= APP_URL ?>/?route=owner-dashboard" class="list-group-item list-group-item-action">
                        <i class="fas fa-home me-2"></i>Dashboard
                    </a>
                    <a href="<?= APP_URL ?>/?route=add-car" class="list-group-item list-group-item-action">
                        <i class="fas fa-plus-circle me-2"></i>Add New Car
                    </a>
                    <a href="<?= APP_URL ?>/?route=manage-reservations" class="list-group-item list-group-item-action active d-flex justify-content-between align-items-center">
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
                <h5 class="mb-0">Reservation Summary</h5>
            </div>
            <div class="card-body">
                <ul class="list-group list-group-flush">
                    <li class="list-group-item d-flex justify-content-between align-items-center">
                        <span><i class="fas fa-clock text-warning me-2"></i>Pending</span>
                        <span class="badge bg-warning text-dark rounded-pill"><?= count($pendingReservations) ?></span>
                    </li>
                    <li class="list-group-item d-flex justify-content-between align-items-center">
                        <span><i class="fas fa-calendar-alt text-primary me-2"></i>Upcoming</span>
                        <span class="badge bg-primary rounded-pill"><?= count($upcomingReservations) ?></span>
                    </li>
                    <li class="list-group-item d-flex justify-content-between align-items-center">
                        <span><i class="fas fa-car text-success me-2"></i>Active</span>
                        <span class="badge bg-success rounded-pill"><?= count($activeReservations) ?></span>
                    </li>
                    <li class="list-group-item d-flex justify-content-between align-items-center">
                        <span><i class="fas fa-history text-secondary me-2"></i>Past</span>
                        <span class="badge bg-secondary rounded-pill"><?= count($pastReservations) ?></span>
                    </li>
                    <li class="list-group-item d-flex justify-content-between align-items-center">
                        <span><i class="fas fa-ban text-danger me-2"></i>Cancelled/Rejected</span>
                        <span class="badge bg-danger rounded-pill"><?= count($cancelledReservations) ?></span>
                    </li>
                </ul>
            </div>
        </div>
    </div>
    
    <!-- Main Content -->
    <div class="col-lg-9">
        <!-- Tabs -->
        <ul class="nav nav-tabs mb-4" id="reservationTabs" role="tablist">
            <li class="nav-item" role="presentation">
                <button class="nav-link active position-relative" id="pending-tab" data-bs-toggle="tab" data-bs-target="#pending" type="button" role="tab" aria-controls="pending" aria-selected="true">
                    Pending
                    <?php if (count($pendingReservations) > 0): ?>
                        <span class="position-absolute top-0 start-100 translate-middle badge rounded-pill bg-danger">
                            <?= count($pendingReservations) ?>
                        </span>
                    <?php endif; ?>
                </button>
            </li>
            <li class="nav-item" role="presentation">
                <button class="nav-link" id="upcoming-tab" data-bs-toggle="tab" data-bs-target="#upcoming" type="button" role="tab" aria-controls="upcoming" aria-selected="false">Upcoming</button>
            </li>
            <li class="nav-item" role="presentation">
                <button class="nav-link" id="active-tab" data-bs-toggle="tab" data-bs-target="#active" type="button" role="tab" aria-controls="active" aria-selected="false">Active</button>
            </li>
            <li class="nav-item" role="presentation">
                <button class="nav-link" id="past-tab" data-bs-toggle="tab" data-bs-target="#past" type="button" role="tab" aria-controls="past" aria-selected="false">Past</button>
            </li>
            <li class="nav-item" role="presentation">
                <button class="nav-link" id="cancelled-tab" data-bs-toggle="tab" data-bs-target="#cancelled" type="button" role="tab" aria-controls="cancelled" aria-selected="false">Cancelled/Rejected</button>
            </li>
        </ul>
        
        <!-- Tab Content -->
        <div class="tab-content" id="reservationTabsContent">
            <!-- Pending Reservations -->
            <div class="tab-pane fade show active" id="pending" role="tabpanel" aria-labelledby="pending-tab">
                <div class="card shadow-sm">
                    <div class="card-header">
                        <h5 class="mb-0">Pending Reservations</h5>
                    </div>
                    <div class="card-body">
                        <?php if (empty($pendingReservations)): ?>
                            <div class="alert alert-info mb-0">
                                <i class="fas fa-info-circle me-2"></i>No pending reservation requests.
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
                                            <th>Action</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php foreach ($pendingReservations as $reservation): ?>
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
                                                            <div class="small text-muted"><?= htmlspecialchars($reservation['location']) ?></div>
                                                        </div>
                                                    </div>
                                                </td>
                                                <td>
                                                    <div>
                                                        <strong><?= htmlspecialchars($reservation['renter_name']) ?></strong>
                                                        <div class="small text-muted"><?= htmlspecialchars($reservation['renter_email']) ?></div>
                                                        <?php if (!empty($reservation['renter_phone'])): ?>
                                                            <div class="small text-muted"><?= htmlspecialchars($reservation['renter_phone']) ?></div>
                                                        <?php endif; ?>
                                                    </div>
                                                </td>
                                                <td>
                                                    <?= formatDate($reservation['start_date']) ?> to <br>
                                                    <?= formatDate($reservation['end_date']) ?>
                                                </td>
                                                <td><?= formatPrice($reservation['total_price']) ?></td>
                                                <td>
                                                    <div class="btn-group" role="group">
                                                        <form action="<?= APP_URL ?>/?route=update-reservation" method="POST" class="d-inline">
                                                            <input type="hidden" name="csrf_token" value="<?= generateCSRFToken() ?>">
                                                            <input type="hidden" name="reservation_id" value="<?= $reservation['id'] ?>">
                                                            <input type="hidden" name="action" value="approve">
                                                            <button type="submit" class="btn btn-sm btn-success" onclick="return confirm('Are you sure you want to approve this reservation?')">
                                                                <i class="fas fa-check me-1"></i>Approve
                                                            </button>
                                                        </form>
                                                        <form action="<?= APP_URL ?>/?route=update-reservation" method="POST" class="d-inline">
                                                            <input type="hidden" name="csrf_token" value="<?= generateCSRFToken() ?>">
                                                            <input type="hidden" name="reservation_id" value="<?= $reservation['id'] ?>">
                                                            <input type="hidden" name="action" value="reject">
                                                            <button type="submit" class="btn btn-sm btn-danger" onclick="return confirm('Are you sure you want to reject this reservation?')">
                                                                <i class="fas fa-times me-1"></i>Reject
                                                            </button>
                                                        </form>
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
            </div>
            
            <!-- Upcoming Reservations -->
            <div class="tab-pane fade" id="upcoming" role="tabpanel" aria-labelledby="upcoming-tab">
                <div class="card shadow-sm">
                    <div class="card-header">
                        <h5 class="mb-0">Upcoming Reservations</h5>
                    </div>
                    <div class="card-body">
                        <?php if (empty($upcomingReservations)): ?>
                            <div class="alert alert-info mb-0">
                                <i class="fas fa-info-circle me-2"></i>No upcoming reservations.
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
                                                            <div class="small text-muted"><?= htmlspecialchars($reservation['location']) ?></div>
                                                        </div>
                                                    </div>
                                                </td>
                                                <td>
                                                    <div>
                                                        <strong><?= htmlspecialchars($reservation['renter_name']) ?></strong>
                                                        <div class="small text-muted"><?= htmlspecialchars($reservation['renter_email']) ?></div>
                                                        <?php if (!empty($reservation['renter_phone'])): ?>
                                                            <div class="small text-muted"><?= htmlspecialchars($reservation['renter_phone']) ?></div>
                                                        <?php endif; ?>
                                                    </div>
                                                </td>
                                                <td>
                                                    <?= formatDate($reservation['start_date']) ?> to <br>
                                                    <?= formatDate($reservation['end_date']) ?>
                                                </td>
                                                <td><?= formatPrice($reservation['total_price']) ?></td>
                                                <td>
                                                    <span class="badge bg-success">Approved</span>
                                                    <?php if (isset($reservation['payment_status'])): ?>
                                                        <br>
                                                        <small class="text-muted">
                                                            Payment: <?= ucfirst($reservation['payment_status'] ?? 'n/a') ?>
                                                        </small>
                                                    <?php endif; ?>
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
            
            <!-- Active Reservations -->
            <div class="tab-pane fade" id="active" role="tabpanel" aria-labelledby="active-tab">
                <div class="card shadow-sm">
                    <div class="card-header">
                        <h5 class="mb-0">Active Reservations</h5>
                    </div>
                    <div class="card-body">
                        <?php if (empty($activeReservations)): ?>
                            <div class="alert alert-info mb-0">
                                <i class="fas fa-info-circle me-2"></i>No active reservations.
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
                                            <th>Action</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php foreach ($activeReservations as $reservation): ?>
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
                                                            <div class="small text-muted"><?= htmlspecialchars($reservation['location']) ?></div>
                                                        </div>
                                                    </div>
                                                </td>
                                                <td>
                                                    <div>
                                                        <strong><?= htmlspecialchars($reservation['renter_name']) ?></strong>
                                                        <div class="small text-muted"><?= htmlspecialchars($reservation['renter_email']) ?></div>
                                                        <?php if (!empty($reservation['renter_phone'])): ?>
                                                            <div class="small text-muted"><?= htmlspecialchars($reservation['renter_phone']) ?></div>
                                                        <?php endif; ?>
                                                    </div>
                                                </td>
                                                <td>
                                                    <?= formatDate($reservation['start_date']) ?> to <br>
                                                    <?= formatDate($reservation['end_date']) ?>
                                                </td>
                                                <td><?= formatPrice($reservation['total_price']) ?></td>
                                                <td>
                                                    <form action="<?= APP_URL ?>/?route=update-reservation" method="POST">
                                                        <input type="hidden" name="csrf_token" value="<?= generateCSRFToken() ?>">
                                                        <input type="hidden" name="reservation_id" value="<?= $reservation['id'] ?>">
                                                        <input type="hidden" name="action" value="complete">
                                                        <button type="submit" class="btn btn-sm btn-outline-success" onclick="return confirm('Mark this reservation as completed?')">
                                                            <i class="fas fa-check-circle me-1"></i>Complete
                                                        </button>
                                                    </form>
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
            
            <!-- Past Reservations -->
            <div class="tab-pane fade" id="past" role="tabpanel" aria-labelledby="past-tab">
                <div class="card shadow-sm">
                    <div class="card-header">
                        <h5 class="mb-0">Past Reservations</h5>
                    </div>
                    <div class="card-body">
                        <?php if (empty($pastReservations)): ?>
                            <div class="alert alert-info mb-0">
                                <i class="fas fa-info-circle me-2"></i>No past reservations.
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
                                        <?php foreach ($pastReservations as $reservation): ?>
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
                                                            <div class="small text-muted"><?= htmlspecialchars($reservation['location']) ?></div>
                                                        </div>
                                                    </div>
                                                </td>
                                                <td>
                                                    <div>
                                                        <strong><?= htmlspecialchars($reservation['renter_name']) ?></strong>
                                                        <div class="small text-muted"><?= htmlspecialchars($reservation['renter_email']) ?></div>
                                                    </div>
                                                </td>
                                                <td>
                                                    <?= formatDate($reservation['start_date']) ?> to <br>
                                                    <?= formatDate($reservation['end_date']) ?>
                                                </td>
                                                <td><?= formatPrice($reservation['total_price']) ?></td>
                                                <td>
                                                    <span class="badge bg-<?= $reservation['status'] === 'completed' ? 'info' : 'secondary' ?>">
                                                        <?= ucfirst($reservation['status']) ?>
                                                    </span>
                                                    <?php if (isset($reservation['payment_status'])): ?>
                                                        <br>
                                                        <small class="text-muted">
                                                            Payment: <?= ucfirst($reservation['payment_status'] ?? 'n/a') ?>
                                                        </small>
                                                    <?php endif; ?>
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
            
            <!-- Cancelled/Rejected Reservations -->
            <div class="tab-pane fade" id="cancelled" role="tabpanel" aria-labelledby="cancelled-tab">
                <div class="card shadow-sm">
                    <div class="card-header">
                        <h5 class="mb-0">Cancelled/Rejected Reservations</h5>
                    </div>
                    <div class="card-body">
                        <?php if (empty($cancelledReservations)): ?>
                            <div class="alert alert-info mb-0">
                                <i class="fas fa-info-circle me-2"></i>No cancelled or rejected reservations.
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
                                        <?php foreach ($cancelledReservations as $reservation): ?>
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
                                                            <div class="small text-muted"><?= htmlspecialchars($reservation['location']) ?></div>
                                                        </div>
                                                    </div>
                                                </td>
                                                <td>
                                                    <div>
                                                        <strong><?= htmlspecialchars($reservation['renter_name']) ?></strong>
                                                        <div class="small text-muted"><?= htmlspecialchars($reservation['renter_email']) ?></div>
                                                    </div>
                                                </td>
                                                <td>
                                                    <?= formatDate($reservation['start_date']) ?> to <br>
                                                    <?= formatDate($reservation['end_date']) ?>
                                                </td>
                                                <td><?= formatPrice($reservation['total_price']) ?></td>
                                                <td>
                                                    <span class="badge bg-danger">
                                                        <?= ucfirst($reservation['status']) ?>
                                                    </span>
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
</div>

<?php include 'views/footer.php'; ?>
