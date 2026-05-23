CREATE DATABASE IF NOT EXISTS aquasmile_clinic CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
USE aquasmile_clinic;

CREATE TABLE IF NOT EXISTS users (
  id INT AUTO_INCREMENT PRIMARY KEY,
  first_name VARCHAR(100) NOT NULL,
  last_name VARCHAR(100) NOT NULL,
  email VARCHAR(120) NOT NULL UNIQUE,
  phone VARCHAR(20) NULL,
  password_hash VARCHAR(255) NOT NULL,
  created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP
);

CREATE TABLE IF NOT EXISTS dentists (
  id INT AUTO_INCREMENT PRIMARY KEY,
  name VARCHAR(150) NOT NULL,
  specialization VARCHAR(100) NULL,
  credentials TEXT NULL,
  bio TEXT NULL,
  created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP
);

CREATE TABLE IF NOT EXISTS services (
  id INT AUTO_INCREMENT PRIMARY KEY,
  name VARCHAR(150) NOT NULL,
  description TEXT NULL,
  price DECIMAL(10,2) NOT NULL DEFAULT 0,
  category VARCHAR(50) NULL,
  daily_slots INT NOT NULL DEFAULT 0,
  created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP
);

CREATE TABLE IF NOT EXISTS products (
  id INT AUTO_INCREMENT PRIMARY KEY,
  name VARCHAR(150) NOT NULL,
  description TEXT NULL,
  price DECIMAL(10,2) NOT NULL DEFAULT 0,
  stock_quantity INT NOT NULL DEFAULT 0,
  created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP
);

CREATE TABLE IF NOT EXISTS appointments (
  id INT AUTO_INCREMENT PRIMARY KEY,
  user_id INT NOT NULL,
  dentist_id INT NOT NULL,
  service_id INT NOT NULL,
  appointment_date DATE NOT NULL,
  appointment_time TIME NOT NULL,
  notes TEXT NULL,
  status ENUM('pending','confirmed','completed','cancelled') NOT NULL DEFAULT 'pending',
  cancellation_reason TEXT NULL,
  cancelled_by ENUM('admin','user') NULL,
  created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP
);

CREATE TABLE IF NOT EXISTS cart_items (
  id INT AUTO_INCREMENT PRIMARY KEY,
  user_id INT NOT NULL,
  product_id INT NOT NULL,
  quantity INT NOT NULL DEFAULT 1,
  added_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP
);

CREATE TABLE IF NOT EXISTS orders (
  id INT AUTO_INCREMENT PRIMARY KEY,
  user_id INT NULL,
  customer_name VARCHAR(255) NULL,
  email VARCHAR(120) NULL,
  phone VARCHAR(20) NULL,
  address TEXT NULL,
  city VARCHAR(100) NULL,
  zip VARCHAR(20) NULL,
  notes TEXT NULL,
  payment_method VARCHAR(50) NULL,
  gcash_number VARCHAR(20) NULL,
  total_amount DECIMAL(10,2) NOT NULL DEFAULT 0,
  status ENUM('pending','completed','cancelled') NOT NULL DEFAULT 'pending',
  created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP
);

CREATE TABLE IF NOT EXISTS order_items (
  id INT AUTO_INCREMENT PRIMARY KEY,
  order_id INT NOT NULL,
  product_id INT NOT NULL,
  quantity INT NOT NULL DEFAULT 1,
  unit_price DECIMAL(10,2) NOT NULL DEFAULT 0,
  created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP
);

CREATE TABLE IF NOT EXISTS notifications (
  id INT AUTO_INCREMENT PRIMARY KEY,
  user_id INT NOT NULL,
  appointment_id INT NULL,
  message TEXT NOT NULL,
  is_read TINYINT(1) NOT NULL DEFAULT 0,
  created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP
);

INSERT IGNORE INTO dentists (id, name, specialization, credentials, bio) VALUES
(1, 'Dr. Sophia Reyes', 'General & Cosmetic Dentistry', 'DMD - 12 years experience', 'Smile transformations and preventive care.'),
(2, 'Dr. Marcus Tan', 'Orthodontics & Oral Surgery', 'DMD, MScD - 9 years experience', 'Complex cases with precision and care.'),
(3, 'Dr. Leila Varon', 'Pediatric & Family Dentistry', 'DMD, PedDent - 7 years experience', 'Warm care for families and younger patients.');

INSERT IGNORE INTO services (id, name, description, price, category) VALUES
(1, 'Dental Cleaning', 'Professional prophylaxis to remove plaque and tartar.', 800, 'Preventive'),
(2, 'Dental X-Ray', 'Digital X-rays for accurate diagnosis.', 450, 'Diagnostic'),
(3, 'Tooth Extraction', 'Safe removal of damaged or problematic teeth.', 1200, 'Restorative'),
(4, 'Teeth Whitening', 'Professional-grade whitening treatment.', 3500, 'Cosmetic'),
(5, 'Dental Braces Consult', 'Orthodontic evaluation and treatment planning.', 500, 'Orthodontic'),
(6, 'Root Canal Treatment', 'Precision endodontic therapy.', 6000, 'Restorative'),
(7, 'Dental Crown', 'Custom-fitted porcelain crowns.', 8000, 'Restorative'),
(8, 'Porcelain Veneers', 'Custom shells for aesthetic results.', 12000, 'Cosmetic'),
(9, 'Pediatric Check-Up', 'Gentle dental visits for children.', 600, 'Preventive');

INSERT IGNORE INTO products (id, name, description, price, stock_quantity) VALUES
(1, 'Sonic Pro Toothbrush', 'Rechargeable electric toothbrush with 3 modes.', 1299, 12),
(2, 'WhiteGlow Toothpaste', 'Enamel-strengthening whitening paste.', 299, 30),
(3, 'Silk Dental Floss', 'Natural silk floss with wax coating.', 189, 24),
(4, 'AquaFresh Mouthwash', 'Antibacterial alcohol-free rinse.', 349, 18),
(5, 'Teeth Whitening Strips', '14-day whitening kit.', 899, 16),
(6, 'Tongue Scraper Set', 'Stainless steel scrapers.', 249, 20),
(7, 'Sensitive Gum Gel', 'Soothing gel for gum sensitivity.', 399, 15),
(8, 'Natural Bamboo Brush Set', '4-pack biodegradable bamboo toothbrushes.', 549, 10);
