-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: Jul 29, 2026 at 02:53 PM
-- Server version: 10.4.32-MariaDB
-- PHP Version: 8.0.30

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Database: `rhino_reservation`
--

-- --------------------------------------------------------

--
-- Table structure for table `booking_officers`
--

CREATE TABLE `booking_officers` (
  `id` int(11) NOT NULL,
  `name` varchar(100) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `booking_officers`
--

INSERT INTO `booking_officers` (`id`, `name`) VALUES
(1, 'Sarah Rep'),
(2, 'Grace'),
(3, 'Front Desk');

-- --------------------------------------------------------

--
-- Table structure for table `guest_registration_records`
--

CREATE TABLE `guest_registration_records` (
  `id` int(11) NOT NULL,
  `booking_id` int(11) NOT NULL,
  `guest_name` varchar(150) NOT NULL,
  `room_type` varchar(50) NOT NULL,
  `check_in_date` date NOT NULL,
  `check_out_date` date NOT NULL,
  `number_of_nights` int(11) NOT NULL,
  `number_of_rooms` int(11) NOT NULL,
  `payment_status` varchar(50) DEFAULT 'Fully Paid',
  `registration_date` date NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  `is_active` tinyint(1) DEFAULT 1,
  `status` varchar(50) DEFAULT 'Outstanding'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `payments`
--

CREATE TABLE `payments` (
  `id` int(11) NOT NULL,
  `booking_id` int(11) NOT NULL,
  `amount_paid` decimal(10,2) NOT NULL,
  `currency` varchar(10) NOT NULL DEFAULT 'KES',
  `payment_method` varchar(50) NOT NULL,
  `reference_no` varchar(100) DEFAULT NULL,
  `recorded_by` varchar(100) NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `payments`
--

INSERT INTO `payments` (`id`, `booking_id`, `amount_paid`, `currency`, `payment_method`, `reference_no`, `recorded_by`, `created_at`) VALUES
(5, 10, 18000.00, 'KES', 'M-Pesa', 'fhjyhk', 'admin', '2026-07-27 06:12:59'),
(6, 10, 200.00, 'USD', 'Cash', '', 'admin', '2026-07-27 06:48:25'),
(8, 9, 45000.00, 'KES', 'Cash', '', 'admin', '2026-07-27 08:50:54'),
(9, 14, 210000.00, 'KES', 'M-Pesa', 'jhu,fk', 'admin', '2026-07-27 09:46:02'),
(10, 12, 150.00, 'USD', 'Cash', 'uyfukfg', 'admin', '2026-07-27 09:46:38'),
(11, 19, 5000.00, 'KES', 'Cash', '', 'admin', '2026-07-28 04:37:45'),
(12, 20, 24000.00, 'KES', 'Cash', '', 'admin', '2026-07-28 12:22:40'),
(13, 10, 60.00, 'USD', 'Cash', '', 'admin', '2026-07-29 05:37:17'),
(14, 21, 10000.00, 'KES', 'Cash', '', 'admin', '2026-07-29 06:15:06'),
(15, 22, 12000.00, 'KES', 'Cash', '', 'admin', '2026-07-29 06:18:39'),
(16, 23, 100.00, 'USD', 'Cash', '', 'admin', '2026-07-29 07:52:17'),
(17, 25, 10000.00, 'KES', 'Cash', '', 'Lucy', '2026-07-29 11:40:31');

-- --------------------------------------------------------

--
-- Table structure for table `reservations`
--

CREATE TABLE `reservations` (
  `id` int(11) NOT NULL,
  `guest_name` varchar(100) NOT NULL,
  `phone` varchar(20) NOT NULL,
  `email` varchar(100) DEFAULT NULL,
  `check_in` date NOT NULL,
  `check_out` date NOT NULL,
  `number_of_adults` int(11) NOT NULL DEFAULT 1,
  `number_of_children` int(11) NOT NULL DEFAULT 0,
  `guest_type` varchar(50) NOT NULL DEFAULT 'Resident',
  `currency` varchar(10) NOT NULL DEFAULT 'KES',
  `room_type` varchar(50) NOT NULL,
  `rooms_count` int(11) NOT NULL,
  `booking_officer` varchar(100) NOT NULL,
  `receipt_no` varchar(50) DEFAULT NULL,
  `deposit_paid` decimal(10,2) DEFAULT 0.00,
  `balance` decimal(10,2) DEFAULT 0.00,
  `status` varchar(20) DEFAULT 'Reserved',
  `nationality` varchar(50) DEFAULT 'Resident',
  `total_amount` decimal(10,2) DEFAULT 0.00,
  `payment_status` varchar(50) DEFAULT 'Outstanding',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `children_under_12` tinyint(1) NOT NULL DEFAULT 1 COMMENT '1 = children under 12, 0 = children 12+',
  `children_own_rooms` int(11) NOT NULL DEFAULT 0 COMMENT 'Number of rooms occupied by children only',
  `child_discount_type` enum('sharing','own_room','none') NOT NULL DEFAULT 'sharing',
  `guests_count` int(11) DEFAULT 1,
  `room_tier` varchar(50) DEFAULT 'Superior Tent',
  `booking_source` varchar(50) DEFAULT 'Direct Client',
  `agency_name` varchar(255) DEFAULT NULL,
  `agency_discount` decimal(10,2) DEFAULT 0.00,
  `discount` decimal(10,2) DEFAULT 0.00,
  `special_requests` text DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `reservations`
--

INSERT INTO `reservations` (`id`, `guest_name`, `phone`, `email`, `check_in`, `check_out`, `number_of_adults`, `number_of_children`, `guest_type`, `currency`, `room_type`, `rooms_count`, `booking_officer`, `receipt_no`, `deposit_paid`, `balance`, `status`, `nationality`, `total_amount`, `payment_status`, `created_at`, `children_under_12`, `children_own_rooms`, `child_discount_type`, `guests_count`, `room_tier`, `booking_source`, `agency_name`, `agency_discount`, `discount`, `special_requests`) VALUES
(8, 'Lilian Munene', 'N/A', '', '2026-07-25', '2026-07-28', 1, 0, 'Resident', 'KES', 'Single Room', 1, 'Front Desk', NULL, 0.00, 0.00, 'Checked Out', 'Resident', 7500.00, 'Outstanding', '2026-07-27 06:02:27', 1, 0, 'sharing', 1, 'Superior Tent', 'Direct Client', NULL, 0.00, 0.00, NULL),
(9, 'Lilian Munene', 'N/A', '', '2026-07-25', '2026-07-28', 1, 0, 'Resident', 'KES', 'Double Room', 2, 'Front Desk', NULL, 0.00, 0.00, 'Checked Out', 'Resident', 45000.00, 'Paid in Full', '2026-07-27 06:02:27', 1, 0, 'sharing', 1, 'Superior Tent', 'Direct Client', NULL, 0.00, 0.00, NULL),
(10, 'Jullian Dubois', 'N/A', '', '2026-07-27', '2026-07-29', 1, 0, 'Non Resident', 'USD', 'Family Room', 1, 'Betty', NULL, 0.00, 0.00, 'Reserved', 'Resident', 260.00, 'Paid in Full', '2026-07-27 06:04:57', 1, 0, 'sharing', 1, 'Superior Tent', 'Direct Client', NULL, 0.00, 0.00, NULL),
(12, 'Benito', 'N/A', '', '2026-08-04', '2026-08-06', 1, 0, 'Non Resident', 'USD', 'Single Room', 1, 'Front Desk', NULL, 0.00, 0.00, 'Reserved', 'Resident', 150.00, 'Paid in Full', '2026-07-27 09:16:59', 1, 0, 'sharing', 1, 'Superior Tent', 'Direct Client', NULL, 0.00, 0.00, NULL),
(14, 'Remy', 'N/A', '', '2026-08-04', '2026-08-10', 1, 0, 'Resident', 'KES', 'Double Room', 5, 'Front Desk', NULL, 0.00, 0.00, 'Reserved', 'Resident', 210000.00, 'Paid in Full', '2026-07-27 09:40:38', 1, 0, 'sharing', 1, 'Superior Tent', 'Direct Client', NULL, 0.00, 0.00, NULL),
(15, 'Lucky', 'N/A', '', '2026-07-27', '2026-07-28', 1, 1, 'Resident', 'KES', 'Double Room', 1, 'Front Desk', NULL, 0.00, 0.00, 'Cancelled', 'Resident', 5625.00, 'Outstanding', '2026-07-27 12:31:50', 1, 0, 'sharing', 1, 'Superior Tent', 'Direct Client', NULL, 0.00, 0.00, NULL),
(16, 'Stefie', 'N/A', '', '2026-08-01', '2026-08-03', 1, 1, 'Resident', 'KES', 'Single Room', 1, 'Front Desk', NULL, 0.00, 0.00, 'Booked', 'Resident', 7500.00, 'Outstanding', '2026-07-27 12:35:03', 1, 1, 'own_room', 1, 'Superior Tent', 'Direct Client', NULL, 0.00, 0.00, NULL),
(17, 'Stefie KK', 'N/A', '', '2026-07-27', '2026-07-29', 1, 1, 'Resident', 'KES', 'Single Room', 2, 'Front Desk', NULL, 0.00, 0.00, 'Booked', 'Resident', 17500.00, 'Outstanding', '2026-07-27 12:42:50', 1, 1, 'own_room', 1, 'Superior Tent', 'Direct Client', NULL, 0.00, 0.00, NULL),
(18, 'Ryan Kelly', 'N/A', '', '2026-07-27', '2026-07-29', 0, 0, 'Non Resident', 'USD', 'Triple Room', 2, 'Front Desk', NULL, 0.00, 0.00, 'Booked', 'Resident', 440.00, 'Outstanding', '2026-07-27 13:29:07', 1, 0, 'none', 1, 'Superior Tent', 'Direct Client', NULL, 0.00, 0.00, NULL),
(19, 'Kennedy', 'N/A', '', '2026-10-20', '2026-10-22', 1, 0, 'Resident', 'KES', 'Single Room', 1, 'Front Desk', NULL, 0.00, 3000.00, 'Booked', 'Resident', 8000.00, 'Partially Paid', '2026-07-28 04:36:17', 1, 0, 'none', 1, 'Superior Tent', 'Direct Client', NULL, 0.00, 0.00, NULL),
(20, 'John', 'N/A', '', '2026-08-01', '2026-08-03', 0, 0, 'Resident', 'KES', 'Single Room', 2, 'Mark', NULL, 0.00, 0.00, 'Reserved', 'Resident', 24000.00, 'Paid in Full', '2026-07-28 12:18:29', 1, 0, 'none', 2, 'Deluxe Room', 'Direct Client', NULL, 0.00, 0.00, NULL),
(21, 'LEWIS', 'N/A', '', '2026-07-30', '2026-08-02', 1, 0, 'Resident', 'KES', 'Single Room', 1, 'Front Desk', NULL, 0.00, 5000.00, 'Booked', 'Resident', 15000.00, 'Partially Paid', '2026-07-29 06:14:54', 1, 0, 'none', 1, 'Superior Tent', 'Direct Client', '', 0.00, 0.00, NULL),
(22, 'DOREEN', 'N/A', '', '2026-07-29', '2026-07-31', 3, 1, 'Resident', 'KES', 'Family Room', 1, 'Front Desk', NULL, 0.00, 6000.00, 'Booked', 'Resident', 18000.00, 'Partially Paid', '2026-07-29 06:18:14', 1, 0, 'sharing', 4, 'Superior Tent', 'Direct Client', '', 0.00, 0.00, NULL),
(23, 'PASCAL', 'N/A', '', '2026-08-10', '2026-08-12', 1, 0, 'Non Resident', 'USD', 'Single Room', 1, '', NULL, 0.00, 40.00, 'Booked', 'Resident', 140.00, 'Partially Paid', '2026-07-29 07:51:39', 1, 0, 'none', 1, 'Deluxe Room', 'Travel Agency', '', 0.00, 10.00, 'He is a vegetarian'),
(24, 'Alex', 'N/A', '', '2026-08-01', '2026-08-02', 1, 0, 'Resident', 'KES', 'Double Room', 1, 'Betty', NULL, 0.00, 0.00, 'Reserved', 'Resident', 7000.00, 'Outstanding', '2026-07-29 09:41:50', 1, 0, 'none', 2, 'Superior Tent', 'Travel Agency', 'Perfect Safaris', 0.00, 0.00, ''),
(25, 'Sharon', 'N/A', '', '2026-08-01', '2026-08-03', 3, 1, 'Resident', 'KES', 'Family Room', 1, 'Front Desk', NULL, 0.00, 8000.00, 'Booked', 'Resident', 18000.00, 'Partially Paid', '2026-07-29 11:39:05', 1, 0, 'sharing', 4, 'Superior Tent', 'Direct Client', '', 0.00, 0.00, 'He is vegetarian'),
(26, 'Valerie', 'N/A', '', '2026-07-29', '2026-07-31', 1, 0, 'Resident', 'KES', 'Single Room', 1, 'Front Desk', NULL, 0.00, 0.00, 'Reserved', 'Resident', 10000.00, 'Outstanding', '2026-07-29 11:39:05', 1, 0, 'none', 1, 'Superior Tent', 'Direct Client', '', 0.00, 0.00, ''),
(27, 'steve', 'N/A', '', '2026-07-29', '2026-07-30', 1, 0, 'Resident', 'KES', 'Double Room', 1, 'Front Desk', NULL, 0.00, 0.00, 'Reserved', 'Resident', 7000.00, 'Outstanding', '2026-07-29 11:48:06', 1, 0, 'none', 2, 'Superior Tent', 'Direct Client', '', 0.00, 0.00, '');

--
-- Triggers `reservations`
--
DELIMITER $$
CREATE TRIGGER `after_reservation_insert` AFTER INSERT ON `reservations` FOR EACH ROW BEGIN
    IF NEW.payment_status = 'Fully Paid' THEN
        INSERT IGNORE INTO guest_registration_records (
            booking_id, guest_name, room_type, room_tier, 
            check_in_date, check_out_date, number_of_nights, 
            number_of_rooms, number_of_guests, payment_status, registration_date
        ) VALUES (
            NEW.id, NEW.guest_name, NEW.room_type, NEW.room_tier, 
            NEW.check_in, NEW.check_out, DATEDIFF(NEW.check_out, NEW.check_in), 
            NEW.rooms_count, NEW.guests_count, NEW.payment_status, CURDATE()
        );
    END IF;
END
$$
DELIMITER ;
DELIMITER $$
CREATE TRIGGER `after_reservation_update` AFTER UPDATE ON `reservations` FOR EACH ROW BEGIN
    IF NEW.payment_status = 'Fully Paid' AND OLD.payment_status != 'Fully Paid' THEN
        INSERT IGNORE INTO guest_registration_records (
            booking_id, guest_name, room_type, room_tier, 
            check_in_date, check_out_date, number_of_nights, 
            number_of_rooms, number_of_guests, payment_status, registration_date
        ) VALUES (
            NEW.id, NEW.guest_name, NEW.room_type, NEW.room_tier, 
            NEW.check_in, NEW.check_out, DATEDIFF(NEW.check_out, NEW.check_in), 
            NEW.rooms_count, NEW.guests_count, NEW.payment_status, CURDATE()
        );
    END IF;
END
$$
DELIMITER ;

-- --------------------------------------------------------

--
-- Table structure for table `rooms`
--

CREATE TABLE `rooms` (
  `id` int(11) NOT NULL,
  `type` varchar(50) NOT NULL,
  `total_inventory` int(11) NOT NULL,
  `max_guests_per_room` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `rooms`
--

INSERT INTO `rooms` (`id`, `type`, `total_inventory`, `max_guests_per_room`) VALUES
(1, 'Single Room', 5, 1),
(2, 'Double Room', 20, 2),
(3, 'Triple Room', 4, 3),
(4, 'Family Room', 5, 4);

-- --------------------------------------------------------

--
-- Table structure for table `system_logs`
--

CREATE TABLE `system_logs` (
  `id` int(11) NOT NULL,
  `username` varchar(50) NOT NULL,
  `role` varchar(50) NOT NULL DEFAULT 'UNKNOWN',
  `action_code` varchar(50) NOT NULL,
  `action` text NOT NULL,
  `ip_address` varchar(45) NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `system_logs`
--

INSERT INTO `system_logs` (`id`, `username`, `role`, `action_code`, `action`, `ip_address`, `created_at`) VALUES
(1, 'admin', 'admin', 'USER_LOGIN', 'User successfully authenticated session console.', '::1', '2026-07-29 10:16:53'),
(2, 'admin', 'admin', 'USER_LOGOUT', 'User securely terminated session.', '::1', '2026-07-29 10:24:04'),
(3, 'admin', 'admin', 'USER_LOGIN', 'User successfully authenticated session console.', '::1', '2026-07-29 10:24:06'),
(4, 'admin', 'admin', 'USER_LOGOUT', 'User securely terminated session.', '::1', '2026-07-29 10:24:12'),
(5, 'admin', 'admin', 'USER_LOGIN', 'User successfully authenticated session console.', '::1', '2026-07-29 10:24:14'),
(6, 'admin', 'admin', 'USER_LOGOUT', 'User securely terminated session.', '::1', '2026-07-29 10:25:29'),
(7, 'admin', 'admin', 'USER_LOGIN', 'User successfully authenticated session console.', '::1', '2026-07-29 10:25:31'),
(8, 'admin', 'admin', 'USER_CREATED', 'Registered new workspace identity: Lucy with consultant privileges.', '::1', '2026-07-29 10:40:44'),
(9, 'admin', 'admin', 'USER_LOGOUT', 'User securely terminated session.', '::1', '2026-07-29 10:40:52'),
(10, 'Lucy', 'consultant', 'USER_LOGIN', 'User successfully authenticated session console.', '::1', '2026-07-29 10:40:57'),
(11, 'Lucy', 'consultant', 'USER_LOGOUT', 'User securely terminated session.', '::1', '2026-07-29 10:41:06'),
(12, 'admin', 'UNKNOWN', 'LOGIN_FAILED', 'Invalid authentication credentials attempt block.', '::1', '2026-07-29 10:41:19'),
(13, 'admin', 'UNKNOWN', 'LOGIN_FAILED', 'Invalid authentication credentials attempt block.', '::1', '2026-07-29 10:41:31'),
(14, 'admin', 'admin', 'USER_LOGIN', 'User successfully authenticated session console.', '::1', '2026-07-29 10:41:47'),
(15, 'admin', 'admin', 'USER_MODIFIED', 'Modified workspace identity profile: Admin.', '::1', '2026-07-29 10:50:38'),
(16, 'Admin', 'admin', 'USER_LOGIN', 'User successfully authenticated session console.', '::1', '2026-07-29 11:08:33'),
(17, 'Admin', 'admin', 'PROPERTY_CONFIG_UPDATE', 'Modified physical property configuration for Single Room [New Inventory: 6 | Max Pax: 1].', '::1', '2026-07-29 11:15:22'),
(18, 'Admin', 'admin', 'PROPERTY_CONFIG_UPDATE', 'Modified physical property configuration for Single Room [New Inventory: 5 | Max Pax: 1].', '::1', '2026-07-29 11:15:54'),
(19, 'Admin', 'admin', 'USER_LOGOUT', 'User securely terminated session.', '::1', '2026-07-29 11:17:25'),
(20, 'Lucy', 'consultant', 'USER_LOGIN', 'User successfully authenticated session console.', '::1', '2026-07-29 11:17:42'),
(21, 'Lucy', 'consultant', 'USER_LOGOUT', 'User securely terminated session.', '::1', '2026-07-29 11:18:20'),
(22, 'Admin', 'admin', 'USER_LOGIN', 'User successfully authenticated session console.', '::1', '2026-07-29 11:18:54'),
(23, 'Admin', 'admin', 'USER_CREATED', 'Registered new workspace identity: Stefie with consultant privileges.', '::1', '2026-07-29 11:23:26'),
(24, 'Stefie', 'consultant', 'USER_LOGIN', 'User successfully authenticated session console.', '192.168.1.233', '2026-07-29 11:24:11'),
(25, 'Stefie', 'consultant', 'USER_LOGOUT', 'User securely terminated session.', '192.168.1.233', '2026-07-29 11:29:38'),
(26, 'Admin', 'admin', 'USER_LOGOUT', 'User securely terminated session.', '::1', '2026-07-29 11:30:22'),
(27, 'Lucy', 'consultant', 'USER_LOGIN', 'User successfully authenticated session console.', '192.168.1.233', '2026-07-29 11:30:38'),
(28, 'Admin', 'admin', 'USER_LOGIN', 'User successfully authenticated session console.', '::1', '2026-07-29 11:30:41'),
(29, 'Lucy', 'consultant', 'USER_LOGOUT', 'User securely terminated session.', '192.168.1.233', '2026-07-29 11:49:47'),
(30, 'Admin', 'admin', 'USER_LOGIN', 'User successfully authenticated session console.', '192.168.1.233', '2026-07-29 11:49:58'),
(31, 'Admin', 'admin', 'PROPERTY_CONFIG_UPDATE', 'Modified physical property configuration for Single Room [New Inventory: 6 | Max Pax: 1].', '192.168.1.233', '2026-07-29 11:50:10'),
(32, 'Admin', 'admin', 'PROPERTY_CONFIG_UPDATE', 'Modified physical property configuration for Single Room [New Inventory: 5 | Max Pax: 1].', '192.168.1.233', '2026-07-29 11:50:26'),
(33, 'Admin', 'admin', 'USER_LOGOUT', 'User securely terminated session.', '192.168.1.233', '2026-07-29 11:50:52'),
(34, 'Stefie', 'consultant', 'USER_LOGIN', 'User successfully authenticated session console.', '192.168.1.233', '2026-07-29 11:50:57'),
(35, 'admin', 'UNKNOWN', 'LOGIN_FAILED', 'Invalid authentication credentials attempt block.', '::1', '2026-07-29 12:27:58'),
(36, 'Admin', 'admin', 'USER_LOGIN', 'User successfully authenticated session console.', '::1', '2026-07-29 12:28:09');

-- --------------------------------------------------------

--
-- Table structure for table `system_rates`
--

CREATE TABLE `system_rates` (
  `season` varchar(50) NOT NULL,
  `room_tier` varchar(50) NOT NULL,
  `room_config` varchar(50) NOT NULL,
  `ksh_rate` decimal(10,2) NOT NULL DEFAULT 0.00,
  `usd_rate` decimal(10,2) NOT NULL DEFAULT 0.00
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `system_rates`
--

INSERT INTO `system_rates` (`season`, `room_tier`, `room_config`, `ksh_rate`, `usd_rate`) VALUES
('Festive Season', 'DELUXE ROOMS', 'double', 7500.00, 80.00),
('Festive Season', 'DELUXE ROOMS', 'family', 12500.00, 135.00),
('Festive Season', 'DELUXE ROOMS', 'single', 6000.00, 75.00),
('Festive Season', 'DELUXE ROOMS', 'triple', 10500.00, 115.00),
('Festive Season', 'SUPERIOR TENTS', 'double', 7000.00, 75.00),
('Festive Season', 'SUPERIOR TENTS', 'family', 12000.00, 130.00),
('Festive Season', 'SUPERIOR TENTS', 'single', 5000.00, 70.00),
('Festive Season', 'SUPERIOR TENTS', 'triple', 10000.00, 110.00),
('High Season', 'DELUXE ROOMS', 'double', 5500.00, 60.00),
('High Season', 'DELUXE ROOMS', 'family', 10500.00, 115.00),
('High Season', 'DELUXE ROOMS', 'single', 4500.00, 50.00),
('High Season', 'DELUXE ROOMS', 'triple', 8000.00, 85.00),
('High Season', 'SUPERIOR TENTS', 'double', 5000.00, 55.00),
('High Season', 'SUPERIOR TENTS', 'family', 10000.00, 110.00),
('High Season', 'SUPERIOR TENTS', 'single', 4000.00, 45.00),
('High Season', 'SUPERIOR TENTS', 'triple', 7500.00, 80.00),
('Low Season', 'DELUXE ROOMS', 'double', 5250.00, 55.00),
('Low Season', 'DELUXE ROOMS', 'family', 9050.00, 95.00),
('Low Season', 'DELUXE ROOMS', 'single', 4000.00, 45.00),
('Low Season', 'DELUXE ROOMS', 'triple', 7550.00, 80.00),
('Low Season', 'SUPERIOR TENTS', 'double', 4850.00, 50.00),
('Low Season', 'SUPERIOR TENTS', 'family', 8800.00, 90.00),
('Low Season', 'SUPERIOR TENTS', 'single', 3800.00, 40.00),
('Low Season', 'SUPERIOR TENTS', 'triple', 7300.00, 75.00),
('Peak Season', 'DELUXE ROOMS', 'double', 7500.00, 80.00),
('Peak Season', 'DELUXE ROOMS', 'family', 12500.00, 135.00),
('Peak Season', 'DELUXE ROOMS', 'single', 6000.00, 75.00),
('Peak Season', 'DELUXE ROOMS', 'triple', 10500.00, 115.00),
('Peak Season', 'SUPERIOR TENTS', 'double', 7000.00, 75.00),
('Peak Season', 'SUPERIOR TENTS', 'family', 12000.00, 130.00),
('Peak Season', 'SUPERIOR TENTS', 'single', 5000.00, 70.00),
('Peak Season', 'SUPERIOR TENTS', 'triple', 10000.00, 110.00);

-- --------------------------------------------------------

--
-- Table structure for table `users`
--

CREATE TABLE `users` (
  `id` int(11) NOT NULL,
  `display_name` varchar(100) NOT NULL DEFAULT 'System User',
  `username` varchar(50) NOT NULL,
  `password` varchar(255) NOT NULL,
  `role` varchar(50) NOT NULL DEFAULT 'Consultant',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `users`
--

INSERT INTO `users` (`id`, `display_name`, `username`, `password`, `role`, `created_at`) VALUES
(2, 'System Administrator', 'Admin', '$2y$10$4OqZS/FWDo7InBAoBrEWZuAqSogIPNx2rKv.oHnfHJkk5p9tL4NdS', 'admin', '2026-07-27 09:13:05'),
(3, 'LUCY', 'Lucy', '$2y$10$3HsjfacMPEX/rXjM4taQs.aIhSpc2MShd0EBFMjX/ZuoQeD38vhmW', 'consultant', '2026-07-29 10:40:44'),
(4, 'Stefie', 'Stefie', '$2y$10$x7ptwCz69dd51saKf6G0Belfz32WfERK0FUrB/8Xv2AR2RoefSc6C', 'consultant', '2026-07-29 11:23:26');

--
-- Indexes for dumped tables
--

--
-- Indexes for table `booking_officers`
--
ALTER TABLE `booking_officers`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `guest_registration_records`
--
ALTER TABLE `guest_registration_records`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `unique_booking` (`booking_id`),
  ADD KEY `idx_booking_id` (`booking_id`),
  ADD KEY `idx_payment_status` (`payment_status`),
  ADD KEY `idx_registration_date` (`registration_date`),
  ADD KEY `idx_guest_name` (`guest_name`);

--
-- Indexes for table `payments`
--
ALTER TABLE `payments`
  ADD PRIMARY KEY (`id`),
  ADD KEY `booking_id` (`booking_id`);

--
-- Indexes for table `reservations`
--
ALTER TABLE `reservations`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_number_of_adults` (`number_of_adults`),
  ADD KEY `idx_number_of_children` (`number_of_children`),
  ADD KEY `idx_child_discount_type` (`child_discount_type`);

--
-- Indexes for table `rooms`
--
ALTER TABLE `rooms`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `system_logs`
--
ALTER TABLE `system_logs`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `system_rates`
--
ALTER TABLE `system_rates`
  ADD PRIMARY KEY (`season`,`room_tier`,`room_config`);

--
-- Indexes for table `users`
--
ALTER TABLE `users`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `username` (`username`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `booking_officers`
--
ALTER TABLE `booking_officers`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT for table `guest_registration_records`
--
ALTER TABLE `guest_registration_records`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `payments`
--
ALTER TABLE `payments`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=18;

--
-- AUTO_INCREMENT for table `reservations`
--
ALTER TABLE `reservations`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=28;

--
-- AUTO_INCREMENT for table `rooms`
--
ALTER TABLE `rooms`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;

--
-- AUTO_INCREMENT for table `system_logs`
--
ALTER TABLE `system_logs`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=37;

--
-- AUTO_INCREMENT for table `users`
--
ALTER TABLE `users`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;

--
-- Constraints for dumped tables
--

--
-- Constraints for table `guest_registration_records`
--
ALTER TABLE `guest_registration_records`
  ADD CONSTRAINT `fk_registration_booking_id` FOREIGN KEY (`booking_id`) REFERENCES `reservations` (`id`) ON UPDATE CASCADE;

--
-- Constraints for table `payments`
--
ALTER TABLE `payments`
  ADD CONSTRAINT `payments_ibfk_1` FOREIGN KEY (`booking_id`) REFERENCES `reservations` (`id`) ON DELETE CASCADE;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
