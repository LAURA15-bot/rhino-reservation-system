<?php
session_start();
if (!isset($_SESSION['logged_in']) || strtolower($_SESSION['role']) !== 'admin') {
    header("Location: dashboard.php");
    exit;
}
$current_page = basename($_SERVER['PHP_SELF']);
require_once 'Includes/load_settings.php';
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
    <title>System Settings - Rhino Camp</title>
	
	<!-- Dynamic Favicon Override -->
	<link rel="icon" type="image/png" href="<?php echo !empty($set['logo_path']) ? htmlspecialchars($set['logo_path']) : 'data:image/x-icon;base64,'; ?>">

    <script src="https://cdn.tailwindcss.com"></script>
    <script> tailwind.config = { darkMode: 'class', } </script>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    
    <script src="js/settings.js?v=9" defer></script>
    
    <style>
        :root {
            --theme-color: <?php echo $primaryColor; ?>;
            --theme-color-focus: <?php echo $primaryColor; ?>33;
        }
        .custom-shadow { box-shadow: 0 4px 20px -2px rgba(0, 0, 0, 0.05); }
        input[type="color"] { -webkit-appearance: none; border: none; width: 100%; height: 40px; border-radius: 8px; cursor: pointer; }
        input[type="color"]::-webkit-color-swatch-wrapper { padding: 0; }
        input[type="color"]::-webkit-color-swatch { border: none; border-radius: 8px; }
        .theme-btn { background-color: var(--theme-color); transition: filter 0.2s; }
        .theme-btn:hover { filter: brightness(85%); }
        .theme-focus:focus { outline: none; border-color: var(--theme-color) !important; box-shadow: 0 0 0 3px var(--theme-color-focus) !important; }
    </style>
</head>
<body data-primary-color="<?php echo $primaryColor; ?>" class="bg-[#f8fafc] dark:bg-slate-900 text-[#334155] dark:text-slate-200 font-sans antialiased min-h-screen overflow-hidden transition-colors duration-300">
    
    <div class="flex h-screen w-screen overflow-hidden">
        
        <?php include 'Includes/sidebar.php'; ?>

        <main class="flex-1 overflow-y-auto h-full bg-[#f8fafc] dark:bg-slate-900 transition-colors duration-300 flex flex-col">
            
            <?php include 'Includes/header.php'; ?>

            <div class="p-4 lg:p-8 max-w-[1400px] mx-auto w-full flex-1">
                
                <div class="bg-white dark:bg-slate-800 p-6 rounded-2xl custom-shadow border border-slate-100 dark:border-slate-700 flex items-center gap-4 mb-6">
                    <div class="w-12 h-12 rounded-xl text-white flex items-center justify-center shadow-inner shrink-0" style="background-color: <?php echo $primaryColor; ?>;">
                        <i class="fa-solid fa-sliders text-xl"></i>
                    </div>
                    <div>
                        <h1 class="text-base font-black tracking-widest text-slate-900 dark:text-white uppercase">Control Center & Settings</h1>
                        <p class="text-[10px] font-bold text-slate-400 dark:text-slate-500 uppercase tracking-widest mt-0.5">Manage live website text, company branding, and system protocols</p>
                    </div>
                </div>

                <div class="grid grid-cols-1 lg:grid-cols-4 gap-6">
                    
                    <div class="lg:col-span-1 space-y-2">
                        <div class="bg-white dark:bg-slate-800 p-3 rounded-2xl custom-shadow border border-slate-100 dark:border-slate-700 space-y-1">
                            <button onclick="switchSettingsTab('tab-sidebar', this)" class="settings-tab-btn w-full text-left px-4 py-3 rounded-xl text-xs font-bold flex items-center gap-3 transition-colors text-white shadow-sm" style="background-color: <?php echo $primaryColor; ?>;">
                                <i class="fa-solid fa-bars-staggered w-4 text-center"></i> Sidebar Configuration
                            </button>
                            <button onclick="switchSettingsTab('tab-header', this)" class="settings-tab-btn w-full text-left px-4 py-3 rounded-xl text-xs font-bold flex items-center gap-3 transition-colors text-slate-600 dark:text-slate-400 hover:bg-slate-50 dark:hover:bg-slate-700/50">
                                <i class="fa-solid fa-heading w-4 text-center"></i> Global Header
                            </button>
                            <button onclick="switchSettingsTab('tab-layouts', this)" class="settings-tab-btn w-full text-left px-4 py-3 rounded-xl text-xs font-bold flex items-center gap-3 transition-colors text-slate-600 dark:text-slate-400 hover:bg-slate-50 dark:hover:bg-slate-700/50">
                                <i class="fa-solid fa-file-pdf w-4 text-center"></i> PDF & Print Layouts
                            </button>
                            <button onclick="switchSettingsTab('tab-themes', this)" class="settings-tab-btn w-full text-left px-4 py-3 rounded-xl text-xs font-bold flex items-center gap-3 transition-colors text-slate-600 dark:text-slate-400 hover:bg-slate-50 dark:hover:bg-slate-700/50">
                                <i class="fa-solid fa-palette w-4 text-center"></i> Workspace Themes
                            </button>
                            <button onclick="switchSettingsTab('tab-footer', this)" class="settings-tab-btn w-full text-left px-4 py-3 rounded-xl text-xs font-bold flex items-center gap-3 transition-colors text-slate-600 dark:text-slate-400 hover:bg-slate-50 dark:hover:bg-slate-700/50">
                                <i class="fa-solid fa-shoe-prints w-4 text-center"></i> Footer Credentials
                            </button>
                            <div class="border-t border-slate-100 dark:border-slate-700 my-2"></div>
                            <button onclick="switchSettingsTab('tab-operations', this)" class="settings-tab-btn w-full text-left px-4 py-3 rounded-xl text-xs font-bold flex items-center gap-3 transition-colors text-slate-600 dark:text-slate-400 hover:bg-slate-50 dark:hover:bg-slate-700/50">
                                <i class="fa-solid fa-shield-halved w-4 text-center"></i> System Operations
                            </button>
                        </div>
                    </div>

                    <div class="lg:col-span-3 relative pb-20">
                        
                        <!-- TAB 1: SIDEBAR CONFIGURATION -->
                        <div id="tab-sidebar" class="settings-content-section space-y-4">
                            <div class="bg-white dark:bg-slate-800 rounded-2xl custom-shadow border border-slate-100 dark:border-slate-700 overflow-hidden">
                                <button onclick="toggleAccordion('acc-sidebar-brand', this)" class="w-full px-6 py-4 flex justify-between items-center bg-slate-50/50 dark:bg-slate-800/50 hover:bg-slate-50 dark:hover:bg-slate-700/50 transition-colors border-b border-slate-100 dark:border-slate-700 outline-none">
                                    <h2 class="text-xs font-black text-slate-800 dark:text-slate-200 uppercase tracking-wider flex items-center gap-2">
                                        <i class="fa-solid fa-image text-emerald-500"></i> Sidebar Brand Assets
                                    </h2>
                                    <i class="fa-solid fa-chevron-up text-slate-400 text-sm transition-transform duration-300"></i>
                                </button>
                                
                                <div id="acc-sidebar-brand" class="block">
                                    <form onsubmit="event.preventDefault(); saveSection('Sidebar Brand', this);">
                                        <div class="p-6 space-y-6">
                                            <div>
                                                <label class="block text-[10px] font-bold text-slate-500 dark:text-slate-400 uppercase tracking-wider mb-3">Brand Display Mode</label>
                                                <div class="flex gap-4">
                                                    <label class="flex items-center gap-2 cursor-pointer">
                                                        <input type="radio" name="sidebar_display_type" value="icon" class="w-4 h-4" style="accent-color: <?php echo $primaryColor; ?>;" <?php echo $set['sidebar_display_type'] === 'icon' ? 'checked' : ''; ?> onchange="toggleDisplayMode('sidebar_display_type', 'wrapper_sidebar_icon', 'wrapper_sidebar_logo')">
                                                        <span class="text-sm font-bold text-slate-700 dark:text-slate-300">Use FontAwesome Icon</span>
                                                    </label>
                                                    <label class="flex items-center gap-2 cursor-pointer">
                                                        <input type="radio" name="sidebar_display_type" value="logo" class="w-4 h-4" style="accent-color: <?php echo $primaryColor; ?>;" <?php echo $set['sidebar_display_type'] === 'logo' ? 'checked' : ''; ?> onchange="toggleDisplayMode('sidebar_display_type', 'wrapper_sidebar_icon', 'wrapper_sidebar_logo')">
                                                        <span class="text-sm font-bold text-slate-700 dark:text-slate-300">Upload Image Logo</span>
                                                    </label>
                                                </div>
                                            </div>

                                            <div id="wrapper_sidebar_icon" class="<?php echo $set['sidebar_display_type'] === 'logo' ? 'hidden' : ''; ?> pt-2">
                                                <label class="block text-[10px] font-bold text-slate-500 dark:text-slate-400 uppercase tracking-wider mb-2">Sidebar Icon Class</label>
                                                <div class="flex items-center gap-3">
                                                    <div class="w-12 h-12 bg-slate-100 dark:bg-slate-800 rounded-xl flex items-center justify-center text-xl shrink-0 border border-slate-200 dark:border-slate-700" style="color: <?php echo $primaryColor; ?>;">
                                                        <i class="fa-solid <?php echo htmlspecialchars($set['sidebar_icon']); ?>" id="preview_sidebar_icon"></i>
                                                    </div>
                                                    <input type="text" name="sidebar_icon" value="<?php echo htmlspecialchars($set['sidebar_icon']); ?>" onkeyup="document.getElementById('preview_sidebar_icon').className = 'fa-solid ' + this.value" class="theme-focus flex-1 bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-600 text-slate-900 dark:text-white rounded-xl p-3 text-sm font-mono transition-colors">
                                                </div>
                                            </div>

                                            <div id="wrapper_sidebar_logo" class="<?php echo $set['sidebar_display_type'] === 'icon' ? 'hidden' : ''; ?> pt-2">
                                                <label class="block text-[10px] font-bold text-slate-500 dark:text-slate-400 uppercase tracking-wider mb-2">Upload Sidebar Logo</label>
                                                <?php if (!empty($set['logo_path'])): ?>
                                                    <div class="mb-3 p-3 bg-slate-50 dark:bg-slate-900 border border-slate-200 dark:border-slate-700 rounded-xl inline-block">
                                                        <p class="text-[9px] font-bold text-slate-400 uppercase mb-1">Current Active Logo:</p>
                                                        <img src="<?php echo htmlspecialchars($set['logo_path']); ?>" alt="Current Logo" class="h-10 object-contain">
                                                    </div>
                                                <?php endif; ?>
                                                <label for="sidebar-dropzone" class="theme-focus flex flex-col items-center justify-center w-full h-40 border-2 border-slate-300 dark:border-slate-600 border-dashed rounded-2xl cursor-pointer bg-slate-50 dark:hover:bg-slate-800 dark:bg-slate-900 transition-colors">
                                                    <div class="flex flex-col items-center justify-center pt-5 pb-6">
                                                        <i class="fa-solid fa-cloud-arrow-up text-3xl text-slate-400 mb-3"></i>
                                                        <p class="mb-1 text-sm text-slate-500 dark:text-slate-400 font-bold"><span class="font-black" style="color: <?php echo $primaryColor; ?>;">Click to browse</span> or drag and drop</p>
                                                        <p id="sidebar-file-name" class="mt-2 text-xs font-black text-emerald-600 hidden bg-emerald-50 px-2 py-1 rounded"></p>
                                                    </div>
                                                    <input id="sidebar-dropzone" type="file" name="sidebar_logo_file" class="hidden" accept=".png, .jpg, .jpeg, .svg, .webp" onchange="displayFileName(this, 'sidebar-file-name')" />
                                                </label>
                                            </div>
                                            
                                            <div class="border-t border-slate-100 dark:border-slate-700 pt-6">
                                                <button type="submit" class="w-full theme-btn text-white font-bold py-3 px-6 rounded-xl text-xs shadow-md transition flex justify-center items-center gap-2" style="background-color: <?php echo $primaryColor; ?>;">
                                                    <i class="fa-solid fa-check"></i> Update Brand Assets
                                                </button>
                                            </div>
                                        </div>
                                    </form>
                                </div>
                            </div>

                            <div class="bg-white dark:bg-slate-800 rounded-2xl custom-shadow border border-slate-100 dark:border-slate-700 overflow-hidden">
                                <button onclick="toggleAccordion('acc-sidebar-text', this)" class="w-full px-6 py-4 flex justify-between items-center bg-slate-50/50 dark:bg-slate-800/50 hover:bg-slate-50 dark:hover:bg-slate-700/50 transition-colors border-b border-slate-100 dark:border-slate-700 outline-none">
                                    <h2 class="text-xs font-black text-slate-800 dark:text-slate-200 uppercase tracking-wider flex items-center gap-2">
                                        <i class="fa-solid fa-font text-blue-500"></i> Sidebar Typography
                                    </h2>
                                    <i class="fa-solid fa-chevron-down text-slate-400 text-sm transition-transform duration-300"></i>
                                </button>
                                
                                <div id="acc-sidebar-text" class="hidden">
                                    <form onsubmit="event.preventDefault(); saveSection('Sidebar Texts', this);">
                                        <div class="p-6 space-y-6">
                                            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                                                <div>
                                                    <label class="block text-[10px] font-bold text-slate-500 dark:text-slate-400 uppercase tracking-wider mb-2">Primary Text (Optional)</label>
                                                    <input type="text" name="sidebar_title" value="<?php echo htmlspecialchars($set['sidebar_title']); ?>" placeholder="Leave blank to maximize logo size" class="theme-focus w-full bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-600 text-slate-900 dark:text-white rounded-xl p-3 text-sm font-bold transition-colors">
                                                </div>
                                                <div>
                                                    <label class="block text-[10px] font-bold text-slate-500 dark:text-slate-400 uppercase tracking-wider mb-2">Secondary Subtext (Optional)</label>
                                                    <input type="text" name="sidebar_subtitle" value="<?php echo htmlspecialchars($set['sidebar_subtitle']); ?>" class="theme-focus w-full bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-600 text-slate-900 dark:text-white rounded-xl p-3 text-sm transition-colors">
                                                </div>
                                            </div>
                                            <div class="border-t border-slate-100 dark:border-slate-700 pt-6">
                                                <button type="submit" class="w-full theme-btn text-white font-bold py-3 px-6 rounded-xl text-xs shadow-md transition flex justify-center items-center gap-2" style="background-color: <?php echo $primaryColor; ?>;">
                                                    <i class="fa-solid fa-check"></i> Update Typography
                                                </button>
                                            </div>
                                        </div>
                                    </form>
                                </div>
                            </div>

                            <div class="bg-white dark:bg-slate-800 rounded-2xl custom-shadow border border-slate-100 dark:border-slate-700 overflow-hidden">
                                <button onclick="toggleAccordion('acc-sidebar-links', this)" class="w-full px-6 py-4 flex justify-between items-center bg-slate-50/50 dark:bg-slate-800/50 hover:bg-slate-50 dark:hover:bg-slate-700/50 transition-colors border-b border-slate-100 dark:border-slate-700 outline-none">
                                    <h2 class="text-xs font-black text-slate-800 dark:text-slate-200 uppercase tracking-wider flex items-center gap-2">
                                        <i class="fa-solid fa-list-ul text-amber-500"></i> Core Navigation Links
                                    </h2>
                                    <i class="fa-solid fa-chevron-down text-slate-400 text-sm transition-transform duration-300"></i>
                                </button>
                                
                                <div id="acc-sidebar-links" class="hidden">
                                    <form onsubmit="event.preventDefault(); saveSection('Navigation Links', this);">
                                        <div class="p-6 space-y-4">
                                            <?php 
                                                $navItems = [
                                                    ['key' => 'nav_dashboard', 'label' => 'Dashboard Module'],
                                                    ['key' => 'nav_calendar', 'label' => 'Calendar Matrix Module'],
                                                    ['key' => 'nav_guest', 'label' => 'Guest Register Module'],
                                                    ['key' => 'nav_alerts', 'label' => 'Alerts Module'],
                                                    ['key' => 'nav_rates', 'label' => 'Rates Module'],
                                                    ['key' => 'nav_finance', 'label' => 'Finance Module']
                                                ];
                                                foreach ($navItems as $nav): 
                                                    $nameKey = $nav['key'] . '_name';
                                                    $iconKey = $nav['key'] . '_icon';
                                            ?>
                                                <div class="grid grid-cols-1 sm:grid-cols-12 gap-3 items-center bg-slate-50 dark:bg-slate-900/50 p-3 rounded-xl border border-slate-100 dark:border-slate-700">
                                                    <div class="sm:col-span-3 text-[10px] font-black uppercase tracking-wider text-slate-500 dark:text-slate-400"><?php echo $nav['label']; ?></div>
                                                    <div class="sm:col-span-5">
                                                        <input type="text" name="<?php echo $nameKey; ?>" value="<?php echo htmlspecialchars($set[$nameKey]); ?>" placeholder="Label" class="theme-focus w-full bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-600 text-slate-900 dark:text-white rounded-lg p-2.5 text-sm font-bold transition-colors">
                                                    </div>
                                                    <div class="sm:col-span-4 flex items-center gap-2">
                                                        <div class="w-10 h-10 flex items-center justify-center shrink-0 bg-slate-200 dark:bg-slate-800 text-slate-600 dark:text-slate-300 rounded-lg shadow-inner"><i class="fa-solid <?php echo htmlspecialchars($set[$iconKey]); ?>" id="preview_<?php echo $iconKey; ?>"></i></div>
                                                        <input type="text" name="<?php echo $iconKey; ?>" value="<?php echo htmlspecialchars($set[$iconKey]); ?>" placeholder="fa-icon" onkeyup="document.getElementById('preview_<?php echo $iconKey; ?>').className = 'fa-solid ' + this.value" class="theme-focus w-full bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-600 text-slate-900 dark:text-white rounded-lg p-2.5 text-sm font-mono transition-colors">
                                                    </div>
                                                </div>
                                            <?php endforeach; ?>
                                            
                                            <div class="border-t border-slate-100 dark:border-slate-700 pt-6">
                                                <button type="submit" class="w-full theme-btn text-white font-bold py-3 px-6 rounded-xl text-xs shadow-md transition flex justify-center items-center gap-2" style="background-color: <?php echo $primaryColor; ?>;">
                                                    <i class="fa-solid fa-check"></i> Apply Navigation Changes
                                                </button>
                                            </div>
                                        </div>
                                    </form>
                                </div>
                            </div>
                        </div>

                        <!-- TAB 2: GLOBAL HEADER CONFIGURATION -->
                        <div id="tab-header" class="settings-content-section space-y-4 hidden">
                            <div class="bg-white dark:bg-slate-800 rounded-2xl custom-shadow border border-slate-100 dark:border-slate-700 overflow-hidden">
                                <button onclick="toggleAccordion('acc-header-brand', this)" class="w-full px-6 py-4 flex justify-between items-center bg-slate-50/50 dark:bg-slate-800/50 hover:bg-slate-50 dark:hover:bg-slate-700/50 transition-colors border-b border-slate-100 dark:border-slate-700 outline-none">
                                    <h2 class="text-xs font-black text-slate-800 dark:text-slate-200 uppercase tracking-wider flex items-center gap-2">
                                        <i class="fa-solid fa-image text-emerald-500"></i> Global Header Brand Assets
                                    </h2>
                                    <i class="fa-solid fa-chevron-up text-slate-400 text-sm transition-transform duration-300"></i>
                                </button>
                                
                                <div id="acc-header-brand" class="block">
                                    <form onsubmit="event.preventDefault(); saveSection('Header Brand', this);">
                                        <div class="p-6 space-y-6">
                                            <div>
                                                <label class="block text-[10px] font-bold text-slate-500 dark:text-slate-400 uppercase tracking-wider mb-3">Header Display Mode</label>
                                                <div class="flex gap-4">
                                                    <label class="flex items-center gap-2 cursor-pointer">
                                                        <input type="radio" name="header_display_type" value="icon" class="w-4 h-4" style="accent-color: <?php echo $primaryColor; ?>;" <?php echo $set['header_display_type'] === 'icon' ? 'checked' : ''; ?> onchange="toggleDisplayMode('header_display_type', 'wrapper_header_icon', 'wrapper_header_logo')">
                                                        <span class="text-sm font-bold text-slate-700 dark:text-slate-300">Use FontAwesome Icon</span>
                                                    </label>
                                                    <label class="flex items-center gap-2 cursor-pointer">
                                                        <input type="radio" name="header_display_type" value="logo" class="w-4 h-4" style="accent-color: <?php echo $primaryColor; ?>;" <?php echo $set['header_display_type'] === 'logo' ? 'checked' : ''; ?> onchange="toggleDisplayMode('header_display_type', 'wrapper_header_icon', 'wrapper_header_logo')">
                                                        <span class="text-sm font-bold text-slate-700 dark:text-slate-300">Upload Header Logo</span>
                                                    </label>
                                                </div>
                                            </div>

                                            <div id="wrapper_header_icon" class="<?php echo $set['header_display_type'] === 'logo' ? 'hidden' : ''; ?> pt-2">
                                                <label class="block text-[10px] font-bold text-slate-500 dark:text-slate-400 uppercase tracking-wider mb-2">Header Icon Class</label>
                                                <div class="flex items-center gap-3">
                                                    <div class="w-12 h-12 bg-slate-100 dark:bg-slate-800 rounded-xl flex items-center justify-center text-xl shrink-0 border border-slate-200 dark:border-slate-700" style="color: <?php echo $primaryColor; ?>;">
                                                        <i class="fa-solid <?php echo htmlspecialchars($set['header_icon']); ?>" id="preview_header_icon"></i>
                                                    </div>
                                                    <input type="text" name="header_icon" value="<?php echo htmlspecialchars($set['header_icon']); ?>" onkeyup="document.getElementById('preview_header_icon').className = 'fa-solid ' + this.value" class="theme-focus flex-1 bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-600 text-slate-900 dark:text-white rounded-xl p-3 text-sm font-mono transition-colors">
                                                </div>
                                            </div>

                                            <div id="wrapper_header_logo" class="<?php echo $set['header_display_type'] === 'icon' ? 'hidden' : ''; ?> pt-2">
                                                <label class="block text-[10px] font-bold text-slate-500 dark:text-slate-400 uppercase tracking-wider mb-2">Upload Header Image</label>
                                                <?php if (!empty($set['header_logo_path'])): ?>
                                                    <div class="mb-3 p-3 bg-slate-50 dark:bg-slate-900 border border-slate-200 dark:border-slate-700 rounded-xl inline-block">
                                                        <img src="<?php echo htmlspecialchars($set['header_logo_path']); ?>" alt="Current Header Logo" class="h-10 object-contain">
                                                    </div>
                                                <?php endif; ?>
                                                <label for="header-dropzone" class="theme-focus flex flex-col items-center justify-center w-full h-40 border-2 border-slate-300 dark:border-slate-600 border-dashed rounded-2xl cursor-pointer bg-slate-50 dark:hover:bg-slate-800 dark:bg-slate-900 transition-colors">
                                                    <div class="flex flex-col items-center justify-center pt-5 pb-6">
                                                        <i class="fa-solid fa-cloud-arrow-up text-3xl text-slate-400 mb-3"></i>
                                                        <p class="mb-1 text-sm text-slate-500 dark:text-slate-400 font-bold"><span class="font-black" style="color: <?php echo $primaryColor; ?>;">Click to browse</span> or drag and drop</p>
                                                        <p id="header-file-name" class="mt-2 text-xs font-black text-emerald-600 hidden bg-emerald-50 px-2 py-1 rounded"></p>
                                                    </div>
                                                    <input id="header-dropzone" type="file" name="header_logo_file" class="hidden" accept=".png, .jpg, .jpeg, .svg, .webp" onchange="displayFileName(this, 'header-file-name')" />
                                                </label>
                                            </div>
                                            
                                            <div class="border-t border-slate-100 dark:border-slate-700 pt-6">
                                                <button type="submit" class="w-full theme-btn text-white font-bold py-3 px-6 rounded-xl text-xs shadow-md transition flex justify-center items-center gap-2" style="background-color: <?php echo $primaryColor; ?>;">
                                                    <i class="fa-solid fa-check"></i> Update Header Brand Assets
                                                </button>
                                            </div>
                                        </div>
                                    </form>
                                </div>
                            </div>

                            <div class="bg-white dark:bg-slate-800 rounded-2xl custom-shadow border border-slate-100 dark:border-slate-700 overflow-hidden">
                                <button onclick="toggleAccordion('acc-header-text', this)" class="w-full px-6 py-4 flex justify-between items-center bg-slate-50/50 dark:bg-slate-800/50 hover:bg-slate-50 dark:hover:bg-slate-700/50 transition-colors border-b border-slate-100 dark:border-slate-700 outline-none">
                                    <h2 class="text-xs font-black text-slate-800 dark:text-slate-200 uppercase tracking-wider flex items-center gap-2">
                                        <i class="fa-solid fa-font text-blue-500"></i> Header Typography
                                    </h2>
                                    <i class="fa-solid fa-chevron-down text-slate-400 text-sm transition-transform duration-300"></i>
                                </button>
                                
                                <div id="acc-header-text" class="hidden">
                                    <form onsubmit="event.preventDefault(); saveSection('Header Details', this);">
                                        <div class="p-6 space-y-6">
                                            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                                                <div>
                                                    <label class="block text-[10px] font-bold text-slate-500 dark:text-slate-400 uppercase tracking-wider mb-2">Main Title Text (Required)</label>
                                                    <input type="text" name="header_title" value="<?php echo htmlspecialchars($set['header_title']); ?>" class="theme-focus w-full bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-600 text-slate-900 dark:text-white rounded-xl p-3 text-sm font-bold transition-colors" required>
                                                </div>
                                                <div>
                                                    <label class="block text-[10px] font-bold text-slate-500 dark:text-slate-400 uppercase tracking-wider mb-2">Subtitle (Optional)</label>
                                                    <input type="text" name="header_subtitle" value="<?php echo htmlspecialchars($set['header_subtitle']); ?>" class="theme-focus w-full bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-600 text-slate-900 dark:text-white rounded-xl p-3 text-sm transition-colors">
                                                </div>
                                            </div>

                                            <div class="border-t border-slate-100 dark:border-slate-700 pt-6">
                                                <button type="submit" class="w-full theme-btn text-white font-bold py-3 px-6 rounded-xl text-xs shadow-md transition flex justify-center items-center gap-2" style="background-color: <?php echo $primaryColor; ?>;">
                                                    <i class="fa-solid fa-check"></i> Apply Header Text
                                                </button>
                                            </div>
                                        </div>
                                    </form>
                                </div>
                            </div>
                        </div>

                        <!-- TAB 3: PDF & PRINT LAYOUTS -->
                        <div id="tab-layouts" class="settings-content-section space-y-4 hidden">
                            <div class="bg-white dark:bg-slate-800 rounded-2xl custom-shadow border border-slate-100 dark:border-slate-700 overflow-hidden">
                                <button onclick="toggleAccordion('acc-rack-rates', this)" class="w-full px-6 py-4 flex justify-between items-center bg-slate-50/50 dark:bg-slate-800/50 hover:bg-slate-50 dark:hover:bg-slate-700/50 transition-colors border-b border-slate-100 dark:border-slate-700 outline-none">
                                    <h2 class="text-xs font-black text-slate-800 dark:text-slate-200 uppercase tracking-wider flex items-center gap-2">
                                        <i class="fa-solid fa-tags text-rose-500"></i> Rack Rates PDF Layout
                                    </h2>
                                    <i class="fa-solid fa-chevron-up text-slate-400 text-sm transition-transform duration-300"></i>
                                </button>
                                
                                <div id="acc-rack-rates" class="block">
                                    <form onsubmit="event.preventDefault(); saveSection('Rack Rates PDF Layout', this);">
                                        <div class="p-6 space-y-6">
                                            <div>
                                                <label class="block text-[10px] font-bold text-slate-500 dark:text-slate-400 uppercase tracking-wider mb-2">Upload Header Graphic (Optional)</label>
                                                <?php if (!empty($set['rack_rates_header_path'])): ?>
                                                    <div class="mb-3 p-3 bg-slate-50 dark:bg-slate-900 border border-slate-200 dark:border-slate-700 rounded-xl inline-block w-full">
                                                        <p class="text-[9px] font-bold text-slate-400 uppercase mb-1">Current Header Image:</p>
                                                        <img src="<?php echo htmlspecialchars($set['rack_rates_header_path']); ?>" alt="Rack Rates Header" class="h-16 w-full object-contain bg-white rounded">
                                                    </div>
                                                <?php endif; ?>
                                                <label for="rack-header-dropzone" class="theme-focus flex flex-col items-center justify-center w-full h-32 border-2 border-slate-300 dark:border-slate-600 border-dashed rounded-2xl cursor-pointer bg-slate-50 dark:hover:bg-slate-800 dark:bg-slate-900 transition-colors">
                                                    <div class="flex flex-col items-center justify-center pt-5 pb-6">
                                                        <i class="fa-solid fa-image text-2xl text-slate-400 mb-2"></i>
                                                        <p class="mb-1 text-sm text-slate-500 dark:text-slate-400 font-bold"><span class="font-black" style="color: <?php echo $primaryColor; ?>;">Browse Header Image</span></p>
                                                        <p id="rack-header-file-name" class="mt-1 text-xs font-black text-emerald-600 hidden bg-emerald-50 px-2 py-1 rounded"></p>
                                                    </div>
                                                    <input id="rack-header-dropzone" type="file" name="rack_header_file" class="hidden" accept=".png, .jpg, .jpeg, .webp" onchange="displayFileName(this, 'rack-header-file-name')" />
                                                </label>
                                            </div>
                                            <div>
                                                <label class="block text-[10px] font-bold text-slate-500 dark:text-slate-400 uppercase tracking-wider mb-2">Upload Footer Graphic (Optional)</label>
                                                <?php if (!empty($set['rack_rates_footer_path'])): ?>
                                                    <div class="mb-3 p-3 bg-slate-50 dark:bg-slate-900 border border-slate-200 dark:border-slate-700 rounded-xl inline-block w-full">
                                                        <p class="text-[9px] font-bold text-slate-400 uppercase mb-1">Current Footer Image:</p>
                                                        <img src="<?php echo htmlspecialchars($set['rack_rates_footer_path']); ?>" alt="Rack Rates Footer" class="h-16 w-full object-contain bg-white rounded">
                                                    </div>
                                                <?php endif; ?>
                                                <label for="rack-footer-dropzone" class="theme-focus flex flex-col items-center justify-center w-full h-32 border-2 border-slate-300 dark:border-slate-600 border-dashed rounded-2xl cursor-pointer bg-slate-50 dark:hover:bg-slate-800 dark:bg-slate-900 transition-colors">
                                                    <div class="flex flex-col items-center justify-center pt-5 pb-6">
                                                        <i class="fa-solid fa-image text-2xl text-slate-400 mb-2"></i>
                                                        <p class="mb-1 text-sm text-slate-500 dark:text-slate-400 font-bold"><span class="font-black" style="color: <?php echo $primaryColor; ?>;">Browse Footer Image</span></p>
                                                        <p id="rack-footer-file-name" class="mt-1 text-xs font-black text-emerald-600 hidden bg-emerald-50 px-2 py-1 rounded"></p>
                                                    </div>
                                                    <input id="rack-footer-dropzone" type="file" name="rack_footer_file" class="hidden" accept=".png, .jpg, .jpeg, .webp" onchange="displayFileName(this, 'rack-footer-file-name')" />
                                                </label>
                                            </div>
                                            <div class="border-t border-slate-100 dark:border-slate-700 pt-6">
                                                <button type="submit" class="w-full theme-btn text-white font-bold py-3 px-6 rounded-xl text-xs shadow-md transition flex justify-center items-center gap-2" style="background-color: <?php echo $primaryColor; ?>;">
                                                    <i class="fa-solid fa-check"></i> Update Rack Rates PDF Settings
                                                </button>
                                            </div>
                                        </div>
                                    </form>
                                </div>
                            </div>

                            <div class="bg-white dark:bg-slate-800 rounded-2xl custom-shadow border border-slate-100 dark:border-slate-700 overflow-hidden">
                                <button onclick="toggleAccordion('acc-receipts', this)" class="w-full px-6 py-4 flex justify-between items-center bg-slate-50/50 dark:bg-slate-800/50 hover:bg-slate-50 dark:hover:bg-slate-700/50 transition-colors border-b border-slate-100 dark:border-slate-700 outline-none">
                                    <h2 class="text-xs font-black text-slate-800 dark:text-slate-200 uppercase tracking-wider flex items-center gap-2">
                                        <i class="fa-solid fa-receipt text-amber-500"></i> Client Receipts PDF Layout
                                    </h2>
                                    <i class="fa-solid fa-chevron-down text-slate-400 text-sm transition-transform duration-300"></i>
                                </button>
                                
                                <div id="acc-receipts" class="hidden">
                                    <form onsubmit="event.preventDefault(); saveSection('Receipt PDF Layout', this);">
                                        <div class="p-6 space-y-6">
                                            <div>
                                                <label class="block text-[10px] font-bold text-slate-500 dark:text-slate-400 uppercase tracking-wider mb-2">Upload Header Graphic (Optional)</label>
                                                <?php if (!empty($set['receipt_header_path'])): ?>
                                                    <div class="mb-3 p-3 bg-slate-50 dark:bg-slate-900 border border-slate-200 dark:border-slate-700 rounded-xl inline-block w-full">
                                                        <p class="text-[9px] font-bold text-slate-400 uppercase mb-1">Current Header Image:</p>
                                                        <img src="<?php echo htmlspecialchars($set['receipt_header_path']); ?>" alt="Receipt Header" class="h-16 w-full object-contain bg-white rounded">
                                                    </div>
                                                <?php endif; ?>
                                                <label for="receipt-header-dropzone" class="theme-focus flex flex-col items-center justify-center w-full h-32 border-2 border-slate-300 dark:border-slate-600 border-dashed rounded-2xl cursor-pointer bg-slate-50 dark:hover:bg-slate-800 dark:bg-slate-900 transition-colors">
                                                    <div class="flex flex-col items-center justify-center pt-5 pb-6">
                                                        <i class="fa-solid fa-image text-2xl text-slate-400 mb-2"></i>
                                                        <p class="mb-1 text-sm text-slate-500 dark:text-slate-400 font-bold"><span class="font-black" style="color: <?php echo $primaryColor; ?>;">Browse Header Image</span></p>
                                                        <p id="receipt-header-file-name" class="mt-1 text-xs font-black text-emerald-600 hidden bg-emerald-50 px-2 py-1 rounded"></p>
                                                    </div>
                                                    <input id="receipt-header-dropzone" type="file" name="receipt_header_file" class="hidden" accept=".png, .jpg, .jpeg, .webp" onchange="displayFileName(this, 'receipt-header-file-name')" />
                                                </label>
                                            </div>
                                            <div>
                                                <label class="block text-[10px] font-bold text-slate-500 dark:text-slate-400 uppercase tracking-wider mb-2">Upload Footer Graphic (Optional)</label>
                                                <?php if (!empty($set['receipt_footer_path'])): ?>
                                                    <div class="mb-3 p-3 bg-slate-50 dark:bg-slate-900 border border-slate-200 dark:border-slate-700 rounded-xl inline-block w-full">
                                                        <p class="text-[9px] font-bold text-slate-400 uppercase mb-1">Current Footer Image:</p>
                                                        <img src="<?php echo htmlspecialchars($set['receipt_footer_path']); ?>" alt="Receipt Footer" class="h-16 w-full object-contain bg-white rounded">
                                                    </div>
                                                <?php endif; ?>
                                                <label for="receipt-footer-dropzone" class="theme-focus flex flex-col items-center justify-center w-full h-32 border-2 border-slate-300 dark:border-slate-600 border-dashed rounded-2xl cursor-pointer bg-slate-50 dark:hover:bg-slate-800 dark:bg-slate-900 transition-colors">
                                                    <div class="flex flex-col items-center justify-center pt-5 pb-6">
                                                        <i class="fa-solid fa-image text-2xl text-slate-400 mb-2"></i>
                                                        <p class="mb-1 text-sm text-slate-500 dark:text-slate-400 font-bold"><span class="font-black" style="color: <?php echo $primaryColor; ?>;">Browse Footer Image</span></p>
                                                        <p id="receipt-footer-file-name" class="mt-1 text-xs font-black text-emerald-600 hidden bg-emerald-50 px-2 py-1 rounded"></p>
                                                    </div>
                                                    <input id="receipt-footer-dropzone" type="file" name="receipt_footer_file" class="hidden" accept=".png, .jpg, .jpeg, .webp" onchange="displayFileName(this, 'receipt-footer-file-name')" />
                                                </label>
                                            </div>
                                            <div class="border-t border-slate-100 dark:border-slate-700 pt-6">
                                                <button type="submit" class="w-full theme-btn text-white font-bold py-3 px-6 rounded-xl text-xs shadow-md transition flex justify-center items-center gap-2" style="background-color: <?php echo $primaryColor; ?>;">
                                                    <i class="fa-solid fa-check"></i> Update Receipt PDF Settings
                                                </button>
                                            </div>
                                        </div>
                                    </form>
                                </div>
                            </div>
                            
                            <div class="bg-white dark:bg-slate-800 rounded-2xl custom-shadow border border-slate-100 dark:border-slate-700 overflow-hidden mt-4">
                                <button onclick="toggleAccordion('acc-watermark', this)" class="w-full px-6 py-4 flex justify-between items-center bg-slate-50/50 dark:bg-slate-800/50 hover:bg-slate-50 dark:hover:bg-slate-700/50 transition-colors border-b border-slate-100 dark:border-slate-700 outline-none">
                                    <h2 class="text-xs font-black text-slate-800 dark:text-slate-200 uppercase tracking-wider flex items-center gap-2">
                                        <i class="fa-solid fa-stamp text-indigo-500"></i> Document Watermark Settings
                                    </h2>
                                    <i class="fa-solid fa-chevron-down text-slate-400 text-sm transition-transform duration-300"></i>
                                </button>
                                
                                <div id="acc-watermark" class="hidden">
                                    <form onsubmit="event.preventDefault(); saveSection('Document Watermark', this);">
                                        <div class="p-6 space-y-6">
                                            <div>
                                                <label class="block text-[10px] font-bold text-slate-500 dark:text-slate-400 uppercase tracking-wider mb-3">Watermark Type</label>
                                                <div class="flex gap-4">
                                                    <label class="flex items-center gap-2 cursor-pointer">
                                                        <input type="radio" name="watermark_type" value="text" class="w-4 h-4" style="accent-color: <?php echo $primaryColor; ?>;" <?php echo ($set['watermark_type'] ?? 'text') === 'text' ? 'checked' : ''; ?> onchange="document.getElementById('wrapper_watermark_text').classList.remove('hidden'); document.getElementById('wrapper_watermark_image').classList.add('hidden');">
                                                        <span class="text-sm font-bold text-slate-700 dark:text-slate-300">Custom Text</span>
                                                    </label>
                                                    <label class="flex items-center gap-2 cursor-pointer">
                                                        <input type="radio" name="watermark_type" value="image" class="w-4 h-4" style="accent-color: <?php echo $primaryColor; ?>;" <?php echo ($set['watermark_type'] ?? '') === 'image' ? 'checked' : ''; ?> onchange="document.getElementById('wrapper_watermark_text').classList.add('hidden'); document.getElementById('wrapper_watermark_image').classList.remove('hidden');">
                                                        <span class="text-sm font-bold text-slate-700 dark:text-slate-300">Image / Logo</span>
                                                    </label>
                                                </div>
                                            </div>
                                            <div id="wrapper_watermark_text" class="<?php echo ($set['watermark_type'] ?? 'text') === 'image' ? 'hidden' : ''; ?> pt-2">
                                                <label class="block text-[10px] font-bold text-slate-500 dark:text-slate-400 uppercase tracking-wider mb-2">Watermark Text</label>
                                                <input type="text" name="watermark_text" value="<?php echo htmlspecialchars($set['watermark_text'] ?? 'Rhino Tourist Camp'); ?>" class="theme-focus w-full bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-600 text-slate-900 dark:text-white rounded-xl p-3 text-sm font-bold transition-colors">
                                                <p class="text-[9px] text-slate-400 mt-2 italic">This text will be scaled and rotated diagonally in the background of your PDFs.</p>
                                            </div>
                                            <div id="wrapper_watermark_image" class="<?php echo ($set['watermark_type'] ?? 'text') === 'text' ? 'hidden' : ''; ?> pt-2">
                                                <label class="block text-[10px] font-bold text-slate-500 dark:text-slate-400 uppercase tracking-wider mb-2">Upload Watermark Image</label>
                                                <?php if (!empty($set['watermark_image_path'])): ?>
                                                    <div class="mb-3 p-3 bg-slate-50 dark:bg-slate-900 border border-slate-200 dark:border-slate-700 rounded-xl inline-block w-full">
                                                        <p class="text-[9px] font-bold text-slate-400 uppercase mb-1">Current Watermark Image:</p>
                                                        <img src="<?php echo htmlspecialchars($set['watermark_image_path']); ?>" alt="Watermark Image" class="h-16 object-contain bg-white rounded">
                                                    </div>
                                                <?php endif; ?>
                                                <label for="watermark-dropzone" class="theme-focus flex flex-col items-center justify-center w-full h-32 border-2 border-slate-300 dark:border-slate-600 border-dashed rounded-2xl cursor-pointer bg-slate-50 dark:hover:bg-slate-800 dark:bg-slate-900 transition-colors">
                                                    <div class="flex flex-col items-center justify-center pt-5 pb-6">
                                                        <i class="fa-solid fa-image text-2xl text-slate-400 mb-2"></i>
                                                        <p class="mb-1 text-sm text-slate-500 dark:text-slate-400 font-bold"><span class="font-black" style="color: <?php echo $primaryColor; ?>;">Browse Image</span></p>
                                                        <p id="watermark-file-name" class="mt-1 text-xs font-black text-emerald-600 hidden bg-emerald-50 px-2 py-1 rounded"></p>
                                                    </div>
                                                    <input id="watermark-dropzone" type="file" name="watermark_image_file" class="hidden" accept=".png, .jpg, .jpeg, .webp" onchange="displayFileName(this, 'watermark-file-name')" />
                                                </label>
                                            </div>
                                            <div class="border-t border-slate-100 dark:border-slate-700 pt-6">
                                                <button type="submit" class="w-full theme-btn text-white font-bold py-3 px-6 rounded-xl text-xs shadow-md transition flex justify-center items-center gap-2" style="background-color: <?php echo $primaryColor; ?>;">
                                                    <i class="fa-solid fa-check"></i> Save Watermark Settings
                                                </button>
                                            </div>
                                        </div>
                                    </form>
                                </div>
                            </div>
                        </div>

                        <!-- TAB 4: WORKSPACE THEMES -->
                        <div id="tab-themes" class="settings-content-section space-y-4 hidden">
                            <form onsubmit="event.preventDefault(); saveSection('Theme Colors', this);" class="bg-white dark:bg-slate-800 rounded-2xl custom-shadow border border-slate-100 dark:border-slate-700 overflow-hidden">
                                <div class="bg-slate-50 dark:bg-slate-800/50 p-4 border-b border-slate-100 dark:border-slate-700">
                                    <h2 class="text-xs font-black text-slate-800 dark:text-slate-200 uppercase tracking-wider flex items-center gap-2">
                                        <i class="fa-solid fa-palette text-purple-500"></i> Workspace Color Schemes
                                    </h2>
                                </div>
                                <div class="p-6">
                                    <div class="grid grid-cols-2 md:grid-cols-3 gap-4 border-b border-slate-100 dark:border-slate-700 pb-6 mb-6">
                                        <label class="theme-option-label cursor-pointer border-2 rounded-xl p-4 flex flex-col items-center text-center gap-2 transition-all <?php echo $set['theme_color'] === 'safari' ? 'border-[#8B3C28] bg-amber-50/50 dark:bg-amber-900/20' : 'border-slate-200 dark:border-slate-700'; ?>" onclick="toggleCustomHexFields(false); selectThemeUI('safari')">
                                            <input type="radio" name="theme_color" value="safari" <?php echo $set['theme_color'] === 'safari' ? 'checked' : ''; ?> class="hidden">
                                            <div class="flex gap-1"><div class="w-6 h-6 rounded-full bg-[#8B3C28] shadow"></div><div class="w-6 h-6 rounded-full bg-[#E6C556] shadow"></div></div>
                                            <span class="text-xs font-bold text-slate-800 dark:text-white">Rhino Safari<br><span class="font-normal text-[9px] text-slate-400">Terracotta & Gold</span></span>
                                        </label>
                                        <label class="theme-option-label cursor-pointer border-2 rounded-xl p-4 flex flex-col items-center text-center gap-2 transition-all <?php echo $set['theme_color'] === 'kairi' ? 'border-[#802b1f] bg-red-50/50 dark:bg-red-900/20' : 'border-slate-200 dark:border-slate-700'; ?>" onclick="toggleCustomHexFields(false); selectThemeUI('kairi')">
                                            <input type="radio" name="theme_color" value="kairi" <?php echo $set['theme_color'] === 'kairi' ? 'checked' : ''; ?> class="hidden">
                                            <div class="flex gap-1"><div class="w-6 h-6 rounded-full bg-[#802b1f] shadow"></div><div class="w-6 h-6 rounded-full bg-[#e6b800] shadow"></div></div>
                                            <span class="text-xs font-bold text-slate-800 dark:text-white">Kairi Tours<br><span class="font-normal text-[9px] text-slate-400">Burgundy & Gold</span></span>
                                        </label>
                                        <label class="theme-option-label cursor-pointer border-2 rounded-xl p-4 flex flex-col items-center text-center gap-2 transition-all <?php echo $set['theme_color'] === 'emerald' ? 'border-[#046a38] bg-emerald-50/50 dark:bg-emerald-900/20' : 'border-slate-200 dark:border-slate-700'; ?>" onclick="toggleCustomHexFields(false); selectThemeUI('emerald')">
                                            <input type="radio" name="theme_color" value="emerald" <?php echo $set['theme_color'] === 'emerald' ? 'checked' : ''; ?> class="hidden">
                                            <div class="w-6 h-6 rounded-full bg-[#046a38] shadow"></div>
                                            <span class="text-xs font-bold text-slate-800 dark:text-white">Forest Emerald<br><span class="font-normal text-[9px] text-slate-400">Classic Green</span></span>
                                        </label>
                                        <label class="theme-option-label cursor-pointer border-2 rounded-xl p-4 flex flex-col items-center text-center gap-2 transition-all <?php echo $set['theme_color'] === 'blue' ? 'border-[#2563eb] bg-blue-50/50 dark:bg-blue-900/20' : 'border-slate-200 dark:border-slate-700'; ?>" onclick="toggleCustomHexFields(false); selectThemeUI('blue')">
                                            <input type="radio" name="theme_color" value="blue" <?php echo $set['theme_color'] === 'blue' ? 'checked' : ''; ?> class="hidden">
                                            <div class="w-6 h-6 rounded-full bg-[#2563eb] shadow"></div>
                                            <span class="text-xs font-bold text-slate-800 dark:text-white">Ocean Blue<br><span class="font-normal text-[9px] text-slate-400">Standard Blue</span></span>
                                        </label>
                                        <label class="theme-option-label cursor-pointer border-2 rounded-xl p-4 flex flex-col items-center text-center gap-2 transition-all <?php echo $set['theme_color'] === 'custom' ? 'border-slate-800 dark:border-slate-400 bg-slate-100 dark:bg-slate-800' : 'border-slate-200 dark:border-slate-700'; ?>" onclick="toggleCustomHexFields(true); selectThemeUI('custom')">
                                            <input type="radio" name="theme_color" value="custom" <?php echo $set['theme_color'] === 'custom' ? 'checked' : ''; ?> class="hidden">
                                            <div class="w-6 h-6 rounded-full bg-[conic-gradient(at_top_right,_var(--tw-gradient-stops))] from-red-500 via-purple-500 to-blue-500 shadow"></div>
                                            <span class="text-xs font-bold text-slate-800 dark:text-white">Custom HEX<br><span class="font-normal text-[9px] text-slate-400">Mix your own</span></span>
                                        </label>
                                    </div>
                                    <div id="wrapper_custom_colors" class="p-5 bg-slate-50 dark:bg-slate-900 rounded-xl border border-slate-200 dark:border-slate-700 mb-6 <?php echo $set['theme_color'] === 'custom' ? '' : 'hidden'; ?>">
                                        <div class="grid grid-cols-2 gap-4">
                                            <div>
                                                <label class="block text-[10px] font-bold text-slate-500 dark:text-slate-400 uppercase tracking-wider mb-2">Primary Color</label>
                                                <input type="color" name="custom_primary" value="<?php echo htmlspecialchars($set['custom_primary']); ?>">
                                            </div>
                                            <div>
                                                <label class="block text-[10px] font-bold text-slate-500 dark:text-slate-400 uppercase tracking-wider mb-2">Secondary Color</label>
                                                <input type="color" name="custom_secondary" value="<?php echo htmlspecialchars($set['custom_secondary']); ?>">
                                            </div>
                                        </div>
                                    </div>
                                    <button type="submit" class="w-full theme-btn text-white font-bold py-3 px-6 rounded-xl text-xs shadow-md transition flex justify-center items-center gap-2" style="background-color: <?php echo $primaryColor; ?>;">
                                        <i class="fa-solid fa-check"></i> Apply Selected Theme
                                    </button>
                                </div>
                            </form>
                        </div>

                        <!-- TAB 5: FOOTER CREDENTIALS -->
                        <div id="tab-footer" class="settings-content-section space-y-4 hidden">
                            <form onsubmit="event.preventDefault(); saveSection('Footer Configuration', this);" class="bg-white dark:bg-slate-800 rounded-2xl custom-shadow border border-slate-100 dark:border-slate-700 overflow-hidden">
                                <div class="bg-slate-50 dark:bg-slate-800/50 p-4 border-b border-slate-100 dark:border-slate-700">
                                    <h2 class="text-xs font-black text-slate-800 dark:text-slate-200 uppercase tracking-wider flex items-center gap-2">
                                        <i class="fa-solid fa-shoe-prints text-amber-500"></i> Footer Credentials
                                    </h2>
                                </div>
                                <div class="p-6">
                                    <div class="border-b border-slate-100 dark:border-slate-700 pb-6 mb-6">
                                        <label class="block text-[10px] font-bold text-slate-500 dark:text-slate-400 uppercase tracking-wider mb-2">Company Name & Registration</label>
                                        <input type="text" name="footer_text" value="<?php echo htmlspecialchars($set['footer_text']); ?>" class="theme-focus w-full bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-600 text-slate-900 dark:text-white rounded-xl p-3 text-sm transition-colors" required>
                                        <p class="text-[9px] text-slate-400 mt-2 italic">The copyright symbol (&copy;) and the current calendar year are added automatically.</p>
                                    </div>
                                    <button type="submit" class="w-full theme-btn text-white font-bold py-3 px-6 rounded-xl text-xs shadow-md transition flex justify-center items-center gap-2" style="background-color: <?php echo $primaryColor; ?>;">
                                        <i class="fa-solid fa-check"></i> Update Footer Settings
                                    </button>
                                </div>
                            </form>
                        </div>

                        <!-- NEW TAB 6: SYSTEM OPERATIONS -->
                        <div id="tab-operations" class="settings-content-section space-y-4 hidden">
                            <div class="bg-white dark:bg-slate-800 rounded-2xl custom-shadow border border-slate-100 dark:border-slate-700 overflow-hidden">
                                <button onclick="toggleAccordion('acc-retroactive', this)" class="w-full px-6 py-4 flex justify-between items-center bg-slate-50/50 dark:bg-slate-800/50 hover:bg-slate-50 dark:hover:bg-slate-700/50 transition-colors border-b border-slate-100 dark:border-slate-700 outline-none">
                                    <h2 class="text-xs font-black text-slate-800 dark:text-slate-200 uppercase tracking-wider flex items-center gap-2">
                                        <i class="fa-solid fa-clock-rotate-left text-rose-500"></i> Retroactive Data Entry
                                    </h2>
                                    <i class="fa-solid fa-chevron-up text-slate-400 text-sm transition-transform duration-300"></i>
                                </button>
                                
                                <div id="acc-retroactive" class="block">
                                    <form onsubmit="event.preventDefault(); saveSection('System Operations', this);">
                                        <div class="p-6 space-y-6">
                                            
                                            <!-- NEW: 3-Tier Access Control -->
                                            <div>
                                                <label class="block text-[10px] font-bold text-slate-500 dark:text-slate-400 uppercase tracking-wider mb-3">Consultant Backdating Permission</label>
                                                <div class="flex flex-col gap-3">
                                                    
                                                    <!-- Option 0: Locked -->
                                                    <label class="flex items-center gap-3 cursor-pointer p-4 border <?php echo ($set['allow_retroactive_bookings'] ?? '0') === '0' ? 'border-slate-400 bg-slate-100 dark:border-slate-500 dark:bg-slate-800/80' : 'border-slate-200 dark:border-slate-700 hover:bg-slate-50 dark:hover:bg-slate-800'; ?> rounded-xl transition-colors">
                                                        <input type="radio" name="allow_retroactive_bookings" value="0" class="w-4 h-4" style="accent-color: <?php echo $primaryColor; ?>;" <?php echo ($set['allow_retroactive_bookings'] ?? '0') === '0' ? 'checked' : ''; ?>>
                                                        <div>
                                                            <span class="text-sm font-bold text-slate-700 dark:text-slate-300 block"><i class="fa-solid fa-lock text-slate-400 mr-1"></i> Locked (Recommended)</span>
                                                            <span class="text-[10px] text-slate-500 dark:text-slate-400 font-medium">No one can backdate bookings. Strict chronological integrity enforced.</span>
                                                        </div>
                                                    </label>
                                                    
                                                    <!-- Option 1: Admins Only -->
                                                    <label class="flex items-center gap-3 cursor-pointer p-4 border <?php echo ($set['allow_retroactive_bookings'] ?? '0') === '1' ? 'border-blue-300 bg-blue-50/50 dark:border-blue-800 dark:bg-blue-900/20' : 'border-slate-200 dark:border-slate-700 hover:bg-blue-50/30 dark:hover:bg-blue-900/10'; ?> rounded-xl transition-colors">
                                                        <input type="radio" name="allow_retroactive_bookings" value="1" class="w-4 h-4" style="accent-color: <?php echo $primaryColor; ?>;" <?php echo ($set['allow_retroactive_bookings'] ?? '0') === '1' ? 'checked' : ''; ?>>
                                                        <div>
                                                            <span class="text-sm font-bold text-blue-700 dark:text-blue-400 block"><i class="fa-solid fa-user-shield text-blue-500 mr-1"></i> Unlocked for Admins</span>
                                                            <span class="text-[10px] text-blue-600 dark:text-blue-500/80 font-medium">Only System Administrators can bypass the date lock. Consultants remain restricted.</span>
                                                        </div>
                                                    </label>

                                                    <!-- Option 2: Everyone -->
                                                    <label class="flex items-center gap-3 cursor-pointer p-4 border <?php echo ($set['allow_retroactive_bookings'] ?? '0') === '2' ? 'border-rose-300 bg-rose-50/50 dark:border-rose-800 dark:bg-rose-900/20' : 'border-slate-200 dark:border-slate-700 hover:bg-rose-50/30 dark:hover:bg-rose-900/10'; ?> rounded-xl transition-colors">
                                                        <input type="radio" name="allow_retroactive_bookings" value="2" class="w-4 h-4" style="accent-color: <?php echo $primaryColor; ?>;" <?php echo ($set['allow_retroactive_bookings'] ?? '0') === '2' ? 'checked' : ''; ?>>
                                                        <div>
                                                            <span class="text-sm font-bold text-rose-700 dark:text-rose-400 block"><i class="fa-solid fa-unlock-keyhole text-rose-500 mr-1"></i> Unlocked for Everyone (Migration Mode)</span>
                                                            <span class="text-[10px] text-rose-600 dark:text-rose-500/80 font-medium">All users (including Consultants) can retroactively log historical data.</span>
                                                        </div>
                                                    </label>

                                                </div>
                                            </div>
                                            
                                            <div class="border-t border-slate-100 dark:border-slate-700 pt-6">
                                                <button type="submit" class="w-full theme-btn text-white font-bold py-3 px-6 rounded-xl text-xs shadow-md transition flex justify-center items-center gap-2" style="background-color: <?php echo $primaryColor; ?>;">
                                                    <i class="fa-solid fa-check"></i> Update Security Protocols
                                                </button>
                                            </div>
                                        </div>
                                    </form>
                                </div>
                            </div>
                        </div>

                    </div>
                </div>

            </div>
            <?php include 'Includes/footer.php'; ?>
        </main>
    </div>
</body>
</html>