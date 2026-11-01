<?php

class AuditLogController extends Controller
{
    public function index(): void
    {
        $repository = new AuditLogRepository();
        $currentUser = Auth::user() ?? [];
        $isSuperAdmin = strtolower((string)($currentUser['role_name'] ?? '')) === 'superadmin';
        $branchScope = $isSuperAdmin ? null : (int)($currentUser['branch_id'] ?? 0);
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
        $total = $repository->count($filters, $branchScope);
        $pages = max(1, (int)ceil($total / $perPage));
        $page = min($page, $pages);
        $logs = $repository->paginate($filters, $perPage, ($page - 1) * $perPage, $branchScope);

        $this->view('admin/layout', [
            'contentView' => __DIR__ . '/../Views/admin/audit-logs.php',
            'auditLogs' => $logs,
            'auditFilters' => $filters,
            'auditStats' => $repository->stats($branchScope),
            'auditOptions' => $repository->filterOptions($branchScope),
            'auditPagination' => ['page' => $page, 'pages' => $pages, 'total' => $total, 'per_page' => $perPage]
        ]);
    }
}
