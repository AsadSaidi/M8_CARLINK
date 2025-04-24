<?php
require_once 'models/Payment.php';
require_once 'models/Reservation.php';

class PaymentController {
    private $db;
    private $paymentModel;
    private $reservationModel;

    public function __construct($db) {
        $this->db = $db;
        $this->paymentModel = new Payment($db);
        $this->reservationModel = new Reservation($db);
    }

    /**
     * Process payment for a reservation
     */
    public function processPayment() {
        // Verify CSRF token
        if (!isset($_POST['csrf_token']) || !validateCSRFToken($_POST['csrf_token'])) {
            $_SESSION['alert'] = 'Invalid form submission';
            $_SESSION['alert_type'] = 'danger';
            redirect('user-dashboard');
            return;
        }

        // Check if user is logged in
        if (!isLoggedIn()) {
            $_SESSION['alert'] = 'You must be logged in to process a payment';
            $_SESSION['alert_type'] = 'danger';
            redirect('login');
            return;
        }

        // Validate required fields
        $requiredFields = ['reservation_id', 'amount', 'card_name', 'card_number', 'card_expiry', 'card_cvv'];
        
        foreach ($requiredFields as $field) {
            if (!isset($_POST[$field]) || empty($_POST[$field])) {
                $_SESSION['alert'] = 'All payment fields are required';
                $_SESSION['alert_type'] = 'danger';
                redirect('user-dashboard');
                return;
            }
        }

        // Sanitize and validate inputs
        $reservationId = (int)$_POST['reservation_id'];
        $amount = (float)$_POST['amount'];

        // Validate credit card inputs
        $cardName = sanitizeInput($_POST['card_name']);
        $cardNumber = sanitizeInput($_POST['card_number']);
        $cardExpiry = sanitizeInput($_POST['card_expiry']);
        $cardCvv = sanitizeInput($_POST['card_cvv']);

        // Validate card number (basic validation)
        if (!preg_match('/^[0-9]{13,19}$/', $cardNumber)) {
            $_SESSION['alert'] = 'Invalid card number';
            $_SESSION['alert_type'] = 'danger';
            redirect('user-dashboard');
            return;
        }

        // Validate expiry date (MM/YY format)
        if (!preg_match('/^(0[1-9]|1[0-2])\/[0-9]{2}$/', $cardExpiry)) {
            $_SESSION['alert'] = 'Invalid card expiry date. Use MM/YY format.';
            $_SESSION['alert_type'] = 'danger';
            redirect('user-dashboard');
            return;
        }

        // Validate CVV (3-4 digits)
        if (!preg_match('/^[0-9]{3,4}$/', $cardCvv)) {
            $_SESSION['alert'] = 'Invalid CVV code';
            $_SESSION['alert_type'] = 'danger';
            redirect('user-dashboard');
            return;
        }

        // Get the reservation details
        $reservation = $this->reservationModel->getReservationById($reservationId);
        
        if (!$reservation) {
            $_SESSION['alert'] = 'Reservation not found';
            $_SESSION['alert_type'] = 'danger';
            redirect('user-dashboard');
            return;
        }

        // Check if the user is the renter
        if ($reservation['renter_id'] != $_SESSION['user_id']) {
            $_SESSION['alert'] = 'You do not have permission to pay for this reservation';
            $_SESSION['alert_type'] = 'danger';
            redirect('user-dashboard');
            return;
        }

        // Check if the amount matches the reservation total price
        if (abs($amount - $reservation['total_price']) > 0.01) {
            $_SESSION['alert'] = 'Payment amount does not match reservation price';
            $_SESSION['alert_type'] = 'danger';
            redirect('user-dashboard');
            return;
        }

        // Create card data array for payment processing
        $cardData = [
            'name' => $cardName,
            'number' => $cardNumber,
            'expiry' => $cardExpiry,
            'cvv' => $cardCvv
        ];

        // Process the payment
        $result = $this->paymentModel->processPayment($reservationId, $amount, $cardData);

        if ($result['success']) {
            $_SESSION['alert'] = 'Payment processed successfully! Your booking is confirmed.';
            $_SESSION['alert_type'] = 'success';
        } else {
            $_SESSION['alert'] = 'Payment processing failed: ' . $result['message'];
            $_SESSION['alert_type'] = 'danger';
        }

        redirect('user-dashboard');
    }

    /**
     * Handle payment webhook (for external payment processors - not implemented)
     */
    public function handlePaymentWebhook() {
        // This would handle callbacks from payment processors like Stripe
        // For now, we're using the simulated payment system

        // Example webhook handling code:
        /*
        // Get the input
        $payload = file_get_contents('php://input');
        $event = json_decode($payload, true);

        // Verify webhook signature
        $signature = $_SERVER['HTTP_STRIPE_SIGNATURE'];
        $secret = 'webhook_secret_key';
        
        try {
            // Verify signature
            // For real implementation, use the Stripe SDK or equivalent
            
            // Process the event
            if ($event['type'] === 'payment_intent.succeeded') {
                $paymentIntent = $event['data']['object'];
                $reservationId = $paymentIntent['metadata']['reservation_id'];
                $amount = $paymentIntent['amount'] / 100; // Convert from cents
                $transactionId = $paymentIntent['id'];
                
                // Update payment status in database
                $this->paymentModel->updatePaymentStatus(
                    $reservationId,
                    'completed',
                    $transactionId
                );
                
                // Update reservation status
                $this->reservationModel->updateReservationStatus(
                    $reservationId,
                    null, // For webhooks, no user ID check
                    'approved'
                );
            }
            
            http_response_code(200);
            echo json_encode(['status' => 'success']);
            
        } catch (Exception $e) {
            http_response_code(400);
            echo json_encode(['error' => $e->getMessage()]);
        }
        */
    }

    /**
     * Refund a payment (not implemented in the UI but added for completeness)
     */
    public function refundPayment() {
        // Verify CSRF token
        if (!isset($_POST['csrf_token']) || !validateCSRFToken($_POST['csrf_token'])) {
            $_SESSION['alert'] = 'Invalid form submission';
            $_SESSION['alert_type'] = 'danger';
            redirect(isOwner() ? 'manage-reservations' : 'user-dashboard');
            return;
        }

        // Check if user is logged in
        if (!isLoggedIn() || !isOwner()) {
            $_SESSION['alert'] = 'You must be logged in as an owner to process refunds';
            $_SESSION['alert_type'] = 'danger';
            redirect('login');
            return;
        }

        // Validate payment ID
        if (!isset($_POST['payment_id']) || !is_numeric($_POST['payment_id'])) {
            $_SESSION['alert'] = 'Invalid payment ID';
            $_SESSION['alert_type'] = 'danger';
            redirect('manage-reservations');
            return;
        }

        $paymentId = (int)$_POST['payment_id'];

        // Get payment details
        // This would need a getPaymentById method in the Payment model
        // $payment = $this->paymentModel->getPaymentById($paymentId);
        
        // For now, let's assume we have the payment details
        $payment = [
            'id' => $paymentId,
            'reservation_id' => 1,
            'amount' => 100.00,
            'status' => 'completed'
        ];
        
        if (!$payment) {
            $_SESSION['alert'] = 'Payment not found';
            $_SESSION['alert_type'] = 'danger';
            redirect('manage-reservations');
            return;
        }

        // Check if payment can be refunded (can only refund completed payments)
        if ($payment['status'] !== 'completed') {
            $_SESSION['alert'] = 'This payment cannot be refunded';
            $_SESSION['alert_type'] = 'danger';
            redirect('manage-reservations');
            return;
        }

        // Process refund
        // This would interface with the payment processor API
        // For our simulation, we'll just update the status
        
        // Update payment status
        $result = $this->paymentModel->updatePaymentStatus(
            $paymentId,
            'refunded'
        );

        if ($result) {
            $_SESSION['alert'] = 'Payment refunded successfully';
            $_SESSION['alert_type'] = 'success';
        } else {
            $_SESSION['alert'] = 'Failed to refund payment';
            $_SESSION['alert_type'] = 'danger';
        }

        redirect('manage-reservations');
    }
}
?>
