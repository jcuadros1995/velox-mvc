<?php

class ApiController extends Controller
{
    private AdminService $adminService;

    public function __construct()
    {
        $this->adminService = new AdminService();
    }

    public function salesMetrics(): void
    {
        try {
            $from = $_GET['from'] ?? null;
            $to = $_GET['to'] ?? null;

            if (!$from || !$to) {
                $to = date('Y-m-d');
                $from = date('Y-m-01');
            }

            $fromDate = DateTime::createFromFormat('Y-m-d', $from);
            $toDate = DateTime::createFromFormat('Y-m-d', $to);

            if (
                !$fromDate ||
                !$toDate ||
                $fromDate->format('Y-m-d') !== $from ||
                $toDate->format('Y-m-d') !== $to
            ) {
                $this->json([
                    'success' => false,
                    'message' => 'Rango de fechas inválido.'
                ], 400);
            }

            $user = Auth::user();

            if (!$user) {
                $this->json([
                    'success' => false,
                    'message' => 'Sesión no válida.'
                ], 401);
            }

            $role = strtolower((string) ($user['role_name'] ?? ''));

            if (!in_array($role, ['superadmin', 'admin', 'staff'], true)) {
                $this->json([
                    'success' => false,
                    'message' => 'No tienes permisos para consultar las métricas.'
                ], 403);
            }

            $branchId = null;

            if ($role !== 'superadmin') {
                $branchId = isset($user['branch_id'])
                    ? (int) $user['branch_id']
                    : null;

                if ($branchId <= 0) {
                    $this->json([
                        'success' => false,
                        'message' => 'El usuario no tiene una sede asignada.'
                    ], 403);
                }
            }

            if ($role === 'superadmin' && isset($_GET['branch_id']) && $_GET['branch_id'] !== '') {
                $branchId = (int) $_GET['branch_id'];

                if ($branchId <= 0) {
                    $this->json([
                        'success' => false,
                        'message' => 'Sede inválida.'
                    ], 400);
                }
            }

            $toExclusive = $toDate
                ->modify('+1 day')
                ->format('Y-m-d');

            $data = $this->adminService->getSalesMetrics(
                $from . ' 00:00:00',
                $toExclusive . ' 00:00:00',
                $branchId
            );

            $this->json([
                'success' => true,
                'data' => $data
            ]);
        } catch (Throwable $e) {
            error_log(
                '[ADMIN_METRICS] ' .
                $e->getMessage()
            );

            $this->json([
                'success' => false,
                'message' => 'No fue posible obtener las métricas.'
            ], 500);
        }
    }
}
