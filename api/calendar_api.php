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

    // 2. Fetch Active Reservations
    $stmt = $pdo->query("SELECT * FROM reservations WHERE status != 'Cancelled'");
    $dbReservations = $stmt->fetchAll(PDO::FETCH_ASSOC);

    $jsReservations = [];
    foreach ($dbReservations as $row) {
        $date1 = new DateTime($row['check_in']);
        $date2 = new DateTime($row['check_out']);
        $nights = $date1->diff($date2)->days;
        if ($nights == 0) $nights = 1;

        $roomTypeFormatted = strtolower(explode(' ', $row['room_type'])[0]);

        $jsReservations[] = [
            'id' => (string)$row['id'],
            'sourceType' => $row['booking_source'] ?? 'direct',
            'agentName' => $row['agency_name'] ?? 'Direct Booking',
            'bookingOfficer' => $row['booking_officer'],
            'status' => $row['status'],
            'checkIn' => $row['check_in'],
            'nights' => $nights,
            'allocations' => [
                [
                    'clientNames' => $row['guest_name'],
                    'roomType' => $roomTypeFormatted,
                    'roomCount' => (int)$row['rooms_count'],
                    'guestsCount' => (int)$row['guests_count']
                ]
            ]
        ];
    }
    
    ob_clean();
    // Return both the reservations and the dynamic rooms
    echo json_encode(['success' => true, 'data' => $jsReservations, 'rooms' => $dbRooms]);
} catch (Exception $e) {
    ob_clean();
    echo json_encode(['success' => false, 'message' => $e->getMessage()]);
}
exit;
?>