<?php
/**
 * app/Views/layouts/partials/navbar.php
 *
 * Navbar público reutilizable.
 */

$currentPath = parse_url($_SERVER['REQUEST_URI'] ?? '/', PHP_URL_PATH);

$currentPath = rtrim($currentPath, '/');

if ($currentPath === '') {
    $currentPath = '/';
}

$homePath = parse_url(APP_URL, PHP_URL_PATH) ?: '/';

$homeUrl = APP_URL;
$reservationsUrl = APP_URL . '/reservas';
$loginUrl = APP_URL . '/login';
$searchReservationUrl = APP_URL . '/consultar-reserva';
$storeUrl = APP_URL . '/tienda';

$isStore = ($currentPath === rtrim(parse_url($storeUrl, PHP_URL_PATH), '/'));
$isHome = ($currentPath === rtrim($homePath, '/'));
$isReservations = ($currentPath === rtrim(parse_url($reservationsUrl, PHP_URL_PATH), '/'));
$isLogin = ($currentPath === rtrim(parse_url($loginUrl, PHP_URL_PATH), '/'));
$isSearchReservation = ( $currentPath === rtrim(parse_url($searchReservationUrl, PHP_URL_PATH),'/'));
/*
|--------------------------------------------------------------------------
| HOME DROPDOWN
|--------------------------------------------------------------------------
|
| El dropdown se considera activo cuando estamos en Inicio.
|
|--------------------------------------------------------------------------
*/

$isHomeMenuActive = $isHome;

// Customer account shortcut (public navbar).
$userMenu = Auth::user();
$isCustomer = $userMenu && strtolower((string)($userMenu['role_name'] ?? '')) === 'customer';
$customerAccountUrl = APP_URL . '/mi-cuenta';

?>

<!-- =========================================================
     NAVBAR PÚBLICO
========================================================= -->

<nav id="mainNavbar" class="navbar navbar-expand-lg public-navbar">

    <div class="container">

        <!-- =====================================================
             BRAND
        ====================================================== -->

        <?php
        $navbarSiteName =
                SiteConfig::siteName();

        $navbarLogo =
                SiteConfig::siteLogo();

        $navbarLogoUrl =
                $navbarLogo !== ''
                        ? rtrim(APP_URL, '/') .
                        '/assets/img/settings/' .
                        rawurlencode($navbarLogo)
                        : '';
        ?>

        <a
            class="navbar-brand velox-brand"
            href="<?= htmlspecialchars($homeUrl) ?>"
            aria-label="<?= htmlspecialchars(
                    $navbarSiteName . ' - Inicio',
                    ENT_QUOTES,
                    'UTF-8'
            ) ?>"
        >

            <span class="brand-mark">

                <?php if ($navbarLogoUrl !== ''): ?>

                    <img
                            src="<?= htmlspecialchars(
                                    $navbarLogoUrl,
                                    ENT_QUOTES,
                                    'UTF-8'
                            ) ?>"
                            alt="<?= htmlspecialchars(
                                    $navbarSiteName,
                                    ENT_QUOTES,
                                    'UTF-8'
                            ) ?>"
                            class="brand-logo"
                    >

                <?php else: ?>

                    <i class="fa-solid fa-bolt"></i>

                <?php endif; ?>

            </span>

            <span>
                <?= htmlspecialchars(
                        $navbarSiteName,
                        ENT_QUOTES,
                        'UTF-8'
                ) ?>
            </span>

        </a>


        <!-- =====================================================
             MOBILE TOGGLER
        ====================================================== -->

        <button
            class="navbar-toggler"
            type="button"
            data-bs-toggle="collapse"
            data-bs-target="#mainNav"
            aria-controls="mainNav"
            aria-expanded="false"
            aria-label="Abrir navegación"
        >
            <span class="navbar-toggler-icon"></span>
        </button>


        <!-- =====================================================
             NAVIGATION
        ====================================================== -->

        <div
            class="collapse navbar-collapse"
            id="mainNav"
        >

            <ul class="navbar-nav ms-auto align-items-lg-center gap-lg-2">


                <!-- =================================================
                     INICIO - DROPDOWN
                ================================================== -->

                <li class="nav-item dropdown">

                    <a
                        class="nav-link dropdown-toggle <?= $isHomeMenuActive ? 'active' : '' ?>"
                        href="<?= htmlspecialchars($homeUrl) ?>"
                        id="homeDropdown"
                        role="button"
                        data-bs-toggle="dropdown"
                        aria-expanded="false"
                        <?= $isHomeMenuActive ? 'aria-current="page"' : '' ?>
                    >
                        <i class="fa-solid fa-house me-1"></i>
                        Inicio
                    </a>


                    <ul
                        class="dropdown-menu dropdown-menu-end velox-dropdown"
                        aria-labelledby="homeDropdown"
                    >

                        <!-- Inicio -->

                        <li>
                            <a
                                class="dropdown-item <?= $isHome ? 'active' : '' ?>"
                                href="<?= htmlspecialchars($homeUrl) ?>"
                            >
                                <i class="fa-solid fa-house"></i>

                                <span>
                                    <strong>Inicio</strong>
                                    <small>Ir al inicio</small>
                                </span>
                            </a>
                        </li>


                        <li>
                            <hr class="dropdown-divider">
                        </li>


                        <!-- Servicios -->

                        <li>
                            <a
                                class="dropdown-item"
                                href="<?= htmlspecialchars($homeUrl) ?>#servicios"
                            >
                                <i class="fa-solid fa-layer-group"></i>

                                <span>
                                    <strong>Servicios</strong>
                                    <small>Conoce nuestras soluciones</small>
                                </span>
                            </a>
                        </li>


                        <!-- Propuesta -->

                        <li>
                            <a
                                class="dropdown-item"
                                href="<?= htmlspecialchars($homeUrl) ?>#nosotros"
                            >
                                <i class="fa-solid fa-lightbulb"></i>

                                <span>
                                    <strong>Propuesta</strong>
                                    <small>Lo que ofrecemos</small>
                                </span>
                            </a>
                        </li>


                        <!-- Métricas -->

                        <li>
                            <a
                                class="dropdown-item"
                                href="<?= htmlspecialchars($homeUrl) ?>#estadisticas"
                            >
                                <i class="fa-solid fa-chart-line"></i>

                                <span>
                                    <strong>Métricas</strong>
                                    <small>Resultados y estadísticas</small>
                                </span>
                            </a>
                        </li>


                        <!-- Testimonios -->

                        <li>
                            <a
                                class="dropdown-item"
                                href="<?= htmlspecialchars($homeUrl) ?>#testimonios"
                            >
                                <i class="fa-solid fa-comments"></i>

                                <span>
                                    <strong>Testimonios</strong>
                                    <small>Experiencias de clientes</small>
                                </span>
                            </a>
                        </li>


                        <!-- Contacto -->

                        <li>
                            <a
                                class="dropdown-item"
                                href="<?= htmlspecialchars($homeUrl) ?>#contacto"
                            >
                                <i class="fa-solid fa-envelope"></i>

                                <span>
                                    <strong>Contacto</strong>
                                    <small>Hablemos contigo</small>
                                </span>
                            </a>
                        </li>

                    </ul>

                </li>


                <!-- =================================================
                     RESERVAS
                ================================================== -->

                <li class="nav-item">

                    <a
                        class="nav-link reservation-nav-link <?= $isReservations ? 'active' : '' ?>"
                        href="<?= htmlspecialchars($reservationsUrl) ?>"
                        <?= $isReservations ? 'aria-current="page"' : '' ?>
                    >

                        <i class="fa-regular fa-calendar-check me-1"></i>

                        Reservas

                    </a>

                </li>

                <!-- =================================================
                     CONSULTAR RESERVA
                ================================================== -->

                <li class="nav-item">

                    <a
                        class="nav-link reservation-search-nav-link <?= $isSearchReservation ? 'active' : '' ?>"
                        href="<?= htmlspecialchars($searchReservationUrl) ?>"
                        <?= $isSearchReservation ? 'aria-current="page"' : '' ?>
                    >
                        <i class="fa-solid fa-magnifying-glass me-1"></i>
                        Consultar reserva
                    </a>

                </li>

                <li class="nav-item">
                    <a
                        href="<?= htmlspecialchars($storeUrl, ENT_QUOTES, 'UTF-8') ?>"
                        class="nav-link <?= $isStore ? 'active' : '' ?>"
                        <?= $isStore ? 'aria-current="page"' : '' ?>
                    >
                        <i class="fa-solid fa-store me-1"></i>
                        Tienda
                    </a>
                </li>

                <li class="nav-item">
                    <a href="<?= htmlspecialchars(APP_URL . '/carrito', ENT_QUOTES, 'UTF-8') ?>" class="nav-link store-cart-nav-link">
                        <i class="fa-solid fa-basket-shopping"></i>
                        Carrito
                        <span class="store-cart-nav-count d-none" data-cart-count>0</span>
                    </a>
                </li>

                <!-- =================================================
                     ACCESO AL PANEL
                ================================================== -->

                <li class="nav-item ms-lg-3 mt-3 mt-lg-0">

<?php if ($isCustomer): ?>
                    <a href="<?= htmlspecialchars($customerAccountUrl, ENT_QUOTES, 'UTF-8') ?>" class="btn btn-velox-primary">
                        <i class="fa-solid fa-user-circle me-2"></i> Mi cuenta
                    </a>
                    <?php else: ?>
                                        <a
                        href="<?= htmlspecialchars($loginUrl) ?>"
                        class="btn btn-velox-primary <?= $isLogin ? 'active' : '' ?>"
                        <?= $isLogin ? 'aria-current="page"' : '' ?>
                    >

                        Acceso al panel

                        <i class="fa-solid fa-arrow-right ms-2"></i>

                    </a>
                    <?php endif; ?>

                </li>

            </ul>

        </div>

    </div>

</nav>