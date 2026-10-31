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
        $this->view(
            'layouts/module_public_layout',
            [
                'title' =>
                    'Menú | Burger Town',

                'contentView' =>
                    __DIR__ .
                    '/../Views/menu/index.php'
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