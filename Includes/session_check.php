<?php
// Includes/session_check.php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Session timeout limit in seconds (15 minutes = 900 seconds)
$inactive_limit = 900; 

if (isset($_SESSION['logged_in']) && $_SESSION['logged_in'] === true) {
    if (isset($_SESSION['last_activity']) && (time() - $_SESSION['last_activity'] > $inactive_limit)) {
        // Record session timeout in audit logs
        require_once __DIR__ . '/database.php';
        $ip = $_SERVER['REMOTE_ADDR'] ?? 'UNKNOWN';
        try {
            $pdo->prepare("INSERT INTO system_logs (username, role, action_code, action, ip_address) VALUES (?, ?, 'SESSION_TIMEOUT', 'User session expired due to prolonged inactivity.', ?)")
                ->execute([$_SESSION['username'] ?? 'Unknown', $_SESSION['role'] ?? 'Consultant', $ip]);
        } catch (Exception $e) {}

        // Clear session data
        session_unset();
        session_destroy();
        
        // Handle AJAX vs standard page requests
        if (!empty($_SERVER['HTTP_X_REQUESTED_WITH']) && strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) === 'xmlhttprequest') {
            header('Content-Type: application/json');
            echo json_encode(['success' => false, 'expired' => true, 'message' => 'Session expired due to inactivity.']);
            exit;
        }

        header("Location: login.php?timeout=1");
        exit;
    }
    
    // Refresh last activity timestamp on active requests
    $_SESSION['last_activity'] = time();
}
?>