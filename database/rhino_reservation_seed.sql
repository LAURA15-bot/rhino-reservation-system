-- phpMyAdmin SQL Dump
-- Consolidated Seed Data with Double-Entry Prevention
-- Database: `rhino_reservation`

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";

/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

-- --------------------------------------------------------
-- 1. Insert Users (Safely ignoring duplicates)
-- --------------------------------------------------------
INSERT IGNORE INTO `users` (`id`, `username`, `password`, `created_at`) VALUES
(2, 'admin', '$2y$10$4OqZS/FWDo7InBAoBrEWZuAqSogIPNx2rKv.oHnfHJkk5p9tL4NdS', '2026-07-27 09:13:05');

INSERT IGNORE INTO `users` (`username`, `password`) VALUES
('frontdesk', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi');

-- --------------------------------------------------------
-- 2. Insert Booking Officers
-- --------------------------------------------------------
INSERT IGNORE INTO `booking_officers` (`id`, `name`) VALUES
(1, 'Sarah Rep'),
(2, 'Grace'),
(3, 'Front Desk');

-- --------------------------------------------------------
-- 3. Insert Rooms
-- --------------------------------------------------------
INSERT IGNORE INTO `rooms` (`id`, `type`, `total_inventory`, `max_guests_per_room`) VALUES
(1, 'Single Room', 5, 1),
(2, 'Double Room', 20, 2),
(3, 'Triple Room', 4, 3),
(4, 'Family Room', 5, 4);

-- --------------------------------------------------------
-- 4. Insert Reservations
-- --------------------------------------------------------
INSERT IGNORE INTO `reservations` (`id`, `guest_name`, `phone`, `email`, `check_in`, `check_out`, `number_of_adults`, `number_of_children`, `guest_type`, `currency`, `room_type`, `rooms_count`, `booking_officer`, `receipt_no`, `deposit_paid`, `balance`, `status`, `nationality`, `total_amount`, `payment_status`, `created_at`, `children_under_12`, `children_own_rooms`, `child_discount_type`, `guests_count`, `room_tier`, `booking_source`, `agency_name`, `agency_discount`, `discount`) VALUES
(8, 'Lilian Munene', 'N/A', '', '2026-07-25', '2026-07-28', 1, 0, 'Resident', 'KES', 'Single Room', 1, 'Front Desk', NULL, 0.00, 0.00, 'Booked', 'Resident', 7500.00, 'Outstanding', '2026-07-27 06:02:27', 1, 0, 'sharing', 1, 'Superior Tent', 'Direct Client', NULL, 0.00, 0.00),
(9, 'Lilian Munene', 'N/A', '', '2026-07-25', '2026-07-28', 1, 0, 'Resident', 'KES', 'Double Room', 2, 'Front Desk', NULL, 0.00, 0.00, 'Reserved', 'Resident', 45000.00, 'Paid in Full', '2026-07-27 06:02:27', 1, 0, 'sharing', 1, 'Superior Tent', 'Direct Client', NULL, 0.00, 0.00),
(10, 'Jullian Dubois', 'N/A', '', '2026-07-27', '2026-07-29', 1, 0, 'Non Resident', 'USD', 'Family Room', 1, 'Betty', NULL, 0.00, 60.00, 'Reserved', 'Resident', 260.00, 'Partially Paid', '2026-07-27 06:04:57', 1, 0, 'sharing', 1, 'Superior Tent', 'Direct Client', NULL, 0.00, 0.00),
(12, 'Benito', 'N/A', '', '2026-08-04', '2026-08-06', 1, 0, 'Non Resident', 'USD', 'Single Room', 1, 'Front Desk', NULL, 0.00, 0.00, 'Reserved', 'Resident', 150.00, 'Paid in Full', '2026-07-27 09:16:59', 1, 0, 'sharing', 1, 'Superior Tent', 'Direct Client', NULL, 0.00, 0.00),
(14, 'Remy', 'N/A', '', '2026-08-04', '2026-08-10', 1, 0, 'Resident', 'KES', 'Double Room', 5, 'Front Desk', NULL, 0.00, 0.00, 'Reserved', 'Resident', 210000.00, 'Paid in Full', '2026-07-27 09:40:38', 1, 0, 'sharing', 1, 'Superior Tent', 'Direct Client', NULL, 0.00, 0.00),
(15, 'Lucky', 'N/A', '', '2026-07-27', '2026-07-28', 1, 1, 'Resident', 'KES', 'Double Room', 1, 'Front Desk', NULL, 0.00, 0.00, 'Reserved', 'Resident', 5625.00, 'Outstanding', '2026-07-27 12:31:50', 1, 0, 'sharing', 1, 'Superior Tent', 'Direct Client', NULL, 0.00, 0.00),
(16, 'Stefie', 'N/A', '', '2026-08-01', '2026-08-03', 1, 1, 'Resident', 'KES', 'Single Room', 1, 'Front Desk', NULL, 0.00, 0.00, 'Booked', 'Resident', 7500.00, 'Outstanding', '2026-07-27 12:35:03', 1, 1, 'own_room', 1, 'Superior Tent', 'Direct Client', NULL, 0.00, 0.00),
(17, 'Stefie KK', 'N/A', '', '2026-07-27', '2026-07-29', 1, 1, 'Resident', 'KES', 'Single Room', 2, 'Front Desk', NULL, 0.00, 0.00, 'Booked', 'Resident', 17500.00, 'Outstanding', '2026-07-27 12:42:50', 1, 1, 'own_room', 1, 'Superior Tent', 'Direct Client', NULL, 0.00, 0.00),
(18, 'Ryan Kelly', 'N/A', '', '2026-07-27', '2026-07-29', 0, 0, 'Non Resident', 'USD', 'Triple Room', 2, 'Front Desk', NULL, 0.00, 0.00, 'Booked', 'Resident', 440.00, 'Outstanding', '2026-07-27 13:29:07', 1, 0, 'none', 1, 'Superior Tent', 'Direct Client', NULL, 0.00, 0.00),
(19, 'Kennedy', 'N/A', '', '2026-10-20', '2026-10-22', 1, 0, 'Resident', 'KES', 'Single Room', 1, 'Front Desk', NULL, 0.00, 3000.00, 'Booked', 'Resident', 8000.00, 'Partially Paid', '2026-07-28 04:36:17', 1, 0, 'none', 1, 'Superior Tent', 'Direct Client', NULL, 0.00, 0.00),
(20, 'John', 'N/A', '', '2026-08-01', '2026-08-03', 0, 0, 'Resident', 'KES', 'Single Room', 2, 'Mark', NULL, 0.00, 0.00, 'Reserved', 'Resident', 24000.00, 'Paid in Full', '2026-07-28 12:18:29', 1, 0, 'none', 2, 'Deluxe Room', 'Direct Client', NULL, 0.00, 0.00);

INSERT IGNORE INTO `reservations` (
    `guest_name`, `phone`, `email`, `check_in`, `check_out`, 
    `number_of_adults`, `number_of_children`, `guest_type`, `currency`, `room_type`, `rooms_count`, 
    `booking_officer`, `total_amount`, `deposit_paid`, `balance`, `payment_status`, `room_tier`,
    `status`, `nationality`, `children_under_12`, `children_own_rooms`, `child_discount_type`, `guests_count`, `booking_source`, `discount`, `agency_discount`
) VALUES
('Jane Traveler', '+254700000000', 'jane@example.com', CURDATE(), DATE_ADD(CURDATE(), INTERVAL 3 DAY), 
2, 0, 'Resident', 'KES', 'Double Room', 1, 
'Sarah Rep', 60000.00, 60000.00, 0.00, 'Fully Paid', 'Double Room',
'Reserved', 'Resident', 1, 0, 'sharing', 2, 'Direct Client', 0.00, 0.00);

-- --------------------------------------------------------
-- 5. Insert Payments
-- --------------------------------------------------------
INSERT IGNORE INTO `payments` (`id`, `booking_id`, `amount_paid`, `currency`, `payment_method`, `reference_no`, `recorded_by`, `created_at`) VALUES
(5, 10, 18000.00, 'KES', 'M-Pesa', 'fhjyhk', 'admin', '2026-07-27 06:12:59'),
(6, 10, 200.00, 'USD', 'Cash', '', 'admin', '2026-07-27 06:48:25'),
(8, 9, 45000.00, 'KES', 'Cash', '', 'admin', '2026-07-27 08:50:54'),
(9, 14, 210000.00, 'KES', 'M-Pesa', 'jhu,fk', 'admin', '2026-07-27 09:46:02'),
(10, 12, 150.00, 'USD', 'Cash', 'uyfukfg', 'admin', '2026-07-27 09:46:38'),
(11, 19, 5000.00, 'KES', 'Cash', '', 'admin', '2026-07-28 04:37:45'),
(12, 20, 24000.00, 'KES', 'Cash', '', 'admin', '2026-07-28 12:22:40');

INSERT IGNORE INTO `payments` (`booking_id`, `amount_paid`, `currency`, `payment_method`, `reference_no`, `recorded_by`) VALUES
(1, 60000.00, 'KES', 'M-Pesa', 'RDX123456789', 'admin');

-- --------------------------------------------------------
-- 6. Insert System Rates (Overwrites pricing if it already exists)
-- --------------------------------------------------------
INSERT INTO `system_rates` (`season`, `room_tier`, `room_config`, `ksh_rate`, `usd_rate`) VALUES
('High Season', 'Single Room', 'Standard', 15000.00, 150.00),
('High Season', 'Double Room', 'Standard', 20000.00, 200.00),
('High Season', 'Family Room', 'Standard', 30000.00, 300.00),
('Low Season', 'Double Room', 'Standard', 12000.00, 120.00),
('Festive Season', 'SUPERIOR TENTS', 'single', 5000.00, 70.00),
('Festive Season', 'SUPERIOR TENTS', 'double', 7000.00, 75.00),
('Festive Season', 'SUPERIOR TENTS', 'triple', 10000.00, 110.00),
('Festive Season', 'SUPERIOR TENTS', 'family', 12000.00, 130.00),
('Festive Season', 'DELUXE ROOMS', 'single', 6000.00, 75.00),
('Festive Season', 'DELUXE ROOMS', 'double', 7500.00, 80.00),
('Festive Season', 'DELUXE ROOMS', 'triple', 10500.00, 115.00),
('Festive Season', 'DELUXE ROOMS', 'family', 12500.00, 135.00),
('High Season', 'SUPERIOR TENTS', 'single', 4000.00, 45.00),
('High Season', 'SUPERIOR TENTS', 'double', 5000.00, 55.00),
('High Season', 'SUPERIOR TENTS', 'triple', 7500.00, 80.00),
('High Season', 'SUPERIOR TENTS', 'family', 10000.00, 110.00),
('High Season', 'DELUXE ROOMS', 'single', 4500.00, 50.00),
('High Season', 'DELUXE ROOMS', 'double', 5500.00, 60.00),
('High Season', 'DELUXE ROOMS', 'triple', 8000.00, 85.00),
('High Season', 'DELUXE ROOMS', 'family', 10500.00, 115.00),
('Low Season', 'SUPERIOR TENTS', 'single', 3800.00, 40.00),
('Low Season', 'SUPERIOR TENTS', 'double', 4850.00, 50.00),
('Low Season', 'SUPERIOR TENTS', 'triple', 7300.00, 75.00),
('Low Season', 'SUPERIOR TENTS', 'family', 8800.00, 90.00),
('Low Season', 'DELUXE ROOMS', 'single', 4000.00, 45.00),
('Low Season', 'DELUXE ROOMS', 'double', 5250.00, 55.00),
('Low Season', 'DELUXE ROOMS', 'triple', 7550.00, 80.00),
('Low Season', 'DELUXE ROOMS', 'family', 9050.00, 95.00),
('Peak Season', 'SUPERIOR TENTS', 'single', 5000.00, 70.00),
('Peak Season', 'SUPERIOR TENTS', 'double', 7000.00, 75.00),
('Peak Season', 'SUPERIOR TENTS', 'triple', 10000.00, 110.00),
('Peak Season', 'SUPERIOR TENTS', 'family', 12000.00, 130.00),
('Peak Season', 'DELUXE ROOMS', 'single', 6000.00, 75.00),
('Peak Season', 'DELUXE ROOMS', 'double', 7500.00, 80.00),
('Peak Season', 'DELUXE ROOMS', 'triple', 10500.00, 115.00),
('Peak Season', 'DELUXE ROOMS', 'family', 12500.00, 135.00)
ON DUPLICATE KEY UPDATE 
ksh_rate = VALUES(ksh_rate),
usd_rate = VALUES(usd_rate);

COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;

DROP TABLE IF EXISTS `system_logs`;

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