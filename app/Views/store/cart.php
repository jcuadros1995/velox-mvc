<main class="store-checkout-shell">
    <div class="container py-5">
        <div class="store-page-heading">
            <span class="store-eyebrow">TU COMPRA</span>
            <h1>Mi carrito</h1>
            <p>Revisa los productos que elegiste antes de continuar.</p>
        </div>

        <div class="row g-4 align-items-start">
            <div class="col-lg-8">
                <section class="store-cart-panel">
                    <div class="store-cart-panel-heading">
                        <h2>Productos seleccionados</h2>
                        <span id="storeCartItemCount" class="store-cart-muted">0 productos</span>
                    </div>
                    <div id="storeCartItems" class="store-cart-items">
                        <div class="store-cart-loading">Cargando carrito...</div>
                    </div>
                    <div id="storeCartEmpty" class="store-cart-empty d-none">
                        <span class="store-empty-icon"><i class="fa-solid fa-basket-shopping"></i></span>
                        <h3>Tu carrito está vacío</h3>
                        <p>Explora el menú y agrega tus favoritos.</p>
                        <a class="btn btn-velox-primary" href="<?= htmlspecialchars(APP_URL . '/menu', ENT_QUOTES, 'UTF-8') ?>">Ver menú <i class="fa-solid fa-arrow-right ms-2"></i></a>
                    </div>
                </section>
            </div>

            <div class="col-lg-4">
                <aside class="store-cart-summary">
                    <h2>Resumen de compra</h2>
                    <div class="store-summary-row"><span>Productos</span><strong id="storeCartSubtotal">$0</strong></div>
                    <div class="store-summary-row store-summary-total"><span>Subtotal estimado</span><strong id="storeCartTotal">$0</strong></div>
                    <p class="store-summary-note">El valor final, incluidos los impuestos aplicables y descuentos, se confirma en el checkout.</p>
                    <a href="<?= htmlspecialchars(APP_URL . '/checkout', ENT_QUOTES, 'UTF-8') ?>" class="btn btn-velox-primary w-100" id="storeCartCheckout">Continuar al checkout <i class="fa-solid fa-arrow-right ms-2"></i></a>
                    <a href="<?= htmlspecialchars(APP_URL . '/menu', ENT_QUOTES, 'UTF-8') ?>" class="store-continue-shopping"><i class="fa-solid fa-arrow-left me-2"></i>Seguir comprando</a>
                </aside>
            </div>
        </div>
    </div>
</main>
