<?php

class CategoryController extends Controller
{
    private CategoryRepository $categoryRepository;
    private CategoryPageRepository $pageRepository;



    public function __construct()
    {
        $this->categoryRepository =
            new CategoryRepository();

        $this->pageRepository =
            new CategoryPageRepository();
    }

    /*
    |--------------------------------------------------------------------------
    | ADMIN - LISTADO DE CATEGORÍAS
    |--------------------------------------------------------------------------
    */

    /**
     * GET /admin/categories
     */
    public function index(): void
    {
        $this->view(
            'admin/module-layout',
            [
                'contentView' => __DIR__ . '/../Views/admin/categories.php'
            ]
        );
    }


    /*
    |--------------------------------------------------------------------------
    | API - LISTADO DE CATEGORÍAS
    |--------------------------------------------------------------------------
    */

    /**
     * GET /api/admin/categories
     */
    public function data(): void
    {
        try {

            $categories =
                $this->categoryRepository
                    ->getAllForAdmin();

            $this->json([
                'success' => true,
                'data' => $categories
            ]);

        } catch (Throwable $e) {

            error_log(
                '[CATEGORY DATA] ' .
                $e->getMessage()
            );

            $this->json([
                'success' => false,
                'message' =>
                    'No fue posible cargar las categorías.'
            ], 500);
        }
    }


    /*
    |--------------------------------------------------------------------------
    | CREAR CATEGORÍA
    |--------------------------------------------------------------------------
    */

    /**
     * GET /admin/categories/create
     */
    public function create(): void
    {
        $parents =
            $this->categoryRepository
                ->getParents();

        $this->view(
            'admin/module-layout',
            [
                'contentView' =>
                    __DIR__ .
                    '/../Views/admin/category-form.php',

                'categoryFormMode' =>
                    'create',

                'categoryFormCategory' =>
                    null,

                'categoryFormParents' =>
                    $parents
            ]
        );
    }


    /*
    |--------------------------------------------------------------------------
    | EDITAR CATEGORÍA
    |--------------------------------------------------------------------------
    */

    /**
     * GET /admin/categories/edit?id=4
     */
    public function edit(): void
    {
        $id =
            (int) (
                $_GET['id']
                ?? 0
            );

        if ($id <= 0) {

            http_response_code(400);

            echo 'Categoría inválida.';

            return;
        }

        $category =
            $this->categoryRepository
                ->find($id);

        if (!$category) {

            http_response_code(404);

            echo 'Categoría no encontrada.';

            return;
        }

        $parents =
            $this->categoryRepository
                ->getParents($id);

        $this->view(
            'admin/module-layout',
            [
                'contentView' =>
                    __DIR__ .
                    '/../Views/admin/category-form.php',

                'categoryFormMode' =>
                    'edit',

                'categoryFormCategory' =>
                    $category,

                'categoryFormParents' =>
                    $parents
            ]
        );
    }


    /*
    |--------------------------------------------------------------------------
    | GUARDAR CATEGORÍA
    |--------------------------------------------------------------------------
    */

    /**
     * POST /api/admin/categories/store
     */
    public function store(): void
    {
        try {

            $data =
                $this->getJsonInput();

            $name =
                trim(
                    (string) (
                        $data['name']
                        ?? ''
                    )
                );

            $slug =
                trim(
                    (string) (
                        $data['slug']
                        ?? ''
                    )
                );

            $parentId =
                !empty($data['parent_id'])
                    ? (int) $data['parent_id']
                    : null;

            $catalogType =
                $data['catalog_type']
                ?? 'general';

            $description =
                trim(
                    (string) (
                        $data['description']
                        ?? ''
                    )
                );

            $isActive =
                !empty(
                $data['is_active']
                )
                    ? 1
                    : 0;


            if ($name === '') {

                $this->json([
                    'success' => false,
                    'message' =>
                        'El nombre de la categoría es obligatorio.'
                ], 422);

                return;
            }

            if ($slug === '') {

                $this->json([
                    'success' => false,
                    'message' =>
                        'El slug de la categoría es obligatorio.'
                ], 422);

                return;
            }

            if (
                !in_array(
                    $catalogType,
                    [
                        'general',
                        'restaurant'
                    ],
                    true
                )
            ) {

                $catalogType =
                    'general';
            }

            $id =
                $this->categoryRepository
                    ->create([
                        'parent_id' =>
                            $parentId,

                        'name' =>
                            $name,

                        'slug' =>
                            $slug,

                        'catalog_type' =>
                            $catalogType,

                        'description' =>
                            $description,

                        'is_active' =>
                            $isActive
                    ]);


            /*
            |--------------------------------------------------------------------------
            | REGLAS PARA CATEGORÍAS DE RESTAURANTE
            |--------------------------------------------------------------------------
            */

            if ($catalogType === 'restaurant') {

                $this->categoryRepository
                    ->createRestaurantRules($id);
            }

            $this->json([
                'success' => true,
                'message' =>
                    'Categoría creada correctamente.',
                'id' => $id
            ]);

        } catch (PDOException $e) {

            if (
                str_contains(
                    strtolower(
                        $e->getMessage()
                    ),
                    'duplicate'
                )
            ) {

                $this->json([
                    'success' => false,
                    'message' =>
                        'Ya existe una categoría con ese slug.'
                ], 422);

                return;
            }

            error_log(
                '[CATEGORY STORE] ' .
                $e->getMessage()
            );

            $this->json([
                'success' => false,
                'message' =>
                    'No fue posible crear la categoría.'
            ], 500);
        }
    }


    /*
    |--------------------------------------------------------------------------
    | ACTUALIZAR CATEGORÍA
    |--------------------------------------------------------------------------
    */

    /**
     * POST /api/admin/categories/update
     */
    public function update(): void
    {
        try {

            $data =
                $this->getJsonInput();

            $id =
                (int) (
                    $data['id']
                    ?? 0
                );

            if ($id <= 0) {

                $this->json([
                    'success' => false,
                    'message' =>
                        'Categoría inválida.'
                ], 422);

                return;
            }

            $category =
                $this->categoryRepository
                    ->find($id);

            if (!$category) {

                $this->json([
                    'success' => false,
                    'message' =>
                        'La categoría no existe.'
                ], 404);

                return;
            }

            $name =
                trim(
                    (string) (
                        $data['name']
                        ?? ''
                    )
                );

            $slug =
                trim(
                    (string) (
                        $data['slug']
                        ?? ''
                    )
                );

            $parentId =
                !empty($data['parent_id'])
                    ? (int) $data['parent_id']
                    : null;

            $catalogType =
                $data['catalog_type']
                ?? 'general';

            $description =
                trim(
                    (string) (
                        $data['description']
                        ?? ''
                    )
                );

            $isActive =
                !empty(
                $data['is_active']
                )
                    ? 1
                    : 0;


            if ($name === '') {

                $this->json([
                    'success' => false,
                    'message' =>
                        'El nombre de la categoría es obligatorio.'
                ], 422);

                return;
            }


            /*
             * Evitar que una categoría
             * sea su propio padre.
             */

            if (
                $parentId !== null &&
                $parentId === $id
            ) {

                $this->json([
                    'success' => false,
                    'message' =>
                        'Una categoría no puede ser su propio padre.'
                ], 422);

                return;
            }


            if (
                !in_array(
                    $catalogType,
                    [
                        'general',
                        'restaurant'
                    ],
                    true
                )
            ) {

                $catalogType =
                    'general';
            }


            $updated =
                $this->categoryRepository
                    ->update(
                        $id,
                        [
                            'parent_id' =>
                                $parentId,

                            'name' =>
                                $name,

                            'slug' =>
                                $slug,

                            'catalog_type' =>
                                $catalogType,

                            'description' =>
                                $description,

                            'is_active' =>
                                $isActive
                        ]
                    );

            if (!$updated) {

                $this->json([
                    'success' => false,
                    'message' =>
                        'No fue posible actualizar la categoría.'
                ], 500);

                return;
            }

            $this->json([
                'success' => true,
                'message' =>
                    'Categoría actualizada correctamente.'
            ]);

        } catch (PDOException $e) {

            if (
                str_contains(
                    strtolower(
                        $e->getMessage()
                    ),
                    'duplicate'
                )
            ) {

                $this->json([
                    'success' => false,
                    'message' =>
                        'Ya existe una categoría con ese slug.'
                ], 422);

                return;
            }

            error_log(
                '[CATEGORY UPDATE] ' .
                $e->getMessage()
            );

            $this->json([
                'success' => false,
                'message' =>
                    'No fue posible actualizar la categoría.'
            ], 500);
        }
    }


    /*
    |--------------------------------------------------------------------------
    | ACTIVAR / DESACTIVAR
    |--------------------------------------------------------------------------
    */

    /**
     * POST /api/admin/categories/toggle
     */
    public function toggle(): void
    {
        try {

            $data =
                $this->getJsonInput();

            $id =
                (int) (
                    $data['id']
                    ?? 0
                );

            $isActive =
                !empty(
                $data['is_active']
                )
                    ? 1
                    : 0;

            if ($id <= 0) {

                $this->json([
                    'success' => false,
                    'message' =>
                        'Categoría inválida.'
                ], 422);

                return;
            }

            $updated =
                $this->categoryRepository
                    ->setActive(
                        $id,
                        $isActive
                    );

            if (!$updated) {

                $this->json([
                    'success' => false,
                    'message' =>
                        'No fue posible cambiar el estado.'
                ], 500);

                return;
            }

            $this->json([
                'success' => true,
                'message' =>
                    $isActive
                        ? 'Categoría activada correctamente.'
                        : 'Categoría desactivada correctamente.'
            ]);

        } catch (Throwable $e) {

            error_log(
                '[CATEGORY TOGGLE] ' .
                $e->getMessage()
            );

            $this->json([
                'success' => false,
                'message' =>
                    'No fue posible cambiar el estado.'
            ], 500);
        }
    }


    /*
    |--------------------------------------------------------------------------
    | ADMIN - CONFIGURACIÓN DE PÁGINA
    |--------------------------------------------------------------------------
    */

    /**
     * GET /admin/categories/page?id=4
     */
    public function page(): void
    {
        $categoryId =
            (int) (
                $_GET['id']
                ?? 0
            );

        if ($categoryId <= 0) {

            http_response_code(400);

            echo 'Categoría inválida.';

            return;
        }

        $category =
            $this->categoryRepository
                ->find($categoryId);

        if (!$category) {

            http_response_code(404);

            echo 'Categoría no encontrada.';

            return;
        }

        $page =
            $this->pageRepository
                ->getPageByCategoryId(
                    $categoryId
                );

        $images = [];

        if ($page) {

            $pageId =
                (int) $page['id'];


            $images =
                $this->pageRepository
                    ->getImages(
                        $pageId
                    );


            /*
            |--------------------------------------------------------------------------
            | FAVORITA POR DEFECTO
            |--------------------------------------------------------------------------
            |
            | Si existen imágenes pero ninguna está marcada
            | como favorita, la primera se convierte en principal.
            |
            */

            $favoriteExists =
                false;


            foreach (
                $images
                as $image
            ) {

                if (
                    (int) (
                        $image['is_favorite']
                        ?? 0
                    ) === 1
                ) {

                    $favoriteExists =
                        true;

                    break;
                }
            }


            if (
                !$favoriteExists &&
                !empty($images)
            ) {

                $firstImageId =
                    (int) (
                        $images[0]['id']
                        ?? 0
                    );


                if (
                    $firstImageId > 0
                ) {

                    $this->pageRepository
                        ->setFavorite(
                            $pageId,
                            $firstImageId
                        );


                    /*
                    |------------------------------------------------------------------
                    | Recargar para que is_favorite = 1
                    |------------------------------------------------------------------
                    */

                    $images =
                        $this->pageRepository
                            ->getImages(
                                $pageId
                            );
                }
            }
        }

        $this->view(
            'admin/module-layout',
            [
                'contentView' =>
                    __DIR__ .
                    '/../Views/admin/category-page.php',

                'categoryPageCategory' =>
                    $category,

                'categoryPageData' =>
                    $page,

                'categoryPageImages' =>
                    $images
            ]
        );
    }


    /*
    |--------------------------------------------------------------------------
    | GUARDAR PÁGINA
    |--------------------------------------------------------------------------
    */

    /**
     * POST /api/admin/categories/page/save
     */
    public function savePage(): void
    {
        try {

            $data =
                $this->getRequestData();

            $categoryId =
                (int) (
                    $data['category_id']
                    ?? 0
                );

            $title =
                trim(
                    (string) (
                        $data['title']
                        ?? ''
                    )
                );

            $description =
                trim(
                    (string) (
                        $data['description']
                        ?? ''
                    )
                );


            if ($categoryId <= 0) {

                $this->json([
                    'success' => false,
                    'message' =>
                        'Categoría inválida.'
                ], 422);

                return;
            }


            if ($title === '') {

                $this->json([
                    'success' => false,
                    'message' =>
                        'El título es obligatorio.'
                ], 422);

                return;
            }


            if (
                mb_strlen($title) > 180
            ) {

                $this->json([
                    'success' => false,
                    'message' =>
                        'El título no puede superar 180 caracteres.'
                ], 422);

                return;
            }


            if (
                mb_strlen($description) > 5000
            ) {

                $this->json([
                    'success' => false,
                    'message' =>
                        'La descripción es demasiado larga.'
                ], 422);

                return;
            }


            $pageId =
                $this->pageRepository
                    ->savePage(
                        $categoryId,
                        $title,
                        $description
                    );


            $this->json([
                'success' => true,
                'message' =>
                    'Página guardada correctamente.',
                'page_id' =>
                    $pageId
            ]);

        } catch (Throwable $e) {

            error_log(
                '[CATEGORY PAGE SAVE] ' .
                $e->getMessage()
            );

            $this->json([
                'success' => false,
                'message' =>
                    'No fue posible guardar la página.'
            ], 500);
        }
    }

    /*
|--------------------------------------------------------------------------
| IMÁGENES DE LA PÁGINA DE CATEGORÍA
|--------------------------------------------------------------------------
*/

    /**
     * POST /api/admin/categories/page/images
     */
    public function saveImages(): void
    {
        /*
        |--------------------------------------------------------------------------
        | Convertir warnings PHP en excepciones
        |--------------------------------------------------------------------------
        |
        | Esto evita que PHP envíe:
        |
        | <br><b>Warning</b>...
        |
        | antes del JSON.
        |
        */

        $previousErrorHandler =
            set_error_handler(
                static function (
                    int $severity,
                    string $message,
                    string $file,
                    int $line
                ): bool {

                    if (
                        !(error_reporting() & $severity)
                    ) {
                        return false;
                    }

                    throw new ErrorException(
                        $message,
                        0,
                        $severity,
                        $file,
                        $line
                    );
                }
            );


        try {

            /*
            |--------------------------------------------------------------------------
            | PAGE ID
            |--------------------------------------------------------------------------
            */

            $pageId =
                (int) (
                    $_POST['page_id']
                    ?? 0
                );


            if (
                $pageId <= 0
            ) {

                $this->json([
                    'status' => 'error',
                    'message' =>
                        'Página de categoría inválida.'
                ], 422);

                return;
            }


            /*
            |--------------------------------------------------------------------------
            | VERIFICAR PÁGINA
            |--------------------------------------------------------------------------
            */

            $page =
                $this->pageRepository
                    ->getPageById(
                        $pageId
                    );


            if (!$page) {

                $this->json([
                    'status' => 'error',
                    'message' =>
                        'La página de categoría no existe.'
                ], 404);

                return;
            }


            /*
            |--------------------------------------------------------------------------
            | VERIFICAR ARCHIVOS
            |--------------------------------------------------------------------------
            */

            if (
                empty($_FILES['images']) ||
                !isset(
                    $_FILES['images']['name']
                )
            ) {

                $this->json([
                    'status' => 'error',
                    'message' =>
                        'No se recibieron imágenes.'
                ], 422);

                return;
            }


            $files =
                $_FILES['images'];


            /*
            |--------------------------------------------------------------------------
            | NORMALIZAR ARCHIVOS
            |--------------------------------------------------------------------------
            */

            $normalizedFiles = [];


            if (
                is_array(
                    $files['name']
                )
            ) {

                foreach (
                    $files['name']
                    as $index => $name
                ) {

                    $normalizedFiles[] = [

                        'name' =>
                            $files['name'][$index]
                            ?? '',

                        'type' =>
                            $files['type'][$index]
                            ?? '',

                        'tmp_name' =>
                            $files['tmp_name'][$index]
                            ?? '',

                        'error' =>
                            $files['error'][$index]
                            ?? UPLOAD_ERR_NO_FILE,

                        'size' =>
                            $files['size'][$index]
                            ?? 0

                    ];
                }

            } else {

                $normalizedFiles[] =
                    $files;
            }


            /*
            |--------------------------------------------------------------------------
            | DIRECTORIO
            |--------------------------------------------------------------------------
            */

            $uploadDirectory =
                __DIR__ .
                '/../../public/uploads/category-pages/' .
                $pageId;


            if (
                !is_dir(
                    $uploadDirectory
                )
            ) {

                if (
                    !mkdir(
                        $uploadDirectory,
                        0755,
                        true
                    )
                ) {

                    throw new RuntimeException(
                        'No fue posible crear el directorio de imágenes.'
                    );
                }
            }


            /*
            |--------------------------------------------------------------------------
            | IMÁGENES EXISTENTES
            |--------------------------------------------------------------------------
            */

            $existingImages =
                $this->pageRepository
                    ->getImages(
                        $pageId
                    );


            /*
            |--------------------------------------------------------------------------
            | SIGUIENTE ORDEN
            |--------------------------------------------------------------------------
            */

            $nextOrder = 0;


            foreach (
                $existingImages
                as $existingImage
            ) {

                $nextOrder =
                    max(
                        $nextOrder,
                        (
                        (int)
                        (
                            $existingImage[
                            'display_order'
                            ]
                            ?? 0
                        )
                        ) + 1
                    );
            }


            /*
            |--------------------------------------------------------------------------
            | SUBIDAS
            |--------------------------------------------------------------------------
            */

            $uploadedImages = [];


            foreach (
                $normalizedFiles
                as $file
            ) {

                /*
                |--------------------------------------------------------------------------
                | ERROR DE UPLOAD
                |--------------------------------------------------------------------------
                */

                if (
                    (
                        $file['error']
                        ?? UPLOAD_ERR_NO_FILE
                    ) !== UPLOAD_ERR_OK
                ) {

                    $this->json([
                        'status' => 'error',
                        'message' =>
                            'No fue posible subir "' .
                            (
                                $file['name']
                                ?? 'archivo'
                            ) .
                            '".'
                    ], 422);

                    return;
                }


                /*
                |--------------------------------------------------------------------------
                | TAMAÑO
                |--------------------------------------------------------------------------
                */

                if (
                    (int) $file['size'] >
                    8 * 1024 * 1024
                ) {

                    $this->json([
                        'status' => 'error',
                        'message' =>
                            'La imagen "' .
                            $file['name'] .
                            '" supera el límite de 8 MB.'
                    ], 422);

                    return;
                }


                /*
                |--------------------------------------------------------------------------
                | IMAGEN REAL
                |--------------------------------------------------------------------------
                */

                $imageInfo =
                    @getimagesize(
                        $file['tmp_name']
                    );


                if (
                    $imageInfo === false
                ) {

                    $this->json([
                        'status' => 'error',
                        'message' =>
                            'El archivo "' .
                            $file['name'] .
                            '" no es una imagen válida.'
                    ], 422);

                    return;
                }


                /*
                |--------------------------------------------------------------------------
                | MIME REAL
                |--------------------------------------------------------------------------
                */

                $finfo =
                    new finfo(
                        FILEINFO_MIME_TYPE
                    );


                $mimeType =
                    @$finfo->file(
                        $file['tmp_name']
                    );


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

                    $this->json([
                        'status' => 'error',
                        'message' =>
                            'El archivo "' .
                            $file['name'] .
                            '" debe ser JPG, PNG o WEBP.'
                    ], 422);

                    return;
                }


                /*
                |--------------------------------------------------------------------------
                | NOMBRE ORIGINAL
                |--------------------------------------------------------------------------
                */

                $originalName =
                    pathinfo(
                        $file['name'],
                        PATHINFO_FILENAME
                    );


                /*
                |--------------------------------------------------------------------------
                | NOMBRE SEGURO
                |--------------------------------------------------------------------------
                */

                $safeName =
                    preg_replace(
                        '/[^a-zA-Z0-9_-]+/',
                        '-',
                        $originalName
                    );


                $safeName =
                    trim(
                        $safeName,
                        '-'
                    );


                if (
                    $safeName === ''
                ) {

                    $safeName =
                        'category-image';
                }


                /*
                |--------------------------------------------------------------------------
                | NOMBRE ÚNICO
                |--------------------------------------------------------------------------
                */

                $fileName =
                    $safeName .
                    '-' .
                    bin2hex(
                        random_bytes(8)
                    ) .
                    '.' .
                    $allowedMimeTypes[
                    $mimeType
                    ];


                $destination =
                    $uploadDirectory .
                    DIRECTORY_SEPARATOR .
                    $fileName;


                /*
                |--------------------------------------------------------------------------
                | MOVER ARCHIVO
                |--------------------------------------------------------------------------
                */

                if (
                    !@move_uploaded_file(
                        $file['tmp_name'],
                        $destination
                    )
                ) {

                    throw new RuntimeException(
                        'No fue posible guardar "' .
                        $file['name'] .
                        '".'
                    );
                }


                /*
                |--------------------------------------------------------------------------
                | RUTA RELATIVA
                |--------------------------------------------------------------------------
                */

                $relativePath =
                    'uploads/category-pages/' .
                    $pageId .
                    '/' .
                    $fileName;


                /*
                |--------------------------------------------------------------------------
                | GUARDAR BD
                |--------------------------------------------------------------------------
                */

                $imageId =
                    $this->pageRepository
                        ->createImage(
                            $pageId,
                            $relativePath,
                            $file['name'],
                            $nextOrder
                        );


                $uploadedImages[] = [

                    'id' =>
                        $imageId,

                    'category_page_id' =>
                        $pageId,

                    'image_path' =>
                        $relativePath,

                    'image_alt' =>
                        $file['name'],

                    'display_order' =>
                        $nextOrder,

                    'is_favorite' =>
                        0,

                    'is_active' =>
                        1

                ];


                $nextOrder++;
            }


            /*
            |--------------------------------------------------------------------------
            | RECARGAR IMÁGENES
            |--------------------------------------------------------------------------
            */

            $images =
                $this->pageRepository
                    ->getImages(
                        $pageId
                    );


            /*
            |--------------------------------------------------------------------------
            | FAVORITA AUTOMÁTICA
            |--------------------------------------------------------------------------
            |
            | Si no existe ninguna favorita, la primera
            | imagen se convierte automáticamente en principal.
            |
            */

            $favoriteExists =
                false;


            foreach (
                $images
                as $image
            ) {

                if (
                    (int) (
                        $image['is_favorite']
                        ?? 0
                    ) === 1
                ) {

                    $favoriteExists =
                        true;

                    break;
                }
            }


            if (
                !$favoriteExists &&
                !empty($images)
            ) {

                $firstImageId =
                    (int) (
                        $images[0]['id']
                        ?? 0
                    );


                if (
                    $firstImageId > 0
                ) {

                    $this->pageRepository
                        ->setFavorite(
                            $pageId,
                            $firstImageId
                        );


                    $images =
                        $this->pageRepository
                            ->getImages(
                                $pageId
                            );
                }
            }


            /*
            |--------------------------------------------------------------------------
            | RESPUESTA JSON
            |--------------------------------------------------------------------------
            */

            $this->json([
                'status' => 'success',

                'message' =>
                    'Imágenes subidas correctamente.',

                'data' => [

                    'images' =>
                        $images,

                    'uploaded' =>
                        $uploadedImages

                ]
            ]);


        } catch (
        Throwable $e
        ) {

            error_log(
                '[CATEGORY PAGE IMAGES] ' .
                $e->getMessage() .
                ' in ' .
                $e->getFile() .
                ':' .
                $e->getLine()
            );


            $this->json([
                'status' => 'error',

                'message' =>
                    'No fue posible guardar las imágenes: ' .
                    $e->getMessage()
            ], 500);


        } finally {

            if (
                $previousErrorHandler
            ) {

                restore_error_handler();
            }
        }
    }


    /**
     * POST /api/admin/categories/page/images/mobile
     */
    public function saveMobileImage(): void
    {
        try {
            $pageId = (int) ($_POST['page_id'] ?? 0);
            $imageId = (int) ($_POST['image_id'] ?? 0);

            if ($pageId <= 0 || $imageId <= 0) {
                $this->json([
                    'status' => 'error',
                    'message' => 'Página o imagen inválida.'
                ], 422);
                return;
            }

            $page = $this->pageRepository->getPageById($pageId);

            if (!$page) {
                $this->json([
                    'status' => 'error',
                    'message' => 'La página de categoría no existe.'
                ], 404);
                return;
            }

            $images = $this->pageRepository->getImages($pageId);
            $imageExists = false;

            foreach ($images as $image) {
                if ((int) $image['id'] === $imageId) {
                    $imageExists = true;
                    break;
                }
            }

            if (!$imageExists) {
                $this->json([
                    'status' => 'error',
                    'message' => 'La imagen no pertenece a esta página.'
                ], 404);
                return;
            }

            $file = $_FILES['mobile_image'] ?? null;

            if (
                !$file ||
                ($file['error'] ?? UPLOAD_ERR_NO_FILE) !== UPLOAD_ERR_OK
            ) {
                $this->json([
                    'status' => 'error',
                    'message' => 'Selecciona una imagen móvil válida.'
                ], 422);
                return;
            }

            if ((int) $file['size'] > 8 * 1024 * 1024) {
                $this->json([
                    'status' => 'error',
                    'message' => 'La imagen móvil no puede superar 8 MB.'
                ], 422);
                return;
            }

            $imageInfo = @getimagesize($file['tmp_name']);
            $finfo = new finfo(FILEINFO_MIME_TYPE);
            $mime = $finfo->file($file['tmp_name']);

            $allowed = [
                'image/jpeg' => 'jpg',
                'image/png'  => 'png',
                'image/webp' => 'webp'
            ];

            if (!$imageInfo || !isset($allowed[$mime])) {
                $this->json([
                    'status' => 'error',
                    'message' => 'Solo se permiten imágenes JPG, PNG o WEBP.'
                ], 422);
                return;
            }

            $directory = __DIR__
                . '/../../public/uploads/category-pages/'
                . $pageId;

            if (!is_dir($directory) &&
                !mkdir($directory, 0755, true) &&
                !is_dir($directory)
            ) {
                throw new RuntimeException(
                    'No fue posible crear el directorio de imágenes.'
                );
            }

            $fileName = 'mobile-' . $imageId . '-'
                . bin2hex(random_bytes(8))
                . '.' . $allowed[$mime];

            $destination = $directory . DIRECTORY_SEPARATOR . $fileName;

            if (!move_uploaded_file($file['tmp_name'], $destination)) {
                throw new RuntimeException(
                    'No fue posible guardar la imagen móvil.'
                );
            }

            $relativePath = 'uploads/category-pages/'
                . $pageId . '/' . $fileName;

            $updated = $this->pageRepository->updateMobileImagePath(
                $imageId,
                $pageId,
                $relativePath
            );

            if (!$updated) {
                @unlink($destination);

                $this->json([
                    'status' => 'error',
                    'message' => 'No fue posible asociar la imagen móvil.'
                ], 500);
                return;
            }

            $this->json([
                'status' => 'success',
                'message' => 'Imagen móvil guardada correctamente.',
                'data' => [
                    'image_id' => $imageId,
                    'mobile_image_path' => $relativePath
                ]
            ]);

        } catch (Throwable $e) {
            error_log('[CATEGORY MOBILE IMAGE] ' . $e->getMessage());

            $this->json([
                'status' => 'error',
                'message' => 'Ocurrió un error al guardar la imagen móvil.'
            ], 500);
        }
    }


    /**
     * POST /api/admin/categories/page/images/favorite
     */
    public function favoriteImage(): void
    {
        try {

            $pageId =
                (int) (
                    $_POST['page_id']
                    ?? 0
                );

            $imageId =
                (int) (
                    $_POST['image_id']
                    ?? 0
                );


            if (
                $pageId <= 0 ||
                $imageId <= 0
            ) {

                $this->json([
                    'status' => 'error',
                    'message' =>
                        'Página o imagen inválida.'
                ], 422);

                return;
            }


            /*
             * Verificar página.
             */
            $page =
                $this->pageRepository
                    ->getPageById($pageId);

            if (!$page) {

                $this->json([
                    'status' => 'error',
                    'message' =>
                        'La página de categoría no existe.'
                ], 404);

                return;
            }


            $success =
                $this->pageRepository
                    ->setFavorite(
                        $pageId,
                        $imageId
                    );


            if (!$success) {

                $this->json([
                    'status' => 'error',
                    'message' =>
                        'La imagen no pertenece a esta página.'
                ], 404);

                return;
            }


            $images =
                $this->pageRepository
                    ->getImages($pageId);


            $this->json([
                'status' => 'success',
                'message' =>
                    'Imagen principal actualizada correctamente.',
                'data' => [
                    'images' => $images
                ]
            ]);


        } catch (Throwable $e) {

            error_log(
                '[CATEGORY PAGE FAVORITE] ' .
                $e->getMessage()
            );

            $this->json([
                'status' => 'error',
                'message' =>
                    'No fue posible establecer la imagen principal.'
            ], 500);
        }
    }


    /**
     * POST /api/admin/categories/page/images/delete
     */
    public function deleteImage(): void
    {
        try {

            $pageId =
                (int) (
                    $_POST['page_id']
                    ?? 0
                );

            $imageId =
                (int) (
                    $_POST['image_id']
                    ?? 0
                );


            if (
                $pageId <= 0 ||
                $imageId <= 0
            ) {

                $this->json([
                    'status' => 'error',
                    'message' =>
                        'Página o imagen inválida.'
                ], 422);

                return;
            }


            /*
             * Buscar imagen.
             */
            $images =
                $this->pageRepository
                    ->getImages($pageId);


            $image = null;

            foreach (
                $images
                as $currentImage
            ) {

                if (
                    (int) $currentImage['id']
                    === $imageId
                ) {

                    $image =
                        $currentImage;

                    break;
                }
            }


            if (!$image) {

                $this->json([
                    'status' => 'error',
                    'message' =>
                        'La imagen no pertenece a esta página.'
                ], 404);

                return;
            }


            /*
             * Eliminar registro.
             */
            $deleted =
                $this->pageRepository
                    ->deleteImage(
                        $imageId
                    );


            if (!$deleted) {

                $this->json([
                    'status' => 'error',
                    'message' =>
                        'No fue posible eliminar la imagen.'
                ], 500);

                return;
            }


            /*
             * Eliminar archivo físico.
             */
            $relativePath =
                ltrim(
                    (string) $deleted['image_path'],
                    '/\\'
                );


            $filePath =
                __DIR__ .
                '/../../public/' .
                $relativePath;


            if (
                is_file($filePath)
            ) {

                @unlink($filePath);
            }


            /*
             * Obtener imágenes restantes.
             */
            $remainingImages =
                $this->pageRepository
                    ->getImages($pageId);


            /*
             * Si no queda ninguna imagen favorita,
             * hacer favorita la primera.
             */
            $hasFavorite = false;

            foreach (
                $remainingImages
                as $remainingImage
            ) {

                if (
                    (int) $remainingImage['is_favorite']
                    === 1
                ) {

                    $hasFavorite = true;

                    break;
                }
            }


            if (
                !$hasFavorite &&
                !empty($remainingImages)
            ) {

                $this->pageRepository
                    ->setFavorite(
                        $pageId,
                        (int) $remainingImages[0]['id']
                    );

                $remainingImages =
                    $this->pageRepository
                        ->getImages($pageId);
            }


            $this->json([
                'status' => 'success',
                'message' =>
                    'Imagen eliminada correctamente.',
                'data' => [
                    'images' =>
                        $remainingImages
                ]
            ]);


        } catch (Throwable $e) {

            error_log(
                '[CATEGORY PAGE DELETE IMAGE] ' .
                $e->getMessage()
            );

            $this->json([
                'status' => 'error',
                'message' =>
                    'No fue posible eliminar la imagen.'
            ], 500);
        }
    }


    /**
     * POST /api/admin/categories/page/images/order
     */
    public function updateImageOrder(): void
    {
        try {

            $pageId =
                (int) (
                    $_POST['page_id']
                    ?? 0
                );


            if ($pageId <= 0) {

                $this->json([
                    'status' => 'error',
                    'message' =>
                        'Página inválida.'
                ], 422);

                return;
            }


            /*
             * El JS envía:
             *
             * order[]=5
             * order[]=3
             * order[]=8
             */
            $order =
                $_POST['order']
                ?? [];


            if (
                !is_array($order) ||
                empty($order)
            ) {

                $this->json([
                    'status' => 'error',
                    'message' =>
                        'No se recibió el orden de las imágenes.'
                ], 422);

                return;
            }


            /*
             * Verificar que la página exista.
             */
            $page =
                $this->pageRepository
                    ->getPageById($pageId);

            if (!$page) {

                $this->json([
                    'status' => 'error',
                    'message' =>
                        'La página de categoría no existe.'
                ], 404);

                return;
            }


            /*
             * Actualizar posición.
             */
            foreach (
                $order
                as $position => $imageId
            ) {

                $imageId =
                    (int) $imageId;


                if (
                    $imageId <= 0
                ) {

                    continue;
                }


                $this->pageRepository
                    ->updateOrder(
                        $imageId,
                        (int) $position
                    );
            }


            $images =
                $this->pageRepository
                    ->getImages($pageId);


            $this->json([
                'status' => 'success',
                'message' =>
                    'Orden de imágenes actualizado correctamente.',
                'data' => [
                    'images' => $images
                ]
            ]);


        } catch (Throwable $e) {

            error_log(
                '[CATEGORY PAGE IMAGE ORDER] ' .
                $e->getMessage()
            );

            $this->json([
                'status' => 'error',
                'message' =>
                    'No fue posible actualizar el orden.'
            ], 500);
        }
    }

    /*
    |--------------------------------------------------------------------------
    | MÉTODOS AUXILIARES
    |--------------------------------------------------------------------------
    */

    private function getJsonInput(): array
    {
        $raw =
            file_get_contents(
                'php://input'
            );

        if (!$raw) {
            return [];
        }

        $data =
            json_decode(
                $raw,
                true
            );

        return is_array($data)
            ? $data
            : [];
    }

    private function getRequestData(): array
    {
        $contentType =
            $_SERVER['CONTENT_TYPE']
            ?? '';

        if (
            str_contains(
                strtolower($contentType),
                'application/json'
            )
        ) {
            return $this->getJsonInput();
        }

        return $_POST;
    }

    /**
     * GET /menu/{slug}
     *
     * Ejemplos:
     *
     * /menu/hamburguesas-bogota
     * /menu/perros-calientes-bogota
     * /menu/alitas-bogota
     */
    public function publicPage(
        string $slug
    ): void {

        $slug =
            trim(
                $slug
            );


        if (
            $slug === ''
        ) {

            http_response_code(
                404
            );

            echo 'Página no encontrada.';

            return;
        }


        /*
         * =====================================================
         * PÁGINA
         * =====================================================
         */

        $page =
            $this->pageRepository
                ->getPublicPageBySlug(
                    $slug
                );


        if (
            !$page
        ) {

            http_response_code(
                404
            );

            echo 'Página no encontrada.';

            return;
        }


        /*
         * =====================================================
         * ID DE CATEGORÍA
         * =====================================================
         */

        $categoryId =
            (int) (
                $page['category_id']
                ?? 0
            );


        if (
            $categoryId <= 0
        ) {

            http_response_code(
                404
            );

            echo 'La página no tiene una categoría válida.';

            return;
        }


        /*
         * =====================================================
         * IMÁGENES DE LA PÁGINA
         * =====================================================
         */

        $images =
            $this->pageRepository
                ->getImages(
                    (int) $page['id']
                );


        /*
         * =====================================================
         * PRODUCTOS
         * =====================================================
         */

        $products =
            $this->pageRepository
                ->getPublicProductsByCategory(
                    $categoryId
                );

        /*
         * =====================================================
         * OPCIONES DE PRECIO
         * =====================================================
         */

        $productIds = [];

        foreach (
            $products as $product
        ) {

            $productIds[] =
                (int) (
                    $product['id']
                    ?? 0
                );
        }


        $priceOptionsByItem =
            $this->pageRepository
                ->getPublicPriceOptionsByItemIds(
                    $productIds
                );


        /*
         * Agregar opciones directamente
         * a cada producto.
         */
        foreach (
            $products as &$product
        ) {

            $productId =
                (int) (
                    $product['id']
                    ?? 0
                );


            $product['price_options'] =
                $priceOptionsByItem[
                $productId
                ]
                ?? [];
        }

        unset(
            $product
        );

        /*
         * =====================================================
         * REGLAS DE CATEGORÍA
         * =====================================================
         */

        $rules =
            $this->pageRepository
                ->getPublicCategoryRules(
                    $categoryId
                );


        /*
         * =====================================================
         * PRODUCTOS DESTACADOS
         * =====================================================
         *
         * Los que tengan is_featured = 1.
         */

        $featuredProducts = [];

        $regularProducts = [];


        foreach (
            $products
            as $product
        ) {

            if (
                (int) (
                    $product['is_featured']
                    ?? 0
                ) === 1
            ) {

                $featuredProducts[] =
                    $product;

            } else {

                $regularProducts[] =
                    $product;
            }


        }


        /*
         * =====================================================
         * RENDER
         * =====================================================
         */

        $this->view(
            'layouts/module_public_layout',
            [

                'title' =>
                    (
                        $page['meta_title']
                        ?? ''
                    )
                        ?: (
                            $page['title']
                            ?? ''
                        )
                        . ' | Burger Town',

                'contentView' =>
                    __DIR__ .
                    '/../Views/menu/category-page.php',

                'categoryPublicPage' =>
                    $page,

                'categoryPublicImages' =>
                    $images,

                'categoryPublicProducts' =>
                    $products,

                'categoryPublicFeaturedProducts' =>
                    $featuredProducts,

                'categoryPublicRegularProducts' =>
                    $regularProducts,

                'categoryPublicRules' =>
                    $rules
            ]
        );
    }
}