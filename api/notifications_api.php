<?php
// api/notifications_api.php
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

// Auto-patch Database to support the Followed Up state if it doesn't exist
try {
    $pdo->exec("ALTER TABLE reservations ADD COLUMN is_followed_up TINYINT(1) NOT NULL DEFAULT 0");
} catch (PDOException $e) {
    // Column already exists, continue silently.
}

// ==============================================================================
// UNIFIED PRICING ENGINE (Harmonized with Dashboard API)
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

// Security Helper: Ensure consultants only manipulate their own records
function verifyOwnership($pdo, $id, $role, $username) {
    if ($role === 'admin') return true;
    $check = $pdo->prepare("SELECT booking_officer, recorded_by FROM reservations WHERE id = ?");
    $check->execute([$id]);
    $res = $check->fetch(PDO::FETCH_ASSOC);
    if ($res) {
        $officer = strtolower($res['booking_officer'] ?? '');
        $recordedBy = strtolower($res['recorded_by'] ?? '');
        if ($officer === $username || $recordedBy === $username) return true;
    }
    return false;
}

$action = $_POST['action'] ?? $_GET['action'] ?? '';
$role = strtolower($_SESSION['role'] ?? 'consultant');
$username = strtolower($_SESSION['username'] ?? '');

// 1. FETCH NOTIFICATIONS
if ($action === 'fetch_notifications') {
    try {
        $sql = "SELECT r.*, 
                (SELECT COALESCE(SUM(p.amount_paid), 0) FROM payments p WHERE p.booking_id = r.id AND p.currency = CASE WHEN LOWER(COALESCE(r.guest_type, 'resident')) = 'resident' THEN 'KES' ELSE 'USD' END) as actual_paid
                FROM reservations r WHERE r.status = 'Reserved'";
        $stmt = $pdo->query($sql);
        $reservations = $stmt->fetchAll(PDO::FETCH_ASSOC);
        $payload = [];
        $today = new DateTime(date('Y-m-d'));

        foreach ($reservations as $row) {
            if ($role !== 'admin') {
                $officer = strtolower($row['booking_officer'] ?? '');
                $recordedBy = strtolower($row['recorded_by'] ?? '');
                if ($officer !== $username && $recordedBy !== $username) continue; 
            }

            $total = (float)($row['total_amount'] ?? 0);
            $paid = (float)($row['actual_paid'] ?? 0);
            if ($paid >= $total && $total > 0) continue; 

            $checkInDate = new DateTime($row['check_in']);
            $interval = $today->diff($checkInDate);
            $daysLeft = (int)$interval->format('%R%a'); 
            
            if ($daysLeft > 3) continue;

            $urgency = 'Warning';
            if ($daysLeft < 0) $urgency = 'Expired';
            elseif ($daysLeft == 0) $urgency = 'Today';
            elseif ($daysLeft == 1) $urgency = 'Tomorrow';
            else $urgency = "In $daysLeft Days";

            $payload[] = [
                'id' => $row['id'],
                'guest_name' => $row['guest_name'],
                'phone' => $row['phone'] ?? 'N/A',
                'room_type' => $row['room_type'],
                'check_in' => $row['check_in'],
                'check_out' => $row['check_out'],
                'booking_source' => $row['booking_source'] ?? 'Direct Client',
                'agency_name' => $row['agency_name'] ?? '',
                'total' => $total,
                'paid' => $paid,
                'balance' => max(0, $total - $paid),
                'currency' => (strtolower($row['guest_type'] ?? 'Resident') === 'resident') ? 'KES' : 'USD',
                'days_left' => $daysLeft,
                'urgency' => $urgency,
                'booking_officer' => $row['booking_officer'] ?? 'Front Desk',
                'recorded_by' => $row['recorded_by'] ?? 'Admin',
                'is_followed_up' => (int)($row['is_followed_up'] ?? 0)
            ];
        }

        usort($payload, function($a, $b) { return $a['days_left'] <=> $b['days_left']; });

        ob_clean();
        echo json_encode(['success' => true, 'data' => $payload]);
    } catch (Exception $e) {
        ob_clean();
        echo json_encode(['success' => false, 'message' => $e->getMessage()]);
    }
    exit;
}

// 2. MARK FOLLOWED UP
if ($action === 'mark_followed_up') {
    try {
        $id = (int)($_POST['id'] ?? 0);
        if (!verifyOwnership($pdo, $id, $role, $username)) {
            throw new Exception('Unauthorized: You can only modify your own reservations.');
        }
        $pdo->prepare("UPDATE reservations SET is_followed_up = 1 WHERE id = ?")->execute([$id]);
        ob_clean();
        echo json_encode(['success' => true]);
    } catch (Exception $e) {
        ob_clean();
        echo json_encode(['success' => false, 'message' => $e->getMessage()]);
    }
    exit;
}

// 3. RESCHEDULE RESERVATION (With Capacity Validation!)
if ($action === 'reschedule_reservation') {
    try {
        $id = (int)($_POST['id'] ?? 0);
        $check_in = trim($_POST['check_in'] ?? '');
        $check_out = trim($_POST['check_out'] ?? '');
        $todayStr = date('Y-m-d');

        if (!$check_in || !$check_out || $check_in >= $check_out) {
            throw new Exception('Invalid dates provided. Check-out must be after check-in.');
        }
        if ($check_in < $todayStr) {
            throw new Exception("Validation Error! You cannot reschedule a booking to a date in the past.");
        }

        if (!verifyOwnership($pdo, $id, $role, $username)) {
            throw new Exception('Unauthorized: You can only modify your own reservations.');
        }

        // Fetch Reservation Details
        $stmt = $pdo->prepare("SELECT * FROM reservations WHERE id = ?");
        $stmt->execute([$id]);
        $resDetails = $stmt->fetch(PDO::FETCH_ASSOC);
        if (!$resDetails) throw new Exception("Reservation not found.");

        $room_type = $resDetails['room_type'];
        $rooms_count = (int)$resDetails['rooms_count'];

        // --- INVENTORY CAPACITY CHECK ---
        $invStmt = $pdo->query("SELECT type, total_inventory FROM rooms");
        $inventory_limits = [];
        while($r = $invStmt->fetch(PDO::FETCH_ASSOC)) {
            $inventory_limits[$r['type']] = (int)$r['total_inventory'];
        }

        $checkSql = "SELECT SUM(rooms_count) as booked_rooms FROM reservations WHERE room_type = ? AND status != 'Cancelled' AND check_in < ? AND check_out > ? AND id != ?";
        $checkStmt = $pdo->prepare($checkSql);
        $checkStmt->execute([$room_type, $check_out, $check_in, $id]);
        $row = $checkStmt->fetch(PDO::FETCH_ASSOC);
        $currently_booked = $row['booked_rooms'] ? (int)$row['booked_rooms'] : 0;
        
        $max_available = $inventory_limits[$room_type] ?? 5;

        if (($currently_booked + $rooms_count) > $max_available) {
            $available_left = max(0, $max_available - $currently_booked);
            throw new Exception("Overbooking Error! Only {$available_left} " . htmlspecialchars($room_type) . "(s) available for the selected dates.");
        }

        // --- RECALCULATE PRICING ---
        $old_nights = max(1, (new DateTime($resDetails['check_in']))->diff(new DateTime($resDetails['check_out']))->days);
        $discount_per_room_per_night = (float)$resDetails['discount'] / ($old_nights * max(1, $rooms_count));

        $calc_results = calculateBookingTotal(
            $pdo, $check_in, $check_out, $resDetails['room_tier'], $room_type, 
            $resDetails['guest_type'], $rooms_count, $resDetails['number_of_adults'], 
            $resDetails['number_of_children'], $resDetails['children_under_12'], 
            $resDetails['children_own_rooms'], $discount_per_room_per_night
        );

        $new_total = $calc_results['final_total'];
        $new_discount = $calc_results['total_discount'];

        // Get currently paid amount to calculate the new accurate balance
        $enforced_currency = (strtolower($resDetails['guest_type'] ?? 'Resident') === 'resident') ? 'KES' : 'USD';
        $pSumStmt = $pdo->prepare("SELECT SUM(amount_paid) as total_paid FROM payments WHERE booking_id = ? AND currency = ?");
        $pSumStmt->execute([$id, $enforced_currency]);
        $sumRes = $pSumStmt->fetch();
        $current_total_paid = (float)($sumRes['total_paid'] ?? 0);
        
        $new_balance = max(0, $new_total - $current_total_paid);
        $new_payment_status = 'Outstanding';
        if ($new_balance <= 0) $new_payment_status = 'Paid in Full';
        elseif ($current_total_paid > 0) $new_payment_status = 'Partially Paid';

        // Commit Updates
        $pdo->prepare("UPDATE reservations SET check_in = ?, check_out = ?, total_amount = ?, discount = ?, balance = ?, payment_status = ?, is_followed_up = 1 WHERE id = ?")
            ->execute([$check_in, $check_out, $new_total, $new_discount, $new_balance, $new_payment_status, $id]);
        
        ob_clean();
        echo json_encode(['success' => true]);
    } catch (Exception $e) {
        ob_clean();
        echo json_encode(['success' => false, 'message' => $e->getMessage()]);
    }
    exit;
}

// 4. CANCEL RESERVATION
if ($action === 'cancel_reservation') {
    try {
        $id = (int)($_POST['id'] ?? 0);
        if (!verifyOwnership($pdo, $id, $role, $username)) {
            throw new Exception('Unauthorized: You can only cancel your own reservations.');
        }
        $pdo->prepare("UPDATE reservations SET status = 'Cancelled' WHERE id = ?")->execute([$id]);
        ob_clean();
        echo json_encode(['success' => true]);
    } catch (Exception $e) {
        ob_clean();
        echo json_encode(['success' => false, 'message' => $e->getMessage()]);
    }
    exit;
}
?>