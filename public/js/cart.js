(() => {
    'use strict';

    const STORAGE_KEY = 'velox_mvc_cart_v1';
    const currency = (amount) => new Intl.NumberFormat('es-CO', {
        style: 'currency', currency: 'COP', maximumFractionDigits: 0
    }).format(Number(amount || 0));

    function readItems() {
        try {
            const parsed = JSON.parse(localStorage.getItem(STORAGE_KEY) || '[]');
            return Array.isArray(parsed) ? parsed.filter((item) =>
                item && Number(item.item_id) > 0 &&
                Number(item.quantity) > 0 &&
                Number.isFinite(Number(item.price))
            ) : [];
        } catch (_) {
            return [];
        }
    }

    let items = readItems();

    function save() {
        try {
            localStorage.setItem(STORAGE_KEY, JSON.stringify(items));
        } catch (error) {
            console.error('[VeloxCart] No fue posible guardar el carrito.', error);
            notify('No fue posible guardar el carrito en este navegador.', 'error');
        }
        render();
        window.dispatchEvent(new CustomEvent('velox:cart-changed', {detail: {items: getItems()}}));
    }

    function notify(message, icon = 'success') {
        if (window.Swal) {
            window.Swal.fire({
                toast: true, position: 'top-end', icon, title: message,
                showConfirmButton: false, timer: 2400
            });
        } else if (typeof window.showToast === 'function') {
            window.showToast(message, icon);
        }
    }

    function getItems() {
        return items.map((item) => ({...item}));
    }

    function getCount() {
        return items.reduce((sum, item) => sum + Number(item.quantity || 0), 0);
    }

    function getSubtotal() {
        return items.reduce((sum, item) => sum + Number(item.price || 0) * Number(item.quantity || 0), 0);
    }

    function add(product) {
        const key = [product.item_id, product.price_option_id || 0, product.is_combo ? 1 : 0].join(':');
        const existing = items.find((item) => item.key === key);
        if (existing) {
            existing.quantity = Math.min(99, Number(existing.quantity) + Number(product.quantity || 1));
        } else {
            items.push({...product, key, quantity: Math.min(99, Math.max(1, Number(product.quantity || 1)))});
        }
        save();
        notify('Producto agregado al carrito.');
    }

    function updateQuantity(key, quantity) {
        const item = items.find((entry) => entry.key === key);
        if (!item) return;
        const next = Number(quantity);
        if (!Number.isInteger(next) || next <= 0) {
            remove(key);
            return;
        }
        item.quantity = Math.min(99, next);
        save();
    }

    function remove(key) {
        items = items.filter((item) => item.key !== key);
        save();
    }

    function clear() {
        items = [];
        save();
    }

    function renderBadge() {
        document.querySelectorAll('[data-cart-count]').forEach((badge) => {
            badge.textContent = String(getCount());
            badge.classList.toggle('d-none', getCount() === 0);
            badge.setAttribute('aria-label', getCount() + ' productos en el carrito');
        });
    }

    function renderCartPage() {
        const container = document.getElementById('storeCartItems');
        if (!container) return;

        const empty = document.getElementById('storeCartEmpty');
        const count = document.getElementById('storeCartItemCount');
        const summaryCount = document.getElementById('storeCartSummaryCount');
        const checkoutLink = document.getElementById('storeCartCheckout');
        const hasItems = items.length > 0;
        const itemCount = getCount();

        empty?.classList.toggle('d-none', hasItems);
        container.classList.toggle('d-none', !hasItems);

        if (count) count.textContent = itemCount + (itemCount === 1 ? ' producto' : ' productos');
        if (summaryCount) summaryCount.textContent = String(itemCount);

        if (checkoutLink) {
            checkoutLink.classList.toggle('is-disabled', !hasItems);
            checkoutLink.setAttribute('aria-disabled', hasItems ? 'false' : 'true');
            checkoutLink.setAttribute('tabindex', hasItems ? '0' : '-1');
        }

        container.innerHTML = items.map((item) => {
            const image = item.image
                ? '<img src="' + escapeHtml(item.image) + '" alt="' + escapeHtml(item.name) + '" loading="lazy">'
                : '<span class="store-cart-placeholder"><i class="fa-solid fa-utensils"></i></span>';

            const details = [item.option_label, item.is_combo ? 'Combo' : ''].filter(Boolean).join(' · ');
            const lineTotal = Number(item.price) * Number(item.quantity);

            return '<article class="velox-cart-line" data-cart-key="' + escapeHtml(item.key) + '">' +
                '<div class="velox-cart-line-media">' + image + '</div>' +
                '<div class="velox-cart-line-info">' +
                    '<span class="velox-cart-line-kicker">PRODUCTO SELECCIONADO</span>' +
                    '<h3>' + escapeHtml(item.name) + '</h3>' +
                    (details ? '<p class="velox-cart-line-details">' + escapeHtml(details) + '</p>' : '<p class="velox-cart-line-details">Preparado para tu pedido</p>') +
                    '<button type="button" class="velox-cart-remove" data-cart-action="remove"><i class="fa-regular fa-trash-can"></i> Eliminar</button>' +
                '</div>' +
                '<div class="velox-cart-line-controls">' +
                    '<div class="velox-cart-unit-price"><span>Precio unitario</span><strong>' + currency(item.price) + '</strong></div>' +
                    '<div class="velox-cart-quantity">' +
                        '<button type="button" data-cart-action="decrease" aria-label="Disminuir cantidad">−</button>' +
                        '<span>' + Number(item.quantity) + '</span>' +
                        '<button type="button" data-cart-action="increase" aria-label="Aumentar cantidad">+</button>' +
                    '</div>' +
                    '<div class="velox-cart-line-total"><span>Subtotal</span><strong>' + currency(lineTotal) + '</strong></div>' +
                '</div>' +
            '</article>';
        }).join('');

        const subtotal = currency(getSubtotal());
        const subtotalNode = document.getElementById('storeCartSubtotal');
        const totalNode = document.getElementById('storeCartTotal');

        if (subtotalNode) subtotalNode.textContent = subtotal;
        if (totalNode) totalNode.textContent = subtotal;
    }

    function escapeHtml(value) {
        return String(value ?? '').replace(/[&<>"']/g, (char) => ({
            '&':'&amp;','<':'&lt;','>':'&gt;','"':'&quot;',"'":'&#039;'
        }[char]));
    }

    document.addEventListener('click', (event) => {
        const addButton = event.target.closest('[data-cart-add]');
        if (addButton) {
            const card = addButton.closest('[data-cart-product]');
            if (!card) return;

            let product;
            try {
                product = JSON.parse(card.dataset.cartProduct || '{}');
            } catch (_) {
                notify('No fue posible identificar este producto.', 'error');
                return;
            }

            const optionSelect = card.querySelector('[data-cart-option]');
            const option = optionSelect
                ? optionSelect.options[optionSelect.selectedIndex]
                : null;
            const comboInput = card.querySelector('[data-cart-combo]');
            const isCombo = Boolean(comboInput?.checked);
            const price = option
                ? Number(isCombo ? option.dataset.comboPrice : option.dataset.price)
                : Number(isCombo ? product.combo_price : product.price);

            if (!Number.isFinite(price) || price <= 0) {
                notify(isCombo ? 'Este producto no tiene un precio de combo disponible.' : 'El precio del producto no es válido.', 'error');
                return;
            }

            const quantityInput = card.querySelector('[data-cart-quantity]');
            const quantity = Math.min(99, Math.max(1, Number(quantityInput?.value || 1)));
            add({
                item_id: Number(product.id),
                name: String(product.name || 'Producto'),
                image: String(product.image || ''),
                price,
                quantity,
                price_option_id: option ? Number(option.value) : null,
                option_label: option ? String(option.dataset.label || '') : '',
                units_per_package: option ? Number(option.dataset.quantity || 1) : 1,
                is_combo: isCombo
            });
            return;
        }

        const actionButton = event.target.closest('[data-cart-action]');
        if (!actionButton) return;
        const row = actionButton.closest('[data-cart-key]');
        if (!row) return;
        const key = row.dataset.cartKey;
        const item = items.find((entry) => entry.key === key);
        if (!item) return;

        switch (actionButton.dataset.cartAction) {
            case 'increase': updateQuantity(key, Number(item.quantity) + 1); break;
            case 'decrease': updateQuantity(key, Number(item.quantity) - 1); break;
            case 'remove': remove(key); break;
        }
    });

    function render() {
        renderBadge();
        renderCartPage();
    }

    window.VeloxCart = {
        getItems, getCount, getSubtotal, add, updateQuantity, remove, clear,
        refresh: render
    };

    window.addEventListener('storage', (event) => {
        if (event.key === STORAGE_KEY) {
            items = readItems();
            render();
        }
    });

    render();
})();
