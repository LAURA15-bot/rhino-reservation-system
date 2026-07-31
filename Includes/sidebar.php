<?php
$current_page = basename($_SERVER['PHP_SELF']);
$user_role = isset($_SESSION['role']) ? strtolower($_SESSION['role']) : 'consultant';

if (!isset($GLOBALS['system_settings'])) {
    require_once __DIR__ . '/load_settings.php';
}
$set = $GLOBALS['system_settings'];

$theme = $set['theme_color'] ?? 'emerald';
$primaryColor = '#046a38'; 
if ($theme === 'safari') $primaryColor = '#8B3C28';
elseif ($theme === 'kairi') $primaryColor = '#802b1f';
elseif ($theme === 'blue') $primaryColor = '#2563eb';
elseif ($theme === 'custom') $primaryColor = $set['custom_primary'] ?? '#046a38';

function navLinkStyle($page, $current_page, $primaryColor) {
    if ($current_page == $page) return "background-color: {$primaryColor}; color: #ffffff; box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.1);";
    return '';
}
function navLinkClass($page, $current_page) {
    if ($current_page == $page) return 'text-white';
    return 'text-slate-500 dark:text-slate-400 hover:bg-slate-100 dark:hover:bg-slate-800 hover:text-slate-900 dark:hover:text-white';
}

$displayType = $set['sidebar_display_type'] ?? 'icon';
$logoPath = $set['logo_path'] ?? '';

// Check if user left the text blank so we can maximize the logo or icon space
$hasSidebarText = !empty(trim($set['sidebar_title'])) || !empty(trim($set['sidebar_subtitle']));
?>

<!-- Minimalist Custom Scrollbar Styling for the Sidebar -->
<style>
    .sidebar-scroll::-webkit-scrollbar { width: 4px; }
    .sidebar-scroll::-webkit-scrollbar-track { background: transparent; }
    .sidebar-scroll::-webkit-scrollbar-thumb { background-color: #cbd5e1; border-radius: 10px; }
    .dark .sidebar-scroll::-webkit-scrollbar-thumb { background-color: #334155; }
</style>

<!-- PERSISTENT SIDEBAR NAVIGATION -->
<aside id="global-sidebar" class="w-64 bg-white dark:bg-slate-900 border-r border-slate-200 dark:border-slate-800 hidden lg:flex flex-col justify-between shrink-0 transition-all duration-300 z-20 overflow-hidden select-none">
    
    <!-- Scrollable Navigation Container -->
    <div class="flex-1 overflow-y-auto overflow-x-hidden sidebar-scroll space-y-6 pb-6">
        
        <!-- Brand Header (Dynamically Sizes Logo based on Text Presence) -->
        <div class="flex items-center gap-3 px-4 pt-6 pb-2 whitespace-nowrap overflow-hidden <?php echo !$hasSidebarText ? 'justify-center' : ''; ?> shrink-0">
            
            <?php if ($displayType === 'logo' && !empty($logoPath)): ?>
                <div class="shrink-0 flex items-center justify-center transition-all duration-300 <?php echo $hasSidebarText ? 'w-14 h-14' : 'w-full h-24 px-4'; ?>">
                    <img src="<?php echo htmlspecialchars($logoPath); ?>" alt="Brand Logo" class="w-full h-full object-contain drop-shadow-sm">
                </div>
            <?php else: ?>
                <!-- Fallback / Selected Icon -->
                <div class="text-white rounded-xl shrink-0 flex items-center justify-center shadow-sm transition-all duration-300 <?php echo $hasSidebarText ? 'w-11 h-11 text-lg' : 'w-14 h-14 text-2xl'; ?>" style="background-color: <?php echo $primaryColor; ?>;">
                    <i class="fa-solid <?php echo htmlspecialchars($set['sidebar_icon']); ?>"></i>
                </div>
            <?php endif; ?>

            <?php if ($hasSidebarText): ?>
            <div class="sidebar-text transition-opacity duration-300 flex flex-col justify-center">
                <?php if (!empty($set['sidebar_title'])): ?>
                    <h1 class="text-sm font-black tracking-wider uppercase text-slate-900 dark:text-white transition-colors leading-tight"><?php echo htmlspecialchars($set['sidebar_title']); ?></h1>
                <?php endif; ?>
                <?php if (!empty($set['sidebar_subtitle'])): ?>
                    <p class="text-[10px] text-slate-400 dark:text-slate-500 font-medium transition-colors leading-tight mt-0.5"><?php echo htmlspecialchars($set['sidebar_subtitle']); ?></p>
                <?php endif; ?>
            </div>
            <?php endif; ?>
        </div>

        <!-- Required Navigation Menu Items (Dynamically Populated) -->
        <nav class="space-y-2 px-3 shrink-0">
            <a href="dashboard.php" class="nav-link-container flex items-center px-3 py-2.5 rounded-xl text-xs font-bold transition-colors whitespace-nowrap <?php echo navLinkClass('dashboard.php', $current_page); ?>" style="<?php echo navLinkStyle('dashboard.php', $current_page, $primaryColor); ?>" title="<?php echo htmlspecialchars($set['nav_dashboard_name']); ?>">
                <div class="w-6 flex items-center justify-center shrink-0"><i class="fa-solid <?php echo htmlspecialchars($set['nav_dashboard_icon']); ?> text-sm"></i></div>
                <span class="sidebar-text ml-3"><?php echo htmlspecialchars($set['nav_dashboard_name']); ?></span>
            </a>
            <a href="calendar.php" class="nav-link-container flex items-center px-3 py-2.5 rounded-xl text-xs font-bold transition-colors whitespace-nowrap <?php echo navLinkClass('calendar.php', $current_page); ?>" style="<?php echo navLinkStyle('calendar.php', $current_page, $primaryColor); ?>" title="<?php echo htmlspecialchars($set['nav_calendar_name']); ?>">
                <div class="w-6 flex items-center justify-center shrink-0"><i class="fa-solid <?php echo htmlspecialchars($set['nav_calendar_icon']); ?> text-sm"></i></div>
                <span class="sidebar-text ml-3"><?php echo htmlspecialchars($set['nav_calendar_name']); ?></span>
            </a>
            <a href="guest_register.php" class="nav-link-container flex items-center px-3 py-2.5 rounded-xl text-xs font-bold transition-colors whitespace-nowrap <?php echo navLinkClass('guest_register.php', $current_page); ?>" style="<?php echo navLinkStyle('guest_register.php', $current_page, $primaryColor); ?>" title="<?php echo htmlspecialchars($set['nav_guest_name']); ?>">
                <div class="w-6 flex items-center justify-center shrink-0"><i class="fa-solid <?php echo htmlspecialchars($set['nav_guest_icon']); ?> text-sm"></i></div>
                <span class="sidebar-text ml-3"><?php echo htmlspecialchars($set['nav_guest_name']); ?></span>
            </a>
            <a href="notifications.php" class="nav-link-container flex items-center px-3 py-2.5 rounded-xl text-xs font-bold transition-colors whitespace-nowrap <?php echo navLinkClass('notifications.php', $current_page); ?>" style="<?php echo navLinkStyle('notifications.php', $current_page, $primaryColor); ?>" title="<?php echo htmlspecialchars($set['nav_alerts_name']); ?>">
                <div class="w-6 flex items-center justify-center shrink-0"><i class="fa-solid <?php echo htmlspecialchars($set['nav_alerts_icon']); ?> text-sm"></i></div>
                <span class="sidebar-text ml-3"><?php echo htmlspecialchars($set['nav_alerts_name']); ?></span>
            </a>
            <a href="rates_controller.php" class="nav-link-container flex items-center px-3 py-2.5 rounded-xl text-xs font-bold transition-colors whitespace-nowrap <?php echo navLinkClass('rates_controller.php', $current_page); ?>" style="<?php echo navLinkStyle('rates_controller.php', $current_page, $primaryColor); ?>" title="<?php echo htmlspecialchars($set['nav_rates_name']); ?>">
                <div class="w-6 flex items-center justify-center shrink-0"><i class="fa-solid <?php echo htmlspecialchars($set['nav_rates_icon']); ?> text-sm"></i></div>
                <span class="sidebar-text ml-3"><?php echo htmlspecialchars($set['nav_rates_name']); ?></span>
            </a>
            <a href="payment_billing.php" class="nav-link-container flex items-center px-3 py-2.5 rounded-xl text-xs font-bold transition-colors whitespace-nowrap <?php echo navLinkClass('payment_billing.php', $current_page); ?>" style="<?php echo navLinkStyle('payment_billing.php', $current_page, $primaryColor); ?>" title="<?php echo htmlspecialchars($set['nav_finance_name']); ?>">
                <div class="w-6 flex items-center justify-center shrink-0"><i class="fa-solid <?php echo htmlspecialchars($set['nav_finance_icon']); ?> text-sm"></i></div>
                <span class="sidebar-text ml-3"><?php echo htmlspecialchars($set['nav_finance_name']); ?></span>
            </a>
        </nav>

        <!-- ADMIN ONLY NAVIGATION SECTION (Hardcoded for safety) -->
        <?php if ($user_role === 'admin'): ?>
        <div class="pt-4 mt-2 border-t border-slate-100 dark:border-slate-800 transition-colors shrink-0">
            <p class="sidebar-text px-6 text-[10px] font-black text-slate-400 dark:text-slate-500 uppercase tracking-wider mb-3 transition-colors">Administration</p>
            <nav class="space-y-2 px-3">
                <a href="manage_rooms.php" class="nav-link-container flex items-center px-3 py-2.5 rounded-xl text-xs font-bold transition-colors whitespace-nowrap <?php echo navLinkClass('manage_rooms.php', $current_page); ?>" style="<?php echo navLinkStyle('manage_rooms.php', $current_page, $primaryColor); ?>" title="Property Setup">
                    <div class="w-6 flex items-center justify-center shrink-0"><i class="fa-solid fa-hotel text-sm"></i></div>
                    <span class="sidebar-text ml-3">Property Setup</span>
                </a>
                <a href="manage_users.php" class="nav-link-container flex items-center px-3 py-2.5 rounded-xl text-xs font-bold transition-colors whitespace-nowrap <?php echo navLinkClass('manage_users.php', $current_page); ?>" style="<?php echo navLinkStyle('manage_users.php', $current_page, $primaryColor); ?>" title="Identity Manager">
                    <div class="w-6 flex items-center justify-center shrink-0"><i class="fa-solid fa-users-gear text-sm"></i></div>
                    <span class="sidebar-text ml-3">Identity Manager</span>
                </a>
                <a href="system_logs.php" class="nav-link-container flex items-center px-3 py-2.5 rounded-xl text-xs font-bold transition-colors whitespace-nowrap <?php echo navLinkClass('system_logs.php', $current_page); ?>" style="<?php echo navLinkStyle('system_logs.php', $current_page, $primaryColor); ?>" title="Security Audits">
                    <div class="w-6 flex items-center justify-center shrink-0"><i class="fa-solid fa-shield-halved text-sm"></i></div>
                    <span class="sidebar-text ml-3">Security Audits</span>
                </a>
                <a href="settings.php" class="nav-link-container flex items-center px-3 py-2.5 rounded-xl text-xs font-bold transition-colors whitespace-nowrap <?php echo navLinkClass('settings.php', $current_page); ?>" style="<?php echo navLinkStyle('settings.php', $current_page, $primaryColor); ?>" title="System Settings">
                    <div class="w-6 flex items-center justify-center shrink-0"><i class="fa-solid fa-gear text-sm"></i></div>
                    <span class="sidebar-text ml-3">System Settings</span>
                </a>
            </nav>
        </div>
        <?php endif; ?>

    </div>

    <!-- User Session Footer (Permanently Docked at Bottom via shrink-0) -->
    <div class="p-4 border-t border-slate-100 dark:border-slate-800 flex items-center justify-between whitespace-nowrap overflow-hidden transition-colors shrink-0">
        <div class="sidebar-text truncate transition-opacity duration-300 pr-2">
            <p class="text-[10px] uppercase font-bold transition-colors" style="color: <?php echo $primaryColor; ?>;">
                <?php echo htmlspecialchars($_SESSION['role'] ?? 'Consultant'); ?>
            </p>
            <p class="text-xs font-black text-slate-800 dark:text-white truncate transition-colors"><?php echo htmlspecialchars($_SESSION['username'] ?? 'User'); ?></p>
        </div>
        <a href="logout.php" class="w-8 h-8 flex items-center justify-center bg-slate-50 dark:bg-slate-800 text-slate-500 dark:text-slate-400 hover:bg-rose-50 dark:hover:bg-rose-900/30 hover:text-rose-600 dark:hover:text-rose-400 rounded-lg transition-colors shrink-0 outline-none" title="Secure Log Out">
            <i class="fa-solid fa-right-from-bracket text-sm"></i>
        </a>
    </div>
</aside>