<?php

class AuditLogRepository
{
    private PDO $db;

    public function __construct()
    {
        $this->db = Database::getConnection();
    }

    private function where(array $filters): array
    {
        $conditions = [];
        $params = [];

        if (($filters['search'] ?? '') !== '') {
            $conditions[] = "(user_name LIKE :search_name OR user_email LIKE :search_email OR action LIKE :search_action OR module LIKE :search_module OR route LIKE :search_route OR ip_address LIKE :search_ip)";
            $value = '%' . $filters['search'] . '%';
            foreach (['search_name','search_email','search_action','search_module','search_route','search_ip'] as $key) $params[':' . $key] = $value;
        }
        foreach (['module' => 'module', 'action' => 'action', 'user_id' => 'user_id'] as $filter => $column) {
            if (($filters[$filter] ?? '') !== '') {
                $conditions[] = "{$column} = :{$filter}";
                $params[':' . $filter] = $filters[$filter];
            }
        }
        if (($filters['from'] ?? '') !== '') {
            $conditions[] = "created_at >= :date_from";
            $params[':date_from'] = $filters['from'] . ' 00:00:00';
        }
        if (($filters['to'] ?? '') !== '') {
            $conditions[] = "created_at <= :date_to";
            $params[':date_to'] = $filters['to'] . ' 23:59:59';
        }
        return [$conditions ? ' WHERE ' . implode(' AND ', $conditions) : '', $params];
    }

    public function paginate(array $filters, int $limit, int $offset): array
    {
        [$where, $params] = $this->where($filters);
        $sql = "SELECT id, user_id, user_name, user_email, user_role, branch_id,
                       module, action, http_method, route, ip_address,
                       response_status, created_at
                FROM admin_audit_logs {$where}
                ORDER BY created_at DESC, id DESC
                LIMIT {$limit} OFFSET {$offset}";
        $stmt = $this->db->prepare($sql);
        $stmt->execute($params);
        return $stmt->fetchAll();
    }

    public function count(array $filters): int
    {
        [$where, $params] = $this->where($filters);
        $stmt = $this->db->prepare("SELECT COUNT(*) FROM admin_audit_logs {$where}");
        $stmt->execute($params);
        return (int)$stmt->fetchColumn();
    }

    public function stats(): array
    {
        $row = $this->db->query("
            SELECT COUNT(*) AS total,
                   SUM(created_at >= CURDATE()) AS today,
                   COUNT(DISTINCT user_id) AS users,
                   SUM(response_status >= 400) AS errors
            FROM admin_audit_logs
        ")->fetch() ?: [];
        return [
            'total' => (int)($row['total'] ?? 0),
            'today' => (int)($row['today'] ?? 0),
            'users' => (int)($row['users'] ?? 0),
            'errors' => (int)($row['errors'] ?? 0)
        ];
    }

    public function filterOptions(): array
    {
        return [
            'modules' => $this->db->query("SELECT DISTINCT module FROM admin_audit_logs ORDER BY module")->fetchAll(PDO::FETCH_COLUMN),
            'actions' => $this->db->query("SELECT DISTINCT action FROM admin_audit_logs ORDER BY action")->fetchAll(PDO::FETCH_COLUMN),
            'users' => $this->db->query("SELECT DISTINCT user_id, user_name FROM admin_audit_logs WHERE user_id IS NOT NULL ORDER BY user_name")->fetchAll()
        ];
    }
}
