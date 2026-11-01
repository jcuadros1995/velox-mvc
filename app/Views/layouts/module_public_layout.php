<!-- app/Views/layouts/public_layout.php -->
<?php

$siteName =
        SiteConfig::siteName();

$siteDescription =
        SiteConfig::siteDescription();

$siteFont =
        SiteConfig::font();

$primaryColor =
        SiteConfig::primaryColor();

$secondaryColor =
        SiteConfig::secondaryColor();

$siteLogo =
        SiteConfig::siteLogo();

$siteFavicon =
        SiteConfig::siteFavicon();

$settingsAssetUrl =
        rtrim(APP_URL, '/') .
        '/assets/img/settings/';

$siteLogoUrl =
        $siteLogo !== ''
                ? $settingsAssetUrl . rawurlencode($siteLogo)
                : '';

$siteFaviconUrl =
        $siteFavicon !== ''
                ? $settingsAssetUrl . rawurlencode($siteFavicon)
                : '';
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>
        <?= htmlspecialchars(
                (!empty($title) ? $title : $siteName),
                ENT_QUOTES,
                'UTF-8'
        ) ?>
    </title>

    <meta
            name="description"
            content="<?= htmlspecialchars(
                    $siteDescription,
                    ENT_QUOTES,
                    'UTF-8'
            ) ?>"
    >

    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&family=Plus+Jakarta+Sans:wght@400;500;600;700&display=swap" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet">
    <link href="<?= APP_URL ?>/public/css/fonts.css" rel="stylesheet">
    <link href="<?= APP_URL ?>/public/css/public.css" rel="stylesheet">
    <link href="<?= APP_URL ?>/public/css/cart-checkout.css" rel="stylesheet">

    <style>
        :root {
            --font-sans: <?= $siteFont ?>;
            --velox-font: var(--font-sans);

            --bs-body-font-family: var(--font-sans);
            --bs-font-sans-serif: var(--font-sans);
        }

        html,
        body {
            font-family: var(--font-sans) !important;
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
            font-family: var(--font-sans) !important;
        }
    </style>

    <?php if ($siteFaviconUrl !== ''): ?>
        <link
                rel="icon"
                href="<?= htmlspecialchars(
                        $siteFaviconUrl,
                        ENT_QUOTES,
                        'UTF-8'
                ) ?>"
        >
    <?php endif; ?>

    <script>
        window.APP_URL = "<?= APP_URL ?>";
    </script>

<?php
$modulePublicPath = parse_url($_SERVER['REQUEST_URI'] ?? '/', PHP_URL_PATH) ?: '/';
$modulePublicBase = parse_url(APP_URL, PHP_URL_PATH) ?: '';
if ($modulePublicBase !== '' && $modulePublicBase !== '/' && str_starts_with($modulePublicPath, $modulePublicBase)) {
    $modulePublicPath = substr($modulePublicPath, strlen($modulePublicBase));
}
$modulePublicPath = '/' . trim($modulePublicPath, '/');
$modulePublicCss = [];
if (str_starts_with($modulePublicPath, '/mi-cuenta')) {
    $modulePublicCss[] = 'customer-account.css';
}
if ($modulePublicPath === '/menu') {
    $modulePublicCss[] = 'menu-public.css';
}
if ($modulePublicPath === '/tienda') {
    $modulePublicCss[] = 'storefront.css';
}
if (in_array($modulePublicPath, ['/menu', '/sedes', '/eventos'], true)
    || str_starts_with($modulePublicPath, '/menu/')
    || str_starts_with($modulePublicPath, '/pages/')) {
    $modulePublicCss[] = 'public-pages.css';
}
if ($modulePublicPath === '/pages/contact') $modulePublicCss[] = 'contact.css';
if ($modulePublicPath === '/pages/eventos-burger-town-bogota' || $modulePublicPath === '/eventos') $modulePublicCss[] = 'events.css';
if (str_starts_with($modulePublicPath, '/pages/') && !in_array($modulePublicPath, ['/pages/contact', '/pages/eventos-burger-town-bogota'], true)) {
    $modulePublicCss[] = 'site-page.css';
}
foreach (array_unique($modulePublicCss) as $cssFile):
    $cssPath = __DIR__ . '/../../../public/css/' . $cssFile;
    if (is_file($cssPath)):
?>
<link rel="stylesheet" href="<?= APP_URL ?>/public/css/<?= htmlspecialchars($cssFile, ENT_QUOTES, 'UTF-8') ?>?v=<?= (int) filemtime($cssPath) ?>">
<?php endif; endforeach; ?>

    <?= Csrf::meta() ?>

    <script>
        window.CSRF_TOKEN = <?= json_encode(Csrf::token(), JSON_HEX_TAG | JSON_HEX_APOS | JSON_HEX_AMP | JSON_HEX_QUOT) ?>;
    </script>
</head>
<body>

<?php require_once __DIR__ . '/partials/module-navbar.php'; ?>

<!-- Inyección dinámica del contenido -->
<?php require_once $contentView; ?>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<script src="<?= APP_URL ?>/public/js/toast.js"></script>
<script src="<?= APP_URL ?>/public/js/cart.js"></script>

<?php if (isset($script) && preg_match('/^[a-z0-9_-]+$/i', (string)$script)): ?>
<script src="<?= APP_URL ?>/public/js/<?= htmlspecialchars($script, ENT_QUOTES, 'UTF-8') ?>.js"></script>
<?php endif; ?>

</body>
</html>