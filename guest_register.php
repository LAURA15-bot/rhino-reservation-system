<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
if (!isset($_SESSION['logged_in'])) {
    header("Location: login.php");
    exit;
}
$current_page = basename($_SERVER['PHP_SELF']);
$user_role = strtolower($_SESSION['role'] ?? 'consultant');

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
    <title>Guest Register - Rhino Camp</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <script>
        tailwind.config = { darkMode: 'class', }
    </script>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    
    <!-- Pass Security Role to JavaScript -->
    <script>
        const IS_ADMIN = <?php echo ($user_role === 'admin') ? 'true' : 'false'; ?>;
    </script>
    
    <!-- Version bumped to clear cache for Search & Pagination logic -->
    <script src="js/guest_register.js?v=11" defer></script>
    
    <style>
        :root {
            --theme-color: <?php echo $primaryColor; ?>;
            --theme-color-focus: <?php echo $primaryColor; ?>33;
        }
        .custom-shadow { box-shadow: 0 4px 20px -2px rgba(0, 0, 0, 0.05); }
        .theme-text { color: var(--theme-color); }
        .theme-btn { background-color: var(--theme-color); transition: filter 0.2s; }
        .theme-btn:hover { filter: brightness(85%); }
        .theme-focus:focus { outline: none; border-color: var(--theme-color); box-shadow: 0 0 0 3px var(--theme-color-focus); }
    </style>
</head>
<body class="bg-[#f8fafc] dark:bg-slate-900 text-[#334155] dark:text-slate-200 font-sans antialiased min-h-screen overflow-hidden transition-colors duration-300">

    <div class="flex h-screen w-screen overflow-hidden">
        
        <?php include 'Includes/sidebar.php'; ?>

        <main class="flex-1 overflow-y-auto h-full bg-[#f8fafc] dark:bg-slate-900 transition-colors duration-300 flex flex-col">
            
            <?php include 'Includes/header.php'; ?>

            <div class="p-4 lg:p-6 space-y-6 max-w-[1600px] mx-auto w-full flex-1">
                
                <!-- Premium Section Header -->
                <div class="bg-white dark:bg-slate-800 p-5 rounded-2xl custom-shadow border border-slate-100 dark:border-slate-700 flex items-center gap-3 transition-colors duration-300">
                    <div class="w-10 h-10 rounded-xl bg-slate-100 dark:bg-slate-900/50 theme-text flex items-center justify-center shadow-inner shrink-0 border border-slate-200 dark:border-slate-700">
                        <i class="fa-solid fa-address-book text-lg"></i>
                    </div>
                    <div>
                        <h2 class="text-sm font-mono font-bold tracking-widest text-slate-900 dark:text-white uppercase">Guest Register Archive</h2>
                        <p class="text-[10px] font-bold text-slate-400 dark:text-slate-500 uppercase tracking-widest mt-0.5">Historical and permanent audit record of all visitors</p>
                    </div>
                </div>

                <!-- Filters & Search Section -->
                <div class="bg-white dark:bg-slate-800 p-4 rounded-2xl custom-shadow border border-slate-100 dark:border-slate-700 flex flex-wrap items-end justify-between gap-4 transition-colors duration-300">
                    
                    <div class="flex flex-wrap items-end gap-4 flex-1 w-full">
                        
                        <!-- ENLARGED: Live Search Bar -->
                        <div class="w-full sm:flex-1 min-w-[280px] lg:min-w-[400px] max-w-2xl">
                            <label class="block text-[10px] font-bold text-slate-500 dark:text-slate-400 uppercase tracking-wider mb-2">Live Search</label>
                            <div class="relative flex items-center border border-slate-200 dark:border-slate-600 rounded-xl px-4 bg-slate-50 dark:bg-slate-900 transition-all duration-300 shadow-sm focus-within:ring-2 focus-within:border-transparent" style="--tw-ring-color: <?php echo $primaryColor; ?>;">
                                <i class="fa-solid fa-magnifying-glass text-slate-400 dark:text-slate-500 mr-3 text-sm"></i>
                                <input type="text" id="searchInput" onkeyup="currentPage=1; filterTable();" placeholder="Search guest, agency, or officer..." class="bg-transparent py-3 w-full text-sm font-semibold text-slate-800 dark:text-slate-200 outline-none placeholder-slate-400">
                            </div>
                        </div>

                        <!-- Per Page Filter -->
                        <div>
                            <label class="block text-[10px] font-bold text-slate-500 dark:text-slate-400 uppercase tracking-wider mb-2">Per Page</label>
                            <select id="rowsPerPageFilter" onchange="changeRowsPerPage()" class="bg-slate-50 dark:bg-slate-900 border border-slate-200 dark:border-slate-600 text-slate-700 dark:text-slate-300 text-xs font-bold rounded-lg theme-focus block w-24 p-3 cursor-pointer transition-colors duration-300">
                                <option value="10">10</option>
                                <option value="20">20</option>
                                <option value="50">50</option>
                                <option value="all">All</option>
                            </select>
                        </div>

                        <!-- Date Filter -->
                        <div>
                            <label class="block text-[10px] font-bold text-slate-500 dark:text-slate-400 uppercase tracking-wider mb-2">Timeframe</label>
                            <select id="dateFilter" class="bg-slate-50 dark:bg-slate-900 border border-slate-200 dark:border-slate-600 text-slate-700 dark:text-slate-300 text-xs font-bold rounded-lg theme-focus block w-48 p-3 cursor-pointer transition-colors duration-300" onchange="currentPage=1; toggleCustomDates(); filterTable();">
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
                                <input type="date" id="startDate" class="bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-600 text-slate-700 dark:text-slate-300 text-xs font-bold rounded-lg p-3 theme-focus transition-colors duration-300" onchange="currentPage=1; filterTable();">
                            </div>
                            <div>
                                <label class="block text-[10px] font-bold text-slate-500 dark:text-slate-400 uppercase tracking-wider mb-2">End Date</label>
                                <input type="date" id="endDate" class="bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-600 text-slate-700 dark:text-slate-300 text-xs font-bold rounded-lg p-3 theme-focus transition-colors duration-300" onchange="currentPage=1; filterTable();">
                            </div>
                        </div>
                    </div>

                    <div>
                        <label class="block text-[10px] font-bold text-slate-500 dark:text-slate-400 uppercase tracking-wider mb-2">Registration Status</label>
                        <select id="statusFilter" class="bg-slate-50 dark:bg-slate-900 border border-slate-200 dark:border-slate-600 text-slate-700 dark:text-slate-300 text-xs font-bold rounded-lg theme-focus block w-48 p-3 cursor-pointer transition-colors duration-300" onchange="currentPage=1; filterTable();">
                            <option value="All Statuses">All Statuses</option>
                            <option value="Fully Paid">Fully Paid (Confirmed)</option>
                            <option value="Partially Paid">Partially Paid</option>
                            <option value="Outstanding">Outstanding</option>
                            <option value="Cancelled">Cancelled</option>
                            <option value="Checked Out">Checked Out (Historical)</option>
                        </select>
                    </div>
                </div>

                <!-- Data Table Card -->
                <div class="bg-white dark:bg-slate-800 rounded-2xl custom-shadow border border-slate-100 dark:border-slate-700 overflow-hidden transition-colors duration-300 h-auto">
                    <div class="overflow-x-auto w-full">
                        <table class="w-full text-left border-collapse" id="guestTable">
                            <thead class="bg-slate-50 dark:bg-slate-800 border-b border-slate-200 dark:border-slate-700 transition-colors duration-300">
                                <tr>
                                    <th class="px-6 py-4 text-[10px] font-bold text-slate-500 dark:text-slate-400 uppercase tracking-wider">Full Name</th>
                                    <th class="px-6 py-4 text-[10px] font-bold text-slate-500 dark:text-slate-400 uppercase tracking-wider">Status</th>
                                    <th class="px-6 py-4 text-[10px] font-bold text-slate-500 dark:text-slate-400 uppercase tracking-wider">Room Type</th>
                                    <th class="px-6 py-4 text-[10px] font-bold text-slate-500 dark:text-slate-400 uppercase tracking-wider">Booking Date</th>
                                    <th class="px-6 py-4 text-[10px] font-bold text-slate-500 dark:text-slate-400 uppercase tracking-wider">Check-in</th>
                                    <th class="px-6 py-4 text-[10px] font-bold text-slate-500 dark:text-slate-400 uppercase tracking-wider">Check-out</th>
                                    <th class="px-6 py-4 text-[10px] font-bold text-slate-500 dark:text-slate-400 uppercase tracking-wider">Nights</th>
                                    <th class="px-6 py-4 text-[10px] font-bold text-slate-500 dark:text-slate-400 uppercase tracking-wider">Rooms</th>
                                    <th class="px-6 py-4 text-[10px] font-bold text-slate-500 dark:text-slate-400 uppercase tracking-wider text-center">Actions</th>
                                </tr>
                            </thead>
                            <tbody id="guestTableBody" class="divide-y divide-slate-100 dark:divide-slate-700/50 bg-white dark:bg-slate-900 transition-colors duration-300">
                            </tbody>
                        </table>
                    </div>
                    
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

            <?php include 'Includes/footer.php'; ?>

        </main>
    </div>

    <!-- ========================================== -->
    <!-- MODAL 1: VIEW DETAILS (Read-Only) -->
    <!-- ========================================== -->
    <div id="view-modal-backdrop" class="fixed inset-0 bg-slate-900/60 dark:bg-slate-900/80 backdrop-blur-sm z-50 hidden items-center justify-center p-4 overflow-y-auto transition-colors duration-300">
        <div class="bg-white dark:bg-slate-900 w-full max-w-4xl rounded-2xl shadow-2xl p-6 relative animate-fadeIn border border-slate-100 dark:border-slate-700 transition-colors duration-300">
            
            <button onclick="closeViewModal()" class="absolute top-4 right-4 text-slate-400 dark:text-slate-500 hover:text-slate-600 dark:hover:text-slate-300 transition-colors">
                <i class="fa-solid fa-xmark text-lg"></i>
            </button>
            
            <div class="mb-6 border-b border-slate-100 dark:border-slate-800 pb-4">
                <h2 class="text-lg font-bold text-slate-800 dark:text-white flex items-center gap-2">
                    <i class="fa-solid fa-address-card text-blue-500"></i> Guest Record Details
                </h2>
                <p class="text-xs text-slate-500 dark:text-slate-400 mt-1 uppercase tracking-wider font-bold">Booking Ref: <span id="view-booking-id" class="text-slate-800 dark:text-slate-200"></span></p>
            </div>
            
            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                <!-- Left Column: Particulars -->
                <div class="space-y-4">
                    <div class="grid grid-cols-2 gap-4 bg-slate-50 dark:bg-slate-800 p-4 rounded-xl border border-slate-200 dark:border-slate-700">
                        <div><span class="block text-[10px] text-slate-500 uppercase tracking-wider font-bold">Guest Name</span><strong id="view-name" class="text-sm text-slate-800 dark:text-slate-200"></strong></div>
                        <div><span class="block text-[10px] text-slate-500 uppercase tracking-wider font-bold">Date Booked</span><strong id="view-booking-date" class="text-sm text-slate-800 dark:text-slate-200"></strong></div>
                        <div><span class="block text-[10px] text-slate-500 uppercase tracking-wider font-bold">Check-in</span><strong id="view-checkin" class="text-sm text-slate-800 dark:text-slate-200"></strong></div>
                        <div><span class="block text-[10px] text-slate-500 uppercase tracking-wider font-bold">Check-out</span><strong id="view-checkout" class="text-sm text-slate-800 dark:text-slate-200"></strong></div>
                        <div><span class="block text-[10px] text-slate-500 uppercase tracking-wider font-bold">Room Assignment</span><strong id="view-room-type" class="text-sm text-slate-800 dark:text-slate-200"></strong></div>
                        <div><span class="block text-[10px] text-slate-500 uppercase tracking-wider font-bold">Source / Agency</span><strong id="view-source" class="text-sm text-slate-800 dark:text-slate-200"></strong></div>
                    </div>

                    <div class="bg-amber-50 dark:bg-amber-900/20 p-4 rounded-xl border border-amber-200 dark:border-amber-800">
                        <span class="block text-[10px] text-amber-600 dark:text-amber-500 uppercase tracking-wider font-bold mb-1"><i class="fa-solid fa-star"></i> Special Requests & Notes</span>
                        <p id="view-notes" class="text-sm text-slate-700 dark:text-slate-300 font-medium"></p>
                    </div>
                </div>

                <!-- Right Column: Financial Ledger Summary -->
                <div class="bg-indigo-50/50 dark:bg-slate-800/80 p-6 rounded-xl border border-indigo-100 dark:border-slate-700 flex flex-col justify-center space-y-5">
                    <h3 class="text-xs text-slate-500 dark:text-slate-400 uppercase tracking-widest font-black border-b border-indigo-200 dark:border-slate-600 pb-3"><i class="fa-solid fa-file-invoice-dollar mr-1"></i> Financial Summary</h3>
                    
                    <div class="flex justify-between items-center">
                        <span class="text-sm font-bold text-slate-600 dark:text-slate-400">Total Billed:</span>
                        <strong id="view-total-amount" class="text-base font-black text-slate-900 dark:text-white font-mono"></strong>
                    </div>
                    
                    <div class="flex justify-between items-center">
                        <span class="text-sm font-bold text-slate-600 dark:text-slate-400">Total Paid:</span>
                        <strong id="view-total-paid" class="text-base font-black text-emerald-600 dark:text-emerald-400 font-mono"></strong>
                    </div>
                    
                    <div class="flex justify-between items-center border-t border-indigo-200 dark:border-slate-600 pt-4">
                        <span class="text-sm font-bold text-slate-600 dark:text-slate-400 uppercase tracking-widest">Balance Due:</span>
                        <strong id="view-balance-due" class="text-2xl font-black text-rose-600 dark:text-rose-400 font-mono"></strong>
                    </div>
                </div>
            </div>
            
            <div class="mt-6 flex justify-end">
                <button onclick="closeViewModal()" class="bg-slate-100 dark:bg-slate-800 hover:bg-slate-200 dark:hover:bg-slate-700 text-slate-700 dark:text-slate-300 border border-slate-200 dark:border-slate-600 font-bold py-2.5 px-6 rounded-xl transition-colors shadow-sm text-sm">
                    Close Viewer
                </button>
            </div>
        </div>
    </div>

    <!-- ========================================== -->
    <!-- MODAL 2: EDIT PARTICULARS (Dashboard Replica) -->
    <!-- ========================================== -->
    <div id="edit-modal-backdrop" class="fixed inset-0 bg-slate-900/60 dark:bg-slate-900/80 backdrop-blur-sm z-50 hidden items-center justify-center p-4 overflow-y-auto transition-colors duration-300">
        <div class="bg-white dark:bg-slate-900 w-full max-w-4xl rounded-2xl shadow-2xl border border-slate-100 dark:border-slate-700 p-6 space-y-5 flex flex-col relative animate-fadeIn transition-colors duration-300">
            
            <button onclick="closeEditModal()" class="absolute top-4 right-4 text-slate-400 dark:text-slate-500 hover:text-slate-600 dark:hover:text-slate-300 transition-colors z-10">
                <i class="fa-solid fa-xmark text-lg"></i>
            </button>
            
            <div class="flex justify-between items-center border-b border-slate-100 dark:border-slate-800 pb-3 shrink-0">
                <div>
                    <h2 class="text-base font-bold text-slate-900 dark:text-white flex items-center gap-2"><i class="fa-solid fa-pen-to-square theme-text"></i> Edit Reservation Terminal</h2>
                    <p class="text-[9px] text-slate-500 dark:text-slate-400 font-bold uppercase tracking-widest mt-1 text-rose-500"><i class="fa-solid fa-lock text-[8px]"></i> Administrator Edit Bypass Access</p>
                </div>
            </div>
            
            <form id="editRecordForm" onsubmit="submitEditForm(event)" class="space-y-4">
                <input type="hidden" name="action" value="update_record">
                <input type="hidden" name="id" id="edit_id">

                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4 bg-slate-50 dark:bg-slate-800 p-4 rounded-xl border border-slate-200 dark:border-slate-700 shrink-0 transition-colors duration-300 mb-2">
                    <div>
                        <label class="block text-[10px] font-bold text-slate-600 dark:text-slate-400 mb-1">Agency Name</label>
                        <input type="text" id="edit_agency_name" class="w-full bg-slate-200/50 dark:bg-slate-900/50 border border-slate-300 dark:border-slate-600 text-slate-500 dark:text-slate-400 rounded-lg p-2 text-xs outline-none cursor-not-allowed font-semibold" readonly>
                    </div>
                    <div>
                        <label class="block text-[10px] font-bold text-slate-600 dark:text-slate-400 mb-1">Booking Officer</label>
                        <input type="text" id="edit_booking_officer" class="w-full bg-slate-200/50 dark:bg-slate-900/50 border border-slate-300 dark:border-slate-600 text-slate-500 dark:text-slate-400 rounded-lg p-2 text-xs outline-none cursor-not-allowed font-semibold" readonly>
                    </div>
                </div>

                <div class="p-3 bg-slate-50 dark:bg-slate-800 rounded-xl border border-slate-200 dark:border-slate-700 space-y-2 relative transition-colors duration-300">
                    
                    <div class="grid grid-cols-1 sm:grid-cols-12 gap-2">
                        <div class="sm:col-span-8">
                            <label class="block text-[9px] font-bold text-slate-400 dark:text-slate-500 uppercase mb-0.5">Guest Name *</label>
                            <input type="text" name="guest_name" id="edit_guest_name" class="theme-focus w-full bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-600 rounded-lg p-1.5 text-xs outline-none text-slate-900 dark:text-white font-bold transition-all" required>
                        </div>
                        <div class="sm:col-span-4">
                            <label class="block text-[9px] font-bold text-slate-400 dark:text-slate-500 uppercase mb-0.5">Booking Date Created *</label>
                            <input type="date" name="booking_date" id="edit_booking_date" class="theme-focus w-full bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-600 rounded-lg p-1.5 text-xs outline-none text-slate-900 dark:text-white font-bold transition-all" required>
                        </div>
                    </div>
                    
                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-2 mt-1">
                        <div>
                            <label class="block text-[9px] font-bold text-slate-400 dark:text-slate-500 uppercase mb-0.5">Guest Type *</label>
                            <select name="guest_type" id="edit_guest_type" class="theme-focus w-full bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-600 rounded-lg p-1.5 text-xs outline-none text-slate-900 dark:text-white transition-all" required>
                                <option value="Resident">Resident (KES)</option>
                                <option value="Non Resident">Non Resident (USD)</option>
                            </select>
                        </div>
                        <div>
                            <label class="block text-[9px] font-bold text-slate-400 dark:text-slate-500 uppercase mb-0.5">Room Tier *</label>
                            <select name="room_tier" id="edit_room_tier" class="theme-focus w-full bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-600 rounded-lg p-1.5 text-xs outline-none text-slate-900 dark:text-white transition-all" required>
                                <option value="Deluxe Room">Deluxe Room</option>
                                <option value="Superior Tent">Superior Tent</option>
                            </select>
                        </div>
                    </div>

                    <div class="grid grid-cols-1 sm:grid-cols-12 gap-2 mt-1 border-b border-slate-200 dark:border-slate-700 pb-2">
                        <div class="sm:col-span-3">
                            <label class="block text-[9px] font-bold text-slate-400 dark:text-slate-500 uppercase mb-0.5">Room Type *</label>
                            <select name="room_type" id="edit_room_type" class="theme-focus w-full bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-600 rounded-lg p-1.5 text-xs outline-none text-slate-900 dark:text-white transition-all" required>
                                <option value="Single Room">Single Room</option>
                                <option value="Double Room">Double Room</option>
                                <option value="Triple Room">Triple Room</option>
                                <option value="Family Room">Family Room</option>
                            </select>
                        </div>
                        <div class="sm:col-span-2">
                            <label class="block text-[9px] font-bold text-slate-400 dark:text-slate-500 uppercase mb-0.5">Rooms Needed</label>
                            <input type="number" min="1" name="rooms_count" id="edit_rooms_count" class="theme-focus w-full bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-600 rounded-lg p-1.5 text-xs text-center text-slate-900 dark:text-white transition-all" required>
                        </div>
                        <div class="sm:col-span-3">
                            <label class="block text-[9px] font-bold text-slate-400 dark:text-slate-500 uppercase mb-0.5">Check-In *</label>
                            <input type="date" name="check_in" id="edit_check_in" class="theme-focus bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-600 rounded-lg p-1.5 text-xs w-full text-slate-900 dark:text-white transition-all" required>
                        </div>
                        <div class="sm:col-span-2">
                            <label class="block text-[9px] font-bold text-slate-400 dark:text-slate-500 uppercase mb-0.5">Nights</label>
                            <input type="number" min="1" name="nights" id="edit_nights" class="theme-focus w-full bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-600 rounded-lg p-1.5 text-xs text-center text-slate-900 dark:text-white transition-all" required>
                        </div>
                        <div class="sm:col-span-2">
                            <label class="block text-[9px] font-bold text-emerald-600 dark:text-emerald-400 uppercase mb-0.5" title="Per room, per night">Disc/Room</label>
                            <input type="number" step="0.01" min="0" name="discount" id="edit_discount" class="theme-focus w-full bg-emerald-50 dark:bg-emerald-900/30 border border-emerald-200 dark:border-emerald-700 text-emerald-700 dark:text-emerald-400 font-bold rounded-lg p-1.5 text-xs text-center outline-none transition-all" required>
                        </div>
                    </div>

                    <div class="grid grid-cols-1 sm:grid-cols-4 gap-2 mt-1 bg-slate-100 dark:bg-slate-900 p-2 rounded-lg border border-slate-200 dark:border-slate-800">
                        <div><label class="block text-[9px] font-bold text-slate-500 dark:text-slate-400 uppercase mb-0.5">Adults</label><input type="number" min="0" name="adults" id="edit_adults" class="theme-focus w-full bg-white dark:bg-slate-800 border border-slate-300 dark:border-slate-700 rounded-lg p-1.5 text-xs text-center text-slate-900 dark:text-white transition-all" required></div>
                        <div><label class="block text-[9px] font-bold text-slate-500 dark:text-slate-400 uppercase mb-0.5">Children</label><input type="number" min="0" name="children" id="edit_children" class="theme-focus w-full bg-white dark:bg-slate-800 border border-slate-300 dark:border-slate-700 rounded-lg p-1.5 text-xs text-center text-slate-900 dark:text-white transition-all" required></div>
                        <div><label class="block text-[9px] font-bold text-slate-500 dark:text-slate-400 uppercase mb-0.5" title="Rooms occupied by children only">Child Rooms</label><input type="number" min="0" name="child_rooms" id="edit_child_rooms" class="theme-focus w-full bg-white dark:bg-slate-800 border border-slate-300 dark:border-slate-700 rounded-lg p-1.5 text-xs text-center text-slate-900 dark:text-white transition-all" required></div>
                        
                        <div class="flex items-end pb-1.5 gap-4">
                            <label class="flex items-center gap-1.5 cursor-pointer pl-1">
                                <input type="checkbox" name="under_12" id="edit_under_12" value="1" class="w-3 h-3 bg-slate-50 dark:bg-slate-800 rounded border-slate-300 dark:border-slate-600" style="accent-color: var(--theme-color);">
                                <span class="text-[9px] font-bold text-slate-600 dark:text-slate-400 uppercase">Under 12 Yrs?</span>
                            </label>
                            <label class="flex items-center gap-1.5 cursor-pointer">
                                <input type="checkbox" id="edit_has_requests" class="w-3 h-3 text-amber-500 bg-slate-50 dark:bg-slate-800 rounded border-slate-300 dark:border-slate-600 focus:ring-amber-500" onchange="toggleEditSpecialReq(this)">
                                <span class="text-[9px] font-bold text-amber-600 dark:text-amber-500 uppercase">Special Req?</span>
                            </label>
                        </div>
                    </div>
                    
                    <div id="edit-req-wrap" class="hidden mt-2 transition-all">
                        <textarea name="special_requests" id="edit_special_requests" class="w-full bg-amber-50 dark:bg-amber-900/20 border border-amber-300 dark:border-amber-700 rounded-lg p-2 text-xs outline-none focus:ring-2 focus:ring-amber-500 text-slate-700 dark:text-slate-300 placeholder-amber-700/40 dark:placeholder-amber-500/40 font-medium" rows="2" placeholder="Enter dietary requirements, accessibility needs, or other special requests here..."></textarea>
                    </div>
                </div>

                <div class="flex items-center gap-3 pt-2 shrink-0">
                    <button type="button" onclick="closeEditModal()" class="flex-1 bg-slate-100 dark:bg-slate-800 hover:bg-slate-200 dark:hover:bg-slate-700 text-slate-600 dark:text-slate-300 border border-slate-200 dark:border-slate-600 font-bold py-2.5 px-4 rounded-xl transition-colors shadow-sm text-sm">
                        Cancel Update
                    </button>
                    <button type="submit" class="theme-btn flex-1 text-white font-bold py-2.5 px-4 rounded-xl transition-colors shadow-sm text-sm flex items-center justify-center gap-2">
                        <i class="fa-solid fa-save"></i> Synchronize Particulars
                    </button>
                </div>
            </form>
        </div>
    </div>

</body>
</html>