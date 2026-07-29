<?php
// Determine the current active page to highlight in the sidebar
$current_page = basename($_SERVER['PHP_SELF']);

// Normalize the role to lowercase for safe checking
$user_role = isset($_SESSION['role']) ? strtolower($_SESSION['role']) : 'consultant';
?>
<!-- PERSISTENT SIDEBAR NAVIGATION -->
<aside id="global-sidebar" class="w-64 bg-white border-r border-slate-200 hidden lg:flex flex-col justify-between shrink-0 transition-all duration-300 z-20 overflow-hidden select-none">
    <div class="space-y-6">
        
        <!-- Brand Header -->
        <div class="flex items-center gap-3 px-4 pt-6 pb-2 whitespace-nowrap">
            <div class="bg-[#046a38] text-white p-2.5 rounded-xl shrink-0 flex items-center justify-center w-11 h-11 shadow-sm">
                <i class="fa-solid fa-campground text-lg"></i>
            </div>
            <div class="sidebar-text transition-opacity duration-300">
                <h1 class="text-xs font-black tracking-wider uppercase text-slate-900">Rhino Camp</h1>
                <p class="text-[10px] text-slate-400 font-medium">Reservation Suite</p>
            </div>
        </div>

        <!-- Required Navigation Menu Items -->
        <nav class="space-y-1.5 px-3">
            <a href="dashboard.php" class="flex items-center gap-3 px-3 py-2.5 rounded-xl text-xs font-bold transition shadow-sm whitespace-nowrap <?php echo ($current_page == 'dashboard.php') ? 'bg-[#046a38] text-white' : 'text-slate-600 hover:bg-slate-50'; ?>" title="Dashboard">
                <div class="w-5 flex items-center justify-center shrink-0"><i class="fa-solid fa-chart-pie text-sm"></i></div>
                <span class="sidebar-text">Dashboard</span>
            </a>
            <a href="calendar.php" class="flex items-center gap-3 px-3 py-2.5 rounded-xl text-xs font-bold transition shadow-sm whitespace-nowrap <?php echo ($current_page == 'calendar.php') ? 'bg-[#046a38] text-white' : 'text-slate-600 hover:bg-slate-50'; ?>" title="Calendar">
                <div class="w-5 flex items-center justify-center shrink-0"><i class="fa-solid fa-calendar-days text-sm"></i></div>
                <span class="sidebar-text">Calendar Matrix</span>
            </a>
            <a href="guest_register.php" class="flex items-center gap-3 px-3 py-2.5 rounded-xl text-xs font-bold transition shadow-sm whitespace-nowrap <?php echo ($current_page == 'guest_register.php') ? 'bg-[#046a38] text-white' : 'text-slate-600 hover:bg-slate-50'; ?>" title="Guest Register">
                <div class="w-5 flex items-center justify-center shrink-0"><i class="fa-solid fa-address-book text-sm"></i></div>
                <span class="sidebar-text">Guest Register</span>
            </a>
            <a href="rates_controller.php" class="flex items-center gap-3 px-3 py-2.5 rounded-xl text-xs font-bold transition shadow-sm whitespace-nowrap <?php echo ($current_page == 'rates_controller.php') ? 'bg-[#046a38] text-white' : 'text-slate-600 hover:bg-slate-50'; ?>" title="Rates Controller">
                <div class="w-5 flex items-center justify-center shrink-0"><i class="fa-solid fa-tags text-sm"></i></div>
                <span class="sidebar-text">Rates Controller</span>
            </a>
            <a href="payment_billing.php" class="flex items-center gap-3 px-3 py-2.5 rounded-xl text-xs font-bold transition shadow-sm whitespace-nowrap <?php echo ($current_page == 'payment_billing.php') ? 'bg-[#046a38] text-white' : 'text-slate-600 hover:bg-slate-50'; ?>" title="Payment & Billing">
                <div class="w-5 flex items-center justify-center shrink-0"><i class="fa-solid fa-receipt text-sm"></i></div>
                <span class="sidebar-text">Payment & Billing</span>
            </a>
        </nav>

        <!-- ADMIN ONLY NAVIGATION SECTION -->
        <?php if ($user_role === 'admin'): ?>
        <div class="pt-4 mt-2 border-t border-slate-100">
            <p class="sidebar-text px-6 text-[10px] font-black text-slate-400 uppercase tracking-wider mb-3">Administration</p>
            <nav class="space-y-1.5 px-3">
                <a href="manage_rooms.php" class="flex items-center gap-3 px-3 py-2.5 rounded-xl text-xs font-bold transition shadow-sm whitespace-nowrap <?php echo ($current_page == 'manage_rooms.php') ? 'bg-indigo-600 text-white' : 'text-slate-600 hover:bg-slate-50'; ?>" title="Edit Room Types">
                    <div class="w-5 flex items-center justify-center shrink-0"><i class="fa-solid fa-hotel text-sm"></i></div>
                    <span class="sidebar-text">Property Setup</span>
                </a>
                <a href="manage_users.php" class="flex items-center gap-3 px-3 py-2.5 rounded-xl text-xs font-bold transition shadow-sm whitespace-nowrap <?php echo ($current_page == 'manage_users.php') ? 'bg-indigo-600 text-white' : 'text-slate-600 hover:bg-slate-50'; ?>" title="Manage Users">
                    <div class="w-5 flex items-center justify-center shrink-0"><i class="fa-solid fa-users-gear text-sm"></i></div>
                    <span class="sidebar-text">Identity Manager</span>
                </a>
                <a href="system_logs.php" class="flex items-center gap-3 px-3 py-2.5 rounded-xl text-xs font-bold transition shadow-sm whitespace-nowrap <?php echo ($current_page == 'system_logs.php') ? 'bg-indigo-600 text-white' : 'text-slate-600 hover:bg-slate-50'; ?>" title="System Logs">
                    <div class="w-5 flex items-center justify-center shrink-0"><i class="fa-solid fa-shield-halved text-sm"></i></div>
                    <span class="sidebar-text">Security Audits</span>
                </a>
            </nav>
        </div>
        <?php endif; ?>

    </div>

    <!-- User Session Footer -->
    <div class="p-4 border-t border-slate-100 flex items-center justify-between whitespace-nowrap">
        <div class="sidebar-text truncate transition-opacity duration-300">
            <p class="text-[10px] uppercase font-bold text-slate-400"><?php echo htmlspecialchars($_SESSION['role'] ?? 'Consultant'); ?></p>
            <p class="text-xs font-black text-slate-800 truncate"><?php echo htmlspecialchars($_SESSION['username'] ?? 'User'); ?></p>
        </div>
        <a href="logout.php" class="w-8 h-8 flex items-center justify-center bg-slate-50 text-slate-400 hover:bg-rose-50 hover:text-rose-600 rounded-lg transition shrink-0 outline-none" title="Secure Log Out">
            <i class="fa-solid fa-right-from-bracket text-sm"></i>
        </a>
    </div>
</aside>