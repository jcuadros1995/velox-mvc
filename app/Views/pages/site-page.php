<?php

/**
 * ============================================================
 * BURGER TOWN
 * PUBLIC - SITE PAGE
 * ============================================================
 */

$page = $publicPage ?? [];


/* ============================================================
   DATOS
   ============================================================ */

$title = trim(
        (string)(
                $page['h1']
                ?? $page['title']
                ?? 'Burger Town'
        )
);


$pageTitle = trim(
        (string)(
                $page['title']
                ?? $title
        )
);


$pageType = strtolower(
        trim(
                (string)(
                        $page['page_type']
                        ?? 'general'
                )
        )
);


$slug = trim(
        (string)(
                $page['slug']
                ?? ''
        )
);


$content = (string)(
        $page['content_html']
        ?? ''
);


/* ============================================================
   CONFIGURACIÓN VISUAL SEGÚN TIPO
   ============================================================ */

$pageConfig = [

        'general' => [
                'eyebrow' => 'BURGER TOWN',
                'icon' => 'fa-solid fa-file-lines',
                'label' => 'Información',
                'description' =>
                        'Conoce más sobre Burger Town y nuestra propuesta.',
        ],

        'contact' => [
                'eyebrow' => 'HABLEMOS',
                'icon' => 'fa-solid fa-comments',
                'label' => 'Contacto',
                'description' =>
                        'Estamos para ayudarte. Encuentra nuestros canales de atención.',
        ],

        'event' => [
                'eyebrow' => 'BURGER TOWN EVENTS',
                'icon' => 'fa-solid fa-champagne-glasses',
                'label' => 'Eventos',
                'description' =>
                        'Celebra tus momentos especiales con Burger Town.',
        ],

        'legal' => [
                'eyebrow' => 'INFORMACIÓN LEGAL',
                'icon' => 'fa-solid fa-scale-balanced',
                'label' => 'Información legal',
                'description' =>
                        'Consulta la información y condiciones de Burger Town.',
        ],

];


$config =
        $pageConfig[$pageType]
        ?? $pageConfig['general'];


/* ============================================================
   ESCAPE
   ============================================================ */

$esc = static function ($value): string {

    return htmlspecialchars(
            (string)$value,
            ENT_QUOTES,
            'UTF-8'
    );

};


/* ============================================================
   CLASE BODY
   ============================================================ */

$pageClass =
        'bt-site-page'
        . ' bt-site-page--'
        . preg_replace(
                '/[^a-z0-9_-]/',
                '',
                $pageType
        );

?>

<section
        class="<?= $esc($pageClass) ?>"
        data-page-type="<?= $esc($pageType) ?>"
        data-page-slug="<?= $esc($slug) ?>"
>


    <!-- ========================================================
         HERO
    ========================================================= -->

    <header class="bt-public-page-hero">

        <div class="bt-public-page-hero-bg"></div>


        <div class="bt-public-page-hero-decoration bt-public-page-hero-decoration-1"></div>

        <div class="bt-public-page-hero-decoration bt-public-page-hero-decoration-2"></div>


        <div class="bt-page-shell bt-public-page-hero-inner">


            <!-- Breadcrumb -->

            <nav
                    class="bt-public-breadcrumb"
                    aria-label="Navegación"
            >

                <a href="<?= $esc(APP_URL) ?>">

                    <i class="fa-solid fa-house"></i>

                    Inicio

                </a>


                <span>

                    <i class="fa-solid fa-chevron-right"></i>

                </span>


                <span class="current">

                    <?= $esc($pageTitle) ?>

                </span>

            </nav>


            <!-- Hero content -->

            <div class="bt-public-page-heading">


                <div class="bt-public-page-heading-icon">

                    <i class="<?= $esc($config['icon']) ?>"></i>

                </div>


                <div class="bt-public-page-heading-content">

                    <span class="bt-public-page-eyebrow">

                        <?= $esc($config['eyebrow']) ?>

                    </span>


                    <h1>

                        <?= $esc($title) ?>

                    </h1>


                    <p>

                        <?= $esc($config['description']) ?>

                    </p>

                </div>

            </div>


        </div>

    </header>


    <!-- ========================================================
         CONTENIDO
    ========================================================= -->

    <main class="bt-public-page-main">

        <div class="bt-page-shell">


            <div class="bt-public-page-layout">


                <!-- =================================================
                     CONTENIDO PRINCIPAL
                ================================================== -->

                <article class="bt-public-content-card">


                    <!-- Barra superior -->

                    <div class="bt-public-content-topbar">

                        <div class="bt-public-content-topbar-left">

                            <span class="bt-public-content-dot"></span>

                            <span>
                                Burger Town
                            </span>

                        </div>


                        <?php if ($pageType !== 'legal'): ?>

                            <span class="bt-public-content-tag">

                                <?= $esc($config['label']) ?>

                            </span>

                        <?php endif; ?>

                    </div>


                    <!-- Contenido -->

                    <div class="bt-rich-content">

                        <?php if (trim($content) !== ''): ?>

                            <?= $content ?>

                        <?php else: ?>

                            <div class="bt-public-content-empty">

                                <div class="bt-public-content-empty-icon">

                                    <i class="fa-solid fa-utensils"></i>

                                </div>


                                <h2>
                                    <?= $esc($title) ?>
                                </h2>


                                <p>
                                    Estamos preparando el contenido
                                    de esta sección.
                                </p>

                            </div>

                        <?php endif; ?>

                    </div>


                    <!-- Footer del contenido -->

                    <div class="bt-public-content-footer">

                        <div>

                            <i class="fa-solid fa-burger"></i>

                            <span>
                                Burger Town
                            </span>

                        </div>


                        <span>
                            Siempre listos para recibirte.
                        </span>

                    </div>


                </article>


                <!-- =================================================
                     SIDEBAR
                ================================================== -->

                <aside class="bt-public-page-sidebar">


                    <!-- CTA -->

                    <div class="bt-public-sidebar-card bt-public-sidebar-card--red">

                        <div class="bt-public-sidebar-pattern"></div>


                        <div class="bt-public-sidebar-icon">

                            <i class="fa-solid fa-burger"></i>

                        </div>


                        <span class="bt-public-sidebar-eyebrow">
                            BURGER TOWN
                        </span>


                        <h2>
                            ¿Se te antojó?
                        </h2>


                        <p>
                            Ven y disfruta nuestras hamburguesas,
                            productos y experiencias.
                        </p>


                        <a
                                href="<?= $esc(APP_URL) ?>/menu"
                                class="bt-public-sidebar-button"
                        >

                            Ver nuestro menú

                            <i class="fa-solid fa-arrow-right"></i>

                        </a>

                    </div>


                    <!-- Reserva -->

                    <div class="bt-public-sidebar-card">

                        <div class="bt-public-sidebar-card-icon">

                            <i class="fa-solid fa-calendar-check"></i>

                        </div>


                        <div>

                            <strong>
                                ¿Quieres celebrar?
                            </strong>

                            <p>
                                Reserva una mesa en tu sede
                                Burger Town favorita.
                            </p>

                        </div>


                        <a
                                href="<?= $esc(APP_URL) ?>/reservas"
                                class="bt-public-sidebar-link"
                        >

                            Reservar ahora

                            <i class="fa-solid fa-chevron-right"></i>

                        </a>

                    </div>


                    <!-- Sedes -->

                    <div class="bt-public-sidebar-card">

                        <div class="bt-public-sidebar-card-icon dark">

                            <i class="fa-solid fa-location-dot"></i>

                        </div>


                        <div>

                            <strong>
                                Encuentra tu sede
                            </strong>

                            <p>
                                Descubre la sede Burger Town
                                más cercana.
                            </p>

                        </div>


                        <a
                                href="<?= $esc(APP_URL) ?>/sedes"
                                class="bt-public-sidebar-link"
                        >

                            Ver sedes

                            <i class="fa-solid fa-chevron-right"></i>

                        </a>

                    </div>


                </aside>

            </div>

        </div>

    </main>


</section>