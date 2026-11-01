<?php
/**
 * Navegación pública Velox MVC.
 * Menú compacto compartido para escritorio y móvil.
 */
$currentPath = rtrim(parse_url($_SERVER['REQUEST_URI'] ?? '/', PHP_URL_PATH) ?: '/', '/') ?: '/';
$basePath = rtrim(parse_url(APP_URL, PHP_URL_PATH) ?: '/', '/');
$pathIs = static function (string $path) use ($currentPath, $basePath): bool {
    $normalized = rtrim($basePath . $path, '/') ?: '/';
    return $currentPath === $normalized || ($normalized !== '/' && strpos($currentPath, $normalized . '/') === 0);
};

$homeUrl = APP_URL;
$loginUrl = APP_URL . '/login';
$accountUrl = APP_URL . '/mi-cuenta';
$reservationsUrl = APP_URL . '/reservas';
$searchReservationUrl = APP_URL . '/consultar-reserva';
$storeUrl = APP_URL . '/tienda';
$cartUrl = APP_URL . '/carrito';
$menuUrl = APP_URL . '/menu';
$branchesUrl = APP_URL . '/sedes';
$eventsUrl = APP_URL . '/eventos';
$contactUrl = APP_URL . '/pages/contact';

$isHome = $currentPath === ($basePath ?: '/');
$isReservations = $pathIs('/reservas');
$isSearchReservation = $pathIs('/consultar-reserva');
$isStore = $pathIs('/tienda');
$isCart = $pathIs('/carrito');
$isMenu = $pathIs('/menu');
$isBranches = $pathIs('/sedes');
$isEvents = $pathIs('/eventos');
$isContact = $pathIs('/pages/contact');

$siteName = SiteConfig::siteName();
$logoName = SiteConfig::siteLogo();
$logoUrl = $logoName !== ''
    ? rtrim(APP_URL, '/') . '/assets/img/settings/' . rawurlencode($logoName)
    : '';

$currentUser = Auth::user();
$isCustomer = $currentUser && strtolower((string)($currentUser['role_name'] ?? '')) === 'customer';
$exploreActive = $isMenu || $isBranches || $isEvents || $isContact;
$reservationsActive = $isReservations || $isSearchReservation;
?>
<nav id="mainNavbar" class="navbar navbar-expand-lg public-navbar" aria-label="Navegación principal">
    <div class="container">
        <a class="navbar-brand velox-brand" href="<?= htmlspecialchars($homeUrl, ENT_QUOTES, 'UTF-8') ?>"
           aria-label="<?= htmlspecialchars($siteName . ' - Inicio', ENT_QUOTES, 'UTF-8') ?>">
            <span class="brand-mark">
                <?php if ($logoUrl !== ''): ?>
                    <img src="<?= htmlspecialchars($logoUrl, ENT_QUOTES, 'UTF-8') ?>"
                         alt="" class="brand-logo"
                         onerror="this.onerror=null;this.parentNode.innerHTML='<i class=&quot;fa-solid fa-bolt&quot; aria-hidden=&quot;true&quot;></i>';">
                <?php else: ?>
                    <i class="fa-solid fa-bolt" aria-hidden="true"></i>
                <?php endif; ?>
            </span>
            <span class="velox-brand-name"><?= htmlspecialchars($siteName, ENT_QUOTES, 'UTF-8') ?></span>
        </a>

        <button class="navbar-toggler" type="button" data-bs-toggle="collapse"
                data-bs-target="#mainNav" aria-controls="mainNav"
                aria-expanded="false" aria-label="Abrir menú de navegación">
            <span class="navbar-toggler-icon"></span>
        </button>

        <div class="collapse navbar-collapse" id="mainNav">
            <ul class="navbar-nav ms-auto align-items-lg-center">
                <li class="nav-item">
                    <a class="nav-link <?= $isHome ? 'active' : '' ?>"
                       href="<?= htmlspecialchars($homeUrl, ENT_QUOTES, 'UTF-8') ?>"
                       <?= $isHome ? 'aria-current="page"' : '' ?>>
                        <i class="fa-solid fa-house" aria-hidden="true"></i><span>Inicio</span>
                    </a>
                </li>

                <li class="nav-item dropdown">
                    <a class="nav-link dropdown-toggle <?= $exploreActive ? 'active' : '' ?>"
                       href="#" id="exploreDropdown" role="button" data-bs-toggle="dropdown"
                       aria-expanded="false">
                        <i class="fa-solid fa-compass" aria-hidden="true"></i><span>Explorar</span>
                    </a>
                    <ul class="dropdown-menu dropdown-menu-end velox-dropdown" aria-labelledby="exploreDropdown">
                        <li><a class="dropdown-item <?= $isMenu ? 'active' : '' ?>" href="<?= htmlspecialchars($menuUrl, ENT_QUOTES, 'UTF-8') ?>">
                            <i class="fa-solid fa-utensils" aria-hidden="true"></i><span><strong>Menú</strong><small>Descubre nuestros productos</small></span></a></li>
                        <li><a class="dropdown-item <?= $isBranches ? 'active' : '' ?>" href="<?= htmlspecialchars($branchesUrl, ENT_QUOTES, 'UTF-8') ?>">
                            <i class="fa-solid fa-location-dot" aria-hidden="true"></i><span><strong>Sedes</strong><small>Encuentra tu punto más cercano</small></span></a></li>
                        <li><a class="dropdown-item <?= $isEvents ? 'active' : '' ?>" href="<?= htmlspecialchars($eventsUrl, ENT_QUOTES, 'UTF-8') ?>">
                            <i class="fa-solid fa-calendar-days" aria-hidden="true"></i><span><strong>Eventos</strong><small>Conoce nuestras actividades</small></span></a></li>
                        <li><a class="dropdown-item <?= $isContact ? 'active' : '' ?>" href="<?= htmlspecialchars($contactUrl, ENT_QUOTES, 'UTF-8') ?>">
                            <i class="fa-solid fa-envelope" aria-hidden="true"></i><span><strong>Contacto</strong><small>Estamos para ayudarte</small></span></a></li>
                    </ul>
                </li>

                <li class="nav-item dropdown">
                    <a class="nav-link dropdown-toggle <?= $reservationsActive ? 'active' : '' ?>"
                       href="#" id="reservationsDropdown" role="button" data-bs-toggle="dropdown"
                       aria-expanded="false">
                        <i class="fa-regular fa-calendar-check" aria-hidden="true"></i><span>Reservas</span>
                    </a>
                    <ul class="dropdown-menu dropdown-menu-end velox-dropdown" aria-labelledby="reservationsDropdown">
                        <li><a class="dropdown-item <?= $isReservations ? 'active' : '' ?>" href="<?= htmlspecialchars($reservationsUrl, ENT_QUOTES, 'UTF-8') ?>">
                            <i class="fa-solid fa-calendar-plus" aria-hidden="true"></i><span><strong>Crear reserva</strong><small>Reserva tu mesa</small></span></a></li>
                        <li><a class="dropdown-item <?= $isSearchReservation ? 'active' : '' ?>" href="<?= htmlspecialchars($searchReservationUrl, ENT_QUOTES, 'UTF-8') ?>">
                            <i class="fa-solid fa-magnifying-glass" aria-hidden="true"></i><span><strong>Consultar reserva</strong><small>Revisa o gestiona tu reserva</small></span></a></li>
                    </ul>
                </li>

                <li class="nav-item">
                    <a class="nav-link <?= $isStore ? 'active' : '' ?>" href="<?= htmlspecialchars($storeUrl, ENT_QUOTES, 'UTF-8') ?>"
                       <?= $isStore ? 'aria-current="page"' : '' ?>>
                        <i class="fa-solid fa-store" aria-hidden="true"></i><span>Tienda</span>
                    </a>
                </li>

                <li class="nav-item">
                    <a class="nav-link store-cart-nav-link <?= $isCart ? 'active' : '' ?>"
                       href="<?= htmlspecialchars($cartUrl, ENT_QUOTES, 'UTF-8') ?>" <?= $isCart ? 'aria-current="page"' : '' ?>>
                        <i class="fa-solid fa-basket-shopping" aria-hidden="true"></i><span>Carrito</span>
                        <span class="store-cart-nav-count d-none" data-cart-count>0</span>
                    </a>
                </li>

                <li class="nav-item nav-account-item">
                    <?php if ($isCustomer): ?>
                        <a href="<?= htmlspecialchars($accountUrl, ENT_QUOTES, 'UTF-8') ?>" class="btn btn-velox-primary">
                            <i class="fa-solid fa-user-circle" aria-hidden="true"></i><span>Mi cuenta</span>
                        </a>
                    <?php else: ?>
                        <a href="<?= htmlspecialchars($loginUrl, ENT_QUOTES, 'UTF-8') ?>" class="btn btn-velox-primary">
                            <span>Acceso al panel</span><i class="fa-solid fa-arrow-right" aria-hidden="true"></i>
                        </a>
                    <?php endif; ?>
                </li>
            </ul>
        </div>
    </div>
</nav>