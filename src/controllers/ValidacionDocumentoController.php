<?php
require_once __DIR__ . '/../models/request-gestion-grados.php';
require_once __DIR__ . '/../models/access-token.php';
require_once __DIR__ . '/../models/envio-correo.php';

/**
 * Clase para manejar la validación de documentos.
 *
 * Esta clase proporciona un método para la validar documentos y enviar notificaciones
 * por correo electrónico a los usuarios correspondientes tras la validación.
 */
class ValidacionDocumentoController
{
    /**
     * Valida el estado del postulado y envía un correo de notificación.
     *
     * Este método procesa la solicitud de validación de un documento, valida el token
     * del usuario, realiza la validación del documento y envía un correo al usuario
     * con información sobre el proceso de validación.
     * 
     * @return void
     */
    public function validacionDocumento()
    {
        $data = file_get_contents('php://input');
        $dataDec = json_decode($data, true);

        $fn = $_GET['fn'];
        $sanitizedData = [];
        foreach ($dataDec as $key => $value) {
            $sanitizedData[$key] = htmlspecialchars(strip_tags(trim($dataDec[$key])), ENT_QUOTES, 'UTF-8');
        }

        $dataDec = $sanitizedData;

       if (isset($dataDec['token'])) {
            $classGestionGrados = new RequestGestionGrados;
            $classAccessToken = new AccessToken;
            $userData = $classAccessToken->validateToken($dataDec['token']);
            if (!$userData) {
                http_response_code(401);
            }
            else {
                unset($dataDec['token']);


                $data = json_encode($dataDec, true);
                $respuesta = $classGestionGrados->requestGestorGrados($fn, $data);

                $respuestaArray = json_decode($respuesta, true);

                // Envio de correo al usuario
                $bodyArray = json_decode($respuestaArray['body'], true);
                $id = $bodyArray[0]['id'];
                if ($id > 0) {
                    $mensaje = "<h3>Estimado Estudiante</h3>";
                    $mensaje .= $dataDec["descripcion"];
                    $mensaje .= "<br><br>";
                    $mensaje .= "Ingresa con tu usuario y contrase&#241;a para validar tu proceso.<br> Ingresa <a href='https://comunidad.uniminuto.edu/genesis/grados/'>aquí</a>";
                    $para = $dataDec["correo"];

                    $subject = "Validación de Documento Grado";
                    $classEnvioCorreo = new EnvioCorreo();
                    $classEnvioCorreo->envioCorreo($para, $mensaje, $subject);
                }

                echo $respuesta;
            }
        }
        else {
            http_response_code(401);
        }
    } 
}
