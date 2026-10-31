<?php
// app/Controllers/ItemController.php

class ItemController extends Controller
{
    private ItemService $itemService;

    public function __construct()
    {
        $this->itemService = new ItemService();
    }

    /*
    |--------------------------------------------------------------------------
    | ADMIN WEB
    |--------------------------------------------------------------------------
    */

    /**
     * GET /admin/items
     *
     * Página principal del módulo de productos.
     */
    public function adminIndex(): void
    {
        $this->view(
            'admin/module-layout',
            [
                'contentView' => __DIR__ . '/../Views/admin/items.php'
            ]
        );
    }

    /**
     * GET /admin/items/create
     */
    public function create(): void
    {
        try {

            $categories =
                $this->itemService
                    ->getCategories();

            $this->view(
                'admin/module-layout',
                [
                    'contentView' =>
                        __DIR__ .
                        '/../Views/admin/item-form.php',

                    'itemFormMode' =>
                        'create',

                    'itemFormItem' =>
                        null,

                    'itemFormCategories' =>
                        $categories,

                    'itemFormImages' =>
                        []
                ]
            );

        } catch (Throwable $e) {

            error_log(
                '[ITEM CREATE] ' .
                $e->getMessage()
            );

            http_response_code(500);

            echo 'No fue posible cargar el formulario.';
        }
    }


    /**
     * GET /admin/items/edit?id=1
     */
    public function edit(): void
    {
        try {

            $id =
                (int) (
                    $_GET['id']
                    ?? 0
                );


            if ($id <= 0) {

                http_response_code(400);

                echo 'ID de producto inválido.';

                return;
            }


            $item =
                $this->itemService
                    ->getById($id);


            if (!$item) {

                http_response_code(404);

                echo 'Producto no encontrado.';

                return;
            }


            $categories =
                $this->itemService
                    ->getCategories();


            $itemImages =
                $item['images']
                ?? [];


            $this->view(
                'admin/module-layout',
                [
                    'contentView' =>
                        __DIR__ .
                        '/../Views/admin/item-form.php',

                    'itemFormMode' =>
                        'edit',

                    'itemFormItem' =>
                        $item,

                    'itemFormCategories' =>
                        $categories,

                    'itemFormImages' =>
                        $itemImages
                ]
            );

        } catch (Throwable $e) {

            error_log(
                '[ITEM EDIT] ' .
                $e->getMessage()
            );

            http_response_code(500);

            echo 'No fue posible cargar el producto.';
        }
    }

    /*
    |--------------------------------------------------------------------------
    | ADMIN API
    |--------------------------------------------------------------------------
    */

    /**
     * GET /api/admin/items
     */
    public function data(): void
    {
        try {

            $items = $this->itemService->getAll(true);

            $this->json([
                'status' => 'success',
                'data'   => $items
            ]);

        } catch (Throwable $e) {

            $this->json([
                'status'  => 'error',
                'message' => 'No fue posible obtener los productos.'
            ], 500);
        }
    }

    /**
     * GET /api/admin/items/show?id=1
     */
    public function adminShow(): void
    {
        $id = (int) ($_GET['id'] ?? 0);

        if ($id <= 0) {
            $this->json([
                'status'  => 'error',
                'message' => 'ID de producto inválido.'
            ], 400);

            return;
        }

        try {

            $item = $this->itemService->getById($id);

            if (!$item) {
                $this->json([
                    'status'  => 'error',
                    'message' => 'Producto no encontrado.'
                ], 404);

                return;
            }

            $this->json([
                'status' => 'success',
                'data'   => $item
            ]);

        } catch (Throwable $e) {

            $this->json([
                'status'  => 'error',
                'message' => 'No fue posible obtener el producto.'
            ], 500);
        }
    }

    /**
     * GET /api/admin/items/categories
     */
    public function categories(): void
    {
        try {

            $categories = $this->itemService->getCategories();

            $this->json([
                'status' => 'success',
                'data'   => $categories
            ]);

        } catch (Throwable $e) {

            $this->json([
                'status'  => 'error',
                'message' => 'No fue posible obtener las categorías.'
            ], 500);
        }
    }

    /**
     * GET /api/admin/items/category-rules?id=1
     */
    public function categoryRules(): void
    {
        $categoryId = (int) (
            $_GET['id'] ?? 0
        );

        if ($categoryId <= 0) {
            $this->json([
                'status'  => 'error',
                'message' => 'Categoría inválida.'
            ], 400);

            return;
        }

        try {

            $rules = $this->itemService
                ->getCategoryRules($categoryId);

            if (!$rules) {

                $this->json([
                    'status'  => 'error',
                    'message' => 'No existen reglas para esta categoría.'
                ], 404);

                return;
            }

            $this->json([
                'status' => 'success',
                'data'   => $rules
            ]);

        } catch (Throwable $e) {

            $this->json([
                'status'  => 'error',
                'message' => 'No fue posible obtener las reglas de la categoría.'
            ], 500);
        }
    }

    /**
     * POST /api/admin/items/store
     */
    public function store(): void
    {
        try {

            $input = $this->getInput();

            $itemId = $this->itemService->create($input);

            $item = $this->itemService->getById(
                $itemId
            );

            $this->json([
                'status'  => 'success',
                'message' => 'Producto creado correctamente.',
                'item_id' => $itemId,
                'data'    => $item
            ], 201);

        } catch (InvalidArgumentException $e) {

            $this->json([
                'status'  => 'error',
                'message' => $e->getMessage()
            ], 422);

        } catch (Throwable $e) {

            $this->json([
                'status'  => 'error',
                'message' => 'No fue posible crear el producto.'
            ], 500);
        }
    }

    /**
     * POST /api/admin/items/update
     */
    public function update(): void
    {
        try {

            $input = $this->getInput();

            $id = (int) (
                $input['id'] ?? 0
            );

            if ($id <= 0) {

                $this->json([
                    'status'  => 'error',
                    'message' => 'ID de producto inválido.'
                ], 400);

                return;
            }

            $updated = $this->itemService->update(
                $id,
                $input
            );

            if (!$updated) {

                $this->json([
                    'status'  => 'error',
                    'message' => 'Producto no encontrado o no pudo actualizarse.'
                ], 404);

                return;
            }

            $item = $this->itemService->getById($id);

            $this->json([
                'status'  => 'success',
                'message' => 'Producto actualizado correctamente.',
                'data'    => $item
            ]);

        } catch (InvalidArgumentException $e) {

            $this->json([
                'status'  => 'error',
                'message' => $e->getMessage()
            ], 422);

        } catch (Throwable $e) {

            $this->json([
                'status'  => 'error',
                'message' => 'No fue posible actualizar el producto.'
            ], 500);
        }
    }

    /**
     * POST /api/admin/items/images/save
     */
    public function saveImages(): void
    {
        try {

            $itemId =
                (int) (
                    $_POST['item_id']
                    ?? 0
                );


            if (
                $itemId <= 0
            ) {

                $this->json([
                    'status' =>
                        'error',

                    'message' =>
                        'ID de producto inválido.'
                ], 400);

                return;
            }


            $newKeys =
                json_decode(
                    $_POST['new_keys']
                    ?? '[]',
                    true
                );

            $deletedIds =
                json_decode(
                    $_POST['deleted_ids']
                    ?? '[]',
                    true
                );

            $order =
                json_decode(
                    $_POST['order']
                    ?? '[]',
                    true
                );


            $favoriteKey =
                $_POST['favorite']
                ?? null;


            if (
                !is_array($newKeys)
            ) {
                $newKeys = [];
            }

            if (
                !is_array($deletedIds)
            ) {
                $deletedIds = [];
            }


            if (
                !is_array($order)
            ) {
                $order = [];
            }


            $images =
                $this->itemService
                    ->saveImages(
                        $itemId,
                        $_FILES['images']
                        ?? [],
                        $newKeys,
                        $order,
                        $favoriteKey,
                        $deletedIds
                    );


            $this->json([

                'status' =>
                    'success',

                'message' =>
                    'Imágenes guardadas correctamente.',

                'data' => [
                    'images' =>
                        $images
                ]

            ]);


        } catch (
        InvalidArgumentException $e
        ) {

            $this->json([

                'status' =>
                    'error',

                'message' =>
                    $e->getMessage()

            ], 422);


        } catch (
        Throwable $e
        ) {

            error_log(
                '[ITEM IMAGES] ' .
                $e->getMessage()
            );


            $this->json([

                'status' =>
                    'error',

                'message' =>
                    'No fue posible guardar las imágenes.'

            ], 500);
        }
    }

    /**
     * POST /api/admin/items/delete
     */
    public function delete(): void
    {
        try {

            $input = $this->getInput();

            $id = (int) (
                $input['id'] ?? 0
            );

            if ($id <= 0) {

                $this->json([
                    'status'  => 'error',
                    'message' => 'ID de producto inválido.'
                ], 400);

                return;
            }

            $deleted = $this->itemService->deactivate(
                $id
            );

            if (!$deleted) {

                $this->json([
                    'status'  => 'error',
                    'message' => 'Producto no encontrado.'
                ], 404);

                return;
            }

            $this->json([
                'status'  => 'success',
                'message' => 'Producto desactivado correctamente.'
            ]);

        } catch (Throwable $e) {

            $this->json([
                'status'  => 'error',
                'message' => 'No fue posible desactivar el producto.'
            ], 500);
        }
    }

    /**
     * POST /api/admin/items/activate
     */
    public function activate(): void
    {
        try {

            $input = $this->getInput();

            $id = (int) (
                $input['id'] ?? 0
            );

            if ($id <= 0) {

                $this->json([
                    'status'  => 'error',
                    'message' => 'ID de producto inválido.'
                ], 400);

                return;
            }

            $activated = $this->itemService->activate(
                $id
            );

            if (!$activated) {

                $this->json([
                    'status'  => 'error',
                    'message' => 'Producto no encontrado.'
                ], 404);

                return;
            }

            $this->json([
                'status'  => 'success',
                'message' => 'Producto activado correctamente.'
            ]);

        } catch (Throwable $e) {

            $this->json([
                'status'  => 'error',
                'message' => 'No fue posible activar el producto.'
            ], 500);
        }
    }

    /*
    |--------------------------------------------------------------------------
    | LEGACY API
    |--------------------------------------------------------------------------
    |
    | Conservamos estas rutas para no romper el código existente.
    |
    */

    /**
     * GET /api/items
     */
    public function index(): void
    {
        try {

            $items = $this->itemService->getAll();

            $this->json([
                'status' => 'success',
                'data'   => $items
            ]);

        } catch (Throwable $e) {

            $this->json([
                'status'  => 'error',
                'message' => 'No fue posible obtener los productos.'
            ], 500);
        }
    }

    /**
     * GET /api/items/show?id=1
     */
    public function show(): void
    {
        $id = (int) (
            $_GET['id'] ?? 0
        );

        $item = $this->itemService->getById(
            $id
        );

        if (!$item) {

            $this->json([
                'error' => 'Ítem no encontrado'
            ], 404);

            return;
        }

        $this->json([
            'status' => 'success',
            'data'   => $item
        ]);
    }

    /**
     * POST /api/items/store
     */
    public function legacyStore(): void
    {
        $this->store();
    }

    /**
     * POST /api/items/update
     */
    public function legacyUpdate(): void
    {
        $this->update();
    }

    /**
     * POST /api/items/delete
     */
    public function legacyDelete(): void
    {
        $this->delete();
    }

    /*
    |--------------------------------------------------------------------------
    | INPUT
    |--------------------------------------------------------------------------
    */

    /**
     * Obtiene datos enviados como:
     *
     * application/json
     * application/x-www-form-urlencoded
     * multipart/form-data
     */
    private function getInput(): array
    {
        $contentType = $_SERVER['CONTENT_TYPE'] ?? '';

        /*
         * JSON
         */
        if (
            stripos(
                $contentType,
                'application/json'
            ) !== false
        ) {

            $raw = file_get_contents(
                'php://input'
            );

            $decoded = json_decode(
                $raw,
                true
            );

            return is_array($decoded)
                ? $decoded
                : [];
        }

        /*
         * POST / multipart
         */
        if (!empty($_POST)) {
            return $_POST;
        }

        /*
         * Último intento:
         * leer JSON aunque Content-Type
         * no esté correctamente configurado.
         */
        $raw = file_get_contents(
            'php://input'
        );

        if ($raw !== '') {

            $decoded = json_decode(
                $raw,
                true
            );

            if (is_array($decoded)) {
                return $decoded;
            }
        }

        return [];
    }
}