<?php
// app/Services/BranchService.php

class BranchService
{
    private BranchRepository $branchRepository;

    private RestaurantTableRepository $tableRepository;

    private BranchImageRepository $imageRepository;

    private const MAX_BRANCH_IMAGES = 20;

    private const MAX_IMAGE_SIZE = 8388608;

    public function __construct()
    {
        $this->branchRepository =
            new BranchRepository();

        $this->tableRepository =
            new RestaurantTableRepository();

        $this->imageRepository =
            new BranchImageRepository();
    }

    /**
     * Obtener sedes según el usuario.
     *
     * SuperAdmin -> todas
     * Admin      -> únicamente su sede
     */
    public function getBranches(
        bool $isSuperAdmin,
        ?int $currentBranchId,
        ?string $search = null,
        ?int $isActive = null
    ): array {

        if ($isSuperAdmin) {

            return $this->branchRepository
                ->getManagedBranches(
                    $search,
                    $isActive
                );
        }

        if (
            $currentBranchId === null ||
            $currentBranchId <= 0
        ) {
            return [];
        }

        $branch =
            $this->branchRepository
                ->findById(
                    $currentBranchId
                );

        if (!$branch) {
            return [];
        }

        if (
            $search !== null &&
            trim($search) !== ''
        ) {

            $term =
                strtolower(
                    trim($search)
                );

            $haystack =
                strtolower(
                    implode(' ', [
                        $branch['code'] ?? '',
                        $branch['name'] ?? '',
                        $branch['phone'] ?? '',
                        $branch['email'] ?? '',
                        $branch['address'] ?? ''
                    ])
                );

            if (
                !str_contains(
                    $haystack,
                    $term
                )
            ) {
                return [];
            }
        }

        if (
            $isActive !== null &&
            (int) $branch['is_active'] !== $isActive
        ) {
            return [];
        }

        return [$branch];
    }

    /**
     * Obtener una sede.
     */
    public function getBranch(
        int $branchId,
        bool $isSuperAdmin,
        ?int $currentBranchId
    ): ?array {

        if (!$isSuperAdmin) {

            if (
                $currentBranchId === null ||
                $branchId !== $currentBranchId
            ) {
                throw new DomainException(
                    'No tienes permiso para acceder a esta sede.',
                    403
                );
            }
        }

        return $this->branchRepository
            ->findById($branchId);
    }

    /**
     * Crear sede.
     *
     * SOLO SUPERADMIN.
     */
    public function createBranch(
        array $input,
        bool $isSuperAdmin
    ): int {

        $this->requireSuperAdmin(
            $isSuperAdmin
        );

        $data =
            $this->validateData(
                $input
            );

        if (
            $this->branchRepository
                ->findByCode(
                    $data['code']
                )
        ) {

            throw new DomainException(
                'El código de la sede ya está registrado.',
                409
            );
        }

        $branchId =
            $this->branchRepository
                ->create($data);

        /*
         * Crear automáticamente la configuración
         * de reservas para la nueva sede.
         */
        $this->branchRepository
            ->createDefaultReservationSettings(
                $branchId
            );

        return $branchId;
    }

    /**
     * Actualizar sede.
     *
     * SuperAdmin -> cualquiera
     * Admin      -> propia
     */
    public function updateBranch(
        int $branchId,
        array $input,
        bool $isSuperAdmin,
        ?int $currentBranchId
    ): bool {

        if (!$isSuperAdmin) {

            if (
                $currentBranchId === null ||
                $branchId !== $currentBranchId
            ) {
                throw new DomainException(
                    'Solo puedes editar tu propia sede.',
                    403
                );
            }
        }

        $existing =
            $this->branchRepository
                ->findById(
                    $branchId
                );

        if (!$existing) {

            throw new DomainException(
                'La sede no existe.',
                404
            );
        }

        $data =
            $this->validateData(
                $input
            );

        $duplicate =
            $this->branchRepository
                ->findByCode(
                    $data['code'],
                    $branchId
                );

        if ($duplicate) {

            throw new DomainException(
                'El código de la sede ya está registrado por otra sede.',
                409
            );
        }

        return $this->branchRepository
            ->update(
                $branchId,
                $data
            );
    }

    /**
     * =========================================================
     * ELIMINAR SEDE COMPLETAMENTE
     * =========================================================
     *
     * Elimina:
     *
     * - Sede
     * - Reservas
     * - Órdenes
     * - Bookings legacy
     * - Empleados exclusivos de la sede
     * - Configuración de reservas
     * - Horarios
     * - Cierres
     * - Mesas
     * - Inventario
     * - Imágenes
     * - Configuración de sistema
     * - Notificaciones de la sede
     * - Relaciones branch_users
     *
     * Los archivos físicos de imágenes y avatares
     * también se eliminan después del COMMIT.
     */
    public function deleteBranch(
        int $branchId,
        bool $isSuperAdmin
    ): bool {

        /*
         * Solo SuperAdmin.
         */
        $this->requireSuperAdmin(
            $isSuperAdmin
        );


        /*
         * Verificar que la sede exista.
         */
        $branch =
            $this->branchRepository
                ->findById(
                    $branchId
                );

        if (!$branch) {

            throw new DomainException(
                'La sede no existe.',
                404
            );
        }


        /*
         * =====================================================
         * OBTENER RECURSOS FÍSICOS ANTES DE ELIMINAR
         * =====================================================
         */

        $branchImages =
            $this->imageRepository
                ->getByBranch(
                    $branchId
                );


        /*
         * Empleados que pertenecen exclusivamente
         * a esta sede.
         */
        $branchUsers =
            $this->branchRepository
                ->getBranchUsersForDeletion(
                    $branchId
                );


        /*
         * =====================================================
         * IDS DE USUARIOS
         * =====================================================
         */

        $userIds =
            array_values(
                array_unique(
                    array_map(
                        static function (
                            array $user
                        ): int {

                            return (int)
                            $user['id'];

                        },
                        $branchUsers
                    )
                )
            );


        /*
         * =====================================================
         * ELIMINACIÓN EN BASE DE DATOS
         * =====================================================
         */

        $result =
            $this->branchRepository
                ->deleteBranchCompletely(
                    $branchId,
                    $userIds
                );


        /*
         * =====================================================
         * ARCHIVOS FÍSICOS DE IMÁGENES
         * =====================================================
         */

        if ($result) {

            /*
             * Eliminar imágenes registradas.
             */
            foreach (
                $branchImages as $image
            ) {

                $this->removeStoredFile(
                    (string)
                    (
                        $image['file_path']
                        ?? ''
                    )
                );
            }


            /*
             * Eliminar cualquier archivo
             * sobrante de la carpeta de la sede.
             */
            $this->removeBranchImageDirectory(
                $branchId
            );


            /*
             * =================================================
             * AVATARES DE EMPLEADOS ELIMINADOS
             * =================================================
             */

            foreach (
                $branchUsers as $user
            ) {

                $avatar =
                    trim(
                        (string)
                        (
                            $user['avatar']
                            ?? ''
                        )
                    );

                $this->removeUserAvatar(
                    $avatar
                );
            }
        }


        return $result;
    }

    /**
     * Estadísticas.
     */
    public function getStatistics(
        bool $isSuperAdmin,
        ?int $currentBranchId
    ): array {

        if ($isSuperAdmin) {

            return $this->branchRepository
                ->getStatistics();
        }

        $branch =
            $currentBranchId !== null
                ? $this->branchRepository
                ->findById(
                    $currentBranchId
                )
                : null;

        if (!$branch) {

            return [
                'total' => 0,
                'active' => 0,
                'inactive' => 0
            ];
        }

        return [
            'total' => 1,

            'active' =>
                (int) $branch['is_active'] === 1
                    ? 1
                    : 0,

            'inactive' =>
                (int) $branch['is_active'] === 0
                    ? 1
                    : 0
        ];
    }

    /**
     * Validación central.
     */
    private function validateData(
        array $input
    ): array {

        $code =
            strtoupper(
                trim(
                    (string)
                    ($input['code'] ?? '')
                )
            );

        $name =
            trim(
                (string)
                ($input['name'] ?? '')
            );

        $phone =
            trim(
                (string)
                ($input['phone'] ?? '')
            );

        $email =
            strtolower(
                trim(
                    (string)
                    ($input['email'] ?? '')
                )
            );

        $address =
            trim(
                (string)
                ($input['address'] ?? '')
            );

        $isActive =
            !empty($input['is_active'])
                ? 1
                : 0;

        if ($code === '') {

            throw new DomainException(
                'El código de la sede es obligatorio.',
                422
            );
        }

        if (
            !preg_match(
                '/^[A-Z0-9_-]{2,20}$/',
                $code
            )
        ) {

            throw new DomainException(
                'El código debe tener entre 2 y 20 caracteres y solo puede contener letras, números, guion y guion bajo.',
                422
            );
        }

        if ($name === '') {

            throw new DomainException(
                'El nombre de la sede es obligatorio.',
                422
            );
        }

        if (
            mb_strlen($name) > 150
        ) {

            throw new DomainException(
                'El nombre de la sede no puede superar los 150 caracteres.',
                422
            );
        }

        if (
            $email !== '' &&
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

        return [
            'code' =>
                $code,

            'name' =>
                $name,

            'phone' =>
                $phone,

            'email' =>
                $email,

            'address' =>
                $address,

            'is_active' =>
                $isActive
        ];
    }

    /**
     * Validar SuperAdmin.
     */
    private function requireSuperAdmin(
        bool $isSuperAdmin
    ): void {

        if (!$isSuperAdmin) {

            throw new DomainException(
                'Esta operación solo está disponible para SuperAdmin.',
                403
            );
        }
    }

    /**
     * Obtener horarios de una sede.
     */
    public function getBusinessHours(
        int $branchId,
        bool $isSuperAdmin,
        ?int $currentBranchId
    ): array {

        $this->assertBranchAccess(
            $branchId,
            $isSuperAdmin,
            $currentBranchId
        );

        $rows =
            $this->branchRepository
                ->getBusinessHoursByBranch(
                    $branchId
                );

        $result = [];

        for ($day = 1; $day <= 7; $day++) {

            $result[$day] = [
                'day_of_week' => $day,
                'open_time' => null,
                'close_time' => null,
                'is_closed' => true
            ];
        }

        foreach ($rows as $row) {

            $day =
                (int) $row['day_of_week'];

            if ($day < 1 || $day > 7) {
                continue;
            }

            $result[$day] = [
                'day_of_week' =>
                    $day,

                'open_time' =>
                    $row['open_time'],

                'close_time' =>
                    $row['close_time'],

                'is_closed' =>
                    (int) $row['is_closed'] === 1
            ];
        }

        return $result;
    }


    /**
     * Guardar horarios.
     */
    public function saveBusinessHours(
        int $branchId,
        array $input,
        bool $isSuperAdmin,
        ?int $currentBranchId
    ): bool {

        $this->assertBranchAccess(
            $branchId,
            $isSuperAdmin,
            $currentBranchId
        );

        $hours =
            $this->validateBusinessHours(
                $input
            );

        return $this->branchRepository
            ->saveBusinessHours(
                $branchId,
                $hours
            );
    }

    /**
     * =========================================================
     * IMÁGENES DE LA SEDE
     * =========================================================
     */

    public function getImages(
        int $branchId,
        bool $isSuperAdmin,
        ?int $currentBranchId
    ): array {

        $this->assertBranchAccess(
            $branchId,
            $isSuperAdmin,
            $currentBranchId
        );

        return $this->imageRepository
            ->getByBranch($branchId);
    }


    /**
     * Guardar imágenes de la sede.
     *
     * Permite:
     * - nuevas imágenes
     * - eliminar imágenes
     * - cambiar orden
     * - seleccionar favorita
     */
    public function saveImages(
        int $branchId,
        array $files,
        array $input,
        bool $isSuperAdmin,
        ?int $currentBranchId
    ): array {

        $this->assertBranchAccess(
            $branchId,
            $isSuperAdmin,
            $currentBranchId
        );


        $order =
            $this->decodeJsonArray(
                $input['order'] ?? '[]'
            );

        $deleteIds =
            $this->decodeJsonArray(
                $input['delete_ids'] ?? '[]'
            );

        $newKeys =
            $this->decodeJsonArray(
                $input['new_keys'] ?? '[]'
            );

        $favorite =
            $this->decodeJsonValue(
                $input['favorite'] ?? 'null'
            );


        $deleteIds =
            array_values(
                array_unique(
                    array_filter(
                        array_map(
                            'intval',
                            $deleteIds
                        ),
                        static fn(int $id): bool =>
                            $id > 0
                    )
                )
            );


        $existingImages =
            $this->imageRepository
                ->getByBranch(
                    $branchId
                );


        $existingById = [];

        foreach ($existingImages as $image) {

            $existingById[
            (int) $image['id']
            ] = $image;
        }


        /*
         * Verificar que las imágenes
         * a eliminar pertenecen a la sede.
         */

        foreach ($deleteIds as $deleteId) {

            if (!isset($existingById[$deleteId])) {

                throw new DomainException(
                    'Una de las imágenes seleccionadas no pertenece a esta sede.',
                    422
                );
            }
        }


        /*
         * Normalizar archivos.
         */

        $newFiles =
            $this->normalizeUploadFiles(
                $files
            );


        $newKeys =
            array_values(
                array_map(
                    static fn($value): string =>
                    (string) $value,
                    $newKeys
                )
            );


        if (
            count($newFiles) !==
            count($newKeys)
        ) {

            throw new DomainException(
                'No fue posible relacionar las imágenes nuevas con su orden.',
                422
            );
        }


        /*
         * Validar máximo.
         */

        $remainingExisting =
            count($existingImages) -
            count($deleteIds);

        $total =
            $remainingExisting +
            count($newFiles);


        if (
            $total >
            self::MAX_BRANCH_IMAGES
        ) {

            throw new DomainException(
                'Una sede puede tener máximo ' .
                self::MAX_BRANCH_IMAGES .
                ' imágenes.',
                422
            );
        }


        $uploaded = [];

        $createdIds = [];

        $deletedImages = [];


        foreach ($deleteIds as $deleteId) {

            $deletedImages[] =
                $existingById[$deleteId];
        }


        try {

            /*
             * Subir físicamente los archivos.
             */

            foreach ($newFiles as $index => $file) {

                $key =
                    $newKeys[$index];

                if ($key === '') {

                    throw new DomainException(
                        'Una imagen nueva no tiene identificador temporal.',
                        422
                    );
                }

                $uploaded[$key] =
                    $this->storeBranchImageFile(
                        $branchId,
                        $file
                    );
            }


            $db =
                Database::getConnection();

            $db->beginTransaction();


            try {

                /*
                 * Eliminar registros seleccionados.
                 */

                $this->imageRepository
                    ->deleteByIds(
                        $branchId,
                        $deleteIds
                    );


                /*
                 * Crear nuevas imágenes.
                 */

                foreach (
                    $uploaded
                    as $key => $meta
                ) {

                    $createdIds[$key] =
                        $this->imageRepository
                            ->create(
                                $branchId,
                                [
                                    'file_path' =>
                                        $meta['file_path'],

                                    'original_name' =>
                                        $meta['original_name'],

                                    'mime_type' =>
                                        $meta['mime_type'],

                                    'file_size' =>
                                        $meta['file_size'],

                                    'width' =>
                                        $meta['width'],

                                    'height' =>
                                        $meta['height'],

                                    'sort_order' =>
                                        0,

                                    'is_favorite' =>
                                        false
                                ]
                            );
                }


                /*
                 * Construir nuevo orden.
                 */

                $normalizedOrder = [];

                $seen = [];


                foreach ($order as $descriptor) {

                    if (!is_array($descriptor)) {
                        continue;
                    }


                    $type =
                        (string) (
                            $descriptor['type']
                            ?? ''
                        );


                    /*
                     * IMAGEN EXISTENTE
                     */

                    if ($type === 'existing') {

                        $id =
                            (int) (
                                $descriptor['id']
                                ?? 0
                            );


                        if (
                            $id <= 0 ||
                            !isset($existingById[$id])
                        ) {

                            throw new DomainException(
                                'El orden de imágenes contiene una referencia inválida.',
                                422
                            );
                        }


                        if (
                            in_array(
                                $id,
                                $deleteIds,
                                true
                            )
                        ) {
                            continue;
                        }


                        if (
                            isset($seen[$id])
                        ) {

                            throw new DomainException(
                                'Una imagen aparece más de una vez en el orden.',
                                422
                            );
                        }


                        $normalizedOrder[] = [
                            'id' => $id,
                            'type' => 'existing'
                        ];

                        $seen[$id] = true;

                        continue;
                    }


                    /*
                     * IMAGEN NUEVA
                     */

                    if ($type === 'new') {

                        $key =
                            (string) (
                                $descriptor['key']
                                ?? ''
                            );


                        if (
                            $key === '' ||
                            !isset($createdIds[$key])
                        ) {

                            throw new DomainException(
                                'El orden de las imágenes nuevas es inválido.',
                                422
                            );
                        }


                        $id =
                            (int) $createdIds[$key];


                        if (
                            isset($seen[$id])
                        ) {

                            throw new DomainException(
                                'Una imagen nueva aparece más de una vez.',
                                422
                            );
                        }


                        $normalizedOrder[] = [
                            'id' => $id,
                            'type' => 'new',
                            'key' => $key
                        ];

                        $seen[$id] = true;
                    }
                }


                /*
                 * Agregar imágenes existentes
                 * que no llegaron en el orden.
                 */

                foreach ($existingImages as $image) {

                    $id =
                        (int) $image['id'];


                    if (
                        in_array(
                            $id,
                            $deleteIds,
                            true
                        )
                    ) {
                        continue;
                    }


                    if (
                        isset($seen[$id])
                    ) {
                        continue;
                    }


                    $normalizedOrder[] = [
                        'id' => $id,
                        'type' => 'existing'
                    ];

                    $seen[$id] = true;
                }


                /*
                 * Agregar nuevas omitidas.
                 */

                foreach ($createdIds as $key => $id) {

                    if (isset($seen[$id])) {
                        continue;
                    }


                    $normalizedOrder[] = [
                        'id' => $id,
                        'type' => 'new',
                        'key' => $key
                    ];

                    $seen[$id] = true;
                }


                /*
                 * Determinar favorita.
                 */

                $favoriteId = null;


                if (is_array($favorite)) {

                    $favoriteType =
                        (string) (
                            $favorite['type']
                            ?? ''
                        );


                    if (
                        $favoriteType ===
                        'existing'
                    ) {

                        $candidate =
                            (int) (
                                $favorite['id']
                                ?? 0
                            );


                        if (
                            $candidate > 0 &&
                            isset(
                                $existingById[$candidate]
                            ) &&
                            !in_array(
                                $candidate,
                                $deleteIds,
                                true
                            )
                        ) {

                            $favoriteId =
                                $candidate;
                        }
                    }


                    if (
                        $favoriteType ===
                        'new'
                    ) {

                        $key =
                            (string) (
                                $favorite['key']
                                ?? ''
                            );


                        if (
                            $key !== '' &&
                            isset(
                                $createdIds[$key]
                            )
                        ) {

                            $favoriteId =
                                (int) $createdIds[$key];
                        }
                    }
                }


                /*
                 * Si no existe favorita,
                 * la primera será portada.
                 */

                if (
                    $favoriteId === null &&
                    !empty($normalizedOrder)
                ) {

                    $favoriteId =
                        (int) $normalizedOrder[0]['id'];
                }


                /*
                 * Guardar orden + favorita.
                 */

                foreach (
                    $normalizedOrder
                    as $sortOrder => $entry
                ) {

                    $id =
                        (int) $entry['id'];


                    $this->imageRepository
                        ->updateMeta(
                            $id,
                            $branchId,
                            $sortOrder,
                            $favoriteId !== null &&
                            $id === $favoriteId
                        );
                }


                $db->commit();

            } catch (Throwable $e) {

                if ($db->inTransaction()) {
                    $db->rollBack();
                }

                throw $e;
            }

        } catch (Throwable $e) {

            /*
             * Si la BD falla después de subir
             * archivos, eliminamos esos archivos.
             */

            foreach ($uploaded as $meta) {

                $this->removeStoredFile(
                    $meta['file_path']
                );
            }

            throw $e;
        }


        /*
         * Eliminar físicamente imágenes
         * que fueron borradas desde la interfaz.
         */

        foreach ($deletedImages as $image) {

            $this->removeStoredFile(
                (string) $image['file_path']
            );
        }


        return $this->imageRepository
            ->getByBranch(
                $branchId
            );
    }

    /**
     * =========================================================
     * MESAS DE LA SEDE
     * =========================================================
     */
    public function getTables(
        int $branchId,
        bool $isSuperAdmin,
        ?int $currentBranchId
    ): array {

        $this->assertBranchAccess(
            $branchId,
            $isSuperAdmin,
            $currentBranchId
        );

        return $this->tableRepository
            ->getByBranch(
                $branchId
            );
    }


    /**
     * =========================================================
     * CREAR MESA
     * =========================================================
     */
    public function createTable(
        int $branchId,
        array $input,
        bool $isSuperAdmin,
        ?int $currentBranchId
    ): int {

        $this->assertBranchAccess(
            $branchId,
            $isSuperAdmin,
            $currentBranchId
        );

        $data =
            $this->validateTableData(
                $input
            );

        $existing =
            $this->tableRepository
                ->findByTableNumber(
                    $branchId,
                    $data['table_number']
                );

        if ($existing) {

            throw new DomainException(
                'Ya existe una mesa con ese número en esta sede.',
                422
            );
        }

        return $this->tableRepository
            ->create(
                $branchId,
                $data
            );
    }


    /**
     * =========================================================
     * ACTUALIZAR MESA
     * =========================================================
     */
    public function updateTable(
        int $tableId,
        array $input,
        bool $isSuperAdmin,
        ?int $currentBranchId
    ): bool {

        $table =
            $this->tableRepository
                ->findById(
                    $tableId
                );

        if (!$table) {

            throw new DomainException(
                'La mesa no existe.',
                404
            );
        }

        $branchId =
            (int) $table['branch_id'];

        $this->assertBranchAccess(
            $branchId,
            $isSuperAdmin,
            $currentBranchId
        );

        $data =
            $this->validateTableData(
                $input
            );

        $existing =
            $this->tableRepository
                ->findByTableNumber(
                    $branchId,
                    $data['table_number'],
                    $tableId
                );

        if ($existing) {

            throw new DomainException(
                'Ya existe otra mesa con ese número en esta sede.',
                422
            );
        }

        return $this->tableRepository
            ->update(
                $tableId,
                $data
            );
    }



    /**
     * Desactivar mesa conservando su historial.
     */
    public function deleteTable(
        int $tableId,
        bool $isSuperAdmin,
        ?int $currentBranchId
    ): bool {

        $table = $this->tableRepository->findById($tableId);

        if (!$table) {
            throw new DomainException('La mesa no existe.', 404);
        }

        $this->assertBranchAccess(
            (int) $table['branch_id'],
            $isSuperAdmin,
            $currentBranchId
        );

        return $this->tableRepository->deactivate($tableId);
    }

    /**
     * Eliminar definitivamente una mesa sin historial.
     */
    public function deleteTablePermanently(
        int $tableId,
        bool $isSuperAdmin,
        ?int $currentBranchId
    ): bool {

        $table = $this->tableRepository->findById($tableId);

        if (!$table) {
            throw new DomainException('La mesa no existe.', 404);
        }

        $this->assertBranchAccess(
            (int) $table['branch_id'],
            $isSuperAdmin,
            $currentBranchId
        );

        $reservationCount =
            $this->tableRepository->countReservations($tableId);

        if ($reservationCount > 0) {
            throw new DomainException(
                'No se puede eliminar definitivamente esta mesa porque tiene historial de reservas. Puedes desactivarla.',
                409
            );
        }

        $deleted = $this->tableRepository->delete($tableId);

        if (!$deleted) {
            throw new DomainException(
                'No fue posible eliminar la mesa.',
                409
            );
        }

        return true;
    }


    public function getTableReservations(
        int $tableId,
        bool $isSuperAdmin,
        ?int $currentBranchId
    ): array {
        $table = $this->tableRepository->findById($tableId);

        if (!$table) {
            throw new DomainException('La mesa no existe.', 404);
        }

        $this->assertBranchAccess(
            (int) $table['branch_id'],
            $isSuperAdmin,
            $currentBranchId
        );

        return $this->tableRepository->getReservationsByTable(
            $tableId
        );
    }


    /**
     * =========================================================
     * VALIDAR DATOS DE MESA
     * =========================================================
     */
    private function validateTableData(
        array $input
    ): array {

        $tableNumber =
            (int) (
                $input['table_number']
                ?? 0
            );

        $name =
            trim(
                (string) (
                    $input['name']
                    ?? ''
                )
            );

        $capacity =
            (int) (
                $input['capacity']
                ?? 0
            );

        $location =
            trim(
                (string) (
                    $input['location']
                    ?? ''
                )
            );

        $status =
            strtolower(
                trim(
                    (string) (
                        $input['status']
                        ?? 'available'
                    )
                )
            );

        $isActive =
            !empty(
            $input['is_active']
            )
                ? 1
                : 0;


        if ($tableNumber <= 0) {

            throw new DomainException(
                'El número de mesa debe ser mayor que cero.',
                422
            );
        }


        if ($capacity <= 0) {

            throw new DomainException(
                'La capacidad debe ser mayor que cero.',
                422
            );
        }


        if ($capacity > 100) {

            throw new DomainException(
                'La capacidad máxima permitida es de 100 personas.',
                422
            );
        }


        if ($name === '') {

            $name =
                'Mesa ' .
                $tableNumber;
        }


        if ($status === '') {

            $status =
                'available';
        }


        /*
         * Solo permitimos los estados
         * que administraremos desde este módulo.
         */
        $allowedStatuses = [
            'available',
            'maintenance'
        ];

        if (
            !in_array(
                $status,
                $allowedStatuses,
                true
            )
        ) {

            throw new DomainException(
                'El estado de la mesa no es válido.',
                422
            );
        }


        return [
            'table_number' =>
                $tableNumber,

            'name' =>
                $name,

            'capacity' =>
                $capacity,

            'location' =>
                $location,

            'status' =>
                $status,

            'is_active' =>
                $isActive
        ];
    }

    /**
     * Validar acceso a la sede.
     */
    private function assertBranchAccess(
        int $branchId,
        bool $isSuperAdmin,
        ?int $currentBranchId
    ): void {

        if ($branchId <= 0) {

            throw new DomainException(
                'Sede inválida.',
                422
            );
        }

        if (!$isSuperAdmin) {

            if (
                $currentBranchId === null ||
                $branchId !== $currentBranchId
            ) {

                throw new DomainException(
                    'No tienes permiso para gestionar esta sede.',
                    403
                );
            }
        }

        if (
            !$this->branchRepository
                ->findById($branchId)
        ) {

            throw new DomainException(
                'La sede no existe.',
                404
            );
        }
    }


    /**
     * Validar horarios semanales.
     */
    private function validateBusinessHours(
        array $input
    ): array {

        $hoursInput =
            $input['hours'] ?? $input;

        if (!is_array($hoursInput)) {
            throw new DomainException(
                'Los horarios enviados no son válidos.',
                422
            );
        }

        $days = [];

        for ($day = 1; $day <= 7; $day++) {

            $raw =
                $hoursInput[$day]
                ?? $hoursInput[(string) $day]
                ?? null;

            if (!is_array($raw)) {

                throw new DomainException(
                    "Falta configurar el día {$day}.",
                    422
                );
            }

            $isClosed =
                !empty($raw['is_closed']);


            if ($isClosed) {

                $days[$day] = [
                    'is_closed' => true,
                    'open_time' => null,
                    'close_time' => null
                ];

                continue;
            }


            $open =
                trim(
                    (string)
                    ($raw['open_time'] ?? '')
                );

            $close =
                trim(
                    (string)
                    ($raw['close_time'] ?? '')
                );


            if (
                !preg_match(
                    '/^\d{2}:\d{2}$/',
                    $open
                ) ||
                !preg_match(
                    '/^\d{2}:\d{2}$/',
                    $close
                )
            ) {

                throw new DomainException(
                    "El horario del día {$day} no es válido.",
                    422
                );
            }


            if ($open >= $close) {

                throw new DomainException(
                    "La hora de apertura debe ser menor que la hora de cierre para el día {$day}.",
                    422
                );
            }


            $days[$day] = [
                'is_closed' => false,
                'open_time' => $open . ':00',
                'close_time' => $close . ':00'
            ];
        }

        return $days;
    }

    private function normalizeUploadFiles(
        array $files
    ): array {

        if (
            empty($files) ||
            !isset($files['name'])
        ) {
            return [];
        }


        $names =
            is_array($files['name'])
                ? $files['name']
                : [$files['name']];

        $types =
            is_array($files['type'])
                ? $files['type']
                : [$files['type']];

        $tmpNames =
            is_array($files['tmp_name'])
                ? $files['tmp_name']
                : [$files['tmp_name']];

        $errors =
            is_array($files['error'])
                ? $files['error']
                : [$files['error']];

        $sizes =
            is_array($files['size'])
                ? $files['size']
                : [$files['size']];


        $normalized = [];


        foreach (
            $names
            as $index => $name
        ) {

            $normalized[] = [
                'name' =>
                    (string) $name,

                'type' =>
                    (string) (
                        $types[$index]
                        ?? ''
                    ),

                'tmp_name' =>
                    (string) (
                        $tmpNames[$index]
                        ?? ''
                    ),

                'error' =>
                    (int) (
                        $errors[$index]
                        ?? UPLOAD_ERR_NO_FILE
                    ),

                'size' =>
                    (int) (
                        $sizes[$index]
                        ?? 0
                    )
            ];
        }


        return $normalized;
    }


    private function decodeJsonArray(
        $value
    ): array {

        if (is_array($value)) {
            return $value;
        }


        if (
            !is_string($value) ||
            trim($value) === ''
        ) {
            return [];
        }


        $decoded =
            json_decode(
                $value,
                true
            );


        return is_array($decoded)
            ? $decoded
            : [];
    }


    private function decodeJsonValue(
        $value
    ): mixed {

        if (is_array($value)) {
            return $value;
        }


        if (
            !is_string($value) ||
            trim($value) === ''
        ) {
            return null;
        }


        return json_decode(
            $value,
            true
        );
    }

    private function storeBranchImageFile(
        int $branchId,
        array $file
    ): array {

        $error =
            (int) (
                $file['error']
                ?? UPLOAD_ERR_NO_FILE
            );


        if (
            $error !==
            UPLOAD_ERR_OK
        ) {

            throw new DomainException(
                'No fue posible cargar una de las imágenes.',
                422
            );
        }


        if (
            empty($file['tmp_name']) ||
            !is_uploaded_file(
                $file['tmp_name']
            )
        ) {

            throw new DomainException(
                'Una de las imágenes no es una carga válida.',
                422
            );
        }


        $size =
            (int) (
                $file['size']
                ?? 0
            );


        if ($size <= 0) {

            throw new DomainException(
                'Una de las imágenes está vacía.',
                422
            );
        }


        if (
            $size >
            self::MAX_IMAGE_SIZE
        ) {

            throw new DomainException(
                'Cada imagen puede pesar máximo 8 MB.',
                422
            );
        }


        if (
            !class_exists('finfo')
        ) {

            throw new RuntimeException(
                'La extensión Fileinfo de PHP es necesaria para validar imágenes.'
            );
        }


        $finfo =
            new finfo(
                FILEINFO_MIME_TYPE
            );


        $mime =
            $finfo->file(
                $file['tmp_name']
            );


        $allowed = [
            'image/jpeg' =>
                'jpg',

            'image/png' =>
                'png',

            'image/webp' =>
                'webp'
        ];


        if (
            !isset(
                $allowed[$mime]
            )
        ) {

            throw new DomainException(
                'Formato no permitido. Usa JPG, PNG o WEBP.',
                422
            );
        }


        $dimensions =
            @getimagesize(
                $file['tmp_name']
            );


        if ($dimensions === false) {

            throw new DomainException(
                'Una de las imágenes no es válida o está dañada.',
                422
            );
        }


        $width =
            (int) (
                $dimensions[0]
                ?? 0
            );

        $height =
            (int) (
                $dimensions[1]
                ?? 0
            );


        if (
            $width <= 0 ||
            $height <= 0
        ) {

            throw new DomainException(
                'Las dimensiones de una imagen no son válidas.',
                422
            );
        }


        if (
            $width > 8000 ||
            $height > 8000
        ) {

            throw new DomainException(
                'Las imágenes no pueden superar 8000 × 8000 píxeles.',
                422
            );
        }


        $directory =
            __DIR__ .
            '/../../public/uploads/branches/' .
            $branchId .
            '/';


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
                    'No fue posible crear el directorio de imágenes.'
                );
            }
        }


        $filename =
            'branch-' .
            $branchId .
            '-' .
            bin2hex(
                random_bytes(12)
            ) .
            '.' .
            $allowed[$mime];


        $destination =
            $directory .
            $filename;


        if (
            !move_uploaded_file(
                $file['tmp_name'],
                $destination
            )
        ) {

            throw new RuntimeException(
                'No fue posible guardar una de las imágenes.'
            );
        }


        return [

            'file_path' =>
                'uploads/branches/' .
                $branchId .
                '/' .
                $filename,

            'original_name' =>
                mb_substr(
                    trim(
                        (string) (
                            $file['name']
                            ?? 'imagen'
                        )
                    ),
                    0,
                    255
                ),

            'mime_type' =>
                $mime,

            'file_size' =>
                $size,

            'width' =>
                $width,

            'height' =>
                $height
        ];
    }


    private function removeStoredFile(
        string $relativePath
    ): void {

        if (
            $relativePath === ''
        ) {
            return;
        }


        $baseDirectory =
            realpath(
                __DIR__ .
                '/../../public/uploads'
            );


        if (
            $baseDirectory === false
        ) {
            return;
        }


        $candidate =
            realpath(
                __DIR__ .
                '/../../public/' .
                ltrim(
                    $relativePath,
                    '/\\'
                )
            );


        if (
            $candidate === false ||
            !is_file($candidate)
        ) {
            return;
        }


        $basePrefix =
            rtrim(
                $baseDirectory,
                DIRECTORY_SEPARATOR
            ) .
            DIRECTORY_SEPARATOR;


        if (
            !str_starts_with(
                $candidate,
                $basePrefix
            )
        ) {
            return;
        }


        @unlink(
            $candidate
        );
    }

    /**
     * =========================================================
     * ELIMINAR CARPETA DE IMÁGENES DE LA SEDE
     * =========================================================
     */
    private function removeBranchImageDirectory(
        int $branchId
    ): void {

        $directory =
            __DIR__ .
            '/../../public/uploads/branches/' .
            $branchId;


        if (
            !is_dir(
                $directory
            )
        ) {
            return;
        }


        $files =
            glob(
                $directory .
                DIRECTORY_SEPARATOR .
                '*'
            );


        if (
            is_array(
                $files
            )
        ) {

            foreach (
                $files as $file
            ) {

                if (
                    is_file(
                        $file
                    )
                ) {

                    @unlink(
                        $file
                    );
                }
            }
        }


        @rmdir(
            $directory
        );
    }


    /**
     * =========================================================
     * ELIMINAR AVATAR DE USUARIO
     * =========================================================
     */
    private function removeUserAvatar(
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
            is_file(
                $path
            )
        ) {

            @unlink(
                $path
            );
        }
    }

}