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
    <title>Rhino Tourist Camp - Rate Management Terminal</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/html2pdf.js/0.10.1/html2pdf.bundle.min.js"></script>
    
    <!-- ExcelJS & FileSaver for visually styled Excel exports -->
    <script src="https://cdnjs.cloudflare.com/ajax/libs/exceljs/4.3.0/exceljs.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/FileSaver.js/2.0.5/FileSaver.min.js"></script>
    
    <!-- Link to external JS -->
    <script src="js/rates_controller.js" defer></script>
    
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        .custom-shadow { box-shadow: 0 4px 20px -2px rgba(0, 0, 0, 0.05); }
        .pdf-matrix-table th, .pdf-matrix-table td { border: 2px solid #1e293b !important; }
    </style>
</head>
<body class="bg-[#f8fafc] text-[#334155] font-sans antialiased min-h-screen overflow-hidden">
    <div class="flex h-screen w-screen overflow-hidden">
        
        <!-- GLOBAL SIDEBAR INCLUSION -->
        <?php include 'Includes/sidebar.php'; ?>

        <main class="flex-1 overflow-y-auto p-4 lg:p-8 space-y-6">
            
            <div class="bg-white p-4 rounded-2xl custom-shadow border border-slate-100 flex flex-col xl:flex-row items-center justify-between gap-4">
                <div>
                    <h1 class="text-base font-bold text-slate-900 tracking-tight flex items-center gap-2">
                        <i class="fa-solid fa-sliders text-[#046a38]"></i> Entry Capture Rate Console
                    </h1>
                    <p class="text-xs text-slate-400 mt-0.5">Manage and audit institutional contract rate matrices</p>
                </div>
                
                <div class="flex items-center gap-3 flex-wrap xl:flex-nowrap">
                    <div class="bg-slate-100 p-1 rounded-xl flex items-center border border-slate-200 shadow-inner">
                        <button onclick="switchSystemConsoleMode('view')" id="mode-view-btn" class="px-4 py-1.5 rounded-lg text-xs font-bold transition-all bg-white text-slate-800 shadow-sm">
                            View Mode
                        </button>
                        <button onclick="switchSystemConsoleMode('edit')" id="mode-edit-btn" class="px-4 py-1.5 rounded-lg text-xs font-bold transition-all text-slate-400 hover:text-slate-600">
                            Edit Mode
                        </button>
                    </div>
                    <button onclick="openRateModal()" id="create-rate-btn" disabled class="bg-slate-200 text-slate-400 cursor-not-allowed text-xs font-bold py-2.5 px-4 rounded-xl shadow-sm transition flex items-center gap-2">
                        <i class="fa-solid fa-plus"></i> Post Group Rates
                    </button>
                    
                    <button onclick="exportRatesToExcel()" class="bg-[#107c41] hover:bg-[#0c5e31] text-white text-xs font-bold py-2.5 px-4 rounded-xl shadow-sm transition flex items-center gap-2">
                        <i class="fa-solid fa-file-excel"></i> Export Excel
                    </button>

                    <button onclick="compileAndDownloadRatesPDF()" class="bg-[#b91c1c] hover:bg-[#991b1b] text-white text-xs font-bold py-2.5 px-4 rounded-xl shadow-sm transition flex items-center gap-2">
                        <i class="fa-solid fa-file-pdf"></i> Download PDF
                    </button>
                </div>
            </div>

            <div id="console-lock-advisory" class="bg-slate-100 text-slate-500 border border-slate-200 text-center py-2.5 px-4 rounded-xl text-xs font-mono tracking-wide uppercase select-none">
                Console Locked (Switch to Edit Mode to Commit Log Matrices)
            </div>

            <div id="standard-registry-log-card" class="bg-white rounded-2xl custom-shadow border border-slate-100 overflow-hidden">
                <div class="p-5 bg-white border-b border-slate-100">
                    <h3 class="text-xs font-bold tracking-wide uppercase text-slate-800 flex items-center gap-2">
                        <i class="fa-solid fa-table-cells text-[#046a38]"></i> Active Rate Parameters Matrix Board
                    </h3>
                </div>
                
                <div class="overflow-x-auto">
                    <table class="w-full text-left border-collapse text-xs font-medium text-slate-600">
                        <thead>
                            <tr class="bg-slate-50/70 text-slate-400 uppercase text-[10px] font-bold tracking-wider border-b border-slate-100">
                                <th class="p-4 pl-6">Seasons / Dates</th>
                                <th class="p-4">Room Tier</th>
                                <th class="p-4 text-center bg-emerald-50/30 text-emerald-800">Single (KSh / $)</th>
                                <th class="p-4 text-center bg-blue-50/30 text-blue-800">Double (KSh / $)</th>
                                <th class="p-4 text-center bg-amber-50/30 text-amber-800">Triple (KSh / $)</th>
                                <th class="p-4 text-center bg-purple-50/30 text-purple-800">Family (KSh / $)</th>
                                <th class="p-4 text-right pr-6 log-actions-column hidden">Actions</th>
                            </tr>
                        </thead>
                        <tbody id="standard-matrix-tbody" class="divide-y divide-slate-100">
                            <!-- Populated dynamically -->
                        </tbody>
                    </table>
                </div>
            </div>

            <!-- HIDDEN PDF DOCUMENT CANVAS -->
            <div id="hidden-pdf-document-canvas" class="hidden bg-white p-12 space-y-6">
                <div class="flex flex-col items-center space-y-2 border-b-2 border-slate-800 pb-4 text-center">
                    <div class="flex items-center justify-center gap-6">
                        <div class="w-24 h-16 bg-slate-200 rounded-xl flex items-center justify-center text-slate-400 italic text-[10px] border border-slate-300">Cheetah Logo</div>
                        <div>
                            <h1 class="text-3xl font-serif font-black tracking-wide text-amber-800 uppercase leading-none">Rhino Tourist Camp</h1>
                            <h2 class="text-2xl font-serif font-bold text-emerald-800 italic tracking-wider mt-1">Masai Mara</h2>
                        </div>
                    </div>
                    <p class="text-[10px] font-mono font-bold text-slate-700 tracking-tight leading-relaxed max-w-3xl pt-2">
                        www.rhinotouristcamp.com email: info@rhinotouristcamp.com Mobile (+254) 0700355555 / 0722518843 / 0727827007. Landline: (+254) 202385334
                    </p>
                    <p class="text-[11px] font-black text-center tracking-wide text-slate-900 uppercase pt-3">
                        RESIDENT AND NON-RESIDENT <span class="underline underline-offset-4">CONTRACT RATES</span> VALID FROM 21ST Dec 2025 to 20th Dec 2026
                    </p>
                    <div class="w-full grid grid-cols-2 text-xs font-mono pt-4 px-6 text-slate-500">
                        <div class="text-left">COMPANY ............................................................................</div>
                        <div class="text-right">NAME ............................................................................</div>
                    </div>
                    <p class="text-[9px] text-slate-500 italic pt-2">
                        We provide drivers with <span class="font-bold text-slate-800">FREE</span> self-contained FB accommodation with hot showers.
                    </p>
                </div>

                <div class="pt-2">
                    <table class="pdf-matrix-table w-full text-center border-collapse text-xs font-bold">
                        <thead>
                            <tr class="bg-slate-50 border-b-2 border-slate-900">
                                <th rowspan="2" class="p-2 text-left text-[10px] uppercase tracking-wider min-w-[150px]">Seasons / Dates</th>
                                <th rowspan="2" class="p-2 min-w-[120px]">Room Tier</th>
                                <th colspan="2" class="p-1.5 uppercase text-slate-900">Single</th>
                                <th colspan="2" class="p-1.5 uppercase text-slate-900">Double</th>
                                <th colspan="2" class="p-1.5 uppercase text-slate-900">Triple</th>
                                <th colspan="2" class="p-1.5 uppercase text-slate-900">Family</th>
                            </tr>
                            <tr class="bg-slate-100 font-mono text-[9px] text-slate-600">
                                <th class="p-1">KSH</th><th class="p-1">USD</th>
                                <th class="p-1">KSH</th><th class="p-1">USD</th>
                                <th class="p-1">KSH</th><th class="p-1">USD</th>
                                <th class="p-1">KSH</th><th class="p-1">USD</th>
                            </tr>
                        </thead>
                        <tbody id="pdf-matrix-tbody"></tbody>
                    </table>
                </div>

                <div class="pt-4 border-t-2 border-slate-800 text-[10px] leading-relaxed space-y-4 font-semibold text-slate-700">
                    <div class="bg-slate-100 p-2 text-center text-[9px] font-bold text-slate-600 rounded-lg border border-slate-200">
                        We are connected to the main power grid so have power 24/7. Our self-contained rooms have instant showers. We have Wi-Fi in the common area.
                    </div>
                </div>
            </div>
        </main>
    </div>

    <!-- DATA CAPTURE MODAL -->
    <div id="rate-modal-backdrop" class="fixed inset-0 bg-slate-900/60 backdrop-blur-sm z-50 hidden items-center justify-center p-4 overflow-y-auto">
        <div class="bg-white w-full max-w-2xl rounded-2xl shadow-xl border border-slate-100 p-6 space-y-5 max-h-[95vh] overflow-y-auto relative animate-fadeIn">
            <button onclick="closeRateModal()" class="absolute top-4 right-4 text-slate-400 hover:text-slate-600 text-sm z-10"><i class="fa-solid fa-xmark text-lg"></i></button>
            <div class="flex justify-between items-center border-b pb-3">
                <h2 id="modal-terminal-title" class="text-base font-bold text-[#0f172a] flex items-center gap-2">
                    <i class="fa-solid fa-money-check-dollar text-[#046a38]"></i> Group Rates Assignment Terminal
                </h2>
            </div>

            <form id="rate-entry-form" onsubmit="handleRateFormSubmit(event)" class="space-y-4 text-xs font-semibold">
                <div class="grid grid-cols-1 sm:grid-cols-3 gap-3 bg-slate-50 p-3 rounded-xl border">
                    <div>
                        <label class="block text-slate-500 mb-1">Target Season *</label>
                        <select id="rate-season" class="w-full bg-white border p-2 rounded-lg outline-none">
                            <option value="Festive Season">Festive Season</option>
                            <option value="High Season">High Season</option>
                            <option value="Low Season">Low Season</option>
                            <option value="Peak Season">Peak Season</option>
                        </select>
                    </div>
                    <div>
                        <label class="block text-slate-500 mb-1">Room Tier *</label>
                        <select id="rate-room-tier" class="w-full bg-white border p-2 rounded-lg outline-none">
                            <option value="SUPERIOR TENTS">SUPERIOR TENTS</option>
                            <option value="DELUXE ROOMS">DELUXE ROOMS</option>
                        </select>
                    </div>
                    <div>
                        <label class="block text-slate-500 mb-1">Currency</label>
                        <select class="w-full bg-slate-100 border p-2 rounded-lg font-bold text-slate-600" disabled><option>KSh & USD</option></select>
                    </div>
                </div>

                <div class="space-y-3 pt-2">
                    <h4 class="text-[10px] font-bold text-slate-400 uppercase tracking-wider border-b pb-1">Capacity Configuration Grid</h4>
                    <div class="grid grid-cols-2 gap-4">
                        <div class="p-3 bg-emerald-50/20 rounded-xl border border-emerald-100 space-y-2">
                            <span class="block text-[10px] uppercase font-bold text-emerald-800">Single Room</span>
                            <div class="grid grid-cols-2 gap-2"><input type="number" id="amt-single-ksh" placeholder="KSh" class="border p-2 rounded-lg font-mono" required><input type="number" id="amt-single-usd" placeholder="USD" class="border p-2 rounded-lg font-mono" required></div>
                        </div>
                        <div class="p-3 bg-blue-50/20 rounded-xl border border-blue-100 space-y-2">
                            <span class="block text-[10px] uppercase font-bold text-blue-800">Double Room</span>
                            <div class="grid grid-cols-2 gap-2"><input type="number" id="amt-double-ksh" placeholder="KSh" class="border p-2 rounded-lg font-mono" required><input type="number" id="amt-double-usd" placeholder="USD" class="border p-2 rounded-lg font-mono" required></div>
                        </div>
                    </div>
                    <div class="grid grid-cols-2 gap-4">
                        <div class="p-3 bg-amber-50/20 rounded-xl border border-amber-100 space-y-2">
                            <span class="block text-[10px] uppercase font-bold text-amber-800">Triple Room</span>
                            <div class="grid grid-cols-2 gap-2"><input type="number" id="amt-triple-ksh" placeholder="KSh" class="border p-2 rounded-lg font-mono" required><input type="number" id="amt-triple-usd" placeholder="USD" class="border p-2 rounded-lg font-mono" required></div>
                        </div>
                        <div class="p-3 bg-purple-50/20 rounded-xl border border-purple-100 space-y-2">
                            <span class="block text-[10px] uppercase font-bold text-purple-800">Family Room</span>
                            <div class="grid grid-cols-2 gap-2"><input type="number" id="amt-family-ksh" placeholder="KSh" class="border p-2 rounded-lg font-mono" required><input type="number" id="amt-family-usd" placeholder="USD" class="border p-2 rounded-lg font-mono" required></div>
                        </div>
                    </div>
                </div>

                <div class="flex justify-end gap-3 pt-3 border-t">
                    <button type="button" onclick="closeRateModal()" class="px-4 py-2 text-xs font-bold text-slate-500 border rounded-xl">Cancel</button>
                    <button type="submit" class="bg-[#046a38] text-white font-black py-2.5 px-6 rounded-xl shadow-md">Save Matrix</button>
                </div>
            </form>
        </div>
    </div>
</body>
</html>