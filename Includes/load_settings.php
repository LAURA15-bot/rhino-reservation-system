<?php
// Includes/load_settings.php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Fallback defaults
$GLOBALS['system_settings'] = [
    'header_title' => 'RHINO TOURIST RESERVATION SYSTEM',
    'header_subtitle' => 'Rhino Tourist Camp Front-Desk Operations Ledger Console',
    'header_icon' => 'fa-hippo',
    'sidebar_title' => 'Rhino Camp',
    'sidebar_subtitle' => 'Reservation Suite',
    'sidebar_icon' => 'fa-campground',
    'footer_text' => '© 2026 RHINO TOURIST CAMP. ALL RIGHTS RESERVED.',
    'theme_color' => 'emerald'
];

try {
    require_once __DIR__ . '/database.php';
    $stmt = $pdo->query("SELECT setting_key, setting_value FROM system_settings");
    $dbSettings = $stmt->fetchAll(PDO::FETCH_KEY_PAIR);
    
    if ($dbSettings) {
        $GLOBALS['system_settings'] = array_merge($GLOBALS['system_settings'], $dbSettings);
    }
} catch (Exception $e) {
    // Silently fallback to defaults if table isn't created yet
}