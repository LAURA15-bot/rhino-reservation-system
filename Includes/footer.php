<!-- Includes/footer.php -->
<footer class="bg-white border-t border-slate-100 px-6 py-3 flex justify-between items-center shrink-0 z-10">
    
    <!-- Left Side: Hamburger & Copyright -->
    <div class="flex items-center gap-4 text-[10px] font-bold text-slate-400 uppercase tracking-wider">
        <button onclick="toggleGlobalSidebar()" class="w-8 h-8 flex items-center justify-center text-slate-400 hover:text-[#046a38] hover:bg-emerald-50 rounded-lg transition text-sm outline-none" title="Toggle Sidebar">
            <i class="fa-solid fa-bars"></i>
        </button>
        <span class="hidden sm:inline-block">&copy; <?php echo date("Y"); ?> Rhino Tourist Camp. All rights reserved.</span>
    </div>
    
    <!-- Right Side: Developer Credits -->
    <div class="text-[10px] font-bold text-slate-400 uppercase tracking-wider">
        Developed by <span class="text-[#046a38] tracking-widest">Kairi Tours and Safaris</span>
    </div>
</footer>

<!-- Global Infrastructure Scripts -->
<script>
    // 1. Real-Time Active Clock
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
    
    // Start the clock immediately and tick every 1000ms
    updateGlobalClock();
    setInterval(updateGlobalClock, 1000);

    // 2. Sidebar Collapse/Expand Logic
    function toggleGlobalSidebar() {
        const sidebar = document.getElementById('global-sidebar');
        if (!sidebar) return;
        
        // Toggle the width of the sidebar (64 = expanded, 20 = minimized icons only)
        sidebar.classList.toggle('w-64');
        sidebar.classList.toggle('w-20');
        
        // Hide/Show the text labels to prevent wrapping errors when minimized
        const textElements = sidebar.querySelectorAll('.sidebar-text');
        textElements.forEach(el => {
            el.classList.toggle('hidden');
        });
    }
</script>