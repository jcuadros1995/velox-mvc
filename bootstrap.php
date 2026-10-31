<?php

/**
 * Velox MVC - Bootstrap
 *
 * Punto central de inicialización de la aplicación.
 */

// ---------------------------------------------------------
// CONFIGURACIÓN
// ---------------------------------------------------------

require_once __DIR__ . '/config/config.php';
require_once __DIR__ . '/config/database.php';

date_default_timezone_set('America/Bogota');
// ---------------------------------------------------------
// AUTOLOAD
// ---------------------------------------------------------

spl_autoload_register(function (string $class): void {

    $directories = [
        __DIR__ . '/core/',
        __DIR__ . '/app/Controllers/',
        __DIR__ . '/app/Models/',
        __DIR__ . '/app/Services/',
        __DIR__ . '/app/Repositories/',
        __DIR__ . '/app/Helpers/',
        __DIR__ . '/services/Gateways/',
    ];

    foreach ($directories as $directory) {

        $file = $directory . $class . '.php';

        if (file_exists($file)) {
            require_once $file;
            return;
        }
    }
});


// ---------------------------------------------------------
// SESIÓN
// ---------------------------------------------------------

Auth::init();