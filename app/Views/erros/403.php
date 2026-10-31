<?php
http_response_code(403);
?>

<div class="container-fluid py-5">
    <div class="row justify-content-center">
        <div class="col-lg-7 col-xl-6">
            <div class="card border-0 shadow-sm text-center p-5">

                <div
                    class="mx-auto mb-4 d-flex align-items-center justify-content-center rounded-circle"
                    style="
                        width:80px;
                        height:80px;
                        background:rgba(220,53,69,.10);
                        color:#dc3545;
                        font-size:32px;
                    "
                >
                    <i class="fa-solid fa-lock"></i>
                </div>

                <h1 class="fw-bold mb-2">
                    Acceso denegado
                </h1>

                <p class="text-muted mb-4">
                    No tienes permisos suficientes para acceder
                    a esta sección.
                </p>

                <div class="d-flex justify-content-center gap-2 flex-wrap">
                    <a
                        href="<?= BASE_URL ?>/admin/dashboard"
                        class="btn btn-primary"
                    >
                        <i class="fa-solid fa-house me-2"></i>
                        Ir al panel
                    </a>

                    <button
                        type="button"
                        class="btn btn-light"
                        onclick="history.back()"
                    >
                        <i class="fa-solid fa-arrow-left me-2"></i>
                        Volver
                    </button>
                </div>

            </div>
        </div>
    </div>
</div>