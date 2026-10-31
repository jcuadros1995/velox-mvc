<?php
/**
 * app/Views/menu/index.php
 */
?>

<div class="bt-menu-page">

    <!-- =========================================================
         HERO
    ========================================================== -->

    <section class="bt-menu-hero">

        <div class="bt-menu-hero-glow"></div>

        <div class="bt-menu-hero-content">

            <span class="bt-menu-eyebrow">
                BURGER TOWN
            </span>

            <h1 class="bt-menu-title">
                The best
                <span>menu in town</span>
            </h1>

            <p class="bt-menu-description">
                Lo que hay aquí te va a volar la cabeza.
            </p>

            <div class="bt-menu-hero-actions">

                <a
                    href="#menuPdf"
                    class="bt-menu-primary-button"
                >
                    <span>
                        Ver menú
                    </span>

                    <i class="fa-solid fa-arrow-down"></i>
                </a>


                <a
                    href="<?= APP_URL ?>/reservas"
                    class="bt-menu-secondary-button"
                >
                    <i class="fa-solid fa-calendar-check"></i>

                    Reservar mesa
                </a>

            </div>

        </div>

    </section>


    <!-- =========================================================
         PDF MENU
    ========================================================== -->

    <main
        id="menuPdf"
        class="bt-menu-document-section"
    >

        <div class="bt-menu-document-header">

            <div>

                <span class="bt-menu-document-eyebrow">
                    CARTA OFICIAL
                </span>

                <h2>
                    Nuestro menú
                </h2>

                <p>
                    Descubre todo lo que tenemos preparado
                    para ti.
                </p>

            </div>


            <a
                href="<?= APP_URL ?>/assets/docs/menu-burguer-town.pdf"
                target="_blank"
                rel="noopener noreferrer"
                class="bt-menu-pdf-button"
            >

                <i class="fa-solid fa-up-right-from-square"></i>

                Abrir menú completo

            </a>

        </div>


        <!-- =====================================================
             VISOR
        ====================================================== -->

        <div class="bt-menu-pdf-card">

            <div class="bt-menu-pdf-toolbar">

                <span class="bt-menu-pdf-status">

                    <span class="bt-menu-pdf-status-dot"></span>

                    Menú Burger Town

                </span>


                <span class="bt-menu-pdf-pages">
                    13 páginas
                </span>

            </div>


            <div class="bt-menu-pdf-viewer">

                <iframe
                    src="<?= APP_URL ?>/assets/docs/menu-burguer-town.pdf#toolbar=0&navpanes=0&scrollbar=1"
                    title="Menú Burger Town"
                    loading="lazy"
                ></iframe>

            </div>

        </div>

    </main>


    <!-- =========================================================
         CTA
    ========================================================== -->

    <section class="bt-menu-cta">

        <div class="bt-menu-cta-glow"></div>

        <div class="bt-menu-cta-content">

            <span class="bt-menu-cta-eyebrow">
                ¿YA SABES QUÉ VAS A PEDIR?
            </span>

            <h2>
                Ahora solo falta
                <span>elegir el plan.</span>
            </h2>

            <p>
                Reserva tu mesa y disfruta Burger Town
                en tu sede favorita.
            </p>

            <a
                href="<?= APP_URL ?>/reservas"
                class="bt-menu-cta-button"
            >

                <span>
                    Reservar mesa
                </span>

                <i class="fa-solid fa-arrow-right"></i>

            </a>

        </div>

    </section>

</div>