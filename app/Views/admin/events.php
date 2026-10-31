
<?php

$pageHeaderIcon =
        'fa-solid fa-champagne-glasses nav-icon';

$pageHeaderEyebrow =
        'EVENTOS';

$pageHeaderTitle =
        'Solicitudes de eventos';

$pageHeaderDescription =
        'Gestiona las celebraciones solicitadas para las sedes Burger Town.';

$pageHeaderBadge =
        'Gestión de eventos';

?>

<div class="admin-module-page bt-admin-events">

    <?php
    require __DIR__ . '/components/page-header.php';
    ?>

    <!-- =========================================================
         KPI CARDS
    ========================================================== -->

    <div class="bt-events-stats">

        <div class="bt-event-stat">

            <div class="bt-event-stat-icon total">
                <i class="fa-solid fa-calendar-days"></i>
            </div>

            <div class="bt-event-stat-content">
                <span>Total solicitudes</span>
                <strong id="eventsTotal">0</strong>
            </div>

        </div>


        <div class="bt-event-stat">

            <div class="bt-event-stat-icon new">
                <i class="fa-solid fa-bell"></i>
            </div>

            <div class="bt-event-stat-content">
                <span>Nuevas</span>
                <strong id="eventsNew">0</strong>
            </div>

        </div>


        <div class="bt-event-stat">

            <div class="bt-event-stat-icon contacted">
                <i class="fa-solid fa-phone"></i>
            </div>

            <div class="bt-event-stat-content">
                <span>Contactadas</span>
                <strong id="eventsContacted">0</strong>
            </div>

        </div>


        <div class="bt-event-stat">

            <div class="bt-event-stat-icon confirmed">
                <i class="fa-solid fa-circle-check"></i>
            </div>

            <div class="bt-event-stat-content">
                <span>Confirmadas</span>
                <strong id="eventsConfirmed">0</strong>
            </div>

        </div>


        <div class="bt-event-stat">

            <div class="bt-event-stat-icon cancelled">
                <i class="fa-solid fa-circle-xmark"></i>
            </div>

            <div class="bt-event-stat-content">
                <span>Canceladas</span>
                <strong id="eventsCancelled">0</strong>
            </div>

        </div>

    </div>


    <!-- =========================================================
         TOOLBAR
    ========================================================== -->

    <div class="bt-events-toolbar">

        <div class="bt-events-search">

            <i class="fa-solid fa-magnifying-glass"></i>

            <input
                    type="search"
                    id="eventsSearch"
                    placeholder="Buscar cliente, correo, teléfono o sede..."
                    autocomplete="off"
            >

        </div>


        <select
                id="eventsStatusFilter"
                class="bt-events-filter"
        >
            <option value="">Todos los estados</option>
            <option value="new">Nuevas</option>
            <option value="contacted">Contactadas</option>
            <option value="confirmed">Confirmadas</option>
            <option value="cancelled">Canceladas</option>
        </select>


        <select
                id="eventsTypeFilter"
                class="bt-events-filter"
        >
            <option value="">Todos los eventos</option>
        </select>


        <button
                type="button"
                class="bt-events-refresh"
                id="eventsRefresh"
                title="Actualizar solicitudes"
        >
            <i class="fa-solid fa-rotate"></i>
            <span>Actualizar</span>
        </button>

    </div>


    <!-- =========================================================
         TABLE CARD
    ========================================================== -->

    <div class="bt-events-card">

        <div class="bt-events-card-header">

            <div class="bt-events-card-title">

                <div class="bt-events-card-icon">
                    <i class="fa-solid fa-calendar-check"></i>
                </div>

                <div>
                    <h2>Solicitudes recibidas</h2>

                    <span>
                        Gestiona y actualiza el estado de cada solicitud.
                    </span>
                </div>

            </div>


            <div class="bt-events-counter">
                <span id="eventsVisibleCount">0</span>
                solicitudes
            </div>

        </div>


        <!-- =====================================================
             TABLE
        ====================================================== -->

        <div class="bt-events-table-wrapper">

            <table
                    class="bt-events-table"
                    id="eventsTable"
            >

                <thead>

                <tr>

                    <th>Cliente</th>

                    <th>Evento</th>

                    <th>Sede</th>

                    <th>Fecha</th>

                    <th>Invitados</th>

                    <th>Estado</th>

                    <th class="text-end">
                        Acción
                    </th>

                </tr>

                </thead>


                <tbody>

                <tr>

                    <td
                            colspan="7"
                            class="bt-events-loading"
                    >

                        <div class="bt-events-loading-spinner">
                            <i class="fa-solid fa-circle-notch fa-spin"></i>
                        </div>

                        Cargando solicitudes...

                    </td>

                </tr>

                </tbody>

            </table>

        </div>

    </div>


    <!-- =========================================================
         DETAIL MODAL
    ========================================================== -->

    <div
            class="modal fade bt-event-modal"
            id="eventDetailModal"
            tabindex="-1"
            aria-hidden="true"
    >

        <div class="modal-dialog modal-dialog-centered modal-lg">

            <div class="modal-content">

                <div class="modal-header">

                    <div class="bt-modal-title">

                        <div class="bt-modal-title-icon">
                            <i class="fa-solid fa-calendar-star"></i>
                        </div>

                        <div>
                            <span>DETALLE DE SOLICITUD</span>
                            <h5>Evento Burger Town</h5>
                        </div>

                    </div>

                    <button
                            type="button"
                            class="btn-close"
                            data-bs-dismiss="modal"
                            aria-label="Cerrar"
                    ></button>

                </div>


                <div class="modal-body">

                    <div class="bt-event-detail-grid">

                        <div class="bt-event-detail">
                            <span>Cliente</span>
                            <strong id="detailName">—</strong>
                        </div>

                        <div class="bt-event-detail">
                            <span>Correo electrónico</span>
                            <strong id="detailEmail">—</strong>
                        </div>

                        <div class="bt-event-detail">
                            <span>Teléfono</span>
                            <strong id="detailPhone">—</strong>
                        </div>

                        <div class="bt-event-detail">
                            <span>Sede</span>
                            <strong id="detailBranch">—</strong>
                        </div>

                        <div class="bt-event-detail">
                            <span>Tipo de evento</span>
                            <strong id="detailType">—</strong>
                        </div>

                        <div class="bt-event-detail">
                            <span>Invitados</span>
                            <strong id="detailGuests">—</strong>
                        </div>

                        <div class="bt-event-detail">
                            <span>Fecha</span>
                            <strong id="detailDate">—</strong>
                        </div>

                        <div class="bt-event-detail">
                            <span>Hora</span>
                            <strong id="detailTime">—</strong>
                        </div>

                    </div>


                    <div class="bt-event-message-box">

                        <div class="bt-event-message-title">
                            <i class="fa-solid fa-message"></i>
                            Mensaje del cliente
                        </div>

                        <p id="detailMessage">
                            Sin mensaje adicional.
                        </p>

                    </div>

                </div>


                <div class="modal-footer">

                    <button
                            type="button"
                            class="bt-modal-close"
                            data-bs-dismiss="modal"
                    >
                        Cerrar
                    </button>

                </div>

            </div>

        </div>

    </div>

</div>