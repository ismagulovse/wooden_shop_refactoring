CREATE DATABASE IF NOT EXISTS wooden_shop CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
USE wooden_shop;

SET FOREIGN_KEY_CHECKS = 0;
DROP TABLE IF EXISTS order_charges;
DROP TABLE IF EXISTS order_items;
DROP TABLE IF EXISTS orders;
DROP TABLE IF EXISTS cart_items;
DROP TABLE IF EXISTS carts;
DROP TABLE IF EXISTS favorites;
DROP TABLE IF EXISTS product_images;
DROP TABLE IF EXISTS products;
DROP TABLE IF EXISTS categories;
DROP TABLE IF EXISTS users;
SET FOREIGN_KEY_CHECKS = 1;

CREATE TABLE users (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    first_name VARCHAR(100) NOT NULL,
    last_name VARCHAR(100) NOT NULL,
    email VARCHAR(190) NOT NULL UNIQUE,
    phone VARCHAR(30) NOT NULL,
    password_hash VARCHAR(255) NOT NULL,
    role ENUM('admin','customer') NOT NULL DEFAULT 'customer',
    status ENUM('active','blocked') NOT NULL DEFAULT 'active',
    created_at DATETIME NOT NULL,
    updated_at DATETIME NOT NULL,
    deleted_at DATETIME NULL,
    INDEX idx_users_status_deleted (status, deleted_at)
) ENGINE=InnoDB;

CREATE TABLE categories (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(150) NOT NULL,
    slug VARCHAR(170) NOT NULL UNIQUE,
    description TEXT NULL,
    created_at DATETIME NOT NULL,
    updated_at DATETIME NOT NULL,
    deleted_at DATETIME NULL,
    INDEX idx_categories_deleted (deleted_at)
) ENGINE=InnoDB;

CREATE TABLE products (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    category_id INT UNSIGNED NOT NULL,
    name VARCHAR(200) NOT NULL,
    slug VARCHAR(220) NOT NULL UNIQUE,
    sku VARCHAR(80) NOT NULL UNIQUE,
    price DECIMAL(10,2) NOT NULL,
    old_price DECIMAL(10,2) NULL,
    short_description VARCHAR(500) NULL,
    full_description TEXT NULL,
    material VARCHAR(120) NULL,
    color VARCHAR(120) NULL,
    dimensions VARCHAR(120) NULL,
    stock INT NOT NULL DEFAULT 0,
    is_active TINYINT(1) NOT NULL DEFAULT 1,
    is_featured TINYINT(1) NOT NULL DEFAULT 0,
    created_at DATETIME NOT NULL,
    updated_at DATETIME NOT NULL,
    deleted_at DATETIME NULL,
    CONSTRAINT fk_products_category FOREIGN KEY (category_id) REFERENCES categories(id),
    INDEX idx_products_deleted_active (deleted_at, is_active),
    INDEX idx_products_category (category_id)
) ENGINE=InnoDB;

CREATE TABLE product_images (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    product_id INT UNSIGNED NOT NULL,
    image_path VARCHAR(255) NOT NULL,
    sort_order INT NOT NULL DEFAULT 1,
    created_at DATETIME NOT NULL,
    deleted_at DATETIME NULL,
    CONSTRAINT fk_product_images_product FOREIGN KEY (product_id) REFERENCES products(id),
    INDEX idx_product_images_product_deleted (product_id, deleted_at)
) ENGINE=InnoDB;

CREATE TABLE favorites (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    user_id INT UNSIGNED NOT NULL,
    product_id INT UNSIGNED NOT NULL,
    created_at DATETIME NOT NULL,
    CONSTRAINT fk_favorites_user FOREIGN KEY (user_id) REFERENCES users(id),
    CONSTRAINT fk_favorites_product FOREIGN KEY (product_id) REFERENCES products(id),
    UNIQUE KEY uniq_favorites_user_product (user_id, product_id)
) ENGINE=InnoDB;

CREATE TABLE carts (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    user_id INT UNSIGNED NOT NULL UNIQUE,
    created_at DATETIME NOT NULL,
    updated_at DATETIME NOT NULL,
    CONSTRAINT fk_carts_user FOREIGN KEY (user_id) REFERENCES users(id)
) ENGINE=InnoDB;

CREATE TABLE cart_items (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    cart_id INT UNSIGNED NOT NULL,
    product_id INT UNSIGNED NOT NULL,
    quantity INT NOT NULL DEFAULT 1,
    created_at DATETIME NOT NULL,
    updated_at DATETIME NOT NULL,
    CONSTRAINT fk_cart_items_cart FOREIGN KEY (cart_id) REFERENCES carts(id) ON DELETE CASCADE,
    CONSTRAINT fk_cart_items_product FOREIGN KEY (product_id) REFERENCES products(id),
    UNIQUE KEY uniq_cart_item (cart_id, product_id)
) ENGINE=InnoDB;

CREATE TABLE orders (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    user_id INT UNSIGNED NOT NULL,
    status ENUM('new','processing','confirmed','shipped','delivered','cancelled') NOT NULL DEFAULT 'new',
    customer_name VARCHAR(190) NOT NULL,
    customer_phone VARCHAR(40) NOT NULL,
    customer_email VARCHAR(190) NOT NULL,
    delivery_address TEXT NOT NULL,
    comment TEXT NULL,
    payment_method VARCHAR(80) NOT NULL,
    delivery_method VARCHAR(80) NOT NULL,
    total_amount DECIMAL(12,2) NOT NULL,
    created_at DATETIME NOT NULL,
    updated_at DATETIME NOT NULL,
    deleted_at DATETIME NULL,
    CONSTRAINT fk_orders_user FOREIGN KEY (user_id) REFERENCES users(id),
    INDEX idx_orders_status_deleted (status, deleted_at)
) ENGINE=InnoDB;

CREATE TABLE order_items (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    order_id INT UNSIGNED NOT NULL,
    product_id INT UNSIGNED NOT NULL,
    product_name VARCHAR(220) NOT NULL,
    price DECIMAL(10,2) NOT NULL,
    quantity INT NOT NULL,
    total DECIMAL(12,2) NOT NULL,
    created_at DATETIME NOT NULL,
    CONSTRAINT fk_order_items_order FOREIGN KEY (order_id) REFERENCES orders(id) ON DELETE CASCADE,
    CONSTRAINT fk_order_items_product FOREIGN KEY (product_id) REFERENCES products(id)
) ENGINE=InnoDB;

CREATE TABLE order_charges (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    order_id INT UNSIGNED NOT NULL,
    code VARCHAR(40) NOT NULL,
    label VARCHAR(255) NOT NULL,
    amount DECIMAL(12,2) NOT NULL,
    CONSTRAINT fk_order_charges_order FOREIGN KEY (order_id) REFERENCES orders(id),
    INDEX idx_order_charges_order (order_id)
) ENGINE=InnoDB;

INSERT INTO users (first_name, last_name, email, phone, password_hash, role, status, created_at, updated_at, deleted_at)
VALUES
('Admin', 'Wooden', 'admin@wooden.local', '+79000000000', '$2y$12$OE.eLx1QP.hYnz/rBME84uVyefnpCiiZ1nhALBJSgVR9eYc//H5RS', 'admin', 'active', NOW(), NOW(), NULL),
('Ivan', 'Petrov', 'ivan@example.com', '+79001234567', '$2y$12$OE.eLx1QP.hYnz/rBME84uVyefnpCiiZ1nhALBJSgVR9eYc//H5RS', 'customer', 'active', NOW(), NOW(), NULL);

INSERT INTO categories (name, slug, description, created_at, updated_at, deleted_at)
VALUES
('Столы', 'stoly', 'Обеденные, журнальные и рабочие столы из массива.', NOW(), NOW(), NULL),
('Стулья', 'stulya', 'Комфортные деревянные стулья для дома и офиса.', NOW(), NOW(), NULL),
('Шкафы', 'shkafy', 'Шкафы с натуральной текстурой дерева.', NOW(), NOW(), NULL),
('Кровати', 'krovati', 'Надежные и эстетичные кровати из дерева.', NOW(), NOW(), NULL);

INSERT INTO products (category_id, name, slug, sku, price, old_price, short_description, full_description, material, color, dimensions, stock, is_active, is_featured, created_at, updated_at, deleted_at)
VALUES
(1, 'Обеденный стол Oak Prime', 'obedennyj-stol-oak-prime', 'WOOD-TBL-001', 45990.00, 51990.00, 'Стол из дуба для 6 персон.', 'Прочный стол из массива дуба с защитным покрытием. Подходит для кухни и гостиной.', 'Дуб', 'Натуральный', '160x90x75 см', 8, 1, 1, NOW(), NOW(), NULL),
(2, 'Стул Ash Comfort', 'stul-ash-comfort', 'WOOD-CHR-001', 10990.00, 12990.00, 'Эргономичный стул из ясеня.', 'Удобная посадка, усиленная конструкция, мягкое сиденье и износостойкая обивка.', 'Ясень', 'Светлый орех', '48x54x84 см', 20, 1, 1, NOW(), NOW(), NULL),
(3, 'Шкаф Pine Line', 'shkaf-pine-line', 'WOOD-WDR-001', 62990.00, NULL, 'Вместительный шкаф из сосны.', 'Трехстворчатый шкаф с надежной фурнитурой и экологичной отделкой.', 'Сосна', 'Медовый', '180x60x220 см', 5, 1, 0, NOW(), NOW(), NULL),
(4, 'Кровать Walnut Sleep', 'krovat-walnut-sleep', 'WOOD-BED-001', 78990.00, 84990.00, 'Двуспальная кровать из ореха.', 'Каркас из массива ореха с усиленным основанием и лаконичным современным дизайном.', 'Орех', 'Темный орех', '200x160x110 см', 4, 1, 1, NOW(), NOW(), NULL);

INSERT INTO product_images (product_id, image_path, sort_order, created_at, deleted_at)
VALUES
(1, '/assets/images/placeholder.svg', 1, NOW(), NULL),
(2, '/assets/images/placeholder.svg', 1, NOW(), NULL),
(3, '/assets/images/placeholder.svg', 1, NOW(), NULL),
(4, '/assets/images/placeholder.svg', 1, NOW(), NULL);
