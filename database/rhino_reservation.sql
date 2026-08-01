-- Fresh Database Schema for Rhino Tourist Camp Production Handoff

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";

-- --------------------------------------------------------
-- 1. Table structure for table `booking_officers`
-- --------------------------------------------------------
CREATE TABLE `booking_officers` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `name` varchar(100) NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------
-- 2. Table structure for table `reservations`
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
  `children_under_12` tinyint(1) NOT NULL DEFAULT 1,
  `children_own_rooms` int(11) NOT NULL DEFAULT 0,
  `child_discount_type` enum('sharing','own_room','none') NOT NULL DEFAULT 'sharing',
  `guests_count` int(11) DEFAULT 1,
  `room_tier` varchar(50) DEFAULT 'Superior Tent',
  `booking_source` varchar(50) DEFAULT 'Direct Client',
  `agency_name` varchar(255) DEFAULT NULL,
  `agency_discount` decimal(10,2) DEFAULT 0.00,
  `discount` decimal(10,2) DEFAULT 0.00,
  `special_requests` text DEFAULT NULL,
  `is_followed_up` tinyint(1) NOT NULL DEFAULT 0,
  PRIMARY KEY (`id`),
  KEY `idx_number_of_adults` (`number_of_adults`),
  KEY `idx_number_of_children` (`number_of_children`),
  KEY `idx_child_discount_type` (`child_discount_type`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------
-- 3. Table structure for table `guest_registration_records`
-- --------------------------------------------------------
CREATE TABLE `guest_registration_records` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `booking_id` int(11) NOT NULL,
  `guest_name` varchar(150) NOT NULL,
  `room_type` varchar(50) NOT NULL,
  `room_tier` varchar(50) DEFAULT 'Superior Tent',
  `check_in_date` date NOT NULL,
  `check_out_date` date NOT NULL,
  `number_of_nights` int(11) NOT NULL,
  `number_of_rooms` int(11) NOT NULL,
  `number_of_guests` int(11) DEFAULT 1,
  `payment_status` varchar(50) DEFAULT 'Fully Paid',
  `registration_date` date NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  `is_active` tinyint(1) DEFAULT 1,
  `status` varchar(50) DEFAULT 'Outstanding',
  `current_status` varchar(50) DEFAULT 'Checked In',
  PRIMARY KEY (`id`),
  UNIQUE KEY `unique_booking` (`booking_id`),
  KEY `idx_booking_id` (`booking_id`),
  KEY `idx_payment_status` (`payment_status`),
  KEY `idx_registration_date` (`registration_date`),
  KEY `idx_guest_name` (`guest_name`),
  CONSTRAINT `fk_registration_booking_id` FOREIGN KEY (`booking_id`) REFERENCES `reservations` (`id`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------
-- 4. Table structure for table `payments`
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
-- 5. Triggers for `reservations`
-- --------------------------------------------------------
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
-- 6. Table structure for table `rooms`
-- --------------------------------------------------------
CREATE TABLE `rooms` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `type` varchar(50) NOT NULL,
  `total_inventory` int(11) NOT NULL,
  `max_guests_per_room` int(11) NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

INSERT INTO `rooms` (`id`, `type`, `total_inventory`, `max_guests_per_room`) VALUES
(1, 'Single Room', 5, 1),
(2, 'Double Room', 20, 2),
(3, 'Triple Room', 4, 3),
(4, 'Family Room', 5, 4);

-- --------------------------------------------------------
-- 7. Table structure for table `system_logs`
-- --------------------------------------------------------
CREATE TABLE `system_logs` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `username` varchar(50) NOT NULL,
  `role` varchar(50) NOT NULL DEFAULT 'UNKNOWN',
  `action_code` varchar(50) NOT NULL,
  `action` text NOT NULL,
  `ip_address` varchar(45) NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------
-- 8. Table structure for table `system_rates`
-- --------------------------------------------------------
CREATE TABLE `system_rates` (
  `season` varchar(50) NOT NULL,
  `room_tier` varchar(50) NOT NULL,
  `room_config` varchar(50) NOT NULL,
  `ksh_rate` decimal(10,2) NOT NULL DEFAULT 0.00,
  `usd_rate` decimal(10,2) NOT NULL DEFAULT 0.00,
  PRIMARY KEY (`season`,`room_tier`,`room_config`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

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
-- 9. Table structure for table `system_settings`
-- --------------------------------------------------------
CREATE TABLE `system_settings` (
  `setting_key` varchar(50) NOT NULL,
  `setting_value` text DEFAULT NULL,
  PRIMARY KEY (`setting_key`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

INSERT INTO `system_settings` (`setting_key`, `setting_value`) VALUES
('custom_primary', '#046a38'),
('custom_secondary', '#10b981'),
('footer_text', '© 2026 RHINO TOURIST CAMP. ALL RIGHTS RESERVED.'),
('header_display_type', 'logo'),
('header_icon', 'fa-hippo'),
('header_logo_path', 'uploads/brand/header_logo_file_1785485977.jpg'),
('header_subtitle', 'Rhino Tourist Camp Front-Desk Operations Ledger Console'),
('header_title', 'RHINO TOURIST CAMP RESERVATION SYSTEM'),
('logo_path', 'uploads/brand/sidebar_logo_file_1785486459.jpg'),
('nav_alerts_icon', 'fa-bell'),
('nav_alerts_name', 'Follow up Alerts'),
('nav_calendar_icon', 'fa-calendar-days'),
('nav_calendar_name', 'Calendar Matrix'),
('nav_dashboard_icon', 'fa-chart-pie'),
('nav_dashboard_name', 'Dashboard'),
('nav_finance_icon', 'fa-receipt'),
('nav_finance_name', 'Payment & Billing'),
('nav_guest_icon', 'fa-address-book'),
('nav_guest_name', 'Guest Register'),
('nav_rates_icon', 'fa-tags'),
('nav_rates_name', 'Rates Controller'),
('rack_rates_footer_path', 'uploads/brand/rack_footer_file_1785489381.jpg'),
('rack_rates_header_path', 'uploads/brand/rack_header_file_1785489381.jpg'),
('receipt_header_path', 'uploads/brand/receipt_header_file_1785497401.jpg'),
('sidebar_display_type', 'logo'),
('sidebar_icon', 'fa-campground'),
('sidebar_subtitle', 'Reservation Suite'),
('sidebar_title', 'Rhino Tourist Camp'),
('theme_color', 'safari'),
('watermark_image_path', 'uploads/brand/watermark_image_file_1785568261.jpg'),
('watermark_text', 'Rhino Tourist Camp'),
('watermark_type', 'text');

-- --------------------------------------------------------
-- 10. Table structure for table `users`
-- --------------------------------------------------------
CREATE TABLE `users` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `display_name` varchar(100) NOT NULL DEFAULT 'System User',
  `username` varchar(50) NOT NULL,
  `password` varchar(255) NOT NULL,
  `role` varchar(50) NOT NULL DEFAULT 'Consultant',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`),
  UNIQUE KEY `username` (`username`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

INSERT INTO `users` (`id`, `display_name`, `username`, `password`, `role`, `created_at`) VALUES
(2, 'System Administrator', 'Admin', '$2y$10$4OqZS/FWDo7InBAoBrEWZuAqSogIPNx2rKv.oHnfHJkk5p9tL4NdS', 'admin', '2026-07-27 09:13:05');

COMMIT;