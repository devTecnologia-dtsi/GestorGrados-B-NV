<?php
class EnvioCorreo
{
    function envioCorreo($para, $mensaje, $subject)
    {
        $URLAPICORREO = getenv('URLAPICORREO');

        $curl = curl_init();
        curl_setopt_array(
            $curl,
            array(
                CURLOPT_URL => $URLAPICORREO,
                CURLOPT_RETURNTRANSFER => true,
                CURLOPT_ENCODING => '',
                CURLOPT_MAXREDIRS => 10,
                CURLOPT_TIMEOUT => 0,
                CURLOPT_FOLLOWLOCATION => true,
                CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
                CURLOPT_CUSTOMREQUEST => 'POST',
                CURLOPT_POSTFIELDS => '{
                "Subject": "' . $subject . '",
                "From": "notificacionesgenesis@uniminuto.edu",
                "Template": {
                "Type": "text/html",
                "Value": "' . $mensaje . '"
                },
                "Recipients": [
                {
                    "To": "' . $para . '"
                    }
                ]
            }',
                CURLOPT_HTTPHEADER => array(
                    'Authorization: Basic RW1haWxWNF9VTklNSU5VVE9fMTcwMzpNdjQudmxKVDhiZTE=',
                    'Content-Type: application/json'
                ),
            )
        );

        $response = curl_exec($curl);
        curl_close($curl);
        return $response;
    }
}