<?php
switch ($error ?? '401') {
    case '401':
        header("HTTP 401 - Unauthorized");
        $mensaje = 'No está autorizado para ver este recurso.';
        $redirect = 'https://uwallet.uniminuto.edu';
        $boton = "Ir a Uwallet";
        break;
    case '406':
        header("HTTP 406 - Not acceptable");
        $mensaje = 'En la imagen debes aparecer solo y procurar no tener muchos objetos alrededor.';
        require_once __DIR__ . '/../views/upload_form.php';
        break;
    case '403':
        header("HTTP 403 Forbidden");
        $mensaje = 'Hemos encontrado un problema para subir la imagen, por favor comunicate con soporte en el siguiente enlace.';
        $redirect = 'https://soporte.uniminuto.edu';
        $boton = "Ir a soporte Uniminuto";
        break;
    case '400':
        header("HTTP 400 Bad Request");
        $redirect = '"javascript:window.history.back();"';
        $boton = "Volver";
        break;

    default:
        header("HTTP 501 - Not implemented");
        $mensaje = 'Por favor regresa e intenta nuevamente.';
        $redirect = 'https://uwallet.uniminuto.edu';
        $boton = "Ir a Uwallet";
        break;
}
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Error</title>
    <link rel="stylesheet" href="/css/styles.css">
</head>

<body>
    <div class="container">
        <h1>Error</h1>
        <div class="error">
            <?php
            echo $mensaje;
            ?>
        </div>
        <a href=<?php echo $redirect ?>><?php echo $boton ?></a>
    </div>
</body>

</html>