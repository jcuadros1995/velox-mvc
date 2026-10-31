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
            $categoryName = mb_strtolower(
                trim((string) ($page['category_name'] ?? '')),
                'UTF-8'
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
        $this->view(
            'layouts/module_public_layout',
            [
                'title' =>
                    'Hamburguesas Bogotá | Burger Town',

                'contentView' =>
                    __DIR__ .
                    '/../Views/menu/hamburguesas.php'
            ]
        );
    }
}