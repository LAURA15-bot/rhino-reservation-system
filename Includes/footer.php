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
?>
<!-- Includes/footer.php -->
<footer class="bg-white dark:bg-slate-900 border-t border-slate-100 dark:border-slate-800 px-6 py-4 flex flex-col sm:flex-row justify-between items-center shrink-0 z-10 gap-2 transition-colors duration-300">
    
    <!-- Left Side: PHP Auto-Updating Copyright Year + Text -->
    <div class="text-[10px] font-bold text-slate-400 dark:text-slate-500 uppercase tracking-wider text-center sm:text-left transition-colors">
        &copy; <?php echo date('Y'); ?> <span id="footer-copyright-text"><?php echo htmlspecialchars($set['footer_text']); ?></span>
    </div>
    
    <!-- Right Side: Developer Credits -->
    <div class="text-[10px] font-bold text-slate-400 dark:text-slate-500 uppercase tracking-wider text-center sm:text-right transition-colors">
        Developed by <span class="tracking-widest font-black transition-colors" style="color: <?php echo $primaryColor; ?>;">Kairi Tours & Safaris IT Department</span>
    </div>
</footer>

<!-- Global Infrastructure Scripts -->
<script>
    // Real-Time Active Clock
    function updateGlobalClock() {
        const now = new Date();
        const year = now.getFullYear();
        const month = String(now.getMonth() + 1).padStart(2, '0');
        const day = String(now.getDate()).padStart(2, '0');
        const hours = String(now.getHours()).padStart(2, '0');
        const minutes = String(now.getMinutes()).padStart(2, '0');
        const seconds = String(now.getSeconds()).padStart(2, '0');
        
        const clockEl = document.getElementById('global-real-time-clock');
        if (clockEl) clockEl.innerText = `${year}-${month}-${day}  ${hours}:${minutes}:${seconds}`;
    }
    updateGlobalClock();
    setInterval(updateGlobalClock, 1000);

    // Sidebar Logic
    function toggleGlobalSidebar() {
        const sidebar = document.getElementById('global-sidebar');
        if (!sidebar) return;
        sidebar.classList.toggle('w-64');
        sidebar.classList.toggle('w-20');
        
        sidebar.querySelectorAll('.sidebar-text').forEach(el => el.classList.toggle('hidden'));
        sidebar.querySelectorAll('.nav-link-container').forEach(el => {
            el.classList.toggle('px-3');
            el.classList.toggle('justify-center');
        });
    }

    // Theme Logic
    function applyTheme() {
        const icon = document.getElementById('theme-icon');
        if (localStorage.getItem('theme') === 'dark') {
            document.documentElement.classList.add('dark');
            if(icon) { icon.classList.replace('fa-moon', 'fa-sun'); icon.classList.add('text-amber-500'); }
        } else {
            document.documentElement.classList.remove('dark');
            if(icon) { icon.classList.replace('fa-sun', 'fa-moon'); icon.classList.remove('text-amber-500'); }
        }
    }
    function toggleTheme() {
        localStorage.setItem('theme', document.documentElement.classList.contains('dark') ? 'light' : 'dark');
        applyTheme();
    }
    applyTheme();
</script>
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<script src="js/session_timeout.js" defer></script>