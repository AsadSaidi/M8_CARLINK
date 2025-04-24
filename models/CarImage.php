<?php
class CarImage {
    private $db;

    public function __construct($db) {
        $this->db = $db;
    }

    /**
     * Add a new car image
     * 
     * @param int $carId Car ID
     * @param string $imagePath Path to the image
     * @param bool $isPrimary Whether this is the primary image
     * @return bool Success status
     */
    public function addImage($carId, $imagePath, $isPrimary = false) {
        try {
            $stmt = $this->db->prepare(
                "INSERT INTO car_images (car_id, image_path, is_primary) 
                 VALUES (:car_id, :image_path, :is_primary)"
            );
            
            $stmt->execute([
                ':car_id' => $carId,
                ':image_path' => $imagePath,
                ':is_primary' => $isPrimary ? 1 : 0
            ]);
            
            return true;
        } catch (PDOException $e) {
            return false;
        }
    }

    /**
     * Get images for a car
     * 
     * @param int $carId Car ID
     * @return array Images for the car
     */
    public function getImagesForCar($carId) {
        try {
            $stmt = $this->db->prepare(
                "SELECT id, image_path, is_primary 
                 FROM car_images 
                 WHERE car_id = :car_id 
                 ORDER BY is_primary DESC, id ASC"
            );
            
            $stmt->execute([':car_id' => $carId]);
            return $stmt->fetchAll();
        } catch (PDOException $e) {
            return [];
        }
    }

    /**
     * Get the primary image for a car
     * 
     * @param int $carId Car ID
     * @return string|null Primary image path or null if not found
     */
    public function getPrimaryImage($carId) {
        try {
            $stmt = $this->db->prepare(
                "SELECT image_path 
                 FROM car_images 
                 WHERE car_id = :car_id AND is_primary = :is_primary 
                 LIMIT 1"
            );
            
            $stmt->execute([
                ':car_id' => $carId,
                ':is_primary' => 1
            ]);
            
            $result = $stmt->fetch();
            return $result ? $result['image_path'] : null;
        } catch (PDOException $e) {
            return null;
        }
    }

    /**
     * Delete an image
     * 
     * @param int $imageId Image ID
     * @param int $carId Car ID (for verification)
     * @return bool Success status
     */
    public function deleteImage($imageId, $carId) {
        try {
            // Check if this is a primary image
            $checkStmt = $this->db->prepare(
                "SELECT is_primary FROM car_images WHERE id = :id AND car_id = :car_id"
            );
            
            $checkStmt->execute([
                ':id' => $imageId,
                ':car_id' => $carId
            ]);
            
            $image = $checkStmt->fetch();
            
            if (!$image) {
                return false;
            }
            
            $isPrimary = $image['is_primary'];
            
            // Delete the image
            $stmt = $this->db->prepare(
                "DELETE FROM car_images WHERE id = :id AND car_id = :car_id"
            );
            
            $stmt->execute([
                ':id' => $imageId,
                ':car_id' => $carId
            ]);
            
            // If this was a primary image, set a new primary image
            if ($isPrimary) {
                $newPrimaryStmt = $this->db->prepare(
                    "UPDATE car_images SET is_primary = :is_primary 
                     WHERE car_id = :car_id ORDER BY id ASC LIMIT 1"
                );
                
                $newPrimaryStmt->execute([
                    ':is_primary' => 1,
                    ':car_id' => $carId
                ]);
            }
            
            return true;
        } catch (PDOException $e) {
            return false;
        }
    }

    /**
     * Set an image as primary
     * 
     * @param int $imageId Image ID
     * @param int $carId Car ID (for verification)
     * @return bool Success status
     */
    public function setPrimaryImage($imageId, $carId) {
        try {
            $this->db->beginTransaction();
            
            // First, set all images for this car as non-primary
            $resetStmt = $this->db->prepare(
                "UPDATE car_images SET is_primary = :is_primary WHERE car_id = :car_id"
            );
            
            $resetStmt->execute([
                ':is_primary' => 0,
                ':car_id' => $carId
            ]);
            
            // Then set the selected image as primary
            $stmt = $this->db->prepare(
                "UPDATE car_images SET is_primary = :is_primary 
                 WHERE id = :id AND car_id = :car_id"
            );
            
            $stmt->execute([
                ':is_primary' => 1,
                ':id' => $imageId,
                ':car_id' => $carId
            ]);
            
            $this->db->commit();
            return true;
        } catch (PDOException $e) {
            $this->db->rollBack();
            return false;
        }
    }
}