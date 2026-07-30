<?php
session_start();
if (!isset($_SESSION['logged_in']) || strtolower($_SESSION['role']) !== 'admin') { 
    header("Location: dashboard.php"); 
    exit; 
}
$current_page = basename($_SERVER['PHP_SELF']);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Property Management - Rhino Camp</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <script>
        tailwind.config = { darkMode: 'class', }
    </script>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    
    <!-- Link to external JS (Version bumped to clear cache) -->
    <script src="js/manage_rooms.js?v=2" defer></script>
    
    <style>
        .custom-shadow { box-shadow: 0 4px 20px -2px rgba(0, 0, 0, 0.05); }
    </style>
</head>
<body class="bg-[#f8fafc] dark:bg-slate-900 text-slate-800 dark:text-slate-200 font-sans antialiased min-h-screen overflow-hidden transition-colors duration-300">
    
    <!-- FULL SCREEN WRAPPER -->
    <div class="flex h-screen w-screen overflow-hidden">
        
        <!-- 1. LEFT SIDEBAR -->
        <?php include 'Includes/sidebar.php'; ?>
        
        <!-- RIGHT CONTENT AREA -->
        <main class="flex-1 flex flex-col h-full overflow-hidden bg-[#f8fafc] dark:bg-slate-900 transition-colors duration-300">
            
            <!-- 2. GLOBAL HEADER -->
            <?php include 'Includes/header.php'; ?>

            <!-- 3. SCROLLING MAIN CONTENT -->
            <div class="flex-1 overflow-y-auto p-4 lg:p-8">
                
                <div class="max-w-7xl mx-auto">
                    <!-- Premium Section Header -->
                    <div class="bg-white dark:bg-slate-800 p-5 rounded-2xl custom-shadow border border-slate-100 dark:border-slate-700 flex items-center gap-3 mb-8 transition-colors duration-300">
                        <div class="w-10 h-10 rounded-xl bg-emerald-50 dark:bg-emerald-900/30 text-emerald-600 dark:text-emerald-400 flex items-center justify-center shadow-inner shrink-0">
                            <i class="fa-solid fa-hotel text-lg"></i>
                        </div>
                        <div>
                            <h2 class="text-sm font-mono font-bold tracking-widest text-[#0f172a] dark:text-white uppercase">Property Configuration Console</h2>
                            <p class="text-[10px] font-bold text-slate-400 dark:text-slate-500 uppercase tracking-widest mt-0.5">Manage physical room inventory and maximum legal occupancy limits</p>
                        </div>
                    </div>

                    <!-- Room Cards Grid Container -->
                    <div id="room-cards-container" class="grid grid-cols-1 md:grid-cols-2 xl:grid-cols-4 gap-6">
                        <!-- Cards Populated via JavaScript -->
                    </div>
                </div>
                
            </div>

            <!-- 4. GLOBAL FOOTER -->
            <?php include 'Includes/footer.php'; ?>

        </main>
    </div>

    <!-- Edit Room Configuration Modal -->
    <div id="room-modal-backdrop" class="fixed inset-0 bg-slate-900/60 dark:bg-slate-900/80 backdrop-blur-sm z-50 hidden items-center justify-center p-4 transition-colors duration-300">
        <div class="bg-white dark:bg-slate-900 w-full max-w-sm rounded-2xl shadow-2xl p-6 relative animate-fadeIn border border-slate-100 dark:border-slate-700 transition-colors duration-300">
            
            <button onclick="closeRoomModal()" class="absolute top-4 right-4 text-slate-400 dark:text-slate-500 hover:text-slate-600 dark:hover:text-slate-300 transition-colors">
                <i class="fa-solid fa-xmark text-lg"></i>
            </button>
            
            <div class="mb-6 border-b border-slate-100 dark:border-slate-800 pb-4 transition-colors duration-300">
                <h2 class="text-lg font-bold text-slate-800 dark:text-white flex items-center gap-2">
                    <i class="fa-solid fa-sliders text-[#046a38] dark:text-emerald-500"></i> Edit Configuration
                </h2>
                <p id="modal-room-title" class="text-xs text-slate-500 dark:text-slate-400 font-mono mt-1 font-bold uppercase tracking-wider"></p>
            </div>
            
            <form id="editRoomForm" onsubmit="submitRoomUpdate(event)" class="space-y-5">
                <input type="hidden" name="id" id="edit_room_id">

                <div class="bg-slate-50 dark:bg-slate-800/80 p-4 rounded-xl border border-slate-200 dark:border-slate-700 space-y-4 transition-colors duration-300">
                    <div>
                        <label class="block text-[11px] font-bold text-slate-500 dark:text-slate-400 uppercase tracking-wide mb-1.5">Total Property Inventory</label>
                        <div class="relative">
                            <div class="absolute inset-y-0 left-0 flex items-center pl-3 pointer-events-none text-emerald-600 dark:text-emerald-500">
                                <i class="fa-solid fa-bed"></i>
                            </div>
                            <input type="number" name="total_inventory" id="edit_total_inventory" min="0" required class="w-full bg-white dark:bg-slate-900 border border-slate-300 dark:border-slate-600 p-2.5 pl-9 rounded-lg text-sm font-bold text-slate-800 dark:text-white outline-none focus:border-emerald-500 focus:ring-1 focus:ring-emerald-500 transition-colors duration-300">
                        </div>
                    </div>

                    <div>
                        <label class="block text-[11px] font-bold text-slate-500 dark:text-slate-400 uppercase tracking-wide mb-1.5">Max Legal Occupancy (Pax)</label>
                        <div class="relative">
                            <div class="absolute inset-y-0 left-0 flex items-center pl-3 pointer-events-none text-amber-500 dark:text-amber-400">
                                <i class="fa-solid fa-users"></i>
                            </div>
                            <input type="number" name="max_guests" id="edit_max_guests" min="1" required class="w-full bg-white dark:bg-slate-900 border border-slate-300 dark:border-slate-600 p-2.5 pl-9 rounded-lg text-sm font-bold text-slate-800 dark:text-white outline-none focus:border-amber-500 focus:ring-1 focus:ring-amber-500 transition-colors duration-300">
                        </div>
                    </div>
                </div>

                <div class="flex items-center gap-3 pt-2">
                    <button type="button" onclick="closeRoomModal()" class="flex-1 bg-white dark:bg-slate-800 hover:bg-slate-50 dark:hover:bg-slate-700 text-slate-600 dark:text-slate-300 border border-slate-200 dark:border-slate-600 font-bold py-2.5 px-4 rounded-xl transition-colors shadow-sm text-sm">
                        Cancel
                    </button>
                    <button type="submit" class="flex-1 bg-[#046a38] hover:bg-[#03542c] text-white font-bold py-2.5 px-4 rounded-xl transition-colors shadow-sm text-sm flex items-center justify-center gap-2">
                        <i class="fa-solid fa-check"></i> Commit Setup
                    </button>
                </div>
            </form>
        </div>
    </div>
</body>
</html>