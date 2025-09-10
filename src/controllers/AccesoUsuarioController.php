<?php
require_once __DIR__ . '/../models/request-gestion-grados.php';
require_once __DIR__ . '/../models/access-token.php';

class AccesoUsuarioController
{
    public function accesoUsuario()
    {
        $data = json_decode(file_get_contents('php://input'), true);

        // Inicializar variables de respuesta con valores por defecto para el fallo
        $resp = false;
        $message = "El usuario no tiene permisos de acceso"; // Mensaje por defecto para la mayoría de los fallos
        $token = "";

        if (isset($data['access'])) {
            $access = $data['access'];
            $classAccessToken = new AccessToken();

            // Intenta desencriptar el correo
            try {
                $correo = $classAccessToken->decryptData($access);

                if (!$correo) {
                    $message = "Error en la desencriptación del token de acceso o token inválido.";
                    // La resp ya es false por defecto
                    // El token ya es "" por defecto
                    $this->sendJsonResponse($message, $resp, $token); // Envía la respuesta y termina
                    return;
                }

            } catch (Exception $e) {
                // Captura cualquier excepción durante la desencriptación (como OpenSSL error)
                $message = "Error de desencriptación: " . $e->getMessage();
                // La resp ya es false por defecto
                // El token ya es "" por defecto
                $this->sendJsonResponse($message, $resp, $token); // Envía la respuesta y termina
                return;
            }


            $classGestionGrados = new RequestGestionGrados;

            // Consulta los datos del directorio activo
            $da_response_raw = $classGestionGrados->requestConsultaDA($correo);
            

            $respuesta_da = json_decode($da_response_raw, true);
            

            // Si la respuesta del DA no es válida o está vacía
            if (!$respuesta_da || !isset($respuesta_da[0])) {
                $message = "No se encontraron datos de usuario en el Directorio Activo o la respuesta fue inválida.";
                $this->sendJsonResponse($message, $resp, $token);
                return;
            }

            $user_data_da = $respuesta_da[0]; // Datos del usuario del Directorio Activo

            $nombre = $user_data_da["givenName"] ?? "";
            $pager = $user_data_da["pager"] ?? "";
            $rol = $user_data_da["description"] ?? ""; // Asumiendo que 'description' contiene el rol

            // Lógica para ADMINISTRATIVO
            if ($rol == "ADMINISTRATIVO") {
                $body = '{"funcion": "p_admin", "consulta": "consultausuario", "correo": "' . $correo . '"}';
                $fn = 'gestionAdmin';

                $respuesta_gestion_grados_admin = $classGestionGrados->requestGestorGrados($fn, $body);

                if ($respuesta_gestion_grados_admin) {
                    $decoded_admin_resp = json_decode($respuesta_gestion_grados_admin, true);
                    if (isset($decoded_admin_resp['body'])) {
                        $body_admin_data = json_decode($decoded_admin_resp['body'], true);
                        if (isset($body_admin_data[0]['estado']) && $body_admin_data[0]['estado'] > 0) {
                            if (isset($body_admin_data[0]['id']) && $body_admin_data[0]['id'] > 0) {
                                // ÉXITO: Usuario ADMINISTRATIVO válido
                                $dataToken = array(
                                    'rol' => $rol,
                                    'nombre' => $nombre,
                                    'correo' => $correo,
                                    'id_rol' => $body_admin_data[0]['id_rol'],
                                    'desc_rol' => $body_admin_data[0]['desc_rol'],
                                    'id_sede' => $body_admin_data[0]['id_Sede'],
                                    'id_rectoria' => $body_admin_data[0]['id_rectoria'],
                                    'id_admin' => $body_admin_data[0]['id'],
                                    'pager' => $pager,
                                    'access' => true
                                );

                                $resp = true;
                                $message = "El usuario cuenta con permisos de acceso";
                                $token = $classAccessToken->generarToken($correo, $dataToken);

                                $this->sendJsonResponse($message, $resp, $token);
                                return; // Termina la ejecución aquí
                            }
                        }
                        // Si estado no es > 0 o id no es > 0, mensaje de que no tiene permisos
                        $message = "El usuario administrativo no tiene un estado activo o un ID válido en el gestor de grados.";
                    } else {
                        $message = "Respuesta inválida del gestor de grados para administrativo.";
                    }
                } else {
                    $message = "Error al consultar la API del gestor de grados para administrativo.";
                }

            }
            // Lógica para ESTUDIANTE
            else if ($rol == "ESTUDIANTE") {
                // Variables específicas para estudiante
                $cod_programa = "";
                $programa = "";
                $cod_nivel_academico = "";
                $idBanner = $user_data_da["company"] ?? "";
                $apellido = $user_data_da["sn"] ?? "";
                $id_postulado = "";
                $id_postulado_nivel = "";
                $id_nivel = "";
                $nombre_nivel = "";
                $aPostuladoNivel = "";
                $snies = "";
                $sede = "";
                $id_sede = "";
                $id_rectoria = "";


                if ($idBanner) {
                    $respuestaEstado = $classGestionGrados->requestBannerEstudiantePostulado($idBanner);

                    // Verifica si la respuesta es un objeto y tiene la propiedad esperada
                    if (isset($respuestaEstado->respuesta->SHRDGMR_DEGS_CODE)) {
                        $estadoPostulado = $respuestaEstado->respuesta->SHRDGMR_DEGS_CODE;

                        if ($estadoPostulado == "TM" || $estadoPostulado == "CG" || $estadoPostulado == "FD") {
                            $cod_programa = $respuestaEstado->respuesta->SHRDGMR_DEGC_CODE ?? '';
                            $programa = $respuestaEstado->respuesta->STVMAJR_DESC ?? '';
                            $cod_nivel_academico_raw = $respuestaEstado->respuesta->SHRDGMR_LEVL_CODE ?? '';

                            switch ($cod_nivel_academico_raw) {
                                case "TP":
                                    $cod_nivel_academico = "1";
                                    break;
                                case "TC":
                                    $cod_nivel_academico = "2";
                                    break;
                                case "UG":
                                    $cod_nivel_academico = "3";
                                    break;
                                case "ES":
                                    $cod_nivel_academico = "4";
                                    break;
                                case "MS":
                                    $cod_nivel_academico = "4";
                                    break;
                                case "TL":
                                    $cod_nivel_academico = "5";
                                    break;
                                default:
                                    $cod_nivel_academico = "";
                                    break;
                            }

                            $fn = "gestionarPostulado";
                            $data_postulado = '{
                                "funcion": "p_postulado",
                                "pager": "' . $pager . '",
                                "idBanner": "' . $idBanner . '",
                                "nombre": "' . $nombre . '",
                                "apellido": "' . $apellido . '",
                                "correo": "' . $correo . '"
                            }';

                            $respuesta_postulado = $classGestionGrados->requestGestorGrados($fn, $data_postulado);
                            $respuestaDecode_postulado = json_decode($respuesta_postulado, true);

                            if (isset($respuestaDecode_postulado['body'])) {
                                $body_data_postulado = json_decode($respuestaDecode_postulado['body'], true);

                                if (isset($body_data_postulado[0]['id'])) {
                                    $id_postulado = $body_data_postulado[0]['id'];

                                    $data_postulado_nivel = '{
                                        "funcion": "p_postulado_nivel",
                                        "id": "0",
                                        "postulado": "' . $id_postulado . '",
                                        "rectoria": "0",
                                        "sede": "0",
                                        "nivel": "0",
                                        "estado": "0",
                                        "snies": "0",
                                        "codigoPrograma": "' . $cod_programa . '",
                                        "consulta": "consultaPostuladoNivelPrograma"
                                    }';
                                    $fn_postulado_nivel = "gestionPostuladoNivel";

                                    $respuesta_postulado_nivel = $classGestionGrados->requestGestorGrados($fn_postulado_nivel, $data_postulado_nivel);
                                    $respuestaDecode_postulado_nivel = json_decode($respuesta_postulado_nivel, true);

                                    if (isset($respuestaDecode_postulado_nivel['body'])) {
                                        $body_data_postulado_nivel = json_decode($respuestaDecode_postulado_nivel['body'], true);
                                        if (isset($body_data_postulado_nivel[0]["id_nivel"])) {
                                            $id_nivel = $body_data_postulado_nivel[0]["id_nivel"];
                                            $nombre_nivel = $body_data_postulado_nivel[0]["nombre"];
                                            $id_postulado_nivel = $body_data_postulado_nivel[0]["postulado_nivel"];
                                            $aPostuladoNivel = $body_data_postulado_nivel;
                                        } else {
                                            // Si no se encuentra postulado_nivel, consulta datos académicos y snies para generar token
                                            $respuestaDatosAcademicos = $classGestionGrados->requestDatosAcademicos($idBanner); // Usar $idBanner aquí
                                            $sede = $respuestaDatosAcademicos->sedeId ?? '';

                                            $fn_sede = "gestionSede";
                                            $bodySede = '{ "funcion": "p_sede", "sede": "' . $sede . '", "consulta": "sede" }';
                                            $responseSede = $classGestionGrados->requestGestorGrados($fn_sede, $bodySede);
                                            $responseSedeDec = json_decode($responseSede, true);

                                            if (isset($responseSedeDec['body'])) {
                                                $bodySedeData = json_decode($responseSedeDec['body'], true);
                                                $id_sede = $bodySedeData[0]["id"] ?? '';
                                                $id_rectoria = $bodySedeData[0]["id_rectoria"] ?? '';
                                            }

                                            $respuestaDatosPrograma = $classGestionGrados->requestDatosSnies($cod_programa, $sede);
                                            $snies = $respuestaDatosPrograma->resultado->snies ?? '';
                                        }

                                        // ÉXITO: Usuario ESTUDIANTE válido
                                        $dataToken = array(
                                            'rol' => $rol,
                                            'nombre' => $nombre,
                                            'correo' => $correo,
                                            'company' => $idBanner,
                                            'id_sede' => $id_sede,
                                            'id_rectoria' => $id_rectoria,
                                            'access' => true,
                                            'id_postulado' => $id_postulado,
                                            'id_postulado_nivel' => $id_postulado_nivel,
                                            'id_nivel' => $id_nivel,
                                            'nombre_nivel' => $nombre_nivel,
                                            'aPostuladoNivel' => $aPostuladoNivel,
                                            'programa' => $programa,
                                            'cod_programa' => $cod_programa,
                                            'pager' => $pager,
                                            'cod_nivel_academico' => $cod_nivel_academico,
                                            'snies' => $snies
                                        );

                                        $resp = true;
                                        $message = "El usuario cuenta con permisos de acceso";
                                        $token = $classAccessToken->generarToken($correo, $dataToken);

                                        $this->sendJsonResponse($message, $resp, $token);
                                        return; // Termina la ejecución aquí
                                    } else {
                                        $message = "Ocurrió un error al consultar postulado nivel.";
                                    }
                                } else {
                                    $message = "Ocurrió un error al consultar postulado (ID no encontrado).";
                                }
                            } else {
                                $message = "Ocurrió un error al consultar postulado (respuesta inválida).";
                            }
                        } else {
                            // Si el estado del postulado no es TM, CG, FD
                            $message = "Aún no se encuentra postulado a grado.\n";
                            $message .= "Recuerde que la postulación es una acción que usted debe realizar a través ";
                            $message .= "de la plataforma Génesis, únicamente dentro de las fechas establecidas por la institución.";
                            $message .= "El cargue de documentos es exclusivo de los postulados y por ende no exonera la postulación a grados";
                        }
                    } else {
                        $message = "Respuesta inválida al consultar el estado de postulación del estudiante.";
                    }
                } else {
                    $message = "El usuario ESTUDIANTE no tiene un ID Banner válido.";
                }
            } else {
                // Rol no es ADMINISTRATIVO ni ESTUDIANTE
                $message = "El usuario no tiene un rol reconocido o permisos de acceso.";
            }

            // Si se llega aquí, significa que alguna de las condiciones no se cumplió
            // y las variables $message, $resp, $token retienen los valores asignados en los 'else' o los valores por defecto.
            $this->sendJsonResponse($message, $resp, $token); // Envía la respuesta final
            return;

        } else {
            // Si no se proporcionó 'access' en los datos POST
            $message = "Faltan datos requeridos (access token).";
            $resp = false; // Ya es false por defecto
            $this->sendJsonResponse($message, $resp, $token);
            return;
        }
    }

    /**
     * Helper function to send JSON response and exit
     */
    private function sendJsonResponse($message, $resp, $token = "")
    {
        header('Content-Type: application/json');
        echo json_encode([
            'message' => $message,
            'resp' => $resp,
            'token' => $token
        ]);
        exit;
    }
}