<?php
// api/payment_billing_api.php
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

$allow_retroactive = false;
try {
    $stmtSet = $pdo->query("SELECT setting_value FROM system_settings WHERE setting_key = 'allow_retroactive_bookings'");
    if ($stmtSet->fetchColumn() === '1') $allow_retroactive = true;
} catch(Exception $e) {}

// Auto-Maintenance Routine
try {
    if (!$allow_retroactive) {
        $pdo->exec("UPDATE reservations SET status = 'Cancelled' WHERE status = 'Reserved' AND check_in < CURDATE() AND payment_status IN ('Outstanding', 'Pending', '') AND is_historical = 0");
    }
    $pdo->exec("UPDATE reservations SET status = 'Checked Out' WHERE status IN ('Booked', 'Reserved') AND check_out < CURDATE() AND status != 'Cancelled'");
} catch (PDOException $e) {}

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
}

// Handle Payment Submission via POST
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['record_payment'])) {
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

        // Enforce Unique Reference Validation
        if (!empty($reference_no)) {
            $refCheck = $pdo->prepare("SELECT id FROM payments WHERE reference_no = ?");
            $refCheck->execute([$reference_no]);
            if ($refCheck->fetch()) {
                throw new Exception("Duplicate Error: The transaction reference '{$reference_no}' has already been used in the ledger.");
            }
        }

        $bStmt = $pdo->prepare("SELECT * FROM reservations WHERE id = ?");
        $bStmt->execute([$booking_id]);
        $booking = $bStmt->fetch();

        if (!empty($reference_no)) {
            $refCheck = $pdo->prepare("SELECT id FROM payments WHERE reference_no = ?");
            $refCheck->execute([$reference_no]);
            if ($refCheck->fetch()) {
                throw new Exception("Transaction reference '{$reference_no}' already exists in the system.");
            }
        }

        if (!$booking) throw new Exception("Invalid booking target reference.");

        if ($booking['status'] === 'Reserved' && $booking['check_in'] < date('Y-m-d') && $booking['is_historical'] == 0) {
            throw new Exception("Booking hold has expired. The check-in date must be edited to a valid future date before accepting payment.");
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
        
        $new_status = 'Outstanding';
        if ($new_balance <= 0) $new_status = 'Paid in Full';
        elseif ($new_total_paid > 0) $new_status = 'Partially Paid';

        $updStmt = $pdo->prepare("UPDATE reservations SET payment_status = ?, balance = ?, status = 'Booked' WHERE id = ?");
        $updStmt->execute([$new_status, $new_balance, $booking_id]);

        $pdo->commit();
        ob_clean();
        echo json_encode(['success' => true, 'message' => 'Payment successfully recorded!']);
    } catch (Exception $e) {
        if ($pdo->inTransaction()) $pdo->rollBack();
        ob_clean();
        echo json_encode(['success' => false, 'message' => $e->getMessage()]);
    }
    exit;
}

// Handle Data Fetching via GET
if ($_SERVER["REQUEST_METHOD"] == "GET" && isset($_GET['action']) && $_GET['action'] === 'fetch_billing') {
    try {
        $search_query = trim($_GET['search'] ?? '');

        // Fetch all records including cancelled ones so we can evaluate status explicitly on the frontend
        $sql = "SELECT r.*, 
                (SELECT COALESCE(SUM(p.amount_paid), 0) FROM payments p WHERE p.booking_id = r.id AND p.currency = CASE WHEN LOWER(COALESCE(r.guest_type, 'resident')) = 'resident' THEN 'KES' ELSE 'USD' END) as actual_paid
                FROM reservations r WHERE 1=1";
        $params = [];

        if (!empty($search_query)) {
            $sql .= " AND (r.guest_name LIKE ? OR r.id LIKE ? OR r.phone LIKE ? OR r.receipt_no LIKE ?)";
            $wildcard = "%$search_query%";
            $params = [$wildcard, $wildcard, $wildcard, $wildcard];
        }

        $sql .= " ORDER BY r.id DESC";
        $stmt = $pdo->prepare($sql);
        $stmt->execute($params);
        $reservations = $stmt->fetchAll();

        $payload = [];
        foreach($reservations as $res) {
            $guest_type = $res['guest_type'] ?? 'Resident';
            $actual_currency = (strtolower($guest_type) === 'resident') ? 'KES' : 'USD';

            $pricingObj = getBookingPricingData($pdo, $res['check_in'], $res['check_out'], $res['room_tier'] ?? 'Superior Tent', $res['room_type'], $guest_type, $res['rooms_count'] ?? 1, $res['number_of_adults'] ?? 1, $res['number_of_children'] ?? 0, $res['children_under_12'] ?? 1, $res['children_own_rooms'] ?? 0);
            
            $total = (float)($res['total_amount'] ?? 0);
            $discount = (float)($res['discount'] ?? 0);
            $paid = (float)($res['actual_paid'] ?? 0);
            $bal = max(0, $total - $paid);
            
            $live_total = (float)$pricingObj['total'];
            $historical_base_rack = $total + $discount; 
            
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

            $status = $res['payment_status'] ?? ($bal <= 0 ? 'Paid in Full' : ($paid > 0 ? 'Partially Paid' : 'Outstanding'));
            if ($res['status'] === 'Cancelled') {
                $status = 'Cancelled';
            } elseif ($res['status'] === 'Checked Out' && $status === 'Paid in Full') {
                $status = 'Checked Out';
            }

            $payload[] = [
                'res' => $res,
                'pricingObj' => $pricingObj,
                'actualCurrency' => $actual_currency,
                'total' => $total,
                'discount' => $discount,
                'paid' => $paid,
                'balance' => $bal,
                'computedStatus' => $status,
                'isPastDue' => ($res['status'] === 'Reserved' && $res['check_in'] < date('Y-m-d') && $res['is_historical'] == 0)
            ];
        }

        ob_clean();
        echo json_encode(['success' => true, 'data' => $payload]);
    } catch (Exception $e) {
        ob_clean();
        echo json_encode(['success' => false, 'message' => $e->getMessage()]);
    }
    exit;
}
?>