<?php
/**
 * CARLINK - Script de comprobación de base de datos SQLite
 * 
 * Este script verifica que la base de datos SQLite esté correctamente
 * configurada y funcionando. Úsalo si tienes problemas con la base de datos.
 */

// Desactivar errores para usuarios
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
    table { border-collapse: collapse; width: 100%; margin: 20px 0; }
    table, th, td { border: 1px solid #ddd; }
    th, td { padding: 8px; text-align: left; }
    th { background-color: #f2f2f2; }
    tr:nth-child(even) { background-color: #f9f9f9; }
</style>';

echo '<h1>CARLINK - Comprobación de Base de Datos SQLite</h1>';

// Comprobar extensión PDO SQLite
echo '<h2>1. Verificando módulos PHP</h2>';
if (extension_loaded('pdo_sqlite')) {
    echo '<p class="success">✓ Extensión PDO SQLite instalada correctamente.</p>';
} else {
    echo '<p class="error">✗ La extensión PDO SQLite no está instalada. Este es un requisito para usar SQLite con PHP.</p>';
    echo '<p>Para instalar la extensión:</p>';
    echo '<pre>sudo apt-get update
sudo apt-get install php-sqlite3</pre>';
    die();
}

// Verificando la ruta de la base de datos
echo '<h2>2. Verificando la base de datos</h2>';
$db_path = __DIR__ . '/database.db';
echo '<p>Ruta del archivo de base de datos: <span class="code">' . htmlspecialchars($db_path) . '</span></p>';

// Verificar si existe
if (file_exists($db_path)) {
    echo '<p class="success">✓ El archivo de base de datos existe.</p>';
    
    // Verificar permisos
    $perms = fileperms($db_path);
    $readable = is_readable($db_path);
    $writable = is_writable($db_path);
    
    echo '<p>Permisos: ' . decoct($perms & 0777) . ' ';
    echo ($readable ? '<span class="success">(Lectura: SÍ)</span>' : '<span class="error">(Lectura: NO)</span>') . ' ';
    echo ($writable ? '<span class="success">(Escritura: SÍ)</span>' : '<span class="error">(Escritura: NO)</span>') . '</p>';
    
    // Verificar tamaño
    $size = filesize($db_path);
    echo '<p>Tamaño: ' . number_format($size) . ' bytes ';
    if ($size < 1000) {
        echo '<span class="warning">(El archivo parece muy pequeño, podría estar vacío)</span>';
    } else {
        echo '<span class="success">(El tamaño parece razonable)</span>';
    }
    echo '</p>';
} else {
    echo '<p class="error">✗ El archivo de base de datos no existe.</p>';
    
    // Verificar permisos del directorio
    $dir = dirname($db_path);
    $dir_writable = is_writable($dir);
    echo '<p>Directorio: ' . htmlspecialchars($dir) . ' ';
    echo ($dir_writable ? 
        '<span class="success">(Con permisos de escritura)</span>' : 
        '<span class="error">(Sin permisos de escritura)</span>') . '</p>';
    
    echo '<p>Intentando crear un archivo de base de datos vacío...</p>';
    $created = @touch($db_path);
    if ($created) {
        echo '<p class="success">✓ Se ha creado un archivo de base de datos vacío.</p>';
        @chmod($db_path, 0644);
    } else {
        echo '<p class="error">✗ No se pudo crear el archivo de base de datos.</p>';
        echo '<p>Asegúrate de que el directorio tenga permisos adecuados.</p>';
        die();
    }
}

// Intentar abrir la base de datos
echo '<h2>3. Conectando a la base de datos</h2>';
try {
    $dsn = "sqlite:$db_path";
    $db = new PDO($dsn);
    $db->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    
    echo '<p class="success">✓ Conexión a SQLite establecida correctamente.</p>';
    
    // Verificar la estructura de la base de datos
    $tables = $db->query("SELECT name FROM sqlite_master WHERE type='table'")->fetchAll(PDO::FETCH_COLUMN);
    
    echo '<p>Tablas encontradas: ' . count($tables) . '</p>';
    
    if (count($tables) > 0) {
        echo '<table>';
        echo '<tr><th>Tabla</th><th>Registros</th></tr>';
        
        foreach ($tables as $table) {
            if ($table != 'sqlite_sequence') {
                $count = $db->query("SELECT COUNT(*) FROM " . $table)->fetchColumn();
                echo '<tr>';
                echo '<td>' . htmlspecialchars($table) . '</td>';
                echo '<td>' . $count . '</td>';
                echo '</tr>';
            }
        }
        
        echo '</table>';
        
        // Verificar si faltan tablas importantes
        $required_tables = ['users', 'cars', 'car_images', 'reservations', 'payments', 'reviews'];
        $missing_tables = array_diff($required_tables, $tables);
        
        if (count($missing_tables) > 0) {
            echo '<p class="warning">⚠ Faltan algunas tablas importantes: ' . implode(', ', $missing_tables) . '</p>';
            echo '<p>Si estas tablas faltan, la aplicación no funcionará correctamente. 
                   Considera ejecutar el script de inicialización de la base de datos.</p>';
        } else {
            echo '<p class="success">✓ Todas las tablas requeridas están presentes.</p>';
        }
    } else {
        echo '<p class="warning">⚠ No se encontraron tablas en la base de datos.</p>';
        echo '<p>La base de datos existe pero está vacía. Necesitas inicializar las tablas.</p>';
        
        echo '<h3>Solución</h3>';
        echo '<p>Puedes intentar inicializar la base de datos con el siguiente código:</p>';
        echo '<pre>// Importar el esquema de la base de datos
$sql = file_get_contents(__DIR__ . \'/database.sql\');
$db->exec($sql);</pre>';
        
        echo '<p>O usar el script principal de la aplicación que debería inicializar la base de datos automáticamente.</p>';
    }
    
} catch (PDOException $e) {
    echo '<p class="error">✗ Error al conectar a la base de datos: ' . htmlspecialchars($e->getMessage()) . '</p>';
    
    echo '<h3>Soluciones posibles:</h3>';
    echo '<ol>';
    echo '<li>Eliminar el archivo de base de datos actual y dejar que la aplicación lo cree automáticamente.</li>';
    echo '<li>Verificar que la extensión PDO SQLite esté activada en PHP.</li>';
    echo '<li>Comprobar los permisos del archivo de base de datos y del directorio.</li>';
    echo '</ol>';
}

echo '<h2>4. Siguiente pasos</h2>';
echo '<p>Si todo está correcto, deberías poder usar la aplicación sin problemas.</p>';
echo '<p>Si encontraste errores:</p>';
echo '<ol>';
echo '<li>Elimina el archivo database.db y deja que la aplicación lo cree de nuevo.</li>';
echo '<li>Asegúrate de que el directorio del proyecto tenga permisos de escritura.</li>';
echo '<li>Comprueba que la extensión PDO SQLite esté habilitada en tu instalación de PHP.</li>';
echo '<li>Si usas GitHub Codespaces, intenta reiniciar el contenedor.</li>';
echo '</ol>';

echo '<p><a href="/">Volver a la aplicación principal</a></p>';
?>