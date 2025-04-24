<?php
require_once 'models/User.php';

class UserController {
    private $db;
    private $userModel;

    public function __construct($db) {
        $this->db = $db;
        $this->userModel = new User($db);
    }

    /**
     * Process user registration
     */
    public function register() {
        // Verify CSRF token
        if (!isset($_POST['csrf_token']) || !validateCSRFToken($_POST['csrf_token'])) {
            $_SESSION['alert'] = 'Invalid form submission';
            $_SESSION['alert_type'] = 'danger';
            redirect('register');
            return;
        }

        // Validate form inputs
        $requiredFields = ['name', 'email', 'password', 'confirm_password', 'role', 'terms'];
        
        foreach ($requiredFields as $field) {
            if (!isset($_POST[$field]) || empty($_POST[$field])) {
                $_SESSION['alert'] = 'All required fields must be filled';
                $_SESSION['alert_type'] = 'danger';
                redirect('register');
                return;
            }
        }

        // Sanitize inputs
        $name = sanitizeInput($_POST['name']);
        $email = filter_var($_POST['email'], FILTER_SANITIZE_EMAIL);
        $password = $_POST['password'];
        $confirmPassword = $_POST['confirm_password'];
        $role = $_POST['role'];
        $phone = isset($_POST['phone']) ? sanitizeInput($_POST['phone']) : null;
        $address = isset($_POST['address']) ? sanitizeInput($_POST['address']) : null;

        // Validate email
        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            $_SESSION['alert'] = 'Please enter a valid email address';
            $_SESSION['alert_type'] = 'danger';
            redirect('register');
            return;
        }

        // Check if passwords match
        if ($password !== $confirmPassword) {
            $_SESSION['alert'] = 'Passwords do not match';
            $_SESSION['alert_type'] = 'danger';
            redirect('register');
            return;
        }

        // Validate password strength
        if (strlen($password) < 8 || !preg_match('/[A-Z]/', $password) || !preg_match('/[a-z]/', $password) || !preg_match('/[0-9]/', $password)) {
            $_SESSION['alert'] = 'Password must be at least 8 characters and include uppercase, lowercase, and numbers';
            $_SESSION['alert_type'] = 'danger';
            redirect('register');
            return;
        }

        // Validate role
        if ($role !== 'owner' && $role !== 'renter') {
            $_SESSION['alert'] = 'Invalid role selection';
            $_SESSION['alert_type'] = 'danger';
            redirect('register');
            return;
        }

        // Register user
        $result = $this->userModel->register($name, $email, $password, $role, $phone, $address);

        if ($result['success']) {
            // Start session and set user info
            $_SESSION['user_id'] = $result['user_id'];
            $_SESSION['user_name'] = $name;
            $_SESSION['user_email'] = $email;
            $_SESSION['role'] = $result['role'];

            $_SESSION['alert'] = 'Registration successful! Welcome to CARLINK.';
            $_SESSION['alert_type'] = 'success';
            
            // Redirect based on role
            if ($role === 'owner') {
                redirect('owner-dashboard');
            } else {
                redirect('user-dashboard');
            }
        } else {
            $_SESSION['alert'] = $result['message'];
            $_SESSION['alert_type'] = 'danger';
            redirect('register');
        }
    }

    /**
     * Process user login
     */
    public function login() {
        // Verify CSRF token
        if (!isset($_POST['csrf_token']) || !validateCSRFToken($_POST['csrf_token'])) {
            $_SESSION['alert'] = 'Invalid form submission';
            $_SESSION['alert_type'] = 'danger';
            redirect('login');
            return;
        }

        // Validate form inputs
        if (!isset($_POST['email']) || !isset($_POST['password']) || 
            empty($_POST['email']) || empty($_POST['password'])) {
            $_SESSION['alert'] = 'Email and password are required';
            $_SESSION['alert_type'] = 'danger';
            redirect('login');
            return;
        }

        // Sanitize inputs
        $email = filter_var($_POST['email'], FILTER_SANITIZE_EMAIL);
        $password = $_POST['password'];

        // Authenticate user
        $result = $this->userModel->login($email, $password);

        if ($result['success']) {
            // Start session and set user info
            $_SESSION['user_id'] = $result['user_id'];
            $_SESSION['user_name'] = $result['name'];
            $_SESSION['user_email'] = $result['email'];
            $_SESSION['role'] = $result['role'];

            $_SESSION['alert'] = 'Login successful! Welcome back, ' . $result['name'] . '.';
            $_SESSION['alert_type'] = 'success';
            
            // Check if there's a redirect after login
            if (isset($_SESSION['redirect_after_login'])) {
                $redirect = $_SESSION['redirect_after_login'];
                unset($_SESSION['redirect_after_login']);
                redirect($redirect);
            } else {
                // Redirect based on role
                if ($result['role'] === 'owner') {
                    redirect('owner-dashboard');
                } else {
                    redirect('user-dashboard');
                }
            }
        } else {
            $_SESSION['alert'] = $result['message'];
            $_SESSION['alert_type'] = 'danger';
            redirect('login');
        }
    }

    /**
     * Log the user out
     */
    public function logout() {
        // Clear all session variables
        $_SESSION = array();

        // Destroy the session
        if (session_status() === PHP_SESSION_ACTIVE) {
            session_destroy();
        }

        // Start a new session to set a message
        session_start();
        $_SESSION['alert'] = 'You have been logged out successfully.';
        $_SESSION['alert_type'] = 'success';

        // Redirect to homepage
        redirect('');
    }

    /**
     * Update user profile
     */
    public function updateProfile() {
        // Verify CSRF token
        if (!isset($_POST['csrf_token']) || !validateCSRFToken($_POST['csrf_token'])) {
            $_SESSION['alert'] = 'Invalid form submission';
            $_SESSION['alert_type'] = 'danger';
            redirect('user-dashboard');
            return;
        }

        // Make sure user is logged in
        if (!isLoggedIn()) {
            $_SESSION['alert'] = 'You must be logged in to update your profile';
            $_SESSION['alert_type'] = 'danger';
            redirect('login');
            return;
        }

        // Validate and sanitize inputs
        $name = isset($_POST['name']) ? sanitizeInput($_POST['name']) : null;
        $phone = isset($_POST['phone']) ? sanitizeInput($_POST['phone']) : null;
        $address = isset($_POST['address']) ? sanitizeInput($_POST['address']) : null;

        // Prepare data for update
        $data = [];
        if ($name) $data['name'] = $name;
        if ($phone) $data['phone'] = $phone;
        if ($address) $data['address'] = $address;

        // Update profile
        $result = $this->userModel->updateProfile($_SESSION['user_id'], $data);

        if ($result) {
            // Update session data
            if (isset($data['name'])) {
                $_SESSION['user_name'] = $data['name'];
            }

            $_SESSION['alert'] = 'Profile updated successfully';
            $_SESSION['alert_type'] = 'success';
        } else {
            $_SESSION['alert'] = 'Failed to update profile';
            $_SESSION['alert_type'] = 'danger';
        }

        // Redirect based on role
        if ($_SESSION['role'] === 'owner') {
            redirect('owner-dashboard');
        } else {
            redirect('user-dashboard');
        }
    }

    /**
     * Change user password
     */
    public function changePassword() {
        // Verify CSRF token
        if (!isset($_POST['csrf_token']) || !validateCSRFToken($_POST['csrf_token'])) {
            $_SESSION['alert'] = 'Invalid form submission';
            $_SESSION['alert_type'] = 'danger';
            redirect('user-dashboard');
            return;
        }

        // Make sure user is logged in
        if (!isLoggedIn()) {
            $_SESSION['alert'] = 'You must be logged in to change your password';
            $_SESSION['alert_type'] = 'danger';
            redirect('login');
            return;
        }

        // Validate inputs
        if (!isset($_POST['current_password']) || !isset($_POST['new_password']) || 
            !isset($_POST['confirm_password']) || empty($_POST['current_password']) || 
            empty($_POST['new_password']) || empty($_POST['confirm_password'])) {
            $_SESSION['alert'] = 'All password fields are required';
            $_SESSION['alert_type'] = 'danger';
            redirect('user-dashboard');
            return;
        }

        $currentPassword = $_POST['current_password'];
        $newPassword = $_POST['new_password'];
        $confirmPassword = $_POST['confirm_password'];

        // Check if new passwords match
        if ($newPassword !== $confirmPassword) {
            $_SESSION['alert'] = 'New passwords do not match';
            $_SESSION['alert_type'] = 'danger';
            redirect('user-dashboard');
            return;
        }

        // Validate new password strength
        if (strlen($newPassword) < 8 || !preg_match('/[A-Z]/', $newPassword) || 
            !preg_match('/[a-z]/', $newPassword) || !preg_match('/[0-9]/', $newPassword)) {
            $_SESSION['alert'] = 'New password must be at least 8 characters and include uppercase, lowercase, and numbers';
            $_SESSION['alert_type'] = 'danger';
            redirect('user-dashboard');
            return;
        }

        // Change password
        $result = $this->userModel->changePassword($_SESSION['user_id'], $currentPassword, $newPassword);

        if ($result['success']) {
            $_SESSION['alert'] = 'Password changed successfully';
            $_SESSION['alert_type'] = 'success';
        } else {
            $_SESSION['alert'] = $result['message'];
            $_SESSION['alert_type'] = 'danger';
        }

        // Redirect based on role
        if ($_SESSION['role'] === 'owner') {
            redirect('owner-dashboard');
        } else {
            redirect('user-dashboard');
        }
    }
}
?>
