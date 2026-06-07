-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: Jun 03, 2026 at 01:55 AM
-- Server version: 10.4.32-MariaDB
-- PHP Version: 8.2.12

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Database: `aquasmile_clinic`
--

-- --------------------------------------------------------

--
-- Table structure for table `appointments`
--

CREATE TABLE `appointments` (
  `id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `dentist_id` int(11) DEFAULT NULL,
  `service_id` int(11) DEFAULT NULL,
  `appointment_date` date NOT NULL,
  `appointment_time` time NOT NULL,
  `notes` text DEFAULT NULL,
  `status` enum('pending','confirmed','completed','cancelled') DEFAULT 'pending',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `cancellation_reason` text DEFAULT NULL,
  `cancelled_by` enum('admin','user') DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `appointments`
--

INSERT INTO `appointments` (`id`, `user_id`, `dentist_id`, `service_id`, `appointment_date`, `appointment_time`, `notes`, `status`, `created_at`, `cancellation_reason`, `cancelled_by`) VALUES
(6, 3, 1, 5, '2026-05-23', '08:00:00', '', 'confirmed', '2026-05-23 02:54:45', NULL, NULL),
(10, 4, 1, 2, '2026-05-26', '15:00:00', '', 'confirmed', '2026-05-24 03:34:22', NULL, NULL),
(11, 4, 1, 5, '2026-05-26', '15:00:00', '', 'cancelled', '2026-05-24 03:37:23', 'due to conflict schedule', 'admin'),
(12, 4, 1, 8, '2026-05-28', '15:00:00', '', 'cancelled', '2026-05-24 03:41:56', 'Cancelled by patient before admin approval.', 'user'),
(13, 5, 2, 1, '2026-05-26', '13:00:00', '', 'cancelled', '2026-05-24 04:13:08', 'Cancelled by patient before admin approval.', 'user'),
(14, 5, 2, 1, '2026-05-26', '13:00:00', '', 'cancelled', '2026-05-24 04:13:08', 'Cancelled by patient before admin approval.', 'user'),
(15, 5, 2, 6, '2026-05-26', '11:00:00', '', 'cancelled', '2026-05-24 04:19:14', 'Cancelled by patient before admin approval.', 'user'),
(16, 5, 2, 2, '2026-05-27', '15:00:00', 'i have pekdsjh', 'completed', '2026-05-24 04:20:07', NULL, NULL),
(17, 5, 3, 2, '2026-05-25', '08:00:00', '', 'confirmed', '2026-05-24 04:49:04', NULL, NULL),
(18, 5, 3, 2, '2026-05-27', '15:00:00', 'peanuts\n', 'cancelled', '2026-05-26 07:17:11', 'Cancelled by patient before admin approval.', 'user'),
(19, 6, 2, 8, '2026-05-26', '15:00:00', '', 'cancelled', '2026-05-26 08:21:57', 'Cancelled by patient before admin approval.', 'user'),
(20, 5, 2, 4, '2026-05-27', '08:00:00', '', 'pending', '2026-05-26 08:41:45', NULL, NULL);

-- --------------------------------------------------------

--
-- Table structure for table `cart_items`
--

CREATE TABLE `cart_items` (
  `id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `product_id` int(11) NOT NULL,
  `quantity` int(11) NOT NULL DEFAULT 1,
  `added_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `cart_items`
--

INSERT INTO `cart_items` (`id`, `user_id`, `product_id`, `quantity`, `added_at`) VALUES
(9, 4, 8, 6, '2026-05-24 03:57:07');

-- --------------------------------------------------------

--
-- Table structure for table `dentists`
--

CREATE TABLE `dentists` (
  `id` int(11) NOT NULL,
  `name` varchar(150) NOT NULL,
  `specialization` varchar(100) DEFAULT NULL,
  `credentials` text DEFAULT NULL,
  `bio` text DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `dentists`
--

INSERT INTO `dentists` (`id`, `name`, `specialization`, `credentials`, `bio`, `created_at`) VALUES
(1, 'Dr. Sophia Reyes', 'General & Cosmetic Dentistry', 'DMD - 12 years experience', 'Smile transformations and preventive care.', '2026-05-23 01:55:18'),
(2, 'Dr. Marcus Tan', 'Orthodontics & Oral Surgery', 'DMD, MScD - 9 years experience', 'Complex cases with precision and care.', '2026-05-23 01:55:18'),
(3, 'Dr. Leila Varon', 'Pediatric & Family Dentistry', 'DMD, PedDent - 7 years experience', 'Warm care for families and younger patients.', '2026-05-23 01:55:18');

-- --------------------------------------------------------

--
-- Table structure for table `notifications`
--

CREATE TABLE `notifications` (
  `id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `appointment_id` int(11) DEFAULT NULL,
  `audience` enum('user','admin') NOT NULL DEFAULT 'user',
  `message` text NOT NULL,
  `is_read` tinyint(1) NOT NULL DEFAULT 0,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `notifications`
--

INSERT INTO `notifications` (`id`, `user_id`, `appointment_id`, `audience`, `message`, `is_read`, `created_at`) VALUES
(1, 3, 6, 'user', 'Your appointment for Dental Braces Consult on 2026-05-23 at 08:00 has been confirmed.', 0, '2026-05-23 02:56:45'),
(2, 4, 10, 'user', 'Your appointment for Dental X-Ray on 2026-05-26 at 15:00 has been confirmed.', 1, '2026-05-24 03:36:15'),
(3, 4, 10, 'user', 'Your appointment for Dental X-Ray on 2026-05-26 at 15:00 has been confirmed.', 1, '2026-05-24 03:36:15'),
(4, 4, 11, 'user', 'Your appointment for Dental Braces Consult on 2026-05-26 at 15:00 has been cancelled. Reason: due to conflict schedule', 1, '2026-05-24 03:37:45'),
(5, 4, 12, 'admin', 'Chrizyl Abella cancelled the appointment for Porcelain Veneers on 2026-05-28 at 15:00.', 1, '2026-05-24 03:42:03'),
(6, 5, 15, 'admin', 'Jeonghan Jeon cancelled the appointment for Root Canal Treatment on 2026-05-26 at 11:00.', 1, '2026-05-24 04:19:39'),
(7, 5, 14, 'admin', 'Jeonghan Jeon cancelled the appointment for Dental Cleaning on 2026-05-26 at 13:00.', 1, '2026-05-24 04:19:41'),
(8, 5, 13, 'admin', 'Jeonghan Jeon cancelled the appointment for Dental Cleaning on 2026-05-26 at 13:00.', 1, '2026-05-24 04:19:43'),
(9, 5, 16, 'user', 'Your appointment for Dental X-Ray on 2026-05-27 at 15:00 has been confirmed.', 1, '2026-05-24 04:20:57'),
(10, 5, 17, 'user', 'Your appointment for Dental X-Ray on 2026-05-25 at 08:00 has been confirmed.', 1, '2026-05-24 04:49:42'),
(11, 5, 18, 'admin', 'Jeonghan Jeon cancelled the appointment for Dental X-Ray on 2026-05-27 at 15:00.', 1, '2026-05-26 07:17:35'),
(12, 6, 19, 'admin', 'System Admin cancelled the appointment for Porcelain Veneers on 2026-05-26 at 15:00.', 1, '2026-05-27 04:59:58');

-- --------------------------------------------------------

--
-- Table structure for table `orders`
--

CREATE TABLE `orders` (
  `id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `customer_name` varchar(255) DEFAULT NULL,
  `email` varchar(120) DEFAULT NULL,
  `phone` varchar(20) DEFAULT NULL,
  `address` text DEFAULT NULL,
  `city` varchar(100) DEFAULT NULL,
  `zip` varchar(20) DEFAULT NULL,
  `notes` text DEFAULT NULL,
  `payment_method` varchar(50) DEFAULT NULL,
  `gcash_number` varchar(20) DEFAULT NULL,
  `total_amount` decimal(10,2) DEFAULT NULL,
  `status` enum('pending','completed','cancelled') DEFAULT 'pending',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `orders`
--

INSERT INTO `orders` (`id`, `user_id`, `customer_name`, `email`, `phone`, `address`, `city`, `zip`, `notes`, `payment_method`, `gcash_number`, `total_amount`, `status`, `created_at`) VALUES
(1, 3, 'Chrizyl Abella', 'abellachrizyl@gmail.com', '+639274213879', 'Batangas', 'STO. TOMAS CITY', '3423', '', 'cod', '', 738.00, 'pending', '2026-05-23 02:56:00'),
(2, 4, 'Chrizyl Abella', 'abellachrizyl@gmail.com', '+639274213879', 'Batangas', 'STO. TOMAS CITY', '3423', '', 'cod', '', 1137.00, 'pending', '2026-05-24 03:35:41'),
(3, 4, 'Chrizyl Abella', 'abellachrizyl@gmail.com', '+639274213879', 'Batangas', 'STO. TOMAS CITY', '3423', '', 'cod', '', 1137.00, 'pending', '2026-05-24 03:35:41'),
(4, 6, 'Chrizyl Abella', 'abellachrizyl@gmail.com', '+639274213879', 'Batangas', 'STO. TOMAS CITY', '3423', '', 'cod', '', 588.00, 'pending', '2026-05-26 08:22:23'),
(5, 5, 'Chrizyl Abella', 'abellachrizyl@gmail.com', '+639274213879', 'Batangas', 'STO. TOMAS CITY', '3423', '', 'cod', '', 1137.00, 'pending', '2026-05-26 08:42:32');

-- --------------------------------------------------------

--
-- Table structure for table `order_items`
--

CREATE TABLE `order_items` (
  `id` int(11) NOT NULL,
  `order_id` int(11) NOT NULL,
  `product_id` int(11) NOT NULL,
  `quantity` int(11) NOT NULL,
  `unit_price` decimal(10,2) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `order_items`
--

INSERT INTO `order_items` (`id`, `order_id`, `product_id`, `quantity`, `unit_price`, `created_at`) VALUES
(1, 1, 8, 1, 549.00, '2026-05-23 02:56:00'),
(2, 1, 3, 1, 189.00, '2026-05-23 02:56:00'),
(3, 2, 7, 1, 399.00, '2026-05-24 03:35:41'),
(4, 2, 3, 1, 189.00, '2026-05-24 03:35:41'),
(5, 2, 8, 1, 549.00, '2026-05-24 03:35:41'),
(6, 3, 7, 1, 399.00, '2026-05-24 03:35:41'),
(7, 3, 3, 1, 189.00, '2026-05-24 03:35:41'),
(8, 3, 8, 1, 549.00, '2026-05-24 03:35:41'),
(9, 4, 3, 1, 189.00, '2026-05-26 08:22:23'),
(10, 4, 7, 1, 399.00, '2026-05-26 08:22:23'),
(11, 5, 3, 1, 189.00, '2026-05-26 08:42:32'),
(12, 5, 7, 1, 399.00, '2026-05-26 08:42:32'),
(13, 5, 8, 1, 549.00, '2026-05-26 08:42:32');

-- --------------------------------------------------------

--
-- Table structure for table `products`
--

CREATE TABLE `products` (
  `id` int(11) NOT NULL,
  `name` varchar(150) NOT NULL,
  `description` text DEFAULT NULL,
  `price` decimal(10,2) DEFAULT NULL,
  `stock_quantity` int(11) DEFAULT 0,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `products`
--

INSERT INTO `products` (`id`, `name`, `description`, `price`, `stock_quantity`, `created_at`) VALUES
(1, 'Sonic Pro Toothbrush', 'Rechargeable electric toothbrush with 3 modes.', 1299.00, 12, '2026-05-23 01:55:18'),
(2, 'WhiteGlow Toothpaste', 'Enamel-strengthening whitening paste.', 299.00, 30, '2026-05-23 01:55:18'),
(3, 'Silk Dental Floss', 'Natural silk floss with wax coating.', 189.00, 24, '2026-05-23 01:55:18'),
(4, 'AquaFresh Mouthwash', 'Antibacterial alcohol-free rinse.', 349.00, 18, '2026-05-23 01:55:18'),
(5, 'Teeth Whitening Strips', '14-day whitening kit.', 899.00, 16, '2026-05-23 01:55:18'),
(6, 'Tongue Scraper Set', 'Stainless steel scrapers.', 249.00, 20, '2026-05-23 01:55:18'),
(7, 'Sensitive Gum Gel', 'Soothing gel for gum sensitivity.', 399.00, 15, '2026-05-23 01:55:18'),
(8, 'Natural Bamboo Brush Set', '4-pack biodegradable bamboo toothbrushes.', 549.00, 10, '2026-05-23 01:55:18');

-- --------------------------------------------------------

--
-- Table structure for table `services`
--

CREATE TABLE `services` (
  `id` int(11) NOT NULL,
  `name` varchar(150) NOT NULL,
  `description` text DEFAULT NULL,
  `price` decimal(10,2) DEFAULT NULL,
  `category` varchar(50) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `daily_slots` int(11) NOT NULL DEFAULT 0
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `services`
--

INSERT INTO `services` (`id`, `name`, `description`, `price`, `category`, `created_at`, `daily_slots`) VALUES
(1, 'Dental Cleaning', 'Professional prophylaxis to remove plaque and tartar.', 800.00, 'Preventive', '2026-05-23 01:55:18', 8),
(2, 'Dental X-Ray', 'Digital X-rays for accurate diagnosis.', 450.00, 'Diagnostic', '2026-05-23 01:55:18', 8),
(3, 'Tooth Extraction', 'Safe removal of damaged or problematic teeth.', 1200.00, 'Restorative', '2026-05-23 01:55:18', 8),
(4, 'Teeth Whitening', 'Professional-grade whitening treatment.', 3500.00, 'Cosmetic', '2026-05-23 01:55:18', 8),
(5, 'Dental Braces Consult', 'Orthodontic evaluation and treatment planning.', 500.00, 'Orthodontic', '2026-05-23 01:55:18', 8),
(6, 'Root Canal Treatment', 'Precision endodontic therapy.', 6000.00, 'Restorative', '2026-05-23 01:55:18', 8),
(7, 'Dental Crown', 'Custom-fitted porcelain crowns.', 8000.00, 'Restorative', '2026-05-23 01:55:18', 8),
(8, 'Porcelain Veneers', 'Custom shells for aesthetic results.', 12000.00, 'Cosmetic', '2026-05-23 01:55:18', 8),
(9, 'Pediatric Check-Up', 'Gentle dental visits for children.', 600.00, 'Preventive', '2026-05-23 01:55:18', 8);

-- --------------------------------------------------------

--
-- Table structure for table `users`
--

CREATE TABLE `users` (
  `id` int(11) NOT NULL,
  `first_name` varchar(100) NOT NULL,
  `last_name` varchar(100) NOT NULL,
  `email` varchar(120) NOT NULL,
  `phone` varchar(20) DEFAULT NULL,
  `password_hash` varchar(255) NOT NULL,
  `role` varchar(20) NOT NULL DEFAULT 'patient',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `users`
--

INSERT INTO `users` (`id`, `first_name`, `last_name`, `email`, `phone`, `password_hash`, `role`, `created_at`) VALUES
(3, 'Chrizyl', 'Abella', 'abellachrizyl@gmail.com', '09274213879', '$2y$10$SpxIEd7d0L3RQtveozevSO5GZfKVB5OVjsB3hVGRFDiBZLrMF/d1S', 'patient', '2026-05-23 02:53:53'),
(4, 'Chrizyl', 'Abella', 'hannie@gmail.com', '09274213879', '$2y$10$J50O5WjgvdrIJkITMGCDKefEnNOW2EFeP0YmlGOD6eO2MDLVRwj1e', 'patient', '2026-05-24 03:33:37'),
(5, 'Jeonghan', 'Jeon', 'jeon@gmail.com', '123456789', '$2y$10$y9L78.r11pQ/WOUq3psuKuw40hrIwyCiilbZqn0m.u9Q8kEKf676O', 'patient', '2026-05-24 03:40:13'),
(6, 'System', 'Admin', 'admin@aquasmile.com', '0000000000', '$2y$10$qnT4XmT6G85rtdowvE8eYuy0eBNaMX69YlUDnqYqNlQljmIpLqJNG', 'admin', '2026-05-26 07:27:15');

--
-- Indexes for dumped tables
--

--
-- Indexes for table `appointments`
--
ALTER TABLE `appointments`
  ADD PRIMARY KEY (`id`),
  ADD KEY `user_id` (`user_id`),
  ADD KEY `dentist_id` (`dentist_id`),
  ADD KEY `service_id` (`service_id`);

--
-- Indexes for table `cart_items`
--
ALTER TABLE `cart_items`
  ADD PRIMARY KEY (`id`),
  ADD KEY `user_id` (`user_id`),
  ADD KEY `product_id` (`product_id`);

--
-- Indexes for table `dentists`
--
ALTER TABLE `dentists`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `notifications`
--
ALTER TABLE `notifications`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `orders`
--
ALTER TABLE `orders`
  ADD PRIMARY KEY (`id`),
  ADD KEY `user_id` (`user_id`);

--
-- Indexes for table `order_items`
--
ALTER TABLE `order_items`
  ADD PRIMARY KEY (`id`),
  ADD KEY `order_id` (`order_id`),
  ADD KEY `product_id` (`product_id`);

--
-- Indexes for table `products`
--
ALTER TABLE `products`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `services`
--
ALTER TABLE `services`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `users`
--
ALTER TABLE `users`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `email` (`email`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `appointments`
--
ALTER TABLE `appointments`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=21;

--
-- AUTO_INCREMENT for table `cart_items`
--
ALTER TABLE `cart_items`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=15;

--
-- AUTO_INCREMENT for table `dentists`
--
ALTER TABLE `dentists`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT for table `notifications`
--
ALTER TABLE `notifications`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=13;

--
-- AUTO_INCREMENT for table `orders`
--
ALTER TABLE `orders`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=6;

--
-- AUTO_INCREMENT for table `order_items`
--
ALTER TABLE `order_items`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=14;

--
-- AUTO_INCREMENT for table `products`
--
ALTER TABLE `products`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=9;

--
-- AUTO_INCREMENT for table `services`
--
ALTER TABLE `services`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=10;

--
-- AUTO_INCREMENT for table `users`
--
ALTER TABLE `users`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=8;

--
-- Constraints for dumped tables
--

--
-- Constraints for table `appointments`
--
ALTER TABLE `appointments`
  ADD CONSTRAINT `appointments_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `appointments_ibfk_2` FOREIGN KEY (`dentist_id`) REFERENCES `dentists` (`id`) ON DELETE SET NULL,
  ADD CONSTRAINT `appointments_ibfk_3` FOREIGN KEY (`service_id`) REFERENCES `services` (`id`) ON DELETE SET NULL;

--
-- Constraints for table `cart_items`
--
ALTER TABLE `cart_items`
  ADD CONSTRAINT `cart_items_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `cart_items_ibfk_2` FOREIGN KEY (`product_id`) REFERENCES `products` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `orders`
--
ALTER TABLE `orders`
  ADD CONSTRAINT `orders_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `order_items`
--
ALTER TABLE `order_items`
  ADD CONSTRAINT `order_items_ibfk_1` FOREIGN KEY (`order_id`) REFERENCES `orders` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `order_items_ibfk_2` FOREIGN KEY (`product_id`) REFERENCES `products` (`id`) ON DELETE CASCADE;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
