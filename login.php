<?php
session_start();
require 'Includes/database.php';

// 1. LOAD SYSTEM SETTINGS
if (!isset($GLOBALS['system_settings'])) {
    require_once 'Includes/load_settings.php';
}
$set = $GLOBALS['system_settings'];

// 2. EXTRACT ACTIVE BRANDING & THEME
$theme = $set['theme_color'] ?? 'emerald';
$primaryColor = '#046a38'; 
if ($theme === 'safari') $primaryColor = '#8B3C28';
elseif ($theme === 'kairi') $primaryColor = '#802b1f';
elseif ($theme === 'blue') $primaryColor = '#2563eb';
elseif ($theme === 'custom') $primaryColor = $set['custom_primary'] ?? '#046a38';

$displayType = $set['sidebar_display_type'] ?? 'icon';
$logoPath = $set['logo_path'] ?? '';
$title = $set['sidebar_title'] ?? 'Rhino Camp';
$subtitle = $set['sidebar_subtitle'] ?? 'Reservation Suite';
$icon = $set['sidebar_icon'] ?? 'fa-campground';

$error = '';

// Check if redirected due to session inactivity timeout
if (isset($_GET['timeout']) && $_GET['timeout'] == 1) {
    $error = "Your session expired due to inactivity. Please log in again.";
}

// 3. AUTHENTICATION LOGIC
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $user = $_POST['username'];
    $pass = $_POST['password'];
    $ip = $_SERVER['REMOTE_ADDR']; // Capture Host IP

    $stmt = $pdo->prepare("SELECT * FROM users WHERE username = :username");
    $stmt->execute(['username' => $user]);
    $user_data = $stmt->fetch();

    if ($user_data && password_verify($pass, $user_data['password'])) {
        $_SESSION['logged_in'] = true;
        $_SESSION['username'] = $user_data['username'];
        $_SESSION['role'] = $user_data['role'] ?? 'Consultant'; 
        $_SESSION['last_activity'] = time(); // Initialize inactivity timestamp

        // SUCCESSFUL LOGIN AUDIT LOG
        try {
            $logStmt = $pdo->prepare("INSERT INTO system_logs (username, role, action_code, action, ip_address) VALUES (?, ?, ?, ?, ?)");
            $logStmt->execute([$user_data['username'], $_SESSION['role'], 'USER_LOGIN', 'User successfully authenticated session console.', $ip]);
        } catch (PDOException $e) { }

        header("Location: dashboard.php");
        exit;
    } else {
        // FAILED LOGIN AUDIT LOG
        try {
            $logStmt = $pdo->prepare("INSERT INTO system_logs (username, role, action_code, action, ip_address) VALUES (?, 'UNKNOWN', 'LOGIN_FAILED', 'Invalid authentication credentials attempt block.', ?)");
            $logStmt->execute([$user, $ip]);
        } catch (PDOException $e) { }
        
        $error = "Incorrect login info! Please check your username and password.";
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login - <?php echo htmlspecialchars($title); ?></title>
    <!-- Tailwind CSS and FontAwesome for modern styling -->
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    
    <style>
        /* Dynamic Theme Integration */
        :root {
            --theme-color: <?php echo $primaryColor; ?>;
            --theme-color-focus: <?php echo $primaryColor; ?>33; /* 20% Opacity for the focus ring */
        }
        
        .custom-focus:focus {
            border-color: var(--theme-color) !important;
            box-shadow: 0 0 0 4px var(--theme-color-focus) !important;
        }
        
        .theme-btn {
            background-color: var(--theme-color);
            transition: filter 0.2s ease-in-out, transform 0.1s ease;
        }
        
        .theme-btn:hover {
            filter: brightness(85%);
            transform: translateY(-1px);
        }
        
        .theme-text {
            color: var(--theme-color);
            transition: filter 0.2s ease;
        }
        
        .theme-text:hover {
            filter: brightness(70%);
        }
    </style>
</head>
<body class="bg-slate-50 flex items-center justify-center min-h-screen font-sans selection:bg-[var(--theme-color)] selection:text-white">
    
    <div class="w-full max-w-md p-8 bg-white rounded-3xl shadow-xl border border-slate-100 mx-4">
        
        <!-- Dynamic Logo & Branding -->
        <div class="text-center mb-8 space-y-2 flex flex-col items-center">
            
            <?php if ($displayType === 'logo' && !empty($logoPath)): ?>
                <!-- Render Custom Image Logo -->
                <div class="inline-flex items-center justify-center w-40 h-24 mb-2 transition-all">
                    <img src="<?php echo htmlspecialchars($logoPath); ?>" alt="System Logo" class="max-w-full max-h-full object-contain drop-shadow-sm">
                </div>
            <?php else: ?>
                <!-- Render Dynamic FontAwesome Icon -->
                <div class="inline-flex items-center justify-center w-16 h-16 text-white rounded-2xl shadow-md mb-2 theme-btn">
                    <i class="fa-solid <?php echo htmlspecialchars($icon); ?> text-3xl"></i>
                </div>
            <?php endif; ?>
            
            <?php if (!empty($title)): ?>
                <h1 class="text-2xl font-black tracking-wider uppercase text-slate-900"><?php echo htmlspecialchars($title); ?></h1>
            <?php endif; ?>
            
            <?php if (!empty($subtitle)): ?>
                <p class="text-xs text-slate-400 font-bold uppercase tracking-widest"><?php echo htmlspecialchars($subtitle); ?></p>
            <?php endif; ?>
            
        </div>

        <!-- Error Message Alert -->
        <?php if($error): ?>
            <div class="bg-rose-50 border border-rose-200 text-rose-600 p-4 rounded-xl text-xs font-bold flex items-center gap-2 mb-6 shadow-sm">
                <i class="fa-solid fa-circle-exclamation text-sm"></i> <?php echo htmlspecialchars($error); ?>
            </div>
        <?php endif; ?>
        
        <!-- Login Form -->
        <form method="POST" action="login.php" class="space-y-5">
            <div>
                <label class="block text-[10px] font-bold text-slate-500 uppercase tracking-wider mb-1.5">Username</label>
                <div class="relative">
                    <div class="absolute inset-y-0 left-0 flex items-center pl-4 pointer-events-none text-slate-400">
                        <i class="fa-solid fa-user"></i>
                    </div>
                    <input type="text" name="username" required 
                        class="w-full bg-slate-50 border border-slate-200 text-slate-900 text-sm rounded-xl block pl-10 p-3 outline-none transition custom-focus" 
                        placeholder="Enter your username">
                </div>
            </div>
            
            <div>
                <label class="block text-[10px] font-bold text-slate-500 uppercase tracking-wider mb-1.5">Password</label>
                <div class="relative">
                    <div class="absolute inset-y-0 left-0 flex items-center pl-4 pointer-events-none text-slate-400">
                        <i class="fa-solid fa-lock"></i>
                    </div>
                    <input type="password" name="password" id="passwordInput" required 
                        class="w-full bg-slate-50 border border-slate-200 text-slate-900 text-sm rounded-xl block pl-10 p-3 outline-none transition custom-focus" 
                        placeholder="••••••••">
                </div>
                
                <div class="flex items-center justify-between mt-3 px-1">
                    <label class="flex items-center gap-2 cursor-pointer group">
                        <input type="checkbox" onclick="togglePassword()" class="w-4 h-4 bg-slate-100 border-slate-300 rounded cursor-pointer transition-colors" style="accent-color: var(--theme-color);">
                        <span class="text-xs font-bold text-slate-500 group-hover:text-slate-700 transition">Show Password</span>
                    </label>
                    <a href="forgot_password.php" class="text-xs font-bold hover:underline transition theme-text">Forgot Password?</a>
                </div>
            </div>
            
            <button type="submit" class="w-full theme-btn text-white font-bold py-3.5 px-5 rounded-xl text-sm transition shadow-md flex justify-center items-center gap-2 mt-4">
                Secure Login <i class="fa-solid fa-arrow-right-to-bracket"></i>
            </button>
        </form>
    </div>

    <!-- Simple JavaScript for Show Password -->
    <script>
        function togglePassword() {
            const passInput = document.getElementById("passwordInput");
            if (passInput.type === "password") {
                passInput.type = "text";
            } else {
                passInput.type = "password";
            }
        }
    </script>
</body>
</html>