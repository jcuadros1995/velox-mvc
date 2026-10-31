(() => {
    'use strict';

    const root = document.querySelector('[data-store-catalog]');
    if (!root) return;

    const grid = document.getElementById('storeProductGrid');
    const searchInput = document.getElementById('storeProductSearch');
    const sortSelect = document.getElementById('storeProductSort');
    const countNode = document.getElementById('storeProductCount');
    const emptyNode = document.getElementById('storeProductsEmpty');
    const searchClear = document.querySelector('[data-store-search-clear]');
    const cards = Array.from(root.querySelectorAll('[data-store-product]'));
    const filters = Array.from(root.querySelectorAll('[data-store-filter]'));

    let activeCategory = String(root.dataset.initialCategory || 'all');
    if (activeCategory === '0') activeCategory = 'all';

    const normalize = (value) => String(value || '')
        .normalize('NFD')
        .replace(/[\u0300-\u036f]/g, '')
        .toLocaleLowerCase('es-CO')
        .trim();

    const originalOrder = new Map(cards.map((card, index) => [card, index]));

    function matchesCategory(card) {
        if (activeCategory === 'all') return true;

        const categoryId = String(card.dataset.storeCategoryId || '');
        const parentId = String(card.dataset.storeParentId || '');

        return categoryId === activeCategory || parentId === activeCategory;
    }

    function sortCards(items) {
        const mode = sortSelect?.value || 'featured';
        return items.sort((a, b) => {
            if (mode === 'name') {
                return normalize(a.dataset.storeName).localeCompare(normalize(b.dataset.storeName), 'es');
            }
            if (mode === 'price-asc') {
                return Number(a.dataset.storePrice || 0) - Number(b.dataset.storePrice || 0);
            }
            if (mode === 'price-desc') {
                return Number(b.dataset.storePrice || 0) - Number(a.dataset.storePrice || 0);
            }

            const featuredDiff = Number(b.dataset.storeFeatured || 0) - Number(a.dataset.storeFeatured || 0);
            return featuredDiff || originalOrder.get(a) - originalOrder.get(b);
        });
    }

    const currency = (value) => '
        filters.forEach((button) => {
            const selected = String(button.dataset.storeFilter) === activeCategory;
            button.classList.toggle('is-active', selected);
            button.setAttribute('aria-pressed', selected ? 'true' : 'false');
        });
    }

    function updateCatalog() {
        const term = normalize(searchInput?.value || '');
        const visibleCards = [];

        cards.forEach((card) => {
            const searchable = normalize(card.dataset.storeSearch || '');
            const matchesSearch = !term || searchable.includes(term);
            const visible = matchesCategory(card) && matchesSearch;

            card.hidden = !visible;
            if (visible) visibleCards.push(card);
        });

        sortCards(visibleCards).forEach((card) => grid.appendChild(card));

        const visibleCount = visibleCards.length;
        if (countNode) countNode.textContent = visibleCount + (visibleCount === 1 ? ' producto' : ' productos');
        if (emptyNode) emptyNode.classList.toggle('d-none', visibleCount > 0);
        if (searchClear) searchClear.classList.toggle('is-visible', Boolean(searchInput?.value));
        syncFilterButtons();
    }

    filters.forEach((button) => {
        button.addEventListener('click', () => {
            activeCategory = String(button.dataset.storeFilter || 'all');
            updateCatalog();
        });
    });

    searchInput?.addEventListener('input', updateCatalog);
    sortSelect?.addEventListener('change', updateCatalog);

    root.querySelectorAll('[data-store-product]').forEach(syncProductOptions);
    root.addEventListener('change', (event) => {
        const select = event.target.closest('[data-cart-option]');
        if (select) {
            const card = select.closest('[data-store-product]');
            if (card) syncProductOptions(card);
        }
    });

    searchClear?.addEventListener('click', () => {
        if (searchInput) {
            searchInput.value = '';
            searchInput.focus();
        }
        updateCatalog();
    });

    root.querySelector('[data-store-reset-filters]')?.addEventListener('click', () => {
        activeCategory = 'all';
        if (searchInput) searchInput.value = '';
        if (sortSelect) sortSelect.value = 'featured';
        updateCatalog();
    });

    updateCatalog();
})();
 + new Intl.NumberFormat('es-CO', {
        maximumFractionDigits: 0
    }).format(Number(value || 0));

    function syncProductOptions(card) {
        const optionSelect = card.querySelector('[data-cart-option]');
        const selectedOption = optionSelect?.options?.[optionSelect.selectedIndex];
        const priceNode = card.querySelector('[data-store-current-price]');
        const comboNode = card.querySelector('[data-store-current-combo]');
        const comboInput = card.querySelector('[data-cart-combo]');

        if (selectedOption) {
            const price = Number(selectedOption.dataset.price || 0);
            const comboPrice = Number(selectedOption.dataset.comboPrice || 0);

            if (priceNode) priceNode.textContent = currency(price);
            if (comboNode && comboPrice > 0) comboNode.textContent = currency(comboPrice);

            if (comboInput) {
                comboInput.disabled = comboPrice <= 0;
                if (comboInput.disabled) comboInput.checked = false;
                comboInput.closest('label')?.classList.toggle('is-disabled', comboInput.disabled);
            }
        }
    }

    function syncFilterButtons() {
        filters.forEach((button) => {
            const selected = String(button.dataset.storeFilter) === activeCategory;
            button.classList.toggle('is-active', selected);
            button.setAttribute('aria-pressed', selected ? 'true' : 'false');
        });
    }

    function updateCatalog() {
        const term = normalize(searchInput?.value || '');
        const visibleCards = [];

        cards.forEach((card) => {
            const searchable = normalize(card.dataset.storeSearch || '');
            const matchesSearch = !term || searchable.includes(term);
            const visible = matchesCategory(card) && matchesSearch;

            card.hidden = !visible;
            if (visible) visibleCards.push(card);
        });

        sortCards(visibleCards).forEach((card) => grid.appendChild(card));

        const visibleCount = visibleCards.length;
        if (countNode) countNode.textContent = visibleCount + (visibleCount === 1 ? ' producto' : ' productos');
        if (emptyNode) emptyNode.classList.toggle('d-none', visibleCount > 0);
        if (searchClear) searchClear.classList.toggle('is-visible', Boolean(searchInput?.value));
        syncFilterButtons();
    }

    filters.forEach((button) => {
        button.addEventListener('click', () => {
            activeCategory = String(button.dataset.storeFilter || 'all');
            updateCatalog();
        });
    });

    searchInput?.addEventListener('input', updateCatalog);
    sortSelect?.addEventListener('change', updateCatalog);

    searchClear?.addEventListener('click', () => {
        if (searchInput) {
            searchInput.value = '';
            searchInput.focus();
        }
        updateCatalog();
    });

    root.querySelector('[data-store-reset-filters]')?.addEventListener('click', () => {
        activeCategory = 'all';
        if (searchInput) searchInput.value = '';
        if (sortSelect) sortSelect.value = 'featured';
        updateCatalog();
    });

    updateCatalog();
})();
