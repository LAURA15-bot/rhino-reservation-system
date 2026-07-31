<?php
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

$hDisplayType = $set['header_display_type'] ?? 'icon';
$hLogoPath = $set['header_logo_path'] ?? '';
$hasHeaderSub = !empty(trim($set['header_subtitle']));
?>
<!-- Includes/header.php -->
<header class="bg-white dark:bg-slate-900 border-b border-slate-100 dark:border-slate-800 px-6 py-4 flex justify-between items-center shrink-0 shadow-sm z-10 transition-colors duration-300">
    
    <!-- Branding & Hamburger Section (Left) -->
    <div class="flex items-center gap-4">
        
        <button onclick="toggleGlobalSidebar()" class="w-9 h-9 flex items-center justify-center text-slate-500 dark:text-slate-400 hover:bg-slate-100 dark:hover:bg-slate-800 rounded-lg transition-colors text-lg outline-none" style="--tw-text-opacity: 1; hover:color: <?php echo $primaryColor; ?>;" title="Toggle Sidebar">
            <i class="fa-solid fa-bars"></i>
        </button>

        <div class="flex items-center gap-3">
            <?php if ($hDisplayType === 'logo' && !empty($hLogoPath)): ?>
                <div class="shrink-0 flex items-center justify-center w-12 h-12">
                    <img src="<?php echo htmlspecialchars($hLogoPath); ?>" alt="Header Brand" class="max-w-full max-h-full object-contain drop-shadow-sm">
                </div>
            <?php else: ?>
                <div class="w-10 h-10 rounded-xl text-white flex items-center justify-center shadow-inner shrink-0 transition-colors duration-300" style="background-color: <?php echo $primaryColor; ?>;">
                    <i class="fa-solid <?php echo htmlspecialchars($set['header_icon']); ?> text-xl"></i>
                </div>
            <?php endif; ?>

            <div class="hidden sm:flex flex-col justify-center">
                <h1 class="text-base font-black text-slate-900 dark:text-white tracking-tight uppercase leading-none transition-colors duration-300"><?php echo htmlspecialchars($set['header_title']); ?></h1>
                <?php if ($hasHeaderSub): ?>
                    <span class="text-[10px] text-slate-400 dark:text-slate-500 font-medium block mt-1 tracking-wider uppercase transition-colors duration-300"><?php echo htmlspecialchars($set['header_subtitle']); ?></span>
                <?php endif; ?>
            </div>
        </div>
    </div>
    
    <div class="flex items-center gap-3 sm:gap-4">
        <!-- Live Real-Time Clock Badge -->
        <div class="hidden md:flex items-center gap-2 border border-slate-200 dark:border-slate-700 bg-slate-50/50 dark:bg-slate-800/50 px-3 py-2 rounded-lg shadow-sm text-slate-700 dark:text-slate-300 transition-colors duration-300">
            <i class="fa-regular fa-calendar" style="color: <?php echo $primaryColor; ?>;"></i>
            <span class="text-xs font-bold text-slate-500 dark:text-slate-400 uppercase tracking-wide">Context:</span>
            <span id="global-real-time-clock" class="text-xs font-bold font-mono tracking-wider text-slate-800 dark:text-slate-200"></span>
        </div>

        <button onclick="toggleTheme()" class="relative w-9 h-9 flex items-center justify-center text-slate-500 dark:text-slate-400 hover:text-amber-500 hover:bg-slate-50 dark:hover:bg-slate-700 transition-colors border border-slate-200 dark:border-slate-700 bg-white dark:bg-slate-800 rounded-lg shadow-sm outline-none">
            <i id="theme-icon" class="fa-solid fa-moon text-sm"></i>
        </button>
        
        <a href="notifications.php" class="relative w-9 h-9 flex items-center justify-center text-slate-500 dark:text-slate-400 hover:text-slate-700 dark:hover:text-slate-200 transition-colors border border-slate-200 dark:border-slate-700 bg-white dark:bg-slate-800 rounded-lg hover:bg-slate-50 dark:hover:bg-slate-700 shadow-sm outline-none">
            <i class="fa-regular fa-bell text-sm"></i>
            <span id="global-notification-badge" class="absolute top-0 right-0 -mt-1 -mr-1 hidden h-4 w-4 items-center justify-center rounded-full bg-rose-500 text-[9px] font-bold text-white shadow ring-2 ring-white">0</span>
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
        setInterval(updateGlobalNotificationBadge, 45000); 
    });
</script>