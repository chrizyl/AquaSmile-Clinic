-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: Jun 14, 2026 at 01:22 PM
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
-- Database: `aquasmile_db`
--
CREATE DATABASE IF NOT EXISTS `aquasmile_db` DEFAULT CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci;
USE `aquasmile_db`;

-- --------------------------------------------------------

--
-- Table structure for table `appointments`
--

DROP TABLE IF EXISTS `appointments`;
CREATE TABLE IF NOT EXISTS `appointments` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `user_id` int(11) NOT NULL,
  `dentist_id` int(11) DEFAULT NULL,
  `service_id` int(11) DEFAULT NULL,
  `appointment_date` date NOT NULL,
  `appointment_time` time NOT NULL,
  `notes` text DEFAULT NULL,
  `status` enum('pending','confirmed','completed','cancelled','archived') NOT NULL DEFAULT 'pending',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `cancellation_reason` text DEFAULT NULL,
  `cancelled_by` enum('admin','user') DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `user_id` (`user_id`),
  KEY `dentist_id` (`dentist_id`),
  KEY `service_id` (`service_id`)
) ENGINE=InnoDB AUTO_INCREMENT=23 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

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
(20, 5, 2, 4, '2026-05-27', '08:00:00', '', 'pending', '2026-05-26 08:41:45', NULL, NULL),
(21, 8, 2, 5, '2026-06-17', '11:00:00', 'allergic sa dust', 'completed', '2026-06-14 09:33:08', NULL, NULL),
(22, 8, 1, 1, '2026-06-27', '09:00:00', '', 'cancelled', '2026-06-14 09:57:04', 'had another schedule', 'user');

-- --------------------------------------------------------

--
-- Table structure for table `cart_items`
--

DROP TABLE IF EXISTS `cart_items`;
CREATE TABLE IF NOT EXISTS `cart_items` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `user_id` int(11) NOT NULL,
  `product_id` int(11) NOT NULL,
  `quantity` int(11) NOT NULL DEFAULT 1,
  `added_at` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`),
  KEY `user_id` (`user_id`),
  KEY `product_id` (`product_id`)
) ENGINE=InnoDB AUTO_INCREMENT=21 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `cart_items`
--

INSERT INTO `cart_items` (`id`, `user_id`, `product_id`, `quantity`, `added_at`) VALUES
(9, 4, 8, 6, '2026-05-24 03:57:07');

-- --------------------------------------------------------

--
-- Table structure for table `dentists`
--

DROP TABLE IF EXISTS `dentists`;
CREATE TABLE IF NOT EXISTS `dentists` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `name` varchar(150) NOT NULL,
  `specialization` varchar(100) DEFAULT NULL,
  `credentials` text DEFAULT NULL,
  `bio` text DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `status` enum('active','archived') NOT NULL DEFAULT 'active',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=4 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `dentists`
--

INSERT INTO `dentists` (`id`, `name`, `specialization`, `credentials`, `bio`, `created_at`, `status`) VALUES
(1, 'Dr. Sophia Reyes', 'General & Cosmetic Dentistry', 'DMD - 12 years experience', 'Smile transformations and preventive care.', '2026-05-23 01:55:18', 'active'),
(2, 'Dr. Marcus Tan', 'Orthodontics & Oral Surgery', 'DMD, MScD - 9 years experience', 'Complex cases with precision and care.', '2026-05-23 01:55:18', 'active'),
(3, 'Dr. Leila Varon', 'Pediatric & Family Dentistry', 'DMD, PedDent - 7 years experience', 'Warm care for families and younger patients.', '2026-05-23 01:55:18', 'active');

-- --------------------------------------------------------

--
-- Table structure for table `notifications`
--

DROP TABLE IF EXISTS `notifications`;
CREATE TABLE IF NOT EXISTS `notifications` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `user_id` int(11) NOT NULL,
  `appointment_id` int(11) DEFAULT NULL,
  `audience` enum('user','admin') NOT NULL DEFAULT 'user',
  `message` text NOT NULL,
  `is_read` tinyint(1) NOT NULL DEFAULT 0,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=15 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

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
(12, 6, 19, 'admin', 'System Admin cancelled the appointment for Porcelain Veneers on 2026-05-26 at 15:00.', 1, '2026-05-27 04:59:58'),
(13, 8, 21, 'user', 'Your appointment for Dental Braces Consult on 2026-06-17 at 11:00 has been confirmed.', 1, '2026-06-14 09:34:18'),
(14, 8, 22, 'admin', 'Mary Josephine Magboo cancelled the appointment for Dental Cleaning on 2026-06-27 at 09:00. Reason: had another schedule', 1, '2026-06-14 10:34:03');

-- --------------------------------------------------------

--
-- Table structure for table `orders`
--

DROP TABLE IF EXISTS `orders`;
CREATE TABLE IF NOT EXISTS `orders` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `user_id` int(11) NOT NULL,
  `first_name` varchar(100) DEFAULT NULL,
  `last_name` varchar(100) DEFAULT NULL,
  `email` varchar(120) DEFAULT NULL,
  `phone` varchar(20) DEFAULT NULL,
  `address` varchar(255) DEFAULT NULL,
  `city` varchar(100) DEFAULT NULL,
  `zip` varchar(20) DEFAULT NULL,
  `notes` text DEFAULT NULL,
  `payment_method` varchar(50) DEFAULT NULL,
  `gcash_number` varchar(20) DEFAULT NULL,
  `total_amount` decimal(10,2) DEFAULT NULL,
  `status` enum('pending','processing','out_for_delivery','delivered','completed','cancelled','archived') NOT NULL DEFAULT 'pending',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`),
  KEY `user_id` (`user_id`)
) ENGINE=InnoDB AUTO_INCREMENT=8 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `orders`
--

INSERT INTO `orders` (`id`, `user_id`, `first_name`, `last_name`, `email`, `phone`, `address`, `city`, `zip`, `notes`, `payment_method`, `gcash_number`, `total_amount`, `status`, `created_at`) VALUES
(1, 3, 'Chrizyl Abella', NULL, 'abellachrizyl@gmail.com', '+639274213879', 'Batangas', 'STO. TOMAS CITY', '3423', '', 'cod', '', 738.00, 'pending', '2026-05-23 02:56:00'),
(2, 4, 'Chrizyl Abella', NULL, 'abellachrizyl@gmail.com', '+639274213879', 'Batangas', 'STO. TOMAS CITY', '3423', '', 'cod', '', 1137.00, 'pending', '2026-05-24 03:35:41'),
(3, 4, 'Chrizyl Abella', NULL, 'abellachrizyl@gmail.com', '+639274213879', 'Batangas', 'STO. TOMAS CITY', '3423', '', 'cod', '', 1137.00, 'pending', '2026-05-24 03:35:41'),
(4, 6, 'Chrizyl Abella', NULL, 'abellachrizyl@gmail.com', '+639274213879', 'Batangas', 'STO. TOMAS CITY', '3423', '', 'cod', '', 588.00, 'pending', '2026-05-26 08:22:23'),
(5, 5, 'Chrizyl Abella', NULL, 'abellachrizyl@gmail.com', '+639274213879', 'Batangas', 'STO. TOMAS CITY', '3423', '', 'cod', '', 1137.00, 'pending', '2026-05-26 08:42:32'),
(6, 8, 'Mary Josephine', 'Magboo', 'magboo.mary@gmail.com', '09196882025', 'Purok 1', 'Calamba', '4027', '', 'cod', '', 567.00, 'pending', '2026-06-14 07:19:52'),
(7, 8, 'Mary Josephine', 'Magboo', 'magboo.mary@gmail.com', '09196882025', 'Purok 1', 'Calamba', '4027', '', 'cod', '', 1137.00, 'pending', '2026-06-14 10:36:42');

-- --------------------------------------------------------

--
-- Table structure for table `order_items`
--

DROP TABLE IF EXISTS `order_items`;
CREATE TABLE IF NOT EXISTS `order_items` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `order_id` int(11) NOT NULL,
  `product_id` int(11) NOT NULL,
  `quantity` int(11) NOT NULL,
  `unit_price` decimal(10,2) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`),
  KEY `order_id` (`order_id`),
  KEY `product_id` (`product_id`)
) ENGINE=InnoDB AUTO_INCREMENT=18 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

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
(13, 5, 8, 1, 549.00, '2026-05-26 08:42:32'),
(14, 6, 3, 3, 189.00, '2026-06-14 07:19:52'),
(15, 7, 8, 1, 549.00, '2026-06-14 10:36:42'),
(16, 7, 7, 1, 399.00, '2026-06-14 10:36:42'),
(17, 7, 3, 1, 189.00, '2026-06-14 10:36:42');

-- --------------------------------------------------------

--
-- Table structure for table `otp_verifications`
--

DROP TABLE IF EXISTS `otp_verifications`;
CREATE TABLE IF NOT EXISTS `otp_verifications` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `email` varchar(120) NOT NULL,
  `first_name` varchar(100) NOT NULL,
  `last_name` varchar(100) NOT NULL,
  `phone` varchar(20) NOT NULL,
  `password_hash` varchar(255) NOT NULL,
  `otp_code` varchar(6) NOT NULL,
  `expires_at` datetime NOT NULL,
  `attempts` tinyint(3) UNSIGNED NOT NULL DEFAULT 0,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  `last_otp_sent_at` datetime DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `email` (`email`),
  KEY `idx_expires_at` (`expires_at`)
) ENGINE=InnoDB AUTO_INCREMENT=10 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `products`
--

DROP TABLE IF EXISTS `products`;
CREATE TABLE IF NOT EXISTS `products` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `name` varchar(150) NOT NULL,
  `description` text DEFAULT NULL,
  `price` decimal(10,2) DEFAULT NULL,
  `stock_quantity` int(11) DEFAULT 0,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `status` enum('active','archived') NOT NULL DEFAULT 'active',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=9 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `products`
--

INSERT INTO `products` (`id`, `name`, `description`, `price`, `stock_quantity`, `created_at`, `status`) VALUES
(1, 'Sonic Pro Toothbrush', 'Rechargeable electric toothbrush with 3 modes.', 1299.00, 12, '2026-05-23 01:55:18', 'active'),
(2, 'WhiteGlow Toothpaste', 'Enamel-strengthening whitening paste.', 299.00, 30, '2026-05-23 01:55:18', 'active'),
(3, 'Silk Dental Floss', 'Natural silk floss with wax coating.', 189.00, 24, '2026-05-23 01:55:18', 'active'),
(4, 'AquaFresh Mouthwash', 'Antibacterial alcohol-free rinse.', 349.00, 18, '2026-05-23 01:55:18', 'active'),
(5, 'Teeth Whitening Strips', '14-day whitening kit.', 899.00, 16, '2026-05-23 01:55:18', 'active'),
(6, 'Tongue Scraper Set', 'Stainless steel scrapers.', 249.00, 20, '2026-05-23 01:55:18', 'active'),
(7, 'Sensitive Gum Gel', 'Soothing gel for gum sensitivity.', 399.00, 15, '2026-05-23 01:55:18', 'active'),
(8, 'Natural Bamboo Brush Set', '4-pack biodegradable bamboo toothbrushes.', 549.00, 10, '2026-05-23 01:55:18', 'active');

-- --------------------------------------------------------

--
-- Table structure for table `services`
--

DROP TABLE IF EXISTS `services`;
CREATE TABLE IF NOT EXISTS `services` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `name` varchar(150) NOT NULL,
  `description` text DEFAULT NULL,
  `price` decimal(10,2) DEFAULT NULL,
  `category` varchar(50) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `daily_slots` int(11) NOT NULL DEFAULT 0,
  `status` enum('active','archived') NOT NULL DEFAULT 'active',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=10 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `services`
--

INSERT INTO `services` (`id`, `name`, `description`, `price`, `category`, `created_at`, `daily_slots`, `status`) VALUES
(1, 'Dental Cleaning', 'Professional prophylaxis to remove plaque and tartar.', 800.00, 'Preventive', '2026-05-23 01:55:18', 8, 'active'),
(2, 'Dental X-Ray', 'Digital X-rays for accurate diagnosis.', 450.00, 'Diagnostic', '2026-05-23 01:55:18', 8, 'active'),
(3, 'Tooth Extraction', 'Safe removal of damaged or problematic teeth.', 1200.00, 'Restorative', '2026-05-23 01:55:18', 8, 'active'),
(4, 'Teeth Whitening', 'Professional-grade whitening treatment.', 3500.00, 'Cosmetic', '2026-05-23 01:55:18', 8, 'active'),
(5, 'Dental Braces Consult', 'Orthodontic evaluation and treatment planning.', 500.00, 'Orthodontic', '2026-05-23 01:55:18', 8, 'active'),
(6, 'Root Canal Treatment', 'Precision endodontic therapy.', 6000.00, 'Restorative', '2026-05-23 01:55:18', 8, 'active'),
(7, 'Dental Crown', 'Custom-fitted porcelain crowns.', 8000.00, 'Restorative', '2026-05-23 01:55:18', 8, 'active'),
(8, 'Porcelain Veneers', 'Custom shells for aesthetic results.', 12000.00, 'Cosmetic', '2026-05-23 01:55:18', 8, 'active'),
(9, 'Pediatric Check-Up', 'Gentle dental visits for children.', 600.00, 'Preventive', '2026-05-23 01:55:18', 8, 'active');

-- --------------------------------------------------------

--
-- Table structure for table `users`
--

DROP TABLE IF EXISTS `users`;
CREATE TABLE IF NOT EXISTS `users` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `first_name` varchar(100) NOT NULL,
  `last_name` varchar(100) NOT NULL,
  `email` varchar(120) NOT NULL,
  `phone` varchar(20) NOT NULL,
  `password_hash` varchar(255) NOT NULL,
  `role` varchar(20) NOT NULL DEFAULT 'patient',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `birthdate` date DEFAULT NULL,
  `gender` enum('Female','Male','Prefer not to say') DEFAULT NULL,
  `emergency_contact_name` varchar(150) DEFAULT NULL,
  `emergency_contact_number` varchar(20) DEFAULT NULL,
  `house_no` varchar(50) DEFAULT NULL,
  `street` varchar(150) DEFAULT NULL,
  `barangay` varchar(100) DEFAULT NULL,
  `city` varchar(100) DEFAULT NULL,
  `province` varchar(100) DEFAULT NULL,
  `zip_code` varchar(10) DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `email` (`email`)
) ENGINE=InnoDB AUTO_INCREMENT=9 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `users`
--

INSERT INTO `users` (`id`, `first_name`, `last_name`, `email`, `phone`, `password_hash`, `role`, `created_at`, `birthdate`, `gender`, `emergency_contact_name`, `emergency_contact_number`, `house_no`, `street`, `barangay`, `city`, `province`, `zip_code`) VALUES
(3, 'Chrizyl', 'Abella', 'abellachrizyl@gmail.com', '09274213879', '$2y$10$SpxIEd7d0L3RQtveozevSO5GZfKVB5OVjsB3hVGRFDiBZLrMF/d1S', 'patient', '2026-05-23 02:53:53', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL),
(4, 'Chrizyl', 'Abella', 'hannie@gmail.com', '09274213879', '$2y$10$J50O5WjgvdrIJkITMGCDKefEnNOW2EFeP0YmlGOD6eO2MDLVRwj1e', 'patient', '2026-05-24 03:33:37', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL),
(5, 'Jeonghan', 'Jeon', 'jeon@gmail.com', '123456789', '$2y$10$y9L78.r11pQ/WOUq3psuKuw40hrIwyCiilbZqn0m.u9Q8kEKf676O', 'patient', '2026-05-24 03:40:13', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL),
(6, 'System', 'Admin', 'admin@aquasmile.com', '0000000000', '$2y$10$qnT4XmT6G85rtdowvE8eYuy0eBNaMX69YlUDnqYqNlQljmIpLqJNG', 'admin', '2026-05-26 07:27:15', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL),
(8, 'Mary Josephine', 'Magboo', 'maryjosephine076@gmail.com', '09672547242', '$2y$10$ajA7zo1IlLxd/Lcx/XIF7eneTMB7cAUaJPjh15.Cn2yRh.aeQV7aC', 'patient', '2026-06-14 06:33:02', NULL, 'Female', NULL, NULL, '1', 'Purok', 'Makiling', 'Calamba', 'LAGUNA', '4027');

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
