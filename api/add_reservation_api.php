<?php
// api/add_reservation_api.php

ob_start(); // Prevent accidental HTML/Warnings from breaking JSON
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

require '../Includes/database.php';
header('Content-Type: application/json');

// Room inventory limits
$inventory_limits = [
    'Single Room' => 5,
    'Double Room' => 20,
    'Triple Room' => 4,
    'Family Room' => 5
];

// Unified Pricing Engine
if (!function_exists('getBookingPricingData')) {
    function getBookingPricingData($pdo, $checkIn, $checkOut, $roomTier, $roomType, $guestType, $roomCount, $numAdults = 1, $numChildren = 0, $childrenUnder12 = 1, $childrenOwnRooms = 0) {
        $date1 = new DateTime($checkIn);
        $date2 = new DateTime($checkOut);
        $nights = $date1->diff($date2)->days;
        if ($nights == 0) $nights = 1;

        $month = (int)$date1->format('m');
        $day = (int)$date1->format('d');
        
        $season = 'Low Season';
        if (($month == 12 && $day >= 21) || ($month == 1 && $day <= 3)) {
            $season = 'Festive Season';
        } elseif (($month == 1 && $day >= 4) || $month == 2 || ($month == 3 && $day <= 15)) {
            $season = 'High Season';
        } elseif ($month >= 7 && $month <= 9) {
            $season = 'Peak Season';
        }

        $tierInput = strtoupper((string)$roomTier);
        $tier = (strpos($tierInput, 'DELUXE') !== false) ? 'DELUXE ROOMS' : 'SUPERIOR TENTS';

        $roomTypeStr = strtoupper((string)$roomType);
        $config = 'single';
        if (strpos($roomTypeStr, 'DOUBLE') !== false) $config = 'double';
        if (strpos($roomTypeStr, 'TRIPLE') !== false) $config = 'triple';
        if (strpos($roomTypeStr, 'FAMILY') !== false) $config = 'family';
        
        $stmt = $pdo->prepare("SELECT ksh_rate, usd_rate FROM system_rates WHERE season = ? AND room_tier = ? AND room_config = ?");
        $stmt->execute([$season, $tier, $config]);
        $rateRow = $stmt->fetch(PDO::FETCH_ASSOC);
        
        $ratePerNightPerRoom = 0;
        if ($rateRow) {
            $isResident = (strtolower($guestType) === 'resident');
            $ratePerNightPerRoom = $isResident ? (float)$rateRow['ksh_rate'] : (float)$rateRow['usd_rate'];
        }
        
        $base_price = $ratePerNightPerRoom;
        $total_rooms_needed = (int)$roomCount > 0 ? (int)$roomCount : 1;
        $rooms_for_children = (int)$childrenOwnRooms;
        $rooms_for_adults = max(0, $total_rooms_needed - $rooms_for_children);
        
        $adult_total_per_night = 0;
        $child_total_per_night = 0;

        if ((int)$childrenUnder12 === 1 && (int)$numChildren > 0) {
            if ($rooms_for_adults > 0) {
                if ($rooms_for_children == 0) {
                    $adult_total_per_night = ($base_price * 0.50) * $rooms_for_adults;
                } else {
                    $adult_total_per_night = $base_price * $rooms_for_adults;
                }
            }
            if ($rooms_for_children > 0) {
                $child_total_per_night = ($base_price * 0.75) * $rooms_for_children;
            } else {
                $child_total_per_night = ($base_price * 0.25) * $numChildren;
            }
        } else {
            $adult_total_per_night = $base_price * $total_rooms_needed;
            $child_total_per_night = 0;
        }
        
        $total_per_night = $adult_total_per_night + $child_total_per_night;
        
        return [
            'base_price' => $base_price,
            'adult_total' => (float)($adult_total_per_night * $nights),
            'child_total' => (float)($child_total_per_night * $nights),
            'total' => (float)($total_per_night * $nights),
            'nights' => $nights
        ];
    }
    
    function calculateBookingTotal($pdo, $checkIn, $checkOut, $roomTier, $roomType, $guestType, $roomCount, $numAdults = 1, $numChildren = 0, $childrenUnder12 = 1, $childrenOwnRooms = 0, $discountPerRoom = 0) {
        $pricing = getBookingPricingData($pdo, $checkIn, $checkOut, $roomTier, $roomType, $guestType, $roomCount, $numAdults, $numChildren, $childrenUnder12, $childrenOwnRooms);
        $baseTotal = $pricing['total'];
        $nights = $pricing['nights'];
        $totalDiscount = $discountPerRoom * $roomCount * $nights;
        return [
            'original_total' => $baseTotal,
            'total_discount' => $totalDiscount,
            'final_total' => max(0, $baseTotal - $totalDiscount)
        ];
    }
}

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    try {
        $guest_name = $_POST['guest_name'] ?? '';
        $phone = $_POST['phone'] ?? '';
        $email = $_POST['email'] ?? '';
        $check_in = $_POST['check_in'] ?? '';
        $check_out = $_POST['check_out'] ?? '';
        
        $booking_source = $_POST['booking_source'] ?? 'Direct Client';
        $agency_name = ($booking_source === 'Travel Agency') ? ($_POST['agency_name'] ?? '') : '';
        $booking_officer = $_POST['booking_officer'] ?? 'Front Desk';
        $discount_input = ($booking_source === 'Travel Agency') ? (float)($_POST['discount'] ?? 0) : 0.00;
        
        $number_of_adults = (int)($_POST['number_of_adults'] ?? 1);
        $number_of_children = (int)($_POST['number_of_children'] ?? 0);
        $children_under_12 = isset($_POST['children_under_12']) ? 1 : 0;
        $children_own_rooms = (int)($_POST['children_own_rooms'] ?? 0);
        $guests_count = max((int)($_POST['guests_count'] ?? 1), ($number_of_adults + $number_of_children));
        
        $child_discount_type = 'none';
        if ($number_of_children > 0 && $children_under_12 == 1) {
            $child_discount_type = ($children_own_rooms > 0) ? 'own_room' : 'sharing';
        }

        $guest_type = $_POST['guest_type'] ?? 'Resident';
        $room_tier = $_POST['room_tier'] ?? 'Superior Tent';
        $room_type = $_POST['room_type'] ?? 'Single Room';
        $rooms_count = (int)($_POST['rooms_count'] ?? 1);
        $receipt_no = $_POST['receipt_no'] ?? '';
        $deposit_paid = (float)($_POST['deposit_paid'] ?? 0);
        $status = $_POST['status'] ?? 'Reserved';
        $currency = (strtolower($guest_type) === 'resident') ? 'KES' : 'USD';
        
        // NEW: Capture special requests
        $special_requests = isset($_POST['has_special_requests']) ? trim($_POST['special_requests'] ?? '') : '';

        // Validations
        if ($check_in < date('Y-m-d')) throw new Exception("Validation Error! You cannot book a room for a date in the past.");
        if ($children_own_rooms > $rooms_count) throw new Exception("Logic Error! Children rooms cannot exceed the total number of rooms booked.");
        if ($discount_input < 0) throw new Exception("Validation Error! The discount amount cannot be negative.");

        $pricing_results = calculateBookingTotal($pdo, $check_in, $check_out, $room_tier, $room_type, $guest_type, $rooms_count, $number_of_adults, $number_of_children, $children_under_12, $children_own_rooms, $discount_input);
        
        if ($pricing_results['total_discount'] > $pricing_results['original_total']) {
            throw new Exception("Validation Error! The discount exceeds the original room total.");
        }

        $original_total = $pricing_results['original_total'];
        $total_discount = $pricing_results['total_discount'];
        $final_amount = $pricing_results['final_total'];
        $balance = max(0, $final_amount - $deposit_paid);

        // Check Inventory
        $stmt = $pdo->prepare("SELECT SUM(rooms_count) as booked_rooms FROM reservations WHERE room_type = :rt AND status != 'Cancelled' AND check_in < :co AND check_out > :ci");
        $stmt->execute(['rt' => $room_type, 'co' => $check_out, 'ci' => $check_in]);
        $currently_booked = (int)$stmt->fetchColumn();
        $max_available = $inventory_limits[$room_type] ?? 5;
        
        if (($currently_booked + $rooms_count) > $max_available) {
            throw new Exception("Overbooking Error! Only " . ($max_available - $currently_booked) . " " . htmlspecialchars($room_type) . "(s) available.");
        }

        // Insert
        $insertStmt = $pdo->prepare("
            INSERT INTO reservations (
                guest_name, phone, email, check_in, check_out, 
                guests_count, room_tier, guest_type, currency, room_type, rooms_count, booking_officer, 
                receipt_no, total_amount, deposit_paid, balance, status,
                number_of_adults, number_of_children, children_under_12, children_own_rooms, child_discount_type,
                booking_source, agency_name, discount, special_requests
            ) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)
        ");
        
        $insertStmt->execute([
            $guest_name, $phone, $email, $check_in, $check_out,
            $guests_count, $room_tier, $guest_type, $currency, $room_type, $rooms_count, $booking_officer,
            $receipt_no, $final_amount, $deposit_paid, $balance, $status,
            $number_of_adults, $number_of_children, $children_under_12, $children_own_rooms, $child_discount_type,
            $booking_source, $agency_name, $total_discount, $special_requests
        ]);

        ob_clean();
        echo json_encode([
            'success' => true,
            'summary' => [
                'currency' => $currency,
                'original_total' => $original_total,
                'discount' => $total_discount,
                'final_total' => $final_amount,
                'deposit_paid' => $deposit_paid,
                'balance' => $balance
            ]
        ]);

    } catch (Exception $e) {
        ob_clean();
        echo json_encode(['success' => false, 'message' => $e->getMessage()]);
    }
    exit;
}
?>