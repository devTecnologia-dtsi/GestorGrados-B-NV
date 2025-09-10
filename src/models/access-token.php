<?php
require_once __DIR__ . '/../../vendor/autoload.php';

use Firebase\JWT\JWT;
use Firebase\JWT\Key;
use Dotenv\Dotenv; // Agrega esta línea

// Cargar las variables de entorno
$dotenv = Dotenv::createImmutable(__DIR__ . '/../../'); // Ajusta la ruta a donde se encuentra tu archivo .env
$dotenv->load();

class AccessToken
{
    function validateToken($jwt)
    {
        try {
            // Ahora JWTKEY debería estar disponible a través de $_ENV
            $JWTKEY = $_ENV['JWTKEY']; // Ya no necesitas el '?? $_SERVER['JWTKEY']' si dotenv está funcionando
            $decoded = JWT::decode($jwt, new Key($JWTKEY, 'HS256'));
            return (array) $decoded->data;
        } catch (Exception $e) {
            // Puedes agregar un log aquí para depuración
            error_log("Error al validar token JWT: " . $e->getMessage());
            return null;
        }
    }

    function decryptData($value)
    {
        $JWTKEY = $_ENV['JWTKEY'];
        $key = hex2bin(hash('sha256', $JWTKEY));
        $encrypt_method = "aes-256-ecb";
        $options = OPENSSL_RAW_DATA | OPENSSL_ZERO_PADDING;
        $decodedValue = base64_decode($value);
        $decrypttext = openssl_decrypt(
            $decodedValue,
            $encrypt_method,
            $key,
            $options
        );

        if ($decrypttext === false) {
            $error = openssl_error_string();
            error_log("Error de OpenSSL: " . $error); // Usar error_log para depuración
            return false;
        }

        $unpaddedText = rtrim($decrypttext, "\0");
        return $unpaddedText;
    }

    function generarToken($correo, $dataToken)
    {
        $timestampMedianoche = strtotime('today midnight');
        $expiracion = $timestampMedianoche + (24 * 60 * 60) - 1;
        $JWTKEY = $_ENV['JWTKEY'];

        $payload = array(
            "iss" => "api_gestor_grados",
            "aud" => "gestor_grados",
            "iat" => time(),
            "nbf" => time(),
            "exp" => $expiracion,
            "data" => [
                "data" => $dataToken
            ]
        );

        $jwt = JWT::encode($payload, $JWTKEY, 'HS256');

        return $jwt;
    }
}