<?php
// config/config.php

date_default_timezone_set('America/Bogota');

// Detecta automáticamente si el proyecto está en subcarpeta o en el root
$protocol = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') ? "https://" : "http://";
$host = $_SERVER['HTTP_HOST'];
$scriptName = str_replace('\\', '/', dirname($_SERVER['SCRIPT_NAME']));
$baseUrl = rtrim($protocol . $host . ($scriptName === '/' ? '' : $scriptName), '/');

// Remover '/public' si la URL apunta a public
$baseUrl = preg_replace('/\\/public$/', '', $baseUrl);

define('APP_URL', $baseUrl);
