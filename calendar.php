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
    <title>Calendar Matrix - Rhino Camp</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    
    <!-- Link to external Calendar JavaScript -->
    <script src="js/calendar.js" defer></script>

    <style>
        .custom-shadow { box-shadow: 0 4px 20px -2px rgba(0, 0, 0, 0.05); }
        .calendar-grid { display: grid; grid-template-columns: repeat(7, minmax(0, 1fr)); }
        input[type="date"]::-webkit-calendar-picker-indicator { cursor: pointer; }
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
            <div class="flex-1 overflow-y-auto p-4 lg:p-8 space-y-6">
                
                <div class="bg-white p-4 rounded-2xl custom-shadow border border-slate-100 flex flex-col sm:flex-row items-center justify-between gap-4">
                    <div>
                        <h1 id="calendar-month-year-label" class="text-xl font-black text-slate-900 tracking-tight">July 2026</h1>
                        <p class="text-xs text-slate-400 mt-0.5">Real-time room occupancy and availability color matrix</p>
                    </div>
                    
                    <div class="flex items-center gap-2">
                        <div class="flex items-center border border-slate-200 rounded-xl px-3 py-1.5 bg-white shadow-sm">
                            <i class="fa-solid fa-calendar-days text-slate-400 mr-2"></i>
                            <input type="date" id="date-picker-input" onchange="jumpToSelectedDate(this.value)" class="text-sm font-bold text-slate-700 outline-none w-32 cursor-pointer">
                        </div>

                        <button onclick="changeMonth(-1)" class="w-9 h-9 border border-slate-200 hover:bg-slate-50 rounded-xl flex items-center justify-center text-slate-600 shadow-sm transition">
                            <i class="fa-solid fa-chevron-left text-xs"></i>
                        </button>
                        <button onclick="changeMonth(1)" class="w-9 h-9 border border-slate-200 hover:bg-slate-50 rounded-xl flex items-center justify-center text-slate-600 shadow-sm transition">
                            <i class="fa-solid fa-chevron-right text-xs"></i>
                        </button>
                    </div>
                </div>

                <div class="bg-white p-1.5 rounded-xl custom-shadow border border-slate-100 flex flex-wrap gap-1">
                    <button onclick="switchCalendarViewTab('all')" id="tab-all" class="flex-1 min-w-[100px] text-center py-2 px-3 rounded-lg text-xs font-bold transition-all bg-[#046a38] text-white">
                        <i class="fa-solid fa-grid-2 mr-1"></i> Overview Summary
                    </button>
                    <button onclick="switchCalendarViewTab('single')" id="tab-single" class="flex-1 min-w-[100px] text-center py-2 px-3 rounded-lg text-xs font-bold transition-all text-slate-600 hover:bg-slate-50">Single Rooms</button>
                    <button onclick="switchCalendarViewTab('double')" id="tab-double" class="flex-1 min-w-[100px] text-center py-2 px-3 rounded-lg text-xs font-bold transition-all text-slate-600 hover:bg-slate-50">Double Rooms</button>
                    <button onclick="switchCalendarViewTab('triple')" id="tab-triple" class="flex-1 min-w-[100px] text-center py-2 px-3 rounded-lg text-xs font-bold transition-all text-slate-600 hover:bg-slate-50">Triple Rooms</button>
                    <button onclick="switchCalendarViewTab('family')" id="tab-family" class="flex-1 min-w-[100px] text-center py-2 px-3 rounded-lg text-xs font-bold transition-all text-slate-600 hover:bg-slate-50">Family Rooms</button>
                </div>

                <div class="bg-white rounded-2xl custom-shadow border border-slate-100 overflow-hidden">
                    <div class="calendar-grid text-center font-bold text-[10px] tracking-wider text-slate-400 uppercase bg-slate-50/70 border-b border-slate-100 py-3">
                        <div>Sun</div><div>Mon</div><div>Tue</div><div>Wed</div><div>Thu</div><div>Fri</div><div>Sat</div>
                    </div>
                    <div id="calendar-days-container" class="calendar-grid divide-x divide-y divide-slate-100 bg-slate-100 gap-px"></div>
                </div>
            </div>

            <!-- 4. GLOBAL FOOTER -->
            <?php include 'Includes/footer.php'; ?>

        </main>
    </div>
</body>
</html>