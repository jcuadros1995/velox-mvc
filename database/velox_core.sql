-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Servidor: 127.0.0.1
-- Tiempo de generación: 16-10-2026 a las 00:56:42
-- Versión del servidor: 10.4.32-MariaDB
-- Versión de PHP: 8.2.12

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Base de datos: `velox_core`
--

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `attributes`
--

CREATE TABLE `attributes` (
  `id` int(10) UNSIGNED NOT NULL,
  `name` varchar(50) NOT NULL,
  `type` enum('text','number','select','boolean') DEFAULT 'text'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Volcado de datos para la tabla `attributes`
--

INSERT INTO `attributes` (`id`, `name`, `type`) VALUES
(1, 'Color de Portada', 'select'),
(2, 'Número de Páginas', 'number'),
(3, 'Incluye Stickers', 'boolean');

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `auth_logs`
--

CREATE TABLE `auth_logs` (
  `id` int(10) UNSIGNED NOT NULL,
  `user_id` int(10) UNSIGNED DEFAULT NULL,
  `ip_address` varchar(45) NOT NULL,
  `user_agent` varchar(255) DEFAULT NULL,
  `event` enum('login_success','login_failed','logout','password_reset') NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Volcado de datos para la tabla `auth_logs`
--

INSERT INTO `auth_logs` (`id`, `user_id`, `ip_address`, `user_agent`, `event`, `created_at`) VALUES
(1, 5, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/154.0.0.0 Safari/537.36', 'password_reset', '2026-10-04 22:36:05'),
(2, 5, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/154.0.0.0 Safari/537.36', 'password_reset', '2026-10-04 23:11:20');

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `auth_security`
--

CREATE TABLE `auth_security` (
  `user_id` int(10) UNSIGNED NOT NULL,
  `failed_attempts` int(10) UNSIGNED NOT NULL DEFAULT 0,
  `locked_until` datetime DEFAULT NULL,
  `session_version` int(10) UNSIGNED NOT NULL DEFAULT 1,
  `active_session_token` char(64) DEFAULT NULL,
  `last_login_at` datetime DEFAULT NULL,
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Volcado de datos para la tabla `auth_security`
--

INSERT INTO `auth_security` (`user_id`, `failed_attempts`, `locked_until`, `session_version`, `active_session_token`, `last_login_at`, `updated_at`) VALUES
(2, 0, NULL, 11, 'a9954dba875618028e8bd8a98e64d5ac0af56c63fbacad3f5a66ec61d5c4bae5', '2026-10-15 17:52:11', '2026-10-15 22:52:11'),
(5, 0, NULL, 27, NULL, '2026-10-12 15:24:45', '2026-10-12 22:14:20'),
(7, 0, NULL, 3, NULL, '2026-10-15 17:51:25', '2026-10-15 22:51:57');

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `bookings`
--

CREATE TABLE `bookings` (
  `id` int(10) UNSIGNED NOT NULL,
  `branch_id` int(10) UNSIGNED NOT NULL,
  `customer_id` int(10) UNSIGNED NOT NULL,
  `staff_id` int(10) UNSIGNED NOT NULL,
  `item_id` int(10) UNSIGNED NOT NULL,
  `booking_date` date NOT NULL,
  `start_time` time NOT NULL,
  `end_time` time NOT NULL,
  `status` enum('pending','confirmed','completed','cancelled','no_show') DEFAULT 'pending',
  `notes` text DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Volcado de datos para la tabla `bookings`
--

INSERT INTO `bookings` (`id`, `branch_id`, `customer_id`, `staff_id`, `item_id`, `booking_date`, `start_time`, `end_time`, `status`, `notes`, `created_at`) VALUES
(1, 1, 4, 3, 1, '2026-09-07', '10:00:00', '10:45:00', 'confirmed', 'Cliente prefiere perfilado de barba con navaja', '2026-09-07 15:02:04');

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `branches`
--

CREATE TABLE `branches` (
  `id` int(10) UNSIGNED NOT NULL,
  `code` varchar(20) NOT NULL,
  `name` varchar(100) NOT NULL,
  `phone` varchar(30) DEFAULT NULL,
  `email` varchar(100) DEFAULT NULL,
  `address` varchar(255) DEFAULT NULL,
  `is_active` tinyint(1) NOT NULL DEFAULT 1,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Volcado de datos para la tabla `branches`
--

INSERT INTO `branches` (`id`, `code`, `name`, `phone`, `email`, `address`, `is_active`, `created_at`, `updated_at`) VALUES
(1, 'CALLE99', 'Burger Town Calle 99', '+57 310 7163415', 'contacto@burgertown.com.co', 'AV CALLE 99 #12 13, Bogotá, Colombia', 1, '2026-09-07 15:02:04', '2026-09-27 12:26:59'),
(2, 'CALLE26', 'Burger Town Calle 26', '+57 310 7163415', 'contacto@burgertown.com.co', 'AV CALLE 26 #69 63, Fontibón, Bogotá, Cundinamarca', 1, '2026-09-07 15:02:04', '2026-09-27 12:26:44'),
(3, 'CEDRITOS', 'Burger Town Cedritos', '+57 310 7163415', 'contacto@burgertown.com.co', 'Av Calle 134 # 9-68 Bogotá, Colombia', 1, '2026-09-27 12:25:38', '2026-09-27 12:28:11'),
(4, 'CENTROINTERNACIONAL', 'Burger Town Centro Internacional', '+57 310 7163415', 'contacto@burgertown.com.co', 'Torres Tequendama, Cra. 10 #27 61 Local 11, Bogotá, Colombia', 1, '2026-09-27 12:42:28', '2026-09-27 12:42:28'),
(5, 'QUINTACAMACHO', 'Burger Town Quinta Camacho', '+57 310 7163415', 'contacto@burgertown.com.co', 'Cra. 9 #70-29, Bogotá, Colombia', 1, '2026-09-27 12:51:37', '2026-09-27 12:51:37'),
(6, 'AGUASCENTRO', 'Burger Town Las Aguas Centro', '+573107163415', 'contacto@burgertown.com.co', 'Zaragoza (Jiménez) #4-56, Local 101, Bogotá, Colombia', 1, '2026-09-27 12:54:17', '2026-09-27 12:54:17'),
(7, 'CCELEDEN', 'Burger Town C.C El Edén', '+573107163415', 'jcuadros@sanmateo.edu.co', 'Cra. 72 #12b-18 Local 3066, Bogotá, Cundinamarca', 1, '2026-09-27 12:59:09', '2026-10-12 22:10:23');

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `branch_business_hours`
--

CREATE TABLE `branch_business_hours` (
  `id` int(10) UNSIGNED NOT NULL,
  `branch_id` int(10) UNSIGNED NOT NULL,
  `day_of_week` tinyint(3) UNSIGNED NOT NULL COMMENT '1=Lunes, 2=Martes, ..., 7=Domingo',
  `open_time` time DEFAULT NULL,
  `close_time` time DEFAULT NULL,
  `is_closed` tinyint(1) NOT NULL DEFAULT 0,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Volcado de datos para la tabla `branch_business_hours`
--

INSERT INTO `branch_business_hours` (`id`, `branch_id`, `day_of_week`, `open_time`, `close_time`, `is_closed`, `created_at`, `updated_at`) VALUES
(232, 2, 1, '12:00:00', '21:00:00', 0, '2026-10-05 01:53:19', '2026-10-05 01:53:19'),
(233, 2, 2, '12:00:00', '21:00:00', 0, '2026-10-05 01:53:19', '2026-10-05 01:53:19'),
(234, 2, 3, '12:00:00', '21:00:00', 0, '2026-10-05 01:53:19', '2026-10-05 01:53:19'),
(235, 2, 4, '12:00:00', '21:00:00', 0, '2026-10-05 01:53:19', '2026-10-05 01:53:19'),
(236, 2, 5, '12:00:00', '22:00:00', 0, '2026-10-05 01:53:19', '2026-10-05 01:53:19'),
(237, 2, 6, '12:00:00', '21:00:00', 0, '2026-10-05 01:53:19', '2026-10-05 01:53:19'),
(238, 2, 7, '12:00:00', '21:00:00', 0, '2026-10-05 01:53:19', '2026-10-05 01:53:19'),
(239, 1, 1, '12:00:00', '21:00:00', 0, '2026-10-05 01:53:55', '2026-10-05 01:53:55'),
(240, 1, 2, '12:00:00', '21:00:00', 0, '2026-10-05 01:53:55', '2026-10-05 01:53:55'),
(241, 1, 3, '12:00:00', '21:00:00', 0, '2026-10-05 01:53:55', '2026-10-05 01:53:55'),
(242, 1, 4, '12:00:00', '21:00:00', 0, '2026-10-05 01:53:55', '2026-10-05 01:53:55'),
(243, 1, 5, '12:00:00', '22:00:00', 0, '2026-10-05 01:53:55', '2026-10-05 01:53:55'),
(244, 1, 6, '12:00:00', '21:00:00', 0, '2026-10-05 01:53:55', '2026-10-05 01:53:55'),
(245, 1, 7, '12:00:00', '21:00:00', 0, '2026-10-05 01:53:55', '2026-10-05 01:53:55'),
(246, 3, 1, '12:00:00', '21:00:00', 0, '2026-10-05 01:54:40', '2026-10-05 01:54:40'),
(247, 3, 2, '12:00:00', '21:00:00', 0, '2026-10-05 01:54:40', '2026-10-05 01:54:40'),
(248, 3, 3, '12:00:00', '21:00:00', 0, '2026-10-05 01:54:40', '2026-10-05 01:54:40'),
(249, 3, 4, '12:00:00', '21:00:00', 0, '2026-10-05 01:54:40', '2026-10-05 01:54:40'),
(250, 3, 5, '12:00:00', '21:00:00', 0, '2026-10-05 01:54:40', '2026-10-05 01:54:40'),
(251, 3, 6, '12:00:00', '21:00:00', 0, '2026-10-05 01:54:40', '2026-10-05 01:54:40'),
(252, 3, 7, '12:00:00', '21:00:00', 0, '2026-10-05 01:54:40', '2026-10-05 01:54:40'),
(253, 4, 1, '12:00:00', '21:00:00', 0, '2026-10-05 01:55:22', '2026-10-05 01:55:22'),
(254, 4, 2, '12:00:00', '21:00:00', 0, '2026-10-05 01:55:22', '2026-10-05 01:55:22'),
(255, 4, 3, '12:00:00', '21:00:00', 0, '2026-10-05 01:55:22', '2026-10-05 01:55:22'),
(256, 4, 4, '12:00:00', '21:00:00', 0, '2026-10-05 01:55:22', '2026-10-05 01:55:22'),
(257, 4, 5, '12:00:00', '22:00:00', 0, '2026-10-05 01:55:22', '2026-10-05 01:55:22'),
(258, 4, 6, '12:00:00', '21:00:00', 0, '2026-10-05 01:55:22', '2026-10-05 01:55:22'),
(259, 4, 7, '12:00:00', '21:00:00', 0, '2026-10-05 01:55:22', '2026-10-05 01:55:22'),
(260, 6, 1, '12:00:00', '21:00:00', 0, '2026-10-05 01:55:56', '2026-10-05 01:55:56'),
(261, 6, 2, '12:00:00', '21:00:00', 0, '2026-10-05 01:55:56', '2026-10-05 01:55:56'),
(262, 6, 3, '12:00:00', '21:00:00', 0, '2026-10-05 01:55:56', '2026-10-05 01:55:56'),
(263, 6, 4, '12:00:00', '21:00:00', 0, '2026-10-05 01:55:56', '2026-10-05 01:55:56'),
(264, 6, 5, '12:00:00', '22:00:00', 0, '2026-10-05 01:55:56', '2026-10-05 01:55:56'),
(265, 6, 6, '12:00:00', '21:00:00', 0, '2026-10-05 01:55:56', '2026-10-05 01:55:56'),
(266, 6, 7, '12:00:00', '21:00:00', 0, '2026-10-05 01:55:56', '2026-10-05 01:55:56'),
(267, 5, 1, '12:00:00', '21:00:00', 0, '2026-10-05 01:56:20', '2026-10-05 01:56:20'),
(268, 5, 2, '12:00:00', '21:00:00', 0, '2026-10-05 01:56:20', '2026-10-05 01:56:20'),
(269, 5, 3, '12:00:00', '21:00:00', 0, '2026-10-05 01:56:20', '2026-10-05 01:56:20'),
(270, 5, 4, '12:00:00', '21:00:00', 0, '2026-10-05 01:56:20', '2026-10-05 01:56:20'),
(271, 5, 5, '12:00:00', '22:00:00', 0, '2026-10-05 01:56:20', '2026-10-05 01:56:20'),
(272, 5, 6, '12:00:00', '21:00:00', 0, '2026-10-05 01:56:20', '2026-10-05 01:56:20'),
(273, 5, 7, '12:00:00', '21:00:00', 0, '2026-10-05 01:56:20', '2026-10-05 01:56:20'),
(323, 7, 1, '12:00:00', '21:00:00', 0, '2026-10-12 22:10:23', '2026-10-12 22:10:23'),
(324, 7, 2, '12:00:00', '21:00:00', 0, '2026-10-12 22:10:23', '2026-10-12 22:10:23'),
(325, 7, 3, '12:00:00', '21:00:00', 0, '2026-10-12 22:10:23', '2026-10-12 22:10:23'),
(326, 7, 4, '12:00:00', '21:00:00', 0, '2026-10-12 22:10:23', '2026-10-12 22:10:23'),
(327, 7, 5, '12:00:00', '22:00:00', 0, '2026-10-12 22:10:23', '2026-10-12 22:10:23'),
(328, 7, 6, '12:00:00', '21:00:00', 0, '2026-10-12 22:10:23', '2026-10-12 22:10:23'),
(329, 7, 7, '12:00:00', '21:00:00', 0, '2026-10-12 22:10:23', '2026-10-12 22:10:23');

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `branch_closures`
--

CREATE TABLE `branch_closures` (
  `id` int(10) UNSIGNED NOT NULL,
  `branch_id` int(10) UNSIGNED NOT NULL,
  `closure_date` date NOT NULL,
  `reason` varchar(255) DEFAULT NULL,
  `is_full_day` tinyint(1) NOT NULL DEFAULT 1,
  `start_time` time DEFAULT NULL,
  `end_time` time DEFAULT NULL,
  `is_active` tinyint(1) NOT NULL DEFAULT 1,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `branch_images`
--

CREATE TABLE `branch_images` (
  `id` int(10) UNSIGNED NOT NULL,
  `branch_id` int(10) UNSIGNED NOT NULL,
  `file_path` varchar(500) NOT NULL,
  `original_name` varchar(255) DEFAULT NULL,
  `mime_type` varchar(50) NOT NULL,
  `file_size` int(10) UNSIGNED NOT NULL DEFAULT 0,
  `width` int(10) UNSIGNED DEFAULT NULL,
  `height` int(10) UNSIGNED DEFAULT NULL,
  `sort_order` int(10) UNSIGNED NOT NULL DEFAULT 0,
  `is_favorite` tinyint(1) NOT NULL DEFAULT 0,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Volcado de datos para la tabla `branch_images`
--

INSERT INTO `branch_images` (`id`, `branch_id`, `file_path`, `original_name`, `mime_type`, `file_size`, `width`, `height`, `sort_order`, `is_favorite`, `created_at`, `updated_at`) VALUES
(18, 7, 'uploads/branches/7/branch-7-59960f11ff791386532d29ba.webp', 'sede-eden-ok.webp', 'image/webp', 93074, 1000, 1000, 0, 1, '2026-10-05 01:51:52', '2026-10-05 01:51:52'),
(19, 2, 'uploads/branches/2/branch-2-605b1c8c4a918a35e4888c7f.webp', 'sede-calle26-ok.webp', 'image/webp', 141786, 1000, 1000, 0, 1, '2026-10-05 01:53:19', '2026-10-05 01:53:19'),
(20, 1, 'uploads/branches/1/branch-1-43c775358a995c0253e8d580.webp', 'sede-calle99-ok.webp', 'image/webp', 133040, 1000, 1000, 0, 1, '2026-10-05 01:53:55', '2026-10-05 01:53:55'),
(21, 3, 'uploads/branches/3/branch-3-e65f86f38918ea278e91878b.webp', 'sede-calle134-ok.webp', 'image/webp', 133500, 1000, 1000, 0, 1, '2026-10-05 01:54:40', '2026-10-05 01:54:40'),
(22, 4, 'uploads/branches/4/branch-4-a14e429d9e8ef805f8413a46.webp', 'sede-centrointernacional-ok.webp', 'image/webp', 153892, 1000, 1000, 0, 1, '2026-10-05 01:55:22', '2026-10-05 01:55:22'),
(23, 6, 'uploads/branches/6/branch-6-515771552d4334adad75c4ee.webp', 'sede-lasaguas-ok.webp', 'image/webp', 86280, 1000, 1000, 0, 1, '2026-10-05 01:55:56', '2026-10-05 01:55:56'),
(24, 5, 'uploads/branches/5/branch-5-f25fd58724e1100f328ec763.webp', 'sede-quintacamacho-ok.webp', 'image/webp', 130794, 1000, 1000, 0, 1, '2026-10-05 01:56:20', '2026-10-05 01:56:20'),
(25, 7, 'uploads/branches/7/branch-7-f0b3ad406d1c34f78305ae85.webp', 'sede-calle26-ok.webp', 'image/webp', 141786, 1000, 1000, 1, 0, '2026-10-09 20:40:14', '2026-10-09 20:40:14');

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `branch_users`
--

CREATE TABLE `branch_users` (
  `id` int(10) UNSIGNED NOT NULL,
  `branch_id` int(10) UNSIGNED NOT NULL,
  `user_id` int(10) UNSIGNED NOT NULL,
  `is_primary` tinyint(1) NOT NULL DEFAULT 0,
  `is_active` tinyint(1) NOT NULL DEFAULT 1,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Volcado de datos para la tabla `branch_users`
--

INSERT INTO `branch_users` (`id`, `branch_id`, `user_id`, `is_primary`, `is_active`, `created_at`) VALUES
(1, 1, 1, 1, 1, '2026-09-07 20:17:57'),
(2, 1, 2, 1, 1, '2026-09-07 20:17:57'),
(3, 1, 3, 1, 1, '2026-09-07 20:17:57'),
(4, 1, 4, 0, 1, '2026-09-07 20:17:57'),
(5, 2, 1, 0, 1, '2026-09-07 20:17:57');

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `categories`
--

CREATE TABLE `categories` (
  `id` int(10) UNSIGNED NOT NULL,
  `parent_id` int(10) UNSIGNED DEFAULT NULL,
  `name` varchar(100) NOT NULL,
  `slug` varchar(120) NOT NULL,
  `catalog_type` enum('general','restaurant') NOT NULL DEFAULT 'general',
  `description` text DEFAULT NULL,
  `is_active` tinyint(1) NOT NULL DEFAULT 1,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Volcado de datos para la tabla `categories`
--

INSERT INTO `categories` (`id`, `parent_id`, `name`, `slug`, `catalog_type`, `description`, `is_active`, `created_at`) VALUES
(4, NULL, 'Hamburguesas', 'hamburguesas', 'restaurant', 'Hamburguesas de Burger Town', 1, '2026-09-28 12:45:43'),
(5, 4, 'De nuestra creación', 'de-nuestra-creacion', 'general', 'Hamburguesas de nuestra creación', 1, '2026-09-28 12:45:44'),
(6, 4, 'Burgers clásicas', 'burgers-clasicas', 'general', 'Hamburguesas clásicas de Burger Town', 1, '2026-09-28 12:52:05'),
(7, NULL, 'Dogs', 'dogs', 'restaurant', 'Hot dogs de Burger Town', 1, '2026-09-28 12:52:05'),
(8, NULL, 'Wings', 'wings', 'general', 'Alitas de pollo', 1, '2026-09-28 12:52:05'),
(9, NULL, 'Entradas', 'entradas', 'general', 'Entradas y acompañamientos', 1, '2026-09-28 12:52:05'),
(10, NULL, 'Menú infantil', 'menu-infantil', 'general', 'Productos del menú infantil', 1, '2026-09-28 12:52:05'),
(11, NULL, 'Steaks', 'steaks', 'general', 'Cortes y preparaciones de carne', 1, '2026-09-28 12:52:05'),
(12, NULL, 'Bebidas sin alcohol', 'bebidas-sin-alcohol', 'general', 'Bebidas sin alcohol', 1, '2026-09-28 12:52:05'),
(13, NULL, 'Cervezas', 'cervezas', 'general', 'Cervezas disponibles en Burger Town', 1, '2026-09-28 12:52:05'),
(14, NULL, 'Cócteles', 'cocteles', 'general', 'Cócteles de Burger Town', 1, '2026-09-28 12:52:05'),
(15, NULL, 'Botellas', 'botellas', 'general', 'Botellas disponibles', 1, '2026-09-28 12:52:05'),
(16, NULL, 'Adiciones', 'adiciones', 'general', 'Ingredientes y productos adicionales', 1, '2026-09-28 12:52:05'),
(17, NULL, 'Alitas', 'alitas', 'restaurant', 'Si buscabas alitas Bogotá, llegaste al lugar correcto. Elige tu sede, mira opciones y reserva tu mesa', 1, '2026-10-04 19:49:03');

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `category_pages`
--

CREATE TABLE `category_pages` (
  `id` int(10) UNSIGNED NOT NULL,
  `category_id` int(10) UNSIGNED NOT NULL,
  `slug` varchar(180) NOT NULL,
  `title` varchar(180) NOT NULL,
  `meta_title` varchar(255) DEFAULT NULL,
  `meta_description` varchar(500) DEFAULT NULL,
  `h1` varchar(255) DEFAULT NULL,
  `intro_text` text DEFAULT NULL,
  `hero_image` varchar(500) DEFAULT NULL,
  `content_top` longtext DEFAULT NULL,
  `content_bottom` longtext DEFAULT NULL,
  `faq_enabled` tinyint(1) NOT NULL DEFAULT 1,
  `is_published` tinyint(1) NOT NULL DEFAULT 1,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Volcado de datos para la tabla `category_pages`
--

INSERT INTO `category_pages` (`id`, `category_id`, `slug`, `title`, `meta_title`, `meta_description`, `h1`, `intro_text`, `hero_image`, `content_top`, `content_bottom`, `faq_enabled`, `is_published`, `created_at`, `updated_at`) VALUES
(1, 4, 'hamburguesas-bogota', 'Hamburguesas', NULL, NULL, NULL, 'Ganadoras Burger Master 2023 y 2024\r\n\r\nSi estás buscando hamburguesas Bogotá, este es el plan: ven a Burger Town, elige tu sede y reserva en minutos.', NULL, NULL, NULL, 1, 1, '2026-10-03 18:57:43', '2026-10-03 21:43:11'),
(2, 7, 'dogs-bogota', 'Perros Calientes', NULL, NULL, NULL, 'Para quienes buscan perros calientes Bogotá: aquí el antojo se viene a la mesa. Mira opciones, elige sede y reserva.', NULL, NULL, NULL, 1, 1, '2026-10-03 20:43:37', '2026-10-03 20:43:37'),
(3, 17, 'alitas-bogota', 'Alitas', NULL, NULL, NULL, 'Si buscabas alitas Bogotá, llegaste al lugar correcto. Elige tu sede, mira opciones y reserva tu mesa', NULL, NULL, NULL, 1, 1, '2026-10-04 19:49:48', '2026-10-04 19:49:48');

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `category_page_images`
--

CREATE TABLE `category_page_images` (
  `id` int(10) UNSIGNED NOT NULL,
  `category_page_id` int(10) UNSIGNED NOT NULL,
  `image_path` varchar(500) NOT NULL,
  `mobile_image_path` varchar(500) DEFAULT NULL,
  `image_alt` varchar(255) DEFAULT NULL,
  `display_order` int(10) UNSIGNED NOT NULL DEFAULT 0,
  `is_favorite` tinyint(1) NOT NULL DEFAULT 0,
  `is_active` tinyint(1) NOT NULL DEFAULT 1,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Volcado de datos para la tabla `category_page_images`
--

INSERT INTO `category_page_images` (`id`, `category_page_id`, `image_path`, `mobile_image_path`, `image_alt`, `display_order`, `is_favorite`, `is_active`, `created_at`, `updated_at`) VALUES
(12, 3, 'uploads/category-pages/3/4PGQWcmH8agFHawJr-2400-x-764a4854bf19f44a.webp', NULL, '4PGQWcmH8agFHawJr-2400-x.webp', 0, 1, 1, '2026-10-05 02:15:13', '2026-10-05 02:15:13'),
(13, 2, 'uploads/category-pages/2/perro-5d409c91b1924e6e.webp', NULL, 'perro.webp', 0, 1, 1, '2026-10-05 02:15:32', '2026-10-05 02:15:32'),
(14, 1, 'uploads/category-pages/1/REQUISICION_WEB_BANNER_2_2-1-a1e271ad11f58636.webp', 'uploads/category-pages/1/mobile-14-ee2fb496efdad681.webp', 'REQUISICION_WEB_BANNER_2_2 (1).webp', 0, 1, 1, '2026-10-05 02:15:51', '2026-10-09 20:21:55');

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `contact_messages`
--

CREATE TABLE `contact_messages` (
  `id` int(10) UNSIGNED NOT NULL,
  `name` varchar(120) NOT NULL,
  `email` varchar(160) NOT NULL,
  `phone` varchar(40) DEFAULT NULL,
  `subject` varchar(180) DEFAULT NULL,
  `message` text NOT NULL,
  `status` enum('new','read','replied','closed') NOT NULL DEFAULT 'new',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `customers`
--

CREATE TABLE `customers` (
  `id` int(10) UNSIGNED NOT NULL,
  `first_name` varchar(80) NOT NULL,
  `last_name` varchar(80) DEFAULT NULL,
  `email` varchar(150) DEFAULT NULL,
  `phone` varchar(30) NOT NULL,
  `document_type` enum('CC','CE','NIT','PP','OTHER') DEFAULT NULL,
  `document_number` varchar(40) DEFAULT NULL,
  `notes` text DEFAULT NULL,
  `is_active` tinyint(1) NOT NULL DEFAULT 1,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Volcado de datos para la tabla `customers`
--

INSERT INTO `customers` (`id`, `first_name`, `last_name`, `email`, `phone`, `document_type`, `document_number`, `notes`, `is_active`, `created_at`, `updated_at`) VALUES
(3, 'Juan', 'Pérez', 'juan.perez@test.com', '3001234567', NULL, NULL, NULL, 1, '2026-09-07 21:50:47', '2026-09-13 19:43:30'),
(4, 'jhonatan', 'cuadros', 'jcuadros@sanmateo.edu.co', '+573209636763', NULL, NULL, NULL, 1, '2026-09-10 20:08:49', '2026-10-12 21:56:56');

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `event_requests`
--

CREATE TABLE `event_requests` (
  `id` int(10) UNSIGNED NOT NULL,
  `branch_id` int(10) UNSIGNED NOT NULL,
  `name` varchar(120) NOT NULL,
  `email` varchar(160) NOT NULL,
  `phone` varchar(40) NOT NULL,
  `event_type` varchar(100) NOT NULL,
  `event_date` date NOT NULL,
  `event_time` time NOT NULL,
  `guests` smallint(5) UNSIGNED NOT NULL,
  `message` text DEFAULT NULL,
  `status` enum('new','contacted','confirmed','cancelled') NOT NULL DEFAULT 'new',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Volcado de datos para la tabla `event_requests`
--

INSERT INTO `event_requests` (`id`, `branch_id`, `name`, `email`, `phone`, `event_type`, `event_date`, `event_time`, `guests`, `message`, `status`, `created_at`, `updated_at`) VALUES
(1, 7, 'jhonatan cuadros', 'jhonatanstivcuadros@gmail.com', '+573209636763', 'Cumpleaños', '2026-10-22', '17:03:00', 10, 'Correo de prueba', 'new', '2026-10-12 22:03:30', '2026-10-12 22:03:30'),
(2, 7, 'jhonatan cuadros', 'jhonatanstivcuadros@gmail.com', '+573209636763', 'Cumpleaños', '2026-10-13', '17:07:00', 10, 'prueba', 'new', '2026-10-12 22:07:21', '2026-10-12 22:07:21'),
(3, 7, 'jhonatan cuadros', 'jhonatanstivcuadros@gmail.com', '+573209636763', 'Evento corporativo', '2026-10-14', '17:11:00', 10, 'prueba', 'new', '2026-10-12 22:12:12', '2026-10-12 22:12:12');

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `inventory`
--

CREATE TABLE `inventory` (
  `id` int(10) UNSIGNED NOT NULL,
  `item_id` int(10) UNSIGNED NOT NULL,
  `branch_id` int(10) UNSIGNED NOT NULL,
  `stock_quantity` int(11) NOT NULL DEFAULT 0,
  `min_stock_alert` int(11) NOT NULL DEFAULT 5,
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Volcado de datos para la tabla `inventory`
--

INSERT INTO `inventory` (`id`, `item_id`, `branch_id`, `stock_quantity`, `min_stock_alert`, `updated_at`) VALUES
(1, 2, 1, 45, 10, '2026-09-07 15:02:04'),
(2, 2, 2, 20, 5, '2026-09-07 15:02:04'),
(3, 3, 1, 60, 10, '2026-09-07 15:02:04');

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `items`
--

CREATE TABLE `items` (
  `id` int(10) UNSIGNED NOT NULL,
  `category_id` int(10) UNSIGNED DEFAULT NULL,
  `type` enum('physical','service','digital','restaurant_dish','parking_spot','subscription') NOT NULL DEFAULT 'physical',
  `sku` varchar(50) NOT NULL,
  `name` varchar(150) NOT NULL,
  `slug` varchar(170) NOT NULL,
  `description` text DEFAULT NULL,
  `ingredients` text DEFAULT NULL,
  `price` decimal(12,2) NOT NULL DEFAULT 0.00,
  `combo_price` decimal(12,2) DEFAULT NULL,
  `portion_size` varchar(100) DEFAULT NULL,
  `spicy_level` tinyint(3) UNSIGNED NOT NULL DEFAULT 0 COMMENT '0 ninguno, 1 suave, 2 medio, 3 alto',
  `allergens` text DEFAULT NULL,
  `preparation_notes` text DEFAULT NULL,
  `cost_price` decimal(12,2) NOT NULL DEFAULT 0.00,
  `tax_rate` decimal(5,2) NOT NULL DEFAULT 0.00 COMMENT 'Porcentaje de IVA u otro impuesto',
  `duration_minutes` int(10) UNSIGNED DEFAULT 0 COMMENT 'Para servicios o citas',
  `is_active` tinyint(1) NOT NULL DEFAULT 1,
  `is_featured` tinyint(1) NOT NULL DEFAULT 0,
  `is_available` tinyint(1) NOT NULL DEFAULT 1,
  `display_order` int(10) UNSIGNED NOT NULL DEFAULT 0,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Volcado de datos para la tabla `items`
--

INSERT INTO `items` (`id`, `category_id`, `type`, `sku`, `name`, `slug`, `description`, `ingredients`, `price`, `combo_price`, `portion_size`, `spicy_level`, `allergens`, `preparation_notes`, `cost_price`, `tax_rate`, `duration_minutes`, `is_active`, `is_featured`, `is_available`, `display_order`, `created_at`, `updated_at`) VALUES
(1, 4, 'restaurant_dish', 'SKU-000014', 'Street Town', 'corte-de-cabello-barba', 'Tocineta ahumada, queso rallado, maiz\ncaramelizado, papas fósforo y salsa.', NULL, 35900.00, 41500.00, NULL, 0, NULL, NULL, 0.00, 0.00, 10, 1, 0, 1, 14, '2026-09-07 15:02:04', '2026-10-03 14:41:23'),
(2, 4, 'restaurant_dish', 'SKU-000007', 'Extra Cheese', 'agenda-atemporal-yousee-lavender', 'Queso asado crocante, queso mozarella, queso\ncheddar, verduras frescas y mayonesa de la casa.', NULL, 36900.00, 42500.00, NULL, 0, NULL, NULL, 0.00, 0.00, 10, 1, 0, 1, 7, '2026-09-07 15:02:04', '2026-10-03 14:42:28'),
(3, 5, 'restaurant_dish', 'SKU-000002', 'Lucky Town', 'agenda-atemporal-yousee-black', 'Reducción de pimentones ahumados en Jameson\nIrish whisky, carne angus, queso philadelphia,\ncrocantes de arracacha, pan pretzel, rúgula,\ntocineta ahumada', NULL, 39500.00, 45500.00, NULL, 0, NULL, NULL, 0.00, 0.00, 10, 1, 1, 1, 2, '2026-09-07 15:02:04', '2026-10-03 14:39:47'),
(4, 5, 'restaurant_dish', 'SKU-000001', 'Sexy Town', 'producto-prueba-1789255321', 'Mermelada de tomate cherry en reducción de\nvino tinto, carne black angus, crujientes de harina,\nqueso asado, tocineta crocante, rúgula fresca, pan\nde papa brioche y mayonesa blanca de la casa.', NULL, 39500.00, 45500.00, NULL, 0, NULL, NULL, 0.00, 0.00, 10, 1, 1, 1, 1, '2026-09-12 23:22:01', '2026-10-03 16:48:04'),
(5, 4, 'restaurant_dish', 'SKU-000009', 'Padrisíma', 'padris-ima-6a4def80', 'Nachos, frijol refrito, queso mozarella y pico de\ngallo bañado en guacamole.', NULL, 38200.00, 44900.00, NULL, 0, NULL, NULL, 0.00, 0.00, 10, 1, 0, 1, 9, '2026-10-03 13:58:08', '2026-10-03 14:43:06'),
(6, 4, 'restaurant_dish', 'SKU-000017', 'Chicken Town', 'chicken-b0ec5165', 'Pollo a la plancha acompañado de queso cheddar,\ntocineta, cebolla crispy, miel mostaza y vegetales\nfrescos.', NULL, 33500.00, 36500.00, NULL, 0, NULL, NULL, 0.00, 0.00, 10, 1, 0, 1, 17, '2026-10-03 14:00:50', '2026-10-03 14:49:27'),
(7, 5, 'restaurant_dish', 'SKU-000003', 'Victory Town', 'victory-town-39b6f9fe', 'Carne angus premium, queso philadelphia cremoso,\nmanzana verde infusionada en ron havana y blueberry\nblush tea, albahaca fresca, sweet potato crocante,\npan de papa brioche, mayonesa de cilantro con\nhierbabuena.', NULL, 39500.00, 45500.00, NULL, 0, NULL, NULL, 0.00, 0.00, 10, 1, 1, 1, 3, '2026-10-03 14:19:49', '2026-10-03 14:19:49'),
(8, 6, 'restaurant_dish', 'SKU-000004', 'Queso Cheddar', 'queso-cheddar-6b8320a1', 'Queso cheddar y verduras frescas', NULL, 32900.00, 38500.00, NULL, 0, NULL, NULL, 0.00, 0.00, 10, 1, 0, 1, 4, '2026-10-03 14:27:29', '2026-10-03 14:27:29'),
(9, 6, 'restaurant_dish', 'SKU-000005', 'Queso Tocineta', 'queso-tocineta-631b5ba8', 'Queso cheddar, tocineta crocante y\nverduras frescas', NULL, 36500.00, 41500.00, NULL, 0, NULL, NULL, 0.00, 0.00, 10, 1, 0, 1, 5, '2026-10-03 14:29:30', '2026-10-03 14:40:11'),
(10, 6, 'restaurant_dish', 'SKU-000006', 'Sencilla', 'sencilla-1108b3e7', 'Carne de res madura 100% y verduras\nfresca', NULL, 27500.00, 33000.00, NULL, 0, NULL, NULL, 0.00, 0.00, 10, 1, 0, 1, 6, '2026-10-03 14:31:49', '2026-10-03 14:31:49'),
(11, 4, 'restaurant_dish', 'SKU-000008', '4X4', '4x4-18a652e4', 'Doble carne madura 100% de res, doble tocineta\ncrocante, doble queso cheddar, verduras frescas.', NULL, 45500.00, 50900.00, NULL, 0, NULL, NULL, 0.00, 0.00, 10, 1, 0, 1, 8, '2026-10-03 14:51:52', '2026-10-03 14:51:52'),
(12, 4, 'restaurant_dish', 'SKU-000010', 'Gaucha', 'gaucha-5a8b21f1', 'Chorizo argentino a la plancha bañado en chimichurri\nde la casa, cebolla roja y queso mozarella.', NULL, 35900.00, 41500.00, NULL, 0, NULL, NULL, 0.00, 0.00, 10, 1, 0, 1, 10, '2026-10-03 14:53:39', '2026-10-03 14:53:39'),
(13, 4, 'restaurant_dish', 'SKU-000011', 'Hawai Town', 'hawai-town-38ffc8e8', 'Piña caramelizada en panela, tocineta crocante\nahumada y queso mozarella.', NULL, 34500.00, 40900.00, NULL, 0, NULL, NULL, 0.00, 0.00, 10, 1, 0, 1, 11, '2026-10-03 14:55:31', '2026-10-03 14:55:31'),
(14, 4, 'restaurant_dish', 'SKU-000012', 'Heinz Town', 'heinz-town-0ec439dc', 'Pepinillos agridulces, cebolla crispy, tocineta\ncrocante, queso philadelphia y salsa de miel mostaza.', NULL, 38000.00, 43500.00, NULL, 0, NULL, NULL, 0.00, 0.00, 10, 1, 0, 1, 12, '2026-10-03 14:58:19', '2026-10-03 14:58:19'),
(15, 4, 'restaurant_dish', 'SKU-000013', 'Napoli Town', 'napolitown-573d3c5a', 'Pepperoni, queso mozarella, tomate, albahaca\nfresca y salsa napolitana.', NULL, 34000.00, 39900.00, NULL, 0, NULL, NULL, 0.00, 0.00, 10, 1, 0, 1, 13, '2026-10-03 15:00:03', '2026-10-03 15:00:37'),
(16, 4, 'restaurant_dish', 'SKU-000015', 'Falafel', 'falafel-6f28e001', 'Carne de garbanzos con especias, pepino,\nrodajas de tomate y queso crema con hierbabuena.', NULL, 30200.00, 36500.00, NULL, 0, NULL, NULL, 0.00, 0.00, 10, 1, 0, 1, 15, '2026-10-03 15:02:22', '2026-10-03 15:02:22'),
(17, 4, 'restaurant_dish', 'SKU-000016', 'Sweet Chick Town', 'sweet-chick-town-61eae926', 'Pollo dorado y crujiente, coleslaw, sweet bbq, queso\ncheddar y mayonesa ahumada en pan de papa brioche.\n\nRECOMENDACIÓN DE LA CASA\nPÍDE LA OPCIÓN PICANTE', NULL, 38000.00, 43500.00, NULL, 0, NULL, NULL, 0.00, 0.00, 10, 1, 0, 1, 16, '2026-10-03 15:05:11', '2026-10-03 15:05:11'),
(18, 7, 'restaurant_dish', 'SKU-000018', 'Heinz Dog', 'heinz-dog-07024d38', 'Salchicha premium, cebolla crispy, queso crema,\ntocineta ahumada, pepinillos agridulces y salsa de\nmiel mostaza de orégano.', NULL, 30000.00, 36000.00, NULL, 0, NULL, NULL, 0.00, 0.00, 10, 1, 0, 1, 18, '2026-10-04 19:26:42', '2026-10-04 19:27:13'),
(19, 7, 'restaurant_dish', 'SKU-000019', 'GAUCHO', 'gaucho-815f8eb5', 'Chorizo argentino premium con chimichurri\nartesanal, cebolla roja y queso mozarella.', NULL, 31900.00, 37500.00, NULL, 0, NULL, NULL, 0.00, 0.00, 10, 1, 0, 1, 19, '2026-10-04 19:29:27', '2026-10-05 01:58:08'),
(20, 7, 'restaurant_dish', 'SKU-000020', 'Street Dog', 'street-dog-188d1be1', 'Salchicha premium, tocineta ahumada, queso rallado,\nmaiz caramelizado, papita crispy y salsa de la casa.', NULL, 29500.00, 35000.00, NULL, 0, NULL, NULL, 0.00, 0.00, 10, 1, 0, 1, 20, '2026-10-04 19:32:00', '2026-10-04 19:32:00'),
(21, 7, 'restaurant_dish', 'SKU-000021', 'Bacon Cheddar', 'bacon-cheddar-ce5b3fc4', 'Salchicha premium con queso cheddar, tocineta\nahumada, papita crispy, cebolla en cubitos y salsas.', NULL, 24500.00, 30900.00, NULL, 0, NULL, NULL, 0.00, 0.00, 10, 1, 0, 1, 21, '2026-10-04 19:33:53', '2026-10-04 19:33:53'),
(22, 7, 'restaurant_dish', 'SKU-000022', 'Hawai', 'hawai-d0500131', 'Salchicha premium, piña caramelizada con panela,\nqueso mozarella, tocineta ahumada, papita crispy,\ncebolla en cubitos y salsas.', NULL, 28500.00, 34500.00, NULL, 0, NULL, NULL, 0.00, 0.00, 10, 1, 0, 1, 22, '2026-10-04 19:34:58', '2026-10-04 19:34:58'),
(23, 7, 'restaurant_dish', 'SKU-000023', 'Sexy Dog', 'sexy-dog-ece95468', 'Salchicha premium, mermelada de tomate cherry\nen reducción de vino tinto, papita crispy, queso\nasado, tocineta crocante, rúgula fresca y mayonesa\nde la casa.', NULL, 32500.00, 38500.00, NULL, 0, NULL, NULL, 0.00, 0.00, 10, 1, 1, 1, 23, '2026-10-04 19:36:36', '2026-10-04 19:36:36'),
(24, 7, 'restaurant_dish', 'SKU-000024', 'Extra Cheese Dog', 'extra-cheese-dog-ea800701', 'Salchicha premium, queso asado crocante, queso\nmozarella, queso cheddar y salsas.', NULL, 30500.00, 36900.00, NULL, 0, NULL, NULL, 0.00, 0.00, 10, 1, 0, 1, 24, '2026-10-04 19:39:12', '2026-10-04 19:39:12'),
(25, 7, 'restaurant_dish', 'SKU-000025', 'American', 'american-24ad86ba', 'Salchicha premium con queso cheddar, papita\ncrispy, cebolla en cubitos y salsas.', NULL, 25500.00, 29900.00, NULL, 0, NULL, NULL, 0.00, 0.00, 10, 1, 0, 1, 25, '2026-10-04 19:43:09', '2026-10-04 19:43:09'),
(26, 17, 'restaurant_dish', 'SKU-000026', 'Sweet BBQ', 'sweet-bbq-bd6ae438', 'Alitas de pollo doradas y crujientes, bañadas en nuestra deliciosa salsa BBQ, con un irresistible toque ahumado y dulce.', NULL, 29900.00, 35900.00, NULL, 0, NULL, NULL, 0.00, 0.00, 15, 1, 1, 1, 26, '2026-10-04 20:04:59', '2026-10-04 20:39:46'),
(27, 17, 'restaurant_dish', 'SKU-000027', 'Miel Mostaza', 'miel-mostaza-729a50d4', 'Alitas de pollo crujientes bañadas en nuestra deliciosa salsa de miel mostaza, dulce, cremosa y llena de sabor.', NULL, 29900.00, 35900.00, NULL, 0, NULL, NULL, 0.00, 0.00, 15, 1, 1, 1, 27, '2026-10-04 20:12:12', '2026-10-04 21:02:38'),
(28, 17, 'restaurant_dish', 'SKU-000028', 'Buffalo', 'buffalo-8fe893ba', 'Alitas crujientes bañadas en nuestra salsa Buffalo, con el toque picante perfecto y un sabor intenso e irresistible.', NULL, 29900.00, 35900.00, NULL, 0, NULL, NULL, 0.00, 0.00, 15, 1, 1, 1, 28, '2026-10-04 20:13:20', '2026-10-04 21:03:26'),
(29, 17, 'restaurant_dish', 'SKU-000029', 'Miel Picante', 'miel-picante-fbfc4fbd', 'Alitas crujientes bañadas en nuestra salsa de miel picante, una deliciosa combinación de dulzor y el picante perfecto.', NULL, 29900.00, 35900.00, NULL, 0, NULL, NULL, 0.00, 0.00, 15, 1, 1, 1, 29, '2026-10-04 20:14:25', '2026-10-04 21:03:55');

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `item_attributes`
--

CREATE TABLE `item_attributes` (
  `item_id` int(10) UNSIGNED NOT NULL,
  `attribute_id` int(10) UNSIGNED NOT NULL,
  `attribute_value` varchar(255) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Volcado de datos para la tabla `item_attributes`
--

INSERT INTO `item_attributes` (`item_id`, `attribute_id`, `attribute_value`) VALUES
(2, 1, 'Morado Lavender'),
(2, 2, '240'),
(2, 3, '1'),
(3, 1, 'Negro Mate'),
(3, 2, '240'),
(3, 3, '1');

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `item_images`
--

CREATE TABLE `item_images` (
  `id` int(10) UNSIGNED NOT NULL,
  `item_id` int(10) UNSIGNED NOT NULL,
  `image_path` varchar(500) NOT NULL,
  `image_alt` varchar(255) DEFAULT NULL,
  `display_order` int(10) UNSIGNED NOT NULL DEFAULT 0,
  `is_favorite` tinyint(1) NOT NULL DEFAULT 0,
  `is_active` tinyint(1) NOT NULL DEFAULT 1,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Volcado de datos para la tabla `item_images`
--

INSERT INTO `item_images` (`id`, `item_id`, `image_path`, `image_alt`, `display_order`, `is_favorite`, `is_active`, `created_at`, `updated_at`) VALUES
(37, 3, 'uploads/items/3/item-3-8e5e78ad42618246a359f043.webp', 'REQUISICION_WEB_BURGER_MASTER_21.webp', 0, 1, 1, '2026-10-05 02:00:00', '2026-10-05 02:00:00'),
(38, 3, 'uploads/items/3/item-3-ff858b5c4d485d91e918b182.webp', 'lucky.webp', 1, 0, 1, '2026-10-05 02:00:00', '2026-10-05 02:00:00'),
(39, 4, 'uploads/items/4/item-4-4e46fb513554404fcaf7ad62.webp', 'REQUISICION_WEB_BURGER_MASTER_22.webp', 0, 1, 1, '2026-10-05 02:00:30', '2026-10-05 02:00:30'),
(40, 4, 'uploads/items/4/item-4-e874e01e7220f1db35dd9eb8.webp', 'sexy_town.webp', 1, 0, 1, '2026-10-05 02:00:30', '2026-10-05 02:00:30'),
(41, 7, 'uploads/items/7/item-7-30760b0199814743c8755b46.png', 'Victory Town.png', 0, 1, 1, '2026-10-05 02:01:17', '2026-10-05 02:01:17'),
(42, 8, 'uploads/items/8/item-8-2dfe2b1abf1a2adc892429e4.png', 'queso_cheddar.png', 0, 1, 1, '2026-10-05 02:02:04', '2026-10-05 02:02:04'),
(43, 9, 'uploads/items/9/item-9-929c4a5037e5a8fe4c863ccf.png', 'queso_tocinata.png', 0, 1, 1, '2026-10-05 02:02:48', '2026-10-05 02:02:48'),
(44, 10, 'uploads/items/10/item-10-4d54a1207f046146848d43c6.png', 'sencilla.png', 0, 1, 1, '2026-10-05 02:03:33', '2026-10-05 02:03:33'),
(45, 2, 'uploads/items/2/item-2-9c6c6360da41c4e6b54fcb5e.webp', 'extra_cheese.webp', 0, 1, 1, '2026-10-05 02:04:00', '2026-10-05 02:04:00'),
(46, 11, 'uploads/items/11/item-11-e355da6ed1f4dca863d9a479.png', '4x4.png', 0, 1, 1, '2026-10-05 02:04:41', '2026-10-05 02:04:41'),
(47, 5, 'uploads/items/5/item-5-0fd09cb7aa3383befbda6fd5.webp', 'Padrisima.webp', 0, 1, 1, '2026-10-05 02:04:59', '2026-10-05 02:04:59'),
(48, 12, 'uploads/items/12/item-12-a5a131291181d133d4247868.png', 'gaucha.png', 0, 1, 1, '2026-10-05 02:05:25', '2026-10-05 02:05:25'),
(49, 13, 'uploads/items/13/item-13-f9ca66c571c1223915691bf1.png', 'hawai_town.png', 0, 1, 1, '2026-10-05 02:05:45', '2026-10-05 02:05:45'),
(50, 14, 'uploads/items/14/item-14-04dac8713814e8ac689318e1.png', 'heinz_town.png', 0, 1, 1, '2026-10-05 02:06:06', '2026-10-05 02:06:06'),
(51, 15, 'uploads/items/15/item-15-72d6496929cd48360e297c2d.png', 'napolitown.png', 0, 1, 1, '2026-10-05 02:06:29', '2026-10-05 02:06:29'),
(52, 1, 'uploads/items/1/item-1-9c11f595ddf6a4543c52c8f7.webp', 'street_town.webp', 0, 1, 1, '2026-10-05 02:06:53', '2026-10-05 02:06:53'),
(53, 16, 'uploads/items/16/item-16-120304b8fd4e38bad52b66f6.png', 'falafel.png', 0, 1, 1, '2026-10-05 02:07:12', '2026-10-05 02:07:12'),
(54, 17, 'uploads/items/17/item-17-37ef015d5aeef02c5913811a.png', 'sweet_chick_town.png', 0, 1, 1, '2026-10-05 02:07:36', '2026-10-05 02:07:36'),
(55, 6, 'uploads/items/6/item-6-6f54279d5a80cfdb47cb9da9.webp', 'chicken.webp', 0, 1, 1, '2026-10-05 02:07:55', '2026-10-05 02:07:55'),
(56, 18, 'uploads/items/18/item-18-20d905c98bb539875f8a272d.webp', 'heinz.webp', 0, 1, 1, '2026-10-05 02:08:20', '2026-10-05 02:08:20'),
(57, 19, 'uploads/items/19/item-19-b691cf416db40d36a02110d0.webp', 'Gaucho.webp', 0, 1, 1, '2026-10-05 02:10:20', '2026-10-05 02:10:20'),
(58, 20, 'uploads/items/20/item-20-00a3e3b7b573003bc88bb9de.webp', 'street_dog.webp', 0, 1, 1, '2026-10-05 02:10:40', '2026-10-05 02:10:40'),
(59, 21, 'uploads/items/21/item-21-3f37acc2e97ac699d9b1e9dc.webp', 'bacon_cheddar.webp', 0, 1, 1, '2026-10-05 02:11:04', '2026-10-05 02:11:04'),
(60, 22, 'uploads/items/22/item-22-a62050a045c7b90666f56633.webp', 'hawai.webp', 0, 1, 1, '2026-10-05 02:11:22', '2026-10-05 02:11:22'),
(61, 23, 'uploads/items/23/item-23-b2bf850d6a85343290454f66.webp', 'sexy_dog.webp', 0, 1, 1, '2026-10-05 02:12:26', '2026-10-05 02:12:26'),
(62, 24, 'uploads/items/24/item-24-f328a5eec5410e2a002f5b0c.png', 'Hot Dog Gourmet con Queso y Salsas.png', 0, 1, 1, '2026-10-05 02:12:49', '2026-10-05 02:12:49'),
(63, 25, 'uploads/items/25/item-25-9704d9035c55266885c0f6eb.png', 'be8441a2-9b16-4996-b2a5-0686547f705b.png', 0, 1, 1, '2026-10-05 02:13:13', '2026-10-05 02:13:13'),
(64, 26, 'uploads/items/26/item-26-eb914af2425c585b953f394e.webp', 'Sweet_BBQ.webp', 0, 1, 1, '2026-10-05 02:13:42', '2026-10-05 02:13:42'),
(65, 27, 'uploads/items/27/item-27-77a8ef4943751a6d8010c388.webp', 'Miel_mostaza.webp', 0, 1, 1, '2026-10-05 02:14:03', '2026-10-05 02:14:03'),
(66, 28, 'uploads/items/28/item-28-08806a77eb8cb15d080f4e51.webp', 'Buffalo.webp', 0, 1, 1, '2026-10-05 02:14:24', '2026-10-05 02:14:24'),
(67, 29, 'uploads/items/29/item-29-a562bacbba91ad4329389338.webp', 'Miel_picante.webp', 0, 1, 1, '2026-10-05 02:14:42', '2026-10-05 02:14:42');

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `item_price_options`
--

CREATE TABLE `item_price_options` (
  `id` int(10) UNSIGNED NOT NULL,
  `item_id` int(10) UNSIGNED NOT NULL,
  `quantity` int(10) UNSIGNED NOT NULL,
  `quantity_label` varchar(100) DEFAULT NULL,
  `price` decimal(12,2) NOT NULL DEFAULT 0.00,
  `combo_price` decimal(12,2) DEFAULT NULL,
  `display_order` int(10) UNSIGNED NOT NULL DEFAULT 0,
  `is_active` tinyint(1) NOT NULL DEFAULT 1,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Volcado de datos para la tabla `item_price_options`
--

INSERT INTO `item_price_options` (`id`, `item_id`, `quantity`, `quantity_label`, `price`, `combo_price`, `display_order`, `is_active`, `created_at`, `updated_at`) VALUES
(49, 26, 6, 'Alitas', 29900.00, 35900.00, 0, 1, '2026-10-05 02:13:42', '2026-10-05 02:13:42'),
(50, 26, 9, 'Alitas', 36500.00, 42500.00, 1, 1, '2026-10-05 02:13:42', '2026-10-05 02:13:42'),
(51, 26, 12, 'Alitas', 41500.00, 47900.00, 2, 1, '2026-10-05 02:13:42', '2026-10-05 02:13:42'),
(52, 26, 20, 'Alitas', 61500.00, 73900.00, 3, 1, '2026-10-05 02:13:42', '2026-10-05 02:13:42'),
(53, 26, 20, 'Combo Familiar', 85900.00, NULL, 4, 1, '2026-10-05 02:13:42', '2026-10-05 02:13:42'),
(54, 26, 30, 'Mega Familiar', 114500.00, NULL, 5, 1, '2026-10-05 02:13:42', '2026-10-05 02:13:42'),
(55, 27, 6, 'Alitas', 29900.00, 35900.00, 0, 1, '2026-10-05 02:14:03', '2026-10-05 02:14:03'),
(56, 27, 9, 'Alitas', 36500.00, 42500.00, 1, 1, '2026-10-05 02:14:03', '2026-10-05 02:14:03'),
(57, 27, 12, 'Alitas', 41500.00, 47900.00, 2, 1, '2026-10-05 02:14:03', '2026-10-05 02:14:03'),
(58, 27, 20, 'Alitas', 61500.00, 73900.00, 3, 1, '2026-10-05 02:14:03', '2026-10-05 02:14:03'),
(59, 27, 20, 'Combo Familiar', 85900.00, 0.00, 4, 1, '2026-10-05 02:14:03', '2026-10-05 02:14:03'),
(60, 27, 30, 'Mega Familiar', 114500.00, 0.00, 5, 1, '2026-10-05 02:14:03', '2026-10-05 02:14:03'),
(61, 28, 6, 'Alitas', 29900.00, 35900.00, 0, 1, '2026-10-05 02:14:24', '2026-10-05 02:14:24'),
(62, 28, 9, 'Alitas', 36500.00, 42500.00, 1, 1, '2026-10-05 02:14:24', '2026-10-05 02:14:24'),
(63, 28, 12, 'Alitas', 41500.00, 47900.00, 2, 1, '2026-10-05 02:14:24', '2026-10-05 02:14:24'),
(64, 28, 20, 'Alitas', 61500.00, 73900.00, 3, 1, '2026-10-05 02:14:24', '2026-10-05 02:14:24'),
(65, 28, 20, 'Combo Familiar', 85900.00, 0.00, 4, 1, '2026-10-05 02:14:24', '2026-10-05 02:14:24'),
(66, 28, 30, 'Mega Familiar', 114500.00, 0.00, 5, 1, '2026-10-05 02:14:24', '2026-10-05 02:14:24'),
(67, 29, 6, 'Alitas', 29900.00, 35900.00, 0, 1, '2026-10-05 02:14:42', '2026-10-05 02:14:42'),
(68, 29, 9, 'Alitas', 36500.00, 42500.00, 1, 1, '2026-10-05 02:14:42', '2026-10-05 02:14:42'),
(69, 29, 12, 'Alitas', 41500.00, 47900.00, 2, 1, '2026-10-05 02:14:42', '2026-10-05 02:14:42'),
(70, 29, 20, 'Alitas', 61500.00, 73900.00, 3, 1, '2026-10-05 02:14:42', '2026-10-05 02:14:42'),
(71, 29, 20, 'Combo Familiar', 85900.00, 0.00, 4, 1, '2026-10-05 02:14:42', '2026-10-05 02:14:42'),
(72, 29, 30, 'Mega Familiar', 114500.00, 0.00, 5, 1, '2026-10-05 02:14:42', '2026-10-05 02:14:42');

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `notifications`
--

CREATE TABLE `notifications` (
  `id` int(10) UNSIGNED NOT NULL,
  `user_id` int(10) UNSIGNED NOT NULL,
  `branch_id` int(10) UNSIGNED DEFAULT NULL,
  `type` varchar(50) NOT NULL,
  `title` varchar(150) NOT NULL,
  `message` varchar(500) NOT NULL,
  `reference_type` varchar(50) DEFAULT NULL,
  `reference_id` int(10) UNSIGNED DEFAULT NULL,
  `is_read` tinyint(1) NOT NULL DEFAULT 0,
  `read_at` datetime DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Volcado de datos para la tabla `notifications`
--

INSERT INTO `notifications` (`id`, `user_id`, `branch_id`, `type`, `title`, `message`, `reference_type`, `reference_id`, `is_read`, `read_at`, `created_at`) VALUES
(1, 5, NULL, 'system', 'Prueba de notificaciones', 'El sistema de notificaciones de Velox está funcionando correctamente.', 'test', NULL, 1, '2026-09-21 11:55:17', '2026-09-21 16:28:16'),
(2, 1, 1, 'reservation_created', 'Nueva reserva', 'Se creó la reserva RSV-B5320D para el 2026-09-21 de 13:30:00 a 15:30:00. Personas: 2.', 'reservation', 37, 0, NULL, '2026-09-21 16:43:21'),
(3, 5, 1, 'reservation_created', 'Nueva reserva', 'Se creó la reserva RSV-B5320D para el 2026-09-21 de 13:30:00 a 15:30:00. Personas: 2.', 'reservation', 37, 1, '2026-09-21 11:55:11', '2026-09-21 16:43:21'),
(4, 2, 1, 'reservation_created', 'Nueva reserva', 'Se creó la reserva RSV-B5320D para el 2026-09-21 de 13:30:00 a 15:30:00. Personas: 2.', 'reservation', 37, 0, NULL, '2026-09-21 16:43:21'),
(5, 1, 2, 'reservation_created', 'Nueva reserva', 'Se creó la reserva RSV-A7DAAA para el 2026-09-21 de 13:30:00 a 15:30:00. Personas: 2.', 'reservation', 38, 0, NULL, '2026-09-21 17:04:22'),
(6, 5, 2, 'reservation_created', 'Nueva reserva', 'Se creó la reserva RSV-A7DAAA para el 2026-09-21 de 13:30:00 a 15:30:00. Personas: 2.', 'reservation', 38, 1, '2026-09-21 12:05:13', '2026-09-21 17:04:22'),
(7, 1, 1, 'reservation_created', 'Nueva reserva', 'Se creó la reserva RSV-42741C para el 2026-09-27 de 16:30:00 a 18:30:00. Personas: 2.', 'reservation', 39, 0, NULL, '2026-09-27 20:00:23'),
(8, 5, 1, 'reservation_created', 'Nueva reserva', 'Se creó la reserva RSV-42741C para el 2026-09-27 de 16:30:00 a 18:30:00. Personas: 2.', 'reservation', 39, 1, '2026-10-09 15:49:49', '2026-09-27 20:00:23'),
(9, 2, 1, 'reservation_created', 'Nueva reserva', 'Se creó la reserva RSV-42741C para el 2026-09-27 de 16:30:00 a 18:30:00. Personas: 2.', 'reservation', 39, 0, NULL, '2026-09-27 20:00:23'),
(10, 1, 2, 'reservation_created', 'Nueva reserva', 'Se creó la reserva RSV-C329E6 para el 2026-09-27 de 17:30:00 a 19:30:00. Personas: 2.', 'reservation', 40, 0, NULL, '2026-09-27 20:03:41'),
(11, 5, 2, 'reservation_created', 'Nueva reserva', 'Se creó la reserva RSV-C329E6 para el 2026-09-27 de 17:30:00 a 19:30:00. Personas: 2.', 'reservation', 40, 1, '2026-10-09 15:49:49', '2026-09-27 20:03:41'),
(12, 1, 1, 'reservation_created', 'Nueva reserva', 'Se creó la reserva RSV-032D49 para el 2026-09-27 de 19:00:00 a 21:00:00. Personas: 2.', 'reservation', 41, 0, NULL, '2026-09-27 20:08:42'),
(13, 5, 1, 'reservation_created', 'Nueva reserva', 'Se creó la reserva RSV-032D49 para el 2026-09-27 de 19:00:00 a 21:00:00. Personas: 2.', 'reservation', 41, 1, '2026-10-09 15:49:49', '2026-09-27 20:08:42'),
(14, 2, 1, 'reservation_created', 'Nueva reserva', 'Se creó la reserva RSV-032D49 para el 2026-09-27 de 19:00:00 a 21:00:00. Personas: 2.', 'reservation', 41, 0, NULL, '2026-09-27 20:08:42'),
(15, 1, 7, 'reservation_created', 'Nueva reserva', 'Se creó la reserva RSV-334B9A para el 2026-09-27 de 19:00:00 a 21:00:00. Personas: 2.', 'reservation', 42, 0, NULL, '2026-09-27 20:36:26'),
(16, 5, 7, 'reservation_created', 'Nueva reserva', 'Se creó la reserva RSV-334B9A para el 2026-09-27 de 19:00:00 a 21:00:00. Personas: 2.', 'reservation', 42, 1, '2026-10-09 15:49:49', '2026-09-27 20:36:26'),
(17, 1, 7, 'reservation_created', 'Nueva reserva', 'Se creó la reserva RSV-0B88D6 para el 2026-09-27 de 19:00:00 a 21:00:00. Personas: 2.', 'reservation', 43, 0, NULL, '2026-09-27 20:37:36'),
(18, 5, 7, 'reservation_created', 'Nueva reserva', 'Se creó la reserva RSV-0B88D6 para el 2026-09-27 de 19:00:00 a 21:00:00. Personas: 2.', 'reservation', 43, 1, '2026-10-09 15:49:49', '2026-09-27 20:37:36'),
(19, 1, 4, 'reservation_created', 'Nueva reserva', 'Se creó la reserva RSV-25F64C para el 2026-09-29 de 12:00:00 a 14:00:00. Personas: 3.', 'reservation', 44, 0, NULL, '2026-09-27 21:13:33'),
(20, 5, 4, 'reservation_created', 'Nueva reserva', 'Se creó la reserva RSV-25F64C para el 2026-09-29 de 12:00:00 a 14:00:00. Personas: 3.', 'reservation', 44, 1, '2026-10-09 15:49:49', '2026-09-27 21:13:33'),
(21, 1, 5, 'reservation_created', 'Nueva reserva', 'Se creó la reserva RSV-C44FA8 para el 2026-10-09 de 18:00:00 a 20:00:00. Personas: 2.', 'reservation', 45, 0, NULL, '2026-10-09 20:28:35'),
(22, 5, 5, 'reservation_created', 'Nueva reserva', 'Se creó la reserva RSV-C44FA8 para el 2026-10-09 de 18:00:00 a 20:00:00. Personas: 2.', 'reservation', 45, 1, '2026-10-09 15:49:49', '2026-10-09 20:28:35'),
(23, 1, 1, 'reservation_created', 'Nueva reserva', 'Se creó la reserva RSV-25D2F8 para el 2026-10-09 de 17:00:00 a 19:00:00. Personas: 2.', 'reservation', 46, 0, NULL, '2026-10-09 20:38:52'),
(24, 5, 1, 'reservation_created', 'Nueva reserva', 'Se creó la reserva RSV-25D2F8 para el 2026-10-09 de 17:00:00 a 19:00:00. Personas: 2.', 'reservation', 46, 1, '2026-10-09 15:39:19', '2026-10-09 20:38:52'),
(25, 2, 1, 'reservation_created', 'Nueva reserva', 'Se creó la reserva RSV-25D2F8 para el 2026-10-09 de 17:00:00 a 19:00:00. Personas: 2.', 'reservation', 46, 0, NULL, '2026-10-09 20:38:52'),
(26, 1, 1, 'reservation_created', 'Nueva reserva', 'Se creó la reserva RSV-D72A48 para el 2026-10-09 de 20:00:00 a 22:00:00. Personas: 2.', 'reservation', 47, 0, NULL, '2026-10-09 20:46:45'),
(27, 5, 1, 'reservation_created', 'Nueva reserva', 'Se creó la reserva RSV-D72A48 para el 2026-10-09 de 20:00:00 a 22:00:00. Personas: 2.', 'reservation', 47, 1, '2026-10-09 15:49:49', '2026-10-09 20:46:45'),
(28, 2, 1, 'reservation_created', 'Nueva reserva', 'Se creó la reserva RSV-D72A48 para el 2026-10-09 de 20:00:00 a 22:00:00. Personas: 2.', 'reservation', 47, 0, NULL, '2026-10-09 20:46:45'),
(29, 1, 7, 'reservation_created', 'Nueva reserva', 'Se creó la reserva RSV-136623 para el 2026-10-16 de 15:30:00 a 17:30:00. Personas: 2.', 'reservation', 48, 0, NULL, '2026-10-09 21:16:46'),
(30, 5, 7, 'reservation_created', 'Nueva reserva', 'Se creó la reserva RSV-136623 para el 2026-10-16 de 15:30:00 a 17:30:00. Personas: 2.', 'reservation', 48, 0, NULL, '2026-10-09 21:16:46'),
(31, 1, 7, 'reservation_created', 'Nueva reserva', 'Se creó la reserva RSV-E139E3 para el 2026-10-12 de 18:00:00 a 20:00:00. Personas: 2.', 'reservation', 49, 0, NULL, '2026-10-12 21:35:22'),
(32, 5, 7, 'reservation_created', 'Nueva reserva', 'Se creó la reserva RSV-E139E3 para el 2026-10-12 de 18:00:00 a 20:00:00. Personas: 2.', 'reservation', 49, 0, NULL, '2026-10-12 21:35:22'),
(33, 1, 7, 'reservation_created', 'Nueva reserva', 'Se creó la reserva RSV-0C8F4A para el 2026-10-12 de 18:00:00 a 20:00:00. Personas: 2.', 'reservation', 50, 0, NULL, '2026-10-12 21:40:33'),
(34, 5, 7, 'reservation_created', 'Nueva reserva', 'Se creó la reserva RSV-0C8F4A para el 2026-10-12 de 18:00:00 a 20:00:00. Personas: 2.', 'reservation', 50, 0, NULL, '2026-10-12 21:40:33'),
(35, 1, 7, 'reservation_created', 'Nueva reserva', 'Se creó la reserva RSV-6F6F8A para el 2026-10-12 de 18:00:00 a 20:00:00. Personas: 2.', 'reservation', 51, 0, NULL, '2026-10-12 21:43:21'),
(36, 5, 7, 'reservation_created', 'Nueva reserva', 'Se creó la reserva RSV-6F6F8A para el 2026-10-12 de 18:00:00 a 20:00:00. Personas: 2.', 'reservation', 51, 0, NULL, '2026-10-12 21:43:21'),
(37, 1, 7, 'reservation_created', 'Nueva reserva', 'Se creó la reserva RSV-4D0860 para el 2026-10-13 de 12:00:00 a 14:00:00. Personas: 2.', 'reservation', 53, 0, NULL, '2026-10-12 21:56:56'),
(38, 5, 7, 'reservation_created', 'Nueva reserva', 'Se creó la reserva RSV-4D0860 para el 2026-10-13 de 12:00:00 a 14:00:00. Personas: 2.', 'reservation', 53, 0, NULL, '2026-10-12 21:56:56'),
(39, 1, 7, 'event_request', 'Nuevo evento solicitado', 'Solicitud de evento de jhonatan cuadros para 2026-10-22.', 'event_request', 1, 0, NULL, '2026-10-12 22:03:30'),
(40, 5, 7, 'event_request', 'Nuevo evento solicitado', 'Solicitud de evento de jhonatan cuadros para 2026-10-22.', 'event_request', 1, 0, NULL, '2026-10-12 22:03:33'),
(41, 1, 7, 'event_request', 'Nuevo evento solicitado', 'Solicitud de evento de jhonatan cuadros para 2026-10-13.', 'event_request', 2, 0, NULL, '2026-10-12 22:07:21'),
(42, 5, 7, 'event_request', 'Nuevo evento solicitado', 'Solicitud de evento de jhonatan cuadros para 2026-10-13.', 'event_request', 2, 0, NULL, '2026-10-12 22:07:24');

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `orders`
--

CREATE TABLE `orders` (
  `id` int(10) UNSIGNED NOT NULL,
  `order_number` varchar(30) NOT NULL,
  `branch_id` int(10) UNSIGNED NOT NULL,
  `customer_id` int(10) UNSIGNED DEFAULT NULL,
  `order_type` enum('pos','ecommerce','booking_service') DEFAULT 'pos',
  `status` enum('pending','processing','completed','cancelled','refunded') DEFAULT 'pending',
  `subtotal` decimal(12,2) NOT NULL DEFAULT 0.00,
  `tax_amount` decimal(12,2) NOT NULL DEFAULT 0.00,
  `discount_amount` decimal(12,2) NOT NULL DEFAULT 0.00,
  `total` decimal(12,2) NOT NULL DEFAULT 0.00,
  `notes` text DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Volcado de datos para la tabla `orders`
--

INSERT INTO `orders` (`id`, `order_number`, `branch_id`, `customer_id`, `order_type`, `status`, `subtotal`, `tax_amount`, `discount_amount`, `total`, `notes`, `created_at`, `updated_at`) VALUES
(1, 'ORD-20260907-001', 1, 4, 'ecommerce', 'completed', 89000.00, 16910.00, 0.00, 105910.00, 'Pedido de prueba e-commerce', '2026-09-07 15:02:04', '2026-09-07 15:02:04'),
(6, 'ORD-6AA6F4C118157', 1, 1, 'ecommerce', 'pending', 10000.00, 0.00, 0.00, 10000.00, 'Prueba OrderService', '2026-09-13 19:08:49', '2026-09-13 19:08:49');

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `order_items`
--

CREATE TABLE `order_items` (
  `id` int(10) UNSIGNED NOT NULL,
  `order_id` int(10) UNSIGNED NOT NULL,
  `item_id` int(10) UNSIGNED NOT NULL,
  `quantity` int(10) UNSIGNED NOT NULL DEFAULT 1,
  `unit_price` decimal(12,2) NOT NULL,
  `total_price` decimal(12,2) NOT NULL,
  `metadata` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL CHECK (json_valid(`metadata`))
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Volcado de datos para la tabla `order_items`
--

INSERT INTO `order_items` (`id`, `order_id`, `item_id`, `quantity`, `unit_price`, `total_price`, `metadata`) VALUES
(1, 1, 2, 1, 89000.00, 89000.00, '{\"color\": \"Morado Lavender\", \"sku\": \"PRD-AGENDA-LAV\"}'),
(6, 6, 1, 1, 10000.00, 10000.00, NULL);

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `payments`
--

CREATE TABLE `payments` (
  `id` int(10) UNSIGNED NOT NULL,
  `order_id` int(10) UNSIGNED NOT NULL,
  `gateway_id` int(10) UNSIGNED NOT NULL,
  `transaction_reference` varchar(100) NOT NULL,
  `gateway_reference` varchar(100) DEFAULT NULL,
  `amount` decimal(12,2) NOT NULL,
  `currency` varchar(3) DEFAULT 'COP',
  `status` enum('pending','approved','rejected','voided') DEFAULT 'pending',
  `raw_response` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL CHECK (json_valid(`raw_response`)),
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Volcado de datos para la tabla `payments`
--

INSERT INTO `payments` (`id`, `order_id`, `gateway_id`, `transaction_reference`, `gateway_reference`, `amount`, `currency`, `status`, `raw_response`, `created_at`) VALUES
(1, 1, 2, 'TX-WOMPI-998877', '12345-1694000000-88412', 105910.00, 'COP', 'approved', '{\"status\": \"APPROVED\", \"reference\": \"TX-WOMPI-998877\", \"amount_in_cents\": 10591000}', '2026-09-07 15:02:04');

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `payment_gateways`
--

CREATE TABLE `payment_gateways` (
  `id` int(10) UNSIGNED NOT NULL,
  `code` varchar(30) NOT NULL,
  `name` varchar(50) NOT NULL,
  `is_active` tinyint(1) DEFAULT 1,
  `is_sandbox` tinyint(1) DEFAULT 1,
  `settings_json` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL CHECK (json_valid(`settings_json`))
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Volcado de datos para la tabla `payment_gateways`
--

INSERT INTO `payment_gateways` (`id`, `code`, `name`, `is_active`, `is_sandbox`, `settings_json`) VALUES
(1, 'cash', 'Efectivo / Contraentrega', 1, 0, NULL),
(2, 'wompi', 'Wompi Colombia', 1, 0, '{\"public_key\": \"pub_prod_bloVIIaE9mPUdTrzO6WZMzPy1smKGof9\", \"private_key\": \"prv_prod_xxxxxxxxxxxxxxxxxxxxxxxx\", \"events_secret\": \"integrity_secret_xxx\"}'),
(3, 'payu', 'PayU Latam', 1, 1, '{\"api_key\": \"test_key\", \"merchant_id\": \"12345\"}');

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `permissions`
--

CREATE TABLE `permissions` (
  `id` int(10) UNSIGNED NOT NULL,
  `slug` varchar(100) NOT NULL,
  `module` varchar(50) NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Volcado de datos para la tabla `permissions`
--

INSERT INTO `permissions` (`id`, `slug`, `module`, `created_at`) VALUES
(8, 'dashboard.read', 'dashboard', '2026-10-05 03:13:51'),
(9, 'reservations.read', 'reservations', '2026-10-05 03:13:51'),
(10, 'reservations.create', 'reservations', '2026-10-05 03:13:51'),
(11, 'reservations.update', 'reservations', '2026-10-05 03:13:51'),
(12, 'reservations.cancel', 'reservations', '2026-10-05 03:13:51'),
(13, 'products.read', 'products', '2026-10-05 03:13:51'),
(14, 'products.create', 'products', '2026-10-05 03:13:51'),
(15, 'products.update', 'products', '2026-10-05 03:13:51'),
(16, 'products.delete', 'products', '2026-10-05 03:13:51'),
(17, 'categories.read', 'categories', '2026-10-05 03:13:51'),
(18, 'categories.create', 'categories', '2026-10-05 03:13:51'),
(19, 'categories.update', 'categories', '2026-10-05 03:13:51'),
(20, 'categories.delete', 'categories', '2026-10-05 03:13:51'),
(21, 'settings.read', 'settings', '2026-10-05 03:13:51'),
(22, 'settings.update', 'settings', '2026-10-05 03:13:51'),
(23, 'users.read', 'users', '2026-10-05 03:13:51'),
(24, 'users.create', 'users', '2026-10-05 03:13:51'),
(25, 'users.update', 'users', '2026-10-05 03:13:51'),
(26, 'users.delete', 'users', '2026-10-05 03:13:51'),
(27, 'branches.read', 'branches', '2026-10-05 03:13:51'),
(28, 'branches.create', 'branches', '2026-10-05 03:13:51'),
(29, 'branches.update', 'branches', '2026-10-05 03:13:51'),
(30, 'branches.delete', 'branches', '2026-10-05 03:13:51');

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `reservation_settings`
--

CREATE TABLE `reservation_settings` (
  `id` int(10) UNSIGNED NOT NULL,
  `branch_id` int(10) UNSIGNED NOT NULL,
  `is_enabled` tinyint(1) NOT NULL DEFAULT 1,
  `min_people` tinyint(3) UNSIGNED NOT NULL DEFAULT 1,
  `max_people` tinyint(3) UNSIGNED NOT NULL DEFAULT 20,
  `slot_interval_minutes` smallint(5) UNSIGNED NOT NULL DEFAULT 30,
  `reservation_duration_minutes` smallint(5) UNSIGNED NOT NULL DEFAULT 120,
  `min_advance_minutes` int(10) UNSIGNED NOT NULL DEFAULT 60,
  `max_advance_days` int(10) UNSIGNED NOT NULL DEFAULT 30,
  `allow_same_day` tinyint(1) NOT NULL DEFAULT 1,
  `allow_customer_cancellation` tinyint(1) NOT NULL DEFAULT 1,
  `cancellation_limit_minutes` int(10) UNSIGNED NOT NULL DEFAULT 120,
  `auto_confirm` tinyint(1) NOT NULL DEFAULT 1,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Volcado de datos para la tabla `reservation_settings`
--

INSERT INTO `reservation_settings` (`id`, `branch_id`, `is_enabled`, `min_people`, `max_people`, `slot_interval_minutes`, `reservation_duration_minutes`, `min_advance_minutes`, `max_advance_days`, `allow_same_day`, `allow_customer_cancellation`, `cancellation_limit_minutes`, `auto_confirm`, `created_at`, `updated_at`) VALUES
(1, 1, 1, 1, 20, 30, 120, 60, 60, 1, 1, 120, 1, '2026-09-07 20:17:19', '2026-09-07 20:17:19'),
(2, 2, 1, 1, 20, 30, 120, 60, 60, 1, 1, 120, 1, '2026-09-07 20:17:19', '2026-09-07 20:17:19'),
(3, 3, 1, 1, 20, 30, 120, 60, 60, 1, 1, 120, 1, '2026-09-27 20:28:23', '2026-09-27 20:28:23'),
(4, 4, 1, 1, 20, 30, 120, 60, 60, 1, 1, 120, 1, '2026-09-27 20:28:23', '2026-09-27 20:28:23'),
(5, 5, 1, 1, 20, 30, 120, 60, 60, 1, 1, 120, 1, '2026-09-27 20:28:23', '2026-09-27 20:28:23'),
(6, 6, 1, 1, 20, 30, 120, 60, 60, 1, 1, 120, 1, '2026-09-27 20:28:23', '2026-09-27 20:28:23'),
(7, 7, 1, 1, 20, 30, 120, 60, 60, 1, 1, 120, 1, '2026-09-27 20:28:23', '2026-09-27 20:28:23');

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `reservation_tables`
--

CREATE TABLE `reservation_tables` (
  `id` int(10) UNSIGNED NOT NULL,
  `reservation_id` int(10) UNSIGNED NOT NULL,
  `table_id` int(10) UNSIGNED NOT NULL,
  `assigned_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Volcado de datos para la tabla `reservation_tables`
--

INSERT INTO `reservation_tables` (`id`, `reservation_id`, `table_id`, `assigned_at`) VALUES
(43, 42, 22, '2026-09-27 20:36:26'),
(44, 43, 20, '2026-09-27 20:37:36'),
(45, 44, 17, '2026-09-27 21:13:33'),
(46, 45, 18, '2026-10-09 20:28:35'),
(47, 46, 8, '2026-10-09 20:38:52'),
(48, 47, 8, '2026-10-09 20:46:45'),
(49, 48, 22, '2026-10-09 21:16:46'),
(50, 49, 22, '2026-10-12 21:35:22'),
(51, 50, 20, '2026-10-12 21:40:33'),
(52, 51, 21, '2026-10-12 21:43:21'),
(53, 52, 22, '2026-10-12 21:50:12'),
(54, 53, 20, '2026-10-12 21:56:56');

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `restaurant_category_rules`
--

CREATE TABLE `restaurant_category_rules` (
  `category_id` int(10) UNSIGNED NOT NULL,
  `show_ingredients` tinyint(1) NOT NULL DEFAULT 0,
  `show_combo_price` tinyint(1) NOT NULL DEFAULT 0,
  `show_portion` tinyint(1) NOT NULL DEFAULT 0,
  `show_spicy_level` tinyint(1) NOT NULL DEFAULT 0,
  `show_allergens` tinyint(1) NOT NULL DEFAULT 0,
  `show_featured` tinyint(1) NOT NULL DEFAULT 1,
  `requires_inventory` tinyint(1) NOT NULL DEFAULT 1,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Volcado de datos para la tabla `restaurant_category_rules`
--

INSERT INTO `restaurant_category_rules` (`category_id`, `show_ingredients`, `show_combo_price`, `show_portion`, `show_spicy_level`, `show_allergens`, `show_featured`, `requires_inventory`, `created_at`, `updated_at`) VALUES
(4, 1, 1, 1, 1, 1, 1, 1, '2026-09-28 12:53:25', '2026-09-28 12:53:25'),
(5, 1, 1, 1, 1, 1, 1, 1, '2026-09-28 12:53:25', '2026-09-28 12:53:25'),
(6, 1, 1, 1, 1, 1, 1, 1, '2026-09-28 12:53:25', '2026-09-28 12:53:25'),
(7, 1, 1, 1, 1, 1, 1, 1, '2026-09-28 12:53:25', '2026-09-28 12:53:25'),
(8, 1, 1, 1, 1, 1, 1, 1, '2026-09-28 12:53:25', '2026-09-28 12:53:25'),
(9, 1, 0, 1, 1, 1, 1, 1, '2026-09-28 12:53:25', '2026-09-28 12:53:25'),
(10, 1, 1, 1, 0, 1, 1, 1, '2026-09-28 12:53:25', '2026-09-28 12:53:25'),
(11, 1, 1, 1, 1, 1, 1, 1, '2026-09-28 12:53:25', '2026-09-28 12:53:25'),
(12, 0, 0, 1, 0, 1, 1, 1, '2026-09-28 12:53:25', '2026-09-28 12:53:25'),
(13, 0, 0, 1, 0, 0, 1, 1, '2026-09-28 12:53:25', '2026-09-28 12:53:25'),
(14, 1, 0, 1, 0, 1, 1, 1, '2026-09-28 12:53:25', '2026-09-28 12:53:25'),
(15, 0, 0, 1, 0, 0, 1, 1, '2026-09-28 12:53:25', '2026-09-28 12:53:25'),
(16, 1, 0, 1, 0, 1, 1, 1, '2026-09-28 12:53:25', '2026-09-28 12:53:25'),
(17, 1, 1, 1, 1, 1, 1, 1, '2026-10-04 20:00:14', '2026-10-04 20:00:14');

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `restaurant_reservations`
--

CREATE TABLE `restaurant_reservations` (
  `id` int(10) UNSIGNED NOT NULL,
  `reservation_code` varchar(30) NOT NULL,
  `branch_id` int(10) UNSIGNED NOT NULL,
  `customer_id` int(10) UNSIGNED NOT NULL,
  `reservation_date` date NOT NULL,
  `start_time` time NOT NULL,
  `end_time` time NOT NULL,
  `people_count` tinyint(3) UNSIGNED NOT NULL,
  `status` enum('pending','confirmed','checked_in','completed','cancelled','no_show') NOT NULL DEFAULT 'pending',
  `source` enum('web','admin','phone','whatsapp','walk_in') NOT NULL DEFAULT 'web',
  `notes` text DEFAULT NULL,
  `cancellation_reason` varchar(255) DEFAULT NULL,
  `confirmed_at` datetime DEFAULT NULL,
  `checked_in_at` datetime DEFAULT NULL,
  `cancelled_at` datetime DEFAULT NULL,
  `created_by` int(10) UNSIGNED DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Volcado de datos para la tabla `restaurant_reservations`
--

INSERT INTO `restaurant_reservations` (`id`, `reservation_code`, `branch_id`, `customer_id`, `reservation_date`, `start_time`, `end_time`, `people_count`, `status`, `source`, `notes`, `cancellation_reason`, `confirmed_at`, `checked_in_at`, `cancelled_at`, `created_by`, `created_at`, `updated_at`) VALUES
(42, 'RSV-334B9A', 7, 4, '2026-09-27', '19:00:00', '21:00:00', 2, 'confirmed', 'web', NULL, NULL, '2026-09-27 15:36:26', NULL, NULL, NULL, '2026-09-27 20:36:26', '2026-09-27 20:36:26'),
(43, 'RSV-0B88D6', 7, 4, '2026-09-27', '19:00:00', '21:00:00', 2, 'confirmed', 'web', NULL, NULL, '2026-09-27 15:37:36', NULL, NULL, NULL, '2026-09-27 20:37:36', '2026-09-27 20:37:36'),
(44, 'RSV-25F64C', 4, 4, '2026-09-29', '12:00:00', '14:00:00', 3, 'confirmed', 'web', NULL, NULL, '2026-09-27 16:13:33', NULL, NULL, NULL, '2026-09-27 21:13:33', '2026-09-27 21:13:33'),
(45, 'RSV-C44FA8', 5, 4, '2026-10-09', '18:00:00', '20:00:00', 2, 'confirmed', 'web', NULL, NULL, '2026-10-09 15:28:34', NULL, NULL, NULL, '2026-10-09 20:28:34', '2026-10-09 20:28:34'),
(46, 'RSV-25D2F8', 1, 4, '2026-10-09', '17:00:00', '19:00:00', 2, 'confirmed', 'web', NULL, NULL, '2026-10-09 15:38:52', NULL, NULL, NULL, '2026-10-09 20:38:52', '2026-10-09 20:38:52'),
(47, 'RSV-D72A48', 1, 4, '2026-10-09', '20:00:00', '22:00:00', 2, 'cancelled', 'web', NULL, 'Cancelación realizada por el cliente desde el portal público', '2026-10-09 15:46:45', NULL, '2026-10-09 15:47:22', NULL, '2026-10-09 20:46:45', '2026-10-09 20:47:22'),
(48, 'RSV-136623', 7, 4, '2026-10-16', '15:30:00', '17:30:00', 2, 'cancelled', 'web', NULL, 'Cancelación realizada por el cliente desde el portal público', '2026-10-09 16:16:46', NULL, '2026-10-09 16:17:34', NULL, '2026-10-09 21:16:46', '2026-10-09 21:17:34'),
(49, 'RSV-E139E3', 7, 4, '2026-10-12', '18:00:00', '20:00:00', 2, 'confirmed', 'web', NULL, NULL, '2026-10-12 16:35:22', NULL, NULL, NULL, '2026-10-12 21:35:22', '2026-10-12 21:35:22'),
(50, 'RSV-0C8F4A', 7, 4, '2026-10-12', '18:00:00', '20:00:00', 2, 'confirmed', 'web', NULL, NULL, '2026-10-12 16:40:33', NULL, NULL, NULL, '2026-10-12 21:40:33', '2026-10-12 21:40:33'),
(51, 'RSV-6F6F8A', 7, 4, '2026-10-12', '18:00:00', '20:00:00', 2, 'confirmed', 'web', NULL, NULL, '2026-10-12 16:43:21', NULL, NULL, NULL, '2026-10-12 21:43:21', '2026-10-12 21:43:21'),
(52, 'RSV-2B49F5', 7, 4, '2026-10-13', '12:00:00', '14:00:00', 2, 'confirmed', 'web', NULL, NULL, '2026-10-12 16:50:12', NULL, NULL, NULL, '2026-10-12 21:50:12', '2026-10-12 21:50:12'),
(53, 'RSV-4D0860', 7, 4, '2026-10-13', '12:00:00', '14:00:00', 2, 'confirmed', 'web', NULL, NULL, '2026-10-12 16:56:56', NULL, NULL, NULL, '2026-10-12 21:56:56', '2026-10-12 21:56:56');

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `restaurant_tables`
--

CREATE TABLE `restaurant_tables` (
  `id` int(10) UNSIGNED NOT NULL,
  `branch_id` int(10) UNSIGNED NOT NULL,
  `table_number` varchar(20) NOT NULL,
  `name` varchar(100) DEFAULT NULL,
  `capacity` tinyint(3) UNSIGNED NOT NULL DEFAULT 2,
  `can_combine` tinyint(1) NOT NULL DEFAULT 1,
  `location` varchar(100) DEFAULT NULL,
  `status` enum('available','occupied','maintenance','inactive') NOT NULL DEFAULT 'available',
  `is_active` tinyint(1) NOT NULL DEFAULT 1,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Volcado de datos para la tabla `restaurant_tables`
--

INSERT INTO `restaurant_tables` (`id`, `branch_id`, `table_number`, `name`, `capacity`, `can_combine`, `location`, `status`, `is_active`, `created_at`, `updated_at`) VALUES
(1, 1, '1', 'Mesa 01', 2, 1, 'Salón principal', 'available', 0, '2026-09-07 20:17:19', '2026-09-27 12:27:11'),
(3, 1, '3', 'Mesa 03', 4, 1, 'Salón principal', 'available', 0, '2026-09-07 20:17:19', '2026-09-27 12:27:17'),
(4, 1, '4', 'Mesa 04', 4, 1, 'Salón principal', 'available', 0, '2026-09-07 20:17:19', '2026-09-27 12:27:24'),
(8, 1, '8', 'Mesa 08', 2, 1, 'Terraza', 'available', 1, '2026-09-07 20:17:19', '2026-09-27 12:20:53'),
(9, 2, '2', 'Mesa 01', 2, 1, 'Salón principal', 'available', 0, '2026-09-07 20:17:19', '2026-09-27 12:17:26'),
(10, 2, '3', 'Mesa 02', 2, 1, 'Salón principal', 'available', 0, '2026-09-07 20:17:19', '2026-09-27 12:17:21'),
(11, 2, '4', 'Mesa 03', 4, 1, 'Salón principal', 'available', 0, '2026-09-07 20:17:19', '2026-09-27 12:17:30'),
(12, 2, '5', 'Mesa 04', 4, 1, 'Salón principal', 'available', 0, '2026-09-07 20:17:19', '2026-09-27 12:17:23'),
(13, 2, '6', 'Mesa 05', 6, 1, 'Salón principal', 'available', 0, '2026-09-07 20:17:19', '2026-09-27 12:17:32'),
(14, 2, '7', 'Mesa 06', 2, 1, 'Terraza', 'available', 1, '2026-09-07 20:17:19', '2026-09-27 11:21:06'),
(16, 3, '1', 'Mesa 1', 4, 1, '', 'available', 1, '2026-09-27 12:25:51', '2026-09-27 12:25:51'),
(17, 4, '1', 'Mesa 1', 4, 1, '', 'available', 1, '2026-09-27 12:48:12', '2026-09-27 12:48:12'),
(18, 5, '1', 'Mesa 1', 4, 1, '', 'available', 1, '2026-09-27 12:51:38', '2026-09-27 12:51:38'),
(19, 6, '1', 'Mesa 1', 4, 1, '', 'available', 1, '2026-09-27 12:54:17', '2026-09-27 12:54:17'),
(20, 7, '1', 'Mesa 1', 4, 1, '', 'available', 1, '2026-09-27 12:59:09', '2026-09-27 12:59:09'),
(21, 7, '2', 'Mesa 2', 4, 1, '', 'available', 1, '2026-09-27 19:43:18', '2026-10-09 19:08:30'),
(22, 7, '3', 'Mesa 3', 2, 1, '', 'available', 1, '2026-09-27 19:43:19', '2026-10-09 19:08:37');

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `roles`
--

CREATE TABLE `roles` (
  `id` int(10) UNSIGNED NOT NULL,
  `name` varchar(50) NOT NULL,
  `description` varchar(255) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Volcado de datos para la tabla `roles`
--

INSERT INTO `roles` (`id`, `name`, `description`, `created_at`) VALUES
(1, 'SuperAdmin', 'Acceso total al sistema y multisucursal', '2026-09-07 15:02:04'),
(2, 'Admin', 'Administrador de sucursal específica', '2026-09-07 15:02:04'),
(3, 'staff', 'Personal operario / Colaborador / Barberos / Atención', '2026-09-07 15:02:04'),
(4, 'customer', 'Cliente final registrado', '2026-09-07 15:02:04');

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `role_permissions`
--

CREATE TABLE `role_permissions` (
  `role_id` int(10) UNSIGNED NOT NULL,
  `permission_id` int(10) UNSIGNED NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Volcado de datos para la tabla `role_permissions`
--

INSERT INTO `role_permissions` (`role_id`, `permission_id`) VALUES
(1, 8),
(1, 9),
(1, 10),
(1, 11),
(1, 12),
(1, 13),
(1, 14),
(1, 15),
(1, 16),
(1, 17),
(1, 18),
(1, 19),
(1, 20),
(1, 21),
(1, 22),
(1, 23),
(1, 24),
(1, 25),
(1, 26),
(1, 27),
(1, 28),
(1, 29),
(1, 30),
(2, 8),
(2, 9),
(2, 10),
(2, 11),
(2, 12),
(2, 13),
(2, 14),
(2, 15),
(2, 16),
(2, 17),
(2, 18),
(2, 19),
(2, 20),
(2, 23),
(2, 24),
(2, 25),
(2, 26),
(2, 27),
(2, 28),
(2, 29),
(2, 30),
(3, 8),
(3, 9),
(3, 10),
(3, 11),
(3, 12);

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `schedules`
--

CREATE TABLE `schedules` (
  `id` int(10) UNSIGNED NOT NULL,
  `staff_id` int(10) UNSIGNED NOT NULL,
  `day_of_week` tinyint(3) UNSIGNED NOT NULL COMMENT '1=Lunes, 7=Domingo',
  `start_time` time NOT NULL,
  `end_time` time NOT NULL,
  `is_active` tinyint(1) DEFAULT 1
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Volcado de datos para la tabla `schedules`
--

INSERT INTO `schedules` (`id`, `staff_id`, `day_of_week`, `start_time`, `end_time`, `is_active`) VALUES
(1, 3, 1, '08:00:00', '17:00:00', 1),
(2, 3, 2, '08:00:00', '17:00:00', 1),
(3, 3, 3, '08:00:00', '17:00:00', 1),
(4, 3, 4, '08:00:00', '17:00:00', 1),
(5, 3, 5, '08:00:00', '18:00:00', 1),
(6, 3, 6, '09:00:00', '15:00:00', 1);

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `site_pages`
--

CREATE TABLE `site_pages` (
  `id` int(10) UNSIGNED NOT NULL,
  `slug` varchar(180) NOT NULL,
  `page_type` enum('general','legal','contact','event') NOT NULL DEFAULT 'general',
  `title` varchar(180) NOT NULL,
  `meta_title` varchar(255) DEFAULT NULL,
  `meta_description` varchar(500) DEFAULT NULL,
  `h1` varchar(255) DEFAULT NULL,
  `content_html` longtext DEFAULT NULL,
  `is_published` tinyint(1) NOT NULL DEFAULT 1,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Volcado de datos para la tabla `site_pages`
--

INSERT INTO `site_pages` (`id`, `slug`, `page_type`, `title`, `meta_title`, `meta_description`, `h1`, `content_html`, `is_published`, `created_at`, `updated_at`) VALUES
(1, 'politicas-de-cookies', 'legal', 'Política de Cookies', 'Política de Cookies | Burger Town', 'Política de cookies y tecnologías similares de Burger Town.', 'Política de Cookies', '<p>El sitio web de Burger Town utiliza cookies y tecnologías similares para mejorar la experiencia de navegación de los usuarios.</p><h2>1. ¿Qué son las Cookies?</h2><p>Las cookies son pequeños archivos de texto que se almacenan en su dispositivo cuando visita un sitio web. Estas permiten recordar información sobre su visita para mejorar la funcionalidad del sitio.</p><h2>2. Tipos de Cookies que Utilizamos</h2><p>Nuestro sitio web puede utilizar los siguientes tipos de cookies:</p><ul><li><strong>Cookies Necesarias:</strong> son esenciales para el funcionamiento del sitio web y permiten funciones básicas como navegación y acceso a áreas seguras.</li><li><strong>Cookies de Rendimiento:</strong> permiten analizar cómo los usuarios interactúan con el sitio para mejorar su funcionamiento.</li><li><strong>Cookies de Funcionalidad:</strong> permiten recordar preferencias del usuario como idioma o ubicación.</li><li><strong>Cookies de Marketing:</strong> se utilizan para mostrar publicidad relevante o medir la efectividad de campañas publicitarias.</li></ul><h2>3. Cookies de Terceros</h2><p>El sitio puede utilizar servicios externos como herramientas de analítica, plataformas de pago y servicios de mapas, sujetos a sus propias políticas.</p><h2>4. Gestión de Cookies</h2><p>Los usuarios pueden aceptar, rechazar o eliminar cookies a través de la configuración de su navegador. Deshabilitar algunas cookies puede afectar determinadas funcionalidades.</p><h2>5. Cambios en la Política de Cookies</h2><p>Burger Town puede actualizar esta política en cualquier momento para reflejar cambios en el uso de cookies o en la legislación aplicable.</p>', 1, '2026-10-04 23:53:36', '2026-10-05 00:59:06'),
(2, 'politica-de-privacidad', 'legal', 'Política de Privacidad', 'Política de Privacidad | Burger Town Bogotá', 'Conoce cómo Burger Town recopila, utiliza y protege la información personal.', 'Política de Privacidad', '<p>En Burger Town valoramos y respetamos la privacidad de nuestros usuarios y clientes. Esta Política de Privacidad describe cómo recopilamos, usamos y protegemos la información personal que usted proporciona al utilizar nuestro sitio web.</p><h2>1. Información que Recopilamos</h2><p>Podemos recopilar nombre y apellido, correo electrónico, teléfono, ubicación, información relacionada con pedidos e información de navegación.</p><h2>2. Uso de la Información</h2><p>La información puede utilizarse para procesar pedidos y servicios, mejorar la experiencia, enviar confirmaciones, responder solicitudes, comunicar novedades y analizar la navegación.</p><h2>3. Protección de la Información</h2><p>Burger Town implementa medidas técnicas y administrativas para proteger la información personal contra acceso no autorizado, pérdida o uso indebido.</p><h2>4. Compartir Información con Terceros</h2><p>Burger Town no vende ni comercializa información personal. Podrá compartirla únicamente con plataformas de pago, servicios de entrega, proveedores tecnológicos o autoridades cuando la ley lo requiera.</p><h2>5. Derechos del Titular</h2><p>De acuerdo con la Ley 1581 de 2012 en Colombia, los usuarios pueden conocer, actualizar o rectificar sus datos, solicitar prueba de autorización, solicitar eliminación cuando sea aplicable y revocar la autorización para el tratamiento.</p><h2>6. Conservación de los Datos</h2><p>La información se conservará durante el tiempo necesario para las finalidades descritas o durante el período exigido por la legislación aplicable.</p><h2>7. Cambios</h2><p>Burger Town se reserva el derecho de actualizar esta política. Las modificaciones serán publicadas en esta misma página.</p>', 1, '2026-10-04 23:53:36', '2026-10-04 23:53:36'),
(3, 'terminos-y-condiciones', 'legal', 'Términos y Condiciones', 'Términos y Condiciones | Burger Town Bogotá', 'Términos y condiciones de uso del sitio web de Burger Town.', 'Términos y Condiciones', '<p>Bienvenido al sitio web de Burger Town. Al acceder y utilizar este sitio web, usted acepta cumplir con los siguientes términos y condiciones de uso.</p><h2>1. Información General</h2><p>Este sitio web es operado por Burger Town. El uso del sitio y la compra de productos o servicios están sujetos a estos términos y a nuestra política de privacidad.</p><h2>2. Uso del Sitio Web</h2><ul><li>El usuario es mayor de edad o cuenta con autorización de un adulto responsable.</li><li>Utilizará el sitio únicamente con fines legales.</li><li>No realizará actividades que puedan afectar el funcionamiento del sitio.</li></ul><h2>3. Productos y Servicios</h2><p>Los productos pueden variar según disponibilidad, sede, temporada o promociones. Burger Town podrá modificar o descontinuar productos.</p><h2>4. Precios</h2><p>Los precios publicados están expresados en pesos colombianos e incluyen los impuestos aplicables salvo indicación contraria.</p><h2>5. Pedidos en Línea</h2><p>Los pedidos están sujetos a confirmación de disponibilidad y los tiempos pueden variar según sede, demanda y factores externos.</p><h2>6. Cancelaciones y Modificaciones</h2><p>Una vez confirmado un pedido, puede no ser posible modificarlo o cancelarlo si la preparación ya comenzó.</p><h2>7. Propiedad Intelectual</h2><p>Logotipos, fotografías, diseños, textos y la marca Burger Town son propiedad de Burger Town o se utilizan con autorización y están protegidos por las leyes aplicables.</p><h2>8. Responsabilidad</h2><p>Burger Town no se hace responsable por problemas técnicos, interrupciones del servicio o daños derivados del uso incorrecto del sitio.</p><h2>9. Enlaces a Terceros</h2><p>El sitio puede contener enlaces a plataformas externas. Burger Town no controla ni se responsabiliza por el contenido o políticas de esos sitios.</p><h2>10. Protección de Datos</h2><p>El tratamiento de datos personales se realiza conforme a la legislación colombiana de protección de datos.</p><h2>11. Modificaciones</h2><p>Burger Town podrá actualizar estos términos en cualquier momento. Los cambios tendrán efecto desde su publicación.</p>', 1, '2026-10-04 23:53:36', '2026-10-04 23:53:36'),
(4, 'contact', 'contact', 'Contacto', 'Contacto | Burger Town', 'Contáctate con Burger Town para reservas, sedes, eventos y atención al cliente.', 'Estamos para ayudarte', '', 1, '2026-10-04 23:53:37', '2026-10-04 23:53:37'),
(5, 'eventos-burger-town-bogota', 'event', 'Eventos Burger Town Bogotá', 'Eventos en Burger Town Bogotá | Grupos y Celebraciones', 'Solicita tu evento en Burger Town Bogotá y selecciona la sede que prefieras.', 'Celebra como se debe: con mesa lista y buen parche', '', 1, '2026-10-04 23:53:37', '2026-10-04 23:53:37');

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `system_settings`
--

CREATE TABLE `system_settings` (
  `id` int(10) UNSIGNED NOT NULL,
  `branch_id` int(10) UNSIGNED DEFAULT NULL,
  `setting_group` varchar(50) NOT NULL DEFAULT 'general',
  `setting_key` varchar(50) NOT NULL,
  `setting_value` text DEFAULT NULL,
  `value_type` enum('string','integer','boolean','json') DEFAULT 'string',
  `is_public` tinyint(1) DEFAULT 0
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Volcado de datos para la tabla `system_settings`
--

INSERT INTO `system_settings` (`id`, `branch_id`, `setting_group`, `setting_key`, `setting_value`, `value_type`, `is_public`) VALUES
(1, NULL, 'branding', 'platform_name', 'Hamburguesas, dogs y alitas', 'string', 1),
(2, NULL, 'branding', 'primary_color', '#000000', 'string', 1),
(3, NULL, 'company', 'legal_name', 'GAD AGENCY SAS', 'string', 0),
(4, NULL, 'company', 'tax_id', '901962964-0', 'string', 0),
(5, 1, 'branding', 'platform_name', 'YouSeeBog Admin - Chicó', 'string', 1),
(6, NULL, 'branding', 'secondary_color', '#c6111a', 'string', 1),
(7, NULL, 'branding', 'description', 'Plataforma de gestión empresarial Demo', 'string', 1),
(8, NULL, 'website', 'site_name', 'Hamburguesas, dogs y alitas', 'string', 1),
(9, NULL, 'website', 'site_description', 'Mi página web Demo', 'string', 1),
(10, NULL, 'website', 'site_font', '\'Plus Jakarta Sans\', sans-serif', 'string', 1),
(11, NULL, 'website', 'site_status', 'online', 'string', 1),
(12, NULL, 'website', 'social_facebook', '', 'string', 1),
(13, NULL, 'website', 'social_instagram', '', 'string', 1),
(14, NULL, 'website', 'social_tiktok', '', 'string', 1),
(15, NULL, 'company', 'address', 'Calle 100', 'string', 0),
(16, NULL, 'company', 'email', 'jcuadros@sanmateo.edu.co', 'string', 0),
(17, NULL, 'billing', 'currency_symbol', '$', 'string', 1),
(18, NULL, 'billing', 'currency_code', 'COP', 'string', 1),
(19, NULL, 'billing', 'decimals', '2', 'integer', 1),
(20, NULL, 'payments', 'wompi_mode', 'sandbox', 'string', 0),
(21, NULL, 'payments', 'wompi_status', '0', 'boolean', 0),
(22, NULL, 'payments', 'wompi_public_key', '', 'string', 0),
(23, NULL, 'payments', 'wompi_private_key', '', 'string', 0),
(24, NULL, 'payments', 'epayco_mode', 'sandbox', 'string', 0),
(25, NULL, 'payments', 'epayco_status', '0', 'boolean', 0),
(26, NULL, 'payments', 'epayco_public_key', '', 'string', 0),
(27, NULL, 'payments', 'epayco_p_key', '', 'string', 0),
(28, NULL, 'payments', 'payu_mode', 'sandbox', 'string', 0),
(29, NULL, 'payments', 'payu_status', '0', 'boolean', 0),
(30, NULL, 'payments', 'payu_merchant_id', '', 'string', 0),
(31, NULL, 'payments', 'payu_api_key', '', 'string', 0),
(32, NULL, 'payments', 'mp_mode', 'sandbox', 'string', 0),
(33, NULL, 'payments', 'mp_status', '0', 'boolean', 0),
(34, NULL, 'payments', 'mp_public_key', '', 'string', 0),
(35, NULL, 'payments', 'mp_access_token', '', 'string', 0),
(36, NULL, 'payments', 'stripe_mode', 'sandbox', 'string', 0),
(37, NULL, 'payments', 'stripe_status', '0', 'boolean', 0),
(38, NULL, 'payments', 'stripe_public_key', '', 'string', 0),
(39, NULL, 'payments', 'stripe_secret_key', '', 'string', 0),
(40, NULL, 'payments', 'openpay_mode', 'sandbox', 'string', 0),
(41, NULL, 'payments', 'openpay_status', '0', 'boolean', 0),
(42, NULL, 'payments', 'openpay_id', '', 'string', 0),
(43, NULL, 'payments', 'openpay_key', '', 'string', 0),
(44, NULL, 'payments', 'addi_mode', 'sandbox', 'string', 0),
(45, NULL, 'payments', 'addi_status', '0', 'boolean', 0),
(46, NULL, 'payments', 'addi_client_id', '', 'string', 0),
(47, NULL, 'security', 'max_attempts', '5', 'integer', 0),
(48, NULL, 'security', 'lockout_time', '15', 'integer', 0),
(49, NULL, 'security', 'session_timeout', '30', 'integer', 0),
(50, NULL, 'security', 'password_expiry', '90', 'integer', 0),
(51, NULL, 'security', 'force_logout', '0', 'boolean', 0),
(52, NULL, 'website', 'site_logo', 'site-logo-b9e4baf24c00ecdd.webp', 'string', 1),
(53, NULL, 'branding', 'logo', 'admin-logo-f36b84e40cbdaf38.webp', 'string', 1),
(54, NULL, 'branding', 'favicon', 'admin-favicon-adb828aedfdfea83.webp', 'string', 1),
(55, NULL, 'website', 'site_favicon', 'site-favicon-e33c56158a585f69.webp', 'string', 1),
(56, NULL, 'mail', 'enabled', '1', 'boolean', 0),
(57, NULL, 'mail', 'provider', 'gmail', 'string', 0),
(58, NULL, 'mail', 'host', 'smtp.gmail.com', 'string', 0),
(59, NULL, 'mail', 'port', '587', 'integer', 0),
(60, NULL, 'mail', 'encryption', 'tls', 'string', 0),
(61, NULL, 'mail', 'username', 'jhonatanstivcuadros@gmail.com', 'string', 0),
(62, NULL, 'mail', 'password', 'ywgb ilvt sxng tnoe', 'string', 0),
(63, NULL, 'mail', 'from_name', 'Burger Town', 'string', 0),
(64, NULL, 'mail', 'from_email', 'jhonatanstivcuadros@gmail.com', 'string', 0);

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `tb_newsletter_subscribers`
--

CREATE TABLE `tb_newsletter_subscribers` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `email` varchar(254) NOT NULL,
  `status` enum('active','unsubscribed') NOT NULL DEFAULT 'active',
  `subscribed_at` datetime NOT NULL DEFAULT current_timestamp(),
  `welcome_sent_at` datetime DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Volcado de datos para la tabla `tb_newsletter_subscribers`
--

INSERT INTO `tb_newsletter_subscribers` (`id`, `email`, `status`, `subscribed_at`, `welcome_sent_at`) VALUES
(2, 'jhonatanstivcuadros@gmail.com', 'active', '2026-10-12 16:30:24', '2026-10-12 16:30:26');

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `users`
--

CREATE TABLE `users` (
  `id` int(10) UNSIGNED NOT NULL,
  `branch_id` int(10) UNSIGNED DEFAULT NULL,
  `role_id` int(10) UNSIGNED NOT NULL,
  `first_name` varchar(60) NOT NULL,
  `last_name` varchar(60) NOT NULL,
  `email` varchar(120) NOT NULL,
  `phone` varchar(30) DEFAULT NULL,
  `password_hash` varchar(255) NOT NULL,
  `password_changed_at` datetime DEFAULT NULL,
  `document_type` enum('CC','CE','NIT','PP') DEFAULT 'CC',
  `document_number` varchar(30) DEFAULT NULL,
  `avatar` varchar(255) DEFAULT 'default.png',
  `is_active` tinyint(1) NOT NULL DEFAULT 1,
  `email_verified_at` timestamp NULL DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Volcado de datos para la tabla `users`
--

INSERT INTO `users` (`id`, `branch_id`, `role_id`, `first_name`, `last_name`, `email`, `phone`, `password_hash`, `password_changed_at`, `document_type`, `document_number`, `avatar`, `is_active`, `email_verified_at`, `created_at`, `updated_at`) VALUES
(1, NULL, 1, 'Carlos', 'Dev', 'carlos@velox.com', '+57 300 000 0000', '$2y$10$E9A0Rk7u2HjY3G5A2X2U3u.0YJ/p02I21QzK4S8w/9vX60Hq3S7Sm', '2026-09-07 10:27:24', 'CC', '1018223344', 'default.png', 1, '2026-09-07 15:02:04', '2026-09-07 15:02:04', '2026-09-21 14:15:42'),
(2, 1, 2, 'Admin', 'Sede Chicó', 'admin.chico@velox.com', '+57 310 111 2233', '$2y$10$d3MzXhIKz4F8YuUWPIwaien8iEC3ZqSroIAwoxmkCVyef1OvpYr7W', '2026-09-18 10:20:11', 'CC', '1020304050', 'user-2-a5b164f29056396a.png', 1, '2026-09-07 15:02:04', '2026-09-07 15:02:04', '2026-09-21 14:15:42'),
(3, 1, 3, 'David', 'Barber', 'david.staff@velox.com', '+57 320 444 5566', '$2y$10$e0MYzXyjpJS7Pd0RVvHwHe1X.wX9MAn6v3oN/Cq3gC3D0Xo1q3k6S', '2026-09-07 10:02:04', 'CC', '80123456', 'default.png', 1, '2026-09-07 15:02:04', '2026-09-07 15:02:04', '2026-09-21 14:15:42'),
(4, 1, 4, 'Ana', 'Gómez', 'ana.gomez@gmail.com', '+57 315 888 9900', '$2y$10$e0MYzXyjpJS7Pd0RVvHwHe1X.wX9MAn6v3oN/Cq3gC3D0Xo1q3k6S', '2026-09-07 10:02:04', 'CC', '52987654', 'default.png', 1, '2026-09-07 15:02:04', '2026-09-07 15:02:04', '2026-09-21 14:15:42'),
(5, NULL, 1, 'Jhonatan', 'Cuadros', 'jhonatanstivcuadros@gmail.com', '+57 3214519862', '$2y$10$fpIPGafuQFaLN4fVGAI8Cu.smeOlHcy3GH3MtvZ/lXcoozomtM33.', '2026-10-04 18:11:20', 'CC', '1104708866', 'default.png', 1, '2026-10-04 22:20:42', '2026-09-07 16:13:33', '2026-10-04 23:11:20'),
(6, NULL, 4, 'Felipe', 'Forero', 'felipe@velox.com', '', '$2y$10$hVLqiQZTx.iQ710vB8DMUu8oxOhkXSYtErgcdsv6L1JUVfb9ms7P2', '2026-09-12 16:44:45', 'CC', NULL, 'default.png', 1, NULL, '2026-09-12 21:44:45', '2026-09-21 14:15:42'),
(7, 1, 3, 'Prueba', 'Prueba', 'prueba@prueba.com', '3209636763', '$2y$10$GjET8ZE2Om0tq0TxSDeXYuME6rbuvwixSIjdunnTbBkC0Hvgg2vn.', '2026-10-15 17:50:37', 'CC', '1020304050', 'default.png', 1, NULL, '2026-10-15 22:50:37', '2026-10-15 22:50:37');

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `user_permissions`
--

CREATE TABLE `user_permissions` (
  `user_id` int(10) UNSIGNED NOT NULL,
  `permission_id` int(10) UNSIGNED NOT NULL,
  `is_allowed` tinyint(1) NOT NULL DEFAULT 1,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Volcado de datos para la tabla `user_permissions`
--

INSERT INTO `user_permissions` (`user_id`, `permission_id`, `is_allowed`, `created_at`, `updated_at`) VALUES
(1, 8, 1, '2026-10-05 03:18:04', '2026-10-05 03:18:04'),
(1, 9, 1, '2026-10-05 03:18:04', '2026-10-05 03:18:04'),
(1, 10, 1, '2026-10-05 03:18:04', '2026-10-05 03:18:04'),
(1, 11, 1, '2026-10-05 03:18:04', '2026-10-05 03:18:04'),
(1, 12, 1, '2026-10-05 03:18:04', '2026-10-05 03:18:04'),
(1, 13, 1, '2026-10-05 03:18:04', '2026-10-05 03:18:04'),
(1, 14, 1, '2026-10-05 03:18:04', '2026-10-05 03:18:04'),
(1, 15, 1, '2026-10-05 03:18:04', '2026-10-05 03:18:04'),
(1, 16, 1, '2026-10-05 03:18:04', '2026-10-05 03:18:04'),
(1, 17, 1, '2026-10-05 03:18:04', '2026-10-05 03:18:04'),
(1, 18, 1, '2026-10-05 03:18:04', '2026-10-05 03:18:04'),
(1, 19, 1, '2026-10-05 03:18:04', '2026-10-05 03:18:04'),
(1, 20, 1, '2026-10-05 03:18:04', '2026-10-05 03:18:04'),
(1, 23, 1, '2026-10-05 03:18:04', '2026-10-05 03:18:04'),
(1, 24, 1, '2026-10-05 03:18:04', '2026-10-05 03:18:04'),
(1, 25, 1, '2026-10-05 03:18:04', '2026-10-05 03:18:04'),
(1, 26, 1, '2026-10-05 03:18:04', '2026-10-05 03:18:04'),
(1, 27, 1, '2026-10-05 03:18:04', '2026-10-05 03:18:04'),
(1, 28, 1, '2026-10-05 03:18:04', '2026-10-05 03:18:04'),
(1, 29, 1, '2026-10-05 03:18:04', '2026-10-05 03:18:04'),
(1, 30, 1, '2026-10-05 03:18:04', '2026-10-05 03:18:04'),
(2, 8, 1, '2026-10-05 03:18:04', '2026-10-05 03:18:04'),
(2, 9, 1, '2026-10-05 03:18:04', '2026-10-05 03:18:04'),
(2, 10, 1, '2026-10-05 03:18:04', '2026-10-05 03:18:04'),
(2, 11, 1, '2026-10-05 03:18:04', '2026-10-05 03:18:04'),
(2, 12, 1, '2026-10-05 03:18:04', '2026-10-05 03:18:04'),
(2, 13, 1, '2026-10-05 03:18:04', '2026-10-05 03:18:04'),
(2, 14, 1, '2026-10-05 03:18:04', '2026-10-05 03:18:04'),
(2, 15, 1, '2026-10-05 03:18:04', '2026-10-05 03:18:04'),
(2, 16, 1, '2026-10-05 03:18:04', '2026-10-05 03:18:04'),
(2, 17, 1, '2026-10-05 03:18:04', '2026-10-05 03:18:04'),
(2, 18, 1, '2026-10-05 03:18:04', '2026-10-05 03:18:04'),
(2, 19, 1, '2026-10-05 03:18:04', '2026-10-05 03:18:04'),
(2, 20, 1, '2026-10-05 03:18:04', '2026-10-05 03:18:04'),
(2, 23, 1, '2026-10-05 03:18:04', '2026-10-05 03:18:04'),
(2, 24, 1, '2026-10-05 03:18:04', '2026-10-05 03:18:04'),
(2, 25, 1, '2026-10-05 03:18:04', '2026-10-05 03:18:04'),
(2, 26, 1, '2026-10-05 03:18:04', '2026-10-05 03:18:04'),
(2, 27, 1, '2026-10-05 03:18:04', '2026-10-05 03:18:04'),
(2, 28, 1, '2026-10-05 03:18:04', '2026-10-05 03:18:04'),
(2, 29, 1, '2026-10-05 03:18:04', '2026-10-05 03:18:04'),
(2, 30, 1, '2026-10-05 03:18:04', '2026-10-05 03:18:04'),
(3, 8, 1, '2026-10-05 03:18:04', '2026-10-05 03:18:04'),
(3, 9, 1, '2026-10-05 03:18:04', '2026-10-05 03:18:04'),
(3, 10, 1, '2026-10-05 03:18:04', '2026-10-05 03:18:04'),
(3, 11, 1, '2026-10-05 03:18:04', '2026-10-05 03:18:04'),
(3, 12, 1, '2026-10-05 03:18:04', '2026-10-05 03:18:04'),
(3, 13, 1, '2026-10-05 03:18:04', '2026-10-05 03:18:04'),
(3, 14, 1, '2026-10-05 03:18:04', '2026-10-05 03:18:04'),
(3, 15, 1, '2026-10-05 03:18:04', '2026-10-05 03:18:04'),
(3, 16, 1, '2026-10-05 03:18:04', '2026-10-05 03:18:04'),
(3, 17, 1, '2026-10-05 03:18:04', '2026-10-05 03:18:04'),
(3, 18, 1, '2026-10-05 03:18:04', '2026-10-05 03:18:04'),
(3, 19, 1, '2026-10-05 03:18:04', '2026-10-05 03:18:04'),
(3, 20, 1, '2026-10-05 03:18:04', '2026-10-05 03:18:04'),
(3, 23, 1, '2026-10-05 03:18:04', '2026-10-05 03:18:04'),
(3, 24, 1, '2026-10-05 03:18:04', '2026-10-05 03:18:04'),
(3, 25, 1, '2026-10-05 03:18:04', '2026-10-05 03:18:04'),
(3, 26, 1, '2026-10-05 03:18:04', '2026-10-05 03:18:04'),
(3, 27, 1, '2026-10-05 03:18:04', '2026-10-05 03:18:04'),
(3, 28, 1, '2026-10-05 03:18:04', '2026-10-05 03:18:04'),
(3, 29, 1, '2026-10-05 03:18:04', '2026-10-05 03:18:04'),
(3, 30, 1, '2026-10-05 03:18:04', '2026-10-05 03:18:04'),
(4, 8, 1, '2026-10-05 03:18:04', '2026-10-05 03:18:04'),
(4, 9, 1, '2026-10-05 03:18:04', '2026-10-05 03:18:04'),
(4, 10, 1, '2026-10-05 03:18:04', '2026-10-05 03:18:04'),
(4, 11, 1, '2026-10-05 03:18:04', '2026-10-05 03:18:04'),
(4, 12, 1, '2026-10-05 03:18:04', '2026-10-05 03:18:04'),
(4, 13, 1, '2026-10-05 03:18:04', '2026-10-05 03:18:04'),
(4, 14, 1, '2026-10-05 03:18:04', '2026-10-05 03:18:04'),
(4, 15, 1, '2026-10-05 03:18:04', '2026-10-05 03:18:04'),
(4, 16, 1, '2026-10-05 03:18:04', '2026-10-05 03:18:04'),
(4, 17, 1, '2026-10-05 03:18:04', '2026-10-05 03:18:04'),
(4, 18, 1, '2026-10-05 03:18:04', '2026-10-05 03:18:04'),
(4, 19, 1, '2026-10-05 03:18:04', '2026-10-05 03:18:04'),
(4, 20, 1, '2026-10-05 03:18:04', '2026-10-05 03:18:04'),
(4, 23, 1, '2026-10-05 03:18:04', '2026-10-05 03:18:04'),
(4, 24, 1, '2026-10-05 03:18:04', '2026-10-05 03:18:04'),
(4, 25, 1, '2026-10-05 03:18:04', '2026-10-05 03:18:04'),
(4, 26, 1, '2026-10-05 03:18:04', '2026-10-05 03:18:04'),
(4, 27, 1, '2026-10-05 03:18:04', '2026-10-05 03:18:04'),
(4, 28, 1, '2026-10-05 03:18:04', '2026-10-05 03:18:04'),
(4, 29, 1, '2026-10-05 03:18:04', '2026-10-05 03:18:04'),
(4, 30, 1, '2026-10-05 03:18:04', '2026-10-05 03:18:04'),
(5, 8, 1, '2026-10-05 03:17:56', '2026-10-05 03:17:56'),
(5, 9, 1, '2026-10-05 03:17:56', '2026-10-05 03:17:56'),
(5, 10, 1, '2026-10-05 03:17:56', '2026-10-05 03:17:56'),
(5, 11, 1, '2026-10-05 03:17:56', '2026-10-05 03:17:56'),
(5, 12, 1, '2026-10-05 03:17:56', '2026-10-05 03:17:56'),
(5, 13, 1, '2026-10-05 03:17:56', '2026-10-05 03:17:56'),
(5, 14, 1, '2026-10-05 03:17:56', '2026-10-05 03:17:56'),
(5, 15, 1, '2026-10-05 03:17:56', '2026-10-05 03:17:56'),
(5, 16, 1, '2026-10-05 03:17:56', '2026-10-05 03:17:56'),
(5, 17, 1, '2026-10-05 03:17:56', '2026-10-05 03:17:56'),
(5, 18, 1, '2026-10-05 03:17:56', '2026-10-05 03:17:56'),
(5, 19, 1, '2026-10-05 03:17:56', '2026-10-05 03:17:56'),
(5, 20, 1, '2026-10-05 03:17:56', '2026-10-05 03:17:56'),
(5, 21, 1, '2026-10-05 03:17:56', '2026-10-05 03:17:56'),
(5, 22, 1, '2026-10-05 03:17:56', '2026-10-05 03:17:56'),
(5, 23, 1, '2026-10-05 03:17:56', '2026-10-05 03:17:56'),
(5, 24, 1, '2026-10-05 03:17:56', '2026-10-05 03:17:56'),
(5, 25, 1, '2026-10-05 03:17:56', '2026-10-05 03:17:56'),
(5, 26, 1, '2026-10-05 03:17:56', '2026-10-05 03:17:56'),
(5, 27, 1, '2026-10-05 03:17:56', '2026-10-05 03:17:56'),
(5, 28, 1, '2026-10-05 03:17:56', '2026-10-05 03:17:56'),
(5, 29, 1, '2026-10-05 03:17:56', '2026-10-05 03:17:56'),
(5, 30, 1, '2026-10-05 03:17:56', '2026-10-05 03:17:56'),
(6, 8, 1, '2026-10-05 03:17:56', '2026-10-05 03:17:56'),
(6, 9, 1, '2026-10-05 03:17:56', '2026-10-05 03:17:56'),
(6, 10, 1, '2026-10-05 03:17:56', '2026-10-05 03:17:56'),
(6, 11, 1, '2026-10-05 03:17:56', '2026-10-05 03:17:56'),
(6, 12, 1, '2026-10-05 03:17:56', '2026-10-05 03:17:56'),
(6, 13, 1, '2026-10-05 03:17:56', '2026-10-05 03:17:56'),
(6, 14, 1, '2026-10-05 03:17:56', '2026-10-05 03:17:56'),
(6, 15, 1, '2026-10-05 03:17:56', '2026-10-05 03:17:56'),
(6, 16, 1, '2026-10-05 03:17:56', '2026-10-05 03:17:56'),
(6, 17, 1, '2026-10-05 03:17:56', '2026-10-05 03:17:56'),
(6, 18, 1, '2026-10-05 03:17:56', '2026-10-05 03:17:56'),
(6, 19, 1, '2026-10-05 03:17:56', '2026-10-05 03:17:56'),
(6, 20, 1, '2026-10-05 03:17:56', '2026-10-05 03:17:56'),
(6, 21, 1, '2026-10-05 03:17:56', '2026-10-05 03:17:56'),
(6, 22, 1, '2026-10-05 03:17:56', '2026-10-05 03:17:56'),
(6, 23, 1, '2026-10-05 03:17:56', '2026-10-05 03:17:56'),
(6, 24, 1, '2026-10-05 03:17:56', '2026-10-05 03:17:56'),
(6, 25, 1, '2026-10-05 03:17:56', '2026-10-05 03:17:56'),
(6, 26, 1, '2026-10-05 03:17:56', '2026-10-05 03:17:56'),
(6, 27, 1, '2026-10-05 03:17:56', '2026-10-05 03:17:56'),
(6, 28, 1, '2026-10-05 03:17:56', '2026-10-05 03:17:56'),
(6, 29, 1, '2026-10-05 03:17:56', '2026-10-05 03:17:56'),
(6, 30, 1, '2026-10-05 03:17:56', '2026-10-05 03:17:56');

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `user_tokens`
--

CREATE TABLE `user_tokens` (
  `id` int(10) UNSIGNED NOT NULL,
  `user_id` int(10) UNSIGNED NOT NULL,
  `token_hash` varchar(64) NOT NULL,
  `type` enum('remember_me','password_reset','api_access') NOT NULL,
  `expires_at` datetime NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Índices para tablas volcadas
--

--
-- Indices de la tabla `attributes`
--
ALTER TABLE `attributes`
  ADD PRIMARY KEY (`id`);

--
-- Indices de la tabla `auth_logs`
--
ALTER TABLE `auth_logs`
  ADD PRIMARY KEY (`id`),
  ADD KEY `fk_al_user` (`user_id`);

--
-- Indices de la tabla `auth_security`
--
ALTER TABLE `auth_security`
  ADD PRIMARY KEY (`user_id`);

--
-- Indices de la tabla `bookings`
--
ALTER TABLE `bookings`
  ADD PRIMARY KEY (`id`),
  ADD KEY `fk_bk_branch` (`branch_id`),
  ADD KEY `fk_bk_customer` (`customer_id`),
  ADD KEY `fk_bk_staff` (`staff_id`),
  ADD KEY `fk_bk_item` (`item_id`);

--
-- Indices de la tabla `branches`
--
ALTER TABLE `branches`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `code` (`code`);

--
-- Indices de la tabla `branch_business_hours`
--
ALTER TABLE `branch_business_hours`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `uk_branch_day` (`branch_id`,`day_of_week`),
  ADD KEY `idx_business_hours_branch` (`branch_id`);

--
-- Indices de la tabla `branch_closures`
--
ALTER TABLE `branch_closures`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_closures_branch_date` (`branch_id`,`closure_date`);

--
-- Indices de la tabla `branch_images`
--
ALTER TABLE `branch_images`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_branch_images_branch_order` (`branch_id`,`sort_order`),
  ADD KEY `idx_branch_images_branch_favorite` (`branch_id`,`is_favorite`);

--
-- Indices de la tabla `branch_users`
--
ALTER TABLE `branch_users`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `uk_branch_user` (`branch_id`,`user_id`),
  ADD KEY `idx_branch_users_branch` (`branch_id`),
  ADD KEY `idx_branch_users_user` (`user_id`);

--
-- Indices de la tabla `categories`
--
ALTER TABLE `categories`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `slug` (`slug`),
  ADD KEY `fk_categories_parent` (`parent_id`);

--
-- Indices de la tabla `category_pages`
--
ALTER TABLE `category_pages`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `uk_category_pages_category` (`category_id`),
  ADD UNIQUE KEY `uk_category_pages_slug` (`slug`);

--
-- Indices de la tabla `category_page_images`
--
ALTER TABLE `category_page_images`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_category_page_images_page_order` (`category_page_id`,`display_order`),
  ADD KEY `idx_category_page_images_page_favorite` (`category_page_id`,`is_favorite`);

--
-- Indices de la tabla `contact_messages`
--
ALTER TABLE `contact_messages`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_contact_status` (`status`),
  ADD KEY `idx_contact_created` (`created_at`);

--
-- Indices de la tabla `customers`
--
ALTER TABLE `customers`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_customers_email` (`email`),
  ADD KEY `idx_customers_phone` (`phone`),
  ADD KEY `idx_customers_document` (`document_number`);

--
-- Indices de la tabla `event_requests`
--
ALTER TABLE `event_requests`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_event_branch` (`branch_id`),
  ADD KEY `idx_event_status` (`status`),
  ADD KEY `idx_event_date` (`event_date`);

--
-- Indices de la tabla `inventory`
--
ALTER TABLE `inventory`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `uk_item_branch` (`item_id`,`branch_id`),
  ADD KEY `fk_inv_branch` (`branch_id`);

--
-- Indices de la tabla `items`
--
ALTER TABLE `items`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `sku` (`sku`),
  ADD UNIQUE KEY `slug` (`slug`),
  ADD KEY `fk_items_category` (`category_id`);

--
-- Indices de la tabla `item_attributes`
--
ALTER TABLE `item_attributes`
  ADD PRIMARY KEY (`item_id`,`attribute_id`),
  ADD KEY `fk_ia_attribute` (`attribute_id`);

--
-- Indices de la tabla `item_images`
--
ALTER TABLE `item_images`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_item_images_item_id` (`item_id`),
  ADD KEY `idx_item_images_order` (`item_id`,`display_order`),
  ADD KEY `idx_item_images_favorite` (`item_id`,`is_favorite`);

--
-- Indices de la tabla `item_price_options`
--
ALTER TABLE `item_price_options`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_item_price_options_item` (`item_id`);

--
-- Indices de la tabla `notifications`
--
ALTER TABLE `notifications`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_notifications_user` (`user_id`),
  ADD KEY `idx_notifications_branch` (`branch_id`),
  ADD KEY `idx_notifications_user_read` (`user_id`,`is_read`),
  ADD KEY `idx_notifications_created` (`created_at`),
  ADD KEY `idx_notifications_reference` (`reference_type`,`reference_id`);

--
-- Indices de la tabla `orders`
--
ALTER TABLE `orders`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `order_number` (`order_number`),
  ADD KEY `fk_ord_branch` (`branch_id`),
  ADD KEY `fk_ord_customer` (`customer_id`);

--
-- Indices de la tabla `order_items`
--
ALTER TABLE `order_items`
  ADD PRIMARY KEY (`id`),
  ADD KEY `fk_oi_order` (`order_id`),
  ADD KEY `fk_oi_item` (`item_id`);

--
-- Indices de la tabla `payments`
--
ALTER TABLE `payments`
  ADD PRIMARY KEY (`id`),
  ADD KEY `fk_pay_order` (`order_id`),
  ADD KEY `fk_pay_gateway` (`gateway_id`);

--
-- Indices de la tabla `payment_gateways`
--
ALTER TABLE `payment_gateways`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `code` (`code`);

--
-- Indices de la tabla `permissions`
--
ALTER TABLE `permissions`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `slug` (`slug`);

--
-- Indices de la tabla `reservation_settings`
--
ALTER TABLE `reservation_settings`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `uk_reservation_settings_branch` (`branch_id`);

--
-- Indices de la tabla `reservation_tables`
--
ALTER TABLE `reservation_tables`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `uk_reservation_table` (`reservation_id`,`table_id`),
  ADD KEY `idx_reservation_tables_table` (`table_id`);

--
-- Indices de la tabla `restaurant_category_rules`
--
ALTER TABLE `restaurant_category_rules`
  ADD PRIMARY KEY (`category_id`);

--
-- Indices de la tabla `restaurant_reservations`
--
ALTER TABLE `restaurant_reservations`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `uk_reservation_code` (`reservation_code`),
  ADD KEY `idx_reservations_branch_date` (`branch_id`,`reservation_date`),
  ADD KEY `idx_reservations_datetime` (`reservation_date`,`start_time`),
  ADD KEY `idx_reservations_customer` (`customer_id`),
  ADD KEY `idx_reservations_status` (`status`),
  ADD KEY `idx_reservations_created_by` (`created_by`);

--
-- Indices de la tabla `restaurant_tables`
--
ALTER TABLE `restaurant_tables`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `uk_table_branch_number` (`branch_id`,`table_number`),
  ADD KEY `idx_tables_branch` (`branch_id`),
  ADD KEY `idx_tables_status` (`status`);

--
-- Indices de la tabla `roles`
--
ALTER TABLE `roles`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `name` (`name`);

--
-- Indices de la tabla `role_permissions`
--
ALTER TABLE `role_permissions`
  ADD PRIMARY KEY (`role_id`,`permission_id`),
  ADD KEY `fk_rp_permission` (`permission_id`);

--
-- Indices de la tabla `schedules`
--
ALTER TABLE `schedules`
  ADD PRIMARY KEY (`id`),
  ADD KEY `fk_schedules_staff` (`staff_id`);

--
-- Indices de la tabla `site_pages`
--
ALTER TABLE `site_pages`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `uq_site_pages_slug` (`slug`),
  ADD KEY `idx_site_pages_type` (`page_type`),
  ADD KEY `idx_site_pages_published` (`is_published`);

--
-- Indices de la tabla `system_settings`
--
ALTER TABLE `system_settings`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `uk_branch_setting` (`branch_id`,`setting_key`);

--
-- Indices de la tabla `tb_newsletter_subscribers`
--
ALTER TABLE `tb_newsletter_subscribers`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `uq_newsletter_email` (`email`);

--
-- Indices de la tabla `users`
--
ALTER TABLE `users`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `email` (`email`),
  ADD KEY `fk_users_branch` (`branch_id`),
  ADD KEY `fk_users_role` (`role_id`);

--
-- Indices de la tabla `user_permissions`
--
ALTER TABLE `user_permissions`
  ADD PRIMARY KEY (`user_id`,`permission_id`),
  ADD KEY `idx_user_permissions_permission` (`permission_id`);

--
-- Indices de la tabla `user_tokens`
--
ALTER TABLE `user_tokens`
  ADD PRIMARY KEY (`id`),
  ADD KEY `fk_ut_user` (`user_id`);

--
-- AUTO_INCREMENT de las tablas volcadas
--

--
-- AUTO_INCREMENT de la tabla `attributes`
--
ALTER TABLE `attributes`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT de la tabla `auth_logs`
--
ALTER TABLE `auth_logs`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- AUTO_INCREMENT de la tabla `bookings`
--
ALTER TABLE `bookings`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT de la tabla `branches`
--
ALTER TABLE `branches`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=9;

--
-- AUTO_INCREMENT de la tabla `branch_business_hours`
--
ALTER TABLE `branch_business_hours`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=330;

--
-- AUTO_INCREMENT de la tabla `branch_closures`
--
ALTER TABLE `branch_closures`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT de la tabla `branch_images`
--
ALTER TABLE `branch_images`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=26;

--
-- AUTO_INCREMENT de la tabla `branch_users`
--
ALTER TABLE `branch_users`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=6;

--
-- AUTO_INCREMENT de la tabla `categories`
--
ALTER TABLE `categories`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=18;

--
-- AUTO_INCREMENT de la tabla `category_pages`
--
ALTER TABLE `category_pages`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT de la tabla `category_page_images`
--
ALTER TABLE `category_page_images`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=15;

--
-- AUTO_INCREMENT de la tabla `contact_messages`
--
ALTER TABLE `contact_messages`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT de la tabla `customers`
--
ALTER TABLE `customers`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;

--
-- AUTO_INCREMENT de la tabla `event_requests`
--
ALTER TABLE `event_requests`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT de la tabla `inventory`
--
ALTER TABLE `inventory`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT de la tabla `items`
--
ALTER TABLE `items`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=30;

--
-- AUTO_INCREMENT de la tabla `item_images`
--
ALTER TABLE `item_images`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=68;

--
-- AUTO_INCREMENT de la tabla `item_price_options`
--
ALTER TABLE `item_price_options`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=73;

--
-- AUTO_INCREMENT de la tabla `notifications`
--
ALTER TABLE `notifications`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=43;

--
-- AUTO_INCREMENT de la tabla `orders`
--
ALTER TABLE `orders`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=7;

--
-- AUTO_INCREMENT de la tabla `order_items`
--
ALTER TABLE `order_items`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=7;

--
-- AUTO_INCREMENT de la tabla `payments`
--
ALTER TABLE `payments`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT de la tabla `payment_gateways`
--
ALTER TABLE `payment_gateways`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT de la tabla `permissions`
--
ALTER TABLE `permissions`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=31;

--
-- AUTO_INCREMENT de la tabla `reservation_settings`
--
ALTER TABLE `reservation_settings`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=11;

--
-- AUTO_INCREMENT de la tabla `reservation_tables`
--
ALTER TABLE `reservation_tables`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=55;

--
-- AUTO_INCREMENT de la tabla `restaurant_reservations`
--
ALTER TABLE `restaurant_reservations`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=54;

--
-- AUTO_INCREMENT de la tabla `restaurant_tables`
--
ALTER TABLE `restaurant_tables`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=26;

--
-- AUTO_INCREMENT de la tabla `roles`
--
ALTER TABLE `roles`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;

--
-- AUTO_INCREMENT de la tabla `schedules`
--
ALTER TABLE `schedules`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=7;

--
-- AUTO_INCREMENT de la tabla `site_pages`
--
ALTER TABLE `site_pages`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=6;

--
-- AUTO_INCREMENT de la tabla `system_settings`
--
ALTER TABLE `system_settings`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=65;

--
-- AUTO_INCREMENT de la tabla `tb_newsletter_subscribers`
--
ALTER TABLE `tb_newsletter_subscribers`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- AUTO_INCREMENT de la tabla `users`
--
ALTER TABLE `users`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=8;

--
-- AUTO_INCREMENT de la tabla `user_tokens`
--
ALTER TABLE `user_tokens`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=21;

--
-- Restricciones para tablas volcadas
--

--
-- Filtros para la tabla `auth_logs`
--
ALTER TABLE `auth_logs`
  ADD CONSTRAINT `fk_al_user` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE SET NULL;

--
-- Filtros para la tabla `auth_security`
--
ALTER TABLE `auth_security`
  ADD CONSTRAINT `fk_auth_security_user` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE;

--
-- Filtros para la tabla `bookings`
--
ALTER TABLE `bookings`
  ADD CONSTRAINT `fk_bk_branch` FOREIGN KEY (`branch_id`) REFERENCES `branches` (`id`),
  ADD CONSTRAINT `fk_bk_customer` FOREIGN KEY (`customer_id`) REFERENCES `users` (`id`),
  ADD CONSTRAINT `fk_bk_item` FOREIGN KEY (`item_id`) REFERENCES `items` (`id`),
  ADD CONSTRAINT `fk_bk_staff` FOREIGN KEY (`staff_id`) REFERENCES `users` (`id`);

--
-- Filtros para la tabla `branch_business_hours`
--
ALTER TABLE `branch_business_hours`
  ADD CONSTRAINT `fk_business_hours_branch` FOREIGN KEY (`branch_id`) REFERENCES `branches` (`id`) ON DELETE CASCADE;

--
-- Filtros para la tabla `branch_closures`
--
ALTER TABLE `branch_closures`
  ADD CONSTRAINT `fk_closures_branch` FOREIGN KEY (`branch_id`) REFERENCES `branches` (`id`) ON DELETE CASCADE;

--
-- Filtros para la tabla `branch_images`
--
ALTER TABLE `branch_images`
  ADD CONSTRAINT `fk_branch_images_branch` FOREIGN KEY (`branch_id`) REFERENCES `branches` (`id`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Filtros para la tabla `branch_users`
--
ALTER TABLE `branch_users`
  ADD CONSTRAINT `fk_branch_users_branch` FOREIGN KEY (`branch_id`) REFERENCES `branches` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `fk_branch_users_user` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE;

--
-- Filtros para la tabla `categories`
--
ALTER TABLE `categories`
  ADD CONSTRAINT `fk_categories_parent` FOREIGN KEY (`parent_id`) REFERENCES `categories` (`id`) ON DELETE SET NULL;

--
-- Filtros para la tabla `category_pages`
--
ALTER TABLE `category_pages`
  ADD CONSTRAINT `fk_category_pages_category` FOREIGN KEY (`category_id`) REFERENCES `categories` (`id`) ON DELETE CASCADE;

--
-- Filtros para la tabla `category_page_images`
--
ALTER TABLE `category_page_images`
  ADD CONSTRAINT `fk_category_page_images_page` FOREIGN KEY (`category_page_id`) REFERENCES `category_pages` (`id`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Filtros para la tabla `event_requests`
--
ALTER TABLE `event_requests`
  ADD CONSTRAINT `fk_event_requests_branch` FOREIGN KEY (`branch_id`) REFERENCES `branches` (`id`);

--
-- Filtros para la tabla `inventory`
--
ALTER TABLE `inventory`
  ADD CONSTRAINT `fk_inv_branch` FOREIGN KEY (`branch_id`) REFERENCES `branches` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `fk_inv_item` FOREIGN KEY (`item_id`) REFERENCES `items` (`id`) ON DELETE CASCADE;

--
-- Filtros para la tabla `items`
--
ALTER TABLE `items`
  ADD CONSTRAINT `fk_items_category` FOREIGN KEY (`category_id`) REFERENCES `categories` (`id`) ON DELETE SET NULL;

--
-- Filtros para la tabla `item_attributes`
--
ALTER TABLE `item_attributes`
  ADD CONSTRAINT `fk_ia_attribute` FOREIGN KEY (`attribute_id`) REFERENCES `attributes` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `fk_ia_item` FOREIGN KEY (`item_id`) REFERENCES `items` (`id`) ON DELETE CASCADE;

--
-- Filtros para la tabla `item_images`
--
ALTER TABLE `item_images`
  ADD CONSTRAINT `fk_item_images_item` FOREIGN KEY (`item_id`) REFERENCES `items` (`id`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Filtros para la tabla `item_price_options`
--
ALTER TABLE `item_price_options`
  ADD CONSTRAINT `fk_item_price_options_item` FOREIGN KEY (`item_id`) REFERENCES `items` (`id`) ON DELETE CASCADE;

--
-- Filtros para la tabla `notifications`
--
ALTER TABLE `notifications`
  ADD CONSTRAINT `fk_notifications_branch` FOREIGN KEY (`branch_id`) REFERENCES `branches` (`id`) ON DELETE SET NULL,
  ADD CONSTRAINT `fk_notifications_user` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE;

--
-- Filtros para la tabla `orders`
--
ALTER TABLE `orders`
  ADD CONSTRAINT `fk_ord_branch` FOREIGN KEY (`branch_id`) REFERENCES `branches` (`id`),
  ADD CONSTRAINT `fk_ord_customer` FOREIGN KEY (`customer_id`) REFERENCES `users` (`id`) ON DELETE SET NULL;

--
-- Filtros para la tabla `order_items`
--
ALTER TABLE `order_items`
  ADD CONSTRAINT `fk_oi_item` FOREIGN KEY (`item_id`) REFERENCES `items` (`id`),
  ADD CONSTRAINT `fk_oi_order` FOREIGN KEY (`order_id`) REFERENCES `orders` (`id`) ON DELETE CASCADE;

--
-- Filtros para la tabla `payments`
--
ALTER TABLE `payments`
  ADD CONSTRAINT `fk_pay_gateway` FOREIGN KEY (`gateway_id`) REFERENCES `payment_gateways` (`id`),
  ADD CONSTRAINT `fk_pay_order` FOREIGN KEY (`order_id`) REFERENCES `orders` (`id`) ON DELETE CASCADE;

--
-- Filtros para la tabla `reservation_settings`
--
ALTER TABLE `reservation_settings`
  ADD CONSTRAINT `fk_reservation_settings_branch` FOREIGN KEY (`branch_id`) REFERENCES `branches` (`id`) ON DELETE CASCADE;

--
-- Filtros para la tabla `reservation_tables`
--
ALTER TABLE `reservation_tables`
  ADD CONSTRAINT `fk_reservation_tables_reservation` FOREIGN KEY (`reservation_id`) REFERENCES `restaurant_reservations` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `fk_reservation_tables_table` FOREIGN KEY (`table_id`) REFERENCES `restaurant_tables` (`id`);

--
-- Filtros para la tabla `restaurant_category_rules`
--
ALTER TABLE `restaurant_category_rules`
  ADD CONSTRAINT `fk_restaurant_category_rules_category` FOREIGN KEY (`category_id`) REFERENCES `categories` (`id`) ON DELETE CASCADE;

--
-- Filtros para la tabla `restaurant_reservations`
--
ALTER TABLE `restaurant_reservations`
  ADD CONSTRAINT `fk_reservations_branch` FOREIGN KEY (`branch_id`) REFERENCES `branches` (`id`),
  ADD CONSTRAINT `fk_reservations_created_by` FOREIGN KEY (`created_by`) REFERENCES `users` (`id`) ON DELETE SET NULL,
  ADD CONSTRAINT `fk_reservations_customer` FOREIGN KEY (`customer_id`) REFERENCES `customers` (`id`);

--
-- Filtros para la tabla `restaurant_tables`
--
ALTER TABLE `restaurant_tables`
  ADD CONSTRAINT `fk_tables_branch` FOREIGN KEY (`branch_id`) REFERENCES `branches` (`id`) ON DELETE CASCADE;

--
-- Filtros para la tabla `role_permissions`
--
ALTER TABLE `role_permissions`
  ADD CONSTRAINT `fk_rp_permission` FOREIGN KEY (`permission_id`) REFERENCES `permissions` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `fk_rp_role` FOREIGN KEY (`role_id`) REFERENCES `roles` (`id`) ON DELETE CASCADE;

--
-- Filtros para la tabla `schedules`
--
ALTER TABLE `schedules`
  ADD CONSTRAINT `fk_schedules_staff` FOREIGN KEY (`staff_id`) REFERENCES `users` (`id`) ON DELETE CASCADE;

--
-- Filtros para la tabla `system_settings`
--
ALTER TABLE `system_settings`
  ADD CONSTRAINT `fk_set_branch` FOREIGN KEY (`branch_id`) REFERENCES `branches` (`id`) ON DELETE CASCADE;

--
-- Filtros para la tabla `users`
--
ALTER TABLE `users`
  ADD CONSTRAINT `fk_users_branch` FOREIGN KEY (`branch_id`) REFERENCES `branches` (`id`) ON DELETE SET NULL,
  ADD CONSTRAINT `fk_users_role` FOREIGN KEY (`role_id`) REFERENCES `roles` (`id`);

--
-- Filtros para la tabla `user_permissions`
--
ALTER TABLE `user_permissions`
  ADD CONSTRAINT `fk_user_permissions_permission` FOREIGN KEY (`permission_id`) REFERENCES `permissions` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `fk_user_permissions_user` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE;

--
-- Filtros para la tabla `user_tokens`
--
ALTER TABLE `user_tokens`
  ADD CONSTRAINT `fk_ut_user` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
