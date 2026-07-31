<?php
// Load dynamic settings if not already loaded
if (!isset($GLOBALS['system_settings'])) {
    require_once __DIR__ . '/load_settings.php';
}
$set = $GLOBALS['system_settings'];
?>
<!-- Includes/footer.php -->
<footer class="bg-white dark:bg-slate-900 border-t border-slate-100 dark:border-slate-800 px-6 py-4 flex flex-col sm:flex-row justify-between items-center shrink-0 z-10 gap-2 transition-colors duration-300">
    
    <!-- Left Side: Dynamic Copyright -->
    <div class="text-[10px] font-bold text-slate-400 dark:text-slate-500 uppercase tracking-wider text-center sm:text-left transition-colors">
        <span id="footer-copyright-text"><?php echo htmlspecialchars($set['footer_text']); ?></span>
    </div>
    
    <!-- Right Side: Developer Credits -->
    <div class="text-[10px] font-bold text-slate-400 dark:text-slate-500 uppercase tracking-wider text-center sm:text-right transition-colors">
        Developed by <span class="text-[#046a38] dark:text-emerald-400 tracking-widest font-black transition-colors">Kairi Tours & Safaris IT Department</span>
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
        
        const dateStr = `${year}-${month}-${day}`;
        const timeStr = `${hours}:${minutes}:${seconds}`;
        
        const clockEl = document.getElementById('global-real-time-clock');
        if (clockEl) {
            clockEl.innerText = `${dateStr}  ${timeStr}`;
        }
    }
    
    updateGlobalClock();
    setInterval(updateGlobalClock, 1000);

    // Sidebar Collapse/Expand Logic
    function toggleGlobalSidebar() {
        const sidebar = document.getElementById('global-sidebar');
        if (!sidebar) return;
        
        sidebar.classList.toggle('w-64');
        sidebar.classList.toggle('w-20');
        
        const textElements = sidebar.querySelectorAll('.sidebar-text');
        textElements.forEach(el => el.classList.toggle('hidden'));

        const navLinks = sidebar.querySelectorAll('.nav-link-container');
        navLinks.forEach(el => {
            el.classList.toggle('px-3');
            el.classList.toggle('justify-center');
        });
    }

    // Dark Mode / Light Mode Logic
    function applyTheme() {
        const icon = document.getElementById('theme-icon');
        if (localStorage.getItem('theme') === 'dark') {
            document.documentElement.classList.add('dark');
            if(icon) {
                icon.classList.remove('fa-moon');
                icon.classList.add('fa-sun');
                icon.classList.add('text-amber-500');
                icon.classList.remove('text-amber-400');
            }
        } else {
            document.documentElement.classList.remove('dark');
            if(icon) {
                icon.classList.remove('fa-sun');
                icon.classList.add('fa-moon');
                icon.classList.remove('text-amber-500');
            }
        }
    }

    function toggleTheme() {
        if (document.documentElement.classList.contains('dark')) {
            localStorage.setItem('theme', 'light');
        } else {
            localStorage.setItem('theme', 'dark');
        }
        applyTheme();
    }
    
    applyTheme();
</script>

<!-- Client-Side Session Inactivity Monitor -->
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<script src="js/session_timeout.js" defer></script>