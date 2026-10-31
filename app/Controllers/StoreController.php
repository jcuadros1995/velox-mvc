<?php

class StoreController extends Controller
{
    private CategoryPageRepository $catalogRepository;

    public function __construct()
    {
        $this->catalogRepository = new CategoryPageRepository();
    }

    /**
     * GET /tienda
     *
     * Catálogo general de productos disponibles para pedidos públicos.
     */
    public function index(): void
    {
        $categories = [];
        $products = [];
        $catalogError = '';

        try {
            $categories = $this->catalogRepository->getPublicCatalogCategories();
            $products = $this->catalogRepository->getPublicCatalogProducts();

            $productIds = array_values(array_unique(array_filter(
                array_map(
                    static fn (array $product): int => (int) ($product['id'] ?? 0),
                    $products
                ),
                static fn (int $id): bool => $id > 0
            )));

            $priceOptionsByItem = $this->catalogRepository
                ->getPublicPriceOptionsByItemIds($productIds);

            foreach ($products as &$product) {
                $productId = (int) ($product['id'] ?? 0);
                $product['price_options'] = $priceOptionsByItem[$productId] ?? [];
            }
            unset($product);
        } catch (Throwable $e) {
            error_log('[PUBLIC STORE CATALOG] ' . $e->getMessage());
            $catalogError = 'No fue posible cargar el catálogo en este momento. Intenta nuevamente más tarde.';
            $categories = [];
            $products = [];
        }

        $this->view('layouts/module_public_layout', [
            'title' => 'Tienda | ' . SiteConfig::siteName(),
            'contentView' => __DIR__ . '/../Views/store/products.php',
            'storeCategories' => $categories,
            'storeProducts' => $products,
            'storeSelectedCategory' => max(0, (int) ($_GET['categoria'] ?? 0)),
            'storeCatalogError' => $catalogError,
            'script' => 'store-products'
        ]);
    }
}
