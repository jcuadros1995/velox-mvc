<?php
// app/Views/admin/users.php

$isSuperAdmin =
        (bool) ($usersPageIsSuperAdmin ?? false);

$branchId =
        $usersPageBranchId ?? null;
?>

<div class="admin-users-page">

    <!-- =========================================================
         HEADER
    ========================================================= -->

    <?php

    $pageHeaderIcon =
            'fa-solid fa-users';

    $pageHeaderEyebrow =
            'ADMINISTRACIÓN';

    $pageHeaderTitle =
            'Usuarios y permisos';

    $pageHeaderDescription =
            'Gestiona usuarios, roles, sedes y accesos de la plataforma.';

    $pageHeaderBadge =
            'Gestión de usuarios';

    require __DIR__ .
            '/components/page-header.php';

    ?>


    <!-- =========================================================
         ESTADÍSTICAS
    ========================================================= -->

    <section class="users-stats">

        <!-- TOTAL -->

        <div class="users-stat-card">

            <div class="users-stat-icon">

                <i class="fa-solid fa-users"></i>

            </div>


            <div class="users-stat-content">

                <span>
                    Total usuarios
                </span>

                <strong id="usersStatTotal">
                    0
                </strong>

            </div>

        </div>


        <!-- ACTIVOS -->

        <div class="users-stat-card">

            <div class="users-stat-icon">

                <i class="fa-solid fa-user-check"></i>

            </div>


            <div class="users-stat-content">

                <span>
                    Activos
                </span>

                <strong id="usersStatActive">
                    0
                </strong>

            </div>

        </div>


        <!-- INACTIVOS -->

        <div class="users-stat-card">

            <div class="users-stat-icon">

                <i class="fa-solid fa-user-slash"></i>

            </div>


            <div class="users-stat-content">

                <span>
                    Inactivos
                </span>

                <strong id="usersStatInactive">
                    0
                </strong>

            </div>

        </div>


        <!-- ROLES -->

        <div class="users-stat-card">

            <div class="users-stat-icon">

                <i class="fa-solid fa-shield-halved"></i>

            </div>


            <div class="users-stat-content">

                <span>
                    Roles
                </span>

                <strong id="usersStatRoles">
                    0
                </strong>

            </div>

        </div>

    </section>


    <!-- =========================================================
         PANEL DE USUARIOS
    ========================================================= -->

    <section class="users-panel">


        <!-- =====================================================
             PANEL HEADER
        ===================================================== -->

        <div class="users-panel-header">

            <div class="users-panel-title">

                <strong>
                    Usuarios registrados
                </strong>

                <span>
                    Consulta y administra los accesos.
                </span>

            </div>


            <!-- ALCANCE -->

            <div class="users-scope-badge">

                <span class="users-scope-dot"></span>

                <?php if ($isSuperAdmin): ?>

                    Todas las sedes

                <?php else: ?>

                    Sede actual

                <?php endif; ?>

            </div>

        </div>


        <!-- =====================================================
             FILTROS
        ===================================================== -->

        <div class="users-filters">


            <!-- =================================================
                 SEDE
            ================================================= -->

            <?php if ($isSuperAdmin): ?>

                <div class="users-filter">

                    <label for="usersBranch">
                        Sede
                    </label>


                    <select
                            id="usersBranch"
                            class="users-input"
                    >

                        <option value="">
                            Todas las sedes
                        </option>

                    </select>

                </div>

            <?php endif; ?>


            <!-- =================================================
                 BUSCAR
            ================================================= -->

            <div class="users-filter users-filter-search">

                <label for="usersSearch">
                    Buscar
                </label>


                <div class="users-search">

                    <i class="fa-solid fa-magnifying-glass"></i>


                    <input
                            type="text"
                            id="usersSearch"
                            class="users-input"
                            placeholder="Nombre, correo, teléfono..."
                            autocomplete="off"
                    >

                </div>

            </div>


            <!-- =================================================
                 ROL
            ================================================= -->

            <div class="users-filter">

                <label for="usersRole">
                    Rol
                </label>


                <select
                        id="usersRole"
                        class="users-input"
                >

                    <option value="">
                        Todos los roles
                    </option>

                </select>

            </div>


            <!-- =================================================
                 ESTADO
            ================================================= -->

            <div class="users-filter">

                <label for="usersStatus">
                    Estado
                </label>


                <select
                        id="usersStatus"
                        class="users-input"
                >

                    <option value="">
                        Todos
                    </option>


                    <option value="1">
                        Activos
                    </option>


                    <option value="0">
                        Inactivos
                    </option>

                </select>

            </div>


            <!-- =================================================
                 REFRESCAR
            ================================================= -->

            <button
                    type="button"
                    class="users-refresh-btn"
                    id="btnRefreshUsers"
                    title="Actualizar usuarios"
                    aria-label="Actualizar usuarios"
            >

                <i class="fa-solid fa-arrows-rotate"></i>

            </button>

        </div>


        <!-- =====================================================
             TABLA
        ===================================================== -->

        <div class="users-table-wrapper">

            <table class="users-table">

                <thead>

                <tr>

                    <th>
                        Usuario
                    </th>


                    <?php if ($isSuperAdmin): ?>

                        <th>
                            Sede
                        </th>

                    <?php endif; ?>


                    <th>
                        Rol
                    </th>


                    <th>
                        Contacto
                    </th>


                    <th>
                        Estado
                    </th>


                    <th class="text-end">
                        Acciones
                    </th>

                </tr>

                </thead>


                <tbody id="usersTableBody">

                <tr>

                    <td
                            colspan="<?= $isSuperAdmin ? 6 : 5 ?>"
                            class="users-loading"
                    >

                        <i
                                class="
                                    fa-solid
                                    fa-spinner
                                    fa-spin
                                "
                        ></i>

                        Cargando usuarios...

                    </td>

                </tr>

                </tbody>

            </table>

        </div>

    </section>

</div>


<!-- =========================================================
     MODAL: CONFIRMAR ELIMINACIÓN
========================================================= -->

<div
        class="modal fade"
        id="deleteUserModal"
        tabindex="-1"
        aria-labelledby="deleteUserModalTitle"
        aria-hidden="true"
>

    <div
            class="
            modal-dialog
            modal-dialog-centered
            modal-sm
        "
    >

        <div class="modal-content users-delete-modal">

            <div class="modal-body">


                <!-- =================================================
                     ICONO DE ADVERTENCIA
                ================================================= -->

                <div class="delete-warning-icon">

                    <i class="fa-solid fa-triangle-exclamation"></i>

                </div>


                <!-- =================================================
                     TITULO
                ================================================= -->

                <h5 id="deleteUserModalTitle">
                    ¿Eliminar usuario?
                </h5>


                <!-- =================================================
                     USUARIO
                ================================================= -->

                <p>

                    Estás a punto de desactivar a

                    <strong id="deleteUserName">
                        este usuario
                    </strong>.

                </p>


                <!-- =================================================
                     ADVERTENCIA
                ================================================= -->

                <p class="delete-warning-text">

                    El usuario perderá el acceso al sistema,
                    pero sus registros históricos se conservarán.

                </p>


                <!-- =================================================
                     ACCIONES
                ================================================= -->

                <div class="delete-modal-actions">


                    <button
                            type="button"
                            class="users-secondary-btn"
                            data-bs-dismiss="modal"
                    >

                        <i class="fa-solid fa-xmark"></i>

                        Cancelar

                    </button>


                    <button
                            type="button"
                            class="users-delete-confirm-btn"
                            id="btnConfirmDeleteUser"
                    >

                        <i class="fa-solid fa-user-slash"></i>

                        Sí, eliminar

                    </button>

                </div>

            </div>

        </div>

    </div>

</div>


<!-- =========================================================
     MODAL: PERMISOS
========================================================= -->

<div
        class="modal fade"
        id="permissionsModal"
        tabindex="-1"
        aria-labelledby="permissionsModalTitle"
        aria-hidden="true"
>

    <div
            class="
            modal-dialog
            modal-lg
            modal-dialog-centered
        "
    >

        <div class="modal-content users-modal">


            <!-- =================================================
                 HEADER
            ================================================= -->

            <div class="modal-header">

                <div>

                    <span class="users-modal-eyebrow">
                        CONTROL DE ACCESO
                    </span>


                    <h5
                            class="modal-title"
                            id="permissionsModalTitle"
                    >
                        Permisos del usuario
                    </h5>

                </div>


                <button
                        type="button"
                        class="btn-close"
                        data-bs-dismiss="modal"
                        aria-label="Cerrar"
                ></button>

            </div>


            <!-- =================================================
                 BODY
            ================================================= -->

            <div class="modal-body">


                <!-- =================================================
                     INFORMACIÓN DEL USUARIO
                ================================================= -->

                <div class="permissions-info">

                    <div class="permissions-info-icon">

                        <i class="fa-solid fa-shield-halved"></i>

                    </div>


                    <div>

                        <strong id="permissionsUserName">
                            Usuario
                        </strong>


                        <span id="permissionsUserRole">
                            Rol
                        </span>

                    </div>

                </div>


                <!-- =================================================
                     PERMISOS
                ================================================= -->

                <div
                        id="permissionsContainer"
                        class="permissions-grid"
                ></div>

            </div>


            <!-- =================================================
                 FOOTER
            ================================================= -->

            <div class="modal-footer">


                <button
                        type="button"
                        class="users-secondary-btn"
                        data-bs-dismiss="modal"
                >

                    <i class="fa-solid fa-xmark"></i>

                    Cancelar

                </button>


                <button
                        type="button"
                        class="users-permissions-save-btn"
                        id="btnSavePermissions"
                >

                    <i class="fa-solid fa-shield-halved"></i>

                    Guardar permisos

                </button>

            </div>

        </div>

    </div>

</div>


<!-- =========================================================
     CONFIGURACIÓN PARA JAVASCRIPT
========================================================= -->

<script>

    window.USERS_PAGE = {

        isSuperAdmin:
                <?= $isSuperAdmin ? 'true' : 'false' ?>,

        branchId:
                <?= $branchId !== null
                        ? (int) $branchId
                        : 'null'
                        ?>

    };

</script>