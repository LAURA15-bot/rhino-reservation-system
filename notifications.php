<?php
session_start();
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
    <title>Follow-up Alerts - Rhino Camp</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <script>
        tailwind.config = { darkMode: 'class', }
    </script>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    
    <!-- Bumped Version to clear cache for new Action Buttons -->
    <script src="js/notifications.js?v=3" defer></script>
    
    <style>
        .custom-shadow { box-shadow: 0 4px 20px -2px rgba(0, 0, 0, 0.05); }
    </style>
</head>
<body class="bg-[#f8fafc] dark:bg-slate-900 text-[#334155] dark:text-slate-200 font-sans antialiased min-h-screen overflow-hidden transition-colors duration-300">
    
    <div class="flex h-screen w-screen overflow-hidden">
        
        <?php include 'Includes/sidebar.php'; ?>

        <main class="flex-1 overflow-y-auto h-full bg-[#f8fafc] dark:bg-slate-900 transition-colors duration-300 flex flex-col">
            
            <?php include 'Includes/header.php'; ?>

            <div class="p-4 lg:p-6 space-y-6 max-w-[1600px] mx-auto w-full flex-1">
                
                <div class="shrink-0 space-y-6">
                    <div class="bg-white dark:bg-slate-800 p-5 rounded-2xl custom-shadow border border-slate-100 dark:border-slate-700 flex flex-col sm:flex-row justify-between items-center gap-4 transition-colors duration-300">
                        <div class="flex items-center gap-3">
                            <div class="w-10 h-10 rounded-xl bg-amber-50 dark:bg-amber-900/30 text-amber-500 flex items-center justify-center shadow-inner shrink-0">
                                <i class="fa-solid fa-bell text-lg"></i>
                            </div>
                            <div>
                                <h1 class="text-sm font-mono font-bold tracking-widest text-slate-900 dark:text-white uppercase">Reservation Follow-Ups & Alerts</h1>
                                <p class="text-[10px] font-bold text-slate-400 dark:text-slate-500 uppercase tracking-widest mt-0.5">Track pending holds and contact clients before expiry</p>
                            </div>
                        </div>
                    </div>

                    <div class="bg-white dark:bg-slate-800 p-4 rounded-2xl custom-shadow border border-slate-100 dark:border-slate-700 flex flex-wrap items-center justify-between gap-4 transition-colors duration-300">
                        
                        <div class="flex items-center gap-2 w-full md:w-auto flex-1 max-w-md">
                            <div class="relative flex-1 flex items-center border border-slate-200 dark:border-slate-600 rounded-xl px-3 bg-slate-50 dark:bg-slate-900 transition-colors duration-300">
                                <i class="fa-solid fa-magnifying-glass text-slate-400 dark:text-slate-500 mr-2 text-xs"></i>
                                <input type="text" id="searchInput" onkeyup="filterNotificationsTable()" placeholder="Search by Client Name or Agency..." class="bg-transparent py-2.5 w-full text-xs text-slate-700 dark:text-slate-300 outline-none">
                            </div>
                        </div>

                        <div class="flex items-center gap-3">
                            <div class="flex items-center gap-2">
                                <label class="text-[10px] font-bold text-slate-500 dark:text-slate-400 uppercase tracking-wider hidden sm:block">Urgency:</label>
                                <select id="urgencyFilter" class="bg-slate-50 dark:bg-slate-900 border border-slate-200 dark:border-slate-600 text-slate-700 dark:text-slate-300 text-xs font-bold rounded-lg block p-2 cursor-pointer outline-none transition-colors duration-300" onchange="filterNotificationsTable();">
                                    <option value="All">All Pending Alerts</option>
                                    <option value="Expired">Expired Holds</option>
                                    <option value="Today">Due Today</option>
                                    <option value="Tomorrow">Due Tomorrow</option>
                                </select>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="bg-white dark:bg-slate-800 rounded-2xl custom-shadow border border-slate-100 dark:border-slate-700 overflow-hidden transition-colors duration-300 h-auto">
                    <div class="p-4 border-b border-slate-100 dark:border-slate-700 bg-white dark:bg-slate-800 flex justify-between items-center transition-colors duration-300">
                        <h3 class="text-xs font-bold tracking-wide uppercase text-slate-900 dark:text-white flex items-center gap-2">
                            <i class="fa-solid fa-clock-rotate-left text-amber-500"></i> Action Required Ledgers
                        </h3>
                        <span id="records-counter-badge" class="text-[11px] bg-slate-100 dark:bg-slate-900 text-slate-600 dark:text-slate-400 px-2.5 py-1 font-bold rounded-lg border border-slate-200 dark:border-slate-700 transition-colors duration-300">0 Alerts Found</span>
                    </div>

                    <div class="overflow-x-auto w-full">
                        <table class="w-full text-left border-collapse">
                            <thead class="bg-slate-50 dark:bg-slate-800 border-b border-slate-200 dark:border-slate-700 text-[10px] font-bold tracking-wider text-slate-500 dark:text-slate-400 uppercase transition-colors duration-300">
                                <tr>
                                    <th class="p-4">Ref No.</th>
                                    <th class="p-4">Client Detail</th>
                                    <th class="p-4">Booking Source</th>
                                    <th class="p-4 text-center">Consultant</th>
                                    <th class="p-4 text-center">Check-in Date</th>
                                    <th class="p-4 text-center">Urgency</th>
                                    <th class="p-4 text-right">Balance Due</th>
                                    <th class="p-4 text-center">Actions</th>
                                </tr>
                            </thead>
                            <tbody id="notificationsTableBody" class="divide-y divide-slate-100 dark:divide-slate-700/50 text-xs text-slate-700 dark:text-slate-300 font-semibold bg-white dark:bg-slate-900 transition-colors duration-300">
                                <!-- Populated via API -->
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>

            <?php include 'Includes/footer.php'; ?>

        </main>
    </div>
</body>
</html>