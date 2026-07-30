<?php
// Determine the current active page to highlight in the sidebar
$current_page = basename($_SERVER['PHP_SELF']);

// Normalize the role to lowercase for safe checking
$user_role = isset($_SESSION['role']) ? strtolower($_SESSION['role']) : 'consultant';

// Helper function for active link styling updated for Dark Mode
function navLinkClass($page, $current_page) {
    if ($current_page == $page) {
        return 'bg-blue-600 text-white shadow-md';
    } else {
        return 'text-slate-500 dark:text-slate-400 hover:bg-blue-50 dark:hover:bg-slate-800 hover:text-blue-600 dark:hover:text-blue-400';
    }
}
?>
<!-- PERSISTENT SIDEBAR NAVIGATION -->
<aside id="global-sidebar" class="w-64 bg-white dark:bg-slate-900 border-r border-slate-200 dark:border-slate-800 hidden lg:flex flex-col justify-between shrink-0 transition-all duration-300 z-20 overflow-hidden select-none">
    <div class="space-y-6">
        
        <!-- Brand Header -->
        <div class="flex items-center gap-3 px-4 pt-6 pb-2 whitespace-nowrap overflow-hidden">
            <div class="bg-blue-600 text-white p-2.5 rounded-xl shrink-0 flex items-center justify-center w-11 h-11 shadow-sm">
                <i class="fa-solid fa-campground text-lg"></i>
            </div>
            <div class="sidebar-text transition-opacity duration-300">
                <h1 class="text-xs font-black tracking-wider uppercase text-slate-900 dark:text-white transition-colors">Rhino Camp</h1>
                <p class="text-[10px] text-slate-400 dark:text-slate-500 font-medium transition-colors">Reservation Suite</p>
            </div>
        </div>

        <!-- Required Navigation Menu Items -->
        <nav class="space-y-2 px-3">
            <a href="dashboard.php" class="nav-link-container flex items-center px-3 py-2.5 rounded-xl text-xs font-bold transition-colors whitespace-nowrap <?php echo navLinkClass('dashboard.php', $current_page); ?>" title="Dashboard">
                <div class="w-6 flex items-center justify-center shrink-0"><i class="fa-solid fa-chart-pie text-sm"></i></div>
                <span class="sidebar-text ml-3">Dashboard</span>
            </a>
            <a href="calendar.php" class="nav-link-container flex items-center px-3 py-2.5 rounded-xl text-xs font-bold transition-colors whitespace-nowrap <?php echo navLinkClass('calendar.php', $current_page); ?>" title="Calendar Matrix">
                <div class="w-6 flex items-center justify-center shrink-0"><i class="fa-solid fa-calendar-days text-sm"></i></div>
                <span class="sidebar-text ml-3">Calendar Matrix</span>
            </a>
            <a href="guest_register.php" class="nav-link-container flex items-center px-3 py-2.5 rounded-xl text-xs font-bold transition-colors whitespace-nowrap <?php echo navLinkClass('guest_register.php', $current_page); ?>" title="Guest Register">
                <div class="w-6 flex items-center justify-center shrink-0"><i class="fa-solid fa-address-book text-sm"></i></div>
                <span class="sidebar-text ml-3">Guest Register</span>
            </a>
   
            
            <a href="rates_controller.php" class="nav-link-container flex items-center px-3 py-2.5 rounded-xl text-xs font-bold transition-colors whitespace-nowrap <?php echo navLinkClass('rates_controller.php', $current_page); ?>" title="Rates Controller">
                <div class="w-6 flex items-center justify-center shrink-0"><i class="fa-solid fa-tags text-sm"></i></div>
                <span class="sidebar-text ml-3">Rates Controller</span>
            </a>
            <a href="payment_billing.php" class="nav-link-container flex items-center px-3 py-2.5 rounded-xl text-xs font-bold transition-colors whitespace-nowrap <?php echo navLinkClass('payment_billing.php', $current_page); ?>" title="Payment & Billing">
                <div class="w-6 flex items-center justify-center shrink-0"><i class="fa-solid fa-receipt text-sm"></i></div>
                <span class="sidebar-text ml-3">Payment & Billing</span>
            </a>
        </nav>

        <!-- ADMIN ONLY NAVIGATION SECTION -->
        <?php if ($user_role === 'admin'): ?>
        <div class="pt-4 mt-2 border-t border-slate-100 dark:border-slate-800 transition-colors">
            <p class="sidebar-text px-6 text-[10px] font-black text-slate-400 dark:text-slate-500 uppercase tracking-wider mb-3 transition-colors">Administration</p>
            <nav class="space-y-2 px-3">
                <a href="manage_rooms.php" class="nav-link-container flex items-center px-3 py-2.5 rounded-xl text-xs font-bold transition-colors whitespace-nowrap <?php echo navLinkClass('manage_rooms.php', $current_page); ?>" title="Property Setup">
                    <div class="w-6 flex items-center justify-center shrink-0"><i class="fa-solid fa-hotel text-sm"></i></div>
                    <span class="sidebar-text ml-3">Property Setup</span>
                </a>
                <a href="manage_users.php" class="nav-link-container flex items-center px-3 py-2.5 rounded-xl text-xs font-bold transition-colors whitespace-nowrap <?php echo navLinkClass('manage_users.php', $current_page); ?>" title="Identity Manager">
                    <div class="w-6 flex items-center justify-center shrink-0"><i class="fa-solid fa-users-gear text-sm"></i></div>
                    <span class="sidebar-text ml-3">Identity Manager</span>
                </a>
                <a href="system_logs.php" class="nav-link-container flex items-center px-3 py-2.5 rounded-xl text-xs font-bold transition-colors whitespace-nowrap <?php echo navLinkClass('system_logs.php', $current_page); ?>" title="Security Audits">
                    <div class="w-6 flex items-center justify-center shrink-0"><i class="fa-solid fa-shield-halved text-sm"></i></div>
                    <span class="sidebar-text ml-3">Security Audits</span>
                </a>
                
                <!-- SETTINGS PAGE LINK -->
                <a href="settings.php" class="nav-link-container flex items-center px-3 py-2.5 rounded-xl text-xs font-bold transition-colors whitespace-nowrap <?php echo navLinkClass('settings.php', $current_page); ?>" title="System Settings">
                    <div class="w-6 flex items-center justify-center shrink-0"><i class="fa-solid fa-gear text-sm"></i></div>
                    <span class="sidebar-text ml-3">System Settings</span>
                </a>
            </nav>
        </div>
        <?php endif; ?>

    </div>

    <!-- User Session Footer -->
    <div class="p-4 border-t border-slate-100 dark:border-slate-800 flex items-center justify-between whitespace-nowrap overflow-hidden transition-colors">
        <div class="sidebar-text truncate transition-opacity duration-300 pr-2">
            <p class="text-[10px] uppercase font-bold text-blue-500 dark:text-blue-400"><?php echo htmlspecialchars($_SESSION['role'] ?? 'Consultant'); ?></p>
            <p class="text-xs font-black text-slate-800 dark:text-white truncate transition-colors"><?php echo htmlspecialchars($_SESSION['username'] ?? 'User'); ?></p>
        </div>
        <a href="logout.php" class="w-8 h-8 flex items-center justify-center bg-slate-50 dark:bg-slate-800 text-slate-500 dark:text-slate-400 hover:bg-rose-50 dark:hover:bg-rose-900/30 hover:text-rose-600 dark:hover:text-rose-400 rounded-lg transition-colors shrink-0 outline-none" title="Secure Log Out">
            <i class="fa-solid fa-right-from-bracket text-sm"></i>
        </a>
    </div>
</aside>