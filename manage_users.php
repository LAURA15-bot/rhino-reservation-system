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
    <title>Manage Users - Rhino Camp</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <script src="js/manage_users.js" defer></script>
</head>
<body class="bg-[#f8fafc] text-slate-800 font-sans flex h-screen overflow-hidden">
    
    <?php include 'Includes/sidebar.php'; ?>
    
    <main class="flex-1 overflow-y-auto p-4 lg:p-8">
        
        <!-- Corporate Identity Security Manager Container -->
        <div class="bg-white rounded-xl shadow-[0_4px_20px_-2px_rgba(0,0,0,0.05)] border border-slate-100 overflow-hidden max-w-7xl mx-auto mt-4">
            
            <div class="p-6 border-b border-slate-100 flex items-center justify-between bg-white">
                <h2 class="text-sm font-mono font-bold tracking-widest text-[#0f172a] uppercase">Corporate Identity Security Manager</h2>
                <button onclick="openUserModal('add')" class="bg-[#3b82f6] hover:bg-[#2563eb] text-white text-xs font-bold py-2.5 px-5 rounded-lg shadow-sm transition flex items-center gap-2">
                    <i class="fa-solid fa-plus"></i> Register New Workspace User
                </button>
            </div>
            
            <div class="overflow-x-auto p-2">
                <table class="w-full text-left whitespace-nowrap">
                    <thead class="bg-white border-b border-slate-100 text-xs text-slate-500 font-medium font-mono">
                        <tr>
                            <th class="py-4 px-6 tracking-wide">User Index Mapping Target</th>
                            <th class="py-4 px-6 tracking-wide">System Username Handle</th>
                            <th class="py-4 px-6 tracking-wide">Assigned Authority Level Scope</th>
                            <th class="py-4 px-6 text-right tracking-wide">Console Management Vectors</th>
                        </tr>
                    </thead>
                    <tbody id="usersTableBody" class="text-sm divide-y divide-slate-50 font-mono">
                        <!-- Populated dynamically via JS -->
                    </tbody>
                </table>
            </div>
        </div>
        
    </main>

    <!-- Account Profile Modal -->
    <div id="user-modal-backdrop" class="fixed inset-0 bg-slate-900/40 backdrop-blur-sm z-50 hidden items-center justify-center p-4 overflow-y-auto transition-opacity">
        <div class="bg-white w-full max-w-md rounded-xl shadow-2xl p-8 space-y-6 relative animate-fadeIn">
            
            <h2 id="modal-title" class="text-2xl font-bold text-slate-700 text-center tracking-tight mb-2">Register Account Profile</h2>
            
            <form id="userProfileForm" onsubmit="submitUserForm(event)" class="space-y-5">
                
                <input type="hidden" name="action" id="form-action" value="add_user">
                <input type="hidden" name="user_id" id="user_id" value="">

                <div>
                    <label class="block text-xs font-mono text-slate-500 mb-1.5">Full User Display Name</label>
                    <input type="text" name="display_name" required class="w-full bg-white border border-slate-200 p-2.5 rounded text-sm text-slate-800 outline-none focus:border-blue-400 focus:ring-1 focus:ring-blue-400 transition">
                </div>

                <div>
                    <label class="block text-xs font-mono text-slate-500 mb-1.5">Account Username Identity</label>
                    <input type="text" name="username" required class="w-full bg-blue-50/50 border border-slate-200 p-2.5 rounded text-sm text-slate-800 font-mono outline-none focus:border-blue-400 focus:ring-1 focus:ring-blue-400 transition">
                </div>

                <div>
                    <label class="block text-xs font-mono text-slate-500 mb-1.5">
                        Security Cipher Password 
                        <span id="password-hint" class="hidden text-slate-400 font-normal italic ml-1">(Leave blank to keep current)</span>
                    </label>
                    <input type="password" name="password" id="user_password" required class="w-full bg-blue-50/50 border border-slate-200 p-2.5 rounded text-sm text-slate-800 font-mono tracking-widest outline-none focus:border-blue-400 focus:ring-1 focus:ring-blue-400 transition">
                </div>

                <div>
                    <label class="block text-xs font-mono text-slate-500 mb-1.5">Authority Level Scope</label>
                    <select name="role" id="user_role" required class="w-full bg-white border border-slate-200 p-2.5 rounded text-sm text-slate-800 outline-none focus:border-blue-400 focus:ring-1 focus:ring-blue-400 transition cursor-pointer disabled:bg-slate-100 disabled:text-slate-400">
                        <option value="consultant">Standard Operator (Worker User)</option>
                        <option value="admin">System Administrator (Full Access)</option>
                    </select>
                </div>

                <div class="flex items-center gap-3 pt-4">
                    <button type="submit" class="bg-[#3b82f6] hover:bg-[#2563eb] text-white font-bold py-2.5 px-6 rounded transition shadow-sm">
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