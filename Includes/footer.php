<!-- Includes/footer.php -->
<footer class="bg-white dark:bg-slate-900 border-t border-slate-100 dark:border-slate-800 px-6 py-4 flex flex-col sm:flex-row justify-between items-center shrink-0 z-10 gap-2 transition-colors duration-300">
    
    <!-- Left Side: Auto-Updating Copyright -->
    <div class="text-[10px] font-bold text-slate-400 dark:text-slate-500 uppercase tracking-wider text-center sm:text-left transition-colors">
        &copy; <span id="footer-year"></span> Rhino Tourist Camp. All rights reserved.
    </div>
    
    <!-- Right Side: Developer Credits -->
    <div class="text-[10px] font-bold text-slate-400 dark:text-slate-500 uppercase tracking-wider text-center sm:text-right transition-colors">
        Developed by <span class="text-blue-600 dark:text-blue-400 tracking-widest font-black transition-colors">Kairi Tours & Safaris IT Department</span>
    </div>
</footer>

<!-- Global Infrastructure Scripts -->
<script>
    // 1. Auto-updating Copyright Year
    document.getElementById('footer-year').innerText = new Date().getFullYear();

    // 2. Real-Time Active Clock
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

    // 3. Sidebar Collapse/Expand Logic
    function toggleGlobalSidebar() {
        const sidebar = document.getElementById('global-sidebar');
        if (!sidebar) return;
        
        // Toggle the width of the sidebar (64 = expanded, 20 = minimized icons only)
        sidebar.classList.toggle('w-64');
        sidebar.classList.toggle('w-20');
        
        // Hide/Show text labels & align icons to center
        const textElements = sidebar.querySelectorAll('.sidebar-text');
        textElements.forEach(el => el.classList.toggle('hidden'));

        const navLinks = sidebar.querySelectorAll('.nav-link-container');
        navLinks.forEach(el => {
            el.classList.toggle('px-3');
            el.classList.toggle('justify-center');
        });
    }

    // 4. Dark Mode / Light Mode Logic
    function applyTheme() {
        const icon = document.getElementById('theme-icon');
        // Check local storage for preference
        if (localStorage.getItem('theme') === 'dark') {
            document.documentElement.classList.add('dark');
            if(icon) {
                icon.classList.remove('fa-moon');
                icon.classList.add('fa-sun');
                icon.classList.add('text-amber-500');
                icon.classList.remove('text-amber-400'); // Ensure hover state isn't stuck
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
    
    // Apply theme immediately on load
    applyTheme();
</script>