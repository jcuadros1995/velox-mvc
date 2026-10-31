<?php

$mode =
    $branchFormMode
    ?? 'create';

$isEdit =
    $mode === 'edit';

$branch =
    $branchFormBranch
    ?? null;

$hours =
        $branchFormHours
        ?? [];

$tables =
        $branchFormTables
        ?? [];

$days = [
    1 => 'Lunes',
    2 => 'Martes',
    3 => 'Miércoles',
    4 => 'Jueves',
    5 => 'Viernes',
    6 => 'Sábado',
    7 => 'Domingo'
];
?>

<div class="admin-branch-form-page">

    <div class="branch-form-topbar">

        <div>

            <a
                href="<?= APP_URL ?>/admin/branches"
                class="branch-back-link"
            >
                <i class="fa-solid fa-arrow-left"></i>
                Sedes
            </a>

            <div class="branch-form-eyebrow">
                CONFIGURACIÓN DE SEDE
            </div>

            <h1>
                <?= $isEdit
                    ? 'Editar sede'
                    : 'Nueva sede'
                ?>
            </h1>

            <p>
                <?= $isEdit
                    ? 'Actualiza la información y los horarios de atención.'
                    : 'Registra una nueva sede y configura sus horarios.'
                ?>
            </p>

        </div>

    </div>


    <form
        id="branchForm"
        novalidate
    >

        <input
            type="hidden"
            id="branchId"
            value="<?= $branch
                ? (int) $branch['id']
                : ''
            ?>"
        >


        <!-- INFORMACIÓN -->

        <section class="branch-form-card">

            <div class="branch-form-card-header">

                <div class="branch-form-card-icon">
                    <i class="fa-solid fa-building"></i>
                </div>

                <div>

                    <h2>
                        Información general
                    </h2>

                    <p>
                        Datos principales de la sede.
                    </p>

                </div>

            </div>


            <div class="branch-form-grid">

                <div class="branch-form-field">

                    <label for="branchCode">
                        Código
                    </label>

                    <input
                        type="text"
                        id="branchCode"
                        maxlength="20"
                        value="<?= htmlspecialchars(
                            $branch['code'] ?? '',
                            ENT_QUOTES,
                            'UTF-8'
                        ) ?>"
                        placeholder="Ej. NORTE"
                        required
                    >

                    <small>
                        Letras, números, guion o guion bajo.
                    </small>

                </div>


                <div class="branch-form-field">

                    <label for="branchName">
                        Nombre de la sede
                    </label>

                    <input
                        type="text"
                        id="branchName"
                        maxlength="150"
                        value="<?= htmlspecialchars(
                            $branch['name'] ?? '',
                            ENT_QUOTES,
                            'UTF-8'
                        ) ?>"
                        placeholder="Ej. Sede Norte"
                        required
                    >

                </div>


                <div class="branch-form-field">

                    <label for="branchPhone">
                        Teléfono
                    </label>

                    <input
                        type="text"
                        id="branchPhone"
                        value="<?= htmlspecialchars(
                            $branch['phone'] ?? '',
                            ENT_QUOTES,
                            'UTF-8'
                        ) ?>"
                        placeholder="+57 300 000 0000"
                    >

                </div>


                <div class="branch-form-field">

                    <label for="branchEmail">
                        Correo electrónico
                    </label>

                    <input
                        type="email"
                        id="branchEmail"
                        value="<?= htmlspecialchars(
                            $branch['email'] ?? '',
                            ENT_QUOTES,
                            'UTF-8'
                        ) ?>"
                        placeholder="sede@velox.com"
                    >

                </div>


                <div class="branch-form-field branch-form-field-full">

                    <label for="branchAddress">
                        Dirección
                    </label>

                    <textarea
                        id="branchAddress"
                        rows="3"
                        placeholder="Dirección completa"
                    ><?= htmlspecialchars(
                            $branch['address'] ?? '',
                            ENT_QUOTES,
                            'UTF-8'
                        ) ?></textarea>

                </div>

            </div>


            <div class="branch-active-row">

                <div>

                    <strong>
                        Sede activa
                    </strong>

                    <span>
                        Las sedes inactivas no estarán disponibles
                        para nuevas reservas.
                    </span>

                </div>

                <label class="branch-switch">

                    <input
                        type="checkbox"
                        id="branchActive"
                        <?= !$branch ||
                        (int) $branch['is_active'] === 1
                            ? 'checked'
                            : ''
                        ?>
                    >

                    <span></span>

                </label>

            </div>

        </section>


        <!-- HORARIOS -->

        <section class="branch-form-card">

            <div class="branch-form-card-header">

                <div class="branch-form-card-icon">
                    <i class="fa-regular fa-clock"></i>
                </div>

                <div>

                    <h2>
                        Horarios de atención
                    </h2>

                    <p>
                        Estos horarios controlarán la disponibilidad
                        pública de esta sede.
                    </p>

                </div>

            </div>


            <div class="branch-hours-list">

                <?php foreach ($days as $day => $label): ?>

                    <?php

                    $dayData =
                        $hours[$day]
                        ?? [
                        'open_time' => null,
                        'close_time' => null,
                        'is_closed' => true
                    ];

                    $closed =
                        !empty(
                        $dayData['is_closed']
                        );

                    $open =
                        $dayData['open_time']
                            ? substr(
                            $dayData['open_time'],
                            0,
                            5
                        )
                            : '';

                    $close =
                        $dayData['close_time']
                            ? substr(
                            $dayData['close_time'],
                            0,
                            5
                        )
                            : '';
                    ?>

                    <div
                        class="branch-hour-row"
                        data-day="<?= $day ?>"
                    >

                        <div class="branch-hour-day">

                            <strong>
                                <?= $label ?>
                            </strong>

                        </div>


                        <label class="branch-hour-toggle">

                            <input
                                type="checkbox"
                                class="hour-open-toggle"
                                <?= !$closed
                                    ? 'checked'
                                    : ''
                                ?>
                            >

                            <span></span>

                        </label>


                        <span class="branch-hour-status">
                            <?= $closed
                                ? 'Cerrado'
                                : 'Abierto'
                            ?>
                        </span>


                        <div class="branch-hour-times">

                            <input
                                type="time"
                                class="hour-open"
                                value="<?= htmlspecialchars(
                                    $open,
                                    ENT_QUOTES,
                                    'UTF-8'
                                ) ?>"
                                <?= $closed
                                    ? 'disabled'
                                    : ''
                                ?>
                            >

                            <span>
                                a
                            </span>

                            <input
                                type="time"
                                class="hour-close"
                                value="<?= htmlspecialchars(
                                    $close,
                                    ENT_QUOTES,
                                    'UTF-8'
                                ) ?>"
                                <?= $closed
                                    ? 'disabled'
                                    : ''
                                ?>
                            >

                        </div>

                    </div>

                <?php endforeach; ?>

            </div>

        </section>

        <!-- =====================================================
             MESAS DE LA SEDE
        ====================================================== -->

        <?php if ($isEdit): ?>

            <section class="branch-form-card">

                <div class="branch-form-card-header">

                    <div class="branch-form-card-icon">
                        <i class="fa-solid fa-chair"></i>
                    </div>

                    <div>

                        <h2>
                            Mesas de la sede
                        </h2>

                        <p>
                            Configura las mesas disponibles,
                            su capacidad y ubicación.
                        </p>

                    </div>

                    <div class="branch-tables-header-actions">

                        <div class="branch-tables-summary">

                            <strong id="tablesCount">
                                <?= count($tables ?? []) ?>
                            </strong>

                            <span>
                                mesas
                            </span>

                        </div>

                        <div class="branch-tables-summary">

                            <strong id="tablesCapacity">
                                <?=
                                array_sum(
                                        array_map(
                                                static function ($table) {
                                                    return (int)
                                                    ($table['capacity'] ?? 0);
                                                },
                                                $tables ?? []
                                        )
                                )
                                ?>
                            </strong>

                            <span>
                                puestos
                            </span>

                        </div>

                    </div>

                </div>


                <div class="branch-tables-toolbar">

                    <div>

                        <span class="branch-tables-toolbar-title">
                            Administración de mesas
                        </span>

                        <span class="branch-tables-toolbar-description">
                            Una mesa inactiva no será utilizada
                            para nuevas reservas.
                        </span>

                    </div>

                    <button
                            type="button"
                            class="branch-form-add-table"
                            id="btnAddTable"
                    >
                        <i class="fa-solid fa-plus"></i>
                        Agregar mesa
                    </button>

                </div>


                <div
                        class="branch-tables-list"
                        id="branchTablesList"
                >

                    <?php if (empty($tables)): ?>

                        <div
                                class="branch-tables-empty"
                                id="tablesEmpty"
                        >

                            <div class="branch-tables-empty-icon">
                                <i class="fa-solid fa-chair"></i>
                            </div>

                            <strong>
                                Esta sede todavía no tiene mesas
                            </strong>

                            <span>
                                Agrega la primera mesa para comenzar
                                a gestionar la capacidad de reservas.
                            </span>

                            <button
                                    type="button"
                                    class="branch-form-add-table"
                                    id="btnAddTableEmpty"
                            >
                                <i class="fa-solid fa-plus"></i>
                                Agregar primera mesa
                            </button>

                        </div>

                    <?php else: ?>

                        <?php foreach ($tables as $table): ?>

                            <?php
                            $tableActive =
                                    (int) (
                                            $table['is_active']
                                            ?? 0
                                    ) === 1;
                            ?>

                            <div
                                    class="branch-table-item <?= $tableActive
                                            ? ''
                                            : 'is-inactive'
                                    ?>"
                                    data-table-id="<?= (int) $table['id'] ?>"
                            >

                                <div class="branch-table-item-number">

                                    <span>
                                        Mesa
                                    </span>

                                    <strong>
                                        #<?= (int) $table['table_number'] ?>
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
                                            value="<?= (int) $table['table_number'] ?>"
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
                                            value="<?= htmlspecialchars(
                                                    $table['name'] ?? '',
                                                    ENT_QUOTES,
                                                    'UTF-8'
                                            ) ?>"
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
                                            value="<?= (int) $table['capacity'] ?>"
                                    >

                                </div>


                                <div class="branch-table-field">

                                    <label>
                                        Ubicación
                                    </label>

                                    <input
                                            type="text"
                                            maxlength="100"
                                            class="branch-table-input table-location"
                                            value="<?= htmlspecialchars(
                                                    $table['location'] ?? '',
                                                    ENT_QUOTES,
                                                    'UTF-8'
                                            ) ?>"
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

                                        <option
                                                value="available"
                                                <?= ($table['status'] ?? '') === 'available'
                                                        ? 'selected'
                                                        : ''
                                                ?>
                                        >
                                            Disponible
                                        </option>

                                        <option
                                                value="maintenance"
                                                <?= ($table['status'] ?? '') === 'maintenance'
                                                        ? 'selected'
                                                        : ''
                                                ?>
                                        >
                                            Mantenimiento
                                        </option>

                                    </select>

                                </div>


                                <div class="branch-table-active">

                                    <label>

                                        <input
                                                type="checkbox"
                                                class="table-active"
                                                <?= $tableActive
                                                        ? 'checked'
                                                        : ''
                                                ?>
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
                                            title="Eliminar mesa"
                                    >
                                        <i class="fa-solid fa-trash"></i>
                                    </button>

                                </div>

                            </div>

                        <?php endforeach; ?>

                    <?php endif; ?>

                </div>

            </section>

        <?php endif; ?>


        <!-- ACCIONES -->

        <div class="branch-form-actions">

            <a
                href="<?= APP_URL ?>/admin/branches"
                class="branch-form-cancel"
            >
                Cancelar
            </a>

            <button
                type="submit"
                class="branch-form-submit"
                id="btnSaveBranch"
            >

                <i class="fa-solid fa-check"></i>

                <?= $isEdit
                    ? 'Guardar cambios'
                    : 'Crear sede'
                ?>

            </button>

        </div>

    </form>

</div>


<script>

    window.BRANCH_FORM = {

        mode:
                <?= json_encode($mode) ?>,

        branchId:
                <?= $branch
                        ? (int) $branch['id']
                        : 'null'
                        ?>,

        tables:
                <?= json_encode(
                        $tables ?? [],
                        JSON_UNESCAPED_UNICODE |
                        JSON_UNESCAPED_SLASHES
                ) ?>

    };

</script>