<?php
session_start();
// Security Check: Only Admins should access system settings
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
    <title>System Settings - Rhino Camp</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <script>
        tailwind.config = { darkMode: 'class', }
    </script>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    
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
        <main class="flex-1 overflow-y-auto h-full bg-[#f8fafc] dark:bg-slate-900 transition-colors duration-300 flex flex-col">
            
            <!-- 2. GLOBAL HEADER -->
            <?php include 'Includes/header.php'; ?>

            <!-- 3. SCROLLING MAIN CONTENT CONTAINER -->
            <div class="p-4 lg:p-8 space-y-8 max-w-[1200px] mx-auto w-full flex-1">
                
                <!-- Premium Section Header -->
                <div class="bg-white dark:bg-slate-800 p-6 rounded-2xl custom-shadow border border-slate-100 dark:border-slate-700 flex flex-col sm:flex-row justify-between items-start sm:items-center gap-4 transition-colors duration-300">
                    <div class="flex items-center gap-4">
                        <div class="w-12 h-12 rounded-xl bg-slate-100 dark:bg-slate-900/50 text-slate-600 dark:text-slate-400 flex items-center justify-center shadow-inner shrink-0 border border-slate-200 dark:border-slate-700">
                            <i class="fa-solid fa-gear text-xl"></i>
                        </div>
                        <div>
                            <h1 class="text-base font-black tracking-widest text-slate-900 dark:text-white uppercase">System Preferences</h1>
                            <p class="text-[10px] font-bold text-slate-400 dark:text-slate-500 uppercase tracking-widest mt-0.5">Customize workspace branding, typography, and layout</p>
                        </div>
                    </div>
                    <button type="button" onclick="saveAllSettings()" class="bg-[#046a38] hover:bg-[#03542c] text-white font-black tracking-wide py-3 px-6 rounded-xl text-xs shadow-md transition flex items-center gap-2 w-full sm:w-auto justify-center">
                        <i class="fa-solid fa-cloud-arrow-up"></i> Save All Changes
                    </button>
                </div>

                <form id="system-settings-form" class="space-y-6">
                    
                    <!-- MODULE 1: GLOBAL HEADER BRANDING -->
                    <div class="bg-white dark:bg-slate-800 rounded-2xl custom-shadow border border-slate-100 dark:border-slate-700 overflow-hidden transition-colors duration-300">
                        <div class="bg-slate-50 dark:bg-slate-800/50 p-4 border-b border-slate-100 dark:border-slate-700 transition-colors">
                            <h2 class="text-xs font-black text-slate-800 dark:text-slate-200 uppercase tracking-wider flex items-center gap-2">
                                <i class="fa-solid fa-heading text-blue-500"></i> Global Header Branding
                            </h2>
                        </div>
                        <div class="p-6 grid grid-cols-1 md:grid-cols-2 gap-6">
                            <div>
                                <label class="block text-[10px] font-bold text-slate-500 dark:text-slate-400 uppercase tracking-wider mb-2">Main Title Text</label>
                                <input type="text" id="setting_header_title" value="RHINO TOURIST RESERVATION SYSTEM" class="w-full bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-600 text-slate-900 dark:text-white rounded-xl p-3 text-sm font-bold outline-none focus:ring-2 focus:ring-blue-500 transition-colors">
                            </div>
                            <div>
                                <label class="block text-[10px] font-bold text-slate-500 dark:text-slate-400 uppercase tracking-wider mb-2">Subtitle / Context Text</label>
                                <input type="text" id="setting_header_subtitle" value="Rhino Tourist Camp Front-Desk Operations Ledger Console" class="w-full bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-600 text-slate-900 dark:text-white rounded-xl p-3 text-sm outline-none focus:ring-2 focus:ring-blue-500 transition-colors">
                            </div>
                            <div class="md:col-span-2">
                                <label class="block text-[10px] font-bold text-slate-500 dark:text-slate-400 uppercase tracking-wider mb-2">Header Icon (FontAwesome Class)</label>
                                <div class="flex items-center gap-3">
                                    <div class="w-12 h-12 bg-blue-50 dark:bg-blue-900/30 text-blue-600 dark:text-blue-400 rounded-xl flex items-center justify-center text-xl shrink-0 border border-blue-100 dark:border-blue-800">
                                        <i class="fa-solid fa-hippo" id="preview_header_icon"></i>
                                    </div>
                                    <input type="text" id="setting_header_icon" value="fa-hippo" onkeyup="document.getElementById('preview_header_icon').className = 'fa-solid ' + this.value" class="flex-1 bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-600 text-slate-900 dark:text-white rounded-xl p-3 text-sm font-mono outline-none focus:ring-2 focus:ring-blue-500 transition-colors">
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- MODULE 2: SIDEBAR BRANDING -->
                    <div class="bg-white dark:bg-slate-800 rounded-2xl custom-shadow border border-slate-100 dark:border-slate-700 overflow-hidden transition-colors duration-300">
                        <div class="bg-slate-50 dark:bg-slate-800/50 p-4 border-b border-slate-100 dark:border-slate-700 transition-colors">
                            <h2 class="text-xs font-black text-slate-800 dark:text-slate-200 uppercase tracking-wider flex items-center gap-2">
                                <i class="fa-solid fa-bars-staggered text-emerald-500"></i> Sidebar Navigation Branding
                            </h2>
                        </div>
                        <div class="p-6 grid grid-cols-1 md:grid-cols-2 gap-6">
                            <div>
                                <label class="block text-[10px] font-bold text-slate-500 dark:text-slate-400 uppercase tracking-wider mb-2">Short Title</label>
                                <input type="text" id="setting_sidebar_title" value="Rhino Camp" class="w-full bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-600 text-slate-900 dark:text-white rounded-xl p-3 text-sm font-bold outline-none focus:ring-2 focus:ring-emerald-500 transition-colors">
                            </div>
                            <div>
                                <label class="block text-[10px] font-bold text-slate-500 dark:text-slate-400 uppercase tracking-wider mb-2">Subtitle</label>
                                <input type="text" id="setting_sidebar_subtitle" value="Reservation Suite" class="w-full bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-600 text-slate-900 dark:text-white rounded-xl p-3 text-sm outline-none focus:ring-2 focus:ring-emerald-500 transition-colors">
                            </div>
                            <div class="md:col-span-2">
                                <label class="block text-[10px] font-bold text-slate-500 dark:text-slate-400 uppercase tracking-wider mb-2">Sidebar Icon (FontAwesome Class)</label>
                                <div class="flex items-center gap-3">
                                    <div class="w-12 h-12 bg-emerald-50 dark:bg-emerald-900/30 text-emerald-600 dark:text-emerald-400 rounded-xl flex items-center justify-center text-xl shrink-0 border border-emerald-100 dark:border-emerald-800">
                                        <i class="fa-solid fa-campground" id="preview_sidebar_icon"></i>
                                    </div>
                                    <input type="text" id="setting_sidebar_icon" value="fa-campground" onkeyup="document.getElementById('preview_sidebar_icon').className = 'fa-solid ' + this.value" class="flex-1 bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-600 text-slate-900 dark:text-white rounded-xl p-3 text-sm font-mono outline-none focus:ring-2 focus:ring-emerald-500 transition-colors">
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- MODULE 3: FOOTER SETTINGS -->
                    <div class="bg-white dark:bg-slate-800 rounded-2xl custom-shadow border border-slate-100 dark:border-slate-700 overflow-hidden transition-colors duration-300">
                        <div class="bg-slate-50 dark:bg-slate-800/50 p-4 border-b border-slate-100 dark:border-slate-700 transition-colors">
                            <h2 class="text-xs font-black text-slate-800 dark:text-slate-200 uppercase tracking-wider flex items-center gap-2">
                                <i class="fa-solid fa-shoe-prints text-amber-500"></i> Footer Credentials
                            </h2>
                        </div>
                        <div class="p-6">
                            <label class="block text-[10px] font-bold text-slate-500 dark:text-slate-400 uppercase tracking-wider mb-2">Copyright & Company Name</label>
                            <input type="text" id="setting_footer_text" value="© 2026 RHINO TOURIST CAMP. ALL RIGHTS RESERVED." class="w-full bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-600 text-slate-900 dark:text-white rounded-xl p-3 text-sm outline-none focus:ring-2 focus:ring-amber-500 transition-colors">
                        </div>
                    </div>

                </form>

            </div>

            <!-- 4. GLOBAL FOOTER -->
            <?php include 'Includes/footer.php'; ?>

        </main>
    </div>

    <!-- Dummy script for layout purposes -->
    <script>
        function saveAllSettings() {
            Swal.fire({
                icon: 'info',
                title: 'Layout Only',
                text: 'The backend connection will be built in the next step!',
                confirmButtonColor: '#046a38'
            });
        }
    </script>
</body>
</html>