<?php
/**
 * CARLINK - Script de inicialización de base de datos SQLite
 * 
 * Este script crea y puebla la base de datos SQLite con datos iniciales.
 * Úsalo si tienes problemas con la base de datos en tu entorno.
 */

// Mostrar errores para depuración
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

// Estilo básico para mostrar la información
echo '<style>
    body { font-family: Arial, sans-serif; margin: 20px; line-height: 1.6; color: #333; }
    h1 { color: #1ec573; }
    h2 { margin-top: 30px; color: #1ec573; }
    .success { color: green; font-weight: bold; }
    .error { color: red; font-weight: bold; }
    .warning { color: orange; font-weight: bold; }
    pre { background: #f5f5f5; padding: 10px; border-radius: 5px; overflow: auto; }
    .code { font-family: monospace; background: #f5f5f5; padding: 2px 5px; border-radius: 3px; }
</style>';

echo '<h1>CARLINK - Inicialización de Base de Datos SQLite</h1>';

// Verificar si la base de datos existe
$db_path = __DIR__ . '/database.db';
echo '<h2>1. Verificando el archivo de base de datos</h2>';

if (file_exists($db_path)) {
    echo '<p class="warning">⚠ El archivo de base de datos ya existe en: ' . htmlspecialchars($db_path) . '</p>';
    echo '<p>Tamaño: ' . filesize($db_path) . ' bytes</p>';
    
    // Preguntar si se quiere sobrescribir
    if (!isset($_GET['force'])) {
        echo '<p>¿Deseas sobrescribir la base de datos existente? Esto eliminará todos los datos existentes.</p>';
        echo '<p><a href="?force=1" style="background: #e74c3c; color: white; padding: 10px 15px; text-decoration: none; border-radius: 5px;">Sobrescribir base de datos</a> &nbsp; 
              <a href="/" style="background: #3498db; color: white; padding: 10px 15px; text-decoration: none; border-radius: 5px;">Cancelar y volver</a></p>';
        exit;
    } else {
        // Eliminar el archivo existente
        if (@unlink($db_path)) {
            echo '<p class="success">✓ Base de datos existente eliminada correctamente.</p>';
        } else {
            echo '<p class="error">✗ No se pudo eliminar la base de datos existente. Verifica los permisos.</p>';
            exit;
        }
    }
}

// Crear nueva base de datos
echo '<h2>2. Creando nueva base de datos</h2>';
try {
    // Crear archivo vacío
    if (@touch($db_path)) {
        echo '<p class="success">✓ Archivo de base de datos creado en: ' . htmlspecialchars($db_path) . '</p>';
        // Establecer permisos
        if (@chmod($db_path, 0644)) {
            echo '<p class="success">✓ Permisos establecidos correctamente.</p>';
        } else {
            echo '<p class="warning">⚠ No se pudieron establecer los permisos, pero esto podría no ser crítico.</p>';
        }
    } else {
        echo '<p class="error">✗ No se pudo crear el archivo de base de datos. Verifica los permisos del directorio.</p>';
        exit;
    }
    
    // Conectar a la base de datos
    $dsn = "sqlite:$db_path";
    $db = new PDO($dsn);
    $db->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    
    echo '<p class="success">✓ Conexión a SQLite establecida correctamente.</p>';
    
    // Activar soporte de claves foráneas
    $db->exec('PRAGMA foreign_keys = ON;');
    echo '<p class="success">✓ Soporte para claves foráneas activado.</p>';
    
    // Leer y ejecutar el archivo SQL
    echo '<h2>3. Creando esquema de base de datos</h2>';
    $sqlFile = __DIR__ . '/database.sql';
    
    if (!file_exists($sqlFile)) {
        echo '<p class="error">✗ No se encuentra el archivo de esquema SQL: ' . htmlspecialchars($sqlFile) . '</p>';
        exit;
    }
    
    $sql = file_get_contents($sqlFile);
    if (empty($sql)) {
        echo '<p class="error">✗ El archivo de esquema SQL está vacío o no se pudo leer.</p>';
        exit;
    }
    
    try {
        $db->exec($sql);
        echo '<p class="success">✓ Esquema de base de datos creado correctamente.</p>';
    } catch (PDOException $e) {
        echo '<p class="error">✗ Error al crear el esquema: ' . htmlspecialchars($e->getMessage()) . '</p>';
        exit;
    }
    
    // Añadir datos iniciales
    echo '<h2>4. Añadiendo datos de muestra</h2>';
    
    // Crear usuario propietario
    $password = password_hash('password123', PASSWORD_DEFAULT);
    $stmt = $db->prepare("INSERT INTO users (name, email, password, role, phone, address) 
                         VALUES (?, ?, ?, ?, ?, ?)");
    $stmt->execute(['Test Owner', 'owner@example.com', $password, 'owner', '+34123456789', 'Madrid, Spain']);
    $ownerId = $db->lastInsertId();
    
    echo '<p class="success">✓ Usuario propietario creado (email: owner@example.com, password: password123)</p>';
    
    // Crear usuario cliente
    $stmt = $db->prepare("INSERT INTO users (name, email, password, role, phone, address) 
                         VALUES (?, ?, ?, ?, ?, ?)");
    $stmt->execute(['Test Renter', 'renter@example.com', $password, 'renter', '+34987654321', 'Barcelona, Spain']);
    
    echo '<p class="success">✓ Usuario cliente creado (email: renter@example.com, password: password123)</p>';
    
    // Crear algunos coches de ejemplo
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
    
    $car_count = 0;
    $image_count = 0;
    
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
        $car_count++;
        
        // Añadir imágenes
        foreach ($car['images'] as $index => $image) {
            $isPrimary = ($index === 0) ? 1 : 0;
            $stmt = $db->prepare("INSERT INTO car_images (car_id, image_path, is_primary) VALUES (?, ?, ?)");
            $stmt->execute([$carId, $image, $isPrimary]);
            $image_count++;
        }
    }
    
    echo '<p class="success">✓ Se han añadido ' . $car_count . ' coches con ' . $image_count . ' imágenes.</p>';
    
    // Verificar la estructura final
    echo '<h2>5. Verificando la estructura final</h2>';
    
    $tables = $db->query("SELECT name FROM sqlite_master WHERE type='table'")->fetchAll(PDO::FETCH_COLUMN);
    
    echo '<ul>';
    foreach ($tables as $table) {
        if ($table != 'sqlite_sequence') {
            $count = $db->query("SELECT COUNT(*) FROM " . $table)->fetchColumn();
            echo '<li><b>' . htmlspecialchars($table) . '</b>: ' . $count . ' registros</li>';
        }
    }
    echo '</ul>';
    
    echo '<h2>✅ Inicialización completada</h2>';
    echo '<p>La base de datos se ha inicializado correctamente.</p>';
    echo '<p><a href="/" style="background: #1ec573; color: white; padding: 10px 15px; text-decoration: none; border-radius: 5px;">Ir a la aplicación</a></p>';
    
} catch (Exception $e) {
    echo '<h2>❌ Error</h2>';
    echo '<p class="error">Se ha producido un error: ' . htmlspecialchars($e->getMessage()) . '</p>';
    
    echo '<pre>' . htmlspecialchars($e->getTraceAsString()) . '</pre>';
}
?>