<?php
/**
 * app/Views/restaurant/branches.php
 */
?>

<div class="bt-branches-page">


    <!-- =========================================================
         HERO
    ========================================================== -->

    <section
        class="bt-branches-hero"
        id="branchesHero"
    >

        <!-- BACKGROUND -->

        <div class="bt-branches-hero-background">

            <div
                class="bt-branches-hero-placeholder"
                id="branchesHeroPlaceholder"
            >
                <i class="fa-solid fa-store"></i>
            </div>

            <img
                id="branchesHeroImage"
                class="bt-branches-hero-image"
                alt=""
                hidden
            >

            <div class="bt-branches-hero-overlay"></div>

        </div>


        <!-- CONTENT -->

        <div class="bt-branches-hero-content">

            <span class="bt-branches-eyebrow">

                <i class="fa-solid fa-location-dot"></i>

                NUESTRAS SEDES

            </span>


            <h1 id="branchesHeroTitle">
                Selecciona una sede
            </h1>


            <p id="branchesHeroDescription">

                Descubre nuestras sedes Burger Town,
                conoce su ubicación y horarios.

            </p>


            <!-- SELECT -->

            <div class="bt-branches-selector">

                <label
                    for="branch"
                    class="bt-branches-selector-label"
                >
                    Elige tu sede
                </label>


                <div class="branch-select-wrapper">

                    <select
                        id="branch"
                        class="form-select custom-input"
                    >

                        <option value="">
                            Cargando sedes...
                        </option>

                    </select>


                    <span class="branch-select-icon">

                        <i class="fa-solid fa-chevron-down"></i>

                    </span>

                </div>

            </div>


            <!-- CONTACTO RÁPIDO -->

            <div
                class="bt-branches-hero-contact"
                id="branchesHeroContact"
                hidden
            >

                <div class="bt-branches-hero-contact-item">

                    <i class="fa-solid fa-location-dot"></i>

                    <span id="branchesHeroAddress">
                        -
                    </span>

                </div>


                <div class="bt-branches-hero-contact-item">

                    <i class="fa-solid fa-phone"></i>

                    <span id="branchesHeroPhone">
                        -
                    </span>

                </div>

            </div>


            <!-- GALERÍA -->

            <div
                class="bt-branches-gallery"
                id="branchesGallery"
                hidden
            >

                <button
                    type="button"
                    class="bt-branches-gallery-arrow"
                    id="branchesGalleryPrev"
                    aria-label="Imagen anterior"
                >
                    <i class="fa-solid fa-chevron-left"></i>
                </button>


                <div
                    class="bt-branches-thumbnails"
                    id="branchesThumbnails"
                ></div>


                <button
                    type="button"
                    class="bt-branches-gallery-arrow"
                    id="branchesGalleryNext"
                    aria-label="Imagen siguiente"
                >
                    <i class="fa-solid fa-chevron-right"></i>
                </button>

            </div>

        </div>

    </section>


    <!-- =========================================================
         INFORMACIÓN
    ========================================================== -->

    <section class="bt-branches-information-section">

        <div class="bt-branches-container">


            <!-- HEADER -->

            <header class="bt-branches-section-header">

                <span class="bt-branches-section-eyebrow">
                    CONOCE LA SEDE
                </span>

                <h2 id="branchesInformationTitle">
                    Información de la sede
                </h2>

                <p>
                    Encuentra toda la información que necesitas
                    antes de visitarnos.
                </p>

            </header>


            <!-- INFO GRID -->

            <div class="bt-branches-info-grid">


                <!-- DIRECCIÓN -->

                <article class="bt-branches-info-card">

                    <div class="bt-branches-info-icon">

                        <i class="fa-solid fa-location-dot"></i>

                    </div>


                    <div>

                        <span>
                            Dirección
                        </span>

                        <strong id="branchInformationAddress">
                            -
                        </strong>

                    </div>

                </article>


                <!-- TELÉFONO -->

                <article class="bt-branches-info-card">

                    <div class="bt-branches-info-icon">

                        <i class="fa-solid fa-phone"></i>

                    </div>


                    <div>

                        <span>
                            Teléfono
                        </span>

                        <strong id="branchInformationPhone">
                            -
                        </strong>

                    </div>

                </article>


                <!-- EMAIL -->

                <article class="bt-branches-info-card">

                    <div class="bt-branches-info-icon">

                        <i class="fa-solid fa-envelope"></i>

                    </div>


                    <div>

                        <span>
                            Correo electrónico
                        </span>

                        <strong id="branchInformationEmail">
                            -
                        </strong>

                    </div>

                </article>


                <!-- CÓDIGO -->

                <article class="bt-branches-info-card">

                    <div class="bt-branches-info-icon">

                        <i class="fa-solid fa-hashtag"></i>

                    </div>


                    <div>

                        <span>
                            Código de sede
                        </span>

                        <strong id="branchInformationCode">
                            -
                        </strong>

                    </div>

                </article>

            </div>


            <!-- =================================================
                 HORARIOS
            ================================================== -->

            <div class="bt-branches-hours-card">

                <div class="bt-branches-hours-header">

                    <div class="bt-branches-hours-icon">

                        <i class="fa-regular fa-clock"></i>

                    </div>


                    <div>

                        <span>
                            HORARIO DE ATENCIÓN
                        </span>

                        <h3>
                            Visítanos
                        </h3>

                    </div>

                </div>


                <div
                    class="bt-branches-hours-list"
                    id="branchBusinessHoursList"
                >

                    <div class="bt-branches-loading">

                        <i class="fa-solid fa-spinner fa-spin"></i>

                        Consultando horarios...

                    </div>

                </div>

            </div>

        </div>

    </section>


    <!-- =========================================================
         MAPA
    ========================================================== -->

    <section class="bt-branches-map-section">

        <div class="bt-branches-container">


            <header class="bt-branches-section-header">

                <span class="bt-branches-section-eyebrow">
                    ENCUÉNTRANOS
                </span>

                <h2>
                    Nuestra ubicación
                </h2>

                <p>
                    Consulta cómo llegar a tu sede Burger Town.
                </p>

            </header>


            <div class="bt-branches-map-card">


                <!-- MAP -->

                <div class="bt-branches-map-wrapper">

                    <iframe
                        id="branchMap"
                        title="Ubicación Burger Town"
                        loading="lazy"
                        referrerpolicy="no-referrer-when-downgrade"
                        src="about:blank"
                    ></iframe>


                    <div
                        class="bt-branches-map-loading"
                        id="branchMapLoading"
                    >

                        <i class="fa-solid fa-spinner fa-spin"></i>

                        Cargando ubicación...

                    </div>

                </div>


                <!-- MAP INFO -->

                <div class="bt-branches-map-info">

                    <div class="bt-branches-map-info-icon">

                        <i class="fa-solid fa-map-location-dot"></i>

                    </div>


                    <div class="bt-branches-map-info-content">

                        <span>
                            SEDE SELECCIONADA
                        </span>

                        <h3 id="branchMapName">
                            -
                        </h3>

                        <p id="branchMapAddress">
                            -
                        </p>


                        <a
                            href="#"
                            target="_blank"
                            rel="noopener noreferrer"
                            id="branchGoogleMapsLink"
                            class="bt-branches-map-link"
                        >

                            <span>
                                Abrir en Google Maps
                            </span>

                            <i class="fa-solid fa-arrow-up-right-from-square"></i>

                        </a>

                    </div>

                </div>

            </div>

        </div>

    </section>


    <!-- =========================================================
         CTA RESERVA
    ========================================================== -->

    <section class="bt-branches-cta">

        <div class="bt-branches-cta-glow"></div>

        <div class="bt-branches-cta-content">

            <span class="bt-branches-cta-eyebrow">

                BURGER TOWN

            </span>


            <h2 id="branchCtaTitle">

                ¿Listo para el plan?

            </h2>


            <p id="branchCtaDescription">

                Reserva tu mesa en esta sede
                y disfruta la experiencia Burger Town.

            </p>


            <a
                href="<?= APP_URL ?>/reservas"
                id="branchReserveButton"
                class="bt-branches-cta-button"
            >

                <span>
                    Reservar mesa
                </span>

                <i class="fa-solid fa-arrow-right"></i>

            </a>

        </div>

    </section>

</div>