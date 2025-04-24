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

// Get make parameter
$make = sanitizeInput($_GET['make']);

// Initialize CarApi
$carApi = new CarApi();

// Use caching to reduce API calls
$cacheKey = 'models_' . $make;
$models = $carApi->getCachedData($cacheKey, function() use ($carApi, $make) {
    return $carApi->getModelsByMake($make);
});

if ($models === null) {
    echo json_encode([
        'success' => false,
        'message' => 'Failed to fetch models for ' . $make
    ]);
    exit;
}

// Return success response
echo json_encode([
    'success' => true,
    'make' => $make,
    'models' => $models
]);
?>
