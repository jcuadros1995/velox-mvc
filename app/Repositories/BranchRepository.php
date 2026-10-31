<?php
// app/Repositories/BranchRepository.php

class BranchRepository
{
    private PDO $db;

    public function __construct()
    {
        $this->db = Database::getConnection();
    }

    /**
     * =========================================================
     * SEDES ACTIVAS PARA RESERVAS
     *
     * Devuelve la información de cada sede
     * junto con TODAS sus imágenes.
     *
     * La favorita se identifica mediante is_favorite = 1.
     * =========================================================
     */
    public function getActiveBranches(): array
    {
        $sql = "
        SELECT
            b.id,
            b.code,
            b.name,
            b.phone,
            b.email,
            b.address,
            b.is_active

        FROM branches b

        WHERE b.is_active = 1

        ORDER BY
            b.name ASC
    ";


        $stmt =
            $this->db->query(
                $sql
            );


        $branches =
            $stmt->fetchAll(
                PDO::FETCH_ASSOC
            );


        if (!$branches) {
            return [];
        }


        /*
         * =====================================================
         * OBTENER TODAS LAS IMÁGENES
         * =====================================================
         */

        $branchIds =
            array_map(
                static fn(array $branch): int =>
                (int) $branch['id'],
                $branches
            );


        $placeholders =
            implode(
                ',',
                array_fill(
                    0,
                    count($branchIds),
                    '?'
                )
            );


        $imageStmt =
            $this->db->prepare("
            SELECT
                id,
                branch_id,
                file_path,
                original_name,
                mime_type,
                width,
                height,
                sort_order,
                is_favorite

            FROM branch_images

            WHERE branch_id IN ($placeholders)

            ORDER BY
                branch_id ASC,
                is_favorite DESC,
                sort_order ASC,
                id ASC
        ");


        $imageStmt->execute(
            $branchIds
        );


        $images =
            $imageStmt->fetchAll(
                PDO::FETCH_ASSOC
            );


        /*
         * =====================================================
         * AGRUPAR IMÁGENES POR SEDE
         * =====================================================
         */

        $imagesByBranch = [];


        foreach (
            $images as $image
        ) {

            $branchId =
                (int) $image['branch_id'];


            $image['url'] =
                rtrim(
                    APP_URL,
                    '/'
                ) .
                '/' .
                ltrim(
                    $image['file_path'],
                    '/\\'
                );


            $image['is_favorite'] =
                (int) (
                    $image['is_favorite']
                    ?? 0
                ) === 1;


            $imagesByBranch[
            $branchId
            ][] = $image;
        }


        /*
         * =====================================================
         * AGREGAR GALERÍA A CADA SEDE
         * =====================================================
         */

        foreach (
            $branches as &$branch
        ) {

            $branchId =
                (int) $branch['id'];


            $branch['images'] =
                $imagesByBranch[
                $branchId
                ] ?? [];


            /*
             * Imagen favorita.
             *
             * Si por algún motivo no existe favorita,
             * usamos la primera imagen.
             */

            $favoriteImage =
                null;


            foreach (
                $branch['images']
                as $image
            ) {

                if (
                    !empty(
                    $image['is_favorite']
                    )
                ) {

                    $favoriteImage =
                        $image;

                    break;
                }
            }


            if (
                !$favoriteImage &&
                !empty(
                $branch['images']
                )
            ) {

                $favoriteImage =
                    $branch['images'][0];
            }


            $branch['image_path'] =
                $favoriteImage['file_path']
                ?? null;


            $branch['image_url'] =
                $favoriteImage['url']
                ?? null;
        }


        unset($branch);


        return $branches;
    }

    /**
     * Obtener todas las sedes para administración.
     */
    public function getManagedBranches(
        ?string $search = null,
        ?int $isActive = null
    ): array {

        $sql = "
            SELECT
                id,
                code,
                name,
                phone,
                email,
                address,
                is_active
            FROM branches
            WHERE 1 = 1
        ";

        $params = [];

        if (
            $search !== null &&
            trim($search) !== ''
        ) {

            $sql .= "
                AND (
                    name LIKE :search
                    OR code LIKE :search
                    OR phone LIKE :search
                    OR email LIKE :search
                    OR address LIKE :search
                )
            ";

            $params['search'] =
                '%' . trim($search) . '%';
        }

        if ($isActive !== null) {

            $sql .= "
                AND is_active = :is_active
            ";

            $params['is_active'] =
                $isActive;
        }

        $sql .= "
            ORDER BY
                is_active DESC,
                name ASC
        ";

        $stmt =
            $this->db->prepare($sql);

        $stmt->execute($params);

        return $stmt->fetchAll(
            PDO::FETCH_ASSOC
        );
    }

    /**
     * Obtener una sede por ID.
     */
    public function findById(
        int $branchId
    ): ?array {

        $stmt = $this->db->prepare("
            SELECT
                id,
                code,
                name,
                phone,
                email,
                address,
                is_active
            FROM branches
            WHERE id = :id
            LIMIT 1
        ");

        $stmt->execute([
            'id' => $branchId
        ]);

        $result =
            $stmt->fetch(
                PDO::FETCH_ASSOC
            );

        return $result ?: null;
    }

    /**
     * Busca una sede activa para validar pedidos públicos.
     */
    public function findActiveById(int $branchId): ?array
    {
        if ($branchId <= 0) {
            return null;
        }

        $stmt = $this->db->prepare("
            SELECT
                id,
                code,
                name,
                phone,
                email,
                address,
                is_active
            FROM branches
            WHERE id = :id
              AND is_active = 1
            LIMIT 1
        ");

        $stmt->execute([
            'id' => $branchId
        ]);

        $branch = $stmt->fetch(PDO::FETCH_ASSOC);

        return $branch ?: null;
    }

    /**
     * Selecciona una sede activa que tenga inventario suficiente
     * para preparar todos los productos que requieren control de stock.
     *
     * Si ningún producto exige inventario, utiliza la primera sede activa.
     */
    public function findActiveBranchWithInventory(array $orderItems): ?array
    {
        $requiredByItem = [];

        foreach ($orderItems as $item) {
            if (empty($item['requires_inventory'])) {
                continue;
            }

            $itemId = (int) ($item['item_id'] ?? 0);
            $quantity = (int) ($item['inventory_quantity'] ?? 0);

            if ($itemId <= 0 || $quantity <= 0) {
                return null;
            }

            $requiredByItem[$itemId] = ($requiredByItem[$itemId] ?? 0) + $quantity;
        }

        $sql = "
            SELECT
                b.id,
                b.code,
                b.name,
                b.phone,
                b.email,
                b.address,
                b.is_active
            FROM branches b
            WHERE b.is_active = 1
        ";

        $params = [];

        foreach ($requiredByItem as $index => $required) {
            $itemParameter = 'inventory_item_' . $index;
            $quantityParameter = 'inventory_quantity_' . $index;

            $sql .= "
                AND EXISTS (
                    SELECT 1
                    FROM inventory inventory_check
                    WHERE inventory_check.branch_id = b.id
                      AND inventory_check.item_id = :" . $itemParameter . "
                      AND inventory_check.stock_quantity >= :" . $quantityParameter . "
                )
            ";

            $params[$itemParameter] = (int) $index;
            $params[$quantityParameter] = (int) $required;
        }

        $sql .= " ORDER BY b.name ASC, b.id ASC LIMIT 1";

        $stmt = $this->db->prepare($sql);
        $stmt->execute($params);

        $branch = $stmt->fetch(PDO::FETCH_ASSOC);

        return $branch ?: null;
    }

    /**
     * Buscar sede por código.
     */
    public function findByCode(
        string $code,
        ?int $excludeId = null
    ): ?array {

        $sql = "
            SELECT
                id,
                code,
                name,
                phone,
                email,
                address,
                is_active
            FROM branches
            WHERE UPPER(code) = UPPER(:code)
        ";

        $params = [
            'code' => trim($code)
        ];

        if ($excludeId !== null) {

            $sql .= "
                AND id <> :exclude_id
            ";

            $params['exclude_id'] =
                $excludeId;
        }

        $sql .= "
            LIMIT 1
        ";

        $stmt =
            $this->db->prepare($sql);

        $stmt->execute($params);

        $result =
            $stmt->fetch(
                PDO::FETCH_ASSOC
            );

        return $result ?: null;
    }

    /**
     * Crear sede.
     */
    public function create(
        array $data
    ): int {

        $stmt = $this->db->prepare("
            INSERT INTO branches (
                code,
                name,
                phone,
                email,
                address,
                is_active
            )
            VALUES (
                :code,
                :name,
                :phone,
                :email,
                :address,
                :is_active
            )
        ");

        $stmt->execute([
            'code' =>
                $data['code'],

            'name' =>
                $data['name'],

            'phone' =>
                $data['phone'],

            'email' =>
                $data['email'],

            'address' =>
                $data['address'],

            'is_active' =>
                $data['is_active']
        ]);

        return (int) $this->db->lastInsertId();
    }

    /**
     * Actualizar sede.
     */
    public function update(
        int $branchId,
        array $data
    ): bool {

        $stmt = $this->db->prepare("
            UPDATE branches
            SET
                code = :code,
                name = :name,
                phone = :phone,
                email = :email,
                address = :address,
                is_active = :is_active
            WHERE id = :id
            LIMIT 1
        ");

        $stmt->execute([
            'id' =>
                $branchId,

            'code' =>
                $data['code'],

            'name' =>
                $data['name'],

            'phone' =>
                $data['phone'],

            'email' =>
                $data['email'],

            'address' =>
                $data['address'],

            'is_active' =>
                $data['is_active']
        ]);

        return $stmt->rowCount() > 0;
    }

    /**
     * =========================================================
     * OBTENER USUARIOS QUE PUEDEN ELIMINARSE JUNTO CON LA SEDE
     * =========================================================
     *
     * Solo elimina empleados administrativos/operativos
     * pertenecientes exclusivamente a esta sede.
     *
     * SuperAdmin y clientes no se eliminan.
     */
    public function getBranchUsersForDeletion(
        int $branchId
    ): array {

        $stmt =
            $this->db->prepare("
            SELECT DISTINCT
                u.id,
                u.avatar

            FROM users u

            WHERE u.role_id IN (2, 3)

              AND (

                    /*
                     * Usuario con branch_id directo.
                     * Solo se elimina si no pertenece
                     * también a otra sede.
                     */
                    (
                        u.branch_id = :branch_id_direct

                        AND NOT EXISTS (
                            SELECT 1
                            FROM branch_users bu_other
                            WHERE
                                bu_other.user_id = u.id
                                AND bu_other.branch_id <> :branch_id_other
                        )
                    )

                    OR

                    /*
                     * Usuario asociado mediante branch_users.
                     * Solo se elimina si esta es su única sede.
                     */
                    (
                        EXISTS (
                            SELECT 1
                            FROM branch_users bu
                            WHERE
                                bu.user_id = u.id
                                AND bu.branch_id = :branch_id_map
                        )

                        AND NOT EXISTS (
                            SELECT 1
                            FROM branch_users bu_other
                            WHERE
                                bu_other.user_id = u.id
                                AND bu_other.branch_id <> :branch_id_other_map
                        )

                        AND (
                            u.branch_id IS NULL
                            OR u.branch_id = :branch_id_map_direct
                        )
                    )

              )
        ");


        $stmt->execute([
            'branch_id_direct' =>
                $branchId,

            'branch_id_other' =>
                $branchId,

            'branch_id_map' =>
                $branchId,

            'branch_id_other_map' =>
                $branchId,

            'branch_id_map_direct' =>
                $branchId
        ]);


        return $stmt->fetchAll(
            PDO::FETCH_ASSOC
        );
    }

    /**
     * =========================================================
     * ELIMINAR SEDE Y TODA SU INFORMACIÓN RELACIONADA
     * =========================================================
     */
    public function deleteBranchCompletely(
        int $branchId,
        array $userIds = []
    ): bool {

        $this->db->beginTransaction();


        try {

            /*
             * =================================================
             * 1. BOOKINGS LEGACY
             * =================================================
             */

            $stmt =
                $this->db->prepare("
                DELETE FROM bookings
                WHERE branch_id = :branch_id
            ");

            $stmt->execute([
                'branch_id' =>
                    $branchId
            ]);


            /*
             * =================================================
             * 2. RESERVAS
             *
             * Primero eliminamos las reservas.
             * reservation_tables tiene ON DELETE CASCADE
             * sobre reservation_id.
             * =================================================
             */

            $stmt =
                $this->db->prepare("
                DELETE FROM restaurant_reservations
                WHERE branch_id = :branch_id
            ");

            $stmt->execute([
                'branch_id' =>
                    $branchId
            ]);


            /*
             * =================================================
             * 3. REGISTROS DE CUPONES Y PAGOS DEL ECOMMERCE
             *
             * Estas tablas no tienen FK física para mantener
             * compatibilidad con instalaciones existentes.
             * Se limpian antes de eliminar los pedidos.
             * =================================================
             */

            $stmt = $this->db->prepare("
                UPDATE coupons c
                SET usage_count = GREATEST(
                    usage_count - (
                        SELECT COUNT(*)
                        FROM coupon_redemptions cr
                        INNER JOIN orders o ON o.id = cr.order_id
                        WHERE o.branch_id = :branch_id_usage
                          AND cr.coupon_id = c.id
                    ),
                    0
                )
                WHERE EXISTS (
                    SELECT 1
                    FROM coupon_redemptions cr
                    INNER JOIN orders o ON o.id = cr.order_id
                    WHERE o.branch_id = :branch_id_exists
                      AND cr.coupon_id = c.id
                )
            ");
            $stmt->execute([
                'branch_id_usage' => $branchId,
                'branch_id_exists' => $branchId
            ]);

            $stmt = $this->db->prepare("
                DELETE pt
                FROM payment_transactions pt
                INNER JOIN orders o ON o.id = pt.order_id
                WHERE o.branch_id = :branch_id
            ");
            $stmt->execute(['branch_id' => $branchId]);

            $stmt = $this->db->prepare("
                DELETE cr
                FROM coupon_redemptions cr
                INNER JOIN orders o ON o.id = cr.order_id
                WHERE o.branch_id = :branch_id
            ");
            $stmt->execute(['branch_id' => $branchId]);


            /*
             * =================================================
             * 4. ÓRDENES
             *
             * order_items y payments tienen
             * ON DELETE CASCADE desde orders.
             * =================================================
             */

            $stmt =
                $this->db->prepare("
                DELETE FROM orders
                WHERE branch_id = :branch_id
            ");

            $stmt->execute([
                'branch_id' =>
                    $branchId
            ]);


            /*
             * =================================================
             * 4. NOTIFICACIONES DE LA SEDE
             * =================================================
             */

            $stmt =
                $this->db->prepare("
                DELETE FROM notifications
                WHERE branch_id = :branch_id
            ");

            $stmt->execute([
                'branch_id' =>
                    $branchId
            ]);


            /*
             * =================================================
             * 5. ELIMINAR EMPLEADOS EXCLUSIVOS
             * =================================================
             */

            if (
                !empty(
                $userIds
                )
            ) {

                $placeholders =
                    implode(
                        ',',
                        array_fill(
                            0,
                            count(
                                $userIds
                            ),
                            '?'
                        )
                    );


                $stmt =
                    $this->db->prepare("
                    DELETE FROM users
                    WHERE id IN ($placeholders)
                ");


                $stmt->execute(
                    array_map(
                        'intval',
                        $userIds
                    )
                );
            }


            /*
             * =================================================
             * 6. ELIMINAR SEDE
             *
             * Aquí entran los ON DELETE CASCADE existentes:
             *
             * - branch_business_hours
             * - branch_closures
             * - branch_images
             * - branch_users
             * - inventory
             * - reservation_settings
             * - restaurant_tables
             * - system_settings
             *
             * =================================================
             */

            $stmt =
                $this->db->prepare("
                DELETE FROM branches
                WHERE id = :branch_id
                LIMIT 1
            ");


            $stmt->execute([
                'branch_id' =>
                    $branchId
            ]);


            if (
                $stmt->rowCount() !== 1
            ) {

                throw new RuntimeException(
                    'No fue posible eliminar la sede.'
                );
            }


            /*
             * =================================================
             * COMMIT
             * =================================================
             */

            $this->db->commit();


            return true;


        } catch (
        Throwable $e
        ) {

            /*
             * Si algo falla:
             * NO queda la sede parcialmente eliminada.
             */
            if (
                $this->db->inTransaction()
            ) {

                $this->db->rollBack();
            }


            throw $e;
        }
    }

    /**
     * Verificar si existen usuarios asociados.
     */
    public function countUsers(
        int $branchId
    ): int {

        $stmt = $this->db->prepare("
            SELECT COUNT(*)
            FROM users
            WHERE branch_id = :branch_id
        ");

        $stmt->execute([
            'branch_id' => $branchId
        ]);

        return (int) $stmt->fetchColumn();
    }

    /**
     * Verificar si existen inventarios asociados.
     */
    public function countInventory(
        int $branchId
    ): int {

        $stmt = $this->db->prepare("
            SELECT COUNT(*)
            FROM inventory
            WHERE branch_id = :branch_id
        ");

        $stmt->execute([
            'branch_id' => $branchId
        ]);

        return (int) $stmt->fetchColumn();
    }

    /**
     * Verificar si existen órdenes asociadas.
     */
    public function countOrders(
        int $branchId
    ): int {

        $stmt = $this->db->prepare("
            SELECT COUNT(*)
            FROM orders
            WHERE branch_id = :branch_id
        ");

        $stmt->execute([
            'branch_id' => $branchId
        ]);

        return (int) $stmt->fetchColumn();
    }

    /**
     * Verificar si existen reservas asociadas.
     */
    public function countReservations(
        int $branchId
    ): int {

        $stmt = $this->db->prepare("
        SELECT COUNT(*)
        FROM restaurant_reservations
        WHERE branch_id = :branch_id
    ");

        $stmt->execute([
            'branch_id' => $branchId
        ]);

        return (int) $stmt->fetchColumn();
    }

    /**
     * Obtener estadísticas.
     */
    public function getStatistics(): array
    {
        $stmt = $this->db->query("
            SELECT
                COUNT(*) AS total,
                SUM(
                    CASE
                        WHEN is_active = 1
                        THEN 1
                        ELSE 0
                    END
                ) AS active,
                SUM(
                    CASE
                        WHEN is_active = 0
                        THEN 1
                        ELSE 0
                    END
                ) AS inactive
            FROM branches
        ");

        $data =
            $stmt->fetch(
                PDO::FETCH_ASSOC
            );

        return [
            'total' =>
                (int) ($data['total'] ?? 0),

            'active' =>
                (int) ($data['active'] ?? 0),

            'inactive' =>
                (int) ($data['inactive'] ?? 0)
        ];
    }

    /**
     * =========================================================
     * MÉTODOS UTILIZADOS POR RESERVAS
     * =========================================================
     */

    public function getBusinessHours(
        int $branchId,
        int $dayOfWeek
    ): ?array {

        $stmt = $this->db->prepare("
            SELECT *
            FROM branch_business_hours
            WHERE branch_id = :branch_id
              AND day_of_week = :day_of_week
            LIMIT 1
        ");

        $stmt->execute([
            'branch_id' =>
                $branchId,

            'day_of_week' =>
                $dayOfWeek
        ]);

        $result =
            $stmt->fetch(
                PDO::FETCH_ASSOC
            );

        return $result ?: null;
    }

    public function isBranchClosed(
        int $branchId,
        string $date
    ): bool {

        $stmt = $this->db->prepare("
            SELECT COUNT(*)
            FROM branch_closures
            WHERE branch_id = :branch_id
              AND closure_date = :closure_date
              AND is_active = 1
              AND is_full_day = 1
        ");

        $stmt->execute([
            'branch_id' =>
                $branchId,

            'closure_date' =>
                $date
        ]);

        return (int) $stmt->fetchColumn() > 0;
    }

    public function isTimeClosed(
        int $branchId,
        string $date,
        string $startTime,
        string $endTime
    ): bool {

        $stmt = $this->db->prepare("
            SELECT COUNT(*)
            FROM branch_closures
            WHERE branch_id = :branch_id
              AND closure_date = :closure_date
              AND is_active = 1
              AND is_full_day = 0
              AND start_time < :end_time
              AND end_time > :start_time
        ");

        $stmt->execute([
            'branch_id' =>
                $branchId,

            'closure_date' =>
                $date,

            'start_time' =>
                $startTime,

            'end_time' =>
                $endTime
        ]);

        return (int) $stmt->fetchColumn() > 0;
    }

    /**
     * =========================================================
     * HORARIOS SEMANALES
     * =========================================================
     */

    /**
     * Obtener los 7 horarios de una sede.
     */
    public function getBusinessHoursByBranch(
        int $branchId
    ): array {

        $stmt = $this->db->prepare("
        SELECT
            id,
            branch_id,
            day_of_week,
            open_time,
            close_time,
            is_closed
        FROM branch_business_hours
        WHERE branch_id = :branch_id
        ORDER BY day_of_week ASC
    ");

        $stmt->execute([
            'branch_id' => $branchId
        ]);

        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }


    /**
     * Guardar los horarios semanales.
     *
     * Se utiliza DELETE + INSERT dentro de una transacción.
     * La tabla tiene UNIQUE(branch_id, day_of_week).
     */
    public function saveBusinessHours(
        int $branchId,
        array $hours
    ): bool {

        $this->db->beginTransaction();

        try {

            $delete = $this->db->prepare("
            DELETE FROM branch_business_hours
            WHERE branch_id = :branch_id
        ");

            $delete->execute([
                'branch_id' => $branchId
            ]);


            $insert = $this->db->prepare("
            INSERT INTO branch_business_hours (
                branch_id,
                day_of_week,
                open_time,
                close_time,
                is_closed
            )
            VALUES (
                :branch_id,
                :day_of_week,
                :open_time,
                :close_time,
                :is_closed
            )
        ");


            foreach ($hours as $day => $data) {

                $insert->execute([
                    'branch_id' =>
                        $branchId,

                    'day_of_week' =>
                        (int) $day,

                    'open_time' =>
                        $data['is_closed']
                            ? null
                            : $data['open_time'],

                    'close_time' =>
                        $data['is_closed']
                            ? null
                            : $data['close_time'],

                    'is_closed' =>
                        $data['is_closed']
                            ? 1
                            : 0
                ]);
            }


            $this->db->commit();

            return true;

        } catch (Throwable $e) {

            if ($this->db->inTransaction()) {
                $this->db->rollBack();
            }

            throw $e;
        }
    }


    /**
     * Obtener horarios públicos.
     */
    public function getPublicBusinessHours(
        int $branchId
    ): array {

        return $this->getBusinessHoursByBranch(
            $branchId
        );
    }

    public function countBranchUsers(
        int $branchId
    ): int {

        $stmt = $this->db->prepare("
        SELECT COUNT(*)
        FROM branch_users
        WHERE branch_id = :branch_id
          AND is_active = 1
    ");

        $stmt->execute([
            'branch_id' => $branchId
        ]);

        return (int) $stmt->fetchColumn();
    }


    /**
     * =========================================================
     * SEDES ACTIVAS PARA HOME
     *
     * Obtiene:
     * - id
     * - code
     * - name
     * - address
     * - imagen favorita
     *
     * Si no existe una imagen favorita,
     * utiliza la primera imagen por sort_order.
     * =========================================================
     */
    public function getActiveBranchesForHome(): array
    {
        $sql = "
        SELECT
            b.id,
            b.code,
            b.name,
            b.phone,
            b.email,
            b.address,
            b.is_active,

            (
                SELECT
                    bi.file_path
                FROM branch_images bi
                WHERE bi.branch_id = b.id
                ORDER BY
                    bi.is_favorite DESC,
                    bi.sort_order ASC,
                    bi.id ASC
                LIMIT 1
            ) AS image_path

        FROM branches b

        WHERE b.is_active = 1

        ORDER BY
            b.name ASC
    ";


        $stmt =
            $this->db->query(
                $sql
            );


        return $stmt->fetchAll(
            PDO::FETCH_ASSOC
        );
    }

    /**
     * Crear configuración de reservas por defecto para una sede.
     */
    public function createDefaultReservationSettings(
        int $branchId
    ): void {

        $defaults = [
            'is_enabled' => 1,
            'min_people' => 1,
            'max_people' => 20,
            'slot_interval_minutes' => 30,
            'reservation_duration_minutes' => 120,
            'min_advance_minutes' => 60,
            'max_advance_days' => 60,
            'allow_same_day' => 1,
            'allow_customer_cancellation' => 1,
            'cancellation_limit_minutes' => 120,
            'auto_confirm' => 1
        ];

        $stmt = $this->db->prepare("
        INSERT INTO reservation_settings (
            branch_id,
            is_enabled,
            min_people,
            max_people,
            slot_interval_minutes,
            reservation_duration_minutes,
            min_advance_minutes,
            max_advance_days,
            allow_same_day,
            allow_customer_cancellation,
            cancellation_limit_minutes,
            auto_confirm
        )
        VALUES (
            :branch_id,
            :is_enabled,
            :min_people,
            :max_people,
            :slot_interval_minutes,
            :reservation_duration_minutes,
            :min_advance_minutes,
            :max_advance_days,
            :allow_same_day,
            :allow_customer_cancellation,
            :cancellation_limit_minutes,
            :auto_confirm
        )
    ");

        $stmt->execute([
            'branch_id' =>
                $branchId,

            'is_enabled' =>
                $defaults['is_enabled'],

            'min_people' =>
                $defaults['min_people'],

            'max_people' =>
                $defaults['max_people'],

            'slot_interval_minutes' =>
                $defaults['slot_interval_minutes'],

            'reservation_duration_minutes' =>
                $defaults['reservation_duration_minutes'],

            'min_advance_minutes' =>
                $defaults['min_advance_minutes'],

            'max_advance_days' =>
                $defaults['max_advance_days'],

            'allow_same_day' =>
                $defaults['allow_same_day'],

            'allow_customer_cancellation' =>
                $defaults['allow_customer_cancellation'],

            'cancellation_limit_minutes' =>
                $defaults['cancellation_limit_minutes'],

            'auto_confirm' =>
                $defaults['auto_confirm']
        ]);
    }


}