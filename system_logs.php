<?php
session_start();
if (!isset($_SESSION['logged_in']) || strtolower($_SESSION['role']) !== 'admin') { 
    header("Location: dashboard.php"); 
    exit; 
}
$current_page = basename($_SERVER['PHP_SELF']);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Security Audit Stream - Rhino Camp</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <script>
        tailwind.config = { darkMode: 'class', }
    </script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    
    <!-- Link to external JS (Version bumped to load dark mode logic) -->
    <script src="js/system_logs.js?v=3" defer></script>
    
    <style>
        .custom-shadow { box-shadow: 0 4px 20px -2px rgba(0, 0, 0, 0.05); }
    </style>
</head>
<body class="bg-[#f8fafc] dark:bg-slate-900 text-slate-800 dark:text-slate-200 font-sans antialiased min-h-screen overflow-hidden transition-colors duration-300">
    
    <!-- FULL SCREEN WRAPPER -->
    <div class="flex h-screen w-screen overflow-hidden">
        
        <!-- 1. LEFT SIDEBAR -->
        <?php include 'Includes/sidebar.php'; ?>

        <!-- RIGHT CONTENT AREA -->
        <main class="flex-1 flex flex-col h-full overflow-hidden bg-[#f8fafc] dark:bg-slate-900 transition-colors duration-300">
            
            <!-- 2. GLOBAL HEADER -->
            <?php include 'Includes/header.php'; ?>

            <!-- 3. SCROLLING MAIN CONTENT (Y-AXIS ISOLATION SET HERE) -->
            <div class="flex-1 p-4 lg:p-6 flex flex-col min-h-0 overflow-hidden max-w-[1600px] mx-auto w-full">
                
                <!-- Fixed Top Section (Headers & Filters) -->
                <div class="shrink-0 space-y-4 mb-4">
                    
                    <div class="bg-white dark:bg-slate-800 p-5 rounded-2xl custom-shadow border border-slate-100 dark:border-slate-700 flex items-center gap-3 transition-colors duration-300">
                        <div class="w-10 h-10 rounded-xl bg-rose-50 dark:bg-rose-900/30 text-rose-500 dark:text-rose-400 flex items-center justify-center shadow-inner shrink-0">
                            <i class="fa-solid fa-shield-halved text-lg"></i>
                        </div>
                        <div>
                            <h2 class="text-sm font-mono font-bold tracking-widest text-[#0f172a] dark:text-white uppercase">Corporate Database Security Audit Stream</h2>
                            <p class="text-[10px] font-bold text-slate-400 dark:text-slate-500 uppercase tracking-widest mt-0.5">Immutable System Activity & Access Ledger</p>
                        </div>
                    </div>

                    <!-- ADVANCED FILTERING PANEL -->
                    <div class="bg-white dark:bg-slate-800 p-4 rounded-xl custom-shadow border border-slate-100 dark:border-slate-700 flex flex-wrap items-end gap-4 transition-colors duration-300">
                        
                        <!-- Search Bar -->
                        <div class="flex-1 min-w-[200px]">
                            <label class="block text-[10px] font-bold text-slate-500 dark:text-slate-400 uppercase tracking-wider mb-2">Search Event Data</label>
                            <div class="relative flex items-center border border-slate-200 dark:border-slate-600 rounded-lg px-3 bg-slate-50 dark:bg-slate-900 transition-colors duration-300">
                                <i class="fa-solid fa-magnifying-glass text-slate-400 dark:text-slate-500 mr-2 text-xs"></i>
                                <input type="text" id="searchInput" onkeyup="filterLogsTable()" placeholder="Search user, action, or IP..." class="bg-transparent py-2 w-full text-xs text-slate-700 dark:text-slate-300 outline-none">
                            </div>
                        </div>

                        <!-- Date Timeframe Filter -->
                        <div>
                            <label class="block text-[10px] font-bold text-slate-500 dark:text-slate-400 uppercase tracking-wider mb-2">Date Timeframe</label>
                            <select id="dateFilter" onchange="toggleCustomDateFilters(); filterLogsTable();" class="bg-slate-50 dark:bg-slate-900 border border-slate-200 dark:border-slate-600 text-slate-700 dark:text-slate-300 text-xs font-bold rounded-lg focus:ring-rose-500 focus:border-rose-500 block w-40 p-2.5 cursor-pointer outline-none transition-colors duration-300">
                                <option value="today">Today</option>
                                <option value="7">Past 7 Days</option>
                                <option value="30">Past 30 Days</option>
                                <option value="all" selected>All Available Data</option>
                                <option value="custom">Specific Date Range</option>
                            </select>
                        </div>

                        <!-- Custom Date Range (Hidden by default) -->
                        <div id="customDateWrapper" class="hidden flex items-end gap-3">
                            <div>
                                <label class="block text-[10px] font-bold text-slate-500 dark:text-slate-400 uppercase tracking-wider mb-2">Date From</label>
                                <input type="date" id="startDate" onchange="filterLogsTable()" class="bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-600 text-slate-700 dark:text-slate-300 text-xs font-bold rounded-lg p-2.5 outline-none focus:ring-rose-500 focus:border-rose-500 transition-colors duration-300">
                            </div>
                            <div>
                                <label class="block text-[10px] font-bold text-slate-500 dark:text-slate-400 uppercase tracking-wider mb-2">Date To</label>
                                <input type="date" id="endDate" onchange="filterLogsTable()" class="bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-600 text-slate-700 dark:text-slate-300 text-xs font-bold rounded-lg p-2.5 outline-none focus:ring-rose-500 focus:border-rose-500 transition-colors duration-300">
                            </div>
                        </div>

                        <!-- Specific Time Filter (Always visible for granular control) -->
                        <div class="flex items-end gap-3 border-l border-slate-100 dark:border-slate-700 pl-4 ml-2 transition-colors duration-300">
                            <div>
                                <label class="block text-[10px] font-bold text-slate-500 dark:text-slate-400 uppercase tracking-wider mb-2">Time From (Opt)</label>
                                <input type="time" id="startTime" onchange="filterLogsTable()" class="bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-600 text-slate-700 dark:text-slate-300 text-xs font-bold rounded-lg p-2.5 outline-none focus:ring-rose-500 focus:border-rose-500 transition-colors duration-300">
                            </div>
                            <div>
                                <label class="block text-[10px] font-bold text-slate-500 dark:text-slate-400 uppercase tracking-wider mb-2">Time To (Opt)</label>
                                <input type="time" id="endTime" onchange="filterLogsTable()" class="bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-600 text-slate-700 dark:text-slate-300 text-xs font-bold rounded-lg p-2.5 outline-none focus:ring-rose-500 focus:border-rose-500 transition-colors duration-300">
                            </div>
                        </div>

                        <!-- Reset Filters -->
                        <div>
                            <button onclick="resetLogFilters()" class="bg-slate-100 dark:bg-slate-700 hover:bg-slate-200 dark:hover:bg-slate-600 text-slate-600 dark:text-slate-300 font-bold py-2.5 px-4 rounded-lg text-xs transition-colors shadow-sm mb-[1px]">
                                <i class="fa-solid fa-rotate-right"></i> Reset
                            </button>
                        </div>
                    </div>
                </div>

                <!-- Ledger Table Container (Fills remaining height, internal Y-scroll) -->
                <div class="flex-1 bg-white dark:bg-slate-800 rounded-xl custom-shadow border border-slate-100 dark:border-slate-700 flex flex-col min-h-0 overflow-hidden transition-colors duration-300">
                    
                    <div class="flex-1 overflow-y-auto">
                        <table class="w-full text-left whitespace-nowrap">
                            <thead class="sticky top-0 z-10 bg-white dark:bg-slate-800 border-b border-slate-200 dark:border-slate-700 text-[10px] text-slate-500 dark:text-slate-400 font-bold uppercase tracking-wider shadow-sm transition-colors duration-300">
                                <tr>
                                    <th class="py-4 px-5">Event Timestamp</th>
                                    <th class="py-4 px-5">Account Token</th>
                                    <th class="py-4 px-5">Role</th>
                                    <th class="py-4 px-5">Action Code</th>
                                    <th class="py-4 px-5 w-full">Context Operational Matrix Payload Details</th>
                                    <th class="py-4 px-5 text-right">Host IP Origin</th>
                                </tr>
                            </thead>
                            <tbody id="logsTableBody" class="text-sm divide-y divide-slate-50 dark:divide-slate-700/50 bg-white dark:bg-slate-900 transition-colors duration-300">
                                <!-- Populated dynamically via JS -->
                            </tbody>
                        </table>
                    </div>
                    
                    <div class="p-3 border-t border-slate-100 dark:border-slate-700 bg-slate-50 dark:bg-slate-800/80 shrink-0 text-right transition-colors duration-300">
                        <span id="records-counter-badge" class="text-[10px] font-bold text-slate-500 dark:text-slate-400 uppercase tracking-wider">0 Events Rendered</span>
                    </div>
                </div>
            </div>

            <!-- 4. GLOBAL FOOTER -->
            <?php include 'Includes/footer.php'; ?>

        </main>
    </div>
</body>
</html>