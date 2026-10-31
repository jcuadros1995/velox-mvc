<?php
// app/Services/ItemService.php

class ItemService
{
    private ItemRepository $itemRepository;

    private ItemImageRepository $itemImageRepository;

    public function __construct()
    {
        $this->itemRepository =
            new ItemRepository();

        $this->itemImageRepository =
            new ItemImageRepository();
    }

    /**
     * Obtiene todos los productos activos.
     */
    public function getAll(
        bool $includeInactive = false
    ): array {
        return $this->itemRepository
            ->getAllWithCategory(
                $includeInactive
            );
    }

    /**
     * Obtiene un producto completo.
     *
     * Incluye:
     * - información del producto
     * - categoría
     * - imágenes
     */
    public function getById(int $id): ?array
    {
        if ($id <= 0) {
            return null;
        }

        return $this->itemRepository->getWithImages($id);
    }

    /**
     * Obtiene las categorías disponibles para Burger Town.
     */
    public function getCategories(): array
    {
        return $this->itemRepository->getRestaurantCategories();
    }

    /**
     * Obtiene las reglas dinámicas de una categoría.
     */
    public function getCategoryRules(int $categoryId): ?array
    {
        return $this->itemRepository->getCategoryRules($categoryId);
    }

    /**
     * Crea un producto.
     */
    public function create(array $input): int
    {
        $data =
            $this->prepareProductData(
                $input
            );

        $itemId =
            $this->itemRepository
                ->create(
                    $data
                );


        /*
         * Guardar opciones de precio.
         */
        $this->itemRepository
            ->syncPriceOptions(
                $itemId,
                $input['price_options']
                ?? []
            );


        return $itemId;
    }

    /**
     * Actualiza un producto.
     */
    public function update(
        int $id,
        array $input
    ): bool {

        if ($id <= 0) {
            return false;
        }

        $existing = $this->itemRepository->find($id);

        if (!$existing) {
            return false;
        }

        $data = $this->prepareProductData(
            $input,
            false,
            $existing
        );

        $updated =
            $this->itemRepository
                ->update(
                    $id,
                    $data
                );


        if (!$updated) {
            return false;
        }


        /*
         * Actualizar opciones de precio.
         *
         * Si el administrador elimina todas,
         * el array estará vacío y se eliminarán
         * las opciones existentes.
         */
        $this->itemRepository
            ->syncPriceOptions(
                $id,
                $input['price_options']
                ?? []
            );


        return true;
    }

    /**
     * Guarda imágenes nuevas de un producto.
     */
    public function saveImages(
        int $itemId,
        array $files,
        array $newKeys = [],
        array $order = [],
        ?string $favoriteKey = null,
        array $deletedIds = []
    ): array {

        if ($itemId <= 0) {

            throw new InvalidArgumentException(
                'ID de producto inválido.'
            );
        }


        $item =
            $this->itemRepository
                ->find($itemId);


        /*
 * =====================================================
 * ELIMINAR IMÁGENES
 * =====================================================
 */

        foreach (
            $deletedIds
            as $deletedId
        ) {

            $deletedId =
                (int) $deletedId;


            if (
                $deletedId <= 0
            ) {
                continue;
            }


            $image =
                $this->itemImageRepository
                    ->find(
                        $deletedId
                    );


            /*
             * Seguridad:
             * la imagen debe pertenecer
             * al producto actual.
             */
            if (
                !$image ||
                (int) $image['item_id']
                !== $itemId
            ) {

                continue;
            }


            /*
             * Eliminar archivo físico.
             */
            $imagePath =
                __DIR__ .
                '/../../public/' .
                ltrim(
                    $image['image_path'],
                    '/\\'
                );


            if (
                is_file(
                    $imagePath
                )
            ) {

                @unlink(
                    $imagePath
                );
            }


            /*
             * Eliminar registro de BD.
             */
            $this->itemImageRepository
                ->delete(
                    $deletedId
                );
        }

        if (!$item) {

            throw new InvalidArgumentException(
                'El producto no existe.'
            );
        }


        $existingImages =
            $this->itemImageRepository
                ->getByItemId($itemId);


        /*
         * Máximo 20 imágenes.
         */
        if (
            count($existingImages) +
            count($newKeys)
            > 20
        ) {

            throw new InvalidArgumentException(
                'El producto puede tener máximo 20 imágenes.'
            );
        }


        /*
         * Normalizar archivos.
         */
        $uploadedFiles =
            $this->normalizeUploadFiles(
                $files
            );


        if (
            count($uploadedFiles) !==
            count($newKeys)
        ) {

            throw new InvalidArgumentException(
                'No fue posible relacionar las imágenes seleccionadas.'
            );
        }


        $createdImages = [];


        /*
         * Subir físicamente las imágenes.
         */
        foreach (
            $uploadedFiles
            as $index => $file
        ) {

            $key =
                (string) (
                    $newKeys[$index]
                    ?? ''
                );


            if ($key === '') {

                throw new InvalidArgumentException(
                    'Una imagen no tiene identificador temporal.'
                );
            }


            $meta =
                $this->storeItemImageFile(
                    $itemId,
                    $file
                );


            $imageId =
                $this->itemImageRepository
                    ->create(
                        $itemId,
                        $meta['image_path'],
                        $meta['image_alt'],
                        9999,
                        false
                    );


            $createdImages[$key] = [
                'id' =>
                    $imageId,

                'image_path' =>
                    $meta['image_path']
            ];
        }


        /*
         * Construir mapa de imágenes existentes.
         */
        $imageIds = [];


        foreach (
            $existingImages
            as $image
        ) {

            $imageIds[
            'existing:' .
            $image['id']
            ] =
                (int) $image['id'];
        }


        /*
         * Agregar las nuevas.
         */
        foreach (
            $createdImages
            as $key => $image
        ) {

            $imageIds[
            'new:' . $key
            ] =
                (int) $image['id'];
        }


        /*
         * Guardar orden.
         */
        foreach (
            $order
            as $position => $token
        ) {

            $token =
                (string) $token;


            if (
                !isset(
                    $imageIds[$token]
                )
            ) {
                continue;
            }


            $this->itemImageRepository
                ->updateOrder(
                    $imageIds[$token],
                    $position
                );
        }

        /*
         * =====================================================
         * ASEGURAR IMAGEN PRINCIPAL
         * =====================================================
        */

        $remainingImages =
            $this->itemImageRepository
                ->getByItemId(
                    $itemId
                );


        if (
            !empty($remainingImages) &&
            empty($favoriteKey)
        ) {

            $firstRemaining =
                $remainingImages[0];


            $this->itemImageRepository
                ->setFavorite(
                    $itemId,
                    (int) $firstRemaining['id']
                );
        }

        /*
         * Guardar favorita.
         */
        if (
            $favoriteKey &&
            isset(
                $imageIds[$favoriteKey]
            )
        ) {

            $this->itemImageRepository
                ->setFavorite(
                    $itemId,
                    $imageIds[$favoriteKey]
                );

        } elseif (
            empty($existingImages) &&
            !empty($createdImages)
        ) {

            /*
             * Si es la primera imagen
             * del producto, será principal.
             */
            $firstId =
                reset(
                    $createdImages
                )['id'];


            $this->itemImageRepository
                ->setFavorite(
                    $itemId,
                    $firstId
                );
        }


        return
            $this->itemImageRepository
                ->getByItemId(
                    $itemId
                );
    }

    /**
     * Desactiva un producto.
     *
     * Si ya posee historial de pedidos:
     *   NO se elimina físicamente.
     *
     * Se realiza baja lógica.
     */
    public function deactivate(int $id): bool
    {
        if ($id <= 0) {
            return false;
        }

        $existing = $this->itemRepository->find($id);

        if (!$existing) {
            return false;
        }

        return $this->itemRepository->deactivate($id);
    }

    /**
     * Activa nuevamente un producto.
     */
    public function activate(int $id): bool
    {
        if ($id <= 0) {
            return false;
        }

        $existing = $this->itemRepository->find($id);

        if (!$existing) {
            return false;
        }

        return $this->itemRepository->activate($id);
    }

    /**
     * Prepara y valida los datos de un producto.
     */
    private function prepareProductData(
        array $input,
        bool $isCreate = true,
        ?array $existing = null
    ): array {

        /*
         * ------------------------------------------------------
         * NOMBRE
         * ------------------------------------------------------
         */
        $name = $this->cleanText(
            $input['name'] ?? ''
        );

        if ($name === '') {
            throw new InvalidArgumentException(
                'El nombre del producto es obligatorio.'
            );
        }

        if (mb_strlen($name) > 150) {
            throw new InvalidArgumentException(
                'El nombre del producto no puede superar los 150 caracteres.'
            );
        }


        /*
         * ------------------------------------------------------
         * CATEGORÍA
         * ------------------------------------------------------
         */
        $categoryId = (int) (
            $input['category_id']
            ?? ($existing['category_id'] ?? 0)
        );

        if ($categoryId <= 0) {
            throw new InvalidArgumentException(
                'Debe seleccionar una categoría.'
            );
        }

        $category = $this->itemRepository
            ->getCategoryById($categoryId);

        if (!$category) {
            throw new InvalidArgumentException(
                'La categoría seleccionada no existe.'
            );
        }

        /*
         * Solo permitimos categorías configuradas
         * para Burger Town.
         */
        $rules = $this->itemRepository
            ->getCategoryRules($categoryId);

        if (!$rules) {
            throw new InvalidArgumentException(
                'La categoría seleccionada no está configurada para el catálogo de Burger Town.'
            );
        }


        /*
         * ------------------------------------------------------
         * PRECIO
         * ------------------------------------------------------
         */
        if (
            $isCreate &&
            !isset($input['price'])
        ) {
            throw new InvalidArgumentException(
                'El precio del producto es obligatorio.'
            );
        }

        $price = $this->normalizeMoney(
            $input['price']
            ?? ($existing['price'] ?? 0)
        );

        if ($price < 0) {
            throw new InvalidArgumentException(
                'El precio no puede ser negativo.'
            );
        }


        /*
         * ------------------------------------------------------
         * SKU
         * ------------------------------------------------------
         */
        $sku = $this->cleanText(
            $input['sku']
            ?? ($existing['sku'] ?? '')
        );

        if ($isCreate && $sku === '') {
            $sku = $this->generateSku($name);
        }

        if ($sku === '') {
            throw new InvalidArgumentException(
                'El SKU del producto es obligatorio.'
            );
        }

        if (mb_strlen($sku) > 50) {
            throw new InvalidArgumentException(
                'El SKU no puede superar los 50 caracteres.'
            );
        }


        /*
         * ------------------------------------------------------
         * SLUG
         * ------------------------------------------------------
         *
         * Al crear:
         *   se genera automáticamente.
         *
         * Al editar:
         *   conservamos el slug existente.
         */
        if ($isCreate) {
            $slug = $this->generateSlug($name);
        } else {
            $slug = $existing['slug']
                ?? $this->generateSlug($name);
        }


        /*
         * ------------------------------------------------------
         * DESCRIPCIÓN
         * ------------------------------------------------------
         */
        $description = $this->cleanLongText(
            $input['description'] ?? ''
        );


        /*
         * ------------------------------------------------------
         * INGREDIENTES
         * ------------------------------------------------------
         */
        $ingredients = null;

        if ((int) $rules['show_ingredients'] === 1) {

            $ingredients = $this->nullableLongText(
                $input['ingredients'] ?? null
            );
        }


        /*
         * ------------------------------------------------------
         * PRECIO COMBO
         * ------------------------------------------------------
         */
        $comboPrice = null;

        if ((int) $rules['show_combo_price'] === 1) {

            $rawComboPrice =
                $input['combo_price']
                ?? null;

            if (
                $rawComboPrice !== null &&
                $rawComboPrice !== ''
            ) {

                $comboPrice = $this->normalizeMoney(
                    $rawComboPrice
                );

                if ($comboPrice < 0) {
                    throw new InvalidArgumentException(
                        'El precio del combo no puede ser negativo.'
                    );
                }
            }
        }


        /*
         * ------------------------------------------------------
         * PORCIÓN
         * ------------------------------------------------------
         */
        $portionSize = null;

        if ((int) $rules['show_portion'] === 1) {

            $portionSize =
                $this->nullableShortText(
                    $input['portion_size'] ?? null
                );
        }


        /*
         * ------------------------------------------------------
         * NIVEL DE PICANTE
         * ------------------------------------------------------
         */
        $spicyLevel = 0;

        if ((int) $rules['show_spicy_level'] === 1) {

            $spicyLevel = (int) (
                $input['spicy_level'] ?? 0
            );

            if (
                $spicyLevel < 0 ||
                $spicyLevel > 3
            ) {
                throw new InvalidArgumentException(
                    'El nivel de picante debe estar entre 0 y 3.'
                );
            }
        }


        /*
         * ------------------------------------------------------
         * ALÉRGENOS
         * ------------------------------------------------------
         */
        $allergens = null;

        if ((int) $rules['show_allergens'] === 1) {

            $allergens =
                $this->nullableLongText(
                    $input['allergens'] ?? null
                );
        }


        /*
         * ------------------------------------------------------
         * NOTAS DE PREPARACIÓN
         * ------------------------------------------------------
         */
        $preparationNotes =
            $this->nullableLongText(
                $input['preparation_notes'] ?? null
            );


        /*
         * ------------------------------------------------------
         * COSTO
         * ------------------------------------------------------
         */
        $costPrice = $this->normalizeMoney(
            $input['cost_price']
            ?? ($existing['cost_price'] ?? 0)
        );

        if ($costPrice < 0) {
            throw new InvalidArgumentException(
                'El costo no puede ser negativo.'
            );
        }


        /*
         * ------------------------------------------------------
         * IVA / IMPUESTO
         * ------------------------------------------------------
         */
        $taxRate = (float) (
            $input['tax_rate']
            ?? ($existing['tax_rate'] ?? 0)
        );

        if (
            $taxRate < 0 ||
            $taxRate > 100
        ) {
            throw new InvalidArgumentException(
                'La tasa de impuesto debe estar entre 0 y 100.'
            );
        }


        /*
         * ------------------------------------------------------
         * DURACIÓN
         * ------------------------------------------------------
         */
        $durationMinutes = (int) (
            $input['duration_minutes']
            ?? ($existing['duration_minutes'] ?? 0)
        );

        if ($durationMinutes < 0) {
            $durationMinutes = 0;
        }


        /*
         * ------------------------------------------------------
         * DESTACADO
         * ------------------------------------------------------
         */
        $isFeatured = 0;

        if ((int) $rules['show_featured'] === 1) {

            $isFeatured = $this->toBooleanInt(
                $input['is_featured']
                ?? ($existing['is_featured'] ?? 0)
            );
        }


        /*
         * ------------------------------------------------------
         * DISPONIBILIDAD
         * ------------------------------------------------------
         */
        $isAvailable = $this->toBooleanInt(
            $input['is_available']
            ?? ($existing['is_available'] ?? 1)
        );


        /*
         * ------------------------------------------------------
         * ACTIVIDAD
         * ------------------------------------------------------
         */
        $isActive = $this->toBooleanInt(
            $input['is_active']
            ?? ($existing['is_active'] ?? 1)
        );


        /*
         * ------------------------------------------------------
         * ORDEN
         * ------------------------------------------------------
         */
        $displayOrder = (int) (
            $input['display_order']
            ?? ($existing['display_order'] ?? 0)
        );

        if ($displayOrder < 0) {
            $displayOrder = 0;
        }


        /*
         * ------------------------------------------------------
         * TIPO
         * ------------------------------------------------------
         *
         * Este módulo administra productos gastronómicos.
         */
        return [
            'category_id'       => $categoryId,
            'type'              => 'restaurant_dish',
            'sku'               => $sku,
            'name'              => $name,
            'slug'              => $slug,
            'description'       => $description,
            'ingredients'       => $ingredients,
            'price'             => $price,
            'combo_price'       => $comboPrice,
            'portion_size'      => $portionSize,
            'spicy_level'       => $spicyLevel,
            'allergens'         => $allergens,
            'preparation_notes' => $preparationNotes,
            'cost_price'        => $costPrice,
            'tax_rate'          => $taxRate,
            'duration_minutes'  => $durationMinutes,
            'is_active'         => $isActive,
            'is_featured'       => $isFeatured,
            'is_available'      => $isAvailable,
            'display_order'     => $displayOrder
        ];
    }

    /**
     * Limpia textos cortos.
     */
    private function cleanText(
        mixed $value
    ): string {

        if ($value === null) {
            return '';
        }

        return trim(
            strip_tags(
                (string) $value
            )
        );
    }

    /**
     * Limpia textos largos.
     */
    private function cleanLongText(
        mixed $value
    ): ?string {

        if ($value === null) {
            return null;
        }

        $value = trim(
            strip_tags(
                (string) $value
            )
        );

        return $value === ''
            ? null
            : $value;
    }

    /**
     * Texto corto nullable.
     */
    private function nullableShortText(
        mixed $value
    ): ?string {

        $value = $this->cleanText($value);

        return $value === ''
            ? null
            : $value;
    }

    /**
     * Texto largo nullable.
     */
    private function nullableLongText(
        mixed $value
    ): ?string {
        return $this->cleanLongText($value);
    }

    /**
     * Normaliza valores monetarios.
     *
     * Acepta:
     * 35000
     * 35000.50
     * "35.000"
     * "35000,50"
     */
    private function normalizeMoney(
        mixed $value
    ): float {

        if ($value === null || $value === '') {
            return 0.00;
        }

        $value = trim(
            str_replace(
                ['$', ' '],
                '',
                (string) $value
            )
        );

        /*
         * Caso colombiano:
         * 35.000,50
         */
        if (
            str_contains($value, '.') &&
            str_contains($value, ',')
        ) {

            $value = str_replace(
                '.',
                '',
                $value
            );

            $value = str_replace(
                ',',
                '.',
                $value
            );

        } elseif (
            str_contains($value, ',')
        ) {

            /*
             * 35000,50
             */
            $value = str_replace(
                ',',
                '.',
                $value
            );
        }

        return round(
            (float) $value,
            2
        );
    }

    /**
     * Convierte diferentes valores a 0/1.
     */
    private function toBooleanInt(
        mixed $value
    ): int {

        return (
            $value === true ||
            $value === 1 ||
            $value === '1' ||
            $value === 'true' ||
            $value === 'on'
        )
            ? 1
            : 0;
    }

    /**
     * Genera un SKU único.
     */
    private function generateSku(
        string $name
    ): string {

        $base = strtoupper(
            preg_replace(
                '/[^A-Z0-9]/',
                '',
                iconv(
                    'UTF-8',
                    'ASCII//TRANSLIT//IGNORE',
                    strtoupper($name)
                )
            )
        );

        $prefix = substr(
            $base !== ''
                ? $base
                : 'ITEM',
            0,
            8
        );

        try {

            $random = strtoupper(
                bin2hex(
                    random_bytes(3)
                )
            );

        } catch (Throwable $e) {

            $random = strtoupper(
                substr(
                    md5(
                        uniqid(
                            '',
                            true
                        )
                    ),
                    0,
                    6
                )
            );
        }

        return 'BT-' . $prefix . '-' . $random;
    }

    /**
     * Genera un slug único.
     */
    private function generateSlug(
        string $name
    ): string {

        $slug = strtolower(
            trim($name)
        );

        $converted = iconv(
            'UTF-8',
            'ASCII//TRANSLIT//IGNORE',
            $slug
        );

        if ($converted !== false) {
            $slug = $converted;
        }

        $slug = preg_replace(
            '/[^a-z0-9]+/',
            '-',
            $slug
        );

        $slug = trim(
            $slug,
            '-'
        );

        if ($slug === '') {
            $slug = 'producto';
        }

        try {

            $suffix = strtolower(
                bin2hex(
                    random_bytes(4)
                )
            );

        } catch (Throwable $e) {

            $suffix = (string) time();
        }

        return $slug . '-' . $suffix;
    }

    /**
     * Normaliza $_FILES.
     */
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


    /**
     * Guarda físicamente una imagen del producto.
     */
    private function storeItemImageFile(
        int $itemId,
        array $file
    ): array {

        if (
            ($file['error'] ?? UPLOAD_ERR_NO_FILE)
            !== UPLOAD_ERR_OK
        ) {

            throw new InvalidArgumentException(
                'No fue posible cargar una de las imágenes.'
            );
        }


        if (
            empty($file['tmp_name']) ||
            !is_uploaded_file(
                $file['tmp_name']
            )
        ) {

            throw new InvalidArgumentException(
                'Una de las imágenes no es válida.'
            );
        }


        $maxSize =
            8 * 1024 * 1024;


        if (
            (int) $file['size']
            > $maxSize
        ) {

            throw new InvalidArgumentException(
                'Las imágenes no pueden superar los 8 MB.'
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

            throw new InvalidArgumentException(
                'Solo se permiten imágenes JPG, PNG o WEBP.'
            );
        }


        $dimensions =
            @getimagesize(
                $file['tmp_name']
            );


        if (
            !$dimensions
        ) {

            throw new InvalidArgumentException(
                'Una de las imágenes no es válida.'
            );
        }


        $width =
            (int) $dimensions[0];


        $height =
            (int) $dimensions[1];


        if (
            $width > 8000 ||
            $height > 8000
        ) {

            throw new InvalidArgumentException(
                'Las imágenes no pueden superar 8000 × 8000 píxeles.'
            );
        }


        $directory =
            __DIR__ .
            '/../../public/uploads/items/' .
            $itemId .
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
            'item-' .
            $itemId .
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

            'image_path' =>
                'uploads/items/' .
                $itemId .
                '/' .
                $filename,

            'image_alt' =>
                mb_substr(
                    trim(
                        (string) (
                            $file['name']
                            ?? 'Imagen del producto'
                        )
                    ),
                    0,
                    255
                )
        ];
    }
}