-- phpMyAdmin SQL Dump
-- version 5.2.1deb3
-- https://www.phpmyadmin.net/
--
-- Host: localhost:3306
-- Generation Time: Feb 11, 2026 at 09:52 PM
-- Server version: 10.11.14-MariaDB-0ubuntu0.24.04.1
-- PHP Version: 8.3.6

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Database: `final`
--

-- --------------------------------------------------------

--
-- Table structure for table `app_settings`
--

CREATE TABLE `app_settings` (
  `key` varchar(100) NOT NULL,
  `value` varchar(255) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `app_settings`
--

INSERT INTO `app_settings` (`key`, `value`) VALUES
('pickup_time_window_hours', '24'),
('service_fee', '3.50');

-- --------------------------------------------------------

--
-- Table structure for table `orders`
--

CREATE TABLE `orders` (
  `id` int(11) NOT NULL,
  `order_number` varchar(20) DEFAULT NULL,
  `customer_id` int(11) NOT NULL,
  `restaurant_id` int(11) NOT NULL,
  `subtotal` decimal(10,2) NOT NULL DEFAULT 0.00,
  `service_fee` decimal(10,2) NOT NULL DEFAULT 0.00,
  `total_amount` decimal(10,2) NOT NULL DEFAULT 0.00,
  `pickup_from` datetime DEFAULT NULL,
  `pickup_to` datetime DEFAULT NULL,
  `total_price` decimal(10,2) NOT NULL,
  `payment_method` enum('cash','card') NOT NULL,
  `status` enum('pending','accepted','preparing','ready','completed','canceled') DEFAULT 'pending',
  `customer_name` varchar(100) DEFAULT NULL,
  `customer_phone` varchar(20) DEFAULT NULL,
  `notes` text DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `orders`
--

INSERT INTO `orders` (`id`, `order_number`, `customer_id`, `restaurant_id`, `subtotal`, `service_fee`, `total_amount`, `pickup_from`, `pickup_to`, `total_price`, `payment_method`, `status`, `customer_name`, `customer_phone`, `notes`, `created_at`, `updated_at`) VALUES
(11, 'ZAD-24696', 23, 1, 40.00, 3.50, 43.50, '2026-01-10 14:00:00', '2026-01-10 16:00:00', 43.50, 'cash', 'ready', 'test', '01022088021', 'No sugar please', '2026-01-09 11:31:54', '2026-02-07 18:17:16'),
(12, 'ZAD-10300', 23, 1, 25.00, 3.50, 28.50, '2026-01-09 15:40:00', '2026-01-09 17:25:00', 28.50, 'cash', 'completed', 'test', '01022088021', NULL, '2026-01-09 13:11:02', '2026-02-06 12:47:03'),
(13, 'ZAD-27462', 23, 1, 25.00, 3.50, 28.50, '2026-01-09 15:40:00', '2026-01-09 17:25:00', 28.50, 'cash', 'ready', 'test', '01022088021', NULL, '2026-01-09 13:11:02', '2026-02-06 15:06:18'),
(14, 'ZAD-41971', 23, 4, 14.00, 3.50, 17.50, '2026-01-09 16:23:00', '2026-01-10 16:23:00', 17.50, 'cash', 'pending', 'test', '01022088021', NULL, '2026-01-09 13:53:46', '2026-01-09 13:53:46'),
(15, 'ZAD-64154', 23, 4, 14.00, 3.50, 17.50, '2026-01-09 16:23:00', '2026-01-10 16:23:00', 17.50, 'cash', 'pending', 'test', '01022088021', NULL, '2026-01-09 13:53:46', '2026-01-09 13:53:46'),
(16, 'ZAD-29160', 23, 1, 100.00, 3.50, 103.50, '2026-01-09 03:25:00', '2026-01-11 03:25:00', 103.50, 'cash', 'ready', 'test', '01022088021', NULL, '2026-01-09 15:24:48', '2026-02-06 12:46:48'),
(17, 'ZAD-12077', 23, 1, 25.00, 3.50, 28.50, '2026-01-09 18:10:00', '2026-01-10 18:13:00', 28.50, 'cash', 'accepted', 'test', '01022088021', NULL, '2026-01-09 15:43:41', '2026-02-06 11:15:59'),
(18, 'ZAD-21846', 23, 1, 10.00, 3.50, 13.50, '2026-01-09 18:21:00', '2026-01-11 17:30:00', 13.50, 'cash', 'accepted', 'test', '01022088021', NULL, '2026-01-09 15:52:06', '2026-02-06 11:08:32'),
(19, 'ZAD-93744', 23, 1, 10.00, 3.50, 13.50, '2026-01-09 18:21:00', '2026-01-11 17:30:00', 13.50, 'cash', 'canceled', 'test', '01022088021', NULL, '2026-01-09 15:52:15', '2026-02-06 11:02:54'),
(20, 'ZAD-27955', 23, 1, 10.00, 3.50, 13.50, '2026-01-09 18:21:00', '2026-01-11 17:30:00', 13.50, 'cash', 'canceled', 'test', '01022088021', NULL, '2026-01-09 15:52:30', '2026-02-01 23:57:37'),
(21, 'ZAD-36414', 23, 1, 50.00, 3.50, 53.50, '2026-01-09 18:33:00', '2026-01-11 18:33:00', 53.50, 'cash', 'preparing', 'test', '01022088021', NULL, '2026-01-09 16:03:37', '2026-02-01 23:56:33'),
(22, 'ZAD-18280', 23, 1, 50.00, 3.50, 53.50, '2026-01-09 18:33:00', '2026-01-11 18:33:00', 53.50, 'cash', 'preparing', 'test', '01022088021', NULL, '2026-01-09 16:04:03', '2026-02-01 23:54:49'),
(23, 'ZAD-50370', 23, 1, 50.00, 3.50, 53.50, '2026-01-09 18:34:00', '2026-01-10 18:34:00', 53.50, 'cash', 'ready', 'test', '01022088021', NULL, '2026-01-09 16:05:01', '2026-02-01 23:54:39'),
(24, 'ZAD-62650', 23, 1, 50.00, 3.50, 53.50, '2026-01-09 18:34:00', '2026-01-10 18:34:00', 53.50, 'cash', 'completed', 'test', '01022088021', NULL, '2026-01-09 16:05:15', '2026-02-01 23:54:28'),
(25, 'ZAD-37927', 23, 1, 50.00, 3.50, 53.50, '2026-01-09 18:35:00', '2026-01-11 18:35:00', 53.50, 'cash', 'accepted', 'test', '01022088021', NULL, '2026-01-09 16:05:33', '2026-02-01 23:53:33'),
(26, 'ZAD-12700', 23, 1, 50.00, 3.50, 53.50, '2026-01-09 18:35:00', '2026-01-10 18:35:00', 53.50, 'cash', 'completed', 'test', '01022088021', NULL, '2026-01-09 16:05:52', '2026-02-01 23:53:57'),
(27, 'ZAD-59180', 23, 1, 40.00, 3.50, 43.50, '2026-01-09 18:35:00', '2026-01-10 18:35:00', 43.50, 'cash', 'ready', 'test', '01022088021', NULL, '2026-01-09 16:13:47', '2026-02-01 23:53:52'),
(28, 'ZAD-72078', 23, 1, 40.00, 3.50, 43.50, '2026-01-09 18:35:00', '2026-01-10 18:35:00', 43.50, 'cash', 'preparing', 'test', '01022088021', NULL, '2026-01-09 16:28:38', '2026-02-01 23:53:45'),
(29, 'ZAD-37826', 23, 1, 50.00, 3.50, 53.50, '2026-01-09 18:35:00', '2026-01-10 18:35:00', 53.50, 'cash', 'preparing', 'test', '01022088021', NULL, '2026-01-09 16:29:24', '2026-02-01 23:53:13'),
(30, 'ZAD-16509', 23, 1, 40.00, 3.50, 43.50, '2026-01-09 18:35:00', '2026-01-10 18:35:00', 43.50, 'cash', 'ready', 'test', '01022088021', NULL, '2026-01-09 16:30:15', '2026-02-01 23:52:54'),
(31, 'ZAD-66471', 23, 4, 14.00, 3.50, 17.50, '2026-01-09 19:01:00', '2026-01-11 19:01:00', 17.50, 'cash', 'pending', 'test', '01022088021', NULL, '2026-01-09 16:31:28', '2026-01-09 16:31:28'),
(32, 'ZAD-49994', 23, 1, 25.00, 3.50, 28.50, '2026-01-09 19:07:00', '2026-01-11 19:07:00', 28.50, 'cash', 'accepted', 'test', '01022088021', NULL, '2026-01-09 16:37:25', '2026-02-01 23:53:39'),
(33, 'ZAD-30558', 23, 1, 40.00, 3.50, 43.50, '2026-01-09 18:35:00', '2026-01-10 18:35:00', 43.50, 'cash', 'completed', 'test', '01022088021', NULL, '2026-01-09 16:50:49', '2026-02-01 23:52:47'),
(34, 'ZAD-88433', 23, 1, 40.00, 3.50, 43.50, '2026-01-09 18:35:00', '2026-01-10 18:35:00', 43.50, 'cash', 'accepted', 'test', '01022088021', NULL, '2026-01-09 16:51:41', '2026-02-01 23:51:37'),
(35, 'ZAD-46249', 23, 1, 40.00, 3.50, 43.50, '2026-01-09 18:35:00', '2026-01-10 18:35:00', 43.50, 'cash', 'ready', 'test', '01022088021', NULL, '2026-01-09 16:51:51', '2026-02-01 23:52:37'),
(36, 'ZAD-18798', 23, 1, 40.00, 3.50, 43.50, '2026-01-09 18:35:00', '2026-01-10 18:35:00', 43.50, 'cash', 'accepted', 'test', '01022088021', NULL, '2026-01-09 16:52:02', '2026-02-01 23:51:30'),
(37, 'ZAD-88400', 23, 1, 150.00, 3.50, 153.50, '2026-01-09 19:23:00', '2026-01-10 19:23:00', 153.50, 'cash', 'completed', 'test', '01022088021', NULL, '2026-01-09 16:53:36', '2026-02-01 23:35:46'),
(38, 'ZAD-46804', 23, 1, 40.00, 3.50, 43.50, '2026-01-09 18:35:00', '2026-01-10 18:35:00', 43.50, 'cash', 'ready', 'test', '01022088021', NULL, '2026-01-09 16:54:01', '2026-02-01 22:25:38'),
(39, 'ZAD-24815', 23, 1, 40.00, 3.50, 43.50, '2026-01-09 18:35:00', '2026-01-10 18:35:00', 43.50, 'cash', 'completed', 'test', '01022088021', NULL, '2026-01-09 16:54:46', '2026-02-01 22:16:01'),
(68, 'ZAD-11209', 28, 1, 35.00, 3.50, 38.50, '2026-02-04 13:57:00', '2026-02-05 13:57:00', 38.50, 'cash', 'preparing', 'Farid Gamal', '01020203030', NULL, '2026-02-04 11:27:12', '2026-02-06 15:15:33'),
(69, 'ZAD-57048', 28, 1, 45.00, 3.50, 48.50, '2026-02-06 17:01:00', '2026-02-06 20:01:00', 48.50, 'cash', 'canceled', 'Farid Gamal', '01020203030', NULL, '2026-02-04 14:31:45', '2026-02-05 20:07:19'),
(70, 'ZAD-88475', 28, 3, 36.00, 3.50, 39.50, '2026-02-04 18:10:00', '2026-02-06 18:10:00', 39.50, 'cash', 'pending', 'Farid Gamal', '01020203030', NULL, '2026-02-04 15:40:31', '2026-02-04 15:40:31'),
(71, 'ZAD-47097', 28, 1, 120.00, 3.50, 123.50, '2026-02-04 21:25:00', '2026-02-05 21:25:00', 123.50, 'cash', 'canceled', 'Farid Gamal', '01020203030', NULL, '2026-02-04 18:56:17', '2026-02-06 18:49:39'),
(72, 'ZAD-11250', 28, 8, 100.00, 3.50, 103.50, '2026-02-04 21:38:00', '2026-02-05 21:38:00', 103.50, 'cash', 'pending', 'Farid Gamal', '01020203030', NULL, '2026-02-04 19:08:15', '2026-02-04 19:08:15'),
(73, 'ZAD-23345', 28, 1, 6.00, 3.50, 9.50, '2026-02-05 14:04:00', '2026-02-06 14:04:00', 9.50, 'cash', 'ready', 'Farid Gamal', '01020203030', NULL, '2026-02-05 11:34:49', '2026-02-06 18:24:41'),
(74, 'ZAD-38756', 28, 8, 100.00, 3.50, 103.50, '2026-02-05 14:05:00', '2026-02-06 14:05:00', 103.50, 'cash', 'pending', 'Farid Gamal', '01020203030', NULL, '2026-02-05 11:35:21', '2026-02-05 11:35:21'),
(75, 'ZAD-60935', 28, 8, 100.00, 3.50, 103.50, '2026-02-05 16:28:00', '2026-02-06 16:28:00', 103.50, 'cash', 'pending', 'Farid Gamal', '01020203030', NULL, '2026-02-05 13:58:24', '2026-02-05 13:58:24'),
(76, 'ZAD-20812', 24, 1, 6.00, 3.50, 9.50, '2026-02-05 21:57:00', '2026-02-06 21:57:00', 9.50, 'cash', 'accepted', 'فاطمة ', '050658526', NULL, '2026-02-05 19:27:59', '2026-02-06 12:35:16'),
(77, 'ZAD-70228', 24, 1, 0.06, 3.50, 3.56, '2026-02-06 13:40:00', '2026-02-07 13:40:00', 3.56, 'cash', 'ready', 'فاطمة ', '050658526', NULL, '2026-02-06 11:10:20', '2026-02-06 12:27:29'),
(78, 'ZAD-74451', 24, 1, 0.36, 3.50, 3.86, '2026-02-06 15:16:00', '2026-02-07 15:16:00', 3.86, 'cash', 'preparing', 'فاطمة ', '050658526', NULL, '2026-02-06 12:46:23', '2026-02-06 12:46:39'),
(79, 'ZAD-25838', 28, 1, 180.00, 3.50, 183.50, '2026-02-06 15:31:00', '2026-02-07 15:31:00', 183.50, 'cash', 'ready', 'Farid Gamal', '01020203030', NULL, '2026-02-06 13:01:57', '2026-02-06 19:06:39'),
(80, 'ZAD-16084', 28, 1, 0.03, 3.50, 3.53, '2026-02-06 17:20:00', '2026-02-07 17:20:00', 3.53, 'cash', 'canceled', 'Farid Gamal', '01020203030', NULL, '2026-02-06 14:50:13', '2026-02-06 19:06:32'),
(81, 'ZAD-86827', 28, 1, 35.00, 3.50, 38.50, '2026-02-06 17:43:00', '2026-02-07 17:43:00', 38.50, 'cash', 'canceled', 'Farid Gamal', '01020203030', NULL, '2026-02-06 15:13:28', '2026-02-06 18:37:21'),
(82, 'ZAD-74315', 28, 1, 15.00, 3.50, 18.50, '2026-02-06 18:00:00', '2026-02-07 18:00:00', 18.50, 'cash', 'ready', 'Farid Gamal', '01020203030', NULL, '2026-02-06 15:30:13', '2026-02-07 18:17:45'),
(83, 'ZAD-20216', 28, 1, 6.00, 3.50, 9.50, '2026-02-06 18:52:00', '2026-02-07 18:52:00', 9.50, 'cash', 'canceled', 'Farid Gamal', '01020203030', NULL, '2026-02-06 16:22:50', '2026-02-06 19:32:20'),
(84, 'ZAD-57131', 28, 1, 0.03, 3.50, 3.53, '2026-02-06 19:46:00', '2026-02-07 19:46:00', 3.53, 'cash', 'canceled', 'Farid Gamal', '01020203030', NULL, '2026-02-06 17:16:15', '2026-02-06 19:29:00'),
(85, 'ZAD-34018', 28, 1, 0.03, 3.50, 3.53, '2026-02-06 22:04:00', '2026-02-07 22:04:00', 3.53, 'cash', 'canceled', 'Farid Gamal', '01020203030', NULL, '2026-02-06 19:34:20', '2026-02-06 19:51:50'),
(86, 'ZAD-74759', 28, 1, 0.03, 3.50, 3.53, '2026-02-07 18:52:00', '2026-02-08 18:52:00', 3.53, 'cash', 'canceled', 'Farid Gamal', '01020203030', NULL, '2026-02-07 16:22:39', '2026-02-07 16:51:53'),
(87, 'ZAD-30977', 28, 1, 6.03, 3.50, 9.53, '2026-02-07 20:46:00', '2026-02-08 20:46:00', 9.53, 'cash', 'accepted', 'Farid Gamal', '01020203030', NULL, '2026-02-07 18:16:49', '2026-02-07 18:17:08'),
(88, 'ZAD-78931', 28, 1, 0.03, 3.50, 3.53, '2026-02-08 13:13:00', '2026-02-09 13:13:00', 3.53, 'cash', 'ready', 'Farid Gamal', '01020203030', NULL, '2026-02-08 10:43:12', '2026-02-08 10:43:50'),
(89, 'ZAD-16626', 28, 1, 130.00, 3.50, 133.50, '2026-02-10 00:01:00', '2026-02-11 00:01:00', 133.50, 'cash', 'ready', 'Farid Gamal', '01020203030', NULL, '2026-02-10 21:31:57', '2026-02-10 21:32:24'),
(90, 'ZAD-89523', 28, 1, 21.00, 3.50, 24.50, '2026-02-11 15:11:00', '2026-02-12 15:12:00', 24.50, 'cash', 'ready', 'Farid Gamal', '01020203030', NULL, '2026-02-11 12:42:07', '2026-02-11 12:43:23'),
(91, 'ZAD-72607', 28, 1, 20.00, 3.50, 23.50, '2026-02-11 20:51:00', '2026-02-12 20:52:00', 23.50, 'cash', 'pending', 'Farid Gamal', '01020203030', NULL, '2026-02-11 18:22:05', '2026-02-11 18:22:05');

-- --------------------------------------------------------

--
-- Table structure for table `order_items`
--

CREATE TABLE `order_items` (
  `id` int(11) NOT NULL,
  `order_id` int(11) NOT NULL,
  `product_id` int(11) NOT NULL,
  `product_name` varchar(150) DEFAULT NULL,
  `unit_price` decimal(10,2) NOT NULL,
  `quantity` int(11) NOT NULL,
  `total_price` decimal(10,2) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `order_items`
--

INSERT INTO `order_items` (`id`, `order_id`, `product_id`, `product_name`, `unit_price`, `quantity`, `total_price`) VALUES
(20, 11, 1, 'كروسان', 20.00, 2, 40.00),
(21, 12, 7, 'كيكة ليمون', 25.00, 1, 25.00),
(22, 13, 7, 'كيكة ليمون', 25.00, 1, 25.00),
(23, 14, 8, 'حخه', 7.00, 2, 14.00),
(24, 15, 8, 'حخه', 7.00, 2, 14.00),
(25, 16, 7, 'كيكة ليمون', 25.00, 4, 100.00),
(26, 17, 7, 'كيكة ليمون', 25.00, 1, 25.00),
(27, 18, 4, 'بيكان', 10.00, 1, 10.00),
(28, 19, 4, 'بيكان', 10.00, 1, 10.00),
(29, 20, 4, 'بيكان', 10.00, 1, 10.00),
(30, 21, 7, 'كيكة ليمون', 25.00, 2, 50.00),
(31, 22, 7, 'كيكة ليمون', 25.00, 2, 50.00),
(32, 23, 7, 'كيكة ليمون', 25.00, 2, 50.00),
(33, 24, 7, 'كيكة ليمون', 25.00, 2, 50.00),
(34, 25, 7, 'كيكة ليمون', 25.00, 2, 50.00),
(35, 26, 7, 'كيكة ليمون', 25.00, 2, 50.00),
(36, 27, 1, 'كروسان', 20.00, 2, 40.00),
(37, 28, 1, 'كروسان', 20.00, 2, 40.00),
(38, 29, 7, 'كيكة ليمون', 25.00, 2, 50.00),
(39, 30, 1, 'كروسان', 20.00, 2, 40.00),
(40, 31, 8, 'حخه', 7.00, 2, 14.00),
(41, 32, 7, 'كيكة ليمون', 25.00, 1, 25.00),
(42, 33, 1, 'كروسان', 20.00, 2, 40.00),
(43, 34, 1, 'كروسان', 20.00, 2, 40.00),
(44, 35, 1, 'كروسان', 20.00, 2, 40.00),
(45, 36, 1, 'كروسان', 20.00, 2, 40.00),
(46, 37, 2, 'كيك', 50.00, 3, 150.00),
(47, 38, 1, 'كروسان', 20.00, 2, 40.00),
(48, 39, 1, 'كروسان', 20.00, 2, 40.00),
(81, 68, 9, 'دبل تشيز برجر', 35.00, 1, 35.00),
(82, 69, 10, 'بطاطس ودجز', 15.00, 3, 45.00),
(83, 70, 12, 'عصير برتقال طازج', 18.00, 2, 36.00),
(84, 71, 10, 'بطاطس ودجز', 15.00, 1, 15.00),
(85, 71, 9, 'دبل تشيز برجر', 35.00, 3, 105.00),
(86, 72, 24, 'Fish', 100.00, 1, 100.00),
(87, 73, 23, 'fyufkf', 6.00, 1, 6.00),
(88, 74, 24, 'Fish', 100.00, 1, 100.00),
(89, 75, 24, 'Fish', 100.00, 1, 100.00),
(90, 76, 23, 'fyufkf', 6.00, 1, 6.00),
(91, 77, 25, 'المرسى', 0.03, 2, 0.06),
(92, 78, 25, 'المرسى', 0.03, 12, 0.36),
(93, 79, 10, 'بطاطس ودجز', 15.00, 12, 180.00),
(94, 80, 25, 'المرسى', 0.03, 1, 0.03),
(95, 81, 9, 'دبل تشيز برجر', 35.00, 1, 35.00),
(96, 82, 10, 'بطاطس ودجز', 15.00, 1, 15.00),
(97, 83, 23, 'fyufkf', 6.00, 1, 6.00),
(98, 84, 25, 'المرسى', 0.03, 1, 0.03),
(99, 85, 25, 'المرسى', 0.03, 1, 0.03),
(100, 86, 25, 'المرسى', 0.03, 1, 0.03),
(101, 87, 25, 'المرسى', 0.03, 1, 0.03),
(102, 87, 23, 'fyufkf', 6.00, 1, 6.00),
(103, 88, 25, 'المرسى', 0.03, 1, 0.03),
(104, 89, 34, 'uy', 65.00, 2, 130.00),
(105, 90, 32, 'uy', 7.00, 3, 21.00),
(106, 91, 35, 'fyufkf', 4.00, 5, 20.00);

-- --------------------------------------------------------

--
-- Table structure for table `password_resets`
--

CREATE TABLE `password_resets` (
  `id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `otp` varchar(6) NOT NULL,
  `expires_at` datetime NOT NULL,
  `used_at` datetime DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `password_resets`
--

INSERT INTO `password_resets` (`id`, `user_id`, `otp`, `expires_at`, `used_at`, `created_at`) VALUES
(25, 23, '308380', '2026-01-14 01:31:54', NULL, '2026-01-13 23:21:54'),
(33, 24, '406582', '2026-02-08 01:43:59', NULL, '2026-02-07 23:33:59'),
(34, 28, '933340', '2026-02-08 01:49:01', '2026-02-08 01:39:48', '2026-02-07 23:39:01');

-- --------------------------------------------------------

--
-- Table structure for table `products`
--

CREATE TABLE `products` (
  `id` int(11) NOT NULL,
  `restaurant_id` int(11) DEFAULT NULL,
  `name` varchar(150) NOT NULL,
  `description` text DEFAULT NULL,
  `price` decimal(10,2) NOT NULL,
  `sale_price` decimal(10,2) DEFAULT NULL,
  `photo` varchar(255) DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT current_timestamp(),
  `quantity` int(11) NOT NULL DEFAULT 0,
  `is_active` tinyint(1) DEFAULT 1
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `products`
--

INSERT INTO `products` (`id`, `restaurant_id`, `name`, `description`, `price`, `sale_price`, `photo`, `created_at`, `quantity`, `is_active`) VALUES
(1, 1, 'كروسان', 'كروسان شوكلاتة', 20.00, 10.00, NULL, '2025-11-04 09:42:59', 0, 1),
(2, 1, 'كيك', 'كيكة توت', 50.00, 10.00, '1769990645_WhatsApp_Image_2026-02-02_at_3.03.02_AM.jpeg', '2025-11-04 09:45:52', 0, 1),
(4, 1, 'بيكان', '', 10.00, 5.00, '1769991946_WhatsApp_Image_2026-02-02_at_3.03.02_AM_(1).jpeg', '2025-11-04 10:05:11', 0, 1),
(7, 1, 'كيكة ليمون', 'ليمون', 25.00, 10.00, '1769991915_WhatsApp_Image_2026-02-02_at_3.03.02_AM_(2).jpeg', '2025-11-04 10:24:16', 0, 1),
(8, 4, 'حخه', 'لاتاىنمة', 7.00, 2.00, NULL, '2025-12-13 20:07:00', 0, 1),
(9, 1, 'دبل تشيز برجر', 'شريحتين لحم مشوي مع جبنة الشيدر والصوص الخاص', 35.00, 10.00, '1769991871_WhatsApp_Image_2026-02-02_at_3.05.31_AM_(1).jpeg', '2026-02-01 20:55:46', 0, 1),
(10, 1, 'بطاطس ودجز', 'بطاطس ودجز متبلة ومشوية', 15.00, 5.00, '1769991797_WhatsApp_Image_2026-02-02_at_3.05.31_AM.jpeg', '2026-02-01 20:55:46', 0, 1),
(11, 2, 'بيتزا بيبيروني', 'عجينة إيطالية رقيقة مع صلصة الطماطم وجبنة الموزاريلا والبيبيروني', 45.00, NULL, 'images/pizza_pep.jpg', '2026-02-01 20:55:46', 0, 1),
(12, 3, 'عصير برتقال طازج', 'عصير برتقال طبيعي 100% بدون سكر', 18.00, 15.00, 'images/orange_juice.jpg', '2026-02-01 20:55:46', 0, 1),
(13, 3, 'موهيتو فراولة', 'مشروب غازي منعش بنكهة الفراولة والنعناع والليمون', 22.00, NULL, 'images/mojito_berry.jpg', '2026-02-01 20:55:46', 0, 1),
(14, 4, 'سبانيش لاتيه بارد', 'قهوة اسبريسو مع الحليب المكثف والثلج', 24.00, 20.00, 'images/spanish_latte.jpg', '2026-02-01 20:55:46', 0, 1),
(15, 4, 'كابتشينو', 'قهوة اسبريسو ساخنة مع رغوة الحليب الكثيفة', 16.00, NULL, 'images/cappuccino.jpg', '2026-02-01 20:55:46', 0, 1),
(16, 5, 'صحن شاورما عربي', 'قطع شاورما دجاج بخبز الصاج مع الثوم والبطاطس والمخلل', 28.00, NULL, 'images/shawarma_plate.jpg', '2026-02-01 20:55:46', 0, 1),
(17, 6, 'كيكة العسل', 'طبقات من كيك العسل الروسي مع الكريمة الخفيفة', 25.00, 18.00, 'images/honey_cake.jpg', '2026-02-01 20:55:46', 0, 1),
(18, 6, 'وافل نوتيلا', 'وافل بلجيكي مقرمش مغطى بشوكولاتة النوتيلا والفراولة', 20.00, NULL, 'images/waffle.jpg', '2026-02-01 20:55:46', 0, 1),
(19, 7, 'سلطة سيزر بالدجاج', 'خس طازج مع قطع دجاج مشوي وجبنة بارميزان وصوص السيزر', 32.00, NULL, 'images/caesar_salad.jpg', '2026-02-01 20:55:46', 0, 1),
(21, 9, 'آيس كريم مانجو', 'كوب آيس كريم طبيعي بنكهة المانجو', 14.00, NULL, 'images/icecream_mango.jpg', '2026-02-01 20:55:46', 0, 1),
(22, 10, 'وجبة فيليه سمك', 'قطع سمك فيليه مقلية مع الأرز والصيادية', 55.00, 45.00, 'images/fish_fillet.jpg', '2026-02-01 20:55:46', 0, 1),
(23, 1, 'fyufkf', 'gckhkc', 6.00, 5.00, '1770223231_anonimostory.com_Instagram_francis.york_3765788006402280771_4183305032.jpeg', '2026-02-04 13:18:09', 0, 1),
(24, 8, 'Fish', 'Salamon fish', 100.00, 55.00, '1770232030_anonimostory.com_Instagram_francis.york_3765788006419036490_4183305032.jpeg', '2026-02-04 19:07:10', 0, 1),
(25, 1, 'المرسى', 'kn', 0.03, 0.01, '1770325615_anonimostory.com_Instagram_francis.york_3765788006402256305_4183305032.jpeg', '2026-02-05 21:06:55', 0, 1),
(26, 1, 'ertert', 'egfvsdfvgdb', 5.00, 3.00, '1770726683_anonimostory.com_Instagram_francis.york_3765788006393840588_4183305032.jpeg', '2026-02-10 12:31:23', 0, 1),
(27, 1, 'wrtewrt', 'ertg rr', 5.00, 4.00, '1770726932_anonimostory.com_Instagram_francis.york_3765788006695894195_4183305032.jpeg', '2026-02-10 12:35:32', 0, 1),
(32, 1, 'uy', '8jj', 7.00, NULL, '1770728276_for-sale-renovated-farmhouse-masia-in-girona-spain+(21).webp', '2026-02-10 12:57:56', 0, 1),
(34, 1, 'uy', 'fdhhbgh', 65.00, NULL, '1770748845_for-sale-renovated-farmhouse-masia-in-girona-spain+(6).webp', '2026-02-10 18:40:45', 0, 1),
(35, 1, 'fyufkf', 'يبلايبى', 4.00, NULL, '1770833999_anonimostory.com_Instagram_francis.york_3765788006393840588_4183305032.jpeg', '2026-02-11 18:19:59', 0, 1);

-- --------------------------------------------------------

--
-- Table structure for table `restaurants`
--

CREATE TABLE `restaurants` (
  `id` int(11) NOT NULL,
  `name` varchar(255) NOT NULL,
  `logo` varchar(255) DEFAULT NULL,
  `owner_id` int(11) NOT NULL,
  `address` varchar(255) DEFAULT NULL,
  `description` text DEFAULT NULL,
  `phone` varchar(50) DEFAULT NULL,
  `working_hours` varchar(100) DEFAULT NULL,
  `status` enum('active','inactive') DEFAULT 'active',
  `created_at` timestamp NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `restaurants`
--

INSERT INTO `restaurants` (`id`, `name`, `logo`, `owner_id`, `address`, `description`, `phone`, `working_hours`, `status`, `created_at`) VALUES
(1, 'المرسى', 'logo_1770325677.jpeg', 2, '', NULL, '', '10:28 - 22:29', 'active', '2025-11-04 15:10:23'),
(3, 'شمندر', 'logo_1762338243.png', 6, 'عثمان بن عفان', '', '0551215999', '10:00 - 23:30', 'active', '2025-11-05 10:14:54'),
(4, 'Rest_test', NULL, 11, '123St. 78Route, texas', '', NULL, NULL, 'active', '2025-12-13 19:45:38'),
(5, 'MC', NULL, 25, '37 A- Kli', '', NULL, NULL, 'active', '2026-01-11 11:57:20'),
(6, 'ماك', NULL, 26, 'بريدة - حي النهضة - شارع عثمان بن عفان', NULL, NULL, NULL, 'active', '2026-02-02 02:49:12'),
(7, 'yukiyu', 'logo_1770223188.jpeg', 29, '', NULL, '', '03:45 - 19:09', 'active', '2026-02-04 13:56:19'),
(8, 'Nile', 'logo_1770231972.jpeg', 30, '', NULL, '', '04:06 - 21:32', 'active', '2026-02-04 19:05:16');

-- --------------------------------------------------------

--
-- Table structure for table `restaurant_requests`
--

CREATE TABLE `restaurant_requests` (
  `id` int(11) NOT NULL,
  `owner_name` varchar(100) DEFAULT NULL,
  `phone` varchar(50) DEFAULT NULL,
  `email` varchar(100) DEFAULT NULL,
  `password` varchar(255) DEFAULT NULL,
  `restaurant_name` varchar(100) DEFAULT NULL,
  `address` varchar(255) DEFAULT NULL,
  `description` text DEFAULT NULL,
  `status` enum('pending','approved','rejected') DEFAULT 'pending',
  `reviewed_by` int(11) DEFAULT NULL,
  `reviewed_at` datetime DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT current_timestamp(),
  `proofs` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL CHECK (json_valid(`proofs`))
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `restaurant_requests`
--

INSERT INTO `restaurant_requests` (`id`, `owner_name`, `phone`, `email`, `password`, `restaurant_name`, `address`, `description`, `status`, `reviewed_by`, `reviewed_at`, `created_at`, `proofs`) VALUES
(1, 'ناصر', '0565895255', 'mugr511@gmail.com', '$2y$10$w4krYQXe2rkKs1NJb31AWu0Lcb3NRQlf3Jmyt0.YVd8o8IIahT4Pm', 'شمندر', 'عثمان بن عفان', '', 'approved', 1, '2025-11-05 13:14:54', '2025-11-04 18:12:13', NULL),
(2, 'RTest', '1234567', 'rest@test.com', '$2y$12$UYNEYKhiA4ElbTAwF6oNsOh31jtHnCPmRWZ3yE8JaQNe34qTqYF2m', 'Rest_test', '123St. 78Route, texas', '', 'approved', 1, '2025-12-13 21:45:38', '2025-12-13 19:45:06', NULL),
(3, 'ghjf', '8861234567', 'test@rest.com', '$2y$12$hQxWTRRuNzU3vnVf.WdJOefr3gm18oyQJL1a/5DV7Pe0gnFFgEEiS', 'MC', '37 A- Kli', '', 'approved', 1, '2026-01-11 13:57:20', '2026-01-11 11:55:27', NULL),
(4, 'جهاد', '055693885', 'drt@gmai.com', '$2y$10$n2wuoWUdVchLnAzInxO42u538m42.HNsKai8NzPxONMk3wQHIOEk.', 'ماك', 'بريدة - حي النهضة - شارع عثمان بن عفان', NULL, 'approved', 1, '2026-02-02 05:49:12', '2026-02-02 02:40:08', '[\"proof_1770000008_71a37674.jpg\"]'),
(5, 'جهاد', '0553658455', 'almustafa77sd@gmail.com', '$2y$10$dCJEMoCpeI5NNEZAuGrCeO5Bx/3b8BK77uO.8SAFhe7EWDCqqDdZ6', 'ماك', 'N/A', NULL, 'rejected', 1, '2026-02-02 05:49:02', '2026-02-02 02:47:28', '[\"proof_1770000448_14fdb1ab.png\"]'),
(6, 'rud', 'dkduk', 'f@d', '$2y$10$i6RyOea44TBJxX45I3OsEu7pfb4CevoHs.l7YgJbSNGXh.TsQTasm', 'yukiyu', 'djdddj', NULL, 'approved', 1, '2026-02-04 15:56:19', '2026-02-04 13:55:28', '[\"proof_1770213328_958c2c58.jpeg\"]'),
(7, 'Fatima', '0902627739', 'restaurant@gmail.com', '$2y$10$L35pZDf/yaaWDmH..a.pzuBzFzlo6kkBFT9yVkQJpmvMF2Ad7Fa4i', 'Nile', 'Khartoum, East Nile', NULL, 'approved', 1, '2026-02-04 21:05:16', '2026-02-04 19:04:42', '[\"proof_1770231882_185d4860.jpeg\"]');

-- --------------------------------------------------------

--
-- Table structure for table `users`
--

CREATE TABLE `users` (
  `id` int(11) NOT NULL,
  `name` varchar(100) NOT NULL,
  `email` varchar(150) NOT NULL,
  `password` varchar(255) NOT NULL,
  `phone` varchar(20) DEFAULT NULL,
  `last_login` datetime DEFAULT NULL,
  `role` enum('admin','owner','customer') NOT NULL,
  `created_at` timestamp NULL DEFAULT current_timestamp(),
  `avatar` varchar(255) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `users`
--

INSERT INTO `users` (`id`, `name`, `email`, `password`, `phone`, `last_login`, `role`, `created_at`, `avatar`) VALUES
(1, 'MUSTAFA ahmed', 'almustafa77sd@gmail.com', '$2y$10$nex8kq/qYfjLtK/0q/3E3OQ231OXqYJM7fn8P9bVZArS1Choi1yAK', NULL, '2026-02-04 21:04:52', 'admin', '2025-11-04 09:01:34', NULL),
(2, 'فريد', 'fared@gmail.com', '$2y$10$uU/keQV.o1CLlO27iLlQduObriM7kshJ7LPLo70vwD/lNxtSG/c5O', NULL, '2026-02-11 23:50:55', 'owner', '2025-11-04 13:50:01', NULL),
(6, 'ناصر', 'mugr511@gmail.com', '$2y$10$nex8kq/qYfjLtK/0q/3E3OQ231OXqYJM7fn8P9bVZArS1Choi1yAK', '0565895255', '2025-11-05 13:16:23', 'owner', '2025-11-05 10:14:54', NULL),
(8, 'خالد', 'h', '$2y$10$nex8kq/qYfjLtK/0q/3E3OQ231OXqYJM7fn8P9bVZArS1Choi1yAK', NULL, NULL, 'customer', '2025-12-12 13:37:22', NULL),
(9, 'محمد', '', '$2y$10$nex8kq/qYfjLtK/0q/3E3OQ231OXqYJM7fn8P9bVZArS1Choi1yAK', NULL, NULL, 'customer', '2025-12-12 16:23:36', NULL),
(11, 'لؤي', 'rest@test.com', '$2y$10$nex8kq/qYfjLtK/0q/3E3OQ231OXqYJM7fn8P9bVZArS1Choi1yAK', '1234567', '2026-01-11 23:43:05', 'owner', '2025-12-13 19:45:38', NULL),
(12, 'مصعب', 'cgh@test.com', '$2y$10$nex8kq/qYfjLtK/0q/3E3OQ231OXqYJM7fn8P9bVZArS1Choi1yAK', '05058500935', NULL, 'customer', '2025-12-15 09:32:04', NULL),
(13, 'عبدالله', 'rgh@gdgh.con', '$2y$10$nex8kq/qYfjLtK/0q/3E3OQ231OXqYJM7fn8P9bVZArS1Choi1yAK', '5968588855', NULL, 'customer', '2025-12-18 11:33:42', NULL),
(23, 'وليد', 'test1@test.com', '$2y$10$nex8kq/qYfjLtK/0q/3E3OQ231OXqYJM7fn8P9bVZArS1Choi1yAK', '0102208810', NULL, 'customer', '2025-12-25 11:04:05', '/uploads/avatars/avatar_23_1768340771.jpg'),
(24, 'فاطمة ', 'farid.jamaleldin@gmail.com', '$2y$10$b7Bg8DpkgAaJCHBv6BNyLeyOhab9P1gvkVBy6u26ehmmMTM9mRV6m', '050658526', NULL, 'customer', '2026-01-01 10:00:47', NULL),
(25, 'عائشة', 'test@rest.com', '$2y$10$nex8kq/qYfjLtK/0q/3E3OQ231OXqYJM7fn8P9bVZArS1Choi1yAK', '8861234567', NULL, 'owner', '2026-01-11 11:57:20', NULL),
(26, 'جهاد', 'drt@gmai.com', '$2y$10$n2wuoWUdVchLnAzInxO42u538m42.HNsKai8NzPxONMk3wQHIOEk.', '055693885', NULL, 'owner', '2026-02-02 02:49:12', NULL),
(27, 'kalid', 'moht@gmail.com', '$2y$10$q9lem7jbIEi48QahloHp9OHzGP4xcRLAOhYMdXYSbPfauSxB2isNG', '050685555', NULL, 'customer', '2026-02-02 11:48:54', NULL),
(28, 'Farid Gamal', 'farid.jamaleldinpp@gmail.com', '$2y$10$We0BAEUAugkwv/YWbFpCVuxEbJM7MYlEPt4foPQqn9bMcgtJhrFIS', '01020203030', NULL, 'customer', '2026-02-04 11:25:02', '/uploads/avatars/avatar_28_1770204350.jpg'),
(29, 'rud', 'f@d', '$2y$10$i6RyOea44TBJxX45I3OsEu7pfb4CevoHs.l7YgJbSNGXh.TsQTasm', 'dkduk', '2026-02-04 18:38:11', 'owner', '2026-02-04 13:56:19', NULL),
(30, 'Fatima', 'restaurant@gmail.com', '$2y$10$L35pZDf/yaaWDmH..a.pzuBzFzlo6kkBFT9yVkQJpmvMF2Ad7Fa4i', '0902627739', '2026-02-04 21:05:35', 'owner', '2026-02-04 19:05:16', NULL),
(31, 'new Farid', 'farid.jamaleldi@gmail.com', '$2y$10$bXw9RsMHdhERvHGjW/Kf9OPynuIT8yEK5CbMtM0rXEfa2cAb8VgZO', '249902627739', NULL, 'customer', '2026-02-05 16:40:04', NULL),
(32, 'farsf', 'fagag@ahshdh.com', '$2y$10$WjLGcql2c1q4ZXrVjC/hqugLDiGXJK34f5g/MgW9ZK2usaAIkBz.S', '249857646464', NULL, 'customer', '2026-02-07 22:46:06', NULL);

-- --------------------------------------------------------

--
-- Table structure for table `user_fcm_tokens`
--

CREATE TABLE `user_fcm_tokens` (
  `id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `fcm_token` varchar(255) NOT NULL,
  `created_at` timestamp NULL DEFAULT current_timestamp(),
  `lang` varchar(5) DEFAULT 'en'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_bin;

--
-- Dumping data for table `user_fcm_tokens`
--

INSERT INTO `user_fcm_tokens` (`id`, `user_id`, `fcm_token`, `created_at`, `lang`) VALUES
(14, 28, 'eV2uRkI6RLeYczw_N3LiGv:APA91bHUGY4RKBtqUC_XX0GeYktfEQ6Cy7ONC1hz2tp4pVN87dke8jAaIKW1vgnK0BLMfoGkeOHPLyiqMdBDCvA4ZspIGWDRJF44644sCVWIZsPR3aov4-8', '2026-02-06 17:15:34', 'en');

--
-- Indexes for dumped tables
--

--
-- Indexes for table `app_settings`
--
ALTER TABLE `app_settings`
  ADD PRIMARY KEY (`key`);

--
-- Indexes for table `orders`
--
ALTER TABLE `orders`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `uniq_order_number` (`order_number`),
  ADD KEY `fk_order_customer` (`customer_id`),
  ADD KEY `fk_order_restaurant` (`restaurant_id`);

--
-- Indexes for table `order_items`
--
ALTER TABLE `order_items`
  ADD PRIMARY KEY (`id`),
  ADD KEY `fk_item_order` (`order_id`),
  ADD KEY `fk_item_product` (`product_id`);

--
-- Indexes for table `password_resets`
--
ALTER TABLE `password_resets`
  ADD PRIMARY KEY (`id`),
  ADD KEY `user_id` (`user_id`),
  ADD KEY `otp` (`otp`);

--
-- Indexes for table `products`
--
ALTER TABLE `products`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `restaurants`
--
ALTER TABLE `restaurants`
  ADD PRIMARY KEY (`id`),
  ADD KEY `owner_id` (`owner_id`);

--
-- Indexes for table `restaurant_requests`
--
ALTER TABLE `restaurant_requests`
  ADD PRIMARY KEY (`id`),
  ADD KEY `fk_reviewed_by` (`reviewed_by`);

--
-- Indexes for table `users`
--
ALTER TABLE `users`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `email` (`email`);

--
-- Indexes for table `user_fcm_tokens`
--
ALTER TABLE `user_fcm_tokens`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `unique_user` (`user_id`),
  ADD UNIQUE KEY `unique_user_token` (`user_id`,`fcm_token`) USING HASH;

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `orders`
--
ALTER TABLE `orders`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=92;

--
-- AUTO_INCREMENT for table `order_items`
--
ALTER TABLE `order_items`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=107;

--
-- AUTO_INCREMENT for table `password_resets`
--
ALTER TABLE `password_resets`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=35;

--
-- AUTO_INCREMENT for table `products`
--
ALTER TABLE `products`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=36;

--
-- AUTO_INCREMENT for table `restaurants`
--
ALTER TABLE `restaurants`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=9;

--
-- AUTO_INCREMENT for table `restaurant_requests`
--
ALTER TABLE `restaurant_requests`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=8;

--
-- AUTO_INCREMENT for table `users`
--
ALTER TABLE `users`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=33;

--
-- AUTO_INCREMENT for table `user_fcm_tokens`
--
ALTER TABLE `user_fcm_tokens`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=47;

--
-- Constraints for dumped tables
--

--
-- Constraints for table `orders`
--
ALTER TABLE `orders`
  ADD CONSTRAINT `fk_order_customer` FOREIGN KEY (`customer_id`) REFERENCES `users` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `fk_order_restaurant` FOREIGN KEY (`restaurant_id`) REFERENCES `restaurants` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `order_items`
--
ALTER TABLE `order_items`
  ADD CONSTRAINT `fk_item_order` FOREIGN KEY (`order_id`) REFERENCES `orders` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `fk_item_product` FOREIGN KEY (`product_id`) REFERENCES `products` (`id`);

--
-- Constraints for table `restaurants`
--
ALTER TABLE `restaurants`
  ADD CONSTRAINT `restaurants_ibfk_1` FOREIGN KEY (`owner_id`) REFERENCES `users` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `restaurant_requests`
--
ALTER TABLE `restaurant_requests`
  ADD CONSTRAINT `fk_reviewed_by` FOREIGN KEY (`reviewed_by`) REFERENCES `users` (`id`) ON DELETE SET NULL;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
