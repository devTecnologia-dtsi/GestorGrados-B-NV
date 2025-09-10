<?php
/**
 * Clase para gestionar solicitudes a diferentes APIs relacionadas con grados académicos.
 * * Esta clase proporciona métodos para realizar solicitudes a APIs que permiten obtener
 * información sobre estudiantes, postulaciones y datos académicos. Se utilizan cURL 
 * para las comunicaciones HTTP y se manejan las respuestas en formato JSON.
 */
class RequestGestionGrados
{
    /**
     * Realiza una solicitud ala api principal de gestor de grados.
     * https://uniminuto.test.digibee.io/pipeline/uniminuto/v1/gestor-grados/
     *
     * @param string $fn Nombre de la función a invocar en la API.
     * @param string $body Cuerpo de la solicitud en formato JSON.
     * * @return string Respuesta de la API en formato JSON.
     */
    function requestGestorGrados($fn, $body)
    {
        // Obtener variables de entorno
        $URLAPIGESTORGRADOS = $_ENV['URLAPIGESTORGRADOS'] ?? null;
        $KEYAPIGESTORGRADOS = $_ENV['KEYAPIGESTORGRADOS'] ?? null;

        if (empty($URLAPIGESTORGRADOS) || empty($KEYAPIGESTORGRADOS)) {
            // Se asume que en producción se debería lanzar una excepción o manejar este caso de forma más robusta.
            return false; // Retorna false si las variables de entorno no están configuradas
        }

        $curl = curl_init();
        curl_setopt_array(
            $curl,
            array(
                CURLOPT_URL => $URLAPIGESTORGRADOS . $fn,
                CURLOPT_RETURNTRANSFER => true,
                CURLOPT_ENCODING => '',
                CURLOPT_MAXREDIRS => 10,
                CURLOPT_TIMEOUT => 30, // Reducir un poco el timeout a 30 segundos
                CURLOPT_FOLLOWLOCATION => true,
                CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
                CURLOPT_CUSTOMREQUEST => 'POST',
                CURLOPT_POSTFIELDS => $body,
                CURLOPT_HTTPHEADER => array(
                    'ApiKey: ' . $KEYAPIGESTORGRADOS,
                    'Content-Type: application/json'
                ),
                CURLOPT_FAILONERROR => false, // No falla en errores HTTP, permite obtener el cuerpo de la respuesta
                CURLOPT_SSL_VERIFYPEER => false, // IMPORTANTE: Deshabilita la verificación SSL para desarrollo local
                CURLOPT_SSL_VERIFYHOST => false, // IMPORTANTE: Deshabilita la verificación SSL para desarrollo local
            )
        );

        $response = curl_exec($curl);
        $err = curl_error($curl); // Captura errores de cURL
        $http_code = curl_getinfo($curl, CURLINFO_HTTP_CODE); // Captura el código de estado HTTP

        curl_close($curl);

        if ($err) {
            // Si hay un error de cURL (ej. problema de red, DNS), se registra internamente o se maneja.
            // En producción, podrías loggear esto en un sistema de logs apropiado.
            return false; // Indica un fallo en la comunicación
        } else {
            // Verifica el código de estado HTTP
            if ($http_code >= 200 && $http_code < 300) {
                // La solicitud fue exitosa (código 2xx)
                return $response;
            } else {
                // La API externa respondió con un código de error (4xx, 5xx)
                // Se podría retornar el $response aquí si el controlador necesita analizar el JSON de error de la API.
                return false; // Retornar false para que AccesoUsuarioController maneje el fallo
            }
        }
    }

    /**
     * Realiza una solicitud a https://uniminuto.api.digibee.io/pipeline/uniminuto/v2/gestor-grados/obtenerPostulacion 
     * para obtener información sobre un postulado.
     *
     * @param string $cn Código Idbanner del estudiante en el sistema.
     * * @return object Información del estudiante en formato JSON.
     */
    public function requestBannerEstudiantePostulado($cn)
    {
        $URLAPIPOSTULADOGRADO = $_ENV['URLAPIPOSTULADOGRADO'] ?? null;
        $KEYAPIPOSTULADOGRADO = $_ENV['KEYAPIPOSTULADOGRADO'] ?? null;

        if (empty($URLAPIPOSTULADOGRADO) || empty($KEYAPIPOSTULADOGRADO)) {
            // Se asume que en producción se debería lanzar una excepción o manejar este caso de forma más robusta.
            return false; // Retorna false si las variables de entorno no están configuradas
        }

        $url_completa = $URLAPIPOSTULADOGRADO . '?id=' . $cn;

        $curl = curl_init();
        curl_setopt_array(
            $curl,
            array(
                CURLOPT_URL => $url_completa,
                CURLOPT_RETURNTRANSFER => true,
                CURLOPT_ENCODING => '',
                CURLOPT_MAXREDIRS => 10,
                CURLOPT_TIMEOUT => 30, // Reducir timeout a 30 segundos
                CURLOPT_FOLLOWLOCATION => true,
                CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
                CURLOPT_CUSTOMREQUEST => 'GET',
                CURLOPT_HTTPHEADER => array(
                    'apikey: ' . $KEYAPIPOSTULADOGRADO,
                    'Content-Type: application/json'
                ),
                CURLOPT_FAILONERROR => false, // IMPORTANTE: Permite obtener el cuerpo de la respuesta en caso de errores HTTP
                CURLOPT_SSL_VERIFYPEER => false, // IMPORTANTE: Deshabilita la verificación SSL para desarrollo local
                CURLOPT_SSL_VERIFYHOST => false, // IMPORTANTE: Deshabilita la verificación SSL para desarrollo local
            )
        );

        $response = curl_exec($curl);
        $err = curl_error($curl); // Captura errores de cURL
        $http_code = curl_getinfo($curl, CURLINFO_HTTP_CODE); // Captura el código de estado HTTP

        curl_close($curl);

        if ($err) {
            // Si hay un error de cURL, se registra internamente o se maneja.
            return false; // Indica un fallo en la comunicación
        } else {
            // Verifica el código de estado HTTP
            if ($http_code >= 200 && $http_code < 300) {
                // La solicitud fue exitosa (código 2xx)
                $decoded_response = json_decode($response); // Decodifica sin 'true' para obtener un objeto

                if (json_last_error() === JSON_ERROR_NONE) {
                    return $decoded_response;
                } else {
                    // Si la respuesta no es un JSON válido, se registra o se maneja.
                    return false; // Retorna false si la respuesta no es un JSON válido
                }
            } else {
                // La API externa respondió con un código de error (4xx, 5xx)
                // Se podría retornar el $response aquí si el controlador necesita analizar el JSON de error de la API.
                return false; // Retornar false para que AccesoUsuarioController maneje el fallo
            }
        }
    }

    /**
     * Realiza una consulta a https://uniminuto.test.digibee.io/pipeline/uniminuto/v1/directorio-activo/consultarCuenta para obtener datos de un usuario por su correo.
     *
     * @param string $correo Correo electrónico del usuario.
     * * @return string Respuesta de la API en formato JSON.
     */
    function requestConsultaDA($correo)
    {
        $URLAPIDA = $_ENV['URLAPIDA'] ?? null;
        $KEYAPIDA = $_ENV['KEYAPIDA'] ?? null;

        if (empty($URLAPIDA) || empty($KEYAPIDA)) {
            // Considerar lanzar una excepción o un manejo de errores más robusto en producción
            return false;
        }

        $post_fields = '{ "peticion": { "filter": "(mail=' . $correo . ')" } }';

        $curl = curl_init();
        curl_setopt_array(
            $curl,
            array(
                CURLOPT_URL => $URLAPIDA,
                CURLOPT_RETURNTRANSFER => true,
                CURLOPT_ENCODING => '',
                CURLOPT_MAXREDIRS => 10,
                CURLOPT_TIMEOUT => 30,
                CURLOPT_FOLLOWLOCATION => true,
                CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
                CURLOPT_CUSTOMREQUEST => 'POST',
                CURLOPT_POSTFIELDS => $post_fields,
                CURLOPT_HTTPHEADER => array(
                    'ApiKey: ' . $KEYAPIDA,
                    'Content-Type: application/json'
                ),
                CURLOPT_SSL_VERIFYPEER => false, // IMPORTANTE: Deshabilita la verificación SSL para desarrollo local
                CURLOPT_SSL_VERIFYHOST => false, // IMPORTANTE: Deshabilita la verificación SSL para desarrollo local
                // CURLOPT_VERBOSE => true, // ELIMINAR O COMENTAR: Solo para depuración de cURL a bajo nivel
                // CURLOPT_STDERR => fopen('php://stderr', 'w') // ELIMINAR O COMENTAR: Solo para depuración de cURL a bajo nivel
            )
        );

        $response = curl_exec($curl);
        $err = curl_error($curl); // Captura errores de cURL
        $http_code = curl_getinfo($curl, CURLINFO_HTTP_CODE); // Captura el código de estado HTTP

        curl_close($curl);

        if ($err) {
            // Manejo de error de cURL. En producción, un log apropiado.
            return false;
        } else {
            // Se podría añadir manejo de $http_code aquí si se desea una validación similar
            // a las otras funciones antes de retornar $response.
            return $response;
        }
    }

    /**
     * Realiza una solicitud a https://comunidad.uniminuto.edu/estudiantes/Estudiantes/DatosAcademicos/ para obtener datos académicos de un estudiante.
     *
     * @param string $idBanner ID del estudiante en el sistema.
     * * @return object Información académica en formato JSON.
     */
    function requestDatosAcademicos($idBanner)
    {
        $URLAPIDATOSACADEMICOS = $_ENV['URLAPIDATOSACADEMICOS'] ?? null;

        if (empty($URLAPIDATOSACADEMICOS)) {
            // Considerar lanzar una excepción o un manejo de errores más robusto en producción
            return false;
        }

        $curl = curl_init();
        curl_setopt_array(
            $curl,
            array(
                CURLOPT_URL => $URLAPIDATOSACADEMICOS . $idBanner,
                CURLOPT_RETURNTRANSFER => true,
                CURLOPT_ENCODING => '',
                CURLOPT_MAXREDIRS => 10,
                CURLOPT_TIMEOUT => 30, // Establecer un timeout razonable
                CURLOPT_FOLLOWLOCATION => true,
                CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
                CURLOPT_CUSTOMREQUEST => 'GET',
                CURLOPT_SSL_VERIFYPEER => false, // IMPORTANTE: Deshabilita la verificación SSL para desarrollo local
                CURLOPT_SSL_VERIFYHOST => false, // IMPORTANTE: Deshabilita la verificación SSL para desarrollo local
            )
        );

        $response = curl_exec($curl);
        $err = curl_error($curl); // Captura errores de cURL
        $http_code = curl_getinfo($curl, CURLINFO_HTTP_CODE); // Captura el código de estado HTTP

        curl_close($curl);

        if ($err) {
            // Manejo de error de cURL. En producción, un log apropiado.
            return false;
        } else {
            if ($http_code >= 200 && $http_code < 300) {
                return json_decode($response);
            } else {
                // Manejo de error HTTP. Podrías loggear $response para ver el error de la API.
                return false;
            }
        }
    }

    /**
     * Realiza una solicitud a https://uniminuto.test.digibee.io/pipeline/uniminuto/v1/datos-identidad/ para obtener datos de SNIES de un programa.
     *
     * @param string $codPrograma Código del programa.
     * @param string $codSede Código de la sede.
     * * @return object Información de SNIES en formato JSON.
     */
    function requestDatosSnies($codPrograma, $codSede)
    {
        $URLAPISNIES = $_ENV['URLAPISNIES'] ?? null;
        $KEYAPISNIES = $_ENV['KEYAPISNIES'] ?? null;

        if (empty($URLAPISNIES) || empty($KEYAPISNIES)) {
            // Considerar lanzar una excepción o un manejo de errores más robusto en producción
            return false;
        }

        $curl = curl_init();
        curl_setopt_array(
            $curl,
            array(
                CURLOPT_URL => $URLAPISNIES . 'snies?codigoCorto=' . $codPrograma . '&sede=' . $codSede,
                CURLOPT_RETURNTRANSFER => true,
                CURLOPT_ENCODING => '',
                CURLOPT_MAXREDIRS => 10,
                CURLOPT_TIMEOUT => 30, // Establecer un timeout razonable
                CURLOPT_FOLLOWLOCATION => true,
                CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
                CURLOPT_CUSTOMREQUEST => 'GET',
                CURLOPT_HTTPHEADER => array(
                    'apikey: ' . $KEYAPISNIES,
                    'Content-Type: application/json'
                ),
                CURLOPT_SSL_VERIFYPEER => false, // IMPORTANTE: Deshabilita la verificación SSL para desarrollo local
                CURLOPT_SSL_VERIFYHOST => false, // IMPORTANTE: Deshabilita la verificación SSL para desarrollo local
            )
        );

        $response = curl_exec($curl);
        $err = curl_error($curl); // Captura errores de cURL
        $http_code = curl_getinfo($curl, CURLINFO_HTTP_CODE); // Captura el código de estado HTTP

        curl_close($curl);

        if ($err) {
            // Manejo de error de cURL. En producción, un log apropiado.
            return false;
        } else {
            if ($http_code >= 200 && $http_code < 300) {
                return json_decode($response);
            } else {
                // Manejo de error HTTP. Podrías loggear $response para ver el error de la API.
                return false;
            }
        }
    }
}

?>