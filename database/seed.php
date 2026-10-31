<?php
// database/seed.php

require_once __DIR__ . '/../config/database.php';

try {
    $db = Database::getConnection();
    echo "Starting Velox MVC Database Seeder...\n";

    // Desactivar temporalmente revisión de llaves foráneas para limpiar
    $db->exec("SET FOREIGN_KEY_CHECKS = 0;");

    // 1. Limpieza de Tablas Principales
    $tables = ['payments', 'order_items', 'orders', 'bookings', 'schedules', 'inventory', 'item_attributes', 'items', 'categories', 'auth_logs', 'user_tokens', 'users', 'role_permissions', 'permissions', 'roles', 'system_settings', 'branches'];
    foreach ($tables as $table) {
        $db->exec("TRUNCATE TABLE `{$table}`;");
    }
    $db->exec("SET FOREIGN_KEY_CHECKS = 1;");
    echo "All tables truncated successfully.\n";

    // 2. Insertar Roles
    $db->exec("INSERT INTO `roles` (`id`, `name`, `description`) VALUES
        (1, 'superadmin', 'Acceso total al sistema'),
        (2, 'admin', 'Administrador de sucursal'),
        (3, 'staff', 'Personal operario / Colaborador'),
        (4, 'customer', 'Cliente final');");
    echo "Roles seeded.\n";

    // 3. Insertar Sucursal Principal
    $db->exec("INSERT INTO `branches` (`id`, `code`, `name`, `phone`, `email`, `address`) VALUES
        (1, 'MAIN', 'Sede Central', '+57 300 000 0000', 'contacto@velox.com', 'Bogotá, Colombia');");
    echo "Branches seeded.\n";

    // 4. Insertar Usuario SuperAdmin
    // Contraseña por defecto: Admin123!
    $adminPassword = password_hash('Admin123!', PASSWORD_BCRYPT);
    $stmtUser = $db->prepare("INSERT INTO `users` (`branch_id`, `role_id`, `first_name`, `last_name`, `email`, `password_hash`, `is_active`) VALUES
        (1, 1, 'Admin', 'Velox', 'admin@velox.com', :password, 1);");
    $stmtUser->execute(['password' => $adminPassword]);
    echo "SuperAdmin user seeded (admin@velox.com / Admin123!).\n";

    // 5. Insertar Categorías
    $db->exec("INSERT INTO `categories` (`id`, `name`, `slug`, `description`) VALUES
        (1, 'Servicios & Citas', 'servicios-citas', 'Servicios agendables'),
        (2, 'Productos Físicos', 'productos-fisicos', 'Artículos para venta directa');");
    echo "Categories seeded.\n";

    // 6. Insertar Ítems Demo (Servicio + Producto)
    $db->exec("INSERT INTO `items` (`id`, `category_id`, `type`, `sku`, `name`, `slug`, `description`, `price`, `duration_minutes`) VALUES
        (1, 1, 'service', 'SRV-001', 'Consulta o Corte Premium', 'consulta-corte-premium', 'Servicio agendable de 45 minutos', 45000.00, 45),
        (2, 2, 'physical', 'PRD-001', 'Producto de Mantenimiento', 'producto-mantenimiento', 'Artículo físico para inventario', 25000.00, 0);");
    echo "Demo Items seeded.\n";

    // 7. Insertar Inventario Inicial
    $db->exec("INSERT INTO `inventory` (`item_id`, `branch_id`, `stock_quantity`, `min_stock_alert`) VALUES
        (2, 1, 50, 5);");
    echo "Inventory seeded.\n";

    // 8. Insertar Horario Laboral para el Admin (Días 1 a 5: Lunes a Viernes)
    $stmtSchedule = $db->prepare("INSERT INTO `schedules` (`staff_id`, `day_of_week`, `start_time`, `end_time`) VALUES
        (1, 1, '08:00:00', '17:00:00'),
        (1, 2, '08:00:00', '17:00:00'),
        (1, 3, '08:00:00', '17:00:00'),
        (1, 4, '08:00:00', '17:00:00'),
        (1, 5, '08:00:00', '17:00:00');");
    $stmtSchedule->execute();
    echo "Schedules seeded.\n";

    echo "\nDatabase Seeding Completed Successfully!\n";

} catch (Exception $e) {
    die("Error seeding database: " . $e->getMessage() . "\n");
}