<?php

class AuditLogController extends Controller
{
    public function index(): void
    {
        $repository = new AuditLogRepository();
        $filters = [
            'search' => trim((string)($_GET['search'] ?? '')),
            'module' => trim((string)($_GET['module'] ?? '')),
            'action' => trim((string)($_GET['action'] ?? '')),
            'user_id' => trim((string)($_GET['user_id'] ?? '')),
            'from' => trim((string)($_GET['from'] ?? '')),
            'to' => trim((string)($_GET['to'] ?? ''))
        ];
        foreach (['from', 'to'] as $field) {
            if ($filters[$field] !== '' && !preg_match('/^\d{4}-\d{2}-\d{2}$/', $filters[$field])) $filters[$field] = '';
        }
        if ($filters['user_id'] !== '' && !ctype_digit($filters['user_id'])) $filters['user_id'] = '';

        $page = max(1, (int)($_GET['page'] ?? 1));
        $perPage = 25;
        $total = $repository->count($filters);
        $pages = max(1, (int)ceil($total / $perPage));
        $page = min($page, $pages);
        $logs = $repository->paginate($filters, $perPage, ($page - 1) * $perPage);

        $this->view('admin/layout', [
            'contentView' => __DIR__ . '/../Views/admin/audit-logs.php',
            'auditLogs' => $logs,
            'auditFilters' => $filters,
            'auditStats' => $repository->stats(),
            'auditOptions' => $repository->filterOptions(),
            'auditPagination' => ['page' => $page, 'pages' => $pages, 'total' => $total, 'per_page' => $perPage]
        ]);
    }
}
