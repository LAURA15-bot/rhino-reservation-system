<?php
session_start();
if (!isset($_SESSION['logged_in']) || strtolower($_SESSION['role']) !== 'admin') { header("Location: dashboard.php"); exit; }
$current_page = basename($_SERVER['PHP_SELF']);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8"><title>Security Audit Stream - Rhino Camp</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <script src="js/system_logs.js" defer></script>
</head>
<body class="bg-slate-50 text-slate-800 font-sans flex h-screen overflow-hidden">
    <?php include 'Includes/sidebar.php'; ?>
    <main class="flex-1 overflow-y-auto p-4 lg:p-8">
        
        <div class="bg-white rounded-xl shadow-[0_4px_20px_-2px_rgba(0,0,0,0.05)] border border-slate-100 overflow-hidden max-w-7xl mx-auto mt-4">
            
            <div class="p-5 border-b border-slate-100 flex items-center gap-3 bg-white">
                <i class="fa-solid fa-shield-halved text-rose-500 text-lg"></i>
                <h2 class="text-xs font-mono font-bold tracking-widest text-slate-800 uppercase">Corporate Database Security Audit Stream</h2>
            </div>
            
            <div class="overflow-x-auto">
                <table class="w-full text-left whitespace-nowrap">
                    <thead class="bg-white border-b border-slate-100 text-[10px] text-slate-500 font-medium">
                        <tr>
                            <th class="py-4 px-5">Event Timestamp</th>
                            <th class="py-4 px-5">Account Token</th>
                            <th class="py-4 px-5">Role</th>
                            <th class="py-4 px-5">Action Code</th>
                            <th class="py-4 px-5">Context Operational Matrix Payload Details</th>
                            <th class="py-4 px-5 text-right">Host IP Origin</th>
                        </tr>
                    </thead>
                    <tbody id="logsTableBody" class="text-sm divide-y divide-slate-50">
                        <!-- Populated dynamically via JS -->
                    </tbody>
                </table>
            </div>
        </div>
        
    </main>
</body>
</html>