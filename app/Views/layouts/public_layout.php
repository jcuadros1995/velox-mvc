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
                $siteName,
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
    <link href="<?= APP_URL ?>/public/css/public-navbar.css" rel="stylesheet">
    <link href="<?= APP_URL ?>/public/css/branches.css" rel="stylesheet">
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
    <?= Csrf::meta() ?>

    <script>
        window.CSRF_TOKEN = <?= json_encode(Csrf::token(), JSON_HEX_TAG | JSON_HEX_APOS | JSON_HEX_AMP | JSON_HEX_QUOT) ?>;
    </script>
</head>
<body>

<?php require_once __DIR__ . '/partials/navbar.php'; ?>

<!-- Inyección dinámica del contenido -->
<?php require_once $contentView; ?>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<script src="<?= APP_URL ?>/public/js/toast.js"></script>
<script src="<?= APP_URL ?>/public/js/cart.js"></script>
<script src="<?= APP_URL ?>/public/js/public-navbar.js"></script>

<?php if (isset($script)): ?>
    <script src="<?= APP_URL ?>/public/js/<?= $script ?>.js"></script>
<?php endif; ?>


<script src="<?= APP_URL ?>/public/js/csrf.js"></script>
</body>
</html>