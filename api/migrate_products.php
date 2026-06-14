<?php
require_once __DIR__ . '/../config/database.php';

$sql = "CREATE TABLE IF NOT EXISTS products_232025 (
  id INT AUTO_INCREMENT PRIMARY KEY,
  user_identifier VARCHAR(255) NOT NULL,
  name VARCHAR(255) NOT NULL,
  price DOUBLE NOT NULL,
  original_price DOUBLE DEFAULT NULL,
  discount_percent INT DEFAULT NULL,
  rating DOUBLE DEFAULT 0,
  sold_count VARCHAR(50) DEFAULT '0',
  location VARCHAR(100) DEFAULT '',
  store_name VARCHAR(100) DEFAULT NULL,
  is_mall TINYINT DEFAULT 0,
  is_bisa_cod TINYINT DEFAULT 0,
  bonus_text VARCHAR(255) DEFAULT NULL,
  is_gratis_ongkir TINYINT DEFAULT 0,
  is_pink_price TINYINT DEFAULT 0,
  is_lokal TINYINT DEFAULT 0,
  image_path VARCHAR(500) NOT NULL,
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
)";

$pdo->exec($sql);
echo "OK";

$dir = __DIR__ . '/../uploads/products';
if (!is_dir($dir)) {
    mkdir($dir, 0777, true);
    echo " + uploads dir";
}
echo "\n";
