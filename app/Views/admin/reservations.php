<?php
$user = Auth::user() ?? [];
$role = strtolower((string) ($user['role_name'] ?? ''));
$isSuperAdmin = $role === 'superadmin';
$branchName = $isSuperAdmin
    ? 'Todas las sedes'
    : ($user['branch_name'] ?? 'Sede no asignada');
?>

<div class="admin-reservations-page">

    <?php

    $pageHeaderIcon =
            'fa-solid fa-calendar-check';

    $pageHeaderEyebrow =
            'ADMINISTRACIÓN';

    $pageHeaderTitle =
            'Gestión de Reservas';

    $pageHeaderDescription =
            'Consulta, filtra y administra las reservas de tus sedes.';

    $pageHeaderBadge =
            'Operación de reservas';

    require __DIR__ .
            '/components/page-header.php';

    ?>

    <section class="reservation-stats-grid">
        <div class="reservation-stat-card">
            <div class="reservation-stat-icon"><i class="fa-solid fa-calendar-days"></i></div>
            <div><span>Total</span><strong id="reservationStatTotal">0</strong></div>
        </div>
        <div class="reservation-stat-card">
            <div class="reservation-stat-icon"><i class="fa-solid fa-calendar-day"></i></div>
            <div><span>Hoy</span><strong id="reservationStatToday">0</strong></div>
        </div>
        <div class="reservation-stat-card">
            <div class="reservation-stat-icon"><i class="fa-solid fa-clock"></i></div>
            <div><span>Pendientes</span><strong id="reservationStatPending">0</strong></div>
        </div>
        <div class="reservation-stat-card">
            <div class="reservation-stat-icon"><i class="fa-solid fa-circle-check"></i></div>
            <div><span>Confirmadas</span><strong id="reservationStatConfirmed">0</strong></div>
        </div>
        <div class="reservation-stat-card">
            <div class="reservation-stat-icon"><i class="fa-solid fa-ban"></i></div>
            <div><span>Canceladas</span><strong id="reservationStatCancelled">0</strong></div>
        </div>
    </section>

    <section class="admin-reservation-panel">

        <div class="reservation-filter-header">
            <div>
                <span class="panel-eyebrow">CONSULTA</span>
                <h3>Filtros de reservas</h3>
            </div>

            <button type="button" class="reservation-refresh-btn" id="btnRefreshReservations">
                <i class="fa-solid fa-arrows-rotate"></i>
                Actualizar
            </button>
        </div>

        <div class="reservation-filters">

            <?php if ($isSuperAdmin): ?>
                <div class="reservation-filter-field">
                    <label for="reservationBranch">Sede</label>
                    <select id="reservationBranch">
                        <option value="">Todas las sedes</option>
                        <?php foreach ($adminBranches as $branch): ?>
                            <option value="<?= (int) $branch['id'] ?>">
                                <?= htmlspecialchars($branch['name'], ENT_QUOTES, 'UTF-8') ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>
            <?php endif; ?>

            <div class="reservation-filter-field">
                <label for="reservationFrom">Desde</label>
                <input type="date" id="reservationFrom">
            </div>

            <div class="reservation-filter-field">
                <label for="reservationTo">Hasta</label>
                <input type="date" id="reservationTo">
            </div>

            <div class="reservation-filter-field">
                <label for="reservationStatus">Estado</label>
                <select id="reservationStatus">
                    <option value="">Todos</option>
                    <option value="pending">Pendiente</option>
                    <option value="confirmed">Confirmada</option>
                    <option value="checked_in">En atención</option>
                    <option value="completed">Completada</option>
                    <option value="cancelled">Cancelada</option>
                    <option value="no_show">No asistió</option>
                </select>
            </div>

            <div class="reservation-filter-field reservation-search-field">
                <label for="reservationSearch">Buscar</label>
                <div class="reservation-search-input">
                    <i class="fa-solid fa-magnifying-glass"></i>
                    <input
                        type="search"
                        id="reservationSearch"
                        placeholder="Código, cliente o correo..."
                    >
                </div>
            </div>

            <button type="button" class="reservation-apply-btn" id="btnApplyReservationFilters">
                <i class="fa-solid fa-filter"></i>
                Filtrar
            </button>
        </div>

    </section>

    <section class="admin-reservation-panel">

        <div class="reservation-table-header">
            <div>
                <span class="panel-eyebrow">RESULTADOS</span>
                <h3>Reservas registradas</h3>
            </div>
            <span class="reservation-result-count" id="reservationResultCount">0 reservas</span>
        </div>

        <div class="reservation-table-wrapper">
            <table class="reservation-admin-table">
                <thead>
                    <tr>
                        <th>Código</th>
                        <?php if ($isSuperAdmin): ?><th>Sede</th><?php endif; ?>
                        <th>Cliente</th>
                        <th>Fecha</th>
                        <th>Horario</th>
                        <th>Personas</th>
                        <th>Estado</th>
                        <th>Origen</th>
                    </tr>
                </thead>
                <tbody id="adminReservationsBody">
                    <tr>
                        <td
                            colspan="<?= $isSuperAdmin ? 8 : 7 ?>"
                            class="reservation-empty"
                        >
                            <i class="fa-solid fa-spinner fa-spin"></i>
                            Cargando reservas...
                        </td>
                    </tr>
                </tbody>
            </table>
        </div>

    </section>
</div>
