<?php
class Payment {
    private $db;

    public function __construct($db) {
        $this->db = $db;
    }

    /**
     * Create a new payment record
     * 
     * @param int $reservationId Reservation ID
     * @param float $amount Payment amount
     * @param string $paymentMethod Payment method
     * @param string $transactionId Payment transaction ID
     * @return array Success status and payment ID or error message
     */
    public function createPayment($reservationId, $amount, $paymentMethod = null, $transactionId = null) {
        try {
            // Check if reservation exists
            $reservationStmt = $this->db->prepare("SELECT id, total_price FROM reservations WHERE id = :id");
            $reservationStmt->bindParam(':id', $reservationId);
            $reservationStmt->execute();
            
            $reservation = $reservationStmt->fetch();
            
            if (!$reservation) {
                return [
                    'success' => false,
                    'message' => 'Reservation not found'
                ];
            }
            
            // Check if payment already exists
            $checkStmt = $this->db->prepare("SELECT id FROM payments WHERE reservation_id = :reservation_id");
            $checkStmt->bindParam(':reservation_id', $reservationId);
            $checkStmt->execute();
            
            if ($checkStmt->rowCount() > 0) {
                return [
                    'success' => false,
                    'message' => 'Payment already exists for this reservation'
                ];
            }
            
            // Verify amount
            if ($amount != $reservation['total_price']) {
                return [
                    'success' => false,
                    'message' => 'Payment amount does not match reservation price'
                ];
            }
            
            // Insert payment
            $stmt = $this->db->prepare(
                "INSERT INTO payments (reservation_id, amount, status, payment_method, transaction_id) 
                 VALUES (:reservation_id, :amount, :status, :payment_method, :transaction_id) 
                 RETURNING id"
            );
            
            $status = $transactionId ? 'completed' : 'pending';
            
            $stmt->bindParam(':reservation_id', $reservationId);
            $stmt->bindParam(':amount', $amount);
            $stmt->bindParam(':status', $status);
            $stmt->bindParam(':payment_method', $paymentMethod);
            $stmt->bindParam(':transaction_id', $transactionId);
            $stmt->execute();
            
            $paymentId = $stmt->fetchColumn();
            
            return [
                'success' => true,
                'payment_id' => $paymentId,
                'status' => $status
            ];
        } catch (PDOException $e) {
            return [
                'success' => false,
                'message' => 'Failed to create payment: ' . $e->getMessage()
            ];
        }
    }

    /**
     * Get payment by reservation ID
     * 
     * @param int $reservationId Reservation ID
     * @return array|false Payment data or false if not found
     */
    public function getPaymentByReservationId($reservationId) {
        try {
            $stmt = $this->db->prepare(
                "SELECT * FROM payments WHERE reservation_id = :reservation_id"
            );
            $stmt->bindParam(':reservation_id', $reservationId);
            $stmt->execute();
            
            return $stmt->fetch();
        } catch (PDOException $e) {
            return false;
        }
    }

    /**
     * Update payment status
     * 
     * @param int $paymentId Payment ID
     * @param string $status New status ('pending', 'completed', 'failed', 'refunded')
     * @param string $transactionId Payment transaction ID (for 'completed' status)
     * @return boolean Success status
     */
    public function updatePaymentStatus($paymentId, $status, $transactionId = null) {
        try {
            $query = "UPDATE payments SET status = :status";
            $params = [':status' => $status, ':id' => $paymentId];
            
            if ($transactionId && $status === 'completed') {
                $query .= ", transaction_id = :transaction_id";
                $params[':transaction_id'] = $transactionId;
            }
            
            $query .= " WHERE id = :id";
            
            $stmt = $this->db->prepare($query);
            return $stmt->execute($params);
        } catch (PDOException $e) {
            return false;
        }
    }

    /**
     * Process payment (simulation)
     * 
     * @param int $reservationId Reservation ID
     * @param float $amount Payment amount
     * @param array $cardData Credit card data
     * @return array Success status and payment details
     */
    public function processPayment($reservationId, $amount, $cardData) {
        try {
            // This would normally include real payment processing
            // For now, we'll just simulate payment
            
            // Validate card data (basic validation)
            if (empty($cardData['number']) || strlen($cardData['number']) < 15) {
                return [
                    'success' => false,
                    'message' => 'Invalid card number'
                ];
            }
            
            if (empty($cardData['expiry']) || !preg_match('/^\d{2}\/\d{2}$/', $cardData['expiry'])) {
                return [
                    'success' => false,
                    'message' => 'Invalid expiry date'
                ];
            }
            
            if (empty($cardData['cvv']) || !preg_match('/^\d{3,4}$/', $cardData['cvv'])) {
                return [
                    'success' => false,
                    'message' => 'Invalid CVV'
                ];
            }
            
            // Generate a fake transaction ID
            $transactionId = 'TX-' . strtoupper(bin2hex(random_bytes(8)));
            
            // Create payment record
            $result = $this->createPayment(
                $reservationId, 
                $amount, 
                'credit_card', 
                $transactionId
            );
            
            if (!$result['success']) {
                return $result;
            }
            
            // Update reservation status to approved
            $this->db->prepare(
                "UPDATE reservations SET status = 'approved' WHERE id = :id"
            )->execute([':id' => $reservationId]);
            
            return [
                'success' => true,
                'payment_id' => $result['payment_id'],
                'transaction_id' => $transactionId,
                'status' => 'completed',
                'amount' => $amount
            ];
        } catch (PDOException $e) {
            return [
                'success' => false,
                'message' => 'Payment processing failed: ' . $e->getMessage()
            ];
        }
    }

    /**
     * Get payments by user ID (for both owners and renters)
     * 
     * @param int $userId User ID
     * @param string $role User role ('owner' or 'renter')
     * @return array User's payments
     */
    public function getPaymentsByUserId($userId, $role) {
        try {
            $query = "SELECT p.*, 
                      r.start_date, r.end_date, r.status as reservation_status,
                      c.make, c.model, c.year";
            
            if ($role === 'renter') {
                $query .= ", u.name as owner_name
                            FROM payments p
                            JOIN reservations r ON p.reservation_id = r.id
                            JOIN cars c ON r.car_id = c.id
                            JOIN users u ON c.owner_id = u.id
                            WHERE r.renter_id = :user_id";
            } else {
                $query .= ", u.name as renter_name
                            FROM payments p
                            JOIN reservations r ON p.reservation_id = r.id
                            JOIN cars c ON r.car_id = c.id
                            JOIN users u ON r.renter_id = u.id
                            WHERE c.owner_id = :user_id";
            }
            
            $query .= " ORDER BY p.created_at DESC";
            
            $stmt = $this->db->prepare($query);
            $stmt->bindParam(':user_id', $userId);
            $stmt->execute();
            
            return $stmt->fetchAll();
        } catch (PDOException $e) {
            return [];
        }
    }
}
?>
