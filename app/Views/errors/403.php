<div class="bt-error-page">

    <div class="bt-error-card">

        <div class="bt-error-icon">
            <i class="fa-solid fa-lock"></i>
        </div>

        <div class="bt-error-code">
            403
        </div>

        <h1 class="bt-error-title">
            Acceso restringido
        </h1>

        <p class="bt-error-description">
            Esta sección requiere un nivel de autorización
            superior al de tu cuenta actual. Puedes continuar
            utilizando las funciones disponibles para tu rol.
        </p>

        <div class="bt-error-meta">

            <i class="fa-solid fa-shield-halved"></i>

            <span>
                Sesión:
                <strong>
                    <?php
                    $user = Auth::user() ?? [];

                    $roleName = strtolower(
                        trim(
                            (string) (
                                $user['role_name']
                                ?? $user['role']
                                ?? ''
                            )
                        )
                    );

                    $roleLabel = match ($roleName) {
                        'superadmin' => 'SuperAdmin',
                        'admin' => 'Administrador',
                        'staff' => 'Personal',
                        'customer' => 'Cliente',
                        default => 'Usuario autorizado',
                    };

                    echo htmlspecialchars(
                        $roleLabel,
                        ENT_QUOTES,
                        'UTF-8'
                    );
                    ?>
                </strong>
            </span>

        </div>

        <div class="bt-error-divider"></div>

        <div class="bt-error-actions">

            <a
                href="<?= APP_URL ?>/admin/dashboard"
                class="bt-error-btn bt-error-btn-primary"
            >
                <i class="fa-solid fa-table-columns"></i>
                Ir al Dashboard
            </a>

            <a
                href="<?= APP_URL ?>/admin/pages"
                class="bt-error-btn bt-error-btn-secondary"
            >
                <i class="fa-solid fa-arrow-left"></i>
                Volver a Páginas
            </a>

        </div>

        <div class="bt-error-help">
            Si necesitas acceso a esta función,
            contacta a un SuperAdmin.
        </div>

    </div>

</div>