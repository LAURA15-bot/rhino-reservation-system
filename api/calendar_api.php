<?php
// api/calendar_api.php
ob_start();
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

require '../Includes/database.php';
header('Content-Type: application/json');

try {
    // 1. Fetch Dynamic Room Configuration Database
    $rStmt = $pdo->query("SELECT * FROM rooms ORDER BY id ASC");
    $dbRooms = $rStmt->fetchAll(PDO::FETCH_ASSOC);

    // 2. Fetch Active Reservations with Payment Totals
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

        $roomTypeFormatted = strtolower(explode(' ', $row['room_type'])[0]);
        $total = (float)($row['total_amount'] ?? 0);
        $paid = (float)($row['actual_paid'] ?? 0);
        $bal = max(0, $total - $paid);
        $guest_type = $row['guest_type'] ?? 'Resident';
        $actual_currency = (strtolower($guest_type) === 'resident') ? 'KES' : 'USD';

        // Compute practical status for the calendar
        $computed_status = $row['payment_status'] ?? ($bal <= 0 ? 'Paid in Full' : ($paid > 0 ? 'Partially Paid' : 'Outstanding'));
        if ($row['status'] === 'Checked Out') $computed_status = 'Checked Out';

        $jsReservations[] = [
            'id' => (string)$row['id'],
            'guestName' => $row['guest_name'],
            'guestCount' => (int)$row['guests_count'],
            'sourceType' => $row['booking_source'] ?? 'Direct Client',
            'agentName' => $row['agency_name'] ?? '',
            'bookingOfficer' => $row['booking_officer'],
            'status' => $row['status'],
            'paymentStatus' => $computed_status,
            'currency' => $actual_currency,
            'total' => $total,
            'paid' => $paid,
            'balance' => $bal,
            'checkIn' => $row['check_in'],
            'checkOut' => $row['check_out'],
            'nights' => $nights,
            'roomTypeFull' => $row['room_type'],
            'allocations' => [
                [
                    'roomType' => $roomTypeFormatted,
                    'roomCount' => (int)$row['rooms_count']
                ]
            ]
        ];
    }
    
    ob_clean();
    echo json_encode(['success' => true, 'data' => $jsReservations, 'rooms' => $dbRooms]);
} catch (Exception $e) {
    ob_clean();
    echo json_encode(['success' => false, 'message' => $e->getMessage()]);
}
exit;
?>