<?php
// We still check the session here just to prevent logged-out users from seeing the HTML dashboard
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
if (!isset($_SESSION['logged_in'])) {
    header("Location: login.php");
    exit;
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Rhino Tourist Camp - Reservation Management</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    
    <!-- Link to our external JavaScript file -->
    <script src="js/dashboard.js" defer></script>
    
    <style>
        .custom-shadow { box-shadow: 0 4px 20px -2px rgba(0, 0, 0, 0.05); }
        sup.ledger-out { font-size: 0.65rem; font-weight: 900; text-transform: uppercase; color: #ef4444; background-color: #fef2f2; border: 1px solid #fca5a5; padding: 1px 3px; border-radius: 4px; margin-left: 2px; vertical-align: super; }
    </style>
</head>
<body class="bg-[#f8fafc] text-[#334155] font-sans antialiased min-h-screen overflow-hidden">
    <div class="flex h-screen w-screen overflow-hidden">
        
        <!-- SIDEBAR INJECTION -->
        <?php include 'Includes/sidebar.php'; ?>

        <main class="flex-1 flex flex-col h-full overflow-hidden">
            
        <?php include 'Includes/header.php'; ?>

            <div class="flex-1 overflow-y-auto p-4 lg:p-6 space-y-6">
                <div class="bg-white p-4 rounded-2xl custom-shadow border border-slate-100 flex flex-col sm:flex-row items-center justify-between gap-4">
                    <div class="flex items-center gap-2 w-full sm:w-auto justify-between">
                        <div class="flex items-center border border-slate-200 rounded-xl px-3 bg-slate-50">
                            <i class="fa-regular fa-calendar text-slate-400 mr-2 text-sm"></i>
                            <input type="date" id="global-manifest-datepicker" onchange="syncSelectedManifestToDate(this.value)" class="bg-transparent py-2 font-semibold text-sm text-slate-700 outline-none w-32">
                        </div>
                        <div class="flex items-center gap-1">
                            <button onclick="navigateDaysOffset(-1)" class="w-9 h-9 border border-slate-200 hover:bg-slate-50 rounded-xl flex items-center justify-center text-slate-600 transition"><i class="fa-solid fa-chevron-left text-xs"></i></button>
                            <button onclick="navigateDaysOffset(1)" class="w-9 h-9 border border-slate-200 hover:bg-slate-50 rounded-xl flex items-center justify-center text-slate-600 transition"><i class="fa-solid fa-chevron-right text-xs"></i></button>
                        </div>
                    </div>
                    <div class="text-center hidden md:block">
                        <span class="block text-[10px] uppercase font-bold tracking-wider text-slate-400">Selected Manifest Date</span>
                        <span id="label-active-manifest-date" class="text-base font-black text-slate-800"></span>
                    </div>
                    <button onclick="openReservationModal()" class="w-full sm:w-auto bg-[#046a38] hover:bg-[#03542c] text-white font-bold py-2.5 px-5 rounded-xl shadow-md transition flex items-center justify-center gap-2 text-sm">
                        <i class="fa-solid fa-calendar-plus"></i> Create New Reservation
                    </button>
                </div>

                <div class="space-y-4">
                    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4" id="room-metrics-grid"></div>
                    <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                        <div class="bg-[#046a38] text-white p-4 rounded-2xl flex justify-between items-center shadow-sm">
                            <div><p class="text-[10px] font-bold tracking-wider uppercase text-emerald-200">Total Occupants Today</p><p class="text-2xl font-black mt-0.5"><span id="meta-beds-used">0</span> <span class="text-xs font-normal text-emerald-200">/ 77 Max Beds</span></p></div>
                            <div class="bg-[#03542c] p-2.5 rounded-xl"><i class="fa-solid fa-bed text-lg text-amber-400"></i></div>
                        </div>
                        <div class="bg-[#b91c1c] text-white p-4 rounded-2xl flex justify-between items-center shadow-sm">
                            <div><p class="text-[10px] font-bold tracking-wider uppercase text-rose-200">Checking Out Today</p><p class="text-2xl font-black mt-0.5"><span id="meta-checkouts-count">0</span> <span class="text-xs font-normal text-rose-200">Pax Leaving</span></p></div>
                            <div class="bg-[#991b1b] p-2.5 rounded-xl"><i class="fa-solid fa-right-from-bracket text-lg text-white"></i></div>
                        </div>
                        <div class="bg-[#0f172a] text-white p-4 rounded-2xl flex justify-between items-center shadow-sm">
                            <div><p class="text-[10px] font-bold tracking-wider uppercase text-slate-400">Total Beds Occupied</p><p class="text-2xl font-black mt-0.5"><span id="meta-rooms-used">0</span> <span class="text-xs font-normal text-slate-400">/ 34 Max Rooms</span></p></div>
                            <div class="bg-[#1e293b] p-2.5 rounded-xl"><i class="fa-solid fa-door-closed text-xl text-cyan-400"></i></div>
                        </div>
                    </div>
                </div>

                <div class="bg-white p-3 rounded-2xl custom-shadow border border-slate-100 flex flex-col sm:flex-row gap-3 items-center">
                    <div class="w-full sm:w-2/3 relative flex items-center border border-slate-200 rounded-xl px-3 bg-slate-50">
                        <input type="text" id="filter-search-value" onkeyup="applyLedgerLiveSearchFilters()" placeholder="Search by Client Name, Travel Agency, or Booking Officer..." class="bg-transparent py-2.5 w-full text-xs text-slate-700 outline-none pr-8">
                        <button onclick="applyLedgerLiveSearchFilters()" class="absolute right-3 text-slate-400 hover:text-[#046a38] transition"><i class="fa-solid fa-magnifying-glass text-sm"></i></button>
                    </div>
                    <div class="w-full sm:w-1/3 relative flex items-center border border-slate-200 rounded-xl px-3 bg-slate-50">
                        <i class="fa-solid fa-sliders text-slate-400 mr-2 text-xs"></i>
                        <select id="filter-category-select" onchange="applyLedgerLiveSearchFilters()" class="bg-transparent py-2.5 w-full text-xs font-semibold text-slate-700 outline-none cursor-pointer">
                            <option value="all">Search All Parameters</option>
                            <option value="pipeline">Filter by Travel Agency</option>
                            <option value="client">Filter by Client Name</option>
                            <option value="officer">Filter by Booking Officer</option>
                        </select>
                    </div>
                </div>

                <div id="printable-daily-manifest-area" class="bg-white rounded-2xl custom-shadow border border-slate-100 overflow-hidden">
                    <div class="p-4 bg-white border-b border-slate-100 flex justify-between items-center">
                        <h3 class="text-xs font-bold tracking-wide uppercase text-slate-900 flex items-center gap-2"><i class="fa-solid fa-book text-amber-500 text-sm"></i> Reserved Entries (Awaiting Payment)</h3>
                        <div class="flex items-center gap-2">
                            <span id="reserved-counter-badge" class="text-[11px] bg-slate-100 text-slate-600 px-2 py-0.5 font-bold rounded-md">0 Entries</span>
                        </div>
                    </div>
                    <div class="overflow-x-auto">
                        <table class="w-full text-left border-collapse">
                            <thead>
                                <tr class="border-b border-slate-100 text-[10px] font-bold tracking-wider text-slate-400 uppercase bg-slate-50/70">
                                    <th class="p-4 min-w-[180px]">Travel Pipeline <span class="block text-[9px] lowercase font-normal text-slate-400">(officer name)</span></th>
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
                            <tbody id="manifest-records-table-body" class="divide-y divide-slate-100 text-xs text-slate-700 font-semibold"></tbody>
                        </table>
                    </div>
                </div>

                <div id="printable-confirmed-ledger-area" class="bg-white rounded-2xl custom-shadow border border-slate-100 overflow-hidden mt-6">
                    <div class="p-4 border-b border-slate-100 bg-white flex justify-between items-center">
                        <h3 class="text-xs font-bold tracking-wide uppercase text-slate-900 flex items-center gap-2"><i class="fa-solid fa-book text-emerald-600 text-sm"></i> Booked Entries for this Day (Confirmed)</h3>
                        <div class="flex items-center gap-2">
                            <span id="booked-counter-badge" class="text-[11px] bg-slate-100 text-slate-600 px-2 py-0.5 font-bold rounded-md">0 Entries</span>
                        </div>
                    </div>
                    <div class="overflow-x-auto">
                        <table class="w-full text-left border-collapse">
                            <thead>
                                <tr class="border-b border-slate-100 text-[10px] font-bold tracking-wider text-slate-400 uppercase bg-slate-50/70">
                                    <th class="p-4 min-w-[180px]">Travel Pipeline <span class="block text-[9px] lowercase font-normal text-slate-400">(officer name)</span></th>
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
                            <tbody id="confirmed-records-table-body" class="divide-y divide-slate-100 text-xs text-slate-700 font-semibold"></tbody>
                        </table>
                    </div>
                </div>

            </div>
        </main>
    </div>

    <!-- PAYMENT & CONFIRMATION MODAL -->
    <div id="payment-modal" class="fixed inset-0 bg-slate-900/60 backdrop-blur-sm z-50 hidden flex items-center justify-center p-4">
        <div class="bg-white w-full max-w-lg rounded-2xl shadow-xl border border-slate-100 p-6 space-y-5 relative">
            <button onclick="closePaymentModal()" class="absolute top-4 right-4 text-slate-400 hover:text-slate-600">
                <i class="fa-solid fa-xmark text-base"></i>
            </button>

            <h2 class="text-base font-bold text-slate-900 flex items-center gap-2 border-b border-slate-100 pb-3">
                <i class="fa-solid fa-cash-register text-[#046a38]"></i> Record Payment for <span id="modal-guest-name" class="text-[#046a38]"></span>
            </h2>

            <form onsubmit="submitPaymentViaAjax(event)" class="space-y-4">
                <input type="hidden" name="booking_id" id="modal-booking-id">
                
                <div class="bg-slate-50 p-3 rounded-xl border border-slate-200 grid grid-cols-2 gap-2 text-xs">
                    <div><span class="text-slate-400 block font-normal">Adult Portion:</span> <strong id="modal-display-adult-portion" class="text-slate-700"></strong></div>
                    <div><span class="text-slate-400 block font-normal">Child Portion:</span> <strong id="modal-display-child-portion" class="text-slate-700"></strong></div>
                    
                    <div id="modal-discount-wrapper" class="col-span-2 hidden">
                        <span class="text-rose-500 block font-bold text-[10px] uppercase">Contract Discount Applied: <strong id="modal-display-discount" class="text-rose-600 text-xs pl-2"></strong></span>
                    </div>
                    
                    <div class="col-span-2 border-t border-slate-200 my-1"></div>
                    
                    <div><span class="text-slate-400 block font-normal">Final Amount Due:</span> <strong id="modal-display-total" class="text-slate-900"></strong></div>
                    <div><span class="text-slate-400 block font-normal">Outstanding Balance:</span> <strong id="modal-display-balance" class="text-rose-600"></strong></div>
                </div>

                <div class="grid grid-cols-2 gap-3">
                    <div>
                        <label class="block text-[10px] font-bold text-slate-500 uppercase mb-1">Payment Amount *</label>
                        <input type="number" step="0.01" name="amount_paid" id="input-amount-paid" required class="w-full bg-white border border-slate-300 rounded-lg p-2.5 text-xs outline-none focus:ring-2 focus:ring-emerald-500 font-bold">
                    </div>
                    <div>
                        <label class="block text-[10px] font-bold text-slate-500 uppercase mb-1">Currency *</label>
                        <select name="currency" id="input-currency" class="w-full bg-slate-50 border border-slate-300 rounded-lg p-2.5 text-xs font-bold text-slate-600 cursor-not-allowed" style="pointer-events: none;" readonly>
                            <option value="KES">Kenya Shillings (KES)</option>
                            <option value="USD">US Dollars (USD)</option>
                        </select>
                    </div>
                </div>

                <div class="grid grid-cols-2 gap-3">
                    <div>
                        <label class="block text-[10px] font-bold text-slate-500 uppercase mb-1">Payment Method *</label>
                        <select name="payment_method" id="input-payment-method" onchange="toggleReferenceField()" class="w-full bg-white border border-slate-300 rounded-lg p-2.5 text-xs outline-none focus:ring-2 focus:ring-emerald-500 font-bold">
                            <option value="Cash">Cash</option>
                            <option value="M-Pesa">M-Pesa</option>
                            <option value="Bank Transfer">Bank Transfer</option>
                            <option value="Credit Card">Credit Card</option>
                        </select>
                    </div>
                    <div id="reference-wrapper">
                        <label class="block text-[10px] font-bold text-slate-500 uppercase mb-1" id="label-ref">Transaction Reference *</label>
                        <input type="text" name="reference_no" id="input-reference-no" placeholder="M-Pesa Code / Bank Ref" class="w-full bg-white border border-slate-300 rounded-lg p-2.5 text-xs outline-none focus:ring-2 focus:ring-emerald-500">
                    </div>
                </div>

                <div class="flex justify-end gap-2 pt-2 border-t border-slate-100">
                    <button type="button" onclick="closePaymentModal()" class="px-4 py-2 text-xs font-bold text-slate-500 border border-slate-200 rounded-xl hover:bg-slate-50">Cancel</button>
                    <button type="submit" class="bg-[#046a38] hover:bg-[#03542c] text-white font-bold py-2 px-5 rounded-xl text-xs transition">Submit Payment</button>
                </div>
            </form>
        </div>
    </div>

    <!-- RESERVATION CAPTURE MODAL -->
    <div id="reservation-modal-backdrop" class="fixed inset-0 bg-slate-900/60 backdrop-blur-sm z-50 hidden flex items-center justify-center p-4 overflow-y-auto">
        <div class="bg-white w-full max-w-4xl rounded-2xl shadow-xl border border-slate-100 p-6 space-y-5 max-h-[90vh] flex flex-col relative">
            <button onclick="closeReservationModal()" class="absolute top-4 right-4 text-slate-400 hover:text-slate-600 text-sm z-10"><i class="fa-solid fa-xmark text-lg"></i></button>
            
            <div class="flex justify-between items-center border-b border-slate-100 pb-3 shrink-0">
                <h2 id="modal-terminal-title" class="text-base font-bold text-[#0f172a] flex items-center gap-2"><i class="fa-solid fa-calendar-plus text-[#046a38]"></i> New Reservation Terminal</h2>
                <input type="hidden" id="editing-target-reservation-id" value="">
            </div>

            <div class="shrink-0">
                <label class="block text-[10px] font-bold text-slate-500 uppercase tracking-wider mb-2">Booking Source *</label>
                <div class="grid grid-cols-2 gap-3">
                    <button type="button" id="src-direct-btn" onclick="setBookingSource('direct')" class="py-2.5 px-4 rounded-xl border-2 font-semibold text-xs flex items-center justify-center gap-2 transition-all border-[#046a38] bg-emerald-50/50 text-[#046a38]"><i class="fa-solid fa-user"></i> Direct Client</button>
                    <button type="button" id="src-agency-btn" onclick="setBookingSource('agency')" class="py-2.5 px-4 rounded-xl border-2 font-semibold text-xs flex items-center justify-center gap-2 transition-all border-slate-200 text-slate-600 hover:bg-slate-50"><i class="fa-solid fa-briefcase"></i> Travel Agency</button>
                </div>
            </div>

            <div id="agency-fields-wrapper" class="hidden grid grid-cols-1 sm:grid-cols-2 gap-4 bg-slate-50 p-4 rounded-xl border border-slate-200 shrink-0">
                <div>
                    <label class="block text-[10px] font-bold text-slate-600 mb-1">Agency Name *</label>
                    <input type="text" id="input-agency-name" placeholder="e.g., Perfect Safaris" class="w-full bg-white border border-slate-300 rounded-lg p-2 text-xs outline-none focus:ring-2 focus:ring-emerald-500">
                </div>
                <div>
                    <label class="block text-[10px] font-bold text-slate-600 mb-1">Booking Officer *</label>
                    <input type="text" id="input-booking-officer" placeholder="Agent rep name" class="w-full bg-white border border-slate-300 rounded-lg p-2 text-xs outline-none focus:ring-2 focus:ring-emerald-500">
                </div>
            </div>

            <div class="space-y-2 flex-1 min-h-0 flex flex-col">
                <div class="flex justify-between items-center shrink-0">
                    <label class="text-[10px] font-bold text-slate-500 uppercase tracking-wider">Room & Guest Line Breakdown</label>
                    <button type="button" onclick="addNewAllocationRowRow()" class="text-[10px] bg-emerald-50 hover:bg-emerald-100 text-[#046a38] border border-emerald-200 font-bold py-1 px-2.5 rounded-lg transition flex items-center gap-1"><i class="fa-solid fa-plus"></i> Add Individual Guest/Room Line</button>
                </div>
                <div id="dynamic-allocation-rows-container" class="space-y-3 flex-1 overflow-y-auto pr-2 max-h-[380px]"></div>
            </div>

            <div class="flex justify-end gap-3 pt-1 border-t border-slate-100 shrink-0">
                <button type="button" onclick="closeReservationModal()" class="px-4 py-2.5 text-xs font-bold text-slate-500 border border-slate-200 rounded-xl hover:bg-slate-50">Cancel</button>
                <button type="button" onclick="processAndValidateFormSubmission()" class="bg-[#046a38] hover:bg-[#03542c] text-white font-black tracking-wide py-3 px-6 rounded-xl text-sm shadow-md transition flex items-center gap-2"><i class="fa-solid fa-save"></i> Save Reservation Entry</button>
            </div>
        </div>
    </div>

    <?php include 'Includes/footer.php'; ?>
</body>
</html>