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
        !payload.branch_id ||
        payload.branch_id <= 0
    ) {

        showToast(
            'No fue posible determinar la sede.',
            'error'
        );

        return;
    }


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

                    deleteTable(
                        row
                    );
                }
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