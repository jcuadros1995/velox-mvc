# Integración selectiva BurgerTown → Velox MVC

## Principio de integración
Velox MVC sigue siendo el proyecto base. Esta integración agrega módulos que no existían en Velox y conserva las páginas y estilos preexistentes.

## Protecciones aplicadas
- Se conservó byte por byte `app/Views/home/index.php` de Velox.
- Se conservó `HomeController.php` de Velox.
- Se conservaron las vistas y layouts que ya existían en Velox; las vistas de BurgerTown se copiaron únicamente cuando no existía un archivo con ese mismo nombre.
- Se conservaron los CSS, fuentes, imágenes de configuración y recursos propios de Velox; los CSS y JS nuevos se añadieron sin sobrescribir los existentes.
- Se conservó `config/database.php`, `database/seed.php` y la instalación de PHPMailer de Velox.
- La tabla de rutas se amplió con rutas de BurgerTown; las 57 rutas que ya existían en Velox se mantienen con el mismo método, path y handler.

## Módulos añadidos
- Menú público y páginas por categoría.
- Sedes públicas y páginas de detalle.
- Páginas públicas administrables.
- Contacto y solicitudes de eventos.
- Newsletter.
- Administración de categorías y páginas de categoría.
- Administración de productos y galerías.
- Administración de eventos.
- Formularios de recuperación/restablecimiento de contraseña.
- Imágenes y recursos multimedia asociados.
- Repositorios, servicios y controladores requeridos por los módulos.

## Estilos
Los estilos globales y páginas existentes de Velox no se reemplazan. Los nuevos CSS se agregan como hojas independientes y se cargan condicionalmente en las rutas nuevas. Se mantienen los tokens de tema `--velox-*` disponibles para que los módulos nuevos hereden la identidad de Velox.

## Configuración y pruebas necesarias
- Se conserva la configuración de conexión de Velox. Ajusta credenciales según el entorno.
- Se incluye la migración disponible de BurgerTown para páginas públicas/eventos, pero no se entregó un dump SQL completo. Deben verificarse y migrarse las tablas faltantes en una copia de la base.
- Configura SMTP y Wompi para el entorno de destino antes de probar correo y pagos.
- Ejecuta pruebas funcionales de login, permisos, categorías, productos, sedes, reservas, eventos, newsletter, pedidos e inventario.
- No ejecutar `database/seed.php` en producción.
- La integración no debe desplegarse sobre la instalación productiva sin backup y pruebas de aceptación.

## Nota técnica
Las vistas existentes de Velox y sus hojas globales se mantienen sin modificaciones. Se crearon `module_public_layout.php`, `module-layout.php` y `module-navbar.php` como copias aditivas basadas en la identidad visual de Velox. Las rutas nuevas usan estos layouts; los layouts y el navbar originales de Velox permanecen intactos.
