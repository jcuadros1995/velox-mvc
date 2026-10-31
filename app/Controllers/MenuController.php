<?php

/**
 * app/Controllers/MenuController.php
 */

class MenuController extends Controller
{
    /**
     * GET /menu
     */
    public function index(): void
    {
        $pagesRepository = new CategoryPageRepository();
        $publishedPages = $pagesRepository->getPublishedMenuPages();

        $burgerPage = null;
        $menuCategories = [];

        foreach ($publishedPages as $page) {
            $categoryName = strtolower(
                trim((string) ($page['category_name'] ?? ''))
            );
            $categorySlug = strtolower((string) ($page['category_slug'] ?? ''));
            $pageSlug = strtolower((string) ($page['page_slug'] ?? ''));

            $isBurgerPage =
                str_contains($categoryName, 'hamburgues') ||
                str_contains($categorySlug, 'hamburgues') ||
                str_contains($pageSlug, 'hamburgues');

            if ($isBurgerPage && $burgerPage === null) {
                $burgerPage = $page;
                continue;
            }

            $menuCategories[] = $page;
        }

        $menuPdfPath = dirname(__DIR__, 2) .
            '/public/assets/docs/menu-burguer-town.pdf';

        $this->view(
            'layouts/module_public_layout',
            [
                'title' => 'Menú | ' . SiteConfig::siteName(),
                'contentView' => __DIR__ . '/../Views/menu/index.php',
                'menuCategories' => $menuCategories,
                'menuBurgerPage' => $burgerPage,
                'menuPdfAvailable' => is_file($menuPdfPath),
                'script' => 'menu-index'
            ]
        );
    }


    /**
     * GET /pages/hamburguesas
     * GET /menu/hamburguesas
     */
    public function hamburguesas(): void
    {
        /*
         * Si existe una página dinámica publicada para hamburguesas,
         * esta tiene prioridad porque usa productos, precios y opciones
         * reales del catálogo administrado.
         */
        $publishedPages = (new CategoryPageRepository())->getPublishedMenuPages();

        foreach ($publishedPages as $page) {
            $categoryName = strtolower(trim((string) ($page['category_name'] ?? '')));
            $categorySlug = strtolower(trim((string) ($page['category_slug'] ?? '')));
            $pageSlug = trim((string) ($page['page_slug'] ?? ''));

            $isBurgerPage =
                str_contains($categoryName, 'hamburgues') ||
                str_contains($categorySlug, 'hamburgues') ||
                str_contains(strtolower($pageSlug), 'hamburgues');

            if ($isBurgerPage && $pageSlug !== '') {
                (new CategoryController())->publicPage($pageSlug);
                return;
            }
        }

        // Página editorial original de BurgerTown como alternativa
        // cuando todavía no se ha publicado la categoría dinámica.
        $this->view(
            'layouts/module_public_layout',
            [
                'title' => 'Hamburguesas Bogotá | ' . SiteConfig::siteName(),
                'contentView' => __DIR__ . '/../Views/menu/hamburguesas.php'
            ]
        );
    }
}