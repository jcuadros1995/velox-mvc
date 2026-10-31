<div class="bt-error-page is-warning">

    <div class="bt-error-card">

        <div class="bt-error-icon">

            <i class="fa-solid fa-triangle-exclamation"></i>

        </div>


        <div class="bt-error-code">

            500

        </div>


        <h1 class="bt-error-title">

            Algo salió mal

        </h1>


        <p class="bt-error-description">

            Burger Town encontró un problema inesperado al
            procesar tu solicitud. Puedes intentarlo
            nuevamente o volver al panel principal.

        </p>


        <div class="bt-error-meta">

            <i class="fa-solid fa-server"></i>

            <span>

                Error interno del servidor

            </span>

        </div>


        <div class="bt-error-divider"></div>


        <?php

        $isAdminError =
            !empty($isAdminError);

        ?>


        <div class="bt-error-actions">

            <?php if ($isAdminError): ?>

                <a
                    href="<?= APP_URL ?>/admin/dashboard"
                    class="bt-error-btn bt-error-btn-primary"
                >

                    <i class="fa-solid fa-rotate-right"></i>

                    Volver al Dashboard

                </a>

            <?php else: ?>

                <a
                    href="<?= APP_URL ?>"
                    class="bt-error-btn bt-error-btn-primary"
                >

                    <i class="fa-solid fa-house"></i>

                    Página principal

                </a>

            <?php endif; ?>


            <?php if ($isAdminError): ?>

                <a
                    href="<?= APP_URL ?>"
                    class="bt-error-btn bt-error-btn-secondary"
                >

                    <i class="fa-solid fa-house"></i>

                    Página principal

                </a>

            <?php endif; ?>

        </div>


        <div class="bt-error-help">

            Si el problema continúa, contacta al
            administrador del sistema.

        </div>

    </div>

</div>