<?php
$cartEscape = static fn ($value): string => htmlspecialchars((string) ($value ?? ''), ENT_QUOTES, 'UTF-8');
?>

<main class="store-checkout-shell velox-cart-page">
    <div class="container py-5">
        <nav class="velox-cart-breadcrumb" aria-label="Ruta de navegación">
            <a href="<?= htmlspecialchars(APP_URL, ENT_QUOTES, 'UTF-8') ?>">Inicio</a>
            <i class="fa-solid fa-chevron-right"></i>
            <span>Carrito de compras</span>
        </nav>

        <header class="velox-cart-header">
            <div>
                <span class="store-eyebrow">TU PEDIDO, A TU MANERA</span>
                <h1>Tu carrito</h1>
                <p>Revisa tu selección, ajusta las cantidades y continúa cuando estés listo.</p>
            </div>

            <div class="velox-cart-progress" aria-label="Progreso de compra">
                <span class="is-current"><b>01</b> Carrito</span>
                <i class="fa-solid fa-chevron-right"></i>
                <span><b>02</b> Datos y sede</span>
                <i class="fa-solid fa-chevron-right"></i>
                <span><b>03</b> Pago seguro</span>
            </div>
        </header>

        <div class="row g-4 align-items-start">
            <div class="col-lg-8">
                <section class="velox-cart-items-panel">
                    <div class="velox-cart-panel-heading">
                        <div>
                            <span class="velox-cart-panel-kicker">RESUMEN DE TU SELECCIÓN</span>
                            <h2>Productos agregados</h2>
                        </div>
                        <span class="velox-cart-count" id="storeCartItemCount">0 productos</span>
                    </div>

                    <div id="storeCartItems" class="velox-cart-items">
                        <div class="velox-cart-loading">
                            <span class="spinner-border spinner-border-sm" role="status" aria-hidden="true"></span>
                            <span>Preparando tus productos...</span>
                        </div>
                    </div>

                    <div id="storeCartEmpty" class="velox-cart-empty d-none">
                        <span class="velox-cart-empty-icon"><i class="fa-solid fa-bag-shopping"></i></span>
                        <span class="velox-cart-panel-kicker">AÚN NO HAS ELEGIDO</span>
                        <h3>Tu carrito está esperando algo delicioso.</h3>
                        <p>Explora el catálogo, descubre tus favoritos y agrégalos aquí para armar tu pedido.</p>
                        <a href="<?= htmlspecialchars(APP_URL . '/tienda', ENT_QUOTES, 'UTF-8') ?>" class="velox-cart-primary-button">
                            Explorar productos <i class="fa-solid fa-arrow-right"></i>
                        </a>
                    </div>
                </section>

                <a class="velox-cart-back-to-store" href="<?= htmlspecialchars(APP_URL . '/tienda', ENT_QUOTES, 'UTF-8') ?>">
                    <i class="fa-solid fa-arrow-left"></i>
                    Seguir explorando productos
                </a>

                <div class="velox-cart-assurances">
                    <div>
                        <span><i class="fa-solid fa-shield-halved"></i></span>
                        <div><strong>Compra protegida</strong><small>Pago procesado de forma segura.</small></div>
                    </div>
                    <div>
                        <span><i class="fa-solid fa-location-dot"></i></span>
                        <div><strong>Recoge en tu sede</strong><small>Elige el punto de recogida al finalizar.</small></div>
                    </div>
                    <div>
                        <span><i class="fa-solid fa-receipt"></i></span>
                        <div><strong>Pedido confirmado</strong><small>Recibirás el estado de tu compra.</small></div>
                    </div>
                </div>
            </div>

            <div class="col-lg-4">
                <aside class="velox-cart-summary">
                    <div class="velox-cart-summary-heading">
                        <span class="velox-cart-summary-icon"><i class="fa-solid fa-receipt"></i></span>
                        <div>
                            <span>ESTÁS A UN PASO</span>
                            <h2>Resumen del pedido</h2>
                        </div>
                    </div>

                    <div class="velox-cart-summary-count">
                        <span>Artículos seleccionados</span>
                        <strong id="storeCartSummaryCount">0</strong>
                    </div>
                    <div class="velox-cart-summary-row">
                        <span>Subtotal estimado</span>
                        <strong id="storeCartSubtotal">$0</strong>
                    </div>

                    <div class="velox-cart-summary-total">
                        <span>Total estimado</span>
                        <strong id="storeCartTotal">$0</strong>
                    </div>

                    <p class="velox-cart-summary-note">
                        El total definitivo se valida al continuar, con los precios, impuestos y descuentos vigentes.
                    </p>

                    <a href="<?= htmlspecialchars(APP_URL . '/checkout', ENT_QUOTES, 'UTF-8') ?>" class="velox-cart-primary-button w-100" id="storeCartCheckout" aria-disabled="true">
                        Continuar compra <i class="fa-solid fa-arrow-right"></i>
                    </a>

                    <div class="velox-cart-payment-note">
                        <i class="fa-solid fa-lock"></i>
                        <span>Conexión segura para el proceso de pago.</span>
                    </div>

                    <div class="velox-cart-summary-divider"></div>
                    <a href="<?= htmlspecialchars(APP_URL . '/tienda', ENT_QUOTES, 'UTF-8') ?>" class="velox-cart-summary-link">
                        <i class="fa-solid fa-plus"></i> Agregar más productos
                    </a>
                </aside>
            </div>
        </div>
    </div>
</main>
