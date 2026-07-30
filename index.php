<?php
// If the user is already logged in, instantly redirect them to the dashboard
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
if (isset($_SESSION['logged_in']) && $_SESSION['logged_in'] === true) {
    header("Location: dashboard.php");
    exit;
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Rhino Tourist Camp - System Terminal</title>
    
    <!-- Load Tailwind for structural layout -->
    <script src="https://cdn.tailwindcss.com"></script>
    
    <!-- Load FontAwesome for the button icons -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    
    <style>
        /* 
         * 1. THE CUSTOM YELLOW BACKGROUND SHAPE
         */
        .yellow-canvas-shape {
            position: absolute;
            top: -15vh;
            left: -10vw;
            width: 75vw;
            height: 120vh;
            background-color: #e5eab6; 
            border-radius: 35% 65% 55% 45% / 40% 30% 70% 60%;
            transform: rotate(-5deg);
            z-index: 0;
            box-shadow: 40px 40px 100px rgba(0,0,0,0.05);
            transition: border-radius 3s ease-in-out;
        }

        .yellow-canvas-shape:hover {
            border-radius: 45% 55% 45% 55% / 50% 40% 60% 50%;
        }

        /* 
         * 2. THE PENTAGON BUTTON
         */
        .pentagon-btn-wrapper {
            position: relative;
            display: inline-block;
            margin-top: 2.5rem;
            perspective: 1000px; 
            z-index: 10;
        }

        .pentagon-btn {
            display: flex;
            align-items: center;
            justify-content: center;
            width: 240px;
            height: 65px;
            background-color: #1e293b; 
            color: #ffffff;
            font-size: 14px;
            font-weight: 900;
            text-transform: uppercase;
            letter-spacing: 1.5px;
            text-decoration: none;
            clip-path: polygon(0% 0%, 85% 0%, 100% 50%, 85% 100%, 0% 100%);
            transition: transform 0.8s cubic-bezier(0.68, -0.55, 0.265, 1.55), background-color 0.4s ease;
            transform-style: preserve-3d;
        }

        .pentagon-btn:hover {
            transform: rotateY(360deg);
            background-color: #046a38; 
        }

        .pentagon-icon {
            margin-left: 12px;
            transition: transform 0.3s ease;
        }

        .pentagon-btn:hover .pentagon-icon {
            transform: translateX(4px);
        }

        /* 
         * 3. TYPOGRAPHY HACKS
         */
        .text-outline {
            color: transparent;
            -webkit-text-stroke: 2px #0f172a;
        }
    </style>
</head>
<body class="bg-[#fcfdfd] font-sans antialiased h-screen w-screen overflow-hidden flex items-center relative selection:bg-[#046a38] selection:text-white">
    
    <!-- The Custom Organic Yellow Background -->
    <div class="yellow-canvas-shape pointer-events-none"></div>

    <!-- Random Ambient Colored Orbs to fill the white space beautifully -->
    <div class="absolute top-[10%] left-[60%] w-48 h-48 bg-rose-300 rounded-full blur-[90px] opacity-30 z-0 pointer-events-none"></div>
    <div class="absolute top-[30%] right-[10%] w-64 h-64 bg-blue-300 rounded-full blur-[100px] opacity-20 z-0 pointer-events-none"></div>
    <div class="absolute bottom-[20%] right-[30%] w-56 h-56 bg-amber-300 rounded-full blur-[90px] opacity-30 z-0 pointer-events-none"></div>
    <div class="absolute bottom-[5%] left-[45%] w-40 h-40 bg-emerald-400 rounded-full blur-[80px] opacity-20 z-0 pointer-events-none"></div>

    <!-- Main Content Container -->
    <div class="relative z-20 w-full max-w-[1500px] mx-auto px-8 md:px-16 flex flex-col lg:flex-row justify-between items-center h-full">
        
        <!-- Left Side: Typography & Login Button -->
        <div class="max-w-2xl w-full pt-20 lg:pt-0 z-30">
            
            <!-- Tiny Top Label -->
            <div class="inline-flex items-center gap-2 mb-8 px-4 py-1.5 rounded-full border border-slate-900/10 bg-white/40 backdrop-blur-md shadow-sm">
                <div class="w-2 h-2 rounded-full bg-[#046a38] animate-pulse"></div>
                <span class="text-[10px] font-black tracking-widest text-slate-700 uppercase">
                    Authorized Personnel Only
                </span>
            </div>
            
            <!-- Massive Professional Typography -->
            <h1 class="text-6xl md:text-[5.5rem] lg:text-[7rem] font-black text-slate-900 tracking-tighter leading-[0.9] mb-6">
                Rhino<br>
                <span class="text-outline drop-shadow-sm">Reservation</span><br>
                System.
            </h1>
            
            <p class="text-lg md:text-xl font-medium text-slate-700/80 leading-relaxed max-w-lg mt-8">
                Enterprise grade property configuration, guest ledger administration, and dynamic financial tracking terminal.
            </p>

            <!-- The Custom 3D Pentagon Button -->
            <div class="pentagon-btn-wrapper">
                <a href="login.php" class="pentagon-btn shadow-2xl">
                    Proceed to Login <i class="fa-solid fa-arrow-right pentagon-icon"></i>
                </a>
            </div>
            
        </div>
        
    </div>

    <!-- The Static "Secure Access" Block -->
    <!-- Positioned static at the bottom right corner -->
    <div class="absolute bottom-12 right-12 lg:bottom-16 lg:right-24 bg-white px-8 py-5 rounded-[2rem] shadow-2xl shadow-slate-200/60 flex items-center gap-4 text-slate-700 border border-slate-100 z-30">
        <div class="w-12 h-12 rounded-full bg-slate-50 text-slate-800 flex items-center justify-center shadow-inner text-xl border border-slate-200/50">
            <i class="fa-solid fa-shield-halved"></i>
        </div>
        <div class="flex flex-col">
            <span class="text-[10px] font-bold text-slate-400 uppercase tracking-widest">Access Control</span>
            <span class="text-sm font-black uppercase tracking-wider text-slate-800">Secure Entry</span>
        </div>
    </div>

</body>
</html>