<?php
// app/Views/admin/branches.php

$isSuperAdmin =
    (bool) (
        $branchesPageIsSuperAdmin
        ?? false
    );

$branchId =
    $branchesPageBranchId
    ?? null;

$pageHeaderIcon =
        'fa-solid fa-building';

$pageHeaderEyebrow =
        'ADMINISTRACIÓN';

$pageHeaderTitle =
        'Sedes';

$pageHeaderDescription =
        'Administra las sedes, horarios y mesas de tu plataforma.';

$pageHeaderBadge =
        'Gestión de sedes';

require __DIR__ .
        '/components/page-header.php';
?>

<div class="admin-branches-page">

    <!-- =========================================================
         SCOPE
    ========================================================= -->

    <section class="branches-scope-card">

        <div class="branches-scope-icon">

            <i class="fa-solid fa-building"></i>

        </div>

        <div>

            <span class="branches-scope-label">
                <?= $isSuperAdmin
                    ? 'ALCANCE GLOBAL'
                    : 'MI SEDE'
                ?>
            </span>

            <strong>
                <?= $isSuperAdmin
                    ? 'Todas las sedes'
                    : 'Sede asignada'
                ?>
            </strong>

            <small>
                <?= $isSuperAdmin
                    ? 'Tienes acceso administrativo a todas las ubicaciones de Velox.'
                    : 'Solo puedes consultar y editar la información de tu sede.'
                ?>
            </small>

        </div>

    </section>


    <!-- =========================================================
         ESTADÍSTICAS
    ========================================================= -->

    <section class="branches-stats">

        <div class="branches-stat-card">

            <div class="branches-stat-icon">
                <i class="fa-solid fa-building"></i>
            </div>

            <div>
                <span>Total sedes</span>
                <strong id="branchesStatTotal">0</strong>
            </div>

        </div>


        <div class="branches-stat-card">

            <div class="branches-stat-icon">
                <i class="fa-solid fa-circle-check"></i>
            </div>

            <div>
                <span>Activas</span>
                <strong id="branchesStatActive">0</strong>
            </div>

        </div>


        <div class="branches-stat-card">

            <div class="branches-stat-icon">
                <i class="fa-solid fa-circle-pause"></i>
            </div>

            <div>
                <span>Inactivas</span>
                <strong id="branchesStatInactive">0</strong>
            </div>

        </div>


        <div class="branches-stat-card">

            <div class="branches-stat-icon">
                <i class="fa-solid fa-chart-simple"></i>
            </div>

            <div>
                <span>Disponibilidad</span>
                <strong id="branchesStatRate">
                    0%
                </strong>
            </div>

        </div>

    </section>


    <!-- =========================================================
         PANEL
    ========================================================= -->

    <section class="branches-panel">

        <div class="branches-panel-header">

            <div>

                <strong>
                    Sedes registradas
                </strong>

                <span>
                    Consulta y administra las ubicaciones.
                </span>

            </div>

        </div>


        <!-- =====================================================
             FILTROS
        ===================================================== -->

        <div class="branches-filters">

            <div class="branches-search">

                <i class="fa-solid fa-magnifying-glass"></i>

                <input
                    type="text"
                    id="branchesSearch"
                    class="branches-input"
                    placeholder="Buscar por nombre, código, teléfono..."
                >

            </div>


            <select
                id="branchesStatus"
                class="branches-input branches-status-filter"
            >

                <option value="">
                    Todos los estados
                </option>

                <option value="1">
                    Activas
                </option>

                <option value="0">
                    Inactivas
                </option>

            </select>


            <button
                type="button"
                class="branches-refresh-btn"
                id="btnRefreshBranches"
            >

                <i class="fa-solid fa-rotate"></i>

                Actualizar

            </button>

        </div>


        <!-- =====================================================
             TABLE
        ===================================================== -->

        <div class="branches-table-wrapper">

            <table class="branches-table">

                <thead>

                <tr>

                    <th>
                        SEDE
                    </th>

                    <th>
                        CÓDIGO
                    </th>

                    <th>
                        CONTACTO
                    </th>

                    <th>
                        UBICACIÓN
                    </th>

                    <th>
                        ESTADO
                    </th>

                    <th class="text-end">
                        ACCIONES
                    </th>

                </tr>

                </thead>

                <tbody id="branchesTableBody">

                <tr>

                    <td
                        colspan="6"
                        class="branches-empty"
                    >

                        <i class="fa-solid fa-spinner fa-spin"></i>

                        Cargando sedes...

                    </td>

                </tr>

                </tbody>

            </table>

        </div>

    </section>

</div>


<!-- CONFIRMACIÓN DE ELIMINACIÓN DE SEDE -->
<div
    class="modal fade"
    id="deleteBranchModal"
    tabindex="-1"
    aria-labelledby="deleteBranchModalTitle"
    aria-hidden="true"
>
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content branches-delete-modal">
            <div class="modal-body">
                <div class="branches-delete-icon">
                    <i class="fa-solid fa-triangle-exclamation"></i>
                </div>
                <h5 id="deleteBranchModalTitle">¿Eliminar sede?</h5>
                <p>
                    Estás a punto de eliminar:
                    <strong id="deleteBranchName">esta sede</strong>
                </p>
                <p class="branches-delete-warning">
                    Esta acción eliminará permanentemente la información asociada a la sede,
                    incluyendo reservas, mesas, fotografías, inventario, pedidos y empleados
                    exclusivos. No se puede deshacer.
                </p>
                <div class="branches-delete-actions">
                    <button type="button" class="btn btn-light" data-bs-dismiss="modal">
                        <i class="fa-solid fa-xmark"></i> Cancelar
                    </button>
                    <button type="button" class="btn btn-danger" id="btnConfirmDeleteBranch">
                        <i class="fa-solid fa-trash"></i> Sí, eliminar
                    </button>
                </div>
            </div>
        </div>
    </div>
</div>

<script>

    window.BRANCHES_PAGE = {

        isSuperAdmin:
            <?= $isSuperAdmin ? 'true' : 'false' ?>,

        branchId:
            <?= $branchId !== null
                ? (int) $branchId
                : 'null'
                ?>

    };

</script>