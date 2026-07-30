<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
if (!isset($_SESSION['logged_in'])) {
    header("Location: login.php");
    exit;
}
$current_page = basename($_SERVER['PHP_SELF']);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Guest Register - Rhino Camp</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <script>
        tailwind.config = { darkMode: 'class', }
    </script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    
    <!-- Version bumped to clear browser cache and load pagination logic -->
    <script src="js/guest_register.js?v=4" defer></script>
    
    <style>
        .custom-shadow { box-shadow: 0 4px 20px -2px rgba(0, 0, 0, 0.05); }
    </style>
</head>
<body class="bg-[#f8fafc] dark:bg-slate-900 text-[#334155] dark:text-slate-200 font-sans antialiased min-h-screen overflow-hidden transition-colors duration-300">

    <!-- FULL SCREEN WRAPPER -->
    <div class="flex h-screen w-screen overflow-hidden">
        
        <!-- 1. LEFT SIDEBAR -->
        <?php include 'Includes/sidebar.php'; ?>

        <!-- RIGHT CONTENT AREA -->
        <!-- Y-AXIS SCROLL MOVED HERE TO LET THE ENTIRE PAGE SCROLL -->
        <main class="flex-1 overflow-y-auto h-full bg-[#f8fafc] dark:bg-slate-900 transition-colors duration-300 flex flex-col">
            
            <!-- 2. GLOBAL HEADER -->
            <?php include 'Includes/header.php'; ?>

            <!-- 3. SCROLLING MAIN CONTENT CONTAINER -->
            <div class="p-4 lg:p-6 space-y-6 max-w-[1600px] mx-auto w-full flex-1">
                
                <!-- Premium Section Header -->
                <div class="bg-white dark:bg-slate-800 p-5 rounded-2xl custom-shadow border border-slate-100 dark:border-slate-700 flex items-center gap-3 transition-colors duration-300">
                    <div class="w-10 h-10 rounded-xl bg-indigo-50 dark:bg-indigo-900/30 text-indigo-500 dark:text-indigo-400 flex items-center justify-center shadow-inner shrink-0">
                        <i class="fa-solid fa-address-book text-lg"></i>
                    </div>
                    <div>
                        <h2 class="text-sm font-mono font-bold tracking-widest text-slate-900 dark:text-white uppercase">Guest Register Archive</h2>
                        <p class="text-[10px] font-bold text-slate-400 dark:text-slate-500 uppercase tracking-widest mt-0.5">Historical and permanent audit record of all visitors</p>
                    </div>
                </div>

                <!-- Filters Section -->
                <div class="bg-white dark:bg-slate-800 p-4 rounded-2xl custom-shadow border border-slate-100 dark:border-slate-700 flex flex-wrap items-end justify-between gap-4 transition-colors duration-300">
                    
                    <div class="flex flex-wrap items-end gap-4">
                        <!-- Per Page Filter -->
                        <div>
                            <label class="block text-[10px] font-bold text-slate-500 dark:text-slate-400 uppercase tracking-wider mb-2">Per Page</label>
                            <select id="rowsPerPageFilter" onchange="changeRowsPerPage()" class="bg-slate-50 dark:bg-slate-900 border border-slate-200 dark:border-slate-600 text-slate-700 dark:text-slate-300 text-xs font-bold rounded-lg focus:ring-emerald-500 focus:border-emerald-500 block w-24 p-2.5 cursor-pointer outline-none transition-colors duration-300">
                                <option value="10">10</option>
                                <option value="20">20</option>
                                <option value="50">50</option>
                                <option value="all">All</option>
                            </select>
                        </div>

                        <!-- Date Filter -->
                        <div>
                            <label class="block text-[10px] font-bold text-slate-500 dark:text-slate-400 uppercase tracking-wider mb-2">Timeframe</label>
                            <select id="dateFilter" class="bg-slate-50 dark:bg-slate-900 border border-slate-200 dark:border-slate-600 text-slate-700 dark:text-slate-300 text-xs font-bold rounded-lg focus:ring-emerald-500 focus:border-emerald-500 block w-48 p-2.5 cursor-pointer outline-none transition-colors duration-300" onchange="toggleCustomDates(); filterTable();">
                                <option value="all">All Available History</option>
                                <option value="today">Today</option>
                                <option value="7">Past 7 Days</option>
                                <option value="30">Past 30 Days</option>
                                <option value="custom">Specific Date Range</option>
                            </select>
                        </div>

                        <div id="customDateWrapper" class="hidden flex items-end gap-4">
                            <div>
                                <label class="block text-[10px] font-bold text-slate-500 dark:text-slate-400 uppercase tracking-wider mb-2">Start Date</label>
                                <input type="date" id="startDate" class="bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-600 text-slate-700 dark:text-slate-300 text-xs font-bold rounded-lg p-2.5 outline-none focus:ring-emerald-500 focus:border-emerald-500 transition-colors duration-300" onchange="filterTable();">
                            </div>
                            <div>
                                <label class="block text-[10px] font-bold text-slate-500 dark:text-slate-400 uppercase tracking-wider mb-2">End Date</label>
                                <input type="date" id="endDate" class="bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-600 text-slate-700 dark:text-slate-300 text-xs font-bold rounded-lg p-2.5 outline-none focus:ring-emerald-500 focus:border-emerald-500 transition-colors duration-300" onchange="filterTable();">
                            </div>
                        </div>
                    </div>

                    <!-- Status Filter -->
                    <div>
                        <label class="block text-[10px] font-bold text-slate-500 dark:text-slate-400 uppercase tracking-wider mb-2">Registration Status</label>
                        <select id="statusFilter" class="bg-slate-50 dark:bg-slate-900 border border-slate-200 dark:border-slate-600 text-slate-700 dark:text-slate-300 text-xs font-bold rounded-lg focus:ring-emerald-500 focus:border-emerald-500 block w-48 p-2.5 cursor-pointer outline-none transition-colors duration-300" onchange="filterTable();">
                            <option value="All Statuses">All Statuses</option>
                            <option value="Fully Paid">Fully Paid (Confirmed)</option>
                            <option value="Partially Paid">Partially Paid</option>
                            <option value="Outstanding">Outstanding</option>
                            <option value="Cancelled">Cancelled</option>
                            <option value="Checked Out">Checked Out (Historical)</option>
                        </select>
                    </div>
                </div>

                <!-- Data Table Card (H-Auto so it hugs the rows perfectly) -->
                <div class="bg-white dark:bg-slate-800 rounded-2xl custom-shadow border border-slate-100 dark:border-slate-700 overflow-hidden transition-colors duration-300 h-auto">
                    <div class="overflow-x-auto w-full">
                        <table class="w-full text-left border-collapse" id="guestTable">
                            <thead class="bg-slate-50 dark:bg-slate-800 border-b border-slate-200 dark:border-slate-700 transition-colors duration-300">
                                <tr>
                                    <th class="px-6 py-4 text-[10px] font-bold text-slate-500 dark:text-slate-400 uppercase tracking-wider">Full Name</th>
                                    <th class="px-6 py-4 text-[10px] font-bold text-slate-500 dark:text-slate-400 uppercase tracking-wider">Status</th>
                                    <th class="px-6 py-4 text-[10px] font-bold text-slate-500 dark:text-slate-400 uppercase tracking-wider">Room Type</th>
                                    <th class="px-6 py-4 text-[10px] font-bold text-slate-500 dark:text-slate-400 uppercase tracking-wider">Check-in</th>
                                    <th class="px-6 py-4 text-[10px] font-bold text-slate-500 dark:text-slate-400 uppercase tracking-wider">Check-out</th>
                                    <th class="px-6 py-4 text-[10px] font-bold text-slate-500 dark:text-slate-400 uppercase tracking-wider">Nights</th>
                                    <th class="px-6 py-4 text-[10px] font-bold text-slate-500 dark:text-slate-400 uppercase tracking-wider">Rooms</th>
                                </tr>
                            </thead>
                            <tbody id="guestTableBody" class="divide-y divide-slate-100 dark:divide-slate-700/50 bg-white dark:bg-slate-900 transition-colors duration-300">
                                <!-- Populated via JavaScript API -->
                            </tbody>
                        </table>
                    </div>
                    
                    <!-- Pagination Navigation -->
                    <div class="p-4 border-t border-slate-100 dark:border-slate-700 bg-slate-50 dark:bg-slate-800/50 flex flex-col sm:flex-row items-center justify-between gap-4 transition-colors duration-300">
                        <span id="page-info" class="text-xs font-bold text-slate-500 dark:text-slate-400">Showing 0 to 0 of 0 entries</span>
                        <div class="flex items-center gap-2">
                            <button id="prev-page-btn" onclick="changePage(-1)" class="px-3 py-1.5 bg-white dark:bg-slate-700 border border-slate-200 dark:border-slate-600 text-slate-600 dark:text-slate-300 rounded-lg text-xs font-bold hover:bg-slate-50 dark:hover:bg-slate-600 transition-colors disabled:opacity-50 disabled:cursor-not-allowed">
                                <i class="fa-solid fa-chevron-left mr-1"></i> Prev
                            </button>
                            <button id="next-page-btn" onclick="changePage(1)" class="px-3 py-1.5 bg-white dark:bg-slate-700 border border-slate-200 dark:border-slate-600 text-slate-600 dark:text-slate-300 rounded-lg text-xs font-bold hover:bg-slate-50 dark:hover:bg-slate-600 transition-colors disabled:opacity-50 disabled:cursor-not-allowed">
                                Next <i class="fa-solid fa-chevron-right ml-1"></i>
                            </button>
                        </div>
                    </div>
                </div>

            </div>

            <!-- 4. GLOBAL FOOTER -->
            <?php include 'Includes/footer.php'; ?>

        </main>
    </div>

</body>
</html>