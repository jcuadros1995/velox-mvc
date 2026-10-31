<?php

/** * app/Views/settings/index.php
 * * * Variables recibidas desde SettingsController: * * @var array $settings * @var int|null $branchId */

$settings = $settings ?? [];

$settingsCanEdit =
        (bool) ($settingsCanEdit ?? false);

$settingsIsReadOnly =
        (bool) ($settingsIsReadOnly ?? true);

/** * Obtener una configuración. */
function setting( array $settings, string $group, string $key, $default = '' ) {
    return $settings[$group][$key] ?? $default;
}

/** * Escapar HTML. */
function e($value): string {
    return htmlspecialchars( (string) ($value ?? ''), ENT_QUOTES, 'UTF-8' );
}

/** * Configuración visual. */
$primaryColor = setting(
        $settings, 'branding', 'primary_color', '#4a238b' );
        $secondaryColor = setting( $settings, 'branding', 'secondary_color', '#6d3bc1' );
        $platformName = setting( $settings, 'branding', 'platform_name', 'Velox Platform' );
        $platformDescription = setting( $settings, 'branding', 'description', 'Plataforma de gestión empresarial' );
        $brandingLogo = setting( $settings, 'branding', 'logo', '' );
        $brandingFavicon = setting( $settings, 'branding', 'favicon', '' );
        $siteFont = setting( $settings, 'website', 'site_font', "'Chillax', 'Helvetica Neue', Arial" );
        $siteStatus = setting( $settings, 'website', 'site_status', 'online' );
        $siteName = setting( $settings, 'website', 'site_name', '' );
        $siteDescription = setting( $settings, 'website', 'site_description', '' );
        $siteLogo = setting( $settings, 'website', 'site_logo', '' );
        $siteFavicon = setting( $settings, 'website', 'site_favicon', '' );
        /** * Datos empresa. */
        $legalName = setting( $settings, 'company', 'legal_name', '' );
        $taxId = setting( $settings, 'company', 'tax_id', '' );
        $companyAddress = setting( $settings, 'company', 'address', '' );
        $companyEmail = setting( $settings, 'company', 'email', '' );
        /** * Facturación. */
        $currencySymbol = setting( $settings, 'billing', 'currency_symbol', '$' );
        $currencyCode = setting( $settings, 'billing', 'currency_code', 'COP' );
        $decimals = setting( $settings, 'billing', 'decimals', 0 );
        /** * Redes sociales. */
        $facebook = setting( $settings, 'website', 'social_facebook', '' );
        $instagram = setting( $settings, 'website', 'social_instagram', '' );
        $tiktok = setting( $settings, 'website', 'social_tiktok', '' );
        /** * Pasarelas. */
        $wompiMode = setting($settings, 'payments', 'wompi_mode', 'sandbox');
        $wompiStatus = (bool) setting($settings, 'payments', 'wompi_status', false);
        $wompiPublicKey = setting($settings, 'payments', 'wompi_public_key', '');
        $wompiPrivateKey = setting($settings, 'payments', 'wompi_private_key', '');
        $epaycoMode = setting($settings, 'payments', 'epayco_mode', 'sandbox');
        $epaycoStatus = (bool) setting($settings, 'payments', 'epayco_status', false);
        $epaycoPublicKey = setting($settings, 'payments', 'epayco_public_key', '');
        $epaycoPKey = setting($settings, 'payments', 'epayco_p_key', '');
        $payuMode = setting($settings, 'payments', 'payu_mode', 'sandbox');
        $payuStatus = (bool) setting($settings, 'payments', 'payu_status', false);
        $payuMerchantId = setting($settings, 'payments', 'payu_merchant_id', '');
        $payuApiKey = setting($settings, 'payments', 'payu_api_key', '');
        $mpMode = setting($settings, 'payments', 'mp_mode', 'sandbox');
        $mpStatus = (bool) setting($settings, 'payments', 'mp_status', false);
        $mpPublicKey = setting($settings, 'payments', 'mp_public_key', '');
        $mpAccessToken = setting($settings, 'payments', 'mp_access_token', '');
        $stripeMode = setting($settings, 'payments', 'stripe_mode', 'sandbox');
        $stripeStatus = (bool) setting($settings, 'payments', 'stripe_status', false);
        $stripePublicKey = setting($settings, 'payments', 'stripe_public_key', '');
        $stripeSecretKey = setting($settings, 'payments', 'stripe_secret_key', '');
        $openpayMode = setting($settings, 'payments', 'openpay_mode', 'sandbox');
        $openpayStatus = (bool) setting($settings, 'payments', 'openpay_status', false);
        $openpayId = setting($settings, 'payments', 'openpay_id', '');
        $openpayKey = setting($settings, 'payments', 'openpay_key', '');
        $addiMode = setting($settings, 'payments', 'addi_mode', 'sandbox');
        $addiStatus = (bool) setting($settings, 'payments', 'addi_status', false);
        $addiClientId = setting($settings, 'payments', 'addi_client_id', '');
        /** * Seguridad. */
        $maxAttempts = setting( $settings, 'security', 'max_attempts', 5 );
        $lockoutTime = setting( $settings, 'security', 'lockout_time', 15 );
        $sessionTimeout = setting( $settings, 'security', 'session_timeout', 60 );
        $passwordExpiry = setting( $settings, 'security', 'password_expiry', 90 );
        $forceLogout = (bool) setting( $settings, 'security', 'force_logout', false );
        /** * Correo SMTP. */
        $mailEnabled = (bool) setting($settings, 'mail', 'enabled', true);
        $mailProvider = setting($settings, 'mail', 'provider', 'gmail');
        $mailHost = setting($settings, 'mail', 'host', 'smtp.gmail.com');
        $mailPort = setting($settings, 'mail', 'port', 587);
        $mailEncryption = setting($settings, 'mail', 'encryption', 'tls');
        $mailUsername = setting($settings, 'mail', 'username', '');
        $mailPasswordConfigured = trim((string) setting($settings, 'mail', 'password', '')) !== '';
        $mailFromName = setting($settings, 'mail', 'from_name', 'Velox Restaurant');
        $mailFromEmail = setting($settings, 'mail', 'from_email', '');
        /** * URLs de assets. * * APP_URL debe existir en config/config.php. */
        $appUrl = defined('APP_URL') ? rtrim(APP_URL, '/') : '';
        $settingsAssetUrl = $appUrl . '/assets/img/settings/';
        $brandingLogoUrl = $brandingLogo ? $settingsAssetUrl . rawurlencode($brandingLogo) : '';
        $brandingFaviconUrl = $brandingFavicon ? $settingsAssetUrl . rawurlencode($brandingFavicon) : '';
        $siteLogoUrl = $siteLogo ? $settingsAssetUrl . rawurlencode($siteLogo) : '';
        $siteFaviconUrl = $siteFavicon ? $settingsAssetUrl . rawurlencode($siteFavicon) : '';

        ?>

<style>
    :root {
        --settings-primary: <?=e($primaryColor) ?>;
        --settings-secondary: <?=e($secondaryColor) ?>;
        --font-sans: <?= $siteFont ?>;
        --velox-font: var(--font-sans);
        --settings-font: var(--font-sans);
    }
</style>
<style>
    .settings-font-preview {
        font-family: var(--font-sans, var(--settings-font, 'Chillax', 'Helvetica Neue', Arial, sans-serif));
        font-size: 0.82rem;
        margin-top: 0.45rem;
        color: var(--settings-primary);
        font-weight: 600;
    }
</style>
<div class="settings-page">
    <!-- ========================================================= HEADER ========================================================== -->
    <?php

    $pageHeaderIcon =
            'fa-solid fa-gear';

    $pageHeaderEyebrow =
            'ADMINISTRACIÓN DE PLATAFORMA';

    $pageHeaderTitle =
            'Configuración del Sistema';

    $pageHeaderDescription =
            'Control total sobre la identidad, seguridad y pagos de la plataforma.';

    $pageHeaderBadge =
            'Configuración global';

    require __DIR__ .
            '/../admin/components/page-header.php';

    ?>
    <!-- ========================================================= CONTENIDO ========================================================== -->
    <div class="settings-layout">
        <!-- ===================================================== SIDEBAR TABS ====================================================== -->
        <aside class="settings-sidebar">
            <div class="settings-sidebar-title">
                <span>CONFIGURACIÓN</span>
                <i class="bi bi-sliders"></i>
            </div>
            <div class="nav nav-pills nav-settings-sidebar" id="settingsTabs" role="tablist" aria-orientation="vertical">
                <button class="nav-link active" id="tab-brand-tab" data-bs-toggle="pill" data-bs-target="#tab-brand" type="button" role="tab" aria-controls="tab-brand" aria-selected="true">
                    <span class="settings-tab-icon">
                        <i class="bi bi-palette2"></i>
                    </span>
                    <span class="settings-tab-text">
                        <strong>Branding y Logo</strong>
                        <small>Identidad visual</small>
                    </span>
                    <i class="bi bi-chevron-right settings-tab-arrow"></i>
                </button>
                <button class="nav-link" id="tab-web-tab" data-bs-toggle="pill" data-bs-target="#tab-web" type="button" role="tab" aria-controls="tab-web" aria-selected="false">
                    <span class="settings-tab-icon">
                        <i class="bi bi-window-stack"></i>
                    </span>
                    <span class="settings-tab-text"> <strong>Página Web</strong>
                        <small>SEO y apariencia</small>
                    </span>
                    <i class="bi bi-chevron-right settings-tab-arrow"></i>
                </button>
                <button class="nav-link" id="tab-billing-tab" data-bs-toggle="pill" data-bs-target="#tab-billing" type="button" role="tab" aria-controls="tab-billing" aria-selected="false">
                    <span class="settings-tab-icon">
                        <i class="bi bi-receipt"></i>
                    </span>
                    <span class="settings-tab-text"> <strong>Facturación y Social</strong>
                        <small>Empresa y redes</small>
                    </span> <i class="bi bi-chevron-right settings-tab-arrow"></i>
                </button>
                <button class="nav-link" id="tab-payments-tab" data-bs-toggle="pill" data-bs-target="#tab-payments" type="button" role="tab" aria-controls="tab-payments" aria-selected="false">
                    <span class="settings-tab-icon">
                        <i class="bi bi-credit-card-2-front"></i>
                    </span>
                    <span class="settings-tab-text">
                        <strong>Pasarelas de Pago</strong>
                        <small>Pagos y credenciales</small>
                    </span>
                    <i class="bi bi-chevron-right settings-tab-arrow"></i>
                </button>


                <button class="nav-link" id="tab-mail-tab" data-bs-toggle="pill" data-bs-target="#tab-mail" type="button" role="tab" aria-controls="tab-mail" aria-selected="false">
                    <span class="settings-tab-icon">
                        <i class="bi bi-envelope-paper"></i>
                    </span>
                    <span class="settings-tab-text">
                        <strong>Correo SMTP</strong>
                        <small>Correo y Notificaciones</small>
                    </span>
                    <i class="bi bi-chevron-right settings-tab-arrow"></i>
                </button>


                <button class="nav-link" id="tab-security-tab" data-bs-toggle="pill" data-bs-target="#tab-security" type="button" role="tab" aria-controls="tab-security" aria-selected="false">
                    <span class="settings-tab-icon">
                        <i class="bi bi-shield-lock"></i>
                    </span> <span class="settings-tab-text">
                        <strong>Seguridad</strong>
                        <small>Acceso y sesiones</small>
                    </span>
                    <i class="bi bi-chevron-right settings-tab-arrow"></i>
                </button>

            </div>
        </aside>
        <!-- ===================================================== TAB CONTENT ====================================================== -->
        <main class="settings-content">
            <form id="formSettingsMaster" method="POST" enctype="multipart/form-data" autocomplete="off">
                <?php if ($settingsIsReadOnly): ?>
                    <fieldset disabled class="settings-readonly-fieldset">
                <?php endif; ?>
                <div class="tab-content" id="settingsTabContent">
                    <!-- ================================================= BRANDING ================================================== -->
                    <div class="tab-pane fade show active" id="tab-brand" role="tabpanel"
                         aria-labelledby="tab-brand-tab" tabindex="0">
                        <div class="settings-section-heading">
                            <div> <span class="settings-section-kicker"> IDENTIDAD </span>
                                <h2> Branding y Logo </h2>
                                <p> Define la identidad visual que utilizará el panel administrativo. </p>
                            </div>
                            <div class="section-icon"> <i class="bi bi-palette2"></i> </div>
                        </div>
                        <div class="settings-card">
                            <div class="form-section-title"> <i class="bi bi-brush"></i> Identidad Visual </div>
                            <div class="row g-4">
                                <div class="col-lg-6"> <label class="form-label premium-label"
                                                              for="branding_platform_name"> Nombre de la plataforma </label> <input
                                        type="text" class="form-control premium-input" id="branding_platform_name"
                                        name="branding__platform_name" value="<?= e($platformName) ?>" maxlength="100"
                                        placeholder="<?= e($platformName) ?>">
                                    <div class="form-help"> Nombre mostrado en el panel administrativo. </div>
                                </div>
                                <div class="col-lg-3"> <label class="form-label premium-label"
                                                              for="branding_primary_color"> Color principal </label>
                                    <div class="color-input-wrapper"> <input type="color" class="premium-color-input"
                                                                             id="branding_primary_color" name="branding__primary_color"
                                                                             value="<?= e( preg_match( '/^#[0-9A-Fa-f]{6}$/', $primaryColor ) ? $primaryColor : '#4a238b' ) ?>">
                                        <input type="text" class="form-control premium-input color-text-input"
                                               id="branding_primary_color_text" value="<?= e($primaryColor) ?>"
                                               maxlength="7"> </div>
                                </div>
                                <div class="col-lg-3"> <label class="form-label premium-label"
                                                              for="branding_secondary_color"> Color secundario </label>
                                    <div class="color-input-wrapper"> <input type="color" class="premium-color-input"
                                                                             id="branding_secondary_color" name="branding__secondary_color"
                                                                             value="<?= e( preg_match( '/^#[0-9A-Fa-f]{6}$/', $secondaryColor ) ? $secondaryColor : '#6d3bc1' ) ?>">
                                        <input type="text" class="form-control premium-input color-text-input"
                                               id="branding_secondary_color_text" value="<?= e($secondaryColor) ?>"
                                               maxlength="7"> </div>
                                </div>
                                <div class="col-12"> <label class="form-label premium-label" for="branding_description">
                                        Slogan / Descripción </label> <textarea class="form-control premium-input"
                                                                                id="branding_description" name="branding__description" rows="3" maxlength="500"
                                                                                placeholder="Descripción corta de tu plataforma..."><?= e($platformDescription) ?></textarea>
                                </div>
                            </div>
                        </div> <!-- LOGOS -->
                        <div class="row g-4 mt-1">
                            <div class="col-lg-6">
                                <div class="settings-card h-100">
                                    <div class="form-section-title"> <i class="bi bi-image"></i> Logo del panel </div>
                                    <div class="logo-upload-box">
                                        <div class="logo-preview" id="brandingLogoPreview">
                                            <?php if ($brandingLogoUrl): ?> <img src="<?= e($brandingLogoUrl) ?>"
                                                                                 alt="Logo administrativo">
                                            <?php else: ?>
                                                <div class="empty-logo"> <i class="bi bi-image"></i> <span>Sin logo</span>
                                                </div>
                                            <?php endif; ?>
                                        </div>
                                        <div class="logo-upload-info"> <label for="branding_logo" class="upload-button">
                                                <i class="bi bi-cloud-arrow-up"></i> Seleccionar logo </label> <input
                                                type="file" class="d-none" id="branding_logo" name="branding__logo"
                                                accept="image/png,image/jpeg,image/webp">
                                            <div class="form-help mt-2"> PNG, JPG o WEBP. Máximo 2 MB. </div>
                                            <?php if ($brandingLogo): ?>
                                                <div class="current-file"> <i class="bi bi-check-circle-fill"></i>
                                                    <?= e($brandingLogo) ?>
                                                </div>
                                            <?php endif; ?>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <div class="col-lg-6">
                                <div class="settings-card h-100">
                                    <div class="form-section-title"> <i class="bi bi-browser-chrome"></i> Favicon </div>
                                    <div class="logo-upload-box">
                                        <div class="favicon-preview" id="brandingFaviconPreview">
                                            <?php if ($brandingFaviconUrl): ?> <img src="<?= e($brandingFaviconUrl) ?>"
                                                                                    alt="Favicon">
                                            <?php else: ?>
                                                <div class="empty-logo"> <i class="bi bi-app-indicator"></i> <span>Sin
                                                    favicon</span> </div>
                                            <?php endif; ?>
                                        </div>
                                        <div class="logo-upload-info"> <label for="branding_favicon"
                                                                              class="upload-button"> <i class="bi bi-cloud-arrow-up"></i> Seleccionar
                                                favicon </label> <input type="file" class="d-none" id="branding_favicon"
                                                                        name="branding__favicon" accept="image/png,image/x-icon,image/webp">
                                            <div class="form-help mt-2"> ICO, PNG o WEBP. Máximo 2 MB. </div>
                                            <?php if ($brandingFavicon): ?>
                                                <div class="current-file"> <i class="bi bi-check-circle-fill"></i>
                                                    <?= e($brandingFavicon) ?>
                                                </div>
                                            <?php endif; ?>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    <!-- ================================================= WEBSITE ================================================== -->
                    <div class="tab-pane fade" id="tab-web" role="tabpanel" aria-labelledby="tab-web-tab" tabindex="0">
                        <div class="settings-section-heading">
                            <div> <span class="settings-section-kicker"> PRESENCIA DIGITAL </span>
                                <h2> Página Web </h2>
                                <p> Configura el nombre, SEO, tipografía y estado de tu sitio público. </p>
                            </div>
                            <div class="section-icon"> <i class="bi bi-window-stack"></i> </div>
                        </div>
                        <div class="settings-card">
                            <div class="form-section-title"> <i class="bi bi-search"></i> Información SEO </div>
                            <div class="row g-4">
                                <div class="col-lg-6"> <label class="form-label premium-label" for="website_site_name">
                                        Nombre del sitio </label> <input type="text" class="form-control premium-input"
                                                                         id="website_site_name" name="website__site_name" value="<?= e($siteName) ?>"
                                                                         maxlength="150" placeholder="Mi sitio web"> </div>
                                <div class="col-lg-6"> <label class="form-label premium-label"
                                                              for="website_site_status"> Estado del sitio </label> <select
                                        class="form-select premium-input" id="website_site_status"
                                        name="website__site_status">
                                        <option value="online" <?=$siteStatus==='online' ? 'selected' : '' ?> > 🟢
                                            Online </option>
                                        <option value="maintenance" <?=$siteStatus==='maintenance' ? 'selected' : '' ?>
                                        > 🟠 Mantenimiento </option>
                                    </select> </div>
                                <div class="col-12"> <label class="form-label premium-label"
                                                            for="website_site_description"> Meta descripción </label> <textarea
                                        class="form-control premium-input" id="website_site_description"
                                        name="website__site_description" rows="4" maxlength="300"
                                        placeholder="Descripción que aparecerá en buscadores..."><?= e($siteDescription) ?></textarea>
                                    <div class="form-help"> Recomendado: entre 120 y 160 caracteres. </div>
                                </div>
                                <div class="col-lg-6"> <label class="form-label premium-label" for="website_site_font">
                                        Tipografía </label> <select class="form-select premium-input"
                                                                    id="website_site_font" name="website__site_font">
                                        <option value="'Plus Jakarta Sans', sans-serif"
                                            <?=$siteFont==="'Plus Jakarta Sans', sans-serif" ? 'selected' : '' ?> > Plus
                                            Jakarta Sans </option>
                                        <option value="'Inter', sans-serif" <?=$siteFont==="'Inter', sans-serif"
                                            ? 'selected' : '' ?> > Inter </option>
                                        <option value="'Chillax', 'Helvetica Neue', Arial" <?=$siteFont==="'Chillax', 'Helvetica Neue', Arial"
                                            ? 'selected' : '' ?> > Chillax </option>
                                    </select>
                                    <div class="settings-font-preview">Vista previa: Aa Bb Cc 123</div>
                                </div>
                            </div>
                        </div> <!-- WEB ASSETS -->
                        <div class="row g-4 mt-1">
                            <div class="col-lg-6">
                                <div class="settings-card h-100">
                                    <div class="form-section-title"> <i class="bi bi-image"></i> Logo del sitio </div>
                                    <div class="logo-upload-box">
                                        <div class="logo-preview" id="siteLogoPreview">
                                            <?php if ($siteLogoUrl): ?> <img src="<?= e($siteLogoUrl) ?>"
                                                                             alt="Logo del sitio">
                                            <?php else: ?>
                                                <div class="empty-logo"> <i class="bi bi-image"></i> <span>Sin logo</span>
                                                </div>
                                            <?php endif; ?>
                                        </div>
                                        <div class="logo-upload-info"> <label for="website_site_logo"
                                                                              class="upload-button"> <i class="bi bi-cloud-arrow-up"></i> Seleccionar
                                                logo </label> <input type="file" class="d-none" id="website_site_logo"
                                                                     name="website__site_logo" accept="image/png,image/jpeg,image/webp">
                                            <div class="form-help mt-2"> PNG, JPG o WEBP. Máximo 2 MB. </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <div class="col-lg-6">
                                <div class="settings-card h-100">
                                    <div class="form-section-title"> <i class="bi bi-browser-chrome"></i> Favicon del
                                        sitio </div>
                                    <div class="logo-upload-box">
                                        <div class="favicon-preview" id="siteFaviconPreview">
                                            <?php if ($siteFaviconUrl): ?> <img src="<?= e($siteFaviconUrl) ?>"
                                                                                alt="Favicon del sitio">
                                            <?php else: ?>
                                                <div class="empty-logo"> <i class="bi bi-app-indicator"></i> <span>Sin
                                                    favicon</span> </div>
                                            <?php endif; ?>
                                        </div>
                                        <div class="logo-upload-info"> <label for="website_site_favicon"
                                                                              class="upload-button"> <i class="bi bi-cloud-arrow-up"></i> Seleccionar
                                                favicon </label> <input type="file" class="d-none"
                                                                        id="website_site_favicon" name="website__site_favicon"
                                                                        accept="image/png,image/x-icon,image/webp">
                                            <div class="form-help mt-2"> ICO, PNG o WEBP. Máximo 2 MB. </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    <!-- ================================================= BILLING / SOCIAL ================================================== -->
                    <div class="tab-pane fade" id="tab-billing" role="tabpanel" aria-labelledby="tab-billing-tab"
                         tabindex="0">
                        <div class="settings-section-heading">
                            <div> <span class="settings-section-kicker"> INFORMACIÓN COMERCIAL </span>
                                <h2> Facturación y Social </h2>
                                <p> Configura los datos legales, moneda y canales sociales de la plataforma. </p>
                            </div>
                            <div class="section-icon"> <i class="bi bi-receipt"></i> </div>
                        </div> <!-- EMPRESA -->
                        <div class="settings-card">
                            <div class="form-section-title"> <i class="bi bi-building"></i> Información legal </div>
                            <div class="row g-4">
                                <div class="col-lg-6"> <label class="form-label premium-label" for="company_legal_name">
                                        Razón social </label> <input type="text" class="form-control premium-input"
                                                                     id="company_legal_name" name="company__legal_name" value="<?= e($legalName) ?>"
                                                                     maxlength="200" placeholder="GAD AGENCY SAS"> </div>
                                <div class="col-lg-6"> <label class="form-label premium-label" for="company_tax_id"> NIT
                                        / Identificación tributaria </label> <input type="text"
                                                                                    class="form-control premium-input" id="company_tax_id" name="company__tax_id"
                                                                                    value="<?= e($taxId) ?>" maxlength="50" placeholder="901962964-0"> </div>
                                <div class="col-lg-6"> <label class="form-label premium-label" for="company_address">
                                        Dirección </label> <input type="text" class="form-control premium-input"
                                                                  id="company_address" name="company__address" value="<?= e($companyAddress) ?>"
                                                                  maxlength="250" placeholder="Dirección comercial"> </div>
                                <div class="col-lg-6"> <label class="form-label premium-label" for="company_email">
                                        Correo empresarial </label> <input type="email"
                                                                           class="form-control premium-input" id="company_email" name="company__email"
                                                                           value="<?= e($companyEmail) ?>" maxlength="150"
                                                                           placeholder="contacto@empresa.com"> </div>
                            </div>
                        </div> <!-- MONEDA -->
                        <div class="settings-card mt-4">
                            <div class="form-section-title"> <i class="bi bi-currency-exchange"></i> Facturación </div>
                            <div class="row g-4">
                                <div class="col-md-4"> <label class="form-label premium-label"
                                                              for="billing_currency_code"> Código de moneda </label> <select
                                        class="form-select premium-input" id="billing_currency_code"
                                        name="billing__currency_code">
                                        <option value="COP" <?=$currencyCode==='COP' ? 'selected' : '' ?> > COP — Peso
                                            colombiano </option>
                                        <option value="USD" <?=$currencyCode==='USD' ? 'selected' : '' ?> > USD — Dólar
                                            estadounidense </option>
                                        <option value="EUR" <?=$currencyCode==='EUR' ? 'selected' : '' ?> > EUR — Euro
                                        </option>
                                        <option value="MXN" <?=$currencyCode==='MXN' ? 'selected' : '' ?> > MXN — Peso
                                            mexicano </option>
                                    </select> </div>
                                <div class="col-md-4"> <label class="form-label premium-label"
                                                              for="billing_currency_symbol"> Símbolo </label> <input type="text"
                                                                                                                     class="form-control premium-input" id="billing_currency_symbol"
                                                                                                                     name="billing__currency_symbol" value="<?= e($currencySymbol) ?>" maxlength="5">
                                </div>
                                <div class="col-md-4"> <label class="form-label premium-label" for="billing_decimals">
                                        Decimales </label> <select class="form-select premium-input"
                                                                   id="billing_decimals" name="billing__decimals">
                                        <option value="0" <?=(int) $decimals===0 ? 'selected' : '' ?> > 0 — $100.000
                                        </option>
                                        <option value="2" <?=(int) $decimals===2 ? 'selected' : '' ?> > 2 — $100.000,00
                                        </option>
                                    </select> </div>
                            </div>
                        </div> <!-- SOCIAL -->
                        <div class="settings-card mt-4">
                            <div class="form-section-title"> <i class="bi bi-share"></i> Redes sociales </div>
                            <div class="row g-4">
                                <div class="col-lg-4"> <label class="form-label premium-label" for="social_facebook"> <i
                                            class="bi bi-facebook me-1"></i> Facebook </label> <input type="url"
                                                                                                      class="form-control premium-input" id="social_facebook"
                                                                                                      name="website__social_facebook" value="<?= e($facebook) ?>"
                                                                                                      placeholder="https://facebook.com/..."> </div>
                                <div class="col-lg-4"> <label class="form-label premium-label" for="social_instagram">
                                        <i class="bi bi-instagram me-1"></i> Instagram </label> <input type="url"
                                                                                                       class="form-control premium-input" id="social_instagram"
                                                                                                       name="website__social_instagram" value="<?= e($instagram) ?>"
                                                                                                       placeholder="https://instagram.com/..."> </div>
                                <div class="col-lg-4"> <label class="form-label premium-label" for="social_tiktok"> <i
                                            class="bi bi-tiktok me-1"></i> TikTok </label> <input type="url"
                                                                                                  class="form-control premium-input" id="social_tiktok"
                                                                                                  name="website__social_tiktok" value="<?= e($tiktok) ?>"
                                                                                                  placeholder="https://tiktok.com/@..."> </div>
                            </div>
                        </div>
                    </div>
                    <!-- ================================================= PAYMENTS ================================================== -->
                    <div class="tab-pane fade" id="tab-payments" role="tabpanel" aria-labelledby="tab-payments-tab"
                         tabindex="0">
                        <div class="settings-section-heading">
                            <div> <span class="settings-section-kicker"> COMERCIO ELECTRÓNICO </span>
                                <h2> Pasarelas de Pago </h2>
                                <p> Administra las plataformas de pago y sus credenciales de integración. </p>
                            </div>
                            <div class="section-icon"> <i class="bi bi-credit-card-2-front"></i> </div>
                        </div>
                        <div class="payment-security-warning">
                            <div class="warning-icon"> <i class="bi bi-shield-lock-fill"></i> </div>
                            <div> <strong>Credenciales protegidas</strong>
                                <p> Las claves privadas no se muestran en pantalla. Si ya existe una credencial, déjala
                                    vacía para conservarla. </p>
                            </div>
                        </div> <!-- WOMPI -->
                        <div class="payment-card">
                            <div class="payment-card-header">
                                <div class="payment-brand">
                                    <div class="payment-logo wompi-logo"> WOMPI </div>
                                    <div>
                                        <h3>Wompi</h3> <span>Pagos online Colombia</span>
                                    </div>
                                </div> <label class="premium-switch"> <input type="hidden" name="payments__wompi_status"
                                                                             value="0"> <input type="checkbox" name="payments__wompi_status" value="1"
                                        <?=$wompiStatus ? 'checked' : '' ?> > <span class="switch-slider"></span> <span
                                        class="switch-label"> Activo </span> </label>
                            </div>
                            <div class="row g-4">
                                <div class="col-md-4"> <label class="form-label premium-label"> Ambiente </label>
                                    <select class="form-select premium-input" name="payments__wompi_mode">
                                        <option value="sandbox" <?=$wompiMode==='sandbox' ? 'selected' : '' ?> > Sandbox
                                        </option>
                                        <option value="production" <?=$wompiMode==='production' ? 'selected' : '' ?> >
                                            Producción </option>
                                    </select> </div>
                                <div class="col-md-4"> <label class="form-label premium-label"> Public Key </label>
                                    <input type="text" class="form-control premium-input"
                                           name="payments__wompi_public_key" value="<?= e($wompiPublicKey) ?>"
                                           placeholder="pub_test_..."> </div>
                                <div class="col-md-4"> <label class="form-label premium-label"> Private Key </label>
                                    <input type="password" class="form-control premium-input"
                                           name="payments__wompi_private_key" value=""
                                           placeholder="<?= $wompiPrivateKey ? '•••••••••••••••• configurada' : 'Private Key' ?>">
                                </div>
                            </div>
                        </div> <!-- EPAYCO -->
                        <div class="payment-card">
                            <div class="payment-card-header">
                                <div class="payment-brand">
                                    <div class="payment-logo epayco-logo"> ePayco </div>
                                    <div>
                                        <h3>ePayco</h3> <span>Pagos y recaudos</span>
                                    </div>
                                </div> <label class="premium-switch"> <input type="hidden"
                                                                             name="payments__epayco_status" value="0"> <input type="checkbox"
                                                                                                                              name="payments__epayco_status" value="1" <?=$epaycoStatus ? 'checked' : '' ?> >
                                    <span class="switch-slider"></span> <span class="switch-label"> Activo </span>
                                </label>
                            </div>
                            <div class="row g-4">
                                <div class="col-md-4"> <label class="form-label premium-label"> Ambiente </label>
                                    <select class="form-select premium-input" name="payments__epayco_mode">
                                        <option value="sandbox" <?=$epaycoMode==='sandbox' ? 'selected' : '' ?> >
                                            Sandbox </option>
                                        <option value="production" <?=$epaycoMode==='production' ? 'selected' : '' ?> >
                                            Producción </option>
                                    </select> </div>
                                <div class="col-md-4"> <label class="form-label premium-label"> P_CUST_ID </label>
                                    <input type="text" class="form-control premium-input"
                                           name="payments__epayco_public_key" value="<?= e($epaycoPublicKey) ?>"
                                           placeholder="Customer ID"> </div>
                                <div class="col-md-4"> <label class="form-label premium-label"> P_KEY </label> <input
                                        type="password" class="form-control premium-input" name="payments__epayco_p_key"
                                        value=""
                                        placeholder="<?= $epaycoPKey ? '•••••••••••••••• configurada' : 'P_KEY' ?>">
                                </div>
                            </div>
                        </div> <!-- PAYU -->
                        <div class="payment-card">
                            <div class="payment-card-header">
                                <div class="payment-brand">
                                    <div class="payment-logo payu-logo"> PayU </div>
                                    <div>
                                        <h3>PayU</h3> <span>Pagos electrónicos</span>
                                    </div>
                                </div> <label class="premium-switch"> <input type="hidden" name="payments__payu_status"
                                                                             value="0"> <input type="checkbox" name="payments__payu_status" value="1"
                                        <?=$payuStatus ? 'checked' : '' ?> > <span class="switch-slider"></span> <span
                                        class="switch-label"> Activo </span> </label>
                            </div>
                            <div class="row g-4">
                                <div class="col-md-4"> <label class="form-label premium-label"> Ambiente </label>
                                    <select class="form-select premium-input" name="payments__payu_mode">
                                        <option value="sandbox" <?=$payuMode==='sandbox' ? 'selected' : '' ?> > Sandbox
                                        </option>
                                        <option value="production" <?=$payuMode==='production' ? 'selected' : '' ?> >
                                            Producción </option>
                                    </select> </div>
                                <div class="col-md-4"> <label class="form-label premium-label"> Merchant ID </label>
                                    <input type="text" class="form-control premium-input"
                                           name="payments__payu_merchant_id" value="<?= e($payuMerchantId) ?>"
                                           placeholder="Merchant ID"> </div>
                                <div class="col-md-4"> <label class="form-label premium-label"> API Key </label> <input
                                        type="password" class="form-control premium-input" name="payments__payu_api_key"
                                        value=""
                                        placeholder="<?= $payuApiKey ? '•••••••••••••••• configurada' : 'API Key' ?>">
                                </div>
                            </div>
                        </div> <!-- MERCADO PAGO -->
                        <div class="payment-card">
                            <div class="payment-card-header">
                                <div class="payment-brand">
                                    <div class="payment-logo mercado-logo"> MP </div>
                                    <div>
                                        <h3>Mercado Pago</h3> <span>Pagos online</span>
                                    </div>
                                </div> <label class="premium-switch"> <input type="hidden" name="payments__mp_status"
                                                                             value="0"> <input type="checkbox" name="payments__mp_status" value="1"
                                        <?=$mpStatus ? 'checked' : '' ?> > <span class="switch-slider"></span> <span
                                        class="switch-label"> Activo </span> </label>
                            </div>
                            <div class="row g-4">
                                <div class="col-md-4"> <label class="form-label premium-label"> Ambiente </label>
                                    <select class="form-select premium-input" name="payments__mp_mode">
                                        <option value="sandbox" <?=$mpMode==='sandbox' ? 'selected' : '' ?> > Sandbox
                                        </option>
                                        <option value="production" <?=$mpMode==='production' ? 'selected' : '' ?> >
                                            Producción </option>
                                    </select> </div>
                                <div class="col-md-4"> <label class="form-label premium-label"> Public Key </label>
                                    <input type="text" class="form-control premium-input" name="payments__mp_public_key"
                                           value="<?= e($mpPublicKey) ?>" placeholder="APP_USR-..."> </div>
                                <div class="col-md-4"> <label class="form-label premium-label"> Access Token </label>
                                    <input type="password" class="form-control premium-input"
                                           name="payments__mp_access_token" value=""
                                           placeholder="<?= $mpAccessToken ? '•••••••••••••••• configurado' : 'Access Token' ?>">
                                </div>
                            </div>
                        </div> <!-- STRIPE -->
                        <div class="payment-card">
                            <div class="payment-card-header">
                                <div class="payment-brand">
                                    <div class="payment-logo stripe-logo"> stripe </div>
                                    <div>
                                        <h3>Stripe</h3> <span>Global payments</span>
                                    </div>
                                </div> <label class="premium-switch"> <input type="hidden"
                                                                             name="payments__stripe_status" value="0"> <input type="checkbox"
                                                                                                                              name="payments__stripe_status" value="1" <?=$stripeStatus ? 'checked' : '' ?> >
                                    <span class="switch-slider"></span> <span class="switch-label"> Activo </span>
                                </label>
                            </div>
                            <div class="row g-4">
                                <div class="col-md-4"> <label class="form-label premium-label"> Ambiente </label>
                                    <select class="form-select premium-input" name="payments__stripe_mode">
                                        <option value="sandbox" <?=$stripeMode==='sandbox' ? 'selected' : '' ?> >
                                            Sandbox </option>
                                        <option value="production" <?=$stripeMode==='production' ? 'selected' : '' ?> >
                                            Producción </option>
                                    </select> </div>
                                <div class="col-md-4"> <label class="form-label premium-label"> Publishable Key </label>
                                    <input type="text" class="form-control premium-input"
                                           name="payments__stripe_public_key" value="<?= e($stripePublicKey) ?>"
                                           placeholder="pk_test_..."> </div>
                                <div class="col-md-4"> <label class="form-label premium-label"> Secret Key </label>
                                    <input type="password" class="form-control premium-input"
                                           name="payments__stripe_secret_key" value=""
                                           placeholder="<?= $stripeSecretKey ? '•••••••••••••••• configurada' : 'sk_test_...' ?>">
                                </div>
                            </div>
                        </div> <!-- OPENPAY -->
                        <div class="payment-card">
                            <div class="payment-card-header">
                                <div class="payment-brand">
                                    <div class="payment-logo openpay-logo"> Openpay </div>
                                    <div>
                                        <h3>Openpay</h3> <span>Pagos online</span>
                                    </div>
                                </div> <label class="premium-switch"> <input type="hidden"
                                                                             name="payments__openpay_status" value="0"> <input type="checkbox"
                                                                                                                               name="payments__openpay_status" value="1" <?=$openpayStatus ? 'checked' : '' ?>
                                    > <span class="switch-slider"></span> <span class="switch-label"> Activo </span>
                                </label>
                            </div>
                            <div class="row g-4">
                                <div class="col-md-4"> <label class="form-label premium-label"> Ambiente </label>
                                    <select class="form-select premium-input" name="payments__openpay_mode">
                                        <option value="sandbox" <?=$openpayMode==='sandbox' ? 'selected' : '' ?> >
                                            Sandbox </option>
                                        <option value="production" <?=$openpayMode==='production' ? 'selected' : '' ?> >
                                            Producción </option>
                                    </select> </div>
                                <div class="col-md-4"> <label class="form-label premium-label"> Merchant ID </label>
                                    <input type="text" class="form-control premium-input" name="payments__openpay_id"
                                           value="<?= e($openpayId) ?>" placeholder="Merchant ID"> </div>
                                <div class="col-md-4"> <label class="form-label premium-label"> Private Key </label>
                                    <input type="password" class="form-control premium-input"
                                           name="payments__openpay_key" value=""
                                           placeholder="<?= $openpayKey ? '•••••••••••••••• configurada' : 'Private Key' ?>">
                                </div>
                            </div>
                        </div> <!-- ADDI -->
                        <div class="payment-card">
                            <div class="payment-card-header">
                                <div class="payment-brand">
                                    <div class="payment-logo addi-logo"> ADDI </div>
                                    <div>
                                        <h3>ADDI</h3> <span>Financiación de compras</span>
                                    </div>
                                </div> <label class="premium-switch"> <input type="hidden" name="payments__addi_status"
                                                                             value="0"> <input type="checkbox" name="payments__addi_status" value="1"
                                        <?=$addiStatus ? 'checked' : '' ?> > <span class="switch-slider"></span> <span
                                        class="switch-label"> Activo </span> </label>
                            </div>
                            <div class="row g-4">
                                <div class="col-md-4"> <label class="form-label premium-label"> Ambiente </label>
                                    <select class="form-select premium-input" name="payments__addi_mode">
                                        <option value="sandbox" <?=$addiMode==='sandbox' ? 'selected' : '' ?> > Sandbox
                                        </option>
                                        <option value="production" <?=$addiMode==='production' ? 'selected' : '' ?> >
                                            Producción </option>
                                    </select> </div>
                                <div class="col-md-8"> <label class="form-label premium-label"> Client ID </label>
                                    <input type="password" class="form-control premium-input"
                                           name="payments__addi_client_id" value=""
                                           placeholder="<?= $addiClientId ? '•••••••••••••••• configurado' : 'Client ID' ?>">
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- =====================================================
                         MAIL
                    ====================================================== -->

                    <div
                            class="tab-pane fade"
                            id="tab-mail"
                            role="tabpanel"
                            aria-labelledby="tab-mail-tab"
                            tabindex="0"
                    >
                        <div class="settings-section-heading">
                            <div>
                                <span class="settings-section-kicker">
                                    COMUNICACIONES
                                </span>
                                <h2>
                                    Correo electrónico
                                </h2>
                                <p>
                                    Configura el servidor SMTP utilizado por Velox
                                    para enviar reservas, notificaciones y mensajes
                                    del sistema.
                                </p>
                            </div>
                            <div class="section-icon">
                                <i class="bi bi-envelope-paper"></i>
                            </div>
                        </div>
                        <div class="security-info-banner">
                            <div class="security-info-icon">
                                <i class="bi bi-send-check"></i>
                            </div>
                            <div>
                                <strong>
                                    Servidor SMTP
                                </strong>
                                <p>
                                    Velox utiliza SMTP autenticado para garantizar
                                    que los correos sean enviados desde una cuenta
                                    autorizada.
                                </p>
                            </div>
                        </div>
                        <div class="settings-card">
                            <div class="form-section-title">
                                <i class="bi bi-server"></i>
                                Configuración del servidor
                            </div>
                            <div class="row g-4">
                                <div class="col-lg-4">
                                    <label class="form-label premium-label">
                                        Estado
                                    </label>
                                    <select
                                            class="form-select premium-input"
                                            name="mail__enabled"
                                    >
                                        <option
                                                value="1"
                                                <?= $mailEnabled ? 'selected' : '' ?>
                                        >
                                            Habilitado
                                        </option>
                                        <option
                                                value="0"
                                                <?= !$mailEnabled ? 'selected' : '' ?>
                                        >
                                            Deshabilitado
                                        </option>
                                    </select>
                                </div>


                                <div class="col-lg-4">
                                    <label class="form-label premium-label">
                                        Proveedor
                                    </label>
                                    <select
                                            class="form-select premium-input"
                                            name="mail__provider"
                                    >
                                        <option
                                                value="gmail"
                                                <?= $mailProvider === 'gmail'
                                                        ? 'selected'
                                                        : '' ?>
                                        >
                                            Gmail
                                        </option>
                                    </select>
                                </div>
                                <div class="col-lg-4">
                                    <label class="form-label premium-label">
                                        Servidor SMTP
                                    </label>
                                    <input
                                            type="text"
                                            class="form-control premium-input"
                                            name="mail__host"
                                            value="<?= e($mailHost) ?>"
                                    >
                                </div>
                                <div class="col-lg-4">
                                    <label class="form-label premium-label">
                                        Puerto
                                    </label>
                                    <input
                                            type="number"
                                            min="1"
                                            max="65535"
                                            class="form-control premium-input"
                                            name="mail__port"
                                            value="<?= e($mailPort) ?>"
                                    >
                                </div>
                                <div class="col-lg-4">
                                    <label class="form-label premium-label">
                                        Seguridad
                                    </label>
                                    <select
                                            class="form-select premium-input"
                                            name="mail__encryption"
                                    >
                                        <option
                                                value="tls"
                                                <?= $mailEncryption === 'tls'
                                                        ? 'selected'
                                                        : '' ?>
                                        >
                                            TLS
                                        </option>
                                        <option
                                                value="ssl"
                                                <?= $mailEncryption === 'ssl'
                                                        ? 'selected'
                                                        : '' ?>
                                        >
                                            SSL
                                        </option>
                                    </select>
                                </div>
                            </div>
                        </div>
                        <div class="settings-card mt-4">
                            <div class="form-section-title">
                                <i class="bi bi-person-badge"></i>
                                Autenticación SMTP
                            </div>
                            <div class="row g-4">
                                <div class="col-lg-6">
                                    <label class="form-label premium-label">
                                        Correo / usuario SMTP
                                    </label>
                                    <input
                                            type="email"
                                            class="form-control premium-input"
                                            name="mail__username"
                                            value="<?= e($mailUsername) ?>"
                                            maxlength="150"
                                            autocomplete="off"
                                    >
                                </div>
                                <div class="col-lg-6">
                                    <label class="form-label premium-label">
                                        Contraseña de aplicación
                                    </label>
                                    <input
                                            type="password"
                                            class="form-control premium-input"
                                            name="mail__password"
                                            value=""
                                            maxlength="255"
                                            autocomplete="new-password"
                                            placeholder="<?= $mailPasswordConfigured
                                                    ? '•••••••••••••••• configurada'
                                                    : 'Introduce la contraseña de aplicación' ?>"
                                    >
                                    <div class="form-help">

                                        Para Gmail utiliza una
                                        <strong>App Password</strong>,
                                        no la contraseña normal de la cuenta.

                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="settings-card mt-4">
                            <div class="form-section-title">
                                <i class="bi bi-person-vcard"></i>
                                Remitente
                            </div>
                            <div class="row g-4">
                                <div class="col-lg-6">
                                    <label class="form-label premium-label">
                                        Nombre del remitente
                                    </label>
                                    <input
                                            type="text"
                                            class="form-control premium-input"
                                            name="mail__from_name"
                                            value="<?= e($mailFromName) ?>"
                                            maxlength="150"
                                    >
                                </div>
                                <div class="col-lg-6">
                                    <label class="form-label premium-label">
                                        Correo del remitente
                                    </label>
                                    <input
                                            type="email"
                                            class="form-control premium-input"
                                            name="mail__from_email"
                                            value="<?= e($mailFromEmail) ?>"
                                            maxlength="150"
                                    >
                                </div>
                            </div>
                        </div>
                        <div class="settings-card mt-4">

                            <div class="form-section-title">
                                <i class="bi bi-send-check"></i>
                                Probar configuración
                            </div>
                            <div class="d-flex flex-column flex-md-row align-items-md-center justify-content-between gap-3">
                                <div>
                                    <strong class="d-block mb-1">
                                        Enviar correo de prueba
                                    </strong>
                                    <span class="text-muted small">
                                        Comprueba que las credenciales SMTP de Gmail
                                        funcionan correctamente.
                                    </span>
                                </div>
                            </div><br>

                            <div class="d-flex gap-2">
                                <input
                                        type="email"
                                        id="smtpTestEmail"
                                        class="form-control premium-input"
                                        placeholder="correo@ejemplo.com"
                                        style="min-width:260px;"
                                >
                                <button
                                        type="button"
                                        id="btnTestSmtp"
                                        class="btn btn-primary"
                                >
                                    <i class="bi bi-send me-2"></i>
                                    Probar conexión
                                </button>

                            </div>
                            <div
                                    id="smtpTestResult"
                                    class="mt-3"
                                    style="display:none;"
                            ></div>

                        </div>
                        <div class="security-danger-box mt-4">
                            <div class="danger-icon">
                                <i class="bi bi-shield-lock-fill"></i>
                            </div>
                            <div>
                                <strong>
                                    Credenciales protegidas
                                </strong>
                                <p>
                                    La contraseña SMTP nunca se mostrará en pantalla.
                                    Si dejas el campo vacío, Velox conservará la
                                    contraseña actualmente configurada.
                                </p>
                            </div>
                        </div>


                    </div>

                    <!-- ================================================= SECURITY ================================================== -->
                    <div class="tab-pane fade" id="tab-security" role="tabpanel" aria-labelledby="tab-security-tab"
                         tabindex="0">
                        <div class="settings-section-heading">
                            <div> <span class="settings-section-kicker"> PROTECCIÓN </span>
                                <h2> Seguridad </h2>
                                <p> Configura la protección contra ataques, sesiones y políticas de contraseña. </p>
                            </div>
                            <div class="section-icon"> <i class="bi bi-shield-lock"></i> </div>
                        </div>
                        <div class="security-info-banner">
                            <div class="security-info-icon"> <i class="bi bi-shield-check"></i> </div>
                            <div> <strong>Protección de acceso</strong>
                                <p> Estos valores controlan el comportamiento de autenticación de los usuarios. </p>
                            </div>
                        </div>
                        <div class="settings-card">
                            <div class="form-section-title"> <i class="bi bi-person-lock"></i> Protección contra fuerza
                                bruta </div>
                            <div class="row g-4">
                                <div class="col-lg-4"> <label class="form-label premium-label"
                                                              for="security_max_attempts"> Intentos máximos </label>
                                    <div class="input-with-suffix"> <input type="number" min="1" max="20"
                                                                           class="form-control premium-input" id="security_max_attempts"
                                                                           name="security__max_attempts" value="<?= e($maxAttempts) ?>"> <span>
                                            intentos </span> </div>
                                    <div class="form-help"> Cantidad de intentos antes del bloqueo. </div>
                                </div>
                                <div class="col-lg-4"> <label class="form-label premium-label"
                                                              for="security_lockout_time"> Tiempo de bloqueo </label>
                                    <div class="input-with-suffix"> <input type="number" min="1" max="1440"
                                                                           class="form-control premium-input" id="security_lockout_time"
                                                                           name="security__lockout_time" value="<?= e($lockoutTime) ?>"> <span> minutos
                                        </span> </div>
                                </div>
                                <div class="col-lg-4"> <label class="form-label premium-label"
                                                              for="security_session_timeout"> Tiempo de sesión </label>
                                    <div class="input-with-suffix"> <input type="number" min="5" max="1440"
                                                                           class="form-control premium-input" id="security_session_timeout"
                                                                           name="security__session_timeout" value="<?= e($sessionTimeout) ?>"> <span>
                                            minutos </span> </div>
                                </div>
                            </div>
                        </div>
                        <div class="settings-card mt-4">
                            <div class="form-section-title"> <i class="bi bi-key"></i> Política de contraseña </div>
                            <div class="row g-4">
                                <div class="col-lg-6"> <label class="form-label premium-label"
                                                              for="security_password_expiry"> Expiración de contraseña </label> <select
                                        class="form-select premium-input" id="security_password_expiry"
                                        name="security__password_expiry">
                                        <option value="0" <?=(int) $passwordExpiry===0 ? 'selected' : '' ?> > Nunca
                                        </option>
                                        <option value="30" <?=(int) $passwordExpiry===30 ? 'selected' : '' ?> > Cada 30
                                            días </option>
                                        <option value="60" <?=(int) $passwordExpiry===60 ? 'selected' : '' ?> > Cada 60
                                            días </option>
                                        <option value="90" <?=(int) $passwordExpiry===90 ? 'selected' : '' ?> > Cada 90
                                            días </option>
                                        <option value="180" <?=(int) $passwordExpiry===180 ? 'selected' : '' ?> > Cada
                                            180 días </option>
                                    </select> </div>
                                <div class="col-lg-6"> <label class="form-label premium-label"
                                                              for="security_force_logout"> Cierre global de sesiones </label> <select
                                        class="form-select premium-input" id="security_force_logout"
                                        name="security__force_logout">
                                        <option value="0" <?=!$forceLogout ? 'selected' : '' ?> > Desactivado </option>
                                        <option value="1" <?=$forceLogout ? 'selected' : '' ?> > Activado </option>
                                    </select>
                                    <div class="form-help"> Fuerza el cierre de sesiones cuando se aplique la política
                                        de seguridad. </div>
                                </div>
                            </div>
                        </div>
                        <div class="security-danger-box">
                            <div class="danger-icon"> <i class="bi bi-exclamation-triangle-fill"></i> </div>
                            <div> <strong> Configuración sensible </strong>
                                <p> Los cambios en seguridad pueden afectar inmediatamente el acceso de los usuarios.
                                    Verifica los valores antes de guardar. </p>
                            </div>
                        </div>
                    </div>
                </div>
                <!-- ===================================================== FOOTER SAVE ====================================================== -->
                <div class="settings-save-bar">
                    <div class="save-status">
                        <div class="save-status-icon">
                            <i class="bi bi-cloud-check"></i>
                        </div>
                        <div>
                            <strong>
                                Configuración de plataforma
                            </strong>
                            <span> Los cambios se guardarán
                                inmediatamente.
                            </span>
                        </div>
                    </div>
                    <?php if ($settingsCanEdit): ?>

                        <button
                                type="submit"
                                class="btn-save-settings"
                                id="btnSaveMaster"
                        >
                            <i class="bi bi-cloud-check-fill me-2"></i>
                            ACTUALIZAR PLATAFORMA
                        </button>

                    <?php else: ?>

                        <div class="settings-readonly-notice">
                            <i class="bi bi-eye me-2"></i>

                            <span>
                                Estás consultando la configuración general.
                                Solo el SuperAdmin puede realizar cambios.
                            </span>
                        </div>

                    <?php endif; ?>
                </div>
                        <?php if ($settingsIsReadOnly): ?>

                    </fieldset>

                <?php endif; ?>

            </form>
        </main>
    </div>
</div>
<!-- ============================================================= TOAST ============================================================== -->
<div class="settings-toast-container" id="settingsToastContainer"></div>
<script> (function () {'use strict'; /* ========================================================= HELPERS ========================================================== */ function showSettingsToast(type, title, message) {const container = document.getElementById('settingsToastContainer'); if (!container) {alert(message); return;} const icon = type === 'success' ? 'bi-check-circle-fill' : 'bi-exclamation-triangle-fill'; const toast = document.createElement('div'); toast.className = 'settings-toast settings-toast-' + type; toast.innerHTML = ` <div class="settings-toast-icon"> <i class="bi ${icon}"></i> </div> <div class="settings-toast-content"> <strong>${escapeHtml(title)}</strong> <span>${escapeHtml(message)}</span> </div> <button type="button" class="settings-toast-close" aria-label="Cerrar" > <i class="bi bi-x-lg"></i> </button> `; container.appendChild(toast); requestAnimationFrame(() => {toast.classList.add('show');}); const closeButton = toast.querySelector('.settings-toast-close'); closeButton.addEventListener('click', function () {removeToast(toast);}); setTimeout(() => {removeToast(toast);}, 5000);} function removeToast(toast) {if (!toast) {return;} toast.classList.remove('show'); setTimeout(() => {toast.remove();}, 300);} function escapeHtml(value) {const div = document.createElement('div'); div.textContent = value ?? ''; return div.innerHTML;} /* ========================================================= COLOR PICKERS ========================================================== */ function bindColorPicker(colorId, textId) {const color = document.getElementById(colorId); const text = document.getElementById(textId); if (!color || !text) {return;} color.addEventListener('input', function () {text.value = color.value;}); text.addEventListener('input', function () {const value = text.value.trim(); if (/^#[0-9A-Fa-f]{6}$/.test(value)) {color.value = value;} }); text.addEventListener('blur', function () {const value = text.value.trim(); if (!/^#[0-9A-Fa-f]{6}$/.test(value)) {text.value = color.value;} });} bindColorPicker('branding_primary_color', 'branding_primary_color_text'); bindColorPicker('branding_secondary_color', 'branding_secondary_color_text'); /* ========================================================= IMAGE PREVIEW ========================================================== */ function bindImagePreview(inputId, previewId) {const input = document.getElementById(inputId); const preview = document.getElementById(previewId); if (!input || !preview) {return;} input.addEventListener('change', function () {const file = input.files && input.files[0]; if (!file) {return;} const allowedExtensions = ['jpg','jpeg','png','webp','ico']; const extension = (file.name.split('.').pop() || '').toLowerCase(); const isImage = file.type.startsWith('image/') || allowedExtensions.includes(extension); if (!isImage || !allowedExtensions.includes(extension)) {showSettingsToast('error', 'Archivo no válido', 'Selecciona un PNG, JPG, WEBP o ICO.'); input.value = ''; return;} if (file.size <= 0) {showSettingsToast('error', 'Archivo vacío', 'El archivo seleccionado no contiene datos.'); input.value = ''; return;} if (file.size > 2 * 1024 * 1024) {showSettingsToast('error', 'Archivo demasiado grande', 'La imagen no puede superar los 2 MB.'); input.value = ''; return;} const reader = new FileReader(); reader.onload = function (event) {preview.innerHTML = ` <img src="${event.target.result}" alt="Vista previa" > `;}; reader.readAsDataURL(file);});} bindImagePreview('branding_logo', 'brandingLogoPreview'); bindImagePreview('branding_favicon', 'brandingFaviconPreview'); bindImagePreview('website_site_logo', 'siteLogoPreview'); bindImagePreview('website_site_favicon', 'siteFaviconPreview'); const fontSelect = document.getElementById('website_site_font'); const settingsPage = document.querySelector('.settings-page'); if (fontSelect && settingsPage) {fontSelect.addEventListener('change', function () {settingsPage.style.setProperty('--font-sans', fontSelect.value); settingsPage.style.setProperty('--settings-font', fontSelect.value);});} /* ========================================================= FORM SUBMIT ========================================================== */ const form = document.getElementById('formSettingsMaster'); const button = document.getElementById('btnSaveMaster'); if (!form || !button) {return;} form.addEventListener('submit', async function (event) {event.preventDefault(); const originalButtonHtml = button.innerHTML; button.disabled = true; button.innerHTML = ` <span class="spinner-border spinner-border-sm me-2" role="status" aria-hidden="true" ></span> SINCRONIZANDO... `; try {const formData = new FormData(form); /* * Los campos booleanos que no están marcados * deben llegar explícitamente como 0. * * Como usamos hidden inputs antes del checkbox, * FormData ya los manejará correctamente. */ const appUrl = <?= json_encode($appUrl, JSON_UNESCAPED_SLASHES) ?>; const endpoint = appUrl + '/api/settings/update'; const response = await fetch(endpoint, {method: 'POST', body: formData, credentials: 'same-origin', headers: {'X-Requested-With': 'XMLHttpRequest', 'Accept': 'application/json'}}); const json = await response.json(); if (!response.ok || !json.success) {throw new Error(json.message || 'No fue posible guardar la configuración.');} showSettingsToast('success', '¡Configuración actualizada!', json.message || 'Los cambios fueron guardados correctamente.'); /* * Recargamos después de un pequeño intervalo * para mostrar los valores actualizados. */ setTimeout(function () {window.location.reload();}, 1400);} catch (error) {console.error('[SETTINGS]', error); showSettingsToast('error', 'Error al guardar', error.message || 'No fue posible conectar con el servidor.');} finally {setTimeout(function () {button.disabled = false; button.innerHTML = originalButtonHtml;}, 1000);} });})();

    const btnTestSmtp =
        document.getElementById('btnTestSmtp');

    const smtpTestEmail =
        document.getElementById('smtpTestEmail');

    const smtpTestResult =
        document.getElementById('smtpTestResult');


    if (btnTestSmtp) {

        btnTestSmtp.addEventListener(
            'click',
            async () => {

                const email =
                    smtpTestEmail.value.trim();

                if (!email) {

                    showSmtpTestResult(
                        'error',
                        'Ingresa el correo donde deseas recibir la prueba.'
                    );

                    smtpTestEmail.focus();

                    return;
                }


                btnTestSmtp.disabled = true;

                btnTestSmtp.innerHTML = `
                <span
                    class="spinner-border spinner-border-sm me-2"
                ></span>
                Enviando...
            `;


                try {

                    const formData =
                        new FormData();

                    formData.append(
                        'email',
                        email
                    );


                    const response =
                        await fetch(
                            '/velox-mvc/api/admin/email/test',
                            {
                                method: 'POST',
                                body: formData
                            }
                        );


                    const data =
                        await response.json();


                    if (!data.success) {

                        throw new Error(
                            data.message ||
                            'No fue posible enviar el correo.'
                        );
                    }


                    showSmtpTestResult(
                        'success',
                        data.message
                    );

                } catch (error) {

                    console.error(
                        '[SMTP TEST]',
                        error
                    );

                    showSmtpTestResult(
                        'error',
                        error.message
                    );

                } finally {

                    btnTestSmtp.disabled = false;

                    btnTestSmtp.innerHTML = `
                    <i class="bi bi-send me-2"></i>
                    Probar conexión
                `;
                }
            }
        );
    }


    function showSmtpTestResult(
        type,
        message
    ) {

        if (!smtpTestResult) {
            return;
        }

        const isSuccess =
            type === 'success';


        smtpTestResult.style.display =
            'block';


        smtpTestResult.className =
            `mt-3 alert ${
                isSuccess
                    ? 'alert-success'
                    : 'alert-danger'
            }`;


        smtpTestResult.innerHTML = `
        <i class="bi ${
            isSuccess
                ? 'bi-check-circle'
                : 'bi-exclamation-triangle'
        } me-2"></i>
        ${message}
    `;
    }
</script>