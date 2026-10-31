<div class="bt-error-page is-dark">

    <div class="bt-error-card">

        <div class="bt-error-icon">

            <i class="fa-solid fa-magnifying-glass"></i>

        </div>


        <div class="bt-error-code">

            404

        </div>


        <h1 class="bt-error-title">

            Página no encontrada

        </h1>


        <p class="bt-error-description">

            La página que estás buscando no existe,
            fue movida o la dirección ingresada ya
            no está disponible.

        </p>


        <div class="bt-error-meta">

            <i class="fa-solid fa-circle-question"></i>

            <span>

                Revisa la dirección e inténtalo nuevamente.

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

                    <i class="fa-solid fa-table-columns"></i>

                    Ir al Dashboard

                </a>

            <?php endif; ?>


            <a
                href="<?= APP_URL ?>"
                class="bt-error-btn bt-error-btn-secondary"
            >

                <i class="fa-solid fa-house"></i>

                Página principal

            </a>

        </div>


        <div class="bt-error-help">

            Código de error: HTTP 404

        </div>

    </div>

</div>