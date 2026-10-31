<!-- app/Views/users/profile.php -->


<?php
$fullName = trim(($user['first_name'] ?? '') . ' ' . ($user['last_name'] ?? ''));
if (empty($fullName)) {
    $fullName = 'Usuario';
}
?>

<div class="container-fluid p-0">
    <!-- Banner de Encabezado -->
    <div class="position-relative rounded-4 shadow-sm overflow-hidden mb-4"
         style="background: linear-gradient(135deg, #0f172a 0%, #1e293b 50%, #0284c7 100%); height: 250px;">
        <div class="position-absolute top-0 end-0 p-4 opacity-10">
            <i class="bi bi-shield-check text-white" style="font-size: 10rem;"></i>
        </div>
    </div>

    <div class="row justify-content-center px-3" style="margin-top: -150px;">
        <div class="col-xl-11 col-xxl-10">
            <div class="card border-0 shadow-lg rounded-4 overflow-hidden bg-white mb-5">
                <div class="card-body p-4 p-lg-5">

                    <!-- Encabezado de Perfil -->
                    <div class="row align-items-end mb-4 text-center text-lg-start g-4">
                        <div class="col-lg-auto">
                            <div class="position-relative d-inline-block">
                                <img src="<?= APP_URL; ?>/assets/img/<?= htmlspecialchars($user['avatar'] ?? 'default.png'); ?>"
                                     class="rounded-circle img-thumbnail shadow-lg"
                                     style="width: 140px; height: 140px; object-fit: cover; border: 4px solid #fff;"
                                     alt="Avatar de <?= htmlspecialchars($fullName); ?>"
                                     onerror="this.onerror=null; this.src='<?= APP_URL; ?>/assets/img/default.png';">
                                <span class="position-absolute bottom-0 end-0 p-2 bg-success border border-3 border-white rounded-circle"
                                      title="En línea"></span>
                            </div>
                        </div>

                        <div class="col-lg ms-lg-2">
                            <!-- Nombre -->
                            <h2 class="fw-bolder mb-1 text-dark tracking-tight"><?= htmlspecialchars($fullName); ?></h2>

                            <!-- Correo -->
                            <p class="text-secondary mb-2 fs-6 d-flex align-items-center justify-content-center justify-content-lg-start gap-2">
                                <i class="bi bi-envelope-at text-primary"></i> <?= htmlspecialchars($user['email'] ?? ''); ?>
                            </p>

                            <!-- Estado (Debajo del correo) -->
                            <div class="d-flex align-items-center justify-content-center justify-content-lg-start">
                                <?php if (!empty($user['is_active'])): ?>
                                    <span class="badge bg-success-subtle text-success border border-success-subtle px-3 py-1 rounded-pill small fw-bold">
                                        <i class="bi bi-patch-check-fill me-1"></i> Cuenta Activa
                                    </span>
                                <?php else: ?>
                                    <span class="badge bg-danger-subtle text-danger border border-danger-subtle px-3 py-1 rounded-pill small fw-bold">
                                        <i class="bi bi-x-circle-fill me-1"></i> Inactiva
                                    </span>
                                <?php endif; ?>
                            </div>
                        </div>

                        <div class="col-lg-auto">
                            <div class="p-3 rounded-4 bg-light border text-center text-lg-end">
                                <small class="d-block text-uppercase fw-bold text-muted mb-1" style="font-size: 0.65rem; letter-spacing: 1px;">
                                    Rol Asignado
                                </small>
                                <span class="fw-extrabold text-primary h6 mb-0 d-inline-flex align-items-center gap-2">
                                    <i class="bi bi-shield-lock-fill"></i>
                                    <?= htmlspecialchars($user['nombre_rol'] ?? $user['role_name'] ?? 'Sin Rol'); ?>
                                </span>
                            </div>
                        </div>
                    </div>

                    <hr class="my-4 text-secondary opacity-25">

                    <!-- Secciones de Detalles y Permisos -->
                    <div class="row g-4">
                        <!-- Identidad -->
                        <div class="col-lg-7">
                            <div class="p-4 rounded-4 bg-body-tertiary border h-100">
                                <h6 class="fw-bold mb-4 d-flex align-items-center gap-2 text-dark">
                                    <i class="bi bi-person-vcard text-primary fs-5"></i> Detalles de Identidad
                                </h6>

                                <div class="row g-3">
                                    <div class="col-md-6">
                                        <div class="p-3 rounded-3 bg-white border border-light-subtle shadow-sm">
                                            <small class="text-muted fw-bold text-uppercase d-block mb-1" style="font-size: 0.7rem;">Nombre Completo</small>
                                            <span class="text-dark fw-bold"><?= htmlspecialchars($fullName); ?></span>
                                        </div>
                                    </div>
                                    <div class="col-md-6">
                                        <div class="p-3 rounded-3 bg-white border border-light-subtle shadow-sm">
                                            <small class="text-muted fw-bold text-uppercase d-block mb-1" style="font-size: 0.7rem;">Correo Electrónico</small>
                                            <span class="text-dark fw-bold text-break"><?= htmlspecialchars($user['email'] ?? ''); ?></span>
                                        </div>
                                    </div>
                                    <div class="col-md-6">
                                        <div class="p-3 rounded-3 bg-white border border-light-subtle shadow-sm">
                                            <small class="text-muted fw-bold text-uppercase d-block mb-1" style="font-size: 0.7rem;">Documento</small>
                                            <span class="text-dark fw-bold">
                                                <?= htmlspecialchars(($user['document_type'] ?? 'CC') . ': ' . ($user['document_number'] ?? 'N/A')); ?>
                                            </span>
                                        </div>
                                    </div>
                                    <div class="col-md-6">
                                        <div class="p-3 rounded-3 bg-white border border-light-subtle shadow-sm">
                                            <small class="text-muted fw-bold text-uppercase d-block mb-1" style="font-size: 0.7rem;">ID Usuario</small>
                                            <span class="text-dark fw-bold">#MP-<?= str_pad($user['id'] ?? 0, 4, "0", STR_PAD_LEFT); ?></span>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Permisos y Módulos -->
                        <div class="col-lg-5">
                            <div class="p-4 rounded-4 bg-body-tertiary border h-100 d-flex flex-column justify-content-between">
                                <div>
                                    <h6 class="fw-bold mb-3 d-flex align-items-center gap-2 text-dark">
                                        <i class="bi bi-shield-check text-primary fs-5"></i> Permisos Habilitados
                                    </h6>
                                    <p class="small text-muted mb-3">Módulos activos para tu perfil:</p>

                                    <div class="d-flex flex-wrap gap-2 mb-4">
                                        <?php if (!empty($misAccesos)): ?>
                                            <?php foreach ($misAccesos as $modulo): ?>
                                                <div class="access-tag-turquesa">
                                                    <i class="bi bi-patch-check-fill"></i>
                                                    <?= htmlspecialchars(mb_strtoupper($modulo)); ?>
                                                </div>
                                            <?php endforeach; ?>
                                        <?php else: ?>
                                            <div class="access-tag-turquesa opacity-75">
                                                <i class="bi bi-info-circle"></i>
                                                ACCESO BÁSICO
                                            </div>
                                        <?php endif; ?>
                                    </div>
                                </div>

                                <div class="p-3 rounded-3 bg-white border shadow-sm d-flex align-items-center gap-3">
                                    <div class="bg-primary-subtle text-primary p-2 rounded-3 d-flex align-items-center justify-content-center" style="width: 42px; height: 42px;">
                                        <i class="bi bi-fingerprint fs-5"></i>
                                    </div>
                                    <div>
                                        <h6 class="mb-0 fw-bold small text-dark">Sesión Protegida</h6>
                                        <small class="text-muted" style="font-size: 0.75rem;">Conexión cifrada vía SSL / TLS.</small>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Botón -->
                    <div class="mt-4 pt-3 text-center">
                        <a href="<?= APP_URL; ?>/admin/dashboard" class="btn btn-primary btn-lg px-4 py-2 rounded-3 fw-bold shadow-sm d-inline-flex align-items-center gap-2">
                            <i class="bi bi-grid-fill"></i> Ir al Panel Control
                        </a>
                    </div>

                </div>
            </div>
        </div>
    </div>
</div>