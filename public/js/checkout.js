(() => {
    'use strict';

    const cart = window.VeloxCart;
    const payButton = document.getElementById('checkoutPayButton');
    if (!payButton || !cart) return;

    const appUrl = window.APP_URL || '';
    const currency = (amount) => new Intl.NumberFormat('es-CO', {
        style:'currency',currency:'COP',maximumFractionDigits:0
    }).format(Number(amount || 0));
    const escapeHtml = (value) => String(value ?? '').replace(/[&<>"']/g, (char) => ({
        '&':'&amp;','<':'&lt;','>':'&gt;','"':'&quot;',"'":'&#039;'
    }[char]));
    const branchSelect = document.getElementById('checkoutBranch');
    const couponInput = document.getElementById('checkoutCouponCode');
    const couponButton = document.getElementById('checkoutApplyCoupon');
    const couponMessage = document.getElementById('checkoutCouponMessage');
    const itemsContainer = document.getElementById('checkoutOrderItems');
    let appliedCoupon = null;
    let busy = false;

    function notify(message, icon = 'error') {
        if (window.Swal) {
            window.Swal.fire({icon,title:message,confirmButtonText:'Entendido'});
        } else {
            window.alert(message);
        }
    }

    function getSubtotal() {
        return cart.getSubtotal();
    }

    function render() {
        const items = cart.getItems();
        itemsContainer.innerHTML = items.map((item) =>
            '<div class="store-checkout-order-line"><span>' +
            Number(item.quantity) + ' × ' + escapeHtml(item.name) +
            (item.option_label ? '<small>' + escapeHtml(item.option_label) + '</small>' : '') +
            '</span><strong>' + currency(Number(item.price) * Number(item.quantity)) + '</strong></div>'
        ).join('');

        const subtotal = getSubtotal();
        const discount = appliedCoupon ? Number(appliedCoupon.discount_amount || 0) : 0;
        document.getElementById('checkoutSubtotal').textContent = currency(subtotal);
        document.getElementById('checkoutDiscount').textContent = '−' + currency(discount);
        document.getElementById('checkoutDiscountRow').classList.toggle('d-none', !appliedCoupon);
        document.getElementById('checkoutTotal').textContent = currency(Math.max(0, subtotal - discount));
        payButton.disabled = items.length === 0 || !branchSelect.value || busy;
        if (items.length === 0) {
            notify('Tu carrito está vacío. Agrega productos antes de continuar.');
            window.location.href = appUrl + '/carrito';
        }
    }

    async function post(path, body) {
        const response = await fetch(appUrl + path, {
            method:'POST',
            credentials:'same-origin',
            headers:{'Content-Type':'application/json'},
            body:JSON.stringify(body)
        });
        const data = await response.json().catch(() => ({}));
        if (!response.ok || data.success === false) {
            throw new Error(data.message || 'No fue posible completar la solicitud.');
        }
        return data;
    }

    couponButton.addEventListener('click', async () => {
        const code = couponInput.value.trim();
        if (!code) {
            appliedCoupon = null;
            couponMessage.textContent = 'Escribe el código del cupón.';
            couponMessage.className = 'store-coupon-message is-error';
            render();
            return;
        }

        couponButton.disabled = true;
        couponButton.textContent = 'Validando...';
        try {
            const result = await post('/api/coupons/validate', {
                code,
                subtotal:getSubtotal()
            });
            appliedCoupon = result.data;
            couponInput.value = appliedCoupon.code;
            couponMessage.textContent = 'Cupón aplicado: ' + appliedCoupon.code + '. El descuento final será validado al registrar el pedido.';
            couponMessage.className = 'store-coupon-message is-success';
            render();
        } catch (error) {
            appliedCoupon = null;
            couponMessage.textContent = error.message;
            couponMessage.className = 'store-coupon-message is-error';
            render();
        } finally {
            couponButton.disabled = false;
            couponButton.textContent = 'Aplicar';
        }
    });

    couponInput.addEventListener('input', () => {
        if (appliedCoupon && couponInput.value.trim().toUpperCase() !== String(appliedCoupon.code).toUpperCase()) {
            appliedCoupon = null;
            couponMessage.textContent = 'El cupón fue retirado. Puedes aplicar otro código.';
            couponMessage.className = 'store-coupon-message';
            render();
        }
    });

    branchSelect.addEventListener('change', render);
    window.addEventListener('velox:cart-changed', render);

    function submitWompi(checkout) {
        const form = document.createElement('form');
        form.method = 'GET';
        form.action = checkout.checkout_url;
        form.style.display = 'none';

        const fields = {
            'public-key': checkout.public_key,
            'currency': checkout.currency,
            'amount-in-cents': String(checkout.amount_in_cents),
            'reference': checkout.reference,
            'signature:integrity': checkout.signature,
            'redirect-url': checkout.redirect_url,
            'customer-data:email': checkout.customer_email || '',
            'customer-data:full-name': checkout.customer_full_name || '',
            'customer-data:phone-number': checkout.customer_phone || '',
            'customer-data:phone-number-prefix': checkout.customer_phone_prefix || ''
        };

        Object.entries(fields).forEach(([name,value]) => {
            if (value === undefined || value === null || value === '') return;
            const input = document.createElement('input');
            input.type = 'hidden';
            input.name = name;
            input.value = String(value);
            form.appendChild(input);
        });

        document.body.appendChild(form);
        form.submit();
    }

    payButton.addEventListener('click', async () => {
        if (busy) return;
        const items = cart.getItems();
        if (!items.length) {
            notify('Tu carrito está vacío.');
            return;
        }
        if (!branchSelect.value) {
            notify('Selecciona la sede donde recogerás tu pedido.');
            branchSelect.focus();
            return;
        }

        busy = true;
        payButton.disabled = true;
        payButton.innerHTML = '<span class="spinner-border spinner-border-sm me-2"></span>Preparando pedido...';

        try {
            const orderResult = await post('/api/orders/create', {
                branch_id:Number(branchSelect.value),
                notes:document.getElementById('checkoutNotes').value.trim(),
                coupon_code:appliedCoupon ? appliedCoupon.code : '',
                items:items.map((item) => ({
                    item_id:Number(item.item_id),
                    quantity:Number(item.quantity),
                    price_option_id:item.price_option_id ? Number(item.price_option_id) : null,
                    is_combo:Boolean(item.is_combo)
                }))
            });

            const order = orderResult.data || {};
            if (!order.order_id) throw new Error('El pedido no devolvió un identificador válido.');

            payButton.innerHTML = '<span class="spinner-border spinner-border-sm me-2"></span>Conectando con Wompi...';
            const paymentResult = await post('/api/payments/checkout', {
                order_id:Number(order.order_id),
                gateway:'wompi'
            });

            const checkout = paymentResult.data || {};
            if (!checkout.public_key || !checkout.signature || !checkout.reference || !checkout.checkout_url) {
                throw new Error('Wompi no devolvió los datos necesarios para iniciar el pago.');
            }

            cart.clear();
            submitWompi(checkout);
        } catch (error) {
            notify(error.message, 'error');
            busy = false;
            payButton.disabled = !cart.getItems().length || !branchSelect.value;
            payButton.innerHTML = 'Continuar al pago seguro <i class="fa-solid fa-lock ms-2"></i>';
        }
    });

    render();
})();
