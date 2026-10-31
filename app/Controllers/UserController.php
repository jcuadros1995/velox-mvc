<?php
// app/Controllers/UserController.php

class UserController extends Controller
{
    private UserService $userService;

    private BranchRepository $branchRepository;

    public function __construct()
    {
        $this->userService =
            new UserService();

        $this->branchRepository =
            new BranchRepository();
    }

    /**
     * Perfil personal.
     */
    public function profile(): void
    {
        $userId =
            $_SESSION['user_id'] ?? null;

        $profile =
            $this->userService->getProfile(
                $userId !== null
                    ? (int) $userId
                    : null
            );

        if (!$profile) {

            $_SESSION['toast_error'] =
                'Usuario no encontrado.';

            $this->redirect(
                '/admin/dashboard'
            );

            return;
        }

        $this->view('admin/layout', [
            'user' =>
                $profile['user'],

            'misAccesos' =>
                $profile['misAccesos'],

            'contentView' =>
                __DIR__ .
                '/../Views/users/profile.php'
        ]);
    }

    /**
     * Panel de usuarios.
     */
    public function index(): void
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

        $currentBranchId =
            $isSuperAdmin
                ? null
                : (
            isset($user['branch_id'])
                ? (int) $user['branch_id']
                : null
            );

        $branches =
            $isSuperAdmin
                ? $this->branchRepository
                ->getActiveBranches()
                : [];

        $this->view(
            'admin/layout',
            [
                'contentView' =>
                    __DIR__ .
                    '/../Views/admin/users.php',

                'adminBranches' =>
                    $branches,

                'usersPageIsSuperAdmin' =>
                    $isSuperAdmin,

                'usersPageBranchId' =>
                    $currentBranchId
            ]
        );
    }

    /**
     * Página para crear usuario.
     */

    /**
     * Página para crear usuario.
     */
    public function create(): void
    {
        $context = $this->getAdminContext();

        $roles = $this->userService->getRoles();

        /*
         * SuperAdmin puede crear usuarios con cualquier rol.
         * Admin de sede solo puede crear staff y customer.
         */
        if (!$context['is_superadmin']) {
            $roles = array_values(array_filter(
                $roles,
                static function (array $role): bool {
                    $roleName = strtolower(
                        trim((string) ($role['name'] ?? ''))
                    );

                    return in_array(
                        $roleName,
                        ['staff', 'customer'],
                        true
                    );
                }
            ));
        }

        /*
         * Cargar las sedes disponibles.
         * El administrador de sede solo recibe su propia sede.
         */
        if ($context['is_superadmin']) {
            $branches = $this->branchRepository->getActiveBranches();
        } else {
            $branchId = (int) ($context['branch_id'] ?? 0);
            $branch = $branchId > 0
                ? $this->branchRepository->findById($branchId)
                : null;

            $branches = $branch ? [$branch] : [];
        }

        $this->view('admin/layout', [
            'contentView' =>
                __DIR__ . '/../Views/admin/user-form.php',

            'userFormMode' => 'create',
            'userFormUser' => null,
            'userFormRoles' => $roles,
            'userFormBranches' => $branches,
            'userFormIsSuperAdmin' => $context['is_superadmin'],
            'userFormBranchId' => $context['branch_id']
        ]);
    }


    /**
     * Página para editar usuario.
     */

    /**
     * Página para editar usuario.
     */
    public function edit(): void
    {
        $context = $this->getAdminContext();

        $userId = (int) ($_GET['id'] ?? 0);

        // Validar el ID del usuario.
        if ($userId <= 0) {
            $_SESSION['toast_error'] = 'Usuario inválido.';
            $this->redirect('/admin/users');
            return;
        }

        /*
         * Obtener el usuario respetando el alcance de acceso:
         * - SuperAdmin: puede consultar usuarios de cualquier sede.
         * - Admin: únicamente usuarios de su propia sede.
         */
        $branchId = (int) ($context['branch_id'] ?? 0);

        $user = $this->userService->getManagedUser(
            $userId,
            $context['is_superadmin'] ? null : $branchId
        );

        if (!$user) {
            $_SESSION['toast_error'] =
                'El usuario no existe o no pertenece a su sede.';

            $this->redirect('/admin/users');
            return;
        }

        /*
         * Obtener los roles disponibles.
         */
        $roles = $this->userService->getRoles();

        /*
         * El administrador de sede solo puede asignar
         * los roles staff y customer.
         *
         * Esta restricción NO impide editar usuarios
         * que actualmente tengan el rol Admin.
         */
        if (!$context['is_superadmin']) {
            $roles = array_values(array_filter(
                $roles,
                static function (array $role): bool {
                    $roleName = strtolower(
                        trim((string) ($role['name'] ?? ''))
                    );

                    return in_array(
                        $roleName,
                        ['staff', 'customer'],
                        true
                    );
                }
            ));
        }

        /*
         * Obtener las sedes disponibles.
         *
         * SuperAdmin: todas las sedes activas.
         * Admin: exclusivamente su propia sede.
         */
        if ($context['is_superadmin']) {
            $branches = $this->branchRepository->getActiveBranches();
        } else {
            $branch = $branchId > 0
                ? $this->branchRepository->findById($branchId)
                : null;

            $branches = $branch ? [$branch] : [];
        }

        /*
         * Mostrar el formulario de edición.
         */
        $this->view('admin/layout', [
            'contentView' => __DIR__ . '/../Views/admin/user-form.php',
            'userFormMode' => 'edit',
            'userFormUser' => $user,
            'userFormRoles' => $roles,
            'userFormBranches' => $branches,
            'userFormIsSuperAdmin' => $context['is_superadmin'],
            'userFormBranchId' => $branchId,
        ]);
    }


    /**
     * Listado AJAX.
     */
    public function data(): void
    {
        $context =
            $this->getAdminContext();

        $search =
            isset($_GET['search'])
                ? trim((string) $_GET['search'])
                : null;

        $roleId =
            isset($_GET['role_id']) &&
            $_GET['role_id'] !== ''
                ? (int) $_GET['role_id']
                : null;

        $isActive =
            isset($_GET['is_active']) &&
            $_GET['is_active'] !== ''
                ? (int) $_GET['is_active']
                : null;

        /*
         * SuperAdmin puede filtrar por sede.
         * Admin ignora cualquier branch_id enviado.
         */
        $branchId =
            $context['is_superadmin']
                ? (
            isset($_GET['branch_id']) &&
            $_GET['branch_id'] !== ''
                ? (int) $_GET['branch_id']
                : null
            )
                : $context['branch_id'];

        $users =
            $this->userService
                ->getManagedUsers(
                    $branchId,
                    $search,
                    $roleId,
                    $isActive
                );

        $this->json([
            'success' => true,
            'data' => $users
        ]);
    }

    /**
     * Datos iniciales del panel.
     */
    public function meta(): void
    {
        $context =
            $this->getAdminContext();

        $this->json([
            'success' => true,

            'data' => [
                'roles' =>
                    $this->userService
                        ->getRoles(),

                'permissions' =>
                    $this->userService
                        ->getPermissions(),

                'branches' =>
                    $context['is_superadmin']
                        ? $this->branchRepository
                        ->getActiveBranches()
                        : []
            ]
        ]);
    }

    /**
     * Crear.
     */
    public function store(): void
    {
        try {

            $context =
                $this->getAdminContext();

            $input =
                $this->getJsonInput();

            $id =
                $this->userService
                    ->createUser(
                        $input,
                        $context['is_superadmin'],
                        $context['branch_id']
                    );

            $this->json([
                'success' => true,
                'message' =>
                    'Usuario creado correctamente.',
                'data' => [
                    'id' => $id
                ]
            ], 201);

        } catch (DomainException $e) {

            $this->json([
                'success' => false,
                'message' => $e->getMessage()
            ], $e->getCode() >= 400
                ? $e->getCode()
                : 422);

        } catch (Throwable $e) {

            error_log(
                '[ADMIN USERS STORE] ' .
                $e->getMessage()
            );

            $this->json([
                'success' => false,
                'message' =>
                    'No fue posible crear el usuario.'
            ], 500);
        }
    }

    /**
     * Actualizar.
     */
    public function update(): void
    {
        try {

            $context =
                $this->getAdminContext();

            $input =
                $this->getJsonInput();

            $userId =
                (int) ($input['id'] ?? 0);

            if ($userId <= 0) {
                throw new DomainException(
                    'Usuario inválido.',
                    422
                );
            }

            $updated =
                $this->userService
                    ->updateUser(
                        $userId,
                        $input,
                        $context['is_superadmin'],
                        $context['branch_id'],
                        $context['user_id'],
                        $_FILES['photo'] ?? null,
                        !empty($input['remove_photo']),
                        $input['permissions'] ?? []
                    );

            $this->json([
                'success' => $updated,
                'message' =>
                    'Usuario actualizado correctamente.'
            ]);

        } catch (DomainException $e) {

            $this->json([
                'success' => false,
                'message' => $e->getMessage()
            ], $e->getCode() >= 400
                ? $e->getCode()
                : 422);

        } catch (Throwable $e) {

            error_log(
                '[ADMIN USERS UPDATE] ' .
                $e->getMessage()
            );

            $this->json([
                'success' => false,
                'message' =>
                    'No fue posible actualizar el usuario.'
            ], 500);
        }
    }

    /**
     * Eliminar / desactivar.
     */
    public function delete(): void
    {
        try {

            $context =
                $this->getAdminContext();

            $input =
                $this->getJsonInput();

            $userId =
                (int) ($input['id'] ?? 0);

            if ($userId <= 0) {
                throw new DomainException(
                    'Usuario inválido.',
                    422
                );
            }

            $this->userService
                ->deleteUser(
                    $userId,
                    $context['is_superadmin'],
                    $context['branch_id'],
                    $context['user_id']
                );

            $this->json([
                'success' => true,
                'message' =>
                    'Usuario desactivado correctamente.'
            ]);

        } catch (DomainException $e) {

            $this->json([
                'success' => false,
                'message' => $e->getMessage()
            ], $e->getCode() >= 400
                ? $e->getCode()
                : 422);

        } catch (Throwable $e) {

            error_log(
                '[ADMIN USERS DELETE] ' .
                $e->getMessage()
            );

            $this->json([
                'success' => false,
                'message' =>
                    'No fue posible eliminar el usuario.'
            ], 500);
        }
    }

    /**
     * Consultar permisos.
     */
    public function permissions(): void
    {
        try {

            $context =
                $this->getAdminContext();

            $userId =
                (int) ($_GET['id'] ?? 0);

            if ($userId <= 0) {
                throw new DomainException(
                    'Usuario inválido.',
                    422
                );
            }

            $user =
                $this->userService
                    ->getManagedUser(
                        $userId,
                        $context['is_superadmin']
                            ? null
                            : $context['branch_id']
                    );

            if (!$user) {
                throw new DomainException(
                    'Usuario no encontrado o fuera de su sede.',
                    404
                );
            }

            $this->json([
                'success' => true,
                'data' => [
                    'user' => $user,
                    'permissions' =>
                        $this->userService
                            ->getUserPermissions(
                                $userId
                            )
                ]
            ]);

        } catch (DomainException $e) {

            $this->json([
                'success' => false,
                'message' => $e->getMessage()
            ], $e->getCode() >= 400
                ? $e->getCode()
                : 422);
        }
    }

    /**
     * Guardar permisos.
     */
    public function savePermissions(): void
    {
        try {

            $context =
                $this->getAdminContext();

            $input =
                $this->getJsonInput();

            $userId =
                (int) ($input['user_id'] ?? 0);

            if ($userId <= 0) {
                throw new DomainException(
                    'Usuario inválido.',
                    422
                );
            }

            $permissions =
                is_array(
                    $input['permissions'] ?? null
                )
                    ? $input['permissions']
                    : [];

            $this->userService
                ->savePermissions(
                    $userId,
                    $permissions,
                    $context['is_superadmin'],
                    $context['branch_id']
                );

            $this->json([
                'success' => true,
                'message' =>
                    'Permisos actualizados correctamente.'
            ]);

        } catch (DomainException $e) {

            $this->json([
                'success' => false,
                'message' => $e->getMessage()
            ], $e->getCode() >= 400
                ? $e->getCode()
                : 422);

        } catch (Throwable $e) {

            error_log(
                '[ADMIN USERS PERMISSIONS] ' .
                $e->getMessage()
            );

            $this->json([
                'success' => false,
                'message' =>
                    'No fue posible actualizar los permisos.'
            ], 500);
        }
    }

    private function getAdminContext(): array
    {
        $user =
            Auth::user() ?? [];

        $role =
            strtolower(
                (string)
                ($user['role_name'] ?? '')
            );

        return [
            'user_id' =>
                (int) ($user['id'] ?? 0),

            'role' =>
                $role,

            'is_superadmin' =>
                $role === 'superadmin',

            'branch_id' =>
                $role === 'superadmin'
                    ? null
                    : (
                isset($user['branch_id'])
                    ? (int) $user['branch_id']
                    : null
                )
        ];
    }

    private function getJsonInput(): array
    {
        /*
         * =========================================================
         * FORM DATA / MULTIPART-FORM-DATA
         * =========================================================
         */

        if (!empty($_POST)) {

            $data = $_POST;


            /*
             * permissions llega como JSON:
             *
             * {
             *     "1": true,
             *     "2": false
             * }
             */

            if (
                isset($data['permissions']) &&
                is_string($data['permissions'])
            ) {

                $permissions =
                    json_decode(
                        $data['permissions'],
                        true
                    );

                $data['permissions'] =
                    is_array($permissions)
                        ? $permissions
                        : [];
            }


            return $data;
        }


        /*
         * =========================================================
         * JSON NORMAL
         * =========================================================
         */

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