<?php
session_start();
if (!isset($_SESSION['logged_in']) || strtolower($_SESSION['role']) !== 'admin') {
    header("Location: dashboard.php");
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
    <title>Manage Users - Rhino Camp</title>
	
	<!-- Dynamic Favicon Override -->
	<link rel="icon" type="image/png" href="<?php echo !empty($set['logo_path']) ? htmlspecialchars($set['logo_path']) : 'data:image/x-icon;base64,'; ?>">

    <script src="https://cdn.tailwindcss.com"></script>
    <script>
        tailwind.config = { darkMode: 'class', }
    </script>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    
    <!-- Version bumped to clear browser cache and load Theme JS logic -->
    <script src="js/manage_users.js?v=4" defer></script>
    
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
        .theme-focus:focus { outline: none; border-color: var(--theme-color) !important; box-shadow: 0 0 0 3px var(--theme-color-focus) !important; }
    </style>
</head>
<body data-primary-color="<?php echo $primaryColor; ?>" class="bg-[#f8fafc] dark:bg-slate-900 text-slate-800 dark:text-slate-200 font-sans antialiased min-h-screen overflow-hidden transition-colors duration-300">
    
    <!-- FULL SCREEN WRAPPER -->
    <div class="flex h-screen w-screen overflow-hidden">
        
        <!-- 1. LEFT SIDEBAR -->
        <?php include 'Includes/sidebar.php'; ?>

        <!-- RIGHT CONTENT AREA -->
        <main class="flex-1 flex flex-col h-full overflow-hidden bg-[#f8fafc] dark:bg-slate-900 transition-colors duration-300">
            
            <!-- 2. GLOBAL HEADER -->
            <?php include 'Includes/header.php'; ?>

            <!-- 3. SCROLLING MAIN CONTENT -->
            <div class="flex-1 overflow-y-auto p-4 lg:p-6">
                
                <div class="max-w-7xl mx-auto space-y-6">
                    
                    <!-- Premium Section Header -->
                    <div class="bg-white dark:bg-slate-800 p-5 rounded-2xl custom-shadow border border-slate-100 dark:border-slate-700 flex items-center gap-3 transition-colors duration-300">
                        <div class="w-10 h-10 rounded-xl bg-slate-100 dark:bg-slate-900/50 theme-text flex items-center justify-center shadow-inner shrink-0 border border-slate-200 dark:border-slate-700">
                            <i class="fa-solid fa-users-gear text-lg"></i>
                        </div>
                        <div>
                            <h2 class="text-sm font-mono font-bold tracking-widest text-[#0f172a] dark:text-white uppercase">Corporate Identity Security Manager</h2>
                            <p class="text-[10px] font-bold text-slate-400 dark:text-slate-500 uppercase tracking-widest mt-0.5">Manage workspace accounts and system access privileges</p>
                        </div>
                    </div>

                    <!-- User Table Container -->
                    <div class="bg-white dark:bg-slate-800 rounded-2xl custom-shadow border border-slate-100 dark:border-slate-700 w-full h-auto overflow-hidden transition-colors duration-300">
                        
                        <div class="p-6 border-b border-slate-100 dark:border-slate-700 flex flex-col sm:flex-row items-center justify-between bg-white dark:bg-slate-800 gap-4 transition-colors duration-300">
                            <h3 class="text-sm font-mono font-bold tracking-widest text-slate-800 dark:text-white uppercase">Active Workspace Users</h3>
                            <button onclick="openUserModal('add')" class="theme-btn text-white text-xs font-bold py-2.5 px-5 rounded-lg shadow-sm transition flex items-center gap-2">
                                <i class="fa-solid fa-plus"></i> Register New Workspace User
                            </button>
                        </div>
                        
                        <div class="overflow-x-auto w-full">
                            <table class="w-full text-left whitespace-nowrap">
                                <thead class="bg-slate-50 dark:bg-slate-900/50 border-b border-slate-100 dark:border-slate-700 text-xs text-slate-500 dark:text-slate-400 font-medium font-mono shadow-sm transition-colors duration-300">
                                    <tr>
                                        <th class="py-4 px-6 tracking-wide">User Index Mapping Target</th>
                                        <th class="py-4 px-6 tracking-wide">System Username Handle</th>
                                        <th class="py-4 px-6 tracking-wide">Assigned Authority Level Scope</th>
                                        <th class="py-4 px-6 text-right tracking-wide">Console Management Vectors</th>
                                    </tr>
                                </thead>
                                <tbody id="usersTableBody" class="text-sm divide-y divide-slate-50 dark:divide-slate-700/50 font-mono bg-white dark:bg-slate-800 transition-colors duration-300">
                                    <!-- Populated dynamically via JS -->
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
                
            </div>

            <!-- 4. GLOBAL FOOTER -->
            <?php include 'Includes/footer.php'; ?>

        </main>
    </div>

    <!-- Account Profile Modal -->
    <div id="user-modal-backdrop" class="fixed inset-0 bg-slate-900/40 dark:bg-slate-900/80 backdrop-blur-sm z-50 hidden items-center justify-center p-4 overflow-y-auto transition-colors duration-300">
        <div class="bg-white dark:bg-slate-900 w-full max-w-md rounded-xl shadow-2xl p-8 space-y-6 relative animate-fadeIn border border-slate-100 dark:border-slate-700 transition-colors duration-300">
            
            <h2 id="modal-title" class="text-2xl font-bold text-slate-700 dark:text-white text-center tracking-tight mb-2">Register Account Profile</h2>
            
            <form id="userProfileForm" onsubmit="submitUserForm(event)" class="space-y-5">
                
                <input type="hidden" name="action" id="form-action" value="add_user">
                <input type="hidden" name="user_id" id="user_id" value="">

                <div>
                    <label class="block text-xs font-mono text-slate-500 dark:text-slate-400 mb-1.5">Full User Display Name</label>
                    <input type="text" name="display_name" required class="theme-focus w-full bg-slate-50 dark:bg-slate-800 border border-slate-200 dark:border-slate-600 p-2.5 rounded text-sm text-slate-800 dark:text-white transition-colors duration-300">
                </div>

                <div>
                    <label class="block text-xs font-mono text-slate-500 dark:text-slate-400 mb-1.5">Account Username Identity</label>
                    <input type="text" name="username" required class="theme-focus w-full bg-slate-50 dark:bg-slate-800 border border-slate-200 dark:border-slate-600 p-2.5 rounded text-sm text-slate-800 dark:text-white font-mono transition-colors duration-300">
                </div>

                <div>
                    <label class="block text-xs font-mono text-slate-500 dark:text-slate-400 mb-1.5">
                        Security Cipher Password 
                        <span id="password-hint" class="hidden text-slate-400 dark:text-slate-500 font-normal italic ml-1">(Leave blank to keep current)</span>
                    </label>
                    <input type="password" name="password" id="user_password" required class="theme-focus w-full bg-slate-50 dark:bg-slate-800 border border-slate-200 dark:border-slate-600 p-2.5 rounded text-sm text-slate-800 dark:text-white font-mono tracking-widest transition-colors duration-300">
                </div>

                <div>
                    <label class="block text-xs font-mono text-slate-500 dark:text-slate-400 mb-1.5">Authority Level Scope</label>
                    <select name="role" id="user_role" required class="theme-focus w-full bg-white dark:bg-slate-800 border border-slate-200 dark:border-slate-600 p-2.5 rounded text-sm text-slate-800 dark:text-white cursor-pointer transition-colors duration-300 disabled:bg-slate-100 dark:disabled:bg-slate-900 disabled:text-slate-400 dark:disabled:text-slate-600">
                        <option value="consultant">Standard Operator (Worker User)</option>
                        <option value="admin">System Administrator (Full Access)</option>
                    </select>
                </div>

                <div class="flex items-center gap-3 pt-4">
                    <button type="submit" class="theme-btn text-white font-bold py-2.5 px-6 rounded transition shadow-sm">
                        Commit Profile Settings
                    </button>
                    <button type="button" onclick="closeUserModal()" class="bg-[#64748b] hover:bg-[#475569] text-white font-bold py-2.5 px-6 rounded transition shadow-sm">
                        Cancel
                    </button>
                </div>
            </form>
        </div>
    </div>
</body>
</html>