<?php
// Cargar el autoloader de Composer
require_once __DIR__ . '/../../vendor/autoload.php';

// Construir la ruta a la ra�z del proyecto para Dotenv
$projectRoot = realpath(__DIR__ . '/../../');

// Cargar las variables de entorno desde el archivo .env
$dotenv = Dotenv\Dotenv::createImmutable($projectRoot);
try {
    $dotenv->load();
} catch (Dotenv\Exception\InvalidPathException $e) {
    // Manejo de errores en producci�n
    http_response_code(500);
    echo json_encode(['error' => 'Error de configuraci�n del servidor.']);
    exit;
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(['error' => 'Error de configuraci�n del servidor.']);
    exit;
}

// Incluir el controlador
require_once __DIR__ . '/../controllers/CargaDocumentosController.php';

// Instanciar y llamar al m�todo para obtener documentos
$cargaDocumentosController = new CargaDocumentosController();
$cargaDocumentosController->obtenerDocumentos();
?>