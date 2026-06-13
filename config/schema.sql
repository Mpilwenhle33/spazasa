-- =====================================================

-- SpazaSa Database Schema

-- Run this file once to set up all tables

-- =====================================================

CREATE DATABASE IF NOT EXISTS spazasa CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;

USE spazasa;

-- =====================================================

-- USERS TABLE

-- =====================================================

CREATE TABLE IF NOT EXISTS users (

  id            INT AUTO_INCREMENT PRIMARY KEY,

  name          VARCHAR(120)  NOT NULL,

  email         VARCHAR(180)  NOT NULL UNIQUE,

  phone         VARCHAR(20)   DEFAULT NULL,

  password_hash VARCHAR(255)  NOT NULL,

  avatar        VARCHAR(255)  DEFAULT 'uploads/avatars/default.png',

  location      VARCHAR(120)  DEFAULT NULL,

  language_pref ENUM('english','isizulu','sesotho','afrikaans') DEFAULT 'english',

  is_active     TINYINT(1)    DEFAULT 1,

  created_at    DATETIME      DEFAULT CURRENT_TIMESTAMP,

  updated_at    DATETIME      DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP

) ENGINE=InnoDB;

-- =====================================================

-- PRODUCTS TABLE

-- =====================================================

CREATE TABLE IF NOT EXISTS products (

  id          INT AUTO_INCREMENT PRIMARY KEY,

  seller_id   INT           NOT NULL,

  title       VARCHAR(200)  NOT NULL,

  description TEXT          DEFAULT NULL,

  price       DECIMAL(10,2) NOT NULL,

  category    ENUM('women','men','kids','electronics','furniture','clothing','other') DEFAULT 'other',

  cond        ENUM('new','used') DEFAULT 'new',

  location    VARCHAR(120)  DEFAULT NULL,

  images      TEXT          DEFAULT NULL,   -- JSON array of image paths

  is_sold     TINYINT(1)    DEFAULT 0,

  is_active   TINYINT(1)    DEFAULT 1,

  views       INT           DEFAULT 0,

  created_at  DATETIME      DEFAULT CURRENT_TIMESTAMP,

  updated_at  DATETIME      DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,

  FOREIGN KEY (seller_id) REFERENCES users(id) ON DELETE CASCADE,

  INDEX idx_category (category),

  INDEX idx_cond (cond),

  INDEX idx_seller (seller_id),

  INDEX idx_active (is_active),

  FULLTEXT idx_search (title, description)

) ENGINE=InnoDB;

-- =====================================================

-- CART ITEMS TABLE

-- =====================================================

CREATE TABLE IF NOT EXISTS cart_items (

  id         INT AUTO_INCREMENT PRIMARY KEY,

  user_id    INT      NOT NULL,

  product_id INT      NOT NULL,

  added_at   DATETIME DEFAULT CURRENT_TIMESTAMP,

  FOREIGN KEY (user_id)    REFERENCES users(id)    ON DELETE CASCADE,

  FOREIGN KEY (product_id) REFERENCES products(id) ON DELETE CASCADE,

  UNIQUE KEY unique_cart (user_id, product_id)

) ENGINE=InnoDB;

-- =====================================================

-- ORDERS TABLE

-- =====================================================

CREATE TABLE IF NOT EXISTS orders (

  id             INT AUTO_INCREMENT PRIMARY KEY,

  buyer_id       INT           NOT NULL,

  product_id     INT           NOT NULL,

  seller_id      INT           NOT NULL,

  delivery_type  ENUM('free','standard','express') DEFAULT 'free',

  delivery_cost  DECIMAL(8,2)  DEFAULT 0.00,

  total_amount   DECIMAL(10,2) NOT NULL,

  status         ENUM('pending','confirmed','in_transit','delivered','cancelled') DEFAULT 'pending',

  created_at     DATETIME      DEFAULT CURRENT_TIMESTAMP,

  updated_at     DATETIME      DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,

  FOREIGN KEY (buyer_id)   REFERENCES users(id)    ON DELETE CASCADE,

  FOREIGN KEY (product_id) REFERENCES products(id) ON DELETE CASCADE,

  FOREIGN KEY (seller_id)  REFERENCES users(id)    ON DELETE CASCADE,

  INDEX idx_buyer  (buyer_id),

  INDEX idx_seller (seller_id),

  INDEX idx_status (status)

) ENGINE=InnoDB;

-- =====================================================

-- MESSAGES TABLE

-- =====================================================

CREATE TABLE IF NOT EXISTS messages (

  id          INT AUTO_INCREMENT PRIMARY KEY,

  sender_id   INT  NOT NULL,

  receiver_id INT  NOT NULL,

  product_id  INT  DEFAULT NULL,

  message     TEXT NOT NULL,

  is_read     TINYINT(1) DEFAULT 0,

  sent_at     DATETIME   DEFAULT CURRENT_TIMESTAMP,

  FOREIGN KEY (sender_id)   REFERENCES users(id)    ON DELETE CASCADE,

  FOREIGN KEY (receiver_id) REFERENCES users(id)    ON DELETE CASCADE,

  FOREIGN KEY (product_id)  REFERENCES products(id) ON DELETE SET NULL,

  INDEX idx_sender   (sender_id),

  INDEX idx_receiver (receiver_id),

  INDEX idx_product  (product_id)

) ENGINE=InnoDB;

-- =====================================================

-- DELIVERY TRACKING TABLE

-- =====================================================

CREATE TABLE IF NOT EXISTS delivery_tracking (

  id               INT AUTO_INCREMENT PRIMARY KEY,

  order_id         INT          NOT NULL,

  status           ENUM('order_placed','seller_confirmed','in_transit','delivered') DEFAULT 'order_placed',

  driver_name      VARCHAR(120) DEFAULT NULL,

  driver_phone     VARCHAR(20)  DEFAULT NULL,

  driver_lat       DECIMAL(10,8) DEFAULT NULL,

  driver_lng       DECIMAL(11,8) DEFAULT NULL,

  destination_lat  DECIMAL(10,8) DEFAULT NULL,

  destination_lng  DECIMAL(11,8) DEFAULT NULL,

  eta_minutes      INT          DEFAULT NULL,

  distance_km      DECIMAL(6,2) DEFAULT NULL,

  updated_at       DATETIME     DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,

  FOREIGN KEY (order_id) REFERENCES orders(id) ON DELETE CASCADE,

  INDEX idx_order (order_id)

) ENGINE=InnoDB;

-- =====================================================

-- SEED: demo user (password = "demo1234")

-- =====================================================

INSERT IGNORE INTO users (id, name, email, phone, password_hash, location, language_pref)

VALUES (

  1,

  'Demo User',

  'demo@spazasa.co.za',

  '0821234567',

  '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', -- demo1234

  'Midrand',

  'english'

);