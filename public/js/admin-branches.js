(function () {

    'use strict';

    const config = window.BRANCHES_PAGE || {};

    let branches = [];
    let deleteModal = null;
    let deleteBranchId = null;

    const $ = (id) => document.getElementById(id);

    document.addEventListener(
        'DOMContentLoaded',
        init
    );

    /**
     * Inicialización
     */
    async function init() {

        /**
         * Modal de eliminación
         */
        const deleteElement = $('deleteBranchModal');

        if (deleteElement) {
            deleteModal = new bootstrap.Modal(
                deleteElement
            );
        }

        bindEvents();

        await loadBranches();
    }

    /**
     * Eventos
     */
    function bindEvents() {

        /**
         * Crear sede
         *
         * Ahora la creación se realiza
         * en una página independiente.
         */
        $('btnCreateBranch')
            ?.addEventListener(
                'click',
                function () {

                    window.location.href =
                        `${window.APP_URL}/admin/branches/create`;
                }
            );

        /**
         * Recargar listado
         */
        $('btnRefreshBranches')
            ?.addEventListener(
                'click',
                loadBranches
            );

        /**
         * Búsqueda
         */
        $('branchesSearch')
            ?.addEventListener(
                'input',
                debounce(
                    loadBranches,
                    300
                )
            );

        /**
         * Estado
         */
        $('branchesStatus')
            ?.addEventListener(
                'change',
                loadBranches
            );

        /**
         * Confirmar eliminación
         */
        $('btnConfirmDeleteBranch')
            ?.addEventListener(
                'click',
                deleteBranch
            );
    }

    /**
     * Cargar sedes
     */
    async function loadBranches() {

        const body = $('branchesTableBody');

        if (body) {

            body.innerHTML = `
                <tr>
                    <td colspan="6" class="branches-empty">
                        <i class="fa-solid fa-spinner fa-spin"></i>
                        Cargando sedes...
                    </td>
                </tr>
            `;
        }

        try {

            const params =
                new URLSearchParams();

            const search =
                $('branchesSearch')
                    ?.value
                    ?.trim();

            const status =
                $('branchesStatus')
                    ?.value;

            if (search) {

                params.set(
                    'search',
                    search
                );
            }

            if (
                status !== undefined &&
                status !== ''
            ) {

                params.set(
                    'is_active',
                    status
                );
            }

            const queryString =
                params.toString();

            const url =
                queryString
                    ? `${window.APP_URL}/api/admin/branches?${queryString}`
                    : `${window.APP_URL}/api/admin/branches`;

            const response =
                await fetch(
                    url,
                    {
                        credentials:
                            'same-origin',

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
                    'No fue posible cargar las sedes.'
                );
            }

            branches =
                result.data || [];

            updateStatistics(
                result.statistics || {}
            );

            renderBranches();

        } catch (error) {

            console.error(
                '[BRANCHES]',
                error
            );

            showError(
                error.message ||
                'No fue posible cargar las sedes.'
            );
        }
    }

    /**
     * Actualizar estadísticas
     */
    function updateStatistics(
        statistics
    ) {

        const total =
            Number(
                statistics.total || 0
            );

        const active =
            Number(
                statistics.active || 0
            );

        const inactive =
            Number(
                statistics.inactive || 0
            );

        const rate =
            total > 0
                ? Math.round(
                    (active / total) * 100
                )
                : 0;

        const totalElement =
            $('branchesStatTotal');

        const activeElement =
            $('branchesStatActive');

        const inactiveElement =
            $('branchesStatInactive');

        const rateElement =
            $('branchesStatRate');

        if (totalElement) {
            totalElement.textContent =
                total;
        }

        if (activeElement) {
            activeElement.textContent =
                active;
        }

        if (inactiveElement) {
            inactiveElement.textContent =
                inactive;
        }

        if (rateElement) {
            rateElement.textContent =
                `${rate}%`;
        }
    }

    /**
     * Renderizar sedes
     */
    function renderBranches() {

        const body =
            $('branchesTableBody');

        if (!body) {
            return;
        }

        if (!branches.length) {

            body.innerHTML = `
                <tr>
                    <td colspan="6" class="branches-empty">
                        <i class="fa-solid fa-building-circle-xmark"></i>
                        No se encontraron sedes.
                    </td>
                </tr>
            `;

            return;
        }

        body.innerHTML =
            branches
                .map(
                    branch =>
                        renderBranch(
                            branch
                        )
                )
                .join('');

        /**
         * Botones editar
         */
        body.querySelectorAll(
            '[data-action="edit"]'
        ).forEach(
            button => {

                button.addEventListener(
                    'click',
                    function () {

                        const id =
                            Number(
                                this.dataset.id
                            );

                        openEdit(id);
                    }
                );
            }
        );

        /**
         * Botones eliminar
         */
        body.querySelectorAll(
            '[data-action="delete"]'
        ).forEach(
            button => {

                button.addEventListener(
                    'click',
                    function () {

                        const id =
                            Number(
                                this.dataset.id
                            );

                        openDelete(id);
                    }
                );
            }
        );
    }

    /**
     * HTML de una sede
     */
    function renderBranch(
        branch
    ) {

        const active =
            Number(
                branch.is_active
            ) === 1;

        const initials =
            getInitials(
                branch.name
            );

        /**
         * Solo SuperAdmin puede eliminar
         */
        const canDelete =
            config.isSuperAdmin === true;

        return `

            <tr>

                <!-- Sede -->
                <td>

                    <div class="branch-table-name">

                        <div class="branch-avatar">
                            ${escapeHtml(
            initials
        )}
                        </div>

                        <div>

                            <strong>
                                ${escapeHtml(
            branch.name
        )}
                            </strong>

                            <span>
                                ${escapeHtml(
            branch.email ||
            'Sin correo'
        )}
                            </span>

                        </div>

                    </div>

                </td>

                <!-- Código -->
                <td>

                    <span class="branch-code">
                        ${escapeHtml(
            branch.code
        )}
                    </span>

                </td>

                <!-- Teléfono -->
                <td>

                    <span class="branch-contact">

                        <i class="fa-solid fa-phone"></i>

                        ${escapeHtml(
            branch.phone ||
            'Sin teléfono'
        )}

                    </span>

                </td>

                <!-- Dirección -->
                <td>

                    <span class="branch-address">

                        <i class="fa-solid fa-location-dot"></i>

                        ${escapeHtml(
            branch.address ||
            'Sin dirección'
        )}

                    </span>

                </td>

                <!-- Estado -->
                <td>

                    <span class="
                        branch-status
                        ${active
            ? 'is-active'
            : 'is-inactive'
        }
                    ">

                        <span></span>

                        ${active
            ? 'Activa'
            : 'Inactiva'
        }

                    </span>

                </td>

                <!-- Acciones -->
                <td>

                    <div class="branch-actions">

                        <!-- Editar -->
                        <button
                            type="button"
                            class="branch-action-btn"
                            title="Editar"
                            data-action="edit"
                            data-id="${branch.id}"
                        >
                            <i class="fa-solid fa-pen"></i>
                        </button>

                        ${
            canDelete
                ? `
                                    <button
                                        type="button"
                                        class="branch-action-btn danger"
                                        title="Eliminar"
                                        data-action="delete"
                                        data-id="${branch.id}"
                                    >
                                        <i class="fa-solid fa-trash"></i>
                                    </button>
                                `
                : ''
        }

                    </div>

                </td>

            </tr>
        `;
    }

    /**
     * Ir a página de edición
     *
     * Ya NO abre modal.
     */
    function openEdit(id) {

        if (!id) {
            return;
        }

        window.location.href =
            `${window.APP_URL}/admin/branches/edit?id=${encodeURIComponent(id)}`;
    }

    /**
     * Abrir confirmación de eliminación
     */
    function openDelete(id) {

        const branch =
            branches.find(
                item =>
                    Number(item.id) === id
            );

        if (!branch) {
            return;
        }

        /**
         * Seguridad adicional:
         * solamente SuperAdmin puede eliminar.
         */
        if (
            config.isSuperAdmin !== true
        ) {

            showError(
                'No tienes permisos para eliminar sedes.'
            );

            return;
        }

        deleteBranchId =
            id;

        const nameElement =
            $('deleteBranchName');

        if (nameElement) {

            nameElement.textContent =
                branch.name || 'esta sede';
        }

        deleteModal?.show();
    }

    /**
     * Eliminar sede
     */
    async function deleteBranch() {

        if (!deleteBranchId) {
            return;
        }

        /**
         * Seguridad adicional
         */
        if (
            config.isSuperAdmin !== true
        ) {

            showError(
                'No tienes permisos para eliminar sedes.'
            );

            return;
        }

        const button =
            $('btnConfirmDeleteBranch');

        if (button) {
            button.disabled = true;
        }

        try {

            const response =
                await fetch(
                    `${window.APP_URL}/api/admin/branches/delete`,
                    {
                        method:
                            'POST',

                        headers: {

                            'Content-Type':
                                'application/json',

                            'Accept':
                                'application/json'
                        },

                        credentials:
                            'same-origin',

                        body:
                            JSON.stringify({
                                id:
                                deleteBranchId
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
                    'No fue posible eliminar la sede.'
                );
            }

            deleteModal?.hide();

            showSuccess(
                result.message ||
                'Sede eliminada correctamente.'
            );

            await loadBranches();

        } catch (error) {

            console.error(
                '[BRANCHES DELETE]',
                error
            );

            showError(
                error.message ||
                'No fue posible eliminar la sede.'
            );

        } finally {

            if (button) {
                button.disabled = false;
            }

            deleteBranchId =
                null;
        }
    }

    /**
     * Obtener iniciales
     */
    function getInitials(
        value
    ) {

        return String(
            value || 'S'
        )
            .trim()
            .split(/\s+/)
            .filter(Boolean)
            .slice(0, 2)
            .map(
                item =>
                    item
                        .charAt(0)
                        .toUpperCase()
            )
            .join('');
    }

    /**
     * Escapar HTML
     */
    function escapeHtml(
        value
    ) {

        return String(
            value ?? ''
        )
            .replace(
                /&/g,
                '&amp;'
            )
            .replace(
                /</g,
                '&lt;'
            )
            .replace(
                />/g,
                '&gt;'
            )
            .replace(
                /"/g,
                '&quot;'
            )
            .replace(
                /'/g,
                '&#039;'
            );
    }

    /**
     * Debounce
     */
    function debounce(
        callback,
        delay
    ) {

        let timeout;

        return function () {

            clearTimeout(
                timeout
            );

            timeout =
                setTimeout(
                    () => callback(),
                    delay
                );
        };
    }

    /**
     * Mostrar error
     */
    function showError(
        message
    ) {

        if (
            typeof window.showToast ===
            'function'
        ) {

            window.showToast(
                message,
                'error'
            );

            return;
        }

        alert(message);
    }

    /**
     * Mostrar éxito
     */
    function showSuccess(
        message
    ) {

        if (
            typeof window.showToast ===
            'function'
        ) {

            window.showToast(
                message,
                'success'
            );

            return;
        }

        alert(message);
    }

})();