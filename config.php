<?php
// Database connection configuration (SQLite)
$db_path = __DIR__ . '/database.db';

// Asegurarse de que el directorio tenga permisos de escritura
$db_dir = dirname($db_path);
if (!is_writable($db_dir)) {
    @chmod($db_dir, 0755);
    if (!is_writable($db_dir)) {
        die("No se pueden escribir archivos en el directorio: $db_dir");
    }
}

// Si la base de datos no existe, crearemos un archivo vacío
if (!file_exists($db_path)) {
    @touch($db_path);
    @chmod($db_path, 0644);
    if (!file_exists($db_path) || !is_writable($db_path)) {
        die("No se pudo crear o escribir en el archivo de base de datos: $db_path");
    }
}

// Create DSN for SQLite
$dsn = "sqlite:$db_path";

// API Configuration (disabled, using local data only)
define('API_NINJAS_URL', '');
define('API_NINJAS_KEY', '');

// Application Configuration
define('APP_NAME', 'CARLINK');
define('APP_URL', 'http://' . $_SERVER['HTTP_HOST']);
define('UPLOAD_DIR', __DIR__ . '/uploads/');
define('MAX_UPLOAD_SIZE', 5 * 1024 * 1024); // 5MB

// Initialize database connection
try {
    // Configuración y opciones adicionales para PDO SQLite
    $options = [
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
        PDO::ATTR_TIMEOUT => 30 // Tiempo de espera en segundos
    ];
    
    // Intentar conexión
    $db = new PDO($dsn, null, null, $options);
    
    // Enable foreign key support for SQLite
    $db->exec('PRAGMA foreign_keys = ON;');
    
    // Check if we need to initialize the database
    $tables = $db->query("SELECT name FROM sqlite_master WHERE type='table'")->fetchAll();
    if (count($tables) <= 1) { // If only sqlite_sequence exists or no tables
        // Cargar el esquema SQL
        $sqlFile = __DIR__ . '/database.sql';
        if (!file_exists($sqlFile)) {
            die("ERROR: No se encuentra el archivo de esquema SQL: $sqlFile");
        }
        
        $sql = file_get_contents($sqlFile);
        if (empty($sql)) {
            die("ERROR: El archivo de esquema SQL está vacío o no se pudo leer: $sqlFile");
        }
        
        // Ejecutar las consultas SQL para crear las tablas
        try {
            $db->exec($sql);
        } catch (PDOException $schemaException) {
            die("ERROR al crear las tablas: " . $schemaException->getMessage() . 
                "<br>Consulta SQL: " . htmlspecialchars(substr($sql, 0, 300)) . "...");
        }
        
        // Cargar los modelos necesarios
        $modelFiles = [
            __DIR__ . '/models/User.php',
            __DIR__ . '/models/Car.php',
            __DIR__ . '/models/CarImage.php'
        ];
        
        foreach ($modelFiles as $modelFile) {
            if (!file_exists($modelFile)) {
                die("ERROR: No se encuentra el archivo de modelo: $modelFile");
            }
            require_once $modelFile;
        }
        
        // Verificar si hay usuarios
        try {
            $userCount = $db->query("SELECT COUNT(*) as count FROM users")->fetch()['count'];
            if ($userCount == 0) {
                createInitialData($db);
            }
        } catch (PDOException $dataException) {
            die("ERROR al verificar/crear datos iniciales: " . $dataException->getMessage());
        }
    }
} catch (PDOException $e) {
    die("ERROR de conexión a la base de datos SQLite: " . $e->getMessage() . 
        "<br>Ruta de la base de datos: " . htmlspecialchars($db_path) . 
        "<br>Permisos del archivo: " . (file_exists($db_path) ? decoct(fileperms($db_path) & 0777) : 'Archivo no existe'));
}

// Function to create initial data
function createInitialData($db) {
    // Create a test user (owner)
    $password = password_hash('password123', PASSWORD_DEFAULT);
    $stmt = $db->prepare("INSERT INTO users (name, email, password, role, phone, address) 
                         VALUES (?, ?, ?, ?, ?, ?)");
    $stmt->execute(['Test Owner', 'owner@example.com', $password, 'owner', '+34123456789', 'Madrid, Spain']);
    $ownerId = $db->lastInsertId();
    
    // Create a test user (renter)
    $stmt = $db->prepare("INSERT INTO users (name, email, password, role, phone, address) 
                         VALUES (?, ?, ?, ?, ?, ?)");
    $stmt->execute(['Test Renter', 'renter@example.com', $password, 'renter', '+34987654321', 'Barcelona, Spain']);
    
    // Create some test cars
    $cars = [
        [
            'make' => 'Volkswagen',
            'model' => 'Golf',
            'year' => 2020,
            'fuel_type' => 'Gasolina',
            'transmission' => 'Manual',
            'price_per_day' => 35.00,
            'location' => 'Madrid, Spain',
            'description' => 'Volkswagen Golf en excelente estado. Perfecto para viajes urbanos.',
            'cylinders' => 4,
            'displacement' => 1.5,
            'power' => 130,
            'city_mpg' => 6.2,
            'highway_mpg' => 4.5,
            'images' => ['vw_golf_1.jpg', 'vw_golf_2.jpg', 'vw_golf_3.jpg']
        ],
        [
            'make' => 'Seat',
            'model' => 'Ibiza',
            'year' => 2019,
            'fuel_type' => 'Diésel',
            'transmission' => 'Manual',
            'price_per_day' => 28.00,
            'location' => 'Barcelona, Spain',
            'description' => 'Seat Ibiza compacto y económico, ideal para la ciudad.',
            'cylinders' => 4,
            'displacement' => 1.6,
            'power' => 95,
            'city_mpg' => 5.9,
            'highway_mpg' => 4.2,
            'images' => ['seat_ibiza_1.jpg', 'seat_ibiza_2.jpg']
        ],
        [
            'make' => 'BMW',
            'model' => '320d',
            'year' => 2021,
            'fuel_type' => 'Diésel',
            'transmission' => 'Automático',
            'price_per_day' => 55.00,
            'location' => 'Valencia, Spain',
            'description' => 'BMW Serie 3 con todas las comodidades. Perfecto para viajes largos o de negocios.',
            'cylinders' => 4,
            'displacement' => 2.0,
            'power' => 190,
            'city_mpg' => 6.5,
            'highway_mpg' => 4.8,
            'images' => ['bmw_320d_1.jpg', 'bmw_320d_2.jpg', 'bmw_320d_3.jpg']
        ],
        [
            'make' => 'Tesla',
            'model' => 'Model 3',
            'year' => 2022,
            'fuel_type' => 'Eléctrico',
            'transmission' => 'Automático',
            'price_per_day' => 70.00,
            'location' => 'Madrid, Spain',
            'description' => 'Tesla Model 3 eléctrico. Tecnología de vanguardia y conducción autónoma.',
            'cylinders' => 0,
            'displacement' => 0,
            'power' => 283,
            'city_mpg' => 0,
            'highway_mpg' => 0,
            'images' => ['tesla_model3_1.jpg', 'tesla_model3_2.jpg']
        ],
        [
            'make' => 'Fiat',
            'model' => '500',
            'year' => 2020,
            'fuel_type' => 'Gasolina',
            'transmission' => 'Manual',
            'price_per_day' => 25.00,
            'location' => 'Sevilla, Spain',
            'description' => 'Fiat 500 compacto y ágil. Ideal para moverse por la ciudad con facilidad.',
            'cylinders' => 2,
            'displacement' => 1.2,
            'power' => 69,
            'city_mpg' => 5.5,
            'highway_mpg' => 4.1,
            'images' => ['fiat_500_1.jpg', 'fiat_500_2.jpg']
        ]
    ];
    
    // Insert the cars and their images
    foreach ($cars as $car) {
        $stmt = $db->prepare("INSERT INTO cars (owner_id, make, model, year, fuel_type, transmission, 
                             price_per_day, location, description, cylinders, displacement, power, 
                             city_mpg, highway_mpg) 
                             VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");
        $stmt->execute([
            $ownerId, $car['make'], $car['model'], $car['year'], $car['fuel_type'], 
            $car['transmission'], $car['price_per_day'], $car['location'], $car['description'],
            $car['cylinders'], $car['displacement'], $car['power'], $car['city_mpg'], $car['highway_mpg']
        ]);
        
        $carId = $db->lastInsertId();
        
        // Add car images
        foreach ($car['images'] as $index => $image) {
            $isPrimary = ($index === 0) ? 1 : 0;
            $stmt = $db->prepare("INSERT INTO car_images (car_id, image_path, is_primary) VALUES (?, ?, ?)");
            $stmt->execute([$carId, $image, $isPrimary]);
        }
    }
}

// Session configuration
session_start();

// Helper functions
function redirect($path) {
    header("Location: " . APP_URL . "/" . $path);
    exit;
}

function isLoggedIn() {
    return isset($_SESSION['user_id']);
}

function isOwner() {
    return isLoggedIn() && $_SESSION['role'] === 'owner';
}

function isRenter() {
    return isLoggedIn() && $_SESSION['role'] === 'renter';
}

function getUserId() {
    return $_SESSION['user_id'] ?? null;
}

function getRole() {
    return $_SESSION['role'] ?? null;
}

function sanitizeInput($input) {
    return htmlspecialchars(trim($input), ENT_QUOTES, 'UTF-8');
}

function generateCSRFToken() {
    if (!isset($_SESSION['csrf_token'])) {
        $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
    }
    return $_SESSION['csrf_token'];
}

function validateCSRFToken($token) {
    return isset($_SESSION['csrf_token']) && hash_equals($_SESSION['csrf_token'], $token);
}

function displayAlert($message, $type = 'success') {
    return "<div class='alert alert-$type'>$message</div>";
}

// Format date for display
function formatDate($date) {
    return date('d M Y', strtotime($date));
}

// Format price for display
function formatPrice($price) {
    return number_format($price, 2) . '€';
}
?>
