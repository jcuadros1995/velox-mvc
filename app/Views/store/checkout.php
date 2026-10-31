<?php
$storeDeliveryFeeSetting = getenv('STORE_DELIVERY_FEE');
$storeDeliveryFee = ($storeDeliveryFeeSetting === false || trim((string) $storeDeliveryFeeSetting) === '')
    ? 0.0
    : max(0, round((float) $storeDeliveryFeeSetting, 0));
$storeFormatDeliveryFee = '
    <div class="container py-5">
        <div class="store-page-heading">
            <span class="store-eyebrow">ÚLTIMO PASO</span>
            <h1>Finalizar compra</h1>
            <p>Elige cómo recibir tu pedido, completa los datos necesarios y continúa al pago seguro.</p>
        </div>

        <div class="store-checkout-steps">
            <span class="store-step is-done"><i class="fa-solid fa-check"></i> Carrito</span>
            <span class="store-step is-current"><i class="fa-solid fa-2"></i> Checkout</span>
            <span class="store-step"><i class="fa-solid fa-3"></i> Pago</span>
        </div>

        <div class="row g-4 align-items-start">
            <div class="col-lg-7">
                <section class="store-checkout-panel">
                    <div class="store-panel-section">
                        <h2><span>01</span> Datos del cliente</h2>
                        <?php if (!empty($storeIsAuthenticated) && !empty($storeCustomer)): ?>
                            <div class="store-customer-summary">
                                <div><small>Nombre</small><strong><?= htmlspecialchars(trim((string)($storeCustomer['first_name'] ?? '') . ' ' . (string)($storeCustomer['last_name'] ?? '')), ENT_QUOTES, 'UTF-8') ?></strong></div>
                                <div><small>Correo electrónico</small><strong><?= htmlspecialchars((string)($storeCustomer['email'] ?? ''), ENT_QUOTES, 'UTF-8') ?></strong></div>
                                <?php if (!empty($storeCustomer['phone'])): ?>
                                    <div><small>Teléfono</small><strong><?= htmlspecialchars((string)$storeCustomer['phone'], ENT_QUOTES, 'UTF-8') ?></strong></div>
                                <?php endif; ?>
                            </div>
                        <?php else: ?>
                            <div class="store-checkout-access">
                                <span class="store-checkout-access-icon"><i class="fa-solid fa-user-lock"></i></span>
                                <div class="store-checkout-access-copy">
                                    <strong>Continúa con tu cuenta de cliente</strong>
                                    <p>Ya puedes revisar tu pedido y elegir cómo recibirlo. Para confirmar la orden y realizar el pago seguro, inicia sesión o crea tu cuenta. Tus productos permanecerán guardados en el carrito.</p>
                                    <div class="store-checkout-access-actions">
                                        <a class="btn btn-velox-primary" href="<?= htmlspecialchars(APP_URL . '/login?redirect=%2Fcheckout', ENT_QUOTES, 'UTF-8') ?>">
                                            Iniciar sesión <i class="fa-solid fa-arrow-right ms-2"></i>
                                        </a>
                                        <a class="store-checkout-register-link" href="<?= htmlspecialchars(APP_URL . '/registro?redirect=%2Fcheckout', ENT_QUOTES, 'UTF-8') ?>">
                                            Crear cuenta
                                        </a>
                                    </div>
                                </div>
                            </div>
                        <?php endif; ?>
                    </div>

                    <div class="store-panel-section">
                        <h2><span>02</span> ¿Cómo deseas recibir tu pedido?</h2>

                        <div class="store-fulfillment-options" role="radiogroup" aria-label="Modalidad de entrega">
                            <label class="store-fulfillment-option is-selected" for="checkoutFulfillmentPickup">
                                <input type="radio" name="checkoutFulfillmentType" id="checkoutFulfillmentPickup" value="pickup" checked>
                                <span class="store-fulfillment-option-icon"><i class="fa-solid fa-store"></i></span>
                                <span class="store-fulfillment-option-copy">
                                    <strong>Recoger en tienda</strong>
                                    <small>Elige la sede donde pasarás por tu pedido.</small>
                                </span>
                                <span class="store-fulfillment-option-check"><i class="fa-solid fa-circle-check"></i></span>
                            </label>

                            <label class="store-fulfillment-option" for="checkoutFulfillmentDelivery">
                                <input type="radio" name="checkoutFulfillmentType" id="checkoutFulfillmentDelivery" value="delivery">
                                <span class="store-fulfillment-option-icon"><i class="fa-solid fa-motorcycle"></i></span>
                                <span class="store-fulfillment-option-copy">
                                    <strong>Envío a domicilio</strong>
                                    <small>Recibe tu pedido en la dirección que indiques.</small>
                                </span>
                                <span class="store-fulfillment-option-check"><i class="fa-solid fa-circle-check"></i></span>
                            </label>
                        </div>

                        <div class="store-fulfillment-panel" id="checkoutPickupOptions">
                            <label class="form-label" for="checkoutBranch">Sede donde recogerás tu pedido *</label>
                            <select class="form-select store-checkout-select" id="checkoutBranch" required>
                                <option value="">Selecciona una sede</option>
                                <?php foreach (($storeBranches ?? []) as $branch): ?>
                                    <option value="<?= (int)$branch['id'] ?>">
                                        <?= htmlspecialchars((string)$branch['name'], ENT_QUOTES, 'UTF-8') ?>
                                        <?= !empty($branch['address']) ? ' — ' . htmlspecialchars((string)$branch['address'], ENT_QUOTES, 'UTF-8') : '' ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                            <?php if (empty($storeBranches)): ?>
                                <p class="store-inline-warning">Por el momento no hay sedes habilitadas para recogida.</p>
                            <?php endif; ?>
                        </div>

                        <div class="store-fulfillment-panel d-none" id="checkoutDeliveryOptions">
                            <div class="store-delivery-fields">
                                <div class="store-delivery-field store-delivery-field-wide">
                                    <label class="form-label" for="checkoutDeliveryAddress">Dirección de entrega *</label>
                                    <input
                                        type="text"
                                        class="form-control store-checkout-select"
                                        id="checkoutDeliveryAddress"
                                        maxlength="255"
                                        placeholder="Ej. Calle 80 # 15-20, apartamento 302"
                                        autocomplete="street-address"
                                    >
                                </div>

                                <div class="store-delivery-field">
                                    <label class="form-label" for="checkoutDeliveryNeighborhood">Barrio *</label>
                                    <input
                                        type="text"
                                        class="form-control store-checkout-select"
                                        id="checkoutDeliveryNeighborhood"
                                        maxlength="120"
                                        placeholder="Tu barrio"
                                        autocomplete="address-level3"
                                    >
                                </div>

                                <div class="store-delivery-field">
                                    <label class="form-label" for="checkoutDeliveryCity">Ciudad o municipio *</label>
                                    <input
                                        type="text"
                                        class="form-control store-checkout-select"
                                        id="checkoutDeliveryCity"
                                        maxlength="120"
                                        placeholder="Ciudad de entrega"
                                        autocomplete="address-level2"
                                    >
                                </div>

                                <div class="store-delivery-field">
                                    <label class="form-label" for="checkoutDeliveryPhone">Teléfono de contacto *</label>
                                    <input
                                        type="tel"
                                        class="form-control store-checkout-select"
                                        id="checkoutDeliveryPhone"
                                        maxlength="25"
                                        placeholder="Número para coordinar la entrega"
                                        autocomplete="tel"
                                        inputmode="tel"
                                    >
                                </div>

                                <div class="store-delivery-field">
                                    <label class="form-label" for="checkoutDeliveryReference">Indicaciones para llegar</label>
                                    <input
                                        type="text"
                                        class="form-control store-checkout-select"
                                        id="checkoutDeliveryReference"
                                        maxlength="255"
                                        placeholder="Conjunto, torre, portería o referencia"
                                    >
                                </div>
                            </div>

                            <div class="store-delivery-fee-note">
                                <span><i class="fa-solid fa-truck-fast"></i> Costo del domicilio</span>
                                <strong id="checkoutDeliveryFeePreview"><?= htmlspecialchars($storeFormatDeliveryFee, ENT_QUOTES, 'UTF-8') ?></strong>
                            </div>
                            <p class="store-delivery-help">El pedido se asignará a una sede activa con inventario suficiente. Verifica que la dirección esté dentro de la cobertura del comercio.</p>
                        </div>
                    </div>

                    <?php if (!empty($storeIsAuthenticated)): ?>
                    <div class="store-panel-section">
                        <h2><span>03</span> Cupón de descuento</h2>
                        <p class="store-section-description">¿Tienes un código promocional? Escríbelo aquí antes de pagar.</p>
                        <div class="store-coupon-control">
                            <input type="text" id="checkoutCouponCode" class="form-control" maxlength="40" placeholder="Código de cupón" autocomplete="off">
                            <button type="button" class="btn btn-outline-secondary" id="checkoutApplyCoupon">Aplicar</button>
                        </div>
                        <div id="checkoutCouponMessage" class="store-coupon-message" aria-live="polite"></div>
                    </div>
                    <?php endif; ?>

                    <div class="store-panel-section">
                        <h2><span>04</span> Indicaciones adicionales</h2>
                        <label class="form-label" for="checkoutNotes">Notas del pedido (opcional)</label>
                        <textarea class="form-control store-checkout-notes" id="checkoutNotes" maxlength="1000" rows="3" placeholder="Escribe aquí alguna indicación para preparar tu pedido."></textarea>
                    </div>
                </section>
            </div>

            <div class="col-lg-5">
                <aside class="store-cart-summary store-checkout-summary">
                    <h2>Tu pedido</h2>
                    <div id="checkoutOrderItems" class="store-checkout-order-items"></div>
                    <div class="store-summary-divider"></div>
                    <div class="store-summary-row"><span>Subtotal estimado</span><strong id="checkoutSubtotal">$0</strong></div>
                    <div class="store-summary-row d-none" id="checkoutDeliveryFeeRow"><span>Envío a domicilio</span><strong id="checkoutDeliveryFee">$0</strong></div>
                    <div class="store-summary-row store-discount-row d-none" id="checkoutDiscountRow"><span>Descuento</span><strong id="checkoutDiscount">−$0</strong></div>
                    <div class="store-summary-row store-summary-total"><span>Total estimado</span><strong id="checkoutTotal">$0</strong></div>
                    <p class="store-summary-note">El importe definitivo se calcula nuevamente en el servidor usando los precios e impuestos vigentes del catálogo.</p>
                    <button type="button" class="btn btn-velox-primary w-100" id="checkoutPayButton" disabled>
                        Continuar al pago seguro <i class="fa-solid fa-lock ms-2"></i>
                    </button>
                    <div class="store-secure-payment"><i class="fa-solid fa-shield-halved"></i><span>Pago procesado de forma segura por Wompi.</span></div>
                    <a href="<?= htmlspecialchars(APP_URL . '/carrito', ENT_QUOTES, 'UTF-8') ?>" class="store-continue-shopping"><i class="fa-solid fa-arrow-left me-2"></i>Volver al carrito</a>
                </aside>
            </div>
        </div>
    </div>
</main>
 . number_format($storeDeliveryFee, 0, ',', '.');
?>
<main
    class="store-checkout-shell"
    data-checkout-authenticated="<?= !empty($storeIsAuthenticated) ? '1' : '0' ?>"
    data-delivery-fee="<?= htmlspecialchars((string) $storeDeliveryFee, ENT_QUOTES, 'UTF-8') ?>"
>
    <div class="container py-5">
        <div class="store-page-heading">
            <span class="store-eyebrow">ÚLTIMO PASO</span>
            <h1>Finalizar compra</h1>
            <p>Confirma tu sede de recogida, aplica tu cupón y continúa al pago seguro.</p>
        </div>

        <div class="store-checkout-steps">
            <span class="store-step is-done"><i class="fa-solid fa-check"></i> Carrito</span>
            <span class="store-step is-current"><i class="fa-solid fa-2"></i> Checkout</span>
            <span class="store-step"><i class="fa-solid fa-3"></i> Pago</span>
        </div>

        <div class="row g-4 align-items-start">
            <div class="col-lg-7">
                <section class="store-checkout-panel">
                    <div class="store-panel-section">
                        <h2><span>01</span> Datos del cliente</h2>
                        <?php if (!empty($storeIsAuthenticated) && !empty($storeCustomer)): ?>
                            <div class="store-customer-summary">
                                <div><small>Nombre</small><strong><?= htmlspecialchars(trim((string)($storeCustomer['first_name'] ?? '') . ' ' . (string)($storeCustomer['last_name'] ?? '')), ENT_QUOTES, 'UTF-8') ?></strong></div>
                                <div><small>Correo electrónico</small><strong><?= htmlspecialchars((string)($storeCustomer['email'] ?? ''), ENT_QUOTES, 'UTF-8') ?></strong></div>
                                <?php if (!empty($storeCustomer['phone'])): ?>
                                    <div><small>Teléfono</small><strong><?= htmlspecialchars((string)$storeCustomer['phone'], ENT_QUOTES, 'UTF-8') ?></strong></div>
                                <?php endif; ?>
                            </div>
                        <?php else: ?>
                            <div class="store-checkout-access">
                                <span class="store-checkout-access-icon"><i class="fa-solid fa-user-lock"></i></span>
                                <div class="store-checkout-access-copy">
                                    <strong>Continúa con tu cuenta de cliente</strong>
                                    <p>Ya puedes revisar tu pedido y elegir la sede de recogida. Para confirmar la orden y realizar el pago seguro, inicia sesión o crea tu cuenta. Tus productos permanecerán guardados en el carrito.</p>
                                    <div class="store-checkout-access-actions">
                                        <a class="btn btn-velox-primary" href="<?= htmlspecialchars(APP_URL . '/login?redirect=%2Fcheckout', ENT_QUOTES, 'UTF-8') ?>">
                                            Iniciar sesión <i class="fa-solid fa-arrow-right ms-2"></i>
                                        </a>
                                        <a class="store-checkout-register-link" href="<?= htmlspecialchars(APP_URL . '/registro?redirect=%2Fcheckout', ENT_QUOTES, 'UTF-8') ?>">
                                            Crear cuenta
                                        </a>
                                    </div>
                                </div>
                            </div>
                        <?php endif; ?>
                    </div>

                    <div class="store-panel-section">
                        <h2><span>02</span> Sede de recogida</h2>
                        <label class="form-label" for="checkoutBranch">Selecciona dónde recogerás tu pedido *</label>
                        <select class="form-select store-checkout-select" id="checkoutBranch" required>
                            <option value="">Selecciona una sede</option>
                            <?php foreach (($storeBranches ?? []) as $branch): ?>
                                <option value="<?= (int)$branch['id'] ?>">
                                    <?= htmlspecialchars((string)$branch['name'], ENT_QUOTES, 'UTF-8') ?>
                                    <?= !empty($branch['address']) ? ' — ' . htmlspecialchars((string)$branch['address'], ENT_QUOTES, 'UTF-8') : '' ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                        <?php if (empty($storeBranches)): ?>
                            <p class="store-inline-warning">Por el momento no hay sedes habilitadas para pedidos.</p>
                        <?php endif; ?>
                    </div>

                    <?php if (!empty($storeIsAuthenticated)): ?>
                    <div class="store-panel-section">
                        <h2><span>03</span> Cupón de descuento</h2>
                        <p class="store-section-description">¿Tienes un código promocional? Escríbelo aquí antes de pagar.</p>
                        <div class="store-coupon-control">
                            <input type="text" id="checkoutCouponCode" class="form-control" maxlength="40" placeholder="Código de cupón" autocomplete="off">
                            <button type="button" class="btn btn-outline-secondary" id="checkoutApplyCoupon">Aplicar</button>
                        </div>
                        <div id="checkoutCouponMessage" class="store-coupon-message" aria-live="polite"></div>
                    </div>
                    <?php endif; ?>

                    <div class="store-panel-section">
                        <h2><span>04</span> Indicaciones adicionales</h2>
                        <label class="form-label" for="checkoutNotes">Notas del pedido (opcional)</label>
                        <textarea class="form-control store-checkout-notes" id="checkoutNotes" maxlength="1000" rows="3" placeholder="Escribe aquí alguna indicación para preparar tu pedido."></textarea>
                    </div>
                </section>
            </div>

            <div class="col-lg-5">
                <aside class="store-cart-summary store-checkout-summary">
                    <h2>Tu pedido</h2>
                    <div id="checkoutOrderItems" class="store-checkout-order-items"></div>
                    <div class="store-summary-divider"></div>
                    <div class="store-summary-row"><span>Subtotal estimado</span><strong id="checkoutSubtotal">$0</strong></div>
                    <div class="store-summary-row store-discount-row d-none" id="checkoutDiscountRow"><span>Descuento</span><strong id="checkoutDiscount">−$0</strong></div>
                    <div class="store-summary-row store-summary-total"><span>Total estimado</span><strong id="checkoutTotal">$0</strong></div>
                    <p class="store-summary-note">El importe definitivo se calcula nuevamente en el servidor usando los precios e impuestos vigentes del catálogo.</p>
                    <button type="button" class="btn btn-velox-primary w-100" id="checkoutPayButton" disabled>
                        Continuar al pago seguro <i class="fa-solid fa-lock ms-2"></i>
                    </button>
                    <div class="store-secure-payment"><i class="fa-solid fa-shield-halved"></i><span>Pago procesado de forma segura por Wompi.</span></div>
                    <a href="<?= htmlspecialchars(APP_URL . '/carrito', ENT_QUOTES, 'UTF-8') ?>" class="store-continue-shopping"><i class="fa-solid fa-arrow-left me-2"></i>Volver al carrito</a>
                </aside>
            </div>
        </div>
    </div>
</main>
