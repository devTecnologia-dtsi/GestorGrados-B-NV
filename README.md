# API para gestión de grados

Este proyecto es una API de gestión de requisitos de grado desarrollada en PHP. 

Para la autorización de solicitudes http a usuarios se realiza la generación de tokens mediante la libreria de JWT. El token se genera con una validez de 1 día y contiene información relevante del usuario como el rol, sede, rectoría y nombres.

Permite a los usuarios subir archivos en formato PDF, almacenarlos en la carpeta ndocs, ademas de la gestión de estados en espera, validado y rechazado de los postulados a grado, 

En el proyecto se realiza el request a la API https://api.masiv.masivian.com/email/v1/delivery donde se ejecuta el envio de email a los postulados.


## Requisitos

- PHP 8 o superior
- Extensión CURL
- Servidor web Apache
- Composer (opcional para desarrollo local)
- Docker (opcional para entorno de desarrollo aislado)

## Estructura del Proyecto
.
├── public
│ ├── css
│ ├── resources
│ ├── js
│ └── index.php
├── config
├── src
│ ├── controllers
│ ├── models
│ └── routes
├── Dockerfile
└── README.md

## VHOST en Apache

Como se puede obsevar en el Dockerfile, todas las peticiones entrantes deben apuntar a public/index.php para seguridad y uso de rutas.

## API REST

El proyecto se desarrollo con buenas prácticas para el desarrollo de API REST teniendo en cuenta:
1. Métodos en plural.
2. Uso de Path params para llaves primarias.
3. Uso de Query params para otras consultas a la base de datos.
4. Respuestas estandarizadas.

## Modelo

## NFS
Para la alta disponibilidad del servicio se utilizó como servidor NFS LODS.

## Docker

docker build -t api_gestor_grados .
docker run --name api_gestor_grados -dp 8081:80 -d --env-file .env api_gestor_grados

## TFS

Clonar el repositorio:

git clone http://10.0.36.206:8080/tfs/DefaultCollection/PHP/_git/gestorGrados_NV_Back
cd api_gestor_grados

