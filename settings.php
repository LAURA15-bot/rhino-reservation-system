<?php
session_start();
if (!isset($_SESSION['logged_in']) || strtolower($_SESSION['role']) !== 'admin') {
    header("Location: dashboard.php");
    exit;
}
$current_page = basename($_SERVER['PHP_SELF']);
require_once 'Includes/load_settings.php';
$set = $GLOBALS['system_settings'];
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
    
    <!-- External JavaScript Link -->
    <script src="js/settings.js?v=1" defer></script>
    
    <style>
        .custom-shadow { box-shadow: 0 4px 20px -2px rgba(0, 0, 0, 0.05); }
    </style>
</head>
<body class="bg-[#f8fafc] dark:bg-slate-900 text-[#334155] dark:text-slate-200 font-sans antialiased min-h-screen overflow-hidden transition-colors duration-300">
    
    <div class="flex h-screen w-screen overflow-hidden">
        
        <?php include 'Includes/sidebar.php'; ?>

        <main class="flex-1 overflow-y-auto h-full bg-[#f8fafc] dark:bg-slate-900 transition-colors duration-300 flex flex-col">
            
            <?php include 'Includes/header.php'; ?>

            <div class="p-4 lg:p-8 space-y-8 max-w-[1200px] mx-auto w-full flex-1">
                
                <div class="bg-white dark:bg-slate-800 p-6 rounded-2xl custom-shadow border border-slate-100 dark:border-slate-700 flex flex-col sm:flex-row justify-between items-start sm:items-center gap-4 transition-colors duration-300">
                    <div class="flex items-center gap-4">
                        <div class="w-12 h-12 rounded-xl bg-slate-100 dark:bg-slate-900/50 text-slate-600 dark:text-slate-400 flex items-center justify-center shadow-inner shrink-0 border border-slate-200 dark:border-slate-700">
                            <i class="fa-solid fa-gear text-xl"></i>
                        </div>
                        <div>
                            <h1 class="text-base font-black tracking-widest text-slate-900 dark:text-white uppercase">System Preferences</h1>
                            <p class="text-[10px] font-bold text-slate-400 dark:text-slate-500 uppercase tracking-widest mt-0.5">Customize workspace branding, typography, and themes</p>
                        </div>
                    </div>
                    <button type="button" onclick="saveAllSettings()" class="bg-[#046a38] hover:bg-[#03542c] text-white font-black tracking-wide py-3 px-6 rounded-xl text-xs shadow-md transition flex items-center gap-2 w-full sm:w-auto justify-center">
                        <i class="fa-solid fa-cloud-arrow-up"></i> Save All Changes
                    </button>
                </div>

                <form id="system-settings-form" onsubmit="event.preventDefault(); saveAllSettings();" class="space-y-6">
                    
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
                                <input type="text" id="setting_header_title" value="<?php echo htmlspecialchars($set['header_title']); ?>" class="w-full bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-600 text-slate-900 dark:text-white rounded-xl p-3 text-sm font-bold outline-none focus:ring-2 focus:ring-blue-500 transition-colors" required>
                            </div>
                            <div>
                                <label class="block text-[10px] font-bold text-slate-500 dark:text-slate-400 uppercase tracking-wider mb-2">Subtitle / Context Text</label>
                                <input type="text" id="setting_header_subtitle" value="<?php echo htmlspecialchars($set['header_subtitle']); ?>" class="w-full bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-600 text-slate-900 dark:text-white rounded-xl p-3 text-sm outline-none focus:ring-2 focus:ring-blue-500 transition-colors" required>
                            </div>
                            <div class="md:col-span-2">
                                <label class="block text-[10px] font-bold text-slate-500 dark:text-slate-400 uppercase tracking-wider mb-2">Header Icon (FontAwesome Class)</label>
                                <div class="flex items-center gap-3">
                                    <div class="w-12 h-12 bg-blue-50 dark:bg-blue-900/30 text-blue-600 dark:text-blue-400 rounded-xl flex items-center justify-center text-xl shrink-0 border border-blue-100 dark:border-blue-800">
                                        <i class="fa-solid <?php echo htmlspecialchars($set['header_icon']); ?>" id="preview_header_icon"></i>
                                    </div>
                                    <input type="text" id="setting_header_icon" value="<?php echo htmlspecialchars($set['header_icon']); ?>" onkeyup="document.getElementById('preview_header_icon').className = 'fa-solid ' + this.value" class="flex-1 bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-600 text-slate-900 dark:text-white rounded-xl p-3 text-sm font-mono outline-none focus:ring-2 focus:ring-blue-500 transition-colors" required>
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
                                <input type="text" id="setting_sidebar_title" value="<?php echo htmlspecialchars($set['sidebar_title']); ?>" class="w-full bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-600 text-slate-900 dark:text-white rounded-xl p-3 text-sm font-bold outline-none focus:ring-2 focus:ring-emerald-500 transition-colors" required>
                            </div>
                            <div>
                                <label class="block text-[10px] font-bold text-slate-500 dark:text-slate-400 uppercase tracking-wider mb-2">Subtitle</label>
                                <input type="text" id="setting_sidebar_subtitle" value="<?php echo htmlspecialchars($set['sidebar_subtitle']); ?>" class="w-full bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-600 text-slate-900 dark:text-white rounded-xl p-3 text-sm outline-none focus:ring-2 focus:ring-emerald-500 transition-colors" required>
                            </div>
                            <div class="md:col-span-2">
                                <label class="block text-[10px] font-bold text-slate-500 dark:text-slate-400 uppercase tracking-wider mb-2">Sidebar Icon (FontAwesome Class)</label>
                                <div class="flex items-center gap-3">
                                    <div class="w-12 h-12 bg-emerald-50 dark:bg-emerald-900/30 text-emerald-600 dark:text-emerald-400 rounded-xl flex items-center justify-center text-xl shrink-0 border border-emerald-100 dark:border-emerald-800">
                                        <i class="fa-solid <?php echo htmlspecialchars($set['sidebar_icon']); ?>" id="preview_sidebar_icon"></i>
                                    </div>
                                    <input type="text" id="setting_sidebar_icon" value="<?php echo htmlspecialchars($set['sidebar_icon']); ?>" onkeyup="document.getElementById('preview_sidebar_icon').className = 'fa-solid ' + this.value" class="flex-1 bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-600 text-slate-900 dark:text-white rounded-xl p-3 text-sm font-mono outline-none focus:ring-2 focus:ring-emerald-500 transition-colors" required>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- MODULE 3: THEME SCHEME SELECTOR -->
                    <div class="bg-white dark:bg-slate-800 rounded-2xl custom-shadow border border-slate-100 dark:border-slate-700 overflow-hidden transition-colors duration-300">
                        <div class="bg-slate-50 dark:bg-slate-800/50 p-4 border-b border-slate-100 dark:border-slate-700 transition-colors">
                            <h2 class="text-xs font-black text-slate-800 dark:text-slate-200 uppercase tracking-wider flex items-center gap-2">
                                <i class="fa-solid fa-palette text-purple-500"></i> Workspace Theme Scheme
                            </h2>
                        </div>
                        <div class="p-6">
                            <label class="block text-[10px] font-bold text-slate-500 dark:text-slate-400 uppercase tracking-wider mb-3">Select Accent Color Palette</label>
                            <div class="grid grid-cols-2 sm:grid-cols-4 gap-4">
                                <label class="cursor-pointer border-2 rounded-xl p-4 flex flex-col items-center gap-2 transition-all <?php echo $set['theme_color'] === 'emerald' ? 'border-[#046a38] bg-emerald-50/50 dark:bg-emerald-900/20' : 'border-slate-200 dark:border-slate-700'; ?>">
                                    <input type="radio" name="theme_color" value="emerald" <?php echo $set['theme_color'] === 'emerald' ? 'checked' : ''; ?> class="hidden">
                                    <div class="w-8 h-8 rounded-full bg-[#046a38] shadow"></div>
                                    <span class="text-xs font-bold text-slate-800 dark:text-white">Forest Emerald</span>
                                </label>
                                <label class="cursor-pointer border-2 rounded-xl p-4 flex flex-col items-center gap-2 transition-all <?php echo $set['theme_color'] === 'blue' ? 'border-blue-600 bg-blue-50/50 dark:bg-blue-900/20' : 'border-slate-200 dark:border-slate-700'; ?>">
                                    <input type="radio" name="theme_color" value="blue" <?php echo $set['theme_color'] === 'blue' ? 'checked' : ''; ?> class="hidden">
                                    <div class="w-8 h-8 rounded-full bg-blue-600 shadow"></div>
                                    <span class="text-xs font-bold text-slate-800 dark:text-white">Ocean Blue</span>
                                </label>
                                <label class="cursor-pointer border-2 rounded-xl p-4 flex flex-col items-center gap-2 transition-all <?php echo $set['theme_color'] === 'amber' ? 'border-amber-600 bg-amber-50/50 dark:bg-amber-900/20' : 'border-slate-200 dark:border-slate-700'; ?>">
                                    <input type="radio" name="theme_color" value="amber" <?php echo $set['theme_color'] === 'amber' ? 'checked' : ''; ?> class="hidden">
                                    <div class="w-8 h-8 rounded-full bg-amber-500 shadow"></div>
                                    <span class="text-xs font-bold text-slate-800 dark:text-white">Sunset Amber</span>
                                </label>
                                <label class="cursor-pointer border-2 rounded-xl p-4 flex flex-col items-center gap-2 transition-all <?php echo $set['theme_color'] === 'indigo' ? 'border-indigo-600 bg-indigo-50/50 dark:bg-indigo-900/20' : 'border-slate-200 dark:border-slate-700'; ?>">
                                    <input type="radio" name="theme_color" value="indigo" <?php echo $set['theme_color'] === 'indigo' ? 'checked' : ''; ?> class="hidden">
                                    <div class="w-8 h-8 rounded-full bg-indigo-600 shadow"></div>
                                    <span class="text-xs font-bold text-slate-800 dark:text-white">Royal Indigo</span>
                                </label>
                            </div>
                        </div>
                    </div>

                    <!-- MODULE 4: FOOTER SETTINGS -->
                    <div class="bg-white dark:bg-slate-800 rounded-2xl custom-shadow border border-slate-100 dark:border-slate-700 overflow-hidden transition-colors duration-300">
                        <div class="bg-slate-50 dark:bg-slate-800/50 p-4 border-b border-slate-100 dark:border-slate-700 transition-colors">
                            <h2 class="text-xs font-black text-slate-800 dark:text-slate-200 uppercase tracking-wider flex items-center gap-2">
                                <i class="fa-solid fa-shoe-prints text-amber-500"></i> Footer Credentials
                            </h2>
                        </div>
                        <div class="p-6">
                            <label class="block text-[10px] font-bold text-slate-500 dark:text-slate-400 uppercase tracking-wider mb-2">Copyright & Company Name</label>
                            <input type="text" id="setting_footer_text" value="<?php echo htmlspecialchars($set['footer_text']); ?>" class="w-full bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-600 text-slate-900 dark:text-white rounded-xl p-3 text-sm outline-none focus:ring-2 focus:ring-amber-500 transition-colors" required>
                        </div>
                    </div>

                </form>

            </div>

            <?php include 'Includes/footer.php'; ?>

        </main>
    </div>
</body>
</html>