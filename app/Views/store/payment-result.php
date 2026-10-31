<main class="store-checkout-shell store-payment-result-shell">
    <div class="container py-5">
        <section class="store-payment-result" id="paymentResultPanel" data-reference="<?= htmlspecialchars((string)($paymentResult['reference'] ?? ''), ENT_QUOTES, 'UTF-8') ?>">
            <?php if (empty($paymentResult)): ?>
                <div class="store-payment-result-icon is-error"><i class="fa-solid fa-circle-exclamation"></i></div>
                <span class="store-eyebrow">PAGO NO ENCONTRADO</span>
                <h1>No encontramos esta transacción</h1>
                <p>Verifica que hayas iniciado sesión con la cuenta que realizó el pedido.</p>
                <a href="<?= htmlspecialchars(APP_URL . '/menu', ENT_QUOTES, 'UTF-8') ?>" class="btn btn-velox-primary">Volver al menú</a>
            <?php else: ?>
                <?php
                $initialStatus = (string)($paymentResult['payment_status'] ?? 'pending');
                $initialApproved = $initialStatus === 'approved';
                $initialRejected = in_array($initialStatus, ['declined', 'error'], true);
                ?>
                <div class="store-payment-result-icon <?= $initialApproved ? 'is-success' : ($initialRejected ? 'is-error' : 'is-pending') ?>" id="paymentResultIcon">
                    <i class="fa-solid <?= $initialApproved ? 'fa-check' : ($initialRejected ? 'fa-xmark' : 'fa-spinner fa-spin') ?>"></i>
                </div>
                <span class="store-eyebrow">PEDIDO <?= htmlspecialchars((string)$paymentResult['order_number'], ENT_QUOTES, 'UTF-8') ?></span>
                <h1 id="paymentResultTitle"><?= $initialApproved ? '¡Pago aprobado!' : ($initialRejected ? 'No se pudo aprobar el pago' : 'Estamos confirmando tu pago') ?></h1>
                <p id="paymentResultMessage">
                    <?= $initialApproved ? 'Tu pedido quedó confirmado. Gracias por comprar con nosotros.' : ($initialRejected ? 'El pago fue rechazado o presentó un error. Puedes revisar tu pedido o intentarlo de nuevo.' : 'Hemos recibido la respuesta de la pasarela. Actualizaremos este estado cuando Wompi confirme la transacción.') ?>
                </p>
                <div class="store-payment-receipt">
                    <div><span>Referencia</span><strong><?= htmlspecialchars((string)$paymentResult['reference'], ENT_QUOTES, 'UTF-8') ?></strong></div>
                    <div><span>Valor</span><strong><?= '$' . number_format((float)$paymentResult['amount'], 0, ',', '.') ?> COP</strong></div>
                    <div><span>Estado</span><strong id="paymentResultStatus"><?= htmlspecialchars(strtoupper($initialStatus), ENT_QUOTES, 'UTF-8') ?></strong></div>
                </div>
                <div id="paymentResultPolling" class="<?= $initialStatus === 'pending' ? '' : 'd-none' ?>"><span class="spinner-border spinner-border-sm me-2"></span>Consultando confirmación del pago...</div>
                <a href="<?= htmlspecialchars(APP_URL . '/menu', ENT_QUOTES, 'UTF-8') ?>" class="btn btn-velox-primary mt-4">Volver al menú</a>
            <?php endif; ?>
        </section>
    </div>
</main>
