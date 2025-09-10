<?php
require_once __DIR__ . '/../models/request-gestion-grados.php';
require_once __DIR__ . '/../models/access-token.php';
require_once __DIR__ . '/../models/envio-correo.php';

/**
 * Clase para manejar el rechazo de documentos de postulación a grado.
 *
 * Esta clase proporciona un método para rechazar documentos y notificar
 * a los estudiantes mediante correo electrónico.
 */
class RechazoDocumentoController
{
    public function rechazoDocumento()
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
                $data = json_encode($dataDec, true);
                $respuesta = $classGestionGrados->requestGestorGrados($fn, $data);

                $respuestaArray = json_decode($respuesta, true);

                // Envio de correo al usuario
                $bodyArray = json_decode($respuestaArray['body'], true);
                $id = $bodyArray[0]['id'];
                if ($id > 0) {
                    $mensaje = "Respetado estudiante<br><br>";
                    if (!isset($dataDec["idDocumento"])) {
                        $mensaje .= "Se han  Rechazado sus Documentos de postulacion a grado  de acuerdo al siguiente mensaje:<br><br>";
                    } else {
                        $mensaje .= "Su<b> " . $dataDec["nombreDocumento"] . "</b> Ha sido rechazado de acuerdo al siguiente mensaje:<br><br>";
                    }

                    $mensaje .= $dataDec["descripcion"];
                    $mensaje .= "<br><br>";
                    $mensaje .= "Ingresa con tu usuario y contrase&#241;a para validar tu proceso.<br> Ingresa <a href='https://comunidad.uniminuto.edu/genesis/grados/'>aquí</a>";

                    $para = $dataDec["correo"];
                    $subject = "Rechazo de Documentos Postulacion a Grado";
                 
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
