<?php
// ¡IMPORTANTE! Estas cabeceras deben ser lo PRIMERO en el script de entrada.
// Si ya hay alguna salida (incluso un espacio en blanco), fallarán.

// Permite solicitudes desde tu frontend Angular (http://localhost:4200)
header("Access-Control-Allow-Origin: http://localhost:4200");
// Métodos HTTP permitidos para esta API
header("Access-Control-Allow-Methods: GET, POST, PUT, DELETE, OPTIONS");
// Cabeceras que el cliente puede enviar en la solicitud real
header("Access-Control-Allow-Headers: Content-Type, Authorization");
// Permite que las credenciales (como cookies o tokens de autenticación) se incluyan en la solicitud
header("Access-Control-Allow-Credentials: true");

// Manejo de la solicitud OPTIONS (preflight request)
// El navegador envía una solicitud OPTIONS antes de una solicitud "compleja" (POST, PUT, DELETE, etc. o con custom headers).
// Si el método es OPTIONS, solo respondemos con las cabeceras CORS y terminamos.
if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200); // Responde con éxito para el preflight
    exit(); // Termina la ejecución para el preflight request
}

// Incluir el controlador principal de la API
require_once __DIR__ . '/../controllers/GestionFuncionesController.php';

// Crear una instancia del controlador
$controller = new GestionFuncionesController();

// Llamar al método principal del controlador para manejar la solicitud
$controller->gestionFunciones();

// Opcional: Asegurarse de que no haya ninguna salida adicional después de la lógica del controlador
// exit();
?>