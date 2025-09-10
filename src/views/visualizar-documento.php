<?php


header("Access-Control-Allow-Origin: *"); // Ajusta en producción
header("Access-Control-Allow-Methods: POST, GET, OPTIONS");
header("Access-Control-Allow-Headers: Content-Type, Authorization");
header("Content-Type: application/json; charset=UTF-8");

// Manejar solicitudes OPTIONS (preflight requests)
if ($_SERVER['REQUEST_METHOD'] == 'OPTIONS') {
    http_response_code(200);
    exit();
}

// Carga de variables de entorno
require __DIR__ . '/../../vendor/autoload.php'; 
$dotenv = Dotenv\Dotenv::createImmutable(__DIR__ . '/../..'); 
$dotenv->load();





// Ahora obtenemos la API Key desde las variables de entorno
define('APIKEY_VALUE', $_ENV['APIKEY']);


// URL del endpoint de Digibee para la consulta de documentos
define('DIGIBEE_VISUALIZACION_URL', 'https://uniminuto.test.digibee.io/pipeline/uniminuto/v2/gestion-docs/consulta');


// 1. Obtener los datos enviados desde Angular (JSON)
$input = file_get_contents('php://input');
$requestData = json_decode($input, true);

// Verificar si se recibieron los datos esperados
if (!isset($requestData['ID']) || !isset($requestData['tipoDocumento']) || !isset($requestData['applicationID'])) {
    http_response_code(400); // Bad Request
    echo json_encode(['status' => 'error', 'message' => 'Parámetros de entrada incompletos. Se requiere ID, tipoDocumento y applicationID.']);
    exit();
}

$idBanner = $requestData['ID'];
$tipoDocumento = $requestData['tipoDocumento'];
$applicationID = $requestData['applicationID'];

// 2. Construir el cuerpo de la petición para Digibee
$digibeePayload = [
    "InformacionDocumentosBDM" => [
        "consultaDocumentos" => [
            "ID" => $idBanner,
            "tipoDocumento" => $tipoDocumento,
            "applicationID" => $applicationID
        ]
    ]
];

$jsonDigibeePayload = json_encode($digibeePayload);

// 3. Realizar la petición POST a la API de Digibee usando cURL
$ch = curl_init(DIGIBEE_VISUALIZACION_URL);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true); // Devuelve la respuesta como string
curl_setopt($ch, CURLOPT_POST, true);           // Petición POST
curl_setopt($ch, CURLOPT_POSTFIELDS, $jsonDigibeePayload); // Datos a enviar en el cuerpo

curl_setopt($ch, CURLOPT_HTTPHEADER, [
    'Content-Type: application/json',
    'apikey: ' . APIKEY_VALUE 
]);

$response = curl_exec($ch);
$httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);

// Manejo de errores de cURL
if (curl_errno($ch)) {
    $error_msg = curl_error($ch);
    curl_close($ch);
    http_response_code(500); // Internal Server Error
    echo json_encode(['status' => 'error', 'message' => 'Error al conectar con la API de Digibee: ' . $error_msg]);
    exit();
}

curl_close($ch);

// 4. Procesar la respuesta de Digibee
$responseData = json_decode($response, true);

// 5. Devolver la respuesta al frontend (Angular)
if ($httpCode >= 200 && $httpCode < 300) {
    // Si la respuesta de Digibee fue exitosa, reenviarla directamente o procesarla
    echo $response;
} else {
    // Si Digibee devolvió un error, reenviar el error o un mensaje personalizado
    http_response_code($httpCode); 
    echo json_encode(['status' => 'error', 'message' => 'La API de Digibee respondió con un error.', 'details' => $responseData]);
}

?>