<?php
require_once 'config.php';

// Simple router
$route = $_GET['route'] ?? 'home';

// Handle routes
switch ($route) {
    case 'home':
        include 'views/home.php';
        break;
    
    case 'login':
        include 'views/login.php';
        break;
    
    case 'register':
        include 'views/register.php';
        break;
    
    case 'logout':
        include 'controllers/UserController.php';
        $userController = new UserController($db);
        $userController->logout();
        break;
    
    case 'explore':
        include 'views/explore.php';
        break;
    
    case 'car':
        include 'views/car_detail.php';
        break;
    
    case 'book':
        if (!isLoggedIn()) {
            $_SESSION['redirect_after_login'] = "book?car_id=" . ($_GET['car_id'] ?? '');
            redirect('login');
        }
        include 'views/book_car.php';
        break;
    
    case 'user-dashboard':
        if (!isLoggedIn()) {
            redirect('login');
        }
        include 'views/user_dashboard.php';
        break;
    
    case 'owner-dashboard':
        if (!isOwner()) {
            redirect(isLoggedIn() ? 'user-dashboard' : 'login');
        }
        include 'views/owner_dashboard.php';
        break;
    
    case 'add-car':
        if (!isOwner()) {
            redirect(isLoggedIn() ? 'user-dashboard' : 'login');
        }
        include 'views/add_car.php';
        break;
    
    case 'manage-reservations':
        if (!isOwner()) {
            redirect(isLoggedIn() ? 'user-dashboard' : 'login');
        }
        include 'views/manage_reservations.php';
        break;
    
    case 'process-login':
        include 'controllers/UserController.php';
        $userController = new UserController($db);
        $userController->login();
        break;
    
    case 'process-register':
        include 'controllers/UserController.php';
        $userController = new UserController($db);
        $userController->register();
        break;
    
    case 'process-add-car':
        include 'controllers/CarController.php';
        $carController = new CarController($db);
        $carController->addCar();
        break;
    
    case 'process-reservation':
        include 'controllers/ReservationController.php';
        $reservationController = new ReservationController($db);
        $reservationController->createReservation();
        break;
    
    case 'update-reservation':
        include 'controllers/ReservationController.php';
        $reservationController = new ReservationController($db);
        $reservationController->updateReservation();
        break;
    
    case 'process-payment':
        include 'controllers/PaymentController.php';
        $paymentController = new PaymentController($db);
        $paymentController->processPayment();
        break;
    
    default:
        http_response_code(404);
        echo "Page not found";
        break;
}
?>
