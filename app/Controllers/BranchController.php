<?php
// app/Controllers/BranchController.php

class BranchController extends Controller
{
    private BranchService $branchService;

    public function __construct()
    {
        $this->branchService =
            new BranchService();
    }

    /**
     * GET /admin/branches
     */
    public function index(): void
    {
        $context =
            $this->getAdminContext();

        $this->view(
            'admin/layout',
            [
                'contentView' =>
                    __DIR__ .
                    '/../Views/admin/branches.php',

                'branchesPageIsSuperAdmin' =>
                    $context['is_superadmin'],

                'branchesPageBranchId' =>
                    $context['branch_id']
            ]
        );
    }

    /**
     * =========================================================
     * GET /sedes
     * Página pública de sedes.
     * =========================================================
     */
    public function publicPage(): void
    {
        $this->view(
            'layouts/module_public_layout',
            [
                'contentView' =>
                    __DIR__ .
                    '/../Views/restaurant/branches.php',

                'script' =>
                    'branches'
            ]
        );
    }

    /**
     * GET /admin/branches/create
     */
    public function create(): void
    {
        $context =
            $this->getAdminContext();

        if (!$context['is_superadmin']) {

            header(
                'Location: ' .
                APP_URL .
                '/admin/branches'
            );

            exit;
        }

        $this->view(
            'admin/layout',
            [
                'contentView' =>
                    __DIR__ .
                    '/../Views/admin/branch-form.php',

                'branchFormMode' =>
                    'create',

                'branchFormIsSuperAdmin' =>
                    true,

                'branchFormBranch' =>
                    null,

                'branchFormHours' =>
                    $this->getDefaultHours(),

                'branchFormTables' =>
                    [],

                'branchFormImages' =>
                    []
            ]
        );
    }


    /**
     * GET /admin/branches/edit
     */
    public function edit(): void
    {
        try {

            $context =
                $this->getAdminContext();

            $branchId =
                (int) ($_GET['id'] ?? 0);

            if ($branchId <= 0) {

                throw new DomainException(
                    'Sede inválida.',
                    422
                );
            }

            $branch =
                $this->branchService
                    ->getBranch(
                        $branchId,
                        $context['is_superadmin'],
                        $context['branch_id']
                    );

            if (!$branch) {

                throw new DomainException(
                    'La sede no existe.',
                    404
                );
            }

            $hours =
                $this->branchService
                    ->getBusinessHours(
                        $branchId,
                        $context['is_superadmin'],
                        $context['branch_id']
                    );

            $tables =
                $this->branchService
                    ->getTables(
                        $branchId,
                        $context['is_superadmin'],
                        $context['branch_id']
                    );

            $images =
                $this->branchService
                    ->getImages(
                        $branchId,
                        $context['is_superadmin'],
                        $context['branch_id']
                    );

            $this->view(
                'admin/layout',
                [
                    'contentView' =>
                        __DIR__ .
                        '/../Views/admin/branch-form.php',

                    'branchFormMode' =>
                        'edit',

                    'branchFormIsSuperAdmin' =>
                        $context['is_superadmin'],

                    'branchFormBranch' =>
                        $branch,

                    'branchFormHours' =>
                        $hours,

                    'branchFormTables' =>
                        $tables,

                    'branchFormImages' =>
                        $images
                ]
            );

        } catch (DomainException $e) {

            http_response_code(
                $e->getCode() >= 400
                    ? $e->getCode()
                    : 422
            );

            echo htmlspecialchars(
                $e->getMessage(),
                ENT_QUOTES,
                'UTF-8'
            );
        }
    }

    /**
     * POST /api/admin/branches/save-hours
     */
    public function saveHours(): void
    {
        try {

            $context =
                $this->getAdminContext();

            $input =
                $this->getInput();

            $branchId =
                (int) ($input['branch_id'] ?? 0);

            $this->branchService
                ->saveBusinessHours(
                    $branchId,
                    $input,
                    $context['is_superadmin'],
                    $context['branch_id']
                );

            $this->json([
                'success' => true,
                'message' =>
                    'Horarios actualizados correctamente.'
            ]);

        } catch (DomainException $e) {

            $this->json([
                'success' => false,
                'message' =>
                    $e->getMessage()
            ], $e->getCode() >= 400
                ? $e->getCode()
                : 422);

        } catch (Throwable $e) {

            error_log(
                '[ADMIN BRANCH HOURS] ' .
                $e->getMessage()
            );

            $this->json([
                'success' => false,
                'message' =>
                    'No fue posible guardar los horarios.'
            ], 500);
        }
    }

    /**
     * =========================================================
     * GET /api/admin/branches/tables
     * =========================================================
     */
    public function tables(): void
    {
        try {

            $context =
                $this->getAdminContext();

            $branchId =
                (int) (
                    $_GET['branch_id']
                    ?? 0
                );

            $tables =
                $this->branchService
                    ->getTables(
                        $branchId,
                        $context['is_superadmin'],
                        $context['branch_id']
                    );

            $this->json([
                'success' => true,
                'data' => $tables
            ]);

        } catch (DomainException $e) {

            $this->json([
                'success' => false,
                'message' =>
                    $e->getMessage()
            ], $e->getCode() >= 400
                ? $e->getCode()
                : 422);

        } catch (Throwable $e) {

            error_log(
                '[ADMIN BRANCH TABLES] ' .
                $e->getMessage()
            );

            $this->json([
                'success' => false,
                'message' =>
                    'No fue posible cargar las mesas.'
            ], 500);
        }
    }


    /**
     * =========================================================
     * POST /api/admin/branches/tables/create
     * =========================================================
     */
    public function createTable(): void
    {
        try {

            $context =
                $this->getAdminContext();

            $input =
                $this->getInput();

            $branchId =
                (int) (
                    $input['branch_id']
                    ?? 0
                );

            $tableId =
                $this->branchService
                    ->createTable(
                        $branchId,
                        $input,
                        $context['is_superadmin'],
                        $context['branch_id']
                    );

            $this->json([
                'success' => true,
                'message' =>
                    'Mesa creada correctamente.',
                'data' => [
                    'id' =>
                        $tableId
                ]
            ]);

        } catch (DomainException $e) {

            $this->json([
                'success' => false,
                'message' =>
                    $e->getMessage()
            ], $e->getCode() >= 400
                ? $e->getCode()
                : 422);

        } catch (Throwable $e) {

            error_log(
                '[ADMIN BRANCH TABLE CREATE] ' .
                $e->getMessage()
            );

            $this->json([
                'success' => false,
                'message' =>
                    'No fue posible crear la mesa.'
            ], 500);
        }
    }


    /**
     * =========================================================
     * POST /api/admin/branches/tables/update
     * =========================================================
     */
    public function updateTable(): void
    {
        try {

            $context =
                $this->getAdminContext();

            $input =
                $this->getInput();

            $tableId =
                (int) (
                    $input['id']
                    ?? 0
                );

            if ($tableId <= 0) {

                throw new DomainException(
                    'Mesa inválida.',
                    422
                );
            }

            $this->branchService
                ->updateTable(
                    $tableId,
                    $input,
                    $context['is_superadmin'],
                    $context['branch_id']
                );

            $this->json([
                'success' => true,
                'message' =>
                    'Mesa actualizada correctamente.'
            ]);

        } catch (DomainException $e) {

            $this->json([
                'success' => false,
                'message' =>
                    $e->getMessage()
            ], $e->getCode() >= 400
                ? $e->getCode()
                : 422);

        } catch (Throwable $e) {

            error_log(
                '[ADMIN BRANCH TABLE UPDATE] ' .
                $e->getMessage()
            );

            $this->json([
                'success' => false,
                'message' =>
                    'No fue posible actualizar la mesa.'
            ], 500);
        }
    }


    /**
     * =========================================================
     * POST /api/admin/branches/tables/delete
     * =========================================================
     */
    public function deleteTable(): void
    {
        try {

            $context =
                $this->getAdminContext();

            $input =
                $this->getInput();

            $tableId =
                (int) (
                    $input['id']
                    ?? 0
                );

            if ($tableId <= 0) {

                throw new DomainException(
                    'Mesa inválida.',
                    422
                );
            }

            $this->branchService
                ->deleteTable(
                    $tableId,
                    $context['is_superadmin'],
                    $context['branch_id']
                );

            $this->json([
                'success' => true,
                'message' => 'Mesa desactivada correctamente.'
            ]);

        } catch (DomainException $e) {

            $this->json([
                'success' => false,
                'message' =>
                    $e->getMessage()
            ], $e->getCode() >= 400
                ? $e->getCode()
                : 422);

        } catch (Throwable $e) {

            error_log(
                '[ADMIN BRANCH TABLE DELETE] ' .
                $e->getMessage()
            );

            $this->json([
                'success' => false,
                'message' =>
                    'No fue posible eliminar la mesa.'
            ], 500);
        }
    }


    /**
     * POST /api/admin/branches/tables/delete-permanently
     */

    public function deleteTablePermanently(): void
    {
        $tableId = 0;
        $context = null;

        try {
            $context = $this->getAdminContext();
            $input = $this->getInput();

            $tableId = (int) ($input['id'] ?? 0);

            if ($tableId <= 0) {
                throw new DomainException('Mesa inválida.', 422);
            }

            $this->branchService->deleteTablePermanently(
                $tableId,
                $context['is_superadmin'],
                $context['branch_id']
            );

            $this->json([
                'success' => true,
                'message' => 'Mesa eliminada definitivamente.'
            ]);

        } catch (DomainException $e) {
            $statusCode = $e->getCode() >= 400
                ? $e->getCode()
                : 422;

            $payload = [
                'success' => false,
                'message' => $e->getMessage()
            ];

            /*
             * Si la eliminación se bloquea por historial,
             * devolver las reservas para el modal.
             */
            if (
                $statusCode === 409 &&
                $tableId > 0 &&
                $context !== null
            ) {
                try {
                    $payload['reservations'] =
                        $this->branchService->getTableReservations(
                            $tableId,
                            $context['is_superadmin'],
                            $context['branch_id']
                        );
                } catch (Throwable $reservationError) {
                    error_log(
                        '[TABLE RESERVATIONS MODAL] ' .
                        $reservationError->getMessage()
                    );

                    $payload['reservations'] = [];
                }
            }

            $this->json($payload, $statusCode);

        } catch (Throwable $e) {
            error_log(
                '[ADMIN BRANCH TABLE PERMANENT DELETE] ' .
                $e->getMessage()
            );

            $this->json([
                'success' => false,
                'message' => 'No fue posible eliminar la mesa.'
            ], 500);
        }
    }



    /**
     * POST /api/admin/branches/images/save
     */
    public function saveImages(): void
    {
        try {

            $context =
                $this->getAdminContext();


            $branchId =
                (int) (
                    $_POST['branch_id']
                    ?? 0
                );


            if ($branchId <= 0) {

                throw new DomainException(
                    'Sede inválida.',
                    422
                );
            }


            $images =
                $this->branchService
                    ->saveImages(
                        $branchId,
                        $_FILES['images']
                        ?? [],
                        $_POST,
                        $context['is_superadmin'],
                        $context['branch_id']
                    );


            $this->json([
                'success' =>
                    true,

                'message' =>
                    'Imágenes guardadas correctamente.',

                'data' => [
                    'images' =>
                        $images
                ]
            ]);


        } catch (DomainException $e) {

            $this->json(
                [
                    'success' =>
                        false,

                    'message' =>
                        $e->getMessage()
                ],
                $e->getCode() >= 400
                    ? $e->getCode()
                    : 422
            );


        } catch (Throwable $e) {

            error_log(
                '[ADMIN BRANCH IMAGES] ' .
                $e->getMessage()
            );


            $this->json(
                [
                    'success' =>
                        false,

                    'message' =>
                        'No fue posible guardar las imágenes de la sede.'
                ],
                500
            );
        }
    }

    /**
     * GET /api/public/branches/hours
     */
    public function publicHours(): void
    {
        try {

            $branchId =
                (int) ($_GET['branch_id'] ?? 0);

            if ($branchId <= 0) {

                throw new DomainException(
                    'Sede inválida.',
                    422
                );
            }

            $branch =
                $this->branchService
                    ->getBranch(
                        $branchId,
                        true,
                        null
                    );

            if (!$branch) {

                throw new DomainException(
                    'La sede no existe.',
                    404
                );
            }

            if ((int) $branch['is_active'] !== 1) {

                throw new DomainException(
                    'La sede no está disponible.',
                    422
                );
            }

            $hours =
                $this->branchService
                    ->getBusinessHours(
                        $branchId,
                        true,
                        null
                    );

            $this->json([
                'success' => true,
                'data' => array_values($hours)
            ]);

        } catch (DomainException $e) {

            $this->json([
                'success' => false,
                'message' =>
                    $e->getMessage()
            ], $e->getCode() >= 400
                ? $e->getCode()
                : 422);

        } catch (Throwable $e) {

            error_log(
                '[PUBLIC BRANCH HOURS] ' .
                $e->getMessage()
            );

            $this->json([
                'success' => false,
                'message' =>
                    'No fue posible consultar los horarios.'
            ], 500);
        }
    }


    /**
     * Horarios iniciales.
     */
    private function getDefaultHours(): array
    {
        $hours = [];

        for ($day = 1; $day <= 7; $day++) {

            $hours[$day] = [
                'day_of_week' => $day,
                'open_time' => null,
                'close_time' => null,
                'is_closed' => true
            ];
        }

        return $hours;
    }

    /**
     * GET /api/admin/branches
     */
    public function data(): void
    {
        try {

            $context =
                $this->getAdminContext();

            $search =
                isset($_GET['search'])
                    ? trim(
                    (string)
                    $_GET['search']
                )
                    : null;

            $isActive =
                isset($_GET['is_active']) &&
                $_GET['is_active'] !== ''
                    ? (int) $_GET['is_active']
                    : null;

            $branches =
                $this->branchService
                    ->getBranches(
                        $context['is_superadmin'],
                        $context['branch_id'],
                        $search,
                        $isActive
                    );

            $statistics =
                $this->branchService
                    ->getStatistics(
                        $context['is_superadmin'],
                        $context['branch_id']
                    );

            $this->json([
                'success' => true,

                'data' => $branches,

                'statistics' =>
                    $statistics,

                'permissions' => [
                    'can_create' =>
                        $context['is_superadmin'],

                    'can_delete' =>
                        $context['is_superadmin'],

                    'can_edit' =>
                        true
                ]
            ]);

        } catch (Throwable $e) {

            error_log(
                '[ADMIN BRANCHES DATA] ' .
                $e->getMessage()
            );

            $this->json([
                'success' => false,
                'message' =>
                    'No fue posible cargar las sedes.'
            ], 500);
        }
    }

    /**
     * GET /api/admin/branches/show
     */
    public function show(): void
    {
        try {

            $context =
                $this->getAdminContext();

            $branchId =
                (int) ($_GET['id'] ?? 0);

            if ($branchId <= 0) {

                throw new DomainException(
                    'Sede inválida.',
                    422
                );
            }

            $branch =
                $this->branchService
                    ->getBranch(
                        $branchId,
                        $context['is_superadmin'],
                        $context['branch_id']
                    );

            if (!$branch) {

                throw new DomainException(
                    'La sede no existe.',
                    404
                );
            }

            $this->json([
                'success' => true,
                'data' => $branch
            ]);

        } catch (DomainException $e) {

            $this->json([
                'success' => false,
                'message' =>
                    $e->getMessage()
            ], $e->getCode() >= 400
                ? $e->getCode()
                : 422);
        }
    }

    /**
     * POST /api/admin/branches/store
     */
    public function store(): void
    {
        try {

            $context =
                $this->getAdminContext();

            $input =
                $this->getInput();

            $id =
                $this->branchService
                    ->createBranch(
                        $input,
                        $context['is_superadmin']
                    );

            $this->json([
                'success' => true,

                'message' =>
                    'Sede creada correctamente.',

                'data' => [
                    'id' => $id
                ]
            ], 201);

        } catch (DomainException $e) {

            $this->json([
                'success' => false,
                'message' =>
                    $e->getMessage()
            ], $e->getCode() >= 400
                ? $e->getCode()
                : 422);

        } catch (Throwable $e) {

            error_log(
                '[ADMIN BRANCHES STORE] ' .
                $e->getMessage()
            );

            $this->json([
                'success' => false,
                'message' =>
                    'No fue posible crear la sede.'
            ], 500);
        }
    }

    /**
     * POST /api/admin/branches/update
     */
    public function update(): void
    {
        try {

            $context =
                $this->getAdminContext();

            $input =
                $this->getInput();

            $branchId =
                (int) ($input['id'] ?? 0);

            if ($branchId <= 0) {

                throw new DomainException(
                    'Sede inválida.',
                    422
                );
            }

            $this->branchService
                ->updateBranch(
                    $branchId,
                    $input,
                    $context['is_superadmin'],
                    $context['branch_id']
                );

            $this->json([
                'success' => true,

                'message' =>
                    'Sede actualizada correctamente.'
            ]);

        } catch (DomainException $e) {

            $this->json([
                'success' => false,
                'message' =>
                    $e->getMessage()
            ], $e->getCode() >= 400
                ? $e->getCode()
                : 422);

        } catch (Throwable $e) {

            error_log(
                '[ADMIN BRANCHES UPDATE] ' .
                $e->getMessage()
            );

            $this->json([
                'success' => false,
                'message' =>
                    'No fue posible actualizar la sede.'
            ], 500);
        }
    }

    /**
     * POST /api/admin/branches/delete
     */
    public function delete(): void
    {
        try {

            $context =
                $this->getAdminContext();

            $input =
                $this->getInput();

            $branchId =
                (int) ($input['id'] ?? 0);

            if ($branchId <= 0) {

                throw new DomainException(
                    'Sede inválida.',
                    422
                );
            }

            $this->branchService
                ->deleteBranch(
                    $branchId,
                    $context['is_superadmin']
                );

            $this->json([
                'success' => true,

                'message' =>
                    'Sede eliminada correctamente.'
            ]);

        } catch (DomainException $e) {

            $this->json([
                'success' => false,
                'message' =>
                    $e->getMessage()
            ], $e->getCode() >= 400
                ? $e->getCode()
                : 422);

        } catch (Throwable $e) {

            error_log(
                '[ADMIN BRANCHES DELETE] ' .
                $e->getMessage()
            );

            $this->json([
                'success' => false,
                'message' =>
                    'No fue posible eliminar la sede.'
            ], 500);
        }
    }

    /**
     * Contexto administrativo.
     */
    private function getAdminContext(): array
    {
        $user =
            Auth::user() ?? [];

        $role =
            strtolower(
                (string)
                ($user['role_name'] ?? '')
            );

        $isSuperAdmin =
            $role === 'superadmin';

        $branchId =
            $isSuperAdmin
                ? null
                : (
            isset($user['branch_id']) &&
            $user['branch_id'] !== null
                ? (int) $user['branch_id']
                : null
            );

        return [
            'user_id' =>
                isset($user['id'])
                    ? (int) $user['id']
                    : (int) (
                    $_SESSION['user_id']
                    ?? 0
                ),

            'role' =>
                $role,

            'is_superadmin' =>
                $isSuperAdmin,

            'branch_id' =>
                $branchId
        ];
    }

    /**
     * Obtener JSON o POST.
     */
    private function getInput(): array
    {
        if (!empty($_POST)) {
            return $_POST;
        }

        $content =
            file_get_contents(
                'php://input'
            );

        if (
            $content === false ||
            trim($content) === ''
        ) {
            return [];
        }

        $data =
            json_decode(
                $content,
                true
            );

        return is_array($data)
            ? $data
            : [];
    }


}