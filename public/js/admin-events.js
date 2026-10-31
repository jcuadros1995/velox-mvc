document.addEventListener('DOMContentLoaded', () => {

    const tableBody = document.querySelector('#eventsTable tbody');

    if (!tableBody) {
        return;
    }


    /* =========================================================
       ELEMENTOS
    ========================================================== */

    const searchInput = document.getElementById('eventsSearch');
    const statusFilter = document.getElementById('eventsStatusFilter');
    const typeFilter = document.getElementById('eventsTypeFilter');
    const refreshButton = document.getElementById('eventsRefresh');

    const totalElement = document.getElementById('eventsTotal');
    const newElement = document.getElementById('eventsNew');
    const contactedElement = document.getElementById('eventsContacted');
    const confirmedElement = document.getElementById('eventsConfirmed');
    const cancelledElement = document.getElementById('eventsCancelled');
    const visibleElement = document.getElementById('eventsVisibleCount');


    let events = [];


    /* =========================================================
       CARGAR EVENTOS
    ========================================================== */

    async function loadEvents() {

        tableBody.innerHTML = `
            <tr>
                <td colspan="7" class="bt-events-loading">

                    <div class="bt-events-loading-spinner">
                        <i class="fa-solid fa-circle-notch fa-spin"></i>
                    </div>

                    Cargando solicitudes...

                </td>
            </tr>
        `;


        try {

            const response = await fetch(
                `${window.APP_URL}/api/admin/events`,
                {
                    credentials: 'same-origin',
                    headers: {
                        'Accept': 'application/json'
                    }
                }
            );


            const data = await response.json();


            if (!response.ok || !data.success) {

                throw new Error(
                    data.message || 'No fue posible cargar los eventos.'
                );

            }


            events = Array.isArray(data.data)
                ? data.data
                : [];


            populateEventTypes();

            updateStats();

            renderEvents();


        } catch (error) {

            tableBody.innerHTML = `
                <tr>
                    <td colspan="7" class="bt-events-loading">

                        <div style="color:#ad1a19;font-size:1.3rem;margin-bottom:8px;">
                            <i class="fa-solid fa-triangle-exclamation"></i>
                        </div>

                        <strong style="color:#555;">
                            No fue posible cargar las solicitudes.
                        </strong>

                        <div style="font-size:.75rem;margin-top:5px;">
                            ${escapeHtml(error.message)}
                        </div>

                    </td>
                </tr>
            `;

        }

    }


    /* =========================================================
       TIPOS DE EVENTO
    ========================================================== */

    function populateEventTypes() {

        if (!typeFilter) {
            return;
        }


        const currentValue = typeFilter.value;


        const types = [
            ...new Set(
                events
                    .map(event => event.event_type)
                    .filter(Boolean)
            )
        ].sort();


        typeFilter.innerHTML = `
            <option value="">Todos los eventos</option>

            ${types.map(type => `
                <option value="${escapeAttribute(type)}">
                    ${escapeHtml(type)}
                </option>
            `).join('')}

        `;


        if (
            types.includes(currentValue)
        ) {
            typeFilter.value = currentValue;
        }

    }


    /* =========================================================
       ESTADÍSTICAS
    ========================================================== */

    function updateStats() {

        const total = events.length;

        const newCount =
            events.filter(
                event => event.status === 'new'
            ).length;

        const contactedCount =
            events.filter(
                event => event.status === 'contacted'
            ).length;

        const confirmedCount =
            events.filter(
                event => event.status === 'confirmed'
            ).length;

        const cancelledCount =
            events.filter(
                event => event.status === 'cancelled'
            ).length;


        animateNumber(totalElement, total);

        animateNumber(newElement, newCount);

        animateNumber(contactedElement, contactedCount);

        animateNumber(confirmedElement, confirmedCount);

        animateNumber(cancelledElement, cancelledCount);

    }


    /* =========================================================
       RENDER
    ========================================================== */

    function renderEvents() {

        const search =
            String(searchInput?.value || '')
                .trim()
                .toLowerCase();


        const status =
            statusFilter?.value || '';


        const type =
            typeFilter?.value || '';


        const filtered = events.filter(event => {

            const searchable = [
                event.name,
                event.email,
                event.phone,
                event.branch_name,
                event.event_type
            ]
                .filter(Boolean)
                .join(' ')
                .toLowerCase();


            const matchesSearch =
                !search ||
                searchable.includes(search);


            const matchesStatus =
                !status ||
                event.status === status;


            const matchesType =
                !type ||
                event.event_type === type;


            return (
                matchesSearch &&
                matchesStatus &&
                matchesType
            );

        });


        if (visibleElement) {

            visibleElement.textContent =
                filtered.length;

        }


        if (!filtered.length) {

            tableBody.innerHTML = `
                <tr>

                    <td
                        colspan="7"
                        class="bt-events-empty"
                    >

                        <div class="bt-events-empty-icon">
                            <i class="fa-solid fa-calendar-xmark"></i>
                        </div>

                        <strong>
                            No encontramos solicitudes
                        </strong>

                        <span>
                            Prueba cambiando los filtros de búsqueda.
                        </span>

                    </td>

                </tr>
            `;

            return;
        }


        tableBody.innerHTML =
            filtered
                .map(renderEventRow)
                .join('');


        bindRowEvents();

    }


    /* =========================================================
       FILA
    ========================================================== */

    function renderEventRow(event) {

        const initials =
            getInitials(event.name);


        const statusClass =
            getStatusClass(event.status);


        const statusLabel =
            getStatusLabel(event.status);


        const date =
            formatDate(event.event_date);


        const time =
            formatTime(event.event_time);


        return `

            <tr>

                <!-- CLIENTE -->

                <td>

                    <div class="bt-event-client">

                        <div class="bt-event-avatar">
                            ${escapeHtml(initials)}
                        </div>

                        <div class="bt-event-client-info">

                            <span class="bt-event-client-name">
                                ${escapeHtml(event.name)}
                            </span>

                            <span class="bt-event-client-contact">
                                ${escapeHtml(event.email || '')}
                            </span>

                        </div>

                    </div>

                </td>


                <!-- EVENTO -->

                <td>

                    <span class="bt-event-type">

                        <i class="fa-solid fa-champagne-glasses"></i>

                        ${escapeHtml(
            event.event_type || 'Evento'
        )}

                    </span>

                </td>


                <!-- SEDE -->

                <td>

                    <div class="bt-event-branch">

                        <i class="fa-solid fa-location-dot"></i>

                        ${escapeHtml(
            event.branch_name || '—'
        )}

                    </div>

                </td>


                <!-- FECHA -->

                <td>

                    <div class="bt-event-date">

                        <strong>
                            ${escapeHtml(date)}
                        </strong>

                        <span>
                            <i class="fa-regular fa-clock"></i>
                            ${escapeHtml(time)}
                        </span>

                    </div>

                </td>


                <!-- INVITADOS -->

                <td>

                    <span class="bt-event-guests">

                        <i class="fa-solid fa-users"></i>

                        ${escapeHtml(
            event.guests ?? '0'
        )}

                    </span>

                </td>


                <!-- ESTADO -->

                <td>

                    <select
                        class="bt-event-status ${statusClass}"
                        data-id="${Number(event.id)}"
                    >

                        <option
                            value="new"
                            ${event.status === 'new' ? 'selected' : ''}
                        >
                            Nueva
                        </option>

                        <option
                            value="contacted"
                            ${event.status === 'contacted' ? 'selected' : ''}
                        >
                            Contactada
                        </option>

                        <option
                            value="confirmed"
                            ${event.status === 'confirmed' ? 'selected' : ''}
                        >
                            Confirmada
                        </option>

                        <option
                            value="cancelled"
                            ${event.status === 'cancelled' ? 'selected' : ''}
                        >
                            Cancelada
                        </option>

                    </select>

                </td>


                <!-- ACCIÓN -->

                <td class="text-end">

                    <button
                        type="button"
                        class="bt-event-view"
                        data-event-id="${Number(event.id)}"
                        title="Ver detalles"
                    >

                        <i class="fa-solid fa-eye"></i>

                    </button>

                </td>

            </tr>

        `;

    }


    /* =========================================================
       EVENTOS DE FILA
    ========================================================== */

    function bindRowEvents() {

        document
            .querySelectorAll('.bt-event-status')
            .forEach(select => {

                select.addEventListener(
                    'change',
                    () => updateStatus(select)
                );

            });


        document
            .querySelectorAll('.bt-event-view')
            .forEach(button => {

                button.addEventListener(
                    'click',
                    () => {

                        const id =
                            Number(
                                button.dataset.eventId
                            );

                        openEventDetail(id);

                    }
                );

            });

    }


    /* =========================================================
       CAMBIAR ESTADO
    ========================================================== */

    async function updateStatus(select) {

        const id =
            Number(select.dataset.id);


        const status =
            select.value;


        const original =
            events.find(
                event => Number(event.id) === id
            )?.status;


        select.disabled = true;


        try {

            const response =
                await fetch(
                    `${window.APP_URL}/api/admin/events/status`,
                    {
                        method: 'POST',

                        credentials: 'same-origin',

                        headers: {
                            'Content-Type':
                                'application/json',

                            'Accept':
                                'application/json'
                        },

                        body: JSON.stringify({
                            id,
                            status
                        })
                    }
                );


            const data =
                await response.json();


            if (!response.ok || !data.success) {

                throw new Error(
                    data.message ||
                    'No fue posible actualizar el estado.'
                );

            }


            const event =
                events.find(
                    item => Number(item.id) === id
                );


            if (event) {

                event.status = status;

            }


            select.className =
                `bt-event-status ${getStatusClass(status)}`;


            updateStats();

            renderEvents();


            if (typeof showToast === 'function') {

                showToast(
                    'success',
                    'Estado actualizado correctamente.'
                );

            }


        } catch (error) {

            select.value =
                original || 'new';


            if (typeof showToast === 'function') {

                showToast(
                    'error',
                    error.message
                );

            }

        } finally {

            select.disabled = false;

        }

    }


    /* =========================================================
       MODAL
    ========================================================== */

    function openEventDetail(id) {

        const event =
            events.find(
                item => Number(item.id) === id
            );


        if (!event) {
            return;
        }


        setText(
            'detailName',
            event.name || '—'
        );

        setText(
            'detailEmail',
            event.email || '—'
        );

        setText(
            'detailPhone',
            event.phone || '—'
        );

        setText(
            'detailBranch',
            event.branch_name || '—'
        );

        setText(
            'detailType',
            event.event_type || '—'
        );

        setText(
            'detailGuests',
            event.guests
                ? `${event.guests} personas`
                : '—'
        );

        setText(
            'detailDate',
            formatDate(event.event_date)
        );

        setText(
            'detailTime',
            formatTime(event.event_time)
        );

        setText(
            'detailMessage',
            event.message ||
            'El cliente no dejó un mensaje adicional.'
        );


        const modalElement =
            document.getElementById(
                'eventDetailModal'
            );


        if (
            modalElement &&
            typeof bootstrap !== 'undefined'
        ) {

            bootstrap.Modal
                .getOrCreateInstance(modalElement)
                .show();

        }

    }


    /* =========================================================
       SEARCH / FILTERS
    ========================================================== */

    searchInput?.addEventListener(
        'input',
        renderEvents
    );


    statusFilter?.addEventListener(
        'change',
        renderEvents
    );


    typeFilter?.addEventListener(
        'change',
        renderEvents
    );


    refreshButton?.addEventListener(
        'click',
        async () => {

            refreshButton.classList.add('loading');

            refreshButton.disabled = true;

            try {

                await loadEvents();

            } finally {

                refreshButton.classList.remove(
                    'loading'
                );

                refreshButton.disabled = false;

            }

        }
    );


    /* =========================================================
       HELPERS
    ========================================================== */

    function setText(id, value) {

        const element =
            document.getElementById(id);


        if (element) {

            element.textContent =
                String(value ?? '—');

        }

    }


    function getInitials(name) {

        return String(name || 'BT')
            .trim()
            .split(/\s+/)
            .slice(0, 2)
            .map(word => word.charAt(0))
            .join('')
            .toUpperCase();

    }


    function getStatusClass(status) {

        return {
            new: 'status-new',
            contacted: 'status-contacted',
            confirmed: 'status-confirmed',
            cancelled: 'status-cancelled'
        }[status] || 'status-new';

    }


    function getStatusLabel(status) {

        return {
            new: 'Nueva',
            contacted: 'Contactada',
            confirmed: 'Confirmada',
            cancelled: 'Cancelada'
        }[status] || 'Nueva';

    }


    function formatDate(date) {

        if (!date) {
            return '—';
        }


        const parts =
            String(date).split('-');


        if (parts.length !== 3) {
            return String(date);
        }


        return `${parts[2]}/${parts[1]}/${parts[0]}`;

    }


    function formatTime(time) {

        if (!time) {
            return 'Sin hora';
        }


        return String(time).substring(0, 5);

    }


    function escapeHtml(value) {

        return String(value ?? '')
            .replace(
                /[&<>"']/g,
                char => ({
                    '&': '&amp;',
                    '<': '&lt;',
                    '>': '&gt;',
                    '"': '&quot;',
                    "'": '&#039;'
                }[char])
            );

    }


    function escapeAttribute(value) {

        return escapeHtml(value);

    }


    function animateNumber(element, target) {

        if (!element) {
            return;
        }


        element.textContent =
            Number(target).toLocaleString('es-CO');

    }


    /* =========================================================
       INIT
    ========================================================== */

    loadEvents();

});