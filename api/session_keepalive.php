<?php
// api/session_keepalive.php
ob_start();
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
header('Content-Type: application/json');

if (!isset($_SESSION['logged_in'])) {
    echo json_encode(['success' => false, 'message' => 'Unauthorized session.']);
    exit;
}

// Reset activity clock
$_SESSION['last_activity'] = time();

ob_clean();
echo json_encode(['success' => true]);
?>