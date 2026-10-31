(() => {
    'use strict';
    const root = document.getElementById('adminCouponsPage');
    if (!root) return;
    const api = (path) => (window.APP_URL || '') + path;
    const tbody = document.getElementById('couponsTableBody');
    const form = document.getElementById('couponForm');
    const modal = window.bootstrap && window.bootstrap.Modal.getOrCreateInstance(document.getElementById('couponModal'));
    const search = document.getElementById('couponSearch');
    const statusFilter = document.getElementById('couponStatusFilter');
    const saveButton = document.getElementById('btnSaveCoupon');
    let coupons = [];
    const escapeHtml = (value) => String(value ?? '').replace(/[&<>"']/g, (char) => ({'&':'&amp;','<':'&lt;','>':'&gt;','"':'&quot;',"'":'&#039;'}[char]));
    const money = (value) => new Intl.NumberFormat('es-CO', {style:'currency',currency:'COP',maximumFractionDigits:0}).format(Number(value || 0));
    const formatDate = (value) => {
        if (!value) return 'Sin fecha';
        const date = new Date(String(value).replace(' ', 'T'));
        return Number.isNaN(date.getTime()) ? '—' : date.toLocaleString('es-CO', {dateStyle:'medium',timeStyle:'short'});
    };
    const discountLabel = (coupon) => coupon.discount_type === 'percentage' ? Number(coupon.discount_value) + '%' : money(coupon.discount_value);
    const statusLabel = {active:'Activo',scheduled:'Programado',expired:'Vencido',exhausted:'Agotado',inactive:'Inactivo'};
    const toast = (message, icon = 'success') => {
        if (window.Swal) Swal.fire({toast:true,position:'top-end',icon,title:message,showConfirmButton:false,timer:2600});
        else window.alert(message);
    };
    async function request(path, options = {}) {
        const response = await fetch(api(path), {credentials:'same-origin',headers:{'Content-Type':'application/json',...(options.headers || {})},...options});
        const payload = await response.json().catch(() => ({}));
        if (!response.ok || payload.success === false) throw new Error(payload.message || 'No fue posible completar la solicitud.');
        return payload;
    }
    function updateStats() {
        document.getElementById('couponStatTotal').textContent = coupons.length;
        document.getElementById('couponStatActive').textContent = coupons.filter((c) => c.computed_status === 'active').length;
        document.getElementById('couponStatScheduled').textContent = coupons.filter((c) => c.computed_status === 'scheduled').length;
        document.getElementById('couponStatUses').textContent = coupons.reduce((sum,c) => sum + Number(c.usage_count || 0),0);
    }
    function render() {
        const query = search.value.trim().toLowerCase(), state = statusFilter.value;
        const filtered = coupons.filter((coupon) => {
            const matchesSearch = !query || (String(coupon.code) + ' ' + String(coupon.description || '')).toLowerCase().includes(query);
            return matchesSearch && (state === 'all' || coupon.computed_status === state);
        });
        tbody.innerHTML = filtered.map((coupon) => {
            const limit = coupon.usage_limit === null ? 'Sin límite' : Number(coupon.usage_count) + ' / ' + Number(coupon.usage_limit);
            const status = coupon.computed_status || 'inactive';
            const validity = escapeHtml(formatDate(coupon.starts_at)) + '<br><small>Hasta: ' + escapeHtml(formatDate(coupon.expires_at)) + '</small>';
            return '<tr><td><strong class="coupon-code">' + escapeHtml(coupon.code) + '</strong><small class="d-block text-muted">' + escapeHtml(coupon.description || 'Sin descripción') + '</small></td>' +
                '<td><strong>' + escapeHtml(discountLabel(coupon)) + '</strong><small class="d-block text-muted">Mínimo: ' + escapeHtml(money(coupon.minimum_purchase)) + '</small></td>' +
                '<td>' + validity + '</td><td>' + escapeHtml(limit) + '<small class="d-block text-muted">' + Number(coupon.per_customer_limit) + ' por cliente</small></td>' +
                '<td><span class="coupon-status coupon-status-' + escapeHtml(status) + '">' + escapeHtml(statusLabel[status] || 'Inactivo') + '</span></td>' +
                '<td class="text-end"><div class="btn-group btn-group-sm"><button class="btn btn-light" data-action="edit" data-id="' + Number(coupon.id) + '" title="Editar"><i class="fa-solid fa-pen"></i></button>' +
                '<button class="btn btn-light" data-action="toggle" data-id="' + Number(coupon.id) + '" data-active="' + (Number(coupon.is_active) === 1 ? 0 : 1) + '" title="' + (Number(coupon.is_active) === 1 ? 'Desactivar' : 'Activar') + '"><i class="fa-solid ' + (Number(coupon.is_active) === 1 ? 'fa-toggle-on' : 'fa-toggle-off') + '"></i></button></div></td></tr>';
        }).join('');
        document.getElementById('couponsEmpty').classList.toggle('d-none', filtered.length > 0);
        if (!filtered.length) tbody.innerHTML = '';
    }
    async function loadCoupons() {
        tbody.innerHTML = '<tr><td colspan="6" class="text-center py-5 text-muted"><span class="spinner-border spinner-border-sm me-2"></span>Cargando...</td></tr>';
        try {
            const result = await request('/api/admin/coupons');
            coupons = Array.isArray(result.data) ? result.data : [];
            updateStats(); render();
        } catch (error) {
            tbody.innerHTML = '<tr><td colspan="6" class="text-center text-danger py-5">' + escapeHtml(error.message) + '</td></tr>';
        }
    }
    function openCreate() {
        form.reset();
        document.getElementById('couponId').value = '';
        document.getElementById('couponIsActive').checked = true;
        document.getElementById('couponPerCustomer').value = '1';
        document.getElementById('couponMinimum').value = '0';
        document.getElementById('couponModalTitle').textContent = 'Crear cupón';
        modal && modal.show();
    }
    function openEdit(id) {
        const c = coupons.find((item) => Number(item.id) === Number(id));
        if (!c) return;
        form.reset();
        document.getElementById('couponId').value = c.id;
        document.getElementById('couponCode').value = c.code || '';
        document.getElementById('couponDescription').value = c.description || '';
        document.getElementById('couponDiscountType').value = c.discount_type;
        document.getElementById('couponDiscountValue').value = c.discount_value;
        document.getElementById('couponMinimum').value = c.minimum_purchase || 0;
        document.getElementById('couponUsageLimit').value = c.usage_limit ?? '';
        document.getElementById('couponPerCustomer').value = c.per_customer_limit || 1;
        document.getElementById('couponStartsAt').value = c.starts_at ? String(c.starts_at).replace(' ','T').slice(0,16) : '';
        document.getElementById('couponExpiresAt').value = c.expires_at ? String(c.expires_at).replace(' ','T').slice(0,16) : '';
        document.getElementById('couponIsActive').checked = Number(c.is_active) === 1;
        document.getElementById('couponModalTitle').textContent = 'Editar cupón';
        modal && modal.show();
    }
    document.getElementById('btnCreateCoupon').addEventListener('click', openCreate);
    search.addEventListener('input', render);
    statusFilter.addEventListener('change', render);
    tbody.addEventListener('click', async (event) => {
        const button = event.target.closest('button[data-action]');
        if (!button) return;
        const id = Number(button.dataset.id);
        if (button.dataset.action === 'edit') { openEdit(id); return; }
        if (button.dataset.action === 'toggle') {
            const active = Number(button.dataset.active);
            const result = await Swal.fire({title:active ? '¿Activar cupón?' : '¿Desactivar cupón?',text:active ? 'El código volverá a estar disponible si cumple sus condiciones.' : 'Los clientes ya no podrán aplicar este código.',icon:'question',showCancelButton:true,confirmButtonText:active ? 'Activar' : 'Desactivar',cancelButtonText:'Cancelar'});
            if (!result.isConfirmed) return;
            try {
                const response = await request('/api/admin/coupons/toggle',{method:'POST',body:JSON.stringify({id,is_active:active})});
                toast(response.message || 'Estado actualizado.'); await loadCoupons();
            } catch (error) { toast(error.message,'error'); }
        }
    });
    form.addEventListener('submit', async (event) => {
        event.preventDefault();
        if (!form.reportValidity()) return;
        saveButton.disabled = true;
        saveButton.innerHTML = '<span class="spinner-border spinner-border-sm me-2"></span>Guardando...';
        const data = Object.fromEntries(new FormData(form).entries());
        data.is_active = document.getElementById('couponIsActive').checked ? 1 : 0;
        data.usage_limit = data.usage_limit === '' ? null : Number(data.usage_limit);
        data.discount_value = Number(data.discount_value);
        data.minimum_purchase = Number(data.minimum_purchase || 0);
        data.per_customer_limit = Number(data.per_customer_limit || 1);
        const path = data.id ? '/api/admin/coupons/update' : '/api/admin/coupons/store';
        try {
            const response = await request(path,{method:'POST',body:JSON.stringify(data)});
            modal && modal.hide(); toast(response.message || 'Cupón guardado.'); await loadCoupons();
        } catch (error) { toast(error.message,'error'); }
        finally { saveButton.disabled = false; saveButton.innerHTML = '<i class="fa-solid fa-floppy-disk me-2"></i>Guardar cupón'; }
    });
    loadCoupons();
})();
