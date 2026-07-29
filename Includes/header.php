<!-- Includes/header.php -->
<header class="bg-white border-b border-slate-100 px-6 py-4 flex justify-between items-center shrink-0 shadow-sm z-10 transition-all duration-300">
    
    <!-- Branding Section (Left) -->
    <div class="flex items-center gap-3">
        <div class="w-10 h-10 rounded-xl bg-emerald-50 text-[#046a38] flex items-center justify-center shadow-inner shrink-0">
            <i class="fa-solid fa-hippo text-xl"></i>
        </div>
        <div>
            <h1 class="text-base font-black text-slate-900 tracking-tight uppercase leading-none">RHINO TOURIST RESERVATION SYSTEM</h1>
            <span class="text-[10px] text-slate-400 font-medium block mt-1 tracking-wider uppercase">Rhino Tourist Camp Front-Desk Operations Ledger Console</span>
        </div>
    </div>
    
    <!-- Context & Notifications (Right) -->
    <div class="flex items-center gap-4 hidden md:flex">
        
        <!-- Live Real-Time Clock Badge -->
        <div class="flex items-center gap-2 border border-slate-200 bg-slate-50/50 px-3 py-2 rounded-lg shadow-sm text-slate-700">
            <i class="fa-regular fa-calendar text-[#046a38]"></i>
            <span class="text-xs font-bold text-slate-500 uppercase tracking-wide">Context:</span>
            <span id="global-real-time-clock" class="text-xs font-bold font-mono tracking-wider text-slate-800">
                <!-- Clock injected via JS -->
            </span>
        </div>
        
        <!-- Notification Bell -->
        <button class="relative w-9 h-9 flex items-center justify-center text-slate-500 hover:text-slate-700 transition border border-slate-200 rounded-lg hover:bg-slate-50 shadow-sm outline-none">
            <i class="fa-regular fa-bell"></i>
            <span class="absolute top-0 right-0 -mt-1 -mr-1 flex h-4 w-4 items-center justify-center rounded-full bg-rose-500 text-[9px] font-bold text-white shadow ring-2 ring-white">1</span>
        </button>

    </div>
</header>