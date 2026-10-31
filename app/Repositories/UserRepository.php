<?php
// app/Repositories/UserRepository.php

class UserRepository
{
    private PDO $db;

    public function __construct()
    {
        $this->db = Database::getConnection();
    }

    /**
     * Buscar usuario por email.
     */
    public function findByEmail(string $email): ?array
    {
        $sql = "
            SELECT
                u.*,
                r.name AS role_name,
                b.name AS branch_name
            FROM users u
            INNER JOIN roles r
                ON r.id = u.role_id
            LEFT JOIN branches b
                ON b.id = u.branch_id
            WHERE LOWER(u.email) = LOWER(:email)
            LIMIT 1
        ";

        $stmt = $this->db->prepare($sql);

        $stmt->execute([
            'email' => $email
        ]);

        $user = $stmt->fetch(PDO::FETCH_ASSOC);

        return $user ?: null;
    }

    /**
     * Buscar usuario por ID incluyendo rol y sede.
     */
    public function findWithRole(int $userId): ?array
    {
        $sql = "
            SELECT
                u.*,
                r.name AS role_name,
                r.description AS role_description,
                b.name AS branch_name
            FROM users u
            INNER JOIN roles r
                ON r.id = u.role_id
            LEFT JOIN branches b
                ON b.id = u.branch_id
            WHERE u.id = :id
            LIMIT 1
        ";

        $stmt = $this->db->prepare($sql);

        $stmt->execute([
            'id' => $userId
        ]);

        $user = $stmt->fetch(PDO::FETCH_ASSOC);

        return $user ?: null;
    }

    /**
     * Obtener usuarios administrables.
     *
     * $branchId = null => SuperAdmin / todas las sedes.
     */
    public function getManagedUsers(
        ?int $branchId = null,
        ?string $search = null,
        ?int $roleId = null,
        ?int $isActive = null
    ): array {

        $sql = "
            SELECT
                u.id,
                u.branch_id,
                u.role_id,
                u.first_name,
                u.last_name,
                u.email,
                u.phone,
                u.document_type,
                u.document_number,
                u.avatar,
                u.is_active,
                u.created_at,
                u.updated_at,

                r.name AS role_name,
                r.description AS role_description,

                b.name AS branch_name,
                b.code AS branch_code

            FROM users u

            INNER JOIN roles r
                ON r.id = u.role_id

            LEFT JOIN branches b
                ON b.id = u.branch_id

            WHERE 1 = 1
        ";

        $params = [];

        if ($branchId !== null) {
            $sql .= "
                AND u.branch_id = :branch_id
            ";

            $params['branch_id'] = $branchId;
        }

        if ($search !== null && trim($search) !== '') {

            $sql .= "
                AND (
                    u.first_name LIKE :search
                    OR u.last_name LIKE :search
                    OR u.email LIKE :search
                    OR u.phone LIKE :search
                    OR u.document_number LIKE :search
                )
            ";

            $params['search'] = '%' . trim($search) . '%';
        }

        if ($roleId !== null) {
            $sql .= "
                AND u.role_id = :role_id
            ";

            $params['role_id'] = $roleId;
        }

        if ($isActive !== null) {
            $sql .= "
                AND u.is_active = :is_active
            ";

            $params['is_active'] = $isActive;
        }

        $sql .= "
            ORDER BY
                u.is_active DESC,
                u.first_name ASC,
                u.last_name ASC
        ";

        $stmt = $this->db->prepare($sql);
        $stmt->execute($params);

        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    /**
     * Obtener un usuario administrable respetando sede.
     *
     * branchId = null => todas las sedes.
     */
    public function findManagedUser(
        int $userId,
        ?int $branchId = null
    ): ?array {

        $sql = "
            SELECT
                u.id,
                u.branch_id,
                u.role_id,
                u.first_name,
                u.last_name,
                u.email,
                u.phone,
                u.document_type,
                u.document_number,
                u.avatar,
                u.is_active,
                u.created_at,
                u.updated_at,

                r.name AS role_name,
                r.description AS role_description,

                b.name AS branch_name,
                b.code AS branch_code

            FROM users u

            INNER JOIN roles r
                ON r.id = u.role_id

            LEFT JOIN branches b
                ON b.id = u.branch_id

            WHERE u.id = :id
        ";

        $params = [
            'id' => $userId
        ];

        if ($branchId !== null) {

            $sql .= "
                AND u.branch_id = :branch_id
            ";

            $params['branch_id'] = $branchId;
        }

        $sql .= " LIMIT 1";

        $stmt = $this->db->prepare($sql);
        $stmt->execute($params);

        $user = $stmt->fetch(PDO::FETCH_ASSOC);

        return $user ?: null;
    }

    /**
     * Roles disponibles.
     */
    public function getRoles(): array
    {
        $stmt = $this->db->query("
            SELECT
                id,
                name,
                description
            FROM roles
            ORDER BY id ASC
        ");

        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    /**
     * Permisos disponibles.
     */
    public function getAllPermissions(): array
    {
        $stmt = $this->db->query("
            SELECT
                id,
                slug,
                module
            FROM permissions
            ORDER BY
                module ASC,
                slug ASC
        ");

        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    /**
     * Permisos heredados por rol.
     */
    public function getPermissionsByRoleId(?int $roleId): array
    {
        if ($roleId === null) {
            return [];
        }

        $sql = "
            SELECT DISTINCT
                p.slug
            FROM role_permissions rp
            INNER JOIN permissions p
                ON p.id = rp.permission_id
            WHERE rp.role_id = :role_id
            ORDER BY p.slug ASC
        ";

        $stmt = $this->db->prepare($sql);

        $stmt->execute([
            'role_id' => $roleId
        ]);

        return $stmt->fetchAll(PDO::FETCH_COLUMN);
    }

    /**
     * Permisos personalizados de un usuario.
     */
    public function getUserPermissionOverrides(
        int $userId
    ): array {

        $sql = "
            SELECT
                up.permission_id,
                p.slug,
                p.module,
                up.is_allowed
            FROM user_permissions up

            INNER JOIN permissions p
                ON p.id = up.permission_id

            WHERE up.user_id = :user_id

            ORDER BY
                p.module ASC,
                p.slug ASC
        ";

        $stmt = $this->db->prepare($sql);

        $stmt->execute([
            'user_id' => $userId
        ]);

        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    /**
     * Buscar usuario para recuperación de contraseña.
     * Acepta correo electrónico, teléfono o documento.
     */
    public function findForPasswordRecovery(string $identifier): ?array
    {
        $identifier = trim($identifier);
        if ($identifier === '') {
            return null;
        }

        $stmt = $this->db->prepare("\n            SELECT id, first_name, last_name, email, phone, document_number, is_active\n            FROM users\n            WHERE LOWER(email) = LOWER(:identifier)\n               OR phone = :phone\n               OR document_number = :document_number\n            LIMIT 1\n        ");

        $stmt->execute([
            'identifier' => $identifier,
            'phone' => $identifier,
            'document_number' => $identifier
        ]);

        $user = $stmt->fetch(PDO::FETCH_ASSOC);
        return $user ?: null;
    }

    /** Eliminar tokens de recuperación anteriores. */
    public function deletePasswordResetTokens(int $userId): void
    {
        $stmt = $this->db->prepare("\n            DELETE FROM user_tokens\n            WHERE user_id = :user_id\n              AND type = 'password_reset'\n        ");
        $stmt->execute(['user_id' => $userId]);
    }

    /**
     * Crear/reemplazar token de recuperación.
     *
     * Solo puede existir un token password_reset
     * vigente por usuario.
     */
    public function createPasswordResetToken(
        int $userId,
        string $tokenHash,
        DateTimeInterface $expiresAt
    ): void {

        /*
         * Eliminar cualquier token anterior
         * del usuario.
         */
        $delete = $this->db->prepare("
        DELETE FROM user_tokens
        WHERE user_id = :user_id
          AND type = 'password_reset'
    ");

        $delete->execute([
            'user_id' => $userId
        ]);


        /*
         * Crear el nuevo token.
         */
        $insert = $this->db->prepare("
        INSERT INTO user_tokens (
            user_id,
            token_hash,
            type,
            expires_at
        )
        VALUES (
            :user_id,
            :token_hash,
            'password_reset',
            :expires_at
        )
    ");

        $insert->execute([
            'user_id' => $userId,
            'token_hash' => $tokenHash,
            'expires_at' => $expiresAt->format('Y-m-d H:i:s')
        ]);
    }

    /** Obtener un token de recuperación vigente. */
    public function findValidPasswordResetToken(string $tokenHash): ?array
    {
        $stmt = $this->db->prepare("\n            SELECT\n                ut.id AS token_id,\n                ut.user_id,\n                ut.expires_at,\n                u.email,\n                u.first_name,\n                u.last_name,\n                u.is_active\n            FROM user_tokens ut\n            INNER JOIN users u ON u.id = ut.user_id\n            WHERE ut.token_hash = :token_hash\n              AND ut.type = 'password_reset'\n              AND ut.expires_at > NOW()\n            LIMIT 1\n        ");
        $stmt->execute(['token_hash' => $tokenHash]);
        $token = $stmt->fetch(PDO::FETCH_ASSOC);
        return $token ?: null;
    }

    /** Registrar evento de autenticación. */
    public function logAuthEvent(?int $userId, string $event): void
    {
        $stmt = $this->db->prepare("\n            INSERT INTO auth_logs (user_id, ip_address, user_agent, event)\n            VALUES (:user_id, :ip_address, :user_agent, :event)\n        ");
        $stmt->execute([
            'user_id' => $userId,
            'ip_address' => $_SERVER['REMOTE_ADDR'] ?? '0.0.0.0',
            'user_agent' => substr($_SERVER['HTTP_USER_AGENT'] ?? '', 0, 255),
            'event' => $event
        ]);
    }

    /**
     * Crear usuario.
     */
    public function createManagedUser(array $data): int
    {
        $sql = "
            INSERT INTO users
            (
                branch_id,
                role_id,
                first_name,
                last_name,
                email,
                phone,
                password_hash,
                password_changed_at,
                document_type,
                document_number,
                avatar,
                is_active
            )
            VALUES
            (
                :branch_id,
                :role_id,
                :first_name,
                :last_name,
                :email,
                :phone,
                :password_hash,
                NOW(),
                :document_type,
                :document_number,
                :avatar,
                :is_active
            )
        ";

        $stmt = $this->db->prepare($sql);

        $stmt->execute([
            'branch_id' =>
                $data['branch_id'],

            'role_id' =>
                $data['role_id'],

            'first_name' =>
                $data['first_name'],

            'last_name' =>
                $data['last_name'],

            'email' =>
                $data['email'],

            'phone' =>
                $data['phone'],

            'password_hash' =>
                $data['password_hash'],

            'document_type' =>
                $data['document_type'],

            'document_number' =>
                $data['document_number'],

            'avatar' =>
                $data['avatar'] ?? 'default.png',

            'is_active' =>
                $data['is_active'] ?? 1
        ]);

        return (int) $this->db->lastInsertId();
    }

    /**
     * Actualizar usuario.
     */
    public function updateManagedUser(
        int $userId,
        array $data,
        ?int $branchId = null
    ): bool {

        $sql = "
        UPDATE users
        SET
            role_id = :role_id,
            first_name = :first_name,
            last_name = :last_name,
            email = :email,
            phone = :phone,
            document_type = :document_type,
            document_number = :document_number,
            is_active = :is_active
    ";


        $params = [

            'role_id' =>
                $data['role_id'],

            'first_name' =>
                $data['first_name'],

            'last_name' =>
                $data['last_name'],

            'email' =>
                $data['email'],

            'phone' =>
                $data['phone'],

            'document_type' =>
                $data['document_type'],

            'document_number' =>
                $data['document_number'],

            'is_active' =>
                $data['is_active'],

            'id' =>
                $userId
        ];


        /*
         * Sede.
         */

        if (
            array_key_exists(
                'branch_id',
                $data
            )
        ) {

            $sql .= ",
            branch_id = :new_branch_id
        ";

            $params['new_branch_id'] =
                $data['branch_id'];
        }


        /*
         * Avatar.
         */

        if (
            array_key_exists(
                'avatar',
                $data
            ) &&
            $data['avatar'] !== ''
        ) {

            $sql .= ",
            avatar = :avatar
        ";

            $params['avatar'] =
                $data['avatar'];
        }


        /*
         * Contraseña.
         */

        if (
            isset(
                $data['password_hash']
            ) &&
            $data['password_hash'] !== ''
        ) {

            $sql .= ",
            password_hash = :password_hash
        ";

            $params['password_hash'] =
                $data['password_hash'];
        }


        /*
         * WHERE.
         */

        $sql .= "
        WHERE id = :id
    ";


        /*
         * Scope por sede.
         */

        if ($branchId !== null) {

            $sql .= "
            AND branch_id = :branch_scope
        ";

            $params['branch_scope'] =
                $branchId;
        }


        $stmt =
            $this->db->prepare(
                $sql
            );


        return $stmt->execute(
            $params
        );
    }

    /**
     * Desactivar usuario respetando sede.
     */
    public function deactivateUser(
        int $userId,
        ?int $branchId = null
    ): bool {

        $sql = "
            UPDATE users
            SET is_active = 0
            WHERE id = :id
        ";

        $params = [
            'id' => $userId
        ];

        if ($branchId !== null) {

            $sql .= "
                AND branch_id = :branch_id
            ";

            $params['branch_id'] =
                $branchId;
        }

        $stmt = $this->db->prepare($sql);

        return $stmt->execute($params);
    }

    /**
     * Sincronizar permisos personalizados.
     *
     * $permissions = [
     *     permissionId => true/false
     * ]
     */
    public function syncUserPermissions(
        int $userId,
        array $permissions
    ): void {

        $this->db->beginTransaction();

        try {

            $delete = $this->db->prepare("
                DELETE FROM user_permissions
                WHERE user_id = :user_id
            ");

            $delete->execute([
                'user_id' => $userId
            ]);

            if (!empty($permissions)) {

                $insert = $this->db->prepare("
                    INSERT INTO user_permissions
                    (
                        user_id,
                        permission_id,
                        is_allowed
                    )
                    VALUES
                    (
                        :user_id,
                        :permission_id,
                        :is_allowed
                    )
                ");

                foreach ($permissions as $permissionId => $isAllowed) {

                    $insert->execute([
                        'user_id' =>
                            $userId,

                        'permission_id' =>
                            (int) $permissionId,

                        'is_allowed' =>
                            $isAllowed ? 1 : 0
                    ]);
                }
            }

            $this->db->commit();

        } catch (Throwable $e) {

            if ($this->db->inTransaction()) {
                $this->db->rollBack();
            }

            throw $e;
        }
    }

    /**
     * Actualizar contraseña.
     */
    public function updatePasswordHash(
        int $userId,
        string $passwordHash
    ): bool {

        $sql = "
        UPDATE users
        SET
            password_hash = :password_hash,
            password_changed_at = NOW()
        WHERE id = :id
    ";

        $stmt =
            $this->db->prepare($sql);

        return $stmt->execute([
            'password_hash' =>
                $passwordHash,

            'id' =>
                $userId
        ]);
    }

    /**
     * Crear usuario cliente.
     */
    public function createCustomer(
        string $firstName,
        string $lastName,
        string $email,
        string $phone,
        string $passwordHash
    ): int {

        $sql = "
            INSERT INTO users
            (
                branch_id,
                role_id,
                first_name,
                last_name,
                email,
                phone,
                password_hash,
                password_changed_at,
                is_active
            )
            VALUES
            (
                NULL,
                4,
                :first_name,
                :last_name,
                :email,
                :phone,
                :password_hash,
                NOW(),
                1
            )
        ";

        $stmt = $this->db->prepare($sql);

        $stmt->execute([
            'first_name' => $firstName,
            'last_name' => $lastName,
            'email' => $email,
            'phone' => $phone,
            'password_hash' => $passwordHash
        ]);

        return (int) $this->db->lastInsertId();
    }

    /**
     * Obtiene los usuarios que deben recibir
     * notificaciones de nuevas reservas de una sede.
     *
     * Incluye:
     * - Todos los SuperAdmin.
     * - Admins activos de la sede correspondiente.
     */
    public function getReservationNotificationRecipients(
        int $branchId
    ): array {

        $sql = "
        SELECT
            u.id,
            u.branch_id,
            u.first_name,
            u.last_name,
            u.email,
            r.name AS role_name
        FROM users u
        INNER JOIN roles r
            ON r.id = u.role_id
        WHERE u.is_active = 1
          AND (
                LOWER(r.name) = 'superadmin'
                OR (
                    LOWER(r.name) = 'admin'
                    AND u.branch_id = :branch_id
                )
          )
        ORDER BY
            CASE
                WHEN LOWER(r.name) = 'superadmin'
                    THEN 1
                ELSE 2
            END,
            u.first_name ASC,
            u.last_name ASC
    ";

        $stmt = $this->db->prepare($sql);

        $stmt->execute([
            'branch_id' => $branchId
        ]);

        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

}