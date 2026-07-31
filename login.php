<?php
session_start();
require 'Includes/database.php';

$error = '';

// Check if redirected due to session inactivity timeout
if (isset($_GET['timeout']) && $_GET['timeout'] == 1) {
    $error = "Your session expired due to inactivity. Please log in again.";
}

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
    <title>Login - Rhino System</title>
    <!-- Tailwind CSS and FontAwesome for modern styling -->
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
</head>
<body class="bg-slate-50 flex items-center justify-center min-h-screen font-sans">
    
    <div class="w-full max-w-md p-8 bg-white rounded-3xl shadow-xl border border-slate-100 mx-4">
        
        <!-- Logo & Branding -->
        <div class="text-center mb-8 space-y-2">
            <div class="inline-flex items-center justify-center w-16 h-16 bg-[#046a38] text-white rounded-2xl shadow-md mb-2">
                <i class="fa-solid fa-campground text-3xl"></i>
            </div>
            <h1 class="text-2xl font-black tracking-wider uppercase text-slate-900">Rhino Camp</h1>
            <p class="text-xs text-slate-400 font-bold uppercase tracking-widest">Reservation Suite</p>
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
                        class="w-full bg-slate-50 border border-slate-200 text-slate-900 text-sm rounded-xl focus:ring-2 focus:ring-[#046a38] focus:border-[#046a38] block pl-10 p-3 outline-none transition" 
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
                        class="w-full bg-slate-50 border border-slate-200 text-slate-900 text-sm rounded-xl focus:ring-2 focus:ring-[#046a38] focus:border-[#046a38] block pl-10 p-3 outline-none transition" 
                        placeholder="••••••••">
                </div>
                
                <div class="flex items-center justify-between mt-3 px-1">
                    <label class="flex items-center gap-2 cursor-pointer group">
                        <input type="checkbox" onclick="togglePassword()" class="w-4 h-4 text-[#046a38] bg-slate-100 border-slate-300 rounded focus:ring-[#046a38] cursor-pointer">
                        <span class="text-xs font-bold text-slate-500 group-hover:text-slate-700 transition">Show Password</span>
                    </label>
                    <a href="forgot_password.php" class="text-xs font-bold text-[#046a38] hover:text-[#03542c] hover:underline transition">Forgot Password?</a>
                </div>
            </div>
            
            <button type="submit" class="w-full bg-[#046a38] hover:bg-[#03542c] text-white font-bold py-3.5 px-5 rounded-xl text-sm transition shadow-md hover:shadow-lg flex justify-center items-center gap-2 mt-4">
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