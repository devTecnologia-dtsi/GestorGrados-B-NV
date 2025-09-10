<?php
// Cargar el autoloader de Composer
require_once __DIR__ . '/../../vendor/autoload.php';

// Construir la ruta a la raíz del proyecto donde está .env de forma robusta
$projectRoot = realpath(__DIR__ . '/../../');

// Cargar las variables de entorno desde el archivo .env
$dotenv = Dotenv\Dotenv::createImmutable($projectRoot);
try {
    $dotenv->load();
    
} catch (Dotenv\Exception\InvalidPathException $e) {
    
} catch (Exception $e) {
    
}
header("Access-Control-Allow-Origin: http://localhost:4200"); // Permite solicitudes desde tu frontend Angular
header("Access-Control-Allow-Methods: POST, GET, OPTIONS, DELETE, PUT"); // Métodos permitidos
header("Access-Control-Allow-Headers: Content-Type, Authorization"); // Encabezados permitidos
header("Access-Control-Allow-Credentials: true");

if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    // Si la solicitud es OPTIONS, solo enviamos los encabezados y salimos.
    // El navegador ya ha obtenido la "aprobación" para la solicitud real.
    http_response_code(200); // Devuelve un 200 OK para la preflight
    exit(); // ¡Importante! Detiene la ejecución del script aquí para OPTIONS
}

// Ahora puedes incluir tus otros archivos
require_once __DIR__ . '/../controllers/AccesoUsuarioController.php';

$accesoUsuarioController = new AccesoUsuarioController();
$accesoUsuarioController->accesoUsuario();
?>