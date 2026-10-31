/**
 * VELOX MVC
 * Admin Notifications
 */

(function () {

    'use strict';


    const API_BASE =
        `${window.APP_URL}/api/admin/notifications`;


    const elements = {
        badge:
            document.getElementById(
                'notificationBadge'
            ),

        subtitle:
            document.getElementById(
                'notificationSubtitle'
            ),

        list:
            document.getElementById(
                'notificationsList'
            ),

        markAll:
            document.getElementById(
                'btnMarkAllNotifications'
            ),

        dropdown:
            document.getElementById(
                'notificationsDropdown'
            )
    };


    if (!elements.badge || !elements.list) {
        return;
    }


    /* =====================================================
       UTILIDADES
    ===================================================== */

    function escapeHtml(value) {

        const div =
            document.createElement('div');

        div.textContent =
            value ?? '';

        return div.innerHTML;
    }


    function formatRelativeTime(dateString) {

        if (!dateString) {
            return '';
        }

        const date =
            new Date(
                dateString.replace(' ', 'T')
            );

        if (Number.isNaN(date.getTime())) {
            return '';
        }

        const now =
            new Date();

        const seconds =
            Math.floor(
                (now.getTime() - date.getTime()) / 1000
            );

        if (seconds < 60) {
            return 'Ahora';
        }

        const minutes =
            Math.floor(seconds / 60);

        if (minutes < 60) {
            return `Hace ${minutes} min`;
        }

        const hours =
            Math.floor(minutes / 60);

        if (hours < 24) {
            return `Hace ${hours} h`;
        }

        const days =
            Math.floor(hours / 24);

        if (days < 7) {
            return `Hace ${days} día${days === 1 ? '' : 's'}`;
        }

        return date.toLocaleDateString(
            'es-CO',
            {
                day: '2-digit',
                month: 'short'
            }
        );
    }


    function getNotificationIcon(type) {

        switch (type) {

            case 'reservation_created':
                return 'bi-calendar-check';

            case 'reservation_cancelled':
                return 'bi-calendar-x';

            case 'user_created':
                return 'bi-person-plus';

            case 'security':
                return 'bi-shield-check';

            default:
                return 'bi-bell';
        }
    }


    function getReferenceLabel(notification) {

        if (
            notification.reference_type ===
            'reservation'
        ) {
            return 'Ver reserva';
        }

        return 'Ver detalle';
    }


    /* =====================================================
       BADGE
    ===================================================== */

    function updateBadge(count) {

        const total =
            Number(count || 0);

        if (total <= 0) {

            elements.badge.classList.add(
                'd-none'
            );

            elements.badge.classList.remove(
                'pulse'
            );

            elements.badge.textContent =
                '0';

        } else {

            elements.badge.classList.remove(
                'd-none'
            );

            elements.badge.classList.add(
                'pulse'
            );

            elements.badge.textContent =
                total > 99
                    ? '99+'
                    : String(total);
        }

        elements.subtitle.textContent =
            total > 0
                ? `${total} notificación${total === 1 ? '' : 'es'} sin leer`
                : 'Sin notificaciones nuevas';
    }


    /* =====================================================
       CONTADOR
    ===================================================== */

    async function loadUnreadCount() {

        try {

            const response =
                await fetch(
                    `${API_BASE}/unread-count`,
                    {
                        method: 'GET',
                        headers: {
                            'Accept':
                                'application/json'
                        },
                        credentials:
                            'same-origin'
                    }
                );

            const result =
                await response.json();

            if (
                !response.ok ||
                !result.success
            ) {
                return;
            }

            updateBadge(
                result.count
            );

        } catch (error) {

            console.error(
                'Notifications count:',
                error
            );
        }
    }


    /* =====================================================
       LISTA
    ===================================================== */

    async function loadNotifications() {

        elements.list.innerHTML = `
            <div class="notification-loading">
                <div
                    class="spinner-border spinner-border-sm"
                    role="status"
                ></div>

                <span>
                    Cargando notificaciones...
                </span>
            </div>
        `;


        try {

            const response =
                await fetch(
                    `${API_BASE}?limit=30`,
                    {
                        method: 'GET',
                        headers: {
                            'Accept':
                                'application/json'
                        },
                        credentials:
                            'same-origin'
                    }
                );


            const result =
                await response.json();


            if (
                !response.ok ||
                !result.success
            ) {
                throw new Error(
                    result.message ||
                    'No fue posible cargar las notificaciones.'
                );
            }


            renderNotifications(
                result.data || []
            );


        } catch (error) {

            console.error(
                'Notifications:',
                error
            );

            elements.list.innerHTML = `
                <div class="notification-empty">

                    <div class="notification-empty-icon">
                        <i class="bi bi-exclamation-circle"></i>
                    </div>

                    <div class="notification-empty-title">
                        No fue posible cargar
                    </div>

                    <div class="notification-empty-text">
                        Intenta nuevamente en unos segundos.
                    </div>

                </div>
            `;
        }
    }


    /* =====================================================
       RENDER
    ===================================================== */

    function renderNotifications(
        notifications
    ) {

        if (!notifications.length) {

            elements.list.innerHTML = `
                <div class="notification-empty">

                    <div class="notification-empty-icon">
                        <i class="bi bi-bell-slash"></i>
                    </div>

                    <div class="notification-empty-title">
                        Todo está al día
                    </div>

                    <div class="notification-empty-text">
                        No tienes notificaciones pendientes.
                    </div>

                </div>
            `;

            elements.markAll.disabled = true;

            return;
        }


        elements.markAll.disabled = false;


        elements.list.innerHTML =
            notifications.map(
                renderNotification
            ).join('');


        elements.list
            .querySelectorAll(
                '[data-notification-id]'
            )
            .forEach(
                item => {

                    item.addEventListener(
                        'click',
                        () => {

                            handleNotificationClick(
                                item
                            );

                        }
                    );
                }
            );
    }


    function renderNotification(
        notification
    ) {

        const id =
            Number(notification.id);

        const unread =
            Number(notification.is_read) === 0;

        const icon =
            getNotificationIcon(
                notification.type
            );

        const time =
            formatRelativeTime(
                notification.created_at
            );

        const title =
            escapeHtml(
                notification.title
            );

        const message =
            escapeHtml(
                notification.message
            );

        const reference =
            notification.reference_id
                ? `
                    <div class="notification-item-reference">
                        <i class="bi bi-arrow-right-circle"></i>
                        ${escapeHtml(
                    getReferenceLabel(
                        notification
                    )
                )}
                    </div>
                  `
                : '';


        return `
            <div
                class="notification-item ${unread ? 'unread' : ''}"
                data-notification-id="${id}"
                data-reference-type="${escapeHtml(
            notification.reference_type || ''
        )}"
                data-reference-id="${Number(
            notification.reference_id || 0
        )}"
            >

                <div class="notification-item-icon">
                    <i class="bi ${icon}"></i>
                </div>


                <div class="notification-item-content">

                    <div class="notification-item-top">

                        <div class="notification-item-title">
                            ${title}
                        </div>

                        <div class="notification-item-time">
                            ${escapeHtml(time)}
                        </div>

                    </div>


                    <div class="notification-item-message">
                        ${message}
                    </div>

                    ${reference}

                </div>

            </div>
        `;
    }


    /* =====================================================
       CLICK
    ===================================================== */

    async function handleNotificationClick(
        element
    ) {

        const id =
            Number(
                element.dataset.notificationId
            );

        const referenceType =
            element.dataset.referenceType;

        const referenceId =
            Number(
                element.dataset.referenceId || 0
            );


        await markAsRead(id);


        if (
            referenceType ===
            'reservation' &&
            referenceId > 0
        ) {

            window.location.href =
                `${window.APP_URL}/admin/reservations`;

            return;
        }


        element.classList.remove(
            'unread'
        );
    }


    /* =====================================================
       MARCAR UNA
    ===================================================== */

    async function markAsRead(id) {

        if (!id) {
            return;
        }

        try {

            const body =
                new URLSearchParams();

            body.set(
                'id',
                String(id)
            );


            const response =
                await fetch(
                    `${API_BASE}/read`,
                    {
                        method: 'POST',
                        headers: {
                            'Accept':
                                'application/json',
                            'Content-Type':
                                'application/x-www-form-urlencoded; charset=UTF-8'
                        },
                        body:
                            body.toString(),
                        credentials:
                            'same-origin'
                    }
                );


            const result =
                await response.json();


            if (
                response.ok &&
                result.success
            ) {
                await loadUnreadCount();
            }

        } catch (error) {

            console.error(
                'Mark notification:',
                error
            );
        }
    }


    /* =====================================================
       MARCAR TODAS
    ===================================================== */

    async function markAllAsRead() {

        try {

            elements.markAll.disabled =
                true;


            const response =
                await fetch(
                    `${API_BASE}/read-all`,
                    {
                        method: 'POST',
                        headers: {
                            'Accept':
                                'application/json'
                        },
                        credentials:
                            'same-origin'
                    }
                );


            const result =
                await response.json();


            if (
                !response.ok ||
                !result.success
            ) {
                throw new Error(
                    result.message ||
                    'No fue posible actualizar las notificaciones.'
                );
            }


            await loadUnreadCount();

            await loadNotifications();


        } catch (error) {

            console.error(
                'Mark all notifications:',
                error
            );

            elements.markAll.disabled =
                false;
        }
    }


    /* =====================================================
       EVENTS
    ===================================================== */

    elements.dropdown?.addEventListener(
        'show.bs.dropdown',
        function () {

            loadNotifications();

        }
    );


    elements.markAll?.addEventListener(
        'click',
        function (event) {

            event.preventDefault();
            event.stopPropagation();

            markAllAsRead();

        }
    );


    /* =====================================================
       INIT
    ===================================================== */

    loadUnreadCount();


    /*
     * Actualiza el contador periódicamente.
     * No recarga el dropdown si está cerrado.
     */
    setInterval(
        loadUnreadCount,
        30000
    );

})();