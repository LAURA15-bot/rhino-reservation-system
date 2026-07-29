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
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/html2pdf.js/0.10.1/html2pdf.bundle.min.js"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    
    <script src="js/payment_billing.js" defer></script>
    
    <style>
        .custom-shadow { box-shadow: 0 4px 20px -2px rgba(0, 0, 0, 0.05); }
    </style>
</head>
<body class="bg-[#f8fafc] text-[#334155] font-sans antialiased min-h-screen overflow-hidden">
    
    <div class="flex h-screen w-screen overflow-hidden">
        
        <?php include 'Includes/sidebar.php'; ?>

        <main class="flex-1 flex flex-col h-full overflow-hidden bg-[#f8fafc]">
            
            <?php include 'Includes/header.php'; ?>

            <div class="flex-1 p-4 lg:p-6 space-y-6 flex flex-col min-h-0 overflow-hidden">
                
                <div class="shrink-0 space-y-6">
                    <div class="bg-white p-5 rounded-2xl custom-shadow border border-slate-100 flex flex-col sm:flex-row justify-between items-center gap-4">
                        <div>
                            <h1 class="text-lg font-black text-slate-900 flex items-center gap-2">
                                <i class="fa-solid fa-receipt text-[#046a38]"></i> Payment & Billing Management
                            </h1>
                            <p class="text-xs text-slate-400 mt-0.5">Manage guest billing accounts, record multi-currency transactions, and print verified financial documents.</p>
                        </div>
                        <div class="flex items-center gap-2">
                            <span class="text-xs bg-emerald-50 text-[#046a38] font-bold px-3 py-1.5 rounded-xl border border-emerald-100">
                                <i class="fa-solid fa-shield-halved mr-1"></i> Active Financial Ledger
                            </span>
                        </div>
                    </div>

                    <div class="bg-white p-4 rounded-2xl custom-shadow border border-slate-100 flex flex-wrap items-center justify-between gap-4">
                        <form id="searchForm" onsubmit="handleSearchSubmit(event)" class="flex items-center gap-2 w-full md:w-auto flex-1 max-w-md">
                            <div class="relative flex-1 flex items-center border border-slate-200 rounded-xl px-3 bg-slate-50">
                                <i class="fa-solid fa-magnifying-glass text-slate-400 mr-2 text-xs"></i>
                                <input type="text" id="searchInput" placeholder="Search by Guest Name, Booking ID..." class="bg-transparent py-2.5 w-full text-xs text-slate-700 outline-none">
                            </div>
                            <button type="submit" class="bg-[#046a38] hover:bg-[#03542c] text-white font-bold py-2.5 px-5 rounded-xl text-xs transition shadow-sm">Search</button>
                            <button type="button" onclick="resetSearch()" class="bg-slate-100 hover:bg-slate-200 text-slate-600 font-bold py-2.5 px-4 rounded-xl text-xs transition">Reset</button>
                        </form>

                        <div class="flex items-center gap-3">
                            <div class="flex items-center gap-2">
                                <label class="text-[10px] font-bold text-slate-500 uppercase tracking-wider hidden sm:block">Timeframe:</label>
                                <select id="dateFilter" class="bg-slate-50 border border-slate-200 text-slate-700 text-xs font-bold rounded-lg block p-2 cursor-pointer outline-none focus:ring-1 focus:ring-[#046a38]" onchange="filterBillingTable();">
                                    <option value="all">All Available History</option>
                                    <option value="today">Today</option>
                                    <option value="7">Past 7 Days</option>
                                    <option value="30">Past 30 Days</option>
                                </select>
                            </div>
                            
                            <div class="flex items-center gap-2">
                                <label class="text-[10px] font-bold text-slate-500 uppercase tracking-wider hidden sm:block">Status:</label>
                                <select id="statusFilter" class="bg-slate-50 border border-slate-200 text-slate-700 text-xs font-bold rounded-lg block p-2 cursor-pointer outline-none focus:ring-1 focus:ring-[#046a38]" onchange="filterBillingTable();">
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

                <div class="flex-1 bg-white rounded-2xl custom-shadow border border-slate-100 flex flex-col min-h-0 overflow-hidden">
                    <div class="p-4 border-b border-slate-100 bg-white flex justify-between items-center shrink-0">
                        <h3 class="text-xs font-bold tracking-wide uppercase text-slate-900 flex items-center gap-2">
                            <i class="fa-solid fa-file-invoice-dollar text-[#046a38]"></i> Guest Financial Ledgers
                        </h3>
                        <span id="records-counter-badge" class="text-[11px] bg-slate-100 text-slate-600 px-2.5 py-1 font-bold rounded-lg border border-slate-200">0 Records Found</span>
                    </div>

                    <div class="flex-1 overflow-y-auto">
                        <table class="w-full text-left border-collapse">
                            <thead class="sticky top-0 z-10 bg-slate-50 shadow-sm border-b border-slate-200 text-[10px] font-bold tracking-wider text-slate-500 uppercase">
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
                            <tbody id="billingTableBody" class="divide-y divide-slate-100 text-xs text-slate-700 font-semibold bg-white">
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>

            <?php include 'Includes/footer.php'; ?>

        </main>
    </div>

    <!-- PAYMENT MODAL -->
    <div id="payment-modal" class="fixed inset-0 bg-slate-900/60 backdrop-blur-sm z-50 hidden items-center justify-center p-4">
        <div class="bg-white w-full max-w-lg rounded-2xl shadow-xl border border-slate-100 p-6 space-y-5 relative animate-fadeIn">
            <button onclick="closePaymentModal()" class="absolute top-4 right-4 text-slate-400 hover:text-slate-600"><i class="fa-solid fa-xmark text-base"></i></button>
            <h2 class="text-base font-bold text-slate-900 flex items-center gap-2 border-b border-slate-100 pb-3">
                <i class="fa-solid fa-cash-register text-[#046a38]"></i> Record Payment for <span id="modal-guest-name" class="text-[#046a38]"></span>
            </h2>
            <form onsubmit="submitPaymentViaAjax(event)" class="space-y-4">
                <input type="hidden" name="booking_id" id="modal-booking-id">
                <div class="bg-slate-50 p-3 rounded-xl border border-slate-200 grid grid-cols-2 gap-2 text-xs">
                    <div><span class="text-slate-400 block">Adult Portion:</span> <strong id="modal-display-adult-portion"></strong></div>
                    <div><span class="text-slate-400 block">Child Portion:</span> <strong id="modal-display-child-portion"></strong></div>
                    <div id="modal-discount-wrapper" class="col-span-2 hidden"><span class="text-rose-500 block font-bold text-[10px] uppercase">Discount: <strong id="modal-display-discount"></strong></span></div>
                    <div class="col-span-2 border-t border-slate-200 my-1"></div>
                    <div><span class="text-slate-400 block">Final Due:</span> <strong id="modal-display-total" class="text-slate-900"></strong></div>
                    <div><span class="text-slate-400 block">Balance:</span> <strong id="modal-display-balance" class="text-rose-600"></strong></div>
                </div>
                <div class="grid grid-cols-2 gap-3">
                    <div><label class="block text-[10px] font-bold text-slate-500 uppercase mb-1">Amount *</label><input type="number" step="0.01" name="amount_paid" id="input-amount-paid" required class="w-full border rounded-lg p-2.5 text-xs font-bold"></div>
                    <div><label class="block text-[10px] font-bold text-slate-500 uppercase mb-1">Currency *</label><input type="text" id="input-currency" class="w-full bg-slate-100 border rounded-lg p-2.5 text-xs font-bold" readonly></div>
                </div>
                <div class="grid grid-cols-2 gap-3">
                    <div><label class="block text-[10px] font-bold text-slate-500 uppercase mb-1">Method *</label><select name="payment_method" id="input-payment-method" onchange="toggleReferenceField()" class="w-full border rounded-lg p-2.5 text-xs font-bold"><option value="Cash">Cash</option><option value="M-Pesa">M-Pesa</option><option value="Bank Transfer">Bank Transfer</option></select></div>
                    <div id="reference-wrapper"><label class="block text-[10px] font-bold text-slate-500 uppercase mb-1" id="label-ref">Reference *</label><input type="text" name="reference_no" id="input-reference-no" placeholder="Ref code" class="w-full border rounded-lg p-2.5 text-xs"></div>
                </div>
                <div class="flex justify-end gap-2 pt-2 border-t"><button type="button" onclick="closePaymentModal()" class="px-4 py-2 text-xs font-bold text-slate-500 border rounded-xl">Cancel</button><button type="submit" class="bg-[#046a38] text-white font-bold py-2 px-5 rounded-xl text-xs">Submit</button></div>
            </form>
        </div>
    </div>

    <!-- DOCUMENT MODAL & HIDDEN PRINT AREA -->
    <div id="document-modal" class="fixed inset-0 bg-slate-900/60 backdrop-blur-sm z-50 hidden items-center justify-center p-4">
        <div class="bg-white w-full max-w-md rounded-2xl shadow-xl border border-slate-100 p-6 space-y-4 relative animate-fadeIn">
            <button onclick="closeDocumentModal()" class="absolute top-4 right-4 text-slate-400 hover:text-slate-600"><i class="fa-solid fa-xmark text-base"></i></button>
            <h2 class="text-base font-bold text-slate-900 border-b pb-3">Documents for <span id="doc-guest-name" class="text-[#046a38]"></span></h2>
            <div class="space-y-3">
                <button id="btn-generate-receipt" onclick="generatePDFReceipt()" class="w-full bg-emerald-600 hover:bg-emerald-700 text-white font-bold py-3 px-4 rounded-xl text-xs flex items-center justify-center gap-2 transition"><i class="fa-solid fa-file-pdf"></i> Generate Official PDF Receipt</button>
                <button onclick="generatePDFInvoice()" class="w-full bg-slate-900 hover:bg-slate-800 text-white font-bold py-3 px-4 rounded-xl text-xs flex items-center justify-center gap-2 transition"><i class="fa-solid fa-file-invoice"></i> Generate Itemized Invoice</button>
            </div>
        </div>
    </div>

    <!-- RESTORED HTML IDs IN THE PRINT AREA -->
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
    <?php include 'Includes/footer.php'; ?>

</body>
</html>