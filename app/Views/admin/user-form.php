<?php
// app/Views/admin/user-form.php

$isEdit =
        ($userFormMode ?? 'create') === 'edit';


$user =
        $userFormUser ?? null;


$isSuperAdmin =
        (bool) (
                $userFormIsSuperAdmin ?? false
        );


$roles =
        $userFormRoles ?? [];


$branches =
        $userFormBranches ?? [];


$currentBranchId =
        $userFormBranchId ?? null;


$fullName =
        $user
                ? trim(
                ($user['first_name'] ?? '') .
                ' ' .
                ($user['last_name'] ?? '')
        )
                : '';


$currentRoleId =
        $user['role_id'] ?? '';


$currentBranch =
        $user['branch_id'] ??
        $currentBranchId;


/*
 * =========================================================
 * FOTO
 * =========================================================
 */

$defaultPhoto =
        rtrim(APP_URL, '/') .
        '/assets/img/default.png';


$userAvatar =
        trim(
                (string) (
                        $user['avatar'] ?? ''
                )
        );


if (
        $userAvatar === '' ||
        $userAvatar === 'default.png'
) {

    $userPhoto =
            $defaultPhoto;

} else {

    $userPhoto =
            rtrim(APP_URL, '/') .
            '/assets/img/users/' .
            rawurlencode(
                    basename($userAvatar)
            );
}
?>


<div class="admin-user-form-page">


    <!-- =========================================================
         TOPBAR
    ========================================================= -->

    <div class="user-form-topbar">

        <div>

            <a
                    href="<?= APP_URL ?>/admin/users"
                    class="user-form-back"
            >
                <i class="fa-solid fa-arrow-left"></i>
                Usuarios
            </a>


            <div class="user-form-eyebrow">
                GESTIÓN DE ACCESOS
            </div>


            <h1>
                <?= $isEdit
                        ? 'Editar usuario'
                        : 'Nuevo usuario'
                ?>
            </h1>


            <p>
                <?= $isEdit
                        ? 'Actualiza la información, sede, rol y estado del usuario.'
                        : 'Crea un nuevo usuario para la plataforma.'
                ?>
            </p>

        </div>


        <?php if ($isEdit): ?>

            <div class="user-form-id">
                ID #<?= (int) $user['id'] ?>
            </div>

        <?php endif; ?>

    </div>


    <!-- =========================================================
         FOTO DE PERFIL
    ========================================================= -->

    <section class="user-form-card user-photo-card">

        <div class="user-photo-card-header">

            <div>

                <h3>
                    <i class="fa-solid fa-camera"></i>
                    Foto de perfil
                </h3>

                <p>
                    Sube una imagen para identificar fácilmente al usuario.
                </p>

            </div>

        </div>


        <div
                id="userPhotoDropzone"
                class="user-photo-dropzone"
        >

            <input
                    type="file"
                    id="userPhotoInput"
                    name="photo"
                    accept="image/jpeg,image/png,image/webp"
                    hidden
            >


            <div
                    id="userPhotoPreview"
                    class="user-photo-preview"
            >

                <img
                        id="userPhotoPreviewImage"
                        src="<?= htmlspecialchars(
                                $userPhoto,
                                ENT_QUOTES,
                                'UTF-8'
                        ) ?>"
                        alt="Foto de usuario"
                >


                <div class="user-photo-overlay">

                    <i class="fa-solid fa-camera"></i>

                    <span>
                        Cambiar foto
                    </span>

                </div>

            </div>


            <div class="user-photo-upload-content">

                <div class="user-photo-upload-icon">

                    <i class="fa-solid fa-cloud-arrow-up"></i>

                </div>


                <h4>
                    Arrastra y suelta la imagen aquí
                </h4>


                <p>
                    o haz clic para seleccionarla
                </p>


                <span class="user-photo-upload-info">
                    JPG, PNG o WEBP · Máximo 5 MB
                </span>

            </div>

        </div>


        <div class="user-photo-actions">

            <button
                    type="button"
                    id="btnChangeUserPhoto"
                    class="user-photo-btn"
            >

                <i class="fa-solid fa-image"></i>

                Seleccionar imagen

            </button>


            <button
                    type="button"
                    id="btnRemoveUserPhoto"
                    class="user-photo-btn user-photo-btn-danger"
            >

                <i class="fa-solid fa-trash"></i>

                Quitar

            </button>

        </div>

    </section>


    <!-- =========================================================
         FORMULARIO
    ========================================================= -->

    <form
            id="adminUserForm"
            class="admin-user-form"
    >

        <input
                type="hidden"
                id="userId"
                value="<?= $isEdit
                        ? (int) $user['id']
                        : ''
                ?>"
        >


        <input
                type="hidden"
                id="userFormMode"
                value="<?= $isEdit
                        ? 'edit'
                        : 'create'
                ?>"
        >


        <!-- =====================================================
             INFORMACIÓN PERSONAL
        ====================================================== -->

        <section class="user-form-card">

            <div class="user-form-card-header">

                <div class="user-form-card-icon">

                    <i class="fa-solid fa-user"></i>

                </div>


                <div>

                    <h2>
                        Información personal
                    </h2>

                    <p>
                        Datos básicos del usuario.
                    </p>

                </div>

            </div>


            <div class="user-form-grid">


                <!-- NOMBRE -->

                <div class="user-form-field">

                    <label for="userFirstName">

                        Nombre

                        <span>*</span>

                    </label>


                    <input
                            type="text"
                            id="userFirstName"
                            class="users-input"
                            value="<?= htmlspecialchars(
                                    $user['first_name'] ?? '',
                                    ENT_QUOTES,
                                    'UTF-8'
                            ) ?>"
                            required
                    >

                </div>


                <!-- APELLIDO -->

                <div class="user-form-field">

                    <label for="userLastName">

                        Apellido

                        <span>*</span>

                    </label>


                    <input
                            type="text"
                            id="userLastName"
                            class="users-input"
                            value="<?= htmlspecialchars(
                                    $user['last_name'] ?? '',
                                    ENT_QUOTES,
                                    'UTF-8'
                            ) ?>"
                            required
                    >

                </div>


                <!-- TIPO DOCUMENTO -->

                <div class="user-form-field">

                    <label for="userDocumentType">
                        Tipo de documento
                    </label>


                    <?php
                    $documentType =
                            $user['document_type'] ?? 'CC';
                    ?>


                    <select
                            id="userDocumentType"
                            class="users-input"
                    >

                        <option
                                value="CC"
                                <?= $documentType === 'CC'
                                        ? 'selected'
                                        : ''
                                ?>
                        >
                            Cédula de ciudadanía
                        </option>


                        <option
                                value="CE"
                                <?= $documentType === 'CE'
                                        ? 'selected'
                                        : ''
                                ?>
                        >
                            Cédula de extranjería
                        </option>


                        <option
                                value="NIT"
                                <?= $documentType === 'NIT'
                                        ? 'selected'
                                        : ''
                                ?>
                        >
                            NIT
                        </option>


                        <option
                                value="PP"
                                <?= $documentType === 'PP'
                                        ? 'selected'
                                        : ''
                                ?>
                        >
                            Pasaporte
                        </option>

                    </select>

                </div>


                <!-- DOCUMENTO -->

                <div class="user-form-field">

                    <label for="userDocumentNumber">
                        Número de documento
                    </label>


                    <input
                            type="text"
                            id="userDocumentNumber"
                            class="users-input"
                            value="<?= htmlspecialchars(
                                    $user['document_number'] ?? '',
                                    ENT_QUOTES,
                                    'UTF-8'
                            ) ?>"
                    >

                </div>

            </div>

        </section>


        <!-- =====================================================
             INFORMACIÓN DE CONTACTO
        ====================================================== -->

        <section class="user-form-card">

            <div class="user-form-card-header">

                <div class="user-form-card-icon">

                    <i class="fa-solid fa-address-book"></i>

                </div>


                <div>

                    <h2>
                        Información de contacto
                    </h2>

                    <p>
                        Datos utilizados para comunicación y acceso.
                    </p>

                </div>

            </div>


            <div class="user-form-grid">


                <!-- EMAIL -->

                <div class="user-form-field">

                    <label for="userEmail">

                        Correo electrónico

                        <span>*</span>

                    </label>


                    <input
                            type="email"
                            id="userEmail"
                            class="users-input"
                            value="<?= htmlspecialchars(
                                    $user['email'] ?? '',
                                    ENT_QUOTES,
                                    'UTF-8'
                            ) ?>"
                            required
                    >

                </div>


                <!-- TELÉFONO -->

                <div class="user-form-field">

                    <label for="userPhone">
                        Teléfono
                    </label>


                    <input
                            type="text"
                            id="userPhone"
                            class="users-input"
                            value="<?= htmlspecialchars(
                                    $user['phone'] ?? '',
                                    ENT_QUOTES,
                                    'UTF-8'
                            ) ?>"
                    >

                </div>

            </div>

        </section>


        <!-- =====================================================
             ACCESO Y SEGURIDAD
        ====================================================== -->

        <section class="user-form-card">

            <div class="user-form-card-header">

                <div class="user-form-card-icon">

                    <i class="fa-solid fa-shield-halved"></i>

                </div>


                <div>

                    <h2>
                        Acceso y seguridad
                    </h2>

                    <p>
                        Define la sede, rol, contraseña y estado.
                    </p>

                </div>

            </div>


            <div class="user-form-grid">


                <!-- =================================================
                     ROL
                ================================================= -->

                <div class="user-form-field">

                    <label for="userRole">

                        Rol

                        <span>*</span>

                    </label>


                    <select
                            id="userRole"
                            class="users-input"
                            required
                    >

                        <option value="">
                            Seleccionar rol...
                        </option>


                        <?php foreach ($roles as $role): ?>

                            <?php

                            $roleName = strtolower(
                                    trim(
                                            $role['name'] ?? ''
                                    )
                            );


                            /*
                             * Admin de sede no puede
                             * asignar SuperAdmin.
                             */

                            if (
                                    !$isSuperAdmin &&
                                    $roleName === 'superadmin'
                            ) {
                                continue;
                            }

                            ?>


                            <option
                                    value="<?= (int) $role['id'] ?>"
                                    <?= (int) $currentRoleId ===
                                    (int) $role['id']
                                            ? 'selected'
                                            : ''
                                    ?>
                            >

                                <?= htmlspecialchars(
                                        $role['name'],
                                        ENT_QUOTES,
                                        'UTF-8'
                                ) ?>

                            </option>

                        <?php endforeach; ?>

                    </select>

                </div>


                <!-- =================================================
                     SEDE
                ================================================= -->

                <div class="user-form-field">

                    <label for="userBranch">
                        Sede
                    </label>

                    <div id="userBranchContainer">

                        <select
                                id="userBranch"
                                class="users-input"
                        >

                            <option value="">
                                Seleccionar sede...
                            </option>

                            <?php foreach ($branches as $branch): ?>

                                <option
                                        value="<?= (int) $branch['id'] ?>"
                                        <?= (
                                                $currentBranch !== null &&
                                                (int) $currentBranch ===
                                                (int) $branch['id']
                                        )
                                                ? 'selected'
                                                : ''
                                        ?>
                                >

                                    <?= htmlspecialchars(
                                            $branch['name'],
                                            ENT_QUOTES,
                                            'UTF-8'
                                    ) ?>

                                </option>

                            <?php endforeach; ?>

                        </select>

                    </div>

                    <div
                            id="userGlobalBranch"
                            class="user-form-global-branch"
                            style="display: none;"
                    >

                        <div class="user-form-global-branch-icon">

                            <i class="fa-solid fa-globe"></i>

                        </div>

                        <div>

                            <strong>
                                Acceso global
                            </strong>

                            <span>
                El SuperAdmin administra todas las sedes
                y no pertenece a ninguna sede.
            </span>

                        </div>

                    </div>

                </div>


                <!-- =================================================
                     CONTRASEÑA
                ================================================= -->

                <div class="user-form-field user-form-full">

                    <label
                            for="password"
                            class="form-label"
                    >

                        <?= $isEdit
                                ? 'Nueva contraseña'
                                : 'Contraseña'
                        ?>


                        <?php if (!$isEdit): ?>

                            <span class="text-danger">
                                *
                            </span>

                        <?php endif; ?>

                    </label>


                    <div class="user-password-input">

                        <input
                                type="password"
                                id="password"
                                name="password"
                                class="form-control"
                                autocomplete="new-password"
                                placeholder="<?= $isEdit
                                        ? 'Déjala vacía para conservar la contraseña actual'
                                        : 'Ingresa una contraseña segura'
                                ?>"
                                <?= !$isEdit
                                        ? 'required'
                                        : ''
                                ?>
                        >


                        <button
                                type="button"
                                id="togglePassword"
                                class="password-toggle"
                                aria-label="Mostrar contraseña"
                                title="Mostrar contraseña"
                        >

                            <i class="fa-solid fa-eye"></i>

                        </button>

                    </div>


                    <!-- =================================================
                         SEGURIDAD DE CONTRASEÑA
                    ================================================= -->

                    <div class="password-strength">

                        <div class="password-strength-header">

                            <span>
                                Seguridad de la contraseña
                            </span>


                            <strong id="passwordStrengthText">
                                Muy débil
                            </strong>

                        </div>


                        <div class="password-progress">

                            <div
                                    id="passwordStrengthBar"
                                    class="password-progress-bar"
                                    style="width: 0%;"
                            ></div>

                        </div>

                    </div>


                    <!-- =================================================
                         REQUISITOS
                    ================================================= -->

                    <div class="password-requirements">


                        <!-- LONGITUD -->

                        <div
                                id="requirementLength"
                                class="password-requirement"
                        >

                            <span class="password-requirement-icon">

                                <i class="fa-solid fa-circle"></i>

                            </span>


                            <span>
                                Mínimo 8 caracteres
                            </span>

                        </div>


                        <!-- MAYÚSCULA -->

                        <div
                                id="requirementUpper"
                                class="password-requirement"
                        >

                            <span class="password-requirement-icon">

                                <i class="fa-solid fa-circle"></i>

                            </span>


                            <span>
                                Al menos una letra mayúscula
                            </span>

                        </div>


                        <!-- MINÚSCULA -->

                        <div
                                id="requirementLower"
                                class="password-requirement"
                        >

                            <span class="password-requirement-icon">

                                <i class="fa-solid fa-circle"></i>

                            </span>


                            <span>
                                Al menos una letra minúscula
                            </span>

                        </div>


                        <!-- NÚMERO -->

                        <div
                                id="requirementNumber"
                                class="password-requirement"
                        >

                            <span class="password-requirement-icon">

                                <i class="fa-solid fa-circle"></i>

                            </span>


                            <span>
                                Al menos un número
                            </span>

                        </div>


                        <!-- ESPECIAL -->

                        <div
                                id="requirementSpecial"
                                class="password-requirement"
                        >

                            <span class="password-requirement-icon">

                                <i class="fa-solid fa-circle"></i>

                            </span>


                            <span>
                                Al menos un carácter especial
                            </span>

                        </div>

                    </div>

                </div>


                <!-- =================================================
                     ESTADO
                ================================================= -->

                <div class="user-form-field user-form-full">

                    <label class="user-form-active">

                        <input
                                type="checkbox"
                                id="userActive"
                                <?= !$isEdit ||
                                (int) ($user['is_active'] ?? 0) === 1
                                        ? 'checked'
                                        : ''
                                ?>
                        >


                        <span>

                            <strong>
                                Usuario activo
                            </strong>


                            <small>
                                Permite que el usuario pueda acceder al sistema.
                            </small>

                        </span>

                    </label>

                </div>

            </div>

        </section>


        <!-- =====================================================
             PERMISOS Y ACCESO
             SECCIÓN COMPLETAMENTE INDEPENDIENTE
        ====================================================== -->

        <section class="user-form-card user-permissions-card">

            <div class="user-permissions-header">

                <div>

                    <div class="user-permissions-title">

                        <div class="user-form-card-icon">

                            <i class="fa-solid fa-shield-halved"></i>

                        </div>


                        <div>

                            <h2>
                                Permisos y acceso
                            </h2>

                            <p>
                                Selecciona las secciones y acciones a las que
                                este usuario podrá acceder.
                            </p>

                        </div>

                    </div>

                </div>


                <!-- CONTADOR -->

                <div class="permissions-summary">

                    <strong id="selectedPermissionsCount">
                        0
                    </strong>


                    <span>
                        permisos seleccionados
                    </span>

                </div>

            </div>


            <!-- =================================================
                 CONTENEDOR DE PERMISOS
            ================================================= -->

            <div
                    id="userPermissionsContainer"
                    class="user-permissions-container"
            >

                <div class="permissions-loading">

                    <i class="fa-solid fa-spinner fa-spin"></i>

                    Cargando permisos...

                </div>

            </div>

        </section>


        <!-- =====================================================
             FOOTER
        ====================================================== -->

        <div class="user-form-actions">

            <a
                    href="<?= APP_URL ?>/admin/users"
                    class="users-secondary-btn"
            >

                <i class="fa-solid fa-arrow-left"></i>

                Cancelar

            </a>


            <button
                    type="submit"
                    class="users-primary-btn"
                    id="btnSaveUserPage"
            >

                <i class="fa-solid fa-check"></i>


                <?= $isEdit
                        ? 'Guardar cambios'
                        : 'Crear usuario'
                ?>

            </button>

        </div>

    </form>

</div>

<script>

    window.USER_FORM = {

        mode:
                <?= json_encode(
                        $isEdit
                                ? 'edit'
                                : 'create'
                ) ?>,


        userId:
                <?= $isEdit
                        ? (int) $user['id']
                        : 'null'
                        ?>,


        roleId:
                <?= $currentRoleId !== ''
                        ? (int) $currentRoleId
                        : 'null'
                        ?>,


        branchId:
                <?= $currentBranch !== null
                        ? (int) $currentBranch
                        : 'null'
                        ?>,


        isSuperAdmin:
                <?= $isSuperAdmin
                        ? 'true'
                        : 'false'
                        ?>,


        defaultPhoto:
                <?= json_encode(
                        $defaultPhoto,
                        JSON_UNESCAPED_SLASHES |
                        JSON_UNESCAPED_UNICODE
                ) ?>,


        currentAvatar:
                <?= json_encode(
                        $userAvatar,
                        JSON_UNESCAPED_SLASHES |
                        JSON_UNESCAPED_UNICODE
                ) ?>,


        roles:
                <?= json_encode(
                        $roles,
                        JSON_UNESCAPED_UNICODE |
                        JSON_UNESCAPED_SLASHES
                ) ?>

    };

</script>