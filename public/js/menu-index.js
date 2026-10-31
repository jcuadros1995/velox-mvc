(() => {
    'use strict';

    const search = document.getElementById('menuCategorySearch');
    const cards = Array.from(document.querySelectorAll('[data-menu-card]'));
    const noResults = document.getElementById('menuCategoryNoResults');

    if (!search || cards.length === 0) return;

    function filterCategories() {
        const query = search.value.trim().toLocaleLowerCase('es-CO');
        let visible = 0;

        cards.forEach((card) => {
            const searchable = (card.dataset.menuSearch || '').toLocaleLowerCase('es-CO');
            const matches = query === '' || searchable.includes(query);

            card.classList.toggle('d-none', !matches);
            if (matches) visible += 1;
        });

        noResults?.classList.toggle('d-none', visible > 0);
    }

    search.addEventListener('input', filterCategories);

    document.addEventListener('keydown', (event) => {
        if ((event.ctrlKey || event.metaKey) && event.key.toLowerCase() === 'k') {
            event.preventDefault();
            search.focus();
        }

        if (event.key === 'Escape' && document.activeElement === search) {
            search.value = '';
            filterCategories();
            search.blur();
        }
    });

    filterCategories();
})();
