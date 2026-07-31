<?php
// Includes/load_settings.php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Fallback defaults
$GLOBALS['system_settings'] = [
    // Global Header
    'header_display_type' => 'icon', 
    'header_logo_path' => '',
    'header_title' => 'RHINO TOURIST RESERVATION SYSTEM',
    'header_subtitle' => 'Rhino Tourist Camp Front-Desk Operations Ledger Console',
    'header_icon' => 'fa-hippo',
    
    // Sidebar
    'sidebar_display_type' => 'icon', 
    'logo_path' => '', 
    'sidebar_title' => 'Rhino Camp',
    'sidebar_subtitle' => 'Reservation Suite',
    'sidebar_icon' => 'fa-campground',
    
    // Navigation Links
    'nav_dashboard_name' => 'Dashboard', 'nav_dashboard_icon' => 'fa-chart-pie',
    'nav_calendar_name' => 'Calendar Matrix', 'nav_calendar_icon' => 'fa-calendar-days',
    'nav_guest_name' => 'Guest Register', 'nav_guest_icon' => 'fa-address-book',
    'nav_alerts_name' => 'Follow-up Alerts', 'nav_alerts_icon' => 'fa-bell',
    'nav_rates_name' => 'Rates Controller', 'nav_rates_icon' => 'fa-tags',
    'nav_finance_name' => 'Payment & Billing', 'nav_finance_icon' => 'fa-receipt',

    // PDF & Print Document Graphics
    'rack_rates_header_path' => '',
    'rack_rates_footer_path' => '',
    'receipt_header_path' => '',
    'receipt_footer_path' => '',

    // Footer & Theme
    'footer_text' => 'RHINO TOURIST CAMP. ALL RIGHTS RESERVED.', 
    'theme_color' => 'safari',
    'custom_primary' => '#046a38',
    'custom_secondary' => '#10b981'
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
?>