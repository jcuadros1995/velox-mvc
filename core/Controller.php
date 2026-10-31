
<?php
// core/Controller.php

abstract class Controller
{
    /**
     * Renderizar una vista.
     */
    protected function view(
        string $viewPath,
        array $data = []
    ): void {
        extract($data);

        $file = __DIR__ . "/../app/Views/{$viewPath}.php";

        if (file_exists($file)) {
            require_once $file;
        } else {
            http_response_code(500);
            die("La vista {$viewPath} no existe.");
        }
    }

    /**
     * Responder con JSON.
     */
    protected function json(
        array $data,
        int $statusCode = 200
    ): void {
        http_response_code($statusCode);

        header(
            'Content-Type: application/json; charset=utf-8'
        );

        echo json_encode(
            $data,
            JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES
        );

        exit;
    }

    /**
     * Exigir autenticación.
     */
    protected function requireAuth(): void
    {
        Middleware::webAuth();
    }

    /**
     * Redirigir a una ruta de la aplicación.
     *
     * Ejemplo:
     * $this->redirect('/admin/users');
     *
     * APP_URL incluye automáticamente el prefijo
     * de instalación, por ejemplo /burgertown.
     */
    protected function redirect(
        string $path,
        int $statusCode = 302
    ): void {
        // Evitar redirecciones externas o URLs protocol-relative.
        $path = trim($path);

        if (
            $path === '' ||
            preg_match('/[\r\n]/', $path) ||
            substr($path, 0, 2) === '//' ||
            preg_match('/^[a-z][a-z0-9+.-]*:/i', $path)
        ) {
            $path = '/admin/dashboard';
        }

        // Solo aceptar rutas internas de la aplicación.
        if ($path[0] !== '/') {
            $path = '/' . $path;
        }

        $baseUrl = defined('APP_URL')
            ? rtrim(APP_URL, '/')
            : '';

        $url = $baseUrl . $path;

        if (!in_array($statusCode, [301, 302, 303, 307, 308], true)) {
            $statusCode = 302;
        }

        header('Location: ' . $url, true, $statusCode);
        exit;
    }
}
