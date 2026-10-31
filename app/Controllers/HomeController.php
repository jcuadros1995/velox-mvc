<?php
// app/Controllers/HomeController.php

class HomeController extends Controller {
    public function index(): void {
        $this->view('layouts/public_layout', [
            'contentView' => __DIR__ . '/../Views/home/index.php',
            'script'      => 'home'
        ]);
    }
}