(function () {

    'use strict';

    const config =
        window.USERS_PAGE || {};

    let users = [];
    let roles = [];
    let permissions = [];
    let branches = [];

    let permissionsModal = null;
    let deleteModal = null;

    let permissionsUserId = null;
    let deleteUserId = null;


    const $ = (id) =>
        document.getElementById(id);


    document.addEventListener(
        'DOMContentLoaded',
        init
    );


    /* =========================================================
       INIT
       ========================================================= */

    async function init() {

        const permissionsModalElement =
            $('permissionsModal');


        if (permissionsModalElement) {

            permissionsModal =
                new bootstrap.Modal(
                    permissionsModalElement
                );
        }


        const deleteModalElement =
            $('deleteUserModal');


        if (deleteModalElement) {

            deleteModal =
                new bootstrap.Modal(
                    deleteModalElement
                );
        }


        bindEvents();


        try {

            await loadMeta();

            await loadUsers();

        } catch (error) {

            console.error(
                '[USERS INIT]',
                error
            );

            showError(
                error.message ||
                'No fue posible inicializar el módulo.'
            );
        }
    }


    /* =========================================================
       EVENTS
       ========================================================= */

    function bindEvents() {

        $('btnRefreshUsers')
            ?.addEventListener(
                'click',
                loadUsers
            );


        $('btnSavePermissions')
            ?.addEventListener(
                'click',
                savePermissions
            );


        $('btnConfirmDeleteUser')
            ?.addEventListener(
                'click',
                confirmDeleteUser
            );


        $('usersSearch')
            ?.addEventListener(
                'input',
                debounce(
                    loadUsers,
                    350
                )
            );


        $('usersRole')
            ?.addEventListener(
                'change',
                loadUsers
            );


        $('usersStatus')
            ?.addEventListener(
                'change',
                loadUsers
            );


        $('usersBranch')
            ?.addEventListener(
                'change',
                loadUsers
            );
    }


    /* =========================================================
       META
       ========================================================= */

    async function loadMeta() {

        const response =
            await fetch(
                `${window.APP_URL}/api/admin/users/meta`,
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
                'No fue posible cargar la información.'
            );
        }


        const data =
            result.data || {};


        roles =
            data.roles ||
            result.roles ||
            [];


        permissions =
            data.permissions ||
            result.permissions ||
            [];


        branches =
            data.branches ||
            result.branches ||
            [];


        renderRoleFilters();

        renderBranches();
    }


    /* =========================================================
       USERS
       ========================================================= */

    async function loadUsers() {

        const tbody =
            $('usersTableBody');


        if (!tbody) {
            return;
        }


        const columns =
            config.isSuperAdmin
                ? 6
                : 5;


        tbody.innerHTML = `
            <tr>
                <td
                    colspan="${columns}"
                    class="users-loading"
                >
                    <i class="fa-solid fa-spinner fa-spin"></i>
                    Cargando usuarios...
                </td>
            </tr>
        `;


        const params =
            new URLSearchParams();


        const search =
            $('usersSearch')
                ?.value
                .trim();


        const roleId =
            $('usersRole')
                ?.value;


        const status =
            $('usersStatus')
                ?.value;


        const branchId =
            $('usersBranch')
                ?.value;


        if (search) {

            params.set(
                'search',
                search
            );
        }


        if (roleId) {

            params.set(
                'role_id',
                roleId
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


        /*
         * Solo SuperAdmin puede filtrar
         * cualquier sede.
         */
        if (
            config.isSuperAdmin &&
            branchId
        ) {

            params.set(
                'branch_id',
                branchId
            );
        }


        try {

            const query =
                params.toString();


            const url =
                query
                    ? `${window.APP_URL}/api/admin/users?${query}`
                    : `${window.APP_URL}/api/admin/users`;


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
                    'No fue posible cargar los usuarios.'
                );
            }


            users =
                result.data || [];


            renderUsers();

            updateStats();


        } catch (error) {

            console.error(
                '[USERS DATA]',
                error
            );


            tbody.innerHTML = `
                <tr>
                    <td
                        colspan="${columns}"
                        class="users-loading"
                    >
                        <i class="fa-solid fa-triangle-exclamation"></i>

                        ${escapeHtml(
                error.message
            )}
                    </td>
                </tr>
            `;
        }
    }


    /* =========================================================
       FILTERS
       ========================================================= */

    function renderRoleFilters() {

        const select =
            $('usersRole');


        if (!select) {
            return;
        }


        select.innerHTML =
            '<option value="">Todos los roles</option>';


        roles.forEach(
            function (role) {

                const option =
                    document.createElement(
                        'option'
                    );


                option.value =
                    role.id;


                option.textContent =
                    role.name;


                select.appendChild(
                    option
                );
            }
        );
    }


    function renderBranches() {

        const filter =
            $('usersBranch');


        if (!filter) {
            return;
        }


        filter.innerHTML =
            '<option value="">Todas las sedes</option>';


        branches.forEach(
            function (branch) {

                const option =
                    document.createElement(
                        'option'
                    );


                option.value =
                    branch.id;


                option.textContent =
                    branch.name;


                filter.appendChild(
                    option
                );
            }
        );
    }


    /* =========================================================
       TABLE
       ========================================================= */

    function renderUsers() {

        const tbody =
            $('usersTableBody');


        if (!tbody) {
            return;
        }


        const columns =
            config.isSuperAdmin
                ? 6
                : 5;


        if (!users.length) {

            tbody.innerHTML = `
                <tr>
                    <td
                        colspan="${columns}"
                        class="users-loading"
                    >
                        <i class="fa-solid fa-users-slash"></i>
                        No se encontraron usuarios.
                    </td>
                </tr>
            `;

            return;
        }


        tbody.innerHTML =
            users
                .map(
                    renderUserRow
                )
                .join('');
    }


    function renderUserRow(user) {

        const fullName =
            `${user.first_name || ''} ${user.last_name || ''}`
                .trim();


        const roleName =
            user.role_name ||
            'Sin rol';


        const active =
            Number(
                user.is_active
            ) === 1;


        const avatarFile =
            String(
                user.avatar || ''
            ).trim();

        const avatar =
            avatarFile !== ''
                ? `${window.APP_URL}/assets/img/users/${encodeURIComponent(
                    avatarFile
                )}`
                : `${window.APP_URL}/assets/img/users/default.png`;

        const branchCell =
            config.isSuperAdmin
                ? `
                    <td>
                        <strong>
                            ${escapeHtml(
                    user.branch_name ||
                    'Sin sede'
                )}
                        </strong>
                    </td>
                `
                : '';


        return `
            <tr>

                <td>

                    <div class="user-cell">

                        <img
                            src="${avatar}"
                            class="user-avatar"
                            alt=""
                            onerror="
                                this.onerror=null;
                                this.src='${window.APP_URL}/assets/img/default.png'
                            "
                        >

                        <div>

                            <div class="user-name">
                                ${escapeHtml(
            fullName
        )}
                            </div>

                            <div class="user-email">
                                ${escapeHtml(
            user.email || ''
        )}
                            </div>

                        </div>

                    </div>

                </td>


                ${branchCell}


                <td>

                    <span class="user-role-badge">
                        ${escapeHtml(
            roleName
        )}
                    </span>

                </td>


                <td>
                    ${escapeHtml(
            user.phone ||
            'Sin teléfono'
        )}
                </td>


                <td>

                    <span
                        class="
                            user-status-badge
                            ${
            active
                ? 'user-status-active'
                : 'user-status-inactive'
        }
                        "
                    >

                        <i
                            class="
                                fa-solid
                                ${
            active
                ? 'fa-check'
                : 'fa-xmark'
        }
                            "
                        ></i>

                        ${
            active
                ? 'Activo'
                : 'Inactivo'
        }

                    </span>

                </td>


                <td>

                    <div class="user-actions">

                        <!-- EDITAR -->

                        <a
                            href="${window.APP_URL}/admin/users/edit?id=${encodeURIComponent(
            user.id
        )}"
                            class="user-action-btn"
                            title="Editar usuario"
                            aria-label="Editar usuario"
                        >
                            <i class="fa-solid fa-pen"></i>
                        </a>


                        <!-- PERMISOS -->

                        <button
                            type="button"
                            class="user-action-btn"
                            title="Gestionar permisos"
                            aria-label="Gestionar permisos"
                            onclick="
                                window.UsersAdmin.permissions(
                                    ${Number(user.id)}
                                )
                            "
                        >
                            <i class="fa-solid fa-shield-halved"></i>
                        </button>


                        <!-- ELIMINAR -->

                        ${
            active
                ? `
                                    <button
                                        type="button"
                                        class="
                                            user-action-btn
                                            user-action-danger
                                        "
                                        title="Eliminar usuario"
                                        aria-label="Eliminar usuario"
                                        onclick="
                                            window.UsersAdmin.delete(
                                                ${Number(user.id)}
                                            )
                                        "
                                    >
                                        <i class="fa-solid fa-user-slash"></i>
                                    </button>
                                  `
                : ''
        }

                    </div>

                </td>

            </tr>
        `;
    }


    /* =========================================================
       STATS
       ========================================================= */

    function updateStats() {

        const total =
            users.length;


        const active =
            users.filter(
                function (user) {

                    return Number(
                        user.is_active
                    ) === 1;
                }
            ).length;


        const inactive =
            total - active;


        if ($('usersStatTotal')) {

            $('usersStatTotal')
                .textContent =
                total;
        }


        if ($('usersStatActive')) {

            $('usersStatActive')
                .textContent =
                active;
        }


        if ($('usersStatInactive')) {

            $('usersStatInactive')
                .textContent =
                inactive;
        }


        if ($('usersStatRoles')) {

            const uniqueRoles =
                new Set(
                    users.map(
                        function (user) {

                            return user.role_id;
                        }
                    )
                );


            $('usersStatRoles')
                .textContent =
                uniqueRoles.size;
        }
    }


    /* =========================================================
       DELETE
       ========================================================= */

    function openDeleteModal(userId) {

        const user =
            users.find(
                function (item) {

                    return Number(item.id) ===
                        Number(userId);
                }
            );


        if (!user) {
            return;
        }


        deleteUserId =
            Number(userId);


        const fullName =
            `${user.first_name || ''} ${user.last_name || ''}`
                .trim();


        const nameElement =
            $('deleteUserName');


        if (nameElement) {

            nameElement.textContent =
                fullName;
        }


        deleteModal?.show();
    }


    async function confirmDeleteUser() {

        if (!deleteUserId) {
            return;
        }


        const button =
            $('btnConfirmDeleteUser');


        if (button) {

            button.disabled =
                true;


            button.innerHTML = `
                <i class="fa-solid fa-spinner fa-spin"></i>
                Eliminando...
            `;
        }


        try {

            const response =
                await fetch(
                    `${window.APP_URL}/api/admin/users/delete`,
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
                                deleteUserId
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
                    'No fue posible eliminar el usuario.'
                );
            }


            deleteModal?.hide();


            showSuccess(
                result.message ||
                'Usuario eliminado correctamente.'
            );


            deleteUserId =
                null;


            await loadUsers();


        } catch (error) {

            console.error(
                '[DELETE USER]',
                error
            );


            showError(
                error.message
            );


        } finally {

            if (button) {

                button.disabled =
                    false;


                button.innerHTML = `
                    <i class="fa-solid fa-user-slash"></i>
                    Sí, eliminar
                `;
            }
        }
    }


    /* =========================================================
       PERMISSIONS MODAL
       ========================================================= */

    async function openPermissions(userId) {

        permissionsUserId =
            Number(userId);


        try {

            const response =
                await fetch(
                    `${window.APP_URL}/api/admin/users/permissions?id=${encodeURIComponent(
                        userId
                    )}`,
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
                    'No fue posible cargar los permisos.'
                );
            }


            const data =
                result.data || {};


            const user =
                data.user || {};


            const overrides =
                data.permissions || [];


            const userName =
                `${user.first_name || ''} ${user.last_name || ''}`
                    .trim();


            const nameElement =
                $('permissionsUserName');


            if (nameElement) {

                nameElement.textContent =
                    userName;
            }


            const roleElement =
                $('permissionsUserRole');


            if (roleElement) {

                roleElement.textContent =
                    user.role_name || '';
            }


            const overrideMap =
                new Map(
                    overrides.map(
                        function (item) {

                            return [
                                Number(
                                    item.permission_id
                                ),
                                Number(
                                    item.is_allowed
                                )
                            ];
                        }
                    )
                );


            const inherited =
                getRolePermissions(
                    Number(
                        user.role_id
                    )
                );


            const container =
                $('permissionsContainer');


            if (!container) {
                return;
            }


            container.innerHTML =
                permissions
                    .map(
                        function (permission) {

                            const inheritedPermission =
                                inherited.includes(
                                    permission.slug
                                );


                            const override =
                                overrideMap.get(
                                    Number(
                                        permission.id
                                    )
                                );


                            const checked =
                                override !== undefined
                                    ? override === 1
                                    : inheritedPermission;


                            const disabled =
                                !config.isSuperAdmin &&
                                [
                                    'users.manage',
                                    'settings.update'
                                ].includes(
                                    permission.slug
                                );


                            return `
                                <label
                                    class="
                                        permission-card
                                        ${
                                disabled
                                    ? 'permission-disabled'
                                    : ''
                            }
                                    "
                                >

                                    <input
                                        type="checkbox"
                                        class="permission-check"
                                        data-permission-id="${Number(
                                permission.id
                            )}"
                                        ${
                                checked
                                    ? 'checked'
                                    : ''
                            }
                                        ${
                                disabled
                                    ? 'disabled'
                                    : ''
                            }
                                    >

                                    <span>

                                        <strong>
                                            ${escapeHtml(
                                permission.slug
                            )}
                                        </strong>

                                        <span>
                                            ${escapeHtml(
                                permission.module ||
                                ''
                            )}

                                            ${
                                inheritedPermission
                                    ? ' · heredado del rol'
                                    : ''
                            }
                                        </span>

                                    </span>

                                </label>
                            `;
                        }
                    )
                    .join('');


            permissionsModal?.show();


        } catch (error) {

            console.error(
                '[USER PERMISSIONS]',
                error
            );


            showError(
                error.message
            );
        }
    }


    async function savePermissions() {

        if (!permissionsUserId) {
            return;
        }


        const inputs =
            document.querySelectorAll(
                '.permission-check'
            );


        const selected =
            {};


        inputs.forEach(
            function (input) {

                selected[
                    input.dataset.permissionId
                    ] =
                    input.checked;
            }
        );


        const button =
            $('btnSavePermissions');


        if (button) {

            button.disabled =
                true;


            button.innerHTML = `
                <i class="fa-solid fa-spinner fa-spin"></i>
                Guardando...
            `;
        }


        try {

            const response =
                await fetch(
                    `${window.APP_URL}/api/admin/users/permissions`,
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

                                user_id:
                                permissionsUserId,

                                permissions:
                                selected
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
                    'No fue posible guardar los permisos.'
                );
            }


            permissionsModal?.hide();


            showSuccess(
                result.message ||
                'Permisos guardados correctamente.'
            );


        } catch (error) {

            console.error(
                '[SAVE PERMISSIONS]',
                error
            );


            showError(
                error.message
            );


        } finally {

            if (button) {

                button.disabled =
                    false;


                button.innerHTML = `
                    <i class="fa-solid fa-shield-halved"></i>
                    Guardar permisos
                `;
            }
        }
    }


    /* =========================================================
       ROLE PERMISSIONS
       ========================================================= */

    function getRolePermissions(roleId) {

        const role =
            roles.find(
                function (item) {

                    return Number(item.id) ===
                        Number(roleId);
                }
            );


        if (!role) {
            return [];
        }


        const roleName =
            String(
                role.name || ''
            )
                .toLowerCase()
                .trim();


        const roleMap = {

            superadmin:
                permissions.map(
                    function (permission) {

                        return permission.slug;
                    }
                ),

            admin: [
                'users.manage',
                'bookings.create',
                'bookings.read',
                'bookings.cancel',
                'orders.manage',
                'inventory.manage'
            ],

            staff: [
                'bookings.create',
                'bookings.read',
                'bookings.cancel'
            ],

            customer: [
                'bookings.create',
                'bookings.read'
            ]
        };


        return roleMap[
            roleName
            ] || [];
    }


    /* =========================================================
       HELPERS
       ========================================================= */

    function showSuccess(message) {

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
                icon: 'success',
                title: 'Correcto',
                text: message,
                timer: 1600,
                showConfirmButton: false
            });

            return;
        }


        alert(message);
    }


    function showError(message) {

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
                icon: 'error',
                title: 'Atención',
                text: message
            });

            return;
        }


        alert(message);
    }


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
                    callback,
                    delay
                );
        };
    }


    function escapeHtml(value) {

        const div =
            document.createElement(
                'div'
            );


        div.textContent =
            value ?? '';


        return div.innerHTML;
    }


    /* =========================================================
       PUBLIC API
       ========================================================= */

    window.UsersAdmin = {

        permissions:
        openPermissions,

        delete:
        openDeleteModal
    };

})();