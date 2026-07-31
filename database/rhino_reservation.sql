-- --------------------------------------------------------
-- Rhino Reservation - Database Schema (Advanced Version)
-- --------------------------------------------------------

CREATE DATABASE IF NOT EXISTS rhino_reservation;
USE rhino_reservation;

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";

-- Drop existing tables to ensure a clean overwrite (Reverse order of dependencies)
DROP TABLE IF EXISTS `payments`;
DROP TABLE IF EXISTS `guest_registration_records`;
DROP TABLE IF EXISTS `reservations`;
DROP TABLE IF EXISTS `system_rates`;
DROP TABLE IF EXISTS `rooms`;
DROP TABLE IF EXISTS `booking_officers`;
DROP TABLE IF EXISTS `users`;

-- --------------------------------------------------------
-- Table structure for table `booking_officers`
-- --------------------------------------------------------
CREATE TABLE `booking_officers` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `name` varchar(100) NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------
-- Table structure for table `reservations`
-- --------------------------------------------------------
CREATE TABLE `reservations` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
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
  PRIMARY KEY (`id`),
  KEY `idx_number_of_adults` (`number_of_adults`),
  KEY `idx_number_of_children` (`number_of_children`),
  KEY `idx_child_discount_type` (`child_discount_type`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------
-- Table structure for table `guest_registration_records`
-- --------------------------------------------------------
CREATE TABLE `guest_registration_records` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
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
  `status` varchar(50) DEFAULT 'Outstanding',
  PRIMARY KEY (`id`),
  UNIQUE KEY `unique_booking` (`booking_id`),
  KEY `idx_booking_id` (`booking_id`),
  KEY `idx_payment_status` (`payment_status`),
  KEY `idx_registration_date` (`registration_date`),
  KEY `idx_guest_name` (`guest_name`),
  CONSTRAINT `fk_registration_booking_id` FOREIGN KEY (`booking_id`) REFERENCES `reservations` (`id`) ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------
-- Table structure for table `payments`
-- --------------------------------------------------------
CREATE TABLE `payments` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `booking_id` int(11) NOT NULL,
  `amount_paid` decimal(10,2) NOT NULL,
  `currency` varchar(10) NOT NULL DEFAULT 'KES',
  `payment_method` varchar(50) NOT NULL,
  `reference_no` varchar(100) DEFAULT NULL,
  `recorded_by` varchar(100) NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`),
  KEY `booking_id` (`booking_id`),
  CONSTRAINT `payments_ibfk_1` FOREIGN KEY (`booking_id`) REFERENCES `reservations` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------
-- Table structure for table `rooms`
-- --------------------------------------------------------
CREATE TABLE `rooms` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `type` varchar(50) NOT NULL,
  `total_inventory` int(11) NOT NULL,
  `max_guests_per_room` int(11) NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------
-- Table structure for table `system_rates`
-- --------------------------------------------------------
CREATE TABLE `system_rates` (
  `season` varchar(50) NOT NULL,
  `room_tier` varchar(50) NOT NULL,
  `room_config` varchar(50) NOT NULL,
  `ksh_rate` decimal(10,2) NOT NULL DEFAULT 0.00,
  `usd_rate` decimal(10,2) NOT NULL DEFAULT 0.00,
  PRIMARY KEY (`season`,`room_tier`,`room_config`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------
-- Table structure for table `users`
-- --------------------------------------------------------
CREATE TABLE `users` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `username` varchar(50) NOT NULL,
  `password` varchar(255) NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`),
  UNIQUE KEY `username` (`username`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------
-- Database Triggers for Automating Guest Registration
-- --------------------------------------------------------
DELIMITER $$
CREATE TRIGGER `after_reservation_insert` AFTER INSERT ON `reservations` FOR EACH ROW BEGIN
    IF NEW.payment_status = 'Fully Paid' THEN
        INSERT IGNORE INTO guest_registration_records (
            booking_id, guest_name, room_type,  
            check_in_date, check_out_date, number_of_nights, 
            number_of_rooms, payment_status, registration_date
        ) VALUES (
            NEW.id, NEW.guest_name, NEW.room_type, 
            NEW.check_in, NEW.check_out, DATEDIFF(NEW.check_out, NEW.check_in), 
            NEW.rooms_count, NEW.payment_status, CURDATE()
        );
    END IF;
END
$$
DELIMITER ;

DELIMITER $$
CREATE TRIGGER `after_reservation_update` AFTER UPDATE ON `reservations` FOR EACH ROW BEGIN
    IF NEW.payment_status = 'Fully Paid' AND OLD.payment_status != 'Fully Paid' THEN
        INSERT IGNORE INTO guest_registration_records (
            booking_id, guest_name, room_type,  
            check_in_date, check_out_date, number_of_nights, 
            number_of_rooms, payment_status, registration_date
        ) VALUES (
            NEW.id, NEW.guest_name, NEW.room_type, 
            NEW.check_in, NEW.check_out, DATEDIFF(NEW.check_out, NEW.check_in), 
            NEW.rooms_count, NEW.payment_status, CURDATE()
        );
    END IF;
END
$$
DELIMITER ;

COMMIT;

CREATE TABLE IF NOT EXISTS `system_settings` (
  `setting_key` VARCHAR(50) NOT NULL,
  `setting_value` TEXT DEFAULT NULL,
  PRIMARY KEY (`setting_key`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- Insert default white-label configurations if empty
INSERT IGNORE INTO `system_settings` (`setting_key`, `setting_value`) VALUES
('header_title', 'RHINO TOURIST RESERVATION SYSTEM'),
('header_subtitle', 'Rhino Tourist Camp Front-Desk Operations Ledger Console'),
('header_icon', 'fa-hippo'),
('sidebar_title', 'Rhino Camp'),
('sidebar_subtitle', 'Reservation Suite'),
('sidebar_icon', 'fa-campground'),
('footer_text', '© 2026 RHINO TOURIST CAMP. ALL RIGHTS RESERVED.'),
('theme_color', 'emerald');