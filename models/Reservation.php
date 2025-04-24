<?php
class Reservation {
    private $db;

    public function __construct($db) {
        $this->db = $db;
    }

    /**
     * Create a new reservation
     * 
     * @param int $carId Car ID
     * @param int $renterId Renter's user ID
     * @param string $startDate Start date (Y-m-d)
     * @param string $endDate End date (Y-m-d)
     * @param float $totalPrice Total price for the reservation
     * @return array Success status and reservation ID or error message
     */
    public function createReservation($carId, $renterId, $startDate, $endDate, $totalPrice) {
        try {
            // Check if car exists and is available
            $carStmt = $this->db->prepare("SELECT owner_id, available FROM cars WHERE id = :car_id");
            $carStmt->bindParam(':car_id', $carId);
            $carStmt->execute();
            
            $car = $carStmt->fetch();
            
            if (!$car || !$car['available']) {
                return [
                    'success' => false,
                    'message' => 'Car is not available for reservation'
                ];
            }
            
            // Check if dates are valid
            if (strtotime($startDate) > strtotime($endDate)) {
                return [
                    'success' => false,
                    'message' => 'End date must be after start date'
                ];
            }
            
            // Check if car is already booked for the requested dates
            $availStmt = $this->db->prepare(
                "SELECT COUNT(*) FROM reservations 
                 WHERE car_id = :car_id 
                 AND status IN ('pending', 'approved') 
                 AND (
                     (start_date <= :end_date AND end_date >= :start_date)
                 )"
            );
            $availStmt->bindParam(':car_id', $carId);
            $availStmt->bindParam(':start_date', $startDate);
            $availStmt->bindParam(':end_date', $endDate);
            $availStmt->execute();
            
            if ($availStmt->fetchColumn() > 0) {
                return [
                    'success' => false,
                    'message' => 'Car is not available for the selected dates'
                ];
            }
            
            // Create reservation
            $stmt = $this->db->prepare(
                "INSERT INTO reservations (car_id, renter_id, start_date, end_date, total_price, status) 
                 VALUES (:car_id, :renter_id, :start_date, :end_date, :total_price, 'pending') 
                 RETURNING id"
            );
            
            $stmt->bindParam(':car_id', $carId);
            $stmt->bindParam(':renter_id', $renterId);
            $stmt->bindParam(':start_date', $startDate);
            $stmt->bindParam(':end_date', $endDate);
            $stmt->bindParam(':total_price', $totalPrice);
            $stmt->execute();
            
            $reservationId = $stmt->fetchColumn();
            
            return [
                'success' => true,
                'reservation_id' => $reservationId,
                'owner_id' => $car['owner_id']
            ];
        } catch (PDOException $e) {
            return [
                'success' => false,
                'message' => 'Failed to create reservation: ' . $e->getMessage()
            ];
        }
    }

    /**
     * Get a reservation by ID
     * 
     * @param int $reservationId Reservation ID
     * @return array|false Reservation data or false if not found
     */
    public function getReservationById($reservationId) {
        try {
            $stmt = $this->db->prepare(
                "SELECT r.*, 
                 c.make, c.model, c.year, c.price_per_day, 
                 c.location, c.owner_id,
                 o.name as owner_name, o.email as owner_email,
                 re.name as renter_name, re.email as renter_email,
                 (SELECT image_path FROM car_images WHERE car_id = c.id AND is_primary = true LIMIT 1) as car_image
                 FROM reservations r
                 JOIN cars c ON r.car_id = c.id
                 JOIN users o ON c.owner_id = o.id
                 JOIN users re ON r.renter_id = re.id
                 WHERE r.id = :id"
            );
            $stmt->bindParam(':id', $reservationId);
            $stmt->execute();
            
            return $stmt->fetch();
        } catch (PDOException $e) {
            return false;
        }
    }

    /**
     * Get reservations by renter ID
     * 
     * @param int $renterId Renter's user ID
     * @return array Renter's reservations
     */
    public function getReservationsByRenterId($renterId) {
        try {
            $stmt = $this->db->prepare(
                "SELECT r.*, 
                 c.make, c.model, c.year, c.price_per_day, c.location,
                 o.name as owner_name,
                 (SELECT image_path FROM car_images WHERE car_id = c.id AND is_primary = true LIMIT 1) as car_image,
                 (SELECT status FROM payments WHERE reservation_id = r.id LIMIT 1) as payment_status
                 FROM reservations r
                 JOIN cars c ON r.car_id = c.id
                 JOIN users o ON c.owner_id = o.id
                 WHERE r.renter_id = :renter_id
                 ORDER BY r.created_at DESC"
            );
            $stmt->bindParam(':renter_id', $renterId);
            $stmt->execute();
            
            return $stmt->fetchAll();
        } catch (PDOException $e) {
            return [];
        }
    }

    /**
     * Get reservations for cars owned by a specific user
     * 
     * @param int $ownerId Owner's user ID
     * @return array Reservations for owner's cars
     */
    public function getReservationsByOwnerId($ownerId) {
        try {
            $stmt = $this->db->prepare(
                "SELECT r.*, 
                 c.make, c.model, c.year, c.price_per_day, c.location,
                 re.name as renter_name, re.email as renter_email, re.phone as renter_phone,
                 (SELECT image_path FROM car_images WHERE car_id = c.id AND is_primary = true LIMIT 1) as car_image,
                 (SELECT status FROM payments WHERE reservation_id = r.id LIMIT 1) as payment_status
                 FROM reservations r
                 JOIN cars c ON r.car_id = c.id
                 JOIN users re ON r.renter_id = re.id
                 WHERE c.owner_id = :owner_id
                 ORDER BY r.created_at DESC"
            );
            $stmt->bindParam(':owner_id', $ownerId);
            $stmt->execute();
            
            return $stmt->fetchAll();
        } catch (PDOException $e) {
            return [];
        }
    }

    /**
     * Update reservation status
     * 
     * @param int $reservationId Reservation ID
     * @param int $ownerId Owner's user ID (for verification)
     * @param string $status New status ('approved', 'rejected', 'cancelled', 'completed')
     * @return boolean Success status
     */
    public function updateReservationStatus($reservationId, $ownerId, $status) {
        try {
            // Verify ownership
            $stmt = $this->db->prepare(
                "SELECT c.owner_id, r.status 
                 FROM reservations r
                 JOIN cars c ON r.car_id = c.id
                 WHERE r.id = :id"
            );
            $stmt->bindParam(':id', $reservationId);
            $stmt->execute();
            
            $reservation = $stmt->fetch();
            
            if (!$reservation || $reservation['owner_id'] != $ownerId) {
                return false;
            }
            
            // Some status transitions are not allowed
            if ($reservation['status'] === 'completed' || 
                $reservation['status'] === 'rejected' && $status !== 'rejected') {
                return false;
            }
            
            // Update status
            $updateStmt = $this->db->prepare(
                "UPDATE reservations SET status = :status WHERE id = :id"
            );
            $updateStmt->bindParam(':id', $reservationId);
            $updateStmt->bindParam(':status', $status);
            
            return $updateStmt->execute();
        } catch (PDOException $e) {
            return false;
        }
    }

    /**
     * Cancel a reservation as a renter
     * 
     * @param int $reservationId Reservation ID
     * @param int $renterId Renter's user ID (for verification)
     * @return boolean Success status
     */
    public function cancelReservation($reservationId, $renterId) {
        try {
            // Verify ownership
            $stmt = $this->db->prepare(
                "SELECT renter_id, status, start_date 
                 FROM reservations 
                 WHERE id = :id"
            );
            $stmt->bindParam(':id', $reservationId);
            $stmt->execute();
            
            $reservation = $stmt->fetch();
            
            if (!$reservation || $reservation['renter_id'] != $renterId) {
                return false;
            }
            
            // Can't cancel completed reservations
            if ($reservation['status'] === 'completed') {
                return false;
            }
            
            // Update status
            $updateStmt = $this->db->prepare(
                "UPDATE reservations SET status = 'cancelled' WHERE id = :id"
            );
            $updateStmt->bindParam(':id', $reservationId);
            
            return $updateStmt->execute();
        } catch (PDOException $e) {
            return false;
        }
    }

    /**
     * Calculate reservation price
     * 
     * @param int $carId Car ID
     * @param string $startDate Start date (Y-m-d)
     * @param string $endDate End date (Y-m-d)
     * @return array Price calculation details
     */
    public function calculatePrice($carId, $startDate, $endDate) {
        try {
            // Get car price
            $stmt = $this->db->prepare("SELECT price_per_day FROM cars WHERE id = :car_id");
            $stmt->bindParam(':car_id', $carId);
            $stmt->execute();
            
            $car = $stmt->fetch();
            
            if (!$car) {
                return [
                    'success' => false,
                    'message' => 'Car not found'
                ];
            }
            
            // Calculate number of days
            $startDateTime = new DateTime($startDate);
            $endDateTime = new DateTime($endDate);
            $interval = $startDateTime->diff($endDateTime);
            $days = $interval->days + 1; // Including both start and end days
            
            if ($days < 1) {
                $days = 1; // Minimum 1 day
            }
            
            $pricePerDay = $car['price_per_day'];
            $subtotal = $days * $pricePerDay;
            
            // Apply discount for longer rentals
            $discount = 0;
            $discountRate = 0;
            
            if ($days >= 7 && $days < 14) {
                $discountRate = 0.05; // 5% discount for 7-13 days
            } elseif ($days >= 14 && $days < 30) {
                $discountRate = 0.10; // 10% discount for 14-29 days
            } elseif ($days >= 30) {
                $discountRate = 0.15; // 15% discount for 30+ days
            }
            
            $discount = $subtotal * $discountRate;
            $totalPrice = $subtotal - $discount;
            
            return [
                'success' => true,
                'price_per_day' => $pricePerDay,
                'days' => $days,
                'subtotal' => $subtotal,
                'discount_rate' => $discountRate,
                'discount' => $discount,
                'total_price' => $totalPrice
            ];
        } catch (PDOException $e) {
            return [
                'success' => false,
                'message' => 'Failed to calculate price: ' . $e->getMessage()
            ];
        }
    }

    /**
     * Get upcoming reservations for a user
     * 
     * @param int $userId User ID
     * @param string $role User role ('owner' or 'renter')
     * @param int $limit Maximum number of reservations to return
     * @return array Upcoming reservations
     */
    public function getUpcomingReservations($userId, $role, $limit = 5) {
        try {
            $query = "SELECT r.*, 
                      c.make, c.model, c.year, c.location, 
                      (SELECT image_path FROM car_images WHERE car_id = c.id AND is_primary = true LIMIT 1) as car_image";
            
            if ($role === 'renter') {
                $query .= ", u.name as owner_name FROM reservations r
                            JOIN cars c ON r.car_id = c.id
                            JOIN users u ON c.owner_id = u.id
                            WHERE r.renter_id = :user_id";
            } else {
                $query .= ", u.name as renter_name FROM reservations r
                            JOIN cars c ON r.car_id = c.id
                            JOIN users u ON r.renter_id = u.id
                            WHERE c.owner_id = :user_id";
            }
            
            $query .= " AND r.status = 'approved'
                       AND r.start_date >= CURRENT_DATE
                       ORDER BY r.start_date ASC
                       LIMIT :limit";
            
            $stmt = $this->db->prepare($query);
            $stmt->bindParam(':user_id', $userId);
            $stmt->bindParam(':limit', $limit, PDO::PARAM_INT);
            $stmt->execute();
            
            return $stmt->fetchAll();
        } catch (PDOException $e) {
            return [];
        }
    }
}
?>
