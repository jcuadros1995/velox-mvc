<?php
// core/Router.php

class Router
{
    private array $routes = [];

    public function get(
        string $path,
        string $handler,
        array $middlewares = []
    ): void {
        $this->addRoute(
            'GET',
            $path,
            $handler,
            $middlewares
        );
    }

    public function post(
        string $path,
        string $handler,
        array $middlewares = []
    ): void {
        $this->addRoute(
            'POST',
            $path,
            $handler,
            $middlewares
        );
    }

    private function addRoute(
        string $method,
        string $path,
        string $handler,
        array $middlewares
    ): void {

        $this->routes[] = [
            'method' => $method,

            'path' =>
                '/' .
                trim(
                    $path,
                    '/'
                ),

            'handler' =>
                $handler,

            'middlewares' =>
                $middlewares
        ];
    }

    /**
     * Obtiene la ruta real solicitada.
     *
     * Preferimos REQUEST_URI porque funciona correctamente
     * cuando Apache utiliza mod_rewrite.
     *
     * También mantenemos compatibilidad con ?url=...
     */
    private function resolveUrl(): string
    {
        /*
         * =====================================================
         * 1. URI REAL DEL NAVEGADOR
         * =====================================================
         */

        $requestPath =
            parse_url(
                $_SERVER['REQUEST_URI'] ?? '/',
                PHP_URL_PATH
            );

        if (
            !is_string($requestPath) ||
            $requestPath === ''
        ) {
            $requestPath = '/';
        }


        /*
         * =====================================================
         * 2. RUTA BASE DE LA APLICACIÓN
         * =====================================================
         *
         * Ejemplo:
         *
         * APP_URL:
         * http://localhost/burgertown
         *
         * base:
         * /burgertown
         */

        $appPath =
            parse_url(
                APP_URL,
                PHP_URL_PATH
            );

        if (
            !is_string($appPath) ||
            $appPath === '/'
        ) {
            $appPath = '';
        } else {
            $appPath =
                '/' .
                trim(
                    $appPath,
                    '/'
                );
        }


        /*
         * =====================================================
         * 3. QUITAR APP_URL
         * =====================================================
         */

        if (
            $appPath !== '' &&
            (
                $requestPath === $appPath ||
                str_starts_with(
                    $requestPath,
                    $appPath . '/'
                )
            )
        ) {

            $requestPath =
                substr(
                    $requestPath,
                    strlen($appPath)
                );
        }


        /*
         * =====================================================
         * 4. QUITAR /public SI APARECE
         * =====================================================
         */

        if (
            $requestPath === '/public'
            ||
            str_starts_with(
                $requestPath,
                '/public/'
            )
        ) {

            $requestPath =
                substr(
                    $requestPath,
                    strlen('/public')
                );
        }


        /*
         * =====================================================
         * 5. NORMALIZAR
         * =====================================================
         */

        $requestPath =
            '/' .
            trim(
                $requestPath,
                '/'
            );


        /*
         * =====================================================
         * 6. Si Apache nos entrega index.php,
         *    intentar ?url=
         * =====================================================
         */

        if (
            $requestPath === '/index.php' ||
            $requestPath === '/'
            && isset($_GET['url'])
        ) {

            if (
                isset($_GET['url']) &&
                is_string($_GET['url']) &&
                trim($_GET['url']) !== ''
            ) {

                $queryUrl =
                    '/' .
                    trim(
                        $_GET['url'],
                        '/'
                    );

                return $queryUrl;
            }

            return '/';
        }


        return $requestPath;
    }

    /**
     * Compara una ruta registrada con la URL solicitada
     * y devuelve los parámetros dinámicos encontrados.
     *
     * Ejemplo:
     *
     * Ruta:
     * /menu/{slug}
     *
     * URL:
     * /menu/hamburguesas-bogota
     *
     * Resultado:
     *
     * [
     *     'hamburguesas-bogota'
     * ]
     */
    private function matchRoute(
        string $routePath,
        string $requestPath
    ): ?array {

        /*
         * =====================================================
         * RUTA EXACTA
         * =====================================================
         */

        if (
            $routePath ===
            $requestPath
        ) {

            return [];
        }


        /*
         * =====================================================
         * DETECTAR PARÁMETROS DINÁMICOS
         * =====================================================
         *
         * Ejemplo:
         *
         * /menu/{slug}
         *
         * se convierte en:
         *
         * /menu/([^/]+)
         */

        if (
            !str_contains(
                $routePath,
                '{'
            )
        ) {

            return null;
        }


        /*
         * =====================================================
         * ESCAPAR LA RUTA
         * =====================================================
         */

        $pattern =
            preg_quote(
                $routePath,
                '#'
            );


        /*
         * =====================================================
         * REEMPLAZAR {parametro}
         * =====================================================
         */

        $pattern =
            preg_replace(
                '/\\\\\{[a-zA-Z_][a-zA-Z0-9_]*\\\\\}/',
                '([^/]+)',
                $pattern
            );


        if (
            !is_string($pattern)
        ) {

            return null;
        }


        /*
         * =====================================================
         * COMPARAR
         * =====================================================
         */

        $pattern =
            '#^' .
            $pattern .
            '$#';


        $matches = [];


        if (
            preg_match(
                $pattern,
                $requestPath,
                $matches
            ) !== 1
        ) {

            return null;
        }


        /*
         * =====================================================
         * ELIMINAR EL MATCH COMPLETO
         * =====================================================
         */

        array_shift(
            $matches
        );


        return $matches;
    }

    /**
     * Ejecuta la ruta correspondiente.
     */
    public function dispatch(): void
    {
        $method =
            strtoupper(
                $_SERVER['REQUEST_METHOD'] ?? 'GET'
            );

        $url =
            $this->resolveUrl();


        /*
         * =====================================================
         * DEBUG TEMPORAL
         * =====================================================
         *
         * No lo dejamos activo.
         */


        foreach (
            $this->routes
            as $route
        ) {

            if (
                $route['method'] !==
                $method
            ) {
                continue;
            }

            $params =
                $this->matchRoute(
                    $route['path'],
                    $url
                );

            if (
                $params === null
            ) {
                continue;
            }


            /*
             * =================================================
             * MIDDLEWARES
             * =================================================
             */

            foreach (
                $route['middlewares']
                as $middleware
            ) {

                if (is_array($middleware)) {

                    [
                        $class,
                        $mMethod,
                        $params
                    ] =
                        $middleware;


                    if (
                        !$class::$mMethod(
                            $params
                        )
                    ) {
                        return;
                    }

                } else {

                    if (
                        !$middleware()
                    ) {
                        return;
                    }
                }
            }


            /*
             * =================================================
             * CONTROLLER
             * =================================================
             */

            [
                $controllerName,
                $action
            ] =
                explode(
                    '@',
                    $route['handler']
                );


            // Auditoría centralizada: registra páginas del panel y cualquier
            // endpoint protegido por roles/permisos administrativos.
            // Se ejecuta después de que todos los middlewares hayan autorizado.
            if ($this->isAdminActivity($url, $route)) {
                AuditLogger::record($method, $url, $route['handler']);
            }

            $controller =
                new $controllerName();


            if (
                method_exists(
                    $controller,
                    $action
                )
            ) {

                $controller->$action(
                    ...$params
                );

                return;
            }


            /*
             * Si la acción no existe,
             * devolver 404.
             */
            http_response_code(404);

            echo json_encode([
                'error' =>
                    '404 - Acción no encontrada'
            ]);

            return;
        }


        /*
         * =====================================================
         * 404
         * =====================================================
         */

        http_response_code(404);

        header(
            'Content-Type: application/json; charset=utf-8'
        );

        echo json_encode([
            'success' => false,
            'error' =>
                '404 - Ruta no encontrada',
            'path' =>
                $url
        ]);
    }

    /**
     * Determina si una solicitud pertenece al panel administrativo.
     *
     * Además de las rutas /admin y /api/admin, reconoce cualquier API
     * protegida por roles administrativos o permisos de módulos del panel.
     * Así, las nuevas acciones AJAX quedan auditadas automáticamente sin
     * tener que mantener una lista manual de endpoints.
     */
    private function isAdminActivity(string $url, array $route): bool
    {
        if (
            $url === '/admin' ||
            str_starts_with($url, '/admin/') ||
            str_starts_with($url, '/api/admin/')
        ) {
            return true;
        }

        foreach (($route['middlewares'] ?? []) as $middleware) {
            if (!is_array($middleware) || count($middleware) < 3) {
                continue;
            }

            [$class, $method, $allowed] = $middleware;
            $method = strtolower((string)$method);
            $allowed = is_array($allowed) ? $allowed : [];

            if (
                in_array($method, ['role', 'webrole'], true) &&
                array_intersect(
                    array_map('strtolower', $allowed),
                    ['superadmin', 'admin', 'staff']
                )
            ) {
                return true;
            }

            if (in_array($method, ['anypermission', 'webanypermission'], true)) {
                return true;
            }
        }

        return false;
    }

    /**
     * Renderiza una respuesta 404.
     */
    private function renderNotFound(string $url): void
    {
        http_response_code(404);


        /*
         * =====================================================
         * API
         * =====================================================
         *
         * Las APIs siempre reciben JSON.
         */

        if (
            str_starts_with(
                $url,
                '/api/'
            )
        ) {

            header(
                'Content-Type: application/json; charset=utf-8'
            );


            echo json_encode([
                'success' => false,
                'error' =>
                    '404 - Ruta no encontrada',
                'path' =>
                    $url
            ]);


            return;
        }


        /*
         * =====================================================
         * ADMIN
         * =====================================================
         */

        $isAdmin =
            $url === '/admin' ||
            str_starts_with(
                $url,
                '/admin/'
            );


        $isAdminError =
            $isAdmin;


        $contentView =
            __DIR__ .
            '/../app/Views/errors/404.php';


        /*
         * =====================================================
         * LAYOUT
         * =====================================================
         */

        if ($isAdmin) {

            $layout =
                __DIR__ .
                '/../app/Views/admin/layout.php';

            $title =
                'Página no encontrada | Burger Town';

        } else {

            $layout =
                __DIR__ .
                '/../app/Views/layouts/public_layout.php';

            $title =
                'Página no encontrada | Burger Town';
        }


        /*
         * =====================================================
         * RENDER
         * =====================================================
         */

        if (
            file_exists($layout) &&
            file_exists($contentView)
        ) {

            require $layout;

            return;
        }


        /*
         * =====================================================
         * FALLBACK
         * =====================================================
         */

        header(
            'Content-Type: text/html; charset=utf-8'
        );


        echo
            '<h1>404</h1>' .
            '<p>Página no encontrada.</p>';
    }
}