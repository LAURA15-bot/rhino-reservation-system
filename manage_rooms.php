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
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    
    <!-- Link to external JS -->
    <script src="js/manage_rooms.js" defer></script>
</head>
<body class="bg-[#f8fafc] text-slate-800 font-sans flex h-screen overflow-hidden">
    
    <!-- GLOBAL SIDEBAR INCLUSION -->
    <?php include 'Includes/sidebar.php'; ?>
    
    <main class="flex-1 overflow-y-auto p-4 lg:p-8">
        
        <!-- Header Section -->
        <div class="mb-8 flex flex-col md:flex-row md:items-center justify-between gap-4">
            <div>
                <h2 class="text-2xl font-black text-slate-900 flex items-center gap-3 tracking-tight">
                    <i class="fa-solid fa-hotel text-[#046a38]"></i> Property Configuration
                </h2>
                <p class="text-sm text-slate-500 mt-1 font-medium">Manage physical room inventory and maximum legal occupancy limits.</p>
            </div>
        </div>

        <!-- Room Cards Grid Container -->
        <div id="room-cards-container" class="grid grid-cols-1 md:grid-cols-2 xl:grid-cols-4 gap-6">
            <!-- Cards Populated via JavaScript -->
        </div>
        
    </main>

    <!-- Edit Room Configuration Modal -->
    <div id="room-modal-backdrop" class="fixed inset-0 bg-slate-900/60 backdrop-blur-sm z-50 hidden items-center justify-center p-4 transition-opacity">
        <div class="bg-white w-full max-w-sm rounded-2xl shadow-2xl p-6 relative animate-fadeIn border border-slate-100">
            
            <button onclick="closeRoomModal()" class="absolute top-4 right-4 text-slate-400 hover:text-slate-600 transition">
                <i class="fa-solid fa-xmark text-lg"></i>
            </button>
            
            <div class="mb-6 border-b border-slate-100 pb-4">
                <h2 class="text-lg font-bold text-slate-800 flex items-center gap-2">
                    <i class="fa-solid fa-sliders text-[#046a38]"></i> Edit Configuration
                </h2>
                <p id="modal-room-title" class="text-xs text-slate-500 font-mono mt-1 font-bold uppercase tracking-wider"></p>
            </div>
            
            <form id="editRoomForm" onsubmit="submitRoomUpdate(event)" class="space-y-5">
                <input type="hidden" name="id" id="edit_room_id">

                <div class="bg-slate-50 p-4 rounded-xl border border-slate-200 space-y-4">
                    <div>
                        <label class="block text-[11px] font-bold text-slate-500 uppercase tracking-wide mb-1.5">Total Property Inventory</label>
                        <div class="relative">
                            <div class="absolute inset-y-0 left-0 flex items-center pl-3 pointer-events-none text-emerald-600">
                                <i class="fa-solid fa-bed"></i>
                            </div>
                            <input type="number" name="total_inventory" id="edit_total_inventory" min="0" required class="w-full bg-white border border-slate-300 p-2.5 pl-9 rounded-lg text-sm font-bold text-slate-800 outline-none focus:border-emerald-500 focus:ring-1 focus:ring-emerald-500 transition">
                        </div>
                    </div>

                    <div>
                        <label class="block text-[11px] font-bold text-slate-500 uppercase tracking-wide mb-1.5">Max Legal Occupancy (Pax)</label>
                        <div class="relative">
                            <div class="absolute inset-y-0 left-0 flex items-center pl-3 pointer-events-none text-amber-500">
                                <i class="fa-solid fa-users"></i>
                            </div>
                            <input type="number" name="max_guests" id="edit_max_guests" min="1" required class="w-full bg-white border border-slate-300 p-2.5 pl-9 rounded-lg text-sm font-bold text-slate-800 outline-none focus:border-amber-500 focus:ring-1 focus:ring-amber-500 transition">
                        </div>
                    </div>
                </div>

                <div class="flex items-center gap-3 pt-2">
                    <button type="button" onclick="closeRoomModal()" class="flex-1 bg-white hover:bg-slate-50 text-slate-600 border border-slate-200 font-bold py-2.5 px-4 rounded-xl transition shadow-sm text-sm">
                        Cancel
                    </button>
                    <button type="submit" class="flex-1 bg-[#046a38] hover:bg-[#03542c] text-white font-bold py-2.5 px-4 rounded-xl transition shadow-sm text-sm flex items-center justify-center gap-2">
                        <i class="fa-solid fa-check"></i> Commit Setup
                    </button>
                </div>
            </form>
        </div>
    </div>
</body>
</html>