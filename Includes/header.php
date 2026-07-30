<!-- Includes/header.php -->
<header class="bg-white dark:bg-slate-900 border-b border-slate-100 dark:border-slate-800 px-6 py-4 flex justify-between items-center shrink-0 shadow-sm z-10 transition-colors duration-300">
    
    <!-- Branding & Hamburger Section (Left) -->
    <div class="flex items-center gap-4">
        
        <!-- Hamburger Menu Toggle -->
        <button onclick="toggleGlobalSidebar()" class="w-9 h-9 flex items-center justify-center text-slate-500 dark:text-slate-400 hover:text-blue-600 dark:hover:text-blue-400 hover:bg-blue-50 dark:hover:bg-slate-800 rounded-lg transition-colors text-lg outline-none" title="Toggle Sidebar">
            <i class="fa-solid fa-bars"></i>
        </button>

        <!-- Brand Logo -->
        <div class="flex items-center gap-3">
            <div class="w-10 h-10 rounded-xl bg-blue-50 dark:bg-blue-900/30 text-blue-600 dark:text-blue-400 flex items-center justify-center shadow-inner shrink-0 transition-colors duration-300">
                <i class="fa-solid fa-hippo text-xl"></i>
            </div>
            <div class="hidden sm:block">
                <h1 class="text-base font-black text-slate-900 dark:text-white tracking-tight uppercase leading-none transition-colors duration-300">RHINO TOURIST RESERVATION SYSTEM</h1>
                <span class="text-[10px] text-slate-400 dark:text-slate-500 font-medium block mt-1 tracking-wider uppercase transition-colors duration-300">Rhino Tourist Camp Front-Desk Operations Ledger Console</span>
            </div>
        </div>
    </div>
    
    <!-- Context, Theme & Notifications (Right) -->
    <div class="flex items-center gap-3 sm:gap-4">
        
        <!-- Live Real-Time Clock Badge -->
        <div class="hidden md:flex items-center gap-2 border border-slate-200 dark:border-slate-700 bg-slate-50/50 dark:bg-slate-800/50 px-3 py-2 rounded-lg shadow-sm text-slate-700 dark:text-slate-300 transition-colors duration-300">
            <i class="fa-regular fa-calendar text-blue-600 dark:text-blue-400"></i>
            <span class="text-xs font-bold text-slate-500 dark:text-slate-400 uppercase tracking-wide">Context:</span>
            <span id="global-real-time-clock" class="text-xs font-bold font-mono tracking-wider text-slate-800 dark:text-slate-200">
                <!-- Clock injected via JS -->
            </span>
        </div>

        <!-- Dark/Light Mode Toggle -->
        <button onclick="toggleTheme()" class="relative w-9 h-9 flex items-center justify-center text-slate-500 dark:text-slate-400 hover:text-amber-500 dark:hover:text-amber-400 hover:bg-slate-50 dark:hover:bg-slate-700 transition-colors border border-slate-200 dark:border-slate-700 bg-white dark:bg-slate-800 rounded-lg shadow-sm outline-none" title="Switch Theme">
            <i id="theme-icon" class="fa-solid fa-moon text-sm"></i>
        </button>
        
        <!-- Notification Bell (Now accurately linked and dynamic) -->
        <a href="notifications.php" class="relative w-9 h-9 flex items-center justify-center text-slate-500 dark:text-slate-400 hover:text-slate-700 dark:hover:text-slate-200 transition-colors border border-slate-200 dark:border-slate-700 bg-white dark:bg-slate-800 rounded-lg hover:bg-slate-50 dark:hover:bg-slate-700 shadow-sm outline-none" title="Follow-up Alerts">
            <i class="fa-regular fa-bell text-sm"></i>
            <span id="global-notification-badge" class="absolute top-0 right-0 -mt-1 -mr-1 hidden h-4 w-4 items-center justify-center rounded-full bg-rose-500 text-[9px] font-bold text-white shadow ring-2 ring-white dark:ring-slate-900">0</span>
        </a>

    </div>
</header>

<!-- Global Notification Engine -->
<script>
    function updateGlobalNotificationBadge() {
        fetch('api/notifications_api.php?action=fetch_notifications')
            .then(res => res.json())
            .then(data => {
                if (data.success) {
                    const badge = document.getElementById('global-notification-badge');
                    if (data.data.length > 0) {
                        badge.innerText = data.data.length;
                        badge.classList.remove('hidden');
                        badge.classList.add('flex');
                    } else {
                        badge.classList.add('hidden');
                        badge.classList.remove('flex');
                    }
                }
            })
            .catch(err => console.log('Notification Check Silently Failed'));
    }

    document.addEventListener("DOMContentLoaded", () => {
        updateGlobalNotificationBadge();
        // Check for new notifications every 45 seconds automatically
        setInterval(updateGlobalNotificationBadge, 45000); 
    });
</script>