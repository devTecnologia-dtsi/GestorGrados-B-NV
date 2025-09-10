<?php
require_once __DIR__ . '/../models/request-gestion-grados.php';
require_once __DIR__ . '/../models/access-token.php';

/**
 * Clase para gestionar las funcionalidades relacionadas con gestor de grados.
 *
 * Esta clase proporciona un método para manejar solicitudes de gestión de grados
 * y validar tokens de acceso de los usuarios.
 */
class GestionFuncionesController
{
    /**
     * Maneja la gestión de funcionalidades relacionadas con grados.
     *
     * Este método procesa la solicitud enviada por el cliente, valida el token de acceso,
     * sanitiza los datos de entrada, y realiza la acción solicitada en función del parámetro 'fn'.
     * Además, envía la respuesta al cliente en formato JSON.
     *
     * @return void
     */
    public function gestionFunciones()
    {
        // 1. Manejo y validación del parámetro 'fn'
        // Si 'fn' no está presente en la URL, se considera una solicitud incorrecta.
        if (!isset($_GET['fn'])) {
            http_response_code(400); // Bad Request
            header('Content-Type: application/json');
            echo json_encode(['error' => 'Parámetro "fn" (función) es requerido en la URL.']);
            exit(); // Termina la ejecución para evitar más errores y salida prematura
        }
        $fn = $_GET['fn']; // Ahora es seguro acceder a $_GET['fn']

        // 2. Manejo y validación del cuerpo de la solicitud JSON
        $input = file_get_contents('php://input');
        $dataDec = json_decode($input, true);

        // Si $dataDec no es un array (lo que ocurre si el JSON es inválido o el cuerpo está vacío),
        // manejar como un error de solicitud.
        if (!is_array($dataDec)) {
            http_response_code(400); // Bad Request
            header('Content-Type: application/json');
            echo json_encode(['error' => 'Cuerpo de la solicitud inválido o vacío. Se esperaba un JSON válido.']);
            exit(); // Termina la ejecución
        }

        $sanitizedData = [];
        // Ahora es seguro iterar sobre $dataDec porque sabemos que es un array
        foreach ($dataDec as $key => $value) {
            // Asegúrate de que $value sea una cadena antes de trim y htmlspecialchars
            $sanitizedData[$key] = htmlspecialchars(strip_tags(trim((string) $value)), ENT_QUOTES, 'UTF-8');
        }

        $dataDec = $sanitizedData;

        // 3. Gestión de la lógica y respuestas HTTP
        if (isset($dataDec['token'])) {
            $classGestionGrados = new RequestGestionGrados();
            $classAccessToken = new AccessToken();
            $userData = $classAccessToken->validateToken($dataDec['token']);

            if (!$userData) {
                http_response_code(401); // Unauthorized
                header('Content-Type: application/json');
                echo json_encode(['error' => 'Token de acceso inválido o expirado.']);
                exit(); // Termina la ejecución
            } else {
                unset($dataDec['token']);
                $data = json_encode($dataDec); // No necesitas 'true' aquí, ya es un JSON

                $respuesta = $classGestionGrados->requestGestorGrados($fn, $data);

                // Siempre envía el encabezado de Content-Type antes del echo
                header('Content-Type: application/json');
                http_response_code(200); // OK
                echo $respuesta;
            }
        } else {
            http_response_code(401); // Unauthorized
            header('Content-Type: application/json');
            echo json_encode(['error' => 'Token de acceso no proporcionado.']);
            exit(); // Termina la ejecución
        }
    }
}