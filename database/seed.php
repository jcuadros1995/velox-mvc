<?php
// database/seed.php

/**
 * Destructive demo seeder.
 *
 * This script is intentionally disabled by default and must never be
 * exposed through the web server. Run only against a disposable database
 * after explicit opt-in from the deployment operator.
 */
if (PHP_SAPI !== 'cli') {
    http_response_code(404);
    exit("Not Found\n");
}

if (getenv('APP_ENV') === 'production') {
    fwrite(STDERR, "Refusing to run database seeder in production.\n");
    exit(1);
}

if (getenv('ALLOW_DESTRUCTIVE_SEED') !== 'yes') {
    fwrite(STDERR, "Seeder disabled. Set ALLOW_DESTRUCTIVE_SEED=yes only for a disposable development database.\n");
    exit(1);
}

require_once __DIR__ . '/../config/database.php';

try {
    $db = Database::getConnection();
    echo "Starting Velox MVC Database Seeder...\n";

    // Destructive seed is limited to isolated development/test databases.
    $db->exec("SET FOREIGN_KEY_CHECKS = 0;");
    $tables = ['payments', 'order_items', 'orders', 'bookings', 'schedules', 'inventory', 'item_attributes', 'items', 'categories', 'auth_logs', 'user_tokens', 'users', 'role_permissions', 'permissions', 'roles', 'system_settings', 'branches'];
    foreach ($tables as $table) {
        $db->exec("TRUNCATE TABLE `{$table}`;");
    }
    $db->exec("SET FOREIGN_KEY_CHECKS = 1;");
    echo "All tables truncated successfully.\n";

    $db->exec("INSERT INTO `roles` (`id`, `name`, `description`) VALUES
        (1, 'superadmin', 'Acceso total al sistema'),
        (2, 'admin', 'Administrador de sucursal'),
        (3, 'staff', 'Personal operario / Colaborador'),
        (4, 'customer', 'Cliente final');");
    echo "Roles seeded.\n";

    $db->exec("INSERT INTO `branches` (`id`, `code`, `name`, `phone`, `email`, `address`) VALUES
        (1, 'MAIN', 'Sede Central', '+57 300 000 0000', 'contacto@velox.com', 'Bogotá, Colombia');");
    echo "Branches seeded.\n";

    $password = getenv('SEED_ADMIN_PASSWORD') ?: '';
    $email = strtolower(trim((string) (getenv('SEED_ADMIN_EMAIL') ?: '')));
    if ($password === '' || strlen($password) < 14 || !filter_var($email, FILTER_VALIDATE_EMAIL)) {
        throw new RuntimeException('Set SEED_ADMIN_EMAIL and a unique SEED_ADMIN_PASSWORD of at least 14 characters.');
    }

    $adminPassword = password_hash($password, PASSWORD_DEFAULT);
    if ($adminPassword === false) {
        throw new RuntimeException('Could not hash the seed administrator password.');
    }

    $stmtUser = $db->prepare("INSERT INTO `users` (`branch_id`, `role_id`, `first_name`, `last_name`, `email`, `password_hash`, `is_active`) VALUES
        (1, 1, 'Admin', 'Velox', :email, :password, 1);");
    $stmtUser->execute(['email' => $email, 'password' => $adminPassword]);
    echo "SuperAdmin user seeded.\n";

    $db->exec("INSERT INTO `categories` (`id`, `name`, `slug`, `description`) VALUES
        (1, 'Servicios & Citas', 'servicios-citas', 'Servicios agendables'),
        (2, 'Productos Físicos', 'productos-fisicos', 'Artículos para venta directa');");
    echo "Categories seeded.\n";

    $db->exec("INSERT INTO `items` (`id`, `category_id`, `type`, `sku`, `name`, `slug`, `description`, `price`, `duration_minutes`) VALUES
        (1, 1, 'service', 'SRV-001', 'Consulta o Corte Premium', 'consulta-corte-premium', 'Servicio agendable de 45 minutos', 45000.00, 45),
        (2, 2, 'physical', 'PRD-001', 'Producto de Mantenimiento', 'producto-mantenimiento', 'Artículo físico para inventario', 25000.00, 0);");
    echo "Demo items seeded.\n";

    $db->exec("INSERT INTO `inventory` (`item_id`, `branch_id`, `stock_quantity`, `min_stock_alert`) VALUES
        (2, 1, 50, 5);");
    echo "Inventory seeded.\n";

    $stmtSchedule = $db->prepare("INSERT INTO `schedules` (`staff_id`, `day_of_week`, `start_time`, `end_time`) VALUES
        (1, 1, '08:00:00', '17:00:00'),
        (1, 2, '08:00:00', '17:00:00'),
        (1, 3, '08:00:00', '17:00:00'),
        (1, 4, '08:00:00', '17:00:00'),
        (1, 5, '08:00:00', '17:00:00');");
    $stmtSchedule->execute();

    echo "Database seeding completed successfully.\n";
} catch (Throwable $e) {
    // Always restore FK checks, even on failure.
    try {
        if (isset($db) && $db instanceof PDO) {
            $db->exec("SET FOREIGN_KEY_CHECKS = 1;");
        }
    } catch (Throwable $restoreError) {
        error_log('[SEED_FK_RESTORE] ' . $restoreError->getMessage());
    }
    fwrite(STDERR, "Seeder failed: " . $e->getMessage() . "\n");
    exit(1);
}
