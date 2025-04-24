<?php
require_once '../config.php';
require_once 'CarApi.php';

// Set content type to JSON
header('Content-Type: application/json');

// Check if make is provided
if (!isset($_GET['make']) || empty($_GET['make'])) {
    echo json_encode([
        'success' => false,
        'message' => 'Make is required'
    ]);
    exit;
}

// Get query parameters
$make = sanitizeInput($_GET['make']);
$model = isset($_GET['model']) ? sanitizeInput($_GET['model']) : '';
$year = isset($_GET['year']) && is_numeric($_GET['year']) ? (int)$_GET['year'] : '';
$fuel_type = isset($_GET['fuel_type']) ? sanitizeInput($_GET['fuel_type']) : '';

// Initialize CarApi
$carApi = new CarApi();

// Get car data
$cars = $carApi->getCars($make, $model, $year, $fuel_type);

if ($cars === null) {
    echo json_encode([
        'success' => false,
        'message' => 'Failed to fetch car data'
    ]);
    exit;
}

// Return success response
echo json_encode([
    'success' => true,
    'cars' => $cars
]);
?>
