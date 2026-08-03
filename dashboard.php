<?php
// We still check the session here just to prevent logged-out users from seeing the HTML dashboard
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
if (!isset($_SESSION['logged_in'])) {
    header("Location: login.php");
    exit;
}
$current_page = basename($_SERVER['PHP_SELF']);

// 1. LOAD SYSTEM SETTINGS & THEME
if (!isset($GLOBALS['system_settings'])) {
    require_once 'Includes/load_settings.php';
}
$set = $GLOBALS['system_settings'];

$theme = $set['theme_color'] ?? 'emerald';
$primaryColor = '#046a38'; 
if ($theme === 'safari') $primaryColor = '#8B3C28';
elseif ($theme === 'kairi') $primaryColor = '#802b1f';
elseif ($theme === 'blue') $primaryColor = '#2563eb';
elseif ($theme === 'custom') $primaryColor = $set['custom_primary'] ?? '#046a38';

// Verify Security Role and Operations Parameter
$is_admin = strtolower($_SESSION['role'] ?? '') === 'admin';
$retro_setting = (int)($set['allow_retroactive_bookings'] ?? 0);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Rhino Tourist Camp - Reservation Management</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <script>
        tailwind.config = { darkMode: 'class', }
    </script>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    
    <!-- Pass Security Parameters to JavaScript Environment -->
    <script>
        const IS_ADMIN = <?php echo $is_admin ? 'true' : 'false'; ?>;
        const RETRO_SETTING = <?php echo $retro_setting; ?>;
        // 0 = Locked, 1 = Admins Only, 2 = Everyone
        const CAN_BACKDATE = (RETRO_SETTING === 2) || (RETRO_SETTING === 1 && IS_ADMIN);
    </script>

    <!-- Version bump to clear cache and load Theme JS logic -->
    <script src="js/dashboard.js?v=9" defer></script>
    
    <style>
        :root {
            --theme-color: <?php echo $primaryColor; ?>;
            --theme-color-focus: <?php echo $primaryColor; ?>33;
        }
        .custom-shadow { box-shadow: 0 4px 20px -2px rgba(0, 0, 0, 0.05); }
        .theme-btn { background-color: var(--theme-color); transition: filter 0.2s; }
        .theme-btn:hover { filter: brightness(85%); }
        .theme-text { color: var(--theme-color); }
        .theme-text-hover:hover { color: var(--theme-color) !important; }
        
        sup.ledger-out { font-size: 0.65rem; font-weight: 900; text-transform: uppercase; color: #ef4444; background-color: #fef2f2; border: 1px solid #fca5a5; padding: 1px 3px; border-radius: 4px; margin-left: 2px; vertical-align: super; }
        .dark sup.ledger-out { background-color: rgba(153, 27, 27, 0.2); border-color: rgba(225, 29, 72, 0.5); color: #fda4af; }
    </style>
</head>
<body data-primary-color="<?php echo $primaryColor; ?>" class="bg-[#f8fafc] dark:bg-slate-900 text-[#334155] dark:text-slate-200 font-sans antialiased min-h-screen overflow-hidden transition-colors duration-300">
    <div class="flex h-screen w-screen overflow-hidden">
        
        <!-- SIDEBAR INJECTION -->
        <?php include 'Includes/sidebar.php'; ?>

        <main class="flex-1 flex flex-col h-full overflow-hidden bg-[#f8fafc] dark:bg-slate-900 transition-colors duration-300">
            
            <?php include 'Includes/header.php'; ?>

            <div class="flex-1 overflow-y-auto p-4 lg:p-6 space-y-6">
                
                <!-- TOP CONTROLS -->
                <div class="bg-white dark:bg-slate-800 p-4 rounded-2xl custom-shadow border border-slate-100 dark:border-slate-700 flex flex-col sm:flex-row items-center justify-between gap-4 transition-colors duration-300">
                    <div class="flex items-center gap-2 w-full sm:w-auto justify-between">
                        <div class="flex items-center border border-slate-200 dark:border-slate-600 rounded-xl px-3 bg-slate-50 dark:bg-slate-900 transition-colors duration-300">
                            <i class="fa-regular fa-calendar text-slate-400 dark:text-slate-500 mr-2 text-sm"></i>
                            <input type="date" id="global-manifest-datepicker" onchange="syncSelectedManifestToDate(this.value)" class="bg-transparent py-2 font-semibold text-sm text-slate-700 dark:text-slate-300 outline-none w-32 cursor-pointer">
                        </div>
                        <div class="flex items-center gap-1">
                            <button onclick="navigateDaysOffset(-1)" class="w-9 h-9 border border-slate-200 dark:border-slate-600 bg-white dark:bg-slate-800 hover:bg-slate-50 dark:hover:bg-slate-700 rounded-xl flex items-center justify-center text-slate-600 dark:text-slate-400 transition-colors duration-300"><i class="fa-solid fa-chevron-left text-xs"></i></button>
                            <button onclick="navigateDaysOffset(1)" class="w-9 h-9 border border-slate-200 dark:border-slate-600 bg-white dark:bg-slate-800 hover:bg-slate-50 dark:hover:bg-slate-700 rounded-xl flex items-center justify-center text-slate-600 dark:text-slate-400 transition-colors duration-300"><i class="fa-solid fa-chevron-right text-xs"></i></button>
                        </div>
                    </div>
                    <div class="text-center hidden md:block">
                        <span class="block text-[10px] uppercase font-bold tracking-wider text-slate-400 dark:text-slate-500">Selected Manifest Date</span>
                        <span id="label-active-manifest-date" class="text-base font-black text-slate-800 dark:text-white"></span>
                    </div>
                    <button onclick="openReservationModal()" class="w-full sm:w-auto theme-btn text-white font-bold py-2.5 px-5 rounded-xl shadow-md transition flex items-center justify-center gap-2 text-sm">
                        <i class="fa-solid fa-calendar-plus"></i> Create New Reservation
                    </button>
                </div>

                <!-- METRICS GRID -->
                <div class="space-y-4">
                    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4" id="room-metrics-grid"></div>
                    <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                        <div class="bg-[#046a38] dark:bg-emerald-900 text-white p-4 rounded-2xl flex justify-between items-center shadow-sm border border-emerald-800 transition-colors duration-300">
                            <div><p class="text-[10px] font-bold tracking-wider uppercase text-emerald-200 dark:text-emerald-300">Total Occupants Today</p><p class="text-2xl font-black mt-0.5"><span id="meta-beds-used">0</span> <span class="text-xs font-normal text-emerald-200 dark:text-emerald-400">/ 77 Max Beds</span></p></div>
                            <div class="bg-[#03542c] dark:bg-emerald-950 p-2.5 rounded-xl"><i class="fa-solid fa-bed text-lg text-amber-400"></i></div>
                        </div>
                        <div class="bg-[#b91c1c] dark:bg-rose-900 text-white p-4 rounded-2xl flex justify-between items-center shadow-sm border border-rose-800 transition-colors duration-300">
                            <div><p class="text-[10px] font-bold tracking-wider uppercase text-rose-200 dark:text-rose-300">Checking Out Today</p><p class="text-2xl font-black mt-0.5"><span id="meta-checkouts-count">0</span> <span class="text-xs font-normal text-rose-200 dark:text-rose-400">Pax Leaving</span></p></div>
                            <div class="bg-[#991b1b] dark:bg-rose-950 p-2.5 rounded-xl"><i class="fa-solid fa-right-from-bracket text-lg text-white"></i></div>
                        </div>
                        <div class="bg-[#0f172a] dark:bg-slate-800 text-white p-4 rounded-2xl flex justify-between items-center shadow-sm border border-slate-700 transition-colors duration-300">
                            <div><p class="text-[10px] font-bold tracking-wider uppercase text-slate-400">Total Beds Occupied</p><p class="text-2xl font-black mt-0.5"><span id="meta-rooms-used">0</span> <span class="text-xs font-normal text-slate-400">/ 34 Max Rooms</span></p></div>
                            <div class="bg-[#1e293b] dark:bg-slate-900 p-2.5 rounded-xl"><i class="fa-solid fa-door-closed text-xl text-cyan-400"></i></div>
                        </div>
                    </div>
                </div>

                <!-- ENHANCED SEARCH FILTERS -->
                <div class="bg-white dark:bg-slate-800 p-3 rounded-2xl custom-shadow border border-slate-100 dark:border-slate-700 flex flex-col sm:flex-row gap-3 items-center transition-colors duration-300">
                    <div class="w-full sm:w-1/3 relative flex items-center border border-slate-200 dark:border-slate-600 rounded-xl px-3 bg-slate-50 dark:bg-slate-900 transition-colors duration-300">
                        <i class="fa-solid fa-sliders text-slate-400 dark:text-slate-500 mr-2 text-xs"></i>
                        <select id="filter-category-select" onchange="updateSearchPlaceholder()" class="bg-transparent py-2.5 w-full text-xs font-bold text-slate-700 dark:text-slate-300 outline-none cursor-pointer">
                            <option value="all">Search All Parameters</option>
                            <option value="pipeline">Filter by Travel Agency</option>
                            <option value="client">Filter by Client Name</option>
                            <option value="officer">Filter by Booking Officer</option>
                        </select>
                    </div>
                    <div class="w-full sm:w-2/3 relative flex items-center border border-slate-200 dark:border-slate-600 rounded-xl px-3 bg-slate-50 dark:bg-slate-900 transition-colors duration-300">
                        <input type="text" id="filter-search-value" onkeyup="applyLedgerLiveSearchFilters()" placeholder="Search by Client Name, Travel Agency, or Booking Officer..." class="bg-transparent py-2.5 w-full text-xs text-slate-700 dark:text-slate-300 outline-none pr-8">
                        <button onclick="applyLedgerLiveSearchFilters()" class="absolute right-3 text-slate-400 dark:text-slate-500 theme-text-hover transition"><i class="fa-solid fa-magnifying-glass text-sm"></i></button>
                    </div>
                </div>

                <!-- AWAITING PAYMENT TABLE -->
                <div id="printable-daily-manifest-area" class="bg-white dark:bg-slate-800 rounded-2xl custom-shadow border border-slate-100 dark:border-slate-700 overflow-hidden transition-colors duration-300">
                    <div class="p-4 bg-white dark:bg-slate-800 border-b border-slate-100 dark:border-slate-700 flex justify-between items-center transition-colors duration-300">
                        <h3 class="text-xs font-bold tracking-wide uppercase text-slate-900 dark:text-white flex items-center gap-2"><i class="fa-solid fa-book text-amber-500 text-sm"></i> Reserved Entries (Awaiting Payment)</h3>
                        <div class="flex items-center gap-2">
                            <span id="reserved-counter-badge" class="text-[11px] bg-slate-100 dark:bg-slate-700 text-slate-600 dark:text-slate-300 px-2 py-0.5 font-bold rounded-md border border-slate-200 dark:border-slate-600 transition-colors duration-300">0 Entries</span>
                        </div>
                    </div>
                    <div class="overflow-x-auto">
                        <table class="w-full text-left border-collapse">
                            <thead>
                                <tr class="border-b border-slate-100 dark:border-slate-700 text-[10px] font-bold tracking-wider text-slate-400 dark:text-slate-500 uppercase bg-slate-50/70 dark:bg-slate-900/50 transition-colors duration-300">
                                    <th class="p-4 min-w-[180px]">Travel Pipeline <span class="block text-[9px] lowercase font-normal text-slate-400 dark:text-slate-500">(officer name)</span></th>
                                    <th class="p-4">Client Name</th>
                                    <th class="p-3 text-center">Single</th>
                                    <th class="p-3 text-center">Double</th>
                                    <th class="p-3 text-center">Triple</th>
                                    <th class="p-3 text-center">Family</th>
                                    <th class="p-3 text-center">Beds Occupied</th>
                                    <th class="p-4 text-center">Officer</th>
                                    <th class="p-4 text-center">Consultant</th>
                                    <th class="p-4 text-right">Actions</th>
                                </tr>
                            </thead>
                            <tbody id="manifest-records-table-body" class="divide-y divide-slate-100 dark:divide-slate-700/50 text-xs text-slate-700 dark:text-slate-300 font-semibold bg-white dark:bg-slate-800 transition-colors duration-300"></tbody>
                        </table>
                    </div>
                </div>

                <!-- CONFIRMED TABLE -->
                <div id="printable-confirmed-ledger-area" class="bg-white dark:bg-slate-800 rounded-2xl custom-shadow border border-slate-100 dark:border-slate-700 overflow-hidden mt-6 transition-colors duration-300">
                    <div class="p-4 border-b border-slate-100 dark:border-slate-700 bg-white dark:bg-slate-800 flex justify-between items-center transition-colors duration-300">
                        <h3 class="text-xs font-bold tracking-wide uppercase text-slate-900 dark:text-white flex items-center gap-2"><i class="fa-solid fa-book theme-text text-sm"></i> Booked Entries for this Day (Confirmed)</h3>
                        <div class="flex items-center gap-2">
                            <span id="booked-counter-badge" class="text-[11px] bg-slate-100 dark:bg-slate-700 text-slate-600 dark:text-slate-300 px-2 py-0.5 font-bold rounded-md border border-slate-200 dark:border-slate-600 transition-colors duration-300">0 Entries</span>
                        </div>
                    </div>
                    <div class="overflow-x-auto">
                        <table class="w-full text-left border-collapse">
                            <thead>
                                <tr class="border-b border-slate-100 dark:border-slate-700 text-[10px] font-bold tracking-wider text-slate-400 dark:text-slate-500 uppercase bg-slate-50/70 dark:bg-slate-900/50 transition-colors duration-300">
                                    <th class="p-4 min-w-[180px]">Travel Pipeline <span class="block text-[9px] lowercase font-normal text-slate-400 dark:text-slate-500">(officer name)</span></th>
                                    <th class="p-4">Client Name</th>
                                    <th class="p-3 text-center">Single</th>
                                    <th class="p-3 text-center">Double</th>
                                    <th class="p-3 text-center">Triple</th>
                                    <th class="p-3 text-center">Family</th>
                                    <th class="p-3 text-center">Beds Occupied</th>
                                    <th class="p-4 text-center">Officer</th>
                                    <th class="p-4 text-center">Consultant</th>
                                    <th class="p-4 text-center">Receipt No</th>
                                </tr>
                            </thead>
                            <tbody id="confirmed-records-table-body" class="divide-y divide-slate-100 dark:divide-slate-700/50 text-xs text-slate-700 dark:text-slate-300 font-semibold bg-white dark:bg-slate-800 transition-colors duration-300"></tbody>
                        </table>
                    </div>
                </div>

            </div>
            
            <?php include 'Includes/footer.php'; ?>
        </main>
    </div>

    <!-- PAYMENT & CONFIRMATION MODAL -->
    <div id="payment-modal" class="fixed inset-0 bg-slate-900/80 backdrop-blur-sm z-50 hidden flex items-center justify-center p-4">
        <div class="bg-white dark:bg-slate-900 w-full max-w-lg rounded-2xl shadow-2xl border border-slate-100 dark:border-slate-700 p-6 space-y-5 relative animate-fadeIn transition-colors duration-300">
            <button onclick="closePaymentModal()" class="absolute top-4 right-4 text-slate-400 hover:text-slate-600 dark:hover:text-slate-200">
                <i class="fa-solid fa-xmark text-base"></i>
            </button>

            <h2 class="text-base font-bold text-slate-900 dark:text-white flex items-center gap-2 border-b border-slate-100 dark:border-slate-800 pb-3">
                <i class="fa-solid fa-cash-register theme-text"></i> Record Payment for <span id="modal-guest-name" class="theme-text"></span>
            </h2>

            <form onsubmit="submitPaymentViaAjax(event)" class="space-y-4">
                <input type="hidden" name="booking_id" id="modal-booking-id">
                
                <div class="bg-slate-50 dark:bg-slate-800 p-3 rounded-xl border border-slate-200 dark:border-slate-700 grid grid-cols-2 gap-2 text-xs transition-colors duration-300">
                    <div><span class="text-slate-400 dark:text-slate-500 block font-normal">Adult Portion:</span> <strong id="modal-display-adult-portion" class="text-slate-700 dark:text-slate-200"></strong></div>
                    <div><span class="text-slate-400 dark:text-slate-500 block font-normal">Child Portion:</span> <strong id="modal-display-child-portion" class="text-slate-700 dark:text-slate-200"></strong></div>
                    
                    <div id="modal-discount-wrapper" class="col-span-2 hidden">
                        <span class="text-rose-500 dark:text-rose-400 block font-bold text-[10px] uppercase">Contract Discount Applied: <strong id="modal-display-discount" class="pl-2"></strong></span>
                    </div>
                    
                    <div class="col-span-2 border-t border-slate-200 dark:border-slate-700 my-1"></div>
                    
                    <div><span class="text-slate-400 dark:text-slate-500 block font-normal">Final Amount Due:</span> <strong id="modal-display-total" class="text-slate-900 dark:text-white"></strong></div>
                    <div><span class="text-slate-400 dark:text-slate-500 block font-normal">Outstanding Balance:</span> <strong id="modal-display-balance" class="text-rose-600 dark:text-rose-400"></strong></div>
                </div>

                <div class="grid grid-cols-2 gap-3">
                    <div>
                        <label class="block text-[10px] font-bold text-slate-500 dark:text-slate-400 uppercase mb-1">Payment Amount *</label>
                        <input type="number" step="0.01" name="amount_paid" id="input-amount-paid" required class="w-full bg-white dark:bg-slate-800 border border-slate-300 dark:border-slate-600 text-slate-900 dark:text-white rounded-lg p-2.5 text-xs outline-none focus:ring-2 focus:ring-slate-500 font-bold transition-colors duration-300">
                    </div>
                    <div>
                        <label class="block text-[10px] font-bold text-slate-500 dark:text-slate-400 uppercase mb-1">Currency *</label>
                        <select name="currency" id="input-currency" class="w-full bg-slate-50 dark:bg-slate-900 border border-slate-300 dark:border-slate-600 rounded-lg p-2.5 text-xs font-bold text-slate-600 dark:text-slate-500 cursor-not-allowed transition-colors duration-300" style="pointer-events: none;" readonly>
                            <option value="KES">Kenya Shillings (KES)</option>
                            <option value="USD">US Dollars (USD)</option>
                        </select>
                    </div>
                </div>

                <div class="grid grid-cols-2 gap-3">
                    <div>
                        <label class="block text-[10px] font-bold text-slate-500 dark:text-slate-400 uppercase mb-1">Payment Method *</label>
                        <select name="payment_method" id="input-payment-method" onchange="toggleReferenceField()" class="w-full bg-white dark:bg-slate-800 border border-slate-300 dark:border-slate-600 text-slate-900 dark:text-white rounded-lg p-2.5 text-xs outline-none focus:ring-2 focus:ring-slate-500 font-bold transition-colors duration-300">
                            <option value="Cash">Cash</option>
                            <option value="M-Pesa">M-Pesa</option>
                            <option value="Bank Transfer">Bank Transfer</option>
                            <option value="Credit Card">Credit Card</option>
                        </select>
                    </div>
                    <div id="reference-wrapper">
                        <label class="block text-[10px] font-bold text-slate-500 dark:text-slate-400 uppercase mb-1" id="label-ref">Transaction Reference *</label>
                        <input type="text" name="reference_no" id="input-reference-no" placeholder="M-Pesa Code / Bank Ref" class="w-full bg-white dark:bg-slate-800 border border-slate-300 dark:border-slate-600 text-slate-900 dark:text-white rounded-lg p-2.5 text-xs outline-none focus:ring-2 focus:ring-slate-500 transition-colors duration-300">
                    </div>
                </div>

                <div class="flex justify-end gap-2 pt-2 border-t border-slate-100 dark:border-slate-800">
                    <button type="button" onclick="closePaymentModal()" class="px-4 py-2 text-xs font-bold text-slate-500 dark:text-slate-400 border border-slate-200 dark:border-slate-700 rounded-xl hover:bg-slate-50 dark:hover:bg-slate-800 transition-colors">Cancel</button>
                    <button type="submit" class="theme-btn text-white font-bold py-2 px-5 rounded-xl text-xs transition">Submit Payment</button>
                </div>
            </form>
        </div>
    </div>

    <!-- RESERVATION CAPTURE MODAL -->
    <div id="reservation-modal-backdrop" class="fixed inset-0 bg-slate-900/80 backdrop-blur-sm z-50 hidden flex items-center justify-center p-4 overflow-y-auto">
        <div class="bg-white dark:bg-slate-900 w-full max-w-4xl rounded-2xl shadow-2xl border border-slate-100 dark:border-slate-700 p-6 space-y-5 max-h-[90vh] flex flex-col relative animate-fadeIn transition-colors duration-300">
            <button onclick="closeReservationModal()" class="absolute top-4 right-4 text-slate-400 hover:text-slate-600 dark:hover:text-slate-200 text-sm z-10"><i class="fa-solid fa-xmark text-lg"></i></button>
            
            <div class="flex justify-between items-center border-b border-slate-100 dark:border-slate-800 pb-3 shrink-0">
                <h2 id="modal-terminal-title" class="text-base font-bold text-slate-900 dark:text-white flex items-center gap-2"><i class="fa-solid fa-calendar-plus theme-text"></i> New Reservation Terminal</h2>
                <input type="hidden" id="editing-target-reservation-id" value="">
            </div>

            <div class="shrink-0">
                <label class="block text-[10px] font-bold text-slate-500 dark:text-slate-400 uppercase tracking-wider mb-2">Booking Source *</label>
                <div class="grid grid-cols-2 gap-3">
                    <!-- Javascript will assign dynamic theme styles to these buttons -->
                    <button type="button" id="src-direct-btn" onclick="setBookingSource('direct')" class="py-2.5 px-4 rounded-xl border-2 font-semibold text-xs flex items-center justify-center gap-2 transition-all"><i class="fa-solid fa-user"></i> Direct Client</button>
                    <button type="button" id="src-agency-btn" onclick="setBookingSource('agency')" class="py-2.5 px-4 rounded-xl border-2 font-semibold text-xs flex items-center justify-center gap-2 transition-all"><i class="fa-solid fa-briefcase"></i> Travel Agency</button>
                </div>
            </div>

            <div id="agency-fields-wrapper" class="hidden grid grid-cols-1 sm:grid-cols-2 gap-4 bg-slate-50 dark:bg-slate-800 p-4 rounded-xl border border-slate-200 dark:border-slate-700 shrink-0 transition-colors duration-300">
                <div>
                    <label class="block text-[10px] font-bold text-slate-600 dark:text-slate-400 mb-1">Agency Name *</label>
                    <input type="text" id="input-agency-name" placeholder="e.g., Perfect Safaris" class="w-full bg-white dark:bg-slate-900 border border-slate-300 dark:border-slate-600 text-slate-900 dark:text-white rounded-lg p-2 text-xs outline-none focus:ring-2 focus:ring-slate-400 transition-colors duration-300">
                </div>
                <div>
                    <label class="block text-[10px] font-bold text-slate-600 dark:text-slate-400 mb-1">Booking Officer *</label>
                    <input type="text" id="input-booking-officer" placeholder="Agent rep name" class="w-full bg-white dark:bg-slate-900 border border-slate-300 dark:border-slate-600 text-slate-900 dark:text-white rounded-lg p-2 text-xs outline-none focus:ring-2 focus:ring-slate-400 transition-colors duration-300">
                </div>
            </div>

            <div class="space-y-2 flex-1 min-h-0 flex flex-col">
                <div class="flex justify-between items-center shrink-0">
                    <label class="text-[10px] font-bold text-slate-500 dark:text-slate-400 uppercase tracking-wider">Room & Guest Line Breakdown</label>
                    <button type="button" onclick="addNewAllocationRowRow()" style="color: var(--theme-color); border-color: var(--theme-color); background-color: var(--theme-color-focus);" class="text-[10px] hover:brightness-95 border font-bold py-1 px-2.5 rounded-lg transition-all flex items-center gap-1"><i class="fa-solid fa-plus"></i> Add Line</button>
                </div>
                <div id="dynamic-allocation-rows-container" class="space-y-3 flex-1 overflow-y-auto pr-2 max-h-[380px]"></div>
            </div>

            <div class="flex justify-end gap-3 pt-1 border-t border-slate-100 dark:border-slate-800 shrink-0">
                <button type="button" onclick="closeReservationModal()" class="px-4 py-2.5 text-xs font-bold text-slate-500 dark:text-slate-400 border border-slate-200 dark:border-slate-700 rounded-xl hover:bg-slate-50 dark:hover:bg-slate-800 transition-colors">Cancel</button>
                <button type="button" onclick="processAndValidateFormSubmission()" class="theme-btn text-white font-black tracking-wide py-3 px-6 rounded-xl text-sm shadow-md transition flex items-center gap-2"><i class="fa-solid fa-save"></i> Save Reservation Entry</button>
            </div>
        </div>
    </div>
</body>
</html>