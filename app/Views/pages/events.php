<?php

/**
 * ============================================================
 * BURGER TOWN
 * PUBLIC - EVENTOS
 * ============================================================
 */

$appUrl = rtrim(
        (string) APP_URL,
        '/'
);

?>

<section class="bt-events-page">

    <!-- ========================================================
         HERO
    ========================================================= -->

    <header class="bt-events-hero">

        <div class="bt-events-hero-bg"></div>

        <div class="bt-events-hero-circle bt-events-hero-circle-1"></div>
        <div class="bt-events-hero-circle bt-events-hero-circle-2"></div>

        <div class="bt-page-shell bt-events-hero-inner">

            <!-- Breadcrumb -->

            <nav
                    class="bt-events-breadcrumb"
                    aria-label="Navegación"
            >

                <a href="<?= htmlspecialchars($appUrl, ENT_QUOTES, 'UTF-8') ?>">

                    <i class="fa-solid fa-house"></i>

                    Inicio

                </a>

                <i class="fa-solid fa-chevron-right"></i>

                <span>
                    Eventos
                </span>

            </nav>


            <!-- Hero -->

            <div class="bt-events-hero-content">

                <div class="bt-events-hero-icon">

                    <i class="fa-solid fa-champagne-glasses"></i>

                </div>

                <div>

                    <span class="bt-eyebrow">
                        BURGER TOWN · BOGOTÁ
                    </span>

                    <h1>
                        Celebra como se debe:
                        con mesa lista y buen parche.
                    </h1>

                    <p>
                        Cumpleaños, reuniones con amigos o planes
                        corporativos. Tú traes la gente y nosotros
                        ponemos el ambiente.
                    </p>

                </div>

            </div>

        </div>

    </header>


    <!-- ========================================================
         CONTENIDO
    ========================================================= -->

    <main class="bt-events-main">

        <div class="bt-page-shell">


            <!-- =================================================
                 PASOS
            ================================================== -->

            <section class="bt-events-steps">

                <article class="bt-events-step">

                    <div class="bt-events-step-number">
                        01
                    </div>

                    <div>

                        <span class="bt-events-step-eyebrow">
                            PASO 01
                        </span>

                        <h3>
                            Elige fecha y tipo
                        </h3>

                        <p>
                            Cuéntanos cuándo quieres celebrar
                            y qué ocasión tienes en mente.
                        </p>

                    </div>

                </article>


                <article class="bt-events-step">

                    <div class="bt-events-step-number">
                        02
                    </div>

                    <div>

                        <span class="bt-events-step-eyebrow">
                            PASO 02
                        </span>

                        <h3>
                            Completa tus datos
                        </h3>

                        <p>
                            Selecciona sede, invitados y la
                            información de contacto.
                        </p>

                    </div>

                </article>


                <article class="bt-events-step">

                    <div class="bt-events-step-number">
                        03
                    </div>

                    <div>

                        <span class="bt-events-step-eyebrow">
                            PASO 03
                        </span>

                        <h3>
                            Recibe confirmación
                        </h3>

                        <p>
                            La sede revisará disponibilidad y se
                            pondrá en contacto contigo.
                        </p>

                    </div>

                </article>

            </section>


            <!-- =================================================
                 GRID PRINCIPAL
            ================================================== -->

            <div class="bt-events-layout">


                <!-- =================================================
                     FORMULARIO
                ================================================== -->

                <section class="bt-events-card bt-events-form-card">


                    <!-- Header -->

                    <div class="bt-events-card-header">

                        <div class="bt-events-card-number">
                            01
                        </div>

                        <div>

                            <span class="bt-events-card-eyebrow">
                                RESERVA TU EVENTO
                            </span>

                            <h2>
                                Cuéntanos los detalles.
                            </h2>

                            <p>
                                Completa la información y cuéntanos
                                cómo quieres celebrar.
                            </p>

                        </div>

                    </div>


                    <!-- Form -->

                    <form
                            id="eventForm"
                            class="bt-events-form"
                            novalidate
                    >


                        <!-- =================================================
                             DATOS DEL EVENTO
                        ================================================== -->

                        <div class="bt-events-form-section">

                            <div class="bt-events-form-section-title">

                                <i class="fa-solid fa-calendar-days"></i>

                                <span>
                                    Datos del evento
                                </span>

                            </div>


                            <div class="bt-events-form-grid">


                                <!-- Sede -->

                                <div class="bt-form-field">

                                    <label for="eventBranch">

                                        Sede

                                        <span>*</span>

                                    </label>

                                    <div class="bt-input-wrapper">

                                        <i class="fa-solid fa-location-dot"></i>

                                        <select
                                                id="eventBranch"
                                                name="branch_id"
                                                required
                                        >

                                            <option value="">
                                                Selecciona una sede
                                            </option>

                                            <?php foreach (($publicBranches ?? []) as $b): ?>

                                                <option
                                                        value="<?= (int) $b['id'] ?>"
                                                >
                                                    <?= htmlspecialchars(
                                                            $b['name'],
                                                            ENT_QUOTES,
                                                            'UTF-8'
                                                    ) ?>
                                                </option>

                                            <?php endforeach; ?>

                                        </select>

                                    </div>

                                </div>


                                <!-- Tipo -->

                                <div class="bt-form-field">

                                    <label for="eventType">

                                        Tipo de evento

                                        <span>*</span>

                                    </label>

                                    <div class="bt-input-wrapper">

                                        <i class="fa-solid fa-champagne-glasses"></i>

                                        <select
                                                id="eventType"
                                                name="event_type"
                                                required
                                        >

                                            <option value="">
                                                Selecciona
                                            </option>

                                            <option value="Cumpleaños">
                                                Cumpleaños
                                            </option>

                                            <option value="Celebración">
                                                Celebración
                                            </option>

                                            <option value="Evento corporativo">
                                                Evento corporativo
                                            </option>

                                            <option value="Reunión de amigos">
                                                Reunión de amigos
                                            </option>

                                            <option value="Otro">
                                                Otro
                                            </option>

                                        </select>

                                    </div>

                                </div>


                                <!-- Invitados -->

                                <div class="bt-form-field">

                                    <label for="eventGuests">

                                        Número de invitados

                                        <span>*</span>

                                    </label>

                                    <div class="bt-input-wrapper">

                                        <i class="fa-solid fa-users"></i>

                                        <input
                                                id="eventGuests"
                                                name="guests"
                                                type="number"
                                                min="2"
                                                max="500"
                                                required
                                                placeholder="20"
                                        >

                                    </div>

                                </div>


                                <!-- Fecha -->

                                <div class="bt-form-field">

                                    <label for="eventDate">

                                        Fecha

                                        <span>*</span>

                                    </label>

                                    <div class="bt-input-wrapper">

                                        <i class="fa-regular fa-calendar"></i>

                                        <input
                                                id="eventDate"
                                                name="date"
                                                type="date"
                                                required
                                        >

                                    </div>

                                </div>


                                <!-- Hora -->

                                <div class="bt-form-field">

                                    <label for="eventTime">

                                        Hora

                                        <span>*</span>

                                    </label>

                                    <div class="bt-input-wrapper">

                                        <i class="fa-regular fa-clock"></i>

                                        <input
                                                id="eventTime"
                                                name="time"
                                                type="time"
                                                required
                                        >

                                    </div>

                                </div>

                            </div>

                        </div>


                        <!-- =================================================
                             DATOS DEL CLIENTE
                        ================================================== -->

                        <div class="bt-events-form-section">

                            <div class="bt-events-form-section-title">

                                <i class="fa-solid fa-user"></i>

                                <span>
                                    Tus datos
                                </span>

                            </div>


                            <div class="bt-events-form-grid">


                                <!-- Nombre -->

                                <div class="bt-form-field">

                                    <label for="eventName">

                                        Nombre

                                        <span>*</span>

                                    </label>

                                    <div class="bt-input-wrapper">

                                        <i class="fa-regular fa-user"></i>

                                        <input
                                                id="eventName"
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

                                    <label for="eventEmail">

                                        Correo electrónico

                                        <span>*</span>

                                    </label>

                                    <div class="bt-input-wrapper">

                                        <i class="fa-regular fa-envelope"></i>

                                        <input
                                                id="eventEmail"
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

                                    <label for="eventPhone">

                                        Teléfono

                                        <span>*</span>

                                    </label>

                                    <div class="bt-input-wrapper">

                                        <i class="fa-solid fa-phone"></i>

                                        <input
                                                id="eventPhone"
                                                name="phone"
                                                type="tel"
                                                maxlength="40"
                                                autocomplete="tel"
                                                required
                                                placeholder="310 000 0000"
                                        >

                                    </div>

                                </div>

                            </div>

                        </div>


                        <!-- =================================================
                             MENSAJE
                        ================================================== -->

                        <div class="bt-events-form-section">

                            <div class="bt-events-form-section-title">

                                <i class="fa-regular fa-comment-dots"></i>

                                <span>
                                    Cuéntanos tu idea
                                </span>

                            </div>


                            <div class="bt-form-field">

                                <label for="eventMessage">
                                    Mensaje
                                </label>

                                <div class="bt-textarea-wrapper">

                                    <textarea
                                            id="eventMessage"
                                            name="message"
                                            rows="6"
                                            maxlength="3000"
                                            placeholder="Decoración, requerimientos, menú, espacio, etc."
                                    ></textarea>

                                    <div class="bt-textarea-footer">

                                        <span>
                                            Cuéntanos todos los detalles.
                                        </span>

                                        <strong id="eventMessageCounter">
                                            0 / 3000
                                        </strong>

                                    </div>

                                </div>

                            </div>

                        </div>


                        <!-- =================================================
                             FOOTER FORM
                        ================================================== -->

                        <div class="bt-events-form-footer">

                            <div class="bt-events-form-note">

                                <i class="fa-solid fa-shield-halved"></i>

                                <span>
                                    Tus datos serán utilizados únicamente
                                    para gestionar tu solicitud de evento.
                                </span>

                            </div>


                            <button
                                    class="bt-primary-btn bt-events-submit"
                                    type="submit"
                                    id="eventSubmit"
                            >

                                <span>
                                    Solicitar evento
                                </span>

                                <i class="fa-solid fa-calendar-check"></i>

                            </button>

                        </div>

                    </form>

                </section>


                <!-- =================================================
                     SIDEBAR
                ================================================== -->

                <aside class="bt-events-sidebar">


                    <!-- =================================================
                         FAQ
                    ================================================== -->

                    <section class="bt-events-card bt-events-faq-card">

                        <div class="bt-events-info-header">

                            <span class="bt-eyebrow">
                                PREGUNTAS FRECUENTES
                            </span>

                            <h2>
                                Todo listo para el parche.
                            </h2>

                            <p>
                                Resolvemos algunas de las preguntas
                                más comunes antes de solicitar tu evento.
                            </p>

                        </div>


                        <div class="bt-events-faq">


                            <details open>

                                <summary>
                                    ¿Qué tipo de eventos puedo hacer?

                                    <i class="fa-solid fa-chevron-down"></i>

                                </summary>

                                <p>
                                    Cumpleaños, celebraciones, reuniones
                                    de amigos, encuentros de trabajo y
                                    planes en grupo.
                                </p>

                            </details>


                            <details>

                                <summary>
                                    ¿Puedo elegir la sede?

                                    <i class="fa-solid fa-chevron-down"></i>

                                </summary>

                                <p>
                                    Sí. Selecciona la sede que más te
                                    convenga en el formulario.
                                </p>

                            </details>


                            <details>

                                <summary>
                                    ¿Con cuánta anticipación debo solicitar?

                                    <i class="fa-solid fa-chevron-down"></i>

                                </summary>

                                <p>
                                    Entre más pronto, mejor. Para fechas
                                    concurridas recomendamos solicitar con
                                    varios días de anticipación.
                                </p>

                            </details>


                            <details>

                                <summary>
                                    ¿La solicitud queda confirmada de inmediato?

                                    <i class="fa-solid fa-chevron-down"></i>

                                </summary>

                                <p>
                                    No. Primero revisamos disponibilidad y
                                    luego la sede te contactará para confirmar.
                                </p>

                            </details>

                        </div>

                    </section>


                    <!-- =================================================
                         CTA RESERVA
                    ================================================== -->

                    <section class="bt-events-cta-card">

                        <div class="bt-events-cta-icon">

                            <i class="fa-solid fa-calendar-check"></i>

                        </div>

                        <div>

                            <strong>
                                ¿Solo quieres reservar una mesa?
                            </strong>

                            <span>
                                Haz tu reserva tradicional en pocos pasos.
                            </span>

                        </div>

                        <a
                                href="<?= htmlspecialchars($appUrl, ENT_QUOTES, 'UTF-8') ?>/reservas"
                                class="bt-events-cta-link"
                        >

                            Reservar mesa

                            <i class="fa-solid fa-arrow-right"></i>

                        </a>

                    </section>


                    <!-- =================================================
                         MENSAJE FINAL
                    ================================================== -->

                    <div class="bt-events-sidebar-bottom">

                        <div class="bt-events-sidebar-bottom-icon">

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