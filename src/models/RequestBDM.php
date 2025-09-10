<?php

class RequestBDM
{
    private $baseUrl = "https://apibdmsquas.uniminuto.edu/DocumentManagementApi/qapi/bdm-documents";

    // Modifica la firma del método para aceptar $dmType
    public function queryDocuments($id, $admissionsRequirement, $dmType) 
    {
        // Obtener credenciales de BDM desde las variables de entorno
        $bdmUsername = $_ENV['BDM_USERNAME'] ?? null;
        $bdmPassword = $_ENV['BDM_PASSWORD'] ?? null;

        if (empty($bdmUsername) || empty($bdmPassword)) {
            return false;
        }

        // Construir el encabezado Basic Auth
        // Codifica el nombre de usuario y la contraseña en Base64, como lo requiere la API.
        $authString = base64_encode($bdmUsername . ':' . $bdmPassword);
        $authHeader = 'Authorization: Basic ' . $authString;

        $body = [
            "dmType" => $dmType, // AHORA USA EL VALOR PASADO POR PARÁMETRO
            "indexes" => [
                "ID" => $id,
                "ADMISSIONS REQUIREMENT" => $admissionsRequirement // USA EL VALOR PASADO POR PARÁMETRO
            ]
        ];

        $jsonBody = json_encode($body);

        $ch = curl_init($this->baseUrl);

        curl_setopt($ch, CURLOPT_CUSTOMREQUEST, "POST");
        curl_setopt($ch, CURLOPT_POSTFIELDS, $jsonBody);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_HTTPHEADER, array(
            'Content-Type: application/json',
            'Content-Length: ' . strlen($jsonBody),
            $authHeader
        ));

        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
        curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, false);

        $response = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        $curlError = curl_error($ch);


        if ($curlError) {
            return false;
        }

        curl_close($ch);

        if ($httpCode >= 200 && $httpCode < 300) {
            return json_decode($response, true);
        } else {
            return false;
        }
    }
}