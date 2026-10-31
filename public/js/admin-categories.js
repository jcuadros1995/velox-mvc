(function () {
    'use strict';

    const $ = (id) => document.getElementById(id);

    document.addEventListener('DOMContentLoaded', () => {
        if ($('adminCategoriesPage')) initList();
        if ($('adminCategoryFormPage')) initCategoryForm();
        if ($('adminCategoryPageForm')) initPageForm();
    });

    async function initList() {
        bindListEvents();
        await loadCategories();
    }

    function bindListEvents() {
        $('btnRefreshCategories')?.addEventListener('click', loadCategories);
        $('categoriesSearch')?.addEventListener('input', renderFiltered);
        $('categoriesStatus')?.addEventListener('change', renderFiltered);
        $('categoriesPageStatus')?.addEventListener('change', renderFiltered);
        $('categoriesTableBody')?.addEventListener('click', handleTableAction);
    }

    let categories = [];

    async function loadCategories() {
        setTableLoading(true);

        try {
            const response = await fetch(`${window.APP_URL}/api/admin/categories`, {
                credentials: 'same-origin',
                headers: { 'Accept': 'application/json' }
            });

            const result = await response.json();

            if (!response.ok || !result.success) {
                throw new Error(result.message || 'No fue posible cargar las categorías.');
            }

            categories = Array.isArray(result.data) ? result.data : [];
            renderStats();
            renderFiltered();
        } catch (error) {
            console.error('[ADMIN CATEGORIES]', error);
            showError(error.message || 'No fue posible cargar las categorías.');
        } finally {
            setTableLoading(false);
        }
    }

    function renderStats() {
        const total = categories.length;
        const active = categories.filter(c => Number(c.is_active) === 1).length;
        const pages = categories.filter(c => Number(c.category_page_id || 0) > 0).length;
        const published = categories.filter(c => Number(c.page_is_published) === 1).length;

        $('categoriesStatTotal').textContent = total;
        $('categoriesStatActive').textContent = active;
        $('categoriesStatPages').textContent = pages;
        $('categoriesStatPublished').textContent = published;
    }

    function renderFiltered() {
        const search = ($('categoriesSearch')?.value || '').trim().toLowerCase();
        const status = $('categoriesStatus')?.value || '';
        const pageStatus = $('categoriesPageStatus')?.value || '';

        const filtered = categories.filter(category => {
            const haystack = [
                category.name,
                category.slug,
                category.description,
                category.parent_name
            ].join(' ').toLowerCase();

            if (search && !haystack.includes(search)) return false;
            if (status !== '' && String(category.is_active) !== status) return false;

            if (pageStatus === 'configured' && Number(category.category_page_id || 0) === 0) return false;
            if (pageStatus === 'pending' && Number(category.category_page_id || 0) > 0) return false;
            if (pageStatus === 'published' && Number(category.page_is_published || 0) !== 1) return false;

            return true;
        });

        renderTable(filtered);
    }

    function renderTable(rows) {
        const body = $('categoriesTableBody');

        if (!rows.length) {
            body.innerHTML = `
                <tr>
                    <td colspan="6" class="categories-empty">
                        <i class="fa-solid fa-folder-open"></i>
                        <strong>No hay categorías para mostrar.</strong>
                        <span>Prueba con otros filtros o crea una nueva categoría.</span>
                    </td>
                </tr>
            `;
            return;
        }

        body.innerHTML = rows.map(category => {
            const active = Number(category.is_active) === 1;
            const hasPage = Number(category.category_page_id || 0) > 0;
            const published = Number(category.page_is_published || 0) === 1;
            const parent = category.parent_name
                ? `<span class="category-parent"><i class="fa-solid fa-turn-up"></i>${escapeHtml(category.parent_name)}</span>`
                : '<span class="category-root">Principal</span>';

            const pageStatus = hasPage
                ? `<span class="category-badge category-badge-page ${published ? 'is-published' : ''}"><i class="fa-solid ${published ? 'fa-circle-check' : 'fa-file-lines'}"></i>${published ? 'Publicada' : 'Configurada'}</span>`
                : '<span class="category-badge category-badge-pending"><i class="fa-solid fa-circle-exclamation"></i>Sin página</span>';

            return `
                <tr>
                    <td>
                        <div class="category-name-cell">
                            <div class="category-name-icon ${category.parent_id ? 'is-child' : ''}">
                                <i class="fa-solid ${category.parent_id ? 'fa-folder' : 'fa-layer-group'}"></i>
                            </div>
                            <div>
                                <strong>${escapeHtml(category.name)}</strong>
                                <small>${escapeHtml(category.slug)}</small>
                            </div>
                        </div>
                    </td>
                    <td>${parent}</td>
                    <td><span class="category-products-count"><i class="fa-solid fa-burger"></i>${Number(category.item_count || 0)}</span></td>
                    <td>${pageStatus}</td>
                    <td><span class="category-status ${active ? 'is-active' : 'is-inactive'}"><span></span>${active ? 'Activa' : 'Inactiva'}</span></td>
                    <td>
                        <div class="category-row-actions">
                            <a class="category-action-btn" href="${window.APP_URL}/admin/categories/edit?id=${Number(category.id)}" title="Editar categoría"><i class="fa-solid fa-pen"></i></a>
                            <a class="category-action-btn category-action-page" href="${window.APP_URL}/admin/categories/page?id=${Number(category.id)}" title="Configurar página"><i class="fa-solid fa-globe"></i></a>
                            <button type="button" class="category-action-btn ${active ? 'is-danger' : 'is-success'}" data-action="toggle" data-id="${Number(category.id)}" data-active="${active ? 1 : 0}" title="${active ? 'Desactivar' : 'Activar'}"><i class="fa-solid ${active ? 'fa-ban' : 'fa-check'}"></i></button>
                        </div>
                    </td>
                </tr>
            `;
        }).join('');
    }

    async function handleTableAction(event) {
        const button = event.target.closest('[data-action="toggle"]');
        if (!button) return;

        const id = Number(button.dataset.id || 0);
        const active = Number(button.dataset.active || 0) === 1;

        if (!id) return;

        const confirmed = await Swal.fire({
            icon: 'question',
            title: active ? '¿Desactivar categoría?' : '¿Activar categoría?',
            text: active
                ? 'La categoría dejará de considerarse activa.'
                : 'La categoría volverá a estar activa.',
            showCancelButton: true,
            confirmButtonText: active ? 'Sí, desactivar' : 'Sí, activar',
            cancelButtonText: 'Cancelar',
            reverseButtons: true
        });

        if (!confirmed.isConfirmed) return;

        try {
            const response = await fetch(`${window.APP_URL}/api/admin/categories/toggle`, {
                method: 'POST',
                credentials: 'same-origin',
                headers: {
                    'Content-Type': 'application/json',
                    'Accept': 'application/json'
                },
                body: JSON.stringify({ id, is_active: active ? 0 : 1 })
            });

            const result = await response.json();

            if (!response.ok || !result.success) {
                throw new Error(result.message || 'No fue posible cambiar el estado.');
            }

            showSuccess(result.message);
            await loadCategories();
        } catch (error) {
            console.error(error);
            showError(error.message || 'No fue posible cambiar el estado.');
        }
    }

    function initCategoryForm() {
        const page = $('adminCategoryFormPage');
        const mode = page.dataset.mode;
        const id = Number(page.dataset.id || 0);

        $('categoryName')?.addEventListener('input', () => {
            const slug = $('categorySlug');
            if (!slug.dataset.touched) slug.value = slugify($('categoryName').value);
        });

        $('categorySlug')?.addEventListener('input', function () {
            this.dataset.touched = '1';
            this.value = slugify(this.value);
        });

        $('btnSaveCategory')?.addEventListener('click', async () => {
            const name = $('categoryName').value.trim();
            const slug = slugify($('categorySlug').value.trim() || name);

            if (!name) {
                showError('El nombre de la categoría es obligatorio.');
                $('categoryName').focus();
                return;
            }

            const payload = {
                id,
                name,
                slug,
                parent_id: $('categoryParent').value || null,
                catalog_type: $('categoryType').value,
                description: $('categoryDescription').value.trim(),
                is_active: $('categoryActive').checked ? 1 : 0
            };

            const endpoint = mode === 'edit'
                ? '/api/admin/categories/update'
                : '/api/admin/categories/store';

            await submitJson(endpoint, payload, mode === 'edit' ? 'Categoría actualizada correctamente.' : 'Categoría creada correctamente.', true);
        });
    }

    function initPageForm() {
        const page = $('adminCategoryPageForm');
        const categoryId = Number(page.dataset.categoryId || 0);

        $('pageSlug')?.addEventListener('input', function () {
            this.value = slugify(this.value);
        });

        $('btnSaveCategoryPage')?.addEventListener('click', async () => {
            const slug = slugify($('pageSlug').value.trim());
            const title = $('pageTitle').value.trim();

            if (!categoryId) {
                showError('Categoría inválida.');
                return;
            }

            if (!slug) {
                showError('El slug público es obligatorio.');
                $('pageSlug').focus();
                return;
            }

            if (!title) {
                showError('El título de la página es obligatorio.');
                $('pageTitle').focus();
                return;
            }

            const payload = {
                category_id: categoryId,
                slug,
                title,
                meta_title: $('pageMetaTitle').value.trim(),
                meta_description: $('pageMetaDescription').value.trim(),
                h1: $('pageH1').value.trim(),
                intro_text: $('pageIntro').value.trim(),
                hero_image: $('pageHeroImage').value.trim(),
                content_top: $('pageContentTop').value,
                content_bottom: $('pageContentBottom').value,
                faq_enabled: $('pageFaqEnabled').checked ? 1 : 0,
                is_published: $('pagePublished').checked ? 1 : 0
            };

            await submitJson('/api/admin/categories/page/save', payload, 'Página guardada correctamente.', false);
        });
    }

    async function submitJson(endpoint, payload, message, redirect) {
        const button = endpoint.includes('/page/') ? $('btnSaveCategoryPage') : $('btnSaveCategory');
        const original = button?.innerHTML;

        if (button) {
            button.disabled = true;
            button.innerHTML = '<i class="fa-solid fa-spinner fa-spin"></i> Guardando...';
        }

        try {
            const response = await fetch(`${window.APP_URL}${endpoint}`, {
                method: 'POST',
                credentials: 'same-origin',
                headers: {
                    'Content-Type': 'application/json',
                    'Accept': 'application/json'
                },
                body: JSON.stringify(payload)
            });

            const result = await response.json();

            if (!response.ok || !result.success) {
                throw new Error(result.message || 'No fue posible guardar los cambios.');
            }

            showSuccess(result.message || message);

            if (redirect) {
                setTimeout(() => {
                    window.location.href = `${window.APP_URL}/admin/categories`;
                }, 650);
            }
        } catch (error) {
            console.error(error);
            showError(error.message || 'No fue posible guardar los cambios.');
        } finally {
            if (button) {
                button.disabled = false;
                button.innerHTML = original;
            }
        }
    }

    function setTableLoading(loading) {
        const body = $('categoriesTableBody');
        if (!body) return;
        if (loading) {
            body.innerHTML = '<tr><td colspan="6" class="categories-loading"><i class="fa-solid fa-spinner fa-spin"></i> Cargando categorías...</td></tr>';
        }
    }

    function slugify(value) {
        if (!value) return '';
        return value
            .normalize('NFD')
            .replace(/[\u0300-\u036f]/g, '')
            .toLowerCase()
            .replace(/[^a-z0-9]+/g, '-')
            .replace(/^-+|-+$/g, '')
            .substring(0, 180);
    }

    function escapeHtml(value) {
        return String(value ?? '')
            .replace(/&/g, '&amp;')
            .replace(/</g, '&lt;')
            .replace(/>/g, '&gt;')
            .replace(/"/g, '&quot;')
            .replace(/'/g, '&#039;');
    }

    function showSuccess(message) {
        if (typeof showToast === 'function') {
            showToast('success', message);
            return;
        }

        if (typeof Swal !== 'undefined') {
            Swal.fire({
                icon: 'success',
                title: 'Listo',
                text: message,
                timer: 1800,
                showConfirmButton: false
            });
            return;
        }

        alert(message);
    }

    function showError(message) {
        if (typeof showToast === 'function') {
            showToast('error', message);
            return;
        }

        if (typeof Swal !== 'undefined') {
            Swal.fire({
                icon: 'error',
                title: 'Error',
                text: message
            });
            return;
        }

        alert(message);
    }
})();
