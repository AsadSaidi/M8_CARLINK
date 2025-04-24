<?php
require_once 'models/Reservation.php';
require_once 'models/Car.php';
require_once 'models/Payment.php';

class ReservationController {
    private $db;
    private $reservationModel;
    private $carModel;
    private $paymentModel;

    public function __construct($db) {
        $this->db = $db;
        $this->reservationModel = new Reservation($db);
        $this->carModel = new Car($db);
        $this->paymentModel = new Payment($db);
    }

    /**
     * Create a new reservation
     */
    public function createReservation() {
        // Verify CSRF token
        if (!isset($_POST['csrf_token']) || !validateCSRFToken($_POST['csrf_token'])) {
            $_SESSION['alert'] = 'Invalid form submission';
            $_SESSION['alert_type'] = 'danger';
            redirect('explore');
            return;
        }

        // Check if user is logged in
        if (!isLoggedIn()) {
            $_SESSION['alert'] = 'You must be logged in to make a reservation';
            $_SESSION['alert_type'] = 'danger';
            redirect('login');
            return;
        }

        // Validate required fields
        $requiredFields = ['car_id', 'start_date', 'end_date', 'total_price', 'card_name', 'card_number', 'card_expiry', 'card_cvv'];
        
        foreach ($requiredFields as $field) {
            if (!isset($_POST[$field]) || empty($_POST[$field])) {
                $_SESSION['alert'] = 'All required fields must be filled';
                $_SESSION['alert_type'] = 'danger';
                redirect('book&car_id=' . $_POST['car_id'] . '&start_date=' . urlencode($_POST['start_date']) . '&end_date=' . urlencode($_POST['end_date']));
                return;
            }
        }

        // Sanitize and validate inputs
        $carId = (int)$_POST['car_id'];
        $startDate = sanitizeInput($_POST['start_date']);
        $endDate = sanitizeInput($_POST['end_date']);
        $totalPrice = (float)$_POST['total_price'];
        
        // Validate dates
        if (!$this->validateDates($startDate, $endDate)) {
            $_SESSION['alert'] = 'Invalid dates selected';
            $_SESSION['alert_type'] = 'danger';
            redirect('book&car_id=' . $carId . '&start_date=' . urlencode($startDate) . '&end_date=' . urlencode($endDate));
            return;
        }

        // Check if car exists and is available
        $car = $this->carModel->getCarById($carId);
        
        if (!$car || !$car['available']) {
            $_SESSION['alert'] = 'Car is not available for reservation';
            $_SESSION['alert_type'] = 'danger';
            redirect('explore');
            return;
        }

        // Check if user is trying to book their own car
        if ($car['owner_id'] == $_SESSION['user_id']) {
            $_SESSION['alert'] = 'You cannot book your own car';
            $_SESSION['alert_type'] = 'danger';
            redirect('car&id=' . $carId);
            return;
        }

        // Check if car is available for the selected dates
        $isAvailable = $this->carModel->checkAvailability($carId, $startDate, $endDate);
        
        if (!$isAvailable) {
            $_SESSION['alert'] = 'This car is not available for the selected dates';
            $_SESSION['alert_type'] = 'danger';
            redirect('car&id=' . $carId);
            return;
        }

        // Calculate price to ensure it matches the provided total price
        $priceCalculation = $this->reservationModel->calculatePrice($carId, $startDate, $endDate);
        
        if (!$priceCalculation['success'] || abs($priceCalculation['total_price'] - $totalPrice) > 0.01) {
            $_SESSION['alert'] = 'Price calculation error. Please try again.';
            $_SESSION['alert_type'] = 'danger';
            redirect('book&car_id=' . $carId . '&start_date=' . urlencode($startDate) . '&end_date=' . urlencode($endDate));
            return;
        }

        // Create reservation
        $result = $this->reservationModel->createReservation(
            $carId,
            $_SESSION['user_id'],
            $startDate,
            $endDate,
            $totalPrice
        );

        if (!$result['success']) {
            $_SESSION['alert'] = 'Failed to create reservation: ' . $result['message'];
            $_SESSION['alert_type'] = 'danger';
            redirect('book&car_id=' . $carId . '&start_date=' . urlencode($startDate) . '&end_date=' . urlencode($endDate));
            return;
        }

        $reservationId = $result['reservation_id'];

        // Process payment
        $cardData = [
            'name' => sanitizeInput($_POST['card_name']),
            'number' => sanitizeInput($_POST['card_number']),
            'expiry' => sanitizeInput($_POST['card_expiry']),
            'cvv' => sanitizeInput($_POST['card_cvv']),
        ];

        $paymentResult = $this->paymentModel->processPayment($reservationId, $totalPrice, $cardData);

        if ($paymentResult['success']) {
            $_SESSION['alert'] = 'Reservation completed successfully! Your booking is confirmed.';
            $_SESSION['alert_type'] = 'success';
            redirect('user-dashboard');
        } else {
            $_SESSION['alert'] = 'Payment failed: ' . $paymentResult['message'] . ' Your reservation is pending.';
            $_SESSION['alert_type'] = 'warning';
            redirect('user-dashboard');
        }
    }

    /**
     * Update reservation status
     */
    public function updateReservation() {
        // Verify CSRF token
        if (!isset($_POST['csrf_token']) || !validateCSRFToken($_POST['csrf_token'])) {
            $_SESSION['alert'] = 'Invalid form submission';
            $_SESSION['alert_type'] = 'danger';
            redirect(isOwner() ? 'manage-reservations' : 'user-dashboard');
            return;
        }

        // Check if user is logged in
        if (!isLoggedIn()) {
            $_SESSION['alert'] = 'You must be logged in to update a reservation';
            $_SESSION['alert_type'] = 'danger';
            redirect('login');
            return;
        }

        // Validate required fields
        if (!isset($_POST['reservation_id']) || !is_numeric($_POST['reservation_id']) || 
            !isset($_POST['action']) || empty($_POST['action'])) {
            $_SESSION['alert'] = 'Invalid request';
            $_SESSION['alert_type'] = 'danger';
            redirect(isOwner() ? 'manage-reservations' : 'user-dashboard');
            return;
        }

        $reservationId = (int)$_POST['reservation_id'];
        $action = sanitizeInput($_POST['action']);

        // Get the reservation details
        $reservation = $this->reservationModel->getReservationById($reservationId);
        
        if (!$reservation) {
            $_SESSION['alert'] = 'Reservation not found';
            $_SESSION['alert_type'] = 'danger';
            redirect(isOwner() ? 'manage-reservations' : 'user-dashboard');
            return;
        }

        // Validate action based on user role
        if (isOwner()) {
            // Owner actions
            if ($reservation['owner_id'] != $_SESSION['user_id']) {
                $_SESSION['alert'] = 'You do not have permission to update this reservation';
                $_SESSION['alert_type'] = 'danger';
                redirect('manage-reservations');
                return;
            }

            switch ($action) {
                case 'approve':
                    $newStatus = 'approved';
                    $result = $this->reservationModel->updateReservationStatus($reservationId, $_SESSION['user_id'], $newStatus);
                    $message = 'Reservation approved successfully';
                    break;
                    
                case 'reject':
                    $newStatus = 'rejected';
                    $result = $this->reservationModel->updateReservationStatus($reservationId, $_SESSION['user_id'], $newStatus);
                    $message = 'Reservation rejected';
                    break;
                    
                case 'complete':
                    $newStatus = 'completed';
                    $result = $this->reservationModel->updateReservationStatus($reservationId, $_SESSION['user_id'], $newStatus);
                    $message = 'Reservation marked as completed';
                    break;
                    
                default:
                    $_SESSION['alert'] = 'Invalid action';
                    $_SESSION['alert_type'] = 'danger';
                    redirect('manage-reservations');
                    return;
            }
        } else {
            // Renter actions
            if ($reservation['renter_id'] != $_SESSION['user_id']) {
                $_SESSION['alert'] = 'You do not have permission to update this reservation';
                $_SESSION['alert_type'] = 'danger';
                redirect('user-dashboard');
                return;
            }

            switch ($action) {
                case 'cancel':
                    $result = $this->reservationModel->cancelReservation($reservationId, $_SESSION['user_id']);
                    $message = 'Reservation cancelled successfully';
                    break;
                    
                default:
                    $_SESSION['alert'] = 'Invalid action';
                    $_SESSION['alert_type'] = 'danger';
                    redirect('user-dashboard');
                    return;
            }
        }

        if ($result) {
            $_SESSION['alert'] = $message;
            $_SESSION['alert_type'] = 'success';
        } else {
            $_SESSION['alert'] = 'Failed to update reservation. It may be in a state that cannot be changed.';
            $_SESSION['alert_type'] = 'danger';
        }

        redirect(isOwner() ? 'manage-reservations' : 'user-dashboard');
    }

    /**
     * Validate dates for reservation
     * 
     * @param string $startDate Start date (Y-m-d)
     * @param string $endDate End date (Y-m-d)
     * @return boolean True if dates are valid, false otherwise
     */
    private function validateDates($startDate, $endDate) {
        // Check date format
        $startDateObj = DateTime::createFromFormat('Y-m-d', $startDate);
        $endDateObj = DateTime::createFromFormat('Y-m-d', $endDate);
        
        if (!$startDateObj || !$endDateObj || $startDateObj->format('Y-m-d') !== $startDate || $endDateObj->format('Y-m-d') !== $endDate) {
            return false;
        }
        
        // Check if start date is in the past
        $today = new DateTime();
        $today->setTime(0, 0, 0); // Set time to midnight
        
        if ($startDateObj < $today) {
            return false;
        }
        
        // Check if end date is after start date
        if ($endDateObj < $startDateObj) {
            return false;
        }
        
        return true;
    }
}
?>
