document.addEventListener('DOMContentLoaded', () => {

    'use strict';

    const form =
        document.getElementById('branchForm');

    if (!form) {
        return;
    }

    const config =
        window.BRANCH_FORM || {};

    const $ =
        id => document.getElementById(id);

    const branchId =
        $('branchId');


    /* ==========================================================
       HORARIOS
    ========================================================== */

    document
        .querySelectorAll('.branch-hour-row')
        .forEach(row => {

            const toggle =
                row.querySelector(
                    '.hour-open-toggle'
                );

            const open =
                row.querySelector(
                    '.hour-open'
                );

            const close =
                row.querySelector(
                    '.hour-close'
                );

            const status =
                row.querySelector(
                    '.branch-hour-status'
                );

            if (!toggle || !open || !close) {
                return;
            }

            function update() {

                const isOpen =
                    toggle.checked;

                open.disabled =
                    !isOpen;

                close.disabled =
                    !isOpen;

                if (status) {
                    status.textContent =
                        isOpen
                            ? 'Abierto'
                            : 'Cerrado';
                }

                row.classList.toggle(
                    'is-closed',
                    !isOpen
                );
            }

            toggle.addEventListener(
                'change',
                update
            );

            update();
        });


    /* ==========================================================
       NORMALIZAR CÓDIGO
    ========================================================== */

    const codeInput =
        $('branchCode');

    if (codeInput) {

        codeInput.addEventListener(
            'input',
            function () {

                /*
                 * El backend solamente permite:
                 *
                 * A-Z
                 * 0-9
                 * -
                 * _
                 *
                 * Convertimos automáticamente
                 * a mayúsculas y eliminamos
                 * cualquier otro carácter.
                 */

                this.value =
                    this.value
                        .toUpperCase()
                        .replace(
                            /[^A-Z0-9_-]/g,
                            ''
                        )
                        .substring(
                            0,
                            20
                        );
            }
        );

        /*
         * Normalizar también al salir
         * del campo.
         */
        codeInput.addEventListener(
            'blur',
            function () {

                this.value =
                    this.value
                        .toUpperCase()
                        .replace(
                            /[^A-Z0-9_-]/g,
                            ''
                        )
                        .substring(
                            0,
                            20
                        );
            }
        );
    }


    /* ==========================================================
       SUBMIT
    ========================================================== */

    form.addEventListener(
        'submit',
        async event => {

            event.preventDefault();

            let loadingStarted = false;

            try {

                const payload =
                    collectForm();

                validateForm(
                    payload
                );


                setLoading(true);

                loadingStarted = true;

                const isEdit =
                    config.mode === 'edit';

                const url =
                    isEdit
                        ? `${window.APP_URL}/api/admin/branches/update`
                        : `${window.APP_URL}/api/admin/branches/store`;


                /* ==================================================
                   GUARDAR INFORMACIÓN GENERAL
                ================================================== */

                const response =
                    await fetch(
                        url,
                        {
                            method: 'POST',

                            credentials:
                                'same-origin',

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
                    await parseJsonResponse(
                        response
                    );


                if (
                    !response.ok ||
                    !result.success
                ) {

                    throw new Error(
                        result.message ||
                        'No fue posible guardar la sede.'
                    );
                }


                /* ==================================================
                   OBTENER ID DE LA SEDE
                ================================================== */

                const savedBranchId =
                    isEdit
                        ? Number(
                            config.branchId
                        )
                        : Number(
                            result.data?.id || 0
                        );


                if (!savedBranchId) {

                    throw new Error(
                        'La sede se guardó pero no se obtuvo su identificador.'
                    );
                }


                /* ==================================================
                   GUARDAR HORARIOS
                ================================================== */

                const hoursResponse =
                    await fetch(
                        `${window.APP_URL}/api/admin/branches/save-hours`,
                        {
                            method: 'POST',

                            credentials:
                                'same-origin',

                            headers: {
                                'Content-Type':
                                    'application/json',

                                'Accept':
                                    'application/json'
                            },

                            body:
                                JSON.stringify({

                                    branch_id:
                                    savedBranchId,

                                    hours:
                                    payload.hours
                                })
                        }
                    );


                const hoursResult =
                    await parseJsonResponse(
                        hoursResponse
                    );


                if (
                    !hoursResponse.ok ||
                    !hoursResult.success
                ) {

                    throw new Error(
                        hoursResult.message ||
                        'La sede se guardó pero no fue posible guardar los horarios.'
                    );
                }


                // Una sede nueva ya tiene ID: asociar ahora las mesas e imágenes pendientes.
                if (!isEdit) {
                    window.BRANCH_FORM = window.BRANCH_FORM || {};
                    window.BRANCH_FORM.mode = 'edit';
                    window.BRANCH_FORM.branchId = savedBranchId;
                    const branchIdInput = document.getElementById('branchId');
                    if (branchIdInput) branchIdInput.value = String(savedBranchId);
                }

                await savePendingTables(savedBranchId);
                await branchImageManager.save(savedBranchId);

                /* ==================================================
                   ÉXITO
                ================================================== */

                showSuccess(
                    isEdit
                        ? 'Sede actualizada correctamente.'
                        : 'Sede creada correctamente.'
                );


                setTimeout(
                    () => {

                        window.location.href =
                            `${window.APP_URL}/admin/branches`;

                    },
                    900
                );


            } catch (error) {

                console.error(
                    '[BRANCH FORM]',
                    error
                );

                showError(
                    error.message ||
                    'No fue posible guardar la sede.'
                );

            } finally {

                if (loadingStarted) {
                    setLoading(false);
                }
            }
        }
    );


    /* ==========================================================
       RECOPILAR FORMULARIO
    ========================================================== */

    function collectForm() {

        const hours = {};


        document
            .querySelectorAll(
                '.branch-hour-row'
            )
            .forEach(row => {

                const day =
                    Number(
                        row.dataset.day
                    );

                const toggle =
                    row.querySelector(
                        '.hour-open-toggle'
                    );

                const open =
                    row.querySelector(
                        '.hour-open'
                    );

                const close =
                    row.querySelector(
                        '.hour-close'
                    );


                if (
                    !toggle ||
                    !open ||
                    !close
                ) {
                    return;
                }


                hours[day] = {

                    is_closed:
                        !toggle.checked,

                    open_time:
                        toggle.checked
                            ? open.value
                            : null,

                    close_time:
                        toggle.checked
                            ? close.value
                            : null
                };
            });


        return {

            id:
                Number(
                    branchId?.value || 0
                ),

            code:
                $('branchCode')
                    ?.value
                    .trim()
                    .toUpperCase()
                    .replace(
                        /[^A-Z0-9_-]/g,
                        ''
                    ),

            name:
                $('branchName')
                    ?.value
                    .trim() || '',

            phone:
                $('branchPhone')
                    ?.value
                    .trim() || '',

            email:
                $('branchEmail')
                    ?.value
                    .trim()
                    .toLowerCase() || '',

            address:
                $('branchAddress')
                    ?.value
                    .trim() || '',

            is_active:
                $('branchActive')
                    ? $('branchActive').checked
                    : true,

            hours
        };
    }


    /* ==========================================================
       VALIDAR FORMULARIO
    ========================================================== */

    function validateForm(
        payload
    ) {

        /* ------------------------------------------------------
           CÓDIGO
        ------------------------------------------------------ */

        if (!payload.code) {

            throw new Error(
                'El código de la sede es obligatorio.'
            );
        }


        /*
         * Esta expresión es exactamente
         * compatible con el backend.
         */
        if (
            !/^[A-Z0-9_-]{2,20}$/
                .test(payload.code)
        ) {

            throw new Error(
                'El código debe tener entre 2 y 20 caracteres y solo puede contener letras, números, guion y guion bajo.'
            );
        }


        /* ------------------------------------------------------
           NOMBRE
        ------------------------------------------------------ */

        if (!payload.name) {

            throw new Error(
                'El nombre de la sede es obligatorio.'
            );
        }


        if (
            payload.name.length > 150
        ) {

            throw new Error(
                'El nombre de la sede no puede superar los 150 caracteres.'
            );
        }


        /* ------------------------------------------------------
           EMAIL
        ------------------------------------------------------ */

        if (
            payload.email &&
            !/^[^\s@]+@[^\s@]+\.[^\s@]+$/
                .test(payload.email)
        ) {

            throw new Error(
                'El correo electrónico no es válido.'
            );
        }


        /* ------------------------------------------------------
           HORARIOS
        ------------------------------------------------------ */

        Object.entries(
            payload.hours
        ).forEach(
            ([day, data]) => {

                /*
                 * Día cerrado:
                 * no necesitamos validar horas.
                 */
                if (data.is_closed) {
                    return;
                }


                if (
                    !data.open_time ||
                    !data.close_time
                ) {

                    throw new Error(
                        `Debes configurar el horario del día ${day}.`
                    );
                }


                if (
                    !/^\d{2}:\d{2}$/
                        .test(data.open_time) ||
                    !/^\d{2}:\d{2}$/
                        .test(data.close_time)
                ) {

                    throw new Error(
                        `El horario del día ${day} no es válido.`
                    );
                }


                if (
                    data.open_time >=
                    data.close_time
                ) {

                    throw new Error(
                        `La apertura debe ser menor que el cierre en el día ${day}.`
                    );
                }
            }
        );
    }


    /* ==========================================================
       RESPUESTA JSON
    ========================================================== */

    async function parseJsonResponse(
        response
    ) {

        const text =
            await response.text();

        if (!text) {

            throw new Error(
                `El servidor no devolvió una respuesta válida. Código HTTP: ${response.status}`
            );
        }


        try {

            return JSON.parse(
                text
            );

        } catch (error) {

            console.error(
                '[BRANCH FORM] Respuesta no JSON:',
                text
            );

            throw new Error(
                `El servidor devolvió una respuesta inesperada. Código HTTP: ${response.status}`
            );
        }
    }


    /* ==========================================================
       LOADING
    ========================================================== */

    function setLoading(
        loading
    ) {

        const button =
            $('btnSaveBranch');

        if (!button) {
            return;
        }


        if (loading) {

            button.disabled =
                true;

            button.innerHTML = `
                <i class="fa-solid fa-spinner fa-spin"></i>
                Guardando...
            `;

        } else {

            button.disabled =
                false;

            button.innerHTML =
                config.mode === 'edit'
                    ? `
                        <i class="fa-solid fa-check"></i>
                        Guardar cambios
                      `
                    : `
                        <i class="fa-solid fa-check"></i>
                        Crear sede
                      `;
        }
    }


    /* ==========================================================
       SUCCESS
    ========================================================== */

    function showSuccess(
        message
    ) {

        /*
         * IMPORTANTE:
         *
         * showToast recibe:
         *
         * showToast(icon, title)
         *
         * NO:
         *
         * showToast(message, icon)
         */

        if (
            typeof window.showToast ===
            'function'
        ) {

            window.showToast(
                'success',
                message
            );

            return;
        }


        if (
            typeof Swal !==
            'undefined'
        ) {

            Swal.fire({

                icon:
                    'success',

                title:
                    'Correcto',

                text:
                message,

                timer:
                    1600,

                showConfirmButton:
                    false
            });

            return;
        }


        alert(message);
    }


    /* ==========================================================
       ERROR
    ========================================================== */

    function showError(
        message
    ) {

        /*
         * IMPORTANTE:
         * misma firma correcta:
         *
         * showToast('error', message)
         */

        if (
            typeof window.showToast ===
            'function'
        ) {

            window.showToast(
                'error',
                message
            );

            return;
        }


        if (
            typeof Swal !==
            'undefined'
        ) {

            Swal.fire({

                icon:
                    'error',

                title:
                    'Atención',

                text:
                message
            });

            return;
        }


        alert(message);
    }

});

/* ==========================================================
   MESAS DE LA SEDE
========================================================== */

const tablesList =
    document.getElementById(
        'branchTablesList'
    );

const btnAddTable =
    document.getElementById(
        'btnAddTable'
    );

const btnAddTableEmpty =
    document.getElementById(
        'btnAddTableEmpty'
    );


function getTablesData() {

    if (!tablesList) {
        return [];
    }

    return [
        ...tablesList.querySelectorAll(
            '.branch-table-item'
        )
    ];
}


function updateTablesSummary() {

    const items =
        getTablesData();

    let totalCapacity = 0;

    items.forEach(
        item => {

            const capacity =
                item.querySelector(
                    '.table-capacity'
                );

            if (capacity) {

                totalCapacity +=
                    Number(
                        capacity.value
                    ) || 0;
            }
        }
    );


    const count =
        document.getElementById(
            'tablesCount'
        );

    const capacity =
        document.getElementById(
            'tablesCapacity'
        );


    if (count) {

        count.textContent =
            items.length;
    }


    if (capacity) {

        capacity.textContent =
            totalCapacity;
    }


    const empty =
        document.getElementById(
            'tablesEmpty'
        );


    if (empty) {

        empty.style.display =
            items.length === 0
                ? ''
                : 'none';
    }
}


function getNextTableNumber() {

    const numbers = [
        ...document.querySelectorAll(
            '.table-number'
        )
    ]
        .map(
            input =>
                Number(
                    input.value
                ) || 0
        )
        .filter(
            number =>
                number > 0
        );


    if (!numbers.length) {
        return 1;
    }


    return Math.max(
        ...numbers
    ) + 1;
}


function createTableRow() {

    const tableNumber =
        getNextTableNumber();


    const row =
        document.createElement(
            'div'
        );

    row.className =
        'branch-table-item is-new';


    row.dataset.tableId =
        '';


    row.innerHTML = `

            <div class="branch-table-item-number">

                <span>
                    Nueva
                </span>

                <strong>
                    #${tableNumber}
                </strong>

            </div>


            <div class="branch-table-field">

                <label>
                    Número
                </label>

                <input
                    type="number"
                    min="1"
                    max="999"
                    class="branch-table-input table-number"
                    value="${tableNumber}"
                >

            </div>


            <div class="branch-table-field">

                <label>
                    Nombre
                </label>

                <input
                    type="text"
                    maxlength="100"
                    class="branch-table-input table-name"
                    value="Mesa ${tableNumber}"
                >

            </div>


            <div class="branch-table-field branch-table-capacity">

                <label>
                    Personas
                </label>

                <input
                    type="number"
                    min="1"
                    max="100"
                    class="branch-table-input table-capacity"
                    value="4"
                >

                <span>
                    personas
                </span>

            </div>


            <div class="branch-table-field">

                <label>
                    Ubicación
                </label>

                <input
                    type="text"
                    maxlength="100"
                    class="branch-table-input table-location"
                    placeholder="Ej. Terraza"
                >

            </div>


            <div class="branch-table-field">

                <label>
                    Estado
                </label>

                <select
                    class="branch-table-input table-status"
                >

                    <option value="available">
                        Disponible
                    </option>

                    <option value="maintenance">
                        Mantenimiento
                    </option>

                </select>

            </div>


            <div class="branch-table-active">

                <label>

                    <input
                        type="checkbox"
                        class="table-active"
                        checked
                    >

                    <span>
                        Activa
                    </span>

                </label>

            </div>


            <div class="branch-table-actions">

                <button
                    type="button"
                    class="branch-table-save"
                    title="Guardar mesa"
                >
                    <i class="fa-solid fa-floppy-disk"></i>
                </button>

                <button
                    type="button"
                    class="branch-table-delete"
                    title="Cancelar"
                >
                    <i class="fa-solid fa-xmark"></i>
                </button>

                <button
                    type="button"
                    class="branch-table-delete-permanent"
                    title="Eliminar definitivamente"
                    aria-label="Eliminar definitivamente"
                >
                    <i class="fa-solid fa-trash-can"></i>
                </button>

            </div>

        `;


    tablesList.appendChild(
        row
    );


    const empty =
        document.getElementById(
            'tablesEmpty'
        );

    if (empty) {

        empty.style.display =
            'none';
    }


    updateTablesSummary();


    const firstInput =
        row.querySelector(
            '.table-number'
        );

    firstInput?.focus();

    firstInput?.select();
}

function showSuccess(message) {

    if (typeof window.showToast === 'function') {

        window.showToast(
            'success',
            message
        );

        return;
    }

    if (typeof Swal !== 'undefined') {

        Swal.fire({
            icon: 'success',
            title: 'Correcto',
            text: message,
            timer: 1600,
            showConfirmButton: false
        });

        return;
    }

    console.log(
        '[SUCCESS]',
        message
    );
}


function showError(message) {

    if (typeof window.showToast === 'function') {

        window.showToast(
            'error',
            message
        );

        return;
    }

    if (typeof Swal !== 'undefined') {

        Swal.fire({
            icon: 'error',
            title: 'Atención',
            text: message
        });

        return;
    }

    console.error(
        '[ERROR]',
        message
    );

    alert(message);
}


function showError(message) {

    if (typeof window.showToast === 'function') {

        window.showToast(
            message,
            'error'
        );

        return;
    }

    console.error(
        '[ERROR]',
        message
    );

    alert(
        message
    );
}

async function saveTable(
    row
) {

    const tableId =
        row.dataset.tableId;


    const number =
        row.querySelector(
            '.table-number'
        );

    const name =
        row.querySelector(
            '.table-name'
        );

    const capacity =
        row.querySelector(
            '.table-capacity'
        );

    const location =
        row.querySelector(
            '.table-location'
        );

    const status =
        row.querySelector(
            '.table-status'
        );

    const active =
        row.querySelector(
            '.table-active'
        );


    if (!number || !capacity) {
        return;
    }


    const payload = {

        branch_id:
            Number(
                window.BRANCH_FORM?.branchId || 0
            ),

        table_number:
            Number(
                number.value
            ),

        name:
            name?.value.trim()
            || '',

        capacity:
            Number(
                capacity.value
            ),

        location:
            location?.value.trim()
            || '',

        status:
            status?.value
            || 'available',

        is_active:
            active?.checked
                ? 1
                : 0
    };


    if (
        !payload.capacity ||
        payload.capacity <= 0
    ) {

        showToast(
            'Ingresa una capacidad válida.',
            'error'
        );

        capacity.focus();

        return;
    }


    if (
        !payload.table_number ||
        payload.table_number <= 0
    ) {

        showError(
            'Ingresa un número de mesa válido.'
        );

        number.focus();

        return;
    }


    if (!payload.branch_id || payload.branch_id <= 0) {
        if (window.BRANCH_FORM?.mode === 'create') {
            row.dataset.tablePending = '1';
            row.classList.add('is-pending');

            const label = row.querySelector('.branch-table-item-number span');
            if (label) label.textContent = 'Pendiente';

            const pendingButton = row.querySelector('.branch-table-save');
            if (pendingButton) {
                pendingButton.innerHTML = '<i class="fa-solid fa-check"></i>';
                pendingButton.title = 'Mesa lista para guardar con la sede';
                pendingButton.setAttribute('aria-label', 'Mesa lista para guardar con la sede');
            }

            showSuccess('Mesa #' + payload.table_number + ' preparada. Se guardará al crear la sede.');
            updateTablesSummary();
            return;
        }

        showToast('No fue posible determinar la sede.', 'error');
        return;
    }

    const button =
        row.querySelector(
            '.branch-table-save'
        );


    if (button) {

        button.disabled =
            true;

        button.innerHTML =
            '<i class="fa-solid fa-spinner fa-spin"></i>';
    }


    try {

        const endpoint =
            tableId
                ? '/api/admin/branches/tables/update'
                : '/api/admin/branches/tables/create';


        if (tableId) {

            payload.id =
                Number(
                    tableId
                );
        }


        const response =
            await fetch(
                `${window.APP_URL}${endpoint}`,
                {
                    method: 'POST',

                    credentials:
                        'same-origin',

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


        if (
            !response.ok ||
            !result.success
        ) {

            throw new Error(
                result.message ||
                'No fue posible guardar la mesa.'
            );
        }


        showSuccess(
            result.message
        );


        if (!tableId) {

            /*
             * Recargamos la página para que
             * la nueva mesa quede renderizada
             * con su ID real.
             */
            setTimeout(
                () => {
                    window.location.reload();
                },
                500
            );

            return;
        }


        row.classList.remove(
            'is-new'
        );


        updateTablesSummary();


    } catch (error) {

        console.error(
            '[BRANCH TABLE SAVE]',
            error
        );

        showToast(
            error.message,
            'error'
        );

    } finally {

        if (button) {

            button.disabled =
                false;

            button.innerHTML =
                '<i class="fa-solid fa-floppy-disk"></i>';
        }
    }
}


async function deleteTable(
    row
) {

    const tableId =
        row.dataset.tableId;


    /*
     * Si todavía no tiene ID,
     * simplemente quitamos la fila.
     */
    if (!tableId) {

        row.remove();

        updateTablesSummary();

        return;
    }


    let confirmed =
        true;


    if (
        typeof Swal !==
        'undefined'
    ) {

        const result =
            await Swal.fire({

                icon:
                    'warning',

                title:
                    '¿Eliminar mesa?',

                text:
                    'Si la mesa tiene historial de reservas será desactivada para conservar el historial.',

                showCancelButton:
                    true,

                confirmButtonText:
                    'Sí, eliminar',

                cancelButtonText:
                    'Cancelar',

                reverseButtons:
                    true
            });


        confirmed =
            result.isConfirmed;
    } else {

        confirmed =
            window.confirm(
                '¿Deseas eliminar esta mesa?'
            );
    }


    if (!confirmed) {
        return;
    }


    const button =
        row.querySelector(
            '.branch-table-delete'
        );


    if (button) {

        button.disabled =
            true;

        button.innerHTML =
            '<i class="fa-solid fa-spinner fa-spin"></i>';
    }


    try {

        const response =
            await fetch(
                `${window.APP_URL}/api/admin/branches/tables/delete`,
                {
                    method: 'POST',

                    credentials:
                        'same-origin',

                    headers: {

                        'Content-Type':
                            'application/json',

                        'Accept':
                            'application/json'
                    },

                    body:
                        JSON.stringify({

                            id:
                                Number(
                                    tableId
                                )
                        })
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
                'No fue posible eliminar la mesa.'
            );
        }


        row.remove();

        updateTablesSummary();


        showSuccess(
            result.message
        );


    } catch (error) {

        console.error(
            '[BRANCH TABLE DELETE]',
            error
        );

        showError(
            error.message
        );

        if (button) {

            button.disabled =
                false;

            button.innerHTML =
                '<i class="fa-solid fa-trash"></i>';
        }
    }
}


if (btnAddTable) {

    btnAddTable.addEventListener(
        'click',
        createTableRow
    );
}


if (btnAddTableEmpty) {

    btnAddTableEmpty.addEventListener(
        'click',
        createTableRow
    );
}


if (tablesList) {

    tablesList.addEventListener(
        'click',
        function (event) {

            const saveButton =
                event.target.closest(
                    '.branch-table-save'
                );

            if (saveButton) {

                const row =
                    saveButton.closest(
                        '.branch-table-item'
                    );

                if (row) {

                    saveTable(
                        row
                    );
                }

                return;
            }


            const deleteButton =
                event.target.closest(
                    '.branch-table-delete'
                );

            if (deleteButton) {

                const row =
                    deleteButton.closest(
                        '.branch-table-item'
                    );

                if (row) {
                    deleteTable(row);
                }
                return;
            }

            const permanentDeleteButton = event.target.closest('.branch-table-delete-permanent');
            if (permanentDeleteButton) {
                const row = permanentDeleteButton.closest('.branch-table-item');
                if (row) deleteTablePermanently(row);
            }
        }
    );


    tablesList.addEventListener(
        'input',
        function (event) {

            if (
                event.target.classList.contains(
                    'table-capacity'
                )
            ) {

                updateTablesSummary();
            }
        }
    );
}


updateTablesSummary();

/* ==========================================================
   GUARDADO DE MESAS PENDIENTES AL CREAR UNA SEDE
========================================================== */
async function savePendingTables(savedBranchId) {
    const branchId = Number(savedBranchId || 0);
    if (!branchId) {
        throw new Error('No fue posible determinar la sede para guardar las mesas.');
    }

    const rows = getTablesData();
    const pendingRows = rows.filter(row => !Number(row.dataset.tableId || 0));
    if (!pendingRows.length) return true;

    const usedNumbers = new Set();
    rows.forEach(row => {
        if (pendingRows.includes(row)) return;
        const number = Number(row.querySelector('.table-number')?.value || 0);
        if (number > 0) usedNumbers.add(number);
    });

    const pendingData = pendingRows.map(row => {
        const tableNumber = Number(row.querySelector('.table-number')?.value || 0);
        const capacity = Number(row.querySelector('.table-capacity')?.value || 0);
        if (!tableNumber || tableNumber < 1 || tableNumber > 999) {
            throw new Error('Todas las mesas deben tener un número válido entre 1 y 999.');
        }
        if (usedNumbers.has(tableNumber)) {
            throw new Error('El número de mesa ' + tableNumber + ' está repetido.');
        }
        usedNumbers.add(tableNumber);
        if (!capacity || capacity < 1 || capacity > 100) {
            throw new Error('La mesa #' + tableNumber + ' debe tener una capacidad entre 1 y 100 personas.');
        }
        return {
            row,
            payload: {
                branch_id: branchId,
                table_number: tableNumber,
                name: row.querySelector('.table-name')?.value.trim() || ('Mesa ' + tableNumber),
                capacity,
                location: row.querySelector('.table-location')?.value.trim() || '',
                status: row.querySelector('.table-status')?.value || 'available',
                is_active: row.querySelector('.table-active')?.checked ? 1 : 0
            }
        };
    });

    for (const item of pendingData) {
        const row = item.row;
        const button = row.querySelector('.branch-table-save');
        if (button) {
            button.disabled = true;
            button.innerHTML = '<i class="fa-solid fa-spinner fa-spin"></i>';
        }

        try {
            const response = await fetch(window.APP_URL + '/api/admin/branches/tables/create', {
                method: 'POST',
                credentials: 'same-origin',
                headers: {
                    'Content-Type': 'application/json',
                    'Accept': 'application/json'
                },
                body: JSON.stringify(item.payload)
            });
            const result = await response.json();
            if (!response.ok || !result.success) {
                throw new Error(result.message || ('No fue posible crear la mesa #' + item.payload.table_number + '.'));
            }

            const id = Number(result.data?.id || 0);
            if (!id) {
                throw new Error('El servidor no devolvió el identificador de la mesa #' + item.payload.table_number + '.');
            }

            row.dataset.tableId = String(id);
            row.dataset.tablePending = '0';
            row.classList.remove('is-new', 'is-pending');
            const label = row.querySelector('.branch-table-item-number span');
            if (label) label.textContent = 'Mesa';
        } finally {
            if (button) {
                button.disabled = false;
                button.innerHTML = '<i class="fa-solid fa-floppy-disk"></i>';
                button.title = 'Guardar mesa';
            }
        }
    }

    updateTablesSummary();
    return true;
}

/* ==========================================================
   ELIMINACIÓN DEFINITIVA DE MESAS
========================================================== */
async function deleteTablePermanently(row) {
    const tableId = Number(row.dataset.tableId || 0);
    if (!tableId) {
        row.remove();
        updateTablesSummary();
        return;
    }

    let confirmed = false;
    if (typeof Swal !== 'undefined') {
        const result = await Swal.fire({
            icon: 'warning',
            title: '¿Eliminar definitivamente?',
            text: 'Esta acción es irreversible. Las mesas con historial de reservas no se pueden eliminar.',
            showCancelButton: true,
            confirmButtonText: 'Eliminar definitivamente',
            cancelButtonText: 'Cancelar',
            confirmButtonColor: '#dc2626',
            reverseButtons: true
        });
        confirmed = result.isConfirmed;
    } else {
        confirmed = window.confirm('¿Eliminar definitivamente esta mesa? Las mesas con historial de reservas no se pueden eliminar.');
    }
    if (!confirmed) return;

    const button = row.querySelector('.branch-table-delete-permanent');
    if (button) {
        button.disabled = true;
        button.innerHTML = '<i class="fa-solid fa-spinner fa-spin"></i>';
    }

    try {
        const response = await fetch(window.APP_URL + '/api/admin/branches/tables/delete-permanently', {
            method: 'POST',
            credentials: 'same-origin',
            headers: {
                'Content-Type': 'application/json',
                'Accept': 'application/json'
            },
            body: JSON.stringify({ id: tableId })
        });
        const result = await response.json();

        if (!response.ok || !result.success) {
            if (response.status === 409 && Array.isArray(result.reservations)) {
                const details = result.reservations.map(item =>
                    [item.reservation_code || '', item.reservation_date || '', item.start_time || '', item.status || ''].filter(Boolean).join(' · ')
                ).join('\n');
                if (typeof Swal !== 'undefined') {
                    await Swal.fire({
                        icon: 'info',
                        title: 'No se puede eliminar esta mesa',
                        text: (result.message || 'La mesa tiene reservas asociadas.') + (details ? '\n' + details : '')
                    });
                } else {
                    showError((result.message || 'La mesa tiene reservas asociadas.') + (details ? '\n' + details : ''));
                }
                return;
            }
            throw new Error(result.message || 'No fue posible eliminar la mesa.');
        }

        row.remove();
        updateTablesSummary();
        showSuccess(result.message || 'Mesa eliminada definitivamente.');
    } catch (error) {
        console.error('[BRANCH TABLE PERMANENT DELETE]', error);
        showError(error.message || 'No fue posible eliminar la mesa.');
    } finally {
        if (button && button.isConnected) {
            button.disabled = false;
            button.innerHTML = '<i class="fa-solid fa-trash-can"></i>';
        }
    }
}

/* ==========================================================
   ADMINISTRADOR DE IMÁGENES DE SEDE
========================================================== */
const branchImageManager = (function () {
    const config = window.BRANCH_FORM || {};
    const input = document.getElementById('branchImagesInput');
    const dropzone = document.getElementById('branchImagesDropzone');
    const thumbs = document.getElementById('branchImagesThumbs');
    const counter = document.getElementById('branchImagesCounter');
    const stage = document.getElementById('branchImagesStage');
    const mainView = document.getElementById('branchImagesMainView');
    const mainEmpty = document.getElementById('branchImagesMainEmpty');
    const mainImage = document.getElementById('branchImagesMainImage');
    const favoriteBadge = document.getElementById('branchImagesFavoriteBadge');
    const newBadge = document.getElementById('branchImagesNewBadge');
    const emptyGallery = document.getElementById('branchImagesEmptyGallery');

    if (!input || !thumbs) return { save: async function () { return true; } };

    const maxImages = 20;
    const maxSize = 8 * 1024 * 1024;
    const state = {
        items: [],
        deletedIds: new Set(),
        favoriteKey: null,
        activeKey: null,
        dirty: false
    };

    function escapeHtml(value) {
        return String(value == null ? '' : value).replace(/[&<>"']/g, function (char) {
            return ({ '&': '&amp;', '<': '&lt;', '>': '&gt;', '"': '&quot;', "'": '&#039;' })[char];
        });
    }

    function makeKey() {
        return 'new-' + Date.now() + '-' + Math.random().toString(36).slice(2);
    }

    function normalizeExisting(images) {
        state.items = (Array.isArray(images) ? images : []).map(function (item) {
            return {
                type: 'existing',
                id: Number(item.id),
                key: 'existing-' + Number(item.id),
                url: item.url || (window.APP_URL + '/public/' + String(item.file_path || '').replace(/^\/+/, '')),
                name: item.original_name || ('Imagen ' + item.id),
                file: null,
                objectUrl: null,
                sortOrder: Number(item.sort_order || 0)
            };
        });
        state.items.sort(function (a, b) { return a.sortOrder - b.sortOrder; });
        const favorite = (Array.isArray(images) ? images : []).find(function (item) { return Boolean(item.is_favorite); });
        state.favoriteKey = favorite ? 'existing-' + Number(favorite.id) : (state.items[0]?.key || null);
        state.activeKey = state.favoriteKey || state.items[0]?.key || null;
    }

    function render() {
        const count = state.items.length;
        if (counter) counter.textContent = count + ' / ' + maxImages + ' imágenes';
        if (emptyGallery) emptyGallery.hidden = count > 0;
        if (!count) {
            stage?.classList.add('is-empty');
            if (mainView) mainView.hidden = true;
            if (mainEmpty) mainEmpty.hidden = false;
            thumbs.innerHTML = '';
            return;
        }

        stage?.classList.remove('is-empty');
        if (mainView) mainView.hidden = false;
        if (mainEmpty) mainEmpty.hidden = true;

        let active = state.items.find(function (item) { return item.key === state.activeKey; });
        if (!active) {
            active = state.items[0];
            state.activeKey = active.key;
        }
        if (mainImage) {
            mainImage.src = active.url;
            mainImage.alt = active.name;
        }
        if (favoriteBadge) favoriteBadge.hidden = active.key !== state.favoriteKey;
        if (newBadge) newBadge.hidden = active.type !== 'new';

        thumbs.innerHTML = '';
        state.items.forEach(function (item, index) {
            const card = document.createElement('div');
            card.className = 'branch-image-thumb';
            card.draggable = true;
            card.dataset.imageKey = item.key;
            if (item.key === state.activeKey) card.classList.add('is-active');
            if (item.key === state.favoriteKey) card.classList.add('is-favorite');

            card.innerHTML =
                '<span class="branch-image-drag-handle"><i class="fa-solid fa-grip-vertical"></i></span>' +
                '<button type="button" class="branch-image-thumb-view" title="Ver imagen">' +
                    '<img src="' + escapeHtml(item.url) + '" alt="' + escapeHtml(item.name) + '">' +
                '</button>' +
                '<div class="branch-image-thumb-overlay">' +
                    '<span class="branch-image-order">' + (index + 1) + '</span>' +
                    '<div class="branch-image-thumb-actions">' +
                        '<button type="button" class="branch-image-favorite" title="Marcar como portada"><i class="fa-' +
                            (item.key === state.favoriteKey ? 'solid' : 'regular') + ' fa-star"></i></button>' +
                        '<button type="button" class="branch-image-remove" title="Eliminar imagen"><i class="fa-solid fa-trash"></i></button>' +
                    '</div>' +
                '</div>' +
                (item.key === state.favoriteKey ? '<span class="branch-image-favorite-label"><i class="fa-solid fa-star"></i> Portada</span>' : '');

            card.querySelector('.branch-image-thumb-view')?.addEventListener('click', function () {
                state.activeKey = item.key;
                render();
            });
            card.querySelector('.branch-image-favorite')?.addEventListener('click', function (event) {
                event.stopPropagation();
                state.favoriteKey = item.key;
                state.activeKey = item.key;
                state.dirty = true;
                render();
            });
            card.querySelector('.branch-image-remove')?.addEventListener('click', function (event) {
                event.stopPropagation();
                removeImage(item.key);
            });
            card.addEventListener('dragstart', function (event) {
                event.dataTransfer.effectAllowed = 'move';
                event.dataTransfer.setData('text/plain', item.key);
                card.classList.add('is-dragging');
            });
            card.addEventListener('dragend', function () {
                card.classList.remove('is-dragging');
                thumbs.querySelectorAll('.branch-image-thumb').forEach(function (node) {
                    node.classList.remove('is-drop-target');
                });
            });
            card.addEventListener('dragover', function (event) {
                event.preventDefault();
                card.classList.add('is-drop-target');
            });
            card.addEventListener('dragleave', function () {
                card.classList.remove('is-drop-target');
            });
            card.addEventListener('drop', function (event) {
                event.preventDefault();
                const sourceKey = event.dataTransfer.getData('text/plain');
                card.classList.remove('is-drop-target');
                if (!sourceKey || sourceKey === item.key) return;
                moveBefore(sourceKey, item.key);
            });
            thumbs.appendChild(card);
        });
    }

    function moveBefore(sourceKey, targetKey) {
        const from = state.items.findIndex(function (item) { return item.key === sourceKey; });
        const to = state.items.findIndex(function (item) { return item.key === targetKey; });
        if (from < 0 || to < 0 || from === to) return;
        const moved = state.items.splice(from, 1)[0];
        state.items.splice(from < to ? to - 1 : to, 0, moved);
        state.activeKey = moved.key;
        state.dirty = true;
        render();
    }

    function removeImage(key) {
        const index = state.items.findIndex(function (item) { return item.key === key; });
        if (index < 0) return;
        const item = state.items[index];
        if (item.type === 'existing') state.deletedIds.add(item.id);
        if (item.objectUrl) URL.revokeObjectURL(item.objectUrl);
        state.items.splice(index, 1);
        if (state.favoriteKey === key) state.favoriteKey = state.items[0]?.key || null;
        if (state.activeKey === key) state.activeKey = state.items[0]?.key || null;
        state.dirty = true;
        render();
    }

    function addFiles(fileList) {
        const selected = Array.from(fileList || []);
        if (!selected.length) return;
        if (state.items.length + selected.length > maxImages) {
            showError('Una sede puede tener máximo ' + maxImages + ' imágenes.');
            return;
        }
        for (const file of selected) {
            if (!['image/jpeg', 'image/png', 'image/webp'].includes(file.type)) {
                showError('El archivo "' + file.name + '" no es una imagen JPG, PNG o WEBP válida.');
                return;
            }
            if (file.size > maxSize) {
                showError('La imagen "' + file.name + '" supera los 8 MB.');
                return;
            }
        }
        selected.forEach(function (file) {
            const key = makeKey();
            const objectUrl = URL.createObjectURL(file);
            state.items.push({
                type: 'new',
                id: null,
                key,
                url: objectUrl,
                name: file.name,
                file,
                objectUrl
            });
            if (!state.favoriteKey) state.favoriteKey = key;
            if (!state.activeKey) state.activeKey = key;
        });
        state.dirty = true;
        input.value = '';
        render();
    }

    input.addEventListener('change', function () { addFiles(input.files); });
    if (dropzone) {
        ['dragenter', 'dragover'].forEach(function (eventName) {
            dropzone.addEventListener(eventName, function (event) {
                event.preventDefault();
                dropzone.classList.add('is-drag-over');
            });
        });
        ['dragleave', 'drop'].forEach(function (eventName) {
            dropzone.addEventListener(eventName, function (event) {
                event.preventDefault();
                dropzone.classList.remove('is-drag-over');
            });
        });
        dropzone.addEventListener('drop', function (event) {
            addFiles(event.dataTransfer?.files || []);
        });
    }

    async function save(savedBranchId) {
        if (!state.dirty) return true;
        const formData = new FormData();
        formData.append('branch_id', String(savedBranchId));
        formData.append('order', JSON.stringify(state.items.map(function (item) {
            return item.type === 'existing' ? { type: 'existing', id: item.id } : { type: 'new', key: item.key };
        })));
        formData.append('delete_ids', JSON.stringify(Array.from(state.deletedIds)));

        const favorite = state.items.find(function (item) { return item.key === state.favoriteKey; });
        formData.append('favorite', JSON.stringify(favorite ? (
            favorite.type === 'existing' ? { type: 'existing', id: favorite.id } : { type: 'new', key: favorite.key }
        ) : null));

        const newItems = state.items.filter(function (item) { return item.type === 'new'; });
        formData.append('new_keys', JSON.stringify(newItems.map(function (item) { return item.key; })));
        newItems.forEach(function (item) { formData.append('images[]', item.file, item.file.name); });

        const response = await fetch(window.APP_URL + '/api/admin/branches/images/save', {
            method: 'POST',
            credentials: 'same-origin',
            headers: { 'Accept': 'application/json' },
            body: formData
        });
        const result = await response.json();
        if (!response.ok || !result.success) {
            throw new Error(result.message || 'No fue posible guardar las imágenes de la sede.');
        }

        if (Array.isArray(result.data?.images)) {
            result.data.images.forEach(function (image) {
                if (image && image.id) {
                    image.url = image.url || (window.APP_URL + '/public/' + String(image.file_path || '').replace(/^\/+/, ''));
                }
            });
            config.images = result.data.images;
            state.items.forEach(function (item) {
                if (item.objectUrl) URL.revokeObjectURL(item.objectUrl);
            });
            normalizeExisting(config.images);
        }
        state.deletedIds.clear();
        state.dirty = false;
        render();
        return true;
    }

    normalizeExisting(config.images);
    render();
    return { save };
})();

