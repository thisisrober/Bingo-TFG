# Bingo! Social Media App

![Logo de Bingo!](https://i.imgur.com/hyhZ9N0.png)

Bingo! Social Media App es una plataforma de red social unificada que centraliza todas las interacciones esenciales en una única interfaz intuitiva. Olvídate de cambiar entre múltiples aplicaciones: aquí podrás crear hilos de publicaciones, enviar mensajes directos, gestionar amigos y comentar, todo de forma segura y responsiva.

## Características Destacadas

- **Interfaz Responsiva:**  
  Desarrollada con HTML5, CSS3, Bootstrap 5.3.0-alpha1 y Tailwind CSS, garantizando una experiencia óptima tanto en dispositivos móviles como en escritorio.
- **Mensajería en Tiempo Real:**  
  Utiliza AJAX y Fetch API para actualizaciones dinámicas sin necesidad de recargar la página.
- **Seguridad y Robustez:**  
  Implementada en PHP (Vanilla) y MySQL/MariaDB mediante PDO, protegiendo contra inyecciones SQL y asegurando la integridad de los datos.
- **Edición de Imágenes:**  
  Integración de Cropper.js para recortar y optimizar imágenes antes de subirlas.
- **Despliegue en Raspberry Pi 4:**  
  Utiliza un entorno de servidor eficiente con Ubuntu 24.04, incorporando un panel de control que muestra el estado de servicios esenciales (Apache, MySQL, SSH) y datos de la red.
- **Potencial de Expansión:**  
  Futuras mejoras incluyen autenticación con Google OAuth, desarrollo de una aplicación móvil nativa (APK) y la incorporación de un chatbot con inteligencia artificial.

![Vista previa](https://i.imgur.com/ZteLtGA.png)

## Instalación y Configuración

### Requisitos
- **Hardware:** Raspberry Pi 4 con Ubuntu 24.04.
- **Software:** Apache, PHP, MySQL/MariaDB, y dependencias de Node.js (si es necesario).
- **Conexión a Internet** para descargar dependencias y actualizaciones.

### Pasos de Instalación

1. **Clona el Repositorio:**
   ```bash
   git clone https://github.com/tuusuario/bingo-social-media-app.git
   cd bingo-social-media-app
   ```
2. **Instala Dependencias en el Servidor:**
```bash
   sudo apt update
   sudo apt install apache2 php libapache2-mod-php mariadb-server -y
```
3. **Configura la Base de Datos:**
  Importa el esquema DDL incluido en el repositorio para crear las tablas necesarias.
4. **Inicia los Servicios:**
```bash
   sudo systemctl start apache2
   sudo systemctl start mysql
```
5. **Accede a la Aplicación:**
  Abre tu navegador y visita http://127.0.0.1 para interactuar con la plataforma.

---

![Login](https://i.imgur.com/l1tQ5K4.png)
