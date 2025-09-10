document.getElementById('uploadForm').addEventListener('submit', function (event) {
    event.preventDefault(); // Evita el envío del formulario por defecto

    var form = event.target;
    var formData = new FormData(form);
    var xhr = new XMLHttpRequest();

    // Muestra la barra de progreso, el GIF de "procesando" y el overlay al enviar el formulario
    document.getElementById('progressContainer').style.display = 'block';
    document.getElementById('responseContainer').style.display = 'none'; // Oculta la respuesta anterior
    document.getElementById('loadingGifContainer').style.display = 'block'; // Muestra el contenedor del GIF
    document.getElementById('overlay').style.display = 'block'; // Muestra el overlay

    xhr.upload.addEventListener('progress', function (event) {
        if (event.lengthComputable) {
            var percentComplete = (event.loaded / event.total) * 100;
            document.getElementById('progressBar').value = percentComplete;
        }
    });

    xhr.addEventListener('load', function () {
        var responseContainer = document.getElementById('responseContainer');
        responseContainer.style.display = 'block';
        document.getElementById('loadingGifContainer').style.display = 'none'; // Oculta el contenedor del GIF
        document.getElementById('overlay').style.display = 'none'; // Oculta el overlay
        var response = xhr.responseText;
        // console.log(response);


        try {
            var response = JSON.parse(xhr.responseText); // Intenta parsear la respuesta como JSON
            if (response.success) {
                responseContainer.innerText = response.message;
                responseContainer.classList.add('success');
                responseContainer.classList.remove('error');
            } else {
                responseContainer.innerText = response.message;
                responseContainer.classList.add('error');
                responseContainer.classList.remove('success');
            }
        } catch (e) {
            console.log(e);

            responseContainer.innerText = 'Error al procesar la respuesta.';
            responseContainer.classList.add('error');
            responseContainer.classList.remove('success');
        }

        document.getElementById('progressBar').value = 0; // Restablece la barra de progreso
        document.getElementById('progressContainer').style.display = 'none'; // Oculta la barra de progreso
    });

    xhr.addEventListener('error', function () {
        var responseContainer = document.getElementById('responseContainer');
        responseContainer.style.display = 'block';
        document.getElementById('loadingGifContainer').style.display = 'none'; // Oculta el contenedor del GIF
        document.getElementById('overlay').style.display = 'none'; // Oculta el overlay
        document.getElementById('progressContainer').style.display = 'none'; // Oculta la barra de progreso

        try {
            var response = JSON.parse(xhr.responseText); // Intenta parsear la respuesta como JSON
            responseContainer.innerText = response.message || 'Error al subir la imagen.';
            responseContainer.classList.add('error');
            responseContainer.classList.remove('success');
        } catch (e) {
            responseContainer.innerText = 'Error al subir la imagen.';
            responseContainer.classList.add('error');
            responseContainer.classList.remove('success');
        }
    });

    xhr.addEventListener('abort', function () {
        var responseFail = JSON.parse(response);
        var responseContainer = document.getElementById('responseContainer');
        responseContainer.style.display = 'block';
        responseContainer.innerText = 'Subida cancelada.';
        responseContainer.classList.add('error');
        responseContainer.classList.remove('success');
        document.getElementById('loadingGifContainer').style.display = 'none'; // Oculta el contenedor del GIF
        document.getElementById('overlay').style.display = 'none'; // Oculta el overlay
        document.getElementById('progressContainer').style.display = 'none'; // Oculta la barra de progreso
    });

    xhr.open('POST', form.action, true);
    xhr.send(formData);
});
