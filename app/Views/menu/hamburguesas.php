<?php
/**
 * app/Views/menu/hamburguesas.php
 */
?>

<div class="bt-burgers-page">


    <!-- =========================================================
         HERO
    ========================================================== -->

    <section class="bt-burgers-hero">

        <div class="bt-burgers-hero-bg">

            <img
                src="<?= APP_URL ?>/assets/img/public/REQUISICION_WEB_BURGER_MASTER_21.webp"
                alt="Burger Town Hamburguesas"
                draggable="false"
            >

            <div class="bt-burgers-hero-overlay"></div>

        </div>


        <div class="bt-burgers-hero-glow"></div>


        <div class="bt-burgers-hero-content">

            <span class="bt-burgers-eyebrow">

                BURGER TOWN
                <span class="bt-burgers-dot"></span>
                BOGOTÁ

            </span>


            <h1 class="bt-burgers-title">

                Hamburguesas

                <span>
                    hechas para el plan.
                </span>

            </h1>


            <p class="bt-burgers-hero-description">

                Hamburguesas artesanales, recetas de la casa
                y combinaciones que convierten cualquier salida
                en un buen plan.

            </p>


            <div class="bt-burgers-hero-actions">

                <a
                    href="#burgers-creacion"
                    class="bt-burgers-primary-button"
                >

                    Explorar hamburguesas

                    <i class="fa-solid fa-arrow-down"></i>

                </a>


                <a
                    href="<?= APP_URL ?>/reservas"
                    class="bt-burgers-secondary-button"
                >

                    <i class="fa-solid fa-calendar-check"></i>

                    Reservar mesa

                </a>

            </div>

        </div>

    </section>


    <!-- =========================================================
         INTRO
    ========================================================== -->

    <section class="bt-burgers-intro">

        <div class="bt-burgers-container">

            <div class="bt-burgers-intro-grid">


                <div class="bt-burgers-intro-copy">

                    <span class="bt-burgers-section-eyebrow">
                        NUESTRA CARTA
                    </span>


                    <h2>

                        Más que una
                        <span>hamburguesa.</span>

                    </h2>


                    <p>

                        En Burger Town construimos nuestras hamburguesas
                        alrededor del sabor, el contraste y ese punto
                        que hace que quieras volver por otra.

                    </p>


                    <div class="bt-burgers-feature-list">

                        <div>

                            <i class="fa-solid fa-fire"></i>

                            <span>
                                Recetas de nuestra creación
                            </span>

                        </div>


                        <div>

                            <i class="fa-solid fa-burger"></i>

                            <span>
                                Carne 100% Angus premium
                            </span>

                        </div>


                        <div>

                            <i class="fa-solid fa-bread-slice"></i>

                            <span>
                                Pan brioche de papa
                            </span>

                        </div>

                    </div>

                </div>


                <div class="bt-burgers-intro-card">

                    <span class="bt-burgers-intro-card-label">
                        BURGER TOWN
                    </span>


                    <strong>
                        El sabor
                        <span>manda.</span>
                    </strong>


                    <p>
                        Elige tu burger, elige tu combo
                        y prepárate para el plan.
                    </p>

                </div>

            </div>

        </div>

    </section>


    <!-- =========================================================
         NAVEGACIÓN INTERNA
    ========================================================== -->

    <section class="bt-burgers-nav-section">

        <div class="bt-burgers-container">

            <div class="bt-burgers-category-nav">

                <a
                    href="#burgers-creacion"
                    class="is-active"
                >
                    De nuestra creación
                </a>


                <a
                    href="#burgers-clasicas"
                >
                    Burgers clásicas
                </a>


                <a
                    href="#burger-notes"
                >
                    Nuestra recomendación
                </a>

            </div>

        </div>

    </section>


    <!-- =========================================================
         BURGERS DE NUESTRA CREACIÓN
    ========================================================== -->

    <section
        id="burgers-creacion"
        class="bt-burgers-products-section"
    >

        <div class="bt-burgers-container">


            <header class="bt-burgers-section-header">

                <span class="bt-burgers-section-eyebrow">
                    BURGER MASTER
                </span>


                <h2>
                    Burgers de
                    <span>nuestra creación</span>
                </h2>


                <p>
                    Las recetas que representan la esencia
                    creativa de Burger Town.
                </p>

            </header>


            <div class="bt-burgers-featured-grid">


                <!-- =================================================
                     SEXY TOWN
                ================================================== -->

                <article class="bt-burger-featured-card">

                    <div class="bt-burger-featured-top">

                        <span class="bt-burger-index">
                            01
                        </span>


                        <span class="bt-burger-badge">
                            BURGER MASTER
                        </span>

                    </div>


                    <div class="bt-burger-featured-icon">

                        <i class="fa-solid fa-burger"></i>

                    </div>


                    <h3>
                        Sexy Town
                    </h3>


                    <p>

                        Una de las burgers que representa
                        la propuesta creativa de Burger Town.

                    </p>


                    <div class="bt-burger-price-row">

                        <div>

                            <small>
                                SOLA
                            </small>

                            <strong>
                                $39.500
                            </strong>

                        </div>


                        <div>

                            <small>
                                EN COMBO
                            </small>

                            <strong>
                                $45.500
                            </strong>

                        </div>

                    </div>


                    <a
                        href="<?= APP_URL ?>/reservas"
                        class="bt-burger-card-button"
                    >

                        Reservar mesa

                        <i class="fa-solid fa-arrow-right"></i>

                    </a>

                </article>


                <!-- =================================================
                     LUCKY TOWN
                ================================================== -->

                <article class="bt-burger-featured-card featured">

                    <div class="bt-burger-featured-top">

                        <span class="bt-burger-index">
                            02
                        </span>


                        <span class="bt-burger-badge">
                            FAVORITA
                        </span>

                    </div>


                    <div class="bt-burger-featured-icon">

                        <i class="fa-solid fa-fire"></i>

                    </div>


                    <h3>
                        Lucky Town
                    </h3>


                    <p>

                        Una creación de Burger Town
                        pensada para quienes quieren
                        una burger con personalidad.

                    </p>


                    <div class="bt-burger-price-row">

                        <div>

                            <small>
                                SOLA
                            </small>

                            <strong>
                                $39.500
                            </strong>

                        </div>


                        <div>

                            <small>
                                EN COMBO
                            </small>

                            <strong>
                                $45.500
                            </strong>

                        </div>

                    </div>


                    <a
                        href="<?= APP_URL ?>/reservas"
                        class="bt-burger-card-button"
                    >

                        Reservar mesa

                        <i class="fa-solid fa-arrow-right"></i>

                    </a>

                </article>


                <!-- =================================================
                     VICTORY TOWN
                ================================================== -->

                <article class="bt-burger-featured-card">

                    <div class="bt-burger-featured-top">

                        <span class="bt-burger-index">
                            03
                        </span>


                        <span class="bt-burger-badge">
                            ESPECIAL
                        </span>

                    </div>


                    <div class="bt-burger-featured-icon">

                        <i class="fa-solid fa-crown"></i>

                    </div>


                    <h3>
                        Victory Town
                    </h3>


                    <p>

                        Parte de las creaciones especiales
                        de Burger Town y su recorrido en
                        Burger Master.

                    </p>


                    <div class="bt-burger-price-row">

                        <div>

                            <small>
                                SOLA
                            </small>

                            <strong>
                                $39.500
                            </strong>

                        </div>


                        <div>

                            <small>
                                EN COMBO
                            </small>

                            <strong>
                                $45.500
                            </strong>

                        </div>

                    </div>


                    <a
                        href="<?= APP_URL ?>/reservas"
                        class="bt-burger-card-button"
                    >

                        Reservar mesa

                        <i class="fa-solid fa-arrow-right"></i>

                    </a>

                </article>

            </div>


        </div>

    </section>


    <!-- =========================================================
         CLÁSICAS
    ========================================================== -->

    <section
        id="burgers-clasicas"
        class="bt-burgers-classic-section"
    >

        <div class="bt-burgers-container">


            <header class="bt-burgers-section-header">

                <span class="bt-burgers-section-eyebrow">
                    CLÁSICAS
                </span>


                <h2>
                    Nuestras
                    <span>burgers clásicas</span>
                </h2>


                <p>
                    Las favoritas que puedes pedir solas
                    o convertir en combo.
                </p>

            </header>


            <div class="bt-burgers-classic-grid">


                <!-- 01 -->

                <article class="bt-burger-classic-card">

                    <div class="bt-burger-classic-header">

                        <span>
                            01
                        </span>

                        <h3>
                            Queso Tocineta
                        </h3>

                    </div>


                    <p>
                        Queso cheddar, tocineta crocante
                        y verduras frescas.
                    </p>


                    <div class="bt-burger-classic-prices">

                        <span>

                            <small>
                                SOLA
                            </small>

                            $36.500

                        </span>


                        <span>

                            <small>
                                COMBO
                            </small>

                            $41.900

                        </span>

                    </div>

                </article>


                <!-- 02 -->

                <article class="bt-burger-classic-card">

                    <div class="bt-burger-classic-header">

                        <span>
                            02
                        </span>

                        <h3>
                            ExtraCheese
                        </h3>

                    </div>


                    <p>
                        Queso asado crocante, mozzarella,
                        cheddar, verduras frescas y
                        mayonesa de la casa.
                    </p>


                    <div class="bt-burger-classic-prices">

                        <span>

                            <small>
                                SOLA
                            </small>

                            $36.900

                        </span>


                        <span>

                            <small>
                                COMBO
                            </small>

                            $42.500

                        </span>

                    </div>

                </article>


                <!-- 03 -->

                <article class="bt-burger-classic-card">

                    <div class="bt-burger-classic-header">

                        <span>
                            03
                        </span>

                        <h3>
                            4x4
                        </h3>

                    </div>


                    <p>
                        Doble carne madura 100% de res,
                        doble tocineta crocante, doble queso
                        cheddar y verduras frescas.
                    </p>


                    <div class="bt-burger-classic-prices">

                        <span>

                            <small>
                                SOLA
                            </small>

                            $45.500

                        </span>


                        <span>

                            <small>
                                COMBO
                            </small>

                            $50.900

                        </span>

                    </div>

                </article>


                <!-- 04 -->

                <article class="bt-burger-classic-card">

                    <div class="bt-burger-classic-header">

                        <span>
                            04
                        </span>

                        <h3>
                            Padrísima
                        </h3>

                    </div>


                    <p>
                        Nachos, frijol refrito, mozzarella
                        y pico de gallo bañado en guacamole.
                    </p>


                    <div class="bt-burger-classic-prices">

                        <span>

                            <small>
                                SOLA
                            </small>

                            $38.200

                        </span>


                        <span>

                            <small>
                                COMBO
                            </small>

                            $44.900

                        </span>

                    </div>

                </article>


                <!-- 05 -->

                <article class="bt-burger-classic-card">

                    <div class="bt-burger-classic-header">

                        <span>
                            05
                        </span>

                        <h3>
                            Gaucha
                        </h3>

                    </div>


                    <p>
                        Chorizo argentino a la plancha,
                        chimichurri de la casa, cebolla roja
                        y queso mozzarella.
                    </p>


                    <div class="bt-burger-classic-prices">

                        <span>

                            <small>
                                SOLA
                            </small>

                            $35.900

                        </span>


                        <span>

                            <small>
                                COMBO
                            </small>

                            $41.500

                        </span>

                    </div>

                </article>


                <!-- 06 -->

                <article class="bt-burger-classic-card">

                    <div class="bt-burger-classic-header">

                        <span>
                            06
                        </span>

                        <h3>
                            Hawai Town
                        </h3>

                    </div>


                    <p>
                        Piña caramelizada en panela,
                        tocineta crocante ahumada
                        y queso mozzarella.
                    </p>


                    <div class="bt-burger-classic-prices">

                        <span>

                            <small>
                                SOLA
                            </small>

                            $34.500

                        </span>


                        <span>

                            <small>
                                COMBO
                            </small>

                            $40.900

                        </span>

                    </div>

                </article>


                <!-- 07 -->

                <article class="bt-burger-classic-card">

                    <div class="bt-burger-classic-header">

                        <span>
                            07
                        </span>

                        <h3>
                            Heinz Town
                        </h3>

                    </div>


                    <p>
                        Pepinillos agridulces, cebolla crispy,
                        tocineta crocante, queso Philadelphia
                        y salsa de miel mostaza.
                    </p>


                    <div class="bt-burger-classic-prices">

                        <span>

                            <small>
                                SOLA
                            </small>

                            $38.000

                        </span>


                        <span>

                            <small>
                                COMBO
                            </small>

                            $43.500

                        </span>

                    </div>

                </article>


                <!-- 08 -->

                <article class="bt-burger-classic-card">

                    <div class="bt-burger-classic-header">

                        <span>
                            08
                        </span>

                        <h3>
                            Napolitown
                        </h3>

                    </div>


                    <p>
                        Pepperoni, queso mozzarella,
                        tomate, albahaca fresca
                        y salsa napolitana.
                    </p>


                    <div class="bt-burger-classic-prices">

                        <span>

                            <small>
                                SOLA
                            </small>

                            $34.000

                        </span>


                        <span>

                            <small>
                                COMBO
                            </small>

                            $39.900

                        </span>

                    </div>

                </article>


                <!-- 09 -->

                <article class="bt-burger-classic-card">

                    <div class="bt-burger-classic-header">

                        <span>
                            09
                        </span>

                        <h3>
                            Streetown
                        </h3>

                    </div>


                    <p>
                        Tocineta ahumada, queso rallado,
                        maíz caramelizado, papas fósforo
                        y salsa.
                    </p>


                    <div class="bt-burger-classic-prices">

                        <span>

                            <small>
                                SOLA
                            </small>

                            $35.900

                        </span>


                        <span>

                            <small>
                                COMBO
                            </small>

                            $41.500

                        </span>

                    </div>

                </article>


                <!-- 10 -->

                <article class="bt-burger-classic-card">

                    <div class="bt-burger-classic-header">

                        <span>
                            10
                        </span>

                        <h3>
                            Falafel
                        </h3>

                    </div>


                    <p>
                        Carne de garbanzos con especias,
                        pepino, tomate y queso crema
                        con hierbabuena.
                    </p>


                    <div class="bt-burger-classic-prices">

                        <span>

                            <small>
                                SOLA
                            </small>

                            $30.200

                        </span>


                        <span>

                            <small>
                                COMBO
                            </small>

                            $36.500

                        </span>

                    </div>

                </article>


                <!-- 11 -->

                <article class="bt-burger-classic-card">

                    <div class="bt-burger-classic-header">

                        <span>
                            11
                        </span>

                        <h3>
                            Chicken Town
                        </h3>

                    </div>


                    <p>
                        Pollo a la plancha, queso cheddar,
                        tocineta, cebolla crispy, miel mostaza
                        y vegetales frescos.
                    </p>


                    <div class="bt-burger-classic-prices">

                        <span>

                            <small>
                                SOLA
                            </small>

                            $33.500

                        </span>


                        <span>

                            <small>
                                COMBO
                            </small>

                            $36.500

                        </span>

                    </div>

                </article>


                <!-- 12 -->

                <article class="bt-burger-classic-card">

                    <div class="bt-burger-classic-header">

                        <span>
                            12
                        </span>

                        <h3>
                            Queso Cheddar
                        </h3>

                    </div>


                    <p>
                        Queso cheddar y verduras frescas.
                    </p>


                    <div class="bt-burger-classic-prices">

                        <span>

                            <small>
                                SOLA
                            </small>

                            $32.900

                        </span>


                        <span>

                            <small>
                                COMBO
                            </small>

                            $38.500

                        </span>

                    </div>

                </article>


                <!-- 13 -->

                <article class="bt-burger-classic-card">

                    <div class="bt-burger-classic-header">

                        <span>
                            13
                        </span>

                        <h3>
                            Sencilla
                        </h3>

                    </div>


                    <p>
                        Carne de res madura 100%
                        y verduras frescas.
                    </p>


                    <div class="bt-burger-classic-prices">

                        <span>

                            <small>
                                SOLA
                            </small>

                            $27.500

                        </span>


                        <span>

                            <small>
                                COMBO
                            </small>

                            $33.000

                        </span>

                    </div>

                </article>


                <!-- 14 -->

                <article class="bt-burger-classic-card">

                    <div class="bt-burger-classic-header">

                        <span>
                            14
                        </span>

                        <h3>
                            Sweet Chick Town
                        </h3>

                    </div>


                    <p>
                        Pollo dorado y crujiente, coleslaw,
                        sweet BBQ, queso cheddar y
                        mayonesa ahumada en pan de papa brioche.
                    </p>


                    <div class="bt-burger-classic-prices">

                        <span>

                            <small>
                                SOLA
                            </small>

                            $38.000

                        </span>


                        <span>

                            <small>
                                COMBO
                            </small>

                            $43.500

                        </span>

                    </div>

                </article>

            </div>

        </div>

    </section>


    <!-- =========================================================
         NOTA DE LA CASA
    ========================================================== -->

    <section
        id="burger-notes"
        class="bt-burgers-notes-section"
    >

        <div class="bt-burgers-container">

            <div class="bt-burgers-notes-card">

                <div class="bt-burgers-notes-icon">

                    <i class="fa-solid fa-star"></i>

                </div>


                <div>

                    <span>
                        RECOMENDACIÓN DE LA CASA
                    </span>

                    <h2>
                        Pídela en su opción
                        <strong>picante.</strong>
                    </h2>

                    <p>
                        Todas nuestras hamburguesas llevan
                        carne 100% Angus premium y pan brioche
                        de papa.
                    </p>

                </div>

            </div>

        </div>

    </section>


    <!-- =========================================================
         CTA
    ========================================================== -->

    <section class="bt-burgers-cta">

        <div class="bt-burgers-cta-glow"></div>


        <div class="bt-burgers-cta-content">

            <span>
                BURGER TOWN
            </span>


            <h2>

                Ya viste las burgers.

                <strong>
                    Ahora falta probarlas.
                </strong>

            </h2>


            <p>

                Elige tu sede favorita en Bogotá
                y reserva tu mesa.

            </p>


            <div class="bt-burgers-cta-actions">

                <a
                    href="<?= APP_URL ?>/reservas"
                    class="bt-burgers-primary-button"
                >

                    <i class="fa-solid fa-calendar-check"></i>

                    Reservar mesa

                </a>


                <a
                    href="<?= APP_URL ?>/menu"
                    class="bt-burgers-secondary-button"
                >

                    Ver menú completo

                    <i class="fa-solid fa-arrow-right"></i>

                </a>

            </div>

        </div>

    </section>

</div>