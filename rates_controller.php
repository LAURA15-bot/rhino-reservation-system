<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
if (!isset($_SESSION['logged_in'])) {
    header("Location: login.php");
    exit;
}
$current_page = basename($_SERVER['PHP_SELF']);
$isAdmin = (isset($_SESSION['role']) && strtolower($_SESSION['role']) === 'admin');

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
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Rhino Tourist Camp - Rate Management Terminal</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <script> tailwind.config = { darkMode: 'class', } </script>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/html2pdf.js/0.10.1/html2pdf.bundle.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/exceljs/4.3.0/exceljs.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/FileSaver.js/2.0.5/FileSaver.min.js"></script>
    
    <script> const IS_USER_ADMIN = <?php echo $isAdmin ? 'true' : 'false'; ?>; </script>
    <script src="js/rates_controller.js?v=4" defer></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    
    <style>
        :root { --theme-color: <?php echo $primaryColor; ?>; }
        .custom-shadow { box-shadow: 0 4px 20px -2px rgba(0, 0, 0, 0.05); }
        .theme-btn { background-color: var(--theme-color); transition: filter 0.2s; }
        .theme-btn:hover { filter: brightness(85%); }
        .theme-text { color: var(--theme-color); }
    </style>
</head>
<body class="bg-[#f8fafc] dark:bg-slate-900 text-[#334155] dark:text-slate-200 font-sans antialiased min-h-screen overflow-hidden transition-colors duration-300">
    
    <div class="flex h-screen w-screen overflow-hidden">
        <?php include 'Includes/sidebar.php'; ?>

        <main class="flex-1 flex flex-col h-full overflow-hidden bg-[#f8fafc] dark:bg-slate-900 transition-colors duration-300">
            <?php include 'Includes/header.php'; ?>

            <div class="flex-1 overflow-y-auto p-4 lg:p-8 space-y-6">
                
                <div class="bg-white dark:bg-slate-800 p-4 rounded-2xl custom-shadow border border-slate-100 dark:border-slate-700 flex flex-col xl:flex-row items-center justify-between gap-4 transition-colors duration-300">
                    <div>
                        <h1 class="text-base font-bold text-slate-900 dark:text-white tracking-tight flex items-center gap-2">
                            <i class="fa-solid fa-sliders theme-text"></i> Entry Capture Rate Console
                        </h1>
                        <p class="text-xs text-slate-400 dark:text-slate-500 mt-0.5">Manage and audit institutional contract rate matrices</p>
                    </div>
                    
                    <div class="flex items-center gap-3 flex-wrap xl:flex-nowrap">
                        <?php if ($isAdmin): ?>
                        <div class="bg-slate-100 dark:bg-slate-900 p-1 rounded-xl flex items-center border border-slate-200 dark:border-slate-700 shadow-inner transition-colors duration-300">
                            <button onclick="switchSystemConsoleMode('view')" id="mode-view-btn" class="px-4 py-1.5 rounded-lg text-xs font-bold transition-all bg-white dark:bg-slate-700 text-slate-800 dark:text-white shadow-sm">
                                View Mode
                            </button>
                            <button onclick="switchSystemConsoleMode('edit')" id="mode-edit-btn" class="px-4 py-1.5 rounded-lg text-xs font-bold transition-all text-slate-400 dark:text-slate-500 hover:text-slate-600 dark:hover:text-slate-300">
                                Edit Mode
                            </button>
                        </div>
                        <button onclick="openRateModal()" id="create-rate-btn" disabled class="bg-slate-200 dark:bg-slate-800 text-slate-400 dark:text-slate-600 cursor-not-allowed text-xs font-bold py-2.5 px-4 rounded-xl shadow-sm transition flex items-center gap-2">
                            <i class="fa-solid fa-plus"></i> Post Group Rates
                        </button>
                        <?php endif; ?>
                        
                        <button onclick="exportRatesToExcel()" class="bg-[#107c41] hover:bg-[#0c5e31] text-white text-xs font-bold py-2.5 px-4 rounded-xl shadow-sm transition flex items-center gap-2">
                            <i class="fa-solid fa-file-excel"></i> Export Excel
                        </button>

                        <button onclick="compileAndDownloadRatesPDF()" class="bg-[#b91c1c] hover:bg-[#991b1b] text-white text-xs font-bold py-2.5 px-4 rounded-xl shadow-sm transition flex items-center gap-2">
                            <i class="fa-solid fa-file-pdf"></i> Download PDF
                        </button>
                    </div>
                </div>

                <?php if ($isAdmin): ?>
                <div id="console-lock-advisory" class="bg-slate-100 dark:bg-slate-800/80 text-slate-500 dark:text-slate-400 border border-slate-200 dark:border-slate-700 text-center py-2.5 px-4 rounded-xl text-xs font-mono tracking-wide uppercase select-none transition-colors duration-300">
                    Console Locked (Switch to Edit Mode to Commit Log Matrices)
                </div>
                <?php endif; ?>

                <div id="standard-registry-log-card" class="bg-white dark:bg-slate-800 rounded-2xl custom-shadow border border-slate-100 dark:border-slate-700 overflow-hidden transition-colors duration-300">
                    <div class="p-5 bg-white dark:bg-slate-800 border-b border-slate-100 dark:border-slate-700 transition-colors duration-300">
                        <h3 class="text-xs font-bold tracking-wide uppercase text-slate-800 dark:text-slate-200 flex items-center gap-2">
                            <i class="fa-solid fa-table-cells theme-text"></i> Active Rate Parameters Matrix Board
                        </h3>
                    </div>
                    
                    <div class="overflow-x-auto">
                        <table class="w-full text-left border-collapse text-xs font-medium text-slate-600 dark:text-slate-300">
                            <thead>
                                <tr class="bg-slate-50/70 dark:bg-slate-900/50 text-slate-400 dark:text-slate-500 uppercase text-[10px] font-bold tracking-wider border-b border-slate-100 dark:border-slate-700 transition-colors duration-300">
                                    <th class="p-4 pl-6">Seasons / Dates</th>
                                    <th class="p-4">Room Tier</th>
                                    <th class="p-4 text-center bg-emerald-50/30 dark:bg-emerald-900/20 text-emerald-800 dark:text-emerald-400">Single (KSh / $)</th>
                                    <th class="p-4 text-center bg-blue-50/30 dark:bg-blue-900/20 text-blue-800 dark:text-blue-400">Double (KSh / $)</th>
                                    <th class="p-4 text-center bg-amber-50/30 dark:bg-amber-900/20 text-amber-800 dark:text-amber-400">Triple (KSh / $)</th>
                                    <th class="p-4 text-center bg-purple-50/30 dark:bg-purple-900/20 text-purple-800 dark:text-purple-400">Family (KSh / $)</th>
                                    <?php if ($isAdmin): ?>
                                    <th class="p-4 text-right pr-6 log-actions-column hidden">Actions</th>
                                    <?php endif; ?>
                                </tr>
                            </thead>
                            <tbody id="standard-matrix-tbody" class="divide-y divide-slate-100 dark:divide-slate-700/50">
                                <!-- Populated dynamically -->
                            </tbody>
                        </table>
                    </div>
                </div>
<!-- HIDDEN PDF DOCUMENT CANVAS -->
<div id="hidden-pdf-document-canvas" class="hidden bg-white p-4 text-slate-800">
    
    <!-- Dynamic PDF Header Upload -->
    <?php if (!empty($set['rack_rates_header_path'])): ?>
        <div class="w-full text-center mb-4">
            <img src="<?php echo htmlspecialchars($set['rack_rates_header_path']); ?>" alt="Company Header" class="w-full object-contain max-h-32">
        </div>
    <?php endif; ?>

    <!-- Scaled Down Screen-Matched Matrix Table -->
    <div class="overflow-hidden border border-slate-300 rounded-lg mt-2">
        <table class="w-full text-left border-collapse text-[10px] font-medium text-slate-800">
            <thead>
                <tr class="bg-slate-100 uppercase text-[9px] font-bold tracking-wider border-b-2 border-slate-300">
                    <th class="p-2 border-r border-slate-300 w-44">Seasons / Dates</th>
                    <th class="p-2 border-r border-slate-300">Room Tier</th>
                    <th class="p-2 text-center border-r border-slate-300 bg-emerald-50 text-emerald-900">Single (KSh / $)</th>
                    <th class="p-2 text-center border-r border-slate-300 bg-blue-50 text-blue-900">Double (KSh / $)</th>
                    <th class="p-2 text-center border-r border-slate-300 bg-amber-50 text-amber-900">Triple (KSh / $)</th>
                    <th class="p-2 text-center bg-purple-50 text-purple-900">Family (KSh / $)</th>
                </tr>
            </thead>
            <tbody id="pdf-matrix-tbody" class="divide-y divide-slate-200">
                <!-- Cloned dynamically from screen via JS -->
            </tbody>
        </table>
    </div>

    <!-- Dynamic PDF Footer Upload -->
    <?php if (!empty($set['rack_rates_footer_path'])): ?>
        <div class="w-full text-center mt-4 pt-2">
            <img src="<?php echo htmlspecialchars($set['rack_rates_footer_path']); ?>" alt="Company Footer" class="w-full object-contain max-h-24">
        </div>
    <?php endif; ?>

</div>
            </div>

            <?php include 'Includes/footer.php'; ?>

        </main>
    </div>

    <!-- DATA CAPTURE MODAL -->
    <?php if ($isAdmin): ?>
    <div id="rate-modal-backdrop" class="fixed inset-0 bg-slate-900/60 dark:bg-slate-900/80 backdrop-blur-sm z-50 hidden items-center justify-center p-4 overflow-y-auto">
        <div class="bg-white dark:bg-slate-900 w-full max-w-2xl rounded-2xl shadow-xl border border-slate-100 dark:border-slate-700 p-6 space-y-5 max-h-[95vh] overflow-y-auto relative animate-fadeIn transition-colors duration-300">
            <button onclick="closeRateModal()" class="absolute top-4 right-4 text-slate-400 hover:text-slate-600 dark:hover:text-slate-300 text-sm z-10"><i class="fa-solid fa-xmark text-lg"></i></button>
            <div class="flex justify-between items-center border-b border-slate-100 dark:border-slate-700 pb-3 transition-colors">
                <h2 id="modal-terminal-title" class="text-base font-bold text-[#0f172a] dark:text-white flex items-center gap-2">
                    <i class="fa-solid fa-money-check-dollar theme-text"></i> Group Rates Assignment Terminal
                </h2>
            </div>

            <form id="rate-entry-form" onsubmit="handleRateFormSubmit(event)" class="space-y-4 text-xs font-semibold text-slate-700 dark:text-slate-200">
                <div class="grid grid-cols-1 sm:grid-cols-3 gap-3 bg-slate-50 dark:bg-slate-800/80 p-3 rounded-xl border border-slate-200 dark:border-slate-700 transition-colors">
                    <div>
                        <label class="block text-slate-500 dark:text-slate-400 mb-1">Target Season *</label>
                        <select id="rate-season" class="w-full bg-white dark:bg-slate-900 border border-slate-300 dark:border-slate-600 text-slate-900 dark:text-white p-2 rounded-lg outline-none transition-colors">
                            <option value="Festive Season">Festive Season</option>
                            <option value="High Season">High Season</option>
                            <option value="Low Season">Low Season</option>
                            <option value="Peak Season">Peak Season</option>
                        </select>
                    </div>
                    <div>
                        <label class="block text-slate-500 dark:text-slate-400 mb-1">Room Tier *</label>
                        <select id="rate-room-tier" class="w-full bg-white dark:bg-slate-900 border border-slate-300 dark:border-slate-600 text-slate-900 dark:text-white p-2 rounded-lg outline-none transition-colors">
                            <option value="SUPERIOR TENTS">SUPERIOR TENTS</option>
                            <option value="DELUXE ROOMS">DELUXE ROOMS</option>
                        </select>
                    </div>
                    <div>
                        <label class="block text-slate-500 dark:text-slate-400 mb-1">Currency</label>
                        <select class="w-full bg-slate-100 dark:bg-slate-900 border border-slate-300 dark:border-slate-700 p-2 rounded-lg font-bold text-slate-600 dark:text-slate-500 transition-colors" disabled><option>KSh & USD</option></select>
                    </div>
                </div>

                <div class="space-y-3 pt-2">
                    <h4 class="text-[10px] font-bold text-slate-400 dark:text-slate-500 uppercase tracking-wider border-b border-slate-100 dark:border-slate-700 pb-1 transition-colors">Capacity Configuration Grid</h4>
                    <div class="grid grid-cols-2 gap-4">
                        <div class="p-3 bg-slate-50 dark:bg-slate-800/50 rounded-xl border border-slate-200 dark:border-slate-700 space-y-2 transition-colors">
                            <span class="block text-[10px] uppercase font-bold text-slate-800 dark:text-slate-400">Single Room</span>
                            <div class="grid grid-cols-2 gap-2">
                                <input type="number" id="amt-single-ksh" placeholder="KSh" class="border border-slate-300 dark:border-slate-600 bg-white dark:bg-slate-800 text-slate-900 dark:text-white p-2 rounded-lg font-mono outline-none focus:ring-1 focus:ring-slate-500 transition-colors" required>
                                <input type="number" id="amt-single-usd" placeholder="USD" class="border border-slate-300 dark:border-slate-600 bg-white dark:bg-slate-800 text-slate-900 dark:text-white p-2 rounded-lg font-mono outline-none focus:ring-1 focus:ring-slate-500 transition-colors" required>
                            </div>
                        </div>
                        <div class="p-3 bg-slate-50 dark:bg-slate-800/50 rounded-xl border border-slate-200 dark:border-slate-700 space-y-2 transition-colors">
                            <span class="block text-[10px] uppercase font-bold text-slate-800 dark:text-slate-400">Double Room</span>
                            <div class="grid grid-cols-2 gap-2">
                                <input type="number" id="amt-double-ksh" placeholder="KSh" class="border border-slate-300 dark:border-slate-600 bg-white dark:bg-slate-800 text-slate-900 dark:text-white p-2 rounded-lg font-mono outline-none focus:ring-1 focus:ring-slate-500 transition-colors" required>
                                <input type="number" id="amt-double-usd" placeholder="USD" class="border border-slate-300 dark:border-slate-600 bg-white dark:bg-slate-800 text-slate-900 dark:text-white p-2 rounded-lg font-mono outline-none focus:ring-1 focus:ring-slate-500 transition-colors" required>
                            </div>
                        </div>
                    </div>
                    <div class="grid grid-cols-2 gap-4">
                        <div class="p-3 bg-slate-50 dark:bg-slate-800/50 rounded-xl border border-slate-200 dark:border-slate-700 space-y-2 transition-colors">
                            <span class="block text-[10px] uppercase font-bold text-slate-800 dark:text-slate-400">Triple Room</span>
                            <div class="grid grid-cols-2 gap-2">
                                <input type="number" id="amt-triple-ksh" placeholder="KSh" class="border border-slate-300 dark:border-slate-600 bg-white dark:bg-slate-800 text-slate-900 dark:text-white p-2 rounded-lg font-mono outline-none focus:ring-1 focus:ring-slate-500 transition-colors" required>
                                <input type="number" id="amt-triple-usd" placeholder="USD" class="border border-slate-300 dark:border-slate-600 bg-white dark:bg-slate-800 text-slate-900 dark:text-white p-2 rounded-lg font-mono outline-none focus:ring-1 focus:ring-slate-500 transition-colors" required>
                            </div>
                        </div>
                        <div class="p-3 bg-slate-50 dark:bg-slate-800/50 rounded-xl border border-slate-200 dark:border-slate-700 space-y-2 transition-colors">
                            <span class="block text-[10px] uppercase font-bold text-slate-800 dark:text-slate-400">Family Room</span>
                            <div class="grid grid-cols-2 gap-2">
                                <input type="number" id="amt-family-ksh" placeholder="KSh" class="border border-slate-300 dark:border-slate-600 bg-white dark:bg-slate-800 text-slate-900 dark:text-white p-2 rounded-lg font-mono outline-none focus:ring-1 focus:ring-slate-500 transition-colors" required>
                                <input type="number" id="amt-family-usd" placeholder="USD" class="border border-slate-300 dark:border-slate-600 bg-white dark:bg-slate-800 text-slate-900 dark:text-white p-2 rounded-lg font-mono outline-none focus:ring-1 focus:ring-slate-500 transition-colors" required>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="flex justify-end gap-3 pt-3 border-t border-slate-100 dark:border-slate-700 transition-colors">
                    <button type="button" onclick="closeRateModal()" class="px-4 py-2 text-xs font-bold text-slate-500 dark:text-slate-400 border border-slate-200 dark:border-slate-600 hover:bg-slate-50 dark:hover:bg-slate-800 rounded-xl transition-colors">Cancel</button>
                    <button type="submit" class="theme-btn text-white font-black py-2.5 px-6 rounded-xl shadow-md transition">Save Matrix</button>
                </div>
            </form>
        </div>
    </div>
    <?php endif; ?>
</body>
</html>