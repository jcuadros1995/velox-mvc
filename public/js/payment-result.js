(() => {
    'use strict';
    const panel = document.getElementById('paymentResultPanel');
    if (!panel) return;
    const reference = panel.dataset.reference;
    const polling = document.getElementById('paymentResultPolling');
    if (!reference || !polling) return;

    const appUrl = window.APP_URL || '';
    const title = document.getElementById('paymentResultTitle');
    const message = document.getElementById('paymentResultMessage');
    const status = document.getElementById('paymentResultStatus');
    const icon = document.getElementById('paymentResultIcon');

    function setState(payment) {
        const value = String(payment.payment_status || 'pending').toLowerCase();
        status.textContent = value.toUpperCase();

        if (value === 'approved') {
            icon.className = 'store-payment-result-icon is-success';
            icon.innerHTML = '<i class="fa-solid fa-check"></i>';
            title.textContent = '¡Pago aprobado!';
            message.textContent = 'Tu pedido quedó confirmado. Gracias por comprar con nosotros.';
            polling.classList.add('d-none');
            return true;
        }
        if (value === 'declined' || value === 'error') {
            icon.className = 'store-payment-result-icon is-error';
            icon.innerHTML = '<i class="fa-solid fa-xmark"></i>';
            title.textContent = 'No se pudo aprobar el pago';
            message.textContent = 'El pago fue rechazado o presentó un error. Puedes volver al menú e iniciar una nueva compra.';
            polling.classList.add('d-none');
            return true;
        }
        if (value === 'refunded') {
            icon.className = 'store-payment-result-icon is-error';
            icon.innerHTML = '<i class="fa-solid fa-rotate-left"></i>';
            title.textContent = 'Pago reembolsado';
            message.textContent = 'La transacción fue marcada como reembolsada.';
            polling.classList.add('d-none');
            return true;
        }
        icon.className = 'store-payment-result-icon is-pending';
        icon.innerHTML = '<i class="fa-solid fa-spinner fa-spin"></i>';
        title.textContent = 'Estamos confirmando tu pago';
        message.textContent = 'Wompi aún no ha confirmado el resultado. Esta página se actualizará automáticamente.';
        return false;
    }

    async function poll() {
        let attempts = 0;
        while (attempts < 30) {
            attempts += 1;
            try {
                const response = await fetch(appUrl + '/api/payments/status?reference=' + encodeURIComponent(reference), {
                    credentials:'same-origin',
                    headers:{'Accept':'application/json'}
                });
                const payload = await response.json();
                if (response.ok && payload.success && setState(payload.data)) return;
            } catch (error) {
                console.warn('[PaymentResult] No fue posible consultar el estado todavía.', error);
            }
            await new Promise((resolve) => window.setTimeout(resolve, 4000));
        }
        polling.textContent = 'La confirmación aún está pendiente. Puedes volver a consultar esta página más tarde.';
    }

    if (status && status.textContent.toLowerCase() === 'pending') poll();
})();
