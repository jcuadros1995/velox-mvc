<?php
/**
 * app/Views/restaurant/reservations.php
 */
?>

<div class="reservation-page">

    <!-- ============================================================
         HERO
    ============================================================ -->

    <section class="reservation-hero">

        <div class="hero-content">

            <div class="hero-eyebrow">
                <?= htmlspecialchars(
                    SiteConfig::siteName(),
                    ENT_QUOTES,
                    'UTF-8'
                ) ?>
            </div>

            <h1 class="hero-title">
                Reserva una mesa
                para una experiencia especial.
            </h1>

            <p class="hero-description">
                Elige tu sede, fecha y horario.
                Nosotros nos encargamos de encontrar
                la mesa perfecta para ti.
            </p>

        </div>

    </section>


    <!-- ============================================================
         RESERVATION CARD
    ============================================================ -->

    <main class="reservation-wrapper">

        <div class="reservation-card">

            <div class="reservation-content">


                <!-- =================================================
                     STEPS
                ================================================== -->

                <div class="reservation-steps" aria-label="Proceso de reserva">

                    <div class="reservation-step active" id="step1">

                        <div class="step-indicator">
                            <span class="step-number">1</span>
                        </div>

                        <div class="step-content">
                            <span class="step-label">Tu reserva</span>
                            <span class="step-description">Detalles</span>
                        </div>

                    </div>


                    <div class="step-line">
                        <span class="step-line-progress"></span>
                    </div>


                    <div class="reservation-step" id="step2">

                        <div class="step-indicator">
                            <span class="step-number">2</span>
                        </div>

                        <div class="step-content">
                            <span class="step-label">Horario</span>
                            <span class="step-description">Fecha y hora</span>
                        </div>

                    </div>


                    <div class="step-line">
                        <span class="step-line-progress"></span>
                    </div>


                    <div class="reservation-step" id="step3">

                        <div class="step-indicator">
                            <span class="step-number">3</span>
                        </div>

                        <div class="step-content">
                            <span class="step-label">Tus datos</span>
                            <span class="step-description">Información personal</span>
                        </div>

                    </div>


                    <div class="step-line">
                        <span class="step-line-progress"></span>
                    </div>


                    <div class="reservation-step" id="step4">

                        <div class="step-indicator">
                            <span class="step-number">4</span>
                        </div>

                        <div class="step-content">
                            <span class="step-label">Confirmación</span>
                            <span class="step-description">Finalizar reserva</span>
                        </div>

                    </div>

                </div>


                <!-- =================================================
                     BOOKING FORM
                ================================================== -->

                <div id="bookingForm">


                    <!-- =================================================
                         SEARCH SECTION
                    ================================================== -->

                    <section>
                        <!-- =========================================================
                                     INFORMACIÓN DE LA SEDE
                                ========================================================== -->
                        <div class="row g-3">


                            <!-- SEDE -->

                            <div class="col-lg-4">

                                <label
                                        for="branch"
                                        class="form-label-custom"
                                >
                                    Sede
                                </label>

                                <select
                                        id="branch"
                                        class="form-select custom-input"
                                >

                                    <option value="">
                                        Cargando sedes...
                                    </option>

                                </select>

                            </div>


                            <!-- FECHA -->

                            <div class="col-lg-4">

                                <label
                                        for="reservationDate"
                                        class="form-label-custom"
                                >
                                    Fecha
                                </label>

                                <input
                                        type="date"
                                        id="reservationDate"
                                        class="form-control custom-input"
                                >

                            </div>


                            <!-- PERSONAS -->

                            <div class="col-lg-2">

                                <label class="form-label-custom">
                                    Personas
                                </label>

                                <div
                                        class="people-selector"
                                        aria-label="Cantidad de personas"
                                >

                                    <button
                                            type="button"
                                            class="people-button"
                                            id="decreasePeople"
                                            aria-label="Disminuir cantidad de personas"
                                    >
                                        −
                                    </button>

                                    <div
                                            class="people-value"
                                            id="peopleValue"
                                            aria-live="polite"
                                    >
                                        2
                                    </div>

                                    <button
                                            type="button"
                                            class="people-button"
                                            id="increasePeople"
                                            aria-label="Aumentar cantidad de personas"
                                    >
                                        +
                                    </button>

                                </div>

                            </div>


                            <!-- BUSCAR -->

                            <div class="col-lg-2 d-flex align-items-end">

                                <button
                                        type="button"
                                        id="searchAvailability"
                                        class="btn-search w-100"
                                >

                                    <i class="fa-solid fa-magnifying-glass me-2"></i>

                                    Buscar

                                </button>

                            </div>



                        </div>
                        <br>

                        <div
                                id="branchInformation"
                                class="public-branch-information"
                                hidden
                        >

                            <div class="public-branch-information-header">

                                <div class="public-branch-information-icon">
                                    <i class="fa-solid fa-building"></i>
                                </div>

                                <div>

                                    <h3
                                            id="branchInformationName"
                                            class="public-branch-information-title"
                                    >
                                        -
                                    </h3>

                                </div>

                            </div>


                            <div class="public-branch-information-grid">

                                <!-- CÓDIGO -->

                                <div class="public-branch-information-item">

                                    <div class="public-branch-information-item-icon">
                                        <i class="fa-solid fa-hashtag"></i>
                                    </div>

                                    <div>

                                            <span>
                                                Código
                                            </span>

                                        <strong id="branchInformationCode">
                                            -
                                        </strong>

                                    </div>

                                </div>


                                <!-- DIRECCIÓN -->

                                <div class="public-branch-information-item">

                                    <div class="public-branch-information-item-icon">
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

                                </div>


                                <!-- TELÉFONO -->

                                <div class="public-branch-information-item">

                                    <div class="public-branch-information-item-icon">
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

                                </div>


                                <!-- CORREO -->

                                <div class="public-branch-information-item">

                                    <div class="public-branch-information-item-icon">
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

                                </div>

                            </div>


                            <!-- =====================================================
                                 HORARIOS
                            ====================================================== -->

                            <div class="public-branch-hours">

                                <div class="public-branch-hours-header">

                                    <div class="public-branch-hours-icon">
                                        <i class="fa-regular fa-clock"></i>
                                    </div>

                                    <div>

                                            <span class="public-branch-hours-eyebrow">
                                                HORARIO DE ATENCIÓN
                                            </span>

                                    </div>

                                </div>


                                <div
                                        id="branchBusinessHoursList"
                                        class="public-branch-hours-list"
                                >

                                    <div class="public-hours-loading">

                                        Selecciona una sede para consultar
                                        sus horarios.

                                    </div>

                                </div>

                            </div>

                        </div>

                    </section>


                    <!-- =================================================
                         AVAILABILITY
                    ================================================== -->

                    <section
                            id="availabilityContainer"
                            class="availability-container"
                    >

                        <div class="availability-header">

                            <div>

                                <div class="section-title mb-1">
                                    Horarios disponibles
                                </div>

                                <div
                                        id="availabilityInfo"
                                        class="selected-info"
                                ></div>

                            </div>

                        </div>


                        <div id="availabilityContent"></div>


                        <!-- =================================================
                             NAVEGACIÓN PASO 2
                        ================================================== -->

                        <div
                                id="step2Navigation"
                                class="reservation-wizard-navigation"
                        >

                            <button
                                    type="button"
                                    id="btnBackToStep1"
                                    class="btn-wizard-back"
                            >
                                <i class="fa-solid fa-arrow-left"></i>

                                <span>
                                    Atrás
                                </span>
                            </button>

                        </div>

                        <!-- =================================================
                             SUMMARY
                        ================================================== -->

                        <div
                                id="reservationSummary"
                                class="reservation-summary"
                        >

                            <div class="summary-title">
                                Resumen de tu reserva
                            </div>


                            <div class="summary-row">

                                <span class="summary-label">
                                    Sede
                                </span>

                                <span
                                        id="summaryBranch"
                                        class="summary-value"
                                ></span>

                            </div>


                            <div class="summary-row">

                                <span class="summary-label">
                                    Fecha
                                </span>

                                <span
                                        id="summaryDate"
                                        class="summary-value"
                                ></span>

                            </div>


                            <div class="summary-row">

                                <span class="summary-label">
                                    Horario
                                </span>

                                <span
                                        id="summaryTime"
                                        class="summary-value"
                                ></span>

                            </div>


                            <div class="summary-row">

                                <span class="summary-label">
                                    Personas
                                </span>

                                <span
                                        id="summaryPeople"
                                        class="summary-value"
                                ></span>

                            </div>

                        </div>


                        <!-- =================================================
                             CUSTOMER
                        ================================================== -->

                        <section
                                id="customerSection"
                                class="customer-section"
                        >

                            <div class="section-title">
                                Completa tus datos
                            </div>

                            <div class="section-description">
                                Solo necesitamos algunos datos
                                para confirmar tu reserva.
                            </div>


                            <div class="row g-3">


                                <!-- NOMBRE -->

                                <div class="col-md-6">

                                    <label
                                            for="customerName"
                                            class="form-label-custom"
                                    >
                                        Nombre completo
                                    </label>

                                    <input
                                            type="text"
                                            id="customerName"
                                            class="form-control custom-input"
                                            placeholder="Ej. Juan Pérez"
                                            autocomplete="name"
                                    >

                                </div>


                                <!-- TELÉFONO -->

                                <div class="col-md-6">

                                    <label
                                            for="customerPhone"
                                            class="form-label-custom"
                                    >
                                        Teléfono
                                    </label>

                                    <input
                                            type="tel"
                                            id="customerPhone"
                                            class="form-control custom-input"
                                            placeholder="+57 300 000 0000"
                                            autocomplete="tel"
                                    >

                                </div>


                                <!-- EMAIL -->

                                <div class="col-md-6">

                                    <label
                                            for="customerEmail"
                                            class="form-label-custom"
                                    >
                                        Correo electrónico
                                    </label>

                                    <input
                                            type="email"
                                            id="customerEmail"
                                            class="form-control custom-input"
                                            placeholder="correo@ejemplo.com"
                                            autocomplete="email"
                                    >

                                </div>


                                <!-- NOTAS -->

                                <div class="col-md-6">

                                    <label
                                            for="reservationNotes"
                                            class="form-label-custom"
                                    >
                                        Nota especial
                                    </label>

                                    <input
                                            type="text"
                                            id="reservationNotes"
                                            class="form-control custom-input"
                                            placeholder="Cumpleaños, aniversario..."
                                    >

                                </div>


                                <!-- CONFIRM -->

                                <div class="col-12 mt-4">

                                    <button
                                            type="button"
                                            id="confirmReservation"
                                            class="btn-confirm"
                                    >

                                        <i
                                                class="fa-solid fa-circle-check me-2"
                                                aria-hidden="true"
                                        ></i>

                                        Confirmar reserva

                                    </button>

                                </div>

                            </div>

                        </section>

                        <!-- =================================================
                             NAVEGACIÓN PASO 3
                        ================================================== -->

                        <div
                                id="step3Navigation"
                                class="reservation-wizard-navigation"
                        >

                            <button
                                    type="button"
                                    id="btnBackToStep2"
                                    class="btn-wizard-back"
                            >

                                <i class="fa-solid fa-arrow-left"></i>

                                <span>
                                    Atrás
                                </span>

                            </button>

                        </div>

                    </section>

                </div>


                <!-- =================================================
                     SUCCESS
                ================================================== -->

                <section
                        id="successContainer"
                        class="success-container"
                        aria-live="polite"
                >

                    <div class="success-card">

                        <div class="success-icon-wrapper">

                            <div class="success-icon">

                                <i
                                        class="fa-solid fa-check"
                                        aria-hidden="true"
                                ></i>

                            </div>

                        </div>


                        <h2 class="success-title">
                            ¡Reserva confirmada!
                        </h2>


                        <p class="success-message">
                            Tu reserva ha sido registrada correctamente.
                            Guarda tu código de reserva para consultar
                            o gestionar tu reserva posteriormente.
                        </p>


                        <div class="reservation-code-container">

            <span class="reservation-code-label">
                CÓDIGO DE RESERVA
            </span>

                            <div
                                    id="reservationCode"
                                    class="reservation-code"
                            >
                                RSV-XXXXXX
                            </div>

                            <span class="reservation-code-hint">
                Usa este código para consultar tu reserva
            </span>

                        </div>


                        <div class="success-notice">

                            <i
                                    class="fa-solid fa-clock"
                                    aria-hidden="true"
                            ></i>

                            <span>
                Te recomendamos llegar unos minutos
                antes de tu horario.
            </span>

                        </div>


                        <button
                                type="button"
                                class="btn-new-reservation"
                                onclick="location.reload()"
                        >

                            <i
                                    class="fa-solid fa-rotate-right"
                                    aria-hidden="true"
                            ></i>

                            <span>
                Hacer otra reserva
            </span>

                        </button>

                    </div>

                </section>

            </div>

        </div>

    </main>


    <!-- ============================================================
         TOAST
    ============================================================ -->

    <div
            id="reservationToast"
            class="reservation-toast"
            role="alert"
            aria-live="assertive"
    ></div>

</div>

