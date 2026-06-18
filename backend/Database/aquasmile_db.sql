-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: Jun 18, 2026 at 10:52 AM
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
  `appointment_id` int(11) NOT NULL AUTO_INCREMENT,
  `user_id` int(11) NOT NULL,
  `dentist_id` int(11) DEFAULT NULL,
  `service_id` int(11) DEFAULT NULL,
  `promo_id` int(11) DEFAULT NULL,
  `promo_code` varchar(50) DEFAULT NULL,
  `discount_amount` decimal(10,2) DEFAULT 0.00,
  `final_fee` decimal(10,2) DEFAULT NULL,
  `appointment_date` date NOT NULL,
  `appointment_time` time NOT NULL,
  `notes` text DEFAULT NULL,
  `status` enum('pending','confirmed','completed','cancelled','archived') NOT NULL DEFAULT 'pending',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `cancellation_reason` text DEFAULT NULL,
  `cancelled_by` enum('admin','user') DEFAULT NULL,
  PRIMARY KEY (`appointment_id`),
  KEY `user_id` (`user_id`),
  KEY `dentist_id` (`dentist_id`),
  KEY `service_id` (`service_id`),
  KEY `appointments_promo_fk` (`promo_id`)
) ENGINE=InnoDB AUTO_INCREMENT=34 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `appointments`
--

INSERT INTO `appointments` (`appointment_id`, `user_id`, `dentist_id`, `service_id`, `promo_id`, `promo_code`, `discount_amount`, `final_fee`, `appointment_date`, `appointment_time`, `notes`, `status`, `created_at`, `cancellation_reason`, `cancelled_by`) VALUES
(6, 3, 1, 5, NULL, NULL, 0.00, NULL, '2026-05-23', '08:00:00', '', 'confirmed', '2026-05-23 02:54:45', NULL, NULL),
(10, 4, 1, 2, NULL, NULL, 0.00, NULL, '2026-05-26', '15:00:00', '', 'confirmed', '2026-05-24 03:34:22', NULL, NULL),
(11, 4, 1, 5, NULL, NULL, 0.00, NULL, '2026-05-26', '15:00:00', '', 'cancelled', '2026-05-24 03:37:23', 'due to conflict schedule', 'admin'),
(12, 4, 1, 8, NULL, NULL, 0.00, NULL, '2026-05-28', '15:00:00', '', 'cancelled', '2026-05-24 03:41:56', 'Cancelled by patient before admin approval.', 'user'),
(13, 5, 2, 1, NULL, NULL, 0.00, NULL, '2026-05-26', '13:00:00', '', 'cancelled', '2026-05-24 04:13:08', 'Cancelled by patient before admin approval.', 'user'),
(14, 5, 2, 1, NULL, NULL, 0.00, NULL, '2026-05-26', '13:00:00', '', 'cancelled', '2026-05-24 04:13:08', 'Cancelled by patient before admin approval.', 'user'),
(15, 5, 2, 6, NULL, NULL, 0.00, NULL, '2026-05-26', '11:00:00', '', 'cancelled', '2026-05-24 04:19:14', 'Cancelled by patient before admin approval.', 'user'),
(16, 5, 2, 2, NULL, NULL, 0.00, NULL, '2026-05-27', '15:00:00', 'i have pekdsjh', 'completed', '2026-05-24 04:20:07', NULL, NULL),
(17, 5, 3, 2, NULL, NULL, 0.00, NULL, '2026-05-25', '08:00:00', '', 'confirmed', '2026-05-24 04:49:04', NULL, NULL),
(18, 5, 3, 2, NULL, NULL, 0.00, NULL, '2026-05-27', '15:00:00', 'peanuts\n', 'cancelled', '2026-05-26 07:17:11', 'Cancelled by patient before admin approval.', 'user'),
(20, 5, 2, 4, NULL, NULL, 0.00, NULL, '2026-05-27', '08:00:00', '', 'pending', '2026-05-26 08:41:45', NULL, NULL),
(21, 8, 2, 5, NULL, NULL, 0.00, NULL, '2026-06-17', '11:00:00', 'allergic sa dust', 'completed', '2026-06-14 09:33:08', NULL, NULL),
(22, 8, 1, 1, NULL, NULL, 0.00, NULL, '2026-06-27', '09:00:00', '', 'archived', '2026-06-14 09:57:04', NULL, NULL),
(23, 8, 3, 6, NULL, NULL, 0.00, NULL, '2026-08-07', '08:00:00', '', 'pending', '2026-06-15 09:00:19', NULL, NULL),
(24, 8, 2, 8, NULL, NULL, 0.00, NULL, '2026-08-06', '15:00:00', '', 'pending', '2026-06-15 09:57:00', NULL, NULL),
(25, 8, 3, 2, NULL, NULL, 0.00, NULL, '2026-08-22', '11:00:00', '', 'confirmed', '2026-06-15 20:58:56', NULL, NULL),
(26, 8, 3, 7, NULL, NULL, 0.00, NULL, '2026-06-17', '17:00:00', '', 'pending', '2026-06-17 09:50:59', NULL, NULL),
(27, 8, 3, 5, NULL, NULL, 0.00, NULL, '2026-06-17', '08:00:00', '', 'pending', '2026-06-17 09:51:52', NULL, NULL),
(28, 8, 2, 4, NULL, NULL, 0.00, NULL, '2026-06-17', '11:00:00', '', 'pending', '2026-06-17 09:57:50', NULL, NULL),
(29, 8, 1, 3, NULL, NULL, 0.00, NULL, '2026-06-17', '14:00:00', '', 'pending', '2026-06-17 09:59:32', NULL, NULL),
(30, 8, 1, 5, NULL, NULL, 0.00, NULL, '2026-06-17', '13:00:00', '', 'pending', '2026-06-17 11:04:01', NULL, NULL),
(31, 8, 2, 2, NULL, NULL, 0.00, NULL, '2026-06-22', '14:00:00', '', 'pending', '2026-06-17 11:28:30', NULL, NULL),
(32, 8, 2, 5, 1, 'APPT10', 50.00, 450.00, '2026-09-01', '11:00:00', '', 'pending', '2026-06-17 15:36:36', NULL, NULL),
(33, 8, 2, 2, NULL, NULL, 0.00, 450.00, '2026-08-31', '15:00:00', '', 'pending', '2026-06-17 16:15:41', NULL, NULL);

-- --------------------------------------------------------

--
-- Table structure for table `cart_items`
--

DROP TABLE IF EXISTS `cart_items`;
CREATE TABLE IF NOT EXISTS `cart_items` (
  `cart_item_id` int(11) NOT NULL AUTO_INCREMENT,
  `user_id` int(11) NOT NULL,
  `product_id` int(11) NOT NULL,
  `quantity` int(11) NOT NULL DEFAULT 1,
  `added_at` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`cart_item_id`),
  KEY `user_id` (`user_id`),
  KEY `product_id` (`product_id`)
) ENGINE=InnoDB AUTO_INCREMENT=40 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `cart_items`
--

INSERT INTO `cart_items` (`cart_item_id`, `user_id`, `product_id`, `quantity`, `added_at`) VALUES
(9, 4, 8, 6, '2026-05-24 03:57:07'),
(37, 8, 3, 2, '2026-06-17 11:47:18');

-- --------------------------------------------------------

--
-- Table structure for table `claimed_coupons`
--

DROP TABLE IF EXISTS `claimed_coupons`;
CREATE TABLE IF NOT EXISTS `claimed_coupons` (
  `claimed_coupon_id` int(11) NOT NULL AUTO_INCREMENT,
  `promo_id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `claimed_at` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`claimed_coupon_id`),
  UNIQUE KEY `claimed_promo_user_unique` (`promo_id`,`user_id`),
  KEY `claimed_coupons_user_fk` (`user_id`)
) ENGINE=InnoDB AUTO_INCREMENT=2 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `claimed_coupons`
--

INSERT INTO `claimed_coupons` (`claimed_coupon_id`, `promo_id`, `user_id`, `claimed_at`) VALUES
(1, 2, 8, '2026-06-17 20:18:58');

-- --------------------------------------------------------

--
-- Table structure for table `dentists`
--

DROP TABLE IF EXISTS `dentists`;
CREATE TABLE IF NOT EXISTS `dentists` (
  `dentist_id` int(11) NOT NULL AUTO_INCREMENT,
  `first_name` varchar(100) DEFAULT NULL,
  `last_name` varchar(100) DEFAULT NULL,
  `specialization` varchar(100) DEFAULT NULL,
  `credentials` text DEFAULT NULL,
  `bio` text DEFAULT NULL,
  `education` varchar(255) DEFAULT NULL,
  `languages` varchar(150) DEFAULT NULL,
  `practicing_since` varchar(100) DEFAULT NULL,
  `image_path` varchar(255) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `status` enum('available','unavailable') NOT NULL DEFAULT 'available',
  PRIMARY KEY (`dentist_id`)
) ENGINE=InnoDB AUTO_INCREMENT=7 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `dentists`
--

INSERT INTO `dentists` (`dentist_id`, `first_name`, `last_name`, `specialization`, `credentials`, `bio`, `education`, `languages`, `practicing_since`, `image_path`, `created_at`, `status`) VALUES
(1, 'Sophia ', 'Reyes', 'General & Cosmetic Dentistry', 'DMD - 12 years experience', 'Smile transformations and preventive care.', NULL, NULL, NULL, NULL, '2026-05-23 01:55:18', 'available'),
(2, 'Marcus', 'Tan', 'Orthodontics & Oral Surgery', 'DMD, MScD - 9 years experience', 'Complex cases with precision and care.', NULL, NULL, NULL, NULL, '2026-05-23 01:55:18', 'available'),
(3, 'Leila', 'Varon', 'Pediatric & Family Dentistry', 'DMD, PedDent - 7 years experience', 'Warm care for families and younger patients.', NULL, NULL, NULL, NULL, '2026-05-23 01:55:18', 'available');

-- --------------------------------------------------------

--
-- Table structure for table `feedback`
--

DROP TABLE IF EXISTS `feedback`;
CREATE TABLE IF NOT EXISTS `feedback` (
  `feedback_id` int(11) NOT NULL AUTO_INCREMENT,
  `user_id` int(11) NOT NULL,
  `appointment_id` int(11) DEFAULT NULL,
  `order_id` int(11) DEFAULT NULL,
  `feedback_type` enum('appointment','order') NOT NULL,
  `rating` tinyint(1) NOT NULL,
  `tags` text DEFAULT NULL,
  `comment` text DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`feedback_id`),
  KEY `user_id` (`user_id`),
  KEY `appointment_id` (`appointment_id`),
  KEY `order_id` (`order_id`)
) ENGINE=InnoDB AUTO_INCREMENT=3 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `feedback`
--

INSERT INTO `feedback` (`feedback_id`, `user_id`, `appointment_id`, `order_id`, `feedback_type`, `rating`, `tags`, `comment`, `created_at`) VALUES
(1, 8, NULL, 12, 'order', 4, 'Great Value, Easy Checkout, Fast Delivery', '', '2026-06-17 10:56:11'),
(2, 8, 30, NULL, 'appointment', 5, 'Clean Website, Fast Website', '', '2026-06-17 11:04:09');

-- --------------------------------------------------------

--
-- Table structure for table `notifications`
--

DROP TABLE IF EXISTS `notifications`;
CREATE TABLE IF NOT EXISTS `notifications` (
  `notification_id` int(11) NOT NULL AUTO_INCREMENT,
  `user_id` int(11) NOT NULL,
  `appointment_id` int(11) DEFAULT NULL,
  `order_id` int(11) DEFAULT NULL,
  `audience` enum('user','admin') NOT NULL DEFAULT 'user',
  `message` text NOT NULL,
  `is_read` tinyint(1) NOT NULL DEFAULT 0,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`notification_id`),
  KEY `notifications_order_fk` (`order_id`)
) ENGINE=InnoDB AUTO_INCREMENT=32 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `notifications`
--

INSERT INTO `notifications` (`notification_id`, `user_id`, `appointment_id`, `order_id`, `audience`, `message`, `is_read`, `created_at`) VALUES
(1, 3, 6, NULL, 'user', 'Your appointment for Dental Braces Consult on 2026-05-23 at 08:00 has been confirmed.', 0, '2026-05-23 02:56:45'),
(2, 4, 10, NULL, 'user', 'Your appointment for Dental X-Ray on 2026-05-26 at 15:00 has been confirmed.', 1, '2026-05-24 03:36:15'),
(3, 4, 10, NULL, 'user', 'Your appointment for Dental X-Ray on 2026-05-26 at 15:00 has been confirmed.', 1, '2026-05-24 03:36:15'),
(4, 4, 11, NULL, 'user', 'Your appointment for Dental Braces Consult on 2026-05-26 at 15:00 has been cancelled. Reason: due to conflict schedule', 1, '2026-05-24 03:37:45'),
(5, 4, 12, NULL, 'admin', 'Chrizyl Abella cancelled the appointment for Porcelain Veneers on 2026-05-28 at 15:00.', 1, '2026-05-24 03:42:03'),
(6, 5, 15, NULL, 'admin', 'Jeonghan Jeon cancelled the appointment for Root Canal Treatment on 2026-05-26 at 11:00.', 1, '2026-05-24 04:19:39'),
(7, 5, 14, NULL, 'admin', 'Jeonghan Jeon cancelled the appointment for Dental Cleaning on 2026-05-26 at 13:00.', 1, '2026-05-24 04:19:41'),
(8, 5, 13, NULL, 'admin', 'Jeonghan Jeon cancelled the appointment for Dental Cleaning on 2026-05-26 at 13:00.', 1, '2026-05-24 04:19:43'),
(9, 5, 16, NULL, 'user', 'Your appointment for Dental X-Ray on 2026-05-27 at 15:00 has been confirmed.', 1, '2026-05-24 04:20:57'),
(10, 5, 17, NULL, 'user', 'Your appointment for Dental X-Ray on 2026-05-25 at 08:00 has been confirmed.', 1, '2026-05-24 04:49:42'),
(11, 5, 18, NULL, 'admin', 'Jeonghan Jeon cancelled the appointment for Dental X-Ray on 2026-05-27 at 15:00.', 1, '2026-05-26 07:17:35'),
(12, 6, 19, NULL, 'admin', 'System Admin cancelled the appointment for Porcelain Veneers on 2026-05-26 at 15:00.', 1, '2026-05-27 04:59:58'),
(13, 8, 21, NULL, 'user', 'Your appointment for Dental Braces Consult on 2026-06-17 at 11:00 has been confirmed.', 1, '2026-06-14 09:34:18'),
(14, 8, 22, NULL, 'admin', 'Mary Josephine Magboo cancelled the appointment for Dental Cleaning on 2026-06-27 at 09:00. Reason: had another schedule', 1, '2026-06-14 10:34:03'),
(15, 8, 25, NULL, 'admin', 'Mary Josephine Magboo booked an appointment for Dental X-Ray on 2026-08-22 at 11:00.', 1, '2026-06-15 20:58:56'),
(16, 8, 25, NULL, 'user', 'Your appointment for Dental X-Ray on 2026-08-22 at 11:00 has been confirmed.', 1, '2026-06-15 20:59:41'),
(17, 8, NULL, 9, 'admin', 'New order #9 has been placed by Mary Josephine Magboo.', 1, '2026-06-17 09:50:10'),
(18, 8, 26, NULL, 'admin', 'Mary Josephine Magboo booked an appointment for Dental Crown on 2026-06-17 at 17:00.', 1, '2026-06-17 09:50:59'),
(19, 8, 27, NULL, 'admin', 'Mary Josephine Magboo booked an appointment for Dental Braces Consult on 2026-06-17 at 08:00.', 1, '2026-06-17 09:51:52'),
(20, 8, NULL, 10, 'admin', 'New order #10 has been placed by Mary Josephine Magboo.', 1, '2026-06-17 09:52:32'),
(21, 8, NULL, 11, 'admin', 'New order #11 has been placed by Mary Josephine Magboo.', 1, '2026-06-17 09:56:24'),
(22, 8, 28, NULL, 'admin', 'Mary Josephine Magboo booked an appointment for Teeth Whitening on 2026-06-17 at 11:00.', 1, '2026-06-17 09:57:50'),
(23, 8, 29, NULL, 'admin', 'Mary Josephine Magboo booked an appointment for Tooth Extraction on 2026-06-17 at 14:00.', 1, '2026-06-17 09:59:33'),
(24, 8, NULL, 12, 'admin', 'New order #12 has been placed by Mary Josephine Magboo.', 1, '2026-06-17 10:55:59'),
(25, 8, 30, NULL, 'admin', 'Mary Josephine Magboo booked an appointment for Dental Braces Consult on 2026-06-17 at 13:00.', 1, '2026-06-17 11:04:01'),
(26, 8, 31, NULL, 'admin', 'Mary Josephine Magboo booked an appointment for Dental X-Ray on 2026-06-22 at 14:00.', 1, '2026-06-17 11:28:30'),
(27, 8, 32, NULL, 'admin', 'Mary Josephine Magboo booked an appointment for Dental Braces Consult on 2026-09-01 at 11:00.', 1, '2026-06-17 15:36:36'),
(28, 8, 33, NULL, 'admin', 'Mary Josephine Magboo booked an appointment for Dental X-Ray on 2026-08-31 at 15:00.', 1, '2026-06-17 16:15:41'),
(29, 8, NULL, 13, 'admin', 'New order #13 has been placed by Mary Josephine Magboo.', 1, '2026-06-17 16:16:11'),
(30, 8, NULL, 14, 'admin', 'New order #14 has been placed by Mary Josephine Magboo.', 1, '2026-06-17 20:51:48'),
(31, 8, NULL, 15, 'admin', 'New order #15 has been placed by Mary Josephine Magboo.', 1, '2026-06-18 07:30:44');

-- --------------------------------------------------------

--
-- Table structure for table `orders`
--

DROP TABLE IF EXISTS `orders`;
CREATE TABLE IF NOT EXISTS `orders` (
  `order_id` int(11) NOT NULL AUTO_INCREMENT,
  `user_id` int(11) NOT NULL,
  `promo_id` int(11) DEFAULT NULL,
  `promo_code` varchar(50) DEFAULT NULL,
  `discount_amount` decimal(10,2) DEFAULT 0.00,
  `first_name` varchar(100) DEFAULT NULL,
  `last_name` varchar(100) DEFAULT NULL,
  `email` varchar(120) DEFAULT NULL,
  `phone` varchar(20) DEFAULT NULL,
  `house_no` varchar(50) DEFAULT NULL,
  `street` varchar(150) DEFAULT NULL,
  `barangay` varchar(100) DEFAULT NULL,
  `city` varchar(100) DEFAULT NULL,
  `province` varchar(100) DEFAULT NULL,
  `zip` varchar(20) DEFAULT NULL,
  `notes` text DEFAULT NULL,
  `payment_method` varchar(50) DEFAULT NULL,
  `gcash_number` varchar(20) DEFAULT NULL,
  `total_amount` decimal(10,2) DEFAULT NULL,
  `status` enum('pending','processing','out_for_delivery','delivered','completed','cancelled','archived') NOT NULL DEFAULT 'pending',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `payment_receipt` varchar(255) DEFAULT NULL,
  `card_number` varchar(20) DEFAULT NULL,
  `card_expiry` varchar(10) DEFAULT NULL,
  `card_holder` varchar(100) DEFAULT NULL,
  PRIMARY KEY (`order_id`),
  KEY `user_id` (`user_id`),
  KEY `orders_promo_fk` (`promo_id`)
) ENGINE=InnoDB AUTO_INCREMENT=16 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `orders`
--

INSERT INTO `orders` (`order_id`, `user_id`, `promo_id`, `promo_code`, `discount_amount`, `first_name`, `last_name`, `email`, `phone`, `house_no`, `street`, `barangay`, `city`, `province`, `zip`, `notes`, `payment_method`, `gcash_number`, `total_amount`, `status`, `created_at`, `payment_receipt`, `card_number`, `card_expiry`, `card_holder`) VALUES
(1, 3, NULL, NULL, 0.00, 'Chrizyl Abella', NULL, 'abellachrizyl@gmail.com', '+639274213879', NULL, NULL, NULL, 'STO. TOMAS CITY', NULL, '3423', '', 'cod', '', 738.00, 'pending', '2026-05-23 02:56:00', NULL, NULL, NULL, NULL),
(2, 4, NULL, NULL, 0.00, 'Chrizyl Abella', NULL, 'abellachrizyl@gmail.com', '+639274213879', NULL, NULL, NULL, 'STO. TOMAS CITY', NULL, '3423', '', 'cod', '', 1137.00, 'pending', '2026-05-24 03:35:41', NULL, NULL, NULL, NULL),
(3, 4, NULL, NULL, 0.00, 'Chrizyl Abella', NULL, 'abellachrizyl@gmail.com', '+639274213879', NULL, NULL, NULL, 'STO. TOMAS CITY', NULL, '3423', '', 'cod', '', 1137.00, 'pending', '2026-05-24 03:35:41', NULL, NULL, NULL, NULL),
(4, 6, NULL, NULL, 0.00, 'Chrizyl Abella', NULL, 'abellachrizyl@gmail.com', '+639274213879', NULL, NULL, NULL, 'STO. TOMAS CITY', NULL, '3423', '', 'cod', '', 588.00, 'pending', '2026-05-26 08:22:23', NULL, NULL, NULL, NULL),
(5, 5, NULL, NULL, 0.00, 'Chrizyl Abella', NULL, 'abellachrizyl@gmail.com', '+639274213879', NULL, NULL, NULL, 'STO. TOMAS CITY', NULL, '3423', '', 'cod', '', 1137.00, 'pending', '2026-05-26 08:42:32', NULL, NULL, NULL, NULL),
(6, 8, NULL, NULL, 0.00, 'Mary Josephine', 'Magboo', 'magboo.mary@gmail.com', '09196882025', NULL, NULL, NULL, 'Calamba', NULL, '4027', '', 'cod', '', 567.00, 'pending', '2026-06-14 07:19:52', NULL, NULL, NULL, NULL),
(7, 8, NULL, NULL, 0.00, 'Mary Josephine', 'Magboo', 'magboo.mary@gmail.com', '09196882025', NULL, NULL, NULL, 'Calamba', NULL, '4027', '', 'cod', '', 1137.00, 'pending', '2026-06-14 10:36:42', NULL, NULL, NULL, NULL),
(8, 8, NULL, NULL, 0.00, 'Mary Josephine', 'Magboo', 'maryjosephine076@gmail.com', '09672547242', '1', 'Purok', 'Makiling', 'Calamba', 'LAGUNA', '4027', '', 'cod', '', 538.00, 'pending', '2026-06-15 09:55:17', NULL, NULL, NULL, NULL),
(9, 8, NULL, NULL, 0.00, 'Mary Josephine', 'Magboo', 'maryjosephine076@gmail.com', '09672547242', '1', 'Purok', 'Makiling', 'Calamba', 'LAGUNA', '4027', '', 'cod', '', 1337.00, 'pending', '2026-06-17 09:50:10', NULL, NULL, NULL, NULL),
(10, 8, NULL, NULL, 0.00, 'Mary Josephine', 'Magboo', 'maryjosephine076@gmail.com', '09672547242', '1', 'Purok', 'Makiling', 'Calamba', 'LAGUNA', '4027', '', 'cod', '', 777.00, 'pending', '2026-06-17 09:52:32', NULL, NULL, NULL, NULL),
(11, 8, NULL, NULL, 0.00, 'Mary Josephine', 'Magboo', 'maryjosephine076@gmail.com', '09672547242', '1', 'Purok', 'Makiling', 'Calamba', 'LAGUNA', '4027', '', 'cod', '', 189.00, 'pending', '2026-06-17 09:56:24', NULL, NULL, NULL, NULL),
(12, 8, NULL, NULL, 0.00, 'Mary Josephine', 'Magboo', 'maryjosephine076@gmail.com', '09672547242', '1', 'Purok', 'Makiling', 'Calamba', 'LAGUNA', '4027', '', 'cod', '', 548.00, 'pending', '2026-06-17 10:55:59', NULL, NULL, NULL, NULL),
(13, 8, 2, 'SHOP10', 39.90, 'Mary Josephine', 'Magboo', 'maryjosephine076@gmail.com', '09672547242', '1', 'Purok', 'Makiling', 'Calamba', 'LAGUNA', '4027', '', 'cod', '', 359.10, 'pending', '2026-06-17 16:16:11', NULL, NULL, NULL, NULL),
(14, 8, 2, 'SHOP10', 54.90, 'Mary Josephine', 'Magboo', 'maryjosephine076@gmail.com', '09672547242', '1', 'Purok', 'Makiling', 'Calamba', 'LAGUNA', '4027', '', 'cod', '', 494.10, 'pending', '2026-06-17 20:51:48', NULL, NULL, NULL, NULL),
(15, 8, NULL, NULL, 0.00, 'Mary Josephine', 'Magboo', 'maryjosephine076@gmail.com', '09672547242', '1', 'Purok', 'Makiling', 'Calamba', 'LAGUNA', '4027', '', 'cod', '', 249.00, 'pending', '2026-06-18 07:30:44', NULL, NULL, NULL, NULL);

-- --------------------------------------------------------

--
-- Table structure for table `order_items`
--

DROP TABLE IF EXISTS `order_items`;
CREATE TABLE IF NOT EXISTS `order_items` (
  `order_item_id` int(11) NOT NULL AUTO_INCREMENT,
  `order_id` int(11) NOT NULL,
  `product_id` int(11) NOT NULL,
  `quantity` int(11) NOT NULL,
  `unit_price` decimal(10,2) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`order_item_id`),
  KEY `order_id` (`order_id`),
  KEY `product_id` (`product_id`)
) ENGINE=InnoDB AUTO_INCREMENT=31 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `order_items`
--

INSERT INTO `order_items` (`order_item_id`, `order_id`, `product_id`, `quantity`, `unit_price`, `created_at`) VALUES
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
(17, 7, 3, 1, 189.00, '2026-06-14 10:36:42'),
(18, 8, 4, 1, 349.00, '2026-06-15 09:55:17'),
(19, 8, 3, 1, 189.00, '2026-06-15 09:55:17'),
(20, 9, 3, 1, 189.00, '2026-06-17 09:50:10'),
(21, 9, 6, 1, 249.00, '2026-06-17 09:50:10'),
(22, 9, 5, 1, 899.00, '2026-06-17 09:50:10'),
(23, 10, 7, 1, 399.00, '2026-06-17 09:52:32'),
(24, 10, 3, 2, 189.00, '2026-06-17 09:52:32'),
(25, 11, 3, 1, 189.00, '2026-06-17 09:56:24'),
(26, 12, 6, 1, 249.00, '2026-06-17 10:55:59'),
(27, 12, 2, 1, 299.00, '2026-06-17 10:55:59'),
(28, 13, 7, 1, 399.00, '2026-06-17 16:16:11'),
(29, 14, 8, 1, 549.00, '2026-06-17 20:51:48'),
(30, 15, 6, 1, 249.00, '2026-06-18 07:30:44');

-- --------------------------------------------------------

--
-- Table structure for table `otp_verifications`
--

DROP TABLE IF EXISTS `otp_verifications`;
CREATE TABLE IF NOT EXISTS `otp_verifications` (
  `otp_id` int(11) NOT NULL AUTO_INCREMENT,
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
  PRIMARY KEY (`otp_id`),
  UNIQUE KEY `email` (`email`),
  KEY `idx_expires_at` (`expires_at`)
) ENGINE=InnoDB AUTO_INCREMENT=17 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `products`
--

DROP TABLE IF EXISTS `products`;
CREATE TABLE IF NOT EXISTS `products` (
  `product_id` int(11) NOT NULL AUTO_INCREMENT,
  `product_name` varchar(150) NOT NULL,
  `category` varchar(50) DEFAULT NULL,
  `description` text DEFAULT NULL,
  `image_path` varchar(255) DEFAULT NULL,
  `price` decimal(10,2) DEFAULT NULL,
  `stock_quantity` int(11) DEFAULT 0,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `status` enum('available','sold_out') NOT NULL DEFAULT 'available',
  PRIMARY KEY (`product_id`)
) ENGINE=InnoDB AUTO_INCREMENT=11 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `products`
--

INSERT INTO `products` (`product_id`, `product_name`, `category`, `description`, `image_path`, `price`, `stock_quantity`, `created_at`, `status`) VALUES
(1, 'Sonic Pro Toothbrush', NULL, 'Rechargeable electric toothbrush with 3 modes.', NULL, 1299.00, 12, '2026-05-23 01:55:18', 'available'),
(2, 'WhiteGlow Toothpaste', NULL, 'Enamel-strengthening whitening paste.', NULL, 299.00, 30, '2026-05-23 01:55:18', 'available'),
(3, 'Silk Dental Floss', NULL, 'Natural silk floss with wax coating.', NULL, 189.00, 24, '2026-05-23 01:55:18', 'available'),
(4, 'AquaFresh Mouthwash', NULL, 'Antibacterial alcohol-free rinse.', NULL, 349.00, 18, '2026-05-23 01:55:18', 'available'),
(5, 'Teeth Whitening Strips', NULL, '14-day whitening kit.', NULL, 899.00, 16, '2026-05-23 01:55:18', 'available'),
(6, 'Tongue Scraper Set', NULL, 'Stainless steel scrapers.', NULL, 249.00, 20, '2026-05-23 01:55:18', 'available'),
(7, 'Sensitive Gum Gel', NULL, 'Soothing gel for gum sensitivity.', NULL, 399.00, 15, '2026-05-23 01:55:18', 'available'),
(8, 'Natural Bamboo Brush Set', NULL, '4-pack biodegradable bamboo toothbrushes.', NULL, 549.00, 10, '2026-05-23 01:55:18', 'available');

-- --------------------------------------------------------

--
-- Table structure for table `promos`
--

DROP TABLE IF EXISTS `promos`;
CREATE TABLE IF NOT EXISTS `promos` (
  `promo_id` int(11) NOT NULL AUTO_INCREMENT,
  `promo_code` varchar(50) NOT NULL,
  `promo_name` varchar(150) NOT NULL,
  `description` text DEFAULT NULL,
  `promo_target` enum('appointment','shop','both') NOT NULL DEFAULT 'appointment',
  `discount_type` enum('percentage','fixed') NOT NULL DEFAULT 'percentage',
  `discount_value` decimal(10,2) NOT NULL,
  `original_price` decimal(10,2) DEFAULT NULL,
  `promo_price` decimal(10,2) DEFAULT NULL,
  `image_path` varchar(255) DEFAULT NULL,
  `status` enum('active','inactive','expired') NOT NULL DEFAULT 'active',
  `start_date` date DEFAULT NULL,
  `end_date` date DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`promo_id`),
  UNIQUE KEY `promo_code` (`promo_code`)
) ENGINE=InnoDB AUTO_INCREMENT=6 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `promos`
--

INSERT INTO `promos` (`promo_id`, `promo_code`, `promo_name`, `description`, `promo_target`, `discount_type`, `discount_value`, `original_price`, `promo_price`, `image_path`, `status`, `start_date`, `end_date`, `created_at`) VALUES
(1, 'APPT10', 'Appointment Discount', 'Hidden appointment promo code.', 'appointment', 'percentage', 10.00, NULL, NULL, NULL, 'active', '2026-06-01', '2026-06-30', '2026-06-17 12:03:00'),
(2, 'SHOP10', 'Shop Discount', 'Hidden shop promo code.', 'shop', 'percentage', 10.00, NULL, NULL, NULL, 'active', '2026-06-01', '2026-06-30', '2026-06-17 12:03:00'),
(5, 'APPT20', '20% Discount', 'Get a 20% discount if you book now!', 'appointment', 'percentage', 20.00, NULL, NULL, NULL, 'active', '2026-06-18', '2026-06-19', '2026-06-18 01:28:33');

-- --------------------------------------------------------

--
-- Table structure for table `services`
--

DROP TABLE IF EXISTS `services`;
CREATE TABLE IF NOT EXISTS `services` (
  `service_id` int(11) NOT NULL AUTO_INCREMENT,
  `service_name` varchar(150) NOT NULL,
  `description` text DEFAULT NULL,
  `image_path` varchar(255) DEFAULT NULL,
  `price` decimal(10,2) DEFAULT NULL,
  `category` varchar(50) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `daily_slots` int(11) NOT NULL DEFAULT 0,
  `status` enum('available','unavailable') NOT NULL DEFAULT 'available',
  PRIMARY KEY (`service_id`)
) ENGINE=InnoDB AUTO_INCREMENT=11 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `services`
--

INSERT INTO `services` (`service_id`, `service_name`, `description`, `image_path`, `price`, `category`, `created_at`, `daily_slots`, `status`) VALUES
(1, 'Dental Cleaning', 'Professional prophylaxis to remove plaque and tartar.', NULL, 800.00, 'Preventive', '2026-05-23 01:55:18', 8, 'available'),
(2, 'Dental X-Ray', 'Digital X-rays for accurate diagnosis.', NULL, 450.00, 'Diagnostic', '2026-05-23 01:55:18', 8, 'available'),
(3, 'Tooth Extraction', 'Safe removal of damaged or problematic teeth.', NULL, 1200.00, 'Restorative', '2026-05-23 01:55:18', 8, 'available'),
(4, 'Teeth Whitening', 'Professional-grade whitening treatment.', NULL, 3500.00, 'Cosmetic', '2026-05-23 01:55:18', 19, 'available'),
(5, 'Dental Braces Consult', 'Orthodontic evaluation and treatment planning.', NULL, 500.00, 'Orthodontic', '2026-05-23 01:55:18', 8, 'available'),
(6, 'Root Canal Treatment', 'Precision endodontic therapy.', NULL, 6000.00, 'Restorative', '2026-05-23 01:55:18', 8, 'available'),
(7, 'Dental Crown', 'Custom-fitted porcelain crowns.', NULL, 8000.00, 'Restorative', '2026-05-23 01:55:18', 16, 'available'),
(8, 'Porcelain Veneers', 'Custom shells for aesthetic results.', NULL, 12000.00, 'Cosmetic', '2026-05-23 01:55:18', 11, 'available'),
(9, 'Pediatric Check-Up', 'Gentle dental visits for children.', NULL, 600.00, 'Preventive', '2026-05-23 01:55:18', 8, 'available');

-- --------------------------------------------------------

--
-- Table structure for table `users`
--

DROP TABLE IF EXISTS `users`;
CREATE TABLE IF NOT EXISTS `users` (
  `user_id` int(11) NOT NULL AUTO_INCREMENT,
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
  PRIMARY KEY (`user_id`),
  UNIQUE KEY `email` (`email`)
) ENGINE=InnoDB AUTO_INCREMENT=9 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `users`
--

INSERT INTO `users` (`user_id`, `first_name`, `last_name`, `email`, `phone`, `password_hash`, `role`, `created_at`, `birthdate`, `gender`, `emergency_contact_name`, `emergency_contact_number`, `house_no`, `street`, `barangay`, `city`, `province`, `zip_code`) VALUES
(3, 'Chrizyl', 'Abella', 'abellachrizyl@gmail.com', '09274213879', '$2y$10$SpxIEd7d0L3RQtveozevSO5GZfKVB5OVjsB3hVGRFDiBZLrMF/d1S', 'patient', '2026-05-23 02:53:53', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL),
(4, 'Chrizyl', 'Abella', 'hannie@gmail.com', '09274213879', '$2y$10$J50O5WjgvdrIJkITMGCDKefEnNOW2EFeP0YmlGOD6eO2MDLVRwj1e', 'patient', '2026-05-24 03:33:37', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL),
(5, 'Jeonghan', 'Jeon', 'jeon@gmail.com', '123456789', '$2y$10$y9L78.r11pQ/WOUq3psuKuw40hrIwyCiilbZqn0m.u9Q8kEKf676O', 'patient', '2026-05-24 03:40:13', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL),
(6, 'System', 'Admin', 'admin@aquasmile.com', '0000000000', '$2y$10$qnT4XmT6G85rtdowvE8eYuy0eBNaMX69YlUDnqYqNlQljmIpLqJNG', 'admin', '2026-05-26 07:27:15', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL),
(8, 'Mary Josephine', 'Magboo', 'maryjosephine076@gmail.com', '09672547242', '$2y$10$cqxtAEZuyPQtRSWolFgJye618tHSODbybdCeylU1iD2PFgoTcakE.', 'patient', '2026-06-14 06:33:02', '2006-08-07', 'Female', NULL, NULL, '1', 'Purok', 'Makiling', 'Calamba', 'LAGUNA', '4027');

--
-- Constraints for dumped tables
--

--
-- Constraints for table `appointments`
--
ALTER TABLE `appointments`
  ADD CONSTRAINT `appointments_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`user_id`) ON DELETE CASCADE,
  ADD CONSTRAINT `appointments_ibfk_2` FOREIGN KEY (`dentist_id`) REFERENCES `dentists` (`dentist_id`) ON DELETE SET NULL,
  ADD CONSTRAINT `appointments_ibfk_3` FOREIGN KEY (`service_id`) REFERENCES `services` (`service_id`) ON DELETE SET NULL,
  ADD CONSTRAINT `appointments_promo_fk` FOREIGN KEY (`promo_id`) REFERENCES `promos` (`promo_id`) ON DELETE SET NULL;

--
-- Constraints for table `cart_items`
--
ALTER TABLE `cart_items`
  ADD CONSTRAINT `cart_items_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`user_id`) ON DELETE CASCADE,
  ADD CONSTRAINT `cart_items_ibfk_2` FOREIGN KEY (`product_id`) REFERENCES `products` (`product_id`) ON DELETE CASCADE;

--
-- Constraints for table `claimed_coupons`
--
ALTER TABLE `claimed_coupons`
  ADD CONSTRAINT `claimed_coupons_promo_fk` FOREIGN KEY (`promo_id`) REFERENCES `promos` (`promo_id`) ON DELETE CASCADE,
  ADD CONSTRAINT `claimed_coupons_user_fk` FOREIGN KEY (`user_id`) REFERENCES `users` (`user_id`) ON DELETE CASCADE;

--
-- Constraints for table `feedback`
--
ALTER TABLE `feedback`
  ADD CONSTRAINT `feedback_appointment_fk` FOREIGN KEY (`appointment_id`) REFERENCES `appointments` (`appointment_id`) ON DELETE SET NULL,
  ADD CONSTRAINT `feedback_order_fk` FOREIGN KEY (`order_id`) REFERENCES `orders` (`order_id`) ON DELETE SET NULL,
  ADD CONSTRAINT `feedback_user_fk` FOREIGN KEY (`user_id`) REFERENCES `users` (`user_id`) ON DELETE CASCADE;

--
-- Constraints for table `notifications`
--
ALTER TABLE `notifications`
  ADD CONSTRAINT `notifications_order_fk` FOREIGN KEY (`order_id`) REFERENCES `orders` (`order_id`) ON DELETE SET NULL;

--
-- Constraints for table `orders`
--
ALTER TABLE `orders`
  ADD CONSTRAINT `orders_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`user_id`) ON DELETE CASCADE,
  ADD CONSTRAINT `orders_promo_fk` FOREIGN KEY (`promo_id`) REFERENCES `promos` (`promo_id`) ON DELETE SET NULL;

--
-- Constraints for table `order_items`
--
ALTER TABLE `order_items`
  ADD CONSTRAINT `order_items_ibfk_1` FOREIGN KEY (`order_id`) REFERENCES `orders` (`order_id`) ON DELETE CASCADE,
  ADD CONSTRAINT `order_items_ibfk_2` FOREIGN KEY (`product_id`) REFERENCES `products` (`product_id`) ON DELETE CASCADE;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
