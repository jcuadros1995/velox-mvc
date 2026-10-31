<?php

$pageHeaderIcon =
    $pageHeaderIcon
    ?? 'fa-solid fa-layer-group';

$pageHeaderEyebrow =
    $pageHeaderEyebrow
    ?? 'ADMINISTRACIÓN';

$pageHeaderTitle =
    $pageHeaderTitle
    ?? 'Velox MVC';

$pageHeaderDescription =
    $pageHeaderDescription
    ?? '';

$pageHeaderBadge =
    $pageHeaderBadge
    ?? 'Panel de gestión';

?>

<section class="admin-page-header">

    <!-- =========================================================
         DECORACIÓN
    ========================================================== -->

    <div class="admin-page-header-orb admin-page-header-orb-1"></div>

    <div class="admin-page-header-orb admin-page-header-orb-2"></div>

    <div class="admin-page-header-orb admin-page-header-orb-3"></div>


    <!-- =========================================================
         CONTENIDO
    ========================================================== -->

    <div class="admin-page-header-content">


        <!-- =====================================================
             ICONO
        ====================================================== -->

        <div class="admin-page-header-icon">

            <i
                class="<?= htmlspecialchars(
                    $pageHeaderIcon,
                    ENT_QUOTES,
                    'UTF-8'
                ) ?>"
            ></i>

        </div>


        <!-- =====================================================
             TEXTO
        ====================================================== -->

        <div class="admin-page-header-text">

            <div class="admin-page-header-eyebrow">

                <?= htmlspecialchars(
                    $pageHeaderEyebrow,
                    ENT_QUOTES,
                    'UTF-8'
                ) ?>

            </div>


            <h1 class="admin-page-header-title">

                <?= htmlspecialchars(
                    $pageHeaderTitle,
                    ENT_QUOTES,
                    'UTF-8'
                ) ?>

            </h1>


            <?php if ($pageHeaderDescription !== ''): ?>

                <p class="admin-page-header-description">

                    <?= htmlspecialchars(
                        $pageHeaderDescription,
                        ENT_QUOTES,
                        'UTF-8'
                    ) ?>

                </p>

            <?php endif; ?>

        </div>


        <!-- =====================================================
             BADGE
        ====================================================== -->

        <div class="admin-page-header-badge">

            <i class="fa-solid fa-globe"></i>

            <span>

                <?= htmlspecialchars(
                    $pageHeaderBadge,
                    ENT_QUOTES,
                    'UTF-8'
                ) ?>

            </span>

        </div>

    </div>

</section>