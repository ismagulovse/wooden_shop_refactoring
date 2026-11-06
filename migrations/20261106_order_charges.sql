CREATE TABLE IF NOT EXISTS order_charges (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    order_id INT UNSIGNED NOT NULL,
    code VARCHAR(40) NOT NULL,
    label VARCHAR(255) NOT NULL,
    amount DECIMAL(12,2) NOT NULL,
    CONSTRAINT fk_order_charges_order FOREIGN KEY (order_id) REFERENCES orders(id),
    INDEX idx_order_charges_order (order_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
