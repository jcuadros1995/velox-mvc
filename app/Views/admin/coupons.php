<?php
$pageHeaderIcon = 'fa-solid fa-ticket';
$pageHeaderEyebrow = 'VENTAS';
$pageHeaderTitle = 'Cupones y promociones';
$pageHeaderDescription = 'Crea y administra códigos promocionales para los pedidos realizados desde la tienda.';
$pageHeaderBadge = 'Marketing';
require __DIR__ . '/components/page-header.php';
?>
<div class="admin-coupons-page" id="adminCouponsPage">
    <section class="coupon-stats">
        <article class="coupon-stat-card"><span class="coupon-stat-icon"><i class="fa-solid fa-ticket"></i></span><div><small>Total de cupones</small><strong id="couponStatTotal">0</strong></div></article>
        <article class="coupon-stat-card"><span class="coupon-stat-icon"><i class="fa-solid fa-circle-check"></i></span><div><small>Activos</small><strong id="couponStatActive">0</strong></div></article>
        <article class="coupon-stat-card"><span class="coupon-stat-icon"><i class="fa-solid fa-calendar-clock"></i></span><div><small>Programados</small><strong id="couponStatScheduled">0</strong></div></article>
        <article class="coupon-stat-card"><span class="coupon-stat-icon"><i class="fa-solid fa-chart-line"></i></span><div><small>Usos acumulados</small><strong id="couponStatUses">0</strong></div></article>
    </section>
    <section class="coupon-panel">
        <div class="coupon-panel-heading">
            <div><h2>Promociones</h2><p>Los descuentos se calculan y validan en el servidor al crear el pedido.</p></div>
            <button type="button" class="btn btn-primary" id="btnCreateCoupon"><i class="fa-solid fa-plus me-2"></i>Crear cupón</button>
        </div>
        <div class="coupon-toolbar">
            <div class="input-group coupon-search"><span class="input-group-text"><i class="fa-solid fa-search"></i></span><input type="search" class="form-control" id="couponSearch" placeholder="Buscar por código o descripción"></div>
            <select class="form-select coupon-status-filter" id="couponStatusFilter" aria-label="Filtrar por estado">
                <option value="all">Todos los estados</option><option value="active">Activos</option><option value="scheduled">Programados</option><option value="expired">Vencidos</option><option value="exhausted">Agotados</option><option value="inactive">Inactivos</option>
            </select>
        </div>
        <div class="table-responsive"><table class="table align-middle coupon-table">
            <thead><tr><th>Código</th><th>Descuento</th><th>Vigencia</th><th>Usos</th><th>Estado</th><th class="text-end">Acciones</th></tr></thead>
            <tbody id="couponsTableBody"><tr><td colspan="6" class="text-center py-5 text-muted">Cargando cupones...</td></tr></tbody>
        </table></div>
        <div class="coupon-empty d-none" id="couponsEmpty">No hay cupones que coincidan con los filtros.</div>
    </section>
</div>
<div class="modal fade" id="couponModal" tabindex="-1" aria-labelledby="couponModalTitle" aria-hidden="true">
    <div class="modal-dialog modal-lg modal-dialog-centered"><div class="modal-content coupon-modal">
        <form id="couponForm">
            <div class="modal-header"><div><h5 class="modal-title" id="couponModalTitle">Crear cupón</h5><p class="mb-0 text-muted small">Configura el descuento y las condiciones de uso.</p></div><button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Cerrar"></button></div>
            <div class="modal-body">
                <input type="hidden" id="couponId" name="id">
                <div class="row g-3">
                    <div class="col-md-5"><label class="form-label" for="couponCode">Código *</label><input class="form-control text-uppercase" id="couponCode" name="code" maxlength="40" required placeholder="EJ: BIENVENIDO10"><small class="form-text">Entre 3 y 40 caracteres, sin espacios.</small></div>
                    <div class="col-md-7"><label class="form-label" for="couponDescription">Descripción</label><input class="form-control" id="couponDescription" name="description" maxlength="255" placeholder="Promoción de bienvenida"></div>
                    <div class="col-md-6"><label class="form-label" for="couponDiscountType">Tipo de descuento *</label><select class="form-select" id="couponDiscountType" name="discount_type" required><option value="percentage">Porcentaje (%)</option><option value="fixed">Valor fijo (COP)</option></select></div>
                    <div class="col-md-6"><label class="form-label" for="couponDiscountValue">Valor del descuento *</label><input class="form-control" type="number" id="couponDiscountValue" name="discount_value" min="0.01" step="0.01" required></div>
                    <div class="col-md-6"><label class="form-label" for="couponMinimum">Compra mínima (COP)</label><input class="form-control" type="number" id="couponMinimum" name="minimum_purchase" min="0" step="1" value="0"></div>
                    <div class="col-md-6"><label class="form-label" for="couponUsageLimit">Límite total de usos</label><input class="form-control" type="number" id="couponUsageLimit" name="usage_limit" min="1" step="1" placeholder="Sin límite"></div>
                    <div class="col-md-6"><label class="form-label" for="couponPerCustomer">Usos máximos por cliente *</label><input class="form-control" type="number" id="couponPerCustomer" name="per_customer_limit" min="1" max="1000" value="1" required></div>
                    <div class="col-md-6"><label class="form-label" for="couponStartsAt">Válido desde</label><input class="form-control" type="datetime-local" id="couponStartsAt" name="starts_at"></div>
                    <div class="col-md-6"><label class="form-label" for="couponExpiresAt">Válido hasta</label><input class="form-control" type="datetime-local" id="couponExpiresAt" name="expires_at"></div>
                    <div class="col-12"><label class="coupon-active-toggle"><input type="checkbox" id="couponIsActive" name="is_active" value="1" checked><span><strong>Cupón activo</strong><small>Los clientes podrán aplicarlo cuando cumpla las condiciones configuradas.</small></span></label></div>
                </div>
            </div>
            <div class="modal-footer"><button type="button" class="btn btn-light" data-bs-dismiss="modal">Cancelar</button><button type="submit" class="btn btn-primary" id="btnSaveCoupon"><i class="fa-solid fa-floppy-disk me-2"></i>Guardar cupón</button></div>
        </form>
    </div></div>
</div>
