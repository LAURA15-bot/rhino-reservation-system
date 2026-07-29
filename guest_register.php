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
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    
    <!-- Link to external JS file -->
    <script src="js/guest_register.js" defer></script>
    
    <style>
        .custom-shadow { box-shadow: 0 4px 20px -2px rgba(0, 0, 0, 0.05); }
    </style>
</head>
<body class="bg-[#f8fafc] text-[#334155] font-sans antialiased min-h-screen overflow-hidden">

    <!-- FULL SCREEN WRAPPER -->
    <div class="flex h-screen w-screen overflow-hidden">
        
        <!-- 1. LEFT SIDEBAR -->
        <?php include 'Includes/sidebar.php'; ?>

        <!-- RIGHT CONTENT AREA -->
        <main class="flex-1 flex flex-col h-full overflow-hidden bg-[#f8fafc]">
            
            <!-- 2. GLOBAL HEADER -->
            <?php include 'Includes/header.php'; ?>

            <!-- 3. SCROLLING MAIN CONTENT -->
            <div class="flex-1 p-4 lg:p-6 space-y-6 flex flex-col min-h-0 overflow-hidden">
                
                <!-- Page Header & Filters (Fixed at top of content area) -->
                <div class="shrink-0 space-y-6">

                    <div class="bg-white p-4 rounded-2xl custom-shadow border border-slate-100 flex flex-wrap items-end justify-between gap-4">
                        <!-- Left Side: Date Filter -->
                        <div class="flex items-end gap-4">
                            <div>
                                <label class="block text-[10px] font-bold text-slate-500 uppercase tracking-wider mb-2">Timeframe</label>
                                <select id="dateFilter" class="bg-slate-50 border border-slate-200 text-slate-700 text-xs font-bold rounded-lg focus:ring-emerald-500 focus:border-emerald-500 block w-48 p-2.5 cursor-pointer outline-none transition" onchange="toggleCustomDates(); filterTable();">
                                    <option value="all">All Available History</option>
                                    <option value="today">Today</option>
                                    <option value="7">Past 7 Days</option>
                                    <option value="30">Past 30 Days</option>
                                    <option value="custom">Specific Date Range</option>
                                </select>
                            </div>

                            <div id="customDateWrapper" class="hidden flex items-end gap-4">
                                <div>
                                    <label class="block text-[10px] font-bold text-slate-500 uppercase tracking-wider mb-2">Start Date</label>
                                    <input type="date" id="startDate" class="bg-white border border-slate-200 text-slate-700 text-xs font-bold rounded-lg p-2.5 outline-none focus:ring-emerald-500 focus:border-emerald-500 transition" onchange="filterTable();">
                                </div>
                                <div>
                                    <label class="block text-[10px] font-bold text-slate-500 uppercase tracking-wider mb-2">End Date</label>
                                    <input type="date" id="endDate" class="bg-white border border-slate-200 text-slate-700 text-xs font-bold rounded-lg p-2.5 outline-none focus:ring-emerald-500 focus:border-emerald-500 transition" onchange="filterTable();">
                                </div>
                            </div>
                        </div>

                        <!-- Right Side: Status Filter -->
                        <div>
                            <label class="block text-[10px] font-bold text-slate-500 uppercase tracking-wider mb-2">Registration Status</label>
                            <select id="statusFilter" class="bg-slate-50 border border-slate-200 text-slate-700 text-xs font-bold rounded-lg focus:ring-emerald-500 focus:border-emerald-500 block w-48 p-2.5 cursor-pointer outline-none transition" onchange="filterTable();">
                                <option value="All Statuses">All Statuses</option>
                                <option value="Fully Paid">Fully Paid (Confirmed)</option>
                                <option value="Partially Paid">Partially Paid</option>
                                <option value="Outstanding">Outstanding</option>
                                <option value="Cancelled">Cancelled</option>
                                <option value="Checked Out">Checked Out (Historical)</option>
                            </select>
                        </div>
                    </div>
                </div>

                <!-- Data Table Card (Takes up remaining vertical space with internal Y-Axis scroll) -->
                <div class="flex-1 bg-white rounded-2xl custom-shadow border border-slate-100 flex flex-col min-h-0 overflow-hidden">
                    <div class="flex-1 overflow-y-auto">
                        <table class="w-full text-left border-collapse" id="guestTable">
                            <thead class="sticky top-0 z-10 bg-slate-50 shadow-sm border-b border-slate-200">
                                <tr>
                                    <th class="px-6 py-4 text-[10px] font-bold text-slate-500 uppercase tracking-wider">Full Name</th>
                                    <th class="px-6 py-4 text-[10px] font-bold text-slate-500 uppercase tracking-wider">Status</th>
                                    <th class="px-6 py-4 text-[10px] font-bold text-slate-500 uppercase tracking-wider">Room Type</th>
                                    <th class="px-6 py-4 text-[10px] font-bold text-slate-500 uppercase tracking-wider">Check-in</th>
                                    <th class="px-6 py-4 text-[10px] font-bold text-slate-500 uppercase tracking-wider">Check-out</th>
                                    <th class="px-6 py-4 text-[10px] font-bold text-slate-500 uppercase tracking-wider">Nights</th>
                                    <th class="px-6 py-4 text-[10px] font-bold text-slate-500 uppercase tracking-wider">Rooms</th>
                                </tr>
                            </thead>
                            <tbody id="guestTableBody" class="divide-y divide-slate-100">
                                <!-- Populated via JavaScript API -->
                            </tbody>
                        </table>
                    </div>
                </div>

            </div>

            <!-- 4. GLOBAL FOOTER -->
            <?php include 'Includes/footer.php'; ?>

        </main>
    </div>

</body>
</html>