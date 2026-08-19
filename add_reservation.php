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
    <title>Add Reservation - Rhino Camp</title>
	
	<!-- Dynamic Favicon Override -->
	<link rel="icon" type="image/png" href="<?php echo !empty($set['logo_path']) ? htmlspecialchars($set['logo_path']) : 'data:image/x-icon;base64,'; ?>">
	
    <script src="https://cdn.tailwindcss.com"></script>
    <script>
        // Configure Tailwind to listen to our dark mode toggle script
        tailwind.config = {
            darkMode: 'class',
        }
    </script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    
    <!-- Link to the external javascript API handler -->
    <script src="js/add_reservation.js" defer></script>
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
                
                <div class="max-w-4xl mx-auto">
                    
                    <!-- Header -->
                    <div class="bg-white dark:bg-slate-800 p-5 rounded-2xl shadow-sm border border-slate-100 dark:border-slate-700 flex items-center justify-between gap-4 mb-6 transition-colors duration-300">
                        <div class="flex items-center gap-3">
                            <div class="w-10 h-10 rounded-xl bg-emerald-50 dark:bg-emerald-900/30 text-[#046a38] dark:text-emerald-400 flex items-center justify-center shadow-inner shrink-0">
                                <i class="fa-solid fa-calendar-plus text-lg"></i>
                            </div>
                            <div>
                                <h2 class="text-sm font-mono font-bold tracking-widest text-slate-900 dark:text-white uppercase">New Reservation Entry</h2>
                                <p class="text-[10px] font-bold text-slate-400 dark:text-slate-500 uppercase tracking-widest mt-0.5">Capture client details and booking parameters</p>
                            </div>
                        </div>
                        <a href="dashboard.php" class="bg-slate-100 dark:bg-slate-700 hover:bg-slate-200 dark:hover:bg-slate-600 text-slate-600 dark:text-slate-300 font-bold py-2 px-4 rounded-xl text-xs transition">
                            Back to Dashboard
                        </a>
                    </div>

                    <!-- DYNAMIC SUMMARY CONTAINER -->
                    <div id="success-summary-container" class="mb-6"></div>

                    <!-- FORM CONTAINER -->
                    <div class="bg-white dark:bg-slate-800 rounded-2xl shadow-sm border border-slate-100 dark:border-slate-700 p-6 lg:p-8 transition-colors duration-300">
                        <form id="reservationForm" onsubmit="submitReservationForm(event)" class="space-y-6">
                            
                            <!-- Booking Source -->
                            <div class="border-b border-slate-200 dark:border-slate-700 pb-6">
                                <label class="block text-xs font-bold text-slate-500 dark:text-slate-400 uppercase tracking-wider mb-2">Booking Source *</label>
                                <select name="booking_source" id="booking_source" onchange="toggleBookingSource()" required class="w-full max-w-sm bg-slate-50 dark:bg-slate-900 border border-slate-200 dark:border-slate-600 text-slate-700 dark:text-slate-300 text-sm font-bold rounded-xl p-3 outline-none focus:ring-2 focus:ring-[#046a38] transition-colors duration-300">
                                    <option value="Direct Client">👤 Direct Client</option>
                                    <option value="Travel Agency">💼 Travel Agency</option>
                                </select>
                            </div>

                            <!-- TRAVEL AGENCY SPECIFIC FIELDS -->
                            <div id="agency_specific_fields" style="display: none;" class="bg-slate-50 dark:bg-slate-800/50 border border-slate-200 dark:border-slate-700 rounded-xl p-5 mb-6 transition-colors duration-300">
                                <div class="grid grid-cols-1 md:grid-cols-2 gap-4 mb-4">
                                    <div>
                                        <label class="block text-[10px] font-bold text-slate-500 dark:text-slate-400 uppercase tracking-wider mb-1.5">Agency Name *</label>
                                        <input type="text" name="agency_name" placeholder="e.g., Perfect Safaris" class="w-full bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-600 text-slate-700 dark:text-slate-300 rounded-lg p-2.5 text-sm outline-none focus:ring-2 focus:ring-[#046a38] transition-colors duration-300">
                                    </div>
                                    <div>
                                        <label class="block text-[10px] font-bold text-slate-500 dark:text-slate-400 uppercase tracking-wider mb-1.5">Booking Officer *</label>
                                        <input type="text" name="booking_officer" id="booking_officer" value="Front Desk" class="w-full bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-600 text-slate-700 dark:text-slate-300 rounded-lg p-2.5 text-sm outline-none focus:ring-2 focus:ring-[#046a38] transition-colors duration-300">
                                    </div>
                                </div>

                                <div id="agency_discount_wrapper" class="bg-emerald-50 dark:bg-emerald-900/20 p-4 rounded-lg border border-emerald-200 dark:border-emerald-800 transition-colors duration-300">
                                    <label class="block text-[10px] font-bold text-emerald-700 dark:text-emerald-400 uppercase tracking-wider mb-2">
                                        Discount Amount (Per Room, Per Night)
                                    </label>
                                    <input type="number" step="0.01" name="discount" id="discount" placeholder="0.00" value="0.00" class="w-40 bg-white dark:bg-slate-900 border border-emerald-300 dark:border-emerald-700 text-emerald-700 dark:text-emerald-400 font-bold rounded-lg p-2.5 outline-none focus:ring-2 focus:ring-emerald-500 transition-colors duration-300">
                                </div>
                            </div>

                            <!-- Main Grid -->
                            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                                <div>
                                    <label class="block text-[10px] font-bold text-slate-500 dark:text-slate-400 uppercase tracking-wider mb-1.5">Guest Name *</label>
                                    <input type="text" name="guest_name" required class="w-full bg-slate-50 dark:bg-slate-900 border border-slate-200 dark:border-slate-600 text-slate-700 dark:text-slate-300 rounded-lg p-2.5 text-sm outline-none focus:ring-2 focus:ring-[#046a38] transition-colors duration-300">
                                </div>
                                <div>
                                    <label class="block text-[10px] font-bold text-slate-500 dark:text-slate-400 uppercase tracking-wider mb-1.5">Phone Number *</label>
                                    <input type="text" name="phone" required class="w-full bg-slate-50 dark:bg-slate-900 border border-slate-200 dark:border-slate-600 text-slate-700 dark:text-slate-300 rounded-lg p-2.5 text-sm outline-none focus:ring-2 focus:ring-[#046a38] transition-colors duration-300">
                                </div>
                                <div>
                                    <label class="block text-[10px] font-bold text-slate-500 dark:text-slate-400 uppercase tracking-wider mb-1.5">Email</label>
                                    <input type="email" name="email" class="w-full bg-slate-50 dark:bg-slate-900 border border-slate-200 dark:border-slate-600 text-slate-700 dark:text-slate-300 rounded-lg p-2.5 text-sm outline-none focus:ring-2 focus:ring-[#046a38] transition-colors duration-300">
                                </div>
                                <div>
                                    <label class="block text-[10px] font-bold text-slate-500 dark:text-slate-400 uppercase tracking-wider mb-1.5">Number of Guests (Total) *</label>
                                    <input type="number" min="1" name="guests_count" id="guests_count" required class="w-full bg-slate-50 dark:bg-slate-900 border border-slate-200 dark:border-slate-600 text-slate-700 dark:text-slate-300 rounded-lg p-2.5 text-sm outline-none focus:ring-2 focus:ring-[#046a38] transition-colors duration-300">
                                </div>

                                <div>
                                    <label class="block text-[10px] font-bold text-slate-500 dark:text-slate-400 uppercase tracking-wider mb-1.5">Check-in Date *</label>
                                    <input type="date" name="check_in" id="check_in" required class="w-full bg-slate-50 dark:bg-slate-900 border border-slate-200 dark:border-slate-600 text-slate-700 dark:text-slate-300 rounded-lg p-2.5 text-sm outline-none focus:ring-2 focus:ring-[#046a38] transition-colors duration-300">
                                </div>
                                <div>
                                    <label class="block text-[10px] font-bold text-slate-500 dark:text-slate-400 uppercase tracking-wider mb-1.5">Check-out Date *</label>
                                    <input type="date" name="check_out" id="check_out" required class="w-full bg-slate-50 dark:bg-slate-900 border border-slate-200 dark:border-slate-600 text-slate-700 dark:text-slate-300 rounded-lg p-2.5 text-sm outline-none focus:ring-2 focus:ring-[#046a38] transition-colors duration-300">
                                </div>

                                <div>
                                    <label class="block text-[10px] font-bold text-slate-500 dark:text-slate-400 uppercase tracking-wider mb-1.5">Guest Type *</label>
                                    <select name="guest_type" required class="w-full bg-slate-50 dark:bg-slate-900 border border-slate-200 dark:border-slate-600 text-slate-700 dark:text-slate-300 rounded-lg p-2.5 text-sm outline-none focus:ring-2 focus:ring-[#046a38] transition-colors duration-300">
                                        <option value="Resident">Resident (Billed in KES)</option>
                                        <option value="Non Resident">Non Resident (Billed in USD)</option>
                                    </select>
                                </div>
                                <div>
                                    <label class="block text-[10px] font-bold text-slate-500 dark:text-slate-400 uppercase tracking-wider mb-1.5">Room Tier *</label>
                                    <select name="room_tier" required class="w-full bg-slate-50 dark:bg-slate-900 border border-slate-200 dark:border-slate-600 text-slate-700 dark:text-slate-300 rounded-lg p-2.5 text-sm outline-none focus:ring-2 focus:ring-[#046a38] transition-colors duration-300">
                                        <option value="Superior Tent">Superior Tent</option>
                                        <option value="Deluxe Room">Deluxe Room</option>
                                    </select>
                                </div>

                                <div>
                                    <label class="block text-[10px] font-bold text-slate-500 dark:text-slate-400 uppercase tracking-wider mb-1.5">Room Type *</label>
                                    <select name="room_type" required class="w-full bg-slate-50 dark:bg-slate-900 border border-slate-200 dark:border-slate-600 text-slate-700 dark:text-slate-300 rounded-lg p-2.5 text-sm outline-none focus:ring-2 focus:ring-[#046a38] transition-colors duration-300">
                                        <option value="Single Room">Single Room (Max 1 pax)</option>
                                        <option value="Double Room">Double Room (Max 2 pax)</option>
                                        <option value="Triple Room">Triple Room (Max 3 pax)</option>
                                        <option value="Family Room">Family Room (Max 4 pax)</option>
                                    </select>
                                </div>
                                <div>
                                    <label class="block text-[10px] font-bold text-slate-500 dark:text-slate-400 uppercase tracking-wider mb-1.5">Number of Rooms *</label>
                                    <input type="number" min="1" name="rooms_count" value="1" required class="w-full bg-slate-50 dark:bg-slate-900 border border-slate-200 dark:border-slate-600 text-slate-700 dark:text-slate-300 rounded-lg p-2.5 text-sm outline-none focus:ring-2 focus:ring-[#046a38] transition-colors duration-300">
                                </div>
                            </div>

                            <div class="border-b border-slate-200 dark:border-slate-700 pt-4"></div>

                            <div>
                                <h3 class="text-xs font-bold text-slate-800 dark:text-slate-200 uppercase tracking-wider mb-4">Guest Composition (Child Pricing Engine)</h3>
                                <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                                    <div>
                                        <label class="block text-[10px] font-bold text-slate-500 dark:text-slate-400 uppercase tracking-wider mb-1.5">Number of Adults</label>
                                        <input type="number" min="0" name="number_of_adults" value="1" required class="w-full bg-slate-50 dark:bg-slate-900 border border-slate-200 dark:border-slate-600 text-slate-700 dark:text-slate-300 rounded-lg p-2.5 text-sm outline-none focus:ring-2 focus:ring-[#046a38] transition-colors duration-300">
                                    </div>
                                    <div>
                                        <label class="block text-[10px] font-bold text-slate-500 dark:text-slate-400 uppercase tracking-wider mb-1.5">Number of Children</label>
                                        <input type="number" min="0" name="number_of_children" value="0" required class="w-full bg-slate-50 dark:bg-slate-900 border border-slate-200 dark:border-slate-600 text-slate-700 dark:text-slate-300 rounded-lg p-2.5 text-sm outline-none focus:ring-2 focus:ring-[#046a38] transition-colors duration-300">
                                    </div>
                                    <div>
                                        <label class="block text-[10px] font-bold text-slate-500 dark:text-slate-400 uppercase tracking-wider mb-1.5">Children in Own Rooms</label>
                                        <input type="number" min="0" name="children_own_rooms" value="0" class="w-full bg-slate-50 dark:bg-slate-900 border border-slate-200 dark:border-slate-600 text-slate-700 dark:text-slate-300 rounded-lg p-2.5 text-sm outline-none focus:ring-2 focus:ring-[#046a38] transition-colors duration-300">
                                    </div>
                                </div>
                                <div class="mt-4 flex items-center gap-2">
                                    <input type="checkbox" name="children_under_12" id="children_under_12" value="1" checked class="w-4 h-4 text-[#046a38] bg-slate-50 dark:bg-slate-900 border-slate-300 dark:border-slate-600 rounded focus:ring-[#046a38]">
                                    <label for="children_under_12" class="text-xs font-bold text-slate-600 dark:text-slate-300 cursor-pointer">Children under 12 yrs?</label>
                                </div>
                            </div>

                            <!-- NEW SPECIAL REQUESTS TOGGLE -->
                            <div class="pt-2">
                                <label class="flex items-center gap-2 cursor-pointer text-amber-600 dark:text-amber-500 font-bold text-xs mb-3">
                                    <input type="checkbox" name="has_special_requests" id="has_special_requests" onchange="toggleSpecialReqStandalone(this)" class="w-4 h-4 text-amber-500 bg-slate-50 dark:bg-slate-900 border-slate-300 dark:border-slate-600 rounded focus:ring-amber-500">
                                    Include Special Requests (Dietary, Accessibility, etc.)
                                </label>
                                <div id="special_req_wrapper" style="display: none;">
                                    <textarea name="special_requests" id="special_requests" rows="3" placeholder="Enter special requests here..." class="w-full bg-amber-50 dark:bg-amber-900/20 border border-amber-300 dark:border-amber-700 text-slate-700 dark:text-slate-300 rounded-lg p-3 text-sm outline-none focus:ring-2 focus:ring-amber-500 transition-colors duration-300 placeholder-amber-700/40 dark:placeholder-amber-500/40"></textarea>
                                </div>
                            </div>

                            <div class="border-b border-slate-200 dark:border-slate-700 pt-2"></div>

                            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                                <div>
                                    <label class="block text-[10px] font-bold text-slate-500 dark:text-slate-400 uppercase tracking-wider mb-1.5">Receipt Number</label>
                                    <input type="text" name="receipt_no" class="w-full bg-slate-50 dark:bg-slate-900 border border-slate-200 dark:border-slate-600 text-slate-700 dark:text-slate-300 rounded-lg p-2.5 text-sm outline-none focus:ring-2 focus:ring-[#046a38] transition-colors duration-300">
                                </div>
                                <div>
                                    <label class="block text-[10px] font-bold text-slate-500 dark:text-slate-400 uppercase tracking-wider mb-1.5">Deposit Paid</label>
                                    <input type="number" step="0.01" name="deposit_paid" value="0.00" class="w-full bg-slate-50 dark:bg-slate-900 border border-slate-200 dark:border-slate-600 text-slate-700 dark:text-slate-300 rounded-lg p-2.5 text-sm outline-none focus:ring-2 focus:ring-[#046a38] transition-colors duration-300">
                                </div>
                                <div>
                                    <label class="block text-[10px] font-bold text-slate-500 dark:text-slate-400 uppercase tracking-wider mb-1.5">Balance</label>
                                    <input type="text" value="System Override" disabled class="w-full bg-slate-100 dark:bg-slate-800 border border-slate-200 dark:border-slate-700 text-slate-400 dark:text-slate-500 rounded-lg p-2.5 text-sm cursor-not-allowed transition-colors duration-300">
                                </div>
                                <div>
                                    <label class="block text-[10px] font-bold text-slate-500 dark:text-slate-400 uppercase tracking-wider mb-1.5">Booking Status</label>
                                    <select name="status" class="w-full bg-slate-50 dark:bg-slate-900 border border-slate-200 dark:border-slate-600 text-slate-700 dark:text-slate-300 rounded-lg p-2.5 text-sm outline-none focus:ring-2 focus:ring-[#046a38] transition-colors duration-300">
                                        <option value="Reserved">Reserved (Awaiting Payment)</option>
                                        <option value="Booked">Booked (Confirmed)</option>
                                    </select>
                                </div>
                            </div>
                            
                            <div class="pt-6">
                                <button type="submit" class="w-full md:w-auto bg-[#046a38] hover:bg-[#03542c] text-white font-bold py-3 px-8 rounded-xl shadow-md transition-colors duration-300 flex items-center justify-center gap-2">
                                    <i class="fa-solid fa-save"></i> Save Reservation Entry
                                </button>
                            </div>
                        </form>
                    </div>

                </div>
            </div>

            <!-- 4. GLOBAL FOOTER -->
            <?php include 'Includes/footer.php'; ?>

        </main>
    </div>
</body>
</html>