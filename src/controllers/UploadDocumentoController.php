<?php
require_once __DIR__ . '/../models/request-gestion-grados.php';
require_once __DIR__ . '/../models/access-token.php';

/**
 * Clase para manejar la carga de documentos.
 *
 * Esta clase proporciona un método para gestionar la subida de documentos
 * en formato PDF, asegurando la validación del token del usuario y
 * el almacenamiento adecuado del archivo en el servidor.
 */
class UploadDocumentoController
{
    /**
     * Maneja la subida de un documento.
     *
     * Este método procesa la solicitud de subida de un documento, valida el token del usuario,
     * verifica el tipo de archivo, y guarda el documento en el servidor.
     * 
     * @return void
     */
    public function uploadDocumento()
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
                $fileName = $dataDec['documento'];
                $fileNameCmps = explode(".", $fileName);
                $fileExtension = strtolower(end($fileNameCmps));
                $newFileName = md5(time() . $fileName) . '.' . $fileExtension;

                if ($fileExtension == "pdf" || $fileExtension == "PDF") {
                    $idNivel = trim($dataDec["idNivel"], '/');
                    $pager = trim($dataDec["pager"], '/');
                    
                    $uploadFileDir = $_SERVER['DOCUMENT_ROOT'] . '/' . 'ndocs/' . $idNivel . '/' . $pager;

                    $uploadFileDirError = false;

                    if (!file_exists($uploadFileDir)) {
                        if (mkdir($uploadFileDir, 0777, true)) {
                            $uploadFileDirError = false;
                        } else {
                            $uploadFileDirError = true;
                        }
                    }
                    else {
                        $uploadFileDirError = false;
                    }

                    if (!$uploadFileDirError) {
                        if (isset($dataDec['archivo'])) {
                            $fileData = $dataDec['archivo'];
                            $fileData = substr($fileData, strpos($fileData, ",") + 1);

                            $fileDataDecoded = base64_decode($fileData);
                            $uploadDir = $uploadFileDir;
                            $filePath = $uploadFileDir . '/' . $newFileName;
                        
                            if (file_put_contents($filePath, $fileDataDecoded) !== false) {
                                $dataDec['documento'] = $newFileName;
                                unset($dataDec['token']);
                                unset($dataDec['archivo']);
            
                                $data = json_encode($dataDec, true);
                                $respuesta = $classGestionGrados->requestGestorGrados($fn, $data);
                                
                                echo $respuesta;
                            } else {
                                echo json_encode(array('success' => false, 'message' => 'Error al guardar el archivo.'));
                            }
                        }
                    }
                    else {
                        http_response_code(500);
                        echo json_encode(array('success' => false, 'message' => 'Error al guardar el archivo.'));
                    }
                } else {
                    http_response_code(500);
                    echo json_encode(array('success' => false, 'message' => 'Error al guardar el archivo.'));
                }
            }
        }
        else {
            http_response_code(401);
        }
    } 
}
