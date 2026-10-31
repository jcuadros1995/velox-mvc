(function () {

    'use strict';

    /* =========================================================
       CONFIGURACIÓN
       ========================================================= */

    const config = window.USER_FORM || {};

    const isEdit =
        String(config.mode || '') === 'edit';

    const userId =
        config.userId
            ? Number(config.userId)
            : null;

    const $ = (id) =>
        document.getElementById(id);

    let availablePermissions = [];
    let currentUserPermissions = [];

    let selectedPhotoFile = null;
    let removePhoto = false;


    /* =========================================================
       INIT
       ========================================================= */

    document.addEventListener(
        'DOMContentLoaded',
        init
    );


    async function init() {

        const form =
            $('adminUserForm');

        if (!form) {
            return;
        }

        initPhotoUploader();

        initPassword();

        initPermissions();

        initRoleChange();

        form.addEventListener(
            'submit',
            saveUser
        );

        /*
         * Cargamos permisos disponibles.
         */
        await loadPermissions();

        /*
         * En edición cargamos permisos actuales.
         */
        if (isEdit && userId) {

            await loadCurrentUserPermissions();
        } else {

            renderUserPermissions();
        }

        /*
         * Estado inicial de contraseña.
         */
        updatePasswordStrength();
    }


    /* =========================================================
       FOTO DE USUARIO
       ========================================================= */

    function initPhotoUploader() {

        const dropzone =
            $('userPhotoDropzone');

        const input =
            $('userPhotoInput');

        const preview =
            $('userPhotoPreviewImage');

        const changeButton =
            $('btnChangeUserPhoto');

        const removeButton =
            $('btnRemoveUserPhoto');

        if (!dropzone || !input || !preview) {
            return;
        }

        /*
         * Click sobre zona.
         */
        dropzone.addEventListener(
            'click',
            function (event) {

                if (
                    event.target.closest(
                        '#btnChangeUserPhoto'
                    ) ||
                    event.target.closest(
                        '#btnRemoveUserPhoto'
                    )
                ) {
                    return;
                }

                input.click();
            }
        );


        /*
         * Botón seleccionar.
         */
        changeButton?.addEventListener(
            'click',
            function (event) {

                event.preventDefault();
                event.stopPropagation();

                input.click();
            }
        );


        /*
         * Input.
         */
        input.addEventListener(
            'change',
            function () {

                if (
                    this.files &&
                    this.files.length
                ) {

                    processPhoto(
                        this.files[0]
                    );
                }
            }
        );


        /*
         * Drag Over.
         */
        dropzone.addEventListener(
            'dragover',
            function (event) {

                event.preventDefault();

                dropzone.classList.add(
                    'dragover'
                );
            }
        );


        /*
         * Drag Leave.
         */
        dropzone.addEventListener(
            'dragleave',
            function (event) {

                event.preventDefault();

                dropzone.classList.remove(
                    'dragover'
                );
            }
        );


        /*
         * Drop.
         */
        dropzone.addEventListener(
            'drop',
            function (event) {

                event.preventDefault();

                dropzone.classList.remove(
                    'dragover'
                );

                const files =
                    event.dataTransfer?.files;

                if (
                    !files ||
                    !files.length
                ) {
                    return;
                }

                const file =
                    files[0];

                processPhoto(file);

                /*
                 * Sincronizar input.
                 */
                try {

                    const dataTransfer =
                        new DataTransfer();

                    dataTransfer.items.add(
                        file
                    );

                    input.files =
                        dataTransfer.files;

                } catch (error) {

                    console.warn(
                        '[USER PHOTO] No se pudo sincronizar el input.',
                        error
                    );
                }
            }
        );


        /*
         * Quitar foto.
         */
        removeButton?.addEventListener(
            'click',
            function (event) {

                event.preventDefault();
                event.stopPropagation();

                removePhoto = true;
                selectedPhotoFile = null;

                input.value = '';

                preview.src =
                    getDefaultPhoto();

                dropzone.classList.add(
                    'photo-removed'
                );
            }
        );
    }


    function processPhoto(file) {

        if (!file) {
            return;
        }

        const allowedTypes = [
            'image/jpeg',
            'image/png',
            'image/webp'
        ];

        if (
            !allowedTypes.includes(
                file.type
            )
        ) {

            showError(
                'Solo se permiten imágenes JPG, PNG o WEBP.'
            );

            return;
        }

        const maxSize =
            5 * 1024 * 1024;

        if (file.size > maxSize) {

            showError(
                'La imagen no puede superar los 5 MB.'
            );

            return;
        }

        selectedPhotoFile = file;
        removePhoto = false;

        const reader =
            new FileReader();

        reader.onload =
            function (event) {

                const preview =
                    $('userPhotoPreviewImage');

                const dropzone =
                    $('userPhotoDropzone');

                if (preview) {

                    preview.src =
                        event.target.result;
                }

                dropzone?.classList.remove(
                    'photo-removed'
                );

                dropzone?.classList.add(
                    'has-photo'
                );
            };

        reader.readAsDataURL(file);
    }


    function getDefaultPhoto() {

        return (
            config.defaultPhoto ||
            `${window.APP_URL}/assets/img/default.png`
        );
    }


    /* =========================================================
       PASSWORD
       ========================================================= */

    function initPassword() {

        const passwordInput =
            $('userPassword');

        const toggleButton =
            $('toggleUserPassword');

        if (!passwordInput) {
            return;
        }

        /*
         * Mostrar / ocultar.
         */
        toggleButton?.addEventListener(
            'click',
            function () {

                const visible =
                    passwordInput.type === 'text';

                passwordInput.type =
                    visible
                        ? 'password'
                        : 'text';

                this.innerHTML =
                    visible
                        ? '<i class="fa-solid fa-eye"></i>'
                        : '<i class="fa-solid fa-eye-slash"></i>';

                this.setAttribute(
                    'aria-label',
                    visible
                        ? 'Mostrar contraseña'
                        : 'Ocultar contraseña'
                );
            }
        );


        /*
         * Fortaleza.
         */
        passwordInput.addEventListener(
            'input',
            updatePasswordStrength
        );
    }


    function getPasswordRules(password) {

        return {

            length:
                password.length >= 8,

            upper:
                /[A-Z]/.test(password),

            lower:
                /[a-z]/.test(password),

            number:
                /[0-9]/.test(password),

            special:
                /[^A-Za-z0-9]/.test(password)
        };
    }


    function updatePasswordStrength() {

        const input =
            $('userPassword');

        if (!input) {
            return false;
        }

        const password =
            input.value;

        const rules =
            getPasswordRules(password);

        const requirements = {

            length:
                $('requirementLength'),

            upper:
                $('requirementUpper'),

            lower:
                $('requirementLower'),

            number:
                $('requirementNumber'),

            special:
                $('requirementSpecial')
        };


        /*
         * Marcar requisitos.
         */
        Object.keys(rules)
            .forEach(function (key) {

                const element =
                    requirements[key];

                if (!element) {
                    return;
                }

                element.classList.toggle(
                    'valid',
                    rules[key]
                );

                const icon =
                    element.querySelector(
                        '.password-requirement-icon i'
                    );

                if (icon) {

                    icon.className =
                        rules[key]
                            ? 'fa-solid fa-check'
                            : 'fa-solid fa-circle';
                }
            });


        const completed =
            Object.values(rules)
                .filter(Boolean)
                .length;


        const percentage =
            (completed / 5) * 100;


        const bar =
            $('passwordStrengthBar');

        const text =
            $('passwordStrengthText');


        if (bar) {

            bar.style.width =
                `${percentage}%`;

            if (completed <= 1) {

                bar.style.background =
                    '#ef4444';

            } else if (completed === 2) {

                bar.style.background =
                    '#f59e0b';

            } else if (completed === 3) {

                bar.style.background =
                    '#eab308';

            } else if (completed === 4) {

                bar.style.background =
                    '#22c55e';

            } else {

                bar.style.background =
                    '#16a34a';
            }
        }


        let strength =
            'Muy débil';

        if (completed === 1) {
            strength = 'Débil';
        }

        if (completed === 2) {
            strength = 'Regular';
        }

        if (completed === 3) {
            strength = 'Buena';
        }

        if (completed === 4) {
            strength = 'Fuerte';
        }

        if (completed === 5) {
            strength = 'Excelente';
        }


        if (text) {

            text.textContent =
                strength;
        }


        /*
         * Estado visual general.
         */
        const strengthContainer =
            document.querySelector(
                '.password-strength'
            );

        strengthContainer?.classList.toggle(
            'password-valid',
            completed === 5
        );


        return completed === 5;
    }


    /* =========================================================
       PERMISOS
       ========================================================= */

    function initPermissions() {

        /*
         * Nada adicional aquí por ahora.
         *
         * Los permisos se cargan después del META.
         */
    }


    async function loadPermissions() {

        try {

            const response =
                await fetch(
                    `${window.APP_URL}/api/admin/users/meta`,
                    {
                        method: 'GET',

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
                result.data || result;


            availablePermissions =
                data.permissions || [];


            renderUserPermissions();


        } catch (error) {

            console.error(
                '[USER FORM PERMISSIONS]',
                error
            );


            const container =
                $('userPermissionsContainer');


            if (container) {

                container.innerHTML = `
                    <div class="permissions-loading">
                        <i class="fa-solid fa-triangle-exclamation"></i>
                        No fue posible cargar los permisos.
                    </div>
                `;
            }
        }
    }


    async function loadCurrentUserPermissions() {

        if (!userId) {
            return;
        }

        try {

            const response =
                await fetch(
                    `${window.APP_URL}/api/admin/users/permissions?id=${encodeURIComponent(
                        userId
                    )}`,
                    {
                        method: 'GET',

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
                    'No fue posible cargar los permisos del usuario.'
                );
            }


            const data =
                result.data || {};


            const overrides =
                data.permissions || [];


            /*
             * Calculamos permisos efectivos.
             */
            const roleId =
                Number(
                    data.user?.role_id ||
                    config.roleId ||
                    $('userRole')?.value ||
                    0
                );


            const inherited =
                getRolePermissions(
                    roleId
                );


            const overrideMap =
                new Map();


            overrides.forEach(
                function (item) {

                    overrideMap.set(
                        Number(
                            item.permission_id
                        ),
                        Number(
                            item.is_allowed
                        )
                    );
                }
            );


            currentUserPermissions =
                availablePermissions
                    .filter(function (permission) {

                        const id =
                            Number(
                                permission.id
                            );

                        if (
                            overrideMap.has(id)
                        ) {

                            return (
                                overrideMap.get(id)
                                === 1
                            );
                        }


                        return inherited.includes(
                            permission.slug
                        );
                    })
                    .map(function (permission) {

                        return Number(
                            permission.id
                        );
                    });


            renderUserPermissions();


        } catch (error) {

            console.error(
                '[CURRENT USER PERMISSIONS]',
                error
            );


            /*
             * Si no hay permisos individuales,
             * mostramos los heredados del rol.
             */
            currentUserPermissions =
                getRolePermissionIds();

            renderUserPermissions();
        }
    }


    function initRoleChange() {

        const role =
            $('userRole');

        if (!role) {
            return;
        }

        /*
         * Estado inicial.
         */
        updateBranchByRole();

        role.addEventListener(
            'change',
            function () {

                /*
                 * Actualizar permisos
                 * según el nuevo rol.
                 */
                currentUserPermissions =
                    getRolePermissionIds();

                renderUserPermissions();

                /*
                 * Actualizar sede.
                 */
                updateBranchByRole();
            }
        );
    }

    function updateBranchByRole() {

        const roleSelect =
            $('userRole');

        const branchSelect =
            $('userBranch');

        const branchContainer =
            $('userBranchContainer');

        const globalBranch =
            $('userGlobalBranch');

        if (
            !roleSelect ||
            !branchSelect
        ) {
            return;
        }

        const roleId =
            Number(
                roleSelect.value || 0
            );

        /*
         * Buscar el rol seleccionado.
         */
        const selectedRole =
            config.roles?.find(
                role =>
                    Number(role.id) === roleId
            );

        const roleName =
            String(
                selectedRole?.name || ''
            )
                .trim()
                .toLowerCase();

        const isSuperAdminRole =
            roleName === 'superadmin';

        /*
         * =========================================================
         * SUPERADMIN
         * =========================================================
         */

        if (isSuperAdminRole) {

            /*
             * SuperAdmin no pertenece a ninguna sede.
             */
            branchSelect.value = '';

            branchSelect.required = false;

            branchSelect.disabled = true;

            if (branchContainer) {
                branchContainer.style.display = 'none';
            }

            if (globalBranch) {
                globalBranch.style.display = 'flex';
            }

            return;
        }

        /*
         * =========================================================
         * USUARIO NORMAL
         * =========================================================
         */

        branchSelect.disabled = false;

        branchSelect.required = true;

        if (branchContainer) {
            branchContainer.style.display = '';
        }

        if (globalBranch) {
            globalBranch.style.display = 'none';
        }

        /*
         * Si existe una sede configurada
         * y todavía no hay selección,
         * intentamos conservarla.
         */
        if (
            config.branchId &&
            !branchSelect.value
        ) {

            branchSelect.value =
                String(config.branchId);
        }
    }


    function getRolePermissionIds() {

        const roleSelect =
            $('userRole');

        if (!roleSelect) {
            return [];
        }

        return getPermissionIdsForRole(
            Number(
                roleSelect.value
            )
        );
    }


    function getPermissionIdsForRole(roleId) {

        const roleName =
            String(
                config.roles?.find(
                    role =>
                        Number(role.id) ===
                        Number(roleId)
                )?.name || ''
            )
                .toLowerCase()
                .trim();


        /*
         * Intentamos obtener el nombre
         * desde el SELECT si config.roles
         * no está disponible.
         */
        let finalRoleName =
            roleName;


        if (!finalRoleName) {

            const option =
                document.querySelector(
                    `#userRole option[value="${roleId}"]`
                );

            finalRoleName =
                String(
                    option?.textContent || ''
                )
                    .toLowerCase()
                    .trim();
        }


        const permissionSlugs =
            getRolePermissionsByName(
                finalRoleName
            );


        return availablePermissions
            .filter(function (permission) {

                return permissionSlugs.includes(
                    permission.slug
                );
            })
            .map(function (permission) {

                return Number(
                    permission.id
                );
            });
    }


    function getRolePermissions(roleId) {

        const roleName =
            String(
                config.roles?.find(
                    role =>
                        Number(role.id) ===
                        Number(roleId)
                )?.name || ''
            )
                .toLowerCase()
                .trim();


        return getRolePermissionsByName(
            roleName
        );
    }


    function getRolePermissionsByName(roleName) {

        const normalized =
            String(
                roleName || ''
            )
                .toLowerCase()
                .trim();


        /*
         * Mantiene la misma lógica que ya
         * utiliza el módulo de usuarios.
         */
        const roleMap = {

            superadmin:
                availablePermissions.map(
                    permission =>
                        permission.slug
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
            normalized
            ] || [];
    }


    function normalizePermissionGroups(
        permissionList
    ) {

        const groups = {};


        permissionList.forEach(
            function (permission) {

                const group =
                    permission.module ||
                    permission.section ||
                    permission.category ||
                    'General';


                if (!groups[group]) {

                    groups[group] = [];
                }


                groups[group].push(
                    permission
                );
            }
        );


        return groups;
    }


    function renderUserPermissions() {

        const container =
            $('userPermissionsContainer');

        if (!container) {
            return;
        }


        if (!availablePermissions.length) {

            container.innerHTML = `
                <div class="permissions-loading">
                    <i class="fa-solid fa-circle-info"></i>
                    No hay permisos configurados.
                </div>
            `;

            updatePermissionCount();

            return;
        }


        const groups =
            normalizePermissionGroups(
                availablePermissions
            );


        let html = '';


        Object.entries(groups)
            .forEach(
                function (
                    [groupName, groupPermissions]
                ) {

                    const groupIds =
                        groupPermissions.map(
                            permission =>
                                Number(permission.id)
                        );


                    const selectedInGroup =
                        groupIds.filter(
                            id =>
                                currentUserPermissions
                                    .includes(id)
                        ).length;


                    html += `
                        <div class="permission-group">

                            <div
                                class="permission-group-header"
                                data-permission-group="${escapeHtml(
                        groupName
                    )}"
                            >

                                <div
                                    class="permission-group-title"
                                >

                                    <i class="fa-solid fa-folder"></i>

                                    <span>
                                        ${escapeHtml(
                        groupName
                    )}
                                    </span>

                                </div>

                                <div
                                    class="permission-group-header-right"
                                >

                                    <span
                                        class="permission-group-count"
                                    >
                                        ${selectedInGroup}/${groupPermissions.length}
                                    </span>

                                    <i
                                        class="fa-solid fa-chevron-down permission-group-chevron"
                                    ></i>

                                </div>

                            </div>

                            <div
                                class="permission-group-body"
                            >
                    `;


                    groupPermissions.forEach(
                        function (permission) {

                            const permissionId =
                                Number(
                                    permission.id
                                );


                            const checked =
                                currentUserPermissions
                                    .includes(
                                        permissionId
                                    );


                            const disabled =
                                !isSuperAdmin() &&
                                [
                                    'users.manage',
                                    'settings.update'
                                ].includes(
                                    permission.slug
                                );


                            const inherited =
                                isPermissionInherited(
                                    permission.slug
                                );


                            html += `
                                <div
                                    class="permission-item"
                                >

                                    <div
                                        class="permission-item-info"
                                    >

                                        <span
                                            class="permission-item-name"
                                        >
                                            ${escapeHtml(
                                permission.name ||
                                permission.label ||
                                permission.slug
                            )}
                                        </span>

                                        <span
                                            class="permission-item-slug"
                                        >
                                            ${escapeHtml(
                                permission.slug ||
                                ''
                            )}

                                            ${
                                inherited
                                    ? `
                                                        <span class="permission-inherited">
                                                            · Heredado del rol
                                                        </span>
                                                      `
                                    : ''
                            }

                                        </span>

                                    </div>

                                    <label
                                        class="permission-switch"
                                    >

                                        <input
                                            type="checkbox"
                                            class="user-permission-checkbox"
                                            value="${permissionId}"
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

                                        <span
                                            class="permission-switch-slider"
                                        ></span>

                                    </label>

                                </div>
                            `;
                        }
                    );


                    html += `
                            </div>
                        </div>
                    `;
                }
            );


        container.innerHTML =
            html;


        bindPermissionEvents();

        updatePermissionCount();
    }


    function bindPermissionEvents() {

        document
            .querySelectorAll(
                '.user-permission-checkbox'
            )
            .forEach(
                function (checkbox) {

                    checkbox.addEventListener(
                        'change',
                        function () {

                            updatePermissionCount();

                            currentUserPermissions =
                                getSelectedPermissions();
                        }
                    );
                }
            );


        /*
         * Expandir / contraer grupos.
         */
        document
            .querySelectorAll(
                '.permission-group-header'
            )
            .forEach(
                function (header) {

                    header.addEventListener(
                        'click',
                        function () {

                            const group =
                                this.closest(
                                    '.permission-group'
                                );

                            group?.classList.toggle(
                                'collapsed'
                            );
                        }
                    );
                }
            );
    }


    function isPermissionInherited(
        permissionSlug
    ) {

        const roleSelect =
            $('userRole');

        if (!roleSelect) {
            return false;
        }

        const inherited =
            getRolePermissions(
                Number(
                    roleSelect.value
                )
            );


        return inherited.includes(
            permissionSlug
        );
    }


    function updatePermissionCount() {

        const selected =
            document.querySelectorAll(
                '.user-permission-checkbox:checked'
            ).length;


        const counter =
            $('selectedPermissionsCount');


        if (counter) {

            counter.textContent =
                selected;
        }
    }


    function getSelectedPermissions() {

        const permissions = {};

        document
            .querySelectorAll(
                '.user-permission-checkbox'
            )
            .forEach(
                function (checkbox) {

                    const permissionId =
                        Number(
                            checkbox.value
                        );

                    if (!permissionId) {
                        return;
                    }

                    permissions[permissionId] =
                        checkbox.checked;
                }
            );

        return permissions;
    }


    function isSuperAdmin() {

        return Boolean(
            config.isSuperAdmin
        );
    }


    /* =========================================================
       GUARDAR USUARIO
       ========================================================= */

    async function saveUser(event) {

        event.preventDefault();


        const form =
            event.currentTarget;


        const mode =
            $('userFormMode')?.value ||
            config.mode ||
            'create';


        const currentUserId =
            $('userId')?.value ||
            userId ||
            '';


        const firstName =
            $('userFirstName')?.value
                .trim() || '';


        const lastName =
            $('userLastName')?.value
                .trim() || '';


        const email =
            $('userEmail')?.value
                .trim() || '';


        const phone =
            $('userPhone')?.value
                .trim() || '';


        const documentType =
            $('userDocumentType')?.value
            || '';


        const documentNumber =
            $('userDocumentNumber')?.value
                .trim() || '';


        const roleId =
            Number(
                $('userRole')?.value || 0
            );


        const branchId =
            Number(
                $('userBranch')?.value || 0
            );


        const password =
            $('userPassword')?.value
            || '';


        const active =
            $('userActive')?.checked
                ? 1
                : 0;


        /*
         * Validaciones.
         */
        if (
            !firstName ||
            !lastName ||
            !email ||
            !roleId
        ) {

            showError(
                'Completa todos los campos obligatorios.'
            );

            return;
        }

        /*
         * =========================================================
         * VALIDAR SEDE
         * =========================================================
         *
         * SuperAdmin no necesita sede.
         * Cualquier otro rol sí.
         */

        const roleSelect =
            $('userRole');

        const selectedRole =
            config.roles?.find(
                role =>
                    Number(role.id) === roleId
            );

        const roleName =
            String(
                selectedRole?.name || ''
            )
                .trim()
                .toLowerCase();

        const isSuperAdminRole =
            roleName === 'superadmin';

        if (
            !isSuperAdminRole &&
            !branchId
        ) {

            showError(
                'Debes seleccionar una sede para este usuario.'
            );

            return;
        }

        /*
         * Contraseña obligatoria en CREATE.
         */
        if (
            mode === 'create'
        ) {

            if (!password) {

                showError(
                    'La contraseña es obligatoria.'
                );

                return;
            }

            if (
                !updatePasswordStrength()
            ) {

                showError(
                    'La contraseña no cumple todos los requisitos de seguridad.'
                );

                return;
            }
        }


        /*
         * Contraseña opcional en EDIT.
         */
        if (
            mode === 'edit' &&
            password !== ''
        ) {

            if (
                !updatePasswordStrength()
            ) {

                showError(
                    'La nueva contraseña no cumple todos los requisitos de seguridad.'
                );

                return;
            }
        }


        /*
         * Foto.
         */
        const photoInput =
            $('userPhotoInput');


        if (
            photoInput &&
            photoInput.files &&
            photoInput.files.length
        ) {

            selectedPhotoFile =
                photoInput.files[0];
        }


        /*
         * Botón.
         */
        const button =
            $('btnSaveUserPage');


        const originalButtonHtml =
            button?.innerHTML ||
            'Guardar';


        if (button) {

            button.disabled =
                true;

            button.innerHTML = `
                <i class="fa-solid fa-spinner fa-spin"></i>
                Guardando...
            `;
        }


        try {

            const endpoint =
                mode === 'create'
                    ? '/api/admin/users/store'
                    : '/api/admin/users/update';


            /*
             * IMPORTANTE:
             *
             * Ahora usamos FormData para poder
             * enviar la fotografía real.
             */
            const formData =
                new FormData();


            formData.append(
                'id',
                currentUserId
                    ? String(currentUserId)
                    : ''
            );


            formData.append(
                'first_name',
                firstName
            );


            formData.append(
                'last_name',
                lastName
            );


            formData.append(
                'email',
                email
            );


            formData.append(
                'phone',
                phone
            );


            formData.append(
                'document_type',
                documentType
            );


            formData.append(
                'document_number',
                documentNumber
            );


            formData.append(
                'role_id',
                String(roleId)
            );


            formData.append(
                'branch_id',
                isSuperAdminRole
                    ? ''
                    : String(branchId)
            );

            formData.append(
                'is_active',
                String(active)
            );


            /*
             * La contraseña solo se manda:
             *
             * CREATE:
             * siempre.
             *
             * EDIT:
             * únicamente si el usuario escribió una.
             */
            if (
                mode === 'create' ||
                password !== ''
            ) {

                formData.append(
                    'password',
                    password
                );
            }


            /*
             * Permisos.
             */
            const selectedPermissions =
                getSelectedPermissions();


            formData.append(
                'permissions',
                JSON.stringify(
                    selectedPermissions
                )
            );


            /*
             * Eliminar fotografía actual.
             */
            formData.append(
                'remove_photo',
                removePhoto
                    ? '1'
                    : '0'
            );


            /*
             * Nueva fotografía.
             */
            if (selectedPhotoFile) {

                formData.append(
                    'photo',
                    selectedPhotoFile,
                    selectedPhotoFile.name
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
                            'Accept':
                                'application/json'
                        },

                        body:
                        formData
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
                    'No fue posible guardar el usuario.'
                );
            }


            showSuccess(
                result.message ||
                (
                    mode === 'create'
                        ? 'Usuario creado correctamente.'
                        : 'Usuario actualizado correctamente.'
                )
            );


            setTimeout(
                function () {

                    window.location.href =
                        `${window.APP_URL}/admin/users`;

                },
                700
            );


        } catch (error) {

            console.error(
                '[ADMIN USER FORM]',
                error
            );


            showError(
                error.message ||
                'Ocurrió un error al guardar el usuario.'
            );


            if (button) {

                button.disabled =
                    false;

                button.innerHTML =
                    originalButtonHtml;
            }
        }
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


    function escapeHtml(value) {

        const div =
            document.createElement(
                'div'
            );


        div.textContent =
            value == null
                ? ''
                : String(value);


        return div.innerHTML;
    }

})();