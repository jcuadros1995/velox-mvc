document.addEventListener('DOMContentLoaded', () => {

    /*
    |--------------------------------------------------------------------------
    | Esta página no corresponde al módulo de reservas.
    |--------------------------------------------------------------------------
    */

    const reservationForm = document.getElementById('bookingForm');

    if (!reservationForm) {
        return;
    }


    const API_BASE = window.APP_URL;

    const branchSelect =
        document.getElementById('branch');

    const dateInput =
        document.getElementById('reservationDate');

    const peopleValue =
        document.getElementById('peopleValue');

    const availabilityContainer =
        document.getElementById('availabilityContainer');

    const availabilityContent =
        document.getElementById('availabilityContent');

    const availabilityInfo =
        document.getElementById('availabilityInfo');

    const reservationSummary =
        document.getElementById('reservationSummary');

    const customerSection =
        document.getElementById('customerSection');

    const successContainer =
        document.getElementById('successContainer');

    const bookingForm =
        document.getElementById('bookingForm');

    let people = 2;
    let selectedSlot = null;
    let branches = [];
    let selectedBranch = null;

    /* ==========================================================
       WIZARD NAVIGATION
    ========================================================== */

    const btnBackToStep1 =
        document.getElementById(
            'btnBackToStep1'
        );


    const btnBackToStep2 =
        document.getElementById(
            'btnBackToStep2'
        );


    if (btnBackToStep1) {

        btnBackToStep1.addEventListener(
            'click',
            goBackToStep1
        );
    }


    if (btnBackToStep2) {

        btnBackToStep2.addEventListener(
            'click',
            goBackToStep2
        );
    }

    /* ==========================================================
       INIT
    ========================================================== */

    initializeDate();
    loadBranches();

    branchSelect.addEventListener(
        'change',
        () => {

            const branchId =
                Number(
                    branchSelect.value
                );

            if (!branchId) {

                selectedBranch = null;

                clearBranchInformation();
                clearBusinessHours();

                return;
            }

            selectedBranch =
                branches.find(
                    branch =>
                        Number(branch.id) ===
                        branchId
                ) || null;

            renderBranchInformation(
                selectedBranch
            );

            loadBusinessHours(
                branchId
            );
        }
    );

    /* ==========================================================
   INFORMACIÓN DE LA SEDE
========================================================== */

    function renderBranchInformation(
        branch
    ) {

        const container =
            document.getElementById(
                'branchInformation'
            );

        if (!container) {
            return;
        }

        if (!branch) {

            clearBranchInformation();

            return;
        }


        const name =
            document.getElementById(
                'branchInformationName'
            );

        const code =
            document.getElementById(
                'branchInformationCode'
            );

        const address =
            document.getElementById(
                'branchInformationAddress'
            );

        const phone =
            document.getElementById(
                'branchInformationPhone'
            );

        const email =
            document.getElementById(
                'branchInformationEmail'
            );


        if (name) {

            name.textContent =
                branch.name ||
                'Sede';
        }


        if (code) {

            code.textContent =
                branch.code ||
                'Sin código';
        }


        if (address) {

            address.textContent =
                branch.address ||
                'Sin dirección';
        }


        if (phone) {

            phone.textContent =
                branch.phone ||
                'Sin teléfono';
        }


        if (email) {

            email.textContent =
                branch.email ||
                'Sin correo electrónico';
        }


        container.hidden =
            false;
    }


    function clearBranchInformation() {

        const container =
            document.getElementById(
                'branchInformation'
            );

        if (!container) {
            return;
        }


        container.hidden =
            true;


        const fields = [
            'branchInformationName',
            'branchInformationCode',
            'branchInformationAddress',
            'branchInformationPhone',
            'branchInformationEmail'
        ];


        fields.forEach(
            id => {

                const element =
                    document.getElementById(id);

                if (element) {
                    element.textContent = '-';
                }
            }
        );
    }

    async function loadBusinessHours(
        branchId
    ) {

        const container =
            document.getElementById(
                'branchBusinessHoursList'
            );

        if (!container) {
            return;
        }


        container.innerHTML = `
        <div class="public-hours-loading">
            <i class="fa-solid fa-spinner fa-spin"></i>
            Consultando horarios...
        </div>
    `;


        try {

            const response =
                await fetch(
                    `${API_BASE}/api/public/branches/hours` +
                    `?branch_id=${encodeURIComponent(branchId)}`,
                    {
                        headers: {
                            'Accept':
                                'application/json'
                        }
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
                    'No fue posible consultar los horarios.'
                );
            }


            renderBusinessHours(
                result.data || []
            );

        } catch (error) {

            console.error(
                '[BUSINESS HOURS]',
                error
            );

            container.innerHTML = `
            <div class="public-hours-error">
                No fue posible consultar los horarios.
            </div>
        `;
        }
    }


    function renderBusinessHours(
        hours
    ) {

        const container =
            document.getElementById(
                'branchBusinessHoursList'
            );

        if (!container) {
            return;
        }


        const dayNames = [
            '',
            'Lunes',
            'Martes',
            'Miércoles',
            'Jueves',
            'Viernes',
            'Sábado',
            'Domingo'
        ];


        const rows =
            [...hours]
                .sort(
                    (a, b) =>
                        Number(a.day_of_week) -
                        Number(b.day_of_week)
                )
                .map(
                    hour => {

                        const closed =
                            Number(
                                hour.is_closed
                            ) === 1 ||
                            hour.is_closed === true;


                        return `
                        <div class="public-hours-row">

                            <span>
                                ${
                            escapeHtml(
                                dayNames[
                                    Number(
                                        hour.day_of_week
                                    )
                                    ] || ''
                            )
                        }
                            </span>

                            <strong>
                                ${
                            closed
                                ? 'Cerrado'
                                : `${formatPublicTime(
                                    hour.open_time
                                )} - ${formatPublicTime(
                                    hour.close_time
                                )}`
                        }
                            </strong>

                        </div>
                    `;
                    }
                )
                .join('');


        container.innerHTML =
            rows ||
            `
            <div class="public-hours-error">
                No hay horarios configurados.
            </div>
        `;
    }


    function clearBusinessHours() {

        const container =
            document.getElementById(
                'branchBusinessHoursList'
            );

        if (!container) {
            return;
        }

        container.innerHTML = `
        <div class="public-hours-loading">
            Selecciona una sede para consultar sus horarios.
        </div>
    `;
    }


    function formatPublicTime(
        time
    ) {

        if (!time) {
            return '--:--';
        }

        return String(time)
            .substring(0, 5);
    }

    /* ==========================================================
       DATE
    ========================================================== */

    function initializeDate() {

        const today = new Date();

        const year =
            today.getFullYear();

        const month =
            String(today.getMonth() + 1)
                .padStart(2, '0');

        const day =
            String(today.getDate())
                .padStart(2, '0');

        const todayString =
            `${year}-${month}-${day}`;

        dateInput.min =
            todayString;

        dateInput.value =
            todayString;
    }


    /* ==========================================================
       BRANCHES
    ========================================================== */

    async function loadBranches() {

        try {

            const url =
                `${API_BASE}/api/public/branches`;

            const response =
                await fetch(url, {
                    headers: {
                        'Accept':
                            'application/json'
                    }
                });

            const text =
                await response.text();

            if (!response.ok) {

                throw new Error(
                    `HTTP ${response.status}: ${text.substring(0, 200)}`
                );
            }

            let result;

            try {

                result =
                    JSON.parse(text);

            } catch (error) {

                throw new Error(
                    'El servidor no devolvió JSON.'
                );
            }

            if (!result.success) {

                throw new Error(
                    result.message ||
                    'No fue posible cargar las sedes.'
                );
            }

            branches =
                Array.isArray(result.data)
                    ? result.data
                    : [];


            branchSelect.innerHTML = `
                <option value="">
                    Selecciona una sede
                </option>
            `;


            branches.forEach(branch => {

                const option =
                    document.createElement('option');

                option.value =
                    branch.id;

                option.textContent =
                    branch.name;

                branchSelect.appendChild(
                    option
                );
            });


            if (branches.length > 0) {

                const firstBranch =
                    branches[0];

                branchSelect.value =
                    firstBranch.id;

                selectedBranch =
                    firstBranch;

                renderBranchInformation(
                    firstBranch
                );

                loadBusinessHours(
                    firstBranch.id
                );
            }

        } catch (error) {

            console.error(
                'Error cargando sedes:',
                error
            );

            branchSelect.innerHTML = `
                <option value="">
                    No fue posible cargar las sedes
                </option>
            `;

            showToast(
                'No fue posible cargar las sedes.'
            );
        }
    }


    /* ==========================================================
       PEOPLE
    ========================================================== */

    document
        .getElementById('increasePeople')
        .addEventListener(
            'click',
            () => {

                if (people < 20) {

                    people++;

                    updatePeople();
                }
            }
        );


    document
        .getElementById('decreasePeople')
        .addEventListener(
            'click',
            () => {

                if (people > 1) {

                    people--;

                    updatePeople();
                }
            }
        );


    function updatePeople() {

        peopleValue.textContent =
            people;


        selectedSlot =
            null;


        reservationSummary
            .classList
            .remove('visible');


        customerSection
            .classList
            .remove('visible');


        /*
         * Si el usuario cambia personas
         * volvemos al paso 1.
         */

        updateSteps(1);
    }


    /* ==========================================================
       SEARCH
    ========================================================== */

    document
        .getElementById('searchAvailability')
        .addEventListener(
            'click',
            searchAvailability
        );


    /* ==========================================================
   SEARCH AVAILABILITY
========================================================== */

    async function searchAvailability() {

        const branchId =
            branchSelect.value;

        const date =
            dateInput.value;


        /*
         * =========================================================
         * VALIDACIONES
         * =========================================================
         */

        if (!branchId) {

            showToast(
                'Selecciona una sede.'
            );

            branchSelect.focus();

            return;
        }


        if (!date) {

            showToast(
                'Selecciona una fecha.'
            );

            dateInput.focus();

            return;
        }


        /*
         * =========================================================
         * RESET
         * =========================================================
         */

        selectedSlot =
            null;


        reservationSummary
            .classList
            .remove('visible');


        customerSection
            .classList
            .remove('visible');


        /*
         * =========================================================
         * PASAR AL PASO 2
         * =========================================================
         *
         * Esto hace que el paso 1 desaparezca
         * y solamente se muestre disponibilidad.
         */

        updateSteps(2);


        /*
         * =========================================================
         * LOADING
         * =========================================================
         */

        availabilityContent.innerHTML = `

        <div class="loading-box">

            <div class="spinner"></div>

            <div>
                Buscando las mejores mesas disponibles...
            </div>

        </div>

    `;


        try {

            const url =
                `${API_BASE}/api/public/availability` +
                `?branch_id=${encodeURIComponent(branchId)}` +
                `&date=${encodeURIComponent(date)}` +
                `&people=${encodeURIComponent(people)}`;


            const response =
                await fetch(
                    url,
                    {
                        headers: {
                            'Accept':
                                'application/json'
                        }
                    }
                );


            const result =
                await response.json();


            if (!result.success) {

                availabilityContent.innerHTML = `

                <div class="empty-box">

                    <div class="empty-icon">

                        <i class="bi bi-exclamation-circle"></i>

                    </div>

                    <h5>
                        No fue posible consultar
                    </h5>

                    <p>
                        ${escapeHtml(
                    result.message ||
                    'Intenta nuevamente.'
                )}
                    </p>

                </div>

            `;

                return;
            }


            renderAvailability(
                result.data
            );


        } catch (error) {

            console.error(
                '[RESERVATION AVAILABILITY]',
                error
            );


            availabilityContent.innerHTML = `

            <div class="empty-box">

                <div class="empty-icon">

                    <i class="bi bi-wifi-off"></i>

                </div>

                <h5>
                    Error de conexión
                </h5>

                <p>
                    No fue posible consultar
                    la disponibilidad.
                </p>

            </div>

        `;
        }
    }

    /* ==========================================================
       RENDER AVAILABILITY
    ========================================================== */

    function renderAvailability(data) {

        const slots =
            data.slots || [];


        availabilityInfo.textContent =
            `${formatDate(data.date)} · ${data.people} ${
                data.people === 1
                    ? 'persona'
                    : 'personas'
            }`;


        if (!slots.length) {

            availabilityContent.innerHTML = `

                <div class="empty-box">

                    <div class="empty-icon">
                        <i class="bi bi-calendar-x"></i>
                    </div>

                    <h5>
                        No hay horarios disponibles
                    </h5>

                    <p>
                        Prueba con otra fecha
                        o una cantidad diferente de personas.
                    </p>

                </div>
            `;

            return;
        }


        const availableSlots =
            slots.filter(
                slot => slot.available
            );


        if (!availableSlots.length) {

            availabilityContent.innerHTML = `

                <div class="empty-box">

                    <div class="empty-icon">
                        <i class="bi bi-calendar-x"></i>
                    </div>

                    <h5>
                        Agenda completa
                    </h5>

                    <p>
                        No encontramos mesas disponibles
                        para esta fecha.
                    </p>

                </div>
            `;

            return;
        }


        const groups = {

            'Almuerzo': [],

            'Tarde': [],

            'Cena': []

        };


        availableSlots.forEach(slot => {

            const hour =
                parseInt(
                    slot.start_time.substring(0, 2),
                    10
                );


            if (hour < 15) {

                groups['Almuerzo'].push(
                    slot
                );

            } else if (hour < 18) {

                groups['Tarde'].push(
                    slot
                );

            } else {

                groups['Cena'].push(
                    slot
                );
            }
        });


        let html = '';


        Object.entries(groups).forEach(
            ([groupName, groupSlots]) => {

                if (!groupSlots.length) {
                    return;
                }


                html += `

                    <div class="time-group">

                        <div class="time-group-title">
                            ${groupName}
                        </div>

                        <div class="time-grid">

                `;


                groupSlots.forEach(slot => {

                    html += `

                        <button
                            type="button"
                            class="time-slot"
                            data-start="${escapeHtml(slot.start_time)}"
                            data-end="${escapeHtml(slot.end_time)}"
                            aria-label="Reservar horario ${escapeHtml(slot.time)}"
                        >
                            <div class="time-slot-time">
                                ${escapeHtml(slot.time)}
                            </div>
                    
                            <div class="time-slot-duration">
                                ${escapeHtml(formatTime(slot.start_time))} –
                                ${escapeHtml(formatTime(slot.end_time))}
                            </div>
                    
                            <div class="time-slot-availability">
                                <span class="time-slot-availability-icon">
                                    <i class="fa-solid fa-chair"></i>
                                </span>
                    
                                <span class="time-slot-availability-text">
                                    ${Number(slot.available_tables_count || 0)}
                                    ${
                                            Number(slot.available_tables_count || 0) === 1
                                                ? 'mesa disponible'
                                                : 'mesas disponibles'
                                        }
                                </span>
                            </div>
                        </button>

                    `;
                });


                html += `

                        </div>

                    </div>

                `;
            }
        );


        availabilityContent.innerHTML =
            html;


        document
            .querySelectorAll('.time-slot')
            .forEach(button => {

                button.addEventListener(
                    'click',
                    () => {

                        selectSlot(
                            button,
                            data
                        );
                    }
                );
            });
    }


    /* ==========================================================
       SELECT SLOT
    ========================================================== */

    /* ==========================================================
   SELECT SLOT
========================================================== */

    function selectSlot(
        button,
        data
    ) {

        /*
         * =========================================================
         * QUITAR SELECCIÓN ANTERIOR
         * =========================================================
         */

        document
            .querySelectorAll('.time-slot')
            .forEach(slot => {

                slot.classList.remove(
                    'selected'
                );

            });


        /*
         * =========================================================
         * SELECCIONAR HORARIO
         * =========================================================
         */

        button.classList.add(
            'selected'
        );


        selectedSlot = {

            start_time:
            button.dataset.start,

            end_time:
            button.dataset.end

        };


        /*
         * =========================================================
         * DATOS DE LA SEDE
         * =========================================================
         */

        const branch =
            data.branch;


        /*
         * =========================================================
         * RESUMEN
         * =========================================================
         */

        document
            .getElementById(
                'summaryBranch'
            )
            .textContent =
            branch.name;


        document
            .getElementById(
                'summaryDate'
            )
            .textContent =
            formatDate(
                data.date
            );


        document
            .getElementById(
                'summaryTime'
            )
            .textContent =
            `${formatTime(
                selectedSlot.start_time
            )} – ${formatTime(
                selectedSlot.end_time
            )}`;


        document
            .getElementById(
                'summaryPeople'
            )
            .textContent =
            `${data.people} ${
                data.people === 1
                    ? 'persona'
                    : 'personas'
            }`;


        /*
         * =========================================================
         * MOSTRAR RESUMEN + DATOS
         * =========================================================
         */

        reservationSummary
            .classList
            .add('visible');


        customerSection
            .classList
            .add('visible');


        /*
         * =========================================================
         * PASAR AL PASO 3
         * =========================================================
         */

        updateSteps(3);


        /*
         * =========================================================
         * LLEVAR AL INICIO DEL PASO 3
         * =========================================================
         */

        setTimeout(() => {

            const card =
                document.querySelector(
                    '.reservation-card'
                );

            if (!card) {
                return;
            }


            const navbarOffset = 90;

            const top =
                card.getBoundingClientRect().top +
                window.scrollY -
                navbarOffset;


            window.scrollTo({

                top: Math.max(
                    0,
                    top
                ),

                behavior:
                    'smooth'
            });

        }, 150);
    }


    /* ==========================================================
       CONFIRM RESERVATION
    ========================================================== */

    document
        .getElementById('confirmReservation')
        .addEventListener(
            'click',
            createReservation
        );


    async function createReservation() {

        if (!selectedSlot) {

            showToast(
                'Selecciona un horario.'
            );

            return;
        }


        const customerName =
            document
                .getElementById('customerName')
                .value
                .trim();


        const customerPhone =
            document
                .getElementById('customerPhone')
                .value
                .trim();


        const customerEmail =
            document
                .getElementById('customerEmail')
                .value
                .trim();


        const notes =
            document
                .getElementById('reservationNotes')
                .value
                .trim();


        if (!customerName) {

            showToast(
                'Ingresa tu nombre.'
            );

            document
                .getElementById('customerName')
                .focus();

            return;
        }


        if (!customerPhone) {

            showToast(
                'Ingresa tu teléfono.'
            );

            document
                .getElementById('customerPhone')
                .focus();

            return;
        }


        const button =
            document.getElementById(
                'confirmReservation'
            );


        button.disabled =
            true;


        button.innerHTML = `

            <span
                class="spinner-border spinner-border-sm me-2"
                aria-hidden="true"
            ></span>

            Confirmando reserva...

        `;


        const payload = {

            branch_id:
                parseInt(
                    branchSelect.value,
                    10
                ),

            reservation_date:
            dateInput.value,

            start_time:
            selectedSlot.start_time,

            people_count:
            people,

            customer: {

                name:
                customerName,

                phone:
                customerPhone,

                email:
                customerEmail

            },

            notes
        };


        try {

            const response =
                await fetch(
                    `${API_BASE}/api/public/reservations`,
                    {

                        method: 'POST',

                        headers: {

                            'Content-Type':
                                'application/json',

                            'Accept':
                                'application/json'
                        },

                        body:
                            JSON.stringify(
                                payload
                            )
                    }
                );


            const result =
                await response.json();


            if (!result.success) {

                showToast(
                    result.message ||
                    'No fue posible crear la reserva.'
                );


                button.disabled =
                    false;


                button.innerHTML = `

                    <i class="fa-solid fa-circle-check me-2"></i>
                
                    Confirmar reserva
                
                `;

                return;
            }


            showSuccess(
                result.data
            );


        } catch (error) {

            console.error(error);


            showToast(
                'Error de conexión al crear la reserva.'
            );


            button.disabled =
                false;


            button.innerHTML = `

                <i class="fa-solid fa-circle-check me-2"></i>
            
                Confirmar reserva
            
            `;
        }
    }


    /* ==========================================================
       SUCCESS
    ========================================================== */

    function showSuccess(data) {

        /*
         * =========================================================
         * OCULTAR TODO EL FORMULARIO
         * =========================================================
         */

        bookingForm.style.display =
            'none';


        /*
         * =========================================================
         * MOSTRAR CONFIRMACIÓN
         * =========================================================
         */

        successContainer
            .classList
            .add('visible');


        /*
         * =========================================================
         * CÓDIGO DE RESERVA
         * =========================================================
         */

        const reservationCode =
            document.getElementById(
                'reservationCode'
            );


        if (reservationCode) {

            reservationCode.textContent =
                data.reservation_code;
        }


        /*
         * =========================================================
         * PASO 4
         * =========================================================
         */

        updateSteps(4);


        /*
         * =========================================================
         * SCROLL
         * =========================================================
         */

        setTimeout(() => {

            const card =
                document.querySelector(
                    '.reservation-card'
                );

            if (!card) {
                return;
            }


            const navbarOffset = 90;

            const top =
                card.getBoundingClientRect().top +
                window.scrollY -
                navbarOffset;


            window.scrollTo({

                top: Math.max(
                    0,
                    top
                ),

                behavior:
                    'smooth'
            });

        }, 100);
    }


    /* ==========================================================
       STEPS
    ========================================================== */

    /* ==========================================================
   STEPS / WIZARD
========================================================== */

    function updateSteps(activeStep) {

        /*
         * =========================================================
         * 1. ACTUALIZAR INDICADORES
         * =========================================================
         */

        for (
            let i = 1;
            i <= 4;
            i++
        ) {

            const step =
                document.getElementById(
                    `step${i}`
                );

            if (!step) {
                continue;
            }


            step.classList.remove(
                'active',
                'completed'
            );


            if (i < activeStep) {

                step.classList.add(
                    'completed'
                );

            } else if (i === activeStep) {

                step.classList.add(
                    'active'
                );
            }
        }


        /*
         * =========================================================
         * 2. ACTUALIZAR LÍNEAS DE PROGRESO
         * =========================================================
         */

        const progressLines =
            document.querySelectorAll(
                '.step-line-progress'
            );


        progressLines.forEach(
            (line, index) => {

                const lineStep =
                    index + 1;

                line.style.width =
                    lineStep < activeStep
                        ? '100%'
                        : '0%';
            }
        );


        /*
         * =========================================================
         * 3. CAMBIAR EL CONTENIDO DEL WIZARD
         * =========================================================
         */

        bookingForm.classList.remove(
            'reservation-wizard-step-1',
            'reservation-wizard-step-2',
            'reservation-wizard-step-3',
            'reservation-wizard-step-4'
        );


        bookingForm.classList.add(
            `reservation-wizard-step-${activeStep}`
        );


        /*
         * =========================================================
         * 4. SCROLL AL INICIO DE LA TARJETA
         * =========================================================
         */

        if (
            activeStep >= 2 &&
            activeStep <= 3
        ) {

            setTimeout(() => {

                const card =
                    document.querySelector(
                        '.reservation-card'
                    );

                if (!card) {
                    return;
                }


                const navbarOffset = 90;

                const top =
                    card.getBoundingClientRect().top +
                    window.scrollY -
                    navbarOffset;


                window.scrollTo({

                    top: Math.max(
                        0,
                        top
                    ),

                    behavior:
                        'smooth'
                });

            }, 100);
        }
    }


    /* ==========================================================
   WIZARD — ATRÁS
========================================================== */

    function goBackToStep1() {

        /*
         * Limpiar horario seleccionado
         */

        selectedSlot =
            null;


        /*
         * Ocultar resumen
         */

        reservationSummary
            .classList
            .remove('visible');


        /*
         * Ocultar datos del cliente
         */

        customerSection
            .classList
            .remove('visible');


        /*
         * Volver al paso 1
         */

        updateSteps(1);


        /*
         * Volver arriba
         */

        scrollToReservationTop();
    }


    /* ==========================================================
       PASO 3 → PASO 2
    ========================================================== */

    function goBackToStep2() {

        /*
         * Ocultar datos del cliente
         */

        customerSection
            .classList
            .remove('visible');


        /*
         * Ocultar resumen
         */

        reservationSummary
            .classList
            .remove('visible');


        /*
         * IMPORTANTE:
         * No eliminamos selectedSlot.
         *
         * De esta forma el usuario puede volver
         * a revisar los horarios.
         */

        updateSteps(2);


        scrollToReservationTop();
    }


    /* ==========================================================
       SCROLL
    ========================================================== */

    function scrollToReservationTop() {

        setTimeout(() => {

            const card =
                document.querySelector(
                    '.reservation-card'
                );


            if (!card) {
                return;
            }


            const navbarOffset =
                90;


            const top =
                card.getBoundingClientRect().top +
                window.scrollY -
                navbarOffset;


            window.scrollTo({

                top:
                    Math.max(
                        0,
                        top
                    ),

                behavior:
                    'smooth'
            });

        }, 100);
    }

    /* ==========================================================
       FORMAT DATE
    ========================================================== */

    function formatDate(dateString) {

        const date =
            new Date(
                `${dateString}T12:00:00`
            );


        return date.toLocaleDateString(
            'es-CO',
            {

                weekday: 'long',

                day: 'numeric',

                month: 'long',

                year: 'numeric'

            }
        );
    }


    /* ==========================================================
       FORMAT TIME
    ========================================================== */

    function formatTime(time) {

        const [
            hour,
            minute
        ] =
            time.split(':');


        return `${hour}:${minute}`;
    }


    /* ==========================================================
       TOAST
    ========================================================== */

    function showToast(message) {

        const toast =
            document.getElementById(
                'reservationToast'
            );


        toast.textContent =
            message;


        toast.classList.add(
            'show'
        );


        setTimeout(() => {

            toast.classList.remove(
                'show'
            );

        }, 4000);
    }


    /* ==========================================================
       ESCAPE HTML
    ========================================================== */

    function escapeHtml(value) {

        const div =
            document.createElement('div');


        div.textContent =
            value ?? '';


        return div.innerHTML;
    }

});