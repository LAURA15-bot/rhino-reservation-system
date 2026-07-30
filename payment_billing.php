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
    <title>Payment & Billing - Rhino Camp</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <script>
        tailwind.config = { darkMode: 'class', }
    </script>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/html2pdf.js/0.10.1/html2pdf.bundle.min.js"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    
    <!-- Bumped version to v=3 to clear cache and load new pagination logic -->
    <script src="js/payment_billing.js?v=3" defer></script>
    
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
                
                <div class="shrink-0 space-y-6">
                    
                    <!-- Premium Section Header -->
                    <div class="bg-white dark:bg-slate-800 p-5 rounded-2xl custom-shadow border border-slate-100 dark:border-slate-700 flex flex-col sm:flex-row justify-between items-center gap-4 transition-colors duration-300">
                        <div class="flex items-center gap-3">
                            <div class="w-10 h-10 rounded-xl bg-emerald-50 dark:bg-emerald-900/30 text-[#046a38] dark:text-emerald-400 flex items-center justify-center shadow-inner shrink-0">
                                <i class="fa-solid fa-receipt text-lg"></i>
                            </div>
                            <div>
                                <h1 class="text-sm font-mono font-bold tracking-widest text-slate-900 dark:text-white uppercase">Payment & Billing Management</h1>
                                <p class="text-[10px] font-bold text-slate-400 dark:text-slate-500 uppercase tracking-widest mt-0.5">Manage guest billing accounts and multi-currency transactions</p>
                            </div>
                        </div>
                        <div class="flex items-center gap-2">
                            <span class="text-xs bg-emerald-50 dark:bg-emerald-900/30 text-[#046a38] dark:text-emerald-400 font-bold px-3 py-1.5 rounded-xl border border-emerald-100 dark:border-emerald-800 transition-colors duration-300">
                                <i class="fa-solid fa-shield-halved mr-1"></i> Active Financial Ledger
                            </span>
                        </div>
                    </div>

                    <!-- Filters Section -->
                    <div class="bg-white dark:bg-slate-800 p-4 rounded-2xl custom-shadow border border-slate-100 dark:border-slate-700 flex flex-wrap items-center justify-between gap-4 transition-colors duration-300">
                        
                        <form id="searchForm" onsubmit="handleSearchSubmit(event)" class="flex items-center gap-2 w-full md:w-auto flex-1 max-w-md">
                            <div class="relative flex-1 flex items-center border border-slate-200 dark:border-slate-600 rounded-xl px-3 bg-slate-50 dark:bg-slate-900 transition-colors duration-300">
                                <i class="fa-solid fa-magnifying-glass text-slate-400 dark:text-slate-500 mr-2 text-xs"></i>
                                <input type="text" id="searchInput" placeholder="Search by Guest Name, Booking ID..." class="bg-transparent py-2.5 w-full text-xs text-slate-700 dark:text-slate-300 outline-none">
                            </div>
                            <button type="submit" class="bg-[#046a38] hover:bg-[#03542c] text-white font-bold py-2.5 px-5 rounded-xl text-xs transition shadow-sm">Search</button>
                            <button type="button" onclick="resetSearch()" class="bg-slate-100 dark:bg-slate-700 hover:bg-slate-200 dark:hover:bg-slate-600 text-slate-600 dark:text-slate-300 font-bold py-2.5 px-4 rounded-xl text-xs transition-colors duration-300">Reset</button>
                        </form>

                        <div class="flex items-center gap-3">
                            <!-- Per Page Filter -->
                            <div class="flex items-center gap-2">
                                <label class="text-[10px] font-bold text-slate-500 dark:text-slate-400 uppercase tracking-wider hidden sm:block">Per Page:</label>
                                <select id="rowsPerPageFilter" onchange="changeRowsPerPage()" class="bg-slate-50 dark:bg-slate-900 border border-slate-200 dark:border-slate-600 text-slate-700 dark:text-slate-300 text-xs font-bold rounded-lg block p-2 cursor-pointer outline-none transition-colors duration-300">
                                    <option value="10">10</option>
                                    <option value="20">20</option>
                                    <option value="50">50</option>
                                    <option value="all">All</option>
                                </select>
                            </div>
                            
                            <!-- Date Filter -->
                            <div class="flex items-center gap-2">
                                <label class="text-[10px] font-bold text-slate-500 dark:text-slate-400 uppercase tracking-wider hidden sm:block">Timeframe:</label>
                                <select id="dateFilter" class="bg-slate-50 dark:bg-slate-900 border border-slate-200 dark:border-slate-600 text-slate-700 dark:text-slate-300 text-xs font-bold rounded-lg block p-2 cursor-pointer outline-none transition-colors duration-300" onchange="filterBillingTable();">
                                    <option value="all">All Available History</option>
                                    <option value="today">Today</option>
                                    <option value="7">Past 7 Days</option>
                                    <option value="30">Past 30 Days</option>
                                </select>
                            </div>
                            
                            <!-- Status Filter -->
                            <div class="flex items-center gap-2">
                                <label class="text-[10px] font-bold text-slate-500 dark:text-slate-400 uppercase tracking-wider hidden sm:block">Status:</label>
                                <select id="statusFilter" class="bg-slate-50 dark:bg-slate-900 border border-slate-200 dark:border-slate-600 text-slate-700 dark:text-slate-300 text-xs font-bold rounded-lg block p-2 cursor-pointer outline-none transition-colors duration-300" onchange="filterBillingTable();">
                                    <option value="All Statuses">All Statuses</option>
                                    <option value="Paid in Full">Paid in Full</option>
                                    <option value="Partially Paid">Partially Paid</option>
                                    <option value="Outstanding">Outstanding</option>
                                    <option value="Checked Out">Checked Out</option>
                                </select>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Ledger Table Container (H-Auto to hug rows perfectly) -->
                <div class="bg-white dark:bg-slate-800 rounded-2xl custom-shadow border border-slate-100 dark:border-slate-700 overflow-hidden transition-colors duration-300 h-auto">
                    <div class="p-4 border-b border-slate-100 dark:border-slate-700 bg-white dark:bg-slate-800 flex justify-between items-center transition-colors duration-300">
                        <h3 class="text-xs font-bold tracking-wide uppercase text-slate-900 dark:text-white flex items-center gap-2">
                            <i class="fa-solid fa-file-invoice-dollar text-[#046a38] dark:text-emerald-500"></i> Guest Financial Ledgers
                        </h3>
                        <span id="records-counter-badge" class="text-[11px] bg-slate-100 dark:bg-slate-900 text-slate-600 dark:text-slate-400 px-2.5 py-1 font-bold rounded-lg border border-slate-200 dark:border-slate-700 transition-colors duration-300">0 Records Found</span>
                    </div>

                    <div class="overflow-x-auto w-full">
                        <table class="w-full text-left border-collapse">
                            <thead class="bg-slate-50 dark:bg-slate-800 border-b border-slate-200 dark:border-slate-700 text-[10px] font-bold tracking-wider text-slate-500 dark:text-slate-400 uppercase transition-colors duration-300">
                                <tr>
                                    <th class="p-4">ID & Guest</th>
                                    <th class="p-4">Booking Source</th>
                                    <th class="p-4 text-center">Stay Duration</th>
                                    <th class="p-4 text-center">Room & Pax</th>
                                    <th class="p-4 text-right">Total Amount</th>
                                    <th class="p-4 text-right">Paid</th>
                                    <th class="p-4 text-right">Balance</th>
                                    <th class="p-4 text-center">Status</th>
                                    <th class="p-4 text-center">Actions</th>
                                </tr>
                            </thead>
                            <tbody id="billingTableBody" class="divide-y divide-slate-100 dark:divide-slate-700/50 text-xs text-slate-700 dark:text-slate-300 font-semibold bg-white dark:bg-slate-900 transition-colors duration-300">
                                <!-- Populated via API -->
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

    <!-- PAYMENT MODAL -->
    <div id="payment-modal" class="fixed inset-0 bg-slate-900/60 dark:bg-slate-900/80 backdrop-blur-sm z-50 hidden items-center justify-center p-4 transition-colors duration-300">
        <div class="bg-white dark:bg-slate-900 w-full max-w-lg rounded-2xl shadow-xl border border-slate-100 dark:border-slate-700 p-6 space-y-5 relative animate-fadeIn transition-colors duration-300">
            <button onclick="closePaymentModal()" class="absolute top-4 right-4 text-slate-400 hover:text-slate-600 dark:hover:text-slate-200"><i class="fa-solid fa-xmark text-base"></i></button>
            <h2 class="text-base font-bold text-slate-900 dark:text-white flex items-center gap-2 border-b border-slate-100 dark:border-slate-700 pb-3 transition-colors">
                <i class="fa-solid fa-cash-register text-[#046a38] dark:text-emerald-500"></i> Record Payment for <span id="modal-guest-name" class="text-[#046a38] dark:text-emerald-500"></span>
            </h2>
            <form onsubmit="submitPaymentViaAjax(event)" class="space-y-4">
                <input type="hidden" name="booking_id" id="modal-booking-id">
                <div class="bg-slate-50 dark:bg-slate-800 p-3 rounded-xl border border-slate-200 dark:border-slate-700 grid grid-cols-2 gap-2 text-xs transition-colors duration-300">
                    <div><span class="text-slate-400 dark:text-slate-500 block">Adult Portion:</span> <strong id="modal-display-adult-portion" class="text-slate-700 dark:text-slate-300"></strong></div>
                    <div><span class="text-slate-400 dark:text-slate-500 block">Child Portion:</span> <strong id="modal-display-child-portion" class="text-slate-700 dark:text-slate-300"></strong></div>
                    <div id="modal-discount-wrapper" class="col-span-2 hidden"><span class="text-rose-500 dark:text-rose-400 block font-bold text-[10px] uppercase">Discount: <strong id="modal-display-discount"></strong></span></div>
                    <div class="col-span-2 border-t border-slate-200 dark:border-slate-700 my-1"></div>
                    <div><span class="text-slate-400 dark:text-slate-500 block">Final Due:</span> <strong id="modal-display-total" class="text-slate-900 dark:text-white"></strong></div>
                    <div><span class="text-slate-400 dark:text-slate-500 block">Balance:</span> <strong id="modal-display-balance" class="text-rose-600 dark:text-rose-400"></strong></div>
                </div>
                <div class="grid grid-cols-2 gap-3">
                    <div><label class="block text-[10px] font-bold text-slate-500 dark:text-slate-400 uppercase mb-1">Amount *</label><input type="number" step="0.01" name="amount_paid" id="input-amount-paid" required class="w-full bg-white dark:bg-slate-800 border border-slate-300 dark:border-slate-600 text-slate-900 dark:text-white rounded-lg p-2.5 text-xs font-bold outline-none focus:ring-2 focus:ring-emerald-500 transition-colors"></div>
                    <div><label class="block text-[10px] font-bold text-slate-500 dark:text-slate-400 uppercase mb-1">Currency *</label><input type="text" id="input-currency" class="w-full bg-slate-100 dark:bg-slate-900 border border-slate-300 dark:border-slate-700 text-slate-600 dark:text-slate-500 rounded-lg p-2.5 text-xs font-bold transition-colors" readonly></div>
                </div>
                <div class="grid grid-cols-2 gap-3">
                    <div><label class="block text-[10px] font-bold text-slate-500 dark:text-slate-400 uppercase mb-1">Method *</label><select name="payment_method" id="input-payment-method" onchange="toggleReferenceField()" class="w-full bg-white dark:bg-slate-800 border border-slate-300 dark:border-slate-600 text-slate-900 dark:text-white rounded-lg p-2.5 text-xs font-bold outline-none focus:ring-2 focus:ring-emerald-500 transition-colors"><option value="Cash">Cash</option><option value="M-Pesa">M-Pesa</option><option value="Bank Transfer">Bank Transfer</option></select></div>
                    <div id="reference-wrapper"><label class="block text-[10px] font-bold text-slate-500 dark:text-slate-400 uppercase mb-1" id="label-ref">Reference *</label><input type="text" name="reference_no" id="input-reference-no" placeholder="Ref code" class="w-full bg-white dark:bg-slate-800 border border-slate-300 dark:border-slate-600 text-slate-900 dark:text-white rounded-lg p-2.5 text-xs outline-none focus:ring-2 focus:ring-emerald-500 transition-colors"></div>
                </div>
                <div class="flex justify-end gap-2 pt-2 border-t border-slate-100 dark:border-slate-700 transition-colors">
                    <button type="button" onclick="closePaymentModal()" class="px-4 py-2 text-xs font-bold text-slate-500 dark:text-slate-400 border border-slate-200 dark:border-slate-600 rounded-xl hover:bg-slate-50 dark:hover:bg-slate-800 transition-colors">Cancel</button>
                    <button type="submit" class="bg-[#046a38] text-white font-bold py-2 px-5 rounded-xl text-xs hover:bg-[#03542c] transition-colors">Submit</button>
                </div>
            </form>
        </div>
    </div>

    <!-- DOCUMENT MODAL (Dark Mode Support) -->
    <div id="document-modal" class="fixed inset-0 bg-slate-900/60 dark:bg-slate-900/80 backdrop-blur-sm z-50 hidden items-center justify-center p-4 transition-colors duration-300">
        <div class="bg-white dark:bg-slate-900 w-full max-w-md rounded-2xl shadow-xl border border-slate-100 dark:border-slate-700 p-6 space-y-4 relative animate-fadeIn transition-colors duration-300">
            <button onclick="closeDocumentModal()" class="absolute top-4 right-4 text-slate-400 hover:text-slate-600 dark:hover:text-slate-200"><i class="fa-solid fa-xmark text-base"></i></button>
            <h2 class="text-base font-bold text-slate-900 dark:text-white border-b border-slate-100 dark:border-slate-700 pb-3 transition-colors">Documents for <span id="doc-guest-name" class="text-[#046a38] dark:text-emerald-500"></span></h2>
            <div class="space-y-3">
                <button id="btn-generate-receipt" onclick="generatePDFReceipt()" class="w-full bg-emerald-600 hover:bg-emerald-700 text-white font-bold py-3 px-4 rounded-xl text-xs flex items-center justify-center gap-2 transition"><i class="fa-solid fa-file-pdf"></i> Generate Official PDF Receipt</button>
                <button onclick="generatePDFInvoice()" class="w-full bg-slate-900 dark:bg-slate-700 hover:bg-slate-800 dark:hover:bg-slate-600 text-white font-bold py-3 px-4 rounded-xl text-xs flex items-center justify-center gap-2 transition"><i class="fa-solid fa-file-invoice"></i> Generate Itemized Invoice</button>
            </div>
        </div>
    </div>

    <!-- HIDDEN PRINT AREA (Must remain Light Mode so PDFs generate on a white background) -->
    <div id="printable-document-area" class="hidden bg-white p-8 max-w-2xl mx-auto text-slate-800 font-sans text-xs">
        <div class="text-center border-b pb-6 mb-4 space-y-1">
            <h1 class="text-xl font-black uppercase text-[#046a38]">Rhino Tourist Camp</h1>
            <p class="text-[11px] text-slate-500">Official Financial Statement</p>
            <p id="doc-title-badge" class="text-xs font-bold text-slate-700 uppercase mt-2"></p>
        </div>
        <div class="grid grid-cols-2 gap-4 mb-6 bg-slate-50 p-4 rounded-xl border">
            <div><p><strong>Booking Ref:</strong> #<span id="p-book-id"></span></p><p><strong>Guest:</strong> <span id="p-guest-name"></span></p></div>
            <div><p><strong>Check-in:</strong> <span id="p-checkin"></span></p><p><strong>Check-out:</strong> <span id="p-checkout"></span></p></div>
        </div>
        <table class="w-full text-left border-collapse mb-6">
            <thead><tr class="border-b text-[10px] uppercase text-slate-500"><th class="py-2">Description</th><th class="py-2 text-center">Nights</th><th class="py-2 text-right">Amount (<span id="pdf-header-currency"></span>)</th></tr></thead>
            <tbody class="divide-y font-semibold">
                <tr><td class="py-3" id="p-desc-room">Room Charge</td><td class="py-3 text-center" id="p-nights-count">1</td><td class="py-3 text-right" id="p-original-total">0.00</td></tr>
                <tr id="tr-discount-row" class="text-rose-600 hidden"><td class="py-3">Discount</td><td class="py-3 text-center">-</td><td class="py-3 text-right" id="p-discount-amount">0.00</td></tr>
                <tr class="border-t font-bold"><td class="py-3">Total Due</td><td class="py-3 text-center">-</td><td class="py-3 text-right font-black" id="p-total-amount">0.00</td></tr>
                <tr class="text-emerald-700"><td class="py-3">Paid</td><td class="py-3 text-center">-</td><td class="py-3 text-right" id="p-total-paid">0.00</td></tr>
                <tr class="text-rose-600 font-black"><td class="py-3">Balance</td><td class="py-3 text-center">-</td><td class="py-3 text-right" id="p-balance-due">0.00</td></tr>
            </tbody>
        </table>
    </div>
</body>
</html>