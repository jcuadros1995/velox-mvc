/**
 * Velox MVC - Administración de reservas
 */
(function () {
    'use strict';

    const state = {
        loading: false
    };

    function getElement(id) {
        return document.getElementById(id);
    }

    function escapeHtml(value) {
        return String(value ?? '')
            .replace(/&/g, '&amp;')
            .replace(/</g, '&lt;')
            .replace(/>/g, '&gt;')
            .replace(/"/g, '&quot;')
            .replace(/'/g, '&#039;');
    }

    function statusLabel(status) {
        const labels = {
            pending: 'Pendiente',
            confirmed: 'Confirmada',
            checked_in: 'En atención',
            completed: 'Completada',
            cancelled: 'Cancelada',
            no_show: 'No asistió'
        };

        return labels[status] || status || 'Sin estado';
    }

    function statusClass(status) {
        return 'status-' + String(status || 'unknown')
            .toLowerCase()
            .replace(/[^a-z0-9_-]/g, '');
    }

    function formatDate(date) {
        if (!date) {
            return '-';
        }

        const parts = date.split('-');

        if (parts.length !== 3) {
            return date;
        }

        return `${parts[2]}/${parts[1]}/${parts[0]}`;
    }

    function getFilters() {
        const params = new URLSearchParams();

        const branch = getElement('reservationBranch');
        const from = getElement('reservationFrom');
        const to = getElement('reservationTo');
        const status = getElement('reservationStatus');
        const search = getElement('reservationSearch');

        if (branch && branch.value) {
            params.set('branch_id', branch.value);
        }

        if (from && from.value) {
            params.set('from', from.value);
        }

        if (to && to.value) {
            params.set('to', to.value);
        }

        if (status && status.value) {
            params.set('status', status.value);
        }

        if (search && search.value.trim()) {
            params.set('search', search.value.trim());
        }

        return params;
    }

    function renderStats(stats) {
        const values = {
            reservationStatTotal: stats.total || 0,
            reservationStatToday: stats.today || 0,
            reservationStatPending: stats.pending || 0,
            reservationStatConfirmed: stats.confirmed || 0,
            reservationStatCancelled: stats.cancelled || 0
        };

        Object.keys(values).forEach(function (id) {
            const element = getElement(id);

            if (element) {
                element.textContent =
                    Number(values[id]).toLocaleString('es-CO');
            }
        });
    }

    function renderRows(reservations) {
        const body = getElement('adminReservationsBody');

        if (!body) {
            return;
        }

        const superAdmin = Boolean(getElement('reservationBranch'));

        if (!reservations.length) {
            body.innerHTML = `
                <tr>
                    <td colspan="${superAdmin ? 8 : 7}" class="reservation-empty">
                        <i class="fa-regular fa-calendar-xmark"></i>
                        <strong>No hay reservas</strong>
                        <span>No encontramos reservas para los filtros seleccionados.</span>
                    </td>
                </tr>
            `;
            return;
        }

        body.innerHTML = reservations.map(function (reservation) {
            const customer = escapeHtml(
                reservation.customer_name || 'Cliente'
            );

            const email = escapeHtml(
                reservation.customer_email || ''
            );

            const phone = escapeHtml(
                reservation.customer_phone || ''
            );

            const branch = escapeHtml(
                reservation.branch_name || 'Sin sede'
            );

            return `
                <tr>
                    <td>
                        <span class="reservation-code">
                            ${escapeHtml(reservation.reservation_code)}
                        </span>
                    </td>
                    ${superAdmin ? `
                        <td>
                            <span class="reservation-branch">
                                ${branch}
                            </span>
                        </td>
                    ` : ''}
                    <td>
                        <div class="reservation-customer">
                            <strong>${customer}</strong>
                            <small>${email}${phone ? ' · ' + phone : ''}</small>
                        </div>
                    </td>
                    <td>${formatDate(reservation.reservation_date)}</td>
                    <td>
                        ${escapeHtml(reservation.start_time)}
                        -
                        ${escapeHtml(reservation.end_time)}
                    </td>
                    <td>
                        <span class="people-count">
                            <i class="fa-solid fa-users"></i>
                            ${Number(reservation.people_count || 0)}
                        </span>
                    </td>
                    <td>
                        <span class="reservation-status ${statusClass(reservation.status)}">
                            ${statusLabel(reservation.status)}
                        </span>
                    </td>
                    <td>
                        <span class="reservation-source">
                            ${escapeHtml(reservation.source || 'web')}
                        </span>
                    </td>
                </tr>
            `;
        }).join('');
    }

    async function loadReservations() {
        if (state.loading) {
            return;
        }

        state.loading = true;

        const body = getElement('adminReservationsBody');

        if (body) {
            const superAdmin = Boolean(getElement('reservationBranch'));
            body.innerHTML = `
                <tr>
                    <td colspan="${superAdmin ? 8 : 7}" class="reservation-empty">
                        <i class="fa-solid fa-spinner fa-spin"></i>
                        Cargando reservas...
                    </td>
                </tr>
            `;
        }

        try {
            const params = getFilters();

            const query = params.toString();

            const response = await fetch(
                `${window.APP_URL}/api/admin/reservations${query ? '?' + query : ''}`,
                {
                    method: 'GET',
                    headers: {
                        'Accept': 'application/json'
                    },
                    credentials: 'same-origin'
                }
            );

            const result = await response.json();

            if (!response.ok || !result.success) {
                throw new Error(
                    result.message || 'No fue posible cargar las reservas.'
                );
            }

            const data = result.data || {};

            renderStats(data.stats || {});
            renderRows(data.reservations || []);

            const counter = getElement('reservationResultCount');

            if (counter) {
                const total = (data.reservations || []).length;

                counter.textContent =
                    `${total.toLocaleString('es-CO')} reserva${total === 1 ? '' : 's'}`;
            }

        } catch (error) {
            console.error('Error cargando reservas administrativas:', error);

            if (body) {
                const superAdmin = Boolean(getElement('reservationBranch'));

                body.innerHTML = `
                    <tr>
                        <td colspan="${superAdmin ? 8 : 7}" class="reservation-empty reservation-error">
                            <i class="fa-solid fa-triangle-exclamation"></i>
                            <strong>No fue posible cargar las reservas.</strong>
                            <span>${escapeHtml(error.message)}</span>
                        </td>
                    </tr>
                `;
            }

            if (typeof window.showToast === 'function') {
                window.showToast(
                    'error',
                    error.message || 'No fue posible cargar las reservas.'
                );
            }
        } finally {
            state.loading = false;
        }
    }

    function init() {
        const applyButton = getElement('btnApplyReservationFilters');
        const refreshButton = getElement('btnRefreshReservations');
        const search = getElement('reservationSearch');

        if (applyButton) {
            applyButton.addEventListener('click', loadReservations);
        }

        if (refreshButton) {
            refreshButton.addEventListener('click', loadReservations);
        }

        if (search) {
            search.addEventListener('keydown', function (event) {
                if (event.key === 'Enter') {
                    event.preventDefault();
                    loadReservations();
                }
            });
        }

        loadReservations();
    }

    document.addEventListener('DOMContentLoaded', init);
})();
