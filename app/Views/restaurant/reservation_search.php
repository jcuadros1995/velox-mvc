
<?php
// app/Views/restaurant/reservation_search.php
?>

<div class="reservation-search-page">

    <section class="reservation-search-hero">
        <div class="container">

            <div
                class="reservation-search-wrapper"
                id="reservationSearchWrapper"
            >

                <!-- ENCABEZADO -->
                <div class="reservation-search-heading">

                    <span class="reservation-search-eyebrow">
                        <i class="fa-solid fa-magnifying-glass"></i>
                        Consulta pública
                    </span>

                    <h1>Consulta tu reserva</h1>

                    <p>
                        Ingresa el código de reserva y el correo electrónico asociado
                        para consultar los detalles de tu reserva.
                    </p>

                </div>


                <!-- TARJETA DE BÚSQUEDA -->
                <div
                    class="reservation-search-card"
                    id="reservationSearchCard"
                >

                    <form
                        id="reservationSearchForm"
                        novalidate
                    >

                        <div class="row g-3 justify-content-center">

                            <div class="col-lg-10 col-xl-10">

                                <label
                                    for="reservationSearchInput"
                                    class="form-label"
                                >
                                    Código de reserva
                                </label>

                                <div class="reservation-search-input">

                                    <i
                                        class="fa-solid fa-magnifying-glass"
                                    ></i>

                                    <input
                                        type="text"
                                        id="reservationSearchInput"
                                        name="search"
                                        class="form-control"
                                        placeholder="Ej. RSV-1FB7BF"
                                        autocomplete="off"
                                        maxlength="40"
                                    >

                                </div>

                                <div
                                    class="invalid-feedback"
                                    id="reservationSearchError"
                                ></div>

                                <label for="reservationSearchEmail" class="form-label mt-3">
                                    Correo electrónico asociado a la reserva
                                </label>
                                <div class="reservation-search-input">
                                    <i class="fa-solid fa-envelope"></i>
                                    <input
                                        type="email"
                                        id="reservationSearchEmail"
                                        name="email"
                                        class="form-control"
                                        placeholder="correo@ejemplo.com"
                                        autocomplete="email"
                                        maxlength="190"
                                    >
                                </div>
                                <div class="invalid-feedback" id="reservationSearchEmailError"></div>

                            </div>

                        </div>


                        <!-- ACCIONES -->
                        <div class="reservation-search-actions">

                            <button
                                type="submit"
                                id="searchReservationButton"
                                class="btn btn-velox-primary reservation-search-button"
                            >

                                <span
                                    class="search-button-content"
                                    id="searchButtonContent"
                                >
                                    <i
                                        class="fa-solid fa-magnifying-glass me-2"
                                    ></i>

                                    Consultar reserva
                                </span>

                                <span
                                    class="search-button-loading d-none"
                                    id="searchButtonLoading"
                                >
                                    <span
                                        class="spinner-border spinner-border-sm me-2"
                                        role="status"
                                        aria-hidden="true"
                                    ></span>

                                    Consultando...
                                </span>

                            </button>

                        </div>


                        <!-- AYUDA -->
                        <div class="reservation-search-hint">

                            <i class="fa-solid fa-circle-info"></i>

                            Por seguridad, se requieren ambos datos: código de reserva y correo electrónico.

                        </div>

                    </form>

                </div>

            </div>

        </div>
    </section>


    <!-- RESULTADOS -->
    <section
        class="reservation-search-results-section"
        id="reservationResultsSection"
    >

        <div class="container">

            <!-- MENSAJE -->
            <div
                id="reservationSearchMessage"
                class="reservation-search-message d-none"
            ></div>


            <!-- ENCABEZADO RESULTADOS -->
            <div
                id="reservationResultsHeader"
                class="reservation-results-header d-none"
            >

                <div>

                    <span class="reservation-results-eyebrow">
                        Resultado de consulta
                    </span>

                    <h2>Tus reservas</h2>

                </div>

                <span
                    id="reservationResultsCount"
                    class="reservation-results-count"
                >
                    0 reservas
                </span>

            </div>


            <!-- GRID -->
            <div
                id="reservationResults"
                class="row g-4 reservation-results-grid"
            ></div>

        </div>

    </section>

</div>
