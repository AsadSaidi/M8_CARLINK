<?php
class User {
    private $db;

    public function __construct($db) {
        $this->db = $db;
    }

    /**
     * Register a new user
     * 
     * @param string $name User's name
     * @param string $email User's email
     * @param string $password User's password (will be hashed)
     * @param string $role User's role (owner or renter)
     * @param string $phone User's phone number (optional)
     * @param string $address User's address (optional)
     * @return array Success status and message/user data
     */
    public function register($name, $email, $password, $role, $phone = null, $address = null) {
        try {
            // Check if email already exists
            $stmt = $this->db->prepare("SELECT id FROM users WHERE email = :email");
            $stmt->bindParam(':email', $email);
            $stmt->execute();
            
            if ($stmt->rowCount() > 0) {
                return [
                    'success' => false,
                    'message' => 'Email already in use'
                ];
            }
            
            // Hash password
            $hashedPassword = password_hash($password, PASSWORD_DEFAULT);
            
            // Insert new user
            $stmt = $this->db->prepare(
                "INSERT INTO users (name, email, password, role, phone, address) 
                 VALUES (:name, :email, :password, :role, :phone, :address) 
                 RETURNING id"
            );
            
            $stmt->bindParam(':name', $name);
            $stmt->bindParam(':email', $email);
            $stmt->bindParam(':password', $hashedPassword);
            $stmt->bindParam(':role', $role);
            $stmt->bindParam(':phone', $phone);
            $stmt->bindParam(':address', $address);
            $stmt->execute();
            
            $userId = $stmt->fetchColumn();
            
            return [
                'success' => true,
                'user_id' => $userId,
                'role' => $role
            ];
        } catch (PDOException $e) {
            return [
                'success' => false,
                'message' => 'Registration failed: ' . $e->getMessage()
            ];
        }
    }

    /**
     * Authenticate a user
     * 
     * @param string $email User's email
     * @param string $password User's password
     * @return array Success status and message/user data
     */
    public function login($email, $password) {
        try {
            $stmt = $this->db->prepare(
                "SELECT id, name, email, password, role 
                 FROM users 
                 WHERE email = :email"
            );
            $stmt->bindParam(':email', $email);
            $stmt->execute();
            
            if ($stmt->rowCount() === 0) {
                return [
                    'success' => false,
                    'message' => 'Invalid email or password'
                ];
            }
            
            $user = $stmt->fetch();
            
            if (password_verify($password, $user['password'])) {
                return [
                    'success' => true,
                    'user_id' => $user['id'],
                    'name' => $user['name'],
                    'email' => $user['email'],
                    'role' => $user['role']
                ];
            } else {
                return [
                    'success' => false,
                    'message' => 'Invalid email or password'
                ];
            }
        } catch (PDOException $e) {
            return [
                'success' => false,
                'message' => 'Login failed: ' . $e->getMessage()
            ];
        }
    }

    /**
     * Get user by ID
     * 
     * @param int $userId User ID
     * @return array|false User data or false if user not found
     */
    public function getUserById($userId) {
        try {
            $stmt = $this->db->prepare(
                "SELECT id, name, email, role, phone, address, created_at 
                 FROM users 
                 WHERE id = :id"
            );
            $stmt->bindParam(':id', $userId);
            $stmt->execute();
            
            return $stmt->fetch();
        } catch (PDOException $e) {
            return false;
        }
    }

    /**
     * Update user profile
     * 
     * @param int $userId User ID
     * @param array $data Data to update (name, phone, address)
     * @return boolean Success status
     */
    public function updateProfile($userId, $data) {
        try {
            $query = "UPDATE users SET ";
            $params = [];
            
            foreach ($data as $key => $value) {
                if (in_array($key, ['name', 'phone', 'address'])) {
                    $query .= "$key = :$key, ";
                    $params[":$key"] = $value;
                }
            }
            
            $query = rtrim($query, ', ') . " WHERE id = :id";
            $params[':id'] = $userId;
            
            $stmt = $this->db->prepare($query);
            return $stmt->execute($params);
        } catch (PDOException $e) {
            return false;
        }
    }

    /**
     * Change user password
     * 
     * @param int $userId User ID
     * @param string $currentPassword Current password
     * @param string $newPassword New password
     * @return array Success status and message
     */
    public function changePassword($userId, $currentPassword, $newPassword) {
        try {
            // Get current user password
            $stmt = $this->db->prepare("SELECT password FROM users WHERE id = :id");
            $stmt->bindParam(':id', $userId);
            $stmt->execute();
            
            $user = $stmt->fetch();
            
            if (!$user || !password_verify($currentPassword, $user['password'])) {
                return [
                    'success' => false,
                    'message' => 'Current password is incorrect'
                ];
            }
            
            // Update password
            $hashedPassword = password_hash($newPassword, PASSWORD_DEFAULT);
            $stmt = $this->db->prepare("UPDATE users SET password = :password WHERE id = :id");
            $stmt->bindParam(':password', $hashedPassword);
            $stmt->bindParam(':id', $userId);
            $stmt->execute();
            
            return [
                'success' => true,
                'message' => 'Password updated successfully'
            ];
        } catch (PDOException $e) {
            return [
                'success' => false,
                'message' => 'Failed to change password: ' . $e->getMessage()
            ];
        }
    }
}
?>
