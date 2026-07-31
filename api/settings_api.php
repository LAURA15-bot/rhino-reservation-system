<?php
// api/settings_api.php
ob_start();
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

if (!isset($_SESSION['logged_in']) || strtolower($_SESSION['role']) !== 'admin') {
    header('Content-Type: application/json');
    echo json_encode(['success' => false, 'message' => 'Unauthorized: Admin privileges required.']);
    exit;
}

require '../Includes/database.php';
header('Content-Type: application/json');

$action = $_POST['action'] ?? $_GET['action'] ?? '';

// Auto-create table if missing
try {
    $pdo->exec("CREATE TABLE IF NOT EXISTS `system_settings` (
        `setting_key` VARCHAR(50) NOT NULL,
        `setting_value` TEXT DEFAULT NULL,
        PRIMARY KEY (`setting_key`)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;");
} catch (PDOException $e) {}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && $action === 'save_settings') {
    try {
        $settings = [
            'header_title' => trim($_POST['header_title'] ?? ''),
            'header_subtitle' => trim($_POST['header_subtitle'] ?? ''),
            'header_icon' => trim($_POST['header_icon'] ?? ''),
            'sidebar_title' => trim($_POST['sidebar_title'] ?? ''),
            'sidebar_subtitle' => trim($_POST['sidebar_subtitle'] ?? ''),
            'sidebar_icon' => trim($_POST['sidebar_icon'] ?? ''),
            'footer_text' => trim($_POST['footer_text'] ?? ''),
            'theme_color' => trim($_POST['theme_color'] ?? 'emerald')
        ];

        $stmt = $pdo->prepare("INSERT INTO system_settings (setting_key, setting_value) VALUES (?, ?) ON DUPLICATE KEY UPDATE setting_value = VALUES(setting_value)");
        
        foreach ($settings as $key => $val) {
            $stmt->execute([$key, $val]);
        }

        // Audit Log entry
        $ip = $_SERVER['REMOTE_ADDR'];
        $logAction = "Modified system branding and layout preferences.";
        $pdo->prepare("INSERT INTO system_logs (username, role, action_code, action, ip_address) VALUES (?, ?, 'SETTINGS_UPDATE', ?, ?)")
            ->execute([$_SESSION['username'], $_SESSION['role'], $logAction, $ip]);

        ob_clean();
        echo json_encode(['success' => true, 'message' => 'System settings successfully saved!']);
    } catch (Exception $e) {
        ob_clean();
        echo json_encode(['success' => false, 'message' => $e->getMessage()]);
    }
    exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'GET' && $action === 'fetch_settings') {
    try {
        $stmt = $pdo->query("SELECT setting_key, setting_value FROM system_settings");
        $results = $stmt->fetchAll(PDO::FETCH_KEY_PAIR);
        
        ob_clean();
        echo json_encode(['success' => true, 'data' => $results]);
    } catch (Exception $e) {
        ob_clean();
        echo json_encode(['success' => false, 'message' => $e->getMessage()]);
    }
    exit;
}
?>