<!-- app/Views/admin/layout.php -->
<?php

$adminSiteName =
        SiteConfig::siteName();

$adminSiteDescription =
        SiteConfig::siteDescription();

$adminSiteFont =
        SiteConfig::font();

$adminPrimaryColor =
        SiteConfig::primaryColor();

$adminSecondaryColor =
        SiteConfig::secondaryColor();

$adminSiteLogo =
        SiteConfig::siteLogo();

$adminSiteFavicon =
        SiteConfig::siteFavicon();

$settingsAssetUrl =
        rtrim(APP_URL, '/') .
        '/assets/img/settings/';

$adminLogoUrl =
        $adminSiteLogo !== ''
                ? $settingsAssetUrl .
                rawurlencode($adminSiteLogo)
                : '';

$adminFaviconUrl =
        $adminSiteFavicon !== ''
                ? $settingsAssetUrl .
                rawurlencode($adminSiteFavicon)
                : '';

/*
|--------------------------------------------------------------------------
| Usuario actual
|--------------------------------------------------------------------------
*/

$adminUser =
        Auth::user() ?? [];

$adminRole =
        strtolower(
                (string) (
                        $adminUser['role_name'] ?? ''
                )
        );

$adminIsSuperAdmin =
        $adminRole === 'superadmin';

$adminIsAdmin =
        $adminRole === 'admin';

$adminIsStaff =
        $adminRole === 'staff';

$adminCanAccessSettings =
        $adminIsSuperAdmin ||
        $adminIsAdmin;

$adminScopeName =
        $adminIsSuperAdmin
                ? 'Todas las sedes'
                : (
                $adminUser['branch_name']
                ?? 'Sede no asignada'
        );


/*
|--------------------------------------------------------------------------
| RUTA ACTUAL DEL PANEL
|--------------------------------------------------------------------------
|
| Permite marcar automáticamente
| la opción activa del menú.
|
*/

$adminCurrentPath =
        parse_url(
                $_SERVER['REQUEST_URI'] ?? '/',
                PHP_URL_PATH
        ) ?: '/';

$appPath =
        parse_url(
                APP_URL,
                PHP_URL_PATH
        ) ?: '';

$appPath =
        '/' .
        trim(
                $appPath,
                '/'
        );


/*
|--------------------------------------------------------------------------
| Eliminar APP_URL de la ruta
|--------------------------------------------------------------------------
|
| Ejemplo:
|
| /velox-mvc/admin/users
|
| pasa a:
|
| /admin/users
|
*/

if (
        $appPath !== '/' &&
        str_starts_with(
                $adminCurrentPath,
                $appPath
        )
) {
    $adminCurrentPath =
            substr(
                    $adminCurrentPath,
                    strlen($appPath)
            );
}

$adminCurrentPath =
        '/' .
        trim(
                $adminCurrentPath,
                '/'
        );


/*
|--------------------------------------------------------------------------
| Helper para determinar menú activo
|--------------------------------------------------------------------------
*/

$adminMenuActive =
        function (
                string $route,
                bool $children = false
        ) use (
                $adminCurrentPath
        ): bool {

            $route =
                    '/' .
                    trim(
                            $route,
                            '/'
                    );

            /*
             * Para módulos con páginas hijas.
             *
             * Ejemplo:
             *
             * /admin/branches
             * /admin/branches/create
             * /admin/branches/edit
             *
             */

            if ($children) {

                return
                        $adminCurrentPath === $route
                        ||
                        str_starts_with(
                                $adminCurrentPath,
                                $route . '/'
                        );
            }

            return
                    $adminCurrentPath === $route;
        };


/*
|--------------------------------------------------------------------------
| Estado de cada módulo
|--------------------------------------------------------------------------
*/

$dashboardActive =
        $adminMenuActive(
                '/admin/dashboard'
        );

$reservationsActive =
        $adminMenuActive(
                '/admin/reservations',
                true
        );


/*
|--------------------------------------------------------------------------
| Configuración
|--------------------------------------------------------------------------
|
| El padre se considera activo cuando estamos
| dentro de cualquiera de sus módulos.
|
*/

$configActive = false;

if ($adminCanAccessSettings) {

    $configActive =
            $adminMenuActive(
                    '/admin/settings',
                    true
            )
            ||
            $adminMenuActive(
                    '/admin/users',
                    true
            )
            ||
            $adminMenuActive(
                    '/admin/branches',
                    true
            );
}


/*
|--------------------------------------------------------------------------
| Submenús
|--------------------------------------------------------------------------
*/

$settingsActive =
        $adminMenuActive(
                '/admin/settings',
                true
        );

$usersActive =
        $adminMenuActive(
                '/admin/users',
                true
        );

$branchesActive =
        $adminMenuActive(
                '/admin/branches',
                true
        );

?>
<!DOCTYPE html>

<html lang="es">

<head>

    <meta charset="UTF-8">

    <meta
            name="viewport"
            content="width=device-width, initial-scale=1.0"
    >

    <title>
        <?= htmlspecialchars(
                $adminSiteName,
                ENT_QUOTES,
                'UTF-8'
        ) ?>
    </title>

    <meta
            name="description"
            content="<?= htmlspecialchars(
                    $adminSiteDescription,
                    ENT_QUOTES,
                    'UTF-8'
            ) ?>"
    >

    <?php if ($adminFaviconUrl !== ''): ?>

        <link
                rel="icon"
                href="<?= htmlspecialchars(
                        $adminFaviconUrl,
                        ENT_QUOTES,
                        'UTF-8'
                ) ?>"
        >

    <?php endif; ?>


    <!-- =========================================================
         FUENTES Y CDN
    ========================================================== -->

    <link
            href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&family=Plus+Jakarta+Sans:wght@400;500;600;700&display=swap"
            rel="stylesheet"
    >

    <link
            href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css"
            rel="stylesheet"
    >

    <link
            href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.1/font/bootstrap-icons.css"
            rel="stylesheet"
    >

    <link
            href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css"
            rel="stylesheet"
    >

    <link
            href="<?= APP_URL ?>/public/css/fonts.css"
            rel="stylesheet"
    >


    <!-- =========================================================
         CSS PROPIO
    ========================================================== -->

    <link
            href="<?= APP_URL ?>/public/css/admin.css"
            rel="stylesheet"
    >

    <link
            href="<?= APP_URL ?>/public/css/admin-page-header.css"
            rel="stylesheet"
    >

    <link
            href="<?= APP_URL ?>/public/css/admin-reservations.css"
            rel="stylesheet"
    >

    <link
            href="<?= APP_URL ?>/public/css/admin-notifications.css"
            rel="stylesheet"
    >

    <link
            href="<?= APP_URL ?>/public/css/admin-users.css"
            rel="stylesheet"
    >

    <link
            href="<?= APP_URL ?>/public/css/admin-branches.css"
            rel="stylesheet"
    >


    <!-- =========================================================
         FUENTE DINÁMICA
    ========================================================== -->

    <style>

        :root {

            --font-sans:
        <?= $adminSiteFont ?>;

            --velox-font:
                    var(--font-sans);

            --bs-body-font-family:
                    var(--font-sans);

            --bs-font-sans-serif:
                    var(--font-sans);
        }

        html,
        body {

            font-family:
                    var(--font-sans)
            !important;
        }

        body,
        body button,
        body input,
        body select,
        body textarea,
        body option,
        body table,
        body th,
        body td,
        body label,
        body a,
        body p,
        body span,
        body small,
        body strong,
        body b,
        body h1,
        body h2,
        body h3,
        body h4,
        body h5,
        body h6 {

            font-family:
                    var(--font-sans)
            !important;
        }

    </style>


    <link
            rel="stylesheet"
            href="<?= APP_URL ?>/public/css/profile.css?v=<?= time(); ?>"
    >

    <link
            rel="stylesheet"
            href="<?= APP_URL ?>/public/css/settings.css"
    >


    <!-- =========================================================
         CHART.JS
    ========================================================== -->

    <script
            src="https://cdn.jsdelivr.net/npm/chart.js"
    ></script>

    <script>

        window.APP_URL =
            "<?= APP_URL ?>";

    </script>


<?php
$moduleAdminPath = parse_url($_SERVER['REQUEST_URI'] ?? '/', PHP_URL_PATH) ?: '/';
$moduleAdminBase = parse_url(APP_URL, PHP_URL_PATH) ?: '';
if ($moduleAdminBase !== '' && $moduleAdminBase !== '/' && str_starts_with($moduleAdminPath, $moduleAdminBase)) {
    $moduleAdminPath = substr($moduleAdminPath, strlen($moduleAdminBase));
}
$moduleAdminPath = '/' . trim($moduleAdminPath, '/');
$moduleAdminCss = [];
if (str_starts_with($moduleAdminPath, '/admin/items')) $moduleAdminCss = ['admin-items.css', 'admin-item-form.css'];
elseif (str_starts_with($moduleAdminPath, '/admin/categories')) $moduleAdminCss = ['admin-categories.css'];
elseif (str_starts_with($moduleAdminPath, '/admin/pages')) $moduleAdminCss = ['admin-pages.css', 'admin-page-form.css'];
elseif (str_starts_with($moduleAdminPath, '/admin/events')) $moduleAdminCss = ['admin-events.css'];
elseif (str_starts_with($moduleAdminPath, '/admin/coupons')) $moduleAdminCss = ['admin-coupons.css'];
foreach ($moduleAdminCss as $cssFile):
    $cssPath = __DIR__ . '/../../../public/css/' . $cssFile;
    if (is_file($cssPath)):
?>
<link rel="stylesheet" href="<?= APP_URL ?>/public/css/<?= htmlspecialchars($cssFile, ENT_QUOTES, 'UTF-8') ?>?v=<?= (int) filemtime($cssPath) ?>">
<?php endif; endforeach; ?>

</head>


<body>


<div class="app-container">


    <!-- =========================================================
         SIDEBAR
    ========================================================== -->

    <aside class="sidebar">


        <!-- =====================================================
             BRAND
        ====================================================== -->

        <div class="brand">

            <div class="brand-icon">

                <?php if ($adminLogoUrl !== ''): ?>

                    <img
                            src="<?= htmlspecialchars(
                                    $adminLogoUrl,
                                    ENT_QUOTES,
                                    'UTF-8'
                            ) ?>"
                            alt="<?= htmlspecialchars(
                                    $adminSiteName,
                                    ENT_QUOTES,
                                    'UTF-8'
                            ) ?>"
                            class="brand-logo"
                    >

                <?php else: ?>

                    <i class="fa-solid fa-bolt"></i>

                <?php endif; ?>

            </div>


            <div class="brand-text">

                <?= htmlspecialchars(
                        $adminSiteName,
                        ENT_QUOTES,
                        'UTF-8'
                ) ?>

            </div>

        </div>


        <!-- =====================================================
             MENÚ
        ====================================================== -->

        <ul class="sidebar-nav">


            <!-- =================================================
                 PRINCIPAL
            ================================================== -->

            <li class="sidebar-label">
                Principal
            </li>


            <!-- Dashboard -->

            <li class="nav-item">

                <a
                        href="<?= APP_URL ?>/admin/dashboard"
                        class="nav-link <?= $dashboardActive ? 'active' : '' ?>"
                >

                    <i
                            class="fa-solid fa-chart-pie nav-icon"
                    ></i>

                    <span class="nav-text">
                        Panel Principal
                    </span>

                </a>

            </li>


            <!-- Reservas -->

            <li class="nav-item">

                <a
                        href="<?= APP_URL ?>/admin/reservations"
                        class="nav-link <?= $reservationsActive ? 'active' : '' ?>"
                >

                    <i
                            class="fa-solid fa-calendar-check nav-icon"
                    ></i>

                    <span class="nav-text">
                        Reservas
                    </span>

                </a>

            </li>


            
            <!-- MÓDULOS INCORPORADOS -->
            <?php if (!$adminIsStaff): ?>
                <li class="sidebar-label">Módulos adicionales</li>
                <?php foreach ([
                    ['/admin/items', 'Productos', 'fa-box'],
                    ['/admin/categories', 'Categorías', 'fa-tags'],
                    ['/admin/pages', 'Páginas', 'fa-file-lines'],
                    ['/admin/events', 'Eventos', 'fa-calendar-days'],
                    ['/admin/coupons', 'Cupones', 'fa-ticket']
                ] as [$moduleRoute, $moduleLabel, $moduleIcon]): ?>
                    <li class="nav-item">
                        <a href="<?= APP_URL . $moduleRoute ?>" class="nav-link <?= $adminMenuActive($moduleRoute, true) ? 'active' : '' ?>">
                            <i class="fa-solid <?= htmlspecialchars($moduleIcon, ENT_QUOTES, 'UTF-8') ?> nav-icon"></i>
                            <span class="nav-text"><?= htmlspecialchars($moduleLabel, ENT_QUOTES, 'UTF-8') ?></span>
                        </a>
                    </li>
                <?php endforeach; ?>
            <?php endif; ?>

<!-- =================================================
                 OPERACIÓN / CONFIGURACIÓN
            ================================================== -->

            <?php if (!$adminIsStaff): ?>


                <!-- =============================================
                     CONFIGURACIÓN
                ============================================== -->

                <?php if ($adminCanAccessSettings): ?>


                    <li class="sidebar-label">
                        Ajustes de Sistema
                    </li>


                    <li class="nav-item">


                        <!-- Padre -->

                        <a
                                href="#submenuConfig"
                                class="nav-link <?= $configActive ? 'active' : '' ?>"
                                data-bs-toggle="collapse"
                                role="button"
                                aria-expanded="<?= $configActive ? 'true' : 'false' ?>"
                                aria-controls="submenuConfig"
                        >

                            <i
                                    class="fa-solid fa-gear nav-icon"
                            ></i>

                            <span class="nav-text">
                                Configuración
                            </span>

                            <i
                                    class="fa-solid fa-chevron-down submenu-arrow"
                            ></i>

                        </a>


                        <!-- Submenú -->

                        <div
                                class="collapse submenu <?= $configActive ? 'show' : '' ?>"
                                id="submenuConfig"
                        >

                            <ul>


                                <!-- =================================
                                     AJUSTES GENERALES
                                     SUPERADMIN + ADMIN
                                ================================== -->

                                <li>

                                    <a
                                            href="<?= APP_URL ?>/admin/settings"
                                            class="submenu-link <?= $settingsActive ? 'active' : '' ?>"
                                    >

                                        <i
                                                class="fa-solid fa-circle-dot me-2"
                                        ></i>

                                        Ajustes Generales

                                        <?php if ($adminIsAdmin && !$adminIsSuperAdmin): ?>

                                            <span
                                                    class="ms-auto"
                                                    title="Solo lectura"
                                            >
                                                <i
                                                        class="fa-solid fa-lock"
                                                        style="font-size:.68rem;"
                                                ></i>
                                            </span>

                                        <?php endif; ?>

                                    </a>

                                </li>


                                <!-- =================================
                                     USUARIOS Y ROLES
                                     SUPERADMIN + ADMIN
                                ================================== -->

                                <?php if (
                                        $adminIsSuperAdmin ||
                                        $adminIsAdmin
                                ): ?>

                                    <li>

                                        <a
                                                href="<?= APP_URL ?>/admin/users"
                                                class="submenu-link <?= $usersActive ? 'active' : '' ?>"
                                        >

                                            <i
                                                    class="fa-solid fa-circle-dot me-2"
                                            ></i>

                                            Usuarios y Roles

                                        </a>

                                    </li>


                                    <!-- =============================
                                         SEDES
                                         SUPERADMIN + ADMIN
                                    ============================== -->

                                    <li>

                                        <a
                                                href="<?= APP_URL ?>/admin/branches"
                                                class="submenu-link <?= $branchesActive ? 'active' : '' ?>"
                                        >

                                            <i
                                                    class="fa-solid fa-circle-dot me-2"
                                            ></i>

                                            Sedes

                                        </a>

                                    </li>

                                <?php endif; ?>


                            </ul>

                        </div>

                    </li>

                <?php endif; ?>


            <?php endif; ?>


        </ul>

    </aside>


    <!-- =========================================================
         CONTENIDO PRINCIPAL
    ========================================================== -->

    <main class="main-content d-flex flex-column">


        <!-- =====================================================
             NAVBAR SUPERIOR
        ====================================================== -->

        <header
                class="top-navbar d-flex align-items-center justify-content-between px-4 bg-white border-bottom shadow-sm"
        >


            <div class="navbar-title font-bold text-secondary">

                <span>
                    Panel de Gestión
                </span>


                <span class="admin-navbar-scope">

                    <i
                            class="fa-solid fa-location-dot"
                    ></i>

                    <?= htmlspecialchars(
                            $adminScopeName,
                            ENT_QUOTES,
                            'UTF-8'
                    ) ?>

                </span>

            </div>


            <!-- ================================================
                 ACCIONES
            ================================================= -->

            <div
                    class="d-flex align-items-center gap-3 ms-auto"
            >


                <div
                        class="d-flex align-items-center gap-2 me-2"
                >


                    <!-- Pantalla completa -->

                    <button
                            type="button"
                            class="btn btn-icon btn-light rounded-circle shadow-sm"
                            id="btnToggleFullscreen"
                            title="Pantalla completa"
                    >

                        <i
                                class="bi bi-arrows-fullscreen text-secondary"
                                style="font-size: 0.9rem;"
                        ></i>

                    </button>


                    <!-- Inicio -->

                    <a
                            href="<?= APP_URL; ?>"
                            class="btn btn-icon btn-light rounded-circle shadow-sm"
                            title="Ir al Inicio"
                    >

                        <i
                                class="bi bi-house text-secondary"
                                style="font-size: 0.95rem;"
                        ></i>

                    </a>


                    <!-- =========================================================
                         NOTIFICACIONES
                    ========================================================= -->

                    <div class="dropdown notification-dropdown">

                        <button
                                type="button"
                                class="btn btn-icon btn-light rounded-circle shadow-sm position-relative notification-trigger"
                                id="notificationsDropdown"
                                data-bs-toggle="dropdown"
                                data-bs-auto-close="outside"
                                aria-expanded="false"
                                aria-label="Notificaciones"
                                title="Notificaciones"
                        >

                            <i class="bi bi-bell text-secondary"></i>

                            <span
                                    id="notificationBadge"
                                    class="notification-badge d-none"
                            >
                                0
                            </span>

                        </button>


                        <!-- =====================================================
                             DROPDOWN
                        ====================================================== -->

                        <div
                                class="dropdown-menu dropdown-menu-end notification-menu"
                                aria-labelledby="notificationsDropdown"
                        >

                            <!-- HEADER -->

                            <div class="notification-menu-header">

                                <div>

                                    <div class="notification-menu-title">
                                        Notificaciones
                                    </div>

                                    <div
                                            class="notification-menu-subtitle"
                                            id="notificationSubtitle"
                                    >
                                        Sin notificaciones nuevas
                                    </div>

                                </div>


                                <button
                                        type="button"
                                        class="notification-mark-all"
                                        id="btnMarkAllNotifications"
                                >
                                    Marcar todas
                                </button>

                            </div>


                            <!-- CONTENIDO -->

                            <div
                                    id="notificationsList"
                                    class="notifications-list"
                            >

                                <div class="notification-loading">

                                    <div
                                            class="spinner-border spinner-border-sm"
                                            role="status"
                                    ></div>

                                    <span>
                                        Cargando notificaciones...
                                    </span>

                                </div>

                            </div>

                            <!-- FOOTER -->

                            <div class="notification-menu-footer">

                                <a
                                        href="<?= APP_URL ?>/admin/reservations"
                                        class="notification-view-all"
                                >
                                    <span>
                                        Ver reservas
                                    </span>

                                    <i class="bi bi-arrow-right"></i>

                                </a>

                            </div>

                        </div>

                    </div>

                </div>


                <!-- =================================================
                     USUARIO
                ================================================== -->

                <div class="dropdown">

                    <a
                            href="#"
                            class="d-flex align-items-center text-decoration-none dropdown-toggle user-dropdown-toggle p-1 pe-2 rounded-pill bg-white shadow-sm"
                            id="dropdownUser"
                            data-bs-toggle="dropdown"
                            aria-expanded="false"
                    >

                        <div
                                class="text-end me-2 d-none d-md-block ps-2"
                        >

                            <span
                                    class="d-block fw-bold text-dark lh-1"
                                    style="font-size: 0.85rem;"
                            >

                                <?= htmlspecialchars(
                                        $_SESSION['user_name']
                                        ?? 'Usuario'
                                ); ?>

                            </span>


                            <small
                                    class="text-muted"
                                    style="font-size: 0.72rem;"
                            >

                                <?= htmlspecialchars(
                                        $_SESSION['user_email']
                                        ?? 'usuario@youseebog.com'
                                ); ?>

                            </small>

                        </div>


                        <?php

                        $adminAvatar =
                                trim(
                                        (string) (
                                                $_SESSION['user_avatar']
                                                ?? ''
                                        )
                                );

                        if ($adminAvatar === '') {
                            $adminAvatar = 'default.png';
                        }

                        $adminAvatar =
                                basename($adminAvatar);

                        $adminAvatarUrl =
                                APP_URL .
                                '/assets/img/' .
                                rawurlencode($adminAvatar);

                        $adminDefaultAvatarUrl =
                                APP_URL .
                                '/assets/img/default.png';

                        ?>

                        <img
                                src="<?= htmlspecialchars(
                                        $adminAvatarUrl,
                                        ENT_QUOTES,
                                        'UTF-8'
                                ) ?>"
                                alt="Avatar"
                                class="admin-user-avatar rounded-circle border border-2 border-white shadow-sm"
                                width="38"
                                height="38"
                                style="object-fit: cover;"
                                onerror="
                                        this.onerror=null;
                                        this.src='<?= htmlspecialchars(
                                        $adminDefaultAvatarUrl,
                                        ENT_QUOTES,
                                        'UTF-8'
                                ) ?>';
                                        "
                        >

                    </a>


                    <!-- =================================================
                         MENÚ PERFIL
                    ================================================== -->

                    <ul
                            class="dropdown-menu dropdown-menu-end shadow-lg border-0 rounded-4 mt-2 py-2"
                            aria-labelledby="dropdownUser"
                            style="min-width: 180px;"
                    >


                        <!-- Mi Perfil -->

                        <li>

                            <a
                                    class="dropdown-item d-flex align-items-center py-2 px-3 fw-medium"
                                    href="<?= APP_URL; ?>/admin/profile"
                            >

                                <i
                                        class="bi bi-person me-2 text-muted fs-6"
                                ></i>

                                Mi Perfil

                            </a>

                        </li>


                        <!-- Ajustes
                             SuperAdmin + Admin
                        -->

                        <?php if ($adminCanAccessSettings): ?>

                            <li>

                                <a
                                        class="dropdown-item d-flex align-items-center py-2 px-3 fw-medium"
                                        href="<?= APP_URL; ?>/admin/settings"
                                >

                                    <i
                                            class="bi bi-gear me-2 text-muted fs-6"
                                    ></i>

                                    Ajustes

                                </a>

                            </li>

                        <?php endif; ?>


                        <li>

                            <hr
                                    class="dropdown-divider my-1 opacity-25"
                            >

                        </li>


                        <!-- Cerrar sesión -->

                        <li>

                            <a
                                    class="dropdown-item d-flex align-items-center py-2 px-3 fw-medium text-danger"
                                    href="#"
                                    id="btnLogout"
                            >

                                <i
                                        class="bi bi-box-arrow-right me-2 fs-6"
                                ></i>

                                Cerrar Sesión

                            </a>

                        </li>


                    </ul>

                </div>

            </div>

        </header>


        <!-- =====================================================
             CONTENIDO
        ====================================================== -->

        <div
                class="content-body p-4 flex-grow-1"
        >

            <?php
            require_once $contentView;
            ?>

        </div>


    </main>

</div>


<!-- ============================================================
     SCRIPTS
============================================================= -->

<script
        src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"
></script>

<script
        src="https://cdn.jsdelivr.net/npm/sweetalert2@11"
></script>

<script
        src="<?= APP_URL ?>/js/toast.js"
></script>

<script
        src="<?= APP_URL ?>/js/admin.js"
></script>

<script
        src="<?= APP_URL ?>/public/js/admin-notifications.js"
></script>

<!-- ============================================================
     USER FORM
============================================================= -->

<?php if (
        isset($contentView) &&
        str_ends_with(
                $contentView,
                '/admin/user-form.php'
        )
): ?>

    <script
            src="<?= APP_URL ?>/public/js/admin-user-form.js"
    ></script>

<?php endif; ?>


<!-- ============================================================
     USERS
============================================================= -->

<?php if (
        isset($contentView) &&
        str_ends_with(
                $contentView,
                '/admin/users.php'
        )
): ?>

    <script
            src="<?= APP_URL ?>/public/js/admin-users.js"
    ></script>

<?php endif; ?>


<!-- ============================================================
     BRANCHES
============================================================= -->

<?php if (
        isset($contentView) &&
        str_ends_with(
                $contentView,
                '/admin/branches.php'
        )
): ?>

    <script
            src="<?= APP_URL ?>/public/js/admin-branches.js"
    ></script>

<?php endif; ?>


<!-- ============================================================
     BRANCH FORM
============================================================= -->

<?php if (
        isset($contentView) &&
        str_ends_with(
                $contentView,
                '/admin/branch-form.php'
        )
): ?>

    <script
            src="<?= APP_URL ?>/public/js/admin-branch-form.js"
    ></script>

<?php endif; ?>


<!-- ============================================================
     SCRIPT DINÁMICO DE LA VISTA
============================================================= -->

<?php if (isset($script)): ?>

    <script
            src="<?= APP_URL ?>/js/<?= $script ?>.js"
    ></script>

<?php endif; ?>



<?php
$moduleAdminScripts = [];
if (isset($contentView)) {
    if (str_ends_with($contentView, '/admin/items.php')) $moduleAdminScripts[] = 'admin-items.js';
    if (str_ends_with($contentView, '/admin/item-form.php')) $moduleAdminScripts[] = 'admin-item-form.js';
    if (str_ends_with($contentView, '/admin/categories.php')) $moduleAdminScripts[] = 'admin-categories.js';
    if (str_ends_with($contentView, '/admin/category-page.php')) $moduleAdminScripts[] = 'admin-category-page.js';
    if (str_ends_with($contentView, '/admin/pages.php')) $moduleAdminScripts[] = 'admin-pages.js';
    if (str_ends_with($contentView, '/admin/page-form.php')) $moduleAdminScripts[] = 'admin-page-form.js';
    if (str_ends_with($contentView, '/admin/events.php')) $moduleAdminScripts[] = 'admin-events.js';
    if (str_ends_with($contentView, '/admin/coupons.php')) $moduleAdminScripts[] = 'admin-coupons.js';
}
foreach ($moduleAdminScripts as $jsFile):
    $jsPath = __DIR__ . '/../../../public/js/' . $jsFile;
    if (is_file($jsPath)):
?>
<script src="<?= APP_URL ?>/public/js/<?= htmlspecialchars($jsFile, ENT_QUOTES, 'UTF-8') ?>?v=<?= (int) filemtime($jsPath) ?>"></script>
<?php endif; endforeach; ?>

</body>

</html>