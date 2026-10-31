-- Velox MVC - modalidad de cumplimiento de pedidos ecommerce
-- Ejecutar una sola vez después de 20261031_store_cart_coupons_payments.sql.

CREATE TABLE IF NOT EXISTS order_fulfillment_details (
    id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
    order_id INT UNSIGNED NOT NULL,
    fulfillment_type ENUM('pickup', 'delivery') NOT NULL DEFAULT 'pickup',
    delivery_address VARCHAR(255) NULL,
    delivery_neighborhood VARCHAR(120) NULL,
    delivery_city VARCHAR(120) NULL,
    delivery_reference VARCHAR(255) NULL,
    contact_phone VARCHAR(20) NULL,
    delivery_fee DECIMAL(12,2) NOT NULL DEFAULT 0.00,
    created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (id),
    UNIQUE KEY uq_order_fulfillment_order (order_id),
    KEY idx_order_fulfillment_type (fulfillment_type)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
