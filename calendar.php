<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
if (!isset($_SESSION['logged_in'])) {
    header("Location: login.php");
    exit;
}
$current_page = basename($_SERVER['PHP_SELF']);

// 1. LOAD SYSTEM SETTINGS
if (!isset($GLOBALS['system_settings'])) {
    require_once 'Includes/load_settings.php';
}
$set = $GLOBALS['system_settings'];

// 2. EXTRACT ACTIVE THEME
$theme = $set['theme_color'] ?? 'emerald';
$primaryColor = '#046a38'; 
if ($theme === 'safari') $primaryColor = '#8B3C28';
elseif ($theme === 'kairi') $primaryColor = '#802b1f';
elseif ($theme === 'blue') $primaryColor = '#2563eb';
elseif ($theme === 'custom') $primaryColor = $set['custom_primary'] ?? '#046a38';
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Calendar Matrix - Rhino Camp</title>
	
	<!-- Dynamic Favicon Override -->
	<link rel="icon" type="image/png" href="<?php echo !empty($set['logo_path']) ? htmlspecialchars($set['logo_path']) : 'data:image/x-icon;base64,'; ?>">

    <script src="https://cdn.tailwindcss.com"></script>
    <script>
        tailwind.config = { darkMode: 'class', }
    </script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    
    <!-- Bumping version to clear browser cache and load the new Theme logic -->
    <script src="js/calendar.js?v=6" defer></script>

    <style>
        :root {
            --theme-color: <?php echo $primaryColor; ?>;
        }
        .custom-shadow { box-shadow: 0 4px 20px -2px rgba(0, 0, 0, 0.05); }
        .theme-text { color: var(--theme-color); }
        .calendar-grid { display: grid; grid-template-columns: repeat(7, minmax(0, 1fr)); }
        input[type="date"]::-webkit-calendar-picker-indicator { cursor: pointer; }
    </style>
</head>
<body data-primary-color="<?php echo $primaryColor; ?>" class="bg-[#f8fafc] dark:bg-slate-900 text-[#334155] dark:text-slate-200 font-sans antialiased min-h-screen overflow-hidden transition-colors duration-300">

    <!-- FULL SCREEN WRAPPER -->
    <div class="flex h-screen w-screen overflow-hidden">
        
        <!-- 1. LEFT SIDEBAR -->
        <?php include 'Includes/sidebar.php'; ?>

        <!-- RIGHT CONTENT AREA -->
        <main class="flex-1 flex flex-col h-full overflow-hidden bg-[#f8fafc] dark:bg-slate-900 transition-colors duration-300">
            
            <!-- 2. GLOBAL HEADER -->
            <?php include 'Includes/header.php'; ?>

            <!-- 3. SCROLLING MAIN CONTENT -->
            <div class="flex-1 overflow-y-auto p-4 lg:p-8 flex flex-col">
                
                <div class="space-y-6 flex-1 max-w-[1600px] mx-auto w-full">
                    <div class="bg-white dark:bg-slate-800 p-4 rounded-2xl custom-shadow border border-slate-100 dark:border-slate-700 flex flex-col sm:flex-row items-center justify-between gap-4 transition-colors duration-300">
                        <div>
                            <h1 id="calendar-month-year-label" class="text-xl font-black text-slate-900 dark:text-white tracking-tight">July 2026</h1>
                            <p class="text-xs text-slate-400 dark:text-slate-500 mt-0.5">Real-time room occupancy and availability color matrix</p>
                        </div>
                        
                        <div class="flex items-center gap-2">
                            <div class="flex items-center border border-slate-200 dark:border-slate-600 rounded-xl px-3 py-1.5 bg-white dark:bg-slate-900 shadow-sm transition-colors duration-300">
                                <i class="fa-solid fa-calendar-days text-slate-400 dark:text-slate-500 mr-2"></i>
                                <input type="date" id="date-picker-input" onchange="jumpToSelectedDate(this.value)" class="text-sm font-bold text-slate-700 dark:text-slate-300 bg-transparent outline-none w-32 cursor-pointer">
                            </div>

                            <button onclick="changeMonth(-1)" class="w-9 h-9 border border-slate-200 dark:border-slate-600 bg-white dark:bg-slate-800 hover:bg-slate-50 dark:hover:bg-slate-700 rounded-xl flex items-center justify-center text-slate-600 dark:text-slate-300 shadow-sm transition-colors duration-300">
                                <i class="fa-solid fa-chevron-left text-xs"></i>
                            </button>
                            <button onclick="changeMonth(1)" class="w-9 h-9 border border-slate-200 dark:border-slate-600 bg-white dark:bg-slate-800 hover:bg-slate-50 dark:hover:bg-slate-700 rounded-xl flex items-center justify-center text-slate-600 dark:text-slate-300 shadow-sm transition-colors duration-300">
                                <i class="fa-solid fa-chevron-right text-xs"></i>
                            </button>
                        </div>
                    </div>

                    <div class="bg-white dark:bg-slate-800 p-1.5 rounded-xl custom-shadow border border-slate-100 dark:border-slate-700 flex flex-wrap gap-1 transition-colors duration-300">
                        <!-- Default Active Tab utilizes the inline style to fetch the PHP primary theme color immediately -->
                        <button onclick="switchCalendarViewTab('all')" id="tab-all" class="flex-1 min-w-[100px] text-center py-2 px-3 rounded-lg text-xs font-bold transition-all text-white shadow-sm" style="background-color: <?php echo $primaryColor; ?>;">
                            <i class="fa-solid fa-grid-2 mr-1"></i> Overview Summary
                        </button>
                        <button onclick="switchCalendarViewTab('single')" id="tab-single" class="flex-1 min-w-[100px] text-center py-2 px-3 rounded-lg text-xs font-bold transition-all text-slate-600 dark:text-slate-300 hover:bg-slate-50 dark:hover:bg-slate-700">Single Rooms</button>
                        <button onclick="switchCalendarViewTab('double')" id="tab-double" class="flex-1 min-w-[100px] text-center py-2 px-3 rounded-lg text-xs font-bold transition-all text-slate-600 dark:text-slate-300 hover:bg-slate-50 dark:hover:bg-slate-700">Double Rooms</button>
                        <button onclick="switchCalendarViewTab('triple')" id="tab-triple" class="flex-1 min-w-[100px] text-center py-2 px-3 rounded-lg text-xs font-bold transition-all text-slate-600 dark:text-slate-300 hover:bg-slate-50 dark:hover:bg-slate-700">Triple Rooms</button>
                        <button onclick="switchCalendarViewTab('family')" id="tab-family" class="flex-1 min-w-[100px] text-center py-2 px-3 rounded-lg text-xs font-bold transition-all text-slate-600 dark:text-slate-300 hover:bg-slate-50 dark:hover:bg-slate-700">Family Rooms</button>
                    </div>

                    <div class="bg-white dark:bg-slate-800 rounded-2xl custom-shadow border border-slate-100 dark:border-slate-700 overflow-hidden transition-colors duration-300">
                        <div class="calendar-grid text-center font-bold text-[10px] tracking-wider text-slate-400 dark:text-slate-500 uppercase bg-slate-50/70 dark:bg-slate-800/80 border-b border-slate-100 dark:border-slate-700 py-3 transition-colors duration-300">
                            <div>Sun</div><div>Mon</div><div>Tue</div><div>Wed</div><div>Thu</div><div>Fri</div><div>Sat</div>
                        </div>
                        <div id="calendar-days-container" class="calendar-grid divide-x divide-y divide-slate-100 dark:divide-slate-700/50 bg-slate-100 dark:bg-slate-700 gap-px transition-colors duration-300"></div>
                    </div>
                </div>

            </div>

            <!-- Permanent Semantic Legend -->
            <div class="shrink-0 bg-white dark:bg-slate-800 border-t border-slate-100 dark:border-slate-700 p-3 flex flex-wrap items-center justify-center gap-6 text-[10px] font-bold text-slate-600 dark:text-slate-400 uppercase tracking-wider transition-colors duration-300">
                <div class="flex items-center gap-2"><span class="w-4 h-4 bg-white dark:bg-slate-800 border border-slate-300 dark:border-slate-600 rounded shadow-inner inline-block"></span> 0% (Available / Empty)</div>
                <div class="flex items-center gap-2"><span class="w-4 h-4 bg-emerald-200 dark:bg-emerald-900/80 border border-emerald-400 dark:border-emerald-600 rounded shadow-inner inline-block"></span> 1% - 75% (Available)</div>
                <div class="flex items-center gap-2"><span class="w-4 h-4 bg-amber-200 dark:bg-amber-900/80 border border-amber-400 dark:border-amber-600 rounded shadow-inner inline-block"></span> Above 75% (Almost Full)</div>
                <div class="flex items-center gap-2"><span class="w-4 h-4 bg-rose-200 dark:bg-rose-900/80 border border-rose-400 dark:border-rose-600 rounded shadow-inner inline-block"></span> 100% (Fully Booked)</div>
            </div>

            <!-- 4. GLOBAL FOOTER -->
            <?php include 'Includes/footer.php'; ?>

        </main>
    </div>

    <!-- DAILY MANIFEST MODAL (POPUP) -->
    <div id="manifest-modal" class="fixed inset-0 bg-slate-900/60 backdrop-blur-sm z-50 hidden items-center justify-center p-4">
        <div class="bg-white dark:bg-slate-900 w-full max-w-5xl rounded-2xl shadow-xl border border-slate-100 dark:border-slate-700 p-6 space-y-5 relative animate-fadeIn flex flex-col max-h-[90vh]">
            <button onclick="closeManifestModal()" class="absolute top-4 right-4 text-slate-400 hover:text-slate-600 dark:hover:text-slate-200"><i class="fa-solid fa-xmark text-lg"></i></button>
            
            <div class="shrink-0 border-b border-slate-100 dark:border-slate-800 pb-4">
                
                <!-- HEADER & NEW INJECTED SUMMARY -->
                <div class="flex flex-col md:flex-row md:items-start justify-between gap-4">
                    <div>
                        <!-- Uses theme-text class -->
                        <h2 class="text-xl font-black text-slate-900 dark:text-white tracking-tight">Camp Manifest: <span id="manifest-date-title" class="theme-text"></span></h2>
                        <p class="text-xs text-slate-500 dark:text-slate-400 mt-1">Comprehensive audit trail of rooms and active personnel metrics for this day</p>
                    </div>
                    
                    <div id="manifest-room-summary" class="hidden md:block"></div>
                </div>
                
                <!-- Filters -->
                <div class="flex items-center gap-4 mt-4">
                    <div class="flex items-center border border-slate-200 dark:border-slate-700 rounded-lg overflow-hidden bg-slate-50 dark:bg-slate-800">
                        <span class="px-3 text-[10px] font-bold text-slate-500 dark:text-slate-400 uppercase tracking-wider border-r border-slate-200 dark:border-slate-700">Room Filter</span>
                        <select id="manifest-room-filter" onchange="renderManifestTable()" class="bg-transparent text-xs font-bold text-slate-700 dark:text-slate-300 p-2 outline-none cursor-pointer">
                            <option value="all">All Rooms</option>
                            <option value="Single Room">Single Room</option>
                            <option value="Double Room">Double Room</option>
                            <option value="Triple Room">Triple Room</option>
                            <option value="Family Room">Family Room</option>
                        </select>
                    </div>
                    <div class="flex items-center border border-slate-200 dark:border-slate-700 rounded-lg overflow-hidden bg-slate-50 dark:bg-slate-800">
                        <span class="px-3 text-[10px] font-bold text-slate-500 dark:text-slate-400 uppercase tracking-wider border-r border-slate-200 dark:border-slate-700">Status Filter</span>
                        <select id="manifest-status-filter" onchange="renderManifestTable()" class="bg-transparent text-xs font-bold text-slate-700 dark:text-slate-300 p-2 outline-none cursor-pointer">
                            <option value="all">All Statuses</option>
                            <option value="confirmed">Confirmed</option>
                            <option value="reserved">Reserved</option>
                            <option value="checkout">Checking Out Today</option>
                        </select>
                    </div>
                </div>
            </div>

            <div class="flex-1 overflow-y-auto min-h-0 bg-white dark:bg-slate-900 rounded-xl border border-slate-200 dark:border-slate-700">
                <table class="w-full text-left border-collapse">
                    <thead class="sticky top-0 bg-slate-50 dark:bg-slate-800 shadow-sm z-10 border-b border-slate-200 dark:border-slate-700 text-[10px] font-bold tracking-wider text-slate-500 dark:text-slate-400 uppercase">
                        <tr>
                            <th class="p-4">Ref No.</th>
                            <th class="p-4">Client Detail</th>
                            <th class="p-4">Channel / Officer</th>
                            <th class="p-4 text-center">Room Type</th>
                            <th class="p-4 text-center">Qty</th>
                            <th class="p-4 text-center">Duration</th>
                            <th class="p-4 text-left">Financial Status</th>
                            <th class="p-4 text-center">Status</th>
                        </tr>
                    </thead>
                    <tbody id="manifestTableBody" class="divide-y divide-slate-100 dark:divide-slate-800 text-xs font-semibold">
                        <!-- Populated via JS -->
                    </tbody>
                </table>
            </div>
        </div>
    </div>

</body>
</html>