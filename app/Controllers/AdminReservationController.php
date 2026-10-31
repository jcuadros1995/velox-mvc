<?php

class AdminReservationController extends Controller
{
    private AdminReservationService $reservationService;
    private BranchRepository $branchRepository;

    public function __construct()
    {
        $this->reservationService = new AdminReservationService();
        $this->branchRepository = new BranchRepository();
    }

    /**
     * GET /admin/reservations
     */
    public function index(): void
    {
        $user = Auth::user();

        if (!$user) {
            return;
        }

        $role = strtolower((string) ($user['role_name'] ?? ''));

        $branches = $role === 'superadmin'
            ? $this->branchRepository->getActiveBranches()
            : [];

        $this->view('admin/layout', [
            'contentView' => __DIR__ . '/../Views/admin/reservations.php',
            'script' => 'admin-reservations',
            'adminBranches' => $branches
        ]);
    }

    /**
     * GET /api/admin/reservations
     */
    public function data(): void
    {
        try {
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
                    'message' => 'No tienes permisos para consultar reservas.'
                ], 403);
            }

            $branchId = $this->resolveBranchId(
                $role,
                $_GET['branch_id'] ?? null
            );

            $from = trim((string) ($_GET['from'] ?? ''));
            $to = trim((string) ($_GET['to'] ?? ''));
            $status = trim((string) ($_GET['status'] ?? ''));
            $search = trim((string) ($_GET['search'] ?? ''));

            $reservations = $this->reservationService->getReservations(
                $branchId,
                $from,
                $to,
                $status,
                $search
            );

            $stats = $this->reservationService->getStats($branchId);

            $this->json([
                'success' => true,
                'data' => [
                    'reservations' => $reservations,
                    'stats' => $stats,
                    'scope' => [
                        'role' => $role,
                        'branch_id' => $branchId,
                        'all_branches' => $role === 'superadmin'
                    ]
                ]
            ]);
        } catch (InvalidArgumentException $e) {
            $this->json([
                'success' => false,
                'message' => $e->getMessage()
            ], 400);
        } catch (Throwable $e) {
            error_log(
                '[ADMIN_RESERVATIONS] ' .
                $e->getMessage()
            );

            $this->json([
                'success' => false,
                'message' => 'No fue posible cargar las reservas.'
            ], 500);
        }
    }

    private function resolveBranchId(
        string $role,
        $requestedBranchId
    ): ?int {
        if ($role === 'superadmin') {
            if (
                $requestedBranchId === null ||
                $requestedBranchId === ''
            ) {
                return null;
            }

            $branchId = (int) $requestedBranchId;

            return $branchId > 0 ? $branchId : null;
        }

        $sessionBranchId = $this->getSessionBranchId();

        if ($sessionBranchId === null) {
            throw new RuntimeException(
                'El usuario no tiene una sede asignada.'
            );
        }

        return $sessionBranchId;
    }

    private function getSessionBranchId(): ?int
    {
        if (
            isset($_SESSION['branch_id']) &&
            $_SESSION['branch_id'] !== null &&
            $_SESSION['branch_id'] !== ''
        ) {
            return (int) $_SESSION['branch_id'];
        }

        return null;
    }
}
