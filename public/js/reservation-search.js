document.addEventListener('DOMContentLoaded', () => {

    const form = document.getElementById('reservationSearchForm');

    if (!form) {
        return;
    }


    /* =========================================================
       ELEMENTOS
    ========================================================= */

    const searchInput = document.getElementById(
        'reservationSearchInput'
    );

    const searchError = document.getElementById('reservationSearchError');
    const emailInput = document.getElementById('reservationSearchEmail');
    const emailError = document.getElementById('reservationSearchEmailError');

    const searchButton = document.getElementById(
        'searchReservationButton'
    );

    const buttonContent = document.getElementById(
        'searchButtonContent'
    );

    const buttonLoading = document.getElementById(
        'searchButtonLoading'
    );

    const message = document.getElementById(
        'reservationSearchMessage'
    );

    const resultsHeader = document.getElementById(
        'reservationResultsHeader'
    );

    const resultsCount = document.getElementById(
        'reservationResultsCount'
    );

    const resultsContainer = document.getElementById(
        'reservationResults'
    );

    const wrapper = document.getElementById(
        'reservationSearchWrapper'
    );

    const resultsSection = document.getElementById(
        'reservationResultsSection'
    );


    /* =========================================================
       TOAST
    ========================================================= */

    function showToast(
        text,
        icon = 'warning'
    ) {

        /*
         * Si existe una configuración global de Toast,
         * intentamos utilizarla.
         */
        if (
            typeof Swal !== 'undefined'
        ) {

            Swal.fire({

                toast: true,

                position: 'top-end',

                icon: icon,

                title: text,

                showConfirmButton: false,

                timer: 3000,

                timerProgressBar: true,

                background: '#101d2e',

                color: '#ffffff',

                iconColor:
                    icon === 'error'
                        ? '#ff6b7a'
                        : '#f5c451'

            });

            return;
        }


        /*
         * Fallback por si SweetAlert no está cargado.
         */
        console.warn(text);
    }


    /* =========================================================
       VALIDACIÓN
    ========================================================= */

    function validateSearch(code, email) {
        code = code.trim().toUpperCase();
        email = email.trim().toLowerCase();

        searchInput.classList.remove('is-invalid', 'is-valid');
        emailInput.classList.remove('is-invalid', 'is-valid');
        searchError.textContent = '';
        emailError.textContent = '';

        const codeValid = /^RSV-[A-Z0-9]{6}$/.test(code);
        const emailValid = /^[^\\s@]+@[^\\s@]+\\.[^\\s@]+$/.test(email);
        let valid = true;

        if (!codeValid) {
            searchInput.classList.add('is-invalid');
            searchError.textContent = 'Ingresa un código de reserva válido (ej. RSV-1FB7BF).';
            valid = false;
        } else {
            searchInput.classList.add('is-valid');
        }

        if (!emailValid) {
            emailInput.classList.add('is-invalid');
            emailError.textContent = 'Ingresa el correo electrónico asociado a la reserva.';
            valid = false;
        } else {
            emailInput.classList.add('is-valid');
        }

        if (!valid) {
            showToast('Ingresa un código de reserva y un correo electrónico válidos.', 'warning');
        }

        return valid;
    }

    /* =========================================================
       LOADING
    ========================================================= */

    function setLoading(isLoading) {

        searchButton.disabled = isLoading;


        if (isLoading) {

            buttonContent.classList.add(
                'd-none'
            );

            buttonLoading.classList.remove(
                'd-none'
            );

        } else {

            buttonContent.classList.remove(
                'd-none'
            );

            buttonLoading.classList.add(
                'd-none'
            );
        }
    }


    /* =========================================================
       MENSAJE DE RESULTADOS
    ========================================================= */

    function showMessage(
        text,
        type = 'info'
    ) {

        message.className =
            'reservation-search-message';


        message.classList.add(
            `reservation-search-message-${type}`
        );


        const icon =
            type === 'error'
                ? 'fa-circle-exclamation'
                : 'fa-magnifying-glass';


        message.innerHTML = `

<i class="fa-solid ${icon}"></i>

<span>
                ${escapeHtml(text)}
            </span>

    `;

    }


    function hideMessage() {

        message.className =
            'reservation-search-message d-none';

        message.innerHTML = '';
    }


    /* =========================================================
       ESCAPAR HTML
    ========================================================= */

    function escapeHtml(value) {

        const div =
            document.createElement('div');

        div.textContent =
            value ?? '';

        return div.innerHTML;
    }


    /* =========================================================
       FORMATEAR FECHA
    ========================================================= */

    function formatDate(dateString) {

        if (!dateString) {
            return '-';
        }


        const date = new Date(
            `${dateString}T00:00:00`
        );


        if (
            Number.isNaN(
                date.getTime()
            )
        ) {

            return dateString;
        }


        return date.toLocaleDateString(
            'es-CO',
            {
                day: '2-digit',
                month: 'long',
                year: 'numeric'
            }
        );
    }


    /* =========================================================
       ESTADO DE RESERVA
    ========================================================= */

    function getStatusInfo(status) {

        const statuses = {

            pending: {
                label: 'Pendiente',
                class: 'warning',
                icon: 'fa-clock'
            },

            confirmed: {
                label: 'Confirmada',
                class: 'success',
                icon: 'fa-circle-check'
            },

            checked_in: {
                label: 'En curso',
                class: 'primary',
                icon: 'fa-user-check'
            },

            completed: {
                label: 'Completada',
                class: 'secondary',
                icon: 'fa-check-double'
            },

            cancelled: {
                label: 'Cancelada',
                class: 'danger',
                icon: 'fa-circle-xmark'
            },

            no_show: {
                label: 'No asistió',
                class: 'dark',
                icon: 'fa-user-xmark'
            }

        };


        return statuses[status] || {

            label:
                status ||
                'Desconocido',

            class: 'secondary',

            icon: 'fa-circle-info'

        };
    }


    /* =========================================================
       RENDER RESULTADOS
    ========================================================= */

    function renderResults(
        reservations,
        searchValue
    ) {

        resultsContainer.innerHTML = '';


        /*
         * =====================================================
         * SIN RESULTADOS
         * =====================================================
         */

        if (
            !Array.isArray(reservations) ||
            reservations.length === 0
        ) {

            resultsHeader.classList.add(
                'd-none'
            );


            /*
             * Mostramos el mensaje debajo
             * del buscador.
             */
            showMessage(
                `No se encontraron resultados para la búsqueda "${searchValue}".`,
                'info'
            );


            /*
             * Dejamos la sección visible.
             */
            wrapper?.classList.add(
                'search-completed'
            );

            resultsSection?.classList.add(
                'results-visible'
            );


            /*
             * No mostramos Toast aquí para evitar
             * duplicar el mensaje.
             */
            return;
        }


        /*
         * =====================================================
         * CON RESULTADOS
         * =====================================================
         */

        hideMessage();


        resultsHeader.classList.remove(
            'd-none'
        );


        resultsCount.textContent =
            reservations.length === 1
                ? '1 reserva'
                : `${reservations.length} reservas`;


        reservations.forEach(
            reservation => {

                const status =
                    getStatusInfo(
                        reservation.status
                    );


                const customerName =
                    reservation.customer_name ||
                    `${reservation.customer_first_name || ''} ${reservation.customer_last_name || ''}`
                        .trim() ||
                    'Cliente';


                const card =
                    document.createElement('div');


                /*
                 * Máximo 4 columnas.
                 */
                card.className =
                    'col-12 col-md-6 col-xl-3';


                card.innerHTML = `

<article class="reservation-result-card">

    <!-- TOP -->

    <div class="reservation-result-card-top">

    <span
class="reservation-result-code"
    >
    ${escapeHtml(
    reservation.reservation_code
)}
</span>


<span
    class="
                                    reservation-result-status
                                    reservation-status-${status.class}
                                "
>

                                <i
                                    class="
                                        fa-solid
                                        ${status.icon}
                                    "
                                ></i>

                                ${escapeHtml(
    status.label
)}

                            </span>

</div>


<!-- ICON -->

<div
    class="reservation-result-icon"
>

    <i
        class="
                                    fa-solid
                                    fa-calendar-check
                                "
    ></i>

</div>


<!-- MAIN -->

<div
    class="reservation-result-main"
>

    <h3>
        ${escapeHtml(
        formatDate(
            reservation.reservation_date
        )
    )}
    </h3>


    <p
        class="
                                    reservation-result-time
                                "
    >

        <i
            class="
                                        fa-regular
                                        fa-clock
                                    "
        ></i>

        ${escapeHtml(
        reservation.start_time
    )}

        -

        ${escapeHtml(
        reservation.end_time
    )}

    </p>

</div>


<!-- DETAILS -->

<div
    class="
                                reservation-result-details
                            "
>

    <!-- SEDE -->

    <div
        class="
                                    reservation-result-detail
                                "
    >

        <i
            class="
                                        fa-solid
                                        fa-location-dot
                                    "
        ></i>


        <div>

            <small>
                Sede
            </small>

            <strong>
                ${escapeHtml(
                reservation.branch_name
            )}
            </strong>

        </div>

    </div>


    <!-- PERSONAS -->

    <div
        class="
                                    reservation-result-detail
                                "
    >

        <i
            class="
                                        fa-solid
                                        fa-users
                                    "
        ></i>


        <div>

            <small>
                Personas
            </small>

            <strong>
                ${escapeHtml(
                String(
                    reservation.people_count
                )
            )}
            </strong>

        </div>

    </div>


    <!-- CLIENTE -->

    <div
        class="
                                    reservation-result-detail
                                "
    >

        <i
            class="
                                        fa-solid
                                        fa-user
                                    "
        ></i>


        <div>

            <small>
                Reserva a nombre de
            </small>

            <strong>
                ${escapeHtml(
                customerName
            )}
            </strong>

        </div>

    </div>

</div>
<!-- CANCELAR -->

${getCancellationButton(reservation)}

<!-- FOOTER -->

<div
    class="
                                reservation-result-footer
                            "
>

                            <span>

                                <i
                                    class="
                                        fa-solid
                                        fa-envelope
                                    "
                                ></i>

                                ${escapeHtml(
                                reservation.customer_email
                            )}

                            </span>

</div>

</article>

`;


                resultsContainer.appendChild(
                    card
                );

            }
        );


        /*
         * =====================================================
         * ANIMACIÓN
         * =====================================================
         */

        requestAnimationFrame(() => {

            wrapper?.classList.add(
                'search-completed'
            );

            resultsSection?.classList.add(
                'results-visible'
            );

        });

    }


    /* =========================================================
       CONSULTAR API
    ========================================================= */

    async function searchReservations(code, email) {
        const params = new URLSearchParams();
        params.set('reservation_code', code.trim().toUpperCase());
        params.set('email', email.trim().toLowerCase());

        const url = `${window.APP_URL}/api/public/reservations/search?${params.toString()}`;
        const response = await fetch(url, {
            method: 'GET',
            headers: { 'Accept': 'application/json' }
        });

        const contentType = response.headers.get('content-type') || '';
        if (!contentType.includes('application/json')) {
            const responseText = await response.text();
            console.error('Respuesta no JSON del servidor:', responseText);
            throw new Error('El servidor devolvió una respuesta inesperada.');
        }

        const data = await response.json();
        if (!response.ok || !data.success) {
            throw new Error(data.message || 'No fue posible consultar la reserva.');
        }

        renderResults(data.data || [], code.trim().toUpperCase());
    }

    /* =========================================================
       SUBMIT
    ========================================================= */

    form.addEventListener('submit', async event => {
        event.preventDefault();

        const code = searchInput.value.trim();
        const email = emailInput.value.trim();

        if (code === '' || email === '') {
            showToast('Completa el código de reserva y el correo electrónico.', 'warning');
            if (code === '') searchInput.focus();
            else emailInput.focus();
            return;
        }

        if (!validateSearch(code, email)) {
            if (!/^RSV-[A-Z0-9]{6}$/i.test(code)) searchInput.focus();
            else emailInput.focus();
            return;
        }

        setLoading(true);
        resultsContainer.innerHTML = '';
        resultsHeader.classList.add('d-none');
        hideMessage();

        try {
            await searchReservations(code, email);
        } catch (error) {
            console.error('Reservation search error:', error);
            showMessage(error.message || 'No fue posible consultar la reserva.', 'error');
        } finally {
            setLoading(false);
        }
    });

    /* =========================================================
       INPUT
    ========================================================= */

    [searchInput, emailInput].forEach(input => {
        input.addEventListener('input', () => {
            input.classList.remove('is-invalid', 'is-valid');
            searchError.textContent = '';
            emailError.textContent = '';
            hideMessage();
        });
    });

    searchInput.addEventListener('blur', () => {
        if (searchInput.value.trim() !== '' || emailInput.value.trim() !== '') {
            validateSearch(searchInput.value, emailInput.value);
        }
    });

    emailInput.addEventListener('blur', () => {
        if (searchInput.value.trim() !== '' || emailInput.value.trim() !== '') {
            validateSearch(searchInput.value, emailInput.value);
        }
    });

    /* =========================================================
       BOTÓN DE CANCELACIÓN
    ========================================================= */

    function getCancellationButton(reservation) {

        /*
         * Solo permitimos cancelar:
         *
         * pending
         * confirmed
         */

        if (
            reservation.status !== 'pending' &&
            reservation.status !== 'confirmed'
        ) {
            return '';
        }


        return `
    
        <div class="reservation-result-cancel">
    
            <button
        type="button"
        class="reservation-cancel-button"
        data-reservation-code="${escapeHtml(
        reservation.reservation_code
    )}"
    data-customer-email="${escapeHtml(
    reservation.customer_email
    )}"
    >
    
    <i class="fa-solid fa-ban"></i>
    
    Cancelar reserva
    
    </button>
    
    </div>
    
    `;
    }



    /* =========================================================
       CANCELAR RESERVA
    ========================================================= */

    async function cancelReservation(
        reservationCode,
        email,
        button
    ) {

        /*
         * Confirmación.
         */
        const confirmation =
            await Swal.fire({

                icon: 'warning',

                title: '¿Cancelar reserva?',

                html: `
        <p class="mb-2">
            Estás a punto de cancelar la reserva
        </p>
    
        <strong>
            ${escapeHtml(reservationCode)}
        </strong>
    
        <p class="mt-3 mb-0 text-muted">
            Esta acción cambiará el estado de la reserva
            a <strong>Cancelada</strong>.
        </p>
            `,

                showCancelButton: true,

                confirmButtonText:
                    'Sí, cancelar reserva',

                cancelButtonText:
                    'No, mantener reserva',

                reverseButtons: true,

                focusCancel: true

            });


        /*
         * Usuario canceló la confirmación.
         */
        if (
            !confirmation.isConfirmed
        ) {
            return;
        }


        /*
         * Loading del botón.
         */
        const originalHtml =
            button.innerHTML;


        button.disabled = true;


        button.innerHTML = `
    
        <span
        class="spinner-border spinner-border-sm me-2"
        role="status"
        aria-hidden="true"
            ></span>
    
        Cancelando...
    
        `;


        try {

            /*
             * =====================================================
             * API
             * =====================================================
             */

            const response =
                await fetch(
                    `${window.APP_URL}/api/public/reservations/cancel`,
                    {

                        method: 'POST',

                        headers: {

                            'Content-Type':
                                'application/json',

                            'Accept':
                                'application/json'
                        },

                        body: JSON.stringify({

                            reservation_code:
                                reservationCode,

                            email:
                                email

                        })

                    }
                );


            /*
             * Validar respuesta JSON.
             */
            const contentType =
                response.headers.get(
                    'content-type'
                ) || '';


            if (
                !contentType.includes(
                    'application/json'
                )
            ) {

                const text =
                    await response.text();


                console.error(
                    'Respuesta no JSON:',
                    text
                );


                throw new Error(
                    'El servidor devolvió una respuesta inesperada.'
                );
            }


            const data =
                await response.json();


            if (
                !response.ok ||
                !data.success
            ) {

                throw new Error(
                    data.message ||
                    'No fue posible cancelar la reserva.'
                );
            }


            /*
             * =====================================================
             * ACTUALIZAR TARJETA
             * =====================================================
             */

            updateReservationCardAsCancelled(
                button
            );


            /*
             * =====================================================
             * TOAST
             * =====================================================
             */

            Swal.fire({

                toast: true,

                position: 'top-end',

                icon: 'success',

                title:
                    'Reserva cancelada correctamente',

                showConfirmButton: false,

                timer: 3500,

                timerProgressBar: true,

                background: '#101d2e',

                color: '#ffffff',

                iconColor: '#38d39f'

            });


        } catch (error) {

            console.error(
                'Reservation cancellation error:',
                error
            );


            /*
             * Restaurar botón.
             */
            button.disabled = false;

            button.innerHTML =
                originalHtml;


            /*
             * Mostrar error.
             */
            Swal.fire({

                toast: true,

                position: 'top-end',

                icon: 'error',

                title:
                    error.message ||
                    'No fue posible cancelar la reserva.',

                showConfirmButton: false,

                timer: 4000,

                timerProgressBar: true,

                background: '#101d2e',

                color: '#ffffff',

                iconColor: '#ff6b7a'

            });

        }
    }

    /* =========================================================
       ACTUALIZAR TARJETA COMO CANCELADA
    ========================================================= */

    function updateReservationCardAsCancelled(
        button
    ) {

        /*
         * Encontramos la tarjeta.
         */
        const card =
            button.closest(
                '.reservation-result-card'
            );


        if (!card) {
            return;
        }


        /*
         * =====================================================
         * CAMBIAR BADGE DE ESTADO
         * =====================================================
         */

        const statusBadge =
            card.querySelector(
                '.reservation-result-status'
            );


        if (statusBadge) {

            statusBadge.className =
                `
        reservation-result-status
        reservation-status-danger
            `;

            statusBadge.innerHTML = `
    
        <i
        class="
        fa-solid
        fa-circle-xmark
        "
        ></i>
    
        Cancelada
    
            `;
        }


        /*
         * =====================================================
         * REEMPLAZAR BOTÓN
         * =====================================================
         */

        const cancelContainer =
            button.closest(
                '.reservation-result-cancel'
            );


        if (cancelContainer) {

            cancelContainer.innerHTML = `
    
        <div
        class="
        reservation-cancelled-label
        "
        >
    
        <i
        class="
        fa-solid
        fa-circle-check
        "
        ></i>
    
        Reserva cancelada
    
        </div>
    
            `;
        }


        /*
         * =====================================================
         * CAMBIAR ICONO PRINCIPAL
         * =====================================================
         */

        const mainIcon =
            card.querySelector(
                '.reservation-result-icon i'
            );


        if (mainIcon) {

            mainIcon.className =
                'fa-solid fa-calendar-xmark';

        }


        /*
         * =====================================================
         * MARCAR TARJETA
         * =====================================================
         */

        card.classList.add(
            'reservation-card-cancelled'
        );

    }

    document.addEventListener(
        'click',
        event => {

            const button =
                event.target.closest(
                    '.reservation-cancel-button'
                );


            if (!button) {
                return;
            }


            const reservationCode =
                button.dataset.reservationCode;


            const email =
                button.dataset.customerEmail;


            if (
                !reservationCode ||
                !email
            ) {

                return;
            }


            cancelReservation(
                reservationCode,
                email,
                button
            );

        }
    );

});

