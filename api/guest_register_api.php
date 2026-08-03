<?php
// api/guest_register_api.php
ob_start();
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

if (!isset($_SESSION['logged_in'])) {
    header('Content-Type: application/json');
    echo json_encode(['success' => false, 'message' => 'Unauthorized']);
    exit;
}

require '../Includes/database.php';
header('Content-Type: application/json');

// ==============================================================================
// UNIFIED PRICING ENGINE (Mirrored from Dashboard for strict consistency)
// ==============================================================================
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

// ==========================================
// HANDLE ADMIN RECORD EDITS (POST)
// ==========================================
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'update_record') {
    try {
        if (strtolower($_SESSION['role']) !== 'admin') {
            throw new Exception("Security Error: Only Administrators can alter finalized booking particulars.");
        }

        $id = (int)$_POST['id'];
        $guest_name = trim($_POST['guest_name']);
        $booking_date = $_POST['booking_date'];
        $guest_type = $_POST['guest_type'];
        $room_tier = $_POST['room_tier'];
        $room_type = $_POST['room_type'];
        $rooms_count = (int)$_POST['rooms_count'];
        $check_in = $_POST['check_in'];
        $nights = (int)$_POST['nights'];
        $discountPerRoom = (float)$_POST['discount'];
        
        $numAdults = (int)$_POST['adults'];
        $numChildren = (int)$_POST['children'];
        $childRooms = (int)$_POST['child_rooms'];
        $under12 = isset($_POST['under_12']) ? 1 : 0;
        $special_requests = trim($_POST['special_requests']);

        // Calculate dynamic Check-out date
        $date_in = new DateTime($check_in);
        $date_out = clone $date_in;
        $date_out->modify("+{$nights} days");
        $check_out = $date_out->format('Y-m-d');

        // 1. Security & Inventory Clash Check
        $currStmt = $pdo->prepare("SELECT rooms_count, status FROM reservations WHERE id = ?");
        $currStmt->execute([$id]);
        $currRow = $currStmt->fetch(PDO::FETCH_ASSOC);

        if (!$currRow) {
            throw new Exception("Error: Record not found.");
        }
        
        if ($currRow['status'] === 'Checked Out') {
            throw new Exception("Security Lock: You cannot edit the particulars of a guest who has already checked out.");
        }

        $invStmt = $pdo->prepare("SELECT total_inventory FROM rooms WHERE type = ?");
        $invStmt->execute([$room_type]);
        $max_inv = $invStmt->fetchColumn() ?: 5;

        $clashStmt = $pdo->prepare("SELECT SUM(rooms_count) FROM reservations WHERE room_type = ? AND status != 'Cancelled' AND check_in < ? AND check_out > ? AND id != ?");
        $clashStmt->execute([$room_type, $check_out, $check_in, $id]);
        $booked_overlap = $clashStmt->fetchColumn() ?: 0;

        if (($booked_overlap + $rooms_count) > $max_inv) {
            throw new Exception("Overbooking Error: The dates selected conflict with other bookings. Not enough {$room_type}s available.");
        }
        if ($childRooms > $rooms_count) {
            throw new Exception("Logic Error: Children rooms cannot exceed the total number of rooms allocated.");
        }

        // 2. Financial & Pricing Calculations
        $calc_results = calculateBookingTotal($pdo, $check_in, $check_out, $room_tier, $room_type, $guest_type, $rooms_count, $numAdults, $numChildren, $under12, $childRooms, $discountPerRoom);
        $final_total = $calc_results['final_total'];
        $total_discount = $calc_results['total_discount'];
        $guests_count = max(($numAdults + $numChildren), $rooms_count);
        $discount_type = ($numChildren > 0 && $under12 == 1) ? (($childRooms > 0) ? 'own_room' : 'sharing') : 'none';

        // Recalculate Balance & Payment Status dynamically based on existing payments
        $enforced_currency = (strtolower($guest_type) === 'resident') ? 'KES' : 'USD';
        $pSumStmt = $pdo->prepare("SELECT SUM(amount_paid) FROM payments WHERE booking_id = ? AND currency = ?");
        $pSumStmt->execute([$id, $enforced_currency]);
        $total_paid = (float)$pSumStmt->fetchColumn();

        $balance = max(0, $final_total - $total_paid);
        $payment_status = 'Outstanding';
        if ($balance <= 0) $payment_status = 'Paid in Full';
        elseif ($total_paid > 0) $payment_status = 'Partially Paid';

        // 3. Commit Master Updates to the Database
        $updStmt = $pdo->prepare("UPDATE reservations SET 
            guest_name=?, booking_date=?, guest_type=?, currency=?, room_tier=?, room_type=?, rooms_count=?, 
            check_in=?, check_out=?, guests_count=?, number_of_adults=?, number_of_children=?, 
            children_under_12=?, children_own_rooms=?, child_discount_type=?, discount=?, 
            total_amount=?, balance=?, payment_status=?, special_requests=? 
            WHERE id=?");
            
        $updStmt->execute([
            $guest_name, $booking_date, $guest_type, $enforced_currency, $room_tier, $room_type, $rooms_count,
            $check_in, $check_out, $guests_count, $numAdults, $numChildren,
            $under12, $childRooms, $discount_type, $total_discount,
            $final_total, $balance, $payment_status, $special_requests,
            $id
        ]);

        $updGuest = $pdo->prepare("UPDATE guest_registration_records SET 
            guest_name=?, room_type=?, check_in_date=?, check_out_date=?, 
            number_of_nights=?, number_of_rooms=?, payment_status=? 
            WHERE booking_id=?");
        $updGuest->execute([
            $guest_name, $room_type, $check_in, $check_out,
            $nights, $rooms_count, $payment_status,
            $id
        ]);

        $ip = $_SERVER['REMOTE_ADDR'];
        $pdo->prepare("INSERT INTO system_logs (username, role, action_code, action, ip_address) VALUES (?, ?, 'BOOKING_ALTERED', ?, ?)")
            ->execute([$_SESSION['username'], $_SESSION['role'], "Admin manually altered and recalculated financial particulars for Booking ID {$id} ({$guest_name}).", $ip]);

        ob_clean();
        echo json_encode(['success' => true]);
    } catch (Exception $e) {
        ob_clean();
        echo json_encode(['success' => false, 'message' => $e->getMessage()]);
    }
    exit;
}

// ==========================================
// HANDLE DATA FETCHING (GET)
// ==========================================
try {
    $pdo->exec("UPDATE reservations SET status = 'Checked Out' WHERE status = 'Booked' AND check_out < CURDATE()");

    // NEW: We now pull `actual_paid` using a subquery to accurately compute the live balance
    $query = "SELECT r.*,
                COALESCE(r.booking_date, DATE(r.created_at)) AS display_booking_date,
                r.check_in AS check_in_date, 
                r.check_out AS check_out_date, 
                GREATEST(DATEDIFF(r.check_out, r.check_in), 1) AS number_of_nights, 
                COALESCE(r.rooms_count, 1) AS number_of_rooms,
                (SELECT COALESCE(SUM(p.amount_paid), 0) FROM payments p WHERE p.booking_id = r.id AND p.currency = CASE WHEN LOWER(COALESCE(r.guest_type, 'resident')) = 'resident' THEN 'KES' ELSE 'USD' END) as actual_paid,
                CASE 
                    WHEN r.status = 'Cancelled' THEN 'Cancelled'
                    WHEN r.status = 'Checked Out' THEN 'Checked Out'
                    WHEN r.payment_status IN ('Fully Paid', 'Paid in Full') THEN 'Fully Paid'
                    WHEN r.payment_status = 'Partially Paid' THEN 'Partially Paid'
                    ELSE 'Outstanding'
                END AS current_status
              FROM reservations r 
              ORDER BY r.check_in DESC";

    $stmt = $pdo->query($query);
    $records = $stmt->fetchAll(PDO::FETCH_ASSOC);

    ob_clean();
    echo json_encode(['success' => true, 'data' => $records]);
} catch (Exception $e) {
    ob_clean();
    echo json_encode(['success' => false, 'message' => $e->getMessage()]);
}
exit;
?>