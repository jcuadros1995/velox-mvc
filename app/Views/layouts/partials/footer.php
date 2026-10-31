<?php
/**
 * app/Views/layouts/partials/footer.php
 *
 * COMPONENTES PÚBLICOS GLOBALES:
 *
 * 1. Sedes Burger Town
 * 2. Preguntas Frecuentes
 * 3. Footer
 */
?>


<!-- =========================================================
     SEDES BURGER TOWN
========================================================= -->

<section
        id="sedes-burgertown-footer"
        class="bt-locations"
>

    <div class="bt-locations-inner">

        <header class="bt-locations-header">

            <span class="bt-locations-eyebrow">
                ELIGE TU SEDE Y RESERVA
            </span>

            <h2 class="bt-locations-title">
                Sedes
                <span>Burger Town</span>
            </h2>

            <p class="bt-locations-description">
                Encuentra tu sede favorita, reserva tu mesa
                y prepárate para el mejor plan.
            </p>

        </header>


        <div class="bt-locations-viewport">

            <div
                    class="bt-locations-track"
                    data-footer-location-track
            >

                <article class="bt-location-card">

                    <div class="bt-location-image">

                        <div class="bt-location-image-placeholder">

                            <i class="fa-solid fa-spinner fa-spin"></i>

                        </div>

                    </div>

                    <div class="bt-location-overlay"></div>

                    <div class="bt-location-content">

                        <span class="bt-location-number">
                            01
                        </span>

                        <p class="bt-location-address">
                            Estamos consultando nuestras sedes.
                        </p>

                    </div>

                </article>

            </div>

        </div>


        <div class="bt-locations-controls">

            <button
                    type="button"
                    class="bt-location-arrow"
                    data-footer-location-prev
                    aria-label="Sede anterior"
            >
                <i class="fa-solid fa-arrow-left"></i>
            </button>


            <div
                    class="bt-location-dots"
                    data-footer-location-dots
            ></div>


            <button
                    type="button"
                    class="bt-location-arrow"
                    data-footer-location-next
                    aria-label="Sede siguiente"
            >
                <i class="fa-solid fa-arrow-right"></i>
            </button>

        </div>

    </div>

</section>

<!-- =========================================================
     PREGUNTAS FRECUENTES
========================================================= -->

<section
    id="preguntas-frecuentes"
    class="bt-faq"
>

    <div class="bt-faq-inner">


        <!-- =====================================================
             HEADER
        ====================================================== -->

        <header class="bt-faq-header">

            <span class="bt-faq-eyebrow">
                BURGER TOWN
            </span>


            <h2 class="bt-faq-title">

                <span>
                    🔥
                </span>

                Preguntas Frecuentes

                <span>
                    🔥
                </span>

            </h2>


            <p class="bt-faq-description">

                Todo lo que necesitas saber antes de venir
                por el plan.

            </p>

        </header>


        <!-- =====================================================
             FAQ
        ====================================================== -->

        <div
            class="bt-faq-list"
            data-faq
        >


            <!-- =================================================
                 01
            ================================================== -->

            <article class="bt-faq-item is-open">

                <button
                    type="button"
                    class="bt-faq-question"
                    aria-expanded="true"
                >

                    <span class="bt-faq-question-left">

                        <span class="bt-faq-number">
                            01
                        </span>


                        <span class="bt-faq-question-text">
                            ¿Cómo hago una reserva en Burger Town?
                        </span>

                    </span>


                    <span class="bt-faq-icon">

                        <i class="fa-solid fa-plus"></i>

                    </span>

                </button>


                <div
                    class="bt-faq-answer"
                    aria-hidden="false"
                >

                    <div class="bt-faq-answer-inner">

                        <p>

                            Puedes reservar directamente desde nuestra
                            página de Reservas. Elige tu sede en Bogotá,
                            selecciona fecha y hora y confirma. En segundos
                            quedas listo para llegar con mesa lista.

                        </p>

                    </div>

                </div>

            </article>


            <!-- =================================================
                 02
            ================================================== -->

            <article class="bt-faq-item">

                <button
                    type="button"
                    class="bt-faq-question"
                    aria-expanded="false"
                >

                    <span class="bt-faq-question-left">

                        <span class="bt-faq-number">
                            02
                        </span>


                        <span class="bt-faq-question-text">
                            ¿En qué sedes puedo reservar?
                        </span>

                    </span>


                    <span class="bt-faq-icon">

                        <i class="fa-solid fa-plus"></i>

                    </span>

                </button>


                <div
                    class="bt-faq-answer"
                    aria-hidden="true"
                >

                    <div class="bt-faq-answer-inner">

                        <p>

                            Puedes reservar en nuestras sedes de Bogotá:
                            Cedritos, Calle 26, Centro Internacional,
                            Quinta Camacho (Calle 70), Calle 99,
                            Las Aguas Centro y C.C. El Edén.
                            En la sección Sedes encuentras dirección,
                            horarios y cómo llegar.

                        </p>

                    </div>

                </div>

            </article>


            <!-- =================================================
                 03
            ================================================== -->

            <article class="bt-faq-item">

                <button
                    type="button"
                    class="bt-faq-question"
                    aria-expanded="false"
                >

                    <span class="bt-faq-question-left">

                        <span class="bt-faq-number">
                            03
                        </span>


                        <span class="bt-faq-question-text">
                            ¿Necesito reservar o puedo llegar sin reserva?
                        </span>

                    </span>


                    <span class="bt-faq-icon">

                        <i class="fa-solid fa-plus"></i>

                    </span>

                </button>


                <div
                    class="bt-faq-answer"
                    aria-hidden="true"
                >

                    <div class="bt-faq-answer-inner">

                        <p>

                            Puedes venir sin reserva, pero si quieres
                            asegurar mesa, especialmente en horas pico
                            y fines de semana, lo más recomendado es
                            reservar antes. Así llegas directo al plan.

                        </p>

                    </div>

                </div>

            </article>


            <!-- =================================================
                 04
            ================================================== -->

            <article class="bt-faq-item">

                <button
                    type="button"
                    class="bt-faq-question"
                    aria-expanded="false"
                >

                    <span class="bt-faq-question-left">

                        <span class="bt-faq-number">
                            04
                        </span>


                        <span class="bt-faq-question-text">
                            ¿Puedo reservar para eventos empresariales,
                            grupos o celebraciones?
                        </span>

                    </span>


                    <span class="bt-faq-icon">

                        <i class="fa-solid fa-plus"></i>

                    </span>

                </button>


                <div
                    class="bt-faq-answer"
                    aria-hidden="true"
                >

                    <div class="bt-faq-answer-inner">

                        <p>

                            Sí. Si es un grupo grande o una celebración,
                            te recomendamos reservar con anticipación.
                            Si necesitas un arreglo especial, revisa la
                            sección de Eventos o escríbenos para coordinar.

                        </p>

                    </div>

                </div>

            </article>


            <!-- =================================================
                 05
            ================================================== -->

            <article class="bt-faq-item">

                <button
                    type="button"
                    class="bt-faq-question"
                    aria-expanded="false"
                >

                    <span class="bt-faq-question-left">

                        <span class="bt-faq-number">
                            05
                        </span>


                        <span class="bt-faq-question-text">
                            ¿Los domicilios se hacen por la página web?
                        </span>

                    </span>


                    <span class="bt-faq-icon">

                        <i class="fa-solid fa-plus"></i>

                    </span>

                </button>


                <div
                    class="bt-faq-answer"
                    aria-hidden="true"
                >

                    <div class="bt-faq-answer-inner">

                        <p>

                            No. Los domicilios se realizan a través de
                            Rappi. En la web nos enfocamos en que reserves
                            tu mesa y vivas la experiencia en nuestras sedes.

                        </p>

                    </div>

                </div>

            </article>


        </div>

    </div>

</section>



<!-- =========================================================
     FOOTER BURGER TOWN
========================================================= -->

<footer class="bt-site-footer">

    <div class="bt-footer-inner">


        <!-- =====================================================
             NEWSLETTER
        ====================================================== -->

        <section class="bt-footer-newsletter">

            <span class="bt-footer-newsletter-eyebrow">
                BURGER TOWN
            </span>


            <h2>
                Novedades Burger Town
            </h2>


            <p>
                Suscríbete a nuestro boletín y recibe
                descuentos exclusivos.
            </p>


            <form
                class="bt-footer-newsletter-form"
                id="footerNewsletterForm"
            >

                <div class="bt-footer-newsletter-input">

                    <i class="fa-regular fa-envelope"></i>

                    <input
                        type="email"
                        name="email"
                        placeholder="Ingresa tu correo"
                        autocomplete="email"
                        required
                    >

                </div>


                <button type="submit">
                    Suscribirme
                </button>

            </form>

        </section>



        <!-- =====================================================
             FOOTER MAIN
        ====================================================== -->

        <div class="bt-footer-main">


            <!-- =================================================
                 BRAND
            ================================================== -->

            <div class="bt-footer-brand-column">

                <a
                    href="<?= APP_URL ?>/"
                    class="bt-footer-logo"
                    aria-label="Burger Town"
                >

                    <?php

                    $footerLogo =
                        SiteConfig::siteLogo();


                    $footerLogoUrl =
                        $footerLogo !== ''
                            ? rtrim(
                                APP_URL,
                                '/'
                            )
                            . '/assets/img/settings/'
                            . rawurlencode(
                                $footerLogo
                            )
                            : '';

                    ?>


                    <?php if ($footerLogoUrl !== ''): ?>

                        <img
                            src="<?= htmlspecialchars(
                                $footerLogoUrl,
                                ENT_QUOTES,
                                'UTF-8'
                            ) ?>"
                            alt="Burger Town"
                            loading="lazy"
                        >

                    <?php else: ?>

                        <span>
                            BURGER TOWN
                        </span>

                    <?php endif; ?>

                </a>


                <p class="bt-footer-brand-description">

                    Hamburguesas, buena música
                    y el mejor plan de Bogotá.

                </p>


                <!-- CONTACTO -->

                <div class="bt-footer-contact">

                    <a
                        href="https://www.google.com/maps/search/?api=1&query=Bogota%2C%20Colombia"
                        target="_blank"
                        rel="noopener noreferrer"
                    >

                        <i class="fa-solid fa-location-dot"></i>

                        Bogotá, Colombia

                    </a>


                    <a
                        href="mailto:contacto@burgertown.com.co"
                    >

                        <i class="fa-regular fa-envelope"></i>

                        contacto@burgertown.com.co

                    </a>


                    <a
                        href="tel:+573107163415"
                    >

                        <i class="fa-solid fa-phone"></i>

                        +57 310 716 3415

                    </a>

                </div>


                <!-- SOCIAL -->

                <div class="bt-footer-socials">

                    <a
                        href="https://www.facebook.com/burgertowncolombia/"
                        aria-label="Facebook"
                        target="_blank"
                    >
                        <i class="bi bi-facebook"></i>
                    </a>


                    <a
                        href="https://www.instagram.com/burger_town/"
                        aria-label="Instagram"
                        target="_blank"
                    >
                        <i class="bi bi-instagram"></i>
                    </a>


                    <a
                        href="https://www.tiktok.com/@burgertowncolombia"
                        aria-label="TikTok"
                        target="_blank"
                    >
                        <i class="bi bi-tiktok"></i>
                    </a>

                </div>

            </div>



            <!-- =================================================
                 ACCESOS
            ================================================== -->

            <div class="bt-footer-column">

                <h3>
                    Accesos Rápidos
                </h3>


                <a href="<?= APP_URL ?>/">
                    Inicio
                </a>


                <a href="<?= APP_URL ?>/menu">
                    Menú
                </a>


                <a href="<?= APP_URL ?>/reservas">
                    Reservas
                </a>


                <a href="<?= APP_URL ?>/sedes">
                    Sedes
                </a>


                <a href="<?= APP_URL ?>/pages/contact">
                    Contacto
                </a>


                <a href="<?= APP_URL ?>/pages/eventos-burger-town-bogota">
                    Eventos
                </a>


                <a href="#preguntas-frecuentes">
                    Preguntas Frecuentes
                </a>

            </div>



            <!-- =================================================
                 SEDES
            ================================================== -->

            <div class="bt-footer-column">

                <h3>
                    Sedes
                </h3>


                <div
                    class="bt-footer-branches"
                    data-footer-branches
                >

                    <span class="bt-footer-loading">
                        Cargando sedes...
                    </span>

                </div>

            </div>



            <!-- =================================================
                 LEGALES
            ================================================== -->

            <div class="bt-footer-column">

                <h3>
                    Legales
                </h3>


                <a href="<?= APP_URL ?>/pages/politicas-de-cookies">
                    Políticas de Cookies
                </a>


                <a href="<?= APP_URL ?>/pages/politica-de-privacidad">
                    Políticas de Privacidad
                </a>


                <a href="<?= APP_URL ?>/pages/terminos-y-condiciones">
                    Términos y Condiciones
                </a>

            </div>

        </div>



        <!-- =====================================================
             BOTTOM
        ====================================================== -->

        <div class="bt-footer-bottom">

            <span>
                Burger Town © <?= date('Y') ?>
                | Todos los derechos reservados
            </span>


            <span class="bt-footer-made">

                Hecho para
                <strong>
                    Burger Town
                </strong>

            </span>

        </div>

    </div>



    <!-- =====================================================
         BACK TO TOP
    ====================================================== -->

    <button
        type="button"
        class="bt-footer-back-top"
        id="btBackToTop"
        aria-label="Volver arriba"
    >

        <i class="fa-solid fa-arrow-up"></i>

    </button>

</footer>

