-- phpMyAdmin SQL Dump
-- version 5.2.2
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1:3306
-- Generation Time: Nov 24, 2025 at 06:32 AM
-- Server version: 11.8.3-MariaDB-log
-- PHP Version: 7.2.34

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Database: `u208597444_van_booking`
--

-- --------------------------------------------------------

--
-- Table structure for table `admin`
--

CREATE TABLE `admin` (
  `admin_id` int(11) NOT NULL,
  `username` varchar(50) NOT NULL,
  `password` varchar(200) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `admin`
--

INSERT INTO `admin` (`admin_id`, `username`, `password`) VALUES
(1, 'admin', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi');

-- --------------------------------------------------------

--
-- Table structure for table `booking`
--

CREATE TABLE `booking` (
  `booking_id` int(11) NOT NULL,
  `passenger_id` int(11) NOT NULL,
  `trip_id` int(11) NOT NULL,
  `sched_id` int(11) NOT NULL,
  `driver_id` int(11) NOT NULL,
  `seat` varchar(200) NOT NULL,
  `drop_off` varchar(300) NOT NULL,
  `pick_up` varchar(300) NOT NULL,
  `payment_method` varchar(30) NOT NULL,
  `pick_up_longitude` decimal(11,8) DEFAULT NULL,
  `pick_up_latitude` decimal(11,8) DEFAULT NULL,
  `payment_status` varchar(30) DEFAULT 'pending',
  `status` varchar(30) DEFAULT 'pending',
  `total_fare` double NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `payment_url` varchar(500) DEFAULT NULL COMMENT 'Payment gateway URL for completing payment',
  `payment_reference` varchar(100) DEFAULT NULL COMMENT 'External payment reference ID from gateway'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `booking`
--

INSERT INTO `booking` (`booking_id`, `passenger_id`, `trip_id`, `sched_id`, `driver_id`, `seat`, `drop_off`, `pick_up`, `payment_method`, `pick_up_longitude`, `pick_up_latitude`, `payment_status`, `status`, `total_fare`, `created_at`, `payment_url`, `payment_reference`) VALUES
(128, 30, 32, 254, 11, 'any', 'Ozamiz, Misamis Occidental, Philippines', 'Molave, Zamboanga del Sur, Philippines', 'gcash', 123.50323165, 8.08840168, 'paid', 'confirmed', 2, '2025-11-24 00:36:54', 'https://checkout.xendit.co/web/6923a8a7434009fc56126143', '6923a8a7434009fc56126143'),
(130, 30, 32, 333, 11, 'any', 'Ozamiz, Misamis Occidental, Philippines', 'Labuyo, Tangub, Misamis Occidental, Philippines', 'cash', 123.73811650, 8.07785732, 'pending', 'pending', 1, '2025-11-24 04:30:29', NULL, NULL),
(131, 30, 32, 334, 11, 'any', 'Labuyo, Tangub, Misamis Occidental, Philippines', 'Aquino, Tangub, Misamis Occidental, Philippines', 'gcash', 123.72117000, 8.05656700, 'paid', 'confirmed', 1, '2025-11-24 04:35:18', 'https://checkout.xendit.co/web/6923e086b116bff78b1c2e74', '6923e086b116bff78b1c2e74'),
(132, 30, 33, 298, 12, 'any', 'Ozamiz', 'Labuyo', 'cash', 123.71911079, 8.06321793, 'pending', 'confirmed', 1, '2025-11-24 05:09:02', NULL, NULL),
(133, 31, 39, 339, 12, 'any', 'Lorenzo Tan, Tangub, Misamis Occidental, Philippines', 'Tangub, Misamis Occidental, Philippines', 'cash', 123.74993000, 8.06310300, 'pending', 'pending', 1, '2025-11-24 05:17:10', NULL, NULL),
(134, 37, 32, 254, 11, 'front', 'Dimaluna, Ozamiz, Misamis Occidental, Philippines', 'Aquino, Tangub, Misamis Occidental, Philippines', 'cash', 123.72117000, 8.05656700, 'pending', 'cancelled', 32, '2025-11-24 05:34:34', NULL, NULL),
(135, 36, 32, 254, 11, 'any', 'Labuyo', 'Liloan, Bonifacio, Misamis Occidental, Philippines', 'cash', 123.56451400, 8.05249500, 'paid', 'completed', 1, '2025-11-24 05:35:06', NULL, NULL),
(136, 37, 32, 333, 11, 'any', 'Tabid, Ozamiz, Misamis Occidental, Philippines', 'Labuyo, Tangub, Misamis Occidental, Philippines', 'gcash', 123.71908000, 8.06324400, 'pending', 'cancelled', 70, '2025-11-24 05:36:00', 'https://checkout.xendit.co/web/6923eec038e12ebd57ea489d', '6923eec038e12ebd57ea489d'),
(137, 37, 32, 333, 11, 'any', 'Tabid', 'Lorenzo Tan', 'cash', 123.70430293, 8.05793231, 'pending', 'confirmed', 76, '2025-11-24 05:48:07', NULL, NULL),
(138, 36, 32, 254, 11, 'any', 'Labuyo, Tangub, Misamis Occidental, Philippines', 'Liloan Bonifacio ', 'cash', 123.56451400, 8.05249500, 'pending', 'cancelled', 1, '2025-11-24 05:55:49', NULL, NULL),
(139, 30, 39, 339, 12, 'any', 'Ramon Magsaysay – Bobongan Road Poblacion, Ramon Magsaysay, Zamboanga del Sur, Philippines', 'Tangub, Misamis Occidental, Philippines', 'cash', 123.73269649, 8.06571352, 'pending', 'confirmed', 1, '2025-11-24 06:08:02', NULL, NULL);

-- --------------------------------------------------------

--
-- Table structure for table `discount`
--

CREATE TABLE `discount` (
  `disc_id` int(11) NOT NULL,
  `type` varchar(30) NOT NULL,
  `price` double NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `discount`
--

INSERT INTO `discount` (`disc_id`, `type`, `price`) VALUES
(7, 'Senior Citizen', 20),
(8, 'Student', 99),
(9, 'Regular', 0),
(10, 'PWD', 20);

-- --------------------------------------------------------

--
-- Table structure for table `driver`
--

CREATE TABLE `driver` (
  `driver_id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `first_name` varchar(100) NOT NULL,
  `last_name` varchar(100) NOT NULL,
  `dob` date NOT NULL,
  `age` int(3) NOT NULL,
  `address` varchar(300) NOT NULL,
  `phone_number` varchar(100) NOT NULL,
  `license_number` varchar(10) NOT NULL,
  `status` varchar(30) DEFAULT 'active',
  `profile_url` varchar(500) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `driver`
--

INSERT INTO `driver` (`driver_id`, `user_id`, `first_name`, `last_name`, `dob`, `age`, `address`, `phone_number`, `license_number`, `status`, `profile_url`) VALUES
(10, 45, 'Driver2', 'Driver2', '2003-10-14', 22, 'De-Asis, Kapatagan', '08xxxxxxxxxxx', 'LN-11111', 'Active', 'assets/image/driverProfile/driver_10_6922c3f0620621.94588789.jpg'),
(11, 46, 'Driver3', 'Test3', '2000-01-01', 25, 'Aquino, Tangub City', '07xxxxxxxxx', 'LN-33333', 'Active', 'assets/image/driverProfile/driver_11_6923f9381ba3c4.48135856.png'),
(12, 50, 'Driver4', 'Test4', '2012-08-22', 13, 'Aquino', '09xxxxxxxx', 'LN-9999', 'Active', 'assets/image/driverProfile/driver_69212cfface669.10842172.png');

-- --------------------------------------------------------

--
-- Table structure for table `fare`
--

CREATE TABLE `fare` (
  `fare_id` int(11) NOT NULL,
  `base_fare` double NOT NULL,
  `base_km` int(11) NOT NULL,
  `per_km_rate` double NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `fare`
--

INSERT INTO `fare` (`fare_id`, `base_fare`, `base_km`, `per_km_rate`) VALUES
(1, 12, 4, 2);

-- --------------------------------------------------------

--
-- Table structure for table `location`
--

CREATE TABLE `location` (
  `location_id` int(11) NOT NULL,
  `latitude` decimal(11,8) NOT NULL,
  `longitude` decimal(11,8) NOT NULL,
  `driver_id` int(11) NOT NULL,
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `location`
--

INSERT INTO `location` (`location_id`, `latitude`, `longitude`, `driver_id`, `updated_at`) VALUES
(7, 7.89000350, 123.79262290, 10, '2025-11-24 05:15:49'),
(8, 8.05361350, 123.56473240, 11, '2025-11-24 06:06:03'),
(9, 8.05361400, 123.56473410, 12, '2025-11-24 06:15:53');

-- --------------------------------------------------------

--
-- Table structure for table `logs`
--

CREATE TABLE `logs` (
  `log_id` int(11) NOT NULL,
  `admin_id` int(11) NOT NULL,
  `action` varchar(50) NOT NULL,
  `ip_address` varchar(45) DEFAULT NULL,
  `user_agent` varchar(255) DEFAULT NULL,
  `created_at` datetime NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `logs`
--

INSERT INTO `logs` (`log_id`, `admin_id`, `action`, `ip_address`, `user_agent`, `created_at`) VALUES
(1, 1, 'login', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Safari/537.36', '2025-11-22 10:50:56'),
(2, 1, 'login', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Safari/537.36', '2025-11-22 11:25:28'),
(3, 1, 'login', '49.145.231.245', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Safari/537.36', '2025-11-22 04:17:56'),
(4, 1, 'login', '49.145.231.245', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Safari/537.36', '2025-11-22 04:26:33'),
(5, 1, 'login', '49.145.231.245', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Safari/537.36', '2025-11-22 04:42:23'),
(6, 1, 'login', '49.145.231.245', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Safari/537.36', '2025-11-22 04:46:20'),
(7, 1, 'login', '49.145.231.245', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Safari/537.36', '2025-11-22 05:21:14'),
(8, 1, 'login', '49.145.231.245', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Safari/537.36', '2025-11-22 07:40:00'),
(9, 1, 'login', '49.145.231.245', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Safari/537.36', '2025-11-22 10:19:08'),
(10, 1, 'login', '49.145.231.245', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Safari/537.36', '2025-11-22 10:19:09'),
(11, 1, 'login', '49.145.231.245', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Safari/537.36', '2025-11-22 12:30:25'),
(12, 1, 'login', '49.145.231.245', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Safari/537.36', '2025-11-22 23:43:40'),
(13, 1, 'login', '49.145.231.245', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Safari/537.36', '2025-11-23 05:24:22'),
(14, 1, 'login', '49.148.137.116', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Safari/537.36', '2025-11-23 07:02:07'),
(15, 1, 'login', '49.148.137.116', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Safari/537.36', '2025-11-23 08:10:51'),
(16, 1, 'login', '49.148.137.116', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Safari/537.36', '2025-11-23 08:15:53'),
(17, 1, 'login', '49.148.137.116', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Safari/537.36', '2025-11-23 08:20:17'),
(18, 1, 'login', '49.145.231.245', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Safari/537.36', '2025-11-23 10:01:57'),
(19, 1, 'login', '49.148.137.116', 'Mozilla/5.0 (X11; Linux x86_64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/137.0.0.0 Safari/537.36', '2025-11-23 10:24:58'),
(20, 1, 'login', '49.148.137.116', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Safari/537.36', '2025-11-23 10:31:06'),
(21, 1, 'login', '49.145.231.245', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Safari/537.36', '2025-11-24 00:27:17'),
(22, 1, 'login', '49.145.231.245', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Safari/537.36', '2025-11-24 01:57:13'),
(23, 1, 'login', '49.148.137.116', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Safari/537.36', '2025-11-24 02:51:26'),
(24, 1, 'login', '2001:4455:2c7:6000:e968:133f:7e74:ce2a', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Safari/537.36', '2025-11-24 03:50:34'),
(25, 1, 'login', '180.190.46.64', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Safari/537.36', '2025-11-24 05:11:46');

-- --------------------------------------------------------

--
-- Table structure for table `message`
--

CREATE TABLE `message` (
  `msg_id` int(11) NOT NULL,
  `sender_id` int(11) NOT NULL,
  `receiver_id` int(11) NOT NULL,
  `message` text NOT NULL,
  `is_read` tinyint(1) DEFAULT 0,
  `created_at` datetime DEFAULT current_timestamp(),
  `updated_at` timestamp NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `message`
--

INSERT INTO `message` (`msg_id`, `sender_id`, `receiver_id`, `message`, `is_read`, `created_at`, `updated_at`) VALUES
(6, 47, 46, 'hello', 1, '2025-11-24 00:58:40', '2025-11-24 05:51:45'),
(7, 46, 47, 'Hello', 0, '2025-11-24 05:51:49', '2025-11-24 05:51:49'),
(8, 46, 54, 'e track daw kol', 0, '2025-11-24 05:59:18', '2025-11-24 05:59:18'),
(9, 46, 54, 'send me message kol if you read this', 0, '2025-11-24 06:00:35', '2025-11-24 06:00:35'),
(10, 47, 50, 'manaaaaa', 1, '2025-11-24 06:08:12', '2025-11-24 06:08:31'),
(11, 50, 47, 'Na confirm na', 1, '2025-11-24 06:08:39', '2025-11-24 06:08:39');

-- --------------------------------------------------------

--
-- Table structure for table `message_deletion_log`
--

CREATE TABLE `message_deletion_log` (
  `log_id` int(11) NOT NULL,
  `msg_id` int(11) NOT NULL,
  `sender_id` int(11) NOT NULL,
  `deleted_by` int(11) NOT NULL,
  `deleted_at` timestamp NULL DEFAULT current_timestamp(),
  `original_message` text NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `notification`
--

CREATE TABLE `notification` (
  `notif_id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `title` varchar(256) NOT NULL,
  `message` text NOT NULL,
  `is_read` tinyint(1) DEFAULT 0,
  `created_at` datetime DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `notification`
--

INSERT INTO `notification` (`notif_id`, `user_id`, `title`, `message`, `is_read`, `created_at`) VALUES
(5, 48, 'Booking Confirmed', 'Your booking has been created successfully.', 0, '2025-11-22 06:04:07'),
(6, 48, 'Booking Status Updated', 'Your booking status has been updated to: Confirmed', 0, '2025-11-22 06:04:58'),
(7, 48, 'Booking Status Updated', 'Your booking status has been updated to: Ongoing', 0, '2025-11-22 06:06:28'),
(8, 48, 'Booking Status Updated', 'Your booking status has been updated to: Completed', 0, '2025-11-22 06:06:33'),
(74, 48, 'Booking Confirmed', 'Your booking has been created successfully.', 0, '2025-11-23 06:17:44'),
(75, 48, 'Booking Status Updated', 'Your booking status has been updated to: Cancelled', 0, '2025-11-23 06:24:55'),
(77, 48, 'Booking Confirmed', 'Your booking has been created successfully.', 0, '2025-11-23 06:33:30'),
(78, 48, 'Booking Status Updated', 'Your booking status has been updated to: Cancelled', 0, '2025-11-23 06:34:44'),
(80, 48, 'Booking Confirmed', 'Your booking has been created successfully.', 0, '2025-11-23 06:38:39'),
(93, 48, 'Booking Confirmed', 'Your booking has been created successfully.', 0, '2025-11-23 07:22:24'),
(95, 49, 'Booking Confirmed', 'Your booking has been created successfully.', 0, '2025-11-23 07:26:42'),
(96, 49, 'Booking Status Updated', 'Your booking status has been updated to: Confirmed', 0, '2025-11-23 07:27:39'),
(97, 48, 'Booking Status Updated', 'Your booking status has been updated to: Confirmed', 0, '2025-11-23 07:27:44'),
(118, 48, 'Booking Status Updated', 'Your booking status has been updated to: Ongoing', 0, '2025-11-23 10:34:02'),
(120, 48, 'Booking Status Updated', 'Your booking status has been updated to: Cancelled', 0, '2025-11-23 10:47:07'),
(122, 48, 'Booking Confirmed', 'Your booking has been created successfully.', 0, '2025-11-23 10:49:48'),
(123, 48, 'Booking Status Updated', 'Your booking status has been updated to: Confirmed', 0, '2025-11-23 10:50:27'),
(124, 48, 'Booking Status Updated', 'Your booking status has been updated to: Cancelled', 0, '2025-11-23 10:51:21'),
(126, 48, 'Booking Confirmed', 'Your booking has been created successfully.', 0, '2025-11-23 11:00:33'),
(128, 48, 'Booking Confirmed', 'Your booking has been created successfully.', 0, '2025-11-23 11:08:49'),
(129, 48, 'Booking Status Updated', 'Your booking status has been updated to: Cancelled', 0, '2025-11-23 11:11:42'),
(130, 48, 'Booking Status Updated', 'Your booking status has been updated to: Cancelled', 0, '2025-11-23 11:11:50'),
(140, 46, 'New Booking', 'You received a new booking request: 1 seat(s), Pref: any, Pickup: Labuyo, Tangub, Misamis Occidental, Philippines, Drop-off: Ozamiz, Misamis Occidental, Philippines.', 0, '2025-11-24 04:30:29'),
(141, 47, 'Booking Confirmed', 'Your booking has been created successfully.', 0, '2025-11-24 04:30:29'),
(142, 46, 'New Booking', 'You received a new booking request: 1 seat(s), Pref: any, Pickup: Aquino, Tangub, Misamis Occidental, Philippines, Drop-off: Labuyo, Tangub, Misamis Occidental, Philippines.', 0, '2025-11-24 04:35:18'),
(143, 47, 'Booking Confirmed', 'Your booking has been created successfully.', 0, '2025-11-24 04:35:18'),
(144, 50, 'New Booking', 'You received a new booking request: 1 seat(s), Pref: any, Pickup: Labuyo, Drop-off: Ozamiz.', 0, '2025-11-24 05:09:02'),
(145, 47, 'Booking Confirmed', 'Your booking has been created successfully.', 0, '2025-11-24 05:09:02'),
(146, 50, 'New Booking', 'You received a new booking request: 1 seat(s), Pref: any, Pickup: Tangub, Misamis Occidental, Philippines, Drop-off: Lorenzo Tan, Tangub, Misamis Occidental, Philippines.', 0, '2025-11-24 05:17:10'),
(147, 48, 'Booking Confirmed', 'Your booking has been created successfully.', 0, '2025-11-24 05:17:10'),
(148, 46, 'New Booking', 'You received a new booking request: 1 seat(s), Pref: front, Pickup: Aquino, Tangub, Misamis Occidental, Philippines, Drop-off: Dimaluna, Ozamiz, Misamis Occidental, Philippines.', 0, '2025-11-24 05:34:34'),
(149, 55, 'Booking Confirmed', 'Your booking has been created successfully.', 0, '2025-11-24 05:34:34'),
(150, 46, 'New Booking', 'You received a new booking request: 1 seat(s), Pref: any, Pickup: Liloan, Bonifacio, Misamis Occidental, Philippines, Drop-off: Labuyo.', 0, '2025-11-24 05:35:06'),
(151, 54, 'Booking Confirmed', 'Your booking has been created successfully.', 0, '2025-11-24 05:35:06'),
(152, 46, 'New Booking', 'You received a new booking request: 3 seat(s), Pref: any, Pickup: Labuyo, Tangub, Misamis Occidental, Philippines, Drop-off: Tabid, Ozamiz, Misamis Occidental, Philippines.', 0, '2025-11-24 05:36:01'),
(153, 55, 'Booking Confirmed', 'Your booking has been created successfully.', 1, '2025-11-24 05:36:01'),
(154, 54, 'Booking Status Updated', 'Your booking status has been updated to: Confirmed', 0, '2025-11-24 05:38:13'),
(155, 54, 'Booking Status Updated', 'Your booking status has been updated to: Ongoing', 0, '2025-11-24 05:38:36'),
(156, 55, 'Booking Status Updated', 'Your booking status has been updated to: Confirmed', 1, '2025-11-24 05:40:41'),
(157, 55, 'Booking Status Updated', 'Your booking status has been updated to: Cancelled', 0, '2025-11-24 05:44:22'),
(158, 46, 'New Booking', 'You received a new booking request: 3 seat(s), Pref: any, Pickup: Lorenzo Tan, Drop-off: Tabid.', 0, '2025-11-24 05:48:07'),
(159, 55, 'Booking Confirmed', 'Your booking has been created successfully.', 0, '2025-11-24 05:48:07'),
(160, 47, 'New message', 'You have a new message from Driver3', 0, '2025-11-24 05:51:49'),
(161, 55, 'Booking Status Updated', 'Your booking status has been updated to: Confirmed', 0, '2025-11-24 05:53:23'),
(162, 54, 'Booking Status Updated', 'Your booking status has been updated to: Completed', 0, '2025-11-24 05:54:37'),
(163, 46, 'New Booking', 'You received a new booking request: 1 seat(s), Pref: any, Pickup: Liloan Bonifacio , Drop-off: Labuyo, Tangub, Misamis Occidental, Philippines.', 0, '2025-11-24 05:55:49'),
(164, 54, 'Booking Confirmed', 'Your booking has been created successfully.', 0, '2025-11-24 05:55:49'),
(165, 54, 'Booking Status Updated', 'Your booking status has been updated to: Confirmed', 0, '2025-11-24 05:56:20'),
(166, 54, 'New message', 'You have a new message from Driver3', 0, '2025-11-24 05:59:18'),
(167, 54, 'New message', 'You have a new message from Driver3', 0, '2025-11-24 06:00:35'),
(168, 54, 'Booking Status Updated', 'Your booking status has been updated to: Cancelled', 0, '2025-11-24 06:04:23'),
(169, 55, 'Booking Status Updated', 'Your booking status has been updated to: Cancelled', 0, '2025-11-24 06:04:27'),
(170, 47, 'Booking Status Updated', 'Your booking status has been updated to: Confirmed', 0, '2025-11-24 06:06:40'),
(171, 50, 'New Booking', 'You received a new booking request: 1 seat(s), Pref: any, Pickup: Tangub, Misamis Occidental, Philippines, Drop-off: Ramon Magsaysay – Bobongan Road Poblacion, Ramon Magsaysay, Zamboanga del Sur, Philippines.', 0, '2025-11-24 06:08:02'),
(172, 47, 'Booking Confirmed', 'Your booking has been created successfully.', 0, '2025-11-24 06:08:02'),
(173, 50, 'New message', 'You have a new message from Passenger1', 0, '2025-11-24 06:08:12'),
(174, 47, 'Booking Status Updated', 'Your booking status has been updated to: Confirmed', 0, '2025-11-24 06:08:28'),
(175, 47, 'New message', 'You have a new message from Driver4', 0, '2025-11-24 06:08:39');

-- --------------------------------------------------------

--
-- Table structure for table `passenger`
--

CREATE TABLE `passenger` (
  `passenger_id` int(11) NOT NULL,
  `first_name` varchar(100) NOT NULL,
  `last_name` varchar(100) NOT NULL,
  `gender` char(6) NOT NULL,
  `age` int(3) NOT NULL,
  `type` varchar(30) NOT NULL,
  `address` varchar(300) NOT NULL,
  `no_show` int(2) DEFAULT 0,
  `user_id` int(11) NOT NULL,
  `id_number` varchar(200) NOT NULL,
  `idPic_url` varchar(300) DEFAULT NULL,
  `is_verified` tinyint(1) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `passenger`
--

INSERT INTO `passenger` (`passenger_id`, `first_name`, `last_name`, `gender`, `age`, `type`, `address`, `no_show`, `user_id`, `id_number`, `idPic_url`, `is_verified`) VALUES
(30, 'Sam', 'Manon-og', 'Male', 21, 'student', 'Purok-2, Kapatagan, Lanao del Norte', 2, 47, '2022-003446', 'https://smtsc-booking.proplocator.online/capstone/backend/idPic/id_47_1763708824.jpg', 1),
(31, 'Nashville ', 'Pacquiao ', 'Female', 21, 'student', 'Labuyo, Tangub city, Misamis Occidental ', 0, 48, '2022727', 'https://smtsc-booking.proplocator.online/capstone/backend/idPic/id_48_1763732972.jpg', 1),
(32, 'Passenger3', 'Test3', 'male', 21, 'student', 'Labuyo, Tangub city', 0, 49, '2022-003446', 'https://smtsc-booking.proplocator.online/capstone/backend/idPic/id_49_1763768236.jpg', 1),
(33, 'Sam', 'Manon-og', 'male', 21, 'regular', 'Labuyo, Tangub city ', 0, 51, '', NULL, 1),
(34, 'Nashville', 'Pacquiao ', 'male', 22, 'student', 'Aquino, Labuyo, Tangub city', 0, 52, '2022-003446', 'https://smtsc-booking.proplocator.online/backend/idPic/id_52_1763897607.jpg', 0),
(35, 'Samyang', 'samyang', 'male', 21, 'student', 'Lanao del Norte', 0, 53, '2022-003446', 'https://smtsc-booking.proplocator.online/backend/idPic/id_53_1763961935.jpg', 0),
(36, 'Delzenn Andrey ', 'Mendez', 'male', 22, 'student', 'Liloan Bonifacio ', 0, 54, '2022-003399', 'https://smtsc-booking.proplocator.online/backend/idPic/id_54_1763962198.jpg', 1),
(37, 'Jemmah Lykah', 'Lospo', 'female', 21, 'student', 'Aquino Tangub City ', 0, 55, '2022-004650 ', 'https://smtsc-booking.proplocator.online/backend/idPic/id_55_1763962323.jpg', 1);

-- --------------------------------------------------------

--
-- Table structure for table `route`
--

CREATE TABLE `route` (
  `route_id` int(11) NOT NULL,
  `route` varchar(200) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `route`
--

INSERT INTO `route` (`route_id`, `route`) VALUES
(1, 'Molave'),
(2, 'Ozamiz'),
(5, 'Tangub'),
(7, 'Pagadian');

-- --------------------------------------------------------

--
-- Table structure for table `schedule`
--

CREATE TABLE `schedule` (
  `sched_id` int(11) NOT NULL,
  `trip_id` int(11) NOT NULL,
  `weekdays` set('Mon','Tue','Wed','Thu','Fri','Sat','Sun') NOT NULL,
  `departure_time` time NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `schedule`
--

INSERT INTO `schedule` (`sched_id`, `trip_id`, `weekdays`, `departure_time`) VALUES
(254, 32, 'Mon', '15:08:00'),
(298, 33, 'Mon', '18:00:00'),
(333, 32, 'Mon', '15:08:00'),
(334, 32, 'Mon', '15:08:00'),
(339, 39, 'Mon', '14:00:00'),
(343, 39, 'Mon', '17:00:00'),
(346, 32, 'Mon', '15:08:00'),
(347, 32, 'Tue', '15:08:00'),
(348, 32, 'Tue', '19:01:00'),
(349, 32, 'Sun', '18:34:00'),
(350, 32, 'Sun', '20:34:00'),
(351, 32, 'Sun', '22:10:00');

-- --------------------------------------------------------

--
-- Table structure for table `schedule_inventory`
--

CREATE TABLE `schedule_inventory` (
  `schedule_inventory_id` int(11) NOT NULL,
  `sched_id` int(11) NOT NULL,
  `capacity` int(4) NOT NULL DEFAULT 0,
  `available_seat` int(4) NOT NULL DEFAULT 0,
  `total_passenger` int(11) NOT NULL DEFAULT 0,
  `status` enum('scheduled','ongoing','completed','cancelled') NOT NULL DEFAULT 'scheduled',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `schedule_inventory`
--

INSERT INTO `schedule_inventory` (`schedule_inventory_id`, `sched_id`, `capacity`, `available_seat`, `total_passenger`, `status`, `created_at`, `updated_at`) VALUES
(18, 45, 14, 11, 3, 'ongoing', '2025-11-20 23:12:37', '2025-11-22 04:52:36'),
(19, 78, 14, 12, 2, 'ongoing', '2025-11-22 05:45:05', '2025-11-22 06:04:07'),
(20, 83, 14, 12, 2, 'scheduled', '2025-11-22 07:44:04', '2025-11-22 11:23:24'),
(21, 84, 14, 12, 2, 'scheduled', '2025-11-22 09:15:50', '2025-11-22 13:27:57'),
(22, 85, 14, 13, 1, 'scheduled', '2025-11-22 11:26:40', '2025-11-22 11:26:42'),
(23, 86, 14, 11, 3, 'scheduled', '2025-11-22 16:15:19', '2025-11-22 23:40:33'),
(24, 93, 14, 13, 1, 'scheduled', '2025-11-22 23:48:40', '2025-11-22 23:48:42'),
(25, 98, 14, 0, 0, 'completed', '2025-11-23 00:03:35', '2025-11-23 02:37:00'),
(26, 96, 14, 13, 0, 'completed', '2025-11-23 00:40:26', '2025-11-23 02:37:19'),
(27, 97, 14, 11, 0, 'completed', '2025-11-23 01:12:34', '2025-11-23 05:20:00'),
(28, 100, 16, 17, 1, 'ongoing', '2025-11-23 06:07:27', '2025-11-23 06:41:37'),
(29, 111, 14, 13, 1, 'ongoing', '2025-11-23 06:51:57', '2025-11-23 06:59:24'),
(30, 112, 14, 13, 1, 'scheduled', '2025-11-23 06:55:46', '2025-11-23 06:55:47'),
(31, 115, 16, 13, 3, 'ongoing', '2025-11-23 07:21:22', '2025-11-24 00:25:18'),
(32, 116, 16, 16, 1, 'scheduled', '2025-11-23 08:02:27', '2025-11-23 09:57:33'),
(33, 278, 16, 16, 0, 'scheduled', '2025-11-23 11:08:44', '2025-11-23 11:11:42'),
(34, 254, 14, 12, 2, 'ongoing', '2025-11-24 00:36:54', '2025-11-24 06:04:27'),
(35, 277, 16, 16, 0, 'scheduled', '2025-11-24 00:48:17', '2025-11-24 00:49:53'),
(36, 333, 14, 8, 6, 'ongoing', '2025-11-24 04:30:27', '2025-11-24 05:48:07'),
(37, 334, 14, 13, 1, 'scheduled', '2025-11-24 04:35:17', '2025-11-24 04:35:18'),
(38, 298, 16, 15, 1, 'ongoing', '2025-11-24 05:09:01', '2025-11-24 05:15:01'),
(39, 339, 16, 14, 2, 'ongoing', '2025-11-24 05:17:09', '2025-11-24 06:08:02');

-- --------------------------------------------------------

--
-- Table structure for table `schedule_status`
--

CREATE TABLE `schedule_status` (
  `id` int(11) NOT NULL,
  `sched_id` int(11) NOT NULL,
  `driver_id` int(11) NOT NULL,
  `trip_id` int(11) NOT NULL,
  `status` enum('scheduled','ongoing','completed','cancelled') DEFAULT 'scheduled',
  `available_seats` int(11) DEFAULT 14,
  `total_passengers` int(11) DEFAULT 0,
  `updated_at` timestamp NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  `created_at` timestamp NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `schedule_status`
--

INSERT INTO `schedule_status` (`id`, `sched_id`, `driver_id`, `trip_id`, `status`, `available_seats`, `total_passengers`, `updated_at`, `created_at`) VALUES
(1, 78, 9, 28, 'scheduled', 14, 2, '2025-11-22 09:13:58', '2025-11-22 07:12:54'),
(2, 82, 9, 28, 'completed', 14, 0, '2025-11-22 07:30:00', '2025-11-22 07:12:54'),
(3, 83, 9, 28, 'scheduled', 14, 0, '2025-11-22 07:38:18', '2025-11-22 07:12:54'),
(4, 84, 9, 28, 'ongoing', 14, 0, '2025-11-22 09:14:06', '2025-11-22 07:12:54'),
(5, 85, 9, 28, 'scheduled', 14, 0, '2025-11-22 07:33:26', '2025-11-22 07:12:54'),
(6, 86, 9, 28, 'ongoing', 14, 0, '2025-11-22 23:38:54', '2025-11-22 07:12:54'),
(7, 87, 9, 28, 'scheduled', 14, 0, '2025-11-22 07:12:54', '2025-11-22 07:12:54');

-- --------------------------------------------------------

--
-- Table structure for table `user`
--

CREATE TABLE `user` (
  `user_id` int(11) NOT NULL,
  `username` varchar(100) NOT NULL,
  `password` varchar(200) NOT NULL,
  `role` varchar(30) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `user`
--

INSERT INTO `user` (`user_id`, `username`, `password`, `role`) VALUES
(45, 'Driver2', '$2y$10$kYXHl08/KILg1B.bdeGZ/evRbYqDUuB1QL4w6OKypvEvK4Wl.q5Ym', 'driver'),
(46, 'Driver3', '$2y$10$vIgCJqv.dI1WfovMSr8wiOVfa/M.db/VViDEzeXCyCr/GzQMA5n5i', 'driver'),
(47, 'Passenger1', '$2y$10$dW4Hls2r1e8TPGf1Cl70I.sTX8LdKNSQgUwK0.0MK/ztQnHEMOoUa', 'passenger'),
(48, 'Passenger2', '$2y$10$k.bqE0e6BArE9oQGxbgVFe3ekTaiL994JZMjm8skmeNLawCRh323u', 'passenger'),
(49, 'Passenger3', '$2y$10$tLZExlbjdNPA6wsX1Le6n.jCJ9Hve6489dbgiDXZNiHaZ04CSFGVe', 'passenger'),
(50, 'Driver4', '$2y$10$peP1IOTQ2NtqyJKsuDYJPuxwjYm8BQ3i4GO6MyvQ8.KqzTJDFq.z6', 'driver'),
(51, 'sam', '$2y$10$wZRGREsCdCXF0eaX4vXTz.OAYIf2Ybevjeq1TYNCwX47SXnJe6V5i', 'passenger'),
(52, 'Nashville', '$2y$10$n9UZhm8j/Zh0nobYNkRYV.m4Ryj91Shj3LHhOBANl4zh/x0qF9fXm', 'passenger'),
(53, 'zam', '$2y$10$uQaBZgtN4kq63cwkVyKuX.EWXwDp/S7Q6vAw3/D52tiZ91HjiGiz2', 'passenger'),
(54, 'delzenn', '$2y$10$pmCHEt.P8RxxJMIcRDAxROao.B7jzNRKWBAHOtTYXk4WFLN3ArIh6', 'passenger'),
(55, 'Lykah', '$2y$10$f/pTwK6i.Vqu6.7ceyRb2.Gjm47a.XWi9pLnX8a2G/1eIjRUnTSpm', 'passenger');

-- --------------------------------------------------------

--
-- Table structure for table `van`
--

CREATE TABLE `van` (
  `van_id` int(11) NOT NULL,
  `van_number` int(11) NOT NULL,
  `plate_number` varchar(30) NOT NULL,
  `capacity` int(2) NOT NULL,
  `status` varchar(30) DEFAULT 'active',
  `driver_id` int(11) DEFAULT NULL,
  `model` varchar(300) NOT NULL,
  `color` varchar(100) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `van`
--

INSERT INTO `van` (`van_id`, `van_number`, `plate_number`, `capacity`, `status`, `driver_id`, `model`, `color`) VALUES
(6, 200, 'CCC-123', 14, 'active', 11, 'toyota', 'white'),
(9, 21, 'PN-55555', 16, 'active', 12, 'Mitsubishi', 'Gray');

-- --------------------------------------------------------

--
-- Table structure for table `van_trip`
--

CREATE TABLE `van_trip` (
  `trip_id` int(11) NOT NULL,
  `van_id` int(11) NOT NULL,
  `origin` varchar(300) NOT NULL,
  `destination` varchar(300) NOT NULL,
  `available_seat` int(2) NOT NULL,
  `total_passenger` int(11) DEFAULT 0,
  `status` varchar(30) DEFAULT 'scheduled'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `van_trip`
--

INSERT INTO `van_trip` (`trip_id`, `van_id`, `origin`, `destination`, `available_seat`, `total_passenger`, `status`) VALUES
(32, 6, 'Molave', 'Ozamiz', 14, 0, 'scheduled'),
(33, 9, 'Molave', 'Ozamiz', 16, 0, 'scheduled'),
(39, 9, 'Tangub', 'Pagadian', 16, 0, 'scheduled');

-- --------------------------------------------------------

--
-- Table structure for table `van_trip_schedule`
--

CREATE TABLE `van_trip_schedule` (
  `sched_id` int(11) NOT NULL,
  `trip_id` int(11) NOT NULL,
  `schedule_time` time NOT NULL,
  `status` varchar(30) DEFAULT 'scheduled',
  `available_seat` int(2) NOT NULL,
  `total_passenger` int(11) DEFAULT 0,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Indexes for dumped tables
--

--
-- Indexes for table `admin`
--
ALTER TABLE `admin`
  ADD PRIMARY KEY (`admin_id`);

--
-- Indexes for table `booking`
--
ALTER TABLE `booking`
  ADD PRIMARY KEY (`booking_id`),
  ADD KEY `fk_booking_passenger` (`passenger_id`),
  ADD KEY `fk_booking_trip` (`trip_id`),
  ADD KEY `fk_booking_driver` (`driver_id`),
  ADD KEY `fk_booking_schedule` (`sched_id`),
  ADD KEY `idx_booking_payment_reference` (`payment_reference`),
  ADD KEY `idx_booking_payment_status` (`payment_status`),
  ADD KEY `idx_booking_payment_method` (`payment_method`);

--
-- Indexes for table `discount`
--
ALTER TABLE `discount`
  ADD PRIMARY KEY (`disc_id`);

--
-- Indexes for table `driver`
--
ALTER TABLE `driver`
  ADD PRIMARY KEY (`driver_id`),
  ADD KEY `fk_driver_user` (`user_id`);

--
-- Indexes for table `fare`
--
ALTER TABLE `fare`
  ADD PRIMARY KEY (`fare_id`);

--
-- Indexes for table `location`
--
ALTER TABLE `location`
  ADD PRIMARY KEY (`location_id`),
  ADD KEY `fk_location_driver` (`driver_id`);

--
-- Indexes for table `logs`
--
ALTER TABLE `logs`
  ADD PRIMARY KEY (`log_id`);

--
-- Indexes for table `message`
--
ALTER TABLE `message`
  ADD PRIMARY KEY (`msg_id`),
  ADD KEY `idx_message_sender_id` (`sender_id`),
  ADD KEY `idx_message_receiver_id` (`receiver_id`);

--
-- Indexes for table `message_deletion_log`
--
ALTER TABLE `message_deletion_log`
  ADD PRIMARY KEY (`log_id`),
  ADD KEY `idx_msg_id` (`msg_id`),
  ADD KEY `idx_deleted_by` (`deleted_by`),
  ADD KEY `idx_deleted_at` (`deleted_at`);

--
-- Indexes for table `notification`
--
ALTER TABLE `notification`
  ADD PRIMARY KEY (`notif_id`),
  ADD KEY `fk_notification_user` (`user_id`);

--
-- Indexes for table `passenger`
--
ALTER TABLE `passenger`
  ADD PRIMARY KEY (`passenger_id`),
  ADD KEY `fk_passenger_user` (`user_id`);

--
-- Indexes for table `route`
--
ALTER TABLE `route`
  ADD PRIMARY KEY (`route_id`);

--
-- Indexes for table `schedule`
--
ALTER TABLE `schedule`
  ADD PRIMARY KEY (`sched_id`),
  ADD KEY `trip_id` (`trip_id`);

--
-- Indexes for table `schedule_inventory`
--
ALTER TABLE `schedule_inventory`
  ADD PRIMARY KEY (`schedule_inventory_id`),
  ADD UNIQUE KEY `uniq_sched_inventory_sched` (`sched_id`);

--
-- Indexes for table `schedule_status`
--
ALTER TABLE `schedule_status`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `unique_schedule_driver` (`sched_id`,`driver_id`),
  ADD KEY `idx_sched_id` (`sched_id`),
  ADD KEY `idx_driver_id` (`driver_id`),
  ADD KEY `idx_trip_id` (`trip_id`),
  ADD KEY `idx_status` (`status`);

--
-- Indexes for table `user`
--
ALTER TABLE `user`
  ADD PRIMARY KEY (`user_id`);

--
-- Indexes for table `van`
--
ALTER TABLE `van`
  ADD PRIMARY KEY (`van_id`),
  ADD KEY `fk_van_driver` (`driver_id`);

--
-- Indexes for table `van_trip`
--
ALTER TABLE `van_trip`
  ADD PRIMARY KEY (`trip_id`),
  ADD KEY `fk_van_trip_van` (`van_id`);

--
-- Indexes for table `van_trip_schedule`
--
ALTER TABLE `van_trip_schedule`
  ADD PRIMARY KEY (`sched_id`),
  ADD KEY `trip_id` (`trip_id`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `admin`
--
ALTER TABLE `admin`
  MODIFY `admin_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT for table `booking`
--
ALTER TABLE `booking`
  MODIFY `booking_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=140;

--
-- AUTO_INCREMENT for table `discount`
--
ALTER TABLE `discount`
  MODIFY `disc_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=11;

--
-- AUTO_INCREMENT for table `driver`
--
ALTER TABLE `driver`
  MODIFY `driver_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=13;

--
-- AUTO_INCREMENT for table `fare`
--
ALTER TABLE `fare`
  MODIFY `fare_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT for table `location`
--
ALTER TABLE `location`
  MODIFY `location_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=10;

--
-- AUTO_INCREMENT for table `logs`
--
ALTER TABLE `logs`
  MODIFY `log_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=26;

--
-- AUTO_INCREMENT for table `message`
--
ALTER TABLE `message`
  MODIFY `msg_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=12;

--
-- AUTO_INCREMENT for table `message_deletion_log`
--
ALTER TABLE `message_deletion_log`
  MODIFY `log_id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `notification`
--
ALTER TABLE `notification`
  MODIFY `notif_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=176;

--
-- AUTO_INCREMENT for table `passenger`
--
ALTER TABLE `passenger`
  MODIFY `passenger_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=38;

--
-- AUTO_INCREMENT for table `route`
--
ALTER TABLE `route`
  MODIFY `route_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=8;

--
-- AUTO_INCREMENT for table `schedule`
--
ALTER TABLE `schedule`
  MODIFY `sched_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=352;

--
-- AUTO_INCREMENT for table `schedule_inventory`
--
ALTER TABLE `schedule_inventory`
  MODIFY `schedule_inventory_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=40;

--
-- AUTO_INCREMENT for table `schedule_status`
--
ALTER TABLE `schedule_status`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=13;

--
-- AUTO_INCREMENT for table `user`
--
ALTER TABLE `user`
  MODIFY `user_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=56;

--
-- AUTO_INCREMENT for table `van`
--
ALTER TABLE `van`
  MODIFY `van_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=13;

--
-- AUTO_INCREMENT for table `van_trip`
--
ALTER TABLE `van_trip`
  MODIFY `trip_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=40;

--
-- AUTO_INCREMENT for table `van_trip_schedule`
--
ALTER TABLE `van_trip_schedule`
  MODIFY `sched_id` int(11) NOT NULL AUTO_INCREMENT;

--
-- Constraints for dumped tables
--

--
-- Constraints for table `booking`
--
ALTER TABLE `booking`
  ADD CONSTRAINT `fk_booking_driver` FOREIGN KEY (`driver_id`) REFERENCES `driver` (`driver_id`),
  ADD CONSTRAINT `fk_booking_passenger` FOREIGN KEY (`passenger_id`) REFERENCES `passenger` (`passenger_id`),
  ADD CONSTRAINT `fk_booking_schedule` FOREIGN KEY (`sched_id`) REFERENCES `schedule` (`sched_id`),
  ADD CONSTRAINT `fk_booking_trip` FOREIGN KEY (`trip_id`) REFERENCES `van_trip` (`trip_id`);

--
-- Constraints for table `driver`
--
ALTER TABLE `driver`
  ADD CONSTRAINT `fk_driver_user` FOREIGN KEY (`user_id`) REFERENCES `user` (`user_id`);

--
-- Constraints for table `location`
--
ALTER TABLE `location`
  ADD CONSTRAINT `fk_location_driver` FOREIGN KEY (`driver_id`) REFERENCES `driver` (`driver_id`);

--
-- Constraints for table `message`
--
ALTER TABLE `message`
  ADD CONSTRAINT `fk_message_receiver_user` FOREIGN KEY (`receiver_id`) REFERENCES `user` (`user_id`),
  ADD CONSTRAINT `fk_message_sender_user` FOREIGN KEY (`sender_id`) REFERENCES `user` (`user_id`);

--
-- Constraints for table `notification`
--
ALTER TABLE `notification`
  ADD CONSTRAINT `fk_notification_user` FOREIGN KEY (`user_id`) REFERENCES `user` (`user_id`);

--
-- Constraints for table `passenger`
--
ALTER TABLE `passenger`
  ADD CONSTRAINT `fk_passenger_user` FOREIGN KEY (`user_id`) REFERENCES `user` (`user_id`);
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
