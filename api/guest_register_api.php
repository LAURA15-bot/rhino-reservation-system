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

try {
    // Auto-Checkout Routine
    $pdo->exec("UPDATE reservations SET status = 'Checked Out' WHERE status = 'Booked' AND check_out < CURDATE()");

    $query = "SELECT 
                guest_name, 
                room_type, 
                check_in AS check_in_date, 
                check_out AS check_out_date, 
                GREATEST(DATEDIFF(check_out, check_in), 1) AS number_of_nights, 
                COALESCE(rooms_count, 1) AS number_of_rooms,
                CASE 
                    WHEN status = 'Cancelled' THEN 'Cancelled'
                    WHEN status = 'Checked Out' THEN 'Checked Out'
                    WHEN payment_status IN ('Fully Paid', 'Paid in Full') THEN 'Fully Paid'
                    WHEN payment_status = 'Partially Paid' THEN 'Partially Paid'
                    ELSE 'Outstanding'
                END AS current_status
              FROM reservations 
              ORDER BY check_in DESC";

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