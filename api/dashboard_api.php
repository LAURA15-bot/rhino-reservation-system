<?php
// api/dashboard_api.php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

require '../Includes/database.php';
header('Content-Type: application/json');

// Global System Security & Protocol Fetch
$retro_setting = '0';
try {
    $stmtSet = $pdo->query("SELECT setting_value FROM system_settings WHERE setting_key = 'allow_retroactive_bookings'");
    $retro_setting = $stmtSet->fetchColumn() ?: '0';
} catch(Exception $e) {}

// ==============================================================================
// SYSTEM AUTO-MAINTENANCE ROUTINE
// ==============================================================================
try {
    // Only clear out expired holds if the record is NOT a protected historical entry
    $pdo->exec("UPDATE reservations SET status = 'Cancelled' WHERE status = 'Reserved' AND check_in < CURDATE() AND payment_status IN ('Outstanding', 'Pending', '') AND is_historical = 0");
    $pdo->exec("UPDATE reservations SET status = 'Checked Out' WHERE status IN ('Booked', 'Reserved') AND check_out < CURDATE() AND status != 'Cancelled'");
} catch (PDOException $e) { }

// ==============================================================================
// UNIFIED PRICING ENGINE 
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

// ==============================================================================
// HANDLE POST REQUESTS (Mutations: Save, Edit, Pay, Delete)
// ==============================================================================
if ($_SERVER["REQUEST_METHOD"] == "POST") {

    if (isset($_POST['ajax_add_reservation_batch']) || isset($_POST['ajax_edit_reservation_batch'])) {
        try {
            $isEdit = isset($_POST['ajax_edit_reservation_batch']);
            $edit_id = $isEdit ? (int)$_POST['reservation_id'] : 0;
            
            $booking_officer = $_POST['booking_officer'] ?? 'Front Desk';
            $booking_source = $_POST['booking_source'] ?? 'Direct Client';
            $agency_name = $_POST['agency_name'] ?? '';
            
            $allocations = json_decode($_POST['allocations'], true);
            $status = 'Reserved';

            $invStmt = $pdo->query("SELECT type, total_inventory FROM rooms");
            $inventory_limits = [];
            while($r = $invStmt->fetch(PDO::FETCH_ASSOC)) {
                $inventory_limits[$r['type']] = (int)$r['total_inventory'];
            }
            
            $todayStr = date('Y-m-d');
            
            // Rebuild Permission Checks based on settings tiers
            $is_admin = (strtolower($_SESSION['role'] ?? '') === 'admin');
            $can_backdate = false;
            if ($retro_setting === '2') {
                $can_backdate = true; // Unlocked for everyone
            } elseif ($retro_setting === '1' && $is_admin) {
                $can_backdate = true; // Unlocked for Admins only
            }

            foreach ($allocations as $alloc) {
                $room_type = $alloc['room_type'];
                $rooms_count = (int)$alloc['rooms_count'];
                $check_in = $alloc['check_in'];
                $check_out = $alloc['check_out'];

                if (!$isEdit && $check_in < $todayStr) {
                    if (!$can_backdate) {
                        throw new Exception("Security Error! Retroactive booking is disabled for your role. Please check settings.");
                    }
                }

                $checkSql = "SELECT SUM(rooms_count) as booked_rooms FROM reservations WHERE room_type = ? AND status != 'Cancelled' AND check_in < ? AND check_out > ?";
                $params = [$room_type, $check_out, $check_in];

                if ($isEdit) {
                    $checkSql .= " AND id != ?";
                    $params[] = $edit_id;
                }

                $checkStmt = $pdo->prepare($checkSql);
                $checkStmt->execute($params);
                $row = $checkStmt->fetch(PDO::FETCH_ASSOC);
                $currently_booked = $row['booked_rooms'] ? (int)$row['booked_rooms'] : 0;
                
                $max_available = $inventory_limits[$room_type] ?? 5;

                if (($currently_booked + $rooms_count) > $max_available) {
                    $available_left = max(0, $max_available - $currently_booked);
                    throw new Exception("Overbooking Error! Only {$available_left} " . htmlspecialchars($room_type) . "(s) available.");
                }
                if ((int)$alloc['children_own_rooms'] > $rooms_count) {
                    throw new Exception("Logic Error! Children rooms cannot exceed the total number of rooms allocated.");
                }
                if ((float)$alloc['discount'] < 0) {
                    throw new Exception("Validation Error! Discount cannot be a negative value.");
                }
            }

            $pdo->beginTransaction();

            if ($isEdit) {
                $delStmt = $pdo->prepare("UPDATE reservations SET status = 'Cancelled' WHERE id = ?");
                $delStmt->execute([$edit_id]);
            }

            // Mapped the booking_date explicitly, and passed is_historical
            $stmt = $pdo->prepare("INSERT INTO reservations (
                guest_name, phone, email, booking_date, check_in, check_out, guests_count, room_tier, guest_type, currency, 
                room_type, rooms_count, booking_officer, status, total_amount,
                number_of_adults, number_of_children, children_under_12, children_own_rooms, child_discount_type,
                booking_source, agency_name, discount, special_requests, is_historical
            ) VALUES (?, 'N/A', '', ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");

            foreach ($allocations as $alloc) {
                $currency = (strtolower($alloc['guest_type']) === 'resident') ? 'KES' : 'USD';
                $num_adults = (int)($alloc['number_of_adults'] ?? 1);
                $num_children = (int)($alloc['number_of_children'] ?? 0);
                $child_under_12 = (int)($alloc['children_under_12'] ?? 1);
                $child_own_rooms = (int)($alloc['children_own_rooms'] ?? 0);
                $line_discount = (float)($alloc['discount'] ?? 0);
                $special_requests = trim($alloc['special_requests'] ?? '');
                $discount_type = ($num_children > 0 && $child_under_12 == 1) ? (($child_own_rooms > 0) ? 'own_room' : 'sharing') : 'none';
                
                // Set the historical flag dynamically
                $is_historical = ($alloc['check_in'] < $todayStr) ? 1 : 0;

                $calc_results = calculateBookingTotal($pdo, $alloc['check_in'], $alloc['check_out'], $alloc['room_tier'], $alloc['room_type'], $alloc['guest_type'], $alloc['rooms_count'], $num_adults, $num_children, $child_under_12, $child_own_rooms, $line_discount);
                
                if ($calc_results['total_discount'] > $calc_results['original_total']) {
                    throw new Exception("Validation Error! The discount applied to " . htmlspecialchars($alloc['guest_name']) . " exceeds their total room cost.");
                }

                $stmt->execute([
                    $alloc['guest_name'], $todayStr, $alloc['check_in'], $alloc['check_out'], $alloc['guests_count'], 
                    $alloc['room_tier'], $alloc['guest_type'], $currency, $alloc['room_type'], $alloc['rooms_count'], 
                    $booking_officer, $status, $calc_results['final_total'], $num_adults, $num_children,
                    $child_under_12, $child_own_rooms, $discount_type, $booking_source, $agency_name, $calc_results['total_discount'], $special_requests, $is_historical
                ]);
            }

            $pdo->commit();
            echo json_encode(['success' => true]);
        } catch(Exception $e) {
            if ($pdo->inTransaction()) $pdo->rollBack();
            echo json_encode(['success' => false, 'message' => $e->getMessage()]);
        }
        exit;
    }

    if (isset($_POST['ajax_record_payment'])) {
        try {
            $booking_id = (int)$_POST['booking_id'];
            $amount_paid = (float)$_POST['amount_paid'];
            $payment_method = $_POST['payment_method'];
            $reference_no = trim($_POST['reference_no']);
            $recorded_by = $_SESSION['username'] ?? 'Admin';

            if ($amount_paid <= 0) throw new Exception("Payment amount must be greater than zero.");
            if (($payment_method === 'M-Pesa' || $payment_method === 'Bank Transfer') && empty($reference_no)) {
                throw new Exception("A reference or transaction number is required for M-Pesa and Bank Transfer payments.");
            }

            $bStmt = $pdo->prepare("SELECT * FROM reservations WHERE id = ?");
            $bStmt->execute([$booking_id]);
            $booking = $bStmt->fetch();

            if (!$booking) throw new Exception("Invalid booking target reference.");
            
            // Only enforce expiration block if the booking is NOT an authorized historical record
            if ($booking['status'] === 'Reserved' && $booking['check_in'] < date('Y-m-d') && $booking['is_historical'] == 0) {
                throw new Exception("Booking hold has expired. Please edit the check-in date to a valid future date before accepting payment.");
            }

            $enforced_currency = (strtolower($booking['guest_type'] ?? 'Resident') === 'resident') ? 'KES' : 'USD';

            $pSumStmt = $pdo->prepare("SELECT SUM(amount_paid) as total_paid FROM payments WHERE booking_id = ? AND currency = ?");
            $pSumStmt->execute([$booking_id, $enforced_currency]);
            $sumRes = $pSumStmt->fetch();
            $current_total_paid = (float)($sumRes['total_paid'] ?? 0);
            
            $total_amount = (float)$booking['total_amount'];
            $outstanding_balance = max(0, $total_amount - $current_total_paid);

            if ($amount_paid > $outstanding_balance) {
                throw new Exception("Payment error: Amount exceeds outstanding balance.");
            }

            $pdo->beginTransaction();

            $payStmt = $pdo->prepare("INSERT INTO payments (booking_id, amount_paid, currency, payment_method, reference_no, recorded_by) VALUES (?, ?, ?, ?, ?, ?)");
            $payStmt->execute([$booking_id, $amount_paid, $enforced_currency, $payment_method, $reference_no, $recorded_by]);
            
            $new_total_paid = $current_total_paid + $amount_paid;
            $new_balance = $total_amount - $new_total_paid;
            
            $new_payment_status = 'Outstanding';
            if ($new_balance <= 0) $new_payment_status = 'Paid in Full';
            elseif ($new_total_paid > 0) $new_payment_status = 'Partially Paid';

            $updStmt = $pdo->prepare("UPDATE reservations SET payment_status = ?, balance = ?, status = 'Booked' WHERE id = ?");
            $updStmt->execute([$new_payment_status, $new_balance, $booking_id]);

            $pdo->commit();
            echo json_encode(['success' => true]);
        } catch(Exception $e) {
            if ($pdo->inTransaction()) $pdo->rollBack();
            echo json_encode(['success' => false, 'message' => $e->getMessage()]);
        }
        exit;
    }

    if (isset($_POST['ajax_delete_reservation'])) {
        try {
            $id = (int)$_POST['id'];
            $del = $pdo->prepare("UPDATE reservations SET status = 'Cancelled' WHERE id = ?");
            $del->execute([$id]);
            echo json_encode(['success' => true]);
        } catch (Exception $e) {
            echo json_encode(['success' => false, 'message' => $e->getMessage()]);
        }
        exit;
    }

    echo json_encode(['success' => false, 'message' => 'Invalid POST request action']);
    exit;
}

if ($_SERVER["REQUEST_METHOD"] == "GET" && isset($_GET['action']) && $_GET['action'] === 'fetch_reservations') {
    
    $rStmt = $pdo->query("SELECT * FROM rooms ORDER BY id ASC");
    $dbRooms = $rStmt->fetchAll(PDO::FETCH_ASSOC);

    $sql = "SELECT r.*, 
            (SELECT COALESCE(SUM(p.amount_paid), 0) FROM payments p WHERE p.booking_id = r.id AND p.currency = CASE WHEN LOWER(COALESCE(r.guest_type, 'resident')) = 'resident' THEN 'KES' ELSE 'USD' END) as actual_paid
            FROM reservations r WHERE r.status != 'Cancelled'";
    $stmt = $pdo->query($sql);
    $dbReservations = $stmt->fetchAll(PDO::FETCH_ASSOC);

    $jsReservations = [];
    foreach ($dbReservations as $row) {
        $date1 = new DateTime($row['check_in']);
        $date2 = new DateTime($row['check_out']);
        $nights = $date1->diff($date2)->days;
        if ($nights == 0) $nights = 1;

        $roomTypeFormatted = $row['room_type'];
        $roomKey = strtolower(explode(' ', $roomTypeFormatted)[0]);
        
        $total_discount = (float)($row['discount'] ?? 0);
        $rooms_count = (int)$row['rooms_count'];
        $discountPerRoom = ($nights > 0 && $rooms_count > 0) ? ($total_discount / ($nights * $rooms_count)) : 0;

        $guest_type = $row['guest_type'] ?? 'Resident';
        $actual_currency = (strtolower($guest_type) === 'resident') ? 'KES' : 'USD';

        $pricingObj = getBookingPricingData($pdo, $row['check_in'], $row['check_out'], $row['room_tier'] ?? 'Superior Tent', $row['room_type'], $guest_type, $rooms_count, $row['number_of_adults'] ?? 1, $row['number_of_children'] ?? 0, $row['children_under_12'] ?? 1, $row['children_own_rooms'] ?? 0);
        
        $total = (float)($row['total_amount'] ?? 0);
        $paid = (float)($row['actual_paid'] ?? 0);
        $bal = max(0, $total - $paid);

        $live_total = (float)$pricingObj['total'];
        $historical_base_rack = $total + $total_discount; 
        
        if ($live_total > 0 && $historical_base_rack != $live_total) {
            $scale = $historical_base_rack / $live_total;
            $pricingObj['adult_total'] *= $scale;
            $pricingObj['child_total'] *= $scale;
            $pricingObj['total'] = $historical_base_rack;
        } elseif ($live_total == 0) {
            $pricingObj['adult_total'] = $historical_base_rack;
            $pricingObj['child_total'] = 0;
            $pricingObj['total'] = $historical_base_rack;
        }

        $jsReservations[] = [
            'id' => (string)$row['id'],
            'guest_name' => $row['guest_name'],
            'sourceType' => $row['booking_source'] ?? 'Direct Client',
            'agentName' => $row['agency_name'] ?? 'Direct Booking',
            'bookingOfficer' => $row['booking_officer'],
            'internalOfficer' => $_SESSION['username'] ?? 'Admin',
            'status' => $row['status'],
            'checkIn' => $row['check_in'],
            'nights' => $nights,
            'receiptNo' => $row['receipt_no'] ?? null,
            'isHistorical' => (int)($row['is_historical'] ?? 0),
            
            'actualCurrency' => $actual_currency,
            'totalAmount' => $total,
            'actualPaid' => $paid,
            'balance' => $bal,
            'discount' => $total_discount,
            'pricingObj' => $pricingObj,

            'allocations' => [
                [
                    'clientNames' => $row['guest_name'],
                    'roomTier' => $row['room_tier'],
                    'guestType' => $guest_type,
                    'roomType' => $roomKey,
                    'roomCount' => $rooms_count,
                    'guestsCount' => (int)$row['guests_count'],
                    'checkIn' => $row['check_in'],
                    'nights' => $nights,
                    'adults' => (int)($row['number_of_adults'] ?? 1),
                    'children' => (int)($row['number_of_children'] ?? 0),
                    'under12' => (int)($row['children_under_12'] ?? 1),
                    'childrenRooms' => (int)($row['children_own_rooms'] ?? 0),
                    'discountPerRoom' => round($discountPerRoom, 2),
                    'specialRequests' => $row['special_requests'] ?? ''
                ]
            ]
        ];
    }
    
    echo json_encode(['success' => true, 'data' => $jsReservations, 'rooms' => $dbRooms]);
    exit;
}
?>