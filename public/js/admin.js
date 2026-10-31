/**
 * VELOX MVC
 * Premium Admin Dashboard
 */

function updateDashboardKpis(metrics) {

    if (!metrics) {
        return;
    }

    const revenueElement =
        document.querySelector('[data-kpi="revenue"]');

    const ordersElement =
        document.querySelector('[data-kpi="orders"]');

    const customersElement =
        document.querySelector('[data-kpi="customers"]');

    const productsElement =
        document.querySelector('[data-kpi="products"]');


    if (revenueElement) {
        revenueElement.textContent =
            '$ ' + Number(metrics.total_sales || 0)
                .toLocaleString('es-CO');
    }

    if (ordersElement) {
        ordersElement.textContent =
            Number(metrics.order_count || 0)
                .toLocaleString('es-CO');
    }

    if (customersElement) {
        customersElement.textContent =
            Number(metrics.customer_count || 0)
                .toLocaleString('es-CO');
    }

    if (productsElement) {
        productsElement.textContent =
            Number(metrics.product_count || 0)
                .toLocaleString('es-CO');
    }

    const lowStockValue = Number(metrics.low_stock_count || 0)
        .toLocaleString('es-CO');

    document.querySelectorAll('[data-kpi="low-stock"]').forEach(element => {
        element.textContent = lowStockValue;
    });
}


/**
 * Renderiza los productos por debajo del mínimo de inventario.
 * La API existente entrega el listado y el contador por sede.
 */
function renderDashboardLowStock(metrics) {
    const container = document.getElementById('dashboardLowStockList');
    const counter = document.getElementById('dashboardLowStockCount');

    if (!container) return;

    const items = Array.isArray(metrics?.low_stock) ? metrics.low_stock : [];
    const rawCount = Number(metrics?.low_stock_count);
    const count = Number.isFinite(rawCount) ? rawCount : items.length;

    if (counter) {
        counter.textContent = count.toLocaleString('es-CO');
    }

    if (items.length === 0) {
        container.innerHTML = `
            <div class="stock-empty" role="status">
                <i class="fa-solid fa-circle-check"></i>
                <span>Inventario saludable. No hay productos por debajo del mínimo.</span>
            </div>
        `;
        return;
    }

    const escapeHtml = value => String(value ?? '').replace(/[&<>"']/g, character => ({
        '&': '&amp;',
        '<': '&lt;',
        '>': '&gt;',
        '"': '&quot;',
        "'": '&#039;'
    })[character]);

    container.innerHTML = items.map(item => {
        const stock = Number(item.stock_quantity || 0);
        const minimum = Number(item.min_stock_alert || 0);
        const quantityClass = stock <= 0 ? 'critical' : 'warning';
        const branchName = item.branch_name || 'Sede sin identificar';

        return `
            <div class="stock-item">
                <div class="stock-product-icon">
                    <i class="fa-solid fa-box-open"></i>
                </div>
                <div class="stock-product">
                    <strong>${escapeHtml(item.name || 'Producto sin nombre')}</strong>
                    <span>${escapeHtml(branchName)} · mínimo ${minimum.toLocaleString('es-CO')} uds.</span>
                </div>
                <div class="stock-quantity ${quantityClass}">
                    ${Math.max(0, stock).toLocaleString('es-CO')} uds.
                </div>
            </div>
        `;
    }).join('');
}

async function loadDashboardMetrics(from, to, branchId = '') {

    try {

        const params = new URLSearchParams({
            from: from,
            to: to
        });

        if (branchId) {
            params.set('branch_id', branchId);
        }

        const response = await fetch(
            `${window.APP_URL}/api/metrics/sales?${params.toString()}`,
            {
                method: 'GET',
                headers: {
                    'Accept': 'application/json'
                },
                credentials: 'same-origin'
            }
        );

        const result = await response.json();

        if (!response.ok || !result.success) {
            throw new Error(
                result.message ||
                'No fue posible cargar las métricas.'
            );
        }

        return result.data;

    } catch (error) {

        console.error(
            'Error cargando métricas:',
            error
        );

        if (typeof window.showToast === 'function') {
            window.showToast(
                'error',
                'No fue posible cargar las métricas del dashboard.'
            );
        }

        return null;
    }
}

async function refreshDashboardMetrics(from, to, branchId = '') {

    const metrics = await loadDashboardMetrics(
        from,
        to,
        branchId
    );

    if (!metrics) {
        return false;
    }

    updateDashboardKpis(metrics);
    renderDashboardLowStock(metrics);

    initRevenueChart(metrics);
    initSalesChart(metrics);
    initCategoryChart(metrics);

    return true;
}

document.addEventListener('DOMContentLoaded', async function () {

    initDashboardCounters();

    initDashboardFilters();

    initDashboardRefresh();

    initLogoutHandler();

    const fromInput = document.getElementById('filterFrom');
    const toInput = document.getElementById('filterTo');
    const branchInput = document.getElementById('filterBranch');

    if (fromInput && toInput) {

        const from = fromInput.value;
        const to = toInput.value;
        const branchId = branchInput ? branchInput.value : '';

        const metrics = await loadDashboardMetrics(
            from,
            to,
            branchId
        );

        if (metrics) {

            updateDashboardKpis(metrics);
            renderDashboardLowStock(metrics);
            initRevenueChart(metrics);
            initSalesChart(metrics);
            initCategoryChart(metrics);
        }
    }

});


/* ========================================================================
   CONTADORES
======================================================================== */

function initDashboardCounters() {

    const counters =
        document.querySelectorAll(
            '.dashboard-page [data-counter]'
        );


    counters.forEach(function (element) {

        const target =
            Number(element.dataset.counter || 0);

        const prefix =
            element.dataset.prefix || '';

        const duration = 1400;

        const startTime =
            performance.now();


        function animate(currentTime) {

            const elapsed =
                currentTime - startTime;

            const progress =
                Math.min(
                    elapsed / duration,
                    1
                );


            const eased =
                1 - Math.pow(
                    1 - progress,
                    4
                );


            const value =
                target * eased;


            if (target >= 10000) {

                element.textContent =
                    prefix +
                    Math.floor(value)
                        .toLocaleString('es-CO');

            } else {

                element.textContent =
                    prefix +
                    Math.floor(value)
                        .toLocaleString('es-CO');

            }


            if (progress < 1) {

                requestAnimationFrame(
                    animate
                );

            } else {

                element.textContent =
                    prefix +
                    target.toLocaleString('es-CO');

            }

        }


        requestAnimationFrame(
            animate
        );

    });

}


/* ========================================================================
   CONFIGURACIÓN GLOBAL CHART.JS
======================================================================== */

function configureChartDefaults() {

    if (typeof Chart === 'undefined') {
        return;
    }


    Chart.defaults.font.family =
        'var(--velox-font, Inter), system-ui, sans-serif';

    Chart.defaults.font.size = 10;

    Chart.defaults.color =
        '#94a3b8';

    Chart.defaults.animation.duration =
        1200;

    Chart.defaults.animation.easing =
        'easeOutQuart';

}


/* ========================================================================
   GRÁFICO PRINCIPAL
======================================================================== */
let revenueChartInstance = null;
let salesChartInstance = null;
let categoryChartInstance = null;
function initRevenueChart(metrics) {

    const canvas = document.getElementById('revenueChart');

    if (!canvas) {
        return;
    }

    if (revenueChartInstance) {
        revenueChartInstance.destroy();
    }

    const labels =
        (metrics.sales_by_day || [])
            .map(item => item.sale_date);

    const values =
        (metrics.sales_by_day || [])
            .map(item => Number(item.total_sales));

    revenueChartInstance = new Chart(canvas, {
        type: 'line',
        data: {
            labels: labels,
            datasets: [{
                label: 'Ingresos',
                data: values,
                fill: true,
                tension: 0.4
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            plugins: {
                legend: {
                    display: false
                }
            },
            scales: {
                y: {
                    beginAtZero: true,
                    ticks: {
                        callback: function(value) {
                            return '$ ' + Number(value).toLocaleString('es-CO');
                        }
                    }
                }
            }
        }
    });
}


/* ========================================================================
   BARRAS DE VENTAS
======================================================================== */

function initSalesChart(metrics) {

    const canvas =
        document.getElementById('salesBarChart');

    if (!canvas) {
        return;
    }

    if (salesChartInstance) {
        salesChartInstance.destroy();
    }

    const labels =
        (metrics.sales_by_day || [])
            .map(item => item.sale_date);

    const values =
        (metrics.sales_by_day || [])
            .map(item => Number(item.order_count));

    salesChartInstance = new Chart(canvas, {
        type: 'bar',
        data: {
            labels: labels,
            datasets: [{
                label: 'Pedidos',
                data: values
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            plugins: {
                legend: {
                    display: false
                }
            },
            scales: {
                y: {
                    beginAtZero: true,
                    ticks: {
                        precision: 0
                    }
                }
            }
        }
    });
}


/* ========================================================================
   DOUGHNUT
======================================================================== */

function initCategoryChart(metrics) {

    const canvas =
        document.getElementById('categoryChart');

    if (!canvas) {
        return;
    }

    if (categoryChartInstance) {
        categoryChartInstance.destroy();
    }

    const labels =
        (metrics.sales_by_category || [])
            .map(item => item.category_name);

    const values =
        (metrics.sales_by_category || [])
            .map(item => Number(item.total_sales));

    categoryChartInstance = new Chart(canvas, {
        type: 'doughnut',
        data: {
            labels: labels,
            datasets: [{
                data: values
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            plugins: {
                legend: {
                    position: 'bottom'
                }
            }
        }
    });
}


/* ========================================================================
   FILTROS
======================================================================== */

function initDashboardFilters() {

    const period =
        document.getElementById('filterPeriod');

    const dateText =
        document.getElementById('dashboardDateText');

    const button =
        document.getElementById('btnApplyFilters');

    const fromInput =
        document.getElementById('filterFrom');

    const toInput =
        document.getElementById('filterTo');


    if (period && dateText) {

        period.addEventListener('change', function () {

            const labels = {
                day: 'Hoy',
                week: 'Esta semana',
                month: 'Este mes',
                year: 'Este año'
            };

            dateText.textContent =
                labels[this.value] ||
                'Personalizado';
        });
    }


    if (!button) {
        return;
    }


    button.addEventListener('click', async function () {

        const from = fromInput ? fromInput.value : '';
        const to = toInput ? toInput.value : '';
        const branchInput = document.getElementById('filterBranch');
        const branchId = branchInput ? branchInput.value : '';


        if (!from || !to) {

            if (typeof window.showToast === 'function') {
                window.showToast(
                    'error',
                    'Selecciona las fechas del período.'
                );
            }

            return;
        }


        if (from > to) {

            if (typeof window.showToast === 'function') {
                window.showToast(
                    'error',
                    'La fecha inicial no puede ser mayor que la fecha final.'
                );
            }

            return;
        }


        button.disabled = true;

        const original =
            button.innerHTML;

        button.innerHTML = `
            <i class="fa-solid fa-circle-notch fa-spin"></i>
            Aplicando...
        `;


        try {

            const success =
                await refreshDashboardMetrics(
                    from,
                    to,
                    branchId
                );


            if (success) {

                if (typeof window.showToast === 'function') {

                    window.showToast(
                        'success',
                        'Filtros aplicados correctamente.'
                    );
                }

            }

        } finally {

            button.disabled = false;

            button.innerHTML = original;
        }

    });
}


/* ========================================================================
   REFRESH
======================================================================== */

function initDashboardRefresh() {

    const button =
        document.getElementById(
            'btnRefreshDashboard'
        );

    if (!button) {
        return;
    }


    button.addEventListener(
        'click',
        async function () {

            if (
                button.classList.contains(
                    'is-loading'
                )
            ) {
                return;
            }


            const fromInput =
                document.getElementById(
                    'filterFrom'
                );

            const toInput =
                document.getElementById(
                    'filterTo'
                );


            const from =
                fromInput
                    ? fromInput.value
                    : '';

            const to =
                toInput
                    ? toInput.value
                    : '';

            const branchInput =
                document.getElementById('filterBranch');

            const branchId =
                branchInput
                    ? branchInput.value
                    : '';


            if (!from || !to) {
                return;
            }


            button.classList.add(
                'is-loading'
            );


            try {

                const success =
                    await refreshDashboardMetrics(
                        from,
                        to,
                        branchId
                    );


                if (
                    success &&
                    typeof window.showToast ===
                    'function'
                ) {

                    window.showToast(
                        'success',
                        'Dashboard actualizado.'
                    );
                }

            } finally {

                button.classList.remove(
                    'is-loading'
                );
            }

        }
    );
}

/* ========================================================================
   LOGOUT
======================================================================== */

function initLogoutHandler() {

    const btnLogout =
        document.getElementById('btnLogout');

    if (!btnLogout) {
        return;
    }

    btnLogout.addEventListener(
        'click',
        function (event) {

            event.preventDefault();

            fetch(
                `${window.APP_URL}/api/logout`,
                {
                    method: 'POST'
                }
            )
                .then(async response => {

                    if (!response.ok) {
                        throw new Error(
                            'No fue posible cerrar la sesión.'
                        );
                    }

                    if (
                        typeof window.showToast ===
                        'function'
                    ) {
                        window.showToast(
                            'success',
                            'Sesión cerrada correctamente'
                        );
                    }

                    setTimeout(
                        function () {
                            window.location.href =
                                `${window.APP_URL}/login`;
                        },
                        600
                    );
                })
                .catch(error => {

                    console.error(
                        'Error cerrando sesión:',
                        error
                    );

                    if (
                        typeof window.showToast ===
                        'function'
                    ) {
                        window.showToast(
                            'error',
                            'No fue posible cerrar la sesión.'
                        );
                    }
                });
        }
    );
}