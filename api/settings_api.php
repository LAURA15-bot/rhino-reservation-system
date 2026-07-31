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

$action = $_POST['action'] ?? '';

try {
    $pdo->exec("CREATE TABLE IF NOT EXISTS `system_settings` (
        `setting_key` VARCHAR(50) NOT NULL,
        `setting_value` TEXT DEFAULT NULL,
        PRIMARY KEY (`setting_key`)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;");
} catch (PDOException $e) {}

// Helper function for uploading any image asset safely
function handleLogoUpload($fileInputKey) {
    if (isset($_FILES[$fileInputKey]) && $_FILES[$fileInputKey]['error'] === UPLOAD_ERR_OK) {
        $uploadDir = '../uploads/brand/';
        if (!is_dir($uploadDir)) mkdir($uploadDir, 0755, true);

        $fileTmpPath = $_FILES[$fileInputKey]['tmp_name'];
        $fileExtension = strtolower(pathinfo($_FILES[$fileInputKey]['name'], PATHINFO_EXTENSION));
        $allowedExtensions = ['jpg', 'jpeg', 'png', 'svg', 'webp'];
        
        if (in_array($fileExtension, $allowedExtensions)) {
            $newFileName = $fileInputKey . '_' . time() . '.' . $fileExtension;
            $dest_path = $uploadDir . $newFileName;
            
            if (move_uploaded_file($fileTmpPath, $dest_path)) {
                return 'uploads/brand/' . $newFileName;
            } else {
                throw new Exception("Server error: Could not save the uploaded image for {$fileInputKey}.");
            }
        } else {
            throw new Exception("Invalid file format. Please upload a PNG, JPG, SVG, or WEBP image.");
        }
    }
    return false;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && $action === 'save_settings') {
    try {
        $settings = [];
        
        // Define all permissible string fields
        $allowed_keys = [
            'header_display_type', 'header_title', 'header_subtitle', 'header_icon', 
            'sidebar_display_type', 'sidebar_title', 'sidebar_subtitle', 'sidebar_icon', 
            'nav_dashboard_name', 'nav_dashboard_icon', 'nav_calendar_name', 'nav_calendar_icon',
            'nav_guest_name', 'nav_guest_icon', 'nav_alerts_name', 'nav_alerts_icon',
            'nav_rates_name', 'nav_rates_icon', 'nav_finance_name', 'nav_finance_icon',
            'footer_text', 'theme_color', 'custom_primary', 'custom_secondary'
        ];

        foreach ($allowed_keys as $key) {
            if (isset($_POST[$key])) {
                $settings[$key] = trim($_POST[$key]);
            }
        }

        // Process potential file uploads separately
        if ($path = handleLogoUpload('sidebar_logo_file')) $settings['logo_path'] = $path;
        if ($path = handleLogoUpload('header_logo_file')) $settings['header_logo_path'] = $path;
        
        // Process the new Print Layout files
        if ($path = handleLogoUpload('rack_header_file')) $settings['rack_rates_header_path'] = $path;
        if ($path = handleLogoUpload('rack_footer_file')) $settings['rack_rates_footer_path'] = $path;
        if ($path = handleLogoUpload('receipt_header_file')) $settings['receipt_header_path'] = $path;
        if ($path = handleLogoUpload('receipt_footer_file')) $settings['receipt_footer_path'] = $path;

        if (empty($settings)) {
            throw new Exception("No valid settings data was received.");
        }

        $stmt = $pdo->prepare("INSERT INTO system_settings (setting_key, setting_value) VALUES (?, ?) ON DUPLICATE KEY UPDATE setting_value = VALUES(setting_value)");
        
        foreach ($settings as $key => $val) {
            $stmt->execute([$key, $val]);
        }

        $ip = $_SERVER['REMOTE_ADDR'];
        $section = $_POST['section_name'] ?? 'System';
        $pdo->prepare("INSERT INTO system_logs (username, role, action_code, action, ip_address) VALUES (?, ?, 'SETTINGS_UPDATE', ?, ?)")
            ->execute([$_SESSION['username'], $_SESSION['role'], "Updated {$section} branding and preferences.", $ip]);

        ob_clean();
        echo json_encode(['success' => true, 'message' => "{$section} successfully saved!"]);
    } catch (Exception $e) {
        ob_clean();
        echo json_encode(['success' => false, 'message' => $e->getMessage()]);
    }
    exit;
}
?>