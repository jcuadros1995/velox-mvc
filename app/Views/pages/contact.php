<?php

/**
 * ============================================================
 * BURGER TOWN
 * PUBLIC - CONTACT
 * ============================================================
 */

$appUrl = rtrim(
        (string)APP_URL,
        '/'
);

?>

<section class="bt-contact-page">


    <!-- ========================================================
         HERO
    ========================================================= -->

    <header class="bt-contact-hero">

        <div class="bt-contact-hero-bg"></div>


        <div class="bt-contact-hero-circle bt-contact-hero-circle-1"></div>

        <div class="bt-contact-hero-circle bt-contact-hero-circle-2"></div>


        <div class="bt-page-shell bt-contact-hero-inner">


            <!-- Breadcrumb -->

            <nav
                    class="bt-contact-breadcrumb"
                    aria-label="Navegación"
            >

                <a href="<?= htmlspecialchars($appUrl, ENT_QUOTES, 'UTF-8') ?>">

                    <i class="fa-solid fa-house"></i>

                    Inicio

                </a>


                <i class="fa-solid fa-chevron-right"></i>


                <span>
                    Contacto
                </span>

            </nav>


            <!-- Hero -->

            <div class="bt-contact-hero-content">

                <div class="bt-contact-hero-icon">

                    <i class="fa-solid fa-comments"></i>

                </div>


                <div>

                    <span class="bt-eyebrow">
                        ATENCIÓN AL CLIENTE
                    </span>


                    <h1>
                        Estamos para ayudarte.
                    </h1>


                    <p>
                        Cuéntanos qué necesitas sobre reservas,
                        sedes, eventos o cualquier inquietud.
                        Nuestro equipo te responderá lo antes posible.
                    </p>

                </div>

            </div>

        </div>

    </header>


    <!-- ========================================================
         CONTENIDO
    ========================================================= -->

    <main class="bt-contact-main">

        <div class="bt-page-shell">


            <div class="bt-contact-layout">


                <!-- =================================================
                     FORMULARIO
                ================================================== -->

                <section class="bt-contact-card bt-contact-form-card">


                    <!-- Header -->

                    <div class="bt-contact-card-header">

                        <div class="bt-contact-card-number">
                            01
                        </div>


                        <div>

                            <span class="bt-contact-card-eyebrow">
                                CONTÁCTANOS
                            </span>

                            <h2>
                                Escríbenos
                            </h2>

                            <p>
                                Completa el formulario y cuéntanos
                                cómo podemos ayudarte.
                            </p>

                        </div>

                    </div>


                    <!-- Form -->

                    <form
                            id="contactForm"
                            class="bt-contact-form"
                            novalidate
                    >


                        <div class="bt-form-section">

                            <div class="bt-form-section-title">

                                <i class="fa-solid fa-user"></i>

                                <span>
                                    Tus datos
                                </span>

                            </div>


                            <div class="bt-form-grid">


                                <!-- Nombre -->

                                <div class="bt-form-field">

                                    <label
                                            for="contactName"
                                    >
                                        Nombre

                                        <span>
                                            *
                                        </span>
                                    </label>


                                    <div class="bt-input-wrapper">

                                        <i class="fa-regular fa-user"></i>

                                        <input
                                                id="contactName"
                                                name="name"
                                                type="text"
                                                maxlength="120"
                                                autocomplete="name"
                                                required
                                                placeholder="Tu nombre"
                                        >

                                    </div>

                                </div>


                                <!-- Correo -->

                                <div class="bt-form-field">

                                    <label
                                            for="contactEmail"
                                    >
                                        Correo electrónico

                                        <span>
                                            *
                                        </span>
                                    </label>


                                    <div class="bt-input-wrapper">

                                        <i class="fa-regular fa-envelope"></i>

                                        <input
                                                id="contactEmail"
                                                name="email"
                                                type="email"
                                                maxlength="160"
                                                autocomplete="email"
                                                required
                                                placeholder="tu@email.com"
                                        >

                                    </div>

                                </div>


                                <!-- Teléfono -->

                                <div class="bt-form-field">

                                    <label
                                            for="contactPhone"
                                    >
                                        Teléfono
                                    </label>


                                    <div class="bt-input-wrapper">

                                        <i class="fa-solid fa-phone"></i>

                                        <input
                                                id="contactPhone"
                                                name="phone"
                                                type="tel"
                                                maxlength="40"
                                                autocomplete="tel"
                                                placeholder="310 000 0000"
                                        >

                                    </div>

                                </div>


                                <!-- Asunto -->

                                <div class="bt-form-field">

                                    <label
                                            for="contactSubject"
                                    >
                                        Asunto
                                    </label>


                                    <div class="bt-input-wrapper">

                                        <i class="fa-regular fa-message"></i>

                                        <input
                                                id="contactSubject"
                                                name="subject"
                                                type="text"
                                                maxlength="180"
                                                value="Consulta Burger Town"
                                                placeholder="¿Sobre qué quieres escribirnos?"
                                        >

                                    </div>

                                </div>

                            </div>

                        </div>


                        <!-- =================================================
                             MENSAJE
                        ================================================== -->

                        <div class="bt-form-section">


                            <div class="bt-form-section-title">

                                <i class="fa-regular fa-comment-dots"></i>

                                <span>
                                    Cuéntanos
                                </span>

                            </div>


                            <div class="bt-form-field">

                                <label
                                        for="contactMessage"
                                >
                                    Mensaje

                                    <span>
                                        *
                                    </span>
                                </label>


                                <div class="bt-textarea-wrapper">

                                    <textarea
                                            id="contactMessage"
                                            name="message"
                                            rows="7"
                                            maxlength="3000"
                                            required
                                            placeholder="¿Cómo podemos ayudarte?"
                                    ></textarea>


                                    <div class="bt-textarea-footer">

                                        <span>
                                            Cuéntanos todos los detalles.
                                        </span>

                                        <strong id="contactMessageCounter">
                                            0 / 3000
                                        </strong>

                                    </div>

                                </div>

                            </div>

                        </div>


                        <!-- =================================================
                             FOOTER FORM
                        ================================================== -->

                        <div class="bt-contact-form-footer">

                            <div class="bt-contact-form-note">

                                <i class="fa-solid fa-shield-halved"></i>

                                <span>
                                    Tus datos serán utilizados únicamente
                                    para responder tu solicitud.
                                </span>

                            </div>


                            <button
                                    class="bt-primary-btn bt-contact-submit"
                                    type="submit"
                                    id="contactSubmit"
                            >

                                <span>
                                    Enviar mensaje
                                </span>

                                <i class="fa-solid fa-paper-plane"></i>

                            </button>

                        </div>

                    </form>

                </section>


                <!-- =================================================
                     SIDEBAR
                ================================================== -->

                <aside class="bt-contact-sidebar">


                    <!-- =================================================
                         INFO
                    ================================================== -->

                    <section class="bt-contact-card bt-contact-info-card">


                        <div class="bt-contact-info-header">

                            <span class="bt-eyebrow">
                                BURGER TOWN
                            </span>


                            <h2>
                                Aquí empieza el plan.
                            </h2>


                            <p>
                                Escríbenos, llámanos o visita
                                cualquiera de nuestras sedes.
                            </p>

                        </div>


                        <div class="bt-contact-info-list">


                            <!-- Ubicación -->

                            <div class="bt-contact-info-row">

                                <div class="bt-contact-info-icon">

                                    <i class="fa-solid fa-location-dot"></i>

                                </div>


                                <div>

                                    <small>
                                        Ubicación
                                    </small>

                                    <strong>
                                        Bogotá, Colombia
                                    </strong>

                                </div>

                            </div>


                            <!-- Teléfono -->

                            <div class="bt-contact-info-row">

                                <div class="bt-contact-info-icon">

                                    <i class="fa-solid fa-phone"></i>

                                </div>


                                <div>

                                    <small>
                                        Teléfono
                                    </small>

                                    <strong>
                                        +57 310 716 3415
                                    </strong>

                                </div>

                            </div>


                            <!-- Correo -->

                            <div class="bt-contact-info-row">

                                <div class="bt-contact-info-icon">

                                    <i class="fa-regular fa-envelope"></i>

                                </div>


                                <div>

                                    <small>
                                        Correo electrónico
                                    </small>

                                    <strong>
                                        contacto@burgertown.com.co
                                    </strong>

                                </div>

                            </div>


                        </div>


                        <!-- CTA -->

                        <a
                                class="bt-outline-btn"
                                href="<?= htmlspecialchars($appUrl, ENT_QUOTES, 'UTF-8') ?>/reservas"
                        >

                            Reservar una mesa

                            <i class="fa-solid fa-arrow-right"></i>

                        </a>

                    </section>


                    <!-- =================================================
                         ACCIONES RÁPIDAS
                    ================================================== -->

                    <section class="bt-contact-quick-card">

                        <span class="bt-contact-quick-eyebrow">
                            TAMBIÉN PUEDES
                        </span>


                        <h3>
                            Encontrarnos fácilmente.
                        </h3>


                        <div class="bt-contact-quick-actions">


                            <a
                                    href="<?= htmlspecialchars($appUrl, ENT_QUOTES, 'UTF-8') ?>/sedes"
                                    class="bt-contact-quick-action"
                            >

                                <span class="bt-contact-quick-icon">

                                    <i class="fa-solid fa-location-dot"></i>

                                </span>


                                <span>

                                    <strong>
                                        Ver nuestras sedes
                                    </strong>

                                    <small>
                                        Encuentra tu Burger Town
                                    </small>

                                </span>


                                <i class="fa-solid fa-chevron-right"></i>

                            </a>


                            <a
                                    href="<?= htmlspecialchars($appUrl, ENT_QUOTES, 'UTF-8') ?>/menu"
                                    class="bt-contact-quick-action"
                            >

                                <span class="bt-contact-quick-icon">

                                    <i class="fa-solid fa-utensils"></i>

                                </span>


                                <span>

                                    <strong>
                                        Explorar el menú
                                    </strong>

                                    <small>
                                        Mira lo que tenemos para ti
                                    </small>

                                </span>


                                <i class="fa-solid fa-chevron-right"></i>

                            </a>


                            <a
                                    href="<?= htmlspecialchars($appUrl, ENT_QUOTES, 'UTF-8') ?>/reservas"
                                    class="bt-contact-quick-action"
                            >

                                <span class="bt-contact-quick-icon">

                                    <i class="fa-solid fa-calendar-check"></i>

                                </span>


                                <span>

                                    <strong>
                                        Hacer una reserva
                                    </strong>

                                    <small>
                                        Reserva tu próxima visita
                                    </small>

                                </span>


                                <i class="fa-solid fa-chevron-right"></i>

                            </a>

                        </div>

                    </section>


                    <!-- =================================================
                         MENSAJE FINAL
                    ================================================== -->

                    <div class="bt-contact-sidebar-bottom">

                        <div class="bt-contact-sidebar-bottom-icon">

                            <i class="fa-solid fa-burger"></i>

                        </div>


                        <div>

                            <strong>
                                Nos vemos en Burger Town.
                            </strong>

                            <span>
                                Buenas hamburguesas, buenos momentos.
                            </span>

                        </div>

                    </div>

                </aside>

            </div>

        </div>

    </main>

</section>