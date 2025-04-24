<?php
// Configuración
$uploadDir = __DIR__ . '/uploads/cars/';

// Verificar si el directorio existe, de lo contrario crearlo
if (!file_exists($uploadDir)) {
    mkdir($uploadDir, 0755, true);
}

// Función para generar una imagen con texto
function generateCarImage($filename, $text, $width = 800, $height = 500, $bgColor = [230, 230, 230], $textColor = [50, 50, 50]) {
    global $uploadDir;
    
    // Crear imagen
    $image = imagecreatetruecolor($width, $height);
    
    // Definir colores
    $bg = imagecolorallocate($image, $bgColor[0], $bgColor[1], $bgColor[2]);
    $text_color = imagecolorallocate($image, $textColor[0], $textColor[1], $textColor[2]);
    
    // Rellenar fondo
    imagefill($image, 0, 0, $bg);
    
    // Añadir un rectángulo para simular un coche
    $carColor = imagecolorallocate($image, rand(50, 200), rand(50, 200), rand(50, 200));
    imagefilledrectangle($image, 100, $height - 250, $width - 100, $height - 150, $carColor);
    
    // Añadir ruedas
    $wheelColor = imagecolorallocate($image, 30, 30, 30);
    imagefilledellipse($image, 200, $height - 150, 80, 80, $wheelColor);
    imagefilledellipse($image, $width - 200, $height - 150, 80, 80, $wheelColor);
    
    // Añadir ventanas
    $windowColor = imagecolorallocate($image, 200, 200, 255);
    imagefilledrectangle($image, 150, $height - 240, 350, $height - 180, $windowColor);
    imagefilledrectangle($image, $width - 350, $height - 240, $width - 150, $height - 180, $windowColor);
    
    // Dividir texto en líneas
    $lines = explode("\n", $text);
    
    // Añadir texto
    $fontSize = 5;
    $y = 50;
    
    foreach ($lines as $line) {
        $bbox = imagettfbbox($fontSize, 0, 5, $line);
        $textWidth = abs($bbox[4] - $bbox[0]);
        $x = ($width - $textWidth) / 2;
        imagestring($image, $fontSize, $x, $y, $line, $text_color);
        $y += 30;
    }
    
    // Guardar imagen
    imagejpeg($image, $uploadDir . $filename, 90);
    
    // Liberar memoria
    imagedestroy($image);
    
    return $uploadDir . $filename;
}

// Generar imágenes para cada coche
$cars = [
    [
        'make' => 'Volkswagen',
        'model' => 'Golf',
        'year' => 2022,
        'files' => [
            'vw_golf_1.jpg' => "Volkswagen Golf 2022\nVista frontal",
            'vw_golf_2.jpg' => "Volkswagen Golf 2022\nVista lateral",
            'vw_golf_3.jpg' => "Volkswagen Golf 2022\nVista trasera"
        ]
    ],
    [
        'make' => 'Seat',
        'model' => 'Ibiza',
        'year' => 2021,
        'files' => [
            'seat_ibiza_1.jpg' => "Seat Ibiza 2021\nVista frontal",
            'seat_ibiza_2.jpg' => "Seat Ibiza 2021\nVista lateral"
        ]
    ],
    [
        'make' => 'BMW',
        'model' => '320d',
        'year' => 2020,
        'files' => [
            'bmw_320d_1.jpg' => "BMW 320d 2020\nVista frontal",
            'bmw_320d_2.jpg' => "BMW 320d 2020\nVista interior"
        ]
    ],
    [
        'make' => 'Tesla',
        'model' => 'Model 3',
        'year' => 2023,
        'files' => [
            'tesla_model3_1.jpg' => "Tesla Model 3 2023\nVista frontal",
            'tesla_model3_2.jpg' => "Tesla Model 3 2023\nVista lateral"
        ]
    ],
    [
        'make' => 'Fiat',
        'model' => '500',
        'year' => 2022,
        'files' => [
            'fiat_500_1.jpg' => "Fiat 500 2022\nVista frontal",
            'fiat_500_2.jpg' => "Fiat 500 2022\nVista trasera"
        ]
    ]
];

// Generar todas las imágenes
$generatedImages = [];

foreach ($cars as $car) {
    foreach ($car['files'] as $filename => $text) {
        // Colores aleatorios para cada coche
        $bgColor = [rand(220, 240), rand(220, 240), rand(220, 240)];
        $carColor = [rand(50, 200), rand(50, 200), rand(50, 200)];
        
        $path = generateCarImage($filename, $text, 800, 500, $bgColor, $carColor);
        $generatedImages[] = $path;
        
        echo "Generada imagen: $filename\n";
    }
}

echo "\nSe generaron " . count($generatedImages) . " imágenes en $uploadDir\n";

// Ahora actualizamos las entradas de imágenes para cada coche en la base de datos
// Necesitamos cargar la configuración de la base de datos
require_once 'config.php';

// Primero, borramos todas las imágenes existentes
$stmt = $db->prepare("DELETE FROM car_images");
$stmt->execute();

// Ahora insertamos las nuevas imágenes
foreach ($cars as $index => $car) {
    $carId = $index + 1; // El ID del coche en la base de datos
    $isPrimary = true;
    
    foreach ($car['files'] as $filename => $text) {
        $imagePath = 'uploads/cars/' . $filename;
        
        $stmt = $db->prepare("
            INSERT INTO car_images (car_id, image_path, is_primary)
            VALUES (:car_id, :image_path, :is_primary)
        ");
        
        $stmt->bindParam(':car_id', $carId);
        $stmt->bindParam(':image_path', $imagePath);
        $stmt->bindParam(':is_primary', $isPrimary, PDO::PARAM_BOOL);
        
        $stmt->execute();
        
        // Solo la primera imagen es la principal
        $isPrimary = false;
        
        echo "Imagen $filename añadida a la base de datos para el coche ID: $carId\n";
    }
}

echo "\n¡Proceso completado!\n";
?>