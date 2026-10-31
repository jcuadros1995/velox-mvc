<?php
// app/Services/UserService.php

class UserService
{
    private UserRepository $userRepository;

    private BranchRepository $branchRepository;

    public function __construct()
    {
        $this->userRepository =
            new UserRepository();

        $this->branchRepository =
            new BranchRepository();
    }

    /**
     * Perfil del usuario autenticado.
     */
    public function getProfile(?int $userId): ?array
    {
        if ($userId === null) {
            return null;
        }

        $userData =
            $this->userRepository->findWithRole(
                $userId
            );

        if (!$userData) {
            return null;
        }

        $misAccesos =
            $this->userRepository
                ->getPermissionsByRoleId(
                    isset($userData['role_id'])
                        ? (int) $userData['role_id']
                        : null
                );

        if (empty($misAccesos)) {
            $misAccesos = ['Acceso Básico'];
        }

        return [
            'user' => $userData,
            'misAccesos' => $misAccesos
        ];
    }

    /**
     * Usuarios del panel administrativo.
     */
    public function getManagedUsers(
        ?int $branchId = null,
        ?string $search = null,
        ?int $roleId = null,
        ?int $isActive = null
    ): array {

        return $this->userRepository
            ->getManagedUsers(
                $branchId,
                $search,
                $roleId,
                $isActive
            );
    }

    public function getManagedUser(
        int $userId,
        ?int $branchId = null
    ): ?array {

        return $this->userRepository
            ->findManagedUser(
                $userId,
                $branchId
            );
    }

    public function getRoles(): array
    {
        return $this->userRepository->getRoles();
    }

    public function getPermissions(): array
    {
        return $this->userRepository
            ->getAllPermissions();
    }

    public function getUserPermissions(
        int $userId
    ): array {

        return $this->userRepository
            ->getUserPermissionOverrides(
                $userId
            );
    }

    /**
     * Crear usuario administrativo.
     *
     * Reglas:
     *
     * SuperAdmin:
     * - Puede crear usuarios en cualquier sede.
     * - Si el usuario creado es SuperAdmin,
     *   branch_id siempre será NULL.
     *
     * Admin:
     * - Solo puede crear usuarios de su propia sede.
     * - No puede crear SuperAdmin.
     */
    public function createUser(
        array $input,
        bool $isSuperAdmin,
        ?int $currentBranchId
    ): int {

        $this->validateName(
            $input['first_name'] ?? '',
            'El nombre es obligatorio.'
        );

        $this->validateName(
            $input['last_name'] ?? '',
            'El apellido es obligatorio.'
        );

        $email =
            strtolower(
                trim($input['email'] ?? '')
            );

        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            throw new DomainException(
                'El correo electrónico no es válido.',
                422
            );
        }

        if (
            $this->userRepository
                ->findByEmail($email)
        ) {
            throw new DomainException(
                'El correo electrónico ya está registrado.',
                409
            );
        }

        $roleId =
            (int) ($input['role_id'] ?? 0);

        $role =
            $this->getRole($roleId);

        $this->validateRoleForActor(
            $role,
            $isSuperAdmin
        );

        /*
         * =========================================================
         * SEDE
         * =========================================================
         *
         * SuperAdmin NO pertenece a ninguna sede.
         *
         * Si el rol seleccionado es SuperAdmin:
         *
         *     branch_id = NULL
         *
         * Si es otro rol:
         *
         *     SuperAdmin actor -> sede seleccionada
         *     Admin actor      -> sede actual
         */

        $roleName =
            strtolower(
                trim($role['name'] ?? '')
            );

        if ($roleName === 'superadmin') {

            $branchId = null;

        } else {

            $branchId =
                $this->resolveBranch(
                    $input['branch_id'] ?? null,
                    $isSuperAdmin,
                    $currentBranchId
                );

            if (!$isSuperAdmin && $branchId === null) {
                throw new DomainException(
                    'El usuario debe pertenecer a la sede del administrador.',
                    422
                );
            }
        }

        /*
         * =========================================================
         * CONTRASEÑA
         * =========================================================
         */

        $password =
            (string) ($input['password'] ?? '');

        if (strlen($password) < 8) {
            throw new DomainException(
                'La contraseña debe tener mínimo 8 caracteres.',
                422
            );
        }

        $id =
            $this->userRepository->createManagedUser([
                'branch_id' =>
                    $branchId,

                'role_id' =>
                    $roleId,

                'first_name' =>
                    trim($input['first_name']),

                'last_name' =>
                    trim($input['last_name']),

                'email' =>
                    $email,

                'phone' =>
                    trim($input['phone'] ?? ''),

                'password_hash' =>
                    password_hash(
                        $password,
                        PASSWORD_BCRYPT
                    ),

                'document_type' =>
                    $input['document_type'] ?? 'CC',

                'document_number' =>
                    trim(
                        $input['document_number'] ?? ''
                    ),

                'avatar' =>
                    'default.png',

                'is_active' =>
                    1
            ]);

        /*
         * Guardar los permisos individuales seleccionados
         * en el formulario de creación.
         */
        $permissions = $input['permissions'] ?? [];

        if (is_array($permissions)) {
            $this->savePermissions(
                $id,
                $permissions,
                $isSuperAdmin,
                $currentBranchId
            );
        }

        return $id;
    }

    /**
     * Actualizar usuario.
     *
     * Reglas:
     *
     * SuperAdmin:
     * - Puede editar usuarios de cualquier sede.
     * - Un usuario con rol SuperAdmin siempre queda con branch_id NULL.
     * - Los demás usuarios pueden tener cualquier sede activa.
     *
     * Admin:
     * - Solo puede editar usuarios de su sede.
     * - La sede del usuario siempre permanece en su sede.
     * - No puede asignar SuperAdmin.
     */
    public function updateUser(
        int $userId,
        array $input,
        bool $isSuperAdmin,
        ?int $currentBranchId,
        int $currentUserId,
        ?array $photo = null,
        bool $removePhoto = false,
        array $permissions = []
    ): bool {

        /*
         * =========================================================
         * 1. BUSCAR USUARIO
         * =========================================================
         */

        $existing =
            $this->userRepository
                ->findManagedUser(
                    $userId,
                    $isSuperAdmin
                        ? null
                        : $currentBranchId
                );

        if (!$existing) {

            throw new DomainException(
                'El usuario no existe o no pertenece a su sede.',
                404
            );
        }

        /*
         * =========================================================
         * 2. NO PERMITIR MODIFICARSE A SÍ MISMO
         * =========================================================
         */

        if ($userId === $currentUserId) {

            throw new DomainException(
                'No puede modificar su propio usuario desde este panel.',
                422
            );
        }

        /*
         * =========================================================
         * 3. ROL
         * =========================================================
         */

        $roleId =
            (int) ($input['role_id'] ?? 0);

        $role =
            $this->getRole(
                $roleId
            );

        $this->validateRoleForActor(
            $role,
            $isSuperAdmin
        );

        $roleName =
            strtolower(
                trim($role['name'] ?? '')
            );

        /*
         * =========================================================
         * 4. EMAIL
         * =========================================================
         */

        $email =
            strtolower(
                trim(
                    $input['email'] ?? ''
                )
            );

        if (
            !filter_var(
                $email,
                FILTER_VALIDATE_EMAIL
            )
        ) {

            throw new DomainException(
                'El correo electrónico no es válido.',
                422
            );
        }

        $emailOwner =
            $this->userRepository
                ->findByEmail(
                    $email
                );

        if (
            $emailOwner &&
            (int) $emailOwner['id'] !== $userId
        ) {

            throw new DomainException(
                'El correo electrónico ya está registrado por otro usuario.',
                409
            );
        }

        /*
         * =========================================================
         * 5. SEDE
         * =========================================================
         *
         * REGLA PRINCIPAL:
         *
         * SuperAdmin -> NULL
         *
         * Esto se determina por el ROL DEL USUARIO OBJETIVO,
         * no por el rol del administrador que está realizando
         * la operación.
         */

        if ($roleName === 'superadmin') {

            $branchId = null;

        } elseif ($isSuperAdmin) {

            $branchId =
                $this->resolveBranch(
                    $input['branch_id'] ?? null,
                    true,
                    null
                );

        } else {

            $branchId =
                (int) $currentBranchId;

            if ($branchId <= 0) {
                throw new DomainException(
                    'El administrador no tiene una sede asignada.',
                    422
                );
            }
        }

        /*
         * =========================================================
         * 6. CONTRASEÑA
         * =========================================================
         */

        $passwordHash = null;

        $password =
            trim(
                (string) (
                    $input['password'] ?? ''
                )
            );

        if ($password !== '') {

            if (strlen($password) < 8) {

                throw new DomainException(
                    'La contraseña debe tener mínimo 8 caracteres.',
                    422
                );
            }

            $passwordHash =
                password_hash(
                    $password,
                    PASSWORD_BCRYPT
                );
        }

        /*
         * =========================================================
         * 7. FOTO
         * =========================================================
         */

        $newAvatar = null;

        if (
            $photo &&
            !empty($photo['tmp_name'])
        ) {

            $newAvatar =
                $this->uploadUserPhoto(
                    $photo,
                    $userId
                );

        } elseif (
            $removePhoto
        ) {

            $newAvatar =
                'default.png';
        }

        /*
         * =========================================================
         * 8. DATOS DEL USUARIO
         * =========================================================
         */

        $userData = [

            'branch_id' =>
                $branchId,

            'role_id' =>
                $roleId,

            'first_name' =>
                trim(
                    $input['first_name'] ?? ''
                ),

            'last_name' =>
                trim(
                    $input['last_name'] ?? ''
                ),

            'email' =>
                $email,

            'phone' =>
                trim(
                    $input['phone'] ?? ''
                ),

            'document_type' =>
                $input['document_type'] ?? 'CC',

            'document_number' =>
                trim(
                    $input['document_number'] ?? ''
                ),

            'is_active' =>
                !empty(
                $input['is_active']
                )
                    ? 1
                    : 0
        ];

        /*
         * Solo modificamos avatar si realmente
         * se seleccionó una foto nueva o se solicitó
         * quitar la actual.
         */

        if ($newAvatar !== null) {

            $userData['avatar'] =
                $newAvatar;
        }

        /*
         * Solo modificamos password si
         * realmente se escribió una nueva.
         */

        if ($passwordHash !== null) {

            $userData['password_hash'] =
                $passwordHash;
        }

        /*
         * =========================================================
         * 9. ACTUALIZAR USUARIO
         * =========================================================
         */

        $updated =
            $this->userRepository
                ->updateManagedUser(
                    $userId,
                    $userData,
                    $isSuperAdmin
                        ? null
                        : $currentBranchId
                );

        if (!$updated) {

            throw new RuntimeException(
                'No fue posible actualizar el usuario.'
            );
        }

        /*
         * =========================================================
         * 10. SINCRONIZAR PERMISOS
         * =========================================================
         *
         * permissions debe contener TODOS los permisos:
         *
         * [
         *     1 => true,
         *     2 => false,
         *     3 => true
         * ]
         *
         * Esto permite revocar permisos heredados del rol.
         */

        if (is_array($permissions)) {

            $this->savePermissions(
                $userId,
                $permissions,
                $isSuperAdmin,
                $currentBranchId
            );
        }

        /*
         * =========================================================
         * 11. ELIMINAR FOTO ANTERIOR
         * =========================================================
         */

        if (
            $newAvatar !== null &&
            !empty($existing['avatar']) &&
            $existing['avatar'] !== 'default.png'
        ) {

            $this->deleteUserPhoto(
                $existing['avatar']
            );
        }

        return true;
    }

    /**
     * Desactivar usuario.
     */
    public function deleteUser(
        int $userId,
        bool $isSuperAdmin,
        ?int $currentBranchId,
        int $currentUserId
    ): bool {

        if ($userId === $currentUserId) {
            throw new DomainException(
                'No puede eliminar su propio usuario.',
                422
            );
        }

        $existing =
            $this->userRepository
                ->findManagedUser(
                    $userId,
                    $isSuperAdmin
                        ? null
                        : $currentBranchId
                );

        if (!$existing) {
            throw new DomainException(
                'El usuario no existe o no pertenece a su sede.',
                404
            );
        }

        return $this->userRepository
            ->deactivateUser(
                $userId,
                $isSuperAdmin
                    ? null
                    : $currentBranchId
            );
    }

    /**
     * Gestionar permisos individuales.
     */
    public function savePermissions(
        int $userId,
        array $permissions,
        bool $isSuperAdmin,
        ?int $currentBranchId
    ): void {

        $user =
            $this->userRepository
                ->findManagedUser(
                    $userId,
                    $isSuperAdmin
                        ? null
                        : $currentBranchId
                );

        if (!$user) {
            throw new DomainException(
                'Usuario no encontrado o fuera de su sede.',
                404
            );
        }

        $allowedPermissions =
            $this->getPermissions();

        $allowedIds = [];

        foreach ($allowedPermissions as $permission) {

            $permissionId =
                (int) $permission['id'];

            /*
             * Admin no puede otorgar permisos
             * críticos a otros usuarios.
             */
            if (
                !$isSuperAdmin &&
                in_array(
                    $permission['slug'],
                    [
                        'users.manage',
                        'settings.update'
                    ],
                    true
                )
            ) {
                continue;
            }

            $allowedIds[] =
                $permissionId;
        }

        $cleanPermissions = [];

        foreach ($permissions as $permissionId => $allowed) {

            $permissionId =
                (int) $permissionId;

            if (
                in_array(
                    $permissionId,
                    $allowedIds,
                    true
                )
            ) {

                $cleanPermissions[$permissionId] =
                    (bool) $allowed;
            }
        }

        $this->userRepository
            ->syncUserPermissions(
                $userId,
                $cleanPermissions
            );
    }

    /**
     * Obtener rol por ID.
     */
    private function getRole(int $roleId): ?array
    {
        foreach (
            $this->userRepository->getRoles()
            as $role
        ) {

            if ((int) $role['id'] === $roleId) {
                return $role;
            }
        }

        return null;
    }


    /**
     * Validar que el usuario autenticado pueda asignar el rol.
     *
     * SuperAdmin:
     * - Puede asignar cualquier rol existente.
     *
     * Admin de sede:
     * - Solo puede asignar staff o customer.
     */
    private function validateRoleForActor(
        ?array $role,
        bool $isSuperAdmin
    ): void {
        if (!$role) {
            throw new DomainException(
                'El rol seleccionado no existe.',
                422
            );
        }

        $roleName = strtolower(
            trim((string) ($role['name'] ?? ''))
        );

        if ($isSuperAdmin) {
            return;
        }

        if (!in_array($roleName, ['staff', 'customer'], true)) {
            throw new DomainException(
                'Un administrador de sede solo puede asignar los roles staff o customer.',
                403
            );
        }
    }


    /**
     * Resolver sede.
     *
     * Si el actor es SuperAdmin:
     * - Debe seleccionar una sede para usuarios normales.
     *
     * Si el actor es Admin:
     * - Siempre utiliza su propia sede.
     */
    private function resolveBranch(
        $requestedBranchId,
        bool $isSuperAdmin,
        ?int $currentBranchId
    ): ?int {

        if (!$isSuperAdmin) {
            return $currentBranchId;
        }

        if (
            $requestedBranchId === null ||
            $requestedBranchId === ''
        ) {
            throw new DomainException(
                'Debe seleccionar una sede.',
                422
            );
        }

        $branchId =
            (int) $requestedBranchId;

        $branch =
            $this->branchRepository
                ->findById($branchId);

        if (
            !$branch ||
            (int) $branch['is_active'] !== 1
        ) {
            throw new DomainException(
                'La sede seleccionada no existe o está inactiva.',
                422
            );
        }

        return $branchId;
    }

    /**
     * Validar nombre.
     */
    private function validateName(
        string $value,
        string $message
    ): void {

        if (trim($value) === '') {
            throw new DomainException(
                $message,
                422
            );
        }
    }

    /**
     * Subir fotografía de usuario.
     */
    private function uploadUserPhoto(
        array $file,
        int $userId
    ): string {

        if (
            empty($file['tmp_name']) ||
            !is_uploaded_file(
                $file['tmp_name']
            )
        ) {

            throw new DomainException(
                'La fotografía no es una carga válida.',
                422
            );
        }

        if (
            !isset($file['size']) ||
            (int) $file['size'] <= 0
        ) {

            throw new DomainException(
                'La fotografía está vacía.',
                422
            );
        }

        /*
         * Máximo 5 MB.
         */

        if (
            (int) $file['size'] >
            5 * 1024 * 1024
        ) {

            throw new DomainException(
                'La fotografía no puede superar los 5 MB.',
                422
            );
        }

        /*
         * Validación MIME real.
         */

        $mimeType = null;

        if (class_exists('finfo')) {

            $finfo =
                new finfo(
                    FILEINFO_MIME_TYPE
                );

            $mimeType =
                $finfo->file(
                    $file['tmp_name']
                );

        } elseif (
            function_exists(
                'mime_content_type'
            )
        ) {

            $mimeType =
                mime_content_type(
                    $file['tmp_name']
                );
        }

        $allowedMimeTypes = [

            'image/jpeg' =>
                'jpg',

            'image/png' =>
                'png',

            'image/webp' =>
                'webp'
        ];

        if (
            !isset(
                $allowedMimeTypes[
                $mimeType
                ]
            )
        ) {

            throw new DomainException(
                'El formato de la fotografía no está permitido. Usa JPG, PNG o WEBP.',
                422
            );
        }

        $extension =
            $allowedMimeTypes[
            $mimeType
            ];

        /*
         * Directorio:
         *
         * public/assets/img/users/
         */

        $directory =
            __DIR__ .
            '/../../public/assets/img/users';

        if (
            !is_dir(
                $directory
            )
        ) {

            if (
                !mkdir(
                    $directory,
                    0755,
                    true
                ) &&
                !is_dir($directory)
            ) {

                throw new RuntimeException(
                    'No fue posible crear el directorio de fotografías.'
                );
            }
        }

        /*
         * Nombre único.
         */

        $filename =
            'user-' .
            $userId .
            '-' .
            bin2hex(
                random_bytes(8)
            ) .
            '.' .
            $extension;

        $destination =
            $directory .
            DIRECTORY_SEPARATOR .
            $filename;

        if (
            !move_uploaded_file(
                $file['tmp_name'],
                $destination
            )
        ) {

            throw new RuntimeException(
                'No fue posible guardar la fotografía en el servidor.'
            );
        }

        return $filename;
    }

    /**
     * Eliminar fotografía anterior.
     */
    private function deleteUserPhoto(
        string $filename
    ): void {

        $filename =
            basename(
                $filename
            );

        if (
            $filename === '' ||
            $filename === 'default.png'
        ) {

            return;
        }

        $path =
            __DIR__ .
            '/../../public/assets/img/users/' .
            $filename;

        if (
            is_file($path)
        ) {

            @unlink($path);
        }
    }
}