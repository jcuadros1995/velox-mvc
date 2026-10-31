<?php
$user = Auth::user() ?? [];
$role = strtolower((string) ($user['role_name'] ?? ''));
$isSuperAdmin = $role === 'superadmin';
$scopeName = $isSuperAdmin ? 'Todas las sedes' : ($user['branch_name'] ?? 'Sede no asignada');
?>
<!-- app/Views/admin/dashboard.php -->

<div class="dashboard-page">

    <!-- ============================================================
         HEADER DEL DASHBOARD
    ============================================================= -->

    <section class="dashboard-header">

        <div class="dashboard-header-left">

            <div class="dashboard-eyebrow">
                <span class="live-dot"></span>
                PANEL DE CONTROL
            </div>

            <h1>
                Resumen general
            </h1>

            <p>
                Visualiza el rendimiento de tu operación
                y toma decisiones basadas en datos.
            </p>

            <span class="dashboard-scope-badge">
                <i class="fa-solid fa-location-dot"></i>
                <?= htmlspecialchars($scopeName, ENT_QUOTES, 'UTF-8') ?>
            </span>

        </div>

        <div class="dashboard-header-actions">

            <button
                    type="button"
                    class="dashboard-action-btn"
                    id="btnRefreshDashboard"
                    title="Actualizar información"
            >
                <i class="fa-solid fa-arrows-rotate"></i>
            </button>

            <button
                    type="button"
                    class="dashboard-date-btn"
                    id="dashboardDateBtn"
            >
                <i class="fa-regular fa-calendar"></i>

                <span id="dashboardDateText">
                    Este mes
                </span>

                <i class="fa-solid fa-chevron-down"></i>
            </button>

        </div>

    </section>


    <!-- ============================================================
         FILTROS
    ============================================================= -->

    <section class="dashboard-filter-card">

        <div class="filter-title">

            <div class="filter-title-icon">
                <i class="fa-solid fa-sliders"></i>
            </div>

            <div>
                <strong>Filtros del reporte</strong>
                <span>Personaliza el período de análisis</span>
            </div>

        </div>


        <div class="dashboard-filters">

            <?php if ($isSuperAdmin): ?>
                <div class="dashboard-filter-group">
                    <label for="filterBranch">Sede</label>
                    <select class="dashboard-select" id="filterBranch">
                        <option value="">Todas las sedes</option>
                        <?php foreach (($adminBranches ?? []) as $branch): ?>
                            <option value="<?= (int) $branch['id'] ?>">
                                <?= htmlspecialchars($branch['name'], ENT_QUOTES, 'UTF-8') ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>
            <?php endif; ?>

            <div class="dashboard-filter-group">

                <label>
                    Período
                </label>

                <select
                        class="dashboard-select"
                        id="filterPeriod"
                >
                    <option value="day">
                        Hoy
                    </option>

                    <option value="week">
                        Esta semana
                    </option>

                    <option value="month" selected>
                        Este mes
                    </option>

                    <option value="year">
                        Este año
                    </option>
                </select>

            </div>


            <div class="dashboard-filter-group">

                <label>
                    Fecha inicio
                </label>

                <input
                        type="date"
                        class="dashboard-input"
                        id="filterFrom"
                >

            </div>


            <div class="dashboard-filter-group">

                <label>
                    Fecha fin
                </label>

                <input
                        type="date"
                        class="dashboard-input"
                        id="filterTo"
                >

            </div>


            <button
                    type="button"
                    class="dashboard-filter-btn"
                    id="btnApplyFilters"
            >
                <i class="fa-solid fa-filter"></i>
                Aplicar filtros
            </button>

        </div>

    </section>


    <!-- ============================================================
         KPI CARDS
    ============================================================= -->

    <section class="dashboard-kpis">


        <!-- VENTAS -->

        <div
                class="dashboard-kpi-card kpi-purple"
                data-dashboard-animate
        >

            <div class="kpi-top">

                <div class="kpi-icon">
                    <i class="fa-solid fa-chart-line"></i>
                </div>

                <span class="kpi-period">
                    Este mes
                </span>

            </div>


            <div class="kpi-label">
                Ingresos
            </div>

            <div
                    class="kpi-value"
                    data-counter="1250000"
                    data-prefix="$ "
            >
                $ 0
            </div>


            <div class="kpi-bottom">

                <span class="kpi-growth positive">
                    <i class="fa-solid fa-arrow-trend-up"></i>
                    +18.4%
                </span>

                <span>
                    vs. mes anterior
                </span>

            </div>


            <div class="kpi-decoration">
                <i class="fa-solid fa-chart-line"></i>
            </div>

        </div>


        <!-- PEDIDOS -->

        <div
                class="dashboard-kpi-card kpi-blue"
                data-dashboard-animate
        >

            <div class="kpi-top">

                <div class="kpi-icon">
                    <i class="fa-solid fa-bag-shopping"></i>
                </div>

                <span class="kpi-period">
                    Este mes
                </span>

            </div>


            <div class="kpi-label">
                Pedidos
            </div>

            <div
                    class="kpi-value"
                    data-counter="328"
            >
                0
            </div>


            <div class="kpi-bottom">

                <span class="kpi-growth positive">
                    <i class="fa-solid fa-arrow-trend-up"></i>
                    +12.8%
                </span>

                <span>
                    vs. mes anterior
                </span>

            </div>


            <div class="kpi-decoration">
                <i class="fa-solid fa-bag-shopping"></i>
            </div>

        </div>


        <!-- CLIENTES -->

        <div
                class="dashboard-kpi-card kpi-green"
                data-dashboard-animate
        >

            <div class="kpi-top">

                <div class="kpi-icon">
                    <i class="fa-solid fa-users"></i>
                </div>

                <span class="kpi-period">
                    Total
                </span>

            </div>


            <div class="kpi-label">
                Clientes
            </div>

            <div
                    class="kpi-value"
                    data-counter="1248"
            >
                0
            </div>


            <div class="kpi-bottom">

                <span class="kpi-growth positive">
                    <i class="fa-solid fa-arrow-trend-up"></i>
                    +8.6%
                </span>

                <span>
                    crecimiento
                </span>

            </div>


            <div class="kpi-decoration">
                <i class="fa-solid fa-users"></i>
            </div>

        </div>


        <!-- PRODUCTOS -->

        <div
                class="dashboard-kpi-card kpi-orange"
                data-dashboard-animate
        >

            <div class="kpi-top">

                <div class="kpi-icon">
                    <i class="fa-solid fa-boxes-stacked"></i>
                </div>

                <span class="kpi-period">
                    Inventario
                </span>

            </div>


            <div class="kpi-label">
                Productos
            </div>

            <div
                    class="kpi-value"
                    data-counter="24"
                    data-kpi="products"
            >
                0
            </div>


            <div class="kpi-bottom">

                <span class="kpi-growth negative">
                    <i class="fa-solid fa-arrow-trend-down"></i>
                    <b data-kpi="low-stock">0</b> críticos
                </span>

                <span>
                    requieren atención
                </span>

            </div>


            <div class="kpi-decoration">
                <i class="fa-solid fa-boxes-stacked"></i>
            </div>

        </div>

    </section>


    <!-- ============================================================
         MAIN CHART + PERFORMANCE
    ============================================================= -->

    <section class="dashboard-grid-main">


        <!-- INGRESOS -->

        <div class="dashboard-panel dashboard-chart-large">

            <div class="panel-header">

                <div>

                    <span class="panel-eyebrow">
                        RENDIMIENTO
                    </span>

                    <h3>
                        Ingresos y ventas
                    </h3>

                    <p>
                        Evolución de tus ingresos durante el período seleccionado.
                    </p>

                </div>


                <div class="chart-legend">

                    <span>
                        <i class="legend-dot revenue"></i>
                        Ingresos
                    </span>

                    <span>
                        <i class="legend-dot orders"></i>
                        Pedidos
                    </span>

                </div>

            </div>


            <div class="chart-container-large">
                <canvas id="revenueChart"></canvas>
            </div>

        </div>


        <!-- PERFORMANCE -->

        <div class="dashboard-panel performance-panel">

            <div class="panel-header">

                <div>

                    <span class="panel-eyebrow">
                        PERFORMANCE
                    </span>

                    <h3>
                        Rendimiento
                    </h3>

                </div>

                <button
                        type="button"
                        class="panel-more-btn"
                >
                    <i class="fa-solid fa-ellipsis"></i>
                </button>

            </div>


            <div class="performance-score">

                <div class="score-circle">

                    <div class="score-inner">

                        <strong>
                            86%
                        </strong>

                        <span>
                            Excelente
                        </span>

                    </div>

                </div>

            </div>


            <div class="performance-items">

                <div class="performance-item">

                    <div class="performance-item-info">

                        <span>
                            Conversión
                        </span>

                        <strong>
                            72%
                        </strong>

                    </div>

                    <div class="performance-progress">

                        <span
                                style="width:72%"
                        ></span>

                    </div>

                </div>


                <div class="performance-item">

                    <div class="performance-item-info">

                        <span>
                            Satisfacción
                        </span>

                        <strong>
                            94%
                        </strong>

                    </div>

                    <div class="performance-progress">

                        <span
                                style="width:94%"
                        ></span>

                    </div>

                </div>


                <div class="performance-item">

                    <div class="performance-item-info">

                        <span>
                            Cumplimiento
                        </span>

                        <strong>
                            82%
                        </strong>

                    </div>

                    <div class="performance-progress">

                        <span
                                style="width:82%"
                        ></span>

                    </div>

                </div>

            </div>

        </div>

    </section>


    <!-- ============================================================
         SECOND ROW CHARTS
    ============================================================= -->

    <section class="dashboard-grid-charts">


        <!-- VENTAS POR DÍA -->

        <div class="dashboard-panel">

            <div class="panel-header">

                <div>

                    <span class="panel-eyebrow">
                        ACTIVIDAD
                    </span>

                    <h3>
                        Ventas por día
                    </h3>

                </div>

                <span class="panel-badge success">
                    +18.4%
                </span>

            </div>

            <div class="chart-container-medium">
                <canvas id="salesBarChart"></canvas>
            </div>

        </div>


        <!-- CATEGORÍAS -->

        <div class="dashboard-panel">

            <div class="panel-header">

                <div>

                    <span class="panel-eyebrow">
                        INVENTARIO
                    </span>

                    <h3>
                        Productos por categoría
                    </h3>

                </div>

            </div>


            <div class="category-chart-wrapper">

                <div class="category-doughnut">

                    <canvas
                            id="categoryChart"
                    ></canvas>

                </div>


                <div class="category-list">

                    <div class="category-item">

                        <span>
                            <i class="category-dot dot-1"></i>
                            Tecnología
                        </span>

                        <strong>
                            35%
                        </strong>

                    </div>


                    <div class="category-item">

                        <span>
                            <i class="category-dot dot-2"></i>
                            Hogar
                        </span>

                        <strong>
                            25%
                        </strong>

                    </div>


                    <div class="category-item">

                        <span>
                            <i class="category-dot dot-3"></i>
                            Oficina
                        </span>

                        <strong>
                            20%
                        </strong>

                    </div>


                    <div class="category-item">

                        <span>
                            <i class="category-dot dot-4"></i>
                            Otros
                        </span>

                        <strong>
                            20%
                        </strong>

                    </div>

                </div>

            </div>

        </div>


        <!-- OBJETIVO -->

        <div class="dashboard-panel goal-panel">

            <div class="panel-header">

                <div>

                    <span class="panel-eyebrow">
                        OBJETIVO MENSUAL
                    </span>

                    <h3>
                        Meta de ventas
                    </h3>

                </div>

                <i class="fa-solid fa-bullseye goal-icon"></i>

            </div>


            <div class="goal-value">

                <strong>
                    $ 1.250.000
                </strong>

                <span>
                    de $ 1.500.000
                </span>

            </div>


            <div class="goal-progress">

                <div
                        class="goal-progress-bar"
                        style="width:83%"
                ></div>

            </div>


            <div class="goal-footer">

                <span>
                    83% completado
                </span>

                <strong>
                    $ 250.000 restantes
                </strong>

            </div>


            <div class="goal-message">

                <i class="fa-solid fa-sparkles"></i>

                <span>
                    ¡Vas por muy buen camino!
                </span>

            </div>

        </div>

    </section>


    <!-- ============================================================
         BOTTOM GRID
    ============================================================= -->

    <section class="dashboard-grid-bottom">


        <!-- PEDIDOS RECIENTES -->

        <div class="dashboard-panel recent-orders-panel">

            <div class="panel-header">

                <div>

                    <span class="panel-eyebrow">
                        OPERACIÓN
                    </span>

                    <h3>
                        Pedidos recientes
                    </h3>

                </div>

                <a
                        href="<?= APP_URL ?>/admin/orders"
                        class="panel-link"
                >
                    Ver todos
                    <i class="fa-solid fa-arrow-right"></i>
                </a>

            </div>


            <div class="orders-table-wrapper">

                <table class="dashboard-orders-table">

                    <thead>

                    <tr>

                        <th>
                            Pedido
                        </th>

                        <th>
                            Cliente
                        </th>

                        <th>
                            Fecha
                        </th>

                        <th>
                            Estado
                        </th>

                        <th class="text-end">
                            Total
                        </th>

                    </tr>

                    </thead>


                    <tbody>

                    <tr>

                        <td>
                                <span class="order-id">
                                    #VX-1024
                                </span>
                        </td>

                        <td>

                            <div class="customer-cell">

                                <div class="customer-avatar">
                                    JP
                                </div>

                                <span>
                                        Juan Pérez
                                    </span>

                            </div>

                        </td>

                        <td>
                            Hoy, 10:42
                        </td>

                        <td>
                                <span class="status-badge completed">
                                    Completado
                                </span>
                        </td>

                        <td class="text-end fw-bold">
                            $ 185.000
                        </td>

                    </tr>


                    <tr>

                        <td>
                                <span class="order-id">
                                    #VX-1023
                                </span>
                        </td>

                        <td>

                            <div class="customer-cell">

                                <div class="customer-avatar avatar-blue">
                                    MC
                                </div>

                                <span>
                                        María Castro
                                    </span>

                            </div>

                        </td>

                        <td>
                            Hoy, 09:28
                        </td>

                        <td>
                                <span class="status-badge pending">
                                    Pendiente
                                </span>
                        </td>

                        <td class="text-end fw-bold">
                            $ 320.000
                        </td>

                    </tr>


                    <tr>

                        <td>
                                <span class="order-id">
                                    #VX-1022
                                </span>
                        </td>

                        <td>

                            <div class="customer-cell">

                                <div class="customer-avatar avatar-orange">
                                    AL
                                </div>

                                <span>
                                        Andrés López
                                    </span>

                            </div>

                        </td>

                        <td>
                            Ayer, 16:20
                        </td>

                        <td>
                                <span class="status-badge completed">
                                    Completado
                                </span>
                        </td>

                        <td class="text-end fw-bold">
                            $ 95.000
                        </td>

                    </tr>


                    <tr>

                        <td>
                                <span class="order-id">
                                    #VX-1021
                                </span>
                        </td>

                        <td>

                            <div class="customer-cell">

                                <div class="customer-avatar avatar-green">
                                    SG
                                </div>

                                <span>
                                        Sofía Gómez
                                    </span>

                            </div>

                        </td>

                        <td>
                            Ayer, 14:12
                        </td>

                        <td>
                                <span class="status-badge cancelled">
                                    Cancelado
                                </span>
                        </td>

                        <td class="text-end fw-bold">
                            $ 75.000
                        </td>

                    </tr>

                    </tbody>

                </table>

            </div>

        </div>


        <!-- STOCK CRÍTICO -->

        <div class="dashboard-panel stock-panel">

            <div class="panel-header">

                <div>

                    <span class="panel-eyebrow">
                        INVENTARIO
                    </span>

                    <h3>
                        Stock crítico
                    </h3>

                </div>

                <span class="critical-count" id="dashboardLowStockCount">0</span>

            </div>


            <div class="stock-list" id="dashboardLowStockList">
                <div class="stock-empty" role="status">
                    <i class="fa-solid fa-circle-notch fa-spin"></i>
                    <span>Cargando inventario...</span>
                </div>
            </div>


            <a
                    href="<?= APP_URL ?>/admin/items"
                    class="stock-action"
            >
                Revisar inventario
                <i class="fa-solid fa-arrow-right"></i>
            </a>

        </div>

    </section>


    <!-- ============================================================
         ACTIVIDAD
    ============================================================= -->

    <section class="dashboard-panel activity-panel">

        <div class="panel-header">

            <div>

                <span class="panel-eyebrow">
                    SISTEMA
                </span>

                <h3>
                    Actividad reciente
                </h3>

            </div>

            <span class="system-online">
                <span></span>
                Sistema operativo
            </span>

        </div>


        <div class="activity-timeline">


            <div class="activity-item">

                <div class="activity-icon success">
                    <i class="fa-solid fa-check"></i>
                </div>

                <div class="activity-content">

                    <strong>
                        Pedido #VX-1024 completado
                    </strong>

                    <span>
                        Juan Pérez realizó una compra por $185.000
                    </span>

                </div>

                <time>
                    Hace 8 min
                </time>

            </div>


            <div class="activity-item">

                <div class="activity-icon primary">
                    <i class="fa-solid fa-user-plus"></i>
                </div>

                <div class="activity-content">

                    <strong>
                        Nuevo cliente registrado
                    </strong>

                    <span>
                        Sofía Gómez se registró en la plataforma
                    </span>

                </div>

                <time>
                    Hace 24 min
                </time>

            </div>


            <div class="activity-item">

                <div class="activity-icon warning">
                    <i class="fa-solid fa-triangle-exclamation"></i>
                </div>

                <div class="activity-content">

                    <strong>
                        Stock bajo detectado
                    </strong>

                    <span>
                        Laptop Pro tiene solamente 2 unidades
                    </span>

                </div>

                <time>
                    Hace 42 min
                </time>

            </div>


            <div class="activity-item">

                <div class="activity-icon info">
                    <i class="fa-solid fa-gear"></i>
                </div>

                <div class="activity-content">

                    <strong>
                        Configuración actualizada
                    </strong>

                    <span>
                        Se modificaron parámetros del sistema
                    </span>

                </div>

                <time>
                    Hace 1 h
                </time>

            </div>


        </div>

    </section>

</div>


<!-- ================================================================
     CHART.JS
================================================================ -->

<script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.4/dist/chart.umd.min.js"></script>
