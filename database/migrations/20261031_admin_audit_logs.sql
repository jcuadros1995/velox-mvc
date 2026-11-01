-- Velox MVC - Auditoría de actividad administrativa
-- Ejecutar una sola vez en la base de datos velox_core.

CREATE TABLE IF NOT EXISTS admin_audit_logs (
    id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
    user_id INT UNSIGNED NULL,
    user_name VARCHAR(160) NOT NULL,
    user_email VARCHAR(190) NULL,
    user_role VARCHAR(60) NULL,
    branch_id INT UNSIGNED NULL,
    module VARCHAR(100) NOT NULL,
    action VARCHAR(160) NOT NULL,
    http_method VARCHAR(10) NOT NULL,
    route VARCHAR(255) NOT NULL,
    ip_address VARCHAR(45) NULL,
    user_agent VARCHAR(500) NULL,
    response_status SMALLINT UNSIGNED NOT NULL DEFAULT 200,
    created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (id),
    KEY idx_audit_created (created_at),
    KEY idx_audit_user_date (user_id, created_at),
    KEY idx_audit_module_action (module, action),
    KEY idx_audit_status_date (response_status, created_at),
    KEY idx_audit_branch_date (branch_id, created_at)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
