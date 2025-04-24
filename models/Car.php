<?php
class Car {
    private $db;

    public function __construct($db) {
        $this->db = $db;
    }

    /**
     * Add a new car
     * 
     * @param int $ownerId Owner's user ID
     * @param array $carData Car details
     * @return array Success status and car ID or error message
     */
    public function addCar($ownerId, $carData) {
        try {
            $this->db->beginTransaction();
            
            // Insert car data
            $stmt = $this->db->prepare(
                "INSERT INTO cars (owner_id, make, model, year, fuel_type, transmission, price_per_day, 
                location, description, cylinders, displacement, power, city_mpg, highway_mpg) 
                VALUES (:owner_id, :make, :model, :year, :fuel_type, :transmission, :price_per_day, 
                :location, :description, :cylinders, :displacement, :power, :city_mpg, :highway_mpg)"
            );
            
            $params = [
                ':owner_id' => $ownerId,
                ':make' => $carData['make'],
                ':model' => $carData['model'],
                ':year' => $carData['year'],
                ':fuel_type' => $carData['fuel_type'],
                ':transmission' => $carData['transmission'],
                ':price_per_day' => $carData['price_per_day'],
                ':location' => $carData['location'],
                ':description' => $carData['description'] ?? null,
                ':cylinders' => $carData['cylinders'] ?? null,
                ':displacement' => $carData['displacement'] ?? null,
                ':power' => $carData['power'] ?? null,
                ':city_mpg' => $carData['city_mpg'] ?? null,
                ':highway_mpg' => $carData['highway_mpg'] ?? null
            ];
            
            $stmt->execute($params);
            $carId = $this->db->lastInsertId();
            
            // Process and store image paths
            if (isset($carData['images']) && is_array($carData['images'])) {
                $imageStmt = $this->db->prepare(
                    "INSERT INTO car_images (car_id, image_path, is_primary) 
                     VALUES (:car_id, :image_path, :is_primary)"
                );
                
                foreach ($carData['images'] as $index => $imagePath) {
                    $isPrimary = ($index === 0) ? 1 : 0;
                    $imageStmt->execute([
                        ':car_id' => $carId,
                        ':image_path' => $imagePath,
                        ':is_primary' => $isPrimary
                    ]);
                }
            }
            
            $this->db->commit();
            
            return [
                'success' => true,
                'car_id' => $carId
            ];
        } catch (PDOException $e) {
            $this->db->rollBack();
            return [
                'success' => false,
                'message' => 'Failed to add car: ' . $e->getMessage()
            ];
        }
    }

    /**
     * Get car by ID with images
     * 
     * @param int $carId Car ID
     * @return array|false Car data or false if not found
     */
    public function getCarById($carId) {
        try {
            // Get car details
            $stmt = $this->db->prepare(
                "SELECT c.*, u.name as owner_name, u.email as owner_email, u.phone as owner_phone 
                 FROM cars c 
                 JOIN users u ON c.owner_id = u.id 
                 WHERE c.id = :car_id"
            );
            $stmt->bindParam(':car_id', $carId);
            $stmt->execute();
            
            if ($stmt->rowCount() === 0) {
                return false;
            }
            
            $car = $stmt->fetch();
            
            // Get car images
            $imageStmt = $this->db->prepare(
                "SELECT id, image_path, is_primary 
                 FROM car_images 
                 WHERE car_id = :car_id 
                 ORDER BY is_primary DESC, id ASC"
            );
            $imageStmt->bindParam(':car_id', $carId);
            $imageStmt->execute();
            
            $car['images'] = $imageStmt->fetchAll();
            
            return $car;
        } catch (PDOException $e) {
            return false;
        }
    }

    /**
     * Search cars with various filters
     * 
     * @param array $filters Search filters
     * @param int $limit Results per page
     * @param int $offset Pagination offset
     * @return array Cars matching the search criteria
     */
    public function searchCars($filters = [], $limit = 10, $offset = 0) {
        try {
            $query = "SELECT c.*, u.name as owner_name, 
                     (SELECT image_path FROM car_images WHERE car_id = c.id AND is_primary = 1 LIMIT 1) as primary_image 
                     FROM cars c 
                     JOIN users u ON c.owner_id = u.id 
                     WHERE c.available = 1";
            $params = [];
            
            // Add filters
            if (!empty($filters['make'])) {
                $query .= " AND c.make = :make";
                $params[':make'] = $filters['make'];
            }
            
            if (!empty($filters['model'])) {
                $query .= " AND c.model = :model";
                $params[':model'] = $filters['model'];
            }
            
            if (!empty($filters['location'])) {
                $query .= " AND c.location LIKE :location";
                $params[':location'] = '%' . $filters['location'] . '%';
            }
            
            if (!empty($filters['min_price'])) {
                $query .= " AND c.price_per_day >= :min_price";
                $params[':min_price'] = $filters['min_price'];
            }
            
            if (!empty($filters['max_price'])) {
                $query .= " AND c.price_per_day <= :max_price";
                $params[':max_price'] = $filters['max_price'];
            }
            
            if (!empty($filters['fuel_type'])) {
                $query .= " AND c.fuel_type = :fuel_type";
                $params[':fuel_type'] = $filters['fuel_type'];
            }
            
            if (!empty($filters['transmission'])) {
                $query .= " AND c.transmission = :transmission";
                $params[':transmission'] = $filters['transmission'];
            }
            
            if (!empty($filters['year_min'])) {
                $query .= " AND c.year >= :year_min";
                $params[':year_min'] = $filters['year_min'];
            }
            
            if (!empty($filters['year_max'])) {
                $query .= " AND c.year <= :year_max";
                $params[':year_max'] = $filters['year_max'];
            }
            
            // Check availability if dates are provided
            if (!empty($filters['start_date']) && !empty($filters['end_date'])) {
                $query .= " AND c.id NOT IN (
                    SELECT car_id FROM reservations 
                    WHERE status IN ('approved', 'pending') 
                    AND (
                        (start_date <= :end_date AND end_date >= :start_date)
                    )
                )";
                $params[':start_date'] = $filters['start_date'];
                $params[':end_date'] = $filters['end_date'];
            }
            
            // Order by
            $orderBy = !empty($filters['order_by']) ? $filters['order_by'] : 'price_per_day';
            $order = !empty($filters['order']) && strtoupper($filters['order']) === 'DESC' ? 'DESC' : 'ASC';
            $query .= " ORDER BY c.$orderBy $order";
            
            // Pagination
            $query .= " LIMIT :limit OFFSET :offset";
            $params[':limit'] = $limit;
            $params[':offset'] = $offset;
            
            $stmt = $this->db->prepare($query);
            
            // Bind parameters
            foreach ($params as $key => $value) {
                $stmt->bindValue($key, $value);
            }
            
            $stmt->execute();
            return $stmt->fetchAll();
        } catch (PDOException $e) {
            return [];
        }
    }

    /**
     * Count total cars matching filters (for pagination)
     * 
     * @param array $filters Search filters
     * @return int Total count
     */
    public function countCars($filters = []) {
        try {
            $query = "SELECT COUNT(*) 
                      FROM cars c 
                      WHERE c.available = 1";
            $params = [];
            
            // Add filters (same as searchCars but for counting)
            if (!empty($filters['make'])) {
                $query .= " AND c.make = :make";
                $params[':make'] = $filters['make'];
            }
            
            if (!empty($filters['model'])) {
                $query .= " AND c.model = :model";
                $params[':model'] = $filters['model'];
            }
            
            if (!empty($filters['location'])) {
                $query .= " AND c.location LIKE :location";
                $params[':location'] = '%' . $filters['location'] . '%';
            }
            
            if (!empty($filters['min_price'])) {
                $query .= " AND c.price_per_day >= :min_price";
                $params[':min_price'] = $filters['min_price'];
            }
            
            if (!empty($filters['max_price'])) {
                $query .= " AND c.price_per_day <= :max_price";
                $params[':max_price'] = $filters['max_price'];
            }
            
            if (!empty($filters['fuel_type'])) {
                $query .= " AND c.fuel_type = :fuel_type";
                $params[':fuel_type'] = $filters['fuel_type'];
            }
            
            if (!empty($filters['transmission'])) {
                $query .= " AND c.transmission = :transmission";
                $params[':transmission'] = $filters['transmission'];
            }
            
            // Check availability if dates are provided
            if (!empty($filters['start_date']) && !empty($filters['end_date'])) {
                $query .= " AND c.id NOT IN (
                    SELECT car_id FROM reservations 
                    WHERE status IN ('approved', 'pending') 
                    AND (
                        (start_date <= :end_date AND end_date >= :start_date)
                    )
                )";
                $params[':start_date'] = $filters['start_date'];
                $params[':end_date'] = $filters['end_date'];
            }
            
            $stmt = $this->db->prepare($query);
            
            // Bind parameters
            foreach ($params as $key => $value) {
                $stmt->bindValue($key, $value);
            }
            
            $stmt->execute();
            return $stmt->fetchColumn();
        } catch (PDOException $e) {
            return 0;
        }
    }

    /**
     * Get cars by owner ID
     * 
     * @param int $ownerId Owner's user ID
     * @return array Owner's cars
     */
    public function getCarsByOwnerId($ownerId) {
        try {
            $stmt = $this->db->prepare(
                "SELECT c.*, 
                 (SELECT image_path FROM car_images WHERE car_id = c.id AND is_primary = 1 LIMIT 1) as primary_image,
                 (SELECT COUNT(*) FROM reservations WHERE car_id = c.id AND status IN ('pending', 'approved')) as active_reservations
                 FROM cars c 
                 WHERE c.owner_id = :owner_id 
                 ORDER BY c.created_at DESC"
            );
            $stmt->bindParam(':owner_id', $ownerId);
            $stmt->execute();
            
            return $stmt->fetchAll();
        } catch (PDOException $e) {
            return [];
        }
    }

    /**
     * Update car details
     * 
     * @param int $carId Car ID
     * @param int $ownerId Owner's user ID (for verification)
     * @param array $carData Updated car details
     * @return boolean Success status
     */
    public function updateCar($carId, $ownerId, $carData) {
        try {
            // Verify ownership
            $stmt = $this->db->prepare("SELECT owner_id FROM cars WHERE id = :id");
            $stmt->bindParam(':id', $carId);
            $stmt->execute();
            
            $car = $stmt->fetch();
            
            if (!$car || $car['owner_id'] != $ownerId) {
                return false;
            }
            
            $this->db->beginTransaction();
            
            // Update car data
            $query = "UPDATE cars SET ";
            $params = [];
            
            foreach ($carData as $key => $value) {
                if (in_array($key, ['make', 'model', 'year', 'fuel_type', 'transmission', 
                    'price_per_day', 'location', 'description', 'available', 
                    'cylinders', 'displacement', 'power', 'city_mpg', 'highway_mpg'])) {
                    $query .= "$key = :$key, ";
                    $params[":$key"] = $value;
                }
            }
            
            $query = rtrim($query, ', ') . " WHERE id = :id AND owner_id = :owner_id";
            $params[':id'] = $carId;
            $params[':owner_id'] = $ownerId;
            
            $updateStmt = $this->db->prepare($query);
            $updateStmt->execute($params);
            
            // Process new images if provided
            if (isset($carData['new_images']) && is_array($carData['new_images']) && !empty($carData['new_images'])) {
                $imageStmt = $this->db->prepare(
                    "INSERT INTO car_images (car_id, image_path, is_primary) 
                     VALUES (:car_id, :image_path, :is_primary)"
                );
                
                // Get existing image count to determine primary
                $countStmt = $this->db->prepare("SELECT COUNT(*) FROM car_images WHERE car_id = :car_id");
                $countStmt->bindParam(':car_id', $carId);
                $countStmt->execute();
                $imageCount = $countStmt->fetchColumn();
                
                foreach ($carData['new_images'] as $index => $imagePath) {
                    $isPrimary = ($imageCount === 0 && $index === 0) ? 1 : 0;
                    $imageStmt->execute([
                        ':car_id' => $carId,
                        ':image_path' => $imagePath,
                        ':is_primary' => $isPrimary
                    ]);
                }
            }
            
            // Remove images if specified
            if (isset($carData['remove_images']) && is_array($carData['remove_images'])) {
                $removeStmt = $this->db->prepare("DELETE FROM car_images WHERE id = :id AND car_id = :car_id");
                
                foreach ($carData['remove_images'] as $imageId) {
                    $removeStmt->execute([':id' => $imageId, ':car_id' => $carId]);
                }
                
                // If primary image was removed, set a new one
                $checkPrimaryStmt = $this->db->prepare(
                    "SELECT COUNT(*) FROM car_images WHERE car_id = :car_id AND is_primary = 1"
                );
                $checkPrimaryStmt->bindParam(':car_id', $carId);
                $checkPrimaryStmt->execute();
                
                if ($checkPrimaryStmt->fetchColumn() === 0) {
                    // Set first remaining image as primary
                    $newPrimaryStmt = $this->db->prepare(
                        "UPDATE car_images SET is_primary = 1 
                         WHERE id = (SELECT id FROM car_images WHERE car_id = :car_id LIMIT 1)"
                    );
                    $newPrimaryStmt->bindParam(':car_id', $carId);
                    $newPrimaryStmt->execute();
                }
            }
            
            $this->db->commit();
            return true;
        } catch (PDOException $e) {
            $this->db->rollBack();
            return false;
        }
    }

    /**
     * Delete a car
     * 
     * @param int $carId Car ID
     * @param int $ownerId Owner's user ID (for verification)
     * @return boolean Success status
     */
    public function deleteCar($carId, $ownerId) {
        try {
            // Verify ownership and check for active reservations
            $stmt = $this->db->prepare(
                "SELECT c.owner_id,
                 (SELECT COUNT(*) FROM reservations 
                  WHERE car_id = c.id AND status IN ('pending', 'approved')) as active_reservations
                 FROM cars c
                 WHERE c.id = :id"
            );
            $stmt->bindParam(':id', $carId);
            $stmt->execute();
            
            $car = $stmt->fetch();
            
            if (!$car || $car['owner_id'] != $ownerId) {
                return false;
            }
            
            // Don't allow deletion if there are active reservations
            if ($car['active_reservations'] > 0) {
                return false;
            }
            
            $this->db->beginTransaction();
            
            // Delete car images (on cascade will handle this, but let's be explicit)
            $deleteImagesStmt = $this->db->prepare("DELETE FROM car_images WHERE car_id = :car_id");
            $deleteImagesStmt->bindParam(':car_id', $carId);
            $deleteImagesStmt->execute();
            
            // Delete reviews for reservations of this car (on cascade will handle this, but let's be explicit)
            $deleteReviewsStmt = $this->db->prepare(
                "DELETE FROM reviews WHERE reservation_id IN (
                    SELECT id FROM reservations WHERE car_id = :car_id
                )"
            );
            $deleteReviewsStmt->bindParam(':car_id', $carId);
            $deleteReviewsStmt->execute();
            
            // Delete payments for reservations of this car (on cascade will handle this, but let's be explicit)
            $deletePaymentsStmt = $this->db->prepare(
                "DELETE FROM payments WHERE reservation_id IN (
                    SELECT id FROM reservations WHERE car_id = :car_id
                )"
            );
            $deletePaymentsStmt->bindParam(':car_id', $carId);
            $deletePaymentsStmt->execute();
            
            // Delete reservations (on cascade will handle this, but let's be explicit)
            $deleteReservationsStmt = $this->db->prepare("DELETE FROM reservations WHERE car_id = :car_id");
            $deleteReservationsStmt->bindParam(':car_id', $carId);
            $deleteReservationsStmt->execute();
            
            // Finally, delete the car
            $deleteCarStmt = $this->db->prepare("DELETE FROM cars WHERE id = :id AND owner_id = :owner_id");
            $deleteCarStmt->bindParam(':id', $carId);
            $deleteCarStmt->bindParam(':owner_id', $ownerId);
            $deleteCarStmt->execute();
            
            $this->db->commit();
            return true;
        } catch (PDOException $e) {
            $this->db->rollBack();
            return false;
        }
    }

    /**
     * Get featured cars for home page
     * 
     * @param int $limit Number of cars to return
     * @return array Featured cars
     */
    public function getFeaturedCars($limit = 6) {
        try {
            $stmt = $this->db->prepare(
                "SELECT c.*, 
                 (SELECT image_path FROM car_images WHERE car_id = c.id AND is_primary = 1 LIMIT 1) as primary_image 
                 FROM cars c 
                 WHERE c.available = 1 
                 ORDER BY c.price_per_day ASC 
                 LIMIT :limit"
            );
            $stmt->bindParam(':limit', $limit, PDO::PARAM_INT);
            $stmt->execute();
            
            return $stmt->fetchAll();
        } catch (PDOException $e) {
            return [];
        }
    }

    /**
     * Check if a car is available for a date range
     * 
     * @param int $carId Car ID
     * @param string $startDate Start date (YYYY-MM-DD)
     * @param string $endDate End date (YYYY-MM-DD)
     * @return boolean Whether the car is available
     */
    public function isAvailable($carId, $startDate, $endDate) {
        try {
            // Check if car exists and is marked as available
            $carStmt = $this->db->prepare("SELECT available FROM cars WHERE id = :car_id");
            $carStmt->bindParam(':car_id', $carId);
            $carStmt->execute();
            
            $car = $carStmt->fetch();
            
            if (!$car || !$car['available']) {
                return false;
            }
            
            // Check for overlapping reservations
            $stmt = $this->db->prepare(
                "SELECT COUNT(*) FROM reservations 
                 WHERE car_id = :car_id AND status IN ('approved', 'pending') 
                 AND (
                     (start_date <= :end_date AND end_date >= :start_date)
                 )"
            );
            
            $stmt->execute([
                ':car_id' => $carId,
                ':start_date' => $startDate,
                ':end_date' => $endDate
            ]);
            
            $overlapping = $stmt->fetchColumn();
            
            return $overlapping === 0;
        } catch (PDOException $e) {
            return false;
        }
    }

    /**
     * Get unique car makes for filters
     * 
     * @return array List of unique car makes
     */
    public function getUniqueMakes() {
        try {
            $stmt = $this->db->query(
                "SELECT DISTINCT make FROM cars ORDER BY make ASC"
            );
            
            return $stmt->fetchAll(PDO::FETCH_COLUMN);
        } catch (PDOException $e) {
            return [];
        }
    }

    /**
     * Get unique fuel types for filters
     * 
     * @return array List of unique fuel types
     */
    public function getUniqueFuelTypes() {
        try {
            $stmt = $this->db->query(
                "SELECT DISTINCT fuel_type FROM cars ORDER BY fuel_type ASC"
            );
            
            return $stmt->fetchAll(PDO::FETCH_COLUMN);
        } catch (PDOException $e) {
            return [];
        }
    }

    /**
     * Get unique transmission types for filters
     * 
     * @return array List of unique transmission types
     */
    public function getUniqueTransmissions() {
        try {
            $stmt = $this->db->query(
                "SELECT DISTINCT transmission FROM cars ORDER BY transmission ASC"
            );
            
            return $stmt->fetchAll(PDO::FETCH_COLUMN);
        } catch (PDOException $e) {
            return [];
        }
    }
}