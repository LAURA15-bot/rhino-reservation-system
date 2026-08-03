<?php
session_start();
if (!isset($_SESSION['logged_in'])) {
    header("Location: login.php");
    exit;
}
$current_page = basename($_SERVER['PHP_SELF']);

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
    <title>Payment & Billing - Rhino Camp</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <script>
        tailwind.config = { darkMode: 'class', }
    </script>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/html2pdf.js/0.10.1/html2pdf.bundle.min.js"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    
    <!-- Script Cache Bust -->
    <script src="js/payment_billing.js?v=10" defer></script>
    
    <style>
        :root {
            --theme-color: <?php echo $primaryColor; ?>;
            --theme-color-focus: <?php echo $primaryColor; ?>33;
        }
        .custom-shadow { box-shadow: 0 4px 20px -2px rgba(0, 0, 0, 0.05); }
        .theme-btn { background-color: var(--theme-color); transition: filter 0.2s; }
        .theme-btn:hover { filter: brightness(85%); }
        .theme-text { color: var(--theme-color); }
        .theme-border { border-color: var(--theme-color); }
        .theme-focus:focus { outline: none; border-color: var(--theme-color); box-shadow: 0 0 0 3px var(--theme-color-focus); }
    </style>
</head>
<body data-primary-color="<?php echo $primaryColor; ?>" class="bg-[#f8fafc] dark:bg-slate-900 text-[#334155] dark:text-slate-200 font-sans antialiased min-h-screen overflow-hidden transition-colors duration-300">
    
    <div class="flex h-screen w-screen overflow-hidden">
        
        <?php include 'Includes/sidebar.php'; ?>

        <main class="flex-1 overflow-y-auto h-full bg-[#f8fafc] dark:bg-slate-900 transition-colors duration-300 flex flex-col">
            
            <?php include 'Includes/header.php'; ?>

            <div class="p-4 lg:p-6 space-y-6 max-w-[1600px] mx-auto w-full flex-1">
                
                <div class="shrink-0 space-y-6">
                    
                    <div class="bg-white dark:bg-slate-800 p-5 rounded-2xl custom-shadow border border-slate-100 dark:border-slate-700 flex flex-col sm:flex-row justify-between items-center gap-4 transition-colors duration-300">
                        <div class="flex items-center gap-3">
                            <div class="w-10 h-10 rounded-xl bg-slate-100 dark:bg-slate-900/50 theme-text flex items-center justify-center shadow-inner shrink-0 border border-slate-200 dark:border-slate-700">
                                <i class="fa-solid fa-receipt text-lg"></i>
                            </div>
                            <div>
                                <h1 class="text-sm font-mono font-bold tracking-widest text-slate-900 dark:text-white uppercase">Payment & Billing Management</h1>
                                <p class="text-[10px] font-bold text-slate-400 dark:text-slate-500 uppercase tracking-widest mt-0.5">Manage guest billing accounts and multi-currency transactions</p>
                            </div>
                        </div>
                        <div class="flex items-center gap-2">
                            <span class="text-xs bg-slate-50 dark:bg-slate-900/50 theme-text font-bold px-3 py-1.5 rounded-xl border border-slate-200 dark:border-slate-700 transition-colors duration-300">
                                <i class="fa-solid fa-shield-halved mr-1"></i> Active Financial Ledger
                            </span>
                        </div>
                    </div>

                    <!-- SEARCH & FILTERS SECTION (Restyled to match screenshot design) -->
                    <div class="bg-white dark:bg-slate-800 p-4 rounded-2xl custom-shadow border border-slate-100 dark:border-slate-700 flex flex-wrap items-end justify-between gap-4 transition-colors duration-300">
                        
                        <div class="flex flex-wrap items-end gap-4 flex-1 w-full">
                            
                            <!-- Enlarged Live Search Bar matching screenshot design -->
                            <div class="w-full lg:w-1/2 min-w-[300px] xl:max-w-4xl shrink-0">
                                <label class="block text-[10px] font-bold text-slate-500 dark:text-slate-400 uppercase tracking-wider mb-2">Live Search</label>
                                <div class="relative flex items-center border border-slate-200 dark:border-slate-600 rounded-xl px-4 bg-slate-50 dark:bg-slate-900 transition-all duration-300 shadow-sm focus-within:ring-2 focus-within:border-transparent" style="--tw-ring-color: <?php echo $primaryColor; ?>;">
                                    <i class="fa-solid fa-magnifying-glass text-slate-400 dark:text-slate-500 mr-3 text-sm"></i>
                                    <input type="text" id="searchInput" onkeyup="filterBillingTable()" placeholder="Search guest, agency, or officer..." class="bg-transparent py-3.5 w-full text-sm font-bold text-slate-800 dark:text-slate-200 outline-none placeholder-slate-400">
                                    <button type="button" onclick="resetSearch()" title="Reset Search" class="text-slate-400 hover:text-slate-600 dark:hover:text-slate-200 ml-2 transition-colors">
                                        <i class="fa-solid fa-rotate-right text-xs"></i>
                                    </button>
                                </div>
                            </div>

                            <!-- Per Page Filter -->
                            <div>
                                <label class="block text-[10px] font-bold text-slate-500 dark:text-slate-400 uppercase tracking-wider mb-2">Per Page</label>
                                <select id="rowsPerPageFilter" onchange="changeRowsPerPage()" class="bg-slate-50 dark:bg-slate-900 border border-slate-200 dark:border-slate-600 text-slate-700 dark:text-slate-300 text-xs font-bold rounded-lg theme-focus block w-24 p-3.5 cursor-pointer transition-colors duration-300">
                                    <option value="10">10</option>
                                    <option value="20">20</option>
                                    <option value="50">50</option>
                                    <option value="all">All</option>
                                </select>
                            </div>

                            <!-- Timeframe Filter -->
                            <div>
                                <label class="block text-[10px] font-bold text-slate-500 dark:text-slate-400 uppercase tracking-wider mb-2">Timeframe</label>
                                <select id="dateFilter" class="bg-slate-50 dark:bg-slate-900 border border-slate-200 dark:border-slate-600 text-slate-700 dark:text-slate-300 text-xs font-bold rounded-lg theme-focus block w-48 p-3.5 cursor-pointer transition-colors duration-300" onchange="filterBillingTable();">
                                    <option value="all">All Available History</option>
                                    <option value="today">Today</option>
                                    <option value="7">Past 7 Days</option>
                                    <option value="30">Past 30 Days</option>
                                </select>
                            </div>
                        </div>

                        <!-- Registration Status Filter -->
                        <div class="w-full sm:w-auto">
                            <label class="block text-[10px] font-bold text-slate-500 dark:text-slate-400 uppercase tracking-wider mb-2">Registration Status</label>
                            <select id="statusFilter" class="bg-slate-50 dark:bg-slate-900 border border-slate-200 dark:border-slate-600 text-slate-700 dark:text-slate-300 text-xs font-bold rounded-lg theme-focus block w-full sm:w-48 p-3.5 cursor-pointer transition-colors duration-300" onchange="filterBillingTable();">
                                <option value="All Statuses">All Statuses</option>
                                <option value="Paid in Full">Paid in Full</option>
                                <option value="Partially Paid">Partially Paid</option>
                                <option value="Outstanding">Outstanding</option>
                                <option value="Checked Out">Checked Out</option>
                            </select>
                        </div>
                    </div>
                </div>

                <div class="bg-white dark:bg-slate-800 rounded-2xl custom-shadow border border-slate-100 dark:border-slate-700 overflow-hidden transition-colors duration-300 h-auto">
                    <div class="p-4 border-b border-slate-100 dark:border-slate-700 bg-white dark:bg-slate-800 flex justify-between items-center transition-colors duration-300">
                        <h3 class="text-xs font-bold tracking-wide uppercase text-slate-900 dark:text-white flex items-center gap-2">
                            <i class="fa-solid fa-file-invoice-dollar theme-text"></i> Guest Financial Ledgers
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

    <!-- PAYMENT MODAL (Enlarged to max-w-xl) -->
    <div id="payment-modal" class="fixed inset-0 bg-slate-900/60 dark:bg-slate-900/80 backdrop-blur-sm z-50 hidden items-center justify-center p-4 transition-colors duration-300">
        <div class="bg-white dark:bg-slate-900 w-full max-w-xl rounded-2xl shadow-xl border border-slate-100 dark:border-slate-700 p-6 space-y-5 relative animate-fadeIn transition-colors duration-300">
            <button onclick="closePaymentModal()" class="absolute top-4 right-4 text-slate-400 hover:text-slate-600 dark:hover:text-slate-200"><i class="fa-solid fa-xmark text-base"></i></button>
            <h2 class="text-base font-bold text-slate-900 dark:text-white flex items-center gap-2 border-b border-slate-100 dark:border-slate-700 pb-3 transition-colors">
                <i class="fa-solid fa-cash-register theme-text"></i> Record Payment for <span id="modal-guest-name" class="theme-text"></span>
            </h2>
            <form onsubmit="submitPaymentViaAjax(event)" class="space-y-4">
                <input type="hidden" name="booking_id" id="modal-booking-id">
                <div class="bg-slate-50 dark:bg-slate-800 p-4 rounded-xl border border-slate-200 dark:border-slate-700 grid grid-cols-2 gap-3 text-xs transition-colors duration-300">
                    <div><span class="text-slate-400 dark:text-slate-500 block">Adult Portion:</span> <strong id="modal-display-adult-portion" class="text-slate-700 dark:text-slate-300"></strong></div>
                    <div><span class="text-slate-400 dark:text-slate-500 block">Child Portion:</span> <strong id="modal-display-child-portion" class="text-slate-700 dark:text-slate-300"></strong></div>
                    <div id="modal-discount-wrapper" class="col-span-2 hidden"><span class="text-rose-500 dark:text-rose-400 block font-bold text-[10px] uppercase">Discount: <strong id="modal-display-discount"></strong></span></div>
                    <div class="col-span-2 border-t border-slate-200 dark:border-slate-700 my-1"></div>
                    <div><span class="text-slate-400 dark:text-slate-500 block">Final Due:</span> <strong id="modal-display-total" class="text-slate-900 dark:text-white"></strong></div>
                    <div><span class="text-slate-400 dark:text-slate-500 block">Balance:</span> <strong id="modal-display-balance" class="text-rose-600 dark:text-rose-400"></strong></div>
                </div>
                <div class="grid grid-cols-2 gap-3">
                    <div>
                        <label class="block text-[10px] font-bold text-slate-500 dark:text-slate-400 uppercase mb-1">Amount *</label>
                        <input type="number" step="0.01" name="amount_paid" id="input-amount-paid" required class="theme-focus w-full bg-white dark:bg-slate-800 border border-slate-300 dark:border-slate-600 text-slate-900 dark:text-white rounded-lg p-3 text-xs font-bold transition-colors">
                    </div>
                    <div>
                        <label class="block text-[10px] font-bold text-slate-500 dark:text-slate-400 uppercase mb-1">Currency *</label>
                        <input type="text" id="input-currency" class="w-full bg-slate-100 dark:bg-slate-900 border border-slate-300 dark:border-slate-700 text-slate-600 dark:text-slate-500 rounded-lg p-3 text-xs font-bold transition-colors" readonly>
                    </div>
                </div>
                <div class="grid grid-cols-2 gap-3">
                    <div>
                        <label class="block text-[10px] font-bold text-slate-500 dark:text-slate-400 uppercase mb-1">Method *</label>
                        <select name="payment_method" id="input-payment-method" onchange="toggleReferenceField()" class="theme-focus w-full bg-white dark:bg-slate-800 border border-slate-300 dark:border-slate-600 text-slate-900 dark:text-white rounded-lg p-3 text-xs font-bold transition-colors"><option value="Cash">Cash</option><option value="M-Pesa">M-Pesa</option><option value="Bank Transfer">Bank Transfer</option></select>
                    </div>
                    <div id="reference-wrapper">
                        <label class="block text-[10px] font-bold text-slate-500 dark:text-slate-400 uppercase mb-1" id="label-ref">Reference *</label>
                        <input type="text" name="reference_no" id="input-reference-no" placeholder="Unique Ref Code" class="theme-focus w-full bg-white dark:bg-slate-800 border border-slate-300 dark:border-slate-600 text-slate-900 dark:text-white rounded-lg p-3 text-xs transition-colors">
                    </div>
                </div>
                <div class="flex justify-end gap-2 pt-2 border-t border-slate-100 dark:border-slate-700 transition-colors">
                    <button type="button" onclick="closePaymentModal()" class="px-4 py-2 text-xs font-bold text-slate-500 dark:text-slate-400 border border-slate-200 dark:border-slate-600 rounded-xl hover:bg-slate-50 dark:hover:bg-slate-800 transition-colors">Cancel</button>
                    <button type="submit" class="theme-btn text-white font-bold py-2.5 px-6 rounded-xl text-xs transition-colors">Submit Payment</button>
                </div>
            </form>
        </div>
    </div>

    <!-- DOCUMENT MODAL (Enlarged to max-w-lg) -->
    <div id="document-modal" class="fixed inset-0 bg-slate-900/60 dark:bg-slate-900/80 backdrop-blur-sm z-50 hidden items-center justify-center p-4 transition-colors duration-300">
        <div class="bg-white dark:bg-slate-900 w-full max-w-lg rounded-2xl shadow-xl border border-slate-100 dark:border-slate-700 p-6 space-y-4 relative animate-fadeIn transition-colors duration-300">
            <button onclick="closeDocumentModal()" class="absolute top-4 right-4 text-slate-400 hover:text-slate-600 dark:hover:text-slate-200"><i class="fa-solid fa-xmark text-base"></i></button>
            <h2 class="text-base font-bold text-slate-900 dark:text-white border-b border-slate-100 dark:border-slate-700 pb-3 transition-colors">Documents for <span id="doc-guest-name" class="theme-text"></span></h2>
            <div class="space-y-3">
                <button id="btn-generate-receipt" onclick="generatePDFReceipt()" class="w-full theme-btn text-white font-bold py-3 px-4 rounded-xl text-xs flex items-center justify-center gap-2 transition"><i class="fa-solid fa-file-pdf"></i> Generate Official PDF Receipt</button>
                <button onclick="generatePDFInvoice()" class="w-full bg-slate-900 dark:bg-slate-700 hover:bg-slate-800 dark:hover:bg-slate-600 text-white font-bold py-3 px-4 rounded-xl text-xs flex items-center justify-center gap-2 transition"><i class="fa-solid fa-file-invoice"></i> Generate Itemized Invoice</button>
            </div>
        </div>
    </div>

    <!-- PRINT AREA -->
    <div id="printable-document-area" class="hidden bg-white px-8 py-6 w-full max-w-2xl mx-auto text-slate-800 font-sans text-xs relative overflow-hidden">
        <div class="absolute inset-0 flex items-center justify-center pointer-events-none z-0 overflow-hidden">
            <?php if (($set['watermark_type'] ?? 'text') === 'image' && !empty($set['watermark_image_path'])): ?>
                <img src="<?php echo htmlspecialchars($set['watermark_image_path']); ?>" alt="Watermark" class="w-3/4 h-3/4 object-contain opacity-30" style="filter: grayscale(100%);">
            <?php else: ?>
                <span class="font-black uppercase tracking-widest opacity-20" style="font-size: 4.5rem; color: #64748b; transform: rotate(-30deg); font-family: 'Times New Roman', Times, serif; white-space: nowrap; user-select: none;">
                    <?php echo htmlspecialchars($set['watermark_text'] ?? 'Rhino Tourist Camp'); ?>
                </span>
            <?php endif; ?>
        </div>

        <div class="relative z-10">
            <?php if (!empty($set['receipt_header_path'])): ?>
                <div class="w-full text-center mb-6 border-b-2 border-slate-200 pb-4">
                    <img src="<?php echo htmlspecialchars($set['receipt_header_path']); ?>" alt="Company Header" class="w-full object-contain max-h-28 inline-block">
                </div>
            <?php else: ?>
                <div class="text-center border-b-2 border-slate-200 pb-4 mb-6 space-y-1">
                    <h1 class="text-3xl font-bold uppercase text-black" style="font-family: 'Times New Roman', Times, serif;"><?php echo htmlspecialchars($set['header_title']); ?></h1>
                </div>
            <?php endif; ?>

            <div class="text-center mb-6">
                <h2 id="doc-title-badge" class="text-2xl font-bold uppercase tracking-widest text-black" style="font-family: 'Times New Roman', Times, serif;">Official Receipt</h2>
            </div>
            
            <table class="w-full mb-6 border-collapse">
                <tr>
                    <td class="w-1/2 align-top p-3 bg-slate-50 border border-slate-200 rounded-l-xl">
                        <p class="text-slate-500 text-[10px] uppercase tracking-wider mb-0.5">Booking Ref</p>
                        <p class="text-slate-900 font-black text-lg mb-2">#<span id="p-book-id"></span></p>
                        <p class="text-slate-500 text-[10px] uppercase tracking-wider mb-0.5">Guest Name</p>
                        <p class="text-slate-900 font-bold text-sm"><span id="p-guest-name"></span></p>
                    </td>
                    <td class="w-1/2 align-top p-3 bg-slate-50 border border-slate-200 border-l-0 rounded-r-xl text-right">
                        <p class="text-slate-500 text-[10px] uppercase tracking-wider mb-0.5">Check-in Date</p>
                        <p class="text-slate-900 font-bold text-sm mb-2"><span id="p-checkin"></span></p>
                        <p class="text-slate-500 text-[10px] uppercase tracking-wider mb-0.5">Check-out Date</p>
                        <p class="text-slate-900 font-bold text-sm"><span id="p-checkout"></span></p>
                    </td>
                </tr>
            </table>
            
            <table class="w-full text-left border-collapse border border-slate-300 mb-6">
                <thead>
                    <tr class="bg-slate-100 text-slate-600 uppercase text-[10px] tracking-wider border-b-2 border-slate-400">
                        <th class="p-3 border-r border-slate-300">Description</th>
                        <th class="p-3 text-center border-r border-slate-300 w-20">Nights</th>
                        <th class="p-3 text-right w-40">Amount (<span id="pdf-header-currency"></span>)</th>
                    </tr>
                </thead>
                <tbody class="text-xs">
                    <tr class="border-b border-slate-200">
                        <td class="p-3 border-r border-slate-300" id="p-desc-room">Accommodation & Taxes</td>
                        <td class="p-3 text-center border-r border-slate-300" id="p-nights-count">1</td>
                        <td class="p-3 text-right font-mono font-bold" id="p-original-total">0.00</td>
                    </tr>
                    <tr id="tr-discount-row" class="text-rose-600 border-b border-slate-200 hidden bg-rose-50/30">
                        <td class="p-3 border-r border-slate-300 font-bold">Discount Applied</td>
                        <td class="p-3 text-center border-r border-slate-300 font-bold">-</td>
                        <td class="p-3 text-right font-mono font-bold" id="p-discount-amount">0.00</td>
                    </tr>
                    <tr class="bg-slate-50 border-t-2 border-slate-400">
                        <td class="p-3 font-bold text-slate-600 uppercase text-[10px] tracking-widest border-r border-slate-300">Total Billed</td>
                        <td class="p-3 text-center border-r border-slate-300">-</td>
                        <td class="p-3 text-right font-black font-mono text-sm" id="p-total-amount">0.00</td>
                    </tr>
                    <tr class="bg-emerald-50/30">
                        <td class="p-3 font-bold text-emerald-700 uppercase text-[10px] tracking-widest border-r border-slate-300 border-b border-slate-300">Total Paid</td>
                        <td class="p-3 text-center border-r border-slate-300 border-b border-slate-300">-</td>
                        <td class="p-3 text-right font-black font-mono text-emerald-700 text-sm border-b border-slate-300" id="p-total-paid">0.00</td>
                    </tr>
                    <tr class="bg-rose-50/50 border-b-2 border-slate-400">
                        <td class="p-3 font-bold text-rose-600 uppercase text-[10px] tracking-widest border-r border-slate-300">Balance Due</td>
                        <td class="p-3 text-center border-r border-slate-300">-</td>
                        <td class="p-3 text-right font-black font-mono text-rose-700 text-base" id="p-balance-due">0.00</td>
                    </tr>
                </tbody>
            </table>

            <?php if (!empty($set['receipt_footer_path'])): ?>
                <div class="w-full text-center mt-6 pt-3 border-t-2 border-slate-200">
                    <img src="<?php echo htmlspecialchars($set['receipt_footer_path']); ?>" alt="Company Footer" class="w-full object-contain max-h-20 inline-block">
                </div>
            <?php else: ?>
                <div class="text-center mt-8 pt-4 border-t-2 border-slate-200">
                    <p class="text-[10px] font-bold text-slate-500 tracking-widest uppercase">THANK YOU FOR CHOOSING <?php echo htmlspecialchars($set['sidebar_title']); ?></p>
                </div>
            <?php endif; ?>
        </div>
    </div>
</body>
</html>