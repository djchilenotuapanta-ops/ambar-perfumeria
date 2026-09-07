# Ambar Perfumería - Sistema de Gestión

Sistema web desarrollado en Laravel para la gestión de una perfumería, incluyendo catálogo de productos, inventario, procesamiento de pedidos y registro de pagos.

## 🛠️ Tecnologías utilizadas

- **Backend:** PHP / Laravel
- **Base de datos:** MySQL
- **Frontend:** Blade, HTML, CSS, JavaScript
- **Gestor de dependencias:** Composer / NPM

## 📋 Requisitos previos

Antes de instalar el proyecto, asegúrate de tener instalado en tu computadora:

- PHP (versión 8.1 o superior recomendado)
- Composer
- MySQL o un gestor como XAMPP/Laragon
- Node.js y NPM (para compilar los assets del frontend)
- Git

## 🚀 Instalación paso a paso

### 1. Clonar el repositorio

git clone https://github.com/tu-usuario/nombre-del-repo.git
cd nombre-del-repo

### 2. Instalar las dependencias de PHP

composer install

### 3. Instalar las dependencias de JavaScript

npm install

### 4. Configurar el archivo de entorno

cp .env.example .env

> Nota: El archivo .env real (con contraseñas y datos sensibles) no está incluido en el repositorio por seguridad. Debes completarlo tú mismo con tus propios datos.

### 5. Generar la clave de la aplicación

php artisan key:generate

### 6. Configurar la base de datos

DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=nombre_de_tu_base_de_datos
DB_USERNAME=root
DB_PASSWORD=tu_contraseña

Luego, crea la base de datos vacía en MySQL:

CREATE DATABASE nombre_de_tu_base_de_datos;

### 7. Ejecutar las migraciones

php artisan migrate

Si el proyecto incluye datos de prueba (seeders):

php artisan migrate --seed

### 8. Compilar los assets (CSS/JS)

npm run dev

o para producción:

npm run build

### 9. Iniciar el servidor local

php artisan serve

El proyecto quedará disponible en: http://127.0.0.1:8000

## Estructura del proyecto

- app/ — Lógica principal (controladores, modelos)
- public/ — Punto de entrada, assets compilados
- resources/ — Vistas Blade, CSS, JS sin compilar
- routes/ — Definición de rutas web y API
- database/ — Migraciones y seeders
- storage/ — Archivos generados, logs, cache

## Notas de seguridad

- Nunca subas tu archivo .env real a un repositorio público.
- Los datos de ejemplo en .env.example deben ser genéricos, sin credenciales reales.

## Autora

**Daisy Chileno**
Estudiante de Desarrollo de Software
LinkedIn: https://www.linkedin.com/in/daisy-chileno-tuapanta-20a508232/
