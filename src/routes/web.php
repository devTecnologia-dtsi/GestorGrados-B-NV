<?php 

// Obtener la URL solicitada
$url = $_SERVER['REQUEST_URI'];

// Obtener el método HTTP de la solicitud (GET, POST, etc.)
$metodo = $_SERVER['REQUEST_METHOD'];

// Analizar la URL para obtener sus componentes
$urlComponents = parse_url($url);

// Obtener la ruta y los parámetros de consulta
$path = $urlComponents['path'] ?? ''; // Ruta de la URL
$query = $urlComponents['query'] ?? ''; // Parámetros de consulta

// Separar los segmentos de la ruta
$pathSegments = explode('/', $path); // Dividir la ruta en segmentos
$rutaBase = $pathSegments[1] ?? '/'; 

// Obtener el último segmento de la ruta
$endPathSegments = $pathSegments[count($pathSegments) - 1];

// Analizar los query parameters

// Procesar las rutas según el último segmento y el método HTTP
switch (true) {
    case 'acceso-usuario' === $endPathSegments && $rutaBase !== null:
        require_once __DIR__ . '/../views/acceso-usuario.php';
        break;
    case 'gestion-funciones' === $endPathSegments && $rutaBase !== null:
        require_once __DIR__ . '/../views/gestion-funciones.php';
        break;
    case 'upload-documentos' === $endPathSegments && $rutaBase !== null:
        require_once __DIR__ . '/../views/upload-documentos.php';
        break;
    case 'rechazo-documento-usuario' === $endPathSegments && 'POST' === $metodo:
        require_once __DIR__ . '/../views/rechazo-documento-usuario.php';
        break;
    case 'validacion-documento-usuario' === $endPathSegments && 'POST' === $metodo:
        require_once __DIR__ . '/../views/validacion-documento-usuario.php';
        break;    
    case 'error' === $rutaBase:
        require_once __DIR__ . '/../views/error.php';
        break;
    default:
            // Enviar un código de estado 503 
        if (!headers_sent()) {
            header('HTTP/1.1 503 Service Unavailable'); // Indicar que el servicio no está disponible
        }
        break;
}