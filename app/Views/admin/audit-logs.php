<?php
$filters = $auditFilters ?? [];
$stats = $auditStats ?? ['total'=>0,'today'=>0,'users'=>0,'errors'=>0];
$options = $auditOptions ?? ['modules'=>[],'actions'=>[],'users'=>[]];
$logs = is_array($auditLogs ?? null) ? $auditLogs : [];
$pagination = $auditPagination ?? ['page'=>1,'pages'=>1,'total'=>0,'per_page'=>25];
$baseQuery = $filters;
unset($baseQuery['page']);
$makePageUrl = static function (int $page) use ($baseQuery): string {
    $query = http_build_query(array_merge($baseQuery, ['page' => $page]));
    return APP_URL . '/admin/audit-logs?' . $query;
};
$formatDate = static function ($value): string {
    try { return (new DateTime((string)$value))->format('d/m/Y H:i:s'); }
    catch (Throwable $e) { return (string)$value; }
};
?>
<div class="audit-page">
    <div class="audit-heading">
        <div>
            <div class="audit-eyebrow"><span></span> CONTROL Y TRAZABILIDAD</div>
            <h1>Auditoría del sistema</h1>
            <p>Consulta quién realizó cada acción, cuándo ocurrió y desde dónde se ejecutó.</p>
        </div>
        <div class="audit-heading-meta"><i class="fa-solid fa-shield-halved"></i><span>Registro de actividad</span></div>
    </div>

    <div class="audit-stats">
        <article class="audit-stat"><div class="audit-stat-icon violet"><i class="fa-solid fa-list-check"></i></div><div><span>Registros totales</span><strong><?= number_format($stats['total']) ?></strong></div></article>
        <article class="audit-stat"><div class="audit-stat-icon blue"><i class="fa-regular fa-calendar-check"></i></div><div><span>Actividad de hoy</span><strong><?= number_format($stats['today']) ?></strong></div></article>
        <article class="audit-stat"><div class="audit-stat-icon green"><i class="fa-solid fa-users"></i></div><div><span>Usuarios registrados</span><strong><?= number_format($stats['users']) ?></strong></div></article>
        <article class="audit-stat"><div class="audit-stat-icon amber"><i class="fa-solid fa-triangle-exclamation"></i></div><div><span>Respuestas con error</span><strong><?= number_format($stats['errors']) ?></strong></div></article>
    </div>

    <form class="audit-filters" method="GET" action="<?= APP_URL ?>/admin/audit-logs">
        <div class="audit-filter-title"><div class="audit-filter-icon"><i class="fa-solid fa-sliders"></i></div><div><strong>Filtrar actividad</strong><small>Combina los criterios para localizar un registro específico</small></div></div>
        <label class="audit-search"><span>Buscar</span><div><i class="fa-solid fa-magnifying-glass"></i><input type="search" name="search" value="<?= htmlspecialchars($filters['search'], ENT_QUOTES, 'UTF-8') ?>" placeholder="Usuario, correo, módulo, acción, IP..."></div></label>
        <label><span>Módulo</span><select name="module"><option value="">Todos los módulos</option><?php foreach ($options['modules'] as $module): ?><option value="<?= htmlspecialchars($module, ENT_QUOTES, 'UTF-8') ?>" <?= $filters['module'] === $module ? 'selected' : '' ?>><?= htmlspecialchars($module, ENT_QUOTES, 'UTF-8') ?></option><?php endforeach; ?></select></label>
        <label><span>Acción</span><select name="action"><option value="">Todas las acciones</option><?php foreach ($options['actions'] as $action): ?><option value="<?= htmlspecialchars($action, ENT_QUOTES, 'UTF-8') ?>" <?= $filters['action'] === $action ? 'selected' : '' ?>><?= htmlspecialchars($action, ENT_QUOTES, 'UTF-8') ?></option><?php endforeach; ?></select></label>
        <label><span>Usuario</span><select name="user_id"><option value="">Todos los usuarios</option><?php foreach ($options['users'] as $user): ?><option value="<?= (int)$user['user_id'] ?>" <?= (string)$filters['user_id'] === (string)$user['user_id'] ? 'selected' : '' ?>><?= htmlspecialchars($user['user_name'], ENT_QUOTES, 'UTF-8') ?></option><?php endforeach; ?></select></label>
        <label><span>Desde</span><input type="date" name="from" value="<?= htmlspecialchars($filters['from'], ENT_QUOTES, 'UTF-8') ?>"></label>
        <label><span>Hasta</span><input type="date" name="to" value="<?= htmlspecialchars($filters['to'], ENT_QUOTES, 'UTF-8') ?>"></label>
        <div class="audit-filter-actions"><button type="submit"><i class="fa-solid fa-filter"></i> Aplicar filtros</button><a href="<?= APP_URL ?>/admin/audit-logs">Limpiar</a></div>
    </form>

    <section class="audit-table-card">
        <div class="audit-table-head"><div><h2>Registro de actividad</h2><p><?= number_format($pagination['total']) ?> eventos encontrados</p></div><span><i class="fa-solid fa-circle"></i> Actualizado al cargar la página</span></div>
        <div class="audit-table-wrap">
            <table class="audit-table">
                <thead><tr><th>Usuario</th><th>Actividad</th><th>Módulo</th><th>Fecha y hora</th><th>IP / Método</th><th>Resultado</th></tr></thead>
                <tbody>
                <?php if (!$logs): ?><tr><td colspan="6"><div class="audit-empty"><i class="fa-regular fa-folder-open"></i><strong>No hay registros para mostrar</strong><span>Prueba cambiando los filtros o realiza una acción en el panel.</span></div></td></tr><?php endif; ?>
                <?php foreach ($logs as $log): ?>
                    <tr>
                        <td><div class="audit-user"><span class="audit-avatar"><?= htmlspecialchars(function_exists('mb_strtoupper') ? mb_strtoupper(function_exists('mb_substr') ? mb_substr((string)$log['user_name'], 0, 1) : substr((string)$log['user_name'], 0, 1)) : strtoupper(substr((string)$log['user_name'], 0, 1)), ENT_QUOTES, 'UTF-8') ?></span><div><strong><?= htmlspecialchars($log['user_name'], ENT_QUOTES, 'UTF-8') ?></strong><small><?= htmlspecialchars($log['user_email'] ?? 'Usuario interno', ENT_QUOTES, 'UTF-8') ?><?= $log['user_role'] ? ' · ' . htmlspecialchars($log['user_role'], ENT_QUOTES, 'UTF-8') : '' ?></small></div></div></td>
                        <td><strong class="audit-action"><?= htmlspecialchars($log['action'], ENT_QUOTES, 'UTF-8') ?></strong><small class="audit-route"><?= htmlspecialchars($log['route'], ENT_QUOTES, 'UTF-8') ?></small></td>
                        <td><span class="audit-module"><?= htmlspecialchars($log['module'], ENT_QUOTES, 'UTF-8') ?></span></td>
                        <td><strong class="audit-date"><?= htmlspecialchars($formatDate($log['created_at']), ENT_QUOTES, 'UTF-8') ?></strong></td>
                        <td><strong class="audit-ip"><?= htmlspecialchars($log['ip_address'] ?? 'No disponible', ENT_QUOTES, 'UTF-8') ?></strong><small><?= htmlspecialchars($log['http_method'], ENT_QUOTES, 'UTF-8') ?></small></td>
                        <td><span class="audit-status <?= (int)$log['response_status'] >= 400 ? 'failed' : 'success' ?>"><?= (int)$log['response_status'] >= 400 ? 'Error ' : 'Correcto ' ?><?= (int)$log['response_status'] ?></span></td>
                    </tr>
                <?php endforeach; ?>
                </tbody>
            </table>
        </div>
        <div class="audit-pagination"><span>Página <?= (int)$pagination['page'] ?> de <?= (int)$pagination['pages'] ?></span><div><?php if ($pagination['page'] > 1): ?><a href="<?= htmlspecialchars($makePageUrl($pagination['page'] - 1), ENT_QUOTES, 'UTF-8') ?>"><i class="fa-solid fa-chevron-left"></i> Anterior</a><?php endif; ?><?php if ($pagination['page'] < $pagination['pages']): ?><a href="<?= htmlspecialchars($makePageUrl($pagination['page'] + 1), ENT_QUOTES, 'UTF-8') ?>">Siguiente <i class="fa-solid fa-chevron-right"></i></a><?php endif; ?></div></div>
    </section>
    <p class="audit-footnote"><i class="fa-solid fa-lock"></i> La auditoría es de solo lectura. No se almacenan contraseñas, tokens ni contenidos sensibles de formularios.</p>
</div>
