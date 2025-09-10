<?php
// Incluye los modelos necesarios para la autenticación y la comunicación con BDM
require_once __DIR__ . '/../models/access-token.php';
require_once __DIR__ . '/../models/RequestBDM.php';

class CargaDocumentosController
{
    public function obtenerDocumentos()
    {
        // Define las cabeceras para permitir peticiones desde el frontend de Angular
        header('Content-Type: application/json');
        header("Access-Control-Allow-Origin: http://localhost:4200");
        header("Access-Control-Allow-Methods: POST, GET, OPTIONS, DELETE, PUT");
        header("Access-Control-Allow-Headers: Content-Type, Authorization");
        header("Access-Control-Allow-Credentials: true");

        //El navegador las envía automáticamente para verificar si el servidor permite la petición real. Si se trata de una petición OPTIONS, se responde con 200 OK y se termina la ejecución.
        if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
            http_response_code(200);
            exit();
        }

        // Obtiene el token de la cabecera 'Authorization'
        $headers = getallheaders();
        $token = $headers['Authorization'] ?? null;

        // Verifica si el token existe y tiene el formato 'Bearer'
        if (!$token || !str_starts_with($token, 'Bearer ')) {
            http_response_code(401);
            echo json_encode(['error' => 'Token de autorización Bearer no proporcionado o inválido.']);
            return;
        }

        // Extrae el JWT (JSON Web Token) del encabezado 'Bearer'
        $jwt = trim(str_replace('Bearer', '', $token));
        $accessToken = new AccessToken();
        $usuarioData = $accessToken->validateToken($jwt);

        // Si el token no es válido (ej. expirado, manipulado), se devuelve un error 403
        if (!$usuarioData) {
            http_response_code(403);
            echo json_encode(['error' => 'Token inválido o expirado. Acceso denegado.']);
            return;
        }

        $tokenPayloadData = $usuarioData['data'] ?? null;

        // Verifica que los datos del usuario existan en el token
        if (!$tokenPayloadData) {
            http_response_code(400);
            echo json_encode(['error' => 'No se encontraron datos de usuario en el token.']);
            return;
        }

        // Obtiene el ID del usuario ('company') del payload del token.
        // Este es el identificador único del usuario en el sistema.
        $idBanner = $tokenPayloadData->company ?? null;
         


        if (!$idBanner) {
            http_response_code(400);
            echo json_encode(['error' => 'ID de usuario (company) no encontrado en el token.']);
            return;
        }

        
        $requestBody = json_decode(file_get_contents('php://input'), true);

        // 2. Extraer los valores necesarios del payload de Angular
        $dmType = $requestBody['dmType'] ?? null;
        $idFromPayload = $requestBody['indexes']['ID'] ?? null;
        $admissionsRequirement = $requestBody['indexes']['ADMISSIONS REQUIREMENT'] ?? null;

        // Validar que los datos de consulta BDM estén presentes en el payload
        if (!$dmType || !$idFromPayload || !$admissionsRequirement) {
            http_response_code(400);
            echo json_encode(['error' => 'Datos de consulta para BDM (dmType, ID, ADMISSIONS REQUIREMENT) incompletos en la solicitud.']);
            return;
        }

        // Opcional: Validar que el ID del payload coincida con el ID del token por seguridad.
        // Es buena práctica usar el ID del token como fuente principal de verdad para el usuario.
        if ($idFromPayload !== $idBanner) {
            $idConsultaBDM = $idBanner; // Prioriza el ID del token
        } else {
            $idConsultaBDM = $idFromPayload; // Si coinciden, usa el ID del payload (que debería ser el mismo)
        }
        // --- FIN DE LOS CAMBIOS CLAVE ---

        // Instanciar el modelo para la API de BDM
        $requestBDM = new RequestBDM();

        // Realizar la consulta a la API de BDM usando los valores recibidos de Angular
        // Asegúrate de que queryDocuments en RequestBDM.php acepte estos tres parámetros
        $documentosBDM = $requestBDM->queryDocuments($idConsultaBDM, $admissionsRequirement, $dmType);

        if ($documentosBDM === false) {
            http_response_code(500);
            echo json_encode(['error' => 'Error al consultar documentos en la BDM.']);
            return;
        }

        // Si todo fue bien, devuelve los documentos obtenidos de la BDM
        http_response_code(200);
        echo json_encode($documentosBDM);
    }
}